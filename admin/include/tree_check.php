<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

global $selected_language;

echo '<H1 align=center>'.__('Family tree data check').'</H1>';

@set_time_limit(3000);

// for rtl direction in tables
$direction="left";
if($rtlmarker=="rtl") $direction="right";

if(CMS_SPECIFIC=="Joomla") {
	echo '<form method="POST" action="index.php?option=com_humo-gen&amp;task=admin&amp;page=check" style="display : inline;">';
}
else {
	echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" style="display : inline;">';
}

echo '<input type="hidden" name="page" value="'.$page.'">';

if (isset($_POST['tree'])){ $tree=$_POST['tree']; }

echo '<span class="noprint">'.__('Choose tree:');  // class "noprint" hides it when printing
	$tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
	$tree_result = $dbh->query($tree_sql);
	echo '<select size="1" name="tree">';
		while ($treeDb=$tree_result->fetch(PDO::FETCH_OBJ)){
			$treetext=show_tree_text($treeDb->tree_prefix, $selected_language);
			$selected=''; if (isset($tree)){
				if ($treeDb->tree_prefix==$tree){
					$selected=' SELECTED';
					$tree_id=$treeDb->tree_id;
					$db_functions->set_tree_id($tree_id);
				}
			}
			echo '<option value="'.$treeDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
		}
	echo '</select>';
echo '</span>';

// menu of data check page
echo '<br><br><input type="hidden" name="page" value="'.$page.'">';
echo '<span class="noprint">';
//echo '<table class="humo" style="width:95%; text-align:center; border:1px solid black;"><tr class="table_header_large">';
echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large">';
echo '<td><input type="Submit" name="data_check" value="'.__('Check consistency of dates').'"></td>';
echo '<td>';
echo '<input type="Submit" name="invalid_dates" value="'.__('Find invalid dates').'">';
echo '</td><td>';
echo '<input type="Submit" name="database_check" value="'.__('Check database integrity').'">';
echo '</td><td>';
echo '<input type="Submit" name="last_changes" value="'.__('View latest changes').'">';
echo '</td></tr></table>';
echo '</span>';

//echo '<br><table class="humo" style="width:95%; text-align:center; border:1px solid black;"><tr><td>'; 
echo '<br><table class="humo standard" style="text-align:center;"><tr><td>'; 

if(!isset($_POST['last_changes']) AND !isset($_POST['database_check']) AND !isset($_POST['data_check'])
		AND !isset($_POST['final_check']) AND !isset($_POST['unmark']) 
		AND !isset($_POST['mark_all']) AND !isset($_POST['invalid_dates'])){
// displays explanations on entry page to data check items
	echo '<table style="text-align:'.$direction.';border:none"><tr><td style="border:none">';
	echo '<br><b>'.__('Check consistency of dates').'</b><br>';
	echo __('With this option you can check the consistency of the dates in your database. For example: birth date after death date, marriage date at age 7, birth date 80 years after mother\'s birth date etc.').'<br>';
	echo __('You can perform the check with all options, or choose only certain options.').'<br>';
	echo __('You can also change default settings for the checks to be performed.').'<br><br>';
	echo '<b>'.__('Check invalid dates').'</b><br>';
	echo __('With this option you can check the database for invalid dates. You will be given a link to edit the errors.').'<br>';
	echo __('This item checks for impossible dates (such as "31 apr 1920"), future dates, incomplete dates ("3 apr") and invalid gedcom date entries.').'<br>';
	echo __('Tip for gedcom validation (case is irrelevant):').'<br>';
	echo __('Only valid month notation: "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"').'<br>';
	echo __('Only valid single prefixes: "bef", "aft", "abt", "est", "int", "cal"').'<br>';
	echo __('Only valid double prefixes: "from 1898 to 1899", "bet 1850 and 1860"').'<br>';
	echo __('Invalid gedcom entries: "1877-1879" (-> bet 1877 and 1879), "12 april 2003" (-> 12 apr 2003), "cir 1884" (-> abt 1884), "1845 ?" (abt 1845)').'<br><br>';
	echo '<b>'.__('Check database integrity').'</b><br>';
	echo __('With this option you can check the integrity of the tables in the MySQL database.').'<br>';
	echo __('If inconsistencies exist they may lead to people being disconnected from relatives or misplaced.').'<br><br>';
	echo '<b>'.__('Latest changes').'</b><br>';
	echo __('Here you can view the latest changes that were made to data in your database.').'<br><br>';
	echo '</td><tr>';
}
 
if (isset($_POST['tree']) AND isset($_POST['last_changes'])){
	// *** Show latest changes and additions ***
	//$person_qry= "(SELECT *, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS datum
	//	FROM ".$tree."person)";
	$person_qry= "(SELECT *, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS datum
		FROM humo_persons WHERE pers_tree_id='".$tree_id."')
		UNION (SELECT *, STR_TO_DATE(pers_new_date,'%d %b %Y') AS datum
		FROM humo_persons WHERE pers_tree_id='".$tree_id."')
		ORDER BY datum DESC, pers_changed_time DESC LIMIT 0,50";

	$person_result = $dbh->query($person_qry);
	echo '<h3>'.__('Latest changes').'</h3>';
	echo '<div style="margin-left:auto;margin-right:auto;height:350px;width:60%; overflow-y: scroll;">';
	echo '<table class="humo" style="width:100%">';

	echo '<tr>';
	echo '<th style="font-size: 90%; text-align: center">'.__('Changed/ Added').'</th>';
	echo '<th style="font-size: 90%; text-align: center">'.__('When changed').'</th>';
	echo '<th style="font-size: 90%; text-align: center">'.__('When added').'</th>';
	echo '</tr>';

	while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
		echo '<tr><td>';
		if(CMS_SPECIFIC=="Joomla") {
			echo '	<a href="index.php?option=com_humo-gen&amp;task=family&amp;database='.$tree.'&amp;id='.$person->pers_indexnr.'&amp;main_person='.$person->pers_gedcomnumber.'">';
		}
		else {
			echo '<a href="../family.php?database='.$tree.'&amp;id='.$person->pers_indexnr.'&main_person='.$person->pers_gedcomnumber.'">';
		}
		echo $person->pers_firstname.' '.$person->pers_prefix.$person->pers_lastname;
		echo '</a>';
		echo '</td><td>';
		echo '<nobr>'.strtolower($person->pers_changed_date).' '.$person->pers_changed_time.'</nobr>';
		echo '</td><td>';
		echo '<nobr>'.strtolower($person->pers_new_date).' '.$person->pers_new_time.'</nobr>';
		echo '</td>';
		echo "</tr>\n";
	}
	echo '</table><br><br>';
	echo '</div>';

}
if (isset($_POST['tree']) AND isset($_POST['database_check'])){
	// *** Check tables for wrongly connected id's etc. ***
	//$person_qry= "SELECT * FROM ".$tree."person ORDER BY pers_lastname, pers_firstname";
	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY pers_lastname, pers_firstname";
	$person_result = $dbh->query($person_qry);
	echo '<h3>'.__('Checking database tables...').'<br>Please wait till finished</h3>';
	//echo '<div style="height: 200px; overflow-y: scroll;">';
	//echo '<table class="humo">';
	$wrong_indexnr=0;
	$wrong_famc=0;
	$wrong_fams=0;

	while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
		$check=false;
		$pers_indexnr='';
		if ($person->pers_famc){ $pers_indexnr=$person->pers_famc; }
		if ($person->pers_fams){
			$pers_fams=explode(';',$person->pers_fams);
			$pers_indexnr=$pers_fams[0];
		}
		if ($person->pers_indexnr==$pers_indexnr){ $check=true; }

		if ($check==false){
			$wrong_indexnr++;
			echo $person->pers_gedcomnumber.' famc: '.$person->pers_famc.' fams: '.$person->pers_fams.
			' index:'.$person->pers_indexnr;
			echo ' <b>Wrong person indexnumber (is restored now)!!</b><br>';

			// *** Restore pers_indexnr ***
			$sql="UPDATE humo_persons SET pers_indexnr='".$pers_indexnr."'
				WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$person->pers_gedcomnumber."'";
			$result=$dbh->query($sql);
		}
		//else { echo ' indexnr ok.<br>'; }

		if ($person->pers_fams){
			$check_fams1=false;
			$fams=explode(";", $person->pers_fams);
			for ($i=0; $i<=count($fams)-1; $i++){
				$fam_qry= "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$fams[$i]."'";
				$fam_result = $dbh->query($fam_qry);
				$famDb=$fam_result->fetch(PDO::FETCH_OBJ);
				if ($famDb){
					if ($famDb->fam_man==$person->pers_gedcomnumber){ $check_fams1=true; }
					if ($famDb->fam_woman==$person->pers_gedcomnumber){ $check_fams1=true; }
				}
				// NO RESTORE YET (maybe not possible to do a restore?).
			}
			if ($check_fams1==false){
				$wrong_fams++; echo ' <b>person-relation problem!</b> Please check:';
				echo ' Person gedcomnumber: '.$person->pers_gedcomnumber;
				echo ', connected relation: '.$person->pers_fams.'<br>';
			}
		}

		if ($person->pers_famc){
			$fam_qry= "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$person->pers_famc."'";
			$fam_result = $dbh->query($fam_qry);
			$famDb=$fam_result->fetch(PDO::FETCH_OBJ);

			$check_children=false;
			$children=explode(";", $famDb->fam_children);
				for ($i=0; $i<=count($children)-1; $i++){
				if ($children[$i]==$person->pers_gedcomnumber){ $check_children=true; }
			}
			if ($check_children==false) {
				echo ' fam: '.$famDb->fam_gedcomnumber.' children: '.$famDb->fam_children;
				echo ' child: '.$person->pers_gedcomnumber;
				echo ' <b> Wrong child-parent connection!!</b><br>';
				$wrong_famc++;
// NO RESTORE YET
			}
		}
	}

	$wrong_children=0;
	$fam_qry= "SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_children LIKE '_%'";
	$fam_result = $dbh->query($fam_qry);
	while($famDb=$fam_result->fetch(PDO::FETCH_OBJ)){
		$children=explode(";", $famDb->fam_children);
		for ($i=0; $i<=count($children)-1; $i++){
			$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$children[$i]."'";
			$person_result = $dbh->query($person_qry);
			$person=$person_result->fetch(PDO::FETCH_OBJ);
			if ($person){
				//echo ' fam: '.$famDb->fam_gedcomnumber.' children: '.$famDb->fam_children.' ';
				//echo ' gedcom: '.$person->pers_gedcomnumber.' famc: '.$person->pers_famc;
				if ($person->pers_famc==''){
					$sql="UPDATE humo_persons SET pers_famc='".$famDb->fam_gedcomnumber."'
						WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$person->pers_gedcomnumber."'";
					$result=$dbh->query($sql);
					echo ' <b> Wrong parent-child connection (is restored now)!</b><br>';
				}
			}
			else{
				$wrong_children++;
				echo ' fam: '.$famDb->fam_gedcomnumber.' children: '.$famDb->fam_children.' ';
				echo ' gedcom: '.$person->pers_gedcomnumber.' famc: '.$person->pers_famc;
				echo ' <b> Wrong parent-child connection!</b><br>';
// NO RESTORE YET
			}
		}
	}

	if ($wrong_indexnr==0){ echo '<p>'.__('Checked all person index numbers:').' ok.'; }
	if ($wrong_fams==0){ echo '<p>'.__('Checked all person-relation connections:').' ok.'; }
	if ($wrong_famc==0){ echo '<p>'.__('Checked all child-parent connections:').' ok.'; }
	if ($wrong_children==0){ echo '<p>'.__('Checked all parent-child connections:').' ok.'; }

}

if (isset($_POST['tree']) AND isset($_POST['invalid_dates'])){ 
// displays results of validity check (with help of the invalid() function)

	echo '<table style="width:100%">';
	echo '<tr><th style="width:10%;border:1px solid black">'.__('ID').
		'</th><th style="width:55%;border:1px solid black">'.__('Edit invalid date').
		'</th><th style="width:20%;border:1px solid black">'.__('Details').
		'</th><th style="width:15%;border:1px solid black">'.__('Invalid date').'</th></tr>';
	echo '<tr><td colspan="4" style="text-align:'.$direction.';font-weight:bold">'.__('Invalid person dates:').'</td></tr>';
	$found = false; // if this stays false, displays message that no problems where found
	$person = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY pers_lastname,pers_firstname");
	while($persdateDb=$person->fetch()){
		if(isset($persdateDb['pers_birth_date']) AND $persdateDb['pers_birth_date']!='')
	 		$result = invalid($persdateDb['pers_birth_date'],$persdateDb['pers_gedcomnumber'],'pers_birth_date');
			if($result===true) {$found = true; }
		if(isset($persdateDb['pers_bapt_date']) AND $persdateDb['pers_bapt_date']!='') 
			$result = invalid($persdateDb['pers_bapt_date'],$persdateDb['pers_gedcomnumber'],'pers_bapt_date');
			if($result===true) {$found = true;}
		if(isset($persdateDb['pers_death_date']) AND $persdateDb['pers_death_date']!='')
 			$result = invalid($persdateDb['pers_death_date'],$persdateDb['pers_gedcomnumber'],'pers_death_date');
			if($result===true) {$found = true;}
		if(isset($persdateDb['pers_buried_date']) AND $persdateDb['pers_buried_date']!='') 
			$result = invalid($persdateDb['pers_buried_date'],$persdateDb['pers_gedcomnumber'],'pers_buried_date');
			if($result===true) {$found = true; }
	}
	if($found===false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

	echo '<tr><td colspan="4" style="text-align:'.$direction.';font-weight:bold">'.__('Invalid family dates:').'</td></tr>';
	$found = false;
	$family = $dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."'");
	while($famdateDb=$family->fetch()){
		if(isset($famdateDb['fam_div_date']) AND $famdateDb['fam_div_date']!='')
 			$result = invalid($famdateDb['fam_div_date'],$famdateDb['fam_gedcomnumber'],'fam_div_date');
			if($result===true) {$found = true; }
		if(isset($famdateDb['fam_marr_church_date']) AND $famdateDb['fam_marr_church_date']!='')
 			$result = invalid($famdateDb['fam_marr_church_date'],$famdateDb['fam_gedcomnumber'],'fam_marr_church_date');
			if($result===true) {$found = true; }
		if(isset($famdateDb['fam_marr_church_notice_date']) AND $famdateDb['fam_marr_church_notice_date']!='')
 			$result = invalid($famdateDb['fam_marr_church_notice_date'],$famdateDb['fam_gedcomnumber'],'fam_marr_church_notice_date');
			if($result===true) {$found = true; }
		if(isset($famdateDb['fam_marr_date']) AND $famdateDb['fam_marr_date']!='')
			$result = invalid($famdateDb['fam_marr_date'],$famdateDb['fam_gedcomnumber'],'fam_marr_date');
			if($result===true) {$found = true; }
		if(isset($famdateDb['fam_marr_notice_date']) AND $famdateDb['fam_marr_notice_date']!='')
		 	$result = invalid($famdateDb['fam_marr_notice_date'],$famdateDb['fam_gedcomnumber'],'fam_marr_notice_date');
			if($result===true) {$found = true; }
		if(isset($famdateDb['fam_relation_date']) AND $famdateDb['fam_relation_date']!='') 
			$result = invalid($famdateDb['fam_relation_date'],$famdateDb['fam_gedcomnumber'],'fam_relation_date');
			if($result===true) {$found = true; }
	}
	if($found===false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

	echo '<tr><td colspan="4" style="text-align:'.$direction.';font-weight:bold">'.__('Invalid event dates:').'</td></tr>';
	$found = false;
	$event = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."'");
	while($eventdateDb=$event->fetch()){ 
		if(isset($eventdateDb['event_date']) AND $eventdateDb['event_date']!='')
 	 		$result = invalid($eventdateDb['event_date'],$eventdateDb['event_id'],'event_date');
			if($result===true) {$found = true; }
	}
	if($found===false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

	echo '<tr><td colspan="4" style="text-align:'.$direction.';font-weight:bold">'.__('Invalid connection dates:').'</td></tr>';
	$found = false;
	//$connection = $dbh->query("SELECT * FROM ".$tree."connections");
	$connection = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='".$tree_id."'");
	while($connectdateDb=$connection->fetch()){
		if(isset($connectdateDb['connect_date']) AND $connectdateDb['connect_date']!='')
  			$result = invalid($connectdateDb['connect_date'],$connectdateDb['connect_id'],'connect_date');
			if($result===true) {$found = true; }
	}
	if($found===false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

	echo '<tr><td colspan="4" style="text-align:'.$direction.';font-weight:bold">'.__('Invalid address dates:').'</td></tr>';
	$found = false;
	//$address = $dbh->query("SELECT * FROM ".$tree."addresses");
	$address = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='".$tree_id."'");
	while($addressdateDb=$address->fetch()){
		if(isset($addressdateDb['address_date']) AND $addressdateDb['address_date']!='')
  			$result = invalid($addressdateDb['address_date'],$addressdateDb['address_id'],'address_date');
			if($result===true) {$found = true; }
	}
	if($found===false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

	echo '<tr><td colspan="4" style="text-align:'.$direction.';font-weight:bold">'.__('Invalid repository dates:').'</td></tr>';
	$found = false;
	//$repo = $dbh->query("SELECT * FROM ".$tree."repositories");
	$repo = $dbh->query("SELECT * FROM humo_repositories
		WHERE repo_tree_id='".$tree_id."'");
	while($repodateDb=$repo->fetch()){ 
		if(isset($repodateDb['repo_date']) AND $repodateDb['repo_date']!='')  
			$result = invalid($repodateDb['repo_date'],$repodateDb['repo_gedcomnr'],'repo_date');
			if($result===true) {$found = true; }
	}
	if($found===false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

	echo '<tr><td colspan="4" style="text-align:'.$direction.';font-weight:bold">'.__('Invalid source dates:').'</td></tr>';
	$found = false;
	//$sources = $dbh->query("SELECT * FROM ".$tree."sources");
	$sources = $dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='".$tree_id."'");
	while($sourcedateDb=$sources->fetch()){
		if(isset($sourcedateDb['source_date']) AND $sourcedateDb['source_date']!='')
			$result = invalid($sourcedateDb['source_date'],$sourcedateDb['source_gedcomnr'],'source_date');
			if($result===true) {$found = true; }
	}  
	if($found===false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';
	echo '</table>';
}

if (isset($_POST['tree']) AND (isset($_POST['data_check']) OR isset($_POST['unmark']) OR isset($_POST['mark_all']))){
// displays menu for date consistency check

	// easily set other defaults:
	$b1_def    = 50;  //Birth date - more than X years after mother's birth
	$b2_def    = 60;  //Birth date - more than X years after father's birth
	$b3_def    = 15;  //Birth date - less than X years after mother's birth
	$b4_def    = 15;  //Birth date - less than X years after father's birth
	$bp1_def   = 50;  //Baptism date - more than X years after mother's birth
	$bp2_def   = 60;  //Baptism date - more than X years after father's birth
	$bp3_def   = 15;  //Baptism date - less than X years after mother's birth
	$bp4_def   = 15;  //Baptism date - less than X years after father's birth
	$marr1_def = 15;  //Marriage date(s) - less than X years after birth date
	$marr2_def = 30;  //Marriage age - age difference of more than X years between partners
	$age1_def  = 100; //Age (by death date) - more than X years
	$age2_def  = 100; //Age (by burial date) - more than X years
	$age3_def  = 100; //Age (up till today) - more than X years 
	$b5_def	  = 9;   //Birth date - less than 9 months after parents' wedding date
	$b6_def	  = 9;   //Birth date - less than 9 months after previous sibbling

	$checked = " checked"; if(isset($_POST['unmark'])) $checked=''; if(isset($_POST['mark_all'])) $checked=' checked';
	echo '<h3>'.__('Check consistency of dates').'</h3>';
	echo __('You can mark or unmark any of the check options and change defaults. Then press ');
	echo '<input type="Submit" style="font-size:120%" name="final_check" value="'.__('Check').'"><br>';
	echo __('(with default settings a full check may take between 12-15 seconds per 10,000 persons)').'<br><br>';
	if($rtlmarker=="ltr") echo '<table class="humo" style="width:100%;text-align:left;border:none"><tr><td style="border:none;width:50%">';
	else echo '<table class="humo" style="width:100%;text-align:right;border:none"><tr><td style="border:none;width:50%">';
	echo '<input type="Submit" style="font-size:90%" name="unmark" value="'.__('Unmark all options').'">&nbsp;'; 
	echo '<input type="Submit" style="font-size:90%" name="mark_all" value="'.__('Mark all options').'"><br>'; 
	echo '<input type="checkbox" id="1" name="birth_date1" value="1" '.$checked.'>'.__('Birth date - after bapt/marr/death/burial date').'<br>';
	// id 2 was moved to end
	echo '<input type="checkbox" id="3" name="birth_date3" value="1" '.$checked.'>'.__('Birth date - more than ');
	echo '<input type="text" name="birth_date3_nr" style="width:30px" value="'.$b1_def.'">';
	echo __(' years after mother\'s birth').'<br>';
	echo '<input type="checkbox" id="4" name="birth_date4" value="1" '.$checked.'>'.__('Birth date - more than ');
	echo '<input type="text" name="birth_date4_nr" style="width:30px" value="'.$b2_def.'">';
	echo __(' years after father\'s birth').'<br>';
	echo '<input type="checkbox" id="5" name="birth_date5" value="1" '.$checked.'>'.__('Birth date - less than ');
	echo '<input type="text" name="birth_date5_nr" style="width:30px" value="'.$b3_def.'">';
	echo __(' years after mother\'s birth').'<br>';
	echo '<input type="checkbox" id="6" name="birth_date6" value="1" '.$checked.'>'.__('Birth date - less than ');
	echo '<input type="text" name="birth_date6_nr" style="width:30px" value="'.$b4_def.'">';
	echo __(' years after father\'s birth').'<br>';
//NEW
	echo '<input type="checkbox" id="23" name="birth_date7" value="1" '.$checked.'>'.__('Birth date - less than ');
	echo '<input type="text" name="birth_date7_nr" style="width:30px" value="'.$b5_def.'">';
	echo __(' months after wedding parents').'<br>';
	echo '<input type="checkbox" id="24" name="birth_date8" value="1" '.$checked.'>'.__('Birth date - before wedding parents').'<br>';
	echo '<input type="checkbox" id="25" name="birth_date9" value="1" '.$checked.'>'.__('Birth date - less than ');
	echo '<input type="text" name="birth_date9_nr" style="width:30px" value="'.$b6_def.'">';
	echo __(' months after previous child of mother').'<br>';
//END NEW
	echo '<input type="checkbox" id="7" name="baptism_date1" value="1" '.$checked.'>'.__('Baptism date - after death/burial date').'<br>';
	// id 8 was joined in with id 2
	echo '<input type="checkbox" id="9" name="baptism_date3" value="1" '.$checked.'>'.__('Baptism date - more than ');
	echo '<input type="text" name="baptism_date3_nr" style="width:30px" value="'.$bp1_def.'">';
	echo __(' years after mother\'s birth').'<br>';
	echo '<input type="checkbox" id="10" name="baptism_date4" value="1" '.$checked.'>'.__('Baptism date - more than ');
	echo '<input type="text" name="baptism_date4_nr" style="width:30px" value="'.$bp2_def.'">';
	echo __(' years after father\'s birth').'<br>';
	echo '<input type="checkbox" id="11" name="baptism_date5" value="1" '.$checked.'>'.__('Baptism date - less than ');
	echo '<input type="text" name="baptism_date5_nr" style="width:30px" value="'.$bp3_def.'">';
	echo __(' years after mother\'s birth').'<br>';
	echo '<input type="checkbox" id="12" name="baptism_date6" value="1" '.$checked.'>'.__('Baptism date - less than ');
	echo '<input type="text" name="baptism_date6_nr" style="width:30px" value="'.$bp4_def.'">';
	echo __(' years after father\'s birth').'<br>';
	echo '</td><td style="border:none;width:50%"><br>';
	echo '<input type="checkbox" id="13" name="marriage_date1" value="1" '.$checked.'>'.__('Marriage date(s) - after death/burial date').'<br>';
	echo '<input type="checkbox" id="14" name="marriage_date2" value="1" '.$checked.'>'.__('Marriage date(s) - less than ');
	echo '<input type="text" name="marriage_date2_nr" style="width:30px" value="'.$marr1_def.'">';
	echo __(' years after birth date').'<br>';
	echo '<input type="checkbox" id="15" name="marriage_age" value="1" '.$checked.'>'.__('Marriage age - age difference of more than ');
	echo '<input type="text" name="marriage_age_nr" style="width:30px" value="'.$marr2_def.'">';
	echo __(' years between partners').'<br>';
 	echo '<input type="checkbox" id="16" name="death_date1" value="1" '.$checked.'>'.__('Death date - after burial date').'<br>';
 	echo '<input type="checkbox" id="17" name="death_date2" value="1" '.$checked.'>'.__('Death date - bef birth of mother').'<br>';
 	echo '<input type="checkbox" id="18" name="death_date3" value="1" '.$checked.'>'.__('Death date - bef birth of father').'<br>';
	echo '<input type="checkbox" id="19" name="burial_date1" value="1" '.$checked.'>'.__('Burial date - bef birth of mother').'<br>';
	echo '<input type="checkbox" id="20" name="burial_date2" value="1" '.$checked.'>'.__('Burial date - bef birth of father').'<br>';
	echo '<input type="checkbox" id="21" name="age1" value="1" '.$checked.'>'.__('Age (by death date) - more than ');
	echo '<input type="text" name="age1_nr" style="width:30px" value="'.$age1_def.'">';
	echo __(' years').'<br>';	
	echo '<input type="checkbox" id="22" name="age2" value="1" '.$checked.'>'.__('Age (by burial date) - more than ');
	echo '<input type="text" name="age2_nr" style="width:30px" value="'.$age2_def.'">';
	echo __(' years').'<br>';  
	// since displaying people with no death/bur date and not marked as deceased might give a long list, this is not checked by default
	echo '<input type="checkbox" id="2" name="birth_date2" value="1">'.__('Age (up till today) - more than ');
	echo '<input type="text" name="birth_date2_nr" style="width:30px" value="'.$age3_def.'">';
	echo __(' years <b>(may give long list!)').'</b><br>';	
	//echo '<br>';
	echo '</td></tr></table>'; 
}

if (isset($_POST['final_check'])){
// performs the date consistency check
	echo '<h3>'.__('Results').'</h3>';
	if($rtlmarker=="ltr") echo '<table class="humo" style="width:100%;text-align:left">';
	else echo '<table class="humo" style="width:100%;text-align:right">';
	echo '<tr><th style="width:20%;border:1px solid black;text-align:center;padding-left:5px;padding-right:5px">'.__('Person').'</th>';
	echo '<th style="width:10%;border:1px solid black;text-align:center;padding-left:5px;padding-right:5px">'.__('ID').'</th>';
	echo '<th style="width:35%;border:1px solid black;text-align:center;padding-left:5px;padding-right:5px">'.__('Possible consistency problems').'</th>';
	echo '<th style="width:35%;border:1px solid black;text-align:center;padding-left:5px;padding-right:5px">'.__('Details').'</th></tr>';

	$results_found=0;

	//$person = $dbh->query("SELECT * FROM ".$tree."person ORDER BY pers_lastname,pers_firstname");
	$person = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY pers_lastname,pers_firstname");
	while($personDb=$person->fetch()){

		/*	// using class slows down considerably: 10,000 persons without class 15 sec, with class for name: over 4 minutes...
			$persclass = New person_cls;
			$persclass->construct($personDb);
			$name=$persclass->person_name($personDb); 
		*/
		$name = $personDb['pers_lastname'].", ".$personDb['pers_firstname'].' '.str_replace("_"," ",$personDb['pers_prefix']);

		// person's dates
		$b_date='';  if(isset($personDb['pers_birth_date'])) $b_date = $personDb['pers_birth_date'];
		$bp_date=''; if(isset($personDb['pers_bapt_date'])) $bp_date = $personDb['pers_bapt_date'];
		$d_date='';  if(isset($personDb['pers_death_date'])) $d_date = $personDb['pers_death_date'];
		$bu_date=''; if(isset($personDb['pers_buried_date'])) $bu_date = $personDb['pers_buried_date'];
	 
		// marriage(s) dates and spouses birth date
		if(isset($personDb['pers_fams'])) {  
			$marr_dates = array(); // marriage dates array
			$marr_notice_dates = array(); // marriage notice dates array
			$marr_church_dates = array(); // marriage church dates array
			$marr_church_notice_dates = array(); // marriage church notice dates array
			$spouse_dates = array(); // array of spouse birth dates
			$marr_array = array(); // array of marriage gedcomnumbers
			$spouse = "fam_woman"; if($personDb['pers_sexe']=="F") $spouse = "fam_man";
			$marr_array = explode(';',$personDb['pers_fams']); 

			for($x=0;$x<count($marr_array);$x++) {
				//$marriages = $dbh->query("SELECT fam_marr_date, fam_marr_notice_date, fam_marr_church_date, fam_marr_church_notice_date, ".$spouse." FROM ".$tree."family WHERE fam_gedcomnumber ='".$marr_array[$x]."'");
				$marriages = $dbh->query("SELECT fam_marr_date, fam_marr_notice_date, fam_marr_church_date, fam_marr_church_notice_date, ".$spouse." 
					FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$marr_array[$x]."'");
				$marriagesDb=$marriages->fetch(PDO::FETCH_OBJ);
				if($marriagesDb !== false) { 
					$marr_dates[$x] = $marriagesDb->fam_marr_date;  
					$marr_notice_dates[$x] = $marriagesDb->fam_marr_notice_date; 
					$marr_church_dates[$x] = $marriagesDb->fam_marr_church_date; 
					$marr_church_notice_dates[$x] = $marriagesDb->fam_marr_church_notice_date; 
					if($personDb['pers_sexe']=="F") { 
						$spouses =  $dbh->query("SELECT pers_birth_date FROM humo_persons 
							WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$marriagesDb->fam_man."'");
					}
					else {  
						$spouses =  $dbh->query("SELECT pers_birth_date FROM humo_persons 
							WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$marriagesDb->fam_woman."'");
					}
					$spousesDb = $spouses->fetch(PDO::FETCH_OBJ);
					if(isset($spousesDb->pers_birth_date)) $spouse_dates[] = $spousesDb->pers_birth_date; 
				}
			} 
		}
	 
		// parents' dates
		$m_b_date=''; // mother's birth date
		$f_b_date=''; // father's birth date
		$par_marr_date=''; // parents' wedding date
		$sib_b_date=''; // previous sibling birth date
		$m_fams=''; // marriage(s) of mother (to find previous sibling)
		$m_fams_arr = array(); // marriage(s) array of mother (to find previous sibling)
		
		if(isset($personDb['pers_famc'])) { 
			$parents = $dbh->query("SELECT fam_gedcomnumber, fam_man, fam_woman, fam_children, fam_marr_date, fam_marr_church_date, fam_marr_notice_date, fam_relation_date
				FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber ='".$personDb['pers_famc']."'");
			$parentsDb=$parents->fetch(PDO::FETCH_OBJ);
//NEW - find parents wedding date
			if(isset($parentsDb->fam_marr_date)) { 
				$par_marr_date = $parentsDb->fam_marr_date;
			}
			elseif(isset($parentsDb->fam_marr_church_date)) { // if no civil date try religious marriage
				$par_marr_date = $parentsDb->fam_marr_church_date;
			} 
			elseif(isset($parentsDb->fam_marr_notice_date)) { // if no civil or religious date, try notice date
				$par_marr_date = $parentsDb->fam_marr_notice_date;
			} 
			elseif(isset($parentsDb->fam_marr_relation_date)) { // if non of above try relation date
				$par_marr_date = $parentsDb->fam_relation_date;
			} 
//END NEW
			if(isset($parentsDb->fam_woman)) {  
				$mother = $dbh->query("SELECT pers_birth_date, pers_fams FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$parentsDb->fam_woman."'");
				$motherDb=$mother->fetch(PDO::FETCH_OBJ);
				if(isset($motherDb->pers_birth_date)) $m_b_date = $motherDb->pers_birth_date;
				if(isset($motherDb->pers_fams)) { 
					$m_fams = $motherDb->pers_fams; // needed for sibling search
					$m_fams_arr = explode(";",$m_fams);
				}
			}
			if(isset($parentsDb->fam_man)) {  
				$father = $dbh->query("SELECT pers_birth_date FROM humo_persons 
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$parentsDb->fam_man."'");
				$fatherDb=$father->fetch(PDO::FETCH_OBJ);
				if(isset($fatherDb->pers_birth_date)) $f_b_date = $fatherDb->pers_birth_date;
			}
//NEW - find previous born sibling
			if(isset($parentsDb->fam_children)) {   
				$ch_array = explode(";",$parentsDb->fam_children);
				$num_ch = count($ch_array); // number of children
				$first_ch = 0;
				if($num_ch > 1) {  // more than 1 children
					$count=0;   
					while($ch_array[$count]!=$personDb['pers_gedcomnumber']) {
						$count++;
					}	
					if($count>0) {  // person is not first child
						$prev_sib_gednr = $ch_array[$count-1]; // gedcomnumber of previous sibling
						$sib = $dbh->query("SELECT pers_birth_date FROM humo_persons
							WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$prev_sib_gednr."'");
						$sibDb = $sib->fetch(PDO::FETCH_OBJ);
						if(isset($sibDb->pers_birth_date) AND $sibDb->pers_birth_date!='' ) {
							$sib_b_date = $sibDb->pers_birth_date;
						}
					}
					elseif($count==0) { $first_ch=1; }	// this is first child in own fam
				}
				if($num_ch==1 OR $first_ch==1) { // if this only or first child in this marriage - look for previous marriage of mother
					if(isset($m_fams_arr) AND count($m_fams_arr)>1 AND $m_fams_arr[0]!=$parentsDb->fam_gedcomnumber) { 
						// if mother has more than one marriage and this is not the first, then look for last child in previous marriage
						$count=0;
						while($m_fams_arr[$count]!=$parentsDb->fam_gedcomnumber) {
							$count++;
						}
						$prev_marr_ged = $m_fams_arr[$count-1];
						$prev_marr = $dbh->query("SELECT * FROM humo_families
							WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".$prev_marr_ged."'");
						$prev_marrDb = $prev_marr->fetch(PDO::FETCH_OBJ);
						if(isset($prev_marrDb->fam_children)) { 
							$prev_ch_arr = explode(";",$prev_marrDb->fam_children);
							$prev_ch_num = count($prev_ch_arr);
							$prev_ch_ged = $prev_ch_arr[$prev_ch_num-1]; // last child
							$sib = $dbh->query("SELECT * FROM humo_persons
								WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber ='".$prev_ch_ged."'");
							$sibDb = $sib->fetch(PDO::FETCH_OBJ);
							if(isset($sibDb->pers_birth_date) AND $sibDb->pers_birth_date!='' ) { 
								$sib_b_date = $sibDb->pers_birth_date; 
							}
						}
					}
				}
			}
//END NEW		
		}

		if($b_date=='' AND $bp_date=='' AND $d_date=='' AND $bu_date=='' 
				AND $m_b_date=='' AND $f_b_date=='' AND !isset($personDb['pers_fams'])) {
			continue; // if no relevant dates at all - don't bother - move to next person
		}

		if($b_date!='') {
			 
			// ID 1 -  Birth date - after bapt/marr/death/burial date

			if(isset($_POST["birth_date1"]) AND $_POST["birth_date1"]=="1") {		
				   
				if($bp_date!='' AND compare_seq($b_date,$bp_date)=="2") {
					write_pers($name, "1",$b_date,$bp_date,__("birth date"),__("baptism date"),0);
					$results_found++;
				}
				if($d_date!='' AND compare_seq($b_date,$d_date)=="2") {  
					write_pers($name, "1",$b_date,$d_date,__("birth date"),__("death date"),0);
					$results_found++;
				}
				if($bu_date!='' AND compare_seq($b_date,$bu_date)=="2") {
					write_pers($name, "1",$b_date,$bu_date,__("birth date"),__("burial date"),0);
					$results_found++;
				}  
				for($i=0;$i<count($marr_dates);$i++) {
					if(isset($marr_dates[$i]) AND compare_seq($b_date,$marr_dates[$i])=="2") {
						write_pers($name, "1",$b_date,$marr_dates[$i],__("birth date"),__("marriage"),0);
						$results_found++;
					}
				}  
			}
			   
			// ID 3 - Birth date more than X years after mother's birth date

			if(isset($_POST["birth_date3"]) AND $_POST["birth_date3"]=="1") {
				if($m_b_date!='') { 
					$gap = compare_gap($m_b_date,$b_date);
					if($gap!==false AND $gap > $_POST["birth_date3_nr"]) {  
						write_pers($name, "3",$b_date,$m_b_date,__("birth date"),__('mother'),$_POST["birth_date3_nr"]);
						$results_found++;
					}
				}
			}
			 
			// ID 4 - Birth date more than X years after father's birth date

			if(isset($_POST["birth_date4"]) AND $_POST["birth_date4"]=="1") {
				if($f_b_date!='') { 
					$gap = compare_gap($f_b_date,$b_date);
					if($gap!==false AND $gap > $_POST["birth_date4_nr"]) {  
						write_pers($name, "4",$b_date,$f_b_date,__("birth date"),__('father'),$_POST["birth_date4_nr"]);
						$results_found++;
					}
				}
			}
			  
			// ID 5 - Birth date less than X years after mother's birth date

			if(isset($_POST["birth_date5"]) AND $_POST["birth_date5"]=="1") {
				if($m_b_date!='') { 
					$gap = compare_gap($m_b_date,$b_date);
					if($gap!==false AND $gap < $_POST["birth_date5_nr"]) {  
						write_pers($name, "5",$b_date,$m_b_date,__("birth date"),__('mother'),$_POST["birth_date5_nr"]);
						$results_found++;
					}
				}
			}
			  
			// ID 6 - Birth date less than X years after father's birth date

			if(isset($_POST["birth_date6"]) AND $_POST["birth_date6"]=="1") { 
				if($f_b_date!='') { 
					$gap = compare_gap($f_b_date,$b_date);
					if($gap!==false AND $gap < $_POST["birth_date6_nr"]) {  
						write_pers($name, "6",$b_date,$f_b_date,__("birth date"),__('father'),$_POST["birth_date6_nr"]);
						$results_found++;
					}
				}
			}  
			
//NEW
			// ID 23 - Birth date less than X months after parents' wedding date
 
			if(isset($_POST["birth_date7"]) AND $_POST["birth_date7"]=="1") { 
				if($par_marr_date!='' AND compare_seq($par_marr_date,$b_date)!="2") { 
					$gap = compare_month_gap($par_marr_date,$b_date,$_POST["birth_date7_nr"]);
					if($gap!==false) {  
						write_pers($name, "23",$b_date,$par_marr_date,__("birth date"),__('parents wedding date'),$_POST["birth_date7_nr"]);
						$results_found++;
					}
				}
			}  
 			
			// ID 24 - Birth date before parents' wedding date

			if(isset($_POST["birth_date8"]) AND $_POST["birth_date8"]=="1") {  
				if($par_marr_date!='' AND compare_seq($par_marr_date,$b_date)=="2") { 
						write_pers($name, "24",$b_date,$par_marr_date,__("birth date"),__("parents wedding date"),0);
						$results_found++;
				}
			}  
			
			// ID 25 - Birth date less than 9 months after previous child of the mother

			if(isset($_POST["birth_date9"]) AND $_POST["birth_date9"]=="1") {  
				if($sib_b_date !='' AND compare_seq($sib_b_date,$b_date)=="1") { 
					$gap = compare_month_gap($sib_b_date,$b_date,$_POST["birth_date9_nr"]);
					if($gap!==false) {  
						write_pers($name, "25",$b_date,$sib_b_date,__("birth date"),__('previous child of mother'),$_POST["birth_date9_nr"]);
						$results_found++;
					}
				}
			}

//END NEW

		} // end if b_date!=''

		if($bp_date!='') {
			  
			// ID 7 - Baptism date - after death/burial date

			if(isset($_POST["baptism_date1"]) AND $_POST["baptism_date1"]=="1") {	
				if($d_date!='' AND compare_seq($bp_date,$d_date)=="2") {  
					write_pers($name, "7",$bp_date,$d_date,__("baptism date"),__("death date"),0);
					$results_found++;
				}
				if($bu_date!='' AND compare_seq($bp_date,$bu_date)=="2") {
					write_pers($name, "7",$bp_date,$bu_date,__("baptism date"),__("burial date"),0);
					$results_found++;
				}
			}  
 
			// ID 8    CANCELLED - was joined with age check ID 2

			// ID 9 - Baptism date more than X years after mother's birth date

			if(isset($_POST["baptism_date3"]) AND $_POST["baptism_date3"]=="1") {
				if($m_b_date!='') { 
					$gap = compare_gap($m_b_date,$bp_date);
					if($gap!==false AND $gap > $_POST["baptism_date3_nr"]) {  
						write_pers($name, "9",$bp_date,$m_b_date,__("baptism date"),__('mother'),$_POST["baptism_date3_nr"]);
						$results_found++;
					}
				}
			}
 
			// ID 10  - Baptism date more than X years after father's birth date

			if(isset($_POST["baptism_date4"]) AND $_POST["baptism_date4"]=="1") {
				if($f_b_date!='') { 
					$gap = compare_gap($f_b_date,$bp_date);
					if($gap!==false AND $gap > $_POST["baptism_date4_nr"]) {  
						write_pers($name, "10",$bp_date,$f_b_date,__("baptism date"),__('father'),$_POST["baptism_date4_nr"]);
						$results_found++;
					}
				}
			}
 
			// ID 11  - Baptism date less than X years after mother's birth date

			if(isset($_POST["baptism_date5"]) AND $_POST["baptism_date5"]=="1") {
				if($m_b_date!='') { 
					$gap = compare_gap($m_b_date,$bp_date);
					if($gap!==false AND $gap < $_POST["baptism_date5_nr"]) {
						write_pers($name, "11",$bp_date,$m_b_date,__("baptism date"),__('mother'),$_POST["baptism_date5_nr"]);
						$results_found++;
					}
				}
			}
  
			// ID 12  - Baptism date less than X years after father's birth date

			if(isset($_POST["baptism_date6"]) AND $_POST["baptism_date6"]=="1") { 
				if($f_b_date!='') {  
					$gap = compare_gap($f_b_date,$bp_date);
					if($gap!==false AND $gap < $_POST["baptism_date6_nr"]) {  
						write_pers($name, "12",$bp_date,$f_b_date,__("baptism date"),__('father'),$_POST["baptism_date6_nr"]);
						$results_found++;
					}
				}
			}
		}  // end if bp_date!=''

		if(isset($personDb['pers_fams'])) {

			// ID 13 - Marriage date after death/burial date

			if(isset($_POST["marriage_date1"]) AND $_POST["marriage_date1"]=="1") {	
				for($i=0;$i<count($marr_dates);$i++) {
					if($marr_dates[$i]!='') {  	
						if($d_date!='' AND compare_seq($marr_dates[$i],$d_date)=="2") {  
							write_pers($name, "13",$marr_dates[$i],$d_date,__("marriage"),__("death date"),0);
							$results_found++;
						}
						if($bu_date!='' AND compare_seq($marr_dates[$i],$bu_date)=="2") {
							write_pers($name, "13",$marr_dates[$i],$bu_date,__("marriage"),__("burial date"),0);
							$results_found++;
						}
					}

					if($marr_notice_dates[$i]!='') { 
						if($d_date!='' AND compare_seq($marr_notice_dates[$i],$d_date)=="2") {  
							write_pers($name, "13",$marr_notice_dates[$i],$d_date,__("marriage notice"),__("death date"),0);
							$results_found++;
						}
						if($bu_date!='' AND compare_seq($marr_notice_dates[$i],$bu_date)=="2") {
							write_pers($name, "13",$marr_notice_dates[$i],$bu_date,__("marriage notice"),__("burial date"),0);
							$results_found++;
						}
					}  
					if($marr_church_dates[$i]!='') { 
						if($d_date!='' AND compare_seq($marr_church_dates[$i],$d_date)=="2") {  
							write_pers($name, "13",$marr_church_dates[$i],$d_date,__("church marriage"),__("death date"),0);
							$results_found++;
						}
						if($bu_date!='' AND compare_seq($marr_church_dates[$i],$bu_date)=="2") {
							write_pers($name, "13",$marr_church_dates[$i],$bu_date,__("church marriage"),__("burial date"),0);
							$results_found++;
						}
					}
					if($marr_church_notice_dates[$i]!='') { 
						if($d_date!='' AND compare_seq($marr_church_notice_dates[$i],$d_date)=="2") {  
							write_pers($name, "13",$marr_church_notice_dates[$i],$d_date,__("church marriage notice"),__("death date"),0);
							$results_found++;
						}
						if($bu_date!='' AND compare_seq($marr_church_notice_dates[$i],$bu_date)=="2") {
							write_pers($name, "13",$marr_church_notice_dates[$i],$bu_date,__("church marriage notice"),__("burial date"),0);
							$results_found++;
						}
					}  
				}  
			}

			// ID 14 - Marriage date less than X years after birth date

			if(isset($_POST["marriage_date2"]) AND $_POST["marriage_date2"]=="1") {
				for($i=0;$i<count($marr_dates);$i++) {
					if($marr_dates[$i]!='' AND $b_date!='') { 
						$gap = compare_gap($b_date,$marr_dates[$i]);
						if($gap!==false AND $gap>=0 AND $gap < $_POST["marriage_date2_nr"]) {  
							write_pers($name,"14",$marr_dates[$i],$b_date,__("marriage"),__('birth date'), $_POST["marriage_date2_nr"]);
							$results_found++;
						}
					}
					if($marr_notice_dates[$i]!='' AND $b_date!='') { 
						$gap = compare_gap($b_date,$marr_notice_dates[$i]);
						if($gap!==false AND $gap>=0 AND $gap < $_POST["marriage_date2_nr"]) {  
							write_pers($name,"14",$marr_notice_dates[$i],$b_date,__("marriage notice"),__('birth date'), $_POST["marriage_date2_nr"]);
							$results_found++;
						}
					}
					if($marr_church_dates[$i]!='' AND $b_date!='') { 
						$gap = compare_gap($b_date,$marr_church_dates[$i]);
						if($gap!==false AND $gap>=0 AND $gap < $_POST["marriage_date2_nr"]) {  
							write_pers($name,"14",$marr_church_dates[$i],$b_date,__("church marriage"),__('birth date'), $_POST["marriage_date2_nr"]);
							$results_found++;
						}
					}
					if($marr_church_notice_dates[$i]!='' AND $b_date!='') { 
						$gap = compare_gap($b_date,$marr_church_notice_dates[$i]);
						if($gap!==false AND $gap>=0 AND $gap < $_POST["marriage_date2_nr"]) {  
							write_pers($name,"14",$marr_church_notice_dates[$i],$b_date,__("church marriage notice"),__('birth date'), $_POST["marriage_date2_nr"]);
							$results_found++;
						}
					}
				}
			}

			// ID 15 - More than X years age difference between spouses

			if(isset($_POST["marriage_age"]) AND $_POST["marriage_age"]=="1") {
				for($i=0;$i<count($spouse_dates);$i++) {  
					if($spouse_dates[$i]!='' AND $b_date!='') { 
						$gap = compare_gap($b_date,$spouse_dates[$i]);
						if($gap!==false AND
						   abs($gap) > $_POST["marriage_age_nr"]) {  
							write_pers($name,"15",$spouse_dates[$i],$b_date,__("birth date"),__("Spouse"), $_POST["marriage_age_nr"]);
							$results_found++;
						}
					}
				}
			}   
		} // end if pers_fams
 
		if($d_date!='') {

			// ID 16 - Death date after burial date

			if(isset($_POST["death_date1"]) AND $_POST["death_date1"]=="1") {	
				if($bu_date!='' AND compare_seq($d_date,$bu_date)=="2") {  
					write_pers($name,"16",$d_date,$bu_date,__("death date"),__("burial date"),0);
					$results_found++;
				}
			}
 
			// ID 17 - Death date before mother's birth date

			if(isset($_POST["death_date2"]) AND $_POST["death_date2"]=="1") {	
				if($m_b_date!='' AND compare_seq($d_date,$m_b_date)=="1") {  
					write_pers($name,"17",$d_date,$m_b_date,__("death date"),__("mother"),0);
					$results_found++;
				}
			}  
  
			// ID 18 - Death date before father's birth date

			if(isset($_POST["death_date3"]) AND $_POST["death_date3"]=="1") {	
				if($f_b_date!='' AND compare_seq($d_date,$f_b_date)=="1") {  
					write_pers($name,"18",$d_date,$f_b_date,__("death date"),__("father"),0);
					$results_found++;
				}
			} 

		} // end if d_date!=''

		if($bu_date!='') {
			// ID 19 - Burial date before mother's birth date

			if(isset($_POST["burial_date1"]) AND $_POST["burial_date1"]=="1") {	
				if($m_b_date!='' AND compare_seq($bu_date,$m_b_date)=="1") {  
					write_pers($name,"19",$bu_date,$m_b_date,__("burial date"),__("mother"),0);
					$results_found++;
				}
			} 
 
			// ID 20 - Burial date before father's birth date

			if(isset($_POST["burial_date2"]) AND $_POST["burial_date2"]=="1") {	
				if($f_b_date!='' AND compare_seq($bu_date,$f_b_date)=="1") {  
					write_pers($name,"20",$bu_date,$f_b_date,__("burial date"),__("father"),0);
					$results_found++;
				}
			} 

		} // end if bu_date!=''

		if($b_date!='' OR $bp_date!='') {
 
			// ID 21 - Age by death date

			if(isset($_POST["age1"]) AND $_POST["age1"]=="1") { 
				if($d_date!='') { 
					if($b_date !='') { $start_date = $b_date; $txt= __("birth date"); } 
					else { $start_date=$bp_date; $txt= __("baptism date"); }
					$gap = compare_gap($start_date,$d_date);
					if($gap!==false AND $gap > $_POST["age1_nr"]) {  
						write_pers($name,"21",$start_date,$d_date,$txt,__('death date'),$_POST["age1_nr"]);
						$results_found++;
					}
				}
			}
	 
			// ID 22 - Age by burial date

			if(isset($_POST["age2"]) AND $_POST["age2"]=="1") { 
				if($bu_date!='') { 
					if($b_date !='') { $start_date = $b_date; $txt= __("birth date"); } 
					else { $start_date=$bp_date; $txt= __("baptism date"); }
					$gap = compare_gap($start_date,$bu_date);
					if($gap!==false AND $gap > $_POST["age1_nr"]) {  
						write_pers($name,"22",$start_date,$bu_date,$txt,__('burial date'),$_POST["age2_nr"]);
						$results_found++;
					}
				}
			}
 
			// ID 2 - Age up till today (no death/burial date)

			if(isset($_POST["birth_date2"]) AND $_POST["birth_date2"]=="1") {  
				$alive=''; if(isset($personDb['pers_alive'])) $alive = $personDb['pers_alive'];
				$d_place=''; if(isset($personDb['pers_death_place'])) $d_place = $personDb['pers_death_place'];
				$bu_place=''; if(isset($personDb['pers_buried_place'])) $bu_place = $personDb['pers_buried_place']; 
				if($d_date=='' AND $bu_date==''  AND $d_place=='' AND $bu_place=='' AND $alive!="deceased") {   
					if($b_date !='') { $start_date = $b_date; $txt= __("birth date"); } 
					else { $start_date=$bp_date; $txt= __("baptism date"); }
					$gap = compare_gap($start_date,date("j M Y"));
					if($gap!==false AND $gap > $_POST["birth_date2_nr"]) {  
						write_pers($name, "2",$start_date,'',$txt,'',$_POST["birth_date2_nr"]);
						$results_found++;
					}
				}
			} 
		} // end if $b_date!='' OR $bp_date!=''

	} // end of while loop with $personDb

	if($results_found==0) echo '<tr><td style="color:red;text-align:center;font-weight:bold;font-size:120%" colspan=4><br>No inconsistencies found!<br><br></td></tr>';
	echo '</table>';
}

echo '</td></tr></table>';
echo "</form>\n";



function compare_seq($first_date,$second_date) {   
// checks sequence of 2 dates (which is the earlier date)
	include_once (CMS_ROOTPATH.'include/calculate_age_cls.php');  
	$process_date = New calculate_year_cls;

	// take care of combined julian/gregorian dates (1678/9)
	if (strpos($first_date,'/')>0){ $temp=explode ('/',$first_date); $first_date=$temp[0];	}
	if (strpos($second_date,'/')>0){ $temp=explode ('/',$second_date); $second_date=$temp[0]; }
 
	$first_date=strtoupper($first_date); // $process_date->search_month uses upppercase months: DEC, FEB
	$second_date=strtoupper($second_date); 
 
	$year1 = $process_date->search_year($first_date);
	$month1 = $process_date->search_month($first_date);
	$day1 = $process_date->search_day($first_date);
	$year2 = $process_date->search_year($second_date);
	$month2 = $process_date->search_month($second_date);
	$day2 = $process_date->search_day($second_date);   

	if($year1 AND $year2) {  
		if($year1 > $year2) return "2"; // a > b
		elseif($year1 < $year2) return "1"; // a < b
		elseif($year1 == $year2) {
			if($month1 AND $month2) {  
				if($month1 > $month2) return "2"; // a > b
				elseif($month1 < $month2) return "1"; // a < b
				elseif($month1 == $month2) {
					if($day1 AND $day2) {  
						if($day1 > $day2) return "2"; // a > b
						elseif($day1 < $day2) return "1"; // a < b
						elseif($day1 == $day2) return "3"; // equal
					}
					else return "3"; // equal
				}
			}
			else return "3"; // equal
		}
	}
	else return 0; // insufficient data
}

function compare_month_gap($first_date,$second_date,$monthgap) {   
// checks gap in months between two dates (to check for birth less than X months after wedding)
	include_once (CMS_ROOTPATH.'include/calculate_age_cls.php');  
	$process_date = New calculate_year_cls;

	// take care of combined julian/gregorian dates (1678/9)
	if (strpos($first_date,'/')>0){ $temp=explode ('/',$first_date); $first_date=$temp[0];	}
	if (strpos($second_date,'/')>0){ $temp=explode ('/',$second_date); $second_date=$temp[0]; }
	$first_date=strtoupper($first_date); // $process_date->search_month uses upppercase months: DEC, FEB
	$second_date=strtoupper($second_date);
	$year1 = $process_date->search_year($first_date); 
	$month1 = $process_date->search_month($first_date);  
	$day1 = $process_date->search_day($first_date);  
	$year2 = $process_date->search_year($second_date);  
	$month2 = $process_date->search_month($second_date);  
	$day2 = $process_date->search_day($second_date);    

	if($year1 AND $year2 AND $month1 AND $month2) {  
		if($year1 == $year2) {  // dates in same year - we can deduct month1 from month2
			if(($month2 - $month1) < $monthgap) return $month2 - $month1;
			else return false;
		}
		elseif($year1 + 1 == $year2) { // consecutive years
			if(((12-$month1) + $month2) < $monthgap) return (12-$month1) + $month2; 
			else return false;
		}
		else return false;
	}
	else return false; // insufficient data
}

function compare_gap($first_date,$second_date) {  
// finds gap between 2 years. No need for months or days, since we look for gaps of several years
	include_once (CMS_ROOTPATH.'include/calculate_age_cls.php');  
	$process_date = New calculate_year_cls;

	// take care of combined julian/gregorian dates (1678/9)
	if (strpos($first_date,'/')>0){ $temp=explode ('/',$first_date); $first_date=$temp[0];	}
	if (strpos($second_date,'/')>0){ $temp=explode ('/',$second_date); $second_date=$temp[0]; }

	$year1 = $process_date->search_year($first_date);
	$year2 = $process_date->search_year($second_date);

	if($year1 AND $year2) return ($year2 - $year1);
	else return false;
}

function write_pers ($name,$id,$first_date,$second_date,$first_text,$second_text,$nr) {   
// displays results for date consistency check
	global $personDb, $tree, $gap;
	$dash = '<span style="font-size:140%;color:red"> &#8596; </span>'; $second_colon = ': ';

	// use short term for "Details" column
	$first = $first_text;
	$second = $second_text;
	if($first_text== __('birth date')) $first = __('BORN_SHORT');
	if($first_text== __('baptism date')) $first = __('BAPTISED_SHORT');
	if($first_text== __('death date')) $first = __('DIED_SHORT');
	if($first_text== __('burial date')) $first = __('BURIED_SHORT');
	if($second_text== __('birth date')) $second = __('BORN_SHORT');
	if($second_text== __('baptism date')) $second = __('BAPTISED_SHORT');
	if($second_text== __('death date')) $second = __('DIED_SHORT');
	if($second_text== __('burial date')) $second = __('BURIED_SHORT');

	echo '<tr><td style="padding-left:5px;padding-right:5px"><a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$personDb['pers_gedcomnumber'].'" target=\'_blank\'>'.$name.'</a></td>';

	echo '<td style="padding-left:5px;padding-right:5px">'.$personDb['pers_gedcomnumber'].'</td>';
	echo '<td style="padding-left:5px;padding-right:5px">';

	if($id=="1" OR $id=="7" OR $id=="13" OR $id=="16") { echo $first_text.' '.__("after").' '.$second_text;}
	elseif($id=="3" OR $id=="4" OR $id=="9" OR $id=="10") { printf(__("%s more than %d years after %s"),$first,$nr,__('birth date').' '.$second_text); $second = $second_text.' '.__('BORN_SHORT');}
	//elseif($id=="9" OR $id=="10") { printf(__("%s more than %d years after %s"),$first,$nr,__('birth date').' '.$second_text); $second = $second_text.' '.__('BAPTISED_SHORT');}
	elseif($id=="5" OR $id=="6" OR $id=="11" OR $id=="12"){ printf(__("%s before or less than %d years after %s"),$first,$nr,__('birth date').' '.$second_text); $second = $second_text.' '.__('BORN_SHORT');}
	//elseif($id=="11" OR $id=="12"){ printf(__("%s before or less than %d years after %s"),$first,$nr,__('birth date').' '.$second_text); $second = $second_text.' '.__('BAPTISED_SHORT');}
	elseif($id=="14"){ printf(__("%s less than %d years after %s"),$first,$nr,$second_text); } 
	elseif($id=="17" OR $id=="18" OR $id=="19" OR $id=="20") { echo $first.' '.__("before").' '.__('birth date').' '.$second_text; $second = $second_text.' '.__('BORN_SHORT');}
	elseif($id=="2") { printf(__("age (up till today) more than %d years (age: %d)"),$nr,$gap); $dash = ''; $second_colon = ''; }
	elseif($id=="21" OR $id=="22") { printf(__("age (by %s) more than %d years (age: %d)"),$second_text,$nr,$gap); }
	elseif($id=="15") { printf(__("age difference of more than %d years with spouse (%d)"),$nr,abs($gap)); $second = strtolower($second_text).' '.__('BORN_SHORT'); }
	elseif($id=="23" OR $id=="25"){ printf(__("%s less than %d months after %s"),$first,$nr,$second_text); }
	elseif($id=="24"){ printf(__("%s before %s"),$first,$second_text); } 
	echo '</td>';
	echo '<td style="padding-left:5px;padding-right:5px">'.$first.': '.$first_date.$dash.$second.$second_colon.$second_date.'</td></tr>';   
}

function invalid($date,$gednr,$table) {  // checks validity with validate_cls.php and displays invalid dates and their details
	global $dbh, $db_functions, $tree, $direction, $dirmark1, $dirmark2;
	include_once (CMS_ROOTPATH.'include/validate_date_cls.php'); 
	$process_date = New validate_date_cls;
	$compare_date=$date;
	if (strpos($date,'/')>0){ // check for combined julian/gregorian date entries like 1654/5 and check the first part
		$temp=explode ('/',$date);
		$compare_date=$temp[0];
		// In case this was not a jul/greg case but an invalid date like: 30/Jun/1980 or 12/3/90 
		// then "$compare_date" will become 30/jun or 12/3 which is still invalid and will be found and listed.
		// For the list of invalid dates, we use "$date" so that the full invalid date (30/Jun/1980 or 12/3/90 etc.) is displayed.
		// Also, if a jul/greg date itself is invalid (3 january 1680/1, 31 FEB 1678/9) then the mistake will be found
		// in the first part and will be listed, while the list will display the original invalid full jul/greg date as we want.	
	}

	if($process_date->check_date(strtoupper($compare_date)) === null) { // invalid date
		if(substr($table,0,3) =="per") {
			$pers = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$gednr."'");
			$personDb=$pers->fetch();
			$name = $personDb['pers_firstname'].' '.str_replace("_"," ",$personDb['pers_prefix'].' '.$personDb['pers_lastname']);
			echo '<tr><td style="text-align:'.$direction.'">'.$gednr.'</td><td style="text-align:'.$direction.'"><a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$personDb['pers_gedcomnumber'].'" target=\'_blank\'>'.$name.'</a></td><td style="text-align:'.$direction.'">'.$table.'</td><td style="text-align:'.$direction.'">'.$dirmark2.$date.'</td></tr>'; 
		}
		if(substr($table,0,3) =="fam") {
			$fam = $dbh->query("SELECT * FROM humo_families WHERE pers_tree_id='".$tree_id."' AND fam_gedcomnumber = '".$gednr."'");
			$famDb=$fam->fetch();
			$spouse1 = $dbh->query("SELECT * FROM humo_persons
				WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$famDb['fam_man']."'");
			$spouse1Db=$spouse1->fetch();
			$name1 = $spouse1Db['pers_firstname'].' '.str_replace("_"," ",$spouse1Db['pers_prefix'].' '.$spouse1Db['pers_lastname']);
			$spouse2 = $dbh->query("SELECT * FROM humo_person
				WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$famDb['fam_woman']."'");
			$spouse2Db=$spouse2->fetch();
			$name2 = $spouse2Db['pers_firstname'].' '.str_replace("_"," ",$spouse2Db['pers_prefix'].' '.$spouse2Db['pers_lastname']);
			$spousegednr = $spouse1Db['pers_gedcomnumber']; if($spousegednr=='') $spousegednr = $spouse2Db['pers_gedcomnumber'];
			$and = ' '.__('and').' '; if($spouse1Db['pers_gedcomnumber']=='' OR $spouse2Db['pers_gedcomnumber']=='') $and='';
			echo '<tr><td style="text-align:'.$direction.'">'.$gednr.'</td><td style="text-align:'.$direction.'"><a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$spousegednr.'" target=\'_blank\'>'.$name1.$and.$name2.'</a></td><td style="text-align:'.$direction.'">'.$table.'</td><td style="text-align:'.$direction.'">'.$dirmark2.$date.'</td></tr>'; 			
		}
		if(substr($table,0,3) =="eve") {
			$ev = $dbh->query("SELECT * FROM humo_events WHERE event_id = '".$gednr."'");
			$evDb=$ev->fetch();
			if($evDb['event_person_id']!='') { 
				$pers = $dbh->query("SELECT * FROM humo_person
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$evDb['event_person_id']."'");
				$persDb=$pers->fetch();
				$fullname = $persDb['pers_firstname'].' '.str_replace("_"," ",$persDb['pers_prefix'].' '.$persDb['pers_lastname']);
				$evdetail= $evDb['event_event']; 
				if($evdetail=='') $evdetail=$evDb['event_gedcom']; 
				if($evdetail!='') $evdetail = ': '.$evdetail;
				echo '<tr><td style="text-align:'.$direction.'">'.$persDb['pers_gedcomnumber'].'</td><td style="text-align:'.$direction.'"><a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$persDb['pers_gedcomnumber'].'" target=\'_blank\'>'.$fullname.'</a> ('.__('Click events by person').')</td><td style="text-align:'.$direction.'">'.$evDb['event_kind'].$evdetail.'</td><td style="text-align:'.$direction.'">'.$dirmark2.$date.'</td></tr>';  
			}
			elseif($evDb['event_family_id']!='') { 
				$fam = $dbh->query("SELECT * FROM humo_families
					WHERE pers_tree_id='".$tree_id."' AND fam_gedcomnumber = '".$evDb['event_family_id']."'");
				$famDb=$fam->fetch();
				$spouse1 = $dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$famDb['fam_man']."'");
				$spouse1Db=$spouse1->fetch();
				$name1 = $spouse1Db['pers_firstname'].' '.str_replace("_"," ",$spouse1Db['pers_prefix'].' '.$spouse1Db['pers_lastname']);
				$spouse2 = $dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$famDb['fam_woman']."'");
				$spouse2Db=$spouse2->fetch();
				
				$name2 = $spouse2Db['pers_firstname'].' '.str_replace("_"," ",$spouse2Db['pers_prefix'].' '.$spouse2Db['pers_lastname']);
				$fullname = $name1.' and '.$name2;
				$spousegednr = $spouse1Db['pers_gedcomnumber']; if($spousegednr=='') $spousegednr = $spouse2Db['pers_gedcomnumber'];
				$evdetail= $evDb['event_event']; 
				if($evdetail=='') $evdetail=$evDb['event_gedcom']; 
				if($evdetail!='') $evdetail = ': '.$evdetail;
				echo '<tr><td style="text-align:'.$direction.'">'.$famDb['fam_gedcomnumber'].'</td><td style="text-align:'.$direction.'"><a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$spousegednr.'" target=\'_blank\'>'.$fullname.'</a> ('.__('Click events by marriage').')</td><td style="text-align:'.$direction.'">'.$evDb['event_kind'].$evdetail.'</td><td style="text-align:'.$direction.'">'.$dirmark2.$date.'</td></tr>';
			}

		}
		if(substr($table,0,3) =="con") {
			//$connect = $dbh->query("SELECT * FROM ".$tree."connections WHERE connect_id = '".$gednr."'");
			$connect = $dbh->query("SELECT * FROM humo_connections WHERE connect_id = '".$gednr."'");
			$connectDb=$connect->fetch();
			$name = '';
			if(substr($connectDb['connect_sub_kind'],0,3)=='per') {
				$pers = $dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$connectDb['connect_connect_id']."'");
				$persDb=$pers->fetch();
				if(substr($connectDb['connect_sub_kind'],-6)=='source') {
					$name = '<a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$persDb['pers_gedcomnumber'].'" target=\'_blank\'>'.$persDb['pers_firstname'].' '.str_replace("_"," ",$persDb['pers_prefix'].' '.$persDb['pers_lastname']).'</a> ('.__('Click relevant person source').')';
				}
				if(substr($connectDb['connect_sub_kind'],-7)=='address') {
					$name = '<a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$persDb['pers_gedcomnumber'].'" target=\'_blank\'>'.$persDb['pers_firstname'].' '.str_replace("_"," ",$persDb['pers_prefix'].' '.$persDb['pers_lastname']).'</a> ('.__('Click addresses').')';
				}
				$gedcomnr = $persDb['pers_gedcomnumber'];
			}
			if(substr($connectDb['connect_sub_kind'],0,3)=='fam') {
				$fam = $dbh->query("SELECT * FROM humo_families
					WHERE pers_tree_id='".$tree_id."' AND fam_gedcomnumber = '".$connectDb['connect_connect_id']."'");
				$famDb=$fam->fetch();
				$spouse1 = $dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND  pers_gedcomnumber='".$famDb['fam_man']."'");
				$spouse1Db=$spouse1->fetch();
				$name1 = $spouse1Db['pers_firstname'].' '.str_replace("_"," ",$spouse1Db['pers_prefix'].' '.$spouse1Db['pers_lastname']);
				$spouse2 = $dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$famDb['fam_woman']."'");
				$spouse2Db=$spouse2->fetch();
				
				$name2 = $spouse2Db['pers_firstname'].' '.str_replace("_"," ",$spouse2Db['pers_prefix'].' '.$spouse2Db['pers_lastname']);
				$name = $name1.' and '.$name2;
				$spousegednr = $spouse1Db['pers_gedcomnumber']; if($spousegednr=='') $spousegednr = $spouse2Db['pers_gedcomnumber'];
				if(substr($connectDb['connect_sub_kind'],-6)=='source') {
					$name = '<a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$spousegednr.'" target=\'_blank\'>'.$name.'</a> ('.__('Click relevant family source');
				}
				$gedcomnr = $famDb['fam_gedcomnumber'];
			}
			if(substr($connectDb['connect_sub_kind'],0,3)=='eve') {
				$ev = $dbh->query("SELECT * FROM humo_events WHERE event_id ='".$connectDb['connect_connect_id']."'");
				$evDb=$ev->fetch();
				if($evDb['event_person_id']!='') {
					$pers = $dbh->query("SELECT * FROM humo_persons
						WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$evDb['event_person_id']."'");
					$persDb=$pers->fetch();
					$gednr = $persDb['pers_gedcomnumber']; // for url string
					$gedcomnr = $persDb['pers_gedcomnumber']; // for first column
					$name = $persDb['pers_firstname'].' '.str_replace("_"," ",$persDb['pers_prefix']).' '.$persDb['pers_lastname'];

				}
				if($evDb['event_family_id']!='') {
					$fam = $dbh->query("SELECT * FROM humo_families
						WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber = '".$evDb['event_family_id']."'");
					$famDb=$fam->fetch();
					$spouse1 = $dbh->query("SELECT * FROM humo_persons
						WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$famDb['fam_man']."'");
					$spouse1Db=$spouse1->fetch();
					$name1 = $spouse1Db['pers_firstname'].' '.str_replace("_"," ",$spouse1Db['pers_prefix'].' '.$spouse1Db['pers_lastname']);
					$spouse2 = $dbh->query("SELECT * FROM humo_persons
						WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$famDb['fam_woman']."'");
					$spouse2Db=$spouse2->fetch();
					
					$name2 = $spouse2Db['pers_firstname'].' '.str_replace("_"," ",$spouse2Db['pers_prefix'].' '.$spouse2Db['pers_lastname']);
					$name = $name1.' and '.$name2;
					$gednr = $spouse1Db['pers_gedcomnumber']; if($spousegednr=='') $spousegednr = $spouse2Db['pers_gedcomnumber'];
					$gedcomnr = $famDb['fam_gedcomnumber']; // for first column
				}
				if(substr($connectDb['connect_sub_kind'],-6)=='source') {
					$name = '<a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$gednr.'" target=\'_blank\'>'.$name.'</a> ('.__('Click relevant event source').')';
				}
			}
			echo '<tr><td style="text-align:'.$direction.'">'.$gedcomnr.'</td><td style="text-align:'.$direction.'">'.$name.'</td><td style="text-align:'.$direction.'">'.$connectDb['connect_sub_kind'].'</td><td style="text-align:'.$direction.'">'.$dirmark2.$date.'</td></tr>'; 
		}
		if(substr($table,0,3) =="add") {
			//$addresses = $dbh->query("SELECT * FROM ".$tree."addresses WHERE address_id = '".$gednr."'");
			$addresses = $dbh->query("SELECT * FROM humo_addresses WHERE address_id = '".$gednr."'");
			$addressesDb=$addresses->fetch();
			if($addressesDb['address_person_id']!='') {
				$pers = $dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber = '".$addressesDb['address_person_id']."'");
				$persDb=$pers->fetch();
				$name = $persDb['pers_firstname'].' '.str_replace("_"," ",$persDb['pers_prefix']).' '.$persDb['pers_lastname'];
				echo '<tr><td style="text-align:'.$direction.'">'.$persDb['pers_gedcomnumber'].'</td><td style="text-align:'.$direction.'"><a href="../admin/index.php?page=editor&tree='.$tree.'&person='.$persDb['pers_gedcomnumber'].'" target=\'_blank\'>'.$name.'</a> ('.__('Click addresses').')</td><td style="text-align:'.$direction.'">'.$table.'</td><td style="text-align:'.$direction.'">'.$date.'</td></tr>'; 
			}
			if($addressesDb['address_gedcomnr']!='') { $second_column = '<a href="index.php?page=edit_addresses" target=\'_blank\'>'.__('Address editor').'</a> (Search for: '.$addressesDb['address_address'].')'; 
				echo '<tr><td style="text-align:'.$direction.'">'.$gednr.'</td><td style="text-align:'.$direction.'">'.$second_column.'</td><td style="text-align:'.$direction.'">'.$table.'</td><td style="text-align:'.$direction.'">'.$dirmark2.$date.'</td></tr>'; 
			}
		}
		if(substr($table,0,3) =="sou") {
			//$sources = $dbh->query("SELECT * FROM ".$tree."sources WHERE source_gedcomnr = '".$gednr."'");
			//$sourcesDb=$sources->fetch();
			$sourcesDb = $db_functions->get_source($gednr);
			echo '<tr><td style="text-align:'.$direction.'">'.$gednr.'</td><td style="text-align:'.$direction.'">'.'<a href="index.php?page=edit_sources" target=\'_blank\'>'.__('Source editor').'</a> (Search for: '.$sourcesDb->source_title.')</td><td style="text-align:'.$direction.'">'.$table.'</td><td style="text-align:'.$direction.'">'.$dirmark2.$date.'</td></tr>'; 
		}
		if(substr($table,0,3) =="rep") {
			//$repos = $dbh->query("SELECT * FROM ".$tree."repositories WHERE repo_gedcomnr = '".$gednr."'");
			//$reposDb=$repos->fetch();
			$reposDb = $db_functions->get_repository($gednr);
			echo '<tr><td style="text-align:'.$direction.'">'.$gednr.'</td><td style="text-align:'.$direction.'">'.'<a href="index.php?page=edit_repositories" target=\'_blank\'>'.__('Repository editor').'</a> (Search for: '.$reposDb->repo_name.')</td><td style="text-align:'.$direction.'">'.$table.'</td><td style="text-align:'.$direction.'">'.$dirmark2.$date.'</td></tr>'; 
		}
		return true;  // found invalid date
	}
	return false; // did not find invalid date
}
?>