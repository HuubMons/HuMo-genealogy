<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }
//error_reporting(E_ALL);
@set_time_limit(3000);

echo '<h1 align=center>'.__('Google maps administration').'</h1>';
//echo '<table style="width:800px; margin-left:auto; margin-right:auto;" class="humo" border="1">';
echo '<table class="humo standard" border="1" style="width:800px;">';

if(isset($_POST['makedatabase'])) {  // the user decided to add locations to the location database
	//echo '<tr bgcolor="green"><th><font color="white">'.__('Creating/ updating database').'</font></th></tr>';
	echo '<tr class="table_header"><th>'.__('Creating/ updating database').'</th>';

	echo '<tr><td>';
	$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
		if(!$temp->rowCount()) {
		// no database exists - so create it
		// (Re)create a location table "humo_location" for each tree (humo1_ , humo2_ etc)
		// It has 4 columns:
		//     1. id
		//     2. name of location
		//     3. latitude as received from a geocode call
		//     4. longitude as received from a geocode call
		//     5. status: what is this location used for: birth/bapt/death/buried, and by which tree(s)

		echo '<br>'.__('Creating location database').'<br>';

		$locationtbl="CREATE TABLE humo_location (
			location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			location_location VARCHAR(100) CHARACTER SET utf8,
			location_lat FLOAT(10,6),
			location_lng FLOAT(10,6),
			location_status TEXT DEFAULT ''
		)";
		$dbh->query($locationtbl);
	}
	$count_parsed = 0;
	$map_notfound_array = array();
	$map_count_found=0;
	$map_count_notfound=0;
	$flag_stop=0;

	echo __('Started adding to data base.').'<br>';
	echo __('Starting time').': '.date('G:i:s').'<br><br>';
	sleep(1); // make sure this gets printed before the next is executed

	foreach($_SESSION['add_locations'] as $value) {
		$count_parsed++;

		$loc=urlencode($value);
		$jsonurl = "http://maps.googleapis.com/maps/api/geocode/json?address=".$loc."&sensor=false";
		@$json = file_get_contents($jsonurl,0,null,null);
		// file_get_contents won't work if "allow_url_fopen" is disabled by host for security considerations.
		// in that case try the PHP "curl" extension that is installed on most hosts (but we still check...)
		if(!$json) {
			if(extension_loaded('curl')) {
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $jsonurl);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				$json = curl_exec($ch);
				curl_close($ch);
			}
			else {
				echo __('<b>A location database could not be created.</b>
<p>This could mean that the PHP function "allow_url_fopen" was disabled by your webhost for security considerations and the PHP extension "curl" (which is an alternative to allow_url_fopen) is not loaded on the server.
<p>You could contact your webhost and request to either have "allow_url_fopen" enabled or "curl" loaded.');
				exit();
			}
		}

		echo '*';  // show progress by simple progress bar of *******
		if($count_parsed % 100 == 0) { echo '<br>'; }

		$json_output = json_decode($json, true);
		if ($json_output['status']=="OK") {
			$map_count_found++;

			$lat=$json_output['results'][0]['geometry']['location']['lat'];
			$lng=$json_output['results'][0]['geometry']['location']['lng'];
			$dbh->query("INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES('".safe_text($value)."', '".$lat."', '".$lng."') ");

			sleep(1);  // crucial, otherwise google kicks you out after a few queries
		}
		elseif ($json_output['status']=="ZERO_RESULTS") { // store locations that were not found by google geocoding
			$map_notfound_array[]= $json_output['status'].' - '.$value;
			$map_count_notfound++;
			sleep(1);  // crucial, otherwise google kicks you out after a few queries
		}
		elseif ($json_output['status']=="OVER_QUERY_LIMIT") {
			$flag_stop=1;
			break;  // out of foreach
		}
		else {
			// not a good situation. this is either "REQUEST_DENIED" which shouldn't happen,
			// or "INVALID_REQUEST" that can't really happen, because this code is perfect....   ;-)
		}

	} // end of foreach

	if($flag_stop==0) {
		echo '<p style="color:red;font-size:120%"><b> '.__('Finished updating geo-location database').'<b></p>';
		echo __('Finish time').': '.date('G:i:s').'<br><br>';
		echo $map_count_found.' '.__('locations were successfully mapped.').' <br><br>';

		if($map_notfound_array) { // some locations were not found by geocoding
			printf(__('The following %s locations were not recognized by google and were not added to the database:'), $map_count_notfound);
			echo '<br>';
			foreach($map_notfound_array as $value) {
				echo $value."<br>";
			}
		}
	}
	else {  // the process was interrupted because of OVER_QUERY_LIMIT. Explain to the admin!
		echo '<p style="color:red;font-size:120%"><b> '.__('The process was interrupted because Google limits to maximum 2500 queries within one day (counting is reset at midnight PST, which is 08:00 AM GMT)').'<b></p>';
		printf(__('In total %1$d out of %2$d new locations were passed for query to Google.'), $count_parsed, count($_SESSION['add_locations']));
		echo __('Tomorrow you can run this process again to add the locations that were not passed for geocoding today.');
		echo '<p>'.$map_count_found.' '.__('locations were recognized by geocoding and have been saved in the database.').'<br><br>';

		if($map_notfound_array) { // some locations were not found by geocoding
			printf(__('The following  %d locations were passed for query, but were not found:'), $map_count_notfound);
			echo '<br>';
			foreach($map_notfound_array as $value) {
				echo $value."<br>";
			}
		}
	}

	// refresh the location_status column
	$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
	if($temp->rowCount() > 0) {
		refresh_status();  // see function at end of script
	}

	unset($_SESSION['add_locations']);

	echo '</td></tr>';
	echo '<br><form action="'.$_SERVER['PHP_SELF'].'?page=google_maps" method="post">';
	echo '<input type="submit" style="font-size:14px" value="'.__('Back').'">';
	echo '<br></form><br>';

}  // end - if add to database

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MAIN SCREEN on entry into the google maps option in the admin menu

else {  // main screen

	if(isset($_POST['deletedatabase'])) {
		$dbh->query("DROP TABLE humo_location");
	}

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CREATE/UPDATE GEOLOCATION DATABASE ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	//echo '<tr bgcolor="green"><th><font color="white">'.__('Create or update geolocation database').'</font></th></tr>';
	echo '<tr class="table_header"><th>'.__('Create or update geolocation database').'</th>';

	echo '<tr><td>';
	if(isset($_POST['check_new'])) { // the "Check" button was pressed
	
		$unionstring='';
		//$tree_prefix_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		//$tree_prefix_result = $dbh->query($tree_prefix_sql);

		//while ($tree_prefixDb=$tree_prefix_result->fetch(PDO::FETCH_OBJ)){
			//$unionstring .= "SELECT pers_birth_place FROM humo_persons WHERE pers_tree_id='".$tree_id."' UNION
			//SELECT pers_bapt_place  FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_birth_place = '' UNION
			//SELECT pers_death_place FROM humo_persons WHERE pers_tree_id='".$tree_id."' UNION
			//SELECT pers_buried_place  FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_death_place = '' UNION ";
			//	// (only take bapt place if no birth place and only take burial place if no death place)
			$unionstring .= "SELECT pers_birth_place FROM humo_persons
				UNION SELECT pers_bapt_place FROM humo_persons WHERE pers_birth_place = ''
				UNION SELECT pers_death_place FROM humo_persons
				UNION SELECT pers_buried_place FROM humo_persons WHERE pers_death_place = ''";
				// (only take bapt place if no birth place and only take burial place if no death place)
		//}

		//$unionstring = substr($unionstring,0,-7); // take off last " UNION "

		// from here on we can use only "pers_birth_place", since the UNION puts also all other locations under pers_birth_place
		$map_person=$dbh->query("SELECT pers_birth_place, count(*) AS quantity
			FROM (".$unionstring.") AS x GROUP BY pers_birth_place ");

		$add_locations = array();

		while (@$personDb=$map_person->fetch(PDO::FETCH_OBJ)){
			$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
			if($temp->rowCount() > 0) {
				// there is a database - see if the location already exists and if so - continue with a next loop
				$location=$dbh->query("SELECT location_location FROM humo_location");
				while (@$locationDb=$location->fetch(PDO::FETCH_OBJ)){
					if($locationDb->location_location == $personDb->pers_birth_place) {
						continue 2;  //continue the outer while loop
					}
				}
			}
			// add the new location to an array for use if the user presses YES
			if($personDb->pers_birth_place) { $add_locations[] = $personDb->pers_birth_place; }
		}

		//echo 'Calculating......<br><br>'; // with a large existing data base and large number of locations to check this can take a second or two...
		if (!$add_locations) {
			echo '<p>'.__('No new locations were found to add to the database').'</p>';
		}

		else {
			$_SESSION['add_locations']=$add_locations;
			$new_locations = count($add_locations);
			$map_totalsecs = $new_locations * 1.25;
			$map_mins = floor($map_totalsecs / 60);
			$map_secs = $map_totalsecs % 60;
			printf(__('There are %s new unique birth/ death locations to add to the database.'), $new_locations);
			echo '<br>';
			printf(__('This will take approximately <b>%1$d minutes and %2$d seconds.</b>'), $map_mins, $map_secs);
			echo '<br>';
			echo __('Do you wish to add these locations to the database database now?').'<br>';
			echo '<form action="'.$_SERVER['PHP_SELF'].'?page=google_maps" method="post">';
			echo '<input type="submit" style="font-size:14px" value="'.__('YES').'" name="makedatabase">';
			echo '</form><br>';
		}
	}
	else {
		$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
		if($temp->rowCount() > 0) {
			echo '<br>'.__('A geolocation database exists.').'<br>';
		}
		else {
			echo '<br><b>'.__('No geolocation database found.').'</b><br>';
		}
		echo __('Check how many new locations have to be indexed and how long the indexing may take (approximately).');
		echo '<form method="POST" name="checkform" action="'.$_SERVER['PHP_SELF'].'?page=google_maps" style="display : inline;">';
		echo '<br><input type="submit" name="check_new" value="'.__('Check').'"><br><br>';
		echo '</form>';
	}
	
	echo '</td></tr>';

	$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
	if($temp->rowCount() > 0) {
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~ REFRESH BIRTH/DEATH STATUS ~~~~~~~~~~~~~~~~~~~~~~
		//echo '<tr bgcolor="green"><th><font color="white">'.__('Refresh birth/ death status and tree affiliation of all locations').'</font></th></tr>';
		echo '<tr class="table_header"><th>'.__('Refresh birth/ death status and tree affiliation of all locations').'</th>';

		echo '<tr><td>';
		if(isset($_POST['refresh'])) {
			refresh_status();  // see function at end of script
			echo '<div style="color:red;font-weight:bold;">'.__('The birth/ death status and tree affiliation has been refreshed.').'</div><br>';
		}
		else {
			echo '<form action="'.$_SERVER['PHP_SELF'].'?page=google_maps" method="post">';
			echo __('The "Find a location on the map" pull-down displays a list according to the chosen tree and the birth/ death mapping choice. For this to work properly, the birth/death status and tree affiliation of all locations has to be up to date.<br><br><b>
TIP:</b> When you import a gedcom, you can mark the option "Add new locations to geo-location database" and the location status of existing locations will also be updated automatically! (If you didn\'t mark this option on import, use the "Update geolocation database" above. This will also refresh the existing location status).
<p><b>When to use this button:</b><ul><li> if you edited location data directly with the HuMo-gen editor</li><li>if you wish to delete all locations that have become obsolete (mark the box below)</li></ul></p>');
echo '<input type="checkbox" name="purge"> '.__('Also delete all locations that have become obsolete (not connected to any persons anymore)').'<br>';
		echo '<input type="submit" style="font-size:14px" value="'.__('Refresh').'" name="refresh">';
			echo '</form>';
		}
		echo '</td></tr>';

		//~~~~~~~~~~~~~~~~~~~~~~~~~~~ EDIT GEOLOCATION DATABASE ~~~~~~~~~~~~~~~~~~~~~~~~~

		//echo '<tr bgcolor="green"><th><font color="white">'.__('Edit geolocation database').'</font></th></tr>';
		echo '<tr class="table_header"><th>'.__('Edit geolocation database').'</th>';

		echo '<tr><td>';
		if (isset($_POST['loc_change']) OR isset($_POST['loc_add']) OR isset($_POST['yes_change']) OR isset($_POST['cancel_change'])) { 
			// the "change" or "add" buttons were used -- show the place that was added or changed
			// the "YES" was pressed -- the lat/lng of bottom box are used so they have to be shown
			// the "NO" button was pressed -- we leave the bottom box as it was so the user may consider again
			$lat = $_POST['add_lat'];  
			$lng = $_POST['add_lng']; 
		}
		else {
			if(isset($_POST['flag_form'])) {  
				// the pulldown was used -- so show the place that was chosen
				$result = $dbh->query("SELECT * FROM humo_location WHERE location_id = ".safe_text($_POST['loc_find']));
			}
			elseif (isset($_POST['loc_delete'])) { 
				// "delete" was used -- so show map+marker for first on list
				$dbh->query("DELETE FROM humo_location WHERE location_id = ".$_POST['loc_del_id']);
				$result = $dbh->query("SELECT * FROM humo_location ORDER BY location_location");
			}
			else { 
				// page was newly entered -- so show map+marker for first on list
				$result = $dbh->query("SELECT * FROM humo_location ORDER BY location_location");
			}
			$row = $result->fetch();
			$lat = $row['location_lat'];  
			$lng = $row['location_lng']; 
		}
		?> 
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<script type="text/javascript"> 
		function disableEnterKey(e){ 
		// works for FF and Chrome
		var key; 
			if(window.event){ 
				key = window.event.keyCode; 
			} 
			else { 
				key = e.which;      
			} 
			if(key == 13){ 
				return false; 
			} 
			else { 
				return true; 
			} 
		} 
		function testForEnter() 
		// works for IE
		{   
			if(navigator.userAgent.indexOf("MSIE") != -1) {
				if (event.keyCode == 13) 
				{        
					event.cancelBubble = true;
					event.returnValue = false;
				}
			}
		} 
		</script> 
		<script type="text/javascript">
		var geocoder;
		var map;
		var markers=[];
		function initialize() {
			geocoder = new google.maps.Geocoder();
			<?php
			echo 'var latlng = new google.maps.LatLng('.$lat.','.$lng.');';
			?>
			var myOptions = {
				zoom: 12,
				center: latlng,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
			map = new google.maps.Map(document.getElementById("map_canvas"), myOptions); 
			<?php
			echo 'document.getElementById("latbox").innerHtml = latlng.lat().toFixed(5);'; 
			echo 'document.getElementById("lngbox").innerHtml = latlng.lng().toFixed(5);';
			?>
			map.setCenter(latlng);

				markers[0] = new google.maps.Marker({
				map: map,
				position: latlng,
				draggable: true
			});
			google.maps.event.addListener(markers[0], 'drag', function(event){
			document.getElementById("latbox").value = event.latLng.lat().toFixed(5);
			document.getElementById("lngbox").value = event.latLng.lng().toFixed(5);
			});
		}
	  
		function clearMarker() {
			for(j=0; j<markers.length; j++){
			if(markers[j] != undefined) markers[j].setMap(null);
			}
		} 

		function codeAddress() {
			clearMarker();
			var address = document.getElementById("address").value;
			geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				document.getElementById("latbox").innerHtml = results[0].geometry.location.lat().toFixed(5); 
				document.getElementById("lngbox").innerHtml = results[0].geometry.location.lng().toFixed(5);
				document.getElementById("latbox").value = results[0].geometry.location.lat().toFixed(5);
				document.getElementById("lngbox").value = results[0].geometry.location.lng().toFixed(5);
				map.setCenter(results[0].geometry.location);

					markers[1] = new google.maps.Marker({
					map: map,
					position: results[0].geometry.location,
					draggable: true
				});
				google.maps.event.addListener(markers[1], 'drag', function(event){
				document.getElementById("latbox").value = event.latLng.lat().toFixed(5);
				document.getElementById("lngbox").value = event.latLng.lng().toFixed(5);

				});  
			} else {
				alert("Geocode was not successful for the following reason: " + status);
			}
			markers.push(markers[1]);         	
			});
		}
		</script>
		<body onload="initialize()" >
		<?php

		echo '<table style="width:100%;border:none">';
		echo '<tr><td valign="top" colspan="2">';
		$leave_bottom = false;
		if(isset($_POST['loc_delete'])) {
			// delete location
			echo '<span style="color:red;font-weight:bold;">'.__('Deleted location:').str_replace("\'","'",safe_text($_POST['loc_del_name'])).'</span><br>';
		}
		if(isset($_POST['loc_change']) OR isset($_POST['yes_change']) OR isset($_POST['cancel_change'])) {
			// "change" location or "yes" button pressed
			$pos = strpos($_POST['add_name'],$_POST['loc_del_name']);

			if(!isset($_POST['cancel_change']) AND ($pos !== false OR isset($_POST['yes_change']))) {  // the name in pulldown appears in the name in the search box
				$dbh->query("UPDATE humo_location SET location_location ='".safe_text($_POST['loc_del_name'])."', location_lat =".floatval($_POST['add_lat']).", location_lng = ".floatval($_POST['add_lng'])." WHERE location_location = '".safe_text($_POST['loc_del_name'])."'");
				echo '<span style="color:red;font-weight:bold;">'.__('Changed location:').' '.str_replace("\'","'",safe_text($_POST['loc_del_name'])).'</span><br>';
			}
			elseif(isset($_POST['cancel_change'])) {
				$leave_bottom = true;
			}
			else {
				$leave_bottom = true;
				echo '<span style="color:red;font-weight:bold;">Are you sure you want to change the lat/lng of </span><b>'.$_POST['loc_del_name'].'</b>';
				echo '<span style="color:red;font-weight:bold;"> and set them to those that belong to </span><b>'.$_POST['add_name'].'?</b></span><br>';
				echo '<form method="POST" name="check_change" action="'.$_SERVER['PHP_SELF'].'?page=google_maps" style="display : inline;">';
				echo '<input type="hidden" name="add_lat" value="'.$_POST['add_lat'].'">';
				echo '<input type="hidden" name="add_lng" value="'.$_POST['add_lng'].'">';
				echo '<input type="hidden" name="add_name" value="'.$_POST['add_name'].'">';
				echo '<input type="hidden" name="loc_del_name" value="'.$_POST['loc_del_name'].'">';
				echo '<input type="hidden" name="loc_del_id" value="'.$_POST['loc_del_id'].'">';
				echo '<input type="submit" name="yes_change" value="'.__('YES').'">';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<input type="submit" name="cancel_change" value="'.__('Cancel').'">';
				echo '</form><br><br>';
			}
		}
		if(isset($_POST['loc_add'])) {
			//  we added new location
			//  make sure this location doesn't exist yet! otherwise we get doubles
			//  if the location already exists do as if "change" was pressed.
			@$result = $dbh->query("SELECT location_location FROM humo_location WHERE location_location = '".$_POST['add_name']."'");
			if($result->rowCount()==0) { // doesn't exist yet
				$dbh->query("INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES('".$_POST['add_name']."','".floatval($_POST['add_lat'])."','".floatval($_POST['add_lng'])."') ");
				echo '<span style="color:red;font-weight:bold;">'.__('Added location:').' '.str_replace("\'","'",safe_text($_POST['add_name'])).'</span><br>';
			}
			else { // location already exists, just update the lat/lng
				$dbh->query("UPDATE humo_location SET location_location ='".$_POST['add_name']."', location_lat =".floatval($_POST['add_lat']).", location_lng = ".floatval($_POST['add_lng'])." WHERE location_location = '".safe_text($_POST['add_name'])."'");
				echo '<span style="color:red;font-weight:bold;"> '.str_replace("\'","'",safe_text($_POST['add_name'])).': Location already exists.<br>Updated lat/lng.</span><br>';
			}
		}

		echo '<form method="POST" name="dbform" action="'.$_SERVER['PHP_SELF'].'?page=google_maps" style="display : inline;">';
		$loc_list = $dbh->query("SELECT * FROM humo_location ORDER BY location_location");
		echo '<input type="hidden" name="flag_form" value="dummy">';
		echo '<select size="1" onChange="document.dbform.submit();" name="loc_find" id="loc_find">';
		$find_default=true;
		while ($loc_listDb=$loc_list->fetch(PDO::FETCH_OBJ)){
			$selected='';
			if(isset($_POST['loc_find'])) {
				if($loc_listDb->location_id == $_POST['loc_find']) {
					$selected=" SELECTED";
				}
			}
			elseif(isset($_POST['loc_change']) OR isset($_POST['yes_change']) OR isset($_POST['cancel_change'])) {
				if($loc_listDb->location_location == $_POST['loc_del_name']) {
					$selected=" SELECTED";
				}
			}	
			elseif(isset($_POST['loc_add'])) {
				if($loc_listDb->location_location == $_POST['add_name']) {
					$selected=" SELECTED";
				}
			}
			else {
				if($find_default===true) { // first location on the list
					$_POST['loc_find'] = $loc_listDb->location_id;
					$find_default = false;
				}
			}
			echo '<option value="'.$loc_listDb->location_id.'"'.$selected.' >'.$loc_listDb->location_location.' </option>';
		}
		echo '</select>';

		echo '</form></td>';
		echo '<td style="width:360px" rowspan="12"><div id="map_canvas" style="height:360px; width:360px;" ></td></tr>';

		if(isset($_POST['loc_add'])) {
			// we have added or changed a location - so show that location after page load
			$result = $dbh->query("SELECT * FROM humo_location WHERE location_location = '".$_POST['add_name']."'");
		}
		elseif(isset($_POST['loc_change']) OR isset($_POST['yes_change']) OR isset($_POST['cancel_change'])) {
			// we have changed a location by "Change" or by "YES" - so show that location after page load
			// or we pushed the "NO" button and want to leave the situation as it was
			$result = $dbh->query("SELECT * FROM humo_location WHERE location_id = ".$_POST['loc_del_id']);
		}
		else {
			// default: show the location that was selected with the pull down box
			$result = $dbh->query("SELECT * FROM humo_location WHERE location_id = ".$_POST['loc_find']);
		}
		$resultDb=$result->fetch(PDO::FETCH_OBJ);

		echo '<form method="POST" name="delform" action="'.$_SERVER['PHP_SELF'].'?page=google_maps" style="display : inline;">';
		echo '<tr><th colspan="2">'.__('Details from the database').'</th></tr>';
		echo '<tr><td>'.__('Location').':</td><td><input type="text" id="loc_name" name="loc_name" value="'.$resultDb->location_location.'" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td></tr>';
		echo '<tr><td>'.__('Latitude').':</td><td><input type="text" id="loc_lat" name="loc_lat" value="'.$resultDb->location_lat.'" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td></tr>';
		echo '<tr><td>'.__('Longitude').':</td><td><input type="text" id="loc_lng" name="loc_lng" value="'.$resultDb->location_lng.'" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td></tr>';
		echo '<tr><td align="center" colspan="2">';
		echo '<input type="hidden" name="loc_del_id" value="'.$resultDb->location_id.'">';
		echo '<input type="hidden" name="loc_del_name" value="'.$resultDb->location_location.'">';
		echo '<input type="Submit" style="color:red;font-weight:bold" name="loc_delete" value="'.__('Delete this location').'"></td></tr>';
		//echo '</form>';

		//echo '<form method="POST" name="searchform" action="'.$_SERVER['PHP_SELF'].'?page=google_maps" style="display : inline;">';
		$search_name = $resultDb->location_location;
		$search_lat = $resultDb->location_lat;
		$search_lng = $resultDb->location_lng;
		if($leave_bottom === true) {
			$search_name = $_POST['add_name'];
			$search_lat =  $_POST['add_lat'];
			$search_lng =  $_POST['add_lng'];
		}
		echo '<tr><td style="border:none;height:20px"></td></tr>';
		echo '<tr><th colspan="2">'.__('Change or add locations').'<br>'.__('(You can also drag the marker!)').'</th></tr>';
		echo '<tr><td colspan="2"><input id="address" onKeyPress="return disableEnterKey(event);" onKeyDown="testForEnter();" type="textbox" value="'.$search_name.'" size="36" name="add_name">';
		echo '<input type="button" name="loc_search" value="'.__('Search').'" onclick="codeAddress();"></td></tr>';
		echo '<tr><td>'.__('Latitude').':</td><td><input size="20" type="text" id="latbox" name="add_lat" onKeyPress="return disableEnterKey(event);" onKeyDown="testForEnter();" value="'.$search_lat.'"></td></tr>';
		echo '<tr><td>'.__('Longitude').':</td><td><input size="20" type="text" id="lngbox" name="add_lng" onKeyPress="return disableEnterKey(event);" onKeyDown="testForEnter();" value="'.$search_lng.'"></td></tr>';	
		echo '<tr><td colspan="2">';
		echo '<input type="Submit" name="loc_change" value="'.__('Change this location').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<input type="Submit" name="loc_add" value="'.__('Add this location').'"></td></tr>';
		echo '</form>';
		echo '</table>';

		echo '</td></tr>';

		//~~~~~~~~~~~~~~~~~~~~~~~~~~~~DELETE GEOLOCATION DATABASE~~~~~~~~~~~~~~~~~~~~~~~~~

		//echo '<tr bgcolor="green"><th><font color="white">'.__('Delete geolocation database').'</font></th></tr>';
		echo '<tr class="table_header"><th>'.__('Delete geolocation database').'</th>';
		echo '<tr><td>';

		if(isset($_POST['deletedatabase'])) {
			echo '<p style="color:red;font-weight:bold;">'.__('Database was deleted!').'<p>';
		}
		else {  // there is a database
			$num_rows = $loc_list->rowCount();
			printf(__('Here you can delete your entire geolocation database (%d entries).<br>
If you are absolutely sure, press the button below.'), $num_rows);
			echo '<br><form action="'.$_SERVER['PHP_SELF'].'?page=google_maps" method="post">';
			echo '<input type="submit" style="font-size:14px;color:red;font-weight:bold" value="'.__('DELETE ENTIRE GEOLOCATION DATABASE').'" name="deletedatabase">';
			echo '<br></form><br>';
		}
		echo '</td></tr>';
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~ SETTINGS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		//echo '<tr bgcolor="green"><th><font color="white">'.__('Settings').'</font></th></tr>';
		echo '<tr class="table_header"><th>'.__('Settings').'</th>';
		echo '<tr><td>';
		echo '<form name="slider" action="'.$_SERVER['PHP_SELF'].'?page=google_maps" method="POST">';
		echo __('The slider has 10 steps. By default the starting year is 1560 with 9 intervals of 50 years up till 2010 and beyond.<br>
You can set the starting year yourself for each tree, to suit it to the earliest years in that tree<br>
The 9 intervals will be calculated automatically. Some example starting years for round intervals:<br>
1110 (intv. 100), 1560 (intv. 50), 1695 (intv. 35),1740 (intv. 30), 1785 (intv. 25), 1830 (intv. 20)').'<br><br>';

		// *** Select family tree ***
		$tree_prefix_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$tree_prefix_result = $dbh->query($tree_prefix_sql);
		echo '<table><tr><th>'.__('Name of tree').'</th><th style="text-align:center">'.__('Starting year').'</th>';
		echo '<th style="text-align:center">'.__('Interval').'</th>';
		$rowspan = $tree_prefix_result->rowCount() + 1;
		echo '<th rowspan='.$rowspan.'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="Submit" name="submit" value="'.__('Change').'"></th></tr>';
		echo '<form method="POST" action="maps.php" style="display : inline;">';
		while ($tree_prefixDb=$tree_prefix_result->fetch(PDO::FETCH_OBJ)){
			${"slider_choice".$tree_prefixDb->tree_prefix}="1560"; // default
			$query = "SELECT * FROM humo_settings WHERE setting_variable='gslider_".$tree_prefixDb->tree_prefix."' ";
			$result = $dbh->query($query);
			$offset="slider_choice_".$tree_prefixDb->tree_prefix;
			if ($result->rowCount() >0) {
				$slider_choiceDb = $result->fetch(PDO::FETCH_OBJ);
				${"slider_choice".$tree_prefixDb->tree_prefix} = $slider_choiceDb->setting_value;
				if(isset($_POST[$offset])) {
					$sql="UPDATE humo_settings SET setting_value='".$_POST[$offset]."' WHERE setting_variable='gslider_".$tree_prefixDb->tree_prefix."'";
					$dbh->query($sql);
					${"slider_choice".$tree_prefixDb->tree_prefix}=$_POST[$offset];
				}
			}
			else {
				if(isset($_POST[$offset])) {
					$sql="INSERT INTO humo_settings SET setting_variable='gslider_".$tree_prefixDb->tree_prefix."', setting_value='".$_POST[$offset]."'";
					$dbh->query($sql);
					${"slider_choice".$tree_prefixDb->tree_prefix}=$_POST[$offset];
				}
			}

			$treetext=show_tree_text($tree_prefixDb->tree_prefix, $selected_language);
			echo "<tr><td>".$treetext['name']."</td>";
			echo "<td><input style='text-align:center' type='text' name='".$offset."' value='${"slider_choice".$tree_prefixDb->tree_prefix}'></td>";
			$interval = round((2010 - ${"slider_choice".$tree_prefixDb->tree_prefix})/9);
			echo "<td style='text-align:center'>".$interval."</td></tr>";
			//echo '<td><input type="Submit" name="submit" value="'.__('Change').'"></td></tr>';

		}
		echo '</table>';  // end list of trees and starting years
		echo '<br>'.__('Default slider position').": ";
		$query = "SELECT * FROM humo_settings WHERE setting_variable='gslider_default_pos' ";
		$result = $dbh->query($query);

		if(isset($_POST['slider_default'])) {
			if ($result->rowCount >0) { 
				$sql="UPDATE humo_settings SET setting_value ='".$_POST['slider_default']."' WHERE setting_variable='gslider_default_pos'";
				$dbh->query($sql);
				$sl_def=$_POST['slider_default'];
			}
			else {
				$sql="INSERT INTO humo_settings SET setting_variable='gslider_default_pos', setting_value='".$_POST['slider_default']."'";
				$dbh->query($sql);
				$sl_def=$_POST['slider_default'];
			}
		}
		else {
			if ($result->rowCount() >0) {
				$sl_default_pos=$result->fetch();
				$sl_def = $sl_default_pos['setting_value'];
			}
			else {
				$sl_def="all";
			}
		}


		echo '<select size="1" name="slider_default" id="slider_default">';
		$selected = ""; if($sl_def=="off") $selected=" SELECTED ";
		echo '<option value="off" '.$selected.'>'.__('OFF position (leftmost position)').'</option>';
		$selected = ""; if($sl_def=="all") $selected=" SELECTED ";
		echo '<option value="all" '.$selected.'>'.__('Show all periods (rightmost position)').'</option>';
		echo '</select>';
		echo '</form>';
		echo '</td></tr>';
	}
}
//else {
//			echo '<p>'.__('No geolocation database found').'</p>';
//}
echo '</table>';  // end google maps admin

// function to refresh location_status column
function refresh_status() {
	global $dbh;
	// make sure the location_status column exists. If not create it
	$result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
	$exists = $result->rowCount();
	if(!$exists) {
		$dbh->query("ALTER TABLE humo_location ADD location_status TEXT DEFAULT '' AFTER location_lng");
	}
	$all_loc = $dbh->query("SELECT location_location FROM humo_location");
	while($all_locDb = $all_loc->fetch(PDO::FETCH_OBJ)) {
		$loca_array[$all_locDb->location_location] = "";
	}
	$status_string = "";

	//$tree_pref_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
	//$tree_pref_result = $dbh->query($tree_pref_sql);
	//while ($tree_prefDb=$tree_pref_result->fetch(PDO::FETCH_OBJ)){
		//$result=$dbh->query("SELECT pers_birth_place, pers_bapt_place, pers_death_place, pers_buried_place
		//	FROM ".$tree_prefDb->tree_prefix."person");
		$result=$dbh->query("SELECT pers_tree_prefix, pers_birth_place, pers_bapt_place, pers_death_place, pers_buried_place
			FROM humo_persons");
		while($resultDb = $result->fetch(PDO::FETCH_OBJ)) {
			//if (isset($loca_array[$resultDb->pers_birth_place]) AND strpos($loca_array[$resultDb->pers_birth_place],$tree_prefDb->tree_prefix."birth ")===false) {
			if (isset($loca_array[$resultDb->pers_birth_place]) AND strpos($loca_array[$resultDb->pers_birth_place],$resultDb->pers_tree_prefix."birth ")===false) {
				//$loca_array[$resultDb->pers_birth_place] .= $tree_prefDb->tree_prefix."birth ";
				$loca_array[$resultDb->pers_birth_place] .= $resultDb->pers_tree_prefix."birth ";
			}
			//if (isset($loca_array[$resultDb->pers_bapt_place]) AND strpos($loca_array[$resultDb->pers_bapt_place],$tree_prefDb->tree_prefix."bapt ")===false) {
			if (isset($loca_array[$resultDb->pers_bapt_place]) AND strpos($loca_array[$resultDb->pers_bapt_place],$resultDb->pers_tree_prefix."bapt ")===false) {
				//$loca_array[$resultDb->pers_bapt_place] .= $tree_prefDb->tree_prefix."bapt ";
				$loca_array[$resultDb->pers_bapt_place] .= $resultDb->pers_tree_prefix."bapt ";
			}
			//if (isset($loca_array[$resultDb->pers_death_place]) AND strpos($loca_array[$resultDb->pers_death_place],$tree_prefDb->tree_prefix."death ")===false) {
			if (isset($loca_array[$resultDb->pers_death_place]) AND strpos($loca_array[$resultDb->pers_death_place],$resultDb->pers_tree_prefix."death ")===false) {
				//$loca_array[$resultDb->pers_death_place] .= $tree_prefDb->tree_prefix."death ";
				$loca_array[$resultDb->pers_death_place] .= $resultDb->pers_tree_prefix."death ";
			}
			//if (isset($loca_array[$resultDb->pers_buried_place]) AND strpos($loca_array[$resultDb->pers_buried_place],$tree_prefDb->tree_prefix."buried ")===false) {
			if (isset($loca_array[$resultDb->pers_buried_place]) AND strpos($loca_array[$resultDb->pers_buried_place],$resultDb->pers_tree_prefix."buried ")===false) {
				//$loca_array[$resultDb->pers_buried_place] .= $tree_prefDb->tree_prefix."buried ";
				$loca_array[$resultDb->pers_buried_place] .= $resultDb->pers_tree_prefix."buried ";
			}
		}
	//}
	foreach($loca_array as $key => $value) {
		if(isset($_POST['purge']) AND $value == "") {
			$dbh->query("DELETE FROM humo_location WHERE location_location = '".addslashes($key)."'");
		}
		else {
			$dbh->query("UPDATE humo_location SET location_status = '".$value."' WHERE location_location = '".addslashes($key)."'");
		}
	}
}

?>