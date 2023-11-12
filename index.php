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

include_once(__DIR__ . "/views/header.php");

$menu = true;
// *** Hide menu in descendant chart shown in iframe in fanchart ***
if (isset($_GET['menu']) and $_GET['menu'] == "1") $menu = false;
if ($menu) include_once(__DIR__ . "/views/menu.php");

// Test lines
//echo $page;
//$page='list';


// *** Base controller ***
require __DIR__ . '/app/controller/Controller.php';
//$controllerObj = new Controller($dbh, $db_functions);



if ($page == 'index') {
    // ***********************************************************************************************
    // ** Main index class ***
    // ***********************************************************************************************

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
    //if (isset($humo_option["slideshow_show"]) and $humo_option["slideshow_show"] == 'y') {
    //    $mainindex->show_slideshow();
    //}

    if ($text) {
        // *** Show CMS page ***
        echo '<div id="mainmenu_centerbox">' . $text . '</div>';
    } else {
        // *** Show default HuMo-genealogy homepage ***
        //$mainindex->show_tree_index();
        include __DIR__ . '/views/tree_index.php';
    }
} elseif ($page == 'address') {
    require __DIR__ . '/app/controller/addressController.php';
    $controllerObj = new addressController($db_functions, $user);
    $data = $controllerObj->detail();
    require  __DIR__ . '/views/address.php';
} elseif ($page == 'addresses') {
    require __DIR__ . '/app/controller/addressesController.php';
    $controllerObj = new addressesController($dbh, $user, $tree_id);
    $data = $controllerObj->list();
    require __DIR__ . '/views/addresses.php';
} elseif ($page == 'ancestor_report') {
    require __DIR__ . '/app/controller/ancestor_reportController.php';
    $controllerObj = new ancestor_reportController($dbh);
    $data = $controllerObj->list($tree_id);
    require __DIR__ . '/views/ancestor_report.php';
} elseif ($page == 'ancestor_chart') {
    require __DIR__ . '/app/controller/ancestor_chartController.php';
    $controllerObj = new ancestor_chartController($dbh, $user);
    $data = $controllerObj->list($tree_id);
    require __DIR__ . '/views/ancestor_chart.php';
} elseif ($page == 'ancestor_sheet') {
    require __DIR__ . '/app/controller/ancestor_sheetController.php';
    $controllerObj = new ancestor_sheetController($dbh, $user);
    $data = $controllerObj->list($tree_id);
    require __DIR__ . '/views/ancestor_sheet.php';
} elseif ($page == 'birthday') {
    require __DIR__ . '/views/birthday_list.php';
} elseif ($page == 'cms_pages') {
    require __DIR__ . '/app/controller/cms_pagesController.php';
    $controllerObj = new CMS_pagesController($dbh, $user);
    $data = $controllerObj->list();
    require __DIR__ . '/views/cms_pages.php';
} elseif ($page == 'cookies') {
    require __DIR__ . '/views/cookies.php';
} elseif ($page == 'descendant') {
    require __DIR__ . '/app/controller/descendant_chartController.php';
    $controllerObj = new descendant_chartController();
    $data = $controllerObj->getFamily($dbh, $tree_id);
    require __DIR__ . '/views/descendant_chart.php';
} elseif ($page == 'family_rtf') {
    require __DIR__ . '/views/family_rtf.php';
} elseif ($page == 'family') {
    require __DIR__ . '/app/controller/familyController.php';
    $controllerObj = new familyController();
    $data = $controllerObj->getFamily($dbh, $tree_id);
    require __DIR__ . '/views/family.php';
} elseif ($page == 'fanchart') {
    require __DIR__ . '/views/fanchart.php';
} elseif ($page == 'help') {
    require __DIR__ . '/views/help.php';
} elseif ($page == 'hourglass') {
    require __DIR__ . '/app/controller/hourglassController.php';
    $controllerObj = new hourglassController();
    $data = $controllerObj->getHourglass($dbh, $tree_id);
    require __DIR__ . '/views/hourglass.php';
} elseif ($page == 'latest_changes') {
    require __DIR__ . '/app/controller/latest_changesController.php';
    $controllerObj = new latest_changesController($dbh);
    $data = $controllerObj->list($dbh, $tree_id);
    require __DIR__ . '/views/latest_changes.php';
} elseif ($page == 'list') {
    require __DIR__ . '/views/list.php';
} elseif ($page == 'list_places_families') {
    require __DIR__ . '/views/list_places_families.php';
} elseif ($page == 'list_names') {
    require __DIR__ . '/views/list_names.php';
} elseif ($page == 'login') {
    require __DIR__ . '/views/login.php';
} elseif ($page == 'mailform') {
    require __DIR__ . '/views/mailform.php';
} elseif ($page == 'maps') {
    require __DIR__ . '/views/maps.php';
} elseif ($page == 'photoalbum') {
    require __DIR__ . '/views/photoalbum.php';
} elseif ($page == 'register') {
    require __DIR__ . '/views/register.php';
} elseif ($page == 'relations') {
    require __DIR__ . '/views/relations.php';
} elseif ($page == 'report_outline') {
    require __DIR__ . '/app/controller/report_outlineController.php';
    $controllerObj = new report_outlineController();
    $data = $controllerObj->getFamily($dbh, $tree_id);
    require __DIR__ . '/views/outline_report.php';
} elseif ($page == 'settings') {
    require __DIR__ . '/views/user_settings.php';
} elseif ($page == 'statistics') {
    require __DIR__ . '/views/statistics.php';
} elseif ($page == 'sources') {
    require __DIR__ . '/app/controller/sourcesController.php';
    $controllerObj = new sourcesController($dbh);
    $data = $controllerObj->list($dbh, $tree_id, $user, $humo_option, $link_cls, $uri_path);
    require __DIR__ . '/views/sources.php';
} elseif ($page == 'source') {
    require __DIR__ . '/app/controller/sourceController.php';
    $controllerObj = new sourceController($dbh, $db_functions, $tree_id); // Using Controller.
    if (isset($_GET["id"])) $id = $_GET["id"]; // *** url_rewrite is disabled ***
    $data = $controllerObj->source($id);
    require __DIR__ . '/views/source.php';
} elseif ($page == 'timelines') {
    require __DIR__ . '/views/timelines.php';
} elseif ($page == 'tree_index') {
    require __DIR__ . '/views/tree_index.php';
}

echo '<br>';
include_once(__DIR__ . "/views/footer.php");
