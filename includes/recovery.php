<?php
defined( 'ABSPATH' ) || exit;

/**
 * Recovery mode to avoid lockout
 */
add_filter( 'login_url', 'logkit_append_recovery_param', 10, 1 );
function logkit_append_recovery_param( $url ) {
    if ( logkit_is_recovery_mode() ) {
        return add_query_arg( 'logkit-recover', '1', $url );
    }
    return $url;
}
