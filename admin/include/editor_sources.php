<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

//$joomlapath=CMS_ROOTPATH_ADMIN.'include/';
include_once ("editor_cls.php");
$editor_cls = New editor_cls;

if (isset($_SESSION['admin_tree_id'])){ $tree_id=$_SESSION['admin_tree_id']; }
if (isset($_SESSION['admin_pers_gedcomnumber'])){ $pers_gedcomnumber=$_SESSION['admin_pers_gedcomnumber']; }
if (isset($_SESSION['admin_fam_gedcomnumber'])){ $marriage=$_SESSION['admin_fam_gedcomnumber']; }
if (isset($_SESSION['admin_address_gedcomnumber'])){ $address_id=$_SESSION['admin_address_gedcomnumber']; }

// *** Needed for event sources ***
$connect_kind='';
if (isset($_GET['connect_kind'])) $connect_kind=$_GET['connect_kind'];
//if (isset($_POST['connect_kind'])) $connect_kind=$_POST['connect_kind'];

$connect_sub_kind='';
//if (isset($_POST['connect_sub_kind'])) $connect_sub_kind=$_POST['connect_sub_kind'];
if (isset($_GET['connect_sub_kind'])) $connect_sub_kind=$_GET['connect_sub_kind'];

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

if ($connect_sub_kind=='pers_name_source'){
	echo '<h2>'.__('Name source').'</h2>';
	echo source_edit("person","pers_name_source",$pers_gedcomnumber);
}

// *** Edit source by sex ***
if ($connect_sub_kind=='pers_sexe_source'){
	echo '<h2>'.__('source').' - '.__('Sex').'</h2>';
	echo source_edit("person","pers_sexe_source",$pers_gedcomnumber);
}

// *** Edit source by birth ***
if ($connect_sub_kind=='pers_birth_source'){
	echo '<h2>'.__('source').' - '.ucfirst(__('born')).'</h2>';
	echo source_edit("person","pers_birth_source",$pers_gedcomnumber);
}

// *** Edit source by baptise ***
if ($connect_sub_kind=='pers_bapt_source'){
	echo '<h2>'.__('source').' - '.ucfirst(__('baptised')).'</h2>';
	echo source_edit("person","pers_bapt_source",$pers_gedcomnumber);
}

// *** Edit source by death ***
if ($connect_sub_kind=='pers_death_source'){
	echo '<h2>'.__('source').' - '.ucfirst(__('died')).'</h2>';
	echo source_edit("person","pers_death_source",$pers_gedcomnumber);
}

// *** Edit source by buried ***
if ($connect_sub_kind=='pers_buried_source'){
	echo '<h2>'.__('source').' - '.ucfirst(__('buried')).'</h2>';
	echo source_edit("person","pers_buried_source",$pers_gedcomnumber);
}

// *** Edit source by text ***
if ($connect_sub_kind=='pers_text_source'){
	echo '<h2>'.__('text').' - '.__('source').'</h2>';
	echo source_edit("person","pers_text_source",$pers_gedcomnumber);
}

// *** Edit source by person ***
if ($connect_sub_kind=='person_source'){
	echo '<h2>'.__('source').' - '.__('person').'</h2>';
	echo source_edit("person","person_source",$pers_gedcomnumber);
}

// *** Edit source by address by person ***
if ($connect_sub_kind=='pers_address_source'){
	echo '<h2>'.__('source').' - '.__('Address').'</h2>';
	//echo source_edit("person","pers_address_source",$pers_gedcomnumber);
	echo source_edit("person","pers_address_source",$connect_connect_id);
}

// *** Edit source by relation ***
if ($connect_sub_kind=='fam_relation_source'){
	echo '<h2>'.__('source').' - '.__('Living together').'</h2>';
	echo source_edit("family","fam_relation_source",$marriage);
}

// *** Edit source by fam_marr_notice ***
if ($connect_sub_kind=='fam_marr_notice_source'){
	echo '<h2>'.__('source').' - '.__('Notice of Marriage').'</h2>';
	echo source_edit("family","fam_marr_notice_source",$marriage);
}

// *** Edit source by fam_marr ***
if ($connect_sub_kind=='fam_marr_source'){
	echo '<h2>'.__('source').' - '.__('Marriage').'</h2>';
	echo source_edit("family","fam_marr_source",$marriage);
}

// *** Edit source by fam_church_notice ***
if ($connect_sub_kind=='fam_marr_church_notice_source'){
	echo '<h2>'.__('source').' - '.__('Religious Notice of Marriage').'</h2>';
	echo source_edit("family","fam_marr_church_notice_source",$marriage);
}

// *** Edit source by fam_marr_church ***
if ($connect_sub_kind=='fam_marr_church_source'){
	echo '<h2>'.__('source').' - '.__('Religious Marriage').'</h2>';
	echo source_edit("family","fam_marr_church_source",$marriage);
}

// *** Edit source by fam_div ***
if ($connect_sub_kind=='fam_div_source'){
	echo '<h2>'.__('source').' - '.__('Divorce').'</h2>';
	echo source_edit("family","fam_div_source",$marriage);
}

// *** Edit source by fam_text ***
if ($connect_sub_kind=='fam_text_source'){
	echo '<h2>'.__('source').' - '.__('text').'</h2>';
	echo source_edit("family","fam_text_source",$marriage);
}

// *** Edit source by family ***
if ($connect_sub_kind=='family_source'){
	echo '<h2>'.__('source').' - '.__('family').'</h2>';
	echo source_edit("family","family_source",$marriage);
}

// *** Edit source by address by person ***
if ($connect_sub_kind=='fam_address_source'){
	echo '<h2>'.__('source').' - '.__('Address').'</h2>';
	//echo source_edit("person","pers_address_source",$pers_gedcomnumber);
	echo source_edit("family","fam_address_source",$connect_connect_id);
}

// *** Edit source by address ***
if ($connect_sub_kind=='address_source'){
	echo '<h2>'.__('source').' - '.__('Address').': </h2>';
	echo source_edit("address","address_source",$address_id);
	echo '<p>';
}

// *** Edit source by person event ***
//if ($connect_sub_kind=='person_event_source' OR ($connect_kind=='person' AND $connect_sub_kind=='event_source')){
//if ($connect_sub_kind=='person_event_source'){
if ($connect_sub_kind=='pers_event_source'){
	echo '<h2>'.__('source').' - '.__('Event').'</h2>';
	echo source_edit("person","pers_event_source",$connect_connect_id);
	echo '<p>';
}

// *** Edit source by family event ***
//if ($connect_sub_kind=='fam_event_source' OR ($connect_kind=='family' AND $connect_sub_kind=='event_source')){
if ($connect_sub_kind=='fam_event_source'){
	echo '<h2>'.__('source').' - '.__('Event').'</h2>';
	echo source_edit("family","fam_event_source",$connect_connect_id);
	echo '<p>';
}

// *** SOURCE EDIT FUNCTION ***
function source_edit($connect_kind, $connect_sub_kind, $connect_connect_id){
	global $dbh, $tree_id, $language, $page, $phpself2, $joomlastring, $marriage;
	global $editor_cls, $field_date;

	// *** Explanation of role and page ***
	$text='<p>'.__('Sourcerole').': '.__('e.g. Writer, Brother, Sister, Father').'<br>';
	$text.=__('Page').': '.__('page in source');

	//$text.= '<table class="humo standard" border="1">';
	$text.= '<table class="humo" border="1">';

	$text.='<form method="POST" action="'.$phpself2.'">';
	$text.='<input type="hidden" name="page" value="'.$page.'">';
	if (isset($_POST['event_person']) OR isset($_GET['event_person']))
		$text.='<input type="hidden" name="event_person" value="1">';
	if (isset($_POST['event_family']) OR isset($_GET['event_family']))
		$text.='<input type="hidden" name="event_family" value="1">';

	$text.= '<tr class="table_header_large">';
		$text.= '<th>'.__('source').'</th>';
		//$text.= '<th>'.__('Sourcerole').'</th>';
		//$text.= '<th>'.__('Page').'</th>';
		//$text.= '<th>'.__('date').' - '.__('place').'</th>';
		//$text.= '<th colspan="2">'.__('source').'</th>';
		$text.= '<th style="border-right:0px;">'.__('Option').'</th>';
		$text.= '<th style="border-left:0px;">'.__('Value').'</th>';
		$text.= '<th><input type="submit" name="submit" title="submit" value="'.__('Save').'"></th>';
	$text.= '</tr>';

	// *** Search for all connected sources ***
	$connect_qry="SELECT * FROM humo_connections
		WHERE connect_tree_id='".$tree_id."'
		AND connect_kind='".$connect_kind."'
		AND connect_sub_kind='".$connect_sub_kind."'
		AND connect_connect_id='".$connect_connect_id."'
		ORDER BY connect_order";
	$connect_sql=$dbh->query($connect_qry);
	$count=$connect_sql->rowCount();

	$change_bg_colour=false;
	while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
		$source_name=$connectDb->connect_id;

		$text.='<input type="hidden" name="connect_change['.$connectDb->connect_id.']" value="'.$connectDb->connect_id.'">';
		$text.='<input type="hidden" name="connect_connect_id['.$connectDb->connect_id.']" value="'.$connectDb->connect_connect_id.'">';
		if (isset($marriage)){ $text.='<input type="hidden" name="marriage_nr['.$connectDb->connect_id.']" value="'.$marriage.'">'; }
		$text.='<input type="hidden" name="connect_kind['.$connectDb->connect_id.']" value="'.$connect_kind.'">';
		$text.='<input type="hidden" name="connect_sub_kind['.$connectDb->connect_id.']" value="'.$connect_sub_kind.'">';
		$text.='<input type="hidden" name="connect_item_id['.$connectDb->connect_id.']" value="">';

		$color= ''; if ($change_bg_colour==true){ $color = ' class="humo_color"'; }
		$text.= '<tr'.$color.'><td>';

			$text.=' <a href="index.php?'.$joomlastring.'page='.$page.
				'&amp;connect_drop='.$connectDb->connect_id.
				'&amp;connect_kind='.$connectDb->connect_kind.
				'&amp;connect_sub_kind='.$connectDb->connect_sub_kind.
				'&amp;connect_connect_id='.$connectDb->connect_connect_id;

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

			if ($connectDb->connect_order<$count){
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
				$text.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
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
				$text.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}

		$text.='</td><td style="border-right:0px;">';

		$text.=__('source');

		$text.='</td><td style="border-left:0px;" colspan="2">';
			$field_text='style="height: 60px; width:600px"';
			$text.= '<textarea rows="2" name="connect_text['.$connectDb->connect_id.']" '.$field_text.'>'.$editor_cls->text_show($connectDb->connect_text).'</textarea>';
		$text.='</td></tr>';

		// *** Source date and place ***
		$color= ''; if ($change_bg_colour==true){ $color = ' class="humo_color"'; }
		$text.= '<tr'.$color.'>';
		$text.='<td><br></td>';	
		$text.='<td style="border-right:0px;">';

			$text.=ucfirst(__('date'));

		$text.='</td><td style="border-left:0px; border-right:0px;">';

			$field_date=12; // Size of date field.
			$text.= $editor_cls->date_show($connectDb->connect_date,'connect_date',"[$connectDb->connect_id]");

			$text.=' '.__('place').' <input type="text" name="connect_place['.$connectDb->connect_id.']" placeholder="'.ucfirst(__('place')).'" value="'.htmlspecialchars($connectDb->connect_place).'" size="15">';

		$text.='</td><td style="border-left:0px;">';

			$text.=' <input type="text" name="connect_role['.$connectDb->connect_id.']" placeholder="'.__('Sourcerole').'" value="'.htmlspecialchars($connectDb->connect_role).'" size="6"> ';
			$text.=' <input type="text" name="connect_page['.$connectDb->connect_id.']" placeholder="'.__('Page').'" value="'.$connectDb->connect_page.'" size="6">';

		$text.='</td></tr>';

		// *** Shared source, source role and source page ***
		$color= ''; if ($change_bg_colour==true){ $color = ' class="humo_color"'; }
		$text.= '<tr'.$color.'>';
		$text.='<td><br></td>';	
		$text.='<td style="border-right:0px;">';
			$text.='<br>';
		$text.='</td><td style="border-left:0px; border-right:0px;" colspan="2">';
			// *** Source: pull-down menu ***
			$source_qry=$dbh->query("SELECT * FROM humo_sources
				WHERE source_tree_id='".safe_text($tree_id)."' ORDER BY source_title");
			$text.='<select size="1" name="connect_source_id['.$connectDb->connect_id.']" style="width: 300px">';
			$text.='<option value="">'.__('Select shared source').':</option>';
			while ($sourceDb=$source_qry->fetch(PDO::FETCH_OBJ)){
				$selected='';
				if($connectDb->connect_source_id != '') {
					if ($sourceDb->source_gedcomnr==$connectDb->connect_source_id){ $selected=' SELECTED'; }
				}
				$text.='<option value="'.@$sourceDb->source_gedcomnr.'"'.$selected.'>'.
					@$sourceDb->source_title.' ['.@$sourceDb->source_gedcomnr.']</option>'."\n";
			}
			$text.='</select> ';

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
			$text.='</select>';

		$text.='</td></tr>';



// *** Picture by source ***




		$text.='<tr class="table_header_large" style="border-top:solid 2px #000000;"><td colspan="4"><br></td></tr>';
 
 		if ($change_bg_colour==true){ $change_bg_colour=false; }
			else{ $change_bg_colour=true; }
	}

	$text.='</form>';

	//$text.='<tr><td colspan="4"><br></td></tr>';

	// *** Add new source connection ***
	$text.='<tr bgcolor="#CCFFFF"><td>'.__('Add').'</td>';
		$text.='<td style="border-right:0px;"></td>';
		$text.='<td style="border-left:0px;"></td>';
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
			$text.=' <input type="Submit" name="connect_add" value="'.__('Add source').'">';
			$text.='</form>';
		$text.='</th>';
	$text.='</tr>';

	$text.='</table>';
	$text.='<p>'; // some extra space below table.

	return $text;
}
?>