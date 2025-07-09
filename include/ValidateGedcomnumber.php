<?php

namespace Genealogy\Include;

class ValidateGedcomnumber
{
    function validate($gedcomnumber)
    {
        //$pattern = '/^^[a-z,A-Z][0-9]{1,}$/';
        $pattern = '/^[a-zA-Z][0-9]{1,}$/';
        if ($gedcomnumber) {
            return (bool)preg_match($pattern, $gedcomnumber);
        }
        return false;
    }
}
