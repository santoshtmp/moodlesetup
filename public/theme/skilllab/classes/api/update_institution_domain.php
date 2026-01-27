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

namespace theme_skilllab\api;

use core_external\external_description;
use core_external\external_value;
use core_external\external_format_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use core_external\external_warnings;
use core_user;
use stdClass;

/**
 * User external functions
 *
 * @package    core_user
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class update_institution_domain extends \core_external\external_api
{


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function update_institution_domain_parameters()
    {

        return new external_function_parameters(
            [
                'old_domain' => new external_value(
                    PARAM_RAW,
                    'old domain'
                ),
                'updated_domain' => new external_value(
                    PARAM_RAW,
                    'new updated domain'
                ),

            ]

        );
    }

    /**
     * Update domain
     *
     * @param array $domain
     */
    public static function update_institution_domain($old_domain, $updated_domain)
    {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/user/lib.php");
        require_once($CFG->dirroot . "/user/profile/lib.php"); // Required for customfields related function.
        require_once($CFG->dirroot . '/user/editlib.php');

        // Ensure the current user is allowed to run this function.
        $context = \context_system::instance();
        require_capability('moodle/user:update', $context);
        self::validate_context($context);

        $params = self::validate_parameters(
            self::update_institution_domain_parameters(),
            array(
                'old_domain' => $old_domain,
                'updated_domain' => $updated_domain
            )
        );
        $old_domain = trim($params['old_domain']);
        $updated_domain = trim($params['updated_domain']);
        $result_return = array();
        if ($updated_domain  ==  $old_domain) {
            $result_return['status'] = false;
            $result_return['message'] =  'both old and updated values are same';
            $result_return['exception'] = true;
            return $result_return;
        }

        try {
            $transaction = $DB->start_delegated_transaction();

            $user_info_field = $DB->get_record('user_info_field', ['shortname' => 'institution_domain'], 'id');
            $sql_query = 'SELECT * 
                FROM {user_info_data} user_info_data
                WHERE user_info_data.data = :old_domain AND
                user_info_data.fieldid = :fieldid
            ';
            $sql_params = [
                'old_domain' => $old_domain,
                'fieldid' => $user_info_field->id
            ];

            if ($user_info_field) {
                $user_info_data = $DB->get_records_sql($sql_query, $sql_params);
                if ($user_info_data) {
                    foreach ($user_info_data as $key => $user_info) {
                        $data = new stdClass();
                        $data->id = $user_info->id;
                        $data->data = $updated_domain;
                        $DB->update_record('user_info_data', $data);
                    }
                } else {
                    $result_return['status'] = false;
                    $result_return['message'] =  $old_domain . ' does not exist institution domain';
                    $result_return['exception'] = true;
                    return $result_return;
                }
            }

            $result_return['status'] = true;
            $result_return['message'] = $old_domain . ' institution domain is updated to ' . $updated_domain;

            $transaction->allow_commit();
        } catch (\Exception $e) {
            try {
                $transaction->rollback($e);
            } catch (\Exception $e) {
                $result_return = array();
                $result_return['status'] = false;
                $result_return['message'] =   'something went wrong ..';
                $result_return['exception'] = true;
            }
            $result_return = array();
            $result_return['status'] = false;
            $result_return['message'] =   'something went wrong ..';
            $result_return['exception'] = true;
        }

        return $result_return;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function update_institution_domain_returns()
    {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status'),
                'message' => new external_value(PARAM_RAW, 'message'),
                'exception' => new external_value(PARAM_RAW, 'message', VALUE_OPTIONAL),
            )
        );
    }
}
