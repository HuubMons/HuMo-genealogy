<?php
/**
 * This is the main web entry point for HuMo-gen.
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
 * Copyright (C) 2008-2009 Huub Mons,
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
 
// *** Version line moved to settings_global.php ***

include_once("include/mobile_detect.php");
$detect = new Mobile_Detect();
$mob_redirect="0"; if(isset($_GET['mobile']) AND $_GET['mobile']=="1") $mob_redirect=1;
if ($detect->isMobile() AND !$detect->isTablet() AND $mob_redirect=="0") {
//if ($detect->isMobile() AND $detect->isTablet() AND $mob_redirect=="0") {
	// refer to mobile site
	$position=strrpos($_SERVER['PHP_SELF'],'/');
	$uri_path= substr($_SERVER['PHP_SELF'],0,$position);
	//header("Location: http://www.humo-gen.com/humo-gen-test/humo_mobile");
	header("Location: ".$uri_path."/humo_mobile");
}

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

// *** Replace the main index by an own CMS page ***
if (isset($humo_option["main_page_cms_id_".$selected_language])) {  
	if ($humo_option["main_page_cms_id_".$selected_language] == "") {
		include_once(CMS_ROOTPATH."include/mainindex_cls.php");
		$mainindex = new mainindex_cls();
		echo $mainindex->show_tree_index();
	}
	else {
		echo '<div id="mainmenu_centerbox">';
			// *** Show page ***
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
				WHERE page_id='".$humo_option["main_page_cms_id_".$selected_language]."' AND page_status!=''");
			$cms_pagesDb=$page_qry->fetch(PDO::FETCH_OBJ);
			echo $cms_pagesDb->page_text;
		echo '</div>';
	}
}
elseif (isset($humo_option["main_page_cms_id"]) AND $humo_option["main_page_cms_id"]){
	echo '<div id="mainmenu_centerbox">';

		// *** Show page ***
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
			WHERE page_id='".$humo_option["main_page_cms_id"]."' AND page_status!=''");
		$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
		echo $cms_pagesDb->page_text;

	echo '</div>';
}
else{

	// ***********************************************************************************************
	// ** Main index class ***
	// ***********************************************************************************************
	include_once(CMS_ROOTPATH."include/mainindex_cls.php");
	$mainindex = new mainindex_cls();

	// *** Show default HuMo-gen homepage ***
	echo $mainindex->show_tree_index();
}

// *** This line can be found in: index.php and tree_index.php ***
echo '<br><div class="humo_version">';
printf(__('This database is made by %s, a freeware genealogical  program'), '<a href="http://www.humo-gen.com">HuMo-gen</a>');
echo ' ('.$humo_option["version"].').<br>';
if (!$bot_visit){ printf(__('European law: %s HuMo-gen cookie information'),'<a href="info_cookies.php">'); }
echo '</a></div>';

include_once(CMS_ROOTPATH."footer.php");
?>