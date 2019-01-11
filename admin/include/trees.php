<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

@set_time_limit(4000);

echo '<h1 align=center>'.__('Family tree administration').'</h1>';

// THIS FILE IS MADE BY Huub Mons
// IT IS PART OF THE HuMo-gen program.
// jan 2014: updated family tree texts.

// have to be declared global here for use in trees_cls.php
global $phpself, $phpself2, $joomlastring;

if(CMS_SPECIFIC=="Joomla") {
	$phpself="index.php?option=com_humo-gen&amp;task=admin"; // used also in trees_cls.php
	$joomlastring="option=com_humo-gen&amp;task=admin&amp;"; // used also in trees_cls.php
	$phpself2= "index.php?option=com_humo-gen&amp;task=admin&amp;"; // used only in trees_cls.php
}
else {
	$phpself ='index.php';
	$phpself2='index.php?';
	$joomlastring='';
}

// *** Family tree admin ***
if (isset($_POST['change_tree_data'])){
	$tree_pict_path=$_POST['tree_pict_path'];
	if (substr($_POST['tree_pict_path'],0,1)=='|'){
		if (isset($_POST['default_path']) AND $_POST['default_path']=='no') $tree_pict_path=substr($tree_pict_path,1);
	}
	else{
		if (isset($_POST['default_path']) AND $_POST['default_path']=='yes') $tree_pict_path='|'.$tree_pict_path;
	}

	$sql="UPDATE humo_trees SET
	tree_email='".safe_text_db($_POST['tree_email'])."',
	tree_owner='".safe_text_db($_POST['tree_owner'])."',
	tree_pict_path='".safe_text_db($tree_pict_path)."',
	tree_privacy='".safe_text_db($_POST['tree_privacy'])."'
	WHERE tree_id=".safe_text_db($_POST['tree_id']);
	$result=$dbh->query($sql);
}

if (isset($_POST['change_tree_text'])){
	$sql="UPDATE humo_tree_texts SET
	treetext_tree_id='".safe_text_db($_POST['tree_id'])."',
	treetext_language='".safe_text_db($_POST['language_tree'])."',
	treetext_name='".safe_text_db($_POST['treetext_name'])."',
	treetext_mainmenu_text='".safe_text_db($_POST['treetext_mainmenu_text'])."',
	treetext_mainmenu_source='".safe_text_db($_POST['treetext_mainmenu_source'])."',
	treetext_family_top='".safe_text_db($_POST['treetext_family_top'])."',
	treetext_family_footer='".safe_text_db($_POST['treetext_family_footer'])."'
	WHERE treetext_id=".safe_text_db($_POST['treetext_id']);
	$result=$dbh->query($sql);
}

if (isset($_POST['add_tree_data'])){
	$sql="INSERT INTO humo_trees SET
	tree_order='".safe_text_db($_POST['tree_order'])."',
	tree_prefix='".safe_text_db($_POST['tree_prefix'])."',
	tree_persons='0',
	tree_families='0',
	tree_email='',
	tree_privacy='',
	tree_pict_path='|../pictures/'
	";
	$result=$dbh->query($sql);

	// *** Immediately add new tables in tree ***
	$_SESSION['tree_prefix']=safe_text_db($_POST['tree_prefix']);
}

if (isset($_POST['add_tree_data_empty'])){
	$sql="INSERT INTO humo_trees SET
	tree_order='".safe_text_db($_POST['tree_order'])."',
	tree_prefix='EMPTY',
	tree_persons='EMPTY',
	tree_families='EMPTY',
	tree_email='EMPTY',
	tree_privacy='EMPTY',
	tree_pict_path='EMPTY'
	";
	$result=$dbh->query($sql);
}

if (isset($_POST['add_tree_text'])){
	$sql="INSERT INTO humo_tree_texts SET
	treetext_tree_id='".safe_text_db($_POST['tree_id'])."',
	treetext_language='".safe_text_db($_POST['language_tree'])."',
	treetext_name='".safe_text_db($_POST['treetext_name'])."',
	treetext_mainmenu_text='".safe_text_db($_POST['treetext_mainmenu_text'])."',
	treetext_mainmenu_source='".safe_text_db($_POST['treetext_mainmenu_source'])."',
	treetext_family_top='".safe_text_db($_POST['treetext_family_top'])."',
	treetext_family_footer='".safe_text_db($_POST['treetext_family_footer'])."'";
	$result=$dbh->query($sql);
}

// *** Change collation of tree ***
if (isset($_POST['tree_collation'])){
	$tree_collation=safe_text_db($_POST['tree_collation']);
	$dbh->query("ALTER TABLE humo_persons CHANGE `pers_lastname` `pers_lastname` VARCHAR(50) COLLATE ".$tree_collation.";");
	$dbh->query("ALTER TABLE humo_persons CHANGE `pers_firstname` `pers_firstname` VARCHAR(50) COLLATE ".$tree_collation.";");
	$dbh->query("ALTER TABLE humo_persons CHANGE `pers_prefix` `pers_prefix` VARCHAR(20) COLLATE ".$tree_collation.";");
	$dbh->query("ALTER TABLE humo_persons CHANGE `pers_callname` `pers_callname` VARCHAR(20) COLLATE ".$tree_collation.";");
	$dbh->query("ALTER TABLE humo_events CHANGE `event_event` `event_event` TEXT COLLATE ".$tree_collation.";");
}

if (isset($_GET['remove_tree']) AND is_numeric($_GET['remove_tree']) ){
	echo '<div class="confirm">';
	echo '<b>'.__('Selected:').' '.$_GET['treetext_name'].'</b> ';
	echo __('Are you sure you want to remove this tree <b>AND all its statistics</b>?');
	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="tree_id" value="'.$_GET['remove_tree'].'">';
	echo ' <input type="Submit" name="remove_tree2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	echo '</form>';
	echo '</div>';
}
if (isset($_POST['remove_tree2']) AND is_numeric($_POST['tree_id']) ){
	$removeqry='SELECT * FROM humo_trees WHERE tree_id="'.safe_text_db($_POST['tree_id']).'"';
	@$removesql = $dbh->query($removeqry);
	@$removeDb=$removesql->fetch(PDO::FETCH_OBJ);
	$remove=$removeDb->tree_prefix;

	// *** Re-order family trees ***
	$repair_order=$removeDb->tree_order;
	$item=$dbh->query("SELECT * FROM humo_trees WHERE tree_order>".$repair_order);
	while($itemDb=$item->fetch(PDO::FETCH_OBJ)){
		$sql="UPDATE humo_trees SET tree_order='".($itemDb->tree_order-1)."' WHERE tree_id=".$itemDb->tree_id;
		$result=$dbh->query($sql);
	}

	$sql="DELETE FROM humo_trees WHERE tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove items from table family_tree_text ***
	$sql="DELETE FROM humo_tree_texts WHERE treetext_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove persons ***
	$sql="DELETE FROM humo_persons WHERE pers_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove families ***
	$sql="DELETE FROM humo_families WHERE fam_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove sources ***
	$sql="DELETE FROM humo_sources WHERE source_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove texts ***
	$sql="DELETE FROM humo_texts WHERE text_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove connections ***
	$sql="DELETE FROM humo_connections WHERE connect_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove addresses ***
	$sql="DELETE FROM humo_addresses WHERE address_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove events ***
	$sql="DELETE FROM humo_events WHERE event_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove statistics ***
	$sql="DELETE FROM humo_stat_date WHERE stat_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove unprocessed tags ***
	$sql="DELETE FROM humo_unprocessed_tags WHERE tag_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove admin favourites ***
	$sql="DELETE FROM humo_settings WHERE setting_variable='admin_favourite' AND setting_tree_id='".safe_text_db($_POST['tree_id'])."'";
	@$result=$dbh->query($sql);

	// *** Remove adjusted glider settings ***
	$sql="DELETE FROM humo_settings WHERE setting_variable='gslider_".$remove."'";
	@$result=$dbh->query($sql);

	// *** Remove geo_tree settings for this tree ***
	$sql="UPDATE humo_settings SET setting_value = REPLACE(setting_value, CONCAT('@',".safe_text_db($_POST['tree_id']).",';'), '')  WHERE setting_variable='geo_trees'";
	@$result=$dbh->query($sql);

	// *** Remove tree_prefix of this tree from location table (humo2_birth, humo2_death, humo2_bapt, humo2_buried)  ***
	$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
	if($temp->rowCount()) {
		$loc_qry = "SELECT * FROM humo_location";
		$loc_result = $dbh->query($loc_qry);
		while($loc_resultDb = $loc_result->fetch(PDO::FETCH_OBJ)) { 
			if(strpos($loc_resultDb->location_status,$remove)!==false) {   // only do this if the prefix appears
				$stat_qry = "UPDATE humo_location SET location_status = REPLACE(REPLACE(REPLACE(REPLACE(location_status, CONCAT('".$remove."','birth'),''),CONCAT('".$remove."','death'),''),CONCAT('".$remove."','bapt'),''),CONCAT('".$remove."','buried'),'')  WHERE location_id = '".$loc_resultDb->location_id."'";
				$stat_result=$dbh->query($stat_qry);  
			}
		}
	}

	unset ($_POST['tree_id']);

	// *** Next lines to reset session items for editor pages ***
	if (isset($_SESSION['admin_tree_prefix'])){ unset($_SESSION['admin_tree_prefix']); }
	unset($_SESSION['admin_pers_gedcomnumber']);
	unset($_SESSION['admin_fam_gedcomnumber']);
}

if (isset($_GET['up']) AND is_numeric($_GET['tree_order']) AND is_numeric($_GET['id']) ){
	// *** Search previous family tree ***
	$item=$dbh->query("SELECT * FROM humo_trees WHERE tree_order=".($_GET['tree_order']-1));
	$itemDb=$item->fetch(PDO::FETCH_OBJ);
	// *** Raise previous family trees ***
	$sql="UPDATE humo_trees SET tree_order='".safe_text_db($_GET['tree_order'])."' WHERE tree_id=$itemDb->tree_id";
	$result=$dbh->query($sql);
	// *** Lower tree order ***
	$sql="UPDATE humo_trees SET tree_order='".safe_text_db($_GET['tree_order']-1)."' WHERE tree_id=".safe_text_db($_GET['id']);
	$result=$dbh->query($sql);
}
if (isset($_GET['down']) AND is_numeric($_GET['tree_order']) AND is_numeric($_GET['id']) ){
	// *** Search next family tree ***
	$item=$dbh->query("SELECT * FROM humo_trees WHERE tree_order=".($_GET['tree_order']+1));
	$itemDb=$item->fetch(PDO::FETCH_OBJ);
	// *** Lower previous family tree ***
	$sql="UPDATE humo_trees SET tree_order='".safe_text_db($_GET['tree_order'])."' WHERE tree_id=$itemDb->tree_id";
	$result=$dbh->query($sql);
	// *** Raise tree order ***
	$sql="UPDATE humo_trees SET tree_order='".safe_text_db($_GET['tree_order']+1)."' WHERE tree_id=".safe_text_db($_GET['id']);
	$result=$dbh->query($sql);
}

// ******************
// *** Start page ***
// ******************

$language_tree=$selected_language; // Default language
if (isset($_GET['language_tree'])){ $language_tree=$_GET['language_tree']; }
if (isset($_POST['language_tree'])){ $language_tree=$_POST['language_tree']; }

include_once ("trees_cls.php");
$tree_cls = New tree_cls;

// *** Selected family tree ***
if (isset($_POST['add_tree_data'])){
	// *** Select new family tree if a new family tree is added ***
	$data2sql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order DESC LIMIT 0,1");
}
else{
	$data2sql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order LIMIT 0,1");
}
//$data2Db = $data2sql->fetchObject();
$data2Db=$data2sql->fetch(PDO::FETCH_OBJ);
if ($data2Db){
	$tree_id=$data2Db->tree_id;
}
if (isset($_POST['tree_id']) AND is_numeric($_POST['tree_id']) ){ $tree_id=$_POST['tree_id']; }
if (isset($_GET['tree_id']) AND is_numeric($_GET['tree_id']) ){ $tree_id=$_GET['tree_id']; }

// ******************************************
// *** Show texts of selected family tree ***
// ******************************************

$data2sql = $dbh->query("SELECT * FROM humo_tree_texts WHERE
	treetext_tree_id='".$tree_id."' AND treetext_language='".$language_tree."'");
$data2Db=$data2sql->fetch(PDO::FETCH_OBJ);
if ($data2Db){
	$treetext_id=$data2Db->treetext_id;
	$treetext_name=$data2Db->treetext_name;
	$treetext_mainmenu_text=$data2Db->treetext_mainmenu_text;
	$treetext_mainmenu_source=$data2Db->treetext_mainmenu_source;
	$treetext_family_top=$data2Db->treetext_family_top;
	$treetext_family_footer=$data2Db->treetext_family_footer;
}
else{
	$treetext_name=__('NO NAME');
	$treetext_mainmenu_text='';
	$treetext_mainmenu_source='';
	//$treetext_family_top='Family page';
	$treetext_family_top='';
	$treetext_family_footer='';
}

$menu_admin='tree_main';
if (isset($_POST['menu_admin'])) $menu_admin=$_POST['menu_admin'];
if (isset($_GET['menu_admin'])) $menu_admin=$_GET['menu_admin'];

// *** Select family tree ***
$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
$tree_search_result = $dbh->query($tree_search_sql);
echo __('Family tree').': ';
echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<select size="1" name="tree_id" onChange="this.form.submit();">';
		while ($tree_searchDb=$tree_search_result->fetch(PDO::FETCH_OBJ)){
			$selected=''; if ($tree_searchDb->tree_id==$tree_id){ $selected=' SELECTED'; }
			$treetext=show_tree_text($tree_searchDb->tree_id, $selected_language);
			echo '<option value="'.$tree_searchDb->tree_id.'"'.$selected.'>'.@$treetext['name'].'</option>';
		}
	echo '</select>';
echo '</form>';

// *** Family trees administration menu ***
$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=".$tree_id);
$data2Db=$data2sql->fetch(PDO::FETCH_OBJ);

echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="">';
echo '<div class="pageHeading">';
	// <div class="pageHeadingText">Configuratie gegevens</div>
	// <div class="pageHeadingWidgets" aria-hidden="true" style="display: none;"></div>

	echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
		echo '<ul class="pageTabs">';
			//echo '<li class="pageTabItem"><div tabindex="0" class="pageTab pageTab-active">Details</div></li>';

			$select_item=''; if ($menu_admin=='tree_main'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;tree_id='.$tree_id.'">'.__('Family tree administration')."</a></div></li>";

			// *** Family tree data ***
			$select_item=''; if ($menu_admin=='tree_data'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_data'.'&amp;tree_id='.$tree_id.'">'.__('Family tree data')."</a></div></li>";

			// *** Read gedcom file ***
			$select_item=''; if ($menu_admin=='tree_gedcom'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_gedcom&amp;tree_id='.$tree_id.'&amp;tree_prefix='.$data2Db->tree_prefix.'&amp;step1=read_gedcom">'.__('Import Gedcom file')."</a></div></li>";

			// *** Family tree texts ***
			$select_item=''; if ($menu_admin=='tree_text'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_text&amp;tree_id='.$tree_id.'">'.__('Family tree texts (per language)')."</a></div></li>";

			// *** Family tree merge ***
			$select_item=''; if ($menu_admin=='tree_merge'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_merge&amp;tree_id='.$tree_id.'">'.__('Merge Data')."</a></div></li>";
		echo '</ul>';
	echo '</div>';
echo '</div>';
echo '</div>';


// *** Align content to the left ***
echo '<div style="float: left; background-color:white; height:500px; padding:10px;">';
	// *** Show main tree screen ***
	if (isset($menu_admin) AND $menu_admin=='tree_main'){
		$tree_cls->tree_main();
	}

	// *** Show main tree screen ***
	if (isset($menu_admin) AND $menu_admin=='tree_gedcom'){
		//$tree_cls->tree_main();
		if(CMS_SPECIFIC=="Joomla") {
			include_once (CMS_ROOTPATH_ADMIN."include/gedcom.php");
		}
		else {
			include_once ("gedcom.php");
		}
	}
	// ********************************************************************************
	// *** Show selected family tree                                                ***
	// ********************************************************************************

	$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=".$tree_id);
	$data2Db=$data2sql->fetch(PDO::FETCH_OBJ);

	// *** Show tree data ***
	if ($menu_admin=='tree_data'){
		$tree_cls->tree_data();
	}

	// *** Show tree text ***
	if ($menu_admin=='tree_text'){
		// ** Show family tree list and family tree name editor in 1 screen ***
		//$tree_cls->tree_main();
		$tree_cls->tree_text();
	}
	// *** Show merge screen ***
	if ($menu_admin=='tree_merge'){
		$tree_cls->tree_merge();
	}
echo '</div>'; // *** div voor menu ***

?>