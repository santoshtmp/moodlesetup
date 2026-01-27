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
 * @package   theme_yipl   
 * @copyright 2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\api;

use core\exception\moodle_exception;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_user;
use theme_yipl\util\UtilYIPL_handler;

class users_delete extends \core_external\external_api
{

    /**
     * Returns description of method parameters
     *
     */
    public static function users_delete_parameters()
    {
        return new external_function_parameters(
            array(
                'userid' =>  new external_value(
                    PARAM_RAW,
                    'encrypted user ID',
                    VALUE_OPTIONAL
                ),
            )
        );
    }

    /**
     * Delete users
     *
     * @throws moodle_exception
     * @param array $userid
     * @return null
     * @since Moodle 2.2
     */
    public static function users_delete($userid = '')
    {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/user/lib.php");

        // Ensure the current user is allowed to run this function.
        $context = \context_system::instance();
        require_capability('moodle/user:delete', $context);
        self::validate_context($context);

        $params = self::validate_parameters(self::users_delete_parameters(), array('userid' => $userid));

        $userDelete = [
            'status' => false,
            'message' => 'failed to delete user',
            'exception' => true
        ];

        try {
            $userid = isset($params['userid']) ? $params['userid'] : '';
            $userid = (int)UtilYIPL_handler::encrypt_decrypt_value($userid, 'decrypt');
            if ($userid) {

                $existinguser = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
                // Must not allow deleting of admins or self!!!
                if ($existinguser) {
                    $transaction = $DB->start_delegated_transaction();

                    if (is_siteadmin($existinguser)) {
                        // throw new moodle_exception('useradminodelete', 'error');
                        $userDelete['message'] = 'Admin user cannot be deleted.';
                        return $userDelete;
                    }
                    if ($USER->id == $existinguser->id) {
                        // throw new moodle_exception('usernotdeletederror', 'error');
                        $userDelete['message'] = 'cannot delete self.';
                        return $userDelete;
                    }
                    // check auth is valid as yipl
                    if ($existinguser->auth != \theme_yipl\util\UtilYIPL_handler::$yipl_auth) {
                        $userDelete['message'] = 'account auth type: ' . $existinguser->auth . ', only allowed to delete account auth type: ' . \theme_yipl\util\UtilYIPL_handler::$yipl_auth;
                        return $userDelete;
                    }
                    user_delete_user($existinguser);
                    $transaction->allow_commit();

                    $userDelete = [
                        'status' => true,
                        'message' => 'sucessfully user deleted',
                        'exception' => true
                    ];
                } else {
                }
            } else {
                $userDelete['message'] = 'user id is required.';
                return $userDelete;
            }
        } catch (\Throwable $th) {
            //throw $th;
            $userDelete['message'] = $th->getMessage();
        }

        return $userDelete;
    }

    /**
     * Returns description of method result value
     *
     */
    public static function users_delete_returns()
    {
        // return null;
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'exception' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            )
        );
    }
}
