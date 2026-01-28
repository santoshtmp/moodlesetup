<?php

namespace theme_skilllab\local;

use stdClass;

require_once($CFG->dirroot . '/theme/skilllab/lib.php');

class skl_custom_course_field
{
    /**
     * Insert or update skill lab custom course field
     */
    public function skl_custom_course_field($courseid, $first)
    {
        global $DB;
        $table = 'skl_custom_course_field';
        $courseInfo = get_course_info($courseid, $during_create = false);
        $data = new stdClass();
        $data->courseid = $courseInfo['id'];
        $data->skill_level = $courseInfo['skill_level'];
        $data->course_duration = $courseInfo['course_duration'];
        if ($first) {
            $DB->insert_record($table, $data);
        } else {
            $custom_id =  $DB->get_record($table, ['courseid' => $courseid], $fields = '*', IGNORE_MISSING);
            if ($custom_id) {
                $data->id = $custom_id->id;
                $DB->update_record($table, $data);
            }
        }
    }
}
