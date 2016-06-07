<?php

namespace core;


class Cron
{
    protected function getPrefix()
    {
        return 'campaign_monitor_woocommerce';
    }

    public function __construct()
    {
        // cron event
        add_filter('cron_schedules', array($this, 'schedules'));       // create custom schedules
        $cronHook = $this->getPrefix() . '_sync';
        $timestamp = wp_next_scheduled($cronHook);


        if ($timestamp == FALSE) {
            wp_schedule_event(time(), '1min', $cronHook);
        }


        add_action($cronHook, array($this, 'run'));

    }

    public function run()
    {
        if (Settings::get('data_sync')) {
            Log::write("Synchronizing data");
            $defaultListId = Settings::get('default_list');

            $users_per_page = 5;
            $page = 1;

            $customer_args = array(
                'fields' => 'ID',
                'role' => 'customer',
                'orderby' => 'registered',
                'number' => '9999999',
            );
            $customersQuery = new \WP_User_Query($customer_args);

            // count the number of users found in the query
            $total_users = $customersQuery->get_total();


            // calculate the total number of pages.
            $total_pages = ceil($total_users / $users_per_page);

            for ($currentPage = 1; $currentPage <= $total_pages; $currentPage++) {

                $offset = $users_per_page * ($currentPage - 1);

                $customer_args = array(
                    'fields' => 'all',
                    'role' => 'customer',
                    'orderby' => 'registered',
                    'number' => $users_per_page,
                    'offset' => $offset,
                );

                $customersQuery = new \WP_User_Query($customer_args);
                $customers = $customersQuery->get_results();

                $mappedFields = Map::get();
                $subscribers = array();

                foreach ($customers as $customer) {
                    $id = $customer->ID;
                    $customer  = get_userdata($id);
                    $userToExport = new \stdClass();
                    $name = $customer->first_name . " " . $customer->last_name;

                    if (empty($name)) {
                        $name = $customer->user_nicename;
                    }

                    $userToExport->Name = $name;
                    $userToExport->EmailAddress = $customer->user_email;

                    // Get all customer orders
                    $customer_orders = get_posts(array(
                        'numberposts' => -1,
                        'meta_key' => '_customer_user',
                        'meta_value' => $id,
                        'post_type' => wc_get_order_types(),
                        'post_status' => array_keys(wc_get_order_statuses()),
                        'orderby'          => 'date',
                        'order'            => 'DESC',
                    ));

                    Log::write("Got orders");

                    $orderCount = wc_get_customer_order_count($id);
                    $fields = array();
                    $fields['orders_count'] = $orderCount;
                    if ($orderCount > 0){
                        $order = $customer_orders[0];
                        $order = new \WC_Order($order->ID);
                        $orderAmount = $order->get_total();
                        $fields['total_price'] = $orderAmount;
                        Log::write($orderAmount);
                    }

                    $fields['total_spent'] = wc_get_customer_total_spent($id);
                    //$fields['verified_email'] = $customer->user_email;
                    $fields['created_at'] = $customer->user_registered;

                    $billing_first_name =  get_user_meta($id,'billing_first_name',true);
                    $billing_last_name = get_user_meta($id,'billing_last_name',true);
                    $billing_company = get_user_meta($id,'billing_company',true);
                    $billing_address = get_user_meta($id,'billing_address_1',true);
                    $billing_address2 = get_user_meta($id,'billing_address_2',true);
                    $billing_city = get_user_meta($id,'billing_city',true);
                    $billing_postcode = get_user_meta($id,'billing_postcode',true);
                    $billing_country = get_user_meta($id,'billing_country',true);
                    $billing_state = get_user_meta($id,'billing_state',true);
                    $billing_email = get_user_meta($id,'billing_email',true);
                    $billing_phone = get_user_meta($id,'billing_phone',true);
                    $billing_paymethod = get_user_meta($id,'payment_method',true);

                    $shipping_first_name =  get_user_meta($id,'shipping_first_name',true);
                    $shipping_last_name = get_user_meta($id,'shipping_last_name',true);
                    $shipping_company = get_user_meta($id,'shipping_company',true);
                    $shipping_address = get_user_meta($id,'shipping_address_1',true);
                    $shipping_address2 = get_user_meta($id,'shipping_address_2',true);
                    $shipping_city = get_user_meta($id,'shipping_city',true);
                    $shipping_postcode = get_user_meta($id,'shipping_postcode',true);
                    $shipping_country = get_user_meta($id,'shipping_country',true);
                    $shipping_state = get_user_meta($id,'shipping_state',true);
                    $shipping_email = get_user_meta($id,'shipping_email',true);
                    $shipping_phone = get_user_meta($id,'shipping_phone',true);
                    $shipping_paymethod = get_user_meta($id,'payment_method',true);


                    $fields['billing_address1'] = $billing_address;
                    $fields['billing_address2'] = $billing_address2;
                    $fields['billing_city'] = $billing_city;
                    $fields['billing_zip'] = $billing_postcode;
                    $fields['billing_country'] = $billing_country;
                    $fields['billing_state'] = $billing_state;
                    $fields['phone'] = $billing_phone;
                    $fields['shipping_address1'] = $shipping_address ;
                    $fields['shipping_address2'] = $shipping_address2;
                    $fields['shipping_city'] = $shipping_city;
                    $fields['shipping_zip'] = $shipping_postcode;
                    $fields['shipping_country'] = $shipping_country;
                    $fields['shipping_state'] = $shipping_state;

                    $customFields = array();

                    foreach ($mappedFields as $mapField => $value) {
                        $customFields[] = array("Key" => $value, 'Value' => $fields[$mapField]);
                    }

                    $userToExport->CustomFields = $customFields;
                    $subscribers[] = (array)$userToExport;
                }
                $listDetails = App::$CampaignMonitor->get_list_details($defaultListId);
                Log::write("Sending customer $name data to campaign monitor");
                $results = App::$CampaignMonitor->import_subscribers($defaultListId, $subscribers);
                Log::write($results);
            }

            wp_mail('leandro@sunriseintegration.com', 'Data Sync', 'Data to '.$listDetails->Title.' was successfully synchronized');
            Settings::add('data_sync', null);
        }

    }

    public function unschedule()
    {
        $cronHook = $this->getPrefix() . '_sync';
        $timestamp = wp_next_scheduled($cronHook);
        wp_unschedule_event($timestamp, $cronHook);
    }

    public function schedules($schedules)
    {
        if (!isset($schedules["1min"])) {
            $schedules["1min"] = array(
                'interval' => 1 * 60,
                'display' => __('Once every 1 minutes'));
        }
        if (!isset($schedules["5min"])) {
            $schedules["5min"] = array(
                'interval' => 5 * 60,
                'display' => __('Once every 5 minutes'));
        }
        if (!isset($schedules["30min"])) {
            $schedules["30min"] = array(
                'interval' => 30 * 60,
                'display' => __('Once every 30 minutes'));
        }
        return $schedules;
    }
}