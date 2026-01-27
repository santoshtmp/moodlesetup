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
 * AMD module
 * change cour login url to theme logout url
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'],
    function ($) {

        return {
            init: function () {

                // all the a tag link which contain core logout url change to theme logout url
                var core_logout_url = $('a[href*="login/logout.php"]').attr('href');
                if (core_logout_url) {
                    let flag = core_logout_url.includes("login/logout.php");
                    if (flag) {
                        let new_logout_url = core_logout_url.replace("login/logout.php", "logout");
                        $('a[href*="login/logout.php"]').attr('href', new_logout_url);
                    }
                }

                // all the form action which contain core logout url change to theme logout url
                var core_logout_action_url = $('form[action*="login/logout.php"]').attr('action');
                if (core_logout_action_url) {
                    let flag_action = core_logout_action_url.includes("login/logout.php");
                    if (flag_action) {
                        let new_logout_url = core_logout_action_url.replace("login/logout.php", "logout");
                        $('form[action*="login/logout.php"]').attr('action', new_logout_url);
                    }
                }
            }
        };

    }
);
