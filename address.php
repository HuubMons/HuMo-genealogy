<?php
include_once("header.php");
include_once (CMS_ROOTPATH."menu.php");

// *** Check user ***
if ($user['group_addresses']!='j'){
	echo __('You are not authorised to see this page.');
	exit();
}

include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/person_cls.php");

print '<table class="humo standard">';
print "<tr><td><h2>".__('Address')."</h2>";

//@$address_qry=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."addresses WHERE address_gedcomnr='".safe_text($_GET['gedcomnumber'])."'",$db);
//$addressDb=mysql_fetch_object($address_qry);
$result = $dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."addresses WHERE address_gedcomnr='".safe_text($_GET['gedcomnumber'])."'");
$addressDb = $result->fetch(PDO::FETCH_OBJ);

if (@$addressDb->address_address){ print "<b>".__('Address').":</b> $addressDb->address_address<br>"; }
if (@$addressDb->address_zip){ print "<b>".__('Zip code').":</b> $addressDb->address_zip<br>"; }
if (@$addressDb->address_place){ print "<b>".__('Place').":</b> $addressDb->address_place<br>"; }
if (@$addressDb->address_phone){ print "<b>".__('Phone').":</b>$addressDb->address_phone<br>"; }
if (@$addressDb->address_text){ print '</td></tr><tr><td>'.nl2br($addressDb->address_text); }

// *** show pictures here ? ***

$person_cls = New person_cls;

print "</td></tr><tr><td>";

	// *** Search address in connections table ***
	//$eventsql="SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."connections
	//	WHERE connect_sub_kind='person_address'
	//	AND connect_item_id='".safe_text($_GET['gedcomnumber'])."'";
	//$eventqry=mysql_query($eventsql,$db);
	$eventsql="SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."connections
		WHERE connect_sub_kind='person_address'
		AND connect_item_id='".safe_text($_GET['gedcomnumber'])."'";
	$eventqry = $dbh->query($eventsql);
	while (@$eventDb=$eventqry->fetch(PDO::FETCH_OBJ)){

		// *** Person address ***
		if ($eventDb->connect_connect_id){
			//$persoon=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
			//	WHERE pers_gedcomnumber='$eventDb->connect_connect_id'",$db);
			//$personDb=mysql_fetch_object($persoon);
			$result = $dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber='$eventDb->connect_connect_id'");
			$personDb = $result->fetch(PDO::FETCH_OBJ);
			print __('Address by person').': <a href="family.php?id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'">';
			$name=$person_cls->person_name($personDb);
			echo $name["standard_name"].'</a>';
			if ($eventDb->connect_role){ echo ' '.$eventDb->connect_role; }
			print '<br>';
		}

	}

print "</td></tr></table>";
include_once(CMS_ROOTPATH."footer.php");
?>