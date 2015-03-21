<?php
class editor_event_cls{

// *** Show event_kind text ***
function event_text($event_kind){
	global $language;
	if ($event_kind=='picture'){ $event_text=__('Picture/ Media'); }
	elseif ($event_kind=='profession'){ $event_text=__('Profession'); }
	elseif ($event_kind=='event'){ $event_text=__('Event'); }
	elseif ($event_kind=='birth_declaration'){ $event_text=__('birth declaration'); }
	elseif ($event_kind=='baptism_witness'){ $event_text=__('baptism witness'); }
	elseif ($event_kind=='death_declaration'){ $event_text=__('death declaration'); }
	elseif ($event_kind=='burial_witness'){ $event_text=__('burial witness'); }
	elseif ($event_kind=='name'){ $event_text=__('Name'); }
	elseif ($event_kind=='nobility'){ $event_text=__('Title of Nobility'); }
	elseif ($event_kind=='title'){ $event_text=__('Title'); }
	elseif ($event_kind=='adoption'){ $event_text=__('Adoption'); }
	elseif ($event_kind=='lordship'){ $event_text=__('Title of Lordship'); }
	elseif ($event_kind=='URL'){ $event_text=__('URL/ Internet link'); }
	elseif ($event_kind=='person_colour_mark'){ $event_text=__('Colour mark by person'); }
	elseif ($event_kind=='marriage_witness'){ $event_text= __('marriage witness'); }
	elseif ($event_kind=='marriage_witness_rel'){ $event_text= __('marriage witness (church)'); }
	else { $event_text=ucfirst($event_kind); }
	return $event_text;
}

// *** Show events ***
// *** REMARK: queries can be found in editor_inc.php! ***
function show_event($selected_events=''){
	global $dbh, $tree_id, $page, $tree_prefix, $pers_gedcomnumber, $marriage, $field_date, $field_text, $joomlastring;
	global $editor_cls, $path_prefix, $tree_pict_path;

	// *** Picture list for selecting pictures ***
	$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='".$tree_prefix."'");
	$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
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

	// *** Change line colour ***
	$change_bg_colour=' class="humo_color3"';

	$event_group='event_person=1';

	// *** Show all events EXCEPT for events allready processed by person data (profession etc.) ***
	if ($selected_events=='person'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'
			AND event_kind!='name'
			AND event_kind!='nobility'
			AND event_kind!='title'
			AND event_kind!='lordship'
			AND event_kind!='birth_declaration'
			AND event_kind!='baptism_witness'
			AND event_kind!='death_declaration'
			AND event_kind!='burial_witness'
			AND event_kind!='profession'
			AND event_kind!='picture'
			ORDER BY event_kind, event_order";
	}
	elseif ($selected_events=='name'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='name' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='name' ORDER BY event_order";
	}
	elseif ($selected_events=='nobility'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='nobility' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='nobility' ORDER BY event_order";
	}
	elseif ($selected_events=='title'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='title' ORDER BY event_order";
		$qry="SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='title' ORDER BY event_order";
	}
	elseif ($selected_events=='lordship'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='lordship' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='lordship' ORDER BY event_order";
	}
	elseif ($selected_events=='birth_declaration'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='birth_declaration' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='birth_declaration' ORDER BY event_order";
	}
	elseif ($selected_events=='baptism_witness'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='baptism_witness' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='baptism_witness' ORDER BY event_order";
	}
	elseif ($selected_events=='death_declaration'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='death_declaration' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='death_declaration' ORDER BY event_order";
	}
	elseif ($selected_events=='burial_witness'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='burial_witness' ORDER BY event_order";
		$qry="SELECT * FROM humo_events WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='burial_witness' ORDER BY event_order";
	}
	elseif ($selected_events=='profession'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='profession' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='profession' ORDER BY event_order";
	}
	elseif ($selected_events=='picture'){
		$event_group='event_person=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_person_id='".$pers_gedcomnumber."' AND event_kind='picture' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='picture' ORDER BY event_order";
	}
	elseif ($selected_events=='family'){
		$event_group='event_family=1';
		//$qry="SELECT * FROM ".$tree_prefix."events 
		$qry="SELECT * FROM humo_events 
			WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."'
			AND event_kind!='marriage_witness'
			AND event_kind!='marriage_witness_rel'
			AND event_kind!='picture'
			ORDER BY event_kind, event_order";
	}
	elseif ($selected_events=='marriage_witness'){
		$event_group='event_family=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_family_id='".$marriage."' AND event_kind='marriage_witness' ORDER BY event_kind, event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."' AND event_kind='marriage_witness' ORDER BY event_kind, event_order";
	}
	elseif ($selected_events=='marriage_witness_rel'){
		$event_group='event_family=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_family_id='".$marriage."' AND event_kind='marriage_witness_rel' ORDER BY event_kind, event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."' AND event_kind='marriage_witness_rel' ORDER BY event_kind, event_order";
	}
	elseif ($selected_events=='marriage_picture'){
		$event_group='event_family=1';
		//$qry="SELECT * FROM ".$tree_prefix."events WHERE event_family_id='".$marriage."' AND event_kind='picture' ORDER BY event_order";
		$qry="SELECT * FROM humo_events
			WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."' AND event_kind='picture' ORDER BY event_order";
	}
	$data_list_qry=$dbh->query($qry);

	// *** Show events by person ***
	if ($selected_events=='person'){
		echo '<tr><td style="border-right:0px;"><a name="event_person_link"><a href="#event_person_link" onclick="hideShow(51);"><span id="hideshowlink51">'.__('[+]').'</span></a> '.__('Events').'</td>';
		echo '<td style="border-right:0px;">';
			//echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_event">'.__('Add').'</a>';
		echo '</td><td style="border-left:0px;">';
			//$count_event=$dbh->query("SELECT * FROM ".$tree_prefix."events
			$count_event=$dbh->query("SELECT * FROM humo_events
				WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."'
				AND event_kind!='name'
				AND event_kind!='nobility'
				AND event_kind!='title'
				AND event_kind!='lordship'
				AND event_kind!='birth_declaration'
				AND event_kind!='baptism_witness'
				AND event_kind!='death_declaration'
				AND event_kind!='burial_witness'
				AND event_kind!='profession'
				AND event_kind!='picture'
				ORDER BY event_kind, event_order");
			$count=$count_event->rowCount();
			echo $count.' x '.__('Events');
		echo '. '.__('For items like:').' '.__('Event').', '.__('baptized as child').', '.__('depart').' '.__('etc.');
		echo '</td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show events by family ***
	if ($selected_events=='family'){
		//echo '<tr class="table_header"><td style="border-right:0px;"><a name="event_family_link"><a href="#event_family_link" onclick="hideShow(52);"><span id="hideshowlink52">'.__('[+]').'</span></a> '.__('Events').'</td>';
		echo '<tr class="humo_color"><td style="border-right:0px;"><a name="event_family_link"><a href="#event_family_link" onclick="hideShow(52);"><span id="hideshowlink52">'.__('[+]').'</span></a> '.__('Events').'</td>';
		echo '<td style="border-right:0px;">';
			//echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_event">'.__('Add').'</a>';
		echo '</td><td style="border-left:0px;">';
			//$count_event=$dbh->query("SELECT * FROM ".$tree_prefix."events
			$count_event=$dbh->query("SELECT * FROM humo_events
				WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."'
				AND event_kind!='marriage_witness'
				AND event_kind!='marriage_witness_rel'
				AND event_kind!='picture'
				ORDER BY event_kind, event_order");
			$count=$count_event->rowCount();
			echo $count.' x '.__('Events');
			echo '. '.__('For items like:').' '.__('Event').', '.__('Marriage contract').', '.__('Marriage license').', '.__('etc.');
			//	__('Marriage settlement')
			//	__('Marriage bond')
			//	__('Divorce filed')
			//	__('Annulled')
			//	__('Engaged')
			//	__('Sealed to spouse LDS')
		echo '</td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show name by person ***
	if ($selected_events=='name'){
		//echo '<tr style="display:none;" class="row1" name="row1">';
		echo '<tr style="display:none;" class="row1">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('Name').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_name">['.__('Add').']</a> ';
			echo __('Nickname').', '.__('alias name').', '.__('Adopted name').', '.__('Hebrew name').', '.__('etc.');
			echo '</td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show nobility by person ***
	if ($selected_events=='nobility'){
		//echo '<tr style="display:none;" class="row1" name="row1">';
		echo '<tr style="display:none;" class="row1">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('Title of Nobility').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_nobility">['.__('Add').']</a> '.__('e.g. Jhr., Jkvr.').'</td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show title by person ***
	if ($selected_events=='title'){
		echo '<tr style="display:none;" class="row1" name="row1">';
		//echo '<tr class="table_header" style="display:none;" id="row1" name="row1">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('Title').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_title">['.__('Add').']</a> '.__('e.g. Prof., Dr.').'</td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show lordship by person ***
	if ($selected_events=='lordship'){
		echo '<tr style="display:none;" class="row1" name="row1">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('Title of Lordship').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_lordship">['.__('Add').']</a> '.__('e.g. Lord of Amsterdam').'</td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show birth declaration by person ***
	if ($selected_events=='birth_declaration'){
		echo '<tr class="humo_color row2" style="display:none;" name="row2">';
		//echo '<tr class="table_header" style="display:none;" id="row2" name="row2">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('birth declaration').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_birth_declaration">['.__('Add').']</a></td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show baptism witness by person ***
	if ($selected_events=='baptism_witness'){
		echo '<tr style="display:none;" class="row3" name="row3">';
		//echo '<tr class="table_header" style="display:none;" id="row3" name="row3">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('baptism witness').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_baptism_witness">['.__('Add').']</a></td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show death declaration by person ***
	if ($selected_events=='death_declaration'){
		echo '<tr class="humo_color row4" style="display:none;" name="row4">';
		//echo '<tr class="table_header" style="display:none;" id="row4" name="row4">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('death declaration').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_death_declaration">['.__('Add').']</a></td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show burial witness by person ***
	if ($selected_events=='burial_witness'){
		echo '<tr style="display:none;" class="row5" name="row5">';
		//echo '<tr class="table_header" style="display:none;" id="row5" name="row5">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('burial witness').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_burial_witness">['.__('Add').']</a></td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show profession by person ***
	if ($selected_events=='profession'){
		//echo '<tr class="humo_color">';
		echo '<tr>';
		echo '<td style="border-right:0px;">';
			echo '<a name="profession"></a>';

			$count=$data_list_qry->rowCount();
			if ($count>0)
			echo '<a href="#profession" onclick="hideShow(50);"><span id="hideshowlink50">'.__('[+]').'</span></a> '; 

			echo __('Profession').'</td>';
		echo '<td style="border-right:0px;"></td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_profession#profession">['.__('Add').']</a> ';
			$text='';
			while($data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ)){
				if ($text) $text.=', ';
				$text.=$data_listDb->event_event;
			}
			echo $text;
		echo '</td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show pictures by person and family ***
	if ($selected_events=='picture' OR $selected_events=='marriage_picture'){
		echo '<tr class="humo_color">';
		echo '<td style="border-right:0px;">';
			echo '<a name="picture"></a>';

			$count_qry=$dbh->query($qry);
			$count=$count_qry->rowCount();
			if ($count>0)
			echo '<a href="#picture" onclick="hideShow(53);"><span id="hideshowlink53">'.__('[+]').'</span></a> ';

			echo __('Picture/ Media').'</td>';
		echo '<td style="border-right:0px;"></td>';
		echo '<td style="border-left:0px;">';
			$event_add='add_picture';
			//if ($selected_events=='marriage_picture') $event_add='add_marriage_picture';
			if ($selected_events=='marriage_picture') $event_add='add_marriage_picture&marriage_nr='.$marriage;
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add='.$event_add.'#picture">['.__('Add').']</a> ';
			//$text='';
			if ($count>1) { echo "&nbsp;&nbsp;".__('(Drag pictures to change display order)');}
			echo '<ul id="sortable_pic" class="sortable_pic handle_pic" style="width:auto">';
			while($data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ)){
				//if ($text) $text.=', ';
				//$text.=$data_listDb->event_event;
				echo '<li style="word-wrap:break-word;hight:auto;" id="'.$data_listDb->event_id.'" class="mediamove">';
				echo '<div style="position:relative">';
				if ($count>1) {
					echo '<div style="position:absolute;top:0;left:0">';
					$show_image= '<img src="'.CMS_ROOTPATH_ADMIN.'images/drag-icon.gif" style="float:left;vertical-align:top;height:16px;">'; echo $show_image;
					echo '</div>';
				}
				echo '<div style="overflow:hidden">';
				$thumb_prefix='';
				if (file_exists($path_prefix.$tree_pict_path.'thumb_'.$data_listDb->event_event)){ $thumb_prefix='thumb_'; }
				$extensions_check=substr($path_prefix.$tree_pict_path.$data_listDb->event_event,-3,3);
				if($extensions_check=="jpg" OR $extensions_check=="gif" OR $extensions_check=="png" OR $extensions_check=="bmp") {
					if (file_exists($path_prefix.$tree_pict_path.$thumb_prefix.$data_listDb->event_event))
						$show_image= '<img src="'.$path_prefix.$tree_pict_path.$thumb_prefix.$data_listDb->event_event.'" style="height:80px;">';
					else
						$show_image= '<img src="../images/thumb_missing-image.jpg" height="60px">';
					if (!$data_listDb->event_event) $show_image= '&nbsp;<img src="../images/thumb_missing-image.jpg" height="60px">';
					echo $show_image;
				}
				else {
					$ext = substr($data_listDb->event_event,-3,3);
					if($ext=="tif" OR $ext=="iff") { echo '<span style="font-size:80%">['.__('Format not supported')."]</span>"; }
					elseif($ext=="pdf") { echo '<img src="../images/pdf.jpeg" style="width:30px;height:30px;">';}
					elseif($ext=="doc") { echo '<img src="../images/msdoc.gif" style="width:30px;height:30px;">';}
					elseif($ext=="avi" OR $ext=="wmv" OR $ext=="mpg" OR $ext=="mov") { echo '<img src="../images/video-file.png" style="width:30px;height:30px;">'; }
					elseif($ext=="wma" OR $ext=="wav" OR $ext=="mp3" OR $ext=="mid" OR $ext=="ram" OR $ext==".ra" ) { echo '<img src="../images/audio.gif" style="width:30px;height:30px;">';}

					echo '<br><span style="font-size:85%">'.$data_listDb->event_event.'</span>';
				
				}
				// *** No picture selected yet, show dummy picture ***
				if (!$data_listDb->event_event) echo '<img src="../images/thumb_missing-image.jpg" height="60px">';
				echo '</div>';
				echo '</div>';
				echo '</li>';
			} 
			echo '</ul>';
			//echo $text;
			
			//echo '<script src="../include/jqueryui/js/jquery-1.8.0.min.js"></script>';
			//echo '<script src="../include/jqueryui/js/jquery.sortable.min.js"></script>';
			
			$data_list_qry=$dbh->query($qry);
			$data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ);

			?>
			<script>
				$('#sortable_pic').sortable().bind('sortupdate', function() {
					var mediastring = ""; 
					var media_arr = document.getElementsByClassName("mediamove"); 
					for (var z = 0; z < media_arr.length; z++) { 
						// create the new order after dragging to store in database with ajax
						mediastring = mediastring + media_arr[z].id + ";"; 
						// change the order numbers of the pics in the pulldown (that was generated before the drag
						// so that if one presses on delete before refresh the right pic will be deleted !!
					}
					mediastring = mediastring.substring(0, mediastring.length-1); // take off last ;
					
					var parnode = document.getElementById('pic_main_' + media_arr[0].id).parentNode; 
					var picdomclass = document.getElementsByClassName("pic_row2");
					var nextnode = picdomclass[(picdomclass.length)-1].nextSibling;

					for(var d=media_arr.length-1; d >=0 ; d--) {
						parnode.insertBefore(document.getElementById('pic_row2_' + media_arr[d].id),nextnode);
						nextnode = document.getElementById('pic_row2_' + media_arr[d].id);
						parnode.insertBefore(document.getElementById('pic_row1_' + media_arr[d].id),nextnode);
						nextnode = document.getElementById('pic_row1_' + media_arr[d].id);
						parnode.insertBefore(document.getElementById('pic_main_' + media_arr[d].id),nextnode);
						nextnode = document.getElementById('pic_main_' + media_arr[d].id);  
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
			</script>
			<?php
			
		echo '</td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show marriage witness by family ***
	if ($selected_events=='marriage_witness'){
		echo '<tr style="display:none;" class="row8" name="row8">';
		//echo '<tr class="table_header" style="display:none;" id="row8" name="row8">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('marriage witness').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_marriage_witness&marriage_nr='.$marriage.'#event_family_link">['.__('Add').']</a></td>';
		echo '<td></td>';
		echo '</tr>';
	}

	// *** Show marriage witness (church) by family ***
	if ($selected_events=='marriage_witness_rel'){
		echo '<tr style="display:none;" class="row10" name="row10">';
		//echo '<tr class="table_header" style="display:none;" id="row10" name="row10">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('marriage witness (religious)').'</td>';
		echo '<td style="border-left:0px;">';
			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;event_add=add_marriage_witness_rel&marriage_nr='.$marriage.'#event_family_link">['.__('Add').']</a></td>';
		echo '<td style="border-left:0px;"></td>';
		echo '</tr>';
	}

	$data_list_qry=$dbh->query($qry);

	while($data_listDb=$data_list_qry->fetch(PDO::FETCH_OBJ)){
		echo '<input type="hidden" name="event_id['.$data_listDb->event_id.']" value="'.$data_listDb->event_id.'">';

		$expand_link=''; $internal_link='#';
		if ($selected_events=='person'){
			//$change_bg_colour=' class="humo_color"';
			$expand_link=' style="display:none;" class="row51" name="row51"';
			$internal_link='#event_person_link';
		}
		if ($selected_events=='family'){
			//$change_bg_colour=' class="humo_color"';
			$expand_link=' style="display:none;" class="row52" name="row52"';
			$internal_link='#event_family_link';
		}
		if ($selected_events=='name'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row1" name="row1"';
			$internal_link='#';
		}
		if ($selected_events=='nobility'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row1" name="row1"';
		}
		if ($selected_events=='title'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row1" name="row1"';
		}
		if ($selected_events=='lordship'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row1" name="row1"';
		}
		if ($selected_events=='birth_declaration'){
			//$change_bg_colour='';
			$change_bg_colour=' class="humo_color"';
			$expand_link=' style="display:none;" class="row2 humo_color" name="row2"';
		}
		if ($selected_events=='baptism_witness'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row3" name="row3"';
		}
		if ($selected_events=='death_declaration'){
			//$change_bg_colour='';
			$change_bg_colour=' class="humo_color"';
			$expand_link=' style="display:none;" class="row4 humo_color" name="row4"';
		}
		if ($selected_events=='burial_witness'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row5" name="row5"';
		}
		if ($selected_events=='profession'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row50" name="row50"';
			$internal_link='#profession';
		}
		if ($selected_events=='picture' OR $selected_events=='marriage_picture'){
			//$change_bg_colour='';
			$change_bg_colour=' class="humo_color"';
			$expand_link=' style="display:none;" id="pic_main_'.$data_listDb->event_id.'" class="pic_main row53 humo_color" name="row53"';
			$internal_link='#picture';
		}
		if ($selected_events=='marriage_witness'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row8" name="row8"';
			$internal_link='#event_family_link';
		}
		if ($selected_events=='marriage_witness_rel'){
			//$change_bg_colour=' class="humo_color"';
			$change_bg_colour='';
			$expand_link=' style="display:none;" class="row10" name="row10"';
			$internal_link='#event_family_link';
		}

		echo '<tr'.$expand_link.'>';

		// *** Show name of event and [+] link ***
		echo '<td>';

			//echo '&nbsp;&nbsp;&nbsp;<a href="'.$internal_link.'" onclick="hideShow('.$data_listDb->event_id.'00);"><span id="hideshowlink'.$data_listDb->event_id.'00">'.__('[+]').'</span></a> ';
			//echo $this->event_text($data_listDb->event_kind).' #'.$data_listDb->event_order;

			echo '&nbsp;&nbsp;&nbsp;<a href="'.$internal_link.'" onclick="hideShow('.$data_listDb->event_id.'00);"><span id="hideshowlink'.$data_listDb->event_id.'00">'.__('[+]').'</span></a>';
			//echo ' #'.$data_listDb->event_order;

			echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;'.$event_group.'&amp;event_kind='.$data_listDb->event_kind.'&amp;event_drop='.
				$data_listDb->event_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="down"></a>';

			if ($data_listDb->event_kind !='picture'){
				// *** Count number of events ***
				if ($event_group=='event_person=1'){
					//$count_event=$dbh->query("SELECT * FROM ".$tree_prefix."events
					$count_event=$dbh->query("SELECT * FROM humo_events
						WHERE event_tree_id='".$tree_id."' AND event_person_id='".$pers_gedcomnumber."' AND event_kind='".$data_listDb->event_kind."'");
				}
				elseif ($event_group=='event_family=1'){
					//$count_event=$dbh->query("SELECT * FROM ".$tree_prefix."events
					$count_event=$dbh->query("SELECT * FROM humo_events
						WHERE event_tree_id='".$tree_id."' AND event_family_id='".$marriage."' AND event_kind='".$data_listDb->event_kind."'");
				}
				$count=$count_event->rowCount();

				// *** dummy is not really necessary, but otherwise it's not possible to click an arrow twice ***
				if ($data_listDb->event_order<$count){
					echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;'.$event_group.'&amp;event_down='.$data_listDb->event_order.'&amp;event_kind='.$data_listDb->event_kind.'&amp;dummy='.$data_listDb->event_id.$internal_link.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>';
				}
				else{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}

				// *** dummy is not really necessary, but otherwise it's not possible to click an arrow twice ***
				if ($data_listDb->event_order>1){
					echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;'.$event_group.'&amp;event_up='.$data_listDb->event_order.'&amp;event_kind='.$data_listDb->event_kind.'&amp;dummy='.$data_listDb->event_id.$internal_link.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="down"></a>';
				}
				else{
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			}
			echo '</td>';

			echo '<td style="border-right:0px;">';
			echo $this->event_text($data_listDb->event_kind);

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
					$show_image='';
					if (file_exists($path_prefix.$tree_pict_path.$thumb_prefix.$data_listDb->event_event))
						$show_image= '<img src="'.$path_prefix.$tree_pict_path.$thumb_prefix.$data_listDb->event_event.'" width="100px">';
					else
						$show_image= '<img src="../images/thumb_missing-image.jpg" height="100px">';

					if (!$data_listDb->event_event) $show_image= '&nbsp;<img src="../images/thumb_missing-image.jpg" height="80px">';
					echo $show_image;
				}
			}

		echo '</td>';


		// *** Witness and declaration persons ***
		if ($data_listDb->event_kind=='baptism_witness' OR $data_listDb->event_kind=='birth_declaration' OR
		$data_listDb->event_kind=='death_declaration' OR $data_listDb->event_kind=='burial_witness'){
			echo '<td style="border-left:0px;">';
			//witness_edit($data_listDb->event_event);
			witness_edit($data_listDb->event_event,'['.$data_listDb->event_id.']');
		}

		elseif ($data_listDb->event_kind=='picture'){
		
			// *** Show pull-down list pictures ***
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
			$family_parents=$dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' ORDER BY fam_gedcomnumber");
			while($family_parentsDb=$family_parents->fetch(PDO::FETCH_OBJ)){
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
		elseif ($data_listDb->event_kind=='lordship'){ echo ' '.__('e.g. Lord of Amsterdam'); }

		// *** Select type of event ***
		if ($data_listDb->event_kind=='event'){
			echo ' <select size="1" name="event_gedcom['.$data_listDb->event_id.']" style="width: 150px">';

			if ($selected_events=='person'){
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
			}

			if ($selected_events=='family'){
				// *** Marriage events ***
				event_option($data_listDb->event_gedcom,'EVEN');
				event_option($data_listDb->event_gedcom,'_MBON');
				event_option($data_listDb->event_gedcom,'MARC');
				event_option($data_listDb->event_gedcom,'MARL');
				event_option($data_listDb->event_gedcom,'MARS');
				event_option($data_listDb->event_gedcom,'DIVF');
				event_option($data_listDb->event_gedcom,'ANUL');
				event_option($data_listDb->event_gedcom,'ENGA');
				event_option($data_listDb->event_gedcom,'SLGS');
			}

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
			if ($event_group=='event_person=1'){
				// *** Calculate and show nr. of sources ***
				//$connect_qry="SELECT *
				//	FROM ".$tree_prefix."connections
				//	WHERE connect_kind='person' AND connect_sub_kind='event_source'
				//	AND connect_connect_id='".$data_listDb->event_id."'";
				$connect_qry="SELECT *
					FROM humo_connections
					WHERE connect_tree_id='".$tree_id."' AND connect_sub_kind='pers_event_source'
					AND connect_connect_id='".$data_listDb->event_id."'";
				$connect_sql=$dbh->query($connect_qry);

				//echo "&nbsp;<a href=\"".$internal_link."\" onClick=\"window.open('index.php?page=editor_sources&".$event_group."&connect_kind=person&connect_sub_kind=person_event_source&connect_connect_id=".$data_listDb->event_id."', '','width=800,height=500')\">".__('source');
				echo "&nbsp;<a href=\"".$internal_link."\" onClick=\"window.open('index.php?page=editor_sources&".$event_group."&connect_kind=person&connect_sub_kind=pers_event_source&connect_connect_id=".$data_listDb->event_id."', '','width=800,height=500')\">".__('source');
			}
			else{
				// *** Calculate and show nr. of sources ***
				//$connect_qry="SELECT *
				//	FROM ".$tree_prefix."connections
				//	WHERE connect_kind='family' AND connect_sub_kind='event_source'
				//	AND connect_connect_id='".$data_listDb->event_id."'";
				$connect_qry="SELECT *
					FROM humo_connections
					WHERE connect_tree_id='".$tree_id."' AND connect_sub_kind='fam_event_source'
					AND connect_connect_id='".$data_listDb->event_id."'";
				$connect_sql=$dbh->query($connect_qry);

				echo "&nbsp;<a href=\"".$internal_link."\" onClick=\"window.open('index.php?page=editor_sources&event_family=1&connect_kind=family&connect_sub_kind=fam_event_source&connect_connect_id=".$data_listDb->event_id."', '','width=800,height=500')\">".__('source');
			}
			echo ' ['.$connect_sql->rowCount().']</a>';
		echo '</td>';

		echo '</tr>';

		// *** Date and place line ***
		//$internal_link1='style="display:none;" id="row'.$data_listDb->event_id.'00" name="row'.$data_listDb->event_id.'00"';
		$pic_row1 = ""; 
		if ($selected_events=='picture' OR $selected_events=='marriage_picture'){ 
			$pic_row1 = ' id="pic_row1_'.$data_listDb->event_id.'"'; 
			$change_bg_colour = ' class="humo_color pic_row1 row'.$data_listDb->event_id.'00"';
		}
		else{
			// *** Date and place line for other events like profession and witnesses etc. ***
			if($change_bg_colour != '') { $change_bg_colour = substr($change_bg_colour,0,-1).' row'.$data_listDb->event_id.'00"'; }
			else { $change_bg_colour = ' class="row'.$data_listDb->event_id.'00"';}	
		}
		$internal_link1='style="display:none;" '.$pic_row1.' name="row'.$data_listDb->event_id.'00"';
		echo '<tr'.$change_bg_colour.' '.$internal_link1.'><td></td>';
			echo '<td style="border-right:0px;">'.__('date').'</td>';
			echo '<td style="border-left:0px;">'.$editor_cls->date_show($data_listDb->event_date,'event_date',"[$data_listDb->event_id]").' '.__('place').' <input type="text" name="event_place['.$data_listDb->event_id.']" placeholder="'.__('place').'" value="'.$data_listDb->event_place.'" size="'.$field_date.'">';
			echo '</td><td>';
		echo '</td></tr>';

		// *** Text by event ***
		$pic_row2 = ""; 
		if ($selected_events=='picture' OR $selected_events=='marriage_picture'){ 
			$pic_row2 = ' id="pic_row2_'.$data_listDb->event_id.'"'; 
			$change_bg_colour = ' class="humo_color pic_row2 row'.$data_listDb->event_id.'00"';
		}
		else{
			// *** Date and place line for other events like profession and witnesses etc. ***
			if($change_bg_colour != '') { $change_bg_colour = substr($change_bg_colour,0,-1).' row'.$data_listDb->event_id.'00"'; }
			else { $change_bg_colour = ' class="row'.$data_listDb->event_id.'00"'; }
		}
		$internal_link2='style="display:none;" '.$pic_row2.' name="row'.$data_listDb->event_id.'00"';
		echo '<tr'.$change_bg_colour.' '.$internal_link2.'><td></td>';
		echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="event_text['.$data_listDb->event_id.']" '.$field_text.'>'.
		$editor_cls->text_show($data_listDb->event_text).'</textarea>';
		echo '</td><td></td></tr>';

		if ($change_bg_colour!=' class="humo_color3"'){ $change_bg_colour=' class="humo_color3"'; }
			else{ $change_bg_colour=' class="humo_color2"'; }
	}

	if ($selected_events=='picture'){
		// *** Upload image ***
		//echo '<tr style="display:none;" id="row51" name="row51"><td class="table_header_large" colspan="4">';
		echo '<tr style="display:none;" class="row53" name="row53"><td class="table_header_large" colspan="4">';
			echo 'Upload new image (max: pic 2MB) or media (max: 49 MB):'.' <input type="file" name="photo_upload">';
			echo '<input type="submit" name="person_event_change" title="submit" value="'.__('Upload').'">';
		echo '</td></tr>';
	}

	// *** Add person event ***
	if ($selected_events=='person'){
		// *** Add person event ***
		echo '<tr bgcolor="#CCFFFF" style="display:none;" class="row51" name="row51"><td>'.__('Add event').'</td><td style="border-right:0px;"></td><td style="border-left:0px;">';
			echo '<select size="1" name="event_kind">';
				//echo '<option value="profession">'.__('Profession').'</option>';
				//echo '<option value="picture">'.__('Picture/ Media').'</option>';
				echo '<option value="event">'.__('Event').'</option>';
				//echo '<option value="birth_declaration">'.__('Birth Declaration').'</option>';
				//echo '<option value="baptism_witness">'.__('Baptism Witness').'</option>';
				//echo '<option value="death_declaration">'.__('Death Declaration').'</option>';
				//echo '<option value="burial_witness">'.__('Burial Witness').'</option>';
				//echo '<option value="name">'.__('Name').'</option>';
				//echo '<option value="nobility">'.__('Title of Nobility').'</option>';
				//echo '<option value="title">'.__('Title').'</option>';
				echo '<option value="adoption">'.__('Adoption').'</option>';
				//echo '<option value="lordship">'.__('Title of Lordship').'</option>';
				echo '<option value="URL">'.__('URL/ Internet link').'</option>';
				echo '<option value="person_colour_mark">'.__('Colour mark by person').'</option>';
			echo '</select>';
		echo'</td><td><input type="Submit" name="person_event_add" value="'.__('Add').'"></td><tr>';
	}

	// *** Add event ***
	if ($selected_events=='family'){
		echo '<tr bgcolor="#CCFFFF" style="display:none;" class="row52" name="row52"></td><td>'.__('Add event').'</td><td style="border-right:0px;"></td><td style="border-left:0px;">';
			echo '<select size="1" name="event_kind">';
				//echo '<option value="address">Address</option>';
				//echo '<option value="picture">Picture</option>';
				echo '<option value="event">'.__('Event').'</option>';
				//echo '<option value="marriage_witness">'.__('Marriage Witness').'</option>';
				//echo '<option value="marriage_witness_rel">'.__('Marriage Witness (church)').'</option>';
			echo '</select>';
		echo '</td><td><input type="Submit" name="marriage_event_add" value="'.__('Add').'"></td><tr>';
	}

	// *** Show events if save or arrow links are used ***
	if (isset($_GET['event_person']) OR isset($_GET['event_family']) OR isset($_GET['event_add'])){
		// *** Script voor expand and collapse of items ***

		$link_id='';
		if (isset($_GET['event_person']) AND $_GET['event_person']=='1') $link_id='51';
		if (isset($_GET['event_family']) AND $_GET['event_family']=='1') $link_id='52';
		if (isset($_GET['event_kind'])){
			if ($_GET['event_kind']=='name') $link_id='1';
			if ($_GET['event_kind']=='nobility') $link_id='1';
			if ($_GET['event_kind']=='title') $link_id='1';
			if ($_GET['event_kind']=='lordship') $link_id='1';
			if ($_GET['event_kind']=='birth_declaration') $link_id='2';
			if ($_GET['event_kind']=='baptism_witness') $link_id='3';
			if ($_GET['event_kind']=='death_declaration') $link_id='4';
			if ($_GET['event_kind']=='burial_witness') $link_id='5';
			if ($_GET['event_kind']=='profession') $link_id='50';
			if ($_GET['event_kind']=='picture') $link_id='53';
			if ($_GET['event_kind']=='marriage_witness') $link_id='8';
			if ($_GET['event_kind']=='marriage_witness_rel') $link_id='10';
		}
		
		if (isset($_GET['event_add'])){
			if ($_GET['event_add']=='add_name') $link_id='1';
			if ($_GET['event_add']=='add_nobility') $link_id='1';
			if ($_GET['event_add']=='add_title') $link_id='1';
			if ($_GET['event_add']=='add_lordship') $link_id='1';
			if ($_GET['event_add']=='add_birth_declaration') $link_id='2';
			if ($_GET['event_add']=='add_baptism_witness') $link_id='3';
			if ($_GET['event_add']=='add_death_declaration') $link_id='4';
			if ($_GET['event_add']=='add_burial_witness') $link_id='5';
			if ($_GET['event_add']=='add_profession') $link_id='50';
			if ($_GET['event_add']=='add_picture') $link_id='53';
			if ($_GET['event_add']=='add_marriage_witness') $link_id='8';
			if ($_GET['event_add']=='add_marriage_witness_rel') $link_id='10';
		}

		echo '
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

		echo '<script>
			Show("'.$link_id.'");
		</script>';
	}

}

}
?>