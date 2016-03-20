<?php
$show_pictures=8; // *** Default value ***
if(isset($_COOKIE["humogenphotos"])) {
	$show_pictures=$_COOKIE["humogenphotos"];
}
elseif (isset( $_SESSION['save_show_pictures'])){
	$show_pictures=$_SESSION['save_show_pictures'];
}
if (isset($_POST['show_pictures'])){
	$show_pictures=$_POST['show_pictures'];
	$_SESSION['save_show_pictures']=$show_pictures;
	setcookie("humogenphotos", $show_pictures, time()+60*60*24*365);
}
if (isset($_GET['show_pictures'])){
	$show_pictures=$_GET['show_pictures'];
	$_SESSION['save_show_pictures']=$show_pictures;
	setcookie("humogenphotos", $show_pictures, time()+60*60*24*365);
}

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

// *** Check user privileges ***
if ($user['group_pictures']!='j' OR $user['group_photobook']!='j'){
	echo __('You are not authorised to see this page.');
	exit();
}

include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/show_picture.php");

// *** Photo search ***
$photo_name='';
if (isset($_POST['photo_name'])){ $photo_name=safe_text($_POST['photo_name']); }
if (isset($_GET['photo_name'])){ $photo_name=safe_text($_GET['photo_name']); }


// Create one-time an array of all pics with person_id's
$qry="SELECT event_event, event_kind, event_connect_kind, event_connect_id FROM humo_events
	WHERE event_tree_id='".$tree_id."' AND event_kind='picture'";
$picqry=$dbh->query($qry);
$my_array=Array();
while($picqryDb = $picqry->fetch(PDO::FETCH_OBJ)) {
	if($picqryDb->event_connect_kind=='person' AND $picqryDb->event_connect_id != '') { // we only want to include pics with a person_id
		$picname = str_replace(" ","_",$picqryDb->event_event);  
		if(!isset($my_array[$picname])) { // this pic does not appear in the array yet
			$my_array[$picname]=$picqryDb->event_connect_id; 
			// example: $my_array['an_example.jpg']="I354"
		}
		else { // pic already exists in array with other person_id. Append this one.
			$my_array[$picname] .= '@@'.$picqryDb->event_connect_id;
			// example: $my_array['an_example.jpg']="I354@@I653"
		}
	}
}

$categories = false; // is set true by following code if necessary
$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");  
if($temp->rowCount()) {   // a humo_photocat table exists
	$temp2 = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none'");
	if($temp2->rowCount() >= 1) { //  the table contains more than the default category (otherwise display regular photoalbum)
		$qry = "SELECT * FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order";
		$result = $dbh->query($qry);
		$result_arr = $result->fetchAll(); // PDO has a problem with resetting pointer in MySQL and the fastest workaround is to use an array instead
		$catpics=0; // checks if any of the user-created categories have pics at all, otherwise don't show the tabbed menu, just regular photo album

		foreach($result_arr as $row) {
			if($row['photocat_prefix'] != 'none')   {  
				$check = glob($dataDb->tree_pict_path.'/'.$row['photocat_prefix'].'*');
				if($check!==false AND count($check) >= 1) {  // found at least one pic for this category
					$catpics++;
				}
				elseif(is_dir($dataDb->tree_pict_path.'/'.substr($row['photocat_prefix'],0,2))) {
					$check = glob($dataDb->tree_pict_path.'/'.substr($row['photocat_prefix'],0,2).'/'.$row['photocat_prefix'].'*');
					if($check!==false AND count($check) >= 1) {  // found at least one pic for this category
						$catpics++;
					}
					// check for sub-sub cat
					else{
						$check2 = glob($dataDb->tree_pict_path.'/'.substr($row['photocat_prefix'],0,2).'/'.'*');
						if($check2!==false AND count($check2) >= 1) {  // found at least one sub-sub for this category
							$catpics++;
						}
					}
				} 
			}
		}  
		if($catpics>0) { // at least one of the user-created categories has at least one picture
			$categories = true;  
			$menu_admin='none';  // by default display the default category
			if(isset($_GET['menu_photoalbum'])) $menu_admin = $_GET['menu_photoalbum'];
			echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="">';
			echo '<div class="pageHeading">';

				echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
					echo '<ul class="pageTabs">';
						$cat_string=""; // will hold the string of user-created prefixes for use in the showthem function below

						foreach($result_arr as $row) {
							if($row['photocat_prefix'] != 'none')   {  
								$cat_string .= $row['photocat_prefix']."@"; 
								// now check if there are pics for this category, otherwise don't show it
								$check2 = glob($dataDb->tree_pict_path.'/'.$row['photocat_prefix'].'*');
								if($check2===false OR count($check2)==0) {  // if there are no pics for this category, try subfolder
									if(is_dir($dataDb->tree_pict_path.'/'.substr($row['photocat_prefix'],0,2))) {  // check for subfolder of the prefix name (without underscore)
										$check3 = glob($dataDb->tree_pict_path.'/'.substr($row['photocat_prefix'],0,2).'/'.$row['photocat_prefix'].'*');
										if($check3===false OR count($check3) == 0) {  // no pics in subfolder  - maybe sub-subs									
											$check4 = glob($dataDb->tree_pict_path.'/'.substr($row['photocat_prefix'],0,2).'/'.'*');
											if($check4!==false AND count($check4) >= 1) {  // found at least one sub-sub for this category
												$catpics++;   
											}
											else {
												continue; 
											}
										}
										else { $catpics++; }
									} 
									else { 
										continue; 
									}
								}
								//if($check2===false OR count($check2)==0) { continue; }   // if there are no pics for this category, don't show the category tab
							}
							// check if name for this category exists for this language
							$qry2= "SELECT * FROM humo_photocat WHERE photocat_prefix ='".$row['photocat_prefix']."' AND photocat_language ='".$selected_language."'";
							$result2 = $dbh->query($qry2);
							if($result2->rowCount()!=0) {  
								$catnameDb = $result2->fetch(PDO::FETCH_OBJ);
								$menutab_name = $catnameDb->photocat_name;
							}
							else {
								// check if default name exists for this category
								$qry3= "SELECT * FROM humo_photocat WHERE photocat_prefix ='".$row['photocat_prefix']."' AND photocat_language ='default'";
								$result3 = $dbh->query($qry3);	
								if($result3->rowCount()!=0) {  
									$catnameDb = $result3->fetch(PDO::FETCH_OBJ);
									$menutab_name = $catnameDb->photocat_name;
								}
								else {
									// no name at all (neither default nor specific)
									$menutab_name = __('NO NAME');
								}
							}
							$select_item=''; if ($menu_admin==$row['photocat_prefix']){ $select_item=' pageTab-active'; }
							echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="photoalbum.php?menu_photoalbum='.$row['photocat_prefix'].'&amp;tree_id='.$tree_id.'">'.$menutab_name."</a></div></li>";
						}
						
					echo '</ul>';
				echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}
}

if($categories == false) {  //show regular photo album with no categories
	showthem("none");  // show all
}
else {  // show album with category tabs
	$chosen_tab = 'none';
	if(isset($_GET['menu_photoalbum'])) { $chosen_tab = $_GET['menu_photoalbum']; }
	$result = $dbh->query("SELECT * FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order");
	while($prefixDb = $result->fetch(PDO::FETCH_OBJ)) {
		if($chosen_tab==$prefixDb->photocat_prefix) {
			showthem($chosen_tab);  // show only pics that match this category
		}
	}
}

function showthem ($pref) {
	global $dataDb, $photo_name, $dbh, $show_pictures, $uri_path, $tree_id, $db_functions, $my_array, $cat_string, $categories, $chosen_tab;
	
	$subfolder="";
	if($pref!='none'  AND $pref!="dummy" AND is_dir($dataDb->tree_pict_path.substr($pref,0,2).'/')) { $subfolder= substr($pref,0,2).'/'; }
	// *** Read all photos from directory ***
	$dir=$dataDb->tree_pict_path.$subfolder;  
	
	$picture_array = Array();
	if (file_exists($dir)){   //echo $dir."<br>";
		$dh  = opendir($dir);
		$subsub=false;
		$sub_arr = array();
		while (false !== ($filename = readdir($dh))) {  //echo $filename."<br>";
			if ((strtolower(substr($filename, -3)) == "jpg" OR strtolower(substr($filename, -3)) == "gif" OR strtolower(substr($filename, -3)) == "png") AND substr($filename,0,6)!='thumb_'){
				if(($pref != 'none' AND $pref != 'dummy' AND substr($filename,0,3)==$pref) OR ($pref == 'none' AND strpos($cat_string,substr($filename,0,3)."@")===false) OR $pref=='dummy') {
					// *** Use search field (search for person) to show pictures ***
					$show_photo=true; 
					if ($photo_name){ 
						$show_photo=false;
						$quicksearch=str_replace(" ", "%", $photo_name);
						if(isset($my_array[str_replace(" ","_",$filename)])) { // pic appears somewhere in the event table with one or more person_id's
							$id_arr = explode("@@",$my_array[str_replace(" ","_",$filename)]);
							foreach($id_arr AS $value) {
								$querie= "SELECT pers_firstname, pers_prefix, pers_lastname FROM humo_persons
									WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$value."'
									AND CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$quicksearch%'";
								$persoon = $dbh->query($querie);
								$personDb = $persoon->fetch(PDO::FETCH_OBJ);
								if ($personDb){ $show_photo=true; }
							}
						}
						if(stripos($filename,$quicksearch)!==false){ $show_photo=true; }
					}
					if ($show_photo){
						$picture_array[]=$filename;
					}
				}
			}
			elseif($pref!='none' AND $pref!='dummy' AND is_dir($dir.$filename) AND $filename != "." AND $filename != ".." AND substr($filename,0,6)!='thumb_') {
				$subsub=true;
				$dh2  = opendir($dir.$filename);
				while (false !== ($subfilename = readdir($dh2))) {
					if((substr($subfilename,-4)=='.jpg'  OR substr($subfilename,-4)=='.png' OR substr($subfilename,-4)=='.gif')  AND substr($subfilename,0,6)!='thumb_') { 
						if ($photo_name){
							$quicksearch=str_replace(" ", "%", $photo_name);
							if(stripos($subfilename,$quicksearch)!==false){ $sub_arr[$filename][]=$subfilename; }
						}
						else {
							$sub_arr[$filename][]=$subfilename;
						}
					}
				}
				closedir($dh2);
			}  
		}
		closedir($dh);
	}  
	$subpage="";
	if($subsub==true) {
		if(isset($_GET['sub'])) {
			$subpage = $_GET['sub'];
		}
		else {
			reset($sub_arr);
			$subpage = key($sub_arr);
		}
	}	
	
	// *** Order pictures by alphabet ***
	//@sort($picture_array);
	
	
	// *** Calculate pages ***
	
	if($subsub==true) {
		@usort($sub_arr[$subpage],'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11
		$nr_pictures = count($sub_arr[$subpage]);
	}
	else {
		@usort($picture_array,'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11
		$nr_pictures=count($picture_array);	
	}

	if(CMS_SPECIFIC=="Joomla") {
		$albumpath='index.php?option=com_humo-gen&amp;task=photoalbum&amp;';
	}
	else {
		$albumpath=$uri_path."photoalbum.php?";
	}

	$item=0; if (isset($_GET['item'])){ $item=$_GET['item']; }
	$start=0; if (isset($_GET["start"])){ $start=$_GET["start"]; }

	$line_pages=__('Page');

	// "<="
	if ($start>1){
		$start2=$start-20;
		$calculated=($start-2)*$show_pictures;
		$line_pages.= ' <a href="'.$albumpath.
		"start=".$start2.
		"&amp;item=".$calculated.
		"&amp;show_pictures=".$show_pictures.
		"&amp;photo_name=".$photo_name.
		"&amp;menu_photoalbum=".$chosen_tab.
		"&amp;sub=".$subpage.
		'">&lt;= </a>';
	}
	if ($start<=0){$start=1;}

	// 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
	for ($i=$start; $i<=$start+19; $i++) {
		$calculated=($i-1)*$show_pictures;
		if ($calculated<$nr_pictures){
			if ($item==$calculated){
				$line_pages.=  " <b>$i</b>";
			}
			else {
				$line_pages.= ' <a href="'.$albumpath.
				"start=".$start.
				"&amp;item=".$calculated.
				"&amp;show_pictures=".$show_pictures.
				"&amp;photo_name=".$photo_name.
				"&amp;menu_photoalbum=".$chosen_tab.
				"&amp;sub=".$subpage.
				'"> '.$i.'</a>';
			}
		}
	}

	// "=>"
	$calculated=($i-1)*$show_pictures;
	if ($calculated<$nr_pictures){
		$line_pages.= ' <a href="'.$albumpath.
		"start=".$i.
		"&amp;item=".$calculated.
		"&amp;show_pictures=".$show_pictures.
		"&amp;photo_name=".$photo_name.
		"&amp;menu_photoalbum=".$chosen_tab.
		"&amp;sub=".$subpage.
		'"> =&gt;</a>';
	}

	//echo '<div style="float: left; background-color:white; height:auto; padding:5px;">';
	if($categories===true) {
		echo '<div style="float: left; background-color:white; height:auto; width:98%;padding:5px;">';
	}
	else {
		echo '<div>';
	}
	echo '<div style="padding:5px" class="center">';
		echo __('Photo\'s per page');
		print ' <select name="show_pictures" onChange="window.location=this.value">';
		for ($i=4; $i<=60; $i++) {
			print '<option value="'.$albumpath.
				'show_pictures='.$i.'
				&amp;start=0&amp;item=0&amp;sub='.$subpage.'&amp;menu_photoalbum='.$chosen_tab.'"';
			if ($i == $show_pictures) print ' selected="selected"';
			print ">".$i."</option>\n";
		}
		print '</select> ';

		echo $line_pages;

		// *** Search by photo name ***
		//echo ' <form method="post" action="photoalbum.php" style="display:inline">';
		$menu="";
		if($categories===true) {
			$menu = "?menu_photoalbum=".$pref;
		}
		if(CMS_SPECIFIC=="Joomla") {   // cant use $albumpath here cause we don't need the &amp; for joomla or the ? for humogen
			echo ' <form method="post" action="index.php?option=com_humo-gen&amp;task=photoalbum'.$menu.'" style="display:inline">';
		}
		else {
			echo ' <form method="post" action="photoalbum.php'.$menu.'" style="display:inline">';
		}
		print ' <input type="text" class="fonts" name="photo_name" value="'.$photo_name.'" size="20">';
		print ' <input class="fonts" type="submit" value="'.__('Search').'">';
		print ' </form>';

	echo '</div>';

	// *** Show photos ***
if($subsub==true) {

	$dir .= $subpage."/";
	
	echo '<div class="outersub">';
	echo '<div class="leftsub" style="width:15%;float:left">';
	echo '<table style="border-spacing: 10px;border-collapse: separate">';
	foreach($sub_arr AS $key => $value) {
		$selected= 'style="text-align:left"';
		if($key == $subpage) { $selected= 'style="font-weight:bold;text-align:left;background-color:#D7EBFF"';  }
		echo '<tr><td '.$selected.' ><a href="photoalbum.php?menu_photoalbum='.$pref.'&amp;tree_id='.$tree_id.'&amp;photo_name='.$photo_name.'&amp;sub='.$key.'">'.$key.'</a></td></tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '<div class="rightsub" style="width:83%;float:left">';
}
	for ($picture_nr=$item; $picture_nr<($item+$show_pictures); $picture_nr++){  
		$thepic="";
		$pic_isset = false;
		if($subsub==true AND isset($sub_arr[$subpage][$picture_nr])) {   $pic_isset = true; $thepic = $sub_arr[$subpage][$picture_nr]; }
		elseif(isset($picture_array[$picture_nr])) { $pic_isset = true; $thepic = $picture_array[$picture_nr]; }
  
		if($pic_isset==true) {
			$filename=$thepic; 
			$picture_text='';	// Text with link to person
			$picture_text2='';	// Text without link to person

			$sql="SELECT * FROM humo_events
				WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_kind='picture' AND LOWER(event_event)='".strtolower($filename)."'";
			$afbqry= $dbh->query($sql);
			$picture_privacy=false;
			if(!$afbqry->rowCount()) {  $picture_text = substr($thepic,0,-4); }
			while($afbDb=$afbqry->fetch(PDO::FETCH_OBJ)) {
				$person_cls = New person_cls;
				$personDb=$db_functions->get_person($afbDb->event_connect_id);
				$name=$person_cls->person_name($personDb);
				$picture_text.='<a href="'.CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].
					'&amp;id='.$personDb->pers_indexnr.
					'&amp;main_person='.$personDb->pers_gedcomnumber.'">'.$name["standard_name"].'</a><br>';
				$picture_text2.=$name["standard_name"];
				$privacy=$person_cls->set_privacy($personDb);
				if ($privacy){ $picture_privacy=true; }
				if($afbDb->event_text!='') {
					$picture_text.=$afbDb->event_text.'<br>';
					$picture_text2.=$afbDb->event_text;
				}
			}

			// *** Show texts from connected objects ***
			$picture_qry=$dbh->query("SELECT * FROM humo_events
				WHERE event_tree_id='".$tree_id."' AND event_kind='object' AND LOWER(event_event)='".strtolower($filename)."'");
			while($pictureDb=$picture_qry->fetch(PDO::FETCH_OBJ)) {
				$connect_qry=$dbh->query("SELECT * FROM humo_connections
					WHERE connect_tree_id='".$tree_id."'
					AND connect_sub_kind='pers_object'
					AND connect_source_id='".$pictureDb->event_gedcomnr."'");
				while($connectDb=$connect_qry->fetch(PDO::FETCH_OBJ)) {
					$person_cls = New person_cls;
					@$personDb=$db_functions->get_person($connectDb->connect_connect_id);
					$name=$person_cls->person_name($personDb);
					$picture_text.='<a href="'.CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].
					'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'">'.$name["standard_name"].'</a><br>';
					$picture_text2.=$name["standard_name"];
					$privacy=$person_cls->set_privacy($personDb);
					if ($privacy){ $picture_privacy=true; }
					if($afbDb->event_text!='') {
						$picture_text.=$pictureDb->event_text.'<br>';
						$picture_text2.=$pictureDb->event_text;
					}
				}
			}

			$picture2=show_picture($dir,$filename,175,120);
			$picture='<img src="'.$dir.$picture2['thumb'].$picture2['picture'].'" width="'.$picture2['width'].'" alt="'.$filename.'"></a>';
			// lightbox can't handle brackets etc so encode it. ("urlencode" doesn't work since it changes spaces to +, so we use rawurlencode)
			$filename = rawurlencode($filename);
			
			echo '<div class="photobook">';
				if ($picture_privacy==false){
					// *** Show photo using the lightbox effect ***
					echo '<a href="'.$dir.$filename.'" rel="lightbox" title="'.$picture_text2.'">';
					echo $picture;
					echo '<div class="photobooktext">'.$picture_text.'</div>';
				}
				else{
					echo __('PRIVACY FILTER');
				}
			echo '</div>';  
	
		}  
	}
if($subsub==true) { 
	echo '</div>'; // rightsub	
	echo '</div>'; // outersub		
}
	echo '</div>'; // end of white menu page
	echo '<br clear="all"><br>';
	echo '<div class="center">'.$line_pages.'</div>';

} // end of function showthem()

include_once(CMS_ROOTPATH."footer.php");
?>