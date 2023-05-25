<?php
//include_once("header.php");
//include_once(CMS_ROOTPATH . "menu.php");


//CHECK...
global $user, $db_functions;


// *** Check user ***
if ($user['group_addresses'] != 'j') {
	echo __('You are not authorised to see this page.');
	exit();
}

include_once(CMS_ROOTPATH . "include/language_date.php");
include_once(CMS_ROOTPATH . "include/person_cls.php");
include_once(CMS_ROOTPATH . "include/show_sources.php");
include_once(CMS_ROOTPATH . "include/show_picture.php");
?>

<table class="humo standard">

	<tr>
		<td>
			<h2><?php echo __('Address'); ?></h2>

			<?php
			//$addressDb = $db_functions->get_address($_GET['gedcomnumber']);

			if ($datos["address"]->address_address) {
				echo '<b>' . __('Street') . ':</b> ' . $datos["address"]->address_address . '<br>';
			}
			if ($datos["address"]->address_zip) {
				echo '<b>' . __('Zip code') . ':</b> ' . $datos["address"]->address_zip . '<br>';
			}
			if ($datos["address"]->address_place) {
				echo '<b>' . __('Place') . ':</b> ' . $datos["address"]->address_place . '<br>';
			}
			if ($datos["address"]->address_phone) {
				echo '<b>' . __('Phone') . ':</b>' . $datos["address"]->address_phone . '<br>';
			}
			if ($datos["address"]->address_text) {
				echo '</td></tr><tr><td>' . nl2br($datos["address"]->address_text);
			}

			// *** show pictures here ? ***

			// *** Show source by addresss ***
			$source_array = show_sources2("address", "address_source", $datos["address"]->address_gedcomnr);
			if ($source_array) {
				echo '</td></tr><tr><td>';
				echo '<b>' . __('Source') . ' ' . $source_array['text'];
			}

			echo '</td></tr><tr><td>';

			$person_cls = new person_cls;
			// *** Search address in connections table ***
			//$event_qry = $db_functions->get_connections('person_address', $_GET['gedcomnumber']);
			$event_qry = $db_functions->get_connections('person_address', $_GET['id']);
			foreach ($event_qry as $eventDb) {
				// *** Person address ***
				if ($eventDb->connect_connect_id) {
					$personDb = $db_functions->get_person($eventDb->connect_connect_id);
					$name = $person_cls->person_name($personDb);

					// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
					$url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
					echo __('Address by person') . ': <a href="' . $url . '">' . $name["standard_name"] . '</a>';

					if ($eventDb->connect_role) {
						echo ' ' . $eventDb->connect_role;
					}
					echo '<br>';
				}
			}
			unset($event_qry); // *** If finished, remove data from memory ***

			echo '</td></tr></table>';

			// *** If source footnotes are selected, show them here ***
			if (isset($_SESSION['save_source_presentation']) and $_SESSION['save_source_presentation'] == 'footnote') {
				echo show_sources_footnotes();
			}

			echo '<br>';

//include_once(CMS_ROOTPATH . "footer.php");
