<?php
/**
 * Simple product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */
 


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

if ( ! $product->is_purchasable() ) return;
if ( is_checkout() ) return;

$active = get_post_meta( $product->id, 'active', true);
$not_found = get_post_meta( $product->id, 'not_found', true);
if ( $active === 0 ) return;
if ( $not_found == 1 ) return;


	// Availability
	$availability = $product->get_availability();

	if ( $availability['availability'] )
		echo apply_filters( 'woocommerce_stock_html', '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>', $availability['availability'] );
		
	$attributes = $product->get_attributes();

?>

<?php if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" method="post" enctype='multipart/form-data'>

		<input name="quantity" type="hidden" value="<?php echo $item["item_meta"]["_qty"][0]; ?>">  
		<?php
		if(is_array($attributes)) {
		
			foreach ( $attributes as $attribute ) {
	
				$name = strtolower ( $attribute['name'] );
			
				echo '<input name="attribute_'.$name.'" type="hidden" value="'. $item[$name] .'">  ';
				
			}
		
		}
		
		?>
	 	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	 	<button type="submit" class="submit refund-button"> 
	 		<?php echo "Buy again"; ?>
	 	</button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	 	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />

	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>