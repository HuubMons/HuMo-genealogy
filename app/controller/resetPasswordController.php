<?php
class ResetPasswordController
{
    public function detail($dbh, $humo_option)
    {
        $resetpasswordModel = new ResetPasswordModel();

        $resetpassword['activation_key'] = $resetpasswordModel->get_activation_key();
        $resetpassword['userid'] = $resetpasswordModel->get_userid();

        // *** Check mail address and spam question ***
        $resetpassword['check_input_msg'] = $resetpasswordModel->check_input($dbh, $humo_option);

        // *** Check clicked linked in mail ***
        $resetpassword['message_activation'] = $resetpasswordModel->check_clicked_link($dbh, $resetpassword['userid'], $resetpassword['activation_key']);

        // *** Create pw_retrieval table if not exists ***
        $resetpasswordModel->check_table($dbh);

        $resetpassword['site_url'] = $resetpasswordModel->get_activation_url();

        // *** Check new password ***
        $resetpassword['message_password'] = $resetpasswordModel->check_new_password($dbh, $resetpassword['userid'], $resetpassword['activation_key']);

        return $resetpassword;
    }
}
