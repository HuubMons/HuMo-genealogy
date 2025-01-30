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
 * Copyright (C) 2008-2025 Huub Mons,
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

/**
 *  Dec. 2024: Added autoload.
 *  Name of class = SomethingClass
 *  Name of script: somethingClass.php ***
 */
// TODO add autoload in gendex.php, sitemap.php, editor_ajax.php, namesearch.php, layout_pdf.php.
function custom_autoload($class_name)
{
    // Examples of autoload files:
    // app/model/ All scripts are autoloading.
    // app/model/adresModel.php

    // controller/ All scripts are autoloading.
    // controller/addressController.php

    // include/dbFunctions.php
    // include/marriage_cls
    // include/personCls.php
    // include/calculateDates.php
    // include/processLinks.php
    // include/validateDate.php

    // languages/languageCls.php

    $include = array(
        'CalculateDates',
        'DbFunctions',
        'MarriageCls',
        'PersonCls',
        'ProcessLinks',
        'ValidateDate',
        'Config'
    );

    if ($class_name == 'LanguageCls') {
        require __DIR__ . '/languages/languageCls.php';
    } elseif (substr($class_name, -10) == 'Controller') {
        require __DIR__ . '/app/controller/' . lcfirst($class_name) . '.php';
    } elseif (substr($class_name, -5) == 'Model') {
        require __DIR__ . '/app/model/' . lcfirst($class_name) . '.php';
    } elseif (in_array($class_name, $include)) {
        require __DIR__ . '/include/' . lcfirst($class_name) . '.php';
    }
}
spl_autoload_register('custom_autoload');


// TODO move to model script (should be processed before setttings_user).
if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_group_id']);
    unset($_SESSION['tree_prefix']);
    session_destroy();
}

// TODO refactor/ check scripts for autoload.
include_once(__DIR__ . "/include/db_login.php"); // Connect to database
include_once(__DIR__ . "/include/show_tree_text.php");
include_once(__DIR__ . "/include/safe.php");

include_once(__DIR__ . "/include/generalSettings.php");
$GeneralSettings = new GeneralSettings();
$user = $GeneralSettings->get_user_settings($dbh);
$humo_option = $GeneralSettings->get_humo_option($dbh);

include_once(__DIR__ . "/include/get_visitor_ip.php"); // Statistics and option to block certain IP addresses.

include_once(__DIR__ . "/include/timezone.php");
//include(__DIR__ . "/languages/languageCls.php");

include_once(__DIR__ . '/app/routing/router.php'); // Page routing.



// *** Added dec. 2024 ***
$controllerObj = new IndexController();
$index = $controllerObj->detail($dbh, $humo_option, $user);

// TODO dec. 2024 for now: use old variable names.
$db_functions = $index['db_functions'];
$visitor_ip = $index['visitor_ip'];
$person_cls = $index['person_cls'];
$bot_visit = $index['bot_visit'];
$language_file = $index['language_file']; // Array including all languages files.
$language = $index['language']; // $language = array.
$selected_language = $index['selected_language'];

// Needed for mail script.
if (isset($_SESSION['tree_prefix'])) {
    $dataDb = $db_functions->get_tree($_SESSION['tree_prefix']);
}

// *** Process LTR and RTL variables ***
$dirmark1 = $index['dirmark1'];  //ltr marker
$dirmark2 = $index['dirmark2'];  //rtl marker
$rtlmarker = $index['rtlmarker'];
$alignmarker = $index['alignmarker'];

// *** New routing script sept. 2023. Search route, return match or not found ***
$page = $index['page'];

if (isset($index['id'])) {
    $id = $index['id'];
}

$tree_id = $index['tree_id'];
$tree_prefix_quoted = $index['tree_prefix_quoted'];



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
$link_cls = new ProcessLinks($uri_path);

/**
 * General config array.
 * In function: use $this->config['dbh'], $this->config['db_functions'], etc, or use:
 * $dbh = $this->config['dbh'];
 * $db_functions = $this->config['db_functions'];
 * $tree_id = $this->config['tree_id'];
 * $user = $this->config['user'];
 * $humo_option = $this->config['humo_option'];
 */
$config = array(
    "dbh" => $dbh,
    "db_functions" => $db_functions,
    "tree_id" => $tree_id,
    "user" => $user,
    "humo_option" => $humo_option
);
// *** General config class. Usage: $controllerObj = new AddressController($config); ***
// *** Allready tested in sourceController.php & photoalbumController.php ***
//$config = new Config($dbh, $db_functions, $tree_id, $user, $humo_option);

if ($page == 'address') {
    // TODO refactor
    include_once(__DIR__ . "/include/show_sources.php");
    include_once(__DIR__ . "/include/showMedia.php");

    $controllerObj = new AddressController($db_functions, $user);
    $data = $controllerObj->detail();
} elseif ($page == 'addresses') {
    $controllerObj = new AddressesController($dbh, $user, $tree_id);
    $data = $controllerObj->list();
} elseif ($page == 'ancestor_report') {
    $controllerObj = new AncestorReportController($dbh);
    $data = $controllerObj->list($tree_id);
} elseif ($page == 'ancestor_report_rtf') {
    $controllerObj = new AncestorReportController($dbh);
    $data = $controllerObj->list($tree_id);
} elseif ($page == 'ancestor_chart') {
    $controllerObj = new AncestorChartController($dbh, $db_functions);
    $data = $controllerObj->list($tree_id);
} elseif ($page == 'ancestor_sheet') {
    $controllerObj = new AncestorSheetController($dbh, $db_functions);
    $data = $controllerObj->list($tree_id);
} elseif ($page == 'anniversary') {
    //TODO refactor
    include_once(__DIR__ . "/include/language_date.php");

    $controllerObj = new AnniversaryController();
    $data = $controllerObj->anniversary();
} elseif ($page == 'cms_pages') {
    $controllerObj = new CmsPagesController($dbh, $user);
    $data = $controllerObj->list();
} elseif ($page == 'cookies') {
    //
} elseif ($page == 'descendant_chart') {
    $controllerObj = new DescendantChartController();
    $data = $controllerObj->getFamily($dbh, $tree_id);
} elseif ($page == 'family_rtf') {
    //
} elseif ($page == 'family') {
    $controllerObj = new FamilyController();
    $data = $controllerObj->getFamily($dbh, $tree_id);
} elseif ($page == 'fanchart') {
    // TODO refactor
    require_once(__DIR__ . "/include/fanchart/persian_log2vis.php");

    $controllerObj = new FanchartController();
    $data = $controllerObj->detail($dbh, $tree_id);
} elseif ($page == 'help') {
    //
} elseif ($page == 'hourglass') {
    $controllerObj = new HourglassController();
    $data = $controllerObj->getHourglass($dbh, $tree_id);
} elseif ($page == 'latest_changes') {
    // TODO refactor
    include_once(__DIR__ . "/include/language_date.php");

    $controllerObj = new LatestChangesController($dbh);
    $data = $controllerObj->list($dbh, $tree_id);
} elseif ($page == 'list') {
    // TODO refactor
    include_once(__DIR__ . "/include/language_date.php");
    include_once(__DIR__ . "/include/date_place.php");

    $controllerObj = new ListController();
    $list = $controllerObj->list_names($dbh, $tree_id, $user, $humo_option);
} elseif ($page == 'list_places_families') {
    // TODO refactor
    include_once(__DIR__ . "/include/language_date.php");
    include_once(__DIR__ . "/include/date_place.php");

    $controllerObj = new ListPlacesFamiliesController();
    $data = $controllerObj->list_places_names($tree_id);
} elseif ($page == 'list_names') {
    $controllerObj = new ListNamesController($config);
    $last_name = '';
    if (isset($index['last_name'])) {
        $last_name = $index['last_name'];
    }
    $list_names = $controllerObj->list_names($last_name, $uri_path);
} elseif ($page == 'login') {
    //
} elseif ($page == 'mailform') {
    $controllerObj = new MailformController($db_functions);
    $mail_data = $controllerObj->get_mail_data($humo_option, $dataDb, $selected_language);
} elseif ($page == 'maps') {
    // TODO refactor
    include_once(__DIR__ . "/include/language_date.php");
    include_once(__DIR__ . "/include/date_place.php");
    include_once(__DIR__ . "/include/ancestors_descendants.php");

    $controllerObj = new MapsController($db_functions);
    $maps = $controllerObj->detail($humo_option, $dbh, $tree_id, $tree_prefix_quoted);
} elseif ($page == 'photoalbum') {
    // TODO refactor
    include_once(__DIR__ . "/include/language_date.php");
    include_once(__DIR__ . "/include/date_place.php");
    include_once(__DIR__ . "/include/showMedia.php");
    //include_once(__DIR__ . "/admin/include/media_inc.php");

    $controllerObj = new PhotoalbumController($config);
    $photoalbum = $controllerObj->detail($selected_language, $uri_path, $link_cls);
} elseif ($page == 'register') {
    $controllerObj = new RegisterController($db_functions);
    $register = $controllerObj->get_register_data($dbh, $dataDb, $humo_option);
} elseif ($page == 'relations') {
    // TODO refactor
    include_once(__DIR__ . "/include/language_date.php");
    include_once(__DIR__ . "/include/date_place.php");

    $controllerObj = new RelationsController($dbh);
    $relation = $controllerObj->getRelations($db_functions, $person_cls, $link_cls, $uri_path, $tree_id, $selected_language);
} elseif ($page == 'reset_password') {
    $controllerObj = new ResetPasswordController();
    $resetpassword = $controllerObj->detail($dbh, $humo_option);
} elseif ($page == 'outline_report') {
    $controllerObj = new OutlineReportController();
    $data = $controllerObj->getOutlineReport($dbh, $tree_id, $humo_option);
} elseif ($page == 'user_settings') {
    // TODO refactor
    include_once(__DIR__ . "/include/2fa_authentication/authenticator.php");
    //if (isset($_POST['update_settings'])) include_once(__DIR__ . '/include/mail.php');

    $controllerObj = new UserSettingsController();
    $data = $controllerObj->user_settings($dbh, $dataDb, $humo_option, $user);
} elseif ($page == 'show_media_file') {
    // *** Show media file using secured folder ***
    // *** Skip layout.php ***
    include_once(__DIR__ . "/views/show_media_file.php");
    exit;
} elseif ($page == 'statistics') {
    // TODO refactor
    include_once(__DIR__ . "/include/language_date.php");
    include_once(__DIR__ . "/include/date_place.php");

    $controllerObj = new StatisticsController();
    $statistics = $controllerObj->detail($dbh, $tree_id);
} elseif ($page == 'sources') {
    // TODO refactor
    include_once(__DIR__ . "/include/language_date.php");
    include_once(__DIR__ . "/include/date_place.php");

    $controllerObj = new SourcesController($dbh);
    $data = $controllerObj->list($dbh, $tree_id, $user, $humo_option, $link_cls, $uri_path);
} elseif ($page == 'source') {
    // TODO refactor
    include_once(__DIR__ . "/include/date_place.php");
    include_once(__DIR__ . "/include/process_text.php");
    include_once(__DIR__ . "/include/showMedia.php");
    //include_once(__DIR__ . "/include/show_sources.php");
    include_once(__DIR__ . "/include/language_date.php");

    $controllerObj = new SourceController($config);

    // *** url_rewrite is disabled ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    $data = $controllerObj->source($id);
} elseif ($page == 'timeline') {
    // TODO refactor
    require_once(__DIR__ . "/include/language_date.php");

    $controllerObj = new TimelineController();
    // *** url_rewrite is disabled ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    $data = $controllerObj->getTimeline($db_functions, $id, $user, $dirmark1);
} elseif ($page == 'tree_index') {
    //  *** TODO: first improve difference between tree_index and mainindex ***
    //$controllerObj = new TreeIndexController();
    //$tree_index["items"] = $controllerObj->get_items($dbh);
}

include_once(__DIR__ . "/views/layout.php");
