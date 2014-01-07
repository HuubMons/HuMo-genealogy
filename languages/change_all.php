<?php
//error_reporting(E_ALL | E_STRICT);

require('../admin/include/po-mo_converter/php-mo.php');

echo "Changing death [&dagger;] symbol (&amp;#134; or &amp;dagger;) to eternity [&infin;] symbol (&amp;infin;) in all language files<br><br>";
 
$dh  = opendir("./");  
 
while (false !== ($filename = readdir($dh))) { 
	if (is_dir($filename) AND $filename!=".." AND $filename!='.' AND strlen($filename)==2){  
		$str=file_get_contents($filename."/".$filename.".po");

		// here are the replace strings
		// anyone who wants to batch-change other or additional values can add them here
		$str=str_replace('msgstr "&#134;"', 'msgstr "&infin;"',$str);
		$str=str_replace('msgstr "&dagger;"', 'msgstr "&infin;"',$str);

		file_put_contents($filename."/".$filename.".po", $str);
		if (phpmo_convert($filename."/".$filename.".po" )===TRUE){
			echo 'The '.$filename.'.mo file was succesfully saved!<br>';
		}
		else{
			echo 'ERROR: the '.$filename.'.mo file IS NOT saved!<br>';
		} 
	}
}
 
?>