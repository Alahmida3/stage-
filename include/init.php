<?php

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('SITE_ROOT') ? null : define('SITE_ROOT', dirname(__DIR__));
defined('LIB_PATH') ? null : define('LIB_PATH', __DIR__);


$web_root_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace(DS, '/', SITE_ROOT));
defined('web_root') ? null : define('web_root', $web_root_path . '/');

defined('PRODUCT_IMAGE_WEB_DIR') ? null : define('PRODUCT_IMAGE_WEB_DIR', 'produit/photos/');

defined('PRODUCT_IMAGE_SERVER_UPLOAD_PATH') ? null : define('PRODUCT_IMAGE_SERVER_UPLOAD_PATH', SITE_ROOT . DS . str_replace('/', DS, PRODUCT_IMAGE_WEB_DIR));

require_once(LIB_PATH.DS."data.php");
?>