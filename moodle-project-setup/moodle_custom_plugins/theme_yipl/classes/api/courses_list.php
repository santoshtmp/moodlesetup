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
 * courses_list API endpoints
 * 
 * @package   theme_yipl   
 * @copyright 2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * Example GET Parameters
 * per_page:3
 * page:2
 * courses[id][0]:2
 * 
 */

namespace theme_yipl\api;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use theme_yipl\util\UtilCourse_handler;

class courses_list extends external_api
{

    /**
     * Returns description of method parameters
     *
     */
    public static function courses_list_parameters()
    {
        return new external_function_parameters(
            array(
                'courses' => new external_single_structure(
                    array(
                        'id' => new external_multiple_structure(
                            new external_value(
                                PARAM_INT,
                                'Course id'
                            ),
                            'List of course id. If empty return all courses except front page course.',
                            VALUE_OPTIONAL
                        )
                    ),
                    'Courses ids',
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
     * Get courses
     *
     * @param array $courses It contains an array (list of ids)
     * @return array
     * @since Moodle 2.2
     */
    public static function courses_list($courses = [], $page = 1, $per_page = 15)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        $returnCourseDatas = [];
        $status = false;

        //validate parameter
        $params = self::validate_parameters(
            self::courses_list_parameters(),
            array(
                'courses' => $courses,
                'page' => $page,
                'per_page' => $per_page
            )
        );

        //retrieve courses
        if (!array_key_exists('id', $params['courses']) or empty($params['courses']['id'])) {
            // $courses = $DB->get_records('course');
            $query = 'SELECT * from {course} course WHERE course.id <> :frontpagecourse_id ';
            $total_count_sql = 'SELECT COUNT(course.id) AS total_count from {course} course WHERE course.id <> :frontpagecourse_id ';
            $sql_params = [
                'frontpagecourse_id' => 1
            ];
            $page_number = $params['page'];
            $per_page = $params['per_page'];

            $limitfrom = 0;
            $limitnum = $per_page;
            if ($page_number > 1) {
                $limitfrom = $limitnum * ($page_number - 1);
                $limitnum = $limitnum;
            }
            $courses = $DB->get_records_sql($query, $sql_params, $limitfrom, $limitnum);
            $total_courses = $DB->get_record_sql($total_count_sql, $sql_params);

            $metaInfo = [
                'total_page' => ceil(($total_courses->total_count) / $per_page),
                'current_page' => $page_number,
                'per_page' => $per_page
            ];
        } else {
            $courses = $DB->get_records_list('course', 'id', $params['courses']['id']);
        }

        //create return value
        $coursesinfo = array();
        $page_data_count = 0;
        foreach ($courses as $course) {

            // now security checks
            $context = \context_course::instance($course->id, IGNORE_MISSING);
            if ($course->id != SITEID) {
                require_capability('moodle/course:view', $context);
            }
            $courseinfo = UtilCourse_handler::get_course_info($course->id);
            $coursesinfo[] = $courseinfo;
            $page_data_count++;
            $status = true;
        }
        $metaInfo['page_data_count'] = $page_data_count;
        $returnCourseDatas['data'] = $coursesinfo;
        $returnCourseDatas['meta'] = $metaInfo;
        $returnCourseDatas['status'] = $status;

        return  $returnCourseDatas;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 2.2
     */
    public static function courses_list_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_BOOL, 'status'),
                'data' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(
                                PARAM_INT,
                                'course id'
                            ),
                            'categoryid' => new external_value(
                                PARAM_INT,
                                'category id'
                            ),
                            'shortname' => new external_value(
                                PARAM_RAW,
                                'course short name'
                            ),
                            'fullname' => new external_value(
                                PARAM_RAW,
                                'course full name'
                            ),
                            'course_link' => new external_value(
                                PARAM_URL,
                                'course url',
                                VALUE_OPTIONAL
                            ),
                            'enrollment_link' => new external_value(
                                PARAM_URL,
                                'course enrollment link',
                                VALUE_OPTIONAL
                            ),
                            'participant_link' => new external_value(
                                PARAM_URL,
                                'course participant_link',
                                VALUE_OPTIONAL
                            ),
                            'thumbnail_image_link' => new external_value(
                                PARAM_RAW,
                                'course course image URL'
                            ),
                            'summary' => new external_value(
                                PARAM_RAW,
                                'summary',
                                VALUE_OPTIONAL
                            ),
                            'course_sortorder' => new external_value(
                                PARAM_INT,
                                'course sort order into the category',
                                VALUE_OPTIONAL
                            ),
                            'course_total_sections' => new external_value(
                                PARAM_INT,
                                'Course sections topics chapters',
                                VALUE_OPTIONAL
                            ),
                            'course_visible' => new external_value(
                                PARAM_INT,
                                '1: available to student, 0:not available',
                                VALUE_OPTIONAL
                            ),
                            'course_startdate' => new external_value(
                                PARAM_INT,
                                'date time when the course start'
                            ),
                            'course_enddate' => new external_value(
                                PARAM_INT,
                                'date time when the course end'
                            ),
                            'course_timecreated' => new external_value(
                                PARAM_INT,
                                'date time when the course have been created',
                                VALUE_OPTIONAL
                            ),
                            'course_timemodified' => new external_value(
                                PARAM_INT,
                                'date time when the course have been modified',
                                VALUE_OPTIONAL
                            ),
                            'enrollment_methods' => new external_multiple_structure(
                                new external_value(PARAM_TEXT, 'Array items', VALUE_OPTIONAL)
                            ),
                            'enroll_total_student' => new external_value(
                                PARAM_INT,
                                'Total number of students',
                                VALUE_OPTIONAL
                            ),
                            'course_customfields' => new external_multiple_structure(
                                new external_single_structure(
                                    [
                                        'name' => new external_value(PARAM_RAW, 'The name of the custom field'),
                                        'shortname' => new external_value(PARAM_ALPHANUMEXT, 'The shortname of the custom field'),
                                        'type'  => new external_value(
                                            PARAM_COMPONENT,
                                            'The type of the custom field - text, checkbox...'
                                        ),
                                        'valueraw' => new external_value(PARAM_RAW, 'The raw value of the custom field'),
                                        'value' => new external_value(PARAM_RAW, 'The value of the custom field')
                                    ]
                                ),
                                'Custom fields and associated values',
                                VALUE_OPTIONAL
                            ),
                        )
                    ),
                    'course data',
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
                    'course list meta information ',
                    VALUE_OPTIONAL
                )
            ]
        );
    }
}
