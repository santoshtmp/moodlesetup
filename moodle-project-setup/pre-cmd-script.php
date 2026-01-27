<?php

declare(strict_types=1);

use moodle_project_setup as setup;

require_once __DIR__ . '/MoodleProjectSetup.php';
echo " Clean ignored files : \n";
foreach (setup::get_exclude_remove_paths() as $key => $value) {
    # code...
    echo " - ".$value . " \n";
}