<?php
// *** Jan. 2024. Added class for processing language selection ***
class LanguageCls
{
    public function get_languages()
    {
        $language_folder = opendir(__DIR__);
        while (false !== ($file = readdir($language_folder))) {
            if (strlen($file) < 6 and $file != '.' and $file != '..') {
                $language_file[] = $file;
                // *** Order of languages ***
                if ($file == 'cn') {
                    $language_order[] = 'Chinese';
                } elseif ($file == 'cs') {
                    $language_order[] = 'Czech';
                } elseif ($file == 'da') {
                    $language_order[] = 'Dansk';
                } elseif ($file == 'de') {
                    $language_order[] = 'Deutsch';
                } elseif ($file == 'en') {
                    $language_order[] = 'English';
                } elseif ($file == 'en_ca') {
                    $language_order[] = 'English_ca';
                } elseif ($file == 'en_us') {
                    $language_order[] = 'English_us';
                } elseif ($file == 'es') {
                    $language_order[] = 'Espanol';
                } elseif ($file == 'fi') {
                    $language_order[] = 'Suomi';
                } elseif ($file == 'fr') {
                    $language_order[] = 'French';
                } elseif ($file == 'fur') {
                    $language_order[] = 'Furlan';
                } elseif ($file == 'gr') {
                    $language_order[] = 'Greek';
                } elseif ($file == 'he') {
                    $language_order[] = 'Hebrew';
                } elseif ($file == 'id') {
                    $language_order[] = 'Indonesian';
                } elseif ($file == 'hu') {
                    $language_order[] = 'Magyar';
                } elseif ($file == 'it') {
                    $language_order[] = 'Italiano';
                } elseif ($file == 'es_mx') {
                    $language_order[] = 'Mexicano';
                } elseif ($file == 'nl') {
                    $language_order[] = 'Nederlands';
                } elseif ($file == 'no') {
                    $language_order[] = 'Norsk';
                } elseif ($file == 'pl') {
                    $language_order[] = 'Polish';
                } elseif ($file == 'pt') {
                    $language_order[] = 'Portuguese';
                } elseif ($file == 'ro') {
                    $language_order[] = 'Romanian';
                } elseif ($file == 'ru') {
                    $language_order[] = 'Russian';
                } elseif ($file == 'sk') {
                    $language_order[] = 'Slovensky';
                } elseif ($file == 'sv') {
                    $language_order[] = 'Swedish';
                } elseif ($file == 'tr') {
                    $language_order[] = 'Turkish';
                } else {
                    $language_order[] = $file;
                }
            }
        }

        closedir($language_folder);
        // *** Order language array by name of language ***
        array_multisort($language_order, $language_file);

        // *** Save choice of language, check if file exists, ONLY save an existing language file ***
        if (isset($_GET["language"]) && in_array($_GET["language"], $language_file)) {
            $_SESSION["language_humo"] = $_GET["language"];
        }

        // *** Save choice of language, check if file exists, ONLY save an existing language file ***
        if (isset($_GET["language_choice"]) && in_array($_GET["language_choice"], $language_file)) {
            $_SESSION["save_language_admin"] = $_GET["language_choice"];
        }

        return $language_file;
    }
}
