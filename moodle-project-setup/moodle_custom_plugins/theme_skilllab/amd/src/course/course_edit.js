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
        // to delete or remove core coure edit form section area
        var delete_elements = [
            $('#fitem_id_visible'),
            $('#fitem_id_startdate'),
            $('#fitem_id_enddate'),
            $('#fitem_id_idnumber'),
            $('#id_appearancehdr'),
            $('#id_filehdr'),
            $('#id_completionhdr'),
            $('#id_groups'),
            $('#id_rolerenaming'),
            $('#id_tagshdr'),
            $('#fitem_id_hiddensections'),
            $('#fitem_id_coursedisplay')
        ];

        return {
            init: function () {

                $.each(delete_elements, function (index, value) {
                    value.remove();
                });

                // invalid error
                $("#id_customfield_course_duration").on("change", function () {
                    /**
                     *
                    */
                    function overried_msg() {
                        if ($("#id_customfield_course_duration").hasClass("is-invalid")) {
                            var child_append = '<div class="form-control-feedback invalid-feedback" ' +
                                'id="id_error_customfield_course_duration_skl" style="display: block;">' +
                                'Course duration is required.</div>';
                            $("#id_customfield_course_duration").parent().append(child_append);
                        } else {
                            $('#id_error_customfield_course_duration_skl').remove();
                        }
                    }
                    window.setTimeout(overried_msg, 40); // 0.04 seconds
                });

                if ($("#id_customfield_course_type").hasClass("is-invalid")) {
                    var child_append = '<div class="form-control-feedback invalid-feedback" ' +
                        'id="id_error_customfield_course_type_skl" style="display: block;">' +
                        'Course type is required.</div>';
                    $("#id_customfield_course_type").parent().append(child_append);
                }

                if ($("#id_customfield_skill_level").hasClass("is-invalid")) {
                    var child_append = '<div class="form-control-feedback invalid-feedback" ' +
                        'id="id_error_customfield_skill_level_skl" style="display: block;">' +
                        'Skill level is required.</div>';
                    $("#id_customfield_skill_level").parent().append(child_append);
                }

                if ($("#id_customfield_course_duration").hasClass("is-invalid")) {
                    var child_append = '<div class="form-control-feedback invalid-feedback" ' +
                        'id="id_error_customfield_course_duration_skl" style="display: block;">' +
                        'Course duration is required.</div>';
                    $("#id_customfield_course_duration").parent().append(child_append);
                }

            }
        };

    }
);
