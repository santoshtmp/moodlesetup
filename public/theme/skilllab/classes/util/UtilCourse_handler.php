<?php

/**
 * @package   theme_skilllab   
 * @copyright 2023 yipl skill lab csc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\util;

defined('MOODLE_INTERNAL') || die;

use completion_info;
use moodle_url;
use core_course_list_element;
use coursecat_helper;
use core_course_category;
use html_writer;
use stdClass;
use core_completion\progress;
use course_modinfo;
use Exception;

// require_once($CFG->dirroot . '/course/renderer.php');
// include_once($CFG->dirroot . '/course/lib.php');
// require_once($CFG->libdir . '/completionlib.php');

/**
 * UtilCourse_handler class utility class
 *
 * @package    theme_skilllab
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class UtilCourse_handler {

    /**
     * return course mod_customcert infomation
     * @param int $course_id course id     
     * @return string mod-custom certificate url
     */
    public static function course_mod_customcert($course_id, $user_id = '') {
        global $DB, $CFG;
        $customcert_data = [
            'mod_id' => '',
            'customcert_id' => '',
            'certificate_url' => '',
            'certificate_url_download' => '',
            'certificate_issues' => false,
            'certificate_issues_date' => 0,
            'certificate_issues_code' => ''
        ];
        $query = 'SELECT course_modules.id AS id, course_modules.instance AS instance
            FROM {course_modules} course_modules 
            JOIN {modules} modules ON modules.id = course_modules.module
            WHERE course_modules.course = :courseid AND modules.name = :modules_name AND course_modules.visible = :course_modules_visible AND  course_modules.deletioninprogress = :deletioninprogress
            Order By course_modules.id DESC
            LIMIT 1
            ';
        $params = [
            'courseid' => $course_id,
            'modules_name' => 'customcert',
            'course_modules_visible' => 1,
            'deletioninprogress' => 0
        ];
        $mod_customcert = $DB->get_record_sql($query, $params);
        if ($mod_customcert) {
            $customcert_data['mod_id'] = $mod_customcert->id;
            $customcert_data['customcert_id'] = $mod_customcert->instance;
            $customcert_data['certificate_url'] = $CFG->wwwroot . '/mod/customcert/view.php?id=' . $mod_customcert->id;
            $customcert_data['certificate_url_download']  = $CFG->wwwroot . '/mod/customcert/view.php?id=' . $mod_customcert->id . '&downloadown=1';
            if ($user_id && $mod_customcert->instance) {
                // $DB->record_exists('customcert_issues', ['userid' => $user_id, 'customcertid' => $customcert_id])
                $customcert_issues = $DB->get_record('customcert_issues', ['userid' => $user_id, 'customcertid' => $mod_customcert->instance]);
                if ($customcert_issues) {
                    $customcert_data['certificate_issues'] = true;
                    $customcert_data['certificate_issues_date'] = $customcert_issues->timecreated;
                    $customcert_data['certificate_issues_code'] = $customcert_issues->code;
                }
            }
        }
        return $customcert_data;
    }

    /**
     * Returns given course's summary with proper embedded files urls and formatted
     * @param stdClass $course
     * @return string
     */
    public static function get_course_formatted_summary($course) {
        global $CFG;
        try {
            require_once($CFG->libdir . '/filelib.php');
            if (!$course->summary) {
                return '';
            }
            $options = null;
            $context = \context_course::instance($course->id);
            $summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', null);
            $summary = format_text($summary, $course->summaryformat, $options, $course->id);

            return $summary;
        } catch (\Throwable $th) {
            $chelper = new coursecat_helper();
            $courseSummary = $chelper->get_course_formatted_summary($courseElement, array('noclean' => true, 'para' => false));
            return $courseSummary;
        }
    }

    /**
     *  Returns the first course's summary image url
     * @param \stdClass $course
     * @param boolen check to return default image or null if there is no course image
     * @return string course image url or null
     */
    public static function get_course_image($course, $default_image_on_null = false) {
        global $CFG, $OUTPUT;
        $course = new core_course_list_element($course);

        foreach ($course->get_course_overviewfiles() as $file) {
            if ($file->is_valid_image()) {
                $url = moodle_url::make_file_url(
                    "$CFG->wwwroot/pluginfile.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                        $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                    !$file->is_valid_image()
                );

                return $url->out();
            }
        }
        if ($default_image_on_null) {
            return $OUTPUT->get_generated_image_for_id($course->id);
        }
        return '';
    }

    /**
     * @param \stdClass $course
     */
    public function get_section_progress($course, $section) {
        // if($section->section == 0){
        //     return '';
        // }

        global $OUTPUT, $USER, $COURSE;
        $context = \context_course::instance($COURSE->id);
        $roles = get_user_roles($context, $USER->id);
        $user_student = false;
        foreach ($roles as $key => $value) {
            if ($value->roleid == '5') {
                $user_student = true;
            }
        }
        if (!$user_student  || isguestuser() || empty($course)) {
            return;
        }

        $modinfo = get_fast_modinfo($course);
        if (empty($modinfo->sections[$section->section])) {
            return '';
        }

        // Generate array with count of activities in this section.
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new completion_info($course);
        foreach ($modinfo->sections[$section->section] as $cmid) {
            $thismod = $modinfo->cms[$cmid];

            if ($thismod->modname == 'label') {
                // Labels are special (not interesting for students)!
                continue;
            }

            if ($thismod->uservisible) {
                if (isset($sectionmods[$thismod->modname])) {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                    $sectionmods[$thismod->modname]['count']++;
                } else {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
                    $sectionmods[$thismod->modname]['count'] = 1;
                }
                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);
                    if (
                        $completiondata->completionstate == COMPLETION_COMPLETE ||
                        $completiondata->completionstate == COMPLETION_COMPLETE_PASS
                    ) {
                        $complete++;
                    }
                }
            }
        }

        if (empty($sectionmods)) {
            // No sections.
            return '';
        }
        // Output section completion data.
        $templatecontext = [];
        if ($total > 0) {
            $completion = new stdClass;
            $completion->complete = $complete;
            $completion->total = $total;

            $percent = 0;
            if ($complete > 0) {
                $percent = (int) (($complete / $total) * 100);
            }

            $templatecontext['percent'] =  $percent;
        }

        return $templatecontext;

        // return $OUTPUT->render_from_template('theme_skilllab/course/progress', $templatecontext);
    }

    /**
     * Function to fetch the customfield data.
     * @param  int $courseid  Course ID
     * @return Custom field data.
     */
    public static function get_course_metadata($courseid) {
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $datas = $handler->get_instance_data($courseid);
        $metadata = [];
        foreach ($datas as $data) {
            if (empty($data->get_value())) {
                continue;
            }
            $metadata[$data->get_field()->get('shortname')] = $data->get_value();
        }
        return $metadata;
    }

    /**
     * Returns custom fields data for this course
     * @param int $course_id course id     
     * @param string $return_format "raw" or "key_value"     
     * @return \core_customfield\data_controller[]
     *  if (!isset($COURSE->customfields)) {
     *    $COURSE->customfields = \core_course\customfield\course_handler::create()->get_instance_data($COURSE->id);
     * }
     */
    public static function get_custom_field_metadata($course_id, $return_format = 'raw') {
        $handler = \core_course\customfield\course_handler::create();
        // $customfields = $handler->get_instance_data($course_id);
        $customfields = $handler->export_instance_data($course_id);
        $metadata = [];

        foreach ($customfields as $data) {
            if ($return_format == 'key_value') {
                $metadata[$data->get_shortname()] = $data->get_value();
            } else {
                $metadata[] = [
                    'type' => $data->get_type(),
                    'value' => $data->get_value(),
                    'valueraw' => $data->get_data_controller()->get_value(),
                    'name' => $data->get_name(),
                    'shortname' => $data->get_shortname()
                ];
            }
        }
        return $metadata;
    }

    /**
     * @param int $course course id
     * @return array or boolen 
     */
    public static function get_course_info($course_id) {
        global $DB, $CFG, $USER;
        $courseinfo = [];

        if ($DB->record_exists('course', array('id' => $course_id))) {
            $course = $DB->get_record('course', ['id' => $course_id]);
            $context = \context_course::instance($course->id, IGNORE_MISSING);
            // $course_categories = $DB->get_record('course_categories', ['id' => $course->category]);
            // course custom field data

            try {
                $numsections = (int)$DB->get_field_sql('SELECT max(section) from {course_sections} WHERE course = ?', [$course->id]);
            } catch (\Throwable $th) {
                $numsections = get_config('moodlecourse ')->numsections;
            }

            // get course enrolment plugin instance.
            $enrollment_methods_info =   $enrollment_methods = [];
            $index = 0;
            $enrolinstances = enrol_get_instances((int)$course->id, true);
            foreach ($enrolinstances as $key => $courseenrolinstance) {
                $enrollment_methods[] = $courseenrolinstance->enrol;
                $enrollment_methods_info[$index]['enrol'] = $courseenrolinstance->enrol;
                $enrollment_methods_info[$index]['name'] = ($courseenrolinstance->name) ?: $courseenrolinstance->enrol;
                $enrollment_methods_info[$index]['cost'] = $courseenrolinstance->cost;
                $enrollment_methods_info[$index]['currency'] = $courseenrolinstance->currency;
                $enrollment_methods_info[$index]['roleid'] = $courseenrolinstance->roleid;
                $enrollment_methods_info[$index]['role_name'] = '';
                $index++;
            }

            // data arrange to return
            $courseinfo['id'] = $course->id;
            $courseinfo['categoryid'] = $course->category;
            $courseinfo['shortname'] =  format_string($course->shortname);
            $courseinfo['fullname'] =  format_string($course->fullname);
            // $courseinfo['category_name'] = $course_categories->name;
            $courseinfo['course_link'] = (new moodle_url('/course/view.php', ['id' => $course->id]))->out();
            $courseinfo['enrollment_link'] = (new \moodle_url('/enrol/index.php', array('id' => $course->id)))->out();
            $courseinfo['participant_link'] = (new moodle_url('/user/index.php', array('id' => $course->id)))->out();
            $courseinfo['thumbnail_image_link'] = format_string(self::get_course_image($course));
            $courseinfo['summary'] = self::get_course_formatted_summary($course);
            $courseinfo['course_sortorder'] = $course->sortorder;
            $courseinfo['course_total_sections'] = $numsections + 1;
            $courseinfo['course_visible'] = $course->visible;
            $courseinfo['course_startdate'] = $course->startdate;
            $courseinfo['course_enddate'] = $course->enddate;
            $courseinfo['course_timecreated'] = $course->timecreated;
            $courseinfo['course_timemodified'] = $course->timemodified;
            $courseinfo['enrollment_methods'] = $enrollment_methods;
            $courseinfo['enrollment_methods_info'] = $enrollment_methods_info;
            $courseinfo['enroll_total_student'] = count_enrolled_users($context, 'moodle/course:isincompletionreports');
            $courseinfo['course_customfields'] = self::get_custom_field_metadata($course_id);

            $extra_metadata = self::get_custom_field_metadata($course_id, 'key_value');
            $courseinfo = [...$courseinfo, ...$extra_metadata]; //array_merge($courseinfo, $extra_metadata);

            return $courseinfo;
        }
        return false;
    }

    /**
     * @param int $per_page 
     * @param int $page_number 
     * @param string $search_course 
     * @param int $search_category_id 
     * @return array
     */
    public static function get_all_course_info(
        $per_page = 20,
        $page_number = 1,
        $search_course = '',
        $search_category_id = 0
    ) {
        global $DB;
        $all_courses_info = [];
        // 
        $limitfrom = 0;
        $limitnum = ($per_page > 0) ? $per_page : 0;
        if ($page_number > 0) {
            $limitfrom = $limitnum * $page_number;
        }
        // 
        $course_id = '';
        $sql_params = [
            'frontpagecourse_id' => 1,
            'visible_val' => 1
        ];
        $where_condition = [];
        $where_condition_apply = "WHERE course.id <> :frontpagecourse_id AND course.visible = :visible_val ";
        if ($search_course) {
            $sql_params['search_fullname'] = "%" . $search_course . "%";
            $sql_params['search_shortname'] = "%" . $search_course . "%";
            $where_condition[] = '( course.fullname LIKE :search_fullname || course.shortname LIKE :search_shortname )';
        }
        if ($course_id) {
            $sql_params['course_id'] = $course_id;
            $where_condition[] = 'course.id = :course_id';
        }
        if ($search_category_id) {
            $sql_params['search_category_id'] = $search_category_id;
            $where_condition[] = 'course.category = :search_category_id';
        }
        if (count($where_condition) > 0) {
            $where_condition_apply .= " AND " . implode(" AND ", $where_condition);
        }
        // 
        $sql_query = 'SELECT * FROM {course} course ' . $where_condition_apply . ' ORDER BY course.id DESC ';
        // 
        $records = $DB->get_records_sql($sql_query, $sql_params, $limitfrom, $limitnum);
        $total_records = $DB->get_records_sql($sql_query, $sql_params);

        //create return value
        $page_data_count = $limitfrom;
        foreach ($records as $record) {
            $page_data_count++;
            $record_info = UtilCourse_handler::get_course_info($record->id, true, false);
            $record_info['sn'] = $page_data_count;
            $all_courses_info['data'][] = $record_info;
        }
        // meta information
        $all_courses_info['meta'] = [
            'total_record' => count($total_records),
            'total_page' => ceil(count($total_records) / $per_page),
            'current_page' => $page_number,
            'per_page' => $per_page,
            'page_data_count' => $page_data_count
        ];
        // return data
        return $all_courses_info;
    }


    /**
     * 
     */
    public function getCourseDetails($courseId) {
        global $CFG, $COURSE, $USER, $DB, $SESSION, $SITE, $PAGE, $OUTPUT;
        if ($DB->record_exists('course', array('id' => $courseId))) {
            $courseDtl = new stdClass();
            $chelper = new coursecat_helper();
            $courseContext = \context_course::instance($courseId);

            $courseRecord = $DB->get_record('course', array('id' => $courseId));
            $courseElement = new core_course_list_element($courseRecord);

            $courseId = $courseRecord->id;
            $courseShortName = $courseRecord->shortname;
            $courseFullName = $courseRecord->fullname;
            $courseSummary = $chelper->get_course_formatted_summary($courseElement, array('noclean' => true, 'para' => false));
            $courseFormat = $courseRecord->format;
            $courseAnnouncements = $courseRecord->newsitems;
            $courseStartDate = $courseRecord->startdate;
            $courseEndDate = $courseRecord->enddate;
            $courseVisible = $courseRecord->visible;
            $courseCreated = $courseRecord->timecreated;
            $courseUpdated = $courseRecord->timemodified;
            $courseRequested = $courseRecord->requested;
            $courseEnrolmentCount = count_enrolled_users($courseContext);
            $courseDtlActivities = course_modinfo::get_array_of_activities(get_course($courseId));
            $ccnCountActivities = count($courseDtlActivities);
            $categoryId = $courseRecord->category;

            try {
                $courseCategory = core_course_category::get($categoryId);
                $categoryName = $courseCategory->get_formatted_name();
                $categoryUrl = $CFG->wwwroot . '/course/index.php?categoryid=' . $categoryId;
            } catch (Exception $e) {
                $courseCategory = "";
                $categoryName = "";
                $categoryUrl = "";
            }

            $enrolmentLink = $CFG->wwwroot . '/enrol/index.php?id=' . $courseId;
            $courseUrl = new moodle_url('/course/view.php', array('id' => $courseId));
            $courseparticipantUrl = new moodle_url('/user/index.php', array('id' => $courseId));


            $contentimages = self::get_course_image($courseRecord);

            $courseDtlSections = [];
            foreach ($courseDtlActivities as $courseSection) {
                if (empty($courseSection->deletioninprogress)) {
                    if (!isset($courseDtlSections[$courseSection->sectionid]['name'])) {
                        if (course_format_uses_sections($courseFormat)) {
                            $courseDtlSections[$courseSection->sectionid]['name'] = get_section_name($courseId, $courseSection);
                        } else {
                            $courseDtlSections[$courseSection->sectionid]['name'] = $courseFullName;
                        }
                    }
                    $courseDtlSections[$courseSection->sectionid][] = $courseSection;
                }
            }
            $countSections = count($courseDtlSections) - 1;


            // Get all enrolled students
            // 'moodle/course:isincompletionreports' - this capability is allowed to only students.
            $enrolledlearners = get_enrolled_users($courseContext, 'moodle/course:isincompletionreports');
            $count_course_completion = 0;
            // Get completion info object to get course completion.
            $completioninfo = new \completion_info($courseRecord);
            //  new completion_info($courseId);
            // If completion is not enable then continue.
            if ($completioninfo->is_enabled()) {
                // For each learners get completions.
                foreach ($enrolledlearners as $user) {
                    // Get progress percentage from a course.
                    $percentage = progress::get_course_progress_percentage($courseRecord, $user->id);
                    if (!is_null($percentage)) {
                        $percentage = floor($percentage);
                    }
                    if ($percentage == 100) {
                        $count_course_completion++;
                    }
                }
            }

            // var_dump($count_course_completion);

            $count_active_users = 0;
            foreach ($enrolledlearners as $user) {
                $user_lastaccess_course = $DB->get_record('user_lastaccess', array('userid' => $user->id, 'courseid' => $courseId));
                if ($user_lastaccess_course) {
                    $count_active_users++;
                }
            }


            /* Map data */
            $courseDtl->courseId = $courseId;
            $courseDtl->enrolments = $courseEnrolmentCount;
            $courseDtl->categoryId = $categoryId;
            $courseDtl->categoryName = $categoryName;
            $courseDtl->categoryUrl = $categoryUrl;
            $courseDtl->shortName = $courseShortName;
            $courseDtl->fullName = format_text($courseFullName, FORMAT_HTML, array('filter' => true));
            $courseDtl->summary = $courseSummary;
            $courseDtl->imageUrl = $contentimages;
            $courseDtl->format = $courseFormat;
            $courseDtl->announcements = $courseAnnouncements;
            $courseDtl->numberOfSections = $countSections;
            $courseDtl->sections = $courseDtlSections;
            $courseDtl->numberOfActivities = $ccnCountActivities;
            $courseDtl->activities = $courseDtlActivities;
            $courseDtl->startDate = userdate($courseStartDate, get_string('strftimedatefullshort', 'langconfig'));
            $courseDtl->endDate = userdate($courseEndDate, get_string('strftimedatefullshort', 'langconfig'));
            $courseDtl->visible = $courseVisible;
            $courseDtl->created = userdate($courseCreated, get_string('strftimedatefullshort', 'langconfig'));
            $courseDtl->updated = userdate($courseUpdated, get_string('strftimedatefullshort', 'langconfig'));
            $courseDtl->requested = $courseRequested;
            $courseDtl->enrolmentLink = $enrolmentLink;
            $courseDtl->url = $courseUrl;
            $courseDtl->participantUrl = $courseparticipantUrl;
            $courseDtl->count_course_completion = $count_course_completion;
            $courseDtl->count_active_users = $count_active_users;

            return $courseDtl;
        }
        return null;
    }
}
