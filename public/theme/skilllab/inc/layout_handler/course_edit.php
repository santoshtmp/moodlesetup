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

defined('MOODLE_INTERNAL') || die();

$course_id = optional_param('id', 0, PARAM_INT);
$scholarship = optional_param('scholarship', 0, PARAM_INT);
$career_road_map = optional_param('career_road_map', 0, PARAM_INT);

if (!$course_id) {
    $templatecontext['secondarymoremenu'] = false;
}

if ($scholarship) {
    $page_title = ($course_id) ? 'Edit scholarship course settings' : 'Add a new scholarship course';
    $PAGE->set_title($page_title);
    $PAGE->set_heading('');
    $PAGE->requires->js('/theme/skilllab/assets/js/edit_course_scholarship.js');
} else if ($career_road_map) {
    $page_title = ($course_id) ? 'Edit career road map course settings' : 'Add a new career road map course';
    $PAGE->set_title($page_title);
    $PAGE->set_heading('');
    $PAGE->requires->js('/theme/skilllab/assets/js/edit_course_career_road_map.js');
} else {
    $page_title = ($course_id) ? 'Edit course settings' : 'Add a new course';
    $PAGE->set_title($page_title);
    $PAGE->set_heading('');
}

echo $OUTPUT->render_from_template('theme_skilllab/layout/course_edit_info', $templatecontext);
