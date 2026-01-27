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

use theme_skilllab\util\UtilUser_handler;

defined('MOODLE_INTERNAL') || die();

// redirect to csc as we don't need this page because all course list is access through csc /course
// $redirect_url =  get_user_csc_redirect($url_path = '', $csc_path = '/courses');
// redirect($redirect_url);


global $DB;
require_once($CFG->dirroot . '/theme/skilllab/inc/layout_handler/main.php');

$role_shortname = UtilUser_handler::get_user_roles();
if (!in_array('admin', $role_shortname)) {
    $redirect_other = $CFG->wwwroot . '/my/courses.php';
    // check if user is valid user
    if (!isguestuser() || $USER->id > 2) {
        $redirect_other = get_user_csc_redirect('/my/courses.php', $csc_path = '/courses');
    }
    // required login in all layout (force login )
    require_login();
    if (isguestuser()) {
        redirect(get_login_url());
    }
    if (theme_skilllab_get_setting('site_environment') != 2) {
        redirect($redirect_other);
    }
}

$templatecontext['course_create'] = true;
$categoryid = 0;
$templatecontext['course_create_url'] = $CFG->wwwroot . '/course/edit.php?category=' . $categoryid;

$page_title = 'Courses';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
$templatecontext['secondarymoremenu'] = false;

$PAGE->requires->js('/theme/skilllab/assets/js/course_filter_section.js');
$PAGE->requires->js_call_amd('theme_skilllab/course/courses_apply_filter', 'init');

echo $OUTPUT->render_from_template('theme_skilllab/layout/coursecategory', $templatecontext);
