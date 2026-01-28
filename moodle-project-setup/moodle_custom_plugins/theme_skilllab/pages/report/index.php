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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

use theme_skilllab\util\UtilReport_handler;
use theme_skilllab\util\UtilUser_handler;

require_once(dirname(__FILE__) . '/../../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/theme/skilllab/inc/locallib/_include.php');

// Get system context.
$context = \context_system::instance();

// Prepare the page information.
$page_url = new moodle_url('/theme/skilllab/pages/report/index.php');
$page_title = 'Student Course Report Page';
$PAGE->set_context($context);
$PAGE->set_url($page_url);
$PAGE->set_pagelayout('admin'); // admin , standard , ...
$PAGE->set_pagetype('student_course_report');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
// $PAGE->navbar->add($page_title);
$PAGE->set_blocks_editing_capability('moodle/site:manageblocks');
// Adds a CSS class to the body tag 
$strcssclass = 'student_course_report';
$PAGE->add_body_class($strcssclass);
$PAGE->requires->jquery();

//
require_login();
if (!has_capability('moodle/site:config', $context)) {
    $contents = "You don't have permission to access this pages";
    $contents .= "<br>";
    $contents .= "<a href='/'> Return Back</a>";
} else {
    global $OUTPUT;
    // $page_url = $CFG->wwwroot . $_SERVER['REQUEST_URI'];
    $records =  UtilUser_handler::get_student_course_report();
    ob_start();
?>
    <style>
        #search-filter-form .input-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        #search-filter-form .form-control {
            max-width: 200px;
        }
    </style>
    <form action="" class="mform " id="search-filter-form">
        <div class="input-fields">
            <input type="text" id="search_moodle_user_id"
                class="form-control"
                placeholder="Search By Moodle User Id"
                aria-label="Search" name="search_moodle_user_id"
                data-region="input" autocomplete="off"
                value="<?php echo $records['search_moodle_user_id']; ?>">
            <input type="text" id="search_user"
                class="form-control"
                placeholder="Search By User name"
                aria-label="Search" name="search_user"
                data-region="input" autocomplete="off"
                value="<?php echo $records['search_user']; ?>">
            <input type="text" id="search_course"
                class="form-control"
                placeholder="Search By course name"
                aria-label="Search" name="search_course"
                data-region="input" autocomplete="off"
                value="<?php echo $records['search_course']; ?>">
        </div>
        <div class="action-btn mt-3">
            <input type="submit" value="Submit">
            <?php
            if ($records['search_course'] || $records['search_moodle_user_id'] || $records['search_user']) {
            ?>
                <a class="btn btn-secondary" href="<?php echo $url; ?>">Reset</a>
            <?php
            }
            ?>
            <!-- <a class="btn btn-primary" href="?download=1">Download</a> -->
        </div>
    </form>
    <div class="no-overflow">
        <table id="student_course_report" class="flexible generaltable generalbox skl-courses-table">
            <thead>
                <tr>
                    <?php
                    foreach ($records['records_final'][0] as $key => $header) {
                        echo "<th>" . $header . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($records['records_final'] as $key => $record) {
                    if ($key > 0) {
                        echo "<tr>";
                        echo "<td>" . $record['sn'] . "</td>";
                        echo "<td>" . $record['secret_user_id'] . "</td>";
                        echo "<td><a href=" . $record['user_profile_url'] . ">" . $record['user_name'] . "</a></td>";
                        echo "<td>" . $record['institution'] . "</td>";
                        echo "<td><a href=" . $record['course_url'] . ">" . $record['course_fullname'] . "</a></td>";
                        echo "<td>" . $record['course_type'] . "</td>";
                        echo "<td>" . $record['course_progress'] . "</td>";
                        echo "<td><a href=" . $record['time_track_detail_url'] . ">" . $record['duration_time'] . "</a></td>";
                        echo "</tr>";
                    }
                }

                ?>
            </tbody>
        </table>
        <?php
        echo $OUTPUT->paging_bar($records['records_count'], $records['page_number'], $records['per_page_data'], $page_url);
        ?>
    </div>
<?php

    $output_data = ob_get_contents();
    ob_end_clean();
    $contents = $output_data;
}

/**
 * ========================================================
 * -------------------  Output Content  -------------------
 * ========================================================
 */
echo $OUTPUT->header();
echo UtilReport_handler::get_report_list();

echo $contents;
echo $OUTPUT->footer();
