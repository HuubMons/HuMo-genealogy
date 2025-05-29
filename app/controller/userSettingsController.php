<?php
class UserSettingsController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function user_settings($dataDb): array
    {
        $user_settingsModel = new UserSettingsModel($this->config);

        $get_user = $user_settingsModel->getUser();
        $result_message = $user_settingsModel->updateSettings($dataDb, $get_user);
        // Reload user settings (needed for 2FA).
        $get_user = $user_settingsModel->getUser();
        $twofa = $user_settingsModel->showQRcode($get_user);

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
