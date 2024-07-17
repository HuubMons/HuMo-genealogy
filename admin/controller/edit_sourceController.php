<?php
require_once __DIR__ . "/../models/edit_source.php";

include_once(__DIR__ . "/../include/editor_cls.php");
include_once(__DIR__ . "/../include/select_tree.php");

class SourceController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }

    public function detail($dbh, $tree_id, $db_functions)
    {
        $editSourceModel = new EditSourceModel($dbh);
        $editSourceModel->set_source_id($dbh, $tree_id);
        $editSourceModel->update_source($dbh, $tree_id, $db_functions, $this->editor_cls);
        $editSource['source_id'] = $editSourceModel->get_source_id();

        $editSource['editor_cls'] = $this->editor_cls;

        return $editSource;
    }
}
