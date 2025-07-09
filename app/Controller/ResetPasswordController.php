<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\ResetPasswordModel;

class ResetPasswordController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function detail(): array
    {
        $resetpasswordModel = new ResetPasswordModel($this->config);

        $resetpassword['activation_key'] = $resetpasswordModel->get_activation_key();
        $resetpassword['userid'] = $resetpasswordModel->get_userid();

        // *** Check mail address and spam question ***
        $resetpassword['check_input_msg'] = $resetpasswordModel->check_input();

        // *** Check clicked linked in mail ***
        $resetpassword['message_activation'] = $resetpasswordModel->check_clicked_link($resetpassword['userid'], $resetpassword['activation_key']);

        // *** Create pw_retrieval table if not exists ***
        $resetpasswordModel->check_table();

        $resetpassword['site_url'] = $resetpasswordModel->get_activation_url();

        // *** Check new password ***
        $resetpassword['message_password'] = $resetpasswordModel->check_new_password($resetpassword['userid'], $resetpassword['activation_key']);

        return $resetpassword;
    }
}
