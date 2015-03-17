<?php
function timezone(){
	global $humo_option;
	// *** Set timezone ***
	if (function_exists('date_default_timezone_set')){
		//date_default_timezone_set('Europe/Amsterdam');
		@date_default_timezone_set($humo_option["timezone"]);
	}
}
?>