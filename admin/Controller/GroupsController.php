<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\GroupsModel;

class GroupsController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $groupsModel = new GroupsModel($this->admin_config);

        $groupsModel->set_group_id();
        $groupsModel->update_group();
        $groups['group_id'] = $groupsModel->get_group_id();

        return $groups;
    }
}
