<?php
require_once  __DIR__ . "/../model/user_settings.php";

include_once(__DIR__ . "/../../include/2fa_authentication/authenticator.php");
//if (isset($_POST['update_settings'])) include_once(__DIR__ . '/../../include/mail.php');


class User_settingsController
{
    //private $db_functions, $user;

    /*
    public function __construct($db_functions, $user)
    {
        $this->db_functions = $db_functions;
        $this->user = $user;
    }
    */

    public function user_settings($dbh, $dataDb, $humo_option, $user)
    {
        $user_settingsModel = new UserSettingsModel();

        $get_user = $user_settingsModel->getUser($dbh);

        $result_message = $user_settingsModel->updateSettings($dbh, $dataDb, $get_user, $humo_option);

        // Reload user settings (needed for 2FA).
        $get_user = $user_settingsModel->getUser($dbh);

        $twofa = $user_settingsModel->showQRcode($dbh, $get_user, $user);


        $data = array(
            "user" => $get_user,
            "result_message" => $result_message
        );

        if (isset($twofa)) $data = array_merge($data, $twofa);

        return $data;
    }
}
