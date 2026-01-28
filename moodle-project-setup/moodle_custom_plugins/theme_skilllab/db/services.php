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
 * Theme moove functions and service definitions.
 *
 * @package    theme_skilllab
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    // The name of your web service function
    'skilllab_get_courses' => array(
        // The name of the namespaced class that the function is located in.
        'classname' => 'theme_skilllab\api\get_courses',
        // 'classpath' => 'theme_skilllab/classes/api/get_courses.php',
        'methodname' => 'get_courses',
        'description' => 'return courses information.',
        'type' => 'read',
        'ajax' => true
    ),
    'skilllab_get_course_user_list' => array(
        'classname' => 'theme_skilllab\api\course_user_list',
        'methodname' => 'course_user_list',
        'classpath' => 'theme/skilllab/classes/api/course_user_list.php',
        'description' => 'return list of all user enrolled in particular course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'moodle/course:viewparticipants'
    ),
    'skilllab_get_user_courses_list' => array(
        'classname' => 'theme_skilllab\api\user_courses_list',
        'methodname' => 'user_courses_list',
        'classpath' => 'theme/skilllab/classes/api/user_courses_list.php',
        'description' => 'retuen list of courses in which particular user is enrolled',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'moodle/course:viewparticipants'
    ),
    'skilllab_get_course_certificate' => array(
        'classname' => 'theme_skilllab\api\course_certificate',
        'methodname' => 'course_certificate',
        'classpath' => 'theme/skilllab/classes/api/course_certificate.php',
        'description' => 'check, set and retuen user course certificate info ',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'mod/customcert:view'
    ),
    'skilllab_create_user' => array(
        'classname' => 'theme_skilllab\api\create_users',
        'methodname' => 'create_users',
        'classpath' => 'theme/skilllab/classes/api/create_users.php',
        'description' => 'Create users.',
        'type' => 'write',
        'capabilities' => 'moodle/user:create',
        'ajax' => false,
    ),
    'skilllab_delete_user' => array(
        'classname' => 'theme_skilllab\api\delete_user',
        'methodname' => 'delete_user',
        'classpath' => 'theme/skilllab/classes/api/delete_user.php',
        'description' => 'Create users.',
        'type' => 'write',
        'capabilities' => 'moodle/user:delete',
        'ajax' => false,
    ),
    'skilllab_user_logout' => array(
        'classname' => 'theme_skilllab\api\user_logout',
        'methodname' => 'user_logout',
        'classpath' => 'theme/skilllab/classes/api/user_logout.php',
        'description' => 'user logout.',
        'type' => 'write',
        'ajax' => false,
    ),
    'skilllab_create_manual_enroll' => array(
        'classname'   => 'theme_skilllab\api\manual_enroll',
        'methodname'  => 'manual_enroll',
        'classpath'   => 'theme/skilllab/classes/api/manual_enroll.php',
        'description' => 'Manual enrol users',
        'capabilities' => 'enrol/manual:enrol',
        'type'        => 'write',
    ),
    'skilllab_update_user' => array(
        'classname'   => 'theme_skilllab\api\update_user',
        'methodname'  => 'update_user',
        'classpath'   => 'theme/skilllab/classes/api/update_user.php',
        'description' => 'Update users.',
        'capabilities' => 'moodle/user:update',
        'type'        => 'write',
    ),
    'skilllab_system_role' => array(
        'classname'   => 'theme_skilllab\api\system_role',
        'methodname'  => 'system_role',
        'classpath'   => 'theme/skilllab/classes/api/system_role.php',
        'description' => 'Assign system role.',
        'capabilities' => 'moodle/site:config',
        'type'        => 'write',
    ),
    'skilllab_update_institution_domain' => array(
        'classname'   => 'theme_skilllab\api\update_institution_domain',
        'methodname'  => 'update_institution_domain',
        'classpath'   => 'theme/skilllab/classes/api/update_institution_domain.php',
        'description' => 'Update users institution_domain.',
        'capabilities' => 'moodle/user:update',
        'type'        => 'write',
    ),
    'skilllab_update_institution_name' => array(
        'classname'   => 'theme_skilllab\api\update_institution_name',
        'methodname'  => 'update_institution_name',
        'classpath'   => 'theme/skilllab/classes/api/update_institution_name.php',
        'description' => 'Update users institution_name.',
        'capabilities' => 'moodle/user:update',
        'type'        => 'write',
    ),
    'skilllab_time_track' => array(
        'classname'   => 'theme_skilllab\api\time_track',
        'methodname'  => 'time_track',
        'classpath'   => 'theme/skilllab/classes/api/time_track.php',
        'description' => 'time_track in page.',
        'type'        => 'write',
        'ajax' => true,
    ),
    'skilllab_time_track_reset_start_time' => [
        'classname'   => 'theme_skilllab\api\time_track',
        'methodname'  => 'reset_start_time',
        'classpath'   => 'theme/skilllab/classes/api/time_track.php',
        'description' => 'reset_start_time time_track in page.',
        'type'        => 'write',
        'ajax' => true,
    ],
    'skilllab_time_track_report' => array(
        'classname'   => 'theme_skilllab\api\time_track_report',
        'methodname'  => 'time_track_report',
        'classpath'   => 'theme/skilllab/classes/api/time_track_report.php',
        'description' => 'time_track_report in page.',
        'type'        => 'read',
        'ajax' => true,
    ),
    'skilllab_get_roles' => array(
        'classname'   => 'theme_skilllab\api\get_roles',
        'methodname'  => 'get_roles',
        'classpath'   => 'theme/skilllab/classes/api/get_roles.php',
        'description' => 'get_roles in page.',
        'type'        => 'read',
        'ajax' => true,
    )
);

$services = array(
    'Skill lab CSC Web Services' => array(
        'functions' => array(
            'skilllab_system_role',
            'skilllab_create_manual_enroll',
            'core_user_get_users',
            'skilllab_get_courses',
            'skilllab_get_course_user_list',
            'skilllab_get_user_courses_list',
            'skilllab_get_course_certificate',
            'skilllab_create_user',
            'skilllab_update_user',
            'skilllab_delete_user',
            'skilllab_user_logout',
            'skilllab_update_institution_domain',
            'skilllab_update_institution_name',
            'skilllab_get_roles'
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);
