<?php
$languageDate = new LanguageDate();

// *** $note_connect_id = I123 or F123 ***
$note_connect_id = $pers_gedcomnumber;
if ($note_connect_kind == 'family') {
    $note_connect_id = $marriage;
}

$note_qry = "SELECT * FROM humo_user_notes
    WHERE note_tree_id='" . $tree_id . "'
    AND note_kind='editor' AND note_connect_kind='" . $note_connect_kind . "'
    AND note_connect_id='" . $note_connect_id . "'";
$note_result = $dbh->query($note_qry);
$num_rows = $note_result->rowCount();

// *** Otherwise link won't work second time because of added anchor ***
$anchor = '#editor_notes';
if (isset($_GET['note_add'])) {
    $anchor = '';
}
?>
<tr class="table_header_large">
    <td><a name="editor_notes"></a><?= __('Editor notes'); ?></td>
    <td colspan="2">
        <a href="index.php?page=editor&amp;note_add=<?= $note_connect_kind . $anchor; ?>">[<?= __('Add'); ?>]</a>
        <?php
        if ($num_rows) {
            printf(__('There are %d editor notes.'), $num_rows);
        } else {
            printf(__('There are %d editor notes.'), 0);
        }
        ?>
    </td>
</tr>

<?php while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) { ?>
    <tr>
        <td>
            <!-- Link to remove note -->
            <a href="index.php?page=editor&amp;note_drop=<?= $noteDb->note_id; ?>">
                <img src="images/button_drop.png" border="0" alt="down">
            </a>
        </td>
        <td colspan="2">
            <input type="hidden" name="note_id[<?= $noteDb->note_id; ?>]" value="<?= $noteDb->note_id; ?>">
            <input type="hidden" name="note_connect_kind[<?= $noteDb->note_id; ?>]" value="<?= $note_connect_kind; ?>">

            <?php $user_name = $db_functions->get_user_name($noteDb->note_new_user_id); ?>
            <?= __('Added by'); ?> <b><?= $user_name; ?></b> (<?= $languageDate->show_datetime($noteDb->note_new_datetime); ?>)<br>

            <?php
            if ($noteDb->note_changed_user_id) {
                $user_name = $db_functions->get_user_name($noteDb->note_changed_user_id);
            ?>
                <?= __('Changed by'); ?> <b><?= $user_name; ?></b> (<?= $languageDate->show_datetime($noteDb->note_changed_datetime); ?>)<br>
            <?php } ?>

            <b><?= $noteDb->note_names; ?></b><br>

            <textarea rows="1" name="note_note[<?= $noteDb->note_id; ?>]" <?= $field_text_large; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($noteDb->note_note); ?></textarea><br>

            <?= __('Priority'); ?>
            <select size="1" name="note_priority[<?= $noteDb->note_id; ?>]">
                <option value="Low"><?= __('Low'); ?></option>
                <option value="Normal" <?= $noteDb->note_priority == 'Normal' ? ' selected' : ''; ?>><?= __('Normal'); ?></option>
                <option value="High" <?= $noteDb->note_priority == 'High' ? ' selected' : ''; ?>><?= __('High'); ?></option>
            </select>

            &nbsp;&nbsp;&nbsp;&nbsp;<?= __('Status'); ?>
            <select size="1" name="note_status[<?= $noteDb->note_id; ?>]">
                <option value="Not started"><?= __('Not started'); ?></option>
                <option value="In progress" <?php if ($noteDb->note_status == 'In progress') echo ' selected'; ?>><?= __('In progress'); ?></option>
                <option value="Completed" <?php if ($noteDb->note_status == 'Completed') echo ' selected'; ?>><?= __('Completed'); ?></option>
                <option value="Postponed" <?php if ($noteDb->note_status == 'Postponed') echo ' selected'; ?>><?= __('Postponed'); ?></option>
                <option value="Cancelled" <?php if ($noteDb->note_status == 'Cancelled') echo ' selected'; ?>><?= __('Cancelled'); ?></option>
            </select>
        </td>
    </tr>
<?php
}
