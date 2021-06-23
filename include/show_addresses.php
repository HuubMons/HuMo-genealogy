<?php 
// *** 25-12-2020: New combined module addresses and shared addresses -> Residences ***
// *** Residences/addresses (was: extended addresses for HuMo-genealogy and dutch Haza-data program (Haza-data plus version) ***
function show_addresses($connect_kind,$connect_sub_kind,$connect_connect_id){
	global $dbh,$db_functions,$user,$uri_path;
	global $temp,$templ_person,$templ_relation; // *** PDF export ***
	global $tree_id;

	$text='';
	$address_nr=0;

	// *** Search for all connected addresses ***
	$connect_sql = $db_functions->get_addresses($connect_kind,$connect_sub_kind,$connect_connect_id);
	$nr_addresses=count($connect_sql);
	foreach ($connect_sql as $connectDb){
		$address_nr++;
		if ($address_nr=='1'){
			//if ($process_text){
			//	if ($family_expanded==true){ $text.='<br>'; } else{ $text.='. '; }
			//}
			if ($nr_addresses=='1'){
				$residence=__('residence');
				if ($connect_kind=='person') $templ_person["address_start"]=ucfirst(__('residence')).': ';
				if ($connect_kind=='family') $templ_relation["address_start"]=ucfirst(__('residence')).': ';
			}
			else{
				$residence=__('residences');
				if ($connect_kind=='person') $templ_person["address_start"]=ucfirst(__('residences')).': ';
				if ($connect_kind=='family') $templ_relation["address_start"]=ucfirst(__('residences')).': ';
			}

			if($temp) { if ($connect_kind=='person') $templ_person[$temp].=". "; }
			//if($temp) { if ($connect_kind=='family') $templ_relation[$temp].=". "; }

			//$templ_person["address_exist"]=ucfirst($residence).': ';
			//$temp="address_exist";
			$text.='<b>'.ucfirst($residence).':</b> ';
		}
		if ($address_nr>1){
			$text.=', ';
			if($temp) { if ($connect_kind=='person') $templ_person[$temp].=", "; }
			if($temp) { if ($connect_kind=='family') $templ_relation[$temp].=", "; }
		}

		// *** Show link to shared address ***
		if ($connectDb->address_shared=='1')
			$text.='<a href="'.$uri_path.'address.php?gedcomnumber='.$connectDb->connect_item_id.'">';

			//if ($user['group_addresses']=='j' AND $connectDb->address_address){
			if ($user['group_living_place']=='j' AND $connectDb->address_address){
				$text.=' '.$connectDb->address_address.' ';

				// *** PDF export ***
				if ($connect_kind=='person'){
					$templ_person["address_address".$address_nr]=$connectDb->address_address;
					if($templ_person["address_address".$address_nr]!='') $temp="address_address".$address_nr;
				}
				if ($connect_kind=='family'){
					$templ_relation["address_address".$address_nr]=$connectDb->address_address;
					if($templ_relation["address_address".$address_nr]!='') $temp="address_address".$address_nr;
				}
			}

			if ($connectDb->address_zip){
				$text.=' '.$connectDb->address_zip.' ';

				// *** PDF export ***
				if ($connect_kind=='person'){
					if (isset($templ_person["address_address".$address_nr]))
						$templ_person["address_address".$address_nr].=' '.$connectDb->address_zip;
					else
						$templ_person["address_address".$address_nr]=$connectDb->address_zip;
					if($templ_person["address_address".$address_nr]!='') $temp="address_address".$address_nr;
				}
				if ($connect_kind=='family'){
					if (isset($templ_relation["address_address".$address_nr]))
						$templ_relation["address_address".$address_nr].=' '.$connectDb->address_zip;
					else
						$templ_relation["address_address".$address_nr]=$connectDb->address_zip;
					if($templ_relation["address_address".$address_nr]!='') $temp="address_address".$address_nr;
				}
			}

			$text.=$connectDb->address_place;

			// *** PDF export ***
			if ($connect_kind=='person'){
				if (isset($templ_person["address_address".$address_nr]))
					$templ_person["address_address".$address_nr].=' '.$connectDb->address_place;
				else
					$templ_person["address_address".$address_nr]=$connectDb->address_place;
				// *** Add date ***
				if ($connectDb->connect_date)
					$templ_person["address_address".$address_nr].= ' ('.date_place($connectDb->connect_date,'').')';
				if($templ_person["address_address".$address_nr]!='') $temp="address_address".$address_nr;
			}
			if ($connect_kind=='family'){
				if (isset($templ_relation["address_address".$address_nr]))
					$templ_relation["address_address".$address_nr].=' '.$connectDb->address_place;
				else
					$templ_relation["address_address".$address_nr]=$connectDb->address_place;
				// *** Add date ***
				if ($connectDb->connect_date) $templ_relation["address_address".$address_nr].= ' ('.date_place($connectDb->connect_date,'').')';
				if($templ_relation["address_address".$address_nr]!='') $temp="address_address".$address_nr;
			}

		// *** END OF: Show link to adres if street is used ***
		if ($connectDb->address_shared=='1')
			$text.="</a>";

		if ($connectDb->address_phone){
			$text.=', '.$connectDb->address_phone;
//PDF
		}

		// *** Don't use address_date. Using connect_date for all adresses ***
		//if ($connectDb->address_date){
		//	//$text.=date_place($connectDb->address_date,'').' ';
		//	$text.=' ('.date_place($connectDb->address_date,'').')';
		//	// default, without place, place is processed later.
		//	$templ_person["address_date".$address_nr]=' ('.date_place($connectDb->address_date,'').')';
		//	$temp="address_date".$address_nr;
		//}
		if ($connectDb->connect_date){
			//$text.=date_place($connectDb->address_date,'').' ';
			$text.=' ('.date_place($connectDb->connect_date,'').')';
			// default, without place, place is processed later.
//				$templ_person["address_date".$address_nr]=' ('.date_place($connectDb->connect_date,'').')';
//				$temp="address_date".$address_nr;
		}

		// *** Address text ***
		if ($connectDb->address_text) {
			$work_text=process_text($connectDb->address_text);
			if ($work_text){
				//if($temp) { $templ_person[$temp].=", "; }
				//$templ_person["address_text".$address_nr]=$work_text;
				//$temp="address_text".$address_nr;
				//$text.=', '.$work_text;

				// *** PDF export ***
				if ($connect_kind=='person'){
					$templ_person["address_text".$address_nr]=' '.$connectDb->address_text;
					if($templ_person["address_text".$address_nr]!='') $temp="address_text".$address_nr;
				}
				if ($connect_kind=='family'){
					$templ_relation["address_text".$address_nr]=' '.$connectDb->address_text;
					if($templ_relation["address_text".$address_nr]!='') $temp="address_text".$address_nr;
				}

				$text.=' '.$work_text;
			}
		}

		if ($connect_kind=='person'){
			$source=show_sources2("person","pers_address_source",$connectDb->address_gedcomnr);
		}
		else{
			$source=show_sources2("family","fam_address_source",$connectDb->address_gedcomnr);
		}
		if ($source){
			// *** PDF export ***
			if ($connect_kind=='person'){
				//$templ_person["address_source".$address_nr]=' '.$source;
				$templ_person["address_source".$address_nr]=$source;

				// *** Extra item, so it's possible to add a comma or space ***
				if($templ_person["address_source".$address_nr]!=''){
					$templ_person["address_add"]='';
					$temp="address_add";
				}
			}
			if ($connect_kind=='family'){
				//$templ_relation["address_source"]=$connectDb->address_address.' '.$connectDb->address_source;
				$templ_relation["address_source".$address_nr]=$source;

				// *** Extra item, so it's possible to add a comma or space ***
				$templ_relation["address_add"]='';
				$temp="address_add";
			}

			$text.=$source;
		}

	}
	return $text;
}
?>