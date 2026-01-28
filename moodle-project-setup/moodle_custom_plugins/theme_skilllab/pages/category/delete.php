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
 * Course and category management interfaces.
 *
 * @package    core_course
 * @copyright  2013 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//this file is the customize of course/management.php 
// to delete the course category from skilllab theme 

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

$categoryid = required_param('categoryid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);


$url = new moodle_url('/theme/skilllab/pages/category/delete.php');
$systemcontext = $context = context_system::instance();

if ($categoryid) {
    $courseid = null;
    $course = null;
    $category = core_course_category::get($categoryid);
    $context = context_coursecat::instance($category->id);
    $url->param('categoryid', $category->id);
}

if (!core_course_category::has_capability_on_any(array('moodle/category:manage', 'moodle/course:create'))) {
    // The user isn't able to manage any categories. Lets redirect them to the relevant course/index.php page.
    $url = new moodle_url('/course/index.php');
    if ($categoryid) {
        $url->param('categoryid', $categoryid);
    }
    redirect($url);
}

$strmanagement = new lang_string('coursecatmanagement');
$pageheading = $category->get_formatted_name();


// This is a system level page that operates on other contexts.
// Set PAGE variables.
$PAGE->set_context($context);
require_login();
$PAGE->set_url($url);
$PAGE->set_pagetype('skilllab-category-delete');
$page_title = 'Delete category';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// If the user poses any of these capabilities then they will be able to see the admin
// tree and the management link within it.
// This is the most accurate form of navigation.
$capabilities = array(
    'moodle/site:config',
    'moodle/backup:backupcourse',
    'moodle/category:manage',
    'moodle/course:create',
    'moodle/site:approvecourse'
);
if ($category && !has_any_capability($capabilities, $systemcontext)) {
    // If the user doesn't poses any of these system capabilities then we're going to mark the category link in the
    // settings block as active, tell the page to ignore the active path and just build what the user would expect.
    // This will at least give the page some relevant navigation.
    navigation_node::override_active_url(new moodle_url('/course/index.php', array('categoryid' => $category->id)));
    $PAGE->set_category_by_id($category->id);
    $PAGE->navbar->ignore_active(true);
} else {
    // If user has system capabilities, make sure the "Category" item in Administration block is active.
    navigation_node::require_admin_tree();
    navigation_node::override_active_url(new moodle_url('/course/index.php'));
}

if ($action !== false && confirm_sesskey()) {
    if ($action == 'deletecategory') {
        // They must have specified a category.
        required_param('categoryid', PARAM_INT);
        if (!$category->can_delete()) {
            throw new moodle_exception('permissiondenied', 'error', '', null, 'core_course_category::can_resort');
        }
        // $continueurl = new moodle_url('/theme/skilllab/pages/category/index.php', ['categoryid' => $categoryid]);
        $continueurl = new moodle_url('/category', ['categoryid' => $categoryid]);

        // Describe the contents of this category.
        $this_catg_contents = '';
        if ($category->has_children()) {
            $this_catg_contents .= html_writer::tag('li', get_string('subcategories'));
        }
        if ($category->has_courses()) {
            $this_catg_contents .= html_writer::tag('li', get_string('courses'));
        }

        // $mform = new core_course_deletecategory_form(null, $category);
        $mform = new \theme_skilllab\form\skl_delete_category(null, $category);
        if ($mform->is_cancelled()) {
            redirect($continueurl);
        }
        // Start output.
        /* @var core_course_management_renderer|core_renderer $renderer */
        $renderer = $PAGE->get_renderer('core_course', 'management');
        echo $renderer->header();
        echo $renderer->heading(get_string('deletecategory', 'moodle', $category->get_formatted_name()));

        if ($data = $mform->get_data()) {
            // The form has been submit handle it.
            if ($data->fulldelete == 1 && $category->can_delete_full()) {
                echo '<div class="delete-content-wrapper">';
                if ($category->parent != '0') {
                    $continueurl->param('categoryid', $category->parent);
                }
                $notification = get_string('coursecategorydeleted', '', $category->get_formatted_name());
                $deletedcourses = $category->delete_full(true);
                foreach ($deletedcourses as $course) {
                    echo $renderer->notification(get_string('coursedeleted', '', $course->shortname), 'notifysuccess');
                }
                echo $renderer->notification($notification, 'notifysuccess');

                echo $renderer->continue_button($continueurl, 'Go back to category list');
                echo '</div>';
            } else if ($data->fulldelete == 0 && $category->can_move_content_to($data->newparent)) {
                // $continueurl = new moodle_url('/course/management.php', array('categoryid' => $data->newparent));
                $category->delete_move($data->newparent, true);
                echo $renderer->continue_button($continueurl, 'Go back to course list');
            } else {
                // Some error in parameters (user is cheating?)
                $mform->display();
            }
        } else {
            echo "<div class = 'category-info'>";
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
  <path d="M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20ZM11 15H13V17H11V15ZM11 7H13V13H11V7Z" fill="#3586A5"/>
</svg>
            ';
            if (!empty($this_catg_contents)) {
                echo "<p>This category is still being used in courses.</p>";
            } else {
                echo "<p class='delete-message'>This category deleted cannot be undone.</p>";
            }

            echo "</div>";
            if (!empty($this_catg_contents)) {
                echo "<div class='delete-action-wrapper'>";
            }

            // Display the form.
            $mform->display();
            if (!empty($this_catg_contents)) {
                echo "</div>";
            }
        }
        // Finish output and exit.

        echo "
        <style>
        .secondary-navigation{
            display:none !important;
        }
        </style>
        ";
        echo $renderer->footer();
        exit();
    }
}
redirect('/course/category');
