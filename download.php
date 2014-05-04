<?php
use Mosaicpro\WP\Plugins\Attachments\Attachments;

$path = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
include_once $path[0] . 'wp-load.php';

$attachments = Attachments::getInstance();
$attachment_id = isset($_GET['id']) ? (int) $_GET['id'] : false;
$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : false;

if (!$post_id && !$attachment_id) return wp_die($attachments->__('Nothing to download'));
if ($attachment_id) $attachments->download_attachment($attachment_id);
if ($post_id) $attachments->download_post_attachments($post_id);