<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// *** Needed for tab "Check database integrity" ***
$db_functions->set_tree_id($tree_id);
?>

<h1 class="center"><?= __('Family tree data check'); ?></h1>

<div class="row mx-2">
    <div class="col-auto">
        <label for="tree" class="col-form-label">
            <?= __('Choose tree:'); ?>
        </label>
    </div>
    <div class="col-auto">
        <?= $selectTree->select_tree($dbh, 'check', $tree_id); ?>
    </div>
</div>

<ul class="nav nav-tabs">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tree_check['tab'] == 'check') echo 'active'; ?>" href="index.php?page=check&amp;tab=check"><?= __('Family tree data check'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tree_check['tab'] == 'consistency') echo 'active'; ?>" href="index.php?page=check&amp;tab=consistency"><?= __('Check consistency of dates'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tree_check['tab'] == 'invalid') echo 'active'; ?>" href="index.php?page=check&amp;tab=invalid"><?= __('Find invalid dates'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tree_check['tab'] == 'integrity') echo 'active'; ?>" href="index.php?page=check&amp;tab=integrity"><?= __('Check database integrity'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tree_check['tab'] == 'changes') echo 'active'; ?>" href="index.php?page=check&amp;tab=changes"><?= __('View latest changes'); ?></a>
    </li>
</ul>

<div class="container-fluid bg-light pt-3">
    <?php if ($tree_check['tab'] == 'check') { ?>
        <b><?= __('Check consistency of dates'); ?></b><br>
        <?= __('With this option you can check the consistency of the dates in your database.'); ?><br>
        <?= __('For example: birth date after death date, marriage date at age 7, birth date 80 years after mother\'s birth date etc.'); ?><br><br>

        <b><?= __('Check invalid dates'); ?></b><br>
        <?= __('With this option you can check the database for invalid dates. You will be given a link to edit the errors.'); ?><br>
        <?= __('This item checks for impossible dates (such as "31 apr 1920"), future dates, incomplete dates ("3 apr") and invalid GEDCOM date entries.'); ?><br>
        <?= __('Tip for GEDCOM validation (case is irrelevant):'); ?><br>
        <?= __('Only valid month notation: "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"'); ?><br>
        <?= __('Only valid single prefixes: "bef", "aft", "abt", "est", "int", "cal"'); ?><br>
        <?= __('Only valid double prefixes: "from 1898 to 1899", "bet 1850 and 1860"'); ?><br>
        <?= __('Invalid GEDCOM entries: "1877-1879" (-> bet 1877 and 1879), "12 april 2003" (-> 12 apr 2003), "cir 1884" (-> abt 1884), "1845 ?" (abt 1845)'); ?><br><br>

        <b><?= __('Check database integrity'); ?></b><br>
        <?= __('With this option you can check the integrity of the tables in the MySQL database.'); ?><br><br>

        <b><?= __('Latest changes'); ?></b><br>
        <?= __('Here you can view the latest changes that were made to data in your database.'); ?><br><br>
    <?php
    } elseif ($tree_check['tab'] == 'changes') {
        include(__DIR__ . '/tree_check_changes.php');
    } elseif ($tree_check['tab'] == 'integrity') {
        include(__DIR__ . '/tree_check_integrity.php');
    } elseif ($tree_check['tab'] == 'invalid') {
        include(__DIR__ . '/tree_check_invalid.php');
    } elseif ($tree_check['tab'] == 'consistency') {
        include(__DIR__ . '/tree_check_consistency.php');
    }
    ?>
</div>