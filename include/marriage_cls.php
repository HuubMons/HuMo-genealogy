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
	global $db, $dbh, $tree_prefix_quoted, $url_path, $dataDb, $uri_path;
	global $language, $user, $screen_mode;
	global $pdfstr;

	if ($marriageDb==''){ $marriageDb=$this->cls_marriage_Db; }

	// *** Open a person class for witnesses ***
	$person_cls = New person_cls;

	$relation_kind='';
	$relation_check=false;
	$marriage_check=false;
	$addition=__(' to: ');
	$text='';

	if ($marriageDb->fam_kind=='living together'){
		$relation_check=true;
		$relation_kind=__('Living together');
	}
	if ($marriageDb->fam_kind=='living apart together'){
		$relation_kind=__('Living apart together');
		$relation_check=true;
	}
	if ($marriageDb->fam_kind=='intentionally unmarried mother'){
		$relation_kind=__('Intentionally unmarried mother');
		$relation_check=true;
		$addition='';
	}
	if ($marriageDb->fam_kind=='homosexual'){
		$relation_check=true;
		$relation_kind=__('Homosexual');
	}
	if ($marriageDb->fam_kind=='non-marital'){
		$relation_check=true;
		$relation_kind= __('Non_marital');
		$addition='';
	}
	if ($marriageDb->fam_kind=='extramarital'){
		$relation_check=true;
		$relation_kind= __('Extramarital');
		$addition='';
	}

	// NOT TESTED
	if ($marriageDb->fam_kind=="PRO-GEN"){
		$relation_check=true;
		$relation_kind= __('Extramarital');
		$addition='';
	}

	// *** Aldfaer relations ***
	if ($marriageDb->fam_kind=='partners'){
		$relation_check=true;
		$relation_kind= __('Partner').' ';
	}
	if ($marriageDb->fam_kind=='registered'){
		$relation_check=true;
		$relation_kind= __('Registered').' ';
	}
	if ($marriageDb->fam_kind=='unknown'){
		$relation_check=true;
		$relation_kind= __('Unknown relation').' ';
	}

	// *** Living together ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_relation_date OR $marriageDb->fam_relation_place){
		$temp_text.= date_place($marriageDb->fam_relation_date,$marriageDb->fam_relation_place);
		$pdfrel["marriage_date"]=date_place($marriageDb->fam_relation_date,$marriageDb->fam_relation_place);
		$temp="marriage_date";
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_relation_text)){
		if ($temp_text){
			$temp_text.= ', ';
			if($temp) { $pdfrel[$temp].=", "; }
		}
		$temp_text.= process_text($marriageDb->fam_relation_text);
		$pdfrel["marriage_text"]=process_text($marriageDb->fam_relation_text);
		$temp="marriage_text";
	}

	// *** Living together source ***
	if ($marriageDb->fam_relation_source){
		if($screen_mode=='PDF') {
			// PDF rendering of sources
			//$pdf->Write(6,show_sources2("family","fam_relation",$marriageDb->fam_gedcomnumber)."\n");
			$pdfrel["marriage_source"]=show_sources2("family","fam_relation_source",$marriageDb->fam_gedcomnumber);
			$temp="marriage_source";
		}
		else {
			$temp_text.=show_sources2("family","fam_relation_source",$marriageDb->fam_gedcomnumber);
		}
	}

	if ($temp_text){
		$relation_check=true;
		$addition=__(' to: ');
		if ($text!=''){
			$text.='<br>';
			$pdfrel["marriage_exist"]="\n";
		}
		// *** Text "living together" already shown in "kind" ***
		// *** Just in case made an extra text "living together" here ***
		if (!$relation_kind){
			$text.=__('Living together');
			if(isset($pdfrel["marriage_exist"])) {$pdfrel["marriage_exist"].=__('Living together')." "; }
			else {$pdfrel["marriage_exist"]=__('Living together')." ";  }
		}
		$text.=' '.$temp_text;
	}

	// *** End of living together. NO end place, end text or end source yet. ***
	$temp_text='';
	$temp='';
	$fam_relation_end_place='';
	if ($marriageDb->fam_relation_end_date OR $fam_relation_end_place){
		$temp_text.= date_place($marriageDb->fam_relation_end_date,$fam_relation_end_place);
		$pdfrel["marriage_end"]='';
		if(isset($pdfrel["marriage_exist"])) { $pdfrel["marriage_end"]='. '; }
		$pdfrel["marriage_end"].=__('End living together').' '.date_place($marriageDb->fam_relation_end_date,$fam_relation_end_place);
		$temp="marriage_end";
	}
	//if ($user["group_texts_fam"]=='j' AND isset($marriageDb->fam_relation_end_text) AND process_text($marriageDb->fam_relation_end_text)){
	//	if ($temp_text){
	//		$temp_text.= ', ';
	//		if($temp) { $pdfrel[$temp].=", "; }
	//	}
	//	$temp_text.= process_text($marriageDb->fam_relation_end_text);
	//	//$pdfrel["marriage_text"]=process_text($marriageDb->fam_relation_end_text);
	//	//$temp="marriage_text";
	//}
	//// *** Living together source ***
	//if (isset($marriageDb->fam_relation_end_source) AND $marriageDb->fam_relation_end_source){
	//	unset($source_array); $source_array[]=explode(';',$marriageDb->fam_relation_end_source);
	//	$temp_text.=show_sources($source_array);
	//	//$pdfrel["marriage_source"]=show_sources($source_array);
	//	//$temp="marriage_source";
	//}
	if ($temp_text){
		if ($text!=''){
			$text.='<br>';
			//$pdfrel["marriage_exist"]="\n";
		}
		$text.=__('End living together');
		//if(isset($pdfrel["marriage_exist"])) {$pdfrel["marriage_exist"].=__('End living together')." "; }
		//else {$pdfrel["marriage_exist"]=__('End living together')." ";  }
		$text.=' '.$temp_text;
	}

	// *** Married Notice ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_marr_notice_date OR $marriageDb->fam_marr_notice_place){
		$temp_text.= date_place($marriageDb->fam_marr_notice_date,$marriageDb->fam_marr_notice_place);
		$pdfrel["prew_date"]=date_place($marriageDb->fam_marr_notice_date,$marriageDb->fam_marr_notice_place);
		$temp="prew_date";
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_marr_notice_text)){
		if ($temp_text){
			$temp_text.= ', ';
			if($temp) { $pdfrel[$temp].=", "; }
		}
		$temp_text.=process_text($marriageDb->fam_marr_notice_text);
		$pdfrel["prew_text"]=process_text($marriageDb->fam_marr_notice_text);
		$temp="prew_text";
	}

	// *** Married notice source ***
	if ($marriageDb->fam_marr_notice_source){
		if($screen_mode=='PDF') {
			// PDF rendering of sources
			$pdfrel["prew_source"]=show_sources2("family","fam_marr_notice_source",$marriageDb->fam_gedcomnumber);
			$temp="pre_source";
		}
		else {
			$temp_text.=show_sources2("family","fam_marr_notice_source",$marriageDb->fam_gedcomnumber);
		}
	}

	if ($temp_text){
		$marriage_check=true;
		$addition=__(' to: ');
		if ($text!=''){
			$text.='<br>';
			$pdfrel["prew_exist"]="\n";
		}
		$text.=__('Marriage notice').' '.$temp_text;
		if(isset($pdfrel["prew_exist"])) {$pdfrel["prew_exist"].=__('Marriage notice').' ';}
		else { $pdfrel["prew_exist"]=__('Marriage notice').' '; }
	}

	// *** Marriage ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_marr_date OR $marriageDb->fam_marr_place){
		$temp_text.= date_place($marriageDb->fam_marr_date,$marriageDb->fam_marr_place);
		$pdfrel["wedd_date"]=date_place($marriageDb->fam_marr_date,$marriageDb->fam_marr_place);
		$temp="wedd_date";
	}
	if ($marriageDb->fam_marr_authority){
		$temp_text.=' ['.$marriageDb->fam_marr_authority.']';
		$pdfrel["wedd_authority"]=" [".$marriageDb->fam_marr_authority."]";
		$temp="wedd_authority";
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_marr_text)){
		if ($temp_text){
			$temp_text.= ', ';
			if($temp) { $pdfrel[$temp].=", "; }
		}
		$temp_text.= process_text($marriageDb->fam_marr_text);
		$pdfrel["wedd_text"]=process_text($marriageDb->fam_marr_text);
		$temp="wedd_text";
	}
	// *** Aldfaer/ HuMo-gen: show witnesses ***
	if ($marriageDb->fam_gedcomnumber){
		$temp_text2=witness($marriageDb->fam_gedcomnumber, 'marriage_witness','family');
		if ($temp_text2){
			$temp_text.= ', '.__('marr. witness ').' '.$temp_text2;
			if($temp) { $pdfrel[$temp].=", "; }
			$pdfrel["wedd_witn"]= __('marr. witness ').' '.$temp_text2;
			$temp="wedd_witn";
		}
	}

	// *** Marriage source ***
	if ($marriageDb->fam_marr_source){
		if($screen_mode=='PDF') {
			// PDF rendering of sources
			$pdfrel["wedd_source"]=show_sources2("family","fam_marr_source",$marriageDb->fam_gedcomnumber);
			$temp="wedd_source";
		}
		else {
			$temp_text.=show_sources2("family","fam_marr_source",$marriageDb->fam_gedcomnumber);
		}
	}

	if ($temp_text){
		$marriage_check=true;
		$addition=__(' to: ');
		if ($text!=''){
			$text.='<br>';
			$pdfrel["wedd_exist"]="\n";
		}
		$text.=__('Married').' '.$temp_text;
		if(isset($pdfrel["wedd_exist"])) {$pdfrel["wedd_exist"].=__('Married').' ';}
		else {$pdfrel["wedd_exist"]=__('Married').' ';}
	}

	// *** Married church notice ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_marr_church_notice_date OR $marriageDb->fam_marr_church_notice_place){
		$temp_text.= date_place($marriageDb->fam_marr_church_notice_date,$marriageDb->fam_marr_church_notice_place);
		$pdfrel["prec_date"]=date_place($marriageDb->fam_marr_church_notice_date,$marriageDb->fam_marr_church_notice_place);
		$temp="prec_date";
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_marr_church_notice_text)){
		if ($temp_text){
			$temp_text.= ', ';
			if($temp) { $pdfrel[$temp].=", "; }
		}
		$temp_text.= process_text($marriageDb->fam_marr_church_notice_text);
		$pdfrel["prec_text"]= process_text($marriageDb->fam_marr_church_notice_text);
		$temp="prec_text";
	}

	// *** Married church notice source ***
	if ($marriageDb->fam_marr_church_notice_source){
		if($screen_mode=='PDF') {
			// PDF rendering of sources
			$pdfrel["prec_source"]=show_sources2("family","fam_marr_church_notice_source",$marriageDb->fam_gedcomnumber);
			$temp="prec_source";
		}
		else {
			$temp_text.=show_sources2("family","fam_marr_church_notice_source",$marriageDb->fam_gedcomnumber);
		}
	}

	if ($temp_text){
		$marriage_check=true;
		$addition=__(' to: ');
		if ($text!=''){
			$text.='<br>';
			$pdfrel["prec_exist"]="\n";
		}
		$text.=__('Married notice (religious)').' '.$temp_text;
		if(isset($pdfrel["prec_exist"])) {$pdfrel["prec_exist"].=__('Married notice (religious)').' ';}
		else {$pdfrel["prec_exist"]=__('Married notice (religious)').' ';}
	}

	// *** Married church ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_marr_church_date OR $marriageDb->fam_marr_church_place){
		$temp_text.= date_place($marriageDb->fam_marr_church_date,$marriageDb->fam_marr_church_place);
		$pdfrel["chur_date"]=date_place($marriageDb->fam_marr_church_date,$marriageDb->fam_marr_church_place);
		$temp="chur_date";
	}
	if ($user["group_texts_fam"]=='j' AND process_text($marriageDb->fam_marr_church_text)){
		if ($temp_text){
			$temp_text.= ', ';
			if($temp) { $pdfrel[$temp].=", "; }
		}
		$temp_text.= process_text($marriageDb->fam_marr_church_text);
		$pdfrel["chur_text"]=process_text($marriageDb->fam_marr_church_text);
		$temp="chur_text";
	}
	// *** Aldfaer/ HuMo-gen show witnesses ***
	if ($marriageDb->fam_gedcomnumber){
		$temp_text2=witness($marriageDb->fam_gedcomnumber, 'marriage_witness_rel','family');
		if ($temp_text2){
			$temp_text.= ', '.__('religious marr. witness').' '.$temp_text2;
			if($temp) { $pdfrel[$temp].=", "; }
			$pdfrel["chur_witn"]=__('religious marr. witness').' '.$temp_text2;
			$temp="chur_witn";
		}
	}

	// *** Married church source ***
	if ($marriageDb->fam_marr_church_source){
		if($screen_mode=='PDF') {
			// PDF rendering of sources
			$pdfrel["chur_source"]=show_sources2("family","fam_marr_church_source",$marriageDb->fam_gedcomnumber);
			$temp="chur_source";
		}
		else {
			$temp_text.=show_sources2("family","fam_marr_church_source",$marriageDb->fam_gedcomnumber);
		}
	}

	if ($temp_text){
		$marriage_check=true;
		$addition=__(' to: ');
		if ($text!=''){
			$text.='<br>';
			$pdfrel["chur_exist"]="\n";
		}
		$text.=__('Married (religious)').' '.$temp_text;
		if(isset($pdfrel["chur_exist"])) {$pdfrel["chur_exist"].=__('Married (religious)').' ';}
		else {$pdfrel["chur_exist"]=__('Married (religious)').' ';}
	}

	// *** Religion ***
	if ($user['group_religion']=='j' AND $marriageDb->fam_religion){
		if ($temp_text){
			$text.=',';
			if($temp) { $pdfrel[$temp].=", "; }
		}
		else {
			if($temp) { $pdfrel[$temp].=", "; }
		}
		$text.= ' <span class="religion">'.__('religion: ').$marriageDb->fam_religion.'</span>';
		$pdfrel["reli_reli"]=__('religion: ').$marriageDb->fam_religion;
	}

	// *** Divorse ***
	$temp_text='';
	$temp='';
	if ($marriageDb->fam_div_date OR $marriageDb->fam_div_place){
		$temp_text.= date_place($marriageDb->fam_div_date,$marriageDb->fam_div_place);
		$pdfrel["devr_date"]=date_place($marriageDb->fam_div_date,$marriageDb->fam_div_place);
		$temp="devr_date";
	}
	if ($marriageDb->fam_div_authority){
		$temp_text.=' ['.$marriageDb->fam_div_authority.']';
		$pdfrel["devr_authority"]=" [".$marriageDb->fam_div_authority."]";
		$temp="devr_authority";
	}
	if ($user["group_texts_fam"]=='j' AND $marriageDb->fam_div_text!='DIVORCE' AND process_text($marriageDb->fam_div_text)){
		if ($temp_text){
			$temp_text.= ', ';
			if($temp) { $pdfrel[$temp].=", "; }
		}
		$temp_text.= process_text($marriageDb->fam_div_text);
		$pdfrel["devr_text"]=process_text($marriageDb->fam_div_text);
		$temp="devr_text";
	}

	// *** Divorse source ***
	if ($marriageDb->fam_div_source){
		if($screen_mode=='PDF') {
			// PDF rendering of sources
			$pdfrel["devr_source"]=show_sources2("family","fam_div_source",$marriageDb->fam_gedcomnumber);
			$temp="devr_source";
		}
		else {
			$temp_text.=show_sources2("family","fam_div_source",$marriageDb->fam_gedcomnumber);
		}
	}

	//if ($temp_text){
	// *** div_text "DIVORCE" is used for divorce without further data! ***
	if ($temp_text OR $marriageDb->fam_div_text=='DIVORCE'){
		$marriage_check=true;
		$addition=' '.__('from:').' ';
		if ($text!=''){
			$text.='<br>';
			$pdfrel["devr_exist"]="\n";
		}
		$text.='<span class="divorse">'.ucfirst(__('divorced')).' '.$temp_text.'</span>';
		if(isset($pdfrel["devr_exist"])) {$pdfrel["devr_exist"].=ucfirst(__('divorced')).' ';}
		else {$pdfrel["devr_exist"]=ucfirst(__('divorced')).' ';}
	}

	// *** No relation data (marriage without date), show standard text ***
	if ($relation_check==false AND $marriage_check==false){
		// *** Show standard marriage text ***
		$text.=__('Marriage/ Related').' ';
		$pdfrel["unkn_rel"]=__('Marriage/ Related').' ';
	}

	else{
		// *** Years of marriage ***
		//if (($marriageDb->fam_marr_church_date OR $marriageDb->fam_marr_date) AND $marriageDb->fam_marr_text!='DIVORCE') {
		if (($marriageDb->fam_marr_church_date OR $marriageDb->fam_marr_date)
			AND $marriageDb->fam_div_text!='DIVORCE'
			AND !($temp_text AND $marriageDb->fam_div_date=='')
			) {

			$end_date='';

			// *** Check death date of husband ***
			$person_man=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
				WHERE pers_gedcomnumber='".$marriageDb->fam_man."'");
			@$person_manDb=$person_man->fetch(PDO::FETCH_OBJ);
			if (isset($person_manDb->pers_death_date) AND $person_manDb->pers_death_date){
				$end_date=$person_manDb->pers_death_date; }

			// *** Check death date of wife ***
			$person_woman=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
				WHERE pers_gedcomnumber='".$marriageDb->fam_woman."'");
			@$person_womanDb=$person_woman->fetch(PDO::FETCH_OBJ);
			if (isset($person_womanDb->pers_death_date) AND $person_womanDb->pers_death_date){
				// *** Check if men died earlier then woman (AT THIS MOMENT ONLY CHECK YEAR) ***
				if ($end_date AND substr($end_date,-4) > substr($person_womanDb->pers_death_date,-4)){
					$end_date=$person_womanDb->pers_death_date;
				}
				// *** Man still living or no date available  ***
				if ($end_date==''){
					$end_date=$person_womanDb->pers_death_date;
				}
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
	//if (isset($pdfstr))
	//	$pdfstr = array_merge((array)$pdfstr,(array)$result[1]);
	//else
	//	$pdfstr=$result[1];

	// *** Show objecs ***

	// *** Show events ***
	if ($user['group_event']=='j'){
		if ($marriageDb->fam_gedcomnumber){
			$event_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
				WHERE event_family_id='$marriageDb->fam_gedcomnumber' AND event_kind='event'");
			$num_rows = $event_qry->rowCount();
			if ($num_rows>0){ $text.= '<span class="event">'; }
			$i=0;
			//while($eventDb=mysql_fetch_object($event_qry)){
			while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
				$i++;
				//echo '<br>'.__('Event (family)');
				if ($text!=''){
					$text.='<br>';
				}
				if($i >1) {
					$pdfrel["event".$i."_ged"]="\n";
				}

				// *** Check if NCHI is 0 or higher ***
				$event_gedcom=$eventDb->event_gedcom;
				$event_text=$eventDb->event_text;
				if ($event_gedcom=='NCHI' AND trim($eventDb->event_text)=='0'){
					$event_gedcom='NCHI0';
					$event_text='';
				}

				$text.= language_event($event_gedcom);
				if(isset($pdfrel["event".$i."_ged"])) {$pdfrel["event".$i."_ged"].=language_event($event_gedcom); }
				else { $pdfrel["event".$i."_ged"]=language_event($event_gedcom); }

				// *** Show event kind ***
				if ($eventDb->event_event){
					$text.= ' ('.$eventDb->event_event.')';
					$pdfrel["event".$i."_event"]=' ('.$eventDb->event_event.')';
				}
				if($eventDb->event_date OR $eventDb->event_place) {
					$text.= ' '.date_place($eventDb->event_date, $eventDb->event_place);
					$pdfrel["event".$i."_date"]=' '.date_place($eventDb->event_date, $eventDb->event_place);
				}
				if($event_text) {
					$text.= ' '.$eventDb->event_text;
					$pdfrel["event".$i."_text"]=' '.$eventDb->event_text;
				}

				// *** Sources by a family event ***
				if ($eventDb->event_source){
					//if($screen_mode=='PDF') {
					//	$pdfrel["event_source"]=show_sources2("family","event_source",$eventDb->event_id);
					//	$temp="event_source";
					//}
					//else{
						$text.=show_sources2("family","event_source",$eventDb->event_id);
					//}
				}

			}
			if ($num_rows>0){
				$text.='</span><br>'; // if there are events, the word "with" should be on a new line to make the text clearer
				$pdfrel["event_lastline"]="\n";
				//$addition=ltrim(__(' to: '));
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
		$text=$relation_number.$relation_kind;
		$pdfrel = array();  //reset array - don't need it
		// *** Show divorse if privacy filter is set ***
		if ($marriageDb->fam_div_date OR $marriageDb->fam_div_place){
			$text.= ' <span class="divorse">('.__('divorced').')</span>';
		}
		// Show end of relation here?

		// *** No addition in text: 2nd marriage Hubertus [Huub] Mons ***
		if ($presentation=='shorter'){ $addition=''; }
	}
	else{
		$text=$relation_number.$relation_kind.$text;
	}

	$text.=$addition;
	$pdfrel["relnr_rel"]=$relation_number.$relation_kind;
	$pdfrel["rel_add"]=$addition;
	if($screen_mode!="PDF") {
		return $text;
	}
	else {
		return $pdfrel;
	}

} // *** End of marriage ***

} // End of class
?>