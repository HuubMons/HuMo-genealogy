<?php
// *** Function to display date - place or place - date. ***
function date_place($process_date, $process_place){
	global $language, $user, $screen_mode, $dirmark1;
	if ($process_place==" "){$process_place="";} // *** If there is no place ***

	if ($user['group_place_date']=='j'){
		$text="";
		if ($user['group_places']=='j' AND $process_place){
			//$text=__('PLACE_AT ').$process_place." ";
			if (__('PLACE_AT ')!='PLACE_AT '){ $text=__('PLACE_AT '); }
			$text.=$process_place." ";
		}
		$text.=$dirmark1.language_date($process_date);
	}
	else{
		$text=$dirmark1.language_date($process_date);
		if ($user['group_places']=='j' AND $process_place){
			//$text.=" ".__('PLACE_AT ').$process_place;
			$text.=' ';
			if (__('PLACE_AT ')!='PLACE_AT '){ $text.=__('PLACE_AT '); }
			$text.=$process_place;
		}
	}
	return $text;
}
?>