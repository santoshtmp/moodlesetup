<?php

namespace theme_skilllab\form;

use html_writer;

class skl_edit_category extends \core_course_editcategory_form
{

    /**
     * Validates the data submit for this form.
     *
     * @param array $data An array of key,value data pairs.
     * @param array $files Any files that may have been submit as well.
     * @return array An array of errors.
     */
    public function validation($data, $files)
    {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['idnumber'])) {
            if ($existing = $DB->get_record('course_categories', array('idnumber' => $data['idnumber']))) {
                if (!$data['id'] || $existing->id != $data['id']) {
                    $errors['idnumber'] = get_string('categoryidnumbertaken', 'error');
                }
            }
        }
        if ($data['name']) {
            if ($existing = $DB->get_record('course_categories', array('name' => trim($data['name'])))) {
                $errors['name'] = 'Category name "' . trim($data['name']) . '" is alrady taken';
            }
        }
        return $errors;
    }
}
