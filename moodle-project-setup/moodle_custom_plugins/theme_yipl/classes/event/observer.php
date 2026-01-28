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
 * @package   theme_yipl    
 * @copyright 2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\event;

use stdClass;
use theme_yipl\util\UtilCourse_handler;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer definition
 *
 * @package   theme_yipl
 * @copyright 2025 YIPL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer
{

    /**
     * course_viewed event
     * @param \core\event\course_viewed $event
     */
    public static function course_viewed(\core\event\course_viewed $event)
    {
        // $data = $event->get_data();
        $courseid = $event->courseid;
        $userid = $event->userid;
        UtilCourse_handler::course_enrolled_message($userid, $courseid);
    }

    /**
     * enrolment event
     * @param \core\event\user_enrolment_created $event
     */
    public static function enrolment_created(\core\event\user_enrolment_created $event)
    {
        global $SESSION;
        $related_user_id = $event->relateduserid;
        $course_id = $event->courseid;
        // // $data = $event->get_data();
        // $enrol_method = 'fee'; // fee, manual , self or other methods
        // // No more process for disabled enrol plugins.
        // if (!enrol_is_enabled($enrol_method)) {
        //     return true;
        // }
        $related_user = \core\user::get_user($related_user_id);
        $user_auth_method = $related_user->auth;
        $SESSION->first_course_enrolled_view = true;

        return true;
    }

    /**
     * module_updated event
     * @param \core\event\course_module_updated $event
     */
    public static function module_updated(\core\event\course_module_updated $event)
    {
        UtilCourse_handler::course_module_created_update_event($event);
    }

    /**
     * module_created event
     * @param \core\event\course_module_created $event
     */
    public static function module_created(\core\event\course_module_created $event)
    {
        UtilCourse_handler::course_module_created_update_event($event);
    }

    // /**
    //  * enrolment event
    //  * @param \core\event\user_enrolment_deleted $event
    //  */
    // public static function enrolment_deleted(\core\event\user_enrolment_deleted $event)
    // {

    //     global $USER;
    //     $user_id = $event->relateduserid;
    //     $course_id = $event->courseid;

    //     // \theme_yipl\util\UtilNotification_handler::send_message($USER, "Enrollment delete", "You un-enrolled user id: " . $user_id . ' in course id: ' . $course_id . " enrol method: " . $event->other['enrol']);

    //     return true;
    // }

    // /**
    //  * course_created event
    //  * @param \core\event\course_course_created $event
    //  */
    // public static function course_created(\core\event\course_created $event)
    // {
    //     global $USER;
    //     $course_id = $event->courseid;
    // }

    // /**
    //  * course_updated event
    //  * @param \core\event\course_course_updated $event
    //  */
    // public static function course_updated(\core\event\course_updated $event)
    // {
    //     global $USER;
    //     $course_id = $event->courseid;
    // }

    // /**
    //  * course_deleted event
    //  * @param \core\event\course_course_deleted $event
    //  */
    // public static function course_deleted(\core\event\course_deleted $event)
    // {
    //     global $USER;
    //     $course_id = $event->courseid;
    // }
    // /**
    //  * course_module_completion_updated event
    //  * @param \core\event\course_module_completion_updated $event
    //  */
    // public static function course_module_completion_updated(\core\event\course_module_completion_updated $event)
    // {
    //     global $USER, $DB;
    //     $user_id = $event->relateduserid;
    //     $course_id = $event->courseid;
    //     $course_module_id = $event->contextinstanceid;
    //     $completionstate = ($event->other)['completionstate'];
    // }
    // /**
    //  * course_section_created event
    //  * @param \core\event\course_section_created $event
    //  */
    // public static function course_section_created(\core\event\course_section_created $event)
    // {
    //     global $USER, $DB;
    //     $course_id = $event->courseid;
    // }

    // /**
    //  * course_section_deleted event
    //  * @param \core\event\course_section_deleted $event
    //  */
    // public static function course_section_deleted(\core\event\course_section_deleted $event)
    // {
    //     global $USER, $DB;
    //     $course_id = $event->courseid;
    // }


    // /**
    //  * course_category_updated event
    //  * @param \core\event\course_category_updated $event
    //  */
    // public static function course_category_updated(\core\event\course_category_updated $event)
    // {
    //     global $USER, $DB;
    //     $category_id = $event->objectid;
    // }

    // /**
    //  * course_category_deleted event
    //  * @param \core\event\course_category_deleted $event
    //  */
    // public static function course_category_deleted(\core\event\course_category_deleted $event)
    // {
    //     // when course category is deleted all the course under this category is moved to next category and course is updated.
    // }

    // 
}
