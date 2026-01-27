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
 * @package   local_skilllab   
 * @copyright  2023 yipl skill lab csc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_completion\progress;
use theme_skilllab\util\UtilCourse_handler;
use theme_skilllab\util\UtilUser_handler;

require_once($CFG->dirroot . '/course/lib.php');


if (!function_exists('get_course_info')) {
    /**
     * @param $course course id
     * @return array or boolen 
     */
    function get_course_info($course_id, $during_create = false)
    {
        global $DB, $CFG, $USER;
        $courseinfo = [];

        if ($DB->record_exists('course', array('id' => $course_id))) {
            $course = $DB->get_record('course', ['id' => $course_id]);
            $context = \context_course::instance($course->id, IGNORE_MISSING);
            $course_categories = $DB->get_record('course_categories', ['id' => $course->category]);
            // course custom field data
            $skill_level = $course_duration = $course_type = '';
            if ($during_create) {
                $courseinfo['created_by'] = encryptValue($USER->id);

                $customfield_skill_level = optional_param('customfield_skill_level', 0, PARAM_INT);
                if ($customfield_skill_level > 0) {
                    $customfield_field = $DB->get_record('customfield_field', ['shortname' => 'skill_level', 'type' => 'select']);
                    $configdata = $customfield_field->configdata;
                    $options = json_decode($configdata)->options;
                    $options = explode("\r\n", $options);
                    $skill_level = $options[$customfield_skill_level - 1];
                }
                $customfield_course_duration = optional_param('customfield_course_duration', '', PARAM_RAW);
                if ($customfield_course_duration) {
                    $course_duration = $customfield_course_duration;
                }
                $customfield_course_type = optional_param('customfield_course_type', 0, PARAM_INT);
                if ($customfield_course_type > 0) {
                    $customfield_field = $DB->get_record('customfield_field', ['shortname' => 'course_type', 'type' => 'select']);
                    $configdata = $customfield_field->configdata;
                    $options = json_decode($configdata)->options;
                    $options = explode("\r\n", $options);
                    $course_type = $options[$customfield_course_type - 1];
                    $course_type  = str_replace(" ", "_", $course_type);
                }
            } else {
                $course_metadata = UtilCourse_handler::get_custom_field_metadata($course_id);
                foreach ($course_metadata as $key => $medata) {
                    if ($medata['shortname'] === 'skill_level') {
                        $skill_level = $medata['value'];
                    }
                    if ($medata['shortname'] === 'course_duration') {
                        $course_duration = $medata['value'];
                    }
                    if ($medata['shortname'] === 'course_type') {
                        $course_type = $medata['value'];
                        $course_type  = str_replace(" ", "_", $course_type);
                    }
                }
            }

            try {
                $numsections = (int)$DB->get_field_sql('SELECT max(section) from {course_sections} WHERE course = ?', [$course->id]);
            } catch (\Throwable $th) {
                $numsections = get_config('moodlecourse ')->numsections;
            }


            // data arrange to return
            // this is also the return data of skilllab_get_courses api 

            $courseinfo['id'] = $course->id;
            $courseinfo['categoryid'] = $course->category;
            $courseinfo['shortname'] =  format_string($course->shortname);
            $courseinfo['fullname'] =  format_string($course->fullname);
            $courseinfo['displayname'] = format_string(get_course_display_name_for_list($course));
            $courseinfo['category_name'] = $course_categories->name;
            $courseinfo['sortorder'] = $course->sortorder;
            $courseinfo['course_url'] = (new moodle_url('/course/view.php', ['id' => $course->id]))->out();
            $courseinfo['summary'] = UtilCourse_handler::get_course_formatted_summary($course);
            $courseinfo['course_img_url'] = format_string(UtilCourse_handler::get_course_image($course));
            $courseinfo['count_enrolled_users'] = count_enrolled_users($context, 'moodle/course:isincompletionreports');
            $courseinfo['numsections'] = $numsections;
            $courseinfo['chapter_topics'] = $numsections;
            $courseinfo['course_visible'] = $course->visible;
            $courseinfo['course_startdate'] = $course->startdate;
            $courseinfo['course_enddate'] = $course->enddate;
            $courseinfo['course_timecreated'] = $course->timecreated;
            $courseinfo['course_timemodified'] = $course->timemodified;
            $courseinfo['skill_level'] = $skill_level;
            $courseinfo['course_duration'] = $course_duration;
            $courseinfo['course_type'] = $course_type;
            return $courseinfo;
        }
        return false;
    }
}


/**
 * return user institution domain if present else default value else empty
 */
function get_current_user_institution_domain()
{
    global $USER, $DB;
    $institution_domain = '';

    if ($USER->profile['institution_domain']) {
        $institution_domain =  $USER->profile['institution_domain'];
    } else {
        // check in db
        $institution_domain = UtilUser_handler::get_user_institution_domain($USER->id);
        // check and get default value
        if (empty($institution_domain)) {
            $defaultdata = $DB->get_record('user_info_field', ['shortname' => 'institution_domain'], 'defaultdata');
            if ($defaultdata->defaultdata) {
                $institution_domain = $defaultdata->defaultdata;
            }
        }
    }
    return $institution_domain;
}

/**
 * return url 
 * according to user doomain to csc
 */
function get_user_csc_redirect($url_path = '/login/index.php', $csc_path = '/login')
{
    global $CFG;
    $institution_domain =  ($institution_domain = get_current_user_institution_domain()) ? $institution_domain . '.' : '';
    $redirect_point = $CFG->wwwroot . '/login/index.php';

    // theme setting
    $theme = theme_config::load('skilllab');
    $site_environment =  $theme->settings->site_environment;
    if ($site_environment == 2) {
        $redirect_point = $CFG->wwwroot . $url_path;
    }
    if ($site_environment == 0) {
        $redirect_point = 'https://' . $institution_domain . 'stage.careerservicelab.com' . $csc_path;
    }
    if ($site_environment == 1) {
        $redirect_point = 'https://' . $institution_domain . 'careerservicelab.com' . $csc_path;
    }

    return  $redirect_point;
}



if (!function_exists('restrict_guest_non_student')) {

    /**
     * Restrict the guest or not real user from accessig course content activity like course page and other
     */
    function restrict_guest_non_student($course, $module_name)
    {
        if (isguestuser()) {
            $redirect_url = new moodle_url('/course/view.php', array('id' => $course->id));
            $message = 'Guest are not allowed to view <strong>"' . $module_name . '"</strong> page. Please <a href="/login"> Login </a>';
            redirect($redirect_url, $message);
        }
        $context = \context_course::instance($course->id);
        // // check course student only = capability moodle/course:isincompletionreports
        // if (!has_capability('gradereport/user:view', $context)) {
        //     $redirect_url = new moodle_url('/course/view.php', array('id' => $course->id));
        //     $message = 'You are not allowed to view <strong>"' . $module_name . '"</strong> page. Please Enroll as studnet in the course';
        //     redirect($redirect_url, $message);
        // }
    }
}
