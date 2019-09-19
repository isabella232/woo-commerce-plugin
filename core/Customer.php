<?php

namespace core;

abstract class Customer {

    protected static $pluginName = 'campaign_monitor_woocommerce';

    private static $_limit;
    private static $_page;
    private static $_query;
    private static $_total;

    /**
     * @return int total orders in this store disregarding status
     */
    public static function getTotal(){
        $count = Order::getTotal();

        if (!empty($count)){
            self::$_total = $count[0];
        }else {
            self::$_total = 0;
        }

        return self::$_total;
    }

    /**
     *
     *
     * @param $orderId
     * @param bool $filter
     * @return \stdClass
     */
    public static function getDetails( $orderId, $filter = false){
        global $wpdb;

        $sql  = ' SELECT post_id, meta_key, meta_value FROM '.$wpdb->prefix.'postmeta WHERE post_id  = %s';
        $params = array($orderId);

        if ($filter) {
            $sql .= ' AND ( meta_key LIKE %s '; // like %_billing%
            $sql .= ' OR meta_key LIKE %s '; // like %_shipping%
            $sql .= ' OR meta_key LIKE %s '; // like %_customer%
            $sql .= ' OR meta_key LIKE %s '; // like %_order_total%
            $sql .= ' OR meta_key LIKE %s ) '; // like %_order_count%
            $params[] = "%_billing%";
            $params[] = "%_shipping%";
            $params[] = "%_customer%";
            $params[] = "%_order_total%";
            $params[] = "%_order_count%";
        }

        $sql .= ' ORDER BY meta_id ASC ';

        $statement = $wpdb->prepare($sql,$params );
        $results = $wpdb->get_results( $statement );
        $orderDetails = new \stdClass();

        $orderDetails->id = '';
        $orderDetails->name = '';
        $orderDetails->email = '';
        $orderDetails->order_total = '';
        $orderDetails->order_count = '';
        $orderDetails->order_id = $orderId;

        $orderDetails->billing_first_name = '';
        $orderDetails->billing_last_name = '';
        $orderDetails->billing_company = '';
        $orderDetails->billing_address = '';
        $orderDetails->billing_address2 = '';
        $orderDetails->billing_city = '';
        $orderDetails->billing_postcode = '';
        $orderDetails->billing_country = '';
        $orderDetails->billing_state = '';
        $orderDetails->billing_email = '';
        $orderDetails->billing_phone = '';
        $orderDetails->billing_paymethod = '';

        $orderDetails->shipping_first_name = '';
        $orderDetails->shipping_last_name = '';
        $orderDetails->shipping_company = '';
        $orderDetails->shipping_address = '';
        $orderDetails->shipping_address2 = '';
        $orderDetails->shipping_city = '';
        $orderDetails->shipping_postcode = '';
        $orderDetails->shipping_country = '';
        $orderDetails->shipping_state = '';
        $orderDetails->shipping_email = '';
        $orderDetails->shipping_phone = '';
        $orderDetails->shipping_paymethod = '';

        if (!empty( $results )) {
            foreach ($results as $result) {
                $key = ltrim($result->meta_key,'_');
                $orderDetails->{$key} = $result->meta_value;
            }

            $orderDetails->id = (isset($orderDetails->customer_user)) ? $orderDetails->customer_user : 0;
            $orderDetails->name = $orderDetails->billing_first_name . " " . $orderDetails->billing_last_name;
            $orderDetails->email = (!empty($orderDetails->billing_email)) ? $orderDetails->billing_email : $orderDetails->shipping_email;
        }

        return $orderDetails;
    }

    public static function getData( $page = 1, $limit = 10, $singleOrderID = 0 )
    {
        global $wpdb;

        self::$_query = "SELECT DISTINCT `order_id` FROM `{$wpdb->prefix}woocommerce_order_items`";
        self::$_limit = $limit;
        self::$_page = $page;

        if ($singleOrderID > 0){
            self::$_query .= ' WHERE `order_id` = ' . $singleOrderID;
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
            $details = self::getDetails($id);

            if (in_array($details->email,$in_data)){
                continue;
            }else {
                $in_data[] = $details->email;
            }
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


    public static function format($userDetails, $mappedFields, $isSubscribe = false )
    {
        $id = $userDetails->id;
        $userToExport = new \stdClass();
        $name = $userDetails->name;
        $email = $userDetails->email;

        if ($isSubscribe) {
            $isSubscribe = 'YES';
        } else {
            $isSubscribe = 'FALSE';
        }
        $fields = array();
        $fields['orders_count'] = 1;
        $fields['total_spent'] = isset( $userDetails->order_total ) ? $userDetails->order_total : 0;
        $fields['total_price'] = isset( $userDetails->order_total ) ? $userDetails->order_total : 0;
        $fields['created_at'] = current_time( 'mysql' );

        // this is a customer
        if ($id > 0) {
            $customer = get_userdata( $id );

            if (empty( $name )) {
                $name = $customer->user_nicename;
            }

            $fields['created_at'] = $customer->user_registered;
        }

        $ordersPerPage = 100;
        // count the number of orders for this email
        $orderCount = Order::getByEmail( $email );

        if ($orderCount > 0) {
            // calculate the total number of pages.
            $totalPages = ceil( $orderCount / $ordersPerPage );
            $lastOrderSpent = 0;
            $totalSpent = 0;
            // foreach order paged
            for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++) {
                $orderIds = Order::getByEmail( $email, false, $currentPage, $ordersPerPage );
                $total = 0;

                $lastOrderSpent = 0;

                $index = 0;
                foreach ($orderIds as $orderId) {
                    $order = wc_get_order( $orderId );
                    $totalSpent += $order->get_total();
                    if ($index == 0) {
                        $lastOrderSpent = $order->get_total();
                    }
                    $index++;
                }

            }
            $fields['total_price'] = $lastOrderSpent;
            $fields['total_spent'] = $totalSpent;
            $fields['orders_count'] = $orderCount;
        }

        $userToExport->Name = $name;
        $userToExport->EmailAddress = $userDetails->email;
        $userToExport->QueueSubscriptionBasedAutoResponders = true;

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
            $customFields[] = array( "Key" => $value, 'Value' => $fields[$mapField] );
        }

        $userToExport->CustomFields = $customFields;

        return $userToExport;
    }
}