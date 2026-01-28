<?php
// require_once

use theme_skilllab\csc_api\update_enroll_user;
use theme_skilllab\local\skl_time_track;
use theme_skilllab\util\UtilNotification_handler;
use theme_skilllab\util\UtilTheme_handler;

require_once(__DIR__ . '/../../config.php');

// require_once($CFG->dirroot . '/theme/skilllab/lib.php');

global $CFG, $PAGE, $USER, $DB;
$PAGE->set_url(new moodle_url('/theme/skilllab/test.php'));
require_once($CFG->dirroot . '/theme/skilllab/lib.php');