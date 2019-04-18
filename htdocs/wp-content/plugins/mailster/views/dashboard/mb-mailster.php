<?php
	$plugin_info = mailster()->plugin_info();

	$license_email = '';
	$license_user = '';

if ( mailster()->is_verified() ) {
	$license_user = mailster()->username( '' );
	$license_email = mailster()->email( '' );
}

?>
<div class="locked">
	<h2><span class="not-valid"><?php esc_html_e( 'Please Validate', 'mailster' ); ?></span><span class="valid"><?php esc_html_e( 'Validated!', 'mailster' ); ?></span>
	</h2>
</div>
<dl class="mailster-icon mailster-icon-finished valid">
	<dt><?php esc_html_e( 'Verified', 'mailster' ); ?></dt>
	<dd><?php printf( esc_html__( 'User: %1$s - %2$s', 'mailster' ), '<span class="mailster-username">' . esc_html( $license_user ) . '</span>', '<span class="mailster-email lighter">' . esc_html( $license_email ) . '</span>' ) ?></dd>
	<dd>
		<?php if ( current_user_can( 'mailster_manage_licenses' ) ) : ?>
		<a href="https://mailster.co/manage-licenses/" class="external"><?php esc_html_e( 'Manage Licenses', 'mailster' ); ?></a> |
		<a href="<?php echo admin_url( 'admin.php?page=mailster_dashboard&reset_license=' . wp_create_nonce( 'mailster_reset_license' ) ) ?>" class="reset-license"><?php esc_html_e( 'Reset License', 'mailster' ); ?></a> |
		<?php endif; ?>
		<a href="https://mailster.co/go/buy/?utm_campaign=plugin&utm_medium=dashboard" class="external"><?php esc_html_e( 'Buy new License', 'mailster' ); ?></a>
	</dd>
</dl>
<dl class="mailster-icon mailster-icon-delete not-valid">
	<dt><?php esc_html_e( 'Not Verified', 'mailster' ); ?></dt>
	<dd><?php esc_html_e( 'Your license has not been verified', 'mailster' ); ?></dd>
	<dd>
		<?php if ( current_user_can( 'mailster_manage_licenses' ) ) : ?>
		<a href="https://mailster.co/manage-licenses/" class="external"><?php esc_html_e( 'Manage Licenses', 'mailster' ); ?></a> |
		<?php endif; ?>
		<a href="https://mailster.co/go/buy/?utm_campaign=plugin&utm_medium=dashboard" class="external"><?php esc_html_e( 'Buy new License', 'mailster' ); ?></a>
	</dd>
</dl>
<dl class="mailster-icon mailster-icon-reload update-not-available">
	<dt><?php printf( esc_html__( 'Installed Version %s', 'mailster' ), MAILSTER_VERSION ) ?></dt>
		<dd><?php esc_html_e( 'You have the latest version', 'mailster' ); ?></dd>
		<dd><span class="lighter"><?php echo isset( $plugin_info->last_update ) ? sprintf( __( 'checked %s ago', 'mailster' ), '<span class="update-last-check">' . human_time_diff( $plugin_info->last_update ) . '</span>' ) . ' - '  : '' ?></span> <span class="lighter"><a href="" class="check-for-update"><?php esc_html_e( 'Check Again', 'mailster' ); ?></a></span></dd>
</dl>
<dl class="mailster-icon mailster-icon-reload update-available">
	<dt><?php printf( esc_html__( 'Installed Version %s', 'mailster' ), MAILSTER_VERSION ) ?></dt>
		<dd><?php esc_html_e( 'A new Version is available', 'mailster' ); ?></dd>
		<dd><a class="thickbox" href="<?php echo network_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=mailster&amp;section=changelog&amp;TB_iframe=true&amp;width=772&amp;height=745' ) ?>"><?php esc_html_e( 'view changelog', 'mailster' ) ?></a> <?php esc_html_e( 'or', 'mailster' ) ?> <a href="update.php?action=upgrade-plugin&plugin=<?php echo urlencode( MAILSTER_SLUG ); ?>&_wpnonce=<?php echo wp_create_nonce( 'upgrade-plugin_' . MAILSTER_SLUG ) ?>" class="update-button"><?php printf( __( 'update to %s now', 'mailster' ), '<span class="update-version">' . $plugin_info->new_version . '</span>' ) ?></a></dd>
</dl>
<dl class="mailster-icon mailster-icon-support">
	<dt><?php esc_html_e( 'Support', 'mailster' ); ?></dt>
	<dd>
		<a href="https://docs.revaxarts.com/mailster/" class="external"><?php esc_html_e( 'Documentation', 'mailster' ); ?></a> |
		<a href="https://kb.mailster.co/" class="external"><?php esc_html_e( 'Knowledge Base', 'mailster' ); ?></a> |
		<a href="https://mailster.co/login/" class="external"><?php esc_html_e( 'Support', 'mailster' ); ?></a> |
		<a href="<?php echo admin_url( 'admin.php?page=mailster_tests' ); ?>"><?php esc_html_e( 'Self Test', 'mailster' ); ?></a>
	</dd>
</dl>
