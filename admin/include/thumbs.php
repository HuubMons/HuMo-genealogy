<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Pictures/ create thumbnails').'</h1>';

echo '- '.__('To show pictures, also check the user-group settings: ');
echo ' <a href="index.php?page=groups">'.__('User groups').'</a><br><br>';

echo __('- Creating thumbnails<br>
- ATTENTION: it may be necessary to (temporarily) change access to the folder with the pictures (rwxrwxrwx)<br>
- Sometimes the php.ini has to be changed slightly, remove the ; before the line with:');
echo ' <i>extension=php.gd2.dll</i>';

if (isset($_POST['tree'])){ $tree=safe_text($_POST["tree"]); }

if(CMS_SPECIFIC=="Joomla") {
	//print "<p><form method='post' action='index.php?option=com_humo-gen&amp;task=admin&amp;page=thumbs'>";
	$prefx = ''; // in joomla the base folder is the main joomla map - not the HuMo-gen admin map
}
else {
	//print "<p><form method='post' action='".$_SERVER['PHP_SELF']."'>";
	$prefx = '../'; // to get out of the admin map
}

echo '<br><br><table class="humo standard" style="width:800px;" border="1">';

echo '<tr class="table_header"><th colspan="2">'.__('Pictures/ create thumbnails').'</th></tr>';

	echo '<tr><td>'.__('Choose family').'</td>';
	echo '<td>';
		$tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$tree_result = $dbh->query($tree_sql);
		echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
		echo '<input type="hidden" name="page" value="thumbs">';
		echo '<select size="1" name="tree">';
			while ($treeDb=$tree_result->fetch(PDO::FETCH_OBJ)){
				$treetext=show_tree_text($treeDb->tree_prefix, $selected_language);
				$selected='';
				if (isset($tree)){
					if ($treeDb->tree_prefix==$tree){
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
			$sql="UPDATE humo_trees SET
			tree_pict_path='".safe_text($_POST['tree_pict_path'])."' WHERE tree_id=".safe_text($_POST['tree_id']);
			$result=$dbh->query($sql);
		}

		$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=".$tree_id);
		$data2Db=$data2sql->fetch(PDO::FETCH_OBJ);

		echo '<tr><td>';
			echo __('Path to the pictures');
		echo '</td><td>';
			echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
			echo '<input type="hidden" name="page" value="thumbs">';
			echo '<input type="hidden" name="tree" value="'.$tree_prefix.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo '<input type="text" name="tree_pict_path" value="'.$data2Db->tree_pict_path.'" size="40">';
			echo ' <input type="Submit" name="change_tree_data" value="'.__('Change').'">';
			echo ' '.__('example: ../pictures/');
			echo '</form>';
		echo '</td></tr>';

		$path_status='';
		if ($data2Db->tree_pict_path!='' AND file_exists($prefx.$data2Db->tree_pict_path))
			$path_status = __('Picture path exists.');
		else
			$path_status = '<b>'.__('Picture path doesn\'t exist!').'</b>';

		echo '<tr><td>';
			echo __('Status of picture path');
		echo '</td><td>';
			echo $path_status;
		echo '</td></tr>';

		// *** Thumb height ***
		$thumb_height=120; // *** Standard thumb height ***
		if (isset($_POST['pict_height']) AND is_numeric($_POST['pict_height'])){ $thumb_height=$_POST['pict_height']; }
		echo '<tr><td>';
			echo ucfirst (strtolower(__('CREATE THUMBNAILS')));
		echo '</td><td>';
			echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
			echo '<input type="hidden" name="page" value="thumbs">';
			echo '<input type="hidden" name="tree" value="'.$tree_prefix.'">';
			echo '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
			echo __('Thumbnail height: ').' <input type="text" name="pict_height" value="'.$thumb_height.'" size="4"> pixels';
			echo ' <input type="Submit" name="thumbnail" value="'.__('CREATE THUMBNAILS').'">';
			echo '</form>';
		echo '</td></tr>';

	}

echo '</table><br>';


$counter=0;
if (isset($_POST["thumbnail"])){
	//$pict_path=$_POST['picture_path'];
	$pict_path=$data2Db->tree_pict_path;

	@set_time_limit(3000);
	$selected_picture_folder=$prefx.$pict_path;

	// *** Extra safety check if folder exists ***
	if (file_exists($selected_picture_folder)){

		$dh  = opendir($selected_picture_folder);
		$gd=gd_info(); // certain versions of GD don't handle gifs
		while (false !== ($filename = readdir($dh))) {
			$imgtype = strtolower(substr($filename, -3));
			if ($imgtype == "jpg" OR $imgtype == "png" OR ($imgtype == "gif" AND $gd["GIF Read Support"]==TRUE AND $gd["GIF Create Support"]==TRUE)){  
				$pict_path_original=$prefx.$pict_path."/".$filename;        //ORIGINEEL
				$pict_path_thumb=$prefx.$pict_path."/thumb_".$filename; //THUMB

				//*** Create a thumbnail ***
				if (substr ($filename, 0, 5)!='thumb'){
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
					print "<img src=\"$pict_path_thumb\">";
				}

			}
		}

	}
	else{
		// *** Normally this is not used ***
		echo '<b>'.__('This folder does not exists!').'</b>';
	}

}
?>