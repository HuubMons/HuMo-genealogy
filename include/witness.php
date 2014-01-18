<?php
// *******************************************
// *** Show witness (birt, baptize, etc. ) ***
// * Used for:
// * birth witness
// * baptise witness
// * death declaration
// * burial witness
// * marriage witness
// * marriage-church witness

// *******************************************
// * function witness (person gedcomnumber, $event item, database field);
// *******************************************
function witness($gedcomnr,$event, $field='person'){
	global $db, $dbh;
	$counter=0; $text='';
	if ($gedcomnr){
		$witness_cls = New person_cls;
		//$witness_qry=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."events
		//	WHERE event_".$field."_id='".$gedcomnr."' AND event_kind='".$event."'",$db);
		$witness_qry=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."events
			WHERE event_".$field."_id='".$gedcomnr."' AND event_kind='".$event."'");
		//while($witnessDb=mysql_fetch_object($witness_qry)){
		while($witnessDb=$witness_qry->fetch(PDO::FETCH_OBJ)){
			$counter++; if ($counter>1){ $text.=', '; }
			if ($witnessDb->event_event){
				if (substr($witnessDb->event_event,0,1)=='@'){
					// *** Connected witness ***
					//$witness_name=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
					//WHERE pers_gedcomnumber='".substr($witnessDb->event_event,1,-1)."'",$db);
					//$witness_nameDb=mysql_fetch_object($witness_name);
					$witness_name=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
					WHERE pers_gedcomnumber='".substr($witnessDb->event_event,1,-1)."'");
					$witness_nameDb=$witness_name->fetch(PDO::FETCH_OBJ);					
					$name=$witness_cls->person_name($witness_nameDb);
					//$text.='<a href="'.$_SERVER['PHP_SELF'].'?id='.$witness_nameDb->pers_indexnr.'">'.
					//rtrim($name["standard_name"]).'</a>';

					$text.='<a href="'.$_SERVER['PHP_SELF'].'?id='.$witness_nameDb->pers_indexnr.'&amp;main_person='.$witness_nameDb->pers_gedcomnumber.'">'.rtrim($name["standard_name"]).'</a>';
				}
				else{
				  // *** Witness as text ***
				  $text.=$witnessDb->event_event;
				}
			}
			if ($witnessDb->event_date){ $text.=' '.date_place($witnessDb->event_date,''); } // *** Use date_place function, there is no place here... ***
			if ($witnessDb->event_source){
				$text.=show_sources2($field,"event_source",$witnessDb->event_id);
			}
		}
	}
	return $text;
}
?>