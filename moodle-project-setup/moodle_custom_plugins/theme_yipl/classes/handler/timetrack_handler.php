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
 * @author     santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\handler;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use stdClass;

class timetrack_handler
{

    /**
     * @param string $timetrack_table time track table name
     */
    protected static $timetrack_table = 'yipl_timetrack';

    /**
     * Insert or update yipl_timetrack
     */
    public static function set_time_track()
    {
        global $DB, $SESSION;
        $status = false;
        // check if yipl_timetrack session is set or not
        if (!isset($SESSION->yipl_timetrack)) {
            return $status;
        }

        // check if yipl_timetrack setting is enable or disable
        $yipl_timetrack = get_config('theme_yipl', 'yipl_timetrack');
        if (!$yipl_timetrack) {
            return $status;
        }

        // get session time track data and match with the provided data.
        $session_time_track = $SESSION->yipl_timetrack;
        $user_id = $session_time_track['user_id'];
        $course_id = $session_time_track['course_id'];
        $cmod_id = $session_time_track['cmod_id'];
        $start_time = $session_time_track['start_time'];
        if (
            !$user_id ||
            !$course_id  ||
            !$cmod_id ||
            !$start_time
        ) {
            return $status;
        }

        // calculate the duration time.
        $end_time = time();
        $duration = $end_time - $start_time;
        $duration = ($duration > 0) ? $duration : 1;
        // manage data to store.
        $data = new stdClass();
        $data->user_id = $user_id;
        $data->course_id = $course_id;
        $data->cmod_id = $cmod_id;
        $data->duration = $duration;
        $data->timemodified = time();
        $conditions =  [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'cmod_id' => $cmod_id
        ];
        $table_data =  $DB->get_record(self::$timetrack_table, $conditions, $fields = '*', IGNORE_MISSING);
        if ($table_data) {
            $old_duration = (int)$table_data->duration;
            $old_duration = ($old_duration >= 0) ? $old_duration : 0;
            $data->id = $table_data->id;
            $data->duration = $data->duration + $old_duration;
            $status = $DB->update_record(self::$timetrack_table, $data);
        } else {
            $data->timecreated = time();
            $status = $DB->insert_record(self::$timetrack_table, $data);
        }
        return ($status) ? $duration : false;
    }

    /**
     * delete time_track data if cmod is deleted
     */
    public static function delete_time_track_cmod_id($cmod_id)
    {
        global $DB;
        $DB->delete_records(self::$timetrack_table, ['cmod_id' => $cmod_id]);
    }


    /**
     * get particular user and course total time_duration
     */
    public static function get_user_course_time_duration($course_id, $user_id)
    {
        global $DB;
        // set data in csc 
        $query = 'SELECT SUM(yipl_timetrack.duration) AS total_duration 
         FROM {yipl_timetrack} yipl_timetrack 
         WHERE yipl_timetrack.course_id = :course_id 
             AND yipl_timetrack.user_id = :user_id';
        $sql_params = [
            'course_id' => $course_id,
            'user_id' => $user_id
        ];
        $time_records = $DB->get_record_sql($query, $sql_params);
        return isset($time_records->total_duration) ? $time_records->total_duration : 0;
    }

    /**
     * start_time_track_session_process
     * @param int $course_id
     * @param int $cmod_id
     * Call the `init` function on `theme_yipl/time_track/time_track`
     *
     */
    public static function start_time_track_session_process($course_id, $cmod_id, $user_id = '')
    {
        global  $USER, $PAGE, $SESSION;
        $yipl_timetrack = get_config('theme_yipl', 'yipl_timetrack');
        if ($yipl_timetrack) {
            $page_data = [
                'start_time' => time(),
                'user_id' => ($user_id) ? $user_id : $USER->id,
                'course_id' => $course_id,
                'cmod_id' => $cmod_id,
            ];
            $SESSION->yipl_timetrack = $page_data;
            $PAGE->requires->js_call_amd('theme_yipl/timetrack/timetrack', 'init', [$page_data]);
            return true;
        }
        return false;
    }

    /**
     * call this in course activity pages i.e incourse layout
     */
    public static function init_start_timetrack_course_activity($cm_id, $course_id, $user_id)
    {
        if ($cm_id) {
            $context_course = \context_course::instance($course_id);
            $enrolled_student = is_enrolled($context_course,  $user_id,  'moodle/course:isincompletionreports');
            if ($enrolled_student) {
                $get_user_roles = get_user_roles($context_course, $user_id);
                foreach ($get_user_roles as $key => $role) {
                    if ($role->roleid == '5') {
                        timetrack_handler::start_time_track_session_process($course_id, $cm_id, $user_id);
                    }
                }
            }
        }
    }

    /**
     * end_time_track_session_process
     */
    public static function end_time_track_session_process()
    {
        global $SESSION;
        $SESSION->yipl_timetrack = '';
        unset($SESSION->yipl_timetrack);
    }

    /**
     * 
     */
    public static function get_all_timetrack_data($course_id = '', $user_id = '')
    {
        global $DB;
        $params = [];
        $limitfrom = 0;
        $limitnum = 0;
        $url = $_SERVER['REQUEST_URI'];
        $url_path = parse_url($url, PHP_URL_PATH);
        // 
        $template_content = [];
        $template_content['path'] = $url_path;
        $template_content['timetrack_course'] = false;
        $template_content['particular_course'] = false;
        $template_content['course_user_activity'] = false;
        //
        $sql_query = 'SELECT 
                course.id AS id ,
                course.fullname AS course_name
            FROM {' . self::$timetrack_table . '} timetrack 
            JOIN {course} AS course ON course.id = timetrack.course_id
            Group By course.id ';
        $records = $DB->get_records_sql($sql_query, $params, $limitfrom, $limitnum);
        if ($records) {
            $template_content['timetrack_course'] = true;
            $i = 0;
            $records_data = [];
            foreach ($records as $key => $value) {
                $records_data[$i]['course_id'] = $value->id;
                $records_data[$i]['course_name'] = $value->course_name;
                $i++;
            }
            $template_content['course_list'] = (array)$records_data;
        }
        // 
        if ($course_id && $user_id) {
            $query = 'SELECT 
                    timetrack.cmod_id AS cmod_id,
                    user.id As user_id ,
                    course.id As course_id ,
                    course.fullname AS course_name ,
                    user.firstname AS firstname ,
                    user.lastname AS lastname ,
                    timetrack.duration As duration
                FROM {' . self::$timetrack_table . '} timetrack 
                JOIN {course} AS course ON course.id = timetrack.course_id 
                JOIN {user} AS user ON user.id = timetrack.user_id 
                WHERE course.id = :current_course_id 
                    AND user.id = :user_id';
            $params = [
                'current_course_id' => $course_id,
                'user_id' => $user_id
            ];
            $records = $DB->get_records_sql($query, $params, $limitfrom, $limitnum);
            if ($records) {
                $template_content['course_user_activity'] = true;
                $records_data = [];
                $i = 0;
                foreach ($records as $key => $value) {
                    $duration = (int)$value->duration; //((int)$value->duration >= 0) ? $value->duration : 0;
                    $course_module_name = self::get_course_module_name($value->cmod_id);
                    if ($course_module_name) {
                        $records_data[$i]['sn'] = $i + 1;
                        $records_data[$i]['course_module_id'] = $value->cmod_id;
                        $records_data[$i]['course_module_name'] = $course_module_name;
                        $records_data[$i]['duration'] = self::get_duration_time($duration);
                        $i++;
                        $template_content['course_id'] = $value->course_id;
                        $template_content['course_name'] = $value->course_name;
                        $template_content['user_id'] = $value->user_id;
                        $template_content['user_name'] = $value->firstname . ' ' . $value->lastname;
                    }
                }
                $template_content['records'] = (array)$records_data;
            }
        } else if ($course_id && $DB->record_exists('course', array('id' => $course_id))) {
            $query = 'SELECT 
                    user.id As user_id ,
                    course.fullname AS course_name ,
                    user.firstname AS firstname ,
                    SUM(timetrack.duration) As duration
                FROM {' . self::$timetrack_table . '} timetrack 
                JOIN {course} AS course ON course.id = timetrack.course_id 
                JOIN {user} AS user ON user.id = timetrack.user_id 
                WHERE course.id = :current_course_id 
                Group By user.id';
            $params = [
                'current_course_id' => $course_id
            ];
            $records = $DB->get_records_sql($query, $params, $limitfrom, $limitnum);
            if ($records) {
                $template_content['particular_course'] = true;
                $records_data = [];
                $i = 0;
                foreach ($records as $key => $value) {
                    $duration = (int)$value->duration; //((int)$value->duration >= 0) ? $value->duration : 0;
                    $records_data[$i]['sn'] = $i + 1;
                    $records_data[$i]['course_id'] = $course_id;
                    $records_data[$i]['user_id'] = $value->user_id;
                    $records_data[$i]['user_name'] = $value->firstname;
                    // $records_data[$i]['course_module_id'] = $value->cmod_id;
                    // $records_data[$i]['course_module_name'] = $this->get_course_module_name($value->cmod_id);
                    $records_data[$i]['duration'] = self::get_duration_time($duration);
                    $i++;
                    $template_content['particular_course_name'] = $value->course_name;
                }
                $template_content['particular_course_users'] = (array)$records_data;
            }
        }
        return $template_content;
    }


    /**
     * 
     */
    public static function get_duration_time($duration, $format_redable = true)
    {
        $time_duration = [];
        // 
        $days = floor($duration / (24 * 3600));             // Days.
        $hours = floor(($duration % (24 * 3600)) / 3600);   // Remaining hours.
        $minutes = floor(($duration % 3600) / 60);          // Remaining minutes.
        $seconds = $duration % 60;                          // Remaining seconds.
        if (!$format_redable) {
            $days = sprintf("%02d", $days);
            $hours = sprintf("%02d", $hours);
            $minutes = sprintf("%02d", $minutes);
            $seconds = sprintf("%02d", $seconds);
            // return "{$days}:{$hours}:{$minutes}:{$seconds}";
            return "{$hours}:{$minutes}:{$seconds}";
        }
        //
        if ($days) {
            // $time_duration[] = sprintf("%02d", $days) . ' days';
            $time_duration[] = $days . ' days';
        }
        if ($hours) {
            $time_duration[] =  $hours . ' hours';
        }
        if ($minutes) {
            $time_duration[] = $minutes . ' minutes';
        }
        if ($seconds) {
            $time_duration[] = $seconds . ' seconds';
        }

        return implode(" ", $time_duration);
    }


    /**
     * 
     */
    protected static function get_course_module_name($cm_id)
    {
        if (empty($cm_id)) {
            return "course main page";
        }
        global $DB;
        $query = 'SELECT 
                modules.name As mod_name,
                course_modules.instance As instance
            FROM {course_modules} course_modules 
            JOIN {modules} AS modules ON modules.id = course_modules.module 
            WHERE course_modules.id = :course_modules_id';
        $params = [
            'course_modules_id' => $cm_id
        ];
        $module_info = $DB->get_record_sql($query, $params);
        if (!$module_info) {
            return '';
        }
        $records = $DB->get_record($module_info->mod_name, ['id' => $module_info->instance], 'name');
        return isset($records->name) ? $records->name : '';
    }

    /**
     * ===== ED ====
     */
}
