<?php

$query_string = http_build_query(
    array(
        'username' => 'xxx',
        'sid' => rand(1, 10000),
        'E_WEB' => 'sk2',
        'E_SITE' => 'mem'
    )
);

//$url = 'http://sk2.mem.pitaya/api/login/test';
$url = 'http://api.pitaya/api/login/test';

$curl = curl_init($url.'?'.$query_string);

curl_setopt_array(
    $curl,
    array(
        CURLOPT_USERPWD => 'colin:',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
    )
);
$ret = curl_exec($curl);

var_dump(json_decode($ret, true), $ret, curl_getinfo($curl));

curl_close($curl);
