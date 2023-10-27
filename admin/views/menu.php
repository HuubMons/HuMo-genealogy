<?php

/**
 * Admin menu
 */

$popup_style = '';
//if ($popup == true) $popup_style = ' style="top:0px;"';

if ($page != 'login' and $page != 'update') {
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    }
}

$menu_top_admin = '';
$menu_item_admin = '';
if ($page == 'admin') {
    $menu_top_admin = ' id="current_top"';
    $menu_item_admin = ' id="current"';
}

$menu_path_website = '../index.php';

$menu_path_logoff = 'index.php?log_off=1';

$menu_item_logoff = '';
if ($page == 'check') {
    $menu_item_logoff = ' id="current"';
}

$menu_top_control = '';
$menu_item_install = '';
$menu_item_extensions = '';
$menu_item_settings = '';
$menu_item_settings_homepage = ''; // Page "setting" is highlighted in menu.
$menu_item_settings_special = ''; // Page "setting" is highlighted in menu.
$menu_item_cms_pages = '';
$menu_item_language_editor = '';
$menu_item_prefix_editor = '';
$menu_item_maps = '';
if ($page == 'install') {
    $menu_top_control = ' id="current_top"';
    $menu_item_install = ' id="current"';
}
if ($page == 'extensions') {
    $menu_top_control = ' id="current_top"';
    $menu_item_extensions = ' id="current"';
}
if ($page == 'settings') {
    $menu_top_control = ' id="current_top"';
    $menu_item_settings = ' id="current"';
}
if ($page == 'cms_pages') {
    $menu_top_control = ' id="current_top"';
    $menu_item_cms_pages = ' id="current"';
}
if ($page == 'favorites') {
    $menu_top_control = ' id="current_top"';
}
if ($page == 'language_editor') {
    $menu_top_control = ' id="current_top"';
    $menu_item_language_editor = ' id="current"';
}
if ($page == 'prefix_editor') {
    $menu_top_control = ' id="current_top"';
    $menu_item_prefix_editor = ' id="current"';
}
if ($page == 'google_maps') {
    $menu_top_control = ' id="current_top"';
    $menu_item_maps = ' id="current"';
}

$menu_top_trees = '';
$menu_item_tree = '';
$menu_item_thumbs = '';
$menu_item_user_notes = '';
$menu_item_check = '';
$menu_item_latest_changes = '';  // Page "check" is highlighted in menu.
$menu_item_cal_date = '';
$menu_item_export = '';
$menu_item_backup = '';
$menu_item_statistics = '';
if ($page == 'tree') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_tree = ' id="current"';
}
if ($page == 'thumbs') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_thumbs = ' id="current"';
}
if ($page == 'user_notes') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_user_notes = ' id="current"';
}
if ($page == 'check') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_check = ' id="current"';
}
if ($page == 'cal_date') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_cal_date = ' id="current"';
}
if ($page == 'export') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_export = ' id="current"';
}
if ($page == 'backup') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_backup = ' id="current"';
}
if ($page == 'statistics') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_statistics = ' id="current"';
}

$menu_top_editor = '';
$menu_item_editor = '';
$menu_item_edit_sources = '';
$menu_item_edit_repositories = '';
$menu_item_edit_addresses = '';
$menu_item_edit_places = '';
if ($page == 'editor') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_editor = ' id="current"';
}
if ($page == 'edit_sources') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_edit_sources = ' id="current"';
}
if ($page == 'edit_repositories') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_edit_repositories = ' id="current"';
}
if ($page == 'edit_addresses') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_edit_addresses = ' id="current"';
}
if ($page == 'edit_places') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_edit_places = ' id="current"';
}

$menu_top_users = '';
$menu_item_users = '';
$menu_item_groups = '';
$menu_item_log = '';
if ($page == 'users') {
    $menu_top_users = ' id="current_top"';
    $menu_item_users = ' id="current"';
}
if ($page == 'groups') {
    $menu_top_users = ' id="current_top"';
    $menu_item_groups = ' id="current"';
}
if ($page == 'log') {
    $menu_top_users = ' id="current_top"';
    $menu_item_log = ' id="current"';
}

$menu_top_flags = '';

if ($popup == false) {
?>
    <div id="humo_menu" <?= $popup_style; ?>>
        <ul class="humo_menu_item">
            <li>
                <div class="<?= $rtlmarker; ?>sddm">
                    <a href="<?= $path_tmp; ?>page=admin" onmouseover="mopen(event,'m1x','?','?')" onmouseout="mclosetime()" <?= $menu_top_admin; ?>><img src="../images/menu_mobile.png" width="18" alt="<?= __('Administration'); ?>"></a>
                    <div id="m1x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                        <ul class="humo_menu_item2">
                            <?php
                            if ($group_administrator == 'j') {
                            ?>
                                <li <?= $menu_item_admin; ?>><a href="<?= $path_tmp; ?>page=admin"><?= __('Administration'); ?> - <?= __('Main menu'); ?></a></li>
                                <li><a href="<?= $menu_path_website; ?>"><?= __('Website'); ?></a></li>
                            <?php
                            }

                            if (isset($_SESSION["user_name_admin"])) {
                                echo '<li' . $menu_item_logoff . '><a href="' . $menu_path_logoff . '">' . __('Logoff') . '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
            <?php

            if ($show_menu_left == true and $page != 'login') {
                if ($group_administrator == 'j') {
            ?>
                    <li>
                        <div class="<?= $rtlmarker; ?>sddm">
                            <a href="<?= $path_tmp; ?>page=admin" onmouseover="mopen(event,'m2x','?','?')" onmouseout="mclosetime()" <?= $menu_top_control; ?>><img src="../images/settings.png" class="mobile_hidden" alt="<?= __('Control'); ?>"><span class="mobile_hidden"> </span><?= __('Control'); ?></a>
                            <div id="m2x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                <ul class="humo_menu_item2">
                                    <li <?= $menu_item_install; ?>><a href="<?= $path_tmp; ?>page=install"><?= __('Install'); ?></a></li>
                                    <?php
                                    echo '<li' . $menu_item_extensions . '><a href="' . $path_tmp . 'page=extensions">' . __('Extensions') . '</a></li>';
                                    echo '<li' . $menu_item_settings . '><a href="' . $path_tmp . 'page=settings">' . __('Settings') . '</a></li>';
                                    echo '<li' . $menu_item_settings_homepage . '><a href="' . $path_tmp . 'page=settings&amp;menu_admin=settings_homepage">' . __('Homepage') . '</a></li>';
                                    echo '<li' . $menu_item_settings_special . '><a href="' . $path_tmp . 'page=settings&amp;menu_admin=settings_special">' . __('Special settings') . '</a></li>';
                                    echo '<li' . $menu_item_cms_pages . '><a href="' . $path_tmp . 'page=cms_pages">' . __('CMS Own pages') . '</a></li>';
                                    echo '<li' . $menu_item_language_editor . '><a href="' . $path_tmp . 'page=language_editor">' . __('Language editor') . '</a></li>';
                                    echo '<li' . $menu_item_prefix_editor . '><a href="' . $path_tmp . 'page=prefix_editor">' . __('Prefix editor') . '</a></li>';
                                    echo '<li' . $menu_item_maps . '><a href="' . $path_tmp . 'page=google_maps">' . __('World map') . '</a></li>';
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </li>
                <?php
                }

                ?>
                <li>
                    <div class="<?= $rtlmarker; ?>sddm">
                        <?php
                        echo '<a href="' . $path_tmp . 'page=tree"';
                        echo ' onmouseover="mopen(event,\'m3x\',\'?\',\'?\')"';
                        echo ' onmouseout="mclosetime()"' . $menu_top_trees . '><img src="images/family_connect.gif" class="mobile_hidden" alt="' . __('Family trees') . '"><span class="mobile_hidden"> </span>' . __('Family trees') . '</a>';
                        ?>
                        <div id="m3x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                if ($group_administrator == 'j') {
                                    echo '<li' . $menu_item_tree . '><a href="' . $path_tmp . 'page=tree">' . __('Family trees') . '</a><li>';
                                    //echo '<li'.$menu_item_thumbs.'><a href="'.$path_tmp.'page=thumbs">'.__('Create thumbnails').'</a>';
                                    echo '<li' . $menu_item_thumbs . '><a href="' . $path_tmp . 'page=thumbs">' . __('Pictures/ create thumbnails') . '</a></li>';
                                    echo '<li' . $menu_item_user_notes . '><a href="' . $path_tmp . 'page=user_notes">' . __('Notes') . '</a></li>';
                                    echo '<li' . $menu_item_check . '><a href="' . $path_tmp . 'page=check">' . __('Family tree data check') . '</a></li>';
                                    echo '<li' . $menu_item_latest_changes . '><a href="' . $path_tmp . 'page=view_latest_changes">' . __('View latest changes') . '</a></li>';
                                    echo '<li' . $menu_item_cal_date . '><a href="' . $path_tmp . 'page=cal_date">' . __('Calculated birth date') . '</a></li>';
                                    echo '<li' . $menu_item_export . '><a href="' . $path_tmp . 'page=export">' . __('Gedcom export') . '</a></li>';
                                    echo '<li' . $menu_item_backup . '><a href="' . $path_tmp . 'page=backup">' . __('Database backup') . '</a></li>';
                                    echo '<li' . $menu_item_statistics . '><a href="' . $path_tmp . 'page=statistics">' . __('Statistics') . '</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </li>

                <li>
                    <div class="<?= $rtlmarker; ?>sddm">
                        <?php
                        echo '<a href="' . $path_tmp . 'page=editor"';
                        echo ' onmouseover="mopen(event,\'m3xa\',\'?\',\'?\')"';
                        echo ' onmouseout="mclosetime()"' . $menu_top_editor . '><img src="images/edit.jpg" class="mobile_hidden" alt="' . __('Editor') . '"><span class="mobile_hidden"> </span>' . __('Editor') . '</a>';
                        ?>
                        <div id="m3xa" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                echo '<li' . $menu_item_editor . '><a href="' . $path_tmp . 'page=editor">' . __('Persons and families') . '</a></li>';
                                echo '<li' . $menu_item_edit_sources . '><a href="' . $path_tmp . 'page=edit_sources">' . __('Sources') . "</a></li>";
                                echo '<li' . $menu_item_edit_repositories . '><a href="' . $path_tmp . 'page=edit_repositories">' . __('Repositories') . "</a></li>";
                                echo '<li' . $menu_item_edit_addresses . '><a href="' . $path_tmp . 'page=edit_addresses">' . __('Shared addresses') . "</a></li>";
                                echo '<li' . $menu_item_edit_places . '><a href="' . $path_tmp . 'page=edit_places">' . __('Rename places') . "</a></li>";
                                ?>
                            </ul>
                        </div>
                    </div>
                </li>
                <?php

                if ($group_administrator == 'j') {
                ?>
                    <li>
                        <div class="<?= $rtlmarker; ?>sddm">
                            <?php
                            echo '<a href="' . $path_tmp . 'page=users"';
                            echo ' onmouseover="mopen(event,\'m4x\',\'?\',\'?\')"';
                            echo ' onmouseout="mclosetime()"' . $menu_top_users . '><img src="images/person_edit.gif" class="mobile_hidden" alt="' . __('Users') . '"><span class="mobile_hidden"> </span>' . __('Users') . '</a>';
                            ?>
                            <div id="m4x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                <ul class="humo_menu_item2">
                                    <?php
                                    echo '<li' . $menu_item_users . '><a href="' . $path_tmp . 'page=users">' . __('Users') . '</a></li>';
                                    echo '<li' . $menu_item_groups . '><a href="' . $path_tmp . 'page=groups">' . __('Groups') . '</a></li>';
                                    echo '<li' . $menu_item_log . '><a href="' . $path_tmp . 'page=log">' . __('Log') . '</a></li>'; ?>
                                </ul>
                            </div>
                        </div>
                    </li>
            <?php
                }
            }

            // *** Check is needed for PHP 7.4 ***
            if (isset($humo_option["hide_languages"]))
                $hide_languages_array = explode(";", $humo_option["hide_languages"]);
            else
                $hide_languages_array[] = '';

            ?>
            <li>
                <div class="<?= $rtlmarker; ?>sddm">
                    <?php
                    include(__DIR__ . '/../../languages/' . $selected_language . '/language_data.php');
                    echo '<a href="index.php?option=com_humo-gen"';
                    echo ' onmouseover="mopen(event,\'m40x\',\'?\',\'?\')"';
                    echo ' onmouseout="mclosetime()"' . $menu_top_flags . '>' . '<img src="../languages/' . $selected_language . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none; height:18px"> </a>';
                    ?>
                    <div id="m40x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                        <ul class="humo_menu_item2">
                            <?php
                            for ($i = 0; $i < count($language_file); $i++) {
                                // *** Get language name ***
                                if ($language_file[$i] != $selected_language and !in_array($language_file[$i], $hide_languages_array)) {
                                    include(__DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php');
                                    echo '<li><a href="' . $path_tmp . 'language_choice=' . $language_file[$i] . '">';
                                    echo '<img src="../languages/' . $language_file[$i] . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none;"> ';
                                    echo '<span class="mobile_hidden">' . $language["name"] . '</span>';
                                    echo '</a>';
                                    echo '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
    </div>
<?php
}
 // *** END OF MENU ***