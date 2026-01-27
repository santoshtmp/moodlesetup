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
 * @package   theme_yipl   
 * @copyright 2025 YIPL
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * https://moodledev.io/docs/4.5/apis/core/message
 */

namespace theme_yipl\util;

use core\output\html_writer;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die;

class UtilNotification_handler
{
    /**
     * send messages notification within Moodle
     * need to configure notification by admin
     * @param object $user
     * @param string $msg_subject
     * @param string $fullmessage
     */
    public static function send_message($user, $msg_subject, $fullmessage)
    {

        $message = new \core\message\message();
        $message->component = 'theme_yipl'; // Your plugin's name
        $message->name =  'yipl_notification_handler'; // Your notification name from message.php
        $message->userfrom = \core\user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $user;
        $message->subject = $msg_subject;
        $message->fullmessage = '';
        $message->fullmessageformat = FORMAT_HTML; // FORMAT_MARKDOWN  FORMAT_HTML
        $message->fullmessagehtml = $fullmessage;
        $message->smallmessage = '';
        $message->notification = 1; // 0 = personal message, 1 = notification
        // $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification
        // $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl
        $content = array('*' => array('header' => '  ', 'footer' => '  ')); // Extra content for specific processor
        $message->set_additional_content('email', $content);

        // // You probably don't need attachments but if you do, here is how to add one
        // $usercontext = \context_user::instance($user->id);
        // $file = new stdClass();
        // $file->contextid = $usercontext->id;
        // $file->component = 'user';
        // $file->filearea = 'private';
        // $file->itemid = 0;
        // $file->filepath = '/';
        // $file->filename = '1.txt';
        // $file->source = 'test';
        // $fs = get_file_storage();
        // $file = $fs->create_file_from_string($file, 'file1 content');
        // $message->attachment = $file;

        // Actually send the message
        $messageid = message_send($message);

        return $messageid;
    }

    /**
     * the message is sent via email used in contact us form or other
     * send email
     */
    public static function send_email(
        $sendto_email,
        $sendto_name,
        $subject,
        $htmlmessage,
        $no_reply = ''
    ) {
        global $CFG, $USER, $SITE, $PAGE;
        $systemcontext = \context_system::instance();
        // Create the recipient.
        $to_user = self::make_emailuser($sendto_email, $sendto_name);
        // Create the sender from the submitted name and email address.
        $site_short_name =  format_text($SITE->shortname, FORMAT_HTML, ['context' => $systemcontext]);
        $from = self::make_emailuser('', $site_short_name);
        // check no reply 
        $no_reply = ($no_reply) ?: $CFG->noreplyaddress;
        // add message footer
        $htmlmessage .= '<hr>';
        $htmlmessage .= html_writer::tag(
            'p',
            "Message From " . $site_short_name . " : " . html_writer::link($PAGE->url->out(), $PAGE->heading) . " Page."
        );
        $htmlmessage .=  $PAGE->url->out();
        // 
        $response = email_to_user(
            $to_user,
            $from,
            $subject,
            $messagetext = '',
            $messagehtml = $htmlmessage,
            $attachment = '',
            $attachname = '',
            $usetrueaddress = true,
            $replyto = $no_reply,
            $replytoname = $no_reply,
            $wordwrapwidth = 79
        );

        return $response;
    }

    /**
     * Creates a user info object based on provided parameters.
     *
     * @param      string  $email  email address.
     * @param      string  $name   (optional) Plain text real name.
     * @param      int     $id     (optional) Moodle user ID.
     * @return     object  Moodle userinfo.
     */
    public static function make_emailuser($email, $name = '', $id = -99)
    {
        $emailuser = new stdClass();
        $emailuser->email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailuser->email = '';
        }
        $emailuser->firstname = format_text($name, FORMAT_PLAIN, array('trusted' => false));
        $emailuser->lastname = '';
        $emailuser->maildisplay = true;
        $emailuser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML emails.
        $emailuser->id = $id;
        $emailuser->firstnamephonetic = '';
        $emailuser->lastnamephonetic = '';
        $emailuser->middlename = '';
        $emailuser->alternatename = '';
        $emailuser->username = '';
        return $emailuser;
    }

    /**
     * callback_api_fail_notification
     */
    public static function callback_api_fail_notification($subject, $full_message)
    {
        $api_fail_notify_user_id = get_config('theme_yipl', 'api_fail_notify_user_id');
        $api_fail_notify_user_ids = explode(',', $api_fail_notify_user_id);
        if (is_array($api_fail_notify_user_ids)) {
            foreach ($api_fail_notify_user_ids as $user_id) {
                $user_id = (int)$user_id;
                $notify_user = \core\user::get_user($user_id);
                if ($notify_user) {
                    self::send_message($notify_user, $subject, $full_message);
                }
            }
        }
    }

    /**
     * ============= END ===============
     */
}
