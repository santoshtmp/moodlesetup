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
 * @package   theme_skilllab
 * @copyright 2023 yipl skill lab csc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingskilllab', get_string('configtitle', 'theme_skilllab'));
    \theme_skilllab\form\skl_settings::general_setting($settings);
    \theme_skilllab\form\skl_settings::skl_advance_style($settings);
    \theme_skilllab\form\skl_settings::skl_frontpagesettings($settings);
    \theme_skilllab\form\skl_settings::skl_api($settings);
}
