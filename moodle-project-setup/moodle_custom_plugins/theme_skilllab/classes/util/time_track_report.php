<?php

/**
 * @package   theme_skilllab   
 * @copyright 2023 yipl skill lab csc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\util;

use core_date;

defined('MOODLE_INTERNAL') || die;

class time_track_report
{

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

    protected function get_course_module_name($cm_id)
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


    protected function export_for_template_course($course_id = '')
    {
        global $DB;
        $template_content = [];
        $params = [];
        $limitfrom = 0;
        $limitnum = 0;


        if ($course_id and $DB->record_exists('course', array('id' => $course_id))) {
            $query = 'SELECT 
                user.id As user_id ,
                course.fullname AS course_name ,
                user.firstname AS firstname ,
                SUM(skl_time_track.duration) As duration
            FROM {skl_time_track} skl_time_track 
            JOIN {course} AS course ON course.id = skl_time_track.course_id 
            JOIN {user} AS user ON user.id = skl_time_track.user_id 
            WHERE course.id = :current_course_id 
            Group By user.id';
            $params = [
                'current_course_id' => $course_id
            ];
            $records = $DB->get_records_sql($query, $params, $limitfrom, $limitnum);
            $template_content['particular_course'] = true;
            $records_data = [];
            $i = 0;
            foreach ($records as $key => $value) {
                $duration = (int)$value->duration; //((int)$value->duration >= 0) ? $value->duration : 0;

                $records_data[$i]['sno'] = $i + 1;
                $records_data[$i]['course_id'] = $course_id;
                $records_data[$i]['user_id'] = $value->user_id;
                $records_data[$i]['user_name'] = $value->firstname;
                // $records_data[$i]['course_module_id'] = $value->cmod_id;
                // $records_data[$i]['course_module_name'] = $this->get_course_module_name($value->cmod_id);
                $records_data[$i]['duration'] = $this->get_duration_time($duration);
                $i++;
                $template_content['course_name'] = $value->course_name;
            }
            $template_content['records'] = (array)$records_data;

            $url = $_SERVER['REQUEST_URI'];
            $url_path = parse_url($url, PHP_URL_PATH);
            $template_content['path'] = $url_path;
        } else {

            $query = 'SELECT 
                course.id AS id ,
                course.fullname AS course_name
            FROM {skl_time_track} skl_time_track 
            JOIN {course} AS course ON course.id = skl_time_track.course_id
            Group By course.fullname ';
            $records = $DB->get_records_sql($query, $params, $limitfrom, $limitnum);
            $template_content['course_list'] = true;
            $records_data = [];
            $i = 0;
            foreach ($records as $key => $value) {
                $records_data[$i]['course_id'] = $value->id;
                $records_data[$i]['course_name'] = $value->course_name;
                $i++;
            }
            $template_content['records'] = (array)$records_data;
        }
        return $template_content;
    }

    protected function export_for_template_course_user($course_id, $user_id)
    {
        global $DB;
        $template_content = [];
        $params = [];
        $limitfrom = 0;
        $limitnum = 0;

        if ($course_id and $DB->record_exists('course', array('id' => $course_id))) {
            $query = 'SELECT 
                skl_time_track.cmod_id AS cmod_id,
                user.id As user_id ,
                course.id As course_id ,
                course.fullname AS course_name ,
                user.firstname AS firstname ,
                user.lastname AS lastname ,
                skl_time_track.duration As duration
            FROM {skl_time_track} skl_time_track 
            JOIN {course} AS course ON course.id = skl_time_track.course_id 
            JOIN {user} AS user ON user.id = skl_time_track.user_id 
            WHERE course.id = :current_course_id 
                AND user.id = :user_id';
            $params = [
                'current_course_id' => $course_id,
                'user_id' => $user_id
            ];
            $records = $DB->get_records_sql($query, $params, $limitfrom, $limitnum);
            $template_content['course_user_activity'] = true;
            $records_data = [];
            $i = 0;
            foreach ($records as $key => $value) {
                $duration = (int)$value->duration; //((int)$value->duration >= 0) ? $value->duration : 0;
                $course_module_name = $this->get_course_module_name($value->cmod_id);
                if ($course_module_name) {
                    $records_data[$i]['sno'] = $i + 1;
                    $records_data[$i]['course_module_id'] = $value->cmod_id;
                    $records_data[$i]['course_module_name'] = $course_module_name;
                    $records_data[$i]['duration'] = $this->get_duration_time($duration);
                    $i++;
                    $template_content['course_id'] = $value->course_id;
                    $template_content['course_name'] = $value->course_name;
                    $template_content['user_id'] = $value->user_id;
                    $template_content['user_name'] = $value->firstname . ' ' . $value->lastname;
                }
            }
            $template_content['records'] = (array)$records_data;

            $url = $_SERVER['REQUEST_URI'];
            $url_path = parse_url($url, PHP_URL_PATH);
            $template_content['path'] = $url_path;
        }


        return $template_content;
    }

    public function output($course_id = '', $user_id = '')
    {
        global $OUTPUT;
        $template_content = [];
        if ($course_id and $user_id) {
            $template_content = $this->export_for_template_course_user($course_id, $user_id);
        } elseif ($course_id) {
            $template_content = $this->export_for_template_course($course_id);
        } else {
            $template_content = $this->export_for_template_course();
        }
        $output_table = $OUTPUT->render_from_template('theme_skilllab/skilllab_pages/user-time-track', $template_content);
        return $output_table;
    }
}
