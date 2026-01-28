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
 * 
 * \core_user_external::get_users()
 */

namespace theme_yipl\api;

use core_external\external_description;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use core_user;
use theme_yipl\util\UtilUser_handler;
use theme_yipl\util\UtilYIPL_handler;

/**
 * 
 */
class users_info extends \core_external\external_api
{


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function users_info_parameters()
    {
        global $CFG;
        return new external_function_parameters(
            array(
                'users' => new external_single_structure(
                    array(
                        'id' => new external_multiple_structure(
                            new external_value(PARAM_RAW, 'user encrypted id'),
                            'List of user encrypted id. If empty return all users.',
                            VALUE_OPTIONAL
                        )
                    ),
                    'users options - operator OR is used',
                    VALUE_DEFAULT,
                    array()
                ),
                'page' => new external_value(
                    PARAM_INT,
                    'page number',
                    VALUE_OPTIONAL
                ),
                'per_page' => new external_value(
                    PARAM_INT,
                    'page size',
                    VALUE_OPTIONAL
                )
            )
        );
    }

    /**
     *
     * @throws invalid_parameter_exception
     * @param array $users An array of users to create.
     * @return array An array of arrays
     */
    public static function users_info($users = [], $page = 1, $per_page = 15)
    {
        global $CFG, $DB;

        // Ensure the current user is allowed to run this function.
        $context = \context_system::instance();
        self::validate_context($context);

        // Do basic automatic PARAM checks on incoming data, using params description.
        // If any problems are found then exceptions are thrown with helpful error messages.
        $params = self::validate_parameters(
            self::users_info_parameters(),
            array(
                'users' => $users,
                'page' => $page,
                'per_page' => $per_page
            )
        );
        $return_Users_Datas = [];
        $status = false;


        //retrieve users
        if (!array_key_exists('id', $params['users']) or empty($params['users']['id'])) {
            // $users = $DB->get_records('user');
            $query = 'SELECT * from {user} user WHERE user.id <> :guest_user_id && user.deleted <> :user_deleted && user.suspended <> :user_suspended && user.auth <> :user_auth';
            $total_count_sql = 'SELECT COUNT(user.id) AS total_count from {user} user WHERE user.id <> :guest_user_id && user.deleted <> :user_deleted && user.suspended <> :user_suspended && user.auth <> :user_auth ';
            $sql_params = [
                'user_deleted' => 1,
                'user_suspended' => 1,
                'guest_user_id' => 1,
                // 'user_auth' => \theme_yipl\util\UtilYIPL_handler::$yipl_auth
            ];

            $page_number = $params['page'];
            $per_page = $params['per_page'];

            $limitfrom = 0;
            $limitnum = $per_page;
            if ($page_number > 1) {
                $limitfrom = $limitnum * ($page_number - 1);
                $limitnum = $limitnum;
            }

            $users = $DB->get_records_sql($query, $sql_params, $limitfrom, $limitnum);
            $total_users = $DB->get_record_sql($total_count_sql, $sql_params);

            $metaInfo = [
                'total_page' => ceil(($total_users->total_count) / $per_page),
                'current_page' => $page_number,
                'per_page' => $per_page
            ];
        } else {
            $user_ids = [];
            foreach ($params['users']['id'] as $key => $id) {
                $user_ids[] = (int)UtilYIPL_handler::encrypt_decrypt_value($id, 'decrypt');
            }
            $users = $DB->get_records_list('user', 'id', $user_ids);
            if (!$users) {
                $return_Users_Datas['message'] = 'Invalid users ids';
            }
        }

        //create return value
        $usersinfo = array();
        $page_data_count = 0;
        foreach ($users as $user) {
            $userinfo = UtilUser_handler::get_user_info($user->id);
            $usersinfo[] = $userinfo;
            $page_data_count++;
            $status = true;
        }
        $metaInfo['page_data_count'] = $page_data_count;
        $return_Users_Datas['data'] = $usersinfo;
        $return_Users_Datas['meta'] = $metaInfo;
        $return_Users_Datas['status'] = $status;

        // var_dump($usersinfo);
        return  $return_Users_Datas;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function users_info_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_BOOL, 'status'),
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id_raw' => new external_value(core_user::get_property_type('id'), 'user id',),
                            'id' => new external_value(PARAM_RAW, 'encrypted user id',),
                            'username' => new external_value(\core_user::get_property_type('username'), 'username'),
                            'email' => new external_value(PARAM_RAW, 'email'),
                            'firstname' => new external_value(PARAM_TEXT, 'firstname', VALUE_OPTIONAL),
                            'lastname' => new external_value(PARAM_TEXT, 'lastname', VALUE_OPTIONAL),
                            'auth' => new external_value(PARAM_TEXT, 'auth', VALUE_OPTIONAL),
                            'phone1' => new external_value(PARAM_RAW, 'phone1', VALUE_OPTIONAL),
                            'phone2' => new external_value(PARAM_RAW, 'phone2', VALUE_OPTIONAL),
                            'institution' => new external_value(PARAM_TEXT, 'institution', VALUE_OPTIONAL),
                            'department' => new external_value(PARAM_TEXT, 'department', VALUE_OPTIONAL),
                            'address' => new external_value(PARAM_TEXT, 'address', VALUE_OPTIONAL),
                            'city' => new external_value(PARAM_TEXT, 'city', VALUE_OPTIONAL),
                            'country' => new external_value(PARAM_TEXT, 'country', VALUE_OPTIONAL),
                            'country_name' => new external_value(PARAM_TEXT, 'country_name', VALUE_OPTIONAL),
                            'lang' => new external_value(PARAM_TEXT, 'lang', VALUE_OPTIONAL),
                            'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                            'profileimageurl' => new external_value(PARAM_URL, 'profileimageurl', VALUE_OPTIONAL),
                            'timezone' => new external_value(PARAM_TEXT, 'timezone', VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT, 'timecreated', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'timemodified', VALUE_OPTIONAL),
                            'firstaccess' => new external_value(PARAM_INT, 'firstaccess', VALUE_OPTIONAL),
                            'lastaccess' => new external_value(PARAM_INT, 'lastaccess', VALUE_OPTIONAL),
                            'lastlogin' => new external_value(PARAM_INT, 'lastlogin', VALUE_OPTIONAL),
                            'interests' => new external_value(PARAM_RAW, 'interests', VALUE_OPTIONAL),
                            'enrolled_courses' => new external_value(PARAM_RAW, 'enrolled_courses', VALUE_OPTIONAL),
                            'customofields' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'type'  => new external_value(PARAM_ALPHANUMEXT, 'The type of the custom field - text field, checkbox...'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the custom field (as stored in the database)'),
                                        'displayvalue' => new external_value(
                                            PARAM_RAW,
                                            'The value of the custom field for display',
                                            VALUE_OPTIONAL
                                        ),
                                        'name' => new external_value(PARAM_RAW, 'The name of the custom field'),
                                        'shortname' => new external_value(PARAM_RAW, 'The shortname of the custom field - to be able to build the field class in the code'),
                                    )
                                ),
                                'User custom fields (also known as user profile fields)',
                                VALUE_OPTIONAL
                            ),
                        )
                    ),
                    'users data',
                    VALUE_OPTIONAL
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
                    'user list meta information ',
                    VALUE_OPTIONAL
                ),
                'message' => new external_value(PARAM_TEXT, 'message', VALUE_OPTIONAL),

            ]
        );
    }
}
