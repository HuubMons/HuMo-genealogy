<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

include_once(__DIR__ . "/../include/select_tree.php");

$prefx = '../'; // to get out of the admin map

$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=" . $tree_id);
$data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
?>

<ul class="nav nav-tabs mt-1">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($thumbs['menu_tab'] == 'picture_settings') echo 'active'; ?>" href="index.php?page=<?= $page; ?>"><?= __('Picture settings'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($thumbs['menu_tab'] == 'picture_thumbnails') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_tab=picture_thumbnails"><?= __('Create thumbnails'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($thumbs['menu_tab'] == 'picture_show') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_tab=picture_show"><?= __('Show thumbnails'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($thumbs['menu_tab'] == 'picture_categories') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_tab=picture_categories"><?= __('Photo album categories'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;">
    <?php if ($thumbs['menu_tab'] == 'picture_settings' || $thumbs['menu_tab'] == 'picture_thumbnails' || $thumbs['menu_tab'] == 'picture_show') { ?>
        <div class="p-3 m-2 genealogy_search">

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="tree" class="col-form-label"><?= __('Choose family tree'); ?></label>
                </div>

                <div class="col-md-7">
                    <?= select_tree($dbh, $page, $tree_id, $thumbs['menu_tab']); ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="picture_path" class="col-form-label"><?= __('Path to the pictures'); ?></label>
                </div>

                <!-- Set path to pictures -->
                <div class="col-md-8">
                    <form method="POST" action="index.php">
                        <input type="hidden" name="page" value="thumbs">
                        <input type="hidden" name="menu_tab" value="<?= $thumbs['menu_tab']; ?>">
                        <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">

                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="yes" name="default_path" id="default_path" <?= $thumbs['default_path'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="default_path">
                                <?= __('Use default picture path:'); ?> <b>media/</b>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="no" name="default_path" id="default_path" <?= !$thumbs['default_path'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="default_path">
                                <input type="text" name="tree_pict_path" value="<?= $thumbs['own_pict_path']; ?>" size="40" placeholder="../pictures/" class="form-control form-control-sm">
                            </label>
                        </div>

                        <?php printf(__('Example of picture path:<br>
www.myhomepage.nl/humo-gen/ => folder for %s files.<br>
www.myhomepage.nl/pictures/ => folder for pictures.<br>
Use a relative path, exactly as shown here: <b>../pictures/</b>'), 'HuMo-genealogy'); ?><br><br>

                        <input type="submit" name="change_tree_data" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"><br>
                    </form>
                </div>
            </div>

            <?php
            // *** Show subdirectories ***
            function get_media_files($first, $prefx, $path)
            {
                $ignore = array('cms', 'slideshow', 'thumbs', '.', '..');
                $dh = opendir($prefx . $path);
                while (false !== ($filename = readdir($dh))) {
                    if (!in_array($filename, $ignore) && is_dir($prefx . $path . $filename)) {
                        if ($first == false) {
                            echo ' ' . __('Subdirectories:');
                            $first = true;
                        }
                        echo '<br>' . $path . $filename . '/';
                        get_media_files($first, $prefx, $path . $filename . '/');
                    }
                }
                closedir($dh);
            }
            ?>

            <div class="row mb-2">
                <div class="col-md-4"><?= __('Status of picture path'); ?></div>

                <div class="col-md-7">
                    <?php if ($thumbs['tree_pict_path'] != '' && file_exists($prefx . $thumbs['tree_pict_path'])) { ?>
                        <span class="bg-success-subtle"><?= __('Picture path exists.'); ?></span>

                    <?php
                        // *** Show subdirectories ***
                        $first = false;
                        get_media_files($first, $prefx, $thumbs['tree_pict_path']);
                    } else {
                        echo '<span class="bg-warning-subtle"><b>' . __('Picture path doesn\'t exist!') . '</b></span>';
                    }
                    ?>
                </div>
            </div>

            <!-- Create thumbnails -->
            <?php
            if ($thumbs['menu_tab'] == 'picture_thumbnails') {
                $thumb_height = 120; // *** Standard thumb height ***
            ?>
                <div class="row mb-2">
                    <div class="col-md-4"><?= __('Create thumbnails'); ?></div>

                    <div class="col-md-7">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="page" value="thumbs">
                            <input type="hidden" name="menu_tab" value="picture_thumbnails">
                            <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                            <input type="submit" name="thumbnail" value="<?= __('Create thumbnails'); ?>" class="btn btn-sm btn-success">
                        </form>
                    </div>
                </div>
            <?php } ?>

            <!-- Show thumbnails -->
            <?php if ($thumbs['menu_tab'] == 'picture_show') { ?>
                <div class="row mb-2">
                    <div class="col-md-4"><?= __('Show thumbnails'); ?></div>

                    <div class="col-md-7">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="page" value="thumbs">
                            <input type="hidden" name="menu_tab" value="picture_show">
                            <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                            <input type="submit" name="change_filename" value="<?= __('Show thumbnails'); ?>" class="btn btn-sm btn-success">
                            <?= ' ' . __('You can change filenames here.'); ?>
                        </form>
                    </div>
                </div>
            <?php } ?>

        </div>

        <?php if ($thumbs['menu_tab'] == 'picture_settings') { ?>
            - <?= __('To show pictures, also check the user-group settings: '); ?>
            <a href="index.php?page=groups"><?= __('User groups'); ?></a>
        <?php
        }

        // *** Create picture thumbnails ***
        if ($thumbs['menu_tab'] == 'picture_thumbnails') {
        ?>
            <?= __('- Creating thumbnails<br>
- ATTENTION: it may be necessary to (temporarily) change access to the folder with the pictures (rwxrwxrwx)<br>
- Sometimes the php.ini has to be changed slightly, remove the ; before the line with:'); ?>
            <i>extension=php.gd2.dll</i>
        <?php }
    }


    // *** Picture categories ***
    if ($thumbs['menu_tab'] == 'picture_categories') {
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

        if (isset($_GET['cat_drop2']) && $_GET['cat_drop2'] == 1 && !isset($_POST['save_cat'])) {
            // delete category and make sure that the order sequence is restored
            $dbh->query("UPDATE humo_photocat SET photocat_order = (photocat_order-1) WHERE photocat_order > '" . safe_text_db($_GET['cat_order']) . "'");
            $dbh->query("DELETE FROM humo_photocat WHERE photocat_prefix = '" . safe_text_db($_GET['cat_prefix']) . "'");
        }
        if (isset($_GET['cat_up']) && !isset($_POST['save_cat'])) {
            // move category up
            $dbh->query("UPDATE humo_photocat SET photocat_order = '999' WHERE photocat_order ='" . safe_text_db($_GET['cat_up']) . "'");  // set present one to temp
            $dbh->query("UPDATE humo_photocat SET photocat_order = '" . $_GET['cat_up'] . "' WHERE photocat_order ='" . (safe_text_db($_GET['cat_up']) - 1) . "'");  // move the one above down
            $dbh->query("UPDATE humo_photocat SET photocat_order = '" . (safe_text_db($_GET['cat_up']) - 1) . "' WHERE photocat_order = '999'");  // move this one up
        }
        if (isset($_GET['cat_down']) && !isset($_POST['save_cat'])) {
            // move category down
            $dbh->query("UPDATE humo_photocat SET photocat_order = '999' WHERE photocat_order ='" . safe_text_db($_GET['cat_down']) . "'");  // set present one to temp
            $dbh->query("UPDATE humo_photocat SET photocat_order = '" . safe_text_db($_GET['cat_down']) . "' WHERE photocat_order ='" . (safe_text_db($_GET['cat_down']) + 1) . "'");  // move the one under it up
            $dbh->query("UPDATE humo_photocat SET photocat_order = '" . (safe_text_db($_GET['cat_down']) + 1) . "' WHERE photocat_order = '999'");  // move this one down
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
        ?>

        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="thumbs">
            <input type="hidden" name="menu_tab" value="picture_categories">
            <input type="hidden" name="language_tree" value="<?= $language_tree; ?>">

            <div class="p-3 m-2 genealogy_search">

                <div class="row mb-2">
                    <div class="col-md-11">
                        <h3><?= __('Create categories for your photo albums'); ?></h3>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-11">
                        <li><?= __('Here you can create categories for all your photo albums.</li><li><b>A category will not be displayed in the photobook menu unless there is at least one picture for it.</b></li><li>Click "Default" to create one default name in all languages. Choose a language from the list to set a specific name for that language.<br><b>TIP:</b> First set an English name as default for all languages, then create specific names for those languages that you know. That way no tabs will display without a name in any language. In any case, setting a default name will not overwrite names for specific languages that you have already set.</li><li>The category prefix has to be made up of two letters and an underscore (like: <b>sp_</b> or <b>ws_</b>).</li><li>Pictures that you want to appear in a specific category have to be named with that prefix like: <b>sp_</b>John Smith.jpg</li><li>Pictures that you want to be displayed in the default photo category don\'t need a prefix.'); ?>
                        <li><?= __('A (sub)directory could also be a category. Example: category prefix = ab_, the directory name = ab.'); ?></li>
                    </div>
                </div>

                <table class="humo" cellspacing="0" style="margin-left:0px; text-align:center; width:80%">
                    <tr>
                        <td style="border-bottom:0px;"></td>
                        <td style="font-size:120%;border-bottom:0px;width:25%" white-space:nowrap;"><b><?= __('Category prefix'); ?></b></td>
                        <td style="font-size:120%;border-bottom:0px;width:60%"><b><?= __('Category name'); ?></b></td>
                    </tr>

                    <?php
                    $add = "";
                    if (isset($_POST['add_new_cat'])) {
                        $add = "&amp;add_new_cat=1";
                    }

                    // *** Language choice ***
                    $language_tree2 = $language_tree;
                    if ($language_tree == 'default') {
                        $language_tree2 = $selected_language;
                    }
                    include(__DIR__ . '/../../languages/' . $language_tree2 . '/language_data.php');
                    $select_top = '';
                    ?>

                    <tr>
                        <td style="border-top:0px"></td>
                        <td style="border-top:0px"></td>
                        <td style="border-top:0px;text-align:center">
                            <div class="row mb-2">
                                <div class="col-md-auto">
                                    <?= __('Language'); ?>:
                                </div>

                                <div class="col-md-auto">
                                    <?php include_once(__DIR__ . "/../../views/partial/select_language.php"); ?>
                                    <?php $language_path = 'index.php?page=thumbs&amp;menu_tab=picture_categories&amp;'; ?>
                                    <?= show_country_flags($language_tree2, '../', 'language_tree', $language_path); ?>
                                </div>

                                <div class="col-md-auto">
                                    <?= __('or'); ?>
                                    <a href="index.php?page=thumbs&amp;menu_tab=picture_categories&amp;language_tree=default<?= $add; ?>"><?= __('Default'); ?></a>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <?php
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

                        // arrows
                        $order_sequence = $dbh->query("SELECT MAX(photocat_order) AS maxorder, MIN(photocat_order) AS minorder FROM humo_photocat");
                        $orderDb = $order_sequence->fetch(PDO::FETCH_ASSOC);
                        $maxorder = $orderDb['maxorder'];
                        $minorder = $orderDb['minorder'];

                        $prefname = $catDb->photocat_prefix;
                        if ($catDb->photocat_prefix == 'none') {
                            $prefname = __('default - without prefix');
                        }  // display default in the display language, so it is clear to everyone
                    ?>
                        <tr>
                            <td>
                                <div style="width:25px;" class="d-inline-block">
                                    <?php
                                    if ($catDb->photocat_prefix != 'none') {
                                        echo '<a href="index.php?page=thumbs&amp;menu_tab=picture_categories&amp;cat_order=' . $catDb->photocat_order . '&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_drop=1"><img src="images/button_drop.png"></a>';
                                    }
                                    ?>
                                </div>

                                <div style="width:20px;" class="d-inline-block">
                                    <?php
                                    if ($catDb->photocat_order != $minorder) {
                                        echo '<a href="index.php?page=thumbs&amp;menu_tab=picture_categories&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_up=' . $catDb->photocat_order . '"><img src="images/arrow_up.gif"></a>';
                                    }
                                    ?>
                                </div>

                                <div style="width:20px;" class="d-inline-block">
                                    <?php
                                    if ($catDb->photocat_order != $maxorder) {
                                        echo '<a href="index.php?page=thumbs&amp;menu_tab=picture_categories&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_down=' . $catDb->photocat_order . '"><img src="images/arrow_down.gif"></a>';
                                    }
                                    ?>
                                </div>
                            </td>

                            <td style="white-space:nowrap;"><?= $prefname; ?></td>

                            <td><input type="text" name="<?= $catDb->photocat_prefix; ?>" value="<?= $catname; ?>" size="30" class="form-control form-control-sm"></td>
                        </tr>
                    <?php
                    }

                    $content = "";
                    if (isset($warning_prefix)) {
                        $content = $warning_prefix;
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td style="white-space:nowrap;"><input type="text" name="new_cat_prefix" value="<?= $content; ?>" size="6" class="form-control form-control-sm">
                            <?php if (isset($warning_invalid_prefix)) { ?>
                                <br><span style="color:red"><?= $warning_invalid_prefix; ?></span>
                            <?php
                            }
                            if (isset($warning_exist_prefix)) {
                            ?>
                                <br><span style="color:red"><?= $warning_exist_prefix; ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <input type="text" name="new_cat_name" value="" size="30" class="form-control form-control-sm">
                            <?php if (isset($warning_noname)) { ?>
                                <br><span style="color:red"><?= $warning_noname; ?></span>
                            <?php } ?>
                        </td>
                    </tr>

                    <?php if (isset($_GET['cat_drop']) && $_GET['cat_drop'] == 1) { ?>
                        <tr>
                            <td colspan="3" style="color:red;font-weight:bold;font-size:120%">
                                <?= __('Do you really want to delete category:'); ?>&nbsp;<?= $_GET['cat_prefix']; ?>&nbsp;?
                                &nbsp;&nbsp;&nbsp;<input type="button" style="color:red;font-weight:bold" onclick="location.href='index.php?page=thumbs&amp;menu_tab=picture_categories&amp;cat_order=<?= $_GET['cat_order']; ?>&amp;cat_prefix=<?= $_GET['cat_prefix']; ?>&amp;cat_drop2=1';" value="<?= __('Yes'); ?>">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" style="color:green;font-weight:bold" onclick="location.href='index.php?page=thumbs&amp;menu_tab=picture_categories';" value="<?= __('No'); ?>">
                            </td>
                        </tr>
                    <?php } ?>
                </table><br>
                <div style="margin-left:auto; margin-right:auto; text-align:center;"><input type="submit" name="save_cat" value="<?= __('Save changes'); ?>" class="btn btn-sm btn-success"></div>
            </div>

        </form>
        <?php
    }

    // *** Change filename ***
    if (isset($_POST['filename'])) {
        $picture_path_old = $_POST['picture_path'];
        $picture_path_new = $_POST['picture_path'];
        // *** If filename has a category AND a sub category directory exists, use it ***
        if (substr($_POST['filename'], 0, 2) !== substr($_POST['filename_old'], 0, 2) && ($_POST['filename'][2] == '_' || $_POST['filename_old'][2] == '_')) { // we only have to do this if something changed in a prefix
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
    if (isset($_POST["thumbnail"]) || isset($_POST['change_filename'])) {
        $pict_path = $data2Db->tree_pict_path;
        if (substr($pict_path, 0, 1) === '|') {
            $pict_path = 'media/';
        }

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
                    if (!in_array($filename, $ignore) && is_dir($prefx . $path . $filename)) {
                        $array_picture_folder[] = $prefx . $path . $filename . '/';
                        get_dirs($prefx, $path . $filename . '/');
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
                        $imgtype === "jpg" || $imgtype === "png" || $imgtype === "gif" && $gd["GIF Read Support"] == TRUE && $gd["GIF Create Support"] == TRUE
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
                        if (substr($filename, 0, 5) !== 'thumb' && !isset($_POST['change_filename'])) {
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
                            if ($imgtype === "jpg") {
                                $source = imagecreatefromjpeg($pict_path_original);
                            } elseif ($imgtype === "png") {
                                $source = imagecreatefrompng($pict_path_original);
                            } else {
                                $source = imagecreatefromgif($pict_path_original);
                            }

                            // *** Resize ***
                            imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                            if ($imgtype === "jpg") {
                                @imagejpeg($thumb, $pict_path_thumb);
                            } elseif ($imgtype === "png") {
                                @imagepng($thumb, $pict_path_thumb);
                            } else {
                                @imagegif($thumb, $pict_path_thumb);
                            }
                        }

                        // *** Show thumbnails ***
                        if (substr($filename, 0, 5) !== 'thumb') {
        ?>
                            <div class="photobook">
                                <img src="<?= $pict_path_thumb; ?>" title="<?= $pict_path_thumb; ?>">
                                <?php
                                // *** Show name of connected persons ***
                                include_once(__DIR__ . '/../../include/person_cls.php');
                                $picture_text = '';
                                $sql = "SELECT * FROM humo_events WHERE event_tree_id='" . safe_text_db($tree_id) . "'
                                    AND event_connect_kind='person' AND event_kind='picture'
                                    AND LOWER(event_event)='" . safe_text_db(strtolower($filename)) . "'";
                                $afbqry = $dbh->query($sql);
                                $picture_privacy = false;
                                while ($afbDb = $afbqry->fetch(PDO::FETCH_OBJ)) {
                                    $person_cls = new person_cls;
                                    $db_functions->set_tree_id($tree_id);
                                    $personDb = $db_functions->get_person($afbDb->event_connect_id);
                                    $name = $person_cls->person_name($personDb);

                                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                                    $uri_path = '../'; // *** Needed if url_rewrite is enabled ***
                                    $url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                                    $picture_text .= '<br><a href="' . $url . '">' . $name["standard_name"] . '</a><br>';
                                }
                                echo $picture_text;

                                if (isset($_POST['change_filename'])) {
                                ?>
                                    <form method="POST" action="index.php">
                                        <input type="hidden" name="page" value="thumbs">
                                        <input type="hidden" name="menu_tab" value="picture_show">
                                        <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                                        <input type="hidden" name="picture_path" value="<?= $selected_picture_folder; ?>">
                                        <input type="hidden" name="filename_old" value="<?= $filename; ?>">
                                        <input type="text" name="filename" value="<?= $filename; ?>" size="20">
                                        <input type="submit" name="change_filename" value="<?= __('Change filename'); ?>">
                                    </form>
                                <?php } else { ?>
                                    <div class="photobooktext"><?= $filename; ?></div>
                                <?php } ?>
                            </div>
    <?php
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