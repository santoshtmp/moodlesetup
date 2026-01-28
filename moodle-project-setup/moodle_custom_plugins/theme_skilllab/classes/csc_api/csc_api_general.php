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

class csc_api_general
{
    /**
     * get institutions list
     * api/public/institutions
     */
    public static function get_institutions_list()
    {
        $api_key = theme_skilllab_get_setting('api_key');
        $site_environment = theme_skilllab_get_setting('site_environment');
        if ($site_environment == 2) {
            return true;
        }
        $stage = ($site_environment == 0) ? 'stage.' : '';
        // get csc institutions data
        $api_url = 'https://api.' . $stage . 'careerservicelab.com/api/public/institutions';
        // Initialize curl
        $ch = curl_init();
        // Set the curl options
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'api-key:' . $api_key,
            'Accept:application/json',
            'Content-Type: application/json'
        ));
        // Send the request
        $response = curl_exec($ch);
        return $response;
    }

    /**
     * csc logout api
     * api/auth/moodle-logout
     */
    public static function logout_csc()
    {
        global $USER;
        $api_key = theme_skilllab_get_setting('api_key');
        $site_environment = theme_skilllab_get_setting('site_environment');
        if ($site_environment == 2) {
            return true;
        }
        $stage = ($site_environment == 0) ? 'stage.' : '';
        $api_url = 'https://api.' . $stage . 'careerservicelab.com/api/auth/moodle-logout';
        $post_data = [
            'moodle_user_id' => encryptValue($USER->id)
        ];
        // Initialize curl
        $ch = curl_init();
        // Set the curl options
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'api-key:' . $api_key,
            'Accept:application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Send the POST request
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        if ($response['success']) {
            return  true;
        }
        if (is_siteadmin($USER) || ($response['message'] == 'User Not Found')) {
            return  true;
        }
        return  false;
    }
}
