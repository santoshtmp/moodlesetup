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

use core_xapi\local\state;
use moodle_url;
use stdClass;

/**
 *
 * @package    theme_yipl
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_pages_handler
{
    // table name
    protected static $custom_pages_table = 'yipl_custom_pages';

    /** */
    protected $page_id = 0;

    /**
     * 
     */
    public function __construct()
    {
        $this->page_id = optional_param('id', 0, PARAM_INT);
    }


    /**
     * Save Data
     * @param object $data
     * @param string $return_url
     */
    public static function save_data($mform_data, $save_return_url, $update_return_url)
    {
        $return_url = $save_return_url;
        // try {
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
        $data->title = $mform_data->title;
        $data->short_name = strtolower($mform_data->short_name);
        $data->content = $content;
        $data->contentformat = $mform_data->content['format'];
        $data->contentitemid = $mform_data->content['itemid'];
        $data->status = isset($mform_data->status) ? 1 : 0; // status 0=draft, 1=published
        $data->timemodified = time();
        if ($data->id && ($mform_data->action == 'edit')) {
            $data_exists = $DB->record_exists(self::$custom_pages_table, ['id' =>  $data->id]);
            if ($data_exists) {
                $status =  $DB->update_record(self::$custom_pages_table, $data);
                if ($status) {
                    $view_url = new moodle_url('/theme/yipl/page/view.php', ['id' => $data->id]);
                    $message = "Page is sucesfully updated. <a href='" . $view_url->out() . "'>View (" . $data->title . ") </a> ";
                }
            }
            $return_url = $update_return_url;
        } else {
            $data->user_id = $USER->id;
            $data->timecreated = time();
            $status = $DB->insert_record(self::$custom_pages_table, $data);
            if ($status) {
                $view_url = new moodle_url('/theme/yipl/page/view.php', ['id' => $status]);
                $message = "Page is sucesfully saved. <a href='" . $view_url->out() . "'>View (" . $data->title . ") </a> ";
            }
            $return_url = $save_return_url;
        }
        // } catch (\Throwable $th) {
        //     $message = "Error on submit :: ";
        //     $message .= "\n" . $th->getMessage();
        // }

        redirect($return_url, $message);
    }

    /**
     * Delete Data
     * @param int $id
     */
    public static function delete_data($id, $return_url)
    {
        try {
            global $DB, $USER;
            $data = $DB->get_record(self::$custom_pages_table, ['id' => $id]);
            if ($data) {
                $delete =  $DB->delete_records(self::$custom_pages_table, ['id' => $data->id]);
                if ($delete) {
                    $message = "Custom Page (" . $data->title . ") is sucesfully deleted.";
                } else {
                    $message =  "Error on delete";
                }
            } else {
                $message =  "Data is missing";
            }
        } catch (\Throwable $th) {
            $message = "Error on delete";
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
            $data = $DB->get_record(self::$custom_pages_table, ['id' => $id]);
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
                $entry->short_name = $data->short_name;
                $entry->content = $content->content_editor;
                $entry->status = $data->status;
                $mform->set_data($entry);
                return $mform;
            } else {
                $message = "Data is missing";
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
    public static function get_data_in_table($baseurl, int $per_page_data = 12)
    {
        global $CFG, $DB;
        require_once("$CFG->libdir/filelib.php");
        $action_base_url = "/theme/yipl/page/edit.php";
        $view_base_url = "/theme/yipl/page/view.php";
        $output_data = '';
        // 
        require_once($CFG->libdir . '/tablelib.php');
        $table = new \flexible_table('moodle-data');
        $tablecolumns = ['id', 'title', 'short_name', 'status', 'action'];
        $tableheaders = ['S.N', 'Title', 'short_name', 'Status', 'Action'];
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($baseurl);
        $table->sortable(true);
        $table->set_attribute('id', 'moodle-data');
        $table->set_attribute('class', 'custom-pages');
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
            $table->pagesize($per_page_data, $DB->count_records(self::$custom_pages_table, []));
        }
        // $limitfrom = $table->get_page_start();
        // $limitnum = $table->get_page_size();
        if (isset($_GET['ssort']) && $table->get_sql_sort()) {
            $sort = $table->get_sql_sort();
        } else {
            $sort = 'id DESC';
        }
        // 
        $data_records = self::get_data_in_array($per_page_data, 'all', $sort);
        ob_start();
        if ($data_records) {
            foreach ($data_records as $record) {

                $edit_url = new moodle_url($action_base_url, ["action" => "edit", "id" => $record['id'], "sesskey" => sesskey()]);
                $delete_url = new moodle_url($action_base_url, ["action" => "delete", "id" => $record['id'], "sesskey" => sesskey()]);
                $view_url = new moodle_url($view_base_url, ["id" => $record['id']]);
                $row = array();
                $row[] = $record['sn'];
                $row[] = $record['title'];
                $row[] = $record['short_name'];
                $row[] = ($record['status']) ? "Published" : "Draft";
                $row[] = '
                <a href="' . $edit_url->out() . '" class="btn btn-primary">Edit</a> 
                <a href="' . $delete_url->out()  . '" class="btn btn-secondary">Delete</a> 
                <a href="' . $view_url->out()  . '" class="btn btn-secondary">View</a> 
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
     * 
     */
    public static function get_search_form()
    {
        $search = optional_param('search', '', PARAM_TEXT);
        $url = new moodle_url('/theme/yipl/page/admin.php');

        $search_form = [
            'action' => $url->out(),
            'sesskey' => sesskey()
        ];

        $search_form['search'] = [
            'inputname' => 'search',
            'query' => $search,
            'searchstring' => 'Search'
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
        $search = optional_param('search', '', PARAM_TEXT);
        return [
            'page_number' => $page_number,
            'id' => $id,
            'search' => $search,
            'faq_get_param_present' => ($id > 1 || $search) ? true : false
        ];
    }
    /**
     * Get data in array format
     * @param int $per_page_data
     * @param string $status published, draft, all
     * @param string $sort
     * @return array List of FAQs
     */
    public static function get_data_in_array(
        int $per_page_data = 30,
        $status = "published",
        $sort = 'id DESC'
    ) {
        global $DB, $CFG;
        $faqs_output = [];
        $context = \context_system::instance();
        // Get Parameters
        $get_form_search_param = self::get_form_search_param();
        $page_number = $get_form_search_param['page_number'];
        $id = $get_form_search_param['id'];

        $search = $get_form_search_param['search'];

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
        if ($search) {
            $sql_params['search'] = "%" . $search . "%";
            $sql_params['search_short_name'] = "%" . $search . "%";
            $sql_params['search_content'] = "%" . $search . "%";
            $where_condition[] = '( page.title LIKE :search || page.short_name LIKE :search_short_name || page.content LIKE :search_content )';
        }
        if ($id) {
            $sql_params['id'] = $id;
            $where_condition[] = 'page.id = :id';
        }
        if ($status === 'published') {
            $sql_params['status'] = 1;
            $where_condition[] = 'page.status = :status';
        } elseif ($status === 'draft') {
            $sql_params['status'] = 0;
            $where_condition[] = 'page.status = :status';
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
        $sql_query = 'SELECT *        
        FROM {yipl_custom_pages} AS page ' .
            $where_condition_apply . '
        ORDER BY page.id DESC
        ';
        // execute sql query
        $data_records = $DB->get_records_sql($sql_query, $sql_params, $limitfrom = $limitfrom, $limitnum = $limitnum);
        // execute sql query
        // $data_records = $DB->get_records(self::$custom_pages_table, [], $sort, $fields = '*', $limitfrom = $limitfrom, $limitnum = $limitnum);

        if ($data_records) {
            $i = $limitfrom + 1;
            require_once($CFG->libdir . '/filelib.php');
            foreach ($data_records as $record) {
                $content = "";
                if ($record->content) {
                    $component = 'theme_yipl';
                    $filearea = 'content';
                    $file_itemid = $record->contentitemid;
                    $content = file_rewrite_pluginfile_urls($record->content, 'pluginfile.php', $context->id, $component,  $filearea, $file_itemid);
                }
                $row = [
                    'id' => $record->id,
                    'sn' => $i,
                    'title' => format_string($record->title),
                    'short_name' => $record->short_name,
                    'content' => format_text($content),
                    'status' => $record->status,
                    'timecreated' => $record->timecreated,
                    'timemodified' => $record->timemodified,
                ];
                $faqs_output[] = $row;
                $i =  $i + 1;
            }
        }
        return $faqs_output;
    }

    /**
     * @param int $id
     */
    public static function get_page_data($id = 0)
    {
        global $DB, $CFG;
        $id = ($id) ? $id : optional_param('id', 0, PARAM_INT);
        if (!$id) {
            return false;
        }
        $context = \context_system::instance();
        require_once($CFG->libdir . '/filelib.php');

        $data_record = $DB->get_record(self::$custom_pages_table, ['id' => $id]);
        if ($data_record) {
            if ($data_record->content) {
                $component = 'theme_yipl';
                $filearea = 'content';
                $file_itemid = $data_record->contentitemid;
                $data_record->content = file_rewrite_pluginfile_urls($data_record->content, 'pluginfile.php', $context->id, $component,  $filearea, $file_itemid);
            }
            return $data_record;
        }
        return false;
    }

    // ----- END -----
}
