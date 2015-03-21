<?php
// *****************************************************
// *** Process person data                           ***
// *** Class for HuMo-gen program                    ***
// *** $templ_person is used for PDF reports         ***
// *****************************************************
//error_reporting(E_ALL);
class person_cls{

var $personDb='';  // Database record
var $privacy='';  // Person privacy

// *** Simple constructor, will work in all PHP versions, I hope :-)) ***
function construct($personDb){
	$this->personDb=$personDb;	// Database record
	$this->privacy=$this->set_privacy($personDb); // Set privacy
}

// ***************************************************
// *** Privacy person                             ***
// ***************************************************
function set_privacy($personDb){
	global $user, $dataDb;
	$privacy_person='';  // *** Standard: show all persons ***
	if ($user['group_privacy']=='n'){
		$privacy_person="1";  // *** Standard: filter privacy data of person ***
		// *** $personDb is empty by N.N. person ***
		if ($personDb){
			// *** HuMo-gen, Haza-data and Aldfaer alive/ deceased status ***
			if ($user['group_alive']=="j"){
				if ($personDb->pers_alive=='deceased'){ $privacy_person=''; }
				if ($personDb->pers_alive=='alive'){ $privacy_person='1'; }
			}

			// *** Privacy filter: date ***
			if ($user["group_alive_date_act"]=="j"){
				if ($personDb->pers_birth_date){
					if (substr($personDb->pers_birth_date,-4) < $user["group_alive_date"]){ $privacy_person=''; }
						else $privacy_person='1'; // *** overwrite pers_alive status ***
				}
				if ($personDb->pers_bapt_date){
					if (substr($personDb->pers_bapt_date,-4) < $user["group_alive_date"]){ $privacy_person=''; }
						else $privacy_person='1'; // *** overwrite pers_alive status ***
				}

				// *** Check if deceased persons should be filtered ***
				if ($user["group_filter_death"]=='n'){
					// *** If person is deceased, filter is off ***
					if ($personDb->pers_death_date or $personDb->pers_death_place){ $privacy_person=''; }
					if ($personDb->pers_buried_date or $personDb->pers_buried_place){ $privacy_person=''; }
					// *** pers_alive for deceased persons without date ***
					if ($personDb->pers_alive=='deceased'){ $privacy_person=''; }
				}
			}

			// *** Privacy filter: date ***
			if ($user["group_death_date_act"]=="j"){
				if ($personDb->pers_death_date){
					if (substr($personDb->pers_death_date,-4) < $user["group_death_date"]){ $privacy_person=''; }
						else $privacy_person='1'; // *** overwrite pers_alive status ***
				}
				if ($personDb->pers_buried_date){
					if (substr($personDb->pers_buried_date,-4) < $user["group_death_date"]){ $privacy_person=''; }
						else $privacy_person='1'; // *** overwrite pers_alive status ***
				}
			}

			// *** Filter person's WITHOUT any date's ***
			if ($user["group_filter_date"]=='j'){
				if ($personDb->pers_birth_date=='' AND $personDb->pers_bapt_date==''
				AND $personDb->pers_death_date=='' AND $personDb->pers_buried_date==''){
					$privacy_person='';
				}
			}

			// *** Privacy filter exceptions (added a space for single character check) ***
			if ($user["group_filter_pers_show_act"]=='j'
				AND strpos(' '.$personDb->pers_own_code,$user["group_filter_pers_show"])>0){ $privacy_person=""; }
			if ($user["group_filter_pers_hide_act"]=='j'
				AND strpos(' '.$personDb->pers_own_code,$user["group_filter_pers_hide"])>0){ $privacy_person="1"; }
		}

	}

	// *** Completely filter a person, if option "completely filter a person" is activated ***
	if ($personDb){
		if ($user["group_pers_hide_totally_act"]=='j'
			AND strpos(' '.$personDb->pers_own_code,$user["group_pers_hide_totally"])>0){ $privacy_person="1"; }
	}

	// *** Privacy filter for whole family tree ***
	if (isset($dataDb->tree_privacy)){
		if ($dataDb->tree_privacy=='filter_persons'){ $privacy_person="1"; }
		if ($dataDb->tree_privacy=='show_persons'){ $privacy_person=""; }
	}
	return $privacy_person;
}

// *************************************************************
// *** Show person name standard                             ***
// *************************************************************
// *** Remark: it's necessary to use $personDb because of witnesses, parents etc. ***
function person_name($personDb){
	global $dbh, $db_functions, $user, $language, $screen_mode, $selection;
	global $humo_option;

	$tree_prefix_quoted='';
	if ($personDb) $tree_prefix_quoted=$personDb->pers_tree_prefix;
	$db_functions->set_tree_prefix($tree_prefix_quoted);

	$stillborn=''; $nobility=''; $lordship='';
	$title_before=''; $title_between=''; $title_after='';

	if (isset($personDb->pers_gedcomnumber) AND $personDb->pers_gedcomnumber){
		// *** Aldfaer: nobility (predikaat) by name ***
		$name_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'nobility');
		foreach($name_qry as $nameDb) $nobility.=$nameDb->event_event.' ';
		unset($name_qry);

		// *** Gedcom 5.5 title: NPFX ***
		$name_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'NPFX');
		foreach($name_qry as $nameDb) $title_before.=' '.$nameDb->event_event.' ';
		unset($name_qry);

		// *** Gedcom 5.5 title: NSFX ***
		$name_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'NSFX');
		foreach($name_qry as $nameDb) $title_after.=' '.$nameDb->event_event.' ';
		unset($name_qry);

		// *** Aldfaer: title by name ***
		/*
		DUTCH Titles FOR DUTCH Genealogical program ALDFAER!
		Title BEFORE name:
			Prof., Dr., Dr.h.c., Dr.h.c.mult., Ir., Mr., Drs., Lic., Kand., Bacc., Ing., Bc., em., Ds.
		Title BETWEEN pers_firstname and pers_lastname:
			prins, prinses, hertog, hertogin, markies, markiezin, markgraaf, markgravin, graaf,
			gravin, burggraaf, burggravin, baron, barones, ridder
		Title AFTER name:
			All other titles.
		*/
		$name_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'title');
		foreach($name_qry as $nameDb){
			$title_position='after';
			if ($nameDb->event_event=='Prof.'){ $title_position='before'; }
			if ($nameDb->event_event=='Dr.'){ $title_position='before'; }
			if ($nameDb->event_event=='Dr.h.c.'){ $title_position='before'; }
			if ($nameDb->event_event=='Dr.h.c.mult.'){ $title_position='before'; }
			if ($nameDb->event_event=='Ir.'){ $title_position='before'; }
			if ($nameDb->event_event=='Mr.'){ $title_position='before'; }
			if ($nameDb->event_event=='Drs.'){ $title_position='before'; }
			if ($nameDb->event_event=='Lic.'){ $title_position='before'; }
			if ($nameDb->event_event=='Kand.'){ $title_position='before'; }
			if ($nameDb->event_event=='Bacc.'){ $title_position='before'; }
			if ($nameDb->event_event=='Ing.'){ $title_position='before'; }
			if ($nameDb->event_event=='Bc.'){ $title_position='before'; }
			if ($nameDb->event_event=='em.'){ $title_position='before'; }
			if ($nameDb->event_event=='Ds.'){ $title_position='before'; }

			if ($nameDb->event_event=='prins'){ $title_position='between'; }
			if ($nameDb->event_event=='prinses'){ $title_position='between'; }
			if ($nameDb->event_event=='hertog'){ $title_position='between'; }
			if ($nameDb->event_event=='hertogin'){ $title_position='between'; }
			if ($nameDb->event_event=='markies'){ $title_position='between'; }
			if ($nameDb->event_event=='markiezin'){ $title_position='between'; }
			if ($nameDb->event_event=='markgraaf'){ $title_position='between'; }
			if ($nameDb->event_event=='markgravin'){ $title_position='between'; }
			if ($nameDb->event_event=='graaf'){ $title_position='between'; }
			if ($nameDb->event_event=='gravin'){ $title_position='between'; }
			if ($nameDb->event_event=='burggraaf'){ $title_position='between'; }
			if ($nameDb->event_event=='burggravin'){ $title_position='between'; }
			if ($nameDb->event_event=='baron'){ $title_position='between'; }
			if ($nameDb->event_event=='barones'){ $title_position='between'; }
			if ($nameDb->event_event=='ridder'){ $title_position='between'; }

			if ($title_position=='before') $title_before.=' '.$nameDb->event_event.' ';
			if ($title_position=='between') $title_between.=' '.$nameDb->event_event.' ';
			if ($title_position=='after') $title_after.=', '.$nameDb->event_event;
		}
		unset($name_qry);

		// ***Still born child ***
		if (isset($personDb->pers_stillborn) AND $personDb->pers_stillborn=="y"){
			if ($personDb->pers_sexe=='M'){$stillborn.=' '.__('stillborn boy');}
			elseif ($personDb->pers_sexe=='F'){$stillborn.=' '.__('stillborn girl');}
			else $stillborn.=' '.__('stillborn child');
		}

		// *** Aldfaer: lordship (heerlijkheid) after name ***
		$name_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'lordship');
		foreach($name_qry as $nameDb) $lordship.=', '.$nameDb->event_event;
		unset($name_qry);

		// *** Re-calculate privacy filter for witness names and parents ***
		$privacy=$this->set_privacy($personDb);

		// *** Privacy filter: show only first character of firstname. Like: D. Duck ***
		$pers_firstname=$personDb->pers_firstname;
		if ($pers_firstname!='N.N.' AND $privacy AND $user['group_filter_name']=='i'){
			$names = explode(' ', $personDb->pers_firstname);
			$pers_firstname = "";
			foreach($names as $character){
				if (substr($character, 0,1)!='(' AND substr($character, 0,1)!='['){
					$pers_firstname .= ucfirst(substr($character, 0,1)).". ";
				}
			}
		}

		$privacy_name=''; if ($privacy AND $user['group_filter_name']=='n'){ $privacy_name=__('Name filtered'); }

		// *** Completely filter person ***
		if ($user["group_pers_hide_totally_act"]=='j'
			AND strpos(' '.$personDb->pers_own_code,$user["group_pers_hide_totally"])>0){ $privacy_name=__('Name filtered'); }

		if ($privacy_name){
			$name_array["show_name"]=false;
			$name_array["firstname"]=$privacy_name;
			$name_array["name"]=$privacy_name;
			$name_array["short_firstname"]=$privacy_name;
			$name_array["standard_name"]=$privacy_name;
			$name_array["index_name"]=$privacy_name;
			$name_array["index_name_extended"]=$privacy_name;
			$name_array["initials"]="-.-.";
		}
		else{
			// *** Hide or show name (privacy) ***
			$name_array["show_name"]=true;

			// *** Firstname only ***
			$name_array["firstname"]=$pers_firstname;

			// *** Firstname, prefix and lastname ***
			$name_array["name"]=$pers_firstname." ";
			$name_array["name"].=str_replace("_", " ", $personDb->pers_prefix);
			$name_array["name"].=$personDb->pers_lastname;

			// *** Short firstname, prefix and lastname ***
			$name_array["short_firstname"]=substr ($personDb->pers_firstname, 0, 1)." ";
			$name_array["short_firstname"].=str_replace("_", " ", $personDb->pers_prefix);
			$name_array["short_firstname"].=$personDb->pers_lastname;

			// *** $name_array["standard_name"] ***
			// *** Example: Predikaat Hubertus [Huub] van Mons, Title, 2nd title ***
			$name_array["standard_name"]=$nobility.$title_before.$pers_firstname." ";
			if ($personDb->pers_patronym){ $name_array["standard_name"].=" ".$personDb->pers_patronym." "; }
			$name_array["standard_name"].=$title_between;
			$name_array["standard_name"].=str_replace("_", " ", $personDb->pers_prefix);
			$name_array["standard_name"].=$personDb->pers_lastname;
			if ($title_after){ $name_array["standard_name"].=$title_after; }
			$name_array["standard_name"].=$stillborn;
			$name_array["standard_name"].=$lordship;
			$name_array["standard_name"]=trim($name_array["standard_name"]);

			// *** Name for indexes or search results in lastname order ***
			// *** "index_name_extended" includes patronym and stillborn. ***
			$prefix1=''; $prefix2='';
			// *** Option to show "van Mons" of "Mons, van" ***
			if($user['group_kindindex']=="j") {
				$prefix1=str_replace("_"," ",$personDb->pers_prefix);
			}
			else {
				$prefix2=" ".str_replace("_"," ",$personDb->pers_prefix);
			}

			$name_array["index_name"]=$prefix1;
			$name_array["index_name_extended"]=$prefix1;
			if ($personDb->pers_lastname){
				$name_array["index_name"].=$personDb->pers_lastname.', ';
				$name_array["index_name_extended"].=$personDb->pers_lastname.', ';
			}
			$name_array["index_name"].=$pers_firstname.$prefix2;

			$name_array["index_name_extended"].=$pers_firstname;

			if ($title_after){ $name_array["index_name_extended"].=$title_after; }

			if ($personDb->pers_patronym){ $name_array["index_name_extended"].=' '.$personDb->pers_patronym;}
			$name_array["index_name_extended"].=$stillborn;
			// *** If a special name is found in the results (event table), show it **
			if (isset($personDb->event_event) AND $personDb->event_event AND $personDb->event_kind=='name'){
				$name_array["index_name_extended"].=' ('.$personDb->event_event.')';}
			$name_array["index_name_extended"].=$prefix2;
			
			if($humo_option['name_order']=="chinese") {  
				// for Chinese no commas or spaces
				$name_array["name"] = $personDb->pers_lastname." ".$personDb->pers_firstname;
				$name_array["short_firstname"] = $personDb->pers_lastname." ".$personDb->pers_firstname;
				$name_array["standard_name"] = $personDb->pers_lastname." ".$personDb->pers_firstname;
				$name_array["index_name_extended"] = $personDb->pers_lastname." ".$personDb->pers_firstname;
			}			

			// *** Is search is done for profession, show profession **
			if ($selection['pers_profession']){
				if (isset($personDb->event_event) AND $personDb->event_event AND $personDb->event_kind=='profession'){
					$name_array["index_name_extended"].=' ('.$personDb->event_event.')';}
			}

			// *** Is search is done for places, show place **
			if ($selection['pers_place']){
				if (isset($personDb->address_place)){
					$name_array["index_name_extended"].=' ('.$personDb->address_place.')';
				}
			}

			// *** Is search is done for places, show place **
			if ($selection['zip_code']){
				if (isset($personDb->address_zip)){
					$name_array["index_name_extended"].=' ('.$personDb->address_zip.')';
				}
			}

			// *** Is search is done for places, show place **
			if ($selection['witness']){
				if (isset($personDb->event_event)){
					$name_array["index_name_extended"].=' ('.$personDb->event_event.')';
				}
			}

			// *** $name["initials"] used in report_descendant.php ***
			// *** Example: H.M. ***
			$name_array["initials"]=substr($personDb->pers_firstname,0,1).'.'.substr($personDb->pers_lastname,0,1).'.';
			
			if($humo_option['name_order']=="chinese") {  
				// for Chinese no commas or spaces, anyway few characters
				$name_array["initials"]= $personDb->pers_lastname." ".$personDb->pers_firstname;
			}
		}

		// *** Colour mark by person ***
		$name_array["colour_mark"]=''; $person_colour_mark='';
		$colour=$db_functions->get_events_person($personDb->pers_gedcomnumber,'person_colour_mark');
		foreach($colour as $colourDb){
			if ($colourDb AND $screen_mode!="PDF" AND $screen_mode!="RTF"){
				$pers_colour='style="-moz-border-radius: 40px; border-radius: 40px;';
				$person_colour_mark=$colourDb->event_event;
				if ($person_colour_mark=='1'){ $pers_colour.=' background-color:#FF0000;'; }
				if ($person_colour_mark=='2'){ $pers_colour.=' background-color:#00FF00;'; }
				if ($person_colour_mark=='3'){ $pers_colour.=' background-color:#0000FF;'; }
				if ($person_colour_mark=='4'){ $pers_colour.=' background-color:#FF00FF;'; }
				if ($person_colour_mark=='5'){ $pers_colour.=' background-color:#FFFF00;'; }
				if ($person_colour_mark=='6'){ $pers_colour.=' background-color:#00FFFF;'; }
				if ($person_colour_mark=='7'){ $pers_colour.=' background-color:#C0C0C0;'; }
				if ($person_colour_mark=='8'){ $pers_colour.=' background-color:#800000;'; }
				if ($person_colour_mark=='9'){ $pers_colour.=' background-color:#008000;'; }
				if ($person_colour_mark=='10'){ $pers_colour.=' background-color:#000080;'; }
				if ($person_colour_mark=='11'){ $pers_colour.=' background-color:#800080;'; }
				if ($person_colour_mark=='12'){ $pers_colour.=' background-color:#A52A2A;'; }
				if ($person_colour_mark=='13'){ $pers_colour.=' background-color:#008080;'; }
				if ($person_colour_mark=='14'){ $pers_colour.=' background-color:#808080;'; }
				$pers_colour.='"';
				$name_array["colour_mark"].=' <span '.$pers_colour.'>&nbsp;&nbsp;&nbsp;</span>';
			}
		}
		unset($colour);

	}
	else{
		$name_array["show_name"]=true;
		$name_array["firstname"]=__('N.N.');
		$name_array["name"]='';
		$name_array["short_firstname"]=__('N.N.');
		$name_array["standard_name"]=__('N.N.');
		$name_array["index_name"]=__('N.N.');
		$name_array["index_name_extended"]=__('N.N.');
		$name_array["initials"]="-.-.";
	}

	return $name_array;
}


// *************************************************************
// *** Show person popup menu                                ***
// *************************************************************
// *** $extended=true; Show a full persons pop-up including picture and person data ***
// *** $replacement_text='text'; Replace the pop-up icon by the replacement_text ***
// *** $extra_popup_text=''; To add extra text in the pop-up screen ***
function person_popup_menu($personDb, $extended=false, $replacement_text='',$extra_popup_text=''){
	global $dbh, $tree_prefix_quoted, $db_functions, $bot_visit, $humo_option, $uri_path, $user, $language;
	global $screen_mode, $dirmark1, $dirmark2, $rtlmarker;
	global $selected_language, $hourglass;
	$text='';
	$privacy=$this->privacy;

	// *** Show pop-up menu ***
	if (!$bot_visit AND $screen_mode!="PDF" AND $screen_mode!="RTF") {

		// *** Family tree for search in multiple family trees ***
		$tree_prefix=safe_text($personDb->pers_tree_prefix);
		$db_functions->set_tree_prefix($tree_prefix);

		if (CMS_SPECIFIC=='Joomla'){
			$start_url='index.php?option=com_humo-gen&amp;task=family&amp;database='.$tree_prefix.
				'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
		}
		elseif ($humo_option["url_rewrite"]=="j"){
			// *** $uri_path made in header.php ***
			$start_url=$uri_path.'family/'.$tree_prefix.'/'.$personDb->pers_indexnr.'/'.$personDb->pers_gedcomnumber.'/';
		}
		else{
			$start_url=CMS_ROOTPATH.'family.php?database='.$tree_prefix.
			'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
		}

		$family_url=$start_url;

		// *** Change start url for a person in a graphical ancestor report ***
		if ($screen_mode=='ancestor_chart' AND $hourglass===false){
			if (CMS_SPECIFIC=='Joomla'){
				$start_url='index.php?option=com_humo-gen&amp;task=ancestor&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber.'&amp;screen_mode=ancestor_chart';
			}
			else{
				$start_url=CMS_ROOTPATH.'report_ancestor.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber.'&amp;screen_mode=ancestor_chart';
			}
		}

		// *** Change start url for a person in a graphical descendant report ***
		if (($screen_mode=='STAR' OR $screen_mode=='STARSIZE') AND $hourglass===false) {
			if (CMS_SPECIFIC=='Joomla'){
				$start_url='index.php?option=com_humo-gen&amp;task=family&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=STAR';
			}
			else{
				$start_url=CMS_ROOTPATH.'family.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=STAR';
			}
		}

		// *** Change start url for a person in an hourglass chart ***
		if (($screen_mode=='STAR' OR $screen_mode=='STARSIZE' OR $screen_mode=='ancestor_chart') AND $hourglass===true){ 
			if (CMS_SPECIFIC=='Joomla'){
				$start_url='index.php?option=com_humo-gen&amp;task=hourglass&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=HOUR';
			}
			else{
				$start_url=CMS_ROOTPATH.'hourglass.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=HOUR';
			}
		}

		$text.='<div class="'.$rtlmarker.'sddm" style="display:inline;">'."\n";

			$text.= '<a href="'.$start_url.'"';
			if ($extended){
				//$text.= ' class="nam" style="font-size:9px; text-align:center; display:block; width:100%; height:100%" ';
				$text.= ' class="nam" style="z-index:100;font-size:10px; display:block; width:100%; height:100%" ';
			}

			$random_nr=rand(); // *** Generate a random number to avoid double numbers ***
			$text.= ' onmouseover="mopen(event,\'m1'.$random_nr.$personDb->pers_gedcomnumber.'\',0,0)"';
			$text.= ' onmouseout="mclosetime()">';
			if ($replacement_text){
				$text.=$replacement_text;
			}
			else{
				$text.= '<img src="'.CMS_ROOTPATH.'images/reports.gif" border="0" alt="reports">';
			}
			$text.= '</a>';

			// *** Remove standard background color from links ***
			//echo '
			//<style>
			//.ltrsddm div a{ background:none; }
			//</style>
			//';

			// *** Added style="z-index:40;" for ancestor and descendant report ***
			$text.= '<div style="z-index:40; border:1px solid #999999;" id="m1'.$random_nr.$personDb->pers_gedcomnumber.
				'" class="sddm_fixed person_popup" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

				$name=$this->person_name($personDb);
				//$text.=$dirmark2.'<span style="font-size:14px; font-weight:bold; color:blue;">'.$name["standard_name"].$name["colour_mark"].'</span><br>';
				$text.=$dirmark2.'<span style="font-size:13px; font-weight:bold; color:blue;">'.$name["standard_name"].$name["colour_mark"].'</span><br>';
				if ($extended)
					$text.='<table><tr><td style="width:auto; border: solid 0px; border-right:solid 1px #999999;">';

				$text.= '<b>'.__('Text reports').':</b>';
				$text.= $dirmark1.'<a href="'.$family_url.'"><img src="'.CMS_ROOTPATH.'images/family.gif" border="0" alt="'.__('Family group sheet').'"> '.__('Family group sheet').'</a>';

				if  ($user['group_gen_protection']=='n' AND $personDb->pers_fams!='') {
					// *** Only show a descendant_report icon if there are children ***
					$check_children=false;
					$check_family=explode(";",$personDb->pers_fams);
					for ($i=0; $i<=substr_count($personDb->pers_fams, ";"); $i++){
						//@$check_childrenDb=$db_functions->get_family($check_family[$i]);
						@$check_childrenDb=$db_functions->get_family($check_family[$i]);
						if ($check_childrenDb->fam_children){ $check_children=true; }
					}
					if ($check_children){

						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=family&amp;database='.$tree_prefix.
								'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;descendant_report=1';
						}
						else{
							$path_tmp=CMS_ROOTPATH.'family.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;descendant_report=1';
						}
						$text.='<a href="'.$path_tmp.'"><img src="'.CMS_ROOTPATH.'images/descendant.gif" border="0" alt="'.__('Descendant report').'"> '.__('Descendant report').'</a>';

						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=outline&amp;database='.$tree_prefix.
								'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;descendant_report=1';
						}
						else{
							$path_tmp=CMS_ROOTPATH.'report_outline.php?database='.$tree_prefix.
				'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
						}
						$text.= '<a href="'.$path_tmp.'"><img src="'.CMS_ROOTPATH.'images/outline.gif" border="0" alt="'.__('Outline report').'"> '.__('Outline report').'</a>';
					}
				}

				if  ($user['group_gen_protection']=='n' AND $personDb->pers_famc!='') {
					// == Ancestor report: link & icons by Klaas de Winkel (www.dewinkelwaar.tk) ==
					if (CMS_SPECIFIC=='Joomla'){
						$path_tmp='index.php?option=com_humo-gen&amp;task=ancestor&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber;
					}
					else{
						$path_tmp=CMS_ROOTPATH.'report_ancestor.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber;
					}
					$text.= '<a href="'.$path_tmp.'"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" border="0" alt="'.__('Ancestor report').'"> '.__('Ancestor report').'</a>';

					$text.= '<a href="'.$path_tmp.'&amp;screen_mode=ancestor_sheet"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" border="0" alt="'.__('Ancestor sheet').'"> '.__('Ancestor sheet').'</a>';
				}

				if  ($user['group_gen_protection']=='n' AND ($personDb->pers_famc!='' OR $personDb->pers_fams!='')) {
					$text.= '<b>'.__('Charts').':</b>';
				}
				if  ($user['group_gen_protection']=='n' AND $personDb->pers_famc!='') {
					// == added by Yossi Beck - FANCHART
					if (CMS_SPECIFIC=='Joomla'){
						$path_tmp='index.php?option=com_humo-gen&amp;task=fanchart&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber;
					}
					else{
						$path_tmp=CMS_ROOTPATH.'fanchart.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber;
					}
					$text.= '<a href="'.$path_tmp.'"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" border="0" alt="Fanchart"> '.__('Fanchart').'</a>';

					if (CMS_SPECIFIC=='Joomla'){
						$path_tmp='index.php?option=com_humo-gen&amp;task=ancestor&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber.'&amp;screen_mode=ancestor_chart';
					}
					else{
						$path_tmp=CMS_ROOTPATH.'report_ancestor.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber.'&amp;screen_mode=ancestor_chart';
					}
					$text.= '<a href="'.$path_tmp.'"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" border="0" alt="'.__('Ancestor chart').'"> '.__('Ancestor chart').'</a>';
				}
				if  ($user['group_gen_protection']=='n' AND $personDb->pers_fams!='') {
					if ($check_children){

						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=family&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=STAR';
						}
						else{
							$path_tmp=CMS_ROOTPATH.'family.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=STAR';
						}
						$text.= '<a href="'.$path_tmp.'"><img src="'.CMS_ROOTPATH.'images/descendant.gif" border="0" alt="'.__('Descendant chart').'"> '.__('Descendant chart').'</a>';
					}
				}
				// DNA charts
				if  ($user['group_gen_protection']=='n' AND ($personDb->pers_famc!="" OR ($personDb->pers_fams!="" AND $check_children))) {
					//if ($check_children){
					if($personDb->pers_sexe=="M") $charttype="ydnamark";
					else $charttype="mtdnamark";
						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=family&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=STAR&amp;dnachart='.$charttype;
						}
						else{
							$path_tmp=CMS_ROOTPATH.'family.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=STAR&amp;dnachart='.$charttype;
						}
						$text.= '<a href="'.$path_tmp.'"><img src="'.CMS_ROOTPATH.'images/dna.png" border="0" alt="'.__('DNA Charts').'"> '.__('DNA Charts').'</a>';
					//}
				}
				
				if  ($user['group_gen_protection']=='n' AND $personDb->pers_famc!='' AND $personDb->pers_fams!='' AND $check_children) {
				// hourglass only if there is at least one generation of ancestors and of children.
					if (CMS_SPECIFIC=='Joomla'){
						//$path_tmp='index.php?option=com_humo-gen&amp;task=hourglass&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber;
						$path_tmp='index.php?option=com_humo-gen&amp;task=hourglass&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=HOUR';
					}
					else{
						//$path_tmp=CMS_ROOTPATH.'hourglass.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber;
						$path_tmp=CMS_ROOTPATH.'hourglass.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber.'&amp;screen_mode=HOUR';
					}
					$text.= '<a href="'.$path_tmp.'"><img src="'.CMS_ROOTPATH.'images/hourglass.gif" border="0" alt="'.__('Hourglass chart').'"> '.__('Hourglass chart').'</a>';
				}
				// check for timeline folder and tml files
				if ($privacy==''){
					$tmlcounter=0;
					$tmldir=CMS_ROOTPATH."languages/".$selected_language."/timelines";
					if (file_exists($tmldir)) {
						$dh  = opendir(CMS_ROOTPATH."languages/".$selected_language."/timelines");
						while (false !== ($filename = readdir($dh))) {
							if (strtolower(substr($filename, -3)) == "txt"){
								$tmlcounter++;
							}
						}
					}
					$tmldates=0;
					if ($personDb->pers_birth_date OR $personDb->pers_bapt_date
						OR $personDb->pers_death_date OR $personDb->pers_buried_date OR $personDb->pers_fams) {
						$tmldates=1;
					}
					if ($user['group_gen_protection']=='n' AND $tmlcounter>0 AND $tmldates==1) {
						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=timelines&amp;database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber;
						}
						else{
							$path_tmp=CMS_ROOTPATH.'timelines.php?database='.$tree_prefix.'&amp;id='.$personDb->pers_gedcomnumber;
						}
						$text.= '<a href="'.$path_tmp.'">';
						$text.= '<img src="'.CMS_ROOTPATH.'images/timeline.gif" border="0" alt="'.__('Timeline').'"> '.__('Timeline').'</a>';
					}
				}

				// *** Editor link ***
				//if  ($user['group_editor']=='j' OR $user['group_admin']=='j') {
				if  ($user['group_edit_trees'] OR $user['group_admin']=='j') {
					$edit_tree_array=explode(";",$user['group_edit_trees']);
					// *** Administrator can always edit in all family trees ***
					if ($user['group_admin']=='j' OR in_array($_SESSION['tree_id'], $edit_tree_array)) {
						$text.= '<b>'.__('Admin').':</b>';
						$path_tmp=CMS_ROOTPATH.'admin/index.php?page=editor&amp;tree='.$tree_prefix.'&amp;person='.$personDb->pers_gedcomnumber;
						$text.= '<a href="'.$path_tmp.'" target="_blank">';
						$text.= '<img src="'.CMS_ROOTPATH.'images/person_edit.gif" border="0" alt="'.__('Timeline').'"> '.__('Editor').'</a>';
					}
				}

				// *** Show person picture and person data at right side of the pop-up box ***
				if ($extended){
					$text.='</td><td style="width:auto; border: solid 0px; font-size: 10px;" valign="top">';

					// *** Show picture in pop-up box ***
					if (!$privacy AND $user['group_pictures']=='j'){
						//  *** Path can be changed per family tree ***
						global $dataDb;
						$tree_pict_path=$dataDb->tree_pict_path;
						$picture_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'picture');
						// *** Only show 1st picture ***
						if (isset($picture_qry[0])){
							$pictureDb=$picture_qry[0];
							$picture=show_picture($tree_pict_path,$pictureDb->event_event,'',120);
							$text.='<img src="'.$tree_pict_path.$picture['thumb'].$picture['picture'].'" style="margin-left:10px; margin-top:5px;" alt="'.$pictureDb->event_text.'" height="'.$picture['height'].'">';
							$text.='<br>';
						}
					}

					// *** Pop-up tekst ***
					if ($privacy==''){
						if ($personDb->pers_birth_date OR $personDb->pers_birth_place){
							$text.=__('*').$dirmark1.' '.
								date_place($personDb->pers_birth_date,$personDb->pers_birth_place);
						}
						elseif ($personDb->pers_bapt_date OR $personDb->pers_bapt_place){
							$text.=__('~').$dirmark1.' '.
								date_place($personDb->pers_bapt_date,$personDb->pers_bapt_place);
						}

						if ($personDb->pers_death_date OR $personDb->pers_death_place){
							$text.='<br>'.__('&#134;').$dirmark1.' '.
								date_place($personDb->pers_death_date,$personDb->pers_death_place);
						}
						elseif ($personDb->pers_buried_date OR $personDb->pers_buried_place){
							$text.='<br>'.__('[]').$dirmark1.' '.
								date_place($personDb->pers_buried_date,$personDb->pers_buried_place);
						}

						// *** If needed add extra text in the pop-up box ***
						if ($extra_popup_text){$text.=$extra_popup_text;}

					}
					else{
						$text.=' '.__('PRIVACY FILTER');
					}

					$text.='</td></tr></table>';
				} // *** End of extended pop-up ***

			$text.= $dirmark1.'</div>';
		$text.= '</div>'."\n";
	}  // end "if not pdf"

	return $text;
}


// ************************************************************************
// *** Show person name and name of parents                             ***
// *** $person_kind = 'child' generates a link by a child to his family ***
// ***                                                                  ***
// *** Oct. 2013: added name of parents after the name of a person.     ***
// ************************************************************************
function name_extended($person_kind){
	global $dbh, $db_functions, $humo_option, $uri_path, $user, $language;
	global $screen_mode, $dirmark1, $dirmark2, $rtlmarker;
	global $selected_language, $family_expanded, $bot_visit;
	global $sect; // *** RTF Export ***

	$text_name=''; $text_name2=''; $text_colour=''; $text_parents=''; $child_marriage='';

	$personDb=$this->personDb;
	$privacy=$this->privacy;
	if (!$personDb){
		// *** Show unknown person N.N. ***
		$text_name= __('N.N.');
	}
	else{
		$tree_prefix_quoted=$personDb->pers_tree_prefix;
		$db_functions->set_tree_prefix($tree_prefix_quoted);

		// *** Show pop-up menu ***
		$text_name.= $this->person_popup_menu($personDb);

		// *** Check privacy filter ***
		if ($privacy AND $user['group_filter_name']=='n'){
			//dummy
		}
		else{
			// *** Show man or woman picture ***
			if($screen_mode!="PDF") {  //  pdf does this elsewhere

				if($screen_mode=="RTF") {  //  rtf does this in family.php
					//
				}
				else{
					$text_name.= $dirmark1;
					if ($personDb->pers_sexe=="M")
						$text_name.='<img src="'.CMS_ROOTPATH.'images/man.gif" alt="man">';
					elseif ($personDb->pers_sexe=="F")
						$text_name.='<img src="'.CMS_ROOTPATH.'images/woman.gif" alt="woman">';
					else
						$text_name.='<img src="'.CMS_ROOTPATH.'images/unknown.gif" alt="unknown">';
				}

				// *** Source by sexe ***
				$source=show_sources2("person","pers_sexe_source",$personDb->pers_gedcomnumber).' ';
				if ($source){
					$text_name.=$source;
				}
			}
		}

		$name=$this->person_name($personDb);
		$standard_name= $name["standard_name"].$dirmark2;

		// *** Show gedcomnummer #I5 as #5 ***
		if ($user['group_gedcomnr']=='j'){
			$show_gedcomnumber=$personDb->pers_gedcomnumber;
			if (substr($show_gedcomnumber, 0, 1)=='I')
				$show_gedcomnumber=substr($show_gedcomnumber, 1);
			$standard_name.= $dirmark1." #".$show_gedcomnumber;
		}

		// *** Check privacy filter for callname ***
		if (($privacy AND $user['group_filter_name']=='n')
			OR ($user["group_pers_hide_totally_act"]=='j' AND strpos($personDb->pers_own_code,$user["group_pers_hide_totally"])>0)){
			//
		}
		else{
			if ($personDb->pers_callname){$standard_name.= ', '.__('Nickname').': '.$personDb->pers_callname;}
		}

		// *** No links if gen_protection is enabled ***
		if ($user["group_gen_protection"]=='j'){ $person_kind=''; }
		if (($person_kind=='child' OR $person_kind=='outline') AND $personDb->pers_fams){
			$child_link=explode(";",$personDb->pers_fams);
			// *** Link to 1st family of person ***
			if(CMS_SPECIFIC=='Joomla') {
				$standard_name='<a href="index.php?option=com_humo-gen&task=family&database='.$_SESSION['tree_prefix'].
				'&amp;id='.$child_link[0].'&amp;main_person='.
				$personDb->pers_gedcomnumber.'">'.$standard_name; // Link to 1st family of person
			}
			elseif ($humo_option["url_rewrite"]=="j"){
				// *** $uri_path is made in header.php ***
				$standard_name='<a href="'.$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.
				$child_link[0].'/'.$personDb->pers_gedcomnumber.'/">'.$standard_name;
			}
			else{
				$standard_name='<a href="'.CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].
				'&amp;id='.$child_link[0].'&amp;main_person='.
				$personDb->pers_gedcomnumber.'">'.$standard_name; // Link to 1st family of person
			}
			// *** Show name with link ***
			$text_name=$text_name.$standard_name.'</a>';
		}
		else
			$text_name.=$standard_name; // *** Show name without link **

		// *** Check privacy filter ***
		$text_name2='';
		if ($privacy){
			//dummy
		}
		else{
			// *** Text by name ***
			if ($user["group_texts_pers"]=='j'){
				$work_text=process_text($personDb->pers_name_text);
				if ($work_text){
					//$templ_person["name_text"]=", ".$work_text;
					$templ_person["name_text"]=" ".$work_text;
					$text_name2.=$templ_person["name_text"];
				}
			}

			// *** Source by name ***
			$source=show_sources2("person","pers_name_source",$personDb->pers_gedcomnumber);
			if ($source){
// PDF doesn't work...
				if($screen_mode=='PDF') {
					$templ_person["name_source"]=$source;
					$temp="name_source";
				}
				else
					$text_name2.=$source;
			}

		}

		// *** Add colour marks to person ***
		$text_colour=$name["colour_mark"];

		// *********************************************************************
		// *** Show: son of/ daughter of/ child of name-father & name-mother ***
		// *********************************************************************
		if (($person_kind=='parent1' OR $person_kind=='parent2') AND $personDb->pers_famc){
			if ($personDb->pers_sexe=='M'){ $text_parents.=__('son of').' '; }
			if ($personDb->pers_sexe=='F'){ $text_parents.=__('daughter of').' '; }
			if ($personDb->pers_sexe==''){ $text_parents.=__('child of').' '; }
			if ($family_expanded==true){
				$text_parents=ucfirst($text_parents);
			}
			else{
				$templ_person["parent_childof"]=', '.$text_parents;
				$temp="parent_childof";
				$text_parents=$templ_person["parent_childof"];
			}

			// *** Find parents ID ***
			$parents_familyDb=$db_functions->get_family($personDb->pers_famc);

			// *** Father ***
			if ($parents_familyDb->fam_man){
				$fatherDb=$db_functions->get_person($parents_familyDb->fam_man);
				$name=$this->person_name($fatherDb);
				$templ_person["parents"]=$name["standard_name"];
			}
			else{
				$templ_person["parents"]=__('N.N.');
			}

			$templ_person["parents"].=' '.__('and').' ';
			$temp="parents";
			$text=$templ_person["parents"];

			// *** Mother ***
			if ($parents_familyDb->fam_woman){
				$motherDb=$db_functions->get_person($parents_familyDb->fam_woman);
				$name=$this->person_name($motherDb);
				$templ_person["parents"].=$name["standard_name"];
				$text.=$name["standard_name"];
			}
			else{ $text.=__('N.N.'); }

			// *** Add link for parents ***
			if(CMS_SPECIFIC=='Joomla') {
				$text2='<a href="index.php?option=com_humo-gen&task=family&database='.$_SESSION['tree_prefix'].'&amp;id='.$personDb->pers_famc;
				if (isset($fatherDb->pers_gedcomnumber)){ $text2.='&amp;main_person='.$fatherDb->pers_gedcomnumber; }
				$text2.='">';
			}
			elseif ($humo_option["url_rewrite"]=="j"){
				// *** $uri_path is made in header.php ***
				$text2='<a href="'.$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.$personDb->pers_famc;
				// *** Added this number for better Google indexing links (otherwise too many links to index) ***
				if (isset($fatherDb->pers_gedcomnumber)){ $text2.= '/'.$fatherDb->pers_gedcomnumber; }
				$text2.='/">';
			}
			else{
				$text2='<a href="'.$uri_path.'family.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$personDb->pers_famc;
				if (isset($fatherDb->pers_gedcomnumber)){ $text2.='&amp;main_person='.$fatherDb->pers_gedcomnumber; }
				$text2.='">';
			}

			// *** Add link ***
			if ($user['group_gen_protection']=='n'){ $text=$text2.$text.'</a>'; }

			$text_parents.='<span class="parents">'.$text.$dirmark2.'.</span>';
		}


		// *********************************************************************************************
		// *** Check for adoptive parents (just for shure: made it for multiple adoptive parents...) ***
		// *********************************************************************************************
		if ($person_kind=='parent1' OR $person_kind=='parent2'){
			$famc_adoptive_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'adoption');
			foreach ($famc_adoptive_qry as $famc_adoptiveDb){
				$text_parents.=' '.ucfirst(__('adoption parents')).': ';

				// *** Just in case: empty $text ***
				$text='';
				// *** Find parents ID ***
				$parents_familyDb=$db_functions->get_family($famc_adoptiveDb->event_event);

				//*** Father ***
				if ($parents_familyDb->fam_man){
					$fatherDb=$db_functions->get_person($parents_familyDb->fam_man);
					$name=$this->person_name($fatherDb);
					$text=$name["standard_name"];
					//$templ_person["parents"]=$name["standard_name"];
					//$temp="parents";
				}
				else{
					$text=__('N.N.');
					//$templ_person["parents"]=__('N.N.');
					//$temp="parents";
				}

				$text.=' '.__('and').' ';
				//$templ_person["parents"].=' '.__('and').' ';
				//$temp="parents";

				//*** Mother ***
				if ($parents_familyDb->fam_woman){
					$motherDb=$db_functions->get_person($parents_familyDb->fam_woman);
					$name=$this->person_name($motherDb);
					$text.=$name["standard_name"];
					//$templ_person["parents"].=$name["standard_name"];
					//$temp="parents";
				}
				else{ $text.=__('N.N.'); }

				if(CMS_SPECIFIC=='Joomla') {
					$text2='<a href="index.php?option=com_humo-gen&task=family&database='.$_SESSION['tree_prefix'].'&amp;id='.$famc_adoptiveDb->event_event;
					if (isset($fatherDb->pers_gedcomnumber)){ $text2.='&amp;main_person='.$fatherDb->pers_gedcomnumber; }
					$text2.='">';
				}
				elseif ($humo_option["url_rewrite"]=="j"){
					// *** $uri_path is gemaakt in header.php ***
					$text2='<a href="'.$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.$famc_adoptiveDb->event_event;
					// *** Dit nummer toegevoegd, anders krijgt Google heel veel te indexeren gezinnen ***
					if (isset($fatherDb->pers_gedcomnumber)){ $text2.= '/'.$fatherDb->pers_gedcomnumber; }
					$text2.='/">';
				}
				else{
					$text2='<a href="'.$uri_path.'family.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$famc_adoptiveDb->event_event;
					if (isset($fatherDb->pers_gedcomnumber)){ $text2.='&amp;main_person='.$fatherDb->pers_gedcomnumber; }
					$text2.='">';
				}

				// *** Add link ***
				if ($user['group_gen_protection']=='n'){ $text=$text2.$text.'</a>'; }

				//$text_parents.='<span class="parents">'.$text.$dirmark2.' </span>';
				$text_parents.='<span class="parents">'.$text.' </span>';
			}
		}


		// ********************************************************
		// *** Check for adoptive parent ESPECIALLY FOR ALDFAER ***
		// ********************************************************
		if ($person_kind=='parent1' OR $person_kind=='parent2'){
			$famc_adoptive_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'adoption_by_person');
			foreach ($famc_adoptive_qry as $famc_adoptiveDb){
				if($famc_adoptiveDb->event_gedcom=='steph') $text_parents.=' '.ucfirst(__('stepparent')).': ';
				elseif($famc_adoptiveDb->event_gedcom=='legal') $text_parents.=' '.ucfirst(__('legal parent')).': ';
				elseif($famc_adoptiveDb->event_gedcom=='foster') $text_parents.=' '.ucfirst(__('foster parent')).': ';
				else $text_parents.=' '.ucfirst(__('adoption parent')).': ';

				//*** Father ***
				//if ($parents_familyDb->fam_man){
					$fatherDb=$db_functions->get_person($famc_adoptiveDb->event_event);
					$name=$this->person_name($fatherDb);
					$text=$name["standard_name"];
					//$templ_person["parents"]=$name["standard_name"];
					//$temp="parents";
				//}
				//else{
				//	$text=__('N.N.');
					//$templ_person["parents"]=__('N.N.');
					//$temp="parents";
				//}

				if (isset($fatherDb->pers_indexnr)){
					if(CMS_SPECIFIC=='Joomla') {
						$text2='<a href="index.php?option=com_humo-gen&task=family&database='.$_SESSION['tree_prefix'].'&amp;id='.$fatherDb->pers_indexnr;
						if (isset($fatherDb->pers_gedcomnumber)){ $text2.='&amp;main_person='.$fatherDb->pers_gedcomnumber; }
						$text2.='">';
					}
					elseif ($humo_option["url_rewrite"]=="j"){
						// *** $uri_path is gemaakt in header.php ***
						$text2='<a href="'.$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.$fatherDb->pers_indexnr;
						// *** Dit nummer toegevoegd, anders krijgt Google heel veel te indexeren gezinnen ***
						if (isset($fatherDb->pers_gedcomnumber)){ $text2.= '/'.$fatherDb->pers_gedcomnumber; }
						$text2.='/">';
					}
					else{
						$text2='<a href="'.$uri_path.'family.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$fatherDb->pers_indexnr;
						if (isset($fatherDb->pers_gedcomnumber)){ $text2.='&amp;main_person='.$fatherDb->pers_gedcomnumber; }
						$text2.='">';
					}
				}

				// *** Add link ***
				if ($user['group_gen_protection']=='n'){ $text=$text2.$text.'</a>'; }

				//$text_parents.='<span class="parents">'.$text.$dirmark2.' </span>';
				$text_parents.='<span class="parents">'.$text.' </span>';
			}
		}


		//*** Show spouse/ partner by child ***
		if (!$bot_visit AND $person_kind=='child' AND $personDb->pers_fams){
			$marriage_array=explode(";",$personDb->pers_fams);
			$nr_marriages=count($marriage_array);
			for ($x=0; $x<=$nr_marriages-1; $x++){
				$fam_partnerDb=$db_functions->get_family($marriage_array[$x]);

				// *** This check is better then a check like: $personDb->pers_sexe=='F', because of unknown sexe or homosexual relations. ***
				if ($personDb->pers_gedcomnumber==$fam_partnerDb->fam_man)
					$partner_id=$fam_partnerDb->fam_woman;
				else
					$partner_id=$fam_partnerDb->fam_man;

				//$relation_short=__('&');
				$relation_short=__('relationship with');
				if ($fam_partnerDb->fam_marr_date OR $fam_partnerDb->fam_marr_place OR $fam_partnerDb->fam_marr_church_date OR $fam_partnerDb->fam_marr_church_place OR $fam_partnerDb->fam_kind=='civil'){
					//$relation_short=__('X');
					$relation_short=__('married to');
					if ($nr_marriages>1) $relation_short=__('marriage with');
				}
				if($fam_partnerDb->fam_div_date OR $fam_partnerDb->fam_div_place){
					//$relation_short=__(') (');
					$relation_short=__('divorced from');
					if ($nr_marriages>1) $relation_short=__('marriage (divorced) with');
				}

				if ($partner_id!='0' AND $partner_id!=''){
					$partnerDb=$db_functions->get_person($partner_id);
					$partner_cls = New person_cls;
					$name=$partner_cls->person_name($partnerDb);
					$famc=$partnerDb->pers_indexnr; // *** Used for partner link ***
					$pers_gedcomnumber=$partnerDb->pers_gedcomnumber;
				}
				else{
					$name["standard_name"]=__('N.N.');
					$famc=$personDb->pers_indexnr; // *** For partner link, if partner is N.N. ***
					$pers_gedcomnumber=$personDb->pers_gedcomnumber;
				}

				// *** Link for partner ***
				if ($humo_option["url_rewrite"]=="j"){
					// *** $uri_path is made in header.php ***
					$text_link='<a href="'.$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.$famc;
					// *** Added this number for better Google indexing links (otherwise too many links to index) ***
					//if (isset($fatherDb->pers_gedcomnumber)){ $text2.= '/'.$fatherDb->pers_gedcomnumber; }
					$text_link.= '/'.$pers_gedcomnumber;
					$text_link.='/">';
				}
				else{
					$text_link='<a href="'.$uri_path.'family.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$famc;
					//if (isset($fatherDb->pers_gedcomnumber)){ $text2.='&amp;main_person='.$fatherDb->pers_gedcomnumber; }
					$text_link.='&amp;main_person='.$pers_gedcomnumber;
					$text_link.='">';
				}
				$name["standard_name"]=$text_link.$name["standard_name"].'</a>';

				//$child_marriage.=' <span class="index_partner" style="font-size:10px;">';
				if ($nr_marriages>1){
					if ($x==0) $child_marriage.= ' '.__('1st');
					elseif ($x==1) $child_marriage.= ', '.__('2nd');
					elseif ($x==2) $child_marriage.= ', '.__('3rd');
					elseif ($x>2) $child_marriage.= ', '.($x+1).__('th');
				}
				else
					$child_marriage.= ' ';
				$child_marriage.= ' '.$relation_short.' '.$dirmark1.$name["standard_name"].$dirmark1;
				//$child_marriage.='</span>';
			}
		}
		// *** End spouse/ partner ***

	}

	if ($family_expanded==true){
		$text_parents='<div class="margin_person">'.$text_parents.'</div>';
		$child_marriage='<div class="margin_child">'.$child_marriage.'</div>';
	}

	//return '<span class="pers_name">'.$text_name.$dirmark1.'</span>'.$text_name2.$text_colour.$text_parents.$child_marriage;
	if ($screen_mode=='RTF')
		return '<b>'.$text_name.$dirmark1.'</b>'.$text_name2.$text_colour.$text_parents.$child_marriage;
	else
		return '<span class="pers_name">'.$text_name.$dirmark1.'</span>'.$text_name2.$text_colour.$text_parents.$child_marriage;
}


// ***************************************************************************************
// *** Show person                                                                     ***
// *** $personDb = data person                                                         ***
// *** $person_kind = parent2 (normally the woman, generates links to all marriages)   ***
// *** $id = family id for link by woman for multiple marrriages                       ***
// *** $privacy = privacyfilter                                                        ***
// ***************************************************************************************
function person_data($person_kind, $id){
	global $dbh, $db_functions, $tree_id, $dataDb, $user, $language, $humo_option, $family_id, $uri_path;
	global $family_expanded, $change_main_person;
	global $childnr, $screen_mode, $dirmark1, $dirmark2;
	global $templ_person;
	global $sect, $arial12; // *** RTF export ***

	unset($templ_person);

	$personDb=$this->personDb;
	$privacy=$this->privacy;

	// *** Settings for mobile version, show details in multiple lines ***
	if ($person_kind=="mobile") $family_expanded=true;

	// *** $personDb is empty by N.N. person ***
	if ($personDb){
	$tree_prefix_quoted=$personDb->pers_tree_prefix;
	$db_functions->set_tree_prefix($tree_prefix_quoted);


	$process_text=''; $temp='';

	//*** PRIVACY PART ***
	$privacy_filter='';
	if ($privacy){
		if($screen_mode=="mobile")
			return __('PRIVACY FILTER');
		elseif($screen_mode!="PDF")
			$privacy_filter=' '.__('PRIVACY FILTER');  // Show privacy text
		else
			return;  // makes no sense to ask for login in a pdf report.....
	}
	else{
		// *** Quality (function show_quality can be found in script: family.php) ***
		// Disabled because normally quality belongs to a source.
		//if ($personDb->pers_quality=='0' or $personDb->pers_quality){
		//	$quality_text=show_quality($personDb->pers_quality);
		//	$process_text.= ' <i>'.ucfirst($quality_text).'</i>';
		//}

		// *** Show extra names of BK ***
		if ($personDb->pers_gedcomnumber){
			$eventnr=0;
			$name_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'name');
			foreach ($name_qry as $nameDb){
				$eventnr++;
				$text='';
				if ($nameDb->event_gedcom=='_AKAN') $text.=__('Also known as').': ';
				// *** MyHeritage Family Tree Builder ***
				if ($nameDb->event_gedcom=='_AKA') $text.=__('Also known as').': ';
				if ($nameDb->event_gedcom=='NICK') $text.=__('Nickname').': ';
				if ($nameDb->event_gedcom=='_ALIA') $text.=__('alias name').': ';	// For Pro-Gen
				if ($nameDb->event_gedcom=='_SHON') $text.=__('Short name (for reports)').': ';
				if ($nameDb->event_gedcom=='_ADPN') $text.=__('Adopted name').': ';
				if ($nameDb->event_gedcom=='_HEBN') $text.=__('Hebrew name').': ';
				if ($nameDb->event_gedcom=='_CENN') $text.=__('Census name').': ';
				if ($nameDb->event_gedcom=='_MARN') $text.=__('Married name').': ';
				if ($nameDb->event_gedcom=='_GERN') $text.=__('Nickname').': ';
				if ($nameDb->event_gedcom=='_FARN') $text.=__('Farm name').': ';
				if ($nameDb->event_gedcom=='_BIRN') $text.=__('Birth name').': ';
				if ($nameDb->event_gedcom=='_INDN') $text.=__('Indian name').': ';
				if ($nameDb->event_gedcom=='_FKAN') $text.=__('Formal name').': ';
				if ($nameDb->event_gedcom=='_CURN') $text.=__('Current name').': ';
				if ($nameDb->event_gedcom=='_SLDN') $text.=__('Soldier name').': ';
				if ($nameDb->event_gedcom=='_FRKA') $text.=__('Formerly known as').': ';
				if ($nameDb->event_gedcom=='_RELN') $text.=__('Religious name').': ';
				if ($nameDb->event_gedcom=='_OTHN') $text.=__('Other name').': ';

				if ($eventnr>1){
					$templ_person["bknames".$eventnr]=', ';
					$process_text.=', ';
					$text=lcfirst($text);
				}
				else{
					$templ_person["bknames".$eventnr]='';
					$text=ucfirst($text);
				}
				$templ_person["bknames".$eventnr].=$text;
				if($templ_person["bknames".$eventnr]!='') $temp="bk_names".$eventnr;
				$process_text.=$text;

				if ($nameDb->event_date){
					$templ_person["bk_date".$eventnr]=date_place($nameDb->event_date,'').' ';
					if($templ_person["bk_date".$eventnr]!='') $temp="bk_date".$eventnr;
					$process_text.=$templ_person["bk_date".$eventnr];
				}

				$templ_person["bk_event".$eventnr]=$nameDb->event_event;
				if($templ_person["bk_event".$eventnr]!='') $temp="bk_event".$eventnr;
				$process_text.=$nameDb->event_event;

				$source=show_sources2("person","pers_event_source",$nameDb->event_id);
				if ($source){
					if($screen_mode=='PDF') {
						$templ_person["bk_source".$eventnr]=$source;
						$temp="bk_source".$eventnr;
					}
					else
						$process_text.=$source;
				}

				if ($nameDb->event_text) {
					$templ_person["bk_text".$eventnr]=' '.$nameDb->event_text;
					$temp="bk_text".$eventnr;
					$process_text.=$templ_person["bk_text".$eventnr];
				}
			}
			unset ($name_qry);
		}

		// *** Own code ***
		if ($user['group_own_code']=='j' AND $personDb->pers_own_code){
			if($temp) { $templ_person[$temp].=", "; }
			//$templ_person["own_code"]='('.ucfirst($personDb->pers_own_code).')';
			$templ_person["own_code"]=ucfirst($personDb->pers_own_code);
			$temp="own_code";

			if (!$process_text OR $family_expanded==true) $text='<b>'.__('Own code').':</b> ';
				else $text=', <b>'.lcfirst(__('Own code')).':</b> ';

			//	if ($process_text) $process_text.=', ';
			//$process_text.='<span class="pers_own_code">'.$templ_person["own_code"].'</span>';
			$process_text.='<span class="pers_own_code">'.$text.$templ_person["own_code"].'</span>';
		}

		// ****************
		// *** BIRTH    ***
		// ****************
		$text='';

		if ($personDb->pers_birth_date OR $personDb->pers_birth_place){
			$templ_person["born_dateplacetime"]=date_place($personDb->pers_birth_date,$personDb->pers_birth_place);
			if($templ_person["born_dateplacetime"]!='') $temp="born_dateplacetime";
			$text=$templ_person["born_dateplacetime"];
		}
		// *** Birth time ***
		if (isset($personDb->pers_birth_time) AND $personDb->pers_birth_time){
			$templ_person["born_dateplacetime"]=' '.__('at').' '.$personDb->pers_birth_time.' '.__('hour');
			$temp="born_dateplacetime";
			$text.=$templ_person["born_dateplacetime"];
		}
 		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_birth_text);
			if ($work_text){
				//$templ_person["born_text"]=", ".$work_text;
				$templ_person["born_text"]=" ".$work_text;
				$temp="born_text";
				$text.=$templ_person["born_text"];
			}
		}
 
		// *** Birth source ***
		$source=show_sources2("person","pers_birth_source",$personDb->pers_gedcomnumber);
		if ($source){
			if($screen_mode=='PDF') {
				$templ_person["born_source"]=$source;
				$temp="born_source";
			}
			elseif($screen_mode=='RTF') {
				$rtf_text=strip_tags($templ_person["born_source"],"<b><i>");
				$sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
			}
			else{
				$text.=$dirmark1.$source;
			}
		}

		// *** Birth declaration/ registration ***
		if ($personDb->pers_gedcomnumber){
			$temp_text=witness($personDb->pers_gedcomnumber, 'birth_declaration');
			if ($temp_text){
				//if($temp) { $templ_person[$temp].=" ("; }
				$templ_person["born_witn"]= ' ('.__('birth declaration').': '.$temp_text.')';
				$temp="born_witn";
				$text.= $templ_person["born_witn"];
			}
		}
		// *** Check for birth items, if needed use a new line ***
		if ($text){
			if (!$process_text OR $family_expanded==true) $text='<b>'.ucfirst(__('born')).'</b> '.$text;
				else $text=', <b>'.__('born').'</b> '.$text;
			if ($process_text AND $family_expanded==true){ $text='<br>'.$text; }
			$process_text.=$text;
		}

		// ***************
		// *** BAPTISE ***
		// ***************
		$text='';

		if ($personDb->pers_bapt_date OR $personDb->pers_bapt_place){
			$templ_person["bapt_dateplacetime"]=date_place($personDb->pers_bapt_date,$personDb->pers_bapt_place);
			if($templ_person["bapt_dateplacetime"]!='') $temp="bapt_dateplacetime";
			$text=$templ_person["bapt_dateplacetime"];
		}
		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_bapt_text);
			if ($work_text){
				//if($temp) { $templ_person[$temp].=", "; }
				//$templ_person["bapt_text"]=$work_text;
				$templ_person["bapt_text"]=' '.$work_text;
				$temp="bapt_text";
				//$text.=", ".$work_text;
				$text.=$templ_person["bapt_text"];
			}
		}

		if ($user['group_religion']=='j' AND $personDb->pers_religion){
			$templ_person["bapt_reli"]=" (".__('religion').': '.$personDb->pers_religion.')';
			$temp="bapt_reli";
			$text.= ' <span class="religion">('.__('religion').': '.$personDb->pers_religion.')</span>';
		}

		// *** Baptise source ***
		$source=show_sources2("person","pers_bapt_source",$personDb->pers_gedcomnumber);
		if ($source){
			if($screen_mode=='PDF') {
				$templ_person["bapt_source"]=$source;
				$temp="bapt_source";
			}
			else
				$text.=$source;
		}

		// *** Show baptise witnesses ***
		if ($personDb->pers_gedcomnumber){
			$temp_text=witness($personDb->pers_gedcomnumber, 'baptism_witness');
			if ($temp_text){
				if($temp) { $templ_person[$temp].=" ("; }
				$templ_person["bapt_witn"]=__('baptism witness').': '.$temp_text.')';
				$temp="bapt_witn";
				$text.= ' ('.__('baptism witness').': '.$temp_text.')';
			}
		}

		// *** check for baptise items, if needed use a new line ***
		if ($text){
			if (!$process_text OR $family_expanded==true) $text='<b>'.ucfirst(__('baptised')).'</b> '.$text;
				else $text=', <b>'.__('baptised').'</b> '.$text;
			if ($process_text AND $family_expanded==true){ $text='<br>'.$text; }
			$process_text.=$text;
		}

		// *** Show age of living person ***
		if (($personDb->pers_bapt_date OR $personDb->pers_birth_date) AND !$personDb->pers_death_date AND $personDb->pers_alive!='deceased'){
			$process_age = New calculate_year_cls;
			$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,'');
			$templ_person["age_liv"]=$age;
			if($templ_person["age_liv"]!='') $temp="age_liv";
			$process_text.=$dirmark1.$age;  // *** komma and space already in $age
		}

		// ******************
		// *** DEATH      ***
		// ******************
		$text='';

		if ($personDb->pers_death_date OR $personDb->pers_death_place){
			$templ_person["dead_dateplacetime"]=date_place($personDb->pers_death_date,$personDb->pers_death_place);
			if($templ_person["dead_dateplacetime"]!='') $temp="dead_dateplacetime";
			$text=$templ_person["dead_dateplacetime"];
		}
		// *** Death time ***
		if (isset($personDb->pers_death_time) AND $personDb->pers_death_time){
			$templ_person["dead_dateplacetime"]=' '.$personDb->pers_death_time;
			$temp="dead_dateplacetime";
			$text.=$templ_person["dead_dateplacetime"];
		}
		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_death_text);
			if ($work_text){
				//$text.=", ".$work_text;
				//if($temp) { $templ_person[$temp].=", "; }
				$templ_person["dead_text"]=' '.$work_text;
				$temp="dead_text";
				$text.=$templ_person["dead_text"];
			}
		}

		// *** Show age, by Yossi Beck ***
		if (($personDb->pers_bapt_date OR $personDb->pers_birth_date) AND $personDb->pers_death_date) {
			$process_age = New calculate_year_cls;
			$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$personDb->pers_death_date);
			$templ_person["dead_age"]=$age;
			if($templ_person["dead_age"]!='') $temp="dead_age";
			$text.=$dirmark1.$age;  // *** comma and space already in $age
		}

		$pers_death_cause='';
		If ($personDb->pers_death_cause=='murdered'){ $pers_death_cause=', '.__('death cause').': '.__('murdered'); }
		If ($personDb->pers_death_cause=='drowned'){ $pers_death_cause=', '.__('death cause').': '.__('drowned'); }
		If ($personDb->pers_death_cause=='perished'){ $pers_death_cause=', '.__('death cause').': '.__('perished'); }
		If ($personDb->pers_death_cause=='killed in action'){ $pers_death_cause=', '.__('killed in action'); }
		If ($personDb->pers_death_cause=='being missed'){ $pers_death_cause=', '.__('being missed'); }
		If ($personDb->pers_death_cause=='committed suicide'){ $pers_death_cause=', '.__('death cause').': '.__('committed suicide'); }
		If ($personDb->pers_death_cause=='executed'){ $pers_death_cause=', '.__('death cause').': '.__('executed'); }
		If ($personDb->pers_death_cause=='died young'){ $pers_death_cause=', '.__('died young'); }
		If ($personDb->pers_death_cause=='died unmarried'){ $pers_death_cause=', '.__('died unmarried'); }
		If ($personDb->pers_death_cause=='registration'){ $pers_death_cause=', '.__('registration'); } //2 TYPE registration?
		If ($personDb->pers_death_cause=='declared death'){ $pers_death_cause=', '.__('declared death'); }
		if ($pers_death_cause){
			$templ_person["dead_cause"]=$pers_death_cause;
			$temp="dead_cause";
			$text.=$pers_death_cause;
		}
		else{
			if ($personDb->pers_death_cause){
				if($temp) { $templ_person[$temp].=", "; }
				$templ_person["dead_cause"]=__('death cause').': '.$personDb->pers_death_cause;
				$temp="dead_cause";
				$text.=', '.__('death cause').': '.$personDb->pers_death_cause;
			}
		}

		// *** Death source ***
		$source=show_sources2("person","pers_death_source",$personDb->pers_gedcomnumber);
		if ($source){
			if($screen_mode=='PDF') {
				$templ_person["dead_source"]=$source;
				$temp="dead_source";
			}
			else
				$text.=$source;
		}

		// *** Death declaration ***
		if ($personDb->pers_gedcomnumber){
			$temp_text=witness($personDb->pers_gedcomnumber, 'death_declaration');
			if ($temp_text){
				if ($temp) { $templ_person[$temp].=" ("; }
				$templ_person["dead_witn"]= __('death declaration').' '.$temp_text.')';
				$temp="dead_witn";
				$text.= ' ('.__('death declaration').' '.$temp_text.')';
			}
		}

		// *** Check for death items, if needed use a new line ***
		if ($text){
			if (!$process_text OR $family_expanded==true) $text='<b>'.ucfirst(__('died')).'</b> '.$text;
				else $text=', <b>'.__('died').'</b> '.$text;
			if ($process_text AND $family_expanded==true){ $text='<br>'.$text; }
			$process_text.=$text;
		}

		// ****************
		// *** BURIED   ***
		// ****************
		$text='';

		if ($personDb->pers_buried_date OR $personDb->pers_buried_place){
			$templ_person["buri_dateplacetime"]=date_place($personDb->pers_buried_date,$personDb->pers_buried_place);
			if($templ_person["buri_dateplacetime"]!='') $temp="buri_dateplacetime";
			$text=$templ_person["buri_dateplacetime"];
		}
		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_buried_text);
			if ($work_text){
				//if($temp) { $templ_person[$temp].=", "; }
				$templ_person["buri_text"]=' '.$work_text;
				$temp="buri_text";
				//$text.=", ".$work_text;
				$text.=$templ_person["buri_text"];
			}
		}

		// *** Buried source ***
		$source=show_sources2("person","pers_buried_source",$personDb->pers_gedcomnumber);
		if ($source){
			if($screen_mode=='PDF') {
				$templ_person["buri_source"]=$source;
				$temp="buri_source";
			}
			else
				$text.=$source;
		}

		// *** Buried witness ***
		if ($personDb->pers_gedcomnumber){
			$temp_text=witness($personDb->pers_gedcomnumber, 'burial_witness');
			if ($temp_text){
				//$templ_person[$temp].=" (";
				$templ_person["buri_witn"]= ' ('.__('burial witness').' '.$temp_text.')';
				$temp="buri_witn";
				$text.= $templ_person["buri_witn"];
			}
		}

		// *** Check for burial items, if needed use a new line ***
		if ($text){
			if ($personDb->pers_cremation){
				$buried_cremation=__('cremation');
				$templ_person["flag_buri"]=1;
			}
			else {
				$buried_cremation=__('buried');
				$templ_person["flag_buri"]=0;
			}

			if (!$process_text OR $family_expanded==true) $text='<b>'.ucfirst($buried_cremation).'</b> '.$text;
				else $text=', <b>'.$buried_cremation.'</b> '.$text;
			if ($process_text AND $family_expanded==true){ $text='<br>'.$text; }
			$process_text.=$text;
		}

		// *** HZ-21 ash dispersion (asverstrooiing) ***
		$name_qry = $db_functions->get_events_person($personDb->pers_gedcomnumber,'ash dispersion');
		foreach ($name_qry as $nameDb){
			$process_text.=', '.__('ash dispersion').' ';
			if ($nameDb->event_date){ $process_text.=date_place($nameDb->event_date,'').' '; }
			$process_text.=$nameDb->event_event.' ';
//SOURCE and TEXT.
//CHECK PDF EXPORT
		}

		// **************************
		// *** Show professions   ***
		// **************************
		if ($personDb->pers_gedcomnumber){
			$eventnr=0;
			$event_qry=$dbh->query("SELECT * FROM humo_events
				WHERE event_tree_id='".$tree_id."' AND event_person_id='".$personDb->pers_gedcomnumber."' AND event_kind='profession'
				ORDER BY substring( event_date,-4 ), event_order");
			$nr_occupations=$event_qry->rowCount();
			while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
				$eventnr++;
				if ($eventnr=='1'){
					if ($nr_occupations=='1'){
						$occupation=__('occupation');
						$templ_person["flag_prof"]=0;
					}
					else{
						$occupation=__('occupations');
						$templ_person["flag_prof"]=1;
					}
					if ($family_expanded==true){
						$process_text.='<br><span class="profession"><b>'.ucfirst($occupation).':</b> ';
					}
					else{
						if ($process_text){ $process_text.='. <span class="profession">'; }
						if($temp) { $templ_person[$temp].=". "; }
						$process_text.='<b>'.ucfirst($occupation).':</b> ';
						//$templ_person["prof_exist"]=ucfirst($occupation).': ';
						//$temp="prof_exist";
					}
				}
				if ($eventnr>1){
					$process_text.=', ';
					if($temp) { $templ_person[$temp].=", "; }
				}
				if ($eventDb->event_date OR $eventDb->event_place){
					$templ_person["prof_date".$eventnr]=date_place($eventDb->event_date,$eventDb->event_place).'; ';
					$temp="prof_date".$eventnr;
					$process_text.=$templ_person["prof_date".$eventnr];
				}

				$process_text.=$eventDb->event_event;
				$templ_person["prof_prof".$eventnr]=$eventDb->event_event;
				$temp="prof_prof".$eventnr;

				if ($eventDb->event_text) {
					$work_text=process_text($eventDb->event_text);
					if ($work_text){
						//if($temp) { $templ_person[$temp].=", "; }
						if($temp) { $templ_person[$temp].=" "; }
						$templ_person["prof_text".$eventnr]=$work_text;
						$temp="prof_text".$eventnr;
						//$process_text.=", ".$work_text;
						$process_text.=" ".$work_text;
					}
				}

				// *** Profession source ***
				$source=show_sources2("person","pers_event_source",$eventDb->event_id);
				if ($source){
					if($screen_mode=='PDF') {
						$templ_person["prof_source"]=$source;
						$temp="prof_source";
					}
					else
						$process_text.=$source;
				}

			}
			if ($eventnr>0){ $process_text.='</span>'; }
		}

		// ***********************
		// *** Show residences ***
		// ***********************
		if ($personDb->pers_gedcomnumber AND $user['group_living_place']=='j'){
			$text='';
			$eventnr=0;
			$event_qry = $db_functions->get_addresses_person($personDb->pers_gedcomnumber);
			$nr_addresses=count($event_qry);
			foreach($event_qry as $eventDb){
				$eventnr++;
				if ($eventnr=='1'){
					if ($process_text){
						if ($family_expanded==true){ $text.='<br>'; } else{ $text.='. '; }
					}
					if ($nr_addresses=='1'){
						$residence=__('residence');
						$templ_person["flag_address"]=0;
					}
					else{
						$residence=__('residences');
						$templ_person["flag_address"]=1;
					}
					if($temp) {$templ_person[$temp].=". "; }
					//$templ_person["address_exist"]=ucfirst($residence).': ';
					//$temp="address_exist";
					$text.='<b>'.ucfirst($residence).':</b> ';
				}
				if ($eventnr>1){
					$text.=', ';
					if($temp) { $templ_person[$temp].=", "; }
				}
				if ($eventDb->address_date){
					$text.=date_place($eventDb->address_date,'').' ';
					// default, without place, place is processed later.
					$templ_person["address_date".$eventnr]=date_place($eventDb->address_date,'').' ';
					$temp="address_date".$eventnr;
				}

				if ($user['group_addresses']=='j' AND $eventDb->address_address){
					$text.=' '.$eventDb->address_address.' ';
					// PDF Export?
				}

				if ($eventDb->address_zip){
					$text.=' '.$eventDb->address_zip.' ';
					// PDF Export?
				}

				$text.=$eventDb->address_place;
				$templ_person["address_address".$eventnr]=$eventDb->address_place;
				if($templ_person["address_address".$eventnr]!='') $temp="address_address".$eventnr;

				if ($eventDb->address_phone){
					$text.=', '.$eventDb->address_phone;
				}

				// *** Address text ***
				if ($eventDb->address_text) {
					$work_text=process_text($eventDb->address_text);
					if ($work_text){
						if($temp) { $templ_person[$temp].=", "; }
						$templ_person["address_text".$eventnr]=$work_text;
						$temp="address_text".$eventnr;
						$text.=", ".$work_text;
					}
				}

				$source=show_sources2("person","pers_address_source",$eventDb->address_id);
				if ($source){
					if($screen_mode=='PDF') {
						$templ_person["address_source"]=$source;
						$temp="address_source";
					}
					else{
						$text.=$source;
					}
				}

			}
			if ($text){
				$process_text.='<span class="pers_living_place">'.$text.'</span>';
			}
		}

		// *** Person source ***
		$source=show_sources2("person","person_source",$personDb->pers_gedcomnumber);
		if ($source){
			if($screen_mode=='PDF') {
				$templ_person["pers_source"]=$source;
				$temp="pers_source";
			}
			else{
				$process_text.=$source;
			}
		}

		// *** Extended addresses for HuMo-gen and dutch Haza-data program (Haza-data plus version) ***
		if ($user['group_addresses']=='j'){
			// *** Search for all connected addresses ***
			$eventnr=0;
			$connect_sql = $db_functions->get_connections_person('person_address',$personDb->pers_gedcomnumber);
			foreach ($connect_sql as $connectDb){
				$eventnr++;

				if($temp) { $templ_person[$temp].=". "; }
				$templ_person["HDadres_exist".$eventnr]=__('Address').': ';
				$temp="HDadres_exist".$eventnr;

				$process_text.='. <b>'.__('Address').':</b> ';
				$process_text.='<a href="'.$uri_path.'address.php?gedcomnumber='.$connectDb->connect_item_id.'">';
					$eventDb2 = $db_functions->get_address($connectDb->connect_item_id);
					if (isset($eventDb2->address_address) AND $eventDb2->address_address){
						$templ_person["HDadres_adres".$eventnr]=" ".trim($eventDb2->address_address);
						$temp="HDadres_adres".$eventnr;
						$process_text.=$templ_person["HDadres_adres".$eventnr];
					}
					if (isset($eventDb2->address_address) AND $eventDb2->address_place){
						$templ_person["HDadres_place".$eventnr]=" ".trim($eventDb2->address_place);
						$temp="HDadres_place".$eventnr;
						$process_text.=$templ_person["HDadres_place".$eventnr];
					}
				$process_text.="</a>";
			}
		}


	} //*** END PRIVACY PART ***

	// *** Use a link for multiple marriages by parent2 ***
	if ($person_kind=='parent2'){
		$marriage_array=explode(";",$personDb->pers_fams);
		if (isset($marriage_array[1])){
			for ($i=0; $i<=substr_count($personDb->pers_fams, ";"); $i++){
				$marriagenr=$i+1;
				$parent2_famDb = $db_functions->get_family($marriage_array[$i]);
				// *** Use a class for marriage ***
				$parent2_marr_cls = New marriage_cls;

				// *** Show standard marriage text ***
				if($screen_mode!="PDF") {
					$parent2_marr_data=$parent2_marr_cls->marriage_data($parent2_famDb,$marriagenr,'short');
				}
				else {
					$pdf_marriage=$parent2_marr_cls->marriage_data($parent2_famDb,$marriagenr,'short');
				}

				if ($change_main_person==true){
					$parent2Db = $db_functions->get_person($parent2_famDb->fam_woman);
				}
				else{
					$parent2Db = $db_functions->get_person($parent2_famDb->fam_man);
				}

				if ($id==$marriage_array[$i]){
					if ($process_text) $process_text.=',';
					if(isset($parent2_marr_data)) {$process_text.=' '.$dirmark1.$parent2_marr_data.' ';}

					// *** $parent2Db is empty if it is a N.N. person ***
					if ($parent2Db){
						$name=$this->person_name($parent2Db);
						$process_text.=$name["standard_name"];
					}
					else{
						$process_text.=__('N.N.');
					}
				}
				else{
					$process_text.=', ';
					// *** url_rewrite ***
					if ($humo_option["url_rewrite"]=="j"){
						// *** $uri_path is made header.php ***
						$process_text.='<a href="'.$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.$marriage_array[$i].'/'.$personDb->pers_gedcomnumber.'/">';
					}
					else{
						$process_text.='<a href="'.CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$marriage_array[$i].'&amp;main_person='.$personDb->pers_gedcomnumber.'">';
					}
					if(isset($parent2_marr_data)) {$process_text.=$dirmark1.$parent2_marr_data.' ';}
					// *** $parent2Db is empty by N.N. person ***
					if ($parent2Db){
						$name=$this->person_name($parent2Db);
						$process_text.=$name["standard_name"];
					}
					else{
						$process_text.=__('N.N.');
					}
					$process_text.='</a>';
				}
				if($screen_mode=="PDF") {
					if ($parent2Db){
						if($temp) { $templ_person[$temp].=", "; }
						$name=$this->person_name($parent2Db);
						$templ_person["marr_more".$marriagenr]=$pdf_marriage["relnr_rel"].$pdf_marriage["rel_add"]." ".$name["standard_name"];
						$temp="marr_more".$marriagenr;
					}
					else{
						if($temp) { $templ_person[$temp].=", "; }
						$templ_person["marr_more".$marriagenr]=$pdf_marriage["relnr_rel"]." ".__('N.N.');
						$temp="marr_more".$marriagenr;
					}
				}
			}
		}
	}

	//if ($privacy){
	if($screen_mode=="mobile" OR $privacy){
		//
	}
	else{

		// *** Show media/ pictures ***
		$result = show_media($personDb,''); // *** This function can be found in file: show_picture.php! ***
		$process_text.= $result[0];
		if (isset($templ_person))
			$templ_person = array_merge((array)$templ_person,(array)$result[1]);
		else
			$templ_person=$result[1];

		// *** Internet links (URL) ***
		$url_qry = $db_functions->get_events_person($personDb->pers_gedcomnumber,'URL');
		if (count($url_qry)>0){ $process_text.='<br>'; }
		foreach($url_qry as $urlDb){
			if ($urlDb->event_text){ $process_text.=$urlDb->event_text.': '; }
			$process_text.='<a href="'.$urlDb->event_event.'" target="_blank">'.$urlDb->event_event.'</a>';
			$process_text.='<br>';
		}

		//******** Text by person **************
		if ($user["group_text_pers"]=='j'){
			$work_text=process_text($personDb->pers_text, 'person');

			// *** BK: Source by person text ***
			$source=show_sources2("person","pers_text_source",$personDb->pers_gedcomnumber);
			if ($source){
//PDF
				if($screen_mode=='PDF') {
					//
				}
				else
					$work_text.=$source;
			}

			if ($work_text){
				$process_text.='<br>'.$work_text."\n";
				$templ_person["pers_text"]="\n".$work_text;
				$temp="pers_text";
			}
		}

		// *** Show events ***
		if ($user['group_event']=='j'){
			if ($personDb->pers_gedcomnumber){
				$event_qry=$db_functions->get_events_person($personDb->pers_gedcomnumber,'event');
				$num_rows = count($event_qry);
				if ($num_rows>0){ $process_text.='<span class="event">'."\n"; }
				$eventnr=0;
				foreach ($event_qry as $eventDb){
					$eventnr++;
					$process_text.="<br>\n";
					$templ_person["event_start".$eventnr]="\n";

					// *** Check if NCHI is 0 or higher ***
					$event_gedcom=$eventDb->event_gedcom;
					//$event_text=$eventDb->event_text;
					if ($event_gedcom=='NCHI' AND trim($eventDb->event_event)==''){
						$event_gedcom='NCHI0';
						//$event_text='';
					}

					$process_text.='<b>'.language_event($event_gedcom).'</b>';
					$templ_person["event_ged".$eventnr]=language_event($event_gedcom);
					$temp="event_ged".$eventnr;

					if ($eventDb->event_event){
						$templ_person["event_event".$eventnr]=' '.$eventDb->event_event;
						$temp="event_event".$eventnr;
						$process_text.=$templ_person["event_event".$eventnr];
					}
					$templ_person["event_dateplace".$eventnr]=' '.date_place($eventDb->event_date, $eventDb->event_place);
					$temp="event_dateplace".$eventnr;
					$process_text.=$templ_person["event_dateplace".$eventnr];

					if ($eventDb->event_text){
						$work_text=process_text($eventDb->event_text);
						if ($work_text){
							//$process_text.=", ".$work_text;
							$process_text.=" ".$work_text;
							//if($temp) { $templ_person[$temp].=", "; }
							if($temp) { $templ_person[$temp].=" "; }
							$templ_person["event_text".$eventnr]=$work_text;
							$temp="event_text".$eventnr;
						}
					}

					$source=show_sources2("person","pers_event_source",$eventDb->event_id);
					if ($source){
						if($screen_mode=='PDF') {
							$templ_person["pers_event_source"]=$source;
							$temp="pers_event_source";
						}
						else{
							$process_text.=$source;
						}
					}

				}
				if ($num_rows>0){ $process_text.="</span>\n"; }
				unset ($event_qry);
			}
		}

	} // End of privacy

	// *** Return person data ***
	if($screen_mode=="mobile") {
		if ($process_text){ return $process_text; }
	}
	elseif($screen_mode!="PDF") {
		if ($process_text){
			$div='<div class="margin_person">';
			if ($person_kind=='child'){ $div='<div class="margin_child">'; }
			return $privacy_filter.$div.$process_text.'</div>';
		}
		else{
			return $privacy_filter;
		}
	}
	else {   // return array with pdf values
		if(isset($templ_person)) {return $templ_person;}
	}

	} // End of check $personDb

} // End of function person_data.
} // End of person_cls
?>