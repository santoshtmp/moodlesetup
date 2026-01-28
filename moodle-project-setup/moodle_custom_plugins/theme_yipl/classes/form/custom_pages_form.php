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
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



namespace theme_yipl\form;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

use moodle_url;
use stdClass;

require_once($CFG->libdir . '/formslib.php');

/**
 *
 * @package    theme_yipl
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_pages_form extends \moodleform
{
    // table name
    protected static $custom_pages_table = 'yipl_custom_pages';

    // define form
    public function definition()
    {
        global $CFG, $DB;
        $context =  \context_system::instance();
        $mform = $this->_form;
        // Title 
        $mform->addElement('text', 'title', "Title", ['size' => 70]);
        $mform->addRule('title', 'Default title', 'required', null, 'client');
        $mform->setType('title', PARAM_TEXT);

        // Title 
        $mform->addElement('text', 'short_name', "Short name", ['size' => 70]);
        $mform->addRule('short_name', 'Default short name', 'required', null, 'client');
        $mform->setType('short_name', PARAM_TEXT);

        // Content
        $editor_options = array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $CFG->maxbytes,
            'trusttext' => true,
            'noclean' => true,
            'context' => $context,
            'subdirs' => false
        );
        $attr = ' placeholder=""  cols="20" class="custom-pages-textarea" rows="15" ';
        $mform->addElement('editor', 'content', "Content", $attr, $editor_options);
        $mform->setType('content', PARAM_RAW);

        // Draft or publish state
        $mform->addElement('checkbox', 'status', "Status", "published");
        $mform->setDefault('status', 1);

        // $this->add_action_buttons();
        $classarray = array('class' => 'form-submit');
        $buttonarray = [
            // $mform->createElement('cancel', 'returnback', 'Return Back', $classarray),
            $mform->createElement('submit', 'submitbutton', get_string('savechanges'), $classarray),
            $mform->createElement('cancel'),
        ];
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');


        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', '');
    }

    // form validation
    function validation($data, $files)
    {
        global $CFG, $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate FAQ title.
        if ($data['short_name']) {
            $data_short_name = trim($data['short_name']);
            if ($existing = $DB->get_record(self::$custom_pages_table, array('short_name' => $data_short_name))) {
                if (!$data['id'] || $existing->id != $data['id']) {
                    $errors['short_name'] = 'Page short name "' . trim($data['short_name']) . '" alrady exist.';
                }
            }
        }

        return $errors;
    }

    // ----- END -----
}
