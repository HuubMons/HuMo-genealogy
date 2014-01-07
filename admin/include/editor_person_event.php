<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

$phpself.='#event_person_link';

// *** Picture list for selecting pictures ***
$datasql = mysql_query("SELECT * FROM humo_trees WHERE tree_prefix='".$tree_prefix."'",$db);
$dataDb=mysql_fetch_object($datasql);
$tree_pict_path=$dataDb->tree_pict_path;
$dir=$path_prefix.$tree_pict_path;
if (file_exists($dir)){
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if (substr($filename,0,6)!='thumb_' AND $filename!='.' AND $filename!='..'){
			$picture_array[]=$filename;
		}
	}
}
// *** Order pictures by alphabet ***
@sort($picture_array);
$nr_pictures=count($picture_array);

if (isset($_POST['person_event_add'])){
	// *** Generate new order number ***
	$event_sql="SELECT * FROM ".$tree_prefix."events
		WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='".$_POST["event_kind"]."'
		ORDER BY event_order DESC LIMIT 0,1";
	$event_qry=mysql_query($event_sql,$db);
	$eventDb=mysql_fetch_object($event_qry);
	$event_order=0;
	if (isset($eventDb->event_order)){
		$event_order=$eventDb->event_order;
	}
	$event_order++;

	$sql="INSERT INTO ".$tree_prefix."events SET
		event_person_id='".$pers_gedcomnumber."',
		event_kind='".$_POST["event_kind"]."',
		event_order='".$event_order."',
		event_new_date='".$gedcom_date."',
		event_new_time='".$gedcom_time."'";
	$result=mysql_query($sql) or die(mysql_error());
}


// *** Upload images ***
if (isset($_FILES['photo_upload']) AND $_FILES['photo_upload']['name']){
	if ( $_FILES['photo_upload']['type']=="image/pjpeg" || $_FILES['photo_upload']['type']=="image/jpeg"){
		$fault="";
		// 100000=100kb.
		if($_FILES['photo_upload']['size']>2000000){ $fault=__('Photo too large'); }
		if (!$fault){
			$picture_original=$dir.$_FILES['photo_upload']['name'];
			$picture_thumb=$dir.'thumb_'.$_FILES['photo_upload']['name'];
			if (!move_uploaded_file($_FILES['photo_upload']['tmp_name'],$picture_original)){
				echo __('Photo upload failed, check folder rights');
			}
			else{
				// *** Resize uploaded picture ***
				if (strtolower(substr($picture_original, -3)) == "jpg"){
					//Breedte en hoogte origineel bepalen
					list($width, $height) = getimagesize($picture_original);

					$create_thumb_height=120;
					$newheight=$create_thumb_height;
					$factor=$height/$newheight;
					$newwidth=$width/$factor;

					$create_thumb = imagecreatetruecolor($newwidth, $newheight);
					$source = imagecreatefromjpeg($picture_original);

					// Resize
					imagecopyresized($create_thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
					@imagejpeg($create_thumb, $picture_thumb);
				}

//					$_POST['text_event'][$key]=$_FILES['photo_upload']['name'];

				// *** Add picture to array ***
				$picture_array[]=$_FILES['photo_upload']['name'];

				// *** Re-order pictures by alphabet ***
				@sort($picture_array);
				$nr_pictures=count($picture_array);
			}
		}
		else{
			print "<FONT COLOR=red>$fault</FONT>";
		}
	}
	elseif ( $_FILES['photo_upload']['type']=="audio/mpeg" || $_FILES['photo_upload']['type']=="audio/mpeg3" || 
		$_FILES['photo_upload']['type']=="audio/x-mpeg" || $_FILES['photo_upload']['type']=="audio/x-mpeg3" || 
		$_FILES['photo_upload']['type']=="audio/mpg" || $_FILES['photo_upload']['type']=="audio/mp3" || 
		$_FILES['photo_upload']['type']=="audio/mid" || $_FILES['photo_upload']['type']=="audio/midi" || 
		$_FILES['photo_upload']['type']=="audio/x-midi" || $_FILES['photo_upload']['type']=="audio/x-ms-wma" || 
		$_FILES['photo_upload']['type']=="audio/wav" || $_FILES['photo_upload']['type']=="audio/x-wav" || 
		$_FILES['photo_upload']['type']=="audio/x-pn-realaudio" || $_FILES['photo_upload']['type']=="audio/x-realaudio" || 
		$_FILES['photo_upload']['type']=="application/pdf" || $_FILES['photo_upload']['type']=="application/msword" || 
		$_FILES['photo_upload']['type']=="application/vnd.openxmlformats-officedocument.wordprocessingml.document" ||
		$_FILES['photo_upload']['type']=="video/quicktime" || $_FILES['photo_upload']['type']=="video/x-ms-wmv" ||
		$_FILES['photo_upload']['type']=="video/avi" || $_FILES['photo_upload']['type']=="video/x-msvideo" ||   
		$_FILES['photo_upload']['type']=="video/msvideo" || $_FILES['photo_upload']['type']=="video/mpeg" 	){
		$fault="";
		// 49MB
		if($_FILES['photo_upload']['size']>49000000){ $fault=__('Media too large'); }
		if (!$fault){
			$picture_original=$dir.$_FILES['photo_upload']['name'];
			if (!move_uploaded_file($_FILES['photo_upload']['tmp_name'],$picture_original)){
				echo __('Media upload failed, check folder rights');
			}
			else{
//					$_POST['text_event'][$key]=$_FILES['photo_upload']['name'];

				// *** Add picture to array ***
				$picture_array[]=$_FILES['photo_upload']['name'];

				// *** Re-order pictures by alphabet ***
				@sort($picture_array);
				$nr_pictures=count($picture_array);
			}
		}
		else{
			print "<FONT COLOR=red>$fault</FONT>";
		}					
	}
	else{
		echo '<FONT COLOR=red>'.__('No valid picture, media or document file').'</font>';
	}
}


if (isset($_POST['person_event_change'])){
	foreach($_POST['person_event_id'] as $key=>$value){  
		$event_event=$editor_cls->text_process($_POST["text_event"][$key]);
		if (isset($_POST["text_event2"][$key]) AND $_POST["text_event2"][$key]!=''){ $event_event=$editor_cls->text_process($_POST["text_event2"][$key]); }

		$sql="UPDATE ".$tree_prefix."events SET
			event_event='".$event_event."',
			event_date='".$editor_cls->date_process("event_date",$key)."',
			event_place='".$editor_cls->text_process($_POST["event_place"][$key])."',
			event_changed_date='".$gedcom_date."', ";
		if (isset($_POST["event_gedcom"][$key])){
			$sql.="event_gedcom='".$editor_cls->text_process($_POST["event_gedcom"][$key])."',";
		}
		if (isset($_POST["event_text"][$key])){
			$sql.="event_text='".$editor_cls->text_process($_POST["event_text"][$key])."',";
		}
		$sql.=" event_changed_time='".$gedcom_time."'";
		//$sql.=" WHERE event_id='".safe_text($_POST["person_event_change"])."'";
		$sql.=" WHERE event_id='".safe_text($_POST["person_event_id"][$key])."'";

	//echo $sql.'<br>';
		$result=mysql_query($sql) or die(mysql_error());

		family_tree_update($tree_prefix);
	}
}

// *** Remove event ***
if (isset($_GET['person_event_drop'])){
	echo '<div class="confirm">';
	echo __('Are you sure you want to remove this event?');
	echo ' <form method="post" action="'.$phpself.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
	echo '<input type="hidden" name="event_person" value="event_person">';
	echo '<input type="hidden" name="event_kind" value="'.$_GET['event_kind'].'">';
	echo '<input type="hidden" name="person_event_drop" value="'.$_GET['person_event_drop'].'">';
	echo ' <input type="Submit" name="person_event_drop2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	echo '</form>';
	echo '</div>';
}
if (isset($_POST['person_event_drop2'])){
	$event_kind=safe_text($_POST['event_kind']);
	$event_order_id=safe_text($_POST['person_event_drop']);

	$sql="DELETE FROM ".$tree_prefix."events
		WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='".$event_kind."' AND event_order='".$event_order_id."'";
	$result=mysql_query($sql) or die(mysql_error());

	$event_sql="SELECT * FROM ".$tree_prefix."events
		WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='".$event_kind."' AND event_order>'".$event_order_id."' ORDER BY event_order";
	$event_qry=mysql_query($event_sql,$db);
	while($eventDb=mysql_fetch_object($event_qry)){
		$sql="UPDATE ".$tree_prefix."events SET
		event_order='".($eventDb->event_order-1)."',
		event_changed_date='".$gedcom_date."',
		event_changed_time='".$gedcom_time."'
		WHERE event_id='".$eventDb->event_id."'";
		$result=mysql_query($sql) or die(mysql_error());
	}
}

if (isset($_GET['person_event_down'])){
	$event_kind=safe_text($_GET['event_kind']);
	$event_order=safe_text($_GET["person_event_down"]);

	$sql="UPDATE ".$tree_prefix."events SET event_order='99'
	WHERE event_person_id='".$pers_gedcomnumber."'
	AND event_kind='".$event_kind."'
	AND event_order='".$event_order."'";
	$result=mysql_query($sql) or die(mysql_error());

	$sql="UPDATE ".$tree_prefix."events SET event_order='".$event_order."'
	WHERE event_person_id='".$pers_gedcomnumber."'
	AND event_kind='".$event_kind."'
	AND event_order='".($event_order+1)."'";
	$result=mysql_query($sql) or die(mysql_error());

	$sql="UPDATE ".$tree_prefix."events SET event_order='".($event_order+1)."'
	WHERE event_person_id='".$pers_gedcomnumber."'
	AND event_kind='".$event_kind."'
	AND event_order=99";
	$result=mysql_query($sql) or die(mysql_error());
}

if (isset($_GET['person_event_up'])){
	$event_kind=safe_text($_GET['event_kind']);
	$event_order=safe_text($_GET["person_event_up"]);

	$sql="UPDATE ".$tree_prefix."events SET
	event_order='99'
	WHERE event_person_id='".$pers_gedcomnumber."'
	AND event_kind='".$event_kind."'
	AND event_order='".$event_order."'";
	$result=mysql_query($sql) or die(mysql_error());

	$sql="UPDATE ".$tree_prefix."events SET
	event_order='".$event_order."'
	WHERE event_person_id='".$pers_gedcomnumber."'
	AND event_kind='".$event_kind."'
	AND event_order='".($event_order-1)."'";
	$result=mysql_query($sql) or die(mysql_error());

	$sql="UPDATE ".$tree_prefix."events SET
	event_order='".($event_order-1)."'
	WHERE event_person_id='".$pers_gedcomnumber."'
	AND event_kind='".$event_kind."'
	AND event_order=99";
	$result=mysql_query($sql) or die(mysql_error());
}


echo '<tr><td class="table_header_large" colspan="4">';

	//echo '<h2 align="center">'.__('Events for person').'</h2>';
	//echo __('Events for person');
	echo '<h3 style="display : inline;">'.__('Events for person').'</h3>';

	// *** Upload image ***
	echo '&nbsp;&nbsp;&nbsp;<form method="POST" action="'.$phpself.'" enctype="multipart/form-data" class="center" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="event_person" value="event_person">';
		echo 'Upload new image (max: pic 2MB) or media (max: 49 MB):'.' <input type="file" name="photo_upload">';
		echo '<input type="submit" name="submit" title="submit" value="'.__('Upload').'">';
	echo '</form>';

echo '</td></tr>';


// *** Show event_kind text ***
function event_text($event_kind){
	global $language;
	if ($event_kind=='picture'){ $event_text=__('Picture/ Media'); }
	elseif ($event_kind=='profession'){ $event_text=__('Profession'); }
	elseif ($event_kind=='event'){ $event_text=__('Event'); }
	elseif ($event_kind=='birth_declaration'){ $event_text=__('Birth Declaration'); }
	elseif ($event_kind=='baptism_witness'){ $event_text=__('Baptism Witness'); }
	elseif ($event_kind=='death_declaration'){ $event_text=__('Death Declaration'); }
	elseif ($event_kind=='burial_witness'){ $event_text=__('Burial Witness'); }
	elseif ($event_kind=='name'){ $event_text=__('Name'); }
	elseif ($event_kind=='nobility'){ $event_text=__('Title of Nobility'); }
	elseif ($event_kind=='title'){ $event_text=__('Title'); }
	elseif ($event_kind=='adoption'){ $event_text=__('Adoption'); }
	elseif ($event_kind=='lordship'){ $event_text=__('Title of Lordship'); }
	elseif ($event_kind=='URL'){ $event_text=__('URL/ Internet link'); }
	elseif ($event_kind=='person_colour_mark'){ $event_text=__('Colour mark by person'); }
	else { $event_text=ucfirst($event_kind); }
	return $event_text;
}

// *** Show events by person ***
$change_bg_colour='';

//echo '<table class="humo standard" border="1">';

echo '<form method="POST" action="'.$phpself.'" enctype="multipart/form-data">';
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '<input type="hidden" name="event_person" value="event_person">';

$data_list_qry=mysql_query("SELECT * FROM ".$tree_prefix."events
	WHERE event_person_id='$pers_gedcomnumber' ORDER BY event_kind, event_order",$db);

//print '<tr class="table_header"><th>'.__('Event').'</th><th style="border-right:0px;">'.__('Option').'</th><th colspan="2" style="border-left:0px;">'.__('Value').'</th></tr>';
print '<tr class="table_header_large"><th>'.__('Event').'</th><th style="border-right:0px;">'.__('Option').'</th><th style="border-left:0px;">'.__('Value').'</th>';
	echo '<td>';
	if (mysql_num_rows($data_list_qry)>0){
		echo '<input type="submit" name="person_event_change" title="submit" value="'.__('Save').'">';
	}
	echo '</td>';
echo '</tr>';

while($data_listDb=mysql_fetch_object($data_list_qry)){
	echo '<input type="hidden" name="person_event_id['.$data_listDb->event_id.']" value="'.$data_listDb->event_id.'">';

	echo '<tr'.$change_bg_colour.'><td>';

		echo '<a href="#event_person_link" onclick="hideShow('.$data_listDb->event_id.'00);"><span id="hideshowlink'.$data_listDb->event_id.'00">'.__('[+]').'</span></a> ';

		echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;event_person=1&amp;event_kind='.$data_listDb->event_kind.'&amp;person_event_drop='.
			$data_listDb->event_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="down"></a>';

		// *** Count number of events ***
		$count_event=mysql_query("SELECT * FROM ".$tree_prefix."events
			WHERE event_person_id='$pers_gedcomnumber' AND event_kind='".$data_listDb->event_kind."'",$db);
		$count=mysql_num_rows($count_event);

		if ($data_listDb->event_order<$count){
			echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;event_person=1&amp;person_event_down='.$data_listDb->event_order.'&amp;event_kind='.$data_listDb->event_kind.'#event_person_link"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>';
		}
		else{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}

		if ($data_listDb->event_order>1){
			echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;event_person=1&amp;person_event_up='.$data_listDb->event_order.'&amp;event_kind='.$data_listDb->event_kind.'#event_person_link"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="down"></a>';
		}
		else{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}

		//echo event_text($data_listDb->event_kind).'<br>';
		echo '<br>';

		if ($data_listDb->event_kind=='picture'){
			$thumb_prefix='';
			if (file_exists($path_prefix.$tree_pict_path.'thumb_'.$data_listDb->event_event)){ $thumb_prefix='thumb_'; }

			$extensions_check=substr($path_prefix.$tree_pict_path.$data_listDb->event_event,-3,3);
			if(strtolower($extensions_check)=="pdf") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'"><img src="'.CMS_ROOTPATH.'images/pdf.jpeg"></a>';
			}
			elseif(strtolower($extensions_check)=="doc" OR strtolower(substr($path_prefix.$tree_pict_path.$data_listDb->event_event,-4,4))=="docx") {   
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'"><img src="'.CMS_ROOTPATH.'images/msdoc.gif"></a>';
			}					
			// *** Show AVI Video file ***
			elseif($extensions_check=="avi") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
			}
			// *** Show WMV Video file ***
			elseif($extensions_check=="wmv") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
			}
			// *** Show MPG Video file ***
			elseif(strtolower($extensions_check)=="mpg") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
			}
			// *** Show MOV Video file ***
			elseif(strtolower($extensions_check)=="mov") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
			}
			// *** Show WMA Audio file ***
			elseif(strtolower($extensions_check)=="wma") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
			}	
			// *** Show WAV Audio file ***
			elseif(strtolower($extensions_check)=="wav") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
			}	
			// *** Show MP3 Audio file ***
			elseif(strtolower($extensions_check)=="mp3") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
			}	
			// *** Show MID Audio file ***
			elseif(strtolower($extensions_check)=="mid") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
			}	
			// *** Show RAM Audio file ***
			elseif(strtolower($extensions_check)=="ram") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
			}
			// *** Show RA Audio file ***
			elseif(strtolower($extensions_check)==".ra") {
				echo '<a href="'.$path_prefix.$tree_pict_path.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
			}						
			else{
				echo '<img src="'.$path_prefix.$tree_pict_path.$thumb_prefix.$data_listDb->event_event.'" width="100px">';
			}
		}

	echo '</td>';


	// *** Show name of event ***
	echo '<td style="border-right:0px;">'.event_text($data_listDb->event_kind).'</td>';

	// *** Witness and declaration persons ***
	if ($data_listDb->event_kind=='baptism_witness' OR $data_listDb->event_kind=='birth_declaration' OR
	$data_listDb->event_kind=='death_declaration' OR $data_listDb->event_kind=='burial_witness'){
		echo '<td style="border-left:0px;">';
		//witness_edit($data_listDb->event_event);
		witness_edit($data_listDb->event_event,'['.$data_listDb->event_id.']');
	}

	elseif ($data_listDb->event_kind=='picture'){
		// *** Show pull-down list pictures ***
		//echo '<td style="border-left:0px;"><select size="1" name="text_event">';
		echo '<td style="border-left:0px;"><select size="1" name="text_event['.$data_listDb->event_id.']">';
		echo '<option value=""></option>';
		for ($picture_nr=0; $picture_nr<$nr_pictures; $picture_nr++){
			$selected=''; if ($picture_array[$picture_nr]==$data_listDb->event_event){ $selected=' SELECTED'; }
			echo '<option value="'.$picture_array[$picture_nr].'"'.$selected.'>'.$picture_array[$picture_nr].'</option>';
		}
		echo '</select>';

		//echo ' <b>'.__('or').' upload (max: pic 2MB, media 49 MB):</b>';
		//echo ' <input type="file" name="photo_upload['.$data_listDb->event_id.']">';
	}

	elseif ($data_listDb->event_kind=='adoption'){
		echo '<td style="border-left:0px;"><select size="1" name="text_event['.$data_listDb->event_id.']">';
		echo '<option value="">'.__('* Select adoption parents *').'</option>';
		// *** Search for adoption parents ***
		$family_parents=mysql_query("SELECT * FROM ".$tree_prefix."family ORDER BY fam_gedcomnumber",$db);
		while($family_parentsDb=mysql_fetch_object($family_parents)){
			$parent_text='['.$family_parentsDb->fam_gedcomnumber.'] ';
			//*** Father ***
			if ($family_parentsDb->fam_man){
				$parent_text.=show_person($family_parentsDb->fam_man);
			}
			else{
				$parent_text=__('N.N.');
			}
			$parent_text.=' '.__('and').' ';

			//*** Mother ***
			if ($family_parentsDb->fam_woman){
				$parent_text.=show_person($family_parentsDb->fam_woman);
			}
			else{
				$parent_text.=__('N.N.');
			}
			$selected=''; if ($family_parentsDb->fam_gedcomnumber==$data_listDb->event_event) $selected=' SELECTED';
			echo '<option value="'.$family_parentsDb->fam_gedcomnumber.'"'.$selected.'>'.$parent_text.'</option>';
		}
		echo '</select>';
	}

	//person_colour_mark
	elseif ($data_listDb->event_kind=='person_colour_mark'){

		$person_colour_mark=$data_listDb->event_event;
		echo '<td style="border-left:0px;">';
		echo '<select class="fonts" size="1" name="text_event['.$data_listDb->event_id.']">';
			echo '<option value="0">'.__('Change colour mark by person').'</option>';
			$selected=''; if ($person_colour_mark=='1'){ $selected=' selected'; }
			echo '<option value="1" style="color:#FF0000;"'.$selected.'>'.__('Colour 1').'</option>';
			$selected=''; if ($person_colour_mark=='2'){ $selected=' selected'; }
			echo '<option value="2" style="color:#00FF00;"'.$selected.'>'.__('Colour 2').'</option>';
			$selected=''; if ($person_colour_mark=='3'){ $selected=' selected'; }
			echo '<option value="3" style="color:#0000FF;"'.$selected.'>'.__('Colour 3').'</option>';
			$selected=''; if ($person_colour_mark=='4'){ $selected=' selected'; }
			echo '<option value="4" style="color:#FF00FF;"'.$selected.'>'.__('Colour 4').'</option>';
			$selected=''; if ($person_colour_mark=='5'){ $selected=' selected'; }
			echo '<option value="5" style="color:#FFFF00;"'.$selected.'>'.__('Colour 5').'</option>';
			$selected=''; if ($person_colour_mark=='6'){ $selected=' selected'; }
			echo '<option value="6" style="color:#00FFFF;"'.$selected.'>'.__('Colour 6').'</option>';
			$selected=''; if ($person_colour_mark=='7'){ $selected=' selected'; }
			echo '<option value="7" style="color:#C0C0C0;"'.$selected.'>'.__('Colour 7').'</option>';
			$selected=''; if ($person_colour_mark=='8'){ $selected=' selected'; }
			echo '<option value="8" style="color:#800000;"'.$selected.'>'.__('Colour 8').'</option>';
			$selected=''; if ($person_colour_mark=='9'){ $selected=' selected'; }
			echo '<option value="9" style="color:#008000;"'.$selected.'>'.__('Colour 9').'</option>';
			$selected=''; if ($person_colour_mark=='10'){ $selected=' selected'; }
			echo '<option value="10" style="color:#000080;"'.$selected.'>'.__('Colour 10').'</option>';
			$selected=''; if ($person_colour_mark=='11'){ $selected=' selected'; }
			echo '<option value="11" style="color:#800080;"'.$selected.'>'.__('Colour 11').'</option>';
			$selected=''; if ($person_colour_mark=='12'){ $selected=' selected'; }
			echo '<option value="12" style="color:#A52A2A;"'.$selected.'>'.__('Colour 12').'</option>';
			$selected=''; if ($person_colour_mark=='13'){ $selected=' selected'; }
			echo '<option value="13" style="color:#008080;"'.$selected.'>'.__('Colour 13').'</option>';
			$selected=''; if ($person_colour_mark=='14'){ $selected=' selected'; }
			echo '<option value="14" style="color:#808080;"'.$selected.'>'.__('Colour 14').'</option>';
		echo '</select>';

// GRAYED-OUT and DISABLED!!!! UNDER CONSTRUCTION!!!!
echo '<span style="color:#6D7B8D;">';
		$check=''; //if (isset($pers_stillborn) AND $pers_stillborn=='y'){ $check=' checked'; }
		echo ' '.__('Also change:').' <input type="checkbox" name="pers_colour_desc['.$data_listDb->event_id.']" '.$check.' DISABLED> '.__('Descendants');
		echo '<input type="checkbox" name="pers_colour_anc" '.$check.' DISABLED> '.__('Ancestors');
		//echo ' <span style="color:#FF0000;">'.$pers_firstname.' '.$pers_prefix.' '.$pers_lastname.'</span>';
echo '</span>';

		$pers_colour='';
		if ($person_colour_mark=='1'){ $pers_colour='style="color:#FF0000;"'; }
		if ($person_colour_mark=='2'){ $pers_colour='style="color:#00FF00;"'; }
		if ($person_colour_mark=='3'){ $pers_colour='style="color:#0000FF;"'; }
		if ($person_colour_mark=='4'){ $pers_colour='style="color:#FF00FF;"'; }
		if ($person_colour_mark=='5'){ $pers_colour='style="color:#FFFF00;"'; }
		if ($person_colour_mark=='6'){ $pers_colour='style="color:#00FFFF;"'; }
		if ($person_colour_mark=='7'){ $pers_colour='style="color:#C0C0C0;"'; }
		if ($person_colour_mark=='8'){ $pers_colour='style="color:#800000;"'; }
		if ($person_colour_mark=='9'){ $pers_colour='style="color:#008000;"'; }
		if ($person_colour_mark=='10'){ $pers_colour='style="color:#000080;"'; }
		if ($person_colour_mark=='11'){ $pers_colour='style="color:#800080;"'; }
		if ($person_colour_mark=='12'){ $pers_colour='style="color:#A52A2A;"'; }
		if ($person_colour_mark=='13'){ $pers_colour='style="color:#008080;"'; }
		if ($person_colour_mark=='14'){ $pers_colour='style="color:#808080;"'; }
		echo ' <span '.$pers_colour.'>'.__('Selected colour').'</span>';
	}

	else{
		echo '<td style="border-left:0px;"><input type="text" name="text_event['.$data_listDb->event_id.']" value="'.$data_listDb->event_event.'" size="60">';
	}

	if ($data_listDb->event_kind=='nobility'){ echo ' '.__('e.g. Jhr., Jkvr.'); }
	elseif ($data_listDb->event_kind=='title'){ echo ' '.__('e.g. Prof., Dr.'); }
	elseif ($data_listDb->event_kind=='lordship'){
		echo ' '.__('e.g. Lord of Amsterdam');
	}


	// *** Select type of event ***
	if ($data_listDb->event_kind=='event'){
		echo ' <select size="1" name="event_gedcom['.$data_listDb->event_id.']" style="width: 150px">';
		event_option($data_listDb->event_gedcom,'EVEN');
		event_option($data_listDb->event_gedcom,'ARVL');
		event_option($data_listDb->event_gedcom,'BAPM');
		event_option($data_listDb->event_gedcom,'DPRT');
		event_option($data_listDb->event_gedcom,'LEGI');
		event_option($data_listDb->event_gedcom,'MILI');
		event_option($data_listDb->event_gedcom,'SLGL');
		event_option($data_listDb->event_gedcom,'TXPY');
		event_option($data_listDb->event_gedcom,'ADOP');
		event_option($data_listDb->event_gedcom,'_ADPF');
		event_option($data_listDb->event_gedcom,'_ADPM');
		event_option($data_listDb->event_gedcom,'BAPL');
		event_option($data_listDb->event_gedcom,'BARM');
		event_option($data_listDb->event_gedcom,'BASM');
		event_option($data_listDb->event_gedcom,'BLES');
		event_option($data_listDb->event_gedcom,'CENS');
		event_option($data_listDb->event_gedcom,'CHRA');
		event_option($data_listDb->event_gedcom,'CONF');
		event_option($data_listDb->event_gedcom,'CONL');
		event_option($data_listDb->event_gedcom,'EMIG');
		event_option($data_listDb->event_gedcom,'ENDL');
		event_option($data_listDb->event_gedcom,'FCOM');
		event_option($data_listDb->event_gedcom,'_FNRL');
		event_option($data_listDb->event_gedcom,'GRAD');
		event_option($data_listDb->event_gedcom,'IMMI');
		event_option($data_listDb->event_gedcom,'NATU');
		event_option($data_listDb->event_gedcom,'ORDN');
		event_option($data_listDb->event_gedcom,'PROB');
		event_option($data_listDb->event_gedcom,'RETI');
		event_option($data_listDb->event_gedcom,'SLGC');
		event_option($data_listDb->event_gedcom,'WILL');
		event_option($data_listDb->event_gedcom,'_YART');
		event_option($data_listDb->event_gedcom,'_INTE');
		event_option($data_listDb->event_gedcom,'_BRTM');
		event_option($data_listDb->event_gedcom,'_NMAR');
		event_option($data_listDb->event_gedcom,'NCHI');
		event_option($data_listDb->event_gedcom,'EDUC');
		event_option($data_listDb->event_gedcom,'NATI');
		event_option($data_listDb->event_gedcom,'CAST');
		event_option($data_listDb->event_gedcom,'AFN');
		event_option($data_listDb->event_gedcom,'SSN');
		event_option($data_listDb->event_gedcom,'IDNO');
		event_option($data_listDb->event_gedcom,'_HEIG');
		event_option($data_listDb->event_gedcom,'_WEIG');
		event_option($data_listDb->event_gedcom,'_EYEC');
		event_option($data_listDb->event_gedcom,'_HAIR');
		event_option($data_listDb->event_gedcom,'_MEDC');
		event_option($data_listDb->event_gedcom,'PROP');
		echo '</select>';
	}

	if ($data_listDb->event_kind=='name'){
		echo ' <select size="1" name="event_gedcom['.$data_listDb->event_id.']" style="width: 150px">';

		$event='_AKAN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_AKAN '.__('Also known as').'</option>';

		$event='NICK'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>NICK '.__('Nickname').'</option>';

		$event='_ALIA'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_ALIA '.__('alias name').'</option>';

		$event='_SHON'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_SHON '.__('Short name (for reports)').'</option>';

		$event='_ADPN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_ADPN '.__('Adopted name').'</option>';

		$event='_HEBN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_HEBN '.__('Hebrew name').'</option>';

		$event='_CENN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_CENN '.__('Census name').'</option>';

		$event='_MARN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_MARN '.__('Married name').'</option>';

		$event='_GERN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_GERN '.__('Nickname').'</option>';

		$event='_FARN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_FARN '.__('Farm name').'</option>';

		$event='_BIRN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_BIRN '.__('Birth name').'</option>';

		$event='_INDN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_INDN '.__('Indian name').'</option>';

		$event='_FKAN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_FKAN '.__('Formal name').'</option>';

		$event='_CURN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_CURN '.__('Current name').'</option>';

		$event='_SLDN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_SLDN '.__('Soldier name').'</option>';

		$event='_RELN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_RELN '.__('Religious name').'</option>';

		$event='_OTHN'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_OTHN '.__('Other name').'</option>';

		$event='_FRKA'; $selected=''; if ($data_listDb->event_gedcom==$event){ $selected=' SELECTED'; }
		echo '<option value="'.$event.'"'.$selected.'>_FRKA '.__('Formerly known as').'</option>';

		echo '</select>';
	}
	//echo '<td><input type="submit" name="submit" title="submit" value="'.__('Save').'"></td></td>';
	echo '<td>';
		// *** Source by event ***
		// *** Calculate and show nr. of sources ***
		$connect_qry="SELECT *
			FROM ".$tree_prefix."connections
			WHERE connect_kind='person' AND connect_sub_kind='event_source'
			AND connect_connect_id='".$data_listDb->event_id."'";
		$connect_sql=mysql_query($connect_qry,$db);
		echo "&nbsp;<a href=\"#event_person_link\" onClick=\"window.open('index.php?page=editor_sources&event_person=1&connect_kind=person&connect_sub_kind=person_event_source&connect_connect_id=".$data_listDb->event_id."', '','width=800,height=500')\">".__('source');
		echo ' ['.mysql_num_rows($connect_sql).']</a>';
	echo '</td>';

	echo '</tr>';


	// *** Date and place line ***
	echo '<tr'.$change_bg_colour.' style="display:none;" id="row'.$data_listDb->event_id.'00" name="row'.$data_listDb->event_id.'00"
><td></td>';
		echo '<td style="border-right:0px;">'.__('date').'</td>';
		echo '<td style="border-left:0px;">'.$editor_cls->date_show($data_listDb->event_date,'event_date',"[$data_listDb->event_id]").' '.__('place').' <input type="text" name="event_place['.$data_listDb->event_id.']" value="'.$data_listDb->event_place.'" size="'.$field_date.'">';
		echo '</td><td>';
	echo '</td></tr>';


	// *** Text by event ***
	if ($data_listDb->event_kind!='profession'){
		echo '<tr'.$change_bg_colour.' style="display:none;" id="row'.$data_listDb->event_id.'00" name="row'.$data_listDb->event_id.'00"
><td></td>';
		echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="event_text['.$data_listDb->event_id.']" '.$field_text.'>'.
		$editor_cls->text_show($data_listDb->event_text).'</textarea>';
		echo '</td><td></td></tr>';
	}

	if ($change_bg_colour!=''){ $change_bg_colour=''; }
		else{ $change_bg_colour=' class="humo_color"'; }

}
echo '</form>';

// *** Add event ***
echo '<form method="POST" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<input type="hidden" name="event_person" value="event_person">';

	echo '<tr bgcolor="#CCFFFF"><td></td><td style="border-right:0px;">'.__('Add event').'</td><td style="border-left:0px;">';
		echo '<select size="1" name="event_kind">';
		echo '<option value="picture">'.__('Picture/ Media').'</option>';
		echo '<option value="profession">'.__('Profession').'</option>';
		echo '<option value="event">'.__('Event').'</option>';
		echo '<option value="birth_declaration">'.__('Birth Declaration').'</option>';
		echo '<option value="baptism_witness">'.__('Baptism Witness').'</option>';
		echo '<option value="death_declaration">'.__('Death Declaration').'</option>';
		echo '<option value="burial_witness">'.__('Burial Witness').'</option>';
		echo '<option value="name">'.__('Name').'</option>';
		echo '<option value="nobility">'.__('Title of Nobility').'</option>';
		echo '<option value="title">'.__('Title').'</option>';
		echo '<option value="adoption">'.__('Adoption').'</option>';
		echo '<option value="lordship">'.__('Title of Lordship').'</option>';
		echo '<option value="URL">'.__('URL/ Internet link').'</option>';
		echo '<option value="person_colour_mark">'.__('Colour mark by person').'</option>';
		echo '</select>';
	echo'</td><td><input type="Submit" name="person_event_add" value="'.__('Add').'"></td><tr>';
echo '</form>';

//echo '</table><br>'."\n";
?>