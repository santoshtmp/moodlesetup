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
 * yipl
 * @package   theme_yipl
 * @copyright 2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\local;

defined('MOODLE_INTERNAL') || die;

use theme_yipl\util\UtilCustomField_handler;

class custom_fields
{

    public static $course_fields_category = "Other Course Information";

    public static $course_fields = [
        [
            'fieldname' => 'Duration',
            'shortname' => 'yipl_duration',
            'type' => 'text',
        ],
        [
            'fieldname' => 'Skill Level',
            'shortname' => 'yipl_skill_level',
            'type' => 'select',
            'options' => [
                'options' => "Beginner\n Intermediate\n Advanced",
                'defaultvalue' => 'Beginner',
            ],
        ],
        [
            'fieldname' => 'Language',
            'shortname' => 'yipl_language',
            'type' => 'select',
            'options' => [
                'options' => "English\nNepali",
                'defaultvalue' => 'English',
            ],
        ],
        [
            'fieldname' => 'Course Introduction Video Url',
            'shortname' => 'yipl_intro_video',
            'type' => 'text',
        ],
        [
            'fieldname' => 'Target Audiences',
            'shortname' => 'yipl_target_audiences',
            'type' => 'textarea',
        ],
        [
            'fieldname' => 'Learning Objectives',
            'shortname' => 'yipl_learning_objective',
            'type' => 'textarea',
        ],
    ];

    /**
     * 
     */
    public static function add_course_customfields()
    {

        $custom_field_categoryid = UtilCustomField_handler::get_customfield_category_id(self::$course_fields_category, 'course');
        UtilCustomField_handler::create_course_customfields($custom_field_categoryid, self::$course_fields);
    }
}
