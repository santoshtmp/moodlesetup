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
 * Logs the user out and sends them to the home page
 *
 * @package    core
 * @copyright  2023 yipl skill lab csc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_skilllab\csc_api\csc_api_general;
use theme_skilllab\util\UtilTheme_handler;

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/theme/skilllab/lib.php');
global $USER, $DB;

$PAGE->set_url('/theme/skilllab/pages/login/csc-lms-logout.php');
$PAGE->set_context(\context_system::instance());

$sesskey = optional_param('sesskey', '__notpresent__', PARAM_RAW); // we want not null default to prevent required sesskey warning
$login   = optional_param('loginpage', 0, PARAM_BOOL);

// can be overridden by auth plugins
if ($login) {
    $redirect = get_login_url();
} else {
    $redirect = $CFG->wwwroot . '/';
}

// define redirect url after logout sucessfully
$redirect_after_logout = get_user_csc_redirect();

if (!isloggedin()) {
    // no confirmation, user has already logged out
    if (csc_api_general::logout_csc()) {
        require_logout();
        UtilTheme_handler::unset_skl_theme_cookie('user_csc_site');
        redirect($redirect_after_logout);
    }
} else if (!confirm_sesskey($sesskey)) {
    $PAGE->set_title($SITE->fullname);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('logoutconfirm'), new moodle_url($PAGE->url, array('sesskey' => sesskey())), $CFG->wwwroot . '/');
    echo $OUTPUT->footer();
    die;
}

$authsequence = get_enabled_auth_plugins(); // auths, in sequence
foreach ($authsequence as $authname) {
    $authplugin = get_auth_plugin($authname);
    $authplugin->logoutpage_hook();
}

if (csc_api_general::logout_csc()) {
    require_logout();
    UtilTheme_handler::unset_skl_theme_cookie('user_csc_site');
    redirect($redirect_after_logout);
}

redirect($redirect, 'Logout Fail <br> Due to login fail in CSC');
