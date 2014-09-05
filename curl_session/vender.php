<?php

//測試curl 跨站存session
session_start();

$_SESSION['curl_session'] = $_POST;

session_write_close();

header('Content-Type:application/json;charset=UTF-8');
echo json_encode(
    array(
        'result' => true,
        'session' => $_SESSION,
        'session_id' => session_id(),
        'cookie' => $_COOKIE,
    )
);
