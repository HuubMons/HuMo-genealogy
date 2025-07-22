<?php

namespace Genealogy\Include;

class LanguagePersonName
{
    function language_name($text): string
    {
        $nameTypes = [
            '_ALIA' => __('alias name') . ': ', // For Pro-Gen
            '_SHON' => __('Short name (for reports)') . ': ',
            '_ADPN' => __('Adopted name') . ': ',
            '_HEBN' => __('Hebrew name') . ': ',
            '_CENN' => __('Census name') . ': ',
            '_MARN' => __('Married name') . ': ',
            '_GERN' => __('Given name') . ': ',
            '_FARN' => __('Farm name') . ': ',
            '_BIRN' => __('Birth name') . ': ',
            '_INDN' => __('Indian name') . ': ',
            '_FKAN' => __('Formal name') . ': ',
            '_CURN' => __('Current name') . ': ',
            '_SLDN' => __('Soldier name') . ': ',
            '_FRKA' => __('Formerly known as') . ': ',
            '_RELN' => __('Religious name') . ': ',
            '_OTHN' => __('Other name') . ': ',
        ];
        return $nameTypes[$text] ?? '';
    }
}
