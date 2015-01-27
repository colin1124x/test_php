<?php

$client = new GearmanClient();
$client->addServer(); // 預設為 localhost
$payload = 'hello '.($t = time());
$client->doBackground('say_hello', serialize($payload));
echo "msg({$t}) is done.\n";

