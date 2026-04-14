<?php
/*
Plugin Name: GhostGate – Protect & Hide Your WordPress Login Access
Plugin URI: https://github.com/axielroque/login-cloak
Description: GhostGate helps you secure your WordPress website by hiding the default login URLs (/wp-admin and /wp-login.php) and replacing them with a custom access path known only to you.
Version: 1.1.1
Author: Axiel Roque
Author URI: https://github.com/axielroque
License: GPL v2 or later
Text Domain: axiel-secure-login-path
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'LCLOAK_PATH', plugin_dir_path( __FILE__ ) );
define( 'LCLOAK_URL', plugin_dir_url( __FILE__ ) );

// Load bootstrap
require_once LCLOAK_PATH . 'includes/bootstrap.php';

// Activation
register_activation_hook( __FILE__, 'lcloak_activate' );
function lcloak_activate() {
    if ( ! get_option( 'lcloak_login_slug' ) ) {
        update_option( 'lcloak_login_slug', wp_generate_password( 14, false ) );
    }

    if ( ! function_exists( 'lcloak_get_login_slug' ) ) {
        require_once LCLOAK_PATH . 'includes/helpers.php';
    }
    if ( ! function_exists( 'lcloak_register_rewrite_rule' ) ) {
        require_once LCLOAK_PATH . 'includes/rewrite.php';
    }
    if ( function_exists( 'lcloak_register_rewrite_rule' ) ) {
        lcloak_register_rewrite_rule();
    }

    flush_rewrite_rules();
}

// Deactivation
register_deactivation_hook( __FILE__, 'lcloak_deactivate' );
function lcloak_deactivate() {
    flush_rewrite_rules();
}
