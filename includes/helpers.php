<?php
defined( 'ABSPATH' ) || exit;

function slp_get_login_slug() {
    return sanitize_title( get_option( 'slp_login_slug' ) );
}

function slp_generate_random_slug() {
    return wp_generate_password( 14, false );
}

function slp_is_recovery_mode() {
    return isset( $_GET['slp-recover'] ) && $_GET['slp-recover'] === '1';
}

function slp_send_block_response() {
    $behavior = (string) get_option( 'slp_block_behavior', 'redirect' );
    switch ( $behavior ) {
        case '404':
            if ( ! headers_sent() ) {
                nocache_headers();
                status_header( 404 );
            }
            wp_die(
                esc_html__( 'Not Found', 'secure-login-path' ),
                esc_html__( 'Not Found', 'secure-login-path' ),
                [ 'response' => 404 ]
            );
            break;
        case '403':
            if ( ! headers_sent() ) {
                nocache_headers();
                status_header( 403 );
            }
            wp_die(
                esc_html__( 'Forbidden', 'secure-login-path' ),
                esc_html__( 'Forbidden', 'secure-login-path' ),
                [ 'response' => 403 ]
            );
            break;
        case 'redirect':
        default:
            wp_safe_redirect( home_url( '/' ), 302 );
            exit;
    }
}
