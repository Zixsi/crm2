<?php

use Symfony\Component\Dotenv\Dotenv;

define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
define('CONFIG_DIR', ROOT_DIR . '/config');
define('LOG_DIR', ROOT_DIR . '/var/log');
define('CACHE_DIR', ROOT_DIR . '/var/cache');
define('VIEW_DIR', APP_DIR . '/Views');
define('WWW_DIR', ROOT_DIR . '/www');

include_once ROOT_DIR . '/vendor/autoload.php';

if (file_exists(ROOT_DIR . '/vendor/autoload.php')) {
	include_once ROOT_DIR . '/vendor/autoload.php';
} else {
	throw new Exception("vendor/autoload.php not found", 1);
}

$dotenv = new Dotenv();

if (file_exists(ROOT_DIR . '/.env')) {
	$dotenv->load(ROOT_DIR . '/.env');
}

if (getenv('ENV') === 'dev') {
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
} else {
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
}

return container();