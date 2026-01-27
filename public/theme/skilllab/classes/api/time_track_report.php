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
 * time_track_report API endpoints
 *
 * @package    theme_skilllab
 * @copyright  2022 Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\api;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

class time_track_report extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function time_track_report_parameters()
    {
        return new external_function_parameters([
            'table' => new external_value(PARAM_INT, 'The duration', VALUE_OPTIONAL),
            'course_id' => new external_value(PARAM_INT, 'The course_id', VALUE_OPTIONAL),
            'user_id' => new external_value(PARAM_INT, 'The user_id', VALUE_OPTIONAL)
        ]);
    }

    /**
     * Get courses
     *
     * @param array $options It contains an array (list of ids)
     * @return array
     * @since Moodle 2.2
     */
    public static function time_track_report($table = false, $course_id = 0, $user_id = 0)
    {

        $params = self::validate_parameters(self::time_track_report_parameters(), [
            'table' => $table,
            'course_id' => $course_id,
            'user_id' => $user_id
        ]);
        $table = $params['table'];
        $course_id = $params['course_id'];
        $user_id = $params['user_id'];

        try {
            if ($table) {
                $skl_time_track_report = new \theme_skilllab\util\time_track_report();
                $output_table = $skl_time_track_report->output($course_id);
                return ['status' => true, 'template_html' => $output_table];
            }
            return ['status' => true, 'template_html' => "only data - no table"];
        } catch (\Exception $e) {
            return ['status' => false, 'exception' => $e->getMessage()];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 2.2
     */
    public static function time_track_report_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL),
            'template_html' => new external_value(PARAM_RAW, 'template html', VALUE_OPTIONAL),
            'exception' => new external_value(PARAM_RAW, 'exception message', VALUE_OPTIONAL),

        ]);
    }
}
