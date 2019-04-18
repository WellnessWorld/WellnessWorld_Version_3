<p class="description"><?php esc_html_e( 'Some of these settings may affect your website. In normal circumstance it is not required to change anything on this page.', 'mailster' ) ?></p>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Cache', 'mailster' ) ?></th>
		<td>
			<label><input type="hidden" name="mailster_options[disable_cache_frontpage]" value=""><input type="checkbox" name="mailster_options[disable_cache_frontpage]" value="1" <?php checked( mailster_option( 'disable_cache_frontpage' ) );?>> <?php esc_html_e( 'Disable Form Caching', 'mailster' ) ?></label> <p class="description"><?php esc_html_e( 'Enable this option if you have issue with the security nonce on Mailster forms.', 'mailster' ); ?></p>
			<br><label><input type="hidden" name="mailster_options[disable_cache]" value=""><input type="checkbox" name="mailster_options[disable_cache]" value="1" <?php checked( mailster_option( 'disable_cache' ) );?>> <?php esc_html_e( 'Disable Object Cache for Mailster', 'mailster' ) ?></label> <p class="description"><?php esc_html_e( 'If enabled Mailster doesn\'t use cache anymore. This causes an increase in page load time! This option is not recommended!', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Remove Data', 'mailster' ) ?></th>
		<td><label><input type="hidden" name="mailster_options[remove_data]" value=""><input type="checkbox" name="mailster_options[remove_data]" value="1" <?php checked( mailster_option( 'remove_data' ) );?>> <?php esc_html_e( 'Remove all data on plugin deletion', 'mailster' ) ?></label> <p class="description"><?php esc_html_e( 'Mailster will remove all it\'s data if you delete the plugin via the plugin page.', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'URL Rewrite', 'mailster' ) ?></th>
		<td><label><input type="hidden" name="mailster_options[got_url_rewrite]" value=""><input type="checkbox" name="mailster_options[got_url_rewrite]" value="1" <?php checked( mailster_option( 'got_url_rewrite' ) );?>> <?php esc_html_e( 'Website supports URL rewrite', 'mailster' ) ?></label> <p class="description"><?php esc_html_e( 'Mailster detects this setting by default so change only if detection fails.', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Form POST protection', 'mailster' ) ?></th>
		<td><input type="text" name="mailster_options[post_nonce]" value="<?php echo esc_attr( mailster_option( 'post_nonce' ) ); ?>" class="regular-text" style="width: 100px;"> <label><input type="hidden" name="mailster_options[use_post_nonce]" value=""><input type="checkbox" name="mailster_options[use_post_nonce]" value="1" <?php checked( mailster_option( 'use_post_nonce' ) );?>> <?php esc_html_e( 'Use on internal forms.', 'mailster' ) ?></label> <span class="description"><?php esc_html_e( 'Check if you have a heavy cached page and problems with invalid Security Nonce.', 'mailster' ) ?></span>
			<p class="description"><?php esc_html_e( 'A unique string to prevent form submissions via POST. Pass this value in a \'_nonce\' variable. Keep empty to disable test.', 'mailster' ) ?></p></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'PHP Mailer', 'mailster' ) ?></th>
		<td>
		<?php $phpmailerversion = mailster_option( 'php_mailer' ); ?>
		<label><?php esc_html_e( 'Use version', 'mailster' ) ?>
		<select name="mailster_options[php_mailer]">
			<option value="0" <?php selected( ! $phpmailerversion ); ?>><?php esc_html_e( 'included in WordPress', 'mailster' ) ?></option>
			<option value="latest" <?php selected( 'latest', $phpmailerversion ); ?>><?php printf( __( 'latest (%s)', 'mailster' ), '5.2.26' ); ?></option>
		</select></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Send Test', 'mailster' ) ?></th>
		<td>
		<div class="mailster-testmail">
			<input type="text" value="<?php echo $current_user->user_email ?>" autocomplete="off" class="form-input-tip mailster-testmail-email">
			<input type="button" value="<?php esc_html_e( 'Send Test', 'mailster' ) ?>" class="button mailster_sendtest" data-role="basic">
			<div class="loading test-ajax-loading"></div>
		</div>
		</td>
	</tr>
</table>
