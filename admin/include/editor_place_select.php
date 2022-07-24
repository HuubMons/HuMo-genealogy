<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Select place').'</h1>';

$place_item=''; $form='';
if (isset($_GET['form'])){
	$check_array = array("1", "2", "5", "6");
	if (in_array($_GET['form'], $check_array)) $form='form'.$_GET['form'];

	$check_array = array("pers_birth_place", "pers_bapt_place", "pers_death_place", "pers_buried_place",
		"fam_relation_place", "fam_marr_notice_place", "fam_marr_place", "fam_marr_church_notice_place", "fam_marr_church_place", "fam_div_place",
		"address_place",
		"event_place");
	if (in_array($_GET['place_item'], $check_array)) $place_item=$_GET['place_item'];

	// *** Multiple places/ addresses: add address_id ***
	if (isset($_GET['address_id']) AND is_numeric($_GET['address_id'])){
		$place_item.='_'.$_GET['address_id'];
	}

	// *** Multiple events: add event_id ***
	if (isset($_GET['event_id']) AND is_numeric($_GET['event_id'])){
		$place_item.=$_GET['event_id'];
	}
}

// *** January 2022: no longer in use? ***
//if(strpos($_GET['place_item'],"add_fam")!== false ) {
//	$form = "form_entire";
//	$place_item = $_GET['place_item'];
//}

echo'
	<script type="text/javascript">
	function select_item(item){
		/* EXAMPLE: window.opener.document.form1.pers_birth_place.value=item; */
		window.opener.document.'.$form.'.'.$place_item.'.value=item;
		top.close();
		return false;
	}
	</script>
';

$query = "(SELECT pers_birth_place as place_order FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND pers_birth_place LIKE '_%' GROUP BY place_order)";

$query.= " UNION (SELECT pers_bapt_place as place_order FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND pers_bapt_place LIKE '_%' GROUP BY place_order)";

//$query.= " UNION (SELECT pers_place_index as place_order FROM humo_persons
//	WHERE pers_tree_id='".$tree_id."' AND pers_place_index LIKE '_%' GROUP BY place_order)";

$query.= " UNION (SELECT event_place as place_order FROM humo_events
	WHERE event_tree_id='".$tree_id."' AND event_place LIKE '_%' GROUP BY place_order)";

$query.= " UNION (SELECT pers_death_place as place_order FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND pers_death_place LIKE '_%' GROUP BY place_order)";

$query.= " UNION (SELECT pers_buried_place as place_order FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND pers_buried_place LIKE '_%' GROUP BY place_order)";

$query.=' ORDER BY place_order';
$result= $dbh->query($query);

while (@$resultDb = $result->fetch(PDO::FETCH_OBJ)){
	//echo '<a href="" onClick=\'return select_item("'.$resultDb->place_order.'")\'>'.$resultDb->place_order.'</a><br>';
	// *** Replace ' by &prime; otherwise a place including a ' character can't be selected ***
	echo '<a href="" onClick=\'return select_item("'.str_replace("'","&prime;",$resultDb->place_order).'")\'>'.$resultDb->place_order.'</a><br>';
}
?>