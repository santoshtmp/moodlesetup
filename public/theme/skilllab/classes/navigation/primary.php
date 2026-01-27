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

namespace theme_skilllab\navigation;

use renderable;
use renderer_base;
use templatable;
use custom_menu;
use theme_skilllab\util\UtilUser_handler;

/**
 * Primary navigation renderable
 *
 * This file combines primary nav, custom menu, lang menu and
 * usermenu into a standardized format for the frontend
 *
 * @package     core
 * @category    navigation
 * @copyright   2021 onwards Peter Dias
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class primary extends \core\navigation\output\primary
{

    /**
     * Combine the various menus into a standardized output.
     *
     * @param renderer_base|null $output
     * @return array
     */
    public function export_for_template(?renderer_base $output = null): array
    {
        $parent_output = parent::export_for_template($output);

        foreach ($parent_output as $key => $moremenu) {
            if ($key == 'moremenu') {
                foreach ($moremenu as $key => $nodearray) {
                    if ($key == 'nodearray') {
                        foreach ($nodearray as $key => $val) {
                            if (is_array($val)) {
                                if (
                                    // ($val['key'] == 'home') or
                                    ($val['key'] == 'siteadminnode') &&
                                    in_array('admin',  UtilUser_handler::get_user_roles())
                                ) {
                                    $siteadminnode = $val;
                                }
                            } else {
                                $new_nodearray[] = $val;
                            }
                        }
                    }
                }
            }
        }

        $new_nodearray[] = (isset($siteadminnode)) ? $siteadminnode : '';
        $parent_output['moremenu']['nodearray'] = $new_nodearray;
        return $parent_output;
    }

    /**
     * 
     */
    public function skl_mobile_primarynav($primarymenu)
    {

        $new_mobile_pnav = [];
        if ($primarymenu) {
            foreach ($primarymenu as $key => $val) {
                if (is_array($val)) {
                    if (
                        ($val['key'] == 'siteadminnode') &&
                        in_array('admin',  UtilUser_handler::get_user_roles())
                    ) {
                        $new_mobile_pnav[] = $val;
                    }
                } else {
                    $new_mobile_pnav[] = $val;
                }
            }
        }

        // $primarymenu = 
        return $new_mobile_pnav;
    }

    /**
     * Custom menu items reside on the same level as the original nodes.
     * Fetch and convert the nodes to a standardised array.
     *
     * @param renderer_base $output
     * @return array
     */
    protected function get_custom_menu(renderer_base $output): array
    {
        global $CFG, $USER;

        // Early return if a custom menu does not exists.
        // if (empty($CFG->custommenuitems)) {
        //     return [];
        // }

        $nodes = [];
        $currentlang = current_language();
        // default common menu itesm
        $custommenuitems = $CFG->custommenuitems;
        $custommenunodes = custom_menu::convert_text_to_menu_nodes($custommenuitems, $currentlang);
        foreach ($custommenunodes as $node) {
            $nodes[] = $node->export_for_template($output);
        }

        $context_sys = \context_system::instance();
        // menu items for admin
        $role_shortname = UtilUser_handler::get_user_roles();
        if (in_array('admin', $role_shortname)) {
            $menuitems_admin = get_config('theme_skilllab')->menuitems_admin;
            $custommenunodes = custom_menu::convert_text_to_menu_nodes($menuitems_admin, $currentlang);
            foreach ($custommenunodes as $node) {
                $nodes[] = $node->export_for_template($output);
            }
        } else {
            // menu itesms for auth users or not admin
            if (!isguestuser() || $USER->id > 2) {
                $menuitems_student = get_config('theme_skilllab')->menuitems_student;
                $custommenunodes = custom_menu::convert_text_to_menu_nodes($menuitems_student, $currentlang);
                foreach ($custommenunodes as $node) {
                    $nodes[] = $node->export_for_template($output);
                }
            }
        }

        return $nodes;
    }
}
