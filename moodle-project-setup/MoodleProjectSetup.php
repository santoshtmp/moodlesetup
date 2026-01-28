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
    public static function get_vendor_moodle_core() {
        return realpath(__DIR__ . '/vendor/moodle/moodle');
    }

    public static function get_vendor_moodle_plugin() {
        return realpath(__DIR__ . '/vendor/moodle_plugin');
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
        return self::get_installed_packages('moodle/moodle', true) > 5.1 ? self::get_moodle_dir() . '/public' : self::get_moodle_dir();
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
            self::get_moodle_dir() . '/.git',
            self::get_moodle_dir() . '/node_modules',
            self::get_moodle_dir() . '/.gitignore',
            self::get_moodle_dir() . '/README.md',
            self::get_moodle_dir() . '/.env',
            self::get_moodle_dir() . '/.htaccess',
            self::get_moodle_dir() . '/config.php',
            self::get_moodle_public_dir() . '/blocks/yipl',
            self::get_moodle_public_dir() . '/theme/skilllab',
            self::get_moodle_public_dir() . '/theme/yipl',
        ];
        return array_filter($excludeRemovePath);
    }

    /**
     * 
     */
    public static function get_installed_packages($packageName = '', $only_version = false) {
        $installed = require __DIR__ . '/vendor/composer/installed.php';
        if ($packageName && isset($installed['versions']) && is_array($installed['versions'])) {
            foreach ($installed['versions'] as $key => $package) {
                if ($key === $packageName) {
                    return $only_version ? $package['version'] : $package;
                }
            }
        }
        return $installed;
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
        try {
            if (!is_dir($src)) {
                return;
            }
            $excludeRemovePath = self::get_exclude_remove_paths();
            @mkdir($dst, 0777, true);
            foreach (scandir($src) as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $srcPath = $src . DIRECTORY_SEPARATOR . $item;
                $dstPath = $dst . DIRECTORY_SEPARATOR . $item;

                if (in_array(realpath($dstPath), $excludeRemovePath, true)) {
                    continue;
                }

                if (is_dir($srcPath)) {
                    self::rrCopy($srcPath, $dstPath);
                } else {
                    copy($srcPath, $dstPath);
                    chmod($dstPath, fileperms($srcPath));
                }
            }
        } catch (\Throwable $th) {
            echo "Error :: " . $th->getMessage() . " \n";
            exit(1);
        }
    }

    /**
     * Recursively remove a directory and its contents,
     * skipping excluded files or directories.
     *
     * @param string $dir Directory to remove
     * @return void
     */
    public static function rrRemove(string $dir) {
        if (!is_dir($dir)) {
            return;
        }
        $excludeRemovePath = self::get_exclude_remove_paths();
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


    /**
     * 
     */
    public static function manage_moodle_core_files() {
        echo "➡️ Copying Moodle core files...\n";
        self::rrCopy(self::get_vendor_moodle_core(), self::get_moodle_dir());
        echo "✅Completed copying Moodle Core files. \n";
    }


    /**
     * 
     */
    public static function manage_moodle_plugins_files() {
        echo "➡️ Copying Moodle plugins files...\n";
        // Manage contributed plugin
        $moodle_plugin = self::get_vendor_moodle_plugin();
        $moodle_public_dir = self::get_moodle_public_dir();
        foreach (scandir($moodle_plugin) as $plugin_name) {
            if ($plugin_name[0] === '.') {
                continue;
            }
            $srcPlugin = $moodle_plugin . DIRECTORY_SEPARATOR . $plugin_name;
            $dstPlugin = $moodle_public_dir . "/" . str_replace("_", "/", $plugin_name);
            self::rrCopy($srcPlugin, $dstPlugin);
        }
        // Manage custom plugin
        $moodle_custom_plugin = realpath(__DIR__ . '/moodle_custom_plugins');
        foreach (scandir($moodle_custom_plugin) as $plugin_name) {
            if ($plugin_name[0] === '.') {
                continue;
            }
            $srcPlugin = $moodle_custom_plugin . DIRECTORY_SEPARATOR . $plugin_name;
            $dstPlugin = $moodle_public_dir . "/" . str_replace("_", "/", $plugin_name);
            self::rrCopy($srcPlugin, $dstPlugin);
        }
        echo "✅Completed copying Moodle plugins files. \n";
    }

    /**
     * === END ===
     */
}
