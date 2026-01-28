/**
 * https://moodledev.io/docs/guides/javascript/jquery
 * https://moodledev.io/docs/guides/javascript
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import $ from 'jquery';
// import Ajax from 'core/ajax';


export const init = () => {

    // $('.course-record a').on('click', function () {
    //     const course_id = $(this).attr('data-id');
    //     getXMLHttpRequestData('/theme/skilllab/pages/report/time_track.php?course_id=' + course_id);
    //     return false;
    // });

    // $('.course-record a').on('click', function () {
    //     const courseid = $(this).attr('data-id');
    //     ajax_call(courseid);
    //     return false;
    // });

};

// /**
//  *
//  * @param {*} courseid
//  */
// function ajax_call(courseid) {
//     const table_section_area = $('.course-duration-info');

//     var ajax_resp = Ajax.call([{
//         methodname: 'skilllab_time_track_report',
//         args: {
//             table: true,
//             course_id: parseInt(courseid)
//         }
//     }])[0];

//     ajax_resp.done(function (response) {
//         var template_html = response['template_html'];
//         if (table_section_area) {
//             table_section_area.html(template_html);
//         } else {
//             $('.time-track-container').append(template_html);
//         }
//     }).fail(function (ex) {
//         window.console.log('------------ ERROR ------------');
//         window.console.log(ex);
//     });
//     window.console.log('---------end-------' + courseid);
// }

// /**
//  * get server response
//  * @param {URL} submissionLink
//  * @returns
//  */
// function getXMLHttpRequestData(submissionLink) {
//     const table_section_area = $('.course-duration-info');
//     table_section_area.css('opacity', '0.6');
//     var get_response = $.post(submissionLink + '&table_only=true', '', function (data, status) {
//         if (status === 'success') {
//             var html_data = data;
//             if(table_section_area){
//                 table_section_area.html(html_data);
//             }else{
//                 window.console.log('can not find section to display output ');
//             }
//             window.history.replaceState("", "url", submissionLink);
//         } else {
//             window.console.log('!!! error on loading course list table ... ');
//         }
//     });
//     get_response.fail(function () {
//         const failed_data = "<p class='failed-to-load'>Fail on loading data try again.</p>";
//         table_section_area.html(failed_data);
//     });
//     get_response.done(function () {
//         table_section_area.css('opacity', '1');
//     });
//     return get_response;
// }