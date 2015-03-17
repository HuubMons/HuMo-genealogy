<?php
// *********************************************************
// *** Birtday list                                      ***
// *********************************************************
// Author : Louis Ywema
// Date  : 29-04-2006
// Website: http://www.ywema.com

// 18-06-2011 Huub: translated all remarks and variables into English. And did some minor updates.

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/person_cls.php");

// *** Check user authority ***
if ($user["group_birthday_list"]!='j'){
	echo __('You are not authorised to see this page.');
	exit();
}

// *** Month to show ***
if (isset($_GET['month'])){
	$month = $_GET['month'];
}
else{
	$month = date ("M");
	$month=strtolower($month);
}

// *** Calculate present date, month and year ***
$today = date('j').' '.date('M');
$today=strtolower($today);
$year = date("Y");

$url_start='<a href="'.$_SERVER['PHP_SELF'].'?month=';
$spacer='&nbsp;&#124;&nbsp;';
$newline ="\n";
$max_age = '110';
$last_cal_day=0;

// *** Center page ***
echo "<div class='fonts center'>";

// *** Show navigation ***
echo '[ ';
echo ($url_start.'jan">'.__('jan')."</a>").$spacer.$newline;
echo ($url_start.'feb">'.__('feb')."</a>").$spacer.$newline;
echo ($url_start.'mar">'.__('mar')."</a>").$spacer.$newline;
echo ($url_start.'apr">'.__('apr')."</a>").$spacer.$newline;
echo ($url_start.'may">'.__('may')."</a>").$spacer.$newline;
echo ($url_start.'jun">'.__('jun')."</a>").$spacer.$newline;
echo ($url_start.'jul">'.__('jul')."</a>").$spacer.$newline;
echo ($url_start.'aug">'.__('aug')."</a>").$spacer.$newline;
echo ($url_start.'sep">'.__('sep')."</a>").$spacer.$newline;
echo ($url_start.'oct">'.__('oct')."</a>").$spacer.$newline;
echo ($url_start.'nov">'.__('nov')."</a>").$spacer.$newline;
echo ($url_start.'dec">'.__('dec')."</a>");
echo " ]\n";

// *** Show month and year ***
echo "<div class='standard_header'>".ucfirst(language_date($month))." ".$year."</div>";

// *** Build page ***
echo '<table class="humo" align="center">';

echo '<tr class=table_headline>'.$newline;
// *** Show headers ***
echo '<th>'.__('Day')."</h></td>\n";
echo '<th>'.ucfirst(__('born'))."</th>\n";
echo '<th>'.__('Name')."</th>\n";
echo '<th>'.ucfirst(__('died'))."</th>\n";
echo "</tr>\n";

// *** Build query ***

$sql = "SELECT *,
	abs(substring( pers_birth_date,1,2 )) as birth_day,
	substring( pers_birth_date,-4 ) as birth_year
	FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND (substring( pers_birth_date,  4,3) = :month
	OR substring( pers_birth_date,  3,3) = :month)
	order by birth_day ";


/* WORKS IF BIRT IS SAVED AS EVENT:
$sql = "SELECT humo_persons.*,
	abs(substring( birth_events.event_date,1,2 )) as birth_day,
	substring( birth_events.event_date,-4 ) as birth_year,
	death_events.event_date as pers_death_date

	FROM humo_persons

	JOIN humo_events as birth_events
	ON (birth_events.event_person_id = humo_persons.pers_id AND birth_events.event_kind='birth')

	LEFT JOIN humo_events as death_events
	ON (death_events.event_person_id = humo_persons.pers_id AND death_events.event_kind='death')

	WHERE humo_persons.pers_tree_id='".$tree_id."'
	AND substring( birth_events.event_date, 4,3) = :month
	OR substring( birth_events.event_date, 3,3) = :month
	order by birth_day ";
*/
try {
	$qry = $dbh->prepare( $sql );
	$qry->bindValue(':month', $month, PDO::PARAM_STR);
	$qry->execute();
}catch (PDOException $e) {
	echo $e->getMessage() . "<br/>";
}
while ($record=$qry->fetch(PDO::FETCH_OBJ)){
	$calendar_day = $record->birth_day;
	$birth_day =$record->birth_day.' '.$month;

	$person_cls = New person_cls;
	$name=$person_cls->person_name($record);
	$person_cls->construct($record);
	$person_name='<a href="'.CMS_ROOTPATH.'family.php?id='.$record->pers_indexnr.'&amp;main_person='.$record->pers_gedcomnumber.'">'.$name["standard_name"].'</a>';

	$death_date = $record->pers_death_date;
	$age = ($year - $record->birth_year);
	// pers_death_date known: print
	// pers_death_date not known, age > 110, pers_death_date is not known, otherwise empty (probably alive)
	if ($death_date !=''){
		$died =language_date($death_date);
	}
	else if ($age > $max_age){
		$died = '? ';
	}
	else{
		$died = '  ';
	}

	// Highlight present day
	if ($birth_day == $today){
		echo '<tr bgcolor="#BFBFBF">'."\n";
	}
	else{
		echo "<tr>\n";
	}

	if ($calendar_day==$last_cal_day)
		echo "<td><br></td>";
	else
		echo "<td>$calendar_day $month</td>";
	$last_cal_day=$calendar_day;

	if (!$person_cls->privacy)
		echo "<td>".$record->birth_year."</td>";
	else
		echo '<td>'.__(' PRIVACY FILTER').'</td>';

	echo '<td align="left">'.$person_name.'</td>';

	if (!$person_cls->privacy)
		echo '<td><div class="pale">'.$died.'</div></td>';
	else
		echo '<td><div class="pale">'.__(' PRIVACY FILTER').'</div></td>';

	echo "</tr>\n";

}
echo "</table>\n";
echo "</div>";
include_once(CMS_ROOTPATH."footer.php");
?>