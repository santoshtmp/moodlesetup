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

// namespace format_topics\output;
namespace theme_skilllab\output;

use \core_courseformat\output\section_renderer;
use html_writer;
use moodle_page;
use renderer_base;
use section_info;
use stdClass;

/**
 * Basic renderer for topics format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_topics_renderer extends \format_topics\output\renderer
{
    public function render_content()
    {
        global $COURSE, $PAGE, $CFG;
        $format = course_get_format($COURSE);
        $renderer = $PAGE->get_renderer('format_topics');
        $outputclass = $format->get_output_classname('content');
        $format_topics_output = new $outputclass($format);
        // $format_topics_output = new \format_topics\output\courseformat\content($format);
        $template_context =  $format_topics_output->export_for_template($renderer);
        // Add add_topic link at the top
        $add_topic = '';
        if ($this->page->user_is_editing()) {
            $add_topic =  '
                <a href="' . $CFG->wwwroot . '/course/changenumsections.php?courseid=' . $COURSE->id . '&amp;insertsection=0&amp;sesskey=' . sesskey() . '" class="add-sections" data-add-sections="Add topic" data-action="addSection" data-id="' . $template_context->initialsection->id . '" data-locked="false" aria-busy="false">
                Add topic
                </a>
            ';
        }
        // to remove general or initial section i.e #section-0
        $template_context->initialsection = '';


        return $add_topic . $this->render_from_template('core_courseformat/local/content', $template_context);
    }
}
