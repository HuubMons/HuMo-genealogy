<?php
class ExtensionsController
{
    public function detail($dbh, $db_functions, $humo_option, $language_file)
    {
        $extensionsModel = new ExtensionsModel($dbh);
        $extensions['theme_folders'] = $extensionsModel->get_theme_folders();

        $extensionsModel->save_settings($db_functions, $humo_option, $language_file, $extensions);

        // *** Re-read variables after changing them ***
        // *** Don't use include_once! Otherwise the old value will be shown ***
        include_once(__DIR__ . "/../../include/generalSettings.php");
        $GeneralSettings = new GeneralSettings();
        //$user = $GeneralSettings->get_user_settings($dbh);
        $humo_option = $GeneralSettings->get_humo_option($dbh);

        $extensions['hide_languages'] = explode(";", $humo_option["hide_languages"]);
        $extensions['hide_themes'] = explode(";", $humo_option["hide_themes"]);

        return $extensions;
    }
}
