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

class course
{
    /**
     * api to set create and update course in csc from moodle
     * api/moodle/course/upsert
     */
    protected function csc_api_moodle_course_upsert($course_info)
    {
        global $USER;

        $api_key = theme_skilllab_get_setting('api_key');
        $site_environment = theme_skilllab_get_setting('site_environment');
        if ($site_environment == 2) {
            return true;
        }
        $stage =  ($site_environment == 0) ? 'stage.' : '';
        $api_url = 'https://api.' . $stage . 'careerservicelab.com/api/moodle/course/upsert';
        $post_data = [];
        $post_data['course'] = $course_info;
        // get_course_info($course_id, $during_create);

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

        if ($site_environment == 2) {
            return true;
        }

        // skilllab_sendmessage($USER, "API hit", " csc_api_moodle_course_upsert <br> API data: <br> ------------------- <br> " . json_encode($course_info));
        return $response['success'];
    }

    protected function format_api_course_info($get_course_info)
    {

        $course_info = [];
        if (isset($get_course_info['created_by'])) {
            $course_info['created_by'] =  $get_course_info['created_by'];
        }
        $course_info['id'] = $get_course_info['id'];
        $course_info['name'] = $get_course_info['fullname'];
        $course_info['short_name'] = $get_course_info['shortname'];
        $course_info['course_id_number'] = null;
        $course_info['description'] = $get_course_info['summary'];
        $course_info['category'] = $get_course_info['category_name'];
        $course_info['level'] = $get_course_info['skill_level'];
        $course_info['duration'] = $get_course_info['course_duration'];
        $course_info['chapters_count'] = $get_course_info['chapter_topics'];
        $course_info['users_enrolled'] = $get_course_info['count_enrolled_users'];
        $course_info['course_url'] = $get_course_info['course_url'];
        $course_info['moodle_course_id'] = $get_course_info['id'];
        $course_info['course_img'] = $get_course_info['course_img_url'];
        $course_info['type'] = $get_course_info['course_type'];

        return $course_info;
    }

    /**
     * 
     */
    public function set_course_create_update($course_id, $during_create = false)
    {
        $get_course_info = get_course_info($course_id, $during_create);
        $course_info = $this->format_api_course_info($get_course_info);
        return $this->csc_api_moodle_course_upsert($course_info);
    }

    /**
     * 
     */
    public function set_course_topics($course_id)
    {
        $id = optional_param('id', 0, PARAM_INT);
        $customfield_course_type = optional_param('customfield_course_type', 0, PARAM_INT);
        $during_create = false;
        if ($customfield_course_type) {
            if (!$id) {
                $during_create = true;
            }
        }

        $get_course_info = get_course_info($course_id, $during_create);
        $course_info = $this->format_api_course_info($get_course_info);
        return $this->csc_api_moodle_course_upsert($course_info);
    }



    /**
     * api to set course course in csc from moodle
     * api/moodle/course/{course_id}'
     */
    public function set_course_delete($course_id)
    {
        $course_id = (int)$course_id;
        $api_key = theme_skilllab_get_setting('api_key');
        $site_environment = theme_skilllab_get_setting('site_environment');
        if ($site_environment == 2) {
            return true;
        }
        $stage =  ($site_environment == 0) ? 'stage.' : '';
        $api_url = 'https://api.' . $stage . 'careerservicelab.com/api/moodle/course/' . $course_id;

        // Initialize curl
        $ch = curl_init();
        // Set the curl options
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'api-key:' . $api_key,
            'Accept:application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Send the POST request
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        if ($response['success']) {
            global $USER;
            // skilllab_sendmessage($USER, "API hit", " csc_api_moodle_course_id <br> API data: <br> ------------------- <br> delete course id: " . $course_id);
            return $response['success'];
        } else {
            return false;
        }
    }
}
