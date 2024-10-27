<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
if ( is_admin() ) {
	require_once RGFB_PLUGIN_DIR . '/admin/admin.php';
} else {
	require_once RGFB_PLUGIN_DIR . '/includes/controller.php';
}