<?php
/*
Plugin Name: Secure Login Path
Plugin URI: https://github.com/axielroque/secure-login-path
Description: Secure and customize the WordPress login URL without modifying core files.
Version: 1.1.0
Author: Axiel Roque
Author URI: https://github.com/axielroque
License: GPL v2 or later
Text Domain: secure-login-path
*/

defined( 'ABSPATH' ) || exit;

// Constantes del plugin
define( 'SLP_PATH', plugin_dir_path( __FILE__ ) );
define( 'SLP_URL', plugin_dir_url( __FILE__ ) );

// Cargar text domain (i18n)
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'secure-login-path', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

// Cargar bootstrap
require_once SLP_PATH . 'includes/bootstrap.php';

// Activación
register_activation_hook( __FILE__, 'slp_activate' );
function slp_activate() {
    if ( ! get_option( 'slp_login_slug' ) ) {
        update_option( 'slp_login_slug', wp_generate_password( 14, false ) );
    }

    if ( function_exists( 'slp_register_rewrite_rule' ) ) {
        slp_register_rewrite_rule();
    }

    flush_rewrite_rules();
}

// Desactivación
register_deactivation_hook( __FILE__, 'slp_deactivate' );
function slp_deactivate() {
    flush_rewrite_rules();
}
