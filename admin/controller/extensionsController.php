<?php
require_once __DIR__ . "/../models/extensions.php";

class ExtensionsController
{
    public function detail($dbh, $db_functions, $humo_option, $language_file)
    {
        $extensionsModel = new ExtensionsModel($dbh);
        $extensions['theme_folders'] = $extensionsModel->get_theme_folders();

        $extensionsModel->save_settings($db_functions, $humo_option, $language_file, $extensions);


        // *** Re-read variables after changing them ***
        // *** Don't use include_once! Otherwise the old value will be shown ***
        include(__DIR__ . "/../../include/settings_global.php"); //variables
        $extensions['hide_languages'] = explode(";", $humo_option["hide_languages"]);
        $extensions['hide_themes'] = explode(";", $humo_option["hide_themes"]);


        return $extensions;
    }
}
