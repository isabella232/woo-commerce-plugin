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
                    ));
                    Log::write("Got orders");
                    $fields = array();
                    $fields['orders_count'] = count($customer_orders);
                    $fields['total_spent'] = wc_get_customer_total_spent($id);
                    $fields['verified_email'] = $customer->user_email;
                    $fields['created_at'] = $customer->user_registered;

                    $customFields = array();
                    foreach ($mappedFields as $mapField => $value) {
                        $customFields[] = array("Key" => $value, 'Value' => $fields[$mapField]);
                    }

                    $userToExport->CustomFields = $customFields;
                    $subscribers[] = (array)$userToExport;
                }
                Log::write("Sending customer $name data to campaign monitor");
                $results = App::$CampaignMonitor->import_subscribers(Settings::get('default_list'), $subscribers);
                Log::write($results);
            }

            wp_mail('leandro@sunriseintegration.com', 'Data Sync', 'Data was successfully synchronized');
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