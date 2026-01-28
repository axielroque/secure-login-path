<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'slp_register_rewrite_rule', 20 ); // prioridad 20

function slp_register_rewrite_rule() {
    $slug = slp_get_login_slug();
    if ( empty( $slug ) ) return;

    // Reescribir URL personalizada
    add_rewrite_rule(
        '^' . preg_quote( $slug ) . '/?$',
        'index.php?slp_login=1',
        'top'
    );
}
add_filter( 'query_vars', 'slp_add_query_vars' );
function slp_add_query_vars( $vars ) {
    $vars[] = 'slp_login';
    return $vars;
}

 

// Nota: el formulario POST puede ir a wp-login.php; lo permitimos en slp_intercept_direct_login()

// Bloquear acceso directo a wp-login.php antes de que WordPress lo procese
add_action( 'init', 'slp_intercept_direct_login', 1 );
function slp_intercept_direct_login() {
    // Solo actuar si estamos accediendo a wp-login.php directamente
    $script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( $_SERVER['SCRIPT_NAME'] ) : '';
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
    $is_login_script = ( $script_name === 'wp-login.php' );
    $is_login_uri = ( $request_uri && strpos( $request_uri, 'wp-login.php' ) !== false );
    if ( ! $is_login_script && ! $is_login_uri ) {
        return;
    }

    // Permitir peticiones POST para que el proceso de autenticación funcione
    $method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : 'GET';
    if ( $method === 'POST' ) {
        return;
    }

    // Permitir solo acciones GET legítimas
    $action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : '';
    $allowed_actions = [ 'lostpassword', 'rp', 'resetpass', 'register', 'logout', 'postpass', 'verifyemail', 'confirm_admin_email', 'reauth' ];
    $allowed_actions = (array) apply_filters( 'slp_allowed_login_actions', $allowed_actions, $action );
    if ( in_array( $action, $allowed_actions, true ) ) {
        return;
    }
    // Permitir si el referer proviene del slug personalizado
    $slug = slp_get_login_slug();
    if ( ! empty( $slug ) ) {
        $referer = isset( $_SERVER['HTTP_REFERER'] ) ? (string) $_SERVER['HTTP_REFERER'] : '';
        if ( $referer && strpos( $referer, '/' . $slug . '/' ) !== false ) {
            return;
        }
    }
    
    // Permitir recovery mode
    if ( slp_is_recovery_mode() ) {
        return;
    }
    
    $slug = slp_get_login_slug();
    if ( empty( $slug ) ) return;
    
    // Bloquear y redirigir
    wp_safe_redirect( home_url( '/' ), 302 );
    exit;
}

// Marcar contexto del slug personalizado temprano
add_action( 'parse_request', 'slp_mark_custom_login', 1 );
function slp_mark_custom_login( $wp ) {
    if ( isset( $wp->query_vars['slp_login'] ) && $wp->query_vars['slp_login'] ) {
        define( 'SLP_CUSTOM_LOGIN', true );
    }
}

add_action( 'template_redirect', 'slp_maybe_render_login' );
function slp_maybe_render_login() {
    if ( get_query_var( 'slp_login' ) ) {
        // Inicializar variables esperadas por wp-login.php
        global $error, $interim_login, $action, $user_login;
        $error = '';
        $interim_login = false;
        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
        $user_login = '';
        nocache_headers();
        header( 'X-Robots-Tag: noindex, nofollow', true );

        require_once ABSPATH . 'wp-login.php';
        exit;
    }
}


