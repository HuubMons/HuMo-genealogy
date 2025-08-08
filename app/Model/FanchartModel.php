<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\AncestorModel;

class FanchartModel extends AncestorModel
{
    public function get_chosengen(): int
    {
        $chosengen = 5;
        if (isset($_GET["chosengen"])) {
            $chosengen = $_GET["chosengen"];
        }
        if (isset($_POST["chosengen"])) {
            $chosengen = $_POST["chosengen"];
        }
        return $chosengen;
    }

    public function get_fontsize(): int
    {
        $fontsize = 8;
        if (isset($_GET["fontsize"])) {
            $fontsize = $_GET["fontsize"];
        }
        if (isset($_POST["fontsize"])) {
            $fontsize = $_POST["fontsize"];
        }
        return $fontsize;
    }

    public function get_date_display(): int
    {
        $date_display = 2;
        if (isset($_GET["date_display"])) {
            $date_display = $_GET["date_display"];
        }
        if (isset($_POST["date_display"])) {
            $date_display = $_POST["date_display"];
        }
        return $date_display;
    }

    public function get_printing(): int
    {
        $printing = 1;
        if (isset($_GET["printing"])) {
            $printing = $_GET["printing"];
        }
        if (isset($_POST["printing"])) {
            $printing = $_POST["printing"];
        }
        return $printing;
    }

    public function get_fan_style(): int
    {
        $fan_style = 3;
        if (isset($_GET["fan_style"])) {
            $fan_style = $_GET["fan_style"];
        }
        if (isset($_POST["fan_style"])) {
            $fan_style = $_POST["fan_style"];
        }
        return $fan_style;
    }

    public function get_fan_width(): string
    {
        $fan_width = "auto";
        if (isset($_GET["fan_width"])) {
            $fan_width = $_GET["fan_width"];
        }
        if (isset($_POST["fan_width"])) {
            $fan_width = $_POST["fan_width"];
        }
        return $fan_width;
    }

    public function get_real_width($fan_width): int
    {
        if ($fan_width > 50 and $fan_width < 301) {
            $tmp_width = $fan_width;
        } else {
            // "auto" or invalid entry - reset to 100%
            $tmp_width = 100;
        }
        $realwidth = (840 * $tmp_width) / 100; // realwidth needed for next line (top text)
        return $realwidth;
    }

    public function generate_fanchart_item_array($chosengen): array
    {
        $fanchart_item = array();
        $maxperson = pow(2, $chosengen);
        // initialize array
        for ($i = 0; $i < $maxperson; $i++) {
            for ($n = 0; $n < 6; $n++) {
                $fanchart_item[$i][$n] = "";
            }
        }
        return $fanchart_item;
    }
}
