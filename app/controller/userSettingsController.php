<?php
class UserSettingsController
{
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

        if (isset($twofa)) {
            $data = array_merge($data, $twofa);
        }

        return $data;
    }
}
