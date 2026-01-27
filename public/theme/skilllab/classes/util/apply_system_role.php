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
 * Theme helper to load a theme configuration.
 *
 * @package    theme_skilllab
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\util;

class apply_system_role
{
    /**
     * @param adduser_id verified user id
     * @param string $addroles role short name
     */
    public function add($adduser_id, $addroles)
    {
        // 
        $status = true;
        $message = '';
        // $context =  \context::instance_by_id(1, IGNORE_MISSING);
        $systemcontext = \context_system::instance();
        $user_roles_exist = UtilUser_handler::get_user_roles($adduser_id, $systemcontext);
        // 
        $addrole_list = explode(',', $addroles);
        foreach ($addrole_list as $key => $add_role) {
            if ($add_role == 'admin') {
                $update_role = $this->system_admin($adduser_id, 'add');
            } else {
                $update_role = $this->system_other_role($adduser_id, $add_role, 'add');
            }
            if (!$update_role['status']) {
                $status = false;
            }
            $message .= $update_role['message'];

            if (in_array($add_role, $user_roles_exist)) {
                $user_roles_exist = array_values(array_diff($user_roles_exist, [$add_role]));
            }
        }
        if ($user_roles_exist) {
            // remove other existinig roles
            foreach ($user_roles_exist as $key => $remove_role) {
                if ($remove_role == 'admin') {
                    $this->system_admin($adduser_id, 'remove');
                } else {
                    $this->system_other_role($adduser_id, $remove_role, 'remove');
                }
            }
        }

        return [
            'status' => $status,
            'message' => $message
        ];
    }

    /**
     * $adduser_id verified user id
     * $action = 'add' or 'remove'
     */
    protected function system_admin($adduser_id, $action)
    {
        global $CFG;
        try {
            $admins = [];
            foreach (explode(',', $CFG->siteadmins) as $admin) {
                $admin = (int)$admin;
                if ($action == 'add' && $adduser_id === $admin) {
                    $data = [
                        'status' => false,
                        'message' => "user is alrady admin \n "
                    ];
                    return $data;
                }
                if ($action == 'remove' && $adduser_id === $admin) {
                    continue;
                }
                if ($admin) {
                    $admins[$admin] = $admin;
                }
            }

            $logstringold = $CFG->siteadmins;
            if ($action == 'add') {
                $admins[$adduser_id] = $adduser_id;
            }
            $logstringnew = implode(',', $admins);

            if ($logstringold != $logstringnew) {
                set_config('siteadmins', implode(',', $admins));
                add_to_config_log('siteadmins', $logstringold, $logstringnew, 'core');
            }

            $data = [
                'status' => true,
                'message' => "admin role added sucessfylly \n "
            ];
            return $data;
        } catch (\Exception $e) {
            $data = [
                'status' => false,
                'message' => $e->getMessage()
            ];
            return $data;
        }
    }


    /**
     * @param adduser_id verified user id
     * @param addrole role short name
     * $action = 'add' or 'remove'
     */
    protected function system_other_role($adduser_id, $addrole, $action)
    {
        global $DB;

        try {
            // Ensure correct context level with a instance id or contextid is passed.
            $contextid = 1;
            $context =  \context::instance_by_id($contextid, IGNORE_MISSING);

            // Ensure the current user is allowed to run this function in the enrolment context.
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
            if ($addrole_id == 0) {
                $data = [
                    'status' => false,
                    'message' => "undefined role OR You can not assign this role:" . $addrole . " \n "
                ];
                return $data;
            }

            if (!array_key_exists($addrole_id, $roles)) {
                $data = [
                    'status' => false,
                    'message' => "can not assign this role in system level; by default role allowed admin, manager and coursecreator. \n "
                ];
                return $data;
            }
            if ($action == 'add') {
                $ras = $DB->get_records('role_assignments', array('roleid' => $addrole_id, 'contextid' => $context->id, 'userid' => $adduser_id, 'component' => '', 'itemid' => 0), 'id');
                if ($ras) {
                    $data = [
                        'status' => true,
                        'message' => $addrole . " role is alrady assign to this user. \n "
                    ];
                    return $data;
                } else {
                    role_assign($addrole_id, $adduser_id, $context->id);
                    $data = [
                        'status' => true,
                        'message' =>  $addrole . "role added sucessfylly. \n "
                    ];
                    return $data;
                }
            }
            if ($action == 'remove') {
                role_unassign($addrole_id, $adduser_id, $context->id);
            }
        } catch (\Exception $e) {
            $data = [
                'status' => false,
                'message' => $e->getMessage()
            ];
            return $data;
        }
    }
}
