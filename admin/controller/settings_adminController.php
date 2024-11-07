<?php
require_once __DIR__ . "/../models/settings_admin.php";

class SettingsController
{
    public function detail($dbh, $db_functions, $humo_option)
    {
        $settingsModel = new SettingsModel($dbh);

        $settings['menu_tab'] = $settingsModel->get_menu_tab();

        $settings['time_lang'] = $settingsModel->get_timeline_language($humo_option);

        $settingsModel->save_settings($dbh, $db_functions, $humo_option, $settings);

        return $settings;
    }
}
