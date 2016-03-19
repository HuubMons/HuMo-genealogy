<?php
/*
 * Statistics
 * First version: René Janssen.
 * Updated by: Huub.
 *
 * April 2015, Huub: added tab menu, and Yossi's new freqently firstnames and surnames pages.
 */

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");
// *** Standard function for names ***
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/calculate_age_cls.php");

// *** Get general data from family tree ***
$db_functions->get_tree($tree_prefix_quoted);

$tree_date=$dataDb->tree_date;
$month=''; // *** empty date ***
if (substr($tree_date,5,2)=='01'){ $month=' '.__('jan').' ';}
if (substr($tree_date,5,2)=='02'){ $month=' '.__('feb').' ';}
if (substr($tree_date,5,2)=='03'){ $month=' '.__('mar').' ';}
if (substr($tree_date,5,2)=='04'){ $month=' '.__('apr').' ';}
if (substr($tree_date,5,2)=='05'){ $month=' '.__('may').' ';}
if (substr($tree_date,5,2)=='06'){ $month=' '.__('jun').' ';}
if (substr($tree_date,5,2)=='07'){ $month=' '.__('jul').' ';}
if (substr($tree_date,5,2)=='08'){ $month=' '.__('aug').' ';}
if (substr($tree_date,5,2)=='09'){ $month=' '.__('sep').' ';}
if (substr($tree_date,5,2)=='10'){ $month=' '.__('oct').' ';}
if (substr($tree_date,5,2)=='11'){ $month=' '.__('nov').' ';}
if (substr($tree_date,5,2)=='12'){ $month=' '.__('dec').' ';}
$tree_date=substr($tree_date,8,2).$month.substr($tree_date,0,4);

// *** Tab menu ***
$menu_tab='stats_tree';
if (isset($_GET['menu_tab']) and $_GET['menu_tab']=='stats_tree') $menu_tab='stats_tree';
if (isset($_GET['menu_tab']) and $_GET['menu_tab']=='stats_persons') $menu_tab='stats_persons';
if (isset($_GET['menu_tab']) and $_GET['menu_tab']=='stats_surnames') $menu_tab='stats_surnames';
if (isset($_GET['menu_tab']) and $_GET['menu_tab']=='stats_firstnames') $menu_tab='stats_firstnames';

echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="">';
echo '<div class="pageHeading">';
	// <div class="pageHeadingText">Configuratie gegevens</div>
	// <div class="pageHeadingWidgets" aria-hidden="true" style="display: none;"></div>

	echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
		echo '<ul class="pageTabs">';
			$select_item=''; if ($menu_tab=='stats_tree'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="'.CMS_ROOTPATH.'statistics.php?'.'tree_id='.$tree_id.'">'.__('Family tree')."</a></div></li>";

			$select_item=''; if ($menu_tab=='stats_persons'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_persons&amp;tree_id='.$tree_id.'">'.__('Persons')."</a></div></li>";

			$select_item=''; if ($menu_tab=='stats_surnames'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_surnames&amp;tree_id='.$tree_id.'">'.__('Frequency of Surnames')."</a></div></li>";

			$select_item=''; if ($menu_tab=='stats_firstnames'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_firstnames&amp;tree_id='.$tree_id.'">'.__('Frequency of First Names')."</a></div></li>";
		echo '</ul>';
	echo '</div>';
echo '</div>';
echo '</div>';


// *** Align content to the left ***
//echo '<div style="float: left; background-color:white; height:500px; padding:10px;">';
echo '<div style="background-color:white; height:500px; padding:10px;">';

	// *** Show tree statistics ***
	if ($menu_tab=='stats_tree'){

		if(CMS_SPECIFIC=="Joomla") {
			$table1_width="100%";
		}
		else {
			// $table1_width="800";
			$table1_width="80%";
		}
		echo '<br><table width='.$table1_width.' class="humo" align="center">';

		echo '<tr class=table_headline><th>'.__('Item').'</th><th><br></th><th><br></th></tr>';

		// *** Latest database update ***
		echo "<tr><td>".__('Latest update')."</td>\n";
		echo "<td align='center'><i>$tree_date</i></td>\n";
		echo '<td><br></td>';
		echo '</tr>';

		echo '<tr><td colspan="3"><br></td></tr>';

		// *** Nr. of families in database ***
		echo "<tr><td>".__('No. of families')."</td>\n";
		echo "<td align='center'><i>$dataDb->tree_families</i></td>\n";
		echo '<td><br></td></tr>';

		// *** Most children in family ***
		echo "<tr><td>".__('Most children in family')."</td>\n";
		$test_number="2"; // *** minimum of 2 children ***
		$res=@$dbh->query("SELECT fam_gedcomnumber, fam_man, fam_woman, fam_children
			FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_children != ''");
		while (@$record=$res->fetch(PDO::FETCH_OBJ)){
			$count_children=substr_count($record->fam_children, ';');
			$count_children=$count_children + 1;
			if ($count_children > $test_number){
				$test_number = "$count_children";
				$man_gedcomnumber = $record->fam_man;
				$woman_gedcomnumber = $record->fam_woman;
				$fam_gedcomnumber=$record->fam_gedcomnumber;
			}
		}
		echo "<td align='center'><i>$test_number</i></td>\n";
		@$record=$db_functions->get_person($man_gedcomnumber);
		$person_cls = New person_cls;
		$person_cls->construct($record);
		$name=$person_cls->person_name($record);
		$man=$name["standard_name"];
		$index = @$record->pers_indexnr;

		@$record=$db_functions->get_person($woman_gedcomnumber);
		$person_cls = New person_cls;
		$person_cls->construct($record);
		$name=$person_cls->person_name($record);
		$woman=$name["standard_name"];

		if(CMS_SPECIFIC=="Joomla") {
			echo '<td align="center"><a href="index.php?option=com_humo-gen&task=family&id='.$fam_gedcomnumber.'"><i><b>'.$man.__(' and ').$woman.'</b></i> </a></td></tr>';
		}
		else{
			echo '<td align="center"><a href="family.php?id='.@$fam_gedcomnumber.'"><i><b>'.$man.__(' and ').$woman.'</b></i> </a></td></tr>';
		}
		// *** Nr. of persons database ***
		$nr_persons=$dataDb->tree_persons;
		echo "<tr><td>".__('No. of persons')."</td>\n";
		echo "<td align='center'><i>$nr_persons</i></td>\n";
		echo '<td><br></td></tr>';

		echo '</table>';

	}


	// *** Show persons statistics ***
	if ($menu_tab=='stats_persons'){

		function convert_date_number($date){
			//31 SEP 2010 -> 20100931

			// *** Remove ABT from date ***
			$date=str_replace("ABT ", "", $date);
			$date=str_replace("EST ABT ", "", $date);
			$date=str_replace("CAL ABT ", "", $date);
			$date=str_replace("AFT ", "", $date);
			$date=str_replace("BEF ", "", $date);
			$date=str_replace("EST ", "", $date);
			$date=str_replace("CAL ", "", $date);
			//$date=str_replace(" BC", "", $date);
			//$date=str_replace(" B.C.", "", $date);

			// Remove first part from date period. BET MAY 1979 AND AUG 1979 => AUG 1979.
			if (strstr($date, ' AND ')){
				$date=strstr($date, ' AND ');
				$date=str_replace(" AND ", "", $date);
			}
			// Remove first part from date period. FROM APR 2000 TO 5 MAR 2001 => 5 MAR 2001.
			if (strstr($date, ' TO ')){
				$date=strstr($date, ' TO ');
				$date=str_replace(" TO ", "", $date);
			}

			// *** Check for year only ***
			if (strlen($date)=='4' AND is_numeric($date)) $date='01 JUN '.$date; // 1887 -> 01 JUN 1887
			if (strlen($date)=='8') $date='15 '.$date; // AUG 1887 -> 15 AUG 1887
			$date=str_replace(" JAN ", "01", $date);
			$date=str_replace(" FEB ", "02", $date);
			$date=str_replace(" MAR ", "03", $date);
			$date=str_replace(" APR ", "04", $date);
			$date=str_replace(" MAY ", "05", $date);
			$date=str_replace(" JUN ", "06", $date);
			$date=str_replace(" JUL ", "07", $date);
			$date=str_replace(" AUG ", "08", $date);
			$date=str_replace(" SEP ", "09", $date);
			$date=str_replace(" OCT ", "10", $date);
			$date=str_replace(" NOV ", "11", $date);
			$date=str_replace(" DEC ", "12", $date);
			$date=substr($date,-4).substr($date,2,2).substr($date,0,2);

			//echo $date.'<br>';
			return $date;
		}

		// *** Men and women table ***
		function show_person($row) {
			global $humo_option, $uri_path;
			$person_cls = New person_cls;
			$person_cls->construct($row);
			$name=$person_cls->person_name($row);
			if(CMS_SPECIFIC=="Joomla") {
				return '<td align="center"><a href="index.php?option=com_humo-gen&task=family&id='.$row->pers_indexnr.'"><i><b>'.$name["standard_name"].'</b></i> </a> </td>';
			}
			else {
				if ($humo_option["url_rewrite"]=="j"){
					// *** $uri_path generated in header.php ***
					return '<td align="center"><a href="'.$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.$row->pers_indexnr.
					'/'.$row->pers_gedcomnumber.'/"><i><b>'.$name["standard_name"].'</b></i> </a> </td>';
				}
				else{
					return '<td align="center"><a href="family.php?id='.$row->pers_indexnr.'"><i><b>'.$name["standard_name"].'</b></i> </a> </td>';
				}
			}
		}

		if(CMS_SPECIFIC=="Joomla") {
			$table2_width="100%";
		}
		else {
			// $table2_width="900";
			$table2_width="80%";
		}
		echo '<br><table width='.$table2_width.' class="humo" align="center">';

		echo '<tr class=table_headline><th width="20%">'.__('Item').'</th><th colspan="2" width="40%">'.__('Male').'</th><th colspan="2" width="40%">'.__('Female').'</th></tr>';

		// *** Count man ***
		$person_qry=$dbh->query("SELECT pers_sexe FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_sexe='m'");
		$count_persons=$person_qry->rowCount();
		echo "<tr><td>".__('No. of persons')."</td>\n";
		echo "<td align='center'><i>$count_persons</i></td>\n";
		@$percent=($count_persons/$nr_persons)*100;
		echo '<td align="center">'.floor($percent).'%</td>';

		// *** Count woman ***
		$person_qry=$dbh->query("SELECT pers_sexe FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_sexe='f'");
		$count_persons=$person_qry->rowCount();
		echo "<td align='center'><i>$count_persons</i></td>\n";
		@$percent=($count_persons/$nr_persons)*100;
		echo '<td align="center">'.floor($percent).'%</td>';

		echo '<tr><td colspan="5"><br></td></tr>';

		// *** Oldest pers_birth_date man.
		echo "<tr><td>".__('Oldest birth date')."</td>\n";
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_birth_date, substring(pers_birth_date,-4) as search
			FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_birth_date LIKE '_%' AND pers_sexe='M' AND substring(pers_birth_date,-3)!=' BC'
			ORDER BY search");
		$oldest_year=''; $oldest_date='30003112'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$oldest_year) $oldest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($oldest_year!=$row->search) break;
			if ($oldest_year==substr($row->pers_birth_date,-4)){
				$pers_birth_date=convert_date_number($row->pers_birth_date);
				if ($pers_birth_date<$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_birth_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";
		// *** Oldest pers_birth_date woman.
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_birth_date, substring(pers_birth_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_birth_date LIKE '_%' AND pers_sexe='F' AND substring(pers_birth_date,-3)!=' BC'
			ORDER BY search
			");
		$oldest_year=''; $oldest_date='30003112'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$oldest_year) $oldest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($oldest_year!=$row->search) break;
			if ($oldest_year==substr($row->pers_birth_date,-4)){
				$pers_birth_date=convert_date_number($row->pers_birth_date);
				if ($pers_birth_date<$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_birth_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";

		// Youngest pers_birth_date man
		echo "<tr><td>".__('Youngest birth date')."</td>\n";
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_birth_date, substring(pers_birth_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_birth_date LIKE '_%' AND pers_sexe='M' AND substring(pers_birth_date,-3)!=' BC'
			ORDER BY search DESC");
		$youngest_year=''; $oldest_date='0'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$youngest_year) $youngest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($youngest_year!=$row->search) break;
			if ($youngest_year==substr($row->pers_birth_date,-4)){
				$pers_birth_date=convert_date_number($row->pers_birth_date);
				if ($pers_birth_date>$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);

			echo "<td align='center'><i>".date_place($row->pers_birth_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";
		// Youngest pers_birth_date woman
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_birth_date, substring(pers_birth_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_birth_date LIKE '_%' AND pers_sexe='F' AND substring(pers_birth_date,-3)!=' BC'
			ORDER BY search DESC");
		$youngest_year=''; $oldest_date='0'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$youngest_year) $youngest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($youngest_year!=$row->search) break;
			if ($youngest_year==substr($row->pers_birth_date,-4)){
				$pers_birth_date=convert_date_number($row->pers_birth_date);
				if ($pers_birth_date>$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_birth_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";


		// *** Oldest pers_bapt_date man.
		echo "<tr><td>".__('Oldest baptise date')."</td>\n";
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_bapt_date, substring(pers_bapt_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_bapt_date LIKE '_%' AND pers_sexe='M' AND substring(pers_bapt_date,-3)!=' BC'
			ORDER BY search");
		$oldest_year=''; $oldest_date='30003112'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$oldest_year) $oldest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($oldest_year!=$row->search) break;
			if ($oldest_year==substr($row->pers_bapt_date,-4)){
				$pers_bapt_date=convert_date_number($row->pers_bapt_date);
				if ($pers_bapt_date<$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_bapt_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";
		// Oldest pers_bapt_date woman
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_bapt_date, substring(pers_bapt_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_bapt_date LIKE '_%' AND pers_sexe='F' AND substring(pers_bapt_date,-3)!=' BC'
			ORDER BY search");
		$oldest_year=''; $oldest_date='30003112'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$oldest_year) $oldest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($oldest_year!=$row->search) break;
			if ($oldest_year==substr($row->pers_bapt_date,-4)){
				$pers_bapt_date=convert_date_number($row->pers_bapt_date);
				if ($pers_bapt_date<$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_bapt_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";


		// Youngest pers_bapt_date man
		echo "<tr><td>".__('Youngest baptise date')."</td>\n";
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_bapt_date, substring(pers_bapt_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_bapt_date LIKE '_%' AND pers_sexe='M' AND substring(pers_bapt_date,-3)!=' BC'
			ORDER BY search DESC");
		$youngest_year=''; $oldest_date='0'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$youngest_year) $youngest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($youngest_year!=$row->search) break;
			if ($youngest_year==substr($row->pers_bapt_date,-4)){
				$pers_bapt_date=convert_date_number($row->pers_bapt_date);
				if ($pers_bapt_date>$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_bapt_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";
		// Youngest pers_bapt_date woman
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_bapt_date, substring(pers_bapt_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_bapt_date LIKE '_%' AND pers_sexe='F' AND substring(pers_bapt_date,-3)!=' BC'
			ORDER BY search DESC");
		$youngest_year=''; $oldest_date='0'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$youngest_year) $youngest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($youngest_year!=$row->search) break;
			if ($youngest_year==substr($row->pers_bapt_date,-4)){
				$pers_bapt_date=convert_date_number($row->pers_bapt_date);
				if ($pers_bapt_date>$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_bapt_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";

		// Oldest death date man
		echo "<tr><td>".__('Oldest death date')."</td>\n";
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_death_date, substring(pers_death_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_death_date LIKE '_%' AND pers_sexe='M' AND substring(pers_death_date,-3)!=' BC'
			ORDER BY search");
		$oldest_year=''; $oldest_date='30003112'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$oldest_year) $oldest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($oldest_year!=$row->search) break;
			if ($oldest_year==substr($row->pers_death_date,-4)){
				$pers_death_date=convert_date_number($row->pers_death_date);
				if ($pers_death_date<$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_death_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";
		// Oldest pers_death_date woman
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_death_date, substring(pers_death_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_death_date LIKE '_%' AND pers_sexe='F' AND substring(pers_death_date,-3)!=' BC'
			ORDER BY search");
		$oldest_year=''; $oldest_date='30003112'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$oldest_year) $oldest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($oldest_year!=$row->search) break;
			if ($oldest_year==substr($row->pers_death_date,-4)){
				$pers_death_date=convert_date_number($row->pers_death_date);
				if ($pers_death_date<$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_death_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";

		// Youngest death date man
		echo "<tr><td>".__('Youngest death date')."</td>\n";
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_death_date, substring(pers_death_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_death_date LIKE '_%' AND pers_sexe='M' AND substring(pers_death_date,-3)!=' BC'
			ORDER BY search DESC");
		$oldest_year=''; $oldest_date='0'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$oldest_year) $oldest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($oldest_year!=$row->search) break;
			if ($oldest_year==substr($row->pers_death_date,-4)){
				$pers_death_date=convert_date_number($row->pers_death_date);
				if ($pers_death_date>$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_death_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";
		// Youngest pers_death_date woman
		$qry = $dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_death_date, substring(pers_death_date,-4) as search
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_death_date LIKE '_%' AND pers_sexe='F' AND substring(pers_death_date,-3)!=' BC'
			ORDER BY search DESC");
		$oldest_year=''; $oldest_date='0'; $person_found='';
		while($row=$qry->fetch(PDO::FETCH_OBJ)){
			if (!$oldest_year) $oldest_year=$row->search;
			// *** Only search for dates in oldest year ***
			if ($oldest_year!=$row->search) break;
			if ($oldest_year==substr($row->pers_death_date,-4)){
				$pers_death_date=convert_date_number($row->pers_death_date);
				if ($pers_death_date>$oldest_date) $person_found=$row;
			}
		}
		if ($person_found){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($person_found->pers_gedcomnumber);
			echo "<td align='center'><i>".date_place($row->pers_death_date,'')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";


		echo '<tr><td colspan="5"><br></td></tr>';

		// *** Longest living man, and calculate ages ***
		$man_min=50;
		$man_max=0;
		$man_min_married=50;
		$man_max_married=0;

		echo "<tr><td>".__('Longest living person')."</td>\n";
		$res = @$dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_birth_date, pers_bapt_date, pers_death_date, pers_fams, pers_indexnr
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_sexe='M' AND (pers_birth_date LIKE '_%' OR pers_bapt_date LIKE '_%') AND pers_death_date LIKE '_%'");
		$test_year="10";
		while(@$record = $res->fetch(PDO::FETCH_OBJ)){
			$age = New calculate_year_cls;
			$age_cal=$age->calculate_age($record->pers_bapt_date,$record->pers_birth_date,$record->pers_death_date,true);
			$age_man[]=$age_cal;
			if ($age_cal>$man_max){ $man_max=$age_cal; }
			if ($age_cal>0 AND $age_cal<$man_min){ $man_min=$age_cal; }

			if ($record->pers_fams!='' AND $age_cal>0){
				$age_man_married[]=$age_cal;
				if ($age_cal>$man_max_married){ $man_max_married=$age_cal; }
				if ($age_cal>0 AND $age_cal<$man_min_married){ $man_min_married=$age_cal; }
			}
			else{
				$age_man_unmarried[]=$age_cal;
			}

			if ($age_cal >= $test_year && $age_cal < 120){
				$index = $record->pers_indexnr;
				$test_year=	$age_cal;
				$oldest_person=$record;
			}
		}
		if (isset($oldest_person)){
			// *** Now get full person data (quicker in large family trees) ***
			$row=$db_functions->get_person($oldest_person->pers_gedcomnumber);
			echo '<td align="center"><i>'.$test_year.' '.__('years')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";

		// *** Longest living woman and calculate ages ***
		$woman_min=50;
		$woman_max=0;
		$woman_min_married=50;
		$woman_max_married=0;
		$res = @$dbh->query("SELECT pers_gedcomnumber, pers_sexe, pers_birth_date, pers_bapt_date, pers_death_date, pers_fams, pers_indexnr
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND pers_sexe='F' AND (pers_birth_date LIKE '_%' OR pers_bapt_date LIKE '_%') AND pers_death_date LIKE '_%'");
		$test_year="10";
		while(@$record = $res->fetch(PDO::FETCH_OBJ)){
			$age = New calculate_year_cls;
			$age_cal=$age->calculate_age($record->pers_bapt_date,$record->pers_birth_date,$record->pers_death_date,true);
			$age_woman[]=$age_cal;
			if ($age_cal>$woman_max){ $woman_max=$age_cal; }
			if ($age_cal>0 AND $age_cal<$woman_min){ $woman_min=$age_cal; }

			if ($record->pers_fams AND $age_cal>0){
				$age_woman_married[]=$age_cal;
				if ($age_cal>$woman_max_married){ $woman_max_married=$age_cal; }
				if ($age_cal>0 AND $age_cal<$woman_min_married){ $woman_min_married=$age_cal; }
			}
			else{
				$age_woman_unmarried[]=$age_cal;
			}

			if ($age_cal >= $test_year && $age_cal < 120){
				$index = $record->pers_indexnr;
				$test_year = $age_cal;
				$oldest_person=$record;
			}
		}
		if (isset($oldest_person)){
			$row=$db_functions->get_person($oldest_person->pers_gedcomnumber);
			echo '<td align="center"><i>'.$test_year.' '.__('years')."</i></td>\n";
			echo show_person($row);
		}
		else echo "<td></td><td></td>\n";

		// *** Average age ***
		echo "<tr><td>".__('Average age')."</td>\n";

		echo '<td align="center">';
		@$average=(array_sum($age_man) / count($age_man));
		echo round($average,1);
		echo ' '.__('years').'</td>';
		if ($man_min==0){ $man_min='0'; }
		echo '<td align="center">'.$man_min.' '.__('years').' - '.$man_max.' '.__('years').'</td>';

		echo '<td align="center">';
		@$average=array_sum($age_woman) / count($age_woman);
		echo round($average,1);
		echo ' '.__('years').'</td>';
		if ($woman_min==0){ $woman_min='0'; }
		echo '<td align="center">'.$woman_min.' '.__('years').' - '.$woman_max.' '.__('years').'</td>';
		echo '</tr>';

		// *** Average age married ***
		echo "<tr><td>".__('Average age married persons')."</td>\n";

		echo '<td align="center">';
		@$average=(array_sum($age_man_married) / count($age_man_married));
		echo round($average,1);
		echo ' '.__('years').'</td>';
		if ($man_min_married==0){ $man_min_married='0'; }
		echo '<td align="center">'.$man_min_married.' '.__('years').' - '.$man_max_married.' '.__('years').'</td>';

		echo '<td align="center">';
		@$average=array_sum($age_woman_married) / count($age_woman_married);
		echo round($average,1);
		echo ' '.__('years').'</td>';
		if ($woman_min_married==0){ $woman_min_married='0'; }
		echo '<td align="center">'.$woman_min_married.' '.__('years').' - '.$woman_max_married.' '.__('years').'</td>';
		echo '</tr>';

		echo '</table>';

	}

	// *** Show frequent surnames ***
	if ($menu_tab=='stats_surnames'){
		// MAIN SETTINGS
		$maxcols = 3; // number of name&nr colums in table. For example 3 means 3x name col + nr col
		if(isset($_POST['maxcols'])) {
			$maxcols = $_POST['maxcols'];
		}

		function tablerow($nr,$lastcol=false) {    
			// displays one set of name & nr column items in the row
			// $nr is the array number of the name set created in function last_names
			// if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
			global $user, $freq_last_names, $freq_pers_prefix, $freq_count_last_names;
			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.$_SESSION['tree_prefix'];
			}
			else{
				$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'];
			}
			echo '<td class="namelst">';
			if(isset($freq_last_names[$nr])) { 
				$top_pers_lastname=''; 	if ($freq_pers_prefix[$nr]){ $top_pers_lastname=str_replace("_", " ", $freq_pers_prefix[$nr]); }
				$top_pers_lastname.=$freq_last_names[$nr];
				if ($user['group_kindindex']=="j"){
					echo '<a href="'.$path_tmp.'&amp;pers_lastname='.str_replace("_", " ", $freq_pers_prefix[$nr]).str_replace("&", "|", $freq_last_names[$nr]); 
				}
				else{
					$top_pers_lastname=$freq_last_names[$nr];
					if ($freq_pers_prefix[$nr]){ $top_pers_lastname.=', '.str_replace("_", " ", $freq_pers_prefix[$nr]); }
					echo '<a href="'.$path_tmp.'&amp;pers_lastname='.str_replace("&", "|", $freq_last_names[$nr]);
					if ($freq_pers_prefix[$nr]){ echo '&amp;pers_prefix='.$freq_pers_prefix[$nr]; }
					else{ echo '&amp;pers_prefix=EMPTY'; }
				}
				echo '&amp;part_lastname=equals">'.$top_pers_lastname."</a>";
			}
			else echo '~';
			echo '</td>';
			
			if($lastcol==false)  echo '<td class="namenr" style="text-align:center;border-right-width:3px">'; // not last column numbers
			else echo '</td><td class="namenr" style="text-align:center">'; // no thick border
			
			if(isset($freq_last_names[$nr])) echo $freq_count_last_names[$nr];
			else echo '~';
			echo '</td>';
		}

		function last_names($max) {
			global $dbh, $tree_id, $language, $user, $humo_option, $uri_path, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $maxcols;
			$personqry="SELECT pers_lastname, pers_prefix,
				CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
				FROM humo_persons
				WHERE pers_tree_id='".$tree_id."' AND pers_lastname NOT LIKE ''
				GROUP BY long_name ORDER BY count_last_names DESC LIMIT 0,".$max;
			$person=$dbh->query($personqry);
			while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){ 
				$freq_last_names[]=$personDb->pers_lastname;  
				$freq_pers_prefix[]=$personDb->pers_prefix;
				$freq_count_last_names[]=$personDb->count_last_names;
			}
			$row = round(count($freq_last_names)/$maxcols);

			for ($i=0; $i<$row; $i++){
				echo '<tr>';
				for($n=0;$n<$maxcols;$n++) {
					if($n == $maxcols-1) {
						tablerow($i+($row*$n),true); // last col
					}
					else {
						tablerow($i+($row*$n)); // other cols
					}
				}
				echo '</tr>';
			}
			return $freq_count_last_names[0];
		}

		//echo '<div class="standard_header">'.__('Frequency of Surnames').'</div>';

		echo '<div style="text-align:center">';
		$maxnames = 51;

		if(isset($_POST['freqsurnames'])) { $maxnames = $_POST['freqsurnames']; }
		//echo ' <form method="POST" action="frequent_surnames.php" style="display:inline;" id="frqnames">';
		echo ' <form method="POST" action="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_surnames&amp;tree_id='.$tree_id.'" style="display:inline;" id="frqnames">';

		echo __('Number of displayed surnames');
		echo ': <select size=1 name="freqsurnames" onChange="this.form.submit();" style="width: 50px; height:20px;">';
		$selected=''; if($maxnames==25) $selected=" selected "; echo '<option value="25" '.$selected.'>25</option>';
		$selected=''; if($maxnames==51) $selected=" selected "; echo '<option value="51" '.$selected.'>50</option>'; // 51 so no empty last field (if more names than this)
		$selected=''; if($maxnames==75) $selected=" selected "; echo '<option value="75" '.$selected.'>75</option>';
		$selected=''; if($maxnames==100) $selected=" selected "; echo '<option value="100" '.$selected.'>100</option>';
		$selected=''; if($maxnames==201) $selected=" selected "; echo '<option value="201" '.$selected.'>200</option>'; // 201 so no empty last field (if more names than this)
		$selected=''; if($maxnames==300) $selected=" selected "; echo '<option value="300" '.$selected.'>300</option>';
		$selected=''; if($maxnames==100000) $selected=" selected "; echo '<option value="100000" '.$selected.'">'.__('All').'</option>'; 
		echo '</select>';

		echo '&nbsp;&nbsp;&nbsp;&nbsp;'.__('Number of columns');
		echo ': <select size=1 name="maxcols" onChange="this.form.submit();" style="width: 50px; height:20px;">';
		for($i=1;$i<7;$i++) {
			$selected=''; if($maxcols==$i) $selected=" selected "; echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
		}
		echo '</select>';
		echo '</form>';
		echo '</div>';

		if(CMS_SPECIFIC=="Joomla") {
			$table2_width="100%";
		}
		else {
			// $table2_width="900";
			$table2_width="90%";
		}

		echo '<br><table width='.$table2_width.' class="humo nametbl" align="center">';

		echo '<tr class=table_headline>';
		$col_width = ((round(100/$maxcols))-6)."%";
		for($x=1; $x<$maxcols;$x++) {
			echo '<th width="'.$col_width.'">'.__('Name').'</th><th style="text-align:center;font-size:90%;border-right-width:3px;width:6%">'.__('Total').'</th>';  
		}
		echo '<th width="'.$col_width.'">'.__('Name').'</th><th style="text-align:center;font-size:90%;width:6%">'.__('Total').'</th>';
		echo '</tr>';

		$baseperc = last_names($maxnames);   // displays the table and sets the $baseperc (= the name with highest frequency that will be 100%)
		echo '</table>';
		echo '
		<script>
		var tbl = document.getElementsByClassName("nametbl")[0];
		var rws = tbl.rows; var baseperc = '.$baseperc.';
		for(var i = 0; i < rws.length; i ++) {
			var tbs =  rws[i].getElementsByClassName("namenr");
			var nms = rws[i].getElementsByClassName("namelst");
			for(var x = 0; x < tbs.length; x ++) {
				var percentage = parseInt(tbs[x].innerHTML, 10);
				percentage = (percentage * 100)/baseperc;  
				if(percentage > 0.1) {
					nms[x].style.backgroundImage= "url(images/lightgray.png)"; 
					nms[x].style.backgroundSize = percentage + "%" + " 100%";
					nms[x].style.backgroundRepeat = "no-repeat";
					nms[x].style.color = "rgb(0, 140, 200)";
				}
			}
		}
		</script>';

	}

	// *** Show frequent firstnames ***
	if ($menu_tab=='stats_firstnames'){
	
		function first_names($max){
			global $dbh, $tree_id, $language, $user, $humo_option, $uri_path;

			$m_first_names = array();
			$f_first_names = array();

			// men
			$personqry="SELECT pers_firstname FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_sexe='M' AND pers_firstname NOT LIKE ''";  
				
			$person=$dbh->query($personqry);
			while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){
				$fstname_arr = explode(" ",$personDb->pers_firstname);
				for($s = 0; $s < count($fstname_arr); $s++) {
					$fstname_arr[$s] = str_replace(array("'","\"","(",")","[","]",".",",","\\"), array("","","","","","","","",""), $fstname_arr[$s]);
					if($fstname_arr[$s]!="" AND is_numeric($fstname_arr[$s])===false AND $fstname_arr[$s]!="-" AND preg_match('/^[A-Z]$/',$fstname_arr[$s])!=1)  {
						if(isset($m_first_names[$fstname_arr[$s]])) {
							$m_first_names[$fstname_arr[$s]]++;
						}
						else {
							$m_first_names[$fstname_arr[$s]] = 1;
						}
					}
				}
			}

			arsort($m_first_names);
			uksort(
				$m_first_names,
				function ($a, $b) use ($m_first_names) {
					if ($m_first_names[$a] == $m_first_names[$b]) {
						return strcmp($a, $b);
					}
					return ($m_first_names[$a] < $m_first_names[$b]) ? 1 : -1;
				}
			);
			
			//women
			$personqry="SELECT pers_firstname FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_sexe='F' AND pers_firstname NOT LIKE ''" ;

			$person=$dbh->query($personqry);
			while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){
				$fstname_arr = explode(" ",$personDb->pers_firstname);
				for($s = 0; $s < count($fstname_arr); $s++) {
					$fstname_arr[$s] = str_replace(array("'","\"","(",")","[","]",".",",","\\"), array("","","","","","","","",""), $fstname_arr[$s]);
					if($fstname_arr[$s]!="" AND is_numeric($fstname_arr[$s])===false  AND $fstname_arr[$s]!="-" AND preg_match('/^[A-Z]$/',$fstname_arr[$s])!=1) {
						if(isset($f_first_names[$fstname_arr[$s]])) {
							$f_first_names[$fstname_arr[$s]]++;
						}
						else {
							$f_first_names[$fstname_arr[$s]] = 1;
						}
					}
				}
			}
			arsort($f_first_names);
			uksort(
				$f_first_names,
				function ($a, $b) use ($f_first_names) {
					if ($f_first_names[$a] == $f_first_names[$b]) {
						return strcmp($a, $b);
					}
					return ($f_first_names[$a] < $f_first_names[$b]) ? 1 : -1;
				}
			);

			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.$_SESSION['tree_prefix'];
			}
			else{
				$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'];
			}

			count($m_first_names) < count($f_first_names) ? $most= count($f_first_names) : $most= count($m_first_names);
			if($most > $max) $most = $max;
			$row = round($most/2);
			$count=0;
			$m_keys = array_keys($m_first_names);
			$f_keys = array_keys($f_first_names);
			
			for($i=0; $i < $row; $i++)    {
				//male 1st name
				echo '<tr><td class="m_namelst">';
				if(isset($m_keys[$i]) AND isset($m_first_names[$m_keys[$i]])) { echo '<a href="'.$path_tmp.'&amp;sexe=M&amp;pers_firstname='.$m_keys[$i].'&amp;part_firstname=contains">'.$m_keys[$i]."</a>"; }
				//male 1st nr
				echo '</td><td class="m_namenr" style="text-align:center;border-right-width:3px">';
				if(isset($m_keys[$i]) AND isset($m_first_names[$m_keys[$i]])) { echo $m_first_names[$m_keys[$i]]; }
				//male 2nd name
				echo '</td><td class="m_namelst">';
				if(isset($m_keys[$i+$row]) AND isset($m_first_names[$m_keys[$i+$row]])) { echo '<a href="'.$path_tmp.'&amp;sexe=M&amp;pers_firstname='.$m_keys[$i+$row].'&amp;part_firstname=contains">'.$m_keys[$i+$row]."</a>"; }
				//male 2nd nr
				echo '</td><td class="m_namenr" style="text-align:center;border-right-width:6px">';
				if(isset($m_keys[$i+$row]) AND isset($m_first_names[$m_keys[$i+$row]])) { echo $m_first_names[$m_keys[$i+$row]]; }
				//female 1st name
				echo '</td><td class="f_namelst">';
				if(isset($f_keys[$i]) AND isset($f_first_names[$f_keys[$i]])) { echo '<a href="'.$path_tmp.'&amp;sexe=F&amp;pers_firstname='.$f_keys[$i].'&amp;part_firstname=contains">'.$f_keys[$i]."</a>"; }
				//female 1st nr
				echo '</td><td class="f_namenr" style="text-align:center;border-right-width:3px">';
				if(isset($f_keys[$i]) AND isset($f_first_names[$f_keys[$i]])) { echo $f_first_names[$f_keys[$i]]; }
				//female 2nd name
				echo '</td><td class="f_namelst">';
				if(isset($f_keys[$i+$row]) AND isset($f_first_names[$f_keys[$i+$row]])) { echo '<a href="'.$path_tmp.'&amp;sexe=F&amp;pers_firstname='.$f_keys[$i+$row].'&amp;part_firstname=contains">'.$f_keys[$i+$row]."</a>"; }
				//female 2nd nr
				echo '</td><td class="f_namenr" style="text-align:center;border-right-width:3px">';
				if(isset($f_keys[$i+$row]) AND isset($f_first_names[$f_keys[$i+$row]])) { echo $f_first_names[$f_keys[$i+$row]]; }

				echo '</td></tr>';
			}
			return reset($m_first_names)."@".reset($f_first_names);
		}

		//echo '<div class="standard_header">'.__('Frequency of First Names').'</div>';

		echo '<div style="text-align:center">';
		$maxnames = 30;

		if(isset($_POST['freqfirstnames'])) { $maxnames = $_POST['freqfirstnames']; }
		//echo ' <form method="POST" action="frequent_firstnames.php" style="display:inline;" id="frqfirnames">';
		echo ' <form method="POST" action="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_firstnames&amp;tree_id='.$tree_id.'" style="display:inline;" id="frqfirnames">';
		echo __('Number of displayed first names');
		echo ': <select size=1 name="freqfirstnames" onChange="this.form.submit();" style="width: 50px; height:20px;">';
		$selected=''; if($maxnames==30) $selected=" selected "; echo '<option value="30" '.$selected.'>30</option>';
		$selected=''; if($maxnames==50) $selected=" selected "; echo '<option value="50" '.$selected.'">50</option>';
		$selected=''; if($maxnames==76) $selected=" selected "; echo '<option value="76" '.$selected.'">75</option>';
		$selected=''; if($maxnames==100) $selected=" selected "; echo '<option value="100" '.$selected.'">100</option>';
		$selected=''; if($maxnames==200) $selected=" selected "; echo '<option value="200" '.$selected.'">200</option>';
		$selected=''; if($maxnames==300) $selected=" selected "; echo '<option value="300" '.$selected.'">300</option>';
		$selected=''; if($maxnames==100000) $selected=" selected "; echo '<option value="100000" '.$selected.'">'.__('All').'</option>'; 
		echo '</select>';
		echo '</form>';
		echo '</div>';


		if(CMS_SPECIFIC=="Joomla") {
			$table2_width="100%";
		}
		else {
			// $table2_width="1000";
			$table2_width="90%";
		}

		echo '<br><table width='.$table2_width.' class="humo nametbl" align="center">';

		//echo '<tr class=table_headline style="height:40px">';
		echo '<tr class=table_headline style="height:25px">';
		echo '<th style="border-right-width:6px;width:50%" colspan="4"><span style="font-size:135%">'.__('Male').'</span></th><th  style="width:50%" colspan="4"><span style="font-size:135%">'.__('Female').'</span></th></tr><tr class=table_headline>';
		echo '<th width="19%">'.__('Name').'</th><th style="text-align:center;font-size:90%;border-right-width:3px;width:6%">'.__('Total').'</th>';  
		echo '<th width="19%">'.__('Name').'</th><th style="text-align:center;font-size:90%;border-right-width:6px;width:6%">'.__('Total').'</th>'; 
		echo '<th width="19%">'.__('Name').'</th><th style="text-align:center;font-size:90%;border-right-width:3px;width:6%">'.__('Total').'</th>';  
		echo '<th width="19%">'.__('Name').'</th><th style="text-align:center;font-size:90%;width:6%">'.__('Total').'</th>';
		echo '</tr>';

		$baseperc = first_names($maxnames);  // displays table and gets return value
		$baseperc_arr = explode("@",$baseperc);
		$m_baseperc = $baseperc_arr[0];  // nr of occurrences for most frequent male name - becomes 100%
		$f_baseperc = $baseperc_arr[1];    // nr of occurrences for most frequent female name - becomes 100%
		echo '</table><br>';  

		echo '
		<script>
		var tbl = document.getElementsByClassName("nametbl")[0];
		var rws = tbl.rows; var m_baseperc = '.$m_baseperc.'; var f_baseperc = '.$f_baseperc.';
		for(var i = 0; i < rws.length; i ++) {
			var m_tbs =  rws[i].getElementsByClassName("m_namenr");
			var m_nms = rws[i].getElementsByClassName("m_namelst");
			var f_tbs =  rws[i].getElementsByClassName("f_namenr");
			var f_nms = rws[i].getElementsByClassName("f_namelst");
			for(var x = 0; x < m_tbs.length; x ++) {
				if(parseInt(m_tbs[x].innerHTML, 10) != NaN && parseInt(m_tbs[x].innerHTML, 10) > 0) {
						var percentage = parseInt(m_tbs[x].innerHTML, 10);
						percentage = (percentage * 100)/m_baseperc;
						m_nms[x].style.backgroundImage= "url(images/lightgray.png)"; 
						m_nms[x].style.backgroundSize = percentage + "%" + " 100%";
						m_nms[x].style.backgroundRepeat = "no-repeat";
						m_nms[x].style.color = "rgb(0, 140, 200)";
				}
			}
			for(var x = 0; x < f_tbs.length; x ++) {
				if(parseInt(m_tbs[x].innerHTML, 10) != NaN && parseInt(m_tbs[x].innerHTML, 10) > 0) {
						var percentage = parseInt(f_tbs[x].innerHTML, 10);
					percentage = (percentage * 100)/f_baseperc;
						f_nms[x].style.backgroundImage= "url(images/lightgray.png)"; 
						f_nms[x].style.backgroundSize = percentage + "%" + " 100%";
						f_nms[x].style.backgroundRepeat = "no-repeat";
						f_nms[x].style.color = "rgb(0, 140, 200)";
				}
			}
		}
		</script>';
	}
	
echo '</div>'; // *** End of tab menu div ***
include_once(CMS_ROOTPATH."footer.php");
?>