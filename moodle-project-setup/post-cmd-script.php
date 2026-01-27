<?php

/**
 * post-cmd-script.php
 *
 * Usage:
 *   composer install   # automatically runs this script
 *   composer update    # automatically runs this script
 *
 */
declare(strict_types=1);

use moodle_project_setup as setup;

require_once __DIR__ . '/MoodleProjectSetup.php';


// ---------------------------
// Start script
// ---------------------------

echo "📦 Starting Moodle code manage script... \n";

// check vendor core moodle 
if (!is_dir(setup::get_vendor_moodle())) {
  echo "❌ Error: Moodle not found in vendor/moodle/moodle \n";
  exit(1);
}

// Delete existing web directory entirely before copying core
if (is_dir(setup::get_moodle_dir())) {
  echo "⚠️ Cleaning existing moodle code directory...\n";
  setup::rrRemove(setup::get_moodle_dir());
  echo "✅ Clean moodle code directory.\n";
}

// Create web dir i.e Ensure web directory exists
if (!is_dir(setup::get_moodle_dir())) {
  @mkdir(setup::get_moodle_dir(), 0777, true);
  echo "✅ Created moodle code directory\n";
}

// ---------------------------
// Copy Moodle core
// ---------------------------
echo "➡️ Copying Moodle core files...\n";
setup::rrCopy(setup::get_vendor_moodle(), setup::get_moodle_dir());
echo "✅ Completed. \n";
