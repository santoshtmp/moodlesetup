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
 *
 * @package    theme_yipl
 * @copyright  2025 YIPL
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;

function xmldb_theme_yipl_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();

    /**
     * yipl_testimonial
     */
    $new_version = 2025013105;
    if ($oldversion < $new_version) {

        // Define table to be created.
        $table = new xmldb_table('yipl_testimonial');
        // Adding fields to table yipl_testimonial.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('designation', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('content', XMLDB_TYPE_TEXT, '', null, XMLDB_NOTNULL, null, null);
        $table->add_field('image', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        // Adding keys to table yipl_testimonial.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Conditionally launch create table for yipl_testimonial.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'theme', 'yipl');
    }

    /**
     * yipl_custom_pages
     * */
    $new_version = 2025013107;
    if ($oldversion < $new_version) {
        // Define table to be created.
        $table = new xmldb_table('yipl_custom_pages');
        // Adding fields to table yipl_custom_pages.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('short_name', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('content', XMLDB_TYPE_TEXT, '', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contentformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contentitemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        // Adding keys to table yipl_custom_pages.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Conditionally launch create table for yipl_custom_pages.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'theme', 'yipl');
    }


    $new_version = 2025013109;
    if ($oldversion < $new_version) {
        \theme_yipl\local\custom_fields::add_course_customfields();
    }


    /**
     * yipl_course_link table
     */
    $new_version = 2025022700;
    if ($oldversion < $new_version) {
        // Define table to be created.
        $table = new xmldb_table('yipl_course_link');
        // Adding fields to table yipl_course_link.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('other_courses', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        // Adding keys to table yipl_course_link.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Conditionally launch create table for yipl_course_link.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'theme', 'yipl');
    }

    /**
     * 
     */
    return true;
}
