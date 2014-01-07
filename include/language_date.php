<?php
function language_date($date_text){
	global $language;
	$date_text=strtoupper($date_text);

	$date_text=str_replace("JAN", __('jan'), $date_text);
	$date_text=str_replace("FEB", __('feb'), $date_text);
	$date_text=str_replace("MAR", __('mar'), $date_text);
	$date_text=str_replace("APR", __('apr'), $date_text);
	$date_text=str_replace("MAY", __('may'), $date_text);
	$date_text=str_replace("JUN", __('jun'), $date_text);
	$date_text=str_replace("JUL", __('jul'), $date_text);
	$date_text=str_replace("AUG", __('aug'), $date_text);
	$date_text=str_replace("SEP", __('sep'), $date_text);
	$date_text=str_replace("OCT", __('oct'), $date_text);
	$date_text=str_replace("NOV", __('nov'), $date_text);
	$date_text=str_replace("DEC", __('dec'), $date_text);

	$date_text=str_replace("EST ABT", __('estimated &#177;'), $date_text);
	$date_text=str_replace("CAL ABT", __('estimated &#177;'), $date_text);

	$date_text=str_replace("AFT", __('after'), $date_text);
	$date_text=str_replace("ABT", __('&#177;'), $date_text);
	$date_text=str_replace("BEF", __('before'), $date_text);
	$date_text=str_replace("BETWEEN", "BET", $date_text);
	$date_text=str_replace("BET", __('between'), $date_text);
	$date_text=str_replace("EST", __('estimated'), $date_text);
	$date_text=str_replace("CAL", __('estimated'), $date_text);
	$date_text=str_replace("AND", __('and'), $date_text);

	// *** Aldfaer items ***
	$date_text=str_replace("FROM", __('between'), $date_text);
	$date_text=str_replace("TO", __('and'), $date_text);

	return $date_text;
}
?>