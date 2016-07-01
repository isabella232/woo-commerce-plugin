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

            // count the number of users found in the query
            $total_users = Customer::getTotal();
            $listDetails = App::$CampaignMonitor->get_list_details($defaultListId);

            // calculate the total number of pages.
            $total_pages = ceil($total_users / $users_per_page);
            $subscribedUsers = Subscribers::get();
            $subscribers = array();
            $totalUserSync = 0;
            $results = null;
            for ($currentPage = 1; $currentPage <= $total_pages; $currentPage++) {

                $results =  \core\Customer::getData($currentPage, $users_per_page);
                $data = $results->data;
                $totalUserSync += count($data);

                $mappedFields = Map::get();

                foreach ($data as $datum) {

                    if (in_array($datum->email, $subscribedUsers)){
                        $isSubscribe = true;
                    }
                    $formattedCustomer = Customer::format($datum, $mappedFields, $isSubscribe);
                    $subscribers[] = (array)$formattedCustomer;
                }
                Log::write($subscribers);
                $results = App::$CampaignMonitor->import_subscribers($defaultListId, $subscribers);
            }

            if (!empty($results)){
                $totalUniqueEmailsSubmitted  =  $results->TotalUniqueEmailSubmitted;
                $totalExistingSubscribers = $results->TotalExistingSubscribers;
                $totalNewSubscribers = $results->TotalNewSubscribers;
                $duplicateEmailsInSubmission = $results->DuplicateEmailsInSubmission;

                $message = array();
                $message['Total Unique Emails'] = $totalUniqueEmailsSubmitted;
                $message['Total Existing Subscribers'] = $totalExistingSubscribers;
            }

            $message['subscribers_count'] = $totalUserSync;
            $toEmail = get_option('admin_email');
            $response = App::$CampaignMonitor->send_email($toEmail, $listDetails->Title, $message);
            Log::write($response);
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