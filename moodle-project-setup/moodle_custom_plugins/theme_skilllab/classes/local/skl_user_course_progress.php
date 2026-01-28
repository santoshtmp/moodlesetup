<?php

namespace theme_skilllab\local;

use stdClass;
use theme_skilllab\util\UtilUser_handler;

require_once($CFG->dirroot . '/theme/skilllab/lib.php');

class skl_user_course_progress
{
    /**
     * @param string $table  name
     */
    public static $table = 'skl_user_course_progress';

    /**
     * Insert or update skl_user_course_progress
     */
    public static function skl_user_course_progress($courseid, $userid)
    {
        global $DB;

        $course = get_course($courseid);
        $progress_status = UtilUser_handler::get_user_course_progress($course, $userid);
        $status = false;
        if ($progress_status) {
            $data = new stdClass();
            $data->courseid = $courseid;
            $data->userid = $userid;
            $data->progress_status = $progress_status;
            $table_data =  $DB->get_record(self::$table, ['courseid' => $courseid, 'userid' => $userid], $fields = '*', IGNORE_MISSING);
            if ($table_data) {
                $data->id = $table_data->id;
                $status = $DB->update_record(self::$table, $data);
            } else {
                $status = $DB->insert_record(self::$table, $data);
            }
        }

        return $status;
    }


    /**
     * check and update only for student
     */
    public static function update_user_course_progress($course, $userid)
    {
        global $DB, $USER;
        $context_course = \context_course::instance($course->id);
        $only_student = has_capability('moodle/course:isincompletionreports', $context_course, $userid);
        if ($only_student) {
            $progress_status = UtilUser_handler::get_user_course_progress($course, $userid);
            $data = new stdClass();
            $data->courseid = $course->id;
            $data->userid = $userid;
            $data->progress_status = $progress_status;
            $table_data = $DB->get_record(self::$table, ['courseid' => $course->id, 'userid' => $userid], $fields = '*', IGNORE_MISSING);
            if ($table_data && ($progress_status != $table_data->progress_status)) {
                $data->id = $table_data->id;
                $status = $DB->update_record(self::$table, $data);
            }
        }
    }
}
