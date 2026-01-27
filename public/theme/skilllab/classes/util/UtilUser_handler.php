<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * user information.
 * @package   theme_skilllab   
 * @copyright 2024 YIPL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\util;

use completion_info;
use core_completion\progress;
use core_tag_tag;
use moodle_url;
use MoodleExcelWorkbook;
use stdClass;
use theme_skilllab\local\skl_time_track;

defined('MOODLE_INTERNAL') || die;

class UtilUser_handler {

    /**
     * Returns List of courses where the user is enrolled
     *
     * @param \stdClass $user
     *
     * @return array
     */
    public static function user_enrolled_courses($user)
    {
        // global $USER, $CFG, $DB;
        // if (($USER->id !== $user->id) && !is_siteadmin($USER->id)) {
        //     return [];
        // }
        // require_once($CFG->dirroot . '/course/renderer.php');
        $enrolledcourses = [];
        if ($mycourses =  enrol_get_users_courses($user->id, true, '*', 'visible DESC, fullname ASC, sortorder ASC')) {
            foreach ($mycourses as $mycourse) {
                if ($mycourse->category) {
                    $coursecontext = \context_course::instance($mycourse->id);
                    $percentage = self::get_user_course_progress($mycourse, $user->id);

                    $enrolledcourse = [
                        "id" => $mycourse->id,
                        "fullname" => format_string($mycourse->fullname, true, array('context' => $coursecontext)),
                        "shortname" => format_string($mycourse->shortname, true, array('context' => $coursecontext)),
                        'course_link' => (new \moodle_url('/course/view.php', array('id' => $mycourse->id)))->out(),
                        "progress" => $percentage,
                        "completion_date" => ($percentage == 100) ? time() : "",
                        "enroll_date" => self::course_user_enrolments($mycourse->id, $user->id)->timecreated,
                        "cost" => "",
                        "currency" => "",
                        "course_user_roles" => "",
                    ];
                    // 
                    $enrolinstances = enrol_get_instances((int)$mycourse->id, true);
                    foreach ($enrolinstances as $key => $courseenrolinstance) {
                        // if ($courseenrolinstance->enrol == 'fee' && $courseenrolinstance->roleid == '5') {
                        if ($courseenrolinstance->enrol == 'fee') {
                            $enrolledcourse['cost'] = $courseenrolinstance->cost;
                            $enrolledcourse['currency'] = $courseenrolinstance->currency;
                        }
                    }
                    $course_user_roles = [];
                    $roles = get_user_roles($coursecontext, $user->id);
                    if ($roles && is_array($roles)) {
                        $count = 0;
                        foreach ($roles as $key => $role) {
                            $course_user_roles[$count]['id'] = $role->id;
                            $course_user_roles[$count]['shortname'] = $role->shortname;
                            $course_user_roles[$count]['name'] = ($role->name) ?: $role->shortname;
                            $count++;
                        }
                    }
                    $enrolledcourse['course_user_roles'] = $course_user_roles;
                    $enrolledcourses[] = $enrolledcourse;
                }
            }
        }
        return $enrolledcourses;
        // 
    }

    /**
     * unenroll_all_course_users
     */
    protected static function unenroll_all_course_users() {
        global $DB;
        try {
            $query = 'SELECT * from {course} course WHERE course.id <> :frontpagecourse_id ';
            $sql_params = [
                'frontpagecourse_id' => 1
            ];
            $courses = $DB->get_records_sql($query, $sql_params);
            foreach ($courses as $key => $course) {
                $enrol = enrol_get_plugin('manual');
                $instance = new stdClass();
                $enrol_instances = enrol_get_instances($course->id, true);
                foreach ($enrol_instances as $course_enrol_instance) {
                    if ($course_enrol_instance->enrol == "manual") {
                        $instance = $course_enrol_instance;
                        break;
                    }
                }
                $course_context = \context_course::instance($course->id);
                $enrolled_users = get_enrolled_users($course_context);
                foreach ($enrolled_users as $key => $user) {
                    $unenroll_user = $enrol->unenrol_user($instance, $user->id);
                }
            }
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }

    /**
     * return human readable date time
     */
    public static function get_user_date_time($timestamp, $format = '%b %d, %Y') {
        // '%A, %b %d, %Y, %I:%M %p'
        $date = new \DateTime();
        $date->setTimestamp(intval($timestamp));
        $user_date_time = userdate($date->getTimestamp(), $format);
        return $user_date_time;
    }

    /**
     * return user progress in the course
     * @param stdClass $course course      
     * @param int $enrolled_user_id id of enrolled user in course
     * @return int $percentage user course progress percentage
     */
    public static function get_user_course_progress($course, $enrolled_user_id) {
        global $CFG;

        require_once("$CFG->libdir/completionlib.php");

        $completioninfo = new \completion_info($course);
        $percentage = 0;
        if ($completioninfo->is_enabled()) {
            $percentage = progress::get_course_progress_percentage($course, $enrolled_user_id);
            if (!is_null($percentage)) {
                $percentage =  (int)($percentage);
                return $percentage;
            }
        }
        return 0;
    }

    /**
     * return user course enrollment infomation
     * @param int $course_id course id     
     * @param int $enrolled_user_id id of enrolled user in course
     * @return object $userenrolments user course enrollment
     */
    public static function course_user_enrolments($course_id, $enrolled_user_id) {
        global $DB;
        $query = 'SELECT user_enrolments.status, user_enrolments.timecreated ,enrol.enrol
            FROM {user_enrolments} user_enrolments 
            LEFT JOIN {enrol} enrol ON user_enrolments.enrolid = enrol.id
            WHERE enrol.courseid = :courseid AND user_enrolments.userid = :userid
            ';
        $params = [
            'courseid' => $course_id,
            'userid' => $enrolled_user_id
        ];
        $userenrolments = $DB->get_records_sql($query, $params);
        return $userenrolments;
    }

    /**
     * User Profile image
     * @param \stdClass $user user object
     * @return url
     */
    public static function get_user_profile_image_url($user) {
        global $PAGE;
        $userpicture = new \user_picture($user);
        $userpicture->size = 1; // Size f1.
        // $userpicture->size = 0; // Size f2. profileimageurlsmall
        $profileimageurl = $userpicture->get_url($PAGE)->out(false);
        return $profileimageurl;
    }

    /**
     * @param \stdClass $user user object
     * @return url
     */
    public static function get_user_description($user) {
        global $CFG;

        $usercontext = \context_user::instance($user->id, MUST_EXIST);
        require_once("$CFG->libdir/filelib.php");
        $description = file_rewrite_pluginfile_urls(
            $user->description,
            'pluginfile.php',
            $usercontext->id,
            'user',
            'profile',
            null
        );
        $description = format_text($description, $user->descriptionformat);
        // \core_external\util::format_text(
        //     $user->description,
        //     $user->descriptionformat,
        //     $usercontext,
        //     'user',
        //     'profile',
        //     null
        // );
        return $description;
    }

    /**
     * User custom fields
     * @param \stdClass $user user object
     * @return url
     */
    public static function get_user_customofields($user) {
        // global $CFG;
        // require_once($CFG->dirroot . "/user/profile/lib.php"); // Custom field library. user_get_user_details
        // $categories = profile_get_user_fields_with_data_by_category($user->id);
        // $user_customfields = array();
        // foreach ($categories as $categoryid => $fields) {
        //     foreach ($fields as $formfield) {
        //         if ($formfield->data()) {
        //             $user_customfields[] = [
        //                 'name' => $formfield->display_name(),
        //                 'value' => $formfield->data,
        //                 'displayvalue' => $formfield->display_data(),
        //                 'type' => $formfield->field->datatype,
        //                 'shortname' => $formfield->field->shortname
        //             ];
        //         }
        //     }
        // }

        global $CFG;
        require_once($CFG->dirroot . "/user/profile/lib.php"); // Custom field library.

        $categories = profile_get_user_fields_with_data_by_category($user->id);
        $user_customfields = [];

        foreach ($categories as $categoryid => $fields) {
            foreach ($fields as $formfield) {
                if (!empty($formfield->data)) {
                    $user_customfields[] = [
                        'name' => $formfield->field->name, // Human-readable name
                        'value' => $formfield->data,       // Raw value
                        'displayvalue' => $formfield->display_data(), // Formatted value
                        'type' => $formfield->field->datatype,
                        'shortname' => $formfield->field->shortname
                    ];
                }
            }
        }


        return $user_customfields;
    }


    /**
     * User roles get_skl_user_roles
     */
    public static function get_user_roles($user_id = 0, $context = '', $return_param = "shortname") {
        global $PAGE;
        $role_data = [];

        if (empty($context)) {
            $context = $PAGE->context;
            // $context_sys = \context_system::instance();
        }
        if (is_siteadmin()) {
            $role_data[] = 'admin';
        }
        foreach (get_user_roles($context, $user_id) as $key => $role) {
            if ($return_param) {
                $role_data[] = $role->$return_param;
            } else {
                $role_data[] = $role;
            }
        }
        if (isloggedin() && !isguestuser()) {
            $role_data[] = 'auth_user';
        }

        return $role_data;
    }

    /**
     * 
     * get_user_info == user_get_user_details from /user/lib.php
     * @param int $user user id
     * @return array or boolen 
     */
    public static function get_user_info($user_id, $timestamp = true) {
        global $DB;
        $userinfo = [];

        if ($DB->record_exists('user', array('id' => $user_id))) {
            $user = $DB->get_record('user', ['id' => $user_id]);
            // default time zone
            $default_timezone = get_config('moodle', 'timezone');
            // users interests tags
            $interests_tags = '';
            $interests = core_tag_tag::get_item_tags_array('core', 'user', $user->id, core_tag_tag::BOTH_STANDARD_AND_NOT, 0, false);
            if ($interests) {
                $interests_tags = join(', ', $interests);
            }

            // // User preferences.
            // $preferences = array();
            // $userpreferences = get_user_preferences();
            // foreach ($userpreferences as $prefname => $prefvalue) {
            //     $preferences[] = array('name' => $prefname, 'value' => $prefvalue);
            // }


            // data arrange to return
            $userinfo['id'] = UtilTheme_handler::encrypt_decrypt_value($user->id, 'encrypt');
            $userinfo['id_raw'] = $user->id;
            $userinfo['username'] = $user->username;
            $userinfo['email'] = $user->email;
            $userinfo['firstname'] = $user->firstname;
            $userinfo['lastname'] = $user->lastname;
            $userinfo['auth'] = $user->auth;
            $userinfo['phone1'] = $user->phone1;
            $userinfo['phone2'] = $user->phone2;
            $userinfo['institution'] = $user->institution;
            $userinfo['department'] = $user->department;
            $userinfo['address'] = $user->address;
            $userinfo['city'] = $user->city;
            $userinfo['country'] = $user->country;
            $userinfo['country_name'] = ($user->country) ? get_string_manager()->get_list_of_countries()[$user->country] : '';
            $userinfo['lang'] = $user->lang;
            $userinfo['profileimageurl'] = self::get_user_profile_image_url($user);
            $userinfo['description'] = self::get_user_description($user);
            $userinfo['timezone'] = ($user->timezone == '99') ? $default_timezone : $user->timezone;
            $userinfo['timecreated'] = ($timestamp) ? $user->timecreated : self::get_user_date_time($user->timecreated);
            $userinfo['timemodified'] = ($timestamp) ? $user->timemodified : self::get_user_date_time($user->timemodified);
            $userinfo['firstaccess'] = ($timestamp) ? $user->firstaccess : self::get_user_date_time($user->firstaccess, '');
            $userinfo['lastaccess'] = ($timestamp) ? $user->lastaccess : self::get_user_date_time($user->lastaccess, '');
            $userinfo['lastlogin'] = ($timestamp) ? $user->lastlogin : self::get_user_date_time($user->lastlogin, '');
            $userinfo['profile_link'] = (new moodle_url('/user/profile.php', ['id' => $user->id]))->out();
            // $userinfo['preferences'] = $preferences;
            $userinfo['interests'] = $interests_tags;
            $userinfo['customofields'] = self::get_user_customofields($user);
            $userinfo['enrolled_courses'] = self::user_enrolled_courses($user);
            $userinfo['sys_roles'] = self::get_all_roles(\context_system::instance(), $user->id);

            return $userinfo;
        }
        return false;
    }

    /**
     * @param int $per_page 
     * @param int $page_number 
     * @param string $search_user 
     * @return array
     */
    public static function get_all_user_info(
        $per_page = 20,
        $page_number = 1,
        $search_user = '',
    ) {

        global $DB;
        $all_user_info = [];
        // 
        $limitfrom = 0;
        $limitnum = ($per_page > 0) ? $per_page : 0;
        if ($page_number > 0) {
            $limitfrom = $limitnum * $page_number;
        }
        // 
        $user_id = '';
        $sql_params = [
            'guest_user_id' => 1,
            'user_deleted' => 1,
            'user_suspended' => 1,
        ];
        $where_condition = [];
        $where_condition_apply = "WHERE user.id <> :guest_user_id && user.deleted <> :user_deleted && user.suspended <> :user_suspended";
        if ($search_user) {
            $sql_params['search_username'] = "%" . $search_user . "%";
            $sql_params['search_firstname'] = "%" . $search_user . "%";
            $sql_params['search_lastname'] = "%" . $search_user . "%";
            $sql_params['search_email'] = "%" . $search_user . "%";
            $where_condition[] = '( user.username LIKE :search_username || user.firstname LIKE :search_firstname || user.lastname LIKE :search_lastname || user.email LIKE :search_email )';
        }
        if ($user_id) {
            $sql_params['user_id'] = $user_id;
            $where_condition[] = 'user.id = :user_id';
        }
        if (count($where_condition) > 0) {
            $where_condition_apply .= " AND " . implode(" AND ", $where_condition);
        }
        // 
        $sql_query = 'SELECT * FROM {user} user ' . $where_condition_apply . ' ORDER BY user.id DESC ';
        // 
        $records = $DB->get_records_sql($sql_query, $sql_params, $limitfrom, $limitnum);
        $total_records = $DB->get_records_sql($sql_query, $sql_params);

        //create return value
        $page_data_count = $limitfrom;
        foreach ($records as $record) {
            $page_data_count++;
            $record_info = self::get_user_info($record->id, false);
            $record_info['sn'] = $page_data_count;
            $all_user_info['data'][] = $record_info;
        }
        // meta information
        $all_user_info['meta'] = [
            'total_record' => count($total_records),
            'total_page' => ceil(count($total_records) / $per_page),
            'current_page' => $page_number,
            'per_page' => $per_page,
            'page_data_count' => $page_data_count
        ];
        // return data
        return $all_user_info;
    }

    /**
     * User roles
     */
    public static function get_all_roles($context = '', $user_id = 0,  $return_param = "shortname") {
        global $PAGE;
        $role_data = [];
        if (!$user_id) {
            return;
        }
        if (empty($context)) {
            $context = $PAGE->context;
            // $context_sys = \context_system::instance();
        }
        if (is_siteadmin($user_id)) {
            $role_data[] = 'admin';
        }
        // else if (isloggedin() && !isguestuser()) {
        //     $role_data[] = 'auth_user';
        // }
        foreach (get_user_roles($context, $user_id) as $key => $role) {
            if ($return_param) {
                $role_data[] = $role->$return_param;
            } else {
                $role_data[] = $role;
            }
        }


        return $role_data;
    }
    // 

    /**
     * 
     */
    public static function get_user_institution_domain($user_id) {
        global $USER, $DB;
        $institution_domain = '';
        $user_info_field = $DB->get_record('user_info_field', ['shortname' => 'institution_domain'], 'id');
        if ($user_info_field) {
            $sql_query = 'SELECT * 
             FROM {user_info_data} user_info_data
             WHERE user_info_data.userid = :userid AND
             user_info_data.fieldid = :fieldid
         ';
            $sql_params = [
                'userid' => $user_id,
                'fieldid' => $user_info_field->id
            ];
            $user_info_data = $DB->get_record_sql($sql_query, $sql_params);
            if ($user_info_data->data) {
                $institution_domain = $user_info_data->data;
            }
        }
        return  $institution_domain;
    }


    /**
     * 
     */
    public static function get_student_course_report() {
        global $DB, $CFG;
        // define for pagination and get per page data
        $page_number = optional_param('page', 0, PARAM_INT); // page number
        $search_moodle_user_id = optional_param('search_moodle_user_id', '', PARAM_RAW);
        $search_user = optional_param('search_user', '', PARAM_TEXT);
        $search_course = optional_param('search_course', '', PARAM_TEXT);
        $download = optional_param('download', 0, PARAM_INT);
        // 
        $table_header = ['S.N', 'User ID', 'User Fullname & Email', 'User Institution', 'Course Name', 'Course Type', 'Course Progress %', 'User Time Spend'];
        // 
        $per_page_data = 30;
        $limitfrom = 0;
        $limitnum = $per_page_data;
        if ($page_number > 0) {
            $limitfrom = $limitnum * $page_number;
        }

        $sql_query = 'SELECT CONCAT(user.id, "-", course.id) AS id,
                    user.id AS user_id, 
                    user.email AS user_email,
                    user.firstname AS user_firstname,
                    user.lastname AS user_lastname,
                    user.username AS user_username,
                    course.id AS course_id,
                    course.fullname AS course_fullname,
                    course.category AS course_category                    
            FROM {course} AS course
            JOIN {context} ctx ON course.id = ctx.instanceid
            JOIN {role_assignments} AS ra ON ctx.id = ra.contextid
            JOIN {user} AS user ON ra.userid = user.id AND ra.roleid = :student_roleid
            WHERE course.id <> :frontpagecourse_id AND user.id <> :guest_user_id AND user.deleted <> :user_deleted AND user.suspended = :user_suspended ';

        $sql_params = [
            'frontpagecourse_id' => 1,
            'user_deleted' => 1,
            'user_suspended' => 0,
            'guest_user_id' => 1,
            'student_roleid' => 5
        ];

        if ($search_course) {
            $sql_query = $sql_query . ' AND course.fullname LIKE \'%' . trim($search_course) . '%\' ';
        }
        if ($search_user) {
            $sql_query = $sql_query . ' AND ( user.firstname LIKE \'%' . trim($search_user) . '%\' OR user.lastname LIKE \'%' . trim($search_user) . '%\' OR user.email LIKE \'%' . trim($search_user) . '%\' ) ';
        }
        if ($search_moodle_user_id) {
            $user_id = (int)$search_moodle_user_id;
            if (!$user_id) {
                $user_id =  UtilTheme_handler::encrypt_decrypt_value(trim($search_moodle_user_id), 'decrypt');
            }
            $sql_query = $sql_query . 'AND user.id = :user_id';
            $sql_params['user_id'] = $user_id;
        }
        if ($download) {
            $records = $DB->get_records_sql($sql_query, $sql_params);
        } else {
            $all_records = $DB->get_records_sql($sql_query, $sql_params);
            $records = $DB->get_records_sql($sql_query, $sql_params, $limitfrom, $limitnum);
        }
        // 
        $records_final[] = $table_header;
        $i = $limitfrom + 1;
        foreach ($records as $key => $record) {
            $course_type = '';
            $course_metadata = UtilCourse_handler::get_custom_field_metadata($record->course_id);
            foreach ($course_metadata as $key => $medata) {
                if ($medata['shortname'] === 'course_type') {
                    $course_type = $medata['value'];
                }
            }
            $course = get_course($record->course_id);
            $total_duration = skl_time_track::get_user_course_time_duration($record->course_id, $record->user_id);
            $duration_time = time_track_report::get_duration_time($total_duration, false);
            $secret_user_id = UtilTheme_handler::encrypt_decrypt_value($record->user_id, 'encrypt');
            $user_name =  $record->user_firstname . ' ' . $record->user_lastname . " \n " . $record->user_email;
            $institution = self::get_user_institution_domain($record->user_id);
            $course_progress = self::get_user_course_progress($course, $record->user_id);
            // 
            $arr = [];
            if ($download) {
                $arr[] =  $i;
                $arr[] = $secret_user_id;
                $arr[] = $user_name;
                $arr[] = $institution;
                $arr[] = $record->course_fullname;
                $arr[] = $course_type;
                $arr[] = $course_progress;
                $arr[] = $duration_time;
            } else {
                $arr['sn'] =  $i;
                $arr['secret_user_id'] = $secret_user_id;
                $arr['user_name'] = $user_name;
                $arr['institution'] = $institution;
                $arr['course_fullname'] = $record->course_fullname;
                $arr['course_type'] = $course_type;
                $arr['course_progress'] = $course_progress;
                $arr['duration_time'] = $duration_time;
                $arr['course_url'] = (new moodle_url('/course/view.php', array('id' => $record->course_id)))->out();
                $arr['user_profile_url'] = (new moodle_url('/user/profile.php', array('id' => $record->user_id)))->out();
                $arr['time_track_detail_url'] = (new moodle_url('/theme/skilllab/pages/report/time_track.php', array("course_id" => $record->course_id, 'user_id' => $record->user_id)))->out();
            }
            $records_final[] = $arr;
            $i++;
        }

        if ($download) {
            // if ($dataformat == 'excel') {
            require_once($CFG->dirroot . '/lib/excellib.class.php');
            $filename = 'student_course_report_' . (time()) . '.xlsx';
            // $records = $DB->get_records_sql($sql_query, $sql_params);
            $downloadfilename = clean_filename($filename);
            // Creating a workbook.
            $workbook = new MoodleExcelWorkbook("-");
            // Sending HTTP headers.
            $workbook->send($downloadfilename);
            // Adding the worksheet.
            $myxls = $workbook->add_worksheet($filename);

            foreach ($records_final as $ri => $col) {
                foreach ($col as $ci => $cv) {
                    $myxls->write_string($ri, $ci, $cv);
                }
            }

            $workbook->close();
            //     exit;
            // }
            redirect('/student-course-report');
        } else {
            return [
                'records_final' => $records_final,
                'records_count' => count($all_records),
                'page_number' => $page_number,
                'per_page_data' => $per_page_data,
                'search_moodle_user_id' => $search_moodle_user_id,
                'search_user' => $search_user,
                'search_course' => $search_course
            ];
        }
    }

    // 
}
