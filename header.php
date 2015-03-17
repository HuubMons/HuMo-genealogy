<?php
// *** For testing purposes ***
//error_reporting(E_ALL | E_STRICT);
//error_reporting(E_ALL);
//error_reporting(0);

// *** Check if HuMo-gen is in a CMS system ***
//	Names:
//		- CMS names used for now are 'Joomla' and 'CMSMS'.
//	Usage:
//		- Code for all CMS: if (CMS_SPECIFIC) {}
//		- Code for one CMS: if (CMS_SPECIFIC == 'Joomla') {}
//		- Code NOT for CMS: if (!CMS_SPECIFIC) {}
if (!defined("CMS_SPECIFIC")) define("CMS_SPECIFIC", false);

// *** When run from CMS, the path to the map (that contains this file) should be given ***
if (!defined("CMS_ROOTPATH")) define("CMS_ROOTPATH", "");

if (!defined("CMS_ROOTPATH_ADMIN")) define("CMS_ROOTPATH_ADMIN", "admin/");

ini_set('url_rewriter.tags','');

if (!CMS_SPECIFIC){
	session_cache_limiter ('private, must-revalidate'); //tb edit
	session_start();
}

if (isset($_GET['log_off'])){
	if (isset($_SESSION["user_name_admin"])) {
		// *** DO NOT REMOVE data if logged in as administrator! ***
		unset($_SESSION['user_name']);
		unset($_SESSION['user_id']);
		unset($_SESSION['user_group_id']);
		unset($_SESSION['tree_prefix']);
	}
	else{
		session_unset(); // *** Clear all variables ***
		session_destroy(); // *** Remove session ***
		session_write_close();
		session_start();
	}
}

include_once(CMS_ROOTPATH."include/db_login.php"); //Inloggen database.
include_once (CMS_ROOTPATH.'include/show_tree_text.php');

// *** Use UTF-8 database connection ***
//$dbh->query("SET NAMES 'utf8'");

// *** Show a message at NEW installation. ***
$result = $dbh->query("SELECT COUNT(*) FROM humo_settings");
if (!$result OR $result->rowCount() ==0) {
	echo "Installation of HuMo-gen is not yet completed.<br>Installatie van HuMo-gen is nog niet voltooid.";
	exit();
}
include_once(CMS_ROOTPATH."include/safe.php");
include_once(CMS_ROOTPATH."include/settings_global.php"); //Variables
include_once(CMS_ROOTPATH."include/settings_user.php"); // USER variables

// *** Set timezone ***
include_once(CMS_ROOTPATH."include/timezone.php"); // set timezone 
timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Check if visitor is a bot or crawler ***
$bot_visit=preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);
// *** Line for bot test! ***
//$bot_visit=true;

$language_folder=opendir(CMS_ROOTPATH.'languages/');
while (false!==($file = readdir($language_folder))) {
	if (strlen($file)<6 AND $file!='.' AND $file!='..'){
		$language_file[]=$file;

		// *** Save choice of language ***
		$language_choice='';
		if (isset($_GET["language"])){ $language_choice=$_GET["language"]; }

		if ($language_choice!=''){
			// Check if file exists (IMPORTANT DO NOT REMOVE THESE LINES)
			// ONLY save an existing language file.
			if ($language_choice==$file){ $_SESSION['language'] = $file;}
		}
	}
}
closedir($language_folder);

// *** Log in ***
if (isset($_POST["username"]) && isset($_POST["password"])){
	$query = "SELECT * FROM humo_users
		WHERE user_name='" . safe_text($_POST["username"]) ."'
		AND user_password='".MD5(safe_text($_POST["password"]))."'";
	$result = $dbh->query($query);
	if($result->rowcount() > 0) {
		@$resultDb = $result->fetch(PDO::FETCH_OBJ);
		$_SESSION['user_name'] = safe_text($_POST["username"]);
		$_SESSION['user_id'] = $resultDb->user_id;
		$_SESSION['user_group_id'] = $resultDb->user_group_id;

		// *** Save log! ***
		$sql="INSERT INTO humo_user_log SET
			log_date='".date("Y-m-d H:i")."',
			log_username='".safe_text($_POST["username"])."',
			log_ip_address='".$_SERVER['REMOTE_ADDR']."',
			log_user_admin='user'";
		$dbh->query($sql);
		
		// *** Send to secured page ***
		if (CMS_SPECIFIC=='Joomla'){
			header("Location: index.php?option=com_humo-gen&amp;menu_choice=main_index");
		}
		else{
			header("Location: ".CMS_ROOTPATH."index.php?menu_choice=main_index");
		}

		exit();
	}
	else{
		// *** No valid user found ***
		$fault=true;
	}
}

// *** Language processing after header("..") lines. *** 
include_once(CMS_ROOTPATH."languages/language.php"); //Taal

// *** Process LTR and RTL variables ***
$dirmark1="&#x200E;";  //ltr marker
$dirmark2="&#x200F;";  //rtl marker
$rtlmarker="ltr";
$alignmarker="left";
// *** Switch direction markers if language is RTL ***
if($language["dir"]=="rtl") {
	$dirmark1="&#x200F;";  //rtl marker
	$dirmark2="&#x200E;";  //ltr marker
	$rtlmarker="rtl";
	$alignmarker="right";
}
if(isset($screen_mode) AND $screen_mode=="PDF") {
	$dirmark1='';
	$dirmark2='';
}


// *** Automatic menu choice ***
// *** Also used for title of webpage ***
$auto_menu=$_SERVER['REQUEST_URI'];
$save_menu_choice='';

// *** Automatic menu_choice main_index ***
if (strpos($auto_menu,'index')>0){ $save_menu_choice='main_index'; }
if (strpos($auto_menu,'tree_index')>0){ $save_menu_choice='tree_index'; }
if (strpos($auto_menu,'list')>0){ $save_menu_choice='persons'; }
if (strpos($auto_menu,'list_names')>0){ $save_menu_choice='names'; }
if (strpos($auto_menu,'places')>0){ $save_menu_choice='places'; }
if (strpos($auto_menu,'places_families')>0){ $save_menu_choice='places_families'; }
if (isset($_POST['index_list'])
	AND $_POST['index_list']=="places"){ $save_menu_choice='places'; }
if (strpos($auto_menu,'photoalbum')>0){ $save_menu_choice='pictures'; }
if (strpos($auto_menu,'source')>0){ $save_menu_choice='sources'; }
if (strpos($auto_menu,'address')>0){ $save_menu_choice='addresses'; }
if (strpos($auto_menu,'birthday')>0){ $save_menu_choice='birthday'; }
if (strpos($auto_menu,'statistics')>0){ $save_menu_choice='statistics'; }
if (strpos($auto_menu,'relations')>0){ $save_menu_choice='relations'; }
if (strpos($auto_menu,'mailform')>0){ $save_menu_choice='mailform'; }
if (strpos($auto_menu,'maps')>0){ $save_menu_choice='maps'; }
if (strpos($auto_menu,'latest_changes')>0){ $save_menu_choice='latest_changes'; }
if (strpos($auto_menu,'help')>0){ $save_menu_choice='help'; }
if (strpos($auto_menu,'info')>0){ $save_menu_choice='info'; }
if (strpos($auto_menu,'credits')>0){ $save_menu_choice='credits'; }
if (strpos($auto_menu,'info_cookies')>0){ $save_menu_choice='info_cookies'; }
if (strpos($auto_menu,'login')>0){ $save_menu_choice='login'; }
if (strpos($auto_menu,'family')>0){ $save_menu_choice='persons'; }
if (strpos($auto_menu,'cms_pages')>0){ $save_menu_choice='cms_pages'; }
if (strpos($auto_menu,'register')>0){ $save_menu_choice='register'; }
if (strpos($auto_menu,'user_settings')>0){ $save_menu_choice='settings'; }

if ($save_menu_choice){ $_SESSION['save_menu_choice']=$save_menu_choice; }

// *** Used for menu highlight ***
$menu_choice="main_index";
if (isset($_SESSION["save_menu_choice"])){ $menu_choice=$_SESSION["save_menu_choice"]; }

// *** Page title ***
$head_text=$humo_option["database_name"];
if ($menu_choice=='main_index'){ $head_text.=' - '.__('Main index'); }
if ($menu_choice=='persons'){ $head_text.=' - '.__('Persons'); }
if ($menu_choice=='names'){ $head_text.=' - '.__('Names'); }
if ($menu_choice=='places'){ $head_text.=' - '.__('Places'); }
if ($menu_choice=='pictures'){ $head_text.=' - '.__('Photobook'); }
if ($menu_choice=='sources'){ $head_text.=' - '.__('Sources'); }
if ($menu_choice=='addresses'){ $head_text.=' - '.__('Address'); }
if ($menu_choice=='birthday'){ $head_text.=' - '.__('Birthday calendar'); }
if ($menu_choice=='statistics'){ $head_text.=' - '.__('Statistics'); }
if ($menu_choice=='relations'){ $head_text.=' - '.__('Relationship calculator'); }
if ($menu_choice=='mailform'){ $head_text.=' - '.__('Mail form'); }
if ($menu_choice=='maps'){ $head_text.=' - '.__('Google maps'); }
if ($menu_choice=='latest_changes'){ $head_text.=' - '.__('Latest changes'); }
if ($menu_choice=='help'){ $head_text.=' - '.__('Help'); }
if ($menu_choice=='info'){ $head_text.=' - '.__('Information'); }
if ($menu_choice=='credits'){ $head_text.=' - '.__('Credits'); }
if ($menu_choice=='info'){ $head_text.=' - '.__('Cookie information'); }
if ($menu_choice=='login'){ $head_text.=' - '.__('Login'); }
if ($menu_choice=='register'){ $head_text.=' - '.__('Register'); }
if ($menu_choice=='settings'){ $head_text.=' - '.__('Settings'); }

// *** For PDF reports: remove html tags en decode ' characters ***
function pdf_convert($text){
	$text=html_entity_decode(strip_tags($text),ENT_QUOTES);
	$text=@iconv("UTF-8","cp1252//IGNORE//TRANSLIT",$text);
	return $text;
}

// *** Don't generate a HTML header in a PDF report ***
//if (isset($screen_mode) AND $screen_mode=='PDF'){
if (isset($screen_mode) AND ($screen_mode=='PDF' OR $screen_mode=="ASPDF")){
	require(CMS_ROOTPATH.'include/fpdf16/fpdf.php');
	require(CMS_ROOTPATH.'include/fpdf16/fpdfextend.php');
	// *** Set variabele for queries ***
	$tree_prefix_quoted = safe_text($_SESSION['tree_prefix']);
}
else{
	// *** Save family-favorite in cookie ***
	if (isset($_POST['favorite'])){
		$favorite_array2=explode("|",$_POST['favorite']);
		// *** Combine tree prefix and family number as unique array id, for example: humo_F4 ***
		$i=$favorite_array2['2'].$favorite_array2['1'];
		setcookie("humo_favorite[$i]", $_POST['favorite'], time()+60*60*24*365);
	}
	// *** Remove family-favorite cookie ***
	if (isset($_POST['favorite_remove'])){
		if (isset($_COOKIE['humo_favorite'])) {
			foreach ($_COOKIE['humo_favorite'] as $name => $value) {
				if ($value==$_POST['favorite_remove']){
					setcookie ("humo_favorite[$name]", "", time() - 3600);
				}
			}
		}
	}


	// *** Cookie for "show descendant chart below fanchart"

	// Set default ("0" is OFF, "1" is ON):
	$showdesc="0";

	if(isset($_POST['show_desc'])){
		if($_POST['show_desc']=="1") {
			$showdesc="1";
			$_SESSION['save_show_desc']="1";
			setcookie("humogen_showdesc", "1", time()+60*60*24*365); // set cookie to "1"
		}
		else {
			$showdesc="0";
			$_SESSION['save_show_desc']="0";
			setcookie("humogen_showdesc", "0", time()+60*60*24*365); // set cookie to "0"
			// we don't delete the cookie but set it to "O" for the sake of those who want to make the default "ON" ($showdesc="1")
		}
	}	

	if (!CMS_SPECIFIC){
		// *** Generate header of HTML pages ***
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">';

		//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		//      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		//  <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">';

		// Prevent validator faults. It's not working good... Replace all & characters in the links by &amp;
		//ini_set('arg_separator.output','&amp;');

		// ----------- changed by Dr Maleki ------------------ start
		$html_text="\n<html>\n";
		if($language["dir"]=="rtl") {   // right to left language
			$html_text="\n<html dir='rtl'>\n";
		}
		if (isset($screen_mode) AND ($screen_mode=="STAR" OR $screen_mode=="STARSIZE")){
			$html_text="\n<html>\n";
		}
		echo $html_text;

		print "<head>\n";
		echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">'; //to support all of the unicode scripts.
		// --------------------------------------------------- end

		print "<title>".$head_text."</title>\n";

		if ($humo_option["searchengine"]=="j"){ print $humo_option["robots_option"]; }
	}

	// *** Generate BASE HREF for use in url_rewrite ***
	// SERVER_NAME   127.0.0.1
	//     PHP_SELF: /url_test/index/1abcd2345/
	// OF: PHP_SELF: /url_test/index.php
	// REQUEST_URI: /url_test/index/1abcd2345/
	// REQUEST_URI: /url_test/index.php?variabele=1
	// *** No url_rewrite ***
	$url_path=$_SERVER['PHP_SELF'];
	$position=strrpos($_SERVER['PHP_SELF'],'/');
	$uri_path= substr($_SERVER['PHP_SELF'],0,$position).'/';
	// *** url_rewrite ***
	if ($humo_option["url_rewrite"]=="j"){
		$uri_path=$_SERVER['REQUEST_URI'];

		if (substr_count($uri_path, 'tree_index')>0){
			$uri_path = str_replace("tree_index", "!", $uri_path);
			$url_path='tree_index.php';
		}

		if (substr_count($uri_path, 'index')>0){
			$uri_path = str_replace("index", "!", $uri_path);
			$url_path='index.php';
		}

		if (substr_count($uri_path, 'list')>0){
			$uri_path = str_replace("list", "!", $uri_path);
			$url_path='list.php';
		}

		if (substr_count($uri_path, 'list_names')>0){
			$uri_path = str_replace("list_names", "!", $uri_path);
			$url_path='list_names.php';
		}

		if (substr_count($uri_path, 'family')>0){
			$uri_path = str_replace("family", "!", $uri_path);
			$url_path='family.php';  // *** Needed for show_sources ***
		}

		if (substr_count($uri_path, 'report_ancestor')>0){
			$uri_path = str_replace("report_ancestor", "!", $uri_path);
			$url_path='report_ancestor.php';  // *** needed for show_sources ***
		}

		$url_position=strpos($uri_path,'!');
		if ($url_position){
			$uri_path= 'http://'.$_SERVER['SERVER_NAME'].substr($uri_path,0,$url_position);
			echo '<base href="'.$uri_path.'">';

			$url_path=$uri_path.$url_path;
		}
		else{
			// *** Use Standard uri ***
			$uri_path= substr($_SERVER['PHP_SELF'],0,$position).'/';
		}
	}

	if (CMS_SPECIFIC=='Joomla'){
		// *** Special CSS file for Joomla ***
		JHTML::stylesheet('gedcom_joomla.css', CMS_ROOTPATH);
	} elseif (CMS_SPECIFIC=='CMSMS'){
		// *** Stylesheet links outside header won't validate. styling will be managed in CMS ***
	} else {
		echo '<link href="'.CMS_ROOTPATH.'gedcom.css" rel="stylesheet" type="text/css">';
	}

	if (CMS_SPECIFIC!='CMSMS') {
		echo '<link href="'.CMS_ROOTPATH.'print.css" rel="stylesheet" type="text/css" media="print">';
		echo '<link rel="shortcut icon" href="'.CMS_ROOTPATH.'images/favicon.ico" type="image/x-icon">';
	}
	
	if (isset($user["group_birthday_rss"]) AND $user["group_birthday_rss"]=="j"){
		$language_rss='en'; if (isset($_SESSION['language'])){ $language_rss=$_SESSION['language']; }
		echo '<link rel="alternate" type="application/rss+xml" title="Birthdaylist" href="'.CMS_ROOTPATH.'birthday_rss.php?lang='.$language_rss.'" >';
	}

	// *** url_rewrite variabele ***
	// *** urlpart[0] = (family) database, urlpart[1] = next variabale, etc. ***
	if ($humo_option["url_rewrite"]=="j"){
		// *** Search variables in: http://127.0.0.1/humo-php/family/F100/humo2_/I10 ***
		$url_path2=$_SERVER['REQUEST_URI'];
		$url_path2 = str_replace("/family/", "~!", $url_path2);
		$url_path2 = str_replace("/tree_index/", "~!", $url_path2);
		$url_path2 = str_replace("/index/", "~!", $url_path2);
		$url_path2 = str_replace("/list/", "~!", $url_path2);
		$url_path2 = str_replace("/list_names/", "~!", $url_path2);
		$url_position=strpos($url_path2,'!');
		if ($url_position){
			$urlpart1=substr($url_path2,$url_position+1,-1);   //    humo2_/F100/I10
			$urlpart = explode("/", $urlpart1);
		}
	}

	// *** Family tree choice ***
	global $database;
	$database='';
	if (isset($urlpart[0]) AND $urlpart[0]!='standaard'){ $database=$urlpart[0]; } // *** url_rewrite ***
	if (isset($_GET["database"])){ $database=$_GET["database"]; }
	if (isset($_POST["database"])){ $database=$_POST["database"]; }
	if (isset($database) AND $database){
		// *** Check if family tree really exists ***
		$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='".safe_text($database)."'");
		if($datasql->rowCount()==1) { $_SESSION['tree_prefix']=$database; }
	}
	// *** No family tree selected yet ***
	if (!isset($_SESSION["tree_prefix"]) OR $_SESSION['tree_prefix']=='' ){
		$_SESSION['tree_prefix']=''; // *** If all trees are blocked then session is empty ***

		// *** Find first family tree that's not blocked for this usergroup ***
		$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
		while(@$dataDb=$datasql->fetch(PDO::FETCH_OBJ)) {
			// *** Check is family tree is showed or hidden for user group ***
			$hide_tree_array=explode(";",$user['group_hide_trees']);
			$hide_tree=false; if (in_array($dataDb->tree_id, $hide_tree_array)) $hide_tree=true;
			if ($hide_tree==false){	
				$_SESSION['tree_prefix']=$dataDb->tree_prefix;
				break;
			}
		}
	}

	// *** Check if tree is allowed for visitor and Google etc. ***
	$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' AND tree_prefix='".safe_text($_SESSION['tree_prefix'])."'");
	@$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
	$hide_tree_array=explode(";",$user['group_hide_trees']);
	$hide_tree=false; if (in_array(@$dataDb->tree_id, $hide_tree_array)) $hide_tree=true;

	$_SESSION['tree_id']=''; $tree_id='';
	if ($hide_tree){
		$_SESSION['tree_prefix']='';
		$_SESSION['tree_id']='';
		$tree_id='';
	}
	elseif (isset($dataDb->tree_id)){
		$_SESSION['tree_id']=$dataDb->tree_id;
		$tree_id=$dataDb->tree_id;
	}

	// *** Set variabele for queries ***
	$tree_prefix_quoted = safe_text($_SESSION['tree_prefix']);

	/*
	// *****************************************************************
	// Use these lines to show a background picture for EACH FAMILY TREE
	// *****************************************************************
	print '<style type="text/css">';
	$picture= "pictures/".$_SESSION['tree_prefix'].".jpg";
	print " body { background-image: url($picture);}";
	print "</style>";
	*/

	//echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/sliderbar/slider.js"></script>';

	if(strpos($_SERVER['REQUEST_URI'],"STAR")!== false OR 
		strpos($_SERVER['REQUEST_URI'],"maps")!== false OR
		strpos($_SERVER['REQUEST_URI'],"HOUR")!== false OR    
		$user['group_pictures']=='j') { 
		// if lightbox activated or descendant chart or hourglass chart or google maps is used --> load jquery
		echo '	<script src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery-1.8.0.min.js"></script> ';
	}
	if(strpos($_SERVER['REQUEST_URI'],"STAR")!== false OR 
		strpos($_SERVER['REQUEST_URI'],"HOUR")!== false OR 
		strpos($_SERVER['REQUEST_URI'],"maps")!== false) { 
		// if descendant chart or hourglass chart or google maps used --> load additional jquery modules for slider
		echo ' <link rel="stylesheet" href="'.CMS_ROOTPATH.'include/jqueryui/css/hot-sneaks/jquery-ui-1.8.23.custom.css"> ';
		echo ' <script src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery-ui-1.8.23.custom.min.js"></script> ';
		echo ' <script src="'.CMS_ROOTPATH.'include/jqueryui/development-bundle/ui/minified/jquery.ui.widget.min.js"></script> ';
		echo ' <script src="'.CMS_ROOTPATH.'include/jqueryui/development-bundle/ui/minified/jquery.ui.mouse.min.js"></script> ';
		if(strpos($_SERVER['REQUEST_URI'],"STAR")!== false OR strpos($_SERVER['REQUEST_URI'],"HOUR")!== false) { // load slider for desc./hourglass chart
			echo ' <script src="'.CMS_ROOTPATH.'include/jqueryui/development-bundle/ui/jquery.ui.slider.js"></script> ';
		}
		if(strpos($_SERVER['REQUEST_URI'],"maps")!== false) { // load slider for google maps
			echo ' <script src="'.CMS_ROOTPATH.'include/jqueryui/development-bundle/ui/jquery.ui.gslider.js"></script> ';
		}
		echo ' <script src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery.ui.touch-punch.min.js"></script> ';
	}
 
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'fontsize.js"></script>';

	// *** Style sheet select ***
	include_once(CMS_ROOTPATH."styles/sss1.php");

	// *** Pop-up menu ***
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/popup_menu/popup_menu.js"></script>';
	if (CMS_SPECIFIC=='Joomla'){
		JHTML::stylesheet('popup_menu.css', CMS_ROOTPATH.'include/popup_menu/');
	} elseif (CMS_SPECIFIC=='CMSMS'){
		// Do nothing. stylesheet links outside header won't validate. styling will be managed in CMS
	} else {
		echo '<link rel="stylesheet" type="text/css" href="'.CMS_ROOTPATH.'include/popup_menu/popup_menu.css">';
	}

	// *** Photo lightbox effect ***
	if ($user['group_pictures']=='j'){
		//echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/lightbox/js/jquery.min.js"></script>';
		echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/lightbox/js/slimbox2.js"></script>';
		echo '<link rel="stylesheet" href="'.CMS_ROOTPATH.'include/lightbox/css/slimbox2.css" type="text/css" media="screen">';
	}

	if (!CMS_SPECIFIC){
		print "</head>\n";
		print "<body onload='checkCookie()'>\n";
	}

	include_once(CMS_ROOTPATH."include/db_functions_cls.php");
	$db_functions = New db_functions;
	$db_functions->set_tree_prefix($tree_prefix_quoted);
	$db_functions->set_tree_id($_SESSION['tree_id']);

	echo '<div class="silverbody">'; 
} // *** End of PDF export check ***

?>