<?php

require '/home/colin/php-websocket/client/lib/class.websocket_client.php';

$client = new WebsocketClient;


var_dump($client->connect('127.0.0.1', 8000, '/demo'));


$payload = json_encode(array(
    'action' => 'echo',
    'data' => 'dos'
));

var_dump($client->sendData($payload));
