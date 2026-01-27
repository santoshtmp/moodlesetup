<?php

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/theme/skilllab/lib.php');

global $OUTPUT, $PAGE;

require_login();


// Set PAGE variables.
$context = context_system::instance();
$PAGE->set_context($context);

// $PAGE->set_url($CFG->wwwroot . '/pages/about-us.php');
$PAGE->set_url(new moodle_url('/theme/skilllab/pages/category/index.php'));

$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('skilllab-category-index');

$page_title = 'Category List';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);

// 
$PAGE->navbar->add($page_title);

$PAGE->requires->jquery();

// Adds a CSS class to the body tag 
$strcssclass = 'csc-category-ist';
$PAGE->add_body_class($strcssclass);

// -------------------
if (!has_capability('moodle/course:create', $context)) {
    if (!isguestuser() || $USER->id > 2) {
        redirect('/my/courses.php');
    }
}
// -----------------

// get theme about_page_content content
// $theme = theme_config::load('skilllab');

$output = '';

$course_category_list = new \theme_skilllab\util\course_category_list();
$output .= $course_category_list->output_html();

// page header
echo $OUTPUT->header();

echo $output;

echo $OUTPUT->footer();
