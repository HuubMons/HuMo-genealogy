<?php

/**
 * Rename places.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}



// TEMPORARY CONTROLLER HERE:
include_once(__DIR__ . "/../include/editor_cls.php");
$editor_cls = new editor_cls;

include_once(__DIR__ . "/../include/select_tree.php");

require_once  __DIR__ . "/../models/edit_rename_place.php";
$renamePlaceModel = new RenamePlaceModel($dbh);
$renamePlaceModel->update_place($dbh, $tree_id, $editor_cls);
$place['result'] = $renamePlaceModel->get_query($dbh, $tree_id);
$place['select'] = $renamePlaceModel->get_place_select();



$phpself = 'index.php';
$field_text_large = 'style="height: 100px; width:550px"';
?>

<h1 class="center"><?= __('Rename places'); ?></h1>

<div class="p-3 my-md-2 genealogy_search container-md">
    <div class="row">
        <div class="col-auto">
            <label for="tree" class="col-form-label">
                <?= __('Family tree'); ?>:
            </label>
        </div>
        <div class="col-auto">
            <?= select_tree($dbh, $page, $tree_id); ?>
        </div>

        <div class="col-auto">
            <label for="count_places" class="col-form-label">
                <?= $place['result']->rowCount(); ?> <?= __('Places'); ?>.
            </label>
        </div>

        <div class="col-auto">
            <label for="location" class="col-form-label">
                <?= __('Select location'); ?>
            </label>
        </div>
        <div class="col-3">
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <select size="1" name="place_select" class="form-select form-select-sm" onChange="this.form.submit();">
                    <?php
                    while ($person = $place['result']->fetch(PDO::FETCH_OBJ)) {
                        if ($person->place_edit != '') {
                    ?>
                            <option value="<?= $person->place_edit; ?>" <?= $place['select'] == $person->place_edit ? ' selected' : ''; ?>><?= $person->place_edit; ?></option>
                    <?php
                        }
                    }
                    ?>
                </select>
            </form>
        </div>

        <!--
        <div class="col-auto">
            <input type="submit" name="dummy8" value="<?= __('Select'); ?>" class="btn btn-sm btn-success">
        </div>
                -->
    </div>
</div>

<!-- Change selected place -->
<?php if ($place['select']) { ?>
    <form method="POST" action="<?= $phpself; ?>" class="mt-4">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="hidden" name="place_old" value="<?= $place['select']; ?>">

        <div class="row mb-2">
            <div class="col-2"></div>

            <div class="col-2">
                <label for="change_location" class="col-form-label">
                    <?= __('Change location'); ?>:
                </label>
            </div>

            <div class="col-3">
                <input type="text" name="place_new" value="<?= $place['select']; ?>" size="60" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row">
            <div class="col-4"></div>
            <div class="col-2">
                <input type="submit" name="place_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>
    </form>
<?php
}
