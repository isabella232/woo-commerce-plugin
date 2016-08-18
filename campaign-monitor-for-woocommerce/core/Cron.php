<?php

namespace core;


class Cron
{
    public static $counter = 0;

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
            if (( $pid = CronHelper::lock() ) !== FALSE) {

                $defaultListId = Settings::get( 'default_list' );
                Log::write( "Processing list: " . $defaultListId );

                $users_per_page = 10;
                $page = 1;

                // count the number of users found in the query
                $total_users = Customer::getTotal();
                Log::write( "Total orders: " . $total_users );
                $listDetails = App::$CampaignMonitor->get_list_details( $defaultListId );

                // calculate the total number of pages.
                $total_pages = ceil( $total_users / $users_per_page );
                $subscribedUsers = Subscribers::get();

                $totalUserSync = 0;
                $results = null;
                Log::write( "number of pages " . $total_pages );
                $totalUniqueEmailsSubmitted = 0;
                $totalExistingSubscribers = 0;
                $totalNewSubscribers = 0;
                $duplicateEmailsInSubmission = 0;
                for ($currentPage = 1; $currentPage <= $total_pages; $currentPage++) {
                    Log::write( "page number: " . $currentPage );
                    $subscribers = array();

                    $results = \core\Customer::getData( $currentPage, $users_per_page );
                    $data = $results->data;
                    $totalUserSync += count( $data );

                    $mappedFields = Map::get();

                    foreach ($data as $datum) {
                        $isSubscribe = false;
                        if (!empty( $subscribedUsers )) {
                            if (in_array( $datum->email, $subscribedUsers )) {
                                $isSubscribe = true;
                            }

                        }

                        $formattedCustomer = Customer::format( $datum, $mappedFields, $isSubscribe );
                        $subscribers[] = (array)$formattedCustomer;
                    }

                    $results = App::$CampaignMonitor->import_subscribers( $defaultListId, $subscribers );

                    if (!empty( $results )) {
                        $totalUniqueEmailsSubmitted += (int)$results->TotalUniqueEmailsSubmitted;
                        $totalExistingSubscribers += (int)$results->TotalExistingSubscribers;
                        $totalNewSubscribers += (int)$results->TotalNewSubscribers;
                        $duplicateEmailsInSubmission += (int)$results->DuplicateEmailsInSubmission;
                    }
                }

                $message = array();
                $message['Total Unique Emails'] = $totalUniqueEmailsSubmitted;
                $message['Total Existing Subscribers'] = $totalExistingSubscribers;
                $message['subscribers_count'] = $totalUserSync;
                $toEmail = get_option( 'admin_email' );
                Log::write( $totalUniqueEmailsSubmitted );

                $response = App::$CampaignMonitor->send_email( $toEmail, $listDetails->Title, $message );
                Log::write($response);
                Settings::add( 'data_sync', null );

                CronHelper::unlock();
            }

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