<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

@set_time_limit(3000);

require_once(CMS_ROOTPATH_ADMIN."statistics/maxChart.class.php"); // REQUIRED FOR STATISTICS
include_once (CMS_ROOTPATH."include/person_cls.php");

echo '<h1 align=center>'.__('Statistics').'</h1>';

// *** Use a class to process person data ***
global $person_cls, $statistics_screen;
$person_cls = New person_cls;

// *** Show 1 statistics line ***
function statistics_line($familyDb){
	global $dbh, $language, $person_cls, $selected_language;

	$tree_id=$familyDb->tree_id;

	echo '<tr>';
	if (isset($familyDb->count_lines)){ echo '<td>'.$familyDb->count_lines.'</td>'; }

	$treetext=show_tree_text($familyDb->tree_prefix, $selected_language);
	echo '<td>'.$treetext['name'].'</td>';

	if (!isset($familyDb->count_lines)){ echo '<td>'.$familyDb->stat_date_stat.'</td>'; }

	// *** Check if family is still in the genealogy! ***
	$check_sql=$dbh->query("SELECT * FROM humo_families
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='$familyDb->stat_gedcom_fam'");
	$checkDb=$check_sql->fetch(PDO::FETCH_OBJ);
	$check=false;
	if ($checkDb AND $checkDb->fam_man==$familyDb->stat_gedcom_man AND $checkDb->fam_woman==$familyDb->stat_gedcom_woman){
		$check=true;
	}

	if ($check==true){
		if(CMS_SPECIFIC == "Joomla") {
			print '<td><a href="index.php?option=com_humo-gen&amp;task=family&amp;id='.$familyDb->stat_gedcom_fam.'&amp;database='.$familyDb->tree_prefix.
		'">'.__('Family').': </a>';
		}
		else {
			print '<td><a href="../family.php?id='.$familyDb->stat_gedcom_fam.'&amp;database='.$familyDb->tree_prefix.
		'">'.__('Family').': </a>';
		}

		//*** Man ***
		$person_qry=$dbh->query("SELECT * FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$familyDb->stat_gedcom_man."'");
		$personDb=$person_qry->fetch(PDO::FETCH_OBJ);

		if (!$familyDb->stat_gedcom_man){
			echo 'N.N.';
		}
		else{
			$name=$person_cls->person_name($personDb);
			echo $name["standard_name"];
		}

		echo " &amp; ";

		//*** Woman ***
		$person_qry=$dbh->query("SELECT * FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$familyDb->stat_gedcom_woman."'");
		$personDb=$person_qry->fetch(PDO::FETCH_OBJ);
		if (!$familyDb->stat_gedcom_woman){
			echo 'N.N.';
		}
		else{
			$name=$person_cls->person_name($personDb);
			echo $name["standard_name"];
		}
	}
	else{
		echo '<td><b>'.__('FAMILY NOT FOUND IN FAMILY TREE').'</b></td>';
	}
}

// *** Show 1 month, statistics calender ***
// *** calender($month, $year, true/false); ***
function calender($month, $year, $thismonth){
	global $dbh, $language, $statistics_screen;

	echo '<table class="humo standard" border="1" cellspacing="0">';
	if ($month=='1'){ $calender_head=__('January'); }
	if ($month=='2'){ $calender_head=__('February'); }
	if ($month=='3'){ $calender_head=__('March'); }
	if ($month=='4'){ $calender_head=__('April'); }
	if ($month=='5'){ $calender_head=__('may'); }
	if ($month=='6'){ $calender_head=__('June'); }
	if ($month=='7'){ $calender_head=__('July'); }
	if ($month=='8'){ $calender_head=__('August'); }
	if ($month=='9'){ $calender_head=__('September'); }
	if ($month=='10'){ $calender_head=__('October'); }
	if ($month=='11'){ $calender_head=__('November'); }
	if ($month=='12'){ $calender_head=__('December'); }
	echo '<tr class="table_header"><th colspan="8">'.$calender_head.' '.$year.'</th></TR>';
	echo '<tr><th>Nr.</th><th>'.__('Monday').'</th><th>'.__('Tuesday').'</th><th>'.__('Wednesday').'</th><th>'.__('Thursday').'</th><th>'.__('Friday').'</th><th>'.__('Saturday').'</th><th>'.__('Sunday').'</th></tr>';
	$week=mktime (0,0,0,$month,1,$year);
	$week_number = date ("W", $week);
	echo "<tr><th>$week_number</th>";

	// If neccesary skip days at start of month
	$First_Day_Of_Month = date("w", mktime(0, 0, 0, $month, 1, $year));
	if ($First_Day_Of_Month > "1") {
		echo '<td colspan="' . ($First_Day_Of_Month-1) . '"><br></td>';
	}
	// Sunday:
	if ($First_Day_Of_Month == "0") { echo '<td colspan="6"><br></td>'; }

	// Show days
	$Days_In_Month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
	$day=1; $row=1; $field=$First_Day_Of_Month;
	if ($field=='0'){ $field=7; }  // First day is sunday.

	$i = 1;
	for ($i; $i <= $Days_In_Month ;$i++) {
		$present_day=date("Y-n-d");
		if ($day<10){ $day='0'.$day; }
		$date=$year.'-'.$month.'-'.$day;
		$yesterday=strtotime ($date);
		$today=$yesterday+86400;

		if ($statistics_screen=='visitors'){
			// *** Show visitors ***
			$datasql = $dbh->query("SELECT stat_ip_address FROM humo_stat_date
				WHERE stat_date_linux > ".$yesterday." AND stat_date_linux < ".$today.' GROUP BY stat_ip_address');
		}
		else{
			// *** Show families ***
			$datasql = $dbh->query("SELECT * FROM humo_stat_date
				WHERE stat_date_linux > ".$yesterday." AND stat_date_linux < ".$today);
		}

		if ($datasql){ $nr_statistics=$datasql->rowCount(); }

		// *** Use another colour for present day ***
		$color=''; if ($date==$present_day){ $color=' bgcolor="#00FFFF"'; }

		echo "<td$color>$day <b>$nr_statistics</b></td>";
		$day++;
		if ($day<=$Days_In_Month){
			$field++;
			if ($field == 8) {
				$week=mktime (0,0,0,$month,$day,$year);
				$week_number = date ("W", $week);
				echo "</tr>\n";
				echo "<tr><th>$week_number</th>";
				$row++;
				$field = 1;
			}
		}

		// *** Array for graphical statistics ***
		$data[$day-1]=$nr_statistics;
	}

	// Add end month spacers
	if ((8 - $field) >= "1") { echo '<td colspan="'.(8 - $field).'"><br></td></tr>'; }

	// *** Always make 6 rows ***
	if ($row==5){ echo "</tr><tr><td colspan=8><br></td></tr>"; }

	echo "</table><br>\n";

	if(CMS_SPECIFIC == "Joomla") {  // make the graph scrollable
		echo '<div style="width:100%;height:230px;overflow:auto;">';
		echo '<div style="height:210px;width:1000px;overflow:visible;">';
	}
	// *** Show graphical month statistics ***
	//$this_month=$thismonth;
	$mc = new maxChart($data);
	//$mc->displayChart($calender_head."&nbsp;".$year,1,700,200,false,$this_month);
	$mc->displayChart($calender_head."&nbsp;".$year,1,700,200,false,$thismonth);
	if(CMS_SPECIFIC == "Joomla") {
		echo '</div></div>';
	}
}

// *** Function to show year statistics ***
function year_graphics($month, $year)  {
	global $dbh, $language, $statistics_screen;
	$start_month=$month + 1;
	$start_year=$year - 1;
	if($month==12) {
		$start_year=$year;
		$start_month=1;
	}
	for ($i=1; $i<13; $i++) {
		if($start_month==13) { $start_month=1; $start_year++; }

		$date=$start_year.'-'.$start_month.'-'."1";
		$first_day=strtotime ($date);
		$Days_In_Month = cal_days_in_month(CAL_GREGORIAN, $start_month, $start_year);
		$latest_day=$first_day+(86400*$Days_In_Month);

		if ($statistics_screen=='visitors'){
			// *** Show visitors ***
			$datasql = $dbh->query("SELECT stat_ip_address FROM humo_stat_date
				WHERE stat_date_linux > ".$first_day." AND stat_date_linux < ".$latest_day."
				GROUP BY stat_ip_address");
		}
		else{
			// *** Show visited families ***
			$datasql = $dbh->query("SELECT * FROM humo_stat_date
				WHERE stat_date_linux > ".$first_day." AND stat_date_linux < ".$latest_day);
		}

		if ($datasql){ $nr_statistics=$datasql->rowCount(); }

		if ($start_month=='1'){ $month_name=__('jan'); }
		if ($start_month=='2'){ $month_name=__('feb'); }
		if ($start_month=='3'){ $month_name=__('mar'); }
		if ($start_month=='4'){ $month_name=__('apr'); }
		if ($start_month=='5'){ $month_name=__('may'); }
		if ($start_month=='6'){ $month_name=__('jun'); }
		if ($start_month=='7'){ $month_name=__('jul'); }
		if ($start_month=='8'){ $month_name=__('aug'); }
		if ($start_month=='9'){ $month_name=__('sep'); }
		if ($start_month=='10'){ $month_name=__('oct'); }
		if ($start_month=='11'){ $month_name=__('nov'); }
		if ($start_month=='12'){ $month_name=__('dec'); }
		$short_year=substr($start_year,2);
		$twelve_months[$month_name."&nbsp;".$short_year]=$nr_statistics;
		$start_month++;
	}
	$mc = new maxChart($twelve_months);
	$this_month = date("n");

	if(CMS_SPECIFIC == "Joomla") {  // make the graph scrollable
		echo '<div style="width:100%;height:230px;overflow:auto;">';
		echo '<div style="height:210px;width:1000px;overflow:visible;">';
	}

	if ($statistics_screen=='visitors'){
		$mc->displayChart(__('Visitors'),1,700,200,false,$this_month);
	}
	else{
		$mc->displayChart(__('Visited families in the past 12 months'),1,700,200,false,$this_month);
	}

	if(CMS_SPECIFIC == "Joomla") {
			echo '</div>';
		echo '</div>';
	}

}
// End statistics

//*************BEGIN COUNTRY STATISTICS FUNCTIONS ******************
function iptocountry($ip, $path) {
	global $language;
	$numbers = preg_split( "/\./", $ip);
	//if(CMS_SPECIFIC == "Joomla") {   // include_once doesn't work in joomla
			include($path.$numbers[0].".php");
	//}
	//else {
	//   include_once($path.$numbers[0].".php");
	//}
	$code=($numbers[0] * 16777216) + ($numbers[1] * 65536) + ($numbers[2] * 256) + ($numbers[3]);

	foreach($ranges as $key => $value){
		if($key<=$code){
			if($ranges[$key][0]>=$code){$country=$ranges[$key][1];break;}
		}
	}
	if (!isset($country)){$country=__('Unknown');}
	return $country;
}

function country(){
	global $dbh, $language;

	// *** For test purposes ***
	$path=CMS_ROOTPATH_ADMIN.'ip_files/';
	// *** For test only ***
	//if (file_exists("../../humo-gen ip_files")){ $path='../../humo-gen ip_files/'; }

	$stat=$dbh->query("SELECT stat_ip_address FROM humo_stat_date GROUP BY stat_ip_address");
	// Print out result
	while($row = $stat->fetch()){
		$country=iptocountry($row['stat_ip_address'], $path);
		if (isset($countries[$country])) {
			$countries[$country]++;
		}
		else {
			$countries[$country]=1;
		}
	}

	echo '<table class="humo" border="1" cellspacing="0" width="100%">';
	echo '<tr class="table_header"><th>'.__('Country of origin').'</th> <th>'.__('Number of unique visitors').'</th> </tr>';
	if (isset($countries)){
		arsort($countries);
		foreach ($countries as $key => $value) {
			include_once($path."countries.php");
			echo '<tr><td>';
			//flag
			//$file_to_check="ip_files/flags/".$key.".gif";
			$file_to_check=$path."flags/".$key.".gif";
			if (file_exists($file_to_check)){
				print '<img src="'.$file_to_check.'" width="30" height="15">';
			}
			else{
				print '<img src="'.$path.'flags/noflag.gif" width=30 height=15>';
			}
			echo "&nbsp;";
			if($key != __('Unknown')) {
				echo $countries[$key][1].'&nbsp;('.$key.')</td>';
			}
			else {
				echo $key;
			}
			echo '<td>'.$value.'</td></tr>';
		}
	}

	echo '<tr><td>'.__('Total number of unique visitors:').'</td>';
	$total=$stat->rowCount();
	echo '<td>'.$total.'</td></tr>';
	echo '</table>';

	//echo "<br>";
}
// ************ END COUNTRY STATISTICS FUNCTIONS  ***************

$statistics_screen='general_statistics';
if (isset($_POST['statistics_screen']) AND $_POST['statistics_screen']=='date_statistics'){ $statistics_screen='date_statistics'; }
if (isset($_POST['statistics_screen']) AND $_POST['statistics_screen']=='visitors'){ $statistics_screen='visitors'; }
if (isset($_POST['statistics_screen']) AND $_POST['statistics_screen']=='statistics_old'){ $statistics_screen='statistics_old'; }
if (isset($_POST['statistics_screen']) AND $_POST['statistics_screen']=='remove'){ $statistics_screen='remove'; }
if (isset($_GET['tree_prefix'])){ $statistics_screen='statistics_old'; }

// *** Show buttons ***
if(CMS_SPECIFIC == "Joomla") {
	$phpself = "index.php?option=com_humo-gen&amp;task=admin&amp;page=statistics";
}
else {
	$phpself = $_SERVER['PHP_SELF'];
}

echo '<table class="humo" style="width:90%; text-align:center; border:1px solid black;"><tr class="table_header_large"><td>';
	echo ' <form method="POST" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="statistics_screen" value="general_statistics">';
		$style=''; if ($statistics_screen=='general_statistics'){ $style=' class="selected_item"'; }
		echo '<input type="Submit" name="submit" value="'.__('General statistics').'"'.$style.'>';
	echo '</form>';
echo '</td><td>';
	echo ' <form method="POST" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo ' <input type="hidden" name="statistics_screen" value="date_statistics">';
		$style=''; if ($statistics_screen=='date_statistics'){ $style=' class="selected_item"'; }
		echo '<input type="Submit" name="submit" value="'.__('Statistics by date').'"'.$style.'>';
	echo '</form>';
echo '</td><td>';
	echo ' <form method="POST" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo ' <input type="hidden" name="statistics_screen" value="visitors">';
		$style=''; if ($statistics_screen=='visitors'){ $style=' class="selected_item"'; }
		echo '<input type="Submit" name="submit" value="'.__('Visitors').'"'.$style.'>';
	echo '</form>';
echo '</td><td>';
	echo ' <form method="POST" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo ' <input type="hidden" name="statistics_screen" value="statistics_old">';
		$style=''; if ($statistics_screen=='statistics_old'){ $style=' class="selected_item"'; }
		echo '<input type="Submit" name="submit" value="'.__('Old statistics').'"'.$style.'>';
	echo '</form>';
echo '</td><td>';
	echo ' <form method="POST" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo ' <input type="hidden" name="statistics_screen" value="remove">';
		$style=''; if ($statistics_screen=='remove'){ $style=' class="selected_item"'; }
		echo '<input type="Submit" name="submit" value="'.__('Remove statistics').'"'.$style.'>';
	echo '</form>';
echo '</td></tr></table>';

// *** Remove old statistics ***
if (isset($_POST['remove2'])){
	$timestamp=mktime(0, 0, 0, $_POST['stat_month'], $_POST['stat_day'], $_POST['stat_year']);

	$sql='DELETE FROM humo_stat_date WHERE stat_date_linux < "'.$timestamp.'"';
	$result = $dbh->query($sql);

	echo '<div class="confirm">';
	echo __('Old statistics').' '.date("d-m-Y", $timestamp).' '.__('are erased');
	echo '</div>';
}

if ($statistics_screen=='remove'){
	echo '<h2>'.__('Remove statistics').'</h2>';

	echo __('Statistics will be removed PERMANENTLY. Make a backup first to save the statistics data').'<br>';

	echo '<form method="POST" action="">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo __('Remove ALL statistics BEFORE this date:');
	echo ' <input type="text" name="stat_day" value="1" size="1">';
	$month=date("m"); $year=date("Y");
	$month--; if ($month==0){ $month=12; $year--; }
	echo ' <input type="text" name="stat_month" value="'.$month.'" size="1">	';

	echo ' <input type="text" name="stat_year" value="'.$year.'" size="2"> '.__('d-m-yyyy').'<br>';

	echo '<input type ="Submit" name="remove2" value="'.__('REMOVE statistic data').'">';
	echo '</form>';
}

if ($statistics_screen=='general_statistics'){

	echo '<h2 align="center">'.__('Status statistics table').'</h2>';
	$family_qry=$dbh->query("SELECT *, count(humo_stat_date.stat_easy_id) as count_lines
		FROM humo_stat_date LEFT JOIN humo_trees
		ON humo_trees.tree_id=humo_stat_date.stat_tree_id
		GROUP BY humo_stat_date.stat_tree_id
		ORDER BY tree_order desc");
	echo '<table class="humo standard" border="1" cellspacing="0">';
	echo '<tr class="table_header"><th>'.__('family tree').'</th><th>Records</th><th>'.__('Number of unique visitors').'</th></tr>';
	while ($familyDb=$family_qry->fetch(PDO::FETCH_OBJ)){
		//statistics_line($familyDb);
		if ($familyDb->tree_prefix){
			$tree_id=$familyDb->tree_id;
			// *** Show family tree name ***
			$treetext=show_tree_text($familyDb->tree_prefix, $selected_language);
			echo '<tr><td>'.$treetext['name'].'</td>';
		}
		else{
			echo '<tr><td><b>'.__('FAMILY TREE ERASED').'</b></td>';
		}
		echo '<td>'.$familyDb->count_lines.'</td>';

		// *** Total number of unique visitors ***
		$count_visitors=0;
		if ($familyDb->tree_id){
			$stat=$dbh->query("SELECT *
				FROM humo_stat_date LEFT JOIN humo_trees
				ON humo_trees.tree_id=humo_stat_date.stat_tree_id
				WHERE humo_trees.tree_id=".$familyDb->tree_id."
				GROUP BY stat_ip_address
				");
			$count_visitors=$stat->rowCount();
		}
		echo '<td>'.$count_visitors.'</td>';
		echo '</tr>';
	}
	echo '</table>';

	echo '<h2 align="center">'.__('General statistics:').'</h2>';

	echo '<table class="humo standard" border="1" cellspacing="0">';
	echo '<tr class="table_header"><th>'.__('Item').'</th><th>'.__('Counter').'</th></tr>';
		// *** Total number unique visitors ***
		$stat=$dbh->query("SELECT * FROM humo_stat_date GROUP BY stat_ip_address");
		$count_visitors=$stat->rowCount();
		echo '<tr><td>'.__('Total number of unique visitors:').'</td><td>'.$count_visitors.'</td>';

		// *** Total number visited families ***
		$datasql = $dbh->query("SELECT * FROM humo_stat_date");
		if ($datasql){ $total=$datasql->rowCount(); }
		echo '<tr><td>'.__('Total number of visited families:').'</td><td>'.$total.'</td>';

		// Visitors per day/ month/ year.
		// 1 day = 86400
		$time_period=strtotime ("now")-3600; // 1 hour
		$datasql = $dbh->query("SELECT * FROM humo_stat_date WHERE stat_date_linux > ".$time_period);
		if ($datasql){ $total=$datasql->rowCount(); }
		echo '<tr><td>'.__('Total number of families in the last hour:').'</td><td>'.$total.'</td>';
	echo '</table>';

	//******** START COUNTRY STATISTICS (second file check for test) *************
	if (file_exists(CMS_ROOTPATH_ADMIN."ip_files") OR file_exists("../../humo-gen ip_files")) {
		echo '<br><b>'.__('Unique visitors - Country of origin').'</b><br>';
		country();
	}
	//******** END COUNTRY STATISTICS ********

	$nr_lines=15; // *** Nr. of statistics lines ***

	$family_qry=$dbh->query("SELECT *, count(humo_stat_date.stat_easy_id) as count_lines
		FROM humo_stat_date, humo_trees
		WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id
		GROUP BY humo_stat_date.stat_easy_id desc
		ORDER BY count_lines desc
		LIMIT 0,".$nr_lines);
	echo '<h2 align="center">'.$nr_lines.' '.__('Most visited families:').'</h2>';
	echo '<table class="humo standard" border="1" cellspacing="0">';
		echo '<tr class="table_header"><th>#</th><th>'.__('family tree').'</th><th>'.__('family').'</th></tr>';
		while ($familyDb=$family_qry->fetch(PDO::FETCH_OBJ)){ statistics_line($familyDb); }
	echo '</table>';

	$family_qry=$dbh->query("SELECT * FROM humo_stat_date, humo_trees
		WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id
		ORDER BY humo_stat_date.stat_date_stat DESC LIMIT 0,".$nr_lines);
	echo '<h2 align="center">'.$nr_lines.' '.__('last visited families:').'</h2>';
	echo '<table class="humo standard" border="1" cellspacing="0">';
		echo '<tr class="table_header"><th>'.__('family tree').'</th><th>'.__('date-time').'</th><th>'.__('family').'</th></tr>';
		while ($familyDb=$family_qry->fetch(PDO::FETCH_OBJ)){ statistics_line($familyDb); }
	echo '</table>';
}


if ($statistics_screen=='date_statistics'){
	// *** Selection of month ***
	$present_month = date("n");
	$month = $present_month;
	if (isset($_POST['month'])){ $month=$_POST['month']; }

	echo '<div class="center">';
		echo '<br><form method="POST" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo ' <input type="hidden" name="statistics_screen" value="date_statistics">';
			echo "<select size='1' name='month'>";
				$select=''; if ($month=='1'){ $select=' SELECTED'; }
				echo '<option value="1"'.$select.'>'.__('January').'</option>';
				$select=''; if ($month=='2'){ $select=' SELECTED'; }
				echo '<option value="2"'.$select.'>'.__('February').'</option>';
				$select=''; if ($month=='3'){ $select=' SELECTED'; }
				echo '<option value="3"'.$select.'>'.__('March').'</option>';
				$select=''; if ($month=='4'){ $select=' SELECTED'; }
				echo '<option value="4"'.$select.'>'.__('April').'</option>';
				$select=''; if ($month=='5'){ $select=' SELECTED'; }
				echo '<option value="5"'.$select.'>'.__('may').'</option>';
				$select=''; if ($month=='6'){ $select=' SELECTED'; }
				echo '<option value="6"'.$select.'>'.__('June').'</option>';
				$select=''; if ($month=='7'){ $select=' SELECTED'; }
				echo '<option value="7"'.$select.'>'.__('July').'</option>';
				$select=''; if ($month=='8'){ $select=' SELECTED'; }
				echo '<option value="8"'.$select.'>'.__('August').'</option>';
				$select=''; if ($month=='9'){ $select=' SELECTED'; }
				echo '<option value="9"'.$select.'>'.__('September').'</option>';
				$select=''; if ($month=='10'){ $select=' SELECTED'; }
				echo '<option value="10"'.$select.'>'.__('October').'</option>';
				$select=''; if ($month=='11'){ $select=' SELECTED'; }
				echo '<option value="11"'.$select.'>'.__('November').'</option>';
				$select=''; if ($month=='12'){ $select=' SELECTED'; }
				echo '<option value="12"'.$select.'>'.__('December').'</option>';
			echo "</select>";

			// *** Selection of year ***

			// *** Search oldest record in database***
			$datasql = $dbh->query("SELECT * FROM humo_stat_date ORDER BY stat_date_linux LIMIT 0,1");
			$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
			$first_year = date("Y",$dataDb->stat_date_linux);

			$present_year = date("Y");
			$year=$present_year;
			if (isset($_POST['year'])){ $year=$_POST['year']; }

			echo " <select size='1' name='year'>";
			for ($year_select=$first_year; $year_select<=$present_year; $year_select++) {
				$select='';
				if ($year==$year_select){ $select=' SELECTED'; }
				echo '<option value="'.$year_select.'"'.$select.'>'.$year_select.'</option>';
			}
			echo "</select>";

			echo ' <input type="Submit" name="submit" value="Select">';

		echo '</form>';

		// *** Visited families in this month ***
		echo '<br><br><b>'.__('Total number of visited families:').'</b><br>';
	echo '</div><br>';

	// Graphic present month
	if ($month==$present_month AND $year==$present_year){
		calender($month, $year, true);
	}
	else{
		calender($month, $year, false);
	}

	// Graphic year
	echo "<br>";
	year_graphics($month, $year);

}

if ($statistics_screen=='visitors'){

	// *** Selection of month ***
	$present_month = date("n");
	$month = $present_month;
	if (isset($_POST['month'])){ $month=$_POST['month']; }

	echo '<div class="center">';
		echo '<br><form method="POST" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo ' <input type="hidden" name="statistics_screen" value="visitors">';
			echo "<select size='1' name='month'>";
				$select=''; if ($month=='1'){ $select=' SELECTED'; }
				echo '<option value="1"'.$select.'>'.__('January').'</option>';
				$select=''; if ($month=='2'){ $select=' SELECTED'; }
				echo '<option value="2"'.$select.'>'.__('February').'</option>';
				$select=''; if ($month=='3'){ $select=' SELECTED'; }
				echo '<option value="3"'.$select.'>'.__('March').'</option>';
				$select=''; if ($month=='4'){ $select=' SELECTED'; }
				echo '<option value="4"'.$select.'>'.__('April').'</option>';
				$select=''; if ($month=='5'){ $select=' SELECTED'; }
				echo '<option value="5"'.$select.'>'.__('may').'</option>';
				$select=''; if ($month=='6'){ $select=' SELECTED'; }
				echo '<option value="6"'.$select.'>'.__('June').'</option>';
				$select=''; if ($month=='7'){ $select=' SELECTED'; }
				echo '<option value="7"'.$select.'>'.__('July').'</option>';
				$select=''; if ($month=='8'){ $select=' SELECTED'; }
				echo '<option value="8"'.$select.'>'.__('August').'</option>';
				$select=''; if ($month=='9'){ $select=' SELECTED'; }
				echo '<option value="9"'.$select.'>'.__('September').'</option>';
				$select=''; if ($month=='10'){ $select=' SELECTED'; }
				echo '<option value="10"'.$select.'>'.__('October').'</option>';
				$select=''; if ($month=='11'){ $select=' SELECTED'; }
				echo '<option value="11"'.$select.'>'.__('November').'</option>';
				$select=''; if ($month=='12'){ $select=' SELECTED'; }
				echo '<option value="12"'.$select.'>'.__('December').'</option>';
			echo "</select>";

			// *** Selection of year ***

			// *** Find oldest record in database ***
			$datasql = $dbh->query("SELECT * FROM humo_stat_date ORDER BY stat_date_linux LIMIT 0,1");
			$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
			$first_year = date("Y",$dataDb->stat_date_linux);

			$present_year = date("Y");
			$year=$present_year;
			if (isset($_POST['year'])){ $year=$_POST['year']; }

			echo " <select size='1' name='year'>";
			for ($year_select=$first_year; $year_select<=$present_year; $year_select++) {
				$select='';
				if ($year==$year_select){ $select=' SELECTED'; }
				echo '<option value="'.$year_select.'"'.$select.'>'.$year_select.'</option>';
			}
			echo "</select>";

			echo ' <input type="Submit" name="submit" value="Select">';

		echo '</form>';

		// *** Visitors in present month ***
		echo '<br><br><b>'.__('Visitors').'</b><br>';
	echo '</div><br>';

	// Graphic of present month
	if ($month==$present_month AND $year==$present_year){
		calender($month, $year, true);
	}
	else{
		calender($month, $year, false);
	}

	// year graphic
	echo "<br>";
	year_graphics($month, $year);

	// *** User agent ***
	echo '<br><b>'.__('User agent information').'</b><br>';
	// *** Show user agent info (50 most used user agents) ***
	$datasql=$dbh->query("SELECT *, count(humo_stat_date.stat_user_agent) as count_lines
		FROM humo_stat_date
		WHERE stat_user_agent LIKE '_%'
		GROUP BY humo_stat_date.stat_user_agent desc
		ORDER BY count_lines desc
		LIMIT 0,50");
	
	while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
		$stat_user_agent=$dataDb->stat_user_agent;
		if (count_chars($stat_user_agent)>100){
			$stat_user_agent=substr($stat_user_agent,0,100).'...';
		}
		echo '<b>'.$dataDb->count_lines.'</b> '.$stat_user_agent.'<br>';
	}

}

if ($statistics_screen=='statistics_old'){

	// *************************
	// *** OLD statistics ***
	// *************************

	// *** Change prefix ***
	if (isset($_GET['tree_prefix'])){
		$_SESSION['tree_prefix']=$_GET['tree_prefix'];
	}
	if (!isset($_SESSION["tree_prefix"])){
		$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
		@$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
		$_SESSION['tree_prefix']=$dataDb->tree_prefix;
	}

	// *** Select database ***
	@$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");

	$num_rows = $datasql->rowCount();
	if ($num_rows>1){
		echo '<h2>'.__('Old statistics (numbers since last gedcom update)').'</h2>';

		echo '<b>'.__('Select family tree').'</b><br>';
		while (@$dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
			if ($dataDb->tree_prefix!='EMPTY'){
				//Count persons and families
				$person_qry=$dbh->query("SELECT pers_id FROM humo_persons WHERE pers_tree_id='".$tree_id."'");
				@$count_persons=$person_qry->rowCount();

				$family_qry=$dbh->query("SELECT fam_id FROM humo_families WHERE fam_tree_id='".$tree_id."'");
				@$count_families=$family_qry->rowCount();

				// *** Update date ***
				$date=$dataDb->tree_date;
				$month=''; //voor lege datums
					if (substr($date,5,2)=='01'){ $month=' jan ';}
					if (substr($date,5,2)=='02'){ $month=' feb ';}
					if (substr($date,5,2)=='03'){ $month=' mrt ';}
					if (substr($date,5,2)=='04'){ $month=' apr ';}
					if (substr($date,5,2)=='05'){ $month=' mei ';}
					if (substr($date,5,2)=='06'){ $month=' jun ';}
					if (substr($date,5,2)=='07'){ $month=' jul ';}
					if (substr($date,5,2)=='08'){ $month=' aug ';}
					if (substr($date,5,2)=='09'){ $month=' sep ';}
					if (substr($date,5,2)=='10'){ $month=' okt ';}
					if (substr($date,5,2)=='11'){ $month=' nov ';}
					if (substr($date,5,2)=='12'){ $month=' dec ';}
				$date=substr($date,8,2).$month.substr($date,0,4);

				$treetext=show_tree_text($dataDb->tree_prefix, $selected_language);
				if (isset($_SESSION['tree_prefix']) AND $_SESSION['tree_prefix']==$dataDb->tree_prefix){
					echo '<b>'.$treetext['name'].'</b>';
					$tree_id=$dataDb->tree_id;
				}
				else{
					if(CMS_SPECIFIC == "Joomla") {
						echo '<a href="index.php?option=com_humo-gen&amp;task=admin&amp;page='.$page.'&amp;tree_prefix='.$dataDb->tree_prefix.'">'.$treetext['name'].'</a>';
					}
					else {
						echo '<a href="'.$_SERVER['PHP_SELF'].'?page='.$page.'&amp;tree_prefix='.$dataDb->tree_prefix.'">'.$treetext['name'].'</a>';
					}
				}
				echo ' <font size=-1>('.$date.': '.$count_persons.' '.__('persons').", ".$count_families.' '.__('families').")</font>\n<br>";
			}
		}
	}

	//*** Statistics ***
	echo '<br><b>'.__('Most visited families:').'</b><br>';
		//MAXIMUM 50 LINES
		$family_qry=$dbh->query("SELECT fam_gedcomnumber, fam_counter, fam_man, fam_woman FROM humo_families
			WHERE fam_tree_id='".$tree_id."' AND fam_counter ORDER BY fam_counter desc LIMIT 0,50");
		while ($familyDb=$family_qry->fetch(PDO::FETCH_OBJ)){
			echo $familyDb->fam_counter." ";
				if(CMS_SPECIFIC == "Joomla") {
					echo '<a href="index.php?option=com_humo-gen&amp;task=family&amp;id='.$familyDb->fam_gedcomnumber.'">'.__('Family').': </a>';
				}
				else {
					echo '<a href="../family.php?id='.$familyDb->fam_gedcomnumber.'">'.__('Family').': </a>';
				}

			//*** Man ***
			$person_qry=$dbh->query("SELECT * FROM humo_persons
				WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$familyDb->fam_man."'");
			$personDb=$person_qry->fetch(PDO::FETCH_OBJ);
			if (!$familyDb->fam_man){
				echo 'N.N.';
			}
			else{
				$name=$person_cls->person_name($personDb);
				echo $name["standard_name"];
			}

			echo " &amp; ";

			//*** Woman ***
			$person_qry=$dbh->query("SELECT * FROM humo_persons
				WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$familyDb->fam_woman."'");
			$personDb=$person_qry->fetch(PDO::FETCH_OBJ);
			if (!$familyDb->fam_woman){
				echo 'N.N.';
			}
			else{
				$name=$person_cls->person_name($personDb);
				echo $name["standard_name"];
			}
			echo "<br>";
		}
	// *** End of old statistics ***
}

?>