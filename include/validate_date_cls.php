<?php
// *** Script by Yossi Beck ***
// finds the following invalid dates:
// - impossible dates (31 apr 2003, 30 feb 2003, 29 feb 2003 (not a leap year!), 43 mar 2003)
// - years in the future (31 apr 2020)
// - partial dates (1 feb,  mar)
// - invalid GEDCOM year entries ( 1820? )
// - invalid GEDCOM month entries (12 april 2003, 23 feb. 2003, 12 december 2003)
// - prefixes before last (or only) date that are not BEF, AFT, ABT, EST, CAL, INT, AND, TO or valid combinations like EST ABT
// - lack of BET or FROM in string if AND or TO (respectively) where found
// - invalid gedcom entries such as use of dash (1845-1847) or slash (23/4/1990) etc
// - and of course any junk that was entered in the date field instead of elsewhere...   ;-)
// - does not (yet) validate the first date in a "BET ... AND ..." or "FROM ... TO ..." two-date string. Maybe we'll add that later

class validate_date_cls{

function check_date($date) {
	$year = $this->check_year($date);
	if($year === null) { return null; }

	if($year > 999) { $strlen=4; }
	elseif($year >99) { $strlen=3; }
	else return null;
	
	if(strlen($date) == $strlen) return "finished"; // date contains just the year, no sense checking a month
	elseif ($this->check_month($date) === null) { return null; }

	if(strlen($date) == $strlen+4) return "finished"; // date contains just the month and year, no sense checking a day
	elseif ($this->check_day($date) === null) { return null; }

	return 1; 
}

function check_month($date) {
	$year = $this->check_year($date);
	if($year > 999) {
		$month=substr($date, -8, 3);   // FEB 1950
		$strlen=4;
	}
	elseif($year >99) {
		$month=substr($date, -7, 3);   // FEB 834
		$strlen=3;
	}
 
 	if( $month=="JAN" OR $month=="FEB" OR $month=="MAR" OR $month=="APR" OR $month=="MAY" OR $month=="JUN"
	 	OR $month=="JUL" OR $month=="AUG" OR $month=="SEP" OR $month=="OCT" OR $month=="NOV" OR $month=="DEC") {
 	 	 	return "month".$month; // flags valid month
 	 	}
 	elseif ($month=="EST" OR $month=="CAL") { 
			if(strlen($date) > ($strlen + 4)) return null; // EST and CAL should not have anything in front of them!
 	 	 	else {return $month; } // flags valid EST or CAL with nothing in front
 	 	}
 	elseif ($month=="BEF" OR $month=="AFT" OR $month=="ABT" OR $month=="INT") { 			
			if(strlen($date)== $strlen+8 AND (substr($date,0,3) == "EST" OR substr($date,0,3)=="CAL")) return $month; //valid "EST ABT" etc.
			elseif(strlen($date) > ($strlen + 4)) return null; // these texts should not have anything in front of them except EST or CAL!
 	 	 	else {return $month; } // flags valid 3 letter text with nothing in front
 	 	}
 	elseif ($month==" TO") { 
			if(substr($date,0,4) != "FROM") return null; // TO must have FROM up front!
			else { return " TO"; } 
	}
 	elseif ($month=="AND") { 
			if(substr($date,0,3) != "BET") return null; // AND must have BET up front
			else {return "AND"; }  
	}
	else { return null; } // if we found "BET" or "FROM" that is also invalid - they can't occur before the last date!
 		 

}

function check_day($date) {
	$year = $this->check_year($date);
	$month = $this->check_month($date);
	if($year >999) { $strlen=10; }
	elseif($year >99) { $strlen=9; }
 
	$day_len=1; // to be added to strlen later. if day is "8" (and not "12" or "08") $day_len will be set to 0

		if(substr($month,0,5) == "month") {
			$day="";
			if (strlen($date) > $strlen) {    // 12 sep 2002 or 08 sep 2002 or ABT 8 sep 2002 or ABT sep 2002
			 	$day=substr($date, -($strlen+1), 2); // gets "12" or "08" or " 8" or "BT" in above examples
			 	if(substr($day,0,1)=="0") {   // 08 aug 2002
			 		$day=substr($day,1,1); // turns $day from "08" into "8"
			 	}
				elseif(substr($day,0,1)==" ") {
					$day_len=0;
				}
			}
			elseif (strlen($date)==$strlen) {    // 8 aug 2002
			 	$day=substr($date, -($strlen), 1); // gets "8"
				$day_len=0;
			}

			if ($day) { 
				if(is_numeric($day)) { //in above examples will accept "12", "8", " 8" but will not accept "BT"
					$day=(int)$day; 
					// check if max day fits month
					$max=31; 
					if(substr($month,5,3)=="FEB") {  // check for leap year
						if($year%400==0) $max=29;
						elseif($year%100==0) $max=28;
						elseif($year%4==0) $max=29;
						else $max=28;
					}
					elseif(substr($month,5,3)=="APR" OR substr($month,5)=="JUN" 
							OR substr($month,5)=="SEP" OR substr($month,5)=="NOV") { $max=30; }

					if ($day>0 AND $day<=$max) { 
						$strlen = $strlen + $day_len;
						if($strlen==strlen($date)) { // nothing before the day digit(s)
							return $day; 
						}
					}
					else return null;
				}
				else { // not numeric for ex. in "ABT FEB 1950", $day will be "BT". We have a case of a month with a prefix
					$strlen = $strlen-2; // we have to search back from beginning of month name
				}
			}

			if (strlen($date) > $strlen) {  // now search for text in front of day or month: BEF 10 FEB 1935 or BEF FEB 1935
			 	$text=substr($date, -($strlen+4), 3);
 	 	 	 	if ($text=="EST" OR $text=="CAL") { 
					if(strlen($date) > ($strlen + 4)) return null; // EST and CAL should not have anything in front of them!
 	 	 			else {return $text; } // flags valid EST or CAL has nothing in front
 	 			}
 	 	 	 	elseif ($text=="BEF" OR $text=="AFT" OR $text=="ABT" OR $text=="INT") { 
					if(strlen($date)== $strlen+8 AND (substr($date,0,3) == "EST" OR substr($date,0,3)=="CAL")) return $text; 
					//valid "EST ABT" etc.
					elseif(strlen($date) > ($strlen + 4)) return null; 
					// these texts should not have anything in front except CAL or EST!
 	 	 			else {return $text; } 
					// flags valid 3 letter text has nothing in front 
 	 			}
 				elseif ($text==" TO") { 
					if(substr($date,0,4) != "FROM") return null; // TO must have FROM up front
					else { return " TO"; } // must have text in front of it (FROM ... TO ...)
				}
 				elseif ($text=="AND") { 
					if(substr($date,0,3) != "BET") return null; // AND must have BET in front!
					else {return "AND"; }  
				} 
			}
			return null;
		}
		elseif($month==" TO") {
			if(substr($date,0,5) == "FROM ") return $month;
			else return null;
		}
		elseif($month=="AND") {
			if(substr($date,0,4) == "BET ") return $month;
			else return null;
		}
		else return 1;
}

function check_year($date) {
	$year=substr($date,-4, 4);  
	if (!is_numeric($year) OR $year > date("Y") OR $year<100) { return null; }
	else { return $year;}
}


} // *** End of class ***
?>