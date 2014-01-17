<?php
// *** Safety line ***
//if (!defined('ADMIN_PAGE')){ exit; }

//global $selected_language;
//global $persids, $famsids; $noteids;
//$persids = array(); $famsids = array(); $noteids = array();

//echo '<H1 align=center>'.__('Gedcom file export').'</H1>';

//if (isset($_POST['tree'])){
//	$tree=safe_text($_POST["tree"]);
//}

//$myFile = CMS_ROOTPATH_ADMIN."backup_tmp/gedcom.ged";
$myFile = "../backup_tmp/gedcom.ged";
// *** FOR TESTING PURPOSES ONLY ***
if (@file_exists("../../gedcom-bestanden")){
	$myFile="../../gedcom-bestanden/gedcom.ged";
}
if (@file_exists("../../../gedcom-bestanden")){
	$myFile="../../../gedcom-bestanden/gedcom.ged";
}

//$file = "http://example.com/go.exe"; 
header("Content-Description: File Transfer"); 
header("Content-Type: application/octet-stream"); 
//header("Content-Disposition: attachment; filename=\"$myFile\""); 
header("Content-Disposition: attachment; filename=\"gedcom.ged\""); 
readfile ($myFile);
?>