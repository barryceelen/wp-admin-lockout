<?php
/**
 * Main plugin file
 *
 * @package   Admin Lockout
 * @author    Barry Ceelen
 * @license   GPL-3.0+
 * @link      https://github.com/barryceelen/wp-admin-lockout
 * @copyright Barry Ceelen
 *
 * Plugin Name: Admin Lockout
 * Plugin URI: https://github.com/barryceelen/wp-admin-lockout
 * Description: Activate this plugin to prevent access to the WordPress admin for all users except yourself.
 * Author: Barry Ceelen
 * Version: 1.0.0
 * Author URI: https://github.com/barryceelen
 * Text Domain: admin-lockout
 * Domain Path: /languages/
 */

// Don't load directly.
defined( 'ABSPATH' ) or die();

// Runs when the plugin is activated.
register_activation_hook( __FILE__, 'admin_lockout_activate' );

// Runs when the plugin is deactivated.
register_deactivation_hook( __FILE__, 'admin_lockout_deactivate' );

// Should the current user be locked out?
add_action( 'admin_init', 'admin_lockout' );

// Maybe remove option if a user is deleted because you never know, right?
add_action( 'deleted_user', 'admin_lockout_on_deleted_user' );

/**
 * Runs when the plugin is activated.
 *
 * @since 1.0.0
 */
function admin_lockout_activate() {

	if ( current_user_can( 'manage_options' ) ) {

		$current_user = wp_get_current_user();

		if ( ! empty( $current_user->ID ) ) {
			update_option( 'plugin_admin_lockout', $current_user->ID );
		}
	}
}

/**
 * Runs when the plugin is deactivated.
 *
 * @since 1.0.0
 */
function admin_lockout_deactivate() {

	delete_option( 'plugin_admin_lockout' );
}

/**
 * Should the current user be locked out?
 *
 * @since 1.0.0
 */
function admin_lockout() {

	$option = get_option( 'plugin_admin_lockout' );

	if ( ! empty( $option ) ) {

		$current_user = wp_get_current_user();

		if ( absint( $option ) !== absint( $current_user->ID ) ) {

			$message = apply_filters( 'admin_lockout_message', __( 'Access to the admin area is temporarily disabled.', 'admin-lockout' ) );
			$title   = apply_filters( 'admin_lockout_title', __( 'Temporary Maintenance', 'admin-lockout' ) );

			wp_die( esc_html( $message), esc_html( $title ), 403 );
		}
	}
}

/**
 * Maybe remove option if a user is deleted because you never know, right?
 *
 * @since 1.0.0
 * @param int $id ID of the deleted user.
 */
function admin_lockout_on_deleted_user( $id ) {

	$option = get_option( 'plugin_admin_lockout' );

	if ( ! empty( $option ) && (int) $option === (int) $id ) {
		delete_option( 'plugin_admin_lockout' );
	}
}
