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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'],
    function ($) {
        /**
         * variables
         */
        var course_list_table = $('#course-list-table'),
            filter_form = $('#course-filter-form'),
            submit_form = $('#filter-submit'),
            clear_form = $('#filter-clear'),
            form_action_url = filter_form.attr('action');

        /**
         * table pagination number click action
        */
        function table_pagination_number_click() {
            $('#course-list-table nav ul li a').click(function () {
                var submissionLink = $(this).attr("href");
                getXMLHttpRequestData(submissionLink);
                return false;
            });
        }

        /**
         * table header sort click action
        */
        function table_header_sort_click() {
            $('#course-list-table .skl-courses-table thead th a').click(function () {
                var header_sort_url = $(this).attr("href");
                var form_data = filter_form.serialize();
                var submissionLink = header_sort_url + '&' + form_data;
                getXMLHttpRequestData(submissionLink);
                show_clear_form_btn(form_data);
                return false;
            });
        }

        /**
         * apply filter form submit click action
        */
        function submit_form_click() {
            submit_form.click(function () {
                var form_data = filter_form.serialize();
                var submissionLink = form_action_url + '?' + form_data + '&treset=1';
                var form_data_each = form_data.split('&');
                $.each(form_data_each, function (index, value) {
                    if ((value.split('='))[1]) {
                        var response_obj = getXMLHttpRequestData(submissionLink);
                        response_obj.done(function () {
                            clear_form.removeClass('skilllab_hide');
                        });
                    }
                });
            });
        }

        /**
         * clear filter click action
        */
        function clear_form_click() {
            clear_form.click(function () {
                filter_form[0].reset();
                $('#search_input').val('');
                var submissionLink = form_action_url + '?treset=1';
                var response_obj = getXMLHttpRequestData(submissionLink);
                response_obj.done(function () {
                    clear_form.addClass('skilllab_hide');
                });
            });
        }

        /**
         * track course_category change on filter
         * then change add course category id
        */
        function track_course_category() {
            $('#course-category-filter').on("change", function () {
                var new_category = $('#course-category-filter').val();
                var add_course_link = $('#btn-add-course').attr('href');
                if (new_category) {
                    add_course_link = add_course_link.split('category')[0] + 'category=' + new_category;
                    $('#btn-add-course').attr('href', add_course_link);
                } else {
                    add_course_link = add_course_link.split('category')[0] + 'category=0';
                    $('#btn-add-course').attr('href', add_course_link);
                }
            });
        }

        /**
         * get server response
         * @param {URL} submissionLink
         * @returns
         */
        function getXMLHttpRequestData(submissionLink) {
            course_list_table.css('opacity', '0.6');
            var get_response = $.post(submissionLink + '&table_only=true', '', function (data, status) {
                if (status === 'success') {
                    // // var htmlDoc = $(get_response.responseText);
                    // var htmlDoc = $(data);
                    // window.console.log(htmlDoc);
                    // var html_data = htmlDoc.find('#course-list-table').html();
                    // window.console.log(submissionLink + '&table_only=true');
                    var html_data = data;
                    course_list_table.html(html_data);
                    window.history.replaceState("", "url", submissionLink);
                } else {
                    window.console.log('!!! error on loading course list table ... ');
                }
            });
            get_response.fail(function () {
                const failed_data = "<p class='failed-to-load'>Fail on loading data try again.</p>";
                course_list_table.html(failed_data);
            });
            get_response.done(function () {
                course_list_table.css('opacity', '1');
                single_clear_filter_item();
                table_header_sort_click();
                table_pagination_number_click();
            });
            return get_response;
        }

        /**
         * check if the form has at least one value or not
         * then show or hide clear form btn
         * @param {JSON} form_data
         */
        function show_clear_form_btn(form_data) {
            var form_data_each = form_data.split('&');
            var check = false;
            $.each(form_data_each, function (index, value) {
                if ((value.split('='))[1]) {
                    check = true;
                }
            });
            if (check) {
                clear_form.removeClass('skilllab_hide');
            } else {
                clear_form.addClass('skilllab_hide');
            }
            return check;
        }

        /**
         *
         */
        function single_clear_filter_item() {
            const clear_filter_item = $('.clear-filter-item');
            const filter_form = $('#course-filter-form');

            clear_filter_item.click(function () {
                var data_id = ($(this).parent()).attr('data-id');
                if ($('#' + data_id).attr('type') == "checkbox") {
                    $('#' + data_id).prop("checked", false);
                } else {
                    $('#' + data_id).val('');
                }
                var form_data = filter_form.serialize();
                var submissionLink = form_action_url + '?' + form_data + '&treset=1';

                if (show_clear_form_btn(form_data)) {
                    getXMLHttpRequestData(submissionLink);
                } else {
                    getXMLHttpRequestData(form_action_url + '?treset=1');
                }
            });
        }


        /**
         *
         */
        function search_enter() {
            $('#search_input').keypress(function (event) {
                var keycode = (event.keyCode ? event.keyCode : event.which);
                if (keycode == '13') {
                    var form_data = filter_form.serialize();
                    var submissionLink = form_action_url + '?' + form_data + '&treset=1';
                    var form_data_each = form_data.split('&');
                    $.each(form_data_each, function (index, value) {
                        if ((value.split('='))[1]) {
                            var response_obj = getXMLHttpRequestData(submissionLink);
                            response_obj.done(function () {
                                clear_form.removeClass('skilllab_hide');
                            });

                        }
                    });
                    return false;
                }
            });
        }


        return {
            init: function () {
                // pagination page number link click
                table_pagination_number_click();

                // table head link sort click
                table_header_sort_click();

                // perform submit apply filter action on click
                submit_form_click();

                // perform clear filter action on click
                clear_form_click();

                // change course category on change
                track_course_category();

                // single filter item clear
                single_clear_filter_item();

                search_enter();

            }
        };

    }
);
