<?php
$path = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
include_once $path[0] . 'wp-load.php';

$attachments = \Mosaicpro\WP\Plugins\Attachments\Attachments::getInstance();
$attachments->download_attachment(isset($_GET['id']) ? (int) $_GET['id'] : 0);