<?php
class adminSettingsController
{
    public function detail($dbh, $db_functions, $humo_option)
    {
        $settingsModel = new AdminSettingsModel($dbh);
        $settings['menu_tab'] = $settingsModel->get_menu_tab();
        $settings['time_lang'] = $settingsModel->get_timeline_language($humo_option);
        $settingsModel->save_settings($dbh, $db_functions, $humo_option, $settings);

        // *** Use a seperate controller for each tab ***
        if ($settings['menu_tab'] == 'settings_homepage') {
            $settings_homepageModel = new SettingsHomepageModel($dbh);
            $settings_homepageModel -> reset_modules($dbh);
            $settings_homepageModel -> save_settings_modules($dbh);
            $settings_homepageModel -> order_modules($dbh);

            $modules = $settings_homepageModel -> get_modules($dbh);
            $settings = array_merge($settings, $modules);

            $settings_homepageModel -> save_settings_favorites($dbh);
        }

        return $settings;
    }
}
