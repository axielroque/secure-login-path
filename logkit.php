<?php
/*
Plugin Name: Logkit - Protect & Hide Your Login Access
Plugin URI: https://github.com/axielroque/login-cloak
Description: Logkit helps you secure your website by hiding the default login URLs (/wp-admin and /wp-login.php) and replacing them with a custom path only you know.
Version: 1.1.1
Author: Axiel Roque
Author URI: https://github.com/axielroque
License: GPL v2 or later
Text Domain: logkit
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'LOGKIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'LOGKIT_URL', plugin_dir_url( __FILE__ ) );

// Load bootstrap
require_once LOGKIT_PATH . 'includes/bootstrap.php';

// Activation
register_activation_hook( __FILE__, 'logkit_activate' );
function logkit_activate() {
    if ( ! get_option( 'logkit_login_slug' ) ) {
        update_option( 'logkit_login_slug', wp_generate_password( 14, false ) );
    }

    if ( function_exists( 'logkit_register_rewrite_rule' ) ) {
        logkit_register_rewrite_rule();
    }

    flush_rewrite_rules();
}

// Deactivation
register_deactivation_hook( __FILE__, 'logkit_deactivate' );
function logkit_deactivate() {
    flush_rewrite_rules();
}
