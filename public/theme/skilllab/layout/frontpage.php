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

// redirect to course page
$redirect_url = $CFG->wwwroot . '/course';
redirect($redirect_url);

require_once($CFG->dirroot . '/theme/skilllab/inc/layout_handler/main.php');

$context_sys = \context_system::instance();
if (has_capability('moodle/course:create', $context_sys)) {
    $templatecontext['course_create'] = true;
    $categoryid = 0;
    $templatecontext['course_create_url'] = $CFG->wwwroot . '/course/edit.php?category=' . $categoryid;
}

echo $OUTPUT->render_from_template('theme_skilllab/layout/frontpage', $templatecontext);
