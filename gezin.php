<head>
<!-- THIS SCRIPT IS FOR BACKWARDS COMPATIBLY AND GENDEX SITES ONLY!!!! -->

<?php
// *** Example: ?database=humo_&id=F59&hoofdpersoon=I151 ***

$database=''; // *** standard: show first family ***
if (isset($_GET["database"])){ $database=$_GET["database"]; }
if (isset($_POST["database"])){ $database=$_POST["database"]; }

$id=1; // *** standard: show first family ***
if (isset($_GET["id"])){ $id=$_GET["id"]; }
if (isset($_POST["id"])){ $id=$_POST["id"]; }

$main_person=''; // *** Mainperson of a family ***
if (isset($_GET["hoofdpersoon"])){ $main_person=$_GET["hoofdpersoon"]; }
if (isset($_POST["hoofdpersoon"])){ $main_person=$_POST["hoofdpersoon"]; }

$location="family.php?database=".$database.'&id='.$id;
if ($main_person!=''){ $location.='&main_person='.$main_person; }

echo '<script language="JavaScript">'."\n";
echo '<!--'."\n";
echo 'window.location="'.$location.'"'."\n";
echo '//-->'."\n";
echo '</script>'."\n";
?>

</head>