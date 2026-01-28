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
 * get_roles API endpoints
 *
 * @package    theme_skilllab
 * @copyright  2022 Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\api;

use core_course\external\course_summary_exporter;
use core_external\external_api;
use core_external\external_files;
use core_external\external_format_value;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use core_external\util;
use Exception;
use moodle_exception;

use stdClass;


class get_roles extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_roles_parameters()
    {
        return new external_function_parameters(
            [
                'shortname' => new external_value(PARAM_RAW, 'role shortname', VALUE_OPTIONAL)
            ]
        );
    }

    /**
     * Get roles
     *
     */
    public static function get_roles($shortname = '')
    {
        global $CFG, $DB;


        //validate parameter
        $params = self::validate_parameters(
            self::get_roles_parameters(),
            array(
                'shortname' => $shortname,
            )
        );

        $page_number = $params['shortname'];


        //retrieve courses
        // if (!array_key_exists('ids', $params['options']) or empty($params['options']['ids'])) {
        //     // $courses = $DB->get_records('course');
        //     $query = 'SELECT * from {course} course WHERE course.id <> :frontpagecourse_id ';
        //     $total_count_sql = 'SELECT COUNT(course.id) AS total_count from {course} course WHERE course.id <> :frontpagecourse_id ';
        //     $sql_params = [
        //         'frontpagecourse_id' => 1
        //     ];
        //     $page_number = $params['page'];

        //     $limitfrom = 0;
        //     $limitnum = $per_page;
        //     if ($page_number > 1) {
        //         $limitfrom = $limitnum * ($page_number - 1);
        //         $limitnum = $limitnum;
        //     }
        //     $courses = $DB->get_records_sql($query, $sql_params, $limitfrom, $limitnum);
        //     $total_count = $DB->get_record_sql($total_count_sql, $sql_params);

        //     $metaInfo = [
        //         'total_page' => ceil(($total_count->total_count) / $per_page),
        //         'current_page' => $page_number,
        //         'per_page' => $per_page
        //     ];
        // } else {
        //     $courses = $DB->get_records_list('course', 'id', $params['options']['ids']);
        // }

        // //create return value
        // $coursesinfo = array();
        // $page_data_count = 0;
        // foreach ($courses as $course) {

        //     // now security checks
        //     $context = \context_course::instance($course->id, IGNORE_MISSING);
        //     $courseformatoptions = course_get_format($course)->get_format_options();
        //     try {
        //         self::validate_context($context);
        //     } catch (Exception $e) {
        //         $exceptionparam = new stdClass();
        //         $exceptionparam->message = $e->getMessage();
        //         $exceptionparam->courseid = $course->id;
        //         throw new moodle_exception('errorcoursecontextnotvalid', 'webservice', '', $exceptionparam);
        //     }
        //     if ($course->id != SITEID) {
        //         require_capability('moodle/course:view', $context);
        //     }
        //     // $course_categories = $DB->get_record('course_categories', ['id' => $course->category]);

        //     // // arrange data to return
        //     // $courseinfo = array();
        //     // $courseinfo['id'] = $course->id;
        //     // $courseinfo['categoryid'] = $course->category;
        //     // $courseinfo['shortname'] = \core_external\util::format_string($course->shortname, $context);
        //     // $courseinfo['fullname'] = \core_external\util::format_string($course->fullname, $context);
        //     // $courseinfo['displayname'] = \core_external\util::format_string(get_course_display_name_for_list($course), $context);
        //     // $courseinfo['category_name'] = $course_categories->name;
        //     // $courseinfo['sortorder'] = $course->sortorder;
        //     // $courseinfo['course_url'] = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
        //     // $courseinfo['summary'] =    get_course_formatted_summary($course);
        //     // $courseinfo['course_img_url'] = \core_external\util::format_string(get_course_image($course), $context);
        //     // $courseinfo['count_enrolled_users'] = count_enrolled_users($context);
        //     // if (array_key_exists('numsections', $courseformatoptions)) {
        //     //     // For backward-compartibility
        //     //     $courseinfo['numsections'] = $courseformatoptions['numsections'];
        //     // }
        //     // // Return numsections for backward-compatibility with clients who expect it.
        //     // $courseinfo['numsections'] = course_get_format($course)->get_last_section_number();
        //     // $courseinfo['course_visible'] = $course->visible;
        //     // $courseinfo['chapter_topics'] =  $courseinfo['numsections'] + 1;

        //     // // $date = new \DateTime();
        //     // // $date->setTimestamp(intval($course->startdate));
        //     // // userdate($date->getTimestamp())
        //     // $courseinfo['course_startdate'] = $course->startdate;
        //     // // $date->setTimestamp(intval($course->enddate));
        //     // $courseinfo['course_enddate'] = $course->enddate;
        //     // // $date->setTimestamp(intval($course->timecreated));
        //     // $courseinfo['course_timecreated'] = $course->timecreated;
        //     // // $date->setTimestamp(intval($course->timemodified));
        //     // $courseinfo['course_timemodified'] = $course->timemodified;

        //     // // course custom field data
        //     // $handler = \core_course\customfield\course_handler::create();
        //     // if ($customfields = $handler->export_instance_data($course->id)) {
        //     //     // $courseinfo['customfields'] = [];
        //     //     foreach ($customfields as $data) {
        //     //         // $courseinfo['customfields'][] = [
        //     //         //     'type' => $data->get_type(),
        //     //         //     'value' => $data->get_value(),
        //     //         //     'valueraw' => $data->get_data_controller()->get_value(),
        //     //         //     'name' => $data->get_name(),
        //     //         //     'shortname' => $data->get_shortname()
        //     //         // ];
        //     //         if (($data->get_shortname() == 'skill_level')) {
        //     //             $courseinfo['skill_level'] =   $data->get_value();
        //     //         }
        //     //         if ($data->get_shortname() == 'course_duration') {
        //     //             $courseinfo['course_duration'] =  $data->get_value();
        //     //         }
        //     //     }
        //     // }

        //     $courseinfo = get_course_info($course->id);
        //     $coursesinfo[] = $courseinfo;
        //     $page_data_count++;
        // }
        // $metaInfo['page_data_count'] = $page_data_count;
        // $returnCourseDatas['data'] = $coursesinfo;
        // $returnCourseDatas['meta'] = $metaInfo;
        $output = [
            'roles' => [
                [
                    'id' => 1,
                    'shortname' => 'shortname',
                    'name' => 'shortname',
                    'description' => 'description',
                    'archetype' => 'archetype',
                    'sortorder' => 4
                ]
            ]
        ];
        return  $output = [];
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 2.2
     */
    public static function get_roles_returns()
    {
        return new external_single_structure(
            [
                'roles' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'role id'),
                            'shortname' => new external_value(PARAM_RAW, 'role short name'),
                            'name' => new external_value(PARAM_RAW, 'role  name'),
                            'description' => new external_value(PARAM_RAW, 'role description'),
                            'archetype' => new external_value(PARAM_RAW, 'role description'),
                            'sortorder' => new external_value(PARAM_INT, 'role sortorder')
                        ]
                    ),
                    'roles array data',
                    VALUE_OPTIONAL
                ),

            ]
        );
    }
}
