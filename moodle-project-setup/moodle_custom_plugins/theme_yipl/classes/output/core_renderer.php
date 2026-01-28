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

namespace theme_yipl\output;

defined('MOODLE_INTERNAL') || die;

use core_block\output\block_contents;
use moodle_url;
use stdClass;
use theme_yipl\handler\settings_handler;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package   theme_yipl   
 * @copyright 2025 YIPL
 * @author    santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * core\output\core_renderer
 */

//  class core_renderer extends \core\output\core_renderer
class core_renderer extends \theme_boost\output\core_renderer
{

	/**
	 * Renders the "breadcrumb" for all pages in boost.
	 *
	 * @return string the HTML for the navbar.
	 */
	public function navbar(): string
	{
		// $newnav = new \theme_boost\boostnavbar($this->page);
		$newnav = new \theme_yipl\local\breadcrumb_navbar($this->page);
		return $this->render_from_template('core/navbar', $newnav);
	}

	/**
	 * Construct a user menu, returning HTML that can be echoed out by a
	 * layout file.
	 *
	 * @param stdClass $user A user object, usually $USER.
	 * @param bool $withlinks true if a dropdown should be built.
	 * @return string HTML fragment.
	 */
	public function user_menu($user = null, $withlinks = null)
	{
		global $USER, $CFG;
		require_once($CFG->dirroot . '/user/lib.php');

		if (is_null($user)) {
			$user = $USER;
		}

		// Note: this behaviour is intended to match that of core_renderer::login_info,
		// but should not be considered to be good practice; layout options are
		// intended to be theme-specific. Please don't copy this snippet anywhere else.
		if (is_null($withlinks)) {
			$withlinks = empty($this->page->layout_options['nologinlinks']);
		}

		// Add a class for when $withlinks is false.
		$usermenuclasses = 'usermenu';
		if (!$withlinks) {
			$usermenuclasses .= ' withoutlinks';
		}

		$returnstr = "";

		// If during initial install, return the empty return string.
		if (during_initial_install()) {
			return $returnstr;
		}

		$loginpage = $this->is_login_page();
		$loginurl = get_login_url();

		// Get some navigation opts.
		$opts = user_get_user_navigation_info($user, $this->page);

		if (!empty($opts->unauthenticateduser)) {
			$returnstr = get_string($opts->unauthenticateduser['content'], 'moodle');
			// If not logged in, show the typical not-logged-in string.
			if (!$loginpage && (!$opts->unauthenticateduser['guest'] || $withlinks)) {
				$returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
			}

			return html_writer::div(
				html_writer::span(
					$returnstr,
					'login nav-link'
				),
				$usermenuclasses
			);
		}

		$avatarclasses = "avatars";
		$avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
		$usertextcontents = $opts->metadata['userfullname'];

		// Other user.
		if (!empty($opts->metadata['asotheruser'])) {
			$avatarcontents .= html_writer::span(
				$opts->metadata['realuseravatar'],
				'avatar realuser'
			);
			$usertextcontents = $opts->metadata['realuserfullname'];
			$usertextcontents .= html_writer::tag(
				'span',
				get_string(
					'loggedinas',
					'moodle',
					html_writer::span(
						$opts->metadata['userfullname'],
						'value'
					)
				),
				['class' => 'meta viewingas']
			);
		}

		// Role.
		if (!empty($opts->metadata['asotherrole'])) {
			$role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
			$usertextcontents .= html_writer::span(
				$opts->metadata['rolename'],
				'meta role role-' . $role
			);
		}

		// User login failures.
		if (!empty($opts->metadata['userloginfail'])) {
			$usertextcontents .= html_writer::span(
				$opts->metadata['userloginfail'],
				'meta loginfailures'
			);
		}

		// MNet.
		if (!empty($opts->metadata['asmnetuser'])) {
			$mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
			$usertextcontents .= html_writer::span(
				$opts->metadata['mnetidprovidername'],
				'meta mnet mnet-' . $mnet
			);
		}

		$returnstr .= html_writer::span(
			html_writer::span($usertextcontents, 'usertext me-1') .
				html_writer::span($avatarcontents, $avatarclasses),
			'userbutton'
		);

		// Create a divider (well, a filler).
		$divider = new action_menu\filler();
		$divider->primary = false;

		$am = new action_menu();
		$am->set_menu_trigger(
			$returnstr,
			'nav-link'
		);
		$am->set_action_label(get_string('usermenu'));
		$am->set_nowrap_on_items();
		if ($withlinks) {
			$navitemcount = count($opts->navitems);
			$idx = 0;
			foreach ($opts->navitems as $key => $value) {
				switch ($value->itemtype) {
					case 'divider':
						// If the nav item is a divider, add one and skip link processing.
						$am->add($divider);
						break;

					case 'invalid':
						// Silently skip invalid entries (should we post a notification?).
						break;

					case 'link':
						// Process this as a link item.
						$pix = null;
						if (isset($value->pix) && !empty($value->pix)) {
							$pix = new pix_icon($value->pix, '', null, ['class' => 'iconsmall']);
						} else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
							$value->title = html_writer::img(
								$value->imgsrc,
								$value->title,
								['class' => 'iconsmall']
							) . $value->title;
						}

						$al = new action_menu\link_secondary(
							$value->url,
							$pix,
							$value->title,
							['class' => 'icon']
						);
						if (!empty($value->titleidentifier)) {
							$al->attributes['data-title'] = $value->titleidentifier;
						}
						$am->add($al);
						break;
				}

				$idx++;

				// Add dividers after the first item and before the last item.
				if ($idx == 1 || $idx == $navitemcount - 1) {
					$am->add($divider);
				}
			}
		}

		return html_writer::div(
			$this->render($am),
			$usermenuclasses
		);
	}


	// /**
	//  * Wrapper for header elements.
	//  *
	//  * @return string HTML to display the main header.
	//  */
	// public function full_header()
	// {}


	/**
	 * Returns standard navigation between activities in a course.
	 * \core\output\core_renderer
	 * @return string the navigation HTML.
	 */
	public function activity_navigation()
	{
		global $PAGE, $COURSE;
		// First we should check if we want to add navigation.
		$context = $PAGE->context;
		if (
			($PAGE->pagelayout !== 'incourse' && $PAGE->pagelayout !== 'frametop')
			|| $context->contextlevel != CONTEXT_MODULE
		) {
			return '';
		}

		// If the activity is in stealth mode, show no links.
		if ($PAGE->cm->is_stealth()) {
			return '';
		}

		//  yipl_activity_navigation value can be  [0 => 'Default', 1 => 'Always show', 2 => 'Always hide']
		$yipl_activity_navigation = get_config('theme_yipl', 'yipl_activity_navigation');
		if ($yipl_activity_navigation == '2') {
			return '';
		} else if ($yipl_activity_navigation == '0') {
			$course = $PAGE->cm->get_course();
			$courseformat = course_get_format($course);
			// If the theme implements course index and the current course format uses course index and the current
			// page layout is not 'frametop' (this layout does not support course index), show no links.
			if (
				$PAGE->theme->usescourseindex && $courseformat->uses_course_index() &&
				$PAGE->pagelayout !== 'frametop'
			) {
				return '';
			}
		}

		// check if it is frontpage 
		if ($COURSE->id == '1') {
			return '';
		}

		// Get a list of all the activities in the course.
		$modules = get_fast_modinfo($COURSE->id)->get_cms();

		// Put the modules into an array in order by the position they are shown in the course.
		$mods = [];
		$activitylist = [];
		foreach ($modules as $module) {
			// Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
			if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
				continue;
			}
			$mods[$module->id] = $module;

			// No need to add the current module to the list for the activity dropdown menu.
			if ($module->id == $PAGE->cm->id) {
				continue;
			}
			// Module name.
			$modname = $module->get_formatted_name();
			// Display the hidden text if necessary.
			if (!$module->visible) {
				$modname .= ' ' . get_string('hiddenwithbrackets');
			}
			// Module URL.
			$linkurl = new moodle_url($module->url, ['forceview' => 1]);
			// Add module URL (as key) and name (as value) to the activity list array.
			$activitylist[$linkurl->out(false)] = $modname;
		}

		$nummods = count($mods);

		// If there is only one mod then do nothing.
		if ($nummods == 1) {
			return '';
		}

		// Get an array of just the course module ids used to get the cmid value based on their position in the course.
		$modids = array_keys($mods);

		// Get the position in the array of the course module we are viewing.
		$position = array_search($PAGE->cm->id, $modids);

		$prevmod = null;
		$nextmod = null;

		// Check if we have a previous mod to show.
		if ($position > 0) {
			$prevmod = $mods[$modids[$position - 1]];
		}

		// Check if we have a next mod to show.
		if ($position < ($nummods - 1)) {
			$nextmod = $mods[$modids[$position + 1]];
		}

		$activitynav = new \core_course\output\activity_navigation($prevmod, $nextmod, $activitylist);
		$renderer = $PAGE->get_renderer('core', 'course');
		return $renderer->render($activitynav);
	}

	/**
	 * Returns the moodle_url for the favicon.
	 *
	 * @since Moodle 2.5.1 2.6
	 * @return moodle_url The moodle_url for the favicon
	 */
	public function favicon()
	{
		$favicon = settings_handler::setting('favicon', 'favicon');
		if ($favicon) {
			return $favicon;
		}

		return parent::favicon();
	}

	/**
	 * 
	 */
	public function get_theme_yipl_logo_description()
	{
		return format_text(settings_handler::setting('logo_description'));
	}

	/**
	 * 
	 */
	public function get_theme_yipl_login_card_popup()
	{
		return (int)settings_handler::setting('login_card_popup');
	}

	/**
	 * Return the site's logo URL, if any.
	 *
	 * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
	 * @param int $maxheight The maximum height, or null when the maximum height does not matter.
	 * @return moodle_url|false
	 */
	public function get_logo_url($maxwidth = null, $maxheight = 200)
	{
		global $CFG;
		$theme_logo = settings_handler::setting('logo', 'logo');
		if ($theme_logo) {
			return $theme_logo;
		}
		$logo = get_config('core_admin', 'logo');
		if (empty($logo)) {
			return false;
		}

		// 200px high is the default image size which should be displayed at 100px in the page to account for retina displays.
		// It's not worth the overhead of detecting and serving 2 different images based on the device.

		// Hide the requested size in the file path.
		$filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

		// Use $CFG->themerev to prevent browser caching when the file changes.
		return moodle_url::make_pluginfile_url(
			context_system::instance()->id,
			'core_admin',
			'logo',
			$filepath,
			theme_get_revision(),
			$logo
		);
	}

	/**
	 * Returns HTML attributes to use within the body tag. This includes an ID and classes.
	 *
	 * @since Moodle 2.5.1 2.6
	 * @param string|array $additionalclasses Any additional classes to give the body tag,
	 * @return string
	 */
	public function body_attributes($additionalclasses = [])
	{
		if (!is_array($additionalclasses)) {
			$additionalclasses = explode(' ', $additionalclasses);
		}
		$additionalclasses[] = "yipl-style";
		return ' id="' . $this->body_id() . '" class="' . $this->body_css_classes($additionalclasses) . '"';
	}


	/**
	 * Prints a nice side block with an optional header.
	 *
	 * @param block_contents $bc HTML for the content
	 * @param string $region the region the block is appearing in.
	 * @return string the HTML to be output.
	 */
	public function block(block_contents $bc, $region)
	{
		$bc = clone ($bc); // Avoid messing up the object passed in.
		if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
			$bc->collapsible = block_contents::NOT_HIDEABLE;
		}

		$id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
		$context = new stdClass();
		$context->skipid = $bc->skipid;
		$context->blockinstanceid = $bc->blockinstanceid ?: uniqid('fakeid-');
		$context->dockable = $bc->dockable;
		$context->id = $id;
		$context->hidden = $bc->collapsible == block_contents::HIDDEN;
		$context->skiptitle = strip_tags($bc->title);
		$context->showskiplink = !empty($context->skiptitle);
		$context->arialabel = $bc->arialabel;
		$context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : 'complementary';
		$context->class = $bc->attributes['class'];
		$context->type = $bc->attributes['data-block'];
		$context->title = format_string($bc->title);
		$context->content = $bc->content;
		$context->annotation = $bc->annotation;
		$context->footer = $bc->footer;
		$context->hascontrols = !empty($bc->controls);
		if ($context->hascontrols) {
			$context->controls = $this->block_controls($bc->controls, $id);
		}
		$context->has_block_title = ($context->title || $context->hascontrols) ? true : false;
		$context->is_full_width = isset($bc->attributes['full_width_section']) ? $bc->attributes['full_width_section'] : false;

		return $this->render_from_template('core/block', $context);
	}

	/**
	 * Renders the login form.
	 *
	 * @param \core_auth\output\login $form The renderable.
	 * @return string
	 */
	public function render_login(\core_auth\output\login $form)
	{
		global $CFG, $SITE;

		$context = $form->export_for_template($this);

		$context->errorformatted = $this->error_text($context->error);
		$url = $this->get_logo_url();
		if ($url) {
			$url = $url->out(false);
		}
		$context->logourl = $url;
		$context->sitename = format_string(
			$SITE->fullname,
			true,
			['context' => \context_course::instance(SITEID), "escape" => false]
		);
		$context->home_url = $CFG->wwwroot;

		return $this->render_from_template('core/loginform', $context);
	}
}
