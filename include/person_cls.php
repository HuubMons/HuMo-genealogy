<?php
// *****************************************************
// *** Process person data                           ***
// *** Class for HuMo-gen program                    ***
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
//echo $user['group_privacy'];
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
	global $user, $language, $dbh, $screen_mode;
	$tree_prefix_quoted=''; if ($personDb) $tree_prefix_quoted=$personDb->pers_tree_prefix;
	$stillborn=''; $nobility=''; $lordship='';
	$title_before=''; $title_between=''; $title_after='';

	if (isset($personDb->pers_gedcomnumber) AND $personDb->pers_gedcomnumber){
		// *** Aldfaer: nobility (predikaat) by name ***
		$name_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			WHERE event_person_id='".$personDb->pers_gedcomnumber."' AND event_kind='nobility' ORDER BY event_order");
		while($nameDb=$name_qry->fetch(PDO::FETCH_OBJ)){
			$nobility.=$nameDb->event_event.' ';
		}

		// *** Gedcom 5.5 title: NPFX ***
		$name_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='NPFX' ORDER BY event_order");
		while($nameDb=$name_qry->fetch(PDO::FETCH_OBJ)){
			$title_before.=' '.$nameDb->event_event.' ';
		}

		// *** Gedcom 5.5 title: NSFX ***
		$name_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='NSFX' ORDER BY event_order");
		while($nameDb=$name_qry->fetch(PDO::FETCH_OBJ)){
			$title_after.=', '.$nameDb->event_event;
		}

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
		$name_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='title' ORDER BY event_order");
		while($nameDb=$name_qry->fetch(PDO::FETCH_OBJ)){
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

			if ($title_position=='before'){
				$title_before.=' '.$nameDb->event_event.' ';
			}
			if ($title_position=='between'){
				$title_between.=' '.$nameDb->event_event.' ';
			}
			if ($title_position=='after'){
				$title_after.=', '.$nameDb->event_event;
			}
		}

		// ***Still born child ***
		if (isset($personDb->pers_stillborn) AND $personDb->pers_stillborn=="y"){
			if ($personDb->pers_sexe=='M'){$stillborn.=' '.__('stillborn boy');}
			elseif ($personDb->pers_sexe=='F'){$stillborn.=' '.__('stillborn girl');}
			else $stillborn.=' '.__('stillborn child');
		}

		// *** Aldfaer: lordship (heerlijkheid) after name ***
		$name_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='lordship'
			ORDER BY event_order");
		while($nameDb=$name_qry->fetch(PDO::FETCH_OBJ)){
			$lordship.=', '.$nameDb->event_event;
		}

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
			if (isset($personDb->event_event) AND $personDb->event_event){
				$name_array["index_name_extended"].=' ('.$personDb->event_event.')';}
			$name_array["index_name_extended"].=$prefix2;

			// *** $name["initials"] used in report_descendant.php ***
			// *** Example: H.M. ***
			$name_array["initials"]=substr($personDb->pers_firstname,0,1).'.'.substr($personDb->pers_lastname,0,1).'.';
		}


		//  *** Colour mark by person ***
		$name_array["colour_mark"]=''; $person_colour_mark='';
		$colour_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			WHERE event_person_id='".$personDb->pers_gedcomnumber."' AND event_kind='person_colour_mark'
			ORDER BY event_order");
		while($colourDb=$colour_qry->fetch(PDO::FETCH_OBJ)){
			if ($colourDb AND $screen_mode!="PDF"){
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

	}
	else{
		$name_array["show_name"]=true;
		$name_array["firstname"]='N.N.';
		$name_array["name"]='';
		$name_array["short_firstname"]='N.N.';
		$name_array["standard_name"]='N.N.';
		$name_array["index_name"]='N.N.';
		$name_array["index_name_extended"]='N.N.';
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
	global $bot_visit, $dbh, $humo_option, $uri_path, $user, $language;
	global $screen_mode, $dirmark1, $dirmark2, $rtlmarker;
	global $selected_language;
	global $hourglass;
	$text='';
	$privacy=$this->privacy;

	// *** Show pop-up menu ***
	if (!$bot_visit AND $screen_mode!="PDF") {

		// *** Family tree for search in multiple family trees ***
		$tree_prefix=safe_text($personDb->pers_tree_prefix);

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
						$check_children_sql=$dbh->query("SELECT * FROM ".$tree_prefix."family
							WHERE fam_gedcomnumber='".$check_family[$i]."'");
						@$check_childrenDb=$check_children_sql->fetch(PDO::FETCH_OBJ);
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
				if  ($user['group_editor']=='j' OR $user['group_admin']=='j') {
					$text.= '<b>'.__('Admin').':</b>';
					$path_tmp=CMS_ROOTPATH.'admin/index.php?page=editor&amp;tree='.$tree_prefix.'&amp;person='.$personDb->pers_gedcomnumber;
					$text.= '<a href="'.$path_tmp.'" target="_blank">';
					$text.= '<img src="'.CMS_ROOTPATH.'images/person_edit.gif" border="0" alt="'.__('Timeline').'"> '.__('Editor').'</a>';
				}

				// *** Show person picture and person data at right side of the pop-up box ***
				if ($extended){
					$text.='</td><td style="width:auto; border: solid 0px; font-size: 10px;" valign="top">';

					// *** Show picture in pop-up box ***
					if (!$privacy AND $user['group_pictures']=='j'){
						//  *** Path can be changed per family tree ***
						global $dataDb;
						$tree_pict_path=$dataDb->tree_pict_path;
						$picture_qry=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."events
							WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='picture'");
						$pictureDb=$picture_qry->fetch(PDO::FETCH_OBJ);
						if (isset($pictureDb->event_event)){
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
	global $dbh, $humo_option, $uri_path, $user, $language;
	global $screen_mode, $dirmark1, $dirmark2, $rtlmarker;
	global $selected_language;
	global $family_expanded;
	global $bot_visit;

	$text_name=''; $text_parents=''; $child_marriage='';

	$personDb=$this->personDb;
	$privacy=$this->privacy;
	if (!$personDb){
		// *** Show unknown person N.N. ***
		$text_name= __('N.N.');
	}
	else{
		$tree_prefix_quoted=$personDb->pers_tree_prefix;

		// *** Show pop-up menu ***
		$text_name.= $this->person_popup_menu($personDb);

		// *** Check privacy filter ***
		if ($privacy AND $user['group_filter_name']=='n'){
			//dummy
		}
		else{
			// *** Show man or woman picture ***
			if($screen_mode!="PDF") {  //  pdf does this elsewhere
				$text_name.= $dirmark1;
				if ($personDb->pers_sexe=="M")
					$text_name.='<img src="'.CMS_ROOTPATH.'images/man.gif" alt="man">';
				elseif ($personDb->pers_sexe=="F")
					$text_name.='<img src="'.CMS_ROOTPATH.'images/woman.gif" alt="woman">';
				else
					$text_name.='<img src="'.CMS_ROOTPATH.'images/unknown.gif" alt="unknown">';

				// *** Source by sexe ***
				if ($personDb->pers_sexe_source){
					//if($screen_mode=='PDF') {
					//	$pdfstr["buri_source"]=show_sources2("person","pers_sexe_source",$personDb->pers_gedcomnumber);
					//	$temp="buri_source";
					//}
					//else{
						$text_name.=show_sources2("person","pers_sexe_source",$personDb->pers_gedcomnumber).' ';
					//}
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
		//if ($person_kind=='child' AND $personDb->pers_fams){
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

		// *** Add colour marks to person ***
		$text_name.=$name["colour_mark"];


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
				$text_parents=', '.$text_parents;
				$pdfstr["parent_childof"]=', '.$text_parents;
				$temp="parent_childof";
			}

			// *** Just in case: empty $text ***
			$text='';

			// *** Find parents ID ***
			$parents_family=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."family WHERE fam_gedcomnumber='$personDb->pers_famc'");
			$parents_familyDb=$parents_family->fetch(PDO::FETCH_OBJ);

			// *** Father ***
			if ($parents_familyDb->fam_man){
				$father_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber='$parents_familyDb->fam_man'");
				$fatherDb=$father_qry->fetch(PDO::FETCH_OBJ);
				$name=$this->person_name($fatherDb);
				$text=$name["standard_name"];
				$pdfstr["parents"]=$name["standard_name"];
				$temp="parents";
			}
			else{
				$text=__('N.N.');
				$pdfstr["parents"]=__('N.N.');
				$temp="parents";
			}

			$text.=' '.__('and').' ';
			$pdfstr["parents"].=' '.__('and').' ';
			$temp="parents";

			// *** Mother ***
			if ($parents_familyDb->fam_woman){
				$mother_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber='$parents_familyDb->fam_woman'");
				$motherDb=$mother_qry->fetch(PDO::FETCH_OBJ);
				$name=$this->person_name($motherDb);
				$text.=$name["standard_name"];
				$pdfstr["parents"].=$name["standard_name"];
				$temp="parents";
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
			$famc_adoptive_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
				WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='adoption'
				ORDER BY event_order");
			while($famc_adoptiveDb=$famc_adoptive_qry->fetch(PDO::FETCH_OBJ)){
				$text_parents.=' '.ucfirst(__('adoption parents')).': ';

				// *** Just in case: empty $text ***
				$text='';
				// *** Find parents ID ***
				$parents_family=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."family WHERE fam_gedcomnumber='$famc_adoptiveDb->event_event'");
				$parents_familyDb=$parents_family->fetch(PDO::FETCH_OBJ);
				//*** Father ***
				if ($parents_familyDb->fam_man){
					$father_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber='$parents_familyDb->fam_man'");
					$fatherDb=$father_qry->fetch(PDO::FETCH_OBJ);
					$name=$this->person_name($fatherDb);
					$text=$name["standard_name"];
					//$pdfstr["parents"]=$name["standard_name"];
					//$temp="parents";
				}
				else{
					$text=__('N.N.');
					//$pdfstr["parents"]=__('N.N.');
					//$temp="parents";
				}

				$text.=' '.__('and').' ';
				//$pdfstr["parents"].=' '.__('and').' ';
				//$temp="parents";

				//*** Mother ***
				if ($parents_familyDb->fam_woman){
					$mother_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber='$parents_familyDb->fam_woman'");
					$motherDb=$mother_qry->fetch(PDO::FETCH_OBJ);
					$name=$this->person_name($motherDb);
					$text.=$name["standard_name"];
					//$pdfstr["parents"].=$name["standard_name"];
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
		// *** Check for adoptive parent ESPECIALLY FOR ALDFAR  ***
		// ********************************************************
		if ($person_kind=='parent1' OR $person_kind=='parent2'){
			$famc_adoptive_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
				WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='adoption_by_person'
				ORDER BY event_order");
			while($famc_adoptiveDb=$famc_adoptive_qry->fetch(PDO::FETCH_OBJ)){
				if($famc_adoptiveDb->event_gedcom=='steph') $text_parents.=' '.ucfirst(__('stepparent')).': ';
				elseif($famc_adoptiveDb->event_gedcom=='legal') $text_parents.=' '.ucfirst(__('legal parent')).': ';
				elseif($famc_adoptiveDb->event_gedcom=='foster') $text_parents.=' '.ucfirst(__('foster parent')).': ';
				else $text_parents.=' '.ucfirst(__('adoption parent')).': ';

				//*** Father ***
				//if ($parents_familyDb->fam_man){
					$father_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber='$famc_adoptiveDb->event_event'");
					$fatherDb=$father_qry->fetch(PDO::FETCH_OBJ);
					$name=$this->person_name($fatherDb);
					$text=$name["standard_name"];
					//$pdfstr["parents"]=$name["standard_name"];
					//$temp="parents";
				//}
				//else{
				//	$text=__('N.N.');
					//$pdfstr["parents"]=__('N.N.');
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
				$qry="SELECT * FROM ".$tree_prefix_quoted."family WHERE fam_gedcomnumber='".$marriage_array[$x]."'";
				$fam_partner=$dbh->query($qry);
				$fam_partnerDb=$fam_partner->fetch(PDO::FETCH_OBJ);

				// *** This check is better then a check like: $personDb->pers_sexe=='F', because of unknown sexe or homosexual relations. ***
				if ($personDb->pers_gedcomnumber==$fam_partnerDb->fam_man)
					$partner_id=$fam_partnerDb->fam_woman;
				else
					$partner_id=$fam_partnerDb->fam_man;

				$relation_short=__('&');
				if ($fam_partnerDb->fam_marr_date OR $fam_partnerDb->fam_marr_place OR $fam_partnerDb->fam_marr_church_date OR $fam_partnerDb->fam_marr_church_place)
					$relation_short=__('X');
				if($fam_partnerDb->fam_div_date OR $fam_partnerDb->fam_div_place)
					$relation_short=__(') (');

				if ($partner_id!='0' AND $partner_id!=''){
					$qry="SELECT * FROM ".$tree_prefix_quoted."person WHERE pers_gedcomnumber='".$partner_id."'";
					$partner=$dbh->query($qry);
					$partnerDb=$partner->fetch(PDO::FETCH_OBJ);
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

				$child_marriage.= ' &nbsp;';
				if ($nr_marriages>1){
					if ($x==0) $child_marriage.= ' '.__('1st');
					elseif ($x==1) $child_marriage.= ' '.__('2nd');
					elseif ($x==2) $child_marriage.= ' '.__('3rd');
					elseif ($x>2) $child_marriage.= ' '.($x+1).__('th');
				}
				//$child_marriage.= ' <span class="index_partner">'.$relation_short.' '.$dirmark1.$name["standard_name"].$dirmark1.'</span>';
				$child_marriage.= ' '.$relation_short.' '.$dirmark1.$name["standard_name"].$dirmark1;
			}
		}
		// *** End spouse/ partner ***

	}

	if ($family_expanded==true){
		$text_parents='<div class="margin_person">'.$text_parents.'</div>';
		$child_marriage='<div class="margin_child">'.$child_marriage.'</div>';
	}

	//return '<span class="pers_name">'.$text_name.$dirmark1.'</span>'.$text_parents;
	return '<span class="pers_name">'.$text_name.$dirmark1.'</span>'.$text_parents.$child_marriage;
}


// ***************************************************************************************
// *** Show person                                                                     ***
// *** $personDb = data person                                                         ***
// *** $person_kind = parent2 (normally the woman, generates links to all marriages)   ***
// *** $id = family id for link by woman for multiple marrriages                       ***
// *** $privacy = privacyfilter                                                        ***
// ***************************************************************************************
function person_data($person_kind, $id){
	global $dbh, $dataDb, $user, $language, $humo_option, $family_id, $uri_path;
	global $family_expanded, $change_main_person;
	global $childnr, $screen_mode, $dirmark1, $dirmark2;
	global $pdfstr;
	unset($pdfstr);

	$personDb=$this->personDb;
	$privacy=$this->privacy;

	// *** Settings for mobile version ***
	if ($person_kind=="mobile"){
		$family_expanded=true; // *** Show details in multiple lines ***
	}

	// *** $personDb is empty by N.N. person ***
	if ($personDb){
	$tree_prefix_quoted=$personDb->pers_tree_prefix;

	$process_text='';  $temp='';

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
		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_name_text);
			if ($work_text){
				$process_text.=", ".$work_text;
				$pdfstr["name_text"]=", ".$work_text;
			}
		}

		if ($personDb->pers_name_source){
			if($screen_mode=='PDF') {
				$pdfstr["name_source"]=show_sources2("person","pers_name_source",$personDb->pers_gedcomnumber);
				if($pdfstr["name_source"]!='') $temp="name_source";
			}
			else{
				$process_text.=show_sources2("person","pers_name_source",$personDb->pers_gedcomnumber);
			}
		}

		// *** Show extra names of BK ***
		if ($personDb->pers_gedcomnumber){
			$name_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
				WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='name'
				ORDER BY event_order");
			//while($nameDb=mysql_fetch_object($name_qry)){
			while($nameDb=$name_qry->fetch(PDO::FETCH_OBJ)){
				$process_text.=', ';
				$pdfstr["bk_names"]=", ";
				if ($nameDb->event_gedcom=='_AKAN'){ $process_text.=__('Also known as').': '; $pdfstr["bk_names"].=__('Also known as').': '; }
				// *** MyHeritage Family Tree Builder ***
				if ($nameDb->event_gedcom=='_AKA'){ $process_text.=__('Also known as').': '; $pdfstr["bk_names"].=__('Also known as').': '; }
				if ($nameDb->event_gedcom=='NICK'){ $process_text.=__('Nickname').': '; $pdfstr["bk_names"].=__('Nickname').': ';    }
				if ($nameDb->event_gedcom=='_ALIA'){ $process_text.=__('alias name').': '; $pdfstr["bk_names"].=__('alias name').': '; }   // Voor Pro-Gen
				if ($nameDb->event_gedcom=='_SHON'){ $process_text.=__('Short name (for reports)').': '; $pdfstr["bk_names"].=__('Short name (for reports)').': '; }
				if ($nameDb->event_gedcom=='_ADPN'){ $process_text.=__('Adopted name').': '; $pdfstr["bk_names"].=__('Adopted name').': '; }
				if ($nameDb->event_gedcom=='_HEBN'){ $process_text.=__('Hebrew name').': '; $pdfstr["bk_names"].=__('Hebrew name').': '; }
				if ($nameDb->event_gedcom=='_CENN'){ $process_text.=__('Census name').': '; $pdfstr["bk_names"].=__('Census name').': '; }
				if ($nameDb->event_gedcom=='_MARN'){ $process_text.=__('Married name').': '; $pdfstr["bk_names"].=__('Married name').': '; }
				if ($nameDb->event_gedcom=='_GERN'){ $process_text.=__('Nickname').': '; $pdfstr["bk_names"].=__('Nickname').': '; }
				if ($nameDb->event_gedcom=='_FARN'){ $process_text.=__('Farm name').': '; $pdfstr["bk_names"].=__('Farm name').': '; }
				if ($nameDb->event_gedcom=='_BIRN'){ $process_text.=__('Birth name').': '; $pdfstr["bk_names"].=__('Birth name').': '; }
				if ($nameDb->event_gedcom=='_INDN'){ $process_text.=__('Indian name').': '; $pdfstr["bk_names"].=__('Indian name').': '; }
				if ($nameDb->event_gedcom=='_FKAN'){ $process_text.=__('Formal name').': '; $pdfstr["bk_names"].=__('Formal name').': '; }
				if ($nameDb->event_gedcom=='_CURN'){ $process_text.=__('Current name').': '; $pdfstr["bk_names"].=__('Current name').': '; }
				if ($nameDb->event_gedcom=='_SLDN'){ $process_text.=__('Soldier name').': '; $pdfstr["bk_names"].=__('Soldier name').': '; }
				if ($nameDb->event_gedcom=='_FRKA'){ $process_text.=__('Formerly known as').': '; $pdfstr["bk_names"].=__('Formerly known as').': '; }
				if ($nameDb->event_gedcom=='_RELN'){ $process_text.=__('Religious name').': '; $pdfstr["bk_names"].=__('Religious name').': '; }
				if ($nameDb->event_gedcom=='_OTHN'){ $process_text.=__('Other name').': '; $pdfstr["bk_names"].=__('Other name').': '; }
				if($pdfstr["bk_names"]!='') $temp="bk_names";
				if ($nameDb->event_date){
					$process_text.=date_place($nameDb->event_date,'').' ';
					$pdfstr["bk_date"]=date_place($nameDb->event_date,'').' ';
					if($pdfstr["bk_date"]!='') $temp="bk_date";
				}
				$process_text.=$nameDb->event_event;
				$pdfstr["bk_event"]=$nameDb->event_event;
				if($pdfstr["bk_event"]!='') $temp="bk_event";

				if ($nameDb->event_source){
					if($screen_mode=='PDF') {
						$pdfstr["bk_source"]=show_sources2("person","event_source",$nameDb->event_id);
						$temp="bk_source";
					}
					else{
						$process_text.=show_sources2("person","event_source",$nameDb->event_id);
					}
				}

				if ($nameDb->event_text) {
					$process_text.=' '.$nameDb->event_text;
					$pdfstr["bk_text"]=' '.$nameDb->event_text;
					$temp="bk_text";
				}
			}
		}

		// *** Own code ***
		if ($user['group_own_code']=='j' AND $personDb->pers_own_code){
			$process_text.=', <span class="pers_own_code">('.$personDb->pers_own_code.')</span>';
			if($temp) { $pdfstr[$temp].=", "; }
			$pdfstr["own_code"]='('.$personDb->pers_own_code.')';
			$temp="own_code";
		}

		// ****************
		// *** BIRTH    ***
		// ****************
		$text='';

		if ($personDb->pers_birth_date OR $personDb->pers_birth_place){
			$text=date_place($personDb->pers_birth_date,$personDb->pers_birth_place);
			$pdfstr["born_dateplacetime"]=$text;
			if($pdfstr["born_dateplacetime"]!='') $temp="born_dateplacetime";
		}
		// *** Birth time ***
		if (isset($personDb->pers_birth_time) AND $personDb->pers_birth_time){
			$text.=' '.__('at').' '.$personDb->pers_birth_time.' '.__('hour');
			$pdfstr["born_dateplacetime"]=$text;
			$temp="born_dateplacetime";
		}
 		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_birth_text);
			if ($work_text){
				$text.=", ".$work_text;
				$pdfstr["born_text"]=", ".$work_text;
				$temp="born_text";
			}
		}
 
		// *** Birth source ***
		if ($personDb->pers_birth_source){
			if($screen_mode=='PDF') {
				$pdfstr["born_source"]=show_sources2("person","pers_birth_source",$personDb->pers_gedcomnumber);
				if($pdfstr["born_source"]!='') $temp="born_source";
			}
			else{
				$text.=$dirmark1.show_sources2("person","pers_birth_source",$personDb->pers_gedcomnumber);
			}
		}

		// *** Birth declaration/ registration ***
		if ($personDb->pers_gedcomnumber){
			$temp_text=witness($personDb->pers_gedcomnumber, 'birth_declaration');
			if ($temp_text){
				$text.= ', '.__('birth_declaration').' '.$temp_text;
				if($temp) { $pdfstr[$temp].=", "; }
				$pdfstr["born_witn"]= ' '.__('birth_declaration').' '.$temp_text;
				$temp="born_witn";
			}
		}
		// *** Check for birth items, if needed use a new line ***
		if ($text){
			if ($family_expanded==true){ $process_text.='<br>'.ucfirst(__('BORN_SHORT')).' '.$text; }
				else{ $process_text.=', '.__('BORN_SHORT').' '.$text; }
		}

		// ***************
		// *** BAPTISE ***
		// ***************
		$text='';

		if ($personDb->pers_bapt_date OR $personDb->pers_bapt_place){
			$text=date_place($personDb->pers_bapt_date,$personDb->pers_bapt_place);
			$pdfstr["bapt_dateplacetime"]=$text;
			if($pdfstr["bapt_dateplacetime"]!='') $temp="bapt_dateplacetime";
		}
		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_bapt_text);
			if ($work_text){
				$text.=", ".$work_text;
				if($temp) { $pdfstr[$temp].=", "; }
				$pdfstr["bapt_text"]=$work_text;
				$temp="bapt_text";
			}
		}

		if ($user['group_religion']=='j' AND $personDb->pers_religion){
			$text.= ' <span class="religion">'.__('religion').': '.$personDb->pers_religion.'</span>';
			$pdfstr["bapt_reli"]=" ".__('religion').': '.$personDb->pers_religion;
			$temp="bapt_reli";
		}

		// *** Baptise source ***
		if ($personDb->pers_bapt_source){
			if($screen_mode=='PDF') {
				$pdfstr["bapt_source"]=show_sources2("person","pers_bapt_source",$personDb->pers_gedcomnumber);
				if($pdfstr["bapt_source"]!='') $temp="bapt_source";
			}
			else{
				$text.=show_sources2("person","pers_bapt_source",$personDb->pers_gedcomnumber);
			}
		}

		// *** Show baptise witnesses ***
		if ($personDb->pers_gedcomnumber){
			$temp_text=witness($personDb->pers_gedcomnumber, 'baptism_witness');
			if ($temp_text){
				$text.= ', '.__('baptise witness').' '.$temp_text;
				if($temp) { $pdfstr[$temp].=", "; }
				$pdfstr["bapt_witn"]=__('baptise witness').' '.$temp_text;
				$temp="bapt_witn";
			}
		}

		// *** check for baptise items, if needed use a new line ***
		if ($text){
			if ($family_expanded==true){ $process_text.='<br>'.ucfirst(__('baptised')).' '.$text; }
				else{ $process_text.=', '.__('baptised').' '.$text; }
		}

		// *** Show age of living person ***
		if (($personDb->pers_bapt_date OR $personDb->pers_birth_date) AND !$personDb->pers_death_date AND $personDb->pers_alive!='deceased'){
			$process_age = New calculate_year_cls;
			$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,'');
			$process_text.=$dirmark1.$age;  // *** komma and space already in $age
			$pdfstr["age_liv"]=$age;
			if($pdfstr["age_liv"]!='') $temp="age_liv";
		}

		// ******************
		// *** DEATH      ***
		// ******************
		$text='';

		if ($personDb->pers_death_date OR $personDb->pers_death_place){
			$text=date_place($personDb->pers_death_date,$personDb->pers_death_place);
			$pdfstr["dead_dateplacetime"]=$text;
			if($pdfstr["dead_dateplacetime"]!='') $temp="dead_dateplacetime";
		}
		// *** Death time ***
		if (isset($personDb->pers_death_time) AND $personDb->pers_death_time){
			$text.=' '.$personDb->pers_death_time;
			$pdfstr["dead_dateplacetime"]=$text;
			$temp="dead_dateplacetime";
		}
		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_death_text);
			if ($work_text){
				$text.=", ".$work_text;
				if($temp) { $pdfstr[$temp].=", "; }
				$pdfstr["dead_text"]=$work_text;
				$temp="dead_text";
			}
		}

		// *** Show age, by Yossi Beck ***
		if (($personDb->pers_bapt_date OR $personDb->pers_birth_date) AND $personDb->pers_death_date) {
			$process_age = New calculate_year_cls;
			$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$personDb->pers_death_date);
			$text.=$dirmark1.$age;  // *** comma and space already in $age
			$pdfstr["dead_age"]=$age;
			if($pdfstr["dead_age"]!='') $temp="dead_age";
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
			$text.=$pers_death_cause;
			$pdfstr["dead_cause"]=$pers_death_cause;
			$temp="dead_cause";
		}
		else{
			if ($personDb->pers_death_cause){
				$text.=', '.__('death cause').': '.$personDb->pers_death_cause;
				if($temp) { $pdfstr[$temp].=", "; }
				$pdfstr["dead_cause"]=__('death cause').': '.$personDb->pers_death_cause;
				$temp="dead_cause";
			}
		}

		// *** Death source ***
		if ($personDb->pers_death_source){
			if($screen_mode=='PDF') {
				$pdfstr["dead_source"]=show_sources2("person","pers_death_source",$personDb->pers_gedcomnumber);
				if($pdfstr["dead_source"]!='') $temp="dead_source";
			}
			else{
				$text.=show_sources2("person","pers_death_source",$personDb->pers_gedcomnumber);
			}
		}

		// *** Death declaration ***
		if ($personDb->pers_gedcomnumber){
			$temp_text=witness($personDb->pers_gedcomnumber, 'death_declaration');
			if ($temp_text){
				$text.= ', '.__('death declaration').' '.$temp_text;
				if ($temp) { $pdfstr[$temp].=", "; }
				$pdfstr["dead_witn"]= __('death declaration').' '.$temp_text;
				$temp="dead_witn";
			}
		}

		// *** Check for death items, if needed use a new line ***
		if ($text){
			if ($family_expanded==true){ $process_text.='<br>'.ucfirst(__('DIED_SHORT')).' '.$text; }
				else{ $process_text.=', '.__('DIED_SHORT').' '.$text; }
		}

		// ****************
		// *** BURIED   ***
		// ****************
		$text='';

		if ($personDb->pers_buried_date OR $personDb->pers_buried_place){
			$text=date_place($personDb->pers_buried_date,$personDb->pers_buried_place);
			$pdfstr["buri_dateplacetime"]=$text;
			if($pdfstr["buri_dateplacetime"]!='') $temp="buri_dateplacetime";
		}
		if ($user["group_texts_pers"]=='j'){
			$work_text=process_text($personDb->pers_buried_text);
			if ($work_text){
				$text.=", ".$work_text;
				if($temp) { $pdfstr[$temp].=", "; }
				$pdfstr["buri_text"]=$work_text;
				$temp="buri_text";
			}
		}

		// *** Buried source ***
		if ($personDb->pers_buried_source){
			if($screen_mode=='PDF') {
				$pdfstr["buri_source"]=show_sources2("person","pers_buried_source",$personDb->pers_gedcomnumber);
				if($pdfstr["buri_source"]!='') $temp="buri_source";
			}
			else{
				$text.=show_sources2("person","pers_buried_source",$personDb->pers_gedcomnumber);
			}
		}

		// *** Buried witness ***
		if ($personDb->pers_gedcomnumber){
			$temp_text=witness($personDb->pers_gedcomnumber, 'burial_witness');
			if ($temp_text){
				$text.= ', '.__('burial witness').' '.$temp_text;
				$pdfstr[$temp].=", ";
				$pdfstr["buri_witn"]= __('burial witness').' '.$temp_text;
				$temp="buri_witn";
			}
		}

		// *** Check for burial items, if needed use a new line ***
		if ($text){
			if ($family_expanded==true){
				$process_text.='<br>';
				if ($personDb->pers_cremation){ $process_text.=ucfirst(__('crem.')).' '; }
				else{ $process_text.=ucfirst(__('BURIED_SHORT')).' '; }
			}
			else{
				$process_text.=', ';
				if ($personDb->pers_cremation){
					$process_text.=__('crem.').' ';
					$pdfstr["flag_buri"]=1;
				}
				else {
					$process_text.=__('BURIED_SHORT').' ';
					$pdfstr["flag_buri"]=0;
				}
			}
			$process_text.=$text;
		}

		// *** HZ-21 ash dispersion (asverstrooiing) ***
		$name_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='ash dispersion' ORDER BY event_order");
		while($nameDb=$name_qry->fetch(PDO::FETCH_OBJ)){
			$process_text.=', '.__('ash dispersion').' ';
			if ($nameDb->event_date){ $process_text.=date_place($nameDb->event_date,'').' '; }
			$process_text.=$nameDb->event_event.' ';
			//if ($nameDb->event_source){
			//	unset($source_array); $source_array[]=explode(';',$nameDb->event_source);
			//	$process_text.=show_sources($source_array);
			//}
			//if ($nameDb->event_text) { $process_text.=' '.$nameDb->event_text; }

//CHECK PDF EXPORT
			//if ($temp) { $pdfstr[$temp].=' '.__('ash dispersion').' ';
			//$pdfstr[$temp].=$nameDb->event_event; }
			//$pdfstr["buri_text"]=$work_text;
			//$temp="buri_text";
		}

		// **************************
		// *** Show professions   ***
		// **************************
		if ($personDb->pers_gedcomnumber){
			$eventnr=0;
			$event_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
				WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='profession'
				ORDER BY substring( event_date,-4 ), event_order");
			$nr_occupations=$event_qry->rowCount();
			//while($eventDb=mysql_fetch_object($event_qry)){
			while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
				$eventnr++;
				if ($eventnr=='1'){
					if ($nr_occupations=='1')
						$occupation=__('occupation');
					else
						$occupation=__('occupations');

					if ($family_expanded==true){
						$process_text.='<br><span class="profession">'.ucfirst($occupation).': ';
					}
					else{
						if ($process_text){ $process_text.='. <span class="profession">'; }
						if($temp) { $pdfstr[$temp].=". "; }
						$process_text.=ucfirst($occupation).': ';
						$pdfstr["prof_exist"]=ucfirst($occupation).': ';
						$temp="prof_exist";
					}
				}
				if ($eventnr>1){
					$process_text.=', ';
					if($temp) { $pdfstr[$temp].=", "; }
				}
				if ($eventDb->event_date OR $eventDb->event_place){
					$process_text.=date_place($eventDb->event_date,$eventDb->event_place).' ';
					$pdfstr["prof_date".$eventnr]=date_place($eventDb->event_date,$eventDb->event_place).' ';
					$temp="prof_date".$eventnr;
				}

				$process_text.=$eventDb->event_event;
				$pdfstr["prof_prof".$eventnr]=$eventDb->event_event;
				$temp="prof_prof".$eventnr;

				if ($eventDb->event_text) {
					$work_text=process_text($eventDb->event_text);
					if ($work_text){
						$process_text.=", ".$work_text;
						if($temp) { $pdfstr[$temp].=", "; }
						$pdfstr["prof_text".$eventnr]=$work_text;
						$temp="prof_text".$eventnr;
					}
				}

				// *** Profession source ***
				if ($eventDb->event_source){
					if($screen_mode=='PDF') {
						$pdfstr["prof_source"]=show_sources2("person","event_source",$eventDb->event_id);
						$temp="prof_source";
					}
					else{
						$process_text.=show_sources2("person","event_source",$eventDb->event_id);
					}
				}

			}
			if ($eventnr>0){ $process_text.='</span>'; }
		}

		// *** Show addresses ***
		if ($personDb->pers_gedcomnumber AND $user['group_living_place']=='j'){
			$text='';
			$eventnr=0;
			$event_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."addresses
				WHERE address_person_id='$personDb->pers_gedcomnumber' ORDER BY address_order");
			$nr_addresses=$event_qry->rowCount();
			while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
				$eventnr++;
				if ($eventnr=='1'){
					if ($process_text){
						if ($family_expanded==true){ $text.='<br>'; } else{ $text.='. '; }
					}
					if ($nr_addresses=='1')
						$residence=__('residence');
					else
						$residence=__('residences');
					if($temp) {$pdfstr[$temp].=". "; }
					$text.=ucfirst($residence).': ';
					$pdfstr["adres_exist"]=ucfirst($residence).': ';
					$temp="adres_exist";
				}
				if ($eventnr>1){
					$text.=', ';
					if($temp) { $pdfstr[$temp].=", "; }
				}
				if ($eventDb->address_date){
					$text.=date_place($eventDb->address_date,'').' ';
					// default, without place, place is processed later.
					$pdfstr["adres_date".$eventnr]=date_place($eventDb->address_date,'').' ';
					$temp="adres_date".$eventnr;
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
				$pdfstr["adres_adres".$eventnr]=$eventDb->address_place;
				if($pdfstr["adres_adres".$eventnr]!='') $temp="adres_adres".$eventnr;

				if ($eventDb->address_phone){
					$text.=', '.$eventDb->address_phone;
				}

				// *** Address text ***
				if ($eventDb->address_text) {
					$work_text=process_text($eventDb->address_text);
					if ($work_text){
						$text.=", ".$work_text;
						if($temp) { $pdfstr[$temp].=", "; }
						$pdfstr["adres_text".$eventnr]=$work_text;
						$temp="adres_text".$eventnr;
					}
				}

				if ($eventDb->address_source){
					if($screen_mode=='PDF') {
						$pdfstr["adres_source"]=show_sources2("person","address_source",$eventDb->address_id);
						$temp="adres_source";
					}
					else{
						$text.=show_sources2("person","address_source",$eventDb->address_id);
					}
				}

			}
			if ($text){
				$process_text.='<span class="pers_living_place">'.$text.'</span>';
			}
		}

		// *** Person source ***
		if($screen_mode=='PDF') {
			$pdfstr["pers_source"]=show_sources2("person","person_source",$personDb->pers_gedcomnumber);
			if($pdfstr["pers_source"]!='') $temp="pers_source";
		}
		else{
			$process_text.=show_sources2("person","person_source",$personDb->pers_gedcomnumber);
		}

		// *** Extended addresses for HuMo-gen and dutch Haza-data program (Haza-data plus version) ***
		if ($user['group_addresses']=='j'){
			// *** Search for all connected addresses ***
			$connect_qry="SELECT * FROM ".$tree_prefix_quoted."connections
				WHERE connect_kind='person'
				AND connect_sub_kind='person_address'
				AND connect_connect_id='".$personDb->pers_gedcomnumber."'
				ORDER BY connect_order";
			$connect_sql=$dbh->query($connect_qry);
			$eventnr=0;
			while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
				$eventnr++;
				$process_text.=', <a href="'.$uri_path.'address.php?gedcomnumber='.$connectDb->connect_item_id.'">'.__('Address').': ';
				if($temp) { $pdfstr[$temp].=", "; }
				$pdfstr["HDadres_exist".$eventnr]=__('Address').': ';
				$temp="HDadres_exist".$eventnr;
				$address_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."addresses WHERE address_gedcomnr='".$connectDb->connect_item_id."'");
				$eventDb2=$address_qry->fetch(PDO::FETCH_OBJ);
				if (isset($eventDb2->address_address) AND $eventDb2->address_address){
					$process_text.=" ".trim($eventDb2->address_address);
					$pdfstr["HDadres_adres".$eventnr]=" ".trim($eventDb2->address_address);
					$temp="HDadres_adres".$eventnr;
				}
				if (isset($eventDb2->address_address) AND $eventDb2->address_place){
					$process_text.=" ".trim($eventDb2->address_place);
					$pdfstr["HDadres_place".$eventnr]=" ".trim($eventDb2->address_place);
					$temp="HDadres_place".$eventnr;
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
				$sql="SELECT * FROM ".$tree_prefix_quoted."family
					WHERE fam_gedcomnumber='$marriage_array[$i]'";
				$parent2_fam=$dbh->query($sql);
				$parent2_famDb=$parent2_fam->fetch(PDO::FETCH_OBJ);
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
					$parent2_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
					WHERE pers_gedcomnumber='$parent2_famDb->fam_woman'");
				}
				else{
					$parent2_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."person
					WHERE pers_gedcomnumber='$parent2_famDb->fam_man'");
				}
				$parent2Db=$parent2_qry->fetch(PDO::FETCH_OBJ);
				
				if ($id==$marriage_array[$i]){
					$process_text.=',';

					if(isset($parent2_marr_data)) {$process_text.=' <b>'.$dirmark1.$parent2_marr_data.' ';}
					// *** $parent2Db is empty if it is a N.N. person ***
					if ($parent2Db){
						$name=$this->person_name($parent2Db);
						$process_text.=$name["standard_name"];
					}
					else{
						$process_text.=__('N.N.');
					}
					$process_text.='</b>';
				}
				else{
					$process_text.=', <b>';
					// *** url_rewrite ***
					if ($humo_option["url_rewrite"]=="j"){
						// *** $uri_path is made header.php ***
						$process_text.='<a href="'.$uri_path.'family/'.$_SESSION['tree_prefix'].'/'.$marriage_array[$i].'/'.$personDb->pers_gedcomnumber.'/">';
					}
					else{
						$process_text.='<a href="'.CMS_ROOTPATH.'family.php?database='.$_SESSION['tree_prefix'].'&amp;id='.$marriage_array[$i].'&amp;main_person='.$personDb->pers_gedcomnumber.'">';
					}
					//$process_text.=$parent2_marr_data.' '.__('to').': ';
					if(isset($parent2_marr_data)) {$process_text.=$dirmark1.$parent2_marr_data.' ';}
					// *** $parent2Db is empty by N.N. person ***
					if ($parent2Db){
						$name=$this->person_name($parent2Db);
						$process_text.=$name["standard_name"];
					}
					else{
						$process_text.=__('N.N.');
					}
					$process_text.='</a></b>';
				}
				if($screen_mode=="PDF") {
					if ($parent2Db){
						if($temp) { $pdfstr[$temp].=", "; }
						$name=$this->person_name($parent2Db);
						$pdfstr["marr_more".$marriagenr]=$pdf_marriage["relnr_rel"].$pdf_marriage["rel_add"]." ".$name["standard_name"];
						$temp="marr_more".$marriagenr;
					}
					else{
						if($temp) { $pdfstr[$temp].=", "; }
						$pdfstr["marr_more".$marriagenr]=$pdf_marriage["relnr_rel"]." ".__('N.N.');
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
		//$process_text.=show_media($personDb,''); // *** This function can be found in file: show_picture.php! ***
		$result = show_media($personDb,''); // *** This function can be found in file: show_picture.php! ***
		$process_text.= $result[0];
		//$pdfstr = array_merge($pdfstr,$result[1]);
		if (isset($pdfstr))
			$pdfstr = array_merge((array)$pdfstr,(array)$result[1]);
		else
			$pdfstr=$result[1];

		// *** Internet links (URL) ***
		$url_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
			WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='URL'
			ORDER BY event_order");
		if ($url_qry->rowCount()>0){ $process_text.='<br>'; }
		//while($urlDb=mysql_fetch_object($url_qry)){
		while($urlDb=$url_qry->fetch(PDO::FETCH_OBJ)){
			if ($urlDb->event_text){ $process_text.=$urlDb->event_text.': '; }
			$process_text.='<a href="'.$urlDb->event_event.'" target="_blank">'.$urlDb->event_event.'</a>';
			$process_text.='<br>';
		}

		//******** Person text **************
		if ($user["group_text_pers"]=='j'){
			$work_text=process_text($personDb->pers_text);

			// *** BK: Source by person text ***
			if ($personDb->pers_text_source){
				$work_text.=show_sources2("person","pers_text_source",$personDb->pers_gedcomnumber);
			}

			if ($work_text){
				$process_text.='<br>'.$work_text;
				$pdfstr["pers_text"]="\n".$work_text;
				$temp="pers_text";
			}
		}

		// *** Show events ***
		if ($user['group_event']=='j'){
			if ($personDb->pers_gedcomnumber){
				$event_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."events
				WHERE event_person_id='$personDb->pers_gedcomnumber' AND event_kind='event' ORDER BY event_order");
				$num_rows = $event_qry->rowCount();
				if ($num_rows>0){ $process_text.='<span class="event">'."\n"; }
				$eventnr=0;
				while($eventDb=$event_qry->fetch(PDO::FETCH_OBJ)){
					$eventnr++;
					$process_text.="<br>\n";
					$pdfstr["event_start".$eventnr]="\n";

					// *** Check if NCHI is 0 or higher ***
					$event_gedcom=$eventDb->event_gedcom;
					//$event_text=$eventDb->event_text;
					if ($event_gedcom=='NCHI' AND trim($eventDb->event_event)==''){
						$event_gedcom='NCHI0';
						//$event_text='';
					}

					$process_text.=language_event($event_gedcom);
					$pdfstr["event_ged".$eventnr]=language_event($event_gedcom);
					$temp="event_ged".$eventnr;

					if ($eventDb->event_event){
						$process_text.=' '.$eventDb->event_event;
						$pdfstr["event_event".$eventnr]=' '.$eventDb->event_event;
						$temp="event_event".$eventnr;
					}
					$process_text.=' '.date_place($eventDb->event_date, $eventDb->event_place);
					$pdfstr["event_dateplace".$eventnr]=' '.date_place($eventDb->event_date, $eventDb->event_place);
					$temp="event_dateplace".$eventnr;

					if ($eventDb->event_text){
						$work_text=process_text($eventDb->event_text);
						if ($work_text){
							$process_text.=", ".$work_text;
							if($temp) { $pdfstr[$temp].=", "; }
							$pdfstr["event_text".$eventnr]=$work_text;
							$temp="event_text".$eventnr;
						}
					}

					if ($eventDb->event_source){
						if($screen_mode=='PDF') {
							$pdfstr["event_source"]=show_sources2("person","event_source",$eventDb->event_id);
							$temp="event_source";
						}
						else{
							$process_text.=show_sources2("person","event_source",$eventDb->event_id);
						}
					}

				}
				if ($num_rows>0){ $process_text.="</span>\n"; }
			}
		}

	} // End of privacy

	// *** Return person data ***
	if($screen_mode=="mobile") {
		if ($process_text){
			// *** Remove first ", " ***
			if (substr($process_text,0,2)==', '){ $process_text=ucfirst(substr($process_text,2)); }
			// *** Remove first <br> in expanded text ***
			if ($family_expanded==true AND substr($process_text,0,4)=='<br>')
				$process_text=ucfirst(substr($process_text,4));
			return $process_text;
		}
	}
	elseif($screen_mode!="PDF") {
		if ($process_text){
			// *** Remove first ", " ***
			if (substr($process_text,0,2)==', ') $process_text=ucfirst(substr($process_text,2));
			// *** Remove first <br> in expanded text ***
			if ($family_expanded==true AND substr($process_text,0,4)=='<br>') $process_text=ucfirst(substr($process_text,4));

			$div='';
			if ($person_kind=='child'){
				if ($childnr<10){ $div.='<div class="margin_child">'; }
				else{ $div.='<div class="margin_child2">'; }
			}
			else{
				$div.='<div class="margin_person">';
			}
			return $privacy_filter.$div.$process_text.'</div>';
		}
		else{
			return $privacy_filter;
		}
	}
	else {   // return array with pdf values
		if(isset($pdfstr)) {return $pdfstr;}
	}

	} // End of check $personDb

} // End of function person_data.
} // End of person_cls
?>