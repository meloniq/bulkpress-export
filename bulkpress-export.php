<?php
/*
	Plugin Name: BulkPress - Export
	Plugin URI: http://blog.meloniq.net/
	Description: Export taxonomies into formatted file compatible with BulkPress plugin.
	Author: MELONIQ.NET
	Author URI: http://blog.meloniq.net
	Version: 1.0
	License: GPLv2 or later
*/


/**
 * Avoid calling file directly
 */
if ( ! function_exists( 'add_action' ) )
	die( 'Whoops! You shouldn\'t be doing that.' );


/**
 * Plugin version and textdomain constants
 */
define( 'BPE_VERSION', '0.1' );
define( 'BPE_TD', 'bulkpress-export' );


/**
 * Load Text-Domain
 */
load_plugin_textdomain( BPE_TD, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


/**
 * Initialize admin menu
 */
if ( is_admin() ) {
	add_action( 'admin_menu', 'bpe_add_menu_links' );
}


/**
 * Populate administration menu of the plugin
 */
function bpe_add_menu_links() {

	add_management_page( __( 'BulkPress - Export', BPE_TD ), __( 'BulkPress - Export', BPE_TD ), 'administrator', 'bulkpress-export', 'bpe_menu_settings' );
}


/**
 * Create settings page in admin
 */
function bpe_menu_settings() {

	include_once( dirname( __FILE__ ) . '/admin-page.php' );
}


