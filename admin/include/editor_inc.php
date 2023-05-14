<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
	exit;
}

$gedcom_date = strtoupper(date("d M Y"));
$gedcom_time = date("H:i:s");

// *** Return deletion confim box in $confirm variabele ***
$confirm = '';
$confirm_relation = '';

if (isset($_GET['pers_favorite'])) {
	if ($_GET['pers_favorite'] == "1") {
		$sql = "INSERT INTO humo_settings SET
			setting_variable='admin_favourite',
			setting_value='" . safe_text_db($pers_gedcomnumber) . "',
			setting_tree_id='" . safe_text_db($tree_id) . "'";
		$result = $dbh->query($sql);
	} else {
		$sql = "DELETE FROM humo_settings
			WHERE setting_variable='admin_favourite'
			AND setting_value='" . safe_text_db($pers_gedcomnumber) . "'
			AND setting_tree_id='" . safe_text_db($tree_id) . "'";
		$result = $dbh->query($sql);
	}
}


// ***************************
// *** PROCESS DATA PERSON ***
// ***************************

if (isset($_POST['person_remove'])) {
	$confirm .= '<div class="confirm">';
	$confirm .= __('This will disconnect this person from parents, spouses and children <b>and delete it completely from the database.</b> Do you wish to continue?');

	// GRAYED-OUT and DISABLED! UNDER CONSTRUCTION!
	$confirm .= '<br>';
	//$disabled='';
	$disabled = ' DISABLED';
	$confirm .= '<span style="color:#6D7B8D;">';
	$selected = ''; //if ($selected_alive=='alive'){ $selected=' CHECKED'; }
	$confirm .= ' <input type="checkbox" name="XXXXX" value="XXXXX"' . $selected . $disabled . '> ' . __('Also remove ALL RELATED PERSONS (including all items)') . '<br>';
	$confirm .= '</span>';

	$confirm .= ' <form method="post" action="' . $phpself . '" style="display : inline;">';
	$confirm .= '<input type="hidden" name="page" value="' . $page . '">';
	$confirm .= ' <input type="Submit" name="person_remove2" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
	$confirm .= ' <input type="Submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
	$confirm .= '</form>';
	$confirm .= '</div>';
}
if (isset($_POST['person_remove2'])) {
	$confirm .= '<div class="confirm">';

	$personDb = $db_functions->get_person($pers_gedcomnumber);

	// *** If person is married: remove marriages from family ***
	if ($personDb->pers_fams) {
		$fams_array = explode(";", $personDb->pers_fams);
		foreach ($fams_array as $key => $value) {
			$famDb = $db_functions->get_family($fams_array[$key]);

			if ($famDb->fam_man == $pers_gedcomnumber) {
				// *** Completely remove marriage if man and woman are removed *** 
				if ($famDb->fam_woman == '' or $famDb->fam_woman == '0') {

					// *** Remove parents by children ***
					$fam_children = explode(";", $famDb->fam_children);
					foreach ($fam_children as $key2 => $value) {
						$sql = "UPDATE humo_persons SET pers_famc=''
							WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $fam_children[$key2] . "'";
						$result = $dbh->query($sql);
					}

					$sql = "DELETE FROM humo_families
						WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
					$result = $dbh->query($sql);
				} else {
					$sql = "UPDATE humo_families SET fam_man='0'
						WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
					$result = $dbh->query($sql);
					$confirm .= __('Person disconnected from marriage(s).') . '<br>';
				}
			}

			if ($famDb->fam_woman == $pers_gedcomnumber) {
				// *** Completely remove marriage if man and woman are removed *** 
				if ($famDb->fam_man == '' or $famDb->fam_man == '0') {

					// *** Remove parents by children ***
					$fam_children = explode(";", $famDb->fam_children);
					foreach ($fam_children as $key2 => $value) {
						$sql = "UPDATE humo_persons SET pers_famc=''
							WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $fam_children[$key2] . "'";
						$result = $dbh->query($sql);
					}

					$sql = "DELETE FROM humo_families
						WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
					$result = $dbh->query($sql);
				} else {
					$sql = "UPDATE humo_families SET fam_woman='0'
						WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
					$result = $dbh->query($sql);
					$confirm .= __('Person disconnected from marriage(s).') . '<br>';
				}
			}
		}
	}

	// *** If person is a child: remove child number from parents family ***
	if ($personDb->pers_famc) {
		$famDb = $db_functions->get_family($personDb->pers_famc);

		$fam_children = explode(";", $famDb->fam_children);
		foreach ($fam_children as $key => $value) {
			if ($fam_children[$key] != $pers_gedcomnumber) {
				$fam_children2[] = $fam_children[$key];
			}
		}
		$fam_children3 = '';
		if (isset($fam_children2[0])) {
			$fam_children3 = implode(";", $fam_children2);
		}

		$sql = "UPDATE humo_families SET
			fam_children='" . $fam_children3 . "'
			WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $personDb->pers_famc . "'";
		$result = $dbh->query($sql);

		$confirm .= __('Person disconnected from parents.') . '<br>';
	}

	$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'";
	$result = $dbh->query($sql);

	$sql = "DELETE FROM humo_addresses WHERE address_tree_id='" . $tree_id . "'
		AND address_connect_sub_kind='person'
		AND address_connect_id='" . $pers_gedcomnumber . "'";
	$result = $dbh->query($sql);

	$sql = "DELETE FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $pers_gedcomnumber . "'";
	$result = $dbh->query($sql);

	$sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_connect_id='" . $pers_gedcomnumber . "'";
	$result = $dbh->query($sql);

	// *** Added in march 2023 ***
	$sql = "DELETE FROM humo_user_notes WHERE note_tree_id='" . $tree_id . "' AND note_connect_id='" . $pers_gedcomnumber . "'";
	$result = $dbh->query($sql);

	// *** Update cache for list of latest changes ***
	cache_latest_changes(true);

	$confirm .= __('Person is removed');

	// *** Select new person ***
	$new_nr_qry = "SELECT * FROM humo_settings
		WHERE setting_variable='admin_favourite'
		AND setting_tree_id='" . $tree_id . "' LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	if ($new_nr_result and $new_nr_result->rowCount()) {
		@$new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
		$pers_gedcomnumber = $new_nr->setting_value;
		$_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;
	} else {
		$new_nr_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' LIMIT 0,1";
		$new_nr_result = $dbh->query($new_nr_qry);
		$new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
		if ($new_nr->pers_gedcomnumber) {
			$pers_gedcomnumber = $new_nr->pers_gedcomnumber;
			$_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;
		}
	}

	family_tree_update($tree_id);

	$confirm .= '</div>';
}

//if (isset($_POST['person_change'])){
$save_person_data = false;
if (isset($_POST['person_change'])) $save_person_data = true;
// *** Also save person data if name is added ***
if (isset($_POST['event_add_name'])) $save_person_data = true;
// *** Also save person data if profession is added ***
if (isset($_POST['event_add_profession'])) $save_person_data = true;
// *** Also save person data if religion is added ***
if (isset($_POST['event_add_religion'])) $save_person_data = true;
// *** Also save person data if person event is added ***
if (isset($_POST['person_event_add'])) $save_person_data = true;

// *** Also save person data if witnesses are added ***
if (isset($_POST['add_birth_declaration'])) $save_person_data = true;
if (isset($_POST['add_baptism_witness'])) $save_person_data = true;
if (isset($_POST['add_death_declaration'])) $save_person_data = true;
if (isset($_POST['add_burial_witness'])) $save_person_data = true;

// *** Also save person data if addresses are added ***
if (isset($_POST['person_add_address'])) $save_person_data = true;

// *** Also save person data if media is added ***
if (isset($_POST['add_picture'])) $save_person_data = true;
if (isset($_POST['person_add_media'])) $save_person_data = true;

//DIT MOETEN KNOPPEN WORDEN. $_POST dus.
//TO DO sources, editor notes, wisselen van vrouw en man in relatie, toevoegen kind.
// Bij bronnen een array meesturen met alle namen van de bron knoppen?
/* Voorbeeld:
<input type="text" name="add_source_button[]" value="'.$page_source.'"/>
<input type="text" name="add_source_button[]" value="'.$page_source.'"/>
<input type="text" name="add_source_button[]" value="'.$page_source.'"/>

http://localhost/humo-genealogy/admin/index.php?
page=editor&
source_add3=1&
connect_kind=person&
>> connect_sub_kind=pers_birth_source&
>> connect_connect_id=I1180
#pers_birth_sourceI1180
*/

if ($save_person_data) {
	// *** Manual alive setting ***
	@$pers_alive = safe_text_db($_POST["pers_alive"]);
	// *** Only change alive setting if birth or bapise date is changed ***
	if ($_POST["pers_birth_date_previous"] != $_POST["pers_birth_date"]) {
		if (is_numeric(substr($_POST["pers_birth_date"], -4))) {
			if (date("Y") - substr($_POST["pers_birth_date"], -4) > 120) $pers_alive = 'deceased';
		}
	}
	if ($_POST["pers_bapt_date_previous"] != $_POST["pers_bapt_date"]) {
		if (is_numeric(substr($_POST["pers_bapt_date"], -4))) {
			if (date("Y") - substr($_POST["pers_bapt_date"], -4) > 120) $pers_alive = 'deceased';
		}
	}
	// *** If person is deceased, set alive setting ***
	if ($_POST["pers_death_date"] or $_POST["pers_death_place"] or $_POST["pers_buried_date"] or $_POST["pers_buried_place"]) {
		$pers_alive = 'deceased';
	}

	$pers_prefix = $editor_cls->text_process($_POST["pers_prefix"]);
	$pers_prefix = str_replace(' ', '_', $pers_prefix);

	//pers_callname='".$editor_cls->text_process($_POST["pers_callname"])."',
	$sql = "UPDATE humo_persons SET
	pers_firstname='" . $editor_cls->text_process($_POST["pers_firstname"]) . "',
	pers_prefix='" . $pers_prefix . "',
	pers_lastname='" . $editor_cls->text_process($_POST["pers_lastname"]) . "',
	pers_patronym='" . $editor_cls->text_process($_POST["pers_patronym"]) . "',
	pers_name_text='" . $editor_cls->text_process($_POST["pers_name_text"], true) . "',
	pers_alive='" . $pers_alive . "',
	pers_sexe='" . safe_text_db($_POST["pers_sexe"]) . "',
	pers_own_code='" . safe_text_db($_POST["pers_own_code"]) . "',
	pers_text='" . $editor_cls->text_process($_POST["person_text"], true) . "',
	pers_changed_user='" . $username . "',
	pers_changed_date='" . $gedcom_date . "',
	pers_changed_time='" . $gedcom_time . "'
	WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($pers_gedcomnumber) . "'";
	//echo $sql;
	$result = $dbh->query($sql);

	$pers_stillborn = '';
	if (isset($_POST["pers_stillborn"])) $pers_stillborn = 'y';

	$pers_death_cause = $_POST["pers_death_cause"];
	if (isset($_POST["pers_death_cause2"]) and $_POST["pers_death_cause2"]) $pers_death_cause = $_POST["pers_death_cause2"];

	// *** Automatically calculate birth date if death date and death age is used ***
	if ($_POST["pers_death_age"] != '' and $_POST["pers_death_date"] != '' and $_POST["pers_birth_date"] == '' and $_POST["pers_bapt_date"] == '') {
		$_POST["pers_birth_date"] = 'ABT ' . (substr($_POST["pers_death_date"], -4) - $_POST["pers_death_age"]);
	}

	// *** Process estimates/ calculated date for privacy filter ***
	$pers_cal_date = '';
	if ($_POST["pers_birth_date"]) $pers_cal_date = $_POST["pers_birth_date"];
	elseif ($_POST["pers_bapt_date"]) $pers_cal_date = $_POST["pers_bapt_date"];
	$pers_cal_date = substr($pers_cal_date, -4);

	$sql = "UPDATE humo_persons SET
	pers_birth_date='" . $editor_cls->date_process("pers_birth_date") . "',
	pers_birth_place='" . $editor_cls->text_process($_POST["pers_birth_place"]) . "',
	pers_birth_time='" . $editor_cls->text_process($_POST["pers_birth_time"]) . "',
	pers_birth_text='" . $editor_cls->text_process($_POST["pers_birth_text"], true) . "',
	pers_stillborn='" . $pers_stillborn . "',
	pers_bapt_date='" . $editor_cls->date_process("pers_bapt_date") . "',
	pers_bapt_place='" . $editor_cls->text_process($_POST["pers_bapt_place"]) . "',
	pers_bapt_text='" . $editor_cls->text_process($_POST["pers_bapt_text"], true) . "',
	pers_religion='" . safe_text_db($_POST["pers_religion"]) . "',
	pers_death_date='" . $editor_cls->date_process("pers_death_date") . "',
	pers_death_place='" . $editor_cls->text_process($_POST["pers_death_place"]) . "',
	pers_death_time='" . $editor_cls->text_process($_POST["pers_death_time"]) . "',
	pers_death_text='" . $editor_cls->text_process($_POST["pers_death_text"], true) . "',
	pers_death_cause='" . safe_text_db($pers_death_cause) . "',
	pers_death_age='" . safe_text_db($_POST["pers_death_age"]) . "',
	pers_buried_date='" . $editor_cls->date_process("pers_buried_date") . "',
	pers_buried_place='" . $editor_cls->text_process($_POST["pers_buried_place"]) . "',
	pers_buried_text='" . $editor_cls->text_process($_POST["pers_buried_text"], true) . "',";
	if ($pers_cal_date) $sql .= "pers_cal_date='" . $pers_cal_date . "',";
	$sql .= "pers_cremation='" . safe_text_db($_POST["pers_cremation"]) . "',
	pers_cremation='" . safe_text_db($_POST["pers_cremation"]) . "',
	pers_changed_user='" . $username . "',
	pers_changed_date='" . $gedcom_date . "',
	pers_changed_time='" . $gedcom_time . "'
	WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($pers_gedcomnumber) . "'";
	$result = $dbh->query($sql);

	// extra UPDATE queries if jewish dates is enabled
	if ($humo_option['admin_hebnight'] == "y") {
		$per_bir_heb = "";
		$per_bur_heb = "";
		$per_dea_heb = "";
		if (isset($_POST["pers_birth_date_hebnight"]))  $per_bir_heb = $_POST["pers_birth_date_hebnight"];
		if (isset($_POST["pers_buried_date_hebnight"]))  $per_bur_heb = $_POST["pers_buried_date_hebnight"];
		if (isset($_POST["pers_death_date_hebnight"]))  $per_dea_heb = $_POST["pers_death_date_hebnight"];
		$sql = "UPDATE humo_persons SET 
			pers_birth_date_hebnight='" . safe_text_db($per_bir_heb) . "',
			pers_death_date_hebnight='" . safe_text_db($per_dea_heb) . "',
			pers_buried_date_hebnight='" . safe_text_db($per_bur_heb) . "'
			WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($pers_gedcomnumber) . "'";
		$result = $dbh->query($sql);
	}

	// Extra UPDATE queries if Hebrew name is displayed in main Name section (and not under name event)
	if ($humo_option['admin_hebname'] == "y") {
		$sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_gedcom = '_HEBN' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_kind='name' AND event_connect_kind='person'";
		$result = $dbh->query($sql);
		if ($result->rowCount() != 0) {     // a Hebrew name entry already exists for this person: UPDATE 
			if ($_POST["even_hebname"] == '') {  // empty entry: existing hebrew name was deleted so delete the event
				$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_gedcom='_HEBN'  AND event_connect_kind='person' AND event_connect_id='" . safe_text_db($pers_gedcomnumber) . "' AND event_kind='name' ";
				$result = $dbh->query($sql);
			} else {  // update or retain the entered value
				//$hebnameDb=$result->fetch(PDO::FETCH_OBJ);
				$sql = "UPDATE `humo_events` SET 
				event_event='" . safe_text_db($_POST["even_hebname"]) . "',
				event_changed_user='" . $username . "',
				event_changed_date='" . $gedcom_date . "',
				event_changed_time='" . $gedcom_time . "'
				WHERE event_tree_id='" . $tree_id . "' AND  event_gedcom='_HEBN' AND event_kind='name' AND event_connect_id='" . safe_text_db($pers_gedcomnumber) . "'";
				$doit = $dbh->query($sql);
			}
		} elseif ($_POST["even_hebname"] != '') {  // new Hebrew name event: INSERT
			// *** Add event. If event is new, use: $new_event=true. ***
			// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
			add_event(false, 'person', $pers_gedcomnumber, 'name', $_POST["even_hebname"], '_HEBN', '', '', '');
		}
	}

	// Extra UPDATE queries if brit mila is displayed 
	if ($humo_option['admin_brit'] == "y") {
		$sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_gedcom = '_BRTM' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
		$result = $dbh->query($sql);
		if ($result->rowCount() != 0) {     // a brit mila already exists for this person: UPDATE 
			if ($_POST["even_brit_date"] == '' and $_POST["even_brit_place"] == '' and $_POST["even_brit_text"] == '') {
				$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_gedcom='_BRTM'  AND event_connect_kind='person' AND event_connect_id='" . safe_text_db($pers_gedcomnumber) . "' AND event_kind='event' ";
				$result = $dbh->query($sql);
			} else {
				//$britDb=$result->fetch(PDO::FETCH_OBJ); echo $britDb->event_gedcom."---".$britDb->event_connect_id;
				$sql = "UPDATE humo_events SET
				event_date='" . $editor_cls->date_process("even_brit_date") . "',
				event_place='" . safe_text_db($_POST["even_brit_place"]) . "',
				event_text='" . safe_text_db($_POST["even_brit_text"]) . "',
				event_changed_user='" . $username . "',
				event_changed_date='" . $gedcom_date . "',
				event_changed_time='" . $gedcom_time . "'
				WHERE event_tree_id='" . $tree_id . "' AND  event_gedcom='_BRTM' AND event_connect_id='" . safe_text_db($pers_gedcomnumber) . "'";
				$doit = $dbh->query($sql);
			}
		} elseif ((isset($_POST["even_brit_date"]) and $_POST["even_brit_date"] != '')
			or (isset($_POST["even_brit_place"]) and $_POST["even_brit_place"] != '')
			or (isset($_POST["even_brit_text"]) and $_POST["even_brit_text"] != '')
		) {  // new brit mila event: INSERT

			// *** Add event. If event is new, use: $new_event=true. ***
			// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
			add_event(false, 'person', $pers_gedcomnumber, 'event', '', '_BRTM', 'even_brit_date', $_POST["even_brit_place"], $_POST["even_brit_text"]);
		}
	}

	// Extra UPDATE queries if Bar Mitsva is displayed 
	if ($humo_option['admin_barm'] == "y") {
		if ($_POST["pers_sexe"] == "F") {
			$barmbasm = "BASM";
		} else {
			$barmbasm = "BARM";
		}
		$sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_gedcom = '" . $barmbasm . "' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
		$result = $dbh->query($sql);
		if ($result->rowCount() != 0) {     // a bar/bat mitsvah already exists for this person: UPDATE 

			if ($_POST["even_barm_date"] == '' and $_POST["even_barm_place"] == '' and $_POST["even_barm_text"] == '') {
				$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_gedcom='" . $barmbasm . "'  AND event_connect_kind='person' AND event_connect_id='" . safe_text_db($pers_gedcomnumber) . "' AND event_kind='event' ";
				$result = $dbh->query($sql);
			} else {
				//$barmDb=$result->fetch(PDO::FETCH_OBJ);
				$sql = "UPDATE humo_events SET 
				event_date='" . $editor_cls->date_process("even_barm_date") . "',
				event_place='" . safe_text_db($_POST["even_barm_place"]) . "',
				event_text='" . safe_text_db($_POST["even_barm_text"]) . "',
				event_changed_user='" . $username . "',
				event_changed_date='" . $gedcom_date . "',
				event_changed_time='" . $gedcom_time . "'
				WHERE event_tree_id='" . $tree_id . "' AND  event_gedcom='" . $barmbasm . "' AND event_connect_id='" . safe_text_db($pers_gedcomnumber) . "'";
				$doit = $dbh->query($sql);
			}
		} elseif ($_POST["even_barm_date"] != '' or $_POST["even_barm_place"] != '' or $_POST["even_barm_text"] != '') {  // new BAR/BAT MITSVA event: INSERT

			// *** Add event. If event is new, use: $new_event=true. ***
			// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
			add_event(false, 'person', $pers_gedcomnumber, 'event', '', $barmbasm, 'even_barm_date', $_POST["even_barm_place"], $_POST["even_barm_text"]);
		}
	}

	family_tree_update($tree_id);

	// *** Update cache for list of latest changes ***
	cache_latest_changes(true);
}

if (isset($_GET['add_person'])) {
	// *** Generate new GEDCOM number ***
	$new_gedcomnumber = 'I' . $db_functions->generate_gedcomnr($tree_id, 'person');
}

if (isset($_POST['person_add']) or isset($_POST['relation_add'])) {
	// *** Added new person in relation, store original person gedcomnumber ***
	if (isset($_POST['relation_add'])) {
		$man_gedcomnumber = $pers_gedcomnumber;
	}

	// *** Generate new GEDCOM number ***
	$new_gedcomnumber = 'I' . $db_functions->generate_gedcomnr($tree_id, 'person');

	// *** If person is deceased, set alive setting ***
	@$pers_alive = safe_text_db($_POST["pers_alive"]);
	if ($_POST["pers_death_date"] or $_POST["pers_death_place"] or $_POST["pers_buried_date"] or $_POST["pers_buried_place"]) {
		$pers_alive = 'deceased';
	}

	$pers_stillborn = '';
	if (isset($_POST["pers_stillborn"])) {
		$pers_stillborn = 'y';
	}

	$pers_death_cause = $_POST["pers_death_cause"];
	if (isset($_POST["pers_death_cause2"]) and $_POST["pers_death_cause2"]) $pers_death_cause = $_POST["pers_death_cause2"];

	// *** Automatically calculate birth date if death date and death age is used ***
	if ($_POST["pers_death_age"] != '' and $_POST["pers_death_date"] != '' and $_POST["pers_birth_date"] == '' and $_POST["pers_bapt_date"] == '') {
		$_POST["pers_birth_date"] = 'ABT ' . (substr($_POST["pers_death_date"], -4) - $_POST["pers_death_age"]);
	}

	// *** Process estimates/ calculated date for privacy filter ***
	$pers_cal_date = '';
	if ($_POST["pers_birth_date"]) $pers_cal_date = $_POST["pers_birth_date"];
	elseif ($_POST["pers_bapt_date"]) $pers_cal_date = $_POST["pers_bapt_date"];
	$pers_cal_date = substr($pers_cal_date, -4);

	$pers_prefix = $editor_cls->text_process($_POST["pers_prefix"]);
	$pers_prefix = str_replace(' ', '_', $pers_prefix);

	//pers_callname='".$editor_cls->text_process($_POST["pers_callname"])."',
	$sql = "INSERT INTO humo_persons SET
		pers_tree_id='" . $tree_id . "',
		pers_tree_prefix='" . $tree_prefix . "',
		pers_famc='',
		pers_fams='',
		pers_gedcomnumber='" . $new_gedcomnumber . "',
		pers_firstname='" . $editor_cls->text_process($_POST["pers_firstname"]) . "',
		pers_prefix='" . $pers_prefix . "',
		pers_lastname='" . $editor_cls->text_process($_POST["pers_lastname"]) . "',
		pers_patronym='" . $editor_cls->text_process($_POST["pers_patronym"]) . "',
		pers_name_text='" . $editor_cls->text_process($_POST["pers_name_text"]) . "',
		pers_alive='" . $pers_alive . "',
		pers_sexe='" . safe_text_db($_POST["pers_sexe"]) . "',
		pers_own_code='" . safe_text_db($_POST["pers_own_code"]) . "',
		pers_place_index='',
		pers_text='" . $editor_cls->text_process($_POST["person_text"]) . "',

		pers_birth_date='" . $editor_cls->date_process("pers_birth_date") . "',
		pers_birth_place='" . $editor_cls->text_process($_POST["pers_birth_place"]) . "',
		pers_birth_time='" . $editor_cls->text_process($_POST["pers_birth_time"]) . "',
		pers_birth_text='" . $editor_cls->text_process($_POST["pers_birth_text"], true) . "',
		pers_stillborn='" . $pers_stillborn . "',
		pers_bapt_date='" . $editor_cls->date_process("pers_bapt_date") . "',
		pers_bapt_place='" . $editor_cls->text_process($_POST["pers_bapt_place"]) . "',
		pers_bapt_text='" . $editor_cls->text_process($_POST["pers_bapt_text"], true) . "',
		pers_religion='" . safe_text_db($_POST["pers_religion"]) . "',
		pers_death_date='" . $editor_cls->date_process("pers_death_date") . "',
		pers_death_place='" . $editor_cls->text_process($_POST["pers_death_place"]) . "',
		pers_death_time='" . $editor_cls->text_process($_POST["pers_death_time"]) . "',
		pers_death_text='" . $editor_cls->text_process($_POST["pers_death_text"], true) . "',
		pers_death_cause='" . safe_text_db($pers_death_cause) . "',
		pers_death_age='" . safe_text_db($_POST["pers_death_age"]) . "',
		pers_buried_date='" . $editor_cls->date_process("pers_buried_date") . "',
		pers_buried_place='" . $editor_cls->text_process($_POST["pers_buried_place"]) . "',
		pers_buried_text='" . $editor_cls->text_process($_POST["pers_buried_text"], true) . "',";
	if ($pers_cal_date) $sql .= "pers_cal_date='" . $pers_cal_date . "',";
	$sql .= "pers_cremation='" . safe_text_db($_POST["pers_cremation"]) . "',
		pers_new_user='" . $username . "',
		pers_new_date='" . $gedcom_date . "',
		pers_new_time='" . $gedcom_time . "'";
	$result = $dbh->query($sql);

	// only needed for jewish settings
	if ($humo_option['admin_hebnight'] == "y" and isset($_POST["pers_birth_date_hebnight"])) {
		$sql = "UPDATE humo_persons SET
			pers_birth_date_hebnight='" . safe_text_db($_POST["pers_birth_date_hebnight"]) . "',
			pers_death_date_hebnight='" . safe_text_db($_POST["pers_death_date_hebnight"]) . "',
			pers_buried_date_hebnight='" . safe_text_db($_POST["pers_buried_date_hebnight"]) . "'
			WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($new_gedcomnumber) . "'";
		$result = $dbh->query($sql);
	}

	// *** New person: add profession ***
	if (isset($_POST["event_profession"]) and $_POST["event_profession"] != "" and $_POST["event_profession"] != "Profession") {
		$event_place = "";
		if (isset($_POST["event_place_profession"])) $event_place = $_POST["event_place_profession"];
		$event_text = "";
		if (isset($_POST["event_text_profession"]))  $event_text = $_POST["event_text_profession"];

		// *** Add event. If event is new, use: $new_event=true. ***
		// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
		add_event(true, 'person', $new_gedcomnumber, 'profession', $_POST["event_profession"], '', 'event_date_profession', $event_place, $event_text);
	}

	// *** New person: add religion ***
	if (isset($_POST["event_religion"]) and $_POST["event_religion"] != "" and $_POST["event_religion"] != "Religion") {
		$event_place = "";
		if (isset($_POST["event_place_religion"])) $event_place = $_POST["event_place_religion"];
		$event_text = "";
		if (isset($_POST["event_text_religion"]))  $event_text = $_POST["event_text_religion"];

		// *** Add event. If event is new, use: $new_event=true. ***
		// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
		add_event(true, 'person', $new_gedcomnumber, 'religion', $_POST["event_religion"], 'RELI', 'event_date_religion', $event_place, $event_text);
	}

	if (!isset($_POST['child_connect'])) {
		// *** Show new person ***
		$pers_gedcomnumber = $new_gedcomnumber;
		$_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;
	}

	family_tree_update($tree_id);

	// *** Add child to family, add a new child (new gedcomnumber) ***
	if (isset($_POST['child_connect'])) {
		$_POST['child_connect2'] = $new_gedcomnumber;
	}

	// *** Update cache for list of latest changes ***
	cache_latest_changes(true);
}

// *** Family move down ***
if (isset($_GET['fam_down'])) {
	$child_array_org = explode(";", safe_text_db($_GET['fam_array']));
	$child_array = $child_array_org;
	$child_array_id = safe_text_db($_GET['fam_down']);
	$child_array[$child_array_id] = $child_array_org[($child_array_id + 1)];
	$child_array[$child_array_id + 1] = $child_array_org[($child_array_id)];
	$fams = '';
	for ($k = 0; $k < count($child_array); $k++) {
		if ($k > 0) {
			$fams .= ';';
		}
		$fams .= $child_array[$k];
	}
	$sql = "UPDATE humo_persons SET
	pers_fams='" . $fams . "',
	pers_changed_user='" . $username . "',
	pers_changed_date='" . $gedcom_date . "',
	pers_changed_time='" . $gedcom_time . "'
	WHERE pers_id='" . safe_text_db($_GET["person_id"]) . "'";
	$result = $dbh->query($sql);
}

// *** Family move up ***
if (isset($_GET['fam_up'])) {
	$child_array_org = explode(";", safe_text_db($_GET['fam_array']));
	$child_array = $child_array_org;
	$child_array_id = safe_text_db($_GET['fam_up']) - 1;
	$child_array[$child_array_id + 1] = $child_array_org[($child_array_id)];
	$child_array[$child_array_id] = $child_array_org[($child_array_id + 1)];
	$fams = '';
	for ($k = 0; $k < count($child_array); $k++) {
		if ($k > 0) {
			$fams .= ';';
		}
		$fams .= $child_array[$k];
	}
	$sql = "UPDATE humo_persons SET
	pers_fams='" . $fams . "',
	pers_changed_user='" . $username . "',
	pers_changed_date='" . $gedcom_date . "',
	pers_changed_time='" . $gedcom_time . "'
	WHERE pers_id='" . safe_text_db($_GET["person_id"]) . "'";
	$result = $dbh->query($sql);
}

// *** Some functions to add and remove a fams number from a person (if marriage is changed) ***
function fams_add($personnr, $familynr)
{
	global $dbh, $db_functions, $tree_id, $gedcom_date, $gedcom_time, $username;
	// *** Add marriage to person records ***
	$person_db = $db_functions->get_person($personnr);
	if (@$person_db->pers_gedcomnumber) {
		$fams = $person_db->pers_fams;
		if ($fams) {
			$fams .= ';' . $familynr;
		} else {
			$fams = $familynr;
		}
		$sql = "UPDATE humo_persons SET
			pers_fams='" . $fams . "',
			pers_changed_user='" . $username . "',
			pers_changed_date='" . $gedcom_date . "',
			pers_changed_time='" . $gedcom_time . "'
			WHERE pers_id='" . $person_db->pers_id . "'";
		$result = $dbh->query($sql);
	}
}

function fams_remove($personnr, $familynr)
{
	global $dbh, $db_functions, $tree_id, $gedcom_date, $gedcom_time, $username;
	$person_db = $db_functions->get_person($personnr);
	if (@$person_db->pers_gedcomnumber) {
		$fams = explode(";", $person_db->pers_fams);
		foreach ($fams as $key => $value) {
			if ($fams[$key] != $familynr) {
				$fams2[] = $fams[$key];
			}
		}
		$fams3 = '';
		if (isset($fams2[0])) {
			$fams3 = implode(";", $fams2);
		}

		$sql = "UPDATE humo_persons SET
			pers_fams='" . $fams3 . "',
			pers_changed_user='" . $username . "',
			pers_changed_date='" . $gedcom_date . "',
			pers_changed_time='" . $gedcom_time . "'
			WHERE pers_id='" . $person_db->pers_id . "'";
		$result = $dbh->query($sql);
	}
}

// *** Family disconnect ***
if (isset($_GET['fam_remove']) or isset($_POST['fam_remove'])) {
	if (isset($_GET['fam_remove'])) {
		$fam_remove = safe_text_db($_GET['fam_remove']);
	};
	if (isset($_POST['marriage_nr'])) {
		$fam_remove = safe_text_db($_POST['marriage_nr']);
	};

	$new_nr = $db_functions->get_family($fam_remove);

	$confirm_relation .= '<div class="confirm">';
	if ($new_nr->fam_children) $confirm_relation .= __('If you continue, ALL children will be disconnected automatically!') . '<br>';
	$confirm_relation .= __('Are you sure to remove this mariage?');
	$confirm_relation .= ' <form method="post" action="' . $phpself . '#marriage" style="display : inline;">';
	$confirm_relation .= '<input type="hidden" name="page" value="' . $page . '">';
	$confirm_relation .= '<input type="hidden" name="fam_remove3" value="' . $fam_remove . '">';
	$confirm_relation .= ' <input type="Submit" name="fam_remove2" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
	$confirm_relation .= ' <input type="Submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
	$confirm_relation .= '</form>';
	$confirm_relation .= '</div>';
}
if (isset($_POST['fam_remove2'])) {
	$fam_remove = safe_text_db($_POST['fam_remove3']);

	// *** Remove fams number from man and woman ***
	$new_nr = $db_functions->get_family($fam_remove);

	// *** Disconnect ALL children from marriage ***
	if ($new_nr->fam_children) {
		$child_gedcomnumber = explode(";", $new_nr->fam_children);
		foreach ($child_gedcomnumber as $i => $value) {
			// *** Find child data ***
			$resultDb = $db_functions->get_person($child_gedcomnumber[$i]);

			// *** Remove parents from child record ***
			$sql = "UPDATE humo_persons SET
			pers_famc='',
			pers_changed_user='" . $username . "',
			pers_changed_date='" . $gedcom_date . "',
			pers_changed_time='" . $gedcom_time . "'
			WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($child_gedcomnumber[$i]) . "'";
			$result = $dbh->query($sql);
		}
	}

	if (isset($new_nr->fam_man)) {
		fams_remove($new_nr->fam_man, $fam_remove);
	}

	unset($fams2);
	if (isset($new_nr->fam_woman)) {
		fams_remove($new_nr->fam_woman, $fam_remove);
	}

	$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
		AND event_connect_kind='family' AND event_connect_id='" . $fam_remove . "'";
	$result = $dbh->query($sql);

	$sql = "DELETE FROM humo_addresses WHERE address_tree_id='" . $tree_id . "'
		AND address_connect_sub_kind='family'
		AND address_connect_id='" . $fam_remove . "'";
	$result = $dbh->query($sql);

	$sql = "DELETE FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $fam_remove . "'";
	$result = $dbh->query($sql);

	$sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_connect_id='" . $fam_remove . "'";
	$result = $dbh->query($sql);

	family_tree_update($tree_id);

	$confirm_relation .= '<div class="confirm">';
	$confirm_relation .= __('Marriage is removed!');
	$confirm_relation .= '</div>';

	// *** If this relation is removed, show 1st relation of person, or link to new relation ***
	$marriage = '';
	if (isset($person->pers_fams) and $person->pers_fams) {
		$fams1 = explode(";", $person->pers_fams);
		$marriage = $fams1[0];
	}
	$_POST["marriage_nr"] = $marriage;
	$_SESSION['admin_fam_gedcomnumber'] = $marriage;
}

// *** Add NEW parents to a child ***
if (isset($_POST['add_parents2'])) {
	// *** Generate new GEDCOM number ***
	$fam_gedcomnumber = 'F' . $db_functions->generate_gedcomnr($tree_id, 'family');

	// *** Generate new GEDCOM number ***
	$temp_number = $db_functions->generate_gedcomnr($tree_id, 'person');
	$man_gedcomnumber = 'I' . $temp_number;
	$woman_gedcomnumber = 'I' . ($temp_number + 1);

	$sql = "INSERT INTO humo_families SET
	fam_gedcomnumber='" . $fam_gedcomnumber . "',
	fam_tree_id='" . $tree_id . "',
	fam_kind='',
	fam_man='" . safe_text_db($man_gedcomnumber) . "',
	fam_woman='" . safe_text_db($woman_gedcomnumber) . "',
	fam_children='" . safe_text_db($pers_gedcomnumber) . "',
	fam_relation_date='', fam_relation_place='', fam_relation_text='',
	fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
	fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
	fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
	fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
	fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
	fam_text='',
	fam_new_user='" . $username . "',
	fam_new_date='" . $gedcom_date . "',
	fam_new_time='" . $gedcom_time . "'";
	$result = $dbh->query($sql);

	// only needed for jewish settings
	if ($humo_option['admin_hebnight'] == "y") {
		$sql = "UPDATE humo_families SET 
		fam_marr_notice_date_hebnight='', fam_marr_date_hebnight='', fam_marr_church_date_hebnight='', fam_marr_church_notice_date_hebnight='' 
		WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . safe_text_db($fam_gedcomnumber) . "'";
		$result = $dbh->query($sql);
	}

	// *** Add father ***
	//pers_callname='',
	$pers_alive1 = '';
	if (isset($_POST['pers_alive1'])) $pers_alive1 = safe_text_db($_POST['pers_alive1']);
	$pers_sexe1 = '';
	if (isset($_POST['pers_sexe1'])) $pers_sexe1 = safe_text_db($_POST['pers_sexe1']);
	$sql = "INSERT INTO humo_persons SET
		pers_gedcomnumber='" . $man_gedcomnumber . "',
		pers_tree_id='" . $tree_id . "',
		pers_tree_prefix='" . $tree_prefix . "',
		pers_famc='', pers_fams='" . safe_text_db($fam_gedcomnumber) . "',
		pers_firstname='" . safe_text_db($_POST['pers_firstname1']) . "',
		pers_prefix='" . safe_text_db($_POST['pers_prefix1']) . "',
		pers_lastname='" . safe_text_db($_POST['pers_lastname1']) . "',
		pers_patronym='" . safe_text_db($_POST['pers_patronym1']) . "',
		pers_name_text='',
		pers_alive='" . $pers_alive1 . "',
		pers_sexe='" . $pers_sexe1 . "',
		pers_own_code='', pers_place_index='', pers_text='',
		pers_birth_date='', pers_birth_place='', pers_birth_time='', pers_birth_text='', pers_stillborn='',
		pers_bapt_date='', pers_bapt_place='', pers_bapt_text='', pers_religion='',
		pers_death_date='', pers_death_place='', pers_death_time='', pers_death_text='', pers_death_cause='',
		pers_buried_date='', pers_buried_place='', pers_buried_text='', pers_cremation='', 
		pers_new_user='" . $username . "',
		pers_new_date='" . $gedcom_date . "',
		pers_new_time='" . $gedcom_time . "'";
	$result = $dbh->query($sql);

	// *** At this moment only event_profession1 is used. ***
	if (isset($_POST["event_profession1"]) and $_POST["event_profession1"] != "" and $_POST["event_profession1"] != "Profession") {
		$event_place = "";
		if (isset($_POST["event_place_profession1"])) $event_place = $_POST["event_place_profession1"];
		$event_text = "";
		if (isset($_POST["event_text_profession1"]))  $event_text = $_POST["event_text_profession1"];

		// *** Add event. If event is new, use: $new_event=true. ***
		// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
		add_event(true, 'person', $man_gedcomnumber, 'profession', $_POST["event_profession1"], '', '', $event_place, $event_text);
	}

	// only needed for jewish settings
	if ($humo_option['admin_hebnight'] == "y") {
		$sql = "UPDATE humo_persons SET 
		pers_birth_date_hebnight='', pers_death_date_hebnight='', pers_buried_date_hebnight='' 
		WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($man_gedcomnumber) . "'";
		$result = $dbh->query($sql);
	}

	// *** Add mother ***
	//pers_callname='',
	$pers_alive2 = '';
	if (isset($_POST['pers_alive2'])) $pers_alive2 = safe_text_db($_POST['pers_alive2']);
	$pers_sexe2 = '';
	if (isset($_POST['pers_sexe2'])) $pers_sexe2 = safe_text_db($_POST['pers_sexe2']);
	$sql = "INSERT INTO humo_persons SET
		pers_gedcomnumber='" . $woman_gedcomnumber . "',
		pers_tree_id='" . $tree_id . "',
		pers_tree_prefix='" . $tree_prefix . "',
		pers_famc='', pers_fams='" . safe_text_db($fam_gedcomnumber) . "',
		pers_firstname='" . safe_text_db($_POST['pers_firstname2']) . "',
		pers_prefix='" . safe_text_db($_POST['pers_prefix2']) . "',
		pers_lastname='" . safe_text_db($_POST['pers_lastname2']) . "',
		pers_patronym='" . safe_text_db($_POST['pers_patronym2']) . "',
		pers_name_text='',
		pers_alive='" . $pers_alive2 . "',
		pers_sexe='" . $pers_sexe2 . "',
		pers_own_code='', pers_place_index='', pers_text='',
		pers_birth_date='', pers_birth_place='', pers_birth_time='', pers_birth_text='', pers_stillborn='',
		pers_bapt_date='', pers_bapt_place='', pers_bapt_text='', pers_religion='',
		pers_death_date='', pers_death_place='', pers_death_time='', pers_death_text='', pers_death_cause='',
		pers_buried_date='', pers_buried_place='', pers_buried_text='', pers_cremation='', 
		pers_new_user='" . $username . "',
		pers_new_date='" . $gedcom_date . "',
		pers_new_time='" . $gedcom_time . "'";
	//echo $sql;
	$result = $dbh->query($sql);

	// *** At this moment only event_profession1 is used. ***
	if (isset($_POST["event_profession2"]) and $_POST["event_profession2"] != "" and $_POST["event_profession2"] != "Profession") {
		$event_place = "";
		if (isset($_POST["event_place_profession2"])) $event_place = $_POST["event_place_profession2"];
		$event_text = "";
		if (isset($_POST["event_text_profession2"]))  $event_text = $_POST["event_text_profession2"];

		// *** Add event. If event is new, use: $new_event=true. ***
		// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
		add_event(true, 'person', $woman_gedcomnumber, 'profession', $_POST["event_profession2"], '', '', $event_place, $event_text);
	}

	// only needed for jewish settings
	if ($humo_option['admin_hebnight'] == "y") {
		$sql = "UPDATE humo_persons SET 
		pers_birth_date_hebnight='', pers_death_date_hebnight='', pers_buried_date_hebnight='' 
		WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($woman_gedcomnumber) . "'";
		$result = $dbh->query($sql);
	}

	// *** Add parents to child record ***
	$sql = "UPDATE humo_persons SET
	pers_famc='" . safe_text_db($fam_gedcomnumber) . "',
	pers_changed_user='" . $username . "',
	pers_changed_date='" . $gedcom_date . "',
	pers_changed_time='" . $gedcom_time . "'
	WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($pers_gedcomnumber) . "'";
	$result = $dbh->query($sql);

	family_tree_update($tree_id);
}

// *** Add EXISTING parents to a child ***
if (isset($_POST['add_parents']) and $_POST['add_parents'] != '') {
	$parentsDb = $db_functions->get_family(strtoupper($_POST['add_parents']));

	// *** Check if manual selected family is existing family in family tree ***
	if (isset($parentsDb->fam_gedcomnumber) and strtoupper($_POST['add_parents']) == $parentsDb->fam_gedcomnumber) {
		if ($parentsDb->fam_children) {
			$fam_children = $parentsDb->fam_children . ';' . $pers_gedcomnumber;
		} else {
			$fam_children = $pers_gedcomnumber;
		}

		$sql = "UPDATE humo_families SET
		fam_children='" . $fam_children . "',
		fam_changed_user='" . $username . "',
		fam_changed_date='" . $gedcom_date . "',
		fam_changed_time='" . $gedcom_time . "'
		WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $parentsDb->fam_gedcomnumber . "'";
		$result = $dbh->query($sql);

		// *** Add parents to child record ***
		$sql = "UPDATE humo_persons SET
		pers_famc='" . $parentsDb->fam_gedcomnumber . "',
		pers_changed_user='" . $username . "',
		pers_changed_date='" . $gedcom_date . "',
		pers_changed_time='" . $gedcom_time . "'
		WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $pers_gedcomnumber . "'";
		$result = $dbh->query($sql);

		family_tree_update($tree_id);

		$confirm .= '<div class="confirm">';
		$confirm .= __('Parents are selected!');
		$confirm .= '</div>';
	} else {
		$confirm .= '<div class="confirm">';
		$confirm .= __('Manual selected family isn\'t an existing family in the family tree!');
		$confirm .= '</div>';
	}
}

// *** Add child to family ***
if (isset($_POST['child_connect2']) and $_POST['child_connect2'] and !isset($_POST['submit'])) {

	// *** Check valid gedcomnumber and check if child already has parents connected! ***
	$resultDb = $db_functions->get_person($_POST["child_connect2"]);

	// *** Check if input is a valid gedcomnumber ***
	if (isset($resultDb->pers_gedcomnumber)) {

		if ($resultDb->pers_famc  and !isset($_POST['child_connecting'])) {
			$confirm .= '<div class="confirm">';
			$confirm .= __('Child already has parents connected! Are you sure you want to connect this child?');
			$confirm .= ' <form method="post" action="' . $phpself . '" style="display : inline;">';
			$confirm .= '<input type="hidden" name="page" value="editor">';
			$confirm .= '<input type="hidden" name="family_id" value="' . $_POST['family_id'] . '">';
			$confirm .= '<input type="hidden" name="children" value="' . $_POST['children'] . '">';
			$confirm .= '<input type="hidden" name="child_connect2" value="' . $_POST['child_connect2'] . '">';
			$confirm .= ' <input type="Submit" name="child_connecting" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
			$confirm .= ' <input type="Submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
			$confirm .= '</form>';
			$confirm .= '</div>';
		} else {

			// *** First check if person already was connected to other parents: remove this person as child from this family. ***
			if ($resultDb->pers_famc) {
				$famDb = $db_functions->get_family($resultDb->pers_famc);
				$fam_children = explode(";", $famDb->fam_children);
				foreach ($fam_children as $key => $value) {
					if ($fam_children[$key] != $_POST["child_connect2"]) {
						$fam_children2[] = $fam_children[$key];
					}
				}
				$fam_children3 = '';
				if (isset($fam_children2[0])) {
					$fam_children3 = implode(";", $fam_children2);
				}

				$sql = "UPDATE humo_families SET
					fam_children='" . $fam_children3 . "'
					WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $resultDb->pers_famc . "'";
				$result = $dbh->query($sql);
			}

			// *** Change i10 into I10 ***
			$_POST["child_connect2"] = ucfirst($_POST["child_connect2"]);
			// *** Change entry "48" into "I48" ***
			if (substr($_POST["child_connect2"], 0, 1) != "I") {
				$_POST["child_connect2"] = "I" . $_POST["child_connect2"];
			}

			if (isset($_POST["children"]) and $_POST["children"]) {
				$sql = "UPDATE humo_families SET
				fam_children='" . safe_text_db($_POST["children"]) . ';' . safe_text_db($_POST["child_connect2"]) . "',
				fam_changed_user='" . $username . "',
				fam_changed_date='" . $gedcom_date . "',
				fam_changed_time='" . $gedcom_time . "'
				WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['family_id']) . "'";
			} else {
				$sql = "UPDATE humo_families SET
				fam_children='" . safe_text_db($_POST["child_connect2"]) . "',
				fam_changed_user='" . $username . "',
				fam_changed_date='" . $gedcom_date . "',
				fam_changed_time='" . $gedcom_time . "'
				WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['family_id']) . "'";
			}
			//echo $sql;
			$result = $dbh->query($sql);

			// *** Add parents to child record ***
			$sql = "UPDATE humo_persons SET
			pers_famc='" . safe_text_db($_POST['family_id']) . "',
			pers_changed_user='" . $username . "',
			pers_changed_date='" . $gedcom_date . "',
			pers_changed_time='" . $gedcom_time . "'
			WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($_POST["child_connect2"]) . "'";
			$result = $dbh->query($sql);

			family_tree_update($tree_id);
		}
	}
}

// *** Disconnect child ***
if (isset($_GET['child_disconnect'])) {
	$confirm .= '<div class="confirm">';
	$confirm .= __('Are you sure you want to disconnect this child?');
	$confirm .= ' <form method="post" action="' . $phpself . '" style="display : inline;">';
	$confirm .= '<input type="hidden" name="page" value="' . $_GET['page'] . '">';
	$confirm .= '<input type="hidden" name="family_id" value="' . $_GET['family_id'] . '">';
	$confirm .= '<input type="hidden" name="child_disconnect2" value="' . $_GET['child_disconnect'] . '">';
	$confirm .= '<input type="hidden" name="child_disconnect_gedcom" value="' . $_GET['child_disconnect_gedcom'] . '">';
	$confirm .= ' <input type="Submit" name="child_disconnecting" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
	$confirm .= ' <input type="Submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
	$confirm .= '</form>';
	$confirm .= '</div>';
}
if (isset($_POST['child_disconnecting'])) {
	$sql = "UPDATE humo_families SET
		fam_children='" . safe_text_db($_POST["child_disconnect2"]) . "',
		fam_changed_user='" . $username . "',
		fam_changed_date='" . $gedcom_date . "',
		fam_changed_time='" . $gedcom_time . "'
		WHERE fam_id='" . safe_text_db($_POST["family_id"]) . "'";
	$result = $dbh->query($sql);

	// *** Remove parents from child record ***
	$sql = "UPDATE humo_persons SET
		pers_famc='',
		pers_changed_user='" . $username . "',
		pers_changed_date='" . $gedcom_date . "',
		pers_changed_time='" . $gedcom_time . "'
		WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($_POST["child_disconnect_gedcom"]) . "'";
	$result = $dbh->query($sql);
}

// *** Child move down ***
if (isset($_GET['child_down'])) {
	$child_array_org = explode(";", safe_text_db($_GET['child_array']));
	$child_array = $child_array_org;
	$child_array_id = safe_text_db($_GET['child_down']);
	$child_array[$child_array_id] = $child_array_org[($child_array_id + 1)];
	$child_array[$child_array_id + 1] = $child_array_org[($child_array_id)];
	$fam_children = '';
	// use implode: $fam_children = implode(";", $child_array);
	for ($k = 0; $k < count($child_array); $k++) {
		if ($k > 0) {
			$fam_children .= ';';
		}
		$fam_children .= $child_array[$k];
	}
	$sql = "UPDATE humo_families SET
	fam_children='" . $fam_children . "',
	fam_changed_user='" . $username . "',
	fam_changed_date='" . $gedcom_date . "',
	fam_changed_time='" . $gedcom_time . "'
	WHERE fam_id='" . safe_text_db($_GET["family_id"]) . "'";
	$result = $dbh->query($sql);
}

// *** Child move up ***
if (isset($_GET['child_up'])) {
	$child_array_org = explode(";", safe_text_db($_GET['child_array']));
	$child_array = $child_array_org;
	$child_array_id = safe_text_db($_GET['child_up']) - 1;
	$child_array[$child_array_id + 1] = $child_array_org[($child_array_id)];
	$child_array[$child_array_id] = $child_array_org[($child_array_id + 1)];
	$fam_children = '';
	// use implode: $fam_children = implode(";", $child_array);
	for ($k = 0; $k < count($child_array); $k++) {
		if ($k > 0) {
			$fam_children .= ';';
		}
		$fam_children .= $child_array[$k];
	}
	$sql = "UPDATE humo_families SET
	fam_children='" . $fam_children . "',
	fam_changed_user='" . $username . "',
	fam_changed_date='" . $gedcom_date . "',
	fam_changed_time='" . $gedcom_time . "'
	WHERE fam_id='" . safe_text_db($_GET["family_id"]) . "'";
	$result = $dbh->query($sql);
}


// ***************************
// *** PROCESS DATA FAMILY ***
// ***************************


// *** Add new family with new partner ***
if (isset($_POST['relation_add'])) {
	// *** Generate new GEDCOM number ***
	$fam_gedcomnumber = 'F' . $db_functions->generate_gedcomnr($tree_id, 'family');

	// *** Directly show new marriage on screen ***
	$_POST["marriage_nr"] = $fam_gedcomnumber;
	$marriage = $fam_gedcomnumber;
	$_SESSION['admin_fam_gedcomnumber'] = $marriage;

	// *** Generate new GEDCOM number ***
	//$partner_gedcomnumber='I'.$db_functions->generate_gedcomnr($tree_id,'person');
	$partner_gedcomnumber = $pers_gedcomnumber;
	$pers_gedcomnumber = $man_gedcomnumber;

	$person_db = $db_functions->get_person($pers_gedcomnumber);
	if ($person_db->pers_sexe == 'M') {
		$man_gedcomnumber = $pers_gedcomnumber;
		$woman_gedcomnumber = $partner_gedcomnumber;
		$sexe = 'F';
	} else {
		$man_gedcomnumber = $partner_gedcomnumber;
		$woman_gedcomnumber = $pers_gedcomnumber;
		$sexe = 'M';
	}

	$sql = "INSERT INTO humo_families SET
	fam_tree_id='" . $tree_id . "',
	fam_gedcomnumber='" . $fam_gedcomnumber . "', fam_kind='',
	fam_man='" . safe_text_db($man_gedcomnumber) . "', fam_woman='" . safe_text_db($woman_gedcomnumber) . "',
	fam_children='',
	fam_relation_date='', fam_relation_place='', fam_relation_text='',
	fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
	fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
	fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
	fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
	fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
	fam_text='',
	fam_new_user='" . $username . "',
	fam_new_date='" . $gedcom_date . "',
	fam_new_time='" . $gedcom_time . "'";
	//echo $sql;
	$result = $dbh->query($sql);

	// only needed for jewish settings
	if ($humo_option['admin_hebnight'] == "y") {
		$sql = "UPDATE humo_families SET 
		fam_marr_notice_date_hebnight='', fam_marr_date_hebnight='', fam_marr_church_date_hebnight='', fam_marr_church_notice_date_hebnight=''  
		WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . safe_text_db($fam_gedcomnumber) . "'";
		$result = $dbh->query($sql);
	}

	// *** Add N.N. partner ***
	/*
	//pers_callname='',
	$sql="INSERT INTO humo_persons SET
		pers_gedcomnumber='".$partner_gedcomnumber."',
		pers_tree_id='".$tree_id."',
		pers_tree_prefix='".$tree_prefix."',
		pers_famc='', pers_fams='".safe_text_db($fam_gedcomnumber)."',
		pers_firstname='".__('N.N.')."', pers_prefix='', pers_lastname='', pers_patronym='', pers_name_text='',
		pers_alive='alive', pers_sexe='".$sexe."', pers_own_code='', pers_place_index='', pers_text='',
		pers_birth_date='', pers_birth_place='', pers_birth_time='', pers_birth_text='', pers_stillborn='',
		pers_bapt_date='', pers_bapt_place='', pers_bapt_text='', pers_religion='',
		pers_death_date='', pers_death_place='', pers_death_time='', pers_death_text='', pers_death_cause='',
		pers_buried_date='', pers_buried_place='', pers_buried_text='', pers_cremation='',
		pers_new_user='".$username."',
		pers_new_date='".$gedcom_date."',
		pers_new_time='".$gedcom_time."'";
	//echo $sql.'<br>';
	$result=$dbh->query($sql);
	*/
	// *** Update fams for new added partner ***
	$sql = "UPDATE humo_persons SET
		pers_famc='', pers_fams='" . safe_text_db($fam_gedcomnumber) . "'
		WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($partner_gedcomnumber) . "'";
	$result = $dbh->query($sql);

	// extra UPDATE queries if jewish dates enabled
	if ($humo_option['admin_hebnight'] == "y") {
		$sql = "UPDATE humo_persons SET 
			pers_birth_date_hebnight='', pers_death_date_hebnight='', pers_buried_date_hebnight='' 
			WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($partner_gedcomnumber) . "'";
		$result = $dbh->query($sql);
	}

	// *** Add marriage to person records MAN and WOMAN ***
	fams_add($pers_gedcomnumber, $fam_gedcomnumber);

	family_tree_update($tree_id);
}

// *** Add new family with selected partner ***
if (isset($_POST['relation_add2']) and $_POST['relation_add2'] != '') {
	// *** Change i10 into I10 ***
	$_POST['relation_add2'] = ucfirst($_POST['relation_add2']);
	// *** Change entry "48" into "I48" ***
	if (substr($_POST['relation_add2'], 0, 1) != "I") {
		$_POST['relation_add2'] = "I" . $_POST['relation_add2'];
	}

	// *** Generate new GEDCOM number ***
	$fam_gedcomnumber = 'F' . $db_functions->generate_gedcomnr($tree_id, 'family');

	// *** Directly show new marriage on screen ***
	$_POST["marriage_nr"] = $fam_gedcomnumber;
	$marriage = $fam_gedcomnumber;
	$_SESSION['admin_fam_gedcomnumber'] = $marriage;

	$person_db = $db_functions->get_person($pers_gedcomnumber);
	if ($person_db->pers_sexe == 'M') {
		$man_gedcomnumber = $pers_gedcomnumber;
		$woman_gedcomnumber = $_POST['relation_add2'];
		$sexe = 'F';
	} else {
		$man_gedcomnumber = $_POST['relation_add2'];
		$woman_gedcomnumber = $pers_gedcomnumber;
		$sexe = 'M';
	}

	$sql = "INSERT INTO humo_families SET
	fam_tree_id='" . $tree_id . "',
	fam_gedcomnumber='" . $fam_gedcomnumber . "', fam_kind='',
	fam_man='" . safe_text_db($man_gedcomnumber) . "', fam_woman='" . safe_text_db($woman_gedcomnumber) . "',
	fam_children='',
	fam_relation_date='', fam_relation_place='', fam_relation_text='',
	fam_marr_notice_date='', fam_marr_notice_place='', fam_marr_notice_text='',
	fam_marr_date='', fam_marr_place='', fam_marr_text='', fam_marr_authority='',
	fam_marr_church_date='', fam_marr_church_place='', fam_marr_church_text='',
	fam_marr_church_notice_date='', fam_marr_church_notice_place='', fam_marr_church_notice_text='', fam_religion='',
	fam_div_date='', fam_div_place='', fam_div_text='', fam_div_authority='',
	fam_text='',
	fam_new_user='" . $username . "',
	fam_new_date='" . $gedcom_date . "',
	fam_new_time='" . $gedcom_time . "'";
	//echo $sql.'<br>';
	$result = $dbh->query($sql);

	// only needed for jewish settings
	if ($humo_option['admin_hebnight'] == "y") {
		$sql = "UPDATE humo_families SET 
		fam_marr_notice_date_hebnight='', fam_marr_date_hebnight='', fam_marr_church_date_hebnight='', fam_marr_church_notice_date_hebnight='' 
		WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . safe_text_db($fam_gedcomnumber) . "'";
		$result = $dbh->query($sql);
	}

	// *** Add marriage to person records MAN and WOMAN ***
	fams_add($man_gedcomnumber, $fam_gedcomnumber);
	fams_add($woman_gedcomnumber, $fam_gedcomnumber);

	family_tree_update($tree_id);
}


//if ($menu_admin=='marriage' AND $person->pers_fams){
// *** Switch parents ***
if (isset($_POST['parents_switch'])) {
	$sql = "UPDATE humo_families SET
		fam_man='" . safe_text_db($_POST["connect_woman"]) . "',
		fam_woman='" . safe_text_db($_POST["connect_man"]) . "'
		WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['marriage']) . "'";
	$result = $dbh->query($sql);

	// *** Empty search boxes if a switch is made ***
	$_POST['search_quicksearch_woman'] = '';
	$_POST['search_quicksearch_man'] = '';
}


// *** Also save relation data if witnesses are added ***
$save_relation_data = false;
if (isset($_POST['marriage_change'])) $save_relation_data = true;
if (isset($_POST['add_marriage_witness'])) $save_relation_data = true;
if (isset($_POST['add_marriage_witness_rel'])) $save_relation_data = true;

// *** Also save relation data if addresses are added ***
if (isset($_POST['relation_add_address'])) $save_relation_data = true;

// *** Also save relation data if addresses are added ***
if (isset($_POST['add_marriage_picture'])) $save_relation_data = true;
if (isset($_POST['relation_add_media'])) $save_relation_data = true;

// ** Change relation ***
//if (isset($_POST['marriage_change'])) {
if ($save_relation_data == true) {
	// *** Change i10 into I10 ***
	$_POST["connect_man"] = ucfirst($_POST["connect_man"]);
	$_POST["connect_woman"] = ucfirst($_POST["connect_woman"]);

	// *** Man is changed in marriage ***
	if ($_POST["connect_man"] != $_POST["connect_man_old"]) {
		fams_remove($_POST['connect_man_old'], $_POST['marriage']);
		fams_add($_POST['connect_man'], $_POST['marriage']);
	}
	// *** Woman is changed in marriage ***
	if ($_POST["connect_woman"] != $_POST["connect_woman_old"]) {
		fams_remove($_POST['connect_woman_old'], $_POST['marriage']);
		fams_add($_POST['connect_woman'], $_POST['marriage']);
	}

	$fam_div_text = '';
	if (isset($_POST['fam_div_no_data'])) $fam_div_text = 'DIVORCE';
	if ($_POST["fam_div_text"]) $fam_div_text = $_POST["fam_div_text"];

	$sql = "UPDATE humo_families SET
		fam_kind='" . safe_text_db($_POST["fam_kind"]) . "',
		fam_man='" . safe_text_db($_POST["connect_man"]) . "',
		fam_woman='" . safe_text_db($_POST["connect_woman"]) . "',
		fam_relation_date='" . $editor_cls->date_process("fam_relation_date") . "',
		fam_relation_end_date='" . $editor_cls->date_process("fam_relation_end_date") . "',
		fam_relation_place='" . $editor_cls->text_process($_POST["fam_relation_place"]) . "',
		fam_relation_text='" . $editor_cls->text_process($_POST["fam_relation_text"], true) . "',
		fam_man_age='" . safe_text_db($_POST["fam_man_age"]) . "',
		fam_woman_age='" . safe_text_db($_POST["fam_woman_age"]) . "',
		fam_marr_notice_date='" . $editor_cls->date_process("fam_marr_notice_date") . "',
		fam_marr_notice_place='" . $editor_cls->text_process($_POST["fam_marr_notice_place"]) . "',
		fam_marr_notice_text='" . $editor_cls->text_process($_POST["fam_marr_notice_text"], true) . "',
		fam_marr_date='" . $editor_cls->date_process("fam_marr_date") . "',
		fam_marr_place='" . $editor_cls->text_process($_POST["fam_marr_place"]) . "',
		fam_marr_text='" . $editor_cls->text_process($_POST["fam_marr_text"], true) . "',
		fam_marr_authority='" . safe_text_db($_POST["fam_marr_authority"]) . "',
		fam_marr_church_date='" . $editor_cls->date_process("fam_marr_church_date") . "',
		fam_marr_church_place='" . $editor_cls->text_process($_POST["fam_marr_church_place"]) . "',
		fam_marr_church_text='" . $editor_cls->text_process($_POST["fam_marr_church_text"], true) . "',
		fam_marr_church_notice_date='" . $editor_cls->date_process("fam_marr_church_notice_date") . "',
		fam_marr_church_notice_place='" . $editor_cls->text_process($_POST["fam_marr_church_notice_place"]) . "',
		fam_marr_church_notice_text='" . $editor_cls->text_process($_POST["fam_marr_church_notice_text"], true) . "',
		fam_religion='" . safe_text_db($_POST["fam_religion"]) . "',
		fam_div_date='" . $editor_cls->date_process("fam_div_date") . "',
		fam_div_place='" . $editor_cls->text_process($_POST["fam_div_place"]) . "',
		fam_div_text='" . $editor_cls->text_process($fam_div_text, true) . "',
		fam_div_authority='" . safe_text_db($_POST["fam_div_authority"]) . "',
		fam_text='" . $editor_cls->text_process($_POST["fam_text"], true) . "',
		fam_changed_user='" . $username . "',
		fam_changed_date='" . $gedcom_date . "',
		fam_changed_time='" . $gedcom_time . "'
		WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['marriage']) . "'";
	$result = $dbh->query($sql);

	// only needed for jewish settings
	if ($humo_option['admin_hebnight'] == "y") {
		$f_m_n_d_h = "";
		$f_m_d_h = "";
		$f_m_c_d_h = "";
		$f_m_c_n_d_h = "";
		if (isset($_POST["fam_marr_notice_date_hebnight"]))  $f_m_n_d_h = $_POST["fam_marr_notice_date_hebnight"];
		if (isset($_POST["fam_marr_date_hebnight"]))  $f_m_d_h = $_POST["fam_marr_date_hebnight"];
		if (isset($_POST["fam_marr_church_date_hebnight"]))  $f_m_c_d_h = $_POST["fam_marr_church_date_hebnight"];
		if (isset($_POST["fam_marr_church_notice_date_hebnight"]))  $f_m_c_n_d_h = $_POST["fam_marr_church_notice_date_hebnight"];
		$sql = "UPDATE humo_families SET 
			fam_marr_notice_date_hebnight='" . $editor_cls->text_process($f_m_n_d_h) . "', 
			fam_marr_date_hebnight='" . $editor_cls->text_process($f_m_d_h) . "', 
			fam_marr_church_date_hebnight='" . $editor_cls->text_process($f_m_c_d_h) . "', 
			fam_marr_church_notice_date_hebnight='" . $editor_cls->text_process($f_m_c_n_d_h) . "' 
			WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . safe_text_db($_POST['marriage']) . "'";
		$result = $dbh->query($sql);
	}

	family_tree_update($tree_id);
}



// **************************
// *** PROCESS DATA EVENT ***
// **************************


// *** Add new event ***
//if (isset($_GET['event_add']) AND !isset($_GET['add_person'])){
$new_event = false;
if (!isset($_GET['add_person'])) {
	if (isset($_GET['event_add'])) {
		$new_event = true;
		$event_add = $_GET['event_add'];
	}

	// *** Add Nickname ***
	if (isset($_POST['event_add_name'])) {
		$new_event = true;
		$event_add = 'add_name';
	}
	// *** If "Save" is clicked, also save event names ***
	if (isset($_POST['event_event_name']) and $_POST['event_event_name'] != '') {
		$new_event = true;
		$event_add = 'add_name';
	}

	// *** April 2023: using POST so it's possible to save person data if event is added ***
	if (isset($_POST['add_birth_declaration'])) {
		$new_event = true;
		$event_add = 'add_birth_declaration';
	}
	if (isset($_POST['add_baptism_witness'])) {
		$new_event = true;
		$event_add = 'add_baptism_witness';
	}
	if (isset($_POST['add_death_declaration'])) {
		$new_event = true;
		$event_add = 'add_death_declaration';
	}
	if (isset($_POST['add_burial_witness'])) {
		$new_event = true;
		$event_add = 'add_burial_witness';
	}
	if (isset($_POST['add_marriage_witness'])) {
		$new_event = true;
		$event_add = 'add_marriage_witness';
	}
	if (isset($_POST['add_marriage_witness_rel'])) {
		$new_event = true;
		$event_add = 'add_marriage_witness_rel';
	}

	// *** Add profession ***
	if (isset($_POST['event_add_profession'])) {
		$new_event = true;
		$event_add = 'add_profession';
	}
	// *** If "Save" is clicked, also save event names ***
	if (isset($_POST['event_event_profession']) and $_POST['event_event_profession'] != '') {
		$new_event = true;
		$event_add = 'add_profession';
	}

	// *** Add religion ***
	if (isset($_POST['event_add_religion'])) {
		$new_event = true;
		$event_add = 'add_religion';
	}
	// *** If "Save" is clicked, also save event names ***
	if (isset($_POST['event_event_religion']) and $_POST['event_event_religion'] != '') {
		$new_event = true;
		$event_add = 'add_religion';
	}

	// *** Add picture ***
	if (isset($_POST['add_picture'])) {
		$new_event = true;
		$event_add = 'add_picture';
	}
	if (isset($_POST['add_marriage_picture'])) {
		$new_event = true;
		$event_add = 'add_marriage_picture';
	}
	if (isset($_POST['add_source_picture'])) {
		$new_event = true;
		$event_add = 'add_source_picture';
	}
}
if ($new_event) {
	if (isset($_POST['marriage'])) $marriage = $_POST['marriage']; // *** Needed to check $_POST for multiple relations ***

	if ($event_add == 'add_name') {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
		$event_kind = 'name';

		if ($_POST['event_gedcom_add'] == 'NPFX') $event_kind = 'NPFX';
		if ($_POST['event_gedcom_add'] == 'NSFX') $event_kind = 'NSFX';
		if ($_POST['event_gedcom_add'] == 'nobility') $event_kind = 'nobility';
		if ($_POST['event_gedcom_add'] == 'title') $event_kind = 'title';
		if ($_POST['event_gedcom_add'] == 'lordship') $event_kind = 'lordship';

		$event_event = $_POST['event_event_name'];
		$event_gedcom = $_POST['event_gedcom_add'];
	}

	if ($event_add == 'add_birth_declaration') {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
		$event_kind = 'birth_declaration';
		$event_event = '';
		$event_gedcom = '';
	}
	if ($event_add == 'add_baptism_witness') {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
		$event_kind = 'baptism_witness';
		$event_event = '';
		$event_gedcom = '';
	}
	if ($event_add == 'add_death_declaration') {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
		$event_kind = 'death_declaration';
		$event_event = '';
		$event_gedcom = '';
	}
	if ($event_add == 'add_burial_witness') {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
		$event_kind = 'burial_witness';
		$event_event = '';
		$event_gedcom = '';
	}
	if ($event_add == 'add_marriage_witness') {
		$event_connect_kind = 'family';
		$event_connect_id = $marriage;
		$event_kind = 'marriage_witness';
		$event_event = '';
		$event_gedcom = '';
	}
	if ($event_add == 'add_marriage_witness_rel') {
		$event_connect_kind = 'family';
		$event_connect_id = $marriage;
		$event_kind = 'marriage_witness_rel';
		$event_event = '';
		$event_gedcom = '';
	}

	if ($event_add == 'add_profession') {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
		$event_kind = 'profession';
		$event_gedcom = '';
		$event_event = $_POST['event_event_profession'];
	}

	if ($event_add == 'add_religion') {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
		$event_kind = 'religion';
		$event_gedcom = 'RELI';
		$event_event = $_POST['event_event_religion'];
	}

	// *** Picture by person ***
	if ($event_add == 'add_picture') {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
		$event_kind = 'picture';
		$event_event = '';
		$event_gedcom = '';
	}
	// *** Picture by relation ***
	if ($event_add == 'add_marriage_picture') {
		$event_connect_kind = 'family';
		$event_connect_id = $marriage;
		$event_kind = 'picture';
		$event_event = '';
		$event_gedcom = '';
	}
	// *** Picture by source ***
	if ($event_add == 'add_source_picture') {
		//$event_connect_kind='source'; $event_connect_id=$_GET['source_id']; $event_kind='picture'; $event_event=''; $event_gedcom='';
		$event_connect_kind = 'source';
		$event_connect_id = $_POST['source_gedcomnr'];
		$event_kind = 'picture';
		$event_event = '';
		$event_gedcom = '';
	}

	// *** Add event. If event is new, use: $new_event=true. ***
	// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
	add_event(false, $event_connect_kind, $event_connect_id, $event_kind, $event_event, $event_gedcom, '', '', '');
}

// *** Add person event ***
if (isset($_POST['person_event_add'])) {
	// *** Add event. If event is new, use: $new_event=true. ***
	// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
	add_event(false, 'person', $pers_gedcomnumber, $_POST["event_kind"], '', '', '', '', '');
}

// *** Add marriage event ***
if (isset($_POST['marriage_event_add'])) {
	// *** Add event. If event is new, use: $new_event=true. ***
	// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
	//add_event(false,'family',$marriage,$_POST["event_kind"],'','','','','');
	add_event(false, 'family', safe_text_db($_POST['marriage']), $_POST["event_kind"], '', '', '', '', '');
}


// *** Upload images ***
if (isset($_FILES['photo_upload']) and $_FILES['photo_upload']['name']) {

	// *** get path of pictures folder 
	$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='" . $tree_prefix . "'");
	$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
	$tree_pict_path = $dataDb->tree_pict_path;
	if (substr($tree_pict_path, 0, 1) == '|') $tree_pict_path = 'media/';
	$dir = $path_prefix . $tree_pict_path;

	// check if this is a category file (file with existing category prefix) and if a subfolder for this category exists, place it there.
	$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
	if ($temp->rowCount()) {  // there is a category table
		$catgry = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
		if ($catgry->rowCount()) {
			while ($catDb = $catgry->fetch(PDO::FETCH_OBJ)) {
				if (
					is_dir($dir . substr($_FILES['photo_upload']['name'], 0, 2)) and
					substr($_FILES['photo_upload']['name'], 0, 3) == $catDb->photocat_prefix
				) {   // there is a subfolder of this prefix
					$dir = $dir . substr($_FILES['photo_upload']['name'], 0, 2) . '/';  // place uploaded file in that subfolder
				}
			}
		}
	}

	if ($_FILES['photo_upload']['type'] == "image/pjpeg" || $_FILES['photo_upload']['type'] == "image/jpeg") {
		$fault = "";

		// *** feb. 2023: removed limits ***
		// 100000=100kb.
		//if($_FILES['photo_upload']['size']>2000000){ $fault=__('Photo too large'); }

		if (!$fault) {
			$picture_original = $dir . $_FILES['photo_upload']['name'];
			$picture_original_tmp = $dir . '0_temp.jpg';
			$picture_thumb = $dir . 'thumb_' . $_FILES['photo_upload']['name'];
			if (!move_uploaded_file($_FILES['photo_upload']['tmp_name'], $picture_original)) {
				echo __('Photo upload failed, check folder rights');
			} else {
				// *** Resize uploaded picture and create thumbnail ***
				if (strtolower(substr($picture_original, -3)) == "jpg") {
					// *** Get width and height of original file ***
					list($width, $height) = getimagesize($picture_original);

					// *** If filesize > 2MB: resize image to: height 720px/ width 1280px or: width 1920px/ height 1080px ***
					if ($_FILES['photo_upload']['size'] > 2000000) {
						if ($height > 1080) {
							// *** First rename original file to temp. file name ***
							rename($picture_original, $picture_original_tmp);

							$newheight = 1080;
							$factor = $height / $newheight;
							$newwidth = $width / $factor;

							$resize_media = imagecreatetruecolor($newwidth, $newheight);
							$source = imagecreatefromjpeg($picture_original_tmp);

							// *** Resize ***
							imagecopyresized($resize_media, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
							@imagejpeg($resize_media, $picture_original);

							// *** Remove temp. file ***
							unlink($picture_original_tmp);

							// *** Recalculate width and height of file, needed for thumbnail ***
							list($width, $height) = getimagesize($picture_original);
						}
					}

					$create_thumb_height = 120;
					$newheight = $create_thumb_height;
					$factor = $height / $newheight;
					$newwidth = $width / $factor;

					$create_thumb = imagecreatetruecolor($newwidth, $newheight);
					$source = imagecreatefromjpeg($picture_original);

					// Resize
					imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
					@imagejpeg($create_thumb, $picture_thumb);
				}

				// *** Add picture to array ***
				$picture_array[] = $_FILES['photo_upload']['name'];

				// *** Re-order pictures by alphabet ***
				@sort($picture_array);
				$nr_pictures = count($picture_array);

				// *** Directly connect new media to person or relation ***
				if (isset($_POST['person_add_media'])) {
					$event_connect_kind = 'person';
					$event_connect_id = $pers_gedcomnumber;
					$event_kind = 'picture';
					$event_event = $_FILES['photo_upload']['name'];
					$event_gedcom = '';
				}
				if (isset($_POST['relation_add_media'])) {
					$event_connect_kind = 'family';
					$event_connect_id = $marriage;
					$event_kind = 'picture';
					$event_event = $_FILES['photo_upload']['name'];
					$event_gedcom = '';
				}
				// *** Add event. If event is new, use: $new_event=true. ***
				// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
				add_event(false, $event_connect_kind, $event_connect_id, $event_kind, $event_event, $event_gedcom, '', '', '');
			}
		} else {
			print "<FONT COLOR=red>$fault</FONT>";
		}
	} elseif (
		$_FILES['photo_upload']['type'] == "audio/mpeg" || $_FILES['photo_upload']['type'] == "audio/mpeg3" ||
		$_FILES['photo_upload']['type'] == "audio/x-mpeg" || $_FILES['photo_upload']['type'] == "audio/x-mpeg3" ||
		$_FILES['photo_upload']['type'] == "audio/mpg" || $_FILES['photo_upload']['type'] == "audio/mp3" ||
		$_FILES['photo_upload']['type'] == "audio/mid" || $_FILES['photo_upload']['type'] == "audio/midi" ||
		$_FILES['photo_upload']['type'] == "audio/x-midi" || $_FILES['photo_upload']['type'] == "audio/x-ms-wma" ||
		$_FILES['photo_upload']['type'] == "audio/wav" || $_FILES['photo_upload']['type'] == "audio/x-wav" ||
		$_FILES['photo_upload']['type'] == "audio/x-pn-realaudio" || $_FILES['photo_upload']['type'] == "audio/x-realaudio" ||
		$_FILES['photo_upload']['type'] == "application/pdf" || $_FILES['photo_upload']['type'] == "application/msword" ||
		$_FILES['photo_upload']['type'] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document" ||
		$_FILES['photo_upload']['type'] == "video/quicktime" || $_FILES['photo_upload']['type'] == "video/x-ms-wmv" ||
		$_FILES['photo_upload']['type'] == "video/avi" || $_FILES['photo_upload']['type'] == "video/x-msvideo" ||
		$_FILES['photo_upload']['type'] == "video/msvideo" || $_FILES['photo_upload']['type'] == "video/mpeg" ||
		$_FILES['photo_upload']['type'] == "video/msvideo" || $_FILES['photo_upload']['type'] == "video/mp4"
	) {
		$fault = "";

		// *** feb. 2023: removed limits ***
		// *** Limit to 49MB ***
		//if($_FILES['photo_upload']['size']>49000000){ $fault=__('Media too large'); }

		if (!$fault) {
			$picture_original = $dir . $_FILES['photo_upload']['name'];
			if (!move_uploaded_file($_FILES['photo_upload']['tmp_name'], $picture_original)) {
				echo __('Media upload failed, check folder rights');
			} else {
				// *** Add picture to array ***
				$picture_array[] = $_FILES['photo_upload']['name'];

				// *** Re-order pictures by alphabet ***
				@sort($picture_array);
				$nr_pictures = count($picture_array);
			}
		} else {
			echo '<font color="red">' . $fault . '</font>';
		}
	} else {
		echo '<font color="red">' . __('No valid picture, media or document file') . '</font>';
	}
}


// *** Change event ***
//also check is_numeric
if (isset($_POST['event_id'])) {
	foreach ($_POST['event_id'] as $key => $value) {
		$event_event = '';
		if (isset($_POST["text_event"][$key]))
			$event_event = $editor_cls->text_process($_POST["text_event"][$key]);

		// *** Replaced array function, because witness popup javascript doesn't work using an html-form-array ***
		if (isset($_POST["text_event2" . $key]) and $_POST["text_event2" . $key] != '') {
			$event_event = '@' . $_POST["text_event2" . $key] . '@';
		}

		// *** Media selection pop-up option *** 
		if (isset($_POST["text_event" . $key]) and $_POST["text_event" . $key] != '') {
			$event_event = $editor_cls->text_process($_POST["text_event" . $key]);
		}

		// *** Only update if there are changed values! Otherwise all event_change variables will be changed... ***
		$event_id = $_POST["event_id"][$key];
		if (is_numeric($event_id)) {
			// *** Read old values ***
			$event_qry = "SELECT * FROM humo_events WHERE event_id='" . $event_id . "'";
			$event_result = $dbh->query($event_qry);
			$eventDb = $event_result->fetch(PDO::FETCH_OBJ);
			$event_changed = false;

			if ($event_event != $eventDb->event_event) $event_changed = true;
			// *** Compare date case-insensitive (for PHP 8.1 check if variabele is used) ***
			//if (isset($_POST["event_date_prefix"][$key]) OR isset($_POST["event_date"][$key])){
			// Doesn't work properly, date isn't always saved:
			//if ($eventDb->event_date AND ($_POST["event_date_prefix"][$key] OR $_POST["event_date"][$key])){
			// Doesn't work if date is removed:
			//if ($_POST["event_date_prefix"][$key] OR $_POST["event_date"][$key]){
			//if (isset($eventDb->event_date)){
			if (isset($_POST["event_date"][$key])) {
				$event_date = '';
				if (isset($eventDb->event_date)) $event_date = $eventDb->event_date;
				if (strcasecmp($_POST["event_date_prefix"][$key] . $_POST["event_date"][$key], $event_date) != 0) $event_changed = true;
			}
			if ($_POST["event_place" . $key] != $eventDb->event_place) $event_changed = true;
			if (isset($_POST["event_gedcom"][$key])) {
				if ($_POST["event_gedcom"][$key] != $eventDb->event_gedcom) $event_changed = true;
			}
			if (isset($_POST["event_text"][$key])) {
				if ($_POST["event_text"][$key] != $eventDb->event_text) $event_changed = true;
			}

			if ($event_changed) {
				$sql = "UPDATE humo_events SET
					event_event='" . $event_event . "',
					event_date='" . $editor_cls->date_process("event_date", $key) . "',
					event_place='" . $editor_cls->text_process($_POST["event_place" . $key]) . "',";
				if (isset($_POST["event_gedcom"][$key])) {
					$sql .= "event_gedcom='" . $editor_cls->text_process($_POST["event_gedcom"][$key]) . "',";
				}
				if (isset($_POST["event_text"][$key])) {
					$sql .= "event_text='" . $editor_cls->text_process($_POST["event_text"][$key]) . "',";
				}
				$sql .= "event_changed_user='" . $username . "',
					event_changed_date='" . $gedcom_date . "',
					event_changed_time='" . $gedcom_time . "'";

				//$sql.=" WHERE event_id='".safe_text_db($_POST["event_id"][$key])."'";
				$sql .= " WHERE event_id='" . $event_id . "'";
				//echo '<br>'.$sql.'<br>';
				$result = $dbh->query($sql);
			}
		}

		// *** Also change person colors by descendants of selected person ***
		if (isset($_POST["pers_colour_desc"][$key])) {
			// EXAMPLE: descendants($family_id,$main_person,$gn,$nr_generations);
			descendants($marriage, $pers_gedcomnumber, 0, 20);
			// *** Starts with 2nd descendant, skip main person (that's already processed above this code)! ***
			// *** $descendant_array[0]= not in use ***
			// *** $descendant_array[1]= main person ***
			for ($i = 2; $i <= $descendant_id; $i++) {
				// *** Check if descendant already has this colour ***
				$event_sql = "SELECT * FROM humo_events
					WHERE event_tree_id='" . $tree_id . "'
					AND event_connect_kind='person' 
					AND event_connect_id='" . $descendant_array[$i] . "'
					AND event_kind='person_colour_mark'
					AND event_event='" . safe_text_db($_POST["event_event_old"][$key]) . "'";
				$event_qry = $dbh->query($event_sql);
				$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

				$event_gedcom = '';
				if (isset($_POST["event_gedcom"][$key])) $event_gedcom = $_POST["event_gedcom"][$key];
				$event_text = '';
				if (isset($_POST["event_text"][$key])) $event_text = $_POST["event_text"][$key];

				// *** Descendant already has this color, change it ***
				if (isset($eventDb->event_event)) {
					$sql = "UPDATE humo_events SET
						event_event='" . $event_event . "',
						event_date='" . $editor_cls->date_process("event_date", $key) . "',
						event_place='" . $editor_cls->text_process($_POST["event_place" . $key]) . "',
						event_changed_user='" . $username . "',
						event_changed_date='" . $gedcom_date . "',
						event_gedcom='" . $editor_cls->text_process($event_gedcom) . "',
						event_text='" . $editor_cls->text_process($event_text) . "',
						event_changed_time='" . $gedcom_time . "'
						WHERE event_id='" . $eventDb->event_id . "'";
					$result = $dbh->query($sql);
				} else {
					// *** Add person event for descendants ***
					// *** Add event. If event is new, use: $new_event=true. ***
					// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
					add_event(false, 'person', $descendant_array[$i], 'person_colour_mark', $event_event, $event_gedcom, 'event_date', $_POST["event_place" . $key], $event_text, $key);
				}
			}
		}

		// *** Also change person colors by ancestors of selected person ***
		if (isset($_POST["pers_colour_anc"][$key])) {
			ancestors($pers_gedcomnumber);
			foreach ($ancestor_array as $key2 => $value) {
				//echo $key2.'-'.$value.', ';
				$selected_ancestor = $value;

				// *** Check if ancestor already has this colour ***
				$event_sql = "SELECT * FROM humo_events
					WHERE event_tree_id='" . $tree_id . "'
					AND event_connect_kind='person'
					AND event_connect_id='" . $selected_ancestor . "'
					AND event_kind='person_colour_mark'
					AND event_event='" . safe_text_db($_POST["event_event_old"][$key]) . "'";
				$event_qry = $dbh->query($event_sql);
				$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

				$event_gedcom = '';
				if (isset($_POST["event_gedcom"][$key])) $event_gedcom = $_POST["event_gedcom"][$key];
				$event_text = '';
				if (isset($_POST["event_text"][$key])) $event_text = $_POST["event_text"][$key];

				// *** Ancestor already has this color, change it ***
				if (isset($eventDb->event_event)) {
					$sql = "UPDATE humo_events SET
						event_event='" . $event_event . "',
						event_date='" . $editor_cls->date_process("event_date", $key) . "',
						event_place='" . $editor_cls->text_process($_POST["event_place" . $key]) . "',
						event_changed_user='" . $username . "',
						event_changed_date='" . $gedcom_date . "',
						event_gedcom='" . $editor_cls->text_process($event_gedcom) . "',
						event_text='" . $editor_cls->text_process($event_text) . "',
						event_changed_time='" . $gedcom_time . "'
						WHERE event_id='" . $eventDb->event_id . "'";
					$result = $dbh->query($sql);
				} else {
					// *** Add person event for ancestors ***
					// *** Add event. If event is new, use: $new_event=true. ***
					// *** true/false, $event_connect_kind,$event_connect_id,$event_kind,$event_event,$event_gedcom,$event_date,$event_place,$event_text ***
					add_event(false, 'person', $selected_ancestor, 'person_colour_mark', $event_event, $event_gedcom, 'event_date', $_POST["event_place" . $key], $event_text, $key);
				}
			}
		}

		family_tree_update($tree_id);
	}
}

// *** Remove event ***
if (isset($_GET['event_drop'])) {
	$confirm .= '<div class="confirm">';
	$confirm .= __('Are you sure you want to remove this event?');
	$confirm .= ' <form method="post" action="' . $phpself;
	if (isset($_GET['source_id'])) $confirm .= '?source_id=' . $_GET['source_id'];
	$confirm .= '" style="display : inline;">';
	$confirm .= '<input type="hidden" name="page" value="' . $_GET['page'] . '">';
	if (isset($_GET['event_person'])) $confirm .= '<input type="hidden" name="event_person" value="event_person">';
	if (isset($_GET['event_family'])) $confirm .= '<input type="hidden" name="event_family" value="event_family">';
	if (isset($_GET['event_source'])) $confirm .= '<input type="hidden" name="event_source" value="event_source">';
	$confirm .= '<input type="hidden" name="event_kind" value="' . $_GET['event_kind'] . '">';
	$confirm .= '<input type="hidden" name="event_drop" value="' . $_GET['event_drop'] . '">';

	if (isset($_GET['event_kind']) and $_GET['event_kind'] == 'person_colour_mark') {
		$selected = ''; //if ($selected_alive=='alive'){ $selected=' CHECKED'; }
		$confirm .= '<br>' . __('Also remove colour marks of');
		$confirm .= ' <input type="checkbox" name="event_descendants" value="alive"' . $selected . '> ' . __('Descendants');
		$confirm .= ' <input type="checkbox" name="event_ancestors" value="alive"' . $selected . '> ' . __('Ancestors') . '<br>';
	}

	$confirm .= ' <input type="Submit" name="event_drop2" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
	$confirm .= ' <input type="Submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
	$confirm .= '</form>';
	$confirm .= '</div>';
}
if (isset($_POST['event_drop2'])) {
	$event_kind = safe_text_db($_POST['event_kind']);
	$event_order_id = safe_text_db($_POST['event_drop']);

	if (isset($_POST['event_person'])) {

		// *** Remove NON SHARED source from event (connection in humo_connections table) ***
		$event_sql = "SELECT * FROM humo_events
			WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'
			AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
		$event_qry = $dbh->query($event_sql);
		$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
		$event_event = $eventDb->event_event;

		// *** Remove sources ***
		remove_sources($tree_id, 'pers_event_source', $eventDb->event_id);

		if (isset($_POST['event_descendants']) or isset($_POST['event_ancestors'])) {
			// *** Get event_event from selected person, needed to remove colour from descendant and/ or ancestors ***
			$event_sql = "SELECT event_event FROM humo_events
				WHERE event_tree_id='" . $tree_id . "'
				AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'
				AND event_kind='person_colour_mark' AND event_order='" . $event_order_id . "'";
			$event_qry = $dbh->query($event_sql);
			$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
			$event_event = $eventDb->event_event;
		}

		$sql = "DELETE FROM humo_events
			WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'
			AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
		$result = $dbh->query($sql);

		// *** Change order of events ***
		$event_sql = "SELECT * FROM humo_events
			WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='person' AND event_connect_id='" . $pers_gedcomnumber . "'
			AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order_id . "' ORDER BY event_order";
		$event_qry = $dbh->query($event_sql);
		while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
			$sql = "UPDATE humo_events SET
			event_order='" . ($eventDb->event_order - 1) . "',
			event_changed_user='" . $username . "',
			event_changed_date='" . $gedcom_date . "',
			event_changed_time='" . $gedcom_time . "'
			WHERE event_id='" . $eventDb->event_id . "'";
			$result = $dbh->query($sql);
		}

		// *** Also remove colour mark from descendants and/ or ancestors ***
		if (isset($_POST['event_descendants'])) {
			// EXAMPLE: descendants($family_id,$main_person,$gn,$nr_generations);
			descendants($marriage, $pers_gedcomnumber, 0, 20);
			// *** Starts with 2nd descendant, skip main person (that's already processed above this code)! ***
			for ($i = 2; $i <= $descendant_id; $i++) {
				// *** Get event_order from selected person ***
				$event_sql = "SELECT event_order FROM humo_events WHERE event_tree_id='" . $tree_id . "'
					AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
					AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
				$event_qry = $dbh->query($event_sql);
				$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
				$event_order = $eventDb->event_order;

				// *** Remove colour from descendant ***
				$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
					AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
					AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
				$result = $dbh->query($sql);

				// *** Restore order of colour marks ***
				$event_sql = "SELECT * FROM humo_events
					WHERE event_tree_id='" . $tree_id . "'
					AND event_connect_kind='person' AND event_connect_id='" . $descendant_array[$i] . "'
					AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order . "' ORDER BY event_order";
				$event_qry = $dbh->query($event_sql);
				while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
					$sql = "UPDATE humo_events SET
					event_order='" . ($eventDb->event_order - 1) . "',
					event_changed_user='" . $username . "',
					event_changed_date='" . $gedcom_date . "',
					event_changed_time='" . $gedcom_time . "'
					WHERE event_id='" . $eventDb->event_id . "'";
					$result = $dbh->query($sql);
				}
			}
		}

		if (isset($_POST['event_ancestors'])) {
			ancestors($pers_gedcomnumber);
			foreach ($ancestor_array as $key2 => $value) {
				//echo $key2.'-'.$value.', ';
				$selected_ancestor = $value;

				// *** Get event_order from selected person ***
				$event_sql = "SELECT event_order FROM humo_events WHERE event_tree_id='" . $tree_id . "'
					AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
					AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
				$event_qry = $dbh->query($event_sql);
				$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
				$event_order = $eventDb->event_order;

				// *** Check if ancestor already has this colour ***
				$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
					AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
					AND event_kind='person_colour_mark' AND event_event='" . $event_event . "'";
				$result = $dbh->query($sql);

				// *** Restore order of colour marks ***
				$event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
					AND event_connect_kind='person' AND event_connect_id='" . $selected_ancestor . "'
					AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order . "' ORDER BY event_order";
				$event_qry = $dbh->query($event_sql);
				while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
					$sql = "UPDATE humo_events SET
					event_order='" . ($eventDb->event_order - 1) . "',
					event_changed_user='" . $username . "',
					event_changed_date='" . $gedcom_date . "',
					event_changed_time='" . $gedcom_time . "'
					WHERE event_id='" . $eventDb->event_id . "'";
					$result = $dbh->query($sql);
				}
			}
		}
	}

	if (isset($_POST['event_family'])) {

		// *** Remove NON SHARED source from event (connection in humo_connections table) ***
		$event_sql = "SELECT * FROM humo_events
			WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
			AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
		$event_qry = $dbh->query($event_sql);
		$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
		$event_event = $eventDb->event_event;

		// *** Remove sources ***
		remove_sources($tree_id, 'fam_event_source', $eventDb->event_id);

		$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
			AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
		$result = $dbh->query($sql);

		$event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='family' AND event_connect_id='" . $marriage . "'
			AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order_id . "' ORDER BY event_order";
		$event_qry = $dbh->query($event_sql);
		while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
			$sql = "UPDATE humo_events SET
				event_order='" . ($eventDb->event_order - 1) . "',
				event_changed_user='" . $username . "',
				event_changed_date='" . $gedcom_date . "',
				event_changed_time='" . $gedcom_time . "'
				WHERE event_id='" . $eventDb->event_id . "'";
			$result = $dbh->query($sql);
		}
	}

	// *** Picture by source: pictures are stored in event table ***
	if (isset($_POST['event_source'])) {
		$sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='source' AND event_connect_id='" . safe_text_db($_GET['source_id']) . "'
			AND event_kind='" . $event_kind . "' AND event_order='" . $event_order_id . "'";
		$result = $dbh->query($sql);

		$event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='source' AND event_connect_id='" . safe_text_db($_GET['source_id']) . "'
			AND event_kind='" . $event_kind . "' AND event_order>'" . $event_order_id . "' ORDER BY event_order";
		$event_qry = $dbh->query($event_sql);
		while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
			$sql = "UPDATE humo_events SET
				event_order='" . ($eventDb->event_order - 1) . "',
				event_changed_user='" . $username . "',
				event_changed_date='" . $gedcom_date . "',
				event_changed_time='" . $gedcom_time . "'
				WHERE event_id='" . $eventDb->event_id . "'";
			$result = $dbh->query($sql);
		}
	}
}

if (isset($_GET['event_down'])) {
	$event_kind = safe_text_db($_GET['event_kind']);
	$event_order = safe_text_db($_GET["event_down"]);

	if (isset($_GET['event_person'])) {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
	}
	if (isset($_GET['event_family'])) {
		$event_connect_kind = 'family';
		$event_connect_id = $marriage;
	}
	// *** Move picture by source in seperate source page ***
	if (isset($_GET['event_source'])) {
		$event_connect_kind = 'source';
		$event_connect_id = $_GET['source_id'];
	}

	$sql = "UPDATE humo_events SET event_order='99' WHERE event_tree_id='" . $tree_id . "'
		AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
		AND event_kind='" . $event_kind . "'
		AND event_order='" . $event_order . "'";
	$result = $dbh->query($sql);

	$sql = "UPDATE humo_events SET event_order='" . $event_order . "' WHERE event_tree_id='" . $tree_id . "'
		AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
		AND event_kind='" . $event_kind . "'
		AND event_order='" . ($event_order + 1) . "'";
	$result = $dbh->query($sql);

	$sql = "UPDATE humo_events SET event_order='" . ($event_order + 1) . "' WHERE event_tree_id='" . $tree_id . "'
	AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
	AND event_kind='" . $event_kind . "'
	AND event_order=99";
	$result = $dbh->query($sql);
}

if (isset($_GET['event_up'])) {
	$event_kind = safe_text_db($_GET['event_kind']);
	$event_order = safe_text_db($_GET['event_up']);

	if (isset($_GET['event_person'])) {
		$event_connect_kind = 'person';
		$event_connect_id = $pers_gedcomnumber;
	}
	if (isset($_GET['event_family'])) {
		$event_connect_kind = 'family';
		$event_connect_id = $marriage;
	}
	// *** Move picture by source in seperate source page ***
	if (isset($_GET['event_source'])) {
		$event_connect_kind = 'source';
		$event_connect_id = $_GET['source_id'];
	}

	$sql = "UPDATE humo_events SET event_order='99'
	WHERE event_tree_id='" . $tree_id . "'
	AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
	AND event_kind='" . $event_kind . "'
	AND event_order='" . $event_order . "'";
	$result = $dbh->query($sql);

	$sql = "UPDATE humo_events SET
	event_order='" . $event_order . "'
	WHERE event_tree_id='" . $tree_id . "'
	AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
	AND event_kind='" . $event_kind . "'
	AND event_order='" . ($event_order - 1) . "'";
	$result = $dbh->query($sql);

	$sql = "UPDATE humo_events SET
	event_order='" . ($event_order - 1) . "'
	WHERE event_tree_id='" . $tree_id . "'
	AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
	AND event_kind='" . $event_kind . "'
	AND event_order=99";
	$result = $dbh->query($sql);
}


// ************************
// *** Save connections ***
// ************************

// *** Add new person-address connection ***
//if (isset($_GET['person_place_address']) and isset($_GET['address_add'])){
if (isset($_POST['person_add_address'])) {
	$_POST['connect_add'] = 'add_address';
	$_POST['connect_kind'] = 'person';
	$_POST["connect_sub_kind"] = 'person_address';
	$_POST["connect_connect_id"] = $pers_gedcomnumber;
}
// *** Add new family-address connection ***
//if (isset($_GET['family_place_address']) and isset($_GET['address_add'])){
if (isset($_POST['relation_add_address'])) {
	$_POST['connect_add'] = 'add_address';
	$_POST['connect_kind'] = 'family';
	$_POST["connect_sub_kind"] = 'family_address';
	$marriage = $_POST['marriage']; // *** Needed to check $_POST for multiple relations ***
	$_POST["connect_connect_id"] = $marriage;
}

// *** Added april 2023: Add new source ***
if (isset($_GET['source_add3'])) {
	$_POST['connect_add'] = 'add_source';
	$_POST['connect_kind'] = $_GET['connect_kind'];
	$_POST["connect_sub_kind"] = $_GET["connect_sub_kind"];
	$_POST["connect_connect_id"] = $_GET["connect_connect_id"];
}


/*
// *** Added may 2023: Add new source ***
//http://localhost/humo-genealogy/admin/index.php?page=editor&
//source_add3=1&
//connect_kind=person&
//connect_sub_kind=pers_name_source&
//connect_connect_id=I9892#pers_name_sourceI9892
if (isset($_POST['add_pers_name_source'])){
	$_POST['connect_add']='add_source';
	$_POST['connect_kind']='person';
	$_POST["connect_sub_kind"]='pers_name_source';
	$_POST["connect_connect_id"]=$pers_gedcomnumber;

unset ($_POST['connect_change']);
}
*/


// *** Add new source/ address connection ***
if (isset($_POST['connect_add'])) {
	// *** Generate new order number ***
	$event_sql = "SELECT * FROM humo_connections
		WHERE connect_tree_id='" . $tree_id . "'
		AND connect_kind='" . safe_text_db($_POST['connect_kind']) . "'
		AND connect_sub_kind='" . safe_text_db($_POST["connect_sub_kind"]) . "'
		AND connect_connect_id='" . safe_text_db($_POST["connect_connect_id"]) . "'";
	$event_qry = $dbh->query($event_sql);
	$count = $event_qry->rowCount();
	$count++;

	$username = $_SESSION['user_name_admin'];
	$sql = "INSERT INTO humo_connections SET
		connect_tree_id='" . $tree_id . "',
		connect_order='" . $count . "',
		connect_new_user='" . $username . "',
		connect_new_date='" . $gedcom_date . "',
		connect_new_time='" . $gedcom_time . "',
		connect_kind='" . safe_text_db($_POST['connect_kind']) . "',
		connect_sub_kind='" . safe_text_db($_POST["connect_sub_kind"]) . "',
		connect_connect_id='" . safe_text_db($_POST["connect_connect_id"]) . "'";
	$result = $dbh->query($sql);
} // *** End of update sources ***

// *** Change source/ address connection ***
if (isset($_POST['connect_change'])) {
	foreach ($_POST['connect_change'] as $key => $value) {
		$username = $_SESSION['user_name_admin'];

		// *** Only update if there are changed values! Otherwise all connect_change variables will be changed... ***
		$connect_changed = true;
		if (isset($_POST['connect_date_old'][$key])) {
			$connect_changed = false;
			// *** Compare date case-insensitive ***
			if (strcasecmp($_POST["connect_date_prefix"][$key] . $_POST["connect_date"][$key], $_POST["connect_date_old"][$key]) != 0) $connect_changed = true;
			if ($_POST["connect_role"][$key] != $_POST["connect_role_old"][$key]) $connect_changed = true;
			if ($_POST['connect_text'][$key] != $_POST["connect_text_old"][$key]) $connect_changed = true;

			// *** Save shared address (even if role or extra text isn't used ***
			if (isset($_POST['connect_item_id'][$key])) {
				if (isset($_POST["connect_item_id_old"][$key])) {
					if ($_POST['connect_item_id'][$key] !== $_POST["connect_item_id_old"][$key]) $connect_changed = true;
				} else {
					$connect_changed = true;
				}
			}
		}

		if ($connect_changed) {
			$sql = "UPDATE humo_connections SET
			connect_kind='" . safe_text_db($_POST['connect_kind'][$key]) . "',
			connect_sub_kind='" . safe_text_db($_POST['connect_sub_kind'][$key]) . "',
			connect_page='" . $editor_cls->text_process($_POST["connect_page"][$key]) . "',
			connect_role='" . $editor_cls->text_process($_POST["connect_role"][$key]) . "',
			connect_source_id='" . safe_text_db($_POST['connect_source_id'][$key]) . "',";

			if (isset($_POST['connect_date'][$key]))
				$sql .= "connect_date='" . $editor_cls->date_process("connect_date", $key) . "',";

			if (isset($_POST['connect_place'][$key]))
				$sql .= "connect_place='" . $editor_cls->text_process($_POST["connect_place"][$key]) . "',";

			// *** Extra text for source ***
			if (isset($_POST['connect_text'][$key]))
				$sql .= "connect_text='" . safe_text_db($_POST['connect_text'][$key]) . "',";

			if (isset($_POST['connect_quality'][$key]))
				$sql .= " connect_quality='" . safe_text_db($_POST['connect_quality'][$key]) . "',";

			if (isset($_POST['connect_item_id'][$key]) and ($_POST['connect_item_id'][$key]))
				$sql .= " connect_item_id='" . safe_text_db($_POST['connect_item_id'][$key]) . "',";

			$sql .= " connect_changed_user='" . $username . "', ";
			$sql .= " connect_changed_date='" . $gedcom_date . "', ";
			$sql .= " connect_changed_time='" . $gedcom_time . "'";
			$sql .= " WHERE connect_id='" . safe_text_db($_POST["connect_change"][$key]) . "'";
			//echo $sql.'<br>';
			$result = $dbh->query($sql);
		}

		//source_status='".$editor_cls->text_process($_POST['source_status'][$key])."',
		//source_publ='".$editor_cls->text_process($_POST['source_publ'][$key])."',
		//source_auth='".$editor_cls->text_process($_POST['source_auth'][$key])."',
		//source_subj='".$editor_cls->text_process($_POST['source_subj'][$key])."',
		//source_item='".$editor_cls->text_process($_POST['source_item'][$key])."',
		//source_kind='".$editor_cls->text_process($_POST['source_kind'][$key])."',
		//source_repo_caln='".$editor_cls->text_process($_POST['source_repo_caln'][$key])."',
		//source_repo_page='".$editor_cls->text_process($_POST['source_repo_page'][$key])."',
		//source_repo_gedcomnr='".$editor_cls->text_process($_POST['source_repo_gedcomnr'][$key])."',
		if (isset($_POST['source_title'][$key])) {
			$username = $_SESSION['user_name_admin'];
			//source_date='".safe_text_db($_POST['source_date'][$key])."',

			//$source_shared=''; if (isset($_POST['source_shared'][$key])) $source_shared='1';
			//source_shared='".$source_shared."',

			$sql = "UPDATE humo_sources SET
			source_title='" . $editor_cls->text_process($_POST['source_title'][$key]) . "',
			source_text='" . $editor_cls->text_process($_POST['source_text'][$key], true) . "',
			source_refn='" . $editor_cls->text_process($_POST['source_refn'][$key]) . "',
			source_date='" . $editor_cls->date_process("source_date", $key) . "',
			source_place='" . $editor_cls->text_process($_POST['source_place'][$key]) . "',
			source_changed_user='" . $username . "',
			source_changed_date='" . $gedcom_date . "',
			source_changed_time='" . $gedcom_time . "'
			WHERE source_tree_id='" . $tree_id . "' AND source_id='" . safe_text_db($_POST["source_id"][$key]) . "'";
			$result = $dbh->query($sql);
			//family_tree_update($tree_id);
		}
	}
}

// *** Remove source/ address connection ***
if (isset($_GET['connect_drop'])) {
	// *** Needed for event sources ***
	$connect_kind = '';
	if (isset($_GET['connect_kind'])) $connect_kind = $_GET['connect_kind'];
	//if (isset($_POST['connect_kind'])) $connect_kind=$_POST['connect_kind'];

	$connect_sub_kind = '';
	//if (isset($_POST['connect_sub_kind'])) $connect_sub_kind=$_POST['connect_sub_kind'];
	if (isset($_GET['connect_sub_kind'])) $connect_sub_kind = $_GET['connect_sub_kind'];

	// *** Needed for event sources ***
	$connect_connect_id = '';
	if (isset($_GET['connect_connect_id']) and $_GET['connect_connect_id']) $connect_connect_id = $_GET['connect_connect_id'];
	//if (isset($_POST['connect_connect_id']) AND $_POST['connect_connect_id']) $connect_connect_id=$_POST['connect_connect_id'];

	$event_link = '';
	if (isset($_POST['event_person']) or isset($_GET['event_person']))
		$event_link = '&event_person=1';
	if (isset($_POST['event_family']) or isset($_GET['event_family']))
		$event_link = '&event_family=1';
	//$phpself2='index.php?page=editor_sources&connect_kind='.$connect_kind.'&connect_sub_kind='.$connect_sub_kind.'&connect_connect_id='.$connect_connect_id;
	$phpself2 = 'index.php?page=' . $page . '&connect_kind=' . $connect_kind . '&connect_sub_kind=' . $connect_sub_kind . '&connect_connect_id=' . $connect_connect_id;
	$phpself2 .= $event_link;

	echo '<div class="confirm">';
	echo __('Are you sure you want to remove this event?');
	echo ' <form method="post" action="' . $phpself2 . '" style="display : inline;">';
	//echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
	echo '<input type="hidden" name="connect_drop" value="' . $_GET['connect_drop'] . '">';

	// *** Needed for events!!! ***
	echo '<input type="hidden" name="connect_kind" value="' . $connect_kind . '">';
	echo '<input type="hidden" name="connect_sub_kind" value="' . $connect_sub_kind . '">';
	echo '<input type="hidden" name="connect_connect_id" value="' . $connect_connect_id . '">';

	if (isset($_POST['event_person']) or isset($_GET['event_person']))
		echo '<input type="hidden" name="event_person" value="1">';
	if (isset($_POST['event_family']) or isset($_GET['event_family']))
		echo '<input type="hidden" name="event_family" value="1">';

	// *** Remove adress event ***
	if (isset($_GET['person_place_address']))
		echo '<input type="hidden" name="person_place_address" value="person_place_address">';

	if (isset($_GET['marriage_nr']))
		echo '<input type="hidden" name="marriage_nr" value="' . safe_text_db($_GET['marriage_nr']) . '">';

	echo ' <input type="Submit" name="connect_drop2" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
	echo ' <input type="Submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';

	echo '</form>';
	echo '</div>';
}
// *** Delete source or address connection ***
if (isset($_POST['connect_drop2'])) {
	$event_sql = "SELECT * FROM humo_connections
		WHERE connect_id='" . safe_text_db($_POST['connect_drop']) . "'";
	$event_qry = $dbh->query($event_sql);
	$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);

	$connect_kind = $eventDb->connect_kind;
	$connect_sub_kind = $eventDb->connect_sub_kind;
	$connect_connect_id = $eventDb->connect_connect_id;
	//echo $connect_kind.' '.$connect_sub_kind.' '.$connect_connect_id.'!!';

	// *** Remove (NON-SHARED) source by all connections ***
	/*
	if ($eventDb->connect_source_id){
		//DOESN'T WORK
		//$sourceDb = $db_functions->get_source($eventDb->connect_source_id);
		$source_sql="SELECT * FROM humo_sources
			WHERE source_gedcomnr='".safe_text_db($eventDb->connect_source_id)."'
			AND source_shared!='1'";
		//echo $source_sql.'<br>';
		$source_qry=$dbh->query($source_sql);
		$sourceDb=$source_qry->fetch(PDO::FETCH_OBJ);
		if ($sourceDb){
			$sql="DELETE FROM humo_sources
				WHERE source_id='".safe_text_db($sourceDb->source_id)."'";
			$result=$dbh->query($sql);
		}
	}
	*/

	// *** Remove NON SHARED addresses ***
	if ($connect_sub_kind == 'person_address' or $connect_sub_kind == 'family_address') {
		$address_sql = "SELECT * FROM humo_addresses
			WHERE address_gedcomnr='" . safe_text_db($eventDb->connect_item_id) . "'
			AND address_shared!='1'";
		$address_qry = $dbh->query($address_sql);
		$addressDb = $address_qry->fetch(PDO::FETCH_OBJ);
		if ($addressDb) {
			$sql = "DELETE FROM humo_addresses
				WHERE address_id='" . safe_text_db($addressDb->address_id) . "'";
			//echo $sql.'!';
			$result = $dbh->query($sql);
		}

		// *** Remove sources ***
		if ($connect_sub_kind == 'person_address')
			remove_sources($tree_id, 'pers_address_source', $eventDb->connect_item_id);
		if ($connect_sub_kind == 'family_address')
			remove_sources($tree_id, 'fam_address_source', $eventDb->connect_item_id);
	}

	$sql = "DELETE FROM humo_connections
		WHERE connect_id='" . safe_text_db($_POST['connect_drop']) . "'";
	$result = $dbh->query($sql);

	// *** Re-order remaining source connections ***
	$event_order = 1;
	$event_sql = "SELECT * FROM humo_connections
		WHERE connect_tree_id='" . $tree_id . "'
		AND connect_kind='" . $connect_kind . "'
		AND connect_sub_kind='" . $connect_sub_kind . "'
		AND connect_connect_id='" . $connect_connect_id . "'
		ORDER BY connect_order";
	$event_qry = $dbh->query($event_sql);
	while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
		$sql = "UPDATE humo_connections
			SET connect_order='" . $event_order . "'
			WHERE connect_id='" . $eventDb->connect_id . "'";
		$result = $dbh->query($sql);
		$event_order++;
	}
}

if (isset($_GET['connect_down'])) {
	$sql = "UPDATE humo_connections SET connect_order='99'
		WHERE connect_id='" . safe_text_db($_GET['connect_down']) . "'";
	$result = $dbh->query($sql);

	$event_order = safe_text_db($_GET['connect_order']);
	$sql = "UPDATE humo_connections SET connect_order='" . $event_order . "'
		WHERE connect_tree_id='" . $tree_id . "'
		AND connect_kind='" . safe_text_db($_GET['connect_kind']) . "'
		AND connect_sub_kind='" . safe_text_db($_GET['connect_sub_kind']) . "'
		AND connect_connect_id='" . safe_text_db($_GET['connect_connect_id']) . "'
		AND connect_order='" . ($event_order + 1) . "'";
	$result = $dbh->query($sql);

	$sql = "UPDATE humo_connections SET connect_order='" . ($event_order + 1) . "'
		WHERE connect_tree_id='" . $tree_id . "'
		AND connect_kind='" . safe_text_db($_GET['connect_kind']) . "'
		AND connect_sub_kind='" . safe_text_db($_GET['connect_sub_kind']) . "'
		AND connect_connect_id='" . safe_text_db($_GET['connect_connect_id']) . "'
		AND connect_order=99";
	$result = $dbh->query($sql);
}

if (isset($_GET['connect_up'])) {
	$sql = "UPDATE humo_connections SET connect_order='99'
	WHERE connect_id='" . safe_text_db($_GET['connect_up']) . "'";
	$result = $dbh->query($sql);

	$event_order = safe_text_db($_GET['connect_order']);
	$sql = "UPDATE humo_connections SET connect_order='" . $event_order . "'
		WHERE connect_tree_id='" . $tree_id . "'
		AND connect_kind='" . safe_text_db($_GET['connect_kind']) . "'
		AND connect_sub_kind='" . safe_text_db($_GET['connect_sub_kind']) . "'
		AND connect_connect_id='" . safe_text_db($_GET['connect_connect_id']) . "'
		AND connect_order='" . ($event_order - 1) . "'";
	$result = $dbh->query($sql);

	$sql = "UPDATE humo_connections SET connect_order='" . ($event_order - 1) . "'
		WHERE connect_tree_id='" . $tree_id . "'
		AND connect_kind='" . safe_text_db($_GET['connect_kind']) . "'
		AND connect_sub_kind='" . safe_text_db($_GET['connect_sub_kind']) . "'
		AND connect_connect_id='" . safe_text_db($_GET['connect_connect_id']) . "'
		AND connect_order=99";
	$result = $dbh->query($sql);
}


// *******************
// *** Save source ***
// *******************

// *** Add a new shared source in page "Shared sources" without connections ***
if (isset($_POST['source_add'])) {
	// *** Generate new GEDCOM number ***
	$new_gedcomnumber = 'S' . $db_functions->generate_gedcomnr($tree_id, 'source');

	//source_shared='1',
	$sql = "INSERT INTO humo_sources SET
		source_tree_id='" . $tree_id . "',
		source_gedcomnr='" . $new_gedcomnumber . "',
		source_status='" . $editor_cls->text_process($_POST['source_status']) . "',
		source_title='" . $editor_cls->text_process($_POST['source_title']) . "',
		source_date='" . safe_text_db($_POST['source_date']) . "',
		source_place='" . $editor_cls->text_process($_POST['source_place']) . "',
		source_publ='" . $editor_cls->text_process($_POST['source_publ']) . "',
		source_refn='" . $editor_cls->text_process($_POST['source_refn']) . "',
		source_auth='" . $editor_cls->text_process($_POST['source_auth']) . "',
		source_subj='" . $editor_cls->text_process($_POST['source_subj']) . "',
		source_item='" . $editor_cls->text_process($_POST['source_item']) . "',
		source_kind='" . $editor_cls->text_process($_POST['source_kind']) . "',
		source_repo_caln='" . $editor_cls->text_process($_POST['source_repo_caln']) . "',
		source_repo_page='" . safe_text_db($_POST['source_repo_page']) . "',
		source_repo_gedcomnr='" . $editor_cls->text_process($_POST['source_repo_gedcomnr']) . "',
		source_text='" . $editor_cls->text_process($_POST['source_text']) . "',
		source_new_user='" . $username . "',
		source_new_date='" . $gedcom_date . "',
		source_new_time='" . $gedcom_time . "'";
	$result = $dbh->query($sql);

	//$new_source_qry= "SELECT * FROM humo_sources
	//	WHERE source_tree_id='".$tree_id."' ORDER BY source_id DESC LIMIT 0,1";
	//$new_source_result = $dbh->query($new_source_qry);
	//$new_source=$new_source_result->fetch(PDO::FETCH_OBJ);
	//$_POST['source_id']=$new_source->source_id;
	$_POST['source_id'] = $dbh->lastInsertId();
}

// *** december 2020: new combined source and shared source system ***
if (isset($_GET['source_add2'])) {
	$username = $_SESSION['user_name_admin'];

	// *** Generate new GEDCOM number ***
	$new_gedcomnumber = 'S' . $db_functions->generate_gedcomnr($tree_id, 'source');

	$sql = "INSERT INTO humo_sources SET
		source_tree_id='" . $tree_id . "',
		source_gedcomnr='" . $new_gedcomnumber . "',
		source_status='',
		source_title='',
		source_date='',
		source_place='',
		source_publ='',
		source_refn='',
		source_auth='',
		source_subj='',
		source_item='',
		source_kind='',
		source_repo_caln='',
		source_repo_page='',
		source_repo_gedcomnr='',
		source_text='',
		source_new_user='" . $username . "',
		source_new_date='" . $gedcom_date . "',
		source_new_time='" . $gedcom_time . "'";
	//echo $sql.'<br>';
	$result = $dbh->query($sql);

	$sql = "UPDATE humo_connections SET
		connect_tree_id='" . $tree_id . "',
		connect_new_date='" . $gedcom_date . "',
		connect_new_time='" . $gedcom_time . "',
		connect_source_id='" . $new_gedcomnumber . "'
		WHERE connect_id='" . safe_text_db($_GET["connect_id"]) . "'";
	//echo $sql.'<br>';
	$result = $dbh->query($sql);
}

//source_shared='".$editor_cls->text_process($_POST['source_shared'])."',
//if (isset($_POST['source_change'])){
$save_source_data = false;
if (isset($_POST['source_change'])) $save_source_data = true;
// *** Also save source data if media is added ***
if (isset($_POST['add_source_picture'])) $save_source_data = true;
if ($save_source_data) {
	$sql = "UPDATE humo_sources SET
	source_status='" . $editor_cls->text_process($_POST['source_status']) . "',
	source_title='" . $editor_cls->text_process($_POST['source_title']) . "',
	source_date='" . $editor_cls->date_process('source_date') . "',
	source_place='" . $editor_cls->text_process($_POST['source_place']) . "',
	source_publ='" . $editor_cls->text_process($_POST['source_publ']) . "',
	source_refn='" . $editor_cls->text_process($_POST['source_refn']) . "',
	source_auth='" . $editor_cls->text_process($_POST['source_auth']) . "',
	source_subj='" . $editor_cls->text_process($_POST['source_subj']) . "',
	source_item='" . $editor_cls->text_process($_POST['source_item']) . "',
	source_kind='" . $editor_cls->text_process($_POST['source_kind']) . "',
	source_repo_caln='" . $editor_cls->text_process($_POST['source_repo_caln']) . "',
	source_repo_page='" . $editor_cls->text_process($_POST['source_repo_page']) . "',
	source_repo_gedcomnr='" . $editor_cls->text_process($_POST['source_repo_gedcomnr']) . "',
	source_text='" . $editor_cls->text_process($_POST['source_text'], true) . "',
	source_changed_user='" . $username . "',
	source_changed_date='" . $gedcom_date . "',
	source_changed_time='" . $gedcom_time . "'
	WHERE source_tree_id='" . $tree_id . "' AND source_id='" . safe_text_db($_POST["source_id"]) . "'";
	$result = $dbh->query($sql);
	family_tree_update($tree_id);
}


// ************************
// *** Save data places ***
// ************************

//OLD CODE
/*
// *** Remove living place ***
if (isset($_GET['living_place_drop'])){
	echo '<div class="confirm">';
	echo __('Are you sure you want to delete this place? ');
	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	if (isset($_GET['pers_place'])){
		echo '<input type="hidden" name="pers_place" value="1">';
	}
	elseif (isset($_GET['fam_place'])){
		echo '<input type="hidden" name="fam_place" value="1">';
	}
	echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
	echo '<input type="hidden" name="person_place_address" value="person_place_address">';
	echo '<input type="hidden" name="living_place_id" value="'.$_GET['living_place_drop'].'">';
	echo ' <input type="Submit" name="living_place_drop2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	echo '</form>';
	echo '</div>';
}
if (isset($_POST['living_place_drop2'])){
	if (isset($_POST['pers_place'])){
		$address_connect_sub_kind='person';
		$address_connect_id=$pers_gedcomnumber;
	}
	elseif (isset($_POST['fam_place'])){
		$address_connect_sub_kind='family';
		$address_connect_id=$marriage;
	}

	$living_place_order=safe_text_db($_POST['living_place_id']);
	$sql="DELETE FROM humo_addresses WHERE address_tree_id='".$tree_id."'
		AND address_connect_sub_kind='".$address_connect_sub_kind."'
		AND address_connect_id='".$address_connect_id."'
		AND address_order='".$living_place_order."'";
	$result=$dbh->query($sql);

	$address_sql="SELECT * FROM humo_addresses
		WHERE address_tree_id='".$tree_id."'
		AND address_connect_sub_kind='".$address_connect_sub_kind."'
		AND address_connect_id='".$address_connect_id."' AND address_order>'".$living_place_order."'
		ORDER BY address_order";
	$event_qry=$dbh->query($address_sql);
	while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
		$sql="UPDATE humo_addresses SET
		address_order='".($eventDb->address_order-1)."',
		address_changed_user='".$username."',
		address_changed_date='".$gedcom_date."',
		address_changed_time='".$gedcom_time."'
		WHERE address_id='".$eventDb->address_id."'";
		$result=$dbh->query($sql);
	}
}
*/

// *** 25-12-2020: NEW combined addresses and shared addresses ***
if (isset($_GET['address_add2'])) {
	// *** Generate new GEDCOM number ***
	$new_gedcomnumber = 'R' . $db_functions->generate_gedcomnr($tree_id, 'address');

	$sql = "INSERT INTO humo_addresses SET
		address_tree_id='" . $tree_id . "',
		address_gedcomnr='" . $new_gedcomnumber . "',
		address_address='',
		address_date='',
		address_zip='',
		address_place='',
		address_phone='',
		address_text='',
		address_new_user='" . $username . "',
		address_new_date='" . $gedcom_date . "',
		address_new_time='" . $gedcom_time . "'";
	$result = $dbh->query($sql);

	$sql = "UPDATE humo_connections SET
		connect_tree_id='" . $tree_id . "',
		connect_new_date='" . $gedcom_date . "',
		connect_new_time='" . $gedcom_time . "',
		connect_kind='" . safe_text_db($_GET['connect_kind']) . "',
		connect_sub_kind='" . safe_text_db($_GET["connect_sub_kind"]) . "',
		connect_connect_id='" . safe_text_db($_GET["connect_connect_id"]) . "',
		connect_item_id='" . $new_gedcomnumber . "'
		WHERE connect_id='" . safe_text_db($_GET["connect_id"]) . "'";
	$result = $dbh->query($sql);
}

// *** Change address ***
if (isset($_POST['change_address_id'])) {
	foreach ($_POST['change_address_id'] as $key => $value) {

		// *** Date for address is processed in connection table ***
		//address_date='".$editor_cls->date_process("address_date",$key)."',
		$address_shared = '';
		if (isset($_POST["address_shared_" . $key])) $address_shared = '1';

		// *** Only update if there are changed values! Otherwise all address_change variables will be changed... ***
		$address_changed = false;
		// Or: get old values out of the database. See editor notes (below in this script).
		if ($address_shared != $_POST["address_shared_old"][$key]) $address_changed = true;
		if ($_POST["address_address_" . $key] != $_POST["address_address_old"][$key]) $address_changed = true;
		if ($_POST["address_place_" . $key] != $_POST["address_place_old"][$key]) $address_changed = true;
		if ($_POST["address_text_" . $key] != $_POST["address_text_old"][$key]) $address_changed = true;
		if ($_POST["address_phone_" . $key] != $_POST["address_phone_old"][$key]) $address_changed = true;
		if ($_POST["address_zip_" . $key] != $_POST["address_zip_old"][$key]) $address_changed = true;

		if ($address_changed) {
			$sql = "UPDATE humo_addresses SET
				address_shared='" . $address_shared . "',
				address_address='" . $editor_cls->text_process($_POST["address_address_" . $key]) . "',
				address_place='" . $editor_cls->text_process($_POST["address_place_" . $key]) . "',
				address_text='" . $editor_cls->text_process($_POST["address_text_" . $key]) . "',
				address_phone='" . $editor_cls->text_process($_POST["address_phone_" . $key]) . "',
				address_zip='" . $editor_cls->text_process($_POST["address_zip_" . $key]) . "',
				address_changed_user='" . $username . "',
				address_changed_date='" . $gedcom_date . "',
				address_changed_time='" . $gedcom_time . "'
			WHERE address_id='" . safe_text_db($_POST["change_address_id"][$key]) . "'";
			$result = $dbh->query($sql);
			//echo $sql.'<br>';
			family_tree_update($tree_id);
		}
	}
}

// *** Add editor note ***
if (isset($_GET['note_add']) and $_GET['note_add']) {
	$gedcom_date = strtoupper(date("d M Y"));
	$gedcom_time = date("H:i:s");

	// *** $note_connect_kind = person or family ***
	$note_connect_kind = 'person';
	if ($_GET['note_add'] == 'family') $note_connect_kind = 'family';

	// *** $note_connect_id = I123 or F123 ***
	$note_connect_id = $pers_gedcomnumber;
	if ($note_connect_kind == 'family') $note_connect_id = $marriage;

	// *** Name of selected person in family tree ***
	@$persDb = $db_functions->get_person($pers_gedcomnumber);
	// *** Use class to process person ***
	$pers_cls = new person_cls;
	$pers_cls->construct($persDb);
	$name = $pers_cls->person_name($persDb);
	$note_names = safe_text_db($name["standard_name"]);

	//note_connect_kind='person',
	$sql = "INSERT INTO humo_user_notes SET
	note_new_date='" . $gedcom_date . "',
	note_new_time='" . $gedcom_time . "',
	note_new_user_id='" . $userid . "',
	note_note='',
	note_kind='editor',
	note_status='Not started',
	note_priority='Normal',
	note_connect_kind='" . $note_connect_kind . "',
	note_connect_id='" . safe_text_db($note_connect_id) . "',
	note_names='" . safe_text_db($note_names) . "',
	note_tree_id='" . $tree_id . "';";
	$result = $dbh->query($sql);
}
// *** Change editor note ***
if (isset($_POST['note_id'])) {
	foreach ($_POST['note_id'] as $key => $value) {
		$note_id = $_POST["note_id"][$key];
		if (is_numeric($note_id)) {
			// *** Read old values ***
			$note_qry = "SELECT * FROM humo_user_notes WHERE note_id='" . $note_id . "'";
			$note_result = $dbh->query($note_qry);
			$noteDb = $note_result->fetch(PDO::FETCH_OBJ);
			$note_changed = false;
			if ($noteDb->note_status != $_POST["note_status"][$key]) $note_changed = true;
			if ($noteDb->note_priority != $_POST["note_priority"][$key]) $note_changed = true;
			if ($noteDb->note_note != $_POST["note_note"][$key]) $note_changed = true;

			if ($note_changed) {
				$gedcom_date = strtoupper(date("d M Y"));
				$gedcom_time = date("H:i:s");
				$sql = "UPDATE humo_user_notes SET
					note_note='" . $editor_cls->text_process($_POST["note_note"][$key]) . "',
					note_status='" . $editor_cls->text_process($_POST["note_status"][$key]) . "',
					note_priority='" . $editor_cls->text_process($_POST["note_priority"][$key]) . "',
					note_changed_user_id='" . $userid . "',
					note_changed_date='" . $gedcom_date . "',
					note_changed_time='" . $gedcom_time . "'
					WHERE note_id='" . $note_id . "'";
				$result = $dbh->query($sql);
			}
		}
	}
}
// *** Remove editor note ***
if (isset($_GET['note_drop']) and is_numeric($_GET['note_drop'])) {
	$confirm .= '<div class="confirm">';
	$confirm .= __('Are you sure you want to remove this event?');
	$confirm .= ' <form method="post" action="' . $phpself . '" style="display : inline;">';
	$confirm .= '<input type="hidden" name="page" value="' . $_GET['page'] . '">';
	$confirm .= '<input type="hidden" name="note_drop" value="' . $_GET['note_drop'] . '">';
	$confirm .= ' <input type="Submit" name="note_drop2" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
	$confirm .= ' <input type="Submit" name="submit" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
	$confirm .= '</form>';
	$confirm .= '</div>';
}
if (isset($_POST['note_drop2']) and is_numeric($_POST['note_drop'])) {
	$sql = "DELETE FROM humo_user_notes WHERE note_id='" . safe_text_db($_POST['note_drop']) . "'";
	$result = $dbh->query($sql);
}

// *** Remove all sources from an item ***
function remove_sources($tree_id, $connect_sub_kind, $connect_connect_id)
{
	global $dbh;
	/*
	// *** Remove (NON-SHARED) source by all connections ***
	$connect_source_sql="SELECT * FROM humo_connections LEFT JOIN humo_sources
		ON source_gedcomnr=connect_source_id
		WHERE connect_tree_id='".$tree_id."' AND source_tree_id='".$tree_id."'
		AND connect_sub_kind='".safe_text_db($connect_sub_kind)."'
		AND connect_connect_id='".safe_text_db($connect_connect_id)."'
		AND source_shared!='1'";
	//echo $connect_source_sql.'<br>';
	$connect_source_qry=$dbh->query($connect_source_sql);
	while($connect_sourceDb=$connect_source_qry->fetch(PDO::FETCH_OBJ)){

// TO DO: ALWAYS REMOVE A CONNECTION, ONLY REMOVE SOURCE IF IT ISN'T SHARED

		$sql="DELETE FROM humo_sources WHERE source_id='".safe_text_db($connect_sourceDb->source_id)."'";
		//echo $sql.'<br>';
		$result=$dbh->query($sql);

		$sql="DELETE FROM humo_connections WHERE connect_id='".safe_text_db($connect_sourceDb->connect_id)."'";
		//echo $sql.'<br>';
		$result=$dbh->query($sql);
	}
	*/
}

// *** Add event. $new_event=false/true ***
// $event_date='event_date'
function add_event($new_event, $event_connect_kind, $event_connect_id, $event_kind, $event_event, $event_gedcom, $event_date, $event_place, $event_text, $multiple_rows = '')
{
	global $dbh, $tree_id, $username, $gedcom_date, $gedcom_time, $editor_cls;

	// *** Generate new order number ***
	$event_order = 1;
	if (!$new_event) {
		$event_sql = "SELECT * FROM humo_events WHERE event_tree_id='" . $tree_id . "'
			AND event_connect_kind='" . $event_connect_kind . "' AND event_connect_id='" . $event_connect_id . "'
			AND event_kind='" . $event_kind . "'
			ORDER BY event_order DESC LIMIT 0,1";
		$event_qry = $dbh->query($event_sql);
		$eventDb = $event_qry->fetch(PDO::FETCH_OBJ);
		$event_order = 0;
		if (isset($eventDb->event_order)) {
			$event_order = $eventDb->event_order;
		}
		$event_order++;
	}

	$sql = "INSERT INTO humo_events SET
		event_tree_id='" . $tree_id . "',
		event_connect_kind='" . $event_connect_kind . "',
		event_connect_id='" . safe_text_db($event_connect_id) . "',
		event_kind='" . $event_kind . "',
		event_event='" . safe_text_db($event_event) . "',
		event_gedcom='" . safe_text_db($event_gedcom) . "',";
	if ($event_date) {
		$sql .= " event_date='" . $editor_cls->date_process($event_date, $multiple_rows) . "',";
	}
	$sql .= " event_place='" . safe_text_db($event_place) . "',
		event_text='" . safe_text_db($event_text) . "',
		event_order='" . $event_order . "',
		event_new_user='" . $username . "',
		event_new_date='" . $gedcom_date . "',
		event_new_time='" . $gedcom_time . "'";
	$result = $dbh->query($sql);
}
