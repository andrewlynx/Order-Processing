<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'order_processing' ) ) {
	class order_processing {
	
		/* Plugin initiation */
		public function __construct() {		
		
			$this->load_actions();		
			
		}
		
		/* Plugin hooks and actions.
		Some actions are hooked to custom actions that do not exists in woocommerce by default 
		*/
		
		private function load_actions(){
			add_action( 'add_order_column', array($this, 'add_order_column') );
			add_action( 'add_order_column_content', array($this, 'add_order_column_content'), 10, 2 );
			add_action( 'woocommerce_view_order', array($this, 'refund_button'), 10, 3 );
			add_action( 'woocommerce_thankyou', array($this, 'refund_button'), 10, 3 );
			add_action( 'wp_footer', array($this, 'webshims_script') );
			add_action( 'wp_head', array($this, 'order_forms_processing'), 5);
			add_action( 'add_order_details', array($this, 'add_order_details'), 10, 3 );
			add_filter( 'woocommerce_order_get_status', 'order_processing_order_status', 10, 2 ); 
			add_action( 'woocommerce_add_order_item_meta', 'save_item_shipping_order_itemmeta', 10, 3 );
		}

		/* Saves shipping price for country shipping plugin */
		function save_item_shipping_order_itemmeta( $item_id, $values, $cart_item_key ) {
			
			if(!function_exists('iqxzvqhmye_prices')) return;
			$quantity 	= $values[ 'quantity' ];
			$id			= $values['product_id'];
			$shipping	= iqxzvqhmye_prices($id);
			$ship_price = $shipping['delivery_cost']; 
			$ship_price *= $quantity; 
			
			wc_add_order_item_meta( $item_id, 'shipping_price', $ship_price , false );		
		}
				
		/* Adds column to account order page */
		public function add_order_column(){
			echo '<th class="product-shipping">Shipping</th>';
		}

		public function add_order_column_content($order, $item){
			if(function_exists('iqxzvqhmye_prices')){
				$shipping = $item['shipping_price'];
				$quantity = 1;
			} else {
				$shipping = $this->get_item_shipping_price($item, $order->id);
				$quantity = $item['qty'];
			}			
			if (empty($shipping) || $shipping == 0){
				$shipping = 'Free';
			} else {
				$shipping = '$'. $shipping * $quantity . ' USD';
			}
			echo '<td class="product-shipping">'. number_format($shipping, 2) .'</td>';	
		}
		
		/* Script for "date" input correct work in mozilla and mac */
		public function webshims_script(){

		?>
			<script src="http://cdn.jsdelivr.net/webshim/1.12.4/extras/modernizr-custom.js"></script>
			<script src="http://cdn.jsdelivr.net/webshim/1.12.4/polyfiller.js"></script> 
			<script>
				webshims.setOptions('waitReady', false);
				webshims.setOptions('forms-ext', {types: 'date'});
				webshims.polyfill('forms forms-ext');
			</script>
		<?php

		}
		
		public function order_notes($order_id){
			$order_note = get_option('order_processing['. $order_id .'][order_note]');
			$out = '';
			$out .= '<div class="inputs" style="padding: 20px;">'; 
				$out .= 'Failed Supplier Checkout Notes<p>';
					$out .= '<textarea cols="40" rows="10" name="order_processing['. $order_id .'][order_note]" placeholder="Failed Supplier Checkout Notes" value="'.$order_note.'">'.$order_note.'</textarea>';
				$out .= '</p>';
			$out .= '</div>';
			echo $out; 
		}
		
		/* account page order item detailes and buttons */
		public function add_order_details($order, $item, $item_id){

			$order_number = $order->get_order_number();
			$shipping_total = $order->get_total_shipping();
			$shipping = $this->get_item_shipping_price($item, $order->id);
			$order_total = $order->get_total();
			$status = order_processing_order_item_status($order, $order_number, $item_id );
			$tracking = get_option('order_processing['. $order_number .']['. $item_id .'][tracking]');
			$company = get_option('order_processing['. $order_number .']['. $item_id .'][company]');
			$notes = get_option('order_processing['. $order_number .']['. $item_id .'][notes]', false);
			$date = get_option('order_processing['. $order_number .']['. $item_id .'][date]'); 
			$button = '';

			if($status != 'Processing'){
				$button = "Request Return";
				$action = "request_return";
			}
			$order_time = get_post_time( 'U', true, $order->id );
			$current_time = time();
			$time_passed = false;
			if($date != false) {
				$past = ($current_time - strtotime($date)) / (60 * 60 * 24); 
				if($past > 61){
					$time_passed = true;
				}
			}
			$current_user = wp_get_current_user();
			
			$id = $item['item_meta']['_product_id'][0];
			$product = new WC_Product($id);
			$sku = $product->get_sku();
			$url = get_permalink( $item['product_id'] );
			
			include('order-detailes.php');
			include('add_to_cart.php');
			
		}
		
		/* Shipping price for item detailes */
		public function get_item_shipping_price($item, $order_id){
		
			if(function_exists('iqxzvqhmye_prices')){
				$shipping = $item['shipping_price'];
				return $shipping;
			} else {		
				global $woocommerce, $post;
				$this_quantity = $item['qty']; 
				$order = new WC_Order($order_id);
				$shipping = $order->get_total_shipping();
				$total_quantity = 0;
				foreach ( $order->get_items() as $a => $b ) {
					$q = $b['qty'];
					$total_quantity += $q;
				}
				$out = $shipping / $total_quantity;
				return number_format($out, 2);
			}
		}
		
		/*Generate refund button above order table */
		public function refund_button( $order_id ){

			$order = wc_get_order($order_id);
			$order_number = $order->get_order_number();
			$shipping_total = $order->get_total_shipping();
			$order_total = $order->get_total();

			if($order->get_status() == 'processing'){
				$button = "Cancel and Refund";
				$action = "item_refund";	

				echo '<form method="POST" class="refund-form">'; 
					echo '<input name="order_action" type="hidden" value="'.$action.'">';
					echo '<input name="order_id" type="hidden" value="'.$order->id.'">';
					echo '<input name="order_number" type="hidden" value="'.$order_number.'">';
					echo '<input name="shipping_total" type="hidden" value="'.$shipping_total.'">';
					echo '<input name="order_total" type="hidden" value="'.$order_total.'">';
					echo '<button class="submit refund-button">'.$button.'</button>';
					echo '<div style="display: none;" class="refund-popup">';
						echo '<p style="order-notice">Are you sure you want to cancel your order? The full refund will be made back to the original payment method you used, and can take between 7 and 14 working days to appear</p>';
						echo '<input type="submit" class="submit refund-button" value="OK">';
						echo '<span class="popup-close">Cancel</span>';
					echo '</div>';
					echo $this->popup_script(); 
				echo '</form>';
				
			}
			
			if( $order->get_status() == 'refunded' ){
				echo '<h4>Order has been refunded!</h4>'; 
				?>
				<style>
					.checkout-confirmed-payment {
						display: none !important;
					}
				</style>
				<?php
			}
			
		}
		
		function order_forms_processing(){
			if(isset($_POST['order_action'])){
				$order_id = $_POST['order_id'];
				$item_id = $_POST['item_id'];
				$order = wc_get_order($order_id);
				$shipping = $_POST['shipping_price'];
				$line_total = $_POST['line_total'];
				$shipping_total = $_POST['shipping_total'];
				$order_total = $_POST['order_total'];  	
				$order_number = $_POST['order_number'];  	
				
				if ( $_POST['order_action'] == 'item_refund' ){
				
					$new_total = $order_total + $shipping;
					update_post_meta( $order_id, '_order_total', $new_total );
					$update_post_data  = array(
						'ID'          => $order_id,
						'post_status' => 'wc-refunded',
					);
					wp_update_post( $update_post_data );
					wc_order_fully_refunded($order_id); 
					
					$email = get_option( 'order_processing_item_refund_receiver' );
					if(empty($email)) $email = $order->billing_email;									
					$email_heading = 'Order refunded from '. get_bloginfo().'';
					$email_content = 'Your order number '. $order_number .' on '. get_bloginfo().' has been refunded to your original payment method. This can take 7 to 14 working days to appear. You can get more details by logging in to <a href="'. get_permalink( get_option('woocommerce_myaccount_page_id') ) .'">Your account</a> page. Thank You.';
					$from_name = get_option( 'order_processing_item_refund_sender' );
					
					order_processing_send_email($email, $email_heading, $email_content, $from_name);
					
				} elseif ( $_POST['order_action'] == 'contact_us' ){
				
					$user_email = $_POST['user_email'];
					$order_number = $_POST['order_number'];
					$item_name = $_POST['item_name'];
					$item_url = $_POST['item_url'];
					$message = $_POST['user_comment'];
					$sku = $_POST['sku_number'];
					$order_date = $_POST['order_date'];
					$order_status = $_POST['order_status'];
					$email = get_option('order_processing_message_receiver');
					if ( !$email ){
						$email = get_option('order_processing_admin_email');
					}	
					if ( !$email ){
						$email = get_option('admin_email');
					}									
					$email_heading = $subj = 'New message from '. get_bloginfo().' user';
					$email_content = 'User email: ' . $user_email;
					$email_content .= '<br>';
					$email_content .= 'Order number: ' . $order_number;
					$email_content .= '<br>';
					$email_content .= 'Order date: ' . $order_date;
					$email_content .= '<br>';
					$email_content .= 'Order status: ' . $order_status;
					$email_content .= '<br>';
					$email_content .= 'Item name: <a href="'.$item_url.'">' . $item_name . '</a>';
					$email_content .= '<br>';
					$email_content .= 'Sku number: ' . $sku ;
					$email_content .= '<br>';
					$email_content .= 'Message: ' . $message;
					$from_name = get_option( 'order_processing_message_sender' );
					
					order_processing_send_email($email, $email_heading, $email_content, $from_name );
					
				} else {
					$return_reason = $_POST['return_reason'];
					$user_return_comment = $_POST['user_return_comment'];
					$item_name = $_POST['item_name'];

					$email = get_option('order_processing_return_reason_receiver');
					if ( !$email ){
						$email = get_option('order_processing_admin_email');
					}	
					if ( !$email ){
						$email = get_option('admin_email');
					}			
					$email_heading = $subj = 'Order refund on '.get_bloginfo().' requested!';
					
					$options = get_option('order_processing', array());
					$sent_to_admin = $plain_text = false;

					$email_content = str_replace('ORDER_NUMBER', $order_number, $options['refund-email']);
					$email_content = str_replace('RETURN_REASON', $return_reason, $email_content);
					$email_content = str_replace('PRODUCT_TITLE', $item_name, $email_content);
					$email_content .= '<br/> Customer comment: ' . $user_return_comment;
					$from_name = get_option( 'order_processing_return_reason_sender' );
					
					order_processing_send_email($email, $email_heading, $email_content, $from_name );

					update_option('order_processing['. $order_number .']['. $item_id .'][order_status]', 'Customer Return / Refund Requested');
					update_option('order_processing['. $order_number .']['. $item_id .'][user_return_comment]', $user_return_comment);
					update_option('order_processing['. $order_number .']['. $item_id .'][return_reason]', $return_reason);
				}
			} else if( !empty($_POST['order_processing']) ){
				foreach($_POST['order_processing'] as $order_number => $details){ 
					foreach($details as $item_id => $opt){
						update_option('order_processing['. $order_number .']['. $item_id .'][user_track_date]', $opt['user_track_date']);
						update_option('order_processing['. $order_number .']['. $item_id .'][user_shipping_company]', $opt['user_shipping_company']);
						update_option('order_processing['. $order_number .']['. $item_id .'][user_track_number]', $opt['user_track_number']);
						$email_heading = 'Product returned from customer';						
						$headers = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
						$email = get_option('order_processing_return_detailes_receiver');
						if ( !$email ){
							$email = get_option('order_processing_admin_email');
						}	
						if ( !$email ){
							$email = get_option('admin_email');
						}	
						$email_content = 'Product on site '.get_bloginfo().' from order '.$order_number.' have returned';
						$from_name = get_option( 'order_processing_return_detailes_sender' );
					
						order_processing_send_email($email, $email_heading, $email_content, $from_name );
					}
				}
			} else {
				return;
			}
		}

		public function popup_script(){
		?>
			<script>
				jQuery('.refund-form > .refund-button, .refund-Details > .refund-button').on('click', function(e){
					e.preventDefault();
					jQuery(this).siblings('.refund-popup').show();
				});
				jQuery('.popup-close').on('click', function(){
					jQuery('.refund-popup').hide();
				});
			</script>
		<?php
		}
		
	}
}
