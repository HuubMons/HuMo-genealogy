<script type="text/javascript"
	src="googlemaps/StyledMarker.js">
</script>

<script type="text/javascript">

var markersArray = [];
var markerContent = "This is a test window";

infoWindow = new google.maps.InfoWindow( { maxWidth: 500} );

google.maps.event.addListener(map, 'click', function() {
	infoWindow.close();
});

function handleMarkerClick(marker, index) {
	return function() {
		infoWindow.setContent(index);
		infoWindow.setZIndex(1000000);
		infoWindow.open(map, marker);
	};
}
function clearOverlays() {
	if (markersArray) {
		for (i in markersArray) {
			markersArray[i].setMap(null);
		}
		markersArray = new Array();
	}
}

<?php
//error_reporting(E_ALL);
global $selected_language;
$location=$dbh->query("SELECT location_id, location_location, location_lat, location_lng FROM humo_location");
while (@$locationDb=$location->fetch(PDO::FETCH_OBJ)){
	//$locarray[$locationDb->location_location][0] = $locationDb->location_location;
	$locarray[$locationDb->location_location][0] = htmlspecialchars($locationDb->location_location);
	$locarray[$locationDb->location_location][1] = $locationDb->location_lat;
	$locarray[$locationDb->location_location][2] = $locationDb->location_lng;
	$locarray[$locationDb->location_location][3] = 0;    // till starting year  (depending on settings)
	$locarray[$locationDb->location_location][4] = 0;    // + 1 interval
	$locarray[$locationDb->location_location][5] = 0;    // + 2 intervals
	$locarray[$locationDb->location_location][6] = 0;    // + 3 intervals
	$locarray[$locationDb->location_location][7] = 0;    // + 4 intervals
	$locarray[$locationDb->location_location][8] = 0;    // + 5 intervals
	$locarray[$locationDb->location_location][9] = 0;    // + 6 intervals
	$locarray[$locationDb->location_location][10] = 0;   // + 7 intervals
	$locarray[$locationDb->location_location][11] = 0;   // + 8 intervals
	$locarray[$locationDb->location_location][12] = 0;   // till today (=2010 and beyond)
	$locarray[$locationDb->location_location][13] = 0;   // all
}
$namesearch_string = '';
if($flag_namesearch!='') {
	$namesearch_string = ' AND (';
	foreach($flag_namesearch as $value) {
		//$namesearch_string .= " pers_lastname = '".$value."' OR ";
		//$namesearch_string .= " totalname = '".$value."' OR ";
		$namesearch_string .= "CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) = '".$value."' OR ";
	}
	$namesearch_string = substr($namesearch_string,0,-3).")"; // take off last "OR "
}

if($flag_desc_search==1 AND $desc_array != '') {
	//for($i=0; $i<count($desc_array); $i++) {
	foreach($desc_array as $value) {
		if($_SESSION['type_birth']==1) {
			$persoon=$dbh->query("SELECT pers_firstname, pers_birth_place, pers_birth_date, pers_bapt_place, pers_bapt_date
				FROM humo_persons WHERE pers_tree_id='".$tree_id."'
				AND pers_gedcomnumber ='".$value."'
				AND (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !=''))");
			@$personDb=$persoon->fetch(PDO::FETCH_OBJ);
		}
		elseif($_SESSION['type_death']==1) {
			//$persoon=$dbh->query("SELECT pers_firstname, pers_death_place, pers_death_date, pers_buried_place, pers_buried_date FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber ='".$value."' AND (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !=''))");
			$persoon=$dbh->query("SELECT pers_firstname, pers_death_place, pers_death_date, pers_buried_place, pers_buried_date
				FROM humo_persons WHERE pers_tree_id='".$tree_id."'
				AND pers_gedcomnumber ='".$value."'
				AND (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !=''))");
			@$personDb=$persoon->fetch(PDO::FETCH_OBJ);
		}
		if($personDb) {
			if($_SESSION['type_birth']==1) {
				$place=$personDb->pers_birth_place;
				$date =$personDb->pers_birth_date;
				if(!$personDb->pers_birth_place AND $personDb->pers_bapt_place) {
					$place=$personDb->pers_bapt_place;
				}
				if(!$personDb->pers_birth_date AND $personDb->pers_bapt_date) {
					$date =$personDb->pers_bapt_date;
				}
			}
			elseif($_SESSION['type_death']==1) {
				$place=$personDb->pers_death_place;
				$date =$personDb->pers_death_date;
				if(!$personDb->pers_death_place AND $personDb->pers_buried_place) {
					$place=$personDb->pers_buried_place;
				}
				if(!$personDb->pers_death_date AND $personDb->pers_buried_date) {
					$date =$personDb->pers_buried_date;
				}
			}
			if(isset($locarray[$place])) { // birthplace exists in location database
				if($date) {
					$year = substr($date,-4);

					if($year > 1 AND $year < $realmin) {  $locarray[$place][3]++; }
					if($year > 1 AND $year < ($realmin+ $step)) {  $locarray[$place][4]++; }
					if($year > 1 AND $year < ($realmin+ (2*$step))) {  $locarray[$place][5]++; }
					if($year > 1 AND $year < ($realmin+ (3*$step))) {  $locarray[$place][6]++; }
					if($year > 1 AND $year < ($realmin+ (4*$step))) {  $locarray[$place][7]++; }
					if($year > 1 AND $year < ($realmin+ (5*$step))) {  $locarray[$place][8]++; }
					if($year > 1 AND $year < ($realmin+ (6*$step))) {  $locarray[$place][9]++; }
					if($year > 1 AND $year < ($realmin+ (7*$step))) {  $locarray[$place][10]++; }
					if($year > 1 AND $year < ($realmin+ (8*$step))) {  $locarray[$place][11]++; }
					if($year > 1 AND $year < 2050) {  $locarray[$place][12]++; }
					$locarray[$place][13]++;  // array of all people incl without birth date
				}
				else {
					$locarray[$place][13]++ ; // array of all people incl without birth date
				}
			}
		}     // end if($personDb)
	}   	// end for
}
else {
	if($_SESSION['type_birth']==1) {
		//$persoon=$dbh->query("SELECT pers_birth_place, pers_birth_date, pers_bapt_place, pers_bapt_date FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !='')) ".$namesearch_string);
		$persoon=$dbh->query("SELECT pers_birth_place, pers_birth_date, pers_bapt_place, pers_bapt_date
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !='')) ".$namesearch_string);
	}
	elseif($_SESSION['type_death']==1) {
		//$persoon=$dbh->query("SELECT pers_death_place, pers_death_date, pers_buried_place, pers_buried_date FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !='')) ".$namesearch_string);
		$persoon=$dbh->query("SELECT pers_death_place, pers_death_date, pers_buried_place, pers_buried_date
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !='')) ".$namesearch_string);
	}
	while (@$personDb=$persoon->fetch(PDO::FETCH_OBJ)){

		if($_SESSION['type_birth']==1) {
			$place=$personDb->pers_birth_place;
			$date =$personDb->pers_birth_date;
			if(!$personDb->pers_birth_place AND $personDb->pers_bapt_place) {
				$place=$personDb->pers_bapt_place;
			}
			if(!$personDb->pers_birth_date AND $personDb->pers_bapt_date) {
				$date =$personDb->pers_bapt_date;
			}
		}
		elseif($_SESSION['type_death']==1) {
			$place=$personDb->pers_death_place;
			$date =$personDb->pers_death_date;
			if(!$personDb->pers_death_place AND $personDb->pers_buried_place) {
				$place=$personDb->pers_buried_place;
			}
			if(!$personDb->pers_death_date AND $personDb->pers_buried_date) {
				$date =$personDb->pers_buried_date;
			}
		}

		if(isset($locarray[$place])) { // birthplace exists in location database
			if($date) {
				$year = substr($date,-4);

				if($year > 1 AND $year < $realmin) {  $locarray[$place][3]++; }
				if($year > 1 AND $year < ($realmin+ $step)) {  $locarray[$place][4]++; }
				if($year > 1 AND $year < ($realmin+ (2*$step))) {  $locarray[$place][5]++; }
				if($year > 1 AND $year < ($realmin+ (3*$step))) {  $locarray[$place][6]++; }
				if($year > 1 AND $year < ($realmin+ (4*$step))) {  $locarray[$place][7]++; }
				if($year > 1 AND $year < ($realmin+ (5*$step))) {  $locarray[$place][8]++; }
				if($year > 1 AND $year < ($realmin+ (6*$step))) {  $locarray[$place][9]++; }
				if($year > 1 AND $year < ($realmin+ (7*$step))) {  $locarray[$place][10]++; }
				if($year > 1 AND $year < ($realmin+ (8*$step))) {  $locarray[$place][11]++; }
				if($year > 1 AND $year < 2050) {  $locarray[$place][12]++; }
				$locarray[$place][13]++;  // array of all people incl without birth date
			}
			else {
				$locarray[$place][13]++ ; // array of all people incl without birth date
			}
		}
	}
}
$nr=0;
echo 'var j = new Array();'; // 1 letter array name to keep download as short as possible!
foreach ($locarray as $key => $value) {
	echo 'j['.$nr.'] = new Array();';
	echo 'j['.$nr.'][0] = "'.$locarray[$key][0].'";';
	echo 'j['.$nr.'][1] = "'.$locarray[$key][1].'";';
	echo 'j['.$nr.'][2] = "'.$locarray[$key][2].'";';
	echo 'j['.$nr.']['.$realmin.'] = "'.$locarray[$key][3].'";';
	echo 'j['.$nr.']['.($realmin+ $step).'] = "'.$locarray[$key][4].'";';
	echo 'j['.$nr.']['.($realmin+ (2*$step)).'] = "'.$locarray[$key][5].'";';
	echo 'j['.$nr.']['.($realmin+ (3*$step)).'] = "'.$locarray[$key][6].'";';
	echo 'j['.$nr.']['.($realmin+ (4*$step)).'] = "'.$locarray[$key][7].'";';
	echo 'j['.$nr.']['.($realmin+ (5*$step)).'] = "'.$locarray[$key][8].'";';
	echo 'j['.$nr.']['.($realmin+ (6*$step)).'] = "'.$locarray[$key][9].'";';
	echo 'j['.$nr.']['.($realmin+ (7*$step)).'] = "'.$locarray[$key][10].'";';
	echo 'j['.$nr.']['.($realmin+ (8*$step)).'] = "'.$locarray[$key][11].'";';
	echo 'j['.$nr.'][2000] = "'.$locarray[$key][12].'";'; // called 2000 but contains up till today
	echo 'j['.$nr.'][3] = "'.$locarray[$key][13].'";';
	echo "\n";
	$nr++;
}

echo 'var namesearch = "";';
$javastring = '';
if($flag_namesearch!='') {   // querystring for multiple family names in popup names
	foreach($flag_namesearch as $value) {
		$javastring .= $value."@";
	}
	$javastring = substr($javastring,0,-1);  // Beck@Willems@Douglas@Smith
	echo " namesearch = '".$javastring."'; ";
}

$_SESSION['desc_array']='';
if($flag_desc_search==1 AND $desc_array !='') {
	$_SESSION['desc_array'] = $desc_array; // for use in namesearch.php
}
else {
	unset($_SESSION['desc_array']);
}

?>

function setcolor(total) {
	var red = "fe2e2e";
	var blue = "2e64fe";
	var green = "2efe2e";
	var yellow = "f7fe2e";
	var cyan = "04b4ae";
	if(total < 10) {return yellow;}
	else if(total < 50) {return green;}
	else if(total < 100) {return blue;}
	else if(total < 10000) {return red;}
	else {return red;}
}
function setmarkersize(total) {
	if(total < 10) {return '0.4';}
	else if(total < 50) {return '0.5';}
	else if(total < 100) {return '0.75';}
	else if(total < 10000) {return '0.9';}
	else {return '1.05';}
}
function setfontsize(total) {
	if(total < 10) {return '12';}
	else if(total < 50) {return '12';}
	else if(total < 100) {return '12';}
	else if(total < 10000) {return '12';}
	else {return '12';}
}

function makeSelection(sel) {

	clearOverlays();

	var max = sel;     // max is used for the "what" and "until" variables for the url_querystring to namesearch.php
	if(sel > 2000) {   // gslider.js returned present year = last step in slider
		sel = 2000;  // sel is used as member in the j array (j[4][sel]). this member is called "2000" for all born till present year
	}

	var what; var until;
	if(sel == 3) {      // 3 flags the "all locations" button (j[i][3])
		what = "all=1"; // for url query string
		<?php echo 'until = "'.__('today ').'";';  ?>
	}
	else {   // years 1550, 1600 .... till today for slider
		what = "max=" + max; // for url querystring
		until = max;         // "until 1850"
	}

	var namestring ='';
	if(namesearch!='') {
	namestring = 'namestring=' + namesearch;
	//namestring = encodeURI(namestring);
	}

	// simulates the php html_entity_decode function otherwise "Delft" is displayed in tooltip as &quot;Delft&quot;
	function convert_html(str){
		var temp=document.createElement("pre");
		temp.innerHTML=str;
		return temp.firstChild.nodeValue;
	}

   	var i;

	for(i = 0; i < j.length; i++) {

		var thislat = parseFloat(j[i][1]);
		var thislng = parseFloat(j[i][2]);
		var thisplace = encodeURI(j[i][0]);
		//a single quote in the name breaks the query string, so we escape it (+ double \\ to escape the \)
		thisplace = thisplace.replace(/'/g,"\\'");  // l'Ile d'Orleans becomes l\'Ile d\'Orleans

		if(j[i][sel] > 0) {   // if 0: this location is not relevant for this period

			var latlng = new google.maps.LatLng(thislat,thislng);
			// convert html entities in tooltip of marker:
			var html_loc = convert_html(j[i][0]); 
			var styleMaker1 = new StyledMarker({styleIcon:new StyledIcon(StyledIconTypes.MARKER,{color:setcolor(j[i][sel]),text:j[i][sel], size:setmarkersize(j[i][sel]),font:setfontsize(j[i][sel])}),title:html_loc,position:latlng,map:map});

			markersArray.push(styleMaker1);

			// wikipedia doesn't search well with "Newcastle, NSW, Australia", so we just take the place name Newcastle
			// Of course there are multiple places with this name but they will appear at the start of the wikipedia page, one click away...
			var place;
			var comma = j[i][0].search(/,/);
			if(comma != -1) { place = j[i][0].substr(0,comma); }
			else { place = j[i][0]; }

			<?php
			// language variables
			echo 'var location ="'.__('Location: ').'";';
			if($_SESSION['type_birth']==1) {
				echo 'var list ="'.__('For a list of persons born here until ').'";';
			}
			elseif($_SESSION['type_death']==1) {
				echo 'var list ="'.__('For a list of all people that died here until ').'";';
			}
			echo 'var click ="'.__(' click here').'";';
			echo 'var readabout ="'.__('Read about this location in ').'";';

			if($selected_language=="hu") {
				echo 'var wikilang="hu";';
			}
			elseif($selected_language=="nl") {
				echo 'var wikilang="nl";';
			}
			elseif($selected_language=="fr") {
				echo 'var wikilang="fr";';
			}
			elseif($selected_language=="de") {
				echo 'var wikilang="de";';
			}
			elseif($selected_language=="fi") {
				echo 'var wikilang="fi";';
			}
			elseif($selected_language=="es") {
				echo 'var wikilang="es";';
			}
			elseif($selected_language=="pt") {
				echo 'var wikilang="pt";';
			}
			elseif($selected_language=="it") {
				echo 'var wikilang="it";';
			}
			elseif($selected_language=="no") {
				echo 'var wikilang="no";';
			}
			elseif($selected_language=="sv") {
				echo 'var wikilang="sv";';
			}
			else {
				echo 'var wikilang="en";';
			}
			?>

			google.maps.event.addListener(styleMaker1, 'click', handleMarkerClick(styleMaker1, "<div>" + location + j[i][0] + "<br>" + readabout + "<a href=\"http://" + wikilang + ".wikipedia.org/wiki/" + place + "\" target=\"blank\"> Wikipedia </a><br><div style=\"display:inline;\" id=\"ajaxlink\" onclick=\"loadurl('googlemaps/namesearch.php?thisplace=" + thisplace + "&amp;" + what + "&amp;" + namestring + "')\">" + list + until + ", <span style=\"color:blue;font-weight:bold\"><a href=\"javascript:void(0)\">" + click + "</a></span><br><br><br><br><div style=\"min-width:370px\"></div></div></div>"));

		}
	}
}

</script>
<?php
?>