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
$search_media='';
if (isset($_POST['search_media'])){ $search_media=safe_text_db($_POST['search_media']); }
if (isset($_GET['search_media'])){ $search_media=safe_text_db($_GET['search_media']); }

$tree_pict_path=$dataDb->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';

// *** Get array of categories ***
$show_categories = false; // is set true by following code if necessary
$chosen_tab = 'none';
$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
if($temp->rowCount()) {   // a humo_photocat table exists
	$temp2 = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none'");
	if($temp2->rowCount() >= 1) { //  the table contains more than the default category (otherwise display regular photoalbum)
		//$qry = "SELECT photocat_prefix, photocat_order FROM humo_photocat GROUP BY photocat_prefix, photocat_order";
		//$qry = "SELECT photocat_prefix FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order";
		$qry = "SELECT photocat_id, photocat_prefix FROM humo_photocat GROUP BY photocat_prefix ORDER BY photocat_order";
		$result = $dbh->query($qry);
		$result_arr = $result->fetchAll();
		foreach($result_arr as $row) {
			$category_array[]=$row['photocat_prefix'];
			$category_id_array[$row['photocat_prefix']]=$row['photocat_id'];
			$category_enabled[$row['photocat_prefix']]=false;
		}
	}
	// *** Get selected category ***
	if(isset($_GET['select_category']) AND $_GET['select_category']!='none' AND in_array($_GET['select_category'],$category_array)) {
		$chosen_tab = $_GET['select_category'];
	}
}

// *** Create an array of all pics with person_id's. Also check for OBJECT (Family Tree Maker GEDCOM file) ***
//$qry="SELECT event_event, event_kind, event_connect_kind, event_connect_id, event_gedcomnr FROM humo_events
//	WHERE (event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_kind='picture' AND event_connect_id NOT LIKE '')
//	OR (event_tree_id='".$tree_id."' AND event_kind='object')";
$qry="SELECT event_event, event_kind, event_connect_kind, event_connect_id, event_gedcomnr FROM humo_events
	WHERE (event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_kind='picture' AND event_connect_id NOT LIKE '')
	OR (event_tree_id='".$tree_id."' AND event_kind='object')
	ORDER BY event_event";
$picqry=$dbh->query($qry);
//$connected_persons=array();
while($picqryDb = $picqry->fetch(PDO::FETCH_OBJ)) {
	//$picname = str_replace(" ","_",$picqryDb->event_event);
	$picname = $picqryDb->event_event;

	// *** Show all media, or show only media from selected category ***
	$process_picture=false;
	if ($chosen_tab=='none' AND !isset($category_array)){
		// *** No categories ***
		$process_picture=true;
	}
	// *** Example in subdir: subdir1/subdir2/ak_akte_mons.gif ***
	//elseif ($chosen_tab!='none' AND substr($picname,0,3)==$chosen_tab){
	elseif ($chosen_tab!='none' AND strpos($picname,$chosen_tab)!==false){
		// *** One category is selected ***
		$process_picture=true;
	}
	// *** Example of category dir: xy/picture.jpg ***
	elseif (strpos($picname,substr($chosen_tab,0,2).'/')!==false){
		// *** One category is selected ***
		$process_picture=true;
	}
	// *** If there are categories: filter category photo's from results ***
	elseif ($chosen_tab=='none' AND isset($category_array)){
		// *** There are categories: filter these items in main page ***
		$process_picture=true;
		//if (isset($category_array) AND in_array(substr($picname,0,3),$category_array)) $process_picture=false;
		// *** Example in subdir: subdir1/subdir2/ak_akte_mons.gif ***
		foreach($category_array as $test_category){
			if (strpos($picname,$test_category)!==false){
				$process_picture=false;
			}
			// *** Example of subdir category: xy/subdir2/akte_mons.gif ***
			if (strpos($picname,substr($test_category,0,2).'/')!==false){
				$process_picture=false;
			}
		}
	}

	// *** Use search field (search for person) to show pictures ***
	if ($search_media){
		$quicksearch=str_replace(" ", "%", $search_media);
		$querie= "SELECT pers_firstname, pers_prefix, pers_lastname FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$picqryDb->event_connect_id."'
			AND CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$quicksearch%'";
		$persoon = $dbh->query($querie);
		$personDb = $persoon->fetch(PDO::FETCH_OBJ);
		if (!$personDb){ $process_picture=false; }
	}

	// *** Check for privacy of connected persons ***
	if ($process_picture){
		if ($picqryDb->event_connect_id){
			// *** Check privacy filter ***
			$person_cls = New person_cls;
			$personDb=$db_functions->get_person($picqryDb->event_connect_id);
			$privacy=$person_cls->set_privacy($personDb);
			if ($privacy){ $process_picture=false; }
			//$name=$person_cls->person_name($personDb);
			// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
			//$url=$person_cls->person_url2($personDb->pers_tree_id,$personDb->pers_famc,$personDb->pers_fams,$personDb->pers_gedcomnumber);
			//$picture_text.='<a href="'.$url.'">'.$name["standard_name"].'</a><br>';
			//$picture_text2.=$name["standard_name"];
		}
		else{
			// *** OBJECTS: Family Tree Maker GEDCOM file ***
			$connect_qry=$dbh->query("SELECT connect_connect_id FROM humo_connections
				WHERE connect_tree_id='".$tree_id."' AND connect_sub_kind='pers_object' AND connect_source_id='".$picqryDb->event_gedcomnr."'");
			while($connectDb=$connect_qry->fetch(PDO::FETCH_OBJ)) {
				$person_cls = New person_cls;
				$personDb=$db_functions->get_person($connectDb->connect_connect_id);
				$privacy=$person_cls->set_privacy($personDb);
				if ($privacy){ $process_picture=false; }
			}
		}
	}

	if ($process_picture){
		//if(!isset($connected_persons[$picname])) { // this pic does not appear in the array yet
		//	$connected_persons[$picname]=$picqryDb->event_connect_id; // example: $connected_persons['an_example.jpg']="I354"
		//	$media_files[]=$picname;
		//}

		//if(!isset($media_files[$picname])) { // this pic does not appear in the array yet
		if(!isset($media_files) OR !in_array($picname,$media_files)) { // this pic does not appear in the array yet
			//$connected_persons[$picname]=$picqryDb->event_connect_id; // example: $connected_persons['an_example.jpg']="I354"
			$media_files[]=$picname;
		}

		//else { // pic already exists in array with other person_id. Append this one.
		//	$connected_persons[$picname] .= '@@'.$picqryDb->event_connect_id; // example: $connected_persons['an_example.jpg']="I354@@I653"
		//	//$media_files[]=$picname;
		//}
	}

	// *** Check if media belongs to category ***
	//if (in_array(substr($picname,0,3),$category_array)){
	//	$show_categories=true; // *** There are categories ***
	//	$category_enabled[substr($picname,0,3)]=true; // *** This categorie will be shown ***
	//}
	if (isset($category_array)){
		foreach($category_array as $test_category){
			// *** Check if media belongs to category ***
			// *** Example in subdir: subdir1/subdir2/ak_akte_mons.gif ***
			if (strpos($picname,$test_category)!==false){
				$show_categories=true; // *** There are categories ***
				$category_enabled[$test_category]=true; // *** This categorie will be shown ***
			}

			// *** Check if directory belongs to category, example: xy/picture.jpg ***
			if (strpos($picname,substr($test_category,0,2).'/')!==false){
				$show_categories=true; // *** There are categories ***
				$category_enabled[$test_category]=true; // *** This categorie will be shown ***
			}
		}
	}
}

// *** Show categories ***
if ($show_categories){
	$category_enabled['none']=true; // *** Always show main category ***
	$selected_category='none'; if(isset($_GET['select_category'])) $selected_category = $_GET['select_category'];
	echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="">';
	echo '<div class="pageHeading">';
		echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
			echo '<ul class="pageTabs">';
				foreach($category_array as $category) {
					if ($category_enabled[$category]==true){
						// check if name for this category exists for this language
						$qry2= "SELECT * FROM humo_photocat WHERE photocat_prefix ='".$category."' AND photocat_language ='".$selected_language."'";
						$result2 = $dbh->query($qry2);
						if($result2->rowCount()!=0) {
							$catnameDb = $result2->fetch(PDO::FETCH_OBJ);
							$menutab_name = $catnameDb->photocat_name;
						}
						else {
							// check if default name exists for this category
							$qry3= "SELECT * FROM humo_photocat WHERE photocat_prefix ='".$category."' AND photocat_language ='default'";
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

						$select_item=''; if ($selected_category==$category){ $select_item=' pageTab-active'; }
						echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="photoalbum.php?tree_id='.$tree_id.'&amp;select_category='.$category.'">'.$menutab_name."</a></div></li>";
					}
				}
			echo '</ul>';
		echo '</div>';
	echo '</div>';
	echo '</div>';
}

// *** Show media/ photo's ***
if (isset($media_files)){
	if($show_categories == false) {  // *** There are no categories: show regular photo album ***
		show_media_files("none");  // show all
	}
	else {  // *** Show album with category tabs ***
		// *** Check is photo category tree is shown or hidden for user group ***
		$hide_photocat_array=explode(";",$user['group_hide_photocat']);
		$hide_photocat=false; if (in_array($category_id_array[$chosen_tab], $hide_photocat_array)) $hide_photocat=true;
		if ($hide_photocat==false)
			show_media_files($chosen_tab);  // show only pics that match this category
		else{
			echo '<div style="float: left; background-color:white; height:auto; width:98%;padding:5px;"><br>';
			echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***');
			echo '<br><br></div>';
		}
	}
}

// *** $pref = category ***
function show_media_files($pref) {
	global $dataDb, $search_media, $dbh, $show_pictures, $uri_path, $tree_id, $db_functions, $cat_string, $show_categories, $chosen_tab, $media_files;
	$tree_pict_path=$dataDb->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';
	$dir=$tree_pict_path;

	// *** Calculate pages ***
	// Ordering is now done in query...
	//@usort($media_files,'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11
	$nr_pictures=count($media_files);

	if(CMS_SPECIFIC=="Joomla") {
		$albumpath='index.php?option=com_humo-gen&amp;task=photoalbum&amp;';
	}
	else {
		$albumpath=$uri_path.'photoalbum.php?tree_id='.$tree_id.'&amp;';
	}

	$item=0; if (isset($_GET['item'])){ $item=$_GET['item']; }
	$start=0; if (isset($_GET["start"])){ $start=$_GET["start"]; }

	$line_pages=__('Page');

	// "<="
	if ($start>1){
		$start2=$start-20;
		$calculated=($start-2)*$show_pictures;
		//"&amp;sub=".$subpage.
		$line_pages.= ' <a href="'.$albumpath.
		"start=".$start2.
		"&amp;item=".$calculated.
		"&amp;show_pictures=".$show_pictures.
		"&amp;search_media=".$search_media.
		"&amp;select_category=".$chosen_tab.
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
				//"&amp;sub=".$subpage.
				$line_pages.= ' <a href="'.$albumpath.
				"start=".$start.
				"&amp;item=".$calculated.
				"&amp;show_pictures=".$show_pictures.
				"&amp;search_media=".$search_media.
				"&amp;select_category=".$chosen_tab.
				'"> '.$i.'</a>';
			}
		}
	}

	// "=>"
	$calculated=($i-1)*$show_pictures;
	if ($calculated<$nr_pictures){
		//"&amp;sub=".$subpage.
		$line_pages.= ' <a href="'.$albumpath.
		"start=".$i.
		"&amp;item=".$calculated.
		"&amp;show_pictures=".$show_pictures.
		"&amp;search_media=".$search_media.
		"&amp;select_category=".$chosen_tab.
		'"> =&gt;</a>';
	}

	if($show_categories===true) {
		echo '<div style="float: left; background-color:white; height:auto; width:98%;padding:5px;">';
	}
	else {
		echo '<div>';
	}
	echo '<div style="padding:5px" class="center">';
		echo __('Photo\'s per page');
		echo ' <select name="show_pictures" onChange="window.location=this.value">';
		for ($i=4; $i<=60; $i++) {
			//sub='.$subpage
			echo '<option value="'.$albumpath.
				'show_pictures='.$i.'
				&amp;start=0&amp;item=0&amp;select_category='.$chosen_tab.'"';
			if ($i == $show_pictures) echo ' selected="selected"';
			echo ">".$i."</option>\n";
		}
		echo '</select> ';

		echo $line_pages;

		// *** Search by photo name ***
		$menu="";
		if($show_categories===true) {
			$menu = '?select_category='.$pref;
		}
		if(CMS_SPECIFIC=="Joomla") {   // cant use $albumpath here cause we don't need the &amp; for joomla or the ? for humogen
			echo ' <form method="post" action="index.php?option=com_humo-gen&amp;task=photoalbum'.$menu.'" style="display:inline">';
		}
		else {
			echo ' <form method="post" action="photoalbum.php'.$menu.'" style="display:inline">';
		}
		echo ' <input type="text" class="fonts" name="search_media" value="'.$search_media.'" size="20">';
		echo ' <input class="fonts" type="submit" value="'.__('Search').'">';
		echo ' </form>';

	echo '</div>';

	// *** Show photos ***
	for ($picture_nr=$item; $picture_nr<($item+$show_pictures); $picture_nr++){
		if (isset($media_files[$picture_nr])){
			$filename=$media_files[$picture_nr];
			$picture_text='';	// Text with link to person
			$picture_text2='';	// Text without link to person

			//$sql="SELECT * FROM humo_events
			//	WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_kind='picture' AND LOWER(event_event)='".strtolower($filename)."'";
			$sql="SELECT * FROM humo_events
				WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND LEFT(event_kind,7)='picture' AND LOWER(event_event)='".safe_text_db(strtolower($filename))."'";
			$afbqry= $dbh->query($sql);
			if(!$afbqry->rowCount()) { $picture_text = substr($filename,0,-4); }
			while($afbDb=$afbqry->fetch(PDO::FETCH_OBJ)) {
				$person_cls = New person_cls;
				$personDb=$db_functions->get_person($afbDb->event_connect_id);
				$name=$person_cls->person_name($personDb);
				$privacy=$person_cls->set_privacy($personDb);
				if (!$privacy){
					// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
					$url=$person_cls->person_url2($personDb->pers_tree_id,$personDb->pers_famc,$personDb->pers_fams,$personDb->pers_gedcomnumber);
					$picture_text.='<a href="'.$url.'">'.$name["standard_name"].'</a><br>';
					$picture_text2.=$name["standard_name"];
				}

				$date_place=date_place($afbDb->event_date,$afbDb->event_place);
				if($afbDb->event_text OR $date_place) {
					if ($date_place) $picture_text.=$date_place.' ';
					$picture_text.=$afbDb->event_text.'<br>';

					//$picture_text2.=$afbDb->event_text; // Only use event text in lightbox.
					$picture_text2.='<br>';
					if ($date_place) $picture_text2.=$date_place.' ';
					$picture_text2.=$afbDb->event_text;
				}
			}

			// *** Show texts from connected objects (where object is saved in seperate table): Family Tree Maker GEDCOM file ***
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
					$privacy=$person_cls->set_privacy($personDb);
					if (!$privacy){
						// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
						$url=$person_cls->person_url2($personDb->pers_tree_id,$personDb->pers_famc,$personDb->pers_fams,$personDb->pers_gedcomnumber);
						if ($picture_text) $picture_text.='<br>';
						$picture_text.='<a href="'.$url.'">'.$name["standard_name"].'</a><br>';
						$picture_text2.=$name["standard_name"];
					}

					//if($pictureDb->event_text!='') {
					//	$picture_text.=$pictureDb->event_text.'<br>';
					//	//$picture_text2=$pictureDb->event_text; // Only use event text in lightbox.
					//	$picture_text2.='<br>'.$pictureDb->event_text;
					//}
					$date_place=date_place($pictureDb->event_date,$pictureDb->event_place);
					if($pictureDb->event_text OR $date_place) {
						if ($date_place) $picture_text.=$date_place.' ';
						$picture_text.=$pictureDb->event_text.'<br>';

						//$picture_text2.=$afbDb->event_text; // Only use event text in lightbox.
						$picture_text2.='<br>';
						if ($date_place) $picture_text2.=$date_place.' ';
						$picture_text2.=$pictureDb->event_text;
					}

				}
			}

			$picture2=show_picture($dir,$filename,175,120);
			//$picture2=show_picture($dir,$filename,175,0);
			//$picture2=show_picture($dir,$filename,0,120);
			// *** Check if media exists ***
			//if (file_exists($dir.$picture2['thumb'].$picture2['picture'])){
			if (file_exists($picture2['path'].$picture2['thumb'].$picture2['picture'])){
				//$picture='<img src="'.$dir.$picture2['thumb'].$picture2['picture'].'" width="'.$picture2['width'].'" alt="'.$filename.'"></a>';
				$picture='<img src="'.$picture2['path'].$picture2['thumb'].$picture2['picture'].'" width="'.$picture2['width'].'" alt="'.$filename.'"></a>';
				//$picture='<img src="'.$picture2['path'].$picture2['thumb'].$picture2['picture'].'" height="'.$picture2['height'].'" alt="'.$filename.'"></a>';
			}
			else{
				$picture='<img src="images/missing-image.jpg" width="'.$picture2['width'].'" alt="'.$filename.'"></a>';
			}
			// lightbox can't handle brackets etc so encode it. ("urlencode" doesn't work since it changes spaces to +, so we use rawurlencode)
			//$filename = rawurlencode($filename);

			echo '<div class="photobook">';
				//if ($picture_privacy==false){
					// *** Show photo using the lightbox effect ***
					//echo '<a href="'.$dir.$filename.'" rel="lightbox" title="'.$picture_text2.'">';
					// *** Show photo using the lightbox: GLightbox effect ***
					//echo '<a href="'.$dir.$filename.'" class="glightbox3" data-gallery="gallery1" data-glightbox="description: '.str_replace("&", "&amp;", $picture_text2).'">';
					echo '<a href="'.$dir.$filename.'" class="glightbox3" data-gallery="gallery1" data-glightbox="description: .custom-desc'.$picture_nr.'">';
					// *** Need a class for multiple lines and HTML code in a text ***
					echo '<div class="glightbox-desc custom-desc'.$picture_nr.'">'.$picture_text2.'</div>';
						echo $picture;
					echo '<div class="photobooktext">'.$picture_text.'</div>';
				//}
				//else{
				//	echo __('PRIVACY FILTER');
				//}
			echo '</div>';
		}
	}
	echo '</div>'; // end of white menu page
	echo '<br clear="all"><br>';
	echo '<div class="center">'.$line_pages.'</div>';
} //  *** End of function showthem() ***

include_once(CMS_ROOTPATH."footer.php");
?>