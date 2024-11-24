<?php

/**
 * This is the main web entry point for HuMo-genealogy.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the manual for basic setup instructions
 *
 * https://humo-gen.com
 *
 * Copyright (C) 2008-2024 Huub Mons,
 * Klaas de Winkel, Jan Maat, Jeroen Beemster, Louis Ywema, Theo Huitema,
 * Reni Janssen, Yossi Beck
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

// *** Disabled 18-01-2023 ***
//ini_set('url_rewriter.tags','');

//session_cache_limiter('private, must-revalidate'); //tb edit
session_start();
// *** Regenerate session id regularly to prevent session hacking ***
//session_regenerate_id();

if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_group_id']);
    unset($_SESSION['tree_prefix']);
    session_destroy();
}

include_once(__DIR__ . "/include/db_login.php"); //Inloggen database.
include_once(__DIR__ . '/include/show_tree_text.php');
include_once(__DIR__ . "/include/db_functions_cls.php");
$db_functions = new db_functions($dbh);

// *** Show a message at NEW installation ***
try {
    $result = $dbh->query("SELECT COUNT(*) FROM humo_settings");
} catch (PDOException $e) {
    echo "Installation of HuMo-genealogy is not yet completed.<br>Installatie van HuMo-genealogy is nog niet voltooid.";
    exit();
}

include_once(__DIR__ . "/include/safe.php");
include_once(__DIR__ . "/include/settings_global.php"); // System variables
include_once(__DIR__ . "/include/settings_user.php"); // User variables

include_once(__DIR__ . "/include/get_visitor_ip.php");
$visitor_ip = visitorIP();

// TODO dec. 2023 now included this in index.php. Check other includes...
include_once(__DIR__ . "/include/person_cls.php");
$person_cls = new person_cls;

// *** Debug HuMo-genealogy front pages ***
if ($humo_option["debug_front_pages"] == 'y') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// *** Check if visitor is allowed access to website ***
if (!$db_functions->check_visitor($visitor_ip, 'partial')) {
    echo 'Access to website is blocked.';
    exit;
}

// *** Set timezone ***
include_once(__DIR__ . "/include/timezone.php"); // set timezone 
timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Check if visitor is a bot or crawler ***
$bot_visit = preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);
// *** Line for bot test! ***
//$bot_visit=true;

// *** Get ordered list of languages ***
include(__DIR__ . '/languages/language_cls.php');
$language_cls = new Language_cls;
$language_file = $language_cls->get_languages();

// *** Log in ***
$valid_user = false;
$fault = false;
if (isset($_POST["username"]) && isset($_POST["password"])) {
    $resultDb = $db_functions->get_user($_POST["username"], $_POST["password"]);
    if ($resultDb) {
        $valid_user = true;

        // *** 2FA is enabled, so check 2FA code ***
        if (isset($resultDb->user_2fa_enabled) && $resultDb->user_2fa_enabled) {
            $valid_user = false;
            $fault = true;
            include_once(__DIR__ . "/include/2fa_authentication/authenticator.php");

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
            $_SESSION['user_name'] = $resultDb->user_name;
            $_SESSION['user_id'] = $resultDb->user_id;
            $_SESSION['user_group_id'] = $resultDb->user_group_id;


            // *** August 2023: Also login for admin pages ***
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
                //echo __('Access to admin pages is not allowed.');
                //exit;
            } else {
                $_SESSION['user_name_admin'] = $resultDb->user_name;
                $_SESSION['user_id_admin'] = $resultDb->user_id;
                $_SESSION['group_id_admin'] = $resultDb->user_group_id;
            }

            // *** Save succesful login into log! ***
            $sql = "INSERT INTO humo_user_log SET
                log_date='" . date("Y-m-d H:i") . "',
                log_username='" . $resultDb->user_name . "',
                log_ip_address='" . $visitor_ip . "',
                log_user_admin='user',
                log_status='success'";
            $dbh->query($sql);

            // *** Send to secured page ***
            header("Location: index.php?menu_choice=main_index");
            exit();
        }
    } else {
        // *** No valid user found ***
        $fault = true;

        // *** Save failed login into log! ***
        $sql = "INSERT INTO humo_user_log SET
            log_date='" . date("Y-m-d H:i") . "',
            log_username='" . safe_text_db($_POST["username"]) . "',
            log_ip_address='" . $visitor_ip . "',
            log_user_admin='user',
            log_status='failed'";
        $dbh->query($sql);
    }
}

// *** Language processing after header("..") lines. *** 
include_once(__DIR__ . "/languages/language.php"); //Taal

// *** Process LTR and RTL variables ***
$dirmark1 = "&#x200E;";  //ltr marker
$dirmark2 = "&#x200F;";  //rtl marker
$rtlmarker = "ltr";
$alignmarker = "left";
// *** Switch direction markers if language is RTL ***
if ($language["dir"] == "rtl") {
    $dirmark1 = "&#x200F;";  //rtl marker
    $dirmark2 = "&#x200E;";  //ltr marker
    $rtlmarker = "rtl";
    $alignmarker = "right";
}
if (isset($screen_mode) && $screen_mode == "PDF") {
    $dirmark1 = '';
    $dirmark2 = '';
}


// *** Process title of page and $uri_path ***
//$request_uri = $_SERVER['REQUEST_URI'];

// *** Option url_rewrite disabled ***
// http://127.0.0.1/humo-genealogy/index.php?page=ancestor_sheet&tree_id=3&id=I1180
// change into (but still process index.php, so this will work in NGinx with url_rewrite disabled):
// http://127.0.0.1/humo-genealogy/ancestor_sheet&tree_id=3&id=I1180
//if (isset($_GET['page'])) $request_uri = str_replace('index.php?page=', '', $request_uri);

// *** Example: http://localhost/HuMo-genealogy/photoalbum/2?start=1&item=11 ***
//$request_uri = strtok($request_uri, "?"); // Remove last part of url: ?start=1&item=11

// *** Get url_rewrite variables ***
//$url_array = explode('/', $request_uri);

// *** Default values
$page = 'index';
$head_text = $humo_option["database_name"];
$tmp_path = '';

// *** New routing script sept. 2023 ***
include_once(__DIR__ . '/app/routing/router.php');
# Search route, return match or not found
$router = new Router();
$matchedRoute = $router->get_route($_SERVER['REQUEST_URI']);
if (isset($matchedRoute['page'])) {
    $page = $matchedRoute['page'];

    // TODO remove title from router script
    $head_text = $matchedRoute['title'];

    if (isset($matchedRoute['select_tree_id'])) {
        $select_tree_id = $matchedRoute['select_tree_id'];
    }

    // *** Used for list_names ***
    if (isset($matchedRoute['last_name']) && is_string($matchedRoute['last_name'])) {
        $last_name = $matchedRoute['last_name'];
    }

    // Old link from http://www.stamboomzoeker.nl to updated website using new links.
    // http://127.0.0.1/humo-genealogy/gezin.php?database=humo2_&id=F59&hoofdpersoon=I151
    if ($humo_option["url_rewrite"] == 'j' && isset($_GET["id"])) {
        // Skip routing. Just use $_GET["id"] from link.
    } elseif (isset($matchedRoute['id'])) {
        // *** Used for source ***
        // TODO improve processing of these variables 
        $id = $matchedRoute['id']; // for source
        $_GET["id"] = $matchedRoute['id']; // for family page, and other pages? TODO improve processing of these variables.
    }

    if ($matchedRoute['tmp_path']) {
        $tmp_path = $matchedRoute['tmp_path'];
    }
}


// *** Family tree choice ***
global $database;
$database = '';
if (isset($_GET["database"])) {
    $database = $_GET["database"];
}
if (isset($_POST["database"])) {
    $database = $_POST["database"];
}

// *** Use family tree number in the url: database=humo_2 changed into: tree_id=1 ***
if (isset($_GET["tree_id"])) {
    $select_tree_id = $_GET["tree_id"];
}
if (isset($_POST["tree_id"])) {
    $select_tree_id = $_POST["tree_id"];
}
if (isset($select_tree_id) && is_numeric($select_tree_id) && $select_tree_id) {
    // *** Check if family tree really exists ***
    $dataDb = $db_functions->get_tree($select_tree_id);
    if ($dataDb && $select_tree_id == $dataDb->tree_id) {
        $_SESSION['tree_prefix'] = $dataDb->tree_prefix;
        $database = $dataDb->tree_prefix;
    }
}

// *** For example: database=humo2_ ***
if (isset($database) && is_string($database) && $database) {
    // *** Check if family tree really exists ***
    $dataDb = $db_functions->get_tree($database);
    if ($dataDb && $database == $dataDb->tree_prefix) {
        $_SESSION['tree_prefix'] = $database;
    }
}

// *** No family tree selected yet ***
if (!isset($_SESSION["tree_prefix"]) || $_SESSION['tree_prefix'] == '') {
    $_SESSION['tree_prefix'] = ''; // *** If all trees are blocked then session is empty ***

    // *** Find first family tree that's not blocked for this usergroup ***
    $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
    while (@$dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
        // *** Check is family tree is showed or hidden for user group ***
        $hide_tree_array = explode(";", $user['group_hide_trees']);
        $hide_tree = false;
        if (in_array($dataDb->tree_id, $hide_tree_array)) {
            $hide_tree = true;
        }
        if ($hide_tree == false) {
            $_SESSION['tree_prefix'] = $dataDb->tree_prefix;
            break;
        }
    }
}

// *** Check if selected tree is allowed for visitor and Google etc. ***
@$dataDb = $db_functions->get_tree($_SESSION['tree_prefix']);
$hide_tree_array = explode(";", $user['group_hide_trees']);
$hide_tree = false;
if (in_array(@$dataDb->tree_id, $hide_tree_array)) {
    $hide_tree = true;
}
if ($hide_tree) {
    // *** Logged in or logged out user is not allowed to see this tree. Select another if possible ***
    $_SESSION['tree_prefix'] = '';
    $_SESSION['tree_id'] = 0;
    $tree_id = 0;

    // *** Find first family tree that's not blocked for this usergroup ***
    $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
    while (@$dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
        // *** Check is family tree is showed or hidden for user group ***
        $hide_tree_array = explode(";", $user['group_hide_trees']);
        $hide_tree = false;
        if (in_array($dataDb->tree_id, $hide_tree_array)) {
            $hide_tree = true;
        }
        if ($hide_tree == false) {
            $_SESSION['tree_prefix'] = $dataDb->tree_prefix;
            $_SESSION['tree_id'] = $dataDb->tree_id;
            $tree_id = $dataDb->tree_id;
            break;
        }
    }
} elseif (isset($dataDb->tree_id)) {
    $_SESSION['tree_id'] = $dataDb->tree_id;
    $tree_id = $dataDb->tree_id;
}

// *** Guest or user has no permission to see any family tree ***
if (!isset($tree_id)) {
    $_SESSION['tree_prefix'] = '';
    $_SESSION['tree_id'] = 0;
    $tree_id = 0;
}

// *** Set variable for queries ***
$tree_prefix_quoted = safe_text_db($_SESSION['tree_prefix']);

// TODO check variable. Just use $tree_id?
$db_functions->set_tree_id($_SESSION['tree_id']);

// *** If an HuMo-gen upgrade is done, automatically update language files ***
if ($humo_option['death_char'] == "y") {   // user wants infinity instead of cross -> check if the language files comply
    $str = file_get_contents("languages/en/en.po");
    if (strpos($str, 'msgstr "&#134;"') || strpos($str, 'msgstr "&dagger;"')) {    // the cross is used (probably new upgrade) so this has to be changed to infinity
        include(__DIR__ . "/languages/change_all.php");
    }
}

// *** New routing script sept. 2023 ***
/*
include_once(__DIR__ . '/app/routing/router.php');
# Search route, return match or not found
$router = new Router();
$matchedRoute = $router->get_route($_SERVER['REQUEST_URI']);
if (isset($matchedRoute['page'])) {
    $page = $matchedRoute['page'];

    // TODO remove title from router script
    $head_text = $matchedRoute['title'];

    if (isset($matchedRoute['select_tree_id'])) {
        $select_tree_id = $matchedRoute['select_tree_id'];
    }
    // *** Used for list_names ***
    if (isset($matchedRoute['last_name']) and is_string($matchedRoute['last_name'])) {
        $last_name = $matchedRoute['last_name'];
    }
    // *** Used for source ***
    // TODO improve processing of these variables 
    if (isset($matchedRoute['id'])) {
        $id = $matchedRoute['id']; // for source
        $_GET["id"] = $matchedRoute['id']; // for family page, and other pages? TODO improve processing of these variables.
    }

    if ($matchedRoute['tmp_path']) {
        $tmp_path = $matchedRoute['tmp_path'];
    }
}
*/

// *** Backwards compatibility only ***
// *** Example: gezin.php?database=humo_&id=F1&hoofdpersoon=I2 ***
// *** Allready moved most variables to routing script ***
if (isset($_GET["hoofdpersoon"])) {
    $_GET['main_person'] = $_GET["hoofdpersoon"];
}
if (isset($_POST["hoofdpersoon"])) {
    $_POST['main_person'] = $_POST["hoofdpersoon"];
}

// *** Generate BASE HREF for use in url_rewrite ***
// SERVER_NAME   127.0.0.1
//     PHP_SELF: /url_test/index/1abcd2345/
// OF: PHP_SELF: /url_test/index.php
// REQUEST_URI: /url_test/index/1abcd2345/
// REQUEST_URI: /url_test/index.php?variabele=1
$base_href = '';
if ($humo_option["url_rewrite"] == "j" && $tmp_path) {
    // *** url_rewrite. 26 jan. 2024 Ron: Added proxy check ***
    //if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
        $uri_path = 'https://' . $_SERVER['SERVER_NAME'] . $tmp_path;
    } else {
        $uri_path = 'http://' . $_SERVER['SERVER_NAME'] . $tmp_path;
    }
    $base_href = $uri_path;
} else {
    // *** Use standard uri ***
    $position = strrpos($_SERVER['PHP_SELF'], '/');
    $uri_path = substr($_SERVER['PHP_SELF'], 0, $position) . '/';
}

// *** To be used to show links in several pages ***
include_once(__DIR__ . '/include/links.php');
$link_cls = new Link_cls($uri_path);

// *** Base controller ***
require __DIR__ . '/app/controller/Controller.php';
//$controllerObj = new Controller($dbh, $db_functions);

if ($page == 'address') {
    require __DIR__ . '/app/controller/addressController.php';
    $controllerObj = new AddressController($db_functions, $user);
    $data = $controllerObj->detail();
} elseif ($page == 'addresses') {
    require __DIR__ . '/app/controller/addressesController.php';
    $controllerObj = new AddressesController($dbh, $user, $tree_id);
    $data = $controllerObj->list();
} elseif ($page == 'ancestor_report') {
    require __DIR__ . '/app/controller/ancestor_reportController.php';
    $controllerObj = new Ancestor_reportController($dbh);
    $data = $controllerObj->list($tree_id);
} elseif ($page == 'ancestor_report_rtf') {
    require __DIR__ . '/app/controller/ancestor_reportController.php';
    $controllerObj = new Ancestor_reportController($dbh);
    $data = $controllerObj->list($tree_id);
} elseif ($page == 'ancestor_chart') {
    require __DIR__ . '/app/controller/ancestor_chartController.php';
    $controllerObj = new Ancestor_chartController($dbh, $db_functions);
    $data = $controllerObj->list($tree_id);
} elseif ($page == 'ancestor_sheet') {
    require __DIR__ . '/app/controller/ancestor_sheetController.php';
    $controllerObj = new Ancestor_sheetController($dbh, $db_functions);
    $data = $controllerObj->list($tree_id);
} elseif ($page == 'anniversary') {
    require __DIR__ . '/app/controller/anniversaryController.php';
    $controllerObj = new AnniversaryController();
    $data = $controllerObj->anniversary();
} elseif ($page == 'cms_pages') {
    require __DIR__ . '/app/controller/cms_pagesController.php';
    $controllerObj = new CMS_pagesController($dbh, $user);
    $data = $controllerObj->list();
} elseif ($page == 'cookies') {
    //
} elseif ($page == 'descendant_chart') {
    require __DIR__ . '/app/controller/descendant_chartController.php';
    $controllerObj = new Descendant_chartController();
    $data = $controllerObj->getFamily($dbh, $tree_id);
} elseif ($page == 'family_rtf') {
    //
} elseif ($page == 'family') {
    require __DIR__ . '/app/controller/familyController.php';
    $controllerObj = new FamilyController();
    $data = $controllerObj->getFamily($dbh, $tree_id);
} elseif ($page == 'fanchart') {
    require __DIR__ . '/app/controller/fanchartController.php';
    $controllerObj = new FanchartController();
    $data = $controllerObj->detail($dbh, $tree_id);
} elseif ($page == 'help') {
    //
} elseif ($page == 'hourglass') {
    require __DIR__ . '/app/controller/hourglassController.php';
    $controllerObj = new HourglassController();
    $data = $controllerObj->getHourglass($dbh, $tree_id);
} elseif ($page == 'latest_changes') {
    require __DIR__ . '/app/controller/latest_changesController.php';
    $controllerObj = new Latest_changesController($dbh);
    $data = $controllerObj->list($dbh, $tree_id);
} elseif ($page == 'list') {
    require __DIR__ . '/app/controller/listController.php';
    $controllerObj = new ListController();
    $list = $controllerObj->list_names($dbh, $tree_id, $user, $humo_option);
} elseif ($page == 'list_places_families') {
    require __DIR__ . '/app/controller/list_places_familiesController.php';
    $controllerObj = new ListPlacesFamiliesController();
    $data = $controllerObj->list_places_names($tree_id);
} elseif ($page == 'list_names') {
    require __DIR__ . '/app/controller/list_namesController.php';
    $controllerObj = new List_namesController();
    $data = $controllerObj->list_names($dbh, $tree_id, $user);
} elseif ($page == 'login') {
    //
} elseif ($page == 'mailform') {
    require __DIR__ . '/app/controller/mailformController.php';
    $controllerObj = new MailformController($db_functions);
    $mail_data = $controllerObj->get_mail_data($humo_option, $dataDb, $selected_language);
} elseif ($page == 'maps') {
    require __DIR__ . '/app/controller/mapsController.php';
    $controllerObj = new MapsController($db_functions);
    $maps = $controllerObj->detail($humo_option, $dbh, $tree_id, $tree_prefix_quoted);
} elseif ($page == 'photoalbum') {
    require __DIR__ . '/app/controller/photoalbumController.php';
    $controllerObj = new PhotoalbumController();
    $photoalbum = $controllerObj->detail($dbh);
} elseif ($page == 'register') {
    require __DIR__ . '/app/controller/registerController.php';
    $controllerObj = new RegisterController($db_functions);
    $register = $controllerObj->get_register_data($dbh, $dataDb, $humo_option);
} elseif ($page == 'relations') {
    require __DIR__ . '/app/controller/relationsController.php';
    $controllerObj = new RelationsController($dbh);
    $relation = $controllerObj->getRelations($db_functions, $person_cls);
} elseif ($page == 'reset_password') {
    require __DIR__ . '/app/controller/reset_passwordController.php';
    $controllerObj = new ResetpasswordController();
    $resetpassword = $controllerObj->detail($dbh, $humo_option);
} elseif ($page == 'outline_report') {
    require __DIR__ . '/app/controller/outline_reportController.php';
    $controllerObj = new Outline_reportController();
    $data = $controllerObj->getOutlineReport($dbh, $tree_id, $humo_option);
} elseif ($page == 'user_settings') {
    require __DIR__ . '/app/controller/user_settingsController.php';
    $controllerObj = new User_settingsController();
    $data = $controllerObj->user_settings($dbh, $dataDb, $humo_option, $user);
} elseif ($page == 'statistics') {
    require __DIR__ . '/app/controller/statisticsController.php';
    $controllerObj = new StatisticsController();
    $statistics = $controllerObj->detail($dbh, $tree_id);
} elseif ($page == 'sources') {
    require __DIR__ . '/app/controller/sourcesController.php';
    $controllerObj = new SourcesController($dbh);
    $data = $controllerObj->list($dbh, $tree_id, $user, $humo_option, $link_cls, $uri_path);
} elseif ($page == 'source') {
    require __DIR__ . '/app/controller/sourceController.php';
    $controllerObj = new SourceController($dbh, $db_functions, $tree_id); // Using Controller.
    // *** url_rewrite is disabled ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    $data = $controllerObj->source($id);
} elseif ($page == 'timeline') {
    require __DIR__ . '/app/controller/timelineController.php';
    $controllerObj = new TimelineController();
    // *** url_rewrite is disabled ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    $data = $controllerObj->getTimeline($db_functions, $id, $user, $dirmark1);
} elseif ($page == 'tree_index') {
    //  *** TODO: first improve difference between tree_index and mainindex ***
    //require __DIR__ . '/app/controller/tree_indexController.php';
    //$controllerObj = new Tree_indexController();
    //$tree_index["items"] = $controllerObj->get_items($dbh);
}

include_once(__DIR__ . "/views/layout.php");
