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
 * Copyright (C) 2008-2025 Huub Mons,
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

use Genealogy\Admin\Controller\AdminAddressController;
use Genealogy\Admin\Controller\AdminCmsPagesController;
use Genealogy\Admin\Controller\AdminIndexController;
use Genealogy\Admin\Controller\AdminMapsController;
use Genealogy\Admin\Controller\AdminRepositoryController;
use Genealogy\Admin\Controller\AdminSettingsController;
use Genealogy\Admin\Controller\AdminSourceController;
use Genealogy\Admin\Controller\AdminSourcesController;
use Genealogy\Admin\Controller\AdminStatisticsController;
use Genealogy\Admin\Controller\BackupController;
use Genealogy\Admin\Controller\EditorController;
use Genealogy\Admin\Controller\ExtensionsController;
use Genealogy\Admin\Controller\GedcomExportController;
use Genealogy\Admin\Controller\GroupsController;
use Genealogy\Admin\Controller\InstallController;
use Genealogy\Admin\Controller\LanguageEditorController;
use Genealogy\Admin\Controller\LogController;
use Genealogy\Admin\Controller\NotesController;
use Genealogy\Admin\Controller\RenamePlaceController;
use Genealogy\Admin\Controller\ThumbsController;
use Genealogy\Admin\Controller\TreeCheckController;
use Genealogy\Admin\Controller\TreesController;
use Genealogy\Admin\Controller\UsersController;
use Genealogy\Include\Authenticator;
use Genealogy\Include\DbFunctions;
use Genealogy\Include\GeneralSettings;
use Genealogy\Include\GetVisitorIP;
use Genealogy\Include\MediaPath;
use Genealogy\Include\ProcessLinks;
use Genealogy\Include\SafeTextDb;
use Genealogy\Include\SelectTree;
use Genealogy\Include\SetTimezone;
use Genealogy\Include\ShowTreeText;
use Genealogy\Include\UserSettings;
use Genealogy\Languages\LanguageCls;

session_start();
// *** Regenerate session id regularly to prevent session hacking ***
//session_regenerate_id();

// DISABLED because the SECURED PAGE message was shown regularly.
// *** Prevent Session hijacking ***
//if (isset( $_SESSION['current_ip_address']) AND $_SESSION['current_ip_address'] != $visitor_ip){
//	// *** Remove login session if IP address is changed ***
//	echo 'BEVEILIGDE BLADZIJDE/ SECURED PAGE';
//	session_unset();
//	session_destroy();
//	die();
//}

$page = 'index';

// *** Autoload composer classes ***
require __DIR__ . '/../vendor/autoload.php';

// TODO refactor/ move to model
// *** Only logoff admin ***
if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name_admin']);
    unset($_SESSION['user_id_admin']);
    unset($_SESSION['group_id_admin']);
}

$ADMIN = TRUE; // *** Override "no database" message for admin ***
include_once(__DIR__ . "/../include/db_login.php"); // *** Database login ***

$safeTextDb = new SafeTextDb();
$showTreeText = new ShowTreeText();
$selectTree = new SelectTree();

if (isset($dbh)) {
    $db_functions = new DbFunctions($dbh);
}

// *** Added october 2023: generate links to frontsite ***
$processLinks = new ProcessLinks();

$mediaPath = new MediaPath();

$getVisitorIP = new GetVisitorIP;
$visitor_ip = $getVisitorIP->visitorIP();



// *** Added dec. 2024 ***
// Files are prepared, not used yet.
//$controllerObj = new Main_adminController();
//$main_admin = $controllerObj->detail();

//$check_tables = $main_admin['check_table'];
//$page = $main_admin['page'];
//$popup = $main_admin['popup'];



// *** Only load settings if database and table exists ***
$main_admin['show_menu'] = false;
$popup = false;

if (isset($database_check) && $database_check) {  // otherwise we can't make $dbh statements
    $check_tables = false;
    try {
        $check_tables = $dbh->query("SELECT * FROM humo_settings");
    } catch (Exception $e) {
        //
    }

    if ($check_tables) {
        $generalSettings = new GeneralSettings();
        $humo_option = $generalSettings->get_humo_option($dbh);

        $userSettings = new UserSettings();
        $user = $userSettings->get_user_settings($dbh);

        // **** Temporary update scripts ***
        //
        //

        $main_admin['show_menu'] = true;

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
    $main_admin['show_menu'] = true;
}

if (isset($database_check) && $database_check) {  // otherwise we can't make $dbh statements
    // *** Update to version 4.6, in older version there is a dutch-named table: humo_instellingen ***
    try {
        $check_update = $dbh->query("SELECT * FROM humo_instellingen");
        if ($check_update) {
            $page = 'update';
            $main_admin['show_menu'] = false;
        }
    } catch (Exception $e) {
        //
    }

    // *** Check HuMo-genealogy database status, will be changed if database update is needed ***
    if (isset($humo_option["update_status"]) && $humo_option["update_status"] < 19) {
        $page = 'update';
        $main_admin['show_menu'] = false;
    }

    if (
        isset($_GET['page']) && ($_GET['page'] == 'editor_sources' || $_GET['page'] == 'editor_place_select' || $_GET['page'] == 'editor_person_select' || $_GET['page'] == 'editor_relation_select' || $_GET['page'] == 'editor_media_select' || $_GET['page'] == 'editor_user_settings' || $_GET['page'] == 'gedcom_import2')
    ) {
        $main_admin['show_menu'] = false;
        $popup = true;
    }
}

// *** Set timezone ***
$setTimezone = new SetTimezone;
$setTimezone->timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Get ordered list of languages ***
$language_cls = new LanguageCls;
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
        $groepDb = $groepsql->fetch(PDO::FETCH_OBJ);
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
            $sql = "INSERT INTO humo_user_log SET
                log_date = :log_date,
                log_username = :log_username,
                log_ip_address = :log_ip_address,
                log_user_admin = 'admin',
                log_status = 'success'";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':log_date', date("Y-m-d H:i"), PDO::PARAM_STR);
            $stmt->bindValue(':log_username', $resultDb->user_name, PDO::PARAM_STR);
            $stmt->bindValue(':log_ip_address', $visitor_ip, PDO::PARAM_STR);
            $stmt->execute();
        }
    } else {
        // *** No valid user or password ***
        $fault = true;

        // *** Save log ***
        $sql = "INSERT INTO humo_user_log SET
            log_date = :log_date,
            log_username = :log_username,
            log_ip_address = :log_ip_address,
            log_user_admin = 'admin',
            log_status = 'failed'";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':log_date', date("Y-m-d H:i"), PDO::PARAM_STR);
        $stmt->bindValue(':log_username', $_POST["username"], PDO::PARAM_STR);
        $stmt->bindValue(':log_ip_address', $visitor_ip, PDO::PARAM_STR);
        $stmt->execute();
    }
}

// *** Login check ***
$group_administrator = '';
$group_edit_trees = '';
if (isset($database_check) && $database_check) {
    // TODO: htaccess login will be removed in a future version.
    if (isset($_SERVER["PHP_AUTH_USER"])) {
        // *** Logged in using .htacess ***

        // *** Standard group permissions ***
        $group_administrator = 'j';
        $group_edit_trees = '';

        // *** If .htaccess is used, check usergroup for admin rights ***
        $query = "SELECT * FROM humo_users LEFT JOIN humo_groups
            ON humo_users.user_group_id = humo_groups.group_id
            WHERE humo_users.user_name = :username";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':username', $_SERVER["PHP_AUTH_USER"], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt;
        if ($result->rowCount() > 0) {
            $resultDb = $result->fetch(PDO::FETCH_OBJ);
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
            $query = "SELECT * FROM humo_users";
            $result = $dbh->query($query);
        } catch (Exception $e) {
            //
        }
        if ($result !== FALSE) {
            if ($result->rowCount() > 0) {
                // *** humo-users table exists, check admin log in ***
                if (isset($_SESSION["group_id_admin"]) && is_numeric($_SESSION["group_id_admin"]) && isset($_SESSION["user_id_admin"])) {
                    // *** Logged in as admin... ***

                    // *** Read group settings ***
                    $groepsql = $dbh->prepare("SELECT * FROM humo_groups WHERE group_id = :group_id");
                    $groepsql->bindValue(':group_id', $_SESSION["group_id_admin"], PDO::PARAM_INT);
                    $groepsql->execute();
                    $groepDb = $groepsql->fetch(PDO::FETCH_OBJ);

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
                    $user_result2 = $dbh->prepare("SELECT * FROM humo_users WHERE user_id = :user_id");
                    $user_result2->bindValue(':user_id', $_SESSION['user_id_admin'], PDO::PARAM_INT);
                    $user_result2->execute();
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

// *** Use your own favicon.ico in media folder ***
if (file_exists('../media/favicon.ico')) {
    $favicon = '<link href="../' . $mediaPath->give_media_path("media/", "favicon.ico") . '" rel="shortcut icon" type="image/x-icon">';
} else {
    $favicon = '<link href="../favicon.ico" rel="shortcut icon" type="image/x-icon">';
}
?>

<!DOCTYPE html>
<html lang="<?= $selected_language; ?>" <?= $language["dir"] == "rtl" ? 'dir="rtl"' : ''; ?>>

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
        <link href="admin_print.css" rel="stylesheet" type="text/css" media="print">
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
            $page_link = 'editor&event_person=1'; // Don't use &amp;
        }
        if ($_GET['connect_sub_kind'] == 'fam_event_source') {
            $page_link = 'editor&event_family=1'; // Don't use &amp;
        }
    }

    // *** Added May 2021: For multiple marriages ***
    if (substr($_GET['connect_sub_kind'], 0, 3) === 'fam') {
        $page_link .= '&marriage_nr=' . $_SESSION['admin_fam_gedcomnumber']; // Don't use &amp;
    }

    if (isset($_GET['event_person']) && $_GET['event_person'] == '1') {
        $page_link = 'editor&event_person=1#event_person_link'; // Don't use &amp;
    }
    //if (isset($_GET['event_family']) AND $_GET['event_family']=='1')
    //	$page_link='editor&event_family=1#event_family_link'; // Don't use &amp;
    // *** Added May 2021: For multiple marriages ***
    if (isset($_GET['event_family']) && $_GET['event_family'] == '1') {
        $page_link = 'editor&event_family=1&marriage_nr=' . $_SESSION['admin_fam_gedcomnumber'] . '#event_family_link'; // Don't use &amp;
    }

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
if ($popup == false) {
    ?>
        <div id="humo_top" <?= $language["dir"] == "rtl" ? 'style = "text-align:right"' : ''; ?>>

            <span id="top_website_name">
                &nbsp;<a href="index.php">HuMo-genealogy</a>
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
                $check_treeDb = $check_tree_sql->fetch(PDO::FETCH_OBJ);
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
                <?php if ($main_admin['show_menu'] == true && $page !== 'login') {; ?>
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
                    <?php } ?>

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
                    <?php } ?>
                <?php } ?>
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

        /**
         * General config array. May 2025: added adminBaseModel.php.
         * 
         * In controller:
         * private $admin_config;
         *   public function __construct($admin_config)
         *   {
         *       $this->admin_config = $admin_config;
         *   }
         *
         * In model: class installModel extends AdminBaseModel.
         * Then use: $this->dbh, $this->db_functions, $this->tree_id, $this->humo_option.
         */
        if (isset($dbh)) {
            $admin_config = array(
                "dbh" => $dbh,
                "db_functions" => $db_functions,
                "tree_id" => $tree_id,
                "humo_option" => $humo_option
            );
        }
        //"user" => $user,

        if ($page === 'install') {
            // *** Don't use $admin_config because of new installation ***
            $controllerObj = new InstallController();
            $install = $controllerObj->detail($dbh);
            include_once(__DIR__ . "/views/install.php");
        } elseif ($page === 'extensions') {
            $controllerObj = new ExtensionsController($admin_config);
            $extensions = $controllerObj->detail($language_file);
            include_once(__DIR__ . "/views/extensions.php");
        } elseif ($page === 'login') {
            include_once(__DIR__ . "/views/login.php");
        } elseif ($group_administrator == 'j' && $page === 'tree') {
            $controllerObj = new TreesController($admin_config);
            $trees = $controllerObj->detail($selected_language);
            include_once(__DIR__ . "/views/trees.php");
        } elseif ($page === 'editor') {
            // TODO check processing of tree_id in db_functions.
            // *** Editor icon for admin and editor: select family tree ***
            if (isset($tree_id) && $tree_id) {
                $db_functions->set_tree_id($tree_id);
            }

            $controllerObj = new EditorController($admin_config);
            $editor = $controllerObj->detail($tree_prefix);
            include_once(__DIR__ . "/views/editor.php");
        } elseif ($page === 'editor_sources') {
            $controllerObj = new AdminSourcesController($admin_config);
            $editSources = $controllerObj->detail();
            include_once(__DIR__ . "/views/editor_sources.php");
        } elseif ($page === 'edit_sources') {
            $controllerObj = new AdminSourceController($admin_config);
            $editSource = $controllerObj->detail();
            include_once(__DIR__ . "/views/edit_source.php");
        } elseif ($page === 'edit_repositories') {
            $controllerObj = new AdminRepositoryController($admin_config);
            $editRepository = $controllerObj->detail();
            include_once(__DIR__ . "/views/edit_repository.php");
        } elseif ($page === 'edit_addresses') {
            $controllerObj = new AdminAddressController($admin_config);
            $editAddress = $controllerObj->detail();
            include_once(__DIR__ . "/views/edit_address.php");
        } elseif ($page === 'edit_places') {
            $controllerObj = new RenamePlaceController($admin_config);
            $place = $controllerObj->detail();
            include_once(__DIR__ . "/views/edit_rename_place.php");
        } elseif ($page === 'editor_place_select') {
            include_once(__DIR__ . "/views/editor_place_select.php");
        } elseif ($page === 'editor_person_select') {
            include_once(__DIR__ . "/views/editor_person_select.php");
        } elseif ($page === 'editor_relation_select') {
            include_once(__DIR__ . "/views/editor_relation_select.php");
        } elseif ($page === 'editor_media_select') {
            include_once(__DIR__ . "/views/editor_media_select.php");
        } elseif ($page === 'check') {
            $controllerObj = new TreeCheckController($admin_config);
            $tree_check = $controllerObj->detail();
            include_once(__DIR__ . "/views/tree_check.php");
        } elseif ($page === 'latest_changes') {
            include_once(__DIR__ . "/views/tree_check.php");
        } elseif ($page === 'settings') {
            $controllerObj = new AdminSettingsController($admin_config);
            $settings = $controllerObj->detail();
            include_once(__DIR__ . "/views/settings_admin.php");
        } elseif ($page === 'thumbs') {
            $controllerObj = new ThumbsController($admin_config);
            $thumbs = $controllerObj->detail();
            include_once(__DIR__ . "/views/thumbs.php");
            //} elseif ($page == 'favorites') {
            //    include_once(__DIR__ . "/include/favorites.php");
        } elseif ($page === 'users') {
            $controllerObj = new UsersController($admin_config);
            $edit_users = $controllerObj->detail();
            include_once(__DIR__ . "/views/users.php");
        } elseif ($page === 'editor_user_settings') {
            include_once(__DIR__ . "/views/editor_user_settings.php");
        } elseif ($page === 'groups') {
            $controllerObj = new GroupsController($admin_config);
            $groups = $controllerObj->detail();
            include_once(__DIR__ . "/views/groups.php");
        } elseif ($page === 'edit_cms_pages') {
            $controllerObj = new AdminCmsPagesController($admin_config);
            $edit_cms_pages = $controllerObj->detail();
            include_once(__DIR__ . "/views/edit_cms_pages.php");
        } elseif ($page === 'backup') {
            $controllerObj = new BackupController($admin_config);
            $backup = $controllerObj->detail();
            include_once(__DIR__ . "/views/backup.php");
        } elseif ($page === 'notes') {
            $controllerObj = new NotesController($admin_config);
            $notes = $controllerObj->detail();
            include_once(__DIR__ . "/views/notes.php");
        } elseif ($page === 'cal_date') {
            //$controllerObj = new CalculateDateController($admin_config);
            //$cal_date = $controllerObj->detail();
            include_once(__DIR__ . "/views/cal_date.php");
        } elseif ($page === 'export') {
            $controllerObj = new GedcomExportController($admin_config);
            $export = $controllerObj->detail();
            include_once(__DIR__ . "/views/gedcom_export.php");
        } elseif ($page === 'log') {
            $controllerObj = new LogController($admin_config);
            $log = $controllerObj->detail();
            include_once(__DIR__ . "/views/log.php");
        } elseif ($page === 'language_editor') {
            $controllerObj = new LanguageEditorController($admin_config);
            $language_editor = $controllerObj->detail();
            include_once(__DIR__ . "/views/language_editor.php");
        } elseif ($page === 'prefix_editor') {
            include_once(__DIR__ . "/views/prefix_editor.php");
        } elseif ($page === 'maps') {
            $controllerObj = new AdminMapsController($admin_config);
            $maps = $controllerObj->detail();
            include_once(__DIR__ . "/views/maps.php");
        } elseif ($page === 'statistics') {
            $controllerObj = new AdminStatisticsController($admin_config);
            $statistics = $controllerObj->detail();
            include_once(__DIR__ . "/views/admin_statistics.php");
        } elseif ($page === 'install_update') {
            include_once(__DIR__ . "/update/install_update.php");
        } elseif ($page === 'update') {
            include_once(__DIR__ . "/include/update.php");
        } elseif ($page === 'gedcom_import2') {
            $controllerObj = new TreesController($admin_config);
            $trees = $controllerObj->detail($selected_language);
            include_once(__DIR__ . "/views/gedcom_import2.php");
        }
        //elseif ($page=='photoalbum'){
        //    include_once (__DIR__ . "/include/photoalbum_categories.php");
        //}

        // *** Edit event by person ***
        //elseif ($page=='editor_person_event'){
        //  include_once (__DIR__ . "/include/editor_person_event.php");
        //}

        // *** Default page for editor ***
        elseif ($group_administrator != 'j' && $group_edit_trees) {
            // TODO check processing of tree_id in db_functions.
            // *** Editor icon for admin and editor: select family tree ***
            if (isset($tree_id) && $tree_id) {
                $db_functions->set_tree_id($tree_id);
            }

            $controllerObj = new EditorController($admin_config);
            $editor = $controllerObj->detail($tree_prefix);
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
            $controllerObj = new AdminIndexController();
            $index = $controllerObj->detail($database_check, $dbh);
            include_once(__DIR__ . "/views/index_admin.php");
        }
        ?>
    </div>

    <!-- May 2025: Bootstrap popover -->
    <script>
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
    </script>

    <!-- May 2025: Bootstrap tooltip -->
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>

    </body>

</html>