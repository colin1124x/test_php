<?php

register_shutdown_function(
    function () {
        $error = error_get_last();
        if (isset($error['type']) && E_ERROR == $error['type']) {
            $code = $error['type'];
            $msg = $error['message'];
            $trace = $error['file'].':'.$error['line'];
            include 'error.php';
            die;
        }
    }
);