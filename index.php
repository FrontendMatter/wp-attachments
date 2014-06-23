<?php namespace Mosaicpro\WP\Plugins\Attachments;

/*
Plugin Name: MP Attachments
Plugin URI: http://mosaicpro.biz
Description: Download Attachments Manager that allows to easily create and display download links to any post or page.
Version: 1.0
Author: MosaicPro
Author URI: http://mosaicpro.biz
Text Domain: mp-attachments
*/

// If this file is called directly, exit.
if ( ! defined( 'WPINC' ) ) { die; }

use Mosaicpro\HtmlGenerators\Core\IoC;
use Mosaicpro\WpCore\Plugin;

// Plugin libraries
$libraries = [
    'Attachments'
];

// Plugin initialization
add_action('plugins_loaded', function() use ($libraries)
{
    if (!class_exists('Mosaicpro\\HtmlGenerators\\Core\\IoC') || !class_exists('Mosaicpro\\WpCore\\Plugin'))
        return;

    // Get the Container from IoC
    $app = IoC::getContainer();

    // Bind the Plugin to the Container
    $app->bindShared('plugin', function()
    {
        return new Plugin( __FILE__ );
    });

    // Load & Initialize libraries
    foreach ($libraries as $library)
    {
        require_once dirname(__FILE__) . '/library/' . $library . '.php';
        forward_static_call_array([ __NAMESPACE__ . '\\' . $library, 'init' ], []);
    }
});

// Plugin activation
register_activation_hook(__FILE__, function() use ($libraries)
{
    // Let the Plugin components know they are being executed in the Plugin activation hook
    defined('MP_PLUGIN_ACTIVATING') || define('MP_PLUGIN_ACTIVATING', true);

    foreach ($libraries as $library)
    {
        require dirname(__FILE__) . '/library/' . $library . '.php';
        forward_static_call_array([ __NAMESPACE__ . '\\' . $library, 'activate' ], []);
    }
});