<?php

require 'AuthBasic.php';

$basic = new AuthBasic(array('colin' => password_hash('123', PASSWORD_BCRYPT)));

 ! $basic->isAuthorized() and $basic->challenge();

//if ( ! isset($_SERVER['PHP_AUTH_USER']) or
//    ! isset($_SERVER['PHP_AUTH_PW']) or
//    'colin:123' !== "{$_SERVER['PHP_AUTH_USER']}:{$_SERVER['PHP_AUTH_PW']}") {
//    header('WWW-Authenticate: Basic realm="abc"');
//    header('HTTP/1.0 401 Unauthorized');
//    echo 'Text to send if user hits Cancel button';
//} else {
//    echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
//    echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";
//}

echo <<<EOF
<div>
    <a href="index.php">index</a>
    <a href="page2.php">page 2</a>
    <a href="logout.php">logout</a>
</div>
EOF;
