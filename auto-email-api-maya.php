<?php
/*
Plugin Name: Auto Email API
Plugin URI: https://www.maya.lk
Description: Brand Awareness Journey API and Product Portfolio and Cart Abandonment Journey API
Version: 1.0
Author: TharinduH
Author URI: https://www.maya.lk
*/

require_once 'inc/api.php';
require_once 'inc/woo_config.php';

//Create Request ID for Registration user
add_action( 'user_register', 'aeam_user_registration', 10, 1 );
function aeam_user_registration( $user_id ) {

    error_log('==================== AUTO_EMAIL_API_REGISTER_USER ======================');

    $access_token = aeam_get_access_token();
    $user_info = get_userdata($user_id);
    $firstName = ( isset($_POST['billing_first_name']) ) ? $_POST['billing_first_name'] : $user_info->first_name;

    error_log('Access Token : '.$access_token);
    error_log('User ID : '.$user_id);
    error_log('User FName : '.$firstName);
    error_log('User Email : '.$user_info->user_email);

    $response = aeam_register_user($access_token , $firstName , $user_info->user_email);

    if( isset($response->requestId) ){
        update_user_meta($user_id, 'aeam_requestId', $response->requestId);

        error_log('Response : '.$response->requestId);
    }
 
}

//Create Request ID for Order Status Change
add_action('woocommerce_order_status_changed','aeam_woo_order_status_change', 10, 3);
function aeam_woo_order_status_change( $order_id, $old_status, $new_status ){

    error_log('==================== AUTO_EMAIL_API_ORDER_STATUS ======================');

    $access_token = aeam_get_access_token();

    // Get an instance of the WC_Order object
    $order = wc_get_order( $order_id );
    $fname = $order->get_billing_first_name();
    $email = $order->get_billing_email();
    $productsArr = array();

    error_log('Access Token : '.$access_token);
    error_log('Order ID : '.$order);

    // Get and Loop Over Order Items
    foreach ( $order->get_items() as $item_id => $item ) {
        array_push( $productsArr, $item->get_name());
    }

    $products = implode(" , ",$productsArr);

    //Send API Request
    $response = aeam_order_status_change($access_token , $fname , $email , $order_id , $new_status , $new_status , $products);

    if( isset($response->requestId) ) {
        wc_update_order_item_meta($order_id,'aeam_woo_status_change_requestId',$response->requestId);

        error_log('Response : '.$response->requestId);
    }

}

//Create Request ID for Update Customer
add_action( 'woocommerce_update_customer', 'aeam_customer_update', 10, 1 );
function aeam_customer_update( $user_id ) {

    error_log('==================== AUTO_EMAIL_API_UPDATE_USER ======================');

    $access_token = aeam_get_access_token();
    $user_info = get_userdata($user_id);
    $firstName = get_user_meta($user_id,'billing_first_name',true);

    error_log('Access Token : '.$access_token);
    error_log('User ID : '.$user_id);
    error_log('User FName : '.$user_info->first_name);
    error_log('User Email : '.$user_info->user_email);

    $response = aeam_register_user($access_token , $user_info->first_name , $user_info->user_email);

    if( isset($response->requestId) ){
        update_user_meta($user_id, 'aeam_requestId', $response->requestId);

        error_log('Response : '.$response->requestId);
    }
 
}

//Get all registred users abandoned carts
function get_all_abandoned_cart_registerd_users() {

    global $wpdb;
    $userData                 = array();
    $blank_cart_info         = '{"cart":[]}';
    $blank_cart_info_guest   = '[]';
    $blank_cart              = '""';

    $ac_cutoff_time = get_option( 'ac_lite_cart_abandoned_time', 10 );
    $cut_off_time   = intval( $ac_cutoff_time ) * 60;
    $compare_time   = current_time( 'timestamp' ) - $cut_off_time; // phpcs:ignore

    $page_number    = isset( $_GET['paged'] ) && $_GET['paged'] > 1 ? sanitize_text_field( wp_unslash( $_GET['paged'] ) ) - 1 : 0; // phpcs:ignore

    $per_page = 100;
    $offset = 0;

    $results = $wpdb->get_results( // phpcs:ignore
        $wpdb->prepare(
            'SELECT wpac . * , wpu.user_login, wpu.user_email FROM `' . $wpdb->prefix . 'ac_abandoned_cart_history_lite` AS wpac LEFT JOIN ' . $wpdb->prefix . "users AS wpu ON wpac.user_id = wpu.id WHERE wpac.recovered_cart='0' AND wpac.cart_ignored <> '1' AND wpac.abandoned_cart_info NOT LIKE %s AND wpac.user_type = 'REGISTERED' AND wpac.abandoned_cart_info NOT LIKE %s AND wpac.abandoned_cart_info NOT LIKE %s AND wpac.abandoned_cart_time <= %d ORDER BY wpac.abandoned_cart_time DESC",
            "%$blank_cart_info%",
            "%$blank_cart%",
            $blank_cart,
            $compare_time
        )
    );

    if( $results ) {
        foreach( $results as $key => $value ) {

            $abandoned_order_id = $value->id;
			$user_id            = $value->user_id;
			$user_login         = $value->user_login;
            $cart_info        = json_decode( $value->abandoned_cart_info );

            $user_email_biiling = get_user_meta( $user_id, 'billing_email', true );
            $user_email         = __( 'User Deleted', 'woocommerce-abandoned-cart' );
            if ( isset( $user_email_biiling ) && '' === $user_email_biiling ) {
                $user_data = get_userdata( $user_id );
                if ( isset( $user_data->user_email ) && '' !== $user_data->user_email ) {
                    $user_email = $user_data->user_email;
                }
            } elseif ( '' !== $user_email_biiling ) {
                $user_email = $user_email_biiling;
            }

            $user_first_name_temp = get_user_meta( $user_id, 'billing_first_name', true );
            if ( isset( $user_first_name_temp ) && '' === $user_first_name_temp ) {
                $user_data = get_userdata( $user_id );
                if ( isset( $user_data->first_name ) && '' !== $user_data->first_name ) {
                    $user_first_name = $user_data->first_name;
                } else {
                    $user_first_name = '';
                }
            } else {
                $user_first_name = $user_first_name_temp;
            }

            $productName = array();
            if( isset($cart_info->cart) ) {
                foreach( $cart_info->cart as $key => $value ) {
                    $product = wc_get_product( $value->product_id );
                    array_push($productName , $product->get_name());
                }
            }

            $itemData = array(
                'user_email' => $user_email,
                'user_first_name' => $user_first_name,
                'cart_info' => implode(" , ",$productName)
            );

            array_push($userData , $itemData);
        }
    }

    return $userData;

}

//Create Request IDs for Abandoned Carts
function aeam_user_abandoned_carts() {

    error_log('==================== AUTO_EMAIL_API_ABANDONED_CARTS ======================');

    $access_token = aeam_get_access_token();

    error_log('Access Token : '.$access_token);

    $abandonedCarts = get_all_abandoned_cart_registerd_users();

    if( $abandonedCarts ) {
        foreach ( $abandonedCarts as $cart ) {

            $fname = $cart['user_first_name'];
            $email = $cart['user_email'];
            $products = $cart['cart_info'];
            $order_id = '';
            $new_status = '';

            //Send API Request
            $response = aeam_order_status_change($access_token , $fname , $email , $order_id , $new_status , $new_status , $products);

            if( isset($response->requestId) ) {
                error_log('Response : '.$response->requestId);
            }

        }
    }    
 
}

//Abandoned Cart Cron
add_action( 'aeam_abandoned_cart_cron', 'aeam_abandoned_cart_cron_func' );
function aeam_abandoned_cart_cron_func() {
    aeam_user_abandoned_carts();
}