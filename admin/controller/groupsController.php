<?php
require_once __DIR__ . "/../models/groups.php";

class GroupsController
{
    public function detail($dbh)
    {
        $groupsModel = new GroupsModel($dbh);
        $groupsModel->set_group_id();
        $groupsModel->update_group($dbh);
        $groups['group_id'] = $groupsModel->get_group_id();

        return $groups;
    }
}
