<?php
require_once  __DIR__ . "/../model/list_names.php";

class List_namesController
{
    public function list_names($dbh, $tree_id, $user)
    {
        $list_namesModel = new list_namesModel();

        $get_alphabet_array = $list_namesModel->getAlphabetArray($dbh, $tree_id, $user);
        $get_max_cols = $list_namesModel->getMaxCols();
        $get_max_names = $list_namesModel->getMaxNames();
        return array(
            "alphabet_array" => $get_alphabet_array,
            "max_cols" => $get_max_cols,
            "max_names" => $get_max_names
        );
    }
}
