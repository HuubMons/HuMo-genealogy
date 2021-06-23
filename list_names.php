<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

echo '<p class="fonts">';

	//*** Find first first_character of last name ***
	print '<div style="text-align:center">';
	$person_qry="SELECT UPPER(substring(pers_lastname,1,1)) as first_character
		FROM humo_persons WHERE pers_tree_id='".$tree_id."' GROUP BY first_character ORDER BY first_character";

	// *** Search pers_prefix for names like: "van Mons" ***
	if ($user['group_kindindex']=="j"){
		$person_qry="SELECT UPPER(substring(CONCAT(pers_prefix,pers_lastname),1,1)) as first_character
			FROM humo_persons WHERE pers_tree_id='".$tree_id."' GROUP BY first_character ORDER BY first_character";
	}
	@$person_result= $dbh->query($person_qry);
	while(@$personDb=$person_result->fetch(PDO::FETCH_OBJ)) {
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list_names&amp;tree_id='.$tree_id.'&amp;last_name='.$personDb->first_character;
		}
		elseif ($humo_option["url_rewrite"]=="j"){
			// *** url_rewrite ***
			// *** $uri_path is made header.php ***
			//$path_tmp=$uri_path.'list_names/'.$_SESSION['tree_prefix'].'/'.$personDb->first_character.'/';
			$path_tmp=$uri_path.'list_names/'.$tree_id.'/'.$personDb->first_character.'/';
		}
		else{
			$path_tmp=CMS_ROOTPATH.'list_names.php?tree_id='.$tree_id.'&amp;last_name='.$personDb->first_character;
		}
		echo ' <a href="'.$path_tmp.'">'.$personDb->first_character.'</a>';
	}
 
	if (CMS_SPECIFIC=='Joomla'){
		$path_tmp='index.php?option=com_humo-gen&amp;task=list_names&amp;last_name=all';
	}
	else {
		$path_tmp=CMS_ROOTPATH."list_names.php?last_name=all";
	}
	echo ' <a href="'.$path_tmp.'">'.__('All names')."</a>\n";
	echo '</div><br>';

// *** Alphabet find first first_character of lastname ***
$last_name='a'; // *** Default first_character ***
// *** Search variables in: http://localhost/humo-gen/list/humo1_/M/ ***
//if (isset($urlpart[1])){
//	$last_name=urldecode(safe_text_db($urlpart[1]));   // without urldecode skandinavian letters don't work!
//}
if (isset($_GET['last_name'])){
	$last_name=safe_text_db($_GET['last_name']);
}
//echo 'TEST'.$_GET['last_name'].'!'.$lastname.'!';

// *** MAIN SETTINGS ***
$maxcols = 5; // number of name & nr colums in table. For example 3 means 3x name col + nr col
if(isset($_POST['maxcols'])){
	$maxcols = $_POST['maxcols'];
	$_SESSION["save_maxcols"]=$maxcols;
}
if (isset($_SESSION["save_maxcols"])) $maxcols=$_SESSION["save_maxcols"];

$maxnames = 201;
if(isset($_POST['freqsurnames'])) {
	$maxnames = $_POST['freqsurnames'];
	$_SESSION["save_maxnames"]=$maxnames;
}
if (isset($_SESSION["save_maxnames"])) $maxnames=$_SESSION["save_maxnames"];
$nr_persons=$maxnames;

$item=0; if (isset($_GET['item'])){ $item=$_GET['item']; }
$start=0; if (isset($_GET["start"])){ $start=$_GET["start"]; }
$uri_path_string='list_names.php?';


function tablerow($nr,$lastcol=false) {
	// displays one set of name & nr column items in the row
	// $nr is the array number of the name set created in function last_names
	// if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
	global $user, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $tree_id;
	//if (CMS_SPECIFIC=='Joomla'){
	//	$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;tree_id='.$tree_id;
	//}
	//else{
		$path_tmp=CMS_ROOTPATH.'list.php?tree_id='.$tree_id;
	//}
	echo '<td class="namelst">';
	if (isset($freq_last_names[$nr])) {
		$top_pers_lastname=''; if ($freq_pers_prefix[$nr]){ $top_pers_lastname=str_replace("_", " ", $freq_pers_prefix[$nr]); }
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
	else echo '-';
	echo '</td>';
	
	if($lastcol==false)  echo '<td class="namenr" style="text-align:center;border-right-width:3px">'; // not last column numbers
	else echo '</td><td class="namenr" style="text-align:center">'; // no thick border
	
	if(isset($freq_last_names[$nr])) echo $freq_count_last_names[$nr];
	else echo '-';
	echo '</td>';
}

// *** Get names from database ***
$number_high=0;

// Mons, van or: van Mons
if ($user['group_kindindex']=="j"){
	// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
	$personqry="SELECT pers_prefix, pers_lastname, count(pers_lastname) as count_last_names
		FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND CONCAT(pers_prefix,pers_lastname) LIKE '".$last_name."%'
		GROUP BY pers_prefix, pers_lastname ORDER BY CONCAT(pers_prefix, pers_lastname)";

	// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
	$count_qry="SELECT pers_lastname, pers_prefix
		FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND CONCAT(pers_prefix,pers_lastname) LIKE '".$last_name."%'
		GROUP BY pers_prefix, pers_lastname";


	if ($last_name=='all'){
		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$personqry="SELECT pers_prefix, pers_lastname, count(pers_lastname) as count_last_names
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			GROUP BY pers_prefix, pers_lastname ORDER BY CONCAT(pers_prefix, pers_lastname)";

		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$count_qry="SELECT pers_prefix, pers_lastname
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			GROUP BY pers_prefix, pers_lastname";
	}
}
else{
	// *** Select alphabet first_character ***
	// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
	$personqry="SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
		FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_lastname LIKE '".$last_name."%'
		GROUP BY pers_lastname, pers_prefix";

	// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
	$count_qry="SELECT pers_lastname, pers_prefix
		FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_lastname LIKE '".$last_name."%'
		GROUP BY pers_lastname, pers_prefix";

	if ($last_name=='all'){
		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$personqry="SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			GROUP BY pers_lastname, pers_prefix";

		// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
		$count_qry="SELECT pers_lastname, pers_prefix
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			GROUP BY pers_lastname, pers_prefix";
	}
}

// *** Add limit to query (results per page) ***
if ($maxnames!='ALL') $personqry.=" LIMIT ".$item.",".$maxnames;

$person=$dbh->query($personqry);
while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){ 
	if ($personDb->pers_lastname=='') $personDb->pers_lastname='...';
	$freq_last_names[]=$personDb->pers_lastname;
	$freq_pers_prefix[]=$personDb->pers_prefix;
	$freq_count_last_names[]=$personDb->count_last_names;
	if ($personDb->count_last_names > $number_high){
		$number_high=$personDb->count_last_names;
	}
}
@$row = ceil(count($freq_last_names)/$maxcols);

// *** Total number of persons for multiple pages ***
//if ($count_qry){
	// *** Use MySQL COUNT command to calculate nr. of persons in simple queries (faster than php num_rows and in simple queries faster than SQL_CAL_FOUND_ROWS) ***
	//@$resultDb = $result->fetch(PDO::FETCH_OBJ);
	//$count_persons=@$resultDb->teller;
	$result= $dbh->query($count_qry);
	$count_persons=$result->rowCount();
//}
//else{
//		// *** USE SQL_CALC_FOUND_ROWS for complex queries (faster than mysql count) ***
//		$result = $dbh->query("SELECT FOUND_ROWS() AS 'found_rows'");
//		$rows = $result->fetch();
//		$count_persons = $rows['found_rows'];
//}


//echo '<div class="standard_header">'.__('Frequency of Surnames').'</div>';

// *** Show options line ***
echo '<div style="text-align:center">';

	//echo ' <form method="POST" action="'.CMS_ROOTPATH.'list_names.php?menu_tab=stats_surnames&amp;tree_id='.$tree_id.'&amp;last_name='.$last_name.'" style="display:inline;" id="frqnames">';
	if ($humo_option["url_rewrite"]=="j"){
		// *** $uri_path made in header.php ***
		$url=$uri_path.'list_names/'.$tree_id.'/'.$last_name;
	}
	else{
		//$url=CMS_ROOTPATH.'list_names.php?menu_tab=stats_surnames&amp;tree_id='.$tree_id.'&amp;last_name='.$last_name;
		$url=CMS_ROOTPATH.'list_names.php?tree_id='.$tree_id.'&amp;last_name='.$last_name;
	}
	echo ' <form method="POST" action="'.$url.'" style="display:inline;" id="frqnames">';
		echo __('Number of displayed surnames');
		echo ': <select size=1 name="freqsurnames" onChange="this.form.submit();" style="width: 50px; height:20px;">';
		$selected=''; if($maxnames==25) $selected=" selected "; echo '<option value="25" '.$selected.'>25</option>';
		$selected=''; if($maxnames==51) $selected=" selected "; echo '<option value="51" '.$selected.'>50</option>'; // 51 so no empty last field (if more names than this)
		$selected=''; if($maxnames==75) $selected=" selected "; echo '<option value="75" '.$selected.'>75</option>';
		$selected=''; if($maxnames==100) $selected=" selected "; echo '<option value="100" '.$selected.'>100</option>';
		$selected=''; if($maxnames==201) $selected=" selected "; echo '<option value="201" '.$selected.'>200</option>'; // 201 so no empty last field (if more names than this)
		$selected=''; if($maxnames==300) $selected=" selected "; echo '<option value="300" '.$selected.'>300</option>';
		$selected=''; if($maxnames=='ALL') $selected=" selected "; echo '<option value="ALL" '.$selected.'">'.__('All').'</option>';
		echo '</select>';

		echo '&nbsp;&nbsp;&nbsp;&nbsp;'.__('Number of columns');
		echo ': <select size=1 name="maxcols" onChange="this.form.submit();" style="width: 50px; height:20px;">';
		for($i=1;$i<7;$i++) {
			$selected=''; if($maxcols==$i) $selected=" selected "; echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
		}
		echo '</select>';
	echo '</form>';


	//*** Show number of persons and pages *********************

	// *** Check for search results ***
	if (@$person->rowCount()==0) {
		$line_pages='';
		//echo '<br><div class="center">'.__('No names found.').'</div>';
	}
	else{
		$line_pages=__('Page');

		// "<="
		if ($start>1){
			$start2=$start-20;
			$calculated=($start-2)*$nr_persons;
			$line_pages.= ' <a href="'.$uri_path_string.
			"&amp;start=".$start2.
			"&amp;item=".$calculated.
			"&last_name=".$last_name.
			'">&lt;= </a>';
		}
		if ($start<=0){$start=1;}

		// 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
		for ($i=$start; $i<=$start+19; $i++) {
			$calculated=($i-1)*$nr_persons;
			if ($calculated<$count_persons){
				if ($item==$calculated){
					$line_pages.=  " <b>$i</b>";
				}
				else {
					$line_pages.= ' <a href="'.$uri_path_string.
					"&amp;start=".$start.
					"&amp;item=".$calculated.
					"&last_name=".$last_name.
					'"> '.$i.'</a>';
				}
			}
		}

		// "=>"
		$calculated=($i-1)*$nr_persons;
		if ($calculated<$count_persons){
			$line_pages.= ' <a href="'.$uri_path_string.
			"&amp;start=".$i.
			"&amp;item=".$calculated.
			"&last_name=".$last_name.
			'"> =&gt;</a>';
		}
	}
	echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$line_pages;

echo '</div>';

if(CMS_SPECIFIC=="Joomla") {
	$table2_width="100%";
}
else {
	//$table2_width="900";
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
echo '</table>';

// *** Show number of names with gray background bar ***
echo '
<script>
var tbl = document.getElementsByClassName("nametbl")[0];
var rws = tbl.rows; var baseperc = '.$number_high.';
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

include_once(CMS_ROOTPATH."footer.php");
?>