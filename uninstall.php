<?php

// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

global $wpdb;
$results = $wpdb->get_results( 'DELETE  FROM '.$wpdb->options.' WHERE option_name LIKE "%campaign_monitor_woocommerce%"', OBJECT );
