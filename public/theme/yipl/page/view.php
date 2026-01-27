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

/**
 * ========================================================
 *             Get Require config. 
 * ========================================================
 */
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();

/**
 * ========================================================
 *             Get parameters. 
 * ========================================================
 */
$id = optional_param('id', 0, PARAM_INT);
if (!empty($id)) {
    $params = ['id' => $id];
} else {
    throw new \moodle_exception('course_page_id', 'theme_yipl');
}
$page_title = 'YIPL Page';

$page_data = custom_pages_handler::get_page_data($id);
if ($page_data) {
    $page_title = $page_data->title;
    $page_id  = $page_data->id;
} else {
    throw new \moodle_exception('missin_page_data', 'theme_yipl');
}


/**
 * ========================================================
 *              Prepare the page information. 
 * ========================================================
 */
// Get system context.
$context = \context_system::instance();
// define page url 
$page_url = new moodle_url('/theme/yipl/page/view.php', ['id' => $page_id]);
// set the context for the page
$PAGE->set_context($context);
// Set the URL for the page
$PAGE->set_url($page_url);
// set the Page layout like custompages, frontpage, standard, admin, base, course, incourse .... used by theme
$PAGE->set_pagelayout('custompages');
// set the page type
$PAGE->set_pagetype('custom-page');
// set page sub pages 
$PAGE->set_subpage((string)$page_id);
// Setting an appropriate title
$PAGE->set_title($page_title);
// setting a heading
$PAGE->set_heading($page_title);
// Adds a CSS class to the body tag 
$this_page_class = str_replace([' '], '-', strtolower($page_data->short_name));
$PAGE->add_body_class('custom-pages ' . $this_page_class);
// set an additional capability able edit blocks on this page.
$strcapability = 'moodle/site:manageblocks';
$PAGE->set_blocks_editing_capability($strcapability);
// include css and js for this page
// $PAGE->requires->jquery();
// $PAGE->requires->js_call_amd('','');
// $PAGE->requires->js(new moodle_url());
// $PAGE->requires->css(new moodle_url());
// Add Page Breadcrumb navigation
if (is_siteadmin()) {
    $PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
    $PAGE->navbar->add(get_string('pluginname', 'theme_yipl'), new moodle_url('/admin/category.php?category=yipladmin_general'));
    $PAGE->navbar->add("Custom Pages", new moodle_url('/theme/yipl/page/admin.php'));
}
$PAGE->navbar->add($PAGE->heading, $PAGE->url);
// Add Page Secondary navigation
if (is_siteadmin()) {
    $PAGE->secondarynav->add('View All Pages', new moodle_url('/theme/yipl/page/admin.php'));
    $PAGE->secondarynav->add('Add New', new moodle_url('/theme/yipl/page/edit.php'));
    $PAGE->secondarynav->add('Edit Page', new moodle_url('/theme/yipl/page/edit.php', ['id' => $page_id, "action" => "edit", "sesskey" => sesskey()]));
    $PAGE->secondarynav->add(get_string('configtitle', 'theme_yipl'), new moodle_url('/admin/settings.php?section=themesettingyipl'));
}
/**
 * ========================================================
 *     Access checks.
 * ========================================================
 */


/**
 * ========================================================
 *     Get the data and display
 * ========================================================
 */
$contents = "";
$yipl_custom_pages = settings_handler::setting('yipl_custom_pages');
if ($yipl_custom_pages == '0' && !is_siteadmin()) {
    $contents = "Page is disable to view. ";
    $PAGE->set_pagelayout('');
} else {
    if ($page_data->status == '1') {
        $template_content = [];
        $template_content = [
            'page_data' => $page_data,
            'is_admin' => is_siteadmin()
        ];
        $contents .= $OUTPUT->render_from_template('theme_yipl/pages/view', $template_content);
    } else {
        $contents = "Page is not published yet. ";
        $PAGE->set_pagelayout('');
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
