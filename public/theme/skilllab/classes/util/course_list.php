<?php

/**
 * @package   theme_skilllab   
 * @copyright 2023 yipl skill lab csc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\util;

use core\context_helper;
use DateTime;
use html_writer;
use moodle_url;
use paging_bar;
use stdClass;

defined('MOODLE_INTERNAL') || die;

class course_list
{
    // get Url Parameters
    protected $courseid;
    protected $categoryid;
    protected $search_course;
    protected $created_date_from;
    protected $created_date_to;
    protected $search_skill;
    protected $per_page = 10;
    protected $page_number;
    protected $course_type;

    // capability variables
    protected $site_admin;
    protected $course_create;
    protected $instructor_view;
    protected $instructor_editor;
    protected $current_student_user;

    /**
     * Class constructor
     */
    public function __construct($course_type = 'Course')
    {
        global $USER;

        $this->course_type = $course_type;
        // get Url Parameters
        $this->courseid = optional_param('courseid', 0, PARAM_INT); // course id
        $this->categoryid = optional_param_array('categoryids', false, PARAM_INT);
        if (!$this->categoryid) {
            $single_categoryid = optional_param('categoryid', 0, PARAM_INT); // Category id
            if ($single_categoryid) {
                $this->categoryid = [$single_categoryid];
            }
        }
        $this->search_course = optional_param('search', '', PARAM_RAW); // search course
        $this->created_date_from = optional_param('created_date_from', '', PARAM_RAW); //created date from
        $this->created_date_to = optional_param('created_date_to', '', PARAM_RAW); //created date to
        $this->search_skill = optional_param_array('skill', false, PARAM_RAW);
        $this->page_number = optional_param('page', 0, PARAM_INT); // page number
        // get systaem level users capability
        $systemcontext = \context_system::instance();
        $this->site_admin = has_capability('moodle/site:config', $systemcontext, $USER->id);
        $this->course_create = has_capability('moodle/course:create', $systemcontext, $USER->id);
        $this->instructor_view = has_capability('theme/skilllab:instructor_view', $systemcontext, $USER->id);
        $this->instructor_editor = has_capability('theme/skilllab:instructor_editor', $systemcontext, $USER->id);
        // $only_student = has_capability('moodle/course:isincompletionreports', $context_course, $USER->id);
        $course_list_per_page = theme_skilllab_get_setting('course_list_per_page');
        $this->per_page = ($course_list_per_page > 0) ? $course_list_per_page : $this->per_page;
    }

    /**
     * define table columns header
     */
    protected function define_table_headers()
    {
        // define common table header values
        $table_columns = ['id', 'shortname', 'skill_level'];
        if ($this->course_type === 'Course') {
            $table_headers = ['S.N', 'Course Name', 'Skill level'];
        } elseif ($this->course_type === 'Scholarship') {
            $table_headers = ['S.N', 'Scholarship Name', 'Skill level'];
        } elseif ($this->course_type === 'Career road map') {
            $table_headers = ['S.N', 'Career Road Map Name', 'Skill level'];
        } else {
            $table_headers = ['S.N', 'Course Name', 'Skill level'];
        }
        // define course creator or admin table header values
        if ($this->course_create) {
            $table_columns = array_merge($table_columns, ['no_of_learner', 'timecreated', 'action']);
            $table_headers = array_merge($table_headers, ['No of learner', 'Created Date', 'Action']);
        } else if (!$this->instructor_view && !$this->instructor_editor) {
            // define student table values
            $table_columns = array_merge($table_columns, ['enrolled_date', 'progress_status', 'action']);
            $table_headers = array_merge($table_headers, ['Enrolled Date', 'Status', 'Action']);
        }


        // define which table columns to sort
        $sort_columns = ['shortname', 'skill_level', 'timecreated', 'enrolled_date', 'no_of_learner', 'progress_status'];

        return array($table_headers, $table_columns, $sort_columns);
    }

    /**
     * DB query
     * return course object records
     */
    protected function skl_get_courses_records($table_sort, $limitfrom, $limitnum)
    {
        global $DB;

        // convert into date time
        $timecreated_timestamp_from = strtotime($this->created_date_from);
        $timecreated_timestamp_to = strtotime($this->created_date_to);

        $extra_sql = '';
        if ($this->course_type) {
            $customfield_field = $DB->get_record('customfield_field', ['shortname' => 'course_type', 'type' => 'select']);
            // $customfield_data = $DB->get_record('customfield_data', ['fieldid' => '']);
            if ($customfield_field) {
                $configdata = $customfield_field->configdata;
                $options = json_decode($configdata)->options;
                $options = explode("\r\n", $options);
                $position = array_search($this->course_type, $options);
                $value_position = '';
                if ($position !== false) {
                    $value_position = $position + 1;
                }
                if ($value_position) {
                    $extra_sql = 'JOIN {customfield_data} customfield_data ON customfield_data.instanceid = course.id AND customfield_data.fieldid = ' . $customfield_field->id . ' AND customfield_data.value = ' . $value_position . '';
                }
            }
        }

        // generate sql query
        $sql_query = 'SELECT course.id as id , skl_course_role_count.count as no_of_learner
        FROM {course} course 
        LEFT JOIN {skl_custom_course_field} skl_custom_course_field ON skl_custom_course_field.courseid = course.id
        LEFT JOIN {skl_course_role_count} skl_course_role_count ON skl_course_role_count.courseid = course.id AND  skl_course_role_count.role = 5 
        ' . $extra_sql . '
        WHERE course.id <> :frontpagecourse_id ';
        //    all other where conditions data
        if ($this->courseid) {
            $sql_query = $sql_query . 'AND course.id = ' . $this->courseid . ' ';
        }
        if ($this->categoryid and is_array($this->categoryid)) {
            $categoryids = implode(',', $this->categoryid);
            if ($categoryids) {
                $sql_query = $sql_query . 'AND course.category IN ( ' . $categoryids . ') ';
            }
        }
        if ($this->search_course) {
            $sql_query = $sql_query . 'AND course.fullname LIKE \'%' . trim($this->search_course) . '%\' ';
        }
        if ($timecreated_timestamp_from) {
            $sql_query = $sql_query . 'AND course.timecreated >= ' . $timecreated_timestamp_from . ' ';
        }
        if ($timecreated_timestamp_to) {
            // $timecreated_timestamp_to = strtotime('+1 day', $timecreated_timestamp_to);
            $timecreated_timestamp_to = $timecreated_timestamp_to + 24 * 3600;
            $sql_query = $sql_query . 'AND course.timecreated <= ' . $timecreated_timestamp_to . ' ';
        }
        if ($this->search_skill) {
            if (count($this->search_skill) < count($this->get_skill_level())) {
                if (count($this->search_skill) > 1) {
                    $skill_level_query = '';
                    foreach ($this->search_skill as $key => $value) {
                        if ($key == 0) {
                            $skill_level_query = " skl_custom_course_field.skill_level LIKE '%" . trim($value) . "%' ";
                        } else {
                            $skill_level_query =  $skill_level_query . "OR skl_custom_course_field.skill_level LIKE '%" . trim($value) . "%' ";
                        }
                    }
                    $sql_query = $sql_query . " AND ( " .  $skill_level_query . " ) ";
                } else {
                    foreach ($this->search_skill as $key => $value) {
                        $sql_query = $sql_query . " AND  skl_custom_course_field.skill_level LIKE '%" . trim($value) . "%' ";
                    }
                }
            }
        }
        // sorting data
        $sql_query = $sql_query . 'ORDER BY  ';
        if ($table_sort) {
            $sql_query = $sql_query . $table_sort . ', ';
        }
        $sql_query = $sql_query . 'course.id DESC ';
        // sql parameters
        $sql_params = [
            'frontpagecourse_id' => 1
        ];
        // execute sql query
        $records = $DB->get_records_sql($sql_query, $sql_params, $limitfrom = $limitfrom, $limitnum = $limitnum);

        return $records;
    }

    /**
     * DB query
     * returens user enrolled coures object records
     */
    protected function skl_enrol_get_users_courses($userid, $onlyactive = false, $sort = null, $limitfrom = 0, $limitnum = 0)
    {
        global $DB;

        // Re-Arrange the course sorting according to the admin settings.
        $sort = enrol_get_courses_sortingsql($sort);

        // Guest account does not have any courses
        if (isguestuser($userid) or empty($userid)) {
            return (array());
        }

        $orderby = "";
        $sort = trim($sort);
        if (!empty($sort)) {
            $rawsorts = explode(',', $sort);
            $sorts = array();
            foreach ($rawsorts as $rawsort) {
                $rawsort = trim($rawsort);
                if ($rawsort) {
                    if (strpos($rawsort, 'c.') === 0) {
                        $rawsort = substr($rawsort, 2);
                    }
                    $rawsort = trim($rawsort);
                    if (str_contains($rawsort, 'skill_level')) {
                        $rawsort = 'skl_custom_course_field.' . $rawsort;
                    } else if (str_contains($rawsort, 'enrolled_date')) {
                        $rawsort = 'en.' . $rawsort;
                    } else if (str_contains($rawsort, 'progress_status')) {
                        $rawsort = 'ucp.' . $rawsort;
                    } else {
                        $rawsort = 'c.' . $rawsort;
                    }
                    $sorts[] = $rawsort;
                }
            }
            $sort = implode(', ', $sorts);
            $orderby = "ORDER BY $sort , en.enrolled_date DESC";
        }

        $params = [];

        if ($onlyactive) {
            $subwhere = "WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)";
            $params['now1'] = round(time(), -2); // improves db caching
            $params['now2'] = $params['now1'];
            $params['active'] = ENROL_USER_ACTIVE;
            $params['enabled'] = ENROL_INSTANCE_ENABLED;
        } else {
            $subwhere = "";
        }

        $course_basefields = array(
            'id',
            'category',
            'sortorder',
            'shortname',
            'fullname',
            'idnumber',
            'startdate',
            'visible',
            'defaultgroupingid',
            'groupmode',
            'groupmodeforce'
        );
        $coursefields = 'c.*';
        $cc_select = ', ' . \core\context_helper::get_preload_record_columns_sql('ctx');
        $cc_join = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $params['contextlevel'] = CONTEXT_COURSE;

        //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
        $sql = "SELECT $coursefields $cc_select , en.enrolled_date, en.userid
                  FROM {course} c
                  JOIN (
                    SELECT DISTINCT e.courseid, ue.timecreated AS enrolled_date, ue.userid AS userid
                        FROM {enrol} e
                        JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                        $subwhere
                    ) en ON (en.courseid = c.id)
               $cc_join
                 WHERE c.id <> " . SITEID . "
              $orderby";
        $params['userid'] = $userid;

        $courses = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

        // preload contexts and check visibility
        if ($onlyactive) {
            foreach ($courses as $id => $course) {
                context_helper::preload_from_record($course);
                if (!$course->visible) {
                    if (!$context = \context_course::instance($id)) {
                        unset($courses[$id]);
                        continue;
                    }
                    if (!has_capability('moodle/course:viewhiddencourses', $context, $userid)) {
                        unset($courses[$id]);
                        continue;
                    }
                }
            }
        }

        return $courses;
    }

    /**
     * course filter section
     * return output template html 
     */
    protected function skl_course_list_filter()
    {
        global $OUTPUT, $DB, $CFG;

        $course_categories = $DB->get_records('course_categories', []);

        if ($this->courseid) {
            $course = $DB->get_record('course', ['id' => $this->courseid]);
            $this->search_course = $course->fullname;
        }

        // data arrange for template content
        $template_content = [];
        $template_content['search_course'] = $this->search_course;
        $template_content['created_date_from'] = $this->created_date_from;
        $template_content['created_date_to'] = $this->created_date_to;

        $i = 0;
        foreach ($course_categories as $key => $category) {
            $template_content['categories'][$i]['id'] = $category->id;
            $template_content['categories'][$i]['name'] = $category->name;
            $template_content['categories'][$i]['category_filter'] = '/course/index.php?categoryid=' . $category->id;
            if ($this->categoryid and is_array($this->categoryid)) {
                foreach ($this->categoryid as $key => $filter_cat_id) {
                    if ($filter_cat_id == $category->id) {
                        $template_content['categories'][$i]['category_checked'] = 'checked';
                    }
                }
            }
            $i++;
        }
        if ($this->site_admin or $this->course_create) {
            $template_content['course_create'] = true;
        }

        $skill_level = $this->get_skill_level();
        $i = 0;
        foreach ($skill_level as $key => $value) {
            $template_content['skill_level'][$i]['value'] = $value;
            if ($this->search_skill and is_array($this->search_skill)) {
                foreach ($this->search_skill as $key => $search_value) {
                    if ($search_value == $value) {
                        $template_content['skill_level'][$i]['skill_checked'] = 'checked';
                    }
                }
            }

            $i++;
        }
        if ($this->categoryid || $this->search_course || $this->search_skill || $this->created_date_from || $this->created_date_to) {
            $template_content['Clear_filter'] = true;
        }
        $template_content['action_url'] = $CFG->wwwroot . '/' . str_replace(" ", "_", strtolower($this->course_type));
        $template_content['course_type'] = strtolower($this->course_type);
        $template = 'theme_skilllab/skilllab_courses/course_filter_section';
        $filter_section = $OUTPUT->render_from_template($template, $template_content);

        return $filter_section;
    }

    /**
     * this is from skill lab theme 
     * to change the course list in /course/index.php page
     * and all the category and sub-category pages
     */
    protected function skl_course_list_table()
    {
        global $CFG, $DB, $USER, $OUTPUT, $PAGE;
        require_once($CFG->libdir . '/tablelib.php');
        require_once($CFG->dirroot . '/theme/skilllab/lib.php');

        // get extra Url Parameters
        $table_only = optional_param('table_only', false, PARAM_BOOL);
        $ssort = optional_param('ssort', '', PARAM_RAW); //
        $s_order = optional_param('sorder', '', PARAM_RAW); //

        // define page url if table_only is true
        $page_url = $CFG->wwwroot . $_SERVER['REQUEST_URI'];
        if ($table_only) {
            $page_url = explode('table_only=true', $page_url);
            $page_url = $page_url[0] . $page_url[1];
        }

        // define for pagination and get per page data
        $per_page_data = $this->per_page;
        $limitfrom = 0;
        $limitnum = $per_page_data;
        if ($this->page_number > 0) {
            $limitfrom = $limitnum * $this->page_number;
        }

        // get define table headers and columns
        list($table_headers, $table_columns, $sort_columns) = $this->define_table_headers();

        // table data sort order
        $table_sort = '';
        if ($s_order == '') {
            $s_order = 'ASC';
        } else if ($s_order == 'ASC') {
            $s_order = 'DESC';
        } else if ($s_order == 'DESC') {
            $s_order = 'ASC';
        }
        if ($ssort) {
            $table_sort = $ssort . ' ' . $s_order;
        }

        // get datas
        $all_records =  $all_records = [];
        if ($this->course_create) {
            $all_records = $this->skl_get_courses_records($table_sort, 0, 0);
            $records = $this->skl_get_courses_records($table_sort, $limitfrom, $limitnum);
        } else if (!$this->instructor_view && !$this->instructor_editor) {
            // return "";
            $all_records = $this->skl_enrol_get_users_courses($USER->id, false, $table_sort);
            $records = $this->skl_enrol_get_users_courses(
                $USER->id,
                $onlyactive = false,
                $sort = $table_sort,
                $limitfrom,
                $limitnum
            );
        }

        // re-arrange the data to output in template 
        $table_datas = [];
        if ($records) {
            $i = $limitfrom + 1;
            foreach ($records as $record) {
                $courseInfo = get_course_info($record->id);
                $context_course = \context_course::instance($record->id);
                // $only_students = get_enrolled_users($context_course, 'moodle/course:isincompletionreports');
                $count_students = count_enrolled_users($context_course, 'moodle/course:isincompletionreports');

                $table_row = [
                    $i,
                    [
                        'course_image' => UtilCourse_handler::get_course_image($record, true),
                        'fullname' => $courseInfo['fullname'],
                        'category_name' => $courseInfo['category_name'],
                        'course_url' => $courseInfo['course_url']
                    ],
                    $courseInfo['skill_level']
                ];

                if ($this->course_create) {
                    $row_2 = [
                        ($count_students == $record->no_of_learner) ? $record->no_of_learner : $count_students,
                        UtilTheme_handler::skl_get_user_date_time($courseInfo['course_timecreated']),
                        [
                            'action_id' => $record->id,
                            'sessKey' => sessKey()
                        ]
                    ];
                    $table_row = array_merge($table_row, $row_2);
                } else if (!$this->instructor_view && !$this->instructor_editor) {
                    \theme_skilllab\local\skl_user_course_progress::update_user_course_progress($record, $USER->id);
                    $user_progress_status = UtilUser_handler::get_user_course_progress($record, $USER->id);
                    $row_2 = [
                        UtilTheme_handler::skl_get_user_date_time($record->enrolled_date),
                        $user_progress_status,
                        ($user_progress_status == 100) ? [
                            'status' => true,
                            'title' => 'Get Certificate',
                            'url' => UtilCourse_handler::course_mod_customcert($record->id)['certificate_url']
                        ] : [
                            'status' => false,
                            'title' => 'Resume',
                            'url' => $courseInfo['course_url']
                        ]
                    ];
                    $table_row = array_merge($table_row, $row_2);
                }


                foreach ($table_columns as $key => $value) {
                    $each_table_row[$value] = $table_row[$key];
                }

                $table_datas[] = $each_table_row;
                $i = $i + 1;
            }
        } else {
            $no_data['status'] = true;
            // $no_data['message'] = 'No data available';
        }

        foreach ($table_columns as $key => $value) {
            $header[$key]['name'] = $table_headers[$key];
            if (in_array($table_columns[$key], $sort_columns)) {
                $header[$key]['sort_by'] = $table_columns[$key];
                $header[$key]['sort_order'] = $s_order;
                $header[$key]['sort_url'] = new moodle_url('/' . str_replace(" ", "_", strtolower($this->course_type)) . '/', [
                    'ssort' => $table_columns[$key],
                    'sorder' => $s_order,
                    'page' => $this->page_number
                ]);
                // . '?ssort=' . $table_columns[$key] . '&sorder=' . $s_order;
                $header[$key]['sort'] = true;
            } else {
                $header[$key]['sort'] = false;
            }
        }

        // define template context data values
        $template_content = [
            'table_headers' => $header,
            'table_datas' => $table_datas,
            'course_create' => ($this->course_create) ? true : false,
            'instructor_view' => ($this->instructor_view) ? true : false,
            'no_data' => (isset($no_data)) ? $no_data : ''
        ];
        $template_content['course_type'] = strtolower($this->course_type);
        $template_content['returnurl'] = '/' . str_replace(" ", "_", strtolower($this->course_type));

        // get output content from template
        $output = '';
        $output .= html_writer::start_tag('div', array('class' => 'course-list', 'id' => 'course-list-table'));
        $output .= $this->get_filter_search_section();

        $template = 'theme_skilllab/skilllab_courses/courses_list_table';
        $output .= $OUTPUT->render_from_template($template, $template_content);

        $output .= $OUTPUT->paging_bar(count($all_records), $this->page_number, $perpage = $limitnum, $page_url);

        $output .= html_writer::end_tag('div');

        // $pagination_bar = new \theme_skilllab\util\pagination_bar(count($all_records), $this->page_number, $perpage = $limitnum, $page_url);
        // $output .= $pagination_bar->out();
        if ($table_only) {
            echo $output;
            exit();
        }

        return $output;
    }


    /**
     * return final output
     */
    public function skl_course_list_out()
    {
        $output = '';
        $output .= html_writer::start_tag('div', array('class' => 'course-list-content', 'id' => 'skl-course-list'));
        $output .= $this->skl_course_list_filter();
        $output .= $this->skl_course_list_table();
        $output .= html_writer::end_tag('div');

        return $output;
    }


    protected function get_filter_search_section()
    {
        global  $OUTPUT, $DB;

        // data arrange for template content
        $template_content = [];
        $template_content['created_date_from'] = $this->created_date_from;
        $template_content['created_date_to'] = $this->created_date_to;
        $template_content['search_categories'] = [];
        $template_content['search_skilllevel'] = [];
        $template_content['search_course'] = $this->search_course;

        if ($this->categoryid and is_array($this->categoryid)) {
            $i = 0;
            $course_categories = $DB->get_records('course_categories', []);
            foreach ($course_categories as $key => $category) {
                foreach ($this->categoryid as $key => $filter_cat_id) {
                    if ($filter_cat_id == $category->id) {
                        $template_content['search_categories_filter'] = true;
                        $template_content['search_categories'][$i]['name'] = $category->name;
                        $template_content['search_categories'][$i]['id'] = $category->id;
                        $i++;
                    }
                }
            }
        }

        if ($this->search_skill and is_array($this->search_skill)) {
            $skill_level = $this->get_skill_level();
            $i = 0;
            foreach ($skill_level as $key => $value) {
                foreach ($this->search_skill as $key => $search_value) {
                    if ($search_value == $value) {
                        $template_content['search_skilllevel_filter'] = true;
                        $template_content['search_skilllevel'][$i]['name'] = $search_value;
                        $i++;
                    }
                }
            }
        }

        if ($this->search_course || $this->categoryid ||  $this->search_skill || $this->created_date_from || $this->created_date_to) {
            $template_content['filter_done'] = true;
        }

        $output = $OUTPUT->render_from_template('theme_skilllab/skilllab_courses/filter_info', $template_content);
        return $output;
    }



    protected function get_skill_level()
    {
        global $DB;
        $skill_level = [];
        $skill_level_configdata = $DB->get_record('customfield_field', ['shortname' => 'skill_level'], 'configdata');
        if ($skill_level_configdata->configdata) {
            $skill_level = json_decode($skill_level_configdata->configdata)->options;
            $skill_level = explode("\r\n", $skill_level);
        }
        return $skill_level;
    }

    // 
}
