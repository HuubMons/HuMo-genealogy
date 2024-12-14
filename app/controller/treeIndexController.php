<?php
class TreeIndexController
{
    public function get_items($dbh)
    {
        $mainindex = new TreeIndexModel($dbh);
        $item_array = $mainindex->show_tree_index();
        return $item_array;
    }
}
