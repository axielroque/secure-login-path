<?php
defined( 'ABSPATH' ) || exit;

/**
 * Recovery mode to avoid lockout
 */
add_filter( 'login_url', 'slp_append_recovery_param', 10, 1 );
function slp_append_recovery_param( $url ) {
    if ( slp_is_recovery_mode() ) {
        return add_query_arg( 'slp-recover', '1', $url );
    }
    return $url;
}
