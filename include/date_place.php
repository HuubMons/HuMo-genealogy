<?php
// *** Function to display date - place or place - date. ***
function date_place($process_date, $process_place){
	global $language, $user, $screen_mode, $dirmark1, $humo_option;
	$self = $_SERVER['QUERY_STRING']; $hebdate='';
	if($humo_option['date_display']=="is") {
		if(stripos($self,"star")===FALSE AND stripos($self,"ancestor_chart")===FALSE AND stripos($self,"ancestor_sheet")===FALSE) { $hebdate=hebdate($process_date); }
	}
	if ($process_place==" "){$process_place="";} // *** If there is no place ***
	if ($user['group_place_date']=='j'){
		$text="";
		if ($user['group_places']=='j' AND $process_place){
			//$text=__('PLACE_AT ').$process_place." ";
			if (__('PLACE_AT ')!='PLACE_AT '){ $text=__('PLACE_AT '); }
			$text.=$process_place." ";
		}
		//$text.=$dirmark1.language_date($process_date);
		$text.=$dirmark1.language_date($process_date).$hebdate;
	}
	else{
		$text=$dirmark1.language_date($process_date).$hebdate;
		if ($user['group_places']=='j' AND $process_place){
			//$text.=" ".__('PLACE_AT ').$process_place;
			$text.=' ';
			if (__('PLACE_AT ')!='PLACE_AT '){ $text.=__('PLACE_AT '); }
			$text.=$process_place;
		}
	}
	return $text;
}

function hebdate($datestr) {  
  global $language;
  $hebdate='';
  $year=NULL; $month=NULL; $day=NULL;
  $year = search_year($datestr);
  if($year) { 
    $month = search_month($datestr); 
    if($month) { $day = search_day($datestr); }
  }
  if($year!=NULL AND $month!=NULL AND $day!=NULL) {
    $str = jdtojewish(gregoriantojd( $month, $day, $year),false);
    $string = explode("/",$str); 
    if($language["dir"]=="rtl") {
       if($string[0]==1) $month = "תשרי";
       if($string[0]==2) $month = "חשון";
       if($string[0]==3) $month = "כסלו";
       if($string[0]==4) $month = "טבת";
       if($string[0]==5) $month = "שבט";
       if($string[0]==6) $month = "אדר";
       if($string[0]==7) $month = "אדר שני";
       if($string[0]==8) $month = "ניסן";
       if($string[0]==9) $month = "אייר";
       if($string[0]==10) $month = "סיון";
       if($string[0]==11) $month = "תמוז";
       if($string[0]==12) $month = "אב";
       if($string[0]==13) $month = "אלול";
    }
    else {
       if($string[0]==1) $month = "Tishrei";
       if($string[0]==2) $month = "Cheshvan";
       if($string[0]==3) $month = "Kislev";
       if($string[0]==4) $month = "Tevet";
       if($string[0]==5) $month = "Shevat";
       if($string[0]==6) $month = "Adar";
       if($string[0]==7) $month = "Adar II";
       if($string[0]==8) $month = "Nisan";
       if($string[0]==9) $month = "Iyar";
       if($string[0]==10) $month = "Sivan";
       if($string[0]==11) $month = "Tamuz";
       if($string[0]==12) $month = "Av";
       if($string[0]==13) $month = "Ellul";
    }
    $hebdate = " (".$string[1]." ".$month." ".$string[2].")"; 
  }
  return $hebdate;
}

function search_year($search_date) {
	$year=substr($search_date,-4, 4);
	if ($year < 2100 AND $year > 0) {}
	else { $year=null;}
	return ($year);
}
function search_month($search_date) {
	$month=strtoupper(substr($search_date, -8, 3));
	if ($month=="JAN") {$text=1;}
	else if($month=="FEB") {$text=2;}
	else if($month=="MAR") {$text=3;}
	else if($month=="APR") {$text=4;}
	else if($month=="MAY") {$text=5;}
	else if($month=="JUN") {$text=6;}
	else if($month=="JUL") {$text=7;}
	else if($month=="AUG") {$text=8;}
	else if($month=="SEP") {$text=9;}
	else if($month=="OCT") {$text=10;}
	else if($month=="NOV") {$text=11;}
	else if($month=="DEC") {$text=12;}
	else {$text=null;}
	return($text);
}
function search_day($search_date) {
	$day="";
	if (strlen($search_date)==11) {    // 12 sep 2002 or 08 sep 2002
		$day=substr($search_date, -11, 2);
		if(substr($day,0,1)=="0") {   // 08 aug 2002
			$day=substr($day,1,1);
		}
	}
	if (strlen($search_date)==10) {    // 8 aug 2002
		$day=substr($search_date, -10, 1);
	}
	if ($day) {
		$day=(int)$day;
	}
	if ($day>0 AND $day<32) {}
	else { $day=null; }
	return($day);
}

?>