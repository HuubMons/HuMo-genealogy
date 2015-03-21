<?php
class editor_cls{

// *** Date functions ***
// 13 OCT 1813 = 13 okt 1813
// BEF 2000 = bef 2000
// ABT 2000 = abt 2000
// AFT 2000 = aft 2000
// BET 1986 AND 1987 = bet 1986 and 1987

// *** $multiple_rows = addition for editing in multiple rows. Example: name = "event_date[]" ***
function date_show($process_date, $process_name, $multiple_rows=''){

	// *** Process BEF, ABT, AFT and BET in a easier pulldown menu ***
	global $language, $field_date;

	$text='<select class="fonts" size="1" name="'.$process_name.'_prefix'.$multiple_rows.'">';
		$text.='<option value="">=</option>';

		$selected=''; if (substr($process_date,0,4)=='BEF '){ $selected=' selected'; }
		$text.='<option value="BEF "'.$selected.'>'.__('before').'</option>';

		$selected=''; if (substr($process_date,0,4)=='ABT '){ $selected=' selected'; }
		$text.='<option value="ABT "'.$selected.'>'.__('&#177;').'</option>';

		$selected=''; if (substr($process_date,0,4)=='AFT '){ $selected=' selected'; }
		$text.='<option value="AFT "'.$selected.'>'.__('after').'</option>';

		$selected=''; if (substr($process_date,0,4)=='BET '){ $selected=' selected'; }
		$text.='<option value="BET "'.$selected.'>'.__('between').'</option>';
	$text.='</select>';

	$text.= '<input type="text" name="'.$process_name.$multiple_rows.'" placeholder="'.ucfirst(__('date')).'" style="direction:ltr" value="';
		// *** BEF, ABT, AFT, etc. is shown in date_prefix ***
		$process_date=strtolower($process_date);

		// *** Show BC with uppercase ***
		if (substr($process_date,-3)==' bc') $process_date=str_replace(' bc',' BC',$process_date);
		if (substr($process_date,-5)==' b.c.') $process_date=str_replace(' b.c.',' B.C.',$process_date);

		if (substr($process_date,0,4)=='bef '){ $text.=substr($process_date,4); }
		elseif (substr($process_date,0,4)=='abt '){ $text.=substr($process_date,4); }
		elseif (substr($process_date,0,4)=='aft '){ $text.=substr($process_date,4); }
		elseif (substr($process_date,0,4)=='bet '){ $text.=substr($process_date,4); }
		else { $text.=$process_date; }

	$text.='" size="'.$field_date.'">';

	return $text;
}

/*
function date_process($process_name, $multiple_rows=''){
	// *** Save "before", "about", "after" texts before a date ***
	$process_name_prefix=$process_name.'_prefix';

	if ($multiple_rows!='')
		$process_date=$_POST["$process_name_prefix"][$multiple_rows].$_POST["$process_name"][$multiple_rows];
	else
		$process_date=$_POST["$process_name_prefix"].$_POST["$process_name"];

	$process_date=strtoupper($process_date);
	$process_date=safe_text($process_date);
	return $process_date;
}
*/

function date_process($process_name, $multiple_rows=''){
	// *** Save "before", "about", "after" texts before a date ***
	$process_name_prefix=$process_name.'_prefix'; 

	if ($multiple_rows!='') { $post_date = $_POST[$process_name][$multiple_rows]; $pref = $_POST["$process_name_prefix"][$multiple_rows]; }
	else { $post_date = $_POST[$process_name]; $pref = $_POST["$process_name_prefix"]; }
	$this_date="";
	$pos = strpos(strtoupper($post_date),"AND");
	if($pos!==false) { 
		if($pref == "BET ") { // we've got "BET" and "AND"
			$date1 = $this->valid_date(substr($post_date,0,$pos-1));  
			$date2 = $this->valid_date(substr($post_date,$pos+4)); 
			if($date1!=null AND $date2!=null) {
				$this_date = $date1." AND ".$date2;
			}
			else $this_date = __('Invalid date'); // one or both dates are invalid
		}
		else $this_date = __('Invalid date'); // "AND" appears but not with "BET"
	}
	elseif($pref == "BET " and $pos===false) {
		$this_date = __('Invalid date'); // "BET" appears but not with "AND"
	}
	elseif($post_date!="") {
		$date = $this->valid_date($post_date);
		if($date != null) {
			$this_date = $date;
		}
		else $this_date = __('Invalid date'); 
	}

	//if ($this_date == __('Invalid date')) $this_date.=': '.$post_date;

	if ($multiple_rows!='')
		//$process_date=$_POST["$process_name_prefix"][$multiple_rows].$_POST["$process_name"][$multiple_rows];
		$process_date=$pref.$this_date;
	else
		//$process_date=$_POST["$process_name_prefix"].$_POST["$process_name"];
		$process_date=$pref.$this_date; 

	$process_date=strtoupper($process_date);
	$process_date=safe_text($process_date);
	return $process_date;
}

function valid_date($date) {
	include_once(CMS_ROOTPATH."include/validate_date_cls.php");
	$check = New validate_date_cls;
	// date entered as 01-04-2013 or 01/04/2013
	if((strpos($date,"-")!==false OR strpos($date,"/")!==false)AND strpos($date," ")===false) { // skips "2 mar 1741/42" and "mar 1741/42"
		if(strpos($date,"-")!==false) { $delimiter = "-"; }
		else { $delimiter = "/"; }
		$date_dash = explode($delimiter,$date); 
		if(count($date_dash)==2) { // date was entered as month and year: 4-2011 or 4/2011 or we have case of "1741/42" (just year no day/month)
			if($date_dash[0] > $date_dash[1]) {
				$member = "none"; // "1741/42" so don't perform transformation
				$this_date = $date;
			}
			else {
				$member = 0; // first member of array is month
			}
		}
		else {
			$member = 1; // second member of array is month
		}
		if($member!="none") {
			if ($date_dash[$member]=="1" OR $date_dash[$member]=="01") { $date_dash[$member] = "JAN"; } 
			else if($date_dash[$member]=="2" OR $date_dash[$member]=="02") { $date_dash[$member] = "FEB"; }
			else if($date_dash[$member]=="3" OR $date_dash[$member]=="03") { $date_dash[$member] = "MAR"; }
			else if($date_dash[$member]=="4" OR $date_dash[$member]=="04") { $date_dash[$member] = "APR"; }
			else if($date_dash[$member]=="5" OR $date_dash[$member]=="05") { $date_dash[$member] = "MAY"; }
			else if($date_dash[$member]=="6" OR $date_dash[$member]=="06") { $date_dash[$member] = "JUN"; }
			else if($date_dash[$member]=="7" OR $date_dash[$member]=="07") { $date_dash[$member] = "JUL"; }
			else if($date_dash[$member]=="8" OR $date_dash[$member]=="08") { $date_dash[$member] = "AUG"; }
			else if($date_dash[$member]=="9" OR $date_dash[$member]=="09") { $date_dash[$member] = "SEP"; }
			else if($date_dash[$member]=="10") { $date_dash[$member] = "OCT"; }
			else if($date_dash[$member]=="11") { $date_dash[$member] = "NOV"; }
			else if($date_dash[$member]=="12") { $date_dash[$member] = "DEC"; }

			$this_date = implode(" ",$date_dash);
		}
	}
	else {
		$this_date = $date;
	}
	$result = $check->check_date(strtoupper($this_date));
	if($result==null) { return null; }
	else return $this_date;
}

function text_process($text,$long_text=false){
	//$text=htmlentities($text,ENT_QUOTES,'UTF-8');
	if ($long_text==true){
		//$text = str_replace("\r\n", "<br>\n", $text);
		$text = str_replace("\r\n", "\n", $text);
	}
	$text=safe_text($text);
	return $text;
}

// *** Show texts without <br> and process Aldfaer and other @xx@ texts ***
function text_show($find_text){
	global $dbh, $tree_id; 
	//$tree_prefix;
	if($find_text != '') {
		$text=$find_text;
		if (substr($find_text, 0, 1)=='@'){
			//$text_check=substr($find_text,1,-1);
			//$search_text=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."texts
			//	WHERE text_gedcomnr='".safe_text($text_check)."'");
			//$search_text=$dbh->query("SELECT * FROM ".$tree_prefix."texts
			//WHERE text_gedcomnr='".substr($find_text,1,-1)."'");
			$search_text=$dbh->query("SELECT * FROM humo_texts
			WHERE text_tree_id='".$tree_id."' AND text_gedcomnr='".substr($find_text,1,-1)."'");
			@$search_textDb=$search_text->fetch(PDO::FETCH_OBJ);
			@$text=$search_textDb->text_text;
			$text = str_replace("<br>", "<br>\n", $text);
		}
		$text = str_replace("<br>", "", $text);
		return $text;
	}
}

function show_selected_person($person){
	$prefix1=''; $prefix2='';
	//if($user['group_kindindex']=="j") {
	//	$prefix1=strtolower(str_replace("_"," ",$person->pers_prefix));
	//}
	//else {
		$prefix2=" ".strtolower(str_replace("_"," ",$person->pers_prefix));
	//}

	$text='['.$person->pers_gedcomnumber.'] '.
		$prefix1.$person->pers_lastname.', '.$person->pers_firstname.$prefix2.' ';

	if ($person->pers_birth_date){$text.=__('*').' '.strtolower($person->pers_birth_date); }
	if (!$person->pers_birth_date AND $person->pers_bapt_date){
		$text.=__('~').' '.strtolower($person->pers_bapt_date); }
	if ($person->pers_death_date){
		if ($text){ $text.=' '; }
		$text.=__('&#134;').' '.strtolower($person->pers_death_date);
	}
	if (!$person->pers_death_date AND $person->pers_buried_date){
		if ($text){ $text.=' '; }
		$text.=__('[]').' '.strtolower($person->pers_buried_date);
	}

	return($text);
}

} // *** End of editor class ***
?>