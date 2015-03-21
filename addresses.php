<?php
include_once("header.php"); // *** returns CMS_ROOTPATH constant ***
include_once(CMS_ROOTPATH."menu.php");

// *** Check user authority ***
if ($user['group_addresses']!='j'){
	echo __('You are not authorised to see this page.');
	exit();
}

include_once(CMS_ROOTPATH."include/language_date.php");

echo '<h1 style="text-align:center;">'.__('Addresses').'</h1>';
echo '<div class="center">';
	//$address = $dbh->query("SELECT * FROM ".$tree_prefix_quoted."addresses 
	//	WHERE address_gedcomnr LIKE '_%'");
	$address = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='".$tree_id."' 
		AND address_gedcomnr LIKE '_%'");
	while(@$addressDb=$address->fetch(PDO::FETCH_OBJ)) {
		print '<a href="'.CMS_ROOTPATH.'address.php?gedcomnumber='.$addressDb->address_gedcomnr.'">'.$addressDb->address_address.'</a> '.$addressDb->address_place.'<br>';
	}
echo '</div>';
include_once(CMS_ROOTPATH."footer.php");
?>