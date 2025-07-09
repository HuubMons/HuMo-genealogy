<?php
require(__DIR__ . '/../admin/include/po-mo_converter/php-mo.php');

//echo 'Changing death [&dagger;] symbol (&amp;#134; or &amp;dagger;) to eternity [&infin;] symbol (&amp;infin;) in all language files<br><br>';

$url_start = '';
if (is_dir('../languages')) {
    $url_start = '../';
}
$dh = opendir($url_start . 'languages');

while (false !== ($filename = readdir($dh))) {
    if (is_dir($url_start . 'languages/' . $filename) and $filename != '..' and $filename != '.' and strlen($filename) == 2) {
        $str = file_get_contents($url_start . 'languages/' . $filename . '/' . $filename . '.po');

        // Replace strings here
        if ($humo_option['death_char'] == 'y') {
            // if setting was no infinity signs --> change to infinity
            $str = str_replace('msgstr "&#134;"', 'msgstr "&infin;"', $str);
            $str = str_replace('msgstr "&dagger;"', 'msgstr "&infin;"', $str);
        } else {
            // setting was infinity --> change to dagger (cross)
            $str = str_replace('msgstr "&infin;"', 'msgstr "&#134;"', $str);
        }

        file_put_contents($url_start . 'languages/' . $filename . '/' . $filename . '.po', $str);
        if (phpmo_convert($url_start . 'languages/' . $filename . '/' . $filename . '.po') === TRUE) {
            //echo 'The '.$url_start.$filename.'.mo file was succesfully saved!<br>';
        } else {
            echo 'ERROR: the ' . $url_start . $filename . '.mo file IS NOT saved!<br>';
        }
    }
}
