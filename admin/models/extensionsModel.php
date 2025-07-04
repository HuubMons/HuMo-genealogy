<?php
class ExtensionsModel extends AdminBaseModel
{
    public function get_theme_folders(): array
    {
        // *** Read themes ***
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

    public function save_settings($language_file, $extensions): void
    {
        if (isset($_POST['save_option'])) {
            // *** Update settings / Language choice ***
            $language_total = '';
            $counter = count($language_file);
            for ($i = 0; $i < $counter; $i++) {
                // *** Get language name ***
                if ($language_file[$i] == $this->humo_option["default_language"] || $language_file[$i] == $this->humo_option["default_language_admin"]) {
                    // *** Don't hide default languages ***
                } elseif (!isset($_POST["$language_file[$i]"])) {
                    if ($language_total !== '') {
                        $language_total .= ';';
                    }
                    $language_total .= $language_file[$i];
                }
            }
            $this->db_functions->update_settings('hide_languages', $language_total);

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
            $this->db_functions->update_settings('hide_themes', $theme_total);
        }
    }
}
