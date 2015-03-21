<?php
/*
This index list for places by families was a copy of index list by persons.
Maybe it's possible to combine these two lists later, but processing is different.
At this moment it's easier to just make a second index list...

sep. 2014 Huub: added this script to HuMo-gen.
*/

include_once("header.php"); //returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/marriage_cls.php");

error_reporting(E_ALL);
@set_time_limit(300);

// *** show person ***
function show_person($familyDb){
	global $dbh, $tree_id, $selected_place, $language, $user;
	global $bot_visit, $humo_option, $uri_path, $search_database, $list_expanded;
	global $selected_language, $privacy, $dirmark1, $dirmark2, $rtlmarker;
	global $select_marriage_notice, $select_marriage, $select_marriage_notice_religious, $select_marriage_religious;

	//if ($user['group_kindindex']=="j"){
	//	$query = "(SELECT SQL_CALC_FOUND_ROWS *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_birth_place as place_order
	//		FROM ".safe_text($_SESSION['tree_prefix'])."person";
	//}
	//else{
	//	$query = "(SELECT SQL_CALC_FOUND_ROWS *, pers_birth_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."person";
	//}

	if ($familyDb->fam_man)
		$selected_person1=$familyDb->fam_man;
	else
		$selected_person1=$familyDb->fam_woman;
	//$query = 'SELECT * FROM '.safe_text($_SESSION['tree_prefix']).'person WHERE pers_gedcomnumber="'.$selected_person1.'"';
	$query = "SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$selected_person1."'";
	$personqry=$dbh->query($query);
	$personDb=@$personqry->fetchObject();

	$pers_tree_prefix=$personDb->pers_tree_prefix;

	//if (CMS_SPECIFIC=='Joomla'){
	//	$start_url='index.php?option=com_humo-gen&amp;task=family&amp;database='.$pers_tree_prefix.
	//		'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
	//}
	//else
	if ($humo_option["url_rewrite"]=="j"){	// *** url_rewrite ***
		// *** $uri_path made in header.php ***
		$start_url= $uri_path.'family/'.$pers_tree_prefix.'/'.$personDb->pers_indexnr.'/'.$personDb->pers_gedcomnumber.'/';
	}
	else{
		$start_url=CMS_ROOTPATH.'family.php?database='.$pers_tree_prefix.'&amp;id='.$personDb->pers_indexnr.'&amp;main_person='.$personDb->pers_gedcomnumber;
	}

	// *** Person class used for name and person pop-up data ***
	$person_cls = New person_cls;
	$person_cls->construct($personDb);
	$privacy=$person_cls->privacy;

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

	// *** Show extra colums before a person in index places ***
	if ($selected_place!=$familyDb->place_order)
		echo '<td colspan="7"><b>'.$dirmark2."$familyDb->place_order</b></td></tr><tr>";
	$selected_place=$familyDb->place_order;
	echo '<td valign="top" style="white-space:nowrap;width:90px">';

		if ($select_marriage_notice=='1'){
			if ($selected_place==$familyDb->fam_marr_notice_place)
				echo '<span class="place_index place_index_selected">'.__('&infin;').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

		if ($select_marriage_notice_religious=='1'){
			if ($selected_place==$familyDb->fam_marr_church_notice_place)
				echo '<span class="place_index place_index_selected">'.__('o').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

		if ($select_marriage=='1'){
			if ($selected_place==$familyDb->fam_marr_place)
				echo '<span class="place_index place_index_selected">'.__('X').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

		if ($select_marriage_religious=='1'){
			if ($selected_place==$familyDb->fam_marr_church_place)
				echo '<span class="place_index place_index_selected">'.__('x').'</span>';
			else
				echo '<span class="place_index">&nbsp;</span>';
		}

	echo '</td>';

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

	echo '</td><td style="border-left:0px;">';

	// *** Show name of person ***
	echo ' <a href="'.$start_url.'">'.$index_name.'</a>';

	//*** Show spouse/ partner ***
	if ($list_expanded==true AND $personDb->pers_fams){
		$marriage_array=explode(";",$personDb->pers_fams);
		// *** Code to show only last marriage ***
		$nr_marriages=count($marriage_array);

		//$stmt = $dbh->prepare("SELECT * FROM ".safe_text($pers_tree_prefix)."family WHERE fam_gedcomnumber=?");
		$stmt = $dbh->prepare("SELECT * FROM humo_families WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber=?");
		$stmt->bindParam(1, $marr_arr);
		//$stmt2 = $dbh->prepare("SELECT * FROM ".safe_text($pers_tree_prefix)."person WHERE pers_gedcomnumber=?");
		$stmt2 = $dbh->prepare("SELECT * FROM humo_persons WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber=?");
		$stmt2->bindParam(1, $partnid);
		for ($x=0; $x<=$nr_marriages-1; $x++){
			$marr_arr = $marriage_array[$x];
			$stmt->execute();
			$fam_partnerDb = $stmt->fetch();

			// *** This check is better then a check like: $personDb->pers_sexe=='F', because of unknown sexe or homosexual relations. ***
			if ($personDb->pers_gedcomnumber==$fam_partnerDb['fam_man'])
				$partner_id=$fam_partnerDb['fam_woman'];
			else
				$partner_id=$fam_partnerDb['fam_man'];

			$relation_short=__('&amp;');
			if ($fam_partnerDb['fam_marr_date'] OR $fam_partnerDb['fam_marr_place'] OR $fam_partnerDb['fam_marr_church_date'] OR $fam_partnerDb['fam_marr_church_place'])
				$relation_short=__('X');
			if($fam_partnerDb['fam_div_date'] OR $fam_partnerDb['fam_div_place'])
				$relation_short=__(') (');

			if ($partner_id!='0' AND $partner_id!=''){
				$partnid = $partner_id;
				$stmt2->execute();
				$partnerDb = $stmt2->fetch(PDO::FETCH_OBJ);

				$partner_cls = New person_cls;
				$privacy2=$person_cls->privacy;
				$name=$partner_cls->person_name($partnerDb);
			}
			else{
				$name["standard_name"]=__('N.N.');
			}

			if ($nr_marriages>1) echo ',';
			if (@$partnerDb->pers_gedcomnumber!=$familyDb->fam_woman)
				echo ' <span class="index_partner" style="font-size:10px;">';
			if ($nr_marriages>1){
				if ($x==0) echo __('1st');
				elseif ($x==1) echo __('2nd');
				elseif ($x==2) echo __('3rd');
				elseif ($x>2) echo ($x+1).__('th');
			}
			echo ' '.$relation_short.' '.rtrim($name["standard_name"]);
			if (@$partnerDb->pers_gedcomnumber!=$familyDb->fam_woman)
				echo '</span>';
		}
	}
	// *** End spouse/ partner ***


	echo '</td><td style="white-space:nowrap;">';
		$info="";
		if ($familyDb->fam_marr_church_notice_date)
			$info=__('o').' '.date_place($familyDb->fam_marr_church_notice_date, '');
		if ($familyDb->fam_marr_notice_date)
			$info=__('&infin;').' '.date_place($familyDb->fam_marr_notice_date, '');
		//echo "<span style='font-size:90%'>".$info.$dirmark1."</span>";
		if ($privacy==1 and $info) echo ' '.__('PRIVACY FILTER');
			else echo $info;

	echo '</td><td>';
		$info="";
		if ($familyDb->fam_marr_church_notice_place)
			$info=__('o').' '.$familyDb->fam_marr_church_notice_place;
		if ($familyDb->fam_marr_notice_place)
			$info=__('&infin;').' '.$familyDb->fam_marr_notice_place;
		//echo "<span style='font-size:90%'>".$info.$dirmark1."</span>";
		if ($privacy==1 and $info) echo ' '.__('PRIVACY FILTER');
			else echo $info;

	echo '</td><td style="white-space:nowrap;">';
		$info="";
		if ($familyDb->fam_marr_church_date)
			$info=__('x').' '.date_place($familyDb->fam_marr_church_date, '');
		if ($familyDb->fam_marr_date)
			$info=__('X').' '.date_place($familyDb->fam_marr_date, '');
		//echo "<span style='font-size:90%'>".$info.$dirmark1."</span>";
		if ($privacy==1 and $info) echo ' '.__('PRIVACY FILTER');
			else echo $info;

	echo '</td><td>';
		$info="";
		if ($familyDb->fam_marr_church_place)
			$info=__('x').' '.$familyDb->fam_marr_church_place;
		if ($familyDb->fam_marr_place)
			$info=__('X').' '.$familyDb->fam_marr_place;
		//echo "<span style='font-size:90%'>".$info.$dirmark1."</span>";
		if ($privacy==1 and $info) echo ' '.__('PRIVACY FILTER');
			else echo $info;
 
	echo '</td></tr>';
} // *** end function show person ***


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


// *** For index places ***
$place_name='';
$select_marriage_notice='0'; $select_marriage='0'; $select_place='0'; $select_marriage_notice_religious='0'; $select_marriage_religious='0';
if (isset($_POST['place_name'])){
	$place_name=$_POST['place_name'];
	//$place_name=htmlentities($_POST['place_name'],ENT_QUOTES,'UTF-8');
	$_SESSION["save_place_name"]=$place_name;

	if (isset($_POST['select_marriage_notice'])){	$select_marriage_notice='1'; $_SESSION["save_select_marriage_notice"]='1'; }
		else{ $_SESSION["save_select_marriage_notice"]='0'; }
	if (isset($_POST['select_marriage'])){ $select_marriage='1'; $_SESSION["save_select_marriage"]='1'; }
		else{ $_SESSION["save_select_marriage"]='0'; }
	if (isset($_POST['select_place'])){ $select_place='1'; $_SESSION["save_select_place"]='1'; }
		else{ $_SESSION["save_select_place"]='0'; }
	if (isset($_POST['select_marriage_notice_religious'])){ $select_marriage_notice_religious='1'; $_SESSION["save_select_marriage_notice_religious"]='1'; }
		else{ $_SESSION["save_select_marriage_notice_religious"]='0'; }
	if (isset($_POST['select_marriage_religious'])){ $select_marriage_religious='1'; $_SESSION["save_select_marriage_religious"]='1'; }
		else{ $_SESSION["save_select_marriage_religious"]='0'; }
}
$part_place_name='';
if (isset($_POST['part_place_name'])){
	$part_place_name=$_POST['part_place_name'];
	$_SESSION["save_part_place_name"]=$part_place_name;
}

// *** Search for places in birth-baptise-died places etc. ***
if (isset($_SESSION["save_place_name"])) $place_name=$_SESSION["save_place_name"];
if (isset($_SESSION["save_part_place_name"])) $part_place_name=$_SESSION["save_part_place_name"];

// *** Enable select boxes ***
if (isset($_GET['reset'])){
	$select_marriage_notice='1'; $_SESSION["save_select_marriage_notice"]='1';
	$select_marriage='1'; $_SESSION["save_select_marriage"]='1';
	$select_marriage_notice_religious='1'; $_SESSION["save_select_marriage_notice_religious"]='1';
	$select_marriage_religious='1'; $_SESSION["save_select_marriage_religious"]='1';
}
else{
	// *** Read and set select boxes for multiple pages ***
	if (isset($_SESSION["save_select_marriage_notice"])){ $select_marriage_notice=$_SESSION["save_select_marriage_notice"]; }
	if (isset($_SESSION["save_select_marriage"])){ $select_marriage=$_SESSION["save_select_marriage"]; }
	if (isset($_SESSION["save_select_marriage_notice_religious"])){ $select_marriage_notice_religious=$_SESSION["save_select_marriage_notice_religious"]; }
	if (isset($_SESSION["save_select_marriage_religious"])){ $select_marriage_religious=$_SESSION["save_select_marriage_religious"]; }
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

//*** Places index ***
// *** EXAMPLE of a UNION querie ***
//$qry = "(SELECT * FROM humo1_person ".$query.') ';
//$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
//$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
//$qry.= " ORDER BY pers_lastname, pers_firstname";

$query='';
$start=false;

// *** Search marriage place ***
if ($select_marriage=='1'){
	//$query = "(SELECT SQL_CALC_FOUND_ROWS *, fam_marr_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."family";
	$query = "(SELECT SQL_CALC_FOUND_ROWS *, fam_marr_place as place_order
		FROM humo_families";
	if($place_name)
		$query.= " WHERE fam_tree_id='".$tree_id."' AND fam_marr_place ".name_qry($place_name,$part_place_name);
	else
		$query.= " WHERE fam_tree_id='".$tree_id."' AND fam_marr_place LIKE '_%'";
	$query.=')';
	$start=true;
}

// *** Search marriage church place ***
if ($select_marriage_religious=='1'){
	if ($start==true){
		$query.=' UNION '; $calc='';
	}
	else{
		$calc='SQL_CALC_FOUND_ROWS ';
	}
	//$query.= "(SELECT ".$calc."*, fam_marr_church_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."family";
	$query.= "(SELECT ".$calc."*, fam_marr_church_place as place_order
		FROM humo_families";
	if($place_name)
		$query.= " WHERE fam_tree_id='".$tree_id."' AND fam_marr_church_place ".name_qry($place_name,$part_place_name);
	else
		$query.= " WHERE fam_tree_id='".$tree_id."' AND fam_marr_church_place LIKE '_%'";
	$query.=')';
	$start=true;
}

// *** Search marriage notice place ***
if ($select_marriage_notice=='1'){
	if ($start==true){
		$query.=' UNION '; $calc='';
	}
	else{
		$calc='SQL_CALC_FOUND_ROWS ';
	}
	//$query.= "(SELECT ".$calc."*, fam_marr_notice_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."family";
	$query.= "(SELECT ".$calc."*, fam_marr_notice_place as place_order 
		FROM humo_families";
	if($place_name)
		$query.= " WHERE fam_tree_id='".$tree_id."' AND fam_marr_notice_place ".name_qry($place_name,$part_place_name);
	else
		$query.= " WHERE fam_tree_id='".$tree_id."' AND fam_marr_notice_place LIKE '_%'";
	$query.=')';
	$start=true;
}

// *** Search marriage notice place ***
if ($select_marriage_notice_religious=='1'){
	if ($start==true){
		$query.=' UNION '; $calc='';
	}
	else{
		$calc='SQL_CALC_FOUND_ROWS ';
	}
	//$query.= "(SELECT ".$calc."*, fam_marr_church_notice_place as place_order FROM ".safe_text($_SESSION['tree_prefix'])."family";
	$query.= "(SELECT ".$calc."*, fam_marr_church_notice_place as place_order
		FROM humo_families";
	if($place_name)
		$query.= " WHERE fam_tree_id='".$tree_id."' AND fam_marr_church_notice_place ".name_qry($place_name,$part_place_name);
	else
		$query.= " WHERE fam_tree_id='".$tree_id."' AND fam_marr_church_notice_place LIKE '_%'";
	$query.=')';
	$start=true;
}

// *** Order by place and marriage date ***
$query.=' ORDER BY place_order, substring(fam_marr_date,-4)';


// **************************
// *** Generate indexlist ***
// **************************

	include_once(CMS_ROOTPATH."menu.php");

	//*** Show number of persons and pages *****************************************
	$item=0; if (isset($_GET['item'])){ $item=$_GET['item']; }
	$start=0; if (isset($_GET["start"])){ $start=$_GET["start"]; }
	$nr_persons=$humo_option['show_persons'];

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

	//if (CMS_SPECIFIC=='Joomla'){
	//	$list_var  = 'index.php?option=com_humo-gen&amp;task=list';  // for use without query string
	//	$list_var2 = 'index.php?option=com_humo-gen&amp;task=list&amp;'; // for use with query string
	//}
	//else {
		$list_var  = CMS_ROOTPATH.'list_places_families.php';
		$list_var2 = CMS_ROOTPATH.'list_places_families.php?';
	//}

	//if ($index_list=='places'){
		//echo '<div class="index_list1">';
		if($language['dir']=="ltr") {
			echo '<div class="left_box" style="width:150px;">';
		}
		else {
			echo '<div class="right_box" style="width:150px;">';
		}

		//************** search places **************************************
		//echo ' <form method="post" action="'.$list_var.'" style="display : inline;">';
		echo ' <form method="post" action="'.$list_var.'">';
			echo __('Find place').':';

			$checked=''; if ($select_marriage_notice=='1'){$checked='checked';}
			echo '<p><input type="Checkbox" name="select_marriage_notice" value="1" '.$checked.'> '.__('&infin;').' '.lcfirst(__('Marriage notice')).'<br>';

			$checked=''; if ($select_marriage_notice_religious=='1'){$checked='checked';}
			echo '<input type="Checkbox" name="select_marriage_notice_religious" value="1" '.$checked.'> '.__('o').' '.lcfirst(__('Married notice (religious)')).'<br>';

			$checked=''; if ($select_marriage=='1'){$checked='checked';}
			echo '<br><input type="Checkbox" name="select_marriage" value="1" '.$checked.'> '.__('X').' '.__('marriage').'<br>';

			$checked=''; if ($select_marriage_religious=='1'){$checked='checked';}
			echo '<input type="Checkbox" name="select_marriage_religious" value="1" '.$checked.'> '.__('x').' '.lcfirst(__('Married (religious)'));

			echo '<p><select name="part_place_name">';
			echo '<option value="contains">'.__('Contains').'</option>';

			$select_item=''; if ($part_place_name=='equals'){ $select_item=' selected'; }
			echo '<option value="equals"'.$select_item.'>'.__('Equals').'</option>';

			$select_item=''; if ($part_place_name=='starts_with'){ $select_item=' selected'; }
			echo '<option value="starts_with"'.$select_item.'>'.__('Starts with').'</option>';
			echo '</select>';

			echo '<p><input type="text" name="place_name" value="'.$place_name.'" size="15"><br>';

			echo '<p><input type="submit" value="'.__('Search').'" name="B1">';
		echo '</form>';
		//***************** end search of places **********************************

		echo '</div>';
	//}

	if (CMS_SPECIFIC=='Joomla'){ $uri_path_string = "index.php?option=com_humo-gen&amp;task=list&amp;"; }
	else { $uri_path_string = $uri_path."list_places_families.php?"; }

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
			"&amp;start=".$i.
			"&amp;item=".$calculated.
			'"> =&gt;</a>';
		}
	}

	echo '<div class="index_list1">';

		// *** Don't use this code if search is done with partner ***
		//if(!$selection['spouse_firstname'] AND !$selection['spouse_lastname']) {
			echo $count_persons.' '.__('families found.');
		//}

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
		//$button_line.=  "&amp;index_list=".$index_list;

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

		echo ' '.$line_pages.'<br><br>';

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
	echo '<table class="humo index_table" align="center">';

	echo '<tr class=table_headline>';
		echo '<th>'.__('Places').'</th>';
		echo '<th colspan="2">'.__('Family').'</th>';
		echo '<th colspan="2" width="280px">'.ucfirst(__('Married notice (religious)')).'</th>';
		echo '<th colspan="2" width="280px">'.ucfirst(__('Married (religious)')).'</th>';
	echo '</tr>';

	while (@$familyDb = $person_result->fetch(PDO::FETCH_OBJ)) {
		// *** Man privacy filter ***
		//$query = 'SELECT * FROM '.safe_text($_SESSION['tree_prefix']).'person WHERE pers_gedcomnumber="'.$familyDb->fam_man.'"';
		$query = "SELECT * FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$familyDb->fam_man."'";
		$personqry=$dbh->query($query);
		$personDb=@$personqry->fetchObject();
		// *** Person class used for name and person pop-up data ***
		$man_cls = New person_cls;
		$man_cls->construct($personDb);

		// *** Woman privacy filter ***
		//$query = 'SELECT * FROM '.safe_text($_SESSION['tree_prefix']).'person WHERE pers_gedcomnumber="'.$familyDb->fam_woman.'"';
		$query = "SELECT * FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".$familyDb->fam_woman."'";
		$personqry=$dbh->query($query);
		$personDb=@$personqry->fetchObject();
		// *** Person class used for name and person pop-up data ***
		$woman_cls = New person_cls;
		$woman_cls->construct($personDb);

		// *** Proces marriage using a class ***
		$marriage_cls = New marriage_cls;
		$marriage_cls->construct($familyDb, $man_cls->privacy, $woman_cls->privacy);
		$family_privacy=$marriage_cls->privacy;

		// *** $family_privacy='1' = filter ***
		if ($family_privacy)
			$privcount++;
		else
			show_person($familyDb);
	}

	echo '</table>';

	if($privcount) { echo "<br>".$privcount.__(' persons are not shown due to privacy settings').".<br>";}

	echo '<br><div class="index_list1">'.$line_pages.'</div>';

//for testing only:
//echo 'Query: '.$query." LIMIT ".safe_text($item).",".$nr_persons.'<br>';
//echo 'Count qry: '.$count_qry.'<br>';
//echo '<p>index_list: '.$index_list;
//echo '<br>nr. of persons: '.$count_persons;

include_once(CMS_ROOTPATH."footer.php");
?>