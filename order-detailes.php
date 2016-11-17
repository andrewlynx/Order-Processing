<?php 
	if($company || $date || $tracking || $notes){ ?>
		<div class="order-shipping">
			<?php if ( empty($company) ) { 
				$company = 'N/A';
			}	
			if ( empty($date) ) {
				$date = 'N/A';
			}	
			if ( empty($tracking) ) {
				$tracking = 'N/A';
			}	
			if ( empty($notes) ) {
				$notes = 'N/A';
			} ?>
			<span class="col-md-6 shipping_company">Shipping Company: <span><?php echo $company ?></span></span>
			<span class="col-md-6 date_shipped">Date Shipped: <span><?php echo $date ?></span></span>
			<span class="col-md-6 tracking_number">Tracking Number: <span><?php echo $tracking ?></span></span>
			<span class="col-md-6 order_notes">Tracking details: <span><?php echo $notes ?></span></span>
		<div class="clear"></div></div>
	<?php } ?>
	
	<?php if ( $order->get_status() != 'refunded' && $status != 'Processing' && $status != 'Customer Return / Refund Requested' && $status != 'Supplier Return / Refund Requested' && $status != 'Awaiting Shipment Return' && $status != 'Refund Completed - Manual' && $status != 'Refunded' && $time_passed == false ) { ?>
		 <form method="POST" class="refund-form">
			 <input name="order_action" type="hidden" value="<?php echo $action ?>">
			<div class="refund-popup" style="display: none;">
				<span class="popup-close">X</span>
				<p>Please select Return / Refund Reason.</p>
				<p><input name="return_reason" type="radio" value="Goods not received">Refund (goods not received)</p>
				<p><input name="return_reason" type="radio" value="Faulty goods">Refund (faulty goods)</p>
				<p><input name="return_reason" type="radio" value="Unwanted goods">Return (unwanted goods)</p> 
				<p><textarea cols="30" rows="4" name="user_return_comment" type="text" placeholder="Please provide as much detail as possible"></textarea></p>
				<input type="submit" class="submit refund-button" value="<?php echo $button ?>">
			</div>
			<input name="order_id" type="hidden" value="<?php echo $order->id ?>">
			<input name="order_number" type="hidden" value="<?php echo $order_number ?>">
			<input name="item_id" type="hidden" value="<?php echo $item_id ?>">
			<input name="item_name" type="hidden" value="<?php echo $item["name"] ?>">
			<input name="shipping_price" type="hidden" value="<?php echo $shipping ?>">
			<input name="line_total" type="hidden" value="<?php echo $item["line_total"] ?>">
			<input name="shipping_total" type="hidden" value="<?php echo $shipping_total ?>">
			<input name="order_total" type="hidden" value="<?php echo $order_total ?>">
			<span class="submit refund-button"><?php echo $button ?></span>
			<?php echo $this->popup_script(); ?>
		</form>
	<?php } ?>
	<?php if ($status == 'Customer Return / Refund Requested'){ ?>
		<p class="order-notice">Your request is currently being processed.<br/> Please allow up to 72 hours for us to process your request. Thank you.</p>
	<?php } ?>
	<?php if ($status == 'Awaiting Shipment Return') {
		$user_shipping_company = get_option('order_processing[' .$order_number .']['. $item_id .'][user_shipping_company]');
		$user_track_number = get_option('order_processing['. $order_number .']['. $item_id .'][user_track_number]');
		$user_track_date = get_option('order_processing['. $order_number .']['. $item_id .'][user_track_date]');
		$supplier_return_address = get_option('order_processing['. $order_number .']['. $item_id .'][supplier_return_address]');
		$rma_num = get_option('order_processing['. $order_number .']['. $item_id .'][rma_num]'); ?>
		<div class="order-return">
			<span class="col-md-6">RMA number: <span><?php echo $rma_num ?></span></span>
			<span class="col-md-6">Supplier return address: <span><?php echo $supplier_return_address ?></span></span>
			<div class="clear"></div>
			<?php if(empty($user_shipping_company) OR empty($user_track_number) OR empty($user_track_date)){ ?>
				<form method="POST" class="refund-details order-notice refund-form">
				<p style="order-notice">Please enter below your return tracking details you have used to return the package.<br/>
				Please ensure the RMA Number and Return Address are clearly printed on the package:</p>
				<span class="col-md-4"><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id  ?>][user_shipping_company]" placeholder="Return Shipping Company" value="<?php echo $user_shipping_company ?>" required></span>
				<span class="col-md-4"><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id  ?>][user_track_number]" placeholder="Return Tracking Number" value="<?php echo $user_track_number ?>" required></span>
				<span class="col-md-4"><input type="date" name="order_processing[<?php echo $order_number ?>][<?php echo  $item_id  ?>][user_track_date]" placeholder="Return Date" value="<?php echo $user_track_date ?>" required></span>
				<button class="submit refund-button pull-right">Submit</button>
				<div class="clear"></div>
				<div style="display: none;" class="refund-popup">
					<p style="order-notice">Please ensure all information is correct. You will not be able to change it after submit</p>
					<input type="submit" class="submit refund-button" value="Submit">
					<span class="popup-close">X</span>
				</div>
				<?php echo $this->popup_script(); ?>
			</form>
			<?php } else { ?>
				<span class="col-md-6">Returning track date: <span><?php echo $user_track_date ?></span></span>
				<span class="col-md-6">Returning shipping company: <span><?php echo $user_shipping_company ?></span></span>
				<span class="col-md-6">Returning track number: <span><?php echo $user_track_number ?></span></span>
			<?php } ?>
		<div class="clear"></div></div>
	<?php } ?>
	
	<form method="POST" class="refund-form">
		<input name="order_action" type="hidden" value="contact_us">
		<div class="refund-popup message" style="display: none;">
			<span class="popup-close">X</span>
			<p><label for="order_number">Order number</label>
			<input disabled  value="<?php echo $order_number ?>" placeholder="<?php echo $order_number ?>"></p>
			<p><label for="order_date">Order date</label>
			<input disabled  value="<?php echo date("F j, Y", $order_time) ?>" placeholder="<?php echo date("F j, Y", $order_time) ?>"></p>
			<p><label for="order_status">Order status</label>
			<input disabled  value="<?php echo $status ?>" placeholder="<?php echo $status ?>"></p>
			<p><label for="item_name">Item name</label>
			<input disabled  value="<?php echo $item["name"] ?>" placeholder="<?php echo $item["name"] ?>"></p>
			<p><label for="sku_number">SKU number</label>
			<input disabled  value="<?php echo $sku ?>" placeholder="<?php echo $sku ?>"></p>
			<p><label for="user_email">Your email</label>
			<input disabled value="<?php echo $current_user->user_email ?>" placeholder="<?php echo $current_user->user_email ?>"></p>	
			<input type="hidden" name="order_number" value="<?php echo $order_number ?>"></p>
			<input type="hidden" name="order_date" value="<?php echo date("F j, Y", $order_time) ?>"></p>
			<input type="hidden" name="order_status" value="<?php echo $status ?>"></p>
			<input type="hidden" name="item_name" value="<?php echo $item["name"] ?>" ></p>
			<input type="hidden" name="item_url" value="<?php echo $url ?>" ></p>
			<input type="hidden" name="sku_number" value="<?php echo $sku ?>" ></p>
			<input type="hidden" name="user_email" value="<?php echo $current_user->user_email ?>" ></p>
			<label for="user_comment">Enter your message</label>
			<p><textarea cols="30" rows="4" name="user_comment" type="text" placeholder="Subject"></textarea></p>
			<input type="submit" class="submit refund-button" value="Send">
		</div>
		<span class="submit refund-button">Contact Us</span>
		<?php echo $this->popup_script(); ?>
	</form>
<?php 