<?php

/**
 * This is the admin web entry point for HuMo-genealogy.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the manual for basic setup instructions
 *
 * https://humo-gen.com
 *
 * ----------
 *
 * Copyright (C) 2008-2024 Huub Mons,
 * Klaas de Winkel, Jan Maat, Jeroen Beemster, Louis Ywema, Theo Huitema,
 * René Janssen, Yossi Beck
 * and others.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

session_start();
// *** Regenerate session id regularly to prevent session hacking ***
//session_regenerate_id();

$page = 'index';

// DISABLED because the SECURED PAGE message was shown regularly.
// *** Prevent Session hijacking ***
//if (isset( $_SESSION['current_ip_address']) AND $_SESSION['current_ip_address'] != $visitor_ip){
//	// *** Remove login session if IP address is changed ***
//	echo 'BEVEILIGDE BLADZIJDE/ SECURED PAGE';
//	session_unset();
//	session_destroy();
//	die();
//}

// *** Only logoff admin ***
if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name_admin']);
    unset($_SESSION['user_id_admin']);
    unset($_SESSION['group_id_admin']);
}

$ADMIN = TRUE; // *** Override "no database" message for admin ***
include_once(__DIR__ . "/../include/db_login.php"); // *** Database login ***

include_once(__DIR__ . "/../include/safe.php"); // Variables

// *** Function to show family tree texts ***
include_once(__DIR__ . '/../include/show_tree_text.php');

include_once(__DIR__ . "/../include/db_functions_cls.php");
if (isset($dbh)) {
    $db_functions = new db_functions($dbh);
}

// *** Added juli 2019: Person functions ***
include_once(__DIR__ . "/../include/person_cls.php");

// *** Added october 2023: generate links to frontsite ***
include_once(__DIR__ . "/../include/links.php");
$link_cls = new Link_cls();

include_once(__DIR__ . "/../include/get_visitor_ip.php");
$visitor_ip = visitorIP();

// *** Only load settings if database and table exists ***
$show_menu_left = false;
$popup = false;

$update_message = '';

if (isset($database_check) && @$database_check) {  // otherwise we can't make $dbh statements
    $check_tables = false;
    try {
        $check_tables = $dbh->query("SELECT * FROM humo_settings");
    } catch (Exception $e) {
        //
    }

    if ($check_tables) {
        include_once(__DIR__ . "/../include/settings_global.php");

        // *** Added may 2020, needed for some user settings in admin section ***
        // *** At this moment there is no separation for front user and admin user... ***
        include_once(__DIR__ . "/../include/settings_user.php"); // USER variables

        // **** Temporary update scripts ***
        //
        //

        $show_menu_left = true;

        // *** Debug HuMo-genealogy`admin pages ***
        if ($humo_option["debug_admin_pages"] == 'y') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }

        // *** Check if visitor is allowed ***
        if (!$db_functions->check_visitor($visitor_ip)) {
            echo 'Access to website is blocked.';
            exit;
        }
    }
}

// *** First installation: show menu if installation of tables is started ***
if (isset($_POST['install_tables2'])) {
    $show_menu_left = true;
}

if (isset($database_check) && @$database_check) {  // otherwise we can't make $dbh statements
    // *** Update to version 4.6, in older version there is a dutch-named table: humo_instellingen ***
    try {
        $check_update = @$dbh->query("SELECT * FROM humo_instellingen");
        if ($check_update) {
            $page = 'update';
            $show_menu_left = false;
        }
    } catch (Exception $e) {
        //
    }

    // *** Check HuMo-genealogy database status, will be changed if database update is needed ***
    if (isset($humo_option["update_status"]) && $humo_option["update_status"] < 19) {
        $page = 'update';
        $show_menu_left = false;
    }

    if (
        isset($_GET['page']) && ($_GET['page'] == 'editor_sources' || $_GET['page'] == 'editor_place_select' || $_GET['page'] == 'editor_person_select' || $_GET['page'] == 'editor_relation_select' || $_GET['page'] == 'editor_media_select' || $_GET['page'] == 'editor_user_settings')
    ) {
        $show_menu_left = false;
        $popup = true;
    }
}

// *** Set timezone ***
include_once(__DIR__ . "/../include/timezone.php"); // set timezone
timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Get ordered list of languages ***
include(__DIR__ . '/../languages/language_cls.php');
$language_cls = new Language_cls;
$language_file = $language_cls->get_languages();

// *** Select admin language ***
$selected_language = "en";
// *** Saved default language ***
if (
    isset($humo_option['default_language_admin']) && file_exists('../languages/' . $humo_option['default_language_admin'] . '/' . $humo_option['default_language_admin'] . '.mo')
) {
    $selected_language = $humo_option['default_language_admin'];
}
// *** Safety: extra check if language exists ***
if (
    isset($_SESSION["save_language_admin"]) && file_exists('../languages/' . $_SESSION["save_language_admin"] . '/' . $_SESSION["save_language_admin"] . '.mo')
) {
    $selected_language = $_SESSION["save_language_admin"];
}

$language = array();
include(__DIR__ . '/../languages/' . $selected_language . '/language_data.php');

// *** .mo language text files ***
include_once(__DIR__ . "/../languages/gettext.php");
// *** Load ***
$_SESSION["language_selected"] = $selected_language;
Load_default_textdomain();
//Load_textdomain('customer_domain', 'languages/'.$selected_language.'/'.$selected_language.'.mo');

// *** Process LTR and RTL variables ***
$dirmark1 = "&#x200E;";  //ltr marker
$dirmark2 = "&#x200F;";  //rtl marker
$rtlmarker = "ltr";

// *** Switch direction markers if language is RTL ***
if ($language["dir"] == "rtl") {
    $dirmark1 = "&#x200F;";  //rtl marker
    $dirmark2 = "&#x200E;";  //ltr marker
    $rtlmarker = "rtl";
}

//TODO remove PHP-MySQL login from admin pages, only login in front main page?
// *** Process login form ***
$fault = false;
$valid_user = false;
if (isset($_POST["username"]) && isset($_POST["password"])) {
    $resultDb = $db_functions->get_user($_POST["username"], $_POST["password"]);
    if ($resultDb) {
        $valid_user = true;

        // *** FIRST CHECK IF USER IS ADMIN OR EDITOR ***
        // *** Edit family trees [GROUP SETTING] ***
        $groepsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $resultDb->user_group_id . "'");
        @$groepDb = $groepsql->fetch(PDO::FETCH_OBJ);
        if (isset($groepDb->group_edit_trees)) {
            $group_edit_trees = $groepDb->group_edit_trees;
        }
        // *** Edit family trees [USER SETTING] ***
        if (isset($resultDb->user_edit_trees) && $resultDb->user_edit_trees) {
            if ($group_edit_trees) {
                $group_edit_trees .= ';' . $resultDb->user_edit_trees;
            } else {
                $group_edit_trees = $resultDb->user_edit_trees;
            }
        }

        if ($groepDb->group_admin != 'j' && $group_edit_trees == '') {
            // *** User is not an administrator or editor ***
            echo __('Access to admin pages is not allowed.');
            exit;
        }

        // *** 2FA is enabled, so check 2FA code ***
        if (isset($resultDb->user_2fa_enabled) && $resultDb->user_2fa_enabled) {
            $valid_user = false;
            $fault = true;
            include_once(__DIR__ . "/../include/2fa_authentication/authenticator.php");

            if ($_POST['2fa_code'] && is_numeric($_POST['2fa_code'])) {
                $Authenticator = new Authenticator();
                $checkResult = $Authenticator->verifyCode($resultDb->user_2fa_auth_secret, $_POST['2fa_code'], 2);        // 2 = 2*30sec clock tolerance
                if ($checkResult) {
                    $valid_user = true;
                    $fault = false;
                }
            }
        }

        if ($valid_user) {
            $_SESSION['user_name_admin'] = $resultDb->user_name;
            $_SESSION['user_id_admin'] = $resultDb->user_id;
            $_SESSION['group_id_admin'] = $resultDb->user_group_id;

            // *** Add login in logbook ***
            $log_date = date("Y-m-d H:i");
            $sql = "INSERT INTO humo_user_log SET
                log_date='$log_date',
                log_username='" . $resultDb->user_name . "',
                log_ip_address='" . $visitor_ip . "',
                log_user_admin='admin',
                log_status='success'";
            @$dbh->query($sql);
        }
    } else {
        // *** No valid user or password ***
        //$fault='<p align="center"><font color="red">'.__('Please enter a valid username or password. ').'</font>';
        $fault = true;

        // *** Save log! ***
        $sql = "INSERT INTO humo_user_log SET
            log_date='" . date("Y-m-d H:i") . "',
            log_username='" . safe_text_db($_POST["username"]) . "',
            log_ip_address='" . $visitor_ip . "',
            log_user_admin='admin',
            log_status='failed'";
        $dbh->query($sql);
    }
}

// *** Login check ***
$group_administrator = '';
$group_edit_trees = '';
if (isset($database_check) && $database_check) {
    if (isset($_SERVER["PHP_AUTH_USER"])) {
        // *** Logged in using .htacess ***

        // *** Standard group permissions ***
        $group_administrator = 'j';
        $group_edit_trees = '';

        // *** If .htaccess is used, check usergroup for admin rights ***
        @$query = "SELECT * FROM humo_users LEFT JOIN humo_groups
            ON humo_users.user_group_id=humo_groups.group_id
            WHERE humo_users.user_name='" . $_SERVER["PHP_AUTH_USER"] . "'";
        @$result = $dbh->query($query);
        if (@$result->rowCount() > 0) {
            @$resultDb = $result->fetch(PDO::FETCH_OBJ);
            $group_administrator = $resultDb->group_admin;

            // *** Check if user is a editor, GROUP SETTINGS ***
            $group_edit_trees = '';
            if (isset($resultDb->group_edit_trees)) {
                $group_edit_trees = $resultDb->group_edit_trees;
            }
            // *** Edit family trees [USER SETTING] ***
            if (isset($resultDb->user_edit_trees) && $resultDb->user_edit_trees) {
                if ($group_edit_trees) {
                    $group_edit_trees .= ';' . $resultDb->user_edit_trees;
                } else {
                    $group_edit_trees = $resultDb->user_edit_trees;
                }
            }
        }
    } elseif ($page === 'update') {
        // *** No log in, update procedure (group table will be changed) ***
    } else {
        // *** Logged in using PHP-MySQL ***
        $result = false;
        try {
            @$query = "SELECT * FROM humo_users";
            @$result = $dbh->query($query);
        } catch (Exception $e) {
            //
        }
        if ($result !== FALSE) {
            if ($result->rowCount() > 0) {
                // *** humo-users table exists, check admin log in ***
                if (isset($_SESSION["group_id_admin"])) {
                    // *** Logged in as admin... ***

                    // *** Read group settings ***
                    $groepsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $_SESSION["group_id_admin"] . "'");
                    @$groepDb = $groepsql->fetch(PDO::FETCH_OBJ);

                    // *** Check if user is an administrator ***
                    $group_administrator = $groepDb->group_admin;
                    if ($group_administrator != 'j') {
                        $page = 'login';
                    }

                    // *** Edit family trees [GROUP SETTING] ***
                    if (isset($groepDb->group_edit_trees)) {
                        $group_edit_trees = $groepDb->group_edit_trees;
                        $page = '';
                    }
                    // *** Edit family trees [USER SETTING] ***
                    $user_result2 = $dbh->query("SELECT * FROM humo_users WHERE user_id=" . $_SESSION['user_id_admin']);
                    $resultDb = $user_result2->fetch(PDO::FETCH_OBJ);
                    if (isset($resultDb->user_edit_trees) && $resultDb->user_edit_trees) {
                        if ($group_edit_trees) {
                            $group_edit_trees .= ';' . $resultDb->user_edit_trees;
                        } else {
                            $group_edit_trees = $resultDb->user_edit_trees;
                        }
                    }
                } else {
                    // *** Show log in screen ***
                    $page = 'login';
                }
            }
        } else {
            // *** No user table: probably first installation: everything will be visible! ***
        }
    }
}

// *** Save ip address in session to prevent session hijacking ***
if (isset($_SESSION['current_ip_address']) == FALSE) {
    $_SESSION['current_ip_address'] = $visitor_ip;
}

$html_text = '';
if ($language["dir"] == "rtl") {   // right to left language
    $html_text = ' dir="rtl"';
}

// *** Use your own favicon.ico in media folder ***
if (file_exists('../media/favicon.ico')) {
    include_once(__DIR__ . '/../include/give_media_path.php');
    $favicon = '<link href="../' . give_media_path("media/", "favicon.ico") . '" rel="shortcut icon" type="image/x-icon">';
} else {
    $favicon = '<link href="../favicon.ico" rel="shortcut icon" type="image/x-icon">';
}
?>

<!DOCTYPE html>
<html lang="<?= $selected_language; ?>" <?= $html_text; ?>>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">

    <!-- *** Bootstrap: rescale standard HuMo-genealogy pages for mobile devices *** -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= __('Administration'); ?></title>

    <?= $favicon; ?>

    <!-- Bootstrap added in dec. 2023 -->
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <link href="admin.css" rel="stylesheet" type="text/css">

    <script src="../assets/jquery/jquery.min.js"></script>
    <script src="../assets/jqueryui/jquery-ui.min.js"></script>

    <!-- Don't load all scripts for source editor (improves speed of page) -->
    <?php if ($popup == false) { ?>
        <!-- Statistics style sheet -->
        <link href="statistics/style.css" rel="stylesheet" type="text/css">
        <link href="admin_print.css" rel="stylesheet" type="text/css" media="print">
        <script src="include/popup_merge.js"></script>
    <?php } ?>

    <!-- Pop-up menu -->
    <link rel="stylesheet" type="text/css" href="../include/popup_menu/popup_menu.css">
    <script src="../include/popup_menu/popup_menu.js"></script>
</head>

<?php
// *** Close pop-up screen and update main screen ***
if (isset($_GET['page']) && $_GET['page'] == 'close_popup') {
    $page_link = 'editor';
    // *** Also add these links in "Close source screen" link ***
    if (isset($_GET['connect_sub_kind'])) {
        if ($_GET['connect_sub_kind'] == 'address_source') {
            $page_link = 'edit_addresses';
        }
        //if ($_GET['connect_sub_kind']=='pers_address_source') $page_link='edit_addresses';
        //if ($_GET['connect_sub_kind']=='fam_address_source') $page_link='edit_addresses';
        if ($_GET['connect_sub_kind'] == 'pers_event_source') {
            $page_link = 'editor&event_person=1';
        } // Don't use &amp;
        if ($_GET['connect_sub_kind'] == 'fam_event_source') {
            $page_link = 'editor&event_family=1';
        } // Don't use &amp;
    }

    // *** Added May 2021: For multiple marriages ***
    if (substr($_GET['connect_sub_kind'], 0, 3) === 'fam') {
        $page_link .= '&marriage_nr=' . $_SESSION['admin_fam_gedcomnumber'];
    } // Don't use &amp;

    if (isset($_GET['event_person']) && $_GET['event_person'] == '1') {
        $page_link = 'editor&event_person=1#event_person_link';
    } // Don't use &amp;
    //if (isset($_GET['event_family']) AND $_GET['event_family']=='1')
    //	$page_link='editor&event_family=1#event_family_link'; // Don't use &amp;
    // *** Added May 2021: For multiple marriages ***
    if (isset($_GET['event_family']) && $_GET['event_family'] == '1') {
        $page_link = 'editor&event_family=1&marriage_nr=' . $_SESSION['admin_fam_gedcomnumber'] . '#event_family_link';
    } // Don't use &amp;

    echo '<script>';
    echo 'function redirect_to(where, closewin){
            opener.location= \'index.php?page=' . $page_link . '\' + where;
            if (closewin == 1){ self.close(); }
        }';
    echo '</script>';

    //echo '<body onload="redirect_to(\'index.php\',\'1\')">';
    echo '<body onload="redirect_to(\'\',\'1\')">';

    die();
} else {
?>

    <body <?= isset($_GET['page']) && $_GET['page'] == 'maps' ? 'onload="initialize()"' : ''; ?> class="humo">
    <?php
}

// *** Show top menu ***
// TODO check if variable is still needed.
$path_tmp = 'index.php?';

$top_dir = '';
if ($language["dir"] == "rtl") {
    $top_dir = 'style = "text-align:right" ';
}

if ($popup == false) {
    ?>
        <div id="humo_top" <?= $top_dir; ?>>

            <span id="top_website_name">
                &nbsp;<a href="index.php" style="color:brown;">HuMo-genealogy</a>
            </span>
        <?php
    }

    // *** Check for HuMo-genealogy updates ***
    if (isset($database_check) && $database_check && $group_administrator == 'j') { // Otherwise we can't make $dbh statements
        include_once(__DIR__ . '/include/index_check_update.php');
    }

    // *** Feb. 2020: centralised processing of tree_id and tree_prefix ***
    // *** Selected family tree, using tree_id ***
    if (isset($database_check) && $database_check) { // Otherwise we can't make $dbh statements
        $check_tree_id = '';
        // *** admin_tree_id must be numeric ***
        if (isset($_SESSION['admin_tree_id']) && is_numeric($_SESSION['admin_tree_id'])) {
            $check_tree_id = $_SESSION['admin_tree_id'];
        }
        // *** tree_id must be numeric ***
        if (isset($_POST['tree_id']) && is_numeric($_POST['tree_id'])) {
            $check_tree_id = $_POST['tree_id'];
        }
        // *** tree_id must be numeric ***
        if (isset($_GET['tree_id']) && is_numeric($_GET['tree_id'])) {
            $check_tree_id = $_GET['tree_id'];
        }

        // *** Check editor permissions ***
        $edit_tree_array = explode(";", $group_edit_trees);
        if ($group_administrator == 'j' || in_array($check_tree_id, $edit_tree_array)) {
            // OK
        } else {
            // *** No valid family tree. Select first allowed family tree ***
            $check_tree_id = $edit_tree_array[0];
        }

        // *** Just logged in, or no tree_id available: find first family tree ***
        if ($check_tree_id == '') {
            $check_tree_sql = false;
            try {
                $check_tree_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order LIMIT 0,1");
            } catch (Exception $e) {
                //
            }
            if ($check_tree_sql) {
                @$check_treeDb = $check_tree_sql->fetch(PDO::FETCH_OBJ);
                $check_tree_id = $check_treeDb->tree_id;
            }
        }

        // *** Double check tree_id and save tree id in session ***
        $tree_id = '';
        $tree_prefix = '';
        if (isset($check_tree_id) && $check_tree_id && $check_tree_id != '') {
            // *** New installation: table doesn't exist and could generate an error ***
            $temp = $dbh->query("SHOW TABLES LIKE 'humo_trees'");
            if ($temp->rowCount() > 0) {
                try {
                    $get_treeDb = $db_functions->get_tree($check_tree_id);
                } catch (Exception $e) {
                    //
                }

                if (isset($get_treeDb) && $get_treeDb) {
                    $tree_id = $get_treeDb->tree_id;
                    $_SESSION['admin_tree_id'] = $tree_id;
                    $tree_prefix = $get_treeDb->tree_prefix;
                }
            }
        }
    }
        ?>

        <!-- Offcanvas Sidebar -->
        <div class="offcanvas offcanvas-end" id="demo">
            <div class="offcanvas-header">
                <h1 class="offcanvas-title"><?= __('Sidebar'); ?></h1>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="offcanvas-body">
                <!-- <p>Some text lorem ipsum.</p> -->
                <!-- <button class="btn btn-secondary" type="button">A Button</button> -->

                <!-- Control -->
                <?php if ($show_menu_left == true && $page !== 'login') {; ?>
                    <?php if ($group_administrator == 'j') {; ?>
                        <ul>
                            <li><a href="index.php?page=install"><?= __('Install'); ?></a></li>
                            <li>
                                <a href="index.php?page=extensions"><?= __('Extensions'); ?></a>
                                <ul>
                                    <li><?= __('Show/ hide languages'); ?></li>
                                    <li><?= __('Show/ hide theme\'s'); ?></li>
                                </ul>
                            </li>
                            <li><a href="index.php?page=settings"><?= __('Settings'); ?></a></li>
                            <li>
                                <a href="index.php?page=settings&amp;menu_admin=settings_homepage"><?= __('Homepage'); ?></a>
                                <ul>
                                    <li><?= __('Homepage'); ?></li>
                                    <li><?= __('Homepage favourites'); ?></li>
                                    <li><?= __('Slideshow on the homepage'); ?></li>
                                </ul>
                            </li>
                            <li>
                                <a href="index.php?page=settings&amp;menu_admin=settings_special"><?= __('Special settings'); ?></a>
                                <ul>
                                    <li><?= __('Jewish settings'); ?></li>
                                    <li><?= __('Sitemap'); ?></li>
                                </ul>
                            </li>
                            <li><a href="index.php?page=edit_cms_pages"><?= __('CMS Own pages'); ?></a></li>
                            <li><a href="index.php?page=language_editor"><?= __('Language editor'); ?></a></li>
                            <li><a href="index.php?page=prefix_editor"><?= __('Prefix editor'); ?></a></li>
                            <li><a href="index.php?page=maps"><?= __('World map'); ?></a></li>
                        </ul>

                        <!-- Family trees -->
                        <ul>
                            <li><a href="index.php?page=tree"><?= __('Family trees'); ?></a></li>
                            <li>
                                <a href="index.php?page=thumbs"><?= __('Pictures/ create thumbnails'); ?></a>
                                <ul>
                                    <li><?= __('Picture settings'); ?></li>
                                    <li><?= __('Create thumbnails'); ?></li>
                                    <li><?= __('Photo album categories'); ?></li>
                                </ul>
                            </li>
                            <li><a href="index.php?page=notes"><?= __('Notes'); ?></a></li>
                            <li>
                                <a href="index.php?page=check"><?= __('Family tree data check'); ?></a>
                                <ul>
                                    <li><?= __('Check consistency of dates'); ?></li>
                                    <li><?= __('Find invalid dates'); ?></li>
                                    <li><?= __('Check database integrity'); ?></li>
                                </ul>
                            </li>
                            <li><a href="index.php?page=check&amp;tab=changes"><?= __('View latest changes'); ?></a></li>
                            <li>
                                <a href="index.php?page=cal_date"><?= __('Calculated birth date'); ?></a>
                                <ul>
                                    <li><?= __('Privacy filter'); ?></li>
                                </ul>
                            </li>
                            <li><a href="index.php?page=export"><?= __('Gedcom export'); ?></a></li>
                            <li><a href="index.php?page=backup"><?= __('Database backup'); ?></a></li>
                            <li><a href="index.php?page=statistics"><?= __('Statistics'); ?></a></li>
                        </ul>
                    <?php }; ?>

                    <!-- Editor -->
                    <ul>
                        <li><a href="index.php?page=editor"><?= __('Persons and families'); ?></a></li>
                        <li><a href="index.php?page=edit_sources"><?= __('Sources'); ?></a></li>
                        <li><a href="index.php?page=edit_repositories"><?= __('Repositories'); ?></a></li>
                        <li><a href="index.php?page=edit_addresses"><?= __('Shared addresses'); ?></a></li>
                        <li><a href="index.php?page=edit_places"><?= __('Rename places'); ?></a></li>
                    </ul>

                    <!-- Users -->
                    <?php if ($group_administrator == 'j') {; ?>
                        <ul>
                            <li><a href="index.php?page=users"><?= __('Users'); ?></a></li>
                            <li><a href="index.php?page=groups"><?= __('User groups'); ?></a></li>
                            <li>
                                <a href="index.php?page=log"><?= __('Log'); ?></a>

                                <ul>
                                    <li><?= __('Logfile users'); ?></li>
                                    <li><?= __('IP Blacklist'); ?></li>
                                </ul>
                            </li>
                        </ul>
                    <?php }; ?>
                <?php }; ?>
            </div>
        </div>

        <?php
        // *** Show menu ***
        include_once(__DIR__ . '/views/menu.php');

        if ($popup == false) {
        ?>
        </div> <!-- End of humo_top -->
    <?php } ?>

    <div class="p-md-2">
        <?php
        define('ADMIN_PAGE', true); // *** Safety line ***

        if ($page === 'install') {
            include_once(__DIR__ . "/views/install.php");
        } elseif ($page === 'extensions') {
            //require __DIR__ . '/controller/extensionsController.php';
            //$controllerObj = new ExtensionsController();
            //$extensions = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/extensions.php");
        } elseif ($page === 'login') {
            include_once(__DIR__ . "/views/login.php");
        } elseif ($group_administrator == 'j' && $page === 'tree') {
            require __DIR__ . '/controller/treesController.php';
            $controllerObj = new TreesController();
            $trees = $controllerObj->detail($dbh, $tree_id, $db_functions, $selected_language);
            include_once(__DIR__ . "/views/trees.php");
        } elseif ($page === 'editor') {
            require __DIR__ . '/controller/editorController.php';
            $controllerObj = new EditorController();
            $editor = $controllerObj->detail($dbh, $tree_id, $tree_prefix, $db_functions, $humo_option);
            include_once(__DIR__ . "/views/editor.php");
        } elseif ($page === 'editor_sources') {
            include_once(__DIR__ . "/include/editor_sources.php");
        } elseif ($page === 'edit_sources') {
            require __DIR__ . '/controller/edit_sourceController.php';
            $controllerObj = new SourceController();
            $editSource = $controllerObj->detail($dbh, $tree_id, $db_functions);
            include_once(__DIR__ . "/views/edit_source.php");
        } elseif ($page === 'edit_repositories') {
            require __DIR__ . '/controller/edit_repositoryController.php';
            $controllerObj = new RepositoryController();
            $editRepository = $controllerObj->detail($dbh, $tree_id, $db_functions);
            include_once(__DIR__ . "/views/edit_repository.php");
        } elseif ($page === 'edit_addresses') {
            require __DIR__ . '/controller/edit_addressController.php';
            $controllerObj = new AddressController();
            $editAddress = $controllerObj->detail($dbh, $tree_id, $db_functions);
            include_once(__DIR__ . "/views/edit_address.php");
        } elseif ($page === 'edit_places') {
            require __DIR__ . '/controller/edit_rename_placeController.php';
            $controllerObj = new PlaceController();
            $place = $controllerObj->detail($dbh, $tree_id);
            include_once(__DIR__ . "/views/edit_rename_place.php");
        } elseif ($page === 'editor_place_select') {
            include_once(__DIR__ . "/include/editor_place_select.php");
        } elseif ($page === 'editor_person_select') {
            include_once(__DIR__ . "/include/editor_person_select.php");
        } elseif ($page === 'editor_relation_select') {
            include_once(__DIR__ . "/include/editor_relation_select.php");
        } elseif ($page === 'editor_media_select') {
            include_once(__DIR__ . "/include/editor_media_select.php");
        } elseif ($page === 'check') {
            require __DIR__ . '/controller/tree_checkController.php';
            $controllerObj = new TreeCheckController();
            $tree_check = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/tree_check.php");
        } elseif ($page === 'latest_changes') {
            include_once(__DIR__ . "/views/tree_check.php");
            //} elseif ($page === 'gedcom') {
            //    include_once(__DIR__ . "/views/gedcom.php");
        } elseif ($page === 'settings') {
            require __DIR__ . '/controller/settings_adminController.php';
            $controllerObj = new SettingsController();
            $settings = $controllerObj->detail($dbh, $db_functions, $humo_option);
            include_once(__DIR__ . "/views/settings_admin.php");
        } elseif ($page === 'thumbs') {
            require __DIR__ . '/controller/thumbsController.php';
            $controllerObj = new ThumbsController();
            $thumbs = $controllerObj->detail($dbh, $tree_id);
            include_once(__DIR__ . "/views/thumbs.php");
            //} elseif ($page == 'favorites') {
            //    include_once(__DIR__ . "/include/favorites.php");
        } elseif ($page === 'users') {
            require __DIR__ . '/controller/usersController.php';
            $controllerObj = new UsersController();
            $edit_users = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/users.php");
        } elseif ($page === 'editor_user_settings') {
            include_once(__DIR__ . "/include/editor_user_settings.php");
        } elseif ($page === 'groups') {
            require __DIR__ . '/controller/groupsController.php';
            $controllerObj = new GroupsController();
            $groups = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/groups.php");
        } elseif ($page === 'edit_cms_pages') {
            require __DIR__ . '/controller/edit_cms_pagesController.php';
            $controllerObj = new edit_cms_pagesController();
            $edit_cms_pages = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/edit_cms_pages.php");
        } elseif ($page === 'backup') {
            require __DIR__ . '/controller/backupController.php';
            $controllerObj = new BackupController();
            $backup = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/backup.php");
        } elseif ($page === 'notes') {
            require __DIR__ . '/controller/notesController.php';
            $controllerObj = new NotesController();
            $notes = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/notes.php");
        } elseif ($page === 'cal_date') {
            //require __DIR__ . '/controller/cal_dateController.php';
            //$controllerObj = new CalculateDateController();
            //$cal_date = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/cal_date.php");
        } elseif ($page === 'export') {
            require __DIR__ . '/controller/gedcom_exportController.php';
            $controllerObj = new Gedcom_exportController();
            $export = $controllerObj->detail($dbh, $tree_id, $humo_option, $db_functions);
            include_once(__DIR__ . "/views/gedcom_export.php");
        } elseif ($page === 'log') {
            require __DIR__ . '/controller/logController.php';
            $controllerObj = new LogController();
            $log = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/log.php");
        } elseif ($page === 'language_editor') {
            require __DIR__ . '/controller/language_editorController.php';
            $controllerObj = new Language_editorController();
            $language_editor = $controllerObj->detail($dbh, $humo_option);
            include_once(__DIR__ . "/views/language_editor.php");
        } elseif ($page === 'prefix_editor') {
            include_once(__DIR__ . "/views/prefix_editor.php");
        } elseif ($page === 'maps') {
            require __DIR__ . '/controller/mapsController.php';
            $controllerObj = new MapsController();
            $maps = $controllerObj->detail($dbh, $db_functions);
            include_once(__DIR__ . "/views/maps.php");
        } elseif ($page === 'statistics') {
            require __DIR__ . '/controller/admin_statisticsController.php';
            $controllerObj = new StatisticsController();
            $statistics = $controllerObj->detail($dbh, $db_functions);
            include_once(__DIR__ . "/views/admin_statistics.php");
        } elseif ($page === 'install_update') {
            include_once(__DIR__ . "/update/install_update.php");
        } elseif ($page === 'update') {
            include_once(__DIR__ . "/include/update.php");
        }
        //elseif ($page=='photoalbum'){ include_once (__DIR__ . "/include/photoalbum_categories.php"); }

        // *** Edit event by person ***
        //elseif ($page=='editor_person_event'){ include_once (__DIR__ . "/include/editor_person_event.php"); }

        // *** Default page for editor ***
        elseif ($group_administrator != 'j' && $group_edit_trees) {
            include_once(__DIR__ . "/views/editor.php");
        }

        // *** Default page for administrator ***
        else {
            // *** TODO: improve processing of uninstalled database ***
            if (!isset($database_check)) {
                $database_check = '';
            }
            if (!isset($dbh)) {
                $dbh = '';
            }
            require __DIR__ . '/controller/index_adminController.php';
            $controllerObj = new IndexController();
            $index = $controllerObj->detail($database_check, $dbh);
            include_once(__DIR__ . "/views/index_admin.php");
        }
        ?>

    </div>

    </body>

</html>