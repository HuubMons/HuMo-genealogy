<?php
$showTreeDate = new \Genealogy\Include\ShowTreeDate();
?>

<br>
<?= __('Administration of the family tree(s), i.e. the name can be changed here, and trees can be added or removed.'); ?><br>

<ul class="list-group">
    <li class="list-group-item">
        <div class="row bg-primary-subtle p-3 mt-2">
            <div class="col-md-1">
                <b><?= __('Order'); ?></b>
            </div>

            <div class="col-md-5">
                <div class="row">
                    <div class="col-md-auto">
                        <b><?= __('Name of family tree'); ?></b>
                    </div>

                    <div class="col-md-auto">
                        <a href="index.php?page=tree&amp;language_tree=default&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Default'); ?></a>
                    </div>

                    <div class="col-md-auto">
                        <?= show_country_flags($trees['language2'], '../', 'language_tree', $trees['language_path']); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <b><?= __('Family tree data'); ?></b>
            </div>
        </div>
    </li>
</ul>

<ul id="sortable_trees" class="sortable_trees list-group">
    <?php
    $datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
    if ($datasql) {
        $count_lines = $datasql->rowCount();
        while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
            $treetext = $showTreeText->show_tree_text($dataDb->tree_id, $trees['language']);
    ?>
            <li class="list-group-item <?= $dataDb->tree_id == $trees['tree_id'] ? 'list-group-item-secondary' : ''; ?>">
                <div class="row">
                    <div class="col-md-1">
                        <span style="cursor:move;" id="<?= $dataDb->tree_id; ?>" class="handle me-4">
                            <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                        </span>

                        <!-- If there is only one family tree, prevent it from being removed -->
                        <?php if ($trees['count_trees'] > 1 || $dataDb->tree_prefix == 'EMPTY') { ?>
                            <a href="index.php?page=tree&amp;remove_tree=<?= $dataDb->tree_id; ?>&amp;treetext_name=<?= $treetext['name']; ?>">
                                <img src="images/button_drop.png" alt="<?= __('Remove tree'); ?>" border="0">
                            </a>
                        <?php } ?>
                    </div>

                    <div class="col-md-5">
                        <?php if ($dataDb->tree_prefix == 'EMPTY') { ?>
                            * <?= __('EMPTY LINE'); ?> *
                        <?php } else { ?>
                            <a href="index.php?page=tree&amp;menu_admin=tree_text&amp;tree_id=<?= $dataDb->tree_id; ?>">
                                <img src="images/edit.jpg" title="edit" alt="edit">
                            </a>
                            <?= $treetext['name']; ?>
                        <?php } ?>
                    </div>

                    <div class="col-md-6">
                        <?php if ($dataDb->tree_prefix != 'EMPTY') { ?>
                            <?php /*
                            <a href="index.php?page=tree&amp;menu_admin=tree_gedcom&amp;tree_id=<?= $dataDb->tree_id; ?>&tree_prefix=<?= $dataDb->tree_prefix; ?>&step1=read_gedcom">
                            */ ?>
                            <a href="index.php?page=tree&amp;menu_admin=tree_gedcom&amp;tree_id=<?= $dataDb->tree_id; ?>&amp;step1=read_gedcom">
                                <img src="images/import.jpg" title="gedcom import" alt="gedcom import">
                            </a>
                        <?php
                        }

                        if ($dataDb->tree_prefix == 'EMPTY') {
                            // *** Empty line, don't show any text ***
                        } elseif ($dataDb->tree_persons > 0) {
                        ?>
                            <font color="#00FF00"><b><?= __('OK'); ?></b></font>

                            <font size=-1><?= $showTreeDate->show_tree_date($dataDb->tree_date); ?>: <?= $dataDb->tree_persons; ?> <?= __('persons'); ?>, <?= $dataDb->tree_families; ?> <?= __('families'); ?></font>
                        <?php } else { ?>
                            <b><?= __('This tree does not yet contain any data or has not been imported properly!'); ?></b>
                        <?php } ?>
                    </div>
                </div>
            </li>

        <?php } ?>
    <?php } ?>
</ul>

<!-- Order items using drag and drop using jquery and jqueryui -->
<script>
    $('#sortable_trees').sortable({
        handle: '.handle'
    }).bind('sortupdate', function() {
        var orderstring = "";
        var order_arr = document.getElementsByClassName("handle");
        for (var z = 0; z < order_arr.length; z++) {
            orderstring = orderstring + order_arr[z].id + ";";
            //document.getElementById('ordernum' + order_arr[z].id).innerHTML = (z + 1);
        }

        orderstring = orderstring.substring(0, orderstring.length - 1);
        $.ajax({
            url: "include/drag.php?drag_kind=trees&order=" + orderstring,
            success: function(data) {},
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    });
</script>

<form method="post" action="index.php" class="my-3">
    <input type="hidden" name="page" value="tree">
    <input type="submit" name="add_tree_data" value="<?= __('Add family tree'); ?>" class="btn btn-sm btn-success">
</form>

<form method="post" action="index.php">
    <input type="hidden" name="page" value="tree">
    <input type="submit" name="add_tree_data_empty" value="<?= __('Add empty line'); ?>" class="btn btn-sm btn-success">
    <?= __('Add empty line in list of family trees'); ?>
</form>

<form method="post" action="index.php" class="mt-3">
    <input type="hidden" name="page" value="tree">

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