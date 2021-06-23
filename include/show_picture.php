<?php
// *** Function to show media by person or by marriage ***
// *** Updated feb 2013, aug 2015. ***
function show_media($event_connect_kind,$event_connect_id){
	global $dbh, $db_functions, $tree_id, $user, $dataDb, $uri_path;
	global $sect, $screen_mode; // *** RTF Export ***
	global $picture_presentation;

	$templ_person = array(); // local version
	$process_text='';
	$media_nr=0;

	// *** Pictures/ media ***
	if ($user['group_pictures']=='j' AND $picture_presentation!='hide'){
		// In joomla relative path is relative to joomla main folder, NOT HuMo-genealogy main folder. Therefore use the path entered as-is, without ROOTPATH.
		//$tree_pict_path=CMS_ROOTPATH.$dataDb->tree_pict_path;
		$tree_pict_path=$dataDb->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';

		// *** Standard connected media by person and family ***
		$picture_qry=$dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."'
			AND event_connect_kind='".safe_text_db($event_connect_kind)."'
			AND event_connect_id='".safe_text_db($event_connect_id)."'
			AND LEFT(event_kind,7)='picture'
			ORDER BY event_kind, event_order");
		while($pictureDb=$picture_qry->fetch(PDO::FETCH_OBJ)){
			$media_nr++;
			$media_event_id[$media_nr]=$pictureDb->event_id;
			$media_event_event[$media_nr]=$pictureDb->event_event;
			$media_event_date[$media_nr]=$pictureDb->event_date;
			$media_event_text[$media_nr]=$pictureDb->event_text;
			// *** Remove last seperator ***
			if(substr(rtrim($media_event_text[$media_nr]),-1)=="|") $media_event_text[$media_nr] = substr($media_event_text[$media_nr],0,-1);
			//$media_event_source[$media_nr]=$pictureDb->event_source;
		}

		// *** Search for all external connected objects by a person or a family ***
		if ($event_connect_kind=='person'){
			$connect_sql = $db_functions->get_connections_connect_id('person','pers_object',$event_connect_id);
		}
		elseif ($event_connect_kind=='family'){
			$connect_sql = $db_functions->get_connections_connect_id('family','fam_object',$event_connect_id);
		}
		if ($event_connect_kind=='person' OR $event_connect_kind=='family'){
			foreach ($connect_sql as $connectDb){
				$picture_qry=$dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."'
					AND event_gedcomnr='".safe_text_db($connectDb->connect_source_id)."' AND event_kind='object'
					ORDER BY event_order");
				while($pictureDb=$picture_qry->fetch(PDO::FETCH_OBJ)){
					$media_nr++;
					$media_event_id[$media_nr]=$pictureDb->event_id;
					$media_event_event[$media_nr]=$pictureDb->event_event;
					$media_event_date[$media_nr]=$pictureDb->event_date;
					$media_event_text[$media_nr]=$pictureDb->event_text;
					// *** Remove last seperator ***
					if(substr(rtrim($media_event_text[$media_nr]),-1)=="|") $media_event_text[$media_nr] = substr($media_event_text[$media_nr],0,-1);
					//$media_event_source[$media_nr]=$pictureDb->event_source;
				}
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
			
			// in case subfolders are made for photobook categories and this was not already set in $picture_path,  look there
			// (if the $picture_path is already set with subfolder this anyway gives false and so the $picture_path given will work)
			$temp_path = $tree_pict_path; // store original so we can reset after using for subfolder path for this picture.
			$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
			if($temp->rowCount()) {   // there is a category table 
				$catg = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
				if($catg->rowCount()) {
					while($catDb = $catg->fetch(PDO::FETCH_OBJ)) {
						if(substr($event_event,0,3)==$catDb->photocat_prefix AND is_dir($tree_pict_path.'/'.substr($event_event,0,2)))  {  // there is a subfolder of this prefix
							$tree_pict_path .= substr($event_event,0,2).'/';  // look in that subfolder
						}
					}
				}
			}

			// *** In some cases the picture name must be converted to lower case ***
			if (file_exists($tree_pict_path.strtolower($event_event))){
				$event_event=strtolower($event_event); }

			// *** Show PDF file ***
			if(strtolower(substr($tree_pict_path.$event_event,-3,3))=="pdf") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'"><img src="'.$picpath.'/images/pdf.jpeg" alt="PDF"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'"><img src="'.$picpath.'images/pdf.jpeg" alt="PDF"></a>';
			}
			// *** Show DOC file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="doc" OR substr($tree_pict_path.$event_event,-4,4)=="docx")
			{
				//$picture='<a href="'.$tree_pict_path.$event_event.'"><img src="'.$picpath.'/images/msdoc.gif" alt="DOC"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'"><img src="'.$picpath.'images/msdoc.gif" alt="DOC"></a>';
			}
			// *** Show AVI Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="avi") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/video-file.png" alt="AVI"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/video-file.png" alt="AVI"></a>';
			}
			// *** Show WMV Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="wmv") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/video-file.png" alt="WMV"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/video-file.png" alt="WMV"></a>';
			}
			// *** Show MPG Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mpg") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/video-file.png" alt="MPG"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/video-file.png" alt="MPG"></a>';
			}
			// *** Show MP4 Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mp4") {
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/video-file.png" alt="MP4"></a>';
			}
			// *** Show MOV Video file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mov") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/video-file.png" alt="MOV"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/video-file.png" alt="MOV"></a>';
			}
			// *** Show WMA Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="wma") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif" alt="WMA"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/audio.gif" alt="WMA"></a>';
			}
			// *** Show MP3 Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mp3") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="MP3"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/audio.gif"" alt="MP3"></a>';
			}
			// *** Show WAV Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="wav") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="WAV"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/audio.gif"" alt="WAV"></a>';
			}
			// *** Show MID Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="mid") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="MID"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/audio.gif"" alt="MID"></a>';
			}
			// *** Show RAM Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-3,3))=="ram") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="RAM"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/audio.gif"" alt="RAM"></a>';
			}
			// *** Show RA Audio file ***
			elseif(strtolower(substr($tree_pict_path.$event_event,-2,2))=="ra") {
				//$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'/images/audio.gif"" alt="RA"></a>';
				$picture='<a href="'.$tree_pict_path.$event_event.'" target="_blank"><img src="'.$picpath.'images/audio.gif"" alt="RA"></a>';
			}
			else{
				// *** Show photo using the lightbox effect ***
				$picture_array=show_picture($tree_pict_path,$event_event,'',120);
				// *** lightbox can't handle brackets etc so encode it. ("urlencode" doesn't work since it changes spaces to +, so we use rawurlencode)
				// *** But: reverse change of / character (if sub folders are used) ***
				//$picture_array['picture'] = rawurlencode($picture_array['picture']);
				$picture_array['picture'] = str_ireplace("%2F","/",rawurlencode($picture_array['picture']));
				$line_pos = strpos($media_event_text[$i],"|");
				//$title_txt=''; if($line_pos !== false) $title_txt = substr($media_event_text[$i],0,$line_pos);
				$title_txt=$media_event_text[$i]; if($line_pos !== false) $title_txt = substr($media_event_text[$i],0,$line_pos);

				$picture='<a href="'.$picture_array['path'].$picture_array['picture'].'" rel="lightbox" title="'.str_replace("&", "&amp;", $title_txt).'">';
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
				// *** Show source by picture ***
				$source='';
				if ($event_connect_kind=='person'){
					$source=show_sources2("person","pers_event_source",$media_event_id[$i]);
				}
				else{
					$source=show_sources2("family","fam_event_source",$media_event_id[$i]);
				}
				if ($source) $picture_text.=$source;

				$process_text.='<div class="photo">';
					$process_text.=$picture;
					if (isset($picture_array['picture']) AND $picture_array['picture']=='missing-image.jpg') $picture_text.='<br><b>'.__('Missing image').':<br>'.$tree_pict_path.$event_event.'</b>';
					if(isset($picture_text)) {$process_text.='<div class="phototext">'.$picture_text.'</div>';}
				$process_text.= '</div>'."\n";
			}
			// reset path back to original in case was used for subfolder
			$tree_pict_path = $temp_path;
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
	global $dbh;
	// in case subfolders are made for photobook categories and this was not already set in $picture_path,  look there
	// in cases where the $picture_path is already set with subfolder this anyway gives false and so the $picture_path gives will work
	$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
	if($temp->rowCount()) {  // there is a category table 
		$cat1 = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
		if($cat1->rowCount()) { 
			while($catDb = $cat1->fetch(PDO::FETCH_OBJ)) {  
				if(substr($picture_org,0,3)==$catDb->photocat_prefix AND is_dir($picture_path.'/'.substr($picture_org,0,2)))  {  // there is a subfolder of this prefix
					$picture_path .= substr($picture_org,0,2).'/';  // look in that subfolder
				}
			}
		}
	}
	
	$picture["path"]=$picture_path; // *** Standard picture path. Will be overwritten if picture is removed ***
	$picture["picture"]=$picture_org;
	$found_picture=false; // *** Check if picture still exists ***

	// *** In some cases the picture name must be converted to lower case ***
	if (file_exists($picture["path"].strtolower($picture['picture']))){
		$found_picture=true;
		$picture['picture']=strtolower($picture['picture']);
	}
	// *** Picture ***
	if (file_exists($picture["path"].$picture['picture'])){
		$found_picture=true;
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
		/*
		// *** Change width and height ***
		$factor=$height/$pict_height;
		$picture['width']=floor($width/$factor);

		// *** If picture is too width, resize it ***
		if ($picture['width']>$pict_width){
			$factor=$width/$pict_width;
			$picture['height']=floor($height/$factor);
		}
		*/
		if ($width > $height){
			// *** Width picture: change width and height ***
			$factor=$width/$pict_width;
			$picture['width']=floor($width/$factor);
		}
		else{
			// *** High picture ***
			$factor=$height/$pict_height;
			$picture['width']=floor($width/$factor);
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