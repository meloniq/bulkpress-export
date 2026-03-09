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

namespace BulkPress\Export;

// If this file is accessed directly, then abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin version and textdomain constants
 */
define( 'BPE_VERSION', '0.4' );
define( 'BPE_TD', 'bulkpress-export' );


/**
 * Setup.
 *
 * @return void
 */
function bpe_init() {
	global $bulkpress_export;

	require_once __DIR__ . '/src/class-admin-page.php';

	$bulkpress_export['admin-page'] = new Admin_Page();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\bpe_init' );

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
