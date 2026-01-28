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

require_once($CFG->libdir . '/formslib.php');

/**
 *
 * @package    theme_yipl
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class faqs_question_form extends \moodleform
{
    // table name
    protected static $faq_table = 'yipl_faq';
    protected static $faq_category_table = 'yipl_faq_category';

    // define form
    public function definition()
    {
        global $CFG, $DB;
        $context =  \context_system::instance();
        $mform = $this->_form;
        // FAQ Title 
        $mform->addElement('text', 'title', get_string('faq_title', 'theme_yipl'), ['size' => 70]);
        $mform->addRule('title', 'Default faq title', 'required', null, 'client');
        $mform->addHelpButton('title', 'faq_title', 'theme_yipl');
        $mform->setType('title', PARAM_TEXT);

        // FAQ Category
        $all_faq_category = $DB->get_records(self::$faq_category_table);
        if ($all_faq_category) {
            $areanames = [];
            foreach ($all_faq_category as $key => $faq_category) {
                $areanames[$faq_category->id] = $faq_category->fullname;
            }
            $options = array(
                'multiple' => true,
                'noselectionstring' => get_string('search_faq_category', 'theme_yipl'),
            );
            $mform->addElement('autocomplete', 'faq_category', get_string('faq_category', 'theme_yipl'), $areanames, $options);
        }

        // FAQ Content
        $editor_options = array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $CFG->maxbytes,
            'trusttext' => true,
            'noclean' => true,
            'context' => $context,
            'subdirs' => false
        );
        $attr = ' placeholder=""  cols="20" class="theme-yipl-faq-textarea" rows="15" ';
        $mform->addElement('editor', 'content', get_string('faq_content', 'theme_yipl'), $attr, $editor_options);
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', '', 'required', null, 'client');
        $mform->addHelpButton('content', 'faq_content', 'theme_yipl');

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
        if ($data['title']) {
            $data_title = trim($data['title']);
            if ($existing = $DB->get_record(self::$faq_table, array('title' => $data_title))) {
                if (!$data['id'] || $existing->id != $data['id']) {
                    $errors['title'] = 'FAQ "' . trim($data['title']) . '" alrady exist.';
                }
            }
        }

        return $errors;
    }

    // ----- END -----
}
