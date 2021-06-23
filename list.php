<?php
include_once("header.php"); //returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
//error_reporting(E_ALL);
@set_time_limit(300);

// *** show person ***
function show_person($personDb){
	global $dbh, $db_functions, $index_list, $selected_place, $language, $user;
	global $bot_visit, $humo_option, $uri_path, $search_database, $list_expanded;
	global $selected_language, $privacy, $dirmark1, $dirmark2, $rtlmarker;
	global $select_birth, $select_bapt, $select_place, $select_death, $select_buried;
	global $selectsort;

	$db_functions->set_tree_id($personDb->pers_tree_id);

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

	echo '<tr>';

	// *** Show extra columns before a person in index places ***
	if ($index_list=='places'){

		if ($selected_place!=$personDb->place_order)
			echo '<td colspan="7"><b>'.$dirmark2."$personDb->place_order</b></td></tr><tr>";
		$selected_place=$personDb->place_order;

		echo '<td valign="top" style="white-space:nowrap;width:90px">';

		if ($select_birth=='1'){
			if ($selected_place==$personDb->pers_birth_place)
				echo '<span class="place_index place_index_selected">'.__('*').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

		if ($select_bapt=='1'){
			if ($selected_place==$personDb->pers_bapt_place)
				echo '<span class="place_index place_index_selected">'.__('~').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

		if ($select_place=='1'){
			if ($selected_place==$personDb->pers_place_index)
				echo '<span class="place_index place_index_selected">'.__('^').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

		if ($select_death=='1'){
			if ($selected_place==$personDb->pers_death_place)
				echo '<span class="place_index place_index_selected">'.__('&#134;').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

		if ($select_buried=='1'){
			if ($selected_place==$personDb->pers_buried_place)
				echo '<span class="place_index place_index_selected">'.__('[]').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

		echo '</td>';
	}

	echo '<td valign="top" style="border-right:0px; white-space:nowrap;">'; 
	// *** Show person popup menu ***
	echo $person_cls->person_popup_menu($personDb);

	// *** Show picture man or wife ***
	if ($personDb->pers_sexe=="M")
		echo $dirmark1.' <img src="'.CMS_ROOTPATH.'images/man.gif" alt="man" style="vertical-align:top">';
	elseif ($personDb->pers_sexe=="F")
		echo $dirmark1.' <img src="'.CMS_ROOTPATH.'images/woman.gif" alt="woman" style="vertical-align:top">';
	else
		echo $dirmark1.' <img src="'.CMS_ROOTPATH.'images/unknown.gif" alt="unknown" style="vertical-align:top">';

	if($humo_option['david_stars'] == "y") {
		$camps="Auschwitz|Oświęcim|Sobibor|Bergen-Belsen|Bergen Belsen|Treblinka|Holocaust|Shoah|Midden-Europa|Majdanek|Belzec|Chelmno|Dachau|Buchenwald|Sachsenhausen|Mauthausen|Theresienstadt|Birkenau|Kdo |Kamp Amersfoort|Gross-Rosen|Gross Rosen|Neuengamme|Ravensbrück|Kamp Westerbork|Kamp Vught|Kommando Sosnowice|Ellrich|Schöppenitz|Midden Europa|Lublin|Tröbitz|Kdo Bobrek|Golleschau|Blechhammer|Kdo Gleiwitz|Warschau|Szezdrzyk|Polen|Kamp Bobrek|Monowitz|Dorohucza|Seibersdorf|Babice|Fürstengrube|Janina|Jawischowitz|Katowice|Kaufering|Krenau|Langenstein|Lodz|Ludwigsdorf|Melk|Mühlenberg|Oranienburg|Sakrau|Schwarzheide|Spytkowice|Stutthof|Tschechowitz|Weimar|Wüstegiersdorf|Oberhausen|Minsk|Ghetto Riga|Ghetto Lodz|Flossenbürg|Malapane";

			if(preg_match("/($camps)/i",$personDb->pers_death_place)!==0 OR 
				preg_match("/($camps)/i",$personDb->pers_buried_place)!==0  OR strpos(strtolower($personDb->pers_death_place), "oorlogsslachtoffer") !==FALSE) {
				echo '<img src="'.CMS_ROOTPATH.'images/star.gif" alt="star">&nbsp;';
			}
	}

	// *** Add own icon by person, using a file name in own code ***
	if($personDb->pers_own_code !='' AND is_file("images/".$personDb->pers_own_code.".gif")){
		if ($personDb->pers_own_code!='foto'){ // *** Remove photo.gif icon, new method is used to show photo icon ***
			echo  $dirmark1.'<img src="'.CMS_ROOTPATH.'images/'.$personDb->pers_own_code.'.gif" alt="'.$personDb->pers_own_code.'">&nbsp;';
		}
	}

	// *** Show camera icon if there is a photo ***
	if ($user['group_pictures']=='j' AND !$privacy){
		global $dataDb;
		$tree_pict_path=$dataDb->tree_pict_path; if (substr($tree_pict_path,0,1)=='|') $tree_pict_path='media/';
		$picture_qry=$db_functions->get_events_connect('person',$personDb->pers_gedcomnumber,'picture');
		// *** Only check 1st picture ***
		if (isset($picture_qry[0])){
			echo  $dirmark1.'<img src="'.CMS_ROOTPATH.'images/photo.gif" alt="photo">&nbsp;';
		}
	}

	echo '</td><td style="border-left:0px;">';

	// *** Show name of person ***
	//$start_url=$person_cls->person_url($personDb);	// *** Get link to family ***
	// *** Person url example (I23 optional): http://localhost/humo-genealogy/family/2/F10/I23/ ***
	$start_url=$person_cls->person_url($personDb->pers_tree_id,$personDb->pers_indexnr,$personDb->pers_gedcomnumber);
	echo ' <a href="'.$start_url.'">'.$index_name.'</a>';

	//*** Show spouse/ partner ***
	if ($list_expanded==true AND $personDb->pers_fams){
		$marriage_array=explode(";",$personDb->pers_fams);
		$nr_marriages=count($marriage_array);
		for ($x=0; $x<=$nr_marriages-1; $x++){
			$fam_partnerDb = $db_functions->get_family($marriage_array[$x]);

			// *** This check is better then a check like: $personDb->pers_sexe=='F', because of unknown sexe or homosexual relations. ***
			if ($personDb->pers_gedcomnumber==$fam_partnerDb->fam_man)
				$partner_id=$fam_partnerDb->fam_woman;
			else
				$partner_id=$fam_partnerDb->fam_man;

			$relation_short=__('&amp;');
			if ($fam_partnerDb->fam_marr_date OR $fam_partnerDb->fam_marr_place OR $fam_partnerDb->fam_marr_church_date OR $fam_partnerDb->fam_marr_church_place OR $fam_partnerDb->fam_kind=='civil')
				$relation_short=__('X');
			if($fam_partnerDb->fam_div_date OR $fam_partnerDb->fam_div_place)
				$relation_short=__(') (');

			if ($partner_id!='0' AND $partner_id!=''){
				$partnerDb = $db_functions->get_person($partner_id);
				$partner_cls = New person_cls;
				$name=$partner_cls->person_name($partnerDb);
			}
			else{
				$name["standard_name"]=__('N.N.');
			}

			if ($nr_marriages>1 and $x>0) echo ',';
			//echo ' <span class="index_partner" style="font-size:10px;">';
			echo ' <span class="index_partner">';
			if ($nr_marriages>1){
				if ($x==0) echo __('1st');
				elseif ($x==1) echo __('2nd');
				elseif ($x==2) echo __('3rd');
				elseif ($x>2) echo ($x+1).__('th');
			}
			echo ' '.$relation_short.' '.rtrim($name["standard_name"]).'</span>';
		}
	}
	// *** End spouse/ partner ***


	echo '</td><td style="white-space:nowrap;">';
		$info="";
		if ($personDb->pers_bapt_date)
			$info=__('~').' '.date_place($personDb->pers_bapt_date, '');
		if ($personDb->pers_birth_date)
			$info=__('*').' '.date_place($personDb->pers_birth_date, '');
		if ($privacy==1 and $info) echo ' '.__('PRIVACY FILTER');
			else echo $info;

	echo '</td><td>';
		$info="";
		if ($personDb->pers_bapt_place)
			$info=__('~').' '.$personDb->pers_bapt_place;
		if ($personDb->pers_birth_place)
			$info=__('*').' '.$personDb->pers_birth_place;
		if ($privacy==1 and $info) echo ' '.__('PRIVACY FILTER');
			else echo $info;

	echo '</td><td style="white-space:nowrap;">';
		$info="";
		if ($personDb->pers_buried_date)
			$info=__('[]').' '.date_place($personDb->pers_buried_date, '');
		if ($personDb->pers_death_date)
			$info=__('&#134;').' '.date_place($personDb->pers_death_date, '');
		if ($privacy==1 and $info) echo ' '.__('PRIVACY FILTER');
			else echo $info;

	echo '</td><td>';
		$info="";
		if ($personDb->pers_buried_place)
			$info=__('[]').' '.$personDb->pers_buried_place;
		if ($personDb->pers_death_place)
			$info=__('&#134;').' '.$personDb->pers_death_place;
		if ($privacy==1 and $info) echo ' '.__('PRIVACY FILTER');
			else echo $info;

		// *** Show name of family tree, if search in multiple family trees is used ***
		if ($search_database=='all_trees' OR $search_database=='all_but_this'){
			//$treetext=show_tree_text($pers_tree_id, $selected_language);
			$treetext=show_tree_text($personDb->pers_tree_id, $selected_language);
			echo '</td><td>';
			echo '<i><font size="-1">'.$treetext['name'].'</font></i>';
		}
 
	echo '</td></tr>';

} // *** end function show person ***

// *** index ***
$index_list='quicksearch';

// *** Reset search fields if necessary ***
//if (isset($_POST['pers_firstname']) OR isset($_GET['pers_lastname']) OR isset($_GET['reset']) OR isset($_POST['quicksearch'])){
if (isset($_POST['pers_firstname']) OR isset($_GET['pers_lastname']) OR isset($_GET['pers_firstname']) OR isset($_GET['reset']) OR isset($_POST['quicksearch'])) {
	unset ($_SESSION["save_search_tree_prefix"]);
	unset ($_SESSION["save_search_database"]);
	unset ($_SESSION["save_adv_search"]);
	//unset ($_SESSION["save_quicksearch"]);

	// *** Array containing multiple search values ***
	unset ($_SESSION["save_selection"]);

	$index_list='search';
}

if (isset($_POST["index_list"])) $index_list=$_POST['index_list'];
if (isset($_GET["index_list"])) $index_list=$_GET['index_list'];

// *** Extra reset needed for "search in all family trees" ***
if ($index_list!='search' AND $index_list!='quicksearch') unset ($_SESSION["save_search_database"]);

// *** Save selected "search" family tree (can be used to erase search values if tree is changed) ***
$_SESSION["save_search_tree_prefix"]=safe_text_db($_SESSION['tree_prefix']);

//************* SORT CHOICES *********************

$make_date=''; // we only need this when sorting by date

$desc_asc=" ASC "; $sort_desc=0;
if(isset($_SESSION['sort_desc'])) {
	if($_SESSION['sort_desc'] == 1){ $desc_asc=" DESC "; $sort_desc=1; }
		else{ $desc_asc=" ASC "; $sort_desc=0; }
}
if(isset($_GET['sort_desc'])) {
	if($_GET['sort_desc'] == 1) { $desc_asc=" DESC "; $sort_desc=1; $_SESSION['sort_desc']=1; }
		else { $desc_asc=" ASC "; $sort_desc=0; $_SESSION['sort_desc']=0; }
}

// *** SOME DEFAULTS ***
$last_or_patronym=" pers_lastname ";
if ($index_list=='patronym') $last_or_patronym = " pers_patronym ";

$orderby = $last_or_patronym.$desc_asc.", pers_firstname ".$desc_asc;
if ($user['group_kindindex']=="j" AND $index_list!='patronym'){ $orderby = " concat_name ".$desc_asc; }

$selectsort = ''; if(isset($_SESSION['sort']) AND !isset($_GET['sort'])) { $selectsort = $_SESSION['sort']; }

if(isset($_GET['sort'])) {
	if($_GET['sort']=="sort_lastname") { $selectsort="sort_lastname"; $_SESSION['sort']=$selectsort; }
	if($_GET['sort']=="sort_firstname") { $selectsort="sort_firstname"; $_SESSION['sort']=$selectsort; }
	if($_GET['sort']=="sort_birthdate") { $selectsort="sort_birthdate"; $_SESSION['sort']=$selectsort; }
	if($_GET['sort']=="sort_birthplace") { $selectsort="sort_birthplace"; $_SESSION['sort']=$selectsort; }
	//if($_GET['sort']=="sort_baptdate") { $selectsort="sort_baptdate"; $_SESSION['sort']=$selectsort; }
	if($_GET['sort']=="sort_deathdate") { $selectsort="sort_deathdate"; $_SESSION['sort']=$selectsort; }
	if($_GET['sort']=="sort_deathplace") { $selectsort="sort_deathplace"; $_SESSION['sort']=$selectsort; }
	//if($_GET['sort']=="sort_burieddate") { $selectsort="sort_burieddate"; $_SESSION['sort']=$selectsort; }
}

if ($selectsort){
	if($selectsort=="sort_lastname")  {
		$orderby = $last_or_patronym.$desc_asc.", pers_firstname ".$desc_asc;
		if ($user['group_kindindex']=="j" AND $index_list!='patronym'){ $orderby = " concat_name ".$desc_asc; }
	}
	if($selectsort=="sort_firstname") {
		$orderby = " pers_firstname ".$desc_asc.",".$last_or_patronym.$desc_asc;
	}

	if($selectsort=="sort_birthdate") {
		
		// *** Replace ABT, AFT, BEF, EST, CAL and BET...AND items and sort by birth or baptise date ***
		$make_date= ", CASE
			WHEN pers_birth_date = '' AND SUBSTR(CONCAT(' ',pers_bapt_date),-4,1)= ' ' THEN 
			replace(
				replace(
					replace(
						replace(
							replace(
								replace(
									UPPER(
										CONVERT(
											CONCAT(
												SUBSTR(pers_bapt_date,1,LENGTH(pers_bapt_date)-3),'0',SUBSTR(pers_bapt_date,-3)
											) USING latin1
										)
									),
								'ABT ',''),
							'AFT ',''),
						'BEF ',''),
					'EST ',''),
				'CAL ',''),
			'AND ','       ')
			WHEN pers_birth_date = '' AND SUBSTR(CONCAT(' ',pers_bapt_date),-4,1)!= ' ' THEN
				replace(
					replace(
						replace(
							replace(
								replace(
									replace(
										UPPER(
											CONVERT(pers_bapt_date USING latin1)
										),
									'ABT ',''),
								'AFT ',''),
							'BEF ',''),
						'EST ',''),
					'CAL ',''),
				'AND ','       ')
			WHEN pers_birth_date != '' AND SUBSTR(CONCAT(' ',pers_birth_date),-4,1)= ' ' THEN
				replace(
					replace(
						replace(
							replace(
								replace(
									replace(
										UPPER(
											CONVERT(
												CONCAT(SUBSTR(pers_birth_date,1,LENGTH(pers_birth_date)-3),'0',SUBSTR(pers_birth_date,-3))
											USING latin1)
										),
									'ABT ',''),
								'AFT ',''),
							'BEF ',''),
						'EST ',''),
					'CAL ',''),
				'AND ','       ')
			WHEN pers_birth_date != '' AND SUBSTR(CONCAT(' ',pers_birth_date),-4,1)!= ' ' THEN
				replace(
					replace(
						replace(
							replace(
								replace(
									replace(
										UPPER(
											CONVERT(pers_birth_date USING latin1)
										),
									'ABT ',''),
								'AFT ',''),
							'BEF ',''),
						'EST ',''),
					'CAL ',''),
				'AND ','       ')
				END AS year";
 
		$orderby = " CONCAT( substring(year,-4),
			date_format( str_to_date( substring(year,-8,3),'%b' ) ,'%m'),
			date_format( str_to_date( substring(year,-11,2),'%d' ),'%d')
			) ".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
	}
	if($selectsort=="sort_birthplace") {
		//$orderby = " pers_birth_place ".$desc_asc.",".$last_or_patronym.$desc_asc;
		$make_date= ", CASE
			WHEN pers_birth_place = '' THEN pers_bapt_place ELSE pers_birth_place
			END AS place";
		$orderby = " place".$desc_asc.", ".$last_or_patronym.$desc_asc;
	}

	if($selectsort=="sort_deathdate") {
		//$make_date = ", right(pers_death_date,4) as year,
		//date_format( str_to_date( substring(pers_death_date,-8,3),'%b' ),'%m') as month,
		//date_format( str_to_date( left(pers_death_date,2),'%d' ),'%d') as day";
		//$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";

		// *** Replace ABT, AFT, BEF items and sort by death or buried date ***

 		$make_date= ", CASE
			WHEN pers_death_date = '' AND SUBSTR(CONCAT(' ',pers_buried_date),-4,1)= ' ' THEN replace(replace(replace(replace(replace(UPPER(CONVERT(CONCAT(SUBSTR(pers_buried_date,1,LENGTH(pers_buried_date)-3),'0',SUBSTR(pers_buried_date,-3)) USING latin1)),'ABT ',''),'AFT ',''),'BEF ',''),'EST ',''),'AND ','       ') 
			WHEN pers_death_date = '' AND SUBSTR(CONCAT(' ',pers_buried_date),-4,1)!= ' ' THEN replace(replace(replace(replace(replace(UPPER(CONVERT(pers_buried_date USING latin1)),'ABT ',''),'AFT ',''),'BEF ',''),'EST ',''),'AND ','       ')
			WHEN pers_death_date != '' AND SUBSTR(CONCAT(' ',pers_death_date),-4,1)= ' ' THEN replace(replace(replace(replace(replace(UPPER(CONVERT(CONCAT(SUBSTR(pers_death_date,1,LENGTH(pers_death_date)-3),'0',SUBSTR(pers_death_date,-3)) USING latin1)),'ABT ',''),'AFT ',''),'BEF ',''),'EST ',''),'AND ','       ') 
			WHEN pers_death_date != '' AND SUBSTR(CONCAT(' ',pers_death_date),-4,1)!= ' ' THEN replace(replace(replace(replace(replace(UPPER(CONVERT(pers_death_date USING latin1)),'ABT ',''),'AFT ',''),'BEF ',''),'EST ',''),'AND ','       ') 
			END AS year";

		$orderby = " CONCAT( right(year,4),
			date_format( str_to_date( substring(year,-8,3),'%b' ) ,'%m'),
			date_format( str_to_date( substring(year,-11,2),'%d' ),'%d')
			)".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
	}
	if($selectsort=="sort_deathplace") {
		//$orderby = " pers_death_place ".$desc_asc.",".$last_or_patronym.$desc_asc;
		$make_date= ", CASE
			WHEN pers_death_place = '' THEN pers_buried_place ELSE pers_death_place
			END AS place";
		$orderby = " place".$desc_asc.", ".$last_or_patronym.$desc_asc;
	}
	/*
	// *** Old code for seperate columns baptise and buried dates ***
	if($selectsort=="sort_baptdate") {
		$make_date = ", right(pers_bapt_date,4) as year,
		date_format( str_to_date( substring(pers_bapt_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_bapt_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
	}
	if($selectsort=="sort_burieddate") {
		$make_date = ", right(pers_buried_date,4) as year,
		date_format( str_to_date( substring(pers_buried_date,-8,3),'%b' ),'%m') as month,
		date_format( str_to_date( left(pers_buried_date,2),'%d' ),'%d') as day";
		$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
	}
	*/
}
//************* END SORT CHOICES *********************


// *** Search in 1 or more family trees ***
//$search_database='';
$search_database='tree_selected';
if (isset($_POST['search_database'])){
	$search_database=$_POST['search_database']; $_SESSION["save_search_database"]=$search_database;
}
if (isset($_GET["search_database"])){
	$search_database=$_GET['search_database']; $_SESSION["save_search_database"]=$search_database;
}
if(isset($humo_option['one_name_study']) AND $humo_option['one_name_study']=='y') {
	$search_database = "all_trees"; $_SESSION["save_search_database"]=$search_database;
}

$selection['pers_firstname']='';
if (isset($_POST['pers_firstname'])){
	$selection['pers_firstname']=$_POST['pers_firstname'];
	//$selection['pers_firstname']=htmlentities($_POST['pers_firstname'],ENT_QUOTES,'UTF-8');
}
// *** Used for frequent firstnames in statistics page ***
if (isset($_GET['pers_firstname'])){
	$selection['pers_firstname']=$_GET['pers_firstname'];
	$_GET['adv_search']='1';
}
$selection['part_firstname']='';
if (isset($_POST['part_firstname'])){
	$selection['part_firstname']=$_POST['part_firstname'];
}
if (isset($_GET['part_firstname'])){
	$selection['part_firstname']=$_GET['part_firstname'];
	$_SESSION["save_selection"]=$selection;
}

// *** Prefix (names list and most frequent names in main menu.) ***
$selection['pers_prefix']='';
if (isset($_POST['pers_prefix'])){ $selection['pers_prefix']=$_POST['pers_prefix']; }
if (isset($_GET['pers_prefix'])){
	$selection['pers_prefix']=$_GET['pers_prefix'];
	//$selection['pers_prefix']=htmlentities($_GET['pers_prefix'],ENT_QUOTES,'UTF-8');
}

// *** Lastname ***

$selection['pers_lastname']='';
if (isset($_POST['pers_lastname'])){
	$selection['pers_lastname']=$_POST['pers_lastname'];
	//$selection['pers_lastname']=htmlentities($_POST['pers_lastname'],ENT_QUOTES,'UTF-8');
	$_SESSION["save_selection"]=$selection;
}
if(isset($humo_option['one_name_study']) AND $humo_option['one_name_study']=='y') {
	if((isset($_GET['adv_search']) AND $_GET['adv_search']==1) OR (isset($_GET['index_list']) AND $_GET['index_list']=='search') OR (isset($_GET['reset']) AND $_GET['reset']==1)) {
		$selection['pers_lastname']= $humo_option['one_name_thename'];
	}
}
if (isset($_GET["pers_lastname"])){
	$selection['pers_lastname']=$_GET['pers_lastname'];
	//$selection['pers_lastname']=htmlentities($_GET['pers_lastname'],ENT_QUOTES,'UTF-8');
	$selection['pers_lastname']=str_replace("|", "&", $selection['pers_lastname']);  // Don't use a & character in a GET link
	$_SESSION["save_selection"]=$selection;
}

$selection['part_lastname']='';
if (isset($_POST['part_lastname'])){
	$selection['part_lastname']=$_POST['part_lastname'];
	$_SESSION["save_selection"]=$selection;
}
// *** Used for clicking in the names list ***
if (isset($_GET['part_lastname'])){
	$selection['part_lastname']=$_GET['part_lastname'];
	$_SESSION["save_selection"]=$selection;
}

// ***  ADVANCED SEARCH added by Yossi Beck, translated and integrated in person search screen by Huub. *** //
$selection['birth_place']=''; if (isset($_POST['birth_place'])){ $selection['birth_place']=$_POST['birth_place']; }
$selection['part_birth_place']=''; if (isset($_POST['part_birth_place'])){ $selection['part_birth_place']=$_POST['part_birth_place']; }

$selection['death_place']=''; if (isset($_POST['death_place'])){ $selection['death_place']=$_POST['death_place']; }
$selection['part_death_place']=''; if (isset($_POST['part_death_place'])){ $selection['part_death_place']=$_POST['part_death_place']; }

$selection['birth_year']=''; if (isset($_POST['birth_year'])){ $selection['birth_year']=$_POST['birth_year']; }
$selection['birth_year_end']=''; if (isset($_POST['birth_year_end'])){ $selection['birth_year_end']=$_POST['birth_year_end']; }

$selection['death_year']=''; if (isset($_POST['death_year'])){ $selection['death_year']=$_POST['death_year']; }
$selection['death_year_end']=''; if (isset($_POST['death_year_end'])){ $selection['death_year_end']=$_POST['death_year_end']; }

$selection['spouse_firstname']='';
if (isset($_POST['spouse_firstname'])){
	$selection['spouse_firstname']=$_POST['spouse_firstname'];
	//$selection['spouse_firstname']=htmlentities($_POST['spouse_firstname'],ENT_QUOTES,'UTF-8');
}

$selection['part_spouse_firstname']='';
if (isset($_POST['part_spouse_firstname'])){ $selection['part_spouse_firstname']=$_POST['part_spouse_firstname']; }

$selection['spouse_lastname']='';
if (isset($_POST['spouse_lastname'])){
	$selection['spouse_lastname']=$_POST['spouse_lastname'];
	//$selection['spouse_lastname']=htmlentities($_POST['spouse_lastname'],ENT_QUOTES,'UTF-8');
}
$selection['part_spouse_lastname']='';
if (isset($_POST['part_spouse_lastname'])){ $selection['part_spouse_lastname']=$_POST['part_spouse_lastname']; }

$selection['sexe']=''; if (isset($_POST['sexe'])){ $selection['sexe']=$_POST['sexe']; }
	elseif (isset($_GET['sexe'])){ $selection['sexe']=$_GET['sexe']; }
// *** Own Code ***
$selection['own_code']=''; if (isset($_POST['own_code'])){ $selection['own_code']=$_POST['own_code']; }
$selection['part_own_code']=''; if (isset($_POST['part_own_code'])){ $selection['part_own_code']=$_POST['part_own_code']; }

// *** Gedcomnumber ***
$selection['gednr']=''; if (isset($_POST['gednr'])){ $selection['gednr']=$_POST['gednr']; }
$selection['part_gednr']=''; if (isset($_POST['part_gednr'])){ $selection['part_gednr']=$_POST['part_gednr']; }

// *** Profession ***
$selection['pers_profession']=''; if (isset($_POST['pers_profession'])){ $selection['pers_profession']=$_POST['pers_profession']; }
$selection['part_profession']=''; if (isset($_POST['part_profession'])){ $selection['part_profession']=$_POST['part_profession']; }

// *** Text ***
$selection['text']=''; if (isset($_POST['text'])){ $selection['text']=$_POST['text']; }
$selection['part_text']=''; if (isset($_POST['part_text'])){ $selection['part_text']=$_POST['part_text']; }

// *** Place ***
$selection['pers_place']=''; if (isset($_POST['pers_place'])){ $selection['pers_place']=$_POST['pers_place']; }
$selection['part_place']=''; if (isset($_POST['part_place'])){ $selection['part_place']=$_POST['part_place']; }

// *** Zip code ***
$selection['zip_code']=''; if (isset($_POST['zip_code'])){ $selection['zip_code']=$_POST['zip_code']; }
$selection['part_zip_code']=''; if (isset($_POST['part_zip_code'])){ $selection['part_zip_code']=$_POST['part_zip_code']; }

// *** Research status ***
$selection['parent_status']=''; if (!isset($_POST['quicksearch']) AND isset($_POST['parent_status'])){ $selection['parent_status']=$_POST['parent_status']; }

// *** Witness ***
$selection['witness']=''; if (isset($_POST['witness'])){ $selection['witness']=$_POST['witness']; }
$selection['part_witness']='';
if (isset($_POST['part_witness'])){
	$selection['part_witness']=$_POST['part_witness'];

	// ******************************************************
	// *** THIS LAST LINE WILL SAVE ALL $selection VALUES ***
	// ******************************************************
	$_SESSION["save_selection"]=$selection;
}

// *** Store selection if search for most frequent firstnames is used from statistics page ***
if (isset($_GET['pers_firstname'])){
	$_SESSION["save_selection"]=$selection;
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
	$_SESSION["save_selection"]=$selection;
}
if (isset($_GET['adv_search'])){
	if ($_GET['adv_search']=='1'){
		$adv_search=true;
		//$quicksearch=''; $_SESSION["save_quicksearch"]=$quicksearch;
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
	if (isset($_SESSION["save_quicksearch"])) $quicksearch=$_SESSION["save_quicksearch"];
	if (isset($_SESSION["save_adv_search"])) $adv_search=$_SESSION["save_adv_search"];

	// *** Multiple search values ***
	if (isset($_SESSION["save_selection"])) $selection=$_SESSION["save_selection"]; 
}

// *** Search for places in birth-baptise-died places etc. ***
if ($index_list=='places'){
	if (isset($_SESSION["save_place_name"])) $place_name=$_SESSION["save_place_name"];
	if (isset($_SESSION["save_part_place_name"])) $part_place_name=$_SESSION["save_part_place_name"];

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
	$text="LIKE '%".safe_text_db($search_name)."%'"; // *** Default value: "contains" ***
	if ($search_part=='equals'){ $text="='".safe_text_db($search_name)."'"; }
	if ($search_part=='starts_with'){ $text="LIKE '".safe_text_db($search_name)."%'"; }
	return $text;
}

// *******************
// *** BUILD QUERY ***
// *******************

$query='';
$count_qry='';

//*** Results of searchform in mainmenu ***
//*** Or: search in lastnames ***

if ($selection['pers_firstname'] OR $selection['pers_prefix'] OR $selection['pers_lastname'] OR $selection['birth_place'] OR $selection['death_place']
	OR $selection['birth_year'] OR $selection['death_year'] OR ($selection['sexe'] AND $selection['sexe']!='both')
	OR $selection['own_code'] OR $selection['gednr'] OR $selection['pers_profession'] OR $selection['pers_place'] OR $selection['text']
	OR $selection['zip_code'] OR $selection['witness'] OR $selection['parent_status']!=""){

	// *** Build query ***
	//$and=" ";
	$and=" AND ";

	$add_address_qry=false;
	$add_event_qry=false;

	if ($selection['pers_lastname']) {
		if ($selection['pers_lastname']==__('...')){
			$query.=$and." pers_lastname=''"; $and=" AND ";
		}
		elseif ($user['group_kindindex']=="j"){
			$query.=$and." CONCAT( REPLACE(pers_prefix,'_',' ') ,pers_lastname) ".
			name_qry($selection['pers_lastname'], $selection['part_lastname']); $and=" AND ";
		}
		else {
			$query.=$and." pers_lastname ".name_qry($selection['pers_lastname'], $selection['part_lastname']); $and=" AND ";
		}
	}
	// *** Namelist: search persons without pers_prefix ***
	if ($selection['pers_prefix']=='EMPTY'){
		$query.=$and."pers_prefix=''"; $and=" AND ";
	}
	elseif ($selection['pers_prefix']){
		//$query.=$and."pers_prefix='".$selection['pers_prefix']."'"; $and=" AND ";
		//$query.=$and."pers_prefix='".safe_text_db( str_replace(' ', '_', $selection['pers_prefix']) )."'"; $and=" AND ";

		// *** Search results for: "van", "van " and "van_" ***
		$pers_prefix=safe_text_db( str_replace(' ', '_', $selection['pers_prefix']));
		//$query.=$and."(pers_prefix='".$pers_prefix."' OR SUBSTRING(pers_prefix,-1) ='".$pers_prefix."')"; $and=" AND ";
		$query.=$and."(pers_prefix='".$pers_prefix."' OR pers_prefix ='".$pers_prefix.'_'."')"; $and=" AND ";
	}

	if ($selection['pers_firstname']){
		$query.=$and."(pers_firstname ".name_qry($selection['pers_firstname'], $selection['part_firstname']);

		//$query.=" OR event_event ".name_qry($selection['pers_firstname'], $selection['part_firstname']).')';
		//$query.=" OR (event_kind='name' AND event_event ".name_qry($selection['pers_firstname'], $selection['part_firstname']).') )';
		//$query.=" OR (event_event ".name_qry($selection['pers_firstname'], $selection['part_firstname']).') )';
		$query.=" OR (event_kind='name' AND event_event ".name_qry($selection['pers_firstname'], $selection['part_firstname']).') )';

		$and=" AND ";
		$add_event_qry=true;
	}

	// *** Search for born AND baptised place ***
	if ($selection['birth_place']){
		//$query.=$and."pers_birth_place ".name_qry($selection['birth_place'], $selection['part_birth_place']); $and=" AND ";
		$query.=$and."(pers_birth_place ".name_qry($selection['birth_place'], $selection['part_birth_place']); $and=" AND ";
		$query.=" OR pers_bapt_place ".name_qry($selection['birth_place'], $selection['part_birth_place']).')'; $and=" AND ";
	}

	// *** Search for death AND buried place ***
	if ($selection['death_place']){
		//$query.=$and."pers_death_place ".name_qry($selection['death_place'], $selection['part_death_place']); $and=" AND ";
		$query.=$and."(pers_death_place ".name_qry($selection['death_place'], $selection['part_death_place']); $and=" AND ";
		$query.=" OR pers_buried_place ".name_qry($selection['death_place'], $selection['part_death_place']).')'; $and=" AND ";
	}

	if ($selection['birth_year']){
		if (!$selection['birth_year_end']){   // filled in one year: exact date
			//$query.=$and."pers_birth_date LIKE '%".safe_text_db($selection['birth_year'])."%'"; $and=" AND ";

			// *** Also search for baptise ***
			$query.=$and."(pers_birth_date LIKE '%".safe_text_db($selection['birth_year'])."%'"; $and=" AND ";
			$query.=" OR pers_bapt_date LIKE '%".safe_text_db($selection['birth_year'])."%')"; $and=" AND ";
		} else{
			//$query.=$and."RIGHT(pers_birth_date, 4)>='".safe_text_db($selection['birth_year'])."' AND RIGHT(pers_birth_date, 4)<='".safe_text_db($selection['birth_year_end'])."'"; $and=" AND ";

			// *** Also search for baptise ***
			$query.=$and."(RIGHT(pers_birth_date, 4)>='".safe_text_db($selection['birth_year'])."' AND RIGHT(pers_birth_date, 4)<='".safe_text_db($selection['birth_year_end'])."'"; $and=" AND ";
			$query.=" OR RIGHT(pers_bapt_date, 4)>='".safe_text_db($selection['birth_year'])."' AND RIGHT(pers_bapt_date, 4)<='".safe_text_db($selection['birth_year_end'])."')"; $and=" AND ";
		}
	}

	if ($selection['death_year']){
		if (!$selection['death_year_end']){      // filled in one year: exact date
			//$query.=$and."pers_death_date LIKE '%".safe_text_db($selection['death_year'])."%'"; $and=" AND ";

			// ** Also search for buried date ***
			$query.=$and."(pers_death_date LIKE '%".safe_text_db($selection['death_year'])."%'"; $and=" AND ";
			$query.="OR pers_buried_date LIKE '%".safe_text_db($selection['death_year'])."%')"; $and=" AND ";
		} else {
			//$query.=$and."RIGHT(pers_death_date, 4)>='".safe_text_db($selection['death_year'])."' AND RIGHT(pers_death_date, 4)<='".safe_text_db($selection['death_year_end'])."'"; $and=" AND ";

			// ** Also search for buried date ***
			$query.=$and."(RIGHT(pers_death_date, 4)>='".safe_text_db($selection['death_year'])."' AND RIGHT(pers_death_date, 4)<='".safe_text_db($selection['death_year_end'])."'"; $and=" AND ";
			$query.=" OR RIGHT(pers_buried_date, 4)>='".safe_text_db($selection['death_year'])."' AND RIGHT(pers_buried_date, 4)<='".safe_text_db($selection['death_year_end'])."')"; $and=" AND ";
		}
	}

	if ($selection['sexe']=="M" OR $selection['sexe']=="F"){
		$query.=$and."pers_sexe='".$selection['sexe']."'"; $and=" AND ";
	}
	if ($selection['sexe']=="Unknown"){
		$query.=$and."(pers_sexe!='M' AND pers_sexe!='F')"; $and=" AND ";
	}

	if ($selection['own_code']){
		$query.=$and."pers_own_code ".name_qry($selection['own_code'], $selection['part_own_code']); $and=" AND ";
	}
	if ($selection['gednr']){
		if(strtoupper(substr($_POST['gednr'],0,1)) != 'I'){
			$selection['gednr']='I'.$_POST['gednr']; // if only number was entered - add "I" before
		}
		else{
			$selection['gednr']=strtoupper($_POST['gednr']); // in case lowercase "i" was entered before number, make it "I"
		}
		$query.=$and."pers_gedcomnumber ".name_qry($selection['gednr'], $selection['part_gednr']); $and=" AND ";
	}

	if ($selection['pers_profession']){
		$query.=$and." (event_kind='profession' AND event_event ".name_qry($selection['pers_profession'], $selection['part_profession']).')';
		$and=" AND ";
		$add_event_qry=true;
	}

	if ($selection['text']){
		$query.=$and."pers_text ".name_qry($selection['text'], $selection['part_text']); $and=" AND ";
	}

	if ($selection['pers_place']){
		$query.=$and." address_place ".name_qry($selection['pers_place'], $selection['part_place']);
		$and=" AND ";
		$add_address_qry=true;
	}

	if ($selection['zip_code']){
		$query.=$and." address_zip ".name_qry($selection['zip_code'], $selection['part_zip_code']);
		$and=" AND ";
		$add_address_qry=true;
	}

	if ($selection['witness']){
		$query.=$and." ( RIGHT(event_kind,7)='witness' AND event_event ".name_qry($selection['witness'], $selection['part_witness']).')';
		$and=" AND ";
		$add_event_qry=true;
	}

	if ($selection['parent_status'] AND $selection['parent_status']=="noparents"){
		$query.=$and." (pers_famc = '') ";
		$and=" AND ";
		$add_event_qry=true;
	}
	
	// *** Change query if searched for spouse ***
	if ($selection['spouse_firstname'] OR $selection['spouse_lastname']) {
		$query.=$and."pers_fams!=''"; $and=" AND ";
	}


	// *** Build SELECT part of query. Search with option "ALL family trees" or "All but selected" ***
	if ($search_database=='all_trees' OR $search_database=='all_but_this') {
		$query_part=$query;

		$counter=0;
		$multi_tree='';
		foreach($dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order") AS $datapdo) {
			if($search_database=="all_but_this" AND $datapdo['tree_prefix']==safe_text_db($_SESSION['tree_prefix'])) {
				continue;
			}

			// *** Check is family tree is shown or hidden for user group ***
			$hide_tree_array=explode(";",$user['group_hide_trees']);
			$hide_tree=false;
			//for ($x=0; $x<=count($hide_tree_array)-1; $x++){
			//	if ($hide_tree_array[$x]==$datapdo['tree_id']){ $hide_tree=true; }
			//}
			if (in_array($datapdo['tree_id'], $hide_tree_array)) $hide_tree=true;
			if ($hide_tree==false){
				if ($counter > 0) $multi_tree.=' OR ';
				$multi_tree.='pers_tree_id='.$datapdo['tree_id'];
				$counter++;
			}

		}
	}
	else{
		// *** Start building query, search in 1 database ***
		$multi_tree=" pers_tree_id='".$tree_id."'";
	}


	// *** Build query, only add events and addresses tables if necessary ***
	/*
	$query_select = "SELECT SQL_CALC_FOUND_ROWS humo_persons.*";
	if ($add_event_qry)
		$query_select .= ", event_kind, event_event";
	if ($add_address_qry)
		$query_select .= ", address_place, address_zip";

	if ($user['group_kindindex']=="j"){
		// *** Change ordering of index, using concat name ***
		$query_select .= ", CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ";
	}
	$query_select .= $make_date." FROM humo_persons";

	if ($add_event_qry)
		$query_select .= " LEFT JOIN humo_events ON event_tree_id=pers_tree_id AND event_connect_id=pers_gedcomnumber";
	if ($add_address_qry)
		$query_select .= " LEFT JOIN humo_connections ON connect_tree_id=pers_tree_id AND connect_connect_id=pers_gedcomnumber
			AND connect_sub_kind='person_address'
			LEFT JOIN humo_addresses ON address_connect_id=pers_gedcomnumber AND address_connect_sub_kind='person' AND address_tree_id=pers_tree_id
			OR address_gedcomnr=connect_item_id AND address_tree_id=connect_tree_id AND connect_connect_id=pers_gedcomnumber";

	$query_select.=" WHERE (".$multi_tree.") ".$query;
	//$query_select.=" GROUP BY pers_gedcomnumber";
	$query_select.=" GROUP BY pers_id";
	$query_select.=" ORDER BY ".$orderby;
	$query=$query_select;
	*/

	// *** Build query, only add events and addresses tables if necessary ***
	// *** Aug. 2017: renewed querie because of > MySQL 5.7 ***
	$query_select="SELECT SQL_CALC_FOUND_ROWS humo_persons2.*, humo_persons1.pers_id";

	if ($add_event_qry)
		$query_select .= ", event_event, event_kind";
	if ($add_address_qry)
		$query_select .= ", address_place, address_zip";

	if ($user['group_kindindex']=="j"){
		// *** Change ordering of index, using concat name ***
		$query_select .= ", CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ";
	}

	$query_select .= $make_date."
	FROM humo_persons as humo_persons2
	RIGHT JOIN
	(
		SELECT pers_id";
		if ($add_event_qry) $query_select .= ", event_event, event_kind";
		if ($add_address_qry) $query_select .= ", address_place, address_zip";
		$query_select .= " FROM humo_persons";

		if ($add_event_qry)
			//$query_select .= " LEFT JOIN humo_events
			//ON event_tree_id=pers_tree_id
			//AND event_connect_id=pers_gedcomnumber
			// *** If event_kind='name' is used, search for name will work, but other events are hidden! ***
			//AND event_kind='name'";
			$query_select .= " LEFT JOIN humo_events
			ON event_tree_id=pers_tree_id
			AND event_connect_id=pers_gedcomnumber";
		if ($add_address_qry)
			$query_select .= " LEFT JOIN humo_connections
			ON connect_tree_id=pers_tree_id
			AND connect_connect_id=pers_gedcomnumber
			AND connect_sub_kind='person_address'
			LEFT JOIN humo_addresses
			ON address_connect_id=pers_gedcomnumber
			AND address_connect_sub_kind='person'
			AND address_tree_id=pers_tree_id
			OR address_gedcomnr=connect_item_id
			AND address_tree_id=connect_tree_id
			AND connect_connect_id=pers_gedcomnumber";

		$query_select.=" WHERE (".$multi_tree.") ".$query." GROUP BY pers_id";
		// *** This line IS DISABLED because it will give multiple lines for one person if there are multiple events. ***
		//if ($add_event_qry) $query_select .= ", event_event, event_kind";
		// *** This line IS DISABLED because it will give multiple lines for one person if there are multiple events. ***
		//if ($add_address_qry) $query_select .= ", address_place, address_zip";

	$query_select .= "
	) as humo_persons1
	ON humo_persons1.pers_id = humo_persons2.pers_id

	ORDER BY ".$orderby;
	$query=$query_select;
}

// *** Menu quicksearch ***
if ($index_list=='quicksearch'){
	// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
	$quicksearch=str_replace(' ', '%', $quicksearch);
	if($humo_option['one_name_study']=='y') { $quicksearch .= '%'.$humo_option['one_name_thename']; }
	// *** In case someone entered "Mons, Huub" using a comma ***
	$quicksearch = str_replace(',','',$quicksearch);

	// One can enter "Huub Mons", "Mons Huub", "Huub van Mons", "van Mons, Huub", "Mons, Huub van" and even "Mons van, Huub"

	// *** Build SELECT part of query. Search in ALL family trees ***
	if ($search_database=='all_trees' OR $search_database=='all_but_this') {
		$query='';
		$counter=0;
		$multi_tree='';
		foreach($dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order") as $pdoresult) {
			if($search_database=="all_but_this" AND $pdoresult['tree_prefix']==safe_text_db($_SESSION['tree_prefix'])) {
				continue;
			}
			// *** Check if family tree is shown or hidden for user group ***
			$hide_tree_array=explode(";",$user['group_hide_trees']);
			$hide_tree=false;
			if (in_array($pdoresult['tree_id'], $hide_tree_array)) $hide_tree=true;
			if ($hide_tree==false){
				if ($counter > 0) $multi_tree.=' OR ';
				$multi_tree.='pers_tree_id='.$pdoresult['tree_id'];
				$counter++;
			}
		}
	}
	else{
		// *** Start building query, search in 1 database ***
		$multi_tree="pers_tree_id='".$tree_id."'";
	}

	/*	******************************************
		*** QUICKSEARCH QUERY ***
		Aug 2017: changed for MySQL > 5.7.
		Feb 2016: added search for patronym
		******************************************
	*/

	/*
	$query.="SELECT SQL_CALC_FOUND_ROWS *,
	CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name
	".$make_date."
	FROM humo_persons
	LEFT JOIN humo_events ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id
	WHERE (".$multi_tree.")
		AND 
		( CONCAT(pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%".safe_text_db($quicksearch)."%'
		OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname,pers_callname) LIKE '%".safe_text_db($quicksearch)."%' 
		OR CONCAT(pers_patronym,pers_lastname,pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text_db($quicksearch)."%' 
		OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname,pers_callname) LIKE '%".safe_text_db($quicksearch)."%'
		OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".safe_text_db($quicksearch)."%'
		OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%".safe_text_db($quicksearch)."%' 
		OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text_db($quicksearch)."%' 
		OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%".safe_text_db($quicksearch)."%'
		)
	GROUP BY pers_id";
	*/

	// *** TEST MYSQL 5.7. If result is found in humo_events, now the extra text is missing... ***
	/*
	$query.="
	SELECT SQL_CALC_FOUND_ROWS CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, humo_persons2.*, humo_persons1.pers_id
	".$make_date."
	FROM humo_persons as humo_persons2
	RIGHT JOIN 
	(
		SELECT pers_id
		FROM humo_persons
		LEFT JOIN humo_events ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id
		WHERE (".$multi_tree.")
			AND 
			( CONCAT(pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%".safe_text_db($quicksearch)."%'
			OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname,pers_callname) LIKE '%".safe_text_db($quicksearch)."%' 
			OR CONCAT(pers_patronym,pers_lastname,pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text_db($quicksearch)."%' 
			OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname,pers_callname) LIKE '%".safe_text_db($quicksearch)."%'
			OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".safe_text_db($quicksearch)."%'
			OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%".safe_text_db($quicksearch)."%' 
			OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text_db($quicksearch)."%' 
			OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%".safe_text_db($quicksearch)."%'
			)
		GROUP BY pers_id
	) as humo_persons1
	ON humo_persons1.pers_id = humo_persons2.pers_id
	";
	*/
	
	$query.="
	SELECT SQL_CALC_FOUND_ROWS CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, humo_persons2.*, humo_persons1.pers_id, event_event, event_kind
	".$make_date."
	FROM humo_persons as humo_persons2
	RIGHT JOIN 
	(
		SELECT pers_id, event_event, event_kind
		FROM humo_persons
		LEFT JOIN humo_events ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id
		WHERE (".$multi_tree.")
			AND 
			( CONCAT(pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%".safe_text_db($quicksearch)."%'
			OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname,pers_callname) LIKE '%".safe_text_db($quicksearch)."%' 
			OR CONCAT(pers_patronym,pers_lastname,pers_firstname,pers_callname,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text_db($quicksearch)."%' 
			OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname,pers_callname) LIKE '%".safe_text_db($quicksearch)."%'
			OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".safe_text_db($quicksearch)."%'
			OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%".safe_text_db($quicksearch)."%' 
			OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text_db($quicksearch)."%' 
			OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%".safe_text_db($quicksearch)."%'
			)
		GROUP BY pers_id, event_event, event_kind
	) as humo_persons1
	ON humo_persons1.pers_id = humo_persons2.pers_id
	";

	$query.=" ORDER BY ".$orderby;
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
				FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		else{
			$query = "(SELECT SQL_CALC_FOUND_ROWS *, pers_birth_place as place_order 
				FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}

		if($place_name) {
			$query.= " AND pers_birth_place ".name_qry($place_name,$part_place_name);
		}
		else {
			$query.= " AND pers_birth_place LIKE '_%'";
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
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		else{
			$query.= "(SELECT ".$calc."*, pers_bapt_place as place_order FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		if ($place_name) {
			$query.= " AND pers_bapt_place ".name_qry($place_name,$part_place_name);
		}
		else {
			$query.= " AND pers_bapt_place LIKE '_%'";
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
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		else{
			$query.= "(SELECT ".$calc."*, pers_place_index as place_order 
				FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		if($place_name) {
			$query.= " AND pers_place_index ".name_qry($place_name,$part_place_name);
		}
		else {
			$query .= " AND pers_place_index LIKE '_%'";
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
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		else{
			$query.= "(SELECT ".$calc."*, pers_death_place as place_order
				FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		if($place_name) {
			$query.= " AND pers_death_place ".name_qry($place_name,$part_place_name);
		}
		else {
			$query.= " AND pers_death_place LIKE '_%'";
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
			FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		else{
			$query.= "(SELECT ".$calc."*, pers_buried_place as place_order
				FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
		if($place_name) {
			$query.= " AND pers_buried_place ".name_qry($place_name,$part_place_name);
		}
		else {
			$query.= " AND pers_buried_place LIKE '_%'";
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
	$query = "SELECT SQL_CALC_FOUND_ROWS * ".$make_date." FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_patronym LIKE '_%' AND pers_lastname='' ORDER BY ".$orderby;
}

// **************************
// *** Generate indexlist ***
// **************************

	// *** Standard index ***
	if ($query=='' OR $index_list=='standard'){
		$query = "SELECT * ".$make_date." FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY ".$orderby;
		$count_qry = "SELECT COUNT(*) as teller ".$make_date." FROM humo_persons WHERE pers_tree_id='".$tree_id."'";

		// Mons, van or: van Mons
		if ($user['group_kindindex']=="j"){
			$query= "SELECT *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ".$make_date."
				FROM humo_persons WHERE pers_tree_id='".$tree_id."' ORDER BY ".$orderby;
			$count_qry = "SELECT COUNT(*) as teller ".$make_date."
				FROM humo_persons WHERE pers_tree_id='".$tree_id."'";
		}
	}

	include_once(CMS_ROOTPATH."menu.php");

// *** DEBUG/ TEST: SHOW QUERY ***
//echo $query.'<br>';

	//*** Show number of persons and pages *****************************************
	$item=0; if (isset($_GET['item'])){ $item=$_GET['item']; }
	$start=0; if (isset($_GET["start"])){ $start=$_GET["start"]; }
	$nr_persons=$humo_option['show_persons'];

	if(!$selection['spouse_firstname'] AND !$selection['spouse_lastname'] AND $selection['parent_status']!="motheronly" AND $selection['parent_status']!="fatheronly") {
	
		$person_result = $dbh->query($query." LIMIT ".$item.",".$nr_persons);
 
		if ($count_qry){  
			// *** Use MySQL COUNT command to calculate nr. of persons in simple queries (faster than php num_rows and in simple queries faster than SQL_CAL_FOUND_ROWS) ***
			$result= $dbh->query($count_qry);
			@$resultDb = $result->fetch(PDO::FETCH_OBJ);
			$count_persons=@$resultDb->teller; 
		}
		else{  
			// *** USE SQL_CALC_FOUND_ROWS for complex queries (faster than mysql count) ***
			$result = $dbh->query("SELECT FOUND_ROWS() AS 'found_rows'");
			$rows = $result->fetch();
			$count_persons = $rows['found_rows'];   
		}
	}
	else{
		$person_result= $dbh->query($query);
		$count_persons=0; // Isn't used if search is done for spouse or for people with only known mother or only known father...
	}

	// *** Show error message if search in multiple trees is going wrong (nr of fields is different in some tables) ***
	// $person_result2=mysql_query($query,$db) or die("FAULT : " . mysql_error());
	// $person_result=mysql_query($query." LIMIT ".safe_text_db($item).",".$nr_persons,$db) or die("FAULT : " . mysql_error());

	if (CMS_SPECIFIC=='Joomla'){
		$list_var  = 'index.php?option=com_humo-gen&amp;task=list';  // for use without query string
		$list_var2 = 'index.php?option=com_humo-gen&amp;task=list&amp;'; // for use with query string
	}
	else {
		$list_var  = CMS_ROOTPATH.'list.php';
		$list_var2 = CMS_ROOTPATH.'list.php?';
	}

	if ($index_list=='places'){
		echo '<table align="center" class="humo index_table">';
		echo '<tr><td>';

			//************** search places **************************************
			//echo ' <form method="post" action="'.$list_var.'" style="display : inline;">';
			echo ' <form method="post" action="'.$list_var.'">';
				echo __('Find place').':<br><br>';

				$checked=''; if ($select_birth=='1'){$checked='checked';}
				echo '<span class="select_box"><input type="Checkbox" name="select_birth" value="1" '.$checked.'> '.__('*').' '.__('birth pl.').'</span>';

				$checked=''; if ($select_bapt=='1'){$checked='checked';}
				echo '<span class="select_box"><input type="Checkbox" name="select_bapt" value="1" '.$checked.'> '.__('~').' '.__('bapt pl.').'</span>';

				$checked=''; if ($select_place=='1'){$checked='checked';}
				echo '<span class="select_box"><input type="Checkbox" name="select_place" value="1" '.$checked.'> '.__('^').' '.__('residence').'</span>';

				$checked=''; if ($select_death=='1'){$checked='checked';}
				echo '<span class="select_box"><input type="Checkbox" name="select_death" value="1" '.$checked.'> '.__('&#134;').' '.__('death pl.').'</span>';

				$checked=''; if ($select_buried=='1'){$checked='checked';}
				echo '<input type="Checkbox" name="select_buried" value="1" '.$checked.'> '.__('[]').' '.__('bur pl.');

				echo '<br><br><select name="part_place_name">';
				echo '<option value="contains">'.__('Contains').'</option>';

				$select_item=''; if ($part_place_name=='equals'){ $select_item=' selected'; }
				echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';

				$select_item=''; if ($part_place_name=='starts_with'){ $select_item=' selected'; }
				echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
				echo '</select>';

				echo ' <input type="text" name="place_name" value="'.safe_text_show($place_name).'" size="15">';
				echo '<input type="hidden" name="index_list" value="'.$index_list.'">';
				echo ' <input type="submit" value="'.__('Search').'" name="B1">';
			echo '</form>';

		echo '</td></tr></table>';

		//***************** end search of places **********************************
	}

	// *** Search fields ***
	if ($index_list=='standard' OR $index_list=='search' OR $index_list=='quicksearch'){

		// *** STANDARD SEARCH BOX ***
		echo '<form method="post" action="'.$list_var.'" style="display : inline;">';

		echo '<table align="center" class="humo index_table">';

		echo '<tr>';

		// *** ADVANCED SEARCH BOX ***
		if ($adv_search==true){

			//echo '<td align="right" class="no_border" >'.__('First name').':';
			echo '<td class="no_border" >'.__('First name').':<br>';
			echo ' <select size="1" name="part_firstname">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_firstname']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_firstname']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="pers_firstname" value="'.safe_text_show($selection['pers_firstname']).'" size="15" placeholder="'.__('First name').'"></td>';
			if($humo_option['one_name_study']!='y') {
				//echo '<td align="right" class="no_border">'.__('Last name').':';
				echo '<td class="no_border">'.__('Last name').':<br>';
				// *** Lastname prefix ***
				$pers_prefix=$selection['pers_prefix']; if ($pers_prefix=='EMPTY') $pers_prefix='';
				echo ' <input type="text" name="pers_prefix" value="'.safe_text_show($pers_prefix).'" size="8" placeholder="'.ucfirst(__('prefix')).'">';
				// *** Lastname ***
				echo ' <select size="1" name="part_lastname">';
				echo '<option value="contains">'.__('Contains').'</option>';
				$select_item=''; if ($selection['part_lastname']=='equals'){ $select_item=' selected'; }
				echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
				$select_item=''; if ($selection['part_lastname']=='starts_with'){ $select_item=' selected'; }
				echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
				echo '</select>';
				echo ' <input type="text" name="pers_lastname" value="'.safe_text_show($selection['pers_lastname']).'" size="15" placeholder="'.__('Last name').'"></td>';
			}
			else {
				echo '<td align="center" class="no_border">'.__('Last name').':';
				echo '<span style="text-align:center; font-weight:bold">'.$humo_option['one_name_thename'].'</span>';
				echo '<input type="hidden" name="pers_lastname" value="'.$humo_option['one_name_thename'].'">';
				echo '<input type="hidden" name="part_lastname" value="equals">';
			}
			// *** Profession ***
			//echo '<td align="right" class="no_border">'.__('Profession').':';
			echo '<td class="no_border">'.__('Profession').':<br>';
			echo ' <select size="1" name="part_profession">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_profession']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_profession']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="pers_profession" value="'.safe_text_show($selection['pers_profession']).'" size="15" placeholder="'.__('Profession').'"></td>';

			echo '</tr>';

		}
		else{
			if($humo_option['one_name_study']!='y') {
				echo '<td class="no_border center" colspan="2">'.__('Enter name or part of name').'<br>';
				echo '<span style="font-size:12px;">'.__('"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"').'</span>';
			}
			else {
				echo '<td class="no_border center" colspan="2">'.__('Enter private name');
			}
			echo '<input type="hidden" name="index_list" value="quicksearch">';
			$quicksearch='';
			if (isset($_POST['quicksearch'])){
				//$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
				$quicksearch=safe_text_show($_POST['quicksearch']);
				$_SESSION["save_quicksearch"]=$quicksearch;
			}
			if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
			if($humo_option['min_search_chars']==1) { $pattern=""; $min_chars =" 1 ";}
			else { $pattern='pattern=".{'.$humo_option['min_search_chars'].',}"'; $min_chars = " ".$humo_option['min_search_chars']." ";}
			echo '<p><input type="text" name="quicksearch" value="'.$quicksearch.'" size="30" '.$pattern.' title="'.__('Minimum:').$min_chars.__('characters').'"></p></td>';
		}

		// *** ADVANCED SEARCH BOX ***
		if ($adv_search==true){
			//echo '<tr><td align="right" class="no_border">'.__('Year (or period) of birth:');
			//echo '<tr><td align="right" class="no_border">'.ucfirst(__('born')).'/ '.ucfirst(__('baptised')).':';
			echo '<tr><td class="no_border">'.ucfirst(__('born')).'/ '.ucfirst(__('baptised')).':<br>';
			echo ' <input type="text" name="birth_year" value="'.safe_text_show($selection['birth_year']).'" size="4" placeholder="'.__('Date').'">';
			echo '&nbsp;&nbsp;('.__('till:').'&nbsp;';
			echo '<input type="text" name="birth_year_end" value="'.safe_text_show($selection['birth_year_end']).'" size="4" placeholder="'.__('Date').'">&nbsp;)</td>';

			//echo '<td align="right" class="no_border">'.__('Place of birth').':';
			//echo '<td align="right" class="no_border">'.ucfirst(__('born')).'/ '.ucfirst(__('baptised')).':';
			echo '<td class="no_border">'.ucfirst(__('born')).'/ '.ucfirst(__('baptised')).':<br>';
			echo ' <select size="1" name="part_birth_place">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_birth_place']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_birth_place']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="birth_place" value="'.safe_text_show($selection['birth_place']).'" size="15" placeholder="'.__('Place').'"></td>';

			//echo '<td align="right" class="no_border">'.__('Own code').':';
			echo '<td class="no_border">'.__('Own code').':<br>';
			echo ' <select size="1" name="part_own_code">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_own_code']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_own_code']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="own_code" value="'.safe_text_show($selection['own_code']).'" size="15" placeholder="'.__('Own code').'">';
			echo '</td>';

			echo '</tr>';
			//echo '<tr><td align="right" class="no_border">'.__('Year (or period) of death:');
			//echo '<tr><td align="right" class="no_border">'.ucfirst(__('died')).'/ '.ucfirst(__('buried')).':';
			echo '<tr><td class="no_border">'.ucfirst(__('died')).'/ '.ucfirst(__('buried')).':<br>';
			echo ' <input type="text" name="death_year" value="'.safe_text_show($selection['death_year']).'" size="4" placeholder="'.__('Date').'">';
			echo '&nbsp;&nbsp;('.__('till:').'&nbsp;';
			echo '<input type="text" name="death_year_end" value="'.safe_text_show($selection['death_year_end']).'" size="4" placeholder="'.__('Date').'">&nbsp;)</td>';

			//echo '<td align="right" class="no_border">'.__('Place of death').':';
			//echo '<td align="right" class="no_border">'.ucfirst(__('died')).'/ '.ucfirst(__('buried')).':';
			echo '<td class="no_border">'.ucfirst(__('died')).'/ '.ucfirst(__('buried')).':<br>';
			echo ' <select size="1" name="part_death_place">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_death_place']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_death_place']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="death_place" value="'.safe_text_show($selection['death_place']).'" size="15" placeholder="'.__('Place').'"></td>';

			// *** Text ***
			//echo '<td align="right" class="no_border">'.__('Text').':';
			echo '<td class="no_border">'.__('Text').':<br>';
			echo ' <select size="1" name="part_text">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_text']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_text']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="text" value="'.safe_text_show($selection['text']).'" size="15" placeholder="'.__('Text by person').'">';
			echo '</td>';

			echo '</tr>';

			//echo '<tr><td align="right" class="no_border">'.__('Choose sex:');
			echo '<tr><td class="no_border">'.__('Choose sex:').'<br>';
			$check=''; if ($selection['sexe']=='both'){ $check=' checked'; }
			echo '<input type="radio" name="sexe" value="both"'.$check.'>'.__('All').'&nbsp;&nbsp;';
			$check=''; if ($selection['sexe']=='M'){ $check=' checked'; }
			echo '<input type="radio" name="sexe" value="M"'.$check.'>'.__('Male').'&nbsp;&nbsp;';
			$check=''; if ($selection['sexe']=='F'){ $check=' checked'; }
			echo '<input type="radio" name="sexe" value="F"'.$check.'>'.__('Female').'&nbsp;&nbsp;';
			$check=''; if ($selection['sexe']=='Unknown'){ $check=' checked'; }
			echo '<input type="radio" name="sexe" value="Unknown"'.$check.'>'.__('Unknown');
			echo '</td>';

			// *** Living place ***
			//echo '<td align="right" class="no_border">'.__('Place').':';
			echo '<td class="no_border">'.__('Place').':<br>';
			echo ' <select size="1" name="part_place">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_place']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_place']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="pers_place" value="'.safe_text_show($selection['pers_place']).'" size="15" placeholder="'.__('Place').'"></td>';

			// *** Zip code ***
			//echo '<td align="right" class="no_border">'.__('Zip code').':';
			echo '<td class="no_border">'.__('Zip code').':<br>';
			echo ' <select size="1" name="part_zip_code">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_zip_code']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_zip_code']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="zip_code" value="'.safe_text_show($selection['zip_code']).'" size="15" placeholder="'.__('Zip code').'">';
			echo '</td>';

			echo '</tr>';

			//echo '<tr><td align="right" class="no_border">'.__('Partner firstname').':';
			echo '<tr><td class="no_border">'.__('Partner firstname').':<br>';
			echo ' <select size="1" name="part_spouse_firstname">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_spouse_firstname']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_spouse_firstname']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="spouse_firstname" value="'.safe_text_show($selection['spouse_firstname']).'" size="15" placeholder="'.__('First name').'"></td>';

			//echo '<td align="right" class="no_border">'.__('Partner lastname').':';
			echo '<td class="no_border">'.__('Partner lastname').':<br>';
			echo ' <select size="1" name="part_spouse_lastname">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_spouse_lastname']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_spouse_lastname']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="spouse_lastname" value="'.safe_text_show($selection['spouse_lastname']).'" size="15" placeholder="'.__('Last name').'"></td>';

			// *** Witness ***
			//echo '<td align="right" class="no_border">'.ucfirst(__('witness')).':';
			echo '<td class="no_border">'.ucfirst(__('witness')).':<br>';
			echo ' <select size="1" name="part_witness">';
			echo '<option value="contains">'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_witness']=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_witness']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="witness" value="'.safe_text_show($selection['witness']).'" size="15" placeholder="'.ucfirst(__('witness')).'">';
			echo '</td>';
			echo '</tr>';

			echo '<tr>';
			//echo '<td align="right" class="no_border">'.ucfirst(__('gedcomnumber (ID)')).':';
			echo '<td class="no_border">'.ucfirst(__('gedcomnumber (ID)')).':<br>';
			echo ' <select size="1" name="part_gednr">';
			echo '<option value="equals">'.__('Equals').'</option>';
			$select_item=''; if ($selection['part_gednr']=='contains'){ $select_item=' selected'; }
			echo '<option value="contains"'.$select_item.'>'.__('Contains').'</option>';
			$select_item=''; if ($selection['part_gednr']=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';
			echo ' <input type="text" name="gednr" value="'.safe_text_show($selection['gednr']).'" size="15" placeholder="'.ucfirst(__('gedcomnumber (ID)')).'">';

			//~~~~~~~~~~~~~~~~~~~
			//echo '</td><td colspan="2" align="center" class="no_border">'.__('Research status:');
			echo '</td><td colspan="2" align="center" class="no_border">'.__('Research status:').'<br>';
			$check=''; if ($selection['parent_status']=='noparents'){ $check=' checked'; }
			echo '<input type="radio" name="parent_status" value="noparents"'.$check.'>'.__('parents unknown').'&nbsp;&nbsp;';
			$check=''; if ($selection['parent_status']=='motheronly'){ $check=' checked'; }
			echo '<input type="radio" name="parent_status" value="motheronly"'.$check.'>'.__('father unknown').'&nbsp;&nbsp;';
			$check=''; if ($selection['parent_status']=='fatheronly'){ $check=' checked'; }
			echo '<input type="radio" name="parent_status" value="fatheronly"'.$check.'>'.__('mother unknown').'&nbsp;&nbsp;';
			//$check=''; if ($selection['parent_status']=='bothparents'){ $check=' checked'; }
			//echo '<input type="radio" name="parent_status" value="bothparents"'.$check.'>'.__('Both parents').'&nbsp;&nbsp;';	
			$check=''; if ($selection['parent_status']=="" OR $selection['parent_status']=='allpersons'){ $check=' checked'; }
			echo '<input type="radio" name="parent_status" value="allpersons"'.$check.'>'.__('All').'&nbsp;&nbsp;';
			//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			//echo '</td><td>';
			echo '</td>';
			echo '</tr>';

		}	// *** End of advanced search fields ***

		// *** Check for multiple family trees ***
		echo '<tr><td colspan="3" class="no_border center">';
		$datasql2 = $dbh->query("SELECT * FROM humo_trees");
		$num_rows2 = $datasql2->rowCount();
		if ($num_rows2>1 AND $humo_option['one_name_study']=='n'){
			$checked=''; if ($search_database=="tree_selected"){ $checked='CHECKED'; }
			echo '<input type="radio" name="search_database" value="tree_selected" '.$checked.'> '.__('Selected family tree');
			$checked=''; if ($search_database=="all_trees"){ $checked='checked'; }
			echo '<input type="radio" name="search_database" value="all_trees" '.$checked.'> '.__('All family trees');
			$checked=''; if ($search_database=="all_but_this"){ $checked='checked'; }
			echo '<input type="radio" name="search_database" value="all_but_this" '.$checked.'> '.__('All but selected tree');
		}
		if ($num_rows2>1 AND $humo_option['one_name_study']=='y'){
			echo '<input type="hidden" name="search_database" value="all_trees">';
		}
		echo '&nbsp;&nbsp; <input type="submit" value="'.__('Search').'" name="B1">';
		// *** Reset button doesn't work if values are allready added in Search form ***
		//echo ' <input type="reset" value="'.__('Reset').'">';

		if ($adv_search==true){

			//echo '&nbsp;<a href="'.CMS_ROOTPATH.'list.php?adv_search=0">'.__('Standard search').'</a>';
			echo '&nbsp;<a href="'.$list_var2.'adv_search=0&reset=1">'.__('Standard search').'</a>';

			//echo '<input type="hidden" name="adv_search2" value="1">';
			echo '<input type="hidden" name="adv_search" value="1">';

			//======== HELP POPUP ========================
			echo '<div class="'.$rtlmarker.'sddm" style="display: inline;">';
			echo '&nbsp;&nbsp;&nbsp;<a href="#" style="display:inline" ';
			echo 'onmouseover="mopen(event,\'help_menu\',10,150)"';
			echo 'onmouseout="mclosetime()"><strong>'.__('Help').'</strong></a>';
			echo '<div class="sddm_fixed" style="z-index:40; text-align:'.$alignmarker.'; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
				echo '<table width="98%" class="humo"><tr>';
				echo '<td width="50">'.__('Tip').':</td>';

				echo '<td>';
				echo __('With Advanced Search you can easily create lists like: all persons with surname <b>Schaap</b> who were born <b>between 1820 and 1840</b> in <b>Amsterdam</b><br>You can also search without a name: all persons who <b>died in 1901</b> in <b>Amstelveen.</b>');
				echo '</td></tr>';

				echo '<tr><td>';
				echo __('Note:</td><td>When you use the birth and/ or death search boxes please note this:<br>1. Persons for whom no birth/ death data exist in the database, will not be found.<br>2. Persons with privacy settings will not be shown, unless you are logged in with the proper permissions.<br>These persons can be found by searching by name and/ or surname only.');
				echo '</td></tr></table>';

				echo '</div>';
			echo '</div><br>';
			//=================================

		}
		else{
			echo '&nbsp;<a href="'.$list_var2.'adv_search=1&index_list=search">'.__('Advanced search').'</a><br>';
		}

		echo '</td></tr></table></form>';
	}

	if (CMS_SPECIFIC=='Joomla'){ $uri_path_string = "index.php?option=com_humo-gen&amp;task=list&amp;"; }
	else { $uri_path_string = $uri_path."list.php?"; }

	// *** Check for search results ***
	if (@$person_result->rowCount()==0) {
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

		// *** Don't use this code if search is done with partner or for people with only mother or only father***
		if(!$selection['spouse_firstname'] AND !$selection['spouse_lastname'] AND $selection['parent_status']!="motheronly" AND $selection['parent_status']!="fatheronly") {
			echo $count_persons.__(' persons found.');
		}
		else {
			echo '<div id="found_div">&nbsp;</div>';
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

		echo ' <form method="POST" action="'.$uri_path_string.$button_line.'" style="display : inline;">';
			if ($list_expanded==true){
				echo '<input type="hidden" name="list_expanded" value="0">';
				echo '<input type="Submit" name="submit" value="'.__('Concise view').'">';
			}
			else{
				echo '<input type="hidden" name="list_expanded" value="1">';
				echo '<input type="Submit" name="submit" value="'.__('Expanded view').'">';
			}
		echo '</form>';

		// *** Don't use code if search is done with partner or for people with only mother or only father***
		if(!$selection['spouse_firstname'] AND !$selection['spouse_lastname'] AND $selection['parent_status']!="motheronly" AND $selection['parent_status']!="fatheronly") {
//			echo '<br>'.$line_pages;
			echo ' '.$line_pages.'<br><br>';
		}

		// *** No results ***
		if ($person_result->rowCount()==0) {
			echo '<br><div class="center">'.__('No names found.').'</div>';
		}

	echo '</div>';

	$dir="";
	if($language["dir"]=="rtl") {
		$dir="rtl"; // loads the proper CSS for rtl display (rtlindex_list2):
	}

	// with extra sort date column, set smaller left margin
	$listnr="2";      // default 20% margin
	//if($index_list != "places" AND ($selectsort=='sort_birthdate' OR $selectsort=='sort_deathdate' OR $selectsort=='sort_baptdate' OR $selectsort=='sort_burieddate')) {
	//	$listnr="3";   // 5% margin
	//}
	//echo '<div class="'.$dir.'index_list'.$listnr.'">';

	//*** Show persons ******************************************************************
	$privcount=0; // *** Count privacy persons ***

	$selected_place="";

	// *** Table to hold left sort date column (when necessary) and right person list column ***
	//echo '<table style="cellpadding:0px; border-collapse:collapse;">';
	if ($search_database=='all_trees' OR $search_database=='all_but_this')
		echo '<table class="humo" align="center">';
		else echo '<table class="humo index_table" align="center">';

	echo '<tr class=table_headline>';
	if ($index_list=='places') echo '<th>'.__('Places').'</th>';
	echo '<th colspan="2">'.__('Name').'</th>';
	echo '<th colspan="2" width="250px">'.ucfirst(__('born')).'/ '.ucfirst(__('baptised')).'</th>';
	echo '<th colspan="2" width="250px">'.ucfirst(__('died')).'/ '.ucfirst(__('buried')).'</th>';
	if ($search_database=='all_trees' OR $search_database=='all_but_this') echo '<th>'.__('Family tree').'</th>';
	echo '</tr>';

	if ($index_list!='places'){
		echo '<tr class=table_headline>';
		$style=''; $sort_reverse=$sort_desc; $img='';
		if ($selectsort=="sort_firstname"){
			$style=' style="background-color:#ffffa0"';
			$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
		}
		echo '<th colspan="2">'.__('Sort by:').' <a href="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_firstname&sort_desc='.$sort_reverse.'"'.$style.'>'.ucfirst(__('firstname')).' <img src="images/button3'.$img.'.png"></a>';
			$style=''; $sort_reverse=$sort_desc; $img='';
			if ($selectsort=="sort_lastname"){
				$style=' style="background-color:#ffffa0"';
				$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
			}
			echo ' <a href="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_lastname&sort_desc='.$sort_reverse.'"'.$style.'>'.ucfirst(__('lastname')).' <img src="images/button3'.$img.'.png"></a></th>';
		$style=''; $sort_reverse=$sort_desc; $img='';
		if ($selectsort=="sort_birthdate"){
			$style=' style="background-color:#ffffa0"';
			$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
		}
		echo '<th><a href="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_birthdate&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Date').' <img src="images/button3'.$img.'.png"></a></th>';
		$style=''; $sort_reverse=$sort_desc; $img='';
		if ($selectsort=="sort_birthplace"){
			$style=' style="background-color:#ffffa0"';
			$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
		}
		echo '<th><a href="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_birthplace&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Place').' <img src="images/button3'.$img.'.png"></a></th>';
		$style=''; $sort_reverse=$sort_desc; $img='';
		if ($selectsort=="sort_deathdate"){
			$style=' style="background-color:#ffffa0"';
			$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
		}
		echo '<th><a href="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_deathdate&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Date').' <img src="images/button3'.$img.'.png"></a></th>';
		$style=''; $sort_reverse=$sort_desc; $img='';
		if ($selectsort=="sort_deathplace"){
			$style=' style="background-color:#ffffa0"';
			$sort_reverse='1'; if ($sort_desc=='1'){ $sort_reverse='0'; $img='up'; }
		}
		echo '<th><a href="list.php?index_list='.$index_list.'&start=1&item=0&sort=sort_deathplace&sort_desc='.$sort_reverse.'"'.$style.'>'.__('Place').' <img src="images/button3'.$img.'.png"></a></th>';

		if ($search_database=='all_trees' OR $search_database=='all_but_this') echo '<th><br></th>';

		echo '</tr>';
	}
	$pers_counter = 0;

	if($adv_search==true AND $selection['parent_status']!="allpersons" AND $selection['parent_status']!="noparents") {
		echo '<script type="text/javascript"> 
			document.getElementById("found_div").innerHTML = "'.__('Loading...').'";
			</script>';
	}
	
	while (@$personDb = $person_result->fetch(PDO::FETCH_OBJ)) {

		//TEST MYSQL 5.7
		//$query="SELECT * FROM humo_persons WHERE pers_tree_id='".$personDb->pers_tree_id."' AND pers_gedcomnumber='".$personDb->pers_gedcomnumber."'";
//		$query="SELECT * FROM humo_persons WHERE pers_id='".$personDb->pers_id."'";
//		$person2_result = $dbh->query($query);
//		$person2Db = $person2_result->fetch(PDO::FETCH_OBJ);


		$spouse_found='1';

		// *** Search name of spouse ***
		if($selection['spouse_firstname'] OR $selection['spouse_lastname']) {
			$spouse_found='0';
			$person_fams=explode(";",$personDb->pers_fams);

			// *** Search all persons with a spouse IN the same tree as the 1st person ***
			for ($marriage_loop=0; $marriage_loop<count($person_fams); $marriage_loop++){
				$famDb = $db_functions->get_family($person_fams[$marriage_loop],'man-woman');

				// *** Search all persons with a spouse IN the same tree as the 1st person ***
				$spouse_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='".$personDb->pers_tree_id."' AND";
				if ($user['group_kindindex']=="j"){
					$spouse_qry= "SELECT *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name
						FROM humo_persons WHERE pers_tree_id='".$personDb->pers_tree_id."' AND";
				}

				if ($personDb->pers_gedcomnumber==$famDb->fam_man){
					$spouse_qry.=' pers_gedcomnumber="'.safe_text_db($famDb->fam_woman).'"';
				}
				else{
					$spouse_qry.=' pers_gedcomnumber="'.safe_text_db($famDb->fam_man).'"';
				}

				if ($selection['spouse_lastname']) {
					if ($selection['spouse_lastname']==__('...')){
						$spouse_qry.=" AND pers_lastname=''";
					}
					elseif ($user['group_kindindex']=="j"){
						$spouse_qry.=" AND CONCAT( REPLACE(pers_prefix,'_',' ') ,pers_lastname) ".name_qry($selection['spouse_lastname'], $selection['part_spouse_lastname']);
					}
					else {
						$spouse_qry.=" AND pers_lastname ".name_qry($selection['spouse_lastname'], $selection['part_spouse_lastname']);
					}
				}
				//if ($selection['pers_prefix']){
				//  $spouse_qry.=" AND pers_prefix='".$selection['pers_prefix']."'";
				//}
				if ($selection['spouse_firstname']){
					$spouse_qry.=" AND pers_firstname ".name_qry($selection['spouse_firstname'], $selection['part_spouse_firstname']);
				}
				$spouse_result= $dbh->query($spouse_qry);
				$spouseDb= $spouse_result->fetch(PDO::FETCH_OBJ);
				if (isset($spouseDb->pers_id)){ $spouse_found='1'; break; }
			}


		}  // End of spouse search

		// *** Search parent status (no parents, only mother, only father) ***
		$parent_status_found='1';
		if($adv_search==true AND $selection['parent_status']!="allpersons" AND $selection['parent_status']!="noparents") { 
			$parent_status_found='0';
			$par_famc = ""; if(isset($personDb->pers_famc)) $par_famc= $personDb->pers_famc;
			if($par_famc != "") { 
				$parDb = $db_functions->get_family($par_famc,'man-woman');

				if($selection['parent_status']=="fatheronly" AND substr($parDb->fam_man,0,1)=="I"
					AND substr($parDb->fam_woman,0,1) != "I") {
					$parent_status_found='1'; 
				}
				elseif($selection['parent_status']=="motheronly" AND substr($parDb->fam_man,0,1)!="I"
					AND substr($parDb->fam_woman,0,1)=="I") {
					$parent_status_found='1'; 
				}
			}
		}

		// *** Show search results ***
		if ($spouse_found=='1' AND ($parent_status_found=='1' OR ($parent_status_found!='1' AND !isset($_POST['adv_search'])))) { 
			//AND $parent_status_found=='1'
			$pers_counter++; // needed for spouses search and mother/father only search
			// Added by Yossi
			$person_cls = New person_cls;
			$person_cls->construct($personDb);
			$privacy=$person_cls->privacy;
			if($privacy==1) { // Privacy restricted person
				if($selection['birth_place']=='' AND $selection['birth_year']=='' AND $selection['death_place']=='' AND $selection['death_year']=='') {
					// No search using birth/death place and/or date

					// *** Extra privacy filter check for total_filter ***
					if ($user["group_pers_hide_totally_act"]=='j' AND strpos(' '.$personDb->pers_own_code,$user["group_pers_hide_totally"])>0){
						$privcount++;
					}
					else{
						show_person($personDb);
//show_person($person2Db);
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
//show_person($person2Db);
				}
			}
		}
	}

	echo '</table>';
	if($privcount) { echo "<br>".$privcount.__(' persons are not shown due to privacy settings').".<br>";}

	//echo '</div>';

	// *** Don't execute this code if spouse search is used or mother/father only persons***
	if(!$selection['spouse_firstname'] AND !$selection['spouse_lastname'] AND $selection['parent_status']!="motheronly" AND $selection['parent_status']!="fatheronly") {
		echo '<br><div class="index_list1">'.$line_pages.'</div>';
	}

	//echo '</div>';

// For use with inline site?
echo '<script type="text/javascript"> 
	if(window.self != window.top) {
		var framew = window.frameElement.offsetWidth; 
		document.getElementById("content").style.width = framew-40+"px";
		var indexes = document.getElementsByClassName("index_table");
		for (var i = 0; i < indexes.length; i++) {
			indexes[i].style.width = framew-40+"px";
		}
		var lists = document.getElementsByClassName("index_list1");
		for (var i = 0; i < lists.length; i++) {
			lists[i].style.width = framew-40+"px";
		}
	}
</script>';

echo '<script type="text/javascript"> 
	document.getElementById("found_div").innerHTML = \''.$pers_counter.__(' persons found.').'\';
</script>';

//for testing only:
//echo 'Query: '.$query." LIMIT ".safe_text_db($item).",".$nr_persons.'<br>';
//echo 'Count qry: '.$count_qry.'<br>';
//echo '<p>index_list: '.$index_list;
//echo '<br>nr. of persons: '.$count_persons;

include_once(CMS_ROOTPATH."footer.php");
?>