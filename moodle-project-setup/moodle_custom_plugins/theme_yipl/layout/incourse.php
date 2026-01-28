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
 * "incourse" based layout for the theme_yipl
 *
 * @package    theme_yipl
 * @copyright  2025 YIPL
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_yipl\handler\timetrack_handler;

defined('MOODLE_INTERNAL') || die();

$cm_id = isset($this->page->cm->id) ? $this->page->cm->id : '';
timetrack_handler::init_start_timetrack_course_activity($cm_id, $COURSE->id, $USER->id);

$templatecontext = \theme_yipl\handler\layout_handler::main();
echo $OUTPUT->render_from_template('theme_yipl/layout/incourse', $templatecontext);
