<?php
// *** Function to safely store items in tables ***
function safe_text_db($text_safe)
{
	global $dbh;

	// *** Strip magic_quotes from $_POST and $_GET, needed for some providers that uses this function ***
	// *** Deprecated in PHP 5.4.0: will return FALSE ***
	//if (get_magic_quotes_gpc()==1) {
	//	// *** magicquotes is activated, addslashes is used ***
	//	$text_safe = stripslashes($text_safe);
	//}
	//else{
	//  $text_safe = addslashes(trim($text_safe));
	//}
	//return @mysql_real_escape_string($text_safe);

	$text_safe = $dbh->quote($text_safe);
	// PDO "quote" escapes like mysql_real_escape_string, BUT also encloses in single quotes. 
	// In all HuMo-genealogy scripts the single quotes are already coded ( "...some-parameter = '".$var."'")  so we take them off:
	$text_safe = substr($text_safe, 1, -1); // remove quotes from beginning and end
	return $text_safe;
}

// *** Function to safely show text on screen (in forms etc.) ***
function safe_text_show($text_safe)
{
	// *** First remove tags, ENT_QUOTES is used to also change single quote character: ' ***
	$text_safe = strip_tags($text_safe, ENT_QUOTES);
	// *** Also convert special characters into HTML entities ***
	$text_safe = htmlspecialchars($text_safe, ENT_QUOTES);
	return $text_safe;
}
