<?php
require_once CAMPAIGN_MONITOR_WOOCOMMERCE_DIR . '/class/csrest_general.php';

$prefix = 'campaign_monitor_woocommerce_';

\core\Settings::add('client_secret', 'y6iX6c6P1664tNnG7W66iITD46X6eZ61d6766aD6q69qn6yGk69Jx6666h6n6YTJx6rO6n6IN50ihg2U' );
\core\Settings::add('client_id', 104245 );
$appSettings  = \core\Settings::get();

//\core\Helper::display($_GET);
/*
\core\Fields::add('orders_count', 'Total Order Count', 'Number', 'description for this item','none', true);
\core\Fields::add('total_spent', 'Total Spent', 'Number', 'description for this item','none', true);
\core\Fields::add('verified_email', 'Verified Email?', 'Text', 'description for this item','none', true);*/
////
//$fields = \core\Fields::get();
//\core\Helper::display($fields);

//// current user
//$user = wp_get_current_user();
//
////\core\Helper::display($user);
//
//$metaName = \core\Helper::getPrefix() . '_account_settings';

if (isset($_GET['disconnect'])){
    \core\Settings::add('default_list', null);
}

// check if user loggin on campaigm monitor
if (isset($_GET['code']) && !empty($_GET['code'])){
    $code = $_GET['code'];
    $redirectUrl = 'http://104.130.155.207/wordpress/wp-admin/admin.php?page=campaign_monitor_woocommerce_settings&connected=true';

   \core\Helper::updateOption('code', $code);

    $params = array('grant_type' => urlencode('authorization_code'),
        'client_id' => urlencode($appSettings['client_id']),
        'client_secret' => urlencode($appSettings['client_secret']),
        'code' => $code,
        'redirect_uri' =>  ($redirectUrl) );

    $postUrl = \core\Connect::getTransport('oauth/token', $params);
    $endpoint = 'https://api.createsend.com/oauth/token';
    $results =  \core\Connect::request($params,$endpoint);

    // Let's authenticate the user
    if (!empty($results)){
        $credentials = json_decode($results);

        \core\Settings::add('access_token', $credentials->access_token);
        \core\Settings::add('refresh_token', $credentials->refresh_token);
        \core\Settings::add('expiry', $credentials->expires_in);
        $appSettings = \core\Settings::get();
    }
}

$defaultList = \core\Settings::get('default_list');
$code = \core\Helper::getOption('code');
$clients = array();
if (!empty($appSettings) && array_key_exists('access_token',$appSettings) && empty($defaultList)){
    $appSettings = (object)$appSettings;
    $clients = \core\App::$CampaignMonitor->get_clients();
}
$actionUrl = get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce_settings';
?>

<div>
    <div id="selector" class="box main-container text-left">
        <?php if ($clients) : ?>
                <div id="clientList">
                    Choose the client you want to connect to <?php echo bloginfo('name') ?>.
                    <ul class="list">
                        <?php foreach ($clients as $client) : ?>
                            <?php $viewClientListUrl = http_build_query((array)$client); ?>
                            <li>
                                <a class="ajax-call" href="<?php echo $actionUrl . '&' . $viewClientListUrl; ?>&action=view_client_list"><?php echo $client->Name; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="content">
                    <div id="variable">
                    </div>
                    <a id="btnToClientLists" class="button">Back to Client Lists</a>
                </div>
         <?php endif; ?>
        <?php if (!empty($defaultList)) : ?>
            <img style="width:200px" src="https://live.dev.apps-market.cm/shopifyApp/images/circleCheck.png">
            <h1>Success! Your list is now syncing.</h1>
            <p>It might take a while to sync your data from Shopify to Campaign Monitor. We'll email you the moment the data sync is complete.</p>
            <a class=" button primary button-primary button-large" href="http://104.130.155.207/wordpress/wp-admin/admin.php?page=campaign_monitor_woocommerce_settings&disconnect=true">
                Switch List
            </a>
        <?php endif; ?>
    </div>
</div>
