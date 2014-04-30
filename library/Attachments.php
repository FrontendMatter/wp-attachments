<?php namespace Mosaicpro\WP\Plugins\Attachments;

use Mosaicpro\Core\IoC;
use Mosaicpro\WpCore\CRUD;
use Mosaicpro\WpCore\MetaBox;
use Mosaicpro\WpCore\PluginGeneric;
use Mosaicpro\WpCore\ThickBox;

/**
 * Class Attachments
 * @package Mosaicpro\WP\Plugins\Attachments
 */
class Attachments extends PluginGeneric
{
    /**
     * Holds an Attachments instance
     * @var
     */
    protected static $instance;

    /**
     * Holds the post types that have attachments
     * @var array
     */
    protected $post_types = [];

    /**
     * Initialize the plugin
     */
    public static function init()
    {
        $instance = self::getInstance();

        // i18n
        $instance->loadTextDomain();

        // Load Plugin Templates into the current Theme
        $instance->plugin->initPluginTemplates();

        // Get the Container from IoC
        $app = IoC::getContainer();

        // Bind the Attachments to the Container
        $app->bindShared('attachments', function() use ($instance)
        {
            return $instance;
        });
    }

    /**
     * Get a Singleton instance of Attachments
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Register Attachments for provided post types
     * @param $post_types
     */
    public function register($post_types)
    {
        $this->post_types = array_merge($this->post_types, $post_types);

        // create relationships
        $this->crud();

        // create metaboxes
        $this->metaboxes();
    }

    /**
     * Create CRUD Relationships
     */
    private function crud()
    {
        $relation = 'attachment';
        foreach ($this->post_types as $post => $label)
        {
            CRUD::make($this->prefix, $post, $relation)
                ->setListFields($relation, $this->getListFields())
                ->setListActions($relation, ['remove_related', 'add_to_post'])
                ->setPostRelatedListActions($relation, ['sortable', 'remove_from_post'])
                ->register();
        }
    }

    /**
     * Get the CRUD List Fields
     * @return array
     */
    private function getListFields()
    {
        return [
            'ID',
            function($post)
            {
                return [
                    'field' => $this->__('Attachment'),
                    'value' => (!wp_attachment_is_image($post->ID) ? '<span class="glyphicon glyphicon-file"></span>' . PHP_EOL : '') .
                        wp_get_attachment_link($post->ID, [50, 50])
                ];
            },
            function($post)
            {
                return [
                    'field' => $this->__('Downloads'),
                    'value' => (int) get_post_meta($post->ID, '_mp_attachment_downloads', true)
                ];
            }
        ];
    }

    /**
     * Create the Meta Boxes
     */
    private function metaboxes()
    {
        foreach ($this->post_types as $post => $metabox_header)
        {
            MetaBox::make($this->prefix, 'attachments', $metabox_header)
                ->setPostType($post)
                ->setDisplay([
                    CRUD::getListContainer(['attachment']),
                    ThickBox::register_iframe( 'thickbox_attachments_list', $this->__('Assign Attachments'), 'admin-ajax.php',
                        ['action' => 'list_' . $post . '_attachment'] )->render(),
                    ThickBox::register_iframe( 'thickbox_attachments_new', $this->__('Upload'), 'media-upload.php',
                        [] )->setButtonAttributes(['class' => 'thickbox button-primary'])->render()
                ])
                ->register();
        }
    }

    /**
     * @param $attachment_id
     * @return bool|string
     */
    public function download_attachment_link($attachment_id)
    {
        if (get_post_type($attachment_id) !== 'attachment') return false;
        $title = get_the_title($attachment_id);
        $link = '<a href="' . $this->download_attachment_url($attachment_id) . '" title="' . $title . '">' . $title . '</a>';return $link;
    }

    /**
     * Get download URL for attachment
     * @param $attachment_id
     * @return string
     */
    public function download_attachment_url($attachment_id)
    {
        // $url = site_url('/'.$options['download_link'].'/'.$attachment_id.'/');
        $url = plugins_url( 'mp-attachments/download.php?id=' . $attachment_id);
        return $url;
    }

    /**
     * Get all post attachments
     * @param $post_id
     * @return array
     */
    public function get_post_attachments($post_id)
    {
        $attachments_ids = get_post_meta($post_id, 'attachment');
        $attachments = get_posts([
            'post_type' => 'attachment',
            'post__in' => $attachments_ids
        ]);
        return $attachments;
    }

    /**
     * Download attachment
     * @param $attachment_id
     * @return bool
     */
    public function download_attachment($attachment_id)
    {
        if (get_post_type($attachment_id) !== 'attachment') return false;

        $uploads = wp_upload_dir();
        $attachment = get_post_meta($attachment_id, '_wp_attached_file', true);
        $filepath = $uploads['basedir'] . '/' . $attachment;

        if(!file_exists($filepath) || !is_readable($filepath))
            return false;

        $filename = $attachment;

        // no directory names
        if (($position = strrpos($attachment, '/', 0)) !== false)
            $filename = substr($attachment, $position + 1);

        if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

        header('Content-Type: application/download');
        header('Content-Disposition: attachment; filename=' . rawurldecode($filename));
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        header('Cache-control: private');
        header('Pragma: private');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-Length: ' . filesize($filepath));

        if ($filepath = fopen($filepath, 'r'))
        {
            while(!feof($filepath) && (!connection_aborted()))
            {
                echo($buffer = fread($filepath, 524288));
                flush();
            }

            fclose($filepath);
        }
        else return false;

        update_post_meta($attachment_id, '_mp_attachment_downloads', (int) get_post_meta($attachment_id, '_mp_attachment_downloads', true) + 1);

        exit;
    }
}