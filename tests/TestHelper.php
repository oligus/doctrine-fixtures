<?php declare(strict_types=1);

use DoctrineFixtures\Tests\Doctrine\Bootstrap;

define("ROOT_PATH", __DIR__);
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');

ini_set("display_errors", '1');
error_reporting(E_ALL);

set_include_path(ROOT_PATH . PATH_SEPARATOR . get_include_path());

(new Bootstrap())->run();






