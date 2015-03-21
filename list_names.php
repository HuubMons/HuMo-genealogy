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

	//echo '</p>';
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

include_once(CMS_ROOTPATH."footer.php");
?>