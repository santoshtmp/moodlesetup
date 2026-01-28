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
 * Admin-only code to delete a course utterly.
 *
 * @package core_course
 * @copyright 2002 onwards Martin Dougiamas (http://dougiamas.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_skilllab\util\UtilCourse_handler;

// this file is the customize file of original course/delete.php
require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

$id = required_param('id', PARAM_INT); // Course ID.
$delete = optional_param('delete', '', PARAM_ALPHANUM); // Confirmation hash.
$delete_conform = optional_param('delete_conform', '', PARAM_ALPHANUM); // Confirmation delete_conform.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$coursecontext = context_course::instance($course->id);

require_login();

if ($SITE->id == $course->id || !can_delete_course($id)) {
    // Can not delete frontpage or don't have permission to delete the course.
    throw new \moodle_exception('cannotdeletecourse');
}

$categorycontext = context_coursecat::instance($course->category);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/theme/skilllab/pages/course/delete.php', array('id' => $id));
// $PAGE->set_pagelayout('admin');
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('skilllab-course-delete');

navigation_node::override_active_url(new moodle_url('/course/management.php', array('categoryid' => $course->category)));

$courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
$coursefullname = format_string($course->fullname, true, array('context' => $coursecontext));
// $categoryurl = new moodle_url('/course/management.php', array('categoryid' => $course->category));
$course_type = 'course';
$course_metadata = UtilCourse_handler::get_custom_field_metadata($course->id);
foreach ($course_metadata as $key => $medata) {
    if ($medata['shortname'] === 'course_type') {
        $course_type = strtolower($medata['value']);
        $course_type = str_replace(" ", "_", $course_type);
    }
}
$return_course_url = new moodle_url('/' . $course_type);
$return_course_completion = new moodle_url('/' . $course_type);

function check_delete($courseid, $delete, $message, $return_course_url)
{
    global $OUTPUT;
    $action_url = new moodle_url('/course/delete', ['id' => $courseid]);

    $delete_icon = '<svg width="18" height="20" viewBox="0 0 18 20" fill="none"
        xmlns="http://www.w3.org/2000/svg">
        <path d="M1.61093 5.68713L1.76855 5.67969H1.60312H0.1875C0.0886897 5.67969 0.0078125 5.59881 0.0078125 5.5V4.75C0.0078125 4.33947 0.339471 4.00781 0.75 4.00781H3.75H5.4375H12.5625H14.25H17.25C17.6605 4.00781 17.9922 4.33947 17.9922 4.75V5.5C17.9922 5.59881 17.9113 5.67969 17.8125 5.67969H16.3969V5.52189L16.3891 5.68713L15.8102 17.9449C15.7729 18.7423 15.1177 19.3672 14.3203 19.3672H3.67969C2.88464 19.3672 2.22714 18.74 2.18984 17.9449L1.61093 5.68713ZM5.42969 3.99219H3.75781V2.125C3.75781 1.30197 4.42697 0.632812 5.25 0.632812H12.75C13.573 0.632812 14.2422 1.30197 14.2422 2.125V3.99219H12.5703V2.32031H12.75V2.3125V2.30469C12.6512 2.30469 12.5703 2.22381 12.5703 2.125H12.5625H12.5547V2.30469H5.44531V2.125H5.4375H5.42969C5.42969 2.22381 5.34881 2.30469 5.25 2.30469V2.3125V2.32031H5.42969V3.99219ZM14.1398 17.6953V17.853L14.1476 17.6879L14.7148 5.68787L14.707 5.6875L14.6992 5.68713L14.5417 5.67969H3.29297V5.6875V5.69531V5.85297L3.85235 17.6879L3.69487 17.6953H3.86016H14.1398Z" fill="#001E2D" stroke="white" stroke-width="0.015625"/>
    </svg>
    ';
    $attributes = [
        'role' => 'alertdialog',
        'aria-labelledby' => 'modal-header',
        'aria-describedby' => 'modal-body',
        'aria-modal' => 'true'
    ];
    $output = $OUTPUT->box_start('generalbox modal modal-dialog modal-in-page show', 'notice', $attributes);
    $output .= $OUTPUT->box_start('modal-content', 'modal-content');
    $output .= $OUTPUT->box_start('modal-header px-3', 'modal-header');
    $output .= html_writer::tag('h4', $delete_icon . '<span>Delete Course</span>');
    $output .= $OUTPUT->box_end();
    $attributes = [
        'role' => 'alert',
        'data-aria-autofocus' => 'true'
    ];
    $output .= $OUTPUT->box_start('modal-body', 'modal-body', $attributes);
    $output .= html_writer::tag('div', $message);
    $output .= html_writer::tag('p', 'Please enter "DELETE" to confirm');
    $output .= html_writer::start_tag('form', ['class' => 'conform-delete mform', 'action' => $action_url, 'method' => 'post']);
    $output .= html_writer::start_tag('div', ['class' => 'form-group col-md-9 form-inline align-items-start felement']);
    $output .= html_writer::start_tag('input', ['type' => 'hidden', 'name' => 'delete', 'value' => $delete]);
    $output .= html_writer::start_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
    $output .= html_writer::start_tag('input', ['type' => 'text', 'name' => 'delete_conform', 'class' => 'form-control', 'required' => 'required']);
    $output .= html_writer::end_tag('div');
    $output .= html_writer::start_tag('div', ['class' => 'col-md-9 form-inline align-items-start felement']);
    $output .= html_writer::tag('a', 'Cancel', ['class' => 'btn btn-secondary', 'href' => $return_course_url]);
    $output .= html_writer::start_tag('input', ['type' => 'submit', 'value' => 'Delete', 'class' => 'btn btn-primary']);
    $output .= html_writer::end_tag('div');
    $output .= html_writer::end_tag('form');
    $output .= $OUTPUT->box_end();
    $output .= $OUTPUT->box_end();
    $output .= $OUTPUT->box_end();

    return $output;
}

// Check if we've got confirmation.
$delete_conformation_fail = false;
if ($delete === md5($course->timemodified)) {
    if ($delete_conform === "DELETE") {
        // We do - time to delete the course.
        require_sesskey();

        $strdeletingcourse = get_string("deletingcourse", "", $courseshortname);

        $PAGE->navbar->add($strdeletingcourse);
        $PAGE->set_title("$SITE->shortname: $strdeletingcourse");
        // $PAGE->set_heading($SITE->fullname);

        echo $OUTPUT->header();
        echo $OUTPUT->heading($strdeletingcourse);
        // This might take a while. Raise the execution time limit.
        core_php_time_limit::raise();
        // We do this here because it spits out feedback as it goes.
        echo "<div id='delete-course-page'>";
        delete_course($course);
        echo $OUTPUT->heading(get_string("deletedcourse", "", $courseshortname));
        // Update course count in categories.
        fix_course_sortorder();
        echo "<div id='course-delete-cotinue'>";
        echo $OUTPUT->continue_button($return_course_completion);
        echo "</div>";
        echo "</div>";
        echo '
        <script>
        var continue_button = document.querySelector("#course-delete-cotinue .continuebutton > form");
        if (continue_button) {
            var page_body = document.getElementById("page-skilllab-course-delete");
            page_body.style.display = "none";
            continue_button.submit();
        }
        </script>
        ';
        echo $OUTPUT->footer();
        exit;
        // We must exit here!!!
    } else {
        $delete_conformation_fail = true;
    }
}

$strdeletecheck = get_string("deletecheck", "", $courseshortname);
$PAGE->navbar->add($strdeletecheck);
$PAGE->set_title("$SITE->shortname: $strdeletecheck");
// $PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
// Only let user delete this course if there is not an async backup in progress.
if (!async_helper::is_async_pending($id, 'course', 'backup')) {
    $strdeletecoursecheck = get_string("deletecoursecheck");
    $message = "<p>Course Name: {$coursefullname} ({$courseshortname}) </p> <p>{$strdeletecoursecheck} </p> ";
    $message .= ($delete_conformation_fail) ? ' <bred> <p class="delete-conformation-fail">"DELETE" conformation failed </p>' : '';
    $continueurl = new moodle_url('/course/delete', array('id' => $course->id, 'delete' => md5($course->timemodified)));
    $continuebutton = new single_button($continueurl, get_string('delete'), 'post');
    // echo $OUTPUT->confirm($message, $continuebutton, $return_course_url);
    echo check_delete($course->id, md5($course->timemodified), $message, $return_course_url);
} else {
    // Async backup is pending, don't let user delete course.
    echo $OUTPUT->notification(get_string('pendingasyncerror', 'backup'), 'error');
    echo $OUTPUT->container(get_string('pendingasyncdeletedetail', 'backup'));
    echo $OUTPUT->continue_button($return_course_url);
}
echo $OUTPUT->footer();
exit;
