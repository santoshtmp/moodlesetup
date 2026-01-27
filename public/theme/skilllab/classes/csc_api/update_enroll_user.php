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

namespace theme_skilllab\csc_api;

use theme_skilllab\local\skl_time_track;
use theme_skilllab\util\UtilNotification_handler;

require_once($CFG->dirroot . '/theme/skilllab/inc/locallib/_include.php');

class update_enroll_user
{

    /**
     * to set user course related data 
     * like 
     *  1) progress percentage
     *  2) course duration
     * api/moodle/course/update-enroll-user
     */
    /**
     * 
     */
    public static function api_update_enroll_user($post_data)
    {
        // 
        $api_key = theme_skilllab_get_setting('api_key');
        $site_environment = get_config('theme_skilllab', 'site_environment');
        // $site_environment = theme_skilllab_get_setting('site_environment'); 
        // site_environment (0,1,2)=(staging, live, local)
        if ($site_environment == 2) {
            return true;
        }
        $stage =  ($site_environment == 0) ? 'stage.' : '';
        $api_url = 'https://api.' . $stage . 'careerservicelab.com/api/moodle/course/update-enroll-user';

        // Initialize curl
        $ch = curl_init();
        // Set the curl options
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'api-key:' . $api_key,
            'Accept:application/json',
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Send the POST request
        $response = curl_exec($ch);
        // // Get the HTTP response code
        // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // // Check for errors in the cURL request.
        // $ch_error = curl_error($ch);
        // Close the cURL session
        curl_close($ch);

        $response = json_decode($response, true);
        if ($response['success']) {
            return $response['success'];
        } else {
            return false;
        }
    }
}
