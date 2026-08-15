<?php

namespace Genealogy\Include;

class ValidateGedcomnumber
{
    public function validate($gedcomnumber): bool
    {
        if (!is_string($gedcomnumber) && !is_int($gedcomnumber)) {
            return false;
        }

        $gedcomnumber = trim((string)$gedcomnumber);
        if ($gedcomnumber === '') {
            return false;
        }

        $pattern = '/^[a-zA-Z][0-9]{1,}$/';
        return (bool) preg_match($pattern, $gedcomnumber);
    }
}
