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

use core_external\external_description;
use core_external\external_value;
use core_external\external_format_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use core_external\external_warnings;
use core_user;
use stdClass;
use theme_yipl\util\UtilYIPL_handler;


/**
 * users_logout external functions
 *
 * @copyright  2025 YIPL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users_logout extends \core_external\external_api
{
    /**
     * Returns description of method parameters
     *
     */
    public static function users_logout_parameters()
    {
        return new external_function_parameters(
            array(
                'userid' => new external_value(
                    PARAM_RAW,
                    'encrypted id of the user',
                    VALUE_OPTIONAL
                )
            )
        );
    }

    /**
     * 
     */
    public static function users_logout($userid = [])
    {
        global $DB;
        $delete_session = '';
        $logout_data = new stdClass;
        $logout_data->status = false;

        $params = self::validate_parameters(
            self::users_logout_parameters(),
            array(
                'userid' => $userid
            )
        );

        // get the parameters 
        $userid = isset($params['userid']) ? $params['userid'] : '';
        if (! $userid) {
            $logout_data->message = "user id is required.";
            return $logout_data;
        }

        // decrypt the encrypted user id;
        $userid = (int)UtilYIPL_handler::encrypt_decrypt_value($userid, 'decrypt');

        $sql_query = 'SELECT *
            FROM {sessions} sessions 
            WHERE sessions.userid = :userid 
            Order By sessions.id DESC
        ';
        $sql_params = [
            'userid' => $userid
        ];
        $all_session = $DB->get_records_sql($sql_query, $sql_params);
        if ($all_session) {
            foreach ($all_session as $key => $each_session) {
                $sid = $each_session->sid;
                if ($sid) {
                    $delete_session = $DB->delete_records('sessions', array('sid' => $sid));
                    // if ($delete_session) {
                    // Store info that gets removed during logout.
                    $event = \core\event\user_loggedout::create(
                        array(
                            'userid' => $userid,
                            'objectid' => $userid,
                            'other' => array('sessionid' => $sid),
                        )
                    );
                    if ($session = $DB->get_record('sessions', array('sid' => $sid))) {
                        $event->add_record_snapshot('sessions', $session);
                    }
                    \core\session\manager::init_empty_session();
                    // Trigger event AFTER action.
                    $event->trigger();
                    // }
                }
            }
        }

        if ($delete_session) {
            $logout_data->status = true;
            $logout_data->message = "user sucessfully logout.";
        } else {
            $logout_data->message = "something went wrong during logout.";
        }

        return $logout_data;
    }

    /**
     * Returns description of method result value
     */
    public static function users_logout_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'exception' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            )
        );
    }
}
