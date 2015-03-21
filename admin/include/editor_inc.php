<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

$gedcom_date=strtoupper(date("d M Y"));
$gedcom_time=date("H:i:s");

// *** Return deletion confim box in $confirm variabele ***
$confirm='';
$confirm_relation='';

$pers_favorite='';
if (isset($_GET['pers_favorite'])){
	//if ($_GET['pers_favorite']=="1"){ $pers_favorite='1'; } else{ $pers_favorite=''; }
	//$sql="UPDATE ".$tree_prefix."person SET pers_favorite='".$pers_favorite."'
	//	WHERE pers_gedcomnumber='".safe_text($pers_gedcomnumber)."'";
	//$result=$dbh->query($sql);

	if ($_GET['pers_favorite']=="1"){
		$sql = "INSERT INTO humo_settings SET
			setting_variable='admin_favourite',
			setting_value='".safe_text($pers_gedcomnumber)."',
			setting_tree_id='".safe_text($tree_id)."'";
		$result = $dbh->query($sql);
	}
	else{
		$sql = "DELETE FROM humo_settings
			WHERE setting_variable='admin_favourite'
			AND setting_value='".safe_text($pers_gedcomnumber)."'
			AND setting_tree_id='".safe_text($tree_id)."'";
		$result = $dbh->query($sql);
	}
}


// ***************************
// *** PROCESS DATA PERSON ***
// ***************************


if (isset($_POST['person_remove'])){
	$new_nr_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$pers_gedcomnumber."'";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);

	$confirm.='<div class="confirm">';
	$confirm.=__('This will disconnect this person from parents, spouses and children <b>and delete it completely from the database.</b> Do you wish to continue?');

	// GRAYED-OUT and DISABLED!!!! UNDER CONSTRUCTION!!!!
	$confirm.='<br>';
	//$disabled='';
	$disabled=' DISABLED';
	$confirm.='<span style="color:#6D7B8D;">';
		$selected=''; //if ($selected_alive=='alive'){ $selected=' CHECKED'; }
		$confirm.=' <input type="checkbox" name="pers_alive" value="alive"'.$selected.$disabled.'> '.__('Also remove ALL RELATED PERSONS (including all items)').'<br>';
	$confirm.='</span>';

	$confirm.=' <form method="post" action="'.$phpself.'" style="display : inline;">';
	$confirm.='<input type="hidden" name="page" value="'.$page.'">';
	$confirm.=' <input type="Submit" name="person_remove2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	$confirm.=' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	$confirm.='</form>';
	$confirm.='</div>';
}
if (isset($_POST['person_remove2'])){
	$confirm.='<div class="confirm">';

	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$pers_gedcomnumber."'";
	$person_result = $dbh->query($person_qry);
	$personDb=$person_result->fetch(PDO::FETCH_OBJ);

	// *** If person is married: remove marriages from family ***
	if ($personDb->pers_fams){
		$fams_array=explode(";",$personDb->pers_fams);
		foreach ($fams_array as $key => $value) {
			$fam_qry= "SELECT * FROM humo_families
				WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$fams_array[$key]."'";
			$fam_result = $dbh->query($fam_qry);
			$famDb=$fam_result->fetch(PDO::FETCH_OBJ);

			if ($famDb->fam_man==$pers_gedcomnumber){
				// *** Completely remove marriage if man and woman are removed *** 
				if ($famDb->fam_woman=='' OR $famDb->fam_woman=='0'){

					// *** Remove parents by children ***
					$fam_children=explode(";",$famDb->fam_children);
					foreach ($fam_children as $key2 => $value) {
						$sql="UPDATE humo_persons SET pers_famc=''
							WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$fam_children[$key2]."'";
						$result=$dbh->query($sql);
					}

					$sql="DELETE FROM humo_families
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$famDb->fam_gedcomnumber."'";
					$result=$dbh->query($sql);

					// *** Remove indexnr from children without own family ***
					$sql="UPDATE humo_persons SET pers_indexnr=''
						WHERE pers_tree_id='".$tree_id."' AND pers_indexnr='".$famDb->fam_gedcomnumber."'";
					$result=$dbh->query($sql);
				}
				else{
					$sql="UPDATE humo_families SET fam_man='0'
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$famDb->fam_gedcomnumber."'";
					$result=$dbh->query($sql);
					$confirm.=__('Person disconnected from marriage(s).').'<br>';
				}
			}

			if ($famDb->fam_woman==$pers_gedcomnumber){
				// *** Completely remove marriage if man and woman are removed *** 
				if ($famDb->fam_man=='' OR $famDb->fam_man=='0'){

					// *** Remove parents by children ***
					$fam_children=explode(";",$famDb->fam_children);
					foreach ($fam_children as $key2 => $value) {
						$sql="UPDATE humo_persons SET pers_famc=''
							WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$fam_children[$key2]."'";
						$result=$dbh->query($sql);
					}

					$sql="DELETE FROM humo_families
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$famDb->fam_gedcomnumber."'";
						$result=$dbh->query($sql);

					// *** Remove indexnr from children without own family ***
					$sql="UPDATE humo_persons SET pers_indexnr=''
						WHERE pers_tree_id='".$tree_id."' AND pers_indexnr='".$famDb->fam_gedcomnumber."'";
					$result=$dbh->query($sql);
				}
				else{
					$sql="UPDATE humo_families SET fam_woman='0'
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$famDb->fam_gedcomnumber."'";
					$result=$dbh->query($sql);
					$confirm.=__('Person disconnected from marriage(s).').'<br>';
				}
			}
		}
	}

	// *** If person is a child: remove child number from parents family ***
	//if (!$personDb->pers_fams AND $personDb->pers_famc){
	if ($personDb->pers_famc){
		$fam_qry= "SELECT * FROM humo_families
			WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$personDb->pers_famc."'";
		$fam_result = $dbh->query($fam_qry);
		$famDb=$fam_result->fetch(PDO::FETCH_OBJ);

		$fam_children=explode(";",$famDb->fam_children);
		foreach ($fam_children as $key => $value) {
			if ($fam_children[$key] != $pers_gedcomnumber){ $fam_children2[]=$fam_children[$key]; }
		}
		$fam_children3='';
		if (isset($fam_children2[0])){ $fam_children3 = implode(";", $fam_children2); }

		$sql="UPDATE humo_families SET
			fam_children='".$fam_children3."'
			WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$personDb->pers_famc."'";
		$result=$dbh->query($sql);

		$confirm.=__('Person disconnected from parents.').'<br>';
	}

	$sql="DELETE FROM humo_events WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'";
	$result=$dbh->query($sql);

	$sql="DELETE FROM humo_addresses WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."'";
	$result=$dbh->query($sql);

	$sql="DELETE FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$pers_gedcomnumber."'";
	$result=$dbh->query($sql);

	$confirm.=__('Person is removed');

	// *** Select new person ***
	$new_nr_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_favorite LIKE '%_' ORDER BY pers_lastname, pers_firstname LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
	if (isset($new_nr->pers_gedcomnumber)){
		$pers_gedcomnumber=$new_nr->pers_gedcomnumber;
		$_SESSION['admin_pers_gedcomnumber']=$pers_gedcomnumber;
	}
	else{
		$new_nr_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' LIMIT 0,1";
		$new_nr_result = $dbh->query($new_nr_qry);
		$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
		if ($new_nr->pers_gedcomnumber){
			$pers_gedcomnumber=$new_nr->pers_gedcomnumber;
			$_SESSION['admin_pers_gedcomnumber']=$pers_gedcomnumber;
		}
	}

	family_tree_update($tree_prefix);

	$confirm.='</div>';
}

if (isset($_POST['person_change'])){
	// *** If person is deceased, set alive setting ***
	@$pers_alive=safe_text($_POST["pers_alive"]);
	if ($_POST["pers_death_date"] OR $_POST["pers_death_place"] OR $_POST["pers_buried_date"] OR $_POST["pers_buried_place"]){
		$pers_alive='deceased';
	}

	$sql="UPDATE humo_persons SET
	pers_firstname='".$editor_cls->text_process($_POST["pers_firstname"])."',
	pers_callname='".$editor_cls->text_process($_POST["pers_callname"])."',
	pers_prefix='".$editor_cls->text_process($_POST["pers_prefix"])."',
	pers_lastname='".$editor_cls->text_process($_POST["pers_lastname"])."',
	pers_patronym='".$editor_cls->text_process($_POST["pers_patronym"])."',
	pers_name_text='".$editor_cls->text_process($_POST["pers_name_text"],true)."',
	pers_alive='".$pers_alive."',
	pers_sexe='".safe_text($_POST["pers_sexe"])."',
	pers_own_code='".safe_text($_POST["pers_own_code"])."',
	pers_text='".$editor_cls->text_process($_POST["person_text"],true)."',
	pers_changed_date='".$gedcom_date."',
	pers_changed_time='".$gedcom_time."'
	WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($pers_gedcomnumber)."'";
	$result=$dbh->query($sql);
	//pers_favorite='".$pers_favorite."',
	//pers_quality='".$_POST["pers_quality"]."',

	$pers_stillborn=''; if (isset($_POST["pers_stillborn"])) $pers_stillborn='y';

	$pers_death_cause=$_POST["pers_death_cause"];
	if (isset($_POST["pers_death_cause2"]) AND $_POST["pers_death_cause2"]) $pers_death_cause=$_POST["pers_death_cause2"];

	$sql="UPDATE humo_persons SET
	pers_birth_date='".$editor_cls->date_process("pers_birth_date")."',
	pers_birth_place='".$editor_cls->text_process($_POST["pers_birth_place"])."',
	pers_birth_time='".$editor_cls->text_process($_POST["pers_birth_time"])."',
	pers_birth_text='".$editor_cls->text_process($_POST["pers_birth_text"],true)."',
	pers_stillborn='".$pers_stillborn."',
	pers_bapt_date='".$editor_cls->date_process("pers_bapt_date")."',
	pers_bapt_place='".$editor_cls->text_process($_POST["pers_bapt_place"])."',
	pers_bapt_text='".$editor_cls->text_process($_POST["pers_bapt_text"],true)."',
	pers_religion='".safe_text($_POST["pers_religion"])."',
	pers_death_date='".$editor_cls->date_process("pers_death_date")."',
	pers_death_place='".$editor_cls->text_process($_POST["pers_death_place"])."',
	pers_death_time='".$editor_cls->text_process($_POST["pers_death_time"])."',
	pers_death_text='".$editor_cls->text_process($_POST["pers_death_text"],true)."',
	pers_death_cause='".safe_text($pers_death_cause)."',
	pers_buried_date='".$editor_cls->date_process("pers_buried_date")."',
	pers_buried_place='".$editor_cls->text_process($_POST["pers_buried_place"])."',
	pers_buried_text='".$editor_cls->text_process($_POST["pers_buried_text"],true)."',
	pers_cremation='".safe_text($_POST["pers_cremation"])."',
	pers_changed_date='".$gedcom_date."',
	pers_changed_time='".$gedcom_time."'
	WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($pers_gedcomnumber)."'";
	$result=$dbh->query($sql);
	family_tree_update($tree_prefix);
}

if (isset($_POST['person_add'])){
	// *** Generate new gedcomnr, find highest gedcomnumber I100: strip I and order by numeric ***
	$new_nr_qry= "SELECT *, ABS(substring(pers_gedcomnumber, 2)) AS gednr
		FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY gednr DESC LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
	$new_gedcomnumber='I1';
	if (isset($new_nr->pers_gedcomnumber)){
		$new_gedcomnumber='I'.(substr($new_nr->pers_gedcomnumber,1)+1);
	}

	// *** If person is deceased, set alive setting ***
	@$pers_alive=safe_text($_POST["pers_alive"]);
	if ($_POST["pers_death_date"] OR $_POST["pers_death_place"] OR $_POST["pers_buried_date"] OR $_POST["pers_buried_place"]){
		$pers_alive='deceased';
	}

	$pers_stillborn=''; if (isset($_POST["pers_stillborn"])){ $pers_stillborn='y'; }
	$sql="INSERT INTO humo_persons SET
		pers_tree_id='".$tree_id."',
		pers_tree_prefix='".$tree_prefix."',
		pers_famc='',
		pers_fams='',
		pers_indexnr='',
		pers_gedcomnumber='".$new_gedcomnumber."',
		pers_firstname='".$editor_cls->text_process($_POST["pers_firstname"])."',
		pers_callname='".$editor_cls->text_process($_POST["pers_callname"])."',
		pers_prefix='".$editor_cls->text_process($_POST["pers_prefix"])."',
		pers_lastname='".$editor_cls->text_process($_POST["pers_lastname"])."',
		pers_patronym='".$editor_cls->text_process($_POST["pers_patronym"])."',
		pers_name_text='".$editor_cls->text_process($_POST["pers_name_text"])."',
		pers_alive='".$pers_alive."',
		pers_sexe='".safe_text($_POST["pers_sexe"])."',
		pers_own_code='".safe_text($_POST["pers_own_code"])."',
		pers_place_index='',
		pers_text='".$editor_cls->text_process($_POST["person_text"])."',

		pers_birth_date='".$editor_cls->date_process("pers_birth_date")."',
		pers_birth_place='".$editor_cls->text_process($_POST["pers_birth_place"])."',
		pers_birth_time='".$editor_cls->text_process($_POST["pers_birth_time"])."',
		pers_birth_text='".$editor_cls->text_process($_POST["pers_birth_text"],true)."',
		pers_stillborn='".$pers_stillborn."',
		pers_bapt_date='".$editor_cls->date_process("pers_bapt_date")."',
		pers_bapt_place='".$editor_cls->text_process($_POST["pers_bapt_place"])."',
		pers_bapt_text='".$editor_cls->text_process($_POST["pers_bapt_text"],true)."',
		pers_religion='".safe_text($_POST["pers_religion"])."',
		pers_death_date='".$editor_cls->date_process("pers_death_date")."',
		pers_death_place='".$editor_cls->text_process($_POST["pers_death_place"])."',
		pers_death_time='".$editor_cls->text_process($_POST["pers_death_time"])."',
		pers_death_text='".$editor_cls->text_process($_POST["pers_death_text"],true)."',
		pers_death_cause='".safe_text($_POST["pers_death_cause"])."',
		pers_buried_date='".$editor_cls->date_process("pers_buried_date")."',
		pers_buried_place='".$editor_cls->text_process($_POST["pers_buried_place"])."',
		pers_buried_text='".$editor_cls->text_process($_POST["pers_buried_text"],true)."',
		pers_cremation='".safe_text($_POST["pers_cremation"])."',

		pers_new_date='".$gedcom_date."',
		pers_new_time='".$gedcom_time."'";
	$result=$dbh->query($sql);
	//pers_favorite='".$pers_favorite."',

	// *** Show new person ***
	$pers_gedcomnumber=$new_gedcomnumber;
	$_SESSION['admin_pers_gedcomnumber']=$pers_gedcomnumber;

	family_tree_update($tree_prefix);

	// *** Add child to family, add a new child (new gedcomnumber) ***
	if (isset($_POST['child_connect'])){
		$_POST['child_connect2']=$new_gedcomnumber;
	}
}

// *** Family move down ***
if (isset($_GET['fam_down'])){
	$child_array_org=explode(";",safe_text($_GET['fam_array']));
	$child_array=$child_array_org;
	$child_array_id=safe_text($_GET['fam_down']);
	$child_array[$child_array_id]=$child_array_org[($child_array_id+1)];
	$child_array[$child_array_id+1]=$child_array_org[($child_array_id)];
	$fams='';
	for ($k=0; $k<count($child_array); $k++){
		if ($k>0){ $fams.=';'; }
		$fams.=$child_array[$k];
	}
	$sql="UPDATE humo_persons SET
	pers_fams='".$fams."',
	pers_changed_date='".$gedcom_date."',
	pers_changed_time='".$gedcom_time."'
	WHERE pers_id='".safe_text($_GET["person_id"])."'";
	$result=$dbh->query($sql);
}

// *** Family move up ***
if (isset($_GET['fam_up'])){
	$child_array_org=explode(";",safe_text($_GET['fam_array']));
	$child_array=$child_array_org;
	$child_array_id=safe_text($_GET['fam_up'])-1;
	$child_array[$child_array_id+1]=$child_array_org[($child_array_id)];
	$child_array[$child_array_id]=$child_array_org[($child_array_id+1)];
	$fams='';
	for ($k=0; $k<count($child_array); $k++){
		if ($k>0){ $fams.=';'; }
		$fams.=$child_array[$k];
	}
	$sql="UPDATE humo_persons SET
	pers_fams='".$fams."',
	pers_changed_date='".$gedcom_date."',
	pers_changed_time='".$gedcom_time."'
	WHERE pers_id='".safe_text($_GET["person_id"])."'";
	$result=$dbh->query($sql);
}

// *** Some functions to add and remove a fams number from a person (if marriage is changed) ***
function fams_add($personnr, $familynr){
	global $dbh, $tree_id, $tree_prefix, $gedcom_date, $gedcom_time;
	// *** Add marriage to person records ***
	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($personnr)."'";
	$person_result = $dbh->query($person_qry);
	$person_db=$person_result->fetch(PDO::FETCH_OBJ);
	if (@$person_db->pers_gedcomnumber){
		$fams=$person_db->pers_fams;
		if ($fams){
			$fams1=explode(";",$fams); $pers_indexnr=$fams1[0];
			$fams.=';'.$familynr;
		}
		else{
			$pers_indexnr=$familynr;
			$fams=$familynr;
		}
		$sql="UPDATE humo_persons SET
			pers_fams='".$fams."',
			pers_indexnr='".$pers_indexnr."',
			pers_changed_date='".$gedcom_date."',
			pers_changed_time='".$gedcom_time."'
			WHERE pers_id='".$person_db->pers_id."'";
		$result=$dbh->query($sql);
	}
}

function fams_remove($personnr, $familynr){
	global $dbh, $tree_id, $tree_prefix, $gedcom_date, $gedcom_time;
	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$personnr."'";
	$person_result = $dbh->query($person_qry);
	$person_db=$person_result->fetch(PDO::FETCH_OBJ);
	if (@$person_db->pers_gedcomnumber){
		$fams=explode(";",$person_db->pers_fams);
		foreach ($fams as $key => $value) {
			if ($fams[$key] != $familynr){ $fams2[]=$fams[$key]; }
		}
		$fams3='';
		$pers_indexnr=''; if ($person_db->pers_famc){ $pers_indexnr=$person_db->pers_famc; }
		if (isset($fams2[0])){
			$fams3 = implode(";", $fams2);
			$pers_indexnr=$fams2[0];
		}
		$sql="UPDATE humo_persons SET
			pers_fams='".$fams3."',
			pers_indexnr='".$pers_indexnr."',
			pers_changed_date='".$gedcom_date."',
			pers_changed_time='".$gedcom_time."'
			WHERE pers_id='".$person_db->pers_id."'";
		$result=$dbh->query($sql);
	}
}

// *** Family disconnect ***
if (isset($_GET['fam_remove']) OR isset($_POST['fam_remove']) ){
	if (isset($_GET['fam_remove'])){ $fam_remove=safe_text($_GET['fam_remove']); };
	if (isset($_POST['marriage_nr'])){ $fam_remove=safe_text($_POST['marriage_nr']); };

	$new_nr_qry= "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$fam_remove."'";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
	$confirm_relation.='<div class="confirm">';
		if ($new_nr->fam_children) $confirm_relation.=__('If you continue, ALL children will be disconnected automatically!').'<br>';
		$confirm_relation.=__('Are you sure to remove this mariage?');
	$confirm_relation.=' <form method="post" action="'.$phpself.'#marriage" style="display : inline;">';
	$confirm_relation.='<input type="hidden" name="page" value="'.$page.'">';
	//$confirm_relation.='<input type="hidden" name="fam_remove" value="'.safe_text($_GET['fam_remove']).'">';
	$confirm_relation.='<input type="hidden" name="fam_remove3" value="'.$fam_remove.'">';
	$confirm_relation.=' <input type="Submit" name="fam_remove2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	$confirm_relation.=' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	$confirm_relation.='</form>';
	$confirm_relation.='</div>';
}
if (isset($_POST['fam_remove2'])){
	$fam_remove=safe_text($_POST['fam_remove3']);

	// *** Remove fams number from man and woman ***
	$new_nr_qry= "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$fam_remove."'";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);

	// *** Disconnect ALL children from marriage ***
	if ($new_nr->fam_children){
		$child_gedcomnumber=explode(";",$new_nr->fam_children);
		for($i=0; $i<=substr_count($new_nr->fam_children, ";"); $i++){
			// *** Find child data ***
			$sql= "SELECT * FROM humo_persons
				WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($child_gedcomnumber[$i])."'";
			$result = $dbh->query($sql);
			$resultDb=$result->fetch(PDO::FETCH_OBJ);
			$pers_indexnr=$resultDb->pers_indexnr;
			if ($pers_indexnr==$fam_remove){ $pers_indexnr=''; }

			// *** Remove parents from child record ***
			$sql="UPDATE humo_persons SET
			pers_famc='',
			pers_indexnr='".safe_text($pers_indexnr)."',
			pers_changed_date='".$gedcom_date."',
			pers_changed_time='".$gedcom_time."'
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($child_gedcomnumber[$i])."'";
			$result=$dbh->query($sql);
		}
	}

	if (isset($new_nr->fam_man)){ fams_remove($new_nr->fam_man, $fam_remove); }

	unset ($fams2);
	if (isset($new_nr->fam_woman)){ fams_remove($new_nr->fam_woman, $fam_remove); }

	//$sql="DELETE FROM ".$tree_prefix."events WHERE event_family_id='".$fam_remove."'";
	$sql="DELETE FROM humo_events WHERE event_tree_id='".$tree_id."' AND event_family_id='".$fam_remove."'";
	$result=$dbh->query($sql);

	//$sql="DELETE FROM ".$tree_prefix."addresses WHERE address_family_id='".$fam_remove."'";
	$sql="DELETE FROM humo_addresses WHERE address_tree_id='".$tree_id."' AND address_family_id='".$fam_remove."'";
	$result=$dbh->query($sql);

	$sql="DELETE FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$fam_remove."'";
	$result=$dbh->query($sql);

	family_tree_update($tree_prefix);

	$confirm_relation.='<div class="confirm">';
	$confirm_relation.=__('Marriage is removed!');
	$confirm_relation.='</div>';

	// *** If this relation is removed, show 1st relation of person, or link to new relation ***
	$marriage='';
	if (isset($person->pers_fams) AND $person->pers_fams){
		$fams1=explode(";",$person->pers_fams);
		$marriage=$fams1[0];
	}
	$_POST["marriage_nr"]=$marriage;
	$_SESSION['admin_fam_gedcomnumber']=$marriage;
}

// *** Add NEW N.N. parents to a child ***
if (isset($_GET['add_parents'])){
	// *** Generate new gedcomnr, find highest gedcomnumber F100: strip F and order by numeric ***
	$new_nr_qry= "SELECT *, ABS(substring(fam_gedcomnumber, 2)) AS gednr
		FROM humo_families WHERE fam_tree_id='".$tree_id."' ORDER BY gednr DESC LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
	//$new_gedcomnumber='F1';
	$fam_gedcomnumber='F1';
	if (isset($new_nr->fam_gedcomnumber)) $fam_gedcomnumber='F'.(substr($new_nr->fam_gedcomnumber,1)+1);

	// *** Generate new gedcomnr, find highest gedcomnumber I100: strip I and order by numeric ***
	$new_nr_qry= "SELECT *, ABS(substring(pers_gedcomnumber, 2)) AS gednr
		FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY gednr DESC LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
	//$new_gedcomnumber='I1';
	$man_gedcomnumber='I1';
	$woman_gedcomnumber='I2';
	if (isset($new_nr->pers_gedcomnumber)){
		$man_gedcomnumber='I'.(substr($new_nr->pers_gedcomnumber,1)+1);
		$woman_gedcomnumber='I'.(substr($new_nr->pers_gedcomnumber,1)+2);
	}

	$sql="INSERT INTO humo_families SET
	fam_gedcomnumber='".$fam_gedcomnumber."',
	fam_tree_id='".$tree_id."',
	fam_kind='',
	fam_man='".safe_text($man_gedcomnumber)."',
	fam_woman='".safe_text($woman_gedcomnumber)."',
	fam_children='".safe_text($pers_gedcomnumber)."';
	fam_relation_date='', fam_relation_place='', fam_relation_text='',
	fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
	fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
	fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
	fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
	fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
	fam_text='',
	fam_new_date='".$gedcom_date."',
	fam_new_time='".$gedcom_time."'";
	//echo $sql.'<br>';
	$result=$dbh->query($sql);

	// *** Add N.N. father ***
	$sql="INSERT INTO humo_persons SET
		pers_gedcomnumber='".$man_gedcomnumber."',
		pers_tree_id='".$tree_id."',
		pers_tree_prefix='".$tree_prefix."',
		pers_famc='', pers_fams='".safe_text($fam_gedcomnumber)."', pers_indexnr='".safe_text($fam_gedcomnumber)."',
		pers_firstname='".__('N.N.')."', pers_callname='', pers_prefix='', pers_lastname='', pers_patronym='', pers_name_text='',
		pers_alive='alive', pers_sexe='M', pers_own_code='', pers_place_index='', pers_text='',
		pers_birth_date='', pers_birth_place='', pers_birth_time='', pers_birth_text='', pers_stillborn='',
		pers_bapt_date='', pers_bapt_place='', pers_bapt_text='', pers_religion='',
		pers_death_date='', pers_death_place='', pers_death_time='', pers_death_text='', pers_death_cause='',
		pers_buried_date='', pers_buried_place='', pers_buried_text='', pers_cremation='',
		pers_new_date='".$gedcom_date."', pers_new_time='".$gedcom_time."'";
	//echo $sql.'<br>';
	$result=$dbh->query($sql);

	// *** Add N.N. mother ***
	$sql="INSERT INTO humo_persons SET
		pers_gedcomnumber='".$woman_gedcomnumber."',
		pers_tree_id='".$tree_id."',
		pers_tree_prefix='".$tree_prefix."',
		pers_famc='', pers_fams='".safe_text($fam_gedcomnumber)."', pers_indexnr='".safe_text($fam_gedcomnumber)."',
		pers_firstname='".__('N.N.')."', pers_callname='', pers_prefix='', pers_lastname='', pers_patronym='', pers_name_text='',
		pers_alive='alive', pers_sexe='F', pers_own_code='', pers_place_index='', pers_text='',
		pers_birth_date='', pers_birth_place='', pers_birth_time='', pers_birth_text='', pers_stillborn='',
		pers_bapt_date='', pers_bapt_place='', pers_bapt_text='', pers_religion='',
		pers_death_date='', pers_death_place='', pers_death_time='', pers_death_text='', pers_death_cause='',
		pers_buried_date='', pers_buried_place='', pers_buried_text='', pers_cremation='',
		pers_new_date='".$gedcom_date."', pers_new_time='".$gedcom_time."'";
	//echo $sql.'<br>';
	$result=$dbh->query($sql);

	// *** Add parents to child record ***
	$sql="UPDATE humo_persons SET
	pers_famc='".safe_text($fam_gedcomnumber)."',
	pers_changed_date='".$gedcom_date."',
	pers_changed_time='".$gedcom_time."'
	WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($pers_gedcomnumber)."'";
	//echo $sql.'<br>';
	$result=$dbh->query($sql);
	//	pers_indexnr='".safe_text($pers_indexnr)."',

	family_tree_update($tree_prefix);
}

// *** Add EXISTING parents to a child ***
if (isset($_POST['add_parents']) AND $_POST['add_parents']!=''){
	$parents= "SELECT * FROM humo_families
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".safe_text($_POST['add_parents'])."'";
	$parents_result = $dbh->query($parents);
	$parentsDb=$parents_result->fetch(PDO::FETCH_OBJ);

	if ($parentsDb->fam_children){
		$fam_children=$parentsDb->fam_children.';'.$pers_gedcomnumber;
	}
	else{
		$fam_children=$pers_gedcomnumber;
	}

	$sql="UPDATE humo_families SET
	fam_children='".$fam_children."',
	fam_changed_date='".$gedcom_date."',
	fam_changed_time='".$gedcom_time."'
	WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".safe_text($_POST['add_parents'])."'";
	$result=$dbh->query($sql);

	// *** Check pers_indexnr, change indexnr if needed ***
	$sql= "SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($pers_gedcomnumber)."'";
	$result = $dbh->query($sql);
	$resultDb=$result->fetch(PDO::FETCH_OBJ);
	$pers_indexnr=$resultDb->pers_indexnr;
	if ($pers_indexnr==''){ $pers_indexnr=$_POST['add_parents']; }

	// *** Add parents to child record ***
	$sql="UPDATE humo_persons SET
	pers_famc='".safe_text($_POST['add_parents'])."',
	pers_indexnr='".safe_text($pers_indexnr)."',
	pers_changed_date='".$gedcom_date."',
	pers_changed_time='".$gedcom_time."'
	WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$pers_gedcomnumber."'";
	$result=$dbh->query($sql);

	family_tree_update($tree_prefix);

	$confirm.='<div class="confirm">';
	$confirm.=__('Parents are selected!');
	$confirm.='</div>';
}

// *** Add child to family ***
if (isset($_POST['child_connect2'])){
	if (isset($_POST["children"])){
		$sql="UPDATE humo_families SET
		fam_children='".safe_text($_POST["children"]).';'.safe_text($_POST["child_connect2"])."',
		fam_changed_date='".$gedcom_date."',
		fam_changed_time='".$gedcom_time."'
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".safe_text($_POST['family_id'])."'";
	}
	else{
		$sql="UPDATE humo_families SET
		fam_children='".safe_text($_POST["child_connect2"])."',
		fam_changed_date='".$gedcom_date."',
		fam_changed_time='".$gedcom_time."'
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".safe_text($_POST['family_id'])."'";
	}
	$result=$dbh->query($sql);

	// *** Check pers_indexnr, change indexnr if needed ***
	$sql= "SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($_POST["child_connect2"])."'";
	$result = $dbh->query($sql);
	$resultDb=$result->fetch(PDO::FETCH_OBJ);
	$pers_indexnr=$resultDb->pers_indexnr;
	if ($pers_indexnr==''){ $pers_indexnr=$_POST['family_id']; }

	// *** Add parents to child record ***
	$sql="UPDATE humo_persons SET
	pers_famc='".safe_text($_POST['family_id'])."',
	pers_indexnr='".safe_text($pers_indexnr)."',
	pers_changed_date='".$gedcom_date."',
	pers_changed_time='".$gedcom_time."'
	WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($_POST["child_connect2"])."'";
	$result=$dbh->query($sql);

	family_tree_update($tree_prefix);
}

// *** Disconnect child ***
if (isset($_GET['child_disconnect'])){
	$confirm.='<div class="confirm">';
		$confirm.=__('Are you sure you want to disconnect this child?');
		$confirm.=' <form method="post" action="'.$phpself.'" style="display : inline;">';
			$confirm.='<input type="hidden" name="page" value="'.$_GET['page'].'">';
			$confirm.='<input type="hidden" name="family_id" value="'.$_GET['family_id'].'">';
			$confirm.='<input type="hidden" name="child_disconnect2" value="'.$_GET['child_disconnect'].'">';
			$confirm.='<input type="hidden" name="child_disconnect_gedcom" value="'.$_GET['child_disconnect_gedcom'].'">';
			$confirm.=' <input type="Submit" name="child_disconnecting" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
			$confirm.=' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
		$confirm.='</form>';
	$confirm.='</div>';
}
if (isset($_POST['child_disconnecting'])){
	$sql="UPDATE humo_families SET
	fam_children='".safe_text($_POST["child_disconnect2"])."',
	fam_changed_date='".$gedcom_date."',
	fam_changed_time='".$gedcom_time."'
	WHERE fam_id='".safe_text($_POST["family_id"])."'";
	$result=$dbh->query($sql);

	// *** Check pers_indexnr, change indexnr if needed ***
	$sql= "SELECT * FROM humo_families
		WHERE fam_id='".safe_text($_POST["family_id"])."'";
	$result = $dbh->query($sql);
	$resultDb=$result->fetch(PDO::FETCH_OBJ);
	$fam_gedcomnumber=$resultDb->fam_gedcomnumber;

	// *** Find child data ***
	$sql= "SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($_POST["child_disconnect_gedcom"])."'";
	$result = $dbh->query($sql);
	$resultDb=$result->fetch(PDO::FETCH_OBJ);
	$pers_indexnr=$resultDb->pers_indexnr;
	if ($pers_indexnr==$fam_gedcomnumber){ $pers_indexnr=''; }

	// *** Remove parents from child record ***
	$sql="UPDATE humo_persons SET
	pers_famc='',
	pers_indexnr='".safe_text($pers_indexnr)."',
	pers_changed_date='".$gedcom_date."',
	pers_changed_time='".$gedcom_time."'
	WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($_POST["child_disconnect_gedcom"])."'";
	$result=$dbh->query($sql);
}

// *** Child move down ***
if (isset($_GET['child_down'])){
	$child_array_org=explode(";",safe_text($_GET['child_array']));
	$child_array=$child_array_org;
	$child_array_id=safe_text($_GET['child_down']);
	$child_array[$child_array_id]=$child_array_org[($child_array_id+1)];
	$child_array[$child_array_id+1]=$child_array_org[($child_array_id)];
	$fam_children='';
	for ($k=0; $k<count($child_array); $k++){
		if ($k>0){ $fam_children.=';'; }
		$fam_children.=$child_array[$k];
	}
	$sql="UPDATE humo_families SET
	fam_children='".$fam_children."',
	fam_changed_date='".$gedcom_date."',
	fam_changed_time='".$gedcom_time."'
	WHERE fam_id='".safe_text($_GET["family_id"])."'";
	$result=$dbh->query($sql);
}

// *** Child move up ***
if (isset($_GET['child_up'])){
	$child_array_org=explode(";",safe_text($_GET['child_array']));
	$child_array=$child_array_org;
	$child_array_id=safe_text($_GET['child_up'])-1;
	$child_array[$child_array_id+1]=$child_array_org[($child_array_id)];
	$child_array[$child_array_id]=$child_array_org[($child_array_id+1)];
	$fam_children='';
	for ($k=0; $k<count($child_array); $k++){
		if ($k>0){ $fam_children.=';'; }
		$fam_children.=$child_array[$k];
	}
	$sql="UPDATE humo_families SET
	fam_children='".$fam_children."',
	fam_changed_date='".$gedcom_date."',
	fam_changed_time='".$gedcom_time."'
	WHERE fam_id='".safe_text($_GET["family_id"])."'";
	$result=$dbh->query($sql);
}


// ***************************
// *** PROCESS DATA FAMILY ***
// ***************************


// *** Add new family with new partner N.N. ***
if (isset($_GET['relation_add'])){
	// *** Generate new gedcomnr, find highest gedcomnumber F100: strip F and order by numeric ***
	$new_nr_qry= "SELECT *, ABS(substring(fam_gedcomnumber, 2)) AS gednr
		FROM humo_families WHERE fam_tree_id='".$tree_id."' ORDER BY gednr DESC LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
	$fam_gedcomnumber='F1';
	if (isset($new_nr->fam_gedcomnumber)) $fam_gedcomnumber='F'.(substr($new_nr->fam_gedcomnumber,1)+1);

	// *** Directly show new marriage on screen ***
	$_POST["marriage_nr"]=$fam_gedcomnumber;
	$marriage=$fam_gedcomnumber;
	$_SESSION['admin_fam_gedcomnumber']=$marriage;

	// *** Generate new gedcomnr, find highest gedcomnumber I100: strip I and order by numeric ***
	$new_nr_qry= "SELECT *, ABS(substring(pers_gedcomnumber, 2)) AS gednr
		FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY gednr DESC LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
	//$new_gedcomnumber='I1';
	$partner_gedcomnumber='I1';
	if (isset($new_nr->pers_gedcomnumber)) $partner_gedcomnumber='I'.(substr($new_nr->pers_gedcomnumber,1)+1);

	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$pers_gedcomnumber."'";
	$person_result = $dbh->query($person_qry);
	$person_db=$person_result->fetch(PDO::FETCH_OBJ);
	if ($person_db->pers_sexe=='M'){
		$man_gedcomnumber=$pers_gedcomnumber; $woman_gedcomnumber=$partner_gedcomnumber; $sexe='F';
	}
	else{
		$man_gedcomnumber=$partner_gedcomnumber; $woman_gedcomnumber=$pers_gedcomnumber; $sexe='M';
	}

	$sql="INSERT INTO humo_families SET
	fam_tree_id='".$tree_id."',
	fam_gedcomnumber='".$fam_gedcomnumber."', fam_kind='',
	fam_man='".safe_text($man_gedcomnumber)."', fam_woman='".safe_text($woman_gedcomnumber)."',
	fam_children='';
	fam_relation_date='', fam_relation_place='', fam_relation_text='',
	fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
	fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
	fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
	fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
	fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
	fam_text='',
	fam_new_date='".$gedcom_date."',
	fam_new_time='".$gedcom_time."'";
	//echo $sql.'<br>';
	$result=$dbh->query($sql);

	// *** Add N.N. partner ***
	$sql="INSERT INTO humo_persons SET
		pers_gedcomnumber='".$partner_gedcomnumber."',
		pers_tree_id='".$tree_id."',
		pers_tree_prefix='".$tree_prefix."',
		pers_famc='', pers_fams='".safe_text($fam_gedcomnumber)."', pers_indexnr='".safe_text($fam_gedcomnumber)."',
		pers_firstname='".__('N.N.')."', pers_callname='', pers_prefix='', pers_lastname='', pers_patronym='', pers_name_text='',
		pers_alive='alive', pers_sexe='".$sexe."', pers_own_code='', pers_place_index='', pers_text='',
		pers_birth_date='', pers_birth_place='', pers_birth_time='', pers_birth_text='', pers_stillborn='',
		pers_bapt_date='', pers_bapt_place='', pers_bapt_text='', pers_religion='',
		pers_death_date='', pers_death_place='', pers_death_time='', pers_death_text='', pers_death_cause='',
		pers_buried_date='', pers_buried_place='', pers_buried_text='', pers_cremation='',
		pers_new_date='".$gedcom_date."', pers_new_time='".$gedcom_time."'";
	//echo $sql.'<br>';
	$result=$dbh->query($sql);

	// *** Add marriage to person records MAN and WOMAN ***
	fams_add($pers_gedcomnumber, $fam_gedcomnumber);

	family_tree_update($tree_prefix);
}

// *** Add new family with selected partner ***
if (isset($_POST['relation_add2']) AND $_POST['relation_add2']!=''){
	// *** Change i10 into I10 ***
	$_POST['relation_add2']=ucfirst($_POST['relation_add2']);

	// *** Generate new gedcomnr, find highest gedcomnumber F100: strip F and order by numeric ***
	$new_nr_qry= "SELECT *, ABS(substring(fam_gedcomnumber, 2)) AS gednr
		FROM humo_families WHERE fam_tree_id='".$tree_id."' ORDER BY gednr DESC LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
	$fam_gedcomnumber='F1';
	if (isset($new_nr->fam_gedcomnumber)) $fam_gedcomnumber='F'.(substr($new_nr->fam_gedcomnumber,1)+1);

	// *** Directly show new marriage on screen ***
	$_POST["marriage_nr"]=$fam_gedcomnumber;
	$marriage=$fam_gedcomnumber;
	$_SESSION['admin_fam_gedcomnumber']=$marriage;

	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$pers_gedcomnumber."'";
	$person_result = $dbh->query($person_qry);
	$person_db=$person_result->fetch(PDO::FETCH_OBJ);
	if ($person_db->pers_sexe=='M'){
		$man_gedcomnumber=$pers_gedcomnumber; $woman_gedcomnumber=$_POST['relation_add2']; $sexe='F';
	}
	else{
		$man_gedcomnumber=$_POST['relation_add2']; $woman_gedcomnumber=$pers_gedcomnumber; $sexe='M';
	}

	$sql="INSERT INTO humo_families SET
	fam_tree_id='".$tree_id."',
	fam_gedcomnumber='".$fam_gedcomnumber."', fam_kind='',
	fam_man='".safe_text($man_gedcomnumber)."', fam_woman='".safe_text($woman_gedcomnumber)."',
	fam_children='';
	fam_relation_date='', fam_relation_place='', fam_relation_text='',
	fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
	fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
	fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
	fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
	fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
	fam_text='',
	fam_new_date='".$gedcom_date."',
	fam_new_time='".$gedcom_time."'";
	//echo $sql.'<br>';
	$result=$dbh->query($sql);

	// *** Add marriage to person records MAN and WOMAN ***
	fams_add($man_gedcomnumber, $fam_gedcomnumber);
	fams_add($woman_gedcomnumber, $fam_gedcomnumber);

	family_tree_update($tree_prefix);
}


//if ($menu_admin=='marriage' AND $person->pers_fams){
	// *** Switch parents ***
	if (isset($_POST['parents_switch'])){
		$sql="UPDATE humo_families SET
		fam_man='".safe_text($_POST["connect_woman"])."',
		fam_woman='".safe_text($_POST["connect_man"])."'
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".safe_text($_POST['marriage'])."'";
		$result=$dbh->query($sql);

		// *** Empty search boxes if a switch is made ***
		$_POST['search_quicksearch_woman']='';
		$_POST['search_quicksearch_man']='';
	}

	// ** Change marriage ***
	if (isset($_POST['marriage_change'])){
		// *** Change i10 into I10 ***
		$_POST["connect_man"]=ucfirst($_POST["connect_man"]);
		$_POST["connect_woman"]=ucfirst($_POST["connect_woman"]);

		// *** Man is changed in marriage ***
		if ($_POST["connect_man"]!=$_POST["connect_man_old"]){
			fams_remove($_POST['connect_man_old'], $_POST['marriage']);	
			fams_add($_POST['connect_man'], $_POST['marriage']);
		}
		// *** Woman is changed in marriage ***
		if ($_POST["connect_woman"]!=$_POST["connect_woman_old"]){
			fams_remove($_POST['connect_woman_old'], $_POST['marriage']);
			fams_add($_POST['connect_woman'], $_POST['marriage']);
		}

		$fam_div_text='';
		if (isset($_POST['fam_div_no_data'])) $fam_div_text='DIVORCE';
		if ($_POST["fam_div_text"]) $fam_div_text=$_POST["fam_div_text"];

		$sql="UPDATE humo_families SET
		fam_kind='".safe_text($_POST["fam_kind"])."',
		fam_man='".safe_text($_POST["connect_man"])."',
		fam_woman='".safe_text($_POST["connect_woman"])."',
		fam_relation_date='".$editor_cls->date_process("fam_relation_date")."',
		fam_relation_end_date='".$editor_cls->date_process("fam_relation_end_date")."',
		fam_relation_place='".$editor_cls->text_process($_POST["fam_relation_place"])."',
		fam_relation_text='".$editor_cls->text_process($_POST["fam_relation_text"],true)."',
		fam_marr_notice_date='".$editor_cls->date_process("fam_marr_notice_date")."',
		fam_marr_notice_place='".$editor_cls->text_process($_POST["fam_marr_notice_place"])."',
		fam_marr_notice_text='".$editor_cls->text_process($_POST["fam_marr_notice_text"],true)."',
		fam_marr_date='".$editor_cls->date_process("fam_marr_date")."',
		fam_marr_place='".$editor_cls->text_process($_POST["fam_marr_place"])."',
		fam_marr_text='".$editor_cls->text_process($_POST["fam_marr_text"],true)."',
		fam_marr_authority='".safe_text($_POST["fam_marr_authority"])."',
		fam_marr_church_date='".$editor_cls->date_process("fam_marr_church_date")."',
		fam_marr_church_place='".$editor_cls->text_process($_POST["fam_marr_church_place"])."',
		fam_marr_church_text='".$editor_cls->text_process($_POST["fam_marr_church_text"],true)."',
		fam_marr_church_notice_date='".$editor_cls->date_process("fam_marr_church_notice_date")."',
		fam_marr_church_notice_place='".$editor_cls->text_process($_POST["fam_marr_church_notice_place"])."',
		fam_marr_church_notice_text='".$editor_cls->text_process($_POST["fam_marr_church_notice_text"],true)."',
		fam_religion='".safe_text($_POST["fam_religion"])."',
		fam_div_date='".$editor_cls->date_process("fam_div_date")."',
		fam_div_place='".$editor_cls->text_process($_POST["fam_div_place"])."',
		fam_div_text='".$editor_cls->text_process($fam_div_text,true)."',
		fam_div_authority='".safe_text($_POST["fam_div_authority"])."',
		fam_text='".$editor_cls->text_process($_POST["fam_text"],true)."',
		fam_changed_date='".$gedcom_date."',
		fam_changed_time='".$gedcom_time."'
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".safe_text($_POST['marriage'])."'";
		$result=$dbh->query($sql);

		family_tree_update($tree_prefix);
	}
//}


// **************************
// *** PROCESS DATA EVENT ***
// **************************


// *** Add new event ***
if (isset($_GET['event_add'])){
	$section='person';
	if ($_GET['event_add']=='add_name'){ $event_kind='name'; $event_event=__('Name'); }
	if ($_GET['event_add']=='add_nobility'){ $event_kind='nobility'; $event_event=__('Title of Nobility'); }
	if ($_GET['event_add']=='add_title'){ $event_kind='title'; $event_event=__('Title'); }
	if ($_GET['event_add']=='add_lordship'){ $event_kind='lordship'; $event_event=__('Title of Lordship'); }
	if ($_GET['event_add']=='add_birth_declaration'){ $event_kind='birth_declaration'; $event_event=__('birth declaration'); }
	if ($_GET['event_add']=='add_baptism_witness'){ $event_kind='baptism_witness'; $event_event=__('baptism witness'); }
	if ($_GET['event_add']=='add_death_declaration'){ $event_kind='death_declaration'; $event_event=__('death declaration'); }
	if ($_GET['event_add']=='add_burial_witness'){ $event_kind='burial_witness'; $event_event=__('burial witness'); }
	if ($_GET['event_add']=='add_profession'){ $event_kind='profession'; $event_event=__('Profession'); }
	if ($_GET['event_add']=='add_picture'){ $event_kind='picture'; $event_event=''; }

	if ($_GET['event_add']=='add_marriage_witness'){ $section='family'; $event_kind='marriage_witness'; $event_event=__('marriage witness'); }
	if ($_GET['event_add']=='add_marriage_witness_rel'){ $section='family'; $event_kind='marriage_witness_rel'; $event_event=__('marriage witness (church)'); }
	if ($_GET['event_add']=='add_marriage_picture'){ $section='family'; $event_kind='picture'; $event_event=''; }

	if ($section=='person'){
		// *** Generate new order number ***
		//$event_sql="SELECT * FROM ".$tree_prefix."events
		$event_sql="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='".$event_kind."'
			ORDER BY event_order DESC LIMIT 0,1";
	}
	if ($section=='family'){
		// *** Generate new order number ***
		//$event_sql="SELECT * FROM ".$tree_prefix."events
		$event_sql="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."' AND event_kind='".$event_kind."'
			ORDER BY event_order DESC LIMIT 0,1";
	}
	$event_qry=$dbh->query($event_sql);
	$eventDb=$event_qry->fetch(PDO::FETCH_OBJ);	
	$event_order=0;
	if (isset($eventDb->event_order)){ $event_order=$eventDb->event_order; }
	$event_order++;

	if ($section=='person'){
		$sql="INSERT INTO humo_events SET
			event_tree_id='".$tree_id."',
			event_person_id='".$pers_gedcomnumber."',
			event_kind='".$event_kind."',
			event_event='".$event_event."',
			event_order='".$event_order."',
			event_new_date='".$gedcom_date."',
			event_new_time='".$gedcom_time."'";
	}
	if ($section=='family'){
		$sql="INSERT INTO humo_events SET
			event_tree_id='".$tree_id."',
			event_family_id='".$marriage."',
			event_kind='".$event_kind."',
			event_order='".$event_order."',
			event_new_date='".$gedcom_date."',
			event_new_time='".$gedcom_time."'";
	}
	$result=$dbh->query($sql);
}

// *** Add person event ***
if (isset($_POST['person_event_add'])){
	// *** Generate new order number ***
	//$event_sql="SELECT * FROM ".$tree_prefix."events
	$event_sql="SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."'
		AND event_person_id='".$pers_gedcomnumber."' AND event_kind='".$_POST["event_kind"]."'
		ORDER BY event_order DESC LIMIT 0,1";
	$event_qry=$dbh->query($event_sql);
	$eventDb=$event_qry->fetch(PDO::FETCH_OBJ);	
	$event_order=0;
	if (isset($eventDb->event_order)){ $event_order=$eventDb->event_order; }
	$event_order++;

	//$sql="INSERT INTO ".$tree_prefix."events SET
	$sql="INSERT INTO humo_events SET
		event_tree_id='".$tree_id."',
		event_person_id='".$pers_gedcomnumber."',
		event_kind='".$_POST["event_kind"]."',
		event_order='".$event_order."',
		event_new_date='".$gedcom_date."',
		event_new_time='".$gedcom_time."'";
	$result=$dbh->query($sql);
}

// *** Add marriage event ***
if (isset($_POST['marriage_event_add'])){
	// *** Generate new order number ***
	$event_sql="SELECT * FROM humo_events
		WHERE event_tree_id='".$tree_id."'
		AND event_family_id='".$marriage."' AND event_kind='".$_POST["event_kind"]."'
		ORDER BY event_order DESC LIMIT 0,1";
	$event_qry=$dbh->query($event_sql);
	$eventDb=$event_qry->fetch(PDO::FETCH_OBJ);	
	$event_order=0;
	if (isset($eventDb->event_order)){ $event_order=$eventDb->event_order; }
	$event_order++;

	//$sql="INSERT INTO ".$tree_prefix."events SET
	$sql="INSERT INTO humo_events SET
		event_tree_id='".$tree_id."',
		event_family_id='".$marriage."',
		event_kind='".$_POST["event_kind"]."',
		event_order='".$event_order."',
		event_new_date='".$gedcom_date."',
		event_new_time='".$gedcom_time."'";
	$result=$dbh->query($sql);
}


// *** Upload images ***
if (isset($_FILES['photo_upload']) AND $_FILES['photo_upload']['name']){

	// *** Picture list for selecting pictures ***
	$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='".$tree_prefix."'");
	$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
	$tree_pict_path=$dataDb->tree_pict_path;
	$dir=$path_prefix.$tree_pict_path;

	if ( $_FILES['photo_upload']['type']=="image/pjpeg" || $_FILES['photo_upload']['type']=="image/jpeg"){
		$fault="";
		// 100000=100kb.
		if($_FILES['photo_upload']['size']>2000000){ $fault=__('Photo too large'); }
		if (!$fault){
			$picture_original=$dir.$_FILES['photo_upload']['name'];
			$picture_thumb=$dir.'thumb_'.$_FILES['photo_upload']['name'];
			if (!move_uploaded_file($_FILES['photo_upload']['tmp_name'],$picture_original)){
				echo __('Photo upload failed, check folder rights');
			}
			else{
				// *** Resize uploaded picture ***
				if (strtolower(substr($picture_original, -3)) == "jpg"){
					//Breedte en hoogte origineel bepalen
					list($width, $height) = getimagesize($picture_original);

					$create_thumb_height=120;
					$newheight=$create_thumb_height;
					$factor=$height/$newheight;
					$newwidth=$width/$factor;

					$create_thumb = imagecreatetruecolor($newwidth, $newheight);
					$source = imagecreatefromjpeg($picture_original);

					// Resize
					imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
					@imagejpeg($create_thumb, $picture_thumb);
				}

//					$_POST['text_event'][$key]=$_FILES['photo_upload']['name'];

				// *** Add picture to array ***
				$picture_array[]=$_FILES['photo_upload']['name'];

				// *** Re-order pictures by alphabet ***
				@sort($picture_array);
				$nr_pictures=count($picture_array);
			}
		}
		else{
			print "<FONT COLOR=red>$fault</FONT>";
		}
	}
	elseif ( $_FILES['photo_upload']['type']=="audio/mpeg" || $_FILES['photo_upload']['type']=="audio/mpeg3" || 
		$_FILES['photo_upload']['type']=="audio/x-mpeg" || $_FILES['photo_upload']['type']=="audio/x-mpeg3" || 
		$_FILES['photo_upload']['type']=="audio/mpg" || $_FILES['photo_upload']['type']=="audio/mp3" || 
		$_FILES['photo_upload']['type']=="audio/mid" || $_FILES['photo_upload']['type']=="audio/midi" || 
		$_FILES['photo_upload']['type']=="audio/x-midi" || $_FILES['photo_upload']['type']=="audio/x-ms-wma" || 
		$_FILES['photo_upload']['type']=="audio/wav" || $_FILES['photo_upload']['type']=="audio/x-wav" || 
		$_FILES['photo_upload']['type']=="audio/x-pn-realaudio" || $_FILES['photo_upload']['type']=="audio/x-realaudio" || 
		$_FILES['photo_upload']['type']=="application/pdf" || $_FILES['photo_upload']['type']=="application/msword" || 
		$_FILES['photo_upload']['type']=="application/vnd.openxmlformats-officedocument.wordprocessingml.document" ||
		$_FILES['photo_upload']['type']=="video/quicktime" || $_FILES['photo_upload']['type']=="video/x-ms-wmv" ||
		$_FILES['photo_upload']['type']=="video/avi" || $_FILES['photo_upload']['type']=="video/x-msvideo" ||   
		$_FILES['photo_upload']['type']=="video/msvideo" || $_FILES['photo_upload']['type']=="video/mpeg" 	){
		$fault="";
		// 49MB
		if($_FILES['photo_upload']['size']>49000000){ $fault=__('Media too large'); }
		if (!$fault){
			$picture_original=$dir.$_FILES['photo_upload']['name'];
			if (!move_uploaded_file($_FILES['photo_upload']['tmp_name'],$picture_original)){
				echo __('Media upload failed, check folder rights');
			}
			else{
//					$_POST['text_event'][$key]=$_FILES['photo_upload']['name'];

				// *** Add picture to array ***
				$picture_array[]=$_FILES['photo_upload']['name'];

				// *** Re-order pictures by alphabet ***
				@sort($picture_array);
				$nr_pictures=count($picture_array);
			}
		}
		else{
			print "<FONT COLOR=red>$fault</FONT>";
		}
	}
	else{
		echo '<FONT COLOR=red>'.__('No valid picture, media or document file').'</font>';
	}
}


// *** Change event ***
if (isset($_POST['event_id'])){
	foreach($_POST['event_id'] as $key=>$value){  
		$event_event=$editor_cls->text_process($_POST["text_event"][$key]);
		if (isset($_POST["text_event2"][$key]) AND $_POST["text_event2"][$key]!=''){ $event_event=$editor_cls->text_process($_POST["text_event2"][$key]); }

		//$sql="UPDATE ".$tree_prefix."events SET
		$sql="UPDATE humo_events SET
			event_event='".$event_event."',
			event_date='".$editor_cls->date_process("event_date",$key)."',
			event_place='".$editor_cls->text_process($_POST["event_place"][$key])."',
			event_changed_date='".$gedcom_date."', ";
		if (isset($_POST["event_gedcom"][$key])){
			$sql.="event_gedcom='".$editor_cls->text_process($_POST["event_gedcom"][$key])."',";
		}
		if (isset($_POST["event_text"][$key])){
			$sql.="event_text='".$editor_cls->text_process($_POST["event_text"][$key])."',";
		}
		$sql.=" event_changed_time='".$gedcom_time."'";
		$sql.=" WHERE event_id='".safe_text($_POST["event_id"][$key])."'";
		$result=$dbh->query($sql);

		family_tree_update($tree_prefix);
	}
}

// *** Remove event ***
if (isset($_GET['event_drop'])){
	$confirm.='<div class="confirm">';
		$confirm.=__('Are you sure you want to remove this event?');
		$confirm.=' <form method="post" action="'.$phpself.'" style="display : inline;">';
			$confirm.='<input type="hidden" name="page" value="'.$_GET['page'].'">';
			if (isset($_GET['event_person'])){
				$confirm.='<input type="hidden" name="event_person" value="event_person">';
			}
			if (isset($_GET['event_family'])){
				$confirm.='<input type="hidden" name="event_family" value="event_family">';
			}
			$confirm.='<input type="hidden" name="event_kind" value="'.$_GET['event_kind'].'">';
			$confirm.='<input type="hidden" name="event_drop" value="'.$_GET['event_drop'].'">';
			$confirm.=' <input type="Submit" name="event_drop2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
			$confirm.=' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
		$confirm.='</form>';
	$confirm.='</div>';
}
if (isset($_POST['event_drop2'])){
	$event_kind=safe_text($_POST['event_kind']);
	$event_order_id=safe_text($_POST['event_drop']);

	if (isset($_POST['event_person'])){
		//$sql="DELETE FROM ".$tree_prefix."events
		$sql="DELETE FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='".$event_kind."' AND event_order='".$event_order_id."'";
		$result=$dbh->query($sql);

		//$event_sql="SELECT * FROM ".$tree_prefix."events
		$event_sql="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='".$event_kind."' AND event_order>'".$event_order_id."' ORDER BY event_order";
		$event_qry=$dbh->query($event_sql);
		while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
			//$sql="UPDATE ".$tree_prefix."events SET
			$sql="UPDATE humo_events SET
			event_order='".($eventDb->event_order-1)."',
			event_changed_date='".$gedcom_date."',
			event_changed_time='".$gedcom_time."'
			WHERE event_id='".$eventDb->event_id."'";
			$result=$dbh->query($sql);
		}
	}

	if (isset($_POST['event_family'])){
		//$sql="DELETE FROM ".$tree_prefix."events
		$sql="DELETE FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."' AND event_kind='".$event_kind."' AND event_order='".$event_order_id."'";
		$result=$dbh->query($sql);

		//$event_sql="SELECT * FROM ".$tree_prefix."events
		$event_sql="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."' AND event_kind='".$event_kind."' AND event_order>'".$event_order_id."' ORDER BY event_order";
		$event_qry=$dbh->query($event_sql);
		while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
			$sql="UPDATE humo_events SET
			event_order='".($eventDb->event_order-1)."',
			event_changed_date='".$gedcom_date."',
			event_changed_time='".$gedcom_time."'
			WHERE event_id='".$eventDb->event_id."'";
			$result=$dbh->query($sql);
		}
	}
}

if (isset($_GET['event_down'])){
	$event_kind=safe_text($_GET['event_kind']);
	$event_order=safe_text($_GET["event_down"]);

	if (isset($_GET['event_person'])){
		$sql="UPDATE humo_events SET event_order='99'
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'
		AND event_kind='".$event_kind."'
		AND event_order='".$event_order."'";
		$result=$dbh->query($sql);

		$sql="UPDATE humo_events SET event_order='".$event_order."'
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'
		AND event_kind='".$event_kind."'
		AND event_order='".($event_order+1)."'";
		$result=$dbh->query($sql);

		$sql="UPDATE humo_events SET event_order='".($event_order+1)."'
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'
		AND event_kind='".$event_kind."'
		AND event_order=99";
		$result=$dbh->query($sql);
	}

	if (isset($_GET['event_family'])){
		$sql="UPDATE humo_events SET event_order='99'
		WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."'
		AND event_kind='".$event_kind."'
		AND event_order='".$event_order."'";
		$result=$dbh->query($sql);

		$sql="UPDATE humo_events SET event_order='".$event_order."'
		WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."'
		AND event_kind='".$event_kind."'
		AND event_order='".($event_order+1)."'";
		$result=$dbh->query($sql);

		$sql="UPDATE humo_events SET event_order='".($event_order+1)."'
		WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."'
		AND event_kind='".$event_kind."'
		AND event_order=99";
		$result=$dbh->query($sql);
	}
}

if (isset($_GET['event_up'])){
	$event_kind=safe_text($_GET['event_kind']);
	$event_order=safe_text($_GET['event_up']);

	if (isset($_GET['event_person'])){
		$sql="UPDATE humo_events SET
		event_order='99'
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'
		AND event_kind='".$event_kind."'
		AND event_order='".$event_order."'";
		$result=$dbh->query($sql);

		$sql="UPDATE humo_events SET
		event_order='".$event_order."'
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'
		AND event_kind='".$event_kind."'
		AND event_order='".($event_order-1)."'";
		$result=$dbh->query($sql);
		
		$sql="UPDATE humo_events SET
		event_order='".($event_order-1)."'
		WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'
		AND event_kind='".$event_kind."'
		AND event_order=99";
		$result=$dbh->query($sql);
	}

	if (isset($_GET['event_family'])){
		$sql="UPDATE humo_events SET
		event_order='99'
		WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."'
		AND event_kind='".$event_kind."'
		AND event_order='".$event_order."'";
		$result=$dbh->query($sql);

		$sql="UPDATE humo_events SET
		event_order='".$event_order."'
		WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."'
		AND event_kind='".$event_kind."'
		AND event_order='".($event_order-1)."'";
		$result=$dbh->query($sql);

		$sql="UPDATE humo_events SET
		event_order='".($event_order-1)."'
		WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."'
		AND event_kind='".$event_kind."'
		AND event_order=99";
		$result=$dbh->query($sql);
	}
}


// ************************
// *** Save data places ***
// ************************


// *** Remove living place ***
if (isset($_GET['living_place_drop'])){
	echo '<div class="confirm">';
	echo __('Are you sure you want to delete this place? ');
	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
	echo '<input type="hidden" name="person_place_address" value="person_place_address">';
	echo '<input type="hidden" name="living_place_id" value="'.$_GET['living_place_drop'].'">';
	echo ' <input type="Submit" name="living_place_drop2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	echo '</form>';
	echo '</div>';
}
if (isset($_POST['living_place_drop2'])){
	$living_place_order=safe_text($_POST['living_place_id']);
	//$sql="DELETE FROM ".$tree_prefix."addresses WHERE address_person_id='".$pers_gedcomnumber."'
	//	AND address_order='".$living_place_order."'";
	$sql="DELETE FROM humo_addresses WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."'
		AND address_order='".$living_place_order."'";
	$result=$dbh->query($sql);

	//$address_sql="SELECT * FROM ".$tree_prefix."addresses
	$address_sql="SELECT * FROM humo_addresses
		WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."' AND address_order>'".$living_place_order."'
		ORDER BY address_order";
	$event_qry=$dbh->query($address_sql);
	while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
		//$sql="UPDATE ".$tree_prefix."addresses SET
		$sql="UPDATE humo_addresses SET
		address_order='".($eventDb->address_order-1)."',
		address_changed_date='".$gedcom_date."',
		address_changed_time='".$gedcom_time."'
		WHERE address_id='".$eventDb->address_id."'";
		$result=$dbh->query($sql);
	}
}

if (isset($_GET['living_place_add'])){
	// *** Generate new order number ***
	//$address_sql="SELECT * FROM ".$tree_prefix."addresses
	$address_sql="SELECT * FROM humo_addresses WHERE address_tree_id='".$tree_id."'
		AND address_person_id='".$pers_gedcomnumber."' ORDER BY address_order DESC LIMIT 0,1";
	$address_qry=$dbh->query($address_sql);
	$addressDb=$address_qry->fetch(PDO::FETCH_OBJ);	
	$address_order=0; if (isset($addressDb->address_order)){ $address_order=$addressDb->address_order; }
	$address_order++;

	//$sql="INSERT INTO ".$tree_prefix."addresses SET
	$sql="INSERT INTO humo_addresses SET
		address_tree_id='".$tree_id."',
		address_person_id='".$pers_gedcomnumber."',
		address_date='',
		address_place='',
		address_order='".$address_order."',
		address_new_date='".$gedcom_date."',
		address_new_time='".$gedcom_time."'";
		//echo $sql;
	$result=$dbh->query($sql);
}

// *** Change address ***
if (isset($_POST['person_address_id'])){
	foreach($_POST['person_address_id'] as $key=>$value){  
		//$sql="UPDATE ".$tree_prefix."addresses SET
		//address_place='".$editor_cls->text_process($_POST["address_place"][$key])."',
		$sql="UPDATE humo_addresses SET
			address_date='".$editor_cls->date_process("address_date",$key)."',
			address_place='".$editor_cls->text_process($_POST["address_place_".$key])."',
			address_changed_date='".$gedcom_date."', ";
		//if (isset($_POST["address_text"][$key])){
			//$sql.="address_text='".$editor_cls->text_process($_POST["address_text"][$key])."',";
		//}
		$sql.=" address_changed_time='".$gedcom_time."'";
		$sql.=" WHERE address_id='".safe_text($_POST["person_address_id"][$key])."'";
		//echo $sql.'<br>';
		$result=$dbh->query($sql);

		family_tree_update($tree_prefix);
	}
}

if (isset($_GET['living_place_down'])){
	//$sql="UPDATE ".$tree_prefix."addresses SET
	$sql="UPDATE humo_addresses SET
	address_order='99'
	WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."' AND address_order='".safe_text($_GET["living_place_down"])."'";
	$result=$dbh->query($sql);

	//$sql="UPDATE ".$tree_prefix."addresses SET
	$sql="UPDATE humo_addresses SET
	address_order='".(safe_text($_GET['living_place_down']))."'
	WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."' AND address_order='".(safe_text($_GET["living_place_down"])+1)."'";
	$result=$dbh->query($sql);

	//$sql="UPDATE ".$tree_prefix."addresses SET
	$sql="UPDATE humo_addresses SET
	address_order='".(safe_text($_GET['living_place_down'])+1)."'
	WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."' AND address_order=99";
	$result=$dbh->query($sql);
}

if (isset($_GET['living_place_up'])){
	//$sql="UPDATE ".$tree_prefix."addresses SET
	$sql="UPDATE humo_addresses SET
	address_order='99'
	WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."' AND address_order='".safe_text($_GET["living_place_up"])."'";
	$result=$dbh->query($sql);

	//$sql="UPDATE ".$tree_prefix."addresses SET
	$sql="UPDATE humo_addresses SET
	address_order='".(safe_text($_GET['living_place_up']))."'
	WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."' AND address_order='".(safe_text($_GET["living_place_up"])-1)."'";
	$result=$dbh->query($sql);

	//$sql="UPDATE ".$tree_prefix."addresses SET
	$sql="UPDATE humo_addresses SET
	address_order='".(safe_text($_GET['living_place_up'])-1)."'
	WHERE address_tree_id='".$tree_id."' AND address_person_id='".$pers_gedcomnumber."' AND address_order=99";
	$result=$dbh->query($sql);
}
?>