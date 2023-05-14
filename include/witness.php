<?php
/*
 *******************************************
 *** Show witness (birt, baptize, etc. ) ***
 * Used for:
 * birth witness
 * baptise witness
 * death declaration
 * burial witness
 * marriage witness
 * marriage-church witness
 *
 * $field = person/ family.
*/

// **********************************************************************
// * function witness (person gedcomnumber, $event item, database field);
// **********************************************************************
function witness($gedcomnr, $event, $field = 'person')
{
	global $dbh, $db_functions;
	$counter = 0;
	$text = '';
	if ($gedcomnr) {
		$witness_cls = new person_cls;
		if ($field == 'person')
			$witness_qry = $db_functions->get_events_connect('person', $gedcomnr, $event);
		else
			$witness_qry = $db_functions->get_events_connect('family', $gedcomnr, $event);

		foreach ($witness_qry as $witnessDb) {
			$counter++;
			if ($counter > 1) {
				$text .= ', ';
			}
			if ($witnessDb->event_event) {
				if (substr($witnessDb->event_event, 0, 1) == '@') {
					// *** Connected witness ***
					$witness_nameDb = $db_functions->get_person(substr($witnessDb->event_event, 1, -1));
					$name = $witness_cls->person_name($witness_nameDb);

					// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
					$url = $witness_cls->person_url2($witness_nameDb->pers_tree_id, $witness_nameDb->pers_famc, $witness_nameDb->pers_fams, $witness_nameDb->pers_gedcomnumber);

					$text .= '<a href="' . $url . '">' . rtrim($name["standard_name"]) . '</a>';
				} else {
					// *** Witness as text ***
					$text .= $witnessDb->event_event;
				}
			}

			//if ($witnessDb->event_date){ $text.=' '.date_place($witnessDb->event_date,''); } // *** Use date_place function, there is no place here... ***
			if ($witnessDb->event_date or $witnessDb->event_place) {
				$text .= ' ' . date_place($witnessDb->event_date, $witnessDb->event_place);
			}

			if ($witnessDb->event_text) {
				$text .= ' ' . process_text($witnessDb->event_text);
			}

			$text_array['text'] = $text;

			// *** Show sources by witnesses ***
			if ($field == 'person') {
				$source_array = show_sources2($field, "pers_event_source", $witnessDb->event_id);
			} else {
				$source_array = show_sources2($field, "fam_event_source", $witnessDb->event_id);
			}
			if ($source_array) {
				$text_array['source'] = $source_array['text'];
			}
		}
	}
	//	return $text;
	if (isset($text_array))
		return $text_array;
	else
		return '';
}

/*
 *******************************************
 *** Person was witness at (birt, baptize, etc. ) ***
 * Used for:
 * birth witness
 * baptise witness
 * death declaration
 * burial witness
 * marriage witness
 * marriage-church witness
*/

// ********************************************************************************
// * function witness_by_events (person gedcomnumber, $event item, database field);
// ********************************************************************************
function witness_by_events($gedcomnr)
{
	global $dbh, $db_functions, $tree_id, $screen_mode;
	$counter = 0;
	$text = '';
	if ($gedcomnr) {
		$witness_cls = new person_cls;

		$source_prep = $dbh->prepare("SELECT * FROM humo_events
			WHERE event_tree_id=:event_tree_id
			AND event_event=:event_event
			AND (event_kind='birth_declaration' OR event_kind='baptism_witness'
				OR event_kind='death_declaration' OR event_kind='burial_witness'
				OR event_kind='marriage_witness' OR event_kind='marriage_witness_rel')
			ORDER BY event_kind
			");
		$source_prep->bindParam(':event_tree_id', $tree_id);

		$event_event = '@' . $gedcomnr . '@';
		$source_prep->bindParam(':event_event', $event_event);

		$source_prep->execute();
		$witness_line = '';
		while ($witnessDb = $source_prep->fetch(PDO::FETCH_OBJ)) {
			if ($counter == 0) {
				//$text='<br>'.__('This person was witness at:').'<br>';
				if ($screen_mode == "PDF") {
					$text = "\n" . __('This person was witness at:') . "\n";
				} else {
					// *** March 2023: added extra empty line before witnes lines ***
					$text = "<br>\n<br>\n" . __('This person was witness at:') . "<br>\n";
				}
			}
			$counter++;
			if ($witnessDb->event_event) {

				if ($witnessDb->event_kind == 'birth_declaration' and $witness_line != 'birth declaration') {
					if ($witness_line != '') $text .= ".<br>\n";
					$text .= __('birth declaration') . ': ';
					$witness_line = 'birth declaration';
				} elseif ($witnessDb->event_kind == 'baptism_witness' and $witness_line != 'baptism witness') {
					if ($witness_line != '') $text .= ".<br>\n";
					$text .= __('baptism witness') . ': ';
					$witness_line = 'baptism witness';
				} elseif ($witnessDb->event_kind == 'death_declaration' and $witness_line != 'death declaration') {
					if ($witness_line != '') $text .= ".<br>\n";
					$text .= __('death declaration') . ': ';
					$witness_line = 'death declaration';
				} elseif ($witnessDb->event_kind == 'burial_witness' and $witness_line != 'burial_witness') {
					if ($witness_line != '') $text .= ".<br>\n";
					$text .= __('burial witness') . ': ';
					$witness_line = 'burial_witness';
				} elseif ($witnessDb->event_kind == 'marriage_witness' and $witness_line != 'marriage_witness') {
					if ($witness_line != '') $text .= ".<br>\n";
					$text .= __('marriage witness') . ': ';
					$witness_line = 'marriage_witness';
				} elseif ($witnessDb->event_kind == 'marriage_witness_rel' and $witness_line != 'marriage_witness_rel') {
					if ($witness_line != '') $text .= ".<br>\n";
					$text .= __('marriage witness (religious)') . ': ';
					$witness_line = 'marriage_witness_rel';
				} else {
					if ($counter > 1) {
						$text .= ', ';
					}
				}

				if ($witnessDb->event_kind == 'marriage_witness' or $witnessDb->event_kind == 'marriage_witness_rel') {
					// *** Connected witness by a family ***
					$fam_db = $db_functions->get_family($witnessDb->event_connect_id, 'man_woman');

					$name_man = __('N.N.');
					if (isset($fam_db->fam_man)) {
						$witness_nameDb = $db_functions->get_person($fam_db->fam_man);
						$name_man = $witness_cls->person_name($witness_nameDb);
					}

					$name_woman = __('N.N.');
					if (isset($fam_db->fam_woman)) {
						$witness_nameDb = $db_functions->get_person($fam_db->fam_woman);
						$name_woman = $witness_cls->person_name($witness_nameDb);
					}

					$text .= '<a href="family.php?id=' . $witnessDb->event_connect_id . '">' . rtrim($name_man["standard_name"]) . ' &amp; ' . rtrim($name_woman["standard_name"]) . '</a>';
				} else {
					// *** Connected witness by a person ***
					$witness_nameDb = $db_functions->get_person($witnessDb->event_connect_id);
					$name = $witness_cls->person_name($witness_nameDb);

					// *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
					$url = $witness_cls->person_url2($witness_nameDb->pers_tree_id, $witness_nameDb->pers_famc, $witness_nameDb->pers_fams, $witness_nameDb->pers_gedcomnumber);

					$text .= '<a href="' . $url . '">' . rtrim($name["standard_name"]) . '</a>';
				}
			}
			if ($witnessDb->event_date) {
				$text .= ' ' . date_place($witnessDb->event_date, '');
			} // *** Use date_place function, there is no place here... ***

			//$source_array=show_sources2($field,"pers_event_source",$witnessDb->event_id);
			//if ($source) $text.=$source_array['text'];
		}
	}
	return $text;
}
