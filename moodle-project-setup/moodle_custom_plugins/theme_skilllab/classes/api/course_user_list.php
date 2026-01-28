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
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use theme_skilllab\util\UtilUser_handler;
use theme_skilllab\util\UtilCourse_handler;

/**
 * User external functions
 *
 * @package    core_user
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class course_user_list extends \core_external\external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.9
     */
    public static function course_user_list_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of the course, 0 for site'),
                'page' => new external_value(PARAM_INT, 'page number', VALUE_OPTIONAL)
            )
        );
    }

    /**
     * return list of user enrolled in course as studnet
     * @param int $courseid id of course
     * @return array of course participatient as student role result
     */
    public static function course_user_list($courseid, $page = 1) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/theme/skilllab/lib.php');
        $course_user_list = [];

        $params = self::validate_parameters(
            self::course_user_list_parameters(),
            array(
                'courseid' => $courseid,
                'page' => $page
            )
        );

        $per_page = 10;
        $page_number =  $params['page'];
        $limitfrom = 0;
        $limitnum = $per_page;
        if ($page_number > 1) {
            $limitfrom = $limitnum * ($page_number - 1);
            $limitnum = $limitnum;
        }

        if ($DB->record_exists('course', array('id' => $params['courseid']))) {
            $course = get_course($params['courseid']);
            $context = \context_course::instance($course->id);
            self::validate_context($context);

            // course_require_view_participants($context);
            // get all enrolled user as STUDENT in the course
            // 'moodle/course:isincompletionreports' - this capability is allowed to only students.
            // $get_enrolled_users = get_enrolled_users($context, 'moodle/course:isincompletionreports');
            $get_all_enrolled_users = get_enrolled_users($context);
            $get_enrolled_users = get_enrolled_users(
                $context,
                $withcapability = '',
                $groupids = 0,
                $userfields = 'u.*',
                $orderby = null,
                $limitfrom,
                $limitnum,
                $onlyactive = false
            );
            $metaInfo = [
                'total_page' => ceil(count($get_all_enrolled_users) / $per_page),
                'current_page' => $page_number,
                'per_page' => $per_page
            ];
            $page_data_count = 0;
            foreach ($get_enrolled_users as $key => $enrolled_user) {

                // get user enrollment data in the course
                $userenrolments = UtilUser_handler::course_user_enrolments($course->id, $enrolled_user->id);
                foreach ($userenrolments as $key => $value) {
                    $enrollment_status = ($value->status == 0) ? 'Active' : 'Suspended';
                    $enrollment_date = $value->timecreated;
                }
                // // get enrolled user role in the course
                $get_user_roles = get_user_roles($context, $enrolled_user->id);
                $user_roles = [];
                foreach ($get_user_roles as $key => $role) {
                    $user_roles[] = $role->shortname;
                }
                // arrange the data
                $user_list['user_id'] = $enrolled_user->id;
                $user_list['firstname'] = $enrolled_user->firstname;
                $user_list['lastname'] = $enrolled_user->lastname;
                $user_list['email'] = $enrolled_user->email;
                $user_list['course_roles'] = json_encode($user_roles);
                $user_list['enrollment_status'] = $enrollment_status;
                $user_list['enrollment_date'] = $enrollment_date;

                // 
                $course_customcert = UtilCourse_handler::course_mod_customcert($course->id);
                $progress_percentage = UtilUser_handler::get_user_course_progress($course, $enrolled_user->id);
                if ($progress_percentage == 100) {
                    if (!$course_customcert['certificate_issues']) {
                        $customcert_id = isset($course_customcert['customcert_id']) ? $course_customcert['customcert_id'] : '';
                        if ($customcert_id) {
                            \mod_customcert\certificate::issue_certificate($customcert_id, $user_id);
                            $course_customcert = UtilCourse_handler::course_mod_customcert($course->id, $enrolled_user->id);
                        }
                    }
                }
                $user_list['progress_percentage'] = $progress_percentage;
                $user_list['certificate_url'] = $course_customcert['certificate_url'];
                $user_list['certificate_issues'] = $course_customcert['certificate_issues'];
                $user_list['certificate_issues_date'] = $course_customcert['certificate_issues_date'];
                $user_list['certificate_issues_code'] = $course_customcert['certificate_issues_code'];

                $course_user_list[] = $user_list;
                $page_data_count++;
            }
        }

        $metaInfo['page_data_count'] = $page_data_count;
        return ['data' => $course_user_list, 'meta' => $metaInfo];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.9
     */
    public static function course_user_list_returns() {
        return  new external_single_structure([
            'data' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'user_id' => new external_value(PARAM_INT, 'user id'),
                        'firstname' => new external_value(PARAM_RAW, 'user first name'),
                        'lastname' => new external_value(PARAM_RAW, 'user last name'),
                        'email' => new external_value(PARAM_RAW, 'user email'),
                        'progress_percentage' => new external_value(PARAM_INT, 'user course progress percentage'),
                        'enrollment_status' => new external_value(PARAM_RAW, 'user enrollment_status'),
                        'enrollment_date' => new external_value(PARAM_INT, 'user enrollment_date'),
                        'course_roles' => new external_value(PARAM_RAW, 'user course roles in jsonencode string'),
                        'certificate_url' => new external_value(PARAM_RAW, 'course certificate url'),
                        'certificate_issues' => new external_value(PARAM_BOOL,'certificated issues or not'),
                        'certificate_issues_date' => new external_value(PARAM_INT,'date time when the course certificate generated and issues'),
                        'certificate_issues_code' => new external_value(PARAM_RAW,'certificate code'),
                    )
                )
            ),

            'meta' => new external_single_structure(
                [
                    'total_page' => new external_value(
                        PARAM_INT,
                        'total page number',
                        VALUE_OPTIONAL
                    ),
                    'current_page' => new external_value(
                        PARAM_INT,
                        'current page number',
                        VALUE_OPTIONAL
                    ),
                    'per_page' => new external_value(
                        PARAM_INT,
                        'Number of data shown per page',
                        VALUE_OPTIONAL
                    ),
                    'page_data_count' => new external_value(
                        PARAM_INT,
                        'current page data count',
                        VALUE_OPTIONAL
                    ),
                ],
                'course list meta information ',
                VALUE_OPTIONAL
            )
        ]);
    }
}
