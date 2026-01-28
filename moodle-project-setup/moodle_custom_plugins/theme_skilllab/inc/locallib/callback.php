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
 * @package   local_skilllab   
 * @copyright  2023 yipl skill lab csc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_skilllab\util\UtilUser_handler;

//  1. https://docs.moodle.org/dev/Callbacks
//  2. https://docs.moodle.org/dev/Output_callbacks 

defined('MOODLE_INTERNAL') || die();

/**
 * From moodle 4.4 callback are managed through callback hook => theme_skilllab\hooks\hook_callbacks
 * https://moodledev.io/docs/4.5/apis/core/hooks
 * https://docs.moodle.org/dev/Output_callbacks#before_http_headers
 */
function theme_skilllab_before_http_headers()
{
    global $CFG;
    if (during_initial_install() || isset($CFG->upgraderunning)) {
        // Do nothing during installation or upgrade.
        return;
    }
    \theme_skilllab\util\UtilTheme_handler::security_header();
}

/**
 * Callback before_standard_html_head
 *
 * @return string
 */
function theme_skilllab_before_standard_html_head()
{
    // set page specific css and js file which are present in dist/css and js directory
    \theme_skilllab\util\UtilTheme_handler::set_skilllab_css_js();
    return "";
}

/**
 * Callback allowing to add contetnt inside the region-main, in the very end
 *
 * @return string
 */
function theme_skilllab_before_footer()
{
    global $COURSE, $USER, $PAGE;
    $role_shortname = UtilUser_handler::get_user_roles();
    $student_user_only = false;
    if (
        in_array('student', $role_shortname) &&
        in_array('auth_user', $role_shortname) &&
        (sizeof($role_shortname) == 2)
    ) {
        $student_user_only = true;
    }
    $before_footer_contents = '';

    //For course and incourse pages
    $user_course_progress = UtilUser_handler::get_user_course_progress($COURSE, $USER->id);
    if (($PAGE->pagelayout === 'course' ||
            $PAGE->pagelayout === 'incourse') &&
        ($user_course_progress == 100) &&
        $student_user_only
    ) {
        ob_start();
?>
        <script type="text/javascript">
            var completion_button = document.querySelectorAll('button[data-action="toggle-manual-completion"]');
            if (completion_button) {
                completion_button.forEach((item) => {
                    item.disabled = true;
                });
            }
        </script>
<?php
        $before_footer_contents .= ob_get_contents();
        ob_end_clean();
    }

    $before_footer_contents .= quiz_summary_finishattempt_single();
    return $before_footer_contents;
}


/**
 * 
 * https://docs.moodle.org/dev/Login_callbacks#after_config
 */
function theme_skilllab_after_config()
{
    global $CFG;
    $CFG->local_adminer_secret = "csc-lms-@2025-YIPL";
}