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
 * @author     santoshtmp7 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//  https://docs.moodle.org/dev/Events_API

/* 
eventname – fully qualified event class name or "*" indicating all events, ex.: \plugintype_pluginname\event\something_happened.
callback - PHP callable type.
includefile - optional. File to be included before calling the observer. Path relative to dirroot.
priority - optional. Defaults to 0. Observers with higher priority are notified first.
internal - optional. Defaults to true. Non-internal observers are not called during database transactions, but instead after a successful commit of the transaction. 
*/

defined('MOODLE_INTERNAL') || die();

$observers = [
    // course module updated
    [
        'eventname'   => '\core\event\course_module_updated',
        'callback'    => '\theme_yipl\event\observer::module_updated',
    ],
    // course module created
    [
        'eventname'   => '\core\event\course_module_created',
        'callback'    => '\theme_yipl\event\observer::module_created',
    ],
    // // user course view
    // [
    //     'eventname' => '\core\event\course_viewed',
    //     'callback' => '\theme_yipl\event\observer::course_viewed',
    // ],
    // // user enrollment created
    // [
    //     'eventname'   => '\core\event\user_enrolment_created',
    //     'callback'    => '\theme_yipl\event\observer::enrolment_created',
    // ],
    // // user enrollment update
    // [
    //     'eventname'   => '\core\event\user_enrolment_updated',
    //     'callback'    => '\theme_yipl\event\observer::enrolment_updated',
    // ],
    // // user enrollment deleted
    // [
    //     'eventname'   => '\core\event\user_enrolment_deleted',
    //     'callback'    => '\theme_yipl\event\observer::enrolment_deleted',
    // ],
    // // course created
    // [
    //     'eventname'   => '\core\event\course_created',
    //     'callback'    => '\theme_yipl\event\observer::course_created',
    // ],
    // // course deleted
    // [
    //     'eventname'   => '\core\event\course_deleted',
    //     'callback'    => '\theme_yipl\event\observer::course_deleted',
    // ],
    // // course updated
    // [
    //     'eventname'   => '\core\event\course_updated',
    //     'callback'    => '\theme_yipl\event\observer::course_updated',
    // ],
    // // role_assigned
    // [
    //     'eventname'   => '\core\event\role_assigned',
    //     'callback'    => '\theme_yipl\event\observer::role_assigned',
    // ],
    // // role_unassigned
    // [
    //     'eventname'   => '\core\event\role_unassigned',
    //     'callback'    => '\theme_yipl\event\observer::role_unassigned',
    // ],
    // // course_module_completion_updated
    // [
    //     'eventname'   => '\core\event\course_module_completion_updated',
    //     'callback'    => '\theme_yipl\event\observer::course_module_completion_updated',
    // ],
    // // course_section_created
    // [
    //     'eventname'   => '\core\event\course_section_created',
    //     'callback'    => '\theme_yipl\event\observer::course_section_created',
    // ],
    // // course_section_deleted
    // [
    //     'eventname'   => '\core\event\course_section_deleted',
    //     'callback'    => '\theme_yipl\event\observer::course_section_deleted',
    // ],
    // // course_category_updated	
    // [
    //     'eventname'   => '\core\event\course_category_updated',
    //     'callback'    => '\theme_yipl\event\observer::course_category_updated',
    // ],
    // // course_category_deleted	
    // [
    //     'eventname'   => '\core\event\course_category_deleted',
    //     'callback'    => '\theme_yipl\event\observer::course_category_deleted',
    // ],

];
