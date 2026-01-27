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
 * Language file.
 *
 * @package   theme_skilllab
 * @copyright 2016 Frédéric Massart
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advancedsettings'] = 'Advanced settings';
$string['backgroundimage'] = 'Background image';
$string['backgroundimage_desc'] = 'The image to display as a background of the site. The background image you upload here will override the background image in your theme preset files.';
$string['brandcolor'] = 'Brand colour';
$string['brandcolor_desc'] = 'The accent colour.';
$string['bootswatch'] = 'Bootswatch';
$string['bootswatch_desc'] = 'A bootswatch is a set of Bootstrap variables and css to style Bootstrap';
$string['choosereadme'] = 'skill lab is a child theme of boots, it is a modern highly-customisable theme. This theme is intended to be used directly, or as a parent theme when creating new themes utilising Bootstrap 4.';
$string['configtitle'] = 'Skill lab CSC Theme Settting';
$string['pluginname'] = 'Skill lab CSC';

$string['nobootswatch'] = 'None';
$string['presetfiles'] = 'Additional theme preset files';
$string['presetfiles_desc'] = 'Preset files can be used to dramatically alter the appearance of the theme. See <a href="https://docs.moodle.org/dev/Boost_Presets">Boost presets OR skilllab preset</a> for information on creating and sharing your own preset files, and see the <a href="https://archive.moodle.net/boost">Presets repository</a> for presets that others have shared.';
$string['preset'] = 'Theme preset';
$string['preset_desc'] = 'Pick a preset to broadly change the look of the theme.';
$string['privacy:metadata'] = 'The skilllab theme does not store any personal data about any user.';
$string['rawscss'] = 'Raw SCSS';
$string['rawscss_desc'] = 'Use this field to provide SCSS or CSS code which will be injected at the end of the style sheet.';
$string['rawscsspre'] = 'Raw initial SCSS';
$string['rawscsspre_desc'] = 'In this field you can provide initialising SCSS code, it will be injected before everything else. Most of the time you will use this setting to define variables.';
$string['region-side-pre'] = 'Right';
$string['showfooter'] = 'Show footer';
$string['unaddableblocks'] = 'Unneeded blocks';
$string['unaddableblocks_desc'] = 'The blocks specified are not needed when using this theme and will not be listed in the \'Add a block\' menu.';
$string['privacy:metadata:preference:draweropenblock'] = 'The user\'s preference for hiding or showing the drawer with blocks.';
$string['privacy:metadata:preference:draweropenindex'] = 'The user\'s preference for hiding or showing the drawer with course index.';
$string['privacy:metadata:preference:draweropennav'] = 'The user\'s preference for hiding or showing the drawer menu navigation.';
$string['privacy:drawerindexclosed'] = 'The current preference for the index drawer is closed.';
$string['privacy:drawerindexopen'] = 'The current preference for the index drawer is open.';
$string['privacy:drawerblockclosed'] = 'The current preference for the block drawer is closed.';
$string['privacy:drawerblockopen'] = 'The current preference for the block drawer is open.';
$string['scheduled_dosomething'] = 'Skill lab Scheduled task. ';
$string['privacy:metadata'] = 'Skill lab theme does not store any personal data.';

// Deprecated since Moodle 4.0.
$string['totop'] = 'Go to top';

// Deprecated since Moodle 4.1.
$string['currentinparentheses'] = '(current)';
$string['privacy:drawernavclosed'] = 'The current preference for the navigation drawer is closed.';
$string['privacy:drawernavopen'] = 'The current preference for the navigation drawer is open.';

// General settings tab.
$string['generalsettings'] = 'General settings';
$string['loginbackgroundimage'] = 'Login page background image';
$string['loginbackgroundimage_desc'] = 'The image to display as a background for the login page.';
$string['logo'] = 'Logo';
$string['logodesc'] = 'The logo is displayed in the header.';
$string['favicon'] = 'Custom favicon';
$string['favicondesc'] = 'Upload your own favicon.  It should be an .ico file.';

$string['custommenuitems'] = 'Custom menu item';
$string['custommenuitemsdesc'] = 'A custom menu may be configured here. Enter each menu item on a new line with format: menu text, a link URL (optional, not for a top menu item with sub-items), a tooltip title (optional) and a language code or comma-separated list of codes (optional, for displaying the line to users of the specified language only), separated by pipe characters. Lines starting with a hyphen will appear as menu items in the previous top level menu and ### makes a divider. For example:<pre>Courses
-All courses|/course/
-Course search|/course/search.php
-###
-FAQ|https://example.org/faq
-Preguntas más frecuentes|https://example.org/pmf||es
Mobile app|https://example.org/app|Download our app
</pre>';
$string['custommenuitems_default'] = '';

$string['customusermenuitems'] = 'Custom User menu items';
$string['customusermenuitemsdesc'] = 'You can configure the contents of the user menu (with the exception of the log out link, which is automatically added). Each line is separated by pipe characters and consists of 1) a string in "langstringname, componentname" form or as plain text, and 2) a URL. Dividers can be used by adding a line of one or more # characters where desired.';
$string['customusermenuitems_default'] = 'profile,moodle|/user/profile.php
grades,grades|/grade/report/mygrades.php
calendar,core_calendar|/calendar/view.php?view=month
privatefiles,moodle|/user/files.php
reports,core_reportbuilder|/reportbuilder/index.php
';

// Frontpage settings tab.
$string['frontpagesettings'] = 'Frontpage';
$string['herosection_image'] = 'Hero Section Picture';
$string['herosection_imagedesc'] = 'Add an image for your hero section.';
$string['herosection_title'] = 'Hero Section title';
$string['herosection_titledesc'] = 'Add the Hero Section\'s title.';
$string['herosection_desc'] = 'Hero Section caption';
$string['herosection_descdesc'] = 'Add a caption for your hero slide';
$string['start_guideline_heading'] = 'Start Guideline Title';
$string['start_guideline_headingdesc'] = 'Add a start guideline heading title';

$string['displaymarketingboxes'] = 'Show front page marketing boxes';
$string['displaymarketingboxesdesc'] = 'If you want to see the boxes, select yes <strong>then click SAVE</strong> to load the input fields.';
$string['marketingsectionheading'] = 'Marketing section heading title';
$string['marketingsectioncontent'] = 'Marketing section content';
$string['marketingicon'] = 'Marketing Icon {$a}';
$string['marketingheading'] = 'Marketing Heading {$a}';
$string['marketingcontent'] = 'Marketing Content {$a}';

$string['disableteacherspic'] = 'Disable teachers picture';
$string['disableteacherspicdesc'] = 'This setting hides the teachers\' pictures from the course cards.';

$string['sliderfrontpageloggedin'] = 'Show slideshow in frontpage after login?';
$string['sliderfrontpageloggedindesc'] = 'If enabled, the slideshow will be showed in the frontpage page replacing the header image.';
$string['startguide_slidercount'] = 'Slider count';
$string['startguide_slidercountdesc'] = 'Select how many slides you want to add <strong>then click SAVE</strong> to load the input fields.';
$string['guide_sliderimage'] = 'Slider picture';
$string['guide_sliderimagedesc'] = 'Add an image for your slide.';
$string['guide_slidertitle'] = 'Slide title';
$string['guide_slidertitledesc'] = 'Add the slide\'s title.';
$string['guide_slidercaption'] = 'Slider caption';
$string['guide_slidercaptiondesc'] = 'Add a caption for your slide';

$string['numbersfrontpage'] = 'Show site numberss';
$string['numbersfrontpagedesc'] = 'If enabled, display the number of active users and courses in the frontpage.';
$string['numbersfrontpagecontent'] = 'Numbers section content';
$string['numbersfrontpagecontentdesc'] = 'You can add any text to the left side of the numbers section';
$string['numbersfrontpagecontentdefault'] = '<h2>Trusted by 25,000+ happy customers.</h2>
                    <p>With lots of unique blocks, you can easily build <br class="d-none d-sm-block d-md-none d-xl-block">
                        a page without coding. Build your next website <br class="d-none d-sm-block d-md-none d-xl-block">
                        within few minutes.</p>';
$string['numbersusers'] = 'Active users accessing our amazing resources';
$string['numberscourses'] = 'Courses made for you that you can trust!';




// Footer settings tab.
$string['footersettings'] = 'Footer';
$string['footer_desc'] = 'Footer Description';
$string['footer_descdesc'] = '';
$string['copyright'] = 'copyright text';
$string['copyrightdesc'] = 'Copyright © 2023 skilllab .';
$string['website'] = 'Website URL';
$string['websitedesc'] = 'Main company Website';
$string['phone_num'] = 'Phone number';
$string['phone_numdesc'] = 'Enter phone number No.';
$string['mail'] = 'E-Mail';
$string['maildesc'] = 'Company support e-mail';
$string['facebook'] = 'Facebook URL';
$string['facebookdesc'] = 'Enter the URL of your Facebook. (i.e http://www.facebook.com/myinstitution)';
$string['twitter'] = 'Twitter URL';
$string['twitterdesc'] = 'Enter the URL of your twitter. (i.e http://www.twitter.com/myinstitution)';
$string['linkedin'] = 'Linkedin URL';
$string['linkedindesc'] = 'Enter the URL of your Linkedin. (i.e http://www.linkedin.com/myinstitution)';
$string['youtube'] = 'Youtube URL';
$string['youtubedesc'] = 'Enter the URL of your Youtube. (i.e https://www.youtube.com/user/myinstitution)';
$string['instagram'] = 'Instagram URL';
$string['instagramdesc'] = 'Enter the URL of your Instagram. (i.e https://www.instagram.com/myinstitution)';
$string['whatsapp'] = 'Whatsapp number';
$string['whatsappdesc'] = 'Enter your whatsapp number for contact.';
$string['telegram'] = 'Telegram';
$string['telegramdesc'] = 'Enter your Telegram contact or group link.';
$string['contactus'] = 'Contact us';
$string['followus'] = 'Follow us';
$string['subscribe'] = 'Subscribe';
$string['your_email'] = 'Your Email';


$string['contact_subject'] = 'Subject';
$string['contact_message'] = 'Message';
$string['contact_submit_message'] = 'Submit';


$string['messageprovider:skilllab_notification'] = 'skilllab message test';
$string['skilllab:manage'] = 'Manage skilllab';
$string['skilllab:instructor_view'] = 'Skill lab instructor capability';
$string['skilllab:skill_lab_viewer'] = 'Skill lab viewer identifier capability';
$string['skilllab:skill_lab_editor'] = 'Skill lab Editor identifier capability';
$string['skilllab:institution_viewer'] = 'Skill lab institution viewer capability';
$string['skilllab:institution_editor'] = 'Skill lab institution editor identifier capability';

$string['add-scholarship-course-type'] = 'Add Scholarship';
$string['add-course-index-category'] = 'Add Course';
$string['add-career-road-map-course-type'] = 'Add Career Road Map';
