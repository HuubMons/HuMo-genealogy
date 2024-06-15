<?php
require_once  __DIR__ . "/../model/tree_index.php";

class Tree_indexController
{
    //private $db_functions;

    /*
    public function __construct()
    {
        $this->db_functions = $db_functions;
    }
    */

    public function get_items($dbh)
    {
        $mainindex = new Mainindex_cls($dbh);
        $item_array = $mainindex->show_tree_index();
        return $item_array;
    }
}
