<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }
ini_set('memory_limit', '-1');

// *** Style copied from gedcom.css ***
echo '<style>
/* Photobook */
div.photobook{
	background-color:#FAF1EB;
	margin: 2px;
	border: 1px solid #0000ff;
	height : 190px;
	width : 200px;
	float: left;
	text-align: center;
	overflow:auto;
}
div.photobook img{
	display: inline;
	margin: 3px;
	border: 1px solid #ffffff;
}
div.photobook a:hover img {
	border: 1px solid #0000ff;
}
div.photobooktext{
	font-style:italic;
	margin: 2px;
}</style>';

if(CMS_SPECIFIC=="Joomla") {
	$prefx = ''; // in joomla the base folder is the main joomla map - not the HuMo-gen admin map
	$joomlastring="option=com_humo-gen&amp;task=admin&amp;";
}
else {
	$prefx = '../'; // to get out of the admin map
	$joomlastring="";
}

// *** Tab menu ***
$menu_admin='picture_settings';
if (isset($_POST['menu_admin'])){ $menu_admin=$_POST['menu_admin']; }
if (isset($_GET['menu_admin'])){ $menu_admin=$_GET['menu_admin']; }

if (isset($_SESSION['tree_prefix'])) $tree_prefix=$_SESSION['tree_prefix'];
if (isset($_POST['tree_prefix'])){
	$tree_prefix=safe_text($_POST["tree_prefix"]);
	$_SESSION['tree_prefix']=safe_text($_POST['tree_prefix']);
}

echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="">';
echo '<div class="pageHeading">';
	// <div class="pageHeadingText">Configuratie gegevens</div>
	// <div class="pageHeadingWidgets" aria-hidden="true" style="display: none;"></div>

	echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
		echo '<ul class="pageTabs">';
			//echo '<li class="pageTabItem"><div tabindex="0" class="pageTab pageTab-active">Details</div></li>';

			// *** Picture settings ***
			$select_item=''; if ($menu_admin=='picture_settings'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'">'.__('Picture settings')."</a></div></li>";

			// *** Create thumbnails ***
			$select_item=''; if ($menu_admin=='picture_thumbnails'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=picture_thumbnails'.'">'.__('Create thumbnails')."</a></div></li>";

			// *** Show thumbnails ***
			$select_item=''; if ($menu_admin=='picture_show'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=picture_show">'.__('Show thumbnails')."</a></div></li>";

			// *** Picture categories ***
			$select_item=''; if ($menu_admin=='picture_categories'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=picture_categories">'.__('Photo album categories')."</a></div></li>";
		echo '</ul>';
	echo '</div>';
echo '</div>';
echo '</div>';

// *** Align content to the left ***
echo '<div style="float: left; background-color:white; height:500px; padding:10px;">';

// *** Default settings ***
$end_text='';
$show_table=false;
$table_header_text=__('Picture settings');

// *** Show picture settings ***
if (isset($menu_admin) AND $menu_admin=='picture_settings'){
	$end_text='- '.__('To show pictures, also check the user-group settings: ');
	$end_text.=' <a href="index.php?page=groups">'.__('User groups').'</a>';
	$show_table=true;
}

// *** Create picture thumbnails ***
if (isset($menu_admin) AND $menu_admin=='picture_thumbnails'){
	$end_text=__('- Creating thumbnails<br>
- ATTENTION: it may be necessary to (temporarily) change access to the folder with the pictures (rwxrwxrwx)<br>
- Sometimes the php.ini has to be changed slightly, remove the ; before the line with:');
	$end_text.=' <i>extension=php.gd2.dll</i>';
	$show_table=true;
	$table_header_text=__('Create thumbnails');
}

// *** Show picture thumbnails ***
if (isset($menu_admin) AND $menu_admin=='picture_show'){
	$show_table=true;
	$table_header_text=__('Show thumbnails');
}

// *** Selection table ***
if ($show_table){
	//echo '<table class="humo standard" style="width:800px;" border="1">';
	//echo '<table class="humo standard" style="margin-left:0px;" border="1">';
	echo '<table class="humo" style="margin-left:0px;" border="1">';

	echo '<tr class="table_header"><th colspan="2">';
	echo $table_header_text;
	echo '</th></tr>';

		echo '<tr><td class="line_item">'.__('Choose family').'</td>';
		echo '<td>';
			$tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
			$tree_result = $dbh->query($tree_sql);
			echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
			echo '<input type="hidden" name="page" value="thumbs">';
			echo '<select size="1" name="tree_prefix">';
				while ($treeDb=$tree_result->fetch(PDO::FETCH_OBJ)){
					$treetext=show_tree_text($treeDb->tree_prefix, $selected_language);
					$selected='';
					if (isset($tree_prefix)){
						if ($treeDb->tree_prefix==$tree_prefix){
							$selected=' SELECTED';
							// *** Needed for submitter ***
							//$tree_owner=$treeDb->tree_owner;
							$tree_id=$treeDb->tree_id;
							$tree_prefix=$treeDb->tree_prefix;
							$db_functions->set_tree_id($tree_id);
						}
					}
					echo '<option value="'.$treeDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
				}
			echo '</select>';

			echo ' <input type="Submit" name="submit_button" value="'.__('Select').'">';
			echo '</form>';

		echo '</td></tr>';

		// *** Set path to pictures ***
		if (isset($tree_prefix)){
			// *** Save new/ changed picture path ***
			if (isset($_POST['change_tree_data'])){
				$tree_pict_path=$_POST['tree_pict_path'];
				if (substr($_POST['tree_pict_path'],0,1)=='|'){
					if (isset($_POST['default_path']) AND $_POST['default_path']=='no') $tree_pict_path=substr($tree_pict_path,1);
				}
				else{
					if (isset($_POST['default_path']) AND $_POST['default_path']=='yes') $tree_pict_path='|'.$tree_pict_path;
				}

				$sql="UPDATE humo_trees SET
				tree_pict_path='".safe_text($tree_pict_path)."' WHERE tree_id=".safe_text($_POST['tree_id']);
				$result=$dbh->query($sql);
			}

			$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=".$tree_id);
			$data2Db=$data2sql->fetch(PDO::FETCH_OBJ);

			echo '<tr><td class="line_item">';
				echo __('Path to the pictures');
			echo '</td><td>';
				// *** Picture path. A | character is used for a default path (the old path will remain in the field) ***
				if (substr($data2Db->tree_pict_path,0,1)=='|'){
					$checked1 = ' checked'; $checked2 = '';
				}
				else{
					$checked1 = ''; $checked2 = ' checked';
				}
				$tree_pict_path=$data2Db->tree_pict_path;
				if (substr($data2Db->tree_pict_path,0,1)=='|') $tree_pict_path=substr($tree_pict_path,1);

				echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
				echo '<input type="hidden" name="page" value="thumbs">';
				echo '<input type="hidden" name="tree_prefix" value="'.$tree_prefix.'">';
				echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';

				echo '<input type="radio" value="yes" name="default_path" '.$checked1.'> '.__('Use default picture path:').' <b>media/</b><br>';
				echo '<input type="radio" value="no" name="default_path" '.$checked2.'> ';

				echo '<input type="text" name="tree_pict_path" value="'.$tree_pict_path.'" size="40">';

				echo ' <input type="Submit" name="change_tree_data" value="'.__('Change').'">';
				echo ' '.__('example: ../pictures/');
				echo '</form>';
			echo '</td></tr>';

			// *** Status of picture path ***
			$path_status='';
			$tree_pict_path=$data2Db->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';
			//if ($data2Db->tree_pict_path!='' AND file_exists($prefx.$data2Db->tree_pict_path))
			if ($tree_pict_path!='' AND file_exists($prefx.$tree_pict_path))
				$path_status = __('Picture path exists.');
			else
				$path_status = '<span class="line_nok"><b>'.__('Picture path doesn\'t exist!').'</b></span>';

			echo '<tr><td class="line_item">';
				echo __('Status of picture path');
			echo '</td><td>';
				echo $path_status;
			echo '</td></tr>';

			// *** Create thumbnails ***
			if (isset($menu_admin) AND $menu_admin=='picture_thumbnails'){
				// *** Thumb height ***
				$thumb_height=120; // *** Standard thumb height ***
				if (isset($_POST['pict_height']) AND is_numeric($_POST['pict_height'])){ $thumb_height=$_POST['pict_height']; }
				echo '<tr><td class="line_item">';
					echo ucfirst (strtolower(__('CREATE THUMBNAILS')));
				echo '</td><td>';
					echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
					echo '<input type="hidden" name="page" value="thumbs">';
					echo '<input type="hidden" name="menu_admin" value="picture_thumbnails">';
					echo '<input type="hidden" name="tree" value="'.$tree_prefix.'">';
					echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
					echo __('Thumbnail height: ').' <input type="text" name="pict_height" value="'.$thumb_height.'" size="4"> pixels';
					echo ' <input type="Submit" name="thumbnail" value="'.__('Create thumbnails').'">';
					echo '</form>';
				echo '</td></tr>';
			}

			// *** Show thumbnails ***
			if (isset($menu_admin) AND $menu_admin=='picture_show'){
				echo '<tr><td class="line_item">';
					echo __('Show thumbnails');
				echo '</td><td>';
					echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
					echo '<input type="hidden" name="page" value="thumbs">';
					echo '<input type="hidden" name="menu_admin" value="picture_show">';
					echo '<input type="hidden" name="tree" value="'.$tree_prefix.'">';
					echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
					echo ' <input type="Submit" name="change_filename" value="'.__('Show thumbnails').'">';
					echo ' '.__('You can change filenames here.');
					echo '</form>';
				echo '</td></tr>';
			}

		}
	echo '</table><br>';

	echo $end_text.'<br>';
}


// *** Picture categories ***
if (isset($menu_admin) AND $menu_admin=='picture_categories'){
	$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
	if(!$temp->rowCount()) {  
		// no category database table exists - so create it
		// It has 4 columns:
		//     1. id
		//     2. name of category prefix- 2 letters and underscore chosen by admin (ws_   bp_)
		//     3. language for name of category
		//     4. name of category

		$albumtbl="CREATE TABLE humo_photocat (
			photocat_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			photocat_order MEDIUMINT(6),
			photocat_prefix VARCHAR(30) CHARACTER SET utf8,
			photocat_language VARCHAR(10) CHARACTER SET utf8,
			photocat_name VARCHAR(50) CHARACTER SET utf8
		)";
		$dbh->query($albumtbl);
		// Enter the default category with default name that can be changed by admin afterwards
		$dbh->query("INSERT INTO humo_photocat (photocat_prefix,photocat_order,photocat_language,photocat_name) VALUES ('none','1','default','".safe_text(__('Photos'))."')");
	}

	//echo '<h1 align=center>'.__('Photo album categories').'</h1>';

	$language_tree=$selected_language; // Default language
	if (isset($_GET['language_tree'])){ $language_tree=$_GET['language_tree']; }
	if (isset($_POST['language_tree'])){ $language_tree=$_POST['language_tree']; }

	if(isset($_GET['cat_drop2']) AND $_GET['cat_drop2']==1 AND !isset($_POST['save_cat'])) {
		// delete category and make sure that the order sequence is restored
		$dbh->query("UPDATE humo_photocat SET photocat_order = (photocat_order-1) WHERE photocat_order > '".safe_text($_GET['cat_order'])."'");
		$dbh->query("DELETE FROM humo_photocat WHERE photocat_prefix = '".safe_text($_GET['cat_prefix'])."'");
	}
	if(isset($_GET['cat_up']) AND !isset($_POST['save_cat'])) { 
		// move category up
		$dbh->query("UPDATE humo_photocat SET photocat_order = 'temp' WHERE photocat_order ='".safe_text($_GET['cat_up'])."'");  // set present one to temp
		$dbh->query("UPDATE humo_photocat SET photocat_order = '".$_GET['cat_up']."' WHERE photocat_order ='".(safe_text($_GET['cat_up']) - 1)."'");  // move the one above down
		$dbh->query("UPDATE humo_photocat SET photocat_order = '".(safe_text($_GET['cat_up']) - 1)."' WHERE photocat_order = 'temp'");  // move this one up
		
	}
	if(isset($_GET['cat_down']) AND !isset($_POST['save_cat'])) {
		// move category down
		$dbh->query("UPDATE humo_photocat SET photocat_order = 'temp' WHERE photocat_order ='".safe_text($_GET['cat_down'])."'");  // set present one to temp
		$dbh->query("UPDATE humo_photocat SET photocat_order = '".safe_text($_GET['cat_down'])."' WHERE photocat_order ='".(safe_text($_GET['cat_down']) + 1)."'");  // move the one under it up
		$dbh->query("UPDATE humo_photocat SET photocat_order = '".(safe_text($_GET['cat_down']) + 1)."' WHERE photocat_order = 'temp'");  // move this one down
	}

	if(isset($_POST['save_cat'])) {  // the user decided to add a new category and/or save changes to names
		// save names of existing categories in case some were altered. There is at least always one name (for default category)

		$qry = "SELECT * FROM humo_photocat GROUP BY photocat_prefix";
		$result = $dbh->query($qry);  
		
		while($resultDb = $result->fetch(PDO::FETCH_OBJ)) {  
			if(isset($_POST[$resultDb->photocat_prefix])) {
				if($language_tree != "default") {  
					// only update names for the chosen language
					$check_lang = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix = '".$resultDb->photocat_prefix."' AND photocat_language='".safe_text($language_tree)."'");
					if($check_lang->rowCount() != 0) { // this language already has a name for this category - update it
						$dbh->query("UPDATE humo_photocat SET photocat_name = '".safe_text($_POST[$resultDb->photocat_prefix])."'
							WHERE photocat_prefix = '".$resultDb->photocat_prefix."' AND photocat_language='".safe_text($language_tree)."'");
					}
					else {  // this language doesn't yet have a name for this category - create it
						$dbh->query("INSERT INTO humo_photocat (photocat_prefix, photocat_order, photocat_language, photocat_name) VALUES ('".$resultDb->photocat_prefix."', '".$resultDb->photocat_order."', '".$language_tree."', '".safe_text($_POST[$resultDb->photocat_prefix])."')");
					}
				}
				else {  // update entered names for all languages 
					$check_default = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix = '".$resultDb->photocat_prefix."' AND photocat_language='default'");
					if($check_default->rowCount() != 0) {	// there is a default name for this language - update it
						$dbh->query("UPDATE humo_photocat SET photocat_name = '".safe_text($_POST[$resultDb->photocat_prefix])."'
							WHERE photocat_prefix='".$resultDb->photocat_prefix."' AND photocat_language='default'");
					}
					else {  // no default name yet for this category - create it
						$dbh->query("INSERT INTO humo_photocat (photocat_prefix, photocat_order, photocat_language, photocat_name) VALUES ('".$resultDb->photocat_prefix."', '".$resultDb->photocat_order."', 'default', '".safe_text($_POST[$resultDb->photocat_prefix])."')");
					}
				}
			}
		}
		
		// save new category
		if(isset($_POST['new_cat_prefix']) AND isset($_POST['new_cat_name'])) { 
			if($_POST['new_cat_prefix']!="") {
				$new_cat_prefix = $_POST['new_cat_prefix'];
				$new_cat_name = $_POST['new_cat_name'];
				$warning_prefix =""; $warning_invalid_prefix ="";
				if(preg_match('/^[a-z][a-z]_$/'  ,$_POST['new_cat_prefix'])!==1) {
					$warning_invalid_prefix = __('Prefix has to be 2 letters and _');
					$warning_prefix = $_POST['new_cat_prefix'];
				}
				else {
					$warning_exist_prefix =""; 
					$check_exist = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='".safe_text($new_cat_prefix)."'");
					if($check_exist->rowCount() == 0) {
						if($_POST['new_cat_name']=="") {
							$warning_noname = __('When creating a category you have to give it a name');
							$warning_prefix = $_POST['new_cat_prefix'];
						}
						else {
							$highest_order= $dbh->query("SELECT MAX(photocat_order) AS maxorder FROM humo_photocat");
							$orderDb = $highest_order->fetch(PDO::FETCH_ASSOC);
							$order = $orderDb['maxorder']; $order++;
							$qry = "INSERT INTO humo_photocat (photocat_prefix,photocat_order,photocat_language,photocat_name) VALUES ('".safe_text($new_cat_prefix)."', '".safe_text($order)."', '".safe_text($language_tree)."', '".safe_text($new_cat_name)."')";
							$dbh->query($qry);
						}
					}
					else {   // this category prefix already exists!
						$warning_exist_prefix= __('A category with this prefix already exists!');
						$warning_prefix = $_POST['new_cat_prefix'];
					}
				}
			}
		}	
	}

	categories();
}

// *** Change filename ***
if (isset($_POST['filename'])){
	$picture_path_old=$_POST['picture_path'];
	$picture_path_new=$_POST['picture_path'];
	// *** If filename has a category AND a sub category directory exists, use it ***
	if(substr($_POST['filename'],0,2) != substr($_POST['filename_old'],0,2) AND ($_POST['filename'][2]=='_' OR $_POST['filename_old'][2]=='_')) { // we only have to do this if something changed in a prefix
		if($_POST['filename'][2]=='_') {
			if(preg_match('!.+/[a-z][a-z]/$!',$picture_path_new) == 1) {   // original path had subfolder
				if(is_dir(substr($picture_path_new,0,-3).substr($_POST['filename'],0,2))) {   // subtract subfolder and add new subfolder
					$picture_path_new = substr($picture_path_new,0,-3).substr($_POST['filename'],0,2)."/"; // move from subfolder to other subfolder
				}
				else {
					$picture_path_new = substr($picture_path_new,0,-3); // move file with prefix that has no folder to main folder
				}
			}
			elseif(is_dir($_POST['picture_path'].substr($_POST['filename'],0,2))) {
				$picture_path_new.=substr($_POST['filename'],0,2).'/';   // move from main folder to subfolder
			}
		}
		elseif(preg_match('!.+/[a-z][a-z]/$!',$picture_path_new) == 1)  {    // regular file, just check if original path had subfolder
			$picture_path_new = substr($picture_path_new,0,-3);  // move from subfolder to main folder
		}
	}
	
	if (file_exists($picture_path_old.$_POST['filename_old'])){
		rename ($picture_path_old.$_POST['filename_old'],$picture_path_new.$_POST['filename']);
		echo '<b>'.__('Changed filename:').'</b> '.$picture_path_old.$_POST['filename_old'].' <b>'.__('into filename:').'</b> '.$picture_path_new.$_POST['filename'].'<br>';
	}

	if (file_exists($picture_path_old.'thumb_'.$_POST['filename_old'])){
		rename ($picture_path_old.'thumb_'.$_POST['filename_old'],$picture_path_new.'thumb_'.$_POST['filename']);
		echo '<b>'.__('Changed filename:').' </b>'.$picture_path_old.'thumb_'.$_POST['filename_old'].' <b>'.__('into filename:').'</b> '.$picture_path_new.'thumb_'.$_POST['filename'].'<br>';
	}

	$sql="UPDATE humo_events SET
	event_event='".safe_text($_POST['filename'])."' WHERE event_event='".safe_text($_POST['filename_old'])."'";
	$result=$dbh->query($sql);
}


$counter=0;
if (isset($_POST["thumbnail"]) OR isset($_POST['change_filename'])){
	$pict_path=$data2Db->tree_pict_path; if (substr($pict_path,0,1)=='|') $pict_path='media/';

	@set_time_limit(3000);
	$selected_picture_folder=$prefx.$pict_path;

	// *** Extra safety check if folder exists ***
	if (file_exists($selected_picture_folder)){

		$dh  = opendir($selected_picture_folder);
		$gd=gd_info(); // certain versions of GD don't handle gifs
		while (false !== ($filename = readdir($dh))) {
			$imgtype = strtolower(substr($filename, -3));
			if ($imgtype == "jpg" OR $imgtype == "png" OR ($imgtype == "gif" AND $gd["GIF Read Support"]==TRUE AND $gd["GIF Create Support"]==TRUE)){  
				$pict_path_original=$prefx.$pict_path."/".$filename;    //ORIGINEEL
				$pict_path_thumb=$prefx.$pict_path."/thumb_".$filename; //THUMB

				//*** Create a thumbnail ***
				if (substr ($filename, 0, 5)!='thumb' AND !isset($_POST['change_filename'])){
					// *** Get size of original picture ***
					list($width, $height) = getimagesize($pict_path_original);

					// *** Calculate format ***
					$newheight=$thumb_height;
					$factor=$height/$newheight;
					$newwidth=$width/$factor;

					// *** Picture folder must be writable!!!
					// Sometimes it's necessary to remove ; in php.ini before this line:
					// extension=php.gd2.dll

					// $thumb = imagecreate($newwidth, $newheight);
					$thumb = imagecreatetruecolor($newwidth, $newheight);
					if ($imgtype == "jpg") { $source = imagecreatefromjpeg($pict_path_original); }
					elseif ($imgtype == "png") { $source = imagecreatefrompng($pict_path_original); }
					else { $source = imagecreatefromgif($pict_path_original); }

					// *** Resize ***
					imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
					if ($imgtype == "jpg") { @imagejpeg($thumb, $pict_path_thumb); }
					elseif ($imgtype == "png") { @imagepng($thumb, $pict_path_thumb); }
					else { @imagegif($thumb, $pict_path_thumb); }
				}

				// *** Show thumbnails ***
				if (substr ($filename, 0, 5)!='thumb'){
					//echo '<img src="'.$pict_path_thumb.'">';

					echo '<div class="photobook">';
						echo '<img src="'.$pict_path_thumb.'" title="'.$pict_path_thumb.'">';

						// *** Show name of connected persons ***
						include_once('../include/person_cls.php');
						$picture_text='';
						$sql="SELECT * FROM humo_events WHERE event_tree_id='".safe_text($tree_id)."'
							AND event_connect_kind='person' AND event_kind='picture'
							AND LOWER(event_event)='".strtolower($filename)."'";
						$afbqry= $dbh->query($sql);
						$picture_privacy=false;
						while($afbDb=$afbqry->fetch(PDO::FETCH_OBJ)) {
							$person_cls = New person_cls;
							$personDb=$db_functions->get_person($afbDb->event_connect_id);
							$name=$person_cls->person_name($personDb);
							$picture_text.='<br><a href="'.CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].
								'&amp;id='.$personDb->pers_indexnr.
								'&amp;main_person='.$personDb->pers_gedcomnumber.'">'.$name["standard_name"].'</a><br>';
						}
						echo $picture_text;

						if (isset($_POST['change_filename'])){
							echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
							echo '<input type="hidden" name="page" value="thumbs">';
							echo '<input type="hidden" name="menu_admin" value="picture_show">';
							echo '<input type="hidden" name="tree" value="'.$tree_prefix.'">';
							echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
							echo '<input type="hidden" name="picture_path" value="'.$prefx.$pict_path.'">';
							echo '<input type="hidden" name="filename_old" value="'.$filename.'">';
							echo '<input type="text" name="filename" value="'.$filename.'" size="20">';
							echo '<input type="Submit" name="change_filename" value="'.__('Change filename').'">';
							echo '</form>';
						}
						else{
							echo '<div class="photobooktext">'.$filename.'</div>';
						}
					echo '</div>';  
				}

			}
		}
		closedir($dh);
		
		// if category subfolders exist do the same there...
		$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
		if($temp->rowCount()) {   // there is a category table
			$catg = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");  
			if($catg->rowCount()) {
				while($catDb = $catg->fetch(PDO::FETCH_OBJ)) {   
					if(is_dir($selected_picture_folder.substr($catDb->photocat_prefix,0,2)))  {    // there is a subfolder for this prefix
						$cat_dir = $selected_picture_folder.substr($catDb->photocat_prefix,0,2);
						$dh2  = opendir($cat_dir);
						$gd=gd_info(); // certain versions of GD don't handle gifs
						while (false !== ($filename = readdir($dh2))) {
							$imgtype = strtolower(substr($filename, -3));
							if ($imgtype == "jpg" OR $imgtype == "png" OR ($imgtype == "gif" AND $gd["GIF Read Support"]==TRUE AND $gd["GIF Create Support"]==TRUE)){   
								//$pict_path_original=$prefx.$pict_path."/".$filename;        //ORIGINEEL
								//$pict_path_thumb=$prefx.$pict_path."/thumb_".$filename; //THUMB
								$pict_path_original=$cat_dir."/".$filename;        //ORIGINEEL
								$pict_path_thumb=$cat_dir."/thumb_".$filename; //THUMB
								//*** Create a thumbnail ***
								//if (substr ($filename, 0, 5)!='thumb'){
								if (substr ($filename, 0, 5)!='thumb' AND !isset($_POST['change_filename'])){
									// *** Get size of original picture ***
									list($width, $height) = getimagesize($pict_path_original);

									// *** Calculate format ***
									$newheight=$thumb_height;
									$factor=$height/$newheight;
									$newwidth=$width/$factor;

									// *** Picture folder must be writable!!!
									// Sometimes it's necessary to remove ; in php.ini before this line:
									// extension=php.gd2.dll

									// $thumb = imagecreate($newwidth, $newheight);
									$thumb = imagecreatetruecolor($newwidth, $newheight);
									if ($imgtype == "jpg") { $source = imagecreatefromjpeg($pict_path_original); }
									elseif ($imgtype == "png") { $source = imagecreatefrompng($pict_path_original); }
									else { $source = imagecreatefromgif($pict_path_original); }

									// *** Resize ***
									imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
									if ($imgtype == "jpg") { @imagejpeg($thumb, $pict_path_thumb); }
									elseif ($imgtype == "png") { @imagepng($thumb, $pict_path_thumb); }
									else { @imagegif($thumb, $pict_path_thumb); }
								}

								// *** Show thumbnails ***
								if (substr ($filename, 0, 5)!='thumb'){
									//echo "<img src=\"$pict_path_thumb\">";

									echo '<div class="photobook">';
										echo '<img src="'.$pict_path_thumb.'" title="'.$pict_path_thumb.'">';
										if (isset($_POST['change_filename'])){
											echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
											echo '<input type="hidden" name="page" value="thumbs">';
											echo '<input type="hidden" name="menu_admin" value="picture_show">';
											echo '<input type="hidden" name="tree" value="'.$tree_prefix.'">';
											echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
											echo '<input type="hidden" name="picture_path" value="'.$cat_dir.'/">';
											echo '<input type="hidden" name="filename_old" value="'.$filename.'">';
											echo '<input type="text" name="filename" value="'.$filename.'" size="20">';
											echo '<input type="Submit" name="change_filename" value="'.__('Change filename').'">';
											echo '</form>';
										}
										else
											echo '<div class="photobooktext">'.$filename.'</div>';
									echo '</div>';  
								}
							}
						}

						closedir($dh2);
					}
				}
			}
		}

	}
	else{
		// *** Normally this is not used ***
		echo '<b>'.__('This folder does not exists!').'</b>';
	}

}

echo '</div>';



function categories(){
	global $language, $language_tree, $selected_language, $dbh, $warning_exist_prefix, $warning_prefix, $warning_invalid_prefix, $warning_noname;
	global $page, $language_file, $data2Db,$phpself, $joomlastring;
	
	echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="menu_admin" value="picture_categorie">';
	echo '<input type="hidden" name="language_tree" value="'.$language_tree.'">';

	//echo '<table class="humo" cellspacing="0" style="text-align:center;width:80%">';
	echo '<table class="humo" cellspacing="0" style="margin-left:0px; text-align:center; width:80%">';

	echo '<tr class="table_header"><th colspan="5">'.__('Create categories for your photo albums').'</th></tr>';

	echo '<tr><td style="text-align:left" colspan="5">';
		echo '<ul><li>'.__('Here you can create categories for all your photo albums.</li><li><b>A category will not be displayed in the photobook menu unless there is at least one picture for it.</b></li><li>Click "Default" to create one default name in all languages. Choose a language from the list to set a specific name for that language.<br><b>TIP:</b> First set an English name as default for all languages, then create specific names for those languages that you know. That way no tabs will display without a name in any language. In any case, setting a default name will not overwrite names for specific languages that you have already set.</li><li>The category prefix has to be made up of two letters and an underscore (like: <b>sp_</b> or <b>ws_</b>).</li><li>Pictures that you want to appear in a specific category have to be named with that prefix like: <b>sp_</b>John Smith.jpg</li><li>Pictures that you want to be displayed in the default photo category don\'t need a prefix.');
	echo '</li></ul></td></tr>';

	echo '<tr><td style="border-bottom:0px;width:5%"></td><td style="border-bottom:0px;width:5%"></td><td style="border-bottom:0px;width:5%"></td><td style="font-size:120%;border-bottom:0px;width:25%" white-space:nowrap;"><b>'.__('Category prefix').'</b></td><td style="font-size:120%;border-bottom:0px;width:60%"><b>'.__('Category name').'</b><br></td></tr>';

	$add=""; if(isset($_POST['add_new_cat'])) { $add = "&amp;add_new_cat=1"; }	
	echo '<tr><td style="border-top:0px"><td style="border-top:0px"></td><td style="border-top:0px"></td><td style="border-top:0px"></td><td style="border-top:0px;text-align:center">'.__('Language').':&nbsp&nbsp;';

		// *** Language choice ***
		$language_tree2=$language_tree; if ($language_tree=='default') $language_tree2=$selected_language;
		echo '&nbsp;&nbsp;<div class="ltrsddm" style="display : inline;">';
			//echo '<a href="index.php?'.$joomlastring.'page=photoalbum&amp;language_tree='.$language_tree2.'"';
			echo '<a href="index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories&amp;language_tree='.$language_tree2.'"';
			//echo '<a href="index.php?'.$joomlastring.'page=photoalbum&amp;language_tree='.$language_tree2.'"';
			echo '<a href="index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories&amp;language_tree='.$language_tree2.'"';
				include(CMS_ROOTPATH.'languages/'.$language_tree2.'/language_data.php');
				echo ' onmouseover="mopen(event,\'adminx\',\'?\',\'?\')"';
				$select_top='';
				echo ' onmouseout="mclosetime()"'.$select_top.'>'.'<img src="'.CMS_ROOTPATH.'languages/'.$language_tree2.'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none; height:14px"> '.$language["name"].' <img src="'.CMS_ROOTPATH.'images/button3.png" height= "13" style="border:none;" alt="pull_down"></a>';
			
			echo '<div id="adminx" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()" style="width:250px;">';
				echo '<ul class="humo_menu_item2">';
					for ($i=0; $i<count($language_file); $i++){
						// *** Get language name ***
						if ($language_file[$i] != $language_tree2) {
							include(CMS_ROOTPATH.'languages/'.$language_file[$i].'/language_data.php');

							echo '<li style="float:left; width:124px;">';
								//echo '<a href="index.php?'.$joomlastring.'page=photoalbum&amp;language_tree='.$language_file[$i].$add.'">';
								echo '<a href="index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories&amp;language_tree='.$language_file[$i].$add.'">';
								echo '<img src="'.CMS_ROOTPATH.'languages/'.$language_file[$i].'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none;"> ';
								echo $language["name"];
								echo '</a>';
							echo '</li>';
						}
					}
				echo '</ul>';
			echo '</div>';
		echo '</div>';
		echo '&nbsp;&nbsp;'.__('or').'&nbsp;&nbsp;';
		echo '<a href="index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories&amp;language_tree=default'.$add.'">'.__('Default').'</a> ';
	echo '</td></tr>';
	
	$qry = "SELECT * FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order";
	$cat_result = $dbh->query($qry);
	$number = 1;  // number on list
	
	while ($catDb = $cat_result->fetch(PDO::FETCH_OBJ)){
		$name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='".$catDb->photocat_prefix."' AND photocat_language = '".safe_text($language_tree)."'");
		if($name->rowCount()) {  // there is a name for this language
			$nameDb = $name->fetch(PDO::FETCH_OBJ);
			$catname = $nameDb->photocat_name;
		}
		else {  // maybe a default is set
			$name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='".$catDb->photocat_prefix."' AND photocat_language = 'default'");
			if($name->rowCount()) {  // there is a default name for this category
				$nameDb = $name->fetch(PDO::FETCH_OBJ);
				$catname = $nameDb->photocat_name;
			}
			else {  // no name at all
				$catname = ""; 
			}
		}

		echo '<tr><td>'.$number++.'.</td>';
		// arrows
		$order_sequence= $dbh->query("SELECT MAX(photocat_order) AS maxorder, MIN(photocat_order) AS minorder FROM humo_photocat");
		$orderDb = $order_sequence->fetch(PDO::FETCH_ASSOC);
		$maxorder = $orderDb['maxorder']; 	
		$minorder = $orderDb['minorder'];
		if($catDb->photocat_order == $minorder) {  echo '<td style="text-align:right;padding-right:4px">'; }
		elseif($catDb->photocat_order == $maxorder) {  echo '<td style="text-align:left;padding-left:4px">'; }
		else { echo '<td>'; }
		if($catDb->photocat_order != $minorder) {
			echo '<a href="index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories&amp;cat_prefix='.$catDb->photocat_prefix.'&amp;cat_up='.$catDb->photocat_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif"></a>&nbsp;&nbsp;';
		}		
		if($catDb->photocat_order != $maxorder) {
			echo '<a href="index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories&amp;cat_prefix='.$catDb->photocat_prefix.'&amp;cat_down='.$catDb->photocat_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif"></a>';
		}
		echo '</td><td>';
		if($catDb->photocat_prefix != 'none') {
			echo '<a href="index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories&amp;cat_order='.$catDb->photocat_order.'&amp;cat_prefix='.$catDb->photocat_prefix.'&amp;cat_drop=1"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png"></a>';
		}
		$prefname = $catDb->photocat_prefix;
		if($catDb->photocat_prefix=='none') $prefname = __('default - without prefix');  // display default in the display language, so it is clear to everyone
		echo '</td><td style="white-space:nowrap;">'.$prefname.'</td><td><input type="text" name="'.$catDb->photocat_prefix.'" value="'.$catname.'" size="30"></td></tr>';
	}

	$content=""; if(isset($warning_prefix))  { $content=$warning_prefix;  }
	echo '<tr><td>'.$number.'.</td><td></td><td></td><td style="white-space:nowrap;">'.'<input type="text" name="new_cat_prefix" value="'.$content.'" size="6">';
	$pref = ""; 
	if(isset($warning_invalid_prefix)) echo '<br><span style="color:red">'.$warning_invalid_prefix.'</span>';
	if(isset($warning_exist_prefix)) echo '<br><span style="color:red">'.$warning_exist_prefix.'</span>';
	echo '</td><td><input type="text" name="new_cat_name" value="" size="30">';
	if(isset($warning_noname)) echo '<br><span style="color:red">'.$warning_noname.'</span>';
	echo '</td></tr>';

	if(isset($_GET['cat_drop']) AND $_GET['cat_drop']==1) {
		echo '<tr><td colspan=5 style="color:red;font-weight:bold;font-size:120%">'.__('Do you really want to delete category:').'&nbsp;'.$_GET['cat_prefix'].'&nbsp;?';
		echo '&nbsp;&nbsp;&nbsp;<input type="button" style="color:red;font-weight:bold" onclick="location.href=\'index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories&amp;cat_order='.$_GET['cat_order'].'&amp;cat_prefix='.$_GET['cat_prefix'].'&amp;cat_drop2=1\';" value="'.__('Yes').'">';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" style="color:green;font-weight:bold" onclick="location.href=\'index.php?'.$joomlastring.'page=thumbs&amp;menu_admin=picture_categories\';" value="'.__('No').'">';
		echo '</td></tr>';
	}
	
	echo '</table>';
	echo '<br><div style="margin-left:auto; margin-right:auto; text-align:center;"><input type="Submit" name="save_cat" value="'.__('Save changes').'"></div>';
	echo '</form>';
}

?>