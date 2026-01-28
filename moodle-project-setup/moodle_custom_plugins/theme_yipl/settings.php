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
 * @package   theme_yipl
 * @copyright 2025 YIPL
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $PAGE;
if (optional_param('section', '', PARAM_TEXT) == 'themesettingyipl') {
    if ($PAGE->pagelayout === 'admin' &&  $PAGE->pagetype === 'admin-setting-themesettingyipl') {
        // CodeMirror core & extras
        $PAGE->requires->css(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.css'));
        $PAGE->requires->css(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/theme/material-palenight.min.css'));

        $PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.js'), true);
        $PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/mode/javascript/javascript.min.js'), true);
        $PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/mode/css/css.min.js'), true);
        $PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/addon/display/autorefresh.min.js'), true);

        // theme css and js
        $PAGE->requires->css('/theme/yipl/style/admin-themesetting.css');
        $PAGE->requires->js_call_amd('theme_yipl/admin-themesetting', 'init', [
            'yipl',
            ['hero_banner_slider_number', 'footer_menu_number', 'banner_cta_count']
        ]);
    }
}

// Ensure only admins see this
if ($hassiteconfig) {
    //Create the top-level category: "YIPL"
    $ADMIN->add('root', new admin_category('yipladmin', get_string('pluginname', 'theme_yipl')));
    // Create a subcategory inside "YIPL" (e.g., "General Settings")
    $ADMIN->add('yipladmin', new admin_category('yipladmin_general', get_string('pluginname', 'theme_yipl')));

    /**
     * YIPL Setings
     */
    $ADMIN->add('yipladmin_general', new admin_externalpage(
        'yipladmin_themesettingyipl', // Unique identifier
        get_string('configtitle', 'theme_yipl'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingyipl') // External URL
    ));

    /**
     * Contact Detail
     */
    $ADMIN->add('yipladmin_general', new admin_externalpage(
        'yipladmin_contact_detail', // Unique identifier
        get_string('contact_detail', 'theme_yipl'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingyipl&title=theme-yipl-contact-detail#general_setting_tab') // External URL
    ));

    /**
     * Start Guideline
     */
    $ADMIN->add('yipladmin_general', new admin_externalpage(
        'yipladmin_start_guideline', // Unique identifier
        get_string('start_guideline', 'theme_yipl'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingyipl&title=theme-yipl-start-guideline#frontpage_setting_tab') // External URL
    ));

    /**
     * FAQS 
     */
    $yipl_faqs = (int)get_config('theme_yipl', 'yipl_faqs');
    if ($yipl_faqs) {
        $ADMIN->add('yipladmin_general', new admin_externalpage(
            'yipladmin_faq', // Unique identifier
            get_string('faq', 'theme_yipl'), // Link name
            new moodle_url('/theme/yipl/page/faqs/admin.php') // External URL
        ));
    }

    /**
     * Testimonial
     */
    $yipl_testimonial = (int)get_config('theme_yipl', 'yipl_testimonial');
    if ($yipl_testimonial) {
        $ADMIN->add('yipladmin_general', new admin_externalpage(
            'yipladmin_testimonial', // Unique identifier
            get_string('testimonial', 'theme_yipl'), // Link name
            new moodle_url('/theme/yipl/page/testimonial/admin.php') // External URL
        ));
    }

    /**
     * Custom Page
     */
    $yipl_custom_pages = (int)get_config('theme_yipl', 'yipl_custom_pages');
    if ($yipl_custom_pages) {
        $ADMIN->add('yipladmin_general', new admin_externalpage(
            'yipladmin_custom_pages', // Unique identifier
            get_string('custom_pages', 'theme_yipl'), // Link name
            new moodle_url('/theme/yipl/page/admin.php') // External URL
        ));
    }

    /**
     * Footer Settings
     */
    $ADMIN->add('yipladmin_general', new admin_externalpage(
        'yipladmin_footer', // Unique identifier
        get_string('footer', 'theme_yipl') . " " . get_string('settings', 'theme_yipl'), // Link name
        new moodle_url('/admin/settings.php?section=themesettingyipl#footer_settings_tab') // External URL
    ));

    /**
     *
     */
}

/**
 * Theme Settings
 */
if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingyipl', get_string('configtitle', 'theme_yipl'));
    \theme_yipl\form\yipl_settings::general_setting($settings);
    \theme_yipl\form\yipl_settings::frontpage_setting($settings);
    \theme_yipl\form\yipl_settings::courses_settings($settings);
    \theme_yipl\form\yipl_settings::footer_settings($settings);
    \theme_yipl\form\yipl_settings::yipl_advance_settings($settings);
    \theme_yipl\form\yipl_settings::style_script_settings($settings);
}
