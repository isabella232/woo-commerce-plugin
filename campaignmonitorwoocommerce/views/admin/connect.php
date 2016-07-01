<?php

require_once CAMPAIGN_MONITOR_WOOCOMMERCE_DIR . '/class/csrest_general.php';
// check for authorization code and is not expired

// get all settings for this app
$appSettings  = \core\Settings::get();
$redirectUrl = \core\Helper::getRedirectUrl();
$pluginUrl = plugins_url('campaignmonitorwoocommerce');
$logoSrc = $pluginUrl . '/views/admin/images/campaign-monitor.png';
$prefix = 'campaign_monitor_woocommerce_';


$notices = \core\Settings::get('notices');

// do I have an authorization token
$autorizationToken = \core\Settings::get('access_token');
if (!empty($autorizationToken)){
    // we are authorize
    // check if refresh token is still good
    if (\core\Settings::get('refresh_token') - time() <  (60*60*24))
    {
        $auth = array(
            'access_token' => \core\Settings::get('access_token'),
            'refresh_token' => \core\Settings::get('refresh_token')
        );
        list($new_access_token, $new_expires_in, $new_refresh_token) = \core\App::$CampaignMonitor->refresh_token($auth);

        \core\Settings::add('access_token',$new_access_token);
        \core\Settings::add('refresh_token',$new_refresh_token);
        \core\Settings::add('expiry',$new_expires_in);
    }

} else {

    if (isset($_GET['error']) && !empty($_GET['error'])){
        // there was something wrong
        $html = '<div class="wrap">';
        $html .= '<h1>Campaign Monitor</h1>';
        $html .= '<div  id="error" class="error">';
        $html .= $_GET['error_description'];
        $html .= '</div><!-- end error-->';
        $html .= '</div><!-- end wrap-->';

        echo $html;
        exit;

    }else {

        // check if user logging on campaign monitor
        if (isset($_GET['code']) && !empty($_GET['code'])){
            $code = $_GET['code'];

            \core\Helper::updateOption('code', $code);

            $params = array('grant_type' => urlencode('authorization_code'),
                'client_id' => urlencode($appSettings['client_id']),
                'client_secret' => urlencode($appSettings['client_secret']),
                'code' => $code,
                'redirect_uri' =>  $redirectUrl);

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

            // we are connected
            \core\Helper::updateOption('connected', TRUE );
            unset($_GET['code']);
        }
    }
}

if (isset($_GET['disconnect'])){
    \core\Settings::add('default_list', null);
}



$defaultList = \core\Settings::get('default_list');
$defaultClient = \core\Settings::get('default_client');
$accessToken = \core\Settings::get('access_token');
$code = \core\Helper::getOption('code');
$clients = array();
if (!empty($appSettings) && !empty($accessToken)){
    $appSettings = (object)$appSettings;
    $auth = array('access_token' => $appSettings->access_token,
        'refresh_token' => $appSettings->refresh_token);
    $clients = \core\App::$CampaignMonitor->get_clients($auth);

    if (count($clients) == 1){
        $CID = $clients[0]->ClientID;
        \core\Settings::add('default_client', $CID);
    }

}


$clientListSettings = \core\ClientList::get($defaultList);

$actionUrl = get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce';

if (!empty($defaultList)) {
    $getOnlyVisibleFields = true;
    $mappedFields = \core\Map::get($getOnlyVisibleFields);
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
$selectedClient = \core\Settings::get('default_client');
$selectedList = \core\Settings::get('default_list');
$canViewLog = \core\Helper::getOption('debug');
$subscription = \core\Helper::getOption('automatic_subscription');
$subscribeText = \core\Helper::getOption('subscribe_text');
$subscriptionBox = \core\Helper::getOption('toggle_subscription_box');

?>

<?php if (!empty($selectedClient) ) : ?>
    <script>
        jQuery(document).ready(function($) {
            $('#clientSelection').trigger('change');
        });
    </script>
<?php endif; ?>
<?php if (!empty($selectedList) ) : ?>
    <script>
        jQuery(document).ready(function($) {
//            $('#lists option[data-id="<?php //echo $selectedList ?>//"]').attr('selected', 'selected');
        });
    </script>
    <input type="hidden" name="selected_list" value="<?php echo $selectedList; ?>"/>
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
                        <table id="fieldMapperTable">
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
                                <th></th>
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
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php
                        $attributes =  array(
                            'id'       => 'newFieldSelect',
                            'name'     => "",
                            'class'    => 'dropdown-select mapped-fields',
                        );
                        echo \core\Fields::get_select(\core\FieldType::ALL, false, $attributes);

                        ?>
                        <button id="btnCreateCustomField" type="button" class="button regular-text ltr">
                            Add Custom Field
                        </button>
                        <button id="btnSaveMapping" type="submit" class="button button-primary regular-text ltr">
                            Save Mapping
                        </button>

                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>



            <?php if (!in_array('connected_list_notice',$notices, TRUE ) && !empty($currentList)) : ?>
            <div data-method="connected_list_notice" class="updated notice cm-plugin-ad is-dismissible">
                <p>Your WooCommerce customer data can be accessed in the list, <strong><?php echo $currentList->Title; ?></strong>, in
                    <a href="https://www.campaignmonitor.com/" target="_blank">
                        Campaign Monitor</a>.&nbsp;
                     We've also created 6 segments for you there.
                </p>
            </div>
        <?php endif; ?>


    <?php if (!in_array('show_ad',$notices, TRUE )) : ?>
        <div id="cmPlugin" data-method="show_ad" class="updated notice cm-plugin-ad is-dismissible">
            <p>Check out the
                <a href="https://wordpress.org/plugins/ajax-campaign-monitor-forms/">Campaign Monitor for Wordpress plugin</a> -- add beautiful forms to your website to capture subscriber data.
            </p>
        </div>
    <?php endif; ?>



    <?php if (!\core\App::is_connected()) : ?>
        <p>Campaign Monitor lets you manage your subscriber lists and email campaigns.<a href="https://www.campaignmonitor.com/signup/?utm_campaign=signup&utm_source=shopifyintegration&utm_medium=referral">Send something beautiful today</a></p>
        <a id="btnConnect" class="static button  button-primary" target="_blank" href="<?php echo \core\App::getConnectUrl(); ?>">Connect</a>
        <?php else : ?>

        <div>
            <?php if (!empty($clients)) : ?>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">

                        <div id="post-body-content">
                            <table ></table>
                            <table class="list form-table">
                                <tr valign="top" <?php echo (count($clients) > 1) ? "" : 'style="display:none;"'; ?> >
                                    <th scope="row">
                                        Client
                                    </th>
                                    <td>
                                        <select id="clientSelection"  class="ajax-call dropdown-select">
                                            <option data-url="">
                                                Please select client
                                            </option>
                                            <option disabled>
                                                ---
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
                                            <option value="2" selected>Confirmed opt-in (confirmation required)</option>
                                            <option value="1" >Single opt-in (no confirmation required)</option>
                                        </select>
                                        <p><em>
                                                <strong>Single opt-in</strong> means new subscribers are added to this list as soon as
                                                they complete the subscribe form.
                                            </em>
                                        </p>
                                        <p><em>
                                                <strong>Confirmed opt-in</strong> means a confirmation email will be sent with a link they must click to validate their address. This confirmation isn't required when you
                                                import existing subscribers, only when new subscribers join via your subscribe form.</em></p>
                                        <p><em><a href="http://help.campaignmonitor.com/topic.aspx?t=16">Learn more about confirmed opt-in lists.</a></em> </p>


                                        <form action="<?php echo get_site_url(); ?>/wp-admin/admin-post.php" method="post">
                                            <input type="hidden" name="action" value="handle_request">
                                            <input type="hidden" name="data[type]" value="create_list">
                                            <input type="hidden" name="data[app_nonce]" value="<?php echo wp_create_nonce( 'app_nonce' ); ?>">
                                            <input type="hidden" id="listNameData" name="data[list_name]" value="">
                                            <input type="hidden" id="clientIdData" name="data[client_id]" value="">
                                            <input type="hidden" id="optInData" name="data[opt_in]" value="">
                                            <!--                                            <button id="btnCreateList" type="submit" class="regular-text ltr" placeholder="List Name">-->
                                            <!--                                                Create List-->
                                            <!--                                            </button>-->
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        Subscription Box
                                    </th>
                                    <td>

                                        <label for="subscriptionBox">
                                            <input id="subscriptionBox" name="toggle_subscription_box"  <?php echo (isset($clientListSettings['toggle_subscription_box']) && $clientListSettings['toggle_subscription_box']) ? 'checked="checked"': ''; ?>  type="checkbox">  Show subscription option at checkout</label>

                                    </td>
                                </tr>
                                <tr id="subscriptionLegend" class="<?php echo (isset($clientListSettings['toggle_subscription_box']) && $clientListSettings['toggle_subscription_box']) ? '': 'hidden'; ?> ">
                                    <th scope="row">
                                        Subscription Box Text
                                    </th>
                                    <td>

                                        <input type="text" id="subscriptionText" name="subscription_text" class="regular-text ltr" value="<?php echo (isset($clientListSettings['subscribe_text'])) ? $clientListSettings['subscribe_text'] : ''; ?>" placeholder="Subscribe to our newsletter"/>
                                        <label for="autoNewsletter">
                                            <input id="autoNewsletter" name="auto_newsletter"  <?php echo (isset($clientListSettings['automatic_subscription']) && $clientListSettings['automatic_subscription']) ? 'checked="checked"': ''; ?>  type="checkbox"> Automatically subscribe customers to your newsletter</label>

                                    </td>
                                    <td>
                                        <div id="postbox-container-1" class="postbox-container ">
                                            <div id="side-sortables" class="preview-box meta-box-sortables ui-sortable" style=""><div id="submitdiv" class="postbox ">
                                                    <button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">Preview
                                </span>
                                                        <!--                                        <span class="toggle-indicator" aria-hidden="true"></span>-->
                                                    </button><h2 class="hndle ui-sortable-handle">
                                                        <span>Preview</span></h2>
                                                    <div class="inside">

                                                        <img src="<?php echo $srcUrl; ?>preview.png"/>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        Debug Log
                                    </th>
                                    <td>
                                        <label>
                                            <label for="logToggle">
                                                <input id="logToggle" name="log_toggle" <?php echo  (isset($clientListSettings['debug']) && $clientListSettings['debug']) ? 'checked="checked"': ''; ?> type="checkbox">
                                                Enable Logging </label>
                                            <?php if  ($canViewLog)   : ?>
                                                <div class="log-output modal">
                                                    <div class="content">
                                                        <span class="btn-close dashicons dashicons-no"></span>
                                                        <div class="debug-log">
                                                            <div class="output">
                                                                <?php
                                                                echo \core\Log::getContent();
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <span class="separator">|</span>
                                                <a id="btnViewLog" href="">
                                                    View Log
                                                </a>
                                            <?php endif; ?>

                                            <p><em>Only enable logging to help get better support from Campaign Monitor</em></p>

                                    </td>
                                </tr>
                            </table>
                            <button type="button" class=" button primary button-primary save-settings" href="">
                                Save Changes
                            </button>

                        </div><!-- /post-body-content -->


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
                                            Please create a client on
                                            <a href="https://www.campaignmonitor.com/" target="_blank">
                                                Campaign Monitor
                                            </a> before continuing.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr style="display: none;">
                                <td>
                                    <input type="hidden" name="action" value="handle_request">
                                    <input type="hidden" name="data[app_nonce]" value="<?php echo wp_create_nonce( 'app_nonce' ); ?>">
                                    <input type="hidden" name="data[type]" value="create_client">
                                    <input type="text" name="data[client_name]" placeholder="New Client Name">
                                </td>
                            </tr>
                            <tr style="display: none;">
                                <td>
                                    <input type="submit" class="button primary button-primary button-large" value="Create Client">
                                </td>
                            </tr>
                        </table>
                    </form>

                <?php endif; ?>
            <?php endif; ?>

            <?php if (true == false) : ?>

                <div class="box main-container text-center">
                    <img class="connected-icon" src="https://live.dev.apps-market.cm/shopifyApp/images/circleCheck.png">
                    <h1>You're Connected</h1>
                    <p>Your Woocommerce customer data can be accessed in the list, <strong><?php echo $currentList->Title; ?></strong>, in
                        <a href="https://www.campaignmonitor.com/" target="_blank">
                            Campaign Monitor
                        </a>
                    </p>
                    <div>
                        <ul class="action-buttons">

                            <li>
                                <button type="button" class="post-ajax button"  id="btnRecreateSegments" data-url="<?php  echo $actionUrl . '&ClientID=' . $defaultClient . '&ListID=' . $defaultList . '&action=set_client_list'; ?>" name="recreate_segments">Recreate Segments</button>

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
