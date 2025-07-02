<?php
class GetVisitorIP
{
    function visitorIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // Visitor is from shared network
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Visitor is using a proxy
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            // Remote address of visitor.
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
