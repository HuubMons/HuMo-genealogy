<?php
//global $language, $data2Db, $page, $menu_admin;
//global $phpself, $phpself2, $joomlastring;

// *** Picture path. A | character is used for a default path (the old path will remain in the field) ***
if (substr($data2Db->tree_pict_path, 0, 1) == '|') {
    $checked1 = ' checked';
    $checked2 = '';
} else {
    $checked1 = '';
    $checked2 = ' checked';
}
$tree_pict_path = $data2Db->tree_pict_path;
if (substr($data2Db->tree_pict_path, 0, 1) == '|') $tree_pict_path = substr($tree_pict_path, 1);

// *** Family tree privacy ***
$select_filter_persons = '';
if ($data2Db->tree_privacy == 'filter_persons') {
    $select_filter_persons = 'selected';
}
$select_show_persons = '';
if ($data2Db->tree_privacy == 'show_persons') {
    $select_show_persons = 'selected';
}

?>
<form method="post" action="<?= $phpself; ?>">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="tree_id" value="<?= $data2Db->tree_id; ?>">
    <input type="hidden" name="menu_admin" value="<?= $menu_admin; ?>">

    <br>
    <table class="humo" cellspacing="0" width="100%">
        <tr class="table_header">
            <th colspan="2"><?= __('Family tree data'); ?></th>
        </tr>

        <tr>
            <td><?= __('E-mail address'); ?><br><?= __('Owner of tree'); ?></td>
            <td><?= __('E-mail address will not be shown on the site: an e-mail form will be generated!'); ?><br><input type="text" name="tree_email" value="<?= $data2Db->tree_email; ?>" size="40"><br>
                <input type="text" name="tree_owner" value="<?= $data2Db->tree_owner; ?>" size="40">
            </td>
        </tr>

        <tr>
            <td><?= __('Path to the pictures'); ?></td>
            <td>
                <input type="radio" value="yes" name="default_path" <?= $checked1; ?>><?= __('Use default picture path:'); ?><b>media/</b><br>
                <input type="radio" value="no" name="default_path" <?= $checked2; ?>>

                <input type="text" name="tree_pict_path" value="<?= $tree_pict_path; ?>" size="40" placeholder="../pictures/"><br>
                <?= sprintf(__('Example of picture path:<br>
www.myhomepage.nl/humo-gen/ => folder for %s files.<br>
www.myhomepage.nl/pictures/ => folder for pictures.<br>
Use a relative path, exactly as shown here: <b>../pictures/</b>'), 'HuMo-genealogy'); ?>

                <br><a href="index.php?page=thumbs"><?= __('Pictures/ create thumbnails'); ?></a><br>
            </td>
        </tr>

        <tr>
            <td><?= __('Tree privacy'); ?>:</td>
            <td><?= __('This option is valid for ALL persons in this tree!'); ?><br><select size="1" name="tree_privacy">
                    <option value="standard"><?= __('Standard'); ?></option>
                    <option value="filter_persons" <?= $select_filter_persons; ?>><?= __('FILTER ALL persons'); ?></option>
                    <option value="show_persons" <?= $select_show_persons; ?>><?= __('DISPLAY ALL persons'); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <td><?= __('Change'); ?></td>
            <td><input type="Submit" name="change_tree_data" value="<?= __('Change'); ?>"></td>
        </tr>

    </table>
</form>