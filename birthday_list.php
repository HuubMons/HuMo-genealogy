<?php
// *********************************************************
// *** Birtday list                                      ***
// *********************************************************
// Author : Louis Ywema
// Date  : 29-04-2006
// Website: http://www.ywema.com
// 18-06-2011 Huub: translated all remarks and variables into English. And did some minor updates.
// 10-11-2019 Yossi Beck - Added wedding anniversaries and menu

include_once __DIR__ .'/header.php'; // returns CMS_ROOTPATH constant
include_once __DIR__ .'/menu.php';
include_once __DIR__ .'/include/language_date.php';
include_once __DIR__ .'/include/person_cls.php';

// *** Check user authority ***
if ($user["group_birthday_list"]!='j'){
	echo __('You are not authorised to see this page.');
	exit();
}

// *** Month to show ***
if (isset($_GET['month']) AND strlen($_GET['month'])=='3' ){
	$month_check = $_GET['month'];
	$month='jan';
	if ($month_check=='jan') $month='jan';
	if ($month_check=='feb') $month='feb';
	if ($month_check=='mar') $month='mar';
	if ($month_check=='apr') $month='apr';
	if ($month_check=='may') $month='may';
	if ($month_check=='jun') $month='jun';
	if ($month_check=='jul') $month='jul';
	if ($month_check=='aug') $month='aug';
	if ($month_check=='sep') $month='sep';
	if ($month_check=='oct') $month='oct';
	if ($month_check=='nov') $month='nov';
	if ($month_check=='dec') $month='dec';
}
else{
	$month = date ("M");
	$month=strtolower($month);
}
$show_month=language_date($month);

// *** Calculate present date, month and year ***
$today = date('j').' '.date('M');
$today=strtolower($today);
$year = date("Y");

$url_start='<a href="'.CMS_ROOTPATH.'birthday_list.php?month=';

$url_end='';
if(isset($_POST['ann_choice']) AND $_POST['ann_choice'] == 'wedding'){
	$url_end='&amp;ann_choice=wedding';
	if(isset($_POST['civil'])) $url_end.='&amp;civil=civil';
	if(isset($_POST['relig'])) $url_end.='&amp;relig=relig';
}

if(isset($_GET['ann_choice']) AND $_GET['ann_choice'] == 'wedding'){
	$url_end='&amp;ann_choice=wedding';
	if(isset($_GET['civil'])) $url_end.='&amp;civil=civil';
	if(isset($_GET['relig'])) $url_end.='&amp;relig=relig';
}

// *** If month is clicked, also set $_POST ***
if (isset($_GET['ann_choice'])) $_POST['ann_choice']='wedding';
if (isset($_GET['civil'])){ $_POST['ann_choice']='wedding'; $_POST['civil']='wedding'; }
if (isset($_GET['relig'])){ $_POST['ann_choice']='wedding'; $_POST['relig']='relig'; }

$spacer='&nbsp;&#124;&nbsp;';
$newline ="\n";
$max_age = '110';
$last_cal_day=0;

// *** Center page ***
echo '<div class="fonts center">';

// *** Show navigation ***
echo '[ ';
echo ($url_start.'jan'.$url_end.'">'.__('jan')."</a>").$spacer.$newline;
echo ($url_start.'feb'.$url_end.'">'.__('feb')."</a>").$spacer.$newline;
echo ($url_start.'mar'.$url_end.'">'.__('mar')."</a>").$spacer.$newline;
echo ($url_start.'apr'.$url_end.'">'.__('apr')."</a>").$spacer.$newline;
echo ($url_start.'may'.$url_end.'">'.__('may')."</a>").$spacer.$newline;
echo ($url_start.'jun'.$url_end.'">'.__('jun')."</a>").$spacer.$newline;
echo ($url_start.'jul'.$url_end.'">'.__('jul')."</a>").$spacer.$newline;
echo ($url_start.'aug'.$url_end.'">'.__('aug')."</a>").$spacer.$newline;
echo ($url_start.'sep'.$url_end.'">'.__('sep')."</a>").$spacer.$newline;
echo ($url_start.'oct'.$url_end.'">'.__('oct')."</a>").$spacer.$newline;
echo ($url_start.'nov'.$url_end.'">'.__('nov')."</a>").$spacer.$newline;
echo ($url_start.'dec'.$url_end.'">'.__('dec')."</a>");
echo " ]\n";

// *** Show month and year ***
echo '<div class="standard_header">'.ucfirst($show_month).' '.$year.'</div>';

echo '<div>';
echo "<form name='anniv' id='anniv' action='".CMS_ROOTPATH."birthday_list.php?month=".$month."' method='post'>";
	echo "<table class='humo' style='text-align:center;width:40%;margin-left:auto;margin-right:auto;border:1px solid black;'><tr>";
	$check = ' checked'; 
	if(isset($_POST['ann_choice']) AND $_POST['ann_choice'] != 'birthdays') $check = '';
	echo "<td style='border:none'><input id='birthd' onClick='document.getElementById(\"anniv\").submit();' type='radio' name='ann_choice' value='birthdays'".$check.">".__('Birthdays')."</td>";
	$check = ''; 
	if(isset($_POST['ann_choice']) AND $_POST['ann_choice'] == 'wedding') $check = " checked";
	echo "<td style='border:none'><input id='wedd' onClick='document.getElementById(\"anniv\").submit();' type='radio' name='ann_choice' value='wedding'".$check.">".__('Wedding anniversaries')."&nbsp;&nbsp;";
	$check= ' checked';
	if(isset($_POST['ann_choice']) AND !isset($_POST['civil'])) $check = '';
	echo "<span style='font-size:90%'>(<input type='checkbox' onClick='document.getElementById(\"wedd\").checked = true;document.getElementById(\"anniv\").submit();' name='civil' id='civil' value='civil'".$check.">".__('Civil');
	$check= '';
	if(isset($_POST['ann_choice']) AND isset($_POST['relig'])) $check = " checked";
	echo "&nbsp;&nbsp;<input type='checkbox' onClick='document.getElementById(\"wedd\").checked = true;document.getElementById(\"anniv\").submit();' name='relig' id='relig' value='relig'".$check.">".__('Religious').")</span></td>";
	echo '</tr></table>';
echo '</form>';
echo '</div><br>';

// *** Build page ***
if(!isset($_POST['ann_choice']) OR $_POST['ann_choice']=="birthdays") {
	$privcount=0; // *** Count privacy persons ***
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
		WHERE pers_tree_id = :tree_id AND (substring( pers_birth_date, 4,3) = :month
		OR substring( pers_birth_date, 3,3) = :month)
		order by birth_day, birth_year ";

	try {
		$qry = $dbh->prepare( $sql );
		$qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
		$qry->bindValue(':month', $month, PDO::PARAM_STR);
		$qry->execute();
	}catch (PDOException $e) {
		echo $e->getMessage() . '<br>';
	}

	while ($record=$qry->fetch(PDO::FETCH_OBJ)){
		$calendar_day = $record->birth_day;
		$birth_day =$record->birth_day.' '.$month;
		$person_cls = New person_cls;
		$name=$person_cls->person_name($record);
		$person_cls->construct($record);

		if (!$person_cls->privacy){
			// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
			$url=$person_cls->person_url2($record->pers_tree_id,$record->pers_famc,$record->pers_fams,$record->pers_gedcomnumber);

			$person_name='<a href="'.$url.'">'.$name["standard_name"].'</a>';

			$death_date = $record->pers_death_date;
			$age = ($year - $record->birth_year);

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
				echo '<td><br></td>';
			else
				echo '<td>'.$calendar_day.' '.$show_month.'</td>';
			$last_cal_day=$calendar_day;

			if (!$person_cls->privacy)
				echo '<td>'.$record->birth_year.'</td>';
			else
				echo '<td>'.__(' PRIVACY FILTER').'</td>';

			echo '<td align="left">'.$person_name.'</td>';

			if (!$person_cls->privacy)
				echo '<td><div class="pale">'.$died.'</div></td>';
			else
				echo '<td><div class="pale">'.__(' PRIVACY FILTER').'</div></td>';

			echo "</tr>\n";
		}
		else
			$privcount++;
	}

	echo "</table>\n";

	if($privcount) { echo "<br>".$privcount.__(' persons are not shown due to privacy settings').".<br>";}
}

else {
	// wedding anniversary
	$privcount=0; // *** Count privacy persons ***

	echo '<table class="humo" align="center">';
	echo '<tr class=table_headline>'.$newline;
		// *** Show headers ***
		echo '<th>'.__('Day')."</h></td>\n";
		echo '<th>'.ucfirst(__('Wedding year'))."</th>\n";
		echo '<th>'.__('Civil/ Religious')."</h></td>\n";
		echo '<th>'.__('Spouses')."</th>\n";
	echo "</tr>\n";	

	$wed = Array();
	$cnt=0;

	// *** Build query ***
	if(isset($_POST['civil'])) {
		$sql = "SELECT *,
		abs(substring( fam_marr_date,1,2 )) as marr_day,
		substring( fam_marr_date,-4 ) as marr_year
		FROM humo_families
		WHERE fam_tree_id = :tree_id AND (substring( fam_marr_date, 4,3) = :month
		OR substring( fam_marr_date, 3,3) = :month)
		order by marr_day, marr_year ";

		try {
			$qry = $dbh->prepare( $sql );
			$qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$qry->bindValue(':month', $month, PDO::PARAM_STR);
			$qry->execute();
		}
		catch (PDOException $e) {
			echo $e->getMessage() . "<br/>";
		}

		while ($record=$qry->fetch(PDO::FETCH_OBJ)) {
			$wed[$cnt]['calday'] = $record->marr_day;
			$wed[$cnt]['marday'] =$record->marr_day.' '.$month;
			$wed[$cnt]['maryr']=$record->marr_year;
			$day =$record->marr_day;  if(strlen($record->marr_day)==1) $day = "0".$day; 
			$wed[$cnt]['dayyear'] = $day.$record->marr_year;
			$wed[$cnt]['man']=$record->fam_man;
			$wed[$cnt]['woman']=$record->fam_woman;
			$wed[$cnt]['type']= __('Civil');
			$cnt++;
		}
	}

	if(isset($_POST['relig'])) {
		$sql = "SELECT *,
		abs(substring( fam_marr_church_date,1,2 )) as marr_day,
		substring( fam_marr_church_date,-4 ) as marr_year
		FROM humo_families
		WHERE fam_tree_id = :tree_id AND (substring( fam_marr_church_date, 4,3) = :month
		OR substring( fam_marr_church_date, 3,3) = :month)
		order by marr_day, marr_year ";
		try {
			$qry = $dbh->prepare( $sql );
			$qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
			$qry->bindValue(':month', $month, PDO::PARAM_STR);
			$ccc = $qry->execute();
		}
		catch (PDOException $e) {
			echo $e->getMessage() . '<br>';
		}
		while ($record=$qry->fetch(PDO::FETCH_OBJ)) {
			$wed[$cnt]['calday'] = $record->marr_day;
			$wed[$cnt]['marday'] =$record->marr_day.' '.$month;
			$wed[$cnt]['maryr']=$record->marr_year;
			$day =$record->marr_day;  if(strlen($record->marr_day)==1) $day = "0".$day;  // for sorting array
			$wed[$cnt]['dayyear'] = $day.$record->marr_year;
			$wed[$cnt]['man']=$record->fam_man;
			$wed[$cnt]['woman']=$record->fam_woman;
			$wed[$cnt]['type']= __('Religious');
			$cnt++;
		}
	}
	if(isset($wed) AND count($wed)>0) {
		// sort the array to mix civill and religious
		if(isset($_POST['civil']) AND isset($_POST['relig'])) {
			function custom_sort($a,$b) {
				//return $a['dayyear']>$b['dayyear']; // DEPRECATED in PHP 8.
				return $a['dayyear']<=>$b['dayyear'];
			}
			// Sort the multidimensional array
			usort($wed, "custom_sort");
			// Define the custom sort function
		}

		foreach($wed AS $key => $value) {
			// get husband
			@$manDb = $db_functions->get_person($value['man']);
			// *** Use class to process person ***
			$man_cls = New person_cls;
			$man_cls->construct($manDb);
			if (!$value['man'])
				$man_name='N.N.';
			else{
				$name=$man_cls->person_name($manDb);

				// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
				$url=$man_cls->person_url2($manDb->pers_tree_id,$manDb->pers_famc,$manDb->pers_fams,$manDb->pers_gedcomnumber);

				$man_name='<a href="'.$url.'">'.$name["standard_name"].'</a>';
			}

			// get wife
			@$womanDb = $db_functions->get_person($value['woman']);
			// *** Use class to process person ***
			$woman_cls = New person_cls;
			$woman_cls->construct($womanDb);
			if (!$value['woman'])
				$woman_name='N.N.';
			else{
				$name=$woman_cls->person_name($womanDb);

				// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
				$url=$woman_cls->person_url2($womanDb->pers_tree_id,$womanDb->pers_famc,$womanDb->pers_fams,$womanDb->pers_gedcomnumber);

				$woman_name='<a href="'.$url.'">'.$name["standard_name"].'</a>';
			}

			$calendar_day = $value['calday'];
			$marr_day =$value['marday'];

			if (!$man_cls->privacy AND !$woman_cls->privacy){
				// Highlight present day
				if ($marr_day == $today){
					echo '<tr bgcolor="#BFBFBF">'."\n";
				}
				else{
					echo "<tr>\n";
				}
				if ($calendar_day==$last_cal_day)
					echo '<td><br></td>';
				else
					echo '<td>'.$calendar_day.' '.$show_month.'</td>';

				$last_cal_day=$calendar_day;
				if (!$man_cls->privacy AND !$woman_cls->privacy)
					echo '<td>'.$value['maryr'].'</td>';
				else
					echo '<td>'.__(' PRIVACY FILTER').'</td>';

				echo '<td align="left">'.$value['type'].'</td>';
				echo '<td align="left">'.$man_name.' & '.$woman_name.'</td>';

				echo "</tr>\n";
			}
			else
				$privcount++;

		}
		unset($wed);

	}
	else {
		echo '<tr><td colspan="4">'.__('No results found for this month').'</td></tr>';
	}
	echo "</table>\n";

	if($privcount) { echo "<br>".$privcount.__(' persons are not shown due to privacy settings').".<br>";}
}
echo '</div>';

include_once __DIR__ .'/footer.php';
