<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'order_processing_admin' ) ) {
	class order_processing_admin {
	
		/* Plugin initiation */
		public function __construct() {		
		
			$this->load_actions();		
			
		}
		
		/* Plugin hooks and actions.
		Some actions are hooked to custom actions that do not exists in woocommerce by default 
		*/
		
		private function load_actions(){
			add_filter( 'woocommerce_api_order_response', 'add_custom_data_to_order', 10, 2 );
			add_action( 'manage_posts_extra_tablenav', array($this, 'manage_posts_extra_tablenav') );
			add_action( 'woocommerce_update_option_order_processing_table', array($this, 'update_option_order_processing_table') );
			add_action( 'woocommerce_admin_field_order_processing_table', array($this, 'admin_field_order_processing_table') );
			add_action( 'woocommerce_update_options', array($this, 'update_order_processing_settings') );
			add_action( 'woocommerce_settings_tabs_order_processing', array($this, 'settings_tab') );
			add_action( 'woocommerce_settings_tabs_array', array($this, 'settings_tabs_array'), 50 );
			add_action( 'woocommerce_after_order_itemmeta', array($this, 'shipping_price'), 10, 3);
			add_action( 'woocommerce_after_order_itemmeta', array($this, 'admin'), 10, 3 );
			add_action( 'admin_footer', array($this, 'ajax_update') );
			add_action( 'wp_ajax_order_processing_save_option', array($this, 'order_processing_save_option') );
			add_action( 'woocommerce_admin_order_totals_after_refunded', array($this, 'supplier_profit_calc'));
			add_action( 'in_admin_footer', array($this, 'wp_admin_style') ); 
			add_filter( 'woocommerce_order_get_status', 'order_processing_order_status', 10, 2); 
		}
		
		/* Woocommerce admin settings tab */
		public function settings_tabs_array($tabs) {
			$tabs['order_processing'] = __('Order Processing', 'order_processing'); 
			return $tabs;
		}

		public function settings_tab() {
			woocommerce_admin_fields( $this->edit_account_form() );
		}

		public function edit_account_form() { 
			$settings = array(
				'section_title' => array(
					'name'     => __( 'Order Processing', 'order_processing' ),
					'type'     => 'title',
					'desc'     => '',
					'id'       => 'wc_settings_tab_order_processing_section_title'
				),
				'admin_email' => array(
					'title'     => 'Common email for order notifications (will be used if other fields are empty)',
					'id'       => 'order_processing_admin_email',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'order_shipped_sender' => array(
					'title'     => 'Order shipped SENDER email',
					'id'       => 'order_processing_order_shipped_sender',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'return_accepted_sender' => array(
					'title'     => 'Return accepted SENDER email',
					'id'       => 'order_processing_return_accepted_sender',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'message_sender' => array(
					'title'     => 'Message SENDER email',
					'id'       => 'order_processing_message_sender',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'message_receiver' => array(
					'title'     => 'Message RECEIVER email',
					'id'       => 'order_processing_message_receiver',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'return_reason_sender' => array(
					'title'     => 'Return reason SENDER email',
					'id'       => 'order_processing_return_reason_sender',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'return_reason_receiver' => array(
					'title'     => 'Return reason RECEIVER email',
					'id'       => 'order_processing_return_reason_receiver',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'item_refund_sender' => array(
					'title'     => 'Item refund SENDER email',
					'id'       => 'order_processing_item_refund_sender',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'return_detailes_sender' => array(
					'title'     => 'Return detailes SENDER email',
					'id'       => 'order_processing_return_detailes_sender',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'return_detailes_receiver' => array(
					'title'     => 'Return detailes RECEIVER email',
					'id'       => 'order_processing_return_detailes_receiver',
					'type'     => 'text',
					'default' => '',
					'css'      => 'width:100%;max-width: 400px;',
				),
				'description' => array(
					'name' => __( 'Description', 'order_processing' ),
					'type' => 'order_processing_table', 
					'desc' => __( '', 'order_processing' ),
					'id'   => 'wc_settings_tab_order_processing_description'
				),
				'section_end' => array(
					 'type' => 'sectionend',
					 'id' => 'wc_settings_tab_order_processing_section_end'
				)
			);
			
			return apply_filters( 'wc_settings_tab_order_processing_settings', $settings );
		}
		
		function update_order_processing_settings() { 
			woocommerce_update_options( $this->edit_account_form() );
		}
		
		public function admin( $item_id, $item, $_product ) { 
			
			global $woocommerce, $post;
			$order = new WC_Order($post->ID);
			$order_number = $order->get_order_number();
						
			$checked = get_option('order_processing['. $order_number .']['. $item_id .'][shipped]');
			$visible = '';
			if ($checked == 'on'){ $checked = 'checked'; } else { $checked = ''; $visible = 'style="display: none;"'; }
			$send_email = get_option('order_processing['. $order_number .']['. $item_id .'][send_email]');
			
			$send_allow_refund_email = get_option('order_processing['. $order_number .']['. $item_id .'][send_allow_refund_email]');
			
			$tracking = get_option('order_processing['. $order_number .']['. $item_id .'][tracking]');
			$company = get_option('order_processing['. $order_number .']['. $item_id .'][company]');
			$notes = get_option('order_processing['. $order_number .']['. $item_id .'][notes]');
			$date = get_option('order_processing['. $order_number .']['. $item_id .'][date]'); 
			
			$reset_status = get_option('order_processing['. $order_number .']['. $item_id .'][reset_status]'); 
			if($reset_status == 'on'){
				delete_option('order_processing['. $order_number .']['. $item_id .'][order_status]');
				update_option( 'order_processing['. $order_number .']['. $item_id .'][reset_status]', false );
			}
			
			$order_status = order_processing_order_item_status($order, $order_number, $item_id ); 
			
			$supplier_order_number = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_order_number]'); 
			$supplier_order_date = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_order_date]');
			$supplier_shipping_cost = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_shipping_cost]');
			$supplier_cost = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_cost]');
			$supplier_qty = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_qty]');
			$supplier_total = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_total]');
			
			$supplier_return_address = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_return_address]');
			$rma_num = get_option('order_processing['. $order_number .']['. $item_id .'][rma_num]'); 
			
			$supplier_refunded_yes = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_refunded_yes]');
			$supplier_refunded_no = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_refunded_no]');
			if ($supplier_refunded_yes == 'on'){ $supplier_refunded_yes = 'checked'; } 
			if ($supplier_refunded_no == 'on' ){ $supplier_refunded_no = 'checked'; }
			$supplier_refund_method = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_refund_method]'); 
			$supplier_refund_comments = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_refund_comments]'); 
			$supplier_refund_date = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_refund_date]'); 
			$supplier_refund_amount = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_refund_amount]'); 
			$screenshot = get_option('order_processing['. $order_number .']['. $item_id .'][screenshot]');
			
			$user_return_comment = get_option('order_processing['. $order_number .']['. $item_id .'][user_return_comment]');
			$user_shipping_company = get_option('order_processing['. $order_number .']['. $item_id .'][user_shipping_company]');
			$user_track_number = get_option('order_processing['. $order_number .']['. $item_id .'][user_track_number]');
			$user_track_date = get_option('order_processing['. $order_number .']['. $item_id .'][user_track_date]');
			$return_reason = get_option('order_processing['. $order_number .']['. $item_id .'][return_reason]');
			$statuses = order_processing_get_statuses();
			$email = $order->billing_email;
			
			if($order_status == "Awaiting Shipment Return" && $send_allow_refund_email == false){
				$subj = 'Your return is accepted';
				$email_heading = 'Your return is accepted';
				
				$options = get_option('order_processing', array());

				$email_content = str_replace('ORDER_NUMBER', $order_number, $options['return-accepted-email']);
				$email_content = str_replace('ITEM_NAME', $item['name'], $email_content);	
				$email_content = str_replace('PRODUCT_TITLE', $item['name'], $email_content);	
				$email_content = str_replace('MY_ACCOUNT', '<a href="'. get_permalink( get_option('woocommerce_myaccount_page_id') ) .'">Your account</a>', $email_content);
				
				$from_name = get_option( 'order_processing_return_accepted_sender' );
				
				$success = order_processing_send_email($email, $email_heading, $email_content, $from_name );
				
				if ($success){
					echo '<h3>Email to customer was sent successfully</h3>'; 
					update_option('order_processing['. $order_number .']['. $item_id .'][send_allow_refund_email]', true);
				} else {
					echo '<h3>Error while sending email</h3>'; 
				}
			}
			
			if( $send_email == false && $checked == 'checked'){
				$email_heading = 'Your order has shipped!';
				
				$options = get_option('order_processing', array());
				
				if(empty($company)){
					$company = 'N/A';
				}
				if(empty($tracking)){
					$tracking = 'N/A';
				}
				if(empty($notes)){
					$notes = 'N/A';
				}

				$email_content = str_replace('ITEM_NAME', $item['name'], $options['order-shipped-email']);
				$email_content = str_replace('PRODUCT_TITLE', $item['name'], $email_content);
				$email_content = str_replace('SHIPPING_COMPANY', $company, $email_content);
				$email_content = str_replace('ORDER_NUMBER', $order_number, $email_content);
				$email_content = str_replace('TRACKING_NUMBER', $tracking, $email_content);
				$email_content = str_replace('NOTES', $notes, $email_content);
				$email_content = str_replace('MY_ACCOUNT', '<a href="'. get_permalink( get_option('woocommerce_myaccount_page_id') ) .'">Your account</a>', $email_content);
				
				$from_name = get_option( 'order_processing_order_shipped_sender' );
				
				$success = order_processing_send_email($email, $email_heading, $email_content, $from_name );
				
				if ($success){
					echo '<h3>Email to customer was sent successfully</h3>'; 
					update_option('order_processing['. $order_number .']['. $item_id .'][send_email]', true);
				} else {
					echo '<h3>Error while sending email</h3>'; 
				}				
			}
			
			include plugin_dir_path( __FILE__ ) . 'order-item-detailes.php';
			
		}
		
		/* Script to update order options */
		public function ajax_update() { 
			?>
			<script src="https://cdn.jsdelivr.net/webshim/1.12.4/extras/modernizr-custom.js"></script>
			<script src="https://cdn.jsdelivr.net/webshim/1.12.4/polyfiller.js"></script> 
			<script>
				(function($) {
					$(function() {
						$( ".inputs input, .inputs select, .inputs textarea" ).change(function() {
							var val = $(this).val();
							if($(this).is(':checkbox')){
								console.log( $(this).closest('.details') );
								$(this).siblings('.details').slideToggle();
								if ($(this).prop('checked')==true){
									val = 'on';
								} else {
									val = 'off';
								}
							}
							
							var name = $(this).attr('name');
							var data = {
								'action': 'order_processing_save_option',
								'name': name,
								'val': val,
							};
							$.post(ajaxurl, data, function(response) {
								console.log('Order settings updated');
							}); 
						});
					});
					$('a.order-show').on('click', function(e){
						e.preventDefault();
						var id = $(this).attr('id');
						$('.orders-list').hide();
						$('div.' + id).show();
					});
					$('a.order-close').on('click', function(e){
						e.preventDefault();
						$('.orders-list').hide();
					});
				})(jQuery);
				webshims.setOptions('waitReady', false);
				webshims.setOptions('forms-ext', {types: 'date'});
				webshims.polyfill('forms forms-ext');
			</script>
			<?php
		}	

		public function order_processing_save_option(){

			$name = $_POST['name'];
			$val = $_POST['val'];
			update_option($name, $val);
			echo var_dump( $val );
			die();
			
		}
				
		public function wp_admin_style() {
			wp_register_style( 'order_wp_admin_css', plugin_dir_url( __FILE__ ) . 'order-admin-style.css', false, '1.0.0' );
			wp_enqueue_style( 'order_wp_admin_css' );
		}
		
		public function add_custom_data_to_order( $order_data , $order  ) {

			$items=$order->get_items();
			foreach ($items as $key=>$item) {
				foreach ($item['item_meta'] as $key_meta => $val_meta) {
					$item['item_meta'][$key_meta]=$val_meta[0];
				}
				$order_data["items_meta"][$key]=$item['item_meta'];
			} 
			return $order_data;

		}
		
		/* Returns formatted shipping price for page output */
		public function shipping_price( $item_id, $item, $_product ){
			
			if(function_exists('iqxzvqhmye_prices')){
				return;
			} else {
				global $woocommerce, $post;
				$this_quantity = $item['qty']; 
				$order = new WC_Order($post->ID);
				$shipping = $order->get_total_shipping();
				$total_quantity = 0;
				foreach ( $order->get_items() as $a => $b ) {
					$q = $b['qty'];
					$total_quantity += $q;
				}
				$out = $shipping / $total_quantity;
				echo '<p class="item-shipping-cost">Shipping Cost $' . number_format($out, 2) . ' each</p>'; 
			}			
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
		
		public function admin_field_order_processing_table($value){
			$options = get_option('order_processing', array());
			
			if(empty($options['refund-email'])) $options['refund-email'] = 'Product PRODUCT_TITLE from order ORDER_NUMBER is requested to return. Reason: RETURN_REASON';
			
			if(empty($options['order-shipped-email'])) $options['order-shipped-email'] = 'Your order ITEM_NAME from ORDER_NUMBER has shipped!<br/>
			Shipping Company: SHIPPING_COMPANY<br/>
			Tracking Number: TRACKING_NUMBER<br/>
			Shipping Notes: NOTES,<br/>
			You can manage your orders in your MY_ACCOUNT page.<br/>
			NOTES.';
			
			if(empty($options['return-accepted-email'])) $options['return-accepted-email'] = 'Your return for order number ORDER_NUMBER PRODUCT_TITLE has been accepted. Please login to your MY_ACCOUNT page to obtain your RMA Number and return address details.';
			
			$example_refund = str_replace('ORDER_NUMBER', '232354', $options['refund-email']);
			$example_refund = str_replace('RETURN_REASON', 'Fault goods', $example_refund);
			$example_refund = str_replace('PRODUCT_TITLE', 'Your Great Thing', $example_refund);
			
			$example_order_shipped = str_replace('ITEM_NAME', 'Your Great Thing', $options['order-shipped-email']);
			$example_order_shipped = str_replace('SHIPPING_COMPANY', 'Shipping Int', $example_order_shipped);
			$example_order_shipped = str_replace('TRACKING_NUMBER', '346HD33SMM8', $example_order_shipped);
			$example_order_shipped = str_replace('NOTES', 'Here is note for your order', $example_order_shipped);
			
			$example_return_accepted = str_replace('ORDER_NUMBER', '232354', $options['return-accepted-email']);
			$example_return_accepted = str_replace('PRODUCT_TITLE', 'Your Great Thing', $example_return_accepted);
			$example_return_accepted = str_replace('MY_ACCOUNT', '<a href="'.get_permalink( get_option('woocommerce_myaccount_page_id') ).'">Your account</a>', $example_return_accepted);
			?>
			
				<h3>Refund email template</h3>
				<p>Edit Refund email template. You can use next variables: ORDER_NUMBER, PRODUCT_TITLE, RETURN_REASON. They will be replaced with actual in real email. Use"&lt;br/&gt;" for line break.</p>
				<p><textarea cols="100" name="order_processing[refund-email]" value="<?php echo $options['refund-email']; ?>"><?php echo $options['refund-email']; ?></textarea></p>
				<p><b>Email text example:</b><br/> <?php echo $example_refund; ?></p>
				<h3>Order Shipped template</h3>
				<p>Edit Order shipped email template. You can use next variables: ITEM NAME, SHIPPING_COMPANY, TRACKING_NUMBER, NOTES, MY_ACCOUNT. They will be replaced with actual in real email. Use"&lt;br/&gt;" for line break.</p>
				<p><textarea cols="100" name="order_processing[order-shipped-email]" value="<?php echo $options['order-shipped-email']; ?>"><?php echo $options['order-shipped-email']; ?></textarea></p>
				<p><b>Email text example:</b><br/> <?php echo $example_order_shipped; ?></p>
				<h3>Return accepted template</h3>
				<p>Edit Return accepted email template. You can use next variables: ORDER_NUMBER, PRODUCT_TITLE, MY_ACCOUNT. They will be replaced with actual in real email. Use"&lt;br/&gt;" for line break.</p>
				<p><textarea cols="100" name="order_processing[return-accepted-email]" value="<?php echo $options['return-accepted-email']; ?>"><?php echo $options['return-accepted-email']; ?></textarea></p>
				<p><b>Email text example:</b><br/> 
				<?php echo $example_return_accepted; ?></p>
				
			<?php 
		}


		public function update_option_order_processing_table($value){
			if(isset($_POST['order_processing']['refund-email'])){
				$order_processing_settings = $_POST['order_processing'];
				update_option('order_processing',$order_processing_settings);
			}
		}
		
		public function manage_posts_extra_tablenav($which){
			$post_type = get_post_type();
			if ($which == 'bottom' || $post_type != 'shop_order') {
				return;
			}
			$orders = get_posts( array(
				'post_type'   => 'shop_order',
				'posts_per_page'   => -1,
			)  );
			$statuses = order_processing_get_statuses();
			$stack = array();
			foreach ($orders as $order_num => $order){
				$order_obj = new WC_Order($order->ID);
				$order_number = $order_obj->get_order_number();
				$order_items = $order_obj->get_items();
				foreach ( $order_items as $item_id => $item ) {
					$item_status = order_processing_order_item_status($order_obj, $order_number, $item_id );
					if (!in_array($order_number, $stack[$item_status])){
						$stack[$item_status][$order_number]['order'] = $order_number;
						$stack[$item_status][$order_number]['id'] = $order->ID;
					}			
				}
			}
			echo '<div style="width: 100%; float: left;">';
				$count = 1;
				foreach ($stack as $status => $orders){
					echo '<a href="#" id="status-'.$count.'" class="order-show">' . $status . '</a> (' . count($orders) . ') | ';
					$count ++;
				}
				$count = 1;
				foreach ($stack as $status => $orders){
					echo '<div style="display: none;" class="status-'.$count.' orders-list">'; 
						foreach ($orders as $order => $order_id){
							echo '<a href="/wp-admin/post.php?post='.$order_id['id'].'&action=edit" target="_blank"><b>#'.$order.'</b></a> ';
						}
						echo '<br/><a href="#" class="status-'.$count.' order-close">Close</a>';
					echo '</div>';
					$count ++;
				}
			echo '</div>';
		}
		
		/* Calculate order profit */
		public function supplier_profit_calc($order_id){
			$supplier_total_cost = 0;	
			$order = wc_get_order($order_id);
			$order_number = $order->get_order_number();
			$order_items = $order->get_items();
			$num = count($order_items);
			foreach ( $order_items as $item_id => $item ) {
				$supplier_shipping_cost = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_shipping_cost]');
				$supplier_cost = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_cost]');
				$supplier_qty = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_qty]');
				$supplier_total = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_total]');
				$supplier_refund_amount = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_refund_amount]'); 
				
				$supplier_total_cost += ($supplier_cost + $supplier_shipping_cost) * $supplier_qty - $supplier_refund_amount;
				if($supplier_total_cost != 0){
					$num -= 1;
				}		
			}
			$order_total = $order->get_total();
			$refunded_total = $order->get_total_refunded();
			if( $order->get_status() != 'refunded' ){
			?>
				<tr>
					<td class="label"><?php _e( 'Suppliers Total Cost', 'woocommerce' ); ?>:</td>
					<td width="1%"></td>
					<td class="total">$<?php echo $supplier_total_cost; ?></td>
				</tr>
				<?php if($num == 0) { ?>
				<tr>
					<td class="label"><?php _e( 'Total Order Profit', 'woocommerce' ); ?>:</td>
					<td width="1%"></td>
					<td class="total">$<?php echo $order_total - $supplier_total_cost - $refunded_total; ?></td>
				</tr>	
				<tr>
					<td class="label"><?php _e( 'Total Order Profit %', 'woocommerce' ); ?>:</td>
					<td width="1%"></td>
					<?php $total = ($order_total - $refunded_total - $supplier_total_cost)*100/$supplier_total_cost; ?>
					<td class="total"><?php echo number_format( $total, 2); ?>%</td>
				</tr>
				<?php } else { ?>
					<tr>
					<td colspan="3">Fill all supplier fields to see profit</td>
					</tr>
				<?php }
			}
		}
		
	}
}
