
<table class="form-table">

	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Bounce Address', 'mailster' ) ?></th>
		<td><input type="text" name="mailster_options[bounce]" value="<?php echo esc_attr( mailster_option( 'bounce' ) ); ?>" class="regular-text"> <span class="description"><?php esc_html_e( 'Undeliverable emails will return to this address', 'mailster' );?></span></td>
	</tr>
	<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td><label><input type="hidden" name="mailster_options[bounce_active]" value=""><input type="checkbox" name="mailster_options[bounce_active]" id="bounce_active" value="1" <?php checked( mailster_option( 'bounce_active' ) );?>> <?php esc_html_e( 'Enable automatic bounce handling', 'mailster' ) ?></label>
		</td>
	</tr>

</table>
<div id="bounce-options" <?php if ( ! mailster_option( 'bounce_active' ) ) { echo 'style="display:none"';} ?>>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">&nbsp;</th>
			<td><p class="description"><?php esc_html_e( 'If you would like to enable bouncing you have to setup a separate mail account', 'mailster' );?></p></td>
		</tr>
	<?php if ( function_exists( 'imap_open' ) ) : ?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Service', 'mailster' );?></th>
			<td>
			<label><input type="radio" name="mailster_options[bounce_service]" value="pop3" <?php checked( mailster_option( 'bounce_service' ), 'pop3' ) ?>> POP3 </label>&nbsp;
			<label><input type="radio" name="mailster_options[bounce_service]" value="imap" <?php checked( mailster_option( 'bounce_service' ), 'imap' ) ?>> IMAP </label>&nbsp;
			<label><input type="radio" name="mailster_options[bounce_service]" value="nntp" <?php checked( mailster_option( 'bounce_service' ), 'nntp' ) ?>> NNTP </label>&nbsp;
			<label><input type="radio" name="mailster_options[bounce_service]" value="" <?php checked( ! mailster_option( 'bounce_service' ) ) ?>> POP3 (legacy)</label>
			</td>
		</tr>
	<?php endif; ?>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Server Address : Port', 'mailster' ) ?></th>
			<td><input type="text" name="mailster_options[bounce_server]" value="<?php echo esc_attr( mailster_option( 'bounce_server' ) ); ?>" class="regular-text">:<input type="text" name="mailster_options[bounce_port]" id="bounce_port" value="<?php echo mailster_option( 'bounce_port' ); ?>" class="small-text"></td>
		</tr>
		<tr valign="top">
			<th scope="row">SSL</th>
			<td><label><input type="hidden" name="mailster_options[bounce_ssl]" value=""><input type="checkbox" name="mailster_options[bounce_ssl]" id="bounce_ssl" value="1" <?php checked( mailster_option( 'bounce_ssl' ) );?>> <?php esc_html_e( 'Use SSL.', 'mailster' ) ?></label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Username', 'mailster' ) ?></th>
			<td><input type="text" name="mailster_options[bounce_user]" value="<?php echo esc_attr( mailster_option( 'bounce_user' ) ); ?>" class="regular-text"></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Password', 'mailster' ) ?></th>
			<td><input type="password" name="mailster_options[bounce_pwd]" value="<?php echo esc_attr( mailster_option( 'bounce_pwd' ) ); ?>" class="regular-text" autocomplete="new-password"></td>
		</tr>
		<tr valign="top" class="wp_cron">
			<th scope="row"></th>
			<td><p><?php printf( __( 'Check bounce server every %s minutes for new messages', 'mailster' ), '<input type="text" name="mailster_options[bounce_check]" value="' . mailster_option( 'bounce_check' ) . '" class="small-text">' ) ?></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Delete messages', 'mailster' );?></th>
			<td><label><input type="hidden" name="mailster_options[bounce_delete]" value=""><input type="checkbox" name="mailster_options[bounce_delete]" value="1" <?php checked( mailster_option( 'bounce_delete' ) ) ?>> <?php esc_html_e( 'Delete messages without tracking code to keep postbox clear (recommended)', 'mailster' ) ?></label>
			</td>
		</tr>
		<tr valign="top" class="wp_cron">
			<th scope="row"><?php esc_html_e( 'Soft Bounces', 'mailster' ) ?></th>
			<td><p><?php printf( __( 'Resend soft bounced mails after %s minutes', 'mailster' ), '<input type="text" name="mailster_options[bounce_delay]" value="' . mailster_option( 'bounce_delay' ) . '" class="small-text">' ) ?></p>
			<p>
<?php
			$dropdown = '<select name="mailster_options[bounce_attempts]" class="postform">';
			$value = mailster_option( 'bounce_attempts' );
for ( $i = 1; $i <= 10; $i++ ) {
	$selected = ( $value == $i ) ? ' selected' : '';
	$dropdown .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
}
			$dropdown .= '</select>';

			printf( __( '%s attempts to deliver message until hardbounce', 'mailster' ), $dropdown );
?>
			</p>
			</td>
		</tr>
	</table>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"></th>
			<td>
			<input type="button" value="<?php esc_html_e( 'Test bounce settings', 'mailster' ) ?>" class="button mailster_bouncetest">
			<div class="loading bounce-ajax-loading"></div>
			<span class="bouncetest_status"></span>
			</td>
		</tr>
	</table>
</div>
