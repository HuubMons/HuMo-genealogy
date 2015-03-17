<?php
function process_text($text_process, $text_sort='standard'){
	global $dbh, $tree_id, $user, $tree_prefix_quoted;
	global $screen_mode, $text_presentation;

	if ($text_presentation!='hide'){
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
				$text_check=substr($text_pieces[$i],1,-1);
				//$search_text=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."texts
				//	WHERE text_gedcomnr='".safe_text($text_check)."'");
				$search_text=$dbh->query("SELECT * FROM humo_texts
					WHERE text_tree_id='".$tree_id."' AND text_gedcomnr='".safe_text($text_check)."'");
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

		if ($text_process){
			if ($screen_mode=='RTF')
				$text_process='<i>'.$text_process.'</i>';
			else
				$text_process='<span class="text">'.$text_process.'</span>';
		}

		// *** Show tekst in popup screen ***
		if ($text_presentation=='popup' AND $screen_mode!='PDF' AND $screen_mode!='RTF' AND $text_process){
			global $rtlmarker, $family_id, $main_person, $alignmarker, $text_nr;
			if (isset($text_nr)) $text_nr++; else $text_nr=1;
			$text= '<div class="'.$rtlmarker.'sddm" style="left:10px;top:10px;display:inline;">';
				$text.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$family_id.'&amp;main_person='.$main_person.'"';
				$text.= ' style="display:inline" ';
				$text.= 'onmouseover="mopen(event,\'show_text'.$text_nr.'\',0,0)"';
				$text.= 'onmouseout="mclosetime()">';
				if ($text_sort=='standard')
					$text.= '['.lcfirst(__('Text')).']';
				else
					$text.= '<b>['.(__('Text')).']</b>';
				$text.= '</a>';

				if (substr_count($text_process,'<br>')>10 OR substr_count($text_process,'<br />')>10)
					$text.='<div class="sddm_fixed" style="z-index:10; padding:4px; text-align:'.$alignmarker.'; direction:'.$rtlmarker.'; height:300px; width:80%; overflow-y: scroll;" id="show_text'.$text_nr.'" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
				else
					$text.='<div class="sddm_fixed" style="z-index:10; padding:4px; text-align:'.$alignmarker.'; direction:'.$rtlmarker.';" id="show_text'.$text_nr.'" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

				// *** Show a correct website link in text ***
				$text_process = str_ireplace('<a href', '<a style="display:inline" href', $text_process);

				$text.=$text_process;
				$text.='</div>';
			$text.='</div>';
			$text_process=$text;
		}

	}
	else $text_process='';

	return $text_process;
}
?>