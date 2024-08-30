<?php
require_once  __DIR__ . "/../model/reset_password.php";

class ResetpasswordController
{
    /*
    private $db_functions, $user;

    public function __construct($db_functions, $user)
    {
        $this->db_functions = $db_functions;
        $this->user = $user;
    }
    */

    public function detail($dbh, $humo_option)
    {
        $resetpasswordModel = new ResetpasswordModel();

        $resetpassword['activation_key'] = $resetpasswordModel->get_activation_key();
        $resetpassword['userid'] = $resetpasswordModel->get_userid();

        // *** Check mail address and spam question ***
        $resetpassword['check_input_msg'] = $resetpasswordModel->check_input($dbh, $humo_option);

        // *** Check clicked linked in mail ***
        $resetpassword['message_activation'] = $resetpasswordModel->check_clicked_link($dbh, $resetpassword['userid'], $resetpassword['activation_key']);

        // *** Create pw_retreival table if not exists ***
        $resetpasswordModel->check_table($dbh);

        $resetpassword['site_url'] = $resetpasswordModel->get_activation_url();

        return $resetpassword;
    }
}
