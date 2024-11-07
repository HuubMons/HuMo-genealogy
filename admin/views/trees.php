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

$tree_id = $trees['tree_id']; // TODO for now use old variable (used in tab scripts: tree_admin.php, tree_data.php, etc.)
$language_tree = $trees['language']; // TODO for now use old variable (used in tab scripts)
$menu_admin = $trees['menu_tab']; // TODO for now use old variable (used in tab scripts)



// ******************************************
// *** Show texts of selected family tree ***
// ******************************************

$data2sql = $dbh->query("SELECT * FROM humo_tree_texts WHERE treetext_tree_id='" . $trees['tree_id'] . "' AND treetext_language='" . $language_tree . "'");
$data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
if ($data2Db) {
    $treetext_id = $data2Db->treetext_id;
    $treetext_name = $data2Db->treetext_name;
    $treetext_mainmenu_text = $data2Db->treetext_mainmenu_text;
    $treetext_mainmenu_source = $data2Db->treetext_mainmenu_source;
    $treetext_family_top = $data2Db->treetext_family_top;
    $treetext_family_footer = $data2Db->treetext_family_footer;
} else {
    $treetext_name = __('NO NAME');
    $treetext_mainmenu_text = '';
    $treetext_mainmenu_source = '';
    //$treetext_family_top='Family page';
    $treetext_family_top = '';
    $treetext_family_footer = '';
}

// *** Select family tree ***
$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
$tree_search_result = $dbh->query($tree_search_sql);
?>

<h1 class="center"><?= __('Family tree administration'); ?></h1>

<?php if (isset($_GET['remove_tree']) && is_numeric($_GET['remove_tree'])) { ?>
    <div class="alert alert-danger">
        <b><?= __('Selected:'); ?> <?= $_GET['treetext_name']; ?></b>
        <?= __('Are you sure you want to remove this tree <b>AND all its statistics</b>?'); ?>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="tree_id" value="<?= $_GET['remove_tree']; ?>">
            <input type="submit" name="remove_tree2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php }; ?>

<div class="row me-1">
    <div class="col-auto">
        <label for="tree" class="col-form-label">
            <?= __('Family tree'); ?>:
        </label>
    </div>

    <div class="col-3">
        <form method="POST" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <select size="1" name="tree_id" class="form-select form-select-sm" onChange="this.form.submit();">
                <?php
                while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                    $selected = '';
                    if ($tree_searchDb->tree_id == $trees['tree_id']) {
                        $selected = ' selected';
                    }
                    $treetext = show_tree_text($tree_searchDb->tree_id, $selected_language);
                ?>
                    <option value="<?= $tree_searchDb->tree_id; ?>" <?= $selected; ?>>
                        <?= @$treetext['name']; ?>
                    </option>
                <?php } ?>
            </select>
        </form>
    </div>
</div>

<?php
// *** Family trees administration menu ***
$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=" . $trees['tree_id']);
$data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
// TODO: check if tree_prefix is still needed in GEDCOM link.
?>

<ul class="nav nav-tabs mt-1">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_admin == 'tree_main') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Family tree administration'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_admin == 'tree_data') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_admin=tree_data&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Family tree data'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_admin == 'tree_gedcom') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_admin=tree_gedcom&amp;tree_id=<?= $trees['tree_id']; ?> '&amp;tree_prefix=<?= $data2Db->tree_prefix; ?>&amp;step1=read_gedcom"><?= __('Import Gedcom file'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_admin == 'tree_text') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_admin=tree_text&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Family tree texts (per language)'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($menu_admin == 'tree_merge') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_admin=tree_merge&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Merge Data'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="background-color:white; height:500px; padding:10px;">
    <?php
    // *** Show main tree screen ***
    if (isset($menu_admin) && $menu_admin == 'tree_main') {
        include(__DIR__ . '/tree_admin.php');
    }
    // *** Import GEDCOM file ***
    if (isset($menu_admin) && $menu_admin == 'tree_gedcom') {
        include(__DIR__ . '/gedcom.php');
    }

    // ********************************************************************************
    // *** Show selected family tree                                                ***
    // ********************************************************************************
    $data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=" . $trees['tree_id']);
    $data2Db = $data2sql->fetch(PDO::FETCH_OBJ);

    // *** Show tree data ***
    if ($menu_admin == 'tree_data') {
        include(__DIR__ . '/tree_data.php');
    }
    // *** Show tree text ***
    if ($menu_admin == 'tree_text') {
        include(__DIR__ . '/tree_text.php');
    }
    // *** Show merge screen ***
    if ($menu_admin == 'tree_merge') {
        include(__DIR__ . '/tree_merge.php');
    }
    ?>
</div>