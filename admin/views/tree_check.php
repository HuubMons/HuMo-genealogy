<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

global $selected_language;

include_once(__DIR__ . "/../../include/language_date.php");

include_once(__DIR__ . "/../include/select_tree.php");

// for rtl direction in tables
$direction = "left";
if ($rtlmarker == "rtl") $direction = "right";

$page = 'check'; // *** Otherwise the direct link to page "Latest changes" doesn't work properly ***

$tab = 'check';
if (isset($_GET['tab'])) {
    $tab = $_GET['tab'];
}
if (isset($_POST['tab'])) {
    $tab = $_POST['tab'];
}

// *** Needed for tab "Check database integrity" ***
$db_functions->set_tree_id($tree_id);
?>

<h1 class="center"><?= __('Family tree data check'); ?></h1>

<div class="row mb-2">
    <div class="col-auto">
        <label for="tree" class="col-form-label">
            <?= __('Choose tree:'); ?>
        </label>
    </div>
    <div class="col-2">
        <?= select_tree($dbh, $page, $tree_id); ?>
    </div>
</div>

<ul class="nav nav-tabs">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'check') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=check"><?= __('Family tree data check'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'consistency') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=consistency"><?= __('Check consistency of dates'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'invalid') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=invalid"><?= __('Find invalid dates'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'integrity') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=integrity"><?= __('Check database integrity'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'changes') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=changes"><?= __('View latest changes'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;"><br>

    <?php if ($tab == 'check') { ?>
        <div class="container" style="text-align:<?= $direction; ?>;"><br>
            <b><?= __('Check consistency of dates'); ?></b><br>
            <?= __('With this option you can check the consistency of the dates in your database. For example: birth date after death date, marriage date at age 7, birth date 80 years after mother\'s birth date etc.'); ?><br>
            <?= __('You can perform the check with all options, or choose only certain options.'); ?><br>
            <?= __('You can also change default settings for the checks to be performed.'); ?><br><br>

            <b><?= __('Check invalid dates'); ?></b><br>
            <?= __('With this option you can check the database for invalid dates. You will be given a link to edit the errors.'); ?><br>
            <?= __('This item checks for impossible dates (such as "31 apr 1920"), future dates, incomplete dates ("3 apr") and invalid GEDCOM date entries.'); ?><br>
            <?= __('Tip for GEDCOM validation (case is irrelevant):'); ?><br>
            <?= __('Only valid month notation: "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"'); ?><br>
            <?= __('Only valid single prefixes: "bef", "aft", "abt", "est", "int", "cal"'); ?><br>
            <?= __('Only valid double prefixes: "from 1898 to 1899", "bet 1850 and 1860"'); ?><br>
            <?= __('Invalid GEDCOM entries: "1877-1879" (-> bet 1877 and 1879), "12 april 2003" (-> 12 apr 2003), "cir 1884" (-> abt 1884), "1845 ?" (abt 1845)'); ?><br><br>

            <b><?= __('Check database integrity'); ?></b><br>
            <?= __('With this option you can check the integrity of the tables in the MySQL database.'); ?><br>
            <?= __('If inconsistencies exist they may lead to people being disconnected from relatives or misplaced.'); ?><br><br>
            <b><?= __('Latest changes'); ?></b><br>
            <?= __('Here you can view the latest changes that were made to data in your database.'); ?><br><br>
        </div>
    <?php
    }

    if ($tab == 'changes') {
        include(__DIR__ . '/tree_check_changes.php');
    }
    if ($tab == 'integrity') {
        include(__DIR__ . '/tree_check_integrity.php');
    }
    if ($tab == 'invalid') {
        include(__DIR__ . '/tree_check_invalid.php');
    }
    if ($tab == 'consistency') {
        include(__DIR__ . '/tree_check_consistency.php');
    }
    ?>
</div>