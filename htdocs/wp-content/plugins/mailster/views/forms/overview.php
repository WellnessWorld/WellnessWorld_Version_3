
<div class="wrap">
<h1><?php esc_html_e( 'Forms', 'mailster' ) ?>
<?php if ( current_user_can( 'mailster_add_forms' ) ) : ?>
	<a href="edit.php?post_type=newsletter&page=mailster_forms&new" class="add-new-h2"><?php esc_html_e( 'Add New', 'mailster' );?></a>
<?php endif; ?>
<?php if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) : ?>
	<span class="subtitle"><?php printf( __( 'Search result for %s', 'mailster' ), '&quot;' . esc_html( stripslashes( $_GET['s'] ) ) . '&quot;' ) ?></span>
	<?php endif; ?>
</h1>
<?php

$table = new Mailster_Forms_Table();

$table->prepare_items();
$table->search_box( __( 'Search Forms', 'mailster' ), 's' );
$table->views();
?><form method="post" action="" id="forms-overview-form"><?php
$table->display();
?></form>
</div>
