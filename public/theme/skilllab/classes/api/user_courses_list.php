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

namespace theme_skilllab\api;

use core_external\external_description;
use core_external\external_format_value;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use theme_skilllab\util\UtilUser_handler;
use theme_skilllab\util\UtilCourse_handler;

/**
 * User external functions
 *
 * @package    core_user
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class user_courses_list extends \core_external\external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.9
     */
    public static function user_courses_list_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(
                    PARAM_RAW,
                    'Encrypted id of the user'
                )
            )
        );
    }

    /**
     * return list of user enrolled in course as studnet
     * @param int $userid id of course
     * @return array of course participatient as student role result
     */
    public static function user_courses_list($userid) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/theme/skilllab/lib.php');
        $user_courses_list = [];

        $params = self::validate_parameters(
            self::user_courses_list_parameters(),
            array(
                'userid' => $userid
            )
        );

        $userid = (int)decrypt($params['userid']);

        if ($DB->record_exists('user', array('id' => $userid))) {

            // $userid = '2';
            $courses = enrol_get_users_courses($userid, false, '*', 'visible DESC, fullname ASC, sortorder ASC');
            foreach ($courses as $key => $course) {
                $context = \context_course::instance($course->id, IGNORE_MISSING);
                $course_categories = $DB->get_record('course_categories', ['id' => $course->category]);

                $courseinfo = array();
                $courseinfo['id'] = $course->id;
                $courseinfo['categoryid'] = $course->category;
                $courseinfo['shortname'] = \core_external\util::format_string($course->shortname, $context);
                $courseinfo['fullname'] = \core_external\util::format_string($course->fullname, $context);
                $courseinfo['category_name'] = $course_categories->name;
                $courseinfo['summary'] =    UtilCourse_handler::get_course_formatted_summary($course);
                $courseinfo['course_url'] = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
                $courseinfo['course_img_url'] = \core_external\util::format_string(UtilCourse_handler::get_course_image($course), $context);
                $courseinfo['sortorder'] = $course->sortorder;
                $courseinfo['course_visible'] = $course->visible;
                $courseinfo['course_startdate'] = $course->startdate;
                $courseinfo['course_enddate'] = $course->enddate;

                // $date = new \DateTime();
                // $date->setTimestamp(intval($course->startdate));
                // $courseinfo['course_startdate'] = userdate($date->getTimestamp());
                // $date->setTimestamp(intval($course->enddate));
                // $courseinfo['course_enddate'] = userdate($date->getTimestamp());

                $courseinfo['progress_percentage'] = UtilUser_handler::get_user_course_progress($course, $userid);

                // get user enrollment data in the course
                $userenrolments = UtilUser_handler::course_user_enrolments($course->id, $userid);
                foreach ($userenrolments as $key => $value) {
                    $enrollment_status = ($value->status == 0) ? 'Active' : 'Suspended';
                    $enrollment_date = $value->timecreated;

                    // $date = new \DateTime();
                    // $date->setTimestamp(intval($enrollment_date));
                    // $enrollment_date = userdate($date->getTimestamp());
                }
                $courseinfo['enrollment_status'] = $enrollment_status;
                $courseinfo['enrollment_date'] = $enrollment_date;

                // // get enrolled user role in the course
                $get_user_roles = get_user_roles($context, $userid);
                $user_roles = [];
                foreach ($get_user_roles as $key => $role) {
                    $user_roles[] = $role->shortname;
                }
                $courseinfo['user_course_roles'] = json_encode($user_roles);

                // course custom field data
                $handler = \core_course\customfield\course_handler::create();
                if ($customfields = $handler->export_instance_data($course->id)) {
                    // $courseinfo['customfields'] = [];
                    foreach ($customfields as $data) {
                        // $courseinfo['customfields'][] = [
                        //     'type' => $data->get_type(),
                        //     'value' => $data->get_value(),
                        //     'valueraw' => $data->get_data_controller()->get_value(),
                        //     'name' => $data->get_name(),
                        //     'shortname' => $data->get_shortname()
                        // ];

                        if (($data->get_shortname() == 'skill_level')) {
                            $courseinfo['skill_level'] =   $data->get_value();
                        }
                        if ($data->get_shortname() == 'course_duration') {
                            $courseinfo['course_duration'] =  $data->get_value();
                        }
                        if ($data->get_shortname() == 'course_type') {
                            $courseinfo['type'] =  $data->get_value();
                        }
                    }
                }

                // certificate url
                $courseinfo['certificate_url'] = '';
                $courseinfo['certificate_issues'] = false;
                $courseinfo['certificate_issues_date'] = 0;
                $courseinfo['certificate_issues_code'] = '';
                if ($courseinfo['progress_percentage'] == 100) {
                    $course_customcert = UtilCourse_handler::course_mod_customcert($course->id, $userid);
                    $courseinfo['certificate_url'] = $course_customcert['certificate_url'];
                    $courseinfo['certificate_issues'] = $course_customcert['certificate_issues'];
                    $courseinfo['certificate_issues_date'] = $course_customcert['certificate_issues_date'];
                    $courseinfo['certificate_issues_code'] = $course_customcert['certificate_issues_code'];
                }

                $user_courses_list[] = $courseinfo;
            }
        }

        return $user_courses_list;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.9
     */
    public static function user_courses_list_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'course id'),
                    'categoryid' => new external_value(PARAM_INT, 'category id'),
                    'shortname' => new external_value(PARAM_RAW, 'course short name'),
                    'fullname' => new external_value(PARAM_RAW, 'full name'),
                    'category_name' => new external_value(PARAM_RAW, 'course category name'),
                    'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
                    'course_url' => new external_value(PARAM_RAW, 'course url', VALUE_OPTIONAL),
                    'course_img_url' => new external_value(
                        PARAM_RAW,
                        'course course image URL',
                        VALUE_OPTIONAL
                    ),
                    'sortorder' => new external_value(
                        PARAM_INT,
                        'sort order into the category',
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
                    'progress_percentage' => new external_value(PARAM_INT, 'given user progress_percentage'),
                    'enrollment_status' => new external_value(
                        PARAM_RAW,
                        'Enrolled status in the course'
                    ),
                    'enrollment_date' => new external_value(
                        PARAM_INT,
                        'date time when the user is enrolled in the course'
                    ),
                    'user_course_roles' => new external_value(
                        PARAM_RAW,
                        'user course roles in jsonencode string',
                        VALUE_OPTIONAL
                    ),
                    'skill_level' => new external_value(
                        PARAM_RAW,
                        'skill level',
                        VALUE_OPTIONAL
                    ),
                    'course_duration' => new external_value(
                        PARAM_RAW,
                        'course duration',
                        VALUE_OPTIONAL
                    ),
                    'type' => new external_value(
                        PARAM_RAW,
                        'course type',
                        VALUE_OPTIONAL
                    ),
                    'customfields' => new external_multiple_structure(
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
                    'certificate_url' => new external_value(
                        PARAM_RAW,
                        'course certificate url'
                    ),
                    'certificate_issues' => new external_value(
                        PARAM_BOOL,
                        'certificated issues or not'
                    ),
                    'certificate_issues_date' => new external_value(
                        PARAM_INT,
                        'date time when the course certificate generated and issues'
                    ),
                    'certificate_issues_code' => new external_value(
                        PARAM_RAW,
                        'certificate code'
                    ),

                )
            )
        );
    }
}
