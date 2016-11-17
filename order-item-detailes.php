<?php 
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	
	?>
		<style>#order_shipping_line_items .inputs{display: none;} #woocommerce-order-items .woocommerce_order_items_wrapper { overflow-x: auto; } #woocommerce-order-downloads .buttons .select2-container, .select2-container-multi .select2-choices .select2-search-field input { width: auto !important; }
		</style>
			<div class="inputs">
				<div class="info-block"><h4>Item Order Status</h4>
					<p>
						<select style="width: 400px; max-width: 100%;" name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][order_status]" >
						<option disabled selected>Item Status</option>
						<?php foreach ($statuses as $status){ ?>
							<option value="<?php echo $status ?>" <?php echo selected($order_status, $status, false) ?> >
								<?php echo $status ?>
							</option>
						<?php } ?>
						</select>
						<input type="checkbox" name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][reset_status]">
						<label for="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][reset_status]">Reset manual status</label>
					</p>
				</div>
				<div class="info-block"><h4>Supplier Order Details</h4>
					<p><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_order_number]" placeholder="Supplier Order Number" value="<?php echo $supplier_order_number?>">Supplier Order Number</p>
					<p><input type="date" name="order_processing[<?php echo  $order_number ?>][<?php echo $item_id ?>][supplier_order_date]" value="<?php echo $supplier_order_date ?>"> Supplier Order Date</p>
					<p><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_shipping_cost]" placeholder="Supplier Shipping Cost" value="<?php echo $supplier_shipping_cost ?>"> Supplier Shipping Cost, $</p>
					<p><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_cost]" placeholder="Supplier Cost" value="<?php echo $supplier_cost ?>"> Supplier Cost, $</p>
					<p><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_qty]" placeholder="Supplier Qty" value="<?php echo $supplier_qty ?>"> Supplier Qty</p>
					<p><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_total]" placeholder="Supplier Total" value="<?php echo $supplier_total ?>"> Supplier Total, $</p>
				</div>
				<div class="info-block"><h4>Original Shipment Details</h4>	
				<input type="checkbox" <?php echo $checked ?> name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][shipped]">
				<label for="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][shipped]">Mark as shipped</label>
					<div <?php echo $visible ?>  class="details">
						<p>
							<input type="date" name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][date]" value="<?php echo $date ?>">Shipping Date
						</p>
						<p>
							<input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][tracking]" placeholder="Tracking number" value="<?php echo $tracking ?>">Tracking number
						</p>
						<p>
							<input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][company]" placeholder="Shipping Company" value="<?php echo $company ?>">Shipping Company
						</p>
						<p>
							<input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][notes]" placeholder="Tracking Information" value="<?php echo $notes ?>">Tracking Information
						</p>
					</div>
				</div>
				<div class="info-block"><h4>Customer Return Package Details</h4>
					<p>
						<textarea style="width: auto;" cols="40" rows="10" name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_return_address]" placeholder="Supplier Return Address" value="<?php echo $supplier_return_address ?>"> <?php echo $supplier_return_address ?></textarea> Supplier Return Address
					</p>
					<p>
						<input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][rma_num]" placeholder="RMA number" value="<?php echo $rma_num ?>"> RMA number
					</p>
				</div>
				<div class="info-block"><h4>Refund By Supplier Details</h4>
					<p>Have we been Refunded by Supplier? <input type="checkbox" <?php echo $supplier_refunded_yes ?> value="yes" name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_refunded_yes]"> Yes
					<input type="checkbox" <?php echo $supplier_refunded_no ?> value="no" name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_refunded_no]"> No
					<p><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_refund_method]" placeholder="Supplier Refund Method" value="<?php echo$supplier_refund_method ?>"> Supplier Refund Method </p>
					<p><input type="date" name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_refund_date]" value="<?php echo$supplier_refund_date?>"> Supplier Refund Date</p>
					<p><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_refund_amount]" placeholder="Supplier Refund Amount to Us" value="<?php echo$supplier_refund_amount ?>"> Supplier Refund Amount to Us </p>
					<p><textarea style="width: auto;" cols="40" rows="10" name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][supplier_refund_comments]" placeholder="Supplier Refund Comments" value="<?php echo$supplier_refund_comments?>"><?php echo $supplier_refund_comments ?></textarea> Supplier Refund Comments</p>
					<p><input name="order_processing[<?php echo $order_number ?>][<?php echo $item_id ?>][screenshot]" placeholder="Screenshot URL" value="<?php echo $screenshot ?>"> Screenshot URL</p>
					<?php if(!empty ($screenshot)){ ?>
						<img width="200" src="<?php echo $screenshot ?>">
					<?php } ?>
				</div>
				<?php if($user_shipping_company || $user_track_number || $user_track_date || $user_return_comment || $return_reason){ ?>
					<div class="info-block"><h4>Return Details</h4>
						<p>User Shipping company: <span style="color:red;"><?php echo $user_shipping_company ?></span></p>
						<p>User Track Number: <span style="color:red;"><?php echo $user_track_number ?></span></p>
						<p>User Shipping Date: <span style="color:red;"><?php echo $user_track_date ?></span></p>
						<p>Return comment: <span style="color:red;"><?php echo $user_return_comment ?></span></p>
						<p>Return reason: <span style="color:red;"><?php echo $return_reason ?></span></p>
					</div>
				<?php } ?>
			</div>