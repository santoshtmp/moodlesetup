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
 * @package    theme_skilllab
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_theme_skilllab_upgrade($oldversion)
{
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $new_version = 2023042405.6;
    if ($oldversion < $new_version) {
        // Define table skl_custom_course_field to be created.
        $table = new xmldb_table('skl_custom_course_field');

        // Adding fields to table skl_custom_course_field.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('skill_level', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_duration', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table skl_custom_course_field.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course', XMLDB_KEY_FOREIGN_UNIQUE, array('courseid'), 'course', array('id'));

        // Conditionally launch create table for skl_custom_course_field.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'theme', 'skilllab');
    }

    $new_version = 2023042406;
    if ($oldversion < $new_version) {
        // Define table skl_custom_course_field to be created.
        $table = new xmldb_table('skl_course_role_count');

        // Adding fields to table skl_custom_course_field.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('role', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('count', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table skl_custom_course_field.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for skl_custom_course_field.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'theme', 'skilllab');
    }

    $new_version = 2023042408.9;
    if ($oldversion < $new_version) {
        // Define table skl_custom_course_field to be created.
        $table = new xmldb_table('skl_user_course_progress');

        // Adding fields to table skl_custom_course_field.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('progress_status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table skl_custom_course_field.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for skl_custom_course_field.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table skl_time_track to be created.
        $table = new xmldb_table('skl_time_track');

        // Adding fields to table skl_time_track.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cmod_id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);


        // Adding keys to table skl_time_track.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'course', array('id'));


        // Conditionally launch create table for skl_time_track.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'theme', 'skilllab');
    }


    $new_version = 2023042410.3;
    if ($oldversion < $new_version) {
        // Define table skl_quiz_lock_course to be created.
        $table = new xmldb_table('skl_quiz_lock_course');
        // Adding fields to table skl_quiz_lock_course.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        // Adding keys to table skl_quiz_lock_course.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Conditionally launch create table for skl_quiz_lock_course.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'theme', 'skilllab');
    }


    $new_version = 2024121603;
    if ($oldversion < $new_version) {
        // //
        \theme_skilllab\local\skl_time_track::delete_time_track_cmod_id_zero();
        // //
        // \theme_skilllab\local\skl_time_track::manage_old_negative_time_track();
        // //
        // // Define table skl_time_track to be created.
        $table = new xmldb_table('skl_time_track');
        $field_sync_csc = new xmldb_field('sync_csc', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'); 
        // $field_sync_csc = new xmldb_field('sync_csc', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');        // Arguments: (field name, type, precision, unsigned, nullability, default value, previous field)
        if (!$dbman->field_exists($table, $field_sync_csc)) {
            $dbman->add_field($table, $field_sync_csc);
        }
        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'theme', 'skilllab');
    }
    return true;
}
