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
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use stdClass;

/**
 * User external functions
 *
 * @package    core_user
 * @category   external
 * @copyright  2023 yipl skill lab csc 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_logout extends \core_external\external_api
{
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.9
     */
    public static function user_logout_parameters()
    {
        return new external_function_parameters(
            array(
                'userid' => new external_value(
                    PARAM_RAW,
                    'id of the user'
                )
            )
        );
    }

    /**
     * return list of user enrolled in course as studnet
     * @param int $courseid id of course
     * @return array of course participatient as student role result
     */
    public static function user_logout($userid)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/theme/skilllab/lib.php');

        $params = self::validate_parameters(
            self::user_logout_parameters(),
            array(
                'userid' => $userid
            )
        );

        $userid = $params['userid'];
        $userid = (int)decrypt($userid);

        $sql_query = 'SELECT *
            FROM {sessions} sessions 
            WHERE sessions.userid = :userid 
            Order By sessions.id DESC
        ';
        $sql_params = [
            'userid' => $userid
        ];
        $all_session = $DB->get_records_sql($sql_query, $sql_params);
        if ($all_session) {
            foreach ($all_session as $key => $each_session) {
                $sid = $each_session->sid;
                if ($sid) {
                    $delete_session = $DB->delete_records('sessions', array('sid' => $sid));
                    // if ($delete_session) {
                    // Store info that gets removed during logout.
                    $event = \core\event\user_loggedout::create(
                        array(
                            'userid' => $userid,
                            'objectid' => $userid,
                            'other' => array('sessionid' => $sid),
                        )
                    );
                    if ($session = $DB->get_record('sessions', array('sid' => $sid))) {
                        $event->add_record_snapshot('sessions', $session);
                    }
                    \core\session\manager::init_empty_session();
                    // Trigger event AFTER action.
                    $event->trigger();
                    // }
                }
            }
        }


        $data = new stdClass;
        $data->userid = ($userid) ? $userid : 0;
        $data->logout = ($delete_session) ?  true : false;

        return $data;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.9
     */
    public static function user_logout_returns()
    {
        return new external_single_structure(
            array(
                'logout' => new external_value(PARAM_BOOL, 'status'),
                'userid' => new external_value(PARAM_INT, 'user id')
            )
        );
    }
}
