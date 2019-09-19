<?php
$clientSecret = \core\Settings::get('client_secret');
$clientId = \core\Settings::get('client_id');


$noSSL =\core\Helper::getOption('no_ssl');
if ($noSSL){

    \core\Helper::updateOption('no_ssl', false);
     $error = \core\Helper::getOption('post_errors');

    $html = '<div id="message" class="notice-error notice is-dismissible">';
    $html .= '<h2>';
    $html .= $error['title'];
    $html .= '</h2>';
    $html .= '<p>';
    $html .=  $error['description'];
    $html .= '</p>';
    $html .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';

    $html .= '</div><!-- /.updated -->';
    echo $html;

}
$notices = \core\Settings::get('notices');

?>

<div class="wrap">

        <div class="content">
            <div class="post-body-content">
                <h1>Campaign Monitor Settings</h1>

                <?php if (!in_array('show_ad',$notices )) : ?>
                    <div id="cmPlugin" data-method="show_ad" class="updated notice cm-plugin-ad is-dismissible">
                        <p>Campaign Monitor lets you manage your subscriber lists and email campaigns. <a href="https://www.campaignmonitor.com/signup?utm_source=woocommerce-plugin&utm_medium=referral">Send something beautiful today!</a></p>
                    </div>
                <?php endif; ?>
            <h2>Campaign Monitor Client ID and Client Secret</h2>
            <p>Please enter your client ID and client secret.</p>
            <p>To retrieve them:</p>
            <ol>
                <li>In your Campaign Monitor account, select <strong>Integrations</strong> tab in the top navigation.
                If you don't see it, you are using the multi-client edition of Campaign Monitor, and will need to select a client first. </li>
            <li>
                In the "OAuth Registrations" section, find WooCommerce, then select <strong>View</strong> next to the WooCommerce icon.
            </li>
                <li>
                    Copy paste the client ID and client secret into the fields below, then select <strong>Save Changes.</strong>
                </li>
            </ol>
                    <form action="<?php echo get_admin_url(); ?>admin-post.php" method="post">
                        <input type="hidden" name="action" value="handle_request">
                        <input type="hidden" name="data[type]" value="save_settings">
                        <input type="hidden" name="data[app_nonce]" value="<?php echo wp_create_nonce( 'app_nonce' ); ?>">
                        <table class="form-table cm-settings-fields">
                            <tbody><tr>
                                <th><label for="client_id">Client ID</label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo $clientId; ?>" id="client_id" name="client_id">
                                    <br>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="client_secrect">Client Secret</label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo $clientSecret; ?>" id="client_secret" name="client_secret">
                                    <br>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            </tbody>

                        </table>

                        <button id="btnSaveSettings" type="submit" class="button button-primary regular-text ltr">
                            Save Changes
                        </button>

                    </form>



            </div>
        </div>
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
