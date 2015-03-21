<?php
// *** Function to show media by person or by marriage ***
// *** Updated feb 2013. ***
function show_media($personDb,$marriageDb){
	global $dbh, $tree_id, $user, $dataDb, $tree_prefix_quoted, $uri_path;
	global $sect, $screen_mode; // *** RTF Export ***
	global $picture_presentation;

	$templ_person = array(); // local version
	$process_text='';
	$media_nr=0;

	// *** Pictures/ media ***
	//if ($user['group_pictures']=='j'){
	if ($user['group_pictures']=='j' AND $picture_presentation!='hide'){
		//$tree_pict_path=CMS_ROOTPATH.$dataDb->tree_pict_path;
		$tree_pict_path=$dataDb->tree_pict_path;
		// in joomla relative path is relative to joomla main folder, NOT HuMo-gen main folder. Therefore use the path entered as-is, without ROOTPATH.

		// *** Standard connected media by person and family ***
		if ($personDb!=''){
			//$picture_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			$picture_qry=$dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."'
				AND event_person_id='".$personDb->pers_gedcomnumber."' AND LEFT(event_kind,7)='picture'
				ORDER BY event_kind, event_order");
		}
		else{
			//$picture_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			$picture_qry=$dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."'
				AND event_family_id='".$marriageDb->fam_gedcomnumber."' AND event_kind='picture'
				ORDER BY event_order");
		}
		while($pictureDb=$picture_qry->fetch(PDO::FETCH_OBJ)){
			$media_nr++;
			$media_event_id[$media_nr]=$pictureDb->event_id;
			$media_event_event[$media_nr]=$pictureDb->event_event;
			$media_event_date[$media_nr]=$pictureDb->event_date;
			$media_event_text[$media_nr]=$pictureDb->event_text;
			//$media_event_source[$media_nr]=$pictureDb->event_source;
		}

		// *** Search for all external connected objects by a person or a family ***
		if ($personDb!=''){
			//$connect_qry="SELECT * FROM ".$tree_prefix_quoted."connections
			$connect_qry="SELECT * FROM humo_connections
				WHERE connect_tree_id='".$tree_id."'
				AND connect_sub_kind='pers_object'
				AND connect_connect_id='".$personDb->pers_gedcomnumber."'
				ORDER BY connect_order";
		}
		else{
			//$connect_qry="SELECT * FROM ".$tree_prefix_quoted."connections
			$connect_qry="SELECT * FROM humo_connections
				WHERE connect_tree_id='".$tree_id."'
				AND connect_sub_kind='fam_object'
				AND connect_connect_id='".$marriageDb->fam_gedcomnumber."'
				ORDER BY connect_order";
		}
		$connect_sql=$dbh->query($connect_qry);
		while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
			//$picture_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			$picture_qry=$dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."'
				AND event_gedcomnr='".$connectDb->connect_source_id."' AND event_kind='object'
				ORDER BY event_order");
			while($pictureDb=$picture_qry->fetch(PDO::FETCH_OBJ)){
				$media_nr++;
				$media_event_id[$media_nr]=$pictureDb->event_id;
				$media_event_event[$media_nr]=$pictureDb->event_event;
				$media_event_date[$media_nr]=$pictureDb->event_date;
				$media_event_text[$media_nr]=$pictureDb->event_text;
				//$media_event_source[$media_nr]=$pictureDb->event_source;
			}
		}


		// ******************
		// *** Show media ***
		// ******************
		if ($media_nr > 0){ $process_text.='<br>'; }

		if(CMS_SPECIFIC=="Joomla") {
			$picpath=CMS_ROOTPATH;
		}
		else {
			$picpath=$uri_path;
		}

		if($screen_mode=="RTF") { $process_text .= "\n"; }

		for ($i=1; $i<($media_nr+1); $i++) {
			// *** If possible show a thumb ***

			// *** Don't use entities in a picture ***
			//$event_event = html_entity_decode($pictureDb->event_event, ENT_NOQUOTES, 'ISO-8859-15');
			$event_event=$media_event_event[$i];

			// *** In some cases the picture name must be converted to lower case ***
			if (file_exists($tree_pict_path.strtolower($event_event))){
				$event_event=strtolower($event_event); }

			// *** Show PDF file ***
			if(strtolower(substr($tree_pict_path.$event_event,-3,3))=="pdf") {
				$picture='<a href="'.$tree_pict_path.$event_event.'"><img src="'.$picpath.'/images/pdf.jpeg" alt="PDF"></a>';
			}
			// *** Show DOC file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="doc" OR substr($tree_pict_path.$event_event,-4,4)=="docx") {
				$picture='<a href="'.$tree_pict_path.$event_event.'"><img src="'.$picpath.'/images/msdoc.gif" alt="DOC"></a>';
			}
			// *** Show AVI Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="avi") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/video-file.png" alt="AVI"></a>';
			}
			// *** Show WMV Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="wmv") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/video-file.png" alt="WMV"></a>';
			}
			// *** Show MPG Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mpg") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/video-file.png" alt="MPG"></a>';
			}
			// *** Show MOV Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mov") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/video-file.png" alt="MOV"></a>';
			}
			// *** Show WMA Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="wma") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif" alt="WMA"></a>';
			}
			// *** Show MP3 Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mp3") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="MP3"></a>';
			}
			// *** Show WAV Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="wav") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="WAV"></a>';
			}
			// *** Show MID Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mid") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="MID"></a>';
			}
			// *** Show RAM Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="ram") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="RAM"></a>';
			}
			// *** Show RA Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-2,2))=="ra") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="RA"></a>';
			}
			else{
				// *** Show photo using the lightbox effect ***
				$picture_array=show_picture($tree_pict_path,$event_event,'',120);
				$picture='<a href="'.$picture_array['path'].$picture_array['picture'].'" rel="lightbox" title="'.str_replace("&", "&amp;", $media_event_text[$i]).'">';
				$picture.='<img src="'.$picture_array['path'].$picture_array['thumb'].$picture_array['picture'].'" height="'.$picture_array['height'].'" alt="'.$event_event.'"></a>';

				//$templ_person["pic_path".$i]=$tree_pict_path."thumb_".$event_event; //for the time being pdf only with thumbs
				$templ_person["pic_path".$i]=$picture_array['path']."thumb_".$picture_array['picture']; //for the time being pdf only with thumbs
				// *** Remove spaces ***
				$templ_person["pic_path".$i]=trim($templ_person["pic_path".$i]);
			}

			// *** Show picture date ***
			$picture_date='';
			if ($media_event_date[$i]){
				if ($screen_mode!='RTF'){ $picture_date=' '.date_place($media_event_date[$i],'').' '; } // default, there is no place
				$templ_person["pic_text".$i]=date_place($media_event_date[$i],'');
			}

			// *** Show text by picture of little space ***
			$picture_text='';
			if (isset($media_event_text[$i]) AND $media_event_text[$i]){
				if ($screen_mode!='RTF'){$picture_text=$picture_date.' '.str_replace("&", "&amp;", $media_event_text[$i]);}
				if(isset($templ_person["pic_text".$i])){ $templ_person["pic_text".$i].=' '.$media_event_text[$i];}
					else {$templ_person["pic_text".$i]=' '.$media_event_text[$i];}
			}

			if ($screen_mode!='RTF'){
				$source=show_sources2("person","pers_event_source",$media_event_id[$i]);
				if ($source) $picture_text.=$source;

				$process_text.='<div class="photo">';
					$process_text.=$picture;
					if(isset($picture_text)) {$process_text.='<div class="phototext">'.$picture_text.'</div>';}
				$process_text.= '</div>'."\n";
			}

		}
		if ($media_nr > 0){
			$process_text.='<br clear="All">';
			$templ_person["got_pics"]=1;
		}
	}
	//return $process_text;
	$result[0] = $process_text;
	$result[1] = $templ_person; // local version with pic data
	return $result;
}

// *** Function to show a picture in several places ***
// *** Made by Huub Mons sept. 2011/ update aug. 2014 ***
// Example:
// $picture=show_picture($tree_pict_path,$pictureDb->event_event,'',120);
// $popup.='<img src="'.$picture['path'].$picture['thumb'].$picture['picture'].'" style="margin-left:50px; margin-top:5px;" alt="'.$pictureDb->event_text.'" height="'.$picture['height'].'">';

function show_picture($picture_path,$picture_org,$pict_width='',$pict_height=''){
	$picture["picture"]=$picture_org;
	$picture["path"]=$picture_path; // *** Standard picture path. Will be overwritten if picture is removed ***
	$found_picture=false; // *** Check if picture still exists ***

	// *** In some cases the picture name must be converted to lower case ***
	if (file_exists($picture["path"].strtolower($picture['picture']))){
		$found_picture=true;
		$picture['picture']=strtolower($picture['picture']);
	}

	$picture['thumb']='';
	// *** Lowercase thumbnail ***
	if (file_exists($picture["path"].'thumb_'.strtolower($picture['picture']))){
		$found_picture=true;
		$picture['thumb']='thumb_';
		$picture['picture']=strtolower($picture['picture']);
	}
	// *** Thumbnail ***
	if (file_exists($picture["path"].'thumb_'.$picture['picture'])){
		$found_picture=true;
		$picture['thumb']='thumb_';
	}

	// *** No picture selected yet (in editor) ***
	if (!$picture['picture']){
		$picture['path']='images/';
		$picture['thumb']='thumb_';
		$picture['picture']='missing-image.jpg';
	}

	if (!$found_picture){
		$picture['path']='images/';
		$picture['thumb']='thumb_';
		$picture['picture']='missing-image.jpg';
	}

	// *** If photo is too wide, correct the size ***
	@list($width, $height) = getimagesize($picture["path"].$picture['thumb'].$picture['picture']);

	if ($pict_width>0 AND $pict_height>0){
		// *** Change width and height ***
		$factor=$height/$pict_height;
		$picture['width']=$width/$factor;

		// *** If picture is too width, resize it ***
		if ($picture['width']>$pict_width){
			$factor=$width/$pict_width;
			$picture['height']=$height/$factor;
		}
	}
	elseif ($pict_width>0){
		// *** Change width ***
		if ($width>$pict_width){ $width=190; }
		$picture['width']=floor($width);
	}
	elseif ($pict_height>0){
		// *** Change height ***
		if ($height>$pict_height){ $height=120; }
		$picture['height']=floor($height);
	}

	return $picture;
}
?>