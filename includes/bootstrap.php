<?php
defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', 'lcloak_bootstrap' );

function lcloak_bootstrap() {
    // Load required files
    require_once LCLOAK_PATH . 'includes/helpers.php';
    require_once LCLOAK_PATH . 'includes/settings.php';
    require_once LCLOAK_PATH . 'includes/rewrite.php';
    require_once LCLOAK_PATH . 'includes/security.php';
    require_once LCLOAK_PATH . 'includes/recovery.php';
}
