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
 *
 * @copyright 2025 YIPL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * https://moodledev.io/docs/guides/javascript/jquery
 * https://moodledev.io/docs/guides/javascript
 *
 */

import $ from 'jquery';
import Ajax from 'core/ajax';

const x_time = 15;
var idleTimer = null;
var idleStatus = false;

export const init = (page_data) => {
    //
    // on_load_send_pendingDuration();
    // window.console.log("  ::: start :::  ");
    // call set_duration when go to next link page
    $(window).on("beforeunload", function () {
        set_duration(page_data);
    });
    // execute
    idleTimer = setTimeout(function () {
        set_duration(page_data);
    }, x_time * 1000);

    //
    $('*').on('mousemove mouseover click keydown keypress keyup mouseenter scroll resize dblclick',
        function () {
            //
            clearTimeout(idleTimer);
            if (idleStatus) {
                idleStatus = false;
                reset_start_time(page_data);
            }
            // if passive or in active in page for x_time seconds
            idleTimer = setTimeout(function () {
                set_duration(page_data);
            }, x_time * 1000);
        }
    );

};

/**
 *
 * @param {*} page_data
 */
function set_duration(page_data) {
    // var duration = parseInt((endTime - startTime) / 1000);// duration in seconds
    let request = {
        methodname: 'yipl_timetrack',
        args: {
            user_id: parseInt(page_data['user_id']),
            course_id: parseInt(page_data['course_id']),
            cmod_id: parseInt(page_data['cmod_id']),
            option: 1
        }
    };
    let ajax = Ajax.call([request])[0];
    ajax.done(function (response) {
        if (response.status) {
            // window.console.log('time duration save.');
        } else {
            // window.console.log('something is wrong during saving user time duration...');
        }
    });
    // ajax.fail(function () {
    //     window.console.log('failed...');
    // });
    // ajax.always(function (response) {
    //     window.console.log(response);
    // });
    //
    idleStatus = true;


}

/**
 *
 * @param {*} page_data
 */
function reset_start_time(page_data) {
    let request = {
        methodname: 'yipl_timetrack',
        args: {
            user_id: parseInt(page_data['user_id']),
            course_id: parseInt(page_data['course_id']),
            cmod_id: parseInt(page_data['cmod_id']),
            option: 2
        }
    };
    Ajax.call([request]);
    // let ajax = Ajax.call([request])[0];
    // ajax.always(function (response) {
    //     window.console.log(response);
    // });
}