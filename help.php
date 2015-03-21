<?php
include_once("header.php");
include_once("menu.php");

if($language["dir"]=="ltr") {
	echo '<div class="help_div">';
}
else {
	echo '<div class="rtlhelp_div">';
}

include_once('languages/'.$selected_language.'/language_help.php'); //Taal

echo '</div>';

include_once("footer.php");
?>