<?php
/*
Plugin Name: AI Enabler
Description: Create dynamic forms with drag-and-drop element functionality.
Version: 1.2.1
Author: Lezgo AI
License: GPL2
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 6.4
Stable tag: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'RGFB_VERSION', '5.8' );

define( 'RGFB_REQUIRED_WP_VERSION', '6.2' );

define( 'RGFB_TEXT_DOMAIN', 'contact-form-7' );

define( 'RGFB_PLUGIN', __FILE__ );

define( 'RGFB_PLUGIN_BASENAME', plugin_basename( RGFB_PLUGIN ) );

define( 'RGFB_PLUGIN_NAME', trim( dirname( RGFB_PLUGIN_BASENAME ), '/' ) );

define( 'RGFB_PLUGIN_DIR', untrailingslashit( dirname( RGFB_PLUGIN ) ) );

define( 'RGFB_PLUGIN_MODULES_DIR', RGFB_PLUGIN_DIR . '/modules' );

if ( ! defined( 'RGFB_LOAD_JS' ) ) {
	define( 'RGFB_LOAD_JS', true );
}

if ( ! defined( 'RGFB_LOAD_CSS' ) ) {
	define( 'RGFB_LOAD_CSS', true );
}

if ( ! defined( 'RGFB_AUTOP' ) ) {
	define( 'RGFB_AUTOP', true );
}

if ( ! defined( 'RGFB_USE_PIPE' ) ) {
	define( 'RGFB_USE_PIPE', true );
}

if ( ! defined( 'RGFB_ADMIN_READ_CAPABILITY' ) ) {
	define( 'RGFB_ADMIN_READ_CAPABILITY', 'edit_posts' );
}

if ( ! defined( 'RGFB_ADMIN_READ_WRITE_CAPABILITY' ) ) {
	define( 'RGFB_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
}

if ( ! defined( 'RGFB_VERIFY_NONCE' ) ) {
	define( 'RGFB_VERIFY_NONCE', false );
}

if ( ! defined( 'RGFB_USE_REALLY_SIMPLE_CAPTCHA' ) ) {
	define( 'RGFB_USE_REALLY_SIMPLE_CAPTCHA', false );
}

if ( ! defined( 'RGFB_VALIDATE_CONFIGURATION' ) ) {
	define( 'RGFB_VALIDATE_CONFIGURATION', true );
}

define( 'RGFB_PLUGIN_URL',
	untrailingslashit( plugins_url( '', RGFB_PLUGIN ) )
);

require_once RGFB_PLUGIN_DIR . '/load.php';
