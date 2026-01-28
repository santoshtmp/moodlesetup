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

use theme_skilllab\util\UtilTheme_handler;

defined('MOODLE_INTERNAL') || die();

/**
 * A login page layout for the boost theme.
 *
 * @package   theme_skilllab
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/theme/skilllab/inc/layout_handler/main.php');

$bodyattributes = $OUTPUT->body_attributes();

// check user_csc_site cookies and redirect if present;
global $CFG, $USER;
if (isguestuser() || $USER->id < 2) {
    $cookies_name = 'user_csc_site';
    // check user_csc_site cookies and redirect if present;
    if (isset($_COOKIE[$cookies_name])) {
        $user_csc_site_val = rc4decrypt($_COOKIE[$cookies_name]);
        if ($user_csc_site_val) {
            UtilTheme_handler::unset_skl_theme_cookie($cookies_name);
            redirect($user_csc_site_val);
        }
    }
}

// 
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes
];

$templatecontext = array_merge($templatecontext, UtilTheme_handler::get_pix_url());


if ($this->body_id() == 'page-login-forgot_password') {
    $url = $CFG->wwwroot . '/login/index.php';
    $message = "You cannot change password. Please Contact Site admin";
    redirect($url, $message);
} else {
    echo $OUTPUT->render_from_template('theme_skilllab/layout/login', $templatecontext);
}
