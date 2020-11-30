<?php
/*
Plugin Name: Campaign Monitor for WooCommerce
Plugin URI: https://www.campaignmonitor.com/integrations/
Description: Email marketing with all the features you want. With <strong>Campaign Monitor</strong>, you have everything you need to run beautifully designed, professional email marketing campaigns to grow your business.
Version: 1.4.7
Author: Campaign Monitor
Author URI: https://www.campaignmonitor.com/integrations/
Text Domain: campaign-monitor-for-woocommerce
Tags: email, marketing
Requires at least: 4.0.1
Tested up to: 5.6
Stable tag: 4.5.3
License: GPLv3 or later License
URI: http://www.gnu.org/licenses/gpl-3.0.html
WC requires at least: 2.5
WC tested up to: 4.7.0
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
$version = floatval(phpversion());
if($version < 5.3) {
    if (isset($_GET['activate'])) {
        unset($_GET['activate']);
    }
    add_action('admin_notices', function() {
        $html = '<div id="message" class="error notice is-dismissible">';
        $html .= '<p>';
        $html .= __(' Campaign Monitor for WooCommerce requires at least PHP Version 5.3.0, version: '.phpversion().' detected', 'campaign-monitor-woocommerce');
        $html .= '</p>';
        $html .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
        $html .= '</div><!-- /.updated -->';
        echo $html;
        deactivate_plugins( __FILE__, true);
    });
} else {
    // Set the version of this plugin
    if( ! defined( 'CAMPAIGN_MONITOR_WOOCOMMERCE' ) ) {
        define( 'CAMPAIGN_MONITOR_WOOCOMMERCE','1.0' );
    } // end if
    define('CAMPAIGN_MONITOR_WOOCOMMERCE_DIR', plugin_dir_path(__FILE__));

    spl_autoload_register(function ($class_name) {

        $location = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class_name)  . '.php';
        if (file_exists($location)) {
            try{
                require_once $location;
                return;
            } catch(Exception $e){
                throw new Exception($e->getMessage());
            }
        }
    });

    add_action('plugins_loaded', function(){

        core\App::$pluginPath = __FILE__;
        core\App::$pluginDirectory = CAMPAIGN_MONITOR_WOOCOMMERCE_DIR;
        core\App::run();

    });
}

