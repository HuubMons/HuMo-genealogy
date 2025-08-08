<?php
// TODO: split into model & view files.

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// *** Update tree settings ***
if (isset($_POST['user_change']) && isset($_POST["id"]) && is_numeric($_POST["id"])) {
    $user_hide_trees = '';
    $user_edit_trees = '';
    $data3sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY'");
    while ($data3Db = $data3sql->fetch(PDO::FETCH_OBJ)) {
        // *** Show/ hide trees ***
        $check = 'show_tree_' . $data3Db->tree_id;
        if (isset($_POST["$check"]) && $_POST["$check"] == 'no') {
            if ($user_hide_trees !== '') {
                $user_hide_trees .= ';';
            }
            $user_hide_trees .= $data3Db->tree_id;
        }
        if (isset($_POST["$check"]) && $_POST["$check"] == 'yes') {
            if ($user_hide_trees !== '') {
                $user_hide_trees .= ';';
            }
            $user_hide_trees .= 'y' . $data3Db->tree_id;
        }

        // *** Edit trees (NOT USED FOR ADMINISTRATOR) ***
        $check = 'edit_tree_' . $data3Db->tree_id;
        if (isset($_POST["$check"])) {
            if ($user_edit_trees !== '') {
                $user_edit_trees .= ';';
            }
            $user_edit_trees .= $data3Db->tree_id;
        }
    }
    $sql = "UPDATE humo_users SET user_hide_trees='" . $user_hide_trees . "',  user_edit_trees='" . $user_edit_trees . "'  WHERE user_id=" . $_POST["id"];
    $dbh->query($sql);
}
?>

<h1 align=center><?= __('Extra settings'); ?></h1>

<h2 align="center"><?= __('Hide or show family trees per user.'); ?></h2>
<?php //echo __('Editor').': '.__('If an .htpasswd file is used: add username in .htpasswd file.').'</td>'; 
?>

<?= __('These are settings PER USER, it\'s also possible to set these setting PER USER GROUP.'); ?>

<?php
if (isset($_GET['user'])) {
    $user = $_GET['user'];
}
if (isset($_POST['id'])) {
    $user = $_POST['id'];
}
if (is_numeric($user)) {
    $usersql = "SELECT * FROM humo_users WHERE user_id='" . $user . "'";
    $user = $dbh->query($usersql);
    $userDb = $user->fetch(PDO::FETCH_OBJ);

    $hide_tree_array = explode(";", $userDb->user_hide_trees);
    $edit_tree_array = explode(";", $userDb->user_edit_trees);
?>

    <form method="POST" action="index.php?page=editor_user_settings">
        <input type="hidden" name="page" value="editor_user_settings">
        <input type="hidden" name="id" value="<?= $userDb->user_id; ?>">
        <table class="table">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Family tree'); ?></th>
                    <th><?= __('Show tree?'); ?></th>
                    <th><?= __('Edit tree?'); ?> <input type="submit" name="user_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
                </tr>
            </thead>
            <?php
            $data3sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
            while ($data3Db = $data3sql->fetch(PDO::FETCH_OBJ)) {
                $treetext = $showTreeText->show_tree_text($data3Db->tree_id, $selected_language);
                $tree_text_name = $treetext['name'];
            ?>
                <tr>
                    <td><?= $tree_text_name; ?></td>

                    <!-- Show/ hide tree for user -->
                    <td>
                        <select size="1" name="show_tree_<?= $data3Db->tree_id; ?>" aria-label="<?= __('Extra settings'); ?>" class="form-select form-select-sm">
                            <option value="user-group"><?= __('Use user-group setting'); ?></option>
                            <option value="yes" <?= in_array('y' . $data3Db->tree_id, $hide_tree_array) ? 'selected' : ''; ?>><?= __('Yes'); ?></option>
                            <option value="no" <?= in_array($data3Db->tree_id, $hide_tree_array) ? 'selected' : ''; ?>><?= __('No'); ?></option>
                        </select>
                    </td>

                    <td>
                        <input type="checkbox" name="edit_tree_<?= $data3Db->tree_id; ?>" <?= in_array($data3Db->tree_id, $edit_tree_array) || $userDb->user_id == '1' ? 'checked' : ''; ?> <?= $userDb->user_id == '1' ? 'disabled' : ''; ?>>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </form>
<?php } ?>