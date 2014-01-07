<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Family tree administration').'</h1>';

//THIS FILE IS MADE BY Huub Mons
//IT IS PART OF THE HuMo-gen program.

require (CMS_ROOTPATH.'include/database_name.php');

// have to be declared global here for use in trees_cls.php
global $phpself, $phpself2, $joomlastring;

if(CMS_SPECIFIC=="Joomla") {
	$phpself="index.php?option=com_humo-gen&amp;task=admin"; // used also in trees_cls.php
	$joomlastring="option=com_humo-gen&amp;task=admin&amp;"; // used also in trees_cls.php
	$phpself2= "index.php?option=com_humo-gen&amp;task=admin&amp;"; // used only in trees_cls.php
}
else {
	$phpself =$_SERVER['PHP_SELF'];
	$phpself2=$_SERVER['PHP_SELF']."?";
	$joomlastring='';
}

// *** Family tree admin ***
if (isset($_POST['change_tree_data'])){
	$sql="UPDATE humo_trees SET
	tree_email='".safe_text($_POST['tree_email'])."',
	tree_owner='".safe_text($_POST['tree_owner'])."',
	tree_pict_path='".safe_text($_POST['tree_pict_path'])."',
	tree_privacy='".safe_text($_POST['tree_privacy'])."'
	WHERE tree_id=".safe_text($_POST['family_tree_id']);
	$result=mysql_query($sql) or die(mysql_error());
}

if (isset($_POST['change_tree_text'])){
	$sql="UPDATE humo_tree_texts SET
	treetext_tree_id='".safe_text($_POST['family_tree_id'])."',
	treetext_language='".safe_text($_POST['language_tree'])."',
	treetext_name='".safe_text($_POST['treetext_name'])."',
	treetext_mainmenu_text='".safe_text($_POST['treetext_mainmenu_text'])."',
	treetext_mainmenu_source='".safe_text($_POST['treetext_mainmenu_source'])."',
	treetext_family_top='".safe_text($_POST['treetext_family_top'])."',
	treetext_family_footer='".safe_text($_POST['treetext_family_footer'])."'
	WHERE treetext_id=".safe_text($_POST['treetext_id']);
	$result=mysql_query($sql) or die(mysql_error());
}

if (isset($_POST['add_tree_data'])){
	$sql="INSERT INTO humo_trees SET
	tree_order='".safe_text($_POST['tree_order'])."',
	tree_prefix='".safe_text($_POST['tree_prefix'])."',
	tree_persons='0',
	tree_families='0',
	tree_email='',
	tree_privacy='',
	tree_pict_path='../plaatjes/'
	";
	$result=mysql_query($sql) or die(mysql_error());
}

if (isset($_POST['add_tree_data_empty'])){
	$sql="INSERT INTO humo_trees SET
	tree_order='".safe_text($_POST['tree_order'])."',
	tree_prefix='EMPTY',
	tree_persons='EMPTY',
	tree_families='EMPTY',
	tree_email='EMPTY',
	tree_privacy='EMPTY',
	tree_pict_path='EMPTY'
	";
	$result=mysql_query($sql) or die(mysql_error());
}

if (isset($_POST['add_tree_text'])){
	$sql="INSERT INTO humo_tree_texts SET
	treetext_tree_id='".safe_text($_POST['family_tree_id'])."',
	treetext_language='".safe_text($_POST['language_tree'])."',
	treetext_name='".safe_text($_POST['treetext_name'])."',
	treetext_mainmenu_text='".safe_text($_POST['treetext_mainmenu_text'])."',
	treetext_mainmenu_source='".safe_text($_POST['treetext_mainmenu_source'])."',
	treetext_family_top='".safe_text($_POST['treetext_family_top'])."',
	treetext_family_footer='".safe_text($_POST['treetext_family_footer'])."'";
	$result=mysql_query($sql) or die(mysql_error());
}

// *** Change collation of tree ***
if (isset($_POST['change_collation'])){
	$collation_prefix=safe_text($_POST['collation_prefix']);
	$tree_collation=safe_text($_POST['tree_collation']);

	mysql_query("ALTER TABLE ".$collation_prefix."person
		CHANGE `pers_lastname` `pers_lastname` VARCHAR(50) COLLATE ".$tree_collation.";");

	mysql_query("ALTER TABLE ".$collation_prefix."person
		CHANGE `pers_firstname` `pers_firstname` VARCHAR(50) COLLATE ".$tree_collation.";");

	mysql_query("ALTER TABLE ".$collation_prefix."person
		CHANGE `pers_prefix` `pers_prefix` VARCHAR(20) COLLATE ".$tree_collation.";");
}

if (isset($_GET['remove_tree'])){
	echo '<div class="confirm">';
	echo '<b>'.__('Selected:').' '.$_GET['treetext_name'].'</b> ';
	echo __('Are you sure you want to remove this tree <b>AND all its statistics</b>?');
	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="family_tree_id" value="'.$_GET['remove_tree'].'">';
	echo ' <input type="Submit" name="remove_tree2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	echo '</form>';
	echo '</div>';
}
if (isset($_POST['remove_tree2'])){
	$removeqry='SELECT * FROM humo_trees WHERE tree_id="'.safe_text($_POST['family_tree_id']).'"';
	@$removesql = mysql_query($removeqry,$db);
	@$removeDb=mysql_fetch_object($removesql);
	$remove=$removeDb->tree_prefix;

	// *** Re-order family trees ***
	$repair_order=$removeDb->tree_order;
	$item=mysql_query("SELECT * FROM humo_trees WHERE tree_order>".$repair_order,$db);
	while($itemDb=mysql_fetch_object($item)){
		$sql="UPDATE humo_trees SET tree_order='".($itemDb->tree_order-1)."' WHERE tree_id=".$itemDb->tree_id;
		$result=mysql_query($sql) or die(mysql_error());
	}

	$sql="DROP TABLE ".$remove."person"; @$result=mysql_query($sql);
	$sql="DROP TABLE ".$remove."family"; @$result=mysql_query($sql);
	$sql="DROP TABLE ".$remove."texts"; @$result=mysql_query($sql);
	$sql="DROP TABLE ".$remove."sources"; @$result=mysql_query($sql);
	$sql="DROP TABLE ".$remove."addresses"; @$result=mysql_query($sql);
	$sql="DROP TABLE ".$remove."events"; @$result=mysql_query($sql);
	$sql="DROP TABLE ".$remove."connections"; @$result=mysql_query($sql);
	$sql="DROP TABLE ".$remove."repositories"; @$result=mysql_query($sql);

	// *** Remove adjusted glider settings ***
	$sql="DELETE FROM humo_settings WHERE setting_variable='gslider_".$remove."'";
	@$result=mysql_query($sql);

	$sql="DELETE FROM humo_trees WHERE tree_id='".safe_text($_POST['family_tree_id'])."'";
	$result=mysql_query($sql) or die(mysql_error());

	// *** Remove items from table family_tree_text ***
	$sql="DELETE FROM humo_tree_texts WHERE treetext_tree_id='".safe_text($_POST['family_tree_id'])."'";
	$result=mysql_query($sql) or die(mysql_error());

	// *** Remove statistics ***
	$sql="DELETE FROM humo_stat_date WHERE stat_tree_id='".safe_text($_POST['family_tree_id'])."'";
	$result=mysql_query($sql) or die(mysql_error());

	unset ($_POST['family_tree_id']);
}

if (isset($_GET['up'])){
	// *** Search previous family tree ***
	$item=mysql_query("SELECT * FROM humo_trees WHERE tree_order=".($_GET['tree_order']-1),$db);
	$itemDb=mysql_fetch_object($item);
	// *** Raise previous family trees ***
	$sql="UPDATE humo_trees SET tree_order='".safe_text($_GET['tree_order'])."' WHERE tree_id=$itemDb->tree_id";
	$result=mysql_query($sql);
	// *** Lower tree order ***
	$sql="UPDATE humo_trees SET tree_order='".($_GET['tree_order']-1)."' WHERE tree_id=".$_GET['id'];
	$result=mysql_query($sql) or die(mysql_error());
}
if (isset($_GET['down'])){
	// *** Search next family tree ***
	$item=mysql_query("SELECT * FROM humo_trees WHERE tree_order=".($_GET['tree_order']+1),$db);
	$itemDb=mysql_fetch_object($item);
	//voorgaande database verlagen:
	$sql="UPDATE humo_trees SET tree_order='".safe_text($_GET['tree_order'])."' WHERE tree_id=$itemDb->tree_id";
	$result=mysql_query($sql);
	// *** Raise tree order ***
	$sql="UPDATE humo_trees SET tree_order='".($_GET['tree_order']+1)."' WHERE tree_id=".$_GET['id'];
	$result=mysql_query($sql) or die(mysql_error());
}

// ******************
// *** Start page ***
// ******************

//$language_tree='en'; // Default language
$language_tree=$selected_language; // Default language
if (isset($_GET['language_tree'])){ $language_tree=$_GET['language_tree']; }
if (isset($_POST['language_tree'])){ $language_tree=$_POST['language_tree']; }

include_once ("trees_cls.php");
$tree_cls = New tree_cls;

// *** Selected family tree ***
//if (isset($_POST['add_tree'])){
if (isset($_POST['add_tree_data'])){
	// *** Select new family tree if a new family tree is added ***
	$data2sql = mysql_query("SELECT * FROM humo_trees ORDER BY tree_order DESC LIMIT 0,1",$db);
}
else{
	$data2sql = mysql_query("SELECT * FROM humo_trees ORDER BY tree_order LIMIT 0,1",$db);
}
$data2Db=mysql_fetch_object($data2sql);
if ($data2Db){
	$family_tree_id=$data2Db->tree_id;
}
if (isset($_POST['family_tree_id'])){ $family_tree_id=$_POST['family_tree_id']; }
if (isset($_GET['family_tree_id'])){ $family_tree_id=$_GET['family_tree_id']; }

// ******************************************
// *** Show texts of selected family tree ***
// ******************************************

$data2sql = mysql_query("SELECT * FROM humo_tree_texts where
	treetext_tree_id='".$family_tree_id."' AND treetext_language='".$language_tree."'",$db);
$data2Db=mysql_fetch_object($data2sql);
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
if (isset($_POST['menu_admin'])){ $menu_admin=$_POST['menu_admin']; }
if (isset($_GET['menu_admin'])){ $menu_admin=$_GET['menu_admin']; }

// *** Select family tree ***
$tree_prefix_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
$tree_prefix_result = mysql_query($tree_prefix_sql,$db);
echo __('Family tree').': ';
echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	//echo '<select size="1" name="family_tree_id">';
	echo '<select size="1" name="family_tree_id" onChange="this.form.submit();">';
		while ($tree_prefixDb=mysql_fetch_object($tree_prefix_result)){
			$selected='';
			if ($tree_prefixDb->tree_id==$family_tree_id){ $selected=' SELECTED'; }
			$treetext_name2=database_name($tree_prefixDb->tree_prefix, $selected_language);
			echo '<option value="'.$tree_prefixDb->tree_id.'"'.$selected.'>'.@$treetext_name2.'</option>';
		}
	echo '</select>';
	//echo ' <input type="Submit" name="submit" value="'.__('Select').'">';
echo '</form>';

// *** Family trees administration menu ***
$data2sql = mysql_query("SELECT * FROM humo_trees WHERE tree_id=".$family_tree_id,$db);
$data2Db=mysql_fetch_object($data2sql);

echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="">';
echo '<div class="pageHeading">';
		// <div class="pageHeadingText">Configuratie gegevens</div>
		// <div class="pageHeadingWidgets" aria-hidden="true" style="display: none;"></div>

		echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
			echo '<ul class="pageTabs">';
				//echo '<li class="pageTabItem"><div tabindex="0" class="pageTab pageTab-active">Details</div></li>';

				$select_item=''; if ($menu_admin=='tree_main'){ $select_item=' pageTab-active'; }
				echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;family_tree_id='.$family_tree_id.'">'.__('Family tree administration')."</a></div></li>";

				// *** Family tree data ***
				$select_item=''; if ($menu_admin=='tree_data'){ $select_item=' pageTab-active'; }
				echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_data'.'&amp;family_tree_id='.$family_tree_id.'">'.__('Family tree data')."</a></div></li>";

				// *** Read gedcom file ***
				$select_item=''; if ($menu_admin=='tree_gedcom'){ $select_item=' pageTab-active'; }
				echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_gedcom&amp;family_tree_id='.$family_tree_id.'&amp;tree_prefix='.$data2Db->tree_prefix.'&amp;step1=read_gedcom">'.__('Import Gedcom file')."</a></div></li>";

				// *** Family tree texts ***
				$select_item=''; if ($menu_admin=='tree_text'){ $select_item=' pageTab-active'; }
				echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_text&amp;family_tree_id='.$family_tree_id.'">'.__('Family tree texts (per language)')."</a></div></li>";

				// *** Family tree merge ***
				$select_item=''; if ($menu_admin=='tree_merge'){ $select_item=' pageTab-active'; }
				echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=tree_merge&amp;family_tree_id='.$family_tree_id.'">'.__('Merge Data')."</a></div></li>";
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

	$data2sql = mysql_query("SELECT * FROM humo_trees WHERE tree_id=".$family_tree_id,$db);
	$data2Db=mysql_fetch_object($data2sql);

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