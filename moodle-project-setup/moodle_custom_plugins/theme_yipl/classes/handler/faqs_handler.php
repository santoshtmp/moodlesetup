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
class faqs_handler
{
    // table name
    protected static $faq_table = 'yipl_faq';
    protected static $faq_category_table = 'yipl_faq_category';

    /**
     * Save Data
     * @param object $data
     * @param string $return_url
     */
    public static function save_faqs_question_data($mform_data, $save_return_url, $update_return_url)
    {
        $return_url = $save_return_url;
        try {
            global $DB, $CFG, $USER;
            $status = false;
            // Move embedded files into a proper filearea and adjust HTML links to match
            // file_prepare_standard_editor  file_postupdate_standard_editor
            $context =  \context_system::instance();
            $editor_options = array(
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes,
                'trusttext' => true,
                'noclean' => true,
                'context' => $context,
                'subdirs' => false
            );
            $component = 'theme_yipl';
            $filearea = 'content';
            $file_itemid = $mform_data->content['itemid'];
            $content = file_save_draft_area_files($mform_data->content['itemid'], $context->id, $component, $filearea, $file_itemid, $editor_options, $mform_data->content['text']);
            // Form was submitted and validated, process the data
            $data = new stdClass();
            $data->id = isset($mform_data->id) ? $mform_data->id : 0;
            $data->faq_category = isset($mform_data->faq_category) ? implode(",", $mform_data->faq_category) : 0;
            $data->title = $mform_data->title;
            $data->content = $content;
            $data->contentformat = $mform_data->content['format'];
            $data->contentitemid = $mform_data->content['itemid'];
            $data->status = isset($mform_data->status) ? 1 : 0; // status 0=draft, 1=published
            $data->timemodified = time();
            if (!$data->title || !$data->content) {
                $message = "Title or content cannot be empty";
                redirect($update_return_url, $message);
            }
            // 

            if ($data->id && ($mform_data->action == 'edit')) {
                $data_exists = $DB->record_exists(self::$faq_table, ['id' =>  $data->id]);
                if ($data_exists) {
                    $status =  $DB->update_record(self::$faq_table, $data);
                    if ($status) {
                        $a = new stdClass();
                        $a->name = '"' . $data->title . '" ';
                        $message = get_string('faq_updated', 'theme_yipl', $a);
                    }
                }
                $return_url = $update_return_url;
            } else {
                $data->user_id = $USER->id;
                $data->timecreated = time();
                $status = $DB->insert_record(self::$faq_table, $data);
                if ($status) {
                    $a = new stdClass();
                    $a->name = '"' . $data->title . '" ';
                    $message =  get_string('faq_added', 'theme_yipl', $a);
                }
                $return_url = $save_return_url;
            }
        } catch (\Throwable $th) {
            $message = get_string('faq_error_submit', 'theme_yipl');
            $message .= "\n :: " . $th->getMessage();
        }

        redirect($return_url, $message);
    }

    /**
     * Delete Data
     * @param int $id
     */
    public static function delete_faqs_question_data($id, $return_url)
    {
        try {
            global $DB, $USER;
            $data = $DB->get_record(self::$faq_table, ['id' => $id]);
            if ($data) {
                $delete =  $DB->delete_records(self::$faq_table, ['id' => $data->id]);
                if ($delete) {
                    $a = new stdClass();
                    $a->name = '"' . $data->title . '" ';
                    $message =  get_string('faq_delete', 'theme_yipl', $a);
                } else {
                    $message =  get_string('faq_error_delete', 'theme_yipl');
                }
            } else {
                $message =  get_string('faq_delete_missing', 'theme_yipl');
            }
        } catch (\Throwable $th) {
            $message = get_string('faq_error_delete', 'theme_yipl');
            $message .= "\n" . $th->getMessage();
        }

        redirect($return_url, $message);
    }

    /**
     * edit form data
     * @param object $mform
     * @param int $id
     */
    public static function edit_faqs_question_form($mform, $id, $return_url)
    {

        try {
            global $DB, $USER, $CFG;
            $data = $DB->get_record(self::$faq_table, ['id' => $id]);
            if ($data) {
                $context = \context_system::instance();
                $editor_options = array(
                    'maxfiles' => EDITOR_UNLIMITED_FILES,
                    'maxbytes' => $CFG->maxbytes,
                    'trusttext' => true,
                    'noclean' => true,
                    'context' =>  $context,
                    'subdirs' => false
                );
                $component = 'theme_yipl';
                $filearea = 'content';
                $file_itemid = $data->contentitemid;
                $content = file_prepare_standard_editor($data, 'content', $editor_options, $context, $component, $filearea, $file_itemid);

                $entry = new stdClass();
                $entry->id = $id;
                $entry->action = 'edit';
                $entry->title = $data->title;
                $entry->status = $data->status;
                $entry->content = $content->content_editor;
                $mform->set_data($entry);
                return $mform;
            } else {
                $message = get_string('faq_missing', 'theme_yipl');
            }
        } catch (\Throwable $th) {
            //throw $th;
            $message = $th->getMessage();
        }
        redirect($return_url, $message);
    }

    /**
     * Get save data
     * @param \moodle_url $baseurl This is base url for the table 
     * @param int $per_page_data This is data shown in a page
     * @return strings HTML section of the table
     */
    public static function get_faqs_question_data_in_table($baseurl, int $per_page_data = 12)
    {
        global $CFG, $DB;
        require_once("$CFG->libdir/filelib.php");
        $action_base_url = "/theme/yipl/page/faqs/edit.php";
        $output_data = '';
        // 
        require_once($CFG->libdir . '/tablelib.php');
        $table = new \flexible_table('moodle-data');
        $tablecolumns = ['id', 'title', 'category', 'status', 'action'];
        $tableheaders = ['S.N', 'Title', 'Category', 'Status', 'Action'];
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($baseurl);
        $table->sortable(true);
        $table->set_attribute('id', 'moodle-data');
        $table->set_attribute('class', 'faqs-question-answer');
        $table->set_control_variables(array(
            TABLE_VAR_SORT    => 'ssort',
            TABLE_VAR_IFIRST  => 'sifirst',
            TABLE_VAR_ILAST   => 'silast',
            TABLE_VAR_PAGE    => 'spage'
        ));
        $table->no_sorting('action');
        $table->no_sorting('id');
        $table->setup();
        if ($per_page_data > 0) {
            $table->pagesize($per_page_data, $DB->count_records(self::$faq_table, []));
        }
        // $limitfrom = $table->get_page_start();
        // $limitnum = $table->get_page_size();
        if (isset($_GET['ssort']) && $table->get_sql_sort()) {
            $sort = $table->get_sql_sort();
        } else {
            $sort = 'id DESC';
        }
        // 
        $data_records = self::get_faqs_question_data_in_array($per_page_data, "all", $sort);
        ob_start();
        if ($data_records) {
            foreach ($data_records as $record) {
                $category = '';
                if ($record['faq_category_fullname'] && is_array($record['faq_category_fullname'])) {
                    foreach ($record['faq_category_fullname'] as $key => $faq_category_fullname) {
                        $category .= '<span>';
                        $category .= $faq_category_fullname;
                        $category .= '</span>';
                    }
                }
                $edit_url = new moodle_url($action_base_url, ["action" => "edit", "id" => $record['id'], "sesskey" => sesskey()]);
                $delete_url = new moodle_url($action_base_url, ["action" => "delete", "id" => $record['id'], "sesskey" => sesskey()]);

                $row = array();
                $row[] = $record['sn'];
                $row[] = $record['title'];
                $row[] = $category;
                $row[] = ($record['status']) ? "Published" : "Draft";
                $row[] = '
                <a href="' . $edit_url->out() . '" class="btn btn-primary">Edit</a> 
                <a href="' . $delete_url->out()  . '" class="btn btn-secondary">Delete</a> 
                ';
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
     * @param string $status published, draft, all
     * @param string $sort
     * @return array List of FAQs
     */
    public static function get_faqs_question_data_in_array(int $per_page_data = 30, $status = "published", $sort = 'id DESC')
    {
        global $DB;
        $faqs_output = [];
        $context = \context_system::instance();
        // Get Parameters
        $get_form_search_param = self::get_form_search_param();
        $page_number = $get_form_search_param['page_number'];
        // $id = $get_form_search_param['id'];
        $category_id = $get_form_search_param['category_id'];
        $title = $get_form_search_param['title'];
        $category_fullname = $get_form_search_param['category_fullname'];
        $category_shortname = $get_form_search_param['category_shortname'];

        // 
        $limitfrom = $limitnum = 0;
        if ($per_page_data > 0) {
            $limitnum = $per_page_data;
            if ($page_number > 0) {
                $limitfrom = $limitnum * $page_number;
            }
        }
        // sql parameters and where condition 
        $sql_params = [];
        $where_condition = [];
        $where_condition_apply = '';
        if ($title) {
            $sql_params['title'] = "%" . $title . "%";
            $where_condition[] = 'faq.title LIKE :title';
        }
        if ($category_fullname) {
            $sql_params['category_fullname'] = $category_fullname;
            $where_condition[] = 'faq_category.fullname LIKE :category_fullname';
        }
        if ($category_id) {
            $sql_params['category_id'] = $category_id;
            $where_condition[] = 'FIND_IN_SET(:category_id, faq.faq_category) > 0';
        }
        if ($category_shortname) {
            $sql_params['category_shortname'] = $category_shortname;
            $where_condition[] = 'faq_category.shortname = :category_shortname';
        }
        // if ($id) {
        //     $sql_params['id'] = $id;
        //     $where_condition[] = 'faq.id = :id';
        // }
        if ($status === 'published') {
            $sql_params['status'] = 1;
            $where_condition[] = 'faq.status = :status';
        } elseif ($status === 'draft') {
            $sql_params['status'] = 0;
            $where_condition[] = 'faq.status = :status';
        } elseif ($status === 'all') {
            // don't apply status condition as we need all
        } else {
            // $status value is unexpected
            return false;
        }
        if (count($where_condition) > 0) {
            $where_condition_apply = "WHERE " . implode(" AND ", $where_condition);
        }
        // sql query
        $sql_query = 'SELECT faq.id AS id, 
                    faq.title AS title,
                    faq.content AS content,
                    faq.faq_category AS faq_category,
                    faq.contentformat AS contentformat,
                    faq.contentitemid AS contentitemid,
                    faq.status AS status,
                    faq.timecreated AS timecreated,
                    faq.timemodified AS timemodified,
                    GROUP_CONCAT(faq_category.fullname) AS faq_category_fullname,
                    GROUP_CONCAT(faq_category.shortname) AS faq_category_shortname
            FROM {yipl_faq} AS faq
            LEFT JOIN {yipl_faq_category} AS faq_category 
                ON FIND_IN_SET(faq_category.id,faq.faq_category) > 0 ' . $where_condition_apply . '
            GROUP BY faq.id
            ORDER BY faq.id DESC
        ';
        // execute sql query
        $data_records = $DB->get_records_sql($sql_query, $sql_params, $limitfrom = $limitfrom, $limitnum = $limitnum);
        // $data_records = $DB->get_records(self::$faq_table, [], $sort, $fields = '*', $limitfrom = $limitfrom, $limitnum = $limitnum);

        if ($data_records) {
            $i = $limitfrom + 1;
            foreach ($data_records as $record) {
                $component = 'theme_yipl';
                $filearea = 'content';
                $file_itemid = $record->contentitemid;
                $content = file_rewrite_pluginfile_urls($record->content, 'pluginfile.php', $context->id, $component,  $filearea, $file_itemid);

                $row = [
                    'id' => $record->id,
                    'sn' => $i,
                    'title' => format_string($record->title),
                    'content' => format_text($content),
                    'status' => $record->status,
                    'timecreated' => $record->timecreated,
                    'timemodified' => $record->timemodified,
                    'faq_category' => ($record->faq_category) ? explode(",", $record->faq_category) : "",
                    'faq_category_fullname' => ($record->faq_category_fullname) ? explode(",", $record->faq_category_fullname) : "",
                    'faq_category_shortname' => ($record->faq_category_shortname) ? explode(",", $record->faq_category_shortname) : "",
                ];
                $faqs_output[] = $row;
                $i =  $i + 1;
            }
        }
        return $faqs_output;
    }

    /**
     * 
     */
    public static function get_search_form()
    {
        $title = optional_param('title', '', PARAM_TEXT);
        $category_shortname = optional_param('category_shortname', '', PARAM_TEXT);
        $url = new moodle_url('/theme/yipl/page/faqs/admin.php');

        $search_form = [
            'action' => $url->out(),
            'sesskey' => sesskey()
        ];

        $all_category = faqs_handler::get_faqs_category_data_in_array(-1);
        if (count($all_category) > 1) {
            $options = [];
            $options[] = [
                'name' => 'All Category',
                'value' => ''
            ];

            foreach ($all_category as $key => $category) {
                $url_param['category_shortname'] = $category['shortname'];
                // $url = new moodle_url('/theme/yipl/page/faqs/admin.php', $url_param);
                $options[] = [
                    'name' => $category['fullname'],
                    'value' => $category['shortname'],
                    'selected' => ($category_shortname == $category['shortname']) ? true : false,
                ];
            }

            $search_form['select_options'] = [
                'id' => 'faqs-cateory-filter-select',
                'options' =>  $options,
                'select_name' => "category_shortname"
            ];
        }

        $search_form['search'] = [
            'inputname' => 'title',
            'query' => $title,
            'searchstring' => 'Search FAQs'
        ];

        return $search_form;
    }

    /**
     * Get page parameters
     */
    public static function get_form_search_param()
    {
        // Get Parameters
        $page_number = optional_param('page', 0, PARAM_INT);
        $id = optional_param('id', 0, PARAM_INT);
        $category_id = optional_param('category_id', 0, PARAM_INT);
        $title = optional_param('title', '', PARAM_TEXT);
        $category_fullname = optional_param('category_fullname', '', PARAM_TEXT);
        $category_shortname = optional_param('category_shortname', '', PARAM_TEXT);
        $jump = optional_param('jump', '', PARAM_TEXT);
        if ($jump) {
            redirect($jump);
        }
        return [
            'page_number' => $page_number,
            'id' => $id,
            'category_id' => $category_id,
            'title' => $title,
            'category_fullname' => $category_fullname,
            'category_shortname' => $category_shortname,
            'faq_get_param_present' => ($id > 1 || $id > 1 || $title || $category_fullname || $category_shortname) ? true : false
        ];
    }


    /**
     * Save Data
     * @param object $data
     * @param string $return_url
     */
    public static function save_faqs_category_data($mform_data, $save_return_url, $update_return_url)
    {
        try {
            global $DB, $CFG, $USER;
            $status = false;
            // Form was submitted and validated, process the data
            $data = new stdClass();
            $data->id = isset($mform_data->id) ? $mform_data->id : 0;
            $data->user_id = $USER->id;
            $data->fullname = $mform_data->fullname;
            $data->shortname = ($mform_data->shortname) ? str_replace([' ', '_'], '-', strtolower($mform_data->shortname)) : str_replace([' ', '_'], '-', strtolower($mform_data->fullname));
            $data->timemodified = time();

            if (!$data->fullname || !$data->shortname) {
                $message = "FAQs category fullname or shortname cannot be empty";
                redirect($update_return_url, $message);
            }
            // 

            if ($data->id && ($mform_data->action == 'edit')) {
                $data_exists = $DB->record_exists(self::$faq_category_table, ['id' =>  $data->id]);
                if ($data_exists) {
                    $status =  $DB->update_record(self::$faq_category_table, $data);
                    if ($status) {
                        $a = new stdClass();
                        $a->name = 'category "' . $data->fullname . '" ';
                        $message = get_string('faq_updated', 'theme_yipl', $a);
                    }
                }
                $return_url = $update_return_url;
            } else {
                $data->timecreated = time();
                $status = $DB->insert_record(self::$faq_category_table, $data);
                if ($status) {
                    $a = new stdClass();
                    $a->name = 'category "' . $data->fullname . '" ';
                    $message =  get_string('faq_added', 'theme_yipl', $a);
                }
                $return_url = $save_return_url;
            }
        } catch (\Throwable $th) {
            $message = get_string('faq_error_submit', 'theme_yipl');
            $message .= "\n" . $th->getMessage();
        }

        redirect($return_url, $message);
    }

    /**
     * Delete Data
     * @param int $id
     */
    public static function delete_faqs_category_data($id, $return_url)
    {
        try {
            global $DB, $USER;
            $data = $DB->get_record(self::$faq_category_table, ['id' => $id]);
            if ($data) {
                $delete =  $DB->delete_records(self::$faq_category_table, ['id' => $data->id]);
                if ($delete) {
                    $a = new stdClass();
                    $a->name = '"' . $data->fullname . '" ';
                    $message =  get_string('faq_delete_category', 'theme_yipl', $a);
                } else {
                    $message =  get_string('faq_error_delete', 'theme_yipl');
                }
            } else {
                $message =  get_string('faq_delete_missing', 'theme_yipl');
            }
        } catch (\Throwable $th) {
            $message = get_string('faq_error_delete', 'theme_yipl');
            $message .= "\n" . $th->getMessage();
        }

        redirect($return_url, $message);
    }

    /**
     * edit form data
     * @param object $mform
     * @param int $id
     */
    public static function edit_faqs_category_form($mform, $id, $return_url)
    {

        try {
            global $DB, $USER, $CFG;
            $data = $DB->get_record(self::$faq_category_table, ['id' => $id]);
            if ($data) {

                $entry = new stdClass();
                $entry->id = $id;
                $entry->action = 'edit';
                $entry->fullname = $data->fullname;
                $entry->shortname = $data->shortname;
                $mform->set_data($entry);
                return $mform;
            } else {
                $message = get_string('faq_missing', 'theme_yipl');
            }
        } catch (\Throwable $th) {
            //throw $th;
            $message = $th->getMessage();
        }
        redirect($return_url, $message);
    }

    /**
     * Get save data
     * @param int $per_page_data
     */
    public static function get_save_faqs_category_data_in_table(int $per_page_data = 12)
    {
        global $CFG, $DB;

        $output_data = '';
        $url = new moodle_url('/theme/yipl/page/faqs/category.php');

        // 
        require_once($CFG->libdir . '/tablelib.php');
        $table = new \flexible_table('moodle-data');
        $tablecolumns = ['id', 'full_name', 'shortname', 'action'];
        $tableheaders = ['S.N', 'Fullname', 'Shortname', 'Action'];
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($url);
        $table->sortable(true);
        $table->set_attribute('id', 'moodle-data');
        $table->set_attribute('class', 'table');
        $table->set_control_variables(array(
            TABLE_VAR_SORT    => 'ssort',
            TABLE_VAR_IFIRST  => 'sifirst',
            TABLE_VAR_ILAST   => 'silast',
            TABLE_VAR_PAGE    => 'spage'
        ));
        $table->no_sorting('action');
        $table->no_sorting('id');
        $table->setup();
        if ($per_page_data > 0) {
            $table->pagesize($per_page_data, $DB->count_records(self::$faq_category_table, []));
        }
        // $limitfrom = $table->get_page_start();
        // $limitnum = $table->get_page_size();
        if (isset($_GET['ssort']) && $table->get_sql_sort()) {
            $sort = $table->get_sql_sort();
        } else {
            $sort = 'id DESC';
        }
        // 
        $data_records = self::get_faqs_category_data_in_array($per_page_data, $sort);
        // $data_records = $DB->get_records(self::$faq_category_table, [], $sort, $fields = '*', $limitfrom = $limitfrom, $limitnum = $limitnum);

        ob_start();
        if ($data_records) {
            foreach ($data_records as $record) {
                $edit_url = $url->out() . '?action=edit&id=' . $record['id'] . '&sesskey=' . sesskey();
                $delete_url = $url->out() . '?action=delete&id=' . $record['id'] . '&sesskey=' . sesskey();

                $row = array();
                $row[] = $record['sn'];
                $row[] = $record['fullname'];
                $row[] = $record['shortname'];
                $row[] = '
                <a href="' . $edit_url . '" class="btn btn-primary">Edit</a> 
                <a href="' . $delete_url  . '" class="btn btn-secondary">Delete</a> 
                ';
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
     * @return array List of FAQs
     */
    public static function get_faqs_category_data_in_array(int $per_page_data = 30, $sort = 'id DESC')
    {
        global $DB;
        $faqs_output = [];
        $context = \context_system::instance();
        // Get Parameters
        $page_number = optional_param('page', 0, PARAM_INT);
        $faq_category_shortname = optional_param('category_shortname', '', PARAM_TEXT);

        // 
        $limitfrom = $limitnum = 0;
        if ($per_page_data > 0) {
            $limitnum = $per_page_data;
            if ($page_number > 0) {
                $limitfrom = $limitnum * $page_number;
            }
        }
        $data_records = $DB->get_records(self::$faq_category_table, [], $sort, $fields = '*', $limitfrom = $limitfrom, $limitnum = $limitnum);

        if ($data_records) {
            $i = $limitfrom + 1;
            foreach ($data_records as $record) {
                $row = [
                    'id' => $record->id,
                    'sn' => $i,
                    'fullname' => format_string($record->fullname),
                    'shortname' => $record->shortname,
                    'timecreated' => $record->timecreated,
                    'timemodified' => $record->timemodified,
                    'get_param_category_shortname' => ($faq_category_shortname == $record->shortname) ? true : false,
                ];
                $faqs_output[] = $row;
                $i =  $i + 1;
            }
        }
        return $faqs_output;
    }


    // ----- END -----
}
