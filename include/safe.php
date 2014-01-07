<?php 
// *** Function to safely store items in tabels ***
function safe_text($text_safe) {
	if (get_magic_quotes_gpc()==1) {
		// *** magicquotes is activated, addslashes is used ***
		$text_safe = stripslashes($text_safe);
	}
	//else{
	//  $text_safe = addslashes(trim($text_safe));
	//}
	return @mysql_real_escape_string($text_safe);
}
?>