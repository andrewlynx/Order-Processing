<?php
/*
Plugin Name: Order Processing
Description:
Version: 0.0.2
Author: Andrew Melnik
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if ( is_admin() ){
	require_once plugin_dir_path( __FILE__ ) . 'class-order-processing-admin.php';
} else {
	require_once plugin_dir_path( __FILE__ ) . 'class-order-processing.php';
}

function run_order_processing() {	
	if ( is_admin() ){
		$plugin = new order_processing_admin();
	} else {
		$plugin = new order_processing();
	}
}

/* List of order items statuses */
function order_processing_get_statuses(){
	return array('Pending Payment',
		'Processing',
		'Sent to Dispatch Warehouse',
		'Item(s) Shipped',
		'Customer Return / Refund Requested',
		'Supplier Return / Refund Requested',
		'Awaiting Shipment Return',
		'Refunded',
		'Refund Completed - Manual'
	);
}

function order_processing_order_item_status($order, $order_number, $item_id ){
	$status = 'Pending Payment';
	$order_number = $order->get_order_number();
	if ($order->get_status() != 'pending'){
		$status = 'Processing';
	}
	if ($order->get_status() == 'senttodispatch592' || $order->get_status() == 'partiallyshipp759' || $order->get_status() == 'waitingshipmen659') {
		$status = 'Sent to Dispatch Warehouse';
	}
	$checked = get_option('order_processing['. $order_number .']['. $item_id .'][shipped]'); 
	if ($checked == 'on'){
		$status = 'Item(s) Shipped';
	}
	$saved_status = get_option('order_processing['. $order_number .']['. $item_id .'][order_status]');
	if($saved_status){
		return $saved_status;
	}
	$user_shipping_company = get_option('order_processing['. $order_number .']['. $item_id .'][user_shipping_company]');
	$user_track_number = get_option('order_processing['. $order_number .']['. $item_id .'][user_track_number]');
	$user_track_date = get_option('order_processing['. $order_number .']['. $item_id .'][user_track_date]');
	if ($user_shipping_company && $user_track_number && $user_track_date){
		$status = 'Awaiting Shipment Return';
	}
	if( $order->get_status() == 'refunded' ) {
		$status = 'Refunded';
	}
	return $status;
}

/* Updates order status after 12 hours */
function order_processing_order_status( $status, $order ){ 

	$order_number = $order->get_order_number();
	
	if ($status == 'processing'){
		
		$current_time = time();
		$order_time = get_post_time( 'U', true, $order->id );
		$past = ($current_time - $order_time) / (60 * 60); 
		if($past > 12) {
			$new_status = 'senttodispatch592';
			$order->post_status = 'wc-' . $new_status;
			$update_post_data  = array(
				'ID'          => $order->id,
				'post_status' => $order->post_status,
			);
			wp_update_post( $update_post_data );
			return $new_status;
		} 
	}
	return $status; 
}

function order_processing_send_email($email, $email_heading, $email_content, $from_name='' ){

	if(!empty($from_name)){
		global $_from_name;
		$_from_name = $from_name;
		add_filter('wp_mail_from', 'order_processing_return_email' );
		add_filter('wp_mail_from_name', 'order_processing_return_email' );
	}
	
	ob_start();
	
		include('emails/email.php');
		
		$to_send = ob_get_contents();
		
	ob_end_clean();
	
	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";									
	$success = wp_mail($email, $email_heading, $to_send, $headers );
	
	$from_name = get_option( 'woocommerce_email_from_address' );
	add_filter('wp_mail_from', 'order_processing_return_email');
	add_filter('wp_mail_from_name', 'order_processing_return_email');
	
	return $success;
}

add_filter('wp_mail_from','order_processing_wp_mail_from');
 
function order_processing_wp_mail_from($content_type) {
	$from_name = get_option( 'woocommerce_email_from_address' );
	return $from_name;
}

add_filter('wp_mail_from_name','order_processing_wp_mail_from_name');

function order_processing_wp_mail_from_name($name) {
	$from_name = get_option( 'woocommerce_email_from_name' );
	return $from_name;
}

function order_processing_return_email() {
	global $_from_name;
	return $_from_name;
}

run_order_processing();




