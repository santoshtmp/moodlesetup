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

namespace theme_skilllab\output;

use coding_exception;
use core_date;
use DateTime;
use html_table;
use html_writer;
use mod_quiz\output\view_page;
use question_display_options;
use mod_quiz\quiz_attempt;



defined('MOODLE_INTERNAL') || die;


/**
 * The main renderer for the quiz module.
 *
 * @package   mod_quiz
 * @category  output
 * @copyright 2011 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quiz_renderer extends \mod_quiz\output\renderer
{

    /**
     * Render the tertiary navigation for the view page.
     *
     * @param view_page $viewobj the information required to display the view page.
     * @return string HTML to output.
     */
    // public function view_page_tertiary_nav(view_page $viewobj): string
    // {
    //     $content = '';

    //     if ($viewobj->buttontext) {
    //         $attemptbtn = $this->start_attempt_button(
    //             $viewobj->buttontext,
    //             $viewobj->startattempturl,
    //             $viewobj->preflightcheckform,
    //             $viewobj->popuprequired,
    //             $viewobj->popupoptions
    //         );
    //         $quiz_attemp_count_in_interval = get_quiz_attemp_in_interval($viewobj->attemptobjs,  $attemptbtn);
    //         $content .= $quiz_attemp_count_in_interval['content'];
    //     }

    //     if ($viewobj->canedit && !$viewobj->quizhasquestions) {
    //         $content .= html_writer::link(
    //             $viewobj->editurl,
    //             get_string('addquestion', 'quiz'),
    //             ['class' => 'btn btn-secondary quiz-add-question']
    //         );
    //     }

    //     if ($content) {
    //         return html_writer::div(html_writer::div($content, 'row'), 'container-fluid tertiary-navigation');
    //     } else {
    //         return '';
    //     }
    // }

    /**
     * Render the tertiary navigation for pages during the attempt.
     *
     * @param string|moodle_url $quizviewurl url of the view.php page for this quiz.
     * @return string HTML to output.
     */
    // public function during_attempt_tertiary_nav($quizviewurl): string
    // {
    //     course_completeion_restrict_attempt();
    //     $output = '';
    //     $output .= html_writer::start_div('container-fluid tertiary-navigation');
    //     $output .= html_writer::start_div('row');
    //     $output .= html_writer::start_div('navitem');
    //     $output .= html_writer::link(
    //         $quizviewurl,
    //         get_string('back'),
    //         ['class' => 'btn btn-secondary']
    //     );
    //     $output .= html_writer::end_div();
    //     $output .= html_writer::end_div();
    //     $output .= html_writer::end_div();
    //     return $output;
    // }
}
