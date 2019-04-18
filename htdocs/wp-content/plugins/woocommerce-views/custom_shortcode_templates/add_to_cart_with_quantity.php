<?php
/**
 * Loop Add to Cart
 * This is an add to cart loop button customization to display quantity next to the button.
 * Original code is here: http://docs.woothemes.com/document/override-loop-template-and-show-quantities-next-to-add-to-cart-buttons/
 * Modified to support latest templates and different WooCommerce versions.
 */
 
global $product, $new_wc_codes, $new_wc_crudclasses; 

/**
 * WooCommerce 2.7.0 compatibility
 */
$looped_product_id			= 0;
$looped_product_type		= '';
if ( false === $new_wc_crudclasses ) {
	//Backward compatibility
	$looped_product_id		= $product->id;
	$looped_product_type	= $product->product_type;
} elseif ( true === $new_wc_crudclasses ) {
	$looped_product_id		= $product->get_id();
	$looped_product_type	= $product->get_type();
}

if( $product->get_price() === '' && $looped_product_type != 'external' ) return;
?>

<?php if ( ! $product->is_in_stock() ) : ?>
		
	<a href="<?php echo get_permalink( $looped_product_id ); ?>" class="button"><?php echo apply_filters('out_of_stock_add_to_cart_text', __('Read More', 'woocommerce')); ?></a>

<?php else : ?>
	
	<?php 
	
		switch ( $looped_product_type ) {
			case "variable" :		
				if ($new_wc_codes) {		
					$link = $product->add_to_cart_url();
					$label 	= $product->add_to_cart_text();
				} else {
					$link 	= get_permalink( $looped_product_id );
					$label 	= apply_filters('variable_add_to_cart_text', __('Select options', 'woocommerce'));					
				}
			break;
			case "grouped" :	
				if ($new_wc_codes) {			
					$link = $product->add_to_cart_url();
					$label 	= $product->add_to_cart_text();
				} else {
					$link 	= get_permalink($looped_product_id );
					$label 	= apply_filters('grouped_add_to_cart_text', __('View options', 'woocommerce'));					
				}
			break;
			case "external" :				
				if ($new_wc_codes) { 
					$link = $product->add_to_cart_url();
					$label 	= $product->add_to_cart_text();
				} else {
					$link 	= get_permalink( $looped_product_id );
					$label 	= apply_filters('external_add_to_cart_text', __('Read More', 'woocommerce'));
				}
			break;
			default :
				if ($new_wc_codes) {
					$link = $product->add_to_cart_url();
					$label 	= $product->add_to_cart_text();
				} else {
					$link 	= esc_url( $product->add_to_cart_url() );
					$label 	= apply_filters('add_to_cart_text', __('Add to cart', 'woocommerce'));					
				}
			break;
		}

		if ( $looped_product_type == 'simple' ) {
			
			?>
			<form action="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="cart" method="post" enctype='multipart/form-data'>
		
			 	<?php 
		            // Displays the quantity box only if its not sold individually
                    if ( ! $product->is_sold_individually()) {
                    	woocommerce_quantity_input();
                    } 
			 	?>
		
			 	<button type="submit" class="button alt"><?php echo $label; ?></button>
		
			</form>
			<?php
			
		} else {
			
			printf('<a href="%s" rel="nofollow" data-product_id="%s" class="button add_to_cart_button product_type_%s">%s</a>', $link, $looped_product_id , $looped_product_type, $label);
			
		}

	?>

<?php endif; ?>