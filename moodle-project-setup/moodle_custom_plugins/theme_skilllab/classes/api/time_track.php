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
 * time_track API endpoints
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
use theme_skilllab\local\skl_time_track;

class time_track extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function time_track_parameters()
    {
        return new external_function_parameters([
            'user_id' => new external_value(PARAM_INT, 'user_id', VALUE_REQUIRED),
            'course_id' => new external_value(PARAM_INT, 'The course_id', VALUE_REQUIRED),
            'cmod_id' => new external_value(PARAM_INT, 'The cmod_id', VALUE_REQUIRED),
        ]);
    }

    /**
     *
     * @param array $options It contains an array (list of ids)
     * @return array
     */
    public static function time_track($user_id, $course_id, $cmod_id)
    {

        $params = self::validate_parameters(self::time_track_parameters(), [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'cmod_id' => $cmod_id
        ]);

        $user_id = (int)$params['user_id'];
        $course_id = (int)$params['course_id'];
        $cmod_id = (int)$params['cmod_id'];

        try {
            $status = skl_time_track::set_time_track();
            global $SESSION;
            $duration =  ($status) ? $status  : 0;
            $message = 'users time duration is ' . $duration . ' seconds ; session for skl_time_track = ' . json_encode($SESSION->skl_time_track);
            return [
                'status' => ($status) ? true : false,
                'message' => $message,
                'exec' => "OK"
            ];
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
    public static function time_track_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL),
            'message' => new external_value(PARAM_RAW),
            'exec' => new external_value(PARAM_RAW),
        ]);
    }

    /***
     * ==================================================================
     */

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function reset_start_time_parameters()
    {
        return new external_function_parameters([
            'user_id' => new external_value(PARAM_INT, 'user_id', VALUE_REQUIRED),
            'course_id' => new external_value(PARAM_INT, 'The course_id', VALUE_REQUIRED),
            'cmod_id' => new external_value(PARAM_INT, 'The cmod_id', VALUE_REQUIRED),
        ]);
    }

    /**
     * Get courses
     *
     * @param array $options It contains an array (list of ids)
     * @return array
     */
    public static function reset_start_time($course_id, $cmod_id, $user_id)
    {
        $params = self::validate_parameters(self::time_track_parameters(), [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'cmod_id' => $cmod_id
        ]);

        $user_id = (int)$params['user_id'];
        $course_id = (int)$params['course_id'];
        $cmod_id = (int)$params['cmod_id'];

        try {
            global $SESSION;
            $SESSION->skl_time_track['start_time'] = time();
            return [
                'status' =>  true,
                'message' => 'reset_start_time',
                'exec' => "OK"
            ];
        } catch (\Throwable $th) {
            return [
                'status' =>  false,
                'message' => 'reset_start_time',
                'exec' => "OK"
            ];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 2.2
     */
    public static function reset_start_time_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL),
            'message' => new external_value(PARAM_RAW),
            'exec' => new external_value(PARAM_RAW),
        ]);
    }
}

/**
 * if we creade custom api page
 */
// define('AJAX_SCRIPT', true);
// define('REQUIRE_CORRECT_ACCESS', true);
// // only allow post method with duration 
// if (!isset($_POST['duration'])) {
//     // @header('Location: /');
//     die;
// }
// require_once(dirname(__FILE__) . '/../../../../config.php');
// // // Allow CORS requests.
// header('Access-Control-Allow-Origin: *');
// header('Content-Type: application/json');
// header('Acess-Control-Allow-Method: POST');
// header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-request-with');
// error_reporting(0);
// // $duration = required_param('duration', PARAM_INT);
// // $duration = (isset($_POST['duration'])) ? $_POST['duration'] : 0; 
// $duration = optional_param('duration', 0, PARAM_INT);
// $PAGE->set_url('/theme/skilllab/pages/course/time_track.php');
// // 
// $skl_time_track = new \theme_skilllab\local\skl_time_track();
// $status = $skl_time_track->set_time_track($duration);
// // 
// $result = new stdClass;
// $result->status = ($status) ? true : false;
// $result->duration = $duration;
// // 
// echo json_encode($result);