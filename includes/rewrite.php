<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'lcloak_register_rewrite_rule', 20 ); // priority 20

function lcloak_register_rewrite_rule() {
    $slug = lcloak_get_login_slug();
    if ( empty( $slug ) ) return;

    // Rewrite custom login path
    add_rewrite_rule(
        '^' . preg_quote( $slug ) . '/?$',
        'index.php?lcloak_login=1',
        'top'
    );
}
add_filter( 'query_vars', 'lcloak_add_query_vars' );
function lcloak_add_query_vars( $vars ) {
    $vars[] = 'lcloak_login';
    $vars[] = 'slp_login';
    return $vars;
}

 

// Note: the login POST can go to wp-login.php; we allow it in lcloak_intercept_direct_login()

// Block direct access to wp-login.php before WordPress processes it
add_action( 'init', 'lcloak_intercept_direct_login', 1 );
function lcloak_intercept_direct_login() {
    // Only act if the request is targeting wp-login.php directly
    $script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    $is_login_script = ( $script_name === 'wp-login.php' );
    $is_login_uri = ( $request_uri && strpos( $request_uri, 'wp-login.php' ) !== false );
    if ( ! $is_login_script && ! $is_login_uri ) {
        return;
    }

    // Allow POST requests so authentication can work
    $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
    if ( $method === 'POST' ) {
        return;
    }

    // Allow only legitimate GET actions
    $action = sanitize_key( (string) filter_input( INPUT_GET, 'action', FILTER_UNSAFE_RAW ) );
    $allowed_actions = [ 'lostpassword', 'rp', 'resetpass', 'register', 'logout', 'postpass', 'verifyemail', 'confirm_admin_email', 'reauth' ];
    $allowed_actions = (array) apply_filters( 'lcloak_allowed_login_actions', $allowed_actions, $action );
    $allowed_actions = (array) apply_filters( 'slp_allowed_login_actions', $allowed_actions, $action );
    if ( in_array( $action, $allowed_actions, true ) ) {
        return;
    }
    // Allow if the referer comes from the custom login path
    $slug = lcloak_get_login_slug();
    if ( ! empty( $slug ) ) {
        $referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
        if ( $referer && strpos( $referer, '/' . $slug . '/' ) !== false ) {
            return;
        }
    }
    
    // Allow recovery mode
    if ( lcloak_is_recovery_mode() ) {
        return;
    }
    
    $slug = lcloak_get_login_slug();
    if ( empty( $slug ) ) return;
    
    // Block according to configured behavior
    lcloak_send_block_response();
}

// Mark custom login context early
add_action( 'parse_request', 'lcloak_mark_custom_login', 1 );
function lcloak_mark_custom_login( $wp ) {
    if ( ( isset( $wp->query_vars['lcloak_login'] ) && $wp->query_vars['lcloak_login'] ) || ( isset( $wp->query_vars['slp_login'] ) && $wp->query_vars['slp_login'] ) ) {
        define( 'LCLOAK_CUSTOM_LOGIN', true );
    }
}

add_action( 'template_redirect', 'lcloak_maybe_render_login' );
function lcloak_maybe_render_login() {
    if ( get_query_var( 'lcloak_login' ) || get_query_var( 'slp_login' ) ) {
        // Initialize variables expected by wp-login.php
        global $error, $interim_login, $action, $user_login;
        $error = '';
        $interim_login = false;
        $action = sanitize_key( (string) filter_input( INPUT_GET, 'action', FILTER_UNSAFE_RAW ) );
        if ( $action === '' ) {
            $action = 'login';
        }
        $user_login = '';
        nocache_headers();
        if ( ! headers_sent() ) {
            header( 'X-Robots-Tag: noindex, nofollow', true );
        }

        require_once ABSPATH . 'wp-login.php';
        exit;
    }
}


