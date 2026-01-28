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
 * renderers/core_renderer.php
 * @package    theme_skilllab
 * @copyright  2015 onwards LMSACE Dev Team (http://www.lmsace.com)
 * @author    LMSACE Dev Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The standard implementation of the core_renderer interface.
 *
 * @copyright 2009 Tim Hunt
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.0
 * @package core
 * @category output
 */

namespace theme_skilllab\output;

use html_writer;
use moodle_url;
use renderer_base;
use single_button;
use stdClass;
use theme_config;

defined('MOODLE_INTERNAL') || die;


/**
 * Academi core renderer renderer from the moodle core renderer
 * @copyright  2015 onwards LMSACE Dev Team (http://www.lmsace.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Renders the "breadcrumb" for all pages in boost-wplus.
     *
     * @return string the HTML for the navbar.
     */
    public function navbar(): string {
        $newnav = new \theme_skilllab\navigation\skilllab_boostnavbar($this->page);
        return $this->render_from_template('core/navbar', $newnav);
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $CFG, $USER;
        $pagetype = $this->page->pagetype;
        $pagelayout = $this->page->pagelayout;
        $homepage = get_home_page();
        $homepagetype = null;
        // Add a special case since /my/courses is a part of the /my subsystem.
        if ($homepage == HOMEPAGE_MY || $homepage == HOMEPAGE_MYCOURSES) {
            $homepagetype = 'my-index';
        } else if ($homepage == HOMEPAGE_SITE) {
            $homepagetype = 'site-index';
        }
        if (
            $this->page->include_region_main_settings_in_header_actions() &&
            !$this->page->blocks->is_block_present('settings')
        ) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(
                html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
                )
            );
        }

        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        if ($pagelayout != 'incourse') {
            $header->contextheader = $this->context_header();
        }
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        if ($pagetype === 'user-profile') {
            $header->pageheadingbutton = '';
        } else {
            $header->pageheadingbutton = $this->page_heading_button();
        }
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();
        if (!empty($pagetype) && !empty($homepagetype) && $pagetype == $homepagetype) {
            $header->welcomemessage = \core_user::welcome_message();
        }

        if ($this->body_id() === 'page-course-index-category' || $pagetype === 'scholarship-course-type' ||  $pagetype === 'career-road-map-course-type') {
            $systemcontext = \context_system::instance();
            // if (has_capability('moodle/course:create', $systemcontext)) {
            // }
            $header->add_default_course = false;
            if (has_capability('moodle/course:create', $systemcontext, $USER->id)) {
                $header->add_default_course = true;
                $header->pagetype = $pagetype;
                if ($pagetype === 'scholarship-course-type') {
                    $header->add_course_url = $CFG->wwwroot . '/course/edit.php?scholarship=1&returnto=url&returnurl=/scholarship&sesskey=' . sesskey();
                } else if ($pagetype === 'career-road-map-course-type') {
                    $header->add_course_url = $CFG->wwwroot . '/course/edit.php?career_road_map=1&returnto=url&returnurl=/career_road_map&sesskey=' . sesskey();
                } else {
                    $header->add_course_url = $CFG->wwwroot . '/course/edit.php?returnto=topcat&category=0';
                }

                $header->add_course_pagetype = get_string('add-' . $pagetype, 'theme_skilllab');
            }
        }
        return $this->render_from_template('core/full_header', $header);
    }

    /**
     * Returns the moodle_url for the favicon.
     *
     * @since Moodle 2.5.1 2.6
     * @return moodle_url The moodle_url for the favicon
     */
    public function favicon() {
        global $CFG;

        $theme = theme_config::load('skilllab');

        $favicon = $theme->setting_file_url('favicon', 'favicon');

        if (!empty(($favicon))) {
            $urlreplace = preg_replace('|^https?://|i', '//', $CFG->wwwroot);
            $favicon = str_replace($urlreplace, '', $favicon);

            return new moodle_url($favicon);
        }

        return parent::favicon();
    }

    /**
     * Gets the logo to be rendered.
     *
     * The priority of get log is: 1st try to get the theme logo, 2st try to get the theme logo
     * If no logo was found return false
     *
     * @return mixed
     */
    public function get_logo() {
        if ($this->should_display_theme_logo()) {
            return $this->get_theme_logo_url();
        }

        $url = $this->get_logo_url();
        if ($url) {
            return $url->out(false);
        }

        return false;
    }

    /**
     * Get the main logo URL.
     *
     * @return string
     */
    public function get_theme_logo_url() {
        $theme = theme_config::load('skilllab');

        return $theme->setting_file_url('logo', 'logo');
    }

    /**
     * Whether we should display the main theme logo in the navbar.
     *
     * @return bool
     */
    public function should_display_theme_logo() {
        $logo = $this->get_theme_logo_url();

        return !empty($logo);
    }



    /**
     * Returns HTML attributes to use within the body tag. This includes an ID and classes.
     *
     * @since Moodle 2.5.1 2.6
     * @param string|array $additionalclasses Any additional classes to give the body tag,
     * @return string
     */
    public function body_attributes($additionalclasses = array()) {
        global $USER;
        $skilllab_css_class = ' skilllab';
        // isguestuser();
        // isloggedin();
        if ($USER->id > 1) {
            $skilllab_css_class = $skilllab_css_class . ' auth-user';
        } else {
            $skilllab_css_class = $skilllab_css_class . ' unauth-user';
        }

        if (!is_array($additionalclasses)) {
            $additionalclasses = explode(' ', $additionalclasses);
        }

        return ' id="' . $this->body_id() . '" class="' . $this->body_css_classes($additionalclasses) . $skilllab_css_class . ' "';
    }

    /**
     * Return the site's logo URL, if any.
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    public function get_logo_url($maxwidth = null, $maxheight = 200) {
        global $CFG;
        $logo = get_config('core_admin', 'logo');

        if (empty($logo)) {
            $logo = get_config('theme_skilllab', 'logo');

            if (empty($logo)) {
                return false;
            }

            return moodle_url::make_pluginfile_url(
                \context_system::instance()->id,
                'theme_skilllab',
                'logo',
                '',
                theme_get_revision(),
                $logo
            );
        }

        // 200px high is the default image size which should be displayed at 100px in the page to account for retina displays.
        // It's not worth the overhead of detecting and serving 2 different images based on the device.

        // Hide the requested size in the file path.
        $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

        // Use $CFG->themerev to prevent browser caching when the file changes.
        return moodle_url::make_pluginfile_url(
            \context_system::instance()->id,
            'core_admin',
            'logo',
            $filepath,
            theme_get_revision(),
            $logo
        );
    }

    /**
     * Renders the login form.
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $CFG, $SITE;

        $context = $form->export_for_template($this);
        $context->errorformatted = $this->error_text($context->error);
        $context = (array) $context;
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context['logourl'] = $url;
        $context['sitename'] = format_string(
            $SITE->fullname,
            true,
            ['context' => \context_course::instance(SITEID), "escape" => false]
        );
        $context['logourl_action'] = $CFG->wwwroot . '/theme/skilllab/pages/csc-lms-login.php';

        $site_environment = theme_skilllab_get_setting('site_environment');
        $context['stage'] = (($site_environment == 0) || ($site_environment == 2)) ? true : false;
        $context['live'] = ($site_environment == 1) ? true : false;


        return $this->render_from_template('core/loginform', $context);
    }

    /**
     * return user username
     */
    public function get_user_name() {
        global $USER;
        return $USER->username;
    }

    /**
     * return user get_full_name
     */
    public function get_user_full_name() {
        global $USER;
        return $USER->firstname . ' ' . $USER->lastname;
    }


    /**
     * Returns standard navigation between activities in a course.
     *
     * @return string the navigation HTML.
     */
    public function activity_navigation() {
        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (
            ($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE
        ) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        $course = $this->page->cm->get_course();
        $courseformat = course_get_format($course);

        // If the theme implements course index and the current course format uses course index and the current
        // page layout is not 'frametop' (this layout does not support course index), show no links.
        // if (
        //     $this->page->theme->usescourseindex && $courseformat->uses_course_index() &&
        //     $this->page->pagelayout !== 'frametop'
        // ) {
        //     return '';
        // }

        // Get a list of all the activities in the course.
        $modules = get_fast_modinfo($course->id)->get_cms();
        global $DB;
        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            // if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {

            // --- start to show the not completed activity also
            $course_modules = $DB->get_record('course_modules', ['course' => $module->course, 'id' => $module->id]);
            $this_contect = \context_course::instance($module->course);
            if (has_capability('moodle/course:activityvisibility', $this_contect)) {
                $course_modules->visible = 1;
            }
            // ---end---

            if (!$course_modules->visible || $course_modules->deletioninprogress || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }
            // // Module URL.
            // $linkurl = new moodle_url($module->url, array('forceview' => 1));
            // // Add module URL (as key) and name (as value) to the activity list array.
            // $activitylist[$linkurl->out(false)] = $modname;
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        // Check if we have a previous mod to show.
        if ($position > 0) {
            $prevmod = $mods[$modids[$position - 1]];
        }

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $nextmod = $mods[$modids[$position + 1]];
        }

        // var_dump($nextmod->uservisible);

        // $activitynav = new \core_course\output\activity_navigation($prevmod, $nextmod, $activitylist);
        $activitynav = new \theme_skilllab\navigation\activity_navigation($prevmod, $nextmod, $activitylist);
        $renderer = $this->page->get_renderer('core', 'course');
        return $renderer->render($activitynav);
    }

    /**
     * Returns HTML to display a continue button that goes to a particular URL.
     *
     * @param string|moodle_url $url The url the button goes to.
     * @return string the HTML to output.
     */
    public function continue_button($url, $btn_label = '') {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }
        if (empty($btn_label)) {
            $btn_label = get_string("continue");
        }

        $button = new single_button($url, $btn_label, 'get', single_button::BUTTON_PRIMARY);
        $button->class = 'continuebutton';

        return $this->render($button);
    }

    /**
     * Outputs the opening section of a box.
     *
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public $login_confirm = 0;
    public function box_start($classes = 'generalbox', $id = null, $attributes = array()) {
        $this->opencontainers->push('box', html_writer::end_tag('div'));
        $attributes['id'] = $id;
        $attributes['class'] = 'box py-3 ' . renderer_base::prepare_classes($classes);
        // only for login page conform box
        if (($this->body_id() === 'page-login-index') || ($this->body_id() === 'page-lms-admin-login')) {
            if ($this->login_confirm == 0) {
                $attributes['class'] = $attributes['class'] . ' login-confirm ';
            }
            $this->login_confirm++;
        }
        return html_writer::start_tag('div', $attributes);
    }

    /**
     * The standard tags (meta tags, links to stylesheets and JavaScript, etc.)
     * that should be included in the <head> tag. Designed to be called in theme
     * layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_head_html() {
        global $CFG, $SESSION, $SITE;

        // Before we output any content, we need to ensure that certain
        // page components are set up.

        // Blocks must be set up early as they may require javascript which
        // has to be included in the page header before output is created.
        foreach ($this->page->blocks->get_regions() as $region) {
            $this->page->blocks->ensure_content_created($region, $this);
        }

        $output = '';

        // Give plugins an opportunity to add any head elements. The callback
        // must always return a string containing valid html head content.
        $pluginswithfunction = get_plugins_with_function('before_standard_html_head', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $function) {
                $output .= $function();
            }
        }

        // Allow a url_rewrite plugin to setup any dynamic head content.
        if (isset($CFG->urlrewriteclass) && !isset($CFG->upgraderunning)) {
            $class = $CFG->urlrewriteclass;
            $output .= $class::html_head_setup();
        }

        $output .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
        $output .= '<meta name="keywords" content="skilllab, lms, csc, moodle, ' . $this->page->title . '" />' . "\n";
        // This is only set by the {@link redirect()} method
        $output .= $this->metarefreshtag;

        // Check if a periodic refresh delay has been set and make sure we arn't
        // already meta refreshing
        if ($this->metarefreshtag == '' && $this->page->periodicrefreshdelay !== null) {
            $output .= '<meta http-equiv="refresh" content="' . $this->page->periodicrefreshdelay . ';url=' . $this->page->url->out() . '" />';
        }

        // Set up help link popups for all links with the helptooltip class
        $this->page->requires->js_init_call('M.util.help_popups.setup');

        $focus = $this->page->focuscontrol;
        if (!empty($focus)) {
            if (preg_match("#forms\['([a-zA-Z0-9]+)'\].elements\['([a-zA-Z0-9]+)'\]#", $focus, $matches)) {
                // This is a horrifically bad way to handle focus but it is passed in
                // through messy formslib::moodleform
                $this->page->requires->js_function_call('old_onload_focus', array($matches[1], $matches[2]));
            } else if (strpos($focus, '.') !== false) {
                // Old style of focus, bad way to do it
                debugging('This code is using the old style focus event, Please update this code to focus on an element id or the moodleform focus method.', DEBUG_DEVELOPER);
                $this->page->requires->js_function_call('old_onload_focus', explode('.', $focus, 2));
            } else {
                // Focus element with given id
                $this->page->requires->js_function_call('focuscontrol', array($focus));
            }
        }

        // Get the theme stylesheet - this has to be always first CSS, this loads also styles.css from all plugins;
        // any other custom CSS can not be overridden via themes and is highly discouraged
        $urls = $this->page->theme->css_urls($this->page);
        foreach ($urls as $url) {
            $this->page->requires->css_theme($url);
        }

        // Get the theme javascript head and footer
        if ($jsurl = $this->page->theme->javascript_url(true)) {
            $this->page->requires->js($jsurl, true);
        }
        if ($jsurl = $this->page->theme->javascript_url(false)) {
            $this->page->requires->js($jsurl);
        }

        // Get any HTML from the page_requirements_manager.
        $output .= $this->page->requires->get_head_code($this->page, $this);

        // List alternate versions.
        foreach ($this->page->alternateversions as $type => $alt) {
            $output .= html_writer::empty_tag('link', array(
                'rel' => 'alternate',
                'type' => $type,
                'title' => $alt->title,
                'href' => $alt->url
            ));
        }

        // Add noindex tag if relevant page and setting applied.
        $allowindexing = isset($CFG->allowindexing) ? $CFG->allowindexing : 0;
        $loginpages = array('login-index', 'login-signup');
        if ($allowindexing == 2 || ($allowindexing == 0 && in_array($this->page->pagetype, $loginpages))) {
            if (!isset($CFG->additionalhtmlhead)) {
                $CFG->additionalhtmlhead = '';
            }
            $CFG->additionalhtmlhead .= '<meta name="robots" content="noindex" />';
        }

        if (!empty($CFG->additionalhtmlhead)) {
            $output .= "\n" . $CFG->additionalhtmlhead;
        }

        if ($this->page->pagelayout == 'frontpage') {
            $summary = s(strip_tags(format_text($SITE->summary, FORMAT_HTML)));
            if (!empty($summary)) {
                $output .= "<meta name=\"description\" content=\"$summary\" />\n";
            }
        }

        return $output;
    }
}
