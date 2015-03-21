<?php

/**
* This is the gedcom processing file for HuMo-gen.
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

// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

global $selected_language;
global $persids, $famsids; $noteids;
$persids = array(); $famsids = array(); $noteids = array();

echo '<H1 align=center>'.__('Gedcom file export').'</H1>';

function decode($buffer){
	//$buffer = html_entity_decode($buffer, ENT_NOQUOTES, 'ISO-8859-15');
	//$buffer = html_entity_decode($buffer, ENT_QUOTES, 'ISO-8859-15');
	if (isset($_POST['gedcom_char_set']) AND $_POST['gedcom_char_set']=='ANSI')
		$buffer=iconv("UTF-8","windows-1252",$buffer);
	return $buffer;
}

// Official gedcom 5.5.1: 255 characters total (including tags).
// Character other programs: Aldfaer about 60 char., BK about 230.
// ALDFAER:
// 1 CONC Bla, bla text.
// 1 CONT
// 1 CONT Another text.
// 1 CONC Bla bla text etc.
// Don't process first part, add if processed (can be: 2 NOTE or 3 NOTE)
function process_text($level,$text,$extractnoteids=true){
	global $noteids;

	$text = str_replace("<br>", "", $text);
	$text = str_replace("\r", "", $text);

	// *** Export referenced texts ***
	if ($extractnoteids==true){
		if (substr($text, 0, 1)=='@') $noteids[]=$text;
	}

	$regel=explode("\n",$text);
	// *** If text is too long split it, gedcom 5.5.1 specs: max. 255 characters including tag. ***
	$text=''; $text_processed='';
	for ($j=0; $j<=(count($regel)-1); $j++){
		$text=$regel[$j]."\r\n";
		if (strlen($regel[$j])>150){
			$line_length=strlen($regel[$j]);
			$words = explode(" ", $regel[$j]);
			$new_line=''; $new_line2=''; $characters=0;
			for ($x=0; $x<=(count($words)-1); $x++){
				if($x>0){ $new_line.=' '; $new_line2.=' '; }
				$new_line.=$words[$x]; $new_line2.=$words[$x];
				$characters=(strlen($new_line2));
				//if ($characters>145){
				// *** Break line if there are >5 characters left AND there are >145 characters ***
				if ($characters>145 AND $line_length-$characters>5){
					$new_line.="\r\n".$level." CONC";
					$new_line2=''; $line_length=$line_length-$characters;
				}
			}
			$text=$new_line."\r\n";
		}

		// *** First line is x NOTE, use CONT at other lines ***
		if ($j>0){
			//$text= $level.' CONT '.$text;
			if (rtrim($text)!='') $text= $level.' CONT '.$text;
				else $text="2 CONT\r\n";
		}
		$text_processed.=$text;
	}
	return $text_processed;
}

function process_place($place, $number){
	global $dbh, $tree;
	// 2 PLAC Cleveland, Ohio, USA
	// 3 MAP
	// 4 LATI N41.500347
	// 4 LONG W81.66687
	$text=$number.' PLAC '.$place."\r\n";
	if (isset($_POST['gedcom_geocode']) AND $_POST['gedcom_geocode']=='yes'){
		$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
		if($temp->rowCount() >0) {
			$geo_location_sql="SELECT * FROM humo_location
				WHERE location_location='".addslashes($place)."'";
			$geo_location_qry=$dbh->query($geo_location_sql);
			$geo_locationDb=$geo_location_qry->fetch(PDO::FETCH_OBJ);
			if ($geo_locationDb){
				$text.=($number+1).' MAP'."\r\n";

				$geocode=$geo_locationDb->location_lat;
				if(substr($geocode,0,1) == '-') { $geocode = 'S'.substr($geocode,1); }
					else { $geocode = 'N'.$geocode; }
				$text.=($number+2).' LATI '.$geocode."\r\n";

				$geocode=$geo_locationDb->location_lng;
				if(substr($geocode,0,1) == '-') { $geocode = 'W'.substr($geocode,1); }
					else { $geocode = 'E'.$geocode; }
				$text.=($number+2).' LONG '.$geocode."\r\n";
			}
		}
	}
	return $text;
}

// *** Function to export all kind of sources including role, pages etc. ***
function sources_export($connect_kind,$connect_sub_kind,$connect_connect_id,$start_number){
	global $dbh, $buffer, $tree_id;
	//$tree;
	// *** Search for all connected sources ***
	//$connect_qry="SELECT * FROM ".$tree."connections
	$connect_qry="SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."'
		AND connect_kind='".$connect_kind."'
		AND connect_sub_kind='".$connect_sub_kind."'
		AND connect_connect_id='".$connect_connect_id."'
		ORDER BY connect_order";
	$connect_sql=$dbh->query($connect_qry);
	while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
		$buffer.=$start_number.' SOUR';
		if ($connectDb->connect_source_id){ $buffer.=' @'.$connectDb->connect_source_id."@"; }
		$buffer.="\r\n";

		// *** Source text ***
		if ($connectDb->connect_text){
			// 3 DATA
			// 4 TEXT text .....
			// 5 CONT ..........
			$buffer.=($start_number+1)." DATA\r\n";
			$buffer.=($start_number+2).' TEXT '.process_text($start_number+3,$connectDb->connect_text);
		}

		if ($connectDb->connect_date){ $buffer.=($start_number+1).' DATE '.$connectDb->connect_date."\r\n"; }
		if ($connectDb->connect_place){ $buffer.=($start_number+1).' PLAC '.$connectDb->connect_place."\r\n"; }
		if ($connectDb->connect_role){ $buffer.=($start_number+1).' ROLE '.$connectDb->connect_role."\r\n"; }
		if ($connectDb->connect_page){ $buffer.=($start_number+1).' PAGE '.$connectDb->connect_page."\r\n"; }
		if ($connectDb->connect_quality){ $buffer.=($start_number+1).' QUAY '.$connectDb->connect_quality."\r\n"; }
	}
}

function descendants($family_id,$main_person,$gn,$max_generations) {
	global $dbh, $tree_id, $db_functions;
	global $persids, $famsids;
	global $language;
	$family_nr=1; //*** Process multiple families ***
	if($max_generations<$gn) { return; }
	$gn++;
	// *** Count marriages of man ***
	// *** If needed show woman as main_person ***
	if($family_id=='') { // single person
		$persids[] = $main_person;
		return;
	}

	$family=$dbh->query("SELECT fam_man, fam_woman FROM humo_families
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$family_id."'");
	try {
		@$familyDb=$family->fetch(PDO::FETCH_OBJ);
	} catch(PDOException $e) {
		echo __('No valid family number.');
	}

	$parent1=''; $parent2=''; $change_main_person=false;

	// *** Standard main_person is the father ***
	if ($familyDb->fam_man){ $parent1=$familyDb->fam_man; }
	// *** If mother is selected, mother will be main_person ***
	if ($familyDb->fam_woman==$main_person){
		$parent1=$familyDb->fam_woman;
		$change_main_person=true;
	}

	// *** Check family with parent1: N.N. ***
	if ($parent1){
		// *** Save man's families in array ***
		//$person_qry=$dbh->query("SELECT pers_fams FROM humo_persons
		//	WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$parent1."'");
		//@$personDb=$person_qry->fetch(PDO::FETCH_OBJ);
		$personDb=$db_functions->get_person($parent1);

		$marriage_array=explode(";",$personDb->pers_fams);
		$nr_families=substr_count($personDb->pers_fams, ";");
	}
	else{
		$marriage_array[0]=$family_id;
		$nr_families="0";
	}

	// *** Loop multiple marriages of main_person ***
	for ($parent1_marr=0; $parent1_marr<=$nr_families; $parent1_marr++){
		$id=$marriage_array[$parent1_marr];
		$familyDb=$db_functions->get_family($id);

		// *************************************************************
		// *** Parent1 (normally the father)                         ***
		// *************************************************************
		if ($familyDb->fam_kind!='PRO-GEN'){  //onecht kind, vrouw zonder man
			if ($family_nr==1){
				// *** Show data of man ***

				if ($change_main_person==true){
					// store I and Fs
					$persids[] = $familyDb->fam_woman;
					$families = explode(';',$personDb->pers_fams);
					foreach($families as $value) {
						$famsids[] = $value;
					}
				}
				else{
					// store I and Fs
					$persids[] = $familyDb->fam_man;
					$families = explode(';',$personDb->pers_fams);
					foreach($families as $value) {
						$famsids[] = $value;
					}
				}
			}
			$family_nr++;
		} // *** end check of PRO-GEN ***

		// *************************************************************
		// *** Parent2 (normally the mother)                         ***
		// *************************************************************
		if(isset($_POST['desc_spouses'])) {
			if ($change_main_person==true){
				$persids[] = $familyDb->fam_man;
				$desc_sp = $familyDb->fam_man;
			}
			else{
				$persids[] = $familyDb->fam_woman;
				$desc_sp = $familyDb->fam_woman;
			}
		}
		if(isset($_POST['desc_sp_parents'])) { // if set, add parents of spouse
			$spqry = $dbh->query("SELECT pers_famc FROM humo_persons
				WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$desc_sp."'");
			$spqryDb = $spqry->fetch(PDO::FETCH_OBJ);
			if(isset($spqryDb->pers_famc) AND $spqryDb->pers_famc) {
				$famqryDb = $db_functions->get_family($spqryDb->pers_famc);
				if($famqryDb->fam_man)   { $persids[] = $famqryDb->fam_man; }
				if($famqryDb->fam_woman) { $persids[] = $famqryDb->fam_woman; }
				$famsids[] = $spqryDb->pers_famc;
			}
		}
		// *************************************************************
		// *** Children                                              ***
		// *************************************************************
		if ($familyDb->fam_children){
			$childnr=1;
			$child_array=explode(";",$familyDb->fam_children);

			for ($i=0; $i<=substr_count("$familyDb->fam_children", ";"); $i++){
				$child=$dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$child_array[$i]."'");
				@$childDb=$child->fetch(PDO::FETCH_OBJ);
				if($child->rowCount()>0) {
					// *** Build descendant_report ***
					if ($childDb->pers_fams){
						// *** 1e family of child ***
						$child_family=explode(";",$childDb->pers_fams);
						$child1stfam=$child_family[0];
						descendants($child1stfam,$childDb->pers_gedcomnumber,$gn,$max_generations);  // recursive
					}
					else{  // Child without own family
						if($max_generations>=$gn) {
							$childgn=$gn+1;
							$persids[] = $childDb->pers_gedcomnumber;
						}
					}
					$childnr++;
				}
			}
		}
	} // Show  multiple marriages
} // End of descendant function

function ancestors($person_id,$max_generations) {
	global $tree, $tree_id, $dbh, $db_functions, $persids, $famsids;
	$ancestor_array2[] = $person_id;
	$ancestor_number2[]=1;
	$marriage_gedcomnumber2[]=0;
	$generation = 1;
	$listed_array=array();

	// *** Loop for ancestor report ***
	while (isset($ancestor_array2[0])){
		if($max_generations <= $generation) { return; }

		unset($ancestor_array);
		$ancestor_array=$ancestor_array2;
		unset($ancestor_array2);

		unset($ancestor_number);
		$ancestor_number=$ancestor_number2;
		unset($ancestor_number2);

		unset($marriage_gedcomnumber);
		$marriage_gedcomnumber=$marriage_gedcomnumber2;
		unset($marriage_gedcomnumber2);

		// *** Loop per generation ***
		for ($i=0; $i<count($ancestor_array); $i++) {

			$listednr='';
			foreach ($listed_array as $key => $value) {
				if($value==$ancestor_array[$i]) {$listednr=$key;}
			}
			if($listednr=='') {  //if not listed yet, add person to array
				$listed_array[$ancestor_number[$i]]=$ancestor_array[$i];
			}
			if ($ancestor_array[$i]!='0'){
				$person_manDb=$db_functions->get_person($ancestor_array[$i]);
				if (strtolower($person_manDb->pers_sexe)=='m' AND $ancestor_number[$i]>1){
					$familyDb=$db_functions->get_family($marriage_gedcomnumber[$i]);
					$person_womanDb=$db_functions->get_person($familyDb->fam_woman);
				}
				if ($listednr=='') {
					//take I and F
					if($person_manDb->pers_gedcomnumber==$person_id) { // for the base person we add spouse manually
						$persids[]=$person_manDb->pers_gedcomnumber;
						if($person_manDb->pers_fams) {
							$families = explode(';',$person_manDb->pers_fams);
							if($person_manDb->pers_sexe=='M') { $spouse = "fam_woman"; }
							else { $spouse = "fam_man"; }
							foreach($families as $value) {
								$sp_main = $dbh->query("SELECT ".$spouse." FROM humo_families
									WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber = '".$value."'");
								$sp_mainDb = $sp_main->fetch(PDO::FETCH_OBJ);
								if(isset($_POST['ances_spouses'])) { // we also include spouses of base person
									$persids[]=$sp_mainDb->$spouse;
								}
								$famsids[]=$value;
							}
						}
					}
					else { // any other person
						$persids[]=$person_manDb->pers_gedcomnumber;
					}
					if($person_manDb->pers_famc AND $generation+1 < $max_generations) {  // if this is the last generation (max gen) we don't want the famc!
						$famsids[]=$person_manDb->pers_famc;
						if(isset($_POST['ances_sibbl'])) { // also get I numbers of sibblings
							$sibbqryDb=$db_functions->get_family($person_manDb->pers_famc);
							$sibs = explode(';',$sibbqryDb->fam_children);
							foreach($sibs as $value) {
								if($value != $person_manDb->pers_gedcomnumber) {
									$persids[]=$value;
								}
							}
						}
					}
				}
				else { // person was already listed
					// do nothing
				}

				// == Check for parents
				if ($person_manDb->pers_famc  AND $listednr==''){
					$family_parentsDb=$db_functions->get_family($person_manDb->pers_famc);
					if ($family_parentsDb->fam_man){
						$ancestor_array2[] = $family_parentsDb->fam_man;
						$ancestor_number2[]=(2*$ancestor_number[$i]);
						$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
					}
					if ($family_parentsDb->fam_woman){
						$ancestor_array2[]= $family_parentsDb->fam_woman;
						$ancestor_number2[]=(2*$ancestor_number[$i]+1);
						$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
					}
					else{
						// *** N.N. name ***
						$ancestor_array2[]= '0';
						$ancestor_number2[]=(2*$ancestor_number[$i]+1);
						$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
					}
				}
			}
			else{
				// *** Show N.N. person ***
				$person_manDb=$db_functions->get_person($ancestor_array[$i]);
				// take I (and F?)
			}
		}	// loop per generation
		$generation++;
	}	// loop ancestors function
}

if (isset($_POST['tree'])){ $tree=safe_text($_POST["tree"]); }

@set_time_limit(3000);

$myFile = CMS_ROOTPATH_ADMIN."backup_tmp/gedcom.ged";
// *** FOR TESTING PURPOSES ONLY ***
if (@file_exists("../../gedcom-bestanden")) $myFile="../../gedcom-bestanden/gedcom.ged";
if (@file_exists("../../../gedcom-bestanden")) $myFile="../../../gedcom-bestanden/gedcom.ged";

// *** Remove gedcom file ***
if (isset($_POST['remove_gedcom'])){
	unlink($myFile);
	echo '<h2>'.__('Gedcom file is REMOVED.').'</h2>';
}

echo __('<b>Don\'t use a gedcom file as a backup for your genealogical data!</b> A gedcom file is only usefull to exchange genealogical data with other genealogical program\'s.
Use "Database backup" for a proper backup.').'<br><br>';

if (CMS_SPECIFIC=='Joomla'){
	echo '<form method="POST" action="index.php?option=com_humo-gen&amp;task=admin&amp;page=export" style="display : inline;">';
}
else {
	echo '<form method="POST" id="aform" action="'.$_SERVER['PHP_SELF'].'" style="display : inline;">';
}
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '<table class="humo">';

echo '<tr class="table_header"><th colspan="2">'.__('Select family tree to export and click "Start export"').'</th>';

echo '<tr><td>'.__('Choose family tree to export').'</td>';
echo '<td>';
	$tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
	$tree_result = $dbh->query($tree_sql);
	$onchange='';
	if(isset($_POST['part_tree']) AND $_POST['part_tree']=='part') {
		// we have to refresh so that the persons to choose from will belong to this tree!
		echo '<input type="hidden" name="flag_newtree" value=\'0\'>';
		$onchange = ' onChange="this.form.flag_newtree.value=\'1\';this.form.submit();" ';
	}
	echo '<select '.$onchange.' size="1" name="tree">';
		while ($treeDb=$tree_result->fetch(PDO::FETCH_OBJ)){
			$treetext=show_tree_text($treeDb->tree_prefix, $selected_language);
			$selected='';
			if (isset($tree)){
				if ($treeDb->tree_prefix==$tree){
					$selected=' SELECTED';
					// *** Needed for submitter ***
					$tree_owner=$treeDb->tree_owner;
					$tree_id=$treeDb->tree_id;
					$db_functions->set_tree_id($tree_id);
				}
			}
			echo '<option value="'.$treeDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
		}
	echo '</select></td></tr><tr><td>';
	echo __('Whole tree or part:').'</td><td>';
	$checked=' checked '; if(isset($_POST['part_tree']) AND $_POST['part_tree']=="part") $checked='';
 	echo '<input type="radio" onClick="javascript:this.form.submit();" value="whole" name="part_tree" '.$checked.'>'.__('Whole tree:');
	$checked=''; if(isset($_POST['part_tree']) AND $_POST['part_tree']=="part") $checked=' checked ';
	echo '<br><input type="radio" onClick="javascript:this.form.submit();" value="part" name="part_tree" '.$checked.'>'.__('Partial tree:');
	echo '</td></tr>';
	if(isset($_POST['part_tree']) AND $_POST['part_tree']=="part") {
		echo '<tr><td>'.__('Choose person:').'</td><td>';
		$pers_gedcomnumber='';
		if(isset($_POST['person']) AND $_POST['flag_newtree']!='1') { $pers_gedcomnumber = $_POST['person']; }
		$pers_search = $dbh->query("SELECT pers_lastname, pers_firstname, pers_gedcomnumber, pers_prefix
			FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY pers_lastname, pers_firstname");
		print '<select size="1" name="person" style="width: 300px">';
		while ($person=$pers_search->fetch(PDO::FETCH_OBJ)){
			$selected='';
			if (isset($pers_gedcomnumber)){
				if ($person->pers_gedcomnumber==$pers_gedcomnumber){ $selected=' SELECTED'; }
			}
			$prefix2=" ".strtolower(str_replace("_"," ",$person->pers_prefix));
			echo '<option value="'.$person->pers_gedcomnumber.'"'.$selected.'>'.
				$person->pers_lastname.', '.$person->pers_firstname.$prefix2.' ['.$person->pers_gedcomnumber.']</option>';
		}
		echo '</select></td></tr><tr><td>';
		echo __('Number of generations to export:').'</td><td>';
		echo '<select size="1" name="generations" style="width:80px">';
		echo '<option value="50">'.__('All').'</option>';
		for($i=1; $i<20; $i++) {
			$selected=''; if(isset($_POST['generations']) AND $_POST['generations']==$i) { $selected = " selected "; }
			echo '<option value="'.$i.'"'.$selected.'>'.($i+1).'</option>';
		}
		echo '</select></td></tr><tr><td>';
		echo __('Choose type of export:').'</td><td>';
		$checked=' checked '; if(isset($_POST['kind_tree']) AND $_POST['kind_tree']=="ancestor") $checked='';
		echo '<input type="radio" value="descendant" name="kind_tree" '.$checked.'>'.__('Descendants');
		$checked=' checked '; if(isset($_POST['kind_tree']) AND !isset($_POST['desc_spouses'])) $checked='';
		echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="desc_spouses" value="1" '.$checked.'>'.__('Include spouses of descendants');
		$checked=''; if(isset($_POST['desc_sp_parents'])) $checked=' checked ';
		echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="desc_sp_parents" value="1" '.$checked.'>'.__('Include parents of spouses');
		$checked=''; if(isset($_POST['kind_tree']) AND $_POST['kind_tree']=="ancestor") $checked=' checked ';
		echo '<br><input type="radio" value="ancestor" name="kind_tree" '.$checked.'>'.__('Ancestors');
		$checked=' checked '; if(isset($_POST['kind_tree']) AND !isset($_POST['ances_spouses'])) $checked='';
		echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="ances_spouses" value="1" '.$checked.'>'.__('Include spouse(s) of base person');
		$checked=''; if(isset($_POST['ances_sibbl'])) $checked=' checked ';
		echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="ances_sibbl" value="1" '.$checked.'>'.__('Include sibblings of ancestors and base person');
		echo '</td></tr>';
	}
echo '<tr><td>'.__('Export texts').'</td><td>';
$selected=''; if (isset($_POST['gedcom_texts']) AND $_POST['gedcom_texts']=='no'){ $selected=' SELECTED'; }
echo '<select size="1" name="gedcom_texts">';
	echo '<option value="yes">'.__('Yes').'</option>';
	echo '<option value="no"'.$selected.'>'.__('No').'</option>';
echo '</select>';
echo '</td></tr>';

echo '<tr><td>'.__('Export sources').'</td><td>';
$selected=''; if (isset($_POST['gedcom_sources']) AND $_POST['gedcom_sources']=='no'){ $selected=' SELECTED'; }
echo '<select size="1" name="gedcom_sources">';
	echo '<option value="yes">'.__('Yes').'</option>';
	echo '<option value="no"'.$selected.'>'.__('No').'</option>';
echo '</select>';
echo '</td></tr>';

// *** Check if geo_location table exists ***
$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
if($temp->rowCount() > 0) {
	echo '<tr><td>'.__('Export longitude & latitude by places').'</td><td>';
	$selected=''; if (isset($_POST['gedcom_geocode']) AND $_POST['gedcom_geocode']=='no'){ $selected=' SELECTED'; }
	echo '<select size="1" name="gedcom_geocode">';
		echo '<option value="yes">'.__('Yes').'</option>';
		echo '<option value="no"'.$selected.'>'.__('No').'</option>';
	echo '</select>';
	echo '</td></tr>';
}

echo '<tr><td>'.__('Character set').'</td><td>';
echo '<select size="1" name="gedcom_char_set">';
	$selected=''; if (isset($_POST['gedcom_char_set']) AND $_POST['gedcom_char_set']=='UTF-8'){ $selected=' SELECTED'; }
	echo '<option value="UTF-8"'.$selected.'>'.__('UTF-8 (recommended character set)').'</option>';

	$selected=''; if (isset($_POST['gedcom_char_set']) AND $_POST['gedcom_char_set']=='ANSI'){ $selected=' SELECTED'; }
	echo '<option value="ANSI"'.$selected.'>ANSI</option>';

	$selected=''; if (isset($_POST['gedcom_char_set']) AND $_POST['gedcom_char_set']=='ASCII'){ $selected=' SELECTED'; }
	echo '<option value="ASCII"'.$selected.'>ASCII</option>';
echo '</select>';
echo '</td></tr>';

echo '<tr><td>'.__('Show export status').'</td><td>';
$selected=''; if (isset($_POST['gedcom_status']) AND $_POST['gedcom_status']=='yes'){ $selected=' SELECTED'; }
echo '<select size="1" name="gedcom_status">';
	echo '<option value="no">'.__('No').'</option>';
	echo '<option value="yes"'.$selected.'>'.__('Yes').'</option>';
echo '</select>';
echo '</td></tr>';

echo '<tr><td>'.__('Gedcom export').'</td><td>';
	echo ' <input type="Submit" name="submit_button" value="'.__('Start export').'">';

	// *** Show processed lines ***
	if (isset($_POST["tree"]) AND isset($_POST['submit_button'])){
		$line_nr=0;
		echo ' <div id="information" style="display: inline;"></div> '.__('Processed lines...');
	}
echo '</td></tr>';

echo '</table>';
echo '</form>';

if (isset($_POST["tree"]) AND isset($_POST['submit_button'])){

	if(isset($_POST['part_tree']) AND $_POST['part_tree']=='part' AND isset($_POST['kind_tree']) AND $_POST['kind_tree']=="descendant") {
		// map descendants
		$desc_fams='';
		$desc_pers = $_POST['person'];
		$max_gens = $_POST['generations'];
		//$fam_search = $dbh->query("SELECT pers_fams, pers_indexnr FROM ".$tree."person WHERE pers_gedcomnumber ='".$desc_pers."'");
		$fam_search = $dbh->query("SELECT pers_fams, pers_indexnr
			FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$desc_pers."'");
		$fam_searchDb = $fam_search->fetch(PDO::FETCH_OBJ);
		if($fam_searchDb->pers_fams != '') { $desc_fams = $fam_searchDb->pers_fams; }
		else { $desc_fams = $fam_searchDb->pers_indexnr; }
		$gn=0;
		descendants($desc_fams,$desc_pers,$gn,$max_gens);
	}
	if(isset($_POST['part_tree']) AND $_POST['part_tree']=='part' AND isset($_POST['kind_tree']) AND $_POST['kind_tree']=="ancestor") {
		// map ancestors
		$anc_pers = $_POST['person'];
		$max_gens = $_POST['generations'] + 2;
		ancestors($anc_pers,$max_gens);
	}

	echo '<p>'.__('Gedcom file will be exported to backup_tmp/ folder').'<br>';
	$gedcom_texts=''; if (isset($_POST['gedcom_texts'])) $gedcom_texts=$_POST['gedcom_texts'];
	$gedcom_sources=''; if (isset($_POST['gedcom_sources'])) $gedcom_sources=$_POST['gedcom_sources'];
	$gedcom_char_set=''; if (isset($_POST['gedcom_char_set'])) $gedcom_char_set=$_POST['gedcom_char_set'];
	$fh = fopen($myFile, 'w') or die("<b>ERROR: no permission to open a new file! Please check permissions of admin/backup_tmp folder!</b>");

// *** Gedcom header ***
$buffer='';
//if ($gedcom_char_set=='UTF-8') $buffer.= "\xEF\xBB\xBF"; // *** Add BOM header to UTF-8 file ***
$buffer.="0 HEAD\r\n";
$buffer.="1 SOUR HuMo-gen\r\n";
$buffer.="2 VERS ".$humo_option["version"]."\r\n";
$buffer.="2 NAME HuMo-gen\r\n";
$buffer.="2 CORP HuMo-gen genealogical software\r\n";
$buffer.="3 ADDR http://www.humo-gen.com\r\n";
$buffer.="1 SUBM @S1@\r\n";
$buffer.="1 GEDC\r\n";
$buffer.="2 VERS 5.5.1\r\n";
$buffer.="2 FORM Lineage-Linked\r\n";

if ($gedcom_char_set=='UTF-8') $buffer.="1 CHAR UTF-8\r\n";
elseif ($gedcom_char_set=='ANSI') $buffer.="1 CHAR ANSI\r\n";
else $buffer.="1 CHAR ASCII\r\n";

// 0 @S1@ SUBM
// 1 NAME Huub Mons
// 1 ADDR adres
$buffer.="0 @S1@ SUBM\r\n";
if ($tree_owner)
	$buffer.="1 NAME ".$tree_owner."\r\n";
else
	$buffer.="1 NAME Unknown\r\n";

fwrite($fh, $buffer);
//$buffer = str_replace("\n", "<br>", $buffer);
//echo '<p>'.$buffer;

/* EXAMPLE:
0 @I1181@ INDI
1 RIN 1181
1 REFN Eigencode
1 NAME Voornaam/Achternaam/
1 SEX M
1 BIRT
2 DATE 21 FEB 1960
2 PLAC 1e woonplaats
1 RESI
2 ADDR 2e woonplaats
1 RESI
2 ADDR 3e woonplaats
1 RESI
2 ADDR 4e woonplaats
1 OCCU 1e beroep
1 OCCU 2e beroep
1 EVEN
2 TYPE living
1 _COLOR 0
1 NOTE @N51@
1 FAMS @F10@
1 FAMC @F8@
1 _NEW
2 TYPE 2
2 DATE 8 JAN 2005
3 TIME 20:31:24
*/

$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
$person_result = $dbh->query($person_qry);
while ($person=$person_result->fetch(PDO::FETCH_OBJ)){

	if(isset($_POST['part_tree']) AND $_POST['part_tree']=='part' AND !in_array($person->pers_gedcomnumber,$persids)) { continue;}

	// 0 @I1181@ INDI *** Gedcomnumber ***
	$buffer='0 @'.$person->pers_gedcomnumber."@ INDI\r\n";

	if (isset($_POST['gedcom_status']) AND $_POST['gedcom_status']=='yes') echo $person->pers_gedcomnumber . ' ';

	// 1 RIN 1181
	// Not really necessary, so disabled this line...
	//$buffer.='1 RIN '.substr($person->pers_gedcomnumber,1)."\r\n";

	// 1 REFN Code *** Own code ***
	if ($person->pers_own_code) $buffer.='1 REFN '.$person->pers_own_code."\r\n";

	// 1 NAME Firstname/Lastname/ *** Name ***
	$buffer.='1 NAME '.$person->pers_firstname.'/';
	$buffer.=str_replace("_", " ", $person->pers_prefix);
	$buffer.=$person->pers_lastname."/\r\n";

	if ($person->pers_callname) $buffer.='2 NICK '.$person->pers_callname."\r\n";

	// Prefix is exported by name!
	//if ($person->pers_prefix) $buffer.='2 SPFX '.$person->pers_prefix."\r\n";

	// *** Text and source by name ***
	if ($gedcom_sources=='yes'){
		sources_export('person','pers_name_source',$person->pers_gedcomnumber,2);
	}

	if ($gedcom_texts=='yes' AND $person->pers_name_text){
		$buffer.='2 NOTE '.process_text(3,$person->pers_name_text); }

	// *** Export all name items, like 2 _AKAN etc. ***
	$nameqry=$dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$person->pers_gedcomnumber."'
		AND event_kind='name' ORDER BY event_order");
	while($nameDb=$nameqry->fetch(PDO::FETCH_OBJ)){	
		$buffer.='2 '.$nameDb->event_gedcom.' '.$nameDb->event_event."\r\n";
		if ($nameDb->event_date) $buffer.='3 DATE '.$nameDb->event_date."\r\n";
		if ($gedcom_sources=='yes')
			sources_export('person','pers_event_source',$nameDb->event_id,3);
		if ($gedcom_texts=='yes' AND $nameDb->event_text){
			$buffer.='3 NOTE '.process_text(4,$nameDb->event_text); }
	}

	if ($person->pers_patronym) $buffer.='1 _PATR '.$person->pers_patronym."\r\n";

	// *** Sexe ***
	$buffer.='1 SEX '.$person->pers_sexe."\r\n";

	// *** Birth data ***
	if ($person->pers_birth_date OR $person->pers_birth_place OR $person->pers_birth_text
		OR 	(isset($person->pers_stillborn) AND $person->pers_stillborn=='y') ){
		$buffer.="1 BIRT\r\n";
		if ($person->pers_birth_date){ $buffer.='2 DATE '.$person->pers_birth_date."\r\n"; }
		if ($person->pers_birth_place){ $buffer.=process_place($person->pers_birth_place,2); }
		if ($person->pers_birth_time){ $buffer.='2 TIME '.$person->pers_birth_time."\r\n"; }
		if ($gedcom_sources=='yes'){
			sources_export('person','pers_birth_source',$person->pers_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $person->pers_birth_text){
			$buffer.='2 NOTE '.process_text(3,$person->pers_birth_text); }

		if (isset($person->pers_stillborn) AND $person->pers_stillborn=='y'){
			$buffer.='2 TYPE stillborn'."\r\n";
		}

	}

	// *** Christened data ***
	if ($person->pers_bapt_date OR $person->pers_bapt_place OR $person->pers_bapt_text){
		$buffer.="1 CHR\r\n";
		if ($person->pers_bapt_date){ $buffer.='2 DATE '.$person->pers_bapt_date."\r\n"; }
		if ($person->pers_bapt_place){ $buffer.=process_place($person->pers_bapt_place,2); }
		if ($gedcom_sources=='yes'){
			sources_export('person','pers_bapt_source',$person->pers_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $person->pers_bapt_text){
			$buffer.='2 NOTE '.process_text(3,$person->pers_bapt_text); }

		// *** Baptise witness ***
		$witness_qry=$dbh->query("SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$person->pers_gedcomnumber."'
			AND event_kind='baptism_witness' ORDER BY event_order");
		while($witnessDb=$witness_qry->fetch(PDO::FETCH_OBJ)){
			$buffer.='2 WITN '.$witnessDb->event_event."\r\n";
		}
	}

	// *** Person religion ***
	if ($person->pers_religion) $buffer.='1 RELI '.$person->pers_religion."\r\n";

	// *** Death data ***
	if ($person->pers_death_date OR $person->pers_death_place OR $person->pers_death_text OR $person->pers_death_cause){
		$buffer.="1 DEAT\r\n";
		if ($person->pers_death_date) $buffer.='2 DATE '.$person->pers_death_date."\r\n";
		if ($person->pers_death_place) $buffer.=process_place($person->pers_death_place,2);
		if ($person->pers_death_time) $buffer.='2 TIME '.$person->pers_death_time."\r\n";
		if ($gedcom_sources=='yes')
			sources_export('person','pers_death_source',$person->pers_gedcomnumber,2);
		if ($gedcom_texts=='yes' AND $person->pers_death_text)
			$buffer.='2 NOTE '.process_text(3,$person->pers_death_text);
		if ($person->pers_death_cause)
			$buffer.='2 CAUS '.$person->pers_death_cause."\r\n";
	}

	// *** Buried data ***
	if ($person->pers_buried_date OR $person->pers_buried_place OR $person->pers_buried_text OR $person->pers_cremation){
		$buffer.="1 BURI\r\n";
		if ($person->pers_buried_date) $buffer.='2 DATE '.$person->pers_buried_date."\r\n";
		if ($person->pers_buried_place) $buffer.=process_place($person->pers_buried_place,2);
		if ($gedcom_sources=='yes')
			sources_export('person','pers_buried_source',$person->pers_gedcomnumber,2);
		if ($gedcom_texts=='yes' AND $person->pers_buried_text)
			$buffer.='2 NOTE '.process_text(3,$person->pers_buried_text);
		if ($person->pers_cremation)
			$buffer.='2 TYPE cremation'."\r\n";
	}

	// *** Living place ***
	// 1 RESI
	// 2 ADDR Ridderkerk
	// 1 RESI
	// 2 ADDR Slikkerveer
	//$addressqry=$dbh->query("SELECT * FROM ".$tree."addresses
	//	WHERE address_person_id='$person->pers_gedcomnumber'");
	$addressqry=$dbh->query("SELECT * FROM humo_addresses
		WHERE address_tree_id='".$tree_id."' AND address_person_id='$person->pers_gedcomnumber'");
	while($addressDb=$addressqry->fetch(PDO::FETCH_OBJ)){
		$buffer.="1 RESI\r\n";
		$buffer.='2 ADDR'."\r\n";
		if ($addressDb->address_place){ $buffer.='3 CITY '.$addressDb->address_place."\r\n"; }
		if ($addressDb->address_zip){ $buffer.='3 POST '.$addressDb->address_zip."\r\n"; }
		if ($addressDb->address_phone){ $buffer.='2 PHON '.$addressDb->address_phone."\r\n"; }
		if ($addressDb->address_date){ $buffer.='2 DATE '.$addressDb->address_date."\r\n"; }
		if ($addressDb->address_text){ $buffer.='2 NOTE '.process_text(3,$addressDb->address_text); }
//SOURCE
		if ($gedcom_sources=='yes'){
		//	$buffer.='2 SOUR '.process_text(3,$addressDb->address_source);
		//	//sources_export('person','address_source',$nameDb->event_id,3);
		}
	}

	// *** Extended address (no valid gedcom 5.5.1) ***
	$eventnr=0;
	$connect_sql = $db_functions->get_connections_person('person_address',$person->pers_gedcomnumber);
	foreach ($connect_sql as $connectDb){
		//1 RESI @R210@
		$buffer.='1 RESI @'.$connectDb->connect_item_id."@\r\n";
	}

	// *** Occupation ***
	$professionqry=$dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."'
		AND event_person_id='$person->pers_gedcomnumber' AND event_kind='profession' ORDER BY event_order");
	while($professionDb=$professionqry->fetch(PDO::FETCH_OBJ)){
		$buffer.='1 OCCU '.$professionDb->event_event."\r\n";
		// *** Source by occupation ***
		if ($gedcom_sources=='yes'){
			sources_export('person','pers_event_source',$professionDb->event_id,2);
		}
	}

	// *** Person source ***
	if ($gedcom_sources=='yes'){
		sources_export('person','person_source',$person->pers_gedcomnumber,1);
	}

	// *** Person pictures ***
	$sourceqry=$dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$person->pers_gedcomnumber."'
		AND event_kind='picture' ORDER BY event_order");
	while($sourceDb=$sourceqry->fetch(PDO::FETCH_OBJ)){	
		$buffer.="1 OBJE\r\n";
		$buffer.="2 FORM jpg\r\n";
		$buffer.='2 FILE '.$sourceDb->event_event."\r\n";
		$buffer.='2 DATE '.$sourceDb->event_date."\r\n";

		if ($gedcom_texts=='yes' AND $sourceDb->event_text){
			$buffer.='2 NOTE '.process_text(3,$sourceDb->event_text); }

		if ($gedcom_sources=='yes'){
			sources_export('person','pers_event_source',$sourceDb->event_id,2);
		}
	}

	// *** Person Note ***
	if ($gedcom_texts=='yes' AND $person->pers_text){
		$buffer.='1 NOTE '.process_text(2,$person->pers_text);
		sources_export('person','pers_text_source',$person->pers_gedcomnumber,2);
	}

	// *** Person color marks ***
	$sourceqry=$dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$person->pers_gedcomnumber."'
		AND event_kind='person_colour_mark' ORDER BY event_order");
	while($sourceDb=$sourceqry->fetch(PDO::FETCH_OBJ)){	
		$buffer.='1 _COLOR '.$sourceDb->event_event."\r\n";
		//if ($gedcom_sources=='yes'){
		//	sources_export('person','pers_event_source',$sourceDb->event_id,2);
		//}
	}

	// *** Person events ***
	$event_qry=$dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$person->pers_gedcomnumber."' AND event_kind='event' ORDER BY event_order");
	while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
		$process_event=false; $process_event2=false;
		if ($eventDb->event_gedcom=='ADOP'){ $process_event2=true; $event_gedcom='1 ADOP'; }
		if ($eventDb->event_gedcom=='BAPM'){ $process_event2=true; $event_gedcom='1 BAPM'; }
		if ($eventDb->event_gedcom=='BAPL'){ $process_event2=true; $event_gedcom='1 BAPL'; }
		if ($eventDb->event_gedcom=='BARM'){ $process_event2=true; $event_gedcom='1 BARM'; }
		if ($eventDb->event_gedcom=='BASM'){ $process_event2=true; $event_gedcom='1 BASM'; }
		if ($eventDb->event_gedcom=='BLES'){ $process_event2=true; $event_gedcom='1 BLES'; }
		if ($eventDb->event_gedcom=='CENS'){ $process_event2=true; $event_gedcom='1 CENS'; }
		if ($eventDb->event_gedcom=='CHRA'){ $process_event2=true; $event_gedcom='1 CHRA'; }
		if ($eventDb->event_gedcom=='CONF'){ $process_event2=true; $event_gedcom='1 CONF'; }
		if ($eventDb->event_gedcom=='CONL'){ $process_event2=true; $event_gedcom='1 CONL'; }
		if ($eventDb->event_gedcom=='EMIG'){ $process_event2=true; $event_gedcom='1 EMIG'; }
		if ($eventDb->event_gedcom=='ENDL'){ $process_event2=true; $event_gedcom='1 ENDL'; }
		if ($eventDb->event_gedcom=='EVEN'){ $process_event2=true; $event_gedcom='1 EVEN'; }
		if ($eventDb->event_gedcom=='FCOM'){ $process_event2=true; $event_gedcom='1 FCOM'; }
		if ($eventDb->event_gedcom=='GRAD'){ $process_event2=true; $event_gedcom='1 GRAD'; }
		if ($eventDb->event_gedcom=='IMMI'){ $process_event2=true; $event_gedcom='1 IMMI'; }
		if ($eventDb->event_gedcom=='MILI'){ $process_event=true; $event_gedcom='1 _MILT'; }
		if ($eventDb->event_gedcom=='NATU'){ $process_event2=true; $event_gedcom='1 NATU'; }
		if ($eventDb->event_gedcom=='ORDN'){ $process_event2=true; $event_gedcom='1 ORDN'; }
		if ($eventDb->event_gedcom=='PROB'){ $process_event2=true; $event_gedcom='1 PROB'; }
		if ($eventDb->event_gedcom=='RETI'){ $process_event2=true; $event_gedcom='1 RETI'; }
		if ($eventDb->event_gedcom=='SLGC'){ $process_event2=true; $event_gedcom='1 SLGC'; }
		if ($eventDb->event_gedcom=='WILL'){ $process_event2=true; $event_gedcom='1 WILL'; }

		// *** Text is added in the first line: 1 _MILT military items. ***
		if ($process_event){
			if ($eventDb->event_text) $buffer.=$event_gedcom.' '.process_text(2,$eventDb->event_text);
			if ($eventDb->event_date) $buffer.='2 DATE '.$eventDb->event_date."\r\n";
			if ($eventDb->event_place) $buffer.='2 PLAC '.$eventDb->event_place."\r\n";
		}

		// *** No text behind first line, add text at second NOTE line ***
		if ($process_event2){
			$buffer.=$event_gedcom;
				if ($eventDb->event_event) $buffer.=' '.$eventDb->event_event;
				$buffer.="\r\n";
			if ($eventDb->event_text) $buffer.='2 NOTE '.process_text(3,$eventDb->event_text);
			if ($eventDb->event_date) $buffer.='2 DATE '.$eventDb->event_date."\r\n";
			if ($eventDb->event_place) $buffer.='2 PLAC '.$eventDb->event_place."\r\n";
		}
	}

	// *** Quality ***
	// Disabled because normally quality belongs to a source.
	//if ($person->pers_quality=='0' or $person->pers_quality){
	//	$buffer.='2 QUAY '.$person->pers_quality."\r\n";
	//}

	// *** FAMS ***
	if ($person->pers_fams){
		$pers_fams=explode(";",$person->pers_fams);
		for ($i=0; $i<=substr_count($person->pers_fams, ";"); $i++){
			if($_POST['part_tree']=='part' AND !in_array($pers_fams[$i],$famsids)) { continue; }
			$buffer.='1 FAMS @'.$pers_fams[$i]."@\r\n";
		}
	}

	// *** FAMC ***
	if ($person->pers_famc){
		if($_POST['part_tree']=='part' AND !in_array($person->pers_famc,$famsids)) { } // don't export FAMC
		else { $buffer.='1 FAMC @'.$person->pers_famc."@\r\n"; }
	}

	// *** Privacy filter, HuMo-gen, Haza-data ***
	if ($person->pers_alive == 'alive'){
		$buffer.="1 EVEN\r\n";
		$buffer.="2 TYPE living\r\n";
	}
	// *** Privacy filter option for HuMo-gen ***
	if ($person->pers_alive == 'deceased'){
		$buffer.="1 EVEN\r\n";
		$buffer.="2 TYPE deceased\r\n";
	}

	// *** Date and time new in database ***
	// 1_NEW
	// 2 DATE 04 AUG 2004
	// 3 TIME 13:39:58
	if ($person->pers_new_date){
		$buffer.="1 _NEW\r\n";
		$buffer.="2 DATE ".$person->pers_new_date."\r\n";
		if ($person->pers_new_time) $buffer.="3 TIME ".$person->pers_new_time."\r\n";
	}

	// *** Date and time changed in database ***
	// 1_CHAN
	// 2 DATE 04 AUG 2004
	// 3 TIME 13:39:58
	if ($person->pers_changed_date){
		$buffer.="1 CHAN\r\n";
		$buffer.="2 DATE ".$person->pers_changed_date."\r\n";
		if ($person->pers_changed_time) $buffer.="3 TIME ".$person->pers_changed_time."\r\n";
	}

	// *** Write person data ***
	$buffer=decode($buffer);
	fwrite($fh, $buffer);


	// *** Update processed lines ***
	echo '<script language="javascript">';
	echo 'document.getElementById("information").innerHTML="'.$line_nr.'";';
	$line_nr++;
	echo '</script>';
	// This is for the buffer achieve the minimum size in order to flush data
	//echo str_repeat(' ',1024*64);
	// Send output to browser immediately
	ob_flush(); 
	flush(); // IE



	// *** Show person data on screen ***
	//$buffer = str_replace("\r\n", "<br>", $buffer);
	//echo $buffer;
}

/* EXAMPLE
0 @F1@ FAM
1 HUSB @I2@
1 WIFE @I3@
1 MARL
2 DATE 25 AUG 1683
2 PLAC Arnhem
1 MARR
2 TYPE civil
2 DATE 30 NOV 1683
2 PLAC Arnhem
2 NOTE @N311@
1 CHIL @I4@
1 CHIL @I5@
1 CHIL @I6@
*/

// *** FAMILY DATA ***
//$family_qry=$dbh->query("SELECT * FROM ".$tree."family");
$family_qry=$dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."'");
while($family=$family_qry->fetch(PDO::FETCH_OBJ)){

	if($_POST['part_tree']=='part'  AND !in_array($family->fam_gedcomnumber,$famsids)) { continue;}

	// 0 @I1181@ INDI *** Gedcomnumber ***
	$buffer='0 @'.$family->fam_gedcomnumber."@ FAM\r\n";

	if (isset($_POST['gedcom_status']) AND $_POST['gedcom_status']=='yes') echo $family->fam_gedcomnumber. ' ';

	if ($family->fam_man){
		if($_POST['part_tree']=='part' AND !in_array($family->fam_man,$persids)) {}
		// skip if not included (e.g. if spouse of base person in ancestor export or spouses of descendants in desc export are not checked for export)
		else { $buffer.='1 HUSB @'.$family->fam_man."@\r\n"; }
	}

	if ($family->fam_woman){
		if($_POST['part_tree']=='part' AND !in_array($family->fam_woman,$persids)) {} // skip if not included
		else { $buffer.='1 WIFE @'.$family->fam_woman."@\r\n"; }
	}

	// *** Living together ***
	if ($family->fam_relation_date OR $family->fam_relation_place OR $family->fam_relation_text){
		$buffer.="1 NMR\r\n";

		// *** Relation start date ***
		if ($family->fam_relation_date) $buffer.='2 DATE '.$family->fam_relation_date."\r\n";

		// *** Relation end date ***
		// How to export this date?

		if ($family->fam_relation_place){
			$buffer.=process_place($family->fam_relation_place,2);
		}
		if ($gedcom_sources=='yes'){
			sources_export('family','fam_relation_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_relation_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_relation_text); }
	}

	// *** Marriage notice ***
	if ($family->fam_marr_notice_date OR $family->fam_marr_notice_place OR $family->fam_marr_notice_text){
		$buffer.="1 MARB\r\n";
		$buffer.="2 TYPE civil\r\n";
		if ($family->fam_marr_notice_date){
			$buffer.='2 DATE '.$family->fam_marr_notice_date."\r\n";
		}
		if ($family->fam_marr_notice_place){
			$buffer.=process_place($family->fam_marr_notice_place,2);
		}
		if ($gedcom_sources=='yes'){
			sources_export('family','fam_marr_notice_source',$family->fam_gedcomnumber,2);
		}

		if ($gedcom_texts=='yes' AND $family->fam_marr_notice_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_marr_notice_text); }
	}

	// *** Marriage notice church ***
	if ($family->fam_marr_church_notice_date OR $family->fam_marr_church_notice_place OR $family->fam_marr_church_notice_text){
		$buffer.="1 MARB\r\n";
		$buffer.="2 TYPE religous\r\n";
		if ($family->fam_marr_church_notice_date){
			$buffer.='2 DATE '.$family->fam_marr_church_notice_date."\r\n";
		}
		if ($family->fam_marr_church_notice_place){
			$buffer.=process_place($family->fam_marr_church_notice_place,2);
		}
		if ($gedcom_sources=='yes'){
			sources_export('family','fam_marr_church_notice_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_marr_church_notice_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_marr_church_notice_text); }
	}

	// *** Marriage ***
	if ($family->fam_marr_date OR $family->fam_marr_place OR $family->fam_marr_text){
		$buffer.="1 MARR\r\n";
		$buffer.="2 TYPE civil\r\n";
		if ($family->fam_marr_date){ $buffer.='2 DATE '.$family->fam_marr_date."\r\n"; }
		if ($family->fam_marr_place){ $buffer.=process_place($family->fam_marr_place,2); }
		if ($gedcom_sources=='yes'){
			sources_export('family','fam_marr_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_marr_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_marr_text); }
	}

	// *** Marriage church ***
	if ($family->fam_marr_church_date OR $family->fam_marr_church_place OR $family->fam_marr_church_text){
		$buffer.="1 MARR\r\n";
		$buffer.="2 TYPE religous\r\n";
		if ($family->fam_marr_church_date){ $buffer.='2 DATE '.$family->fam_marr_church_date."\r\n"; }
		if ($family->fam_marr_church_place){ $buffer.=process_place($family->fam_marr_church_place,2); }
		if ($gedcom_sources=='yes'){
			sources_export('family','fam_marr_church_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_marr_church_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_marr_church_text); }
	}

	// *** Divorced ***
	if ($family->fam_div_date OR $family->fam_div_place OR $family->fam_div_text){
		$buffer.="1 DIV\r\n";
		if ($family->fam_div_date){ $buffer.='2 DATE '.$family->fam_div_date."\r\n"; }
		if ($family->fam_div_place){ $buffer.=process_place($family->fam_div_place,2); }
		if ($gedcom_sources=='yes'){
			sources_export('family','fam_div_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_div_text AND $family->fam_div_text!='DIVORCE'){
			$buffer.='2 NOTE '.process_text(3,$family->fam_div_text); }
	}

	if ($family->fam_children){
		$child=explode(";",$family->fam_children);
		for ($i=0; $i<=substr_count($family->fam_children, ";"); $i++){
			if($_POST['part_tree']=='part' AND !in_array($child[$i],$persids))  { continue; }
			$buffer.='1 CHIL @'.$child[$i]."@\r\n";
		}
	}

	// *** Family source ***
	if ($gedcom_sources=='yes'){
		sources_export('family','family_source',$family->fam_gedcomnumber,1);
	}

	// *** Family Note ***
	if ($gedcom_texts=='yes' AND $family->fam_text){
		$buffer.='1 NOTE '.process_text(2,$family->fam_text);
		sources_export('family','fam_text_source',$family->fam_gedcomnumber,2);
	}

	// *** Family events ***
	$event_qry=$dbh->query("SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."' AND event_family_id='".$family->fam_gedcomnumber."'
		AND event_kind='event' ORDER BY event_order");
	while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
		$process_event=false; $process_event2=false;
		if ($eventDb->event_gedcom=='ANUL'){ $process_event2=true; $event_gedcom='1 ANUL'; }
		if ($eventDb->event_gedcom=='CENS'){ $process_event2=true; $event_gedcom='1 CENS'; }
		if ($eventDb->event_gedcom=='DIVF'){ $process_event2=true; $event_gedcom='1 DIVF'; }
		if ($eventDb->event_gedcom=='ENGA'){ $process_event2=true; $event_gedcom='1 ENGA'; }
		if ($eventDb->event_gedcom=='EVEN'){ $process_event2=true; $event_gedcom='1 EVEN'; }
		if ($eventDb->event_gedcom=='MARC'){ $process_event2=true; $event_gedcom='1 MARC'; }
		if ($eventDb->event_gedcom=='MARL'){ $process_event2=true; $event_gedcom='1 MARL'; }
		if ($eventDb->event_gedcom=='MARS'){ $process_event2=true; $event_gedcom='1 MARS'; }
		if ($eventDb->event_gedcom=='SLGS'){ $process_event2=true; $event_gedcom='1 SLGS'; }

		// *** Text is added in the first line: 1 _MILT military items. ***
		//if ($process_event){
		//	if ($eventDb->event_text) $buffer.=$event_gedcom.' '.process_text(2,$eventDb->event_text);
		//	if ($eventDb->event_date) $buffer.='2 DATE '.$eventDb->event_date."\r\n";
		//	if ($eventDb->event_place) $buffer.='2 PLAC '.$eventDb->event_place."\r\n";
		//}

		// *** No text behind first line, add text at second NOTE line ***
		if ($process_event2){
			$buffer.=$event_gedcom;
				if ($eventDb->event_event) $buffer.=' '.$eventDb->event_event;
				$buffer.="\r\n";
			if ($eventDb->event_text) $buffer.='2 NOTE '.process_text(3,$eventDb->event_text);
			if ($eventDb->event_date) $buffer.='2 DATE '.$eventDb->event_date."\r\n";
			if ($eventDb->event_place) $buffer.='2 PLAC '.$eventDb->event_place."\r\n";
		}
	}

	// *** Date and time new in database ***
	// 1_NEW
	// 2 DATE 04 AUG 2004
	// 3 TIME 13:39:58
	if ($family->fam_new_date){
		$buffer.="1 _NEW\r\n";
		$buffer.="2 DATE ".$family->fam_new_date."\r\n";
		if ($family->fam_new_time) $buffer.="3 TIME ".$family->fam_new_time."\r\n";
	}

	// *** Date and time changed in database ***
	// 1_CHAN
	// 2 DATE 04 AUG 2004
	// 3 TIME 13:39:58
	if ($family->fam_changed_date){
		$buffer.="1 CHAN\r\n";
		$buffer.="2 DATE ".$family->fam_changed_date."\r\n";
		if ($family->fam_changed_time) $buffer.="3 TIME ".$family->fam_changed_time."\r\n";
	}

	// *** Write family data ***
	$buffer=decode($buffer);
	fwrite($fh, $buffer);

	// *** Update processed lines ***
	echo '<script language="javascript">';
	echo 'document.getElementById("information").innerHTML="'.$line_nr.'";';
	$line_nr++;
	echo '</script>';
	// This is for the buffer achieve the minimum size in order to flush data
	//echo str_repeat(' ',1024*64);
	// Send output to browser immediately
	ob_flush(); 
	flush(); // IE

	// *** Show family data on screen ***
	//$buffer = str_replace("\r\n", "<br>", $buffer);
	//echo $buffer;
}


// *** Sources ***
//0 @S1@ SOUR
//1 TITL Persoonskaarten
//1 DATE 24 JAN 2003
//1 PLAC Heerhugowaard
//1 REFN Pers-v
//1 PHOTO @#APLAATJES\AKTEMONS.GIF GIF@
//2 DSCR Afbeelding van Persoonskaarten
//1 PHOTO @#APLAATJES\HUUB&LIN.JPG JPG@
//2 DSCR Beschrijving
//1 NOTE Persoonskaarten (van overleden personen) besteld bij CBVG te Den Haag.

if($_POST['part_tree']=='part') {  // only include sources that are used by the people in this partial tree
	$source_array= array();
	// find all sources referred to by persons (I233) or families (F233)
	//$qry = $dbh->query("SELECT connect_connect_id, connect_source_id FROM ".$tree."connections
	//	WHERE connect_source_id != ''");
	$qry = $dbh->query("SELECT connect_connect_id, connect_source_id FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND connect_source_id != ''");
	while($qryDb=$qry->fetch(PDO::FETCH_OBJ)){
		if(in_array($qryDb->connect_connect_id,$persids) OR in_array($qryDb->connect_connect_id,$famsids)) {
			$source_array[]=$qryDb->connect_source_id;
		}
	}
	// find all sources referred to by addresses (233)
	// extended addresses: we need a three-fold procedure....
	// First: in the connections table search for exported persons/families that have an RESI number connection (R34)
	//$address_connect_qry = $dbh->query("SELECT connect_connect_id, connect_item_id FROM ".$tree."connections WHERE connect_sub_kind LIKE '%_address'");
	$address_connect_qry = $dbh->query("SELECT connect_connect_id, connect_item_id
		FROM humo_connections WHERE connect_tree_id='".$tree_id."' AND connect_sub_kind LIKE '%_address'");
	$resi_array = array();
	while($address_connect_qryDb=$address_connect_qry->fetch(PDO::FETCH_OBJ)){
		if(in_array($address_connect_qryDb->connect_connect_id,$persids) or in_array($address_connect_qryDb->connect_connect_id,$famsids)) {
			$resi_array[] = $address_connect_qryDb->connect_item_id;
		}
	}
	// Second: in the address table search for the previously found R numbers and get their id number (33)
	//$address_address_qry = $dbh->query("SELECT address_gedcomnr, address_id FROM ".$tree."addresses WHERE address_gedcomnr !='' ");
	$address_address_qry = $dbh->query("SELECT address_gedcomnr, address_id FROM humo_addresses
		WHERE address_tree_id='".$tree_id."' AND address_gedcomnr !='' ");
	$resi_id_array = array();
	while($address_address_qryDb=$address_address_qry->fetch(PDO::FETCH_OBJ)){
		if(in_array($address_address_qryDb->address_gedcomnr,$resi_array)) {
			$resi_id_array[] = $address_address_qryDb->address_id;
		}
	}
	// Third: back in the connections table, find the previously found address id numbers and get the associated source ged number ($23)
	//$address_connect2_qry = $dbh->query("SELECT connect_connect_id, connect_source_id
	//	FROM ".$tree."connections
	//	WHERE connect_sub_kind = 'address_source'");
	$address_connect2_qry = $dbh->query("SELECT connect_connect_id, connect_source_id
		FROM humo_connections
		WHERE connect_tree_id='".$tree_id."' AND connect_sub_kind = 'address_source'");
	while($address_connect2_qry_qryDb=$address_connect2_qry->fetch(PDO::FETCH_OBJ)){
		if(in_array($address_connect2_qry_qryDb->connect_connect_id,$resi_id_array)) {
			$source_array[] = $address_connect2_qry_qryDb->connect_source_id;
		}
	}
	// "direct" addresses
	//$addressqry = $dbh->query("SELECT address_id, address_person_id, address_family_id FROM ".$tree."addresses");
	$addressqry = $dbh->query("SELECT address_id, address_person_id, address_family_id FROM humo_addresses
		WHERE address_tree_id='".$tree_id."'");
	$source_address_array=array();
	while($addressqryDb=$addressqry->fetch(PDO::FETCH_OBJ)){
		if($addressqryDb->address_person_id!='' AND in_array($addressqryDb->address_person_id,$persids)) {
			$source_address_array[] = $addressqryDb->address_id;
		}
		if($addressqryDb->address_family_id!='' AND in_array($addressqryDb->address_family_id,$famsids)) {
			$source_address_array[] = $addressqryDb->address_id;
		}
	}
	//$addresssourceqry = $dbh->query("SELECT connect_source_id, connect_connect_id FROM ".$tree."connections WHERE connect_sub_kind LIKE 'address_%'");
	$addresssourceqry = $dbh->query("SELECT connect_source_id, connect_connect_id
		FROM humo_connections WHERE connect_tree_id='".$tree_id."' AND connect_sub_kind LIKE 'address_%'");
	while($addresssourceqryDb=$addresssourceqry->fetch(PDO::FETCH_OBJ)){
		if(in_array($addresssourceqryDb->connect_connect_id,$source_address_array)) {
			$source_array[] = $addresssourceqryDb->connect_source_id;
		}
	}

	// find all sources referred to by events (233)
	$eventqry = $dbh->query("SELECT event_id, event_person_id, event_family_id FROM humo_events");
	$source_event_array = array();
	while($eventqryDb=$eventqry->fetch(PDO::FETCH_OBJ)){
		if($eventqryDb->event_person_id!='' AND in_array($eventqryDb->event_person_id,$persids)) {
			$source_event_array[] = $eventqryDb->event_id;
		}
		if($eventqryDb->event_family_id!='' AND in_array($eventqryDb->event_family_id,$famsids)) {
			$source_event_array[] = $eventqryDb->event_id;
		}
	}
	//$eventsourceqry = $dbh->query("SELECT connect_source_id, connect_connect_id FROM ".$tree."connections WHERE connect_sub_kind LIKE 'event_%'");
	$eventsourceqry = $dbh->query("SELECT connect_source_id, connect_connect_id
		FROM humo_connections WHERE connect_tree_id='".$tree_id."' AND connect_sub_kind LIKE 'event_%'");
	while($eventsourceqryDb=$eventsourceqry->fetch(PDO::FETCH_OBJ)){
		if(in_array($eventsourceqryDb->connect_connect_id,$source_event_array)) {
			$source_array[] = $eventsourceqryDb->connect_source_id;
		}
	}

	// eliminate duplicates
	if(isset($source_array)) {
		$source_array = array_unique($source_array);
	}
}

if ($gedcom_sources=='yes'){
	//$family_qry=$dbh->query("SELECT * FROM ".$tree."sources");
	$family_qry=$dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='".$tree_id."'");
	while($family=$family_qry->fetch(PDO::FETCH_OBJ)){
		if($_POST['part_tree']=='part'  AND !in_array($family->source_gedcomnr,$source_array)) { continue; }

		// 0 @I1181@ INDI *** Gedcomnumber ***
		$buffer='0 @'.$family->source_gedcomnr."@ SOUR\r\n";

		if (isset($_POST['gedcom_status']) AND $_POST['gedcom_status']=='yes') echo $family->source_gedcomnr. ' ';

		if ($family->source_title){ $buffer.='1 TITL '.$family->source_title."\r\n"; }
		if ($family->source_abbr){ $buffer.='1 ABBR '.$family->source_abbr."\r\n"; }
		if ($family->source_date){ $buffer.='1 DATE '.$family->source_date."\r\n"; }
		if ($family->source_place){ $buffer.='1 PLAC '.$family->source_place."\r\n"; }
		if ($family->source_publ){ $buffer.='1 PUBL '.$family->source_publ."\r\n"; }
		if ($family->source_refn){ $buffer.='1 REFN '.$family->source_refn."\r\n"; }
		if ($family->source_auth){ $buffer.='1 AUTH '.$family->source_auth."\r\n"; }
		if ($family->source_subj){ $buffer.='1 SUBJ '.$family->source_subj."\r\n"; }
		if ($family->source_item){ $buffer.='1 ITEM '.$family->source_item."\r\n"; }
		if ($family->source_kind){ $buffer.='1 KIND '.$family->source_kind."\r\n"; }
		if ($family->source_text){ $buffer.='1 NOTE '.process_text(2,$family->source_text); }
		if (isset($family->source_status) AND $family->source_status=='restricted'){ $buffer.='1 RESN privacy'."\r\n"; }

		// photo = old gedcom tag?
		if ($family->source_photo){ $buffer.='1 PHOT '.$family->source_photo."\r\n"; }

		// source_repo_name, source_repo_caln, source_repo_page.

		// *** Date and time new in database ***
		// 1_NEW
		// 2 DATE 04 AUG 2004
		// 3 TIME 13:39:58
		if ($family->source_new_date){
			$buffer.="1 _NEW\r\n";
			$buffer.="2 DATE ".$family->source_new_date."\r\n";
			if ($family->source_new_time) $buffer.="3 TIME ".$family->source_new_time."\r\n";
		}

		// *** Date and time changed in database ***
		// 1_CHAN
		// 2 DATE 04 AUG 2004
		// 3 TIME 13:39:58
		if ($family->source_changed_date){
			$buffer.="1 CHAN\r\n";
			$buffer.="2 DATE ".$family->source_changed_date."\r\n";
			if ($family->source_changed_time) $buffer.="3 TIME ".$family->source_changed_time."\r\n";
		}

		// *** Write source data ***
		$buffer=decode($buffer);
		fwrite($fh, $buffer);

		// *** Update processed lines ***
		echo '<script language="javascript">';
		echo 'document.getElementById("information").innerHTML="'.$line_nr.'";';
		$line_nr++;
		echo '</script>';
		// This is for the buffer achieve the minimum size in order to flush data
		//echo str_repeat(' ',1024*64);
		// Send output to browser immediately
		ob_flush(); 
		flush(); // IE

		// *** Show source data on screen ***
		//$buffer = str_replace("\n", "<br>", $buffer);
		//echo $buffer;
	}

	/*
	repo_place='".$editor_cls->text_process($_POST['repo_place'])."',
	repo_date='".$editor_cls->date_process('repo_date')."',
	repo_mail='".safe_text($_POST['repo_mail'])."',
	repo_url='".safe_text($_POST['repo_url'])."',
	*/
	// *** Repository data ***
	//$repo_qry=$dbh->query("SELECT * FROM ".$tree."repositories
	//	ORDER BY repo_name, repo_place");
	$repo_qry=$dbh->query("SELECT * FROM humo_repositories
		WHERE repo_tree_id='".$tree_id."'
		ORDER BY repo_name, repo_place");
	while($repoDb=$repo_qry->fetch(PDO::FETCH_OBJ)){
		$buffer='0 @'.$repoDb->repo_gedcomnr."@ REPO\r\n";
		if ($repoDb->repo_name){ $buffer.='1 NAME '.$repoDb->repo_name."\r\n"; }
		if ($repoDb->repo_text){ $buffer.='1 NOTE '.process_text(2,$repoDb->repo_text); }
		if ($repoDb->repo_address){ $buffer.='1 ADDR '.process_text(2,$repoDb->repo_address); }
		if ($repoDb->repo_zip){ $buffer.='2 POST '.$repoDb->repo_zip."\r\n"; }
		if ($repoDb->repo_phone){ $buffer.='1 PHON '.$repoDb->repo_phone."\r\n"; }
		if ($repoDb->repo_mail){ $buffer.='1 EMAIL '.$repoDb->repo_mail."\r\n"; }
		if ($repoDb->repo_url){ $buffer.='1 WWW '.$repoDb->repo_url."\r\n"; }

		// *** Date and time new in database ***
		// 1_NEW
		// 2 DATE 04 AUG 2004
		// 3 TIME 13:39:58
		if ($repoDb->repo_new_date){
			$buffer.="1 _NEW\r\n";
			$buffer.="2 DATE ".$repoDb->repo_new_date."\r\n";
			if ($repoDb->repo_new_time) $buffer.="3 TIME ".$repoDb->repo_new_time."\r\n";
		}

		// *** Date and time changed in database ***
		// 1_CHAN
		// 2 DATE 04 AUG 2004
		// 3 TIME 13:39:58
		if ($repoDb->repo_changed_date){
			$buffer.="1 CHAN\r\n";
			$buffer.="2 DATE ".$repoDb->repo_changed_date."\r\n";
			if ($repoDb->repo_changed_time) $buffer.="3 TIME ".$repoDb->repo_changed_time."\r\n";
		}

		// *** Write repoitory data ***
		$buffer=decode($buffer);
		fwrite($fh, $buffer);

		// *** Update processed lines ***
		echo '<script language="javascript">';
		echo 'document.getElementById("information").innerHTML="'.$line_nr.'";';
		$line_nr++;
		echo '</script>';
		// This is for the buffer achieve the minimum size in order to flush data
		//echo str_repeat(' ',1024*64);
		// Send output to browser immediately
		ob_flush(); 
		flush(); // IE

	}

}

// *** THIS PART ISN'T VALID GEDCOM 5.5.1!!!!!!!! ***
// *** Addresses ***
// 0 @R155@ RESI
// 1 ADDR Straat
// 1 PLAC Plaats
// *** ADDRESS IS ADDED BY PERSON! ***
$family_qry=$dbh->query("SELECT * FROM humo_addresses
	WHERE address_tree_id='".$tree_id."' AND address_gedcomnr LIKE '_%'");
while($family=$family_qry->fetch(PDO::FETCH_OBJ)){
	// 0 @I1181@ INDI *** Gedcomnumber ***
	$buffer='0 @'.$family->address_gedcomnr."@ RESI\r\n";

	if ($family->address_address){ $buffer.='1 ADDR '.$family->address_address."\r\n"; }
	if ($family->address_zip){ $buffer.='1 ZIP '.$family->address_zip."\r\n"; }
	if ($family->address_date){ $buffer.='1 DATE '.$family->address_date."\r\n"; }
	if ($family->address_place){ $buffer.='1 PLAC '.$family->address_place."\r\n"; }
	if ($family->address_phone){ $buffer.='1 PHON '.$family->address_phone."\r\n"; }
	if ($gedcom_sources=='yes'){
		//SOURCE
	}
	if ($family->address_text){ $buffer.='1 NOTE '.process_text(2,$family->address_text); }

	// photo

	// *** Write source data ***
	$buffer=decode($buffer);
	fwrite($fh, $buffer);
	// *** Show source data on screen ***
	//$buffer = str_replace("\r\n", "<br>", $buffer);
	//echo $buffer;
}

// *** Notes ***
// 0 @N1@ NOTE
// 1 CONT Start of the note
// 2 CONC add a bit more to the line
// 2 CONT Another line of the note

// This adds seperate note records for all the note refs in table texts captured in $noteids
if ($gedcom_texts=='yes'){
	$buffer='';
	natsort($noteids);
	foreach ($noteids as $s){
		//$text_query = "SELECT * FROM ".$tree."texts WHERE text_gedcomnr='".$s."'";
		//$text_query = "SELECT * FROM ".$tree."texts WHERE text_gedcomnr='".substr($s,1,-1)."'";
		$text_query = "SELECT * FROM humo_texts
			WHERE text_tree_id='".$tree_id."' AND text_gedcomnr='".substr($s,1,-1)."'";
		$text_sql=$dbh->query($text_query);
		while($textDb=$text_sql->fetch(PDO::FETCH_OBJ)){
			$buffer.="0 ".$s." NOTE\r\n";
			$buffer.='1 CONC '.process_text(1,$textDb->text_text);

			// *** Date and time new in database ***
			// 1_NEW
			// 2 DATE 04 AUG 2004
			// 3 TIME 13:39:58
			if ($textDb->text_new_date){
				$buffer.="1 _NEW\r\n";
				$buffer.="2 DATE ".$textDb->text_new_date."\r\n";
				if ($textDb->text_new_time) $buffer.="3 TIME ".$textDb->text_new_time."\r\n";
			}

			// *** Date and time changed in database ***
			// 1_CHAN
			// 2 DATE 04 AUG 2004
			// 3 TIME 13:39:58
			if ($textDb->text_changed_date){
				$buffer.="1 CHAN\r\n";
				$buffer.="2 DATE ".$textDb->text_changed_date."\r\n";
				if ($textDb->text_changed_time) $buffer.="3 TIME ".$textDb->text_changed_time."\r\n";
			}

		}
	}

	// *** Write note data ***
	$buffer=decode($buffer);
	fwrite($fh, $buffer);

	// *** Update processed lines ***
	echo '<script language="javascript">';
	echo 'document.getElementById("information").innerHTML="'.$line_nr.'";';
	$line_nr++;
	echo '</script>';
	// This is for the buffer achieve the minimum size in order to flush data
	//echo str_repeat(' ',1024*64);
	// Send output to browser immediately
	ob_flush(); 
	flush(); // IE
}

fwrite($fh, '0 TRLR');
fclose($fh);

echo '<p>'.__('Gedcom file is generated').'<br>';

echo '<form method="POST" action="include/gedcom_download.php" target="_blank">';
echo ' <input type="Submit" name="something" value="'.__('Download gedcom file').'">';
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '</form><br>';

if (CMS_SPECIFIC=='Joomla'){
	echo '<form method="POST" action="index.php?option=com_humo-gen&amp;task=admin&amp;page=export">';
}
else {
	echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
}

echo ' <input type="Submit" name="remove_gedcom" value="'.__('Remove gedcom file').'">';
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '</form>';

} // end of tree
?>