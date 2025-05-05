<?php

/**
 * THIS FILE IS MADE BY Huub Mons
 * IT IS PART OF THE HuMo-genealogy program.
 * 
 * jan 2014: updated family tree texts.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// *** Selected family tree ***
$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=" . $trees['tree_id']);
$data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
?>

<h1 class="center"><?= __('Family tree administration'); ?></h1>

<?php if (isset($_GET['remove_tree']) && is_numeric($_GET['remove_tree'])) { ?>
    <div class="alert alert-danger">
        <b><?= __('Selected:'); ?> <?= $_GET['treetext_name']; ?></b>
        <?= __('Are you sure you want to remove this tree <b>AND all its statistics</b>?'); ?>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $_GET['remove_tree']; ?>">
            <input type="submit" name="remove_tree2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php } ?>

<div class="row me-1">
    <div class="col-auto">
        <label for="tree" class="col-form-label">
            <?= __('Family tree'); ?>:
        </label>
    </div>

    <div class="col-3">
        <?= select_tree($dbh, 'tree', $trees['tree_id']); ?>
    </div>
</div>

<ul class="nav nav-tabs mt-1">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($trees['menu_tab'] == 'tree_main') echo 'active'; ?>" href="index.php?page=tree&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Family tree administration'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($trees['menu_tab'] == 'tree_data') echo 'active'; ?>" href="index.php?page=tree&amp;menu_admin=tree_data&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Family tree data'); ?></a>
    </li>
    <li class="nav-item me-1">
        <?php /*
        <a class="nav-link genealogy_nav-link <?php if ($trees['menu_tab'] == 'tree_gedcom') echo 'active'; ?>" href="index.php?page=tree&amp;menu_admin=tree_gedcom&amp;tree_id=<?= $trees['tree_id']; ?>&amp;tree_prefix=<?= $data2Db->tree_prefix; ?>"><?= __('Import Gedcom file'); ?></a>
        */ ?>
        <a class="nav-link genealogy_nav-link <?php if ($trees['menu_tab'] == 'tree_gedcom') echo 'active'; ?>" href="index.php?page=tree&amp;menu_admin=tree_gedcom&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Import Gedcom file'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($trees['menu_tab'] == 'tree_text') echo 'active'; ?>" href="index.php?page=tree&amp;menu_admin=tree_text&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Family tree texts (per language)'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($trees['menu_tab'] == 'tree_merge') echo 'active'; ?>" href="index.php?page=tree&amp;menu_admin=tree_merge&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Merge Data'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="background-color:white; height:500px; padding:10px;">
    <?php
    // *** Show main tree screen ***
    if ($trees['menu_tab'] == 'tree_main') {
        include(__DIR__ . '/tree_admin.php');
    }
    // *** Import GEDCOM file ***
    elseif ($trees['menu_tab'] == 'tree_gedcom') {
        include(__DIR__ . '/gedcom_import.php');
    }
    // *** Show tree data ***
    elseif ($trees['menu_tab'] == 'tree_data') {
        include(__DIR__ . '/tree_data.php');
    }
    // *** Show tree text ***
    elseif ($trees['menu_tab'] == 'tree_text') {
        include(__DIR__ . '/tree_text.php');
    }
    // *** Show merge screen ***
    elseif ($trees['menu_tab'] == 'tree_merge') {
        include(__DIR__ . '/tree_merge.php');
    }
    ?>
</div>