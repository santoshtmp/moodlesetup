/**
 * https://moodledev.io/docs/guides/javascript/jquery
 * https://moodledev.io/docs/guides/javascript
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        methodname: 'skilllab_time_track',
        args: {
            user_id: parseInt(page_data['user_id']),
            course_id: parseInt(page_data['course_id']),
            cmod_id: parseInt(page_data['cmod_id'])
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
        methodname: 'skilllab_time_track_reset_start_time',
        args: {
            user_id: parseInt(page_data['user_id']),
            course_id: parseInt(page_data['course_id']),
            cmod_id: parseInt(page_data['cmod_id'])
        }
    };
    Ajax.call([request]);
    // let ajax = Ajax.call([request])[0];
    // ajax.always(function (response) {
    //     window.console.log(response);
    // });
}

// /**
//  *
//  */
// function on_load_send_pendingDuration() {
//     // On page load, process any pending data
//     $(window).on("load", function () {
//         const pendingData = localStorage.getItem('pendingDuration');
//         if (pendingData) {
//             const data = JSON.parse(pendingData);
//             Ajax.call([{
//                 methodname: 'skilllab_time_track',
//                 args: data
//             }]);
//             localStorage.removeItem('pendingDuration');
//         }
//     });
// }