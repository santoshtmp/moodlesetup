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
 * skilllab theme settings form
 * @package   theme_skilllab
 * @copyright 2024 YIPL
 * @license   http://www.gn.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_skilllab\form;

use admin_setting_configcheckbox;
use admin_settingpage;
use admin_setting_configcolourpicker;
use admin_setting_configselect;
use admin_setting_configstoredfile;
use admin_setting_configtext;
use admin_setting_configtextarea;
use admin_setting_configthemepreset;
use admin_setting_heading;
use admin_setting_scsscode;

defined('MOODLE_INTERNAL') || die;


/**
 * 
 */
class skl_settings
{
    /**
     * general_setting   
     * 
     */
    public static function general_setting($settings)
    {
        $page = new admin_settingpage('theme_skilllab_general', get_string('generalsettings', 'theme_skilllab'));

        // Logo file setting.
        $name = 'theme_skilllab/logo';
        $title = get_string('logo', 'theme_skilllab');
        $description = get_string('logodesc', 'theme_skilllab');
        $opts = array('accepted_types' => array('.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'), 'maxfiles' => 1);
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo', 0, $opts);
        $page->add($setting);

        // Favicon setting.
        $name = 'theme_skilllab/favicon';
        $title = 'Favicon';
        $description = get_string('favicondesc', 'theme_skilllab');
        $opts = array('accepted_types' => array('.ico'), 'maxfiles' => 1);
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon', 0, $opts);
        $page->add($setting);

        // Unaddable blocks.
        // Blocks to be excluded when this theme is enabled in the "Add a block" list: Administration, Navigation, Courses and
        // Section links.
        $default = 'navigation,settings,course_list,section_links';
        $setting = new admin_setting_configtext(
            'theme_skilllab/unaddableblocks',
            get_string('unaddableblocks', 'theme_skilllab'),
            get_string('unaddableblocks_desc', 'theme_skilllab'),
            $default,
            PARAM_TEXT
        );
        $page->add($setting);

        // Preset.
        $name = 'theme_skilllab/preset';
        $title = get_string('preset', 'theme_skilllab');
        $description = get_string('preset_desc', 'theme_skilllab');
        $default = 'default.scss';

        $context = \context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'theme_skilllab', 'preset', 0, 'itemid, filepath, filename', false);

        $choices = [];
        foreach ($files as $file) {
            $choices[$file->get_filename()] = $file->get_filename();
        }
        // These are the built in presets.
        $choices['default.scss'] = 'default.scss';
        $choices['plain.scss'] = 'plain.scss';

        $setting = new admin_setting_configthemepreset($name, $title, $description, $default, $choices, 'skilllab');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $options = array();
        for ($i = 1; $i <= 100; $i++) {
            $options[$i] = $i;
        }

        $name = 'theme_skilllab/course_list_per_page';
        $title = 'Course List Per Page Data';
        $description = '';
        $default = 10;
        $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $name = 'theme_skilllab/category_list_per_page';
        $title = 'Category List Per Page Data';
        $description = '';
        $default = 5;
        $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $name = 'theme_skilllab/skl_pre_loader';
        $title = 'Pre-loader ';
        $description = 'Enable or Disable pre-loader';
        $default = 0;
        $choices = array(0 => 'Disable', 1 => 'Enable');
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $page->add($setting);

        $name = 'theme_skilllab/skl_time_track';
        $title = 'SKL Time Track ';
        $description = 'Enable or Disable SKL Time Track';
        $default = 0;
        $choices = array(0 => 'Disable', 1 => 'Enable');
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $page->add($setting);

        // Must add the page after definiting all the settings!
        $settings->add($page);
    }

    /*
    * --------------------
    * Skill lab API and enceyption settings tab
    * --------------------
    */
    public static function skl_api($settings)
    {

        $page = new admin_settingpage('theme_skilllab_api', 'Skill lab Setting');

        $name = 'theme_skilllab/api_setting_other';
        $heading = 'LMS Web Service API';
        $information = '
1. <a href="/admin/search.php?query=enablewebservices" > Enable web service </a> <br>
2. <a href="/admin/settings.php?section=webserviceprotocols" > Enable protocol </a> <br>
3. <a href="/admin/settings.php?section=webservicesoverview" > Check Web services Overview </a> <br>
3. <a href="/admin/settings.php?section=externalservices" > Check External services </a> <br>
4. <a href="/admin/webservice/tokens.php" > Manage tokens </a> <br>
';

        $setting = new admin_setting_heading($name, $heading, $information);
        $page->add($setting);


        $setting = new admin_setting_heading('api_setting_other_separator', '', '<hr>');
        $page->add($setting);

        $name = 'theme_skilllab/api_setting';
        $heading = 'Skill lab API Setting';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $page->add($setting);

        $name = 'theme_skilllab/site_environment';
        $title = 'Environment';
        $description = '';
        $default = 0;
        $choices = array(0 => 'Staging', 1 => 'Live', 2 => 'Local');
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $page->add($setting);

        $name = 'theme_skilllab/api_key';
        $title = 'Skill lab API key';
        $description = '';
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_skilllab/api_fail_notify_user_id';
        $title = 'Skill lab API Fail Notifiy user ids';
        $description = 'Moodle User IDs [ Example value: 2,3 ]. This id user will get notification when calback csc api failed or in other defined case. The notification will be send when <a href="/admin/message.php">site notification</a> is enable ';
        $default = '2';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        $setting = new admin_setting_heading('site_environment_separator', '', '<hr>');
        $page->add($setting);

        // ----------------------------------------------

        $name = 'theme_skilllab/encryption';
        $heading = 'Skill lab encryption Setting';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $page->add($setting);

        $name = 'theme_skilllab/encryptionAlgorithm';
        $title = 'Encryption Algorithm';
        $description = '';
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_skilllab/openssl_decrypt_key';
        $title = 'Decrypt Key';
        $description = '';
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_skilllab/openssl_iv_key';
        $title = 'IV Key';
        $description = '';
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // ---------------------------------------------------------------------------------------

        $settings->add($page);
    }

    /**
     * 
     */
    public static function skl_advance_style($settings)
    {
        // Advanced settings.
        // get_string('advancedsettings', 'theme_skilllab')
        $page = new admin_settingpage('theme_skilllab_scss_style', "Theme Style");

        // Raw SCSS to include before the content.
        $setting = new admin_setting_scsscode(
            'theme_skilllab/scsspre',
            get_string('rawscsspre', 'theme_skilllab'),
            get_string('rawscsspre_desc', 'theme_skilllab'),
            '',
            PARAM_RAW
        );
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Raw SCSS to include after the content.
        $setting = new admin_setting_scsscode(
            'theme_skilllab/scss',
            get_string('rawscss', 'theme_skilllab'),
            get_string('rawscss_desc', 'theme_skilllab'),
            '',
            PARAM_RAW
        );
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $settings->add($page);
    }

    /*
    * --------------------
    * Footer settings tab
    * --------------------
    */
    public static function skl_footer_settings($settings)
    {
        $page = new admin_settingpage('theme_skilllab_footer', get_string('footersettings', 'theme_skilllab'));


        $name = 'theme_skilllab/footer_general';
        $heading = 'Footer General Setting';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $page->add($setting);
        // ------------------------------------------------------------------------------------------
        $name = 'theme_skilllab/footer_desc';
        $title = 'Footer short descriptions';
        $description = '';
        $default = 'Our vision is to provide convenience and help increase your sales business.';
        $setting = new admin_setting_configtextarea($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        $setting = new admin_setting_heading('footer_desc_separator', '', '<hr>');
        $page->add($setting);
        // ------------------------------------------------------------------------------------------

        $name = 'theme_skilllab/footer_firstcolumn';
        $heading = 'Footer First Column Setting';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $page->add($setting);

        $name = 'theme_skilllab/firstcolumn_title';
        $title = 'First Column Title';
        $description = '';
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_skilllab/firstcolumn_count';
        $title = 'First Column Count';
        $description = '';
        $default = 0;
        $options = array();
        for ($i = 0; $i < 6; $i++) {
            $options[$i] = $i;
        }
        $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        $firstcolumn_count = get_config('theme_skilllab', 'firstcolumn_count');
        if (!$firstcolumn_count) {
            $firstcolumn_count = $default;
        }
        if ($firstcolumn_count) {

            for ($sliderindex = 1; $sliderindex <= $firstcolumn_count; $sliderindex++) {

                $setting = new admin_setting_heading('footer_count_' . $sliderindex, '', '<hr>');
                $page->add($setting);

                $name = 'theme_skilllab/firstcolumn_label_' . $sliderindex;
                $title = 'Label - ' . $sliderindex;
                $description = '';
                $default = 'label-' . $sliderindex;
                $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
                $page->add($setting);

                $name = 'theme_skilllab/firstcolumn_link_' . $sliderindex;
                $title = 'Link - ' . $sliderindex;
                $description = '';
                $default = '#link';
                $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
                $page->add($setting);
            }
        }

        $setting = new admin_setting_heading('firstcolumn_separator', '', '<hr>');
        $page->add($setting);
        // ------------------------------------------------------------------------------------------

        $name = 'theme_skilllab/footer_secondcolumn';
        $heading = 'Footer Second Column Setting';
        $information = 'Enter the setting for social link';
        $setting = new admin_setting_heading($name, $heading, $information);
        $page->add($setting);

        $name = 'theme_skilllab/secondcolumn_title';
        $title = 'Second Column Title';
        $description = 'Enter Second Column Title';
        $default = 'Social';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // Facebook url setting.
        $name = 'theme_skilllab/facebook';
        $title = get_string('facebook', 'theme_skilllab');
        $description = get_string('facebookdesc', 'theme_skilllab');
        $setting = new admin_setting_configtext($name, $title, $description, 'http://www.facebook.com');
        $page->add($setting);

        // Twitter url setting.
        $name = 'theme_skilllab/twitter';
        $title = get_string('twitter', 'theme_skilllab');
        $description = get_string('twitterdesc', 'theme_skilllab');
        $setting = new admin_setting_configtext($name, $title, $description, 'http://www.twitter.com');
        $page->add($setting);

        // Instagram url setting.
        $name = 'theme_skilllab/instagram';
        $title = get_string('instagram', 'theme_skilllab');
        $description = get_string('instagramdesc', 'theme_skilllab');
        $setting = new admin_setting_configtext($name, $title, $description, 'https://www.instagram.com');
        $page->add($setting);

        // Linkdin url setting.
        $name = 'theme_skilllab/linkedin';
        $title = get_string('linkedin', 'theme_skilllab');
        $description = get_string('linkedindesc', 'theme_skilllab');
        $setting = new admin_setting_configtext($name, $title, $description, 'http://www.linkedin.com');
        $page->add($setting);

        $setting = new admin_setting_heading('secondcolumn_separator', '', '<hr>');
        $page->add($setting);

        // ------------------------------------------------------------------------------------------

        $name = 'theme_skilllab/footer_thirdcolumn';
        $heading = 'Footer Third Column Setting';
        $information = 'Enter the setting for Contact link';
        $setting = new admin_setting_heading($name, $heading, $information);
        $page->add($setting);

        $name = 'theme_skilllab/thirdcolumn_title';
        $title = 'Third Column Title';
        $description = 'Enter Third Column Title';
        $default = 'Contact';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // location_point.
        $name = 'theme_skilllab/location_point';
        $title = 'Location Point';
        $description = 'Location point, address, P.O.BOX ; Also accept HTML code tags';
        $default = '';
        $setting = new admin_setting_configtextarea($name, $title, $description,  $default);
        $page->add($setting);

        // phone_num.
        $name = 'theme_skilllab/phone_num';
        $title = get_string('phone_num', 'theme_skilllab');
        $description = get_string('phone_numdesc', 'theme_skilllab');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description,  $default);
        $page->add($setting);

        // Mail.
        $name = 'theme_skilllab/mail';
        $title = get_string('mail', 'theme_skilllab');
        $description = get_string('maildesc', 'theme_skilllab');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description,  $default);
        $page->add($setting);

        // Website.
        $name = 'theme_skilllab/website';
        $title = get_string('website', 'theme_skilllab');
        $description = get_string('websitedesc', 'theme_skilllab');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description,  $default);
        $page->add($setting);

        $setting = new admin_setting_heading('thirdcolumn_separator', '', '<hr>');
        $page->add($setting);
        // ------------------------------------------------------------------------------------------

        $name = 'theme_skilllab/footer_other';
        $heading = 'Footer Other Setting ';
        // $information = 'Enter other setting for footer';
        $information = '';
        $setting = new admin_setting_heading($name, $heading, $information);
        $page->add($setting);

        $name = 'theme_skilllab/copyright';
        $title = get_string('copyright', 'theme_skilllab');
        $description = get_string('copyrightdesc', 'theme_skilllab');
        $default = 'Copyright © 2023 skilllab.';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        $name = 'theme_skilllab/other_extra_count';
        $title = 'Other Extra Field Count';
        $description = '';
        $default = 0;
        $options = array();
        for ($i = 0; $i < 6; $i++) {
            $options[$i] = $i;
        }
        $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        $other_extra_count = get_config('theme_skilllab', 'other_extra_count');
        if (!$other_extra_count) {
            $other_extra_count = $default;
        }
        if ($other_extra_count) {
            for ($sliderindex = 1; $sliderindex <= $other_extra_count; $sliderindex++) {

                $setting = new admin_setting_heading('other_extra_count' . $sliderindex, '', '<hr>');
                $page->add($setting);

                $name = 'theme_skilllab/other_extra_label_' . $sliderindex;
                $title = 'Label - ' . $sliderindex;
                $description = '';
                $default = 'label-' . $sliderindex;
                $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
                $page->add($setting);

                $name = 'theme_skilllab/other_extra_link_' . $sliderindex;
                $title = 'Link - ' . $sliderindex;
                $description = '';
                $default = '#link';
                $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT);
                $page->add($setting);
            }
        }
        // ------------------------------------------------------------------------------------------

        $settings->add($page);
    }


    /*
    * -----------------------
    * Frontpage settings tab
    * -----------------------
    */
    public static function skl_frontpagesettings($settings)
    {

        $page = new admin_settingpage('theme_skilllab_frontpage', get_string('frontpagesettings', 'theme_skilllab'));

        // =========================================================
        // $name = 'theme_skilllab/newsletter_section';
        // $heading = 'Newsletter Section';
        // $information = 'Enter the setting for newsletter section';
        // $setting = new admin_setting_heading($name, $heading, $information);
        // $page->add($setting);

        $settings->add($page);
    }
    /**
     * === ENF ===
     */
}
