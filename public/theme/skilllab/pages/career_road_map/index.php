<?php

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/theme/skilllab/lib.php');

// required login 
require_login();

// Set PAGE variables.
$context = \context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/theme/skilllab/pages/career_road_map/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('career-road-map-course-type');
$page_title = 'Career Road Map';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
$PAGE->navbar->add($page_title);
// Adds a CSS class to the body tag 
$strcssclass = 'all-career-road-map-course-type';
$PAGE->add_body_class($strcssclass);
$PAGE->requires->js('/theme/skilllab/assets/js/course_filter_section.js');
$PAGE->requires->js_call_amd('theme_skilllab/course/courses_apply_filter', 'init');
//
$course_type = 'Career road map';
$skl_course_list_table = new \theme_skilllab\util\course_list($course_type);
$courses_table_html = $skl_course_list_table->skl_course_list_out();

$table_only = optional_param('table_only', false, PARAM_BOOL);
if ($table_only) {
    echo $courses_table_html;
    die();
}

echo $OUTPUT->header();
echo  $courses_table_html;
echo $OUTPUT->footer();
