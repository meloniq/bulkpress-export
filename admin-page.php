<?php

	if ( ! current_user_can( 'manage_options' ) )
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );


	$taxonomies = get_taxonomies( array(), 'objects' );
?>
	<div class="wrap">
		<h1><?php _e( 'BulkPress - Export', 'bulkpress-export' ); ?></h1>
		<p><?php _e( 'When you click the button below, it will create an formatted file for you to save to your computer.', 'bulkpress-export' ); ?></p>
		<p><?php _e( 'Once you\'ve saved the download file, you can use the BulkPress plugin in another WordPress installation to import the content from this site.', 'bulkpress-export' ); ?></p>
		<form name="mainform" method="post" action="">
			<input type="hidden" value="true" name="bpe-download">
			<?php wp_nonce_field( 'bpe-download' ); ?>

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="taxonomy"><?php _e( 'Taxonomy', 'bulkpress-export' ); ?></label></th>
						<td>
							<select id="taxonomy" name="taxonomy">
								<?php foreach ( $taxonomies as $key => $taxonomy ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $taxonomy->labels->singular_name ); ?></option>
								<?php } ?>
							</select>
							<p class="description"><?php _e( 'Select the taxonomy which you would like to export.', 'bulkpress-export' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="content"><?php _e( 'Content', 'bulkpress-export' ); ?></label></th>
						<td>
							<input type="radio" value="names" name="content" checked="checked"> <?php _e( 'Names', 'bulkpress-export' ); ?>
							<input type="radio" value="slugs" name="content"> <?php _e( 'Slugs', 'bulkpress-export' ); ?>
							<p class="description"><?php _e( 'Choose what to export.', 'bulkpress-export' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="submit">
				<input type="submit" id="submit" name="submit" class="button button-primary" value="<?php _e( 'Download Export File', 'bulkpress-export' ); ?>" />
			</p>

		</form>
	</div>
	<div class="clear"></div>
