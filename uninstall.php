<?php
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Option name(s) used by the plugin
$lcloak_option_names = array( 'lcloak_login_slug', 'lcloak_block_behavior' );

if ( is_multisite() ) {
    $lcloak_sites = function_exists( 'get_sites' ) ? get_sites() : array();
    if ( $lcloak_sites ) {
        foreach ( $lcloak_sites as $lcloak_site ) {
            $blog_id = is_object( $lcloak_site ) ? $lcloak_site->blog_id : (int) $lcloak_site['blog_id'];
            switch_to_blog( $blog_id );
            foreach ( $lcloak_option_names as $lcloak_option_name ) {
                delete_option( $lcloak_option_name );
            }
            restore_current_blog();
        }
    }
} else {
    foreach ( $lcloak_option_names as $lcloak_option_name ) {
        delete_option( $lcloak_option_name );
    }
}
