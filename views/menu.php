<?php
// *** LTR or RTL ***
$rtlmark = 'ltr';
if ($language["dir"] == "rtl") {
    $rtlmark = 'rtl';
}

// *** Show logo or name of website ***
$logo = $humo_option["database_name"];
if (is_file('media/logo.png'))
    $logo = '<img src="media/logo.png">';
elseif (is_file('media/logo.jpg'))
    $logo = '<img src="media/logo.jpg">';
?>

<div id="top_menu"> <!-- TODO At this moment only needed for print version?  -->
    <div id="top" style="direction:<?= $rtlmark; ?>">

        <div class="row g-3">
            <div class="col-sm-5">
                <span id="top_website_name">
                    <!-- *** Show logo or name of website *** -->
                    &nbsp;<a href="<?= $humo_option["homepage"]; ?>"><?= $logo; ?></a>
                </span>
                &nbsp;&nbsp;

                <?php
                // *** Select family tree ***
                if (!$bot_visit) {
                    $sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
                    $tree_search_result2 = $dbh->query($sql);
                    $num_rows = $tree_search_result2->rowCount();
                    $count = 0;
                    // *** Changed 1 into 0. So pull-down menu is always shown ***
                    //if ($num_rows > 1) {
                    if ($num_rows > 0) {
                        $link = $link_cls->get_link($uri_path, 'tree_index');
                ?>
            </div>

            <div class="col-sm-2">
                <form method="POST" action="<?= $link; ?>" style="display : inline;">
                    <!-- <?= __('Family tree') . ': '; ?> -->
                    <select size="1" name="tree_id" onChange="this.form.submit();" class="form-select form-select-sm">
                        <option value=""><?= __('Select a family tree:'); ?></option>
                        <?php
                        while ($tree_searchDb = $tree_search_result2->fetch(PDO::FETCH_OBJ)) {
                            // *** Check if family tree is shown or hidden for user group ***
                            $hide_tree_array2 = explode(";", $user['group_hide_trees']);
                            $hide_tree2 = false;
                            if (in_array($tree_searchDb->tree_id, $hide_tree_array2)) $hide_tree2 = true;
                            if ($hide_tree2 == false) {
                                $selected = '';
                                if (isset($_SESSION['tree_prefix'])) {
                                    if ($tree_searchDb->tree_prefix == $_SESSION['tree_prefix']) {
                                        $selected = ' selected';
                                    }
                                } else {
                                    if ($count == 0) {
                                        $_SESSION['tree_prefix'] = $tree_searchDb->tree_prefix;
                                        $selected = ' selected';
                                    }
                                }
                                $treetext = show_tree_text($tree_searchDb->tree_id, $selected_language);
                                echo '<option value="' . $tree_searchDb->tree_id . '"' . $selected . '>' . @$treetext['name'] . '</option>';
                                $count++;
                            }
                        }
                        ?>
                    </select>
                </form>
            </div>

    <?php
                    }
                }
    ?>

    <?php
    // *** This code is used to restore $dataDb reading. Used for picture etc. ***
    if (is_string($_SESSION['tree_prefix']) and $_SESSION['tree_prefix'])
        $dataDb = $db_functions->get_tree($_SESSION['tree_prefix']);

    // *** Show quicksearch field ***
    if (!$bot_visit) {
        $menu_path = $link_cls->get_link($uri_path, 'list', $tree_id);

        $quicksearch = '';
        if (isset($_POST['quicksearch'])) {
            $quicksearch = safe_text_show($_POST['quicksearch']);
            $_SESSION["save_quicksearch"] = $quicksearch;
        }
        if (isset($_SESSION["save_quicksearch"])) {
            $quicksearch = $_SESSION["save_quicksearch"];
        }
        if ($humo_option['min_search_chars'] == 1) {
            $pattern = "";
            $min_chars = " 1 ";
        } else {
            $pattern = 'pattern=".{' . $humo_option['min_search_chars'] . ',}"';
            $min_chars = " " . $humo_option['min_search_chars'] . " ";
        }
    ?>

        <div class="col-sm-2">
            <form method="post" action="<?= $menu_path; ?>">
                <input type="hidden" name="index_list" value="quicksearch">
                <input type="hidden" name="search_database" value="tree_selected">
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm" name="quicksearch" placeholder="<?= __('Name'); ?>" value="<?= $quicksearch; ?>" size="10" <?= $pattern; ?> title="<?= __('Minimum:') . $min_chars . __('characters'); ?>">
                    <button type="submit" class="btn btn-success btn-sm"><?= __('Search'); ?></button>
                </div>
            </form>
        </div>

        <!-- hidden in mobile version -->
        <div class="col-sm-1 d-none d-md-block">
            <?php
            // *** Link for extended search form ***
            $menu_path = $link_cls->get_link($uri_path, 'list', $tree_id, true);
            $menu_path .= 'adv_search=1&amp;index_list=search';
            ?>

            <!--
            <a href="<?= $menu_path; ?>"><img src="images/advanced-search.jpg" width="17" alt="<?= __('Advanced search'); ?>"></a>
    -->

            <form method="post" action="<?= $menu_path; ?>">
                <button type="submit" class="btn btn-light btn-sm"><img src="images/advanced-search.jpg" width="17" alt="<?= __('Advanced search'); ?>"></button>
            </form>
        </div>
    <?php
    }

    // *** Favourite list for family pages ***
    if (!$bot_visit) {
        include_once(__DIR__ . "/../include/person_cls.php");
        // *** Show favorites in selection list ***
        $link = $link_cls->get_link($uri_path, 'family', $tree_id);
    ?>
        <div class="col-sm-2">

            <form method="POST" action="<?= $link; ?>" style="display : inline;">
                <!-- <img src="images/favorite_blue.png" alt="<?= __('Favourites'); ?>"> -->
                <select size=1 name="humo_favorite_id" onChange="this.form.submit();" class="form-select form-select-sm">
                    <option value=""><?= __('Favourites list:'); ?></option>
                    <?php
                    if (isset($_SESSION["save_favorites"])) {
                        sort($_SESSION['save_favorites']);
                        foreach ($_SESSION['save_favorites'] as $key => $value) {
                            if (is_string($value) and $value) {
                                $favorite_array2 = explode("|", $value);

                                // *** July 2023: New favorite system: 0=tree/ 1=family/ 2=person GEDCOM number ***
                                // *** Show only persons in selected family tree ***
                                if ($tree_id == $favorite_array2['0']) {
                                    // *** Check if family tree is still the same family tree ***
                                    // *** Proces man using a class ***
                                    $test_favorite = $db_functions->get_person($favorite_array2['2']);
                                    if ($test_favorite) {
                                        //$name_cls = new person_cls($favorite_array2['3']);
                                        //$name_cls = new person_cls($favorite_array2['2']);
                                        $name_cls = new person_cls($test_favorite);
                                        $name = $name_cls->person_name($test_favorite);
                                        echo '<option value="' . $favorite_array2['1'] . '|' . $favorite_array2['2'] . '">' . $name['name'] . ' [' . $favorite_array2['2'] . ']</option>';
                                    }
                                }
                            }
                        }
                    }
                    ?>
                </select>
            </form>
        </div>
        </div>

    <?php
    }
    ?>

    </div> <!-- End of Top -->

    <?php
    // *** Menu ***
    $ie7_rtlhack = '';  // in some skins in rtl display in IE7 menu runs off the screen and needs float:right
    if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 7.0") !== false and $language['dir'] == "rtl") {
        $ie7_rtlhack = ' class="headerrtl"';
    }

    $menu_path_home = $link_cls->get_link($uri_path, 'index', $tree_id);

    // *** Mobile menu ***
    $menu_top_home = '';

    if ($user['group_menu_login'] == 'j') {
        $menu_item_login = '';
        if ($page == 'login') {
            $menu_item_login = ' id="current"';
        }

        $menu_path_login = $link_cls->get_link($uri_path, 'login');
    }

    // *** Log off ***
    $menu_item_logoff = '';
    $menu_path_logoff = $link_cls->get_link($uri_path, 'logoff');

    $menu_path_help = $link_cls->get_link($uri_path, 'help');

    $menu_item_register = '';
    if ($page == 'register') {
        $menu_item_register = ' id="current"';
    }
    $menu_path_register = $link_cls->get_link($uri_path, 'register');

    $menu_item_cms = '';
    if ($page == 'cms_pages') {
        $menu_item_cms = ' id="current"';
    }
    $menu_path_cms = $link_cls->get_link($uri_path, 'cms_pages');

    $menu_path_cookies = $link_cls->get_link($uri_path, 'cookies');

    $menu_item_persons = '';
    if ($page == 'persons' || $page == 'family' || $page == 'family_rtf' || $page == 'descendant' || $page == 'ancestor' || $page == 'ancestor_chart' || $page == 'ancestor_sheet' || $page == 'list') {
        $menu_item_persons = ' id="current"';
    }
    $menu_path_persons = $link_cls->get_link($uri_path, 'list', $tree_id, true);
    $menu_path_persons .= 'reset=1';

    $menu_item_names = '';
    if ($page == 'list_names') {
        $menu_item_names = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_names = 'list_names/' . $tree_id . '/';
    } else {
        $menu_path_names = 'index.php?page=list_names&amp;tree_id=' . $tree_id;
    }
    // Doesn't work yet. An extra / is added at end of link.
    //$menu_path_names = $link_cls->get_link($uri_path, 'list_names',$tree_id);

    $menu_item_user_settings = '';
    if ($page == 'settings') {
        $menu_item_user_settings = ' id="current"';
    }
    $menu_path_user_settings = $link_cls->get_link($uri_path, 'user_settings');

    $menu_item_admin = '';
    $menu_path_admin = 'admin/index.php';

    $menu_item_anniversary = '';
    if ($page == 'birthday') {
        $menu_item_anniversary = ' id="current"';
    }
    $menu_path_anniversary = $link_cls->get_link($uri_path, 'anniversary');

    $menu_item_statistics = '';
    if ($page == 'statistics') {
        $menu_item_statistics = ' id="current"';
    }
    $menu_path_statistics = $link_cls->get_link($uri_path, 'statistics');

    $menu_item_calculator = '';
    if ($page == 'relations') {
        $menu_item_calculator = ' id="current"';
    }
    $menu_path_calculator = $link_cls->get_link($uri_path, 'relations');

    $menu_item_map = '';
    if ($page == 'maps') {
        $menu_item_map = ' id="current"';
    }
    $menu_path_map = $link_cls->get_link($uri_path, 'maps');

    $menu_item_contact = '';
    if ($page == 'mailform') {
        $menu_item_contact = ' id="current"';
    }
    $menu_path_contact = $link_cls->get_link($uri_path, 'mailform');

    // *** Latest changes ***
    $menu_item_latest_changes = '';
    if ($page == 'latest_changes') {
        $menu_item_latest_changes = ' id="current"';
    }
    $menu_path_latest_changes = $link_cls->get_link($uri_path, 'latest_changes', $tree_id);

    $menu_item_tree_index = '';
    if ($page == 'tree_index') {
        $menu_item_tree_index = ' id="current"';
    }
    $menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index', $tree_id);

    $menu_item_places_persons = '';
    if ($page == 'places') {
        $menu_item_places_persons = ' id="current"';
    }
    $menu_path_places_persons = $link_cls->get_link($uri_path, 'list', $tree_id, true);
    $menu_path_places_persons .= 'index_list=places&amp;reset=1';

    $menu_item_list_places_families = '';
    if ($page == 'list_places_families') {
        $menu_item_list_places_families = ' id="current"';
    }
    $menu_path_list_places_families = $link_cls->get_link($uri_path, 'list_places_families', $tree_id, true);
    $menu_path_list_places_families .= 'reset=1';

    $menu_item_photoalbum = '';
    if ($page == 'photoalbum') {
        $menu_item_photoalbum = ' id="current"';
    }
    $menu_path_photoalbum = $link_cls->get_link($uri_path, 'photoalbum', $tree_id);

    $menu_item_sources = '';
    if ($page == 'sources' || $page == 'source') {
        $menu_item_sources = ' id="current"';
    }
    $menu_path_sources = $link_cls->get_link($uri_path, 'sources', $tree_id);

    $menu_item_addresses = '';
    if ($page == 'addresses' || $page == 'address') {
        $menu_item_addresses = ' id="current"';
    }
    $menu_path_addresses = $link_cls->get_link($uri_path, 'addresses', $tree_id);

    ?>
    <div id="humo_menu" <?= $ie7_rtlhack; ?>>
        <ul class="humo_menu_item">
            <!-- You can use this link, for an extra link to another main homepage -->
            <!-- <li><a href="...">Homepage</a></li> -->
            <li <?php if ($page == 'index') echo 'id="current"'; ?> class="mobile_hidden"><a href="<?= $menu_path_home; ?>"><img src="images/menu_mobile.png" width="18" class="mobile_icon" alt="<?= __('Home'); ?>"> <?= __('Home'); ?></a></li>
            <li class="mobile_visible">
                <div class="<?= $rtlmarker; ?>sddm">
                    <?php
                    echo '<a href="' . $menu_path_home . '"';
                    echo ' onmouseover="mopen(event,\'m0x\',\'?\',\'?\')"';
                    echo ' onmouseout="mclosetime()"' . $menu_top_home . '><img src="images/menu_mobile.png" width="18" alt="' . __('Home') . '"></a>';
                    ?>
                    <div id="m0x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                        <ul class="humo_menu_item2">
                            <li <?php if ($page == 'index') echo ' id="current"'; ?>><a href="<?= $menu_path_home; ?>"><?= __('Home'); ?></a></li>
                            <?php
                            // *** Login - Logoff ***
                            if ($user['group_menu_login'] == 'j') {
                                if (!$user["user_name"]) {
                                    echo '<li' . $menu_item_login . '><a href="' . $menu_path_login . '">' . __('Login') . "</a></li>\n";
                                } else {
                                    // *** Link to administration ***
                                    if ($user['group_edit_trees'] or $user['group_admin'] == 'j') {
                                        echo '<li' . $menu_item_admin . '><a href="' . $menu_path_admin . '" target="_blank">' . __('Admin') . '</a></li>';
                                    }
                                    echo '<li' . $menu_item_logoff . '><a href="' . $menu_path_logoff . '">' . __('Logoff') . '</a></li>';
                                }
                            }

                            // *** Link to registration form ***
                            if (!$user["user_name"] and $humo_option["visitor_registration"] == 'y') {
                                echo '<li' . $menu_item_register . '><a href="' . $menu_path_register . '">' . __('Register') . '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
            <?php

            // *** Menu genealogy (for CMS pages) ***
            if ($user['group_menu_cms'] == 'y') {
                $cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
                if ($cms_qry->rowCount() > 0) {
                    echo '<li' . $menu_item_cms . '><a href="' . $menu_path_cms . '"><img src="images/reports.gif" class="mobile_hidden" alt="' . __('Information') . '"><span class="mobile_hidden"> </span>' . __('Information') . "</a></li>\n";
                }
            }

            // *** Menu: Family tree ***
            if ($bot_visit and $humo_option["searchengine_cms_only"] == 'y') {
                // *** Show CMS link for search bots ***
                // *** Menu genealogy (for CMS pages) ***
                $cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
                if ($cms_qry->rowCount() > 0) {
                    echo '<li' . $menu_item_cms . '><a href="' . $menu_path_cms . '">' . __('Information') . "</a></li>\n";
                }
            } else {
                $menu_top = '';
                if ($page == 'tree_index') {
                    $menu_top = ' id="current_top"';
                }
                if ($page == 'persons' || $page == 'family' || $page == 'family_rtf' || $page == 'descendant' || $page == 'ancestor_report' || $page == 'ancestor_chart' || $page == 'ancestor_sheet' || $page == 'list') {
                    $menu_top = ' id="current_top"';
                }
                if ($page == 'list_names') {
                    $menu_top = ' id="current_top"';
                }
                if ($page == 'sources' || $page == 'source') {
                    $menu_top = ' id="current_top"';
                }
                if ($page == 'places') {
                    $menu_top = ' id="current_top"';
                }
                if ($page == 'list_places_families') {
                    $menu_top = ' id="current_top"';
                }
                if ($page == 'photoalbum') {
                    $menu_top = ' id="current_top"';
                }
                if ($page == 'addresses' || $page == 'address') {
                    $menu_top = ' id="current_top"';
                }
            ?>

                <li>
                    <div class="<?= $rtlmarker; ?>sddm">
                        <?php
                        echo '<a href="' . $menu_path_tree_index . '"';
                        echo ' onmouseover="mopen(event,\'mft\',\'?\',\'?\')"';
                        echo ' onmouseout="mclosetime()"' . $menu_top . '><img src="images/family_tree.png" class="mobile_hidden" alt="' . __('Family tree') . '"><span class="mobile_hidden"> </span>' . __('Family tree') . '</a>';
                        ?>
                        <div id="mft" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                echo '<li' . $menu_item_tree_index . '><a href="' . $menu_path_tree_index . '">' . __('Family tree index') . '</a></li>';

                                // *** Persons ***
                                if ($user['group_menu_persons'] == "j") {
                                    echo '<li' . $menu_item_persons . '><a href="' . $menu_path_persons . '">' . __('Persons') . '</a></li>';
                                }
                                // *** Names ***
                                if ($user['group_menu_names'] == "j") {
                                    echo '<li' . $menu_item_names . '><a href="' . $menu_path_names . '">' . __('Names') . "</a></li>\n";
                                }

                                // *** Places ***
                                if ($user['group_menu_places'] == "j") {
                                    echo '<li' . $menu_item_places_persons . '><a href="' . $menu_path_places_persons . '">' . __('Places (by persons)') . "</a></li>\n";
                                    echo '<li' . $menu_item_list_places_families . '><a href="' . $menu_path_list_places_families . '">' . __('Places (by families)') . "</a></li>\n";
                                }

                                if ($user['group_photobook'] == 'j') {
                                    echo '<li' . $menu_item_photoalbum . '><a href="' . $menu_path_photoalbum . '">' . __('Photobook') . "</a></li>\n";
                                }

                                //if ($user['group_sources']=='j'){
                                if ($user['group_sources'] == 'j' and $tree_prefix_quoted != '' and $tree_prefix_quoted != 'EMPTY') {
                                    // *** Check if there are sources in the database ***
                                    //$source_qry=$dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='".$tree_id."'AND source_shared='1'");
                                    $source_qry = $dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'");
                                    @$sourceDb = $source_qry->rowCount();
                                    if ($sourceDb > 0) {
                                        echo '<li' . $menu_item_sources . '><a href="' . $menu_path_sources . '">' . __('Sources') . "</a></li>\n";
                                    }
                                }

                                if ($user['group_addresses'] == 'j' and $tree_prefix_quoted != '' and $tree_prefix_quoted != 'EMPTY') {
                                    // *** Check for addresses in the database ***
                                    $address_qry = $dbh->query("SELECT * FROM humo_addresses
                                        WHERE address_tree_id='" . $tree_id . "' AND address_shared='1'");
                                    @$addressDb = $address_qry->rowCount();
                                    if ($addressDb > 0) {
                                        echo '<li' . $menu_item_addresses . '><a href="' . $menu_path_addresses . '">' . __('Addresses') . "</a></li>\n";
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </li>
                <?php
            } // *** End of bot check ***

            // *** Menu: Tools menu ***
            if ($bot_visit and $humo_option["searchengine_cms_only"] == 'y') {
                //
            } else {

                // make sure at least one of the submenus is activated, otherwise don't show TOOLS menu
                //	AND $dbh->query("SELECT * FROM humo_settings WHERE setting_variable ='geo_trees'
                //		AND setting_value LIKE '%@".$tree_id.";%' ")->rowCount() > 0)
                if (
                    $user["group_birthday_list"] == 'j' or $user["group_showstatistics"] == 'j' or $user["group_relcalc"] == 'j'
                    or ($user["group_googlemaps"] == 'j' and $dbh->query("SHOW TABLES LIKE 'humo_location'")->rowCount() > 0)
                    or ($user["group_contact"] == 'j' and $dataDb->tree_owner and $dataDb->tree_email)
                    or $user["group_latestchanges"] == 'j'
                ) {
                ?>
                    <li>
                        <div class="<?= $rtlmarker; ?>sddm">
                            <?php
                            $menu_top = '';
                            if ($page == 'birthday') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($page == 'statistics') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($page == 'relations') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($page == 'maps') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($page == 'mailform') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($page == 'latest_changes') {
                                $menu_top = ' id="current_top"';
                            }

                            echo '<a href="' . $menu_path_tree_index . '"';
                            echo ' onmouseover="mopen(event,\'m1x\',\'?\',\'?\')"';
                            echo ' onmouseout="mclosetime()"' . $menu_top . '><img src="images/outline.gif" class="mobile_hidden" alt="' . __('Tools') . '"><span class="mobile_hidden"> </span>' . __('Tools') . '</a>';

                            ?>
                            <div id="m1x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                <ul class="humo_menu_item2">
                                    <?php
                                    if ($user["group_birthday_list"] == 'j') {
                                        echo '<li' . $menu_item_anniversary . '><a href="' . $menu_path_anniversary . '">' . __('Anniversary list') . '</a></li>';
                                    }
                                    if ($user["group_showstatistics"] == 'j') {
                                        echo '<li' . $menu_item_statistics . '><a href="' . $menu_path_statistics . '">' . __('Statistics') . '</a></li>';
                                    }
                                    if ($user["group_relcalc"] == 'j') {
                                        echo '<li' . $menu_item_calculator . '><a href="' . $menu_path_calculator . '">' . __('Relationship calculator') . "</a></li>\n";
                                    }
                                    if ($user["group_googlemaps"] == 'j') {
                                        //	AND $dbh->query("SELECT * FROM humo_settings WHERE setting_variable ='geo_trees'
                                        //		AND setting_value LIKE '%@".$tree_id.";%' ")->rowCount() > 0) {  // this tree has been indexed
                                        if (!$bot_visit and $dbh->query("SHOW TABLES LIKE 'humo_location'")->rowCount() > 0) {
                                            echo '<li' . $menu_item_map . '><a href="' . $menu_path_map . '">' . __('World map') . "</a></li>\n";
                                        }
                                    }
                                    if ($user["group_contact"] == 'j') {
                                        // *** Show link to contact form ***
                                        if (@$dataDb->tree_owner) {
                                            if ($dataDb->tree_email) {
                                                echo '<li' . $menu_item_contact . '><a href="' . $menu_path_contact . '">' . __('Contact') . "</a></li>\n";
                                            }
                                        }
                                    }
                                    if ($user["group_latestchanges"] == 'j') {
                                        echo '<li' . $menu_item_latest_changes . '><a href="' . $menu_path_latest_changes . '">' . __('Latest changes') . '</a></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </li>
                <?php
                } // *** End of menu check ***
            } // *** End of bot check

            // *** Only show login/ register if user isn't logged in ***
            if ($user['group_menu_login'] == 'j' and !$user["user_name"]) {
                ?>
                <li class="mobile_hidden">
                    <div class="<?= $rtlmarker; ?>sddm">
                        <?php
                        $menu_top = '';
                        if ($page == 'login' || $page == 'register') {
                            $menu_top = ' id="current_top"';
                        }

                        echo '<a href="' . $menu_path_login . '"';
                        echo ' onmouseover="mopen(event,\'m6x\',\'?\',\'?\')"';
                        //echo ' onmouseout="mclosetime()"'.$menu_top.'>'.__('Tools').'&nbsp;<img src="images/button3.png" height= "13" style="border:none;" class="mobile_hidden pull_down_icon" alt="pull_down"></a>';
                        echo ' onmouseout="mclosetime()"' . $menu_top . '><img src="images/man.gif" width="15" alt="' . __('Login') . '"> ' . __('Login') . '</a>';

                        ?>
                        <div id="m6x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                echo '<li' . $menu_item_login . '><a href="' . $menu_path_login . '">' . __('Login') . '</a></li>';

                                // *** Link to registration form ***
                                if (!$user["user_name"] and $humo_option["visitor_registration"] == 'y') {
                                    echo '<li' . $menu_item_register . ' class="mobile_hidden"><a href="' . $menu_path_register . '">' . __('Register') . '</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </li>
            <?php
            }

            // *** Menu: Control menu ***
            if (!$bot_visit) {
            ?>
                <li>
                    <div class="<?= $rtlmarker; ?>sddm">

                        <?php
                        $menu_top = '';
                        if ($page == 'settings') {
                            $menu_top = ' id="current_top"';
                        }

                        echo '<a href="' .  $menu_path_user_settings . '"';
                        echo ' onmouseover="mopen(event,\'m5x\',\'?\',\'?\')"';
                        echo ' onmouseout="mclosetime()"' . $menu_top . '><img src="images/settings.png" width="15" class="mobile_hidden" alt="' . __('Control') . '"><span class="mobile_hidden"> </span>' . __('Control') . '</a>';
                        ?>

                        <div id="m5x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                echo '<li' . $menu_item_user_settings . '><a href="' . $menu_path_user_settings . '">' . __('User settings') . '</a></li>';

                                // *** Link to administration ***
                                if ($user['group_edit_trees'] or $user['group_admin'] == 'j') {
                                    echo '<li' . $menu_item_admin . '><a href="' . $menu_path_admin . '" target="_blank">' . __('Admin') . '</a></li>';
                                }

                                // *** Login - Logoff ***
                                if ($user['group_menu_login'] == 'j' and $user["user_name"]) {
                                    echo '<li' . $menu_item_logoff . '><a href="' . $menu_path_logoff . '">' . __('Logoff');
                                    echo ' <span style="color:#0101DF; font-weight:bold;">[' . ucfirst($_SESSION["user_name"]) . ']</span>';
                                    echo '</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </li>
            <?php
            } // *** End of bot check


            // *** Country flags ***
            if (!$bot_visit) {
                $menu_top = '';
            ?>
                <li>
                    <div class="<?= $rtlmarker; ?>sddm">
                        <a href="index.php?option=com_humo-gen" onmouseover="mopen(event,'m4x','?','?')" onmouseout="mclosetime()" <?= $menu_top; ?>> <img src="<?= 'languages/' . $selected_language; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>" style="border:none; height:18px;"></a>
                        <!-- In gedcom.css special adjustment (width) for m4x! -->
                        <div id="m4x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                $hide_languages_array = explode(";", $humo_option["hide_languages"]);
                                for ($i = 0; $i < count($language_file); $i++) {
                                    // *** Get language name ***
                                    if ($language_file[$i] != $selected_language and !in_array($language_file[$i], $hide_languages_array)) {
                                        include(__DIR__ . '/../languages/' . $language_file[$i] . '/language_data.php');
                                        $language_path = $link_cls->get_link($uri_path, 'language', '', true);
                                ?>
                                        <li>
                                            <a href="<?= $language_path . 'language=' . $language_file[$i]; ?>">
                                                <img src="<?= 'languages/' . $language_file[$i]; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>" style="border:none;">
                                                <!-- Hide names of languages in mobile version -->
                                                <span class="mobile_hidden"><?= $language["name"]; ?></span>
                                            </a>
                                        </li>
                                <?php
                                    }
                                }

                                // *** Odd number of languages in menu ***
                                /*
                                if ($i % 2 == 0){
                                    echo '<li style="float:left; width:124px;">';
                                        echo '<a href="index.php" style="height:18px;">&nbsp;<br></a>';
                                    echo '</li>';
                                }
                                */

                                ?>
                            </ul>
                        </div>
                    </div>
                </li>
            <?php
                include(__DIR__ . '/../languages/' . $selected_language . '/language_data.php');
            }
            ?>
        </ul>
    </div> <!-- End of humo_menu -->

</div> <!-- End of top_menu -->





<!-- Test bootstrap menu using hoover effect -->
<!-- Example from: https://bootstrap-menu.com/detail-basic-hover.html -->
<!--
<style>
    @media all and (min-width: 992px) {
        .navbar .nav-item .dropdown-menu {
            display: none;
        }

        .navbar .nav-item:hover .nav-link {}

        .navbar .nav-item:hover .dropdown-menu {
            display: block;
        }

        .navbar .nav-item .dropdown-menu {
            margin-top: 0;
        }
    }
</style>
<nav class="mt-5 navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
-->
        <!-- <a class="navbar-brand" href="#">Brand</a> -->
<!--
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main_nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="main_nav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php if ($page == 'index') echo 'active'; ?>" href="<?= $menu_path_home; ?>"><?= __('Home'); ?></a>
                </li>

                <?php
                // TODO improve code
                // *** Menu genealogy (for CMS pages) ***
                if ($user['group_menu_cms'] == 'y') {
                    $cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
                    if ($cms_qry->rowCount() > 0) {
                ?>
                        <li class="nav-item">
                            <a class="nav-link <?php if ($page == 'cms_pages') echo 'active'; ?>" href="<?= $menu_path_cms; ?>"><?= __('Information'); ?></a>
                        </li>
                <?php
                    }
                }
                ?>


                <li class="nav-item dropdown">
            -->
                    <!-- TODO add active if dropdown item is selected -->
<!--
                    <a class="nav-link dropdown-toggle" href="<?= $menu_path_tree_index; ?>" data-bs-toggle="dropdown"><?= __('Family tree'); ?></a>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item <?php if ($page == 'tree_index') echo 'active'; ?>" href="<?= $menu_path_tree_index; ?>"><?= __('Family tree index'); ?></a></li>

                        <li><a class="dropdown-item <?php if ($page == 'persons' || $page == 'family' || $page == 'family_rtf' || $page == 'descendant' || $page == 'ancestor' || $page == 'ancestor_chart' || $page == 'ancestor_sheet' || $page == 'list') echo 'active'; ?>" href="<?= $menu_path_persons ?>"><?= __('Persons'); ?></a></li>

                        <?php
                        /*
                        // *** Persons ***
                        if ($user['group_menu_persons'] == "j") {
                            echo '<li' . $menu_item_persons . '><a href="' . $menu_path_persons . '">' . __('Persons') . '</a></li>';
                        }
                        // *** Names ***
                        if ($user['group_menu_names'] == "j") {
                            echo '<li' . $menu_item_names . '><a href="' . $menu_path_names . '">' . __('Names') . "</a></li>\n";
                        }

                        // *** Places ***
                        if ($user['group_menu_places'] == "j") {
                            echo '<li' . $menu_item_places_persons . '><a href="' . $menu_path_places_persons . '">' . __('Places (by persons)') . "</a></li>\n";
                            echo '<li' . $menu_item_list_places_families . '><a href="' . $menu_path_list_places_families . '">' . __('Places (by families)') . "</a></li>\n";
                        }

                        if ($user['group_photobook'] == 'j') {
                            echo '<li' . $menu_item_photoalbum . '><a href="' . $menu_path_photoalbum . '">' . __('Photobook') . "</a></li>\n";
                        }

                        //if ($user['group_sources']=='j'){
                        if ($user['group_sources'] == 'j' and $tree_prefix_quoted != '' and $tree_prefix_quoted != 'EMPTY') {
                            // *** Check if there are sources in the database ***
                            //$source_qry=$dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='".$tree_id."'AND source_shared='1'");
                            $source_qry = $dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'");
                            @$sourceDb = $source_qry->rowCount();
                            if ($sourceDb > 0) {
                                echo '<li' . $menu_item_sources . '><a href="' . $menu_path_sources . '">' . __('Sources') . "</a></li>\n";
                            }
                        }

                        if ($user['group_addresses'] == 'j' and $tree_prefix_quoted != '' and $tree_prefix_quoted != 'EMPTY') {
                            // *** Check for addresses in the database ***
                            $address_qry = $dbh->query("SELECT * FROM humo_addresses
                                        WHERE address_tree_id='" . $tree_id . "' AND address_shared='1'");
                            @$addressDb = $address_qry->rowCount();
                            if ($addressDb > 0) {
                                echo '<li' . $menu_item_addresses . '><a href="' . $menu_path_addresses . '">' . __('Addresses') . "</a></li>\n";
                            }
                        }
                        */
                        ?>


                    </ul>
                </li>


                <li class="nav-item"><a class="nav-link" href="#"> About </a></li>
                <li class="nav-item"><a class="nav-link" href="#"> Services </a></li>
            </ul>
            <form class="d-flex" role="search">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>
-->






<?php
// *** Override margin if slideshow is used ***
if ($page == 'index' and isset($humo_option["slideshow_show"]) and $humo_option["slideshow_show"] == 'y') {
    echo '<style>
    #rtlcontent {
        padding-left:0px;
        padding-right:0px;
    }
    #content {
        padding-left:0px;
        padding-right:0px;
    }
    </style>';
}

if ($language["dir"] == "rtl") {
    echo '<div id="rtlcontent">';
} else {
    echo '<div id="content">';
}
