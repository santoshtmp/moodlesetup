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

use theme_yipl\handler\faqs_handler;
use theme_yipl\handler\settings_handler;

// Require config.
require_once(dirname(__FILE__) . '/../../../../config.php');
defined('MOODLE_INTERNAL') || die();

// Get parameters.

// Get system context.
$context = \context_system::instance();

// Prepare the page information.
$url = new moodle_url('/theme/yipl/page/faqs/admin.php');
$page_title = 'FAQs Setting';
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin'); // admin , standard , ...
$PAGE->set_pagetype('admin-faqs-setting');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
// $PAGE->navbar->add($page_title);
// $PAGE->requires->js_call_amd();
// $PAGE->requires->css(new moodle_url());
// $PAGE->requires->jquery();
// Access checks.
// admin_externalpage_setup();
require_login(null, false);



/**
 * ========================================================
 *     Get the data and display
 * ========================================================
 */
$contents = "";

$template_content = [
    'admin_faq_url' => $url->out(),
    'add_faq_url' => (new moodle_url('/theme/yipl/page/faqs/edit.php'))->out(),
    'view_faq_url' => (new moodle_url('/theme/yipl/page/faqs/view.php'))->out(),
    'add_faq_category_url' => (new moodle_url('/theme/yipl/page/faqs/category.php'))->out(),
    'faqs_data_table' => faqs_handler::get_faqs_question_data_in_table($url, -1),
    'yipl_faqs' => settings_handler::setting('yipl_faqs'),
    'search_form'=>faqs_handler::get_search_form(),
];

$contents .= $OUTPUT->render_from_template('theme_yipl/pages/faqs/admin', $template_content);

/**
 * ========================================================
 * -------------------  Output Content  -------------------
 * ========================================================
 */
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
