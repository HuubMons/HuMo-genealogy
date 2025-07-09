<?php
// *** Default language ***
$selected_language = "en";

// *** Saved default language ***
if (
    isset($humo_option['default_language'])
    and file_exists(__DIR__ . '/' . $humo_option['default_language'] . '/' . $humo_option['default_language'] . '.mo')
) {
    $selected_language = $humo_option['default_language'];
}

// *** Extra check if language exists ***
if (isset($_SESSION["language_humo"]) and file_exists(__DIR__ . '/' . $_SESSION["language_humo"] . '/' . $_SESSION["language_humo"] . '.mo')) {
    $selected_language = $_SESSION["language_humo"];
}

// *** Read language file ***
$language = array();
include_once(__DIR__ . '/' . $selected_language . '/language_data.php');

// *** .mo language text files ***
include_once(__DIR__ . "/gettext.php");
// *** Load ***
$_SESSION["language_selected"] = $selected_language;
Load_default_textdomain();
//Load_textdomain('customer_domain', 'languages/'.$selected_language.'/'.$selected_language.'.mo');
