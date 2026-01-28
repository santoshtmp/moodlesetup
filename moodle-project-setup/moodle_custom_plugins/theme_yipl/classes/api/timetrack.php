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
 * courses_list API endpoints
 * 
 * @package   theme_yipl   
 * @copyright 2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * 
 */

namespace theme_yipl\api;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use theme_yipl\handler\timetrack_handler;

class timetrack extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function timetrack_parameters()
    {
        return new external_function_parameters([
            'user_id' => new external_value(PARAM_INT, 'user_id', VALUE_REQUIRED),
            'course_id' => new external_value(PARAM_INT, 'The course_id', VALUE_REQUIRED),
            'cmod_id' => new external_value(PARAM_INT, 'The cmod_id', VALUE_REQUIRED),
            'option' => new external_value(PARAM_INT, 'timetrack 0=start, 1=set, 2=reset', VALUE_REQUIRED),
        ]);
    }

    /**
     *
     * @param array $options It contains an array (list of ids)
     * @return array
     */
    public static function timetrack($user_id, $course_id, $cmod_id, $option)
    {

        $params = self::validate_parameters(self::timetrack_parameters(), [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'cmod_id' => $cmod_id,
            'option' => $option
        ]);

        $user_id = (int)$params['user_id'];
        $course_id = (int)$params['course_id'];
        $cmod_id = (int)$params['cmod_id'];
        $option = (int)$params['option']; // timetrack option ::  0=start, 1=set, 2=reset

        try {
            global $SESSION;
            if ($option == '0') {
                $status = timetrack_handler::start_time_track_session_process($course_id, $cmod_id, $user_id);
                return [
                    'status' => ($status) ? true : false,
                    'message' => 'start time track',
                    'exec' => "OK"
                ];
            } elseif ($option == '1') {
                $status = timetrack_handler::set_time_track();
                $duration =  ($status) ? $status  : 0;
                $message = 'users time duration is ' . $duration . ' seconds ; session for yipl_timetrack = ' . json_encode($SESSION->yipl_timetrack);
                return [
                    'status' => ($status) ? true : false,
                    'message' => $message,
                    'exec' => "OK"
                ];
            } elseif ($option == '2') {
                $SESSION->yipl_timetrack['start_time'] = time();
                return [
                    'status' =>  true,
                    'message' => 'reset time track start time',
                    'exec' => "OK"
                ];
            } else {
                return [
                    'status' =>  false,
                    'message' => 'something went wrong; check option param value',
                    'exec' => "OK"
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'failed to process',
                'exec' => $e->getMessage()
            ];
        }
    }


    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 2.2
     */
    public static function timetrack_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL),
            'message' => new external_value(PARAM_RAW),
            'exec' => new external_value(PARAM_RAW),
        ]);
    }
}
