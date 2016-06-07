<?php

namespace core;

class App
{

    private static $instance = null;
    public static $pluginLocation = null;
    public static $optionPrefix = 'campaign_monitor_woocommerce';
    public static $session = null;
    public static $pluginPath = '';

    public static $CampaignMonitor = null;
    public static $Cron = null;

    public static function run()
    {

        // Get an instance of the
        if (null == self::$instance) {
            self::$instance = new self;
        } // end if

        return self::$instance;

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
            register_activation_hook(self::$pluginPath, array(__CLASS__, 'plugin_activation'));
            register_deactivation_hook(self::$pluginPath, array(__CLASS__, 'plugin_deactivation'));

            add_action('admin_notices', array(__CLASS__, 'plugin_notices'));
            add_action('admin_menu', array(__CLASS__, 'create_menu'));
            add_action( 'admin_enqueue_scripts', array(__CLASS__, 'load_custom_wp_admin_style') );
            add_filter( 'admin_body_class', array(__CLASS__, 'add_admin_body_class') );
            add_action( 'admin_menu', array(__CLASS__, 'custom_menu_page_removing') );
            add_action( 'admin_post_handle_request', array(__CLASS__, 'handle_request') );


            self::$Cron = new Cron();

            $accessToken = Settings::get('access_token');
            $refreshToken = Settings::get('refresh_token');
            self::$CampaignMonitor = new CampaignMonitor($accessToken, $refreshToken);
            self::$session = new Session();

            // handle ajax
            Ajax::run();

        }

    } // end constructor

    public static function cron(){

    }

    public static function get_custom_fields(){

    }

    public static function handle_request()
    {
        status_header(200);
//        die("Server received '{$_REQUEST['data']}' from your browser.");

//        wp_redirect( $_SERVER['HTTP_REFERER'] );
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

                    Helper::updateOption('selectedClient', $clientId);
                    Helper::updateOption('selectedList', $newList);
                }

                if ($type == 'map_custom_fields') {

                    Helper::display($_REQUEST);
                    if (array_key_exists('fields', $data)) {
                        $fields = $data['fields'];
                        $listId = Settings::get('default_list');

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


        wp_redirect($_SERVER['HTTP_REFERER']);
        exit();
    }

    public static function  add_admin_body_class( $classes ) {
        return "$classes campaign-monitor-woocommerce";
    }


    /**
     * Saves the version of the plugin to the database and displays an activation notice on where users
     * can access the new options.
     */
    public static function plugin_activation()
    {

    }

    public  static function custom_menu_page_removing() {
       // remove_menu_page( 'campaign_monitor_woocommerce' );
    }

    public static function create_menu()
    {

        //create new top-level menu
        $pageTitle = "Campaign Monitor for Woocommerce";
        $menuTitle = "Campaign Monitor<br> for  Woocommerce";
        $capability = 'administrator';
        $menuSlug = 'campaign_monitor_woocommerce';
        $callable = 'register_settings_page';
        $iconUrl = plugins_url('/campaignmonitorwoocommerce/views/admin/images/icon.svg');
        $position = 100;

        add_menu_page($pageTitle,$menuTitle,$capability,$menuSlug,array(__CLASS__,$callable),$iconUrl, $position);
       // add_submenu_page($menuSlug, 'Settings', 'Settings', $capability,'campaign_monitor_woocommerce_settings',array(__CLASS__,'admin_sub_page'));

        //call register settings function
        add_action('admin_init', array(__CLASS__, 'register_settings_settings'));

    }

    public static function load_custom_wp_admin_style() {

        $plugins_url = plugins_url('campaignmonitorwoocommerce');
        wp_register_style( 'custom_wp_admin_css', $plugins_url  . '/views/admin/css/main.css', false, '1.0.0' );
        wp_enqueue_style( 'custom_wp_admin_css' );



        wp_enqueue_script( 'app-script', $plugins_url . '/views/admin/js/app.js', array('jquery') );
        wp_enqueue_script( 'ajax-script', $plugins_url . '/views/admin/js/ajax.js', array('jquery') );
        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script( 'ajax-script', 'ajax_request', array(
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ));
        
    }

    public static function admin_sub_page(){
        Helper::renderer('settings');
    }

    public static function register_settings_settings(){
        //register our settings
        register_setting('settings_page_group', self::$optionPrefix . '_client_id');
        register_setting('settings_page_group', self::$optionPrefix . '_client_secret');
        register_setting('settings_page_group', self::$optionPrefix . '_access_token');
        register_setting('settings_page_group', self::$optionPrefix . '_refresh_token');
        register_setting('settings_page_group', self::$optionPrefix . '_expiry');
        register_setting('settings_page_group', self::$optionPrefix . '_code');
    }

    public static function register_settings_page()
    {
//        $pluginUrl = plugins_url('campaignmonitorwoocommerce');
//        $logoSrc = $pluginUrl . '/images/icon.svg';
//        ob_start();
//        settings_fields( 'settings_page_group' );
//        $settingsFields = ob_get_contents();
//        do_settings_sections( 'settings_page_group' );
//        $settingsSections = ob_get_contents();
//        ob_end_clean();


        $blogUrl = str_replace("http://", "", get_bloginfo('url'));
        $hostUrl = $_SERVER['HTTP_HOST'];

        $folder = str_replace($hostUrl, "", $blogUrl);
        $realSlug = str_replace($folder, "", $_SERVER['REQUEST_URI']);

        Helper::renderer('connect');
    }

    public static function  auto_deactivate()
    {
        if (get_option('campaign_monitor_woocommerce')){
            delete_option('campaign_monitor_woocommerce');
        }
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
            $html .= __(' Campaign Monitor for Woocommerce requires <a href="https://www.woothemes.com/woocommerce/">Woocommerce</a> to be installed and activated.', 'campaign-monitor-woocommerce');
            $html .= '</p>';
            $html .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
            $html .= '</div><!-- /.updated -->';

        } else {

            if ("1.0" != get_option('campaign_monitor_woocommerce')) {
                add_option('campaign_monitor_woocommerce', "1.0");

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
        if (null != self::$Cron){
            self::$Cron->unschedule();
        }

        // Display an error message if the option isn't properly deleted.
        if (false == delete_option('campaign_monitor_woocommerce')) {

            $html = '<div class="error">';
            $html .= '<p>';
            $html .= __('There was a problem deactivating the Campaign Monitor for Woocommerce plugin. Please try again.', 'campaign-monitor-woocommerce');
            $html .= '</p>';
            $html .= '</div><!-- /.updated -->';

            echo $html;

        }

    }
}