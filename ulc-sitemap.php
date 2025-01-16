<?php
/**
 * ULC Sitemap
 *
 * @package           ULCSitemap
 * @author            Patrick Aziken
 * @copyright         2025 Patrick Aziken
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       ULC Sitemap
 * Plugin URI:        https://upperloftcreations.com/ulc-sitemap
 * Description:       A fast and secure XML sitemap generator for WordPress
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      8.2
 * Author:            Patrick Aziken
 * Author URI:        https://github.com/skylarng89
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://upperloftcreations.com/ulc-sitemap
 * Text Domain:       ulc-sitemap
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('ULC_SITEMAP_VERSION', '1.0.0');
define('ULC_SITEMAP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ULC_SITEMAP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once ULC_SITEMAP_PLUGIN_DIR . 'includes/class-ulc-sitemap-autoloader.php';

// Initialize the plugin
function ulc_sitemap_init(): void {
    $plugin = new ULC_Sitemap\Core\Plugin();
    $plugin->run();
}

// Hook into WordPress init
add_action('plugins_loaded', 'ulc_sitemap_init');

// Register activation hook
register_activation_hook(__FILE__, function(): void {
    require_once ULC_SITEMAP_PLUGIN_DIR . 'includes/class-ulc-sitemap-activator.php';
    ULC_Sitemap\Core\Activator::activate();
});

// Register deactivation hook
register_deactivation_hook(__FILE__, function(): void {
    require_once ULC_SITEMAP_PLUGIN_DIR . 'includes/class-ulc-sitemap-deactivator.php';
    ULC_Sitemap\Core\Deactivator::deactivate();
});