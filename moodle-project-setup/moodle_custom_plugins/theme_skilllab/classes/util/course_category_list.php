<?php

namespace theme_skilllab\util;

use html_writer;
use moodle_url;

class course_category_list
{

    protected $categoryid;
    protected $search_category;
    protected $per_page = 5;
    protected $page_number;

    // capability variables
    protected $site_admin;
    protected $course_create;
    protected $instructor_view;

    /**
     * Class constructor
     */
    public function __construct()
    {
        global $USER;

        $this->page_number = optional_param('page', 0, PARAM_INT); // page number
        $this->search_category = optional_param('scategory', '', PARAM_RAW); // search category
        $this->categoryid = optional_param('categoryid', 0, PARAM_INT); // Category id

        // get systaem level users capability
        $systemcontext = \context_system::instance();
        $this->site_admin = has_capability('moodle/site:config', $systemcontext, $USER->id);
        $this->course_create = has_capability('moodle/course:create', $systemcontext, $USER->id);
        $this->instructor_view = has_capability('theme/skilllab:instructor_view', $systemcontext, $USER->id);
        // $only_student = has_capability('moodle/course:isincompletionreports', $context_course, $USER->id);

        $category_list_per_page = theme_skilllab_get_setting('category_list_per_page');
        $this->per_page = ($category_list_per_page > 0) ? $category_list_per_page : $this->per_page;
    }


    /**
     * define table columns header
     */
    protected function define_table_headers()
    {
        // define common table header values
        $table_columns = ['id', 'shortname'];
        $table_headers = ['S.N', 'Category Name'];
        // // define course creator or admin table header values
        if ($this->course_create) {
            $table_columns = array_merge($table_columns, ['action']);
            $table_headers = array_merge($table_headers, ['Action']);
        }
        //  else {
        //     // define student or non-instructor_view table header values
        //     if (!$this->instructor_view) {
        //         $table_columns = array_merge($table_columns, ['enrolled_date', 'progress_status', 'action']);
        //         $table_headers = array_merge($table_headers, ['Enrolled Date', 'Status', 'Action']);
        //     }
        // }

        // define which table columns to sort
        $sort_columns = ['shortname'];

        return array($table_headers, $table_columns, $sort_columns);
    }

    protected function skl_get_course_categories($limitfrom = 0, $limitnum = 0)
    {
        global $DB;
        $course_categories = $DB->get_records('course_categories', [], 'id DESC', '*', $limitfrom, $limitnum);
        return $course_categories;
    }


    /**
     * course filter section
     * return output template html 
     */
    protected function skl_category_list_filter()
    {
        global $OUTPUT, $DB, $CFG;

        $course_categories = $DB->get_records('course_categories', []);

        // data arrange for template content
        $template_content = [];
        $template_content['categoryid'] = ($this->categoryid) ? $this->categoryid : 0;
        $i = 0;
        foreach ($course_categories as $key => $category) {
            $template_content['categories'][$i]['id'] = $category->id;
            $template_content['categories'][$i]['name'] = $category->name;
            $template_content['categories'][$i]['category_filter'] = '/course/index.php?categoryid=' . $category->id;

            $template_content['categories'][$i]['selected'] = ($this->search_category == $category->id) ? 'selected' : '';
            $i++;
        }
        if ($this->site_admin or $this->course_create) {
            $template_content['course_create'] = true;
        }

        // if ($this->categoryid || $this->search_course || $this->search_skill || $this->created_date_from || $this->created_date_to) {
        //     $template_content['Clear_filter'] = true;
        // }
        $template_content['action_url'] = $CFG->wwwroot . '/theme/skilllab/category/index.php';

        $course_filter_section = $OUTPUT->render_from_template('theme_skilllab/skilllab_courses/category_filter_section', $template_content);

        return $course_filter_section;
    }


    public function skl_category_list_table()
    {

        global $OUTPUT, $CFG;

        $page_url = $CFG->wwwroot . $_SERVER['REQUEST_URI'];


        // get define table headers and columns
        list($table_headers, $table_columns, $sort_columns) =  $this->define_table_headers();

        // define for pagination and get per page data
        $per_page_data = $this->per_page;
        $limitfrom = 0;
        $limitnum = $per_page_data;
        if ($this->page_number > 0) {
            $limitfrom = $limitnum * $this->page_number;
        }

        $all_records = $this->skl_get_course_categories(0, 0);
        $records = $this->skl_get_course_categories($limitfrom, $limitnum);

        if ($records) {
            $i = $limitfrom + 1;
            foreach ($records as $record) {

                $table_row = [
                    $i,
                    [
                        'name' => $record->name,
                        'url' => new moodle_url('/course/index.php', ['categoryid' => $record->id])
                    ],
                    [
                        'id' => $record->id,
                        'sesskey' => sessKey()
                    ],
                ];

                foreach ($table_columns as $key => $value) {
                    $each_table_row[$value] = $table_row[$key];
                }

                $table_datas[] = $each_table_row;
                $i =  $i + 1;
            }
        } else {
            $no_data = 'no data';
        }

        foreach ($table_columns as $key => $value) {
            $header[$key]['name'] =  $table_headers[$key];
        }

        $template_context = [
            'table_headers' => $header,
            'table_datas' => $table_datas,
            'course_create' => ($this->course_create) ? true : false,
            'instructor_view' => ($this->instructor_view) ? true : false,
            'no_data' => (isset($no_data)) ? $no_data : ''
        ];
        // get output content from template
        $output = '';
        $output .= $OUTPUT->render_from_template('theme_skilllab/skilllab_courses/category_list_table', $template_context);
        $output .= $OUTPUT->paging_bar(count($all_records), $this->page_number, $perpage = $limitnum, $page_url);

        return  $output;
    }

    public function output_html()
    {

        $output = '';
        $output .= html_writer::start_tag('div', array('class' => 'category-list-content', 'id' => 'skl-category-list'));
        $output .= $this->skl_category_list_filter();
        $output .= html_writer::start_tag('div', array('class' => 'category-list', 'id' => 'category-list-table'));
        $output .= $this->skl_category_list_table();
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        return  $output;
    }
}
