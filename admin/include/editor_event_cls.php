<?php
class editor_event_cls{

// *** Encode entire array (for picture array searches) ***
function utf8ize($d) {
	foreach($d as $key => $value) {
		$d[$key] = utf8_encode($value);
	}
	return $d;
}

// *** Show event_kind text ***
function event_text($event_kind){
	global $language;

	if ($event_kind=='picture') $event_text=__('Picture/ Media');
	elseif ($event_kind=='profession') $event_text=__('Profession');
	elseif ($event_kind=='event') $event_text=__('Event');
	elseif ($event_kind=='birth_declaration') $event_text=__('birth declaration');
	elseif ($event_kind=='baptism_witness') $event_text=__('baptism witness');
	elseif ($event_kind=='death_declaration') $event_text=__('death declaration');
	elseif ($event_kind=='burial_witness') $event_text=__('burial witness');
	elseif ($event_kind=='name') $event_text=__('Name');
	elseif ($event_kind=='NPFX') $event_text=__('Prefix');
	elseif ($event_kind=='NSFX') $event_text=__('Suffix');
	elseif ($event_kind=='nobility') $event_text=__('Title of Nobility');
	elseif ($event_kind=='title') $event_text=__('Title');
	elseif ($event_kind=='adoption') $event_text=__('Adoption');
	elseif ($event_kind=='lordship') $event_text=__('Title of Lordship');
	elseif ($event_kind=='URL') $event_text=__('URL/ Internet link');
	elseif ($event_kind=='person_colour_mark') $event_text=__('Colour mark by person');
	elseif ($event_kind=='marriage_witness') $event_text= __('marriage witness');
	elseif ($event_kind=='marriage_witness_rel') $event_text= __('marriage witness (religious)');
	elseif ($event_kind=='source_picture') $event_text=__('Picture/ Media');
	elseif ($event_kind=='religion') $event_text= __('Religion');
	else $event_text=ucfirst($event_kind);
	return $event_text;
}

// *** Hide or show lines for editing, using <span> ***
function hide_show_start($data_listDb, $alternative_text=''){
	// *** Use hideshow to show and hide the editor lines ***
	$text='';
	$hideshow='9000'.$data_listDb->event_id;
	$display=' display:none;';
	$event_event=$data_listDb->event_event;
	if ($data_listDb->event_event==''){
		//$event_event=__('EMPTY LINE');
		$display='';
	}
	if ($alternative_text) $event_event=$alternative_text;

	// *** Also show date and place ***
	//if ($data_listDb->event_date) $event_event.=', '.date_place($data_listDb->event_date,$data_listDb->event_place);
	if ($data_listDb->event_date) $event_event.=', '.hideshow_date_place($data_listDb->event_date,$data_listDb->event_place);

	if ($event_event OR $data_listDb->event_text){
		$text.='<span class="hideshowlink" onclick="hideShow('.$hideshow.');">'.$event_event;
			if ($data_listDb->event_text) $text.=' <img src="images/text.png" height="16px">';
		$text.='</span><br>';
	}

	$text.='<span class="humo row'.$hideshow.'" style="margin-left:0px;'.$display.'">';
	return $text;
}

// *** Show events ***
// *** REMARK: queries can be found in editor_inc.php! ***
function show_event($event_connect_kind,$event_connect_id,$event_kind){
	global $dbh, $tree_id, $page, $field_date, $field_place, $field_text, $field_text_medium, $joomlastring;
	global $editor_cls, $path_prefix, $tree_pict_path, $humo_option,$field_popup;
	global $db_functions;

	$text='';

	$picture_array = Array();
	// *** Picture list for selecting pictures ***
	$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id='".$tree_id."'");
	$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
	$tree_pict_path=$dataDb->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';
	$dir=$path_prefix.$tree_pict_path;
	if (file_exists($dir)){
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if (substr($filename,0,6)!='thumb_' AND $filename!='.' AND $filename!='..' AND !is_dir($dir.$filename)){
				$picture_array[]=$filename;
			}
		}
		closedir($dh);
	}
	@usort($picture_array,'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11

	$is_cat=false; // flags there are category files (for use later on)
	$picture_array2 = Array(); // declare, otherwise if not used gives error
	// if subfolders exist for category files, list those too
	$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
	if($temp->rowCount()) { // there is a category table
		$catg = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
		if($catg->rowCount()) {
			while($catDb = $catg->fetch(PDO::FETCH_OBJ)) { 
				if(is_dir($dir.substr($catDb->photocat_prefix,0,2)))  {  // there is a subfolder for this prefix
					$dh  = opendir($dir.substr($catDb->photocat_prefix,0,2));
					while (false !== ($filename = readdir($dh))) {
						if (substr($filename,0,6)!='thumb_' AND $filename!='.' AND $filename!='..'){
							$picture_array2[]=$filename;
							$is_cat=true;
						}
					}
					closedir($dh);
				}
			}
		}
	}
	// *** Order pictures by alphabet ***
	@usort($picture_array2,'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11
	$picture_array = array_merge($picture_array,$picture_array2);
	//@sort($picture_array);  
	//@usort($picture_array,'strnatcasecmp');   // sorts case insensitive and with digits as numbers: pic1, pic3, pic11
	$nr_pictures=count($picture_array);

	// *** Change line colour ***
	$change_bg_colour=' class="humo_color3"';

	// 2021: No longer in use (only needed if source is edited in a pop-up screen)?
	//$event_group='event_person=1';
	if ($event_connect_kind=='person') $event_group='event_person=1';
	if ($event_connect_kind=='family') $event_group='event_family=1';
	if ($event_connect_kind=='source') $event_group='event_source=1';

	// *** Show all events EXCEPT for events already processed by person data (profession etc.) ***

	// Don't show Brit Mila and/or Bar Mitzva if user set them to be displayed among person data
	$hebtext='';
	//if($humo_option['admin_brit']=="y") {  $hebtext .= " AND event_gedcom!='_BRTM'  "; }
	//if($humo_option['admin_barm']=="y") {  $hebtext .= " AND event_gedcom!='BARM' AND event_gedcom!='BASM' "; }
	if($humo_option['admin_brit']=="y") {  $hebtext .= " AND (event_gedcom!='_BRTM'  OR event_gedcom IS NULL) "; }
	if($humo_option['admin_barm']=="y") {  $hebtext .= " AND ((event_gedcom!='BARM' AND event_gedcom!='BASM') OR event_gedcom IS NULL) "; } 

	if ($event_kind=='person'){
		/*
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."'
			AND event_kind!='name'
			AND event_kind!='NPFX'
			AND event_kind!='NSFX'
			AND event_kind!='nobility'
			AND event_kind!='title'
			AND event_kind!='lordship'
			AND event_kind!='birth_declaration'
			AND event_kind!='baptism_witness'
			AND event_kind!='death_declaration'
			AND event_kind!='burial_witness'
			AND event_kind!='profession'
			AND event_kind!='religion'
			AND event_kind!='picture' ".$hebtext."
			ORDER BY event_kind, event_order";
		*/
		// *** Filter several events, allready shown in seperate lines in editor ***
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."'
			AND event_kind NOT IN ('name','NPFX','NSFX','nobility','title','lordship','birth_declaration','baptism_witness',
				'death_declaration','burial_witness','profession','religion','picture')
			".$hebtext."
			ORDER BY event_kind, event_order";
	}
	elseif ($event_kind=='name'){
		$hebclause=""; if($humo_option['admin_hebname'] == 'y') {  $hebclause=" AND event_gedcom!='_HEBN' "; }
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='name' ".$hebclause."ORDER BY event_order";
	}
	elseif ($event_kind=='NPFX'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='NPFX' ORDER BY event_order";
	}
	elseif ($event_kind=='NSFX'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='NSFX' ORDER BY event_order";
	}
	elseif ($event_kind=='nobility'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='nobility' ORDER BY event_order";
	}
	elseif ($event_kind=='title'){
		$qry="SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='title' ORDER BY event_order";
	}
	elseif ($event_kind=='lordship'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='lordship' ORDER BY event_order";
	}
	elseif ($event_kind=='birth_declaration'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='birth_declaration' ORDER BY event_order";
	}
	elseif ($event_kind=='baptism_witness'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='baptism_witness' ORDER BY event_order";
	}
	elseif ($event_kind=='death_declaration'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='death_declaration' ORDER BY event_order";
	}
	elseif ($event_kind=='burial_witness'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='burial_witness' ORDER BY event_order";
	}
	elseif ($event_kind=='profession'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='profession' ORDER BY event_order";
	}
	elseif ($event_kind=='religion'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='religion' ORDER BY event_order";
	}
	/*
	elseif ($event_kind=='picture'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND
			event_kind='picture' ORDER BY event_order";
	}
	*/
	elseif ($event_kind=='picture'){
		$search_picture = ""; $searchpic="";
		if(isset($_POST['searchpic'])) { $search_picture = $_POST['searchpic']; }
		if($search_picture != "") { $searchpic = " AND event_event LIKE '%".$search_picture."%' ";}
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND
			event_kind='picture' ".$searchpic." ORDER BY event_order";
	}

	elseif ($event_kind=='family'){
		$qry="SELECT * FROM humo_events 
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='family' AND event_connect_id='".$event_connect_id."'
			AND event_kind!='marriage_witness'
			AND event_kind!='marriage_witness_rel'
			AND event_kind!='picture'
			ORDER BY event_kind, event_order";
	}
	elseif ($event_kind=='marriage_witness'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='family' AND event_connect_id='".$event_connect_id."' AND event_kind='marriage_witness' ORDER BY event_kind, event_order";
	}
	elseif ($event_kind=='marriage_witness_rel'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='family' AND event_connect_id='".$event_connect_id."' AND event_kind='marriage_witness_rel' ORDER BY event_kind, event_order";
	}
	elseif ($event_kind=='marriage_picture'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='family' AND event_connect_id='".$event_connect_id."' AND event_kind='picture' ORDER BY event_order";
	}
	elseif ($event_kind=='source_picture'){
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_connect_kind='source' AND event_connect_id='".$event_connect_id."' AND event_kind='picture' ORDER BY event_order";
	}

	$data_list_qry=$dbh->query($qry);


	// *** If there are events, also show add line ***
	// Doesn't work:
	//$show_event_add=' style="display:none;"';
	//if ($data_list_qry) $show_event_add='';

	//$show_event_add=' style="display:none;"';
	//$count=$data_list_qry->rowCount();
	//if ($count>0) $show_event_add='';

	$show_event_add=false;
	$count=$data_list_qry->rowCount();
	if ($count>0) $show_event_add=true;


	// *** Show events by person ***
	if ($event_kind=='person'){
		//$text.='<tr><td style="border-right:0px;"><a name="event_person_link"></a><a href="#event_person_link" onclick="hideShow(51);"><span id="hideshowlink51">'.__('[+]').'</span></a> '.__('Events').'</td>';
		$link='event_person_link';
		$text.='<tr class="table_header_large"><td style="border-right:0px;"><a name="event_person_link"></a>'.__('Events').'</td>';
		$text.='<td style="border-right:0px;">';
			//$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_event">'.__('Add').'</a>';
		$text.='</td><td style="border-left:0px;">';

			// Don't show Brit Mila and/or Bar Mitzva in event list if user set them to be displayed among person data
			//$hebtext='';
			//if($humo_option['admin_brit']=="y") {  $hebtext .= " AND (event_gedcom!='_BRTM'  OR event_gedcom IS NULL) "; }
			//if($humo_option['admin_barm']=="y") {  $hebtext .= " AND ((event_gedcom!='BARM' AND event_gedcom!='BASM') OR event_gedcom IS NULL) "; } 
			//$count_event=$dbh->query("SELECT * FROM humo_events
			//	WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."'
			//	AND event_kind!='name'
			//	AND event_kind!='NPFX'
			//	AND event_kind!='NSFX'
			//	AND event_kind!='nobility'
			//	AND event_kind!='title'
			//	AND event_kind!='lordship'
			//	AND event_kind!='birth_declaration'
			//	AND event_kind!='baptism_witness'
			//	AND event_kind!='death_declaration'
			//	AND event_kind!='burial_witness'
			//	AND event_kind!='profession'
			//	AND event_kind!='religion'
			//	AND event_kind!='picture' ".$hebtext."
			//	ORDER BY event_kind, event_order");
			//$count=$count_event->rowCount();
			//$text.=$count.' x '.__('Events').'. ';

		// *** Add person event ***
		$text.='<select size="1" name="event_kind" style="width: 150px">';
			//$text.='<option value="profession">'.__('Profession').'</option>';
			//$text.='<option value="picture">'.__('Picture/ Media').'</option>';
			$text.='<option value="event">'.__('Event').'</option>';
			//$text.='<option value="birth_declaration">'.__('Birth Declaration').'</option>';
			//$text.='<option value="baptism_witness">'.__('Baptism Witness').'</option>';
			//$text.='<option value="death_declaration">'.__('Death Declaration').'</option>';
			//$text.='<option value="burial_witness">'.__('Burial Witness').'</option>';
			//$text.='<option value="name">'.__('Name').'</option>';
			//$text.='<option value="nobility">'.__('Title of Nobility').'</option>';
			//$text.='<option value="title">'.__('Title').'</option>';
			$text.='<option value="adoption">'.__('Adoption').'</option>';
			//$text.='<option value="lordship">'.__('Title of Lordship').'</option>';
			$text.='<option value="URL">'.__('URL/ Internet link').'</option>';
			$text.='<option value="person_colour_mark">'.__('Colour mark by person').'</option>';
		$text.='</select>';
		$text.=' <input type="Submit" name="person_event_add" value="'.__('Add event').'">';

		//$text.=__('For items like:').' '.__('Event').', '.__('baptized as child').', '.__('depart').' '.__('etc.');
		// *** HELP POPUP for source ***
		$rtlmarker="ltr";
		$text.= '&nbsp;<div class="fonts '.$rtlmarker.'sddm" style="display:inline;">';
			$text.= '<a href="#" style="display:inline" ';
			//echo='onmouseover="mopen(event,\'help_source_shared\',100,250)"';
			$text.= 'onmouseover="mopen(event,\'help_event_person\',0,0)"';
			$text.= 'onmouseout="mclosetime()">';
				$text.= '<img src="../images/help.png" height="16" width="16">';
			$text.= '</a>';
			$text.= '<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:'.$rtlmarker.'" id="help_event_person" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
				$text.= __('For items like:').' '.__('Event').', '.__('baptized as child').', '.__('depart').' '.__('etc.');
			$text.= '</div>';
		$text.= '</div><br>';

		$text.='</td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show events by family ***
	if ($event_kind=='family'){
		$link='event_family_link';
		//$text.='<tr><td style="border-right:0px;"><a name="event_family_link"></a><a href="#event_family_link" onclick="hideShow(52);"><span id="hideshowlink52">'.__('[+]').'</span></a> '.__('Events').'</td>';
		$text.='<tr class="table_header_large"><td style="border-right:0px;"><a name="event_family_link"></a>'.__('Events').'</td>';
		$text.='<td style="border-right:0px;">';
			//$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_event">'.__('Add').'</a>';
		$text.='</td><td style="border-left:0px;">';
			//$count_event=$dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."'
			//	AND event_connect_kind='family' AND event_connect_id='".$event_connect_id."'
			//	AND event_kind!='marriage_witness'
			//	AND event_kind!='marriage_witness_rel'
			//	AND event_kind!='picture'
			//	ORDER BY event_kind, event_order");
			//$count=$count_event->rowCount();
			//$text.=$count.' x '.__('Events').'. ';

			$text.='<select size="1" name="event_kind">';
				//$text.='<option value="picture">Picture</option>';
				$text.='<option value="event">'.__('Event').'</option>';
				//$text.='<option value="marriage_witness">'.__('Marriage Witness').'</option>';
				//$text.='<option value="marriage_witness_rel">'.__('marriage witness (religious)').'</option>';
			$text.='</select>';
			$text.=' <input type="Submit" name="marriage_event_add" value="'.__('Add event').'">';

			//$text.=__('For items like:').' '.__('Event').', '.__('Marriage contract').', '.__('Marriage license').', '.__('etc.');
			//	__('Marriage settlement')
			//	__('Marriage bond')
			//	__('Divorce filed')
			//	__('Annulled')
			//	__('Engaged')
			//	__('Sealed to spouse LDS')

			// *** HELP POPUP for source ***
			$rtlmarker="ltr";
			$text.= '&nbsp;<div class="fonts '.$rtlmarker.'sddm" style="display:inline;">';
				$text.= '<a href="#" style="display:inline" ';
				//echo='onmouseover="mopen(event,\'help_source_shared\',100,250)"';
				$text.= 'onmouseover="mopen(event,\'help_event_family\',0,0)"';
				$text.= 'onmouseout="mclosetime()">';
					$text.= '<img src="../images/help.png" height="16" width="16">';
				$text.= '</a>';
				$text.= '<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:'.$rtlmarker.'" id="help_event_family" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
					$text.= __('For items like:').' '.__('Event').', '.__('Marriage contract').', '.__('Marriage license').', '.__('etc.');
				$text.= '</div>';
			$text.= '</div><br>';

		$text.='</td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show name by person ***
	if ($event_kind=='name'){
		$link='name';
		//$text.='<tr style="display:none;" class="row1">';
		//$text.='<tr>';
		$text.='<tr class="table_header_large">';
		$text.='<td></td>';
		$text.='<td style="border-right:0px;">'.__('Name').'</td>';
		$text.='<td style="border-left:0px;">';
			// *** Nickname, alias, adopted name, hebrew name, etc. ***
			// *** Remark: in editor_inc.php a check is done for event_event_name, so this will also be saved if "Save" is clicked ***
			//$text.='<input type="text" name="event_event_name" placeholder="'.__('Nickname').'" value="" size="35">';
			$text.='<input type="text" name="event_event_name" placeholder="'.__('Nickname').' - '.__('Prefix').' - '.__('Suffix').' - '.__('Title').'" value="" size="35">';
			//$text.=' <select size="1" name="event_gedcom_add" style="width: 150px">';
			$text.=' <select size="1" name="event_gedcom_add" style="width: 200px">';
				$text.=event_selection('');
			$text.='</select>';
			$text.=' <input type="Submit" name="event_add_name" value="'.__('Add').'">';
			$text.='</td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show birth declaration by person ***
	if ($event_kind=='birth_declaration'){
		$link='born';
		//$text.='<tr class="humo_color row2" style="display:none;" name="row2">';
		$text.='<tr class="table_header_large row2" style="display:none;" name="row2">';
		//$text.='<tr'.$show_event_add.' class="humo_color row2" name="row2">';
		$text.='<td></td>';
		$text.='<td style="border-right:0px;">'.__('birth declaration').'</td>';
		$text.='<td style="border-left:0px;">';
			$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_birth_declaration">['.__('Add').']</a></td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show baptism witness by person ***
	if ($event_kind=='baptism_witness'){
		$link='baptised';
		//$text.='<tr class="table_header" style="display:none;" id="row3" name="row3">';
		//$text.='<tr style="display:none;" class="row3" name="row3">';
		$text.='<tr style="display:none;" class="table_header_large row3" name="row3">';
		//$text.='<tr'.$show_event_add.' class="row3" name="row3">';
		$text.='<td></td>';
		$text.='<td style="border-right:0px;">'.__('baptism witness').'</td>';
		$text.='<td style="border-left:0px;">';
			$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_baptism_witness">['.__('Add').']</a></td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show death declaration by person ***
	if ($event_kind=='death_declaration'){
		$link='died';
		//$text.='<tr class="table_header" style="display:none;" id="row4" name="row4">';
		//$text.='<tr style="display:none;" class="humo_color row4" name="row4">';
		$text.='<tr style="display:none;" class="table_header_large row4" name="row4">';
		//$text.='<tr'.$show_event_add.' class="humo_color row4" name="row4">';
		$text.='<td></td>';
		$text.='<td style="border-right:0px;">'.__('death declaration').'</td>';
		$text.='<td style="border-left:0px;">';
			$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_death_declaration">['.__('Add').']</a></td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show burial witness by person ***
	if ($event_kind=='burial_witness'){
		$link='buried';
		//$text.='<tr class="table_header" style="display:none;" id="row5" name="row5">';
		//$text.='<tr style="display:none;" class="row5" name="row5">';
		$text.='<tr style="display:none;" class="table_header_large row5" name="row5">';
		//$text.='<tr'.$show_event_add.' class="row5" name="row5">';
		$text.='<td></td>';
		$text.='<td style="border-right:0px;">'.__('burial witness').'</td>';
		$text.='<td style="border-left:0px;">';
			$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_burial_witness">['.__('Add').']</a></td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show profession by person ***
	if ($event_kind=='profession'){
		$text.='<tr class="table_header_large">';
		$text.='<td style="border-right:0px;">';
			$text.='<a name="profession"></a>';
			$link='profession';
			$text.=__('Profession').'</td>';
		$text.='<td style="border-right:0px;"></td>';
		$text.='<td style="border-left:0px;">';

		// *** Skip for newly added person ***
		if (!isset($_GET['add_person'])){
			// *** Remark: in editor_inc.php a check is done for event_event_profession, so this will also be saved if "Save" is clicked ***
			$text.='<input type="text" name="event_event_profession" placeholder="'.__('Profession').'" value="" size="35">';
			$text.=' <input type="Submit" name="event_add_profession" value="'.__('Add').'">';
		}

		$text.='</td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show religion by person ***
	if ($event_kind=='religion'){
		$text.='<tr class="table_header_large">';
		$text.='<td style="border-right:0px;">';
			$text.='<a name="religion"></a>';
			$link='religion';
			$text.=__('Religion').'</td>';
		$text.='<td style="border-right:0px;"></td>';
		$text.='<td style="border-left:0px;">';

		// *** Skip for newly added person ***
		if (!isset($_GET['add_person'])){
			// *** Remark: in editor_inc.php a check is done for event_event_religion, so this will also be saved if "Save" is clicked ***
			$text.='<input type="text" name="event_event_religion" placeholder="'.__('Religion').'" value="" size="35">';
			$text.=' <input type="Submit" name="event_add_religion" value="'.__('Add').'">';
		}

		$text.='</td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show pictures by person, family and (shared) source ***
	if ($event_kind=='picture' OR $event_kind=='marriage_picture' OR $event_kind=='source_picture'){
		//$text.='<tr class="humo_color">';
		$text.='<tr class="table_header_large">';

		$text.='<td style="border-right:0px;">';
		$text.='<a name="picture"></a>';
		$link='picture';

		//$count_qry=$dbh->query($qry);
		//$count=$count_qry->rowCount();
		//if ($count>0)
		//	$text.='<a href="#picture" onclick="hideShow(53);"><span id="hideshowlink53">'.__('[+]').'</span></a> ';

		$text.=__('Picture/ Media').'</td>';
		$text.='<td style="border-right:0px;"></td>';
		$text.='<td style="border-left:0px;">';
			$event_add='add_picture';
			if ($event_kind=='marriage_picture') $event_add='add_marriage_picture&marriage_nr='.$event_connect_id;
			// *** Otherwise link won't work second time because of added anchor ***
			$anchor='#picture';
			if (isset($_GET['event_add'])){
				$anchor='';
			}
			if ($event_kind=='source_picture'){
				$event_add='add_source_picture&source_id='.$event_connect_id;
				$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;event_add='.$event_add.$anchor.'">['.__('Add').']</a> ';
			}
			else
				$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add='.$event_add.$anchor.'">['.__('Add').']</a> ';

			/*
			// *** JUNE 2021: disabled drag and drop to get a clearer editor page ***
			if ($count>1) { $text.="&nbsp;&nbsp;".__('(Drag pictures to change display order)'); }
			$text.='&nbsp;&nbsp;&nbsp;<a href="index.php?page=thumbs">'.__('Pictures/ create thumbnails').'.</a>';

			$text.='<ul id="sortable_pic" class="sortable_pic handle_pic" style="width:auto">';
			while($data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ)){
				$text.='<li style="word-wrap:break-word;hight:auto;" id="'.$data_listDb->event_id.'" class="mediamove">';
				$text.='<div style="position:relative">';
				if ($count>1) {
					$text.='<div style="position:absolute;top:0;left:0">';
					$show_image= '<img src="'.CMS_ROOTPATH_ADMIN.'images/drag-icon.gif" style="float:left;vertical-align:top;height:16px;">'; $text.=$show_image;
					$text.='</div>';
				}
				$text.='<div style="overflow:hidden">';
				$tree_pict_path2 = $tree_pict_path;  // we change it only if category subfolders exist
				$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
				if($temp->rowCount()) {  // there is a category table 
					$catgr = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
					if($catgr->rowCount()) { 
						while($catDb = $catgr->fetch(PDO::FETCH_OBJ)) {  
							if(substr($data_listDb->event_event,0,3)==$catDb->photocat_prefix AND is_dir($path_prefix.$tree_pict_path2.substr($data_listDb->event_event,0,2)))  {   // there is a subfolder of this prefix
								$tree_pict_path2 = $tree_pict_path2.substr($data_listDb->event_event,0,2).'/';  // look in that subfolder
							}
						}
					}
				}

				$thumb_prefix='';
				if (file_exists($path_prefix.$tree_pict_path2.'thumb_'.$data_listDb->event_event)){ $thumb_prefix='thumb_'; }
				$extensions_check=substr($path_prefix.$tree_pict_path2.$data_listDb->event_event,-3,3);
				if($extensions_check=="jpg" OR $extensions_check=="gif" OR $extensions_check=="png" OR $extensions_check=="bmp") {
					if (file_exists($path_prefix.$tree_pict_path2.$thumb_prefix.$data_listDb->event_event))
						$show_image= '<img src="'.$path_prefix.$tree_pict_path2.$thumb_prefix.$data_listDb->event_event.'" style="height:80px;">';
					else
						$show_image= '<img src="../images/thumb_missing-image.jpg" height="60px">';
					if (!$data_listDb->event_event) $show_image= '&nbsp;<img src="../images/thumb_missing-image.jpg" height="60px">';
					$text.=$show_image;
				}
				else {
					$ext = substr($data_listDb->event_event,-3,3);
					if($ext=="tif" OR $ext=="iff") { $text.='<span style="font-size:80%">['.__('Format not supported')."]</span>"; }
					elseif($ext=="pdf") { $text.='<img src="../images/pdf.jpeg" style="width:30px;height:30px;">';}
					elseif($ext=="doc" OR $ext=="ocx") { $text.='<img src="../images/msdoc.gif" style="width:30px;height:30px;">';}
					elseif($ext=="avi" OR $ext=="wmv" OR $ext=="mpg" OR $ext=="mp4" OR $ext=="mov") { $text.='<img src="../images/video-file.png" style="width:30px;height:30px;">'; }
					elseif($ext=="wma" OR $ext=="wav" OR $ext=="mp3" OR $ext=="mid" OR $ext=="ram" OR $ext==".ra" ) { $text.='<img src="../images/audio.gif" style="width:30px;height:30px;">';}

					$text.='<br><span style="font-size:85%">'.$data_listDb->event_event.'</span>';
				
				}
				// *** No picture selected yet, show dummy picture ***
				if (!$data_listDb->event_event) $text.='<img src="../images/thumb_missing-image.jpg" height="60px">';
				$text.='</div>';
				$text.='</div>';
				$text.='</li>';
			} 
			$text.='</ul>';
			*/

			// DEC 2015: FOR NOW, ONLY SHOW NUMBER OF PICTURE-OBJECTS.
			// *** Search for all external connected objects by a person or a family ***
			if ($event_connect_kind=='person'){
				$connect_qry="SELECT * FROM humo_connections
					WHERE connect_tree_id='".$tree_id."'
					AND connect_sub_kind='pers_object'
					AND connect_connect_id='".$event_connect_id."'
					ORDER BY connect_order";
			}
			elseif ($event_connect_kind=='family'){
				$connect_qry="SELECT * FROM humo_connections
					WHERE connect_tree_id='".$tree_id."'
					AND connect_sub_kind='fam_object'
					AND connect_connect_id='".$event_connect_id."'
					ORDER BY connect_order";
			}
			if ($event_connect_kind=='person' OR $event_connect_kind=='family'){
				$media_nr=0;
				$connect_sql=$dbh->query($connect_qry);
				while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
					$picture_qry=$dbh->query("SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."'
						AND event_gedcomnr='".$connectDb->connect_source_id."' AND event_kind='object'
						ORDER BY event_order");
					while($pictureDb=$picture_qry->fetch(PDO::FETCH_OBJ)){
						$media_nr++;
						//$media_event_id[$media_nr]=$pictureDb->event_id;
						//$media_event_event[$media_nr]=$pictureDb->event_event;
						//$media_event_date[$media_nr]=$pictureDb->event_date;
						//$media_event_text[$media_nr]=$pictureDb->event_text;
						//$media_event_source[$media_nr]=$pictureDb->event_source;
					}
				}
				if ($media_nr>0)
					$text.='<div style="white-space: nowrap;"><b>'.$media_nr.' Picture-objects found. Editing not supported yet...</b></div>';
			}

			//$data_list_qry=$dbh->query($qry);
			//$data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ);

			/*
			$text.= '
			<script>
			$(\'#sortable_pic\').sortable().bind(\'sortupdate\', function() {
				var mediastring = ""; 
				var media_arr = document.getElementsByClassName("mediamove"); 
				for (var z = 0; z < media_arr.length; z++) { 
					// create the new order after dragging to store in database with ajax
					mediastring = mediastring + media_arr[z].id + ";"; 
					// change the order numbers of the pics in the pulldown (that was generated before the drag
					// so that if one presses on delete before refresh the right pic will be deleted !!
				}
				mediastring = mediastring.substring(0, mediastring.length-1); // take off last ;
				
				var parnode = document.getElementById(\'pic_main_\' + media_arr[0].id).parentNode; 
				var picdomclass = document.getElementsByClassName("pic_row2");
				var nextnode = picdomclass[(picdomclass.length)-1].nextSibling;

				for(var d=media_arr.length-1; d >=0 ; d--) {
					parnode.insertBefore(document.getElementById(\'pic_row2_\' + media_arr[d].id),nextnode);
					nextnode = document.getElementById(\'pic_row2_\' + media_arr[d].id);
					parnode.insertBefore(document.getElementById(\'pic_row1_\' + media_arr[d].id),nextnode);
					nextnode = document.getElementById(\'pic_row1_\' + media_arr[d].id);
					parnode.insertBefore(document.getElementById(\'pic_main_\' + media_arr[d].id),nextnode);
					nextnode = document.getElementById(\'pic_main_\' + media_arr[d].id);  
				}

				$.ajax({ 
					url: "include/drag.php?drag_kind=media&mediastring=" + mediastring ,
					success: function(data){
					} ,
					error: function (xhr, ajaxOptions, thrownError) {
						alert(xhr.status);
						alert(thrownError);
					}
				});
			});
			</script>';
			*/

			$text.= '
			<script>
			$(\'#sortable_pic\').sortable().bind(\'sortupdate\', function() {
				var mediastring = ""; 
				var media_arr = document.getElementsByClassName("mediamove"); 
				for (var z = 0; z < media_arr.length; z++) { 
					// create the new order after dragging to store in database with ajax
					mediastring = mediastring + media_arr[z].id + ";"; 
					// change the order numbers of the pics in the pulldown (that was generated before the drag
					// so that if one presses on delete before refresh the right pic will be deleted !!
				}
				mediastring = mediastring.substring(0, mediastring.length-1); // take off last ;
				
				var parnode = document.getElementById(\'pic_main_\' + media_arr[0].id).parentNode; 
				//var picdomclass = document.getElementsByClassName("pic_row2");
				//var nextnode = picdomclass[(picdomclass.length)-1].nextSibling;
				var nextnode = document.getElementById(\'pic_main_\' + media_arr[1].id); 

				for(var d=media_arr.length-1; d >=0 ; d--) {
					//parnode.insertBefore(document.getElementById(\'pic_row2_\' + media_arr[d].id),nextnode);
					//nextnode = document.getElementById(\'pic_row2_\' + media_arr[d].id);

					//parnode.insertBefore(document.getElementById(\'pic_row1_\' + media_arr[d].id),nextnode);
					//nextnode = document.getElementById(\'pic_row1_\' + media_arr[d].id);

					parnode.insertBefore(document.getElementById(\'pic_main_\' + media_arr[d].id),nextnode);
					nextnode = document.getElementById(\'pic_main_\' + media_arr[d].id);  
				}

				$.ajax({ 
					url: "include/drag.php?drag_kind=media&mediastring=" + mediastring ,
					success: function(data){
					} ,
					error: function (xhr, ajaxOptions, thrownError) {
						alert(xhr.status);
						alert(thrownError);
					}
				});
			});
			</script>';

		$text.='</td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show marriage witness by family ***
	if ($event_kind=='marriage_witness'){
		$link='marriage_relation';
		//$text.='<tr class="table_header" style="display:none;" id="row8" name="row8">';
		//$text.='<tr style="display:none;" class="row8 humo_color" name="row8">';
		$text.='<tr style="display:none;" class="row8 table_header_large" name="row8">';
		//$text.='<tr'.$show_event_add.' class="row8 humo_color" name="row8">';
		$text.='<td></td>';
		$text.='<td style="border-right:0px;">'.__('marriage witness').'</td>';
		$text.='<td style="border-left:0px;">';
			$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_marriage_witness&marriage_nr='.$event_connect_id.'#event_family_link">['.__('Add').']</a></td>';
		$text.='<td></td>';
		$text.='</tr>';
	}

	// *** Show marriage witness (religious) by family ***
	if ($event_kind=='marriage_witness_rel'){
		$link='marr_church';
		//$text.='<tr class="table_header" style="display:none;" id="row10" name="row10">';
		//$text.='<tr style="display:none;" class="row10 humo_color" name="row10">';
		$text.='<tr style="display:none;" class="row10 table_header_large" name="row10">';
		//$text.='<tr '.$show_event_add.' class="row10 humo_color" name="row10">';
		$text.='<td></td>';
		$text.='<td style="border-right:0px;">'.__('marriage witness (religious)').'</td>';
		$text.='<td style="border-left:0px;">';
			$text.='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_marriage_witness_rel&marriage_nr='.$event_connect_id.'#event_family_link">['.__('Add').']</a></td>';
		$text.='<td style="border-left:0px;"></td>';
		$text.='</tr>';
	}

	if (!isset($_GET['add_person'])) {
	$data_list_qry=$dbh->query($qry);
	while($data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ)){
		$text.='<input type="hidden" name="event_id['.$data_listDb->event_id.']" value="'.$data_listDb->event_id.'">';

		$expand_link=''; $internal_link='#';
		if ($event_kind=='person'){
			//$change_bg_colour=' class="humo_color"';
			//$expand_link=' style="display:none;" class="row51" name="row51"';
			$expand_link='';
			$change_bg_colour='';
			$internal_link='#event_person_link';
		}
		if ($event_kind=='family'){
			//$change_bg_colour=' class="humo_color"';
			//$expand_link=' style="display:none;" class="row52" name="row52"';
			$expand_link='';
			$change_bg_colour='';
			$internal_link='#event_family_link';
		}
		if ($event_kind=='name'){
			//$change_bg_colour=' class="humo_color"';
			//$expand_link=' style="display:none;" class="row1" name="row1"';
			$expand_link='';
			$change_bg_colour='';
			$internal_link='#';
		}
		if ($event_kind=='NPFX'){
			//$change_bg_colour=' class="humo_color"';
			//$expand_link=' style="display:none;" class="row1" name="row1"';
			$expand_link='';
			$change_bg_colour='';
		}
		if ($event_kind=='NSFX'){
			//$change_bg_colour=' class="humo_color"';
			//$expand_link=' style="display:none;" class="row1" name="row1"';
			$expand_link='';
			$change_bg_colour='';
		}
		if ($event_kind=='nobility'){
			//$change_bg_colour=' class="humo_color"';
			//$expand_link=' style="display:none;" class="row1" name="row1"';
			$expand_link='';
			$change_bg_colour='';
		}
		if ($event_kind=='title'){
			//$change_bg_colour=' class="humo_color"';
			//$expand_link=' style="display:none;" class="row1" name="row1"';
			$expand_link='';
			$change_bg_colour='';
		}
		if ($event_kind=='lordship'){
			//$change_bg_colour=' class="humo_color"';
			//$expand_link=' style="display:none;" class="row1" name="row1"';
			$expand_link='';
			$change_bg_colour='';
		}
		if ($event_kind=='birth_declaration'){
			//$expand_link=' style="display:none;" class="row2 humo_color" name="row2"';
			$expand_link='';
			//$change_bg_colour='';
			$change_bg_colour=' class="humo_color"';
		}
		if ($event_kind=='baptism_witness'){
			//$expand_link=' style="display:none;" class="row3" name="row3"';
			$expand_link='';
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
		}
		if ($event_kind=='death_declaration'){
			//$expand_link=' style="display:none;" class="row4 humo_color" name="row4"';
			$expand_link='';
			//$change_bg_colour='';
			$change_bg_colour=' class="humo_color"';
		}
		if ($event_kind=='burial_witness'){
			//$expand_link=' style="display:none;" class="row5" name="row5"';
			$expand_link='';
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
		}
		if ($event_kind=='profession'){
			//$expand_link=' style="display:none;" class="row13" name="row13"';
			$expand_link='';
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$internal_link='#profession';
		}
		if ($event_kind=='religion'){
			//$expand_link=' style="display:none;" class="row13" name="row13"';
			$expand_link='';
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$internal_link='#religion';
		}
		if ($event_kind=='picture' OR $event_kind=='marriage_picture' OR $event_kind=='source_picture'){
			//$expand_link=' style="display:none;" id="pic_main_'.$data_listDb->event_id.'" class="pic_main row53 humo_color" name="row53"';
			$expand_link='';
			//$change_bg_colour='';
			$change_bg_colour=' class="humo_color"';
			$internal_link='#picture';
		}
		if ($event_kind=='marriage_witness'){
			//$expand_link=' style="display:none;" class="row8 humo_color" name="row8"';
			$expand_link='';
			//$change_bg_colour='';
			$change_bg_colour=' class="humo_color"';
			$internal_link='#event_family_link';
		}
		if ($event_kind=='marriage_witness_rel'){
			//$expand_link=' style="display:none;" class="row10 humo_color" name="row10"';
			$expand_link='';
			//$change_bg_colour='';
			$change_bg_colour=' class="humo_color"';
			$internal_link='#event_family_link';
		}

		//$text.='<tr'.$expand_link.'>';
		$text.='<tr'.$expand_link.$change_bg_colour.'>';

		// *** Show name of event and [+] link ***
		$text.='<td>';
			//$text.='&nbsp;&nbsp;&nbsp;<a href="'.$internal_link.'" onclick="hideShow('.$data_listDb->event_id.'00);"><span id="hideshowlink'.$data_listDb->event_id.'00">'.__('[+]').'</span></a>';
			//$text.=' #'.$data_listDb->event_order;
			$newpers = ""; if(isset($_GET['add_person'])) { $newpers = "&amp;add_person=1"; }
			$text.='<a href="index.php?'.$joomlastring.'page='.$page.$newpers.'&amp;'.$event_group.
				'&amp;event_kind='.$data_listDb->event_kind.'&amp;event_drop='.$data_listDb->event_order;
			// *** Remove picture by source ***
			if ($event_kind=='source_picture') $text.='&amp;source_id='.$data_listDb->event_connect_id;
			$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="down"></a>';

			//if ($data_listDb->event_kind !='picture'){
				// *** Count number of events ***
				if ($event_connect_kind=='person'){
					$count_event=$dbh->query("SELECT * FROM humo_events
						WHERE event_tree_id='".$tree_id."' AND event_connect_kind='person' AND event_connect_id='".$event_connect_id."' AND event_kind='".$data_listDb->event_kind."'");
				}
				elseif ($event_connect_kind=='family'){
					$count_event=$dbh->query("SELECT * FROM humo_events
						WHERE event_tree_id='".$tree_id."' AND event_connect_kind='family' AND event_connect_id='".$event_connect_id."' AND event_kind='".$data_listDb->event_kind."'");
				}
				// *** Edit picture by source in seperate source page ***
				elseif ($event_connect_kind=='source'){
					$count_event=$dbh->query("SELECT * FROM humo_events
						WHERE event_tree_id='".$tree_id."' AND event_connect_kind='source' AND event_connect_id='".$event_connect_id."' AND event_kind='".$data_listDb->event_kind."'");
				}
				$count=$count_event->rowCount();

				// *** dummy is not really necessary, but otherwise it's not possible to click an arrow twice ***
				if ($data_listDb->event_order<$count){
					$text.=' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;'.$event_group.'&amp;event_down='.$data_listDb->event_order.'&amp;event_kind='.$data_listDb->event_kind;
					// *** Edit picture by source in seperate source page ***
					if ($event_kind=='source_picture') $text.='&amp;source_id='.$data_listDb->event_connect_id;
					$text.='&amp;dummy='.$data_listDb->event_id.$internal_link.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>';
				}
				else{
					$text.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}

				// *** dummy is not really necessary, but otherwise it's not possible to click an arrow twice ***
				if ($data_listDb->event_order>1){
					$text.=' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;'.$event_group.'&amp;event_up='.$data_listDb->event_order.'&amp;event_kind='.$data_listDb->event_kind;
					// *** Edit picture by source in seperate source page ***
					if ($event_kind=='source_picture') $text.='&amp;source_id='.$data_listDb->event_connect_id;
					$text.='&amp;dummy='.$data_listDb->event_id.$internal_link;
					$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="down"></a>';
				}
				else{
					$text.='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			//}
			$text.='</td>';

			$text.='<td style="border-right:0px;">';

			// ** Show name of this event ***
			if ($data_listDb->event_kind!='picture'){
				$text.=$this->event_text($data_listDb->event_kind);
				$text.='<br>';
			}

			// *** Picture: show thumbnail ***
			if ($data_listDb->event_kind=='picture'){

				$tree_pict_path3 = $tree_pict_path;  // we change it only if category subfolders exist
				$temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
				if($temp->rowCount()) {  // there is a category table 
					$catgr = $dbh->query("SELECT photocat_prefix FROM humo_photocat WHERE photocat_prefix != 'none' GROUP BY photocat_prefix");
					if($catgr->rowCount()) { 
						while($catDb = $catgr->fetch(PDO::FETCH_OBJ)) {  
							if(substr($data_listDb->event_event,0,3)==$catDb->photocat_prefix AND is_dir($path_prefix.$tree_pict_path3.substr($data_listDb->event_event,0,2)))  {   // there is a subfolder of this prefix
								$tree_pict_path3 = $tree_pict_path3.substr($data_listDb->event_event,0,2).'/';  // look in that subfolder
							}
						}
					}
				}

				$extensions_check=substr($path_prefix.$tree_pict_path3.$data_listDb->event_event,-3,3);
				if(strtolower($extensions_check)=="pdf") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'"><img src="'.CMS_ROOTPATH.'images/pdf.jpeg"></a>';
				}
				elseif(strtolower($extensions_check)=="doc" OR strtolower(substr($path_prefix.$tree_pict_path3.$data_listDb->event_event,-4,4))=="docx") {   
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'"><img src="'.CMS_ROOTPATH.'images/msdoc.gif"></a>';
				}
				// *** Show AVI Video file ***
				elseif($extensions_check=="avi") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
				}
				// *** Show WMV Video file ***
				elseif($extensions_check=="wmv") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
				}
				// *** Show MPG Video file ***
				elseif(strtolower($extensions_check)=="mpg") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
				}
				// *** Show MP4 Video file ***
				elseif(strtolower($extensions_check)=="mp4") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
				}
				// *** Show MOV Video file ***
				elseif(strtolower($extensions_check)=="mov") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/video-file.png"></a>';
				}
				// *** Show WMA Audio file ***
				elseif(strtolower($extensions_check)=="wma") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
				}
				// *** Show WAV Audio file ***
				elseif(strtolower($extensions_check)=="wav") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
				}
				// *** Show MP3 Audio file ***
				elseif(strtolower($extensions_check)=="mp3") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
				}
				// *** Show MID Audio file ***
				elseif(strtolower($extensions_check)=="mid") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
				}
				// *** Show RAM Audio file ***
				elseif(strtolower($extensions_check)=="ram") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
				}
				// *** Show RA Audio file ***
				elseif(strtolower($extensions_check)==".ra") {
					$text.='<a href="'.$path_prefix.$tree_pict_path3.$data_listDb->event_event.'" target="_blank"><img src="'.CMS_ROOTPATH.'images/audio.gif"></a>';
				}
				else{
					$show_image='';

					// *** No subdirectory: show piture/ thumbnail ***
					$thumb_prefix='';
					if (file_exists($path_prefix.$tree_pict_path3.'thumb_'.$data_listDb->event_event)){ $thumb_prefix='thumb_'; }
					$picture=$path_prefix.$tree_pict_path3.$thumb_prefix.$data_listDb->event_event;

					// *** Check if picture is in subdirectory ***
					// Example: subdir1_test/xy/2022_02_12 Scheveningen.jpg
					if ($thumb_prefix==''){
						$dirname=dirname($data_listDb->event_event); // subdir1_test/xy/2022_02_12
						$basename=basename($data_listDb->event_event); // 2022_02_12 Scheveningen.jpg
						if (file_exists($path_prefix.$tree_pict_path3.$dirname.'/thumb_'.$basename)){ $thumb_prefix='thumb_'; }
						$picture=$path_prefix.$tree_pict_path3.$dirname.'/'.$thumb_prefix.$basename;
					}

					if ($data_listDb->event_event AND file_exists($picture)){
						// *** Get size of original picture ***
						list($width, $height) = getimagesize($picture);
						$size=' style="width:100px"';
						if ($height>$width) $size=' style="height:80px"';
						//$show_image= '<img src="'.$path_prefix.$tree_pict_path3.$thumb_prefix.$data_listDb->event_event.'"'.$size.'>';
						$show_image= '<img src="'.$picture.'"'.$size.'>';
					}
					else
						$show_image= '<img src="../images/thumb_missing-image.jpg" style="width:100px">';
//Check line above. If thumb if missing, missing picture is shown...

					if (!$data_listDb->event_event) $show_image= '<img src="../images/thumb_missing-image.jpg" style="width:100px">';
					$text.=$show_image;
				}
			}

		$text.='</td>';

		//$text.='<td style="border-left:0px; border-bottom:solid 2px #0000FF;">';
		$text.='<td style="border-left:solid 2px #0000FF;">';

		// *** Witness and declaration persons ***
		if ($data_listDb->event_kind=='baptism_witness' OR $data_listDb->event_kind=='birth_declaration'
		OR $data_listDb->event_kind=='death_declaration' OR $data_listDb->event_kind=='burial_witness'
		OR $data_listDb->event_kind=='marriage_witness' OR $data_listDb->event_kind=='marriage_witness_rel')
		{

			// *** Hide or show editor fields ***
			if (substr($data_listDb->event_event,0,1)=='@'){
				$witness_name=show_person(substr($data_listDb->event_event,1,-1),$gedcom_date=false, $show_link=false);
			}
			else{
				$witness_name=$data_listDb->event_event;
			}
			// *** Hide/show line (start <span> to hide edit line) ***
			$text.=$this->hide_show_start($data_listDb,$witness_name);


			//$text.='<td style="border-left:0px;">';
			$event_text=$this->event_text($data_listDb->event_kind);
			$text.=witness_edit($event_text,$data_listDb->event_event,'['.$data_listDb->event_id.']');
		}

		elseif ($data_listDb->event_kind=='picture'){
			// *** Hide/show line (start <span> to hide edit line) ***
			$text.=$this->hide_show_start($data_listDb);

			// *** Use text box for pictures and pop-up window ***
			// *** To use place selection pop-up, replaced event_place[x] array by: 'event_place_'.$data_listDb->event_id ***
			$text.='<input type="text" name="text_event'.$data_listDb->event_id.'" placeholder="'.__('Picture/ Media').'" value="'.$data_listDb->event_event.'" style="width: 500px">';
			$form=1;
			if ($event_connect_kind=='family') $form=2;
			if ($event_connect_kind=='source') $form=3;
			//$text.='<a href="javascript:;" onClick=window.open("index.php?page=editor_media_select&amp;form='.$form.'&amp;event_id='.$data_listDb->event_id.'","","width=400,height=500,top=100,left=100,scrollbars=yes");><img src="../images/search.png" border="0"></a>';
			$text.='<a href="javascript:;" onClick=window.open("index.php?page=editor_media_select&amp;form='.$form.'&amp;event_id='.$data_listDb->event_id.'","","'.$field_popup.'");><img src="../images/search.png" border="0"></a>';
		}

		elseif ($data_listDb->event_kind=='adoption'){
			// *** Show names of adoption parents ***
			$parent_text='';
			if ($data_listDb->event_event){
				$adoptionDb = $db_functions->get_family($data_listDb->event_event,'man-woman');
				$parent_text='['.$data_listDb->event_event.'] ';

				//*** Father ***
				if (isset($adoptionDb->fam_man) AND $adoptionDb->fam_man){
					$parent_text.=show_person($adoptionDb->fam_man,false,false);
				}
				else{
					$parent_text=__('N.N.');
				}

				$parent_text.=' '.__('and').' ';

				//*** Mother ***
				if (isset($adoptionDb->fam_woman) AND $adoptionDb->fam_woman){
					$parent_text.=show_person($adoptionDb->fam_woman,false,false);
				}
				else{
					$parent_text.=__('N.N.');
				}
			}

			// *** Hide/show line (start <span> to hide edit line) ***
			$text.=$this->hide_show_start($data_listDb,$parent_text);

			// *** Use pop-up to select adoption parents ***
			$text.='<input type="text" name="text_event'.$data_listDb->event_id.'" placeholder="'.__('GEDCOM number (ID)').'" value="'.$data_listDb->event_event.'" style="width: 250px">';
			$text.='<a href="javascript:;" onClick=window.open("index.php?page=editor_relation_select&amp;adoption_id='.$data_listDb->event_id.'","","'.$field_popup.'");><img src="../images/search.png" border="0"></a>';
		}

		// *** person_colour_mark ***
		elseif ($data_listDb->event_kind=='person_colour_mark'){
			// *** Needed for descendants/ ascendants color ***
			$text.='<input type="hidden" name="event_event_old['.$data_listDb->event_id.']" value="'.$data_listDb->event_event.'">';

			$pers_colour='';
			$person_colour_mark=$data_listDb->event_event;
			if ($person_colour_mark=='1') $pers_colour='style="color:#FF0000;"';
			if ($person_colour_mark=='2') $pers_colour='style="color:#00FF00;"';
			if ($person_colour_mark=='3') $pers_colour='style="color:#0000FF;"';
			if ($person_colour_mark=='4') $pers_colour='style="color:#FF00FF;"';
			if ($person_colour_mark=='5') $pers_colour='style="color:#FFFF00;"';
			if ($person_colour_mark=='6') $pers_colour='style="color:#00FFFF;"';
			if ($person_colour_mark=='7') $pers_colour='style="color:#C0C0C0;"';
			if ($person_colour_mark=='8') $pers_colour='style="color:#800000;"';
			if ($person_colour_mark=='9') $pers_colour='style="color:#008000;"';
			if ($person_colour_mark=='10') $pers_colour='style="color:#000080;"';
			if ($person_colour_mark=='11') $pers_colour='style="color:#800080;"';
			if ($person_colour_mark=='12') $pers_colour='style="color:#A52A2A;"';
			if ($person_colour_mark=='13') $pers_colour='style="color:#008080;"';
			if ($person_colour_mark=='14') $pers_colour='style="color:#808080;"';
			//$text.=' <span '.$pers_colour.'>'.__('Selected colour').'</span>';
			$person_colour=' <span '.$pers_colour.'>'.__('Selected colour').'</span>';

			// *** Hide/show line (start <span> to hide edit line) ***
			$text.=$this->hide_show_start($data_listDb,$person_colour);

			//$text.='<td style="border-left:0px;">';
			$text.=' <select class="fonts" size="1" name="text_event['.$data_listDb->event_id.']">';
				$text.='<option value="0">'.__('Change colour mark by person').'</option>';
				$selected=''; if ($person_colour_mark=='1'){ $selected=' selected'; }
				$text.='<option value="1" style="color:#FF0000;"'.$selected.'>'.__('Colour 1').'</option>';
				$selected=''; if ($person_colour_mark=='2'){ $selected=' selected'; }
				$text.='<option value="2" style="color:#00FF00;"'.$selected.'>'.__('Colour 2').'</option>';
				$selected=''; if ($person_colour_mark=='3'){ $selected=' selected'; }
				$text.='<option value="3" style="color:#0000FF;"'.$selected.'>'.__('Colour 3').'</option>';
				$selected=''; if ($person_colour_mark=='4'){ $selected=' selected'; }
				$text.='<option value="4" style="color:#FF00FF;"'.$selected.'>'.__('Colour 4').'</option>';
				$selected=''; if ($person_colour_mark=='5'){ $selected=' selected'; }
				$text.='<option value="5" style="color:#FFFF00;"'.$selected.'>'.__('Colour 5').'</option>';
				$selected=''; if ($person_colour_mark=='6'){ $selected=' selected'; }
				$text.='<option value="6" style="color:#00FFFF;"'.$selected.'>'.__('Colour 6').'</option>';
				$selected=''; if ($person_colour_mark=='7'){ $selected=' selected'; }
				$text.='<option value="7" style="color:#C0C0C0;"'.$selected.'>'.__('Colour 7').'</option>';
				$selected=''; if ($person_colour_mark=='8'){ $selected=' selected'; }
				$text.='<option value="8" style="color:#800000;"'.$selected.'>'.__('Colour 8').'</option>';
				$selected=''; if ($person_colour_mark=='9'){ $selected=' selected'; }
				$text.='<option value="9" style="color:#008000;"'.$selected.'>'.__('Colour 9').'</option>';
				$selected=''; if ($person_colour_mark=='10'){ $selected=' selected'; }
				$text.='<option value="10" style="color:#000080;"'.$selected.'>'.__('Colour 10').'</option>';
				$selected=''; if ($person_colour_mark=='11'){ $selected=' selected'; }
				$text.='<option value="11" style="color:#800080;"'.$selected.'>'.__('Colour 11').'</option>';
				$selected=''; if ($person_colour_mark=='12'){ $selected=' selected'; }
				$text.='<option value="12" style="color:#A52A2A;"'.$selected.'>'.__('Colour 12').'</option>';
				$selected=''; if ($person_colour_mark=='13'){ $selected=' selected'; }
				$text.='<option value="13" style="color:#008080;"'.$selected.'>'.__('Colour 13').'</option>';
				$selected=''; if ($person_colour_mark=='14'){ $selected=' selected'; }
				$text.='<option value="14" style="color:#808080;"'.$selected.'>'.__('Colour 14').'</option>';
			$text.='</select>';

			// *** Also change color of ascendants and/ or descendants ***
			$check=''; //if (isset($xx) AND $xx=='y'){ $check=' checked'; }
			$text.='<br>'.__('Also change').' <input type="checkbox" name="pers_colour_desc['.$data_listDb->event_id.']" '.$check.'> '.__('Descendants');
			$text.='<input type="checkbox" name="pers_colour_anc['.$data_listDb->event_id.']" '.$check.'> '.__('Ancestors');
		}

		// *** profession ***
		elseif ($data_listDb->event_kind=='profession'){
			// *** Hide/show line (start <span> to hide edit line) ***
			$text.=$this->hide_show_start($data_listDb);

			$text.='<textarea rows="1" name="text_event['.$data_listDb->event_id.']" '.$field_text.' placeholder="'.__('Profession').'">'.$editor_cls->text_show($data_listDb->event_event).'</textarea>';
		}

		// *** religion ***
		elseif ($data_listDb->event_kind=='religion'){
			// *** Hide/show line (start <span> to hide edit line) ***
			$text.=$this->hide_show_start($data_listDb);

			$text.='<textarea rows="1" name="text_event['.$data_listDb->event_id.']" '.$field_text.' placeholder="'.__('Religion').'">'.$editor_cls->text_show($data_listDb->event_event).'</textarea>';
		}

		// *** General name of event ***
		else{
			// *** Hide/show line (start <span> to hide edit line) ***
			$text.=$this->hide_show_start($data_listDb);

			// *** Check if event has text ***
			$style=''; if (!$data_listDb->event_event) $style='style="background-color:#FFAA80"';
			$text.='<input type="text" '.$style.' name="text_event['.$data_listDb->event_id.']" placeholder="'.__('Event').'" value="'.$data_listDb->event_event.'" size="60">';
		}

		if ($data_listDb->event_kind=='NPFX'){ $text.=' '.__('e.g. Lt. Cmndr.'); }
		elseif ($data_listDb->event_kind=='NSFX'){ $text.=' '.__('e.g. Jr.'); }
		elseif ($data_listDb->event_kind=='nobility'){ $text.=' '.__('e.g. Jhr., Jkvr.'); }
		elseif ($data_listDb->event_kind=='title'){ $text.=' '.__('e.g. Prof., Dr.'); }
		elseif ($data_listDb->event_kind=='lordship'){ $text.=' '.__('e.g. Lord of Amsterdam'); }

		// *** Select type of event ***
		if ($data_listDb->event_kind=='event'){
			$text.=' <select size="1" name="event_gedcom['.$data_listDb->event_id.']" style="width: 150px">';

			if ($event_kind=='person'){
				$text.='<optgroup label="'.__('Events').'">';
					$text.=event_option($data_listDb->event_gedcom,'EVEN');
					$text.=event_option($data_listDb->event_gedcom,'_NMAR');
					$text.=event_option($data_listDb->event_gedcom,'NCHI');
					$text.=event_option($data_listDb->event_gedcom,'MILI');
					$text.=event_option($data_listDb->event_gedcom,'TXPY');
					$text.=event_option($data_listDb->event_gedcom,'CENS');
					$text.=event_option($data_listDb->event_gedcom,'RETI');
					$text.=event_option($data_listDb->event_gedcom,'CAST');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Baptise').'">';
					$text.=event_option($data_listDb->event_gedcom,'BAPM');
					$text.=event_option($data_listDb->event_gedcom,'CHRA');
					$text.=event_option($data_listDb->event_gedcom,'LEGI');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Adoption').'">';
					$text.=event_option($data_listDb->event_gedcom,'ADOP');
					$text.=event_option($data_listDb->event_gedcom,'_ADPF');
					$text.=event_option($data_listDb->event_gedcom,'_ADPM');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Settling').'">';
					$text.=event_option($data_listDb->event_gedcom,'ARVL');
					$text.=event_option($data_listDb->event_gedcom,'DPRT');
					$text.=event_option($data_listDb->event_gedcom,'IMMI');
					$text.=event_option($data_listDb->event_gedcom,'EMIG');
					$text.=event_option($data_listDb->event_gedcom,'NATU');
					$text.=event_option($data_listDb->event_gedcom,'NATI');
					$text.=event_option($data_listDb->event_gedcom,'PROP');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Characteristics').'">';
					$text.=event_option($data_listDb->event_gedcom,'_HEIG');
					$text.=event_option($data_listDb->event_gedcom,'_WEIG');
					$text.=event_option($data_listDb->event_gedcom,'_EYEC');
					$text.=event_option($data_listDb->event_gedcom,'_HAIR');
					$text.=event_option($data_listDb->event_gedcom,'_MEDC');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Buried').'">';
					$text.=event_option($data_listDb->event_gedcom,'_FNRL');
					$text.=event_option($data_listDb->event_gedcom,'_INTE');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Will').'">';
					$text.=event_option($data_listDb->event_gedcom,'PROB');
					$text.=event_option($data_listDb->event_gedcom,'WILL');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Religious').'">';
					$text.=event_option($data_listDb->event_gedcom,'CONF');
					$text.=event_option($data_listDb->event_gedcom,'BLES');
					$text.=event_option($data_listDb->event_gedcom,'FCOM');
					$text.=event_option($data_listDb->event_gedcom,'ORDN');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Education').'">';
					$text.=event_option($data_listDb->event_gedcom,'GRAD');
					$text.=event_option($data_listDb->event_gedcom,'EDUC');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Social').'">';
					$text.=event_option($data_listDb->event_gedcom,'AFN');
					$text.=event_option($data_listDb->event_gedcom,'SSN');
					$text.=event_option($data_listDb->event_gedcom,'IDNO');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('LDS').'">';
					$text.=event_option($data_listDb->event_gedcom,'BAPL');
					$text.=event_option($data_listDb->event_gedcom,'CONL');
					$text.=event_option($data_listDb->event_gedcom,'ENDL');
					$text.=event_option($data_listDb->event_gedcom,'SLGC');
					$text.=event_option($data_listDb->event_gedcom,'SLGL');
				$text.='</optgroup>';

				$text.='<optgroup label="'.__('Jewish').'">';
					$text.=event_option($data_listDb->event_gedcom,'BARM');
					$text.=event_option($data_listDb->event_gedcom,'BASM');
					$text.=event_option($data_listDb->event_gedcom,'_BRTM');
					$text.=event_option($data_listDb->event_gedcom,'_YART');
				$text.='</optgroup>';
			}

			if ($event_kind=='family'){
				// *** Marriage events ***
				$text.=event_option($data_listDb->event_gedcom,'EVEN');
				$text.=event_option($data_listDb->event_gedcom,'_MBON');
				$text.=event_option($data_listDb->event_gedcom,'MARC');
				$text.=event_option($data_listDb->event_gedcom,'MARL');
				$text.=event_option($data_listDb->event_gedcom,'MARS');
				$text.=event_option($data_listDb->event_gedcom,'DIVF');
				$text.=event_option($data_listDb->event_gedcom,'ANUL');
				$text.=event_option($data_listDb->event_gedcom,'ENGA');
				$text.=event_option($data_listDb->event_gedcom,'SLGS');
			}

			$text.='</select>';
		}

		if ($data_listDb->event_kind=='name'){
			$text.=' <select size="1" name="event_gedcom['.$data_listDb->event_id.']" style="width: 150px">';
				// *** Nickname, alias, adopted name, hebrew name, etc. ***
				$text.=event_selection($data_listDb->event_gedcom);
			$text.='</select>';
		}
		//$text.='<td><input type="submit" name="submit" title="submit" value="'.__('Save').'"></td></td>';

		// *** Date and place by event ***
		$text.='<br>'.$editor_cls->date_show($data_listDb->event_date,'event_date',"[$data_listDb->event_id]");

		// *** To use place selection pop-up, replaced event_place[x] array by: 'event_place_'.$data_listDb->event_id ***
		$text.=' '.__('place').' <input type="text" name="event_place'.$data_listDb->event_id.'" placeholder="'.__('place').'" value="'.$data_listDb->event_place.'" size="'.$field_place.'">';
		$form=1;
		if ($event_connect_kind=='family') $form=2;
		if ($event_connect_kind=='source') $form=3;
		//$text.='<a href="javascript:;" onClick=window.open("index.php?page=editor_place_select&amp;form='.$form.'&amp;place_item=event_place&amp;event_id='.$data_listDb->event_id.'","","width=400,height=500,top=100,left=100,scrollbars=yes");><img src="../images/search.png" border="0"></a>';
		$text.='<a href="javascript:;" onClick=window.open("index.php?page=editor_place_select&amp;form='.$form.'&amp;place_item=event_place&amp;event_id='.$data_listDb->event_id.'","","'.$field_popup.'");><img src="../images/search.png" border="0"></a>';

		// *** Text by event ***
		$field_text_selected=$field_text;
		if ($data_listDb->event_text AND preg_match('/\R/',$data_listDb->event_text)) $field_text_selected=$field_text_medium;
		$text.='<br><textarea rows="1" name="event_text['.$data_listDb->event_id.']" '.$field_text_selected.' placeholder="'.__('text').'">'.$editor_cls->text_show($data_listDb->event_text).'</textarea>';

		// *** Use hideshow to show and hide the editor lines ***
		if (isset($hideshow) AND substr($hideshow,0,4)=='9000') $text.='</span>';

		$text.='</td>';

		$text.='<td>';
			// *** Source by event ***
			if ($event_connect_kind=='person'){
				if (!isset($link)) $link='';
				$text.=source_link2('10'.$data_listDb->event_id,$data_listDb->event_id,'pers_event_source',$link);

				// *** Could be used to connect a picture in a text field (Geneanet doesnt have constant GEDCOM numbers or an own text field) ***
				if ($event_kind=='picture')
					$text.='<br>&nbsp;<span style="font-size:smaller;">'.__('ID').': '.$data_listDb->event_id.'</span>';

			}
			elseif ($event_connect_kind=='family'){
				if (!isset($link)) $link='';
				$text.=source_link2('20'.$data_listDb->event_id,$data_listDb->event_id,'fam_event_source',$link);
			}
			// *** Source by picture by source... ***
			// DISABLED... Not sure if it's necessary to use a source by a picture by a source...
			//elseif ($event_connect_kind=='source'){
			//	// *** Calculate and show nr. of sources ***
			//	$text.=source_link2('30'.$data_listDb->event_id,$data_listDb->event_id,'fam_event_source');
			//}
		$text.='</td>';
		$text.='</tr>';

		if ($event_connect_kind=='person'){
			// *** Show iframe source ***
			$text.=iframe_source('10'.$data_listDb->event_id,'person','pers_event_source',$data_listDb->event_id);
		}
		elseif ($event_connect_kind=='family'){
			// *** Show iframe source ***
			$text.=iframe_source('20'.$data_listDb->event_id,'family','fam_event_source',$data_listDb->event_id);
		}
	}
	} // *** Don't use this block for newly added person ***


	// *** Directly add a first profession for new person ***
	if(isset($_GET['add_person'])) {
		if ($event_kind=='profession'){
			$text.='<tr>';
				$text.='<td></td>';
				$text.='<td style="border-right:0px;">'.__('Profession').'</td>';
				$text.='<td>';
					// *** Profession ***
					$text.='<input type="text" name="event_profession" placeholder="'.__('Profession').'" value="" size="60"><br>';

					// *** Date and place by event ***
					$text.=$editor_cls->date_show("","event_date_profession","").' '.__('place').' <input type="text" name="event_place_profession" placeholder="'.__('place').'" value="" size="'.$field_date.'"><br>';

					// *** Text by event ***
					$text.='<textarea rows="1" name="event_text_profession" '.$field_text.' placeholder="'.__('text').'">'.$editor_cls->text_show("").'</textarea>';
				$text.='</td>';
				$text.='<td></td>';
			$text.='</tr>';
		}
		elseif ($event_kind=='religion'){
			$text.='<tr>';
				$text.='<td></td>';
				$text.='<td style="border-right:0px;">'.__('Religion').'</td>';
				$text.='<td>';
					// *** Religion ***
					$text.='<input type="text" name="event_religion" placeholder="'.__('Religion').'" value="" size="60"><br>';

					// *** Date and place by event ***
					$text.=$editor_cls->date_show("","event_date_religion","").' '.__('place').' <input type="text" name="event_place_religion" placeholder="'.__('place').'" value="" size="'.$field_date.'"><br>';

					// *** Text by event ***
					$text.='<textarea rows="1" name="event_text_religion" '.$field_text.' placeholder="'.__('text').'">'.$editor_cls->text_show("").'</textarea>';
				$text.='</td>';
				$text.='<td></td>';
			$text.='</tr>';
		}
	}

	if ($event_kind=='picture' OR $event_kind=='marriage_picture'){
		// *** Upload image ***
		//$text.='<tr style="display:none;" class="row53" name="row53"><td class="table_header_large" colspan="4">';
		$text.='<tr><td class="table_header_large" colspan="4">';
			//$text.=sprintf(__('Upload new image. Picture max: %1$d MB or media max: %2$d MB.'), '2', '49');
			$text.=__('Upload new image');
			$text.=' <input type="file" name="photo_upload">';
			if ($event_kind=='picture')
				//$text.='<input type="submit" name="person_event_change" title="submit" value="'.__('Upload').'">';
				$text.='<input type="submit" name="person_add_media" title="submit" value="'.__('Upload').'">';
			else
				//$text.='<input type="submit" name="marriage_event_change" title="submit" value="'.__('Upload').'">';
				$text.='<input type="submit" name="relation_add_media" title="submit" value="'.__('Upload').'">';
		$text.='</td></tr>';
	}

	// *** Show events if save or arrow links are used ***
	if (isset($_GET['event_person']) OR isset($_GET['event_family']) OR isset($_GET['event_add'])){
		// *** Script voor expand and collapse of items ***

		$link_id='';
		if (isset($_GET['event_person']) AND $_GET['event_person']=='1') $link_id='51';
		if (isset($_GET['event_family']) AND $_GET['event_family']=='1') $link_id='52';
		if (isset($_GET['event_kind'])){
			if ($_GET['event_kind']=='name') $link_id='1';
			if ($_GET['event_kind']=='npfx') $link_id='1';
			if ($_GET['event_kind']=='nsfx') $link_id='1';
			if ($_GET['event_kind']=='nobility') $link_id='1';
			if ($_GET['event_kind']=='title') $link_id='1';
			if ($_GET['event_kind']=='lordship') $link_id='1';
			if ($_GET['event_kind']=='birth_declaration') $link_id='2';
			if ($_GET['event_kind']=='baptism_witness') $link_id='3';
			if ($_GET['event_kind']=='death_declaration') $link_id='4';
			if ($_GET['event_kind']=='burial_witness') $link_id='5';
			if ($_GET['event_kind']=='profession') $link_id='13';
			if ($_GET['event_kind']=='religion') $link_id='14';
			if ($_GET['event_kind']=='picture') $link_id='53';
			if ($_GET['event_kind']=='marriage_witness') $link_id='8';
			if ($_GET['event_kind']=='marriage_witness_rel') $link_id='10';
		}

		if (isset($_GET['event_add'])){
			if ($_GET['event_add']=='add_name') $link_id='1';
			if ($_GET['event_add']=='add_npfx') $link_id='1';
			if ($_GET['event_add']=='add_nsfx') $link_id='1';
			if ($_GET['event_add']=='add_nobility') $link_id='1';
			if ($_GET['event_add']=='add_title') $link_id='1';
			if ($_GET['event_add']=='add_lordship') $link_id='1';
			if ($_GET['event_add']=='add_birth_declaration') $link_id='2';
			if ($_GET['event_add']=='add_baptism_witness') $link_id='3';
			if ($_GET['event_add']=='add_death_declaration') $link_id='4';
			if ($_GET['event_add']=='add_burial_witness') $link_id='5';
			if ($_GET['event_add']=='add_profession') $link_id='13';
			if ($_GET['event_add']=='add_religion') $link_id='14';
			if ($_GET['event_add']=='add_picture') $link_id='53';
			if ($_GET['event_add']=='add_source_picture') $link_id='53';
			if ($_GET['event_add']=='add_marriage_picture') $link_id='53';
			if ($_GET['event_add']=='add_marriage_witness') $link_id='8';
			if ($_GET['event_add']=='add_marriage_witness_rel') $link_id='10';
		}

		$text.='
		<script type="text/javascript">
		function Show(el_id){
			// *** Hide or show item ***
			var arr = document.getElementsByClassName(\'row\'+el_id);
			for (i=0; i<arr.length; i++){
				arr[i].style.display="";
			}
			// *** Change [+] into [-] ***
			document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[-]";
		}
		</script>';

		$text.='<script>
			Show("'.$link_id.'");
		</script>';
	}

	return $text;

}   // end function show_event

}   // end class


function event_selection($event_gedcom){
	global $humo_option;
	$text='';

	if (!$event_gedcom){
		$text.='<optgroup label="'.__('Prefix').' - '.__('Suffix').' - '.__('Title').'">';
			$text.='<option value="NPFX">'.__('Prefix').': '.__('e.g. Lt. Cmndr.').'</option>';

			$event='NSFX'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
			$text.='<option value="NSFX"'.$selected.'>'.__('Suffix').': '.__('e.g. Jr.').'</option>';

			$event='nobility'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
			$text.='<option value="nobility"'.$selected.'>'.__('Title of Nobility').': '.__('e.g. Jhr., Jkvr.').'</option>';

			$event='title'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
			$text.='<option value="title"'.$selected.'>'.__('Title').': '.__('e.g. Prof., Dr.').'</option>';

			$event='lordship'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
			$text.='<option value="lordship"'.$selected.'>'.__('Title of Lordship').': '.__('e.g. Lord of Amsterdam').'</option>';
		$text.='</optgroup>';
		$text.='<optgroup label="'.__('Name').'">';
	}

	$event='NICK'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>NICK '.__('Nickname').'</option>';

	$event='_AKAN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_AKAN '.__('Also known as').'</option>';

	$event='_ALIA'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_ALIA '.__('alias name').'</option>';

	$event='_SHON'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_SHON '.__('Short name (for reports)').'</option>';

	$event='_ADPN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_ADPN '.__('Adopted name').'</option>';

	if($humo_option['admin_hebname']!="y" ) {  // display here if user didn't set to be displayed in main name section
		$event='_HEBN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
		$text.='<option value="'.$event.'"'.$selected.'>_HEBN '.__('Hebrew name').'</option>';
	}

	$event='_CENN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_CENN '.__('Census name').'</option>';

	$event='_MARN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_MARN '.__('Married name').'</option>';

	$event='_GERN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_GERN '.__('Given name').'</option>';

	$event='_FARN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_FARN '.__('Farm name').'</option>';

	$event='_BIRN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_BIRN '.__('Birth name').'</option>';

	$event='_INDN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_INDN '.__('Indian name').'</option>';

	$event='_FKAN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_FKAN '.__('Formal name').'</option>';

	$event='_CURN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_CURN '.__('Current name').'</option>';

	$event='_SLDN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_SLDN '.__('Soldier name').'</option>';

	$event='_RELN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_RELN '.__('Religious name').'</option>';

	$event='_OTHN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_OTHN '.__('Other name').'</option>';

	$event='_FRKA'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_FRKA '.__('Formerly known as').'</option>';

	$event='_RUFN'; $selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	$text.='<option value="'.$event.'"'.$selected.'>_RUFN '.__('German Rufname').'</option>';

	if (!$event_gedcom){
		$text.='</optgroup>';
	}

	return $text;
}

// *** Javascript for "search by file name of picture" feature ***
// March 2022: no longer in use
/*
echo '<script type="text/javascript">
	function Search_pic(idnum, picnr, picarr){
		var searchval = document.getElementById("inp_text_event" + idnum).value;
		searchval = searchval.toLowerCase();
		var countarr = 0;
		// *** delete existing full list ***
		document.getElementById("text_event" + idnum).options.length=0; 
		for (var countpics=0; countpics<picnr; countpics++){
			var picname = picarr[countpics].toLowerCase();
			if(picname.indexOf(searchval) != -1) {
				document.getElementById("text_event" + idnum).options[countarr]=new Option(picarr[countpics], picarr[countpics], true, false);
				countarr++;
			}
		}
	}
	</script>';
*/

// *** If profession is added, jump to profession part of screen ***
if (isset($_POST['event_event_profession']) AND $_POST['event_event_profession']!=''){
	echo '<script type="text/javascript">
		window.location = window.location.origin + window.location.pathname + "#profession";
	</script>';
}

// *** If religion is added, jump to religion part of screen ***
if (isset($_POST['event_event_religion']) AND $_POST['event_event_religion']!=''){
	echo '<script type="text/javascript">
		window.location = window.location.origin + window.location.pathname + "#religion";
	</script>';
}

?>