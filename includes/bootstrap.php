<?php
defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', 'lcloak_bootstrap' );

function lcloak_bootstrap() {
    $base_path = defined( 'GHOSTGATE_PATH' ) ? GHOSTGATE_PATH : LCLOAK_PATH;
    // Load required files
    require_once $base_path . 'includes/helpers.php';
    require_once $base_path . 'includes/settings.php';
    require_once $base_path . 'includes/rewrite.php';
    require_once $base_path . 'includes/security.php';
    require_once $base_path . 'includes/recovery.php';
}
