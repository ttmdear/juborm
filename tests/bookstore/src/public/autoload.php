<?php
// composer autoload
require_once "../../../vendor/autoload.php";

$fun = get_defined_functions();
if(!in_array('str_replace_first', $fun['user'])){
    function str_replace_first($from, $to, $subject)
    {
        $from = '/'.preg_quote($from, '/').'/';
        return preg_replace($from, $to, $subject, 1);
    }

}

spl_autoload_register(
    function($classname) {
        $classname = str_replace_first('Bookstore', __DIR__.'/../../src', $classname);
        $classname = str_replace('\\', '/', $classname);
        $classname = "$classname.php";


        if (file_exists($classname)) {
            require $classname;
        }
    },
    true,
    false
);
