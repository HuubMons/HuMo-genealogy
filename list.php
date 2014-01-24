<?php
include_once("header.php"); //returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
error_reporting(E_ALL);
@set_time_limit(300);

// *** show person ***
function show_person($personDb){
	global $index_list, $selected_place, $language, $user;
	global $bot_visit, $db, $dbh, $humo_option, $uri_path, $search_database, $list_expanded;
	global $selected_language, $privacy, $dirmark1, $dirmark2, $rtlmarker;
	global $select_birth, $select_bapt, $select_place, $select_death, $select_buried;
	global $selectsort;

	$pers_tree_prefix=$personDb->pers_tree_prefix;

	if (CMS_SPECIFIC=='Joomla'){
		$start_url='index.php?option=com_humo-gen&amp;task=family&amp;database='.$pers_tree_prefix.
			'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
	}
	elseif ($humo_option["url_rewrite"]=="j"){	// *** url_rewrite ***
		// *** $uri_path made in header.php ***
		$start_url= $uri_path.'family/'.$pers_tree_prefix.'/'.$personDb->pers_indexnr.'/'.$personDb->pers_gedcomnumber.'/';
	}
	else{
		$start_url=CMS_ROOTPATH.'family.php?database='.$pers_tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
	}

	// *** Person class used for name and person pop-up data ***
	$person_cls = New person_cls;
	$person_cls->construct($personDb);
	$name=$person_cls->person_name($personDb);

	// *** Show name ***
	$index_name='';
	if ($name["show_name"]==false){
		$index_name=__('Name filtered');
	}
	else{
		// *** If there is no lastname, show a - character. ***
		if ($personDb->pers_lastname==""){
			// Don't show a "-" by pers_patronymes
			if (!isset($_GET['pers_patronym'])){ $index_name="-&nbsp;&nbsp;"; }
		}
		$index_name.=$name["index_name_extended"].$name["colour_mark"];
	}

	// when sorting by date we add column to make clearer what the list is ordered by.
	if($index_list!='places' AND ($selectsort=="sort_deathdate" OR $selectsort=="sort_birthdate" OR $selectsort=="sort_baptdate" OR $selectsort=="sort_burieddate")) {

		echo '<tr><td style="width:125px;text-align:right;vertical-align:top;white-space:nowrap">';

		if($selectsort=="sort_deathdate") {
			if ($personDb->pers_death_date == '') {
				echo __('no date')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			elseif($privacy==1) {
				echo __('privacy').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			else {
				echo date_place($personDb->pers_death_date,'')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		}
		if($selectsort=="sort_birthdate") {
			if ($personDb->pers_birth_date == '') {
				echo __('no date')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			elseif($privacy==1) {
				echo __('privacy').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			else {
				echo date_place($personDb->pers_birth_date,'')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		}
		if($selectsort=="sort_baptdate") {
			if ($personDb->pers_bapt_date == '') {
				echo __('no date')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			elseif($privacy==1) {
				echo __('privacy').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			else {
				echo date_place($personDb->pers_bapt_date,'')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		}
		if($selectsort=="sort_burieddate") {
			if ($personDb->pers_buried_date == '') {
				echo __('no date')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			elseif($privacy==1) {
				echo __('privacy').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			else {
				echo date_place($personDb->pers_buried_date,'')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		}
		echo '</td>';
	}
	if($selectsort!='sort_birthdate' AND $selectsort !='sort_deathdate' AND $selectsort!='sort_baptdate' AND $selectsort !='sort_burieddate') {
		echo '<tr>';
	}
	//echo '<td>';

	// *** Show extra colums before a person in index places ***
	if ($index_list=='places'){

		if ($selected_place!=$personDb->place_order){ 
			//echo "<b>$personDb->place_order</b><br>"; 
			echo "<td colspan=2><b>".$dirmark2."$personDb->place_order</b></td></tr><tr>";			
		}
		$selected_place=$personDb->place_order;		

		echo '<td style="white-space:nowrap;width:90px">'; 		

		if ($select_birth=='1'){
			if ($selected_place==$personDb->pers_birth_place){
				echo '<span class="place_index place_index_selected">'.__('*').'</span>';
			}
			else{
				echo '<span class="place_index">&nbsp;</span>';
			}
		}

		if ($select_bapt=='1'){
			if ($selected_place==$personDb->pers_bapt_place){
				echo '<span class="place_index place_index_selected">'.__('~').'</span>';
			}
			else{
				echo '<span class="place_index">&nbsp;</span>';
			}
		}

		if ($select_place=='1'){
			if ($selected_place==$personDb->pers_place_index){
				echo '<span class="place_index place_index_selected">'.__('^').'</span>';
			}
			else{
				echo '<span class="place_index">&nbsp;</span>';
			}
		}

		if ($select_death=='1'){
			if ($selected_place==$personDb->pers_death_place){
				echo '<span class="place_index place_index_selected">'.__('&#134;').'</span>';
			}
			else{
				echo '<span class="place_index">&nbsp;</span>';
			}
		}

		if ($select_buried=='1'){
			if ($selected_place==$personDb->pers_buried_place){
				echo '<span class="place_index place_index_selected">'.__('[]').'</span>';
			}
			else{
				echo '<span class="place_index">&nbsp;</span>';
			}
		}

		echo '</td>';
		//echo '&nbsp;';
	}
	echo '<td>'; 
	// *** Show person popup menu ***
	echo $person_cls->person_popup_menu($personDb);

	// *** Show picture man or wife ***
	if ($personDb->pers_sexe=="M"){
		echo $dirmark1.' <img src="'.CMS_ROOTPATH.'images/man.gif" alt="man" style="vertical-align:middle">';
	}
	elseif ($personDb->pers_sexe=="F"){
		echo $dirmark1.' <img src="'.CMS_ROOTPATH.'images/woman.gif" alt="woman" style="vertical-align:middle">';
	}
	else {
		echo $dirmark1.' <img src="'.CMS_ROOTPATH.'images/unknown.gif" alt="unknown" style="vertical-align:middle">';
	}

	echo ' <a href="'.$start_url.'">'.$index_name.$dirmark2.'</a>';

	$info="";
	if ($personDb->pers_bapt_date OR $personDb->pers_bapt_place){
		$info=', '.__('BAPTISED_SHORT').' '.date_place($personDb->pers_bapt_date, $personDb->pers_bapt_place);
	}
	if ($personDb->pers_birth_date OR $personDb->pers_birth_place){
		$info=', '.__('BORN_SHORT').' '.date_place($personDb->pers_birth_date, $personDb->pers_birth_place);
	}

	if ($personDb->pers_death_date OR $personDb->pers_death_place){
		$info=$info.', '.__('DIED_SHORT').' '.date_place($personDb->pers_death_date, $personDb->pers_death_place);
	}
	else{
		if ($personDb->pers_buried_date OR $personDb->pers_buried_place){
			$info=$info.', '.__('BURIED_SHORT').' '.date_place($personDb->pers_buried_date, $personDb->pers_buried_place);
		}
	}
	if (substr($info,0,2)==', '){
		$sp=""; if ($language["dir"]=="rtl") { $sp="&nbsp;"; }
		$info=$sp." (".substr($info,2).") ";
		$info=str_replace(", ", " - ", $info);
	}

	// *** privacy filter
	if ($privacy==1){
		echo ' '.__('PRIVACY FILTER');
	}
	else{
		//echo "<span style='font-size:90%'>".$info."&nbsp;".$dirmark1."</span>";
		echo "<span style='font-size:90%'>".$info.$dirmark1."</span>";
	}

	//*** Show spouse/ partner ***
	if ($list_expanded==true AND $personDb->pers_fams){
		$marriage_array=explode(";",$personDb->pers_fams);
		// *** Code to show only last marriage ***
		//$last_relation=end($marriage_array);
		//$qry="SELECT * FROM ".$pers_tree_prefix."family WHERE fam_gedcomnumber='".$last_relation."'";
		$nr_marriages=count($marriage_array);
		//NEW
		$stmt = $dbh->prepare("SELECT * FROM ".safe_text($pers_tree_prefix)."family WHERE fam_gedcomnumber=?");
		$stmt->bindParam(1, $marr_arr);
		$stmt2 = $dbh->prepare("SELECT * FROM ".safe_text($pers_tree_prefix)."person WHERE pers_gedcomnumber=?");
		$stmt2->bindParam(1, $partnid);
		for ($x=0; $x<=$nr_marriages-1; $x++){
			//$qry="SELECT * FROM ".safe_text($pers_tree_prefix)."family WHERE fam_gedcomnumber='".safe_text($marriage_array[$x])."'";
			$marr_arr = $marriage_array[$x];
			$stmt->execute();
			$fam_partnerDb = $stmt->fetch();
			//$fam_partner=mysql_query($qry,$db);
			//$fam_partnerDb=mysql_fetch_object($fam_partner);

			// *** This check is better then a check like: $personDb->pers_sexe=='F', because of unknown sexe or homosexual relations. ***
			if ($personDb->pers_gedcomnumber==$fam_partnerDb['fam_man'])
				$partner_id=$fam_partnerDb['fam_woman'];
			else
				$partner_id=$fam_partnerDb['fam_man'];

			$relation_short=__('&');
			if ($fam_partnerDb['fam_marr_date'] OR $fam_partnerDb['fam_marr_place'] OR $fam_partnerDb['fam_marr_church_date'] OR $fam_partnerDb['fam_marr_church_place'])
				$relation_short=__('X');
			if($fam_partnerDb['fam_div_date'] OR $fam_partnerDb['fam_div_place'])
				$relation_short=__(') (');

			if ($partner_id!='0' AND $partner_id!=''){
				//$qry="SELECT * FROM ".safe_text($pers_tree_prefix)."person WHERE pers_gedcomnumber='".safe_text($partner_id)."'";
				$partnid = $partner_id;
				$stmt2->execute();	
				$partnerDb = $stmt2->fetch(PDO::FETCH_OBJ);				
				//$partner=mysql_query($qry,$db);
				//$partnerDb=mysql_fetch_object($partner);
				$partner_cls = New person_cls;
				$name=$partner_cls->person_name($partnerDb);
			}
			else{
				$name["standard_name"]=__('N.N.');
			}
			
			if ($nr_marriages>1){
				if ($x==0) echo __('1st');
				elseif ($x==1) echo __('2nd');
				elseif ($x==2) echo __('3rd');
				elseif ($x>2) echo ($x+1).__('th');
			}
			echo ' <span class="index_partner">'.$relation_short.' '.$dirmark1.$name["standard_name"].$dirmark1.' </span>';
		}
	}
	// *** End spouse/ partner ***

	// *** Show name of family tree, if search in multiple family trees is used ***
	if ($search_database=='all_trees' OR $search_database=='all_but_this'){
		$treetext=show_tree_text($pers_tree_prefix, $selected_language);
		echo ' <i><font size="-1">['.__('Family tree').': '.$treetext['name'].']</font></i>';
	}
 
	echo '</td></tr>';

} // *** end function show person ***

// *** index ***
//$index_list='standard';
$index_list='quicksearch';

// *** Reset search fields if necessary ***
//if (isset($_POST['pers_firstname']) OR isset($_GET['pers_lastname']) OR isset($_GET['reset']) ){
if (isset($_POST['pers_firstname']) OR isset($_GET['pers_lastname']) OR isset($_GET['reset']) OR isset($_POST['quicksearch'])){
	unset ($_SESSION["save_search_tree_prefix"]);
	unset ($_SESSION["save_search_database"]);
	unset ($_SESSION["save_firstname"]);
	unset ($_SESSION["save_part_firstname"]);
	unset ($_SESSION["save_place_name"]);
	unset ($_SESSION["save_part_place_name"]);
	unset ($_SESSION["save_prefix"]);
	unset ($_SESSION["save_lastname"]);
	unset ($_SESSION["save_part_lastname"]);
	unset ($_SESSION["save_birth_place"]);
	unset ($_SESSION["save_part_birth_place"]);
	unset ($_SESSION["save_death_place"]);
	unset ($_SESSION["save_part_death_place"]);
	unset ($_SESSION["save_birth_year"]);
	unset ($_SESSION["save_birth_year_end"]);
	unset ($_SESSION["save_death_year"]);
	unset ($_SESSION["save_death_year_end"]);
	unset ($_SESSION["save_spouse_firstname"]);
	unset ($_SESSION["save_part_spouse_firstname"]);
	unset ($_SESSION["save_spouse_lastname"]);
	unset ($_SESSION["save_part_spouse_lastname"]);
	unset ($_SESSION["save_sexe"]);
	unset ($_SESSION["save_own_code"]);
	unset ($_SESSION["save_part_own_code"]);
	//unset ($_SESSION["save_quicksearch"]);
	unset ($_SESSION["save_adv_search"]);

	$index_list='search';
}

if (isset($_POST["index_list"])) $index_list=$_POST['index_list'];
if (isset($_GET["index_list"])) $index_list=$_GET['index_list'];

// *** Extra reset needed for "search in all family trees" ***
//if ($index_list!='search'){
if ($index_list!='search' AND $index_list!='quicksearch') unset ($_SESSION["save_search_database"]);

// *** Save selected "search" family tree (can be used to erase search values if tree is changed) ***
$_SESSION["save_search_tree_prefix"]=safe_text($_SESSION['tree_prefix']);

//************* SORT CHOICES *********************

$make_date=''; // we only need this when sorting by date

$desc_asc=" ASC "; $sort_desc=0;

if(isset($_SESSION['sort_desc'])) {
	if($_SESSION['sort_desc'] == 1){
		$desc_asc=" DESC ";  $sort_desc=1;
	}
	else{
		$desc_asc=" ASC ";  $sort_desc=0;
	}
}

if(isset($_POST['sort_desc'])) {
	if($_POST['sort_desc'] == 1) {
		$desc_asc=" DESC ";  $sort_desc=1;
		$_SESSION['sort_desc']=1;
	}
	else {
		$desc_asc=" ASC ";  $sort_desc=0;
		$_SESSION['sort_desc']=0;
	}
}

// SOME DEFAULTS
$last_or_patronym=" pers_lastname ";
if ($index_list=='patronym'){
	$last_or_patronym = " pers_patronym ";
}
$orderby = $last_or_patronym.$desc_asc.", pers_firstname ".$desc_asc;
if ($user['group_kindindex']=="j" AND $index_list!='patronym'){ $orderby = " concat_name ".$desc_asc; }
$selectsort = '';

if(isset($_SESSION['sort']) AND !isset($_GET['sort'])) {
	$selectsort = $_SESSION['sort'];
	if($_SESSION['sort']=="sort_lastname")  {
		$orderby = $last_or_patronym.$desc_asc.", pers_firstname ".$desc_asc;
		if ($user['group_kindindex']=="j" AND $index_list!='patronym'){ $orderby = " concat_name ".$desc_asc; }
	}
	if($_SESSION['sort']=="sort_firstname") {
		$orderby = " pers_firstname ".$desc_asc.",".$last_or_patronym.$desc_asc;
	}
	if($_SESSION['sort']=="sort_birthdate") {
		$make_date = ", right(pers_birth_date,4) as year,
		date_format( str_to_date( substring(pers_birth_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_birth_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
	}
	if($_SESSION['sort']=="sort_deathdate") {
		$make_date = ", right(pers_death_date,4) as year,
		date_format( str_to_date( substring(pers_death_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_death_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
	}
	if($_SESSION['sort']=="sort_baptdate") {
		$make_date = ", right(pers_bapt_date,4) as year,
		date_format( str_to_date( substring(pers_bapt_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_bapt_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
	}
	if($_SESSION['sort']=="sort_burieddate") {
		$make_date = ", right(pers_buried_date,4) as year,
		date_format( str_to_date( substring(pers_buried_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_buried_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
	}
}
if(isset($_GET['sort']))  {
	if($_GET['sort']=="sort_lastname") {
		$orderby = $last_or_patronym.$desc_asc.", pers_firstname ".$desc_asc;
		if ($user['group_kindindex']=="j" AND $index_list!='patronym'){ $orderby = " concat_name ".$desc_asc; }
		$selectsort="sort_lastname";
		$_SESSION['sort']=$selectsort;
	}

	if($_GET['sort']=="sort_firstname") {
		$orderby = " pers_firstname ".$desc_asc.", ".$last_or_patronym.$desc_asc;
		$selectsort="sort_firstname";
		$_SESSION['sort']=$selectsort;
	}

	if($_GET['sort']=="sort_birthdate") {
		$make_date = ", right(pers_birth_date,4) as year,
		date_format( str_to_date( substring(pers_birth_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_birth_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
		$selectsort="sort_birthdate";
		$_SESSION['sort']=$selectsort;
	}

	if($_GET['sort']=="sort_deathdate") {
		$make_date = ", right(pers_death_date,4) as year,
		date_format( str_to_date( substring(pers_death_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_death_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
		$selectsort="sort_deathdate";
		$_SESSION['sort']=$selectsort;
	}
	if($_GET['sort']=="sort_baptdate") {
		$make_date = ", right(pers_bapt_date,4) as year,
		date_format( str_to_date( substring(pers_bapt_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_bapt_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
		$selectsort="sort_baptdate";
		$_SESSION['sort']=$selectsort;
	}
	if($_GET['sort']=="sort_burieddate") {
		$make_date = ", right(pers_buried_date,4) as year,
		date_format( str_to_date( substring(pers_buried_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_buried_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
		$selectsort="sort_burieddate";
		$_SESSION['sort']=$selectsort;
	}
}
//************* END SORT CHOICES *********************


// *** Search in 1 or more family trees ***
//$search_database='';
$search_database='tree_selected';
if (isset($_POST['search_database'])){
	$search_database=$_POST['search_database'];
	$_SESSION["save_search_database"]=$search_database;
}
if (isset($_GET["search_database"])){
	$search_database=$_GET['search_database'];
	$_SESSION["save_search_database"]=$search_database;
}

$pers_firstname='';
if (isset($_POST['pers_firstname'])){
	$pers_firstname=$_POST['pers_firstname'];
	//$pers_firstname=htmlentities($_POST['pers_firstname'],ENT_QUOTES,'UTF-8');
	$_SESSION["save_firstname"]=$pers_firstname;
}
$part_firstname='';
if (isset($_POST['part_firstname'])){
	$part_firstname=$_POST['part_firstname'];
	$_SESSION["save_part_firstname"]=$part_firstname;
}

// *** Pre-fix (names list and most namenlijst en most frequent names in main menu.) ***
$pers_prefix='';
if (isset($_GET['pers_prefix'])){
	$pers_prefix=$_GET['pers_prefix'];
	//$pers_prefix=htmlentities($_GET['pers_prefix'],ENT_QUOTES,'UTF-8');
	$_SESSION["save_prefix"]=$pers_prefix;
}

// *** Lastname ***
$pers_lastname='';
if (isset($_POST['pers_lastname'])){
	$pers_lastname=$_POST['pers_lastname'];
	//$pers_lastname=htmlentities($_POST['pers_lastname'],ENT_QUOTES,'UTF-8');
	$_SESSION["save_lastname"]=$pers_lastname;
}
if (isset($_GET["pers_lastname"])){
	$pers_lastname=$_GET['pers_lastname'];
	//$pers_lastname=htmlentities($_GET['pers_lastname'],ENT_QUOTES,'UTF-8');
	$pers_lastname=str_replace("|", "&", $pers_lastname);  // Don't use a & character in a GET link
	$_SESSION["save_lastname"]=$pers_lastname;
}

$part_lastname='';
if (isset($_POST['part_lastname'])){
	$part_lastname=$_POST['part_lastname'];
	$_SESSION["save_part_lastname"]=$part_lastname;
}
// *** Used for clicking in the names list ***
if (isset($_GET['part_lastname'])){
	$part_lastname=$_GET['part_lastname'];
	$_SESSION["save_part_lastname"]=$part_lastname;
}

// ***  ADVANCED SEARCH added by Yossi Beck, translated and integrated in person search screen by Huub. *** //
$birth_place='';
if (isset($_POST['birth_place'])){
	$birth_place=$_POST['birth_place'];
	$_SESSION["save_birth_place"]=$birth_place;
}
$part_birth_place='';
if (isset($_POST['part_birth_place'])){
	$part_birth_place=$_POST['part_birth_place'];
	$_SESSION["save_part_birth_place"]=$part_birth_place;
}

$death_place='';
if (isset($_POST['death_place'])){
	$death_place=$_POST['death_place'];
	$_SESSION["save_death_place"]=$death_place;
}
$part_death_place='';
if (isset($_POST['part_death_place'])){
	$part_death_place=$_POST['part_death_place'];
	$_SESSION["save_part_death_place"]=$part_death_place;
}

$birth_year='';
if (isset($_POST['birth_year'])){
	$birth_year=$_POST['birth_year'];
	$_SESSION["save_birth_year"]=$birth_year;
}
$birth_year_end='';
if (isset($_POST['birth_year_end'])){
	$birth_year_end=$_POST['birth_year_end'];
	$_SESSION["save_birth_year_end"]=$birth_year_end;
}

$death_year='';
if (isset($_POST['death_year'])){
	$death_year=$_POST['death_year'];
	$_SESSION["save_death_year"]=$death_year;
}
$death_year_end='';
if (isset($_POST['death_year_end'])){
	$death_year_end=$_POST['death_year_end'];
	$_SESSION["save_death_year_end"]=$death_year_end;
}

$spouse_firstname='';
if (isset($_POST['spouse_firstname'])){
	$spouse_firstname=$_POST['spouse_firstname'];
	//$spouse_firstname=htmlentities($_POST['spouse_firstname'],ENT_QUOTES,'UTF-8');
	$_SESSION["save_spouse_firstname"]=$spouse_firstname;
}

$part_spouse_firstname='';
if (isset($_POST['part_spouse_firstname'])){
	$part_spouse_firstname=$_POST['part_spouse_firstname'];
	$_SESSION["save_part_spouse_firstname"]=$part_spouse_firstname;
}

$spouse_lastname='';
if (isset($_POST['spouse_lastname'])){
	$spouse_lastname=$_POST['spouse_lastname'];
	//$spouse_lastname=htmlentities($_POST['spouse_lastname'],ENT_QUOTES,'UTF-8');
	$_SESSION["save_spouse_lastname"]=$spouse_lastname;
}
$part_spouse_lastname='';
if (isset($_POST['part_spouse_lastname'])){
	$part_spouse_lastname=$_POST['part_spouse_lastname'];
	$_SESSION["save_part_spouse_lastname"]=$part_spouse_lastname;
}

$sexe='';
if (isset($_POST['sexe'])){
	$sexe=$_POST['sexe'];
	$_SESSION["save_sexe"]=$sexe;
}

$own_code='';
if (isset($_POST['own_code'])){
	$own_code=$_POST['own_code'];
	$_SESSION["save_own_code"]=$own_code;
}
$part_own_code='';
if (isset($_POST['part_own_code'])){
	$part_own_code=$_POST['part_own_code'];
	$_SESSION["save_part_own_code"]=$part_own_code;
}

$quicksearch='';
if (isset($_POST['quicksearch'])){
	//$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
	$quicksearch=$_POST['quicksearch'];
	$_SESSION["save_quicksearch"]=$quicksearch;
}

$adv_search=false;
// *** Link from "names" list, automatically use advanced search ***
if (isset($_GET['part_lastname'])){
	$_GET['adv_search']='1';
}
if (isset($_GET['adv_search'])){
	if ($_GET['adv_search']=='1'){
		$adv_search=true;

		//$quicksearch='';
		//$_SESSION["save_quicksearch"]=$quicksearch;
	}
	$_SESSION["save_adv_search"]=$adv_search;

	// *** Switch from advanced search to standard search (now quick search) ***
	if (isset($_SESSION["save_quicksearch"]) AND $_GET['adv_search']=='0'){
		$quicksearch=$_SESSION["save_quicksearch"];
	}
}
if (isset($_POST['adv_search'])){
	if ($_POST['adv_search']=='1'){ $adv_search=true; }
	$_SESSION["save_adv_search"]=$adv_search;
}

// *** For index places ***
$place_name='';
$select_birth='0'; $select_bapt='0'; $select_place='0'; $select_death='0'; $select_buried='0';
if (isset($_POST['place_name'])){
	$place_name=$_POST['place_name'];
	//$place_name=htmlentities($_POST['place_name'],ENT_QUOTES,'UTF-8');
	$_SESSION["save_place_name"]=$place_name;

	if (isset($_POST['select_birth'])){	$select_birth='1'; $_SESSION["save_select_birth"]='1'; }
		else{ $_SESSION["save_select_birth"]='0'; }
	if (isset($_POST['select_bapt'])){ $select_bapt='1'; $_SESSION["save_select_bapt"]='1'; }
		else{ $_SESSION["save_select_bapt"]='0'; }
	if (isset($_POST['select_place'])){ $select_place='1'; $_SESSION["save_select_place"]='1'; }
		else{ $_SESSION["save_select_place"]='0'; }
	if (isset($_POST['select_death'])){ $select_death='1'; $_SESSION["save_select_death"]='1'; }
		else{ $_SESSION["save_select_death"]='0'; }
	if (isset($_POST['select_buried'])){ $select_buried='1'; $_SESSION["save_select_buried"]='1'; }
		else{ $_SESSION["save_select_buried"]='0'; }
}
$part_place_name='';
if (isset($_POST['part_place_name'])){
	$part_place_name=$_POST['part_place_name'];
	$_SESSION["save_part_place_name"]=$part_place_name;
}

// *** Read session for multiple pages ***
if (isset($_GET['item'])){
	if (isset($_SESSION["save_search_database"])){ $search_database=$_SESSION["save_search_database"]; }
	if (isset($_SESSION["save_firstname"])){ $pers_firstname=$_SESSION["save_firstname"]; }
	if (isset($_SESSION["save_part_firstname"])){
		$part_firstname=$_SESSION["save_part_firstname"]; }
	if (isset($_SESSION["save_prefix"])){ $pers_prefix=$_SESSION["save_prefix"]; }
	if (isset($_SESSION["save_lastname"])){ $pers_lastname=$_SESSION["save_lastname"]; }
	if (isset($_SESSION["save_part_lastname"])){
		$part_lastname=$_SESSION["save_part_lastname"]; }
	if (isset($_SESSION["save_birth_place"])){ $birth_place=$_SESSION["save_birth_place"]; }
	if (isset($_SESSION["save_part_birth_place"])){
		$part_birth_place=$_SESSION["save_part_birth_place"]; }
	if (isset($_SESSION["save_death_place"])){ $death_place=$_SESSION["save_death_place"]; }
	if (isset($_SESSION["save_part_death_place"])){
		$part_death_place=$_SESSION["save_part_death_place"]; }
	if (isset($_SESSION["save_birth_year"])){ $birth_year=$_SESSION["save_birth_year"]; }
	if (isset($_SESSION["save_birth_year_end"])){ $birth_year_end=$_SESSION["save_birth_year_end"]; }
	if (isset($_SESSION["save_death_year"])){ $death_year=$_SESSION["save_death_year"]; }
	if (isset($_SESSION["save_death_year_end"])){ $death_year_end=$_SESSION["save_death_year_end"]; }
	if (isset($_SESSION["save_spouse_firstname"])){ $spouse_firstname=$_SESSION["save_spouse_firstname"]; }
	if (isset($_SESSION["save_part_spouse_firstname"])){
		$part_spouse_firstname=$_SESSION["save_part_spouse_firstname"]; }
	if (isset($_SESSION["save_spouse_lastname"])){ $spouse_lastname=$_SESSION["save_spouse_lastname"]; }
	if (isset($_SESSION["save_part_spouse_lastname"])){
		$part_spouse_lastname=$_SESSION["save_part_spouse_lastname"]; }
	if (isset($_SESSION["save_sexe"])){ $sexe=$_SESSION["save_sexe"]; }
	if (isset($_SESSION["save_own_code"])){ $own_code=$_SESSION["save_own_code"]; }
	if (isset($_SESSION["save_part_own_code"])){ $part_own_code=$_SESSION["save_part_own_code"]; }
	if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
	if (isset($_SESSION["save_adv_search"])){ $adv_search=$_SESSION["save_adv_search"]; }
}

// *** Search for places in birth-baptise-died places etc. ***
if ($index_list=='places'){
	if (isset($_SESSION["save_place_name"])){ $place_name=$_SESSION["save_place_name"]; }
	if (isset($_SESSION["save_part_place_name"])){ $part_place_name=$_SESSION["save_part_place_name"]; }

	// *** Enable select boxes ***
	if (isset($_GET['reset'])){
		$select_birth='1'; $_SESSION["save_select_birth"]='1';
		$select_bapt='1'; $_SESSION["save_select_bapt"]='1';
		$select_place='1'; $_SESSION["save_select_place"]='1';
		$select_death='1'; $_SESSION["save_select_death"]='1';
		$select_buried='1'; $_SESSION["save_select_buried"]='1';
	}
	else{
		// *** Read and set select boxes for multiple pages ***
		if (isset($_SESSION["save_select_birth"])){ $select_birth=$_SESSION["save_select_birth"]; }
		if (isset($_SESSION["save_select_bapt"])){ $select_bapt=$_SESSION["save_select_bapt"]; }
		if (isset($_SESSION["save_select_place"])){ $select_place=$_SESSION["save_select_place"]; }
		if (isset($_SESSION["save_select_death"])){ $select_death=$_SESSION["save_select_death"]; }
		if (isset($_SESSION["save_select_buried"])){ $select_buried=$_SESSION["save_select_buried"]; }
	}
}

// *** Search for (part of) first or lastname ***
function name_qry($search_name, $search_part){
	$text="LIKE '%".safe_text($search_name)."%'"; // *** Default value: "contains" ***
	if ($search_part=='equals'){ $text="='".safe_text($search_name)."'"; }
	if ($search_part=='starts_with'){ $text="LIKE '".safe_text($search_name)."%'"; }
	return $text;
}

// *******************
// *** BUILD QUERY ***
// *******************

$query='';
$count_qry='';

//*** Results of searchform in mainmenu ***
//*** Or: search in lastnames ***
if ($pers_firstname OR $pers_lastname OR $birth_place OR $death_place OR $birth_year OR $death_year OR ($sexe AND $sexe!='both') OR $own_code){

	// *** Build query ***
	$and=" ";

	if ($pers_lastname) {
		if ($pers_lastname==__('...')){
			$query.=" pers_lastname=''"; $and=" AND ";
		}
		elseif ($user['group_kindindex']=="j"){
			$query.=" CONCAT( REPLACE(pers_prefix,'_',' ') ,pers_lastname) ".
			name_qry($pers_lastname, $part_lastname); $and=" AND ";
		}
		else {
			$query.=" pers_lastname ".name_qry($pers_lastname, $part_lastname); $and=" AND ";
		}
	}
	// *** Namelist: search persons without pers_prefix ***
	if ($pers_prefix=='EMPTY'){
		$query.=$and."pers_prefix=''"; $and=" AND ";
	}
	elseif ($pers_prefix){
		$query.=$and."pers_prefix='".$pers_prefix."'"; $and=" AND ";
	}

	if ($pers_firstname){
		//$query.=$and."pers_firstname ".name_qry($pers_firstname, $part_firstname); $and=" AND ";

		$query.=$and."(pers_firstname ".name_qry($pers_firstname, $part_firstname);
		$query.=" OR event_event ".name_qry($pers_firstname, $part_firstname).')';
		$and=" AND ";
	}

	if ($birth_place){
		$query.=$and."pers_birth_place ".name_qry($birth_place, $part_birth_place); $and=" AND ";
	}

	if ($death_place){
		$query.=$and."pers_death_place ".name_qry($death_place, $part_death_place); $and=" AND ";
	}

	if ($birth_year AND !$birth_year_end){   // filled in one year: exact date
		$query.=$and."pers_birth_date LIKE '%".safe_text($birth_year)."%'"; $and=" AND ";
	}

	if ($birth_year AND $birth_year_end){     //filled in two years: check period
		$query.=$and."RIGHT(pers_birth_date, 4)>='".safe_text($birth_year)."' AND RIGHT(pers_birth_date, 4)<='".safe_text($birth_year_end)."'"; $and=" AND ";
	}

	if ($death_year AND !$death_year_end){      // filled in one year: exact date
		$query.=$and."pers_death_date LIKE '%".safe_text($death_year)."%'"; $and=" AND ";
	}

	if ($death_year AND $death_year_end){     // filled in two years: check period
		$query.=$and."RIGHT(pers_death_date, 4)>='".safe_text($death_year)."' AND RIGHT(pers_death_date, 4)<='".safe_text($death_year_end)."'"; $and=" AND ";
	}

	if ($sexe=="M" OR $sexe=="F"){
		$query.=$and."pers_sexe='".$sexe."'"; $and=" AND ";
	}
	if ($sexe=="Unknown"){
		//$query.=$and."sexe=''"; $and=" AND ";
		$query.=$and."(pers_sexe!='M' AND pers_sexe!='F')"; $and=" AND ";
	}

	if ($own_code){
		$query.=$and."pers_own_code ".name_qry($own_code, $part_own_code); $and=" AND ";
	}

	// *** Change querie if searched for spouse ***
	if($spouse_firstname OR $spouse_lastname) {
		$query.=$and."pers_fams!=''"; $and=" AND ";
	}

	// *** Build SELECT part of query. Search with option "ALL family trees" or "All but selected" ***
	if ($search_database=='all_trees' OR $search_database=='all_but_this') {
		$query_part=$query;
		$query='';
		$counter=0;
		//$datasql = mysql_query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order",$db);
		foreach($dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order") AS $datapdo) {
		//while (@$dataDb=mysql_fetch_object($datasql)){
			if($search_database=="all_but_this" AND $datapdo['tree_prefix']==safe_text($_SESSION['tree_prefix'])) {
				continue;
			}
			// *** Check is family tree is shown or hidden for user group ***
			$hide_tree_array=explode(";",$user['group_hide_trees']);
			$hide_tree=false;
			for ($x=0; $x<=count($hide_tree_array)-1; $x++){
				if ($hide_tree_array[$x]==$datapdo['tree_id']){ $hide_tree=true; }
			}
			if ($hide_tree==false){

				$counter++;
				//$tree_prefix=$dataDb->tree_prefix;
				$tree_prefix=$datapdo['tree_prefix'];

				// *** EXAMPLE ***
				//$qry = "(SELECT * FROM humo1_persoon ".$query.') ';
				//$qry.= " UNION (SELECT * FROM humo2_persoon ".$query.')';
				//$qry.= " UNION (SELECT * FROM humo3_persoon ".$query.')';
				//$qry.= " ORDER BY pers_lastname, pers_firstname";
				$union=''; if ($counter>1){ $union=' UNION '; }

				if ($user['group_kindindex']=="j"){
					if ($counter>1){
						$query.=$union.'(SELECT';
					}
					else{
						$query.=$union.'(SELECT SQL_CALC_FOUND_ROWS';
					}
					$query.=' *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name '.$make_date.'
					FROM '.safe_text($tree_prefix).'person
					LEFT JOIN '.safe_text($tree_prefix).'events
					ON pers_gedcomnumber=event_person_id AND event_kind="name"
					WHERE'.$query_part.')';
				}
				else{
					if ($counter>1){
						$query.=$union.'(SELECT';
					}
					else{
						$query.=$union.'(SELECT SQL_CALC_FOUND_ROWS';
					}
					$query.=' * '.$make_date.'
					FROM '.$tree_prefix.'person
					LEFT JOIN '.$tree_prefix.'events
					ON pers_gedcomnumber=event_person_id AND event_kind="name"
					WHERE'.$query_part.')';
				}

			}

		}
	}
	else{
		// *** Start building query, search in 1 database ***
		$query_select= "SELECT SQL_CALC_FOUND_ROWS *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ".$make_date."
		FROM ".safe_text($_SESSION['tree_prefix'])."person
		LEFT JOIN ".safe_text($_SESSION['tree_prefix'])."events
		ON pers_gedcomnumber=event_person_id AND event_kind='name'
		WHERE";
		$query=$query_select.' ('.$query.')';
		//if ($pers_firstname){
		//	$query.=" OR event_event ".name_qry($pers_firstname, $part_firstname);
		//}
//$query.=" GROUP BY pers_gedcomnumber";
	}

	$query.=" ORDER BY ".$orderby;
}

// *** Menu quicksearch ***

if ($index_list=='quicksearch'){
	// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
	$quicksearch=str_replace(' ', '%', $quicksearch);

	// *** In case someone entered "Mons, Huub" using a comma ***
	$quicksearch = str_replace(',','',$quicksearch);

	// One can enter "Huub Mons", "Mons Huub", "Huub van Mons", "van Mons, Huub", "Mons, Huub van" and even "Mons van, Huub"

	// *** Build SELECT part of query. Search in ALL family trees ***
	if ($search_database=='all_trees' OR $search_database=='all_but_this') {
		//$query_part=$query;
		$query='';
		$counter=0;
		//$datasql = mysql_query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order",$db);
		foreach($dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order") as $pdoresult) {
		//while (@$dataDb=mysql_fetch_object($datasql)){
			if($search_database=="all_but_this" AND $pdoresult['tree_prefix']==safe_text($_SESSION['tree_prefix'])) {
				continue;
			}
			// *** Check if family tree is shown or hidden for user group ***
			$hide_tree_array=explode(";",$user['group_hide_trees']);
			$hide_tree=false;
			for ($x=0; $x<=count($hide_tree_array)-1; $x++){
				if ($hide_tree_array[$x]==$pdoresult['tree_id']){ $hide_tree=true; }
			}
			if ($hide_tree==false){

				$counter++;
				$tree_prefix=$pdoresult['tree_prefix'];

				// *** EXAMPLE ***
				//$qry = "(SELECT * FROM humo1_person ".$query.') ';
				//$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
				//$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
				//$qry.= " ORDER BY pers_lastname, pers_firstname";
				$union=''; if ($counter>1){ $union=' UNION '; }

				if ($counter>1){
					$query.=$union.'(SELECT';
				}
				else{
					$query.=$union.'(SELECT SQL_CALC_FOUND_ROWS';
				}
				$query.=" *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ".$make_date."
				FROM ".safe_text($tree_prefix)."person
				LEFT JOIN ".safe_text($tree_prefix)."events
				ON pers_gedcomnumber=event_person_id AND event_kind='name'
				WHERE CONCAT(pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".safe_text($quicksearch)."%'
					OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname,pers_callname) LIKE '%".safe_text($quicksearch)."%' 
					OR CONCAT(pers_lastname,pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text($quicksearch)."%' 
					OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname,pers_callname) LIKE '%".safe_text($quicksearch)."%'
					OR CONCAT(event_event,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".safe_text($quicksearch)."%'
					OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%".safe_text($quicksearch)."%' 
					OR CONCAT(pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text($quicksearch)."%' 
					OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%".safe_text($quicksearch)."%'
				GROUP BY pers_gedcomnumber)";

			}
		}
		$query.=" ORDER BY ".$orderby;
	}
	else{
		// *** Start building query, search in 1 database ***
		$query= "SELECT SQL_CALC_FOUND_ROWS *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ".$make_date."
		FROM ".safe_text($_SESSION['tree_prefix'])."person
		LEFT JOIN ".safe_text($_SESSION['tree_prefix'])."events
		ON pers_gedcomnumber=event_person_id AND event_kind='name'
		WHERE CONCAT(pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".safe_text($quicksearch)."%'
			OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname,pers_callname) LIKE '%".safe_text($quicksearch)."%' 
			OR CONCAT(pers_lastname,pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text($quicksearch)."%' 
			OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname,pers_callname) LIKE '%".safe_text($quicksearch)."%'
			OR CONCAT(event_event,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".safe_text($quicksearch)."%'
			OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%".safe_text($quicksearch)."%' 
			OR CONCAT(pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text($quicksearch)."%' 
			OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%".safe_text($quicksearch)."%'
		GROUP BY pers_gedcomnumber";

		$query.=" ORDER BY ".$orderby;
	}
}


//*** Places index ***
if ($index_list=='places'){
	// *** EXAMPLE of a UNION querie ***
	//$qry = "(SELECT * FROM humo1_person ".$query.') ';
	//$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
	//$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
	//$qry.= " ORDER BY pers_lastname, pers_firstname";

	$query='';
	$start=false;

	// *** Search birth place ***
	if ($select_birth=='1'){
		if ($user['group_kindindex']=="j"){
			$query = "(SELECT SQL_CALC_FOUND_ROWS *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_birth_place as place_order
				FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		else{
			$query = "(SELECT SQL_CALC_FOUND_ROWS *, pers_birth_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		if($place_name) {
			$query.= " WHERE pers_birth_place ".name_qry($place_name,$part_place_name);
		}
		else {
			$query.= " WHERE pers_birth_place LIKE '_%'";
		}
		$query.=')';
		$start=true;
	}

	// *** Search baptise place ***
	if ($select_bapt=='1'){
		if ($start==true){
			$query.=' UNION '; $calc='';
		}
		else{
			$calc='SQL_CALC_FOUND_ROWS ';	
		}
		if ($user['group_kindindex']=="j"){
			$query.= "(SELECT ".$calc."*,CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_bapt_place as place_order
			FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		else{
			$query.= "(SELECT ".$calc."*, pers_bapt_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		if ($place_name) {
			$query.= " WHERE pers_bapt_place ".name_qry($place_name,$part_place_name);
		}
		else {
			$query.= " WHERE pers_bapt_place LIKE '_%'";
		}
		$query.=')';
		$start=true;
	}

	// *** Search residence ***
	if ($select_place=='1'){
		if ($start==true){
			$query.=' UNION '; $calc='';
		}
		else{
			$calc='SQL_CALC_FOUND_ROWS ';	
		}
		if ($user['group_kindindex']=="j"){
			$query.= "(SELECT ".$calc."*, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_place_index as place_order
			FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		else{
			$query.= "(SELECT ".$calc."*, pers_place_index as place_order FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		if($place_name) {
			$query.= " WHERE pers_place_index ".name_qry($place_name,$part_place_name);
		}
		else {
			$query .= " WHERE pers_place_index LIKE '_%'";
		}
		$query.=')';
		$start=true;
	}

	// *** Search death place ***
	if ($select_death=='1'){
		if ($start==true){
			$query.=' UNION '; $calc='';
		}
		else{
			$calc='SQL_CALC_FOUND_ROWS ';	
		}
		if ($user['group_kindindex']=="j"){
			$query.= "(SELECT ".$calc."*, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_death_place as place_order
			FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		else{
			$query.= "(SELECT ".$calc."*, pers_death_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		if($place_name) {
			$query.= " WHERE pers_death_place ".name_qry($place_name,$part_place_name);
		}
		else {
			$query.= " WHERE pers_death_place LIKE '_%'";
		}
		$query.=')';
		$start=true;
	}

	// *** Search buried place ***
	if ($select_buried=='1'){
		if ($start==true){
			$query.=' UNION '; $calc='';
		}
		else{
			$calc='SQL_CALC_FOUND_ROWS ';	
		}
		if ($user['group_kindindex']=="j"){
			$query.= "(SELECT ".$calc."*,CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,pers_buried_place as place_order
			FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		else{
			$query.= "(SELECT ".$calc."*, pers_buried_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
		if($place_name) {
			$query.= " WHERE pers_buried_place ".name_qry($place_name,$part_place_name);
		}
		else {
			$query.= " WHERE pers_buried_place LIKE '_%'";
		}
		$query.=')';
		$start=true;
	}

	// *** Order by place and name: "Mons, van" or: "van Mons" ***
	if ($user['group_kindindex']=="j"){
		$query.=' ORDER BY place_order, concat_name';
	}
	else{
		$query.=' ORDER BY place_order, pers_lastname, pers_firstname';
	}

}

//*** Patronym list ***
if ($index_list=='patronym'){
	//Only in pers_patronym index if there is no pers_lastname!
	$query = "SELECT SQL_CALC_FOUND_ROWS * ".$make_date." FROM ".safe_text($_SESSION['tree_prefix'])."person
	WHERE pers_patronym LIKE '_%' AND pers_lastname=''  ORDER BY ".$orderby;
}

// **************************
// *** Generate indexlist ***
// **************************

	// *** Standard index ***
	if ($query=='' OR $index_list=='standard'){
		//$query = "SELECT SQL_CALC_FOUND_ROWS * ".$make_date." FROM ".safe_text($_SESSION['tree_prefix'])."person ORDER BY ".$orderby;
		//$count_qry = "SELECT *, COUNT(*) as teller ".$make_date." FROM ".safe_text($_SESSION['tree_prefix'])."person";

		$query = "SELECT * ".$make_date." FROM ".safe_text($_SESSION['tree_prefix'])."person ORDER BY ".$orderby;
		$count_qry = "SELECT COUNT(*) as teller ".$make_date." FROM ".safe_text($_SESSION['tree_prefix'])."person";

		// Mons, van or: van Mons
		if ($user['group_kindindex']=="j"){
			//$query= "SELECT SQL_CALC_FOUND_ROWS *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ".$make_date."
			//	FROM ".safe_text($_SESSION['tree_prefix'])."person ORDER BY ".$orderby;
			//$count_qry = "SELECT *, COUNT(*) as teller ".$make_date." FROM ".safe_text($_SESSION['tree_prefix'])."person";

			$query= "SELECT *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ".$make_date."
			FROM ".safe_text($_SESSION['tree_prefix'])."person ORDER BY ".$orderby;
			$count_qry = "SELECT COUNT(*) as teller ".$make_date." FROM ".safe_text($_SESSION['tree_prefix'])."person";
		}
	}

	include_once(CMS_ROOTPATH."menu.php");

	//*** Show number of persons and pages *****************************************
	$item=0; if (isset($_GET['item'])){ $item=$_GET['item']; }
	$start=0; if (isset($_GET["start"])){ $start=$_GET["start"]; }
	$nr_persons=$humo_option['show_persons'];

	/*
	// *** Total number of persons (results), needed to calculate pages ***
	if(!$spouse_firstname AND !$spouse_lastname) {
		if ($count_qry){
			// *** Use MySQL COUNT command to calculate nr. of persons (VERY FAST) ***
			$result=@mysql_query($count_qry,$db);
			$resultDb=@mysql_fetch_object($result);
			$count_persons=@$resultDb->teller;
		}
		else{
			// *** Use mysql_num rows for more difficult queries (SLOW!) ***
			$person_result2=mysql_query($query,$db);
			$count_persons=@mysql_num_rows($person_result2);
		}
	}
	else{
		$count_persons=0; // Isn't use if search is done for spouse...
	}

	// *** No LIMIT if search is done for spouse ***
	if(!$spouse_firstname AND !$spouse_lastname) {
		$person_result=mysql_query($query." LIMIT ".safe_text($item).",".$nr_persons,$db);
	}
	else{
		$person_result=mysql_query($query,$db);
	}
	*/

	if(!$spouse_firstname AND !$spouse_lastname) {
	
		//$person_result=mysql_query($query." LIMIT ".safe_text($item).",".$nr_persons,$db);
		$person_result = $dbh->query($query." LIMIT ".$item.",".$nr_persons);
		//$count_them = $dbh->query($count_qry);
		//$count_persons = $count_them->rowCount(); echo "COUNTPERS= ".$count_persons."<br>";
 		
		if ($count_qry){  
			// *** Use MySQL COUNT command to calculate nr. of persons in simple queries (faster than php num_rows and in simple queries faster than SQL_CAL_FOUND_ROWS) ***
			//$result=@mysql_query($count_qry,$db);
			//$resultDb=@mysql_fetch_object($result);
			$result= $dbh->query($count_qry);
			$resultDb = $result->fetch(PDO::FETCH_OBJ);
			$count_persons=@$resultDb->teller; 
		}
		else{  
			// *** USE SQL_CALC_FOUND_ROWS for complex queries (faster than mysql count) ***
			//$sql="SELECT FOUND_ROWS() AS 'found_rows'";
			//$rows = mysql_query($sql);
			//$rows = mysql_fetch_assoc($rows);
			$result = $dbh->query("SELECT FOUND_ROWS() AS 'found_rows'");
			$rows = $result->fetch();
			$count_persons = $rows['found_rows'];   
		}
 		
	}
	else{
		$person_result= $dbh->query($query);
		$count_persons=0; // Isn't used if search is done for spouse...
	}

	// *** Show error message if search in multiple trees is going wrong (nr of fields is different in some tables) ***
	// $person_result2=mysql_query($query,$db) or die("FAULT : " . mysql_error());
	// $person_result=mysql_query($query." LIMIT ".safe_text($item).",".$nr_persons,$db) or die("FAULT : " . mysql_error());

	if (CMS_SPECIFIC=='Joomla'){
		$list_var  = 'index.php?option=com_humo-gen&amp;task=list';  // for use without query string
		$list_var2 = 'index.php?option=com_humo-gen&amp;task=list&amp;'; // for use with query string
	}
	else {
		$list_var  = CMS_ROOTPATH.'list.php';
		$list_var2 = CMS_ROOTPATH.'list.php?';
	}

	if ($index_list=='places'){
		//echo '<div class="index_list1">';
		if($language['dir']=="ltr") {
			echo '<div class="left_box">';
		}
		else {
			echo '<div class="right_box">';		
		}

		//************** search places **************************************
		//print ' <form method="post" action="'.$list_var.'" style="display : inline;">';
		print ' <form method="post" action="'.$list_var.'">';
			echo __('Find place').':';

			$checked=''; if ($select_birth=='1'){$checked='checked';}
			print '<p><input type="Checkbox" name="select_birth" value="1" '.$checked.'> '.__('*').' '.__('birth pl.').'<br>';

			$checked=''; if ($select_bapt=='1'){$checked='checked';}
			print ' <input type="Checkbox" name="select_bapt" value="1" '.$checked.'> '.__('~').' '.__('bapt pl.').'<br>';

			$checked=''; if ($select_place=='1'){$checked='checked';}
			print ' <input type="Checkbox" name="select_place" value="1" '.$checked.'> '.__('^').' '.__('residence').'<br>';

			$checked=''; if ($select_death=='1'){$checked='checked';}
			print '<input type="Checkbox" name="select_death" value="1" '.$checked.'> '.__('&#134;').' '.__('death pl.').'<br>';

			$checked=''; if ($select_buried=='1'){$checked='checked';}
			print '<input type="Checkbox" name="select_buried" value="1" '.$checked.'> '.__('[]').' '.__('bur pl.');

		print '<p><select name="part_place_name">';
		echo '<option value="contains">'.__('Contains').'</option>';

		$select_item=''; if ($part_place_name=='equals'){ $select_item=' selected'; }
		echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';

		$select_item=''; if ($part_place_name=='starts_with'){ $select_item=' selected'; }
		echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
		print '</select>';

		print '<p><input type="text" name="place_name" value="'.$place_name.'" size="17"><br>';

		print '<input type="hidden" name="index_list" value="'.$index_list.'">';
		print '<p><input type="submit" value="'.__('Search').'" name="B1">';
		print '</form>';
		//***************** end search of places **********************************

		echo '</div>';
	}

	// *** Search fields ***
	if ($index_list=='standard' OR $index_list=='search' OR $index_list=='quicksearch'){

		// *** STANDARD SEARCH BOX ***
		//print '<form method="post" action="'.CMS_ROOTPATH.'list.php" style="display : inline;">';
		print '<form method="post" action="'.$list_var.'" style="display : inline;">';

		echo '<table align="center" class="humo" width="750">';

		echo '<tr>';

		// *** ADVANCED SEARCH BOX ***
		if ($adv_search==true){ 

			echo '<td align="right" class="no_border" >'.__('First name').':';
			print ' <select size="1" name="part_firstname">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($part_firstname=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($part_firstname=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			print '</select>';
			print ' <input type="text" name="pers_firstname" value="'.$pers_firstname.'" size="17"></td>';

			echo '<td align="right" class="no_border">';
			print __('Last name').':';
			print ' <select size="1" name="part_lastname">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($part_lastname=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($part_lastname=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			print '</select>';
			print ' <input type="text" name="pers_lastname" value="'.$pers_lastname.'" size="17"></td></tr>';
			
		}
		else{
			echo '<td class="no_border center" colspan="2">'.__('Enter name or part of name').'<br>';
			echo '<span style="font-size:10px;">"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"</span>';

			print '<input type="hidden" name="index_list" value="quicksearch">';
			$quicksearch='';
			if (isset($_POST['quicksearch'])){
				$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
				$_SESSION["save_quicksearch"]=$quicksearch;
			}
			if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
			print '<p><input type="text" name="quicksearch" value="'.$quicksearch.'" size="30" pattern=".{3,}" title="'.__('Minimum: 3 characters.').'"></p></td>';
		}

		// *** ADVANCED SEARCH BOX ***
		if ($adv_search==true){

			echo '<tr><td align="right" class="no_border">';
			print __('Year (or period) of birth:');
			print '<input type="text" name="birth_year" value="'.$birth_year.'" size="4">';
			print '&nbsp;&nbsp;('.__('till:').'&nbsp;';
			print '<input type="text" name="birth_year_end" value="'.$birth_year_end.'" size="4">&nbsp;)</td>';

			echo '<td align="right" class="no_border">';
			print __('Place of birth:');
			print ' <select size="1" name="part_birth_place">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($part_birth_place=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($part_birth_place=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			print '</select>';
			echo ' <input type="text" name="birth_place" value="'.$birth_place.'" size="17"></td></tr>';

			echo '<tr><td align="right" class="no_border">';
			print __('Year (or period) of death:');
			echo '<input type="text" name="death_year" value="'.$death_year.'" size="4">';
			print '&nbsp;&nbsp;('.__('till:').'&nbsp;';
			print '<input type="text" name="death_year_end" value="'.$death_year_end.'" size="4">&nbsp;)</td>';

			echo '<td align="right" class="no_border">';
			print __('Place of death:');
			print ' <select size="1" name="part_death_place">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($part_death_place=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($part_death_place=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			print '</select>';
			echo ' <input type="text" name="death_place" value="'.$death_place.'" size="17"></td></tr>';

			echo '<tr><td align="right" class="no_border">';
			print __('Choose sex:');
			$check=''; if ($sexe=='both'){ $check=' checked'; }
			print '<input type="radio" name="sexe" value="both"'.$check.'>'.__('All').'&nbsp;&nbsp;';
			$check=''; if ($sexe=='M'){ $check=' checked'; }
			print '<input type="radio" name="sexe" value="M"'.$check.'>'.__('Male').'&nbsp;&nbsp;';
			$check=''; if ($sexe=='F'){ $check=' checked'; }
			print '<input type="radio" name="sexe" value="F"'.$check.'>'.__('Female').'&nbsp;&nbsp;';
			$check=''; if ($sexe=='Unknown'){ $check=' checked'; }
			print '<input type="radio" name="sexe" value="Unknown"'.$check.'>'.__('Unknown');
			echo '</td>';

			echo '<td align="right" class="no_border">';
			print __('Own code').':';
			print ' <select size="1" name="part_own_code">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($part_own_code=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($part_own_code=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			print '</select>';
			echo ' <input type="text" name="own_code" value="'.$own_code.'" size="17">';
			echo '</td></tr>';

			echo '<tr><td align="right" class="no_border">';
			echo __('Partner firstname').':';
			print ' <select size="1" name="part_spouse_firstname">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($part_spouse_firstname=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($part_spouse_firstname=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			print '</select>';
			echo ' <input type="text" name="spouse_firstname" value="'.$spouse_firstname.'" size="17"></td>';

			echo '<td align="right" class="no_border">';
			echo __('Partner lastname').':';
			print ' <select size="1" name="part_spouse_lastname">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($part_spouse_lastname=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($part_spouse_lastname=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			print '</select>';
			echo ' <input type="text" name="spouse_lastname" value="'.$spouse_lastname.'" size="17"></td></tr>';

		}	// *** End of advanced search fields ***

		// *** Check for multiple family trees ***
		print '<tr><td colspan="2" class="no_border center">';
		//$datasql2 = mysql_query("SELECT * FROM humo_trees",$db);
		$datasql2 = $dbh->query("SELECT * FROM humo_trees");
		$num_rows2 = $datasql2->rowCount();
		//$num_rows2 = mysql_num_rows($datasql2);
		if ($num_rows2>1){
			$checked=''; if ($search_database=="tree_selected"){ $checked='CHECKED'; }
			print '<input type="radio" name="search_database" value="tree_selected" '.$checked.'> '.
				__('Selected family tree');
			$checked=''; if ($search_database=="all_trees"){ $checked='checked'; }
			print '<input type="radio" name="search_database" value="all_trees" '.$checked.'> '.__('All family trees');
			$checked=''; if ($search_database=="all_but_this"){ $checked='checked'; }
			print '<input type="radio" name="search_database" value="all_but_this" '.$checked.'> '.__('All but selected tree');
		}

		print '&nbsp;&nbsp; <input type="submit" value="'.__('Search').'" name="B1">';

		if ($adv_search==true){

			//print '&nbsp;<a href="'.CMS_ROOTPATH.'list.php?adv_search=0">'.__('Standard search').'</a>';
			print '&nbsp;<a href="'.$list_var2.'adv_search=0">'.__('Standard search').'</a>';

			//echo '<input type="hidden" name="adv_search2" value="1">';
			echo '<input type="hidden" name="adv_search" value="1">';

			//======== HELP POPUP ========================
			echo '<div class="table_header '.$rtlmarker.'sddm" style="display: inline;">';
			echo ' <a href="#"';
			echo ' style="display:inline" ';
			echo 'onmouseover="mopen(event,\'help_menu\',10,150)"';
			echo 'onmouseout="mclosetime()">';
			echo '&nbsp;&nbsp;&nbsp;<strong>'.__('Help').'</strong>';
			echo '</a>';
			echo '<div class="sddm_fixed" style="z-index:40; text-align:'.$alignmarker.'; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
				echo '<table width="98%" class="humo"><tr>';
				echo '<td width="50">'.__('Tip').':</td>';

				echo '<td>';
				echo __('With Advanced Search you can easily create lists like: all persons with surname <b>Schaap</b> who were born <b>between 1820 and 1840</b> in <b>Amsterdam</b><br>
You can also search without a name: all persons who <b>died in 1901</b> in <b>Amstelveen.</b>');
				echo '</td></tr>';

				echo '<tr><td>';
				echo __('Note:</td>
<td>When you use the birth and/or death search boxes please note this:<br>
&nbsp;&nbsp;1. Persons for whom no birth/death data exist in the database, will not be found.<br>
2. Persons with privacy settings will not be shown, unless you are logged in with the proper permissions.<br>
&nbsp;&nbsp;These persons can be found by searching by name and/or surname only.');
				echo '</td></tr></table>';

				echo '</div>';
			echo '</div><br>';
			//=================================

		}
		else{
			//print '&nbsp;<a href="'.CMS_ROOTPATH.'list.php?adv_search=1">'.__('Advanced search').'</a><br>';
			//print '&nbsp;<a href="'.$list_var2.'adv_search=1">'.__('Advanced search').'</a><br>';
			print '&nbsp;<a href="'.$list_var2.'adv_search=1&index_list=search">'.__('Advanced search').'</a><br>';
		}

		print '</td></tr></table></form>';
	}

	if (CMS_SPECIFIC=='Joomla'){ $uri_path_string = "index.php?option=com_humo-gen&amp;task=list&amp;"; }
	else { $uri_path_string = $uri_path."list.php?"; }

	// *** Check for search results ***
	if ($person_result->rowCount()==0) {
	//if (@mysql_num_rows($person_result)==0) {
		$line_pages='';
		//echo '<br><div class="center">'.__('No names found.').'</div>';
	}
	else{
		$line_pages=__('Page');

		// "<="
		if ($start>1){
			$start2=$start-20;
			$calculated=($start-2)*$nr_persons;
			$line_pages.= ' <a href="'.$uri_path_string.
			"index_list=".$index_list.
			"&amp;start=".$start2.
			"&amp;item=".$calculated.
			'">&lt;= </a>';
		}
		if ($start<=0){$start=1;}

		// 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
		for ($i=$start; $i<=$start+19; $i++) {
			$calculated=($i-1)*$nr_persons;
			if ($calculated<$count_persons){
				if ($item==$calculated){
					$line_pages.=  " <b>$i</b>";
				}
				else {
					$line_pages.= ' <a href="'.$uri_path_string.
					"index_list=".$index_list.
					"&amp;start=".$start.
					"&amp;item=".$calculated.
					'"> '.$i.'</a>';
				}
			}
		}

		// "=>"
		$calculated=($i-1)*$nr_persons;
		if ($calculated<$count_persons){
			$line_pages.= ' <a href="'.$uri_path_string.
			"index_list=".$index_list.
			"&amp;start=".$i.
			"&amp;item=".$calculated.
			'"> =&gt;</a>';
		}
	}

	echo '<div class="index_list1">';

	// *** Don't use this code if search is done with partner ***
	if(!$spouse_firstname AND !$spouse_lastname) {
		echo $count_persons.__(' persons found.');
	}

	// *** Normal or expanded list ***
	if (isset($_POST['list_expanded'])){
		if ($_POST['list_expanded']=='0'){
			$_SESSION['save_list_expanded']='0';
		}
		else{
			$_SESSION['save_list_expanded']='1';
		}
	}
	global $list_expanded; // for joomla
	//$list_expanded=false; // *** Default value ***
	//if (isset($_SESSION['save_list_expanded']) AND $_SESSION['save_list_expanded']=='1'){
	//	$list_expanded=true;
	//}

	$list_expanded=true; // *** Default value ***
	if (isset($_SESSION['save_list_expanded'])){
		if ($_SESSION['save_list_expanded']=='1')
			$list_expanded=true;
		else $list_expanded=false;
	}

	// *** Button: normal or expanded list ***
	$button_line= "item=".$item;   // the ? or & is already included in the $uri_path_string created above
	if (isset($_GET['start'])){
		$button_line.= "&amp;start=".$_GET['start'];
	}
	else{
		$button_line.= "&amp;start=1";
	}
	$button_line.=  "&amp;index_list=".$index_list;

	print ' <form method="POST" action="'.$uri_path_string.$button_line.'" style="display : inline;">';

	if ($list_expanded==true){
		print '<input type="hidden" name="list_expanded" value="0">';
		print '<input type="Submit" name="submit" value="'.__('Concise view').'">';
	}
	else{
		print '<input type="hidden" name="list_expanded" value="1">';
		print '<input type="Submit" name="submit" value="'.__('Expanded view').'">';
	}
	print '</form>';

	// SORT BY **************************************
	if($index_list != "places") {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo __('Sort by:').' <form method="" style="display : inline;">';
		echo '<select onChange="window.location=this.value;"  "size="1" id="sortby" name="sortby">';
		echo '<option value="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_lastname"';
		$select=''; if($selectsort=="sort_lastname") { $select=" SELECTED "; }
		if($index_list!='patronym') {
			echo $select.'>'.strtolower(__('Last name')).'</option>';
		}
		else {
			echo $select.'>'.__('patronym').'</option>';
		}
		echo '<option value="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_firstname"';
		$select=''; if($selectsort=="sort_firstname") { $select=" SELECTED "; }
		echo $select.'>'.strtolower(__('First name')).'</option>';
		echo '<option value="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_birthdate"';
		$select=''; if($selectsort=="sort_birthdate") { $select=" SELECTED "; }
		echo $select.'>'.__('birth date').'</option>';
		echo '<option value="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_deathdate"';
		$select=''; if($selectsort=="sort_deathdate") { $select=" SELECTED "; }
		echo $select.'>'.__('death date').'</option>';
		echo '<option value="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_baptdate"';
		$select=''; if($selectsort=="sort_baptdate") { $select=" SELECTED "; }
		echo $select.'>'.__('baptism date').'</option>';
		echo '<option value="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_burieddate"';
		$select=''; if($selectsort=="sort_burieddate") { $select=" SELECTED "; }
		echo $select.'>'.__('burial date').'</option>';
		echo '</select>';
		echo '</form>';
	}
	// "CHANGE SORT ORDER" BUTTON **************************************
	if($index_list!='places') {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;'.__('Change sort order:').'&nbsp;';
		print ' <form method="POST" action="'.'list.php?index_list='.$index_list.'&start=1&item=0'.'" style="display : inline;">';
		if ($sort_desc==1){
			print '<input type="hidden" name="sort_desc" value="0">';
			print '<input type="image" name="submit" src="images/sort-button-up.gif" style="vertical-align:middle">';
		}
		else{
			print '<input type="hidden" name="sort_desc" value="1">';
			print '<input type="image" name="submit" src="images/sort-button-down.gif" style="vertical-align:middle">';
		}
		print '</form>';
	}

	// *** Don't use code if search is done with partner ***
	if(!$spouse_firstname AND !$spouse_lastname) {
		echo '<br>'.$line_pages;
	}

	// *** No results ***
	if ($person_result->rowCount()==0) {
	//if (@mysql_num_rows($person_result)==0) {
		echo '<br><div class="center">'.__('No names found.').'</div>';
	}

	$dir="";
	if($language["dir"]=="rtl") {
		$dir="rtl"; // loads the proper CSS for rtl display (rtlindex_list2):
	}

	// with extra sort date column, set smaller left margin
	$listnr="2";      // default 20% margin
	if($index_list != "places" AND ($selectsort=='sort_birthdate' OR $selectsort=='sort_deathdate' OR $selectsort=='sort_baptdate' OR $selectsort=='sort_burieddate')) {
		$listnr="3";   // 5% margin
	}
	echo '<div class="'.$dir.'index_list'.$listnr.'">';

	//*** Show persons ******************************************************************
	$privcount=0; // *** Count privacy persons ***

	$selected_place="";

	// table to hold left sort date column (when necessary) and right person list column
	echo '<table style="cellpadding:0px;border-collapse:collapse;">';
	if($index_list != "places" AND ($selectsort=='sort_birthdate' OR $selectsort=='sort_deathdate' OR $selectsort=='sort_baptdate' OR $selectsort=='sort_burieddate')) {
		// set header for extra column to explain to users
		echo '<tr><td style="text-align:center">'.__('Sort order').':</td><td>&nbsp;</td></tr>';
	}
	//while (@$personDb=mysql_fetch_object($person_result)){
	while (@$personDb = $person_result->fetch(PDO::FETCH_OBJ)) {
		$spouse_found='1';

		// *** Search name of spouse ***
		if($spouse_firstname OR $spouse_lastname) {
			$spouse_found='0';
			$person_fams=explode(";",$personDb->pers_fams);

			for ($marriage_loop=0; $marriage_loop<count($person_fams); $marriage_loop++){
				// *** Search all persons with a spouse IN the same tree as the 1st person ***
				//$fam_qry = "SELECT * FROM ".safe_text($personDb->pers_tree_prefix).'family WHERE fam_gedcomnumber="'.safe_text($person_fams[$marriage_loop]).'"';
				$fam_result = $dbh->query("SELECT * FROM ".safe_text($personDb->pers_tree_prefix).'family WHERE fam_gedcomnumber="'.$person_fams[$marriage_loop].'"');
				//$fam_result=mysql_query($fam_qry,$db);
				//while($famDb=mysql_fetch_object($fam_result)){
				while($famDb= $fam_result->fetch(PDO::FETCH_OBJ)) {

					// *** Search all persons with a spouse IN the same tree as the 1st person ***
					$spouse_qry = "SELECT * FROM ".safe_text($personDb->pers_tree_prefix)."person WHERE";
					if ($user['group_kindindex']=="j"){
						$spouse_qry= "SELECT *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name
							FROM ".safe_text($personDb->pers_tree_prefix)."person WHERE";
					}

					if ($personDb->pers_gedcomnumber==$famDb->fam_man){
						$spouse_qry.=' pers_gedcomnumber="'.safe_text($famDb->fam_woman).'"';
					}
					else{
						$spouse_qry.=' pers_gedcomnumber="'.safe_text($famDb->fam_man).'"';
					}

					if ($spouse_lastname) {
						if ($spouse_lastname==__('...')){
							$spouse_qry.=" AND pers_lastname=''";
						}
						elseif ($user['group_kindindex']=="j"){
							$spouse_qry.=" AND CONCAT( REPLACE(pers_prefix,'_',' ') ,pers_lastname) ".name_qry($spouse_lastname, $part_spouse_lastname);
						}
						else {
							$spouse_qry.=" AND pers_lastname ".name_qry($spouse_lastname, $part_spouse_lastname);
						}
					}
					//if ($pers_prefix){
					//  $spouse_qry.=" AND pers_prefix='".$pers_prefix."'";
					//}
					if ($spouse_firstname){
						$spouse_qry.=" AND pers_firstname ".name_qry($spouse_firstname, $part_spouse_firstname);
					}
					$spouse_result= $dbh->query($spouse_qry);
					$spouseDb= $spouse_result->fetch(PDO::FETCH_OBJ);
					if (isset($spouseDb->pers_id)){ $spouse_found='1'; break; }
				}
			}


		}  // End of spouse search


		// *** Show search results ***
		if ($spouse_found=='1'){
			// Added by Yossi
			$person_cls = New person_cls;
			$person_cls->construct($personDb);
			$privacy=$person_cls->privacy;
			if($privacy==1) { // Privacy restricted person
				if($birth_place=='' AND $birth_year=='' AND $death_place=='' AND $death_year=='') {
					// No search using birth/death place and/or date

					// *** Extra privacy filter check for total_filter ***
					if ($user["group_pers_hide_totally_act"]=='j' AND strpos(' '.$personDb->pers_own_code,$user["group_pers_hide_totally"])>0){
						$privcount++;
					}
					else{
						show_person($personDb);
					}

				}
				else {
					$privcount++;
					// If it is a privacy person and it's an birth/ death search
					//   - don't show anything:
					// it's not allowed to search a privacy restricted with non-privacy data!
				}
			}
			else {
				// No privacy restrictions

				// *** Extra privacy filter check for total_filter ***
				if ($user["group_pers_hide_totally_act"]=='j' AND strpos(' '.$personDb->pers_own_code,$user["group_pers_hide_totally"])>0){
					$privcount++;
				}
				else{
					show_person($personDb);
				}
			}
		}
	}

	echo '</table>';
	if($privcount) { echo "<br>".$privcount.__(' persons are not shown due to privacy settings').".<br>";}

	echo '</div>';

	// *** Don't executed this code if spouse search is used ***
	if(!$spouse_firstname AND !$spouse_lastname) {
		echo '<br>'.$line_pages;
	}

	echo '</div>';

//for testing only:
//echo 'Query: '.$query." LIMIT ".safe_text($item).",".$nr_persons.'<br>';
//echo 'Count qry: '.$count_qry.'<br>';
//echo '<p>index_list: '.$index_list;
//echo '<br>nr. of persons: '.$count_persons;

include_once(CMS_ROOTPATH."footer.php");
?>