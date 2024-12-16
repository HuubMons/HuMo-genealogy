<?php
class AdminRepositoryController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new Editor_cls;
    }

    public function detail($dbh, $tree_id, $db_functions)
    {
        $editRepositoryModel = new AdminRepositoryModel($dbh);
        $editRepositoryModel->set_repo_id();
        $editRepositoryModel->update_repository($dbh, $tree_id, $db_functions, $this->editor_cls);
        $editRepository['repo_id'] = $editRepositoryModel->get_repo_id();

        $editRepository['editor_cls'] = $this->editor_cls;

        return $editRepository;
    }
}
