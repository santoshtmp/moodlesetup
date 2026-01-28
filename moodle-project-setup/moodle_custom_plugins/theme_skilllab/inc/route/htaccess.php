<?php

defined('MOODLE_INTERNAL') || die();

/**
 * used during install and upgrade
 */
function set_skilllab_htaccess()
{
    require_once(dirname(__FILE__) . '/../../../../config.php');
    global $CFG;
    $htaccess_file_path = $CFG->dirroot . '/.htaccess';
    try {
        if (file_exists($htaccess_file_path)) {
            $contents = file_get_contents($htaccess_file_path);
            $contents = string_except_between_two_string($contents, '# BEGIN_MOODLE_SKL_THEME', '# END_MOODLE_SKL_THEME');
            $update_content = $contents . "\n" . get_default_htaccess_content();
            $update_content = trim($update_content);
            // $file = fopen($htaccess_file_path, "w");
            // fwrite($file, $update_content);
            // fclose($file);
            file_put_contents($htaccess_file_path, $update_content);
        } else {
            $default_contents = get_default_htaccess_content();
            file_put_contents($htaccess_file_path, $default_contents);
        }
        return true;
    } catch (\Exception $e) {
        echo $e->getMessage();
        return false;
    }
    return false;
}

/**
 * used during uninstall
 */
function unset_skilllab_htaccess()
{
    require_once(dirname(__FILE__) . '/../../../../config.php');
    global $CFG;
    $htaccess_file_path = $CFG->dirroot . '/.htaccess';
    try {
        if (file_exists($htaccess_file_path)) {
            $contents = file_get_contents($htaccess_file_path);
            $contents = string_except_between_two_string($contents, '# BEGIN_MOODLE_SKL_THEME', '# END_MOODLE_SKL_THEME');
            $update_content = trim($contents);
            file_put_contents($htaccess_file_path, $update_content);
        }
        return true;
    } catch (\Exception $e) {
        echo $e->getMessage();
        return false;
    }
    return false;
}

/**
 * return string 
 */
function string_except_between_two_string($content_string, $starting_word, $ending_word)
{
    $start_pos = ($start_pos = strpos($content_string, $starting_word)) ? $start_pos : 0;
    $end_pos = strrpos($content_string, $ending_word);
    if ($end_pos) {
        $end_pos += strlen($ending_word);
        $content_string = substr($content_string, 0, $start_pos) . substr($content_string, $end_pos);
    }
    return $content_string;
}

/**
 * get default rule
 */
function get_default_htaccess_content()
{
    $default_contents = "
# BEGIN_MOODLE_SKL_THEME
# DO NOT EDIT route
<IfModule mod_rewrite.c>
# Enable RewriteEngine
Options +FollowSymLinks
Options -MultiViews
Options -Indexes
RewriteEngine on
# All relative URLs are based from root
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /theme/skilllab/inc/route/skl_route.php [L]
RewriteRule ^course/delete$ /theme/skilllab/pages/course/delete.php [L]
ErrorDocument 403 /theme/skilllab/pages/error/404.php
ErrorDocument 404 /theme/skilllab/pages/error/404.php
</IfModule>
# DO NOT EDIT route
# END_MOODLE_SKL_THEME

# Deny access to hidden files - files that start with a dot (.)
<FilesMatch \"^\.\">
Order allow,deny
Deny from all
</FilesMatch>
    ";
    $default_contents = trim($default_contents);
    return $default_contents;
}
