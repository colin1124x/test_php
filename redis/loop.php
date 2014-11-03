<?php

require __DIR__.'/../vendor/autoload.php';

$redis = new Predis\Client([
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
    ]);


$redis->pubSubLoop(['subscribe' => 'x'], function($client, $x){

        print_r($x);

    });

