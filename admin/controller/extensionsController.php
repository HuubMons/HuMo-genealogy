<?php
class ExtensionsController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail($language_file): array
    {
        $extensionsModel = new ExtensionsModel($this->admin_config);

        $extensions['theme_folders'] = $extensionsModel->get_theme_folders();

        $extensionsModel->save_settings($language_file, $extensions);

        // *** Re-read variables after changing them ***
        // *** Don't use include_once! Otherwise the old value will be shown ***
        include_once(__DIR__ . "/../../include/generalSettings.php");
        $generalSettings = new GeneralSettings();
        //$user = $generalSettings->get_user_settings();
        $humo_option = $generalSettings->get_humo_option($this->admin_config['dbh']);

        $extensions['hide_languages'] = explode(";", $humo_option["hide_languages"]);
        $extensions['hide_themes'] = explode(";", $humo_option["hide_themes"]);

        return $extensions;
    }
}
