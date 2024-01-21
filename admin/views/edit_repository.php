<?php

/**
 * Edit or add a repository.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}



// TODO create seperate controller script.
include_once(__DIR__ . "/../include/editor_cls.php");
$editor_cls = new editor_cls;

require_once  __DIR__ . "/../models/edit_repository.php";
$editRepositoryModel = new EditorRepositoryModel($dbh);
$editRepositoryModel->set_repo_id();
$editRepositoryModel->update_repository($dbh, $tree_id, $db_functions, $editor_cls);
$editRepository['repo_id'] = $editRepositoryModel->get_repo_id();



$phpself = 'index.php';
$field_text_large = 'style="height: 100px; width:550px"';

// *** Editor icon for admin and editor: select family tree ***
//if (isset($tree_id) and $tree_id) {
//    $db_functions->set_tree_id($tree_id);
//}

$repo_qry = $dbh->query("SELECT * FROM humo_repositories WHERE repo_tree_id='" . $tree_id . "' ORDER BY repo_name, repo_place");
?>

<h1 class="center"><?= __('Repositories'); ?></h1>
<?= __('A repository can be connected to a source. Edit a source to connect a repository.'); ?>

<?php if (isset($_POST['repo_remove'])) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Really remove repository with all repository links?'); ?></strong>
        <form method="post" action="<?= $phpself; ?>" style="display : inline;">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="repo_id" value="<?= $editRepository['repo_id']; ?>">
            <input type="submit" name="repo_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="dummy6" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php } ?>

<?php if (isset($_POST['repo_remove2'])) { ?>
    <div class="alert alert-success">
        <strong><?= __('Repository is removed!'); ?></strong>
    </div>
<?php } ?>

<form method="POST" action="<?= $phpself; ?>" style="display : inline;">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <div class="p-3 m-2 genealogy_search">
        <div class="row">
            <div class="col-auto">
                <label for="tree" class="col-form-label">
                    <?= __('Family tree'); ?>:
                </label>
            </div>

            <div class="col-auto">
                <?= $editor_cls->select_tree($page); ?>
            </div>

            <div class="col-auto">
                <label for="tree" class="col-form-label">
                    <?= __('Select repository'); ?>
                </label>
            </div>

            <div class="col-auto">
                <select size="1" name="repo_id" class="form-select form-select-sm" onChange="this.form.submit();">
                    <!--  For new repository in new database... -->
                    <option value=""><?= __('Select repository'); ?></option>
                    <?php
                    while ($repoDb = $repo_qry->fetch(PDO::FETCH_OBJ)) {
                        $selected = '';
                        if ($editRepository['repo_id'] == $repoDb->repo_id) {
                            $selected = ' selected';
                        }
                        echo '<option value="' . $repoDb->repo_id . '"' . $selected . '>' .
                            @$repoDb->repo_gedcomnr . ', ' . $repoDb->repo_name . ' ' . $repoDb->repo_place . '</option>' . "\n";
                    }
                    ?>
                </select>
            </div>

            <div class="col-auto">
                <?= __('or'); ?>:
                <input type="submit" name="add_repo" value="<?= __('Add repository'); ?>" class="btn btn-sm btn-secondary">
            </div>
        </div>
    </div>
</form>
<?php

// *** Show selected repository ***
if ($editRepository['repo_id'] or isset($_POST['add_repo'])) {
    if (isset($_POST['add_repo'])) {
        $repo_name = '';
        $repo_address = '';
        $repo_zip = '';
        $repo_place = '';
        $repo_phone = '';
        $repo_date = '';
        $repo_text = '';
        $repo_mail = '';
        $repo_url = '';
        $repo_new_user = '';
        $repo_new_date = '';
        $repo_new_time = '';
        $repo_changed_user = '';
        $repo_changed_date = '';
        $repo_changed_time = '';
    } else {
        @$repo_qry = $dbh->query("SELECT * FROM humo_repositories WHERE repo_id='" . $editRepository['repo_id'] . "'");
        $die_message = __('No valid repository number.');
        try {
            @$repoDb = $repo_qry->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $die_message;
        }
        $repo_name = $repoDb->repo_name;
        $repo_address = $repoDb->repo_address;
        $repo_zip = $repoDb->repo_zip;
        $repo_place = $repoDb->repo_place;
        $repo_phone = $repoDb->repo_phone;
        $repo_date = $repoDb->repo_date;
        $repo_text = $repoDb->repo_text;
        $repo_mail = $repoDb->repo_mail;
        $repo_url = $repoDb->repo_url;
        $repo_new_user = $repoDb->repo_new_user;
        $repo_new_date = $repoDb->repo_new_date;
        $repo_new_time = $repoDb->repo_new_time;
        $repo_changed_user = $repoDb->repo_changed_user;
        $repo_changed_date = $repoDb->repo_changed_date;
        $repo_changed_time = $repoDb->repo_changed_time;
    }
?>
    <form method="POST" action="<?= $phpself; ?>">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="hidden" name="repo_id" value="<?= $editRepository['repo_id']; ?>">
        <table class="humo standard" border="1">
            <tr class="table_header">
                <th><?= __('Option'); ?></th>
                <th colspan="2"><?= __('Value'); ?></th>
            </tr>

            <tr>
                <td><?= __('Title'); ?></td>
                <td><input type="text" name="repo_name" value="<?= htmlspecialchars($repo_name); ?>" size="60"></td>
            </tr>

            <tr>
                <td><?= __('Address'); ?></td>
                <td><input type="text" name="repo_address" value="<?= htmlspecialchars($repo_address); ?>" size="60"></td>
            </tr>

            <tr>
                <td><?= __('Zip code'); ?></td>
                <td><input type="text" name="repo_zip" value="<?= $repo_zip; ?>" size="60"></td>
            </tr>

            <tr>
                <td><?= ucfirst(__('date')) . ' - ' . __('place'); ?></td>
                <td><?= $editor_cls->date_show($repo_date, "repo_date"); ?> <input type="text" name="repo_place" value="<?= htmlspecialchars($repo_place); ?>" placeholder="<?= ucfirst(__('place')); ?>" size="50"></td>
            </tr>

            <tr>
                <td><?= __('Phone'); ?></td>
                <td><input type="text" name="repo_phone" value="<?= $repo_phone; ?>" size="60"></td>
            </tr>

            <tr>
                <td><?= ucfirst(__('text')); ?></td>
                <td><textarea rows="1" name="repo_text" <?= $field_text_large; ?>><?= $editor_cls->text_show($repo_text); ?></textarea></td>
            </tr>

            <tr>
                <td><?= __('E-mail'); ?></td>
                <td><input type="text" name="repo_mail" value="<?= $repo_mail; ?>" size="60"></td>
            </tr>

            <tr>
                <td><?= __('URL/ Internet link'); ?></td>
                <td><input type="text" name="repo_url" value="<?= $repo_url; ?>" size="60"></td>
            </tr>

            <?php
            if (isset($_POST['add_repo'])) {
                echo '<tr><td>' . __('Add') . '</td><td><input type="submit" name="repo_add" value="' . __('Add') . '"></td></tr>';
            } else {
                echo '<tr><td>' . __('Save') . '</td><td><input type="submit" name="repo_change" value="' . __('Save') . '">';

                echo ' ' . __('or') . ' ';
                echo '<input type="submit" name="repo_remove" value="' . __('Delete') . '">';

                echo '</td></tr>';
            }
            ?>
        </table>
    </form>
<?php
}
