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

//session_cache_limiter('private, must-revalidate'); //tb edit
session_start();
// *** Regenerate session id regularly to prevent session hacking ***
// TODO: if needed, only use this after user login.
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

$safeTextDb = new Genealogy\Include\SafeTextDb();

$generalSettings = new Genealogy\Include\GeneralSettings();
$humo_option = $generalSettings->get_humo_option($dbh);

$userSettings = new Genealogy\Include\UserSettings();
$user = $userSettings->get_user_settings($dbh);

$showTreeText = new Genealogy\Include\ShowTreeText();


/*
// *** TEST use Symfony Translation component (to replace very old gettext.php script) ***
// TODO Check code. Slows down website.
$language_cls = new Genealogy\Languages\LanguageCls();
$language_file = $language_cls->get_languages();
$selected_language = $language_cls->get_selected_language($humo_option);
$language = $language_cls->get_language_data($selected_language);

$translator = new Symfony\Component\Translation\Translator($selected_language);
$translator->addLoader('mo', new Symfony\Component\Translation\Loader\MoFileLoader());
$mofile = __DIR__ . '/languages/' . $selected_language . '/' . $selected_language . '.mo';
$translator->addResource('mo', $mofile, $selected_language);
$GLOBALS['translator'] = $translator;
function __($text)
{
    //global $translator;
    //return $translator->trans($text);
    return $GLOBALS['translator']->trans($text);
}
*/

/*
// TEST: using gettext/gettext.
// TODO: check code. Code doesnt work.
$language_cls = new Genealogy\Languages\LanguageCls();
$language_file = $language_cls->get_languages();
$selected_language = $language_cls->get_selected_language($humo_option);
$language = $language_cls->get_language_data($selected_language);
// Set your language code and path
$mofile = __DIR__ . "/languages/$selected_language/$selected_language.mo";
// Load translations from the .mo file
$translations = Gettext\Translations::fromMoFile($mofile);
// Create and register the translator globally
$translator = new Gettext\Translator();
$translator->loadTranslations($translations);
$translator->register(); // This enables __(), _n(), etc.
*/


// *** Added dec. 2024 ***
$controllerObj = new Genealogy\App\Controller\IndexController();
$index = $controllerObj->detail($dbh, $humo_option, $user);

// TODO dec. 2024 for now: use old variable names.
$db_functions = $index['db_functions'];
$language_file = $index['language_file']; // Array including all languages files.
$language = $index['language']; // $language = array.
$selected_language = $index['selected_language'];


// Needed for mail script. Jul. 2025 new variable $selectedFamilyTree.
$selectedFamilyTree = $db_functions->get_tree($index['tree_id']);

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
$processLinks = new Genealogy\Include\ProcessLinks($uri_path);

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
    $controllerObj = new Genealogy\App\Controller\AddressController($config);
    $data = $controllerObj->detail();
} elseif ($index['page'] == 'addresses') {
    $controllerObj = new Genealogy\App\Controller\AddressesController($config);
    $data = $controllerObj->list();
} elseif ($index['page'] == 'ancestor_report') {
    $controllerObj = new Genealogy\App\Controller\AncestorReportController($config);
    $data = $controllerObj->list($id);
} elseif ($index['page'] == 'ancestor_report_pdf') {
    $controllerObj = new Genealogy\App\Controller\AncestorReportPdfController($config);
    $data = $controllerObj->list($id);
    include_once(__DIR__ . "/views/ancestor_report_pdf.php");
    exit; // Skip layout.php
} elseif ($index['page'] == 'ancestor_report_rtf') {
    $controllerObj = new Genealogy\App\Controller\AncestorReportController($config);
    $data = $controllerObj->list($id);
} elseif ($index['page'] == 'ancestor_chart') {
    $controllerObj = new Genealogy\App\Controller\AncestorChartController($config);
    $data = $controllerObj->list($id);
} elseif ($index['page'] == 'ancestor_sheet_pdf') {
    //$controllerObj = new Genealogy\App\Controller\AncestorSheetController($config);
    //$data = $controllerObj->list($id);
    include_once(__DIR__ . "/views/ancestor_sheet_pdf.php");
    exit; // Skip layout.php
} elseif ($index['page'] == 'ancestor_sheet') {
    $controllerObj = new Genealogy\App\Controller\AncestorSheetController($config);
    $data = $controllerObj->list($id);
} elseif ($index['page'] == 'anniversary') {
    $controllerObj = new Genealogy\App\Controller\AnniversaryController();
    $data = $controllerObj->anniversary();
} elseif ($index['page'] == 'cms_pages') {
    $controllerObj = new Genealogy\App\Controller\CmsPagesController($config);
    $data = $controllerObj->list();
} elseif ($index['page'] == 'cookies') {
    //
} elseif ($index['page'] == 'descendant_chart') {
    $controllerObj = new Genealogy\App\Controller\DescendantChartController($config);
    $data = $controllerObj->getFamily();
} elseif ($index['page'] == 'family_pdf') {
    //$controllerObj = new AncestorReportController($config);
    //$data = $controllerObj->list($id);
    include_once(__DIR__ . "/views/family_pdf.php");
    exit; // Skip layout.php
} elseif ($index['page'] == 'family_rtf') {
    //
} elseif ($index['page'] == 'family') {
    $controllerObj = new Genealogy\App\Controller\FamilyController($config);
    $data = $controllerObj->getFamily();
} elseif ($index['page'] == 'fanchart') {
    // TODO refactor
    require_once(__DIR__ . "/include/fanchart/persian_log2vis.php");

    $controllerObj = new Genealogy\App\Controller\FanchartController($config);
    $data = $controllerObj->detail($id);
} elseif ($index['page'] == 'help') {
    //
} elseif ($index['page'] == 'hourglass') {
    $controllerObj = new Genealogy\App\Controller\HourglassController($config);
    $data = $controllerObj->getHourglass();
} elseif ($index['page'] == 'latest_changes') {
    $controllerObj = new Genealogy\App\Controller\LatestChangesController($config);
    $data = $controllerObj->list();
} elseif ($index['page'] == 'list') {
    $controllerObj = new Genealogy\App\Controller\ListController($config);
    $list = $controllerObj->list_names();
} elseif ($index['page'] == 'list_places_families') {
    $controllerObj = new Genealogy\App\Controller\ListPlacesFamiliesController($config);
    $data = $controllerObj->list_places_names();
} elseif ($index['page'] == 'list_names') {
    $controllerObj = new Genealogy\App\Controller\ListNamesController($config);
    $last_name = '';
    if (isset($index['last_name'])) {
        $last_name = $index['last_name'];
    }
    $list_names = $controllerObj->list_names($last_name);
} elseif ($index['page'] == 'login') {
    //
} elseif ($index['page'] == 'mailform') {
    $controllerObj = new Genealogy\App\Controller\MailformController($config);
    $mail_data = $controllerObj->get_mail_data($selected_language);
} elseif ($index['page'] == 'maps') {
    $controllerObj = new Genealogy\App\Controller\MapsController($config);
    $maps = $controllerObj->detail($tree_prefix_quoted);
} elseif ($index['page'] == 'photoalbum') {
    $controllerObj = new Genealogy\App\Controller\PhotoalbumController($config);
    $photoalbum = $controllerObj->detail($selected_language);
} elseif ($index['page'] == 'register') {
    $controllerObj = new Genealogy\App\Controller\RegisterController($config);
    $register = $controllerObj->get_register_data();
} elseif ($index['page'] == 'relations') {
    $controllerObj = new Genealogy\App\Controller\RelationsController($config);
    $relation = $controllerObj->getRelations($selected_language);
} elseif ($index['page'] == 'reset_password') {
    $controllerObj = new Genealogy\App\Controller\ResetPasswordController($config);
    $resetpassword = $controllerObj->detail();
} elseif ($index['page'] == 'outline_report_pdf') {
    //$controllerObj = new Genealogy\App\Controller\OutlineReportController($config);
    //$data = $controllerObj->getOutlineReport();
    include_once(__DIR__ . "/views/outline_report_pdf.php");
    exit; // Skip layout.php
} elseif ($index['page'] == 'outline_report') {
    $controllerObj = new Genealogy\App\Controller\OutlineReportController($config);
    $data = $controllerObj->getOutlineReport();
} elseif ($index['page'] == 'user_settings') {
    // TODO refactor
    include_once(__DIR__ . "/include/2fa_authentication/authenticator.php");

    $controllerObj = new Genealogy\App\Controller\UserSettingsController($config);
    $data = $controllerObj->user_settings();
} elseif ($index['page'] == 'show_media_file') {
    // *** Show media file using secured folder ***
    include_once(__DIR__ . "/views/show_media_file.php");
    exit; // *** Skip layout.php ***
} elseif ($index['page'] == 'statistics') {
    $controllerObj = new Genealogy\App\Controller\StatisticsController($config);
    $statistics = $controllerObj->detail();
} elseif ($index['page'] == 'sources') {
    $controllerObj = new Genealogy\App\Controller\SourcesController($config);
    $data = $controllerObj->list();
} elseif ($index['page'] == 'source') {
    $controllerObj = new Genealogy\App\Controller\SourceController($config);

    // *** url_rewrite is disabled ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    $data = $controllerObj->source($id);
} elseif ($index['page'] == 'timeline') {
    $controllerObj = new Genealogy\App\Controller\TimelineController($config);
    // *** url_rewrite is disabled ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    $data = $controllerObj->getTimeline($id);
} elseif ($index['page'] == 'tree_index') {
    //  *** TODO: first improve difference between tree_index and mainindex ***
    //$controllerObj = new Genealogy\App\Controller\TreeIndexController($config);
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
