<?php
// *** Script by Yossi Beck ***
// *** Function calculate_marriage added by Huub Mons ***
// *** Translated all variables and remarks by Huub Mons ***

class calculate_year_cls{

function search_month($search_date) {
	$month=substr($search_date, -8, 3);
	if ($month=="JAN") {$text=1;}
	else if($month=="FEB") {$text=2;}
	else if($month=="MAR") {$text=3;}
	else if($month=="APR") {$text=4;}
	else if($month=="MAY") {$text=5;}
	else if($month=="JUN") {$text=6;}
	else if($month=="JUL") {$text=7;}
	else if($month=="AUG") {$text=8;}
	else if($month=="SEP") {$text=9;}
	else if($month=="OCT") {$text=10;}
	else if($month=="NOV") {$text=11;}
	else if($month=="DEC") {$text=12;}
	else {$text=null;}
	return($text);
}

function search_day($search_date) {
	$day="";
	if (strlen($search_date)==11) {    // 12 sep 2002 or 08 sep 2002
		$day=substr($search_date, -11, 2);
		if(substr($day,0,1)=="0") {   // 08 aug 2002
			$day=substr($day,1,1);
		}
	}
	if (strlen($search_date)==10) {    // 8 aug 2002
		$day=substr($search_date, -10, 1);
	}
	if ($day) {
		$day=(int)$day;
	}
	if ($day>0 AND $day<32) {}
	else { $day=null; }
	return($day);
}

function search_year($search_date) {
	$year=substr($search_date,-4, 4);
	if ($year < 2100 AND $year > 0) {}
	else { $year=null;}
	return ($year);
}

function process_special_text($date1, $date2, $baptism) {
	global $language;
	$date1_remark=null; // pers_birth_date
	$date2_remark=null; // pers_death_date
	$text=null;

	if (strlen(stristr($date1,"BEF"))>0){$date1_remark="Bef";}
	else if (strlen(stristr($date1,"AFT"))>0){$date1_remark="Aft";}
	else if (strlen(stristr($date1,"ABT"))>0){$date1_remark="Abt";}
	else if (strlen(stristr($date1,"EST"))>0){$date1_remark="Abt";}  // Calculate Abt, Est and Cal as the same text
	else if (strlen(stristr($date1,"CAL"))>0){$date1_remark="Abt";}  // calculate Abt, Est and Cal as the same text
	else if (strlen(stristr($date1,"BET"))>0){$date1_remark="Bet";}  // Don't calculate BET text
	else {$date1_remark=null;}

	if (strlen(stristr($date2,"BEF"))>0){$date2_remark="Bef";}
	else if (strlen(stristr($date2,"AFT"))>0){$date2_remark="Aft";}
	else if (strlen(stristr($date2,"ABT"))>0){$date2_remark="Abt";}
	else if (strlen(stristr($date2,"EST"))>0){$date2_remark="Abt";}  // Calculate Abt, Est and Cal as the same text
	else if (strlen(stristr($date2,"CAL"))>0){$date2_remark="Abt";}  // Calculate Abt, Est and Cal as the same text
	else if (strlen(stristr($date2,"BET"))>0){$date2_remark="Bet";}  // Don't calculate BET text
	else {$date2_remark=null;}

	if($date1_remark OR $date2_remark) { // there is at least 1 remark
		if($date1_remark==="Bef") {
			if($date2_remark==="Bef" OR $date2_remark==="Abt") { $text=-1;}  // Can't calculate age. flag -1
			else if ($date2_remark===null OR $date2_remark==="Aft") {  $text=__('at least')." "; }
		}
		else if($date1_remark==="Aft") {
			if($date2_remark==="Aft" OR $date2_remark==="Abt") {  $text=-1;}  // Can't calculate age. flag -1
			else if ($date2_remark===null OR $date2_remark==="Bef") {  $text=__('at most')." "; }
		}
		else if($date1_remark==="Abt") {
			if($date2_remark==="Bef" OR $date2_remark==="Aft") {  $text=-1;}  // Can't calculate age. flag -1
			else if ($date2_remark===null OR $date2_remark==="Abt") {  $text=__('approximately')." "; }
		}
		else if($date1_remark===null) {
			if($date2_remark==="Bef") { $text=__('at most')." "; }
			else if ($date2_remark==="Aft") {  $text=__('at least')." "; }
			else if ($date2_remark==="Abt") {  $text=__('approximately')." "; }
		}
		else if($date1_remark==="Bet" OR $date2_remark==="Bet") {  $text=-1;}   // Don't calculate age if text = BET, sorry...
	}
	else {
		// No remarks
		$text=null;
	}

	// *** If calculated with baptism, always use about ***
	if ($baptism==true AND $text!=-1){ $text=__('approximately')." "; }

	return($text);
}

// *** $age_check=false/true. true=show short age text. ***
function calculate_age($baptism_date, $birth_date, $death_date, $age_check=false) {	global $language;

	$birth_date=strtoupper($birth_date);

	// *** Also calculate age if only baptism and death date is known ***
	$baptism=false;
	if ($birth_date==''){
		$baptism=true;
		$birth_date=$baptism_date;
	}

	// *** Remove gregorian date from date ***
	//		convert "1 Jan 1634/5" to "1 Jan 1634".
	//		and convert "25 Dec 1600/4 Jan 1601" to "25 Dec 1600".
	if (strpos($birth_date,'/')>0){
		$temp=explode ('/',$birth_date);
		$birth_date=$temp[0];
		$baptism=true;
	}
	if (strpos($death_date,'/')>0){
		$temp=explode ('/',$death_date);
		$death_date=$temp[0];
		$baptism=true;
	}

	// *** Calculate age by living person ***
	if ($death_date==''){ $death_date=date("j M Y"); }
	$death_date=strtoupper($death_date);
	$calculated_age='';
	$age="";

	if (($birth_year=$this->search_year($birth_date)) AND ($death_year=$this->search_year($death_date))) { // There must be 2 years....

		// Check for EST AFT ABT etc. If calculation is not possible: $special_text -1
		$special_text=$this->process_special_text($birth_date,$death_date,$baptism);

		if($birth_year==$death_year) { // born 1850 - death 1850
			if(!$special_text) {
				$age=__('under 1 year old');
			}
			else {
				if($special_text!=-1) {
					//print "BOOGIE";
					$age=$special_text.__('1 years');
				}
			}
		}
		else { // Month is needed for better calculation
			if (($birth_month=$this->search_month($birth_date))  AND ($death_month=$this->search_month($death_date))) { // 2 months
				if($birth_month==$death_month) { // same month: we need day for exact age
					if (($birth_day=$this->search_day($birth_date)) AND ($death_day=$this->search_day($death_date))) { // 2 days
						// *** Show "about" is calculated with "baptism" ***
						if ($special_text){ $age=$special_text; }
						if (($birth_day==$death_day) OR ($birth_day < $death_day)) {
							$calculated_age=$death_year - $birth_year;
							$age.=$calculated_age." ".__('years');
						}
						else if ($birth_day > $death_day) {
							$calculated_age=($death_year - $birth_year)-1;
							$age.=$calculated_age." ".__('years');
						}
					}
					else { // Day is missing in 1 or 2 date's
						if(!$special_text) {
							$calculated_age=($death_year - $birth_year)-1;
							$age=$calculated_age." ".__('or')." ".($death_year - $birth_year)." ".__('years');
						}
						else {
							if($special_text!=-1) {
								$calculated_age=$death_year - $birth_year;
								$age=$special_text.$calculated_age." ".__('years');;
							}
						}
					}
				}
				else if ($birth_month < $death_month) { // No day needed
					if($special_text!=-1) {
						$calculated_age=$death_year - $birth_year;
						$age=$special_text.$calculated_age." ".__('years');
					}
				}
				else if ($birth_month > $death_month) { // No day needed
					if($special_text!=-1) {
						$calculated_age=($death_year - $birth_year)-1;
						$age=$special_text.$calculated_age." ".__('years');
					}
				}
			}
			else { // Month is missing in 1 or 2 years.
				if(!$special_text) { // no EST ABT AFT BEF
					$calculated_age=($death_year - $birth_year)-1;
					$age=$calculated_age." ".__('or')." ".($death_year - $birth_year)." ".__('years');
				}
				else {
					if($special_text!=-1) {
						$calculated_age=$death_year - $birth_year;
						$age=$special_text.$calculated_age." ".__('years');
					}
					// "about 45 or 46 year", "at most 45 or 46 year" is written as: "about 46 year" or at most 46 year
				}
			}
		}
		if($age) { $age=", ".$age;}

		if ($calculated_age>100){ $age=''; }

		if ($age_check==true){ $age=$calculated_age; }
		return($age);
	}
}

// *** $age_check=false/true. true=show shortened age ***
// *** Function calculate_marriage added by Huub Mons ***
function calculate_marriage($church_marr_date, $marr_date, $end_date, $age_check=false) {	global $language, $selected_language;

	$marr_date=strtoupper($marr_date);

	// *** Also calculate marriage if only marriage date is known ***
	$baptism=false;
	if ($marr_date==''){
		$baptism=true;
		$marr_date=$church_marr_date;
	}

	// *** Remove gregorian date from date ***
	//		convert "1 Jan 1634/5" to "1 Jan 1634".
	//		and convert "25 Dec 1600/4 Jan 1601" to "25 Dec 1600".
	if (strpos($marr_date,'/')>0){
		$temp=explode('/',$marr_date);
		$marr_date=$temp[0];
		$baptism=true;
	}
	if (strpos($end_date,'/')>0){
		$temp=explode('/',$end_date);
		$end_date=$temp[0];
		$baptism=true;
	}

	// *** Calculate age by living persons ***
	if ($end_date==''){ $end_date=date("j M Y"); }
	$end_date=strtoupper($end_date);
	$calculated_age=''; // *** Calculated age ***
	$age="";

	if (($start_year=$this->search_year($marr_date)) AND ($end_year=$this->search_year($end_date))) { // there must be 2 dates...
		// Check for EST AFT ABT
		$special_text=$this->process_special_text($marr_date,$end_date,$baptism);

		if($start_year==$end_year) { // start 1850 - end 1850
			if(!$special_text) {
				// Not in use in marriage calulation
				//$age=__('under 1 year old');
			}
			else {
				if($special_text!=-1) {
					// Not in use in marriage calulation
					//$age=$special_text.__('1 years');
				}
			}
		}
		else { // Months are not the same: we need month for exact calculation
			if (($start_month=$this->search_month($marr_date))  AND ($end_month=$this->search_month($end_date))) { // 2 month
				if($start_month==$end_month) { // same month, we need day for exact calculation
					if (($start_day=$this->search_day($marr_date)) AND ($end_day=$this->search_day($end_date))) { // 2 days
						if ($special_text){ $age=$special_text; }
						if (($start_day==$end_day) OR ($start_day < $end_day)) {
							$calculated_age=$end_year - $start_year;
							$age.=$calculated_age." ".__('years');
						}
						else if ($start_day > $end_day) {
							$calculated_age=($end_year - $start_year)-1;
							$age.=$calculated_age." ".__('years');
						}
					}
					else { // Day is missing in 1 or 2 dates
						if(!$special_text) {
							$calculated_age=($end_year - $start_year)-1;
							$age=$calculated_age." ".__('or')." ".($end_year - $start_year)." ".__('years');
						}
						else {
							if($special_text!=-1) {
								$calculated_age=$end_year - $start_year;
								$age=$special_text.$calculated_age." ".__('years');;
							}
						}
					}
				}
				else if ($start_month < $end_month) { // no day needed
					if($special_text!=-1) {
						$calculated_age=$end_year - $start_year;
						$age=$special_text.$calculated_age." ".__('years');
					}
				}
				else if ($start_month > $end_month) { // no day needed
					if($special_text!=-1) {
						$calculated_age=($end_year - $start_year)-1;
						$age=$special_text.$calculated_age." ".__('years');
					}
				}
			}
			else { // Month is missing in 1 or 2 years
				if(!$special_text) { // no EST ABT AFT BEF
					$calculated_age=($end_year - $start_year)-1;
					$age=$calculated_age." ".__('or')." ".($end_year - $start_year)." ".__('years');
				}
				else { //
					if($special_text!=-1) {
						$calculated_age=$end_year - $start_year;
						$age=$special_text.$calculated_age." ".__('years');
					}
				}
			}
		}
		//if($age) {
		//	$age=" (".$age.' '.strtolower(__('Married')).')';
		//}
		// *** Maybe something like this code is better: $age=printf(__('married %d'), $age);
		if($age) {
			if($selected_language=="sv" OR $selected_language=="no" OR $selected_language=="da") {
				$age=" (".strtolower(__('Married')).' '.$age.')';
			}
			else {
				$age=" (".$age.' '.strtolower(__('Married')).')';
			}
		}

		if ($calculated_age>80){ $age=''; }

		// Not in use in marriage calulation
		//if ($age_check==true){ $age=$calculated_age; }

		return($age);
	}
}

} // *** End of class ***
?>