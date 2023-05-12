<?php
include_once("header.php"); // returns CMS_ROOTPATH constant
//error_reporting(E_ALL);

include_once(CMS_ROOTPATH."menu.php");
include_once(CMS_ROOTPATH.'include/person_cls.php');
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");

echo '<script src="'.CMS_ROOTPATH.'googlemaps/namesearch.js"></script>';

// *** OpenStreetMap ***
if(isset($humo_option["use_world_map"]) AND $humo_option["use_world_map"]=='OpenStreetMap') {
	//dummy
}
else{
	//cover map with loading animation + half opaque background till page is fully loaded
	//using the slider/button before complete page load goes wrong
	echo '<div id="wait" style="background:url(images/loader.gif) no-repeat center center; opacity:0.6; filter:alpha(opacity=60); position:fixed; top:70px; margin-left:auto; margin-right:auto; height:610px; width:1000px; background-color:#000000; z-index:100"></div>';
}

echo '<div style="position:relative"> ';  // div with table for all menu bars (2 + optional third)
echo '<table>';

// 1st MENU BAR
echo '<tr><td style="font-size:110%;border:1px solid #d8d8d8;width:995px;background-color:#f2f2f2">';
echo '&nbsp;&nbsp;'.__('Display birth or death locations across different time periods');

// SELECT FAMILY TREE
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

$tree_id_string = " AND ( ";
$id_arr = explode(";",substr($humo_option['geo_trees'],0,-1)); // substr to remove trailing ";"
foreach($id_arr as $value) {
	$tree_id_string .= "tree_id='".substr($value,1)."' OR ";  // substr removes leading "@" in geo_trees setting string
}
$tree_id_string = substr($tree_id_string,0,-4).")"; // take off last " ON " and add ")"

$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ".$tree_id_string." ORDER BY tree_order";
$tree_search_result = $dbh->query($tree_search_sql);
$count=0;
echo '<form method="POST" action="maps.php" style="display : inline;">';
echo '<select size="1" name="database" onChange="this.form.submit();">';
	echo '<option value="">'.__('Select a family tree:').'</option>';
	while ($tree_searchDb=$tree_search_result->fetch(PDO::FETCH_OBJ)){
		// *** Check if family tree is shown or hidden for user group ***
		$hide_tree_array=explode(";",$user['group_hide_trees']);
		$hide_tree=false;
		if (in_array($tree_searchDb->tree_id, $hide_tree_array)) $hide_tree=true;
		if ($hide_tree==false){
			$selected='';
			if (isset($_SESSION['tree_prefix'])){
				if ($tree_searchDb->tree_prefix==$_SESSION['tree_prefix']){
					$selected=' SELECTED';
					$tree_id=$tree_searchDb->tree_id;
					$_SESSION['tree_id']=$tree_id;
					$db_functions->set_tree_id($tree_id);
				}
			}
			else {
				if($count==0) {
					$_SESSION['tree_prefix'] = $tree_searchDb->tree_prefix;
					$selected=' SELECTED';
					$tree_id=$tree_searchDb->tree_id;
					$_SESSION['tree_id']=$tree_id;
					$db_functions->set_tree_id($tree_id);
				}
			}
			$treetext=show_tree_text($tree_searchDb->tree_id, $selected_language);
			echo '<option value="'.$tree_searchDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
			$count++;
		}
	}
echo '</select>';
echo '</form>';

// SET BIRTH OR DEATH MAP
if(!isset($_SESSION['type_birth']) AND !isset($_SESSION['type_death'])) { $_SESSION['type_birth']=1; $_SESSION['type_death']=0; }
if(isset($_POST['map_type']) AND $_POST['map_type']=="type_birth" ) { $_SESSION['type_birth']=1; $_SESSION['type_death']=0; }
if(isset($_POST['map_type']) AND $_POST['map_type']=="type_death" ) { $_SESSION['type_death']=1; $_SESSION['type_birth']=0; }

// PULL-DOWN: births/bapt OR death/burial
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Display:').'&nbsp;';
echo '<form name="type_form" method="POST" action="" style="display : inline;">';
	echo '<select style="max-width:200px" size="1" onChange="document.type_form.submit()" id="map_type" name="map_type">';
	$selected=''; if(isset($_SESSION['type_birth']) AND $_SESSION['type_birth']==1 ) { $selected = ' selected '; }
	echo '<option value="type_birth" '.$selected.'>'.__('Birth locations').'</option>';
	$selected=''; if(isset($_SESSION['type_death']) AND $_SESSION['type_death']==1 )  { $selected = ' selected '; }
	echo '<option value="type_death" '.$selected.'>'.__('Death locations').'</option>';
	echo '</select>';
echo '</form>';

echo '</td></tr>';

// *** OpenStreetMap ***
if(isset($humo_option["use_world_map"]) AND $humo_option["use_world_map"]=='OpenStreetMap') {
	//dummy
}
else{

// 2nd MENU BAR
echo '<tr><td style="border:1px solid #bdbdbd; width:995px; background-color:#d8d8d8">';

if($language['dir']!="rtl") { echo '<div style="margin-top:4px;font-size:110%;float:left">'; }  // div tree choice
else { echo '<div style="font-size:110%;float:right">'; }
	echo '&nbsp;&nbsp;'.__('Filters:').'&nbsp;&nbsp;';
echo '</div>';

if($language['dir']!="rtl") {echo '<div style="float:left">'; } // div slider text + year box
else { echo '<div style="float:right">'; }

// slider defaults
$realmin=1560;  // first year shown on slider
$step="50";     // interval
$minval="1510"; // OFF position (first year minus step, year is not shown)
$yr = date("Y");

// check for stored min value, created with google maps admin menu
$query = "SELECT setting_value FROM humo_settings WHERE setting_variable='gslider_".$tree_prefix_quoted."' ";
$result = $dbh->query($query);
if($result->rowCount() > 0) {
	$sliderDb=$result->fetch(PDO::FETCH_OBJ);
	$realmin = $sliderDb->setting_value;
	$step = floor(($yr - $realmin) / 9);
	$minval = $realmin - $step;
}

$qry="SELECT setting_value FROM humo_settings WHERE setting_variable='gslider_default_pos'";
$result = $dbh->query($qry);
if($result->rowCount() > 0) {
	$def = $result->fetch(); // defaults to array
	$slider_def = $def['setting_value'];
	if($slider_def=="off") { $defaultyr = $minval; $default_display = "------>"; $makesel=""; } // slider at leftmost position
	else { $defaultyr = $yr; $default_display = $defaultyr; $makesel = " makeSelection(3); "; } // slider ar rightmost position
}
else {
	//$defaultyr = $minval; $default_display = "------>"; $makesel=""; // slider at leftmost position 
	$defaultyr = $yr; $default_display = $defaultyr; $makesel = " makeSelection(3); ";  // slider at rightmost position (default)
}

echo '
	<script>
	var minval = '.$minval.';
	$(function() {
		// Set default slider setting
		'.$makesel.'
		$( "#slider" ).slider({
			value: '.$defaultyr.',
			min: '.$minval.',
			max: '.$yr.',
			step: '.$step.',
			slide: function( event, ui ) {
				if(ui.value == minval) { $( "#amount" ).val("----->"); }
				else if(ui.value > 2000) { $( "#amount" ).val('.$yr.'); }
				else {	$( "#amount" ).val(ui.value ); }
			}
		});
		$( "#amount" ).val("'.$default_display.'");

		// Only change map if value is changed.
		startPos = $("#slider").slider("value");
		$("#slider").on("slidestop", function(event, ui) {
			endPos = ui.value;
			if (startPos != endPos) {
				// Change map. This script can be found in: google_initiate.php.
				makeSelection(endPos);
			}
			startPos = endPos;
		});

	});
	</script>
';

// SLIDER
if($language['dir']!="rtl") { echo '<div style="float:left">'; } // div slider text + year box
	else { echo '<div style="float:right">'; }
if($_SESSION['type_birth']==1) {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Display births until: ').'&nbsp;';
}
elseif($_SESSION['type_death']==1) {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.__('Display deaths until: ').'&nbsp;';
}

echo '<input type="text" id="amount" disabled="disabled" size="4" style="border:0; color:#0000CC; font-weight:normal;font-size:115%;">';
echo '&nbsp;&nbsp;&nbsp;&nbsp;</div>';
if($language['dir']!="rtl"){ echo '<div id="slider" style="float:left;width:170px;margin-top:7px;margin-right:15px;">'; }
	else { echo '<div id="slider" style="float:right;direction:ltr;width:150px;margin-top:7px;margin-right:15px;">'; }

echo '</div>';

// BUTTON: SEARCH BY SPECIFIC NAME
echo ' <input type="Submit" style="font-size:110%;" name="anything" onclick="document.getElementById(\'namemapping\').style.display=\'block\' ;" value="'.__('Filter by specific family name(s)').'">';

// BUTTON: SEARCH BY DESCENDANTS
echo '<form method="POST" style="display:inline" name="descform" action="maps.php">';
	echo '<input type="hidden" name="descmap" value="1">';
	echo '&nbsp;&nbsp;&nbsp;<input type="Submit" style="font-size:110%;" name="anything" value="'.__('Filter by descendants').'">';
echo '</form>';

//echo '</td></tr>';

// BUTTON: SEARCH BY ANCESTORS
echo '<form method="POST" style="display:inline" name="ancform" action="maps.php">';
	echo '<input type="hidden" name="ancmap" value="1">';
	echo '&nbsp;&nbsp;&nbsp;<input type="Submit" style="font-size:110%;" name="anythingelse" value="'.__('Filter by ancestors').'">';
echo '</form>';

echo '</td></tr>';

// 3rd MENU BAR
echo '<tr><td style="border:1px solid #d8d8d8;width:995px;background-color:#f2f2f2">';

if($language['dir']!="rtl") { echo '<div style="margin-top:4px;font-size:110%;float:left">'; }  
else { echo '<div style="font-size:120%;float:right">'; }
echo '&nbsp;&nbsp;'.__('Other tools:').'&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</div>';
/*
// BIRTH LOCATION BUTTON
echo  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
if($_SESSION['type_birth']==1) {
	echo  ' <input style="font-size:14px" type="button" value="'.__('Mark all birth locations').'" onclick="makeSelection(3)">  ';
}
elseif($_SESSION['type_death']==1) {
	echo  ' <input style="font-size:14px" type="button" value="'.__('Mark all death locations').'" onclick="makeSelection(3)">  ';
}
echo '</div>';
*/

// HELP POPUP
if(CMS_SPECIFIC=="Joomla") {
	echo '<div class="fonts '.$rtlmarker.'sddm" style="z-index:400; position:absolute; top:20px; left:10px;">';
	$popwidth="width:700px;";
}
else {
	echo '<div class="fonts '.$rtlmarker.'sddm" style="border:1px solid #d8d8d8; margin-top:2px; display:inline; float:left;">';
	$popwidth="";
}
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#"';
echo ' style="display:inline" ';
if(CMS_SPECIFIC=="Joomla") {
	echo 'onmouseover="mopen(event,\'help_menu\',0,0)"';
}
else {
	echo 'onmouseover="mopen(event,\'help_menu\',10,150)"';
}
echo 'onmouseout="mclosetime()">';
echo '<strong>'.__('Help').'</strong>';
echo '</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<div class="sddm_fixed" style="'.$popwidth.' z-index:400; text-align:'.$alignmarker.'; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

echo __('<b>Top menu line:</b>
<ul><li>Choose family tree. On sites with multiple family trees here you can choose which tree to map.</li>
<li>Choose whether to display birth location or death locations.</li></ul>
<b>Second menu line:</b>
<ul><li>The "births until" slider. With this slider you can mark the birthplaces of persons who were born until a certain date. The slider has ten positions.</li>
<li>Filter by specific family name(s)". This will open a window with all family names. Mark checkboxes next to names and press "Choose" to start mapping the locations of those families only.<br>
After pressing "Choose" you will see a yellow banner near the top of the map, informing you which family names are being filtered. Now use the slider or "mark all location" button to place the markers.</li>
<li>Filter by descendants". This will open a window with all persons that have descendants. Click the person whose descendants you want to map.<br>
A yellow banner will appear near the top of the map, informing which persons\' descendants are filtered. Now use the slider or "mark all location" button to place the markers.</li></ul>
<b>Third menu line:</b>
<ul><li>Find location on the map". Here you can pick a location from all locations in the tree and zoom in to it automatically.</li></ul>
<b>The map:</b>
<ul><li>Colored markers are placed on the map according to the settings made in the menu. Inside the marker you will see the number of people born in that location.</li>
<li>There are 4 different size markers (from small to big): Red markers (over 100 people), blue markers (50-99 people), green markers (9-49 people) and yellow markers (1-9 people)</li>
<li>When you hover with the mouse over a marker a "tooltip" will show with the name of the location.</li>
<li>When you click on the marker you will see two links.</li>
<li>The first link will open a new browser tab with the Wikipedia entry about this location (if such an entry exists).</li>
<li>The second link will present (in the Info Window itself) a list of all persons born in this location.</li>
<li>The names in this list are clickable and will open a new browser tab with the family page of this person.</li>');

echo '</ul>';
echo '</div>';
echo '</div>';

// PULL-DOWN: FIND LOCATION
echo  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
if($result->rowCount()>0) {
	if($_SESSION['type_birth']==1) {
		$loc_search = "SELECT * FROM humo_location WHERE location_status LIKE '%".$tree_prefix_quoted."birth%' OR location_status LIKE '%".$tree_prefix_quoted."bapt%' OR location_status = '' ORDER BY location_location";
	}
	if($_SESSION['type_death']==1) {
		$loc_search = "SELECT * FROM humo_location WHERE location_status LIKE '%".$tree_prefix_quoted."death%' OR location_status LIKE '%".$tree_prefix_quoted."buried%' OR location_status = '' ORDER BY location_location";
	}
}
else {
	// this is for backward compatibility - if someone doesn't yet have a location_status column: show all locations as until now
	$loc_search = "SELECT * FROM humo_location ORDER BY location_location";
}
$loc_search_result = $dbh->query($loc_search); if($loc_search_result !== false) 
echo '<form method="POST" action="" style="display : inline;">';
	echo '<select style="max-width:250px" onChange="findPlace()" size="1" id="loc_search" name="loc_search">';
		echo '<option value="toptext">'.__('Find location on the map').'</option>';
		while($loc_searchDb=$loc_search_result->fetch(PDO::FETCH_OBJ)) {
			echo '<option value="'.$loc_searchDb->location_id.','.$loc_searchDb->location_lat.','.$loc_searchDb->location_lng.'">'.$loc_searchDb->location_location.'</option>';
			$count++;
		}
echo '</select>';
echo '</form>';

echo '</td></tr>';


// OPTIONAL 4th (YELLOW) NOTIFICATION MENU BAR
echo '<tr><td style="border:1px solid #bdbdbd; width:995px; background-color:#d8d8d8">';

// NOTIFICATION: SEARCHING BY SPECIFIC NAMES
$flag_namesearch='';
if(isset($_POST['items'])) {
	// for use in google_initiate.php
	echo '<div id="name_search" style="border: 0px solid #bdbdbd;background-color:#f3f781;">';
	$flag_namesearch = $_POST['items'];
	$names='';
	echo '&nbsp;'.__('Mapping with specific name(s): ');
	foreach($flag_namesearch as $value) {
		$pos = strpos($value,'_');
		$pref=''; $last='';
		$last = substr($value,0,$pos);
		$pref = substr($value,$pos+1); if($pref!='') { $pref = $pref.' '; }
		//$names .= $value.", ";
		$names .= $pref.$last.", ";
	}
	$names = substr($names,0,-2); // take off last ", "
	echo $names;
	echo '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="maps.php">'.__('Switch name filter off').'</a>';
	echo '</div>';
}

// FUNCTION TO FIND DESCENDANTS OF CHOSEN PERSON AND SHOW NOTIFICATION
$flag_desc_search=0; $chosenperson=''; $persfams = '';
if(isset($_GET['persged']) AND isset($_GET['persfams'])) {
	$flag_desc_search=1;
	$chosenperson= $_GET['persged'];
	$persfams = $_GET['persfams'];
	$persfams_arr = explode(';',$persfams);
	$myresult = $dbh->query("SELECT pers_lastname, pers_firstname, pers_prefix FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$chosenperson."'");
	$myresultDb=$myresult->fetch(PDO::FETCH_OBJ);
	$chosenname = $myresultDb->pers_firstname.' '.strtolower(str_replace('_','',$myresultDb->pers_prefix)).' '.$myresultDb->pers_lastname;

	$gn=0; // generation number

	function outline($family_id,$main_person,$gn) {
		global $dbh, $db_functions, $desc_array;
		global $language, $dirmark1, $dirmark1;
		$family_nr=1; //*** Process multiple families ***

		$familyDb = $db_functions->get_family($family_id,'man-woman');
		$parent1=''; $parent2=''; $swap_parent1_parent2=false;

		// *** Standard main_person is the father ***
		if ($familyDb->fam_man){
			$parent1=$familyDb->fam_man;
		}
		// *** If mother is selected, mother will be main_person ***
		if ($familyDb->fam_woman==$main_person){
			$parent1=$familyDb->fam_woman;
			$swap_parent1_parent2=true;
		}

		// *** Check family with parent1: N.N. ***
		if ($parent1){
			// *** Save man's families in array ***
			@$personDb=$db_functions->get_person($parent1,'famc-fams');
			$marriage_array=explode(";",$personDb->pers_fams);
			$nr_families=substr_count($personDb->pers_fams, ";");
		}
		else{
			$marriage_array[0]=$family_id;
			$nr_families="0";
		}

		// *** Loop multiple marriages of main_person ***
		for ($parent1_marr=0; $parent1_marr<=$nr_families; $parent1_marr++){
			@$familyDb = $db_functions->get_family($marriage_array[$parent1_marr]);

			// *** Privacy filter man and woman ***
			@$person_manDb = $db_functions->get_person($familyDb->fam_man);
			@$person_womanDb = $db_functions->get_person($familyDb->fam_woman);

			// *************************************************************
			// *** Parent1 (normally the father)                         ***
			// *************************************************************
			if ($familyDb->fam_kind!='PRO-GEN'){  //onecht kind, vrouw zonder man
				if ($family_nr==1){
				// *** Show data of man ***

					if ($swap_parent1_parent2==true){
						if($person_womanDb->pers_birth_place OR $person_womanDb->pers_bapt_place) {
							$desc_array[]=$person_womanDb->pers_gedcomnumber;
						}
					}
					else{
						if($person_manDb->pers_birth_place OR $person_manDb->pers_bapt_place) {
							$desc_array[]=$person_manDb->pers_gedcomnumber;
						}
					}
				}
				else{  }   // don't take person twice!
				$family_nr++;
			} // *** end check of PRO-GEN ***

			// *************************************************************
			// *** Children                                              ***
			// *************************************************************
			if ($familyDb->fam_children){
				//$childnr=1;
				$child_array=explode(";",$familyDb->fam_children);
				foreach ($child_array as $i => $value){
					@$childDb = $db_functions->get_person($child_array[$i]);

					// *** Build descendant_report ***
					if ($childDb->pers_fams){
						// *** 1e family of child ***
						$child_family=explode(";",$childDb->pers_fams);
						$child1stfam=$child_family[0];
						outline($child1stfam,$childDb->pers_gedcomnumber,$gn);  // recursive
					}
					else{    // Child without own family
						if($childDb->pers_birth_place OR $childDb->pers_bapt_place) {
							$desc_array[]=$childDb->pers_gedcomnumber;
						}
					}
				}
				//$childnr++;
			}

		} // Show  multiple marriages
	} // End of outline function

	// ******* Start function here - recursive if started ******
	//$desc_array = '';
	$desc_array = []; // Needed for PHP 7.x: creates an array

	outline($persfams_arr[0], $chosenperson, $gn);
	if($desc_array != '') {
		$desc_array = array_unique($desc_array); // removes duplicate persons (because of related ancestors)
	}
	echo '<div id="desc_search" style="border: 0px solid #bdbdbd;background-color:#f3f781;">';
	if($desc_array!='') {
		echo '&nbsp;'.__('Filter by descendants of: ').$chosenname.'&nbsp;&nbsp;<a href="maps.php">'.'&nbsp;|&nbsp;'.__('Switch descendant filter off').'</a>' ;
	}
	else {
		echo '&nbsp;'.__('No known birth places amongst descendants').'&nbsp;&nbsp;|&nbsp;&nbsp;<a href="maps.php">'.__('Close').'</a>';
	}
	echo '</div>';
} // end descendant notifications

// =============================
// FUNCTION TO FIND ANCESTORS OF CHOSEN PERSON AND SHOW NOTIFICATION
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/marriage_cls.php");
$flag_anc_search=0; $chosenperson=''; $persfams = '';
if(isset($_GET['anc_persged']) AND isset($_GET['anc_persfams'])) {
	$flag_anc_search=1;
	$chosenperson= $_GET['anc_persged'];
	$persfams = $_GET['anc_persfams'];
	$persfams_arr = explode(';',$persfams);
	$myresult = $dbh->query("SELECT pers_lastname, pers_firstname, pers_prefix FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$chosenperson."'");
	$myresultDb=$myresult->fetch(PDO::FETCH_OBJ);
//also check privacy
	$chosenname = $myresultDb->pers_firstname.' '.strtolower(str_replace('_','',$myresultDb->pers_prefix)).' '.$myresultDb->pers_lastname;

	function find_anc($family_id) { // function to find all ancestors - family_id = person GEDCOM number
		global $dbh, $db_functions, $anc_array;
		global $language, $dirmark1, $dirmark1;
		global $listed_array;
		$ancestor_array2[] = $family_id;
		$ancestor_number2[]=1;
		$marriage_gedcomnumber2[]=0;
		$generation = 1;

		//$listed_array=array();

		// *** Loop for ancestor report ***
		while (isset($ancestor_array2[0])){
			unset($ancestor_array);
			$ancestor_array=$ancestor_array2;
			unset($ancestor_array2);

			unset($ancestor_number);
			$ancestor_number=$ancestor_number2;
			unset($ancestor_number2);

			unset($marriage_gedcomnumber);
			$marriage_gedcomnumber=$marriage_gedcomnumber2;
			unset($marriage_gedcomnumber2);

			// *** Loop per generation ***
			for ($i=0; $i<count($ancestor_array); $i++) {

				$listednr='';

				foreach ($listed_array as $key => $value) {
					if($value==$ancestor_array[$i]) {$listednr=$key;}
					// if person was already listed, $listednr gets kwartier number for reference in report:
					// instead of person's details it will say: "already listed above under number 4234"
					// and no additional ancestors will be looked for, to prevent duplicated branches
				}
				if($listednr=='') {  //if not listed yet, add person to array
					$listed_array[$ancestor_number[$i]]=$ancestor_array[$i];  
					//$listed_array[]=$ancestor_array[$i];  
				}

				if ($ancestor_array[$i]!='0'){
					@$person_manDb = $db_functions->get_person($ancestor_array[$i]);

					// ==	Check for parents
					if ($person_manDb->pers_famc  AND $listednr==''){
 						@$family_parentsDb = $db_functions->get_family($person_manDb->pers_famc);
						if ($family_parentsDb->fam_man){
							$ancestor_array2[] = $family_parentsDb->fam_man;
							$ancestor_number2[]=(2*$ancestor_number[$i]);
							$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
						}

						if ($family_parentsDb->fam_woman){
							$ancestor_array2[]= $family_parentsDb->fam_woman;
							$ancestor_number2[]=(2*$ancestor_number[$i]+1);
							$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
						}
						else{
							// *** N.N. name ***
							$ancestor_array2[]= '0';
							$ancestor_number2[]=(2*$ancestor_number[$i]+1);
							$marriage_gedcomnumber2[]=$person_manDb->pers_famc;
						}
					}
				}
				else{
					// *** Show N.N. person ***
					@$person_manDb = $db_functions->get_person($ancestor_array[$i]);
					$listed_array[0] = $person_manDb->pers_gedcomnumber;
				}
			}	// loop per generation
			$generation++;
		}	// loop ancestor report

	}

	// ******* Start function here ******
	$anc_array = array();
	$listed_array=array();
	find_anc($chosenperson);
	foreach($listed_array as $value) {
		$anc_array[] = $value;
	}
/*	if($anc_array != '') {
		$anc_array = array_unique($anc_array); // removes duplicate persons (because of related ancestors)
	}
*/
	echo '<div id="anc_search" style="border: 0px solid #bdbdbd;background-color:#f3f781;">';

	if($anc_array!='') {
		echo '&nbsp;'.__('Filter by ancestors of: ').$chosenname.'&nbsp;&nbsp;<a href="maps.php">'.'&nbsp;|&nbsp;'.__('Switch ancestor filter off').'</a>' ;
	}
	else {
		echo '&nbsp;'.__('No known birth places amongst ancestors').'&nbsp;&nbsp;|&nbsp;&nbsp;<a href="maps.php">'.__('Close').'</a>';
	}
	echo '</div>';
} // end ancestor notifications
// END NEW =========================

echo '</td></tr>';
}  // *** Hide these items for OpenStreetMap ***

echo '</table>';
echo '</div>';
// END MENU

// FIXED WINDOW WITH LIST OF SPECIFIC FAMILY NAMES TO MAP BY
//$fam_search = "SELECT * , CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) as totalname
//	FROM humo_persons
//	WHERE pers_tree_id='".$tree_id."'
//	AND (pers_birth_place != '' OR (pers_birth_place='' AND pers_bapt_place != '')) AND pers_lastname != '' GROUP BY totalname ";
$fam_search = "SELECT CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) as totalname
	FROM humo_persons
	WHERE pers_tree_id='".$tree_id."'
	AND (pers_birth_place != '' OR (pers_birth_place='' AND pers_bapt_place != '')) AND pers_lastname != '' GROUP BY totalname ";
$fam_search_result = $dbh->query($fam_search);
echo '<div id="namemapping" style="display:none; z-index:100; position:absolute; top:90px; margin-left:10px; height:460px; width:250px; border:1px solid #000; background:#d8d8d8; color:#000; margin-bottom:1.5em;">';
	echo '<form method="POST" action="maps.php" name="yossi" style="display : inline;">';
		echo '<table style="z-index:200;"><tr><td style="text-align:center">'.__('Mark checkbox next to name(s)');
		echo '</td></tr><tr><td>';
		echo '<div style="z-index:110;height: 400px; width:241px; overflow: auto; border: 1px solid #000; background: #eee; color: #000; "> ';
		while($fam_searchDb=$fam_search_result->fetch(PDO::FETCH_OBJ)) {
			$pos = strpos($fam_searchDb->totalname,'_');
			$pref=''; $last='';
			$last = substr($fam_searchDb->totalname,0,$pos);
			$pref = substr($fam_searchDb->totalname,$pos+1); if($pref!='') { $pref = ', '.$pref; }
			echo '<input type="checkbox" name="items[]" value="'.$fam_searchDb->totalname.'">'.$last.$pref.'<br>';
		}
		echo '</div>';
		echo '</td></tr><tr><td style="text-align:center">';
		echo '<input type="Submit" name="submit" value="'.__('Choose').'">';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<input type="button" name="cancelfam" onclick="document.getElementById(\'namemapping\').style.display=\'none\';"  value="'.__('Cancel').'">';
		echo '</td></tr></table>';
	echo '</form>';
echo '</div>';


// FIXED WINDOW WITH LIST TO CHOOSE PERSON TO MAP WITH DESCENDANTS
if(isset($_POST['descmap'])) {

	//adjust pulldown for mobiles/tablets
	$select_size = 'size="20"'; $select_height = '400px';
	if (isset($_SERVER["HTTP_USER_AGENT"]) OR ($_SERVER["HTTP_USER_AGENT"] != "")) { //adjust pulldown for mobiles/tablets
		$visitor_user_agent = $_SERVER["HTTP_USER_AGENT"];
		if(strstr($visitor_user_agent,"Android")!== false OR
			strstr($visitor_user_agent,"iOS")!== false OR
			strstr($visitor_user_agent,"iPad")!== false OR
			strstr($visitor_user_agent,"iPhone")!== false ) {
			$select_size=""; $select_height= '100px';
		}
	}

	echo '<div id="descmapping" style="display:block; z-index:100; position:absolute; top:90px; margin-left:140px; height:'.$select_height.'; width:400px; border:1px solid #000; background:#d8d8d8; color:#000; margin-bottom:1.5em;z-index:20">';
	if($user['group_kindindex']=="j") { $orderlast = "CONCAT(pers_prefix,pers_lastname)"; }
		else { $orderlast = "pers_lastname"; }
	$desc_search = "SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_fams !='' ORDER BY ".$orderlast.", pers_firstname";
	$desc_search_result = $dbh->query($desc_search);
	echo '&nbsp;&nbsp;<strong>'.__('Filter by descendants of a person').'</strong><br>';
	echo '&nbsp;&nbsp;'.__('Pick a name or enter ID:').'<br>';
	echo '<form method="POST" action="" style="display : inline;">';
	echo '<select style="max-width:396px;background:#eee" '.$select_size.' onChange="window.location=this.value;" id="desc_map" name="desc_map">';
	echo '<option value="toptext">'.__('Pick a name from the pulldown list').'</option>';
	//prepared statement out of loop
	$chld_prep = $dbh->prepare("SELECT fam_children FROM humo_families
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber =? AND fam_children != ''");
	$chld_prep->bindParam(1,$chld_var);
	while($desc_searchDb=$desc_search_result->fetch(PDO::FETCH_OBJ)) {
		$countmarr = 0;
		$man_cls = New person_cls;
		$fam_arr = explode(";", $desc_searchDb->pers_fams);
		foreach ($fam_arr as $value) {
			if($countmarr==1) { break; } //this person is already listed
			$chld_var = $value;
			$chld_prep->execute();
			while($chld_search_resultDb = $chld_prep->fetch(PDO::FETCH_OBJ)) {
				$countmarr = 1;
				$selected='';
				//if($desc_searchDb->pers_gedcomnumber == $chosenperson) { $selected = ' SELECTED '; }
				$man_cls->construct($desc_searchDb);
				$privacy_man=$man_cls->privacy;
				$date='';
				if(!$privacy_man) {
					// if a person has privacy set (even if only for data, not for name,
					// we won't put them on the list. Most likely it concerns recent people.
					// Also, using the $man_cls->person_name functions takes too much time...
					$b_date=$desc_searchDb->pers_birth_date;
					$b_sign = __('born').' ';
					if (!$desc_searchDb->pers_birth_date AND $desc_searchDb->pers_bapt_date) {
						$b_date = $desc_searchDb->pers_bapt_date;
						$b_sign = __('baptised').' ';
					}
					$d_date=$desc_searchDb->pers_death_date;
					$d_sign = __('died').' ';
					if (!$desc_searchDb->pers_death_date AND $desc_searchDb->pers_buried_date) {
						$d_date = $desc_searchDb->pers_buried_date;
						$d_sign = __('buried').' ';
					}
					$date='';
					if($b_date AND !$d_date) {
						$date = ' ('.$b_sign.date_place($b_date,'').')';
					}
					if($b_date AND $d_date) {
						$date .= ' ('.$b_sign.date_place($b_date,'').' - '.$d_sign.date_place($d_date,'').')';
					}
					if(!$b_date AND $d_date) {
						$date = '('.$d_sign.date_place($d_date,'').')';
					}
					$name = ''; $pref = ''; $last = '- , '; $first = '-';
					if($desc_searchDb->pers_lastname) { $last = $desc_searchDb->pers_lastname.', '; }
					if($desc_searchDb->pers_firstname) { $first = $desc_searchDb->pers_firstname; }
					if($desc_searchDb->pers_prefix) { $pref = strtolower(str_replace('_','',$desc_searchDb->pers_prefix)); }

					if($user['group_kindindex']=="j") {
						if($desc_searchDb->pers_prefix) { $pref = strtolower(str_replace('_','',$desc_searchDb->pers_prefix)).' '; }
						$name = $pref.$last.$first;
					}
					else {
						if($desc_searchDb->pers_prefix) { $pref = ' '.strtolower(str_replace('_','',$desc_searchDb->pers_prefix)); }
						$name = $last.$first.$pref;
					}
					echo '<option value="maps.php?persged='.$desc_searchDb->pers_gedcomnumber.'&persfams='.$desc_searchDb->pers_fams.'" '.$selected.'>'.$name.$date.' [#'.$desc_searchDb->pers_gedcomnumber.']</option>';
				}
			}
		}
	}
	echo '</select>';
	echo '</form>';

	?>
	<script>
	function findGednr (pers_id) {
		for(var i=1;i<desc_map.length-1;i++) {
			if(desc_map.options[i].text.indexOf("[#" + pers_id + "]") != -1 || desc_map.options[i].text.indexOf("[#I" + pers_id + "]") != -1 ) { 
				window.location = desc_map.options[i].value;
			}
		}
	}
	</script>
	<?php
	echo '<br><div style="margin-top:5px;text-align:left">&nbsp;&nbsp;Find by ID (I324):<input id="id_field" type="text" style="font-size:120%;width:60px;" value=""><input type="button" value="'.__('Go!').'" onclick="findGednr(getElementById(\'id_field\').value);">';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="maps.php">'.__('Cancel').'</a></div>';
	echo '</div>';
}

// NEW~~~~~~~~~~~~~~~~~~~~~~~~~~`
// FIXED WINDOW WITH LIST TO CHOOSE PERSON TO MAP WITH ANCESTORS
if(isset($_POST['ancmap'])) {

	//adjust pulldown for mobiles/tablets
	$select_size = 'size="20"'; $select_height = '400px';
	if (isset($_SERVER["HTTP_USER_AGENT"]) OR ($_SERVER["HTTP_USER_AGENT"] != "")) { //adjust pulldown for mobiles/tablets
		$visitor_user_agent = $_SERVER["HTTP_USER_AGENT"];
		if(strstr($visitor_user_agent,"Android")!== false OR
			strstr($visitor_user_agent,"iOS")!== false OR
			strstr($visitor_user_agent,"iPad")!== false OR
			strstr($visitor_user_agent,"iPhone")!== false ) {
			$select_size=""; $select_height= '100px';
		}
	}

	echo '<div id="ancmapping" style="display:block; z-index:100; position:absolute; top:90px; margin-left:140px; height:'.$select_height.'; width:400px; border:1px solid #000; background:#d8d8d8; color:#000; margin-bottom:1.5em;z-index:20">';
	if($user['group_kindindex']=="j") { $orderlast = "CONCAT(pers_prefix,pers_lastname)"; }
	else { $orderlast = "pers_lastname"; }
	$anc_search = "SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_fams !='' ORDER BY ".$orderlast.", pers_firstname";
	$anc_search_result = $dbh->query($anc_search);
	echo '&nbsp;&nbsp;<strong>'.__('Filter by ancestors of a person').'</strong><br>';
	echo '&nbsp;&nbsp;'.__('Pick a name or enter ID:').'<br>';
	echo '<form method="POST" action="" style="display : inline;">';
	echo '<select style="max-width:396px;background:#eee" '.$select_size.' onChange="window.location=this.value;" id="anc_map" name="anc_map">';
	echo '<option value="toptext">'.__('Pick a name from the pulldown list').'</option>';
	//prepared statement out of loop
	$chld_prep = $dbh->prepare("SELECT fam_children FROM humo_families
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber =? AND fam_children != ''");
	$chld_prep->bindParam(1,$chld_var);
	while($anc_searchDb=$anc_search_result->fetch(PDO::FETCH_OBJ)) {
		$countmarr = 0;
		$man_cls = New person_cls;
		$fam_arr = explode(";", $anc_searchDb->pers_fams);
		foreach ($fam_arr as $value) {
			if($countmarr==1) { break; } //this person is already listed
			$chld_var = $value;
			$chld_prep->execute();
			while($chld_search_resultDb = $chld_prep->fetch(PDO::FETCH_OBJ)) {
				$countmarr = 1;
				$selected='';
				//if($anc_searchDb->pers_gedcomnumber == $chosenperson) { $selected = ' SELECTED '; }
				$man_cls->construct($anc_searchDb);
				$privacy_man=$man_cls->privacy;
				$date='';
				if(!$privacy_man) { // don't show dates if privacy is set for this person
					// if a person has privacy set (even if only for data, not for name,
					// we won't put them on the list. Most likely it concerns recent people.
					// Also, using the $man_cls->person_name functions takes too much time...
					$b_date=$anc_searchDb->pers_birth_date;
					$b_sign = __('born').' ';
					if (!$anc_searchDb->pers_birth_date AND $anc_searchDb->pers_bapt_date) {
						$b_date = $anc_searchDb->pers_bapt_date;
						$b_sign = __('baptised').' ';
					}
					$d_date=$anc_searchDb->pers_death_date;
					$d_sign = __('died').' ';
					if (!$anc_searchDb->pers_death_date AND $anc_searchDb->pers_buried_date) {
						$d_date = $anc_searchDb->pers_buried_date;
						$d_sign = __('buried').' ';
					}
					$date='';
					if($b_date AND !$d_date) {
						$date = ' ('.$b_sign.date_place($b_date,'').')';
					}
					if($b_date AND $d_date) {
						$date .= ' ('.$b_sign.date_place($b_date,'').' - '.$d_sign.date_place($d_date,'').')';
					}
					if(!$b_date AND $d_date) {
						$date = '('.$d_sign.date_place($d_date,'').')';
					}
				}
				if(!$privacy_man OR ($privacy_man AND $user['group_filter_name']=="j")) { 
					// don't show the person at all on the list if names are hidden when privacy is set for person
					$name = ''; $pref = ''; $last = '- , '; $first = '-';
					if($anc_searchDb->pers_lastname) { $last = $anc_searchDb->pers_lastname.', '; }
					if($anc_searchDb->pers_firstname) { $first = $anc_searchDb->pers_firstname; }
					if($anc_searchDb->pers_prefix) { $pref = strtolower(str_replace('_','',$anc_searchDb->pers_prefix)); }

					if($user['group_kindindex']=="j") {
						if($anc_searchDb->pers_prefix) { $pref = strtolower(str_replace('_','',$anc_searchDb->pers_prefix)).' '; }
						$name = $pref.$last.$first;
					}
					else {
						if($anc_searchDb->pers_prefix) { $pref = ' '.strtolower(str_replace('_','',$anc_searchDb->pers_prefix)); }
						$name = $last.$first.$pref;
					}
					echo '<option value="maps.php?anc_persged='.$anc_searchDb->pers_gedcomnumber.'&anc_persfams='.$anc_searchDb->pers_fams.'" '.$selected.'>'.$name.$date.' [#'.$anc_searchDb->pers_gedcomnumber.']</option>';
				}
			}
		}
	}
	echo '</select>';
	echo '</form>';

	?>
	<script>
	function findGednr (pers_id) {
		for(var i=1;i<anc_map.length-1;i++) {
			if(anc_map.options[i].text.indexOf("[#" + pers_id + "]") != -1 || anc_map.options[i].text.indexOf("[#I" + pers_id + "]") != -1 ) {
				window.location = anc_map.options[i].value;
			}
		}
	}
	</script>
	<?php
	echo '<br><div style="margin-top:5px;text-align:left">&nbsp;&nbsp;Find by ID (I324):<input id="id_field" type="text" style="font-size:120%;width:60px;" value=""><input type="button" value="'.__('Go!').'" onclick="findGednr(getElementById(\'id_field\').value);">';
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="maps.php">'.__('Cancel').'</a></div>';
	echo '</div>';
}
// END NEW~~~~~~~~~~~~~~~~~~~~~~



// *** OpenStreetMap ***
if(isset($humo_option["use_world_map"]) AND $humo_option["use_world_map"]=='OpenStreetMap') {
	$location_array[]=''; $lat_array[]=''; $lon_array[]=''; $text_array[]='';
	$text_count_array[]='';

	$location=$dbh->query("SELECT location_id, location_location, location_lat, location_lng FROM humo_location");
	while (@$locationDb=$location->fetch(PDO::FETCH_OBJ)){
		//$locarray[$locationDb->location_location][0] = $locationDb->location_location;
		$locarray[$locationDb->location_location][0] = htmlspecialchars($locationDb->location_location);
		$locarray[$locationDb->location_location][1] = $locationDb->location_lat;
		$locarray[$locationDb->location_location][2] = $locationDb->location_lng;
		//$locarray[$locationDb->location_location][3] = 0;    // till starting year  (depending on settings)
		//$locarray[$locationDb->location_location][4] = 0;    // + 1 interval
		//$locarray[$locationDb->location_location][5] = 0;    // + 2 intervals
		//$locarray[$locationDb->location_location][6] = 0;    // + 3 intervals
		//$locarray[$locationDb->location_location][7] = 0;    // + 4 intervals
		//$locarray[$locationDb->location_location][8] = 0;    // + 5 intervals
		//$locarray[$locationDb->location_location][9] = 0;    // + 6 intervals
		//$locarray[$locationDb->location_location][10] = 0;   // + 7 intervals
		//$locarray[$locationDb->location_location][11] = 0;   // + 8 intervals
		//$locarray[$locationDb->location_location][12] = 0;   // till today (=2010 and beyond)
		//$locarray[$locationDb->location_location][13] = 0;   // all

		//TEST add all location in maps...
		//$location_array[]=htmlspecialchars($locationDb->location_location);
		//$lat_array[]=$locationDb->location_lat;
		//$lon_array[]=$locationDb->location_lng;
		//$text_array[]='test';
	}
	$namesearch_string='';
	if($_SESSION['type_birth']==1) {
		//$persoon=$dbh->query("SELECT pers_tree_id, pers_birth_place, pers_birth_date, pers_bapt_place, pers_bapt_date
		//	FROM humo_persons WHERE pers_tree_id='".$tree_id."'
		//	AND (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !='')) ".$namesearch_string);
		$persoon=$dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."'
			AND (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !='')) ".$namesearch_string);
	}
	elseif($_SESSION['type_death']==1) {
		//$persoon=$dbh->query("SELECT pers_tree_id, pers_death_place, pers_death_date, pers_buried_place, pers_buried_date
		//	FROM humo_persons WHERE pers_tree_id='".$tree_id."'
		//	AND (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !='')) ".$namesearch_string);
		$persoon=$dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."'
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

				//if($year > 1 AND $year < $realmin) {  $locarray[$place][3]++; }
				//if($year > 1 AND $year < ($realmin+ $step)) {  $locarray[$place][4]++; }
				//if($year > 1 AND $year < ($realmin+ (2*$step))) {  $locarray[$place][5]++; }
				//if($year > 1 AND $year < ($realmin+ (3*$step))) {  $locarray[$place][6]++; }
				//if($year > 1 AND $year < ($realmin+ (4*$step))) {  $locarray[$place][7]++; }
				//if($year > 1 AND $year < ($realmin+ (5*$step))) {  $locarray[$place][8]++; }
				//if($year > 1 AND $year < ($realmin+ (6*$step))) {  $locarray[$place][9]++; }
				//if($year > 1 AND $year < ($realmin+ (7*$step))) {  $locarray[$place][10]++; }
				//if($year > 1 AND $year < ($realmin+ (8*$step))) {  $locarray[$place][11]++; }
				//if($year > 1 AND $year < 2050) {  $locarray[$place][12]++; }
				//$locarray[$place][13]++;  // array of all people incl without birth date

				// *** Use person class ***
				$person_cls = New person_cls;
				$person_cls->construct($personDb);
				$name=$person_cls->person_name($personDb);


				$key = array_search($locarray[$place][0], $location_array);
				if (isset($key) AND $key>0){
					// *** Check the number of lines of the text_array ***
					$text_count_array[$key]++;
// *** For now: limited results in text box of OpenStreetMap ***
					if ($text_count_array[$key]<26)
						$text_array[$key].='<br>'.addslashes($name["standard_name"].' '.$locarray[$place][0]);
					if ($text_count_array[$key]==26)
						$text_array[$key].='<br>'.__('Results are limited.');
				}
				else{
					$location_array[]=htmlspecialchars($locarray[$place][0]);
					$lat_array[]=$locarray[$place][1];
					$lon_array[]=$locarray[$place][2];

					$text_array[]=addslashes($name["standard_name"].' '.$locarray[$place][0]);
					$text_count_array[]=1; // *** Number of text lines ***
				}
			}
			else {
				//$locarray[$place][13]++ ; // array of all people incl without birth date
			}
	//echo $locarray[$place][1].'!'.$locarray[$place][2];
		}
	}

	//echo '<script>
	//	function hide() {
	//		document.getElementById(\'wait\').style.display = "none";
	//	}
	//</script>';

	echo '<link rel="stylesheet" href="include/leaflet/leaflet.css">';
	echo '<script src="include/leaflet/leaflet.js"></script>';

	// *** Show map ***
	echo '<div id="map" style="width:1000px; height:520px"></div>';

	// *** Map using fitbound (all markers visible) ***
	echo '<script>
		var map = L.map("map").setView([48.85, 2.35], 10);
		var markers = [';

//echo 'L.marker([51,5, -0.09]) .bindPopup(\'Test\')';


//include_once(CMS_ROOTPATH."googlemaps/google_initiate.php");

	// *** Add all markers from array ***
	for ($i=1; $i<count($location_array); $i++){
		if ($i>1) echo ',';
		echo 'L.marker(['.$lat_array[$i].', '.$lon_array[$i].']) .bindPopup(\''.$text_array[$i].'\')';
	}

	echo '];
		var group = L.featureGroup(markers).addTo(map);
		setTimeout(function () {
		  map.fitBounds(group.getBounds());
		}, 1000);
		L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
		  attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
		}).addTo(map);
	</script>';
}
else{

	// *** Google Maps ***
	echo '<div id="map_canvas" style="width:1000px; height:520px"></div>'; // placeholder div for map generated below

	// function to read multiple values from location search bar and zoom to map location:
	echo '
	<script>
	function findPlace () {
		infoWindow.close();
		var e = document.getElementById("loc_search");
		var locSearch = e.options[e.selectedIndex].value;
		if(locSearch != "toptext") {   // if not default text "find location on map"
			var opt_array = new Array();
			opt_array = locSearch.split(",",3);
			map.setZoom(11);
			var ltln = new google.maps.LatLng(opt_array[1],opt_array[2]);
			map.setCenter(ltln);
		}
	}
	</script>';


	$api_key = '';
	if(isset($humo_option['google_api_key']) AND $humo_option['google_api_key']!='') {
		$api_key = '?key='.$humo_option['google_api_key'].'&callback=Function.prototype'; //echo "http://maps.googleapis.com/maps/api/js".$api_key;
	}
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
		echo '<script src="https://maps.googleapis.com/maps/api/js'.$api_key.'"></script>';
	}
	else {
		echo '<script src="http://maps.googleapis.com/maps/api/js'.$api_key.'"></script>';
	}
	$maptype = "ROADMAP";
	if(isset($humo_option['google_map_type'])) {
		$maptype = $humo_option['google_map_type'];
	}

	echo '
	<script>
		var map;
		function initialize() {
			var latlng = new google.maps.LatLng(22, -350);
			var myOptions = {
				zoom: 2,
				center: latlng,
				mapTypeId: google.maps.MapTypeId.'.$maptype.'
			};
			map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		}
	</script>';

	echo '<script>
		initialize();
	</script>';

	echo '<script>
		function hide() {
			document.getElementById(\'wait\').style.display = "none";
		}
	</script>';

	include_once(CMS_ROOTPATH."googlemaps/google_initiate.php");

	echo '<script>
		window.onload = hide;
	</script>';
}

include_once(CMS_ROOTPATH."footer.php");
?>