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
        return {
            init: function (e, student_user) {

                /// for pop up
                // document.addEventListener("DOMContentLoaded", function () {
                const continueBtn = document.querySelector("#student-pop-up-continue");
                const studentPopup = document.querySelector("#popup-csc");
                const studentBackDrop = document.querySelector(".student-popup-backdrop");
                const popUpContainer = document.querySelector(".student-info-popup");
                const crossButton = document.querySelector("#cross-student-popup");

                /**
                 *
                 */
                function handleClosePopup() {
                    studentPopup?.classList.remove("active-popup");
                }
                studentBackDrop.addEventListener("click", handleClosePopup);
                popUpContainer.addEventListener("click", (ee) => {
                    ee.stopPropagation();
                });
                crossButton.addEventListener("click", handleClosePopup);
                continueBtn.addEventListener("click", handleClosePopup);
                // });
                // pop-up-end

                let interval = setInterval(function () {
                    ($(e).find('a')).removeClass('cm-items-csc');
                    var top = $(e).parent().parent().parent();
                    var section = $(e).parent().parent();
                    if (top.attr('id')) {
                        const section_id = (section.attr('id')).replace(/[^0-9]/g, "");
                        $('#courseindexsection' + section_id + ' a').removeClass(' collapsed ');
                        section.addClass(' show ');
                        if (student_user == 1) {
                            $('.locked-cm').click(function () {
                                $('#popup-csc').addClass(' active-popup ');
                                return false;
                            });

                            setTimeout(function () {
                                var complete_cm = $('#course-index div .courseindex-item-content ul li');
                                complete_cm = Object.values(complete_cm);
                                var i = 0;
                                for (i; i < complete_cm.length; i++) {
                                    var content = $(complete_cm[i].innerHTML);
                                    if (content.hasClass('completion_complete')) {
                                        var data_id = $(content[2]).attr('id');
                                        var a_point = $('#' + data_id);
                                        a_point.removeClass('cm-items-csc');
                                    }
                                }
                                $('.cm-items-csc').click(function () {
                                    var complete = $('.activity-complete .completion-info button').text();
                                    if ($.trim(complete) == 'Mark as done') {
                                        $('#popup-csc').addClass(' active-popup ');
                                        return false;
                                    }
                                });
                            }, 1000);
                        }
                        clearInterval(interval);
                    }
                }, 1000);

                if (student_user == 1) {
                    $('#next-activity-link').click(function () {
                        var complete = $('.activity-complete .completion-info button').text();
                        if ($.trim(complete) == 'Mark as done') {
                            $('#popup-csc').addClass(' active-popup ');
                            return false;
                        }
                    });
                }


            }
        };

    }
);
