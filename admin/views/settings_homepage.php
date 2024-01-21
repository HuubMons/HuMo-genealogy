<?php

// *** Reset all modules ***
if (isset($_GET['template_homepage_reset']) and $_GET['template_homepage_reset'] == '1') {
    $sql = "DELETE FROM humo_settings WHERE setting_variable='template_homepage'";
    $result = $dbh->query($sql);

    // *** Reload page to get new values ***
    echo '<script> window.location="index.php?page=settings&menu_admin=settings_homepage";</script>';
}

// *** Change Module ***
if (isset($_POST['change_module'])) {
    $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage'");
    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
        $setting_value = $_POST[$dataDb->setting_id . 'module_status'] . '|' . $_POST[$dataDb->setting_id . 'module_column'] . '|' . $_POST[$dataDb->setting_id . 'module_item'];
        if (isset($_POST[$dataDb->setting_id . 'module_option_1'])) $setting_value .= '|' . $_POST[$dataDb->setting_id . 'module_option_1'];
        if (isset($_POST[$dataDb->setting_id . 'module_option_2'])) $setting_value .= '|' . $_POST[$dataDb->setting_id . 'module_option_2'];
        $sql = "UPDATE humo_settings SET setting_value='" . safe_text_db($setting_value) . "' WHERE setting_id=" . safe_text_db($_POST[$dataDb->setting_id . 'id']);
        //echo $sql.'<br>';
        $result = $dbh->query($sql);
    }
}

// *** Remove module  ***
if (isset($_GET['remove_module']) and is_numeric($_GET['remove_module'])) {
    $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_id='" . $_GET['remove_module'] . "'");
    $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
    $sql = "DELETE FROM humo_settings WHERE setting_id='" . $dataDb->setting_id . "'";
    $result = $dbh->query($sql);

    // *** Re-order links ***
    $repair_order = $dataDb->setting_order;
    $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order>" . $repair_order);
    while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
        $sql = "UPDATE humo_settings SET setting_order='" . ($itemDb->setting_order - 1) . "' WHERE setting_id=" . $itemDb->setting_id;
        $result = $dbh->query($sql);
    }
}

// *** Add module ***
if (isset($_POST['add_module']) and is_numeric($_POST['module_order'])) {
    $setting_value = $_POST['module_status'] . "|" . $_POST['module_column'] . "|" . $_POST['module_item'];
    $sql = "INSERT INTO humo_settings SET setting_variable='template_homepage',
        setting_value='" . safe_text_db($setting_value) . "', setting_order='" . safe_text_db($_POST['module_order']) . "'";
    $result = $dbh->query($sql);
}

if (isset($_GET['mod_up'])) {
    // *** Search previous module ***
    $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order=" . (safe_text_db($_GET['module_order']) - 1));
    $itemDb = $item->fetch(PDO::FETCH_OBJ);

    // *** Raise previous module ***
    $sql = "UPDATE humo_settings SET setting_order='" . safe_text_db($_GET['module_order']) . "' WHERE setting_id='" . $itemDb->setting_id . "'";
    $result = $dbh->query($sql);

    // *** Lower module order ***
    $sql = "UPDATE humo_settings SET setting_order='" . (safe_text_db($_GET['module_order']) - 1) . "' WHERE setting_id=" . safe_text_db($_GET['id']);
    $result = $dbh->query($sql);
}
if (isset($_GET['mod_down'])) {
    // *** Search next link ***
    $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order=" . (safe_text_db($_GET['module_order']) + 1));
    $itemDb = $item->fetch(PDO::FETCH_OBJ);

    // *** Lower previous link ***
    $sql = "UPDATE humo_settings SET setting_order='" . safe_text_db($_GET['module_order']) . "' WHERE setting_id='" . $itemDb->setting_id . "'";
    $result = $dbh->query($sql);

    // *** Raise link order ***
    $sql = "UPDATE humo_settings SET setting_order='" . (safe_text_db($_GET['module_order']) + 1) . "' WHERE setting_id=" . safe_text_db($_GET['id']);
    $result = $dbh->query($sql);
}


// *** Automatic group all items: left, center and right items. So it's easier to move items ***
$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
$left = 0;
$center = 0;
$right = 0;
if ($datasql) {
    $teller = 0;
    // *** Read all items ***
    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
        $dataDb->setting_value .= '|'; // In some cases the last | is missing. TODO: improve saving of settings.
        $lijst = explode("|", $dataDb->setting_value);
        if ($lijst[1] == 'left') {
            $left++;
        }
        if ($lijst[1] == 'center') {
            $center++;
        }
        if ($lijst[1] == 'right') {
            $right++;
        }
        $item_array[$teller]['id'] = $dataDb->setting_id;
        $item_array[$teller]['column'] = $lijst[1];
        $item_array[$teller]['order'] = $dataDb->setting_order;
        $teller++;
    }
}
$count_left = 0;
$count_center = $left;
$count_right = $left + $center;
// *** Reorder all items (if new items is added) ***
for ($i = 0; $i < count($item_array); $i++) {
    if ($item_array[$i]['column'] == 'left') {
        $count_left++;
        if ($item_array[$i]['order'] != $count_left) {
            $sql = "UPDATE humo_settings SET setting_order='" . $count_left . "' WHERE setting_id='" . $item_array[$i]['id']."'";
            $result = $dbh->query($sql);        
        }
    }

    if ($item_array[$i]['column'] == 'center') {
        $count_center++;
        if ($item_array[$i]['order'] != $count_center) {
            $sql = "UPDATE humo_settings SET setting_order='" . $count_center . "' WHERE setting_id='" . $item_array[$i]['id']."'";
            $result = $dbh->query($sql);        
        }
    }

    if ($item_array[$i]['column'] == 'right') {
        $count_right++;
        if ($item_array[$i]['order'] != $count_right) {
            $sql = "UPDATE humo_settings SET setting_order='" . $count_right . "' WHERE setting_id='" . $item_array[$i]['id']."'";
            $result = $dbh->query($sql);        
        }
    }
}

// *** Show all links ***
?>
<form method="post" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="menu_admin" value="settings_homepage">
    <table class="humo" border="1">
        <tr class="table_header_large">
            <th class="table_header" colspan="7"><? __('Homepage template'); ?>
                <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;template_homepage_reset=1">[<?= __('Default settings'); ?>]</a>
            </th>
        </tr>

        <tr class="table_header">
            <th><?= __('Status'); ?></th>
            <th><?= __('Position'); ?></th>
            <th><?= __('Item'); ?></th>
            <th><br></th>
            <th><input type="submit" name="change_module" value="<?= __('Change'); ?>"></th>
        </tr>

        <?php
        $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
        // *** Number for new module ***
        $count_links = 0;
        if ($datasql->rowCount()) $count_links = $datasql->rowCount();
        $new_number = 1;
        if ($count_links) $new_number = $count_links + 1;
        if ($datasql) {
            $teller = 1;
            while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                $dataDb->setting_value .= '|'; // In some cases the last | is missing. TODO: improve saving of settings.
                $lijst = explode("|", $dataDb->setting_value);
                // *** Just to prevent error messages, set a default value ***
                if (!isset($lijst[3])) $lijst[3] = '';
                if (!isset($lijst[4])) $lijst[3] = '';
        ?>
                <tr>
                    <!-- Active/ inactive with background colour -->
                    <td <?php if ($lijst[0] == 'inactive') echo 'bgcolor="orange"'; ?>>
                        <input type="hidden" name="<?= $dataDb->setting_id; ?>id" value="<?= $dataDb->setting_id; ?>">
                        <select size="1" name="<?= $dataDb->setting_id; ?>module_status">
                            <option value="active"><?= __('Active'); ?></option>
                            <option value="inactive" <?php if ($lijst[0] == 'inactive') echo ' selected'; ?>><?= __('Inactive'); ?></option>
                        </select>
                    </td>

                    <!-- TODO use seperate blocks for editing left/center/right items -->
                    <td>
                        <select size="1" name="<?= $dataDb->setting_id; ?>module_column">
                            <option value="left"><?= __('Left'); ?></option>
                            <option value="center" <?php if ($lijst[1] == 'center') echo ' selected'; ?>><?= __('Center'); ?></option>
                            <option value="right" <?php if ($lijst[1] == 'right') echo ' selected'; ?>><?= __('Right'); ?></option>
                        </select>
                        <?php

                        if ($dataDb->setting_order != '1') {
                            echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;mod_up=1&amp;module_order=' . $dataDb->setting_order .
                                '&amp;id=' . $dataDb->setting_id . '"><img src="images/arrow_up.gif" border="0" alt="up"></a>';
                        }
                        if ($dataDb->setting_order != $count_links) {
                            echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;mod_down=1&amp;module_order=' . $dataDb->setting_order . '&amp;id=' .
                                $dataDb->setting_id . '"><img src="images/arrow_down.gif" border="0" alt="down"></a>';
                        }
                        ?>
                    </td>

                    <td>
                        <select size="1" name="<?= $dataDb->setting_id; ?>module_item">
                            <option value="select_family_tree"><?= __('Select family tree'); ?></option>
                            <option value="selected_family_tree" <?php if ($lijst[2] == 'selected_family_tree') echo ' selected'; ?>><?= __('Selected family tree'); ?></option>
                            <option value="search" <?php if ($lijst[2] == 'search') echo ' selected'; ?>><?= __('Search'); ?></option>
                            <option value="names" <?php if ($lijst[2] == 'names') echo ' selected'; ?>><?= __('Names'); ?></option>
                            <option value="history" <?php if ($lijst[2] == 'history') echo ' selected'; ?>><?= __('Today in history'); ?></option>
                            <option value="favourites" <?php if ($lijst[2] == 'favourites') echo ' selected'; ?>><?= __('Favourites'); ?></option>
                            <option value="alphabet" <?php if ($lijst[2] == 'alphabet') echo ' selected'; ?>><?= __('Surnames Index'); ?></option>
                            <option value="random_photo" <?php if ($lijst[2] == 'random_photo') echo ' selected'; ?>><?= __('Random photo'); ?></option>
                            <option value="text" <?php if ($lijst[2] == 'text') echo ' selected'; ?>><?= __('Text'); ?></option>
                            <option value="own_script" <?php if ($lijst[2] == 'own_script') echo ' selected'; ?>><?= __('Own script'); ?></option>
                            <option value="cms_page" <?php if ($lijst[2] == 'cms_page') echo ' selected'; ?>><?= __('CMS Own pages'); ?></option>
                            <option value="empty_line" <?php if ($lijst[2] == 'empty_line') echo ' selected'; ?>><?= __('EMPTY LINE'); ?></option>
                        </select>
                    </td>

                    <!-- Extra table column used for extra options -->
                    <td>
                        <?php
                        //if ($lijst[2]=='select_family_tree'){
                        //	echo ' '.__('Only use for multiple family trees.');
                        //}

                        if ($lijst[2] == 'names') {
                            echo ' ' . __('Columns');
                            echo ' <select size="1" name="' . $dataDb->setting_id . 'module_option_1">';
                            echo '<option value="1">1</option>';
                            $selected = '';
                            if ($lijst[3] == '2') $selected = ' selected';
                            echo '<option value="2"' . $selected . '>2</option>';
                            $selected = '';
                            if ($lijst[3] == '3') $selected = ' selected';
                            echo '<option value="3"' . $selected . '>3</option>';
                            $selected = '';
                            if ($lijst[3] == '4') $selected = ' selected';
                            echo '<option value="4"' . $selected . '>4</option>';
                            echo '</select>';

                            echo ' ' . __('Rows');
                            echo ' <select size="1" name="' . $dataDb->setting_id . 'module_option_2">';
                            echo '<option value="1">1</option>';
                            $selected = '';
                            if ($lijst[4] == '2') $selected = ' selected';
                            echo '<option value="2"' . $selected . '>2</option>';
                            $selected = '';
                            if ($lijst[4] == '3') $selected = ' selected';
                            echo '<option value="3"' . $selected . '>3</option>';
                            $selected = '';
                            if ($lijst[4] == '4') $selected = ' selected';
                            echo '<option value="4"' . $selected . '>4</option>';
                            $selected = '';
                            if ($lijst[4] == '5') $selected = ' selected';
                            echo '<option value="5"' . $selected . '>5</option>';
                            $selected = '';
                            if ($lijst[4] == '6') $selected = ' selected';
                            echo '<option value="6"' . $selected . '>6</option>';
                            $selected = '';
                            if ($lijst[4] == '7') $selected = ' selected';
                            echo '<option value="7"' . $selected . '>7</option>';
                            $selected = '';
                            if ($lijst[4] == '8') $selected = ' selected';
                            echo '<option value="8"' . $selected . '>8</option>';
                            $selected = '';
                            if ($lijst[4] == '9') $selected = ' selected';
                            echo '<option value="9"' . $selected . '>9</option>';
                            $selected = '';
                            if ($lijst[4] == '10') $selected = ' selected';
                            echo '<option value="10"' . $selected . '>10</option>';
                            $selected = '';
                            if ($lijst[4] == '11') $selected = ' selected';
                            echo '<option value="11"' . $selected . '>11</option>';
                            $selected = '';
                            if ($lijst[4] == '12') $selected = ' selected';
                            echo '<option value="12"' . $selected . '>12</option>';
                            echo '</select>';
                        }

                        if ($lijst[2] == 'text') {
                            // *** Header text ***
                            $header = '';
                            if (isset($lijst[3])) $header = $lijst[3];
                            echo '<input type="text" placeholder="' . __('Header') . '" name="' . $dataDb->setting_id . 'module_option_1" value="' . $header . '" size="30"><br>';

                            $module_text = '';
                            if (isset($lijst[4])) $module_text = $lijst[4];
                            echo '<textarea rows="4" cols="50" placeholder="' . __('Text') . '" name="' . $dataDb->setting_id . 'module_option_2">' . $module_text . '</textarea><br>';

                            echo __('Show text block, HTML codes can be used.');
                        }

                        if ($lijst[2] == 'own_script') {
                            // *** Header text ***
                            $header = '';
                            if (isset($lijst[3])) $header = $lijst[3];
                            echo '<input type="text" placeholder="' . __('Header') . '" name="' . $dataDb->setting_id . 'module_option_1" value="' . $header . '" size="30"><br>';
                            $module_text = '';
                            if (isset($lijst[4])) $module_text = $lijst[4];
                            echo '<input type="text" placeholder="' . __('File name') . '" name="' . $dataDb->setting_id . 'module_option_2" value="' . $module_text . '" size="30"><br>';
                            echo __('File name (full path) of the file with own script.');
                        }

                        if ($lijst[2] == 'cms_page') {
                            echo ' <select size="1" name="' . $dataDb->setting_id . 'module_option_1">';
                            $qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
                            while ($pageDb = $qry->fetch(PDO::FETCH_OBJ)) {
                                //$select=''; if ($lijst[3]==$pageDb->setting_id.'module_option_1'){ $select=' selected'; }
                                $selected = '';
                                if ($lijst[3] == $pageDb->page_id) {
                                    $selected = ' selected';
                                }
                                echo '<option value="' . $pageDb->page_id . '"' . $selected . '>' . $pageDb->page_title . '</option>';
                            }
                            echo '</select>';
                            echo ' ' . __('Show text from CMS system.');
                        }

                        if ($lijst[2] == 'history') {
                            echo ' ' . __('View');
                            echo ' <select size="1" name="' . $dataDb->setting_id . 'module_option_1">';
                            echo '<option value="with_table">' . __('with table') . '</option>';

                            $selected = '';
                            if ($lijst[3] == 'without_table') $selected = ' selected';
                            echo '<option value="without_table"' . $selected . '>' . __('without table') . '</option>';
                            echo '</select>';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_module=<?= $dataDb->setting_id; ?>">
                            <img src="images/button_drop.png" border="0" alt="remove"></a>
                    </td>
                </tr>
            <?php
                $teller++;
            }

            ?>
            <!-- Add new module -->
            <tr bgcolor="green">
                <input type="hidden" name="module_order" value="<?= $new_number; ?>">
                <td>
                    <select size="1" name="module_status">
                        <option value="active"><?= __('Active'); ?></option>
                        <option value="inactive"><?= __('Inactive'); ?></option>
                    </select>
                </td>

                <td>
                    <select size="1" name="module_column">
                        <option value="left"><?= __('Left'); ?></option>
                        <option value="center"><?= __('Center'); ?></option>
                        <option value="right"><?= __('Right'); ?></option>
                    </select>
                </td>

                <td>
                    <select size="1" name="module_item">
                        <option value="select_family_tree"><?= __('Select family tree'); ?></option>
                        <option value="selected_family_tree"><?= __('Selected family tree'); ?></option>
                        <option value="search"><?= __('Search'); ?></option>
                        <option value="names"><?= __('Names'); ?></option>
                        <option value="history"><?= __('Today in history'); ?></option>
                        <option value="favourites"><?= __('Favourites'); ?></option>
                        <option value="alphabet"><?= __('Surnames Index'); ?></option>
                        <option value="random_photo"><?= __('Random photo'); ?></option>
                        <option value="text"><?= __('Text'); ?></option>
                        <option value="own_script"><?= __('Own script'); ?></option>
                        <option value="cms_page"><?= __('CMS Own pages'); ?></option>
                        <option value="empty_line"><?= __('EMPTY LINE'); ?></option>
                    </select>
                </td>

                <td><br></td>

                <td><input type="submit" name="add_module" value="<?= __('Add'); ?>"></td>
            </tr>
        <?php
        } else {
            echo '<tr><td colspan="4">' . __('Database is not yet available.') . '</td></tr>';
        }
        ?>
    </table>
</form>

<?= __("If the left column isn't used, the center column will be made large automatically."); ?><br>
<?php
// *** Change Link ***
if (isset($_POST['change_link'])) {
    $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
        $setting_value = $_POST[$dataDb->setting_id . 'own_code'] . "|" . $_POST[$dataDb->setting_id . 'link_text'];
        $sql = "UPDATE humo_settings SET setting_value='" . safe_text_db($setting_value) . "' WHERE setting_id=" . safe_text_db($_POST[$dataDb->setting_id . 'id']);
        $result = $dbh->query($sql);
    }
}

// *** Remove link  ***
if (isset($_GET['remove_link']) and is_numeric($_GET['remove_link'])) {
    $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_id='" . $_GET['remove_link'] . "'");
    $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
    $sql = "DELETE FROM humo_settings WHERE setting_id='" . $dataDb->setting_id . "'";
    $result = $dbh->query($sql);

    // *** Re-order links ***
    $repair_order = $dataDb->setting_order;
    $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order>" . $repair_order);
    while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
        $sql = "UPDATE humo_settings SET setting_order='" . ($itemDb->setting_order - 1) . "' WHERE setting_id=" . $itemDb->setting_id;
        $result = $dbh->query($sql);
    }
}

// *** Add link ***
if (isset($_POST['add_link']) and is_numeric($_POST['link_order'])) {
    $setting_value = $_POST['own_code'] . "|" . $_POST['link_text'];
    $sql = "INSERT INTO humo_settings SET setting_variable='link',
        setting_value='" . safe_text_db($setting_value) . "', setting_order='" . safe_text_db($_POST['link_order']) . "'";
    $result = $dbh->query($sql);
}

if (isset($_GET['up'])) {
    // *** Search previous link ***
    $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order=" . (safe_text_db($_GET['link_order']) - 1));
    $itemDb = $item->fetch(PDO::FETCH_OBJ);

    // *** Raise previous link ***
    $sql = "UPDATE humo_settings SET setting_order='" . safe_text_db($_GET['link_order']) . "' WHERE setting_id='" . $itemDb->setting_id . "'";

    $result = $dbh->query($sql);
    // *** Lower link order ***
    $sql = "UPDATE humo_settings SET setting_order='" . (safe_text_db($_GET['link_order']) - 1) . "' WHERE setting_id=" . safe_text_db($_GET['id']);

    $result = $dbh->query($sql);
}
if (isset($_GET['down'])) {
    // *** Search next link ***
    $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order=" . (safe_text_db($_GET['link_order']) + 1));
    $itemDb = $item->fetch(PDO::FETCH_OBJ);

    // *** Lower previous link ***
    $sql = "UPDATE humo_settings SET setting_order='" . safe_text_db($_GET['link_order']) . "' WHERE setting_id='" . $itemDb->setting_id . "'";

    $result = $dbh->query($sql);
    // *** Raise link order ***
    $sql = "UPDATE humo_settings SET setting_order='" . (safe_text_db($_GET['link_order']) + 1) . "' WHERE setting_id=" . safe_text_db($_GET['id']);

    $result = $dbh->query($sql);
}

// *** Show all links ***
?>
<h1 align=center><?= __('Homepage favourites'); ?></h1>

<form method="post" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="menu_admin" value="settings_homepage">

    <table class="humo standard" border="1">
        <tr class="table_header_large">
            <th class="table_header" colspan="4"><?= __('Show list of favourites in homepage'); ?></th>
        </tr>

        <tr class="table_header">
            <th>Nr.</th>
            <th><?= __('Own code'); ?></th>
            <th><?= __('Description'); ?></th>
            <th><input type="submit" name="change_link" value="<?= __('Change'); ?>"></th>
        </tr>
        <?php
        $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' ORDER BY setting_order");
        // *** Number for new link ***
        $count_links = 0;
        if ($datasql->rowCount()) $count_links = $datasql->rowCount();
        $new_number = 1;
        if ($count_links) $new_number = $count_links + 1;
        if ($datasql) {
            $teller = 1;
            while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                $lijst = explode("|", $dataDb->setting_value);
        ?>
                <tr>
                    <td>
                        <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_link=<?= $dataDb->setting_id; ?>">
                            <img src="images/button_drop.png" border="0" alt="remove"></a>

                        <input type="hidden" name="<?= $dataDb->setting_id; ?>id" value="<?= $dataDb->setting_id; ?>"><?= __('Link') . ' ' . $teller; ?>
                        <?php
                        if ($dataDb->setting_order != '1') {
                            echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;up=1&amp;link_order=' . $dataDb->setting_order .
                                '&amp;id=' . $dataDb->setting_id . '"><img src="images/arrow_up.gif" border="0" alt="up"></a>';
                        }
                        if ($dataDb->setting_order != $count_links) {
                            echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;down=1&amp;link_order=' . $dataDb->setting_order . '&amp;id=' .
                                $dataDb->setting_id . '"><img src="images/arrow_down.gif" border="0" alt="down"></a>';
                        }
                        ?>
                    </td>
                    <td><input type="text" name="<?= $dataDb->setting_id; ?>own_code" value="<?= $lijst[0]; ?>" size="5"></td>
                    <td><input type="text" name="<?= $dataDb->setting_id; ?>link_text" value="<?= $lijst[1]; ?>" size="20"></td>
                    <td><br></td>
                </tr>
            <?php
                $teller++;
            }

            // *** Add new link ***
            ?>
            <tr bgcolor="green">
                <td><br></td>
                <input type="hidden" name="link_order" value="<?= $new_number; ?>">
                <td><input type="text" name="own_code" value="Code" size="5"></td>
                <td><input type="text" name="link_text" value="<?= __('Owner of tree'); ?>" size="20"></td>
                <td><input type="submit" name="add_link" value="<?= __('Add'); ?>"></td>
            </tr>
        <?php
        } else {
            echo '<tr><td colspan="4">' . __('Database is not yet available.') . '</td></tr>';
        }
        ?>
    </table>
</form>

<?= __('Own code is the code that has to be entered in your genealogy program under "own code or REFN"
<p>Do the following:<br>
1) In your genealogy program, put a code. For example, with the patriarch enter a code "patriarch".<br>
2) Enter the same code in this table (multiple codes are possible)<br>
3) After processing the GEDCOM file, an extra link will appear in the main menu, i.e. to the patriarch!<br>'); ?>

<?php
// *** Slideshow ***
$slideshow_01 = explode('|', $humo_option["slideshow_01"]);
$slideshow_02 = explode('|', $humo_option["slideshow_02"]);
$slideshow_03 = explode('|', $humo_option["slideshow_03"]);
$slideshow_04 = explode('|', $humo_option["slideshow_04"]);
?>
<br>
<form method="post" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="menu_admin" value="settings_homepage">
    <table class="humo" border="1">

        <tr class="table_header">
            <th colspan="2"><?= __('Slideshow on the homepage'); ?> <input type="submit" name="save_option2" value="<?= __('Change'); ?>"></th>
        </tr>

        <tr>
            <td colspan="2"><?= __('This option shows a slideshow at the homepage. Put the images in the media/slideshow/ folder at the website.<br>Example of image link:'); ?> <b>media/slideshow/slide01.jpg</b><br>
                <?= __('Images size should be about:'); ?> <b>950 x 170 pixels.</b>
            </td>
        </tr>

        <tr>
            <td style="white-space:nowrap;"><?= __('Show slideshow on the homepage'); ?>?</td>
            <td>
                <select size="1" name="slideshow_show">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["slideshow_show"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </td>
        </tr>

        <!-- Picture 1 -->
        <tr>
            <td><?= __('Link to image'); ?> 1<br><?= __('Link description'); ?> 1</td>
            <td><input type="text" name="slideshow_slide_01" value="<?= $slideshow_01[0]; ?>" size="40"> media/slideshow/slide01.jpg<br>
                <input type="text" name="slideshow_text_01" value="<?= $slideshow_01[1]; ?>" size="40">
            </td>
        </tr>
        <!-- Picture 2 -->
        <tr>
            <td><?= __('Link to image'); ?> 2<br><?= __('Link description'); ?> 2</td>
            <td><input type="text" name="slideshow_slide_02" value="<?= $slideshow_02[0]; ?>" size="40"> media/slideshow/slide02.jpg<br>
                <input type="text" name="slideshow_text_02" value="<?= $slideshow_02[1]; ?>" size="40">
            </td>
        </tr>
        <!-- Picture 3 -->
        <tr>
            <td><?= __('Link to image'); ?> 3<br><?= __('Link description'); ?> 3</td>
            <td><input type="text" name="slideshow_slide_03" value="<?= $slideshow_03[0]; ?>" size="40"> media/slideshow/slide03.jpg<br>
                <input type="text" name="slideshow_text_03" value="<?= $slideshow_03[1]; ?>" size="40">
            </td>
        </tr>
        <!-- Picture 4 -->
        <tr>
            <td><?= __('Link to image'); ?> 4<br><?= __('Link description'); ?> 4</td>
            <td><input type="text" name="slideshow_slide_04" value="<?= $slideshow_04[0]; ?>" size="40"> media/slideshow/slide04.jpg<br>
                <input type="text" name="slideshow_text_04" value="<?= $slideshow_04[1]; ?>" size="40">
            </td>
        </tr>

    </table>
</form><br><br>