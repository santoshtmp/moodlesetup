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
 * courses_enroll API endpoints
 * 
 * @package   theme_yipl   
 * @copyright 2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\api;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use theme_yipl\util\UtilYIPL_handler;

/**
 * courses_enroll external functions
 *
 * @copyright  2025 YIPL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_enroll extends external_api
{
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function courses_enroll_parameters()
    {
        return new external_function_parameters(
            array(
                'enrolments' => new external_single_structure(
                    array(
                        'roleid' => new external_value(
                            PARAM_INT,
                            'Role to assign to the user',
                            VALUE_DEFAULT,
                            5
                        ),
                        'userid' => new external_value(
                            PARAM_RAW,
                            'The user that is going to be enrolled: encrypted value'
                        ),
                        'courseid' => new external_value(
                            PARAM_INT,
                            'The course to enrol the user role in'
                        ),
                        'timestart' => new external_value(
                            PARAM_INT,
                            'Timestamp when the enrolment start',
                            VALUE_OPTIONAL
                        ),
                        'timeend' => new external_value(
                            PARAM_INT,
                            'Timestamp when the enrolment end',
                            VALUE_OPTIONAL
                        ),
                        'suspend' => new external_value(
                            PARAM_INT,
                            'set to 1 to suspend the enrolment',
                            VALUE_OPTIONAL
                        )
                    )
                )
            )
        );
    }

    /**
     * Enrolment of users.
     *
     * Function throw an exception at the first error encountered.
     * @param array $enrolments  An array of user enrolment
     * @since Moodle 2.2
     */
    public static function courses_enroll($enrolments)
    {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');

        $params = self::validate_parameters(
            self::courses_enroll_parameters(),
            array('enrolments' => $enrolments)
        );
        $enrollDatas = [];
        $enrollDatas['status'] = false;

        try {
            // Rollback all enrolment if an error occurs
            // (except if the DB doesn't support it).
            $transaction = $DB->start_delegated_transaction();

            $enrolment = $params['enrolments'];
            // Ensure the current user is allowed to run this function in the enrolment context.
            $context = \context_course::instance($enrolment['courseid'], IGNORE_MISSING);
            self::validate_context($context);

            // decrypt the given encrypted user id vale
            $enrolment['userid'] = (int)UtilYIPL_handler::encrypt_decrypt_value($enrolment['userid'], 'decrypt');

            // check if course exist
            if (!$DB->record_exists('course', array('id' => $enrolment['courseid']))) {
                $enrollDatas['message'] = 'course does not exist';
                return $enrollDatas;
            }
            // check if user exist
            if (!$DB->record_exists('user', array('id' => $enrolment['userid']))) {
                $enrollDatas['message'] = 'Unauthenticate user - user does not exist';
                return $enrollDatas;
            }

            // // check if role exist
            $roles = get_assignable_roles($context);
            if (!array_key_exists($enrolment['roleid'], $roles)) {
                $enrollDatas['message'] = 'role does not exist';
                return $enrollDatas;
            }

            // Retrieve the manual enrolment plugin.
            $enrol = enrol_get_plugin('manual');
            if (empty($enrol)) {
                $enrollDatas['message'] = 'enrollment process is not installed';
                return $enrollDatas;
            }

            // Check manual enrolment plugin instance is enabled/exist.
            // Check that the user has the permission to manual enrol.
            require_capability('enrol/manual:enrol', $context);
            $instance = null;
            $enrolinstances = enrol_get_instances($enrolment['courseid'], true);
            foreach ($enrolinstances as $courseenrolinstance) {
                if ($courseenrolinstance->enrol == "manual") {
                    $instance = $courseenrolinstance;
                    break;
                }
            }
            if (empty($instance)) {
                $enrollDatas['message'] = 'enrollment process is not allowed';
                return $enrollDatas;
            }

            // Check that the plugin accept enrolment 
            //(it should always the case, it's hard coded in the plugin).
            if (!$enrol->allow_enrol($instance)) {
                $enrollDatas['message'] = 'enrollment is not allowed';
                return $enrollDatas;
            }

            // check if user is alrady enrolled in course;
            // check given user is student in the course
            // $student_role = false;
            $get_user_roles = get_user_roles($context, $enrolment['userid']);
            foreach ($get_user_roles as $key => $role) {
                if ($role->roleid == $enrolment['roleid']) {
                    $enrollDatas['message'] = 'Given user is alrady enrolled as roleid : ' . $enrolment['roleid'];
                    $enrollDatas['status'] = true;
                    return $enrollDatas;
                }
            }

            // Finally proceed the enrolment.
            $enrolment['timestart'] = isset($enrolment['timestart']) ? $enrolment['timestart'] : 0;
            $enrolment['timeend'] = isset($enrolment['timeend']) ? $enrolment['timeend'] : 0;
            $enrolment['status'] = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ?
                ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;

            $enroll_user = $enrol->enrol_user(
                $instance,
                $enrolment['userid'],
                $enrolment['roleid'],
                $enrolment['timestart'],
                $enrolment['timeend'],
                $enrolment['status']
            );


            $enrollDatas['status'] = true;
            $enrollDatas['message'] = $enroll_user . 'user enrolled sucessfully in courseid ' . $enrolment['courseid'] . ' with roleid ' . $enrolment['roleid'];

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            try {
                $transaction->rollback($e);
            } catch (\Exception $ee) {
                $errmsg = $ee->getMessage();
                $enrollDatas['status'] = false;
                $enrollDatas['exception'] = true;
                $enrollDatas['message'] = $errmsg;
                return $enrollDatas;
            }
            $errmsg = $e->getMessage();
            $enrollDatas['status'] = false;
            $enrollDatas['exception'] = true;
            $enrollDatas['message'] = $errmsg;
            return $enrollDatas;
        }
        return $enrollDatas;
    }

    /**
     * Returns description of method result value.
     *
     * @return null
     * @since Moodle 2.2
     */
    public static function courses_enroll_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'exception' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL)
            )
        );
    }
}
