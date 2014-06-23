<?php namespace Mosaicpro\WP\Plugins\Attachments;

use Mosaicpro\HtmlGenerators\Button\Button;
use Mosaicpro\WpCore\Utility;
use WP_Widget;

/**
 * Class Download_Attachments_Widget
 * @package Mosaicpro\WP\Plugins\Attachments
 */
class Download_Attachments_Widget extends WP_Widget
{
    /**
     * Construct the Widget
     */
    function __construct()
    {
        $options = array(
            'name' => '(MP Attachments) Download',
            'description' => 'Display Download Buttons for Attachments.'
        );
        parent::__construct(strtolower(class_basename(__CLASS__)), '', $options);
    }

    /**
     * The Widget Form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        extract($instance);
        $mp_attachments = Attachments::getInstance();

        if (!isset($type)) $type = 'list';
        $button_post_attachments_text = !empty($button_post_attachments_text) ? $button_post_attachments_text : $mp_attachments->__('Download Files');
        $link_post_attachments_text = !empty($link_post_attachments_text) ? $link_post_attachments_text : $button_post_attachments_text;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title: </label><br/>
            <input type="text"
                   class="widefat"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   value="<?php if (isset($title)) echo esc_attr($title); ?>" />
        </p>

        <p><strong>Display:</strong></p>
        <div id="<?php echo $this->get_field_id('type-controls'); ?>">
            <p>
                <label for="<?php echo $this->get_field_id('type-list'); ?>">
                    <input type="radio"
                           id="<?php echo $this->get_field_id('type-list'); ?>"
                           name="<?php echo $this->get_field_name('type'); ?>"
                           value="list"<?php checked('list', $type); ?> /> List of Download Links for all post attachments
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('type-button_post_attachments'); ?>">
                    <input type="radio"
                           id="<?php echo $this->get_field_id('type-button_post_attachments'); ?>"
                           name="<?php echo $this->get_field_name('type'); ?>"
                           value="button_post_attachments"<?php checked('button_post_attachments', $type); ?> /> Download Button for all post attachments
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('type-link_post_attachments'); ?>">
                    <input type="radio"
                           id="<?php echo $this->get_field_id('type-link_post_attachments'); ?>"
                           name="<?php echo $this->get_field_name('type'); ?>"
                           value="link_post_attachments"<?php checked('link_post_attachments', $type); ?> /> Download Link for all post attachments
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('type-button_attachment'); ?>">
                    <input type="radio"
                           id="<?php echo $this->get_field_id('type-button_attachment'); ?>"
                           name="<?php echo $this->get_field_name('type'); ?>"
                           value="button_attachment"<?php checked('button_attachment', $type); ?> /> Download Button for single attachment
                </label>
            </p>
        </div>

        <p id="<?php echo $this->get_field_id('type-button_post_attachments-options'); ?>">
            <label for="<?php echo $this->get_field_id('button_post_attachments_text'); ?>">Button Text: </label><br/>
            <input type="text"
                   class="widefat"
                   id="<?php echo $this->get_field_id('button_post_attachments_text'); ?>"
                   name="<?php echo $this->get_field_name('button_post_attachments_text'); ?>"
                   value="<?php if (isset($button_post_attachments_text)) echo esc_attr($button_post_attachments_text); ?>" />
        </p>
        <p id="<?php echo $this->get_field_id('type-link_post_attachments-options'); ?>">
            <label for="<?php echo $this->get_field_id('link_post_attachments_text'); ?>">Link Text: </label><br/>
            <input type="text"
                   class="widefat"
                   id="<?php echo $this->get_field_id('link_post_attachments_text'); ?>"
                   name="<?php echo $this->get_field_name('link_post_attachments_text'); ?>"
                   value="<?php if (isset($link_post_attachments_text)) echo esc_attr($link_post_attachments_text); ?>" />
        </p>
        <?php

        $types = ['list', 'button_post_attachments', 'link_post_attachments', 'button_attachment'];
        foreach ($types as $type)
        {
            Utility::enqueue_show_hide([
                'when' => '#' . $this->get_field_id('type-controls'),
                'attribute' => 'value',
                'is_value' => $type,
                'show_target' => '#' . $this->get_field_id('type-' . $type . '-options')
            ]);
        }
    }

    /**
     * The Widget Output
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        extract($args);
        extract($instance);

        if (!isset($post_id) || !$post_id) $post_id = get_the_ID();
        $type = isset($type) ? $type : 'list';

        $mp_attachments = Attachments::getInstance();
        $attachments = $mp_attachments->get_post_attachments($post_id);
        $button_post_attachments_text = !empty($button_post_attachments_text) ? $button_post_attachments_text : $mp_attachments->__('Download Files');
        $link_post_attachments_text = !empty($link_post_attachments_text) ? $link_post_attachments_text : $button_post_attachments_text;

        /*
        $courses = Courses::getInstance();
        if (get_post_type() == $courses->getPrefix('lesson'))
        {
            $course_id = isset($_REQUEST['course_id']) ? $_REQUEST['course_id'] : false;
            if ($course_id)
            {
                $course_attachments = $mp_attachments->get_post_attachments($course_id);
                if (!$course_attachments && !$attachments)
                    return;
            }
        }*/

        if (!$attachments)
            return;

        echo $before_widget;
        if (!empty($title)) echo $before_title . $title . $after_title;

        ?>
        <div class="text-center">
            <?php
            if ($type == 'list')
                echo $mp_attachments->get_download_attachments_list($attachments);

            if ($type == 'button_post_attachments')
                echo Button::success($button_post_attachments_text)
                    ->addUrl($mp_attachments->download_post_attachments_url($post_id));

            if ($type == 'link_post_attachments')
                echo Button::success($link_post_attachments_text)
                    ->addUrl($mp_attachments->download_post_attachments_url($post_id))
                    ->isLink();

            /*if (get_post_type() == $courses->getPrefix('lesson') && $course_id)
            {
                echo sprintf (
                    $courses->__('<p>You can also <a href="%1$s">get all the course files</a>.</p>'),
                    $mp_attachments->download_post_attachments_url($course_id) );
            }*/
            ?>
        </div>
        <?php
        echo $after_widget;
    }
}