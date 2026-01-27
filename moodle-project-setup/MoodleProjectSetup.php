<?php

/**
 * Moodle Project Setup Utilities.
 *
 * Helper methods used by Composer post-install / post-update scripts
 * to copy Moodle core, manage contributed pluginds, and safely clean up
 * files while respecting exclusion rules.
 *
 * @package     moodle-project-setup
 * @author      santoshtmp7@gmail.com
 * @copyright   2026@santoshtmp7@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version     1.0.0
 */

declare(strict_types=1);

class moodle_project_setup {

    /**
     * Get the absolute path to Moodle core installed via Composer.
     *
     * @return string|false Absolute path to vendor/moodle/moodle or false if not found
     */
    public static function get_vendor_moodle() {
        return realpath(__DIR__ . '/vendor/moodle/moodle');
    }

    /**
     * Get the Moodle root directory.
     * Creates it if missing.
     *
     * @return string|false
     */
    public static function get_moodle_dir() {
        $moodleDir = __DIR__ . '/../';

        if (!is_dir($moodleDir)) {
            @mkdir($moodleDir, 0777, true);
        }
        return realpath($moodleDir);
    }

    /**
     * Get Moodle public directory.
     *
     * @return string|false
     */
    public static function get_moodle_public_dir() {
        return realpath(self::get_moodle_dir() . '/public');
    }

    /**
     * Get a list of absolute paths that should be excluded
     * when removing files or directories.
     *
     * Only existing paths are returned.
     *
     * @return string[] Array of absolute paths to exclude
     */
    public static function get_exclude_remove_paths(): array {
        $excludeRemovePath =  [
            realpath(__DIR__ . '/../moodle-project-setup'),
            self::get_moodle_dir() . '/public/blocks/yipl',
            self::get_moodle_dir() . '/public/theme/skilllab',
            self::get_moodle_dir() . '/public/theme/yipl',
            self::get_moodle_dir() . '/theme',
            self::get_moodle_dir() . '/node_modules',
            self::get_moodle_dir() . '.gitignore',
            self::get_moodle_dir() . '.env',
            self::get_moodle_dir() . '.htaccess',
            self::get_moodle_dir() . 'config.php',
        ];
        return array_filter($excludeRemovePath);
    }


    /**
     * Execute a shell command and stop execution on failure.
     *
     * @param string $cmd Shell command to run
     * @return void
     */
    function run(string $cmd): void {
        echo "▶ $cmd\n";
        exec($cmd, $output, $status);
        if ($status !== 0) {
            echo "❌ Command failed\n";
            throw new RuntimeException('Command failed: ' . $cmd);
            exit(1);
        }
    }

    /**
     * Recursively copy files and directories.
     * Preserves file permissions.
     *
     * @param string $src Source directory
     * @param string $dst Destination directory
     * @return void
     */
    public static function rrCopy(string $src, string $dst): void {
        if (!is_dir($src)) {
            return;
        }
        @mkdir($dst, 0777, true);
        foreach (scandir($src) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $srcPath = $src . DIRECTORY_SEPARATOR . $item;
            $dstPath = $dst . DIRECTORY_SEPARATOR . $item;
            if (is_dir($srcPath)) {
                self::rrCopy($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
                chmod($dstPath, fileperms($srcPath));
            }
        }
    }

    /**
     * Recursively remove a directory and its contents,
     * skipping excluded files or directories.
     *
     * @param string $dir Directory to remove
     * @param string[] $excludeRemovePath Absolute paths to exclude
     * @return void
     */
    public static function rrRemove(string $dir, array $excludeRemovePath) {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = realpath($dir . DIRECTORY_SEPARATOR . $item);
            if (!$path) {
                continue;
            }

            if (in_array($path, $excludeRemovePath, true)) {
                continue; // skip excluded
            }

            if (is_dir($path)) {
                self::rrRemove($path, $excludeRemovePath);
            } else {
                unlink($path);
            }
        }

        // Remove the directory itself if it's not excluded
        if (!in_array(realpath($dir), $excludeRemovePath, true)) {
            @rmdir($dir);
        }
    }
}
