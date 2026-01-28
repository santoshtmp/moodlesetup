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
 * Theme functions.
 *
 * @package    theme_boost
 * @copyright  2023 yipl skill lab csc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/skilllab/inc/locallib/_include.php');

if (!function_exists('theme_skilllab_pluginfile')) {
    /**
     * Serves any files associated with the theme settings.
     *
     * @param stdClass $course
     * @param stdClass $cm
     * @param context $context
     * @param string $filearea
     * @param array $args
     * @param bool $forcedownload
     * @param array $options
     * @return bool
     */
    function theme_skilllab_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array())
    {
        $theme = theme_config::load('skilllab');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }

        if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'logo' || $filearea === 'backgroundimage' || $filearea == 'login_backgroundimage'  || $filearea === 'signupbackgroundimage'  || $filearea === 'loginbgimg' || $filearea == 'favicon' || $filearea == 'outcome_image')) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        }

        if ($context->contextlevel == CONTEXT_SYSTEM && $filearea === 'herosection_image') {
            return $theme->setting_file_serve('herosection_image', $args, $forcedownload, $options);
        }

        if ($context->contextlevel == CONTEXT_SYSTEM && preg_match("/^testimonial_image_[1-9][0-9]?$/", $filearea) !== false) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        }

        send_file_not_found();
    }
}

// /**
//  * Post process the CSS tree.
//  *
//  * @param string $tree The CSS tree.
//  * @param theme_config $theme The theme config object.
//  */
// function theme_boost_css_tree_post_processor($tree, $theme) {
//     error_log('theme_boost_css_tree_post_processor() is deprecated. Required' .
//         'prefixes for Bootstrap are now in theme/boost/scss/moodle/prefixes.scss');
//     $prefixer = new theme_boost\autoprefixer($tree);
//     $prefixer->prefix();
// }


if (!function_exists('theme_skilllab_get_extra_scss')) {
    /**
     * Inject additional SCSS.
     *
     * @param theme_config $theme The theme config object.
     * @return string
     */
    function theme_skilllab_get_extra_scss($theme)
    {
        $content = '';
        $imageurl = $theme->setting_file_url('backgroundimage', 'backgroundimage');

        // Sets the background image, and its settings.
        if (!empty($imageurl)) {
            $content .= '@media (min-width: 768px) {';
            $content .= 'body { ';
            $content .= "background-image: url('$imageurl'); background-size: cover;";
            $content .= ' } }';
        }

        // Always return the background image with the scss when we have it.
        return !empty($theme->settings->scss) ? $theme->settings->scss . ' ' . $content : $content;
    }
}


/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_skilllab_get_main_scss_content($theme)
{
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_skilllab', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_skilllab and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // theme_skilllab scss
    // this is loaded AFTER the main scss but before the extra scss from the setting.
    $theme_skilllab_scss = file_get_contents($CFG->dirroot . '/theme/skilllab/scss/skilllab_scss.scss');

    // Combine them together.
    $allscss = $scss . "\n" . $theme_skilllab_scss;

    return $allscss;
}


if (!function_exists('theme_skilllab_get_precompiled_css')) {
    /**
     * Get compiled css.
     *
     * @return string compiled css
     */
    function theme_skilllab_get_precompiled_css()
    {
        global $CFG;
        return file_get_contents($CFG->dirroot . '/theme/skilllab/assets/css/skilllab.css');
    }
}


if (!function_exists('theme_skilllab_get_pre_scss')) {
    /**
     * Get SCSS to prepend.
     *
     * @param theme_config $theme The theme config object.
     * @return array
     */
    function theme_skilllab_get_pre_scss($theme)
    {
        global $CFG;

        $scss = '';
        $configurable = [
            // Config key => [variableName, ...].
            'brandcolor' => ['primary'],
        ];

        // Prepend variables first.
        foreach ($configurable as $configkey => $targets) {
            $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
            if (empty($value)) {
                continue;
            }
            array_map(function ($target) use (&$scss, $value) {
                $scss .= '$' . $target . ': ' . $value . ";\n";
            }, (array) $targets);
        }

        // Prepend pre-scss.
        if (!empty($theme->settings->scsspre)) {
            $scss .= $theme->settings->scsspre;
        }

        return $scss;
    }
}

if (!function_exists('theme_skilllab_get_setting')) {
    function theme_skilllab_get_setting($setting, $format = false)
    {
        $theme = theme_config::load('skilllab');

        if (empty($theme->settings->$setting)) {
            return false;
        }

        if (!$format) {
            return $theme->settings->$setting;
        }

        if ($format === 'format_text') {
            return format_text($theme->settings->$setting, FORMAT_PLAIN);
        }

        if ($format === 'format_html') {
            return format_text($theme->settings->$setting, FORMAT_HTML, array('trusted' => true, 'noclean' => true));
        }

        return format_string($theme->settings->$setting);
    }
}
