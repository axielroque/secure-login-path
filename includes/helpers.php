<?php
defined( 'ABSPATH' ) || exit;

function lcloak_get_login_slug() {
    $slug = get_option( 'lcloak_login_slug' );
    if ( ! $slug ) {
        $slug = get_option( 'slp_login_slug' );
    }
    return sanitize_title( (string) $slug );
}

function lcloak_generate_random_slug() {
    return wp_generate_password( 14, false );
}

function lcloak_is_recovery_mode() {
    $recover = filter_input( INPUT_GET, 'lcloak-recover', FILTER_SANITIZE_NUMBER_INT );
    if ( $recover === null ) {
        $recover = filter_input( INPUT_GET, 'slp-recover', FILTER_SANITIZE_NUMBER_INT );
    }
    return $recover === '1';
}

function lcloak_send_block_response() {
    $behavior = (string) get_option( 'lcloak_block_behavior', '' );
    if ( $behavior === '' ) {
        $behavior = (string) get_option( 'slp_block_behavior', 'redirect' );
    }
    switch ( $behavior ) {
        case '404':
            if ( ! headers_sent() ) {
                nocache_headers();
                status_header( 404 );
            }
            wp_die(
                esc_html__( 'Not Found', 'login-cloak' ),
                esc_html__( 'Not Found', 'login-cloak' ),
                [ 'response' => 404 ]
            );
            break;
        case '403':
            if ( ! headers_sent() ) {
                nocache_headers();
                status_header( 403 );
            }
            wp_die(
                esc_html__( 'Forbidden', 'login-cloak' ),
                esc_html__( 'Forbidden', 'login-cloak' ),
                [ 'response' => 403 ]
            );
            break;
        case 'redirect':
        default:
            wp_safe_redirect( home_url( '/' ), 302 );
            exit;
    }
}
