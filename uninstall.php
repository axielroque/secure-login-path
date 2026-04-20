<?php
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Option name(s) used by the plugin
$logkit_option_names = array( 'logkit_login_slug', 'logkit_block_behavior' );

if ( is_multisite() ) {
    $logkit_sites = function_exists( 'get_sites' ) ? get_sites() : array();
    if ( $logkit_sites ) {
        foreach ( $logkit_sites as $logkit_site ) {
            $blog_id = is_object( $logkit_site ) ? $logkit_site->blog_id : (int) $logkit_site['blog_id'];
            switch_to_blog( $blog_id );
            foreach ( $logkit_option_names as $logkit_option_name ) {
                delete_option( $logkit_option_name );
            }
            restore_current_blog();
        }
    }
} else {
    foreach ( $logkit_option_names as $logkit_option_name ) {
        delete_option( $logkit_option_name );
    }
}
