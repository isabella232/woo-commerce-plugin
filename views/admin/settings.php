<?php
require_once CAMPAIGN_MONITOR_WOOCOMMERCE_DIR . '/class/csrest_general.php';

$prefix = 'campaign_monitor_woocommerce_';
$clientId = \core\Helper::getOption('client_id');
$clientSecret = \core\Helper::getOption('client_secret');
$accessToken = \core\Helper::getOption('access_token');
$refreshToken = \core\Helper::getOption('refresh_token');
$expiry = \core\Helper::getOption('expiry');

\core\Helper::display($_GET);

// check if user loggin on campaigm monitor
if (isset($_GET['code']) && !empty($_GET['code'])){
    $code = $_GET['code'];
    $redirectUrl = 'http://104.130.155.207/wordpress/wp-admin/admin.php?page=campaign_monitor_woocommerce_settings&connected=true';

   \core\Helper::updateOption('code', $code);

    $params = array('grant_type' => urlencode('authorization_code'),
        'client_id' => urlencode($clientId),
        'client_secret' => urlencode($clientSecret),
        'code' => $code,
        'redirect_uri' =>  ($redirectUrl) );

    $postUrl = \core\Connect::getTransport('oauth/token', $params);
    $endpoint = 'https://api.createsend.com/oauth/token';
    $results =  \core\Connect::request($params,$endpoint);

    // Let's authenticate the user
    if (!empty($results)){
        $credentials = json_decode($results);
        \core\Helper::display($results);

        $accessToken = $credentials->access_token;
        $refreshToken = $credentials->refresh_token;
        $expiry = $credentials->expires_in;

        \core\Helper::updateOption('access_token', $accessToken );
        \core\Helper::updateOption('refresh_token', $refreshToken);
        \core\Helper::updateOption('expiry', $expiry);
    }

}
$code = \core\Helper::getOption('code');

if (!empty($accessToken) && !empty($refreshToken)){
    $auth = array(
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken);
    $wrap = new CS_REST_General($auth);

    $clientsCall = $wrap->get_clients();


    if($clientsCall->was_successful()) {
        $clients = $clientsCall->response;
    } else {
        echo 'Failed with code '.$result->http_status_code."<br /><pre>";
        var_dump($result->response);
    }
}
$actionUrl = get_admin_url() . 'admin.php?page=campaign_monitor_woocommerce_settings';
?>

<div>
    <h2>Campaign Monitor for Woocommerce Settings</h2>

    <div class="box main-container text-left">
        <?php if ($clients) : ?>
            Choose the client you want to connect to this Shopify Store.
            <ul class="list">
                <?php foreach ($clients as $client) : ?>
                    <?php $viewClientListUrl = http_build_query((array)$client); ?>
                    <li>
                        <a href="<?php echo $actionUrl . '&' . $viewClientListUrl; ?>&action=view_client_list"><?php echo $client->Name; ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <form method="post" action="options.php">
       <?php settings_fields( 'settings_page_group' ); ?>
        <?php do_settings_sections( 'settings_page_group' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Client Id</th>
                <td><input type="text" class="regular-text" name="<?php echo $prefix . 'client_id'; ?>" value="<?php echo esc_attr( $clientId ); ?>" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Client Secret</th>
                <td><input type="text" class="regular-text" name="<?php echo $prefix . 'client_secret' ?>" value="<?php echo esc_attr( $clientSecret ) ?>" /></td>
            </tr>

            <?php if (!empty($code)) : ?>
                <tr valign="top">
                    <th scope="row">Code</th>
                    <td><input type="text" class="regular-text" name="<?php echo $prefix . 'code' ?>" value="<?php echo esc_attr( \core\Helper::getOption('code') ) ?>" /></td>
                </tr>
            <?php endif; ?>

            <?php if (!empty($accessToken)) : ?>
                <tr valign="top">
                    <th scope="row">Access Token</th>
                    <td><input type="text" class="regular-text" name="<?php echo $prefix . 'access_token' ?>" value="<?php echo esc_attr( $accessToken ) ?>" /></td>
                </tr>
            <?php endif; ?>

            <?php if (!empty($refreshToken)) : ?>
                <tr valign="top">
                    <th scope="row">Refresh Token</th>
                    <td><input type="text" class="regular-text" name="<?php echo $prefix . 'refresh_token' ?>" value="<?php echo esc_attr( $refreshToken ) ?>" /></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($expiry)) : ?>
                <tr valign="top">
                    <th scope="row">Expiry</th>
                    <td><input type="text" class="regular-text" name="<?php echo $prefix . 'expiry' ?>" value="<?php echo esc_attr( $expiry ) ?>" /></td>
                </tr>
            <?php endif; ?>



        </table>
         <?php submit_button(); ?>

    </form></div>
</div>