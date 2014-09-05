<?php

set_exception_handler(
    function ($e) {
        $code = $e->getCode();
        $msg = $e->getMessage();
        $trace = $e->getFile().':'.$e->getLine();
        include 'error.php';
        die;
    }
);