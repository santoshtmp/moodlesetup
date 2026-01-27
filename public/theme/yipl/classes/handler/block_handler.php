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
 * yipl
 * @package   theme_yipl
 * @copyright 2025 YIPL
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_yipl\handler;

use core\output\html_writer;
use core\output\theme_config;
use theme_yipl\local\custom_fields;
use theme_yipl\util\UtilCourse_handler;
use theme_yipl\util\UtilNotification_handler;
use theme_yipl\util\UtilUser_handler;

defined('MOODLE_INTERNAL') || die();


/**
 * 
 */
class block_handler
{

    /**
     * @var string $block_type Type of the yipl block
     */
    public $block_type;

    /**
     * @var \stdClass $block_settings
     */
    protected $block_settings;

    /**
     * @var \context $context
     */
    protected $context;


    /**
     * constructor
     */
    public function __construct($block_type, $block_settings)
    {
        global $COURSE;
        $this->block_type = $block_type;
        $this->block_settings = $block_settings;
        $this->context = \core\context\course::instance($COURSE->id); // \context_course::instance($COURSE->id);

    }

    /**
     * define blocks type
     * @return array
     */
    public static function get_block_yipl_types()
    {
        $theme = theme_config::load('yipl');
        $block_types = [
            'progress_bar' => 'Progress Bar Block',
            'course_info' => 'Course Information Block',
            'course_list' => "Courses List Block",
            'contact_us' => "Contact Us",
            'start_guideline' => "Start Guideline",
        ];
        if ($theme->settings->yipl_faqs == '1') {
            $block_types['faqs'] = 'FAQs Block';
        }
        if ($theme->settings->yipl_testimonial == '1') {
            $block_types['testimonial'] = 'Testimonial Block';
        }

        if ($theme->settings->yipl_courserating == '1') {
            $block_types['course_rating'] = 'Course Rating Block';
        }
        // array_multisort(array_column($block_types, 'name'), SORT_ASC, $block_types);;
        ksort($block_types);
        return $block_types;
    }
    /**
     * 
     */
    public static function course_fields_list()
    {
        $course_meta_var = [];
        foreach (custom_fields::$course_fields as $key => $field) {
            $course_meta_var[$field['shortname']] = $field['fieldname'];
        }
        $course_meta_var['description_summery'] = 'Course description summary';
        ksort($course_meta_var);

        return $course_meta_var;
    }

    /**
     * get_block_yipl_content
     */
    public function get_block_yipl_content()
    {
        $method_name = $this->block_type . "_block_content";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
        return $this->default_block_content();
    }

    /**
     * default_block_content
     */
    protected function default_block_content()
    {
        if ($this->block_type) {
            return "YIPL Block Type (" . $this->block_type . ") is in development. Contact developer or site admin. ";
        }
        return "YIPL Block Type is not defined. Edit the block and set YIPL Block Type";
    }

    /**
     * progress_bar
     */
    protected function progress_bar_block_content()
    {
        global $CFG, $OUTPUT, $USER, $COURSE;
        $template_content = [];
        $template_content['block_yipl_id'] = $this->block_settings->block_yipl_id;
        $template_content['is_enrolled'] = is_enrolled($this->context, $USER);
        $template_content['course_percentage'] = UtilUser_handler::get_user_course_progress($COURSE, $USER->id);
        $template_content['enrol_url'] = $CFG->wwwroot . '/enrol/index.php?id=' . $COURSE->id;
        return $OUTPUT->render_from_template("theme_yipl/blocks/progress-bar", $template_content);
    }

    /**
     * contact_us
     */
    protected function contact_us_block_content()
    {
        global $OUTPUT, $PAGE, $CFG, $SITE;
        $send_email = optional_param('send_email', 0, PARAM_INT);
        $sesskey = optional_param('sesskey', '', PARAM_RAW);
        $template_content = settings_handler::contact_details_settings();
        // check if the message is send or not
        if ($_POST && $send_email && $sesskey) {
            if ($sesskey == sesskey()) {
                $sendto_email = ($template_content['contact_form_recipient_email']) ?: $CFG->supportemail;
                $sendto_name = ($template_content['contact_form_recipient_name']) ?: $CFG->supportname;
                $form_name = optional_param('name', '', PARAM_TEXT);
                $form_email = optional_param('email', '', PARAM_TEXT);
                $form_subject = optional_param('subject', '', PARAM_TEXT);
                $form_message = optional_param('message', '', PARAM_TEXT);
                if ($form_name && $form_email && $form_message) {
                    $msg_subject =  $SITE->shortname  . " : Contact Us Message";
                    // 
                    $htmlmessage = "";
                    $htmlmessage .= html_writer::start_tag('div');
                    $htmlmessage .= html_writer::tag(
                        'p',
                        "Contact us form content :: "
                    );
                    $htmlmessage .= html_writer::tag(
                        'p',
                        html_writer::tag('strong', 'Name : ') . $form_name
                    );
                    $htmlmessage .= html_writer::tag(
                        'p',
                        html_writer::tag('strong', 'Email : ') . $form_email
                    );
                    $htmlmessage .= html_writer::tag(
                        'p',
                        html_writer::tag('strong', 'Subject : ') . $form_subject
                    );
                    $htmlmessage .= html_writer::tag(
                        'p',
                        html_writer::tag('strong', 'Message : ') . $form_message
                    );
                    $htmlmessage .= html_writer::end_tag('div');
                    // 
                    $response_msg_send = UtilNotification_handler::send_email(
                        $sendto_email,
                        $sendto_name,
                        $msg_subject,
                        $htmlmessage
                    );
                    if ($response_msg_send) {
                        $redirect_msg = "Your message is send sucessfully. We will Get in touch with you shorthly";
                    } else {
                        $redirect_msg = "Email configuration is not completed or Something went wrong !";
                    }
                } else {
                    $redirect_msg = "Required data is missing.";
                }
            } else {
                $redirect_msg = "Session time out.";
            }
            redirect($PAGE->url->out(), $redirect_msg);
        } else {
            $template_content['block_yipl_id'] = $this->block_settings->block_yipl_id;
            $template_content['form_action'] = $PAGE->url->out();
            $template_content['title'] = format_string($this->block_settings->title);
            return $OUTPUT->render_from_template("theme_yipl/blocks/contact-us", $template_content);
        }
    }
    /**
     * course_list
     */
    protected function course_list_block_content()
    {
        global $OUTPUT;

        $course_list = '';
        $courselist_order = isset($this->block_settings->courselist_order) ? explode(',', $this->block_settings->courselist_order) : '';
        if ($courselist_order) {
            $course_list = [];
            foreach ($courselist_order as $key => $course_id) {
                $course_list[] = UtilCourse_handler::course_card_info($course_id, true);
            }
        }


        $template_content = [];
        $template_content['block_yipl_id'] = $this->block_settings->block_yipl_id;
        $template_content['courses'] = $course_list;
        return $OUTPUT->render_from_template("theme_yipl/blocks/course_list", $template_content);
    }

    /**
     * course_info
     */
    protected function course_info_block_content()
    {
        global $OUTPUT, $COURSE, $DB;

        $course_id = empty($this->block_settings->course_list) ? $COURSE->id : end($this->block_settings->course_list);
        $course_fields = explode(",", $this->block_settings->course_fields_order);
        $course_fields_metadata = UtilCourse_handler::get_custom_field_metadata($course_id, "key_array");
        $course_fields_value = [];
        foreach ($course_fields as $key => $field_shortname) {
            if ($field_shortname === 'description_summery') {
                $course = $DB->get_record('course', ['id' => $course_id]);
                $label = 'Summery';
                $value = UtilCourse_handler::get_course_formatted_summary($course);
            } else {
                $label = $course_fields_metadata[$field_shortname]['name'];
                $value = $course_fields_metadata[$field_shortname]['value'];
            }
            if ($field_shortname === 'yipl_intro_video' &&  $value) {
                $video_id = $thumbnail_url = '';
                $video_url = $value;
                if (str_contains($value, 'vimeo.com')) {
                    $video_type = 'vimeo';
                } elseif (str_contains($value, 'youtube.com') || str_contains($value, 'youtu')) {
                    $video_type = 'youtube';
                } else {
                    $video_type = '';
                }
                if ($video_type == 'youtube') {
                    $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*\?v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                    preg_match($pattern, $value, $matches);
                    // preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $value, $matches);
                    $video_id = isset($matches[1]) ? $matches[1] : $video_id;
                    $thumbnail_url = 'http://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
                    $video_url = "https://www.youtube.com/embed/" . $video_id;
                    $value = $video_url;
                }
                if ($video_type == 'vimeo') {
                    // preg_match('/<iframe[^>]+src="([^"]+)"/i', $value, $matches);
                    // $value = isset($matches[1]) ? $matches[1] : $value;
                    // preg_match('/vimeo\.com\/video\/(\d+)/', $embed_video, $matches);
                    $pattern = '/vimeo\.com\/(?:channels\/[\w]+\/|groups\/[\w]+\/videos\/|album\/\d+\/video\/|video\/|)(\d+)(?:\?.*?h=([\w\d]+))?/';
                    // $pattern = '/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(?:channels\/[\w]+\/|groups\/[\w]+\/videos\/|album\/\d+\/video\/|video\/|)(\d+)/';
                    preg_match($pattern, $value, $matches);
                    $video_id = isset($matches[1]) ? $matches[1] : $video_id;
                    $video_id = isset($matches[2]) ? $video_id . '/' . $matches[2] : $video_id;
                    // $thumbnail = self::get_vimeo_data_from_id($video_id, 'thumbnail_url');
                    // if ($thumbnail) {
                    //     $thumbnail_url = $thumbnail;
                    // }
                }
                $course_fields_value[$field_shortname]['thumbnail_url'] = $thumbnail_url;
                // var_dump($value);
                // var_dump($video_type);
                // var_dump($video_id);
                // var_dump($thumbnail_url);
            }
            $course_fields_value[$key][$field_shortname]['label'] = $label;
            $course_fields_value[$key][$field_shortname]['value'] = $value;
        }

        $course_informations = '';
        foreach ($course_fields_value as $key => $fields_value) {
            $course_informations .=  $OUTPUT->render_from_template("theme_yipl/blocks/course_info", $fields_value);
        }
        // 
        $template_content = [];
        $template_content['block_yipl_id'] = $this->block_settings->block_yipl_id;
        $template_content['course_id'] = $course_id;
        // $template_content['course_fields'] = $course_fields_value;
        $template_content['course_informations'] = $course_informations;

        return $OUTPUT->render_from_template("theme_yipl/blocks/course_informations", $template_content);
    }

    /**
     * course_rating
     */
    protected function course_rating_block_content()
    {
        global $OUTPUT, $PAGE;
        $courserating = optional_param('courserating', 0, PARAM_INT);
        if ($courserating == '1') {
            $rating = optional_param('rating', 0, PARAM_INT);
            $feedback = optional_param('feedback', 0, PARAM_TEXT);
            // var_dump($rating);
            // var_dump($feedback);
            $data = [
                'rating' => $rating,
                'feedback' => $feedback
            ];
            courserating_handler::save_data('', $data, $PAGE->url->out());
        }
        $get_path = $PAGE->url->get_path();
        $params = $PAGE->url->params();
        $params['courserating'] = 1;
        $template_content = [];
        $template_content['rating_action'] = (new \moodle_url($get_path, $params))->out(false);
        $template_content['block_yipl_id'] = $this->block_settings->block_yipl_id;
        return $OUTPUT->render_from_template("theme_yipl/blocks/course_rating", $template_content);
    }


    /**
     * faqs 
     */
    protected function faqs_block_content()
    {
        global $OUTPUT;
        $template_content = [];
        $template_content['block_yipl_id'] = $this->block_settings->block_yipl_id;
        $template_content['title'] = $this->block_settings->title;
        $template_content['faq_datas'] = faqs_handler::get_faqs_question_data_in_array(-1);
        return $OUTPUT->render_from_template("theme_yipl/blocks/faqs", $template_content);
    }


    /**
     * testimonial 
     */
    protected function testimonial_block_content()
    {
        global $OUTPUT;
        $template_content = [];
        $template_content['block_yipl_id'] = $this->block_settings->block_yipl_id;
        $template_content['title'] = $this->block_settings->title;
        $template_content['testimonial_datas'] = testimonial_handler::get_data_in_array(-1);
        return $OUTPUT->render_from_template("theme_yipl/blocks/testimonial", $template_content);
    }

    /**
     * start_guideline
     */
    protected function start_guideline_block_content()
    {
        global $OUTPUT;
        $template_content = [];
        $template_content['block_yipl_id'] = $this->block_settings->block_yipl_id;
        $template_content['title'] = $this->block_settings->title;
        $template_content = array_merge($template_content, settings_handler::start_guideline_settings());
        return $OUTPUT->render_from_template("theme_yipl/blocks/start-guideline", $template_content);
    }

    /**
     * Grab the specified data like Thumbnail URL of a publicly embeddable video hosted on Vimeo.
     *
     * @param  str $video_id The ID of a Vimeo video.
     * @param  str $data 	  Video data to be fetched
     * @return str            The specified data
     */
    protected function get_vimeo_data_from_id($video_id, $data)
    {
        if (!$video_id) {
            return '';
        }
        // $request = wp_remote_get('https://vimeo.com/api/oembed.json?url=https://vimeo.com/' . $video_id);
        // $response = wp_remote_retrieve_body($request);
        // $video_array = json_decode($response, true);
        // if ($video_array) {
        //     return $video_array[$data];
        // } else {
        return '';
        // }
    }

    /**
     * ===== END =====
     */
}
