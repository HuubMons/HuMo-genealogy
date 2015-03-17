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
function witness($gedcomnr,$event, $field='person'){
	global $dbh, $db_functions;
	$counter=0; $text='';
	if ($gedcomnr){
		$witness_cls = New person_cls;
		if ($field=='person')
			$witness_qry = $db_functions->get_events_person($gedcomnr,$event);
		else
			$witness_qry = $db_functions->get_events_family($gedcomnr,$event);
		foreach ($witness_qry as $witnessDb){
			$counter++; if ($counter>1){ $text.=', '; }
			if ($witnessDb->event_event){
				if (substr($witnessDb->event_event,0,1)=='@'){
					// *** Connected witness ***
					$witness_nameDb = $db_functions->get_person(substr($witnessDb->event_event,1,-1));
					$name=$witness_cls->person_name($witness_nameDb);
					$text.='<a href="'.$_SERVER['PHP_SELF'].'?id='.$witness_nameDb->pers_indexnr.'&amp;main_person='.$witness_nameDb->pers_gedcomnumber.'">'.rtrim($name["standard_name"]).'</a>';
				}
				else{
					// *** Witness as text ***
					$text.=$witnessDb->event_event;
				}
			}
			if ($witnessDb->event_date){ $text.=' '.date_place($witnessDb->event_date,''); } // *** Use date_place function, there is no place here... ***

			$source=show_sources2($field,"pers_event_source",$witnessDb->event_id);
			if ($source) $text.=$source;
		}
	}
	return $text;
}
?>