<?php

/**
 * This is the admin web entry point for HuMo-genealogy.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the manual for basic setup instructions
 *
 * https://humo-gen.com
 *
 * ----------
 *
 * Copyright (C) 2008-2023 Huub Mons,
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
if (!CMS_SPECIFIC) {
    session_start();
    // *** Regenerate session id regularly to prevent session hacking ***
    session_regenerate_id();
}

$page = 'index';

// *** Globals needed for Joomla ***
global $menu_admin, $tree_id, $language_file, $page, $language_tree, $data2Db;
global $treetext_name, $treetext_mainmenu_text, $treetext_mainmenu_source, $treetext_family_top, $treetext_family_footer, $treetext_id;

// DISABLED because the SECURED PAGE message was shown regularly.
// *** Prevent Session hijacking ***
//if (isset( $_SESSION['current_ip_address']) AND $_SESSION['current_ip_address'] != $_SERVER['REMOTE_ADDR']){
//	// *** Remove login session if IP address is changed ***
//	echo 'BEVEILIGDE BLADZIJDE/ SECURED PAGE';
//		// *** Test ***
//		//echo '<br>'.$_SESSION['current_ip_address'].'<br>';
//		//echo $_SERVER['REMOTE_ADDR'];
//	session_unset();
//	session_destroy();
//	die();
//}

// *** Only logoff admin ***
if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name_admin']);
    unset($_SESSION['user_id_admin']);
    unset($_SESSION['group_id_admin']);
}

$ADMIN = TRUE; // *** Override "no database" message for admin ***
include_once(CMS_ROOTPATH . "include/db_login.php"); // *** Database login ***

include_once(CMS_ROOTPATH . "include/safe.php"); // Variables

// *** Function to show family tree texts ***
include_once(CMS_ROOTPATH . 'include/show_tree_text.php');

include_once(CMS_ROOTPATH . "include/db_functions_cls.php");
$db_functions = new db_functions();

// *** Added juli 2019: Person functions ***
include_once(CMS_ROOTPATH . "include/person_cls.php");

// *** Only load settings if database and table exists ***
$show_menu_left = false;
$popup = false;

$update_message = '';

if (isset($database_check) and @$database_check) {  // otherwise we can't make $dbh statements
    $check_tables = false;
    try {
        $check_tables = $dbh->query("SELECT * FROM humo_settings");
    } catch (Exception $e) {
        //
    }

    if ($check_tables) {
        include_once(CMS_ROOTPATH . "include/settings_global.php");

        // *** Added may 2020, needed for some user settings in admin section ***
        // *** At this moment there is no separation for front user and admin user... ***
        include_once(CMS_ROOTPATH . "include/settings_user.php"); // USER variables

        // **** Temporary update scripts ***

        // *** Check table user_notes ***
        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_user_notes');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        // *** Automatic update ***
        if (!isset($field['note_order'])) {
            $sql = "ALTER TABLE humo_user_notes CHANGE note_date note_new_date varchar(20) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes CHANGE note_time note_new_time varchar(25) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes CHANGE note_user_id note_new_user_id smallint(5);";
            $result = $dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes ADD note_changed_date varchar(20) CHARACTER SET utf8 AFTER note_new_user_id;";
            $result = $dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes ADD note_changed_time varchar(25) CHARACTER SET utf8 AFTER note_changed_date;";
            $result = $dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes ADD note_changed_user_id smallint(5) AFTER note_changed_time;";
            $result = $dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes ADD note_priority varchar(15) CHARACTER SET utf8 AFTER note_status;";
            $result = $dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes CHANGE note_status note_status varchar(15) CHARACTER SET utf8;";
            $result = $dbh->query($sql);

            // *** Add note_order ***
            $sql = "ALTER TABLE humo_user_notes ADD note_order smallint(5) AFTER note_id;";
            $result = $dbh->query($sql);

            // *** Add note_connect_kind = person/ family/ source/ repository ***
            $sql = "ALTER TABLE humo_user_notes ADD note_connect_kind varchar(20) CHARACTER SET utf8 AFTER note_tree_id;";
            $result = $dbh->query($sql);

            // *** Add note_kind = user/ editor ***
            $sql = "ALTER TABLE humo_user_notes ADD note_kind varchar(10) CHARACTER SET utf8 AFTER note_tree_id;";
            $result = $dbh->query($sql);

            // *** Change all existing note_connect_kind items into 'person' ***
            $sql = "UPDATE humo_user_notes SET note_connect_kind='person';";
            $result = $dbh->query($sql);

            // *** Change note_pers_gedcomnumber into: note_connect_id ***
            $sql = "ALTER TABLE humo_user_notes CHANGE note_pers_gedcomnumber note_connect_id VARCHAR(25) CHARACTER SET utf8;";
            $result = $dbh->query($sql);

            // *** Update tree_id, could be missing in some cases ***
            $sql = "SELECT * FROM humo_user_notes LEFT JOIN humo_trees ON note_tree_prefix=tree_prefix ORDER BY note_id;";
            $qry = $dbh->query($sql);
            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                $sql2 = "UPDATE humo_user_notes SET note_tree_id='" . $qryDb->tree_id . "', note_kind='user' WHERE note_id='" . $qryDb->note_id . "'";
                $result = $dbh->query($sql2);
            }

            // *** Remove note_fam_gedcomnumber ***
            $sql = "ALTER TABLE humo_user_notes DROP note_fam_gedcomnumber;";
            $result = $dbh->query($sql);

            // *** Remove note_fam_gedcomnumber ***
            $sql = "ALTER TABLE humo_user_notes DROP note_tree_prefix;";
            $result = $dbh->query($sql);
        }

        // *** Remove "NOT NULL" from hebnight variables ***
        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_persons');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['pers_birth_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_birth_date_hebnight pers_birth_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            //echo $sql;
            $result = $dbh->query($sql);
        }
        if (isset($field['pers_death_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_death_date_hebnight pers_death_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
        }
        if (isset($field['pers_buried_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_buried_date_hebnight pers_buried_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_families');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['fam_marr_notice_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_notice_date_hebnight fam_marr_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
        }
        if (isset($field['fam_marr_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_date_hebnight fam_marr_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
        }
        if (isset($field['fam_marr_church_notice_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_church_notice_date_hebnight fam_marr_church_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
        }
        if (isset($field['fam_marr_church_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_church_date_hebnight fam_marr_church_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
        }

        $column_qry = $dbh->query('SHOW COLUMNS FROM humo_events');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['event_date_hebnight'])) {
            $sql = "ALTER TABLE humo_events CHANGE event_date_hebnight event_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $result = $dbh->query($sql);
        }

        // ***************************************************
        // *** Aug. 2022: Cleanup old HuMo-genealogy files ***
        // ***************************************************
        global $update_dir, $update_files;

        function remove_the_folders($remove_folders)
        {
            global $update_dir, $update_files;
            //echo '<br><br><br><br><br><br><br>';
            foreach ($remove_folders as $rf) {
                //unset ($update_dir,$update_files);
                //echo $rf . ' folder<br>';
                if (is_dir($rf)) {
                    // *** Remove these old HuMo-genealogy files, a__ is just some random text (skip items)... ***
                    listFolderFiles2($rf, array('a__', 'a__'), 'update_files');
                    //echo $update_dir[0] . ' ' . $update_files[0];
                    // *** Count down, because files must be removed first before removing directories ***
                    if (is_array($update_files)) {
                        for ($i = count($update_files) - 1; $i >= 0; $i--) {
                            if (!is_dir($update_dir[$i] . '/' . $update_files[$i])) {
                                unlink($update_dir[$i] . '/' . $update_files[$i]);
                            } else {
                                rmdir($update_dir[$i] . '/' . $update_files[$i]);
                            }
                            //echo $update_dir[$i] . '/' . $update_files[$i] . '<br>';
                        }
                    }
                    rmdir($rf);
                    unset($update_dir, $update_files);
                }
            }
        }

        function listFolderFiles2($dir, $exclude, $file_array)
        {
            global $update_dir, $update_files;
            $ffs = scandir($dir);
            foreach ($ffs as $ff) {
                if (is_array($exclude) and !in_array($ff, $exclude)) {
                    if ($ff != '.' && $ff != '..') {
                        // *** Skip media files in ../media/, ../media/cms/ etc.
                        //if (substr($dir,0,8)=='../media' AND !is_dir($dir.'/'.$ff) AND $ff != 'readme.txt'){
                        //	// skip media files
                        //}
                        //else{
                        $update_dir[] = $dir;
                        $update_files[] = $ff;
                        if (is_dir($dir . '/' . $ff)) listFolderFiles2($dir . '/' . $ff, $exclude, $file_array);
                        //}
                    }
                }
            }
        }

        if (!isset($humo_option['cleanup_status'])) {
            // *** Remove old files ***
            $remove_file[] = 'gedcom_files/HuMo-gen 2020_05_02 UTF-8.ged';
            $remove_file[] = 'gedcom_files/HuMo-gen test gedcomfile.ged'; // *** File is renamed to HuMo-genealogy ***
            $remove_file[] = '../include/.htaccess'; // *** This file blocks loading of several js scripts ***
            $remove_file[] = '../languages/.htaccess'; // *** This file blocks showing of language flag icons ***
            $remove_file[] = '../styles/Blauw.css';
            $remove_file[] = '../styles/Blue.css';
            $remove_file[] = '../styles/Brown.css';
            $remove_file[] = '../styles/Clear White.css';
            $remove_file[] = '../styles/Donkerbruin.css';
            $remove_file[] = '../styles/Elegant Blue.css';
            $remove_file[] = '../styles/Elegant Corsiva.css';
            $remove_file[] = '../styles/Elegant Green.css';
            $remove_file[] = '../styles/Elegant Mauve.css';
            $remove_file[] = '../styles/Elegant_Blue.css';
            $remove_file[] = '../styles/Elegant_Green.css';
            $remove_file[] = '../styles/Experiment_HTML5.css';
            $remove_file[] = '../styles/Green.css';
            $remove_file[] = '../styles/Groen.css';
            $remove_file[] = '../styles/Heelal.css';
            $remove_file[] = '../styles/Mauve fixed menu.css';
            $remove_file[] = '../styles/Mauve left menu.css';
            $remove_file[] = '../styles/Orange.css';
            $remove_file[] = '../styles/Oranje.css';
            $remove_file[] = '../styles/Paars.css';
            $remove_file[] = '../styles/Purple.css';

            foreach ($remove_file as $rfile) {
                if (file_exists($rfile)) {
                    //echo $rfile.'<br>';
                    unlink($rfile);
                }
            }

            // *** Remove old folders ***
            $remove_folders[] = '../fanchart';
            $remove_folders[] = '../fpdf16';
            $remove_folders[] = '../humo_mobile';
            $remove_folders[] = '../include/fpdf16';
            $remove_folders[] = '../include/jqueryui/css';
            $remove_folders[] = '../include/jqueryui/development-bundle';
            $remove_folders[] = '../include/jqueryui/js';
            $remove_folders[] = '../include/lightbox';
            $remove_folders[] = '../include/sliderbar';
            $remove_folders[] = '../languages/fa DISABLED';
            $remove_folders[] = '../lightbox';
            $remove_folders[] = '../menu';
            $remove_folders[] = '../popup_menu';
            $remove_folders[] = '../sliderbar';
            $remove_folders[] = '../styles/images_blue';
            $remove_folders[] = '../styles/images_green';
            $remove_folders[] = '../styles/imagesantique';
            $remove_folders[] = '../styles/imagesblauw';
            $remove_folders[] = '../styles/imagesdonkerbruin';
            $remove_folders[] = '../styles/imagesgroen';
            $remove_folders[] = '../styles/imagesheelal';
            $remove_folders[] = '../styles/imagesoranje';
            $remove_folders[] = '../styles/imagesoriginal';
            $remove_folders[] = '../styles/imagespaars';
            $remove_folders[] = '../styles/imagessilverline';
            $remove_folders[] = '../styles/imageswhite';
            $remove_folders[] = '../styles/imagesyossi';
            $remove_folders[] = '../talen';
            $remove_folders[] = 'languages';        // admin/languages
            $remove_folders[] = 'menu';            // admin/languages
            $remove_folders[] = 'statistieken';    // admin/statistieken

            remove_the_folders($remove_folders);

            // *** First cleanup, insert cleanup status into settings ***
            $sql = "INSERT INTO humo_settings SET
                setting_variable='cleanup_status',
                setting_value='1'";
            @$dbh->query($sql);
            $humo_option['cleanup_status'] = '1';
        }

        // *** Second cleanup of files ***
        if (isset($humo_option['cleanup_status']) and $humo_option['cleanup_status'] == '1') {
            unset($remove_folders, $update_dir, $update_files);

            $remove_folders[] = '../include/securimage';
            remove_the_folders($remove_folders);

            // *** Update "update_status" to number 2 ***
            $result = $dbh->query("UPDATE humo_settings SET setting_value='2' WHERE setting_variable='cleanup_status'");
            $humo_option['cleanup_status'] = '2';
        }

        // *** Third cleanup of files ***
        if (isset($humo_option['cleanup_status']) and $humo_option['cleanup_status'] == '2') {
            // *** Remove old files ***
            $remove_file[] = '../info.php';
            $remove_file[] = '../credits.php';
            $remove_file[] = '../README.TXT';
            $remove_file[] = '../lijst.php';
            $remove_file[] = '../lijst_namen.php';
            $remove_file[] = '../gezin.php';

            foreach ($remove_file as $rfile) {
                if (file_exists($rfile)) {
                    //echo $rfile.'<br>';
                    unlink($rfile);
                }
            }

            // *** Remove old folders ***
            // *** For some reason it doesn't work properly to use multiple dir's in one array ***
            //$remove_folders[] = 'include/ckeditor';
            //$remove_folders[] = 'include/kcfinder';
            //remove_the_folders($remove_folders);
            unset($remove_folders, $update_dir, $update_files);
            $remove_folders[] = 'include/ckeditor';
            remove_the_folders($remove_folders);

            unset($remove_folders, $update_dir, $update_files);
            $remove_folders[] = 'include/kcfinder';
            remove_the_folders($remove_folders);

            // *** Update "update_status" to number 3 ***
            $result = $dbh->query("UPDATE humo_settings SET setting_value='3' WHERE setting_variable='cleanup_status'");
            $humo_option['cleanup_status'] = '3';
        }

        $show_menu_left = true;

        // *** Debug HuMo-genealogy`admin pages ***
        if ($humo_option["debug_admin_pages"] == 'y') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }

        // *** Check if visitor is allowed ***
        if (!$db_functions->check_visitor($_SERVER['REMOTE_ADDR'])) {
            echo 'Access to website is blocked.';
            exit;
        }

        // *** Added in mar. 2023. To prevent double results in search results ***
        //SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
        $result = $dbh->query("SET SESSION sql_mode=(SELECT
            REPLACE(
                REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','')
            ,'NO_ZERO_IN_DATE',''));");
    }
}

// *** First installation: show menu if installation of tables is started ***
if (isset($_POST['install_tables2'])) {
    $show_menu_left = true;
}

if (isset($database_check) and @$database_check) {  // otherwise we can't make $dbh statements
    // *** Update to version 4.6, in older version there is a dutch-named table: humo_instellingen ***
    try {
        $check_update = @$dbh->query("SELECT * FROM humo_instellingen");
        if ($check_update) {
            $page = 'update';
            $show_menu_left = false;
        }
    } catch (Exception $e) {
        //
    }

    // *** Check HuMo-genealogy database status ***
    // *** Change this value if the database must be updated ***
    if (isset($humo_option["update_status"])) {
        if ($humo_option["update_status"] < 15) {
            $page = 'update';
            $show_menu_left = false;
        }
    }

    if (
        isset($_GET['page'])
        and ($_GET['page'] == 'editor_sources'
            or $_GET['page'] == 'editor_place_select'
            or $_GET['page'] == 'editor_person_select'
            or $_GET['page'] == 'editor_relation_select'
            or $_GET['page'] == 'editor_media_select'
            or $_GET['page'] == 'editor_user_settings')
    ) {
        $show_menu_left = false;
        $popup = true;
    }
    /*
    if (isset($_GET['page'])
        AND ($_GET['page']=='editor_place_select'
            OR $_GET['page']=='editor_person_select'
            OR $_GET['page']=='editor_relation_select'
            OR $_GET['page']=='editor_media_select'
            OR $_GET['page']=='editor_user_settings')){
        $show_menu_left=false;
        $popup=true;
    }
    */
}

// *** Set timezone ***
include_once(CMS_ROOTPATH . "include/timezone.php"); // set timezone
timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Language selection for admin ***
$map = opendir(CMS_ROOTPATH . 'languages/');
while (false !== ($file = readdir($map))) {
    if (strlen($file) < 6 and $file != '.' and $file != '..') {
        $language_select[] = $file;
        if (file_exists(CMS_ROOTPATH . 'languages/' . $file . '/' . $file . '.mo')) {
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
            elseif ($file == 'hu') $language_order[] = 'Magyar';
            elseif ($file == 'id') $language_order[] = 'Indonesian';
            elseif ($file == 'it') $language_order[] = 'Italiano';
            elseif ($file == 'es_mx') $language_order[] = 'Mexicano';
            elseif ($file == 'nl') $language_order[] = 'Nederlands';
            elseif ($file == 'no') $language_order[] = 'Norsk';
            elseif ($file == 'pt') $language_order[] = 'Portuguese';
            elseif ($file == 'ro') $language_order[] = 'Romanian';
            elseif ($file == 'ru') $language_order[] = 'Russian';
            elseif ($file == 'sk') $language_order[] = 'Slovensky';
            elseif ($file == 'sv') $language_order[] = 'Swedish';
            elseif ($file == 'tr') $language_order[] = 'Turkish';
            elseif ($file == 'zh') $language_order[] = 'Chinese_traditional';
            elseif ($file == 'pl') $language_order[] = 'Polish';
            else $language_order[] = $file;
        }
        // *** Save language choice ***
        if (isset($_GET["language_choice"])) {
            // *** Check if language file really exists, to prevent hack of website ***
            if ($_GET["language_choice"] == $file) {
                $_SESSION['save_language_admin'] = $file;
            }
        }
    }
}
closedir($map);
// *** Order language array by name of language ***
array_multisort($language_order, $language_file);

// *** Select admin language ***
$selected_language = "en";
// *** Saved default language ***
if (
    isset($humo_option['default_language_admin'])
    and file_exists(CMS_ROOTPATH . 'languages/' . $humo_option['default_language_admin'] . '/' . $humo_option['default_language_admin'] . '.mo')
) {
    $selected_language = $humo_option['default_language_admin'];
}
// *** Safety: extra check if language exists ***
if (
    isset($_SESSION["save_language_admin"])
    and file_exists(CMS_ROOTPATH . 'languages/' . $_SESSION["save_language_admin"] . '/' . $_SESSION["save_language_admin"] . '.mo')
) {
    $selected_language = $_SESSION["save_language_admin"];
}

$language = array();
include(CMS_ROOTPATH . 'languages/' . $selected_language . '/language_data.php');

// *** .mo language text files ***
include_once(CMS_ROOTPATH . "languages/gettext.php");
// *** Load ***
$_SESSION["language_selected"] = $selected_language;
Load_default_textdomain();
//Load_textdomain('customer_domain', 'languages/'.$selected_language.'/'.$selected_language.'.mo');

// *** Process LTR and RTL variables ***
$dirmark1 = "&#x200E;";  //ltr marker
$dirmark2 = "&#x200F;";  //rtl marker
$rtlmarker = "ltr";

// *** Switch direction markers if language is RTL ***
if ($language["dir"] == "rtl") {
    $dirmark1 = "&#x200F;";  //rtl marker
    $dirmark2 = "&#x200E;";  //ltr marker
    $rtlmarker = "rtl";
}

// *** Process login form ***
$fault = false;
$valid_user = false;
if (isset($_POST["username"]) && isset($_POST["password"])) {
    $resultDb = $db_functions->get_user($_POST["username"], $_POST["password"]);
    if ($resultDb) {
        $valid_user = true;

        // *** FIRST CHECK IF USER IS ADMIN OR EDITOR ***
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
            echo __('Access to admin pages is not allowed.');
            exit;
        }

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
            $_SESSION['user_name_admin'] = $resultDb->user_name;
            $_SESSION['user_id_admin'] = $resultDb->user_id;
            $_SESSION['group_id_admin'] = $resultDb->user_group_id;

            // *** Add login in logbook ***
            $log_date = date("Y-m-d H:i");
            $sql = "INSERT INTO humo_user_log SET
                log_date='$log_date',
                log_username='" . $resultDb->user_name . "',
                log_ip_address='" . $_SERVER['REMOTE_ADDR'] . "',
                log_user_admin='admin',
                log_status='success'";
            @$dbh->query($sql);
        }
    } else {
        // *** No valid user or password ***
        //$fault='<p align="center"><font color="red">'.__('Please enter a valid username or password. ').'</font>';
        $fault = true;

        // *** Save log! ***
        $sql = "INSERT INTO humo_user_log SET
            log_date='" . date("Y-m-d H:i") . "',
            log_username='" . safe_text_db($_POST["username"]) . "',
            log_ip_address='" . $_SERVER['REMOTE_ADDR'] . "',
            log_user_admin='admin',
            log_status='failed'";
        $dbh->query($sql);
    }
}

// *** Login check ***
$group_administrator = '';
$group_edit_trees = '';
if (isset($database_check) and $database_check) {
    if (isset($_SERVER["PHP_AUTH_USER"])) {
        // *** Logged in using .htacess ***

        // *** Standard group permissions ***
        $group_administrator = 'j';
        $group_edit_trees = '';

        // *** If .htaccess is used, check usergroup for admin rights ***
        @$query = "SELECT * FROM humo_users LEFT JOIN humo_groups
            ON humo_users.user_group_id=humo_groups.group_id
            WHERE humo_users.user_name='" . $_SERVER["PHP_AUTH_USER"] . "'";
        @$result = $dbh->query($query);
        if (@$result->rowCount() > 0) {
            @$resultDb = $result->fetch(PDO::FETCH_OBJ);
            $group_administrator = $resultDb->group_admin;

            // *** Check if user is a editor, GROUP SETTINGS ***
            $group_edit_trees = '';
            if (isset($resultDb->group_edit_trees)) {
                $group_edit_trees = $resultDb->group_edit_trees;
            }
            // *** Edit family trees [USER SETTING] ***
            if (isset($resultDb->user_edit_trees) and $resultDb->user_edit_trees) {
                if ($group_edit_trees) $group_edit_trees .= ';' . $resultDb->user_edit_trees;
                else $group_edit_trees = $resultDb->user_edit_trees;
            }
        }
    } elseif ($page == 'update') {
        // *** No log in, update procedure (group table will be changed) ***
    } else {
        // *** Logged in using PHP-MySQL ***
        $result = false;
        try {
            @$query = "SELECT * FROM humo_users";
            @$result = $dbh->query($query);
        } catch (Exception $e) {
            //
        }
        if ($result !== FALSE) {
            if ($result->rowCount() > 0) {
                // *** humo-users table exists, check admin log in ***
                if (isset($_SESSION["group_id_admin"])) {
                    // *** Logged in as admin... ***

                    // *** Read group settings ***
                    $groepsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $_SESSION["group_id_admin"] . "'");
                    @$groepDb = $groepsql->fetch(PDO::FETCH_OBJ);

                    // *** Check if user is an administrator ***
                    $group_administrator = $groepDb->group_admin;
                    if ($group_administrator != 'j') $page = 'login';

                    // *** Edit family trees [GROUP SETTING] ***
                    if (isset($groepDb->group_edit_trees)) {
                        $group_edit_trees = $groepDb->group_edit_trees;
                        $page = '';
                    }
                    // *** Edit family trees [USER SETTING] ***
                    $user_result2 = $dbh->query("SELECT * FROM humo_users WHERE user_id=" . $_SESSION['user_id_admin']);
                    $resultDb = $user_result2->fetch(PDO::FETCH_OBJ);
                    if (isset($resultDb->user_edit_trees) and $resultDb->user_edit_trees) {
                        if ($group_edit_trees) $group_edit_trees .= ';' . $resultDb->user_edit_trees;
                        else $group_edit_trees = $resultDb->user_edit_trees;
                    }
                } else {
                    // *** Show log in screen ***
                    $page = 'login';
                }
            }
        } else {
            // *** No user table: probably first installation: everything will be visible! ***
        }
    }
}

// *** Save ip address in session to prevent session hijacking ***
if (isset($_SESSION['current_ip_address']) == FALSE) {
    $_SESSION['current_ip_address'] = $_SERVER['REMOTE_ADDR'];
}

if (!CMS_SPECIFIC) {
    $html_text = '';
    if ($language["dir"] == "rtl") {   // right to left language
        $html_text = ' dir="rtl"';
    }
    if (isset($screen_mode) and ($screen_mode == "STAR" or $screen_mode == "STARSIZE")) {
        $html_text = '';
    }

    // *** Use your own favicon.ico in media folder ***
    if (file_exists('../media/favicon.ico'))
        $favicon = '<link href="../media/favicon.ico" rel="shortcut icon" type="image/x-icon">';
    else
        $favicon = '<link href="' . CMS_ROOTPATH . 'favicon.ico" rel="shortcut icon" type="image/x-icon">';

?>
    <!DOCTYPE html>
    <html lang="<?= $selected_language; ?>" <?= $html_text; ?>>

    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">

        <!-- *** Rescale standard HuMo-genealogy pages for mobile devices *** -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= __('Administration'); ?></title>

        <?= $favicon; ?>

        <link href="admin.css" rel="stylesheet" type="text/css">

        <!-- CSS changes for mobile devices -->
        <link rel="stylesheet" media="(max-width: 640px)" href="admin_mobile.css">

        <?php
        // *** Don't load all scripts for source editor (improves speed of page) ***
        if ($popup == false) {
        ?>
            <!-- Statistics style sheet -->
            <link href="statistics/style.css" rel="stylesheet" type="text/css">

            <link href="admin_print.css" rel="stylesheet" type="text/css" media="print">

            <script src="<?= CMS_ROOTPATH; ?>include/jquery/jquery.min.js"></script>
            <script src="<?= CMS_ROOTPATH; ?>include/jqueryui/jquery-ui.min.js"></script>
            <script src="include/popup_merge.js"></script>
        <?php
        }
        ?>
        <!-- Main menu pull-down -->
        <link rel="stylesheet" type="text/css" href="<?= CMS_ROOTPATH; ?>include/popup_menu/popup_menu.css">
        <!-- Pop-up menu -->
        <script src="<?= CMS_ROOTPATH; ?>include/popup_menu/popup_menu.js"></script>
    </head>

    <?php

    // *** Close pop-up screen and update main screen ***
    if (isset($_GET['page']) and $_GET['page'] == 'close_popup') {
        $page_link = 'editor';
        // *** Also add these links in "Close source screen" link ***
        if (isset($_GET['connect_sub_kind'])) {
            if ($_GET['connect_sub_kind'] == 'address_source') $page_link = 'edit_addresses';
            //if ($_GET['connect_sub_kind']=='pers_address_source') $page_link='edit_addresses';
            //if ($_GET['connect_sub_kind']=='fam_address_source') $page_link='edit_addresses';
            if ($_GET['connect_sub_kind'] == 'pers_event_source') $page_link = 'editor&event_person=1'; // Don't use &amp;
            if ($_GET['connect_sub_kind'] == 'fam_event_source') $page_link = 'editor&event_family=1'; // Don't use &amp;
        }

        // *** Added May 2021: For multiple marriages ***
        if (substr($_GET['connect_sub_kind'], 0, 3) == 'fam')
            $page_link .= '&marriage_nr=' . $_SESSION['admin_fam_gedcomnumber']; // Don't use &amp;

        if (isset($_GET['event_person']) and $_GET['event_person'] == '1')
            $page_link = 'editor&event_person=1#event_person_link'; // Don't use &amp;
        //if (isset($_GET['event_family']) AND $_GET['event_family']=='1')
        //	$page_link='editor&event_family=1#event_family_link'; // Don't use &amp;
        // *** Added May 2021: For multiple marriages ***
        if (isset($_GET['event_family']) and $_GET['event_family'] == '1')
            $page_link = 'editor&event_family=1&marriage_nr=' . $_SESSION['admin_fam_gedcomnumber'] . '#event_family_link'; // Don't use &amp;

        echo '<script>';
        echo 'function redirect_to(where, closewin){
                opener.location= \'index.php?page=' . $page_link . '\' + where;
                if (closewin == 1){ self.close(); }
            }';
        echo '</script>';

        //echo '<body onload="redirect_to(\'index.php\',\'1\')">';
        echo '<body onload="redirect_to(\'\',\'1\')">';

        die();
    } else {
        echo '<body class="humo">';
    }
}
/*
else {
    JHTML::stylesheet('admin_joomla.css', CMS_ROOTPATH . 'admin/');
    JHTML::stylesheet('v1.css', CMS_ROOTPATH . 'admin/menu/');
    JHTML::stylesheet('style.css', CMS_ROOTPATH . 'admin/statistics/');

    // *** Main menu pull-down ***
    if (CMS_SPECIFIC != 'CMSMS') {
        JHTML::stylesheet('popup_menu.css', CMS_ROOTPATH . 'include/popup_menu/');
    }

    // *** Pop-up menu ***
    echo '<script src="' . CMS_ROOTPATH . 'include/popup_menu/popup_menu.js"></script>';
}
*/

// *** Show top menu ***
//if (CMS_SPECIFIC == 'Joomla') {
//	$path_tmp = 'index.php?option=com_humo-gen&amp;task=admin&amp;';
//} else {
$path_tmp = 'index.php?';
//}

$top_dir = '';
if ($language["dir"] == "rtl") {
    $top_dir = 'style = "text-align:right" ';
}

//echo '<img src="'.CMS_ROOTPATH_ADMIN.'images/humo-gen-small.gif" align="left" alt="logo">';
//echo '<img src="'.CMS_ROOTPATH_ADMIN.'images/humo-gen-25a.png" align="left" alt="logo" height="45">';
if ($popup == false) {
    ?>
    <div id="humo_top" <?= $top_dir; ?>>

        <span id="top_website_name">
            &nbsp;<a href="index.php" style="color:brown;">HuMo-genealogy</a>
        </span>
    <?php
}

//if (isset($database_check) AND $database_check) { // Otherwise we can't make $dbh statements
if (isset($database_check) and $database_check and $group_administrator == 'j') { // Otherwise we can't make $dbh statements
    // *** Enable/ disable HuMo-genealogy update check ***
    if (isset($_POST['enable_update_check_change'])) {
        if (isset($_POST['enable_update_check'])) {
            $update_last_check = '2012-01-01';
            $update_text = '';
            $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';
        } else {
            $update_last_check = 'DISABLED';
            $update_text = '  ' . __('update check is disabled.');
            $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';
        }

        $result = $db_functions->update_settings('update_text', $update_text);
        $result = $db_functions->update_settings('update_last_check', $update_last_check);

        $humo_option['update_last_check'] = $update_last_check;
        //$humo_option['update_text']=$update_text;
    }

    // *** Check if installation is completed, before checking for an update ***
    $check_update = @$dbh->query("SELECT * FROM humo_settings");
    if ($check_update and $page != 'login' and $page != 'update' and $popup == false) {
        $debug_update = 'Start. ';

        // *** Manual check for update ***
        if (isset($_GET['update_check']) and $humo_option['update_last_check'] != 'DISABLED') {
            // *** Update settings ***
            $result = $db_functions->update_settings('update_last_check', '2012-01-01');
            $humo_option['update_last_check'] = '2012-01-01';
        }

        // *** Update file, example ***
        // echo "version=4.8.4\r\n";
        // echo "version_date=2012-09-02\r\n";
        // echo "test=testline";

        // *** Update check, once a day ***
        // 86400 = 1 day. yyyy-mm-dd
        if ($humo_option['update_last_check'] != 'DISABLED' and strtotime("now") - strtotime($humo_option['update_last_check']) > 86400) {
            $link_name = str_replace(' ', '_', $_SERVER['SERVER_NAME']);
            $link_version = str_replace(' ', '_', $humo_option["version"]);

            if (function_exists('curl_exec')) {

                // First try GitHub ***
                // *** Oct. 2021: Added random number to prevent CURL cache problems ***
                $source = 'https://raw.githubusercontent.com/HuubMons/HuMo-genealogy/master/admin/update/version_check.txt?random=' . rand();

                $resource = curl_init();
                curl_setopt($resource, CURLOPT_URL, $source);
                curl_setopt($resource, CURLOPT_HEADER, false);
                curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 20);
                // *** BE AWARE: for provider Hostinger this must be a low value, otherwise the $dbh connection will be disconnected! ***
                curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 15);

                // *** Oct 2021: Don't use CURL cache ***
                curl_setopt($resource, CURLOPT_FRESH_CONNECT, true); // don't use a cached version of the url

                // *** Added for GitHub ***
                curl_setopt($resource, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($resource, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

                $content = curl_exec($resource);
                curl_close($resource);

                $content_array = explode(PHP_EOL, $content); // *** Split array into seperate lines ***

                // *** Debug information and validation of data ***
                if (isset($content_array[0])) {
                    $debug_update .= ' Github:' . $content_array[1] . '. ';

                    // *** Check if there is valid information, there should be at least 4 version lines ***
                    $valid = 0;
                    foreach ($content_array as $content_line) {
                        if (substr($content_line, 0, 7) == 'version') $valid++;
                    }

                    if ($valid > 3) {
                        $debug_update .= ' Valid.';
                    } else {
                        unset($content_array);
                        $debug_update .= ' Invalid.';
                    }
                }

                // *** Use humo-gen.com if GitHub isn't working ***
                if (!isset($content_array)) {
                    // *** Read update data from HuMo-genealogy website ***
                    // *** Oct. 2021: Added random number to prevent CURL cache problems ***
                    $source = 'https://humo-gen.com/update/index.php?status=check_update&website=' . $link_name . '&version=' . $link_version . '&random=' . rand();

                    //$update_file='update/temp_update_check.php';
                    $resource = curl_init();
                    curl_setopt($resource, CURLOPT_URL, $source);
                    curl_setopt($resource, CURLOPT_HEADER, false);
                    curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
                    //curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 20);
                    // *** BE AWARE: for provider Hostinger this must be a low value, otherwise the $dbh connection will be disconnected! ***
                    curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 15);

                    // *** Oct 2021: Don't use CURL cache ***
                    curl_setopt($resource, CURLOPT_FRESH_CONNECT, true); // don't use a cached version of the url

                    $content = curl_exec($resource);
                    curl_close($resource);

                    $content_array = explode(PHP_EOL, $content); // *** Split array into seperate lines ***

                    // *** Debug information and validation of data ***
                    if (isset($content_array[0])) {
                        $debug_update .= ' HG:' . $content_array[0] . ' ';

                        // *** Check if there is valid information, there should be 4 version lines ***
                        $valid = 0;
                        foreach ($content_array as $content_line) {
                            if (substr($content_line, 0, 7) == 'version') $valid++;
                        }

                        if ($valid > 3) {
                            $debug_update .= ' Valid.';
                        } else {
                            unset($content_array);
                            $debug_update .= ' Invalid.';
                        }
                    }

                    //if($content != ''){
                    //	$fp = @fopen($update_file, 'w');
                    //	$fw = @fwrite($fp, $content);
                    //	@fclose($fp);
                    //}

                }

                // *** If provider or curl blocks https link: DISABLE SSL and recheck ***
                if (!isset($content_array)) {
                    // *** Oct. 2021: Added random number to prevent CURL cache problems ***
                    $source = 'https://humo-gen.com/update/index.php?status=check_update&website=' . $link_name . '&version=' . $link_version . '&random=' . rand();

                    //$update_file='update/temp_update_check.php';
                    $resource = curl_init();
                    curl_setopt($resource, CURLOPT_URL, $source);
                    curl_setopt($resource, CURLOPT_HEADER, false);
                    curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
                    //curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 20);
                    // *** BE AWARE: for provider Hostinger this must be a low value, otherwise the $dbh connection will be disconnected! ***
                    curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 15);

                    // *** Oct 2021: Don't use CURL cache ***
                    curl_setopt($resource, CURLOPT_FRESH_CONNECT, true); // don't use a cached version of the url

                    // *********************************************************************
                    // *** EXTRA SETTINGS TO DISABLE SSL CHECK NEEDED FOR SOME PROVIDERS ***
                    //Disable CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER by
                    //setting them to false.
                    curl_setopt($resource, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, false);
                    // *********************************************************************

                    $content = curl_exec($resource);
                    curl_close($resource);

                    $content_array = explode(PHP_EOL, $content); // *** Split array into seperate lines ***

                    // *** Debug information ***
                    if (isset($content_array[0])) {
                        $debug_update .= ' 3:' . $content_array[0] . ' ';
                    }
                }
            }

            // *** Copy HuMo-genealogy to server using file_get_contents ***
            /*
                if (!file_exists('update/temp_update_check.php')){
                    $source='https://humo-gen.com/update/index.php?status=check_update&website='.$link_name.'&version='.$link_version;
                    $update_file='update/temp_update_check.php';

                    $content = @file_get_contents($source);
                    //if ($content === false) {
                    //	$this->_log->addError(sprintf('Could not download update "%s"!', $updateUrl));
                    //	return false;
                    //}

                    // *** Open file ***
                    $handle = fopen($update_file, 'w');
                    //if (!$handle) {
                    //	$this->_log->addError(sprintf('Could not open file handle to save update to "%s"!', $updateFile));
                    //	return false;
                    //}

                    // *** Copy file ***
                    if (!fwrite($handle, $content)) {
                    //	$this->_log->addError(sprintf('Could not write update to file "%s"!', $updateFile));
                    //	fclose($handle);
                    //	return false;
                    }

                    fclose($handle);
                }
                */

            // *** Copy HuMo-genealogy to server using copy ***
            // DISABLED BECAUSE MOST PROVIDERS BLOCK THIS COPY FUNCTION FOR OTHER WEBSITES...
            //if (!file_exists('update/temp_update_check.php')){
            //	$source='https://humo-gen.com/update/index.php?status=check_update&website='.$link_name.'&version='.$link_version;
            //	$update_file='update/temp_update_check.php';
            //	@copy($source, $update_file);
            //}


            //if ($f = @fopen($update_file, 'r')){
            //if (is_file($update_file) AND $f = @fopen($update_file, 'r')){
            if (isset($content_array) and $content_array) {
                // *** Used for automatic update procedure ***
                $update['up_to_date'] = 'no';

                // *** HuMo-genealogy version ***
                $update['version'] = '';
                $update['version_date'] = '';
                $update['version_auto_download'] = '';
                // At this moment only 4 lines permitted that starts with version...
                $update['new_version_auto_download_github'] = '';

                // *** HuMo-genealogy beta version ***
                $update['beta_version'] = '';
                $update['beta_version_date'] = '';
                $update['beta_version_auto_download'] = '';

                //while(!feof($f)) { 
                foreach ($content_array as $content_line) {
                    //$update_data = fgets( $f, 4096 );
                    $update_array = explode("=", $content_line);

                    // *** HuMo-genealogy version ***
                    if ($update_array[0] == 'version') {
                        $update['version'] = trim($update_array[1]);
                    }
                    if ($update_array[0] == 'version_date') {
                        $update['version_date'] = trim($update_array[1]);
                    }
                    if ($update_array[0] == 'version_download') {
                        $update['version_download'] = trim($update_array[1]);
                    }
                    if ($update_array[0] == 'version_auto_download') {
                        $update['version_auto_download'] = trim($update_array[1]);
                    }
                    if ($update_array[0] == 'version_auto_download_github') {
                        $update['version_auto_download_github'] = trim($update_array[1]);
                    }

                    // *** HuMo-genealogy beta version ***
                    if ($update_array[0] == 'beta_version') {
                        $update['beta_version'] = trim($update_array[1]);
                    }
                    if ($update_array[0] == 'beta_version_date') {
                        $update['beta_version_date'] = trim($update_array[1]);
                    }
                    if ($update_array[0] == 'beta_version_download') {
                        $update['beta_version_download'] = trim($update_array[1]);
                    }
                    if ($update_array[0] == 'beta_version_auto_download') {
                        $update['beta_version_auto_download'] = trim($update_array[1]);
                    }
                }
                //fclose($f);

                //$humo_option["version"]='0'; // *** Test line ***
                // *** 1) Standard status ***
                $update['up_to_date'] = 'yes';
                $update_text = ' ' . __('Update check failed.');
                $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';

                //NEW
                if ($humo_option["version"] == $update['version']) {
                    $update['up_to_date'] = 'yes';
                    $update_text = ' ' . __('is up-to-date!');
                    $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';
                }

                // *** 2) HuMo-genealogy up-to-date (checking version numbers) ***
                //if ($humo_option["version"]==$update['version']){
                // *** If GitHub numbering isn't up-to-date yet, just ignore version check. Could happen while updating sites! ***
                if (strtotime($update['version_date']) - strtotime($humo_option["version_date"]) < 0) {
                    $update['up_to_date'] = 'yes';
                    $update_text = ' ' . __('is up-to-date!');
                    $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';
                }

                // *** 3) First priority: check for normal HuMo-genealogy update ***
                if (strtotime($update['version_date']) - strtotime($humo_option["version_date"]) > 0) {
                    $update['up_to_date'] = 'no';
                    $update_text = ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update available') . ' (' . $update['version'] . ')!</a>';
                }
                // *** 4) Second priority: check for Beta version update ***
                elseif (strtotime($update['beta_version_date']) - strtotime($humo_option["version_date"]) > 0) {
                    $update['up_to_date'] = 'yes';
                    $update_text = ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Beta version available') . ' (' . $update['beta_version'] . ')!</a>';
                }

                // *** Update settings ***
                $update_last_check = date("Y-m-d");
                $result = $db_functions->update_settings('update_last_check', $update_last_check);

                // *** Remove temporary file, used for curl method ***
                //if (file_exists('update/temp_update_check.php')) unlink ('update/temp_update_check.php');
            } else {
                //$update_text= '  '.__('Online version check unavailable.');
                //$update_text.= ' <a href="'.$path_tmp.'page=install_update&update_check=1">'.__('Update options').'</a>';
                $update_text = ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Online version check unavailable.') . '</a>';

                if (!function_exists('curl_exec')) $update_text .= ' Extension php_curl.dll is disabled.';
                elseif (!is_writable('update')) $update_text .= ' Folder admin/update/ is read only.';

                //if( !ini_get('allow_url_fopen') ) $update_text.=' Setting allow_url_fopen is disabled.';

                // *** Update settings, only check for update once a day ***
                $update_last_check = date("Y-m-d");
                $result = $db_functions->update_settings('update_last_check', $update_last_check);
            }

            $result = $db_functions->update_settings('update_text', $update_text);

            $update_text .= ' *';

            // *** Show debug information ***
            if (isset($_POST['debug_update'])) {
                $update_text .= ' ' . __('Debug information:') . ' [' . $debug_update . ']';
            }
        } else {
            // No online check now, use saved text...
            $update_text = $humo_option["update_text"];
        }
        echo $update_text;
    }
}

// *** Feb. 2020: centralised processing of tree_id and tree_prefix ***
// *** Selected family tree, using tree_id ***

// *** Don't check for group_administrator, because of family tree editors ***
//if (isset($database_check) AND $database_check AND $group_administrator=='j') { // Otherwise we can't make $dbh statements
if (isset($database_check) and $database_check) { // Otherwise we can't make $dbh statements
    $check_tree_id = '';
    // *** admin_tree_id must be numeric ***
    if (isset($_SESSION['admin_tree_id']) and is_numeric($_SESSION['admin_tree_id'])) {
        $check_tree_id = $_SESSION['admin_tree_id'];
    }
    // *** tree_id must be numeric ***
    if (isset($_POST['tree_id']) and is_numeric($_POST['tree_id'])) {
        $check_tree_id = $_POST['tree_id'];
    }
    // *** tree_id must be numeric ***
    if (isset($_GET['tree_id']) and is_numeric($_GET['tree_id'])) {
        $check_tree_id = $_GET['tree_id'];
    }

    // *** Just logged in, or no tree_id available: find first family tree ***
    if ($check_tree_id == '') {
        $check_tree_sql = false;
        try {
            $check_tree_sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order LIMIT 0,1");
        } catch (Exception $e) {
            //
        }
        if ($check_tree_sql) {
            @$check_treeDb = $check_tree_sql->fetch(PDO::FETCH_OBJ);
            $check_tree_id = $check_treeDb->tree_id;
        }
    }

    // *** Double check tree_id and save tree id in session ***
    $tree_id = '';
    $tree_prefix = '';
    if (isset($check_tree_id) and $check_tree_id and $check_tree_id != '') {
        // *** New installation: table doesn't exist and could generate an error ***
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_trees'");
        if ($temp->rowCount() > 0) {
            try {
                $get_treeDb = $db_functions->get_tree($check_tree_id);
            } catch (Exception $e) {
                //
            }
            if (isset($get_treeDb) and $get_treeDb) {
                $tree_id = $get_treeDb->tree_id;
                $_SESSION['admin_tree_id'] = $tree_id;
                $tree_prefix = $get_treeDb->tree_prefix;
            }
        }
    }

    // *** Double double check for family tree editor. ***
    $edit_tree_array = explode(";", $group_edit_trees);
    if ($group_administrator == 'j' or in_array($check_tree_id, $edit_tree_array)) {
        // OK
    } else {
        // No access to family tree.
        $check_tree_id = '';
    }

    //echo 'test'.$tree_id.' '.$tree_prefix;
}

/**
 * 
 * *** START MENU ***
 * 
 */

$popup_style = '';
//if ($popup == true) $popup_style = ' style="top:0px;"';

if ($page != 'login' and $page != 'update') {
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    }
}

$menu_top_admin = '';
$menu_item_admin = '';
if ($page == 'admin') {
    $menu_top_admin = ' id="current_top"';
    $menu_item_admin = ' id="current"';
}

//if (CMS_SPECIFIC == 'Joomla') {
//    $menu_path_website = 'index.php?option=com_humo-gen';
//} else {
$menu_path_website = CMS_ROOTPATH . 'index.php';
//}

//if (CMS_SPECIFIC == 'Joomla') {
//    $menu_path_logoff = 'index.php?option=com_humo-gen&amp;task=admin&amp;log_off=1';
//} else {
$menu_path_logoff = 'index.php?log_off=1';
//}

$menu_item_logoff = '';
if ($page == 'check') {
    $menu_item_logoff = ' id="current"';
}

$menu_top_control = '';
$menu_item_install = '';
$menu_item_extensions = '';
$menu_item_settings = '';
$menu_item_settings_homepage = ''; // Page "setting" is highlighted in menu.
$menu_item_settings_special = ''; // Page "setting" is highlighted in menu.
$menu_item_cms_pages = '';
$menu_item_language_editor = '';
$menu_item_prefix_editor = '';
$menu_item_maps = '';
if ($page == 'install') {
    $menu_top_control = ' id="current_top"';
    $menu_item_install = ' id="current"';
}
if ($page == 'extensions') {
    $menu_top_control = ' id="current_top"';
    $menu_item_extensions = ' id="current"';
}
if ($page == 'settings') {
    $menu_top_control = ' id="current_top"';
    $menu_item_settings = ' id="current"';
}
if ($page == 'cms_pages') {
    $menu_top_control = ' id="current_top"';
    $menu_item_cms_pages = ' id="current"';
}
if ($page == 'favorites') {
    $menu_top_control = ' id="current_top"';
}
if ($page == 'language_editor') {
    $menu_top_control = ' id="current_top"';
    $menu_item_language_editor = ' id="current"';
}
if ($page == 'prefix_editor') {
    $menu_top_control = ' id="current_top"';
    $menu_item_prefix_editor = ' id="current"';
}
if ($page == 'google_maps') {
    $menu_top_control = ' id="current_top"';
    $menu_item_maps = ' id="current"';
}

$menu_top_trees = '';
$menu_item_tree = '';
$menu_item_thumbs = '';
$menu_item_user_notes = '';
$menu_item_check = '';
$menu_item_latest_changes = '';  // Page "check" is highlighted in menu.
$menu_item_cal_date = '';
$menu_item_export = '';
$menu_item_backup = '';
$menu_item_statistics = '';
if ($page == 'tree') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_tree = ' id="current"';
}
if ($page == 'thumbs') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_thumbs = ' id="current"';
}
if ($page == 'user_notes') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_user_notes = ' id="current"';
}
if ($page == 'check') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_check = ' id="current"';
}
if ($page == 'cal_date') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_cal_date = ' id="current"';
}
if ($page == 'export') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_export = ' id="current"';
}
if ($page == 'backup') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_backup = ' id="current"';
}
if ($page == 'statistics') {
    $menu_top_trees = ' id="current_top"';
    $menu_item_statistics = ' id="current"';
}

$menu_top_editor = '';
$menu_item_editor = '';
$menu_item_edit_sources = '';
$menu_item_edit_repositories = '';
$menu_item_edit_addresses = '';
$menu_item_edit_places = '';
if ($page == 'editor') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_editor = ' id="current"';
}
if ($page == 'edit_sources') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_edit_sources = ' id="current"';
}
if ($page == 'edit_repositories') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_edit_repositories = ' id="current"';
}
if ($page == 'edit_addresses') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_edit_addresses = ' id="current"';
}
if ($page == 'edit_places') {
    $menu_top_editor = ' id="current_top"';
    $menu_item_edit_places = ' id="current"';
}

$menu_top_users = '';
$menu_item_users = '';
$menu_item_groups = '';
$menu_item_log = '';
if ($page == 'users') {
    $menu_top_users = ' id="current_top"';
    $menu_item_users = ' id="current"';
}
if ($page == 'groups') {
    $menu_top_users = ' id="current_top"';
    $menu_item_groups = ' id="current"';
}
if ($page == 'log') {
    $menu_top_users = ' id="current_top"';
    $menu_item_log = ' id="current"';
}

$menu_top_flags = '';

if ($popup == false) {
    ?>
        <div id="humo_menu" <?= $popup_style; ?>>
            <ul class="humo_menu_item">
                <li>
                    <div class="<?= $rtlmarker; ?>sddm">
                        <a href="<?= $path_tmp; ?>page=admin" onmouseover="mopen(event,'m1x','?','?')" onmouseout="mclosetime()" <?= $menu_top_admin; ?>><img src="../images/menu_mobile.png" width="18" alt="<?= __('Administration'); ?>"></a>
                        <div id="m1x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                if ($group_administrator == 'j') {
                                ?>
                                    <li <?= $menu_item_admin; ?>><a href="<?= $path_tmp; ?>page=admin"><?= __('Administration'); ?> - <?= __('Main menu'); ?></a></li>
                                    <li><a href="<?= $menu_path_website; ?>"><?= __('Website'); ?></a></li>
                                <?php
                                }

                                if (isset($_SESSION["user_name_admin"])) {
                                    echo '<li' . $menu_item_logoff . '><a href="' . $menu_path_logoff . '">' . __('Logoff') . '</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </li>
                <?php

                if ($show_menu_left == true and $page != 'login') {
                    if ($group_administrator == 'j') {
                ?>
                        <li>
                            <div class="<?= $rtlmarker; ?>sddm">
                                <a href="<?= $path_tmp; ?>page=admin" onmouseover="mopen(event,'m2x','?','?')" onmouseout="mclosetime()" <?= $menu_top_control; ?>><img src="../images/settings.png" class="mobile_hidden" alt="<?= __('Control'); ?>"><span class="mobile_hidden"> </span><?= __('Control'); ?></a>
                                <div id="m2x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                    <ul class="humo_menu_item2">
                                        <li <?= $menu_item_install; ?>><a href="<?= $path_tmp; ?>page=install"><?= __('Install'); ?></a></li>
                                        <?php
                                        echo '<li' . $menu_item_extensions . '><a href="' . $path_tmp . 'page=extensions">' . __('Extensions') . '</a></li>';
                                        echo '<li' . $menu_item_settings . '><a href="' . $path_tmp . 'page=settings">' . __('Settings') . '</a></li>';
                                        echo '<li' . $menu_item_settings_homepage . '><a href="' . $path_tmp . 'page=settings&amp;menu_admin=settings_homepage">' . __('Homepage') . '</a></li>';
                                        echo '<li' . $menu_item_settings_special . '><a href="' . $path_tmp . 'page=settings&amp;menu_admin=settings_special">' . __('Special settings') . '</a></li>';
                                        echo '<li' . $menu_item_cms_pages . '><a href="' . $path_tmp . 'page=cms_pages">' . __('CMS Own pages') . '</a></li>';
                                        echo '<li' . $menu_item_language_editor . '><a href="' . $path_tmp . 'page=language_editor">' . __('Language editor') . '</a></li>';
                                        echo '<li' . $menu_item_prefix_editor . '><a href="' . $path_tmp . 'page=prefix_editor">' . __('Prefix editor') . '</a></li>';
                                        echo '<li' . $menu_item_maps . '><a href="' . $path_tmp . 'page=google_maps">' . __('World map') . '</a></li>';
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    <?php
                    }

                    ?>
                    <li>
                        <div class="<?= $rtlmarker; ?>sddm">
                            <?php
                            echo '<a href="' . $path_tmp . 'page=tree"';
                            echo ' onmouseover="mopen(event,\'m3x\',\'?\',\'?\')"';
                            echo ' onmouseout="mclosetime()"' . $menu_top_trees . '><img src="images/family_connect.gif" class="mobile_hidden" alt="' . __('Family trees') . '"><span class="mobile_hidden"> </span>' . __('Family trees') . '</a>';
                            ?>
                            <div id="m3x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                <ul class="humo_menu_item2">
                                    <?php
                                    if ($group_administrator == 'j') {
                                        echo '<li' . $menu_item_tree . '><a href="' . $path_tmp . 'page=tree">' . __('Family trees') . '</a><li>';
                                        //echo '<li'.$menu_item_thumbs.'><a href="'.$path_tmp.'page=thumbs">'.__('Create thumbnails').'</a>';
                                        echo '<li' . $menu_item_thumbs . '><a href="' . $path_tmp . 'page=thumbs">' . __('Pictures/ create thumbnails') . '</a></li>';
                                        echo '<li' . $menu_item_user_notes . '><a href="' . $path_tmp . 'page=user_notes">' . __('Notes') . '</a></li>';
                                        echo '<li' . $menu_item_check . '><a href="' . $path_tmp . 'page=check">' . __('Family tree data check') . '</a></li>';
                                        echo '<li' . $menu_item_latest_changes . '><a href="' . $path_tmp . 'page=view_latest_changes">' . __('View latest changes') . '</a></li>';
                                        echo '<li' . $menu_item_cal_date . '><a href="' . $path_tmp . 'page=cal_date">' . __('Calculated birth date') . '</a></li>';
                                        echo '<li' . $menu_item_export . '><a href="' . $path_tmp . 'page=export">' . __('Gedcom export') . '</a></li>';
                                        echo '<li' . $menu_item_backup . '><a href="' . $path_tmp . 'page=backup">' . __('Database backup') . '</a></li>';
                                        echo '<li' . $menu_item_statistics . '><a href="' . $path_tmp . 'page=statistics">' . __('Statistics') . '</a></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </li>

                    <li>
                        <div class="<?= $rtlmarker; ?>sddm">
                            <?php
                            echo '<a href="' . $path_tmp . 'page=editor"';
                            echo ' onmouseover="mopen(event,\'m3xa\',\'?\',\'?\')"';
                            echo ' onmouseout="mclosetime()"' . $menu_top_editor . '><img src="images/edit.jpg" class="mobile_hidden" alt="' . __('Editor') . '"><span class="mobile_hidden"> </span>' . __('Editor') . '</a>';
                            ?>
                            <div id="m3xa" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                <ul class="humo_menu_item2">
                                    <?php
                                    echo '<li' . $menu_item_editor . '><a href="' . $path_tmp . 'page=editor">' . __('Persons and families') . '</a></li>';
                                    echo '<li' . $menu_item_edit_sources . '><a href="' . $path_tmp . 'page=edit_sources">' . __('Sources') . "</a></li>";
                                    echo '<li' . $menu_item_edit_repositories . '><a href="' . $path_tmp . 'page=edit_repositories">' . __('Repositories') . "</a></li>";
                                    echo '<li' . $menu_item_edit_addresses . '><a href="' . $path_tmp . 'page=edit_addresses">' . __('Shared addresses') . "</a></li>";
                                    echo '<li' . $menu_item_edit_places . '><a href="' . $path_tmp . 'page=edit_places">' . __('Rename places') . "</a></li>";
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <?php

                    if ($group_administrator == 'j') {
                    ?>
                        <li>
                            <div class="<?= $rtlmarker; ?>sddm">
                                <?php
                                echo '<a href="' . $path_tmp . 'page=users"';
                                echo ' onmouseover="mopen(event,\'m4x\',\'?\',\'?\')"';
                                echo ' onmouseout="mclosetime()"' . $menu_top_users . '><img src="images/person_edit.gif" class="mobile_hidden" alt="' . __('Users') . '"><span class="mobile_hidden"> </span>' . __('Users') . '</a>';
                                ?>
                                <div id="m4x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                    <ul class="humo_menu_item2">
                                        <?php
                                        echo '<li' . $menu_item_users . '><a href="' . $path_tmp . 'page=users">' . __('Users') . '</a></li>';
                                        echo '<li' . $menu_item_groups . '><a href="' . $path_tmp . 'page=groups">' . __('Groups') . '</a></li>';
                                        echo '<li' . $menu_item_log . '><a href="' . $path_tmp . 'page=log">' . __('Log') . '</a></li>'; ?>
                                    </ul>
                                </div>
                            </div>
                        </li>
                <?php
                    }
                }

                // *** Check is needed for PHP 7.4 ***
                if (isset($humo_option["hide_languages"]))
                    $hide_languages_array = explode(";", $humo_option["hide_languages"]);
                else
                    $hide_languages_array[] = '';

                ?>
                <li>
                    <div class="<?= $rtlmarker; ?>sddm">
                        <?php
                        include(CMS_ROOTPATH . 'languages/' . $selected_language . '/language_data.php');
                        echo '<a href="index.php?option=com_humo-gen"';
                        echo ' onmouseover="mopen(event,\'m40x\',\'?\',\'?\')"';
                        echo ' onmouseout="mclosetime()"' . $menu_top_flags . '>' . '<img src="' . CMS_ROOTPATH . 'languages/' . $selected_language . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none; height:18px"> </a>';
                        ?>
                        <div id="m40x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                            <ul class="humo_menu_item2">
                                <?php
                                for ($i = 0; $i < count($language_file); $i++) {
                                    // *** Get language name ***
                                    if ($language_file[$i] != $selected_language and !in_array($language_file[$i], $hide_languages_array)) {
                                        include(CMS_ROOTPATH . 'languages/' . $language_file[$i] . '/language_data.php');
                                        echo '<li><a href="' . $path_tmp . 'language_choice=' . $language_file[$i] . '">';
                                        echo '<img src="' . CMS_ROOTPATH . 'languages/' . $language_file[$i] . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none;"> ';
                                        echo '<span class="mobile_hidden">' . $language["name"] . '</span>';
                                        echo '</a>';
                                        echo '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div> <!-- End of humo_top -->
<?php
}
// *** END OF MENU ***

//echo '</div>'; // *** End of humo_top ***

?>
<div id="content_admin">
    <?php

    define('ADMIN_PAGE', true); // *** Safety line ***

    if ($page == 'install') {
        include_once("include/install.php");
    } elseif ($page == 'extensions') {
        include_once("include/extensions.php");
    } elseif ($page == 'login') {
        include_once("include/login.php");
    } elseif ($group_administrator == 'j' and $page == 'tree') {
        include_once("include/trees.php");
    } elseif ($page == 'editor') {
        $_GET['menu_admin'] = 'person';
        include_once("include/editor.php");
    } elseif ($page == 'editor_sources') {
        $_GET['menu_admin'] = 'person';
        include_once("include/editor_sources.php");
    }
    // NEW edit_sources for all source links...
    elseif ($page == 'edit_sources') {
        $_GET['menu_admin'] = 'sources';
        include_once("include/editor.php");
    } elseif ($page == 'edit_repositories') {
        $_GET['menu_admin'] = 'repositories';
        include_once("include/editor.php");
    } elseif ($page == 'edit_addresses') {
        $_GET['menu_admin'] = 'addresses';
        include_once("include/editor.php");
    } elseif ($page == 'edit_places') {
        $_GET['menu_admin'] = 'places';
        include_once("include/editor.php");
    } elseif ($page == 'editor_place_select') {
        $_GET['menu_admin'] = 'places';
        include_once("include/editor_place_select.php");
    } elseif ($page == 'editor_person_select') {
        $_GET['menu_admin'] = 'marriage';
        include_once("include/editor_person_select.php");
    } elseif ($page == 'editor_relation_select') {
        $_GET['menu_admin'] = 'relation';
        include_once("include/editor_relation_select.php");
    } elseif ($page == 'editor_media_select') {
        $_GET['menu_admin'] = 'menu';
        include_once("include/editor_media_select.php");
    } elseif ($page == 'check') {
        include_once("include/tree_check.php");
    } elseif ($page == 'view_latest_changes') {
        $_POST['last_changes'] = 'View latest changes';
        include_once("include/tree_check.php");
    } elseif ($page == 'gedcom') {
        include_once("include/gedcom.php");
    } elseif ($page == 'settings') {
        include_once("include/settings_admin.php");
    } elseif ($page == 'thumbs') {
        include_once("include/thumbs.php");
    } elseif ($page == 'favorites') {
        include_once("include/favorites.php");
    } elseif ($page == 'users') {
        include_once("include/users.php");
    } elseif ($page == 'editor_user_settings') {
        $_GET['menu_admin'] = 'users';
        include_once("include/editor_user_settings.php");
    } elseif ($page == 'groups') {
        include_once("include/groups.php");
    } elseif ($page == 'cms_pages') {
        include_once("include/cms_pages.php");
    } elseif ($page == 'backup') {
        include_once("include/backup.php");
    } elseif ($page == 'user_notes') {
        include_once("include/user_notes.php");
    } elseif ($page == 'cal_date') {
        include_once("include/cal_date.php");
    } elseif ($page == 'export') {
        include_once("include/gedcom_export.php");
    } elseif ($page == 'log') {
        include_once("include/log.php");
    } elseif ($page == 'language_editor') {
        include_once("include/language_editor.php");
    } elseif ($page == 'prefix_editor') {
        include_once("include/prefix_editor.php");
    } elseif ($page == 'google_maps') {
        include_once("include/make_db_maps.php");
    } elseif ($page == 'statistics') {
        include_once("include/statistics.php");
    } elseif ($page == 'install_update') {
        include_once("update/install_update.php");
    } elseif ($page == 'update') {
        include_once("include/update.php");
    }
    //elseif ($page=='photoalbum'){ include_once ("include/photoalbum_categories.php"); }

    // *** Edit event by person ***
    //elseif ($page=='editor_person_event'){ include_once ("include/editor_person_event.php"); }

    // *** Default page for editor ***
    elseif ($group_administrator != 'j' and $group_edit_trees) {
        $_GET['menu_admin'] = 'person';
        include_once("include/editor.php");
    }

    // *** Default page for administrator ***
    else {
        include_once("include/index_inc.php");
    }
    ?>
</div>

</body>

    </html>