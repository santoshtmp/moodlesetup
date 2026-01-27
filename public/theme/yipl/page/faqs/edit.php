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

use theme_yipl\form\faqs_question_form;
use theme_yipl\handler\faqs_handler;

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
$url = new moodle_url('/theme/yipl/page/faqs/edit.php');
$redirect_url = new moodle_url('/theme/yipl/page/faqs/admin.php');
if ($action == 'add') {
    $page_title = 'Add FAQs';
} else if ($action == 'edit') {
    $page_title = 'Edit FAQs';
} else if ($action == 'delete') {
    $page_title = 'Delete FAQs';
} else {
    $page_title = '';
}

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin'); // admin , standard , ...
$PAGE->set_pagetype('admin-yipl-faqs-edit');
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
$faqs_question_form = new faqs_question_form();
if ($faqs_question_form->is_cancelled()) {
    redirect($redirect_url);
} else if ($form_data = $faqs_question_form->get_data()) {
    faqs_handler::save_faqs_question_data($form_data, $url, $redirect_url);
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
            faqs_handler::delete_faqs_question_data($id, $redirect_url);
        }
        // For Edit
        if ($action == 'edit') {
            $faqs_question_form = faqs_handler::edit_faqs_question_form($faqs_question_form, $id, $redirect_url);
        }
    }
}

/**
 * ========================================================
 *     Get the data and display
 * ========================================================
 */
$contents = '';
$contents .= '<div class="faqs-setting-edit ' . $action . '">';
// $contents .= '<div class="header-container"> <h3> Add New FAQs <h3> </div>';
$contents .= $faqs_question_form->render();
$contents .= '</div>';
/**
 * ========================================================
 * -------------------  Output Content  -------------------
 * ========================================================
 */
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
