<?php
class RenamePlaceController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new Editor_cls;
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
