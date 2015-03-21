<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<html><head><title>'.__('Select person').'</title></head><body>';
echo '<h1 align=center>'.__('Select person').'</h1>';

$place_item='connect_man'; $form='form2';
if ($_GET['person_item']=='woman'){ $place_item='connect_woman'; $form='form2'; }
if ($_GET['person_item']=='relation_add2'){ $place_item='relation_add2'; $form='form4'; }
$man_gedcomnumber=$_GET['person'];
$tree_prefix=$_GET['tree_prefix'];

$tree_id=$_SESSION['admin_tree_id'];

echo'
	<script type="text/javascript">
	function select_item(item){
		/* window.opener.document.form1.pers_birth_place.value=item; */
		window.opener.document.'.$form.'.'.$place_item.'.value=item;
		top.close();
		return false;
	}
	</script>
';

echo '<form method="POST" action="index.php?page=editor_person_select&person_item='.$_GET['person_item'].'&person='.$_GET['person'].'&tree_prefix='.$_GET['tree_prefix'].'" style="display : inline;">';
	$search_quicksearch_man=''; if (isset($_POST['search_quicksearch_man'])){ $search_quicksearch_man=$_POST['search_quicksearch_man']; }
	print ' <input class="fonts" type="text" name="search_quicksearch_man" placeholder="'.__('Name').'" value="'.$search_quicksearch_man.'" size="15">';

	$search_man_id=''; if (isset($_POST['search_man_id'])) $search_man_id=safe_text($_POST['search_man_id']);
	echo __('or ID:').' <input class="fonts" type="text" name="search_man_id" value="'.$search_man_id.'" size="5">';

	echo ' <input class="fonts" type="submit" name="submit" value="'.__('Search').'">';
echo '</form><br><br>';

if($search_quicksearch_man != '') {
	// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
	$search_quicksearch_man=str_replace(' ', '%', $search_quicksearch_man);
	// *** In case someone entered "Mons, Huub" using a comma ***
	$search_quicksearch_man = str_replace(',','',$search_quicksearch_man);
	//$person_qry= "SELECT *, CONCAT(pers_firstname,pers_prefix,pers_lastname) as concat_name
	$person_qry= "SELECT *
	FROM humo_persons
	WHERE pers_tree_id='".$tree_id."'
		AND (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname)
		LIKE '%".$search_quicksearch_man."%'
		OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname)
		LIKE '%".$search_quicksearch_man."%' 
		OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' '))
		LIKE '%".$search_quicksearch_man."%' 
		OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname)
		LIKE '%".$search_quicksearch_man."%')
		ORDER BY pers_lastname, pers_firstname";
}
elseif($search_man_id!='') {
	if(substr($search_man_id,0,1)!="i" AND substr($search_man_id,0,1)!="I") { $search_man_id = "I".$search_man_id; } //make entry "48" into "I48"
	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$search_man_id."'";
}
else{
	$person_qry= "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$man_gedcomnumber."'";
}
$person_result = $dbh->query($person_qry);

include ('include/editor_cls.php');
$editor_cls = New editor_cls;

while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
	echo '<a href="" onClick=\'return select_item("'.$person->pers_gedcomnumber.'")\'>'.$editor_cls->show_selected_person($person).'</a><br>';
}
?>