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
 * Course renderer.
 *
 * @package    theme_skilllab
 * @copyright  2022 Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\output\core;

use completion_info;
use core_course_category;
use html_writer;
use coursecat_helper;
use stdClass;
use core_course_list_element;
use lang_string;
use moodle_url;
use theme_config;

defined('MOODLE_INTERNAL') || die();

// require_once($CFG->dirroot . "/course/renderer.php");

class course_renderer extends \core_course_renderer
{

    /**
     * Displays courses name only.
     */
    public function course_name_only($chelper, $course)
    {
        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(
            new moodle_url('/course/view.php', ['id' => $course->id]),
            $coursename,
            ['class' => $course->visible ? 'aalink' : 'aalink dimmed']
        );
        return html_writer::tag('div', $coursenamelink, ['class' => 'course-name']);
    }

    /**
     * Displays courses category name only.
     */
    public function course_cat_name_only($course)
    {
        $content = '';
        if ($cat = core_course_category::get($course->category, IGNORE_MISSING)) {
            $content .= html_writer::start_tag('div', ['class' => 'course-category']);
            // $content .= html_writer::tag('span', get_string('category') . ': ', ['class' => 'font-weight-bold']);
            $content .= html_writer::link(
                new moodle_url('/course/index.php', ['categoryid' => $cat->id]),
                $cat->get_formatted_name(),
                ['class' => $cat->visible ? '' : 'dimmed']
            );
            $content .= html_writer::end_tag('div');
        }
        return $content;
    }

    /**
     * Renders part of frontpage with a skip link (i.e. "My courses", "Site news", etc.)
     *
     * @param string $skipdivid
     * @param string $contentsdivid
     * @param string $header Header of the part
     * @param string $contents Contents of the part
     * @return string
     */
    protected function frontpage_part($skipdivid, $contentsdivid, $header, $contents)
    {
        return '';
    }

    /**
     * Renders HTML to display particular course category - list of it's subcategories and courses
     *
     * Invoked from /course/index.php
     *
     * @param int|stdClass|core_course_category $category
     */
    public function course_category($category)
    {
        $skl_course_list_table = new \theme_skilllab\util\course_list();
        $courses_table_html = $skl_course_list_table->skl_course_list_out();
        return  $courses_table_html;
    }


}
