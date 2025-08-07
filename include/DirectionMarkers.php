<?php

/**
 * DirectionMarkers.php
 * 
 * Jul. 2025 Huub: created a general DirectionMarkers class.
 */

namespace Genealogy\Include;

class DirectionMarkers
{
    public $dirmark1;
    public $dirmark2;
    public $rtlmarker;
    public $alignmarker;

    public function __construct(string $direction = 'ltr', string|null $screen_mode = 'HTML')
    {
        if ($direction == 'ltr') {
            $this->dirmark1 = "&#x200E;";  // ltr marker
            $this->dirmark2 = "&#x200F;";  // rtl marker
            $this->rtlmarker = "ltr";
            $this->alignmarker = "left";
        } elseif ($direction == 'rtl') {
            $this->dirmark1 = "&#x200F;";  // rtl marker
            $this->dirmark2 = "&#x200E;";  // ltr marker
            $this->rtlmarker = "rtl";
            $this->alignmarker = "right";
        }
        
        // *** Don't use direction markers in PDF export ***
        if ($screen_mode == 'PDF' || $screen_mode == 'ASPDF') {
            $this->dirmark1 = '';
            $this->dirmark2 = '';
            $this->rtlmarker = '';
            $this->alignmarker = '';
        }
    }
}
