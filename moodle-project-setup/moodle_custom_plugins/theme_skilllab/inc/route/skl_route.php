<?php

/**
 * Custom route handler
 */

$http_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$url_path = parse_url($uri, PHP_URL_PATH);
$url_query = ($url_query = parse_url($uri, PHP_URL_QUERY)) ? '?' . $url_query : '';
$url = $http_protocol . '://' . $host . $uri;
$projectRoot = isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : dirname(__DIR__, 4);
$_SERVER['DOCUMENT_ROOT'] = $projectRoot;

/**
 * define new url path and the actual page url path
 */
function get_skl_define_route() {
    $routes = [
        '/category' => '/theme/skilllab/pages/category/index.php',
        '/category/edit' => '/theme/skilllab/pages/category/edit.php',
        '/category/delete' => '/theme/skilllab/pages/category/delete.php',
        '/lms-admin-login' => '/theme/skilllab/pages/login/admin-login.php',
        '/logout' => '/theme/skilllab/pages/login/csc-lms-logout.php',
        '/course/delete' => '/theme/skilllab/pages/course/delete.php',
        '/time-track-report' => '/theme/skilllab/pages/report/time_track.php',
        '/student-course-report' => '/theme/skilllab/pages/report/index.php',
        '/scholarship' => '/theme/skilllab/pages/scholarship/index.php',
        '/career_road_map' => '/theme/skilllab/pages/career_road_map/index.php'
    ];

    return $routes;
}

/**
 * skilllab_error_page
 */
if (!function_exists('skilllab_error_page')) {
    function skilllab_error_page() {
        global $CFG;
        header("HTTP/1.0 404 Not Found");
        http_response_code('404');
        $_SERVER['REDIRECT_STATUS'] = '404';
        $filepath = $CFG->dirroot . '/theme/skilllab/pages/error/404.php';
        chdir(dirname($filepath));
        require($filepath);
        die();
    }
}

/**
 * check if new path and file exist or not
 */
$get_file_exists = false;
$route_define = get_skl_define_route();
foreach ($route_define as $new_path => $actual_path) {
    if (($url_path == $new_path) || ($url_path == $new_path . '/')) {
        $filepath = $projectRoot . $actual_path;
        if (file_exists($filepath)) {
            chdir(dirname($filepath));
            require($filepath);
            die();
        }
    }
}

/**
 * check if php file is present in path
 */
if (str_contains($url_path, '.php')) {
    $moodleStaticScripts = [
        'styles.php',
        'javascript.php',
        'jquery.php',
        'requirejs.php',
        'font.php',
        'image.php',
        'yui_combo.php',
        'pluginfile.php',
        'draftfile.php'
    ];

    foreach ($moodleStaticScripts as $script) {
    }


    $filepath = $projectRoot . explode('.php', $url_path)[0] . '.php';
    if (file_exists($filepath)) {
        chdir(dirname($filepath));
        require($filepath);
        die();
    }
}

/**
 * dir_path the path as directory 
 */
$dir_path = $projectRoot . $url_path;
if (is_dir($dir_path)) {
    $files = scandir($dir_path);
    foreach ($files as $filename) {
        if ($filename === 'index.html' || $filename === 'index.php') {
            $path_info_folder = pathinfo($filename);
            $filepath = $dir_path . '/index.' . $path_info_folder['extension'];
            chdir(dirname($filepath));
            require($filepath);
            die();
        }
    }
}

/**
 * at last redirect to 404 page if the path is not found
 */
// if (!$get_file_exists) {
// if (!file_exists($dir_path) && !is_dir($dir_path)) {}
// }
skilllab_error_page();
