<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// *** Read theme's ***
$folder = opendir('../styles/');
while (false !== ($file = readdir($folder))) {
    if (substr($file, -4, 4) == '.css') {
        $theme_folder[] = $file;
    }
}
closedir($folder);

if (isset($_POST['save_option'])) {
    // *** Update settings / Language choice ***
    $language_total = '';
    for ($i = 0; $i < count($language_file); $i++) {
        // *** Get language name ***
        if ($language_file[$i] == $humo_option["default_language"] or  $language_file[$i] == $humo_option["default_language_admin"]) {
            // *** Don't hide default languages ***
        } else {
            if (!isset($_POST["$language_file[$i]"])) {
                if ($language_total != '') {
                    $language_total .= ';';
                }
                $language_total .= $language_file[$i];
            }
        }
    }
    $result = $db_functions->update_settings('hide_languages', $language_total);

    // *** Update settings / Theme choice ***
    $theme_total = '';
    for ($i = 0; $i < count($theme_folder); $i++) {
        $theme = $theme_folder[$i];
        $theme = str_replace(".css", "", $theme);

        if (!isset($_POST["$theme"])) {
            if ($theme_total != '') {
                $theme_total .= ';';
            }
            $theme_total .= $theme;
        }
    }
    $result = $db_functions->update_settings('hide_themes', $theme_total);
}

// *** Re-read variables after changing them ***
// *** Don't use include_once! Otherwise the old value will be shown ***
include(__DIR__ . "/../../include/settings_global.php"); //variables

$hide_languages_array = explode(";", $humo_option["hide_languages"]);
?>

<h1 class="center"><?= __('Extensions'); ?></h1>

<form method="post" action="index.php" class="ms-3">
    <input type="hidden" name="page" value="<?= $page; ?>">

    <h2><?= __('Show/ hide languages'); ?></h2>

    <?php
    // *** Language choice ***
    for ($i = 0; $i < count($language_file); $i++) {
        // *** Get language name ***
        include(__DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php');

        $checked = ' checked';
        if (in_array($language_file[$i], $hide_languages_array)) $checked = '';

        $disabled = '';
        if ($language_file[$i] == $humo_option["default_language"]) {
            $disabled = ' disabled';
            $checked = ' checked';
        }
        if ($language_file[$i] == $humo_option["default_language_admin"]) {
            $disabled = ' disabled';
            $checked = ' checked';
        }
    ?>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="<?= $language_file[$i]; ?>" <?= $checked . $disabled; ?>>
            <label class="form-check-label">
                <img src="../languages/<?= $language_file[$i]; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>" style="border:none;"><?= $language["name"]; ?><br>
            </label>
        </div>
    <?php } ?>

    <input type="submit" name="save_option" class="btn btn-sm btn-success" value="<?= __('Change'); ?>">


    <h2 class="mt-2"><?= __('Show/ hide theme\'s'); ?></h2>
    <?php
    $hide_themes_array = explode(";", $humo_option["hide_themes"]);
    for ($i = 0; $i < count($theme_folder); $i++) {
        $theme = $theme_folder[$i];
        $theme = str_replace(".css", "", $theme);
        $checked = ' checked';
        if (in_array($theme, $hide_themes_array)) $checked = '';
    ?>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="<?= $theme; ?>" <?= $checked; ?>>
            <label class="form-check-label">
                <?= $theme; ?><br>
            </label>
        </div>
    <?php } ?>
    <input type="submit" name="save_option" class="btn btn-sm btn-success" value="<?= __('Change'); ?>">
</form>