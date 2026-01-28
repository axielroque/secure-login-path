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
