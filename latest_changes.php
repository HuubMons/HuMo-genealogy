<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");
include_once(CMS_ROOTPATH."include/person_cls.php");

global $selected_language;

$person_cls = New person_cls;

// *** EXAMPLE of a UNION querie ***
//$qry = "(SELECT * FROM humo1_person ".$query.') ';
//$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
//$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
//$qry.= " ORDER BY pers_lastname, pers_firstname";

//$person_qry= "(SELECT *, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
//	FROM ".$tree_prefix_quoted."person WHERE pers_changed_date IS NOT NULL)";
$person_qry= "(SELECT *, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
	FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_changed_date IS NOT NULL)";

$person_qry.= " UNION (SELECT *, STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
	FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_changed_date IS NULL)";

$person_qry.= " ORDER BY changed_date DESC, changed_time DESC LIMIT 0,100";

$search_name='';
if (isset($_POST["search_name"])){
	$search_name=$_POST["search_name"];

	// *** EXAMPLE of a UNION querie ***
	//$qry = "(SELECT * FROM humo1_person ".$query.') ';
	//$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
	//$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
	//$qry.= " ORDER BY pers_lastname, pers_firstname";

	//$person_qry = "(SELECT * , STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
	//	FROM humo_persons WHERE pers_tree_id='".$tree_id."'
 	//	LEFT JOIN humo_events WHERE event_tree_id='".$tree_id."'
 	//		ON pers_gedcomnumber=event_person_id AND event_kind='name'
	//	WHERE (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$search_name%'
 	//		OR event_event LIKE '%$search_name%')
	//		AND pers_changed_date IS NOT NULL
	//		)";

	$person_qry = "(SELECT * , STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
		FROM humo_persons WHERE pers_tree_id='".$tree_id."'
 		LEFT JOIN humo_events
 			ON pers_gedcomnumber=event_person_id AND event_kind='name' AND event_tree_id='".$tree_id."' 
		WHERE (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$search_name%'
 			OR event_event LIKE '%$search_name%')
			AND pers_changed_date IS NOT NULL
			)";

	//$person_qry .= " UNION (SELECT * , STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
	//	FROM ".$tree_prefix_quoted."person
 	//	LEFT JOIN ".$tree_prefix_quoted."events
 	//		ON pers_gedcomnumber=event_person_id AND event_kind='name'
	//	WHERE (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$search_name%'
 	//		OR event_event LIKE '%$search_name%')
	//		AND pers_changed_date IS NULL)";

	$person_qry .= " UNION (SELECT * , STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
		FROM humo_persons WHERE pers_tree_id='".$tree_id."'
 		LEFT JOIN humo_events
 			ON pers_gedcomnumber=event_person_id AND event_kind='name' AND event_tree_id='".$tree_id."'
		WHERE (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$search_name%'
 			OR event_event LIKE '%$search_name%')
			AND pers_changed_date IS NULL)";

	$person_qry .= " ORDER BY changed_date DESC, changed_time DESC LIMIT 0,100";
}

$person_result = $dbh->query($person_qry);

echo '<h2 class="center">'.__('Recently changed and/or new persons').'</h2>';

// *** Search box ***
echo '<div style="text-align: center; margin-bottom: 16px">';
echo '<form action="'.CMS_ROOTPATH.'latest_changes.php" method="post">';
echo '<input type="text" name="search_name" id="part_of_name" value="'.$search_name.'">';
echo ' <input type="submit" value="'.__('Search').'">';
echo '</form>';
echo '</div>';

//echo '<div style="height: 400px; width: 90%; margin-left: 5%; overflow-y: scroll;">';
if($rtlmarker=="ltr") echo '<div style="height:400px; width:60%; margin-left: 20%; overflow-y: scroll;">';
else echo '<div style="height:400px; width:60%; margin-right: 20%; overflow-y: scroll;">';
echo '<table class="humo" width="99%">';
echo '<tr class=table_headline>';
echo '<th style="font-size: 90%; text-align: left">'.__('Changed/ Added').'</th>';
echo '<th style="font-size: 90%; text-align: left">'.__('When changed').'</th>';
echo '<th style="font-size: 90%; text-align: left">'.__('When added').'</th>';
echo '</tr>';

$rowcounter=0;
while (@$person=$person_result->fetch(PDO::FETCH_OBJ)){
	$rowcounter++;
	echo '<tr>';
	echo '<td style="font-size: 90%">';

	$person_cls->construct($person);
	echo $person_cls->person_popup_menu($person);

	if ($person->pers_sexe=="M"){
		echo '<img src="'.CMS_ROOTPATH.'images/man.gif" alt="man">';
	}
	elseif ($person->pers_sexe=="F"){
		echo '<img src="'.CMS_ROOTPATH.'images/woman.gif" alt="woman">';
	}
	else{
		echo '<img src="'.CMS_ROOTPATH.'images/unknown.gif" alt="unknown">';
	}

	echo '<a href="'.CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$person->pers_indexnr.'&amp;main_person='.$person->pers_gedcomnumber.'">';
	$name=$person_cls->person_name($person);
	echo $name["standard_name"];
	echo '</a>';

	echo '</td><td style="font-size: 90%">';
		echo '<span style="white-space: nowrap">'.strtolower($person->pers_changed_date).' - '.$person->pers_changed_time.'</span>';
	echo '</td><td style="font-size: 90%">';
		echo '<span style="white-space: nowrap">'.strtolower($person->pers_new_date).' - '.$person->pers_new_time.'</span></td>';

	echo '</tr>';
}
echo '</table>';

echo '</div>';

include_once(CMS_ROOTPATH."footer.php");
?>