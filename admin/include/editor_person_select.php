<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

//echo '<html><head><title>'.__('Select person').'</title></head><body>';
echo '<h1 align=center>'.__('Select person').'</h1>';

$place_item='connect_man'; $form='form2';
if ($_GET['person_item']=='woman'){ $place_item='connect_woman'; $form='form2'; }
if ($_GET['person_item']=='relation_add2'){ $place_item='relation_add2'; $form='form4'; }
//if ($_GET['person_item']=='child_connect2'){ $place_item='child_connect2'; $form='form4'; }
if ($_GET['person_item']=='child_connect2'){ $place_item='child_connect2'; $form='form7'; }

// *** Witnesses (=event, multiple witnesses possible) ***
if ($_GET['person_item']=='person_witness'){ $place_item='text_event2'.$_GET['event_row']; $form='form1'; }
if ($_GET['person_item']=='marriage_witness'){ $place_item='text_event2'.$_GET['event_row']; $form='form2'; }

if ($_GET['person_item']=='add_partner'){ $form='form_entire'; }
if (substr($_GET['person_item'],0,10)=='add_child_'){ $form='form_entire'; }

$man_gedcomnumber=safe_text_db($_GET['person']);

if($_GET['person_item']!= 'add_partner' AND substr($_GET['person_item'],0,10)!= 'add_child_') {
	echo'
		<script type="text/javascript">
		function select_item(item){
			window.opener.document.'.$form.'.'.$place_item.'.value=item;
			top.close();
			return false;
		}
		</script>
	';
}
else {
	$pers_status = "";
	$childnr = "";
	$trname = "";
	$searnr = "";
	$chnr = "";
	if($_GET['person_item']== 'add_partner') {
		$pers_status = "partner";
		$trname = "pmain";
		$searnr = "psearp";
	}
	elseif(substr($_GET['person_item'],0,10)== 'add_child_') {
		$pers_status = "child";
		$chnr = substr($_GET['person_item'],10);
		$trname = "child".$chnr;
		$searnr = "psearp".$chnr;
		$childnr = "_".$chnr;
	}
 	echo'
		<script type="text/javascript">
		function select_item2(pgn,ppf,pln,pfn,pbdp,pbd,pbp,pddp,pdd,pdp,psx){

			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_exist'.$childnr.'.value=pgn;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_lastname'.$childnr.'.value=pln;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_prefix'.$childnr.'.value=ppf;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_firstname'.$childnr.'.value=pfn;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_birthdate'.$childnr.'_prefix.value=pbdp;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_birthdate'.$childnr.'.value=pbd;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_birthplace'.$childnr.'.value=pbp;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_deathdate'.$childnr.'_prefix.value=pddp;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_deathdate'.$childnr.'.value=pdd;
			window.opener.document.'.$form.'.add_fam_'.$pers_status.'_deathplace'.$childnr.'.value=pdp;
			//window.opener.document.'.$form.'.add_fam_'.$pers_status.'_sexe'.$childnr.'.value=psx;
			if(psx=="M") window.opener.document.getElementById("prad1'.$childnr.'").checked=true;
			else if(psx=="F") window.opener.document.getElementById("prad2'.$childnr.'").checked=true;
			else window.opener.document.getElementById("prad3'.$childnr.'").checked=true;
			
			window.opener.document.getElementById("'.$trname.'").setAttribute("style", "background-color:#EBEBE4;text-align:left;");
			window.opener.document.getElementById("'.$searnr.'").style.textAlign="center";
			top.close();
			return false;
		}
		</script>
	';
}
echo '<form method="POST" action="index.php?page=editor_person_select&person_item='.$_GET['person_item'].'&person='.$_GET['person'];
if (isset($_GET['event_row'])) echo '&event_row='.$_GET['event_row'];
echo '&tree_id='.$tree_id.'" style="display : inline;">';
	$search_quicksearch_man=''; if (isset($_POST['search_quicksearch_man'])){ $search_quicksearch_man=safe_text_db($_POST['search_quicksearch_man']); }
	echo ' <input class="fonts" type="text" name="search_quicksearch_man" placeholder="'.__('Name').'" value="'.$search_quicksearch_man.'" size="15">';

	$search_man_id=''; if (isset($_POST['search_man_id'])) $search_man_id=safe_text_db($_POST['search_man_id']);
	echo __('or ID:').' <input class="fonts" type="text" name="search_man_id" value="'.$search_man_id.'" size="5">';

	echo ' <input class="fonts" type="submit" name="submit" value="'.__('Search').'">';
echo '</form><br><br>';

if($search_quicksearch_man != '') {
	// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
	$search_quicksearch_man=str_replace(' ', '%', $search_quicksearch_man);
	// *** In case someone entered "Mons, Huub" using a comma ***
	$search_quicksearch_man = str_replace(',','',$search_quicksearch_man);
	//$person_qry= "SELECT *, CONCAT(pers_firstname,pers_prefix,pers_lastname) as concat_name
	$person_qry= "SELECT *
	FROM humo_persons
	WHERE pers_tree_id='".$tree_id."'
		AND (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".$search_quicksearch_man."%'
		OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%".$search_quicksearch_man."%'
		OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%".$search_quicksearch_man."%'
		OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%".$search_quicksearch_man."%')
		ORDER BY pers_lastname, pers_firstname";
	$person_result = $dbh->query($person_qry);
}
elseif($search_man_id!='') {
	if(substr($search_man_id,0,1)!="i" AND substr($search_man_id,0,1)!="I") { $search_man_id = "I".$search_man_id; } //make entry "48" into "I48"
	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$search_man_id."'";
	$person_result = $dbh->query($person_qry);
	//$person = $db_functions->get_person($man_gedcomnumber);
}
else{
	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$man_gedcomnumber."'";
	$person_result = $dbh->query($person_qry);
	//$person = $db_functions->get_person($man_gedcomnumber);
}

include ('include/editor_cls.php');
$editor_cls = New editor_cls;

if($_GET['person_item']!= 'add_partner' AND substr($_GET['person_item'],0,10)!= 'add_child_') {
	while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
		echo '<a href="" onClick=\'return select_item("'.$person->pers_gedcomnumber.'")\'>'.$editor_cls->show_selected_person($person).'</a>';
			if ($person->pers_famc) echo ' ('.__('Parents').' '.$person->pers_famc.')';
		echo '<br>';
	}
}
else {
	$search = array("'",'"');
	$replace = array("&#39;",'\"');
	while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
		$bdate_arr = explode(" ",$person->pers_birth_date);
		//if(is_numeric(substr($bdate_arr[0],0,1))===false){ $dateprefix = $bdate_arr[0]." "; $dateself = substr($person->pers_birth_date,strpos($person->pers_birth_date," ")+1);}
		if(substr($bdate_arr[0],0,3)=="BEF" OR substr($bdate_arr[0],0,3)=="AFT" OR substr($bdate_arr[0],0,3)=="ABT" OR substr($bdate_arr[0],0,3)=="BET"){ $dateprefix = $bdate_arr[0]." "; $dateself = substr($person->pers_birth_date,strpos($person->pers_birth_date," ")+1);}
		else {
			$dateself=$person->pers_birth_date;
			$dateprefix="";
		}
		
		$ddate_arr = explode(" ",$person->pers_death_date);
		if(substr($ddate_arr[0],0,3)=="BEF" OR substr($ddate_arr[0],0,3)=="AFT" OR substr($ddate_arr[0],0,3)=="ABT" OR substr($ddate_arr[0],0,3)=="BET"){ $dateprefix2 = $ddate_arr[0]." "; $dateself2 = substr($person->pers_death_date,strpos($person->pers_death_date," ")+1);}
		else {
			$dateself2=$person->pers_death_date;
			$dateprefix2="";
		}
		
		$pgn = $person->pers_gedcomnumber;
		$ppf = str_replace($search,$replace,$person->pers_prefix);
		$pln = str_replace($search,$replace,$person->pers_lastname);
		$pfn = str_replace($search,$replace,$person->pers_firstname);
		$pbdp = $dateprefix;
		$pbd = $dateself;
		$pbp = str_replace($search,$replace,$person->pers_birth_place);
		$pddp = $dateprefix2;
		$pdd = $dateself2;
		$pdp = str_replace($search,$replace,$person->pers_death_place);
		$psx = $person->pers_sexe;
		echo '<a href="" onClick=\'return select_item2("'.$pgn.'","'.$ppf.'","'.$pln.'","'.$pfn.'","'.$pbdp.'","'.$pbd.'","'.$pbp.'","'.$pddp.'","'.$pdd.'","'.$pdp.'","'.$psx.'")\'>'.$editor_cls->show_selected_person($person).'</a><br>';
	}
}
?>