<?php

namespace theme_skilllab\local;

use stdClass;

require_once($CFG->dirroot . '/theme/skilllab/lib.php');

class skl_time_track
{

    /**
     * @param string $table time track table name
     */
    protected static $table = 'skl_time_track';

    /**
     * Insert or update skl_time_track
     */
    public static function set_time_track()
    {
        global $DB, $SESSION;
        $status = false;
        if (!isset($SESSION->skl_time_track)) {
            return $status;
        }
        // get session time track data and match with the provided data.
        $session_time_track = $SESSION->skl_time_track;
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
        // 
        $skl_time_track = get_config('theme_skilllab', 'skl_time_track');
        if (!$skl_time_track) {
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
        $data->sync_csc = 0;
        $data->timemodified = time();
        $conditions =  [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'cmod_id' => $cmod_id
        ];
        $table_data =  $DB->get_record(self::$table, $conditions, $fields = '*', IGNORE_MISSING);
        if ($table_data) {
            $old_duration = (int)$table_data->duration;
            $old_duration = ($old_duration >= 0) ? $old_duration : 0;
            $data->id = $table_data->id;
            $data->duration = $data->duration + $old_duration;
            $status = $DB->update_record(self::$table, $data);
        } else {
            $data->timecreated = time();
            $status = $DB->insert_record(self::$table, $data);
        }
        return ($status) ? $duration : false;
    }

    /**
     * delete time_track data if course is deleted
     */
    public static function delete_time_track_course_id($course_id)
    {
        global $DB;
        $DB->delete_records(self::$table, ['course_id' => $course_id]);
    }

    /**
     * delete time_track data if cmod is deleted
     */
    public static function delete_time_track_cmod_id($cmod_id)
    {
        global $DB;
        $DB->delete_records(self::$table, ['cmod_id' => $cmod_id]);
    }

    /**
     * delete time_track data if cmod value is zero
     * this is for old data
     */
    public static function delete_time_track_cmod_id_zero()
    {
        global $DB;
        $DB->delete_records(self::$table, ['cmod_id' => 0]);
    }

    /**
     * manage_old_negative_time_track
     * this is for old data
     */
    public static function manage_old_negative_time_track()
    {
        global $DB;
        $records = $DB->get_records_select(self::$table, 'duration < 0');
        foreach ($records as $record) {
            $time_taken = 0;

            $query = 'SELECT 
                    modules.name As mod_name,
                    course_modules.instance As instance
                FROM {course_modules} course_modules 
                JOIN {modules} AS modules ON modules.id = course_modules.module 
                WHERE course_modules.id = :course_modules_id';
            $params = [
                'course_modules_id' => $record->cmod_id
            ];
            $module_info = $DB->get_record_sql($query, $params);
            if ($module_info) {
                if ($module_info->mod_name == 'quiz') {
                    $quiz_id = $module_info->instance;
                    // Fetch the quiz attempt details
                    $attempts = $DB->get_records('quiz_attempts', [
                        'quiz' => $quiz_id,
                        'userid' => $record->user_id,
                        'state' => 'finished'
                    ], 'timestart, timefinish');
                    foreach ($attempts as $key => $attempt) {
                        if ($attempt) {
                            $time_taken += (int)$attempt->timefinish - (int)$attempt->timestart;
                        }
                    }
                }
            }
            // 
            $data = new stdClass();
            $data->id = $record->id;
            $data->duration = $time_taken;
            $data->timemodified = time();
            $status = $DB->update_record(self::$table, $data);
        }
    }

    /**
     * get particular user and course total time_duration
     */
    public static function get_user_course_time_duration($course_id, $user_id)
    {
        global $DB;
        // set data in csc 
        $query = 'SELECT SUM(skl_time_track.duration) AS total_duration 
         FROM {skl_time_track} skl_time_track 
         WHERE skl_time_track.course_id = :course_id 
             AND skl_time_track.user_id = :user_id';
        $sql_params = [
            'course_id' => $course_id,
            'user_id' => $user_id
        ];
        $time_records = $DB->get_record_sql($query, $sql_params);
        return isset($time_records->total_duration) ? $time_records->total_duration : 0;
    }

    /**
     * get all un sync_csc value
     */
    public static function get_unsync_csc_time_track()
    {
        global $DB;

        $sql_query = "SELECT 
                user_id AS user_id, 
                course_id AS course_id, 
                MAX(timemodified) AS latest_timemodified
            FROM {skl_time_track}
            WHERE sync_csc = :sync_csc 
            GROUP BY user_id, course_id
        ";
        $sql_params = [
            'sync_csc' => 0
        ];
        $records = $DB->get_records_sql($sql_query, $sql_params);

        return $records;
    }

    /**
     * start_time_track_session_process
     * @param int $course_id
     * @param int $cmod_id
     * Call the `init` function on `theme_skilllab/time_track/time_track`
     *
     */
    public static function start_time_track_session_process($course_id, $cmod_id, $user_id = '')
    {
        global  $USER, $PAGE, $SESSION;
        $skl_time_track = get_config('theme_skilllab', 'skl_time_track');
        if ($skl_time_track) {
            $page_data = [
                'start_time' => time(),
                'user_id' => ($user_id) ? $user_id : $USER->id,
                'course_id' => $course_id,
                'cmod_id' => $cmod_id,
            ];
            $SESSION->skl_time_track = $page_data;
            $PAGE->requires->js_call_amd('theme_skilllab/time_track/time_track', 'init', [$page_data]);
        }
    }

    /**
     * end_time_track_session_process
     */
    public static function end_time_track_session_process()
    {
        global $SESSION;
        $SESSION->skl_time_track = '';
        unset($SESSION->skl_time_track);
    }

    // 
    public static function undate_sync_csc_time_track($user_id, $course_id)
    {
        global $DB;
        $conditions =  [
            'user_id' => $user_id,
            'course_id' => $course_id,
        ];
        $table_datas =  $DB->get_records(self::$table, $conditions, '', $fields = '*');
        if ($table_datas) {
            foreach ($table_datas as $key => $table_data) {
                // $data = new stdClass();
                // $data->id = $table_data->id;
                $table_data->sync_csc = 1;
                $table_data->timemodified = time();
                $DB->update_record(self::$table, $table_data);
            }
        }
    }
}
