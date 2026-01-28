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
 * yipl
 * @package   theme_yipl
 * @copyright 2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\handler;

defined('MOODLE_INTERNAL') || die();

use core\output\theme_config;
use theme_yipl\util\UtilYIPL_handler;
use moodle_url;

class settings_handler
{

    /**
     * @var \stdClass $theme The theme object.
     */
    public $theme;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->theme = theme_config::load('yipl');
    }

    /**
     * Get particular theme yipl setting
     */
    public static function setting($theme_yipl_setting, $filearea = '')
    {
        $theme = theme_config::load('yipl');
        if ($filearea) {
            $file_url = $theme->setting_file_url($theme_yipl_setting, $filearea);
            return UtilYIPL_handler::moodle_file_url($file_url);
        }
        return isset($theme->settings->$theme_yipl_setting) ? $theme->settings->$theme_yipl_setting : '';
    }

    /**
     * setting_advanced_callback_api
     * api_token = ''
     * api_url = '';
     * @return array [] 
     */
    public static function setting_advanced_callback_api()
    {
        $settings = [];
        $yipl_settings = get_config('theme_yipl');
        $settings['api_token'] = ($yipl_settings->api_token) ?: '';
        $settings['api_url'] = ($yipl_settings->api_url) ?: '';
        return $settings;
    }

    /**
     * Footer settings
     */
    public static function footer_settings()
    {
        $theme = theme_config::load('yipl');
        $copyright = $theme->settings->copyright;
        $footer_menu_number = (int)$theme->settings->footer_menu_number;

        $templatecontext = [];
        $templatecontext['copyright'] = ($copyright) ? str_replace("{year}", date('Y'), format_string($copyright)) : "";
        $templatecontext['footer_description'] = format_string($theme->settings->footer_description);
        $templatecontext['footer_contact_section'] = $theme->settings->footer_contact_section;
        $templatecontext['footer_contact_section_label'] = format_string($theme->settings->footer_contact_section_label);
        $templatecontext['footer_menu'] = [];
        if ($footer_menu_number > 0) {
            for ($i = 0, $j = 1; $i < $footer_menu_number; $i++, $j++) {
                $label_name = 'footer_menu_label_' . $j;
                $items_name = 'footer_menu_items_' . $j;
                $menu_items = $theme->settings->$items_name;
                $menu_items_values = [];
                if ($menu_items) {
                    $lines = explode("\n", $menu_items);
                    $linenumber_key = 0;
                    foreach ($lines as $linenumber => $line) {
                        $line = trim($line);
                        if (strlen($line) == 0) {
                            continue;
                        }
                        $values = explode('|', $line);
                        $title = $link = '';
                        foreach ($values as $key => $value) {
                            $value = trim($value);
                            if ($value !== '') {
                                switch ($key) {
                                    case 0: // prefix and Menu text.
                                        $title = $value;
                                        break;
                                    case 1: // URL.
                                        $link = ($value) ?: '#';
                                        break;
                                }
                            }
                        }
                        $menu_items_values[$linenumber_key]['title'] = format_string($title);
                        $menu_items_values[$linenumber_key]['link'] = $link;
                        $linenumber_key++;
                    }
                }
                $templatecontext['footer_menu'][$i]['label'] = format_string($theme->settings->$label_name);
                $templatecontext['footer_menu'][$i]['label_class'] = str_replace([' ', '_'], '-', strtolower($theme->settings->$label_name));
                $templatecontext['footer_menu'][$i]['items'] = $menu_items_values;
                $templatecontext['footer_menu'][$i]['items_present'] = ($menu_items_values) ? true : false;
            }
        }
        return $templatecontext;
    }

    /**
     * Contact Detail settings
     */
    public static function contact_details_settings()
    {
        $theme = theme_config::load('yipl');
        $phone_number = $theme->settings->phone_number;
        $map_location = $theme->settings->map_location;
        preg_match('/<iframe[^>]+src="([^"]+)"/', $map_location, $matches);
        // 
        $templatecontext = [];
        $templatecontext['contact_form_recipient_email'] = $theme->settings->contact_form_recipient_email;
        $templatecontext['contact_form_recipient_name'] = $theme->settings->contact_form_recipient_name;

        $templatecontext['contact_name'] = format_string($theme->settings->contact_name);
        $templatecontext['location_address'] = format_string($theme->settings->location_address);
        $templatecontext['other_contact_info'] = format_text($theme->settings->other_contact_info);
        $templatecontext['map_location_src'] = (!empty($matches[1])) ? $matches[1] : $map_location;
        $templatecontext['phone_number_exist'] = ($phone_number) ? true : false;
        $templatecontext['phone_number'] = ($phone_number) ? explode(",", $phone_number) : false;
        $templatecontext['mail'] = $theme->settings->mail;
        $templatecontext['website'] = $theme->settings->website;
        $templatecontext['social_link']['facebook'] = $theme->settings->facebook;
        $templatecontext['social_link']['twitter'] = $theme->settings->twitter;
        $templatecontext['social_link']['linkedin'] = $theme->settings->linkedin;
        $templatecontext['social_link']['instagram'] = $theme->settings->instagram;
        $templatecontext['isEmpty'] = self::isArrayValuesEmpty($templatecontext);
        return $templatecontext;
    }

    /**
     * Front Page settings
     */
    public static function front_page_settings()
    {
        global $PAGE;
        $theme = theme_config::load('yipl');

        $templatecontext = [];
        // hero banner
        $hero_banner = $theme->settings->hero_banner;
        if ($hero_banner) {
            $templatecontext['hero_banner'] = true;
            // banner slider or single banner
            $hero_banner_slider = isset($theme->settings->hero_banner_slider) ? $theme->settings->hero_banner_slider : '';
            if ($hero_banner_slider) {
                $templatecontext['hero_banner_slider'] = true;
                $hero_banner_slider_number = (int)$theme->settings->hero_banner_slider_number;
                if ($hero_banner_slider_number > 0) {
                    for ($i = 0; $i < $hero_banner_slider_number; $i++) {
                        $banner_title = 'banner_title_' . ($i + 1);
                        $banner_image = 'banner_image_' . ($i + 1);
                        $templatecontext['banner_slider'][$i]['banner_title'] = format_string($theme->settings->$banner_title);
                        $templatecontext['banner_slider'][$i]['banner_image'] = $theme->setting_file_url($banner_image, $banner_image);
                    }
                }
            } else {
                $templatecontext['banner_title_1'] = isset($theme->settings->banner_title_1) ? format_string($theme->settings->banner_title_1) : '';
                $templatecontext['banner_image_1'] = $theme->setting_file_url('banner_image_1', 'banner_image_1');
            }
            // login card
            $hero_banner_login_card = isset($theme->settings->hero_banner_login_card) ? $theme->settings->hero_banner_login_card : '';
            if ($hero_banner_login_card) {
                $templatecontext['hero_banner_login_card'] = $hero_banner_login_card;
            }
            // CTA
            $banner_cta_count = isset($theme->settings->banner_cta_count) ? (int)$theme->settings->banner_cta_count : 0;
            $templatecontext['banner_cta_count'] = $banner_cta_count;
            if ($banner_cta_count > 0) {
                for ($i = 0; $i < $banner_cta_count; $i++) {
                    $cta_label = 'banner_cta_label_' . ($i + 1);
                    $cta_link = 'banner_cta_link_' . ($i + 1);
                    $templatecontext['banner_cta'][$i]['label'] =  format_string($theme->settings->$cta_label);
                    $templatecontext['banner_cta'][$i]['link'] =  $theme->settings->$cta_link;
                }
            }
        }

        // banner popup
        $banner_popup_enable = $theme->settings->banner_popup_enable;
        if ($banner_popup_enable) {
            $templatecontext['banner_popup_enable'] = true;
            $templatecontext['banner_popup_link'] = $theme->settings->banner_popup_link;
            $templatecontext['banner_popup_image'] = $theme->setting_file_url('banner_popup_image', 'banner_popup_image');
            // $templatecontext['popup_image'] = UtilYIPL_handler::moodle_file_url($templatecontext['banner_popup_image']);
            $PAGE->requires->js(new moodle_url('/theme/yipl/javascript/home-banner-popup.js'));
            $PAGE->requires->css(new moodle_url('/theme/yipl/style/home-banner-popup.css'));
        }

        return $templatecontext;
    }

    /**
     * Front Page settings
     */
    public static function start_guideline_settings()
    {
        $theme = theme_config::load('yipl');
        $templatecontext = [];
        $start_guideline_item_count = isset($theme->settings->start_guideline_item_count) ? (int)$theme->settings->start_guideline_item_count : 0;
        if ($start_guideline_item_count) {
            for ($i = 1, $j = 0; $i <= $start_guideline_item_count; $i++, $j++) {
                $start_guideline_image = "start_guideline_image_{$i}";
                $start_guideline_title = "start_guideline_title_{$i}";
                $start_guideline_desc = "start_guideline_desc_{$i}";
                $image = $theme->setting_file_url($start_guideline_image, $start_guideline_image);
                if (empty($image)) {
                    if ($i == 1) {
                        $image = '/theme/yipl/pix/icons/start-guideline-user.svg';
                    } elseif ($i == 2) {
                        $image = '/theme/yipl/pix/icons/start-guideline-circle-check.svg ';
                    } else {
                        $image = '/theme/yipl/pix/icons/start-guideline-web.svg';
                    }
                }
                $templatecontext['start_guideline'][$j]['image'] = $image;
                $templatecontext['start_guideline'][$j]['title'] = format_string($theme->settings->$start_guideline_title);
                $templatecontext['start_guideline'][$j]['desc'] = format_string($theme->settings->$start_guideline_desc);
            }
        }
        return $templatecontext;
    }

    /**
     * Check if the array key value is present or not
     */
    public static function isArrayValuesEmpty($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!self::isArrayValuesEmpty($value)) {
                    return false;
                }
            } elseif (!empty($value)) {
                return false;
            }
        }
        return true;
    }
}
