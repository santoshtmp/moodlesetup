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
 * report information.
 * @package   theme_skilllab   
 * @copyright 2025 skilllab
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\util;

use html_writer;
use moodle_url;
use stdClass;

/**
 * UtilReport_handler class utility class
 *
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class UtilReport_handler {

    public static function get_report_page_title($type) {
        if ($type == 'course') {
            $page_title = 'Course List Report';
        } elseif ($type == 'user') {
            $page_title = 'User List Report';
        } elseif ($type == 'courserating') {
            $page_title = 'Course Rating Report';
        } elseif ($type == 'timetrack') {
            $page_title = 'User Course Time Track Report';
        } else {
            $page_title = 'Report and Analysis';
        }
        return $page_title;
    }

    /**
     * 
     */
    public static function get_report_list() {
        $type = optional_param('type', '', PARAM_TEXT);
        $contents = '';
        $contents .= html_writer::start_tag(
            'div',
            ['class' => 'skilllab-report-list mb-3']
        );
        $contents .= html_writer::start_tag(
            'div',
            ['class' => 'list-wrapper d-flex gap-3', 'style' => 'gap:10px']
        );
        $contents .= html_writer::link(
            new moodle_url('/theme/skilllab/pages/report/index.php'),
            'Course Student Report',
            // ['class' => 'btn btn-primary']
        );
        $contents .= html_writer::link(
            new moodle_url('/theme/skilllab/pages/report/type.php', ['type' => 'course']),
            self::get_report_page_title('course'),
            ['class' => ($type == 'course') ? 'active btn btn-primary' : 'btn btn-secondary']
        );
        $contents .= html_writer::link(
            new moodle_url('/theme/skilllab/pages/report/type.php', ['type' => 'user']),
            self::get_report_page_title('user'),
            ['class' => ($type == 'user') ? 'active btn btn-primary' : 'btn btn-secondary']
        );


        $contents .= html_writer::end_tag('div');
        $contents .= html_writer::end_tag('div');

        return $contents;
    }

    /**
     * 
     */
    public static function  get_search_form_content($action_url, $hidden_values = []) {
        $search = optional_param('search', '', PARAM_TEXT);
        // $hidden_values = is_array($hidden_values) ? $hidden_values : [];
        $hidden_values[] = ['name' => 'sesskey', 'value' => sesskey()];
        $search_form = [
            'action' => $action_url->out(),
            'hidden_values' => $hidden_values
        ];

        $search_form['search'] = [
            'inputname' => 'search',
            'query' => $search,
            'searchstring' => 'Search',
        ];

        return $search_form;
    }




    /**
     * @param array $chart_type = ["pie","bar"]
     * @param array $chart_data 
     */
    public static function getChart($chart_type, $chart_data) {
        global $OUTPUT;
        if (!is_array($chart_type) || !is_array($chart_data)) {
            return false;
        }


        foreach ($chart_data as $title => $value) {
            $all_course_enrolment_num_only_series = new \core\chart_series($title, $value);
        }

        // $allcourses = [];//new AllCourseDetails();
        $allCourseReportData = new stdClass(); //$allcourses->getAllCourseReportData();

        $all_course_name_only = $allCourseReportData->all_course_name_only;
        $all_course_enrolment_num_only = $allCourseReportData->all_course_enrolment_num_only;
        $all_course_active_num_only = $allCourseReportData->all_course_active_num_only;
        $all_course_completion_num_only = $allCourseReportData->all_course_completion_num_only;

        $all_course_enrolment_num_only_series = new \core\chart_series('Enrolled Users', $all_course_enrolment_num_only);
        $all_course_active_num_only_series = new \core\chart_series('In Progress Users', $all_course_active_num_only);
        $all_course_completion_num_only_series = new \core\chart_series('Course Completion Users', $all_course_completion_num_only);
        $all_course_name_only_labels = $all_course_name_only;

        $chart_1 = new \core\chart_pie();
        $chart_1->set_doughnut(true);
        // $chart_1->set_title('Course Enrol Users Pie Chart');
        $chart_1->add_series($all_course_enrolment_num_only_series);
        $chart_1->set_labels($all_course_name_only_labels);

        // $chart_2 = new core\chart_bar();
        // $chart_2->set_stacked(true);
        // $chart_2->set_horizontal(true);
        $chart_2 = new \core\chart_line();
        $chart_2->set_smooth(true);
        // $chart_2->set_title('Course Users Chart');
        $chart_2->add_series($all_course_enrolment_num_only_series);
        $chart_2->add_series($all_course_active_num_only_series);
        $chart_2->add_series($all_course_completion_num_only_series);
        $chart_2->set_labels($all_course_name_only_labels);

        $all_chart = new stdClass();
        $all_chart->chart_1 = $OUTPUT->render($chart_1);
        $all_chart->chart_2 = $OUTPUT->render($chart_2);

        return $all_chart;
    }
}
