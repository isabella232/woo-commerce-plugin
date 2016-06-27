<?php

namespace core;

abstract class Customer {

    protected static $pluginName = 'campaign_monitor_woocommerce';

    private static $_limit;
    private static $_page;
    private static $_query;
    private static $_total;

    public static function getTotal(){
        global $wpdb;
        $count = $wpdb->get_col( "SELECT COUNT(DISTINCT `order_id`)  FROM `{$wpdb->prefix}woocommerce_order_items`" );

        if (!empty($count)){
            self::$_total = $count[0];
        }else {
            self::$_total = 0;
        }

        return self::$_total;
    }


    public static function getData( $page = 1, $limit = 10, $id = 0 )
    {
        global $wpdb;

        self::$_query = "SELECT DISTINCT `order_id` FROM `{$wpdb->prefix}woocommerce_order_items`";
        self::$_limit = $limit;
        self::$_page = $page;

        if ($id > 0){
            self::$_query .= ' WHERE `order_id` = ' . $id;
        }


        if (self::$_limit == 'all') {
            $query = self::$_query;
        } else {

            $query = self::$_query . " LIMIT " . ((self::$_page - 1) * self::$_limit) . ", " . self::$_limit;
        }

        $ids = $wpdb->get_col($query);

        $data = array();

        $in_data = array();
        foreach ($ids as $id) {

            $details = new \stdClass();
            $email = get_post_meta($id, '_billing_email', true);

            if (in_array($email,$in_data)){
                continue;
            }else {
                $in_data[] = $email;
            }

            $firstName = get_post_meta($id, '_billing_first_name', true);
            $lastName = get_post_meta($id, '_billing_last_name', true);

            $details->id = get_post_meta($id, '_customer_user', true);
            $details->name = $firstName . ' ' . $lastName;

            $details->email = $email;
            $details->order_total = get_post_meta($id, '_order_total', true);
            $details->order_count = get_post_meta($id, '_order_count', true );

            $details->order_id = $id;

            $details->billing_first_name = get_post_meta($id, '_billing_first_name', true);
            $details->billing_last_name = get_post_meta($id, '_billing_last_name', true);
            $details->billing_company = get_post_meta($id, '_billing_company', true);
            $details->billing_address = get_post_meta($id, '_billing_address_1', true);
            $details->billing_address2 = get_post_meta($id, '_billing_address_2', true);
            $details->billing_city = get_post_meta($id, '_billing_city', true);
            $details->billing_postcode = get_post_meta($id, '_billing_postcode', true);
            $details->billing_country = get_post_meta($id, '_billing_country', true);
            $details->billing_state = get_post_meta($id, '_billing_state', true);
            $details->billing_email = get_post_meta($id, '_billing_email', true);
            $details->billing_phone = get_post_meta($id, '_billing_phone', true);
            $details->billing_paymethod = get_post_meta($id, '_payment_method', true);

            $details->shipping_first_name = get_post_meta($id, '_shipping_first_name', true);
            $details->shipping_last_name = get_post_meta($id, '_shipping_last_name', true);
            $details->shipping_company = get_post_meta($id, '_shipping_company', true);
            $details->shipping_address = get_post_meta($id, '_shipping_address_1', true);
            $details->shipping_address2 = get_post_meta($id, '_shipping_address_2', true);
            $details->shipping_city = get_post_meta($id, '_shipping_city', true);
            $details->shipping_postcode = get_post_meta($id, '_shipping_postcode', true);
            $details->shipping_country = get_post_meta($id, '_shipping_country', true);
            $details->shipping_state = get_post_meta($id, '_shipping_state', true);
            $details->shipping_email = get_post_meta($id, '_shipping_email', true);
            $details->shipping_phone = get_post_meta($id, '_shipping_phone', true);
            $details->shipping_paymethod = get_post_meta($id, '_payment_method', true);

            $data[] = $details;
        }


        $data = self::clean($data);

        $result = new \stdClass();
        $result->page = self::$_page;
        $result->limit = self::$_limit;
        $result->total = (empty(self::$_total)) ? self::getTotal() : self::$_total;
        $result->data = $data;

        return $result;
    }

    protected static function clean($data){
        if (!empty($data)){
            $data = array_map( 'unserialize', array_unique( array_map( 'serialize', $data ) ) );
        }

        return $data;
    }


    public static function createLinks( $links, $list_class ) {
        if (self::$_limit == 'all' ) {
            return '';
        }

        $last       = ceil(self::$_total /self::$_limit );

        $start      = ( (self::$_page - $links ) > 0 ) ?self::$_page - $links : 1;
        $end        = ( (self::$_page + $links ) < $last ) ?self::$_page + $links : $last;

        $html       = '<ul class="' . $list_class . '">';

        $class      = (self::$_page == 1 ) ? "disabled" : "";
        $html       .= '<li class="' . $class . '"><a href="?limit=' .self::$_limit . '&page=' . (self::$_page - 1 ) . '">&laquo;</a></li>';

        if ( $start > 1 ) {
            $html   .= '<li><a href="?limit=' .self::$_limit . '&page=1">1</a></li>';
            $html   .= '<li class="disabled"><span>...</span></li>';
        }

        for ( $i = $start ; $i <= $end; $i++ ) {
            $class  = (self::$_page == $i ) ? "active" : "";
            $html   .= '<li class="' . $class . '"><a href="?limit=' .self::$_limit . '&page=' . $i . '">' . $i . '</a></li>';
        }

        if ( $end < $last ) {
            $html   .= '<li class="disabled"><span>...</span></li>';
            $html   .= '<li><a href="?limit=' .self::$_limit . '&page=' . $last . '">' . $last . '</a></li>';
        }

        $class      = (self::$_page == $last ) ? "disabled" : "";
        $html       .= '<li class="' . $class . '"><a href="?limit=' .self::$_limit . '&page=' . (self::$_page + 1 ) . '">&raquo;</a></li>';

        $html       .= '</ul>';

        return $html;
    }


    public static function format($userDetails, $mappedFields, $isSubscribe = false ){

        $id = $userDetails->id;
        $userToExport = new \stdClass();
        $name = $userDetails->name;
        $email = $userDetails->email;


        if ($isSubscribe){
            $isSubscribe = 'TRUE';
        }else {
            $isSubscribe = 'FALSE';
        }
        $fields = array();
        $fields['orders_count']  = 1;
        $fields['total_spent'] = $userDetails->order_total;
        $fields['total_price'] =$userDetails->order_total;
        $fields['created_at']  = current_time( 'mysql' );

        if ($id > 0) {
            $customer = get_userdata($id);

            if (empty($name)) {
                $name = $customer->user_nicename;
            }

            // Get all customer orders
            $customer_orders = get_posts(array(
                'numberposts' => -1,
                'meta_key' => '_customer_user',
                'meta_value' => $id,
                'post_type' => wc_get_order_types(),
                'post_status' => array_keys(wc_get_order_statuses()),
            ));

            $total = 0;
            $orderCount = 0;
            foreach ( $customer_orders as $customer_order ) {
                $order = wc_get_order( $customer_order->ID );
                $total += $order->get_total();
                $orderCount++;
            }

            $fields['orders_count'] = $orderCount;
            $fields['total_price'] = $total;
            $fields['total_spent'] = $total;
            $fields['created_at'] = $customer->user_registered;

        }

        $userToExport->Name = $name;
        $userToExport->EmailAddress = $userDetails->email;


        $fields['newsletter_subscribers'] = $isSubscribe;
        $fields['billing_address1'] = $userDetails->billing_address;
        $fields['billing_address2'] = $userDetails->billing_address2;
        $fields['billing_city'] = $userDetails->billing_city;
        $fields['billing_zip'] = $userDetails->billing_postcode;
        $fields['billing_country'] = $userDetails->billing_country;
        $fields['billing_state'] = $userDetails->billing_state;
        $fields['phone'] = $userDetails->billing_phone;
        $fields['shipping_address1'] = $userDetails->shipping_address;
        $fields['shipping_address2'] = $userDetails->shipping_address2;
        $fields['shipping_city'] = $userDetails->shipping_city;
        $fields['shipping_zip'] = $userDetails->shipping_postcode;
        $fields['shipping_country'] = $userDetails->shipping_country;
        $fields['shipping_state'] = $userDetails->shipping_state;

        $customFields = array();

        foreach ($mappedFields as $mapField => $value) {
            $customFields[] = array("Key" => $value, 'Value' => $fields[$mapField]);
        }

        $userToExport->CustomFields = $customFields;

        return $userToExport;
    }
}