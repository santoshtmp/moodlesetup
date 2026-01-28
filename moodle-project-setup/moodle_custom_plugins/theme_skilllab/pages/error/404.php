<?php

use theme_skilllab\util\UtilTheme_handler;

require_once(dirname(__FILE__) . '/../../../../config.php');

global $OUTPUT, $PAGE;
$url = '/theme/skilllab/pages/error/404.php';
$redirect_status = ($_SERVER['REDIRECT_STATUS'] === '403') ? "403" : http_response_code();
if ($redirect_status === '403') {
    $page_title =    "Forbidden Page";
} else {
    $page_title =    "Page Not Found";
}


// Set PAGE variables.
$context = context_system::instance();
$PAGE->set_context($context);
$url = new moodle_url($url);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($page_title);
// $PAGE->set_heading($page_title);
$PAGE->set_pagetype('skilllab-404');

$PAGE->navbar->add($page_title);
$PAGE->requires->jquery();

// Adds a CSS class to the body tag 
$strcssclass = $redirect_status . '-page';
$PAGE->add_body_class($strcssclass);

// output content

$context = [
    'title' => $page_title,
    "status_code" => $redirect_status,
    'skilllab_active' => "Skill lab"
];
$context = array_merge($context, UtilTheme_handler::get_pix_url());

$templatename = 'theme_skilllab/skilllab_pages/404';
// page header
echo $OUTPUT->header();
echo $OUTPUT->render_from_template($templatename, $context);;
echo $OUTPUT->footer();
