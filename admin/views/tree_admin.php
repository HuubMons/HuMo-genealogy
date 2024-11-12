<br>
<?= __('Administration of the family tree(s), i.e. the name can be changed here, and trees can be added or removed.'); ?><br>

<table class="table">
    <thead class="table-primary">
        <tr>
            <th><?= __('Order'); ?></th>
            <th><?= __('Name of family tree'); ?></th>
            <th><?= __('Family tree data'); ?></th>
            <th><?= __('Remove'); ?></th>
        </tr>

        <tr>
            <td></td>
            <td>
                <div class="row">
                    <div class="col-md-3">
                        <a href="index.php?page=tree&amp;language_tree=default&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Default'); ?></a>
                    </div>

                    <div class="col-md-auto ms-2">
                        <?= show_country_flags($trees['language2'], '../', 'language_tree', $trees['language_path']); ?>
                    </div>
                </div>
            </td>
            <td></td>
            <td></td>
        </tr>
    </thead>

    <?php
    $datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
    if ($datasql) {
        $count_lines = $datasql->rowCount();
        while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
    ?>
            <tr <?= $dataDb->tree_id == $trees['tree_id'] ? 'class="table-active"' : ''; ?>>
                <td nowrap>
                    <?= $dataDb->tree_order < 10 ? '0' : ''; ?><?= $dataDb->tree_order; ?>
                    <?php if ($dataDb->tree_order != '1') { ?>
                        <a href="index.php?page=tree&amp;up=1&amp;tree_order=<?= $dataDb->tree_order; ?>&amp;id=<?= $dataDb->tree_id; ?>">
                            <img src="images/arrow_up.gif" border="0" alt="up">
                        </a>
                    <?php
                    }
                    if ($dataDb->tree_order != $count_lines) {
                    ?>
                        <a href="index.php?page=tree&amp;down=1&amp;tree_order=<?= $dataDb->tree_order; ?>&amp;id=<?= $dataDb->tree_id; ?>">
                            <img src="images/arrow_down.gif" border="0" alt="down">
                        </a>
                    <?php } ?>
                </td>

                <td>
                    <?php
                    // *** Show/ Change family tree name ***
                    $treetext = show_tree_text($dataDb->tree_id, $trees['language']);
                    if ($dataDb->tree_prefix == 'EMPTY') {
                    ?>
                        * <?= __('EMPTY LINE'); ?> *
                    <?php } else { ?>
                        <a href="index.php?page=tree&amp;menu_admin=tree_text&amp;tree_id=<?= $dataDb->tree_id; ?>">
                            <img src="images/edit.jpg" title="edit" alt="edit">
                        </a> <?= $treetext['name']; ?>
                    <?php } ?>
                </td>

                <td>
                    <?php if ($dataDb->tree_prefix != 'EMPTY') { ?>
                        <a href="index.php?page=tree&amp;menu_admin=tree_gedcom&amp;tree_id=<?= $dataDb->tree_id; ?>&tree_prefix=<?= $dataDb->tree_prefix; ?>&step1=read_gedcom">
                            <img src="images/import.jpg" title="gedcom import" alt="gedcom import">
                        </a>
                    <?php
                    }

                    if ($dataDb->tree_prefix == 'EMPTY') {
                        // *** Empty line, don't show any text ***
                    } elseif ($dataDb->tree_persons > 0) {
                    ?>
                        <font color="#00FF00"><b><?= __('OK'); ?></b></font>

                        <font size=-1><?= show_tree_date($dataDb->tree_date); ?>: <?= $dataDb->tree_persons; ?> <?= __('persons'); ?>, <?= $dataDb->tree_families; ?> <?= __('families'); ?></font>
                    <?php } else { ?>
                        <b><?= __('This tree does not yet contain any data or has not been imported properly!'); ?></b>
                    <?php } ?>
                </td>

                <td>
                    <?php
                    // *** If there is only one family tree, prevent it from being removed ***
                    if ($trees['count_trees'] > 1 || $dataDb->tree_prefix == 'EMPTY') {
                    ?>
                        <a href="index.php?page=tree&amp;remove_tree=<?= $dataDb->tree_id; ?>&amp;treetext_name=<?= $treetext['name']; ?>">
                            <img src="images/button_drop.png" alt="<?= __('Remove tree'); ?>" border="0">
                        </a>
                    <?php } ?>
                </td>
            </tr>
    <?php
        }
    }
    ?>
</table>

<form method="post" action="index.php" class="mb-2">
    <input type="hidden" name="page" value="tree">
    <input type="submit" name="add_tree_data" value="<?= __('Add family tree'); ?>" class="btn btn-sm btn-success">
</form>

<form method="post" action="index.php">
    <input type="hidden" name="page" value="tree">
    <input type="submit" name="add_tree_data_empty" value="<?= __('Add empty line'); ?>" class="btn btn-sm btn-success">
    <?= __('Add empty line in list of family trees'); ?>
</form>

<form method="post" action="index.php" style="display : inline;">
    <input type="hidden" name="page" value="tree">

    <br>
    <div class="row mb-2">
        <div class="col-md-auto">
            <label for="tree_collation" class="col-form-label"><?= __('Collation'); ?></label>
        </div>

        <div class="col-md-auto">
            <select size="1" name="tree_collation" class="form-select form-select-sm">
                <option value="utf8_general_ci">utf8_general_ci (default)</option>
                <option value="utf8_swedish_ci" <?= $trees['collation'] == 'utf8_swedish_ci' || $trees['collation'] == 'utf8mb3_swedish_ci' ? 'selected' : ''; ?>>utf8_swedish_ci</option>
                <option value="utf8_danish_ci" <?= $trees['collation'] == 'utf8_danish_ci' || $trees['collation'] == 'utf8mb3_danish_ci' ? 'selected' : ''; ?>>utf8_danish_ci</option>
            </select>
        </div>

        <div class="col-md-auto">
            <input type="submit" name="change_collation" value="OK" class="btn btn-sm btn-secondary">
        </div>
    </div>
</form>