<?php
// *********************************************************************
// *** Proces marriage data                                          ***
// *** Class for HuMo-gen program                                    ***
// *********************************************************************
//error_reporting(E_ALL);
class marriage_cls{

var $cls_marriage_Db='';  // Database record
var $privacy='';  // Privacy van persoon

// *** Simple constructor, will work in all PHP versions, I hope :-)) ***
function construct($familyDb, $privacy_man, $privacy_woman){
	$this->cls_marriage_Db=$familyDb;           // Database record
	$this->privacy=$this->set_privacy($privacy_man, $privacy_woman); // Set privacy
}

// ***************************************************
// *** Marriage privacy filter                     ***
// ***************************************************
//  Privacy filter for marriage (if man OR woman privacy filter is set)
function set_privacy($privacy_man, $privacy_woman){
	global $user;
	$privacy_marriage='';
	if ($user["group_privacy"]=='n'){
		if ($privacy_man){ $privacy_marriage=1; }
		if ($privacy_woman){ $privacy_marriage=1; }
	}
	return $privacy_marriage;
}

// ***************************************************
// *** Show marriage                               ***
// ***************************************************
function marriage_data($marriageDb='', $number='0', $presentation='standard'){
	global $dbh, $db_functions, $tree_prefix_quoted, $url_path, $dataDb, $uri_path;
	global $language, $user, $screen_mode;
	global $templ_person;

	if ($marriageDb==''){ $marriageDb=$this->cls_marriage_Db; }

	// *** Open a person class for witnesses ***
	$person_cls = New person_cls;

	$relation_kind='';
	$relation_check=false;
	$marriage_check=false;
	$addition=__(' to: ');
	$text='';

	if ($marriageDb->fam_kind=='living together'){
		$relation_check=true; $relation_kind=__('Living together');
	}
	if ($marriageDb->fam_kind=='living apart together'){
		$relation_kind=__('Living apart together'); $relation_check=true;
	}
	if ($marriageDb->fam_kind=='intentionally unmarried mother'){
		$relation_kind=__('Intentionally unmarried mother'); $relation_check=true;
		$addition='';
	}
	if ($marriageDb->fam_kind=='homosexual'){
		$relation_check=true; $relation_kind=__('Homosexual');
	}
	if ($marriageDb->fam_kind=='non-marital'){
		$relation_check=true; $relation_kind= __('Non marital');
		$addition='';
	}
	if ($marriageDb->fam_kind=='extramarital'){
		$relation_check=true; $relation_kind= __('Extramarital');
		$addition='';
	}

	// NOT TESTED
	if ($marriageDb->fam_kind=="PRO-GEN"){
		$relation_check=true; $relation_kind= __('Extramarital');
		$addition='';
	}

	// *** Aldfaer relations ***
	if ($marriageDb->fam_kind=='partners'){
		$relation_check=true; $relation_kind= __('Partner').' ';
	}
	if ($marriageDb->fam_kind=='registered'){
		$relation_check=true; $relation_kind= __('Registered').' ';
	}
	if ($marriageDb->fam_kind=='unknown'){
		$relation_check=true; $relation_kind= __('Unknown relation').' ';
	}

	// *** Living together ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_relation_date OR $marriageDb->fam_relation_place){
		$templ_relation["marriage_date"]=date_place($marriageDb->fam_relation_date,$marriageDb->fam_relation_place);
		$temp="marriage_date";
		$temp_text.= $templ_relation["marriage_date"];
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_relation_text)){
		if ($temp_text){
			//$temp_text.= ', ';
			//if($temp) { $templ_relation[$temp].=", "; }
			$temp_text.= ' ';
			if($temp) { $templ_relation[$temp].=' '; }
		}
		$templ_relation["marriage_text"]=process_text($marriageDb->fam_relation_text);
		$temp="marriage_text";
		$temp_text.= $templ_relation["marriage_text"];
	}

	// *** Living together source ***
	$source=show_sources2("family","fam_relation_source",$marriageDb->fam_gedcomnumber);
	if ($source){
		if($screen_mode=='PDF') {
			$templ_relation["marriage_source"]=$source;
			$temp="marriage_source";
		}
		else
			$temp_text.=$source;
	}

	if ($temp_text){
		$relation_check=true;
		$addition=__(' to: ');
		if ($text!=''){ $text.="<br>\n"; $templ_relation["marriage_exist"]="\n"; }
		// *** Text "living together" already shown in "kind" ***
		// *** Just in case made an extra text "living together" here ***
		if (!$relation_kind){
			$text.='<b>'.__('Living together').'</b>';
			if(isset($templ_relation["marriage_exist"])) {$templ_relation["marriage_exist"].=__('Living together')." "; }
				else {$templ_relation["marriage_exist"]=__('Living together')." ";  }
		}
		$text.=' '.$temp_text;
	}

	// *** End of living together. NO end place, end text or end source yet. ***
	$temp_text='';
	$temp='';
	$fam_relation_end_place='';
	if ($marriageDb->fam_relation_end_date OR $fam_relation_end_place){
		$temp_text.= date_place($marriageDb->fam_relation_end_date,$fam_relation_end_place);
		$templ_relation["marriage_end"]='';
		if(isset($templ_relation["marriage_exist"])) { $templ_relation["marriage_end"]='. '; }
		$templ_relation["marriage_end"].=__('End living together').' '.date_place($marriageDb->fam_relation_end_date,$fam_relation_end_place);
		$temp="marriage_end";
	}
	//if ($user["group_texts_fam"]=='j' AND isset($marriageDb->fam_relation_end_text) AND process_text($marriageDb->fam_relation_end_text)){
	//	if ($temp_text){
	//		$temp_text.= ', ';
	//		if($temp) { $templ_relation[$temp].=", "; }
	//	}
	//	$temp_text.= process_text($marriageDb->fam_relation_end_text);
	//	//$templ_relation["marriage_text"]=process_text($marriageDb->fam_relation_end_text);
	//	//$temp="marriage_text";
	//}
	// *** Living together source ***
	// no source yet...
	if ($temp_text){
		$marriage_check=true;
		if ($text!='' or $relation_kind){
			$text.="<br>\n"; //$templ_relation["marriage_exist"]="\n";
		}
		$text.='<b>'.__('End living together').'</b>';
		//if(isset($templ_relation["marriage_exist"])) {$templ_relation["marriage_exist"].=__('End living together')." "; }
		//else {$templ_relation["marriage_exist"]=__('End living together')." ";  }
		$text.=' '.$temp_text;
	}

	// *** Married Notice ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_marr_notice_date OR $marriageDb->fam_marr_notice_place){
		$temp_text.= date_place($marriageDb->fam_marr_notice_date,$marriageDb->fam_marr_notice_place);
		$templ_relation["prew_date"]=date_place($marriageDb->fam_marr_notice_date,$marriageDb->fam_marr_notice_place);
		$temp="prew_date";
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_marr_notice_text)){
		if ($temp_text){
			//$temp_text.= ', ';
			//if($temp) { $templ_relation[$temp].=", "; }
			$temp_text.= ' ';
			if($temp) { $templ_relation[$temp].=" "; }
		}
		$temp_text.=process_text($marriageDb->fam_marr_notice_text);
		$templ_relation["prew_text"]=process_text($marriageDb->fam_marr_notice_text);
		$temp="prew_text";
	}

	// *** Married notice source ***
	$source=show_sources2("family","fam_marr_notice_source",$marriageDb->fam_gedcomnumber);
	if ($source){
		if($screen_mode=='PDF') {
			$templ_relation["prew_source"]=$source;
			$temp="pre_source";
		}
		else {
			$temp_text.=$source;
		}
	}

	if ($temp_text){
		$marriage_check=true;
		$addition=__(' to: ');
		if ($text!=''){ $text.="<br>\n"; $templ_relation["prew_exist"]="\n"; }
		$text.='<b>'.__('Marriage notice').'</b> '.$temp_text;
		if(isset($templ_relation["prew_exist"])) {$templ_relation["prew_exist"].=__('Marriage notice').' ';}
		else { $templ_relation["prew_exist"]=__('Marriage notice').' '; }
	}

	// *** Marriage ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_marr_date OR $marriageDb->fam_marr_place){
		$templ_relation["wedd_date"]=date_place($marriageDb->fam_marr_date,$marriageDb->fam_marr_place);
		$temp="wedd_date";
		$temp_text.= $templ_relation["wedd_date"];
	}
	if ($marriageDb->fam_marr_authority){
		$templ_relation["wedd_authority"]=" [".$marriageDb->fam_marr_authority."]";
		$temp="wedd_authority";
		$temp_text.=$templ_relation["wedd_authority"];
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_marr_text)){
		if ($temp_text){
			//$temp_text.= ', ';
			//if($temp) { $templ_relation[$temp].=", "; }
			$temp_text.= ' ';
			if($temp) { $templ_relation[$temp].=" "; }
		}
		$templ_relation["wedd_text"]=process_text($marriageDb->fam_marr_text);
		$temp="wedd_text";
		$temp_text.= $templ_relation["wedd_text"];
	}
	// *** Aldfaer/ HuMo-gen: show witnesses ***
	if ($marriageDb->fam_gedcomnumber){
		$temp_text2=witness($marriageDb->fam_gedcomnumber, 'marriage_witness','family');
		if ($temp_text2){
			$temp_text.= ' ('.__('marriage witness').' '.$temp_text2.')';

			if($temp) { $templ_relation[$temp].=" ("; }
			$templ_relation["wedd_witn"]= __('marriage witness').' '.$temp_text2.')';
			$temp="wedd_witn";
		}
	}

	// *** Marriage source ***
	$source=show_sources2("family","fam_marr_source",$marriageDb->fam_gedcomnumber);
	if ($source){
		if($screen_mode=='PDF') {
			$templ_relation["wedd_source"]=$source;
			$temp="wedd_source";
		}
		else {
			$temp_text.=$source;
		}
	}

	if ($temp_text){
		$marriage_check=true;
		$addition=__(' to: ');
		if ($text!=''){ $text.="<br>\n"; $templ_relation["wedd_exist"]="\n"; }
		$text.='<b>'.__('Married').'</b> '.$temp_text;
		if(isset($templ_relation["wedd_exist"])) {$templ_relation["wedd_exist"].=__('Married').' ';}
		else {$templ_relation["wedd_exist"]=__('Married').' ';}
	}
	else{
		// *** Marriage without further data (date or place) ***
		if ($marriageDb->fam_kind=='civil'){
			$marriage_check=true;
			$addition=__(' to: ');
			$text.='<b>'.__('Married').'</b>';
			$templ_relation["wedd_exist"]=__('Married');
		}
	}

	// *** Married church notice ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_marr_church_notice_date OR $marriageDb->fam_marr_church_notice_place){
		$templ_relation["prec_date"]=date_place($marriageDb->fam_marr_church_notice_date,$marriageDb->fam_marr_church_notice_place);
		$temp="prec_date";
		$temp_text.= $templ_relation["prec_date"];
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_marr_church_notice_text)){
		if ($temp_text){
			//$temp_text.= ', ';
			//if($temp) { $templ_relation[$temp].=", "; }
			$temp_text.= ' ';
			if($temp) { $templ_relation[$temp].=" "; }
		}
		$templ_relation["prec_text"]= process_text($marriageDb->fam_marr_church_notice_text);
		$temp="prec_text";
		$temp_text.= $templ_relation["prec_text"];
	}

	// *** Married church notice source ***
	$source=show_sources2("family","fam_marr_church_notice_source",$marriageDb->fam_gedcomnumber);
	if ($source){
		if($screen_mode=='PDF') {
			$templ_relation["prec_source"]=$source;
			$temp="prec_source";
		}
		else
			$temp_text.=$source;
	}

	if ($temp_text){
		$marriage_check=true;
		$addition=__(' to: ');
		if ($text!=''){ $text.="<br>\n"; $templ_relation["prec_exist"]="\n"; }
		$text.='<b>'.__('Married notice (religious)').'</b> '.$temp_text;
		if(isset($templ_relation["prec_exist"])) {$templ_relation["prec_exist"].=__('Married notice (religious)').' ';}
		else {$templ_relation["prec_exist"]=__('Married notice (religious)').' ';}
	}

	// *** Married church ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_marr_church_date OR $marriageDb->fam_marr_church_place){
		$templ_relation["chur_date"]=date_place($marriageDb->fam_marr_church_date,$marriageDb->fam_marr_church_place);
		$temp="chur_date";
		$temp_text.= $templ_relation["chur_date"];
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_marr_church_text)){
		if ($temp_text){
			//$temp_text.= ', ';
			//if($temp) { $templ_relation[$temp].=", "; }
			$temp_text.= ' ';
			if($temp) { $templ_relation[$temp].=" "; }
		}
		$templ_relation["chur_text"]=process_text($marriageDb->fam_marr_church_text);
		$temp="chur_text";
		$temp_text.= $templ_relation["chur_text"];
	}
	// *** Aldfaer/ HuMo-gen show witnesses ***
	if ($marriageDb->fam_gedcomnumber){
		$temp_text2=witness($marriageDb->fam_gedcomnumber, 'marriage_witness_rel','family');
		if ($temp_text2){
			$temp_text.= ' ('.__('marriage witness (religious)').' '.$temp_text2.')';
			if($temp) { $templ_relation[$temp].=" ("; }
			$templ_relation["chur_witn"]=__('marriage witness (religious)').' '.$temp_text2.')';
			$temp="chur_witn";
		}
	}

	// *** Married church source ***
	$source=show_sources2("family","fam_marr_church_source",$marriageDb->fam_gedcomnumber);
	if ($source){
		if($screen_mode=='PDF') {
			$templ_relation["chur_source"]=$source;
			$temp="chur_source";
		}
		else
			$temp_text.=$source;
	}

	if ($temp_text){
		$marriage_check=true;
		$addition=__(' to: ');
		if ($text!=''){ $text.="<br>\n"; $templ_relation["chur_exist"]="\n"; }
		$text.='<b>'.__('Married (religious)').'</b> '.$temp_text;
		if(isset($templ_relation["chur_exist"])) {$templ_relation["chur_exist"].=__('Married (religious)').' ';}
		else {$templ_relation["chur_exist"]=__('Married (religious)').' ';}
	}

	// *** Religion ***
	if ($user['group_religion']=='j' AND $marriageDb->fam_religion){
		$templ_relation["reli_reli"]=' ('.__('religion: ').$marriageDb->fam_religion.')';
		$text.= ' <span class="religion">('.__('religion: ').$marriageDb->fam_religion.')</span>';
	}

	// *** Divorse ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_div_date OR $marriageDb->fam_div_place){
		$templ_relation["devr_date"]=date_place($marriageDb->fam_div_date,$marriageDb->fam_div_place);
		$temp="devr_date";
		$temp_text.= $templ_relation["devr_date"];
	}
	if ($marriageDb->fam_div_authority){
		$templ_relation["devr_authority"]=" [".$marriageDb->fam_div_authority."]";
		$temp="devr_authority";
		$temp_text.=$templ_relation["devr_authority"];
	}
	if ($user["group_texts_fam"]=='j' AND $marriageDb->fam_div_text!='DIVORCE' AND process_text($marriageDb->fam_div_text)){
		if ($temp_text){
			//$temp_text.= ', ';
			//if($temp) { $templ_relation[$temp].=", "; }
			$temp_text.= ' ';
			if($temp) { $templ_relation[$temp].=" "; }
		}
		$templ_relation["devr_text"]=process_text($marriageDb->fam_div_text);
		$temp="devr_text";
		$temp_text.= $templ_relation["devr_text"];
	}

	// *** Divorse source ***
	$source=show_sources2("family","fam_div_source",$marriageDb->fam_gedcomnumber);
	if ($source){
		if($screen_mode=='PDF') {
			$templ_relation["devr_source"]=$source;
			$temp="devr_source";
		}
		else
			$temp_text.=$source;
	}

	//if ($temp_text){
	// *** div_text "DIVORCE" is used for divorce without further data! ***
	if ($temp_text OR $marriageDb->fam_div_text=='DIVORCE'){
		$marriage_check=true;
		$addition=' '.__('from:').' ';
		if ($text!=''){ $text.="<br>\n"; $templ_relation["devr_exist"]="\n"; }
		$text.='<span class="divorse"><b>'.ucfirst(__('divorced')).'</b> '.$temp_text.'</span>';
		if(isset($templ_relation["devr_exist"])) {$templ_relation["devr_exist"].=ucfirst(__('divorced')).' ';}
		else {$templ_relation["devr_exist"]=ucfirst(__('divorced')).' ';}
	}

	// *** No relation data (marriage without date), show standard text ***
	if ($relation_check==false AND $marriage_check==false){
		// *** Show standard marriage text ***
		$templ_relation["unkn_rel"]=__('Marriage/ Related').' ';
		$text.='<b>'.__('Marriage/ Related').'</b> ';
	}
	else{
		// *** Years of marriage ***
		if (($marriageDb->fam_marr_church_date OR $marriageDb->fam_marr_date)
			AND $marriageDb->fam_div_text!='DIVORCE'
			AND !($temp_text AND $marriageDb->fam_div_date==''))
		{
			$end_date='';

			// *** Check death date of husband ***
			@$person_manDb=$db_functions->get_person($marriageDb->fam_man);
			if (isset($person_manDb->pers_death_date) AND $person_manDb->pers_death_date) $end_date=$person_manDb->pers_death_date;

			// *** Check death date of wife ***
			@$person_womanDb=$db_functions->get_person($marriageDb->fam_woman);
			if (isset($person_womanDb->pers_death_date) AND $person_womanDb->pers_death_date){
				// *** Check if men died earlier then woman (AT THIS MOMENT ONLY CHECK YEAR) ***
				if ($end_date AND substr($end_date,-4) > substr($person_womanDb->pers_death_date,-4)){
					$end_date=$person_womanDb->pers_death_date;
				}
				// *** Man still living or no date available  ***
				if ($end_date=='') $end_date=$person_womanDb->pers_death_date;
			}

			// *** End of marriage by divorse ***
			if ($marriageDb->fam_div_date){ $end_date=$marriageDb->fam_div_date; }

			$marr_years = New calculate_year_cls;
			$age=$marr_years->calculate_marriage($marriageDb->fam_marr_church_date,$marriageDb->fam_marr_date,$end_date);

			$text.=$age;  // Space and komma in $age
			//PDF?
		}

	}

	// *** Show media/ pictures ***
	//$text.=show_media('',$marriageDb); // *** This function can be found in file: show_picture.php! ***
	$result = show_media('',$marriageDb); // *** This function can be found in file: show_picture.php! ***
	$text.= $result[0];
	//if (isset($templ_person))
	//	$templ_person = array_merge((array)$templ_person,(array)$result[1]);
	//else
	//	$templ_person=$result[1];

	// *** Show objecs ***

	// *** Show events ***
	if ($user['group_event']=='j'){
		if ($marriageDb->fam_gedcomnumber){
			$event_qry=$db_functions->get_events_family($marriageDb->fam_gedcomnumber,'event');
			$num_rows=count($event_qry);
			if ($num_rows>0){ $text.= '<span class="event">'; }
			$i=0;
			foreach($event_qry as $eventDb){
				$i++;
				//echo '<br>'.__('Event (family)');
				if ($text!=''){
					$text.="<br>\n";
				}
				if($i >1) {
					$templ_relation["event".$i."_ged"]="\n";
				}

				// *** Check if NCHI is 0 or higher ***
				$event_gedcom=$eventDb->event_gedcom;
				$event_text=$eventDb->event_text;
				if ($event_gedcom=='NCHI' AND trim($eventDb->event_text)=='0'){
					$event_gedcom='NCHI0';
					$event_text='';
				}

				$text.= '<b>'.language_event($event_gedcom).'</b>';
				if(isset($templ_relation["event".$i."_ged"])) {$templ_relation["event".$i."_ged"].=language_event($event_gedcom); }
				else { $templ_relation["event".$i."_ged"]=language_event($event_gedcom); }

				// *** Show event kind ***
				if ($eventDb->event_event){
					$templ_relation["event".$i."_event"]=' ('.$eventDb->event_event.')';
					$text.= $templ_relation["event".$i."_event"];
				}
				if($eventDb->event_date OR $eventDb->event_place) {
					$templ_relation["event".$i."_date"]=' '.date_place($eventDb->event_date, $eventDb->event_place);
					$text.= $templ_relation["event".$i."_date"];
				}
				if($event_text) {
					$templ_relation["event".$i."_text"]=' '.process_text($eventDb->event_text);
					$text.= $templ_relation["event".$i."_text"];
				}

				// *** Sources by a family event ***
				$source=show_sources2("family","fam_event_source",$eventDb->event_id);
				if ($source){
					if($screen_mode=='PDF') {
					//	$templ_relation["event_source"]=show_sources2("family","fam_event_source",$eventDb->event_id);
					//	$temp="fam_event_source";
					}
					else
						$text.=$source;
				}

			}
			if ($num_rows>0){
				$text.="</span><br>\n"; // if there are events, the word "with" should be on a new line to make the text clearer
				$templ_relation["event_lastline"]="\n";
				$addition=ltrim($addition);
			}
		}
	}

	// **********************************
	// *** Concacenate marriage texts ***
	// **********************************

	// Process english 1st, 2nd, 3rd and 4th marriage.
	$relation_number='';
	//if ($number!=''){
	if ($presentation=='short' OR $presentation=='shorter'){
		if ($number=='1'){ $relation_number=__('1st'); }
		if ($number=='2'){ $relation_number=__('2nd'); }
		if ($number=='3'){ $relation_number=__('3rd'); }
		if ($number>'3') { $relation_number=$number.__('th'); }

		if ($marriage_check==true){
			if ($number){
				$relation_number.=' '.__('marriage');     // marriage
				$relation_kind='';
				$addition=__(' to: ');
			}
			else{
				$relation_number.=__('Married ');       // Married
				$relation_kind='';
				$addition=__(' to: ');
			}
		}

		if ($relation_check==true){
			if ($number){
				$relation_number.=' '.__('related');   // relation
				$relation_kind='';
				$addition=__(' to: ');
			}
			else{
				$relation_number=ucfirst(__('related')).' ';      // Relation
				$relation_kind='';
				$addition=__(' to: ');
			}
		}

		if ($relation_check==false AND $marriage_check==false){
			if ($number){
				// *** Other text in 2nd marriage: 2nd marriage Hubertus [Huub] Mons ***
				if ($presentation=='shorter'){
					$relation_number.=' '.__('marriage/ relation');   // relation
				}
				else{
					$relation_number.=' '.__('marriage/ related');   // relation
				}
				$relation_kind='';
				$addition=__(' to: ');
			}
			else{
				$relation_number.=__('Marriage/ Related');      // Relation
				$relation_kind='';
				$addition=__(' to: ');
			}
		}

	}

	if ($presentation=='short' OR $presentation=='shorter'){
		$text='<b>'.$relation_number.$relation_kind.'</b>';
		$templ_relation = array();  //reset array - don't need it
		// *** Show divorse if privacy filter is set ***
		if ($marriageDb->fam_div_date OR $marriageDb->fam_div_place OR $marriageDb->fam_div_text){
			$text.= ' <span class="divorse">('.__('divorced').')</span>';
		}
		// Show end of relation here?

		// *** No addition in text: 2nd marriage Hubertus [Huub] Mons ***
		if ($presentation=='shorter'){ $addition=''; }
	}
	else{
		$text='<b>'.$relation_number.$relation_kind.'</b>'.$text;
	}
	if ($addition) $text.='<b>'.$addition.'</b>';

	$templ_relation["relnr_rel"]=$relation_number.$relation_kind;
	$templ_relation["rel_add"]=$addition;
	if($screen_mode!="PDF") {
		return $text;
	}
	else {
		return $templ_relation;
	}

} // *** End of marriage ***

} // End of class
?>