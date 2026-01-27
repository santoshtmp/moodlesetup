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
 *
 * @package    theme_yipl
 * @copyright  2025 YIPL
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_yipl\form\testimonial_form;
use theme_yipl\handler\testimonial_handler;

// Require config.
require_once(dirname(__FILE__) . '/../../../../config.php');
defined('MOODLE_INTERNAL') || die();

/**
 *  Get parameter
 */
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 'add', PARAM_TEXT); // action = add or edit or delete

// Get system context.
$context = \context_system::instance();

// Prepare the page information.
$url = new moodle_url('/theme/yipl/page/testimonial/edit.php');
$redirect_url = new moodle_url('/theme/yipl/page/testimonial/admin.php');
if ($action == 'add') {
    $page_title = 'Add Testimonial';
} else if ($action == 'edit') {
    $page_title = 'Edit Testimonial';
} else if ($action == 'delete') {
    $page_title = 'Delete Testimonial';
} else {
    $page_title = '';
}

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin'); // admin , standard , ...
$PAGE->set_pagetype('admin-yipl-testimonial-edit');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
// $PAGE->navbar->add($page_title);
// $PAGE->requires->js_call_amd();
// $PAGE->requires->css(new moodle_url());
$contents = "";

// Access checks.
// admin_externalpage_setup();
require_login(null, false);


/**
 * ========================================================
 *     FORM actions
 * ========================================================
 */
$testimonial_form = new testimonial_form();
if ($testimonial_form->is_cancelled()) {
    redirect($redirect_url);
} else if ($form_data = $testimonial_form->get_data()) {
    testimonial_handler::save_data($form_data, $url, $redirect_url);
} else {
    if ($action && $id) {
        // verify sesskey
        $sesskey = required_param('sesskey', PARAM_ALPHANUM);
        if ($sesskey != sesskey()) {
            $message = "Your session key is missing or invalid.";
            redirect($redirect_url, $message);
        }
        if (!$page_title) {
            $message = "Unknown action param.";
            redirect($redirect_url, $message);
        }
        // For Delete
        if ($action == 'delete') {
            testimonial_handler::delete_data($id, $redirect_url);
        }
        // For Edit
        if ($action == 'edit') {
            $testimonial_form = testimonial_handler::edit_form($testimonial_form, $id, $redirect_url);
        }
    }
}

/**
 * ========================================================
 *     Get the data and display
 * ========================================================
 */
$contents = '';
$contents .= '<div class="testimonial-setting-edit ' . $action . '">';
$contents .= $testimonial_form->render();
$contents .= '</div>';
/**
 * ========================================================
 * -------------------  Output Content  -------------------
 * ========================================================
 */
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
