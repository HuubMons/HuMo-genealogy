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

$addressDb = $db_functions->get_address($_GET['gedcomnumber']);

if (@$addressDb->address_address){ print "<b>".__('Address').":</b> $addressDb->address_address<br>"; }
if (@$addressDb->address_zip){ print "<b>".__('Zip code').":</b> $addressDb->address_zip<br>"; }
if (@$addressDb->address_place){ print "<b>".__('Place').":</b> $addressDb->address_place<br>"; }
if (@$addressDb->address_phone){ print "<b>".__('Phone').":</b>$addressDb->address_phone<br>"; }
if (@$addressDb->address_text){ print '</td></tr><tr><td>'.nl2br($addressDb->address_text); }

// *** show pictures here ? ***

$person_cls = New person_cls;

print "</td></tr><tr><td>";

	// *** Search address in connections table ***
	$event_qry = $db_functions->get_connections('person_address',$_GET['gedcomnumber']);
	foreach($event_qry as $eventDb){
		// *** Person address ***
		if ($eventDb->connect_connect_id){
			$personDb=$db_functions->get_person($eventDb->connect_connect_id);
			$name=$person_cls->person_name($personDb);
			print __('Address by person').': <a href="family.php?id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'">';
			echo $name["standard_name"].'</a>';
			if ($eventDb->connect_role){ echo ' '.$eventDb->connect_role; }
			print '<br>';
		}
	}
	unset($event_qry); // *** If finished, remove data from memory ***

print "</td></tr></table>";
include_once(CMS_ROOTPATH."footer.php");
?>