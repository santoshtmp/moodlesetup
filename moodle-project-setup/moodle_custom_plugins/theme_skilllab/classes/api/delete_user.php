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
use moodle_exception;

/**
 * User external functions
 *
 * @package    core_user
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since 
 */
class delete_user extends \core_external\external_api
{


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function delete_user_parameters()
    {
        return new external_function_parameters(
            [
                'userid' => new external_value(
                    PARAM_RAW,
                    'Encrypted ID of the user'
                )
            ]
        );
    }

    /**
     * Create one or more users.
     *
     * @throws invalid_parameter_exception
     * @param array $users An array of users to create.
     * @return array An array of arrays
     * @since Moodle 2.2
     */
    public static function delete_user($users)
    {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/user/lib.php");
        require_once($CFG->dirroot . '/theme/skilllab/lib.php');


        // Ensure the current user is allowed to run this function.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:delete', $context);
        $params = self::validate_parameters(
            self::delete_user_parameters(),
            array('userid' => $users)
        );
        // ---------------------------------


        try {
            // var_dump($params);
            if ($params['userid']) {
                $userid = $params['userid'];
                $userid = (int)decrypt($userid);

                $transaction = $DB->start_delegated_transaction();
                $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
                // Must not allow deleting of admins or self!!!
                if (is_siteadmin($user)) {
                    // throw new moodle_exception('useradminodelete', 'error');
                    $return_delete = [
                        'status' => false,
                        'message' => 'given user is an admin, admin cannot be deleted'
                    ];
                    return $return_delete;
                }
                if ($USER->id == $user->id) {
                    // throw new moodle_exception('usernotdeletederror', 'error');
                    $return_delete = [
                        'status' => false,
                        'message' => 'You cannot delete yourself'
                    ];
                    return $return_delete;
                }

                user_delete_user($user);
                $transaction->allow_commit();

                $return_delete = [
                    'status' => true,
                    'message' => 'user sucessfuly delete ; username : ' . $user->username
                ];
            } else {
                $return_delete =
                    [
                        'status' => false,
                        'message' => "error userid"
                    ];
            }
        } catch (\Throwable $th) {
            $return_delete =
                [
                    'status' => false,
                    'message' => $th->getMessage()
                ];
        }
        return $return_delete;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function delete_user_returns()
    {
        return
            new external_single_structure(
                array(
                    'status' => new external_value(PARAM_BOOL, 'status'),
                    'message' => new external_value(PARAM_RAW, 'message'),
                )
            );
    }
}
