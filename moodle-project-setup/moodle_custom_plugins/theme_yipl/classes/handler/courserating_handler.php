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

use core\output\action_menu;
use core\output\html_writer;
use core\output\pix_icon;
use moodle_url;
use stdClass;


/**
 *
 * @package    theme_yipl
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courserating_handler
{
    // table name
    protected static $courserating_table = 'yipl_courserating';

    /**
     * Save Data
     * @param object $mform_data
     * @param object $data
     * @param string $return_url
     */
    public static function save_data($mform_data = '', $custom_data = [], $return_url = '')
    {
        global $DB, $PAGE, $USER, $COURSE;
        $return_url = ($return_url) ?: $PAGE->url->out(false);

        try {
            $status = false;
            // Form was submitted and validated, process the data

            $data = new stdClass();
            if ($mform_data) {
                $data->id = isset($mform_data->id) ? $mform_data->id : 0;
                $data->course = $mform_data->course;
                $data->user = $mform_data->user;
                $data->rating = $mform_data->rating;
                $data->feedback = $mform_data->feedback;
            } else if ($custom_data) {
                $data->id = isset($custom_data['id']) ? (int)$custom_data['id'] : 0;
                $data->course = isset($custom_data['course']) ? (int)$custom_data['course'] : $COURSE->id;
                $data->user = isset($custom_data['user']) ? (int)$custom_data['user'] : $USER->id;
                $data->rating = isset($custom_data['rating']) ? (int)$custom_data['rating'] : 0;
                $data->feedback = isset($custom_data['feedback']) ? $custom_data['feedback'] : 0;
            }
            $data->timemodified = time();
            // 
            if ($data->id) {
                $data_exists = $DB->record_exists(self::$courserating_table, ['id' =>  $data->id]);
                if ($data_exists) {
                    $status =  $DB->update_record(self::$courserating_table, $data);
                    if ($status) {
                        $message =  "Your course rating sucessfully updated.";
                    }
                }
            } else {
                $data->timecreated = time();
                $status = $DB->insert_record(self::$courserating_table, $data);
                if ($status) {
                    $message =  "Your course rating sucessfully saved.";
                }
            }
        } catch (\Throwable $th) {
            $message = "Error on saving course rating :: ";
            $message .= "\n" . $th->getMessage();
        }

        redirect($return_url, $message);
    }

    /**
     * Delete Data
     * @param int $id
     */
    public static function delete_data($id, $return_url)
    {
        try {
            global $DB;
            $data = $DB->get_record(self::$courserating_table, ['id' => $id]);
            if ($data) {
                $delete =  $DB->delete_records(self::$courserating_table, ['id' => $data->id]);
                if ($delete) {
                    $message = "Course rating deleted.";
                } else {
                    $message = "Error on course rating deleted.";
                }
            } else {
                $message =  "Course rating data missing";
            }
        } catch (\Throwable $th) {
            $message = "Error :: ";
            $message .= "\n" . $th->getMessage();
        }

        redirect($return_url, $message);
    }

    /**
     * edit form data
     * @param object $mform
     * @param int $id
     */
    public static function edit_form($mform, $id, $return_url)
    {

        try {
            global $DB, $USER, $CFG;
            $data = $DB->get_record(self::$courserating_table, ['id' => $id]);
            if ($data) {
                $entry = new stdClass();
                $entry->id = $id;
                $entry->action = 'edit';
                $entry->course = $data->course;
                $entry->user = $data->user;
                $entry->rating = $data->rating;
                $entry->feedback = $data->feedback;

                $mform->set_data($entry);
                return $mform;
            } else {
                $message = "Data missing.";
            }
        } catch (\Throwable $th) {
            //throw $th;
            $message = " Fail because: " . $th->getMessage();
        }
        redirect($return_url, $message);
    }

    /**
     * Get save data
     * @param \moodle_url $baseurl This is base url for the table 
     * @param int $per_page_data This is data shown in a page
     * @return strings HTML section of the table
     */
    public static function get_data_in_table($baseurl, int $per_page_data = -1)
    {
        global $CFG, $DB, $PAGE;
        $output_data = '';
        // 
        require_once($CFG->libdir . '/tablelib.php');
        $table = new \flexible_table('moodle-data');
        $tablecolumns = ['id', 'course', 'user', 'rating', 'feedback'];
        $tableheaders = ['id', 'course id', 'user id', 'rating', 'feedback'];
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($baseurl);
        $table->sortable(true);
        $table->set_attribute('id', '');
        $table->set_attribute('class', '');
        $table->set_control_variables(array(
            TABLE_VAR_SORT    => 'ssort',
            TABLE_VAR_IFIRST  => 'sifirst',
            TABLE_VAR_ILAST   => 'silast',
            TABLE_VAR_PAGE    => 'spage'
        ));
        $table->no_sorting('id');
        $table->setup();
        if ($per_page_data > 0) {
            $table->pagesize($per_page_data, $DB->count_records(self::$courserating_table, []));
        }
        // $limitfrom = $table->get_page_start();
        // $limitnum = $table->get_page_size();
        if (isset($_GET['ssort']) && $table->get_sql_sort()) {
            $sort = $table->get_sql_sort();
        } else {
            $sort = 'id DESC';
        }
        // 
        $data_records = self::get_data_in_array($per_page_data, $sort);
        ob_start();
        if ($data_records) {
            foreach ($data_records as $record) {
                $row = array();
                $row[] = $record->id;
                $row[] = $record->course;
                $row[] = $record->user;
                $row[] = $record->rating;
                $row[] = $record->feedback;
                $table->add_data($row);
            }
        }
        $table->finish_output();
        $output_data = ob_get_contents();
        ob_end_clean();
        // 
        return $output_data;
    }


    /**
     * Get data in array format
     * @param int $per_page_data
     * @param string $sort
     * @return array List of data
     */
    public static function get_data_in_array(int $per_page_data = 30, $sort = 'id DESC')
    {
        global $DB;
        $testis_output = [];
        // 
        $limitfrom = $limitnum = 0;
        // if ($per_page_data > 0) {
        //     $limitnum = $per_page_data;
        //     if ($page_number > 0) {
        //         $limitfrom = $limitnum * $page_number;
        //     }
        // }
        // execute sql query
        $data_records = $DB->get_records(self::$courserating_table, [], $sort = '', $fields = '*', $limitfrom = $limitfrom, $limitnum = $limitnum);
        return $data_records;

        if ($data_records) {
            $i = $limitfrom + 1;
            foreach ($data_records as $record) {
                $row = [
                    'id' => $record->id,
                    'sn' => $i,
                    'name' => format_string($record->name),
                    'timecreated' => $record->timecreated,
                    'timemodified' => $record->timemodified,
                ];
                $testis_output[] = $row;
                $i =  $i + 1;
            }
        }
        return $testis_output;
    }

    // ----- END -----
}
