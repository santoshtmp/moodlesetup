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
use core_external\external_format_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use core_external\external_warnings;
use core_user;
use tool_usertours\local\filter\role;

/**
 * User external functions
 *
 * @package    core_user
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class system_role extends \core_external\external_api
{
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function system_role_parameters()
    {
        return new external_function_parameters(
            array(
                'assign' => new  external_single_structure(
                    array(
                        'role'    => new external_value(
                            PARAM_TEXT,
                            'Role short-name to assign to the user'
                        ),
                        'userid'    => new external_value(
                            PARAM_RAW,
                            'encrypted user id'
                        )
                    )
                )
            )
        );
    }

    /**
     * Manual role assign to users
     *
     * @param array $assign An array of manual role assign
     */
    public static function system_role($assign)
    {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/theme/skilllab/lib.php');


        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages
        $params = self::validate_parameters(
            self::system_role_parameters(),
            array('assign' => $assign)
        );

        $execute_data = [];
        $execute_data['status'] = false;

        $transaction = $DB->start_delegated_transaction();

        foreach (array($params['assign']) as $assign) {

            // decrypt the given user id vale
            $encrypt_user_id = $assign['userid'];
            $addusers = (int)decrypt($assign['userid']);
            $addrole = $assign['role'];

            // First check the user exists.
            if (!$existinguser = core_user::get_user($addusers)) {
                $execute_data['message'] = 'invalid user id';
                return $execute_data;
            }

            if ($addrole == 'admin') {
                $admins = array();
                foreach (explode(',', $CFG->siteadmins) as $admin) {
                    $admin = (int)$admin;
                    if ($admin) {
                        $admins[$admin] = $admin;
                    }
                    if ($addusers === $admin) {
                        $execute_data['message'] = 'user is alrady admin';
                        return $execute_data;
                    }
                }

                $logstringold = implode(', ', $admins);
                $admins[$addusers] = $addusers;
                $logstringnew = implode(', ', $admins);

                set_config('siteadmins', implode(',', $admins));
                add_to_config_log('siteadmins', $logstringold, $logstringnew, 'core');
            } else {

                // Ensure correct context level with a instance id or contextid is passed.
                $assign['contextid'] = 1;
                $context = self::get_context_from_params($assign);

                // Ensure the current user is allowed to run this function in the enrolment context.
                self::validate_context($context);
                require_capability('moodle/role:assign', $context);

                // throw an exception if user is not able to assign the role in this context 
                // By defalut allow manager and coursecreator
                $roles = get_assignable_roles($context, ROLENAME_SHORT);
                $addrole_id = 0;
                foreach ($roles as $key => $role) {
                    if ($role == $addrole) {
                        $addrole_id = $key;
                    }
                }

                if (!array_key_exists($addrole_id, $roles)) {
                    $execute_data['message'] = 'can not assign this role in system level; role allowed admin, manager and coursecreator';
                    return $execute_data;
                }

                $ras = $DB->get_records('role_assignments', array('roleid' => $addrole_id, 'contextid' => $context->id, 'userid' => $addusers, 'component' => '', 'itemid' => 0), 'id');
                if ($ras) {
                    $execute_data['message'] = $addrole . ' role is alrady assign to this user';
                    return $execute_data;
                } else {
                    role_assign($addrole_id, $addusers, $context->id);
                }
            }
        }

        $transaction->allow_commit();

        $execute_data['status'] = true;
        $execute_data['message'] =  $addrole . ' added sucessfylly';
        return $execute_data;
    }

    /**
     * Returns description of method result value
     *
     * @return null
     */
    public static function system_role_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status'),
                'message' => new external_value(PARAM_RAW, 'message')
            )
        );
    }
}
