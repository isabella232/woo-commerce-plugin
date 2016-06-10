<?php
$clientSecret = \core\Settings::get('client_secret');
$clientId = \core\Settings::get('client_id');

?>

<div class="wrap">

    <div>
        <div class="content">
            <div class="box main-container text-center">
                <h1>Campaign Monitor Settings</h1>
                    <form action="<?php echo get_site_url(); ?>/wp-admin/admin-post.php" method="post">
                        <input type="hidden" name="action" value="handle_request">
                        <input type="hidden" name="data[type]" value="save_settings">
                        <input type="hidden" name="data[app_nonce]" value="<?php echo wp_create_nonce( 'app_nonce' ); ?>">
                        <table class="form-table">
                            <tbody><tr>
                                <th><label for="client_id">Client ID</label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo $clientId; ?>" id="client_id" name="client_id">
                                    <br>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="client_secrect">Client Secrect</label></th>
                                <td>
                                    <input type="text" class="regular-text" value="<?php echo $clientSecret; ?>" id="client_secret" name="client_secret">
                                    <br>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <button id="btnSaveSettings" type="submit" class="button button-primary regular-text ltr">
                            Save Settings
                        </button>

                    </form>



            </div>
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
