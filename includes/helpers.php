<?php
defined( 'ABSPATH' ) || exit;

function logkit_get_login_slug() {
    $slug = get_option( 'logkit_login_slug' );
    return sanitize_title( (string) $slug );
}

function logkit_generate_random_slug() {
    return wp_generate_password( 14, false );
}

function logkit_is_recovery_mode() {
    $recover = filter_input( INPUT_GET, 'logkit-recover', FILTER_SANITIZE_NUMBER_INT );
    return $recover === '1';
}

function logkit_send_block_response() {
    $behavior = (string) get_option( 'logkit_block_behavior', 'redirect' );
    switch ( $behavior ) {
        case '404':
            if ( ! headers_sent() ) {
                nocache_headers();
                status_header( 404 );
            }
            wp_die(
                esc_html__( 'Not Found', 'logkit' ),
                esc_html__( 'Not Found', 'logkit' ),
                [ 'response' => 404 ]
            );
            break;
        case '403':
            if ( ! headers_sent() ) {
                nocache_headers();
                status_header( 403 );
            }
            wp_die(
                esc_html__( 'Forbidden', 'logkit' ),
                esc_html__( 'Forbidden', 'logkit' ),
                [ 'response' => 403 ]
            );
            break;
        case 'redirect':
        default:
            wp_safe_redirect( home_url( '/' ), 302 );
            exit;
    }
}
