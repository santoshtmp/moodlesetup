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
 * @copyright  2024 
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * https://moodledev.io/docs/4.4/apis/commonfiles/db-tasks.php
 * 
 */


namespace theme_yipl\task;


class scheduled_name extends \core\task\scheduled_task
{

    /**
     * Task description about this scheduled task.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('scheduled_dosomething', 'theme_yipl');
    }

    /**
     * Execute the scheduled task.
     * Call api or perform action
     */
    public function execute()
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
            //  $th->getMessage()
        }
    }
}
