<?php
/*
 * Statistics
 * First version: René Janssen.
 * Updated by: Huub.
 *
 * April 2015, Huub: added tab menu, and Yossi's frequently firstnames and surnames pages.
 */

// *** Standard function for names ***
include_once(__DIR__ . "/../include/person_cls.php");
include_once(__DIR__ . "/../include/language_date.php");
include_once(__DIR__ . "/../include/date_place.php");
include_once(__DIR__ . "/../include/calculate_age_cls.php");

if ($humo_option["url_rewrite"] == "j") {
    $path = 'statistics';
    $path2 = 'statistics?';
} else {
    $path = 'index.php?page=statistics';
    $path2 = 'index.php?page=statistics&amp;';
}

// *** Get general data from family tree ***
$dataDb = $db_functions->get_tree($tree_prefix_quoted);

$tree_date = $dataDb->tree_date;
$month = ''; // *** empty date ***
if (substr($tree_date, 5, 2) === '01') {
    $month = ' ' . __('jan') . ' ';
}
if (substr($tree_date, 5, 2) === '02') {
    $month = ' ' . __('feb') . ' ';
}
if (substr($tree_date, 5, 2) === '03') {
    $month = ' ' . __('mar') . ' ';
}
if (substr($tree_date, 5, 2) === '04') {
    $month = ' ' . __('apr') . ' ';
}
if (substr($tree_date, 5, 2) === '05') {
    $month = ' ' . __('may') . ' ';
}
if (substr($tree_date, 5, 2) === '06') {
    $month = ' ' . __('jun') . ' ';
}
if (substr($tree_date, 5, 2) === '07') {
    $month = ' ' . __('jul') . ' ';
}
if (substr($tree_date, 5, 2) === '08') {
    $month = ' ' . __('aug') . ' ';
}
if (substr($tree_date, 5, 2) === '09') {
    $month = ' ' . __('sep') . ' ';
}
if (substr($tree_date, 5, 2) === '10') {
    $month = ' ' . __('oct') . ' ';
}
if (substr($tree_date, 5, 2) === '11') {
    $month = ' ' . __('nov') . ' ';
}
if (substr($tree_date, 5, 2) === '12') {
    $month = ' ' . __('dec') . ' ';
}
$tree_date = substr($tree_date, 8, 2) . $month . substr($tree_date, 0, 4);

// *** Tab menu ***
$menu_tab = 'stats_tree';
if (isset($_GET['menu_tab']) && $_GET['menu_tab'] == 'stats_tree') {
    $menu_tab = 'stats_tree';
}
if (isset($_GET['menu_tab']) && $_GET['menu_tab'] == 'stats_persons') {
    $menu_tab = 'stats_persons';
}
if (isset($_GET['menu_tab']) && $_GET['menu_tab'] == 'stats_surnames') {
    $menu_tab = 'stats_surnames';
}
if (isset($_GET['menu_tab']) && $_GET['menu_tab'] == 'stats_firstnames') {
    $menu_tab = 'stats_firstnames';
}
?>

<ul class="nav nav-tabs mt-1">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_tab == 'stats_tree') echo 'active'; ?>" href="<?= $path2; ?>tree_id=<?= $tree_id; ?>"><?= __('Family tree'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_tab == 'stats_persons') echo 'active'; ?>" href="<?= $path2; ?>menu_tab=stats_persons&amp;tree_id=<?= $tree_id; ?>"><?= __('Persons'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_tab == 'stats_surnames') echo 'active'; ?>" href="<?= $path2; ?>menu_tab=stats_surnames&amp;tree_id=<?= $tree_id; ?>"><?= __('Frequency of Surnames'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_tab == 'stats_firstnames') echo 'active'; ?>" href="<?= $path2; ?>menu_tab=stats_firstnames&amp;tree_id=<?= $tree_id; ?>"><?= __('Frequency of First Names'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div id="statistics_screen">
    <?php
    // *** Show tree statistics ***
    if ($menu_tab === 'stats_tree') {
        include_once(__DIR__ . '/stats_tree.php');
    }

    // *** Show persons statistics ***
    if ($menu_tab === 'stats_persons') {
        include_once(__DIR__ . '/stats_persons.php');
    }

    // *** Show frequent surnames ***
    if ($menu_tab === 'stats_surnames') {
        include_once(__DIR__ . '/stats_surnames.php');
    }

    // *** Show frequent firstnames ***
    if ($menu_tab === 'stats_firstnames') {
        include_once(__DIR__ . '/stats_firstnames.php');
    }
    ?>
</div>