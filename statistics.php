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
print "<tr><td>".__('Latest update')."</td>\n";
print "<td align='center'><i>$tree_date</i></td>\n";
echo '<td><br></td>';
echo '</tr>';

echo '<tr><td colspan="3"><br></td></tr>';

// *** Nr. of families in database ***
print "<tr><td>".__('No. of families')."</td>\n";
print "<td align='center'><i>$dataDb->tree_families</i></td>\n";
echo '<td><br></td></tr>';

// *** Most children in family ***
print "<tr><td>".__('Most children in family')."</td>\n";
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
print "<td align='center'><i>$test_number</i></td>\n";
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
	print '<td align="center"><a href="index.php?option=com_humo-gen&task=family&id='.$fam_gedcomnumber.'"><i><b>'.$man.__(' and ').$woman.'</b></i> </a></td></tr>';
}
else{
	print '<td align="center"><a href="family.php?id='.$fam_gedcomnumber.'"><i><b>'.$man.__(' and ').$woman.'</b></i> </a></td></tr>';
}
// *** Nr. of persons database ***
$nr_persons=$dataDb->tree_persons;
print "<tr><td>".__('No. of persons')."</td>\n";
print "<td align='center'><i>$nr_persons</i></td>\n";
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

echo '<tr style="font-weight:bold; text-align:center;"><td width="20%">'.__('Item').'</td><td colspan="2" width="40%">'.__('Male').'</td><td colspan="2" width="40%">'.__('Female').'</td></tr>';

// *** Count man ***
$person_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_sexe='m'");
$count_persons=$person_qry->rowCount();
print "<tr><td>".__('No. of persons')."</td>\n";
print "<td align='center'><i>$count_persons</i></td>\n";
@$percent=($count_persons/$nr_persons)*100;
echo '<td align="center">'.floor($percent).'%</td>';

// *** Count woman ***
$person_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_sexe='f'");
$count_persons=$person_qry->rowCount();
print "<td align='center'><i>$count_persons</i></td>\n";
@$percent=($count_persons/$nr_persons)*100;
echo '<td align="center">'.floor($percent).'%</td>';

echo '<tr><td colspan="5"><br></td></tr>';

// *** Oldest pers_birth_date man. Check only full birth date (10 or 11 characters) ***
print "<tr><td>".__('Oldest birth date')."</td>\n";
$res = $dbh->query("SELECT *, STR_TO_DATE(pers_birth_date,'%e %b %Y') as search
	FROM ".$tree_prefix_quoted."person
	WHERE (CHAR_LENGTH(pers_birth_date)=10 OR CHAR_LENGTH(pers_birth_date)=11)
	AND pers_sexe='M'
	ORDER BY search LIMIT 0,1
	");
$row=$res->fetch(PDO::FETCH_OBJ);
if ($row->pers_birth_date!=NULL){
	print "<td align='center'><i>".date_place($row->pers_birth_date,'')."</i></td>\n";
	echo show_person($row);
}
// Oldest pers_birth_date woman
$res = $dbh->query("SELECT *, STR_TO_DATE(pers_birth_date,'%e %b %Y') as search
	FROM ".$tree_prefix_quoted."person
	WHERE (CHAR_LENGTH(pers_birth_date)=10 OR CHAR_LENGTH(pers_birth_date)=11)
	AND pers_sexe='F'
	ORDER BY search LIMIT 0,1
	");	
$row=$res->fetch(PDO::FETCH_OBJ);
if (@$row->pers_birth_date!=NULL){
	print "<td align='center'><i>".date_place($row->pers_birth_date,'')."</i></td>\n";
	echo show_person($row);
}

// Youngest pers_birth_date man
print "<tr><td>".__('Youngest birth date')."</td>\n";
$res = $dbh->query("SELECT *, STR_TO_DATE(pers_birth_date,'%e %b %Y') as search
	FROM ".$tree_prefix_quoted."person
	WHERE (CHAR_LENGTH(pers_birth_date)=10 OR CHAR_LENGTH(pers_birth_date)=11)
	AND pers_sexe='M'
	ORDER BY search DESC LIMIT 0,1");
$row=$res->fetch(PDO::FETCH_OBJ);
if ($row->pers_birth_date!=NULL){
	$person_cls = New person_cls;
	$person_cls->construct($row);
	if (!$person_cls->privacy){
		echo '<td align="center"><i>'.date_place($row->pers_birth_date,'').'</i></td>';
		echo show_person($row);
	}
	else{
		echo '<td align="center"><b>'.__(' PRIVACY FILTER').'</b></td><td align="center"><b>'.__(' PRIVACY FILTER').'</b></td>';
	}
}
// Youngest pers_birth_date woman
$res = $dbh->query("SELECT *, STR_TO_DATE(pers_birth_date,'%e %b %Y') as search
	FROM ".$tree_prefix_quoted."person
	WHERE (CHAR_LENGTH(pers_birth_date)=10 OR CHAR_LENGTH(pers_birth_date)=11)
	AND pers_sexe='F'
	ORDER BY search DESC LIMIT 0,1");
$row=$res->fetch(PDO::FETCH_OBJ);
if ($row->pers_birth_date!=NULL){
	$person_cls = New person_cls;
	$person_cls->construct($row);
	if (!$person_cls->privacy){
		echo '<td align="center"><i>'.date_place($row->pers_birth_date,'').'</i></td>';
		echo show_person($row);
	}
	else{
		echo '<td align="center"><b>'.__(' PRIVACY FILTER').'</b></td><td align="center"><b>'.__(' PRIVACY FILTER').'</b></td></tr>';
	}
}

// Oldest death date man
print "<tr><td>".__('Oldest death date')."</td>\n";
$res = $dbh->query("SELECT *, STR_TO_DATE(pers_death_date,'%e %b %Y') as search
	FROM ".$tree_prefix_quoted."person
	WHERE (CHAR_LENGTH(pers_death_date)=10 OR CHAR_LENGTH(pers_death_date)=11)
	AND pers_sexe='M'
	ORDER BY search LIMIT 0,1");
$row=$res->fetch(PDO::FETCH_OBJ);
if (@$row->pers_death_date!=NULL){
	print "<td align='center'><i>".date_place($row->pers_death_date,'')."</i></td>\n";
	echo show_person($row);
}

// Oldest death date woman
$res = $dbh->query("SELECT *, STR_TO_DATE(pers_death_date,'%e %b %Y') as search
	FROM ".$tree_prefix_quoted."person
	WHERE (CHAR_LENGTH(pers_death_date)=10 OR CHAR_LENGTH(pers_death_date)=11)
	AND pers_sexe='F'
	ORDER BY search LIMIT 0,1");
$row=$res->fetch(PDO::FETCH_OBJ);
if (@$row->pers_death_date!=NULL){
	print "<td align='center'><i>".date_place($row->pers_death_date,'')."</i></td>\n";
	echo show_person($row);
}

// Youngest death date man
print "<tr><td>".__('Youngest death date')."</td>\n";
$res = $dbh->query("SELECT *, STR_TO_DATE(pers_death_date,'%e %b %Y') as search
	FROM ".$tree_prefix_quoted."person
	WHERE (CHAR_LENGTH(pers_death_date)=10 OR CHAR_LENGTH(pers_death_date)=11)
	AND pers_sexe='M'
	ORDER BY search DESC LIMIT 0,1");
$row=$res->fetch(PDO::FETCH_OBJ);
if ($row->pers_death_date!=NULL){
	print "<td align='center'><i>".date_place($row->pers_death_date,'')."</i></td>\n";
	$pers_prefix=str_replace("_", " ", $row->pers_prefix);
	echo show_person($row);
}

// Youngest death date woman
$res = $dbh->query("SELECT *, STR_TO_DATE(pers_death_date,'%e %b %Y') as search
	FROM ".$tree_prefix_quoted."person
	WHERE (CHAR_LENGTH(pers_death_date)=10 OR CHAR_LENGTH(pers_death_date)=11)
	AND pers_sexe='F'
	ORDER BY search DESC LIMIT 0,1");
$row=$res->fetch(PDO::FETCH_OBJ);
if ($row->pers_death_date!=NULL){
	print "<td align='center'><i>".date_place($row->pers_death_date,'')."</i></td>\n";
	$pers_prefix=str_replace("_", " ", $row->pers_prefix);
	echo show_person($row);
}

echo '<tr><td colspan="5"><br></td></tr>';

// *** Longest living man ***
$man_min=50;
$man_max=0;
$man_min_married=50;
$man_max_married=0;

print "<tr><td>".__('Longest living person')."</td>\n";
$res = @$dbh->query("SELECT *
	FROM ".$tree_prefix_quoted."person
	WHERE pers_sexe='M' AND pers_death_date LIKE '_%'
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
	print '<td align="center"><i>'.$test_year.' '.__('years')."</i></td>\n";
	echo show_person($oldest_person);
}

// *** Woman ***
$woman_min=50;
$woman_max=0;
$woman_min_married=50;
$woman_max_married=0;
$res = @$dbh->query("SELECT *
	FROM ".$tree_prefix_quoted."person
	WHERE pers_sexe='F' AND pers_death_date LIKE '_%'
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
	print "<td align='center'><i>$test_year ".__('years')."</i></td>\n";
	echo show_person($oldest_person);
}

// *** Average age ***
print "<tr><td>".__('Average age')."</td>\n";

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
print "<tr><td>".__('Average age married persons')."</td>\n";

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

print '</table>';

include_once(CMS_ROOTPATH."footer.php");
?>