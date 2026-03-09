<?php
/**
 * Plugin Name:       BulkPress - Export
 * Plugin URI:        https://blog.meloniq.net/
 *
 * Description:       Export taxonomies into formatted file compatible with BulkPress plugin.
 * Tags:              bulkpress, export, taxonomy, terms
 *
 * Requires at least: 4.9
 * Requires PHP:      7.4
 * Version:           0.4
 *
 * Author:            MELONIQ.NET
 * Author URI:        https://meloniq.net/
 *
 * License:           GPLv2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain:       bulkpress-export
 *
 * @package BulkPress\Export
 */

/**
 * Avoid calling file directly
 */
if ( ! function_exists( 'add_action' ) ) {
	die( 'Whoops! You shouldn\'t be doing that.' );
}


/**
 * Plugin version and textdomain constants
 */
define( 'BPE_VERSION', '0.4' );
define( 'BPE_TD', 'bulkpress-export' );


/**
 * Initialize admin menu.
 */
if ( is_admin() ) {
	add_action( 'admin_menu', 'bpe_add_menu_links' );
}


/**
 * Populate administration menu of the plugin.
 */
function bpe_add_menu_links() {

	add_management_page( __( 'BulkPress - Export', 'bulkpress-export' ), __( 'BulkPress - Export', 'bulkpress-export' ), 'manage_options', 'bulkpress-export', 'bpe_menu_settings' );
}


/**
 * Create settings page in admin.
 */
function bpe_menu_settings() {

	include_once __DIR__ . '/admin-page.php';
}


/**
 * Creates array of terms paths or slugs.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $content Type of content to export, either 'names' or 'slugs'.
 *
 * @return array Array of terms paths or slugs.
 */
function bpe_get_terms_array( $taxonomy, $content ) {

	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$terms_args = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
	);

	// get all terms for the taxonomy.
	$terms = get_terms( $terms_args );
	$paths = array();
	$slugs = array();

	if ( empty( $terms ) ) {
		return array();
	}

	foreach ( $terms as $key => $term ) {
		if ( 0 === $term->parent ) {
			$paths[] = bpe_esc_name( $term->name );
		} else {
			$paths[] = bpe_walk_terms( $terms, $term, '' );
		}
		$slugs[] = $term->slug;
	}
	array_multisort( $paths, $slugs );

	return ( 'names' === $content ) ? $paths : $slugs;
}


/**
 * Creates term path, helper function for bpe_get_taxonomies_array().
 *
 * @param array  $terms All terms for the taxonomy.
 * @param object $current_term Current term for which the path is being created.
 * @param string $path Current path, used for recursive calls, should be empty when calling function for the first time.
 *
 * @return string
 */
function bpe_walk_terms( $terms, $current_term, $path ) {
	$path = ( empty( $path ) ) ? bpe_esc_name( $current_term->name ) : bpe_esc_name( $current_term->name ) . '/' . $path;

	if ( 0 === $current_term->parent ) {
		return $path;
	}

	foreach ( $terms as $term ) {
		if ( $current_term->parent === $term->term_id ) {
			$path = bpe_walk_terms( $terms, $term, $path );
			break;
		}
	}

	return $path;
}


/**
 * Escapes name for use in path.
 *
 * @param string $name Name to escape.
 *
 * @return string
 */
function bpe_esc_name( $name ) {
	$name = str_replace( '&amp;', '&', $name );
	$name = str_replace( '/', '\/', $name );

	return $name;
}


/**
 * Generate and send .txt file to user browser.
 *
 * @param array $content Array of lines to output in the file.
 *
 * @return void
 */
function bpe_export( $content = array() ) {
	$sitename = sanitize_key( get_bloginfo( 'name' ) );
	$filename = $sitename . '-bulkpress-export-' . gmdate( 'Y-m-d' ) . '.txt';

	header( 'Content-Description: File Transfer' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ), true );

	echo implode( "\r\n", $content ); // phpcs:ignore
}


/**
 * Listener for file download request.
 */
function bpe_listen_export() {
	if ( ! isset( $_POST['bpe-download'] ) ) {
		return;
	}

	if ( empty( $_POST['taxonomy'] ) || empty( $_POST['content'] ) ) {
		return;
	}

	// check nonce.
	if ( ! isset( $_POST['_wpnonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( wp_kses_data( wp_unslash( $_POST['_wpnonce'] ) ), 'bpe-download' ) ) {
		return;
	}

	// check given content type value.
	$content_types = array( 'names', 'slugs' );
	$content       = wp_kses_data( wp_unslash( $_POST['content'] ) );
	if ( ! in_array( $content, $content_types, true ) ) {
		return;
	}

	// check given taxonomy value.
	$taxonomies = get_taxonomies( array(), 'names' );
	$taxonomy   = wp_kses_data( wp_unslash( $_POST['taxonomy'] ) );
	if ( ! in_array( $taxonomy, $taxonomies, true ) ) {
		return;
	}

	// output file with terms names or slugs.
	$terms = bpe_get_terms_array( $taxonomy, $content );
	bpe_export( $terms );
	die();
}
add_action( 'admin_init', 'bpe_listen_export' );
