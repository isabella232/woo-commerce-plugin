<?php

namespace core;

abstract class Order {

    protected static $pluginName = 'campaign_monitor_woocommerce';

    private static $_limit;
    private static $_page;
    private static $_query;
    private static $_total;

    public static function getByEmail( $email, $displayCount = true, $page = 1, $limit = 10){
        global $wpdb;

        $verb = '';

        if ($displayCount){
            $verb = "COUNT( DISTINCT p.ID ) AS total ";
        } else {

            $verb = "p.ID";
        }

        $sql =  " SELECT {$verb} FROM {$wpdb->prefix}posts AS p INNER JOIN {$wpdb->prefix}postmeta AS pm  ON ( p.ID = pm.post_id ) WHERE 1=1 ";
        $sql .= " AND ( ( pm.meta_key = '_billing_email' AND pm.meta_value = %s )) ";
        $sql .= " AND p.post_type IN ('shop_order', 'shop_order_refund') ";
        $sql .= " AND ((p.post_status = 'wc-pending' OR p.post_status = 'wc-processing' ";
        $sql .= " OR p.post_status = 'wc-on-hold' OR p.post_status = 'wc-completed' ";
        $sql .= " OR p.post_status = 'wc-cancelled' OR p.post_status = 'wc-refunded' ";
        $sql .= " OR p.post_status = 'wc-failed')) ";

        if (!$displayCount){
            $start = ($page - 1) * $limit;
            $sql .= " GROUP BY p.ID ORDER BY p.post_date DESC, p.ID LIMIT {$start}, {$limit}";
        }

        $statement = $wpdb->prepare($sql, $email);

        $results = $wpdb->get_col( $statement);

        if ($displayCount) {
            if (!empty( $results )) {
                return current( $results );
            }
        }
        return $results;
    }

    /**
     * @return int total orders in this store disregarding status
     */
    public static function getTotal(){
        global $wpdb;
        $count = $wpdb->get_col( "SELECT COUNT(DISTINCT `order_id`)  FROM `{$wpdb->prefix}woocommerce_order_items`" );

        if (!empty($count)){
           return $count[0];
        }

        return 0;
    }

}