<?php
// *** Safety line ***
//if (!defined('ADMIN_PAGE')){
//  exit;
//}

//echo '<H1 align=center>'.__('GEDCOM file export').'</H1>';

if (isset($_POST['file_name']) && file_exists('../' . $_POST['file_name'])) {
    //$file = "http://example.com/go.exe"; 
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    //header("Content-Disposition: attachment; filename=\"$myFile\"");
    //header("Content-Disposition: attachment; filename=\"gedcom.ged\"");
    header("Content-Disposition: attachment; filename=\"" . $_POST['file_name_short'] . "\"");
    readfile('../' . $_POST['file_name']);
}
