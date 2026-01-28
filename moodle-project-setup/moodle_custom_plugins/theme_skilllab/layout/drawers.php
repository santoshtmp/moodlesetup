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

require_once($CFG->dirroot . '/theme/skilllab/inc/layout_handler/main.php');

// To delete course perform from theme custom file
if ($this->body_id() == 'page-course-delete') {
    $delete_id = required_param('id', PARAM_INT); // Course ID.
    $redirect_url = $CFG->wwwroot . '/course/delete?id=' . $delete_id;
    redirect($redirect_url);
}

// do not open change password page
if ($this->body_id() == 'page-login-change_password') {
    $url = $CFG->wwwroot;
    $message = "You cannot change password from here. Contact Site admin";
    redirect($url, $message);
}

// redirect to home page as we don't need preferences page
if ($this->body_id() == 'page-user-preferences') {
    $redirect_url = $CFG->wwwroot;
    $message = $CFG->wwwroot . $_SERVER['REQUEST_URI']  . " this page is no longer available";
    redirect($redirect_url, $message);
}

// for course edit / add page
if ($this->body_id() == 'page-course-edit') {
    require_once($CFG->dirroot . '/theme/skilllab/inc/layout_handler/course_edit.php');
} else {
    echo $OUTPUT->render_from_template('theme_skilllab/layout/drawers', $templatecontext);
}
