<?php

require_once CAMPAIGN_MONITOR_WOOCOMMERCE_DIR . '/class/csrest_general.php';

$pluginUrl = plugins_url('campaignmonitorwoocommerce');
$logoSrc = $pluginUrl . '/views/admin/images/campaign-monitor.png';
define("CAMP_MON_API_PERMISSION_SCOPE",
  "ViewReports".",".
   "ViewSubscribersInReports".",".
   "ManageLists".",".
    "ImportSubscribers".",".
    "AdministerAccount");


$params = array('type' => 'web_server', 'client_id' => '104245',
    'redirect_uri' => 'http://104.130.155.207/wordpress/wp-admin/admin.php?page=campaign_monitor_woocommerce&connected=true',
    'scope' => CAMP_MON_API_PERMISSION_SCOPE,
    'state' => '');
$postUrl = \core\Connect::getTransport('oauth', $params);
$prefix = 'campaign_monitor_woocommerce_';

\core\Settings::add('client_secret', 'y6iX6c6P1664tNnG7W66iITD46X6eZ61d6766aD6q69qn6yGk69Jx6666h6n6YTJx6rO6n6IN50ihg2U' );
\core\Settings::add('client_id', 104245 );
$appSettings  = \core\Settings::get();


$isFieldDefault = true;
\core\Fields::add('orders_count', 'Total Order Count', 'Number', 'description for this item', $isFieldDefault);
\core\Fields::add('total_spent', 'Total Spent', 'Number', 'description for this item', $isFieldDefault);
\core\Fields::add('verified_email', 'User Email', 'Text', 'description for this item', $isFieldDefault);
\core\Fields::add('created_at', 'User Registered', 'Date', 'description for this item', $isFieldDefault);
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


if (isset($_GET['disconnect'])){
    \core\Settings::add('default_list', null);
}


// check if user logging on campaign monitor
if (isset($_GET['code']) && !empty($_GET['code'])){
    $code = $_GET['code'];
    $redirectUrl = 'http://104.130.155.207/wordpress/wp-admin/admin.php?page=campaign_monitor_woocommerce&connected=true';

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
$accessToken = \core\Settings::get('access_token');
$code = \core\Helper::getOption('code');
$clients = array();
if (!empty($appSettings) && !empty($accessToken) && empty($defaultList)){
    $appSettings = (object)$appSettings;
    $auth = array('access_token' => $appSettings->access_token,
                  'refresh_token' => $appSettings->refresh_token);
    $clients = \core\App::$CampaignMonitor->get_clients($auth);

}

$actionUrl = get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce';


//$crons = get_option('cron');
//\core\Helper::display($crons);

//\core\Helper::display(\core\Map::get());

//\core\Helper::display(\core\Settings::get('data_sync'));

/*$data = array("importData" => "testing");
$complex_message = array(
    "From" => 'info@campaignmonitor.com"',
    "To" => 'leandro@villagranstudio.com',
    "Data" => $data
);

$result = \core\App::$CampaignMonitor->send_email($complex_message);
\core\Helper::display($result);*/

if (!empty($defaultList)) {
    $mappedFields = \core\Map::get();
    $fields = \core\Fields::get();
    $campaignMonitorFields = \core\App::$CampaignMonitor->get_custom_fields($defaultList);
    if (!empty($campaignMonitorFields)){
        // put the map fields first
        $campaignMonitorFields = (object)array_reverse((array)$campaignMonitorFields) ;
    }

    $counter = 1;

    $currentList = \core\App::$CampaignMonitor->get_list_details($defaultList);
    if (empty($currentList)) {
        \core\Settings::add('default_list', null);
        echo "<script>location.reload();</script>";

    }
}
$srcUrl = get_site_url(). '/wp-content/plugins/campaignmonitorwoocommerce/views/admin/images/';
$selectedClient = \core\Helper::getOption('selectedClient');
$selectedList = \core\Helper::getOption('selectedList');
$canViewLog = \core\Helper::getOption('debug');
$subscription = \core\Helper::getOption('automatic_subscription');

?>
<?php if (!empty($selectedClient) ) : ?>
    <script>
        jQuery(document).ready(function($) {
            $('#clientSelection').trigger('change');
        });
    </script>
<?php endif; ?>
<div class="wrap">
    <h1>Campaign Monitor</h1>
    <div  id="fieldMappper" class="modal">
        <div class="content">
            <span class="btn-close dashicons dashicons-no"></span>
        <div class="box main-container text-center">
        <?php if (!empty($defaultList)) : ?>
            <form action="<?php echo get_site_url(); ?>/wp-admin/admin-post.php" method="post">

                <input type="hidden" name="action" value="handle_request">
                <input type="hidden" name="data[type]" value="map_custom_fields">
                <input type="hidden" name="data[app_nonce]" value="<?php echo wp_create_nonce( 'app_nonce' ); ?>">


            <div class="text-left">
                <p>&nbsp;&nbsp;You are currently mapping custom fields for <strong>
                        <?php echo (isset($currentList->Title)) ? $currentList->Title : ''; ?>
                    </strong>.
                </p>
            </div>
            <table>
                <thead>
                <tr>
                    <th colspan="4">

                    </th>
                </tr>
                <tr>
                    <th>#</th>
                    <th>
                        Campaign Monitor Custom Fields
                    </th>
                    <th>Woocommerce Fields</th>
                    <th>Field Type</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($campaignMonitorFields as $field):
                    $fieldName = str_replace(array('[', ']'),'' , $field->Key );
                    $fieldName = htmlentities($fieldName);
                    ?>
                    <tr>
                        <td>
                            <?php echo $counter++; ?>
                        </td>
                        <td>
                            <input type="text" class="regular-text ltr" name="data[fields][<?php echo $fieldName; ?>][name]" value="<?php echo $field->FieldName; ?>">
                        </td>
                        <td>
                            <?php
                            $matches = array_keys($mappedFields, $field->Key);
                            $isSelected = false;

                             if (!empty($matches)){
                                 foreach ($matches as $match){
                                     if  ($mappedFields[$match] == $field->Key){
                                         $isSelected = $match;
                                     }
                                 }
                             }
                                  $attributes =  array(
                                        'id'       => $field->Key,
                                        'name'     => "data[fields][{$fieldName}][map_to]",
                                        'class'    => 'dropdown-select mapped-fields',
                                    );
                            ?>
                            <?php switch ($field->DataType) {
                                case 'Number' :
                                    echo \core\Fields::get_select(\core\FieldType::NUMBER, $isSelected, $attributes );
                                    break;
                                case 'Text' :
                                    echo \core\Fields::get_select(\core\FieldType::TEXT, $isSelected, $attributes);
                                    break;
                                case 'Date' :
                                    echo \core\Fields::get_select(\core\FieldType::DATE, $isSelected, $attributes);
                                    break;
                            }
                            ?>
                        </td>
                        <td>
                            <?php switch ($field->DataType) {
                                case 'Number' :
                                    echo '<span class="dashicons dashicons-editor-ol"></span>';
                                    break;
                                case 'Text' :
                                    echo '<span class="dashicons dashicons-editor-textcolor"></span>';
                                    break;
                                case 'Date' :
                                    echo '<span class="dashicons dashicons-calendar-alt"></span>';
                                    break;
                            }
                            ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
                <button id="btnSaveMapping" type="submit" class="button button-primary regular-text ltr">
                    Save Mapping
                </button>
        </form>
        <?php endif; ?>
            </div>
    </div>
        </div>
    <div id="message" class="updated notice ">
       <p>Check out the  <a href="https://wordpress.org/plugins/ajax-campaign-monitor-forms/">Campaign Monitor for Wordpress plugin</a> so you can add beautiful forms to your website to capture ubscriber data.
        </p>
    </div>
    <?php if (empty($accessToken)) : ?>
    <div class="box main-container text-center">
        <div class="logo-container">
            <img class="logo" src="<?php echo $logoSrc; ?>" alt="Campaign Monitor Logo"/>
        </div>
        <h2>Get started with Campaign Monitor for Woocommerce </h2>
        <p>
            <a class="static button  button-primary button-large" href="<?php echo $postUrl; ?>"
               target="_blank">Connect</a>
        </p>
        <p>Connect your Campaign Monitor account so you can transfer data from Shopify and send personalized emails.</p>
        <p>Don\'t have a Campaign Monitor account? <a href="https://www.campaignmonitor.com/signup/?utm_campaign=signup&utm_source=shopifyintegration&utm_medium=referral">Sign up for free today</a></p>
    </div>
    <?php else : ?>

    <div>

            <?php if (!empty($clients)) : ?>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        Choose the client you want to connect to <?php echo bloginfo('name') ?>.

                    </div>
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <table class="list form-table">
                                <tr valign="top">
                                    <th scope="row">
                                        Client List
                                    </th>
                                    <td>
                                        <select id="clientSelection"  class="ajax-call dropdown-select">
                                            <option data-url="">
                                                Please select list
                                            </option>
                                            <option data-url="<?php echo $actionUrl; ?>&action=create_client">
                                               Create New Client
                                            </option>
                                            <?php
                                            // [ClientID] => c4339e20ba838cd827e4b59b28a83d69
                                            // [Name] => Kerl
                                            ?>
                                            <?php foreach ($clients as $client) : ?>
                                                <?php $viewClientListUrl = http_build_query((array)$client); ?>
                                                <option <?php echo ($client->ClientID == $selectedClient) ? 'selected="selected"' : ''; ?> value="<?php echo $client->ClientID; ?>" data-url="<?php echo $actionUrl . '&' . $viewClientListUrl; ?>&action=view_client_list"><?php echo $client->Name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top" class="new-client-creation">
                                    <th scope="row">
                                        Client Name
                                    </th>
                                    <td>
                                        <input type="text" id="clientName" class="regular-text ltr" placeholder="Client Name"/>
                                    </td>
                                </tr>
                                <tr valign="top" class="new-client-creation">
                                    <th scope="row">

                                    </th>
                                    <td>
                                        <form action="<?php echo get_site_url(); ?>/wp-admin/admin-post.php" method="post">

                                                        <input type="hidden" name="action" value="handle_request">
                                                        <input type="hidden" name="data[type]" value="create_client">
                                                        <input type="hidden" name="data[app_nonce]" value="<?php echo wp_create_nonce( 'app_nonce' ); ?>">
                                                        <input type="hidden" id="clientNameData" name="data[client_name]" placeholder="New Client Name" value="">

                                            <button id="btnCreateNewClient" type="submit" class="regular-text ltr" placeholder="List Name">
                                                Create Client
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        Campaign Monitor List
                                    </th>
                                    <td>
                                        <div id="createList">
                                            <select class="dropdown-select">
                                                <option data-url="">
                                                    Please select a client to see its lists
                                                </option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top" class="new-list-creation">
                                    <th scope="row">
                                        List Name
                                    </th>
                                    <td>
                                        <input id="listName" type="text" class="regular-text ltr" placeholder="List Name"/>
                                    </td>
                                </tr>
                                <tr valign="top" class="new-list-creation">
                                    <th scope="row">
                                        List Type
                                    </th>
                                    <td>
                                        <select id="listType" name="type" class="dropdown-select">
                                            <option value="1" selected="">Single opt-in (no confirmation required)</option>
                                            <option value="2">Confirmed opt-in (confirmation required)</option>
                                        </select>
                                        <p><strong>Single opt-in</strong> means new subscribers are added to this list as soon as they complete the subscribe form. </p>
                                        <p><strong>Confirmed opt-in</strong> means a confirmation email will be sent with a link they must click to validate their address. This confirmation isn't required when you
                                            import existing subscribers, only when new subscribers join via your subscribe form.</p>
                                    </td>
                                </tr>
                                <tr valign="top" class="new-list-creation">
                                    <th scope="row">

                                    </th>
                                    <td>
                                        <form action="<?php echo get_site_url(); ?>/wp-admin/admin-post.php" method="post">
                                            <input type="hidden" name="action" value="handle_request">
                                            <input type="hidden" name="data[type]" value="create_list">
                                            <input type="hidden" name="data[app_nonce]" value="<?php echo wp_create_nonce( 'app_nonce' ); ?>">
                                            <input type="hidden" id="listNameData" name="data[list_name]" value="">
                                            <input type="hidden" id="clientIdData" name="data[client_id]" value="">
                                            <input type="hidden" id="optInData" name="data[opt_in]" value="">
                                            <button id="btnCreateList" type="submit" class="regular-text ltr" placeholder="List Name">
                                                Create List
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        Subscription Text
                                    </th>
                                    <td>

                                        <input type="text" class="regular-text ltr" placeholder="Subscriber to our newsletter"/>

                                        <p>This text will be shown beside a checkbox at checkout.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        Newsletter
                                    </th>
                                    <td>
                                        <label for="autoNewsletter">
                                        <input id="autoNewsletter" name="auto_newsletter"  <?php echo ($subscription) ? 'checked="checked"': ''; ?>  type="checkbox"> Automatically subscribe customers to your newsletter.</label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        Debug Log
                                    </th>
                                    <td>
                                        <label>
                                            <label for="logToggle">
                                            <input id="logToggle" name="log_toggle" <?php echo ($canViewLog) ? 'checked="checked"': ''; ?> type="checkbox"> Enable Logging <?php echo ($canViewLog) ? '| View Log' : ''; ?></label>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            <a class=" button primary button-primary button-large save-settings" href="">
                                Save Changes
                            </a>

                        </div><!-- /post-body-content -->

                        <div id="postbox-container-1" class="postbox-container">
                            <div id="side-sortables" class="meta-box-sortables ui-sortable" style=""><div id="submitdiv" class="postbox ">
                                    <button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">Preview
                                </span><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle ui-sortable-handle">
                                        <span>Preview</span></h2>
                                    <div class="inside">

                                        <img src="<?php echo $srcUrl; ?>preview.png"/>
                                    </div>
                                </div>


                            </div>
                        </div>

                    </div><!-- /post-body -->
                    <br class="clear">
                </div>
                <div class="content">
                    <div id="variable">
                    </div>
                </div>

            <?php else : ?>
                <?php if (empty($defaultList)) : ?>
            <form action="<?php echo get_site_url(); ?>/wp-admin/admin-post.php" method="post">
                    <table>
                        <tr>
                            <td>
                                <div class="notice notice-error">
                                    <p>
                                        It seems like you have no clients in your account.
                                        Please create a client before continuing.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="hidden" name="action" value="handle_request">
                                <input type="hidden" name="data[app_nonce]" value="<?php echo wp_create_nonce( 'app_nonce' ); ?>">
                                <input type="hidden" name="data[type]" value="create_client">
                                <input type="text" name="data[client_name]" placeholder="New Client Name">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="submit" class="button primary button-primary button-large" value="Create Client">
                            </td>
                        </tr>
                    </table>
            </form>

                    <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($defaultList)) : ?>

                <div class="box main-container text-center">
                    <img style="width:200px" src="https://live.dev.apps-market.cm/shopifyApp/images/circleCheck.png">
                    <h1>You're Connected</h1>
                    <p>Your Woocommerce customer data can be accessed in the list, <strong><?php echo $currentList->Title; ?></strong>, in
                        <a href="https://www.campaignmonitor.com/" target="_blank">
                            Campaign Monitor
                        </a>
                    </p>
                    <div>
                        <ul class="action-buttons">

                                <li>
                                    <button type="button" class="button"  id="btnRecreateSegments" name="recreate_segments">Recreate Segments</button>

                                </li>
                                <li>
                                    <button type="button" class="button"  id="btnMapCustomFields" name="map_custom_fields">Map Custom Fields</button>

                                </li>
                                <li>
                                    <a class=" button primary button-primary button-large switch-list" href="<?php echo get_admin_url(); ?>/admin.php?page=campaign_monitor_woocommerce&disconnect=true">
                                        Switch List
                                    </a>
                                </li>

                        </ul>
                    </div>
                </div>

            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>
<div class="progress-notice">
    <div class="sk-circle">
        <div class="sk-circle1 sk-child"></div>
        <div class="sk-circle2 sk-child"></div>
        <div class="sk-circle3 sk-child"></div>
        <div class="sk-circle4 sk-child"></div>
        <div class="sk-circle5 sk-child"></div>
        <div class="sk-circle6 sk-child"></div>
        <div class="sk-circle7 sk-child"></div>
        <div class="sk-circle8 sk-child"></div>
        <div class="sk-circle9 sk-child"></div>
        <div class="sk-circle10 sk-child"></div>
        <div class="sk-circle11 sk-child"></div>
        <div class="sk-circle12 sk-child"></div>
    </div>
</div>
