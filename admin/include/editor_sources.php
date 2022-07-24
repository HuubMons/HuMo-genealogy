<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

//$joomlapath=CMS_ROOTPATH_ADMIN.'include/';
include_once ("editor_cls.php");
$editor_cls = New editor_cls;

if (isset($_SESSION['admin_pers_gedcomnumber'])){ $pers_gedcomnumber=$_SESSION['admin_pers_gedcomnumber']; }
if (isset($_SESSION['admin_fam_gedcomnumber'])){ $marriage=$_SESSION['admin_fam_gedcomnumber']; }
if (isset($_SESSION['admin_address_gedcomnumber'])){ $address_gedcomnr=$_SESSION['admin_address_gedcomnumber']; }


//echo '<br>'.$pers_gedcomnumber;

// *** Needed for event sources ***
$connect_kind='';
if (isset($_GET['connect_kind'])) $connect_kind=$_GET['connect_kind'];
//if (isset($_POST['connect_kind'])) $connect_kind=$_POST['connect_kind'];

$connect_sub_kind='';
if (isset($_GET['connect_sub_kind'])) $connect_sub_kind=$_GET['connect_sub_kind'];
//if (isset($_POST['connect_sub_kind'])) $connect_sub_kind=$_POST['connect_sub_kind'];

// *** Needed for event sources ***
$connect_connect_id='';
if (isset($_GET['connect_connect_id']) AND $_GET['connect_connect_id']) $connect_connect_id=$_GET['connect_connect_id'];
//if (isset($_POST['connect_connect_id']) AND $_POST['connect_connect_id']) $connect_connect_id=$_POST['connect_connect_id'];

$event_link='';
if (isset($_POST['event_person']) OR isset($_GET['event_person']))
	$event_link='&event_person=1';
if (isset($_POST['event_family']) OR isset($_GET['event_family']))
	$event_link='&event_family=1';

$gedcom_date=strtoupper(date("d M Y"));
$gedcom_time=date("H:i:s");

$phpself2='index.php?page=editor_sources&connect_kind='.$connect_kind.'&connect_sub_kind='.$connect_sub_kind.'&connect_connect_id='.$connect_connect_id;
$phpself2.=$event_link;

// *** Process queries ***
include_once ("editor_inc.php");

// **************************
// *** Show source editor ***
// **************************
function show_source_header($source_header){
	//echo '<h2>'.$source_header.'</h2>';
	echo '<b>'.$source_header.'</b>';
}

if ($connect_sub_kind=='pers_name_source'){
	show_source_header(__('Name source'));
	echo source_edit("person","pers_name_source",$pers_gedcomnumber);
}

// *** Edit source by sex ***
if ($connect_sub_kind=='pers_sexe_source'){
	show_source_header(__('Source').' - '.__('Sex'));
	echo source_edit("person","pers_sexe_source",$pers_gedcomnumber);
}

// *** Edit source by birth ***
if ($connect_sub_kind=='pers_birth_source'){
	show_source_header(__('Source').' - '.ucfirst(__('born')));
	echo source_edit("person","pers_birth_source",$pers_gedcomnumber);
}

// *** Edit source by baptise ***
if ($connect_sub_kind=='pers_bapt_source'){
	show_source_header(__('Source').' - '.ucfirst(__('baptised')));
	echo source_edit("person","pers_bapt_source",$pers_gedcomnumber);
}

// *** Edit source by death ***
if ($connect_sub_kind=='pers_death_source'){
	show_source_header(__('Source').' - '.ucfirst(__('died')));
	echo source_edit("person","pers_death_source",$pers_gedcomnumber);
}

// *** Edit source by buried ***
if ($connect_sub_kind=='pers_buried_source'){
	show_source_header(__('Source').' - '.ucfirst(__('buried')));
	echo source_edit("person","pers_buried_source",$pers_gedcomnumber);
}

// *** Edit source by text ***
if ($connect_sub_kind=='pers_text_source'){
	show_source_header(__('text').' - '.__('source'));
	echo source_edit("person","pers_text_source",$pers_gedcomnumber);
}

// *** Edit source by person ***
if ($connect_sub_kind=='person_source'){
	show_source_header(__('Source').' - '.__('person'));
	echo source_edit("person","person_source",$pers_gedcomnumber);
}

// *** Edit source by address by person ***
if ($connect_sub_kind=='pers_address_source'){
	show_source_header(__('Source').' - '.__('Address'));
	echo source_edit("person","pers_address_source",$connect_connect_id);
}

// *** Edit source by living together ***
if ($connect_sub_kind=='fam_relation_source'){
	show_source_header(__('Source').' - '.__('Living together'));
	echo source_edit("family","fam_relation_source",$marriage);
}

// *** Edit source by fam_marr_notice ***
if ($connect_sub_kind=='fam_marr_notice_source'){
	show_source_header(__('Source').' - '.__('Notice of Marriage'));
	echo source_edit("family","fam_marr_notice_source",$marriage);
}

// *** Edit source by fam_marr ***
if ($connect_sub_kind=='fam_marr_source'){
	show_source_header(__('Source').' - '.__('Marriage'));
	echo source_edit("family","fam_marr_source",$marriage);
}

// *** Edit source by fam_church_notice ***
if ($connect_sub_kind=='fam_marr_church_notice_source'){
	show_source_header(__('Source').' - '.__('Religious Notice of Marriage'));
	echo source_edit("family","fam_marr_church_notice_source",$marriage);
}

// *** Edit source by fam_marr_church ***
if ($connect_sub_kind=='fam_marr_church_source'){
	show_source_header(__('Source').' - '.__('Religious Marriage'));
	echo source_edit("family","fam_marr_church_source",$marriage);
}

// *** Edit source by fam_div ***
if ($connect_sub_kind=='fam_div_source'){
	show_source_header(__('Source').' - '.__('Divorce'));
	echo source_edit("family","fam_div_source",$marriage);
}

// *** Edit source by fam_text ***
if ($connect_sub_kind=='fam_text_source'){
	show_source_header(__('Source').' - '.__('text'));
	echo source_edit("family","fam_text_source",$marriage);
}

// *** Edit source by family ***
if ($connect_sub_kind=='family_source'){
	show_source_header(__('Source').' - '.__('family'));
	echo source_edit("family","family_source",$marriage);
}

// *** Edit source by address by family ***
if ($connect_sub_kind=='fam_address_source'){
	show_source_header(__('Source').' - '.__('Address'));
	echo source_edit("family","fam_address_source",$connect_connect_id);
}

// *** Edit source by address ***
if ($connect_sub_kind=='address_source'){
	show_source_header(__('Source').' - '.__('Address'));
	echo source_edit("address","address_source",$address_gedcomnr);
	echo '<p>';
}

// *** Edit source by person event ***
//if ($connect_sub_kind=='person_event_source' OR ($connect_kind=='person' AND $connect_sub_kind=='event_source')){
//if ($connect_sub_kind=='person_event_source'){
if ($connect_sub_kind=='pers_event_source'){
	show_source_header(__('source').' - '.__('Event'));
	echo source_edit("person","pers_event_source",$connect_connect_id);
	echo '<p>';
}

// *** Edit source by family event ***
//if ($connect_sub_kind=='fam_event_source' OR ($connect_kind=='family' AND $connect_sub_kind=='event_source')){
if ($connect_sub_kind=='fam_event_source'){
	show_source_header(__('source').' - '.__('Event'));
	echo source_edit("family","fam_event_source",$connect_connect_id);
	echo '<p>';
}

// *** SOURCE EDIT FUNCTION ***
function source_edit($connect_kind, $connect_sub_kind, $connect_connect_id){
	global $dbh, $tree_id, $language, $page, $phpself2, $joomlastring, $marriage;
	global $editor_cls, $field_date;

	global $db_functions;
	$db_functions->set_tree_id($tree_id);

	//$text='<p>'.__('<b>Sourcerole</b>: e.g. Writer, Brother, Sister, Father. <b>Page</b>: page in source.');
	$text='';

	//$text.= '<table class="humo standard" border="1">';
	$text.= '<table class="humo" border="1">';

	$text.='<form method="POST" action="'.$phpself2.'">';
	$text.='<input type="hidden" name="page" value="'.$page.'">';
	if (isset($_POST['event_person']) OR isset($_GET['event_person']))
		$text.='<input type="hidden" name="event_person" value="1">';
	if (isset($_POST['event_family']) OR isset($_GET['event_family']))
		$text.='<input type="hidden" name="event_family" value="1">';

	//$text.= '<tr class="table_header_large">';
	//	$text.= '<th>'.__('Source').'</th>';
	//	$text.= '<th style="border-right:0px;"><br></th>';
	//	$text.= '<th><input type="submit" name="submit" title="submit" value="'.__('Save').'"></th>';
	//$text.= '</tr>';

	// *** Search for all connected sources ***
	$connect_sql = $db_functions->get_connections_connect_id($connect_kind,$connect_sub_kind,$connect_connect_id);
	$nr_sources=count($connect_sql);
	$change_bg_colour=false;
	foreach ($connect_sql as $connectDb){
		//$source_name=$connectDb->connect_id;

		$text.='<input type="hidden" name="connect_change['.$connectDb->connect_id.']" value="'.$connectDb->connect_id.'">';
		$text.='<input type="hidden" name="connect_connect_id['.$connectDb->connect_id.']" value="'.$connectDb->connect_connect_id.'">';
		if (isset($marriage)){ $text.='<input type="hidden" name="marriage_nr['.$connectDb->connect_id.']" value="'.$marriage.'">'; }
		$text.='<input type="hidden" name="connect_kind['.$connectDb->connect_id.']" value="'.$connect_kind.'">';
		$text.='<input type="hidden" name="connect_sub_kind['.$connectDb->connect_id.']" value="'.$connect_sub_kind.'">';
		$text.='<input type="hidden" name="connect_item_id['.$connectDb->connect_id.']" value="">';

		$text.= '<tr class="table_header_large">';
			//$text.= '<th>'.__('Source').'</th>';
			//$text.= '<th style="border-right:0px;"><br></th>';

			$text.= '<td style="border-right:0px;"><b>'.__('Source').'</b>';
			$text.=' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;connect_drop='.$connectDb->connect_id;
				// *** Needed for events **
				$text.='&amp;connect_kind='.$connect_kind;
				$text.='&amp;connect_sub_kind='.$connect_sub_kind;
				$text.='&amp;connect_connect_id='.$connect_connect_id;
				if (isset($_POST['event_person']) OR isset($_GET['event_person'])){
					$text.='&amp;event_person=1';
				}
				if (isset($_POST['event_family']) OR isset($_GET['event_family'])){
					$text.='&amp;event_family=1';
				}
				if (isset($marriage)){
					$text.='&amp;marriage_nr='.$marriage;
				}
				$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="remove"></a>';

				if ($connectDb->connect_order<$nr_sources){
					$text.= ' <a href="index.php?'.$joomlastring.'page='.$page.
					'&amp;connect_down='.$connectDb->connect_id.
					'&amp;connect_kind='.$connectDb->connect_kind.
					'&amp;connect_sub_kind='.$connectDb->connect_sub_kind.
					'&amp;connect_connect_id='.$connectDb->connect_connect_id.
					'&amp;connect_order='.$connectDb->connect_order;
					if (isset($_POST['event_person']) OR isset($_GET['event_person'])){
						$text.='&amp;event_person=1';
					}
					if (isset($_POST['event_family']) OR isset($_GET['event_family'])){
						$text.='&amp;event_family=1';
					}
					if (isset($marriage)){
						$text.='&amp;marriage_nr='.$marriage;
					}
					$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>';
				}
				else{
					//$text.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					$text.= '&nbsp;&nbsp;&nbsp;';
				}

				if ($connectDb->connect_order>1){
					$text.= ' <a href="index.php?'.$joomlastring.'page='.$page.
					'&amp;connect_up='.$connectDb->connect_id.
					'&amp;connect_kind='.$connectDb->connect_kind.
					'&amp;connect_sub_kind='.$connectDb->connect_sub_kind.
					'&amp;connect_connect_id='.$connectDb->connect_connect_id.
					'&amp;connect_order='.$connectDb->connect_order;
					if (isset($_POST['event_person']) OR isset($_GET['event_person'])){
						$text.='&amp;event_person=1';
					}
					if (isset($_POST['event_family']) OR isset($_GET['event_family'])){
						$text.='&amp;event_family=1';
					}
					if (isset($marriage)){
						$text.='&amp;marriage_nr='.$marriage;
					}
					$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="down"></a>';
				}
				else{
					//$text.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			$text.= '</td>';


			$text.= '<th><input type="submit" name="submit" title="submit" value="'.__('Save').'"></th>';
		$text.= '</tr>';


		$color= ''; if ($change_bg_colour==true){ $color = ' class="humo_color"'; }
		$text.= '<tr'.$color.'>';

			$text.='<td style="vertical-align:top; min-width:100px">'.__('Source').'<br>';
				$text.='<div style="margin-top:3px;">'.__('Title').'</div>';
				$text.='<div style="margin-top:3px;">'.__('Date').'</div>';
				$text.='<div style="margin-top:3px;">'.__('Text').'</div>';
				//$text.='<div style="margin-top:50px;">'.__('Own code').'</div>';
				$text.='<div style="margin-top:50px;">'.__('Sourcerole').'</div>';
				$text.='<div style="margin-top:3px;">'.__('Date').'</div>';
				$text.='<div style="margin-top:3px;">'.__('Extra text').'</div>';
			$text.='</td>';

			//$text.='<td colspan="2" style="border-left:0px; border-right:0px;">';
			$text.='<td style="vertical-align:top;">';
			//$text.='<br>';

			$text.='<div style="border: 2px solid red">';

				if ($connectDb->connect_source_id!=''){
					$text.='<input type="hidden" name="connect_source_id['.$connectDb->connect_id.']" value="'.$connectDb->connect_source_id.'">';
					$text.=__('Source GEDCOM number:').' '.$connectDb->connect_source_id.'.&nbsp;&nbsp;&nbsp;&nbsp;';

					$sourceDb=$db_functions->get_source ($connectDb->connect_source_id);

					$text.=__('Own code').' <input type="text" name="source_refn['.$connectDb->connect_id.']" placeholder="'.__('Own code').'" value="'.htmlspecialchars($sourceDb->source_refn).'" size="15">';

					$text.='<input type="hidden" name="source_id['.$connectDb->connect_id.']" value="'.$sourceDb->source_id.'">';

					//$checked=''; if ($sourceDb->source_shared) $checked=' checked';
					//$text.='<input type="checkbox" name="source_shared['.$connectDb->connect_id.']" value="no_data"'.$checked.'> '.__('Shared source');

					/*
					// *** HELP POPUP for source ***
					$rtlmarker="ltr";
					$text.='&nbsp;<div class="fonts '.$rtlmarker.'sddm" style="display:inline;">';
						$text.='<a href="#" style="display:inline" ';
						//$text.='onmouseover="mopen(event,\'help_source_shared\',100,250)"';
						$text.='onmouseover="mopen(event,\'help_source_shared\',0,0)"';
						$text.='onmouseout="mclosetime()">';
							$text.='<img src="../images/help.png" height="16" width="16">';
						$text.='</a>';
						$text.='<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:'.$rtlmarker.'" id="help_source_shared" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
							$text.='<b>'.__('A shared source can be connected to multiple persons, relations or other items.').'</b><br>';
						$text.='</div>';
					$text.='</div><br>';
					*/

					$text.='<br><input type="text" name="source_title['.$connectDb->connect_id.']" value="'.htmlspecialchars($sourceDb->source_title).'" size="60" placeholder="'.__('Title').'"><br>';

					$field_date=12; // Size of date field.
					$text.= $editor_cls->date_show($sourceDb->source_date,'source_date',"[$connectDb->connect_id]");

					$text.=' '.ucfirst(__('place')).' <input type="text" name="source_place['.$connectDb->connect_id.']" placeholder="'.ucfirst(__('place')).'" value="'.htmlspecialchars($sourceDb->source_place).'" size="15"><br>';

					//$field_text='style="height: 60px; width:550px"';
					$field_text='style="height: 60px; width:600px"';
					$text.= '<textarea rows="2" name="source_text['.$connectDb->connect_id.']" '.$field_text.' placeholder="'.__('Text').'">'.
						$editor_cls->text_show($sourceDb->source_text).'</textarea><br>';

					//$text.='<input type="text" name="source_refn['.$connectDb->connect_id.']" placeholder="'.__('Own code').'" value="'.htmlspecialchars($sourceDb->source_refn).'" size="15">';

				}
				else{

					$source_search=''; if (isset($_POST['source_search'])){ $source_search=safe_text_db($_POST['source_search']); }
					$text.='<input type="text" class="fonts" name="source_search" value="'.$source_search.'" size="20" placeholder="'.__('Search existing source').'">';
					$text.=' <input class="fonts" type="submit" value="'.__('Search').'">';

					// *** Source: pull-down menu ***
					//$source_qry=$dbh->query("SELECT * FROM humo_sources
					//	WHERE source_tree_id='".safe_text_db($tree_id)."' AND source_shared='1' ORDER BY source_title");
					$qry="SELECT * FROM humo_sources
						WHERE source_tree_id='".safe_text_db($tree_id)."'";
					if (isset($_POST['source_search'])){
						$qry.=" AND ( source_title LIKE '%".safe_text_db($_POST['source_search'])."%' OR (source_title='' AND source_text LIKE '%".safe_text_db($source_search)."%') )";
					}
					//$qry.=" ORDER BY source_title";
					$qry.=" ORDER BY IF (source_title!='',source_title,source_text)";

					$source_qry=$dbh->query($qry);

					$text.='<select size="1" name="connect_source_id['.$connectDb->connect_id.']" style="width: 300px">';
					//$text.='<option value="">'.__('Select shared source').':</option>';
					$text.='<option value="">'.__('Select existing source').':</option>';
					while ($sourceDb=$source_qry->fetch(PDO::FETCH_OBJ)){
						$selected='';
						if($connectDb->connect_source_id != '') {
							if ($sourceDb->source_gedcomnr==$connectDb->connect_source_id){ $selected=' SELECTED'; }
						}
						$text.='<option value="'.@$sourceDb->source_gedcomnr.'"'.$selected.'>';
							//@$sourceDb->source_title.' ['.@$sourceDb->source_gedcomnr.']</option>'."\n";
							if ($sourceDb->source_title){
								$text.=$sourceDb->source_title;
							} else {
								$text.=substr($sourceDb->source_text,0,40);
								if (strlen($sourceDb->source_text)>40) $text.='...';
							}
							$text.=' ['.@$sourceDb->source_gedcomnr.']</option>'."\n";
					}
					$text.='</select>';

					$text.= '&nbsp;&nbsp;<input type="submit" name="submit" title="submit" value="'.__('Select').'">';

					// *** Add new source ***
					$text.='<br>'.__('Or:').' ';
					$text.= '<a href="index.php?'.$joomlastring.'page='.$page.'
					&amp;source_add2=1
					&amp;connect_id='.$connectDb->connect_id.'
					&amp;connect_order='.$connectDb->connect_order.'
					&amp;connect_kind='.$connectDb->connect_kind.'
					&amp;connect_sub_kind='.$connectDb->connect_sub_kind.'
					&amp;connect_connect_id='.$connectDb->connect_connect_id;
					if (isset($_POST['event_person']) OR isset($_GET['event_person'])){
						$text.='&amp;event_person=1';
					}
					if (isset($_POST['event_family']) OR isset($_GET['event_family'])){
						$text.='&amp;event_family=1';
					}
					$text.='#addresses">'.__('add new source').'</a> ';

				}

			$text.='</div>';

			if ($connectDb->connect_source_id!=''){

				$text.=' <input type="text" name="connect_role['.$connectDb->connect_id.']" placeholder="'.__('Sourcerole').'" value="'.htmlspecialchars($connectDb->connect_role).'" size="6">';
				// *** HELP POPUP ***
				$rtlmarker="ltr";
				$text.='<div class="fonts '.$rtlmarker.'sddm" style="display:inline;">';
					$text.='<a href="#" style="display:inline" ';
					//$text.='onmouseover="mopen(event,\''.$connectDb->connect_id.'help_sourcerole\',100,400)"';
					$text.='onmouseover="mopen(event,\''.$connectDb->connect_id.'help_sourcerole\',1,150)"';
					$text.='onmouseout="mclosetime()">';
						$text.='<img src="../images/help.png" height="16" width="16">';
					$text.='</a>';
					//$text.='<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:'.$rtlmarker.'" id="help_sourcerole" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
					$text.='<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:'.$rtlmarker.'" id="'.$connectDb->connect_id.'help_sourcerole" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
						$text.=__('e.g. Writer, Brother, Sister, Father.').'<br>';
					$text.='</div>';
				$text.='</div>';

				$text.=' '.__('Page').' <input type="text" name="connect_page['.$connectDb->connect_id.']" placeholder="'.__('Page').'" value="'.$connectDb->connect_page.'" size="6">';
				// *** HELP POPUP ***
				$rtlmarker="ltr";
				$text.='<div class="fonts '.$rtlmarker.'sddm" style="display:inline;">';
					$text.='<a href="#" style="display:inline" ';
					//$text.='onmouseover="mopen(event,\''.$connectDb->connect_id.'help_sourcerole\',100,400)"';
					$text.='onmouseover="mopen(event,\''.$connectDb->connect_id.'help_sourcepage\',1,150)"';
					$text.='onmouseout="mclosetime()">';
						$text.='<img src="../images/help.png" height="16" width="16">';
					$text.='</a>';
					//$text.='<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:'.$rtlmarker.'" id="help_sourcerole" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
					$text.='<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:'.$rtlmarker.'" id="'.$connectDb->connect_id.'help_sourcepage" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
						$text.=__('Page in source.').'<br>';
					$text.='</div>';
				$text.='</div>';

				// *** Quality ***
				$text.=' <select size="1" name="connect_quality['.$connectDb->connect_id.']" style="width: 300px">';
				$text.='<option value="">'.ucfirst(__('quality: default')).'</option>';
				$selected=''; if ($connectDb->connect_quality=='0'){ $selected=' SELECTED'; }
				$text.='<option value="0"'.$selected.'>'.ucfirst(__('quality: unreliable evidence or estimated data')).'</option>';
				$selected=''; if ($connectDb->connect_quality=='1'){ $selected=' SELECTED'; }
				$text.='<option value="1"'.$selected.'>'.ucfirst(__('quality: questionable reliability of evidence')).'</option>';
				$selected=''; if ($connectDb->connect_quality=='2'){ $selected=' SELECTED'; }
				$text.='<option value="2"'.$selected.'>'.ucfirst(__('quality: data from secondary evidence')).'</option>';
				$selected=''; if ($connectDb->connect_quality=='3'){ $selected=' SELECTED'; }
				$text.='<option value="3"'.$selected.'>'.ucfirst(__('quality: data from direct source')).'</option>';
				$text.='</select><br>';

				$field_date=12; // Size of date field.
				$text.= $editor_cls->date_show($connectDb->connect_date,'connect_date',"[$connectDb->connect_id]");
				$text.=' '.ucfirst(__('place')).' <input type="text" name="connect_place['.$connectDb->connect_id.']" placeholder="'.ucfirst(__('place')).'" value="'.htmlspecialchars($connectDb->connect_place).'" size="15">';

				// *** Extra text by shared source ***
				$field_text='style="height: 20px; width:550px"';
				$text.= '<br><textarea rows="2" name="connect_text['.$connectDb->connect_id.']" placeholder="'.__('Extra text by source').'" '.$field_text.'>'.$editor_cls->text_show($connectDb->connect_text).'</textarea>';

			}
			else{
				$text.='<input type="hidden" name="connect_role['.$connectDb->connect_id.']" value="">';
				$text.='<input type="hidden" name="connect_page['.$connectDb->connect_id.']" value="">';
				$text.='<input type="hidden" name="connect_quality['.$connectDb->connect_id.']" value="">';
				$text.='<input type="hidden" name="connect_text['.$connectDb->connect_id.']" value="">';
			}

		$text.='</td></tr>';


// *** Picture by source ***


		//$text.='<tr class="table_header_large" style="border-top:solid 2px #000000;"><td colspan="4"><br></td></tr>';
 
 		if ($change_bg_colour==true){ $change_bg_colour=false; }
			else{ $change_bg_colour=true; }
	}

	$text.='</form>';

	//$text.='<tr><td colspan="4"><br></td></tr>';

	//$text.='<tr class="table_header_large" style="border-top:solid 2px #000000;"><td colspan="4"><br></td></tr>';
	//$text.='<tr class="table_header_large" style="border-top:solid 2px #000000;"><td colspan="3"><br></td></tr>';

	// *** Add new source connection ***
	if (!isset($_POST['connect_add'])){
		$text.='<tr bgcolor="#CCFFFF" style="border-top:solid 2px #000000;"><td>'.__('Add').'</td>';
			//$text.='<td style="border-right:0px;"></td>';
			//$text.='<td style="border-left:0px;"></td>';
			$text.='<th>';
				$text.='<form method="POST" action="'.$phpself2.'">';
				$text.='<input type="hidden" name="page" value="'.$page.'">';

				if (isset($_POST['event_person']) OR isset($_GET['event_person'])){
					$text.='<input type="hidden" name="event_person" value="1">';
				}
				if (isset($_POST['event_family']) OR isset($_GET['event_family'])){
					$text.='<input type="hidden" name="event_family" value="1">';
				}

				$text.='<input type="hidden" name="connect_kind" value="'.$connect_kind.'">';
				$text.='<input type="hidden" name="connect_sub_kind" value="'.$connect_sub_kind.'">';
				$text.='<input type="hidden" name="connect_connect_id" value="'.$connect_connect_id.'">';

				if (isset($marriage)){ $text.='<input type="hidden" name="marriage_nr" value="'.$marriage.'">'; }
				//echo '<tr bgcolor="#CCFFFF"><td>'.__('Add').'</td><td colspan="2">';

				if ($nr_sources>0){
					$text.=' <input type="Submit" name="connect_add" value="'.__('Add another source').'">';
				}
				else{
					$text.=' <input type="Submit" name="connect_add" value="'.__('Add source').'">';
				}

				$text.='</form>';
			$text.='</th>';
		$text.='</tr>';
	}

	$text.='</table>';
	$text.='<p>'; // some extra space below table.

	return $text;
}
?>