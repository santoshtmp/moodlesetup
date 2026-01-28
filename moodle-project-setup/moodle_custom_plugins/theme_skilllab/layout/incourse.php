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
 * A drawer based layout for the boost theme.
 *
 * @package   theme_skilllab
 * @copyright 2021 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_skilllab\local\skl_time_track;
use theme_skilllab\util\UtilUser_handler;

defined('MOODLE_INTERNAL') || die();
global $COURSE, $USER, $DB;

require_once($CFG->dirroot . '/theme/skilllab/inc/layout_handler/main.php');

if ($this->page->cm) {
    if ($this->page->cm->id) {
        $course = $this->page->cm->get_course();
        $module_name  = $this->page->cm->name;
        // course activity restriciton for guest and non-student to the course.
        restrict_guest_non_student($course, $module_name);
        // 
        // show course side bar only in course module pages
        $templatecontext['course_side_bar'] = true;
        $templatecontext['course_courseindex'] = true;
        $templatecontext['cmid'] = $this->page->cm->id;
        $templatecontext['student_user'] = false;

        $context_sys = \context_system::instance();
        if (!has_capability('moodle/course:create', $context_sys)) {
            $enrolled = ($USER->enrol)['enrolled'];
            if ($enrolled) {
                // // get enrolled user role in the course
                $context_course = \context_course::instance($COURSE->id);
                $get_user_roles = get_user_roles($context_course, $USER->id);
                foreach ($get_user_roles as $key => $role) {
                    if ($role->roleid == '5') {
                        $templatecontext['student_user'] = true;
                        // 
                        if ($this->body_id() == 'page-mod-customcert-view') {
                            $percentage =  UtilUser_handler::get_user_course_progress($COURSE, $USER->id);
                            if ($percentage < 100) {
                                redirect('/course/view.php?id=' . $COURSE->id, 'You need to complete the course as 100% to view');
                            }
                        }
                        // 
                        skl_time_track::start_time_track_session_process($COURSE->id, $this->page->cm->id);
                    }
                }
            }
        }
    }
}
// redirect to home page as we don't need enrolled page
if ($this->body_id() == 'page-enrol-index') {
    $redirect_url = $CFG->wwwroot;
    redirect($redirect_url);
}

// add time track report link
$context = \context_system::instance();
if (has_capability('moodle/site:config', $context)) {
    if (str_contains($_SERVER['REQUEST_URI'], '/report/view.php')) {
        if ($DB->record_exists('skl_time_track', array('course_id' => $COURSE->id))) {
            $PAGE->requires->js_call_amd('theme_skilllab/time_track/add_report_item', 'init', [$COURSE->id]);
        }
    }
}

echo $OUTPUT->render_from_template('theme_skilllab/layout/incourse', $templatecontext);
