<?php
//应用所在目录
define("APP_PATH",  __DIR__ . '/application/');
//使用Composer
define("VENDOR_DIR", __DIR__ . '/vendor/');
require(VENDOR_DIR . 'autoload.php');
//初始化
$app  = new yesf\Yesf(APP_PATH . "/Config.ini");
$app->bootstrap()->run();