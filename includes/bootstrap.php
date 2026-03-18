<?php
defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', 'slp_bootstrap' );

function slp_bootstrap() {
    // Load required files
    require_once SLP_PATH . 'includes/helpers.php';
    require_once SLP_PATH . 'includes/settings.php';
    require_once SLP_PATH . 'includes/rewrite.php';
    require_once SLP_PATH . 'includes/security.php';
    require_once SLP_PATH . 'includes/recovery.php';
}
