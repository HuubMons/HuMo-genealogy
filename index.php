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
 * ----------
 *
 * Copyright (C) 2008-2022 Huub Mons,
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
 
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

// ***********************************************************************************************
// ** Main index class ***
// ***********************************************************************************************
include_once(CMS_ROOTPATH."include/mainindex_cls.php");
$mainindex = new mainindex_cls();

// *** Replace the main index by an own CMS page ***
$text='';
if (isset($humo_option["main_page_cms_id_".$selected_language]) AND $humo_option["main_page_cms_id_".$selected_language]) {
	// *** Show CMS page ***
	if (is_numeric($humo_option["main_page_cms_id_".$selected_language])){
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
			WHERE page_id='".$humo_option["main_page_cms_id_".$selected_language]."' AND page_status!=''");
		$cms_pagesDb=$page_qry->fetch(PDO::FETCH_OBJ);
		$text=$cms_pagesDb->page_text;
	}
}
elseif (isset($humo_option["main_page_cms_id"]) AND $humo_option["main_page_cms_id"]){
	// *** Show CMS page ***
	if (is_numeric($humo_option["main_page_cms_id"])){
		$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
			WHERE page_id='".$humo_option["main_page_cms_id"]."' AND page_status!=''");
		$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
		$text=$cms_pagesDb->page_text;
	}
}

// *** Show slideshow ***
if (isset($humo_option["slideshow_show"]) AND $humo_option["slideshow_show"]=='y'){
	$mainindex->show_slideshow();
}

if ($text){
	// *** Can be used for extra box in lay-out ***
	echo '<div id="mainmenu_centerbox">';
		// *** Show CMS page ***
		echo $text;
	echo '</div>';
}
else{
	// *** Show default HuMo-genealogy homepage ***
	$mainindex->show_tree_index();
}

// *** Show HuMo-genealogy footer ***
echo $mainindex->show_footer();

include_once(CMS_ROOTPATH."footer.php");
?>