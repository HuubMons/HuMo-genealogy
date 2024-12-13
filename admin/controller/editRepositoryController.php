<?php
include_once(__DIR__ . "/../include/editor_cls.php");
include_once(__DIR__ . "/../include/select_tree.php");

class EditRepositoryController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }

    public function detail($dbh, $tree_id, $db_functions)
    {
        $editRepositoryModel = new EditRepositoryModel($dbh);
        $editRepositoryModel->set_repo_id();
        $editRepositoryModel->update_repository($dbh, $tree_id, $db_functions, $this->editor_cls);
        $editRepository['repo_id'] = $editRepositoryModel->get_repo_id();

        $editRepository['editor_cls'] = $this->editor_cls;

        return $editRepository;
    }
}
