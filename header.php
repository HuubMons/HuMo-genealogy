<?php
/*
 * Check if HuMo-genealogy is in a CMS system
 *	Names:
 *		- CMS names used for now are 'Joomla' and 'CMSMS'.
 *	Usage:
 *		- Code for all CMS: if (CMS_SPECIFIC) {}
 *		- Code for one CMS: if (CMS_SPECIFIC == 'Joomla') {}
 *		- Code NOT for CMS: if (!CMS_SPECIFIC) {}
*/
if (!defined("CMS_SPECIFIC")) define("CMS_SPECIFIC", false);

// *** When run from CMS, the path to the map (that contains this file) should be given ***
if (!defined("CMS_ROOTPATH")) define("CMS_ROOTPATH", "");

if (!defined("CMS_ROOTPATH_ADMIN")) define("CMS_ROOTPATH_ADMIN", "admin/");

// *** Disabled 18-01-2023 ***
//ini_set('url_rewriter.tags','');

if (!CMS_SPECIFIC) {
    session_cache_limiter('private, must-revalidate'); //tb edit
    session_start();
    // *** Regenerate session id regularly to prevent session hacking ***
    session_regenerate_id();
}

if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_group_id']);
    unset($_SESSION['tree_prefix']);
    session_destroy();
}

include_once(CMS_ROOTPATH . "include/db_login.php"); //Inloggen database.
include_once(CMS_ROOTPATH . 'include/show_tree_text.php');
include_once(CMS_ROOTPATH . "include/db_functions_cls.php");
$db_functions = new db_functions;

// *** Show a message at NEW installation. Use "try" for PHP 8.1. ***
try {
    $result = $dbh->query("SELECT COUNT(*) FROM humo_settings");
} catch (PDOException $e) {
    echo "Installation of HuMo-genealogy is not yet completed.<br>Installatie van HuMo-genealogy is nog niet voltooid.";
    exit();
}

include_once(CMS_ROOTPATH . "include/safe.php");
include_once(CMS_ROOTPATH . "include/settings_global.php"); //Variables
include_once(CMS_ROOTPATH . "include/settings_user.php"); // USER variables

// *** Debug HuMo-genealogy`front pages ***
if ($humo_option["debug_front_pages"] == 'y') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// *** Check if visitor is allowed ***
if (!$db_functions->check_visitor($_SERVER['REMOTE_ADDR'], 'partial')) {
    echo 'Access to website is blocked.';
    exit;
}

// *** Set timezone ***
include_once(CMS_ROOTPATH . "include/timezone.php"); // set timezone 
timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Check if visitor is a bot or crawler ***
$bot_visit = preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);
// *** Line for bot test! ***
//$bot_visit=true;

$language_folder = opendir(CMS_ROOTPATH . 'languages/');
while (false !== ($file = readdir($language_folder))) {
    if (strlen($file) < 6 and $file != '.' and $file != '..') {
        $language_file[] = $file;

        // *** Order of languages ***
        if ($file == 'cn') $language_order[] = 'Chinese';
        elseif ($file == 'cs') $language_order[] = 'Czech';
        elseif ($file == 'da') $language_order[] = 'Dansk';
        elseif ($file == 'de') $language_order[] = 'Deutsch';
        elseif ($file == 'en') $language_order[] = 'English';
        elseif ($file == 'en_ca') $language_order[] = 'English_ca';
        elseif ($file == 'en_us') $language_order[] = 'English_us';
        elseif ($file == 'es') $language_order[] = 'Espanol';
        elseif ($file == 'fa') $language_order[] = 'Persian';
        elseif ($file == 'fi') $language_order[] = 'Suomi';
        elseif ($file == 'fr') $language_order[] = 'French';
        elseif ($file == 'fur') $language_order[] = 'Furlan';
        elseif ($file == 'he') $language_order[] = 'Hebrew';
        elseif ($file == 'id') $language_order[] = 'Indonesian';
        elseif ($file == 'hu') $language_order[] = 'Magyar';
        elseif ($file == 'it') $language_order[] = 'Italiano';
        elseif ($file == 'es_mx') $language_order[] = 'Mexicano';
        elseif ($file == 'nl') $language_order[] = 'Nederlands';
        elseif ($file == 'no') $language_order[] = 'Norsk';
        elseif ($file == 'pl') $language_order[] = 'Polish';
        elseif ($file == 'pt') $language_order[] = 'Portuguese';
        elseif ($file == 'ro') $language_order[] = 'Romanian';
        elseif ($file == 'ru') $language_order[] = 'Russian';
        elseif ($file == 'sk') $language_order[] = 'Slovensky';
        elseif ($file == 'sv') $language_order[] = 'Swedish';
        elseif ($file == 'tr') $language_order[] = 'Turkish';
        elseif ($file == 'zh') $language_order[] = 'Chinese_traditional';
        else $language_order[] = $file;

        // *** Save choice of language ***
        $language_choice = '';
        if (isset($_GET["language"])) {
            $language_choice = $_GET["language"];
        }

        if ($language_choice != '') {
            // Check if file exists (IMPORTANT DO NOT REMOVE THESE LINES)
            // ONLY save an existing language file.
            if ($language_choice == $file) {
                $_SESSION["language_humo"] = $file;
            }
        }
    }
}
closedir($language_folder);
// *** Order language array by name of language ***
array_multisort($language_order, $language_file);


// *** Log in ***
$valid_user = false;
if (isset($_POST["username"]) && isset($_POST["password"])) {
    $resultDb = $db_functions->get_user($_POST["username"], $_POST["password"]);
    if ($resultDb) {
        $valid_user = true;

        // *** 2FA is enabled, so check 2FA code ***
        if (isset($resultDb->user_2fa_enabled) and $resultDb->user_2fa_enabled) {
            $valid_user = false;
            $fault = true;
            include_once(CMS_ROOTPATH . "include/2fa_authentication/authenticator.php");

            if ($_POST['2fa_code'] and is_numeric($_POST['2fa_code'])) {
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

            // *** Save succesful login into log! ***
            $sql = "INSERT INTO humo_user_log SET
                log_date='" . date("Y-m-d H:i") . "',
                log_username='" . $resultDb->user_name . "',
                log_ip_address='" . $_SERVER['REMOTE_ADDR'] . "',
                log_user_admin='user',
                log_status='success'";
            $dbh->query($sql);

            // *** Send to secured page ***
            //if (CMS_SPECIFIC == 'Joomla') {
            //    header("Location: index.php?option=com_humo-gen&amp;menu_choice=main_index");
            //} else {
            header("Location: " . CMS_ROOTPATH . "index.php?menu_choice=main_index");
            //}
            exit();
        }
    } else {
        // *** No valid user found ***
        $fault = true;

        // *** Save failed login into log! ***
        $sql = "INSERT INTO humo_user_log SET
            log_date='" . date("Y-m-d H:i") . "',
            log_username='" . safe_text_db($_POST["username"]) . "',
            log_ip_address='" . $_SERVER['REMOTE_ADDR'] . "',
            log_user_admin='user',
            log_status='failed'";
        $dbh->query($sql);
    }
}

// *** Language processing after header("..") lines. *** 
include_once(CMS_ROOTPATH . "languages/language.php"); //Taal

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
if (isset($screen_mode) and $screen_mode == "PDF") {
    $dirmark1 = '';
    $dirmark2 = '';
}


// *** Process highlight of menu item, title of page and $uri_path ***
$auto_menu = $_SERVER['REQUEST_URI'];
$save_menu_choice = '';
$page = 'index';
$head_text = $humo_option["database_name"];
$tmp_path = '';

if (strpos($auto_menu, 'index') > 0) {
    $save_menu_choice = 'main_index';
    $head_text .= ' - ' . __('Main index');

    $url_position = strpos($auto_menu, 'index');
    $tmp_path = substr($auto_menu, 0, $url_position);
}
if (strpos($auto_menu, 'tree_index') > 0) {
    $save_menu_choice = 'tree_index';
    $head_text .= ' - ' . __('Family tree index');

    $url_position = strpos($auto_menu, 'tree_index');
    $tmp_path = substr($auto_menu, 0, $url_position);
}
if (strpos($auto_menu, 'list') > 0) {
    $save_menu_choice = 'persons';
    $head_text .= ' - ' . __('Persons');

    $url_position = strpos($auto_menu, 'list');
    $tmp_path = substr($auto_menu, 0, $url_position);
}
// *** Backwards compatibility only ***
if (strpos($auto_menu, 'lijst') > 0) {
    $save_menu_choice = 'persons';
    $head_text .= ' - ' . __('Persons');

    $url_position = strpos($auto_menu, 'lijst');
    $tmp_path = substr($auto_menu, 0, $url_position);

    $page = 'list';
}
if (strpos($auto_menu, 'list_names') > 0) {
    $save_menu_choice = 'names';
    $head_text .= ' - ' . __('Names');

    $url_position = strpos($auto_menu, 'list_names');
    $tmp_path = substr($auto_menu, 0, $url_position);
}
// *** Backwards compatibility only ***
if (strpos($auto_menu, 'lijst_namen') > 0) {
    $save_menu_choice = 'names';
    $head_text .= ' - ' . __('Names');

    $url_position = strpos($auto_menu, 'lijst_namen');
    $tmp_path = substr($auto_menu, 0, $url_position);

    $page = 'list_names';
}
if (strpos($auto_menu, 'places') > 0) {
    $save_menu_choice = 'places';
    $head_text .= ' - ' . __('Places');
}
if (strpos($auto_menu, 'places_families') > 0) {
    $save_menu_choice = 'places_families';
    $head_text .= ' - ' . __('Places');
}
if (
    isset($_POST['index_list'])
    and $_POST['index_list'] == "places"
) {
    $save_menu_choice = 'places';
    $head_text .= ' - ' . __('Places');
}
if (strpos($auto_menu, 'photoalbum') > 0) {
    $save_menu_choice = 'pictures';
    $head_text .= ' - ' . __('Photobook');
}
if (strpos($auto_menu, 'source') > 0) {
    $save_menu_choice = 'sources';
    $head_text .= ' - ' . __('Source');

    $url_position = strpos($auto_menu, 'source');
    $tmp_path = substr($auto_menu, 0, $url_position);
}
// *** First check 'address' then 'addresses' ***
if (strpos($auto_menu, 'address') > 0) {
    $save_menu_choice = 'address';
    $head_text .= ' - ' . __('Address');

    $url_position = strpos($auto_menu, 'address');
    $tmp_path = substr($auto_menu, 0, $url_position);

    $page = 'address';
}
if (strpos($auto_menu, 'addresses') > 0) {
    $save_menu_choice = 'addresses';
    $head_text .= ' - ' . __('Addresses');
}
if (strpos($auto_menu, 'birthday_list') > 0) {
    $save_menu_choice = 'birthday';
    $head_text .= ' - ' . __('Birthday calendar');

    $url_position = strpos($auto_menu, 'birthday_list');
    $tmp_path = substr($auto_menu, 0, $url_position);

    $page = 'birthday';
}
if (strpos($auto_menu, 'statistics') > 0) {
    $save_menu_choice = 'statistics';
    $head_text .= ' - ' . __('Statistics');

    $url_position = strpos($auto_menu, 'statistics');
    $tmp_path = substr($auto_menu, 0, $url_position);

    $page = 'statistics';
}
if (strpos($auto_menu, 'relations') > 0) {
    $save_menu_choice = 'relations';
    $head_text .= ' - ' . __('Relationship calculator');
}
if (strpos($auto_menu, 'mailform') > 0) {
    $save_menu_choice = 'mailform';
    $head_text .= ' - ' . __('Mail form');
}
if (strpos($auto_menu, 'maps') > 0) {
    $save_menu_choice = 'maps';
    $head_text .= ' - ' . __('World map');
}
if (strpos($auto_menu, 'latest_changes') > 0) {
    $save_menu_choice = 'latest_changes';
    $head_text .= ' - ' . __('Latest changes');
}
if (strpos($auto_menu, 'help') > 0) {
    $save_menu_choice = 'help';
    $head_text .= ' - ' . __('Help');

    $url_position = strpos($auto_menu, 'help');
    $tmp_path = substr($auto_menu, 0, $url_position);

    $page = 'help';
}
/*
if (strpos($auto_menu, 'info') > 0) {
    $save_menu_choice = 'info';
    $head_text .= ' - ' . __('Information');
}
if (strpos($auto_menu, 'credits') > 0) {
    $save_menu_choice = 'credits';
    $head_text .= ' - ' . __('Credits');
}
*/
if (strpos($auto_menu, 'cookies') > 0) {
    $save_menu_choice = 'cookies';
    $head_text .= ' - ' . __('Cookie information');

    $url_position = strpos($auto_menu, 'cookies');
    $tmp_path = substr($auto_menu, 0, $url_position);

    $page = 'cookies';
}
if (strpos($auto_menu, 'login') > 0) {
    $save_menu_choice = 'login';
    $head_text .= ' - ' . __('Login');

    $url_position = strpos($auto_menu, 'login');
    $tmp_path = substr($auto_menu, 0, $url_position);

    $page = 'login';
}
if (strpos($auto_menu, 'family') > 0) {
    $save_menu_choice = 'persons';
    $head_text .= ' - ' . __('Family Page');

    $url_position = strpos($auto_menu, 'family');
    $tmp_path = substr($auto_menu, 0, $url_position);
}
// *** Backwards compatibility only ***
// *** Example: gezin.php?database=humo_&id=F1&hoofdpersoon=I2 ***
if (strpos($auto_menu, 'gezin') > 0) {
    $save_menu_choice = 'persons';
    $head_text .= ' - ' . __('Family Page');

    $url_position = strpos($auto_menu, 'gezin');
    $tmp_path = substr($auto_menu, 0, $url_position);

    if (isset($_GET["hoofdpersoon"])) {
        $_GET['main_person'] = $_GET["hoofdpersoon"];
    }
    if (isset($_POST["hoofdpersoon"])) {
        $_POST['main_person'] = $_POST["hoofdpersoon"];
    }
    $page = 'family';
}
if (strpos($auto_menu, 'cms_pages') > 0) {
    $save_menu_choice = 'cms_pages';
    $head_text .= ' - ' . __('Information');

    $url_position = strpos($auto_menu, 'cms_pages');
    $tmp_path = substr($auto_menu, 0, $url_position);
}
if (strpos($auto_menu, 'register') > 0) {
    $save_menu_choice = 'register';
    $head_text .= ' - ' . __('Register');
}
if (strpos($auto_menu, 'user_settings') > 0) {
    $save_menu_choice = 'settings';
    $head_text .= ' - ' . __('Settings');

    $url_position = strpos($auto_menu, 'user_settings');
    $tmp_path = substr($auto_menu, 0, $url_position);
    $page = 'settings';
}
if (strpos($auto_menu, 'report_ancestor') > 0) {
    $save_menu_choice = 'report_ancestor';
    $head_text .= ' - ' . __('Ancestor report');

    $url_position = strpos($auto_menu, 'report_ancestor');
    $tmp_path = substr($auto_menu, 0, $url_position);
}

$menu_choice = $save_menu_choice;

// *** Generate BASE HREF for use in url_rewrite ***
// SERVER_NAME   127.0.0.1
//     PHP_SELF: /url_test/index/1abcd2345/
// OF: PHP_SELF: /url_test/index.php
// REQUEST_URI: /url_test/index/1abcd2345/
// REQUEST_URI: /url_test/index.php?variabele=1
$base_href = '';
if ($humo_option["url_rewrite"] == "j" and $tmp_path) {
    // *** url_rewrite ***
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
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


// *** For PDF reports: remove html tags en decode ' characters ***
function pdf_convert($text)
{
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES);
    //$text=@iconv("UTF-8","cp1252//IGNORE//TRANSLIT",$text);	// Only needed if FPDF is used. We now use TFPDF.
    return $text;
}

// *** Set default PDF font ***
$pdf_font = 'DejaVu';

// *** Don't generate a HTML header in a PDF report ***
if (isset($screen_mode) and ($screen_mode == 'PDF' or $screen_mode == "ASPDF")) {
    //require(CMS_ROOTPATH.'include/fpdf/fpdf.php');
    //require(CMS_ROOTPATH.'include/fpdf/fpdfextend.php');

    // *** june 2022: FPDF supports romanian and greek characters ***
    //define('FPDF_FONTPATH',"include/fpdf16//font/unifont");
    require(CMS_ROOTPATH . 'include/tfpdf/tfpdf.php');
    require(CMS_ROOTPATH . 'include/tfpdf/tfpdfextend.php');

    // *** Set variabele for queries ***
    $tree_prefix_quoted = safe_text_db($_SESSION['tree_prefix']);
} else {
    // *** Cookie for "show descendant chart below fanchart"

    // Set default ("0" is OFF, "1" is ON):
    $showdesc = "0";

    if (isset($_POST['show_desc'])) {
        if ($_POST['show_desc'] == "1") {
            $showdesc = "1";
            $_SESSION['save_show_desc'] = "1";
            setcookie("humogen_showdesc", "1", time() + 60 * 60 * 24 * 365); // set cookie to "1"
        } else {
            $showdesc = "0";
            $_SESSION['save_show_desc'] = "0";
            setcookie("humogen_showdesc", "0", time() + 60 * 60 * 24 * 365); // set cookie to "0"
            // we don't delete the cookie but set it to "O" for the sake of those who want to make the default "ON" ($showdesc="1")
        }
    }

    if (!CMS_SPECIFIC) {
        // ----------- RTL by Dr Maleki ------------------
        $html_text = '';
        if ($language["dir"] == "rtl") {   // right to left language
            $html_text = ' dir="rtl"';
        }
        if (isset($screen_mode) and ($screen_mode == "STAR" or $screen_mode == "STARSIZE")) {
            $html_text = '';
        }
?>
        <!DOCTYPE html>

        <html lang="<?= $selected_language; ?>" <?= $html_text; ?>>

        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8">

            <!-- Rescale standard HuMo-genealogy pages for mobile devices -->
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <title><?= $head_text; ?></title>

    <?php
        if ($humo_option["searchengine"] == "j") {
            echo $humo_option["robots_option"];
        }

        if ($base_href) echo '<base href="' . $base_href . '">' . "\n";
    }

    //if (CMS_SPECIFIC == 'Joomla') {
    //    // *** Special CSS file for Joomla ***
    //    JHTML::stylesheet('gedcom_joomla.css', CMS_ROOTPATH);
    //} elseif (CMS_SPECIFIC == 'CMSMS') {
    //    // *** Stylesheet links outside header won't validate. styling will be managed in CMS ***
    //} else {
    //echo '<link href="' . CMS_ROOTPATH . 'gedcom.css" rel="stylesheet" type="text/css">';
    //}
    echo '<link href="' . CMS_ROOTPATH . 'gedcom.css" rel="stylesheet" type="text/css">';

    if (CMS_SPECIFIC != 'CMSMS') {
        echo '<link href="' . CMS_ROOTPATH . 'print.css" rel="stylesheet" type="text/css" media="print">';

        // *** Use your own favicon.ico in media folder ***
        if (file_exists('media/favicon.ico'))
            echo '<link rel="shortcut icon" href="' . CMS_ROOTPATH . 'media/favicon.ico" type="image/x-icon">';
        else
            echo '<link rel="shortcut icon" href="' . CMS_ROOTPATH . 'favicon.ico" type="image/x-icon">';
    }

    if (isset($user["group_birthday_rss"]) and $user["group_birthday_rss"] == "j") {
        //$language_rss = 'en';
        //if (isset($_SESSION["language_humo"])) {
        //    $language_rss = $_SESSION["language_humo"];
        //}
        //echo '<link rel="alternate" type="application/rss+xml" title="Birthdaylist" href="' . CMS_ROOTPATH . 'birthday_rss.php?lang=' . $language_rss . '" >';
        echo '<link rel="alternate" type="application/rss+xml" title="Birthdaylist" href="' . CMS_ROOTPATH . 'birthday_rss.php?lang=' . $selected_language . '" >';
    }

    // *** Family tree choice ***
    global $database;
    $database = '';
    if (isset($_GET["database"])) $database = $_GET["database"];
    if (isset($_POST["database"])) $database = $_POST["database"];
    /*
    if (isset($urlpart[0]) AND $urlpart[0]!='' AND $urlpart[0]!='standaard'){
        // backwards compatible: humo2_
        $database=$urlpart[0]; // *** url_rewrite ***
        $_GET["database"]=$database; // *** Needed to check for CMS page if url-rewrite is used ***

        // numeric value
        if (is_numeric($urlpart[0])){
            // *** Check if family tree really exists ***
            $dataDb=$db_functions->get_tree($urlpart[0]);
            if ($dataDb){
                if ($urlpart[0]==$dataDb->tree_id){
                    $_SESSION['tree_prefix']=$dataDb->tree_prefix;
                    $database=$dataDb->tree_prefix;
                }
            }
        }
    }
    */

    // *** New option, use family tree number in the url: database=humo_2 changed into: tree_id=1 ***
    if (isset($_GET["tree_id"])) $temp_tree_id = $_GET["tree_id"];
    if (isset($_POST["tree_id"])) $temp_tree_id = $_POST["tree_id"];
    if (isset($temp_tree_id) and is_numeric($temp_tree_id) and $temp_tree_id) {
        // *** Check if family tree really exists ***
        $dataDb = $db_functions->get_tree($temp_tree_id);
        if ($dataDb) {
            if ($temp_tree_id == $dataDb->tree_id) {
                $_SESSION['tree_prefix'] = $dataDb->tree_prefix;
                $database = $dataDb->tree_prefix;
            }
        }
    }

    // *** For example: database=humo2_ ***
    if (isset($database) and is_string($database) and $database) {
        // *** Check if family tree really exists ***
        $dataDb = $db_functions->get_tree($database);
        if ($dataDb) {
            if ($database == $dataDb->tree_prefix) $_SESSION['tree_prefix'] = $database;
        }
    }

    // *** No family tree selected yet ***
    if (!isset($_SESSION["tree_prefix"]) or $_SESSION['tree_prefix'] == '') {
        $_SESSION['tree_prefix'] = ''; // *** If all trees are blocked then session is empty ***

        // *** Find first family tree that's not blocked for this usergroup ***
        $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while (@$dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
            // *** Check is family tree is showed or hidden for user group ***
            $hide_tree_array = explode(";", $user['group_hide_trees']);
            $hide_tree = false;
            if (in_array($dataDb->tree_id, $hide_tree_array)) $hide_tree = true;
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
    if (in_array(@$dataDb->tree_id, $hide_tree_array)) $hide_tree = true;
    if ($hide_tree) {
        // *** Logged in or logged out user is not allowed to see this tree. Select another if possible ***
        $_SESSION['tree_prefix'] = '';
        $_SESSION['tree_id'] = '';
        $tree_id = '';

        // *** Find first family tree that's not blocked for this usergroup ***
        $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while (@$dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
            // *** Check is family tree is showed or hidden for user group ***
            $hide_tree_array = explode(";", $user['group_hide_trees']);
            $hide_tree = false;
            if (in_array($dataDb->tree_id, $hide_tree_array)) $hide_tree = true;
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
        $_SESSION['tree_id'] = '';
        $tree_id = '';
    }

    // *** Set variabele for queries ***
    $tree_prefix_quoted = safe_text_db($_SESSION['tree_prefix']);

    /*
    // *****************************************************************
    // Use these lines to show a background picture for EACH FAMILY TREE
    // *****************************************************************
    print '<style type="text/css">';
    $picture= "pictures/".$_SESSION['tree_prefix'].".jpg";
    print " body { background-image: url($picture);}";
    print "</style>";
    */

    // if (lightbox activated or) descendant chart or hourglass chart or google maps is used --> load jquery
    if (
        strpos($_SERVER['REQUEST_URI'], "STAR") !== false or
        strpos($_SERVER['REQUEST_URI'], "HOUR") !== false or
        strpos($_SERVER['REQUEST_URI'], "maps") !== false
    ) {
        echo '<script src="' . CMS_ROOTPATH . 'include/jquery/jquery.min.js"></script> ';
        echo '<link rel="stylesheet" href="' . CMS_ROOTPATH . 'include/jqueryui/jquery-ui.min.css"> ';
        echo '<script src="' . CMS_ROOTPATH . 'include/jqueryui/jquery-ui.min.js"></script>';
    }

    // *** Was needed to change fontsize ***
    //CHECK: the function getCookie in this script is also used for theme selection!
    echo '<script src="' . CMS_ROOTPATH . 'fontsize.js"></script>';

    // *** Style sheet select ***
    include_once(CMS_ROOTPATH . "styles/sss1.php");

    // *** Pop-up menu ***
    echo '<script src="' . CMS_ROOTPATH . 'include/popup_menu/popup_menu.js"></script>';
    //if (CMS_SPECIFIC == 'Joomla') {
    //    JHTML::stylesheet('popup_menu.css', CMS_ROOTPATH . 'include/popup_menu/');
    //} elseif (CMS_SPECIFIC == 'CMSMS') {
    //    // Do nothing. stylesheet links outside header won't validate. styling will be managed in CMS
    //} else {
    echo '<link rel="stylesheet" type="text/css" href="' . CMS_ROOTPATH . 'include/popup_menu/popup_menu.css">';
    //}

    // *** Always load script, because of "Random photo" at homepage ***
    // *** Photo lightbox effect using GLightbox ***
    echo '<link rel="stylesheet" href="' . CMS_ROOTPATH . 'include/glightbox/css/glightbox.css">';
    echo '<script src="' . CMS_ROOTPATH . 'include/glightbox/js/glightbox.min.js"></script>';
    // *** There is also a script in footer.php, otherwise GLightbox doesn't work ***

    // *** CSS changes for mobile devices ***
    echo '<link rel="stylesheet" media="(max-width: 640px)" href="gedcom_mobile.css">';

    // *** Extra items in header added by admin ***
    if ($humo_option["text_header"]) echo "\n" . $humo_option["text_header"];

    if (!CMS_SPECIFIC) {
        echo "</head>\n";
        //echo "<body onload='checkCookie()'>\n";  // *** Was needed to change fontsize ***
        echo "<body>\n";
    }

    $db_functions->set_tree_id($_SESSION['tree_id']);

    if ($humo_option['death_char'] == "y") {   // user wants infinity instead of cross -> check if the language files comply
        $str = file_get_contents(CMS_ROOTPATH . "languages/en/en.po");
        if (strpos($str, 'msgstr "&#134;"') or strpos($str, 'msgstr "&dagger;"')) {    // the cross is used (probably new upgrade) so this has to be changed to infinity
            $humo_option['death_char'] = "n"; // fool "change_all.php" into thinking a change was requested from cross to infinity
            include(CMS_ROOTPATH . "languages/change_all.php");
        }
    }

    // *** Added in mar. 2022: disable NO_ZERO_DATE and NO_ZERO_IN_DATE. To solve sorting problems in dates. ***
    //$result= $dbh->query("SET GLOBAL sql_mode=(SELECT
    //	REPLACE(
    //		REPLACE(@@sql_mode,'NO_ZERO_DATE','')
    //	,'NO_ZERO_IN_DATE',''));");
    // *** This query is probably better ***
    $result = $dbh->query("SET SESSION sql_mode=(SELECT
        REPLACE(
            REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE','')
        ,'NO_ZERO_IN_DATE',''));");

    // *** Added in mar. 2023. To prevent double results in search results ***
    // *** Also added in admin/index.php ***
    //SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
    $result = $dbh->query("SET SESSION sql_mode=(SELECT
        REPLACE(
            REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','')
        ,'NO_ZERO_IN_DATE',''));");

    echo '<div class="silverbody">';
} // *** End of PDF export check ***
