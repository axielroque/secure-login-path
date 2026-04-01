<?php
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Option name(s) used by the plugin
$option_names = array( 'lcloak_login_slug', 'lcloak_block_behavior', 'slp_login_slug', 'slp_block_behavior' );

if ( is_multisite() ) {
    $sites = function_exists( 'get_sites' ) ? get_sites() : array();
    if ( $sites ) {
        foreach ( $sites as $site ) {
            $blog_id = is_object( $site ) ? $site->blog_id : (int) $site['blog_id'];
            switch_to_blog( $blog_id );
            foreach ( $option_names as $option_name ) {
                delete_option( $option_name );
            }
            restore_current_blog();
        }
    }
} else {
    foreach ( $option_names as $option_name ) {
        delete_option( $option_name );
    }
}
