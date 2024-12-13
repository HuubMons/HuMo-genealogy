<?php
class ExtensionsModel
{
    public function get_theme_folders()
    {
        // *** Read theme's ***
        $folder = opendir('../styles/');
        $theme_folders = [];
        while (false !== ($file = readdir($folder))) {
            if (substr($file, -4, 4) === '.css') {
                $theme_folders[] = $file;
            }
        }
        closedir($folder);
        return $theme_folders;
    }

    public function save_settings($db_functions, $humo_option, $language_file, $extensions)
    {
        if (isset($_POST['save_option'])) {
            // *** Update settings / Language choice ***
            $language_total = '';
            $counter = count($language_file);
            for ($i = 0; $i < $counter; $i++) {
                // *** Get language name ***
                if ($language_file[$i] == $humo_option["default_language"] || $language_file[$i] == $humo_option["default_language_admin"]) {
                    // *** Don't hide default languages ***
                } elseif (!isset($_POST["$language_file[$i]"])) {
                    if ($language_total !== '') {
                        $language_total .= ';';
                    }
                    $language_total .= $language_file[$i];
                }
            }
            $db_functions->update_settings('hide_languages', $language_total);

            // *** Update settings / Theme choice ***
            $theme_total = '';
            $counter = count($extensions['theme_folders']);
            for ($i = 0; $i < $counter; $i++) {
                $theme = $extensions['theme_folders'][$i];
                $theme = str_replace(".css", "", $theme);

                if (!isset($_POST["$theme"])) {
                    if ($theme_total != '') {
                        $theme_total .= ';';
                    }
                    $theme_total .= $theme;
                }
            }
            $db_functions->update_settings('hide_themes', $theme_total);
        }
    }
}
