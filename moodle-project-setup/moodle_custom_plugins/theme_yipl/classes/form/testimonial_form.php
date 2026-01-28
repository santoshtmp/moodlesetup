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
class testimonial_form extends \moodleform
{
    // table name
    protected static $testimonial_table = 'yipl_testimonial';

    // define form
    public function definition()
    {
        global $CFG, $DB;
        $context =  \context_system::instance();
        $mform = $this->_form;
        // Testimonial Title 
        $mform->addElement('text', 'name', "Person Name", ['size' => 70]);
        $mform->addRule('name', 'Testimonial title', 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        // Testimonial Title 
        $mform->addElement('text', 'designation', "Designation", ['size' => 70]);
        $mform->setType('designation', PARAM_TEXT);

        // Testimonial content 
        $mform->addElement('textarea', 'content', "Content");
        $mform->addRule('content', 'Testimonial content', 'required', null, 'client');
        $mform->setType('content', PARAM_TEXT);

        // Testimonial image
        $testimonial_image_options = [
            'maxfiles' => 1,
            'maxbytes' => $CFG->maxbytes,
            'trusttext' => true,
            'noclean' => true,
            'context' => $context,
            'subdirs' => false,
            'accepted_types' => ['image']
        ];
        $mform->addElement('filemanager', 'testimonial_image', "Image", null, $testimonial_image_options);

        // Draft or publish state
        $mform->addElement('checkbox', 'status', "Status", "published");
        $mform->setDefault('status', 1);


        // $this->add_action_buttons();
        $classarray = array('class' => 'form-submit');
        $buttonarray = [
            // $mform->createElement('cancel', 'returnback', 'Return Back', $classarray),
            $mform->createElement('submit', 'submitbutton', get_string('savechanges'), $classarray),
            $mform->createElement('cancel', "cancelbutton", "Cancel and Return back."),
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


        return $errors;
    }

    // ----- END -----
}
