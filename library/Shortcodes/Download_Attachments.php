<?php namespace Mosaicpro\WP\Plugins\Attachments;

use Mosaicpro\WpCore\Shortcode;

/**
 * Class Download_Attachments_Shortcode
 * @package Mosaicpro\WP\Plugins\Attachments
 */
class Download_Attachments_Shortcode extends Shortcode
{
    /**
     * Holds a Download_Attachments_Shortcode instance
     * @var
     */
    protected static $instance;

    /**
     * Add the Shortcode to WP
     */
    public function addShortcode()
    {
        add_shortcode('attachments_download', function($atts)
        {
            $atts = shortcode_atts(
                array(
                    'title' => false,
                    'type' => 'list',
                    'button_post_attachments_text' => '',
                    'list_post_attachments_text' => '',
                    'post_id' => false
                ), $atts
            );

            ob_start();
            the_widget( __NAMESPACE__ . '\Download_Attachments_Widget', $atts, ['before_title' => '<h4>', 'after_title' => '</h4>'] );
            $widget = ob_get_clean();

            return $widget;
        });
    }
}