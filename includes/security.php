<?php
defined( 'ABSPATH' ) || exit;

/**
 * Bloqueo de wp-login.php por defecto
 * DESHABILITADO: El bloqueo se maneja desde template_redirect
 */
// add_action( 'login_init', 'slp_block_default_login', 1 );
// function slp_block_default_login() {
//     // No bloquear si estamos en el contexto del slug personalizado
//     if ( defined( 'SLP_CUSTOM_LOGIN' ) && SLP_CUSTOM_LOGIN ) {
//         return;
//     }
//
//     if ( slp_is_recovery_mode() ) {
//         return;
//     }
//
//     $slug = slp_get_login_slug();
//     if ( empty( $slug ) ) return;
//
//     // Bloquear acceso directo a wp-login.php
//     wp_safe_redirect( home_url( '/' ), 302 );
//     exit;
// }

/**
 * Interceptar /wp-admin temprano para usuarios no autenticados
 * Evita que WordPress redirija a wp-login.php y fuerza volver al home.
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
        wp_safe_redirect( home_url( '/' ), 302 );
        exit;
    }
}

/**
 * Bloqueo de wp-admin para usuarios no logueados
 */
add_action( 'admin_init', 'slp_block_wp_admin' );
function slp_block_wp_admin() {
    if ( slp_is_recovery_mode() ) return;

    $script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( $_SERVER['SCRIPT_NAME'] ) : '';
    $allowed_scripts = apply_filters( 'slp_allowed_admin_scripts', [ 'admin-ajax.php', 'admin-post.php' ] );
    $is_allowed_script = in_array( $script_name, $allowed_scripts, true );
    $is_cron = function_exists( 'wp_doing_cron' ) && wp_doing_cron();

    if ( ! is_user_logged_in() && ! wp_doing_ajax() && ! $is_allowed_script && ! $is_cron ) {
        wp_safe_redirect( home_url( '/' ), 302 );
        exit;
    }
}

/**
 * Noindex en la página de login
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
