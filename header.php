<?php
// *** Disabled 18-01-2023 ***
//ini_set('url_rewriter.tags','');

session_cache_limiter('private, must-revalidate'); //tb edit
session_start();
// *** Regenerate session id regularly to prevent session hacking ***
session_regenerate_id();

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
$db_functions = new db_functions;

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

// *** Debug HuMo-genealogy front pages ***
if ($humo_option["debug_front_pages"] == 'y') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// *** Check if visitor is allowed access to website ***
if (!$db_functions->check_visitor($_SERVER['REMOTE_ADDR'], 'partial')) {
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

$language_folder = opendir(__DIR__ . '/languages/');
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
            include_once(__DIR__ . "/include/2fa_authentication/authenticator.php");

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


            // *** August 2023: Also login for admin pages ***
            // *** Edit family trees [GROUP SETTING] ***
            $groepsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $resultDb->user_group_id . "'");
            @$groepDb = $groepsql->fetch(PDO::FETCH_OBJ);
            if (isset($groepDb->group_edit_trees)) {
                $group_edit_trees = $groepDb->group_edit_trees;
            }
            // *** Edit family trees [USER SETTING] ***
            if (isset($resultDb->user_edit_trees) and $resultDb->user_edit_trees) {
                if ($group_edit_trees) $group_edit_trees .= ';' . $resultDb->user_edit_trees;
                else $group_edit_trees = $resultDb->user_edit_trees;
            }
            if ($groepDb->group_admin != 'j' and $group_edit_trees == '') {
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
                log_ip_address='" . $_SERVER['REMOTE_ADDR'] . "',
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
            log_ip_address='" . $_SERVER['REMOTE_ADDR'] . "',
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
if (isset($screen_mode) and $screen_mode == "PDF") {
    $dirmark1 = '';
    $dirmark2 = '';
}


// *** Process title of page and $uri_path ***
$request_uri = $_SERVER['REQUEST_URI'];

// *** Option url_rewrite disabled ***
// http://127.0.0.1/humo-genealogy/index.php?page=ancestor_sheet&tree_id=3&id=I1180
// change into (but still process index.php, so this will work in NGinx with url_rewrite disabled):
// http://127.0.0.1/humo-genealogy/ancestor_sheet&tree_id=3&id=I1180
if (isset($_GET['page'])) $request_uri = str_replace('index.php?page=', '', $request_uri);

// *** Example: http://localhost/HuMo-genealogy/photoalbum/2?start=1&item=11 ***
$request_uri = strtok($request_uri, "?"); // Remove last part of url: ?start=1&item=11

// *** Get url_rewrite variables ***
$url_array = explode('/', $request_uri);
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
    if (isset($matchedRoute['last_name']) and is_string($matchedRoute['last_name'])) {
        $last_name = $matchedRoute['last_name'];
    }
    // *** Used for source ***
    // TODO improve processing of these variables 
    if (isset($matchedRoute['id'])) {
        $id = $matchedRoute['id']; // for source
        $_GET["id"] = $matchedRoute['id']; // for address
    }

    if ($matchedRoute['tmp_path']) {
        $tmp_path = $matchedRoute['tmp_path'];
    }
}

// *** Backwards compatibility only ***
// *** Example: gezin.php?database=humo_&id=F1&hoofdpersoon=I2 ***
//if (strpos($request_uri, 'gezin') > 0) {
// *** Allready moved most variables to routing script ***
if (isset($_GET["hoofdpersoon"])) {
    $_GET['main_person'] = $_GET["hoofdpersoon"];
}
if (isset($_POST["hoofdpersoon"])) {
    $_POST['main_person'] = $_POST["hoofdpersoon"];
}
//}

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

// *** To be used to show links in several pages ***
include_once(__DIR__ . '/include/links.php');
$link_cls = new Link_cls($uri_path);

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
    // *** june 2022: FPDF supports romanian and greek characters ***
    //define('FPDF_FONTPATH',"include/fpdf16//font/unifont");
    require(__DIR__ . '/include/tfpdf/tfpdf.php');
    require(__DIR__ . '/include/tfpdf/tfpdfextend.php');

    // *** Set variabele for queries ***
    $tree_prefix_quoted = safe_text_db($_SESSION['tree_prefix']);
} else {

    // *** Set cookies before any output ***

    // *** Number of photo's in photobook ***
    if (isset($_POST['show_pictures']) and is_numeric($_POST['show_pictures'])) {
        $show_pictures = $_POST['show_pictures'];
        setcookie("humogenphotos", $show_pictures, time() + 60 * 60 * 24 * 365);
    }
    if (isset($_GET['show_pictures']) and is_numeric($_GET['show_pictures'])) {
        $show_pictures = $_GET['show_pictures'];
        setcookie("humogenphotos", $show_pictures, time() + 60 * 60 * 24 * 365);
    }

    // *** Use session if session is available ***
    if (isset($_SESSION["save_favorites"]) and $_SESSION["save_favorites"]) {
        $favorites_array = $_SESSION["save_favorites"];
    } else {
        // *** Get favourites from cookie (only if session is empty) ***
        if (isset($_COOKIE['humo_favorite'])) {
            foreach ($_COOKIE['humo_favorite'] as $name => $value) {
                $favorites_array[] = $value;
            }
            // *** Save cookie array in session ***
            $_SESSION["save_favorites"] = $favorites_array;
        }
    }

    // *** Add new favorite to list of favourites ***
    // *** Remark: cookies must be set in header, otherwise they don't work ***
    if (isset($_POST['favorite'])) {
        // *** Add favourite to session ***
        $favorites_array[] = $_POST['favorite'];
        $_SESSION["save_favorites"] = $favorites_array;

        // *** Add favourite to cookie ***
        $favorite_array2 = explode("|", $_POST['favorite']);
        // *** Combine tree id and family number as unique array id: 1F4 ***
        $i = $favorite_array2['0'] . $favorite_array2['1'];
        setcookie("humo_favorite[$i]", $_POST['favorite'], time() + 60 * 60 * 24 * 365);
    }

    // *** Remove favourite from favorite list ***
    if (isset($_POST['favorite_remove'])) {
        // *** Remove favourite from session ***
        $process_favorites = false;
        if (isset($_SESSION["save_favorites"])) {
            unset($favorites_array);
            foreach ($_SESSION['save_favorites'] as $key => $value) {
                if ($value != $_POST['favorite_remove']) {
                    $favorites_array[] = $value;
                    $process_favorites = true;
                }
            }
            //Doesn't work properly: if (isset($favorites_array)){}
            if ($process_favorites) {
                $_SESSION["save_favorites"] = $favorites_array;
            } else {
                // *** Just removed last favorite, so remove session ***
                unset($_SESSION["save_favorites"]);
            }
        }

        // *** Remove cookie ***
        if (isset($_COOKIE['humo_favorite'])) {
            foreach ($_COOKIE['humo_favorite'] as $name => $value) {
                if ($value == $_POST['favorite_remove']) {
                    setcookie("humo_favorite[$name]", "", time() - 3600);
                }
            }
        }
    }

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

    // ----------- RTL by Dr Maleki ------------------
    $html_text = '';
    if ($language["dir"] == "rtl") {   // right to left language
        $html_text = ' dir="rtl"';
    }
    // TODO check this code
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
        if ($humo_option["searchengine"] == "j") echo $humo_option["robots_option"];
        if ($base_href) echo '<base href="' . $base_href . '">' . "\n";
        ?>

        <link href="css/gedcom.css" rel="stylesheet" type="text/css">
        <link href="css/form.css" rel="stylesheet" type="text/css">
        <link href="css/tab_menu.css" rel="stylesheet" type="text/css">
        <!-- TODO this is only needed for outline report -->
        <link href="css/outline_report.css" rel="stylesheet" type="text/css">

    <?php
    echo '<link href="css/print.css" rel="stylesheet" type="text/css" media="print">';

    // *** Use your own favicon.ico in media folder ***
    if (file_exists('media/favicon.ico'))
        echo '<link rel="shortcut icon" href="media/favicon.ico" type="image/x-icon">';
    else
        echo '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">';

    if (isset($user["group_birthday_rss"]) and $user["group_birthday_rss"] == "j") {
        echo '<link rel="alternate" type="application/rss+xml" title="Birthdaylist" href="birthday_rss.php?lang=' . $selected_language . '" >';
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

    // *** Use family tree number in the url: database=humo_2 changed into: tree_id=1 ***
    if (isset($_GET["tree_id"])) $select_tree_id = $_GET["tree_id"];
    if (isset($_POST["tree_id"])) $select_tree_id = $_POST["tree_id"];
    if (isset($select_tree_id) and is_numeric($select_tree_id) and $select_tree_id) {
        // *** Check if family tree really exists ***
        $dataDb = $db_functions->get_tree($select_tree_id);
        if ($dataDb) {
            if ($select_tree_id == $dataDb->tree_id) {
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
    //if (
    //    strpos($_SERVER['REQUEST_URI'], "STAR") !== false or
    //    strpos($_SERVER['REQUEST_URI'], "HOUR") !== false or
    //    strpos($_SERVER['REQUEST_URI'], "maps") !== false
    if (
        strpos($_SERVER['REQUEST_URI'], "descendant") !== false or
        strpos($_SERVER['REQUEST_URI'], "HOUR") !== false or
        strpos($_SERVER['REQUEST_URI'], "maps") !== false
    ) {
        echo '<script src="include/jquery/jquery.min.js"></script> ';
        echo '<link rel="stylesheet" href="include/jqueryui/jquery-ui.min.css"> ';
        echo '<script src="include/jqueryui/jquery-ui.min.js"></script>';
    }

    // *** Cookie for theme selection ***
    echo '<script>
    function getCookie(NameOfCookie) {
        if (document.cookie.length > 0) {
            begin = document.cookie.indexOf(NameOfCookie + "=");
            if (begin != -1) {
                begin += NameOfCookie.length + 1;
                end = document.cookie.indexOf(";", begin);
                if (end == -1) {
                    end = document.cookie.length;
                }
                return unescape(document.cookie.substring(begin, end));
            }
        }
        return null;
    }
    </script>';

    // *** Style sheet select ***
    include_once(__DIR__ . "/styles/sss1.php");

    // *** Pop-up menu ***
    echo '<script src="include/popup_menu/popup_menu.js"></script>';
    echo '<link rel="stylesheet" type="text/css" href="include/popup_menu/popup_menu.css">';

    // *** Always load script, because of "Random photo" at homepage ***
    // *** Photo lightbox effect using GLightbox ***
    echo '<link rel="stylesheet" href="include/glightbox/css/glightbox.css">';
    echo '<script src="include/glightbox/js/glightbox.min.js"></script>';
    // *** Remark: there is also a script in footer.php, otherwise GLightbox doesn't work ***

    // *** CSS changes for mobile devices ***
    echo '<link rel="stylesheet" media="(max-width: 640px)" href="css/gedcom_mobile.css">';

    // *** Extra items in header added by admin ***
    if ($humo_option["text_header"]) echo "\n" . $humo_option["text_header"];

    echo "</head>\n";
    echo "<body>\n";

    $db_functions->set_tree_id($_SESSION['tree_id']);

    if ($humo_option['death_char'] == "y") {   // user wants infinity instead of cross -> check if the language files comply
        $str = file_get_contents("languages/en/en.po");
        if (strpos($str, 'msgstr "&#134;"') or strpos($str, 'msgstr "&dagger;"')) {    // the cross is used (probably new upgrade) so this has to be changed to infinity
            $humo_option['death_char'] = "n"; // fool "change_all.php" into thinking a change was requested from cross to infinity
            include(__DIR__ . "/languages/change_all.php");
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
