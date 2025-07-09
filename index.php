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

use Genealogy\Include\GeneralSettings;
use Genealogy\Include\ProcessLinks;
use Genealogy\Include\SafeTextDb;
use Genealogy\Include\ShowTreeText;
use Genealogy\Include\UserSettings;
use Genealogy\App\Controller\AddressController;
use Genealogy\App\Controller\AddressesController;
use Genealogy\App\Controller\AncestorChartController;
use Genealogy\App\Controller\AncestorReportController;
use Genealogy\App\Controller\AncestorSheetController;
use Genealogy\App\Controller\AnniversaryController;
use Genealogy\App\Controller\CmsPagesController;
use Genealogy\App\Controller\DescendantChartController;
use Genealogy\App\Controller\FamilyController;
use Genealogy\App\Controller\FanchartController;
use Genealogy\App\Controller\HourglassController;
use Genealogy\App\Controller\IndexController;
use Genealogy\App\Controller\LatestChangesController;
use Genealogy\App\Controller\ListController;
use Genealogy\App\Controller\ListNamesController;
use Genealogy\App\Controller\ListPlacesFamiliesController;
use Genealogy\App\Controller\MailformController;
use Genealogy\App\Controller\MapsController;
use Genealogy\App\Controller\OutlineReportController;
use Genealogy\App\Controller\PhotoalbumController;
use Genealogy\App\Controller\RegisterController;
use Genealogy\App\Controller\RelationsController;
use Genealogy\App\Controller\ResetPasswordController;
use Genealogy\App\Controller\SourceController;
use Genealogy\App\Controller\SourcesController;
use Genealogy\App\Controller\StatisticsController;
use Genealogy\App\Controller\TimelineController;
use Genealogy\App\Controller\UserSettingsController;

// *** Disabled 18-01-2023 ***
//ini_set('url_rewriter.tags','');

//session_cache_limiter('private, must-revalidate'); //tb edit
session_start();
// *** Regenerate session id regularly to prevent session hacking ***
//session_regenerate_id();

// *** Autoload composer classes ***
require __DIR__ . '/vendor/autoload.php';

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

$safeTextDb = new SafeTextDb();

$generalSettings = new GeneralSettings();
$humo_option = $generalSettings->get_humo_option($dbh);

$userSettings = new UserSettings();
$user = $userSettings->get_user_settings($dbh);

$showTreeText = new ShowTreeText();


// *** Added dec. 2024 ***
$controllerObj = new IndexController();
$index = $controllerObj->detail($dbh, $humo_option, $user);

// TODO dec. 2024 for now: use old variable names.
$db_functions = $index['db_functions'];
$bot_visit = $index['bot_visit'];
$language_file = $index['language_file']; // Array including all languages files.
$language = $index['language']; // $language = array.
$selected_language = $index['selected_language'];

// Needed for mail script.
$dataDb = $db_functions->get_tree($index['tree_id']);

// *** Process LTR and RTL variables ***
$dirmark1 = $index['dirmark1'];  //ltr marker
$dirmark2 = $index['dirmark2'];  //rtl marker
$rtlmarker = $index['rtlmarker'];
$alignmarker = $index['alignmarker'];

// *** New routing script sept. 2023. Search route, return match or not found ***
$page = $index['page'];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id']; // TODO: check if this is needed.
} elseif (isset($index['id'])) {
    $id = $index['id']; // TODO: check if this is needed.
} else {
    $id = '';   // Default value, if no id is set.
}

$tree_id = $index['tree_id'];
$tree_prefix_quoted = $index['tree_prefix_quoted']; // Still in use for maps.

$db_functions->set_tree_id($index['tree_id']);

// *** If a HuMo-gen upgrade is done, automatically update language files ***
if ($humo_option['death_char'] == "y") {
    // User wants infinity instead of cross -> check if the language files comply
    $str = file_get_contents("languages/en/en.po");
    if (strpos($str, 'msgstr "&#134;"') || strpos($str, 'msgstr "&dagger;"')) {
        // The cross is used (probably new upgrade) so this has to be changed to infinity
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
$processLinks = new ProcessLinks($uri_path);

/**
 * General config array. May 2025: added baseModel.php.
 * 
 * In controller:
 * private $config;
 *   public function __construct($config)
 *   {
 *       $this->config = $config;
 *   }
 *
 * In model: class listNamesModel extends BaseModel.
 * Then use: $this->dbh, $this->db_functions, $this->tree_id, $this->user, $this->humo_option.
 */
include_once(__DIR__ . "/include/config.php");

if ($index['page'] == 'address') {
    $controllerObj = new AddressController($config);
    $data = $controllerObj->detail();
} elseif ($index['page'] == 'addresses') {
    $controllerObj = new AddressesController($config);
    $data = $controllerObj->list();
} elseif ($index['page'] == 'ancestor_report') {
    $controllerObj = new AncestorReportController($config);
    $data = $controllerObj->list($id);
} elseif ($index['page'] == 'ancestor_report_pdf') {
    //$controllerObj = new AncestorReportController($config);
    //$data = $controllerObj->list($id);
    include_once(__DIR__ . "/views/ancestor_report_pdf.php");
    exit; // Skip layout.php
} elseif ($index['page'] == 'ancestor_report_rtf') {
    $controllerObj = new AncestorReportController($config);
    $data = $controllerObj->list($id);
} elseif ($index['page'] == 'ancestor_chart') {
    $controllerObj = new AncestorChartController($config);
    $data = $controllerObj->list($id);
} elseif ($index['page'] == 'ancestor_sheet_pdf') {
    //$controllerObj = new AncestorSheetController($config);
    //$data = $controllerObj->list($id);
    include_once(__DIR__ . "/views/ancestor_sheet_pdf.php");
    exit; // Skip layout.php
} elseif ($index['page'] == 'ancestor_sheet') {
    $controllerObj = new AncestorSheetController($config);
    $data = $controllerObj->list($id);
} elseif ($index['page'] == 'anniversary') {
    $controllerObj = new AnniversaryController();
    $data = $controllerObj->anniversary();
} elseif ($index['page'] == 'cms_pages') {
    $controllerObj = new CmsPagesController($config);
    $data = $controllerObj->list();
} elseif ($index['page'] == 'cookies') {
    //
} elseif ($index['page'] == 'descendant_chart') {
    $controllerObj = new DescendantChartController($config);
    $data = $controllerObj->getFamily();
} elseif ($index['page'] == 'family_pdf') {
    //$controllerObj = new AncestorReportController($config);
    //$data = $controllerObj->list($id);
    include_once(__DIR__ . "/views/family_pdf.php");
    exit; // Skip layout.php
} elseif ($index['page'] == 'family_rtf') {
    //
} elseif ($index['page'] == 'family') {
    $controllerObj = new FamilyController($config);
    $data = $controllerObj->getFamily();
} elseif ($index['page'] == 'fanchart') {
    // TODO refactor
    require_once(__DIR__ . "/include/fanchart/persian_log2vis.php");

    $controllerObj = new FanchartController($config);
    $data = $controllerObj->detail($id);
} elseif ($index['page'] == 'help') {
    //
} elseif ($index['page'] == 'hourglass') {
    $controllerObj = new HourglassController($config);
    $data = $controllerObj->getHourglass();
} elseif ($index['page'] == 'latest_changes') {
    $controllerObj = new LatestChangesController($config);
    $data = $controllerObj->list();
} elseif ($index['page'] == 'list') {
    $controllerObj = new ListController($config);
    $list = $controllerObj->list_names();
} elseif ($index['page'] == 'list_places_families') {
    $controllerObj = new ListPlacesFamiliesController($config);
    $data = $controllerObj->list_places_names();
} elseif ($index['page'] == 'list_names') {
    $controllerObj = new ListNamesController($config);
    $last_name = '';
    if (isset($index['last_name'])) {
        $last_name = $index['last_name'];
    }
    $list_names = $controllerObj->list_names($last_name);
} elseif ($index['page'] == 'login') {
    //
} elseif ($index['page'] == 'mailform') {
    $controllerObj = new MailformController($config);
    $mail_data = $controllerObj->get_mail_data($dataDb, $selected_language);
} elseif ($index['page'] == 'maps') {
    $controllerObj = new MapsController($config);
    $maps = $controllerObj->detail($tree_prefix_quoted);
} elseif ($index['page'] == 'photoalbum') {
    $controllerObj = new PhotoalbumController($config);
    $photoalbum = $controllerObj->detail($selected_language);
} elseif ($index['page'] == 'register') {
    $controllerObj = new RegisterController($config);
    $register = $controllerObj->get_register_data($dataDb);
} elseif ($index['page'] == 'relations') {
    $controllerObj = new RelationsController($config);
    $relation = $controllerObj->getRelations($selected_language);
} elseif ($index['page'] == 'reset_password') {
    $controllerObj = new ResetPasswordController($config);
    $resetpassword = $controllerObj->detail();
} elseif ($index['page'] == 'outline_report_pdf') {
    //$controllerObj = new OutlineReportController($config);
    //$data = $controllerObj->getOutlineReport();
    include_once(__DIR__ . "/views/outline_report_pdf.php");
    exit; // Skip layout.php
} elseif ($index['page'] == 'outline_report') {
    $controllerObj = new OutlineReportController($config);
    $data = $controllerObj->getOutlineReport();
} elseif ($index['page'] == 'user_settings') {
    // TODO refactor
    include_once(__DIR__ . "/include/2fa_authentication/authenticator.php");

    $controllerObj = new UserSettingsController($config);
    $data = $controllerObj->user_settings($dataDb);
} elseif ($index['page'] == 'show_media_file') {
    // *** Show media file using secured folder ***
    include_once(__DIR__ . "/views/show_media_file.php");
    exit; // *** Skip layout.php ***
} elseif ($index['page'] == 'statistics') {
    $controllerObj = new StatisticsController($config);
    $statistics = $controllerObj->detail();
} elseif ($index['page'] == 'sources') {
    $controllerObj = new SourcesController($config);
    $data = $controllerObj->list();
} elseif ($index['page'] == 'source') {
    $controllerObj = new SourceController($config);

    // *** url_rewrite is disabled ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    $data = $controllerObj->source($id);
} elseif ($index['page'] == 'timeline') {
    $controllerObj = new TimelineController($config);
    // *** url_rewrite is disabled ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    $data = $controllerObj->getTimeline($id, $dirmark1);
} elseif ($index['page'] == 'tree_index') {
    //  *** TODO: first improve difference between tree_index and mainindex ***
    //$controllerObj = new TreeIndexController($config);
    //$tree_index["items"] = $controllerObj->get_items();
}

/*
// Script to prevent too many requests.
if (@$_SESSION['last_request'] > time() - 1) {
?>
    <div class="centered"><strong>TOO MANY REQUESTS</strong>
        <hr>
        <p><em>You are allowed 1 request every second.</em></p>
    </div>
<?php
    exit;
}
$_SESSION['last_request'] = time();
*/

// *** 301 code: generate 301 redirect ***
//if ($index['page301'] != '') {
//    header("HTTP/1.1 301 Moved Permanently");
//    header("Location: " . $index['page301']);
//}

$error_page = '';
//if ($index['page403']) {
//    $error_page = __('403 Forbidden');
//}

// TODO: this is disabled, because it blockes favorite pages.
//if ($index['page404']) {
//    $error_page = __('404 Not Found');
//}

//else {
//    $error_page = __('410 Gone');
//}
//if ($index['page429']) {
//    $error_page = __('429 Too Many Requests');
//}

// *** If page isn't valid, show 404 Not Found page ***
if ($error_page) {
    //if ($index['page403']) {
    //    header("HTTP/1.1 403 Forbidden");
    //}
    if ($index['page404']) {
        header("HTTP/1.1 404 Not Found");
    }
    //else{
    //    header("HTTP/1.1 410 Gone");
    //}
    //else {
    //    header("HTTP/1.1 429 Too Many Requests");
    //}
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <!-- Bootstrap: rescale pages for mobile devices -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet"> -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <title><?= $error_page; ?></title>
    </head>

    <body>
        <div class="row mt-5"></div>

        <div class="row mt-5">
            <div class="col-md-3"></div>
            <div class="col bg-primary-subtle border border-primary-subtle rounded-3 p-4">
                <h1 class="text-center"><?= $error_page; ?></h1>
            </div>
            <div class="col-md-3"></div>
        </div>
    </body>

    </html>

<?php
    exit();
}

include_once(__DIR__ . "/views/layout.php");
