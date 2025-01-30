<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<h1 class="center"><?= __('CMS Own pages'); ?></h1>
<?php printf(__('Here you can add your own pages to %s! It\'s possible to use categories in the menu (like "Family history", "Family stories").'), 'HuMo-genealogy'); ?>

<ul class="nav nav-tabs pt-2">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($edit_cms_pages['menu_tab'] === 'pages') echo 'active'; ?>" href="index.php?page=edit_cms_pages&amp;cms_tab=pages"><?= __('Pages'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($edit_cms_pages['menu_tab'] === 'menu') echo 'active'; ?>" href="index.php?page=edit_cms_pages&amp;cms_tab=menu"><?= __('Menu'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($edit_cms_pages['menu_tab'] === 'settings')  echo 'active'; ?>" href="index.php?page=edit_cms_pages&amp;cms_tab=settings"><?= __('CMS settings'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;">

    <!-- Remove page, only allow numeric values -->
    <?php if (isset($_GET['page_remove']) && is_numeric($_GET['page_remove'])) { ?>
        <div class="alert alert-danger">
            <?php if (isset($humo_option["main_page_cms_id"]) && $humo_option["main_page_cms_id"] == $_GET['page_remove']) { ?>
                <?= __('This page is selected as homepage!'); ?>
            <?php } else { ?>
                <?= __('Are you sure you want to remove this page?'); ?>
                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="edit_cms_pages" value="cms_page">
                    <input type="hidden" name="page_id" value="<?= $_GET['page_remove']; ?>">
                    <input type="submit" name="page_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
                    <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
                </form>
            <?php } ?>
        </div>
    <?php
    }

    if (isset($_GET['menu_remove']) && is_numeric($_GET['menu_remove'])) {
        $qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_menu_id='" . $_GET['menu_remove'] . "' ORDER BY page_order");
        $count = $qry->rowCount();
    ?>

        <div class="alert alert-danger">
            <?php if ($count > 0) { ?>
                <?= __('There are still pages connected to this menu!<br>
Please disconnect the pages from this menu first.'); ?>
            <?php } else { ?>
                <?= __('Are you sure you want to remove this menu?'); ?>
                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="cms_tab" value="menu">
                    <input type="hidden" name="menu_id" value="<?= $_GET['menu_remove']; ?>">
                    <input type="submit" name="menu_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
                    <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
                </form>
            <?php } ?>
        </div>
    <?php
    }

    // *** Show and edit pages ***
    if ($edit_cms_pages['menu_tab'] === 'pages') {
        $qry = $dbh->query("SELECT * FROM humo_cms_pages ORDER BY page_menu_id, page_order");
        $page_nr = 0;
        //$edit_cms_pages['page_menu_id'] = 0;
        $list_menu_id = 0;
    ?>

        <div class="row">

            <div class="col-md-3">
                <!--  List of pages -->
                <table>
                    <?php
                    while ($cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ)) {
                        // ** Show name of menu/ category ***
                        if ($cms_pagesDb->page_menu_id == '9999') {
                    ?>
                            <tr>
                                <td colspan="2"><b>* <?= __('Hide page in menu'); ?> *</b></td>
                            </tr>
                        <?php
                            $page_nr = 0;
                            //} elseif ($cms_pagesDb->page_menu_id > 0 && $edit_cms_pages['page_menu_id'] != $cms_pagesDb->page_menu_id) {
                        } elseif ($cms_pagesDb->page_menu_id > 0 && $list_menu_id != $cms_pagesDb->page_menu_id) {
                            $qry_menu = $dbh->query("SELECT * FROM humo_cms_menu WHERE menu_id='" . $cms_pagesDb->page_menu_id . "'");
                            $cmsDb = $qry_menu->fetch(PDO::FETCH_OBJ);
                        ?>
                            <tr>
                                <td colspan="2"><b><?= $cmsDb->menu_name; ?></b></td>
                            </tr>
                        <?php
                            $page_nr = 0;
                            //$edit_cms_pages['page_menu_id'] = $cms_pagesDb->page_menu_id;
                            $list_menu_id = $cms_pagesDb->page_menu_id;
                        }

                        $page_nr++;
                        ?>
                        <tr>
                            <td style="width:60px;">
                                <a href="index.php?page=edit_cms_pages&amp;select_page=<?= $cms_pagesDb->page_id; ?>&amp;page_remove=<?= $cms_pagesDb->page_id; ?>">
                                    <img src="images/button_drop.png" alt="<?= __('Remove page'); ?>" border="0">
                                </a>
                                <?php if ($page_nr != '1') { ?>
                                    <a href="index.php?page=edit_cms_pages&amp;page_up=<?= $previous_page; ?>&amp;select_page=<?= $cms_pagesDb->page_id; ?>"><img src="images/arrow_up.gif" border="0" alt="up"></a>
                                <?php
                                }
                                if ($page_nr != $edit_cms_pages['pages_in_category'][$cms_pagesDb->page_menu_id]) {
                                ?>
                                    <a href="index.php?page=edit_cms_pages&amp;page_down=<?= $cms_pagesDb->page_order; ?>&amp;select_page=<?= $cms_pagesDb->page_id; ?>&amp;menu_id=<?= $cms_pagesDb->page_menu_id; ?>"><img src="images/arrow_down.gif" border="0" alt="down"></a>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="index.php?page=edit_cms_pages&amp;select_page=<?= $cms_pagesDb->page_id; ?>">
                                    <?= $cms_pagesDb->page_title ? $cms_pagesDb->page_title : '[' . __('No page title') . ']'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php
                        $previous_page = $cms_pagesDb->page_id;
                    }
                    ?>
                </table><br>
                <a href="index.php?page=edit_cms_pages"><?= __('Add page'); ?></a>
            </div>

            <div class="col-md-9">
                <?= __('"Hide page in menu" is a special option. These pages can be accessed using a direct link.'); ?><br>
                <?php
                if ($edit_cms_pages['page_id']) {
                    // SERVER_NAME   127.0.0.1
                    // REQUEST_URI: /url_test/index/1abcd2345/
                    // REQUEST_URI: /url_test/index.php?variabele=1

                    // Search for: /admin/ in $_SERVER['PHP_SELF']
                    $position = strpos($_SERVER['PHP_SELF'], '/admin/');
                    $path_tmp = 'http://' . $_SERVER['SERVER_NAME'] . substr($_SERVER['REQUEST_URI'], 0, $position);
                ?>
                    <?= __('This page can be accessed using this link: '); ?><br>
                    <b><?= $path_tmp; ?>/index.php?page=cms_pages&amp;select_page=<?= $edit_cms_pages['page_id']; ?>&amp;menu=1</b><br>
                    <?php if ($humo_option["url_rewrite"] == "j") { ?>
                        <?= __('or'); ?>: <b><?= $path_tmp; ?>/cms_pages/<?= $edit_cms_pages['page_id']; ?>?menu=1</b><br>
                <?php
                    }
                }
                ?>

                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="cms_pages" value="cms_page">
                    <input type="hidden" name="page_id" value="<?= $edit_cms_pages['page_id']; ?>">
                    <input type="hidden" name="page_menu_id_old" value="<?= $edit_cms_pages['page_menu_id']; ?>">
                    <input type="text" name="page_title" value="<?= $edit_cms_pages['page_title']; ?>" size=25>
                    <select size="1" name="page_menu_id">
                        <option value='0'>* <?= __('No menu selected'); ?> *</option>
                        <option value="9999" <?php if ($edit_cms_pages['page_menu_id'] == '9999') echo ' selected'; ?>>* <?= __('Hide page in menu'); ?> *</option>
                        <?php
                        $qry = $dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
                        while ($menuDb = $qry->fetch(PDO::FETCH_OBJ)) {
                        ?>
                            <option value="<?= $menuDb->menu_id; ?>" <?= $menuDb->menu_id == $edit_cms_pages['page_menu_id'] ? 'selected' : ''; ?>>
                                <?= $menuDb->menu_name; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <input type="checkbox" name="page_status" <?= $edit_cms_pages['page_status'] ? 'checked' : ''; ?>><?= __('Published'); ?>

                    <?php if ($edit_cms_pages['select_page'] == 0) { ?>
                        <input type="submit" name="add_page" value="<?= __('Add'); ?>" class="btn btn-sm btn-success">
                    <?php } else { ?>
                        <input type="submit" name="change_page" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">
                    <?php } ?>

                    <?= __('Visitors counter'); ?>: <?= $edit_cms_pages['page_counter']; ?><br>
                    <textarea id="editor" name="page_text"><?= $edit_cms_pages['page_text']; ?></textarea>
                </form>
            </div>
        </div>

        <!-- TinyMCE Editor -->
        <script src="../assets/tinymce/tinymce.min.js"></script>
        <script src="include/tinymce_settings/tinymce_settings.js"></script>
    <?php
    }

    // *** Show and edit menu's ***
    if ($edit_cms_pages['menu_tab'] === 'menu') {
        $qry = $dbh->query("SELECT * FROM humo_cms_menu ORDER BY menu_order");
        $count_menu = $qry->rowCount();
    ?>
        <!-- List of categories -->
        <?= __('Add and edit menu/ category items:'); ?>
        <table class="table">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Order'); ?></th>
                    <th><?= __('Menu item/ category'); ?></th>
                    <th><?= __('Save'); ?></th>
                </tr>
            </thead>
            <?php while ($cms_pagesDb = $qry->fetch(PDO::FETCH_OBJ)) { ?>
                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="cms_tab" value="menu">
                    <input type="hidden" name="menu_id" value="<?= $cms_pagesDb->menu_id; ?>">
                    <tr>
                        <td>
                            <a href="index.php?page=<?= $page; ?>&amp;select_menu=<?= $cms_pagesDb->menu_id; ?>&amp;menu_remove=<?= $cms_pagesDb->menu_id; ?>">
                                <img src="images/button_drop.png" alt="<?= __('Remove menu'); ?>" border="0">
                            </a>
                            <?php if ($cms_pagesDb->menu_order != '1') { ?>
                                <a href="index.php?page=<?= $page; ?>&amp;select_menu=<?= $cms_pagesDb->menu_id; ?>&amp;menu_up=<?= $cms_pagesDb->menu_order; ?>">
                                    <img src="images/arrow_up.gif" border="0" alt="up">
                                </a>
                            <?php
                            }
                            if ($cms_pagesDb->menu_order != $count_menu) {
                            ?>
                                <a href="index.php?page=<?= $page; ?>&amp;select_menu=<?= $cms_pagesDb->menu_id; ?>&amp;menu_down=<?= $cms_pagesDb->menu_order; ?>">
                                    <img src="images/arrow_down.gif" border="0" alt="down">
                                </a>
                            <?php } ?>
                        </td>
                        <td><input type="text" name="menu_name" value="<?= $cms_pagesDb->menu_name; ?>" size=50></td>
                        <td><input type="submit" name="change_menu" value="<?= __('Save'); ?>" class="btn btn-sm btn-success"></td>
                    </tr>
                </form>
            <?php } ?>

            <form method="post" action="index.php" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="cms_tab" value="menu">
                <tr bgcolor="green">
                    <td><br></td>
                    <td><input type="text" name="menu_name" value="" size=50></td>
                    <td><input type="submit" name="add_menu" value="<?= __('Add'); ?>" class="btn btn-sm btn-primary"></td>
                </tr>
            </form>
        </table>
    <?php
    }

    if ($edit_cms_pages['menu_tab'] === 'settings') {
        // *** Automatic installation or update ***
        if (!isset($humo_option["cms_images_path"])) {
            $dbh->query("INSERT INTO humo_settings SET setting_variable='cms_images_path', setting_value='|'");
            $cms_images_path = '|';
        } else {
            $cms_images_path = $humo_option["cms_images_path"];
        }

        // *** Automatic installation or update ***
        if (!isset($humo_option["main_page_cms_id"])) {
            $dbh->query("INSERT INTO humo_settings SET setting_variable='main_page_cms_id', setting_value=''");
            $main_page_cms_id = '';
        } else {
            $main_page_cms_id = $humo_option["main_page_cms_id"];
        }

        if (isset($_POST['cms_images_path'])) {
            $cms_images_path = $_POST['cms_images_path'];
            if (substr($_POST['cms_images_path'], 0, 1) === '|') {
                if (isset($_POST['default_path']) && $_POST['default_path'] == 'no') {
                    $cms_images_path = substr($cms_images_path, 1);
                }
            } elseif (isset($_POST['default_path']) && $_POST['default_path'] == 'yes') {
                $cms_images_path = '|' . $cms_images_path;
            }

            // *** Save settings***
            $db_functions->update_settings('cms_images_path', $cms_images_path);

            //$humo_option["cms_images_path"]=$_POST["cms_images_path"];
            //$cms_images_path=$humo_option["cms_images_path"];
            $humo_option["cms_images_path"] = $cms_images_path;
            $cms_images_path = $humo_option["cms_images_path"];
        }

        if (isset($_POST['main_page_cms_id'])) {
            // *** Save settings***
            $db_functions->update_settings('main_page_cms_id', $_POST["main_page_cms_id"]);

            $humo_option["main_page_cms_id"] = $_POST["main_page_cms_id"];
            $main_page_cms_id = $humo_option["main_page_cms_id"];
        }

        if (isset($_POST['languages_choice']) && $_POST['languages_choice'] == "all") {
            // admin chose to use one page for all languages - delete any language_specific entries if set (format: main_page_cms_id_nl etc)
            // note that because of the last underline before the %, the default main_page_id will not be affected!
            $dbh->query("DELETE FROM humo_settings WHERE setting_variable LIKE 'main_page_cms_id_%'");
        }

        if ((isset($_POST['cms_settings']) and $_POST['cms_settings'] != '1') && (isset($_POST['languages_choice']) && $_POST['languages_choice'] == "specific")) {
            // admin chose to use different pages for specific languages
            $counter = count($language_file);
            // admin chose to use different pages for specific languages
            for ($i = 0; $i < $counter; $i++) {
                if (!isset($humo_option["main_page_cms_id_" . $language_file[$i]])) {
                    $dbh->query("INSERT INTO humo_settings SET setting_variable='main_page_cms_id_" . $language_file[$i] . "', setting_value='" . $_POST['main_page_cms_id_' . $language_file[$i]] . "'");
                } else {
                    // *** Save settings***
                    $db_functions->update_settings('main_page_cms_id_' . $language_file[$i], $_POST['main_page_cms_id_' . $language_file[$i]]);
                }
            }
        }
    ?>

        <p>
        <form method="post" name="cms_setting_form" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="cms_settings" value="1"> <!-- if Save button is not pressed but checkboxes changed! -->
            <table class="table">
                <thead class="table-primary">
                    <tr>
                        <th><?= __('CMS Settings'); ?></th>
                        <th><input type="submit" name="cms_settings" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
                    </tr>
                </thead>

                <tr>
                    <td>
                        <?= __('Path for pictures in CMS pages'); ?>:<br>
                        <?= __('To point the main humo-gen folder, use ../../../foldername<br>
To point to a folder outside (and parallel to) the humo-gen folder, use ../../../../foldername'); ?>
                    </td>
                    <td>
                        <?php
                        // *** Picture path. A | character is used for a default path (the old path will remain in the field) ***
                        if (substr($cms_images_path, 0, 1) === '|') {
                            $checked1 = ' checked';
                            $checked2 = '';
                            //$edit_cms_pages['default_path'] = true;
                        } else {
                            $checked1 = '';
                            $checked2 = ' checked';
                            //$edit_cms_pages['default_path'] = false;
                        }
                        if (substr($cms_images_path, 0, 1) === '|') {
                            $cms_images_path = substr($cms_images_path, 1);
                        }
                        ?>
                        <input type="radio" value="yes" name="default_path" <?= $checked1; ?>><?= __('Use default picture path:'); ?><b>media/cms</b><br>

                        <input type="radio" value="no" name="default_path" <?= $checked2; ?>>
                        <input type="text" name="cms_images_path" value="<?= $cms_images_path; ?>" size=25>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php printf(__('Select main homepage (welcome page for visitors) for %s<br>
<b>The selected CMS page will replace the main index!</b>'), 'HuMo-genealogy'); ?>
                    </td>
                    <td>
                        <?php
                        $lang_qry = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable LIKE 'main_page_cms_id_%'"); // check if there are language-specific entries
                        $num = $lang_qry->rowCount();
                        $checked1 = ' checked';
                        $checked2 = '';
                        if (isset($_POST['languages_choice']) && ($num >= 1 && $_POST['languages_choice'] != "all" || $num < 1 && $_POST['languages_choice'] == "specific")) {
                            // there are language specific entries so don't check the radiobox "Use for all languages"
                            $checked1 = '';
                            $checked2 = ' checked';
                        }
                        echo '<input type="radio" onChange="document.cms_setting_form.submit()" value="all" name="languages_choice" ' . $checked1 . '> ' . __('Use for all languages');
                        ?>
                        <select size="1" name="main_page_cms_id">
                            <option value=''>* <?= __('Standard main index'); ?> *</option>
                            <?php
                            $qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
                            while ($pageDb = $qry->fetch(PDO::FETCH_OBJ)) {
                            ?>
                                <option value="<?= $pageDb->page_id; ?>" <?= $pageDb->page_id == $main_page_cms_id ? 'selected' : ''; ?>>
                                    <?= $pageDb->page_title; ?>
                                </option>
                            <?php } ?>
                        </select><br><br>
                        <input type="radio" onChange="document.cms_setting_form.submit()" value="specific" name="languages_choice" <?= $checked2; ?>> <?= __('Set per language'); ?>

                        <?php if ($checked1 === '') { ?>
                            <br>
                            <table style="border:none">
                                <?php
                                $counter = count($language_file);
                                for ($i = 0; $i < $counter; $i++) {
                                    include(__DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php');
                                    $select_page = 'dummy';
                                    $qry = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'main_page_cms_id_" . $language_file[$i] . "'");
                                    while ($lang_pageDb = $qry->fetch(PDO::FETCH_OBJ)) {
                                        $select_page = $lang_pageDb->setting_value;
                                    }
                                    $sel = '';
                                    if ($select_page != 'dummy' && $select_page != '') {
                                        // no entry was found - use default
                                        $sel = $select_page;
                                    } elseif ($select_page == 'dummy') {
                                        //else the value was '' which means language was set individually to "main index", so don't set "select" so "main index" will show
                                        $sel = $main_page_cms_id;
                                    }
                                ?>

                                    <tr>
                                        <td>
                                            <img src="<?= '../languages/' . $language_file[$i]; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>" style="border:none;"><?= $language["name"]; ?>
                                        </td>
                                        <td>
                                            <select size="1" name="main_page_cms_id_<?= $language_file[$i]; ?>">
                                                <option value=''>* <?= __('Standard main index'); ?> *</option>
                                                <?php
                                                $qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
                                                while ($pageDb = $qry->fetch(PDO::FETCH_OBJ)) {
                                                ?>
                                                    <option value="<?= $pageDb->page_id; ?>" <?= $pageDb->page_id == $sel ? 'selected' : ''; ?>><?= $pageDb->page_title; ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </form>
    <?php } ?>
</div>