<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

echo '<p class="fonts">';

	//*** Find first first_character of last name ***

	print '<div style="text-align:center">';
	$person_qry="SELECT UPPER(substring(pers_lastname,1,1)) as first_character
		FROM humo_persons WHERE pers_tree_id='".$tree_id."' GROUP BY first_character";

	// *** Search pers_prefix for names like: "van Mons" ***
	if ($user['group_kindindex']=="j"){
		$person_qry="SELECT UPPER(substring(CONCAT(pers_prefix,pers_lastname),1,1)) as first_character
			FROM humo_persons WHERE pers_tree_id='".$tree_id."' GROUP BY first_character";
	}
	@$person_result= $dbh->query($person_qry);
	while(@$personDb=$person_result->fetch(PDO::FETCH_OBJ)) {
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list_names&amp;database='.
			$_SESSION['tree_prefix'].'&amp;last_name='.$personDb->first_character;
		}
		elseif ($humo_option["url_rewrite"]=="j"){
			// *** url_rewrite ***
			// *** $uri_path is made header.php ***
			$path_tmp=$uri_path.'list_names/'.$_SESSION['tree_prefix'].'/'.$personDb->first_character.'/';
		}
		else{
			$path_tmp=CMS_ROOTPATH.'list_names.php?database='.$_SESSION['tree_prefix'].'&amp;last_name='.$personDb->first_character;
		}
		print ' <a href="'.$path_tmp.'">'.$personDb->first_character.'</a>';
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
if (isset($urlpart[1])){  
	$last_name=urldecode(safe_text($urlpart[1]));   // without urldecode skandinavian letters don't work!
}
if (isset($_GET['last_name'])){
	$last_name=safe_text($_GET['last_name']);
}

/*
echo '<div class="index_lastname">';

// Mons, van or: van Mons
if ($user['group_kindindex']=="j"){
	$person_result=$dbh->query("SELECT pers_lastname, pers_prefix,
		CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_lastnames
		FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND CONCAT(pers_prefix,pers_lastname) LIKE '".$last_name."%'
		GROUP BY long_name");

	if ($last_name=='all'){
		$person_result=$dbh->query("SELECT pers_lastname, pers_prefix,
			CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_lastnames
			FROM humo_persons WHERE pers_tree_id='".$tree_id."' GROUP BY long_name");
	}

	while(@$personDb=$person_result->fetch(PDO::FETCH_OBJ)) {
		// *** No & character in a link, replace to: | !!!
		$long_name=str_replace("_", " ", $personDb->long_name);
		if ($long_name){
			$link=str_replace("_", " ", $personDb->pers_prefix).$personDb->pers_lastname;
			$link=str_replace("&", "|", $link);
			$person_name=$long_name;
		}
		else{
			$link=__('...');
			$person_name=__('...');
		}

		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.
			$_SESSION['tree_prefix'].'&amp;pers_last_name='.$link.'&amp;part_lastname=equals';
		}
		else {
			$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'].'&amp;pers_lastname='.$link.'&amp;part_lastname=equals';
		}

		echo '<a href="'.$path_tmp.'">'.$person_name.'</a> ('.$personDb->count_lastnames.')'.$dirmark2.' / '.$dirmark2;
	}
}
else{
	// *** Select alphabet first_character ***
		$person_result=$dbh->query("SELECT pers_lastname, pers_prefix,
		CONCAT(pers_lastname,pers_prefix) as long_name, count(pers_lastname) as count_lastnames
		FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_lastname LIKE '".$last_name."%'
		GROUP BY long_name");

	if ($last_name=='all'){ 
		$person_result=$dbh->query("SELECT pers_lastname, pers_prefix,
		CONCAT(pers_lastname,pers_prefix) as long_name, count(pers_lastname) as count_lastnames
		FROM humo_persons WHERE pers_tree_id='".$tree_id."'
		GROUP BY long_name");
	}

	while(@$personDb=$person_result->fetch(PDO::FETCH_OBJ)) {
		// *** Do not use a & character in a GET, rename to: | !!! ***
		$pers_lastname=$personDb->pers_lastname;
		if ($personDb->pers_prefix){ $pers_lastname.=', '.$personDb->pers_prefix; }
		$pers_lastname=str_replace("_", " ", $pers_lastname);
		// *** Backwards compatibly only! Not in use in newer versions ***
		if ($pers_lastname){
			$link=$personDb->pers_lastname;
			$link=str_replace("&", "|", $link);
			$pers_prefix='';
			if ($personDb->pers_prefix){
				$pers_prefix=$personDb->pers_prefix;
			}
			else{
				$pers_prefix='EMPTY';
			}
			$person_name=$pers_lastname;

		}
		else{
			$link=__('...');
			$pers_prefix=''; //if ($personDb->pers_prefix){ $pers_prefix='&amp;pers_prefix='.$personDb->pers_prefix; }
			$person_name=__('...');
		}

		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.
			$_SESSION['tree_prefix'].'&amp;pers_lastname='.$link;
		}
		else {
			$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'].'&amp;pers_lastname='.$link;
		}

		if ($pers_prefix){ $path_tmp .= '&amp;pers_prefix='.$pers_prefix; }
		$path_tmp .= '&amp;part_lastname=equals';
		echo '<a href="'.$path_tmp.'">'.$person_name.'</a> ('.$personDb->count_lastnames.')'.$dirmark2.' / '.$dirmark2;

	}
}
echo '<br><br><br>'; // some joomla templates have "back to top link" before end of page. make room for that.
echo '</div>';
*/

// MAIN SETTINGS
$maxcols = 5; // number of name&nr colums in table. For example 3 means 3x name col + nr col
if(isset($_POST['maxcols'])) {
	$maxcols = $_POST['maxcols'];
}

function tablerow($nr,$lastcol=false) {    
	// displays one set of name & nr column items in the row
	// $nr is the array number of the name set created in function last_names
	// if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
	global $user, $freq_last_names, $freq_pers_prefix, $freq_count_last_names;
	//if (CMS_SPECIFIC=='Joomla'){
	//	$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.$_SESSION['tree_prefix'];
	//}
	//else{
		$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'];
	//}
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
	else echo '-';
	echo '</td>';
	
	if($lastcol==false)  echo '<td class="namenr" style="text-align:center;border-right-width:3px">'; // not last column numbers
	else echo '</td><td class="namenr" style="text-align:center">'; // no thick border
	
	if(isset($freq_last_names[$nr])) echo $freq_count_last_names[$nr];
	else echo '-';
	echo '</td>';
}

function last_names($max) {
	global $dbh, $tree_id, $language, $user, $humo_option, $uri_path, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $maxcols;
	global $last_name;
	//$personqry="SELECT pers_lastname, pers_prefix,
	//	CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
	//	FROM humo_persons
	//	WHERE pers_tree_id='".$tree_id."' AND pers_lastname NOT LIKE ''
	//	GROUP BY long_name ORDER BY count_last_names DESC LIMIT 0,".$max;

	// Mons, van or: van Mons
	if ($user['group_kindindex']=="j"){
		$personqry="SELECT pers_lastname, pers_prefix,
			CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
			FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND CONCAT(pers_prefix,pers_lastname) LIKE '".$last_name."%'
			GROUP BY long_name LIMIT 0,".$max;

		if ($last_name=='all'){
			$personqry="SELECT pers_lastname, pers_prefix,
				CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
				FROM humo_persons WHERE pers_tree_id='".$tree_id."' GROUP BY long_name LIMIT 0,".$max;
		}
	}
	else{
		// *** Select alphabet first_character ***
			$personqry="SELECT pers_lastname, pers_prefix,
			CONCAT(pers_lastname,pers_prefix) as long_name, count(pers_lastname) as count_last_names
			FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_lastname LIKE '".$last_name."%'
			GROUP BY long_name LIMIT 0,".$max;

		if ($last_name=='all'){
			$personqry="SELECT pers_lastname, pers_prefix,
			CONCAT(pers_lastname,pers_prefix) as long_name, count(pers_lastname) as count_last_names
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			GROUP BY long_name LIMIT 0,".$max;
		}
	}

	$person=$dbh->query($personqry);
	while (@$personDb=$person->fetch(PDO::FETCH_OBJ)){ 
		if ($personDb->pers_lastname=='') $personDb->pers_lastname='...';
		$freq_last_names[]=$personDb->pers_lastname;
		$freq_pers_prefix[]=$personDb->pers_prefix;
		$freq_count_last_names[]=$personDb->count_last_names;
	}
	//$row = round(count($freq_last_names)/$maxcols);
	$row = ceil(count($freq_last_names)/$maxcols);

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
$maxnames = 201;

if(isset($_POST['freqsurnames'])) { $maxnames = $_POST['freqsurnames']; }
//echo ' <form method="POST" action="'.CMS_ROOTPATH.'statistics.php?menu_tab=stats_surnames&amp;tree_id='.$tree_id.'" style="display:inline;" id="frqnames">';
echo ' <form method="POST" action="'.CMS_ROOTPATH.'list_names.php?menu_tab=stats_surnames&amp;tree_id='.$tree_id.'&amp;last_name='.$last_name.'" style="display:inline;" id="frqnames">';

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

$baseperc = last_names($maxnames);   // displays the table and sets the $baseperc (= the name with highest frequency that will be 100%)
echo '</table>';
/*
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
*/

include_once(CMS_ROOTPATH."footer.php");
?>