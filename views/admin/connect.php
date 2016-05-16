<?php
$pluginUrl = plugins_url('campaignmonitorwoocommerce');
$logoSrc = $pluginUrl . '/images/campaign-monitor.png';

$params = array('type' => 'web_server', 'client_id' => '104245',
    'redirect_uri' => 'http://104.130.155.207/wordpress/wp-admin/admin.php?page=campaign_monitor_woocommerce_settings&connected=true',
    'scope' => 'CreateCampaigns,SendCampaigns,ViewReports',
    'state' => '');
$postUrl = \core\Connect::getTransport('oauth', $params);
?>
<div class="wrap">
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