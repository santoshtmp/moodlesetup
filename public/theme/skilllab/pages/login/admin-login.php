<?php

// require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../../../../config.php');

global $CFG, $SITE, $PAGE;
$sitename = format_string(
    $SITE->fullname,
    true,
    ['context' => \context_course::instance(SITEID), "escape" => false]
);
$admin_login = 'admin login';

// Set PAGE variables.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/theme/skilllab/pages/login/admin-login.php'));
$PAGE->set_pagelayout('login');
$PAGE->set_title($sitename . ': Log in to the site');
$PAGE->set_heading($admin_login);
$PAGE->set_pagetype('skilllab-lms-admin-login');
$PAGE->navbar->add($admin_login);
$PAGE->requires->jquery();
$PAGE->set_pagetype('lms-admin-login');

// Adds a CSS class to the body tag 
$strcssclass = 'moodle-admin-login';
$PAGE->add_body_class($strcssclass);

$theme = theme_config::load('skilllab');
$site_environment = $theme->settings->site_environment; 
// site_environment (0,1,2)=(staging, live, local)
// if ($site_environment == 1) {
//     redirect('/login/index.php');
// }
// ----------------------
$maintenance = '';
if ($CFG->maintenance_enabled == true) {
    if (!empty($CFG->maintenance_message)) {
        $maintenance = $CFG->maintenance_message;
    } else {
        $maintenance = get_string('sitemaintenance', 'admin');
    }
}
// ---------template variable-------------
$context = [];
$context['sitename'] = $sitename;
$context['loginurl_action'] = $CFG->wwwroot . '/theme/skilllab/pages/login/csc-lms-login.php';
$context['admin_login'] = true;
$context['logintoken'] = \core\session\manager::get_login_token();
$context['maintenance'] = format_text($maintenance, FORMAT_MOODLE);

// page header
echo $OUTPUT->header();
// echo ".....................................";
if (isloggedin() and !isguestuser()) {
    // prevent logging when already logged in, we do not want them to relogin by accident because sesskey would be changed
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url('/theme/skilllab/pages/login/csc-lms-logout.php', array('sesskey' => sesskey(), 'loginpage' => 1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('alreadyloggedin', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
} else {
    echo $OUTPUT->render_from_template('theme_skilllab/skilllab_pages/lms-admin-login', $context);
}

echo $OUTPUT->footer();
