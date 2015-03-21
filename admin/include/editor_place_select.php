<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

//echo '<html><head><title>'.__('Select place').'</title></head><body>';
echo '<h1 align=center>'.__('Select place').'</h1>';

$place_item='pers_birth_place'; $form='form1';
if (isset($_GET['place_item'])){
	// *** Places by person ***
	if ($_GET['place_item']=='baptise'){ $place_item='pers_bapt_place'; $form='form1'; }
	if ($_GET['place_item']=='death'){ $place_item='pers_death_place'; $form='form1'; }
	if ($_GET['place_item']=='buried'){ $place_item='pers_buried_place'; $form='form1'; }
	if ($_GET['place_item']=='place'){ $place_item='address_place_'.$_GET['address_place']; $form='form1'; }

	// *** Places by family ***
	if ($_GET['place_item']=='relation'){ $place_item='fam_relation_place'; $form='form2'; }
	if ($_GET['place_item']=='marr_notice'){ $place_item='fam_marr_notice_place'; $form='form2'; }
	if ($_GET['place_item']=='marr'){ $place_item='fam_marr_place'; $form='form2'; }
	if ($_GET['place_item']=='fam_marr_church_notice'){ $place_item='fam_marr_church_notice_place'; $form='form2'; }
	if ($_GET['place_item']=='fam_marr_church'){ $place_item='fam_marr_church_place'; $form='form2'; }
	if ($_GET['place_item']=='fam_div'){ $place_item='fam_div_place'; $form='form2'; }
}
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

$tree_id=$_SESSION['admin_tree_id'];
$query = "(SELECT pers_birth_place as place_order
	FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND pers_birth_place LIKE '_%' GROUP BY place_order)";

$query.= " UNION (SELECT pers_bapt_place as place_order
	FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND pers_bapt_place LIKE '_%' GROUP BY place_order)";

//$query.= " UNION (SELECT pers_place_index as place_order
//	FROM humo_persons
//	WHERE pers_tree_id='".$tree_id."' AND pers_place_index LIKE '_%' GROUP BY place_order)";

$query.= " UNION (SELECT pers_death_place as place_order
	FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND pers_death_place LIKE '_%' GROUP BY place_order)";

$query.= " UNION (SELECT pers_buried_place as place_order
	FROM humo_persons
	WHERE pers_tree_id='".$tree_id."' AND pers_buried_place LIKE '_%' GROUP BY place_order)";

$query.=' ORDER BY place_order';
$result= $dbh->query($query);

while (@$resultDb = $result->fetch(PDO::FETCH_OBJ)){
	//echo '<a href="" onClick=\'return select_item("'.$resultDb->place_order.'")\'>'.$resultDb->place_order.'</a><br>';
	// *** Replace ' by &prime; otherwise a place including a ' character can't be selected ***
	echo '<a href="" onClick=\'return select_item("'.str_replace("'","&prime;",$resultDb->place_order).'")\'>'.$resultDb->place_order.'</a><br>';
}
?>