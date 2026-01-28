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
 * @package    theme_skilllab
 * @copyright  2024 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * https://moodledev.io/docs/4.4/apis/commonfiles/db-tasks.php
 * 
 */


namespace theme_skilllab\task;

use theme_skilllab\csc_api\update_enroll_user;
use theme_skilllab\local\skl_time_track;
use theme_skilllab\util\UtilNotification_handler;
use theme_skilllab\util\UtilTheme_handler;
use theme_skilllab\util\UtilUser_handler;
use theme_skilllab\util\UtilCourse_handler;

class scheduled_time_duration extends \core\task\scheduled_task {

    /**
     * Task description about this scheduled task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('scheduled_dosomething', 'theme_skilllab');
    }

    /**
     * Execute the scheduled task.
     * Call api or perform action
     */
    public function execute() {
        try {
            global $DB;
            $datas = [];
            $durations = skl_time_track::get_unsync_csc_time_track();
            foreach ($durations  as $key => $duration) {
                $course_id = $duration->course_id;
                $user_id = $duration->user_id;
                if (
                    $DB->record_exists('course', array('id' => $course_id)) &&
                    $DB->record_exists('user', array('id' => $user_id))
                ) {
                    $course = get_course($course_id);
                    $course_customcert = UtilCourse_handler::course_mod_customcert($course_id, $user_id);
                    $progress_percentage = UtilUser_handler::get_user_course_progress($course, $user_id);
                    if ($progress_percentage == 100) {
                        if (!$course_customcert['certificate_issues']) {
                            $customcert_id = isset($course_customcert['customcert_id']) ? $course_customcert['customcert_id'] : '';
                            if ($customcert_id) {
                                \mod_customcert\certificate::issue_certificate($customcert_id, $user_id);
                                $course_customcert = UtilCourse_handler::course_mod_customcert($course_id, $user_id);
                            }
                        }
                    }
                    $datas[] = [
                        'course_id' => $course_id,
                        'moodle_user_id' => UtilTheme_handler::encrypt_decrypt_value($user_id, 'encrypt'),
                        'duration' => skl_time_track::get_user_course_time_duration($course_id, $user_id),
                        'progress_percentage' => $progress_percentage,
                        'certificate_url' => $course_customcert['certificate_url'],
                        'certificate_issues' => $course_customcert['certificate_issues'],
                        'certificate_issues_date' => $course_customcert['certificate_issues_date'],
                    ];
                }
            }
            $post_datas['data'] = $datas;
            if ($datas) {
                $status = update_enroll_user::api_update_enroll_user($post_datas);
                if ($status) {
                    foreach ($durations  as $key => $duration) {
                        $course_id = $duration->course_id;
                        $user_id = $duration->user_id;
                        skl_time_track::undate_sync_csc_time_track($user_id, $course_id);
                    }
                    // 
                    $subject = "scheduled_time_duration";
                    $full_message = " API call Success <br>  \"careerservicelab.com/api/moodle/course/update-enroll-user\" <br> post data =  " . json_encode($post_datas);
                    UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
                } else {
                    $subject = "scheduled_time_duration";
                    $full_message = " API call Failed <br>  \"careerservicelab.com/api/moodle/course/update-enroll-user\" <br> post data =  " . json_encode($post_datas);
                    UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
                }
            } else {
                $subject = "scheduled_time_duration";
                $full_message = " No data <br>  \"careerservicelab.com/api/moodle/course/update-enroll-user\" <br> post data =  " . json_encode($post_datas);
                UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
            }
        } catch (\Throwable $th) {
            $subject = "scheduled_time_duration";
            $full_message = " Process Failed <br>  \"careerservicelab.com/api/moodle/course/update-enroll-user\" <br> message =  " . $th->getMessage();
            UtilNotification_handler::callback_api_fail_notification($subject, $full_message);
        }
    }
}
