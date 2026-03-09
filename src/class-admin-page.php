<?php
/**
 * Admin Page.
 *
 * @package BulkPress\Export
 */

namespace BulkPress\Export;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Page class.
 */
class Admin_Page {

	/**
	 * Admin page URL.
	 *
	 * @var string
	 */
	public static $admin_page_url = 'tools.php?page=bulkpress-export';

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ), 10 );
		add_action( 'admin_post_bulkpress_export_download', array( $this, 'handle_export' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Add menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_submenu_page(
			'tools.php',
			__( 'BulkPress - Export', 'bulkpress-export' ),
			__( 'BulkPress - Export', 'bulkpress-export' ),
			'manage_options',
			'bulkpress-export',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'BulkPress - Export', 'bulkpress-export' ); ?></h1>
			<p><?php esc_html_e( 'When you click the button below, it will create an formatted file for you to save to your computer.', 'bulkpress-export' ); ?></p>
			<p><?php esc_html_e( 'Once you\'ve saved the download file, you can use the BulkPress plugin in another WordPress installation to import the content from this site.', 'bulkpress-export' ); ?></p>
			<?php $this->render_form_section(); ?>
		</div>
		<?php
	}

	/**
	 * Render the form section.
	 *
	 * @return void
	 */
	protected function render_form_section(): void {
		$taxonomies = get_taxonomies( array(), 'objects' );
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="bulkpress_export_download">
			<?php wp_nonce_field( 'bulkpress-export' ); ?>

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="taxonomy"><?php esc_html_e( 'Taxonomy', 'bulkpress-export' ); ?></label></th>
						<td>
							<select id="taxonomy" name="taxonomy">
								<?php foreach ( $taxonomies as $key => $taxonomy_obj ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $taxonomy_obj->labels->singular_name ); ?></option>
								<?php } ?>
							</select>
							<p class="description"><?php esc_html_e( 'Select the taxonomy which you would like to export.', 'bulkpress-export' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="content"><?php esc_html_e( 'Content', 'bulkpress-export' ); ?></label></th>
						<td>
							<input type="radio" value="names" name="content" checked="checked"> <?php esc_html_e( 'Names', 'bulkpress-export' ); ?>
							<input type="radio" value="slugs" name="content"> <?php esc_html_e( 'Slugs', 'bulkpress-export' ); ?>
							<p class="description"><?php esc_html_e( 'Choose what to export.', 'bulkpress-export' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Download Export File', 'bulkpress-export' ), 'secondary' ); ?>
		</form>

		<?php
	}

	/**
	 * Handle export action.
	 *
	 * @return void
	 */
	public function handle_export(): void {
		// Verify nonce.
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulkpress-export' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'bulkpress-export' ) );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'bulkpress-export' ) );
		}

		// Validate required fields.
		if ( empty( $_POST['taxonomy'] ) || empty( $_POST['content'] ) ) {
			wp_safe_redirect( add_query_arg( 'bpex_error', 'missing_fields', admin_url( self::$admin_page_url ) ) );
			exit;
		}

		// check given content type value.
		$content_types = array( 'names', 'slugs' );
		$content       = wp_kses_data( wp_unslash( $_POST['content'] ) );
		if ( ! in_array( $content, $content_types, true ) ) {
			wp_safe_redirect( add_query_arg( 'bpex_error', 'invalid_content_type', admin_url( self::$admin_page_url ) ) );
			exit;
		}

		// check given taxonomy value.
		$taxonomies = get_taxonomies( array(), 'names' );
		$taxonomy   = wp_kses_data( wp_unslash( $_POST['taxonomy'] ) );
		if ( ! in_array( $taxonomy, $taxonomies, true ) ) {
			wp_safe_redirect( add_query_arg( 'bpex_error', 'invalid_taxonomy', admin_url( self::$admin_page_url ) ) );
			exit;
		}

		// output file with terms names or slugs.
		$terms = bpe_get_terms_array( $taxonomy, $content );
		bpe_export( $terms );
		die();
	}

	/**
	 * Display admin notices.
	 *
	 * @return void
	 */
	public function admin_notices(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'tools_page_bulkpress-export' !== $screen->id ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['bpex_error'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$error = sanitize_text_field( wp_unslash( $_GET['bpex_error'] ) );

			$messages = array(
				'missing_fields'       => __( 'Please fill in all required fields.', 'bulkpress-export' ),
				'invalid_content_type' => __( 'Invalid content type selected.', 'bulkpress-export' ),
				'invalid_taxonomy'     => __( 'Invalid taxonomy selected.', 'bulkpress-export' ),
				'default'              => __( 'An error occurred.', 'bulkpress-export' ),
			);

			$message = $messages[ $error ] ?? $messages['default'];
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<?php
		}
	}
}
