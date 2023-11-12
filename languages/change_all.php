<?php
require(__DIR__ . '/../admin/include/po-mo_converter/php-mo.php');

//echo "Changing death [&dagger;] symbol (&amp;#134; or &amp;dagger;) to eternity [&infin;] symbol (&amp;infin;) in all language files<br><br>";

$dh = opendir("languages");
while (false !== ($filename = readdir($dh))) {
    if (is_dir("languages/" . $filename) and $filename != ".." and $filename != '.' and strlen($filename) == 2) {
        $str = file_get_contents("languages/" . $filename . "/" . $filename . ".po");

        // here are the replace strings
        if ($humo_option['death_char'] == "n") {     //  if setting was no infinity signs --> change to infinity
            $str = str_replace('msgstr "&#134;"', 'msgstr "&infin;"', $str);
            $str = str_replace('msgstr "&dagger;"', 'msgstr "&infin;"', $str);
        } else {   // setting was infinity --> change to dagger (cross)
            $str = str_replace('msgstr "&infin;"', 'msgstr "&#134;"', $str);
        }

        file_put_contents("languages/" . $filename . "/" . $filename . ".po", $str);
        if (phpmo_convert("languages/" . $filename . "/" . $filename . ".po") === TRUE) {
            //echo 'The '.$filename.'.mo file was succesfully saved!<br>';
        } else {
            echo 'ERROR: the ' . $filename . '.mo file IS NOT saved!<br>';
        }
    }
}
