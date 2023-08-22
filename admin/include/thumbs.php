<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
//ini_set('memory_limit', '-1');

$prefx = '../'; // to get out of the admin map
$joomlastring = "";

// *** Tab menu ***
$menu_admin = 'picture_settings';
if (isset($_POST['menu_admin'])) {
    $menu_admin = $_POST['menu_admin'];
}
if (isset($_GET['menu_admin'])) {
    $menu_admin = $_GET['menu_admin'];
}
?>
<p>
<div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false">
    <div class="pageHeading">
        <!-- <div class="pageHeadingText">Configuratie gegevens</div> -->
        <!-- <div class="pageHeadingWidgets" aria-hidden="true" style="display: none;"></div> -->
        <div class="pageTabsContainer" aria-hidden="false">
            <ul class="pageTabs">
                <?php
                //echo '<li class="pageTabItem"><div tabindex="0" class="pageTab pageTab-active">Details</div></li>';

                // *** Picture settings ***
                $select_item = '';
                if ($menu_admin == 'picture_settings') {
                    $select_item = ' pageTab-active';
                }
                echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="index.php?' . $joomlastring . 'page=' . $page . '">' . __('Picture settings') . "</a></div></li>";

                // *** Create thumbnails ***
                $select_item = '';
                if ($menu_admin == 'picture_thumbnails') {
                    $select_item = ' pageTab-active';
                }
                echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;menu_admin=picture_thumbnails' . '">' . __('Create thumbnails') . "</a></div></li>";

                // *** Show thumbnails ***
                $select_item = '';
                if ($menu_admin == 'picture_show') {
                    $select_item = ' pageTab-active';
                }
                echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;menu_admin=picture_show">' . __('Show thumbnails') . "</a></div></li>";

                // *** Picture categories ***
                $select_item = '';
                if ($menu_admin == 'picture_categories') {
                    $select_item = ' pageTab-active';
                }
                echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;menu_admin=picture_categories">' . __('Photo album categories') . "</a></div></li>";
                ?>
            </ul>
        </div>
    </div>
</div>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;">
    <?php

    // *** Default settings ***
    $end_text = '';
    $show_table = false;
    $table_header_text = __('Picture settings');

    // *** Show picture settings ***
    if (isset($menu_admin) and $menu_admin == 'picture_settings') {
        $end_text = '- ' . __('To show pictures, also check the user-group settings: ');
        $end_text .= ' <a href="index.php?page=groups">' . __('User groups') . '</a>';
        $show_table = true;
    }

    // *** Create picture thumbnails ***
    if (isset($menu_admin) and $menu_admin == 'picture_thumbnails') {
        $end_text = __('- Creating thumbnails<br>
- ATTENTION: it may be necessary to (temporarily) change access to the folder with the pictures (rwxrwxrwx)<br>
- Sometimes the php.ini has to be changed slightly, remove the ; before the line with:');
        $end_text .= ' <i>extension=php.gd2.dll</i>';
        $show_table = true;
        $table_header_text = __('Create thumbnails');
    }

    // *** Show picture thumbnails ***
    if (isset($menu_admin) and $menu_admin == 'picture_show') {
        $show_table = true;
        $table_header_text = __('Show thumbnails');
    }

    // *** Selection table ***
    if ($show_table) {
    ?>
        <table class="humo" style="margin-left:0px;" border="1">
            <tr class="table_header">
                <th colspan="2">
                    <?= $table_header_text; ?>
                </th>
            </tr>

            <tr>
                <td class="line_item"><?= __('Choose family tree'); ?></td>
                <td>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="page" value="thumbs">
                        <input type="hidden" name="menu_admin" value="<?= $menu_admin; ?>">
                        <select size="1" name="tree_id" onChange="this.form.submit();">
                            <?php
                            $tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
                            $tree_result = $dbh->query($tree_sql);
                            while ($treeDb = $tree_result->fetch(PDO::FETCH_OBJ)) {
                                $treetext = show_tree_text($treeDb->tree_id, $selected_language);
                                $selected = '';
                                if (isset($tree_id) and ($treeDb->tree_id == $tree_id)) {
                                    $selected = ' selected';
                                    $db_functions->set_tree_id($tree_id);
                                }
                                echo '<option value="' . $treeDb->tree_id . '"' . $selected . '>' . @$treetext['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </form>
                </td>
            </tr>

            <?php
            // *** Set path to pictures ***
            if (isset($tree_id)) {
                // *** Save new/ changed picture path ***
                if (isset($_POST['change_tree_data'])) {
                    $tree_pict_path = $_POST['tree_pict_path'];
                    if (substr($_POST['tree_pict_path'], 0, 1) == '|') {
                        if (isset($_POST['default_path']) and $_POST['default_path'] == 'no') $tree_pict_path = substr($tree_pict_path, 1);
                    } else {
                        if (isset($_POST['default_path']) and $_POST['default_path'] == 'yes') $tree_pict_path = '|' . $tree_pict_path;
                    }
                    $sql = "UPDATE humo_trees SET tree_pict_path='" . safe_text_db($tree_pict_path) . "' WHERE tree_id=" . safe_text_db($tree_id);
                    $result = $dbh->query($sql);
                }

                $data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=" . $tree_id);
                $data2Db = $data2sql->fetch(PDO::FETCH_OBJ);

                echo '<tr><td class="line_item">';
                echo __('Path to the pictures');
                echo '</td><td>';
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

                echo '<form method="POST" action="index.php">';
                echo '<input type="hidden" name="page" value="thumbs">';
                echo '<input type="hidden" name="menu_admin" value="' . $menu_admin . '">';
                echo '<input type="hidden" name="tree_id" value="' . $tree_id . '">';

                echo '<input type="radio" value="yes" name="default_path" ' . $checked1 . '> ' . __('Use default picture path:') . ' <b>media/</b><br>';
                echo '<input type="radio" value="no" name="default_path" ' . $checked2 . '> ';

                echo '<input type="text" name="tree_pict_path" value="' . $tree_pict_path . '" size="40" placeholder="../pictures/">';
                echo ' <input type="Submit" name="change_tree_data" value="' . __('Change') . '"><br>';
                printf(__('Example of picture path:<br>
www.myhomepage.nl/humo-gen/ => folder for %s files.<br>
www.myhomepage.nl/pictures/ => folder for pictures.<br>
Use a relative path, exactly as shown here: <b>../pictures/</b>'), 'HuMo-genealogy');
                echo '</form>';
                echo '</td></tr>';

                // *** Show subdirectories ***
                function get_media_files($first, $prefx, $path)
                {
                    $ignore = array('cms', 'slideshow', 'thumbs', '.', '..');
                    $dh = opendir($prefx . $path);
                    while (false !== ($filename = readdir($dh))) {
                        if (!in_array($filename, $ignore)) {
                            if (is_dir($prefx . $path . $filename)) {
                                if ($first == false) {
                                    echo ' ' . __('Subdirectories:');
                                    $first = true;
                                }
                                echo '<br>' . $path . $filename . '/';
                                get_media_files($first, $prefx, $path . $filename . '/');
                            }
                        }
                    }
                    closedir($dh);
                }

                // *** Status of picture path ***
                echo '<tr><td class="line_item">';
                echo __('Status of picture path');
                echo '</td><td>';
                $tree_pict_path = $data2Db->tree_pict_path;
                if (substr($tree_pict_path, 0, 1) == '|') $tree_pict_path = 'media/';
                //if ($data2Db->tree_pict_path!='' AND file_exists($prefx.$data2Db->tree_pict_path))
                if ($tree_pict_path != '' and file_exists($prefx . $tree_pict_path)) {
                    echo __('Picture path exists.');

                    // *** Show subdirectories ***
                    $first = false;
                    get_media_files($first, $prefx, $tree_pict_path);
                } else {
                    echo '<span class="line_nok"><b>' . __('Picture path doesn\'t exist!') . '</b></span>';
                }
                echo '</td></tr>';

                // *** Create thumbnails ***
                if (isset($menu_admin) and $menu_admin == 'picture_thumbnails') {
                    // *** Thumb height ***
                    $thumb_height = 120; // *** Standard thumb height ***
                    // *** Feb. 2023: no user changable picture size ***
                    //if (isset($_POST['pict_height']) AND is_numeric($_POST['pict_height'])){ $thumb_height=$_POST['pict_height']; }
                    echo '<tr><td class="line_item">';
                    echo __('Create thumbnails');
                    echo '</td><td>';
                    echo '<form method="POST" action="index.php">';
                    echo '<input type="hidden" name="page" value="thumbs">';
                    echo '<input type="hidden" name="menu_admin" value="picture_thumbnails">';
                    echo '<input type="hidden" name="tree_id" value="' . $tree_id . '">';
                    // *** Feb. 2023: no user changable picture size ***
                    //echo __('Thumbnail height: ').' <input type="text" name="pict_height" value="'.$thumb_height.'" size="4"> pixels';
                    echo ' <input type="Submit" name="thumbnail" value="' . __('Create thumbnails') . '">';
                    echo '</form>';
                    echo '</td></tr>';
                }

                // *** Show thumbnails ***
                if (isset($menu_admin) and $menu_admin == 'picture_show') {
            ?>
                    <tr>
                        <td class="line_item">
                            <?= __('Show thumbnails'); ?>
                        </td>
                        <td>
                            <form method="POST" action="index.php">
                                <input type="hidden" name="page" value="thumbs">
                                <input type="hidden" name="menu_admin" value="picture_show">
                                <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                                <input type="Submit" name="change_filename" value="<?= __('Show thumbnails'); ?>">
                                <?= ' ' . __('You can change filenames here.'); ?>
                            </form>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </table><br>
    <?php

        echo $end_text . '<br>';
    }


    // *** Picture categories ***
    if (isset($menu_admin) and $menu_admin == 'picture_categories') {
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
        if (!$temp->rowCount()) {
            // no category database table exists - so create it
            // It has 4 columns:
            //     1. id
            //     2. name of category prefix- 2 letters and underscore chosen by admin (ws_   bp_)
            //     3. language for name of category
            //     4. name of category

            $albumtbl = "CREATE TABLE humo_photocat (
            photocat_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            photocat_order MEDIUMINT(6),
            photocat_prefix VARCHAR(30) CHARACTER SET utf8,
            photocat_language VARCHAR(10) CHARACTER SET utf8,
            photocat_name VARCHAR(50) CHARACTER SET utf8
        ) DEFAULT CHARSET=utf8";
            $dbh->query($albumtbl);
            // Enter the default category with default name that can be changed by admin afterwards
            $dbh->query("INSERT INTO humo_photocat (photocat_prefix,photocat_order,photocat_language,photocat_name) VALUES ('none','1','default','" . safe_text_db(__('Photos')) . "')");
        }

        //echo '<h1 align=center>'.__('Photo album categories').'</h1>';

        $language_tree = $selected_language; // Default language
        if (isset($_GET['language_tree'])) {
            $language_tree = $_GET['language_tree'];
        }
        if (isset($_POST['language_tree'])) {
            $language_tree = $_POST['language_tree'];
        }

        if (isset($_GET['cat_drop2']) and $_GET['cat_drop2'] == 1 and !isset($_POST['save_cat'])) {
            // delete category and make sure that the order sequence is restored
            $dbh->query("UPDATE humo_photocat SET photocat_order = (photocat_order-1) WHERE photocat_order > '" . safe_text_db($_GET['cat_order']) . "'");
            $dbh->query("DELETE FROM humo_photocat WHERE photocat_prefix = '" . safe_text_db($_GET['cat_prefix']) . "'");
        }
        if (isset($_GET['cat_up']) and !isset($_POST['save_cat'])) {
            // move category up
            $dbh->query("UPDATE humo_photocat SET photocat_order = 'temp' WHERE photocat_order ='" . safe_text_db($_GET['cat_up']) . "'");  // set present one to temp
            $dbh->query("UPDATE humo_photocat SET photocat_order = '" . $_GET['cat_up'] . "' WHERE photocat_order ='" . (safe_text_db($_GET['cat_up']) - 1) . "'");  // move the one above down
            $dbh->query("UPDATE humo_photocat SET photocat_order = '" . (safe_text_db($_GET['cat_up']) - 1) . "' WHERE photocat_order = 'temp'");  // move this one up
        }
        if (isset($_GET['cat_down']) and !isset($_POST['save_cat'])) {
            // move category down
            $dbh->query("UPDATE humo_photocat SET photocat_order = 'temp' WHERE photocat_order ='" . safe_text_db($_GET['cat_down']) . "'");  // set present one to temp
            $dbh->query("UPDATE humo_photocat SET photocat_order = '" . safe_text_db($_GET['cat_down']) . "' WHERE photocat_order ='" . (safe_text_db($_GET['cat_down']) + 1) . "'");  // move the one under it up
            $dbh->query("UPDATE humo_photocat SET photocat_order = '" . (safe_text_db($_GET['cat_down']) + 1) . "' WHERE photocat_order = 'temp'");  // move this one down
        }

        if (isset($_POST['save_cat'])) {  // the user decided to add a new category and/or save changes to names
            // save names of existing categories in case some were altered. There is at least always one name (for default category)

            //$qry = "SELECT * FROM humo_photocat GROUP BY photocat_prefix";
            // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
            $qry = "SELECT photocat_prefix, photocat_order FROM humo_photocat GROUP BY photocat_prefix, photocat_order";
            $result = $dbh->query($qry);

            while ($resultDb = $result->fetch(PDO::FETCH_OBJ)) {
                if (isset($_POST[$resultDb->photocat_prefix])) {
                    if ($language_tree != "default") {
                        // only update names for the chosen language
                        $check_lang = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix = '" . $resultDb->photocat_prefix . "' AND photocat_language='" . safe_text_db($language_tree) . "'");
                        if ($check_lang->rowCount() != 0) { // this language already has a name for this category - update it
                            $dbh->query("UPDATE humo_photocat SET photocat_name = '" . safe_text_db($_POST[$resultDb->photocat_prefix]) . "'
                            WHERE photocat_prefix = '" . $resultDb->photocat_prefix . "' AND photocat_language='" . safe_text_db($language_tree) . "'");
                        } else {  // this language doesn't yet have a name for this category - create it
                            $dbh->query("INSERT INTO humo_photocat (photocat_prefix, photocat_order, photocat_language, photocat_name) VALUES ('" . $resultDb->photocat_prefix . "', '" . $resultDb->photocat_order . "', '" . $language_tree . "', '" . safe_text_db($_POST[$resultDb->photocat_prefix]) . "')");
                        }
                    } else {  // update entered names for all languages 
                        $check_default = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix = '" . $resultDb->photocat_prefix . "' AND photocat_language='default'");
                        if ($check_default->rowCount() != 0) {    // there is a default name for this language - update it
                            $dbh->query("UPDATE humo_photocat SET photocat_name = '" . safe_text_db($_POST[$resultDb->photocat_prefix]) . "'
                            WHERE photocat_prefix='" . $resultDb->photocat_prefix . "' AND photocat_language='default'");
                        } else {  // no default name yet for this category - create it
                            $dbh->query("INSERT INTO humo_photocat (photocat_prefix, photocat_order, photocat_language, photocat_name) VALUES ('" . $resultDb->photocat_prefix . "', '" . $resultDb->photocat_order . "', 'default', '" . safe_text_db($_POST[$resultDb->photocat_prefix]) . "')");
                        }
                    }
                }
            }

            // save new category
            if (isset($_POST['new_cat_prefix']) and isset($_POST['new_cat_name'])) {
                if ($_POST['new_cat_prefix'] != "") {
                    $new_cat_prefix = $_POST['new_cat_prefix'];
                    $new_cat_name = $_POST['new_cat_name'];
                    $warning_prefix = "";
                    $warning_invalid_prefix = "";
                    if (preg_match('/^[a-z][a-z]_$/', $_POST['new_cat_prefix']) !== 1) {
                        $warning_invalid_prefix = __('Prefix has to be 2 letters and _');
                        $warning_prefix = $_POST['new_cat_prefix'];
                    } else {
                        $warning_exist_prefix = "";
                        $check_exist = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='" . safe_text_db($new_cat_prefix) . "'");
                        if ($check_exist->rowCount() == 0) {
                            if ($_POST['new_cat_name'] == "") {
                                $warning_noname = __('When creating a category you have to give it a name');
                                $warning_prefix = $_POST['new_cat_prefix'];
                            } else {
                                $highest_order = $dbh->query("SELECT MAX(photocat_order) AS maxorder FROM humo_photocat");
                                $orderDb = $highest_order->fetch(PDO::FETCH_ASSOC);
                                $order = $orderDb['maxorder'];
                                $order++;
                                $qry = "INSERT INTO humo_photocat (photocat_prefix,photocat_order,photocat_language,photocat_name) VALUES ('" . safe_text_db($new_cat_prefix) . "', '" . safe_text_db($order) . "', '" . safe_text_db($language_tree) . "', '" . safe_text_db($new_cat_name) . "')";
                                $dbh->query($qry);
                            }
                        } else {   // this category prefix already exists!
                            $warning_exist_prefix = __('A category with this prefix already exists!');
                            $warning_prefix = $_POST['new_cat_prefix'];
                        }
                    }
                }
            }
        }

        categories();
    }

    // *** Change filename ***
    if (isset($_POST['filename'])) {
        $picture_path_old = $_POST['picture_path'];
        $picture_path_new = $_POST['picture_path'];
        // *** If filename has a category AND a sub category directory exists, use it ***
        if (substr($_POST['filename'], 0, 2) != substr($_POST['filename_old'], 0, 2) and ($_POST['filename'][2] == '_' or $_POST['filename_old'][2] == '_')) { // we only have to do this if something changed in a prefix
            if ($_POST['filename'][2] == '_') {
                if (preg_match('!.+/[a-z][a-z]/$!', $picture_path_new) == 1) {   // original path had subfolder
                    if (is_dir(substr($picture_path_new, 0, -3) . substr($_POST['filename'], 0, 2))) {   // subtract subfolder and add new subfolder
                        $picture_path_new = substr($picture_path_new, 0, -3) . substr($_POST['filename'], 0, 2) . "/"; // move from subfolder to other subfolder
                    } else {
                        $picture_path_new = substr($picture_path_new, 0, -3); // move file with prefix that has no folder to main folder
                    }
                } elseif (is_dir($_POST['picture_path'] . substr($_POST['filename'], 0, 2))) {
                    $picture_path_new .= substr($_POST['filename'], 0, 2) . '/';   // move from main folder to subfolder
                }
            } elseif (preg_match('!.+/[a-z][a-z]/$!', $picture_path_new) == 1) {    // regular file, just check if original path had subfolder
                $picture_path_new = substr($picture_path_new, 0, -3);  // move from subfolder to main folder
            }
        }

        if (file_exists($picture_path_old . $_POST['filename_old'])) {
            rename($picture_path_old . $_POST['filename_old'], $picture_path_new . $_POST['filename']);
            echo '<b>' . __('Changed filename:') . '</b> ' . $picture_path_old . $_POST['filename_old'] . ' <b>' . __('into filename:') . '</b> ' . $picture_path_new . $_POST['filename'] . '<br>';
        }

        if (file_exists($picture_path_old . 'thumb_' . $_POST['filename_old'])) {
            rename($picture_path_old . 'thumb_' . $_POST['filename_old'], $picture_path_new . 'thumb_' . $_POST['filename']);
            echo '<b>' . __('Changed filename:') . ' </b>' . $picture_path_old . 'thumb_' . $_POST['filename_old'] . ' <b>' . __('into filename:') . '</b> ' . $picture_path_new . 'thumb_' . $_POST['filename'] . '<br>';
        }

        $sql = "UPDATE humo_events SET
            event_event='" . safe_text_db($_POST['filename']) . "' WHERE event_event='" . safe_text_db($_POST['filename_old']) . "'";
        $result = $dbh->query($sql);
    }


    // *** Create thumbnails ***
    $counter = 0;
    if (isset($_POST["thumbnail"]) or isset($_POST['change_filename'])) {
        $pict_path = $data2Db->tree_pict_path;
        if (substr($pict_path, 0, 1) == '|') $pict_path = 'media/';

        @set_time_limit(3000);

        //$selected_picture_folder=$prefx.$pict_path;
        $array_picture_folder[] = $prefx . $pict_path;

        // *** Extra safety check if folder exists ***
        //if (file_exists($selected_picture_folder)){
        if (file_exists($array_picture_folder[0])) {
            // *** Get all subdirectories ***
            function get_dirs($prefx, $path)
            {
                global $array_picture_folder;
                $ignore = array('cms', 'slideshow', 'thumbs', '.', '..');
                $dh = opendir($prefx . $path);
                while (false !== ($filename = readdir($dh))) {
                    if (!in_array($filename, $ignore)) {
                        if (is_dir($prefx . $path . $filename)) {
                            $array_picture_folder[] = $prefx . $path . $filename . '/';
                            get_dirs($prefx, $path . $filename . '/');
                        }
                    }
                }
                closedir($dh);
            }

            get_dirs($prefx, $pict_path);

            foreach ($array_picture_folder as $selected_picture_folder) {
                echo '<br style="clear: both">';
                echo '<h3>' . $selected_picture_folder . '</h3>';

                $dh = opendir($selected_picture_folder);
                $gd = gd_info(); // certain versions of GD don't handle gifs
                while (false !== ($filename = readdir($dh))) {
                    $imgtype = strtolower(substr($filename, -3));
                    if (
                        $imgtype == "jpg"
                        or $imgtype == "png"
                        or ($imgtype == "gif" and $gd["GIF Read Support"] == TRUE and $gd["GIF Create Support"] == TRUE)
                    ) {
                        //$pict_path_original=$prefx.$pict_path."/".$filename;    //ORIGINEEL
                        //$pict_path_thumb=$prefx.$pict_path."/thumb_".$filename; //THUMB

                        $pict_path_original = $selected_picture_folder . $filename;        //ORIGINEEL
                        $pict_path_thumb = $selected_picture_folder . 'thumb_' . $filename; //THUMB

                        // test: maybe create only new thumbnails.
                        //if (substr($filename, 0, 5) != 'thumb'){ 
                        //	if (file_exists($pict_path_thumb)) echo 'EXISTS<br>'; else echo 'NEW '.$pict_path_thumb.'<br>';
                        //}

                        //*** Create a thumbnail ***
                        if (substr($filename, 0, 5) != 'thumb' and !isset($_POST['change_filename'])) {
                            // *** Get size of original picture ***
                            list($width, $height) = getimagesize($pict_path_original);

                            // *** Calculate format ***
                            $factor = $height / $thumb_height;
                            $newheight = round($thumb_height);
                            $newwidth = round($width / $factor);

                            // *** Picture folder must be writable!!!
                            // Sometimes it's necessary to remove ; in php.ini before this line:
                            // extension=php.gd2.dll

                            // $thumb = imagecreate($newwidth, $newheight);
                            $thumb = imagecreatetruecolor($newwidth, $newheight);
                            if ($imgtype == "jpg") {
                                $source = imagecreatefromjpeg($pict_path_original);
                            } elseif ($imgtype == "png") {
                                $source = imagecreatefrompng($pict_path_original);
                            } else {
                                $source = imagecreatefromgif($pict_path_original);
                            }

                            // *** Resize ***
                            imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                            if ($imgtype == "jpg") {
                                @imagejpeg($thumb, $pict_path_thumb);
                            } elseif ($imgtype == "png") {
                                @imagepng($thumb, $pict_path_thumb);
                            } else {
                                @imagegif($thumb, $pict_path_thumb);
                            }
                        }

                        // *** Show thumbnails ***
                        if (substr($filename, 0, 5) != 'thumb') {
                            echo '<div class="photobook">';
                            echo '<img src="' . $pict_path_thumb . '" title="' . $pict_path_thumb . '">';

                            // *** Show name of connected persons ***
                            include_once('../include/person_cls.php');
                            $picture_text = '';
                            $sql = "SELECT * FROM humo_events WHERE event_tree_id='" . safe_text_db($tree_id) . "'
                                AND event_connect_kind='person' AND event_kind='picture'
                                AND LOWER(event_event)='" . safe_text_db(strtolower($filename)) . "'";
                            $afbqry = $dbh->query($sql);
                            $picture_privacy = false;
                            while ($afbDb = $afbqry->fetch(PDO::FETCH_OBJ)) {
                                $person_cls = new person_cls;
                                $personDb = $db_functions->get_person($afbDb->event_connect_id);
                                $name = $person_cls->person_name($personDb);

                                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                                $uri_path = '../'; // *** Needed if url_rewrite is enabled ***
                                $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                                //$picture_text.='<br><a href="'.CMS_ROOTPATH.$url.'">'.$name["standard_name"].'</a><br>';
                                $picture_text .= '<br><a href="' . $url . '">' . $name["standard_name"] . '</a><br>';
                            }
                            echo $picture_text;

                            if (isset($_POST['change_filename'])) {
                                echo '<form method="POST" action="index.php">';
                                echo '<input type="hidden" name="page" value="thumbs">';
                                echo '<input type="hidden" name="menu_admin" value="picture_show">';
                                echo '<input type="hidden" name="tree_id" value="' . $tree_id . '">';
                                echo '<input type="hidden" name="picture_path" value="' . $selected_picture_folder . '">';
                                echo '<input type="hidden" name="filename_old" value="' . $filename . '">';
                                echo '<input type="text" name="filename" value="' . $filename . '" size="20">';
                                echo '<input type="Submit" name="change_filename" value="' . __('Change filename') . '">';
                                echo '</form>';
                            } else {
                                echo '<div class="photobooktext">' . $filename . '</div>';
                            }
                            echo '</div>';
                        }
                    }
                }
                closedir($dh);
            }
        } else {
            // *** Normally this is not used ***
            echo '<b>' . __('This folder does not exists!') . '</b>';
        }
    }
    ?>
</div>
<?php

function categories()
{
    global $language, $language_tree, $selected_language, $dbh, $warning_exist_prefix, $warning_prefix, $warning_invalid_prefix, $warning_noname;
    global $page, $language_file, $data2Db, $phpself, $joomlastring;
?>
    <form method="post" action="<?= $phpself; ?>" style="display : inline;">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="hidden" name="menu_admin" value="picture_categorie">
        <input type="hidden" name="language_tree" value="<?= $language_tree; ?>">
        <table class="humo" cellspacing="0" style="margin-left:0px; text-align:center; width:80%">
            <tr class="table_header">
                <th colspan="5"><?= __('Create categories for your photo albums'); ?></th>
            </tr>

            <?php
            echo '<tr><td style="text-align:left" colspan="5">';
            echo '<ul><li>' . __('Here you can create categories for all your photo albums.</li><li><b>A category will not be displayed in the photobook menu unless there is at least one picture for it.</b></li><li>Click "Default" to create one default name in all languages. Choose a language from the list to set a specific name for that language.<br><b>TIP:</b> First set an English name as default for all languages, then create specific names for those languages that you know. That way no tabs will display without a name in any language. In any case, setting a default name will not overwrite names for specific languages that you have already set.</li><li>The category prefix has to be made up of two letters and an underscore (like: <b>sp_</b> or <b>ws_</b>).</li><li>Pictures that you want to appear in a specific category have to be named with that prefix like: <b>sp_</b>John Smith.jpg</li><li>Pictures that you want to be displayed in the default photo category don\'t need a prefix.');
            echo '<li>' . __('A (sub)directory could also be a category. Example: category prefix = ab_, the directory name = ab.') . '</li>';
            echo '</li></ul></td></tr>';

            echo '<tr><td style="border-bottom:0px;width:5%"></td><td style="border-bottom:0px;width:5%"></td><td style="border-bottom:0px;width:5%"></td><td style="font-size:120%;border-bottom:0px;width:25%" white-space:nowrap;"><b>' . __('Category prefix') . '</b></td><td style="font-size:120%;border-bottom:0px;width:60%"><b>' . __('Category name') . '</b><br></td></tr>';

            $add = "";
            if (isset($_POST['add_new_cat'])) {
                $add = "&amp;add_new_cat=1";
            }
            echo '<tr><td style="border-top:0px"><td style="border-top:0px"></td><td style="border-top:0px"></td><td style="border-top:0px"></td><td style="border-top:0px;text-align:center">' . __('Language') . ':&nbsp&nbsp;';

            // *** Language choice ***
            $language_tree2 = $language_tree;
            if ($language_tree == 'default') $language_tree2 = $selected_language;
            echo '&nbsp;&nbsp;<div class="ltrsddm" style="display : inline;">';
            echo '<a href="index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories&amp;language_tree=' . $language_tree2 . '"';
            echo '<a href="index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories&amp;language_tree=' . $language_tree2 . '"';
            include(CMS_ROOTPATH . 'languages/' . $language_tree2 . '/language_data.php');
            echo ' onmouseover="mopen(event,\'adminx\',\'?\',\'?\')"';
            $select_top = '';
            echo ' onmouseout="mclosetime()"' . $select_top . '>' . '<img src="' . CMS_ROOTPATH . 'languages/' . $language_tree2 . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none; height:14px"> ' . $language["name"] . ' <img src="' . CMS_ROOTPATH . 'images/button3.png" height="13" style="border:none;" alt="pull_down"></a>';

            ?>
            <div id="adminx" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()" style="width:250px;">
                <ul class="humo_menu_item2">
                    <?php
                    for ($i = 0; $i < count($language_file); $i++) {
                        // *** Get language name ***
                        if ($language_file[$i] != $language_tree2) {
                            include(CMS_ROOTPATH . 'languages/' . $language_file[$i] . '/language_data.php');
                            echo '<li style="float:left; width:124px;">';
                            echo '<a href="index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories&amp;language_tree=' . $language_file[$i] . $add . '">';
                            echo '<img src="' . CMS_ROOTPATH . 'languages/' . $language_file[$i] . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none;"> ';
                            echo $language["name"];
                            echo '</a>';
                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
            <?php
            echo '</div>';
            echo '&nbsp;&nbsp;' . __('or') . '&nbsp;&nbsp;';
            echo '<a href="index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories&amp;language_tree=default' . $add . '">' . __('Default') . '</a> ';
            echo '</td></tr>';

            //$qry = "SELECT * FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order";
            // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
            $qry = "SELECT photocat_prefix, photocat_order FROM humo_photocat GROUP BY photocat_prefix, photocat_order ORDER BY photocat_order";
            $cat_result = $dbh->query($qry);
            $number = 1;  // number on list

            while ($catDb = $cat_result->fetch(PDO::FETCH_OBJ)) {
                $name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='" . $catDb->photocat_prefix . "' AND photocat_language = '" . safe_text_db($language_tree) . "'");
                if ($name->rowCount()) {  // there is a name for this language
                    $nameDb = $name->fetch(PDO::FETCH_OBJ);
                    $catname = $nameDb->photocat_name;
                } else {  // maybe a default is set
                    $name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='" . $catDb->photocat_prefix . "' AND photocat_language = 'default'");
                    if ($name->rowCount()) {  // there is a default name for this category
                        $nameDb = $name->fetch(PDO::FETCH_OBJ);
                        $catname = $nameDb->photocat_name;
                    } else {  // no name at all
                        $catname = "";
                    }
                }

                echo '<tr><td>' . $number++ . '.</td>';
                // arrows
                $order_sequence = $dbh->query("SELECT MAX(photocat_order) AS maxorder, MIN(photocat_order) AS minorder FROM humo_photocat");
                $orderDb = $order_sequence->fetch(PDO::FETCH_ASSOC);
                $maxorder = $orderDb['maxorder'];
                $minorder = $orderDb['minorder'];
                if ($catDb->photocat_order == $minorder) {
                    echo '<td style="text-align:right;padding-right:4px">';
                } elseif ($catDb->photocat_order == $maxorder) {
                    echo '<td style="text-align:left;padding-left:4px">';
                } else {
                    echo '<td>';
                }
                if ($catDb->photocat_order != $minorder) {
                    echo '<a href="index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_up=' . $catDb->photocat_order . '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_up.gif"></a>&nbsp;&nbsp;';
                }
                if ($catDb->photocat_order != $maxorder) {
                    echo '<a href="index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_down=' . $catDb->photocat_order . '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_down.gif"></a>';
                }
                echo '</td><td>';
                if ($catDb->photocat_prefix != 'none') {
                    echo '<a href="index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories&amp;cat_order=' . $catDb->photocat_order . '&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_drop=1"><img src="' . CMS_ROOTPATH_ADMIN . 'images/button_drop.png"></a>';
                }
                $prefname = $catDb->photocat_prefix;
                if ($catDb->photocat_prefix == 'none') $prefname = __('default - without prefix');  // display default in the display language, so it is clear to everyone
                echo '</td><td style="white-space:nowrap;">' . $prefname . '</td><td><input type="text" name="' . $catDb->photocat_prefix . '" value="' . $catname . '" size="30"></td></tr>';
            }

            $content = "";
            if (isset($warning_prefix)) {
                $content = $warning_prefix;
            }
            echo '<tr><td>' . $number . '.</td><td></td><td></td><td style="white-space:nowrap;">' . '<input type="text" name="new_cat_prefix" value="' . $content . '" size="6">';
            if (isset($warning_invalid_prefix)) echo '<br><span style="color:red">' . $warning_invalid_prefix . '</span>';
            if (isset($warning_exist_prefix)) echo '<br><span style="color:red">' . $warning_exist_prefix . '</span>';
            echo '</td><td><input type="text" name="new_cat_name" value="" size="30">';
            if (isset($warning_noname)) echo '<br><span style="color:red">' . $warning_noname . '</span>';
            echo '</td></tr>';

            if (isset($_GET['cat_drop']) and $_GET['cat_drop'] == 1) {
                echo '<tr><td colspan=5 style="color:red;font-weight:bold;font-size:120%">' . __('Do you really want to delete category:') . '&nbsp;' . $_GET['cat_prefix'] . '&nbsp;?';
                echo '&nbsp;&nbsp;&nbsp;<input type="button" style="color:red;font-weight:bold" onclick="location.href=\'index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories&amp;cat_order=' . $_GET['cat_order'] . '&amp;cat_prefix=' . $_GET['cat_prefix'] . '&amp;cat_drop2=1\';" value="' . __('Yes') . '">';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" style="color:green;font-weight:bold" onclick="location.href=\'index.php?' . $joomlastring . 'page=thumbs&amp;menu_admin=picture_categories\';" value="' . __('No') . '">';
                echo '</td></tr>';
            }
            ?>
        </table>
        <br>
        <div style="margin-left:auto; margin-right:auto; text-align:center;"><input type="Submit" name="save_cat" value="<?= __('Save changes'); ?>"></div>
    </form>
<?php
}
