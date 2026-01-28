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
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\util;

use Exception;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * UtilCustomField_handler
 */
class UtilCustomField_handler
{

    public static function get_field_shortname($fieldname)
    {
        $replace_for = [' ', '(', ')'];
        $replace_with = ['_', '_', '_'];
        $filteredname = str_replace($replace_for, $replace_with, $fieldname);
        $shortname = "yipl_" . strtolower($filteredname);
        return $shortname;
    }


    /**
     * This function creates custom field category.
     * @param  string $categoryname  name of the category
     * @param  string $area "course" or "user"
     * @return int    Newly created Category id.
     */
    public static function get_customfield_category_id($categoryname, $area = 'course')
    {
        global $DB;
        // Check if the category already exists
        $existingCategory = $DB->get_record('customfield_category', ['name' => $categoryname]);
        if (!$existingCategory) {
            // Create a new category for the custom field
            if ($area == 'course') {
                $handler = \core_customfield\handler::get_handler('core_course', 'course', 0);
                $categoryid = $handler->create_category($categoryname);
            } elseif ($area == 'user') {
                $categoryid = self::get_userinfo_customfield_category($categoryname);
            } else {
                $categoryid = '';
            }
        } else {
            $categoryid = $existingCategory->id;
        }
        return (int)$categoryid;
    }

    /**
     * @param int $customfield_categoryid
     * @param array $customfields
     */
    public static function create_course_customfields($customfield_categoryid, $customfields)
    {
        global $DB;

        foreach ($customfields as $customfield) {
            $fieldname = $customfield['fieldname'];
            // shortname 
            $shortname = isset($customfield['shortname']) ? $customfield['shortname'] : '';
            $shortname = ($shortname) ? $shortname : self::get_field_shortname($customfield['fieldname']);
            // Make sure not to repeat the fields.
            if (!$DB->record_exists('customfield_field', array(
                'shortname' => $shortname,
                'name' => $fieldname,
                'categoryid' => $customfield_categoryid
            ))) {
                self::create_course_custom_field_data($customfield_categoryid, $customfield);
            }
        }
    }

    /**
     * This function creates custom field.
     * @param  int $categoryid  Category Id, in which new field will be created.
     * @param  array $customfield = ['fieldname'=>'','shortname'=>'','type'=>'','options'=>'']
     * @return int    Newly created Category id.
     */
    public static function create_course_custom_field_data($categoryid, $customfield)
    {
        try {
            $field_configdata = self::get_course_customfield_data($categoryid, $customfield);

            $category = \core_customfield\category_controller::create($categoryid);
            $field = \core_customfield\field_controller::create(0, (object)['type' => $customfield['type']], $category);
            $handler = $field->get_handler();

            $fieldid = $handler->save_field_configuration($field, $field_configdata);
            return $fieldid;
        } catch (Exception $e) {
            error_log($e);
        }
    }


    /**
     * This function creates custom field.
     * @param  int $categoryid  Category Id, in which new field will be created.     
     * @param  array $customfield = ['fieldname'=>'','shortname'=>'','type'=>'','options'=>'']
     * @return \stdClass $data of custom field configuration      
     */
    public static function get_course_customfield_data($categoryid, $customfield)
    {
        $fieldname = $customfield['fieldname'];
        $shortname = isset($customfield['shortname']) ? $customfield['shortname'] : '';
        $shortname = ($shortname) ? $shortname : self::get_field_shortname($customfield['fieldname']);
        $fieldtype = $customfield['type'];
        $options = $customfield['options'];

        // 
        $data = new \stdClass;
        $data->name = $fieldname;
        $data->shortname = $shortname;
        $data->mform_isexpanded_id_header_specificsettings = 1;
        $data->mform_isexpanded_id_course_handler_header = 1;
        $data->categoryid = $categoryid;
        $data->type = $fieldtype;
        $data->id = 0; // This is always zero.

        $configdata = [
            "required" => 0,
            "uniquevalues" => 0,
            "locked" => 0,
            "visibility" => 2,
        ];

        switch ($fieldtype) {
            case 'checkbox':
                $configdata["checkbydefault"] = 0;
                break;
            case 'date':
                $configdata["includetime"] = 0;
                $configdata["mindate"] = 1605158580;
                $configdata["maxdate"] = 1605158580;
                break;
            case 'select':
                $configdata["options"] = "menuitem1";
                $configdata["defaultvalue"] = "menuitem1";
                break;
            case 'text':
                $configdata["defaultvalue"] = "";
                $configdata["displaysize"] = 50;
                $configdata["maxlength"] = 1333;
                $configdata["ispassword"] = 0;
                break;
            case 'number':
                $configdata["defaultvalue"] = 0;
                $configdata["minimumvalue"] = 0;
                $configdata["decimalplaces"] = 0;
                $configdata["display"] = "{value}";
                $configdata["displaywhenzero"] = "0";
                break;
            case 'textarea':
                $configdata['defaultvalue_editor'] = array();
                break;
            default:
                throw new Exception("No such type of field");
                break;
        }

        if ($options && (is_array($options) || is_object($options))) {
            foreach ($options as $key => $value) {
                $configdata[$key] = $value;
            }
        }


        $data->configdata = $configdata;
        return (object)$data;
    }


    /**
     * @param string $category_name user field catgory name
     * @return int user field category id;
     */
    public static function get_userinfo_customfield_category($category_name)
    {
        global $DB, $CFG;
        $table = "user_info_category";
        if (!$DB->record_exists($table, ['name' => $category_name])) {
            $data = new stdClass();
            $data->name = $category_name;
            $data->sortorder = 9991;
            $DB->insert_record($table, $data);
        }
        $category = $DB->get_record($table, ['name' => $category_name]);
        return ((int)$category->id) ?: 0;
    }


    /**
     * @param string $user_field_name custom user field name
     */
    public static function create_userinfo_profile_field($user_field_name, $category_id)
    {
        global $DB;
        $status = false;
        $shortname = self::get_field_shortname($user_field_name);
        $table = "user_info_field";
        // Make sure not to repeat the fields.

        $data = new stdClass();
        $data->shortname = $shortname;
        $data->name = trim($user_field_name);
        $data->datatype = "text";
        $data->description = "";
        $data->descriptionformat = 1;
        $data->categoryid = $category_id;
        $data->defaultdata = "";
        $data->visible = 2;
        $data->param1 = 40;
        $data->param2 = 2048;
        $data->param3 = "";
        $data->param4 = "";
        $data->param5 = "";

        $user_field = $DB->get_record($table, ['shortname' => $shortname]);
        if ($user_field) {
            $data->id = $user_field->id;
            $status = $DB->update_record($table, $data);
        } else {
            $status = $DB->insert_record($table, $data);
        }

        return $status;
    }


    // 
}
