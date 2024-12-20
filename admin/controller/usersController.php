<?php
class UsersController
{
    public function detail($dbh)
    {
        $usersModel = new UsersModel($dbh);
        //$usersModel->set_user_id();
        $users['alert'] = $usersModel->update_user($dbh);
        //$users['user_id'] = $usersModel->get_user_id();

        $check_username_password = $usersModel->check_username_password($dbh);

        return array_merge($users, $check_username_password);
    }
}
