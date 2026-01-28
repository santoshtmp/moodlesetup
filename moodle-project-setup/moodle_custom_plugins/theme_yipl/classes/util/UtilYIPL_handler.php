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
 * YIPL helper to load a theme_yipl settings and configuration.
 * @package   theme_yipl   
 * @copyright 2025 YIPL
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\util;

use moodle_url;

defined('MOODLE_INTERNAL') || die;

class UtilYIPL_handler
{

    /**
     * @param string $yipl_auth type
     */
    public static $yipl_auth = 'yipl';

    /**
     * security_header
     */
    public static function security_header()
    {
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
     * @param string $type
     * @return string $output_value
     * https://www.php.net/manual/en/function.openssl-encrypt.
     * https://www.php.net/manual/en/function.openssl-decrypt.php
     * https://www.geeksforgeeks.org/how-to-encrypt-and-decrypt-a-php-string/
     * 
     */
    public static function encrypt_decrypt_value($value, $type = "decrypt")
    {
        $output_value = '';
        $cipher_algo = 'AES-256-CBC';
        $openssl_decrypt_key = '3uPSO9hQ/2KgLJ5iJXU03Lhaef5SWT4YghGtZGC43AExF6/eLagf2OB3E7/SZaM';
        $openssl_iv_key = ')]Ks[P2Qv7G!79p-';

        if ($type == "decrypt") {
            $output_value = openssl_decrypt($value, $cipher_algo, $openssl_decrypt_key, 0, $openssl_iv_key);
        }

        if ($type == "encrypt") {
            $output_value =  openssl_encrypt($value, $cipher_algo, $openssl_decrypt_key, 0, $openssl_iv_key);
        }

        return $output_value;
    }

    /**
     * set_extra_css_js
     * @param string $path path to your theme example '/theme/yourtheme'
     */
    public static function set_extra_css_js($path)
    {
        global $PAGE, $CFG;
        // $theme = isset($PAGE->theme->name) ? $PAGE->theme->name : '';
        $style_path = $path . "/style";
        $js_path = $path . "/javascript";
        /**
         * css files
         */
        $page_css = [];
        // By page type
        $pagetype = $PAGE->pagetype;
        $filepath = $CFG->dirroot . $style_path . '/' . $pagetype . '.css';
        if (file_exists($filepath)) {
            $page_css[] =  $pagetype;
        }
        // By page layout
        $pagelayout = $PAGE->pagelayout;
        $filepath = $CFG->dirroot . $style_path . '/' . $pagelayout . '.css';
        if (file_exists($filepath)) {
            $page_css[] =  $pagelayout;
        }
        foreach ($page_css as $key => $value) {
            $PAGE->requires->css(new moodle_url($CFG->wwwroot . $style_path . '/' . $value . '.css'));
        }

        /**
         * js files
         */
        $page_js = [];
        // By page type
        $pagetype = $PAGE->pagetype;
        $filepath = $CFG->dirroot . $js_path . '/' . $pagetype . '.js';
        if (file_exists($filepath)) {
            $page_js[] =  $pagetype;
        }
        // By page layout
        $pagelayout = $PAGE->pagelayout;
        $filepath = $CFG->dirroot . $js_path . '/' . $pagelayout . '.js';
        if (file_exists($filepath)) {
            $page_js[] =  $pagelayout;
        }
        foreach ($page_js as $key => $value) {
            $PAGE->requires->js(new moodle_url($CFG->wwwroot .  $js_path . '/' . $value . '.js'));
        }
    }


    /**
     * 
     */
    public static function set_yipl_cookie($cookies_name, $cookies_value)
    {
        global $CFG;
        $cookiesecure = is_moodle_cookie_secure();
        setcookie($cookies_name, $cookies_value, time() + 3600, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $cookiesecure, $CFG->cookiehttponly);
    }

    /**
     * 
     */
    public static function unset_yipl_cookie($cookies_name)
    {
        global $CFG;
        $cookiesecure = is_moodle_cookie_secure();
        setcookie($cookies_name, '', time() - HOURSECS, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $cookiesecure, $CFG->cookiehttponly);
    }

    /**
     * 
     */
    public static function moodle_file_url($url)
    {
        global $CFG;
        $relativebaseurl = preg_replace('|^https?://|i', '//', $CFG->wwwroot);
        $url = str_replace($relativebaseurl, '', $url);
        $url = new \moodle_url($url);

        return $url;
    }

    /**
     * 
     */
    public static function set_sync_parent_theme_setting($child_theme, $setting, $parent_theme)
    {
        $des_req = 'required updated';
        $sync_parent_child_settings = get_config($child_theme, $setting);
        if ($sync_parent_child_settings) {
            $parent_theme_settings = (array)get_config($parent_theme);
            unset($parent_theme_settings['version']);
            foreach ($parent_theme_settings as $key => $value) {
                $child_setting = get_config($child_theme, $key);
                if ($child_setting) {
                    if ($child_setting != $value) {
                        set_config($key, $value, $plugin = $child_theme);
                    }
                } else {
                    set_config($key, $value, $plugin = $child_theme);
                }
            }
            $des_req = 'all updated';
        }
        return '<br> <strong> ' . $des_req . ' </strong>';
    }


    /**
     * @return array
     */
    public static function get_login_form_context()
    {
        global $CFG;
        $maintenance = '';
        if ($CFG->maintenance_enabled == true) {
            if (!empty($CFG->maintenance_message)) {
                $maintenance = $CFG->maintenance_message;
            } else {
                $maintenance = get_string('sitemaintenance', 'admin');
            }
        }
        $context = [
            'loginurl' => get_login_url(),
            'logintoken' => \core\session\manager::get_login_token(),
            'canloginasguest' => $CFG->guestloginbutton and !isguestuser(),
            'canloginbyemail' => !empty($CFG->authloginviaemail),
            'cansignup' => $CFG->registerauth == 'email' || !empty($CFG->registerauth),
            'maintenance' => format_text($maintenance, FORMAT_MOODLE)
        ];
        return $context;
    }

    // class end
}
