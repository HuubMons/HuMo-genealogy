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

// *** Added dec. 2024 ***
require __DIR__ . '/app/controller/indexController.php';
$controllerObj = new IndexController();
$index = $controllerObj->detail($dbh, $humo_option);

// TODO dec. 2024 for now: use old variable names.
$db_functions = $index['db_functions'];
$visitor_ip = $index['visitor_ip'];
$person_cls = $index['person_cls'];
$bot_visit = $index['bot_visit'];
$language_file = $index['language_file']; // Array including all languages files.
$language = $index['language']; // $language = array.
$selected_language = $index['selected_language'];

// *** Process LTR and RTL variables ***
$dirmark1 = $index['dirmark1'];  //ltr marker
$dirmark2 = $index['dirmark2'];  //rtl marker
$rtlmarker = $index['rtlmarker'];
$alignmarker = $index['alignmarker'];

// *** New routing script sept. 2023. Search route, return match or not found ***
$page = $index['page'];
if (isset($index['last_name'])) {
    $last_name = $index['last_name'];
}
if (isset($index['id'])) {
    $id = $index['id'];
}



// *** Family tree choice. Example: database=humo2_ (backwards compatible, now we use tree_id) ***
// Test link: http://127.0.0.1/humo-genealogy/gezin.php?database=humo2_&id=F59&hoofdpersoon=I151
$database = '';
if (isset($_GET["database"])) {
    $database = $_GET["database"];
}
if (isset($_POST["database"])) {
    $database = $_POST["database"];
}

// *** For example: database=humo2_ (backwards compatible, now we use tree_id) ***
if (isset($database) && is_string($database) && $database) {
    // *** Check if family tree really exists ***
    $dataDb = $db_functions->get_tree($database);
    if ($dataDb && $database == $dataDb->tree_prefix) {
        $_SESSION['tree_prefix'] = $database;
    }
}





// *** Use family tree number in the url: database=humo_2 changed into: tree_id=1 ***
if (isset($_GET["tree_id"])) {
    $index['select_tree_id'] = $_GET["tree_id"];
}
if (isset($_POST["tree_id"])) {
    $index['select_tree_id'] = $_POST["tree_id"];
}
if (isset($index['select_tree_id']) && is_numeric($index['select_tree_id']) && $index['select_tree_id']) {
    // *** Check if family tree really exists ***
    $dataDb = $db_functions->get_tree($index['select_tree_id']);
    if ($dataDb && $index['select_tree_id'] == $dataDb->tree_id) {
        $_SESSION['tree_prefix'] = $dataDb->tree_prefix;
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
if ($humo_option["url_rewrite"] == "j" && $index['tmp_path']) {
    // *** url_rewrite. 26 jan. 2024 Ron: Added proxy check ***
    //if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
        $uri_path = 'https://' . $_SERVER['SERVER_NAME'] . $index['tmp_path'];
    } else {
        $uri_path = 'http://' . $_SERVER['SERVER_NAME'] . $index['tmp_path'];
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
    $photoalbum = $controllerObj->detail($dbh, $tree_id, $db_functions);
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
