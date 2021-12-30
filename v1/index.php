<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');
header('Access-Control-Allow-Origin: *');
ini_set('memory_limit', '-1'); // unlimited memory limit
date_default_timezone_set('UTC');


define("APP_BASE_PATH", __DIR__.'/');
define("VERSION", '1.0');


include_once "http/request.php";
include_once "vendor/utils.php";

Request::instance()->start();



