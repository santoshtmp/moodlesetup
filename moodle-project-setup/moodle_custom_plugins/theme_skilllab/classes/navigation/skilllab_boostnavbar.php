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

namespace theme_skilllab\navigation;

use core\navigation\views\view;
use navigation_node;
use moodle_url;
use action_link;
use lang_string;
use stdClass;

/**
 * Creates a navbar for boost that allows easy control of the navbar items.
 *
 * @package    theme_skilllab
 * @copyright   
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skilllab_boostnavbar extends \theme_boost\boostnavbar {

    /**
     * Prepares the navigation nodes for use with boost.
     */
    protected function prepare_nodes_for_boost(): void {
        global $PAGE;

        // Remove the navbar nodes that already exist in the primary navigation menu.
        $this->remove_items_that_exist_in_navigation($PAGE->primarynav);

        // Defines whether section items with an action should be removed by default.
        $removesections = true;

        if ($this->page->context->contextlevel == CONTEXT_COURSECAT) {
            // Remove the 'Permissions' navbar node in the Check permissions page.
            if ($this->page->pagetype === 'admin-roles-check') {
                $this->remove('permissions');
            }
        }
        if ($this->page->context->contextlevel == CONTEXT_COURSE) {
            // Remove any duplicate navbar nodes.
            $this->remove_duplicate_items();
            // Remove 'My courses' and 'Courses' if we are in the course context.
            $this->remove('mycourses');
            $this->remove('courses');
            $this->remove('home');

            // Remove the course category breadcrumb node.
            $this->remove($this->page->course->category, \breadcrumb_navigation_node::TYPE_CATEGORY);
            // Remove the course breadcrumb node.
            // $this->remove($this->page->course->id, \breadcrumb_navigation_node::TYPE_COURSE);
            // Remove the navbar nodes that already exist in the secondary navigation menu.
            // $this->remove_items_that_exist_in_navigation($PAGE->secondarynav);

            switch ($this->page->pagetype) {
                case 'group-groupings':
                case 'group-grouping':
                case 'group-overview':
                case 'group-assign':
                    // Remove the 'Groups' navbar node in the Groupings, Grouping, group Overview and Assign pages.
                    $this->remove('groups');
                case 'backup-backup':
                case 'backup-restorefile':
                case 'backup-copy':
                case 'course-reset':
                    // Remove the 'Import' navbar node in the Backup, Restore, Copy course and Reset pages.
                    $this->remove('import');
                case 'course-user':
                    $this->remove('mygrades');
                    $this->remove('grades');
            }
        }

        // Remove 'My courses' if we are in the module context.
        if ($this->page->context->contextlevel == CONTEXT_MODULE) {
            $this->remove('mycourses');
            $this->remove('courses');
            $this->remove('home');

            // Remove the course category breadcrumb node.
            $this->remove($this->page->course->category, \breadcrumb_navigation_node::TYPE_CATEGORY);
            $courseformat = course_get_format($this->page->course)->get_course();
            // Section items can be only removed if a course layout (coursedisplay) is not explicitly set in the
            // given course format or the set course layout is not 'One section per page'.
            $removesections = !isset($courseformat->coursedisplay) ||
                $courseformat->coursedisplay != COURSE_DISPLAY_MULTIPAGE;
            if ($removesections) {
                // If the course sections are removed, we need to add the anchor of current section to the Course.
                $coursenode = $this->get_item($this->page->course->id);
                if (!is_null($coursenode) && $this->page->cm->sectionnum !== null) {
                    $coursenode->action = course_get_format($this->page->course)->get_view_url($this->page->cm->sectionnum);
                }
            }
        }

        if ($this->page->context->contextlevel == CONTEXT_SYSTEM) {
            // Remove the navbar nodes that already exist in the secondary navigation menu.
            $this->remove_items_that_exist_in_navigation($PAGE->secondarynav);
        }

        // Set the designated one path for courses.
        $mycoursesnode = $this->get_item('mycourses');
        if (!is_null($mycoursesnode)) {
            $url = new \moodle_url('/my/courses.php');
            $mycoursesnode->action = $url;
            $mycoursesnode->text = get_string('mycourses');
        }

        // $pagetype = $this->page->pagetype;
        $remove_home_page = [
            'page-skilllab-category-index',
            'page-career-road-map-course-type',
            'page-scholarship-course-type'
        ];
        if (in_array($PAGE->bodyid, $remove_home_page)) {
            $this->remove('home');
        }

        // remove no link items
        $this->remove_no_link_items($removesections);

        // modify nav for course edit page
        $this->page_course_edit();

        // Don't display the navbar if there is only one item. Apparently this is bad UX design.
        if ($this->item_count() <= 1) {
            $this->clear_items();
            return;
        }

        // Make sure that the last item is not a link. Not sure if this is always a good idea.
        $this->remove_last_item_action();

        // this should alys be at the end to identify first items 
        $this->identify_start_item();
    }

    /**
     * identify_start_item
     * this should be called at last
     */
    protected function identify_start_item(): void {
        $item = $this->items[0];
        $item->initial_item = true;
        reset($this->items);
    }

    /**
     * course edit page nav
     */
    protected function page_course_edit() {
        $scholarship = optional_param('scholarship', 0, PARAM_INT);
        $career_road_map = optional_param('career_road_map', 0, PARAM_INT);

        $pagetype = $this->page->pagetype;
        $course_id = optional_param('id', 0, PARAM_INT);
        if ($pagetype == 'course-edit' and !$course_id) {
            foreach ($this->items as $key => $item) {
                if ($key > 1) {
                    unset($this->items[$key]);
                }
            }
            $new_item_0 = $this->items[0];
            $new_item = $this->items[1];
            if ($scholarship) {
                $new_item_0->text = "Scholarship";
                $new_item_0->action = "/scholarship";
                $new_item->text = 'Add a new scholarship course';
            } elseif ($career_road_map) {
                $new_item_0->text = "Career Road Map";
                $new_item_0->action = "/career_road_map";
                $new_item->text = 'Add a New Career Road Map';
            } else {
                $new_item->text = 'Add a new course';
            }
            $new_item->is_hidden = true;
            $new_item->action = null;
        }
    }
}
