<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\AdminRepositoryModel;
use Genealogy\Include\Editor_cls;

class AdminRepositoryController
{
    protected $admin_config;
    private $editor_cls;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;

        $this->editor_cls = new Editor_cls;
    }

    public function detail(): array
    {
        $editRepositoryModel = new AdminRepositoryModel($this->admin_config);

        $editRepositoryModel->set_repo_id();
        $editRepositoryModel->update_repository($this->editor_cls);
        $editRepository['repo_id'] = $editRepositoryModel->get_repo_id();
        $editRepository['editor_cls'] = $this->editor_cls;

        return $editRepository;
    }
}
