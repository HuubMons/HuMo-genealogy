<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Thumbnails').'</h1>';

echo __('- Creating thumbnails<br>
- ATTENTION: it may be necessary to (temporarily) change access to the folder with the pictures (rwxrwxrwx)<br>
- Sometimes the php.ini has to be changed slightly, remove the ; before the line with:');

echo ' <i>extension=php.gd2.dll</i>';

if(CMS_SPECIFIC=="Joomla") {
	print "<p><form method='post' action='index.php?option=com_humo-gen&amp;task=admin&amp;page=thumbs'>";
	$prefx = ''; // in joomla the base folder is the main joomla map - not the HuMo-gen admin map
}
else {
	print "<p><form method='post' action='".$_SERVER['PHP_SELF']."'>";
	$prefx = '../'; // to get out of the admin map
}

echo '<input type="hidden" name="page" value="'.$page.'">';

// *** Select folder ***
$dataqry='SELECT * FROM humo_trees GROUP BY tree_pict_path';
@$datasql = $dbh->query($dataqry);
echo __('Path to pictures:');
echo ' <select size="1" name="picture_path">';
while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
	$pict_path=$dataDb->tree_pict_path;
	if (file_exists($prefx.$pict_path)){
		$selected='';
		if (isset($_POST['picture_path'])){ if ($_POST['picture_path']==$pict_path){ $selected=' SELECTED'; } }
		echo '<option value="'.$pict_path.'"'.$selected.'>'.
		@$pict_path.'</option>';
	}
}
echo '</select><br>';

// *** Thumb height ***
$thumb_height=120; // *** Standard thumb height ***
if (isset($_POST['pict_height']) AND is_numeric($_POST['pict_height'])){ $thumb_height=$_POST['pict_height']; }
echo __('Thumbnail height: ').' <input type="text" name="pict_height" value="'.$thumb_height.'" size="4"> pixels <br>';
print '<input type="Submit" name="thumbnail" value="'.__('CREATE THUMBNAILS').'">';
print "</form>";

$counter=0;
if (isset($_POST["thumbnail"])){
	$pict_path=$_POST['picture_path'];

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
		echo 'Deze map bestaat niet - this folder does not exists!';
	}

}
?>