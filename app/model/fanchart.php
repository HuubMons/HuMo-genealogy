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
}
