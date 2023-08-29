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

<div id="top_menu">
    <div id="top" style="direction:<?= $rtlmark; ?>">
        <div style="direction:ltr;">
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
                if ($num_rows > 1) {
                    //if ($humo_option["url_rewrite"] == "j") {
                    //    $link = $uri_path . 'tree_index/';
                    //} else {
                    //    //$link = 'tree_index.php';
                    //    $link = 'index.php?page=tree_index';
                    //}
                    $link = $link_cls->get_link($uri_path, 'tree_index');
            ?>

                    <form method="POST" action="<?= $link; ?>" style="display : inline;" id="top_tree_select">
                        <?= __('Family tree') . ': '; ?>
                        <select size="1" name="tree_id" onChange="this.form.submit();" style="width: 150px; height:20px;">
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
            <?php
                }
            }
            ?>
        </div>

        <?php
        // *** This code is used to restore $dataDb reading. Used for picture etc. ***
        if (is_string($_SESSION['tree_prefix']))
            $dataDb = $db_functions->get_tree($_SESSION['tree_prefix']);

        // *** Show quicksearch field ***
        if (!$bot_visit) {
            $menu_path = CMS_ROOTPATH . 'list.php';

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
            <form method="post" action="<?= $menu_path; ?>" id="top_quicksearch">
                <input type="hidden" name="index_list" value="quicksearch">
                <input type="hidden" name="search_database" value="tree_selected">
                <?php
                echo '<input type="text" name="quicksearch" placeholder="' . __('Name') . '" value="' . $quicksearch . '" size="10" ' . $pattern . ' title="' . __('Minimum:') . $min_chars . __('characters') . '">';
                echo ' <input type="submit" value="' . __('Search') . '">';

                // *** Link for extended search form ***
                $menu_path = CMS_ROOTPATH . 'list.php?adv_search=1&index_list=search';
                echo ' <a href="' . $menu_path . '"><img src="images/advanced-search.jpg" width="17" alt="' . __('Advanced search') . '"></a>';
                ?>
            </form>
        <?php
        }

        // *** Favourite list for family pages ***
        if (!$bot_visit) {
            include_once(CMS_ROOTPATH . "include/person_cls.php");
            // *** Show favorites in selection list ***
        ?>
            <form method="POST" action="<?= $uri_path; ?>family.php" style="display : inline;" id="top_favorites_select">
                <img src="images/favorite_blue.png" alt="<?= __('Favourites'); ?>">
                <select size=1 name="humo_favorite_id" onChange="this.form.submit();" style="width:115px; height:20px;">
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

    //if ($humo_option["url_rewrite"] == "j") {
    //    //$menu_path_home = 'index/' . $tree_id . "/";
    //    $menu_path_home = 'index/' . $tree_id;
    //} else {
    //    $menu_path_home = CMS_ROOTPATH . 'index.php?tree_id=' . $tree_id;
    //}
    $menu_path_home = $link_cls->get_link($uri_path, 'index', $tree_id);


    // *** Mobile menu ***
    $menu_top_home = '';
    if ($menu_choice == 'help') {
        $menu_top_home = ' id="current_top"';
    }
    if ($menu_choice == 'cookies') {
        $menu_top_home = ' id="current_top"';
    }

    if ($user['group_menu_login'] == 'j') {
        $menu_item_login = '';
        if ($menu_choice == 'login') {
            $menu_item_login = ' id="current"';
        }

        //if ($humo_option["url_rewrite"] == "j") {
        //    $menu_path_login = $uri_path . 'login';
        //} else {
        //    $menu_path_login = CMS_ROOTPATH . 'login.php';
        //}
        $menu_path_login = $link_cls->get_link($uri_path, 'login');
    }

    //if ($user['group_edit_trees'] or $user['group_admin'] == 'j') {
    //    $menu_item_admin = '';
    //    $menu_path_admin = CMS_ROOTPATH_ADMIN . 'index.php';
    //}

    $menu_item_logoff = ''; //if ($menu_choice=='help'){ $menu_item=' id="current"'; }
    // *** Log off ***
    //if ($humo_option["url_rewrite"] == "j") {
    //    $menu_path_logoff = $uri_path . 'index?log_off=1';
    //} else {
    //    $menu_path_logoff = CMS_ROOTPATH . 'index.php?log_off=1';
    //}
    $menu_path_logoff = $link_cls->get_link($uri_path, 'logoff');

    //if ($humo_option["url_rewrite"] == "j") {
    //    $menu_path_help = $uri_path . 'help';
    //} else {
    //    //$menu_path_help = CMS_ROOTPATH . 'help.php';
    //    $menu_path_help = CMS_ROOTPATH . 'index.php?page=help';
    //}
    $menu_path_help = $link_cls->get_link($uri_path, 'help');
    $menu_item_help = '';
    if ($menu_choice == 'help') {
        $menu_item_help = ' id="current"';
    }

    $menu_item_register = '';
    if ($menu_choice == 'register') {
        $menu_item_register = ' id="current"';
    }
    //if ($humo_option["url_rewrite"] == "j") {
    //    $menu_path_register = $uri_path . 'register';
    //} else {
    //    $menu_path_register = CMS_ROOTPATH . 'index.php?page=register';
    //}
    $menu_path_register = $link_cls->get_link($uri_path, 'register');

    $menu_item_cms = '';
    if ($menu_choice == 'cms_pages') {
        $menu_item_cms = ' id="current"';
    }
    //if ($humo_option["url_rewrite"] == "j") {
    //    $menu_path_cms = $uri_path . 'cms_pages';
    //} else {
    //    $menu_path_cms = CMS_ROOTPATH . 'index.php?page=cms_pages';
    //}
    $menu_path_cms = $link_cls->get_link($uri_path, 'cms_pages');

    $menu_item_cookies = '';
    if ($menu_choice == 'cookies') {
        $menu_item_cookies = ' id="current"';
    }
    //if ($humo_option["url_rewrite"] == "j") {
    //    $menu_path_cookies = $uri_path . 'cookies';
    //} else {
    //    //$menu_path_cookies = CMS_ROOTPATH . 'cookies.php';
    //    $menu_path_cookies = CMS_ROOTPATH . 'index.php?page=cookies';
    //}
    $menu_path_cookies = $link_cls->get_link($uri_path, 'cookies');

    $menu_item_persons = '';
    if ($menu_choice == 'persons') {
        $menu_item_persons = ' id="current"';
    }
    $menu_path_persons = CMS_ROOTPATH . 'list.php?tree_id=' . $tree_id . '&amp;reset=1';
    //$menu_path_persons = $link_cls->get_link($uri_path, 'persons',$tree_id,true);
    //$menu_path_persons.='reset=1';

    $menu_item_names = '';
    if ($menu_choice == 'names') {
        $menu_item_names = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_names = 'list_names/' . $tree_id . '/';
    } else {
        $menu_path_names = CMS_ROOTPATH . 'index.php?page=list_names&amp;tree_id=' . $tree_id;
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    $menu_item_user_settings = '';
    if ($menu_choice == 'user_settings') {
        $menu_item_user_settings = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_user_settings = 'user_settings';
    } else {
        //$menu_path_user_settings = CMS_ROOTPATH . 'user_settings.php';
        $menu_path_user_settings = CMS_ROOTPATH . 'index.php?page=user_settings';
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    $menu_item_admin = '';
    $menu_path_admin = CMS_ROOTPATH_ADMIN . 'index.php';

    $menu_item_anniversary = '';
    if ($menu_choice == 'birthday') {
        $menu_item_anniversary = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_anniversary = 'birthday_list';
    } else {
        $menu_path_anniversary = CMS_ROOTPATH . 'index.php?page=birthday_list';
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    $menu_item_statistics = '';
    if ($menu_choice == 'statistics') {
        $menu_item_statistics = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_statistics = 'statistics';
    } else {
        $menu_path_statistics = CMS_ROOTPATH . 'index.php?page=statistics';
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    $menu_item_calculator = '';
    if ($menu_choice == 'relations') {
        $menu_item_calculator = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_calculator = 'relations';
    } else {
        $menu_path_calculator = CMS_ROOTPATH . 'relations.php';
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    $menu_item_map = '';
    if ($menu_choice == 'maps') {
        $menu_item_map = ' id="current"';
    }
    $menu_path_map = CMS_ROOTPATH . 'maps.php';

    $menu_item_contact = '';
    if ($menu_choice == 'mailform') {
        $menu_item_contact = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_contact = 'mailform';
    } else {
        $menu_path_contact = CMS_ROOTPATH . 'index.php?page=mailform';
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    // *** Latest changes ***
    $menu_item_latest_changes = '';
    if ($menu_choice == 'latest_changes') {
        $menu_item_latest_changes = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        //$menu_path_latest_changes = 'latest_changes';
        $menu_path_latest_changes = 'latest_changes/' . $tree_id;
    } else {
        //$menu_path_latest_changes = CMS_ROOTPATH . 'latest_changes.php';
        $menu_path_latest_changes = CMS_ROOTPATH . 'index.php?page=latest_changes&amp;tree_id=' . $tree_id;
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    $menu_item_tree_index = '';
    if ($menu_choice == 'tree_index') {
        $menu_item_tree_index = ' id="current"';
    }
    //if ($humo_option["url_rewrite"] == "j") {
    //    //$menu_path_tree_index = 'tree_index/' . $tree_id . "/";
    //    $menu_path_tree_index = 'tree_index/' . $tree_id;
    //} else {
    //    //$menu_path_tree_index = CMS_ROOTPATH . 'tree_index.php?tree_id=' . $tree_id . '&amp;reset=1';
    //    $menu_path_tree_index = CMS_ROOTPATH . 'index.php?page=tree_index&amp;tree_id=' . $tree_id . '&amp;reset=1';
    //}
    $menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index', $tree_id);

    $menu_item_places_persons = '';
    if ($menu_choice == 'places') {
        $menu_item_places_persons = ' id="current"';
    }
    $menu_path_places_persons = CMS_ROOTPATH . 'list.php?tree_id=' . $tree_id . '&amp;index_list=places&amp;reset=1';

    $menu_item_places_families = '';
    if ($menu_choice == 'places_families') {
        $menu_item_places_families = ' id="current"';
    }
    $menu_path_places_families = CMS_ROOTPATH . 'list_places_families.php?tree_id=' . $tree_id . '&amp;index_list=places&amp;reset=1';

    $menu_item_photoalbum = '';
    if ($menu_choice == 'pictures') {
        $menu_item_photoalbum = ' id="current"';
    }
    //if ($humo_option["url_rewrite"] == "j") {
    //    $menu_path_photoalbum = 'photoalbum/' . $tree_id;
    //} else {
    //    $menu_path_photoalbum = CMS_ROOTPATH . 'photoalbum.php?tree_id=' . $tree_id;
    //}
    $menu_path_photoalbum = $link_cls->get_link($uri_path, 'photoalbum',$tree_id);

    $menu_item_sources = '';
    if ($menu_choice == 'sources') {
        $menu_item_sources = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_sources = 'sources/' . $tree_id;
    } else {
        //$menu_path_sources = CMS_ROOTPATH . 'sources.php?tree_id=' . $tree_id;
        $menu_path_sources = CMS_ROOTPATH . 'index.php?page=sources&amp;tree_id=' . $tree_id;
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    $menu_item_addresses = '';
    if ($menu_choice == 'addresses') {
        $menu_item_addresses = ' id="current"';
    }
    if ($humo_option["url_rewrite"] == "j") {
        $menu_path_addresses = 'addresses/' . $tree_id;
    } else {
        //$menu_path_addresses = CMS_ROOTPATH . 'addresses.php?tree_id=' . $tree_id;
        $menu_path_addresses = CMS_ROOTPATH . 'index.php?page=addresses&amp;tree_id=' . $tree_id;
    }
    //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');

    ?>
    <div id="humo_menu" <?= $ie7_rtlhack; ?>>
        <ul class="humo_menu_item">
            <!-- You can use this link, for an extra link to another main homepage -->
            <!-- <li><a href="...">Homepage</a></li> -->
            <li <?php if ($menu_choice == 'main_index') echo 'id="current"'; ?> class="mobile_hidden"><a href="<?= $menu_path_home; ?>"><img src="images/menu_mobile.png" width="18" class="mobile_icon" alt="<?= __('Home'); ?>"> <?= __('Home'); ?></a></li>
            <?php

            // Doesn't work properly. Icon too large and orange...
            //echo '<li'.$menu_item.' class="mobile_hidden"><a href="'.$menu_path.'">';
            //	echo '<svg width="35" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff9900" stroke-width="2" stroke-linecap="butt" stroke-linejoin="miter">';
            //	echo '<line x1="0" y1="4" x2="18" y2="4"></line><line x1="0" y1="10" x2="18" y2="10"></line>';
            //	echo '<line x1="0" y1="16" x2="18" y2="16"></line>';
            //	echo '</svg>';
            //echo __('Home')."</a></li>\n";

            ?>
            <li class="mobile_visible">
                <div class="<?= $rtlmarker; ?>sddm">
                    <?php
                    echo '<a href="' . $menu_path_home . '"';
                    echo ' onmouseover="mopen(event,\'m0x\',\'?\',\'?\')"';
                    echo ' onmouseout="mclosetime()"' . $menu_top_home . '><img src="images/menu_mobile.png" width="18" alt="' . __('Home') . '"></a>';
                    ?>
                    <div id="m0x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                        <ul class="humo_menu_item2">
                            <li <?php if ($menu_choice == 'main_index') echo ' id="current"'; ?>><a href="<?= $menu_path_home; ?>"><?= __('Home'); ?></a></li>
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

                            // *** Help items ***
                            //echo '<li' . $menu_item_help . '><a href="' . $menu_path_help . '">' . __('Help') . '</a></li>';

                            //if (!$bot_visit) {
                            //    echo '<li' . $menu_item_cookies . '><a href="' . $menu_path_cookies . '">';
                            //    printf(__('%s cookies'), 'HuMo-genealogy');
                            //    echo '</a></li>';
                            //}
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
                if ($menu_choice == 'tree_index') {
                    $menu_top = ' id="current_top"';
                }
                //if ($menu_choice=='cms_pages'){ $menu_top=' id="current_top"'; }
                if ($menu_choice == 'persons') {
                    $menu_top = ' id="current_top"';
                }
                if ($menu_choice == 'names') {
                    $menu_top = ' id="current_top"';
                }
                if ($menu_choice == 'sources') {
                    $menu_top = ' id="current_top"';
                }
                if ($menu_choice == 'places') {
                    $menu_top = ' id="current_top"';
                }
                if ($menu_choice == 'places_families') {
                    $menu_top = ' id="current_top"';
                }
                if ($menu_choice == 'pictures') {
                    $menu_top = ' id="current_top"';
                }
                if ($menu_choice == 'addresses') {
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
                                    echo '<li' . $menu_item_places_families . '><a href="' . $menu_path_places_families . '">' . __('Places (by families)') . "</a></li>\n";
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
                            if ($menu_choice == 'birthday') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($menu_choice == 'statistics') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($menu_choice == 'relations') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($menu_choice == 'maps') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($menu_choice == 'mailform') {
                                $menu_top = ' id="current_top"';
                            }
                            if ($menu_choice == 'latest_changes') {
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

            /*
            $menu_top = '';
            if ($menu_choice == 'help') {
                $menu_top = ' id="current_top"';
            }
            if ($menu_choice == 'cookies') {
                $menu_top = ' id="current_top"';
            }
            */
            /*
            ?>
            <li class="mobile_hidden">
                <div class="<?= $rtlmarker; ?>sddm">
                    <?php
                    echo '<a href="' . $menu_path_help . '"';
                    echo ' onmouseover="mopen(event,\'m2x\',\'?\',\'?\')"';
                    //echo ' onmouseout="mclosetime()"'.$menu_top.'>'.__('Help').'&nbsp;<img src="'.CMS_ROOTPATH.'images/button3.png" height= "13" style="border:none;" class="pull_down_icon" alt="pull_down"></a>';
                    echo ' onmouseout="mclosetime()"' . $menu_top . '><img src="images/help.png" width="15" alt="' . __('Help') . '"> ' . __('Help') . '</a>';
                    ?>
                    <div id="m2x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                        <ul class="humo_menu_item2">
                            <?php
                            echo '<li' . $menu_item_help . '><a href="' . $menu_path_help . '">' . __('Help') . '</a></li>';

                            if (!$bot_visit) {
                                echo '<li' . $menu_item_cookies . '><a href="' . $menu_path_cookies . '">';
                                printf(__('%s cookies'), 'HuMo-genealogy');
                                echo '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
            <?php
            */

            // *** Only show login/ register if user isn't logged in ***
            if ($user['group_menu_login'] == 'j' and !$user["user_name"]) {
                ?>
                <li class="mobile_hidden">
                    <div class="<?= $rtlmarker; ?>sddm">
                        <?php
                        $menu_top = '';
                        if ($menu_choice == 'login') {
                            $menu_top = ' id="current_top"';
                        }
                        if ($menu_choice == 'register') {
                            $menu_top = ' id="current_top"';
                        }

                        echo '<a href="' . $menu_path_login . '"';
                        echo ' onmouseover="mopen(event,\'m6x\',\'?\',\'?\')"';
                        //echo ' onmouseout="mclosetime()"'.$menu_top.'>'.__('Tools').'&nbsp;<img src="'.CMS_ROOTPATH.'images/button3.png" height= "13" style="border:none;" class="mobile_hidden pull_down_icon" alt="pull_down"></a>';
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
                        if ($menu_choice == 'settings') {
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
                        <a href="index.php?option=com_humo-gen" onmouseover="mopen(event,'m4x','?','?')" onmouseout="mclosetime()" <?= $menu_top; ?>> <img src="<?= CMS_ROOTPATH . 'languages/' . $selected_language; ?>/flag.gif" title="<?= $language["name"]; ?>" alt="<?= $language["name"]; ?>" style="border:none; height:18px;"></a>
                        <!-- In gedcom.css special adjustment (width) for m4x! -->
                        <div id="m4x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                $hide_languages_array = explode(";", $humo_option["hide_languages"]);
                                for ($i = 0; $i < count($language_file); $i++) {
                                    // *** Get language name ***
                                    if ($language_file[$i] != $selected_language and !in_array($language_file[$i], $hide_languages_array)) {
                                        include(CMS_ROOTPATH . 'languages/' . $language_file[$i] . '/language_data.php');
                                        echo '<li>';
                                        if ($humo_option["url_rewrite"] == "j") {
                                            echo '<a href="' . $uri_path . 'index?language=' . $language_file[$i] . '">';
                                        } else {
                                            echo '<a href="' . CMS_ROOTPATH . 'index.php?language=' . $language_file[$i] . '">';
                                        }
                                        //$menu_path_tree_index = $link_cls->get_link($uri_path, 'tree_index');
                                        echo '<img src="' . CMS_ROOTPATH . 'languages/' . $language_file[$i] . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none;"> ';
                                        // *** Hide names of languages in mobile version ***
                                        echo '<span class="mobile_hidden">' . $language["name"] . '</span>';
                                        echo '</a>';
                                        echo '</li>';
                                    }
                                }

                                // *** Odd number of languages in menu ***
                                /*
                                if ($i % 2 == 0){
                                    echo '<li style="float:left; width:124px;">';
                                        echo '<a href="'.CMS_ROOTPATH.'index.php" style="height:18px;">&nbsp;<br></a>';
                                    echo '</li>';
                                }
                                */

                                ?>
                            </ul>
                        </div>
                    </div>
                </li>
            <?php
                include('languages/' . $selected_language . '/language_data.php');
            }
            ?>
        </ul>
    </div> <!-- End of humo_menu -->

</div> <!-- End of top_menu -->
<?php

// *** Override margin if slideshow is used ***
if ($menu_choice == 'main_index' and isset($humo_option["slideshow_show"]) and $humo_option["slideshow_show"] == 'y') {
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
