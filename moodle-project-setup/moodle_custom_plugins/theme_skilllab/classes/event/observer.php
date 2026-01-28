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
 * @package   theme_skilllab    
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\event;

use stdClass;
use theme_skilllab\local\skl_time_track;
use theme_skilllab\util\UtilNotification_handler;
use theme_skilllab\util\UtilTheme_handler;
use theme_skilllab\util\UtilUser_handler;
use theme_skilllab\util\UtilCourse_handler;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/skilllab/lib.php');

/**
 * Observer definition
 *
 * @package    
 * @copyright  2017 e-ABC Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Osvaldo Arriola <osvaldo@e-abclearning.com>
 */
class observer {


    /**
     * hook role_assigned event
     * @param \core\event\role_assigned $event
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        global $DB, $USER;
        $user_id = $event->relateduserid;
        $role_id = $event->objectid;
        $context_id = $event->contextid;

        $skl_course_role_count = new \theme_skilllab\local\skl_course_role_count();
        $role_count = $skl_course_role_count->role_assign($role_id,  $context_id);


        if ($role_count) {
            // skilllab_sendmessage($USER, "role assign", $user_id . ' ' . $role_id . ' ' . $context_id);
        }
    }

    /**
     * hook role_unassigned event
     * @param \core\event\role_unassigned $event
     */
    public static function role_unassigned(\core\event\role_unassigned $event) {
        global $DB, $USER;
        $user_id = $event->relateduserid;
        $role_id = $event->objectid;
        $context_id = $event->contextid;


        $skl_course_role_count = new \theme_skilllab\local\skl_course_role_count();
        $role_count = $skl_course_role_count->role_unassign($role_id,  $context_id);

        if ($role_count) {
            // skilllab_sendmessage($USER, "role unassign", $user_id . ' ' . $role_id . ' ' . $context_id);
        }
    }

    /**
     * hook enrolment event
     * @param \core\event\user_enrolment_created $event
     */
    public static function enrolment_created(\core\event\user_enrolment_created $event) {
        // global $DB, $USER, $CFG;
        // $user_id = $event->relateduserid;
        // $course_id = $event->courseid;
    }

    /**
     * hook enrolment event
     * @param \core\event\user_enrolment_updated $event
     */
    public static function enrolment_updated(\core\event\user_enrolment_updated $event) {
        // global $DB, $USER;
    }

    /**
     * hook enrolment event
     * @param \core\event\user_enrolment_deleted $event
     */
    public static function enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        global $DB, $USER;
        $user = $DB->get_record('user', array('id' => $event->relateduserid));
        $course = $DB->get_record('course', array('id' => $event->courseid));
        $csc_enrolled = new \theme_skilllab\csc_api\enrolled();
        $sucess = $csc_enrolled->set_enrolled_user_delete($course->id, $user->id);
        if (!$sucess) {
            $subject = "API Fail during event enrolment_deleted";
            $full_message = " <br>  \"careerservicelab.com/api/moodle/remove-enrolled\" <br> Failed for course_id: " . $event->courseid;
            UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
        }
    }

    /**
     * hook course_created event
     * @param \core\event\course_course_created $event
     */
    public static function course_created(\core\event\course_created $event) {
        global $USER;
        $course_id = $event->courseid;
        $csc_course = new \theme_skilllab\csc_api\course();
        $sucess = $csc_course->set_course_create_update($course_id, true);
        if (!$sucess) {
            $subject = "API Fail during event course_created";
            $full_message = " <br>  \"careerservicelab.com/api/moodle/course/upsert\" <br> Failed for course_id: " . $event->courseid;
            UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
        }
        $skl_custom_course_field = new \theme_skilllab\local\skl_custom_course_field();
        $skl_custom_course_field->skl_custom_course_field($course_id, true);

        $skl_course_role_count = new \theme_skilllab\local\skl_course_role_count();
        $skl_course_role_count->course_creade_role_assign($course_id);
    }

    /**
     * hook course_updated event
     * @param \core\event\course_course_updated $event
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $USER;
        $course_id = $event->courseid;
        $csc_course = new \theme_skilllab\csc_api\course();
        $sucess = $csc_course->set_course_create_update($course_id);
        if (!$sucess) {
            $subject = "API Fail during event course_updated";
            $full_message = " <br>  \"careerservicelab.com/api/moodle/course/upsert\" <br> Failed for course_id: " . $event->courseid;
            UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
        }
        $skl_custom_course_field = new \theme_skilllab\local\skl_custom_course_field();
        $skl_custom_course_field->skl_custom_course_field($course_id, false);
    }

    /**
     * hook course_deleted event
     * @param \core\event\course_course_deleted $event
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $USER;
        $course_id = $event->courseid;
        $csc_course = new \theme_skilllab\csc_api\course();
        $sucess = $csc_course->set_course_delete($course_id);
        if (!$sucess) {
            $subject = "API Fail during event course_deleted";
            $full_message = " <br>  \"careerservicelab.com/api/moodle/course/ID\" <br> Failed for course_id: " . $event->courseid;
            UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
        }
        \theme_skilllab\local\skl_time_track::delete_time_track_course_id($course_id);
    }


    /**
     * hook module_updated event
     * @param \core\event\course_module_updated $event
     */
    public static function module_updated(\core\event\course_module_updated $event) {
        module_created_update($event);
    }

    /**
     * hook module_created event
     * @param \core\event\course_module_created $event
     */
    public static function module_created(\core\event\course_module_created $event) {
        module_created_update($event);
    }

    /**
     * hook module_deleted event
     * @param \core\event\course_module_deleted $event
     */
    public static function module_deleted(\core\event\course_module_deleted $event) {
        $cm_id = $event->objectid;
        \theme_skilllab\local\skl_time_track::delete_time_track_cmod_id($cm_id);
    }

    /**
     * hook course_module_completion_updated event
     * @param \core\event\course_module_completion_updated $event
     */
    public static function course_module_completion_updated(\core\event\course_module_completion_updated $event) {
        $user_id = $event->relateduserid;
        $course_id = $event->courseid;
        // $course_module_id = $event->contextinstanceid;
        // $completionstate = ($event->other)['completionstate'];

        \theme_skilllab\local\skl_user_course_progress::skl_user_course_progress($course_id, $user_id);
        //    
        $course = get_course($course_id);
        $course_customcert = UtilCourse_handler::course_mod_customcert($course_id, $user_id);
        $progress_percentage = UtilUser_handler::get_user_course_progress($course, $user_id);
        if ($progress_percentage == 100) {
            if (!$course_customcert['certificate_issues']) {
                $customcert_id = isset($course_customcert['customcert_id']) ? $course_customcert['customcert_id'] : '';
                if ($customcert_id) {
                    \mod_customcert\certificate::issue_certificate($customcert_id, $user_id);
                    $course_customcert = UtilCourse_handler::course_mod_customcert($course_id, $user_id);
                }
            }
        }
        $post_data = [
            "data" => [
                [
                    'course_id' => $course_id,
                    'moodle_user_id' => UtilTheme_handler::encrypt_decrypt_value($user_id, 'encrypt'),
                    'duration' => skl_time_track::get_user_course_time_duration($course_id, $user_id),
                    'progress_percentage' => $progress_percentage,
                    'certificate_url' => $course_customcert['certificate_url'],
                    'certificate_issues' => $course_customcert['certificate_issues'],
                    'certificate_issues_date' => $course_customcert['certificate_issues_date'],
                ]
            ]
        ];
        $sucess = \theme_skilllab\csc_api\update_enroll_user::api_update_enroll_user($post_data);
        if ($sucess) {
            skl_time_track::undate_sync_csc_time_track($user_id, $course_id);
            $subject = "Course Completion API Success";
            $full_message = " <br>  \"careerservicelab.com/api/moodle/course/update-enroll-user\" <br> success for course id: " . $course_id . " and user id : " . $user_id . " Encryped user id : " . UtilTheme_handler::encrypt_decrypt_value($user_id, 'encrypt');
            UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
        } else {
            $subject = "Course Completion API Fail";
            $full_message = " <br>  \"careerservicelab.com/api/moodle/course/update-enroll-user\" <br> Failed for course id: " . $course_id . " and user id : " . $user_id . " Encryped user id : " . UtilTheme_handler::encrypt_decrypt_value($user_id, 'encrypt');
            UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
        }
    }

    /**
     * hook course_section_created event
     * @param \core\event\course_section_created $event
     */
    public static function course_section_created(\core\event\course_section_created $event) {
        global $USER, $DB;
        $course_id = $event->courseid;

        if (count($DB->get_records('course_sections', array('course' => $course_id))) > 1) {
            $csc_course = new \theme_skilllab\csc_api\course();
            $sucess = $csc_course->set_course_topics($course_id);
            if ($sucess) {
                $course = $DB->get_record('course', ['id' => $course_id]);
                // try {
                //     $numsections = course_get_format($course)->get_last_section_number();
                // } catch (\Throwable $th) {
                //     $numsections = get_config('moodlecourse ')->numsections;
                // }
                // skilllab_sendmessage($USER, "course_section_created", $USER->id . ' , ' . $course_id . ' , ' . $numsections);
                // // skilllab_sendmessage($USER, "course_section_created", "course_section_created " . json_encode(get_course_info($course_id)));
            } else {
                $subject = "API Fail during event course_section_created";
                $full_message = " <br>  \"careerservicelab.com/api/moodle/course/upsert\" <br> Failed for course_id: " . $course_id;
                UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
            }
        }
    }

    /**
     * hook course_section_deleted event
     * @param \core\event\course_section_deleted $event
     */
    public static function course_section_deleted(\core\event\course_section_deleted $event) {
        global $USER, $DB;
        $course_id = $event->courseid;
        $csc_course = new \theme_skilllab\csc_api\course();
        $sucess = $csc_course->set_course_topics($course_id);
        if ($sucess) {
            $course = $DB->get_record('course', ['id' => $course_id]);
            $numsections = course_get_format($course)->get_last_section_number();
            // // skilllab_sendmessage($USER, "course_section_deleted", "course_section_deleted " . json_encode(get_course_info($course_id)));
            // skilllab_sendmessage($USER, "course_section_deleted", $USER->id . ' , ' . $course_id . ' , ' . $numsections);
        }
    }


    /**
     * hook course_category_updated event
     * @param \core\event\course_category_updated $event
     */
    public static function course_category_updated(\core\event\course_category_updated $event) {
        global $USER, $DB;
        $category_id = $event->objectid;
        $csc_course = new \theme_skilllab\csc_api\course();

        if ($category_id) {
            $courses = $DB->get_records('course', ['category' => $category_id]);
            foreach ($courses as $key => $course) {
                $sucess = $csc_course->set_course_create_update($course->id);
                // skilllab_sendmessage($USER, "course_category_updated", "course_category_updated course-id " . $course->id);
                if (!$sucess) {
                    $subject = "API Fail during event course_category_updated";
                    $full_message = " <br>  \"careerservicelab.com/api/moodle/course/upsert\" <br> Failed for category id: " . $category_id;
                    UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
                }
            }
        }
    }

    /**
     * hook course_category_deleted event
     * @param \core\event\course_category_deleted $event
     */
    public static function course_category_deleted(\core\event\course_category_deleted $event) {
        // when course category is deleted all the course under this category is moved to next category and course is updated.
    }

    // 
}

/**
 * @param $event
 */
function module_created_update($event) {
    global $DB, $CFG;
    $cm_id = $event->objectid;
    $modulename = $event->other['modulename'];
    $data = new stdClass();
    $data->id = $cm_id;
    // For mod customcert the completion as null defined
    if ($modulename == 'customcert') {
        $cm = get_coursemodule_from_id('customcert', $cm_id, 0, false, MUST_EXIST);
        $data->completion = 0;
        // // update db
        $DB->update_record('course_modules', $data);
        //clear cache
        require_once($CFG->libdir . '/adminlib.php');
        purge_caches();
    }
    // // For other mod except forum completion as view default
    // else if ($modulename != 'forum') {
    //     $data->completion = 2;
    //     $data->completionview = 1;
    // }
    // // update db
    // $DB->update_record('course_modules', $data);
    // //clear cache
    // require_once($CFG->libdir . '/adminlib.php');
    // purge_caches();
}
