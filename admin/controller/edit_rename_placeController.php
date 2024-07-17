<?php
require_once __DIR__ . "/../models/edit_rename_place.php";

include_once(__DIR__ . "/../include/editor_cls.php");
include_once(__DIR__ . "/../include/select_tree.php");

class PlaceController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }

    public function detail($dbh, $tree_id)
    {
        $renamePlaceModel = new RenamePlaceModel($dbh);
        $renamePlaceModel->update_place($dbh, $tree_id, $this->editor_cls);
        $place['result'] = $renamePlaceModel->get_query($dbh, $tree_id);
        $place['select'] = $renamePlaceModel->get_place_select();

        return $place;
    }
}
