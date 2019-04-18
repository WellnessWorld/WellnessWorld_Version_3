<?php

$editable = ! in_array( $post->post_status, array( 'active', 'finished' ) );
if ( isset( $_GET['showstats'] ) && $_GET['showstats'] ) {
	$editable = false;
}

?>
<?php if ( $editable ) : ?>

	<span class="spinner" id="colorschema-ajax-loading"></span>
	<p>
		<label>
		<input name="mailster_data[embed_images]" id="mailster_data_embed_images" value="1" type="checkbox" <?php echo ( isset( $this->post_data['embed_images'] ) ) ? ( ( $this->post_data['embed_images'] ) ? 'checked' : '' ) : ( mailster_option( 'embed_images' ) ? 'checked' : '' ) ?>> <?php esc_html_e( 'Embed Images', 'mailster' ) ?>
		</label>
	</p>
	<p>
		<label>
		<input name="mailster_data[track_opens]" id="mailster_data_track_opens" value="1" type="checkbox" <?php echo ( isset( $this->post_data['track_opens'] ) ) ? ( ( $this->post_data['track_opens'] ) ? 'checked' : '' ) : ( mailster_option( 'track_opens' ) ? 'checked' : '' ) ?>> <?php esc_html_e( 'Track Opens', 'mailster' ) ?>
		</label>
	</p>
	<p>
		<label>
		<input name="mailster_data[track_clicks]" id="mailster_data_track_clicks" value="1" type="checkbox" <?php echo ( isset( $this->post_data['track_clicks'] ) ) ? ( ( $this->post_data['track_clicks'] ) ? 'checked' : '' ) : ( mailster_option( 'track_clicks' ) ? 'checked' : '' ) ?>> <?php esc_html_e( 'Track Clicks', 'mailster' ) ?>
		</label>
	</p>
	<label><?php esc_html_e( 'Colors', 'mailster' );?></label> <a class="savecolorschema"><?php esc_html_e( 'save this schema', 'mailster' ) ?></a>

<?php
	$html = $this->templateobj->get( true );
	$colors = array();
	preg_match_all( '/#[a-fA-F0-9]{6}/', $html, $hits );
	$original_colors = array_keys( array_count_values( $hits[0] ) );
	$original_names = array();

foreach ( $original_colors as $i => $color ) {
	preg_match( '/' . $color . '\/\*([^*]+)\*\//', $html, $x );
	$original_names[ $i ] = isset( $x[1] ) ? $x[1] : '';
}
?>
	<ul class="colors<?php if ( count( array_count_values( $original_names ) ) > 1 ) { echo ' has-labels'; } ?>" data-original-colors='<?php echo json_encode( $original_colors ) ?>'>
<?php

	$html = $post->post_content;

if ( ! empty( $html ) && isset( $this->post_data['template'] ) && $this->post_data['template'] == $this->get_template() && $this->post_data['file'] == $this->get_file() ) {
	preg_match_all( '/#[a-fA-F0-9]{6}/', $html, $hits );
	$current_colors = array_keys( array_count_values( $hits[0] ) );
} else {
	$current_colors = $original_colors;
}

foreach ( $current_colors as $i => $color ) {
	$value = strtoupper( $color );
	$colors[] = $value;

	?>
	<li class="mailster-color" id="mailster-color-<?php echo strtolower( substr( $value, 1 ) ) ?>">
	<label title="<?php echo isset( $original_names[ $i ] ) ? $original_names[ $i ] : '' ?>"><?php echo isset( $original_names[ $i ] ) ? $original_names[ $i ] : '' ?></label>
	<input type="text" class="form-input-tip color" name="mailster_data[newsletter_color][<?php echo esc_attr( $color ) ?>]"  value="<?php echo esc_attr( $value ) ?>" data-value="<?php echo esc_attr( $value ) ?>" data-default-color="<?php echo esc_attr( $value ) ?>">
	<a class="default-value mailster-icon" href="#" tabindex="-1"></a>
	</li>
	<?php
}
?>
	</ul>
	<div class="clear"></div>
	<p>
		<label><?php esc_html_e( 'Colors Schemas', 'mailster' );?></label>
		<?php
		$customcolors = get_option( 'mailster_colors' );
		if ( isset( $customcolors[ $this->get_template() ] ) ) : ?>
			<a class="colorschema-delete-all"><?php esc_html_e( 'Delete all custom schemas', 'mailster' ) ?></a>
		<?php endif; ?>
	</p>
	<ul class="colorschema" title="<?php esc_html_e( 'original', 'mailster' ) ?>">
	<?php
	$original_colors_temp = array();
	foreach ( $original_colors as $i => $color ) {
		$color = strtolower( $color );
		$original_colors_temp[] = $color;
		?>
		<li class="colorschema-field" title="<?php echo isset( $original_names[ $i ] ) ? $original_names[ $i ] : '' ?>" data-hex="<?php echo $color ?>" style="background-color:<?php echo $color ?>"></li>
		<?php
	}
	?>
	</ul>
	<?php if ( strtolower( implode( '', $original_colors_temp ) ) != strtolower( implode( '', $current_colors ) ) ) : ?>
		<ul class="colorschema" title="<?php esc_html_e( 'current', 'mailster' ) ?>">
			<?php foreach ( $colors as $i => $color ) {	?>
				<li class="colorschema-field" title="<?php echo isset( $original_names[ $i ] ) ? $original_names[ $i ] : '' ?>" data-hex="<?php echo strtolower( $color ) ?>" style="background-color:<?php echo $color ?>"></li>
			<?php }	?>
		</ul>
	<?php endif; ?>

	<?php if ( isset( $customcolors[ $this->get_template() ] ) ) : ?>
		<?php foreach ( $customcolors[ $this->get_template() ] as $hash => $colorschema ) : ?>
		<ul class="colorschema custom" data-hash="<?php echo $hash ?>">
		<?php foreach ( $colorschema as $i => $color ) { ?>
			<li class="colorschema-field" title="<?php echo isset( $original_names[ $i ] ) ? $original_names[ $i ] : '' ?>" data-hex="<?php echo strtolower( $color ) ?>" style="background-color:<?php echo $color ?>"></li>
		<?php } 	?>
		<li class="colorschema-delete-field"><a class="colorschema-delete">&#10005;</a></li>
		</ul>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php /*
	?>

	<hr>
	<label><?php esc_html_e( 'Background', 'mailster' ) ?></label><br>

	<?php $value = ( isset( $this->post_data['background'] ) && $this->post_data['template'] == $this->get_template() ) ? $this->post_data['background'] : '';
	?>
	<input type="hidden" id="mailster_background" name="mailster_data[background]" value="<?php echo $value ?>">
	<ul class="backgrounds">
		<li><a style="background-image:<?php echo ( 'none' == $value || empty( $value ) ) ? 'none' : 'url(' . $value . ')' ?>"></a>
		<?php

		$custombgs = MAILSTER_UPLOAD_DIR . '/backgrounds';
		$custombgsuri = MAILSTER_UPLOAD_URI . '/backgrounds';

		if ( ! is_dir( $custombgs ) ) {
			wp_mkdir_p( $custombgs );
		}

		if ( $files = list_files( $custombgs ) ) : ?>
			<ul data-base="<?php echo $custombgsuri ?>">
				<li>
					<a title="<?php esc_html_e( 'none', 'mailster' ) ?>" data-file="" <?php if ( ! $value ) {echo ' class="active"'; }?>><?php esc_html_e( 'none', 'mailster' ) ?></a>
				</li>

		<?php
			sort( $files );

		foreach ( $files as $file ) :

			if ( ! in_array( strrchr( $file, '.' ), array( '.png', '.gif', '.jpg', '.jpeg' ) ) ) {
				continue;
			}

			$value = ( isset( $this->post_data['background'] ) ) ? $this->post_data['background'] : false;
			$file = str_replace( $custombgs, '', $file );
			?>
			<li>
			<a title="<?php echo basename( $file ); ?>" data-file="<?php echo $file ?>" style="background-image:url(<?php echo $custombgsuri . $file ?>)"<?php if ( $custombgsuri . $file == $value ) {	echo ' class="active"'; } ?>>&nbsp;</a>
			</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		</li>

	</ul>
	<p class="howto tiny"><?php esc_html_e( 'background images are not displayed on all clients!', 'mailster' ) ?></p>

	<?php */ ?>

<?php else : ?>

	<p>
		<?php if ( $this->post_data['embed_images'] ) { ?>&#10004;<?php } else { ?>&#10005;<?php }?> <?php esc_html_e( 'Embedded Images', 'mailster' ) ?>
	</p>
	<p>
		<?php if ( $this->post_data['track_opens'] ) { ?>&#10004;<?php } else { ?>&#10005;<?php }?> <?php esc_html_e( 'Track Opens', 'mailster' ) ?>
	</p>
	<p>
		<?php if ( $this->post_data['track_clicks'] ) { ?>&#10004;<?php } else { ?>&#10005;<?php }?> <?php esc_html_e( 'Track Clicks', 'mailster' ) ?>
	</p>
	<label><?php esc_html_e( 'Colors Schema', 'mailster' ) ?></label><br>
	<ul class="colorschema finished">
	<?php
	$colors = $this->post_data['colors'];
	foreach ( $colors as $color ) :	?>
		<li data-hex="<?php echo $color ?>" style="background-color:<?php echo $color ?>"></li>
	<?php endforeach; ?>
	</ul>
	<?php /*
	?>
	<?php if ( $this->post_data['background'] ) {
		$file = $this->post_data['background'];
		?>
	<hr>
	<label><?php esc_html_e( 'Background', 'mailster' ) ?></label><br>
	<ul class="backgrounds finished">
		<li><a title="<?php echo basename( $file ); ?>" style="background-image:url(<?php echo $file ?>)"></a></li>
	</ul>
	<?php } ?>
	<?php */ ?>

<?php endif; ?>
