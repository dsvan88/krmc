<?php
ini_set('display_error', 1);
error_reporting(E_ALL);

function debug($str, $stop = true)
{
    echo '<pre>';
    var_dump($str);
    echo '</pre>';
    if ($stop)
        exit;
}
