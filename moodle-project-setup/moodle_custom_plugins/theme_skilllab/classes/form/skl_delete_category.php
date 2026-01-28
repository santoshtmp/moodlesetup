<?php

namespace theme_skilllab\form;

use html_writer;

class skl_delete_category extends \core_course_deletecategory_form
{

    /**
     * Defines the form.
     */
    public function definition()
    {
        $mform = $this->_form;
        $this->coursecat = $this->_customdata;

        // $categorycontext = \context_coursecat::instance($this->coursecat->id);
        $categoryname = $this->coursecat->get_formatted_name();

        // Check permissions, to see if it OK to give the option to delete
        // the contents, rather than move elsewhere.
        $candeletecontent = $this->coursecat->can_delete_full();

        // Get the list of categories we might be able to move to.
        $displaylist = $this->coursecat->move_content_targets_list();

        // Now build the options.
        $options = array();
        if ($displaylist) {
            $options[0] = get_string('movecontentstoanothercategory');
        }
        if ($candeletecontent) {
            $options[1] = get_string('deleteallcannotundo');
        }
        if (empty($options)) {
            throw new \moodle_exception('youcannotdeletecategory', 'error', 'index.php', $categoryname);
        }

        // Now build the form.
        // $mform->addElement('header', 'general', get_string('categorycurrentcontents', '', $categoryname));

        // // Describe the contents of this category.
        $contents = '';
        if ($this->coursecat->has_children()) {
            $contents .= html_writer::tag('li', get_string('subcategories'));
        }
        if ($this->coursecat->has_courses()) {
            $contents .= html_writer::tag('li', get_string('courses'));
        }
        // if (question_context_has_any_questions($categorycontext)) {
        //     $contents .= html_writer::tag('li', get_string('questionsinthequestionbank'));
        // }

        // // Check if plugins can provide more info.
        // $pluginfunctions = $this->coursecat->get_plugins_callback_function('get_course_category_contents');
        // foreach ($pluginfunctions as $pluginfunction) {
        //     if ($plugincontents = $pluginfunction($this->coursecat)) {
        //         $contents .= html_writer::tag('li', $plugincontents);
        //     }
        // }

        // if (!empty($contents)) {
        //     $mform->addElement('static', 'emptymessage', get_string('thiscategorycontains'), html_writer::tag('ul', $contents));
        // } else {
        //     $mform->addElement('static', 'emptymessage', '', get_string('deletecategoryempty'));
        // }

        $label_full_delete = '';
        if (!empty($contents)) {
            $label_full_delete = 'Choose an action';
        } else {
            $options[1] = '';
        }
        // Give the options for what to do.
        $mform->addElement('select', 'fulldelete', $label_full_delete, $options);
        if (count($options) == 1) {
            // Freeze selector if only one option available.
            $optionkeys = array_keys($options);
            $option = reset($optionkeys);
            $mform->hardFreeze('fulldelete');
            $mform->setConstant('fulldelete', $option);
        }

        if ($displaylist) {
            $mform->addElement('autocomplete', 'newparent', 'Move content to', $displaylist);
            if (in_array($this->coursecat->parent, $displaylist)) {
                $mform->setDefault('newparent', $this->coursecat->parent);
            }
            $mform->hideIf('newparent', 'fulldelete', 'eq', '1');
        }


        $mform->addElement('hidden', 'categoryid', $this->coursecat->id);
        $mform->setType('categoryid', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'action', 'deletecategory');
        $mform->setType('action', PARAM_ALPHANUM);

        $this->add_action_buttons(true, get_string('delete'));
    }
}