<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Select media').'</h1>';

$place_item='';
$form='';
if (isset($_GET['form'])){
	$check_array = array("1", "2", "5", "6");
	$selected_form='';
	if (in_array($_GET['form'], $check_array)){
		$form='form'.$_GET['form'];
		$selected_form=$_GET['form'];
	}

	$place_item='text_event';

	// *** Multiple events: add event_id ***
	$event_id='';
	if (isset($_GET['event_id']) AND is_numeric($_GET['event_id'])){
		$event_id=$_GET['event_id'];
		$place_item.=$_GET['event_id'];
	}
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

if(CMS_SPECIFIC=="Joomla") {
	$prefx = ''; // in joomla the base folder is the main joomla map - not the HuMo-genealogy admin map
	$joomlastring="option=com_humo-gen&amp;task=admin&amp;";
}
else {
	$prefx = '../'; // to get out of the admin map
	$joomlastring="";
}

// *** Get main path for selected family tree ***
$data2sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id=".$tree_id);
$data2Db=$data2sql->fetch(PDO::FETCH_OBJ);
$pict_path=$data2Db->tree_pict_path; if (substr($pict_path,0,1)=='|') $pict_path='media/';
$array_picture_folder[]=$prefx.$pict_path;
//$array_picture_sub_dir[]='';

// *** Extra safety check if folder exists ***
if (file_exists($array_picture_folder[0])){
	// *** Get all subdirectories ***
	function get_dirs($prefx,$path){
		//global $array_picture_folder,$array_picture_sub_dir;
		global $array_picture_folder;
		$ignore = array( 'cms','slideshow','thumbs','.','..');
		$dh = opendir($prefx.$path);
		while (false !== ($filename = readdir($dh))) {
			if(!in_array($filename,$ignore)){
				// *** Only process directories here. So list of media files will be in directory order ***
				if (is_dir($prefx.$path.$filename)){
					$array_picture_folder[]=$prefx.$path.$filename.'/';
					//sub-sub dir: alles in $array_picture_folder zonder $prefx en $path...
					//$array_picture_sub_dir[]=$filename.'/';
					get_dirs($prefx,$path.$filename.'/');
				}
			}
		}
		closedir($dh);
	}
	// *** Get directories ***
	get_dirs($prefx,$pict_path);

	echo '<form method="POST" action="index.php?page=editor_media_select&form='.$selected_form.'&event_id='.$event_id.'">';
		$search_quicksearch=''; if (isset($_POST['search_quicksearch'])){ $search_quicksearch=safe_text_db($_POST['search_quicksearch']); }
		echo ' <input class="fonts" type="text" name="search_quicksearch" placeholder="'.__('Name').'" value="'.$search_quicksearch.'" size="15">';
		echo ' <input class="fonts" type="submit" name="submit" value="'.__('Search').'">';
	echo '</form><br>';

	// *** List of media files ***
	$ignore = array('.','..','cms','readme.txt','slideshow','thumbs');
	$dirname_start=strlen($prefx.$pict_path);

	//$count_media=(count($array_picture_folder)-1);
	//for($i=0; $i<=$count_media; $i++){
	foreach($array_picture_folder as $selected_picture_folder){
		echo '<br style="clear: both">';
		echo '<h3>'.$selected_picture_folder.'</h3>';

		$dh = opendir($selected_picture_folder);
		$gd=gd_info(); // certain versions of GD don't handle gifs
		while (false !== ($filename = readdir($dh))) {
			if(is_dir($selected_picture_folder.$filename)){
				//
			}
			else{
				if (!in_array($filename,$ignore) AND substr($filename,0,6)!='thumb_'){
					if ($search_quicksearch=='' OR ($search_quicksearch!='' AND strpos($filename,$search_quicksearch)!==false)){
						// *** Replace ' by &prime; otherwise a place including a ' character can't be selected ***
						$sub_dir=substr($selected_picture_folder,$dirname_start);
						echo '<a href="" onClick=\'return select_item("'.$sub_dir.str_replace("'","&prime;",$filename).'")\'>'.$sub_dir.$filename.'</a><br>';
					}
				}
			}
		}
	}

}
?>