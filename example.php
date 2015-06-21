<?php
/**
 * Created by PhpStorm.
 * User: ilyas
 * Date: 21.06.2015
 * Time: 10:55
 */

require_once("./vendor/autoload.php");

$socketBridge = WebSocketBridge\Factory::create('wss://0.0.0.0:8033','wss://echo.websocket.org');

//$socketBridge->setUpSSL('/path/to/cert.pem');

$socketBridge->run();