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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'],
    function ($) {

        return {
            init: function () {
                if ($('.preloader')) {
                    // window.console.log('preloader----');
                }
                // --------- pre-loader start ---------
                // window.addEventListener("load", function () {
                    const preloader = document.querySelector(".preloader");
                    //   preloader.style.display = "none";
                    if (preloader) {
                        setTimeout(function () {
                            // preloader.style.display = 'none';
                            preloader.remove();
                        }, 100);
                    }
                // });
                // --------- pre-loader end ---------
            }
        };

    }
);
