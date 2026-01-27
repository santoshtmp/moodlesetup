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
 * @copyright  2023 yipl skill lab csc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\api;

use core_course\external\course_summary_exporter;
use core_external\external_api;
use core_external\external_files;
use core_external\external_format_value;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use core_external\util;
use Exception;
use moodle_exception;

use stdClass;


class manual_enroll extends external_api
{
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function manual_enroll_parameters()
    {
        return new external_function_parameters(
            array(
                'enrolments' => new external_single_structure(
                    array(
                        'roleid' => new external_value(
                            PARAM_INT,
                            'Role to assign to the user'
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
    public static function manual_enroll($enrolments)
    {
        global $DB, $CFG;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->dirroot . '/theme/skilllab/lib.php');

        $params = self::validate_parameters(
            self::manual_enroll_parameters(),
            array('enrolments' => $enrolments)
        );

        $transaction = $DB->start_delegated_transaction();
        // Rollback all enrolment if an error occurs
        // (except if the DB doesn't support it).

        $enrollDatas = [];
        $enrollDatas['status'] = false;

        // Retrieve the manual enrolment plugin.
        $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            $enrollDatas['message'] = 'enrollment process is not installed';
            return $enrollDatas;
        }

        foreach (array($params['enrolments']) as $enrolment) {
            // decrypt the given user id vale
            $enrolment['userid'] = (int)decrypt($enrolment['userid']);
            // check if user, course and role exist
            $course_doesnot = true;
            if ($DB->record_exists('course', array('id' => $enrolment['courseid']))) {
                $course_doesnot = false;
            }
            if ($course_doesnot) {
                $enrollDatas['message'] = 'course does not exist';
                return $enrollDatas;
            }

            $user_doesnot = true;
            if ($DB->record_exists('user', array('id' => $enrolment['userid']))) {
                $user_doesnot = false;
            }
            if ($user_doesnot) {
                $enrollDatas['message'] = 'user does not exist';
                return $enrollDatas;
            }
            // Ensure the current user is allowed to run this function in the enrolment context.
            $context = \context_course::instance($enrolment['courseid'], IGNORE_MISSING);
            self::validate_context($context);

            // Check that the user has the permission to manual enrol.
            require_capability('enrol/manual:enrol', $context);

            // Throw an exception if user is not able to assign the role.
            $roles = get_assignable_roles($context);
            if (!array_key_exists($enrolment['roleid'], $roles)) {
                $enrollDatas['message'] = 'role does not exist';
                return $enrollDatas;
            }

            // Check manual enrolment plugin instance is enabled/exist.
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
                    // return $enrollDatas;
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
        }

        $transaction->allow_commit();

        return $enrollDatas;
    }

    /**
     * Returns description of method result value.
     *
     * @return null
     * @since Moodle 2.2
     */
    public static function manual_enroll_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status'),
                'message' => new external_value(PARAM_RAW, 'message'),
            )
        );
    }
}
