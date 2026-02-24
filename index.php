<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/sources/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/sources/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require __DIR__.'/sources/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$request = Request::capture();
$app->handleRequest($request);