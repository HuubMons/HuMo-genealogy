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
include_once (CMS_ROOTPATH.'include/database_name.php');

echo '<H1 align=center>'.__('Gedcom file export').'</H1>';

function decode($buffer){
	//$buffer = html_entity_decode($buffer, ENT_NOQUOTES, 'ISO-8859-15');
	//$buffer = html_entity_decode($buffer, ENT_QUOTES, 'ISO-8859-15');

	if (isset($_POST['gedcom_char_set']) AND $_POST['gedcom_char_set']=='ANSI')
		$buffer=iconv("UTF-8","windows-1252",$buffer);

	return $buffer;
}

// NOTE line max. 80 characters long (Aldfaer about 60 char., BK about 230)
// ALDFAER:
// 1 CONC Bla, bla text.
// 1 CONT
// 1 CONT Another text.
// 1 CONC Bla bla text etc.
// Don't process first part, add if processed (can be: 2 NOTE or 3 NOTE)
function process_text($level,$text,$extractnoteids=true){
	global $noteids;

	$text = str_replace("<br>", "", $text);

	// *** Export referenced texts ***
	if ($extractnoteids==true){
		if (substr($text, 0, 1)=='@'){
			$noteids[]=$text;
		}
	}

	$regel=explode("\n",$text);
	// *** If text is too long split it ***
	$text=''; $text_processed='';
	for ($j=0; $j<=(count($regel)-1); $j++){
		$text=$regel[$j]."\n";
		if (strlen($regel[$j])>80){
			$words = explode(" ", $regel[$j]);
			$new_line=''; $new_line2=''; $characters=0;
			for ($x=0; $x<=(count($words)-1); $x++){
				if($x>0){ $new_line.=' '; $new_line2.=' '; }
				$new_line.=$words[$x]; $new_line2.=$words[$x];
				$characters=(strlen($new_line2));
					if ($characters>75){
					$new_line.="\n".$level." CONC"; $new_line2='';
				}
			}
			$text=$new_line."\n";
		}

		// *** First line is x NOTE, only CONT at higher lines ***
		if ($j>0){ $text= $level.' CONT '.$text; }
		$text_processed.=$text;
	}
	return $text_processed;
}

function process_place($place, $number){
	global $db,$dbh, $tree;
	// 2 PLAC Cleveland, Ohio, USA
	// 3 MAP
	// 4 LATI N41.500347
	// 4 LONG W81.66687
	$text=$number.' PLAC '.$place."\n";
	if (isset($_POST['gedcom_geocode']) AND $_POST['gedcom_geocode']=='yes'){
		//if (mysql_num_rows( mysql_query("SHOW TABLES LIKE 'humo_location'", $db))) {
		$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
		if($temp->rowCount() >0) {
			$geo_location_sql="SELECT * FROM humo_location
				WHERE location_location='".addslashes($place)."'";				
			//$geo_location_qry=mysql_query($geo_location_sql,$db);
			$geo_location_qry=$dbh->query($geo_location_sql);			
			//$geo_locationDb=mysql_fetch_object($geo_location_qry);
			$geo_locationDb=$geo_location_qry->fetch(PDO::FETCH_OBJ);
			if ($geo_locationDb){
				$text.=($number+1).' MAP'."\n";

				$geocode=$geo_locationDb->location_lat;
				if(substr($geocode,0,1) == '-') { $geocode = 'S'.substr($geocode,1); }
					else { $geocode = 'N'.$geocode; }
				$text.=($number+2).' LATI '.$geocode."\n";
			
				$geocode=$geo_locationDb->location_lng;
				if(substr($geocode,0,1) == '-') { $geocode = 'W'.substr($geocode,1); }
					else { $geocode = 'E'.$geocode; }
				$text.=($number+2).' LONG '.$geocode."\n";
			}
		}
	}
	return $text;
}

// *** Function to export all kind of sources including role, pages etc. ***
function sources_export($connect_kind,$connect_sub_kind,$connect_connect_id,$start_number){
	global $db, $dbh, $buffer,$tree;
	// *** Search for all connected sources ***
	$connect_qry="SELECT * FROM ".$tree."connections
		WHERE connect_kind='".$connect_kind."'
		AND connect_sub_kind='".$connect_sub_kind."'
		AND connect_connect_id='".$connect_connect_id."'
		ORDER BY connect_order";
	//$connect_sql=mysql_query($connect_qry,$db);
	$connect_sql=$dbh->query($connect_qry);
	//while($connectDb=mysql_fetch_object($connect_sql)){
	while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
		$buffer.=$start_number.' SOUR';
		if ($connectDb->connect_source_id){
			$buffer.=' @'.$connectDb->connect_source_id."@";
		}
		$buffer.="\n";

		// *** Source text ***
		if ($connectDb->connect_text){
			// 3 DATA
			// 4 TEXT text .....
			// 5 CONT ..........
			$buffer.=($start_number+1)." DATA\n";
			$buffer.=($start_number+2).' TEXT '.process_text($start_number+3,$connectDb->connect_text);
		}

		if ($connectDb->connect_date){
			$buffer.=($start_number+1).' DATE '.$connectDb->connect_date."\n";
		}

		if ($connectDb->connect_place){
			$buffer.=($start_number+1).' PLAC '.$connectDb->connect_place."\n";
		}

		if ($connectDb->connect_role){
			$buffer.=($start_number+1).' ROLE '.$connectDb->connect_role."\n";
		}

		if ($connectDb->connect_page){
			$buffer.=($start_number+1).' PAGE '.$connectDb->connect_page."\n";
		}
	}
}

function descendants($family_id,$main_person,$gn,$max_generations) {
	global $persids, $famsids;
	global $db, $dbh, $tree;
	global $language;
	$family_nr=1; //*** Process multiple families ***
	if($max_generations<$gn) { return; }
	$gn++;
	// *** Count marriages of man ***
	// *** If needed show woman as main_person ***
	if($family_id=='') { // loose person
		$persids[] = $main_person;
		return;
	}
	//$family=mysql_query("SELECT fam_man, fam_woman FROM ".$tree.'family
	//	WHERE fam_gedcomnumber="'.$family_id.'"',$db);
	$family=$dbh->query("SELECT fam_man, fam_woman FROM ".$tree.'family
		WHERE fam_gedcomnumber="'.$family_id.'"');		
	//@$familyDb=mysql_fetch_object($family) or die("'.__('No valid family number.').'");
	//@$familyDb=mysql_fetch_object($family) or die(__('No valid family number.'));
	try {
		@$familyDb=$family->fetch(PDO::FETCH_OBJ);
	} catch(PDOException $e) {
		echo __('No valid family number.');
	}

	$parent1=''; $parent2='';	$change_main_person=false;

	// *** Standard main_person is the father ***
	if ($familyDb->fam_man){
		$parent1=$familyDb->fam_man;
	}
	// *** If mother is selected, mother will be main_person ***
	if ($familyDb->fam_woman==$main_person){
		$parent1=$familyDb->fam_woman;
		$change_main_person=true;
	}

	// *** Check family with parent1: N.N. ***
	if ($parent1){
		// *** Save man's families in array ***
		//$person_qry=mysql_query("SELECT pers_fams FROM ".$tree."person
		//	WHERE pers_gedcomnumber='$parent1'",$db);
		$person_qry=$dbh->query("SELECT pers_fams FROM ".$tree."person
			WHERE pers_gedcomnumber='$parent1'");			
		//@$personDb=mysql_fetch_object($person_qry);
		@$personDb=$person_qry->fetch(PDO::FETCH_OBJ);
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
		//$family=mysql_query("SELECT * FROM ".$tree."family WHERE fam_gedcomnumber='$id'",$db);
		//@$familyDb=mysql_fetch_object($family);
		$family=$dbh->query("SELECT * FROM ".$tree."family WHERE fam_gedcomnumber='$id'");
		@$familyDb=$family->fetch(PDO::FETCH_OBJ);		

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
			//$spqry = mysql_query("SELECT pers_famc FROM ".$tree."person WHERE pers_gedcomnumber = '".$desc_sp."'");
			//$spqryDb = mysql_fetch_object($spqry,$db);
			$spqry = $dbh->query("SELECT pers_famc FROM ".$tree."person WHERE pers_gedcomnumber = '".$desc_sp."'");
			$spqryDb = $spqry->fetch(PDO::FETCH_OBJ);			
			if($qryDb->pers_famc) {
					//$famqry = mysql_query("SELECT * FROM ".$tree."family WHERE fam_gedcomnumber = '".$qryDb->pers_famc."'");
					//$famqryDb = mysql_fetch_object($famqry,$db);
					$famqry = $dbh->query("SELECT * FROM ".$tree."family WHERE fam_gedcomnumber = '".$qryDb->pers_famc."'");
					$famqryDb = $famqry->fetch(PDO::FETCH_OBJ);					
					if($famqryDb->fam_man)   { $persids[] = $famqryDb->fam_man; }
					if($famqryDb->fam_woman) { $persids[] = $famqryDb->fam_woman; }
					$famsids[] = $qryDb->pers_famc;
			}
		}
		// *************************************************************
		// *** Children                                              ***
		// *************************************************************
		if ($familyDb->fam_children){
			$childnr=1;
			$child_array=explode(";",$familyDb->fam_children);

			for ($i=0; $i<=substr_count("$familyDb->fam_children", ";"); $i++){
				//$child=mysql_query("SELECT * FROM ".$tree."person
				//	WHERE pers_gedcomnumber='$child_array[$i]'",$db);
				//@$childDb=mysql_fetch_object($child);
				$child=$dbh->query("SELECT * FROM ".$tree."person
					WHERE pers_gedcomnumber='$child_array[$i]'");
				@$childDb=$child->fetch(PDO::FETCH_OBJ);				
				//if(mysql_num_rows($child)>0) {
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

	global $tree, $db, $dbh, $persids, $famsids;
	$ancestor_array2[] = $person_id;
	$ancestor_number2[]=1;
	$marriage_gedcomnumber2[]=0;
	$generation = 1;
	$listed_array=array();
	
	// some prepared statements before loops
	$pers_prep = $dbh->prepare("SELECT * FROM ".$tree."person WHERE pers_gedcomnumber=?");
	$pers_prep->bindParam(1,$pers_prep_var);
	$fam_prep = ("SELECT * FROM ".$tree."family WHERE fam_gedcomnumber='?");
	$fam_prep->bindParam(1,$fam_prep_var);
	
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
				//$person_man=mysql_query("SELECT * FROM ".$tree."person WHERE pers_gedcomnumber='".$ancestor_array[$i]."'",$db);
				//@$person_manDb=mysql_fetch_object($person_man);
				$pers_prep_var = $ancestor_array[$i];
				$pers_prep->execute();
				@$person_manDb = $pers_prep->fetch(PDO::FETCH_OBJ);
				if (strtolower($person_manDb->pers_sexe)=='m' AND $ancestor_number[$i]>1){
					//$family_qry=mysql_query("SELECT * FROM ".$tree."family
					//	WHERE fam_gedcomnumber='".safe_text($marriage_gedcomnumber[$i])."'",$db);
					//@$familyDb=mysql_fetch_object($family_qry);
					$fam_prep_var = $marriage_gedcomnumber[$i];
					$fam_prep->execute();
					@$familyDb = $fam_prep->fetch(PDO::FETCH_OBJ);
					//$person_woman=mysql_query("SELECT * FROM ".$tree."person
					//	WHERE pers_gedcomnumber='".safe_text($familyDb->fam_woman)."'",$db);
					//@$person_womanDb=mysql_fetch_object($person_woman);
					$pers_prep_var = $familyDb->fam_woman;
					$pers_prep->execute();
					@$person_womanDb = $pers_prep->fetch(PDO::FETCH_OBJ);
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
									//$sp_main = mysql_query("SELECT ".$spouse." FROM ".$tree."family WHERE fam_gedcomnumber = '".$value."'");
									//$sp_mainDb = mysql_fetch_object($sp_main);
									$sp_main = $dbh->query("SELECT ".$spouse." FROM ".$tree."family WHERE fam_gedcomnumber = '".$value."'");
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
								//$sibbqry = mysql_query("SELECT * FROM ".$tree."family WHERE fam_gedcomnumber = '".$person_manDb->pers_famc."'",$db);
								//$sibbqryDb = mysql_fetch_object($sibbqry);
								$fam_prep_var = $person_manDb->pers_famc;
								$fam_prep->execute();
								$sibbqryDb = $fam_prep->fetch(PDO::FETCH_OBJ);
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

				// ==	Check for parents
				if ($person_manDb->pers_famc  AND $listednr==''){
					//$family_parents_qry	= "SELECT * FROM ".$tree."family WHERE fam_gedcomnumber = '".$person_manDb->pers_famc."'";
					//$family_parents_result = mysql_query($family_parents_qry,$db);
					//@$family_parentsDb = mysql_fetch_object($family_parents_result);
					$fam_prep_var = $person_manDb->pers_famc;
					$fam_prep->execute();
					@$family_parentsDb = $fam_prep->fetch(PDO::FETCH_OBJ);
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
				//$person_man=mysql_query("SELECT * FROM ".$tree."person 
				//	WHERE pers_gedcomnumber='".safe_text($ancestor_array[$i])."'",$db);
				//@$person_manDb=mysql_fetch_object($person_man);
				$pers_prep_var = $ancestor_array[$i];
				$pers_prep->execute();
				@$person_manDb = $pers_prep->fetch(PDO::FETCH_OBJ);
				// take I (and F?)
			}
		}	// loop per generation
		$generation++;
	}	// loop ancestors function
}

if (isset($_POST['tree'])){
	$tree=safe_text($_POST["tree"]);
}

@set_time_limit(3000);

$myFile = CMS_ROOTPATH_ADMIN."backup_tmp/gedcom.ged";
// *** FOR TESTING PURPOSES ONLY ***
if (@file_exists("../../gedcom-bestanden")){
	$myFile="../../gedcom-bestanden/gedcom.ged";
}
if (@file_exists("../../../gedcom-bestanden")){
	$myFile="../../../gedcom-bestanden/gedcom.ged";
}

// *** Remove gedcom file ***
if (isset($_POST['remove_gedcom'])){
	unlink($myFile);
	echo '<h2>'.__('Gedcom file is REMOVED.').'</h2>';
}

// TEMPORARY MESSAGE will be removed later when gedcom export is finished...
//echo ' <h3 style="color: red;">'.__('The gedcom export is not completely ready yet!').'</h3>';
//echo ' <h3 style="color: red;">'. __('Select family tree to export and click "Start export"').'</h3>';

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
	//$tree_result = mysql_query($tree_sql,$db);
	$tree_result = $dbh->query($tree_sql);
	$onchange='';
	if(isset($_POST['part_tree']) AND $_POST['part_tree']=='part') {
		// we have to refresh so that the persons to choose from will belong to this tree!
		echo '<input type="hidden" name="flag_newtree" value=\'0\'>';
		$onchange = ' onChange="this.form.flag_newtree.value=\'1\';this.form.submit();" ';
	}
	echo '<select '.$onchange.' size="1" name="tree">';
		//while ($treeDb=mysql_fetch_object($tree_result)){
		while ($treeDb=$tree_result->fetch(PDO::FETCH_OBJ)){
			$treetext_name=database_name($treeDb->tree_prefix, $selected_language);
			$selected='';
			if (isset($tree)){
				if ($treeDb->tree_prefix==$tree){
					$selected=' SELECTED';
					// *** Needed for submitter ***
					$tree_owner=$treeDb->tree_owner;
				}
			}
			echo '<option value="'.$treeDb->tree_prefix.'"'.$selected.'>'.@$treetext_name.'</option>';
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
		//$pers_search = mysql_query("SELECT pers_lastname, pers_firstname, pers_gedcomnumber, pers_prefix FROM ".$tree."person ORDER BY pers_lastname, pers_firstname",$db);
		$pers_search = $dbh->query("SELECT pers_lastname, pers_firstname, pers_gedcomnumber, pers_prefix FROM ".$tree."person ORDER BY pers_lastname, pers_firstname");
		print '<select size="1" name="person" style="width: 300px">';
		//while ($person=mysql_fetch_object($pers_search)){
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
//if (mysql_num_rows( mysql_query("SHOW TABLES LIKE 'humo_location'", $db))) {
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
echo '</td></tr>';

echo '</table>';
echo '</form>';

if (isset($_POST["tree"]) AND isset($_POST['submit_button'])){

	if(isset($_POST['part_tree']) AND $_POST['part_tree']=='part' AND isset($_POST['kind_tree']) AND $_POST['kind_tree']=="descendant") {
		// map descendants
		$desc_fams='';
		$desc_pers = $_POST['person'];
		$max_gens = $_POST['generations'];
		//$fam_search = mysql_query("SELECT pers_fams, pers_indexnr FROM ".$tree."person WHERE pers_gedcomnumber ='".$desc_pers."'", $db);
		//$fam_searchDb = mysql_fetch_object($fam_search);
		$fam_search = $dbh->query("SELECT pers_fams, pers_indexnr FROM ".$tree."person WHERE pers_gedcomnumber ='".$desc_pers."'");
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
	$gedcom_texts='';
	if (isset($_POST['gedcom_texts'])){	$gedcom_texts=$_POST['gedcom_texts']; }

	$gedcom_sources='';
	if (isset($_POST['gedcom_sources'])){	$gedcom_sources=$_POST['gedcom_sources']; }

	$gedcom_char_set='';
	if (isset($_POST['gedcom_char_set'])){	$gedcom_char_set=$_POST['gedcom_char_set']; }

	//$tree=safe_text($_POST["tree"]);   YB: $tree is used already for $selected above and for the desc and ances function so was moved to higher up
	//echo '<p>'.__('Gedcom file will be exported to backup_tmp/ folder').'<br>';
	$fh = fopen($myFile, 'w') or die("can't open file");

// *** Gedcom header ***
$buffer="0 HEAD\n";
$buffer.="1 SOUR HuMo-gen\n";
$buffer.="2 VERS ".$humo_option["version"]."\n";
$buffer.="2 NAME HuMo-gen\n";
$buffer.="2 CORP HuMo-gen genealogical software\n";
$buffer.="3 ADDR http://www.humo-gen.com\n";

if ($tree_owner)
	$buffer.="1 SUBM ".$tree_owner."\n";
else
	$buffer.="1 SUBM Unknown\n";

$buffer.="1 GEDC\n";
$buffer.="2 VERS 5.5\n";
$buffer.="2 FORM Lineage-Linked\n";

if ($gedcom_char_set=='UTF-8'){
	$buffer.="1 CHAR UTF-8\n";
}
elseif ($gedcom_char_set=='ANSI'){
	$buffer.="1 CHAR ANSI\n";
}
else{
	$buffer.="1 CHAR ASCII\n";
}

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

$person_qry= "SELECT * FROM ".$tree."person";
//$person_result = mysql_query($person_qry,$db);
//while ($person=mysql_fetch_object($person_result)){
$person_result = $dbh->query($person_qry);
while ($person=$person_result->fetch(PDO::FETCH_OBJ)){

	if(isset($_POST['part_tree']) AND $_POST['part_tree']=='part' AND !in_array($person->pers_gedcomnumber,$persids)) { continue;}

	// 0 @I1181@ INDI *** Gedcomnumber ***
	$buffer='0 @'.$person->pers_gedcomnumber."@ INDI\n";

	if (isset($_POST['gedcom_status']) AND $_POST['gedcom_status']=='yes'){
		echo $person->pers_gedcomnumber . ' ';
	}

	// 1 RIN 1181
	$buffer.='1 RIN '.substr($person->pers_gedcomnumber,1)."\n";

	// 1 REFN Code *** Own code ***
	if ($person->pers_own_code){
		$buffer.='1 REFN '.$person->pers_own_code."\n";
	}

	// 1 NAME Firstname/Lastname/ *** Name ***
	$buffer.='1 NAME '.$person->pers_firstname.'/';
	$buffer.=str_replace("_", " ", $person->pers_prefix);
	$buffer.=$person->pers_lastname."/\n";

	if ($person->pers_callname){
		$buffer.='2 NICK '.$person->pers_callname."\n";
	}

	// Prefix is exported by name!
	//if ($person->pers_prefix){
	//	$buffer.='2 SPFX '.$person->pers_prefix."\n";
	//}

	// *** Text and source by name ***
	if ($gedcom_sources=='yes' AND $person->pers_name_source){
		sources_export('person','pers_name_source',$person->pers_gedcomnumber,2);
	}

	if ($gedcom_texts=='yes' AND $person->pers_name_text){
		$buffer.='2 NOTE '.process_text(3,$person->pers_name_text); }

	// *** Export all name items, like 2 _AKAN etc. ***
	//$nameqry=mysql_query("SELECT * FROM ".$tree."events
	//	WHERE event_person_id='$person->pers_gedcomnumber' AND event_kind='name'",$db);
	//while($nameDb=mysql_fetch_object($nameqry)){
	$nameqry=$dbh->query("SELECT * FROM ".$tree."events
		WHERE event_person_id='$person->pers_gedcomnumber' AND event_kind='name'");
	while($nameDb=$nameqry->fetch(PDO::FETCH_OBJ)){	
		$buffer.='2 '.$nameDb->event_gedcom.' '.$nameDb->event_event."\n";
		if ($nameDb->event_date){	$buffer.='3 DATE '.$nameDb->event_date."\n"; }
		if ($gedcom_sources=='yes' AND $nameDb->event_source){
			sources_export('person','event_source',$nameDb->event_id,3);
		}
		if ($gedcom_texts=='yes' AND $nameDb->event_text){
			$buffer.='3 NOTE '.process_text(4,$nameDb->event_text); }
	}

	if ($person->pers_patronym){
		$buffer.='1 _PATR '.$person->pers_patronym."\n";
	}

	// *** Sexe ***
	$buffer.='1 SEX '.$person->pers_sexe."\n";

	// *** Birth data ***
	if ($person->pers_birth_date OR $person->pers_birth_place OR $person->pers_birth_text
		OR 	(isset($person->pers_stillborn) AND $person->pers_stillborn=='y') ){
		$buffer.="1 BIRT\n";
		if ($person->pers_birth_date){
			$buffer.='2 DATE '.$person->pers_birth_date."\n";
		}
		if ($person->pers_birth_place){
			$buffer.=process_place($person->pers_birth_place,2);
		}
		if ($person->pers_birth_time){
			$buffer.='2 TIME '.$person->pers_birth_time."\n";
		}
		if ($gedcom_sources=='yes' AND $person->pers_birth_source){
			sources_export('person','pers_birth_source',$person->pers_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $person->pers_birth_text){
			$buffer.='2 NOTE '.process_text(3,$person->pers_birth_text); }

		if (isset($person->pers_stillborn) AND $person->pers_stillborn=='y'){
			$buffer.='2 TYPE stillborn'."\n";
		}

	}

	// *** Christened data ***
	if ($person->pers_bapt_date OR $person->pers_bapt_place OR $person->pers_bapt_text){
		$buffer.="1 CHR\n";
		if ($person->pers_bapt_date){
			$buffer.='2 DATE '.$person->pers_bapt_date."\n";
		}
		if ($person->pers_bapt_place){
			$buffer.=process_place($person->pers_bapt_place,2);
		}
		if ($gedcom_sources=='yes' AND $person->pers_bapt_source){
			sources_export('person','pers_bapt_source',$person->pers_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $person->pers_bapt_text){
			$buffer.='2 NOTE '.process_text(3,$person->pers_bapt_text); }

		// *** Baptise witness ***
		//$witness_qry=mysql_query("SELECT * FROM ".$tree."events
		//	WHERE event_person_id='".$person->pers_gedcomnumber."' AND event_kind='baptism_witness'",$db);
		//while($witnessDb=mysql_fetch_object($witness_qry)){
		$witness_qry=$dbh->query("SELECT * FROM ".$tree."events
			WHERE event_person_id='".$person->pers_gedcomnumber."' AND event_kind='baptism_witness'");
		while($witnessDb=$witness_qry->fetch(PDO::FETCH_OBJ)){		
			$buffer.='2 WITN '.$witnessDb->event_event."\n";
		}
	}

	// *** Person religion ***
	if ($person->pers_religion){
		$buffer.='1 RELI '.$person->pers_religion."\n";
	}

	// *** Death data ***
	if ($person->pers_death_date	OR $person->pers_death_place OR $person->pers_death_text OR $person->pers_death_cause){
		$buffer.="1 DEAT\n";
		if ($person->pers_death_date)
			$buffer.='2 DATE '.$person->pers_death_date."\n";
		if ($person->pers_death_place)
			$buffer.=process_place($person->pers_death_place,2);
		if ($person->pers_death_time)
			$buffer.='2 TIME '.$person->pers_death_time."\n";
		if ($gedcom_sources=='yes' AND $person->pers_death_source)
			sources_export('person','pers_death_source',$person->pers_gedcomnumber,2);
		if ($gedcom_texts=='yes' AND $person->pers_death_text)
			$buffer.='2 NOTE '.process_text(3,$person->pers_death_text);
		if ($person->pers_death_cause)
			$buffer.='2 CAUS '.$person->pers_death_cause."\n";
	}

	// *** Buried data ***
	if ($person->pers_buried_date OR $person->pers_buried_place OR $person->pers_buried_text OR $person->pers_cremation){
		$buffer.="1 BURI\n";
		if ($person->pers_buried_date)
			$buffer.='2 DATE '.$person->pers_buried_date."\n";
		if ($person->pers_buried_place)
			$buffer.=process_place($person->pers_buried_place,2);
		if ($gedcom_sources=='yes' AND $person->pers_buried_source)
			sources_export('person','pers_buried_source',$person->pers_gedcomnumber,2);
		if ($gedcom_texts=='yes' AND $person->pers_buried_text)
			$buffer.='2 NOTE '.process_text(3,$person->pers_buried_text);
		if ($person->pers_cremation)
			$buffer.='2 TYPE cremation'."\n";
	}

	// *** Living place ***
	// 1 RESI
	// 2 ADDR Ridderkerk
	// 1 RESI
	// 2 ADDR Slikkerveer
	//$addressqry=mysql_query("SELECT * FROM ".$tree."addresses
	//	WHERE address_person_id='$person->pers_gedcomnumber'",$db);
	//while($addressDb=mysql_fetch_object($addressqry)){
	$addressqry=$dbh->query("SELECT * FROM ".$tree."addresses
		WHERE address_person_id='$person->pers_gedcomnumber'");
	while($addressDb=$addressqry->fetch(PDO::FETCH_OBJ)){	
		$buffer.="1 RESI\n";
		$buffer.='2 ADDR'."\n";
		if ($addressDb->address_place){
			$buffer.='3 CITY '.$addressDb->address_place."\n";
		}
		if ($addressDb->address_zip){
			$buffer.='3 POST '.$addressDb->address_zip."\n";
		}
		if ($addressDb->address_phone){
			$buffer.='2 PHON '.$addressDb->address_phone."\n";
		}
		if ($addressDb->address_date){
			$buffer.='2 DATE '.$addressDb->address_date."\n";
		}
		if ($addressDb->address_text){
			$buffer.='2 NOTE '.process_text(3,$addressDb->address_text)."\n";
		}
//SOURCE
		if ($addressDb->address_source){
			$buffer.='2 SOUR '.process_text(3,$addressDb->address_source)."\n";
			//sources_export('person','address_source',$nameDb->event_id,3);
		}
	}

	// *** Occupation ***
	//$professionqry=mysql_query("SELECT * FROM ".$tree."events
	//	WHERE event_person_id='$person->pers_gedcomnumber' AND event_kind='profession'",$db);
	//while($professionDb=mysql_fetch_object($professionqry)){
	$professionqry=$dbh->query("SELECT * FROM ".$tree."events
		WHERE event_person_id='$person->pers_gedcomnumber' AND event_kind='profession'");
	while($professionDb=$professionqry->fetch(PDO::FETCH_OBJ)){	
		$buffer.='1 OCCU '.$professionDb->event_event."\n";
		// *** Source by occupation ***
		if ($gedcom_sources=='yes' AND $professionDb->event_source){
			sources_export('person','event_source',$professionDb->event_id,2);
		}
	}

	// *** Person source ***
	if ($gedcom_sources=='yes'){
		sources_export('person','person_source',$person->pers_gedcomnumber,1);
	}

	// *** Person pictures ***
	//$sourceqry=mysql_query("SELECT * FROM ".$tree."events
	//	WHERE event_person_id='$person->pers_gedcomnumber' AND event_kind='picture'",$db);
	//while($sourceDb=mysql_fetch_object($sourceqry)){
	$sourceqry=$dbh->query("SELECT * FROM ".$tree."events
		WHERE event_person_id='$person->pers_gedcomnumber' AND event_kind='picture'");
	while($sourceDb=$sourceqry->fetch(PDO::FETCH_OBJ)){	
		$buffer.="1 OBJE\n";
		$buffer.="2 FORM jpg\n";
		$buffer.='2 FILE '.$sourceDb->event_event."\n";
		$buffer.='2 DATE '.$sourceDb->event_date."\n";

		if ($gedcom_texts=='yes' AND $sourceDb->event_text){
			$buffer.='2 NOTE '.process_text(3,$sourceDb->event_text); }

		if ($gedcom_sources=='yes' AND $sourceDb->event_source){
			sources_export('person','event_source',$sourceDb->event_id,2);
		}
	}

	// *** Person Note ***
	if ($gedcom_texts=='yes' AND $person->pers_text){
		$buffer.='1 NOTE '.process_text(2,$person->pers_text);
		sources_export('person','pers_text_source',$person->pers_gedcomnumber,2);
	}


	// *** Person events ***


	// *** FAMS ***
	if ($person->pers_fams){
		$pers_fams=explode(";",$person->pers_fams);
		for ($i=0; $i<=substr_count($person->pers_fams, ";"); $i++){
			if($_POST['part_tree']=='part' AND !in_array($pers_fams[$i],$famsids)) { continue; }
			$buffer.='1 FAMS @'.$pers_fams[$i]."@\n";
		}
	}

	// *** FAMC ***
	if ($person->pers_famc){
		if($_POST['part_tree']=='part' AND !in_array($person->pers_famc,$famsids)) { } // don't export FAMC
		else { $buffer.='1 FAMC @'.$person->pers_famc."@\n"; }
	}

	// *** Privacy filter, HuMo-gen, Haza-data ***
	if ($person->pers_alive == 'alive'){
		$buffer.="1 EVEN\n";
		$buffer.="2 TYPE living\n";
	}
	// *** Privacy filter option for HuMo-gen ***
	if ($person->pers_alive == 'deceased'){
		$buffer.="1 EVEN\n";
		$buffer.="2 TYPE deceased\n";
	}

	// *** Date and time new in database ***
	// 1_NEW
	// 2 DATE 04 AUG 2004
	// 3 TIME 13:39:58
	if ($person->pers_new_date){
		$buffer.="1 _NEW\n";
		$buffer.="2 DATE ".$person->pers_new_date."\n";
		if ($person->pers_new_time){
			$buffer.="3 TIME ".$person->pers_new_time."\n";
		}
	}

	// *** Date and time changed in database ***
	// 1_CHAN
	// 2 DATE 04 AUG 2004
	// 3 TIME 13:39:58
	if ($person->pers_changed_date){
		$buffer.="1 CHAN\n";
		$buffer.="2 DATE ".$person->pers_changed_date."\n";
		if ($person->pers_changed_time){
			$buffer.="3 TIME ".$person->pers_changed_time."\n";
		}
	}

	// *** Write person data ***
	$buffer=decode($buffer);
	fwrite($fh, $buffer);
	// *** Show person data on screen ***
	//$buffer = str_replace("\n", "<br>", $buffer);
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
//$family_qry=mysql_query("SELECT * FROM ".$tree."family",$db);
//while($family=mysql_fetch_object($family_qry)){
$family_qry=$dbh->query("SELECT * FROM ".$tree."family");
while($family=$family_qry->fetch(PDO::FETCH_OBJ)){

	if($_POST['part_tree']=='part'  AND !in_array($family->fam_gedcomnumber,$famsids)) { continue;}

	// 0 @I1181@ INDI *** Gedcomnumber ***
	$buffer='0 @'.$family->fam_gedcomnumber."@ FAM\n";

	if (isset($_POST['gedcom_status']) AND $_POST['gedcom_status']=='yes'){
		echo $family->fam_gedcomnumber. ' ';
	}

	if ($family->fam_man){
		if($_POST['part_tree']=='part' AND !in_array($family->fam_man,$persids)) {}
		// skip if not included (e.g. if spouse of base person in ancestor export or spouses of descendants in desc export are not checked for export)
		else { $buffer.='1 HUSB @'.$family->fam_man."@\n"; }
	}

	if ($family->fam_woman){
		if($_POST['part_tree']=='part' AND !in_array($family->fam_woman,$persids)) {} // skip if not included
		else { $buffer.='1 WIFE @'.$family->fam_woman."@\n"; }
	}

	// *** Living together ***
	if ($family->fam_relation_date OR $family->fam_relation_place OR $family->fam_relation_text){
		$buffer.="1 NMR\n";

		// *** Relation start date ***
		if ($family->fam_relation_date){
			$buffer.='2 DATE '.$family->fam_relation_date."\n";
		}

		// *** Relation end date ***
		// How to export this date?

		if ($family->fam_relation_place){
			$buffer.=process_place($family->fam_relation_place,2);
		}
		if ($gedcom_sources=='yes' AND $family->fam_relation_source){
			sources_export('family','fam_relation_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_relation_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_relation_text); }
	}

	// *** Marriage notice ***
	if ($family->fam_marr_notice_date OR $family->fam_marr_notice_place OR $family->fam_marr_notice_text){
		$buffer.="1 MARB\n";
		$buffer.="2 TYPE civil\n";
		if ($family->fam_marr_notice_date){
			$buffer.='2 DATE '.$family->fam_marr_notice_date."\n";
		}
		if ($family->fam_marr_notice_place){
			$buffer.=process_place($family->fam_marr_notice_place,2);
		}
		if ($gedcom_sources=='yes' AND $family->fam_marr_notice_source){
			sources_export('family','fam_marr_notice_source',$family->fam_gedcomnumber,2);
		}

		if ($gedcom_texts=='yes' AND $family->fam_marr_notice_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_marr_notice_text); }
	}

	// *** Marriage notice church ***
	if ($family->fam_marr_church_notice_date OR $family->fam_marr_church_notice_place OR $family->fam_marr_church_notice_text){
		$buffer.="1 MARB\n";
		$buffer.="2 TYPE religous\n";
		if ($family->fam_marr_church_notice_date){
			$buffer.='2 DATE '.$family->fam_marr_church_notice_date."\n";
		}
		if ($family->fam_marr_church_notice_place){
			$buffer.=process_place($family->fam_marr_church_notice_place,2);
		}
		if ($gedcom_sources=='yes' AND $family->fam_marr_church_notice_source){
			sources_export('family','fam_marr_church_notice_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_marr_church_notice_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_marr_church_notice_text); }
	}

	// *** Marriage ***
	if ($family->fam_marr_date OR $family->fam_marr_place OR $family->fam_marr_text){
		$buffer.="1 MARR\n";
		$buffer.="2 TYPE civil\n";
		if ($family->fam_marr_date){
			$buffer.='2 DATE '.$family->fam_marr_date."\n";
		}
		if ($family->fam_marr_place){
			$buffer.=process_place($family->fam_marr_place,2);
		}
		if ($gedcom_sources=='yes' AND $family->fam_marr_source){
			sources_export('family','fam_marr_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_marr_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_marr_text); }
	}

	// *** Marriage church ***
	if ($family->fam_marr_church_date OR $family->fam_marr_church_place OR $family->fam_marr_church_text){
		$buffer.="1 MARR\n";
		$buffer.="2 TYPE religous\n";
		if ($family->fam_marr_church_date){
			$buffer.='2 DATE '.$family->fam_marr_church_date."\n";
		}
		if ($family->fam_marr_church_place){
			$buffer.=process_place($family->fam_marr_church_place,2);
		}
		if ($gedcom_sources=='yes' AND $family->fam_marr_church_source){
			sources_export('family','fam_marr_church_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_marr_church_text){
			$buffer.='2 NOTE '.process_text(3,$family->fam_marr_church_text); }
	}

	// *** Divorced ***
	if ($family->fam_div_date OR $family->fam_div_place OR $family->fam_div_text){
		$buffer.="1 DIV\n";
		if ($family->fam_div_date){
			$buffer.='2 DATE '.$family->fam_div_date."\n";
		}
		if ($family->fam_div_place){
			$buffer.=process_place($family->fam_div_place,2);
		}
		if ($gedcom_sources=='yes' AND $family->fam_div_source){
			sources_export('family','fam_div_source',$family->fam_gedcomnumber,2);
		}
		if ($gedcom_texts=='yes' AND $family->fam_div_text AND $family->fam_div_text!='DIVORCE'){
			$buffer.='2 NOTE '.process_text(3,$family->fam_div_text); }
	}

	if ($family->fam_children){
		$child=explode(";",$family->fam_children);
		for ($i=0; $i<=substr_count($family->fam_children, ";"); $i++){
			if($_POST['part_tree']=='part' AND !in_array($child[$i],$persids))  { continue; }
			$buffer.='1 CHIL @'.$child[$i]."@\n";
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

	// *** Date and time new in database ***
	// 1_NEW
	// 2 DATE 04 AUG 2004
	// 3 TIME 13:39:58
	if ($family->fam_new_date){
		$buffer.="1 _NEW\n";
		$buffer.="2 DATE ".$family->fam_new_date."\n";
		if ($family->fam_new_time){
			$buffer.="3 TIME ".$family->fam_new_time."\n";
		}
	}

	// *** Date and time changed in database ***
	// 1_CHAN
	// 2 DATE 04 AUG 2004
	// 3 TIME 13:39:58
	if ($family->fam_changed_date){
		$buffer.="1 CHAN\n";
		$buffer.="2 DATE ".$family->fam_changed_date."\n";
		if ($family->fam_changed_time){
			$buffer.="3 TIME ".$family->fam_changed_time."\n";
		}
	}

	// *** Write family data ***
	$buffer=decode($buffer);
	fwrite($fh, $buffer);
	// *** Show family data on screen ***
	//$buffer = str_replace("\n", "<br>", $buffer);
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
	//$qry = mysql_query("SELECT connect_connect_id, connect_source_id FROM ".$tree."connections
	//						WHERE connect_sub_kind LIKE 'pers_%' OR connect_sub_kind LIKE 'fam_%'",$db);
	//$qry = mysql_query("SELECT connect_connect_id, connect_source_id FROM ".$tree."connections
	//						WHERE connect_source_id != ''",$db);
	//while($qryDb=mysql_fetch_object($qry)){
	$qry = $dbh->query("SELECT connect_connect_id, connect_source_id FROM ".$tree."connections
							WHERE connect_source_id != ''");
	while($qryDb=$qry->fetch(PDO::FETCH_OBJ)){	
		if(in_array($qryDb->connect_connect_id,$persids) OR in_array($qryDb->connect_connect_id,$famsids)) {
			$source_array[]=$qryDb->connect_source_id;
		}
	}
	// find all sources referred to by addresses (233)
	// extended addresses: we need a three-fold procedure....
	// First: in the connections table search for exported persons/families that have an RESI number connection (R34)
	//$address_connect_qry = mysql_query("SELECT connect_connect_id, connect_item_id FROM ".$tree."connections WHERE connect_sub_kind LIKE '%_address'",$db) or die("momo");
	$address_connect_qry = $dbh->query("SELECT connect_connect_id, connect_item_id FROM ".$tree."connections WHERE connect_sub_kind LIKE '%_address'");
	$resi_array = array();
	//while($address_connect_qryDb=mysql_fetch_object($address_connect_qry)){
	while($address_connect_qryDb=$address_connect_qry->fetch(PDO::FETCH_OBJ)){
		if(in_array($address_connect_qryDb->connect_connect_id,$persids) or in_array($address_connect_qryDb->connect_connect_id,$famsids)) {
			$resi_array[] = $address_connect_qryDb->connect_item_id;
		}
	}
	// Second: in the address table search for the previously found R numbers and get their id number (33)
	//$address_address_qry = mysql_query("SELECT address_gedcomnr, address_id FROM ".$tree."addresses WHERE address_gedcomnr !='' ",$db);
	$address_address_qry = $dbh->query("SELECT address_gedcomnr, address_id FROM ".$tree."addresses WHERE address_gedcomnr !='' ");
	$resi_id_array = array();
	//while($address_address_qryDb=mysql_fetch_object($address_address_qry)){
	while($address_address_qryDb=$address_address_qry->fetch(PDO::FETCH_OBJ)){
		if(in_array($address_address_qryDb->address_gedcomnr,$resi_array)) {
			$resi_id_array[] = $address_address_qryDb->address_id;
		}
	}
	// Third: back in the connections table, find the previously found address id numbers and get the associated source ged number ($23)
	//$address_connect2_qry = mysql_query("SELECT connect_connect_id, connect_source_id FROM ".$tree."connections WHERE connect_sub_kind = 'address_source'",$db);
	$address_connect2_qry = $dbh->query("SELECT connect_connect_id, connect_source_id FROM ".$tree."connections WHERE connect_sub_kind = 'address_source'");
	//while($address_connect2_qry_qryDb=mysql_fetch_object($address_connect2_qry)){
	while($address_connect2_qry_qryDb=$address_connect2_qry->fetch(PDO::FETCH_OBJ)){
		if(in_array($address_connect2_qry_qryDb->connect_connect_id,$resi_id_array)) {
			$source_array[] = $address_connect2_qry_qryDb->connect_source_id;
		}
	}
	// "direct" addresses
	//$addressqry = mysql_query("SELECT address_id, address_person_id, address_family_id FROM ".$tree."addresses",$db);
	$addressqry = $dbh->query("SELECT address_id, address_person_id, address_family_id FROM ".$tree."addresses");
	$source_address_array=array();
	//while($addressqryDb=mysql_fetch_object($addressqry)){
	while($addressqryDb=$addressqry->fetch(PDO::FETCH_OBJ)){
		if($addressqryDb->address_person_id!='' AND in_array($addressqryDb->address_person_id,$persids)) {
			$source_address_array[] = $addressqryDb->address_id;
		}
		if($addressqryDb->address_family_id!='' AND in_array($addressqryDb->address_family_id,$famsids)) {
			$source_address_array[] = $addressqryDb->address_id;
		}
	}
	//$addresssourceqry = mysql_query("SELECT connect_source_id, connect_connect_id FROM ".$tree."connections WHERE connect_sub_kind LIKE 'address_%'",$db);
	$addresssourceqry = $dbh->query("SELECT connect_source_id, connect_connect_id FROM ".$tree."connections WHERE connect_sub_kind LIKE 'address_%'");
	//while($addresssourceqryDb=mysql_fetch_object($addresssourceqry)){
	while($addresssourceqryDb=$addresssourceqry->fetch(PDO::FETCH_OBJ)){
		if(in_array($addresssourceqryDb->connect_connect_id,$source_address_array)) {
			$source_array[] = $addresssourceqryDb->connect_source_id;
		}
	}

	// find all sources referred to by events (233)
	//$eventqry = mysql_query("SELECT event_id, event_person_id, event_family_id FROM ".$tree."events",$db);
	$eventqry = $dbh->query("SELECT event_id, event_person_id, event_family_id FROM ".$tree."events");
	$source_event_array = array();
	//while($eventqryDb=mysql_fetch_object($eventqry)){
	while($eventqryDb=$eventqry->fetch(PDO::FETCH_OBJ)){
		if($eventqryDb->event_person_id!='' AND in_array($eventqryDb->event_person_id,$persids)) {
			$source_event_array[] = $eventqryDb->event_id;
		}
		if($eventqryDb->event_family_id!='' AND in_array($eventqryDb->event_family_id,$famsids)) {
			$source_event_array[] = $eventqryDb->event_id;
		}
	}
	//$eventsourceqry = mysql_query("SELECT connect_source_id, connect_connect_id FROM ".$tree."connections WHERE connect_sub_kind LIKE 'event_%'",$db);
	$eventsourceqry = $dbh->query("SELECT connect_source_id, connect_connect_id FROM ".$tree."connections WHERE connect_sub_kind LIKE 'event_%'");
	//while($eventsourceqryDb=mysql_fetch_object($eventsourceqry)){
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
	//$family_qry=mysql_query("SELECT * FROM ".$tree."sources",$db);
	//while($family=mysql_fetch_object($family_qry)){
	$family_qry=$dbh->query("SELECT * FROM ".$tree."sources");
	while($family=$family_qry->fetch(PDO::FETCH_OBJ)){	

		if($_POST['part_tree']=='part'  AND !in_array($family->source_gedcomnr,$source_array)) { continue; }

		// 0 @I1181@ INDI *** Gedcomnumber ***
		$buffer='0 @'.$family->source_gedcomnr."@ SOUR\n";

		if (isset($_POST['gedcom_status']) AND $_POST['gedcom_status']=='yes'){
			echo $family->source_gedcomnr. ' ';
		}

		if ($family->source_title){ $buffer.='1 TITLE '.$family->source_title."\n"; }
		if ($family->source_abbr){ $buffer.='1 ABBR '.$family->source_abbr."\n"; }
		if ($family->source_date){ $buffer.='1 DATE '.$family->source_date."\n"; }
		if ($family->source_place){ $buffer.='1 PLAC '.$family->source_place."\n"; }
		if ($family->source_publ){ $buffer.='1 PUBL '.$family->source_publ."\n"; }
		if ($family->source_refn){ $buffer.='1 REFN '.$family->source_refn."\n"; }
		if ($family->source_auth){ $buffer.='1 AUTH '.$family->source_auth."\n"; }
		if ($family->source_subj){ $buffer.='1 SUBJ '.$family->source_subj."\n"; }
		if ($family->source_item){ $buffer.='1 ITEM '.$family->source_item."\n"; }
		if ($family->source_kind){ $buffer.='1 KIND '.$family->source_kind."\n"; }
		if ($family->source_text){ $buffer.='1 NOTE '.process_text(2,$family->source_text); }
		if (isset($family->source_status) AND $family->source_status=='restricted'){ $buffer.='1 RESN privacy'."\n"; }

		// photo = old gedcom tag?
		if ($family->source_photo){ $buffer.='1 PHOT '.$family->source_photo."\n"; }

		// source_repo_name, source_repo_caln, source_repo_page.

		// *** Write source data ***
		$buffer=decode($buffer);
		fwrite($fh, $buffer);
		// *** Show source data on screen ***
		//$buffer = str_replace("\n", "<br>", $buffer);
		//echo $buffer;
	}
}

// *** Addresses ***
// 0 @R155@ RESI
// 1 ADDR Straat
// 1 PLAC Plaats
/*
// *** ADDRESS IS ADDED BY PERSON! ***
$family_qry=mysql_query("SELECT * FROM ".$tree."addresses
	WHERE address_gedcomnr LIKE '_%'",$db);
while($family=mysql_fetch_object($family_qry)){
	// 0 @I1181@ INDI *** Gedcomnumber ***
	$buffer='0 @'.$family->address_gedcomnr."@ RESI\n";

	if ($family->address_address){ $buffer.='1 ADDR '.$family->address_address."\n"; }
	if ($family->address_zip){ $buffer.='1 ZIP '.$family->address_zip."\n"; }
	if ($family->address_date){ $buffer.='1 DATE '.$family->address_date."\n"; }
	if ($family->address_place){ $buffer.='1 PLAC '.$family->address_place."\n"; }
	if ($family->address_phone){ $buffer.='1 PHON '.$family->address_phone."\n"; }
	if ($gedcom_sources=='yes' AND $family->address_source){
//SOURCE
		$buffer.='1 SOUR '.$family->address_source."\n"; }
	if ($family->address_text){ $buffer.='1 NOTE '.process_text(2,$family->address_text); }

// photo

	// *** Write source data ***
	$buffer=decode($buffer);
	fwrite($fh, $buffer);
	// *** Show source data on screen ***
	//$buffer = str_replace("\n", "<br>", $buffer);
	//echo $buffer;
}
*/


// *** Notes ***
// 0 @N1@ NOTE
// 1 CONT Start of the note
// 2 CONC add a bit more to the line
// 2 CONT Another line of the note

// This adds seperate note records for all the note refs in table texts captured in $noteids
if ($gedcom_texts=='yes'){
	natsort($noteids);
	foreach ($noteids as $s){
		$text_query = "SELECT * FROM ".$tree."texts WHERE text_gedcomnr='" . $s . "'";
		//$text_sql=mysql_query($text_query,$db);
		$text_sql=$dbh->query($text_query);
		//while($textDb=mysql_fetch_object($text_sql)){
		while($textDb=$text_sql->fetch(PDO::FETCH_OBJ)){
			$linecount=0;
			$textlines = process_text(1, $textDb->text_text, false);
			$textarray = explode("\n", $textlines);
			foreach ($textarray as $line) {
				if (strlen($line) > 0){
					if ($linecount==0) {
						//fwrite($fh, "0 " . $s . " NOTE " . $line . "\n");
						fwrite($fh, "0 " . $s . " NOTE\n");
						fwrite($fh, "1 CONC " . $line . "\n");
						if (isset($_POST['gedcom_status']) AND $_POST['gedcom_status']=='yes'){
							echo $s . ' '; 
						}
					} else {
						fwrite($fh, $line . "\n");
					}
					$linecount++;	
				}
			}
		}
	}
}

fwrite($fh, '0 TRLR');

fclose($fh);

echo '<p>'.__('Gedcom file is generated').'<br>';

//echo '<p><a href="'.$myFile.'" target="_blank">Download gedcom file</a>';

//echo '<form method="POST" action="'.$myFile.'" target="_blank">';
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