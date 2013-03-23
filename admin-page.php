<?php

	if ( ! current_user_can( 'manage_options' ) )
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );


	if ( isset( $_POST['bpe-download'] ) ) {
		// output file with terms names or slugs
	}

	$taxonomies = get_taxonomies( array(), 'objects' );
?>
	<div class="wrap">
		<div class="icon32" id="icon-tools"><br /></div>
		<h2><?php _e( 'BulkPress - Export', BPE_TD ); ?></h2>
		<p><?php _e( 'When you click the button below, it will create an formatted file for you to save to your computer.', BPE_TD ); ?></p>
		<p><?php _e( 'Once you\'ve saved the download file, you can use the BulkPress plugin in another WordPress installation to import the content from this site.', BPE_TD ); ?></p>
		<form name="mainform" method="post" action="">
			<input type="hidden" value="true" name="bpe-download">

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="taxonomy"><?php _e( 'Taxonomy', BPE_TD ); ?></label></th>
						<td>
							<select id="taxonomy" name="taxonomy">
								<?php foreach ( $taxonomies as $key => $taxonomy ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>"><?php echo $taxonomy->labels->singular_name; ?></option>
								<?php } ?>
							</select>
							<p class="description"><?php _e( 'Select the taxonomy which you would like to export.', BPE_TD ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="content"><?php _e( 'Content', BPE_TD ); ?></label></th>
						<td>
							<input type="radio" value="names" name="content"> <?php _e( 'Names', BPE_TD ); ?>
							<input type="radio" value="slugs" name="content"> <?php _e( 'Slugs', BPE_TD ); ?>
							<p class="description"><?php _e( 'Choose what to export.', BPE_TD ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="submit">
				<input type="submit" id="submit" name="submit" class="button button-primary" value="<?php _e( 'Download Export File', BPE_TD ); ?>" />
			</p>

		</form>
	</div>
	<div class="clear"></div>
