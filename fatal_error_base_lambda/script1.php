<?php

//重現 Fatal error: Base lambda function for closure not found
apc_clear_cache();
//ini_set('apc.cache_by_default', 0);
//include 'lambda_function.php';
//
////header('Location: script4.php');
//
//ini_set('apc.cache_by_default', 1);
//include 'lambda_function.php';
?>

<a href="script2.php">go to script2</a>