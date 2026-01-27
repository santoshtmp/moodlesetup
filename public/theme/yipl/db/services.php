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
 * Theme functions and service definitions.
 *
 * @package    theme_yipl
 * @copyright  2025 YIPL
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * 
 * https://moodledev.io/docs/4.5/apis/subsystems/external
 * https://moodledev.io/docs/4.5/apis/subsystems/external/description
 * https://moodledev.io/docs/4.5/apis/subsystems/external/functions
 * https://moodledev.io/docs/4.5/apis/subsystems/external/advanced/custom-services
 * 
 * efd0cc60b99f125eec116fc4c41ce477
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'yipl_users_create' => [
        'classname' => 'theme_yipl\api\users_create',
        'methodname' => 'users_create',
        'description' => 'yipl create users.',
        'type' => 'write',
        'capabilities' => 'moodle/user:create',
        'ajax' => false,
    ],
    'yipl_users_update' => [
        'classname' => 'theme_yipl\api\users_update',
        'methodname' => 'users_update',
        'description' => 'yipl update users.',
        'type' => 'write',
        'capabilities' => 'moodle/user:update',
        'ajax' => false,
    ],
    'yipl_users_delete' => [
        'classname' => 'theme_yipl\api\users_delete',
        'methodname' => 'users_delete',
        'description' => 'yipl delete users.',
        'type' => 'write',
        'capabilities' => 'moodle/user:delete',
        'ajax' => false,
    ],
    'yipl_users_logout' => [
        'classname' => 'theme_yipl\api\users_logout',
        'methodname' => 'users_logout',
        'description' => 'yipl users logout.',
        'type' => 'write',
        'ajax' => false,
    ],
    'yipl_courses_list' => array(
        'classname' => 'theme_yipl\api\courses_list', // The name of the namespaced class that the function is located in.
        'methodname' => 'courses_list',
        'description' => 'return courses list information.',
        'type' => 'read',
        'ajax' => false
    ),
    'yipl_courses_enroll' => array(
        'classname' => 'theme_yipl\api\courses_enroll', // The name of the namespaced class that the function is located in.
        'methodname' => 'courses_enroll',
        'description' => 'enroll the user into course',
        'type' => 'write',
        'ajax' => false
    ),
    'yipl_users_info' => array(
        'classname' => 'theme_yipl\api\users_info', // The name of the namespaced class that the function is located in.
        'methodname' => 'users_info',
        'description' => 'return users list information.',
        'type' => 'read',
        'ajax' => false
    ),
    'yipl_timetrack' => array(
        'classname'   => 'theme_yipl\api\timetrack',
        'methodname'  => 'timetrack',
        'description' => 'yipl timetrack in course activity and rescource pages.',
        'type'        => 'write',
        'ajax' => true,
    ),
];

$services = [
    // The name of the service.
    'YIPL Web Services' => [
        // A list of external functions available in this service.
        'functions' => [
            'yipl_users_create',
            'yipl_users_update',
            'yipl_users_delete',
            'yipl_users_logout',
            'yipl_courses_list',
            'yipl_courses_enroll',
            'yipl_users_info',
            'yipl_timetrack'
        ],
        // If enabled, the Moodle administrator must link a user to this service from the Web UI.
        'restrictedusers' => 0,
        // Whether the service is enabled by default or not.
        'enabled' => 1,
        // This field os optional, but requried if the `restrictedusers` value is
        // set, so as to allow configuration via the Web UI.
        'shortname' =>  'yipl_services',
        // Whether to allow file downloads.
        'downloadfiles' => 0,
        // Whether to allow file uploads.
        'uploadfiles'  => 0,
    ]
];
