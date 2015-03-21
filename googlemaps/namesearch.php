<?php
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//the parts of header.php that we need:
//error_reporting(E_ALL);
if (!defined("CMS_SPECIFIC")) define("CMS_SPECIFIC", false);

// *** When run from CMS, the path to the map (that contains this file) should be given ***
if (!defined("CMS_ROOTPATH")) define("CMS_ROOTPATH", "../");

//if (!defined("CMS_ROOTPATH_ADMIN")) define("CMS_ROOTPATH_ADMIN", "admin/");

ini_set('url_rewriter.tags','');

//if (!CMS_SPECIFIC){
//	session_cache_limiter ('private, must-revalidate'); //tb edit
	session_start();
//}
include_once(CMS_ROOTPATH."include/db_login.php"); //Inloggen database.

// *** Use UTF-8 database connection ***
//$dbh->query("SET NAMES 'utf8'");

include_once(CMS_ROOTPATH."include/safe.php");
include_once(CMS_ROOTPATH."include/settings_global.php"); //Variables
include_once(CMS_ROOTPATH."include/settings_user.php"); // USER variables
include_once(CMS_ROOTPATH."include/person_cls.php"); // for privacy
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");

$tree_id=$_SESSION['tree_id'];

include_once(CMS_ROOTPATH."include/db_functions_cls.php");
$db_functions = New db_functions;
$db_functions->set_tree_id($tree_id);

$language_folder=opendir(CMS_ROOTPATH.'languages/');
while (false!==($file = readdir($language_folder))) {
	if (strlen($file)<5 AND $file!='.' AND $file!='..'){
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

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~`
// start of the namesearch part

if (isset($_GET['max'])) {
	$map_max = $_GET['max'];
}
else { // Logically we can never get here because this file is always called with this parameter
	$map_max = date('Y'); //this year
}

if (isset($_GET['thisplace'])) {
	$thisplace = urldecode($_GET['thisplace']);     // should be done automatically but it doesn't hurt
	$thisplace = str_replace("\'","'",$thisplace);  // in some settings the \ is passed on with the ' while in others not
	$thisplace = str_replace("'","''",$thisplace); 	// for MySQL single quote has to be written as 2 single quotes: '' in the mysql query
}
else { // Logically we can never get here because this file is always called with this parameter
	$thisplace = "NONFOUND";
}

function mapbirthplace ($place) {
	global $dbh, $tree_id, $language, $map_max;

	if (isset($_GET['namestring'])) {
		$temparray = explode("@",$_GET['namestring']);
		$namestring = " (";
		foreach($temparray as $value) { //echo $value.'<br>';
			//$namestring .=  "pers_lastname = '".$value."' OR ";
			$namestring .= "CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) = '".$value."' OR ";
		}
		$namestring = substr($namestring,0,-3).") AND "; //echo $namestring;
	}
	else {
		$namestring = '';
	}

	$desc_arr=''; $idstring='';
	if(isset($_SESSION['desc_array'])) {
		$desc_arr = $_SESSION['desc_array'];
		$idstring = ' (';
		foreach($desc_arr as $value) {
			$idstring.= " pers_gedcomnumber = '".$value."' OR ";
		}
		$idstring = substr($idstring,0,-3).') AND ';
	}

	$min = 1;
	if($place != "NONFOUND") {
		if($_SESSION['type_birth']==1) {
			if(isset($_GET['all'])) { // the 'All birth locations' button
				echo '<b><u>'.__('All persons born here: ').'</u></b><br>';
				//$maplist=$dbh->query("SELECT * , CONCAT(pers_lastname,pers_firstname) AS wholename FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE ".$idstring.$namestring." (pers_birth_place = '".$place."' OR (pers_birth_place = '' AND pers_bapt_place = '".$place."')) ORDER BY wholename");
				$sql="SELECT * , CONCAT(pers_lastname,pers_firstname) AS wholename
					FROM humo_persons WHERE pers_tree_id='".$tree_id."'
					AND ".$idstring.$namestring." (pers_birth_place = '".$place."' OR (pers_birth_place = '' AND pers_bapt_place = '".$place."')) ORDER BY wholename";
				$maplist=$dbh->query($sql);
			}
			else { // the slider
				echo '<b><u>'.__('Persons born here until ').$map_max.':</u></b><br>';
				$sql="SELECT * , CONCAT(pers_lastname,pers_firstname) AS wholename FROM humo_persons
					WHERE pers_tree_id='".$tree_id."'
					AND ".$idstring.$namestring." (pers_birth_place = '".$place."' OR (pers_birth_place = '' AND pers_bapt_place = '".$place."'))
					AND ((SUBSTR(pers_birth_date,-LEAST(4,CHAR_LENGTH(pers_birth_date))) < ".$map_max."
					AND SUBSTR(pers_birth_date,-LEAST(4,CHAR_LENGTH(pers_birth_date))) > ".$min.")
					OR (pers_birth_date='' AND SUBSTR(pers_bapt_date,-LEAST(4,CHAR_LENGTH(pers_bapt_date))) < ".$map_max."
						AND SUBSTR(pers_bapt_date,-LEAST(4,CHAR_LENGTH(pers_bapt_date))) > ".$min."))
					ORDER BY wholename";
				$maplist=$dbh->query($sql);
			}
		}
		elseif($_SESSION['type_death']==1) {
			if(isset($_GET['all'])) { // the 'All birth locations' button
				echo '<b><u>'.__('All persons that died here: ').'</u></b><br>';
				//$maplist=$dbh->query("SELECT * , CONCAT(pers_lastname,pers_firstname) AS wholename FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE ".$idstring.$namestring." (pers_death_place = '".$place."' OR (pers_death_place = '' AND pers_buried_place = '".$place."')) ORDER BY wholename");
				$sql="SELECT * , CONCAT(pers_lastname,pers_firstname) AS wholename
					FROM humo_persons
					WHERE pers_tree_id='".$tree_id."'
					AND ".$idstring.$namestring."
					(pers_death_place = '".$place."' OR (pers_death_place = '' AND pers_buried_place = '".$place."'))
					ORDER BY wholename";
				$maplist=$dbh->query($sql);
			}
			else { // the slider
				echo '<b><u>'.__('Persons that died here until ').$map_max.':</u></b><br>';
				$sql="SELECT * , CONCAT(pers_lastname,pers_firstname) AS wholename FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND ".$idstring.$namestring."
					(pers_death_place = '".$place."' OR (pers_death_place = '' AND pers_buried_place = '".$place."')) AND
					((SUBSTR(pers_death_date,-LEAST(4,CHAR_LENGTH(pers_death_date))) < ".$map_max." AND SUBSTR(pers_death_date,-LEAST(4,CHAR_LENGTH(pers_death_date))) > ".$min.") OR
					(pers_death_date='' AND SUBSTR(pers_buried_date,-LEAST(4,CHAR_LENGTH(pers_buried_date))) < ".$map_max." AND SUBSTR(pers_buried_date,-LEAST(4,CHAR_LENGTH(pers_buried_date))) > ".$min."))
					ORDER BY wholename";
				$maplist=$dbh->query($sql);
			}
		}
//echo 'TEST: '.$sql;

		$man_cls = New person_cls;
		echo '<div style="direction:ltr">';
		while (@$maplistDb=$maplist->fetch(PDO::FETCH_OBJ)){
			$man_cls->construct($maplistDb);
			$privacy_man=$man_cls->privacy;
			$name=$man_cls->person_name($maplistDb);
			if ($name["show_name"]==true){
				echo '<a href=family.php?database='.safe_text($_SESSION['tree_prefix']).'&amp;id='.$maplistDb->pers_indexnr.'&amp;main_person='.$maplistDb->pers_gedcomnumber.' target="blank">';
			}
			if($_SESSION['type_birth']==1) {
				echo $name["index_name"];
					$date=$maplistDb->pers_birth_date;
				$sign = __('born').' ';
				if (!$maplistDb->pers_birth_date AND $maplistDb->pers_bapt_date) {
					$date = $maplistDb->pers_bapt_date;
					$sign = __('baptised').' ';
				}
			}
			if($_SESSION['type_death']==1) {
				echo $name["index_name"];
					$date=$maplistDb->pers_death_date;
				$sign = __('died').' ';
				if (!$maplistDb->pers_death_date AND $maplistDb->pers_buried_date) {
					$date = $maplistDb->pers_buried_date;
					$sign = __('buried').' ';
				}
			}
			if (!$privacy_man AND $date AND $name["show_name"]==true) {
				echo ' ('.$sign.date_place($date,'').')'; }
			if ($name["show_name"]==true){ echo '</a>'; }
			echo '<br>';
		}
		echo '</div>';
	}
	else { // Logically we can never get here
		echo 'No persons found';
	}
}

mapbirthplace($thisplace);
?>