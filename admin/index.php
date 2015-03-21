<?php
//error_reporting(E_ALL);

/**
* This is the admin web entry point for HuMo-gen.
*
* If you are reading this in your web browser, your server is probably
* not configured correctly to run PHP applications!
*
* See the manual for basic setup instructions
*
* http://www.huubmons.nl/software/
*
* ----------
*
* Copyright (C) 2008-2014 Huub Mons,
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

/* *** CMS_SPECIFIC: when run from CMS, this will contain it's name. ***
	Names:
		- CMS names used for now are 'Joomla' and 'CMSMS'.
	Usage:
		- Code for all CMS: if (CMS_SPECIFIC) {}
		- Code for one CMS: if (CMS_SPECIFIC == 'Joomla') {}
		- Code NOT for CMS: if (!CMS_SPECIFIC) {}
*/

if (!defined("CMS_SPECIFIC")) define("CMS_SPECIFIC", false);
if (!defined("CMS_ROOTPATH")) define("CMS_ROOTPATH", "../");
// *** When run from CMS, the path to the parent-map that contains this file should be given ***
if (!defined("CMS_ROOTPATH_ADMIN")) define("CMS_ROOTPATH_ADMIN", "");
if (!CMS_SPECIFIC){ session_start(); }

$page='index';

// *** Globals needed for Joomla ***
global $menu_admin, $tree_id, $language_file, $page, $language_tree, $data2Db;
global $treetext_name, $treetext_mainmenu_text, $treetext_mainmenu_source, $treetext_family_top, $treetext_family_footer, $treetext_id;

// *** Prevent Session hijacking ***
if (isset( $_SESSION['current_ip_address']) AND $_SESSION['current_ip_address'] != $_SERVER['REMOTE_ADDR']){
	// *** Remove login session if IP address is changed ***
	echo 'BEVEILIGDE BLADZIJDE/ SECURED PAGE';
	session_unset();
	session_destroy();
	die();
}

// *** Only logoff admin ***
if (isset($_GET['log_off'])){
	unset($_SESSION['user_name_admin']);
	unset($_SESSION['user_id_admin']);
	unset($_SESSION['group_id_admin']);
}

$ADMIN=TRUE; // *** Override "no database" message for admin ***
include_once(CMS_ROOTPATH."include/db_login.php"); // *** Database login ***

// *** Use UTF-8 database connection ***
//@mysql_query("SET NAMES 'utf8'", $db);
//@$dbh->query("SET NAMES 'utf8'");
 
include_once(CMS_ROOTPATH."include/safe.php"); // Variables

// *** Function to show family tree texts ***
include_once (CMS_ROOTPATH.'include/show_tree_text.php');

include_once(CMS_ROOTPATH."include/db_functions_cls.php");
$db_functions = New db_functions();

// *** Only load settings if database and table exists ***
$show_menu_left=false;
$popup=false;

$update_message='';

if (isset($database_check) AND @$database_check){  // otherwise we can't make $dbh statements
	$check_tables = $dbh->query("SELECT * FROM humo_settings");
	if ($check_tables){
		include_once(CMS_ROOTPATH."include/settings_global.php");
		$show_menu_left=true;
	}
}

// *** First installation: show menu if installation of tables is started ***
if (isset($_POST['install_tables2'])){ $show_menu_left=true; }

if (isset($database_check) AND @$database_check){  // otherwise we can't make $dbh statements
	// *** Update to version 4.6, in older version there is a dutch-named table: humo_instellingen ***
	$check_update = @$dbh->query("SELECT * FROM humo_instellingen");
	if ($check_update){ $page='update'; $show_menu_left=false; }

	// *** Check HuMo-gen database status ***
	// *** Change this value if the database must be updated ***
	if (isset($humo_option["update_status"])){ 
		if ($humo_option["update_status"]<8){ $page='update'; $show_menu_left=false; }
//		if ($humo_option["update_status"]<9){ $page='update'; $show_menu_left=false; }
	}

	if (isset($_GET['page']) AND ($_GET['page']=='editor_sources' OR $_GET['page']=='editor_place_select' OR $_GET['page']=='editor_person_select' OR $_GET['page']=='editor_user_settings')){
		$show_menu_left=false;
		$popup=true;
	}
}
 
// *** Set timezone ***
include_once(CMS_ROOTPATH."include/timezone.php"); // set timezone
timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Language selection for admin ***
$map=opendir(CMS_ROOTPATH.'languages/');
while (false!==($file = readdir($map))) {
	if (strlen($file)<6 AND $file!='.' AND $file!='..'){
		$language_select[]=$file;
		if (file_exists(CMS_ROOTPATH.'languages/'.$file.'/'.$file.'.mo')){
			$language_file[]=$file;
		}
		// *** Save language choice ***
		if (isset($_GET["language_choice"])){
			// *** Check if language file really exists, to prevent hack of website ***
			if ($_GET["language_choice"]==$file){ $_SESSION['save_language_admin'] = $file; }
		}
	}
}
closedir($map);

// *** Select admin language ***
$selected_language="en";
// *** Saved default language ***
if (isset($humo_option['default_language_admin'])
	AND file_exists(CMS_ROOTPATH.'languages/'.$humo_option['default_language_admin'].'/'.$humo_option['default_language_admin'].'.mo')){
	$selected_language=$humo_option['default_language_admin'];
}
// *** Safety: extra check if language exists ***
if (isset($_SESSION["save_language_admin"])
	AND file_exists(CMS_ROOTPATH.'languages/'.$_SESSION["save_language_admin"].'/'.$_SESSION["save_language_admin"].'.mo')){
	$selected_language=$_SESSION["save_language_admin"];
}

$language = array();
include(CMS_ROOTPATH.'languages/'.$selected_language.'/language_data.php');
 
// *** .mo language text files ***
include_once(CMS_ROOTPATH."languages/gettext.php");
// *** Load ***
$_SESSION["language_selected"]=$selected_language;
Load_default_textdomain();
//Load_textdomain('customer_domain', 'languages/'.$selected_language.'/'.$selected_language.'.mo');

// *** Process LTR and RTL variables ***
$dirmark1="&#x200E;";  //ltr marker
$dirmark2="&#x200F;";  //rtl marker
$rtlmarker="ltr";

// *** Switch direction markers if language is RTL ***
if($language["dir"]=="rtl") {
	$dirmark1="&#x200F;";  //rtl marker
	$dirmark2="&#x200E;";  //ltr marker
	$rtlmarker="rtl";
}


// *** Process login form ***
$fault='';
if (isset($_POST['username'])){
	$query = "SELECT * FROM humo_users WHERE user_name='" .$_POST["username"] ."' AND user_password='".MD5($_POST["paswoord"])."'";
	$result = $dbh->query($query);
	if ($result->rowCount() > 0){
		@$resultDb=$result->fetch(PDO::FETCH_OBJ);
		$_SESSION['user_name_admin'] = safe_text($_POST["username"]);
		$_SESSION['user_id_admin'] = $resultDb->user_id;
		$_SESSION['group_id_admin'] = $resultDb->user_group_id;

		// *** Add login in logbook ***
		$log_date=date("Y-m-d H:i");
		$sql="INSERT INTO humo_user_log SET
			log_date='$log_date',
			log_username='".safe_text($_POST["username"])."',
			log_ip_address='".$_SERVER['REMOTE_ADDR']."',
			log_user_admin='admin'";
		@$dbh->query($sql);
	}
	else{
		// *** No valid user or password ***
		$fault='<p align="center"><font color="red">'.__('Please enter a valid username or password. ').'</font>';
	}
}


// *** Login check ***
$group_administrator=''; $group_edit_trees=''; //$group_editor='';
if(isset($database_check) AND $database_check) {
	if (isset($_SERVER["PHP_AUTH_USER"])){
		// *** Logged in using .htacess ***
 
		// *** Standard group permissions ***
		$group_administrator='j'; $group_edit_trees='';
		//$group_editor='j';

		// *** If username = editor then change group permissions ***
		//if ($_SERVER["PHP_AUTH_USER"]=='editor'){
		//	$group_administrator='n';
		//	$group_editor='j';
		//}

		// *** If .htaccess is used, check usergroup for admin rights ***
		@$query = "SELECT * FROM humo_users LEFT JOIN humo_groups
			ON humo_users.user_group_id=humo_groups.group_id
			WHERE humo_users.user_name='".$_SERVER["PHP_AUTH_USER"]."'";
		@$result = $dbh->query($query);
		if (@$result->rowCount() > 0){
			@$resultDb=$result->fetch(PDO::FETCH_OBJ);
			$group_administrator=$resultDb->group_admin;

			// *** Check if user is a editor ***
			//$group_editor='j'; if (isset($resultDb->group_editor)){ $group_editor=$resultDb->group_editor; }
			$group_edit_trees=''; if (isset($resultDb->group_edit_trees)){ $group_edit_trees=$resultDb->group_edit_trees; }

			// *** Edit family trees [USER SETTING] ***
			if (isset($resultDb->user_edit_trees) AND $resultDb->user_edit_trees){
				if ($group_edit_trees) $group_edit_trees.=';'.$resultDb->user_edit_trees;
					else $group_edit_trees=$resultDb->user_edit_trees;
			}

		}   
	}
	elseif($page=='update') {
		// *** No log in, update procedure (group table will be changed) ***
	}
	else{
		// *** Logged in using PHP-MySQL ***
		@$query = "SELECT * FROM humo_users";
		@$result = $dbh->query($query);
		if($result !== FALSE) {
            if($result->rowCount() > 0) {
				// *** humo-users table exists, check admin log in ***
				//if (isset($_SESSION["group_id_admin"]) AND $_SESSION["group_id_admin"] == "1") {
				if (isset($_SESSION["group_id_admin"])) {
				// *** Logged in as admin... ***

					// *** Read group settings ***
					$groepsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='".$_SESSION["group_id_admin"]."'");
					@$groepDb=$groepsql->fetch(PDO::FETCH_OBJ);

					// *** Check if user is an administrator ***
					$group_administrator=$groepDb->group_admin;
					if ($group_administrator!='j') $page='login';

					// *** Check if user is an editor ***
					//if (isset($groepDb->group_editor)){
					//	$group_editor=$groepDb->group_editor;
					//	if ($group_editor=='j'){ $page=''; }
					//}

					// *** Edit family trees [GROUP SETTING] ***
					if (isset($groepDb->group_edit_trees)){ $group_edit_trees=$groepDb->group_edit_trees; $page=''; }
					// *** Edit family trees [USER SETTING] ***
					$user_result2=$dbh->query("SELECT * FROM humo_users WHERE user_id=".$_SESSION['user_id_admin']);
					$resultDb=$user_result2->fetch(PDO::FETCH_OBJ);
					// *** Edit family trees [USER SETTING] ***
					if (isset($resultDb->user_edit_trees) AND $resultDb->user_edit_trees){
						if ($group_edit_trees) $group_edit_trees.=';'.$resultDb->user_edit_trees;
							else $group_edit_trees=$resultDb->user_edit_trees;
					}

				}
				else{
					// *** Show log in screen ***
					$page='login';
				} 
			}
		}
		else{
			// *** No user table: probably first installation: everything will be visible! ***
		}  

	}
}

// *** Save ip address in session to prevent session hijacking ***
if( isset( $_SESSION['current_ip_address'] ) == FALSE ){
	$_SESSION['current_ip_address'] = $_SERVER['REMOTE_ADDR'];
}

if (!CMS_SPECIFIC){
	// *** Generate header of HTML pages ***
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">'."\n";
	
	$html_text="\n<html>\n";
	if($language["dir"]=="rtl") {   // right to left language
		$html_text="\n<html dir='rtl'>\n";
	}
	if (isset($screen_mode) AND ($screen_mode=="STAR" OR $screen_mode=="STARSIZE")){
		$html_text="\n<html>\n";
	}
	echo $html_text;
	//echo "<html>\n";
	echo "<head>\n";
	echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">'."\n";
	echo '<title>'.__('Administration').'</title>'."\n";
	echo '<link href="'.CMS_ROOTPATH.'images/favicon.ico" rel="shortcut icon" type="image/x-icon">';
	echo '<link href="admin.css" rel="stylesheet" type="text/css">';
	echo '<link href="statistics/style.css" rel="stylesheet" type="text/css">'; // STYLE SHEET VOOR GRAFIEK
	echo '<link href="admin_print.css" rel="stylesheet" type="text/css" media="print">';

	//echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/lightbox/js/jquery.min.js"></script>';

	echo '<script src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery-1.8.0.min.js"></script> ';
	echo '<script src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery.sortable.min.js"></script>';
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/lightbox/js/slimbox2.js"></script>';
	echo '<link rel="stylesheet" href="'.CMS_ROOTPATH.'include/lightbox/css/slimbox2.css" type="text/css" media="screen">';

	echo '<script type="text/javascript" src="include/popup_merge.js"></script>';

	// *** Main menu pull-down ***
	//echo '<link rel="stylesheet" type="text/css" href="../popup_menu/popup_menu.css">';
	echo '<link rel="stylesheet" type="text/css" href="'.CMS_ROOTPATH.'include/popup_menu/popup_menu.css">';

	// *** Pop-up menu ***
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/popup_menu/popup_menu.js"></script>';

	echo '</head>';

	// *** Close pop-up screen and update main screen ***
	if (isset($_GET['page']) AND $_GET['page']=='close_popup'){
		echo '<script type="text/javascript">';

		$page_link='editor';
		// *** Also add these links in "Close source screen" link ***
		if (isset($_GET['connect_sub_kind'])){
			if ($_GET['connect_sub_kind']=='address_source') $page_link='edit_addresses';
			//if ($_GET['connect_sub_kind']=='pers_address_source') $page_link='edit_addresses';
			//if ($_GET['connect_sub_kind']=='fam_address_source') $page_link='edit_addresses';
			if ($_GET['connect_sub_kind']=='pers_event_source') $page_link='editor&event_person=1';
			if ($_GET['connect_sub_kind']=='fam_event_source') $page_link='editor&event_family=1';
		}
		if (isset($_GET['event_person']) AND $_GET['event_person']=='1') $page_link='editor&event_person=1#event_person_link';
		if (isset($_GET['event_family']) AND $_GET['event_family']=='1') $page_link='editor&event_family=1#event_family_link';

		echo 'function redirect_to(where, closewin){
			opener.location= \'index.php?page='.$page_link.'\' + where;
			if (closewin == 1){ self.close(); }
		}';
		echo '</script>';

		//echo '<body onload="redirect_to(\'index.php\',\'1\')">';
		echo '<body onload="redirect_to(\'\',\'1\')">';
		
		die();
	}
	else{
		echo '<body class="humo">';
	}

}
else{
	JHTML::stylesheet('admin_joomla.css', CMS_ROOTPATH.'admin/');
	JHTML::stylesheet('v1.css', CMS_ROOTPATH.'admin/menu/');
	JHTML::stylesheet('style.css', CMS_ROOTPATH.'admin/statistics/');

	// *** Main menu pull-down ***
	if (CMS_SPECIFIC!='CMSMS'){
		JHTML::stylesheet('popup_menu.css', CMS_ROOTPATH.'include/popup_menu/');
	}

	// *** Pop-up menu ***
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/popup_menu/popup_menu.js"></script>';
}

// *** Show top menu ***

if (CMS_SPECIFIC=='Joomla'){
	$path_tmp='index.php?option=com_humo-gen&amp;task=admin&amp;';
}
else{
	$path_tmp='index.php?';
}

$top_dir = ''; if($language["dir"]=="rtl") { $top_dir = 'style = "text-align:right" '; }
echo '<div id="humo_top" '.$top_dir.'>';
	//echo '<img src="'.CMS_ROOTPATH_ADMIN.'images/humo-gen-small.gif" align="left" alt="logo">';
	echo '<img src="'.CMS_ROOTPATH_ADMIN.'images/humo-gen-25a.png" align="left" alt="logo" height="45px">';
	if (isset($database_check) AND $database_check) { // Otherwise we can't make $dbh statements

		// *** Enable/ disable HuMo-gen update check ***
		if (isset($_POST['enable_update_check_change'])){
			if (isset($_POST['enable_update_check'])){
				$update_last_check='2012-01-01';
				$update_text='';
				$update_text.= ' <a href="'.$path_tmp.'page=install_update&update_check=1">'.__('Update options').'</a>';
			}
			else{
				$update_last_check='DISABLED';
				$update_text= '  '.__('HuMo-gen update check is disabled.');
				$update_text.= ' <a href="'.$path_tmp.'page=install_update&update_check=1">'.__('Update options').'</a>';
			}

			$result = $dbh->query("UPDATE humo_settings
				SET setting_value='".$update_text."'
				WHERE setting_variable='update_text'");
			$result = $dbh->query("UPDATE humo_settings
				SET setting_value='".$update_last_check."'
				WHERE setting_variable='update_last_check'");
			$humo_option['update_last_check']=$update_last_check;
			//$humo_option['update_text']=$update_text;
		}

		// *** Check if installation is completed, before checking for an update ***
		$check_update = @$dbh->query("SELECT * FROM humo_settings");
		if ($check_update AND $page!='login' AND $page!='update' AND $popup==false){

			// *** Update check, once a day ***

			// *** Manual check for update ***
			//if (isset($_GET['update_check'])){
			if (isset($_GET['update_check']) AND $humo_option['update_last_check']!='DISABLED'){
				// *** Update settings ***
				$result = @$dbh->query("UPDATE humo_settings
					SET setting_value='2012-01-01'
					WHERE setting_variable='update_last_check'");
				$humo_option['update_last_check']='2012-01-01';
			}

			// *** Update file, example ***
			// echo "version=4.8.4\r\n";
			// echo "version_date=2012-09-02\r\n";
			// echo "test=testline";

			// 86400 = 1 day. yyyy-mm-dd
			//if (strtotime ("now") - strtotime($humo_option['update_last_check']) > 86400 ){
			if ($humo_option['update_last_check']!='DISABLED' AND strtotime ("now") - strtotime($humo_option['update_last_check']) > 86400 ){
				$link_name=str_replace(' ', '_', $_SERVER['SERVER_NAME']);
				$link_versie=str_replace(' ', '_', $humo_option["version"]);

				// *** Use update file directly from humo-gen website ***
				$update_file='http://www.humo-gen.com/update/index.php?status=check_update&website='.$link_name.'&version='.$link_versie;

				// *** Copy update data from humo-gen website to local website ***
				if(function_exists('curl_exec')){
					$source='http://www.humo-gen.com/update/index.php?status=check_update&website='.$link_name.'&version='.$link_versie;
					$update_file='update/temp_update_check.php';
					$resource = curl_init();
					curl_setopt($resource, CURLOPT_URL, $source);
					curl_setopt($resource, CURLOPT_HEADER, false);
					curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
					//curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 30);
					curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 20);
					$content = curl_exec($resource);
					curl_close($resource);
					if($content != ''){
						$fp = @fopen($update_file, 'w');
						$fw = @fwrite($fp, $content);
						@fclose($fp);
					}
				}

				if ($f = @fopen($update_file, 'r')){
					// *** Used for automatic update procedure ***
					$update['up_to_date']='no';

					// *** HuMo-gen version ***
					$update['version']='';
					$update['version_date']='';
					$update['version_auto_download']='';

					// *** HuMo-gen beta version ***
					$update['beta_version']='';
					$update['beta_version_date']='';
					$update['beta_version_auto_download']='';

					while(!feof($f)) { 
						$update_data = fgets( $f, 4096 );
						$update_array=explode("=",$update_data);

						// *** HuMo-gen version ***
						if ($update_array[0]=='version'){ $update['version']=trim($update_array[1]); }
						if ($update_array[0]=='version_date'){ $update['version_date']=trim($update_array[1]); }
						if ($update_array[0]=='version_download'){ $update['version_download']=trim($update_array[1]); }
						if ($update_array[0]=='version_auto_download'){ $update['version_auto_download']=trim($update_array[1]); }

						// *** HuMo-gen beta version ***
						if ($update_array[0]=='beta_version'){ $update['beta_version']=trim($update_array[1]); }
						if ($update_array[0]=='beta_version_date'){ $update['beta_version_date']=trim($update_array[1]); }
						if ($update_array[0]=='beta_version_download'){ $update['beta_version_download']=trim($update_array[1]); }
						if ($update_array[0]=='beta_version_auto_download'){ $update['beta_version_auto_download']=trim($update_array[1]); }
					}
					fclose($f);

					// *** 1) Standard text: HuMo-gen up-to-date ***
					$update['up_to_date']='yes';
					$update_text= '  '.__('HuMo-gen is up-to-date!');
					$update_text.= ' <a href="'.$path_tmp.'page=install_update&update_check=1">'.__('Update options').'</a>';

					// *** 2) First priority: check for normal HuMo-gen update ***
					if (strtotime ($update['version_date'])-strtotime($humo_option["version_date"])>0){
						$update['up_to_date']='no';
						$update_text= ' <a href="'.$path_tmp.'page=install_update&update_check=1">'.__('Update available').' ('.$update['version'].')!</a>';
					}
					// *** 3) Second priority: check for Beta version update ***
					elseif (strtotime ($update['beta_version_date'])-strtotime($humo_option["version_date"])>0){
						$update['up_to_date']='yes';
						$update_text= ' <a href="'.$path_tmp.'page=install_update&update_check=1">'.__('Beta version available').' ('.$update['beta_version'].')!</a>';
					}

					// *** Update settings ***
					$update_last_check=date("Y-m-d");
					$result = $dbh->query("UPDATE humo_settings
						SET setting_value='".safe_text($update_last_check)."'
						WHERE setting_variable='update_last_check'");

					// *** Remove temporary file, used for curl method ***
					if (file_exists('update/temp_update_check.php')) unlink ('update/temp_update_check.php');
				}
				else{
					$update_text= '  '.__('Online version check unavailable.');
					//$update_text.= ' <a href="'.$path_tmp.'page=install_update&update_check=1">'.__('Update options').'</a>';

				if(!function_exists('curl_exec')) $update_text.=' Extension php_curl.dll is disabled.';
				elseif (!is_writable('update')) $update_text.=' Folder admin/update/ is read only.';

					// *** Update settings, only check for update once a day ***
					$update_last_check=date("Y-m-d");
					$result = $dbh->query("UPDATE humo_settings
						SET setting_value='".safe_text($update_last_check)."'
						WHERE setting_variable='update_last_check'");
				}

				$result = $dbh->query("UPDATE humo_settings
					SET setting_value='".safe_text($update_text)."'
					WHERE setting_variable='update_text'");

				$update_text.=' *';
			}
			else{
				// No online check now, use saved text...
				$update_text=$humo_option["update_text"];
			}
			echo $update_text;
		}
	}
	// ******************
	// *** START MENU ***
	// ******************

	if ($page!='login' AND $page!='update'){
		if (isset($_GET['page'])){ $page=$_GET['page']; }
		if (isset($_POST['page'])){ $page=$_POST['page']; }
	}

	echo '<div id="humo_menu">';
	echo '<ul class="humo_menu_item">';

		// *** Menu ***
		if ($popup==false){
			$select_top='';
			if ($page=='admin'){ $select_top=' id="current_top"'; }
			echo '<li>';
				echo '<div class="'.$rtlmarker.'sddm">';
					echo '<a href="'.$path_tmp.'page=admin"';
					echo ' onmouseover="mopen(event,\'m1x\',\'?\',\'?\')"';
					echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Main menu').'</a>';
					echo '<div id="m1x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
					echo '<ul class="humo_menu_item2">';

						if ($group_administrator=='j'){
							$menu_item=''; if ($page=='admin'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=admin">'.__('Administration').' - '.__('Main menu').'</a>';
						}

						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp2='index.php?option=com_humo-gen';
						}
						else{
							$path_tmp2=CMS_ROOTPATH.'index.php';
						}

						echo '<li><a href="'.$path_tmp2.'">'.__('Website').'</a>';

						if (isset($_SESSION["user_name_admin"])) {
							if (CMS_SPECIFIC=='Joomla'){
								$path_tmp2='index.php?option=com_humo-gen&amp;task=admin&amp;log_off=1';
							}
							else{
								$path_tmp2='index.php?log_off=1';
							}
							$menu_item=''; if ($page=='check'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp2.'">'.__('Logoff').'</a>';
						}
					echo '</ul>';
				echo '</div>';
			echo '</div>';
			echo '</li>';
		}
		elseif ($page=='editor_sources'){
			// *** Pop-up screen is shown, show button to close pop-up screen ***
			$select_top='';
			if ($page=='backup'){ $select_top=' id="current_top"'; }
			echo '<li>';
			echo '<div class="'.$rtlmarker.'sddm">';
				echo '<a href="'.$path_tmp.'page=close_popup';

				// *** Return link to addresses ***
				if (isset($_GET['connect_sub_kind']) AND $_GET['connect_sub_kind']=='address_source')
					echo '&connect_sub_kind=address_source';

				// *** Return link to person events ***
				if (isset($_GET['event_person']) AND $_GET['event_person']=='1') echo '&event_person=1';
				// *** Return link to family events ***
				if (isset($_GET['event_family']) AND $_GET['event_family']=='1') echo '&event_family=1';

				echo '">'.__('Close source editor').'</a>';
			echo '</div>';
			echo '</li>';
		}

		if ($show_menu_left==true and $page!='login'){

			// POP-UP MENU
			if ($group_administrator=='j'){
				$select_top='';
				if ($page=='install'){ $select_top=' id="current_top"'; }
				if ($page=='settings'){ $select_top=' id="current_top"'; }
				if ($page=='thumbs'){ $select_top=' id="current_top"'; }
				if ($page=='links'){ $select_top=' id="current_top"'; }
				if ($page=='language_editor'){ $select_top=' id="current_top"'; }
				if ($page=='prefix_editor'){ $select_top=' id="current_top"'; }
				if ($page=='google_maps'){ $select_top=' id="current_top"'; }
				echo '<li>';
					echo '<div class="'.$rtlmarker.'sddm">';
					echo '<a href="'.$path_tmp.'page=admin"';
					echo ' onmouseover="mopen(event,\'m2x\',\'?\',\'?\')"';
					echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Control').'</a>';
					echo '<div id="m2x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
					echo '<ul class="humo_menu_item2">';

						$menu_item=''; if ($page=='install'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=install">'.__('Install').'</a></li>';

						$menu_item=''; if ($page=='settings'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=settings">'.__('Settings').'</a>';

						$menu_item=''; if ($page=='thumbs'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=thumbs">'.__('Create thumbnails').'</a>';

						$menu_item=''; if ($page=='links'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=links">'.__('Extra links').'</a>';

						// *** Language Editor ***
						$menu_item=''; if ($page=='language_editor'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=language_editor">'.__('Language editor').'</a>';

						// *** Prefix Editor ***
						$menu_item=''; if ($page=='prefix_editor'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=prefix_editor">'.__('Prefix editor').'</a>';

						// *** Language Editor ***
						$menu_item=''; if ($page=='google_maps'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=google_maps">'.__('Google maps').'</a>';

					echo '</ul>';
					echo '</div>';
				echo '</div>';
				echo '</li>';
			}

			// POP-UP MENU family tree
			$select_top='';
			if ($page=='tree'){ $select_top=' id="current_top"'; }
			if ($page=='user_notes'){ $select_top=' id="current_top"'; }
			if ($page=='check'){ $select_top=' id="current_top"'; }
			if ($page=='export'){ $select_top=' id="current_top"'; }
			echo '<li>';
			echo '<div class="'.$rtlmarker.'sddm">';
				echo '<a href="'.$path_tmp.'page=tree"';
				echo ' onmouseover="mopen(event,\'m3x\',\'?\',\'?\')"';
				echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Family trees').'</a>';

				echo '<div id="m3x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
					echo '<ul class="humo_menu_item2">';
						if ($group_administrator=='j'){
							$menu_item=''; if ($page=='tree'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=tree">'.__('Family trees').'</a>';

							$menu_item=''; if ($page=='user_notes'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=user_notes">'.__('User notes').'</a>';

							$menu_item=''; if ($page=='check'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=check">'.__('Data check').'</a>';

							$menu_item=''; if ($page=='export'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=export">'.__('Gedcom export').'</a>';
						}

					echo '</ul>';
				echo '</div>';
				echo '</div>';
			echo '</li>';

			// POP-UP MENU editor
			$select_top='';
			if ($page=='editor'){ $select_top=' id="current_top"'; }
			if ($page=='edit_sources'){ $select_top=' id="current_top"'; }
			if ($page=='edit_repositories'){ $select_top=' id="current_top"'; }
			if ($page=='edit_addresses'){ $select_top=' id="current_top"'; }
			if ($page=='edit_places'){ $select_top=' id="current_top"'; }
			echo '<li>';
			echo '<div class="'.$rtlmarker.'sddm">';
				echo '<a href="'.$path_tmp.'page=editor"';
				echo ' onmouseover="mopen(event,\'m3xa\',\'?\',\'?\')"';
				// *** Changed text "EDITOR" into "Editor" ***
				$editor_text=ucfirst(strtolower(__('EDITOR')));
				echo ' onmouseout="mclosetime()"'.$select_top.'>'.$editor_text.'</a>';

				echo '<div id="m3xa" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
					echo '<ul class="humo_menu_item2">';

						$menu_item=''; if ($page=='editor'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=editor">'.$editor_text.'</a>';

						// *** Sources ***
						$menu_item=''; if ($page=='edit_sources'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=edit_sources">* '.__('Sources')."</a>";

						// *** Repositories ***
						$menu_item=''; if ($page=='edit_repositories'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=edit_repositories">* '.__('Repositories')."</a>";

						// *** Addresses ***
						$menu_item=''; if ($page=='edit_addresses'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=edit_addresses">* '.__('Addresses')."</a>";

						// *** Place editor ***
						$menu_item=''; if ($page=='edit_places'){ $menu_item=' id="current"'; }
						echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=edit_places">* '.__('Rename places')."</a>";

					echo '</ul>';
				echo '</div>';
				echo '</div>';
			echo '</li>';

			// POP-UP MENU for users and usergroups
			if ($group_administrator=='j'){
				$select_top='';
				if ($page=='users'){ $select_top=' id="current_top"'; }
				if ($page=='groups'){ $select_top=' id="current_top"'; }
				echo '<li>';
				echo '<div class="'.$rtlmarker.'sddm">';
					echo '<a href="'.$path_tmp.'page=users"';
					echo ' onmouseover="mopen(event,\'m4x\',\'?\',\'?\')"';
					echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Users').'</a>';
					echo '<div id="m4x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
						echo '<ul class="humo_menu_item2">';

							$menu_item=''; if ($page=='users'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=users">'.__('Users').'</a>';

							$menu_item=''; if ($page=='groups'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=groups">'.__('Groups').'</a>';

						echo '</ul>';
					echo '</div>';
				echo '</div>';
				echo '</li>';
			}

			// POP-UP MENU for CMS categories and pages
			if ($group_administrator=='j'){
				$select_top='';
				//if ($page=='cms_categories'){ $select_top=' id="current_top"'; }
				if ($page=='cms_pages'){ $select_top=' id="current_top"'; }
				echo '<li>';
				echo '<div class="'.$rtlmarker.'sddm">';
					//echo '<a href="'.$path_tmp.'page=cms_pages"';
					//echo ' onmouseover="mopen(event,\'m5ax\',\'?\',\'?\')"';
					//echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Own pages').'</a>';
					echo '<a href="'.$path_tmp.'page=cms_pages"'.$select_top.'>'.__('CMS Own pages').'</a>';

					/*
					echo '<div id="m5ax" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
						echo '<ul class="humo_menu_item2">';

							$menu_item=''; if ($page=='cms_categories'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=cms_categories">'.__('Categories').'</a>';

							$menu_item=''; if ($page=='cms_pages'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=cms_pages">'.__('Pages').'</a>';

						echo '</ul>';
					echo '</div>';
					*/

				echo '</div>';
				echo '</li>';
			}

			// POP-UP MENU for database backup
			if ($group_administrator=='j'){
				$select_top='';
				if ($page=='backup'){ $select_top=' id="current_top"'; }
				echo '<li>';
				echo '<div class="'.$rtlmarker.'sddm">';
					//echo '<a href="'.$path_tmp.'page=backup"';
					//echo ' onmouseover="mopen(event,\'m5x\',\'?\',\'?\')"';
					//echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Database backup').'</a>';
					echo '<a href="'.$path_tmp.'page=backup"'.$select_top.'>'.__('Database backup').'</a>';
					//echo '<div id="m5x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
					//	echo '<ul class="humo_menu_item2">';

					//		$menu_item=''; if ($page=='backup'){ $menu_item=' id="current"'; }
					//		echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=backup">'.__('Database backup').'</a>';

					//	echo '</ul>';
					//echo '</div>';
				echo '</div>';
				echo '</li>';
			}

			// POP-UP MENU for logs
			if ($group_administrator=='j'){
				$select_top='';
				if ($page=='log'){ $select_top=' id="current_top"'; }
				if ($page=='statistics'){ $select_top=' id="current_top"'; }
				echo '<li>';
				echo '<div class="'.$rtlmarker.'sddm">';
					echo '<a href="'.$path_tmp.'page=log"';
					echo ' onmouseover="mopen(event,\'m6x\',\'?\',\'?\')"';
					echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Logs').'</a>';
					echo '<div id="m6x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
						echo '<ul class="humo_menu_item2">';

							$menu_item=''; if ($page=='log'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=log">'.__('Log').'</a>';

							$menu_item=''; if ($page=='statistics'){ $menu_item=' id="current"'; }
							echo '<li'.$menu_item.'><a href="'.$path_tmp.'page=statistics">'.__('Statistics').'</a>';
						echo '</ul>';
					echo '</div>';
				echo '</div>';
				echo '</li>';
			}

		}

		if ($popup==false){
			// *** Country flags ***
			$select_top='';
			echo '<li>';
			echo '<div class="'.$rtlmarker.'sddm">';
				include(CMS_ROOTPATH.'languages/'.$selected_language.'/language_data.php');
				echo '<a href="index.php?option=com_humo-gen"';
				echo ' onmouseover="mopen(event,\'m40x\',\'?\',\'?\')"';
				//echo ' onmouseout="mclosetime()"'.$select_top.'>'.'<img src="'.CMS_ROOTPATH.'languages/'.$selected_language.'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none; height:14px"> '.$language["name"].'</a>';
				echo ' onmouseout="mclosetime()"'.$select_top.'>'.'<img src="'.CMS_ROOTPATH.'languages/'.$selected_language.'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none; height:14px"> </a>';
				//echo '<div id="m40x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
				echo '<div id="m40x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()" style="width:250px;">';
					echo '<ul class="humo_menu_item2">';
						for ($i=0; $i<count($language_file); $i++){
							// *** Get language name ***
							if ($language_file[$i] != $selected_language) {
								include(CMS_ROOTPATH.'languages/'.$language_file[$i].'/language_data.php');
								//echo '<li><a href="'.$path_tmp.'language_choice='.$language_file[$i].'">';
								echo '<li style="float:left; width:124px;"><a href="'.$path_tmp.'language_choice='.$language_file[$i].'">';

								echo '<img src="'.CMS_ROOTPATH.'languages/'.$language_file[$i].'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none;"> ';
								echo $language["name"];
								echo '</a>';
								echo '</li>';
							}
						}
					echo '</ul>';
				echo '</div>';
			echo '</div>';
			echo '</li>';
		}
		else{
			// *** Set language is popup window is used ***
			include(CMS_ROOTPATH.'languages/'.$selected_language.'/language_data.php');
			for ($i=0; $i<count($language_file); $i++){
				if ($language_file[$i] != $selected_language) {
					include(CMS_ROOTPATH.'languages/'.$language_file[$i].'/language_data.php');
				}
			}
		}

	echo '</ul>';
	echo '</div>';
	// *** END OF MENU ***

echo '</div>'; // *** End of humo_top ***


// *** Show selected page, default page = admin homepage ***
echo '<div id="content_admin">';
	define('ADMIN_PAGE', true); // *** Safety line ***

	// *** Editor group is only allowed to see editor screen ***
	//if ($group_administrator!='j' AND $group_editor=='j'){ $page='editor'; }

	if ($page=='install'){ include_once ("include/install.php"); }
	elseif ($page=='login'){ include_once ("include/login.php"); }
	elseif ($group_administrator=='j' AND $page=='tree'){ include_once ("include/trees.php"); }
	elseif ($page=='editor'){ $_GET['menu_admin']='person'; include_once ("include/editor.php"); }
	elseif ($page=='editor_sources'){ $_GET['menu_admin']='person'; include_once ("include/editor_sources.php"); }
	// NEW edit_sources for all source links...
	elseif ($page=='edit_sources'){ $_GET['menu_admin']='sources'; include_once ("include/editor.php"); }
	elseif ($page=='edit_repositories'){ $_GET['menu_admin']='repositories'; include_once ("include/editor.php"); }
	elseif ($page=='edit_addresses'){ $_GET['menu_admin']='addresses'; include_once ("include/editor.php"); }
	elseif ($page=='edit_places'){ $_GET['menu_admin']='places'; include_once ("include/editor.php"); }
	elseif ($page=='editor_place_select'){ $_GET['menu_admin']='places'; include_once ("include/editor_place_select.php"); }
	elseif ($page=='editor_person_select'){ $_GET['menu_admin']='marriage'; include_once ("include/editor_person_select.php"); }

	elseif ($page=='check'){ include_once ("include/tree_check.php"); }
	elseif ($page=='gedcom'){ include_once ("include/gedcom.php"); }
	elseif ($page=='settings'){ include_once ("include/settings_admin.php"); }
	elseif ($page=='thumbs'){ include_once ("include/thumbs.php"); }
	elseif ($page=='links'){ include_once ("include/links.php"); }
	elseif ($page=='users'){ include_once ("include/users.php"); }
	elseif ($page=='editor_user_settings'){ $_GET['menu_admin']='users'; include_once ("include/editor_user_settings.php"); }

	elseif ($page=='groups'){ include_once ("include/groups.php"); }
	elseif ($page=='cms_pages'){ include_once ("include/cms_pages.php"); }
	elseif ($page=='backup'){ include_once ("include/backup.php"); }
	elseif ($page=='user_notes'){ include_once ("include/user_notes.php"); }
	elseif ($page=='export'){ include_once ("include/gedcom_export.php"); }
	elseif ($page=='log'){ include_once ("include/log.php"); }
	elseif ($page=='language_editor'){ include_once ("include/language_editor.php"); }
	elseif ($page=='prefix_editor'){ include_once ("include/prefix_editor.php"); }
	elseif ($page=='google_maps'){ include_once ("include/make_db_maps.php"); }
	elseif ($page=='statistics'){ include_once ("include/statistics.php"); }
	elseif ($page=='install_update'){ include_once ("update/install_update.php"); }
	elseif ($page=='update'){ include_once ("include/update.php"); }

	// *** Edit event by person ***
	//elseif ($page=='editor_person_event'){ include_once ("include/editor_person_event.php"); }

	// *** Default page for editor ***
	//elseif ($group_administrator!='j' AND $group_editor=='j'){ $_GET['menu_admin']='person'; include_once ("include/editor.php"); }
	elseif ($group_administrator!='j' AND $group_edit_trees){ $_GET['menu_admin']='person'; include_once ("include/editor.php"); }

	// *** Default page for administrator ***
	else{ include_once ("include/index_inc.php"); }

echo '</div>';

if (!CMS_SPECIFIC){
	print "</body>\n";
	print "</html>";
}

?>