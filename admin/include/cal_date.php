<?php
@set_time_limit(3000);
//@ini_set('memory_limit','-1');

if (!defined('ADMIN_PAGE')) {
	exit;
}

$trees = $db_functions->get_trees();

echo '<h1 class="center">' . __('Calculated birth date') . '</h1>';

echo __('Calculated birth date is an estimated/ calculated date that is used for the privacy filter.<br>
These calculated dates will be used for persons where all dates are missing (no birth, baptise, death or burial dates).<br>
Calculation will be done using birth, baptise, death, burial and marriage dates of persons and these dates of parents and children.') . '<br><br>';

echo '<table class="humo standard" style="width:800px;" border="1">';

echo '<tr class="table_header"><th colspan="2">' . __('Calculated birth date') . '</th></tr>';

echo '<tr><td>' . __('Choose family tree') . '</td>';
echo '<td>';
echo '<form method="POST" action="index.php">';
echo '<input type="hidden" name="page" value="cal_date">';
echo '<select size="1" name="tree_id">';

foreach ($trees as $tree) {
	$treetext = show_tree_text($tree->tree_id, $selected_language);
	$selected = '';
	if (isset($tree_id) and ($tree->tree_id == $tree_id)) {
		$selected = ' SELECTED';
	}
	echo '<option value="' . $tree->tree_id . '"' . $selected . '>' . @$treetext['name'] . '</option>';
}
echo '</select>';

echo ' <input type="Submit" name="submit_button" value="' . __('Select') . '">';
echo '</form>';

echo '</td></tr>';

if (isset($_POST['submit_button']) and isset($tree_id)) {
	$db_functions->set_tree_id($tree_id);

	function calculate_person($gedcomnumber)
	{
		global $db_functions;
		$pers_cal_date = '';
		$person2_db = $db_functions->get_person($gedcomnumber);
		if ($person2_db) {
			if ($person2_db->pers_cal_date) $pers_cal_date = $person2_db->pers_cal_date;
			elseif ($person2_db->pers_birth_date) $pers_cal_date = $person2_db->pers_birth_date;
			elseif ($person2_db->pers_bapt_date) $pers_cal_date = $person2_db->pers_bapt_date;
			$pers_cal_date = substr($pers_cal_date, -4);
		}
		return $pers_cal_date;
	}

	echo '<tr><td colspan="2">';
	// *** Process estimates/ calculated date for privacy filter ***
	$person_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND (pers_cal_date='' OR pers_cal_date IS NULL)";
	$person_result = $dbh->query($person_qry);
	while ($person_db = $person_result->fetch(PDO::FETCH_OBJ)) {
		$pers_cal_date = '';
		if ($person_db->pers_birth_date) $pers_cal_date = $person_db->pers_birth_date;

		elseif ($person_db->pers_bapt_date) $pers_cal_date = $person_db->pers_bapt_date;

		// *** Check first marriage of person ***
		if ($pers_cal_date == '' and $person_db->pers_fams) {
			$marriage_array = explode(";", $person_db->pers_fams);
			$fam_db = $db_functions->get_family($marriage_array[0]);
			if ($fam_db->fam_marr_date) $pers_cal_date = $fam_db->fam_marr_date;
			if ($fam_db->fam_marr_church_date) $pers_cal_date = $fam_db->fam_marr_church_date;
			if ($pers_cal_date) {
				$pers_cal_date = substr($pers_cal_date, -4);
				if ($pers_cal_date) $pers_cal_date = $pers_cal_date - 25;
			}

			// *** Check date of man/ wife ***
			$gedcomnumber = $fam_db->fam_man;
			if ($person_db->pers_gedcomnumber == $fam_db->fam_man) $gedcomnumber = $fam_db->fam_woman;
			$pers_cal_date = calculate_person($gedcomnumber);

			// *** Check date of children ***
			if ($pers_cal_date == '' and $fam_db->fam_children) {
				$children_array = explode(";", $fam_db->fam_children);
				$pers_cal_date = calculate_person($children_array[0]);
				if ($pers_cal_date) $pers_cal_date = $pers_cal_date - 25;
			}
		}

		// *** Check marriage of parents ***
		if ($pers_cal_date == '' and $person_db->pers_famc) {
			$fam_qry = "SELECT fam_man, fam_woman, fam_relation_date, fam_marr_notice_date, fam_marr_date, fam_marr_church_notice_date, fam_marr_church_date, fam_div_date
					FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $person_db->pers_famc . "'";
			$fam_result = $dbh->query($fam_qry);
			$fam_db = $fam_result->fetch(PDO::FETCH_OBJ);
			if ($fam_db->fam_marr_date) $pers_cal_date = $fam_db->fam_marr_date;
			if ($fam_db->fam_marr_church_date) $pers_cal_date = $fam_db->fam_marr_church_date;
			if ($pers_cal_date) {
				$pers_cal_date = substr($pers_cal_date, -4);
				if ($pers_cal_date) $pers_cal_date = $pers_cal_date + 1;
			}

			// *** Check date of father ***
			if ($pers_cal_date == '' and $fam_db->fam_man) {
				$pers_cal_date = calculate_person($fam_db->fam_man);
				if ($pers_cal_date) $pers_cal_date = $pers_cal_date + 25;
			}
			// *** Check date of mother ***
			if ($pers_cal_date == '' and $fam_db->fam_woman) {
				$pers_cal_date = calculate_person($fam_db->fam_woman);
				if ($pers_cal_date) $pers_cal_date = $pers_cal_date + 25;
			}
		}

		if ($pers_cal_date == '' and $person_db->pers_death_date) {
			$pers_cal_date = substr($person_db->pers_death_date, -4);
			if ($pers_cal_date) $pers_cal_date = $pers_cal_date - 60;
		}

		echo '<span style="width:80px; display:inline-block;">' . $person_db->pers_gedcomnumber . '</span> ';
		//use class to show name.
		echo $person_db->pers_firstname . ' ' . strtolower(str_replace("_", " ", $person_db->pers_prefix)) . $person_db->pers_lastname;
		echo ' ' . $pers_cal_date;
		if ($pers_cal_date == '') echo '<b>' . __('No dates') . '</b>';
		echo '<br>';

		$sql = "UPDATE humo_persons SET pers_cal_date='" . $pers_cal_date . "'
				WHERE pers_tree_id='" . $tree_id . "' AND pers_id='" . $person_db->pers_id . "'";
		$result = $dbh->query($sql);
	}
	echo '<b>' . __('Calculation of birth dates is completed. Sometimes more dates will be found if calculation is restarted!') . '</b>';

	echo '</td></tr>';
}

echo '</table>';
