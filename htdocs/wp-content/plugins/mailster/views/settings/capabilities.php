<p class="description"><?php esc_html_e( 'Define capabilities for each user role. To add new roles you can use a third party plugin. Administrator has always all privileges', 'mailster' );?></p>
<div id="current-cap"></div>
<table class="form-table"><?php

unset( $roles['administrator'] );

?>
<tr valign="top">
	<td>
		<table id="capabilities-table">
			<thead>
				<tr>
				<th>&nbsp;</th><?php
				foreach ( $roles as $role => $name ) {
					echo '<th><input type="hidden" name="mailster_options[roles][' . $role . '][]" value="">' . $name . ' <input type="checkbox" class="selectall" value="' . $role . '" title="' . __( 'toggle all', 'mailster' ) . '"></th>';

				}?>
				</tr>
			</thead>
			<tbody>
<?php include MAILSTER_DIR . 'includes/capability.php'; ?>

	<?php foreach ( $mailster_capabilities as $capability => $data ) { ?>
			<tr><th><?php echo $data['title'] ?></th>
		<?php foreach ( $roles as $role => $name ) { ?>
		<?php $r = get_role( $role );
			echo '<td><label title="' . sprintf( __( '%1$s can %2$s', 'mailster' ), $name, $data['title'] ) . '"><input name="mailster_options[roles][' . $role . '][]" type="checkbox" class="cap-check-' . $role . '" value="' . $capability . '" ' . checked( ! empty( $r->capabilities[ $capability ] ), 1, false ) . ' ' . ( $role == 'administrator' ? 'readonly' : '' ) . '></label></td>';
		?>
		<?php } ?>
			</tr>
	<?php } ?>
			</tbody>
		</table>
	</td>
</tr>
</table>
<p>
<a onclick='return confirm("<?php esc_html_e( 'Do you really like to reset all capabilities? This cannot be undone!', 'mailster' );?>");' href="edit.php?post_type=newsletter&page=mailster_settings&reset-capabilities=1&_wpnonce=<?php echo wp_create_nonce( 'mailster-reset-capabilities' ) ?>"><?php esc_html_e( 'Reset all capabilities', 'mailster' );?></a>
</p>
