<?php

/**
 * Quality
 */

namespace Genealogy\Include;

class ShowQuality
{
    function show_quality(int $quality): string
    {
        $quality_text = '';
        if ($quality == '0') {
            $quality_text = __('quality: unreliable evidence or estimated data');
        }
        if ($quality == '1') {
            $quality_text = __('quality: questionable reliability of evidence');
        }
        if ($quality == '2') {
            $quality_text = __('quality: data from secondary evidence');
        }
        if ($quality == '3') {
            $quality_text = __('quality: data from direct source');
        }
        return $quality_text;
    }
}
