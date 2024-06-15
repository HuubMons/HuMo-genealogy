<?php
/*
 * Statistics
 * First version: René Janssen.
 * Updated by: Huub.
 *
 * April 2015, Huub: added tab menu, and Yossi's frequently firstnames and surnames pages.
 */

if ($humo_option["url_rewrite"] == "j") {
    $path = 'statistics';
    $path2 = 'statistics?';
} else {
    $path = 'index.php?page=statistics';
    $path2 = 'index.php?page=statistics&amp;';
}
?>

<ul class="nav nav-tabs mt-1">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $statistics["menu_tab"] == 'stats_tree' ? 'active' : ''; ?>" href="<?= $path2; ?>tree_id=<?= $tree_id; ?>"><?= __('Family tree'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $statistics["menu_tab"] == 'stats_persons' ? 'active' : ''; ?>" href="<?= $path2; ?>menu_tab=stats_persons&amp;tree_id=<?= $tree_id; ?>"><?= __('Persons'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $statistics["menu_tab"] == 'stats_surnames' ? 'active' : ''; ?>" href="<?= $path2; ?>menu_tab=stats_surnames&amp;tree_id=<?= $tree_id; ?>"><?= __('Frequency of Surnames'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $statistics["menu_tab"] == 'stats_firstnames' ? 'active' : ''; ?>" href="<?= $path2; ?>menu_tab=stats_firstnames&amp;tree_id=<?= $tree_id; ?>"><?= __('Frequency of First Names'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div id="statistics_screen">
    <?php
    // *** Show tree statistics ***
    if ($statistics["menu_tab"] === 'stats_tree') {
        include_once(__DIR__ . '/stats_tree.php');
    }

    // *** Show persons statistics ***
    if ($statistics["menu_tab"] === 'stats_persons') {
        include_once(__DIR__ . '/stats_persons.php');
    }

    // *** Show frequent surnames ***
    if ($statistics["menu_tab"] === 'stats_surnames') {
        include_once(__DIR__ . '/stats_surnames.php');
    }

    // *** Show frequent firstnames ***
    if ($statistics["menu_tab"] === 'stats_firstnames') {
        include_once(__DIR__ . '/stats_firstnames.php');
    }
    ?>
</div>