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

	$text.= '<input type="text" name="'.$process_name.$multiple_rows.'" value="';
		// *** BEF, ABT, AFT, etc. is shown in date_prefix ***
		$process_date=strtolower($process_date);
		if (substr($process_date,0,4)=='bef '){ $text.=substr($process_date,4); }
		elseif (substr($process_date,0,4)=='abt '){ $text.=substr($process_date,4); }
		elseif (substr($process_date,0,4)=='aft '){ $text.=substr($process_date,4); }
		elseif (substr($process_date,0,4)=='bet '){ $text.=substr($process_date,4); }
		else { $text.=$process_date; }
	$text.='" size="'.$field_date.'">';

	return $text;
}

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
	global $db, $tree_prefix;
	if($find_text != '') {
		$text=$find_text;
		if (substr($find_text, 0, 1)=='@'){
			$search_text=mysql_query("SELECT * FROM ".$tree_prefix."texts
			WHERE text_gedcomnr='".$find_text."'",$db);
			@$search_textDb=mysql_fetch_object($search_text);
			@$text=$search_textDb->text_text;
			$text = str_replace("<br>", "<br>\n", $text);
		}
		$text = str_replace("<br>", "", $text);
		return $text;
	}
}

} // *** End of editor class ***
?>