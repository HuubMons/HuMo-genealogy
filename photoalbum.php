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
$qry="SELECT event_event, event_kind, event_person_id FROM humo_events WHERE event_tree_id='".$tree_id."' AND event_kind='picture'";	
$picqry=$dbh->query($qry); 
$my_array=Array(); 
while($picqryDb = $picqry->fetch(PDO::FETCH_OBJ)) {
	if($picqryDb->event_person_id != '') { // we only want to include pics with a person_id
		$picname = str_replace(" ","_",$picqryDb->event_event);
		if(!isset($my_array[$picname])) { // this pic does not appear in the array yet
			$my_array[$picname]=$picqryDb->event_person_id; 
			// example: $my_array['an_example.jpg']="I354"
		}
		else { // pic already exists in array with other person_id. Append this one.
			$my_array[$picname] .= '@@'.$picqryDb->event_person_id;
			// example: $my_array['an_example.jpg']="I354@@I653"
		}
	}
}
// *** Read all photos from directory ***
$dir=$dataDb->tree_pict_path;
if (file_exists($dir)){
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if ((strtolower(substr($filename, -3)) == "jpg" OR strtolower(substr($filename, -3)) == "gif") AND substr($filename,0,6)!='thumb_'){

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
}
// *** Order pictures by alphabet ***
@sort($picture_array);

// *** Calculate pages ***
$nr_pictures=count($picture_array);

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
		'"> =&gt;</a>';
	}


echo '<div class="center">';
	echo __('Photo\'s per page');
	print '<select name="show_pictures" onChange="window.location=this.value">';
	for ($i=4; $i<=60; $i++) {
		print '<option value="'.$albumpath.
			'show_pictures='.$i.'
			&amp;start=0&amp;item=0"';
		if ($i == $show_pictures) print ' selected="selected"';
		print ">".$i."</option>\n";
	}
	print '</select> ';

	echo $line_pages;

	// *** Search by photo name ***
	//echo ' <form method="post" action="photoalbum.php" style="display:inline">';
	if(CMS_SPECIFIC=="Joomla") {   // cant use $albumpath here cause we don't need the &amp; for joomla or the ? for humogen
		echo ' <form method="post" action="index.php?option=com_humo-gen&amp;task=photoalbum" style="display:inline">';
	}
	else {
		echo ' <form method="post" action="photoalbum.php" style="display:inline">';
	}
	print ' <input type="text" class="fonts" name="photo_name" value="'.$photo_name.'" size="20">';
	print ' <input class="fonts" type="submit" value="'.__('Search').'">';
	print ' </form>';

echo '</div><br>';

// *** Show photos ***
for ($picture_nr=$item; $picture_nr<($item+$show_pictures); $picture_nr++){
	if (isset($picture_array[$picture_nr])){
		$filename=$picture_array[$picture_nr];
		$picture_text='';	// Text with link to person
		$picture_text2='';	// Text without link to person

		$sql="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_kind='picture' AND LOWER(event_event)='".strtolower($filename)."'";
		$afbqry= $dbh->query($sql);
		$picture_privacy=false;
		while($afbDb=$afbqry->fetch(PDO::FETCH_OBJ)) {
			$person_cls = New person_cls;
			@$personDb=$db_functions->get_person($afbDb->event_person_id);
			$name=$person_cls->person_name($personDb);
			$picture_text.='<a href="'.CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].
				'&amp;id='.$personDb->pers_indexnr.
				'&amp;main_person='.$personDb->pers_gedcomnumber.'">'.$name["standard_name"].'</a><br>';
			$picture_text2.=$name["standard_name"].'<br>';
			$privacy=$person_cls->set_privacy($personDb);
			if ($privacy){ $picture_privacy=true; }
			if($afbDb->event_text!='') {
				$picture_text.=$afbDb->event_text.'<br>';
				$picture_text2.=$afbDb->event_text.'<br>';
			}
		}


		// *** Show texts from connected objects ***
		$picture_qry=$dbh->query("SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_kind='object' AND LOWER(event_event)='".strtolower($filename)."'");
		while($pictureDb=$picture_qry->fetch(PDO::FETCH_OBJ)) {
		 	//$connect_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."connections
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
				$picture_text2.=$name["standard_name"].'<br>';
				$privacy=$person_cls->set_privacy($personDb);
				if ($privacy){ $picture_privacy=true; }
				$picture_text.=$pictureDb->event_text.'<br>';
				$picture_text2.=$pictureDb->event_text.'<br>';
			}
		}

		$picture2=show_picture($dir,$filename,180,120);
		$picture='<img src="'.$dir.$picture2['thumb'].$picture2['picture'].'" width="'.$picture2['width'].'" alt="'.$filename.'"></a>';

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

echo '<br clear="all"><br>';
echo '<div class="center">'.$line_pages.'</div>';

include_once(CMS_ROOTPATH."footer.php");
?>