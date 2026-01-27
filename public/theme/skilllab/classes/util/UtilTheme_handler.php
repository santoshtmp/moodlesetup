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
 * Theme helper to load a theme configuration.
 * @package   theme_skilllab   
 * @copyright 2023 yipl skill lab csc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\util;

use moodle_url;
use theme_config;

defined('MOODLE_INTERNAL') || die;

class UtilTheme_handler {

    /**
     * security_header
     */
    public static function security_header() {
        // security header
        @header('X-Frame-Options: SAMEORIGIN');
        @header('Referrer-Policy: strict-origin-when-cross-origin');
        @header('X-Content-Type-Options: nosniff');
        @header('X-XSS-Protection: 1; mode=block');
        @header("Content-Security-Policy: frame-ancestors 'self';");
        @header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    /**
     * encrypt or decrypt the given value
     * @param string $value
     * @param string $type = encrypt / decrypt
     * @return string $output_value
     * https://www.php.net/manual/en/function.openssl-encrypt.
     * https://www.php.net/manual/en/function.openssl-decrypt.php
     * https://www.geeksforgeeks.org/how-to-encrypt-and-decrypt-a-php-string/
     * 
     */
    public static function encrypt_decrypt_value($value, $type = "decrypt") {

        $theme = theme_config::load('skilllab');
        $encryptionAlgorithm = $theme->settings->encryptionAlgorithm;
        $openssl_decrypt_key = $theme->settings->openssl_decrypt_key;
        $openssl_iv_key = $theme->settings->openssl_iv_key;

        if ($type == "decrypt") {
            $output_value = openssl_decrypt($value, $encryptionAlgorithm, $openssl_decrypt_key, 0, $openssl_iv_key);
        } elseif ($type == "encrypt") {
            $output_value =  openssl_encrypt($value, $encryptionAlgorithm, $openssl_decrypt_key, 0, $openssl_iv_key);
        } else {
            $output_value = '';
        }

        return $output_value;
    }

    /**
     * 
     */
    public function frontpage_section() {
        $templatecontext = [];
        $templatecontext = array_merge($templatecontext, $this->hero_section());
        $templatecontext = array_merge($templatecontext, $this->testimonial());
        $templatecontext = array_merge($templatecontext, $this->newsletter());

        return $templatecontext;
    }
    public function hero_section() {
        $theme = theme_config::load('skilllab');

        $templatecontext['herosection_status'] = $theme->settings->herosection_status;
        $templatecontext['herosection_title'] = format_text($theme->settings->herosection_title, FORMAT_PLAIN);
        $templatecontext['herosection_desc'] = format_text($theme->settings->herosection_desc, FORMAT_PLAIN);
        $templatecontext['herosection_image_1'] = $theme->setting_file_url('herosection_image_1', 'herosection_image_1');
        $templatecontext['herosection_image_2'] = $theme->setting_file_url('herosection_image_2', 'herosection_image_2');
        $templatecontext['herosection_image_3'] = $theme->setting_file_url('herosection_image_3', 'herosection_image_3');
        $templatecontext['herosection_image_4'] = $theme->setting_file_url('herosection_image_4', 'herosection_image_4');
        $templatecontext['herosection_cat_label'] = format_text($theme->settings->herosection_cat_label, FORMAT_PLAIN);
        $templatecontext['herosection_cat_link'] = format_text($theme->settings->herosection_cat_link, FORMAT_PLAIN);
        $templatecontext['herosection_cat_label_2'] = format_text($theme->settings->herosection_cat_label_2, FORMAT_PLAIN);
        $templatecontext['herosection_video_id'] = format_text($theme->settings->herosection_video_id, FORMAT_PLAIN);

        return $templatecontext;
    }

    public function testimonial() {
        $theme = theme_config::load('skilllab');

        $templatecontext['testimonial_count'] = $theme->settings->testimonial_count;
        $templatecontext['testimonial_heading'] = theme_skilllab_get_setting('testimonial_heading', 'format_text');
        for ($i = 0, $j = 1; $i < $templatecontext['testimonial_count']; $i++, $j++) {
            $name = 'testimonial_name_' . $j;
            $text = 'testimonial_text_' . $j;
            $image = 'testimonial_image_' . $j;
            $templatecontext['testimonial_items'][$i]['i'] = $j;
            $templatecontext['testimonial_items'][$i]['name'] = theme_skilllab_get_setting($name, 'format_text');
            $templatecontext['testimonial_items'][$i]['text'] = theme_skilllab_get_setting($text, 'format_text');
            $templatecontext['testimonial_items'][$i]['image'] =  $theme->setting_file_url($image,  $image);
        }
        return $templatecontext;
    }

    public function newsletter() {
        $theme = theme_config::load('skilllab');

        $templatecontext['newsletter_status'] = $theme->settings->newsletter_status;
        $templatecontext['newsletter_form_action'] = $theme->settings->newsletter_form_action;
        return $templatecontext;
    }

    public function footer() {
        $theme = theme_config::load('skilllab');

        $templatecontext['footer_style_layout'] = $theme->settings->footer_style_layout;
        $templatecontext['footer_desc'] = $theme->settings->footer_desc;

        $templatecontext['firstcolumn_title'] = $theme->settings->firstcolumn_title;
        $templatecontext['firstcolumn_count'] = $theme->settings->firstcolumn_count;
        for ($i = 0, $j = 1; $i < $templatecontext['firstcolumn_count']; $i++, $j++) {
            $label = 'firstcolumn_label_' . $j;
            $link = 'firstcolumn_link_' . $j;
            $templatecontext['firstcolumn'][$i]['label'] = theme_skilllab_get_setting($label, 'format_text');
            $templatecontext['firstcolumn'][$i]['link'] = theme_skilllab_get_setting($link, 'format_text');
        }

        $social_link_settings = [
            'facebook',
            'twitter',
            'linkedin',
            'youtube',
            'instagram',
            'whatsapp',
            'telegram',
            'website',
            'phone_num',
            'mail',
            'location_point'
        ];
        foreach ($social_link_settings as $social_link) {
            $templatecontext[$social_link] = $theme->settings->$social_link;
        }

        $templatecontext['copyright'] = $theme->settings->copyright;
        $templatecontext['other_extra_count'] = $theme->settings->other_extra_count;
        for ($i = 0, $j = 1; $i < $templatecontext['other_extra_count']; $i++, $j++) {
            $label = 'other_extra_label_' . $j;
            $link = 'other_extra_link_' . $j;
            $templatecontext['other_extra'][$i]['label'] = $theme->settings->$label;
            $templatecontext['other_extra'][$i]['link'] = $theme->settings->$link;
        }

        if ($templatecontext['footer_style_layout'] == '1') {
            $templatecontext['newsletter_column_title'] = $theme->settings->newsletter_column_title;
            $templatecontext['footer_subscribe_form_action'] = $theme->settings->footer_subscribe_form_action;
        }

        if ($templatecontext['footer_style_layout'] == '2') {
            $templatecontext['secondcolumn_title'] = $theme->settings->secondcolumn_title;
            $templatecontext['thirdcolumn_title'] = $theme->settings->thirdcolumn_title;
        }

        return $templatecontext;
    }

    /**
     * 
     */
    public static function get_pix_url() {
        global $CFG, $OUTPUT;
        $output_urls = [];
        $theme_pix_path  = $CFG->dirroot . '/theme/skilllab/pix';
        if (file_exists($theme_pix_path)) {
            foreach (new \DirectoryIterator($theme_pix_path) as $file) {
                if ($file->isDot())
                    continue;

                if (!$file->isDir()) {
                    continue;
                }
                $accept_dir = ['icons', 'images'];
                $current_dir = $file->getFilename();
                if (in_array($current_dir, $accept_dir)) {

                    $dir = $theme_pix_path . '/' . $current_dir;
                    $files = scandir($dir);
                    foreach ($files as $filename) {
                        if ($filename === '.' or $filename === '..')
                            continue;

                        $path_info_folder = pathinfo($filename);
                        if (!isset($path_info_folder['extension'])) {
                            continue;
                        }

                        $accept_file = ['svg', "png", "gif", "jpg", "jpeg"];
                        if (in_array($path_info_folder['extension'], $accept_file) && $filename) {
                            $output_urls[$current_dir][$path_info_folder['filename']] = $OUTPUT->image_url($current_dir . '/' . $path_info_folder['filename'], "theme");
                        }
                    }
                }
            }
        }
        return ['theme_pix' => $output_urls];
    }

    /**
     * 
     */
    public static function set_skilllab_css_js() {
        global $PAGE,  $CFG;
        $theme_assets = "/theme/skilllab/assets";
        /**
         * css files
         */
        $skilllab_page_css = [];
        // By page type
        $pagetype = $PAGE->pagetype;
        $filepath = $CFG->dirroot . $theme_assets . '/css/' . $pagetype . '.css';
        if (file_exists($filepath)) {
            $skilllab_page_css[] =  $pagetype;
        }
        // By page layout
        $pagelayout = $PAGE->pagelayout;
        $filepath = $CFG->dirroot . $theme_assets . '/css/' . $pagelayout . '.css';
        if (file_exists($filepath)) {
            $skilllab_page_css[] =  $pagelayout;
        }
        foreach ($skilllab_page_css as $key => $value) {
            $PAGE->requires->css(new moodle_url($CFG->wwwroot . $theme_assets . '/css/' . $value . '.css'));
        }
        $PAGE->requires->css(new moodle_url($CFG->wwwroot . $theme_assets . '/css/skilllab.css'));

        /**
         * js files
         */
        $skilllab_page_js = [];
        // By page type
        $pagetype = $PAGE->pagetype;
        $filepath = $CFG->dirroot . $theme_assets . '/js/' . $pagetype . '.js';
        if (file_exists($filepath)) {
            $skilllab_page_js[] =  $pagetype;
        }
        // By page layout
        $pagelayout = $PAGE->pagelayout;
        $filepath = $CFG->dirroot . $theme_assets . '/js/' . $pagelayout . '.js';
        if (file_exists($filepath)) {
            $skilllab_page_js[] =  $pagelayout;
        }
        foreach ($skilllab_page_js as $key => $value) {
            $PAGE->requires->js(new moodle_url($CFG->wwwroot .  $theme_assets . '/js/' . $value . '.js'));
        }
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . $theme_assets . '/js/javascript.js'));
    }

    /**
     * set user csc site in cookies
     */
    public static function set_skl_theme_cookie($cookies_name, $cookies_value) {
        global $CFG;
        $cookiesecure = is_moodle_cookie_secure();
        // setcookie($cookies_name, $cookies_value, time() + 3600, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $cookiesecure, $CFG->cookiehttponly);
        setcookie($cookies_name, rc4encrypt($cookies_value), time() + 3600, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $cookiesecure, $CFG->cookiehttponly);
    }

    /**
     * 
     */
    public static function unset_skl_theme_cookie($cookies_name) {
        global $CFG;
        $cookiesecure = is_moodle_cookie_secure();
        setcookie($cookies_name, '', time() - HOURSECS, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $cookiesecure, $CFG->cookiehttponly);
    }

    /**
     * return human readable date time
     */
    public static function skl_get_user_date_time($timestamp, $format = '%b %d, %Y') {
        // '%A, %b %d, %Y, %I:%M %p'
        $date = new \DateTime();
        $date->setTimestamp(intval($timestamp));
        $user_date_time = userdate($date->getTimestamp(), $format);
        return $user_date_time;
    }

    /**
     * ---- END ----
     */
}
