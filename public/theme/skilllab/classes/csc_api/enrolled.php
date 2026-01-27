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

require_once($CFG->dirroot . '/theme/skilllab/inc/locallib/_include.php');

class enrolled
{
    /**
     * api/moodle/course/enroll-user
     */
    public function set_enroll_user($course_id, $user_id)
    {

        $moodle_user_id = encryptValue($user_id);

        // 
        $api_key = theme_skilllab_get_setting('api_key');
        $site_environment = theme_skilllab_get_setting('site_environment'); // site_environment (0,1,2)=(staging, live, local)
        if ($site_environment == 2) {
            return true;
        }
        $stage =  ($site_environment == 0) ? 'stage.' : '';
        $api_url = 'https://api.' . $stage . 'careerservicelab.com/api/moodle/course/enroll-user';

        $post_data = [
            'course_id' => $course_id,
            'moodle_user_id' => $moodle_user_id
        ];

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
        $response = json_decode($response, true);
        if ($response['success']) {
            return $response['success'];
        } else {
            return false;
        }
    }

    /**
     * api to set delete enrolled user in csc from moodle
     * api/moodle/remove-enrolled
     */
    public function set_enrolled_user_delete($course_id, $user_id)
    {
        $api_key = theme_skilllab_get_setting('api_key');
        $site_environment = theme_skilllab_get_setting('site_environment');
        if ($site_environment == 2) {
            return true;
        }
        $stage =  ($site_environment == 0) ? 'stage.' : '';
        $api_url = 'https://api.' . $stage . 'careerservicelab.com/api/moodle/remove-enrolled';
        $post_data = [
            'course_id' => $course_id,
            'moodle_user_id' => encryptValue($user_id)
        ];

        // Initialize curl
        $ch = curl_init();
        // Set the curl options
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'api-key:' . $api_key,
            'Accept:application/json',
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Send the POST request
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        if ($response['success']) {
            return $response['success'];
        } else {
            return false;
        }
    }
}
