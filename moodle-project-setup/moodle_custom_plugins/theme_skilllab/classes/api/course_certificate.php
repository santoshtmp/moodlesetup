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

namespace theme_skilllab\api;

use core_external\external_description;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use theme_skilllab\util\UtilUser_handler;
use theme_skilllab\util\UtilCourse_handler;
use theme_skilllab\util\UtilTheme_handler;

/**
 * External API to issue or get course certificate.
 *
 * @package theme_skilllab
 */
class course_certificate extends \core_external\external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function course_certificate_parameters() {

        return new external_function_parameters(
            array(
                'course_id' => new external_value(PARAM_INT, 'ID of the course, 0 for site'),
                'user_id' => new external_value(PARAM_RAW, 'Encrypted ID of the user'),
                'certificate_date' => new external_value(PARAM_RAW, 'To set course certificate generate date', VALUE_OPTIONAL, '')
            )
        );
    }

    /**
     * return list of user enrolled in course as studnet
     * @param int $course_id
     * @param string $user_id
     * @param int $certificate_date
     * @return array
     */
    public static function course_certificate($course_id, $user_id, $certificate_date = 0) {

        global $DB;
        // set the default return data
        $return_data = [
            'status' => false,
            'message' => '',
        ];

        // Get the parameters.
        $params = self::validate_parameters(
            self::course_certificate_parameters(),
            array(
                'course_id' => $course_id,
                'user_id' => $user_id,
                'certificate_date' => $certificate_date
            )
        );
        $course_id = (int)$params['course_id'];
        $user_id = (int)UtilTheme_handler::encrypt_decrypt_value($params['user_id'], 'decrypt');
        $certificate_date = $params['certificate_date'] ? $params['certificate_date'] : time();

        // Validate user ID
        if (!$DB->record_exists('user', ['id' => $user_id])) {
            $return_data['message'] = 'Invalid user ID.';
            return $return_data;
        }

        // Validate course ID
        if (!$DB->record_exists('course', ['id' => $course_id])) {
            $return_data['message'] = 'Invalid course ID.';
            return $return_data;
        }

        // Validate certificate date
        if ($certificate_date) {
            if (!is_numeric($certificate_date)) {
                $return_data['message'] = 'Certificate date must be a valid Timestamp number.';
                return $return_data;
            }
            $certificate_date = (int)$certificate_date;
            $minTimestamp = 978307200;   // 2001-01-01
            $maxTimestamp = 4133894399;  // 2100-12-31
            if ($certificate_date < $minTimestamp || $certificate_date > $maxTimestamp) {
                $return_data['message'] = 'Provided certificate date is out of valid range. It must be between ' .
                    $minTimestamp . ' (approx. ' . UtilUser_handler::get_user_date_time($minTimestamp) . ') and ' .
                    $maxTimestamp . ' (approx. ' . UtilUser_handler::get_user_date_time($maxTimestamp) . ').';
                return $return_data;
            }
        }


        $course = get_course($params['course_id']);
        $context = \context_course::instance($course->id);
        self::validate_context($context);

        // Check user is student in the course
        $student_role = false;
        $get_user_roles = get_user_roles($context, $user_id);
        foreach ($get_user_roles as $key => $role) {
            if (($role->roleid == 5)) {
                $student_role =  true;
            }
        }
        if (!$student_role) {
            $return_data['message'] = 'User is not enrolled as a student in this course.';
            return $return_data;
        }

        // Check if the course is completed as 100 percentage.
        $progress_percentage = UtilUser_handler::get_user_course_progress($course, $user_id);
        if ($progress_percentage < 100) {
            $return_data['message'] = 'User have not completed the course as 100 percentage.';
            return $return_data;
        }

        // Get or issue certificate
        $course_customcert = UtilCourse_handler::course_mod_customcert($course->id, $user_id);
        if (!$course_customcert['certificate_issues']) {
            $customcert_id = isset($course_customcert['customcert_id']) ? $course_customcert['customcert_id'] : '';
            if ($customcert_id) {
                // \mod_customcert\certificate::issue_certificate($customcert_id, $user_id);
                $issue = new \stdClass();
                $issue->userid = $user_id;
                $issue->customcertid = $customcert_id;
                $issue->code = \mod_customcert\certificate::generate_code();
                $issue->emailed = 0;
                $issue->timecreated = $certificate_date;
                // Insert the record into the database.
                $DB->insert_record('customcert_issues', $issue);
                // Again get the course customcert data after issuing the certificate.
                $course_customcert = UtilCourse_handler::course_mod_customcert($course->id, $user_id);
            } else {
                $return_data['message'] = 'Certificate not configured for this course.';
                return $return_data;
            }
        }

        if ($course_customcert['mod_id'] && $course_customcert['customcert_id']) {
            $return_data['status'] = true;
            $return_data['certificate_data'] = [
                'mod_id' => (int) ($course_customcert['mod_id'] ?? ''),
                'customcert_id' => (int) ($course_customcert['customcert_id'] ?? ''),
                'certificate_url' => (string) ($course_customcert['certificate_url'] ?? ''),
                'certificate_url_download' => (string) ($course_customcert['certificate_url_download'] ?? ''),
                'certificate_issues' => (bool) ($course_customcert['certificate_issues'] ?? false),
                'certificate_issues_date' => (int) ($course_customcert['certificate_issues_date'] ?? 0),
                'certificate_issues_code' => (string) ($course_customcert['certificate_issues_code'] ?? ''),
            ];
            $return_data['message'] = 'course certificate data.';
            return $return_data;
        } else {
            $return_data['message'] = 'Failed to issue certificate for this course.';
            return $return_data;
        }

        return $return_data;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function course_certificate_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'certificate_data' => new external_single_structure(
                    [
                        'mod_id' => new external_value(PARAM_INT, 'Module ID of customcert'),
                        'customcert_id' => new external_value(PARAM_INT, 'Customcert ID'),
                        'certificate_url' => new external_value(PARAM_RAW, 'URL to view the certificate'),
                        'certificate_url_download' => new external_value(PARAM_RAW, 'URL to download the certificate'),
                        'certificate_issues' => new external_value(PARAM_BOOL, 'Whether the certificate is issued'),
                        'certificate_issues_date' => new external_value(PARAM_INT, 'Issue date timestamp'),
                        'certificate_issues_code' => new external_value(PARAM_RAW, 'Certificate issue code'),
                    ],
                    'Detailed certificate data',
                    VALUE_OPTIONAL
                ),
            )
        );
    }
}
