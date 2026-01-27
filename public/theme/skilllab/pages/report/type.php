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
 * @package    theme_skilllab
 * @copyright  2025 skilllab
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_skilllab\util\UtilCourse_handler;
use theme_skilllab\util\UtilReport_handler;
use theme_skilllab\util\UtilUser_handler;

/**
 * ========================================================
 *             Get Require config. 
 * ========================================================
 */
require_once(dirname(__FILE__) . '/../../../../config.php');
defined('MOODLE_INTERNAL') || die();

/**
 * ========================================================
 *             Get parameters. 
 * ========================================================
 */
$type = optional_param('type', '', PARAM_TEXT);
$page_number = optional_param('page', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$download = optional_param('download', 0, PARAM_INT);
$search_category_id = '';
$per_page_data = 20;

if (!empty($type)) {
    $params = ['type' => strtolower($type)];
    // define page url 
    $page_url = new moodle_url('/theme/skilllab/pages/report/type.php', $params);
    // 
    if ($search) {
        $params['search'] = $search;
    }
    $search_page_url = new moodle_url('/theme/skilllab/pages/report/type.php', $params);
} else {
    redirect('/theme/skilllab/pages/report/type.php?type=course');
}
/**
 * ========================================================
 *              Prepare the page information. 
 * ========================================================
 */
// define page title
$page_title = UtilReport_handler::get_report_page_title($type);
// Get system context.
$context = \context_system::instance();
// set the context for the page
$PAGE->set_context($context);
// Set the URL for the page
$PAGE->set_url($page_url);
// set the Page layout like custompages, frontpage, standard, admin, base, course, incourse .... used by theme
$PAGE->set_pagelayout('admin');
// set the page type
$PAGE->set_pagetype('skilllab_report');
// set page sub pages 
$PAGE->set_subpage((string)$type);
// Setting an appropriate title
$PAGE->set_title($page_title);
// setting a heading
$PAGE->set_heading($page_title);
// Adds a CSS class to the body tag 
$PAGE->add_body_class('skilllab-report');
// Add Page Breadcrumb navigation
$PAGE->navbar->add($page_title, $page_url);
// 
$PAGE->requires->jquery();

/**
 * ========================================================
 *     Access checks.
 * ========================================================
 */
require_login(null, false);

/**
 * ========================================================
 *     Get the data and display
 * ========================================================
 */
 $contents = '';
if (!has_capability('moodle/site:config', $context)) {
    $contents .= "You don't have permission to access this pages";
    $contents .= "<br>";
    $contents .= "<a href='/'> Return Back</a>";
} else {

    $contents = UtilReport_handler::get_report_list();

    if ($type == 'course') {
        $all_course_info = UtilCourse_handler::get_all_course_info(
            $per_page_data,
            $page_number,
            $search,
            $search_category_id
        );
        $pagination = $OUTPUT->paging_bar(
            $all_course_info['meta']['total_record'],
            $page_number,
            $per_page_data,
            $search_page_url
        );
        // 
        $template_content = [
            'course_info' => $all_course_info['data'],
            'has_data' => ($all_course_info['meta']['page_data_count']) ? true : false,
            'pagination' => $pagination,
            'search_form' => UtilReport_handler::get_search_form_content($page_url, [['name' => 'type', 'value' => $type]]),
        ];
        $contents .= $OUTPUT->render_from_template('theme_skilllab/pages/report/course_report', $template_content);
    } elseif ($type == 'user') {
        $all_user_info = UtilUser_handler::get_all_user_info(
            $per_page_data,
            $page_number,
            $search,
        );
        $pagination = $OUTPUT->paging_bar(
            $all_user_info['meta']['total_record'],
            $page_number,
            $per_page_data,
            $search_page_url
        );
        $template_content = [
            'user_info' => $all_user_info['data'],
            'has_data' => ($all_user_info['meta']['page_data_count']) ? true : false,
            'pagination' => $pagination,
            'search_form' => UtilReport_handler::get_search_form_content($page_url, [['name' => 'type', 'value' => $type]]),
        ];
        $contents .= $OUTPUT->render_from_template('theme_skilllab/pages/report/user_report', $template_content);
    } else {
        $contents .= '<div> Please select the type.</div>';
    }
}



/**
 * ========================================================
 * -------------------  Output Content  -------------------
 * ========================================================
 */
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
