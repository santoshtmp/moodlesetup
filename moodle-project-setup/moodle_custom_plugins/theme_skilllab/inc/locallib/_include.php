<?php

use theme_skilllab\util\UtilTheme_handler;

defined('MOODLE_INTERNAL') || die();

// require all other skill lab local lib files
require_once(dirname(__FILE__) . '/user_course_info.php');
require_once dirname(__FILE__) . '/callback.php';
require_once dirname(__FILE__) . '/quiz_rules.php';

/**
 * decrypt the given value
 */
function decrypt($value)
{
    return UtilTheme_handler::encrypt_decrypt_value($value, 'decrypt');
}

/**
 * encrypt the given value
 */
function encryptValue($value)
{
    return UtilTheme_handler::encrypt_decrypt_value($value, 'encrypt');
}
