<?php
function process_text($text_process){
	global $user, $db, $dbh;
	//1 NOTE Text by person#werktekst#
	//2 CONT 2e line text persoon#2e werktekst#
	//2 CONT 3e line #3e werktekst# tekst persoon

	// *** If multiple texts are read, a | seperator character is added ***
	// *** Split the text, and check for @Nxx@ texts ***
	$text_pieces = explode("|", $text_process);
	$text_result='';
	for ( $i = 0; $i <= (count($text_pieces)-1); $i++) { 
		// *** Search for Aldfaer texts ***
		if (substr($text_pieces[$i], 0, 1)=='@'){
			//$search_text=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."texts
			//	WHERE text_gedcomnr='".safe_text($text_pieces[$i])."'",$db);
			//$search_textDb=mysql_fetch_object($search_text);
			$search_text=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."texts
				WHERE text_gedcomnr='".safe_text($text_pieces[$i])."'");
			$search_textDb=$search_text->fetch(PDO::FETCH_OBJ);			
			if ($text_result){ $text_result.='<br>'; }
			$text_result.=@$search_textDb->text_text;
		}
		else{
			if ($text_result){ $text_result.='<br>'; }
			$text_result.=$text_pieces[$i];
		}
	}
	if ($text_result){ $text_process=$text_result; }

	// *** If needed strip worktext (used in Haza-Data) ***
	if ($user['group_work_text']=='n'){
		// *** Added a '!' sign to prevent '0' detection. The routine will stop then! ***
		$text_process="!".$text_process;
		WHILE (strpos($text_process,'#')>0){
			$first=strpos($text_process,'#');
			$text1=substr($text_process,0,$first);
			$text_process=substr($text_process,$first+1);

			$second=strpos($text_process,'#');
			$text2=substr($text_process,$second+1);

			$text_process=$text1.$text2;
		}
		// *** Strip added '!' sign ***
		$text_process=substr($text_process,1);
	}

	// *** Convert all url's in a text to clickable links ***
	$text_process = preg_replace("#(^|[ \n\r\t])www.([a-z\-0-9]+).([a-z]{2,4})($|[ \n\r\t])#mi", "\\1<a href=\"http://www.\\2.\\3\" target=\"_blank\">www.\\2.\\3</a>\\4", $text_process);
	//$text_process = preg_replace("#(^|[ \n\r\t])(((ftp://)|(http://)|(https://))([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+))#mi", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text_process);
	$text_process = preg_replace("#(^|[ \n\r\t])(((http://)|(https://))([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+))#mi", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text_process);

	if ($text_process){ $text_process=nl2br($text_process); }

	if ($text_process){ $text_process='<span class="text">'.$text_process.'</span>'; }

	return $text_process;
}
?>