<?php

function str_replace_first($from, $to, $subject)
{
    $from = '/'.preg_quote($from, '/').'/';
    return preg_replace($from, $to, $subject, 1);
}

spl_autoload_register(
    function($classname) {

        $classname = str_replace_first('Juborm\\', '', $classname);
        $classname = str_replace('\\', '/', $classname);

        if (!file_exists("phar://juborm.phar/$classname.php")) {
            die(print_r($classname, true));
        }else{
            require_once "phar://juborm.phar/$classname.php";
        }
    },
    true,
    false
);

