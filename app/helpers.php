<?php

function get_ip()
{
    if ($_SERVER['HTTP_CF_CONNECTING_IP']) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif ($_SERVER['HTTP_X_REAL_IP']) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $ip = explode(',', $ip);

    return $ip[0];
}
