<?php
function visitorIP()
{
    // Check if visitor is from shared network
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $vIP = $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check if visitor is using a proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $vIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    // Check for the remote address of visitor.
    else {
        $vIP = $_SERVER['REMOTE_ADDR'];
    }
    return $vIP;
}
