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

use theme_yipl\handler\custom_pages_handler;
use theme_yipl\handler\settings_handler;
use theme_yipl\local\theme_pages;

// Require config.
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

// Get parameters.

// Get system context.
$context = \context_system::instance();

// Prepare the page information.
$url = new moodle_url('/theme/yipl/page/admin.php');
$page_title = get_string('custom_pages', 'theme_yipl');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin'); // admin , standard , ...
$PAGE->set_pagetype('admin-yipl-pages-setting');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
// $PAGE->navbar->add($page_title);
// $PAGE->requires->js_call_amd();
// $PAGE->requires->css(new moodle_url());
// navbar
// Access checks.
// admin_externalpage_setup();
require_login(null, false);
// Add Page Breadcrumb navigation
$PAGE->navbar->add($PAGE->heading, $PAGE->url);


/**
 * ========================================================
 *     Get the data and display
 * ========================================================
 */
$contents = "";

$template_content = [
    'admin_url' => $url->out(),
    'add_url' => (new moodle_url('/theme/yipl/page/edit.php'))->out(),
    'custom_pages_data_table' => custom_pages_handler::get_data_in_table($url, -1),
    'yipl_custom_pages' => settings_handler::setting('yipl_custom_pages'),
    'search_form' => custom_pages_handler::get_search_form(),
];

$contents .= $OUTPUT->render_from_template('theme_yipl/pages/admin', $template_content);

/**
 * ========================================================
 * -------------------  Output Content  -------------------
 * ========================================================
 */
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
