<?php

/**
 * DO NOT EDIT
 * api for moodle lms 
 * - login with encrypted user id 
 * - check user by username and password
 */

use theme_skilllab\util\UtilTheme_handler;

define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_DEBUG_DISPLAY', true);

@header('Access-Control-Allow-Origin: *');

require_once(dirname(__FILE__) . '/../../../config.php');
require_once(dirname(__FILE__) . '/../lib.php');

global $CFG, $PAGE;
$PAGE->set_url(new moodle_url('/theme/skilllab/pages/login.php'));

// only allow POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field
    $user_id = $_POST['id'];
    $back_url = $_POST['back_url'];
    $goto_url = $_POST['goto_url'];
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    // decrypt the encrypted value
    $user_id = (int)decrypt($user_id);
    // perform authenticate user and complete user login
    check_username_password();
    $user_login = user_login($user_id);
    if ($user_login) {
        // if type is "enroll" or "resume" perform user enroll check and enroll in the course
        if ($type == 'enroll' || $type == 'resume') {
            user_enrollment($user_id, $goto_url);
        }
        $userid = $user_login->id;
        $sesskey = $user_login->sesskey;
        // set user csc site in cookies
        UtilTheme_handler::set_skl_theme_cookie('user_csc_site', get_user_csc_redirect());
        // redirect_to => goto url
        if ($goto_url) {
            redirect_to($goto_url);
        } else {
            $output_login = [
                'status' => false,
                'message' => 'can not find goto url; it must be send through csc site.'
            ];
            output_json($output_login);
        }
    } else {
        return_back($message = 'login process failed.');
    }
} else {
    $redirect_other = $CFG->wwwroot;
    // get user institution sub domain
    $institution_domain = '';
    if ($USER->profile['institution_domain']) {
        $institution_domain =  $USER->profile['institution_domain'] . '.';
    } else {
        // check and get default value
        $defaultdata = $DB->get_record('user_info_field', ['shortname' => 'institution_domain'], 'defaultdata');
        if ($defaultdata->defaultdata) {
            $institution_domain = $defaultdata->defaultdata . '.';
        }
    }
    // theme setting
    $theme = theme_config::load('skilllab');
    $site_environment =  $theme->settings->site_environment;
    if ($site_environment == 2) {
        $redirect_other = $CFG->wwwroot . '/my/courses.php';
    }
    if ($site_environment == 0) {
        $redirect_other = 'https://' . $institution_domain . 'stage.careerservicelab.com/login';
    }
    if ($site_environment == 1) {
        $redirect_other = 'https://' . $institution_domain . 'careerservicelab.com/login';
    }
    redirect_to($redirect_other);
}


/**
 * return status json and die the process
 */
function check_username_password()
{
    $credential_important = (int)$_POST['credential_important'];
    if ($credential_important == 1) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if ($username and $password) {
            $reason = null;
            $user = authenticate_user_login($username, $password, false, $reason, false);
            if ($user) {
                complete_user_login($user);
                $output_login = [
                    'status' => true,
                    'user_data' => $user
                ];
            } else {
                $output_login = [
                    'status' => false,
                    'credential' => 'invalid'
                ];
            }
            output_json($output_login);
        }
    }
}

/**
 * 
 */
function output_json($output_login)
{
    echo json_encode($output_login);
    die;
}

/**
 * @param $username, $password
 * return false or auth user data 
 */
function user_login($userid)
{
    global $DB, $USER;

    if ($authenticate_user =  check_authenticate_user($userid)) {
        if ($authenticate_user->id == $USER->id) {
            return $USER;
        } else {
            // Let's get them all set up.
            complete_user_login($authenticate_user);
            // \core\session\manager::apply_concurrent_login_limit($authenticate_user->id, session_id());
            // set_moodle_cookie($authenticate_user->username);
            // $sid = session_id();
            // call csc api to set session in csc site or not
            return $authenticate_user;
        }
    } else {
        return false;
    }

    return false;
}

/**
 * check user authenticate
 */
function check_authenticate_user($userid)
{
    global $DB;
    if ($DB->record_exists('user', array('id' => $userid, 'deleted' => 0, 'suspended' => 0))) {
        $authenticate_user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0, 'suspended' => 0]);
        return  $authenticate_user;
    }
    return false;
}


/**
 * enroll user in the course
 */
function user_enrollment($user_id, $goto_url)
{
    global $CFG, $DB;
    require_once($CFG->libdir . '/enrollib.php');

    if (!(str_contains($goto_url, 'course/view.php?id='))) {
        return_back($message = "course path is not correct");
    }
    $goto_url = explode('id=', $goto_url);
    $course_id = (int)filter_var($goto_url[1], FILTER_SANITIZE_NUMBER_INT);     // Filter the Numbers from String
    $role_id = 5; //student role id

    $sys_context = context_system::instance();
    // check user role as system institution role
    if (has_capability('theme/skilllab:instructor_view', $sys_context)) {
        // $role_id = 10; //institution role id
        $role_data = $DB->get_record('role', ['shortname' => 'institution']);
        $role_id = ($role_data) ? $role_data->id : $role_id;
    }

    if (has_capability('theme/skilllab:skill_lab_viewer', $sys_context)) {
        $role_data = $DB->get_record('role', ['shortname' => 'skill_lab_viewer']);
        $role_id = ($role_data) ? $role_data->id : $role_id;
    }

    if (has_capability('theme/skilllab:skill_lab_editor', $sys_context)) {
        $role_data = $DB->get_record('role', ['shortname' => 'skill_lab_editor']);
        $role_id = ($role_data) ? $role_data->id : $role_id;
    }

    if (has_capability('theme/skilllab:institution_viewer', $sys_context)) {
        $role_data = $DB->get_record('role', ['shortname' => 'institution_viewer']);
        $role_id = ($role_data) ? $role_data->id : $role_id;
    }

    if (has_capability('theme/skilllab:institution_editor', $sys_context)) {
        $role_data = $DB->get_record('role', ['shortname' => 'institution_admin']);
        $role_id = ($role_data) ? $role_data->id : $role_id;
    }


    // check if course exist
    $course_doesnot = true;
    if ($DB->record_exists('course', array('id' =>  $course_id))) {
        $course_doesnot = false;
    }
    if ($course_doesnot) {
        $message = 'course does not exist';
        return_back($message);
    }

    // check user
    if (!check_authenticate_user($user_id)) {
        $message = "Unauthenticate user - user does not exist";
        return_back($message);
    }


    // Check manual enrolment plugin instance is enabled/exist.
    $instance = null;
    $enrolinstances = enrol_get_instances($course_id, true);
    foreach ($enrolinstances as $courseenrolinstance) {
        if ($courseenrolinstance->enrol == "manual") {
            $instance = $courseenrolinstance;
            break;
        }
    }
    if (empty($instance)) {
        $message = 'enrollment process is not allowed';
        return_back($message);
    }

    // check if user is alrady enrolled in course;
    // check given user is student in the course
    // $student_role = false;
    $alrady_enrolled = false;
    $context = \context_course::instance($course_id, IGNORE_MISSING);
    $get_user_roles = get_user_roles($context, $user_id);
    foreach ($get_user_roles as $key => $role) {
        if ($role->roleid == $role_id) {
            $message = 'given user is alrady enrolled as student';
            $alrady_enrolled = true;
            // return_back($message);
            // return true;
        }
    }
    if (!$alrady_enrolled) {
        try {
            $transaction = $DB->start_delegated_transaction();

            // Retrieve the manual enrolment plugin.
            $enrol = enrol_get_plugin("manual");
            $enrol->enrol_user(
                $instance,
                $user_id,
                $role_id
            );
            $transaction->allow_commit();
        } catch (\Exception $e) {
            $transaction->rollback($e);
            $message = 'something went wrong';
            return_back($message);
        }
    }
}


/**
 * return back with message
 */
function return_back($message = '')
{
    global $CFG;
    $return_url = $_POST['back_url'];
    if (empty($return_url) || str_contains($return_url, $CFG->wwwroot)) {
        $return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $return_url;
    }

    if (empty($return_url)) {
        // $return_url = $CFG->wwwroot . '/login/index.php';
        // redirect_to($return_url, $message);
        $output_login = [
            'status' => false,
            'message' => 'something went wrong'
        ];
        output_json($output_login);
    }

    if (str_contains($return_url, "?")) {
        if ($message) {
            if (str_contains($return_url, '&msg=')) {
                $return_url = explode('&msg=', $return_url)[0];
                $return_url = $return_url . '&msg=' . $message;
            } else {
                $return_url = $return_url . '&msg=' . $message;
            }
        }
    } else {
        if ($message) {
            $return_url = $return_url . '?msg=' . $message;
        }
    }

    redirect_to($return_url);
    // header('Location: ' . $return_url);
    // die;
}


/**
 * redirect and die
 */
function redirect_to($url = '/')
{
    $redirectby = 'skilllab-csc-lms-moodle';
    @header("X-Redirect-By: $redirectby");
    // 302 might not work for POST requests, 303 is ignored by obsolete clients.
    @header($_SERVER['SERVER_PROTOCOL'] . ' 303 See Other');
    @header('Location: ' . $url);
    exit;
}
