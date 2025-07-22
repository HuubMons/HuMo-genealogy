<?php

/**
 * Function to safely show text on screen (in forms etc.)
 */

namespace Genealogy\Include;

class SafeTextShow
{
    function safe_text_show($text_safe): string
    {
        // *** First remove tags, ENT_QUOTES is used to also change single quote character: ' ***
        $text_safe = strip_tags($text_safe, ENT_QUOTES);
        // *** Also convert special characters into HTML entities ***
        $text_safe = htmlspecialchars($text_safe, ENT_QUOTES);
        return $text_safe;
    }
}
