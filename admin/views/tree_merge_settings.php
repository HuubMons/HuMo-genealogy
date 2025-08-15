<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// *** Re-read variables after changing them ***
$generalSettings = new \Genealogy\Include\GeneralSettings();
$humo_option = $generalSettings->get_humo_option($dbh);
?>

<form method="post" action="index.php" class="my-2">
    <input type="hidden" name="page" value="tree">
    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
    <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">

    <table class="table" style="width:1200px;">
        <tr class="table-primary">
            <th colspan="3"><?= __('Merge filter settings'); ?></th>
        </tr>

        <tr>
            <th colspan="3"><?= __('General'); ?></th>
        </tr>

        <tr>
            <td><?= __('Max characters to match firstname:'); ?></td>
            <td width="100">
                <input type="text" name="merge_chars" value="<?= $humo_option["merge_chars"]; ?>" size="1" class="form-control form-control-sm">
            </td>
            <td>
                <?= __('In different trees, first names may be listed differently: Thomas Julian Booth, Thomas J. Booth, Thomas Booth etc. By default a match of the first 10 characters of the first name will be considered a match. You can change this to another value. Try and find the right balance: if you set a low number of chars you will get many unwanted possible matches. If you set it too high, you may miss possible matches as in the example names above.'); ?>
            </td>
        </tr>

        <tr>
            <th colspan="3"><?= __('Duplicate merge'); ?></th>
        </tr>

        <tr>
            <td><?= __('include blank lastnames'); ?></td>
            <td>
                <select size="1" name="merge_lastname" class="form-select form-select-sm">
                    <option value="YES"><?= __('Yes'); ?></option>
                    <option value="NO" <?= $humo_option["merge_lastname"] == 'NO' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                </select>
            </td>
            <td>
                <?= __('By default two persons with missing lastnames will be included as possible duplicates. Two persons called "John" without lastname will be considered a possible match. If you have many cases like this you could get a very long list of possible duplicates and you might want to disable this, so only persons with lastnames will be included.'); ?>
            </td>
        </tr>

        <tr>
            <td><?= __('include blank firstnames'); ?></td>
            <td>
                <select size="1" name="merge_firstname" class="form-select form-select-sm">
                    <option value="YES"><?= __('Yes'); ?></option>
                    <option value="NO" <?= $humo_option["merge_firstname"] == 'NO' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                </select>
            </td>
            <td>
                <?= __('Same as above, but for first names. When enabled (default), all persons called "Smith" without first name will be considered possible duplicates of each other. If you have many cases like this it could give you a long list and you might want to disable it.'); ?>
            </td>
        </tr>

        <tr>
            <td><?= __('include blank dates'); ?></td>
            <td>
                <select size="1" name="merge_dates" class="form-select form-select-sm">
                    <option value="YES"><?= __('Yes'); ?></option>
                    <option value="NO" <?= $humo_option["merge_dates"] == 'NO' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                </select>
            </td>
            <td>
                <?= __('By default, two persons with identical names, but with one or both missing birth/death dates are considered possible duplicates. In certain trees this can give a long list of possible duplicates. You can choose to disable this so only persons who both have a birth or death date and this date is identical, will be considered a possible match. This can drastically cut down the number of possible duplicates, but of course you may also miss out on pairs that actually are duplicates.'); ?>
            </td>
        </tr>

        <tr>
            <th colspan="3"><?= __('Automatic merge'); ?></th>
        </tr>

        <tr>
            <td><?= __('include parents marriage date:'); ?></td>
            <td>
                <select size="1" name="merge_parentsdate" class="form-select form-select-sm">
                    <option value="YES"><?= __('Yes'); ?></option>
                    <option value="NO" <?= $humo_option["merge_parentsdate"] == 'NO' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                </select>
            </td>
            <td>
                <?= __('Automatic merging is a dangerous business. Therefore many clauses are used to make sure the persons are indeed identical. Besides identical names, identical birth or death dates and identical names of parents, also the parents\' wedding date is included. If you consider this too much and rely on the above clauses, you can disable this.'); ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td></td>
            <td style="text-align:center">
                <input type="submit" name="settings" value="<?= __('Save'); ?>" class="btn btn-success">
                &nbsp;&nbsp;&nbsp;<input type="submit" name="reset" value="<?= __('Reset'); ?>" class="btn btn-secondary">
            </td>
        </tr>

    </table>
</form>