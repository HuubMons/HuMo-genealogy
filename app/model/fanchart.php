<?php

class FanchartModel extends AncestorModel
{
    /*
    private $Connection;

    public function __construct($Connection)
    {
        $this->Connection = $Connection;
    }
    */

    public function get_chosengen()
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

    public function get_fontsize()
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

    public function get_date_display()
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

    public function get_printing()
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

    public function generate_fanchart_item_array($chosengen)
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
