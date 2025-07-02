<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<h1 class="center"><?= __('Settings'); ?></h1>

<?php
// *** Re-read variables after changing them ***
// *** Don't use include_once! Otherwise the old value will be shown ***
include_once(__DIR__ . "/../../include/generalSettings.php");
$generalSettings = new GeneralSettings();
//$user = $generalSettings->get_user_settings($dbh);
$humo_option = $generalSettings->get_humo_option($dbh);


// *** Read languages in language array ***
$arr_count = 0;
$arr_count_admin = 0;
$folder = opendir('../languages/');
while (false !== ($file = readdir($folder))) {
    if (strlen($file) < 6 && $file !== '.' && $file !== '..') {
        // *** Get language name ***
        include(__DIR__ . "/../../languages/" . $file . "/language_data.php");
        $langs[$arr_count][0] = $language["name"];
        $langs[$arr_count][1] = $file;
        $arr_count++;
        if (file_exists('../languages/' . $file . '/' . $file . '.mo')) {
            $langs_admin[$arr_count_admin][0] = $language["name"];
            $langs_admin[$arr_count_admin][1] = $file;
            $arr_count_admin++;
        }
    }
}
closedir($folder);
?>

<ul class="nav nav-tabs">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $settings['menu_tab'] == 'settings' ? 'active' : ''; ?>" href="index.php?page=settings">
            <?= __('Settings'); ?>
        </a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $settings['menu_tab'] == 'settings_homepage' ? 'active' : ''; ?>" href="index.php?page=settings&amp;menu_admin=settings_homepage">
            <?= __('Homepage'); ?>
        </a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $settings['menu_tab'] == 'settings_special' ? 'active' : ''; ?>" href="index.php?page=settings&amp;menu_admin=settings_special">
            <?= __('Special settings'); ?>
        </a>
    </li>
</ul>

<div style="background-color:white;">
    <?php
    // *** Show settings ***
    if ($settings['menu_tab'] == 'settings') {
        include(__DIR__ . '/settings.php');
    }

    // *** Show homepage settings ***
    if ($settings['menu_tab'] == 'settings_homepage') {
        include(__DIR__ . '/settings_homepage.php');
    }

    // *** Show special settings ***
    if ($settings['menu_tab'] == 'settings_special') {
        include(__DIR__ . '/settings_special.php');
    }
    ?>
</div>