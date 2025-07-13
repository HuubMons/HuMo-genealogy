<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\AdminSettingsModel;
use Genealogy\Admin\Models\SettingsHomepageModel;

class AdminSettingsController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $settingsModel = new AdminSettingsModel($this->admin_config);

        $settings['menu_tab'] = $settingsModel->get_menu_tab();
        $settings['time_lang'] = $settingsModel->get_timeline_language();
        $settingsModel->save_settings($settings);

        // *** Use a seperate controller for each tab ***
        if ($settings['menu_tab'] == 'settings_homepage') {
            $settings_homepageModel = new SettingsHomepageModel($this->admin_config);

            $settings_homepageModel->reset_modules();
            $settings_homepageModel->save_settings_modules();
            $settings_homepageModel->order_modules();

            $modules = $settings_homepageModel->get_modules();
            $settings = array_merge($settings, $modules);

            $settings_homepageModel->save_settings_favorites();
        }

        return $settings;
    }
}
