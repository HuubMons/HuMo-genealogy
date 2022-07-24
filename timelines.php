<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");
require_once(CMS_ROOTPATH."include/person_cls.php");
require_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/calculate_age_cls.php");
$process_age = New calculate_year_cls;

if(isset($_GET['id'])) $id=$_GET['id'];
$personDb = $db_functions->get_person($id);

$isborn=0; $isdeath=0; $ismarr=0; $ischild=0;
$deathtext=''; $borntext=''; $bapttext=''; $burrtext=''; $marrtext= Array();
$privacy_filtered=false;

function julgreg($date) {   // alters a julian/gregorian date entry such as 4 mar 1572/3 to use regular date for calculations
	if (strpos($date,'/')>0){
		$temp=explode ('/',$date);
		$date=$temp[0];
	}
	return $date;
}

$bornyear=''; $borndate=''; $temp='';
if(@$personDb->pers_birth_date) {
	$borndate=julgreg($personDb->pers_birth_date);
	$temp=substr($borndate,-4);
	if($temp > 0 AND $temp < 2200) {
		$bornyear=$temp;
		$borntext= ucfirst(__('birth')).' '.language_date($borndate);
		$isborn=1;
	}
}
$baptyear=''; $baptdate=''; $temp='';
if(@$personDb->pers_bapt_date) {
	$baptdate=julgreg($personDb->pers_bapt_date);
	$temp=substr($baptdate,-4);
	if($temp > 0 AND $temp < 2200) {
		$baptyear=$temp;
		$bapttext= ucfirst(__('baptised')).' '.language_date($baptdate);
		$isborn=1;
	}
}
$deathyear=''; $deathdate=''; $temp='';
if(@$personDb->pers_death_date) {
	$deathdate=julgreg($personDb->pers_death_date);
	$temp=substr($deathdate,-4);
	if($temp > 0 AND $temp < 2200) {
		$deathyear=$temp;
		$deathtext= ucfirst(__('death')).' '.language_date($deathdate);

		$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$personDb->pers_death_date, true);
		if ($age){ $deathtext='['.$age.'] '.$deathtext; }

		$isdeath=1;
	}
}
$burryear=''; $burrdate=''; $temp='';
if(@$personDb->pers_buried_date) {
	$burrdate=julgreg($personDb->pers_buried_date);
	$temp=substr($burrdate,-4);
	if($temp > 0 AND $temp < 2200) {
		$burryear=$temp;
		$burrtext= ucfirst(__('buried')).language_date($burrdate);
		$isdeath=1;
	}
}

// ***********  MARRIAGES & CHILDREN
if(@$personDb->pers_fams) {
	$marriages=explode(";",$personDb->pers_fams);
	for($i=0; $i<count($marriages);$i++) {
		$children[$i]=''; $marryear[$i]=''; $marrdate[$i]=''; $temp='';
		@$familyDb = $db_functions->get_family($marriages[$i]);
		if ($personDb->pers_gedcomnumber==$familyDb->fam_man){
			$spouse=$familyDb->fam_woman;
		}
		else{
			$spouse=$familyDb->fam_man;
		}
		@$spouse2Db = $db_functions->get_person($spouse);
		$privacy=true;
		if ($spouse2Db){
			$person_cls = New person_cls;
			$person_cls->construct($spouse2Db);
			$privacy=$person_cls->privacy;
			$name=$person_cls->person_name($spouse2Db);
		}
		if (!$privacy){
			if(isset($spouse2Db->pers_death_date) AND $spouse2Db->pers_death_date) {
				$spousedeathname[$i]=''; $spousedeathyear[$i]=''; $spousedeathtext[$i]='';
				$spousedeathdate[$i]=julgreg($spouse2Db->pers_death_date);
				$temp=substr($spousedeathdate[$i],-4);
				if($temp AND $temp > 0 AND $temp < 2200) {
					if($spouse2Db->pers_sexe=="M") { $spouse=__('SPOUSE_MALE'); }
					else { $spouse=__('SPOUSE_FEMALE'); }
						$spousedeathyear[$i]=$temp;
						if($name["firstname"]) { $spousedeathname[$i]=$name["firstname"]; }
						$spousedeathtext[$i]= ucfirst(__('death')).' '.$spouse." ".$spousedeathname[$i]." ".$dirmark1.str_replace(" ","&nbsp;",language_date($spousedeathdate[$i]));

						$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$spouse2Db->pers_death_date, true);
						if ($age){ $spousedeathtext[$i]='['.$age.'] '.$spousedeathtext[$i]; }
				}
			}

			$temp='';
			if($familyDb->fam_marr_date) {
				$marrdate[$i]=julgreg($familyDb->fam_marr_date);
				$text=ucfirst(__('marriage')).' ';
			}
			elseif($familyDb->fam_marr_church_date) {
				$marrdate[$i]=julgreg($familyDb->fam_marr_church_date);
				$text=ucfirst(__('church marriage')).' ';
			}
			elseif($familyDb->fam_marr_notice_date) {
			$marrdate[$i]=julgreg($familyDb->fam_marr_notice_date);
				$text=ucfirst(__('marriage notice')).' ';
			}
			elseif($familyDb->fam_marr_church_notice_date) {
				$marrdate[$i]=julgreg($familyDb->fam_marr_church_notice_date);
				$text=ucfirst(__('church marriage notice')).' ';
			}
			elseif($familyDb->fam_relation_date) {
				$marrdate[$i]=julgreg($familyDb->fam_relation_date);
				$text=ucfirst(__('partnership')).' ';
			}
			if($marrdate[$i]) {
				$temp=substr($marrdate[$i],-4);
			}
			if($temp AND $temp > 0 AND $temp < 2200) {
				if($name["firstname"]) {
					$spousename=$name["firstname"];
					$spousetext=__('with ').$spousename;
				}
				$marryear[$i]=$temp;
				//$marrtext[$i]= $text.$spousetext."<br>".language_date($marrdate[$i]);
				$marrtext[$i]= $text.$spousetext." ".$dirmark1.str_replace(" ","&nbsp;",language_date($marrdate[$i]));
				$ismarr=1;

				$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$marrdate[$i], true);
				if ($age){ $marrtext[$i]='['.$age.'] '.$marrtext[$i]; }
			}

		}
		else{
			// *** Privacy filter activated ***
			$privacy_filtered=true;
		}

		if($familyDb->fam_children) {
			$children[$i]=explode(";",$familyDb->fam_children);
			for($m=0; $m<count($children[$i]); $m++) {
				$chmarriages[$i][$m]=''; // enter value so we wont get error messages
				@$chldDb = $db_functions->get_person($children[$i][$m]);

				if($chldDb->pers_sexe=="M") { $child=__('son'); }
				else if ($chldDb->pers_sexe=="F") { $child=__('daughter'); }
				else { $child=__('child '); }

				$person2_cls = New person_cls;
				$person2_cls->construct($chldDb);
				$privacy=$person2_cls->privacy;
				$name=$person2_cls->person_name($chldDb);
				if (!$privacy){
					$chbornyear[$i][$m]=''; $chborndate[$i][$m]=''; $chborntext[$i][$m]='';
					$chdeathyear[$i][$m]=''; $chdeathdate[$i][$m]=''; $chdeathtext[$i][$m]='';
					$temp='';

					$childname[$i][$m]=$name["firstname"];
					$chborndate[$i][$m]=julgreg($chldDb->pers_birth_date);
					$temp=substr($chborndate[$i][$m],-4);
					if($temp > 0 AND $temp < 2200) {
						$chbornyear[$i][$m]=$temp;
						$chborntext[$i][$m]=ucfirst(__('birth')).' '.$child." ".$childname[$i][$m]." ".$dirmark1.str_replace(" ","&nbsp;",language_date($chborndate[$i][$m]));
						$ischild=1;

						$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$chldDb->pers_birth_date, true);
						if ($age){ $chborntext[$i][$m]='['.$age.'] '.$chborntext[$i][$m]; }
					}
					$chdeathdate[$i][$m]=julgreg($chldDb->pers_death_date);
					$temp='';
					$temp=substr($chdeathdate[$i][$m],-4);
					if($temp > 0 AND $temp < 2200) {
						$chdeathyear[$i][$m]=$temp;
						$chdeathtext[$i][$m]=ucfirst(__('death')).' '.$child." ".$childname[$i][$m]." ".$dirmark1.str_replace(" ","&nbsp;",language_date($chdeathdate[$i][$m]));

						$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$chldDb->pers_death_date, true);
						if ($age){ $chdeathtext[$i][$m]='['.$age.'] '.$chdeathtext[$i][$m]; }
					}
				}
				else{
					// *** Privacy filter activated ***
					$privacy_filtered=true;
				}
				if($chldDb->pers_fams) {
					$chmarriages[$i][$m]=explode(";",$chldDb->pers_fams);
					for($p=0; $p<count($chmarriages[$i][$m]);$p++) {
						$grchildren[$i][$m][$p]=''; // enter value so webserver wont throw error messages
						$chmarryear[$i][$m][$p]=''; $chmarrdate[$i][$m][$p]=''; $temp='';
						@$chfamilyDb = $db_functions->get_family($chmarriages[$i][$m][$p]);

						// CHILDREN'S MARRIAGES
						if ($chldDb->pers_gedcomnumber==$chfamilyDb->fam_man){
							$chspouse=$chfamilyDb->fam_woman;
						}
						else{
							$chspouse=$chfamilyDb->fam_man;
						}
						@$chspouse2Db = $db_functions->get_person($chspouse);
						$person_cls = New person_cls;
						$person_cls->construct($chspouse2Db);
						$privacy=$person_cls->privacy;
						$name=$person_cls->person_name($chspouse2Db);
						if (!$privacy){
							if ($chfamilyDb->fam_marr_date) {
								$chmarrdate[$i][$m][$p]=julgreg($chfamilyDb->fam_marr_date);
								$chtext=ucfirst(__('marriage')).' ';
							}
							elseif ($chfamilyDb->fam_marr_church_date) {
								$chmarrdate[$i][$m][$p]=julgreg($chfamilyDb->fam_marr_church_date);
								$chtext=ucfirst(__('church marriage')).' ';
							}
							elseif ($chfamilyDb->fam_marr_notice_date) {
							$chmarrdate[$i][$m][$p]=julgreg($chfamilyDb->fam_marr_notice_date);
								$chtext=ucfirst(__('marriage notice')).' ';
							}
							elseif	($chfamilyDb->fam_marr_church_notice_date) {
								$chmarrdate[$i][$m][$p]=julgreg($chfamilyDb->fam_marr_church_notice_date);
								$chtext=ucfirst(__('church marriage notice')).' ';
							}
							elseif	($chfamilyDb->fam_relation_date) {
								$chmarrdate[$i][$m][$p]=julgreg($chfamilyDb->fam_relation_date);
								$chtext=ucfirst(__('partnership')).' ';
							}
							if	($chmarrdate[$i][$m][$p]) {
								$temp=substr($chmarrdate[$i][$m][$p],-4);
							}
							if	($temp AND $temp > 0 AND $temp < 2200) {
								//if	(isset($chspouse2Db->pers_firstname) AND $chspouse2Db->pers_firstname) {
								if ($name["firstname"]){
									$chspousename=$name["firstname"];
									$chspousetext=__('with ').$chspousename;
								}
								$chmarryear[$i][$m][$p]=$temp;
								$chmarrtext[$i][$m][$p]= $chtext.$child." ".$childname[$i][$m].' '.$chspousetext." ".$dirmark1.str_replace(" ","&nbsp;",language_date($chmarrdate[$i][$m][$p]));
								//$chismarr=1;

								$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$chmarrdate[$i][$m][$p], true);
								if ($age){ $chmarrtext[$i][$m][$p]='['.$age.'] '.$chmarrtext[$i][$m][$p]; }
							}

						}
						else{
							// *** Privacy filter activated ***
							$privacy_filtered=true;
						}
						// END CHILDREN'S MARRIAGES

						if($chfamilyDb->fam_children) {

							$grchildren[$i][$m][$p]=explode(";",$chfamilyDb->fam_children);
							for($g=0; $g<count($grchildren[$i][$m][$p]); $g++) {
								@$grchldDb = $db_functions->get_person($grchildren[$i][$m][$p][$g]);
								$person3_cls = New person_cls;
								$person3_cls->construct($grchldDb);
								$privacy=$person3_cls->privacy;
								$name=$person3_cls->person_name($grchldDb);
								if (!$privacy){

									$grchbornyear[$i][$m][$p][$g]=''; $grchborndate[$i][$m][$p][$g]=''; $grchborntext[$i][$m][$p][$g]='';
									$grchdeathyear[$i][$m][$p][$g]=''; $grchdeathdate[$i][$m][$p][$g]=''; $grchdeathtext[$i][$m][$p][$g]='';
									$temp='';

									if($grchldDb->pers_sexe=="M") { $grchild=__('grandson'); }
									else if ($grchldDb->pers_sexe=="F") { $grchild=__('granddaughter'); }
									else { $grchild=__('grandchild'); }

									$grchildname[$i][$m][$p][$g]=$name["firstname"];
									$grchborndate[$i][$m][$p][$g]=julgreg($grchldDb->pers_birth_date);
									$temp=substr($grchborndate[$i][$m][$p][$g],-4);
									if($temp > 0 AND $temp < 2200) {
										$grchbornyear[$i][$m][$p][$g]=$temp;
										$grchborntext[$i][$m][$p][$g]=ucfirst(__('birth')).' '.$grchild." ".$grchildname[$i][$m][$p][$g]." ".$dirmark1.str_replace(" ","&nbsp;",language_date($grchborndate[$i][$m][$p][$g]));

										$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$grchldDb->pers_birth_date, true);
										if ($age){ $grchborntext[$i][$m][$p][$g]='['.$age.'] '.$grchborntext[$i][$m][$p][$g]; }
									}
									$grchdeathdate[$i][$m][$p][$g]=julgreg($grchldDb->pers_death_date);
									$temp='';
									$temp=substr($grchdeathdate[$i][$m][$p][$g],-4);
									if($temp > 0 AND $temp < 2200) {
										$grchdeathyear[$i][$m][$p][$g]=$temp;
										$grchdeathtext[$i][$m][$p][$g]=ucfirst(__('death')).' '.$grchild." ".$grchildname[$i][$m][$p][$g]."  ".$dirmark1.str_replace(" ","&nbsp;",language_date($grchdeathdate[$i][$m][$p][$g]));

										$age=$process_age->calculate_age($personDb->pers_bapt_date,$personDb->pers_birth_date,$grchldDb->pers_death_date, true);
										if ($age){ $grchdeathtext[$i][$m][$p][$g]='['.$age.'] '.$grchdeathtext[$i][$m][$p][$g]; }
									}
								} // end if privacy==''

								else{
								// *** Privacy filter activated ***
									$privacy_filtered=true;
								}
							} // end for grchildren
						}	// end if grchildren
					} // end for chmarriages
				} //end if chldDb->pers_fams
			} //end for
		} // end if children
	}
}
// *********** END MARRIAGES & CHILDREN

// *********** CHECK IF ANY DATES ARE AVAILABLE. IF PART ARE MISSING ESTIMATE BIRTH/DEATH

// *** Check privacy filter ***

$person_cls = New person_cls;
$person_cls->construct($personDb);
$privacy=$person_cls->privacy;
if($privacy) {
	echo '<br><br>'.__('PRIVACY FILTER');
	exit();
}

if($isborn==0 AND $isdeath==0 AND $ismarr==0 AND $ischild==0) {   // no birth or death dates available
	echo "<br><br>".__('There are no dates available for this person. Timeline can not be calculated.');
	exit();
}
if($isborn==1 AND $isdeath==0) {  // birth date but no death date: we show 80 years from birth
	if($bornyear!=0) {
		$deathyear=$bornyear+80;
	}
	else {
		$deathyear=$baptyear+80;
	}
	$deathtext=__('Date of death unknown');
	if($deathyear > date("Y")) {  // if birth+80 goes beyond present, we stop there but of course don't mention death.... ;-)
		$deathyear=date("Y");
		$deathtext='';
	}
}
if($isborn==0 AND $isdeath==1) {  // death date but no birth date: we show 80 years prior to death
	if($deathyear!=0) {
		$bornyear=$deathyear-80;
	}
	else {
		$bornyear=$burryear-80;
	}
	$borntext=__('Date of birth unknown');
}
if($isborn==0 AND $isdeath==0 AND $ismarr==1) {
	// no birth or death date but there is a marriage date:
	// birth is estimated as 25 years prior to marriage date
	// death is estimated as 55 years after marriage date
	if($marryear[0]!=0){
		$bornyear=$marryear[0]-25;
		$deathyear=$marryear[0]+55;
	}
	$borntext=__('Date of birth unknown');
	$deathtext=__('Date of death unknown');
}
if($isborn==0 AND $isdeath==0 AND $ismarr==0 and $ischild==1) {
	// no birth,death or marriage date but there is a childbirth date:
	// birth is estimated as 25 years prior to child birth date
	// death is estimated as 55 years after child birth date
	if($chbornyear[0][0]!=0) {
		$bornyear=$chbornyear[0][0]-25;
		$deathyear=$chbornyear[0][0]+55;
	}
	$borntext=__('Date of birth unknown');
	$deathtext=__('Date of death unknown');
}

// *** OPEN TIMELINE DIRECTORY FOR READING AVAILABLE FILES ***
if (is_dir(CMS_ROOTPATH."languages/".$selected_language."/timelines")){
	// *** Open languages/xx/timelines folder ***
	$dh  = opendir(CMS_ROOTPATH."languages/".$selected_language."/timelines");
}
else{
	// *** No timelines folder found inside selected language: use default timeline folder ***
	$dh  = opendir(CMS_ROOTPATH."languages/default_timelines");
}

$counter=0;
while (false !== ($filename = readdir($dh))) {
	if (strtolower(substr($filename, -3)) == "txt"){
		$counter++;
		if (is_file(CMS_ROOTPATH."languages/".$selected_language."/timelines/".$filename)) {
			$filenames[$counter-1][0]=CMS_ROOTPATH."languages/".$selected_language."/timelines/".$filename;
		}
		elseif (is_file(CMS_ROOTPATH."languages/default_timelines/".$filename)){
			$filenames[$counter-1][0]=CMS_ROOTPATH."languages/default_timelines/".$filename;
		}
		else{
			$filenames[$counter-1][0]=''; // Should not be used normally...
		}
		$filenames[$counter-1][1]=substr($filename,0,-4);
	}
}
sort($filenames);

// *** Selected step ***
$step=5; // default step - user can choose 1 or 10 instead
if(isset($_POST['step'])) $step=$_POST['step'];

// *** Selected timeline ***
$tml = $filenames[0][1]; // if default is not set the first file will be checked
if(isset($_POST['tml'])) $tml=$_POST['tml'];
elseif(isset($humo_option['default_timeline']) AND $humo_option['default_timeline']!="") {

	$str = explode("@",substr($humo_option['default_timeline'],0,-1));  // humo_option is: nl!europa@de!Sweitz@en!british  etc.
	$val_arr = Array();
	foreach($str AS $value) {
		$str2 = explode("!",$value);   //  $value = nl!europa
		$val_arr[$str2[0]] = $str2[1];   //  $val_arr[nl]='europa'
	}

	// *** Use timeline file from default folder ***
	if(isset($val_arr[$selected_language]) AND is_file(CMS_ROOTPATH."languages/default_timelines/".$val_arr[$selected_language].".txt")) {
		$tml= $val_arr[$selected_language];
	}

	// *** Use timeline from language folder ***
	if(isset($val_arr[$selected_language]) AND is_file(CMS_ROOTPATH."languages/".$selected_language."/timelines/".$val_arr[$selected_language].".txt")) {
		$tml= $val_arr[$selected_language];
	}

	// *** Use timeline file from default folder ***
	$selected_language2='default_timelines';
	if(!isset($val_arr[$selected_language]) AND is_file(CMS_ROOTPATH."languages/default_timelines/".$val_arr[$selected_language2].".txt")) {
		$tml= $val_arr[$selected_language2];
	}
}
//$default=false; if($tml==$filenames[0][1]) $default=true;

// **** SHOW MENU ****
echo '<table align="center" class="humo index_table">';
echo '<tr><td>';
	if(CMS_SPECIFIC=="Joomla") {
		echo '<form name="tmlstep" method="post" action="index.php?option=com_humo-gen&task=timelines&id='.$id.'&amp;database='.$database.'" style="display:inline;">';
	}
	else {
		echo '<form name="tmlstep" method="post" action="timelines.php?id='.$id.'&amp;database='.$database.'" style="display:inline;">';
	}

	//======== HELP POPUP ========================
	if(CMS_SPECIFIC=="Joomla") {
		echo '<div class="fonts '.$rtlmarker.'sddm" style="postion:absolute; top:32; left:7;">';
		$popwidth="width:700px;";
	}
	else {
		echo '<div class="fonts '.$rtlmarker.'sddm" style="display:inline">';
		$popwidth="";
	}
	echo '&nbsp;&nbsp;&nbsp;<a href="#"';
	echo ' style="display:inline" ';
	if(CMS_SPECIFIC=="Joomla") {
		echo 'onmouseover="mopen(event,\'help_menu\',0,0)"';
	}
	else {
		echo 'onmouseover="mopen(event,\'help_menu\',10,150)"';
	}
	echo 'onmouseout="mclosetime()">';
	echo '<strong>'.__('Help').'</strong>';
	echo '</a>&nbsp;';
	echo '<div class="sddm_fixed" style="'.$popwidth.' z-index:40; text-align:'.$alignmarker.'; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

		echo __('Explanation of the timeline chart:<br>
<ul><li>The middle column displays the years of the timeline. The starting point will be just before birth and the end year will be just after death.</li>
<li>The left column displays the events in the person\'s life.<br>
Events listed are: birth, death and marriage(s) of main person, death of spouse, birth, marriage and death of children and birth and death of grandchildren.<br>
Birth, death, marriages and death of spouse are listed in bold red. Birth, marriage and death of children in green. Birth and death of grandchildren in blue</li>
<li>The rightmost column displays historic events that took place in these years.</li></ul>
The timeline menu:<br>
<ul><li>On the top part of the menu you can choose how the chart will be displayed. There are three choices:<br>
1 - will display each year in a separate row.<br>
5 - will create periods of five years for a more concise display.<br>
10 - displays the chart in periods of one decade for even more concise display.</li>
<li>If the webmaster enabled more than one timeline, the bottom part of the menu will let you choose from amongst several possible timelines. For example "American History", "Dutch History" etc.</li>
<li><strong>After choosing the desired step and/or timeline, click the "Change Display" button on the bottom of the menu.</strong></li></ul>');

			echo '</div>';
	echo '</div><br>';
	
	// =============================================
	// *** Steps of years in display: 1, 5 or 10 ***
	echo '<br>'.__('Steps:').'<br>';
	echo '<span class="select_box"><input type="radio" name="step" value="1"'; if ($step==1) echo ' checked="checked"';
	echo ' >1 '.__('year').'</span>';
	echo '<span class="select_box"><input type="radio" name="step" value="5"'; if ($step==5) echo ' checked="checked"';
	echo ' >5 '.__('years').'</span>';
	echo '<span class="select_box"><input type="radio" name="step" value="10"'; if ($step==10) echo ' checked="checked"';
	echo ' >10 '.__('years').'</span>';

	// *** Choice of timeline files available ***
	if(count($filenames) > 1) { // only show timelines menu if there are more than 1 timeline files

		echo '<br><br>'.__('Choose timeline').':<br>';

		//echo '<div style="direction:ltr">';
		$selected_language2='default_timelines';
		$checked='';
		for ($i=0; $i<count($filenames); $i++){
			// *** A timeline is selected ***
			if(isset($_POST['tml']) AND $_POST['tml']==$filenames[$i][1]) {
				$checked=" checked";
			}

			// *** If no selection is made, use default settings ***
			if(!isset($_POST['tml'])){
				// *** humo_option is: nl!europa@de!Sweitz@en!british  etc. ***
				if(isset($humo_option['default_timeline']) AND strpos($humo_option['default_timeline'],$selected_language."!".$filenames[$i][1]."@") !== false) {
					$checked=" checked";
				}
				// *** humo_option is: nl!europa@de!Sweitz@en!british  etc. ***
				elseif(isset($humo_option['default_timeline']) AND strpos($humo_option['default_timeline'],$selected_language2."!".$filenames[$i][1]."@") !== false) {
					$checked=" checked";
				}
			}

			echo '<span class="select_box">';
				echo '<input type="radio" name="tml" value="'.$filenames[$i][1].'"'.$checked.'>'.$filenames[$i][1];
			echo '</span>';
			$checked='';
		}
		//echo '</div>';
	}
	echo '<br clear="all"><br><input type="submit" value="'.__('Change Display').'" >';
	echo '</form>';

echo '</td></tr></table><br>';

// **** END MENU ****
if(file_exists($filenames[0][0])) {
	if (file_exists(CMS_ROOTPATH."languages/".$selected_language."/timelines/".$tml.'.txt')){
		$handle = fopen(CMS_ROOTPATH."languages/".$selected_language."/timelines/".$tml.'.txt',"r");
	}
	elseif (file_exists(CMS_ROOTPATH."languages/default_timelines/".$tml.'.txt')){
		$handle = fopen(CMS_ROOTPATH."languages/default_timelines/".$tml.'.txt',"r");
	}
}
($isborn==1 AND $bornyear == '') ? $byear = $baptyear : $byear = $bornyear; // if only bapt date available use that
$beginyear=$byear-(($byear % $step) + $step);    // if beginyear=1923 and step is 5 this makes it 1915
($isdeath==1 AND $deathyear == '') ? $dyear = $burryear : $dyear = $deathyear; // if only burial date available use that
$endyear=$dyear+(($step-($dyear % $step)))+($step); // if endyear=1923 and step is 5 this makes it 1929
if($endyear>date("Y")) { $endyear=date("Y"); }
$flag=0; // flags a first entry of timeline event in a specific year. is set to 1 when at least one entry has been made

// ****** DISPLAY

//echo "<div style='position:absolute;top:30px;left:150px;right:10px'>";
//echo "<div style='position:absolute; left:150px; right:10px'>";

if ($privacy_filtered==true){
	echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***').'<br>';
}

//echo "<table id='timetable' class='humo' style='border:1px'>";
echo '<table align="center" class="humo index_table">';

$name=$person_cls->person_name($personDb);
echo "<tr class=table_headline><th colspan='3'>".$name["name"]."</th></tr>";

echo "<tr class=table_headline><th>".__('Life events')."</th>";
echo "<th>".__('Year')."</th>";
$nofiles='';
if(!file_exists($filenames[0][0]))
	$nofiles="<br>".__('There are no timeline files available for this language.');
echo "<th>".__('Historic events').$nofiles."</th></tr>";

$step==1?$yearwidth=60:$yearwidth=120; // when step is 1 the column can be much shorter
$flag_isbuffer=0;
$eventdir="ltr"; // default direction of timeline file is ltr (set to rtl later in the script if necessary
for($yr=$beginyear; $yr<$endyear; $yr+=$step) {  // range of years for lifespan

	// DISPLAY LIFE EVENTS FOR THIS YEAR/PERIOD (1st column)

	echo "<tr><td style='width:250px;padding:4px;vertical-align:top;font-weight:bold;color:red'>";
	$br_flag=0;
	for($tempyr=$yr; $tempyr<$yr+$step; $tempyr++) {

		if ($bornyear!='' AND $bornyear == $tempyr) {
			if($br_flag==1) { echo "<br>"; }
			echo $borntext;
			$br_flag=1;
		}
		else if ($baptyear!='' AND $baptyear == $tempyr) {
			if($br_flag==1) { echo "<br>"; }
			echo $bapttext;
			$br_flag=1;
		}
		if(isset($marryear)) {
			for($i=0;$i<count($marryear);$i++) {
				if ($marryear[$i]!='' AND $marryear[$i] == $tempyr) {
					if($br_flag==1) { echo "<br>"; }
					echo $marrtext[$i];
					$br_flag=1;
				}
			}
		}
		if(isset($spousedeathyear)) {
			for($i=0;$i<count($spousedeathyear);$i++) {
				if ($spousedeathyear[$i]!='' AND $spousedeathyear[$i] == $tempyr) {
					if($br_flag==1) { echo "<br>"; }
					echo $spousedeathtext[$i];
					$br_flag=1;
				}
			}
		}
		if(isset($chbornyear)) {
			for($i=0; $i<count($marriages);$i++) {
				if(is_array($children[$i])) {
					for($m=0; $m<count($children[$i]);$m++) {
						if (isset($chbornyear[$i][$m]) AND $chbornyear[$i][$m] == $tempyr) {
							if($br_flag==1) { echo "<br>"; }
							echo "<span style='color:green;font-weight:normal'>".$chborntext[$i][$m]."</span>";
							$br_flag=1;
						}
					}
				}
			}
		}
		if(isset($chdeathyear)) {
			for($i=0; $i<count($marriages);$i++) {
				if(is_array($children[$i])) {
					for($m=0; $m<count($children[$i]);$m++) {
						if (isset($chdeathyear[$i][$m]) AND $chdeathyear[$i][$m] == $tempyr) {
							if($br_flag==1) { echo "<br>"; }
							echo "<span style='color:green;font-weight:normal'>".$chdeathtext[$i][$m]."</span>";
							$br_flag=1;
						}
					}
				}
			}
		}
		if(isset($chmarryear)) {
			for($i=0; $i<count($marriages);$i++) {
				if(is_array($children[$i])) {
					for($m=0; $m<count($children[$i]);$m++) {
						if(is_array($chmarriages[$i][$m])) {
							for($p=0; $p<count($chmarriages[$i][$m]);$p++) {
								if (isset($chmarryear[$i][$m][$p]) AND $chmarryear[$i][$m][$p]!='' AND $chmarryear[$i][$m][$p] == $tempyr) {
									if($br_flag==1) { echo "<br>"; }
									echo "<span style='color:green;font-weight:normal'>".$chmarrtext[$i][$m][$p]."</span>";
									$br_flag=1;
								}
							}
						}
					}
				}
			}
		}
		if(isset($grchbornyear)) {
			for($i=0; $i<count($marriages);$i++) {
				if(is_array($children[$i])) {
					for($m=0; $m<count($children[$i]);$m++) {
						if(is_array($chmarriages[$i][$m])) {
							for($p=0; $p<count($chmarriages[$i][$m]);$p++) {
								if(is_array($grchildren[$i][$m][$p])) {
									for($g=0; $g<count($grchildren[$i][$m][$p]);$g++) {
										if (isset($grchbornyear[$i][$m][$p][$g]) AND $grchbornyear[$i][$m][$p][$g]!='' AND $grchbornyear[$i][$m][$p][$g] == $tempyr) {
											if($br_flag==1) { echo "<br>"; }
											echo "<span style='color:blue;font-weight:normal'>".$grchborntext[$i][$m][$p][$g]."</span>";
											$br_flag=1;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		if(isset($grchdeathyear)) {
			for($i=0; $i<count($marriages);$i++) {
				if(is_array($children[$i])) {
					for($m=0; $m<count($children[$i]);$m++) {
						if(is_array($chmarriages[$i][$m])) {
							for($p=0; $p<count($chmarriages[$i][$m]);$p++) {
								if(is_array($grchildren[$i][$m][$p])) {
									for($g=0; $g<count($grchildren[$i][$m][$p]);$g++) {
										if (isset($grchdeathyear[$i][$m][$p][$g]) AND $grchdeathyear[$i][$m][$p][$g]!='' AND $grchdeathyear[$i][$m][$p][$g] == $tempyr) {
											if($br_flag==1) { echo "<br>"; }
											echo "<span style='color:blue;font-weight:normal'>".$grchdeathtext[$i][$m][$p][$g]."</span>";
											$br_flag=1;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		if ($deathyear!='' AND $deathyear == $tempyr) {
			if($br_flag==1) { echo "<br>"; }
			echo $deathtext;
			$br_flag=1;
		}
		else if ($burryear!='' AND $burryear == $tempyr) {
			if($br_flag==1) { echo "<br>"; }
			echo $burrtext;
			$br_flag=1;
		}
	} // end life events loop

	echo "</td>";

	// DISPLAY YEAR/PERIOD (2nd column)
	$period='';
	if($step!=1) {
		//$period="-".($yr+$step)+1;   // not allowed in PHP 7.4.
		$tmp=($yr+$step)+1;
		$period="-".$tmp;
	}
	echo "<td style='width:".$yearwidth."px;padding:4px;text-align:center;vertical-align:top;font-weight:bold;font-size:120%'>".$yr.$period."</td>";

	// DISPLAY HISTORIC EVENTS FOR THIS YEAR/PERIOD (3rd column)
	echo "<td style='vertical-align:top'>";

	if(file_exists($filenames[0][0])) {
		$flag_br=0;
		//while (!feof($handle)) {
		while (!feof($handle) OR (feof($handle) AND $flag_isbuffer==1) ) {
			$eventyear=''; $eventdata='';
			if($flag_isbuffer!=1) {
				$buffer = fgets($handle, 4096);
				$temp=substr($buffer,0,4);
			}
			else {
				$flag_isbuffer=0;
			}

			if($temp>0 AND $temp <2200) { // valid year
				if($temp < $yr){ // we didn't get to the lifespan yet - take next line
					continue;
				}
				else if($temp >= $yr+$step) { // event year is beyond the year/period checked, flag existence of buffer and break out of while loop
					$flag_isbuffer=1;
					//echo "</td></tr>";
					break;
				}
				else if($temp>=$yr AND $temp<$yr+$step) {
					if($flag_br==0) { // first entry in this year/period. if a "rtl" was read before the first text entry make direction rtl
						echo '<div style="direction:'.$eventdir.'">';
					}
					$thisyear='';
					if($step!=1) {
						$thisyear=$temp." ";
					}
					if(substr($buffer,4,1)=='-') {
						$temp2=substr($buffer,5,4);
						if($temp2 >0 AND $temp2 < 2200) {
							$tillyear=$temp2;
							$eventdata="(".__('till')." ".$tillyear.") ".substr($buffer,10);
							if($flag_br==1) { echo "<br>"; }
							echo $thisyear.$eventdata;
							$flag_br=1;
						}
					}
					else {
						$eventdata=substr($buffer,5);
						if($flag_br==1) { echo "<br>"; }
						echo $thisyear.$eventdata;
						$flag_br=1;
					}
				}
			}
			else { // line doesn't start with valid year - take next line
				if(substr($temp,0,3)=="rtl") {  //the timeline file is a rtl file (the word rtl was on one of the first lines in the file)
					$eventdir="rtl";
				}
				continue;
			}

		} // end while loop
		if($flag_br!=0) {
			echo '</div>';
		}
	}
	echo "</td></tr>";
} // end total lifespan loop

echo "</table>";
echo "<br><br><br><br>";
//echo "</div>";

// the following javascript reads height of table and adds a fake div with this height
// so that the joomla page will stretch down to allow for the whole table
if(CMS_SPECIFIC=="Joomla") {
	echo '<script type="text/javascript">';
	echo 'var tabheight = document.getElementById("timetable").offsetHeight;';
	echo 'tabheight += 80;';
	echo 'document.write(\'<div style="height:\' + tabheight + \'px">&nbsp;</div>\');';
	echo 'tabheight = 0;';
	echo '</script>';
}

// END DISPLAY
if(file_exists($filenames[0][0])) {
	fclose($handle);
}
include_once(CMS_ROOTPATH."footer.php");
?>