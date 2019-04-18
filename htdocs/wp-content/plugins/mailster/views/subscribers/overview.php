<?php

$table = new Mailster_Subscribers_Table();

$table->prepare_items();

?>
<div class="wrap">
<h1>
<?php printf( _n( '%s Subscriber found', '%s Subscribers found', $table->total_items, 'mailster' ), number_format_i18n( $table->total_items ) ); ?>
<?php if ( current_user_can( 'mailster_add_subscribers' ) ) : ?>
	<a href="edit.php?post_type=newsletter&page=mailster_subscribers&new" class="add-new-h2"><?php esc_html_e( 'Add New', 'mailster' );?></a>
<?php endif; ?>
<?php if ( current_user_can( 'mailster_import_subscribers' ) ) : ?>
	<a href="edit.php?post_type=newsletter&page=mailster_manage_subscribers&tab=import" class="add-new-h2"><?php esc_html_e( 'Import', 'mailster' );?></a>
<?php endif; ?>
<?php if ( current_user_can( 'mailster_export_subscribers' ) ) : ?>
	<a href="edit.php?post_type=newsletter&page=mailster_manage_subscribers&tab=export" class="add-new-h2"><?php esc_html_e( 'Export', 'mailster' );?></a>
<?php endif; ?>
<?php if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) : ?>
	<span class="subtitle"><?php printf( __( 'Search result for %s', 'mailster' ), '&quot;' . esc_html( stripslashes( $_GET['s'] ) ) . '&quot;' ) ?></span>
	<?php endif; ?>
</h1>
<?php
$table->search_box( __( 'Search Subscribers', 'mailster' ), 's' );
$table->views();

$text = sprintf( __( 'Do you like to select all %s subscribers?', 'mailster' ), number_format_i18n( $table->total_items ) );

?>
<form method="post" action="" id="subscribers-overview-form">
<input type="hidden" name="all_subscribers" id="all_subscribers" data-label="<?php echo esc_attr( $text ) ?>" data-count="<?php echo $table->total_items ?>" value="0">
<?php $table->display(); ?>
</form>
</div>
