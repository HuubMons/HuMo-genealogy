<?php
require_once __DIR__ . "/../models/trees.php";

class TreesController
{
    /*
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }
    */

    public function detail($dbh, $tree_id, $db_functions)
    {
        $treesModel = new TreesModel($dbh);
        $treesModel->set_tree_id($tree_id); // $tree_id from index.php.
        $treesModel->update_tree($dbh, $db_functions);
        $trees['tree_id'] = $treesModel->get_tree_id();

        return $trees;
    }
}
