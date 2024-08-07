<?php
/*
Original Google Maps script: Yossi.
April 2022 Huub: added OpenStreetMap.
July 2024 Huub: added Controller and Model scripts.
*/

// TODO: is it still needed to store tree/birth/death items in location table? Maybe other places will be added later.

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// *** Show tabs ***
$menu = 'worldmap';
if (isset($_POST['menu'])) {
    $menu = $_POST['menu'];
}
if (isset($_GET['menu'])) {
    $menu = $_GET['menu'];
}

$select_item_homepage = '';
if ($menu == 'worldmap') {
    $select_item_homepage = ' pageTab-active';
}
$select_item_special = '';
if ($menu == 'locations') {
    $select_item_special = ' pageTab-active';
}
$select_item_settings = '';
if ($menu == 'settings') {
    $select_item_settings = ' pageTab-active';
}
?>

<h1 align="center"><?= __('World map administration'); ?></h1>

<ul class="nav nav-tabs">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu == 'worldmap') echo 'active'; ?>" href="index.php?page=<?= $page; ?>"><?= __('World map'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu == 'locations') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu=locations"><?= __('Edit geolocation database'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu == 'settings') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu=settings"><?= __('Settings'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;" class="container-lg">
    <?php
    // *** Show homepage settings ***
    if ($menu == 'worldmap') {
        include(__DIR__ . '/maps_worldmap.php');
    }

    // *** Show special settings ***
    if ($menu == 'locations') {
        include(__DIR__ . '/maps_locations.php');
    }

    // *** Show settings ***
    if ($menu == 'settings') {
        include(__DIR__ . '/maps_settings.php');
    }
?>
</div>