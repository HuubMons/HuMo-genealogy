<?php
require_once  __DIR__ . "/../model/ancestor.php";
require_once  __DIR__ . "/../model/fanchart.php";

require_once(__DIR__ . "/../../include/fanchart/persian_log2vis.php");

class FanchartController
{
    /*
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }
    */

    public function detail($dbh, $tree_id)
    {
        $get_fanchartModel = new FanchartModel($dbh);
        $main_person = $get_fanchartModel->getMainPerson();
        $ancestor_header = $get_fanchartModel->getAncestorHeader('Fanchart', $tree_id, $main_person);

        $chosengen = $get_fanchartModel->get_chosengen();

        $data = array(
            "main_person" => $main_person,
            "ancestor_header" => $ancestor_header,
            "chosengen" => $chosengen
        );
        return $data;
    }
}
