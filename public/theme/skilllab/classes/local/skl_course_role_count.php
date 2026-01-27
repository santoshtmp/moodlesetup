<?php

namespace theme_skilllab\local;

use stdClass;

class skl_course_role_count
{

    protected $table = 'skl_course_role_count';
    protected $userid;
    protected $courseid;
    protected $context;
    protected $role_id;

    /**
     * Insert or update skill lab course role count
     */
    public function role_assign($role_id, $context_id)
    {
        global $DB;
        $this->role_id = (int)$role_id;
        try {
            $transaction = $DB->start_delegated_transaction();

            $context = \context::instance_by_id($context_id, MUST_EXIST);
            if ($context->contextlevel == CONTEXT_COURSE) {
                $this->courseid = $context->instanceid;
                // $get_user_roles = get_user_roles($this->context, $this->userid);

                $data = new stdClass();
                $data->courseid = $this->courseid;
                $data->role = $this->role_id;
                $course_role =  $DB->get_record($this->table, ['courseid' => $this->courseid, 'role' => $data->role], $fields = '*', IGNORE_MISSING);
                if ($course_role) {
                    $data->id = $course_role->id;
                    if ($data->role == 5) {
                        $context_course = \context_course::instance($data->courseid);
                        $data->count = count_enrolled_users($context_course, 'moodle/course:isincompletionreports');
                    } else {
                        $data->count = (int)($course_role->count + 1);
                    }
                    $status  = $DB->update_record($this->table, $data);
                } else {
                    $data->count = (int)1;
                    $status = $DB->insert_record($this->table, $data);
                }
            }
            $transaction->allow_commit();
        } catch (\Exception $e) {
            try {
                $transaction->rollback($e);
            } catch (\Exception $e) {
                $status = false;
            }
            $status = false;
        }
        return $status;
    }


    /**
     * 
     */
    public function role_unassign($role_id, $context_id)
    {
        global $DB;
        $this->role_id = (int)$role_id;
        try {
            $transaction = $DB->start_delegated_transaction();

            $context = \context::instance_by_id($context_id, MUST_EXIST);
            if ($context->contextlevel == CONTEXT_COURSE) {
                $this->courseid = $context->instanceid;
                // $get_user_roles = get_user_roles($this->context, $this->userid);

                $data = new stdClass();
                $data->courseid = $this->courseid;
                $data->role = $this->role_id;
                $course_role =  $DB->get_record($this->table, ['courseid' => $this->courseid, 'role' => $data->role], $fields = '*', IGNORE_MISSING);
                if ($course_role) {
                    $data->id = $course_role->id;
                    if ($data->role == 5) {
                        $context_course = \context_course::instance($data->courseid);
                        $data->count = count_enrolled_users($context_course, 'moodle/course:isincompletionreports');
                    } else {
                        $data->count = (int)($course_role->count - 1);
                    }
                    if ($data->count >= 0) {
                        $status  = $DB->update_record($this->table, $data);
                    }
                    $status = true;
                } else {
                    $status = false;
                }
            }
            $transaction->allow_commit();
        } catch (\Exception $e) {
            try {
                $transaction->rollback($e);
            } catch (\Exception $e) {
                $status = false;
            }
            $status = false;
        }
        return $status;
    }

    public function course_creade_role_assign($courseid)
    {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            $context = \context_course::instance($courseid);
            if ($context->contextlevel == CONTEXT_COURSE) {
                $data = new stdClass();
                $data->courseid = $courseid;
                $data->role = 5;
                $data->count = (int)0;
                $status = $DB->insert_record($this->table, $data);
            }
            $transaction->allow_commit();
        } catch (\Exception $e) {
            try {
                $transaction->rollback($e);
            } catch (\Exception $e) {
                $status = false;
            }
            $status = false;
        }
        return $status;
    }
}
