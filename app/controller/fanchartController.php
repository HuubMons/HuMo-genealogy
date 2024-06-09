<?php
require_once  __DIR__ . "/../model/ancestor.php";

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
        $get_ancestorModel = new AncestorModel($dbh);
        $main_person = $get_ancestorModel->getMainPerson();
        $data['ancestor_header'] = $get_ancestorModel->getAncestorHeader('Fanchart', $tree_id, $main_person);

        return $data;
    }
}
