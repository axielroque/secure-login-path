<?php
defined( 'ABSPATH' ) || exit;

/**
 * Intercept /wp-admin early for non-authenticated users.
 * Prevent WordPress from redirecting to wp-login.php and instead apply the configured block behavior.
 */
add_action( 'init', 'logkit_intercept_wp_admin', 1 );
function logkit_intercept_wp_admin() {
    if ( logkit_is_recovery_mode() ) return;

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    if ( ! $request_uri || strpos( $request_uri, '/wp-admin' ) === false ) {
        return;
    }

    $script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';
    $allowed_scripts = apply_filters( 'logkit_allowed_admin_scripts', [ 'admin-ajax.php', 'admin-post.php' ] );
    $is_allowed_script = in_array( $script_name, $allowed_scripts, true );
    $is_cron = function_exists( 'wp_doing_cron' ) && wp_doing_cron();

    if ( ! is_user_logged_in() && ! wp_doing_ajax() && ! $is_allowed_script && ! $is_cron ) {
        logkit_send_block_response();
    }
}

/**
 * Block wp-admin for non-authenticated users.
 */
add_action( 'admin_init', 'logkit_block_wp_admin_for_visitors' );
function logkit_block_wp_admin_for_visitors() {
    if ( logkit_is_recovery_mode() ) return;

    if ( is_user_logged_in() ) {
        return;
    }

    $script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';
    $allowed_scripts = apply_filters( 'logkit_allowed_admin_scripts', [ 'admin-ajax.php', 'admin-post.php' ] );
    $is_allowed_script = in_array( $script_name, $allowed_scripts, true );
    $is_cron = function_exists( 'wp_doing_cron' ) && wp_doing_cron();

    if ( ! is_user_logged_in() && ! wp_doing_ajax() && ! $is_allowed_script && ! $is_cron ) {
        logkit_send_block_response();
    }
}

/**
 * Add noindex on the login page.
 */
add_action( 'login_init', 'logkit_login_send_noindex_header', 1 );
function logkit_login_send_noindex_header() {
    if ( ! headers_sent() ) {
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }
}
add_action( 'login_head', 'logkit_login_meta_noindex' );
function logkit_login_meta_noindex() {
    echo '<meta name="robots" content="noindex,nofollow" />';
}
