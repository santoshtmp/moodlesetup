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
 * @package   theme_yipl   
 * @copyright 2025 YIPL
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\output\core;

use core\lang_string;
use core\output\html_writer;
use core\output\theme_config;
use core_course_category;
use core_course_list_element;
use coursecat_helper;
use moodle_url;
use stdClass;
use theme_yipl\util\UtilCourse_handler;

defined('MOODLE_INTERNAL') || die;


/**
 * Class to override the core course renderer
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('core','course');
 */
class course_renderer extends \core_course_renderer
{

    /**
     * Renders the list of courses
     *
     * This is internal function, please use {@link core_course_renderer::courses_list()} or another public
     * method from outside of the class
     *
     * If list of courses is specified in $courses; the argument $chelper is only used
     * to retrieve display options and attributes, only methods get_show_courses(),
     * get_courses_display_option() and get_and_erase_attributes() are called.
     *
     * @param coursecat_helper $chelper various display options
     * @param array $courses the list of courses to display
     * @param int|null $totalcount total number of courses (affects display mode if it is AUTO or pagination if applicable),
     *     defaulted to count($courses)
     * @return string
     */
    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null)
    {
        global $CFG;
        $theme = theme_config::load('yipl');
        $courses_view = $theme->settings->courses_view;
        // defaulr or card
        if ($courses_view == 'default') {
            return parent::coursecat_courses($chelper, $courses, $totalcount);
        }
        // 
        if ($totalcount === null) {
            $totalcount = count($courses);
        }
        if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        }

        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {
            // In 'auto' course display mode we analyse if number of courses is more or less than $CFG->courseswithsummarieslimit
            if ($totalcount <= $CFG->courseswithsummarieslimit) {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
            } else {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
            }
        }

        // prepare content of paging bar if it is needed
        $paginationurl = $chelper->get_courses_display_option('paginationurl');
        $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
        if ($totalcount > count($courses)) {
            // there are more results that can fit on one page
            if ($paginationurl) {
                // the option paginationurl was specified, display pagingbar
                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage))
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)
                    ), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                // the option for 'View more' link was specified, display more link
                // $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag(
                    'div',
                    html_writer::link(
                        $viewmoreurl,
                        get_string('view_all_course', 'theme_yipl', $totalcount) .
                            html_writer::img(
                                $CFG->wwwroot . "/theme/yipl/pix/icons/arrow-right.svg",
                                'view-all-course',
                                []
                            ),
                        [
                            'class' => 'btn btn-primary view-all-course',
                        ]
                    ),
                    ['class' => 'paging paging-morelink yipl-view-more']
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link(
                $paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)
            ), array('class' => 'paging paging-showperpage'));
        }

        // display list of courses content
        $content = "";
        $attributes = $chelper->get_and_erase_attributes('courses');
        $content .= html_writer::start_tag('div', $attributes);
        $content .= html_writer::start_tag('div', ['class' => 'yipl-courses-wrapper']);
        $coursecount = 0;
        foreach ($courses as $course) {
            $coursecount++;
            $classes = ($coursecount % 2) ? 'odd' : 'even';
            if ($coursecount == 1) {
                $classes .= ' first';
            }
            if ($coursecount >= count($courses)) {
                $classes .= ' last';
            }
            $content .= $this->coursecat_coursebox($chelper, $course, $classes);
        }
        $content .= html_writer::end_tag('div'); //.yipl-courses-wrapper

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div'); // .courses
        return $content;
    }

    /**
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use {@link core_course_renderer::course_info_box()}
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_list_element|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '')
    {
        global $OUTPUT;
        $theme = theme_config::load('yipl');
        $courses_view = $theme->settings->courses_view;
        // defaulr or card
        if ($courses_view == 'default') {
            return parent::coursecat_coursebox($chelper, $course, $additionalclasses);
        }
        // add css class yipl-course-card
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }
        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }
        // if ($course instanceof stdClass) {
        //     $course = new core_course_list_element($course);
        // }
        // $classes = trim('coursebox clearfix '. $additionalclasses);
        // if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
        //     $classes .= ' collapsed';
        // }

        // .coursebox
        // $content = '';
        // $content .= html_writer::start_tag('div', array(
        //     'class' => $classes,
        //     'data-courseid' => $course->id,
        //     'data-type' => self::COURSECAT_TYPE_COURSE,
        // ));
        // $content .= html_writer::start_tag('div', array('class' => 'info'));
        // $content .= $this->course_name($chelper, $course);
        // $content .= $this->course_enrolment_icons($course);
        // $content .= html_writer::end_tag('div');
        // $content .= html_writer::start_tag('div', array('class' => 'content'));
        // $content .= $this->coursecat_coursebox_content($chelper, $course);
        // $content .= html_writer::end_tag('div');
        // $content .= html_writer::end_tag('div'); // .coursebox


        $template_content = UtilCourse_handler::course_card_info($course->id, true);
        $template_content['datatype'] = self::COURSECAT_TYPE_COURSE;
        $content = $OUTPUT->render_from_template('theme_yipl/parts/course-card', $template_content);

        return $content;
    }


    /**
     * Returns HTML to display course content (summary, course contacts and optionally category name)
     *
     * This method is called from coursecat_coursebox() and may be re-used in AJAX
     *
     * @param coursecat_helper $chelper various display options
     * @param stdClass|core_course_list_element $course
     * @return string
     */
    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course)
    {
        global $CFG, $OUTPUT;
        $theme = theme_config::load('yipl');
        $courses_view = $theme->settings->courses_view;
        // defaulr or card
        if ($courses_view == 'default') {
            return parent::coursecat_coursebox_content($chelper, $course);
        }

        // if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
        //     return '';
        // }
        // if ($course instanceof stdClass) {
        //     $course = new core_course_list_element($course);
        // }
        // $content = html_writer::start_tag('div', ['class' => 'd-flex']);
        // $content .= $this->course_overview_files($course);
        // $content .= html_writer::start_tag('div', ['class' => 'flex-grow-1']);
        // $content .= $this->course_summary($chelper, $course);
        // $content .= $this->course_contacts($course);
        // $content .= $this->course_category_name($chelper, $course);
        // $content .= $this->course_custom_fields($course);
        // $content .= html_writer::end_tag('div');
        // $content .= html_writer::end_tag('div');


        $template_content =  UtilCourse_handler::course_card_info($course->id, true);
        $content = $OUTPUT->render_from_template('theme_yipl/parts/course-card', $template_content);
        return $content;

        // //
        // $course_card_info = $template_content;
        // $content = html_writer::start_tag('div', ['class' => 'card-head']);
        // $content .= html_writer::start_tag('a', [
        //     'class' => 'category-link',
        //     'id' => 'category-' . $course_card_info['categoryid'],
        //     'href' => $course_card_info['course_category_link']
        // ]);
        // $content .= html_writer::tag('div', $course_card_info['category_name'], ['class' => 'course-category-name']);
        // $content .= html_writer::end_tag('a');
        // $content .= html_writer::empty_tag('img', [
        //     'src' => $course_card_info['thumbnail_image_link'],
        //     'class' => 'card-img-top w-100',
        //     'alt' => $course_card_info['fullname'] . " image ",
        // ]);
        // $content .= html_writer::end_tag('div');
        // // 
        // $content .= html_writer::start_tag('div', ['class' => 'card-body']);
        // $content .= html_writer::tag(
        //     'h4',
        //     html_writer::link(
        //         $course_card_info['course_link'],
        //         $course_card_info['fullname'],
        //         ['class' => 'course-name']
        //     ),
        //     ['class' => 'card-title']
        // );
        // $content .= $this->course_summary($chelper, $course);
        // $content .= html_writer::end_tag('div');
        // // 
        // $content .= html_writer::start_tag('div', ['class' => 'card-footer']);
        // $content .= html_writer::link(
        //     $course_card_info['course_link'],
        //     get_string('view_course', 'theme_yipl') .  html_writer::img(
        //         $OUTPUT->image_url('icons/arrow-right', 'theme_yipl'),
        //         'view-course',
        //         ['class' => '']
        //     ),
        //     ['class' => 'view-course-link btn btn-primary']
        // );
        // $content .= html_writer::end_tag('div');
        // return $content;
    }

    /**
     * Renders the list of subcategories in a category
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat
     * @param int $depth depth of the category in the current tree
     * @return string
     */
    protected function coursecat_subcategories(coursecat_helper $chelper, $coursecat, $depth)
    {
        global $CFG;
        $subcategories = array();
        if (!$chelper->get_categories_display_option('nodisplay')) {
            $subcategories = $coursecat->get_children($chelper->get_categories_display_options());
        }
        $totalcount = $coursecat->get_children_count();
        if (!$totalcount) {
            // Note that we call core_course_category::get_children_count() AFTER core_course_category::get_children()
            // to avoid extra DB requests.
            // Categories count is cached during children categories retrieval.
            return '';
        }

        // prepare content of paging bar or more link if it is needed
        $paginationurl = $chelper->get_categories_display_option('paginationurl');
        $paginationallowall = $chelper->get_categories_display_option('paginationallowall');
        if ($totalcount > count($subcategories)) {
            if ($paginationurl) {
                // the option 'paginationurl was specified, display pagingbar
                $perpage = $chelper->get_categories_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_categories_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage))
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)
                    ), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_categories_display_option('viewmoreurl')) {
                // the option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id)
                if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
                    $viewmoreurl->param('categoryid', $coursecat->id);
                }
                $viewmoretext = $chelper->get_categories_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag(
                    'div',
                    html_writer::link($viewmoreurl, $viewmoretext),
                    array('class' => 'paging paging-morelink')
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link(
                $paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)
            ), array('class' => 'paging paging-showperpage'));
        }

        // display list of subcategories
        $content = html_writer::start_tag('div', array('class' => 'subcategories yipl-subcategories'));

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        foreach ($subcategories as $subcategory) {
            $content .= $this->coursecat_category($chelper, $subcategory, $depth + 1);
        }

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div');
        return $content;
    }

    /**
     * Returns HTML to print list of available courses for the frontpage
     *
     * @return string
     */
    public function frontpage_available_courses()
    {
        global $CFG;
        $classes = "";
        $available_courses = get_config('theme_yipl', 'available_courses');
        if ($available_courses === 'hide') {
            return;
        } elseif ($available_courses === 'default') {
            return parent::frontpage_available_courses();
        } else {
            $classes = " yipl-frontpage-course-list yipl-frontpage-course-slider";
        }
        $chelper = new coursecat_helper();
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->set_courses_display_options(array(
            'recursive' => true,
            'limit' => $CFG->frontpagecourselimit,
            'viewmoreurl' => new moodle_url('/course/index.php'),
            'viewmoretext' => new lang_string('fulllistofcourses')
        ));

        $chelper->set_attributes(array('class' => trim($classes)));
        $courses = core_course_category::top()->get_courses($chelper->get_courses_display_options());
        $totalcount = core_course_category::top()->get_courses_count($chelper->get_courses_display_options());
        // if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', context_system::instance())) {
        //     // Print link to create a new course, for the 1st available category.
        //     return $this->add_new_course_button();
        // }
        return $this->coursecat_courses($chelper, $courses, $totalcount);
    }
}
