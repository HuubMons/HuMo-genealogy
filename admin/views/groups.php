<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$phpself = 'index.php';



// TODO create seperate controller script.
require_once  __DIR__ . "/../models/groups.php";
$groupsModel = new GroupsModel($dbh);
$groupsModel->set_group_id();
$groupsModel->update_group($dbh);
$groups['group_id'] = $groupsModel->get_group_id();



?>
<h1 class="center"><?= __('User groups'); ?></h1>

<?php
if (isset($_POST['group_remove'])) {
    $usersql = "SELECT * FROM humo_users WHERE user_group_id=" . $groups['group_id'];
    $user = $dbh->query($usersql);
    $nr_users = $user->rowCount();
?>
    <div class="alert alert-danger">
        <?php if ($nr_users > 0) { ?>
            <!-- There are still users connected to this group -->
            <strong><?= __('It\'s not possible to delete this group: there is/ are'); ?> <?= $nr_users; ?> <?= __('user(s) connected to this group!'); ?></strong>
        <?php } else { ?>
            <strong><?= __('Are you sure you want to remove the group:'); ?> "<?= $_POST['group_name']; ?>"?</strong>
            <form method="post" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="group_id" value="<?= $groups['group_id']; ?>">
                <input type="submit" name="group_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
                <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
            </form>
        <?php } ?>
    </div>
<?php
}

// *** User groups ***
printf(__('You can have multiple users in %s. Every user can be connected to 1 group.<br>
Examples:<br>
Group "guest" = <b>guests at the website (who are not logged in).</b><br>
Group "admin" = website administrator.<br>
Group "family" = family members or genealogists.'), 'HuMo-genealogy');

$groupsql = "SELECT group_id, group_name FROM humo_groups";
$groupresult = $dbh->query($groupsql);
?>
<br>
<table class="humo standard" style="text-align:center;">
    <tr class="table_header_large">
        <td>
            <b><?= __('Choose a user group: '); ?></b>
            <?php while ($groupDb = $groupresult->fetch(PDO::FETCH_OBJ)) { ?>
                <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="group_id" value="<?= $groupDb->group_id; ?>">
                    <input type="submit" name="submit" value="<?php echo ($groupDb->group_name == '') ? 'NO NAME' : $groupDb->group_name; ?>" <?= $groupDb->group_id == $groups['group_id'] ? 'class="btn btn-sm btn-primary"' : 'class="btn btn-sm btn-secondary"'; ?>>
                </form>
            <?php } ?>

            <!-- Add group -->
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="submit" name="group_add" value="<?= __('ADD GROUP'); ?>" class="btn btn-sm btn-secondary">
            </form>
        </td>
    </tr>
</table><br>

<?php
/* *** Automatic installation or update ***
 * Januari 2016: Older updates are moved to update and installation script (was already a long list...)!
 */
$column_qry = $dbh->query('SHOW COLUMNS FROM humo_groups');
while ($columnDb = $column_qry->fetch()) {
    $field_value = $columnDb['Field'];
    $field[$field_value] = $field_value;
}
if (!isset($field['group_citation_generation'])) {
    $sql = "ALTER TABLE humo_groups ADD group_citation_generation VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n' AFTER group_own_code;";
    $result = $dbh->query($sql);
}
if (!isset($field['group_menu_change_password'])) {
    $sql = "ALTER TABLE humo_groups ADD group_menu_change_password VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'y' AFTER group_menu_login;";
    $result = $dbh->query($sql);
}
if (!isset($field['group_menu_cms'])) {
    $sql = "ALTER TABLE humo_groups ADD group_menu_cms VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'y' AFTER group_menu_login;";
    $result = $dbh->query($sql);
}
if (!isset($field['group_show_age_living_person'])) {
    $sql = "ALTER TABLE humo_groups ADD group_show_age_living_person VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'y' AFTER group_maps_presentation;";
    $result = $dbh->query($sql);
}

// *** Show usergroup ***
$groupsql = "SELECT * FROM humo_groups WHERE group_id='" . $groups['group_id'] . "'";
$groupresult = $dbh->query($groupsql);
$groupDb = $groupresult->fetch(PDO::FETCH_OBJ);

?>
<form method="POST" action="<?= $phpself; ?>">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="group_id" value="<?= $groups['group_id']; ?>">
    <table class="humo standard" border="1">
        <tr class="table_header">
            <th><?= __('Group'); ?>
                <?php
                if ($groupDb->group_id > '3') {
                    echo ' <input type="submit" name="group_remove" value="' . __('REMOVE GROUP') . '" class="btn btn-sm btn-secondary">';
                }
                ?>
            </th>
            <th><input type="submit" name="group_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <tr>
            <td><?= __('Group name'); ?></td>
            <td><input type="text" name="group_name" value="<?= $groupDb->group_name; ?>" size="15"></td>
        </tr>

        <tr>
            <td><?= __('Administrator'); ?></td>
            <?php
            $check = '';
            if ($groupDb->group_admin != 'n') {
                $check = ' checked';
            }
            // *** Administrator group: don't change admin rights for administrator ***
            $disabled = '';
            if ($groupDb->group_id == '1') {
                $disabled = ' disabled';
                echo '<input type="hidden" name="group_admin" value="' . $groupDb->group_admin . '">';
            }
            ?>
            <td><input type="checkbox" name="group_admin" <?= $check . $disabled; ?>></td>
        </tr>

        <tr>
            <td><?= __('Save statistics data'); ?></td>
            <td><input type="checkbox" name="group_statistics" <?php if ($groupDb->group_statistics != 'n') echo ' checked' ?>></td>
        </tr>

        <tr class="table_header">
            <th><?= __('Menu'); ?></th>
            <th><input type="submit" name="group_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <tr>
            <td><?= __('Birthday RSS in main menu'); ?></td>
            <td><input type="checkbox" name="group_birthday_rss" <?php if ($groupDb->group_birthday_rss != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('INFORMATION menu: show "CMS" pages'); ?></td>
            <td><input type="checkbox" name="group_menu_cms" <?php if ($groupDb->group_menu_cms != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('FAMILY TREE menu: show "Persons" submenu'); ?></td>
            <td><input type="checkbox" name="group_menu_persons" <?php if ($groupDb->group_menu_persons != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('FAMILY TREE menu: show "Names" submenu'); ?></td>
            <td><input type="checkbox" name="group_menu_names" <?php if ($groupDb->group_menu_names != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('FAMILY TREE menu: show "Places" submenu'); ?></td>
            <td><input type="checkbox" name="group_menu_places" <?php if ($groupDb->group_menu_places != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('FAMILY TREE menu: show "Addresses" submenu (only shown if there really are addresses)'); ?></td>
            <td><input type="checkbox" name="group_addresses" <?php if ($groupDb->group_addresses != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('FAMILY TREE menu: show "Photobook" submenu'); ?></td>
            <td><input type="checkbox" name="group_photobook" <?php if ($groupDb->group_photobook != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('TOOLS menu: show "Anniversary" (birthday list) submenu'); ?></td>
            <td><input type="checkbox" name="group_birthday_list" <?php if ($groupDb->group_birthday_list != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('TOOLS menu: show "Statistics" submenu'); ?></td>
            <td><input type="checkbox" name="group_showstatistics" <?php if ($groupDb->group_showstatistics != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('TOOLS menu: show "Relationship Calculator" submenu'); ?></td>
            <td><input type="checkbox" name="group_relcalc" <?php if ($groupDb->group_relcalc != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('TOOLS menu: show "Google maps" submenu (only shown if geolocation database was created)'); ?></td>
            <td><input type="checkbox" name="group_googlemaps" <?php if ($groupDb->group_googlemaps != 'n') echo ' checked'; ?>></td>
        </tr>

        <tr>
            <td><?= __('TOOLS menu: show "Contact" submenu (only shown if tree owner and email were entered)'); ?></td>
            <td><input type="checkbox" name="group_contact" <?= $groupDb->group_contact != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('TOOLS menu: show "Latest changes" submenu'); ?></td>
            <td><input type="checkbox" name="group_latestchanges" <?= $groupDb->group_latestchanges != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show "Login" link (can be changed in group "guest" only)'); ?></td>
            <?php
            // *** Only change this item for guest group ***
            $disabled = '';
            if ($groupDb->group_id != '3') {
                $disabled = ' disabled';
                echo '<input type="hidden" name="group_menu_login" value="' . $groupDb->group_menu_login . '">';
            }
            ?>
            <td><input type="checkbox" name="group_menu_login" <?= $groupDb->group_menu_login != 'n' ? 'checked' : ''; ?> <?= $disabled; ?>></td>
        </tr>

        <tr>
            <td><?= __('Is allowed to change password'); ?></td>
            <td><input type="checkbox" name="group_menu_change_password" <?= $groupDb->group_menu_change_password != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr class="table_header">
            <th><?= __('General'); ?></th>
            <th><input type="submit" name="group_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <tr>
            <td><?= __('Show pictures'); ?>
                <i><?= __('(option can only be disabled if option "Show photobook in submenu" is disabled)'); ?></i><br>
                <a href="index.php?page=thumbs"><?= __('Pictures/ create thumbnails'); ?></a>
            </td>
            <td><input type="checkbox" name="group_pictures" <?= $groupDb->group_pictures != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show Gedcom number (from gedcom file)'); ?></td>
            <td><input type="checkbox" name="group_gedcomnr" <?= $groupDb->group_gedcomnr != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show residence and address'); ?></td>
            <td><input type="checkbox" name="group_living_place" <?= $groupDb->group_living_place != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show places with bapt., birth, death and cemetery.'); ?></td>
            <td><input type="checkbox" name="group_places" <?= $groupDb->group_places != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show religion (with bapt. and wedding)'); ?></td>
            <td><input type="checkbox" name="group_religion" <?= $groupDb->group_religion != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show date and place (i.e. with birth, bapt., death, cemetery.)'); ?></td>
            <td>
                <select size="1" name="group_place_date">
                    <option value="j">Alkmaar 18 feb 1965</option>
                    <option value="n" <?= $groupDb->group_place_date == 'n' ? 'selected' : ''; ?>>18 feb 1965 Alkmaar</option>
                </select>
            </td>
        </tr>

        <tr>
            <td><?= __('Show name in indexes'); ?></td>
            <td><select size="1" name="group_kindindex">
                    <option value='j'>van Mons, Henk</option>
                    <option value="n" <?= $groupDb->group_kindindex == 'n' ? 'selected' : ''; ?>>Mons, Henk van</option>
                </select></td>
        </tr>

        <tr>
            <td><?= __('Show events'); ?></td>
            <td><input type="checkbox" name="group_event" <?= $groupDb->group_event != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show own code'); ?></td>
            <td><input type="checkbox" name="group_own_code" <?= $groupDb->group_own_code != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <!-- First default presentation of a family page (visitor can override value) -->
        <tr>
            <td><?= __('Default presentation of family page'); ?></td>
            <td>
                <select size="1" name="group_family_presentation">
                    <option value="compact" <?= $groupDb->group_family_presentation == 'compact' ? 'selected' : ''; ?>><?= __('Compact view'); ?></option>
                    <option value="expanded1" <?= $groupDb->group_family_presentation == 'expanded1' ? 'selected' : ''; ?>><?= __('Expanded view'); ?> 1</option>
                    <option value="expanded2" <?= $groupDb->group_family_presentation == 'expanded2' ? 'selected' : ''; ?>><?= __('Expanded view'); ?> 2</option>
                </select>
            </td>
        </tr>

        <!-- First default presentation of Google maps in family page (visitor can override value) -->
        <tr>
            <td><?= __('Default presentation of Google maps in family page'); ?></td>
            <td>
                <select size="1" name="group_maps_presentation">
                    <option value="show" <?= $groupDb->group_maps_presentation == 'show' ? 'selected' : ''; ?>><?= __('Show Google maps'); ?></option>
                    <option value="hide" <?= $groupDb->group_maps_presentation == 'hide' ? 'selected' : ''; ?>><?= __('Hide Google maps'); ?></option>
                </select>
            </td>
        </tr>

        <!-- Show age of living person -->
        <tr>
            <td><?= __('Show age of living person'); ?></td>
            <td><input type="checkbox" name="group_show_age_living_person" <?= $groupDb->group_show_age_living_person != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <!-- Show PDF report button -->
        <tr>
            <td><?= __('Show "PDF Report" button in family screen and reports'); ?></td>
            <td><input type="checkbox" name="group_pdf_button" <?= $groupDb->group_pdf_button != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <!-- Show RTF report button -->
        <tr>
            <td><?= __('Show "RTF Report" button in family screen and reports'); ?></td>
            <td><input type="checkbox" name="group_rtf_button" <?= $groupDb->group_rtf_button != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <!-- Show Citation generation -->
        <tr>
            <td><?= __('Generate citations (can be used as source).'); ?></td>
            <td><input type="checkbox" name="group_citation_generation" <?= $groupDb->group_citation_generation != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('User is allowed to add notes/ remarks by a person in the family tree') . '. ' . __('Disabled in group "Guest"'); ?></td>
            <?php
            $disabled = '';
            // *** Disable this option in "Guest" group.
            if ($groupDb->group_id == '3') {
                $disabled = ' disabled';
            }
            ?>
            <td><input type="checkbox" name="group_user_notes" <?= $groupDb->group_user_notes != 'n' ? 'checked' : ''; ?> <?= $disabled; ?>></td>
        </tr>

        <tr>
            <td><?= __('User can see notes/ remarks added by other users in the family tree'); ?></td>
            <?php
            $disabled = ''; //if ($groupDb->group_id=='3'){ $disabled=' disabled';} // *** Disable this option in "Guest" group.
            ?>
            <td><input type="checkbox" name="group_user_notes_show" <?= $groupDb->group_user_notes_show != 'n' ? 'checked' : ''; ?> <?= $disabled; ?>></td>
        </tr>

        <!-- Sources -->
        <tr class="table_header">
            <th><?= __('Sources'); ?></th>
            <th><input type="submit" name="group_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <tr>
            <td>
                <?= __('Don\'t show sources'); ?><br>
                <?= __('Only show source titles'); ?><br>
                <?= __('Show sources and menu sources'); ?><br>
            </td>
            <td>
                <input type="radio" name="group_sources" value="n" <?= $groupDb->group_sources == 'n' ? 'checked' : ''; ?>><br>
                <input type="radio" name="group_sources" value="t" <?= $groupDb->group_sources == 't' ? 'checked' : ''; ?>><br>
                <input type="radio" name="group_sources" value="j" <?= $groupDb->group_sources == 'j' ? 'checked' : ''; ?>><br>
            </td>
        </tr>

        <!-- First default presentation of sources, by administrator (visitor can override value) -->
        <tr>
            <td><?= __('Default presentation of source'); ?></td>
            <td>
                <select size="1" name="group_source_presentation">
                    <option value="title" <?= $groupDb->group_source_presentation == 'title' ? 'selected' : ''; ?>><?= __('Show source'); ?></option>
                    <option value="footnote" <?= $groupDb->group_source_presentation == 'footnote' ? 'selected' : ''; ?>><?= __('Show source as footnote'); ?></option>
                    <option value="hide" <?= $groupDb->group_source_presentation == 'hide' ? 'selected' : ''; ?>><?= __('Hide sources'); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <td><?= __('Show restricted source'); ?></td>
            <td><input type="checkbox" name="group_show_restricted_source" <?= $groupDb->group_show_restricted_source != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr class="table_header">
            <th><?= __('Texts'); ?></th>
            <th><input type="submit" name="group_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <!-- First default presentation of texts, by administrator (visitor can override value) -->
        <tr>
            <td><?= __('Default presentation of text'); ?></td>
            <td>
                <select size="1" name="group_text_presentation">
                    <option value="show" <?= $groupDb->group_text_presentation == 'show' ? 'selected' : ''; ?>><?= __('Show texts'); ?></option>
                    <option value="popup" <?= $groupDb->group_text_presentation == 'popup' ? 'selected' : ''; ?>><?= __('Show texts in popup screen'); ?></option>
                    <option value="hide" <?= $groupDb->group_text_presentation == 'hide' ? 'selected' : ''; ?>><?= __('Hide texts'); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <td><?= __('Show hidden text/ own remarks (text between # characters in text fields, example: #check birthday#)'); ?></td>
            <td><input type="checkbox" name="group_work_text" <?= $groupDb->group_work_text != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td>
                <?php
                // *** SPARE ITEM ***
                echo '<input type="hidden" name="group_texts" value="j">';
                //echo '<tr><td>'.__('Show text at wedding [NOT YET IN USE]').'</td>';
                //echo '<td><select size="1" name="group_texts"><option value="j">'.__('Yes').'</option>';
                //$selected=''; if ($groupDb->group_texts=='n'){ $selected=' selected'; }
                //echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';
                ?>
                <?= __('Show text with person'); ?>
            </td>
            <td><input type="checkbox" name="group_text_pers" <?= $groupDb->group_text_pers != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show text with bapt., birth, death, cemetery'); ?></td>
            <td><input type="checkbox" name="group_texts_pers" <?= $groupDb->group_texts_pers != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('Show text with pre-nuptial etc.'); ?></td>
            <td><input type="checkbox" name="group_texts_fam" <?= $groupDb->group_texts_fam != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr class="table_header">
            <th><?= __('Privacy filter'); ?></th>
            <th><input type="submit" name="group_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <tr>
            <th><?= __('Activate privacy filter'); ?></th>
            <td></td>
        </tr>

        <tr>
            <td><?= __('Activate privacy filter'); ?><br>
                <i><?= __('TIP: the best privacy filter is your genealogy program<br>
If possible, try to filter with that'); ?></i>
            </td>
            <!-- BE AWARE: REVERSED CHECK OF VARIABLE! -->
            <td><input type="checkbox" name="group_privacy" <?= $groupDb->group_privacy == 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <th><?= __('Privacy filter settings'); ?></th>
            <td></td>
        </tr>

        <tr>
            <td>1)
                <?php printf(__('%s (alive or deceased), Aldfaer (death sign), Haza-data (filter living persons)'), 'HuMo-genealogy'); ?>
            </td>
            <td><input type="checkbox" name="group_alive" <?= $groupDb->group_alive != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td>2) <?= __('Privacy filter, filter persons born in or after this year'); ?></td>
            <td>
                <input type="checkbox" name="group_alive_date_act" <?= $groupDb->group_alive_date_act != 'n' ? 'checked' : ''; ?>>
                <?= __('Year'); ?>: <input type="text" name="group_alive_date" value="<?= $groupDb->group_alive_date; ?>" size="4">
            </td>
        </tr>

        <tr>
            <td>3) <?= __('Privacy filter, filter persons deceased in or after this year'); ?></td>
            <td>
                <input type="checkbox" name="group_death_date_act" <?= $groupDb->group_death_date_act != 'n' ? 'checked' : ''; ?>>
                <?= __('Year'); ?>: <input type="text" name="group_death_date" value="<?= $groupDb->group_death_date; ?>" size="4">
            </td>
        </tr>

        <tr>
            <td><?= __('Also filter data of deceased persons (for filter 2)'); ?></td>
            <td><input type="checkbox" name="group_filter_death" <?= $groupDb->group_filter_death != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <th><?= __('Privacy filter exceptions'); ?></th>
            <td></td>
        </tr>

        <tr>
            <td><?= __('DO show privacy data of persons (with the following text in own code)'); ?></td>
            <td>
                <input type="checkbox" name="group_filter_pers_show_act" <?= $groupDb->group_filter_pers_show_act != 'n' ? 'checked' : ''; ?>>
                <?= __('Text'); ?>: <input type="text" name="group_filter_pers_show" value="<?= $groupDb->group_filter_pers_show; ?>" size="10">
            </td>
        </tr>

        <tr>
            <td><?= __('HIDE privacy data of persons (with the following text in own code)'); ?></td>
            <td>
                <input type="checkbox" name="group_filter_pers_hide_act" <?= $groupDb->group_filter_pers_hide_act != 'n' ? 'checked' : ''; ?>>
                <?= __('Text'); ?>: <input type="text" name="group_filter_pers_hide" value="<?= $groupDb->group_filter_pers_hide; ?>" size="10">
            </td>
        </tr>

        <tr>
            <td><?= __('TOTALLY filter persons (with the following text in own code)'); ?></td>
            <td>
                <input type="checkbox" name="group_pers_hide_totally_act" <?= $groupDb->group_pers_hide_totally_act != 'n' ? 'checked' : ''; ?>>
                <?= __('Text'); ?>: <input type="text" name="group_pers_hide_totally" value="<?= $groupDb->group_pers_hide_totally; ?>" size="10">
            </td>
        </tr>

        <tr>
            <th><?= __('Extra privacy filter option'); ?></th>
            <td></td>
        </tr>

        <tr>
            <td><?= __('Show persons with no date information<br>
<i>with these persons the privacy filter cannot calculate if they are alive</i>'); ?></td>
            <td><input type="checkbox" name="group_filter_date" <?= $groupDb->group_filter_date != 'n' ? 'checked' : ''; ?>></td>
        </tr>

        <tr>
            <td><?= __('With privacy show names'); ?></td>
            <td>
                <select size="1" name="group_filter_name">
                    <option value="j"><?= __('Yes'); ?></option>
                    <option value="n" <?= $groupDb->group_filter_name == 'n' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                    <option value="i" <?= $groupDb->group_filter_name == 'i' ? 'selected' : ''; ?>><?= __('Show initials: D. E. Duck'); ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <td><?= __('Genealogical copy protection<br>
<i>family browsing disabled, no family trees</i>'); ?></td>
            <?php
            $check = '';
            if ($groupDb->group_gen_protection != 'n') $check = ' checked';
            ?>
            <td><input type="checkbox" name="group_gen_protection" <?= $check; ?>></td>
        </tr>

        <tr class="table_header">
            <th>
                <?php
                // *** SPARE ITEM ***
                echo '<input type="hidden" name="group_filter_fam" value="n">';
                //echo '<tr><td>'.__('Filter family').'</td>';
                //echo '<td><select size="1" name="group_filter_fam"><option value="j">'.__('Yes').'</option>';
                //$selected=''; if ($groupDb->group_filter_fam=='n'){ $selected=' selected'; }
                //echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

                // *** SPARE ITEM ***
                echo '<input type="hidden" name="group_filter_total" value="n">';
                //echo '<tr><td>'.__('Filter totally').'</td>';
                //echo '<td><select size="1" name="group_filter_total"><option value="j">'.__('Yes').'</option>';
                //$selected=''; if ($groupDb->group_filter_total=='n'){ $selected=' selected'; }
                //echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';
                ?>

                <?= __('Save all changes'); ?></th>
            <th><input type="submit" name="group_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>
    </table>

    <?php
    // *** User settings per family tree (hide or show tree, edit tree etc.) ***
    $hide_tree_array = explode(";", $groupDb->group_hide_trees);
    $edit_tree_array = explode(";", $groupDb->group_edit_trees);

    // *** Update tree settings ***
    //if (isset($_POST['group_change']) and is_numeric($_POST["id"])) {
    if (isset($_POST['group_change']) and is_numeric($_POST["group_id"])) {
        $group_hide_trees = '';
        $group_edit_trees = '';
        $data3sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY'");
        while ($data3Db = $data3sql->fetch(PDO::FETCH_OBJ)) {
            // *** Show/ hide trees ***
            $check = 'show_tree_' . $data3Db->tree_id;
            if (!isset($_POST["$check"])) {
                if ($group_hide_trees != '') {
                    $group_hide_trees .= ';';
                }
                $group_hide_trees .= $data3Db->tree_id;
            }

            // *** Edit trees (NOT USED FOR ADMINISTRATOR) ***
            $check = 'edit_tree_' . $data3Db->tree_id;
            if (isset($_POST["$check"])) {
                if ($group_edit_trees != '') {
                    $group_edit_trees .= ';';
                }
                $group_edit_trees .= $data3Db->tree_id;
            }
        }
        $sql = "UPDATE humo_groups SET group_hide_trees='" . $group_hide_trees . "',  group_edit_trees='" . $group_edit_trees . "' WHERE group_id=" . $_POST["group_id"];
        $result = $dbh->query($sql);

        $hide_tree_array = explode(";", $group_hide_trees);
        $edit_tree_array = explode(";", $group_edit_trees);
    }
    ?>

    <h2 align="center"><?= __('Hide or show family trees per user group.'); ?></h2>
    <?= __('Editor') . ': ' . __('If an .htpasswd file is used: add username in .htpasswd file.'); ?><br>
    <?= __('These settings can also be set per user!'); ?>

    <table class="humo standard" border="1">
        <tr class="table_header">
            <th><?= __('Family tree'); ?></th>
            <th><?= __('Show tree?'); ?></th>
            <th><?= __('Edit tree?'); ?> <input type="submit" name="group_change" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>
        <?php
        $data3sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($data3Db = $data3sql->fetch(PDO::FETCH_OBJ)) {
            $treetext = show_tree_text($data3Db->tree_id, $selected_language);
            $treetext_name = $treetext['name'];
        ?>
            <tr>
                <td><?= $data3Db->tree_id; ?> <?= $treetext_name; ?></td>
                <?php
                // *** Show/ hide tree for user ***
                $check = ' checked';
                if (in_array($data3Db->tree_id, $hide_tree_array)) $check = '';
                ?>
                <td><input type="checkbox" name="show_tree_<?= $data3Db->tree_id; ?>" <?= $check; ?>></td>

                <td>
                    <?php
                    // *** Editor rights per family tree (NOT USED FOR ADMINISTRATOR) ***
                    $check = '';
                    if (in_array($data3Db->tree_id, $edit_tree_array)) $check = ' checked';
                    $disabled = '';
                    if ($groupDb->group_admin == 'j') {
                        $check = ' checked';
                        $disabled = ' disabled';
                        echo '<input type="hidden" name="edit_tree_' . $data3Db->tree_id . '" value="1">';
                    }
                    ?>
                    <input type="checkbox" name="edit_tree_<?= $data3Db->tree_id; ?>" <?= $check . $disabled; ?>>
                </td>
            </tr>
        <?php
        }
        ?>
    </table>

    <?php
    // *** Photo categories ***
    // *** User settings per photo category ***
    $hide_photocat_array = explode(";", $groupDb->group_hide_photocat);

    // *** Update photocat settings ***
    $table_exists = $dbh->query("SHOW TABLES LIKE 'humo_photocat'")->rowCount() > 0;
    if ($table_exists and isset($_POST['change_photocat']) and is_numeric($_POST["id"])) {
        /*
        $group_hide_photocat='';
        $data3sql = $dbh->query("SELECT * FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order");
        while($data3Db=$data3sql->fetch(PDO::FETCH_OBJ)){
            // *** Show/ hide categories ***
            $check='show_photocat_'.$data3Db->photocat_id;
            if (!isset($_POST["$check"])){
                if ($group_hide_photocat!=''){ $group_hide_photocat.=';'; }
                $group_hide_photocat.=$data3Db->photocat_id;
            }
        }
        */

        $group_hide_photocat = '';
        $photocat_prefix_array[] = '';
        // *** Can't use GROUP BY in this querie because we need multiple fields (not allowed in MySQL 5.7) ***
        $data3sql = $dbh->query("SELECT * FROM humo_photocat ORDER BY photocat_order");
        while ($data3Db = $data3sql->fetch(PDO::FETCH_OBJ)) {
            // *** Only use first found prefix ***
            if (!in_array($data3Db->photocat_prefix, $photocat_prefix_array)) {
                $photocat_prefix_array[] = $data3Db->photocat_prefix;

                // *** Show/ hide categories ***
                $check = 'show_photocat_' . $data3Db->photocat_id;
                if (!isset($_POST["$check"])) {
                    if ($group_hide_photocat != '') {
                        $group_hide_photocat .= ';';
                    }
                    $group_hide_photocat .= $data3Db->photocat_id;
                }
            }
        }
        // *** Remove array, so it can be re-used ***
        unset($photocat_prefix_array);

        $sql = "UPDATE humo_groups SET group_hide_photocat='" . $group_hide_photocat . "'  WHERE group_id=" . $_POST["id"];
        $result = $dbh->query($sql);

        $hide_photocat_array = explode(";", $group_hide_photocat);
    }
    ?>

    <h2 align="center"><?= __('Hide or show photo categories per user group.'); ?></h2>
    <table class="humo standard" border="1">
        <tr class="table_header">
            <th><?= __('Category prefix'); ?></th>
            <th><?= __('Show category?'); ?> <input type="submit" name="change_photocat" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <?php
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
        if ($temp->rowCount()) {   // a humo_photocat table exists
            /*
            $data3sql = $dbh->query("SELECT * FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order");
            // MySQL 5.7: doesn't work yet:
            //$data3sql = $dbh->query("SELECT photocat_id,photocat_prefix FROM humo_photocat GROUP BY photocat_prefix,photocat_id ORDER BY photocat_order");
            while($data3Db=$data3sql->fetch(PDO::FETCH_OBJ)){
                // *** Show/ hide photo categories for user ***
                $check=' checked'; if (in_array($data3Db->photocat_id, $hide_photocat_array)) $check='';
                echo '<tr><td>'.$data3Db->photocat_prefix.'</td>';
                echo '<td><input type="checkbox" name="show_photocat_'.$data3Db->photocat_id.'"'.$check.'></td></tr>';
            }
            */

            // *** Can't do GROUP BY because we need multiple fields and MySQL 5.7 doesn't like that ***
            $data3sql = $dbh->query("SELECT * FROM humo_photocat ORDER BY photocat_order");
            $photocat_prefix_array[] = '';
            while ($data3Db = $data3sql->fetch(PDO::FETCH_OBJ)) {
                // *** Only use first found prefix ***
                if (!in_array($data3Db->photocat_prefix, $photocat_prefix_array)) {
                    $photocat_prefix_array[] = $data3Db->photocat_prefix;
                    // *** Show/ hide photo categories for user ***
                    $check = ' checked';
                    if (in_array($data3Db->photocat_id, $hide_photocat_array)) $check = '';
                    echo '<tr><td>' . $data3Db->photocat_prefix . '</td>';
                    echo '<td><input type="checkbox" name="show_photocat_' . $data3Db->photocat_id . '"' . $check . '></td></tr>';
                }
            }
        } else
            echo '<tr><td colspan="2">' . __('No photo categories available.') . '</td></tr>';
        ?>
    </table>
</form>