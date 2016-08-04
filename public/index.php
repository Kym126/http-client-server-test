<?php
require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('GMT+0');

$server_protocol =  $_SERVER['SERVER_PROTOCOL'] . '200 OK';
$time = time();
$date = new DateTime("@$time");
$date_string = 'Date: ' . $date->format('D, d M Y H:i:s T');
$time_string = $date->format('H:i:s T');
$server = 'Server: ' . $_SERVER['SERVER_SOFTWARE'];
$last_mod = 'Last-Modified: ' . date('D, d M Y H:i:s T', filemtime('public/index.php'));
$content_type = 'Content-Type: application/json';

$message = array('@id' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
    'to' => 'Pillr',
    'subject' => 'Hello Pillr',
    'message' => 'Here is my submission',
    'from' => 'Kym',
    'timeSent' => $time_string);

$message = json_encode($message);

$length = 'Content Length: ' . strlen($message);

header($date_string);
header($server);
header($last_mod);
header($length);
header($content_type);
echo $message;

# TIP: Use the $_SERVER Sugerglobal to get all the data your need from the Client's HTTP Request.

# TIP: HTTP headers are printed natively in PHP by invoking header().
#      Ex. header('Content-Type', 'text/html');
