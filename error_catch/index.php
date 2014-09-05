<?php

ini_set('error_reporting', 0);

include 'shutdown.php';
include 'error_handle.php';
include 'exception_handle.php';

throw new Exception('xxx', 123);

//Fatal error
//new XX;

//Warning
//$x = 'x';
//foreach ($x as $n) {
//
//}

//notice
$a = array();
echo $a[1];

echo 'hello world';