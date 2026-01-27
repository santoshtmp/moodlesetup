<?php

require_once(dirname(__FILE__) . '/../../../../config.php');

global $OUTPUT, $PAGE, $DB;
$theme = theme_config::load('skilllab');
$skl_time_track = $theme->settings->skl_time_track;
if (!$skl_time_track) {
    redirect('/');
}

$course_id      = optional_param('course_id', 0, PARAM_INT);
$user_id      = optional_param('user_id', 0, PARAM_INT);

// Set PAGE variables.
$context = \context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/theme/skilllab/pages/report/time_track.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('skilllab-time-track-report');
$page_title = 'User time track report';
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
$PAGE->navbar->add($page_title);
// Adds a CSS class to the body tag 
$strcssclass = 'time_track_report';
$PAGE->add_body_class($strcssclass);
// $PAGE->requires->js_call_amd('theme_skilllab/time_track/report', 'init');
//
require_login();
if (!has_capability('moodle/site:config', $context)) {
    redirect('/');
    die;
}

// course_id=260&user_id=3348
if ($course_id and $DB->record_exists('user', array('id' => $user_id))) {
    $course_context = context_course::instance($course_id);
    $get_user_roles = get_user_roles($course_context, $user_id);
    $user_roles = [];
    // var_dump($get_user_roles);
    if ($get_user_roles) {
        foreach ($get_user_roles as $key => $role) {
            if ($role->roleid == '5') {
                $student_user = true;
            }
        }
    } else {
        // echo "user is not enrolled in course";
        $user_id = 0;
    }
} else {
    // echo "user does not exist";
    $user_id = 0;
}
//------------------------------------------
$skl_time_track_report = new \theme_skilllab\util\time_track_report();
$output_table = $skl_time_track_report->output();
if ($user_id and $course_id) {
    $output_table .= $skl_time_track_report->output($course_id, $user_id);
} elseif ($course_id) {
    $output_table .= $skl_time_track_report->output($course_id);
}

// -----------------------------------------

$table_only = optional_param('table_only', false, PARAM_BOOL);
if ($table_only) {
    echo $output_table;
    exit();
}
// ---------------------------------------
// page header
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('theme_skilllab/skilllab_pages/user-time-track', ['initial_call' => true]);
echo $output_table;
echo $OUTPUT->render_from_template('theme_skilllab/skilllab_pages/user-time-track', ['final_call' => true]);

echo $OUTPUT->footer();
