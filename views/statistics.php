<?php

/**
 * Statistics
 * First version: René Janssen.
 * Updated by: Huub.
 *
 * April 2015, Huub: added tab menu, and Yossi's frequently firstnames and surnames pages.
 */

$processLinks = new \Genealogy\Include\ProcessLinks();
$path = $processLinks->get_link($uri_path, 'statistics', $tree_id, true);
?>

<ul class="nav nav-tabs mt-1">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $statistics["menu_tab"] == 'stats_tree' ? 'active' : ''; ?>" href="<?= $HtmlEscaper->escHtml($path . 'menu_tab=stats_tree'); ?>"><?= $HtmlEscaper->escHtml(__('Family tree')); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $statistics["menu_tab"] == 'stats_persons' ? 'active' : ''; ?>" href="<?= $HtmlEscaper->escHtml($path . 'menu_tab=stats_persons'); ?>"><?= $HtmlEscaper->escHtml(__('Persons')); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $statistics["menu_tab"] == 'stats_surnames' ? 'active' : ''; ?>" href="<?= $HtmlEscaper->escHtml($path . 'menu_tab=stats_surnames'); ?>"><?= $HtmlEscaper->escHtml(__('Frequency of Surnames')); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $statistics["menu_tab"] == 'stats_firstnames' ? 'active' : ''; ?>" href="<?= $HtmlEscaper->escHtml($path . 'menu_tab=stats_firstnames'); ?>"><?= $HtmlEscaper->escHtml(__('Frequency of First Names')); ?></a>
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