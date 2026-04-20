<?php
defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', 'logkit_bootstrap' );

function logkit_bootstrap() {
    // Load required files
    require_once LOGKIT_PATH . 'includes/helpers.php';
    require_once LOGKIT_PATH . 'includes/settings.php';
    require_once LOGKIT_PATH . 'includes/rewrite.php';
    require_once LOGKIT_PATH . 'includes/security.php';
    require_once LOGKIT_PATH . 'includes/recovery.php';
}
