<?php
class UsersController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $usersModel = new UsersModel($this->admin_config);

        //$usersModel->set_user_id();
        $users['alert'] = $usersModel->update_user();
        //$users['user_id'] = $usersModel->get_user_id();

        $check_username_password = $usersModel->check_username_password();

        return array_merge($users, $check_username_password);
    }
}
