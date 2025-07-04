<?php

/**
 * Safely store items in tables
 */
class SafeTextDb
{
    function safe_text_db($text_safe): string
    {
        global $dbh;

        if ($text_safe) {
            $text_safe = $dbh->quote($text_safe);

            // PDO "quote" escapes like mysql_real_escape_string, BUT also encloses in single quotes. 
            // In all HuMo-genealogy scripts the single quotes are already coded ( "...some-parameter = '".$var."'")  so we take them off:
            $text_safe = substr($text_safe, 1, -1); // remove quotes from beginning and end
        }

        return $text_safe;
    }
}
