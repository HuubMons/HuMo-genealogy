<?php
// ************************************
// *** Statistics                   ***
// *** First version: René Janssen. ***
// *** Updated by: Huub.            ***
// ************************************

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");
// *** Standard function for names ***
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/calculate_age_cls.php");

// *** Get general data from family tree ***
$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='".$tree_prefix_quoted."'");
@$dataDb = $datasql->fetch(PDO::FETCH_OBJ);

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

if(CMS_SPECIFIC=="Joomla") {
	$table1_width="100%";
}
else {
	$table1_width="800";
}
echo '<table width='.$table1_width.' class="humo" align="center">';

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
$res=@$dbh->query("SELECT * FROM ".$tree_prefix_quoted."family");
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
$res=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber='".$man_gedcomnumber."'");
@$record=$res->fetch(PDO::FETCH_OBJ);
$person_cls = New person_cls;
$person_cls->construct($record);
$name=$person_cls->person_name($record);
$man=$name["standard_name"];
$index = "$record->pers_indexnr";

$res=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber ='".$woman_gedcomnumber."'");
@$record=$res->fetch(PDO::FETCH_OBJ);
$person_cls = New person_cls;
$person_cls->construct($record);
$name=$person_cls->person_name($record);
$woman=$name["standard_name"];

if(CMS_SPECIFIC=="Joomla") {
	echo '<td align="center"><a href="index.php?option=com_humo-gen&task=family&id='.$fam_gedcomnumber.'"><i><b>'.$man.__(' and ').$woman.'</b></i> </a></td></tr>';
}
else{
	echo '<td align="center"><a href="family.php?id='.$fam_gedcomnumber.'"><i><b>'.$man.__(' and ').$woman.'</b></i> </a></td></tr>';
}
// *** Nr. of persons database ***
$nr_persons=$dataDb->tree_persons;
echo "<tr><td>".__('No. of persons')."</td>\n";
echo "<td align='center'><i>$nr_persons</i></td>\n";
echo '<td><br></td></tr>';

echo '</table>';

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
	$table2_width="900";
}
echo '<br><table width='.$table2_width.' class="humo" align="center">';

echo '<tr class=table_headline><th width="20%">'.__('Item').'</th><th colspan="2" width="40%">'.__('Male').'</th><th colspan="2" width="40%">'.__('Female').'</th></tr>';

// *** Count man ***
$person_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_sexe='m'");
$count_persons=$person_qry->rowCount();
echo "<tr><td>".__('No. of persons')."</td>\n";
echo "<td align='center'><i>$count_persons</i></td>\n";
@$percent=($count_persons/$nr_persons)*100;
echo '<td align="center">'.floor($percent).'%</td>';

// *** Count woman ***
$person_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_sexe='f'");
$count_persons=$person_qry->rowCount();
echo "<td align='center'><i>$count_persons</i></td>\n";
@$percent=($count_persons/$nr_persons)*100;
echo '<td align="center">'.floor($percent).'%</td>';

echo '<tr><td colspan="5"><br></td></tr>';

// *** Oldest pers_birth_date man.
echo "<tr><td>".__('Oldest birth date')."</td>\n";
$qry = $dbh->query("SELECT *, substring(pers_birth_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_birth_date LIKE '_%' AND pers_sexe='M' AND substring(pers_birth_date,-3)!=' BC'
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
	echo "<td align='center'><i>".date_place($person_found->pers_birth_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";
// Oldest pers_birth_date woman
$qry = $dbh->query("SELECT *, substring(pers_birth_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_birth_date LIKE '_%' AND pers_sexe='F' AND substring(pers_birth_date,-3)!=' BC'
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
	echo "<td align='center'><i>".date_place($person_found->pers_birth_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";

// Youngest pers_birth_date man
echo "<tr><td>".__('Youngest birth date')."</td>\n";
$qry = $dbh->query("SELECT *, substring(pers_birth_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_birth_date LIKE '_%' AND pers_sexe='M' AND substring(pers_birth_date,-3)!=' BC'
	ORDER BY search DESC
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_birth_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";
// Youngest pers_birth_date woman
$qry = $dbh->query("SELECT *, substring(pers_birth_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_birth_date LIKE '_%' AND pers_sexe='F' AND substring(pers_birth_date,-3)!=' BC'
	ORDER BY search DESC
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_birth_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";


// *** Oldest pers_bapt_date man.
echo "<tr><td>".__('Oldest baptise date')."</td>\n";
$qry = $dbh->query("SELECT *, substring(pers_bapt_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_bapt_date LIKE '_%' AND pers_sexe='M' AND substring(pers_bapt_date,-3)!=' BC'
	ORDER BY search
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_bapt_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";
// Oldest pers_bapt_date woman
$qry = $dbh->query("SELECT *, substring(pers_bapt_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_bapt_date LIKE '_%' AND pers_sexe='F' AND substring(pers_bapt_date,-3)!=' BC'
	ORDER BY search
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_bapt_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";


// Youngest pers_bapt_date man
echo "<tr><td>".__('Youngest baptise date')."</td>\n";
$qry = $dbh->query("SELECT *, substring(pers_bapt_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_bapt_date LIKE '_%' AND pers_sexe='M' AND substring(pers_bapt_date,-3)!=' BC'
	ORDER BY search DESC
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_bapt_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";
// Youngest pers_bapt_date woman
$qry = $dbh->query("SELECT *, substring(pers_bapt_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_bapt_date LIKE '_%' AND pers_sexe='F' AND substring(pers_bapt_date,-3)!=' BC'
	ORDER BY search DESC
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_bapt_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";

// Oldest death date man
echo "<tr><td>".__('Oldest death date')."</td>\n";
$qry = $dbh->query("SELECT *, substring(pers_death_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_death_date LIKE '_%' AND pers_sexe='M' AND substring(pers_death_date,-3)!=' BC'
	ORDER BY search
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_death_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";
// Oldest pers_death_date woman
$qry = $dbh->query("SELECT *, substring(pers_death_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_death_date LIKE '_%' AND pers_sexe='F' AND substring(pers_death_date,-3)!=' BC'
	ORDER BY search
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_death_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";

// Youngest death date man
echo "<tr><td>".__('Youngest death date')."</td>\n";
$qry = $dbh->query("SELECT *, substring(pers_death_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_death_date LIKE '_%' AND pers_sexe='M' AND substring(pers_death_date,-3)!=' BC'
	ORDER BY search DESC
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_death_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";
// Youngest pers_death_date woman
$qry = $dbh->query("SELECT *, substring(pers_death_date,-4) as search
	FROM ".$tree_prefix_quoted."person
	WHERE pers_death_date LIKE '_%' AND pers_sexe='F' AND substring(pers_death_date,-3)!=' BC'
	ORDER BY search DESC
	");
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
	echo "<td align='center'><i>".date_place($person_found->pers_death_date,'')."</i></td>\n";
	echo show_person($person_found);
}
else echo "<td></td><td></td>\n";


echo '<tr><td colspan="5"><br></td></tr>';

// *** Longest living man ***
$man_min=50;
$man_max=0;
$man_min_married=50;
$man_max_married=0;

echo "<tr><td>".__('Longest living person')."</td>\n";
$res = @$dbh->query("SELECT *
	FROM ".$tree_prefix_quoted."person
	WHERE pers_sexe='M' AND (pers_birth_date LIKE '_%' OR pers_bapt_date LIKE '_%') AND pers_death_date LIKE '_%'
	");
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
	echo '<td align="center"><i>'.$test_year.' '.__('years')."</i></td>\n";
	echo show_person($oldest_person);
}
else echo "<td></td><td></td>\n";

// *** Woman ***
$woman_min=50;
$woman_max=0;
$woman_min_married=50;
$woman_max_married=0;
$res = @$dbh->query("SELECT *
	FROM ".$tree_prefix_quoted."person
	WHERE pers_sexe='F' AND (pers_birth_date LIKE '_%' OR pers_bapt_date LIKE '_%') AND pers_death_date LIKE '_%'
	");
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
	echo "<td align='center'><i>$test_year ".__('years')."</i></td>\n";
	echo show_person($oldest_person);
}
else echo "<td></td><td></td>\n";

// *** Average age ***
echo "<tr><td>".__('Average age')."</td>\n";

echo '<td align="center">';
@$average=(array_sum($age_man) / count($age_man));
echo round($average,1);
echo '</td>';
if ($man_min==0){ $man_min='0'; }
echo '<td align="center">'.$man_min.' - '.$man_max.'</td>';

echo '<td align="center">';
@$average=array_sum($age_woman) / count($age_woman);
echo round($average,1);
echo '</td>';
if ($woman_min==0){ $woman_min='0'; }
echo '<td align="center">'.$woman_min.' - '.$woman_max.'</td>';
echo '</tr>';

// *** Average age married ***
echo "<tr><td>".__('Average age married persons')."</td>\n";

echo '<td align="center">';
@$average=(array_sum($age_man_married) / count($age_man_married));
echo round($average,1);
echo '</td>';
if ($man_min_married==0){ $man_min_married='0'; }
echo '<td align="center">'.$man_min_married.' - '.$man_max_married.'</td>';

echo '<td align="center">';
@$average=array_sum($age_woman_married) / count($age_woman_married);
echo round($average,1);
echo '</td>';
if ($woman_min_married==0){ $woman_min_married='0'; }
echo '<td align="center">'.$woman_min_married.' - '.$woman_max_married.'</td>';
echo '</tr>';

echo '</table>';

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

include_once(CMS_ROOTPATH."footer.php");
?>