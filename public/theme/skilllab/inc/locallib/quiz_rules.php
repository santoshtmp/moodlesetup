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

defined('MOODLE_INTERNAL') || die();

/**
 * $interval_time = "24 hours" or "1 day"
 * $attemptbtn = default 
 * $attemptobjs = $viewobj->attemptobjs
 */
function get_quiz_attemp_in_interval($attemptobjs,  $attemptbtn, $interval_time = "24 hours")
{
    global $COURSE, $USER, $OUTPUT;
    $count = $return_next_time = 0;
    $content =  $attemptbtn;
    $role_shortname = UtilUser_handler::get_user_roles();
    $student_user_only = false;
    if (
        in_array('student', $role_shortname) &&
        in_array('auth_user', $role_shortname) &&
        (sizeof($role_shortname) == 2)
    ) {
        $student_user_only = true;
    }
    if ($student_user_only) {
        foreach ($attemptobjs as $attemptobj) {
            // $interval_date = new DateTime($interval_time, core_date::get_server_timezone_object());
            $next_time = strtotime('+' . $interval_time, $attemptobj->get_submitted_date());
            if ($next_time > time()) {
                $count++;
                $return_next_time = $next_time;
            }
        }

        if ($count >= 3) {
            $message = ' You have finished your attempts for today. Please try again after ' . html_writer::span(userdate($return_next_time));
            $content = html_writer::div(html_writer::span("<img src='" . $OUTPUT->image_url('icons/lock', 'theme') . "' alt='lock-icon'>") . $message, "quiz-re-attempt-lock");
        }
        if (UtilUser_handler::get_user_course_progress($COURSE, $USER->id) == 100) {
            $message = " The course has been completed and certificate has been issued so cannot retake the quiz. ";
            $content = html_writer::div(html_writer::span("<img src='" . $OUTPUT->image_url('icons/lock', 'theme') . "' alt='lock-icon'>") . $message, "quiz-re-attempt-lock certificate-released");
            // $content = html_writer::div(html_writer::span($OUTPUT->svg_content('lock')) . $message, "quiz-re-attempt-lock certificate-released");
        }
    }
    $return_data = [
        'count' => $count,
        'next_time' => $return_next_time,
        'content' => $content
    ];
    return $return_data;
}

/**
 * 
 */
function course_completeion_restrict_attempt()
{
    global $COURSE, $USER;
    $role_shortname = UtilUser_handler::get_user_roles();
    $student_user_only = false;
    if (
        in_array('student', $role_shortname) &&
        in_array('auth_user', $role_shortname) &&
        (sizeof($role_shortname) == 2)
    ) {
        $student_user_only = true;
    }
    if (UtilUser_handler::get_user_course_progress($COURSE, $USER->id)  == 100 &&  $student_user_only) {
        $cmid = required_param('cmid', PARAM_INT);
        $url = new moodle_url(
            '/mod/quiz/view.php',
            array('id' => $cmid)
        );
        $message = "You are not allowed to attempt quiz as you have already completed the course 100%";
        redirect($url, $message);
    }
}


/**
 * 
 */
function quiz_summary_finishattempt_single()
{
    global $PAGE;
    $contents = '';
    /** 
     * For mod-quiz-summary pages 
     * function to directly jump to quiz review page 
     */
    if ($PAGE->pagetype === 'mod-quiz-summary') {
        ob_start();
?>
        <script type="text/javascript">
            var quiz_summery = document.getElementById("page-mod-quiz-summary");
            if (quiz_summery) {
                var submit_btn = document.querySelector('#frm-finishattempt button');
                submit_btn.addEventListener("click", function() {
                    var finish_attempt = document.getElementById("frm-finishattempt");
                    finish_attempt.submit();
                });
            }
        </script>
        <style>
            .modal,
            .modal-backdrop {
                display: none !important;
            }
        </style>
<?php
        $contents .=  ob_get_contents();
        ob_end_clean();
    }

    return $contents;
}
