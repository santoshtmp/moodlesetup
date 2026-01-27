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

use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use stdClass;

// // You can redirect the user to yipl-lms.local where it sets the moodle session cookie and then redirects back from where it is login in:
// header("Location: https://yipl-lms.local/set-cookie.php?redirect=https://yipl.com");
// exit;
// // Then, on yipl-lms.local, after setting the cookie, redirect back:
// if (isset($_GET['redirect'])) {
//     header("Location: " . $_GET['redirect']);
//     exit;
// }


// OR use API to set moodle session cookies

/**
 * users_login external functions
 *
 * @copyright  2025 YIPL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users_login extends \core_external\external_api
{
    /**
     * Returns description of method parameters
     *
     */
    public static function users_login_parameters()
    {
        return new external_function_parameters(
            array(
                'userid' => new external_value(
                    PARAM_RAW,
                    'id of the user',
                    VALUE_OPTIONAL
                )
            )
        );
    }

    /**
     * 
     */
    public static function users_login($userid = [])
    {
        global $DB;
        $login_data = new stdClass;
        $login_data->status = false;

        $params = self::validate_parameters(
            self::users_login_parameters(),
            array(
                'userid' => $userid
            )
        );

        // get the parameters 
        $userid = isset($params['userid']) ? $params['userid'] : '';
        if (! $userid) {
            $login_data->message = "user id is required.";
            return $login_data;
        }


        // decrypt the encrypted user id;
        // $userid = (int)UtilYIPL_handler::encrypt_decrypt_value($userid, 'decrypt');
        // setcookie(
        //     "MoodleSession", "9pvd41e5rmtnn2nk9lfts6nap7", [
        //     "expires" => time() + 3600,
        //     "path" => "/",
        //     "domain" => "yipl-lms.local",
        //     "secure" => true,  // Use `true` if using HTTPS
        //     "httponly" => true,
        //     "samesite" => "None"
        // ]);



        return $login_data;
    }

    /**
     * Returns description of method result value
     */
    public static function users_login_returns()
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
