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
 * YIPL.
 *
 * @package    block_yipl
 * @copyright  2025 YIPL
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_yipl\handler\block_handler;
use theme_yipl\handler\course_link_handler;

defined('MOODLE_INTERNAL') || die();

class block_yipl_edit_form extends block_edit_form
{

    protected function specific_definition($mform)
    {

        global $CFG, $PAGE, $COURSE;

        $formdata = $this->_form->exportValues();

        // Section header.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // course_list_title
        $mform->addElement('text', 'config_title', 'Enter Title');
        $mform->setType('config_title', PARAM_TEXT);

        // block type
        $block_yipl_types = block_handler::get_block_yipl_types();
        $options = array(
            'multiple' => false,
            'noselectionstring' => 'Select Block Type',
        );
        $mform->addElement('autocomplete', 'config_block_yipl_type', 'YIPL Block Type', $block_yipl_types, $options);
        $mform->addRule('config_block_yipl_type', get_string('required'), 'required', null, 'client');

        // select courses list when config_block_yipl_type == course_list
        $options = array(
            'multiple' => true,
            'noselectionstring' => "Select Courses",
        );
        $mform->addElement('course', 'config_courselist', get_string('course'), $options);
        $mform->addElement('hidden', 'config_courselist_order', '');
        $mform->setType('config_courselist_order', PARAM_TEXT);

        // meta course info
        $course_fields = block_handler::course_fields_list();
        $options = array(
            'multiple' => true,
            'noselectionstring' => 'Select course fields',
        );
        $mform->addElement('autocomplete', 'config_course_fields', 'Course fields', $course_fields, $options);
        $mform->hideIf('config_course_fields', 'config_block_yipl_type', 'neq', 'course_info');
        // 
        $mform->addElement('hidden', 'config_course_fields_order', '');
        $mform->setType('config_course_fields_order', PARAM_TEXT);

        $layout_type = [
            '' => 'Select',
            'about_course' => "About the Course",
            'learning_Objectives' => "Objectives Points",
            'short_intro' => "Short Intro Information"
        ];
        $mform->addElement('select', 'config_course_fields_layout', "Fields Layout Type", $layout_type);
        $mform->hideIf('config_course_fields_layout', 'config_block_yipl_type', 'neq', 'course_info');

        // contact_us information
        $mform->addElement('static', "contact_us_description", "", "Contact Us data is managed through <a href='/admin/settings.php?section=themesettingyipl#general_setting_tab'> Theme YIPL settings</a>");
        $mform->hideIf('contact_us_description', 'config_block_yipl_type', 'neq', 'contact_us');

        // FAQs information
        $mform->addElement('static', "faqs_description", "", "FAQs must be enable in Theme YIPL settings and FAQs data is managed through <a href='/theme/yipl/pages/faqs/admin.php'> FAQs Settings</a>");
        $mform->hideIf('faqs_description', 'config_block_yipl_type', 'neq', 'faqs');

        // Testimonial information
        $mform->addElement('static', "testimonial_description", "", "Testimonial must be enable in Theme YIPL settings and Testimonial data is managed through <a href='/theme/yipl/pages/testimonial/admin.php'> Testimonial Settings</a>");
        $mform->hideIf('testimonial_description', 'config_block_yipl_type', 'neq', 'testimonial');

        // Testimonial information
        $mform->addElement('static', "start_guideline_description", "", "Start Guideline data is managed through <a href='/admin/settings.php?section=themesettingyipl#frontpage_setting_tab'> Theme YIPL settings</a>");
        $mform->hideIf('start_guideline_description', 'config_block_yipl_type', 'neq', 'start_guideline');

        // Section header layout.
        $mform->addElement('header', 'config_header_layout', "Block Layout");

        //
        if (
            $PAGE->pagetype == 'course-view' &&
            $PAGE->context->contextlevel == CONTEXT_COURSE &&
            $PAGE->context->instanceid == $COURSE->id
        ) {
            $mform->addElement('select', 'config_apply_course_links',  "Apply currect course links with other selected courses. ", [0 => "No", 1 => "Yes"]);
            $mform->hideIf('config_apply_course_links', 'config_block_yipl_type', 'neq', 'course_list');

            $mform->addElement('static', "config_apply_course_links_description", "", "This will also apply \"Course meta link\" enrollment method to all selected course, if Course meta link is enable in site. ");
            $mform->hideIf('config_apply_course_links_description', 'config_block_yipl_type', 'neq', 'course_list');

        }
        $mform->addElement('select', 'config_remove_main_heading', "Remove main heading", [0 => "No", 1 => "Yes"]);
        $mform->addElement('select', 'config_full_width_section', "Full width", [0 => "No", 1 => "Yes"]);

        $block_config_js = file_get_contents($CFG->dirroot . '/blocks/yipl/block-config-form.js');
        $mform->addElement('html', '<script>' . $block_config_js . '</script>');
    }

    // form validation
    function validation($data, $files)
    {
        global $CFG, $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate title.
        // if ($data['title']) {
        //     $data_title = trim($data['title']);
        //     if ($existing = $DB->get_record("", array('title' => $data_title))) {
        //         if (!$data['id'] || $existing->id != $data['id']) {
        //             $errors['title'] = 'Title "' . trim($data['title']) . '" alrady exist.';
        //         }
        //     }
        // }

        return $errors;
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * note: $slashed param removed
     *
     * @return stdClass|null submitted data; NULL if not valid or not submitted or cancelled
     */
    function get_data()
    {
        $data = parent::get_data();
        if ($data) {
            if ($data->config_block_yipl_type == 'course_list') {
                course_link_handler::save_data($data);
                $config_courselist_order = isset($data->config_courselist_order) ? explode(',', $data->config_courselist_order) : [];
                $data->config_courselist = $config_courselist_order;
            }
            if ($data->config_block_yipl_type == 'course_info') {
                $config_course_fields_order = isset($data->config_course_fields_order) ? explode(',', $data->config_course_fields_order) : [];
                $data->config_course_fields = $config_course_fields_order;
            }
        }
        return $data;
    }
}
