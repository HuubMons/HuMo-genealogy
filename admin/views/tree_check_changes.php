<h3><?= __('Latest changes'); ?></h3>

<form method="POST" action="index.php">
    <input type="hidden" name="page" value="check">
    <input type="hidden" name="tab" value="changes">

    <!-- <div class="row p-2 mb-3 mx-sm-1"> -->
    <div class="row gy-2 gx-3 align-items-center">
        <div class="col-auto">

            <div class="input-group">
                <label for="editor" class="col-sm-auto col-form-label"><?= __('Select editor:'); ?>&nbsp;</label>
                <select size="1" name="editor" id="editor" class="form-select form-select-sm">
                    <option value=""><?= __('All editors'); ?></option>
                    <?php
                    while ($select_editorDb = $tree_check['list_editors']->fetch(PDO::FETCH_OBJ)) {
                        if ($select_editorDb->user) {
                            $qry = $dbh->query("SELECT * FROM humo_users WHERE user_id='" . $select_editorDb->user . "'");
                            $editorDb = $qry->fetch(PDO::FETCH_OBJ);
                    ?>
                            <option value="<?= $select_editorDb->user; ?>" <?= $select_editorDb->user == $tree_check['editor'] ? 'selected' : ''; ?>>
                                <?= $editorDb->user_name; ?>
                            </option>
                    <?php
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Number of results in list -->
        <div class="col-auto">
            <div class="input-group">
                <label for="limit" class="col-sm-auto col-form-label"><?= __('Results'); ?>:&nbsp;</label>
                <select size="1" name="limit" id="limit" class="form-select form-select-sm">
                    <option value="50">50</option>
                    <option value="100" <?= $tree_check['limit'] == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?= $tree_check['limit'] == 200 ? 'selected' : ''; ?>>200</option>
                    <option value="500" <?= $tree_check['limit'] == 500 ? 'selected' : ''; ?>>500</option>
                </select>
            </div>
        </div>

        <div class="col-auto">
            <input type="checkbox" id="1" name="show_persons" id="show_persons" class="form-check-input" value="1" <?= $tree_check['show_persons'] ? ' checked' : ''; ?>>
            <label class="form-check-label" for="show_persons"><?= __('Persons'); ?></label>

            <input type="checkbox" id="1" name="show_families" id="show_families" class="form-check-input ms-2" value="1" <?= $tree_check['show_families'] ? ' checked' : ''; ?>>
            <label class="form-check-label" for="show_families"><?= __('Families'); ?></label>
        </div>

        <!-- Future options: also select sources, addresses, etc.? -->

        <div class="col-auto">
            <input type="submit" name="last_changes" class="btn btn-sm btn-success" value="<?= __('Select'); ?>">
        </div>
    </div>
</form>

<table class="table my-2">
    <thead class="table-primary">
        <tr>
            <th><?= __('Item'); ?></th>
            <th><?= __('Changed/ Added'); ?></th>
            <th><?= __('When changed'); ?></th>
            <th><?= __('When added'); ?></th>
        </tr>
    </thead>

    <?php
    $counter = count($tree_check['changes']);
    for ($row = 0; $row < $counter; $row++) {
    ?>
        <tr>
            <td><?= $tree_check['changes'][$row][0]; ?></td>
            <td><?= $tree_check['changes'][$row][1]; ?></td>
            <td><?= $tree_check['changes'][$row][2]; ?></td>
            <td><?= $tree_check['changes'][$row][3]; ?></td>
        </tr>
    <?php } ?>
</table>