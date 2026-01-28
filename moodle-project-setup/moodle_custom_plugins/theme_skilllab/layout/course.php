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
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_skilllab\util\UtilCourse_handler;

defined('MOODLE_INTERNAL') || die();

global $DB, $COURSE, $USER;
$section = optional_param('section', '', PARAM_INT);
require_once($CFG->dirroot . '/theme/skilllab/inc/layout_handler/main.php');

$templatecontext['show_edit_switch'] = true;

if ($section) {
    $PAGE->set_heading('');
    $templatecontext['secondarymoremenu'] = false;
} else {
    $templatecontext['course_description'] = UtilCourse_handler::get_course_formatted_summary($COURSE);
}

// check  topic course format setting 
$topic_format = $DB->get_record('course_format_options', ['courseid' => $COURSE->id, 'format' => 'topics', 'name' => 'coursedisplay']);
if ($topic_format->value) {
    $templatecontext['topic_course_type'] = "each_section";
} else {
    $templatecontext['topic_course_type'] = "all_sections";
}
$templatecontext['context_id'] = $context->id;

// $enrolled = ($USER->enrol)['enrolled'];
// $skl_time_track = theme_skilllab_get_setting('skl_time_track');
// if ($enrolled) {
//     $context_course = \context_course::instance($COURSE->id);
//     $get_user_roles = get_user_roles($context_course, $USER->id);
//     foreach ($get_user_roles as $key => $role) {
//         if ($role->roleid == '5') {
//             // // Call the `init` function on `theme_skilllab/time_track/time_track`.
//             $context = context_course::instance($COURSE->id);
//             $templatecontext['context_id'] = $context->id;
//             $page_data = [
//                 'course_id' => $COURSE->id,
//                 'cmod_id' => 0
//             ];
//             if ($skl_time_track) {
//                 $PAGE->requires->js_call_amd('theme_skilllab/time_track/time_track', 'init', [$page_data]);
//             }
//         }
//     }
// }

// section
echo $OUTPUT->render_from_template('theme_skilllab/layout/course', $templatecontext);
