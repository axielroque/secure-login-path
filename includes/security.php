<?php
defined( 'ABSPATH' ) || exit;

/**
 * Intercept /wp-admin early for non-authenticated users.
 * Prevent WordPress from redirecting to wp-login.php and instead apply the configured block behavior.
 */
add_action( 'init', 'slp_intercept_wp_admin', 1 );
function slp_intercept_wp_admin() {
    if ( slp_is_recovery_mode() ) return;

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
    if ( ! $request_uri || strpos( $request_uri, '/wp-admin' ) === false ) {
        return;
    }

    $script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( $_SERVER['SCRIPT_NAME'] ) : '';
    $allowed_scripts = apply_filters( 'slp_allowed_admin_scripts', [ 'admin-ajax.php', 'admin-post.php' ] );
    $is_allowed_script = in_array( $script_name, $allowed_scripts, true );
    $is_cron = function_exists( 'wp_doing_cron' ) && wp_doing_cron();

    if ( ! is_user_logged_in() && ! wp_doing_ajax() && ! $is_allowed_script && ! $is_cron ) {
        slp_send_block_response();
}
}

/**
 * Block wp-admin for non-authenticated users.
 */
add_action( 'admin_init', 'slp_block_wp_admin' );
function slp_block_wp_admin() {
    if ( slp_is_recovery_mode() ) return;

    $script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( $_SERVER['SCRIPT_NAME'] ) : '';
    $allowed_scripts = apply_filters( 'slp_allowed_admin_scripts', [ 'admin-ajax.php', 'admin-post.php' ] );
    $is_allowed_script = in_array( $script_name, $allowed_scripts, true );
    $is_cron = function_exists( 'wp_doing_cron' ) && wp_doing_cron();

    if ( ! is_user_logged_in() && ! wp_doing_ajax() && ! $is_allowed_script && ! $is_cron ) {
        slp_send_block_response();
}
}

/**
 * Add noindex on the login page.
 */
add_action( 'login_init', 'slp_login_send_noindex_header', 1 );
function slp_login_send_noindex_header() {
    if ( ! headers_sent() ) {
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }
}
add_action( 'login_head', 'slp_login_meta_noindex' );
function slp_login_meta_noindex() {
    echo '<meta name="robots" content="noindex,nofollow" />';
}
