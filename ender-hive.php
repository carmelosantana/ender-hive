<?php
/*
Plugin Name: Ender Hive
Plugin URI: https://enderhive.com/
Description: Minecraft server manager.
Version: 0.1.0-alpha.13
Author: Carmelo Santana
Author URI: https://carmelosantana.com/
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Defines
define('ENDER_HIVE', 'ender-hive');
define('ENDER_HIVE_TITLE', 'Ender Hive');
define('ENDER_HIVE_FILE_PATH', __FILE__);
define('ENDER_HIVE_DIR_PATH', plugin_dir_path(__FILE__));
define('ENDER_HIVE_DIR_URL', plugin_dir_url(__FILE__));

// Composer
if (!file_exists($composer = plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    wp_die(__('Error locating Composer autoloader. Please run <code>composer install</code>.', ENDER_HIVE));
}
require $composer;

// Action Scheduler
if (!file_exists($action_scheduler = plugin_dir_path(__FILE__) . 'vendor-wordpress/action-scheduler/action-scheduler.php')) {
    wp_die(__('Error locating Action Scheduler. Please run <code>composer install</code>.', ENDER_HIVE));
}
require $action_scheduler;

new \CarmeloSantana\EnderHive\EnderHive();
