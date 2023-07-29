<?php
@set_time_limit(3000);
//@ini_set('memory_limit','-1');

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

include_once(CMS_ROOTPATH . "include/language_date.php");

$tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
$tree_result = $dbh->query($tree_sql);
?>
<h1 class="center"><?= __('Notes'); ?></h1>

<table class="humo standard" border="1">
    <tr class="table_header">
        <th colspan="2"><?= __('Notes'); ?></th>
    </tr>

    <tr>
        <td><?= __('Family tree'); ?></td>
        <td>
            <form method="POST" action="index.php">
                <input type="hidden" name="page" value="user_notes">
                <select size="1" name="tree_id" onChange="this.form.submit();">
                    <?php
                    while ($treeDb = $tree_result->fetch(PDO::FETCH_OBJ)) {
                        $treetext = show_tree_text($treeDb->tree_id, $selected_language);
                        $selected = '';
                        if (isset($tree_id) and ($treeDb->tree_id == $tree_id)) {
                            $selected = ' selected';
                            $note_tree_id = $treeDb->tree_id;
                            $db_functions->set_tree_id($tree_id);
                        }

                        $note_qry = "SELECT * FROM humo_user_notes WHERE note_tree_id='" . $treeDb->tree_id . "' AND note_status!='editor'";
                        //$note_qry= "SELECT * FROM humo_user_notes WHERE note_tree_id='".$treeDb->tree_id."' AND (note_status IN ('new','approved') OR note_status IS NULL)";
                        $note_qry = "SELECT * FROM humo_user_notes WHERE note_tree_id='" . $treeDb->tree_id . "' AND note_kind='user'";
                        $note_result = $dbh->query($note_qry);
                        $num_rows = $note_result->rowCount();

                        echo '<option value="' . $treeDb->tree_id . '"' . $selected . '>' . @$treetext['name'] . ' [' . $num_rows . ']</option>';
                    }
                    ?>
                </select>
            </form>
        </td>
    </tr>
    <?php

    $limit = 50;
    if (isset($_POST['limit']) and is_numeric($_POST['limit'])) {
        $limit = safe_text_db($_POST['limit']);
        $_SESSION['save_limit'] = $limit;
    }
    $user_notes = true;
    $editor_notes = true;
    if (isset($_POST['note_settings'])) {
        $user_notes = false;
        if (isset($_POST['user_notes'])) $user_notes = true;
        $_SESSION['save_user_notes'] = $user_notes;

        $editor_notes = false;
        if (isset($_POST['editor_notes'])) $editor_notes = true;
        $_SESSION['save_editor_notes'] = $editor_notes;
    }
    if (isset($_SESSION['save_user_notes'])) $user_notes = $_SESSION['save_user_notes'];
    if (isset($_SESSION['save_editor_notes'])) $editor_notes = $_SESSION['save_editor_notes'];
    if (isset($_SESSION['save_limit']) and is_numeric($_SESSION['save_limit'])) $limit = $_SESSION['save_limit'];

    ?>
    <tr>
        <td><?= __('Show notes'); ?></td>
        <td>
            <form method="POST" action="index.php" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <?php
                // *** Number of results in list ***
                echo ' ' . __('Results') . ': <select size="1" name="limit">';
                echo '<option value="50">50</option>';

                $selected = '';
                if ($limit == 100) {
                    $selected = ' selected';
                }
                echo '<option value="100"' . $selected . '>100</option>';

                $selected = '';
                if ($limit == 200) {
                    $selected = ' selected';
                }
                echo '<option value="200"' . $selected . '>200</option>';

                $selected = '';
                if ($limit == 500) {
                    $selected = ' selected';
                }
                echo '<option value="500"' . $selected . '>500</option>';
                echo '</select>';

                $check = '';
                if ($user_notes) $check = ' checked';
                echo ' <input type="checkbox" name="user_notes"' . $check . '>' . __('User notes');

                $check = '';
                if ($editor_notes) $check = ' checked';
                echo ' <input type="checkbox" name="editor_notes"' . $check . '>' . __('Editor notes');

                echo ' <input type="Submit" name="note_settings" value="' . __('Select') . '">';
                ?>
            </form>
        </td>
    </tr>
    <?php

    if (isset($_POST['note_status']) and is_numeric($_POST['note_id'])) {
        // *** For safety reasons: only save valid values ***
        $note_status = '';
        if ($_POST['note_status'] == 'new') {
            $note_status = 'new';
        }
        if ($_POST['note_status'] == 'approved') {
            $note_status = 'approved';
        }
        if ($note_status) {
            $sql = "UPDATE humo_user_notes
            SET note_status='" . $note_status . "'
            WHERE note_id='" . $_POST['note_id'] . "'";
            $result = $dbh->query($sql);
        }

        if ($_POST['note_status'] == 'remove') {
            echo '<div class="confirm">';
            echo __('Are you sure you want to remove this note?');
            echo ' <form method="post" action="index.php" style="display : inline;">';
            echo '<input type="hidden" name="page" value="user_notes">';
            echo '<input type="hidden" name="tree" value="' . $tree_id . '">';
            echo '<input type="hidden" name="note_id" value="' . $_POST['note_id'] . '">';
            echo ' <input type="Submit" name="note_remove" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
            echo ' <input type="Submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
            echo '</form>';
            echo '</div>';
        }
    }

    if (isset($_POST['note_remove']) and is_numeric($_POST["note_id"])) {
        echo '<div class="confirm">';
        // *** Delete source ***
        $sql = "DELETE FROM humo_user_notes WHERE note_id='" . safe_text_db($_POST["note_id"]) . "'";
        $result = $dbh->query($sql);
        echo __('Note is removed.');
        echo '</div>';
    }

    // *** Show user added notes ***
    if (isset($note_tree_id)) {
        //$note_qry= "SELECT * FROM humo_user_notes WHERE note_tree_id='".$note_tree_id."'";
        //$note_qry= "SELECT * FROM humo_user_notes WHERE note_tree_id='".$note_tree_id."' AND note_kind='user'";

        if ($user_notes) $note_kind = "AND note_kind='user'";
        if ($editor_notes) $note_kind = "AND note_kind='editor'";
        if ($user_notes and $editor_notes) $note_kind = '';
        $note_qry = "SELECT * FROM humo_user_notes WHERE note_tree_id='" . $note_tree_id . "' " . $note_kind . " LIMIT 0," . $limit;

        $note_result = $dbh->query($note_qry);
        $num_rows = $note_result->rowCount();

        /*
        echo '<tr class="humo_user_notes"><td>';
            if ($num_rows)
                echo '<a href="#humo_user_notes"></a> ';
            echo __('Notes').'</td><td colspan="2">';
            if ($num_rows)
                printf(__('There are %d user added notes.'), $num_rows);
            else
                printf(__('There are %d user added notes.'), 0);
        echo '</td></tr>';
        */

        while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) {
            $user_qry = "SELECT * FROM humo_users WHERE user_id='" . $noteDb->note_new_user_id . "'";
            $user_result = $dbh->query($user_qry);
            $userDb = $user_result->fetch(PDO::FETCH_OBJ);

    ?>
            <tr class="humo_color">
                <td style="min-width:200px">
                    <?php
                    if ($noteDb->note_kind == 'user') {
                        echo __('User note');
                    } else {
                        echo __('Editor note');
                    }

                    // *** Select status of message ***
                    $note_status = '';
                    if ($noteDb->note_status) $note_status = $noteDb->note_status;
                    ?>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="page" value="user_notes">
                        <input type="hidden" name="tree" value="<?= $tree_id; ?>">
                        <input type="hidden" name="note_id" value="<?= $noteDb->note_id; ?>">
                        <?php
                        echo '<select size="1" name="note_status">';
                        if ($noteDb->note_kind == 'user') {
                            $selected = '';
                            echo '<option value="new"' . $selected . '>' . __('New') . '</option>';
                            $selected = '';
                            if ($note_status == 'approved') $selected = ' selected';
                            echo '<option value="approved"' . $selected . '>' . __('Approved') . '</option>';
                        }
                        $selected = '';
                        if ($note_status == 'remove') $selected = ' selected';
                        echo '<option value="remove"' . $selected . '>' . __('Remove') . '</option>';
                        echo '</select>';
                        echo ' <input type="Submit" name="submit_button" value="' . __('Select') . '">';
                        ?>
                    </form>
                    <?php

                    if ($noteDb->note_kind != 'user') {
                        echo __('Priority') . ': ' . __($noteDb->note_priority) . '<br>';
                        echo __('Status') . ': ' . __($noteDb->note_status) . '<br>';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    //echo '<b>'.language_date($noteDb->note_date).' '.$noteDb->note_time.' '.$userDb->user_name.'</b><br>';
                    echo __('Added by') . ' <b>' . $userDb->user_name . '</b> (' . language_date($noteDb->note_new_date) . ' ' . $noteDb->note_new_time . ')<br>';

                    //echo '<b>'.$noteDb->note_names.'</b><br>';

                    // *** Link: index.php?page=editor&amp;tree_id=2_&amp;person=I313 ***
                    if (substr($noteDb->note_connect_id, 0, 1) == 'F') {
                        // *** Editor note by family ***
                        @$find_parent1Db = $db_functions->get_family($noteDb->note_connect_id);
                        if ($find_parent1Db->fam_man != "") {
                            echo '<b><a href="index.php?page=editor&amp;tree_id=' . $tree_id . '&amp;menu_tab=marriage&amp;person=' . $find_parent1Db->fam_man . '&amp;marriage_nr=' . $noteDb->note_connect_id . '">' . $noteDb->note_connect_id . ' ' . $noteDb->note_names . '</a></b><br>';
                        }
                    } else {
                        // *** Editor note by person ***
                        echo '<b><a href="index.php?page=editor&amp;tree_id=' . $tree_id . '&amp;menu_tab=person&amp;person=' . $noteDb->note_connect_id . '">' . $noteDb->note_connect_id . ' ' . $noteDb->note_names . '</a></b><br>';
                    }

                    echo nl2br($noteDb->note_note);
                    ?>
                </td>
            </tr>
    <?php
        }
    }
    ?>
</table>