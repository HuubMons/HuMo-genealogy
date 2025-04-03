<?php
class TreeIndexController
{
    public function get_items($dbh, $humo_option)
    {
        $mainindex = new TreeIndexModel($dbh, $humo_option);
        $item_array = $mainindex->show_tree_index();
        return $item_array;
    }
}
