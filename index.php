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
 * Copyright (C) 2008-2023 Huub Mons,
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
include_once(CMS_ROOTPATH . "menu.php");



if ($page=='index') {

	// ***********************************************************************************************
	// ** Main index class ***
	// ***********************************************************************************************
	include_once(CMS_ROOTPATH . "include/mainindex_cls.php");
	$mainindex = new mainindex_cls();

	// *** Replace the main index by an own CMS page ***
	$text = '';
	if (isset($humo_option["main_page_cms_id_" . $selected_language]) and $humo_option["main_page_cms_id_" . $selected_language]) {
		// *** Show CMS page ***
		if (is_numeric($humo_option["main_page_cms_id_" . $selected_language])) {
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
			WHERE page_id='" . $humo_option["main_page_cms_id_" . $selected_language] . "' AND page_status!=''");
			$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
			$text = $cms_pagesDb->page_text;
		}
	} elseif (isset($humo_option["main_page_cms_id"]) and $humo_option["main_page_cms_id"]) {
		// *** Show CMS page ***
		if (is_numeric($humo_option["main_page_cms_id"])) {
			$page_qry = $dbh->query("SELECT * FROM humo_cms_pages
			WHERE page_id='" . $humo_option["main_page_cms_id"] . "' AND page_status!=''");
			$cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);
			$text = $cms_pagesDb->page_text;
		}
	}

	// *** Show slideshow ***
	if (isset($humo_option["slideshow_show"]) and $humo_option["slideshow_show"] == 'y') {
		$mainindex->show_slideshow();
	}

	if ($text) {
		// *** Can be used for extra box in lay-out ***
		echo '<div id="mainmenu_centerbox">';
		// *** Show CMS page ***
		echo $text;
		echo '</div>';
	} else {
		// *** Show default HuMo-genealogy homepage ***
		$mainindex->show_tree_index();
	}

	// *** Show HuMo-genealogy footer ***
	echo $mainindex->show_footer();

} elseif ($page == 'address') {
	/**
	 * MAy 2023: Added MVC system
	 * 
	 * 
	 */

	//include_once(CMS_ROOTPATH . "footer.php");

	// Global setting
	//require_once 'config/global.php';
	define("CONTROLLER_DEFAULT", "Address");
	define("ACTION_DEFAULT", "address");

	function routeController($controller)
	{

//include_once __DIR__ . '/include/db_functions_cls.php';
//$db_functions = new db_functions($dbh);


		switch ($controller) {
			case 'address':
				$strFileController = 'controller/addressController.php';
				require_once $strFileController;
				$controllerObj = new addressController();
				break;
			default:
				$strFileController = 'controller/addressController.php';
				require_once $strFileController;
				$controllerObj = new addressController();
				break;
		}
		return $controllerObj;
	}

	function launchAction($controllerObj)
	{
//TIJDELIJK
$_GET["action"]='detail';
		if (isset($_GET["action"])) {
			$controllerObj->run($_GET["action"]);
		} else {
			$controllerObj->run(ACTION_DEFAULT);
		}
	}

	// We load the controller and execute the action
	//if (isset($_GET["controller"])) {
	//	// We load the instance of the corresponding controller
	//	$controllerObj = routeController($_GET["controller"]);
	//	// We launch the action
	//	launchAction($controllerObj);
	//} else {
		// We load the default controller instance
		$controllerObj = routeController(CONTROLLER_DEFAULT);
		// We launch the action
		launchAction($controllerObj);
	//}
}



include_once(CMS_ROOTPATH . "footer.php");
