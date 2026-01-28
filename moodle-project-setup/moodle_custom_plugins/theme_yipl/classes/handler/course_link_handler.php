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
 *
 * @package    theme_yipl
 * @copyright  2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



namespace theme_yipl\handler;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

use moodle_url;
use stdClass;

/**
 *
 * @package    theme_yipl
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_link_handler {
    // table name
    protected static $course_list_table = 'yipl_course_link';

    /**
     * Save Data
     * @param object $data
     */
    public static function save_data($mform_data) {
        try {
            global $DB, $COURSE, $CFG;
            // Form was submitted and validated, process the data
            $apply_course_links = isset($mform_data->config_apply_course_links) ? (int)$mform_data->config_apply_course_links : 0;
            if ($apply_course_links) {
                $other_courses = (string)$mform_data->config_courselist_order;
                // 
                $data = new stdClass();
                $data->course_id = $COURSE->id;
                $data->other_courses = $other_courses;
                $data->timemodified = time();
                if ($data->course_id) {
                    $data_exists = $DB->get_record(self::$course_list_table, ['course_id' =>  $data->course_id]);
                    if ($data_exists) {
                        $data->id =  $data_exists->id;
                        $DB->update_record(self::$course_list_table, $data);
                    } else {
                        $data->timecreated = time();
                        $DB->insert_record(self::$course_list_table, $data);
                    }
                    self::set_meta_link_enrollment($other_courses);
                    self::unset_meta_link_enrollment($COURSE->id, $other_courses);
                }
            } else {
                self::delete_data($COURSE->id);
                self::unset_meta_link_enrollment($COURSE->id);
            }
        } catch (\Throwable $th) {
            // $message = "Error on submit :: ";
            // $message .= "\n" . $th->getMessage();
        }
    }

    /**
     * Delete Data
     * @param int $id
     */
    public static function delete_data($id) {
        try {
            global $DB;
            $data = $DB->get_record(self::$course_list_table, ['course_id' => $id]);
            if ($data) {
                $DB->delete_records(self::$course_list_table, ['id' => $data->id]);
            }
        } catch (\Throwable $th) {
        }
    }

    /**
     * 
     */
    public static function get_other_courses($course_id) {
        global $DB;
        return $DB->get_record(self::$course_list_table, ['course_id' =>  $course_id]);
    }


    /**
     * 
     */
    public static function get_course_link_navigation() {
        global $DB, $USER, $COURSE;

        if ($COURSE->format != "singleactivity") {
            return;
        }
        // if (!$cm_id) {
        //     // $cm = get_coursemodule_from_id('', $PAGE->context->instanceid);
        //     return;
        // }
        $output = [];
        $sql_params = [
            'frontpagecourse_id' => 1,
            'visible_val' => 1
        ];
        $sql_query = 'SELECT course_link.course_id AS course_id ,
                            course_link.other_courses AS other_courses_ids,
                            course.fullname AS course_fullname,
                            course.format AS format,
                            course.category AS category_id,
                            course_categories.name AS category_name
                    FROM {wtpoker_course_link} as course_link
                    JOIN {course} AS course ON course.id = course_link.course_id
                    JOIN {course_categories} AS course_categories ON course_categories.id = course.category
                    WHERE course.id <> :frontpagecourse_id AND course.visible = :visible_val
                    ORDER BY course.sortorder ASC 
                ';
        $records = $DB->get_records_sql($sql_query, $sql_params);
        $current_course_in_nav_tree = false;

        if ($records) {
            $output_data = [];
            $next_previous = [];
            foreach ($records as $key => $record) {
                if ($record->other_courses_ids && $record->format == 'topics') {
                    $course_ids = explode(',', $record->other_courses_ids);
                    list($sql_in, $params) = $DB->get_in_or_equal($course_ids, SQL_PARAMS_QM);
                    $sql_courses_link = "SELECT c.id AS course_id, 
                            c.fullname AS course_fullname, 
                            c.format AS format,
                            c.category AS category_id, 
                            cc.name AS category_name
                        FROM {course} c
                        JOIN {course_categories} cc ON c.category = cc.id
                        WHERE c.id $sql_in
                        ORDER BY FIELD(c.id, " . implode(',', array_map('intval', $course_ids)) . ")
                    ";
                    $records_other_courses = $DB->get_records_sql($sql_courses_link, $params);
                    if ($records_other_courses) {
                        $other_courses_link = [];
                        $active_current_course = false;
                        foreach ($records_other_courses as $key => $course) {
                            if ($course->format != 'singleactivity') {
                                continue;
                            }
                            if ($COURSE->id == $course->course_id) {
                                $next_previous = self::get_adjacent_array_point($records_other_courses, $COURSE->id, 'key');
                                if ($next_previous) {
                                }
                            }
                            $course->course_url = (new \moodle_url('/course/view.php', array('id' => $course->course_id)))->out();
                            $course->current_course = ($COURSE->id == $course->course_id) ? true : false;
                            $active_current_course = ($course->current_course) ? true : $active_current_course;
                            $course->is_enrolled = is_enrolled(
                                \context_course::instance($course->course_id),
                                $USER->id,
                                // 'moodle/course:isincompletionreports'
                            );
                            $other_courses_link[] = $course;
                        }
                        $current_course_in_nav_tree = ($active_current_course) ? true : $current_course_in_nav_tree;
                        $record->current_course = $active_current_course;
                        $record->course_url = (new \moodle_url('/course/view.php', array('id' => $record->course_id)))->out();
                        $record->child_courses = (count($other_courses_link) > 0) ? true : false;
                        $record->is_enrolled = is_enrolled(
                            \context_course::instance($record->course_id),
                            $USER->id,
                            // 'moodle/course:isincompletionreports'
                        );
                        $record->courses = $other_courses_link;
                    }
                    $output_data[] = $record;
                }
            }
            if ($next_previous) {
                if ($next_previous['prev']) {
                    $prev_course = $DB->get_record('course', ['id' => $next_previous['prev']]);
                    $next_previous['prev_name'] = $prev_course->fullname;
                    $next_previous['prev_link'] = (new \moodle_url('/course/view.php', array('id' => $prev_course->id)))->out();
                }
                if ($next_previous['next']) {
                    $next_course = $DB->get_record('course', ['id' => $next_previous['next']]);
                    $next_previous['next_name'] = $next_course->fullname;
                    $next_previous['next_link'] = (new \moodle_url('/course/view.php', array('id' => $next_course->id)))->out();
                }
            }
            $output = [
                'courses' => $output_data,
                'next_previous' => $next_previous
            ];
        }
        return ($current_course_in_nav_tree) ? $output : [];
    }

    /**
     * @param array $array
     * @param int|string $current_point
     * @param string $action "value" or "key" 
     */
    public static function get_adjacent_array_point($array, $current_point, $action = 'value') {
        if ($action == 'key') {
            $values = array_keys($array); // Get all keys
        } else {
            $values = array_values($array); // Ensure it's indexed
        }

        $index = array_search($current_point, $values);

        if ($index === false) {
            return ['prev' => null, 'next' => null]; // Value not found
        }

        $prev = $index > 0 ? $values[$index - 1] : null;
        $next = $index < count($values) - 1 ? $values[$index + 1] : null;

        return ['prev' => $prev, 'next' => $next];
    }


    /**
     * 
     */
    public static function set_meta_link_enrollment($other_courses) {
        global $CFG, $DB, $COURSE;

        require_once($CFG->dirroot . '/enrol/meta/lib.php');
        require_once($CFG->dirroot . '/enrol/meta/locallib.php');
        $other_courses_list = ($other_courses) ? explode(',', $other_courses) : [];

        $enrol_meta_plugin = new \enrol_meta_plugin();
        foreach ($other_courses_list as $key => $course_id) {
            $fields = [
                'customint1' => $COURSE->id,
                'customint2' => $course_id
            ];
            $course = $DB->get_record('course', ['id' => $course_id]);
            $existing = $DB->get_record('enrol', [
                'courseid' => $course_id,
                'enrol'    => 'meta',
                'customint1' => $COURSE->id
            ]);
            if (!$existing) {
                $enrol_meta_plugin->add_instance($course, $fields);
            }
        }
    }

    /**
     * 
     */
    public static function unset_meta_link_enrollment($course_id, $other_courses = '') {
        global $CFG, $DB, $COURSE;
        require_once($CFG->libdir . '/enrollib.php');

        // Find the meta enrollment method
        $enrol_instances = $DB->get_records('enrol', [
            'enrol'    => 'meta',
            'customint1' => $course_id
        ]);
        // 
        $other_courses_list = ($other_courses) ? explode(',', $other_courses) : [];

        // 
        if ($enrol_instances) {
            $enrol_plugin = enrol_get_plugin('meta');
            if ($enrol_plugin) {
                foreach ($enrol_instances as $key => $enrol_instance) {
                    if (in_array($enrol_instance->courseid, $other_courses_list)) {
                        continue;
                    }
                    $enrol_plugin->delete_instance($enrol_instance);
                }
            }
        }
    }



    // ----- END -----
}
