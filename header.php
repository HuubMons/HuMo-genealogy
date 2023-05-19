<?php

require __DIR__ . '/config/bootstrap.php';

if (!defined("CMS_ROOTPATH")) define("CMS_ROOTPATH", "");
if (!defined("CMS_ROOTPATH_ADMIN")) define("CMS_ROOTPATH_ADMIN", "admin/");


include_once __DIR__ . '/include/db_tree_text.php';
include_once __DIR__ . '/include/db_functions_cls.php';
include_once __DIR__ . '/include/model/db_user_log.php';

$db_functions = new db_functions($dbh);
$db_tree_text = new db_tree_text($dbh);
$db_user_log = new db_user_log($dbh);

include_once __DIR__ . '/include/safe.php';

include_once __DIR__ . '/include/settings_user.php'; // USER variables

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
if (isset($_POST["username"]) && isset($_POST["password"])) {
	require __DIR__ . '/nextlib/Authenticator.php';
	$auth = new Authenticator($dbh);
	$auth = $auth->login(safe_text_db($_POST["username"]), safe_text_db($_POST["password"]), safe_text_db($_POST['2fa_code']) ?? null);
	if ($auth) 
	{
		$fault = false;

		$_SESSION['user_name'] = $auth->user_name;
		$_SESSION['user_id'] = $auth->user_id;
		$_SESSION['user_group_id'] = $auth->user_group_id;

		// *** Send to secured page ***
		header("Location: /index.php?menu_choice=main_index");
		exit();
		
	} else {
		$fault = true;
	}
}

// *** Language processing after header("..") lines. *** 
include_once __DIR__ . '/languages/language.php'; //Taal

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


// *** Automatic menu choice ***
// *** Also used for title of webpage ***
$auto_menu = $_SERVER['REQUEST_URI'];
$save_menu_choice = '';

// *** Automatic menu_choice main_index ***
if (strpos($auto_menu, 'index') > 0) {
	$save_menu_choice = 'main_index';
}
if (strpos($auto_menu, 'tree_index') > 0) {
	$save_menu_choice = 'tree_index';
}
if (strpos($auto_menu, 'list') > 0) {
	$save_menu_choice = 'persons';
}
if (strpos($auto_menu, 'list_names') > 0) {
	$save_menu_choice = 'names';
}
if (strpos($auto_menu, 'places') > 0) {
	$save_menu_choice = 'places';
}
if (strpos($auto_menu, 'places_families') > 0) {
	$save_menu_choice = 'places_families';
}
if (
	isset($_POST['index_list'])
	and $_POST['index_list'] == "places"
) {
	$save_menu_choice = 'places';
}
if (strpos($auto_menu, 'photoalbum') > 0) {
	$save_menu_choice = 'pictures';
}
if (strpos($auto_menu, 'source') > 0) {
	$save_menu_choice = 'sources';
}
if (strpos($auto_menu, 'address') > 0) {
	$save_menu_choice = 'addresses';
}
if (strpos($auto_menu, 'birthday') > 0) {
	$save_menu_choice = 'birthday';
}
if (strpos($auto_menu, 'statistics') > 0) {
	$save_menu_choice = 'statistics';
}
if (strpos($auto_menu, 'relations') > 0) {
	$save_menu_choice = 'relations';
}
if (strpos($auto_menu, 'mailform') > 0) {
	$save_menu_choice = 'mailform';
}
if (strpos($auto_menu, 'maps') > 0) {
	$save_menu_choice = 'maps';
}
if (strpos($auto_menu, 'latest_changes') > 0) {
	$save_menu_choice = 'latest_changes';
}
if (strpos($auto_menu, 'help') > 0) {
	$save_menu_choice = 'help';
}
if (strpos($auto_menu, 'info') > 0) {
	$save_menu_choice = 'info';
}
if (strpos($auto_menu, 'credits') > 0) {
	$save_menu_choice = 'credits';
}
if (strpos($auto_menu, 'info_cookies') > 0) {
	$save_menu_choice = 'info_cookies';
}
if (strpos($auto_menu, 'login') > 0) {
	$save_menu_choice = 'login';
}
if (strpos($auto_menu, 'family') > 0) {
	$save_menu_choice = 'persons';
}
if (strpos($auto_menu, 'cms_pages') > 0) {
	$save_menu_choice = 'cms_pages';
}
if (strpos($auto_menu, 'register') > 0) {
	$save_menu_choice = 'register';
}
if (strpos($auto_menu, 'user_settings') > 0) {
	$save_menu_choice = 'settings';
}

if ($save_menu_choice) {
	$_SESSION['save_menu_choice'] = $save_menu_choice;
}

// *** Used for menu highlight ***
$menu_choice = "main_index";
if (isset($_SESSION["save_menu_choice"])) {
	$menu_choice = $_SESSION["save_menu_choice"];
}

// *** Page title ***
$head_text = $humo_option["database_name"];
$extra_css = '';
$extra_js = '';

if ($menu_choice == 'main_index') {
	$head_text .= ' - ' . __('Main index');
}
if ($menu_choice == 'persons') {
	$head_text .= ' - ' . __('Persons');
}
if ($menu_choice == 'names') {
	$head_text .= ' - ' . __('Names');
}
if ($menu_choice == 'places') {
	$head_text .= ' - ' . __('Places');
}
if ($menu_choice == 'pictures') {
	$head_text .= ' - ' . __('Photobook');
}
if ($menu_choice == 'sources') {
	$head_text .= ' - ' . __('Sources');
}
if ($menu_choice == 'addresses') {
	$head_text .= ' - ' . __('Address');
}
if ($menu_choice == 'birthday') {
	$head_text .= ' - ' . __('Birthday calendar');
}
if ($menu_choice == 'statistics') {
	$head_text .= ' - ' . __('Statistics');
}
if ($menu_choice == 'relations') {
	$head_text .= ' - ' . __('Relationship calculator');
}
if ($menu_choice == 'mailform') {
	$head_text .= ' - ' . __('Mail form');
}
if ($menu_choice == 'maps') {
	$head_text .= ' - ' . __('Google maps');
}
if ($menu_choice == 'latest_changes') {
	$head_text .= ' - ' . __('Latest changes');
}
if ($menu_choice == 'help') {
	$head_text .= ' - ' . __('Help');
}
if ($menu_choice == 'info') {
	$head_text .= ' - ' . __('Information');
}
if ($menu_choice == 'credits') {
	$head_text .= ' - ' . __('Credits');
}
if ($menu_choice == 'info') {
	$head_text .= ' - ' . __('Cookie information');
}
if ($menu_choice == 'login') {
	$head_text .= ' - ' . __('Login');
}
if ($menu_choice == 'register') {
	$head_text .= ' - ' . __('Register');
}
if ($menu_choice == 'settings') {
	$head_text .= ' - ' . __('Settings');
}

	// *** Family tree choice ***
	global $database;
	$database = '';
	if (isset($_GET["database"])) $database = $_GET["database"];
	if (isset($_POST["database"])) $database = $_POST["database"];

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
	require __DIR__ . '/externals/tfpdf/tfpdf.php';
	require __DIR__ . '/externals/tfpdf/tfpdfextend.php';

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
			// setcookie("humogen_showdesc", "1", time() + 60 * 60 * 24 * 365); // set cookie to "1"
		} else {
			$showdesc = "0";
			$_SESSION['save_show_desc'] = "0";
			// setcookie("humogen_showdesc", "0", time() + 60 * 60 * 24 * 365); // set cookie to "0"
			// we don't delete the cookie but set it to "O" for the sake of those who want to make the default "ON" ($showdesc="1")
		}
	}

	if (!CMS_SPECIFIC) {
		// *** Generate header of HTML pages ***	
		
		// *** Use your own favicon.ico in media folder ***
		if (file_exists(__DIR__ . '/media/favicon.ico')) {
			$favicon = "/media/favicon.ico";
		} else {
			$favicon = "/theme/favicon.ico";
		}

		$robots_option = $humo_option["searchengine"] == "j" ? $humo_option["robots_option"] : ""
?>
		<!DOCTYPE html>
		<html lang="<?= $selected_language; ?>">

		<head>
			<meta charset="UTF-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?= $head_text; ?></title>
			<link rel="shortcut icon" href="<?= $favicon; ?>" type="image/x-icon">
			<link href="/theme/gedcom.css" rel="stylesheet" type="text/css">
			<link href="/theme/print.css" rel="stylesheet" type="text/css" media="print">

			<?= $robots_option; ?>

	<?php }

	/*
	// *****************************************************************
	// Use these lines to show a background picture for EACH FAMILY TREE
	// *****************************************************************
	print '<style type="text/css">';
	$picture= "pictures/".$_SESSION['tree_prefix'].".jpg";
	print " body { background-image: url($picture);}";
	print "</style>";
	*/

	//if(strpos($_SERVER['REQUEST_URI'],"STAR")!== false OR
	//	strpos($_SERVER['REQUEST_URI'],"maps")!== false OR
	//	strpos($_SERVER['REQUEST_URI'],"HOUR")!== false OR
	//	$user['group_pictures']=='j') {
	//if(strpos($_SERVER['REQUEST_URI'],"STAR")!== false OR
	//	strpos($_SERVER['REQUEST_URI'],"maps")!== false OR
	//	strpos($_SERVER['REQUEST_URI'],"HOUR")!== false) {
	//	// if (lightbox activated or) descendant chart or hourglass chart or google maps is used --> load jquery
	//		echo '	<script src="'.CMS_ROOTPATH.'include/jquery/jquery.min.js"></script> ';
	//}
	if (
		strpos($_SERVER['REQUEST_URI'], "STAR") !== false or
		strpos($_SERVER['REQUEST_URI'], "HOUR") !== false or
		strpos($_SERVER['REQUEST_URI'], "maps") !== false
	) {
		echo '<script src="/externals/jquery/jquery.min.js"></script> ';
		echo '<link rel="stylesheet" href="/externals/jqueryui/jquery-ui.min.css"> ';
		echo '<script src="/externals/jqueryui/jquery-ui.min.js"></script>';
	}

	// *** Style sheet select ***
	include_once __DIR__ . '/theme/sss1.php';

	// *** Pop-up menu ***
	echo '<link rel="stylesheet" type="text/css" href="/include/popup_menu/popup_menu.css">';
	echo '<script type="text/javascript" src="/include/popup_menu/popup_menu.js"></script>';

	// *** Always load script, because of "Random photo" at homepage ***
	// *** Photo lightbox effect using GLightbox ***
	echo '<link rel="stylesheet" href="/externals/glightbox/css/glightbox.css" />';
	echo '<script src="/externals/glightbox/js/glightbox.min.js"></script>';
	// *** There is also a script in footer.php, otherwise GLightbox doesn't work ***

	// *** CSS changes for mobile devices ***
	echo '<link rel="stylesheet" media="(max-width: 640px)" href="/theme/gedcom_mobile.css">';

	// *** Extra items in header added by admin ***
	if ($humo_option["text_header"]) echo "\n" . $humo_option["text_header"];

	echo "</head>\n";
	echo "<body>\n";

	$db_functions->set_tree_id($_SESSION['tree_id']);

	if ($humo_option['death_char'] == "y") {   // user wants infinity instead of cross -> check if the language files comply
		$str = file_get_contents(__DIR__ . "/languages/en/en.po");
		if (strpos($str, 'msgstr "&#134;"') or strpos($str, 'msgstr "&dagger;"')) {    // the cross is used (probably new upgrade) so this has to be changed to infinity
			$humo_option['death_char'] = "n"; // fool "change_all.php" into thinking a change was requested from cross to infinity
			include __DIR__ . '/languages/change_all.php';
		}
	}

	// *** Added in mar. 2022: disable NO_ZERO_DATE and NO_ZERO_IN_DATE. To solve sorting problems in dates. ***
	//$result= $dbh->query("SET GLOBAL sql_mode=(SELECT
	//	REPLACE(
	//		REPLACE(@@sql_mode,'NO_ZERO_DATE','')
	//	,'NO_ZERO_IN_DATE',''));");
	// *** This query is probably better ***
	// TODO: bad choice, overriding mysql server errors has never been a solution.
	/* $result = $dbh->query("SET SESSION sql_mode=(SELECT
		REPLACE(
			REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE','')
		,'NO_ZERO_IN_DATE',''));");

	// *** Added in mar. 2023. To prevent double results in search results ***
	// *** Also added in admin/index.php ***
	//SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
	$result = $dbh->query("SET SESSION sql_mode=(SELECT
		REPLACE(
			REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','')
		,'NO_ZERO_IN_DATE',''));"); */

	echo '<div class="silverbody">';
	// *** End of PDF export check ***
}
