<?php

/**
 * TotallyFilterPerson class
 *
 * This class checks if a person is totally filtered based on usergroup settings.
 */

namespace Genealogy\Include;

class TotallyFilterPerson
{
    public function isTotallyFiltered($user, $person)
    {
        if ($user["group_pers_hide_totally_act"] == 'j' && isset($person->pers_own_code) && strpos(' ' . $person->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
            return true;
        }
        return false;
    }
}
