<?php

namespace core;

/**
 * The controller for the app
 *
 * Class App
 * @package core
 */
class App
{

    private static $instance = null;
    public static $pluginLocation = null;
    public static $optionPrefix = 'campaign_monitor_woocommerce';
    public static $session = null;
    public static $pluginPath = '';
    public static $notices = '';
    public static $pluginDirectory = '';


    public static $CampaignMonitor = null;
    public static $Cron = null;

    public static function getPath(){
        return plugin_dir_path(dirname(__FILE__));

    }
    public static function getDirectoryUrl(){
        return plugins_url('',dirname(__FILE__)) . '/';
    }

    public static function run()
    {

        // Get an instance of the
        if (null == self::$instance) {
            self::$instance = new self;
        } // end if

        return self::$instance;

    }

    public static function getConnectUrl(){
        $adminUri = get_admin_url() . 'admin.php';
        $instantiateUrl = self::$CampaignMonitor->instantiate_url("campaign-monitor-for-woo-commerce", $adminUri, Helper::getCampaignMonitorPermissions());

        return $instantiateUrl;

    }

    protected $controller = 'connect';
    protected $method = 'index';
    protected $params = array();

    private function __construct()
    {

        $isWoocommerceInstalled = in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
        if (!$isWoocommerceInstalled) {

            add_option('campaign_monitor_woocommerce_auto_deactivate', true);
            add_action('admin_init', array(__CLASS__, 'auto_deactivate'));
            add_action('admin_notices', array(__CLASS__, 'plugin_notices'));

        } else {
            // install app
            register_activation_hook(self::$pluginPath, array(__CLASS__, 'plugin_activation'));
            register_deactivation_hook(self::$pluginPath, array(__CLASS__, 'plugin_deactivation'));

            add_action('admin_notices', array(__CLASS__, 'plugin_notices'));
            add_action('admin_menu', array(__CLASS__, 'create_menu'));
            add_action('admin_enqueue_scripts', array(__CLASS__, 'load_custom_wp_admin_scripts'));
            add_action('wp_enqueue_scripts', array(__CLASS__, 'load_custom_wp_scripts'));
            add_filter('admin_body_class', array(__CLASS__, 'add_admin_body_class'));
            add_action('admin_menu', array(__CLASS__, 'custom_menu_page_removing'));
            add_action('admin_post_handle_request', array(__CLASS__, 'handle_request'));
            add_action('admin_post_nopriv', array(__CLASS__, 'handle_request'));

            add_action('woocommerce_review_order_after_submit', array(__CLASS__, 'woocommerce_subscription_box'));
            add_action('woocommerce_review_order_before_submit', array(__CLASS__, 'woocommerce_subscription_box_before'));
            add_action('woocommerce_checkout_order_processed', array(__CLASS__, 'checkout_process'));
            add_action('woocommerce_order_status_completed', array(__CLASS__, 'checkout_process'));
            add_action('profile_update', array(__CLASS__, 'profile_update'), 12);


            $accessToken = Settings::get('access_token');
            $refreshToken = Settings::get('refresh_token');
            self::$CampaignMonitor = new CampaignMonitor($accessToken, $refreshToken);

            $isError = (isset($_GET['error']) && !empty($_GET['error'])) && (isset($_GET['page']) && $_GET['page'] === 'campaign_monitor_woocommerce');

            if ($isError) {

                Helper::updateOption('no_ssl', true);
                $_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
                $title = $_GET['error'];
                $description = $_GET['error_description'];
                $error['title'] = $title;
                $error['description'] = $description;

                Helper::updateOption('post_errors', $error);

                $settingsPage = get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce_settings';
                wp_redirect($settingsPage);
                die();
            }

            $fileContent = file_get_contents("php://input");
            if (!empty($fileContent)){
                $credentials = json_decode($fileContent);

                if (!empty($credentials)){
                    if (!empty($credentials)) {
                        if (isset($credentials->ClientId) && isset($credentials->ClientSecret)) {

                            // extract client id and client secret from post request
                            $clientId = $credentials->ClientId;
                            $clientSecret = $credentials->ClientSecret;

                            // save for subsequent request
                            \core\Settings::add('client_secret', $clientSecret );
                            \core\Settings::add('client_id', $clientId);

                            $authorizeUrl = self::$CampaignMonitor->authorize_url($clientId,Helper::getRedirectUrl() , Helper::getCampaignMonitorPermissions() );

                            // redirect to get an access token
                            wp_redirect($authorizeUrl);
                            die();
                        }
                    }
                }
            }

            self::$Cron = new Cron();
            self::$session = new Session();

            Log::$switch = Helper::getOption('debug');

            // handle ajax
            Ajax::run();
            self::create_fields();

            $date = new \DateTime();
            $today = $date->format('Y-m-d H:i:s');
            $cleanDate = Settings::get('log_clean_date');
            $now     = strtotime($today);
            $tomorrow  = strtotime('now + 1 day');

            if (empty($cleanDate)){
                Settings::add('log_clean_date',$tomorrow);
            }

            $future = Settings::get('log_clean_date');

            if ($now > $future){
                Log::clear();
                Settings::add('log_clean_date',$tomorrow);
            }

        }

    } // end constructor

    public static function cron()
    {

    }

    public static function profile_update($user_id )
    {

        $details = new \stdClass();

        $userData = new \WP_User($user_id);
        $email = isset($userData->user_email) ? $userData->user_email : '';

        $firstName = get_user_meta($user_id, 'first_name', true);
        $lastName = get_user_meta($user_id, 'last_name', true);

        $details->id = $user_id;
        $details->name = $firstName . ' ' . $lastName;
        $details->email = $email;

        $details->order_total = get_user_meta($user_id, 'order_total', true);
        $details->order_count = get_user_meta($user_id, 'order_count', true);
        $details->billing_first_name = get_user_meta($user_id, 'billing_first_name', true);
        $details->billing_last_name = get_user_meta($user_id, 'billing_last_name', true);
        $details->billing_company = get_user_meta($user_id, 'billing_company', true);
        $details->billing_address = get_user_meta($user_id, 'billing_address_1', true);
        $details->billing_address2 = get_user_meta($user_id, 'billing_address_2', true);
        $details->billing_city = get_user_meta($user_id, 'billing_city', true);
        $details->billing_postcode = get_user_meta($user_id, 'billing_postcode', true);
        $details->billing_country = get_user_meta($user_id, 'billing_country', true);
        $details->billing_state = get_user_meta($user_id, 'billing_state', true);
        $details->billing_email = get_user_meta($user_id, 'billing_email', true);
        $details->billing_phone = get_user_meta($user_id, 'billing_phone', true);
        $details->billing_paymethod = get_user_meta($user_id, 'payment_method', true);

        $details->shipping_first_name = get_user_meta($user_id, 'shipping_first_name', true);
        $details->shipping_last_name = get_user_meta($user_id, 'shipping_last_name', true);
        $details->shipping_company = get_user_meta($user_id, 'shipping_company', true);
        $details->shipping_address = get_user_meta($user_id, 'shipping_address_1', true);
        $details->shipping_address2 = get_user_meta($user_id, 'shipping_address_2', true);
        $details->shipping_city = get_user_meta($user_id, 'shipping_city', true);
        $details->shipping_postcode = get_user_meta($user_id, 'shipping_postcode', true);
        $details->shipping_country = get_user_meta($user_id, 'shipping_country', true);
        $details->shipping_state = get_user_meta($user_id, 'shipping_state', true);
        $details->shipping_email = get_user_meta($user_id, 'shipping_email', true);
        $details->shipping_phone = get_user_meta($user_id, 'shipping_phone', true);
        $details->shipping_paymethod = get_user_meta($user_id, 'payment_method', true);

        $listId = Settings::get('default_list');
        $mappedFields = Map::get();
        $isSubscribe = false;
        $userToExport = Customer::format($details, $mappedFields, $isSubscribe);

        $userToExport= (array)$userToExport;

        $result = self::$CampaignMonitor->import_subscribers($listId, array($userToExport));
        Log::write($result);
    }

    /**
     * Change Proceed To Checkout Text in WooCommerce
     * Place this in your Functions.php file
     **/
    public static function woocommerce_subscription_box_before() {
        $legend = Helper::getOption('subscribe_text');
        $autoSubscribe = Helper::getOption('automatic_subscription');

        $isChecked = '';
        if (!empty($autoSubscribe) && $autoSubscribe ){
            $isChecked = 'checked="checked"';
        }
        if (empty($legend)){
            $legend = 'Subscribe to our newsletter';
        }

        $html = '';
        $html .= '<input id="subscriptionNonce" type="hidden" name="subscription_nonce" value="'.wp_create_nonce('app_nonce').'">';
        $html .= '<label for="cmw_register_email" style="margin-bottom: 7px;">';
        $html .= '<input id="cmw_register_email" name="cmw_register_email" style="margin-right: 7px;" value="on" '.$isChecked.' type="checkbox">'.$legend.'</label>';
        $subscriptionBox = \core\Helper::getOption('toggle_subscription_box');

        if ($subscriptionBox){
            echo $html;
        }
    }

    public static function checkout_process($orderId){

        if (!empty($orderId)){
            $order = new \WC_Order( $orderId );
            $address = $order->get_address();
            $page = 1;
            $limit = 1;
            $isSubscribe = false;
            $newCustomer = Customer::getData($page, $limit, $orderId);
            $email = '';
            if (array_key_exists('billing_email',$_POST ) && !empty($_POST['billing_email']))
            {
                $email = filter_input(INPUT_POST, 'billing_email', FILTER_SANITIZE_STRING);

                if (is_email($email)) {
                    $email = sanitize_email($email);
                }else {
                    $email = '';
                    Log::write('The email provided doesn\'t seem to be valid!');
                }

            }

            if (array_key_exists('cmw_register_email', $_POST)){
                if (!empty($email)){
                    Subscribers::add($email);
                    $isSubscribe = true;
                }
            }

            if (!empty($newCustomer->data)){
                $listId = Settings::get('default_list');
                $mappedFields = Map::get();
                $details = current($newCustomer->data);

                $autoSubscribe = Helper::getOption('automatic_subscription');
                if (!empty($autoSubscribe) && $autoSubscribe ){
                    if (!empty($email)){
                        Subscribers::add($email);
                    }
                }

                if (!empty($email) || isset($address['email'])){
                    if (isset($address['email']) && !empty($address['email'])){
                        $emailToEnroll = $address['email'];
                    } else if (!empty($email)){
                        $emailToEnroll = $email;
                    }

                    $alreadyEnroll = Subscribers::get($emailToEnroll);
                    if (!empty($alreadyEnroll)){
                        $isSubscribe = true;
                    }
                }

                $expiry = Settings::get('expiry');
                $auth = array(
                    'access_token' => Settings::get('access_token'),
                    'refresh_token' => Settings::get('refresh_token')
                );
                if ($expiry !== null && ($expiry - time() <  (60*60*24)))
                {
                    list($new_access_token, $new_expires_in, $new_refresh_token) = self::$CampaignMonitor->refresh_token($auth);

                    Settings::add('access_token',$new_access_token);
                    Settings::add('refresh_token',$new_refresh_token);
                    Settings::add('expiry',$new_expires_in + time());
                    $auth = array(
                        'access_token' => $new_access_token,
                        'refresh_token' => $new_refresh_token
                    );
                }

                $userToExport = Customer::format($details, $mappedFields, $isSubscribe);
                $userToExport = (array)$userToExport;
                $result = self::$CampaignMonitor->add_subscriber($listId, $userToExport, $auth);
                Log::write($result);

            }
        }
    }
    public static function woocommerce_subscription_box(){
        echo '<br></br>';
    }
    /**
     * @return bool true if connected false otherwise
     */
    public static function is_connected()
    {
        return Helper::getOption('connected');
    }

    protected static function create_fields()
    {
        $isFieldDefault = true;
        \core\Fields::add('orders_count', 'Total Order Count', 'Number', 'description for this item', $isFieldDefault);
        \core\Fields::add('total_spent', 'Total Spent', 'Number', 'description for this item', $isFieldDefault);
        //\core\Fields::add('verified_email', 'User Email', 'Text', 'description for this item', $isFieldDefault, false);
        \core\Fields::add('created_at', 'User Registered', 'Date', 'description for this item', $isFieldDefault);
        \core\Fields::add('total_price', 'Last Order Amount', 'Number', 'description for this item', $isFieldDefault);
        \core\Fields::add('newsletter_subscribers', 'Newsletter Subscriber', 'Text', 'description for this item', $isFieldDefault);

        \core\Fields::add('company', 'Company', 'Text', 'Company name', false);
        \core\Fields::add('billing_address1', 'Billing Address1', 'Text', 'Billing Address 1', false);
        \core\Fields::add('billing_address2', 'Billing Address2', 'Text', 'Billing Address 2', false);
        \core\Fields::add('billing_city', 'Billing City', 'Text', 'Billing City', false);
        \core\Fields::add('billing_zip', 'Billing Postal Code', 'Text', 'Billing Postal Code', false);
        \core\Fields::add('billing_country', 'Billing Country', 'Text', 'Billing Country', false);
        \core\Fields::add('billing_state', 'Billing Country/State', 'Text', 'Billing County', false);
        \core\Fields::add('phone', 'Telephone', 'Text', 'telephone', false);
        \core\Fields::add('shipping_address1', 'Shipping Address1', 'Text', 'Shipping Address 1', false);
        \core\Fields::add('shipping_address2', 'Shipping Address2', 'Text', 'Shipping Address 2', false);
        \core\Fields::add('shipping_city', 'Shipping City', 'Text', 'Shipping City', false);
        \core\Fields::add('shipping_zip', 'Shipping Postal Code', 'Text', 'Shipping Postal Code', false);
        \core\Fields::add('shipping_country', 'Shipping Country', 'Text', 'Shipping Country', false);
        \core\Fields::add('shipping_state', 'Shipping Country/State', 'Text', 'Shipping County', false);
    }

    public static function get_custom_fields()
    {

    }

    /**
     * TODO create handler class for http requests
     */
    public static function handle_request()
    {
        status_header(200);

        $data = $_REQUEST['data'];
        $nonce = $data['app_nonce'];
        $type = $data['type'];


        $nonce = wp_verify_nonce($nonce, 'app_nonce');
        switch ($nonce) {
            case TRUE :

                if ($type == 'create_client') {
                    $clientName = $data['client_name'];
                    $clientSettings = array(
                        'CompanyName' => $clientName,
                        'Country' => 'United States of America',
                        'Timezone' => '(GMT) Coordinated Universal Time'
                    );

                    $newClient = App::$CampaignMonitor->create_client($clientSettings);
                    Helper::updateOption('selectedClient', $newClient);
                }

                if ($type == 'create_list') {
                    $clientId = $data['client_id'];
                    $listName = $data['list_name'];
                    $optIn = $data['opt_in'];
                    $optIn = ($optIn == 2) ? true : false;
                    $newList = App::$CampaignMonitor->create_list($clientId, $listName, $optIn);


                    Settings::add('default_list', $newList);
                    Settings::add('default_client',$clientId );
                }

                if ($type == 'map_custom_fields') {
                    if (array_key_exists('fields', $data)) {
                        $fields = $data['fields'];
                        $listId = Settings::get('default_list');

                        if (array_key_exists('new_fields', $fields)) {
                            $newFields = $fields['new_fields'];
                            $items = $newFields['items'];

                            foreach ($items as $item) {
                                $createdField = App::$CampaignMonitor->create_custom_field($listId, $item['name'], ucfirst($item['type']));

                                if (!empty($createdField)) {
                                    Map::add($item['map_to'], $createdField);
                                }
                            }
                        }

                        foreach ($fields as $fieldKey => $options) {
                            $fieldKey = "[{$fieldKey}]";
                            $fieldName = $options['name'];
                            $mapTo = $options['map_to'];

                            if (empty($mapTo)) {
                                $removeByValue = true;
                                Map::remove($fieldKey, $removeByValue);
                            } else {
                                $updatedKey = App::$CampaignMonitor->update_custom_field($listId, $fieldKey, $fieldName);
                                Map::add($mapTo, $updatedKey);
                            }
                        }
                        Settings::add('data_sync', true);
                    }
                }

                if ($type == 'save_settings'){
                    // 2 instantiate app will send client id and secret
                    if (!empty($_POST)) {
                        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
                        if (array_key_exists('client_id', $_POST)) {

                            Helper::updateOption('connected', null);
                            Settings::clear();
                            // extract client id and client secret from post request

                            $credentials = (object)$_POST;
                            if (!empty($credentials)){
                                $clientId = $credentials->client_id;
                                $clientSecret = $credentials->client_secret;
                                // save for subsequent request
                                \core\Settings::add('client_secret', $clientSecret );
                                \core\Settings::add('client_id', $clientId);

                                $authorizeUrl = self::$CampaignMonitor->authorize_url($clientId,Helper::getRedirectUrl() , Helper::getCampaignMonitorPermissions() );
                                // redirect to get an access token
                                wp_redirect($authorizeUrl);
                                die();
                            } else {
                                Log::write("There was a problem with your credentials");
                            }

                        }
                    }
                }

                break;
            case 1:
//                echo 'Nonce is less than 12 hours old';
                break;

            case 2:
//                echo 'Nonce is between 12 and 24 hours old';
                break;
            default:
                die('You killed the app!');

        }

        $actionUrl = Helper::getActionUrl();
        wp_redirect($actionUrl);
        exit();
    }

    public static function add_admin_body_class($classes)
    {
        return "$classes campaign-monitor-woocommerce";
    }


    /**
     * Saves the version of the plugin to the database and displays an activation notice on where users
     * can access the new options.
     */
    public static function plugin_activation()
    {
        // show a notice advertising the form plugin

    }

    public static function custom_menu_page_removing()
    {
        // remove_menu_page( 'campaign_monitor_woocommerce' );
    }

    public static function create_menu()
    {

        //create new top-level menu
        $pageTitle = "Campaign Monitor for WooCommerce";
        $menuTitle = "Subscribe at checkout";
        $capability = 'administrator';
        $menuSlug = 'campaign_monitor_woocommerce';
        $callable = 'register_settings_page';
        $iconUrl = plugins_url('/campaign-monitor-for-woocommerce/views/admin/images/icon.svg');
        $position = 100;

        add_menu_page($pageTitle, $menuTitle, $capability, $menuSlug, array(__CLASS__, $callable), $iconUrl, $position);
        add_submenu_page(null,'Settings' , 'Settings' , $capability, 'campaign_monitor_woocommerce_settings', array(__CLASS__, 'setting_page') );

    }

    public static function load_custom_wp_admin_scripts($hook_suffix)
    {

        if (strpos($hook_suffix, 'campaign_monitor_woocommerce') !== false){

            $plugins_url = plugins_url('campaign-monitor-for-woocommerce');

            if (is_admin()){
                wp_register_style('custom_wp_admin_css', $plugins_url . '/views/admin/css/main.css', false, '1.0.0');
                wp_enqueue_style('custom_wp_admin_css' );
                wp_enqueue_script('app-script', $plugins_url . '/views/admin/js/app.js', array('jquery'));
                wp_enqueue_script('ajax-script', $plugins_url . '/views/admin/js/ajax.js', array('jquery'));
            }
            // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
            wp_localize_script('ajax-script', 'ajax_request', array(
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }

    }
    public static function load_custom_wp_scripts()
    {

        $plugins_url = plugins_url('campaign-monitor-for-woocommerce');

        wp_enqueue_script('ajax-script-public', $plugins_url . '/views/public/js/app.js', array('jquery'));
        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script('ajax-script-public', 'ajax_request', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));

    }

    public static function admin_sub_page()
    {
        Helper::renderer('settings');
    }

    public static function register_settings_settings()
    {

    }

    public static function register_settings_page()
    {
        Helper::renderer('connect');
    }
    public static function setting_page()
    {
        Helper::renderer('settings');
    }

    public static function auto_deactivate()
    {
        if (Helper::getOPtion('campaign_monitor_woocommerce')) {
            Helper::deleteOption('campaign_monitor_woocommerce');
        }
        Settings::add('notices', array());
        deactivate_plugins(self::$pluginPath);
    }


    public static function plugin_notices()
    {
        $html = "";
        if (true == delete_option('campaign_monitor_woocommerce_auto_deactivate')) {
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
            $html = '<div id="message" class="error notice is-dismissible">';
            $html .= '<p>';
            $html .= __(' Campaign Monitor for WooCommerce requires <a href="https://www.woothemes.com/woocommerce/">Woocommerce</a> to be installed and activated.', 'campaign-monitor-woocommerce');
            $html .= '</p>';
            $html .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
            $html .= '</div><!-- /.updated -->';

        } else {

            if ("1.0" != Helper::getOption('campaign_monitor_woocommerce')) {
                Helper::updateOption('campaign_monitor_woocommerce', "1.0");
                Settings::add('notices', array());

                $html = '<div id="message" class="updated notice is-dismissible">';
                $html .= '<p>';
                $html .= __('Campaign Monitor <a href="admin.php?page=campaign_monitor_woocommerce">Connect Account</a>.', 'campaign-monitor-woocommerce');
                $html .= '</p>';
                $html .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';

                $html .= '</div><!-- /.updated -->';
            }

        }



        echo $html;

    }

    /**
     * Deletes the option from the database. Optionally displays an error message if there is a
     * problem deleting the option.
     */
    public static function plugin_deactivation()
    {
        // remove cron jobs
        if (null != self::$Cron) {
            self::$Cron->unschedule();
        }

        // Display an error message if the option isn't properly deleted.
        if (false == Helper::deleteOption('campaign_monitor_woocommerce')) {

            $html = '<div class="error">';
            $html .= '<p>';
            $html .= __('There was a problem deactivating the Campaign Monitor for Woocommerce plugin. Please try again.', 'campaign-monitor-woocommerce');
            $html .= '</p>';
            $html .= '</div><!-- /.updated -->';

            echo $html;

        }

    }
}