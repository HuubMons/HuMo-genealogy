<?php
function select_tree($dbh, $page, $tree_id, $menu_tab = '')
{
    global $group_edit_trees, $group_administrator, $selected_language;

    // *** Select family tree ***
    $tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
    $tree_search_result = $dbh->query($tree_search_sql);
?>
    <form method="POST" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="<?= $page; ?>">

        <?php if ($menu_tab) { ?>
            <input type="hidden" name="menu_tab" value="<?= $menu_tab; ?>">
        <?php } ?>

        <select size="1" name="tree_id" onChange="this.form.submit();" class="form-select form-select-sm">
            <option value=""><?= __('Select a family tree:'); ?></option>
            <?php
            while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                $edit_tree_array = explode(";", $group_edit_trees);
                // *** Administrator can always edit in all family trees ***
                if ($group_administrator == 'j' || in_array($tree_searchDb->tree_id, $edit_tree_array)) {
                    $selected = '';
                    if (isset($tree_id) && $tree_searchDb->tree_id == $tree_id) {
                        $selected = ' selected';
                    }
                    $treetext = show_tree_text($tree_searchDb->tree_id, $selected_language);
            ?>
                    <option value="<?= $tree_searchDb->tree_id; ?>" <?= $selected; ?>><?= $treetext['name']; ?></option>
            <?php
                }
            }
            ?>
        </select>
    </form>
<?php
}
