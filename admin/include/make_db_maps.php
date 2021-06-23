<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }
//error_reporting(E_ALL);
@set_time_limit(3000);

echo '<h1 align=center>'.__('Google maps administration').'</h1>';
//echo '<table style="width:800px; margin-left:auto; margin-right:auto;" class="humo" border="1">';
echo '<table class="humo standard" border="1" style="width:900px;">';

if(isset($_POST['makedatabase'])) {  // the user decided to add locations to the location database
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
			location_status TEXT
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

	// If the locations are taken from one tree, add the id of this tree to humo_settings "geo_trees", if not already there
	// so we can update correctly with the "REFRESH BIRTH/DEATH STATUS" option further on.
	if($_SESSION['geo_tree']  != "all_geo_trees") {  // we add locations from one tree
		if(strpos($humo_option['geo_trees'],"@".$_SESSION['geo_tree'].";")===false) { // this tree_id does not appear already
				$dbh->query("UPDATE humo_settings SET setting_value = '".$humo_option['geo_trees']."@".$_SESSION['geo_tree'] .";' WHERE setting_variable ='geo_trees'"); 
			// add tree_prefix if not already present
			$humo_option['geo_trees'] .= "@".$_SESSION['geo_tree'].';'; // humo_option is used further on before page is refreshed so we have to update it manually
		}
	}
	else {
		$str="";
		$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$tree_search_result = $dbh->query($tree_search_sql);
		while ($tree_searchDb=$tree_search_result->fetch(PDO::FETCH_OBJ)){ 
			$str .= "@".$tree_searchDb->tree_id.";";
		}
			$dbh->query("UPDATE humo_settings SET setting_value = '".$str."' WHERE setting_variable ='geo_trees'"); 
		$humo_option['geo_trees'] = $str; // humo_option is used further on before page is refreshed so we have to update it manually
	}
	foreach($_SESSION['add_locations'] as $value) {
		$count_parsed++;
		//if($count_parsed<110 OR $count_parsed > 125) continue;
		$loc=urlencode($value);
		//echo "<br>".$value." - ".$loc."<br>";
		/* This piece is outdated since Google's API revision. The second key (IP address restricted) has to be used and an https connection is mandatory.
 		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') { 
 			$jsonurl = "https://maps.googleapis.com/maps/api/geocode/json?address=".$loc.$api_key; 

 		}
 		else {
 
 			$jsonurl = "http://maps.googleapis.com/maps/api/geocode/json?address=".$loc.$api_key;
 		}
		*/

		$api_key = ''; 
		// Key is meant for showing maps and should be set to restriction: "HTTP referrers". This key will only be used here if no second key is present.
		// This key will only work here if admin temporarily set it to restriction "None" or to "IP addresses" with server IP.
		if(isset($humo_option['google_api_key']) AND $humo_option['google_api_key']!='') {
			$api_key = "&key=".$humo_option['google_api_key'];
		} 

		$api_key2 = ''; // Key meant for geolocation. Is protected by "IP addresses" restriction.
		if(isset($humo_option['google_api_key2']) AND $humo_option['google_api_key2']!='') {
			$api_key2 = "&key=".$humo_option['google_api_key2'];
		} 
		if($api_key2 == "") { $api_key2 = $api_key; }  // if no second key is present, try to use first key.

		$jsonurl = "https://maps.googleapis.com/maps/api/geocode/json?address=".$loc.$api_key2; 
		//echo $api_key." - ".$api_key2."<br>";
		//echo $jsonurl."<br>";
		//$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$loc.$api_key2);
		//echo $json;
 
		$json = file_get_contents($jsonurl,0,null,null);
		// file_get_contents won't work if "allow_url_fopen" is disabled by host for security considerations.
		// in that case try the PHP "curl" extension that is installed on most hosts (but we still check...)
		if(!$json) {
			if(extension_loaded('curl')) {
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $jsonurl);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
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
			$dbh->query("INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES('".safe_text_db($value)."', '".$lat."', '".$lng."') ");

			sleep(1);  // crucial, otherwise google kicks you out after a few queries
		}
		elseif ($json_output['status']=="ZERO_RESULTS") { // store locations that were not found by google geocoding
			$map_notfound_array[]= $json_output['status'].' - '.$value;
			$map_count_notfound++;
			$dbh->query("INSERT INTO humo_no_location (no_location_location) VALUES('".safe_text_db($value)."') ");
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
			printf(__('The following %d new locations were passed for query, but were not found. Please check their validity.'), $map_count_notfound);
			echo '<br>';
			foreach($map_notfound_array as $value) {
				echo $value."<br>";
			}
		}
	}
	else {  // the process was interrupted because of OVER_QUERY_LIMIT. Explain to the admin!
		echo '<p style="color:red;font-size:120%"><b> '.__('The process was interrupted because Google limits to maximum 2500 queries within one day (counting is reset at midnight PST, which is 08:00 AM GMT)').'</b></p>';
		printf(__('In total %1$d out of %2$d new locations were passed for query to Google.'), $count_parsed, count($_SESSION['add_locations']));
		echo __('Tomorrow you can run this process again to add the locations that were not passed for geocoding today.');
		echo '<p>'.$map_count_found.' '.__('locations were recognized by geocoding and have been saved in the database.').'</p><br>';

		if($map_notfound_array) { // some locations were not found by geocoding
			echo '<b>';
			printf(__('The following %d new locations were passed for query, but were not found. Please check their validity.'), $map_count_notfound);
			echo '</b><br>';
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
	echo '<br><form action="index.php?page=google_maps" method="post">';
	echo '<input type="submit" style="font-size:14px" value="'.__('Back').'">';
	echo '<br></form><br>';
 
}  // end - if add to database

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MAIN SCREEN on entry into the google maps option in the admin menu

else {  // main screen

	if(isset($_POST['deletedatabase'])) {
		$dbh->query("DROP TABLE humo_location");
		$dbh->query("UPDATE humo_settings SET setting_value='' WHERE setting_variable = 'geo_trees'");
	}
	if(isset($_POST['refresh_no_locs'])) { // refresh non-indexable locations table
		$new_no_locs = array();
		$unionstring = "SELECT pers_birth_place FROM humo_persons
			UNION SELECT pers_bapt_place FROM humo_persons WHERE pers_birth_place = ''
			UNION SELECT pers_death_place FROM humo_persons
			UNION SELECT pers_buried_place FROM humo_persons WHERE pers_death_place = ''";
			// (only take bapt place if no birth place and only take burial place if no death place)
			
		// from here on we can use only "pers_birth_place", since the UNION puts also all other locations under pers_birth_place
		$map_person=$dbh->query("SELECT pers_birth_place, count(*) AS quantity
			FROM (".$unionstring.") AS x GROUP BY pers_birth_place ");

		// make array of all stored non indexable locations
		$no_location=$dbh->query("SELECT no_location_location FROM humo_no_location");
		while (@$no_locationDb=$no_location->fetch(PDO::FETCH_OBJ)){
			$non_exist_locs[] = $no_locationDb->no_location_location; 
		}
		
		while (@$personDb=$map_person->fetch(PDO::FETCH_OBJ)){ // loop thru all locations in database
			foreach($non_exist_locs AS $value) {  // loop thru stored list of non-indexable loactions
				if($value == $personDb->pers_birth_place) { // check if this non-indexable location indeed still exists in the birth/death places in database
					$new_no_locs[] = $value;  // if it does - add to array
				}
			}
		}
		$dbh->query("TRUNCATE TABLE humo_no_location"); 
		foreach($new_no_locs AS $value) { 
			$dbh->query("INSERT INTO humo_no_location (no_location_location) VALUES('".safe_text_db($value)."') ");
		}
	}


	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CHECK FOR GOOGLE MAPS API KEY ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	echo '<tr class="table_header"><th>'.__('Google maps API Key').'</th>';
	echo '<tr><td>';
	echo __('As of Jun 22, 2016 Google has changed its API policy and all queries to the Google maps API now require a site-specific key.')."<br>";
	echo __('For now, it seems that installations that started API queries prior to June 22, 2016 will continue to work also without a key.')."<br>";
	echo __('If however you start activating the Google map feature after this date, you are likely to encounter error messages concerning a missing key if you do not first enter and save a key here.')."<br>";
	echo __('If you don\'t have a Google account, first create one. Once logged into your Google account, go to:');
	echo ' <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">'.__('Get API key').'</a> ';
	echo __('and follow the instructions.')."<br>";
	echo "<strong>".__('Create two keys').":</strong><br><ul><li>";
	echo __('For the first key, set restriction to <strong>"HTTP referrers"</strong> and enter your website domain name.')."<br>".__('If your domain looks like \'www.mydomain.com\', enter:')." <strong>*.mydomain.com/*</strong><br>".__('If your domain looks like \'mydomain.com\', enter:')." <strong>mydomain.com/*</strong></li><br>";

	//Function to try every way to resolve domain IP. Is more accurate than good old: gethostbyname($_SERVER['SERVER_NAME']) or gethostbyname(gethostname()) ;
	function get_host() {
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) AND $host = $_SERVER['HTTP_X_FORWARDED_HOST']) {
			$elements = explode(',', $host);
			$host = trim(end($elements));
		}
		else {
				if (!$host = $_SERVER['HTTP_HOST']) {
					if (!$host = $_SERVER['SERVER_NAME']) {
						$host = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
					}
			}
		}
		// Remove port number from host
		$host = preg_replace('/:\d+$/', '', $host);
		return trim($host);
	}
	// get IPv4 address
	$ip = gethostbyname(get_host());
	// get IPv6 address
	$ipv6 = dns_get_record(get_host(),DNS_AAAA);

	echo "<li>".__('For the second key, set restriction to <strong>"IP addresses"</strong> and enter your server IP.')." ".__('Not your computer\'s IP!')."<br>";
	echo __('Your server IP would seem to be:')." <strong>".$ip."</strong><br>";
	if(isset($ipv6[0]['ipv6'])) {  // cpntains the IPv6 address is present
		echo __('Your server also has an IPv6 address. If the above IP doesn\'t work, try the IPv6 which would seem to be:')." <strong>".$ipv6[0]['ipv6']."</strong><br>";
	}
	echo __('If this doesn\'t work, contact your provider and try to obtain the proper IP address from them.')."<br>";
	echo __('Once you receive the keys enter them in the two fields below and save.')."<br><br>";
	$api_query = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'google_api_key'");
	$apiDb = $api_query->fetch(PDO::FETCH_OBJ);   
	if($api_query->rowCount() > 0) { // there is an api key 1 setting in the database
		if(isset($_POST['change_api']) OR $apiDb->setting_value=='') {  
			// admin requested to change the existing key OR key setting in database is empty - show field to enter updated key
			echo __('API key')." 1 (restriction: <strong>HTTP referrers</strong>): ";
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '<input type="text" id="new_api" name="new_api" size="40" >';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Save').'" name="api_save">';
			echo '</form><br>';
		}

		elseif(isset($_POST['delete_api'])) {  
			// admin requested to delete the existing key - show field to enter updated key
			$temp = $dbh->query("DELETE FROM humo_settings WHERE setting_variable = 'google_api_key'");
			echo __('API key')." 1 (restriction: <strong>HTTP referrers</strong>): ";
			echo __('has been deleted');
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Change').'" name="change_api">';
			echo '</form><br>';
		}
		
		else  {
			// fresh page called OR updated key entered
			if(isset($_POST['new_api']) AND $_POST['new_api']!="") {  
				// admin enter updated key
				$temp = $dbh->query("UPDATE humo_settings SET setting_value = '".$_POST['new_api']."' WHERE setting_variable = 'google_api_key'");
				echo __('API key')." 1 (restriction: <strong>HTTP referrers</strong>): "; 
				echo '<span style="font-weight:bold">'.$_POST['new_api'].'</span>';
			}
			else {
				echo __('API key')." 1 (restriction: <strong>HTTP referrers</strong>): "; 
				echo '<span style="font-weight:bold">'.$apiDb->setting_value.'</span>'; 
			}
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Change').'" name="change_api">';
			echo '</form>&nbsp;&nbsp;&nbsp;';
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Delete').'" name="delete_api">';
			echo '</form><br>';
		}

	}
	else  { // no API key 1 variable found in database
		if(!isset($_POST['new_api'])) { 
			// fresh page when no api key 1 variable exists - show field to enter key 1
 			echo __('API key')." 1 (restriction: <strong>HTTP referrers</strong>): ";
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '<input type="text" id="new_api" name="new_api" size="40" >';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Save').'" name="save_new_api">';
			echo '</form><br>';
		}
		else { 
			// new api was entered
			if($_POST['new_api'] != "") { // a key was entered, store in database and show
				$temp = $dbh->query("INSERT INTO humo_settings SET setting_variable='google_api_key', setting_value='".$_POST['new_api']."'");
				echo __('API key')." 1 (restriction: <strong>HTTP referrers</strong>): "; 
				echo '<span style="font-weight:bold">'.$_POST['new_api'].'</span>';
				echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
				echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Change').'" name="change_api">';
				echo '</form>&nbsp;&nbsp;&nbsp;';
				echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
				echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Delete').'" name="delete_api">';
				echo '</form><br>';
			}
			else { // empty key was entered, show field again...
				echo __('API key')." 1 (restriction: <strong>HTTP referrers</strong>): ";
				echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
				echo '<input type="text" id="new_api" name="new_api" size="40" >';
				echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Save').'" name="save_new_api">';
				echo '</form><br>';
			}
		}
	}
	echo '<br>';
	$api_query2 = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'google_api_key2'");
	$apiDb2 = $api_query2->fetch(PDO::FETCH_OBJ);   
	if($api_query2->rowCount() > 0) { // there is an api key 1 setting in the database
		if(isset($_POST['change_api2']) OR $apiDb2->setting_value=='') {  
			// admin requested to change the existing key OR key setting in database is empty - show field to enter updated key
			echo __('API key')." 2 (restriction: <strong>IP addresses</strong>):&nbsp;&nbsp;&nbsp;&nbsp;";
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '<input type="text" id="new_api2" name="new_api2" size="40" >';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Save').'" name="api_save2">';
			echo '</form><br>';
		}

		elseif(isset($_POST['delete_api2'])) {  
			// admin requested to delete the existing key - show field to enter updated key
			$temp2 = $dbh->query("DELETE FROM humo_settings WHERE setting_variable = 'google_api_key2'");
			echo __('API key')." 2 (restriction: <strong>IP addresses</strong>):&nbsp;&nbsp;&nbsp;&nbsp;";
			echo __('has been deleted');
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Change').'" name="change_api2">';
			echo '</form><br>';
		}
		
		else  {  
			// fresh page called OR updated key entered
			if(isset($_POST['new_api2']) AND $_POST['new_api2']!="") {  
				// admin enter updated key
				$temp = $dbh->query("UPDATE humo_settings SET setting_value = '".$_POST['new_api2']."' WHERE setting_variable = 'google_api_key2'");
				echo __('API key')." 2 (restriction: <strong>IP addresses</strong>):&nbsp;&nbsp;&nbsp;&nbsp;"; 
				echo '<span style="font-weight:bold">'.$_POST['new_api2'].'</span>';
			}
			else { 
	  	  		echo __('API key')." 2 (restriction: <strong>IP addresses</strong>):&nbsp;&nbsp;&nbsp;&nbsp;"; 
				echo '<span style="font-weight:bold">'.$apiDb2->setting_value.'</span>'; 
			}
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Change').'" name="change_api2">';
			echo '</form>&nbsp;&nbsp;&nbsp;';
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Delete').'" name="delete_api2">';
			echo '</form><br>';
		}

	}
	else  { // no API key 1 variable found in database
		if(!isset($_POST['new_api2'])) { 
			// fresh page when no api key 2 variable exists - show field to enter key 1
 			echo __('API key')." 2 (restriction: <strong>IP addresses</strong>):&nbsp;&nbsp;&nbsp;&nbsp;";
			echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
			echo '<input type="text" id="new_api2" name="new_api2" size="40" >';
			echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Save').'" name="save_new_api2">';
			echo '</form><br>';
		}
		else { 
			// new api was entered
			if($_POST['new_api2'] != "") { // a key was entered, store in database and show
				$temp = $dbh->query("INSERT INTO humo_settings SET setting_variable='google_api_key2', setting_value='".$_POST['new_api2']."'");
				echo __('API key')." 2 (restriction: <strong>IP addresses</strong>):&nbsp;&nbsp;&nbsp;&nbsp;"; 
				echo '<span style="font-weight:bold">'.$_POST['new_api2'].'</span>';
				echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
				echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Change').'" name="change_api2">';
				echo '</form>&nbsp;&nbsp;&nbsp;';
				echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
				echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Delete').'" name="delete_api2">';
				echo '</form><br>';
			}
			else { // empty key was entered, show field again...
				echo __('API key')." 2 (restriction: <strong>IP addresses</strong>):&nbsp;&nbsp;&nbsp;&nbsp;";
				echo '<form action="index.php?page=google_maps" method="post" style="display:inline">';
				echo '<input type="text" id="new_api2" name="new_api2" size="40" >';
				echo '&nbsp;&nbsp;&nbsp;<input type="submit" style="font-size:14px" value="'.__('Save').'" name="save_new_api2">';
				echo '</form><br>';
			}
		}
	}

	echo '<br></td></tr>';	
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CREATE/UPDATE GEOLOCATION DATABASE ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	//echo '<tr bgcolor="green"><th><font color="white">'.__('Create or update geolocation database').'</font></th></tr>';
	echo '<tr class="table_header"><th>'.__('Create or update geolocation database').'</th>';

	echo '<tr><td>';
	if(isset($_POST['check_new'])) { // the "Check" button was pressed
	
		$unionstring='';

		if(isset($_SESSION['geo_tree']) AND $_SESSION['geo_tree'] != "all_geo_trees") {   
			$unionstring .= "SELECT pers_birth_place FROM humo_persons WHERE pers_tree_id='".$_SESSION['geo_tree']."' UNION
			SELECT pers_bapt_place FROM humo_persons WHERE pers_tree_id='".$_SESSION['geo_tree']."' AND pers_birth_place = '' UNION
			SELECT pers_death_place FROM humo_persons WHERE pers_tree_id='".$_SESSION['geo_tree']."' UNION
			SELECT pers_buried_place FROM humo_persons WHERE pers_tree_id='".$_SESSION['geo_tree']."' AND pers_death_place = ''";
				// (only take bapt place if no birth place and only take burial place if no death place)
		}
		else { 
			$unionstring .= "SELECT pers_birth_place FROM humo_persons
				UNION SELECT pers_bapt_place FROM humo_persons WHERE pers_birth_place = ''
				UNION SELECT pers_death_place FROM humo_persons
				UNION SELECT pers_buried_place FROM humo_persons WHERE pers_death_place = ''";
				// (only take bapt place if no birth place and only take burial place if no death place)
		}

		//$unionstring = substr($unionstring,0,-7); // take off last " UNION "

		// from here on we can use only "pers_birth_place", since the UNION puts also all other locations under pers_birth_place
		$map_person=$dbh->query("SELECT pers_birth_place, count(*) AS quantity
			FROM (".$unionstring.") AS x GROUP BY pers_birth_place ");

		$add_locations = array();
		
		// make array of all existing locations in database	
		$exist_locs = array();
		$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
		$is_database=false;
		if($temp->rowCount() > 0) {
			// there is a database 
			$is_database = true;
			$location=$dbh->query("SELECT location_location FROM humo_location");
			while (@$locationDb=$location->fetch(PDO::FETCH_OBJ)){
				$exist_locs[] = $locationDb->location_location;
			}
		}

		// make array of all non-recognized locations (from previous attempts)
		$non_exist_locs = array();
		$temp = $dbh->query("SHOW TABLES LIKE 'humo_no_location'");
		$is_noloc_database=false;
		if($temp->rowCount() > 0) {
			// there is a table with not found locations 
			$is_noloc_database = true;
			$no_location=$dbh->query("SELECT no_location_location FROM humo_no_location");
			while (@$no_locationDb=$no_location->fetch(PDO::FETCH_OBJ)){
				$non_exist_locs[] = $no_locationDb->no_location_location;
			}
		}
		else {
			// Database table for non recognized locations doesn't exists so create it.
			// We need it in a minute to prevent google api queries that we already know won't yield results
			$temp = $dbh->query("SHOW TABLES LIKE 'humo_no_location'");
			if(!$temp->rowCount()) {
				// no such database table exists - so create it
				// (Re)create a location table "humo_no_location"
				// It has 2 columns:
				//     1. id
				//     2. name of location
				$nolocationtbl="CREATE TABLE humo_no_location (
					no_location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					no_location_location VARCHAR(100) CHARACTER SET utf8
				)";
				$dbh->query($nolocationtbl);
			}
		}
		
		$thistree_non_exist= array(); 
		// This will hold only those non-indexable locations (from $non_exist_locs) that appear in the chosen tree (or trees if 'all' was chosen)
		
		while (@$personDb=$map_person->fetch(PDO::FETCH_OBJ)){
			// for each location we check:
			// 1. if it has already been indexed (if so, skip it)
			// 2. if in the past it couldn't be found by google api (if so, skip it)
			// If neither of these two cases - add it to the array of locations to be queried through google api ($add_locations)
		
			if($is_database===true) {
				// there is a database - see if the location already exists and if so - continue with a next loop
				foreach($exist_locs AS $value) {
					if($value == $personDb->pers_birth_place) {  // this location has already been mapped
						continue 2;  //continue the outer while loop 
					}
				}
				if($is_noloc_database===true) { // stored list of non-indexable locations exists
					foreach($non_exist_locs AS $value) {
						if($value == $personDb->pers_birth_place) {  // this location cannot be mapped (not found by google api)
							$thistree_non_exist[]=$value;
							continue 2;  //continue the outer while loop
						}
					}
				}
			}
			// add the new location to an array for use if the user presses YES
			if($personDb->pers_birth_place) { $add_locations[] = $personDb->pers_birth_place; }
		}
		
/*
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
*/
		//echo 'Calculating......<br><br>'; // with a large existing data base and large number of locations to check this can take a second or two...
		if (!$add_locations) {
			echo '<p>'.__('No new locations were found to add to the database').'</p>';
			if($thistree_non_exist) {
				echo '<b>';
				printf(__('The following %d locations are already known as non-indexable by Google. Please check their validity.'),count($thistree_non_exist));
				echo '</b><br>';
				foreach($thistree_non_exist AS $value) {
					echo $value."<br>";
				}
			}
		}

		else {
			$_SESSION['add_locations']=$add_locations;
			$new_locations = count($add_locations);
			$map_totalsecs = $new_locations * 1.25;
			$map_mins = floor($map_totalsecs / 60);
			$map_secs = $map_totalsecs % 60;
			$one_tree="";
			if(isset($_SESSION['geo_tree']) AND $_SESSION['geo_tree'] != "all_geo_trees") {
				$tree_search_sql2 = "SELECT * FROM humo_trees WHERE tree_id='".$_SESSION['geo_tree']."'";
				$tree_search_result2 = $dbh->query($tree_search_sql2);
				$tree_searchDb2=$tree_search_result2->fetch(PDO::FETCH_OBJ);
				$treetext2=show_tree_text($tree_searchDb2->tree_id, $selected_language);
				$one_tree= "<b>".__('Family tree')." ".@$treetext2['name'].": </b>";
			}
			echo $one_tree;
			printf(__('There are %s new unique birth/ death locations to add to the database.'), $new_locations);
			echo '<br>';
			printf(__('This will take approximately <b>%1$d minutes and %2$d seconds.</b>'), $map_mins, $map_secs);
			echo '<br>';
			echo __('Do you wish to add these locations to the database database now?').'<br>';
			echo '<form action="index.php?page=google_maps" method="post">';
			echo '<input type="submit" style="font-size:14px" value="'.__('YES').'" name="makedatabase">';
			echo '</form><br>';

			if($thistree_non_exist) {
				echo "<br><b>";
				printf(__('The following %d locations are already known as non-indexable by Google. Please check their validity.'),count($thistree_non_exist));
				echo '</b><br>';
				foreach($thistree_non_exist AS $value) {
					echo $value."<br>";
				}
			}
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

		// SELECT FAMILY TREE
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$tree_search_result = $dbh->query($tree_search_sql);
		$count=0;
		echo '<br><form method="POST" action="index.php?page=google_maps" style="display : inline;">';
		//echo '<select size="1" name="tree_prefix" onChange="this.form.submit();">';
		echo '<select size="1" name="database" onChange="this.form.submit();">';
			//echo '<option value="">'.__('Select a family tree:').'</option>';
			$selected=''; 	
			if (!isset($_SESSION['geo_tree']) OR (isset($_POST['database']) AND $_POST['database']=="all_geo_trees") )  { 
				$selected=' SELECTED';  
				$_SESSION['geo_tree']="all_geo_trees";
			}
			echo '<option value="all_geo_trees"'.$selected.'>'.__('All family trees').'</option>';
			while ($tree_searchDb=$tree_search_result->fetch(PDO::FETCH_OBJ)){ 

				$selected='';
				if (isset($_POST['database'])){   
					if ($tree_searchDb->tree_prefix==$_POST['database']){ 
						$selected=' SELECTED';
						$_SESSION['geo_tree']=$tree_searchDb->tree_id;
					}
				}
				else { 
					if(isset($_SESSION['geo_tree']) AND $_SESSION['geo_tree'] ==$tree_searchDb->tree_id) { 
						$selected=' SELECTED';
					}
				}
				$treetext=show_tree_text($tree_searchDb->tree_id, $selected_language);
				echo '<option value="'.$tree_searchDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
				$count++;
			}
		echo '</select>';
		echo '</form><br>';

		echo '<form method="POST" name="checkform" action="index.php?page=google_maps" style="display : inline;">';
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
			echo '<form action="index.php?page=google_maps" method="post">';
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
				$result = $dbh->query("SELECT * FROM humo_location WHERE location_id = ".safe_text_db($_POST['loc_find']));
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

		$api_key = '';
		if(isset($humo_option['google_api_key']) AND $humo_option['google_api_key']!='') {
			$api_key = "?key=".$humo_option['google_api_key']; 
		}
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') { 
			echo '<script type="text/javascript" src="https://maps.google.com/maps/api/js'.$api_key.'"></script>';
		}
		else {
			echo '<script type="text/javascript" src="https://maps.google.com/maps/api/js'.$api_key.'"></script>';
		}
		?>

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
			echo '<span style="color:red;font-weight:bold;">'.__('Deleted location:').str_replace("\'","'",safe_text_db($_POST['loc_del_name'])).'</span><br>';
		}
		if(isset($_POST['loc_change']) OR isset($_POST['yes_change']) OR isset($_POST['cancel_change'])) {
			// "change" location or "yes" button pressed
			$pos = strpos($_POST['add_name'],$_POST['loc_del_name']);

			if(!isset($_POST['cancel_change']) AND ($pos !== false OR isset($_POST['yes_change']))) {  // the name in pulldown appears in the name in the search box
				$dbh->query("UPDATE humo_location SET location_location ='".safe_text_db($_POST['loc_del_name'])."', location_lat =".floatval($_POST['add_lat']).", location_lng = ".floatval($_POST['add_lng'])." WHERE location_location = '".safe_text_db($_POST['loc_del_name'])."'");
				echo '<span style="color:red;font-weight:bold;">'.__('Changed location:').' '.str_replace("\'","'",safe_text_db($_POST['loc_del_name'])).'</span><br>';
			}
			elseif(isset($_POST['cancel_change'])) {
				$leave_bottom = true;
			}
			else {
				$leave_bottom = true;
				echo '<span style="color:red;font-weight:bold;">Are you sure you want to change the lat/lng of </span><b>'.$_POST['loc_del_name'].'</b>';
				echo '<span style="color:red;font-weight:bold;"> and set them to those that belong to </span><b>'.$_POST['add_name'].'?</b></span><br>';
				echo '<form method="POST" name="check_change" action="index.php?page=google_maps" style="display : inline;">';
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
				echo '<span style="color:red;font-weight:bold;">'.__('Added location:').' '.str_replace("\'","'",safe_text_db($_POST['add_name'])).'</span><br>';
			}
			else { // location already exists, just update the lat/lng
				$dbh->query("UPDATE humo_location SET location_location ='".$_POST['add_name']."', location_lat =".floatval($_POST['add_lat']).", location_lng = ".floatval($_POST['add_lng'])." WHERE location_location = '".safe_text_db($_POST['add_name'])."'");
				echo '<span style="color:red;font-weight:bold;"> '.str_replace("\'","'",safe_text_db($_POST['add_name'])).': Location already exists.<br>Updated lat/lng.</span><br>';
			}
		}

		echo '<form method="POST" name="dbform" action="index.php?page=google_maps" style="display : inline;">';
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

		echo '<form method="POST" name="delform" action="index.php?page=google_maps" style="display : inline;">';
		echo '<tr><th colspan="2">'.__('Details from the database').'</th></tr>';
		echo '<tr><td>'.__('Location').':</td><td><input type="text" id="loc_name" name="loc_name" value="'.$resultDb->location_location.'" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td></tr>';
		echo '<tr><td>'.__('Latitude').':</td><td><input type="text" id="loc_lat" name="loc_lat" value="'.$resultDb->location_lat.'" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td></tr>';
		echo '<tr><td>'.__('Longitude').':</td><td><input type="text" id="loc_lng" name="loc_lng" value="'.$resultDb->location_lng.'" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td></tr>';
		echo '<tr><td align="center" colspan="2">';
		echo '<input type="hidden" name="loc_del_id" value="'.$resultDb->location_id.'">';
		echo '<input type="hidden" name="loc_del_name" value="'.$resultDb->location_location.'">';
		echo '<input type="Submit" style="color:red;font-weight:bold" name="loc_delete" value="'.__('Delete this location').'"></td></tr>';
		//echo '</form>';

		//echo '<form method="POST" name="searchform" action="index.php?page=google_maps" style="display : inline;">';
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
			echo '<br><form action="index.php?page=google_maps" method="post">';
			echo '<input type="submit" style="font-size:14px;color:red;font-weight:bold" value="'.__('DELETE ENTIRE GEOLOCATION DATABASE').'" name="deletedatabase">';
			echo '<br></form><br>';
		}
		if(isset($_POST['refresh_no_locs'])) {
			echo '<p style="color:red;font-weight:bold;">'.__('List of non-indexable locations was refreshed!').'<p>';
		}
		else {  
			$temp1 = $dbh->query("SHOW TABLES LIKE 'humo_no_location'");
			if($temp1->rowCount() > 0) {
				$no_loc_list = $dbh->query("SELECT * FROM humo_no_location ORDER BY no_location_location");
				$num_rows1 = $no_loc_list->rowCount();
				if($num_rows1>0) {
					printf(__('Here you can refresh the list of %d non-indexable locations that was stored in your database after previous geolocation processes.<br>
		Do this if you have corrected non-indexable locations in your data or have imported updated gedcoms and some of these locations may no longer appear in your data.'), $num_rows1);
					echo '<br><form action="index.php?page=google_maps" method="post">';
					echo '<input type="submit" style="font-size:14px;color:red;font-weight:bold" value="'.__('REFRESH LIST OF NON-INDEXABLE LOCATIONS').'" name="refresh_no_locs">';
					echo '<br></form><br>';
				}
			}
		}
		
		echo '</td></tr>';
		//~~~~~~~~~~~~~~~~~~~~~~~~~~~ SETTINGS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

		//echo '<tr bgcolor="green"><th><font color="white">'.__('Settings').'</font></th></tr>';
		echo '<tr class="table_header"><th>'.__('Settings').'</th>';
		echo '<tr><td>';
		echo '<form name="slider" action="index.php?page=google_maps" method="POST">';
		echo __('The slider has 10 steps. By default the starting year is 1560 with 9 intervals of 50 years up till 2010 and beyond.<br>
You can set the starting year yourself for each tree, to suit it to the earliest years in that tree<br>
The 9 intervals will be calculated automatically. Some example starting years for round intervals:<br>
1110 (intv. 100), 1560 (intv. 50), 1695 (intv. 35),1740 (intv. 30), 1785 (intv. 25), 1830 (intv. 20)').'<br><br>';

		// *** Select family tree ***
		$tree_id_string = " AND ( ";
		$id_arr = explode(";",substr($humo_option['geo_trees'],0,-1)); // substr to remove trailing ;
		foreach($id_arr as $value) {
			$tree_id_string .= "tree_id='".substr($value,1)."' OR ";  // substr removes leading "@" in geo_trees setting string
		}
		$tree_id_string = substr($tree_id_string,0,-4).")"; // take off last " ON " and add ")"

		$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ".$tree_id_string." ORDER BY tree_order"; 
		$tree_search_result = $dbh->query($tree_search_sql);
		echo '<table><tr><th>'.__('Name of tree').'</th><th style="text-align:center">'.__('Starting year').'</th>';
		echo '<th style="text-align:center">'.__('Interval').'</th>';
		$rowspan = $tree_search_result->rowCount() + 1;
		echo '<th rowspan='.$rowspan.'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="Submit" name="submit" value="'.__('Change').'"></th></tr>';
		echo '<form method="POST" action="maps.php" style="display : inline;">';

		while ($tree_searchDb=$tree_search_result->fetch(PDO::FETCH_OBJ)){
			${"slider_choice".$tree_searchDb->tree_prefix}="1560"; // default
			$query = "SELECT * FROM humo_settings WHERE setting_variable='gslider_".$tree_searchDb->tree_prefix."' ";
			$result = $dbh->query($query);
			$offset="slider_choice_".$tree_searchDb->tree_prefix;
			if ($result->rowCount() >0) {
				$slider_choiceDb = $result->fetch(PDO::FETCH_OBJ);
				${"slider_choice".$tree_searchDb->tree_prefix} = $slider_choiceDb->setting_value;
				if(isset($_POST[$offset])) {
					$sql="UPDATE humo_settings SET setting_value='".$_POST[$offset]."' WHERE setting_variable='gslider_".$tree_searchDb->tree_prefix."'";
					$dbh->query($sql);
					${"slider_choice".$tree_searchDb->tree_prefix}=$_POST[$offset];
				}
			}
			else {
				if(isset($_POST[$offset])) {
					$sql="INSERT INTO humo_settings SET setting_variable='gslider_".$tree_searchDb->tree_prefix."', setting_value='".$_POST[$offset]."'";
					$dbh->query($sql);
					${"slider_choice".$tree_searchDb->tree_prefix}=$_POST[$offset];
				}
			}

			$treetext=show_tree_text($tree_searchDb->tree_id, $selected_language);
			echo "<tr><td>".$treetext['name']."</td>";
			echo "<td><input style='text-align:center' type='text' name='".$offset."' value='${"slider_choice".$tree_searchDb->tree_prefix}'></td>";
			$interval = round((2010 - ${"slider_choice".$tree_searchDb->tree_prefix})/9);
			echo "<td style='text-align:center'>".$interval."</td></tr>";
			//echo '<td><input type="Submit" name="submit" value="'.__('Change').'"></td></tr>';

		}
		echo '</table>';  // end list of trees and starting years

		echo '</form>';

		echo '<br>'.__('Default slider position').": ";
		$query = "SELECT * FROM humo_settings WHERE setting_variable='gslider_default_pos' ";
		$result = $dbh->query($query);

		if(isset($_GET['slider_default'])) {
			if ($result->rowCount() >0) { 
				$sql="UPDATE humo_settings SET setting_value ='".$_GET['slider_default']."' WHERE setting_variable='gslider_default_pos'";
				$dbh->query($sql);
				$sl_def=$_GET['slider_default'];
			}
			else {
				$sql="INSERT INTO humo_settings SET setting_variable='gslider_default_pos', setting_value='".$_GET['slider_default']."'";
				$dbh->query($sql);
				$sl_def=$_GET['slider_default'];
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


		echo '<select size="1" name="slider_default" id="slider_default" onChange="window.location=\'index.php?page=google_maps&slider_default=\'+this.value;">';
		$selected = ""; if($sl_def=="off") $selected=" SELECTED ";
		echo '<option value="off" '.$selected.'>'.__('OFF position (leftmost position)').'</option>';
		$selected = ""; if($sl_def=="all") $selected=" SELECTED ";
		echo '<option value="all" '.$selected.'>'.__('Show all periods (rightmost position)').'</option>';
		echo '</select>';

//NEW

		echo '<br><br>'.__('Default map type').": ";
		$query = "SELECT * FROM humo_settings WHERE setting_variable='google_map_type' ";
		$result = $dbh->query($query);

		if(isset($_GET['maptype_default'])) {
			if ($result->rowCount() >0) { 
				$sql="UPDATE humo_settings SET setting_value ='".$_GET['maptype_default']."' WHERE setting_variable='google_map_type'";
				$dbh->query($sql);
				$maptype_def=$_GET['maptype_default'];
			}
			else {
				$sql="INSERT INTO humo_settings SET setting_variable='google_map_type', setting_value='".$_GET['maptype_default']."'";
				$dbh->query($sql);
				$maptype_def=$_GET['maptype_default'];
			}
		}
		else {
			if ($result->rowCount() >0) {
				$maptype_default=$result->fetch();
				$maptype_def = $maptype_default['setting_value'];
			}
			else {
				$maptype_def="ROADMAP";
			}
		}


		echo '<select size="1" name="maptype_default" id="maptype_default" onChange="window.location=\'index.php?page=google_maps&maptype_default=\'+this.value;">';
		$selected = ""; if($maptype_def=="ROADMAP") $selected=" SELECTED ";
		echo '<option value="ROADMAP" '.$selected.'>'.__('Regular map (ROADMAP)').'</option>';
		$selected = ""; if($maptype_def=="HYBRID") $selected=" SELECTED ";
		echo '<option value="HYBRID" '.$selected.'>'.__('Satellite map with roads and places (HYBRID)').'</option>';
		echo '</select>';

		echo '<br><br>'.__('Default zoom').": ";
		$query = "SELECT * FROM humo_settings WHERE setting_variable='google_map_zoom' ";
		$result = $dbh->query($query);

		if(isset($_GET['map_zoom_default'])) {
			if ($result->rowCount() >0) { 
				$sql="UPDATE humo_settings SET setting_value ='".$_GET['map_zoom_default']."' WHERE setting_variable='google_map_zoom'";
				$dbh->query($sql);
				$mapzoom_def=$_GET['map_zoom_default'];
			}
			else {
				$sql="INSERT INTO humo_settings SET setting_variable='google_map_zoom', setting_value='".$_GET['map_zoom_default']."'";
				$dbh->query($sql);
				$mapzoom_def=$_GET['map_zoom_default'];
			}
		}
		else {
			if ($result->rowCount() >0) {
				$mapzoom_default=$result->fetch();
				$mapzoom_def = $mapzoom_default['setting_value'];
			}
			else {
				$mapzoom_def="11";
			}
		}


		echo '<select size="1" name="map_zoom_default" id="map_zoom_default" onChange="window.location=\'index.php?page=google_maps&map_zoom_default=\'+this.value;">';
		for($x=1;$x<15;$x++) {
			$selected = ""; if($mapzoom_def==$x) $selected=" SELECTED ";
			echo '<option value="'.$x.'" '.$selected.'>'.$x.'</option>';
		}
		echo '</select>';

//END NEW
		//echo '</form>';
		echo '</td></tr>';
	}
}
//else {
//			echo '<p>'.__('No geolocation database found').'</p>';
//}
echo '</table>';  // end google maps admin

// function to refresh location_status column
function refresh_status() {
	global $dbh, $humo_option;

	// make sure the location_status column exists. If not create it
	$result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
	$exists = $result->rowCount();
	if(!$exists) {
		$dbh->query("ALTER TABLE humo_location ADD location_status TEXT AFTER location_lng");
	}

	$all_loc = $dbh->query("SELECT location_location FROM humo_location");
	while($all_locDb = $all_loc->fetch(PDO::FETCH_OBJ)) {
		$loca_array[$all_locDb->location_location] = "";
	}
	$status_string = "";

	$tree_id_string = " WHERE ";
	$id_arr = explode(";",substr($humo_option['geo_trees'],0,-1)); // substr to take off last ;
	foreach($id_arr as $value) {
		$tree_id_string .= "pers_tree_id='".substr($value,1)."' OR ";   // substr removes leading "@" in geo_trees setting string
	}
	$tree_id_string = substr($tree_id_string,0,-4); // take off last " OR"

	$result=$dbh->query("SELECT pers_tree_id, pers_tree_prefix, pers_birth_place, pers_bapt_place, pers_death_place, pers_buried_place
		FROM humo_persons".$tree_id_string);
	while($resultDb = $result->fetch(PDO::FETCH_OBJ)) {
		if (isset($loca_array[$resultDb->pers_birth_place]) AND strpos($loca_array[$resultDb->pers_birth_place],$resultDb->pers_tree_prefix."birth ")===false) {
			$loca_array[$resultDb->pers_birth_place] .= $resultDb->pers_tree_prefix."birth ";
		}
		if (isset($loca_array[$resultDb->pers_bapt_place]) AND strpos($loca_array[$resultDb->pers_bapt_place],$resultDb->pers_tree_prefix."bapt ")===false) {
			$loca_array[$resultDb->pers_bapt_place] .= $resultDb->pers_tree_prefix."bapt ";
		}
		if (isset($loca_array[$resultDb->pers_death_place]) AND strpos($loca_array[$resultDb->pers_death_place],$resultDb->pers_tree_prefix."death ")===false) {
			$loca_array[$resultDb->pers_death_place] .= $resultDb->pers_tree_prefix."death ";
		}
		if (isset($loca_array[$resultDb->pers_buried_place]) AND strpos($loca_array[$resultDb->pers_buried_place],$resultDb->pers_tree_prefix."buried ")===false) {
			$loca_array[$resultDb->pers_buried_place] .= $resultDb->pers_tree_prefix."buried ";
		}
	}
 
	foreach($loca_array as $key => $value) {
		if(isset($_POST['purge']) AND ($value == "" OR $value == NULL)) {
			$dbh->query("DELETE FROM humo_location WHERE location_location = '".addslashes($key)."'");
		}
		else {
			$dbh->query("UPDATE humo_location SET location_status = '".$value."' WHERE location_location = '".addslashes($key)."'");
		}
	}
}

?>