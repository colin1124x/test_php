<?php

set_error_handler(
    function ($err_no, $err_msg, $err_file, $err_line, $err_context) {
        $code = $err_no;
        $msg = $err_msg;
        $trace = $err_file.':'.$err_line;
        include 'error.php';
        die;
    },
    E_ALL & ~ E_NOTICE
);