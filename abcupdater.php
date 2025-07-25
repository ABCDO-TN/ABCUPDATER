<?php
/**
 * Plugin Name:       ABCUPDATER
 * Description:       Manages automatic updates for multiple themes and plugins from private or public GitHub repositories.
 * Version:           0.13.3
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Plugin URI:        http://abcdo.tn/abcupdater
 * Author:            ABCDO
 * Author URI:        http://abcdo.tn
 * Text Domain:       abcupdater
 * License:           MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define the main plugin file path.
if ( ! defined( 'ABCUPDATER_PLUGIN_FILE' ) ) {
    define( 'ABCUPDATER_PLUGIN_FILE', __FILE__ );
}

// Include the main plugin class.
require_once dirname( __FILE__ ) . '/includes/class-abcupdater-main.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function abcupdater_run() {
    return \ABCUPDATER\Includes\ABCUPDATER_Main::instance();
}

// Run the plugin.
abcupdater_run();

/**
 * Activation hook.
 *
 * Checks for minimum WordPress and PHP versions.
 */
register_activation_hook( __FILE__, 'abcupdater_activation_check' );
function abcupdater_activation_check() {
    $min_wp_version = '5.5';
    $min_php_version = '7.4';
    global $wp_version;

    if ( version_compare( $wp_version, $min_wp_version, '<' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( sprintf( esc_html__( 'Could not activate %1$s. This plugin requires WordPress version %2$s or later. Your site is running version %3$s.', 'abcupdater' ), '<strong>ABCUPDATER</strong>', esc_html( $min_wp_version ), esc_html( $wp_version ) ), 'Plugin Activation Error', [ 'back_link' => true ] );
    }

    if ( version_compare( PHP_VERSION, $min_php_version, '<' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( sprintf( esc_html__( 'Could not activate %1$s. This plugin requires PHP version %2$s or later. Your site is running version %3$s.', 'abcupdater' ), '<strong>ABCUPDATER</strong>', esc_html( $min_php_version ), esc_html( PHP_VERSION ) ), 'Plugin Activation Error', [ 'back_link' => true ] );
    }
}
