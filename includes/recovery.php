<?php
defined( 'ABSPATH' ) || exit;

/**
 * Recovery mode to avoid lockout
 */
add_filter( 'login_url', 'lcloak_append_recovery_param', 10, 1 );
function lcloak_append_recovery_param( $url ) {
    if ( function_exists( 'slp_is_recovery_mode' ) ) {
        $is_recovery_mode = slp_is_recovery_mode();
    } else {
        $is_recovery_mode = lcloak_is_recovery_mode();
    }
    if ( $is_recovery_mode ) {
        $recover_param = add_query_arg( 'lcloak-recover', '1', $url );
        if ( strpos( $recover_param, 'slp-recover' ) === false ) {
            $recover_param = add_query_arg( 'slp-recover', '1', $recover_param );
        }
        return $recover_param;
    }
    return $url;
}
