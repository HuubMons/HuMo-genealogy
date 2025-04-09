<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

include_once(__DIR__ . "/../../include/language_date.php");

$tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
$tree_result = $dbh->query($tree_sql);
?>

<h1 class="center"><?= __('Notes'); ?></h1>

<form method="POST" action="index.php">
    <input type="hidden" name="page" value="notes">

    <div class="p-3 m-2 genealogy_search">
        <div class="row align-items-center">
            <div class="col-auto">
                <label for="tree" class="col-form-label">
                    <?= __('Family tree'); ?>:
                </label>
            </div>

            <div class="col-auto">
                <select size="1" name="tree_id" class="form-select form-select-sm" onChange="this.form.submit();">
                    <?php
                    while ($treeDb = $tree_result->fetch(PDO::FETCH_OBJ)) {
                        $treetext = show_tree_text($treeDb->tree_id, $selected_language);
                        $selected = '';
                        if (isset($tree_id) && $treeDb->tree_id == $tree_id) {
                            $selected = ' selected';
                            // TODO check this variable.
                            $note_tree_id = $treeDb->tree_id;
                            $db_functions->set_tree_id($tree_id);
                        }

                        $note_qry = "SELECT note_id FROM humo_user_notes WHERE note_tree_id='" . $treeDb->tree_id . "'";
                        $note_result = $dbh->query($note_qry);
                        $num_rows = $note_result->rowCount();
                    ?>
                        <option value="<?= $treeDb->tree_id; ?>" <?= $selected; ?>>
                            <?= $treetext['name']; ?> [<?= $num_rows; ?>]
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>

            <div class="col-auto">
                <label for="address" class="col-form-label">
                    <?= __('Results'); ?>:
                </label>
            </div>
            <div class="col-auto">
                <!-- Number of results in list -->
                <select size="1" name="limit" class="form-select form-select-sm">
                    <option value="50">50</option>
                    <option value="100" <?= $notes['limit'] == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?= $notes['limit'] == 200 ? 'selected' : ''; ?>>200</option>
                    <option value="500" <?= $notes['limit'] == 500 ? 'selected' : ''; ?>>500</option>
                </select>
            </div>

            <div class="col-auto">
                <input type="checkbox" name="user_notes" id="user_notes" <?= $notes['user_notes'] ? ' checked' : ''; ?> class="form-check-input">
                <label class="form-check-label" for="user_notes"><?= __('User notes'); ?></label>
            </div>

            <div class="col-auto">
                <input type="checkbox" name="editor_notes" id="editor_notes" <?= $notes['editor_notes'] ? ' checked ' : ''; ?> class="form-check-input">
                <label class="form-check-label" for="editor_notes"><?= __('Editor notes'); ?></label>
            </div>

            <div class="col-auto">
                <input type="submit" name="note_settings" class="btn btn-success btn-sm" value="<?= __('Select'); ?>">
            </div>
        </div>
    </div>
</form>

<?php if (isset($_POST['note_status']) && is_numeric($_POST['note_id']) && $_POST['note_status'] == 'remove') { ?>
    <div class="alert alert-danger">
        <strong><?= __('Are you sure you want to remove this note?'); ?></strong>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="notes">
            <input type="hidden" name="tree" value="<?= $tree_id; ?>">
            <input type="hidden" name="note_id" value="<?= $_POST['note_id']; ?>">
            <input type="submit" name="note_remove" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php
}

if (isset($_POST['note_remove']) && is_numeric($_POST["note_id"])) {
    // *** Delete source ***
    $sql = "DELETE FROM humo_user_notes WHERE note_id='" . safe_text_db($_POST["note_id"]) . "'";
    $result = $dbh->query($sql);
?>
    <div class="alert alert-success">
        <strong><?= __('Note is removed.'); ?></strong>
    </div>
<?php }

// *** Show user added notes ***
// TODO check this line.
if (isset($note_tree_id)) {
    if ($notes['user_notes']) {
        $note_kind = "AND note_kind='user'";
    }
    if ($notes['editor_notes']) {
        $note_kind = "AND note_kind='editor'";
    }
    if ($notes['user_notes'] && $notes['editor_notes']) {
        $note_kind = '';
    }
    $note_qry = "SELECT * FROM humo_user_notes WHERE note_tree_id='" . $note_tree_id . "' " . $note_kind . " LIMIT 0," . $notes['limit'];
    $note_result = $dbh->query($note_qry);
    $num_rows = $note_result->rowCount();
?>

    <?php
    /*
        echo '<tr class="humo_user_notes"><td>';
            if ($num_rows)
            {
                echo '<a href="#humo_user_notes"></a> ';
            }
            echo __('Notes').'</td><td colspan="2">';
            if ($num_rows)
            {
                printf(__('There are %d user added notes.'), $num_rows);
            } else {
                printf(__('There are %d user added notes.'), 0);
            }
        echo '</td></tr>';
        */

    while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) {
        $user_name = $db_functions->get_user_name($noteDb->note_new_user_id);

        $note_status = '';
        if ($noteDb->note_status) {
            $note_status = $noteDb->note_status;
        }
    ?>

        <form method="POST" action="index.php">
            <input type="hidden" name="tree" value="<?= $tree_id; ?>">
            <input type="hidden" name="page" value="notes">
            <input type="hidden" name="note_id" value="<?= $noteDb->note_id; ?>">

            <div class="p-2 m-2 genealogy_search">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <?php if ($noteDb->note_kind == 'user') { ?>
                            <?= __('User note'); ?>
                        <?php } else { ?>
                            <?= __('Editor note'); ?>
                        <?php } ?>
                    </div>
                    <div class="col-md-9">
                        <?php
                        // *** Link: index.php?page=editor&amp;tree_id=2_&amp;person=I313 ***
                        if (substr($noteDb->note_connect_id, 0, 1) === 'F') {
                            // *** Editor note by family ***
                            $find_parent1Db = $db_functions->get_family($noteDb->note_connect_id);
                            if ($find_parent1Db->fam_man != "") {
                        ?>
                                <a href="index.php?page=editor&amp;tree_id=<?= $tree_id; ?>&amp;menu_tab=marriage&amp;person=<?= $find_parent1Db->fam_man; ?>&amp;marriage_nr=<?= $noteDb->note_connect_id; ?>">
                                    <b><?= $noteDb->note_connect_id; ?> <?= $noteDb->note_names; ?></b>
                                </a><br>
                            <?php
                            }
                        } else {
                            // *** Editor note by person ***
                            ?>
                            <a href="index.php?page=editor&amp;tree_id=<?= $tree_id; ?>&amp;menu_tab=person&amp;person=<?= $noteDb->note_connect_id; ?>">
                                <b><?= $noteDb->note_connect_id; ?> <?= $noteDb->note_names; ?></b>
                            </a><br>
                        <?php } ?>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3"></div>

                    <div class="col-md-2">
                        <select size="1" name="note_status" class="form-select form-select-sm">
                            <?php if ($noteDb->note_kind == 'user') { ?>
                                <option value="new"><?= __('New'); ?></option>
                                <option value="approved" <?= $note_status == 'approved' ? ' selected' : ''; ?>><?= __('Approved'); ?></option>
                            <?php } ?>
                            <option value="remove" <?= $note_status == 'remove' ? ' selected' : ''; ?>><?= __('Remove'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="submit" name="submit_button" value="<?= __('Select'); ?>" class="btn btn-success btn-sm">
                    </div>
                </div>

                <?php if ($noteDb->note_kind != 'user') { ?>
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <?= __('Priority'); ?>
                        </div>
                        <div class="col-md-7">
                            <?= __($noteDb->note_priority); ?>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-3">
                            <?= __('Status'); ?>
                        </div>
                        <div class="col-md-7">
                            <?= __($noteDb->note_status); ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="row mb-2">
                    <div class="col-md-3">
                        <?= __('Added by'); ?>
                    </div>
                    <div class="col-md-7">
                        <b><?= $user_name; ?></b> <?= show_datetime($noteDb->note_new_datetime); ?>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-3">
                        <?= ucfirst(__('text')); ?>
                    </div>
                    <div class="col-md-7">
                        <?= nl2br($noteDb->note_note); ?>
                    </div>
                </div>

            </div>

        </form>

    <?php } ?>
<?php } ?>