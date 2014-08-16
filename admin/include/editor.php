<?php
/**
* This is the editor file for HuMo-gen.
*
* If you are reading this in your web browser, your server is probably
* not configured correctly to run PHP applications!
*
* See the manual for basic setup instructions
*
* http://www.huubmons.nl/software/
*
* ----------
*
* Copyright (C) 2008-2014 Huub Mons,
* Klaas de Winkel, Jan Maat, Jeroen Beemster, Louis Ywema, Theo Huitema,
* René Janssen, Yossi Beck
* and others.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

//globals for joomla
global $tree_prefix, $gedcom_date, $gedcom_time, $pers_gedcomnumber;

if(CMS_SPECIFIC=="Joomla") {
	$phpself='index.php?option=com_humo-gen&amp;task=admin&amp;page=editor';
	$joomlastring='option=com_humo-gen&amp;task=admin&amp;';  // can be placed after existing index?
	$family_string='index.php?option=com_humo-gen&task=family&amp;';
	$sourcestring='index.php?option=com_humo-gen&task=source&amp;';
	$addresstring='index.php?option=com_humo-gen&task=address&amp;';
	$path_prefix=''; // in joomla we are already in main joomla map and do not have to "get out of admin"
}
else {
	$phpself=$_SERVER['PHP_SELF'];
	$joomlastring='';
	$family_string='../family.php?';
	$sourcestring='../source.php?';
	$addresstring='../address.php?';
	$path_prefix='../';
}

$joomlapath=CMS_ROOTPATH_ADMIN.'include/';

include_once ($joomlapath."editor_cls.php");
$editor_cls = New editor_cls;

include_once (CMS_ROOTPATH."include/language_date.php");
include_once (CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/language_event.php");

include ('editor_event_cls.php');
$event_cls = New editor_event_cls;


// *****************
// *** FUNCTIONS ***
// *****************

// *** Calculate nr. of persons and families ***
function family_tree_update($tree_prefix){
	global $dbh;

	$total = $dbh->query("SELECT COUNT(*) FROM ".$tree_prefix."person"); 
	$total = $total->fetch();
	$nr_persons=$total[0]; 

	$total1 = $dbh->query("SELECT COUNT(*) FROM ".$tree_prefix."family"); 
	$total1 = $total1->fetch();
	$nr_families=$total1[0]; 

	$tree_date=date("Y-m-d H:i");
	$sql="UPDATE humo_trees SET
	tree_persons='".$nr_persons."',
	tree_families='".$nr_families."',
	tree_date='".$tree_date."'
	WHERE tree_prefix='".$tree_prefix."'";
	$dbh->query($sql);
}

// *** Show event options ***
function event_option($event_gedcom,$event){
	global $language;
	$selected=''; if ($event_gedcom==$event){ $selected=' SELECTED'; }
	echo '<option value="'.$event.'"'.$selected.'>'.language_event($event).'</option>';
}

function witness_edit($witness, $multiple_rows=''){
	global $dbh, $tree_prefix, $language;

	// *** Witness: pull-down menu ***
	$witnessqry=$dbh->query("SELECT * FROM ".$tree_prefix."person ORDER BY pers_lastname, pers_firstname");
	echo '<select size="1" name="text_event2'.$multiple_rows.'" style="width: 250px">';
	echo '<option value=""></option>';
	while ($witnessDb=$witnessqry->fetch(PDO::FETCH_OBJ)){
		$selected=''; if ($witnessDb->pers_gedcomnumber==substr($witness,1,-1)){ $selected=' SELECTED'; }
		echo '<option value="@'.$witnessDb->pers_gedcomnumber.'@"'.$selected.'>'.
			$witnessDb->pers_lastname.', '.$witnessDb->pers_firstname.' '.strtolower(str_replace("_"," ",$witnessDb->pers_prefix)).' ['.$witnessDb->pers_gedcomnumber.']</option>'."\n";
	}
	echo '</select>';

	// *** Witness: text field ***
	$witness_value=$witness;
	if (substr($witness,0,1)=='@'){ $witness_value=''; }
	//echo ' <b>'.__('or').':</b> <input type="text" name="text_event" value="'.htmlspecialchars($witness_value).'" size="40">';
	echo ' <b>'.__('or').':</b> <input type="text" name="text_event'.$multiple_rows.'" value="'.htmlspecialchars($witness_value).'" size="40">';
}

function show_person($gedcomnumber, $gedcom_date=false, $show_link=true){
	global $dbh, $tree_prefix, $page, $joomlastring;
	if ($gedcomnumber){
		$person_qry=$dbh->query("SELECT * FROM ".$tree_prefix."person
			WHERE pers_gedcomnumber='$gedcomnumber'");
		$personDb=$person_qry->fetch(PDO::FETCH_OBJ);
		if ($show_link==true){
			$text='<a href="index.php?'.$joomlastring.'page='.$page.'&amp;tree='.$tree_prefix.
				'&amp;person='.$personDb->pers_gedcomnumber.'">'.$personDb->pers_firstname.' '.
				strtolower(str_replace("_"," ",$personDb->pers_prefix)).$personDb->pers_lastname.'</a>'."\n";
		}
		else{
			$text=$personDb->pers_firstname.' '.strtolower(str_replace("_"," ",$personDb->pers_prefix)).$personDb->pers_lastname."\n";
		}
	}
	else { $text='N.N.'; }

	if($gedcom_date==true){
		if ($personDb->pers_birth_date){
			$text.=' * '.date_place($personDb->pers_birth_date,'');
		}
		elseif($personDb->pers_bapt_date){
			$text.=' ~ '.date_place($personDb->pers_bapt_date,'');
		}
		elseif($personDb->pers_death_date){
			$text.=' &#134; '.date_place($personDb->pers_death_date,'');
		}
		elseif($personDb->pers_buried_date){
			$text.=' [] '.date_place($personDb->pers_buried_date,'');
		}
	}
	return $text;
}


// ***********************
// *** HuMo-gen Editor ***
// ***********************

$new_tree=false;

// *** Use sessions for some parameters ***
$menu_admin='person';
if (isset($_GET["menu_admin"])){
	$menu_admin=$_GET['menu_admin'];
	$_SESSION['admin_menu_admin']=$menu_admin;
}
if (isset($_SESSION['admin_menu_admin'])){ $menu_admin=$_SESSION['admin_menu_admin']; }

if (isset($_POST["tree_prefix"])){
	$tree_prefix=$_POST['tree_prefix'];
	$_SESSION['admin_tree_prefix']=$tree_prefix;
	unset ($pers_gedcomnumber);
	unset ($_SESSION['admin_pers_gedcomnumber']);

	// *** Select first person to show ***
	$new_nr_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_favorite LIKE '%_' ORDER BY pers_lastname, pers_firstname LIMIT 0,1";
	$new_nr_result = $dbh->query($new_nr_qry);
	@$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);	
	if (isset($new_nr->pers_gedcomnumber)){
		$pers_gedcomnumber=$new_nr->pers_gedcomnumber;
		$_SESSION['admin_pers_gedcomnumber']=$pers_gedcomnumber;
	}
	else{
		$new_nr_qry= "SELECT * FROM ".$tree_prefix."person LIMIT 0,1";
		$new_nr_result = $dbh->query($new_nr_qry);
		@$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
		if (isset($new_nr->pers_gedcomnumber)){
			$pers_gedcomnumber=$new_nr->pers_gedcomnumber;
			$_SESSION['admin_pers_gedcomnumber']=$pers_gedcomnumber;
		}
	}
}

// *** Editor icon for admin and editor: select family tree ***
if (isset($_GET["tree"])){
	$tree_prefix=$_GET['tree'];
	$_SESSION['admin_tree_prefix']=$tree_prefix;
}
if (isset($_SESSION['admin_tree_prefix'])){ $tree_prefix=$_SESSION['admin_tree_prefix']; }


// *** Delete session id's for new person ***
if (isset($_POST['person_add'])){
	unset($_SESSION['admin_pers_gedcomnumber']);
	unset($_SESSION['admin_fam_gedcomnumber']);
}

// *** Save person gedcomnumber ***
$pers_gedcomnumber='';
if (isset($_POST["person"]) AND $_POST["person"]){
	$pers_gedcomnumber=$_POST['person'];
	$_SESSION['admin_pers_gedcomnumber']=$pers_gedcomnumber;
}
if (isset($_GET["person"])){
	$pers_gedcomnumber=$_GET['person'];
	$_SESSION['admin_pers_gedcomnumber']=$pers_gedcomnumber;
}
if (isset($_SESSION['admin_pers_gedcomnumber'])){ $pers_gedcomnumber=$_SESSION['admin_pers_gedcomnumber']; }


// *** Save family gedcomnumber ***
if (isset($pers_gedcomnumber) AND $pers_gedcomnumber){
	$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$pers_gedcomnumber."'";
	$person_result = $dbh->query($person_qry);
	if ($person_result) $person=$person_result->fetch(PDO::FETCH_OBJ);
}
if (isset($person->pers_fams) AND $person->pers_fams){
	$fams1=explode(";",$person->pers_fams);
	$marriage=$fams1[0];
	$_SESSION['admin_fam_gedcomnumber']=$marriage;

	if (isset($_POST["marriage_nr"]) AND $_POST["marriage_nr"]){
		$marriage=$_POST['marriage_nr'];
		$_SESSION['admin_fam_gedcomnumber']=$marriage;
	}
	if (isset($_GET["marriage_nr"])){
		$marriage=$_GET['marriage_nr'];
		$_SESSION['admin_fam_gedcomnumber']=$marriage;
	}
	if (isset($_SESSION['admin_fam_gedcomnumber'])){ $marriage=$_SESSION['admin_fam_gedcomnumber']; }
}


// *** Check for new person ***
$add_person=false; if (isset($_GET['add_person'])){ $add_person=true; }

// *** Select family tree ***
$tree_prefix_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
$tree_prefix_result = $dbh->query($tree_prefix_sql);
echo __('Family tree').': ';
echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '<select size="1" name="tree_prefix" onChange="this.form.submit();">';
	echo '<option value="">'.__('Select a family tree:').'</option>';
	while ($tree_prefixDb=$tree_prefix_result->fetch(PDO::FETCH_OBJ)){
		$selected='';
		if (isset($tree_prefix)){
			if ($tree_prefixDb->tree_prefix==$tree_prefix){ $selected=' SELECTED'; }
		}
		$treetext=show_tree_text($tree_prefixDb->tree_prefix, $selected_language);
		echo '<option value="'.$tree_prefixDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
	}
echo '</select>';
echo '</form>';

if (isset($tree_prefix)){

	// *** Process queries ***
	include_once ($joomlapath."editor_inc.php");

	// *** New family tree: no default or selected pers_gedcomnumer, add new person ***
	if ($pers_gedcomnumber==''){
		$add_person=true; $_GET['add_person']='1';
		$new_tree=true;
	}

	// *** Select person ***
	$search_quicksearch='';
	$search_id='';
	if (isset($_POST["search_quicksearch"])){
		$search_quicksearch=safe_text($_POST['search_quicksearch']);
		$_SESSION['admin_search_quicksearch']=$search_quicksearch;
		$_SESSION['admin_search_id']='';
		$search_id='';
	}
	if (isset($_SESSION['admin_search_quicksearch'])){
		$search_quicksearch=$_SESSION['admin_search_quicksearch']; }

	if (isset($_POST["search_id"]) AND (!isset($_POST["search_quicksearch"]) OR $_POST["search_quicksearch"]=='')){
		// if both name and ID given go by name
		$search_id=safe_text($_POST['search_id']);
		$_SESSION['admin_search_id']=$search_id;
		$_SESSION['admin_search_quicksearch']='';
		$search_quicksearch='';
	}
	if (isset($_SESSION['admin_search_id']))
		$search_id=$_SESSION['admin_search_id'];

	if ($menu_admin=='person'){
		if ($new_tree==false){
			// *** Favorites ***
			echo '&nbsp;&nbsp;&nbsp; <img src="'.CMS_ROOTPATH.'images/favorite_blue.png"> ';
			echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_favorite LIKE '%_' ORDER BY pers_lastname, pers_firstname";
				$person_result = $dbh->query($person_qry);
				echo '<select size="1" name="person" onChange="this.form.submit();" style="width: 200px">';
				echo '<option value="">'.__('Favourites list:').'</option>';
				while ($person_fav=$person_result->fetch(PDO::FETCH_OBJ)){
					$selected='';
					if (isset($pers_gedcomnumber)){
						if ($person_fav->pers_gedcomnumber==$pers_gedcomnumber){ $selected=' SELECTED'; }
					}
					echo '<option value="'.$person_fav->pers_gedcomnumber.'"'.$selected.'>'.
						$editor_cls->show_selected_person($person_fav).'</option>';
				}
				echo '</select>';
			echo '</form>';
		}

		if (isset($pers_gedcomnumber)){
			echo '<span style="font-size:11px;">';
				echo '<br>'.__('Examples of date entries, using English month abbreviations: jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec or month numbers:').'<br>';
				echo '<b>'.__('13 oct 1813, 13-10-1813, 13/10/1813, between 1986 and 1987').', 13 oct 1100 BC.</b><br>';
				echo __('In all text fields it\'s possible to add a hidden text/ own remarks by using # characters. Example: #Check birthday.#');
			echo '</span>';
		}

		// *** Show delete message ***
		if ($confirm) echo $confirm;

		if ($new_tree==false){
		//echo '<br><table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';
		echo '<br><table class="humo" style="text-align:center; width:1100px; margin-left:50px;"><tr class="table_header_large"><td>';

			// *** Search persons firstname/ lastname ***
			echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				print __('Person').':';
				print ' <input class="fonts" type="text" name="search_quicksearch" placeholder="'.__('Name').'" value="'.$search_quicksearch.'" size="15"> ';
				print __('or ID:');
				print ' <input class="fonts" type="text" name="search_id" value="'.$search_id.'" size="8">';
				echo ' <input type="hidden" name="tree_prefix" value="'.$tree_prefix.'">';
				print ' <input class="fonts" type="submit" value="'.__('Search').'">';
			print "</form>\n";
			unset($person_result);

			$idsearch=false; // flag for search with ID;
			//if($search_lastname != ''  OR $search_firstname != '' ) {
			if($search_quicksearch != '') {
				// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
				$search_quicksearch=str_replace(' ', '%', $search_quicksearch);

				// *** In case someone entered "Mons, Huub" using a comma ***
				$search_quicksearch = str_replace(',','',$search_quicksearch);

				//$person_qry= "SELECT *, CONCAT(pers_firstname,pers_prefix,pers_lastname) as concat_name
				$person_qry= "SELECT *
				FROM ".$tree_prefix."person
				WHERE CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname)
					LIKE '%$search_quicksearch%'
					OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname)
					LIKE '%$search_quicksearch%' 
					OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' '))
					LIKE '%$search_quicksearch%' 
					OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname)
					LIKE '%$search_quicksearch%'
					ORDER BY pers_lastname, pers_firstname, CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED)";
					//ORDER BY pers_lastname, pers_firstname";
				$person_result = $dbh->query($person_qry);
			}
			elseif($search_id!='') {
				if(substr($search_id,0,1)!="i" AND substr($search_id,0,1)!="I") { $search_id = "I".$search_id; } //make entry "48" into "I48"
				$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$search_id."'";
				$person_result = $dbh->query($person_qry);
				$idsearch=true;
			}

			if (isset($person_result)){
				if($person_result->rowCount() ==0) echo __('Person not found');
				if($idsearch==true OR $person_result->rowCount()==0) { echo '<span style="display:none">';}
				echo '<b>'.__('Found:').'</b> ';
				echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				print '<select size="1" name="person" style="width: 200px">';
				$counter==0;
				while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
					$selected='';
					if (isset($pers_gedcomnumber)){
						if ($person->pers_gedcomnumber==$pers_gedcomnumber){ $selected=' SELECTED'; }
					}

					// *** Directly select first founded person! ***
					$counter++;
					if ($counter==1 AND isset($_POST["search_quicksearch"])){
						$pers_gedcomnumber=$person->pers_gedcomnumber;
						$_SESSION['admin_pers_gedcomnumber']=$pers_gedcomnumber;

						// *** Reset marriage number ***
						$fams1=explode(";",$person->pers_fams);
						$marriage=$fams1[0];
						$_SESSION['admin_fam_gedcomnumber']=$marriage;
					}
					echo '<option value="'.$person->pers_gedcomnumber.'"'.$selected.'>'.
						$editor_cls->show_selected_person($person).'</option>';
				}
				echo '</select>';
				echo ' <input type="Submit" name="submit" value="'.__('Select').'">';
				echo '</form>';
				if($idsearch==true OR $person_result->rowCount()==0) { echo '</span>'; }
			}

			// *** Add new person ***
			echo '&nbsp;&nbsp;&nbsp; <a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;add_person=1">
			<img src="'.CMS_ROOTPATH_ADMIN.'images/person_connect.gif" border="0" title="'.__('Add person').'" alt="'.__('Add person').'"> '.
			__('Add person').'</a>';

		echo '</td></tr></table>';
		} // *** end of check for new tree ***

	}
	else{
		echo '<br>';
	}

}


if (isset($pers_gedcomnumber)){


	// *** Tab menu ***
	$menu_tab='person';
	if (isset($_GET['menu_tab'])){
		$menu_tab=$_GET['menu_tab'];
		$_SESSION['admin_menu_tab']=$menu_tab;
	}
	if (isset($_SESSION['admin_menu_tab'])) $menu_tab=$_SESSION['admin_menu_tab'];
	if (isset($_GET['add_person'])) $menu_tab='person';

	if ($menu_admin=='person' AND isset($tree_prefix)){
		echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="margin-left:210px; width:970px;">';
		echo '<div class="pageHeading">';
			echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
				echo '<ul class="pageTabs">';
					//echo '<li class="pageTabItem"><div tabindex="0" class="pageTab pageTab-active">Details</div></li>';

					$select_item=''; if ($menu_tab=='person'){ $select_item=' pageTab-active'; }
					echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_tab=person">'.__('Person')."</a></div></li>";

					if (!isset($_GET['add_person'])){
						// *** Family tree data ***
						$select_item=''; if ($menu_tab=='marriage'){ $select_item=' pageTab-active'; }
						echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_tab=marriage">'.__('Marriage(s) and children');
						if (isset($marriage)) echo ' *';
						echo "</a></div></li>";
					}

				echo '</ul>';
			echo '</div>';
		echo '</div>';
		echo '</div>';

		// *** Align content to the left ***
		//echo '<div style="float: left; background-color:white; height:500px; padding:10px;">';
		echo '<div style="float: left; background-color:white; height:500px; margin-left:205px; padding-top:10px;">';
	}

	// *** Get person data to show name and calculate nr. of items ***
	$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$pers_gedcomnumber."'";
	$person_result = $dbh->query($person_qry);
	@$person=$person_result->fetch(PDO::FETCH_OBJ);


	// *** Align content to the left ***
//	echo '<div style="float: left;">';

	// *****************
	// *** Show data ***
	// *****************

	if ($add_person==true){
		$pers_gedcomnumber='';
		$pers_firstname=''; $pers_callname='';
		$pers_prefix=''; $pers_lastname=''; $pers_patronym='';
		$pers_name_text=''; $pers_name_source='';
		$pers_alive=''; $pers_sexe=''; $pers_own_code=''; $person_text='';
		$pers_favorite='';

		$pers_birth_date=''; $pers_birth_place=''; $pers_birth_time=''; $pers_stillborn=''; $pers_birth_text='';
		$pers_bapt_date=''; $pers_bapt_place=''; $pers_religion=''; $pers_bapt_text='';
		$pers_death_date=''; $pers_death_place=''; $pers_death_time=''; $pers_death_cause=''; $pers_death_text='';
		$pers_buried_date=''; $pers_buried_place=''; $pers_cremation=''; $pers_buried_text='';
		$pers_quality='';
	}
	else{
		$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$pers_gedcomnumber."'";
		$person_result = $dbh->query($person_qry);
		$person=$person_result->fetch(PDO::FETCH_OBJ);

		$pers_gedcomnumber=$person->pers_gedcomnumber;
		$pers_firstname=$person->pers_firstname; $pers_callname=$person->pers_callname;
		$pers_prefix=$person->pers_prefix; $pers_lastname=$person->pers_lastname; $pers_patronym=$person->pers_patronym;
		$pers_name_text=$person->pers_name_text; $pers_name_source=$person->pers_name_source;
		$pers_alive=$person->pers_alive; $pers_sexe=$person->pers_sexe;
		$pers_own_code=$person->pers_own_code; $person_text=$person->pers_text;
		$pers_favorite=@$person->pers_favorite;

		$pers_birth_date=$person->pers_birth_date; $pers_birth_place=$person->pers_birth_place;
		$pers_birth_time=$person->pers_birth_time; $pers_stillborn=$person->pers_stillborn;
		$pers_birth_text=$person->pers_birth_text;
		$pers_bapt_date=$person->pers_bapt_date; $pers_bapt_place=$person->pers_bapt_place;
		$pers_religion=$person->pers_religion; $pers_bapt_text=$person->pers_bapt_text;
		$pers_death_date=$person->pers_death_date; $pers_death_place=$person->pers_death_place;
		$pers_death_time=$person->pers_death_time; $pers_death_cause=$person->pers_death_cause;
		$pers_death_text=$person->pers_death_text;
		$pers_buried_date=$person->pers_buried_date; $pers_buried_place=$person->pers_buried_place;
		$pers_cremation=$person->pers_cremation; $pers_buried_text=$person->pers_buried_text;
		$pers_quality=$person->pers_quality;
	}

	// *** Text area size ***
	$field_date=15;
	$field_place=40;
	//$field_text='style="height: 40px; width:500px"';
	//$field_text='style="height: 18px; width:400px;"';
	$field_text='style="height: 18px; width:550px;"';
	$field_text_large='style="height: 200px; width:500px"';


	// *******************
	// *** Show person ***
	// *******************

	if ($menu_admin=='person'){

		// *** MARRIAGE sources ***
		if (isset($person->pers_fams) AND $person->pers_fams){
			$fams1=explode(";",$person->pers_fams);
			$marriage=$fams1[0];
			if (isset($_POST['marriage_nr'])){ $marriage=$_POST['marriage_nr']; }
			if (isset($_GET['marriage_nr'])){ $marriage=$_GET['marriage_nr']; }
		}

		// *** Add child to family, 1st option: select a child from a pull-down list ***
		if (isset($_GET['child_connect'])){

			if (isset($_GET['family_id'])){
				// *** Search for parents ***
				$family_parents=$dbh->query("SELECT * FROM ".$tree_prefix."family
					WHERE fam_gedcomnumber='".$_GET['family_id']."'");
				$family_parentsDb=$family_parents->fetch(PDO::FETCH_OBJ);

				echo '<br><br><b>'.__('Add child to family:').' ';
				//*** Father ***
				if ($family_parentsDb->fam_man) echo show_person($family_parentsDb->fam_man);
					else echo __('N.N.');

				echo ' '.__('and').' ';

				//*** Mother ***
				if ($family_parentsDb->fam_woman) echo show_person($family_parentsDb->fam_woman);
					else echo __('N.N.');
				echo '</b>';
			}

			echo '<div class="confirm">';

			// *** Search for an child in database ***
			echo '<form method="POST" action="'.$phpself.'?page=editor&family_id='.$_GET['family_id'];
				if (isset($_GET['children'])){ echo '&children='.$_GET['children']; }
				echo '&child_connect=1&add_person=1" style="display : inline;">';
				// *** Search persons firstname/ lastname ***
				$search_quicksearch_child='';
				if (isset($_POST['search_quicksearch_child'])){ $search_quicksearch_child=$_POST['search_quicksearch_child']; }
				print __('Child').':';
				print ' <input class="fonts" type="text" name="search_quicksearch_child" value="'.$search_quicksearch_child.'" size="25">';
				print ' <input class="fonts" type="submit" value="'.__('Search').'">';
			echo '</form>';

			echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			if (isset($_GET['children'])){
				echo '<input type="hidden" name="children" value="'.$_GET['children'].'">';
			}
			echo '<input type="hidden" name="family_id" value="'.$_GET['family_id'].'">';

			if($search_quicksearch_child != '') {
				// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
				$search_quicksearch_child=str_replace(' ', '%', $search_quicksearch_child);
				// *** In case someone entered "Mons, Huub" using a comma ***
				$search_quicksearch_child = str_replace(',','',$search_quicksearch_child);
				//$person_qry= "SELECT *, CONCAT(pers_firstname,pers_prefix,pers_lastname) as concat_name
				$person_qry= "SELECT *
				FROM ".$tree_prefix."person
				WHERE CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname)
					LIKE '%$search_quicksearch_child%'
					OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname)
					LIKE '%$search_quicksearch_child%' 
					OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' '))
					LIKE '%$search_quicksearch_child%' 
					OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname)
					LIKE '%$search_quicksearch_child%'
					ORDER BY pers_lastname, pers_firstname";
			}
			else{
				$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_famc='' ORDER BY pers_lastname, pers_firstname";
			}
			$person_result = $dbh->query($person_qry);
			//if (isset($_GET['child_connect'])){
				echo __('Select child').' ';
				print '<select size="1" name="child_connect2" style="width: 250px">';
			//}
			while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
				echo '<option value="'.$person->pers_gedcomnumber.'">'.
					$editor_cls->show_selected_person($person).'</option>';
			}
			echo '</select>';
			echo ' <input type="Submit" name="submit" value="'.__('Select').'">';
			echo '</form>';
			echo '</div>';
			echo '<p>'.__('Or add a new child:').'<br>';
		}

		// *** Script voor expand and collapse of items ***
		echo '
		<script type="text/javascript">
		function hideShow(el_id){
			// *** Hide or show item ***
			var arr = document.getElementsByName(\'row\'+el_id);
			for (i=0; i<arr.length; i++){
				if(arr[i].style.display!="none"){
					arr[i].style.display="none";
				}else{
					arr[i].style.display="";
				}
			}

			// *** Change [+] into [-] or reverse ***
			if (document.getElementById(\'hideshowlink\'+el_id).innerHTML == "[+]")
				document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[-]";
			else
				document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[+]";
		}

		function hideShowAll(){
			// *** PERSON: Change [+] into [-] or reverse ***
			if (document.getElementById(\'hideshowlinkall\').innerHTML == "[+]")
				document.getElementById(\'hideshowlinkall\').innerHTML = "[-]";
			else
				document.getElementById(\'hideshowlinkall\').innerHTML = "[+]";

			for (j=1; j<12; j++){
				// *** Hide or show item ***
				var arr = document.getElementsByName(\'row\'+j);
				for (i=0; i<arr.length; i++){
					if(arr[i].style.display!="none"){
						arr[i].style.display="none";
					}else{
						arr[i].style.display="";
					}
				}

				// *** Change [+] into [-] or reverse ***
				if (document.getElementById(\'hideshowlink\'+j).innerHTML == "[+]")
					document.getElementById(\'hideshowlink\'+j).innerHTML = "[-]";
				else
					document.getElementById(\'hideshowlink\'+j).innerHTML = "[+]";
			}

			// *** MARRIAGE: Change [+] into [-] or reverse ***
			//if (document.getElementById(\'hideshowlinkall2\').innerHTML == "[+]")
			//	document.getElementById(\'hideshowlinkall2\').innerHTML = "[-]";
			//else
			//	document.getElementById(\'hideshowlinkall2\').innerHTML = "[+]";
		}

		function hideShowAll2(){
			// *** MARRIAGE: Change [+] into [-] or reverse ***
			if (document.getElementById(\'hideshowlinkall2\').innerHTML == "[+]")
				document.getElementById(\'hideshowlinkall2\').innerHTML = "[-]";
			else
				document.getElementById(\'hideshowlinkall2\').innerHTML = "[+]";

			for (j=6; j<12; j++){
				// *** Hide or show item ***
				var arr = document.getElementsByName(\'row\'+j);
				for (i=0; i<arr.length; i++){
					if(arr[i].style.display!="none"){
						arr[i].style.display="none";
					}else{
						arr[i].style.display="";
					}
				}

				// *** Change [+] into [-] or reverse ***
				if (document.getElementById(\'hideshowlink\'+j).innerHTML == "[+]")
					document.getElementById(\'hideshowlink\'+j).innerHTML = "[-]";
				else
					document.getElementById(\'hideshowlink\'+j).innerHTML = "[+]";
			}

		}

		</script>';

		// *** Show box with list of parents, person, marriages etc. ***
		echo '<div style="position:absolute;
			top:170px; left:10px;
			padding:8px;
			background-color:#F8F8F8;
			border:solid 1px #999999;
			width:180px;
			font-size:9px;
			";>';
		if ($add_person==false){

			echo '<b>'.__('Parents').'</b><br>';
			if ($person->pers_famc){
				// *** Search for parents ***
				$family_parents=$dbh->query("SELECT * FROM ".$tree_prefix."family
					WHERE fam_gedcomnumber='$person->pers_famc'");
				$family_parentsDb=$family_parents->fetch(PDO::FETCH_OBJ);

				//*** Father ***
				if ($family_parentsDb->fam_man) echo show_person($family_parentsDb->fam_man).'<br>';
					else echo __('N.N.').'<br>';
				//echo ' '.__('and').' ';

				//*** Mother ***
				if ($family_parentsDb->fam_woman) echo show_person($family_parentsDb->fam_woman).'<br>';
					else echo __('N.N.').'<br>';
			}

			// *** Show person ***
			echo '<br><b>'.__('Person').'</b><br>';
			echo show_person($person->pers_gedcomnumber).'<br>';

			// *** Show marriages and children ***
			if ($person->pers_fams){
				// *** Search for own family ***
				$fams1=explode(";",$person->pers_fams);
				$fam_count=substr_count($person->pers_fams, ";");
				for ($i=0; $i<=$fam_count; $i++){
					$family=$dbh->query("SELECT * FROM ".$tree_prefix."family
						WHERE fam_gedcomnumber='".$fams1[$i]."'");
					$familyDb=$family->fetch(PDO::FETCH_OBJ);
					//$fam_count++;
					echo '<br><b>'.ucfirst(__('marriage/ relation')).' '.($i+1).'</b><br>';
					if ($person->pers_gedcomnumber==$familyDb->fam_man)
						echo show_person($familyDb->fam_woman).'<br>';
					else
						echo show_person($familyDb->fam_man).'<br>';

					if ($familyDb->fam_children){
						echo '<b>'.__('Children').'</b><br>';
						$fam_children_array=explode(";",$familyDb->fam_children);
						$child_count=substr_count($familyDb->fam_children, ";");
						for ($j=0; $j<=$child_count; $j++){
							echo show_person($fam_children_array[$j]).'<br>';
						}
					}

				}
			}
		}
		echo '</div>';


		// *** Start of editor table ***
		//echo '<br><table class="humo standard" border="1">';
		echo '<table class="humo" border="1">';
		echo '<form method="POST" action="'.$phpself.'" style="display : inline;" enctype="multipart/form-data">';
		echo '<input type="hidden" name="page" value="'.$page.'">';

		// *** Add child to family, 2nd option: add a new child ***
		if (isset($_GET['child_connect'])){
			echo '<input type="hidden" name="child_connect" value="'.$_GET['child_connect'].'">';
			if (isset($_GET['children'])){
				echo '<input type="hidden" name="children" value="'.$_GET['children'].'">';
			}
			echo '<input type="hidden" name="family_id" value="'.$_GET['family_id'].'">';
		}

		if ($menu_tab=='person'){

		// *** Show mother and father with a link ***
		if ($add_person==false){
			print '<tr><th class="table_header" colspan="4">'.ucfirst(__('parents')).'</tr>';

			echo '<tr><td>'.ucfirst(__('parents')).'</td><td colspan="3">';
			$parent_text='';

			if ($person->pers_famc){
				// *** Search for parents ***
				$family_parents=$dbh->query("SELECT * FROM ".$tree_prefix."family
					WHERE fam_gedcomnumber='$person->pers_famc'");
				$family_parentsDb=$family_parents->fetch(PDO::FETCH_OBJ);

				//*** Father ***
				if ($family_parentsDb->fam_man) $parent_text.=show_person($family_parentsDb->fam_man);
					else $parent_text=__('N.N.');
				$parent_text.=' '.__('and').' ';

				//*** Mother ***
				if ($family_parentsDb->fam_woman) $parent_text.=show_person($family_parentsDb->fam_woman);
					else $parent_text.=__('N.N.');
			}
			else{
				// *** Add existing or new parents ***
				echo '<b>'.__('There are no parents.').' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;add_parents=1">';
				echo __('Add new parents (N.N. & N.N.)').'</a></b> '.__('or select an existing family as parents.').'<br>';

				$search_quicksearch_parent='';
				if (isset($_POST['search_quicksearch_parent'])){ $search_quicksearch_parent=$_POST['search_quicksearch_parent']; }

				echo '<input class="fonts" type="text" name="search_quicksearch_parent" value="'.$search_quicksearch_parent.'" size="25">';
				echo ' <input class="fonts" type="submit" value="'.__('Search').'">';
				if($search_quicksearch_parent != '') {
				// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
					$search_quicksearch_parent=str_replace(' ', '%', $search_quicksearch_parent);
					// *** In case someone entered "Mons, Huub" using a comma ***
					$search_quicksearch_parent = str_replace(',','',$search_quicksearch_parent);

					// *** EXAMPLE ***
					//$qry = "(SELECT * FROM humo1_persoon ".$query.') ';
					//$qry.= " UNION (SELECT * FROM humo2_persoon ".$query.')';

					// *** Search for man ***
					$parents= "(SELECT * FROM ".$tree_prefix."family, ".$tree_prefix."person
						WHERE fam_man=pers_gedcomnumber
						AND (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname)
						LIKE '%$search_quicksearch_parent%'
						OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname)
						LIKE '%$search_quicksearch_parent%' 
						OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' '))
						LIKE '%$search_quicksearch_parent%' 
						OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname)
						LIKE '%$search_quicksearch_parent%'))";

					// *** Search for woman ***
					$parents.= " UNION (SELECT * FROM ".$tree_prefix."family, ".$tree_prefix."person
						WHERE fam_woman=pers_gedcomnumber
						AND (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname)
						LIKE '%$search_quicksearch_parent%'
						OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname)
						LIKE '%$search_quicksearch_parent%' 
						OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' '))
						LIKE '%$search_quicksearch_parent%' 
						OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname)
						LIKE '%$search_quicksearch_parent%')) ORDER BY fam_gedcomnumber";

					$parents_result = $dbh->query($parents);
				}
				else{
					$parents= "SELECT * FROM ".$tree_prefix."family ORDER BY fam_gedcomnumber LIMIT 0,100";
					$parents_result = $dbh->query($parents);
				}

				echo ' <select size="1" name="add_parents" style="width: 250px">';
				echo '<option value="">'.__('Select parents:').'</option>';
				while ($parentsDb=$parents_result->fetch(PDO::FETCH_OBJ)){
					$parent2_text='';
					//*** Father ***
					if ($parentsDb->fam_man) $parent2_text.=show_person($parentsDb->fam_man);
						else $parent2_text=__('N.N.');
					$parent2_text.=' '.__('and').' ';

					//*** Mother ***
					if ($parentsDb->fam_woman) $parent2_text.=show_person($parentsDb->fam_woman);
						else $parent2_text.=__('N.N.');

					echo '<option value="'.$parentsDb->fam_gedcomnumber.'">['.$parentsDb->fam_gedcomnumber.'] '.$parent2_text.'</option>';
				}
				if($search_quicksearch_parent == '')
					echo '<option value="">*** '.__('Results are limited, use search to find more parents.').' ***</option>';
				echo '</select>';
				echo ' <input type="Submit" name="submit" value="'.__('Select').'">';
			}
			echo $parent_text.'</td></tr>';

			// *** Empty line in table ***
			echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr>';
		}

	//echo '<tr class="table_header" style="background-image: url(\'../images/default_trans_large.png\');">';
	echo '<tr class="table_header_large">';

		// *** Hide or show all hide-show items ***
		$hide_show_all='<a href="#" onclick="hideShowAll();"><span id="hideshowlinkall">'.__('[+]').'</span> '.__('All').'</a> ';

		if ($add_person==false){
			echo '<td>'.$hide_show_all.' <input type="Submit" name="person_remove" value="'.__('Delete person').'"></td>';

			// *** Example of family screen in popup ***
			echo '<td style="border-right: none">'."<a href=\"#\" onClick=\"window.open('../family.php?database=".$tree_prefix."&id=".$person->pers_indexnr."&main_person=".$person->pers_gedcomnumber."', '','width=800,height=500')\"><b>*** ".__('Example').' ***</b></a></td>';
		}
		else{
			// *** New person: no delete example link ***
			echo '<td>'.$hide_show_all.'</td>';

			echo '<td style="border-right: none"><br></td>';
		}

		//echo '<th colspan="2">'.__('Person');
		echo '<th style="border-left: none">'.__('Person');

		if ($add_person==false){
			echo ': ['.$pers_gedcomnumber.'] '.show_person($person->pers_gedcomnumber,false,false);

			// *** Add person to admin favorite list ***
			$checked='';
			if ($pers_favorite=='1'){
				echo '<a href="'.$phpself.'?page=editor&pers_favorite=0"><img src="'.CMS_ROOTPATH.'images/favorite_blue.png" style="border: 0px"></a>';
			}
			else{
				echo '<a href="'.$phpself.'?page=editor&pers_favorite=1"><img src="'.CMS_ROOTPATH.'images/favorite.png" style="border: 0px"></a>';
			}

			echo '<br>';
		}
		echo '</th><td>';

		if ($add_person==false){
			echo '<input type="Submit" name="person_change" value="'.__('Save').'">';
		}
		else{
			echo '<input type="Submit" name="person_add" value="'.__('Add').'">';
		}
	echo '</td></tr>';

		// *** Name ***
		echo '<tr><td rowspan="3">';
		echo '<a href="#" onclick="hideShow(1);"><span id="hideshowlink1">'.__('[+]').'</span></a> ';
		echo __('Name').'</td>';

		echo '<td style="border-right:0px;"><b>'.__('firstname').'</b></td><td style="border-left:0px;"><input type="text" name="pers_firstname" value="'.$pers_firstname.'"  size="40"> '.strtolower(__('Nickname')).' <input type="text" name="pers_callname" value="'.$pers_callname.'" size="30">';

		echo '</td><td rowspan="3">';
		if (!isset($_GET['add_person'])){
			// *** Source by name ***
			// *** Calculate and show nr. of sources ***
			$connect_qry="SELECT * FROM ".$tree_prefix."connections
				WHERE connect_kind='person' AND connect_sub_kind='pers_name_source'
				AND connect_connect_id='".$pers_gedcomnumber."'";
			$connect_sql=$dbh->query($connect_qry);
			echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=pers_name_source', '','width=800,height=500')\">".__('source');
			echo ' ['.$connect_sql->rowCount().']</a>';
		}
		echo '</td></tr>';

		echo '<tr><td style="border-right:0px;">'.__('prefix').'</td><td style="border-left:0px;"><input type="text" name="pers_prefix" value="'.$pers_prefix.'" size="10">'.__("For example: d\' or:  van_ (use _ for a space)").'</td></tr>';

		echo '<tr><td style="border-right:0px;"><b>'.__('lastname').'</b></td><td style="border-left:0px;"><input type="text" name="pers_lastname" value="'.$pers_lastname.'" size="40"> ';
		echo __('patronymic').' <input type="text" name="pers_patronym" value="'.$pers_patronym.'" size="30"></td></tr>';

		// *** Person text by name ***
		echo '<tr style="display:none;" id="row1" name="row1">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="pers_name_text" '.$field_text.'>'.$editor_cls->text_show($pers_name_text).'</textarea></td>';
		echo '<td></td></tr>';

		if ($add_person==false){
			// *** Event name ***
			$event_cls->show_event('name');

			// *** Title of Nobility ***
			$event_cls->show_event('nobility');

			// *** Title ***
			$event_cls->show_event('title');

			// *** Lordship ***
			$event_cls->show_event('lordship');
		}

		// *** Alive ***

		// *** Disable radio boxes if person is deceased ***
		$disabled='';
		if ($pers_death_date OR $pers_death_place OR $pers_buried_date OR $pers_buried_place){ $disabled=' DISABLED'; }

		echo '<tr class="humo_color"><td>'.ucfirst(__('alive')).'</td><td style="border-right:0px;">'.__('For the privacy filter').'</td><td style="border-left:0px;">';
			$selected_alive='alive'; if ($pers_alive=='deceased'){ $selected_alive='deceased'; }

			$selected=''; if ($selected_alive=='alive'){ $selected=' CHECKED'; }
			echo ' <input type="radio" name="pers_alive" value="alive"'.$selected.$disabled.'> '.__('alive');

			$selected=''; if ($selected_alive=='deceased'){ $selected=' CHECKED'; }
			echo ' <input type="radio" name="pers_alive" value="deceased"'.$selected.$disabled.'> '.__('deceased');
		echo '</td><td></td></tr>';

		// *** Sexe ***
		$colour='';
		// *** If sexe = unknown then show a red line (new person = other colour). ***
		if ($pers_sexe==''){ $colour=' bgcolor="#FF0000"'; }
		if ($add_person==true AND $pers_sexe==''){ $colour=' bgcolor="#CCFFFF"'; }
		echo '<tr><td>'.__('Sexe').'</td><td style="border-right:0px;"></td><td'.$colour.' style="border-left:0px;">';
			$selected=''; if ($pers_sexe=='M'){ $selected=' CHECKED'; }
			echo '<input type="radio" name="pers_sexe" value="M"'.$selected.'> '.__('male');
			$selected=''; if ($pers_sexe=='F'){ $selected=' CHECKED'; }
			echo ' <input type="radio" name="pers_sexe" value="F"'.$selected.'> '.__('female');
			$selected=''; if ($pers_sexe==''){ $selected=' CHECKED'; }
			echo ' <input type="radio" name="pers_sexe" value=""'.$selected.'> ?';
		echo '</td><td>';

		if (!isset($_GET['add_person'])){
			// *** Calculate and show nr. of sources ***
			$connect_qry="SELECT * FROM ".$tree_prefix."connections
				WHERE connect_kind='person' AND connect_sub_kind='pers_sexe_source'
				AND connect_connect_id='".$pers_gedcomnumber."'";
			$connect_sql=$dbh->query($connect_qry);
			echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=pers_sexe_source', '','width=800,height=500')\">".__('source');
			echo ' ['.$connect_sql->rowCount().']</a>';
		}
		echo '</td></tr>';

		// *** Birth ***
		echo '<tr class="humo_color"><td><a href="#" onclick="hideShow(2);"><span id="hideshowlink2">'.__('[+]').'</span></a> ';
		echo ucfirst(__('born')).'</td>';

		echo '<td style="border-right:0px;">'.__('date').'</td>';
		echo '<td style="border-left:0px;">'.$editor_cls->date_show($pers_birth_date,'pers_birth_date').' '.__('place').' <input type="text" name="pers_birth_place" placeholder="'.__('place').'" value="'.htmlspecialchars($pers_birth_place).'" size="'.$field_place.'"></td>';

		// *** Source by birth ***
		echo '<td>';
		if (!isset($_GET['add_person'])){
			// *** Calculate and show nr. of sources ***
			$connect_qry="SELECT *
				FROM ".$tree_prefix."connections
				WHERE connect_kind='person' AND connect_sub_kind='pers_birth_source'
				AND connect_connect_id='".$pers_gedcomnumber."'";
			$connect_sql=$dbh->query($connect_qry);
			echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=pers_birth_source', '','width=800,height=500')\">".__('source');
			echo ' ['.$connect_sql->rowCount().']</a>';
		}

		echo '</td></tr>';

		echo '<tr class="humo_color" style="display:none;" id="row2" name="row2">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('birth time').'</td><td style="border-left:0px;"><input type="text" name="pers_birth_time" value="'.$pers_birth_time.'" size="'.$field_date.'">';
			// *** Stillborn child ***
			$check=''; if (isset($pers_stillborn) AND $pers_stillborn=='y'){ $check=' checked'; }
			print '<input type="checkbox" name="pers_stillborn" '.$check.'> '.__('stillborn child');
		echo '</td><td>';
		echo '</td></tr>';

		echo '<tr class="humo_color" style="display:none;" id="row2" name="row2">';
		echo '</td><td>';
		echo '<td style="border-right:0px;">'.__('text').'</td>';
		echo '<td style="border-left:0px;"><textarea rows="1" name="pers_birth_text" '.$field_text.'>'.
		$editor_cls->text_show($pers_birth_text).'</textarea></td>';
		echo '<td></td></tr>';

		// *** Birth declaration ***
		if ($add_person==false) $event_cls->show_event('birth_declaration');

		// *** Baptise ***
		echo '<tr>';
		echo '<td><a href="#" onclick="hideShow(3);"><span id="hideshowlink3">'.__('[+]').'</span></a> ';
		echo ucfirst(__('baptised')).'</td>';
		echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($pers_bapt_date,'pers_bapt_date').' '.__('place').'  <input type="text" name="pers_bapt_place" placeholder="'.__('place').'" value="'.htmlspecialchars($pers_bapt_place).'" size="'.$field_place.'"></td>';

		// *** Source by baptise ***
		echo '<td>';
		if (!isset($_GET['add_person'])){
			// *** Calculate and show nr. of sources ***
			$connect_qry="SELECT *
				FROM ".$tree_prefix."connections
				WHERE connect_kind='person' AND connect_sub_kind='pers_bapt_source'
				AND connect_connect_id='".$pers_gedcomnumber."'";
			$connect_sql=$dbh->query($connect_qry);
			echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=pers_bapt_source', '','width=800,height=500')\">".__('source');
			echo ' ['.$connect_sql->rowCount().']</a>';
		}
		echo '</td></tr>';

		echo '<tr style="display:none;" id="row3" name="row3">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('religion').'</td><td style="border-left:0px;"><input type="text" 
		name="pers_religion" value="'.htmlspecialchars($pers_religion).'" size="20"></td>';
		echo '<td></td>';
		echo '</tr>';

		echo '<tr style="display:none;" id="row3" name="row3">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('text').'</td>';
		echo '<td style="border-left:0px;"><textarea rows="1" name="pers_bapt_text" '.$field_text.'>'.
			$editor_cls->text_show($pers_bapt_text).'</textarea>';
		echo '<td></td>';
		echo '</td></tr>';

		// *** Baptism Witness ***
		if ($add_person==false) $event_cls->show_event('baptism_witness');

		// *** Death ***
		echo '<tr class="humo_color"><td>';
		echo '<a href="#" onclick="hideShow(4);"><span id="hideshowlink4">'.__('[+]').'</span></a> ';
		echo ucfirst(__('died')).'</td>';
		echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($pers_death_date,'pers_death_date').' '.__('place').'  <input type="text" name="pers_death_place" placeholder="'.__('place').'" value="'.htmlspecialchars($pers_death_place).'" size="'.$field_place.'">';

		// *** Source by death ***
		echo '</td><td>';
		if (!isset($_GET['add_person'])){
			// *** Calculate and show nr. of sources ***
			$connect_qry="SELECT *
				FROM ".$tree_prefix."connections
				WHERE connect_kind='person' AND connect_sub_kind='pers_death_source'
				AND connect_connect_id='".$pers_gedcomnumber."'";
			$connect_sql=$dbh->query($connect_qry);
			echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=pers_death_source', '','width=800,height=500')\">".__('source');
			echo ' ['.$connect_sql->rowCount().']</a>';
		}
		echo '</td></tr>';

		echo '<tr class="humo_color" style="display:none;" id="row4" name="row4">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('death time').'</td><td style="border-left:0px;"><input type="text" name="pers_death_time" value="'.$pers_death_time.'" size="'.$field_date.'"> ';

		echo __('cause').' ';
		$cause=false;
		echo '<select size="1" name="pers_death_cause">';
			echo '<option value=""></option>';

			$selected=''; if ($pers_death_cause=='murdered'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="murdered"'.$selected.'>'.__('murdered').'</option>';

			$selected=''; if ($pers_death_cause=='drowned'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="drowned"'.$selected.'>'.__('drowned').'</option>';

			$selected=''; if ($pers_death_cause=='perished'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="perished"'.$selected.'>'.__('perished').'</option>';

			$selected=''; if ($pers_death_cause=='killed in action'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="killed in action"'.$selected.'>'.__('killed in action').'</option>';

			$selected=''; if ($pers_death_cause=='being missed'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="being missed"'.$selected.'>'.__('being missed').'</option>';

			$selected=''; if ($pers_death_cause=='committed suicide'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="committed suicide"'.$selected.'>'.__('committed suicide').'</option>';

			$selected=''; if ($pers_death_cause=='executed'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="executed"'.$selected.'>'.__('executed').'</option>';

			$selected=''; if ($pers_death_cause=='died young'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="died young"'.$selected.'>'.__('died young').'</option>';

			$selected=''; if ($pers_death_cause=='died unmarried'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="died unmarried"'.$selected.'>'.__('died unmarried').'</option>';

			$selected=''; if ($pers_death_cause=='registration'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="registration"'.$selected.'>'.__('registration').'</option>';

			$selected=''; if ($pers_death_cause=='declared death'){ $cause=true; $selected=' SELECTED'; }
			echo '<option value="declared death"'.$selected.'>'.__('declared death').'</option>';

		echo '</select>';

			echo '<b>'.__('or').':</b>';
			$pers_death_cause2=''; if ($pers_death_cause AND $cause==false) $pers_death_cause2=$pers_death_cause;
			echo '<input type="text" name="pers_death_cause2" value="'.$pers_death_cause2.'" size="'.$field_date.'">';

		echo '</td><td></td>';
		echo '</tr>';

		echo '<tr class="humo_color" style="display:none;" id="row4" name="row4">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="pers_death_text" '.$field_text.'>'.$editor_cls->text_show($pers_death_text).'</textarea></td>';
		echo '<td></td>';
		echo '</tr>';

		// *** Death Declaration ***
		if ($add_person==false) $event_cls->show_event('death_declaration');

		// *** Burial ***
		echo '<tr>';
		echo '<td><a href="#" onclick="hideShow(5);"><span id="hideshowlink5">'.__('[+]').'</span></a> ';
		echo __('Buried').'</td>';
		echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($pers_buried_date,'pers_buried_date').' '.__('place').' <input type="text" name="pers_buried_place" placeholder="'.__('place').'" value="'.htmlspecialchars($pers_buried_place).'" size="'.$field_place.'">';

		// *** Source by burial ***
		echo '</td><td>';
		if (!isset($_GET['add_person'])){
			// *** Calculate and show nr. of sources ***
			$connect_qry="SELECT *
				FROM ".$tree_prefix."connections
				WHERE connect_kind='person' AND connect_sub_kind='pers_buried_source'
				AND connect_connect_id='".$pers_gedcomnumber."'";
			$connect_sql=$dbh->query($connect_qry);
			echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=pers_buried_source', '','width=800,height=500')\">".__('source');
			echo ' ['.$connect_sql->rowCount().']</a>';
		}
		echo '</td></tr>';

		echo '<tr style="display:none;" id="row5" name="row5">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('buried').'/ '.__('cremation').'</td><td style="border-left:0px;">';
			$selected=''; if ($pers_cremation==''){ $selected=' CHECKED'; }
			echo '<input type="radio" name="pers_cremation" value=""'.$selected.'> '.__('buried');
			$selected=''; if ($pers_cremation=='1'){ $selected=' CHECKED'; }
			echo ' <input type="radio" name="pers_cremation" value="1"'.$selected.'> '.__('cremation');
		echo '<td></td>';
		echo '</td></tr>';

		echo '<tr style="display:none;" id="row5" name="row5">';
		echo '<td></td>';
		echo '<td style="border-right:0px;">'.__('text').'</td>';
		echo '<td style="border-left:0px;"><textarea rows="1" name="pers_buried_text" '.$field_text.'>'.
			$editor_cls->text_show($pers_buried_text).'</textarea></td>';
		echo '<td></td>';
		echo '</tr>';

		// *** Burial Witness ***
		if ($add_person==false) $event_cls->show_event('burial_witness');

		// *** Own code ***
		echo '<tr class="humo_color"><td>'.ucfirst(__('own code')).'</td><td style="border-right:0px;"></td>';
		echo '<td style="border-left:0px;"><input type="text" name="pers_own_code" value="'.htmlspecialchars($pers_own_code).'" size="60"></td><td></td></tr>';

		if (!isset($_GET['add_person'])){

			// *** Profession(s) ***
			$event_cls->show_event('profession');

			// *** Show and edit places by person ***
			echo '<tr class="humo_color">';
			echo '<td style="border-right:0px;">';
				echo '<a name="places"></a>';

				$address_qry=$dbh->query("SELECT * FROM ".$tree_prefix."addresses
					WHERE address_person_id='".$pers_gedcomnumber."' ORDER BY address_order");
				$count=$address_qry->rowCount();
				if ($count>0)
				echo '<a href="#places" onclick="hideShow(54);"><span id="hideshowlink54">'.__('[+]').'</span></a> ';

				echo __('Places').'</td>';
			echo '<td style="border-right:0px;"></td>';
			echo '<td style="border-left:0px;">';
				echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;pers_place=1&amp;living_place_add=1#places">['.__('Add').']</a> ';
				$text='';
				$address_qry=$dbh->query("SELECT * FROM ".$tree_prefix."addresses
					WHERE address_person_id='".$pers_gedcomnumber."' ORDER BY address_order");
				while($addressDb=$address_qry->fetch(PDO::FETCH_OBJ)){
					if ($text) $text.=', ';
					$text.=htmlspecialchars($addressDb->address_place);
				}
				echo $text;
			echo '</td>';
			echo '<td></td>';
			echo '</tr>';

			$address_qry=$dbh->query("SELECT * FROM ".$tree_prefix."addresses
				WHERE address_person_id='".$pers_gedcomnumber."' ORDER BY address_order");
			$address_count=$address_qry->rowCount();
			$address_nr=0;
			while($addressDb=$address_qry->fetch(PDO::FETCH_OBJ)){
				$address_nr++;
				echo '<input type="hidden" name="person_address_id['.$addressDb->address_id.']" value="'.$addressDb->address_id.'">';

				echo '<tr class="humo_color" style="display:none;" id="row54" name="row54">';
				echo '<td style="border-right:0px;">&nbsp;&nbsp;&nbsp;';
				echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;pers_place=1&amp;living_place_drop='.
					$addressDb->address_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0"></a>';

					if ($address_nr < $address_count){
						echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;pers_place=1&amp;living_place_down='.$addressDb->address_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0"></a>';
					}
					else{
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					if ($address_nr > 1){
						echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;pers_place=1&amp;living_place_up='.$addressDb->address_order.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0"></a>';
					}
					else{
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}
				echo '</td>';
				echo '<td style="border-right:0px;">'.__('date').'</td>';
				echo '<td style="border-left:0px;">';
					echo $editor_cls->date_show($addressDb->address_date,'address_date',"[$addressDb->address_id]").' '.__('place').' <input type="text" name="address_place['.$addressDb->address_id.']" placeholder="'.__('place').'" value="'.$addressDb->address_place.'" size="'.$field_date.'">';
				echo '</td>';
				echo '<td></td>';
				echo '</tr>';
			}


			// *** Show and edit addresses by person ***
			// *** Also include sources script for queries to save, edit and remove addresses in connect table ***
			include ('editor_sources.php');

			echo '<tr>';
			echo '<td style="border-right:0px;">';
				echo '<a name="addresses"></a>';

				$connect_sql="SELECT * FROM ".$tree_prefix."connections
					WHERE connect_kind='person' AND connect_sub_kind='person_address'
					AND connect_connect_id='".safe_text($pers_gedcomnumber)."'";
				$connect_qry=$dbh->query($connect_sql);
				$count=$connect_qry->rowCount();
				if ($count>0)
				echo '<a href="#addresses" onclick="hideShow(55);"><span id="hideshowlink55">'.__('[+]').'</span></a> ';

				echo __('Adresses').'</td>';
			echo '<td style="border-right:0px;"></td>';
			echo '<td style="border-left:0px;">';
				echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;person_place_address=1&amp;address_add=1#addresses">['.__('Add').']</a> ';
				$text='';
				// *** Search for all connected sources ***
				$connect_qry="SELECT * FROM ".$tree_prefix."connections, ".$tree_prefix."addresses
					WHERE connect_kind='person'
					AND connect_sub_kind='person_address'
					AND connect_item_id=address_gedcomnr
					AND connect_connect_id='".safe_text($pers_gedcomnumber)."'
					ORDER BY connect_order";
				$connect_sql=$dbh->query($connect_qry);
				while($connectDb=$connect_sql->fetch(PDO::FETCH_OBJ)){
					if ($text) $text.=', ';
					$text.=@$connectDb->address_place;
				}
				echo $text;
				echo ' '.__('(extended address by a person)');
			echo '</td>';
			echo '<td></td>';
			echo '</tr>';

			$connect_qry=$dbh->query("SELECT * FROM ".$tree_prefix."connections
				WHERE connect_kind='person'
				AND connect_sub_kind='person_address'
				AND connect_connect_id='".safe_text($pers_gedcomnumber)."'
				ORDER BY connect_order");
			$count=$connect_qry->rowCount();
			$address_nr=0;
			while($addressDb=$connect_qry->fetch(PDO::FETCH_OBJ)){
				$text='';
				$address_nr++;
				$key=$addressDb->connect_id;
				echo '<input type="hidden" name="connect_change['.$key.']" value="'.$addressDb->connect_id.'">';
				echo '<input type="hidden" name="connect_connect_id['.$key.']" value="'.$addressDb->connect_connect_id.'">';
				echo '<input type="hidden" name="connect_kind['.$key.']" value="person">';
				echo '<input type="hidden" name="connect_sub_kind['.$key.']" value="person_address">';
				echo '<input type="hidden" name="connect_page['.$key.']" value="">';
				echo '<input type="hidden" name="connect_place['.$key.']" value="">';

				echo '<tr style="display:none;" id="row55" name="row55">';
				echo '<td style="border-right:0px;">&nbsp;&nbsp;&nbsp;';

					$text.=' <a href="index.php?'.$joomlastring.'page='.$page.
						'&amp;person_place_address=1&amp;connect_drop='.$addressDb->connect_id.
						'&amp;connect_kind='.$addressDb->connect_kind.
						'&amp;connect_sub_kind='.$addressDb->connect_sub_kind.
						'&amp;connect_connect_id='.$addressDb->connect_connect_id;
						$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="down"></a>';

					if ($addressDb->connect_order<$count){
						$text.= ' <a href="index.php?'.$joomlastring.'page='.$page.
						'&amp;person_place_address=1&amp;connect_down='.$addressDb->connect_id.
						'&amp;connect_kind='.$addressDb->connect_kind.
						'&amp;connect_sub_kind='.$addressDb->connect_sub_kind.
						'&amp;connect_connect_id='.$addressDb->connect_connect_id.
						'&amp;connect_order='.$addressDb->connect_order;
						$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>';
					}
					else{
						$text.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}

					if ($addressDb->connect_order>1){
						$text.= ' <a href="index.php?'.$joomlastring.'page='.$page.
						'&amp;person_place_address=1&amp;connect_up='.$addressDb->connect_id.
						'&amp;connect_kind='.$addressDb->connect_kind.
						'&amp;connect_sub_kind='.$addressDb->connect_sub_kind.
						'&amp;connect_connect_id='.$addressDb->connect_connect_id.
						'&amp;connect_order='.$addressDb->connect_order;
						$text.='"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="down"></a>';
					}
					else{
						$text.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					echo $text;

				echo '</td>';
				echo '<td style="border-right:0px;">'.__('date').'</td>';
				echo '<td style="border-left:0px;">';
					echo $editor_cls->date_show($addressDb->connect_date,'connect_date',"[$addressDb->connect_id]").' '.__('Address').' ';

					// *** Source ***
					// NO SOURCE YET
					echo '<input type="hidden" name="connect_source_id['.$key.']" value="">';
					echo '<input type="hidden" name="connect_text['.$key.']" value="">';

					// *** Only show addresses if a gedcomnumber is used (= link to full adres) ***
					$addressqry=$dbh->query("SELECT * FROM ".$tree_prefix."addresses WHERE address_gedcomnr LIKE '_%'
						ORDER BY address_place, address_address");
					echo '<select size="1" name="connect_item_id['.$key.']" style="width: 300px">';
					echo '<option value="">'.__('Select address').'</option>';
					while ($address2Db=$addressqry->fetch(PDO::FETCH_OBJ)){
						$selected='';
						if ($addressDb->connect_item_id==$address2Db->address_gedcomnr){ $selected=' SELECTED'; }
						echo '<option value="'.$address2Db->address_gedcomnr.'"'.$selected.'>'.
							@$address2Db->address_place.', '.$address2Db->address_address.' ['.@$address2Db->address_gedcomnr.']</option>';
					}
					echo '</select>';

				echo '</td>';
				echo '<td></td>';
				echo '</tr>';

				echo '<tr style="display:none;" id="row55" name="row55">';
				echo '<td></td>';
				echo '<td style="border-right:0px;">'.__('Addressrole').'</td>';
				echo '<td style="border-left:0px;">';
					echo ' <input type="text" name="connect_role['.$key.']" value="'.htmlspecialchars($addressDb->connect_role).'" size="6">';
				echo '</td>';
				echo '<td></td>';
				echo '</tr>';
			}


			// *** Show places or addresses if save or arrow links are used ***
			if (isset($_GET['pers_place']) OR isset($_GET['person_place_address'])){
				// *** Script voor expand and collapse of items ***
				if (isset($_GET['pers_place'])) $link_id='54';
				if (isset($_GET['person_place_address'])) $link_id='55';
				echo '
				<script type="text/javascript">
				function Show(el_id){
					// *** Hide or show item ***
					var arr = document.getElementsByName(\'row\'+el_id);
					for (i=0; i<arr.length; i++){
						arr[i].style.display="";
					}
					// *** Change [+] into [-] ***
					document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[-]";
				}
				</script>';

				echo '<script>
					Show("'.$link_id.'");
				</script>';
			}

		} // *** End of check for new person ***


		// *** General text by person ***
		echo '<tr class="humo_color"><td>'.__('General text for person').'</td>';
		echo '<td style="border-right:0px;"></td>';
		echo '<td style="border-left:0px;"><textarea rows="1" name="person_text"'.$field_text_large.'>'.$editor_cls->text_show($person_text).'</textarea>';
		echo '</td><td>';
		// *** Source by text ***
		if (!isset($_GET['add_person'])){
			// *** Calculate and show nr. of sources ***
			$connect_qry="SELECT *
				FROM ".$tree_prefix."connections
				WHERE connect_kind='person' AND connect_sub_kind='pers_text_source'
				AND connect_connect_id='".$pers_gedcomnumber."'";
			$connect_sql=$dbh->query($connect_qry);
			echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=pers_text_source', '','width=800,height=500')\">".__('source');
			echo ' ['.$connect_sql->rowCount().']</a>';
		}
		echo '</td></tr>';

		if (!isset($_GET['add_person'])){

			// *** Person sources in new person editor screen ***
			echo '<tr><td>'.__('General source for person').'</td><td colspan="2">';
			echo '</td><td>';
				// *** Calculate and show nr. of sources ***
				$connect_qry="SELECT *
					FROM ".$tree_prefix."connections
					WHERE connect_kind='person' AND connect_sub_kind='person_source'
					AND connect_connect_id='".$pers_gedcomnumber."'";
				$connect_sql=$dbh->query($connect_qry);
				echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=person_source', '','width=800,height=500')\">".__('source');
				echo ' ['.$connect_sql->rowCount().']</a>';
			echo '</td></tr>';

			// *** Picture ***
			$event_cls->show_event('picture');

			// *** Person event editor ***
			$event_cls->show_event('person');

			// *** Quality ***
			// Disabled quality by person. Quality officially belongs to a source...
			/*
			echo '<tr><td>'.__('Quality of data').'</td>';
			echo '<td style="border-right:0px;"></td>';
				echo '<td style="border-left:0px;"><select size="1" name="pers_quality" style="width: 400px">';
				echo '<option value="">'.ucfirst(__('quality: default')).'</option>';
				$selected=''; if ($pers_quality=='0'){ $selected=' SELECTED'; }
				echo '<option value="0"'.$selected.'>'.ucfirst(__('quality: unreliable evidence or estimated data')).'</option>';
				$selected=''; if ($pers_quality=='1'){ $selected=' SELECTED'; }
				echo '<option value="1"'.$selected.'>'.ucfirst(__('quality: questionable reliability of evidence')).'</option>';
				$selected=''; if ($pers_quality=='2'){ $selected=' SELECTED'; }
				echo '<option value="2"'.$selected.'>'.ucfirst(__('quality: data from secondary evidence')).'</option>';
				$selected=''; if ($pers_quality=='3'){ $selected=' SELECTED'; }
				echo '<option value="3"'.$selected.'>'.ucfirst(__('quality: data from direct source')).'</option>';
				echo '</select></td>';
			echo '<td></td>';
			echo '</tr>';
			*/

			// *** End of person form ***
			echo '</form>';


			// *** Show unprocessed gedcom tags ***
			if (isset($person->pers_unprocessed_tags)){
				$tags_array=explode('<br>',$person->pers_unprocessed_tags);
				echo '<tr class="humo_tags_pers humo_color"><td>';
				//echo '<tr class="humo_tags_pers"><td>';

				echo '<a href="#humo_tags_pers" onclick="hideShow(61);"><span id="hideshowlink61">'.__('[+]').'</span></a> ';

				echo __('Gedcom tags').'</td><td colspan="2">';
				if ($person->pers_unprocessed_tags){
					printf(__('There are %d unprocessed gedcom tags.'), count ($tags_array));
				}
				else{
					printf(__('There are %d unprocessed gedcom tags.'), 0);
				}
				echo '</td><td></td></tr>';
				echo '<tr style="display:none;" id="row61" name="row61"><td></td>';
					echo '<td colspan="2">'.$person->pers_unprocessed_tags.'</td>';
				echo '<td></td></tr>';
			}

			// *** NEW: show user added notes ***
			$note_qry= "SELECT * FROM humo_user_notes
				WHERE note_tree_prefix='".$tree_prefix."'
				AND note_pers_gedcomnumber='".$pers_gedcomnumber."'";
			$note_result = $dbh->query($note_qry);
			$num_rows = $note_result->rowCount();

			//echo '<tr class="humo_user_notes humo_color"><td>';
			echo '<tr class="humo_user_notes"><td>';
				if ($num_rows)
					echo '<a href="#humo_user_notes" onclick="hideShow(62);"><span id="hideshowlink62">'.__('[+]').'</span></a> ';
				echo __('User notes').'</td><td colspan="2">';
				if ($num_rows)
					printf(__('There are %d user added notes.'), $num_rows);
				else
					printf(__('There are %d user added notes.'), 0);
			echo '</td><td></td></tr>';

			while($noteDb=$note_result->fetch(PDO::FETCH_OBJ)){
				$user_qry = "SELECT * FROM humo_users
					WHERE user_id='".$noteDb->note_user_id."'";
				$user_result = $dbh->query($user_qry);
				$userDb=$user_result->fetch(PDO::FETCH_OBJ);

				echo '<tr class="humo_color" style="display:none;" id="row62" name="row62"><td></td>';
					echo '<td colspan="2">';
					echo '<b>'.$noteDb->note_date.' '.$noteDb->note_time.' '.$userDb->user_name.'</b><br>';
					echo '<b>'.$noteDb->note_names.'</b><br>';
					echo nl2br($noteDb->note_note);
					echo '</td>';
				echo '<td></td></tr>';
			}
		}

		} // *** end of menu_tab ***
		if ($menu_tab=='marriage'){

		// ***********************************
		// *** Marriages and children list ***
		// ***********************************

		if (!isset($_GET['add_person'])){
			// *** Empty line in table ***
//			echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr>';

			echo '<tr><th class="table_header" colspan="4">'.__('Marriage(s) and children').'</tr>';
		}

		if ($add_person==false){
			if ($person->pers_fams){
				// *** Search for own family ***
				$fams1=explode(";",$person->pers_fams);
				$fam_count=substr_count($person->pers_fams, ";");
				for ($i=0; $i<=$fam_count; $i++){
					$family=$dbh->query("SELECT * FROM ".$tree_prefix."family
						WHERE fam_gedcomnumber='".$fams1[$i]."'");
					$familyDb=$family->fetch(PDO::FETCH_OBJ);

					echo '<tr><td>';
						if ($fam_count>0){
							//echo '<form method="POST" action="'.$phpself.'#marriage">';
							echo '<form method="POST" action="'.$phpself.'">';
							echo '<input type="hidden" name="page" value="'.$page.'">';
							echo '<input type="hidden" name="marriage_nr" value="'.$familyDb->fam_gedcomnumber.'">';
							echo ' <input type="Submit" name="submit" value="'.__('Select marriage').' '.($i+1).'">';
							echo '</form>';
						}
						else{
							echo ucfirst(__('marriage')).' '.($i+1);
						}
					echo '</td><td valign="top">';

					if ($i<$fam_count){
						echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;person_id='.$person->pers_id.'&amp;fam_down='.$i.'&amp;fam_array='.$person->pers_fams.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="fam_down"></a> ';
					}
					else{
						echo '&nbsp;&nbsp;&nbsp;';
					}
					if ($i>0){
						echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;person_id='.$person->pers_id.'&amp;fam_up='.$i.'&amp;fam_array='.$person->pers_fams.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="fam_up"></a> ';
					}
					else{
						//echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					}

					echo '</td><td colspan="2"><b>';
					echo show_person($familyDb->fam_man).' '.__('and').' '.show_person($familyDb->fam_woman);
					echo '</b>';

					if ($familyDb->fam_marr_date){ echo ' X '.date_place($familyDb->fam_marr_date,''); }
					echo '<br>';

					if ($familyDb->fam_children){

						echo __('Children').':<br>';
						$fam_children_array=explode(";",$familyDb->fam_children);
						$child_count=substr_count($familyDb->fam_children, ";");
						for ($j=0; $j<=$child_count; $j++){
							// *** Create new children variabele, for disconnect child ***
							$fam_children='';
							for ($k=0; $k<=substr_count($familyDb->fam_children, ";"); $k++){
								if ($k!=$j){ $fam_children.=$fam_children_array[$k].';'; }
							}
							$fam_children=substr($fam_children,0,-1); // *** strip last ; character ***
							echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;family_id='.$familyDb->fam_id.'&amp;child_disconnect='.$fam_children.
								'&amp;child_disconnect_gedcom='.$fam_children_array[$j].'">
								<img src="'.CMS_ROOTPATH_ADMIN.'images/person_disconnect.gif" border="0" title="'.__('Disconnect child').'" alt="'.__('Disconnect child').'"></a>';

								if ($j<$child_count){
								echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;family_id='.$familyDb->fam_id.'&amp;child_down='.$j.'&amp;child_array='.
									$familyDb->fam_children.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="child_down"></a>';
							}
							else{ echo '<span style="margin-left:21px;"></span>'; }

							if ($j>0){
								echo ' <a href="index.php?'.$joomlastring.'page='.$page.'&amp;family_id='.$familyDb->fam_id.'&amp;child_up='.$j.'&amp;child_array='.
									$familyDb->fam_children.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="child_up"></a> ';
							}
							else{ echo '<span style="margin-left:26px;"></span>'; }

//							if ($j<9){ echo '0'; }
							if ($j<9){ echo '<span style="margin-left:8px;"></span>'; }
							echo ($j+1).'. '.show_person($fam_children_array[$j],true).'<br>';
						}
					}

					echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;family_id='.$familyDb->fam_gedcomnumber;
					if ($familyDb->fam_children){ echo '&amp;children='.$familyDb->fam_children; }
					echo '&amp;child_connect=1&amp;add_person=1"><img src="'.CMS_ROOTPATH_ADMIN.'images/person_connect.gif" border="0" title="'.__('Connect child').'" alt="'.__('Connect child').'"><span style="margin-left:73px;">'.__('Add child').'</span></a><br>';

					echo '</td></tr>';
				}
			}

			// *** Add new marriage ***
			echo '<tr><td>'.__('Add relation').'</td>';
			echo '<td>';
			echo '</td><td colspan="2">';
				echo '<form method="POST" action="'.$phpself.'#marriage">';
				echo '<input type="hidden" name="page" value="'.$page.'">';

				echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_admin=person&amp;relation_add=1#marriage"><b>';
				echo __('Add relation with new partner (N.N.)').'</b></a> '.__('or add relation with existing person as partner.').'<br>';

				$search_quicksearch_partner='';
				if (isset($_POST['search_quicksearch_partner'])){ $search_quicksearch_partner=$_POST['search_quicksearch_partner']; }
				echo ' <input class="fonts" type="text" name="search_quicksearch_partner" placeholder="'.__('Name').'" value="'.$search_quicksearch_partner.'" size="15">';

				$search_partner_id='';
				if (isset($_POST['search_partner_id'])) $search_partner_id=safe_text($_POST['search_partner_id']);
				echo __('or ID:').' <input class="fonts" type="text" name="search_partner_id" value="'.$search_partner_id.'" size="5">';

				echo ' <input class="fonts" type="submit" name="submit" value="'.__('Search').'">';
				if($search_quicksearch_partner != '') {
					// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
					$search_quicksearch_partner=str_replace(' ', '%', $search_quicksearch_partner);
					// *** In case someone entered "Mons, Huub" using a comma ***
					$search_quicksearch_partner = str_replace(',','',$search_quicksearch_partner);
					$person_qry= "SELECT *
					FROM ".$tree_prefix."person
					WHERE CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname)
						LIKE '%$search_quicksearch_partner%'
						OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname)
						LIKE '%$search_quicksearch_partner%' 
						OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' '))
						LIKE '%$search_quicksearch_partner%' 
						OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname)
						LIKE '%$search_quicksearch_partner%'
						ORDER BY pers_lastname, pers_firstname";
				}
				elseif($search_partner_id!='') {
					if(substr($search_partner_id,0,1)!="i" AND substr($search_partner_id,0,1)!="I") { $search_partner_id = "I".$search_partner_id; } //make entry "48" into "I48"
					$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$search_partner_id."'";
				}
				else{
					$person_qry= "SELECT * FROM ".$tree_prefix."person LIMIT 0,100";
				}
				$person_result = $dbh->query($person_qry);

				echo '<select size="1" name="relation_add" style="width: 200px">';
				if($search_partner_id=='') echo '<option value="">'.__('Select person').':</option>';

				while ($partner=$person_result->fetch(PDO::FETCH_OBJ)){
					echo '<option value="'.$partner->pers_gedcomnumber.'">'.
						$editor_cls->show_selected_person($partner).'</option>';
				}
				if($search_quicksearch_partner=='' AND $search_partner_id=='')
					echo '<option value="">*** '.__('Results are limited, use search to find more persons.').' ***</option>';
				echo '</select>';

				echo ' <input type="Submit" name="submit" value="'.__('Select').'">';
				echo '</form>';

			echo '</td></tr>';

		// ***********************
		// *** Marriage editor ***
		// ***********************

		// *** Select marriage ***
		if ($person->pers_fams){

			$family=$dbh->query("SELECT * FROM ".$tree_prefix."family
				WHERE fam_gedcomnumber='".$marriage."'");
			$familyDb=$family->fetch(PDO::FETCH_OBJ);

			$fam_kind=$familyDb->fam_kind;
			$man_gedcomnumber=$familyDb->fam_man; $woman_gedcomnumber=$familyDb->fam_woman;
			$fam_gedcomnumber=$familyDb->fam_gedcomnumber;
			$fam_relation_date=$familyDb->fam_relation_date; $fam_relation_end_date=$familyDb->fam_relation_end_date;
			$fam_relation_place=$familyDb->fam_relation_place; $fam_relation_source=$familyDb->fam_relation_source; $fam_relation_text=$editor_cls->text_show($familyDb->fam_relation_text);
			$fam_marr_notice_date=$familyDb->fam_marr_notice_date; $fam_marr_notice_place=$familyDb->fam_marr_notice_place; $fam_marr_notice_source=$familyDb->fam_marr_notice_source;
			$fam_marr_notice_text=$editor_cls->text_show($familyDb->fam_marr_notice_text);
			$fam_marr_date=$familyDb->fam_marr_date; $fam_marr_place=$familyDb->fam_marr_place; $fam_marr_source=$familyDb->fam_marr_source;
			$fam_marr_text=$editor_cls->text_show($familyDb->fam_marr_text); $fam_marr_authority=$editor_cls->text_show($familyDb->fam_marr_authority);
			$fam_marr_church_notice_date=$familyDb->fam_marr_church_notice_date; $fam_marr_church_notice_place=$familyDb->fam_marr_church_notice_place;
			$fam_marr_church_notice_source=$familyDb->fam_marr_church_notice_source; $fam_marr_church_notice_text=$editor_cls->text_show($familyDb->fam_marr_church_notice_text);
			$fam_marr_church_date=$familyDb->fam_marr_church_date; $fam_marr_church_place=$familyDb->fam_marr_church_place; $fam_marr_church_source=$familyDb->fam_marr_church_source;
			$fam_marr_church_text=$editor_cls->text_show($familyDb->fam_marr_church_text);
			$fam_religion=$familyDb->fam_religion;
			$fam_div_date=$familyDb->fam_div_date; $fam_div_place=$familyDb->fam_div_place;
			$fam_div_source=$familyDb->fam_div_source; $fam_div_text=$editor_cls->text_show($familyDb->fam_div_text);
			$fam_div_authority=$editor_cls->text_show($familyDb->fam_div_authority);
			// *** Checkbox for no data by divorce ***
			$fam_div_no_data=false; if ($fam_div_date OR $fam_div_place OR $fam_div_text) $fam_div_no_data=true;
			$fam_text=$editor_cls->text_show($familyDb->fam_text);

			// *** Show delete message ***
			if ($confirm_relation){
				echo '<tr><td colspan="4" class="table_empty_line" style="border: solid 1px white;"><br>'.$confirm_relation.'</td><tr>';
			}

			echo '<form method="POST" action="'.$phpself.'#marriage">';
			echo '<input type="hidden" name="page" value="'.$page.'">';

			// *** Empty line in table ***
			echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr>';

			//echo '<tr class="table_header" style="background-image: url(\'../images/default_trans_large.png\');">';
			echo '<tr class="table_header_large">';

			// *** Hide or show all hide-show items ***
			//$hide_show_all='<a href="#marriage" onclick="hideShowAll();"><span id="hideshowlinkall2">'.__('[+]').'</span> '.__('All').'</a> ';
			$hide_show_all='<a href="#marriage" onclick="hideShowAll2();"><span id="hideshowlinkall2">'.__('[+]').'</span> '.__('All').'</a> ';

			// *** Remove marriage ***
			if (isset($marriage)){
				echo '<td>'.$hide_show_all.'<a name="marriage"></a><input type="Submit" name="fam_remove" value="'.__('Delete relation').'"></td>';
			}
			else{
				echo '<td>'.$hide_show_all.'<a name="marriage"></a><br></td>';
			}

			echo '<th colspan="2">'.__('Edit marriage');
				echo ': ['.$fam_gedcomnumber.'] '.show_person($man_gedcomnumber).' '.__('and').' '.show_person($woman_gedcomnumber).'<br>';
			echo '<td>';
				echo '<input type="Submit" name="marriage_change" value="'.__('Save').'">';
			echo '</td></tr>';

			if (isset($marriage)){
				echo '<input type="hidden" name="marriage_nr" value="'.$marriage.'">';
			}

			echo '<tr><td>'.__('Marriage').'</td>';
			echo '<td style="border-right:0px;"></td>';
			echo '<td style="border-left:0px;">';

			$search_quicksearch_man='';
			if (isset($_POST['search_quicksearch_man'])){ $search_quicksearch_man=$_POST['search_quicksearch_man']; }
			print ' <input class="fonts" type="text" name="search_quicksearch_man" placeholder="'.__('Name').'" value="'.$search_quicksearch_man.'" size="15">';

			$search_man_id='';
			if (isset($_POST['search_man_id'])) $search_man_id=safe_text($_POST['search_man_id']);
			echo __('or ID:').' <input class="fonts" type="text" name="search_man_id" value="'.$search_man_id.'" size="5">';

			echo ' <input class="fonts" type="submit" name="submit" value="'.__('Search').'">';

			// *** Use old value to detect change of man in marriage ***
			echo '<input type="hidden" name="connect_man_old" value="'.$man_gedcomnumber.'">';

			if($search_quicksearch_man != '') {
				// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
				$search_quicksearch_man=str_replace(' ', '%', $search_quicksearch_man);
				// *** In case someone entered "Mons, Huub" using a comma ***
				$search_quicksearch_man = str_replace(',','',$search_quicksearch_man);
				//$person_qry= "SELECT *, CONCAT(pers_firstname,pers_prefix,pers_lastname) as concat_name
				$person_qry= "SELECT *
				FROM ".$tree_prefix."person
				WHERE CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname)
					LIKE '%$search_quicksearch_man%'
					OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname)
					LIKE '%$search_quicksearch_man%' 
					OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' '))
					LIKE '%$search_quicksearch_man%' 
					OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname)
					LIKE '%$search_quicksearch_man%'
					ORDER BY pers_lastname, pers_firstname";
			}
			elseif($search_man_id!='') {
				if(substr($search_man_id,0,1)!="i" AND substr($search_man_id,0,1)!="I") { $search_man_id = "I".$search_man_id; } //make entry "48" into "I48"
				$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$search_man_id."'";
			}
			else{
				$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$man_gedcomnumber."'";
			}
			$person_result = $dbh->query($person_qry);

			echo '<select size="1" name="connect_man" style="width: 200px">';
			if($search_man_id=='') echo '<option value="">'.__('Select person').':</option>';

			while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
				$selected='';
				if (isset($man_gedcomnumber)){
					if ($person->pers_gedcomnumber==$man_gedcomnumber){ $selected=' SELECTED'; }
				}
				echo '<option value="'.$person->pers_gedcomnumber.'"'.$selected.'>'.
					$editor_cls->show_selected_person($person).'</option>';
			}
			echo '</select>';

			echo '<br>'.__('and').'<br>';

			$search_quicksearch_woman=''; if (isset($_POST['search_quicksearch_woman'])){ $search_quicksearch_woman=$_POST['search_quicksearch_woman']; }
			//print __('Female').':';
			echo ' <input class="fonts" type="text" name="search_quicksearch_woman" placeholder="'.__('Name').'" value="'.$search_quicksearch_woman.'" size="15">';

			$search_woman_id='';
			if (isset($_POST['search_woman_id'])) $search_woman_id=safe_text($_POST['search_woman_id']);
			echo __('or ID:').' <input class="fonts" type="text" name="search_woman_id" value="'.$search_woman_id.'" size="5">';

			echo ' <input class="fonts" type="submit" name="submit" value="'.__('Search').'">';

			// *** Use old value to detect change of woman in marriage ***
			echo '<input type="hidden" name="connect_woman_old" value="'.$woman_gedcomnumber.'">';

			if($search_quicksearch_woman != '') {
				// *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
				$search_quicksearch_woman=str_replace(' ', '%', $search_quicksearch_woman);
				// *** In case someone entered "Mons, Huub" using a comma ***
				$search_quicksearch_woman = str_replace(',','',$search_quicksearch_woman);
				//$person_qry= "SELECT *, CONCAT(pers_firstname,pers_prefix,pers_lastname) as concat_name
				$person_qry= "SELECT *
				FROM ".$tree_prefix."person
				WHERE CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname)
					LIKE '%$search_quicksearch_woman%'
					OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname)
					LIKE '%$search_quicksearch_woman%' 
					OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' '))
					LIKE '%$search_quicksearch_woman%' 
					OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname)
					LIKE '%$search_quicksearch_woman%'
					ORDER BY pers_lastname, pers_firstname";
			}
			elseif($search_woman_id!='') {
				if(substr($search_woman_id,0,1)!="i" AND substr($search_woman_id,0,1)!="I") { $search_woman_id = "I".$search_woman_id; } //make entry "48" into "I48"
				$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$search_woman_id."'";
			}
			else{
				$person_qry= "SELECT * FROM ".$tree_prefix."person WHERE pers_gedcomnumber='".$woman_gedcomnumber."'";
			}

			echo '<select size="1" name="connect_woman" style="width: 200px">';
			if($search_woman_id=='') echo '<option value="">'.__('Select person').':</option>';
			$person_result = $dbh->query($person_qry);
			while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
				$selected='';
				if (isset($woman_gedcomnumber)){
					if ($person->pers_gedcomnumber==$woman_gedcomnumber){ $selected=' SELECTED'; }
				}
				echo '<option value="'.$person->pers_gedcomnumber.'"'.$selected.'>'.
					$editor_cls->show_selected_person($person).'</option>';
			}
			echo '</select>';

			if (!isset($_GET['add_marriage'])){
				echo ' <BUTTON TYPE="submit" name="parents_switch" title="Switch Persons" class="button"><img src="'.CMS_ROOTPATH_ADMIN.'images/turn_around.gif" width="17"></BUTTON>';
			}

			echo '</td><td></td></tr>';

			// *** $marriage is empty by single persons ***
			if (isset($marriage)){
				echo '<input type="hidden" name="marriage" value="'.$marriage.'">';
			}
			echo '<tr class="humo_color"><td>'.__('Relation Type').'</td><td style="border-right:0px;"></td><td style="border-left:0px;">';
			echo '<select size="1" name="fam_kind">';
				echo '<option value="">'.__('Married').' </option>';

				$selected=''; if ($fam_kind=='living together'){ $selected=' SELECTED'; }
				echo '<option value="living together"'.$selected.'>'.__('Living together').'</option>';

				$selected=''; if ($fam_kind=='living apart together'){ $selected=' SELECTED'; }
				echo '<option value="living apart together"'.$selected.'>'.__('Living apart together').'</option>';

				$selected=''; if ($fam_kind=='intentionally unmarried mother'){ $selected=' SELECTED'; }
				echo '<option value="intentionally unmarried mother"'.$selected.'>'.__('Intentionally unmarried mother').'</option>';

				$selected=''; if ($fam_kind=='homosexual'){ $selected=' SELECTED'; }
				echo '<option value="homosexual"'.$selected.'>'.__('Homosexual').'</option>';

				$selected=''; if ($fam_kind=='non-marital'){ $selected=' SELECTED'; }
				echo '<option value="non-marital"'.$selected.'>'.__('Non_marital').'</option>';

				$selected=''; if ($fam_kind=='extramarital'){ $selected=' SELECTED'; }
				echo '<option value="extramarital"'.$selected.'>'.__('Extramarital').'</option>';

				$selected=''; if ($fam_kind=='partners'){ $selected=' SELECTED'; }
				echo '<option value="partners"'.$selected.'>'.__('Partner').'</option>';

				$selected=''; if ($fam_kind=='registered'){ $selected=' SELECTED'; }
				echo '<option value="registered"'.$selected.'>'.__('Registered').'</option>';

				$selected=''; if ($fam_kind=='unknown'){ $selected=' SELECTED'; }
				echo '<option value="unknown"'.$selected.'>'.__('Unknown relation').'</option>';

			echo '</select>';
			echo '</td><td></td></tr>';

			// *** Living together ***
			echo '<tr>';
			echo '<td><a href="#marriage" onclick="hideShow(6);"><span id="hideshowlink6">'.__('[+]').'</span></a> ';

			echo __('Living together').'</td>';
			echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($fam_relation_date,'fam_relation_date').' '.__('place').' <input type="text" name="fam_relation_place" placeholder="'.__('place').'" value="'.htmlspecialchars($fam_relation_place).'" size="'.$field_place.'">';

			echo '</td><td>';
				// *** Source by relation ***
				if (isset($marriage) AND !isset($_GET['add_marriage'])){
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='family' AND connect_sub_kind='fam_relation_source'
						AND connect_connect_id='".$marriage."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#marriage\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=fam_relation_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				}
			echo '</td></tr>';

			// *** End of living together ***
			echo '<tr style="display:none;" id="row6" name="row6">';
			echo '<td></td>';
			echo '<td style="border-right:0px;">'.__('End date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($fam_relation_end_date,"fam_relation_end_date").'</td>';
			echo '<td></td></tr>';

			echo '<tr style="display:none;" id="row6" name="row6">';
			echo '<td></td>';
			echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="fam_relation_text" '.$field_text.'>'.$fam_relation_text.'</textarea>';
			echo '<td></td>';
			echo '</td></tr>';

			// *** Marriage notice ***
			echo '<tr class="humo_color"><td>';
			echo '<a href="#marriage" onclick="hideShow(7);"><span id="hideshowlink7">'.__('[+]').'</span></a> ';
			echo __('Notice of Marriage').'</td>';
			echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($fam_marr_notice_date,"fam_marr_notice_date").' '.__('place').' <input type="text" name="fam_marr_notice_place" placeholder="'.__('place').'" value="'.htmlspecialchars($fam_marr_notice_place).'" size="'.$field_place.'">';

			echo '</td><td>';
				// *** Source by fam_marr_notice ***
				if (isset($marriage) AND !isset($_GET['add_marriage'])){
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='family' AND connect_sub_kind='fam_marr_notice_source'
						AND connect_connect_id='".$marriage."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#marriage\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=fam_marr_notice_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				}
			echo '</td></tr>';

			echo '<tr class="humo_color" style="display:none;" id="row7" name="row7">';
			echo '<td></td>';
			echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="fam_marr_notice_text" '.$field_text.'>'.$fam_marr_notice_text.'</textarea></td>';
			echo '<td></td></tr>';

			// *** Marriage ***
			echo '<tr><td>';
			echo '<a href="#marriage" onclick="hideShow(8);"><span id="hideshowlink8">'.__('[+]').'</span></a> ';
			echo __('Marriage').'</td>';
			echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($fam_marr_date,"fam_marr_date").' '.__('place').' <input type="text" name="fam_marr_place" placeholder="'.__('place').'" value="'.htmlspecialchars($fam_marr_place).'" size="'.$field_place.'">';

				echo '</td><td>';
				// *** Source by fam_marr ***
				if (isset($marriage) AND !isset($_GET['add_marriage'])){
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='family' AND connect_sub_kind='fam_marr_source'
						AND connect_connect_id='".$marriage."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#marriage\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=fam_marr_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				}

			echo '</td></tr>';

			echo '<tr style="display:none;" id="row8" name="row8">';
			echo '<td></td>';
			echo '<td style="border-right:0px;">'.__('Registrar').'</td><td style="border-left:0px;"><input type="text" name="fam_marr_authority" value="'.$fam_marr_authority.'" size="60"></td>';
			echo '<td></td></tr>';

			echo '<tr style="display:none;" id="row8" name="row8">';
			echo '<td></td>';
			echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="fam_marr_text" '.$field_text.'>'.$fam_marr_text.'</textarea></td>';
			echo '<td></td></tr>';

			// *** Marriage Witness ***
			$event_cls->show_event('marriage_witness');

			// *** Religious marriage notice ***
			echo '<tr class="humo_color"><td>';
			echo '<a href="#marriage" onclick="hideShow(9);"><span id="hideshowlink9">'.__('[+]').'</span></a> ';
			echo __('Religious Notice of Marriage').'</td>';
			echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($fam_marr_church_notice_date,"fam_marr_church_notice_date").' '.__('place').' <input type="text" name="fam_marr_church_notice_place" placeholder="'.__('place').'" value="'.htmlspecialchars($fam_marr_church_notice_place).'" size="'.$field_place.'">';

				echo '</td><td>';
				// *** Source by fam_marr_church_notice ***
				if (isset($marriage) AND !isset($_GET['add_marriage'])){
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='family' AND connect_sub_kind='fam_marr_church_notice_source'
						AND connect_connect_id='".$marriage."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#marriage\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=fam_marr_church_notice_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				}
			echo '</td></tr>';

			echo '<tr class="humo_color" style="display:none;" id="row9" name="row9">';
			echo '<td></td>';
			echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="fam_marr_church_notice_text" '.$field_text.'>'.$fam_marr_church_notice_text.'</textarea></td>';
			echo '<td></td></tr>';


			// *** Church marriage ***
			echo '<tr><td>';
			echo '<a href="#marriage" onclick="hideShow(10);"><span id="hideshowlink10">'.__('[+]').'</span></a> ';
			echo __('Religious Marriage').'</td>';
			echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($fam_marr_church_date,"fam_marr_church_date").' '.__('place').' <input type="text" name="fam_marr_church_place" placeholder="'.__('place').'" value="'.htmlspecialchars($fam_marr_church_place).'" size="'.$field_place.'">';
				echo '</td><td>';
				// *** Source by fam_marr_church ***
				if (isset($marriage) AND !isset($_GET['add_marriage'])){
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='family' AND connect_sub_kind='fam_marr_church_source'
						AND connect_connect_id='".$marriage."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#marriage\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=fam_marr_church_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				}
			echo '</td></tr>';

			echo '<tr style="display:none;" id="row10" name="row10">';
			echo '<td></td>';
			echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;"><textarea rows="1" name="fam_marr_church_text" '.$field_text.'>'.$fam_marr_church_text.'</textarea></td>';
			echo '<td></td></tr>';

			// *** Marriage Witness (church) ***
			$event_cls->show_event('marriage_witness_rel');

			// *** Religion ***
			echo '<tr class="humo_color"><td rowspan="1">'.__('Religion').'</td>';
			echo '<td style="border-right:0px;">'.__('Religion').'</td><td style="border-left:0px;"><input type="text" name="fam_religion" value="'.htmlspecialchars($fam_religion).'" size="60"></td><td></td></tr>';

			// *** divorce ***
			echo '<tr><td>';
			echo '<a href="#marriage" onclick="hideShow(11);"><span id="hideshowlink11">'.__('[+]').'</span></a> ';
			echo __('Divorce').'</td>';
			echo '<td style="border-right:0px;">'.__('date').'</td><td style="border-left:0px;">'.$editor_cls->date_show($fam_div_date,"fam_div_date").' '.__('place').' <input type="text" name="fam_div_place" placeholder="'.__('place').'" value="'.htmlspecialchars($fam_div_place).'" size="'.$field_place.'">';

			echo '</td><td>';
				// *** Source by fam_div ***
					if (isset($marriage) AND !isset($_GET['add_marriage'])){
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='family' AND connect_sub_kind='fam_div_source'
						AND connect_connect_id='".$marriage."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#marriage\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=fam_div_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				}
			echo '</td></tr>';

			// *** Use checkbox for divorse without further data ***
			echo '<tr><td></td>';
			echo '<td style="border-right:0px;"></td>';
			echo '<td style="border-left:0px;">';
				$checked=''; if ($fam_div_no_data) $checked=' checked';
				echo '<input type="checkbox" name="fam_div_no_data" value="no_data"'.$checked.'> '.__('Divorce (use this checkbox for a divorce without further data).');
			echo '</td><td></td></tr>';

			echo '<tr style="display:none;" id="row11" name="row11">';
			echo '<td></td>';
			echo '<td style="border-right:0px;">'.__('Registrar').'</td><td style="border-left:0px;"><input type="text" name="fam_div_authority" value="'.htmlspecialchars($fam_div_authority).'" size="60"></td>';
			echo '<td></td></tr>';

			echo '<tr style="display:none;" id="row11" name="row11">';
			echo '<td></td>';
			if ($fam_div_text=='DIVORCE') $fam_div_text=''; // *** Hide this text, it's a hidden value for a divorce without data ***
			echo '<td style="border-right:0px;">'.__('text').'</td><td style="border-left:0px;">
				<textarea rows="1" name="fam_div_text" '.$field_text.'>'.$fam_div_text.'</textarea></td>';
			echo '<td></td></tr>';

			// *** General text by marriage ***
			echo '<tr class="humo_color"><td>'.__('General text by marriage').'</td>';
			echo '<td style="border-right:0px;"></td>';
			echo '<td style="border-left:0px;">';
			echo '<textarea rows="1" name="fam_text"'.$field_text_large.'>'.$fam_text.'</textarea>';
				echo '</td><td>';
				// *** Source by text ***
				if (isset($marriage) AND !isset($_GET['add_marriage'])){
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='family' AND connect_sub_kind='fam_text_source'
						AND connect_connect_id='".$marriage."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#marriage\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=fam_text_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				}
			echo '</td></tr>';

			// *** Family sources in new person editor screen ***
			if (isset($marriage) AND !isset($_GET['add_marriage'])){
				echo '<tr><td>'.__('General source by marriage').'</td><td colspan="2">';
				echo '</td><td>';
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='family' AND connect_sub_kind='family_source'
						AND connect_connect_id='".$marriage."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#marriage\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=family_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				echo '</td></tr>';
			}

			// *** Family event editor ***
			$event_cls->show_event('family');

			echo '</form>';

			// *** NEW: show unprocessed gedcom tags ***
			if (isset($familyDb->fam_unprocessed_tags)){
				$tags_array=explode('<br>',$familyDb->fam_unprocessed_tags);
				echo '<tr class="humo_tags_fam"><td>';
				echo '<a href="#humo_tags_fam" onclick="hideShow(110);"><span id="hideshowlink110">'.__('[+]').'</span></a> ';
				echo __('Gedcom tags').'</td><td colspan="2">';
				if ($familyDb->fam_unprocessed_tags){
					printf(__('There are %d unprocessed gedcom tags.'), count ($tags_array));
				}
				else{
					printf(__('There are %d unprocessed gedcom tags.'), 0);
				}
				echo '</td><td></td></tr>';
				echo '<tr style="display:none;" id="row110" name="row110"><td></td>';
					echo '<td colspan="2">'.$familyDb->fam_unprocessed_tags.'</td>';
				echo '<td></td></tr>';
			}

		}


		}

		}	// End of menu_tab
		if ($menu_admin=='person') echo '</div>';

		echo '</table><br>'."\n";
	}



	// ********************
	// *** Show sources ***
	// ********************


	if ($menu_admin=='sources'){
		if (isset($_POST['source_add'])){
			// *** Generate new gedcomnr, find highest gedcomnumber I100: strip I and order by numeric ***
			$new_nr_qry= "SELECT *, ABS(substring(source_gedcomnr, 2)) AS gednr
				FROM ".$tree_prefix."sources ORDER BY gednr DESC LIMIT 0,1";
			$new_nr_result = $dbh->query($new_nr_qry);
			$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);

			$new_gedcomnumber='S1';
			if (isset($new_nr->source_gedcomnr)){
				$new_gedcomnumber='S'.(substr($new_nr->source_gedcomnr,1)+1);
			}

			$sql="INSERT INTO ".$tree_prefix."sources SET
				source_gedcomnr='".$new_gedcomnumber."',
				source_status='".$editor_cls->text_process($_POST['source_status'])."',
				source_title='".$editor_cls->text_process($_POST['source_title'])."',
				source_date='".safe_text($_POST['source_date'])."',
				source_place='".$editor_cls->text_process($_POST['source_place'])."',
				source_publ='".$editor_cls->text_process($_POST['source_publ'])."',
				source_refn='".$editor_cls->text_process($_POST['source_refn'])."',
				source_auth='".$editor_cls->text_process($_POST['source_auth'])."',
				source_subj='".$editor_cls->text_process($_POST['source_subj'])."',
				source_item='".$editor_cls->text_process($_POST['source_item'])."',
				source_kind='".$editor_cls->text_process($_POST['source_kind'])."',
				source_repo_caln='".$editor_cls->text_process($_POST['source_repo_caln'])."',
				source_repo_page='".safe_text($_POST['source_repo_page'])."',
				source_repo_gedcomnr='".$editor_cls->text_process($_POST['source_repo_gedcomnr'])."',
				source_text='".$editor_cls->text_process($_POST['source_text'])."',
				source_new_date='".$gedcom_date."',
				source_new_time='".$gedcom_time."'";
			$result=$dbh->query($sql);

			$new_source_qry= "SELECT * FROM ".$tree_prefix."sources ORDER BY source_id DESC LIMIT 0,1";
			$new_source_result = $dbh->query($new_source_qry);
			$new_source=$new_source_result->fetch(PDO::FETCH_OBJ);
			$_POST['source_id']=$new_source->source_id;
		}

		if (isset($_POST['source_change'])){
			$sql="UPDATE ".$tree_prefix."sources SET
			source_status='".$editor_cls->text_process($_POST['source_status'])."',
			source_title='".$editor_cls->text_process($_POST['source_title'])."',
			source_date='".$editor_cls->date_process('source_date')."',
			source_place='".$editor_cls->text_process($_POST['source_place'])."',
			source_publ='".$editor_cls->text_process($_POST['source_publ'])."',
			source_refn='".$editor_cls->text_process($_POST['source_refn'])."',
			source_auth='".$editor_cls->text_process($_POST['source_auth'])."',
			source_subj='".$editor_cls->text_process($_POST['source_subj'])."',
			source_item='".$editor_cls->text_process($_POST['source_item'])."',
			source_kind='".$editor_cls->text_process($_POST['source_kind'])."',
			source_repo_caln='".$editor_cls->text_process($_POST['source_repo_caln'])."',
			source_repo_page='".$editor_cls->text_process($_POST['source_repo_page'])."',
			source_repo_gedcomnr='".$editor_cls->text_process($_POST['source_repo_gedcomnr'])."',
			source_text='".$editor_cls->text_process($_POST['source_text'],true)."',
			source_changed_date='".$gedcom_date."',
			source_changed_time='".$gedcom_time."'
			WHERE source_id='".safe_text($_POST["source_id"])."'";
			$result=$dbh->query($sql);
			family_tree_update($tree_prefix);
		}

		if (isset($_POST['source_remove'])){
			echo '<div class="confirm">';
				echo __('Are you sure you want to remove this source and ALL source references?');
			echo ' <form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="source_id" value="'.$_POST['source_id'].'">';
			echo ' <input type="Submit" name="source_remove2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
			echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
			echo '</form>';
			echo '</div>';
		}
		if (isset($_POST['source_remove2'])){
			echo '<div class="confirm">';

			// *** Find gedcomnumber, needed for events query ***
			$source_qry=$dbh->query("SELECT * FROM ".$tree_prefix."sources
			WHERE source_id='".safe_text($_POST["source_id"])."'");
			$sourceDb=$source_qry->fetch(PDO::FETCH_OBJ);

			// *** Delete source references ***
			$sql="DELETE FROM ".$tree_prefix."events
			WHERE event_kind='source' AND event_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			// *** Delete person sources ***
			$sql="UPDATE ".$tree_prefix."person
			SET pers_name_source='' WHERE pers_name_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."person
			SET pers_birth_source='' WHERE pers_birth_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."person
			SET pers_bapt_source='' WHERE pers_bapt_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."person
			SET pers_death_source='' WHERE pers_death_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."person
			SET pers_buried_source='' WHERE pers_buried_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			// **** Delete family sources ***
			$sql="UPDATE ".$tree_prefix."family
			SET fam_relation_source='' WHERE fam_relation_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."family
			SET fam_marr_notice_source='' WHERE fam_marr_notice_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."family
			SET fam_marr_source='' WHERE fam_marr_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."family
			SET fam_marr_church_notice_source='' WHERE fam_marr_church_notice_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."family
			SET fam_marr_church_source='' WHERE fam_marr_church_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."family
			SET fam_div_source='' WHERE fam_div_source='@".$sourceDb->source_gedcomnr."@'";
			$result=$dbh->query($sql);

			// *** Delete source ***
			$sql="DELETE FROM ".$tree_prefix."sources
			WHERE source_id='".safe_text($_POST["source_id"])."'";
			$result=$dbh->query($sql);

			echo __('Source is removed!');

			echo '</div>';
		}

		echo '<h2>'.__('Source list, these sources can be connected to multiple items.').'</h2>';

		$source_id='';
		echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';
			echo '<form method="POST" action="'.$phpself.'">';
			echo '<input type="hidden" name="page" value="'.$page.'">';

			$source_qry=$dbh->query("SELECT * FROM ".$tree_prefix."sources ORDER BY source_title");
			echo __('Select source').': ';
			echo '<select size="1" name="source_id" style="width: 300px" onChange="this.form.submit();">';
			echo '<option value="">'.__('Select source').'</option>'; // *** For new source in new database... ***
			while ($sourceDb=$source_qry->fetch(PDO::FETCH_OBJ)){
				$selected='';
				if (isset($_POST['source_id'])){
					if ($_POST['source_id']==$sourceDb->source_id){
						$selected=' SELECTED';
						$source_id=$_POST['source_id'];
					}
				}
				echo '<option value="'.$sourceDb->source_id.'"'.$selected.'>'.@$sourceDb->source_title.
					' ['.@$sourceDb->source_gedcomnr.']</option>'."\n";
			}
			echo '</select>';

			echo ' '.__('or').': ';

			echo '<input type="Submit" name="add_source" value="'.__('Add source').'">';

			echo '</form>';
		echo '</td></tr></table><br>';

		// *** Show selected source ***
		if ($source_id OR isset($_POST['add_source'])){
			echo '<table class="humo standard" border="1">';
			print '<tr class="table_header"><th>'.__('Option').'</th><th colspan="2">'.__('Value').'</th></tr>';

			if (isset($_POST['add_source'])){
				$source_status=''; $source_title=''; $source_date=''; $source_place=''; $source_publ=''; $source_refn='';
				$source_auth=''; $source_auth=''; $source_subj=''; $source_item=''; $source_kind='';
				$source_text='';
				$source_repo_caln=''; $source_repo_page='';
				$source_repo_gedcomnr='';
			}
			else{
				@$source_qry=$dbh->query("SELECT * FROM ".$tree_prefix."sources
					WHERE source_id='".safe_text($source_id)."'");

				$die_message=__('No valid source number.');
				try {
					@$sourceDb=$source_qry->fetch(PDO::FETCH_OBJ);
				} catch (PDOException $e) {
					echo $die_message;
				}
				$source_status=$sourceDb->source_status;
				$source_title=$sourceDb->source_title; $source_date=$sourceDb->source_date;
				$source_place=$sourceDb->source_place; $source_publ=$sourceDb->source_publ;
				$source_refn=$sourceDb->source_refn; $source_auth=$sourceDb->source_auth;
				$source_auth=$sourceDb->source_auth; $source_subj=$sourceDb->source_subj;
				$source_item=$sourceDb->source_item; $source_kind=$sourceDb->source_kind;
				$source_text=$sourceDb->source_text;
				$source_repo_caln=$sourceDb->source_repo_caln; $source_repo_page=$sourceDb->source_repo_page;
				$source_repo_gedcomnr=$sourceDb->source_repo_gedcomnr;
			}

			echo '<form method="POST" action="'.$phpself.'">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="source_id" value="'.$_POST['source_id'].'">';

			echo '<tr><td>'.__('Status:').'</td><td>';
				echo '<select class="fonts" size="1" name="source_status">';
					$selected=''; if ($source_status=='publish'){ $selected=' selected'; }
					echo '<option value="publish"'.$selected.'>'.__('publish').'</option>';

					$selected=''; if ($source_status=='restricted'){ $selected=' selected'; }
					echo '<option value="restricted"'.$selected.'>'.__('restricted').'</option>';
					echo '</select> '.__('restricted = only visible for selected user groups');
			echo '</td></tr>';


			echo '<tr><td>'.__('Title').'</td><td><input type="text" name="source_title" value="'.htmlspecialchars($source_title).'" size="60"></td></tr>';

			echo '<tr><td>'.__('Subject').'</td><td><input type="text" name="source_subj" value="'.htmlspecialchars($source_subj).'" size="60"></td></tr>';
			echo '<tr><td>'.__('date').' - '.__('place').'</td><td>'.$editor_cls->date_show($source_date,"source_date").' <input type="text" name="source_place" value="'.htmlspecialchars($source_place).'" size="50"></td></tr>';

			echo '<tr><td>'.__('Repository').'</td><td>';
				$repo_qry=$dbh->query("SELECT * FROM ".$tree_prefix."repositories
					ORDER BY repo_name, repo_place");
				echo '<select size="1" name="source_repo_gedcomnr">';
				echo '<option value=""></option>'; // *** For new repository in new database... ***
				while($repoDb=$repo_qry->fetch(PDO::FETCH_OBJ)){
					$selected='';
					if ($repoDb->repo_gedcomnr==$source_repo_gedcomnr){$selected=' SELECTED';}
					echo '<option value="'.$repoDb->repo_gedcomnr.'"'.$selected.'>'.
					@$repoDb->repo_gedcomnr.', '.$repoDb->repo_name.' '.$repoDb->repo_place.'</option>'."\n";
				}
				echo '</select>';
			echo '</td></tr>';

			echo '<tr><td>'.__('Publication').'</td><td><input type="text" name="source_publ" value="'.htmlspecialchars($source_publ).'" size="60"> http://... '.__('will be shown as a link.').'</td></tr>';
			echo '<tr><td>'.__('Own code').'</td><td><input type="text" name="source_refn" value="'.$source_refn.'" size="60"></td></tr>';
			echo '<tr><td>'.__('Author').'</td><td><input type="text" name="source_auth" value="'.$source_auth.'" size="60"></td></tr>';
			echo '<tr><td>'.__('Nr.').'</td><td><input type="text" name="source_item" value="'.$source_item.'" size="60"></td></tr>';
			echo '<tr><td>'.__('Kind').'</td><td><input type="text" name="source_kind" value="'.$source_kind.'" size="60"></td></tr>';
			echo '<tr><td>'.__('Archive').'</td><td><input type="text" name="source_repo_caln" value="'.$source_repo_caln.'" size="60"></td></tr>';
			echo '<tr><td>'.__('Page').'</td><td><input type="text" name="source_repo_page" value="'.$source_repo_page.'" size="60"></td></tr>';
			echo '<tr><td>'.__('text').'</td><td><textarea rows="6" cols="80" name="source_text" '.$field_text_large.'>'.$editor_cls->text_show($source_text).'</textarea></td></tr>';

			if (isset($_POST['add_source'])){
				echo '<tr><td>'.__('Add').'</td><td><input type="Submit" name="source_add" value="'.__('Add').'"></td></tr>';
			}
			else{
				echo '<tr><td>'.__('Save').'</td><td><input type="Submit" name="source_change" value="'.__('Save').'">';

				echo ' '.__('or').' ';
				echo '<input type="Submit" name="source_remove" value="'.__('Delete').'">';

				echo '</td></tr>';
			}

			echo '</form>';
			echo '</table>'."\n";

			// *** Source example in IFRAME ***
			if (!isset($_POST['add_source'])){
				echo '<p>'.__('Example').'<br>';
				echo '<iframe src ="'.$sourcestring.'database='.$tree_prefix.'&amp;id='.$sourceDb->source_gedcomnr.'" class="iframe">';
//TRANSLATE
				echo '  <p>Your browser does not support iframes.</p>';
				echo '</iframe>';
			}
		}

	}


	// *******************************
	// *** Show/ edit repositories ***
	// *******************************


	if ($menu_admin=='repositories'){
		if (isset($_POST['repo_add'])){
			// *** Generate new gedcomnr, find highest gedcomnumber I100: strip I and order by numeric ***
			$new_nr_qry= "SELECT *, ABS(substring(repo_gedcomnr, 2)) AS gednr
				FROM ".$tree_prefix."repositories ORDER BY gednr DESC LIMIT 0,1";
			$new_nr_result = $dbh->query($new_nr_qry);
			$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
			$new_gedcomnumber='R1';
			if (isset($new_nr->repo_gedcomnr)){
				$new_gedcomnumber='R'.(substr($new_nr->repo_gedcomnr,1)+1);
			}

			$sql="INSERT INTO ".$tree_prefix."repositories SET
				repo_gedcomnr='".$new_gedcomnumber."',
				repo_name='".$editor_cls->text_process($_POST['repo_name'])."',
				repo_address='".$editor_cls->text_process($_POST['repo_address'])."',
				repo_zip='".safe_text($_POST['repo_zip'])."',
				repo_place='".$editor_cls->text_process($_POST['repo_place'])."',
				repo_phone='".safe_text($_POST['repo_phone'])."',
				repo_date='".$editor_cls->date_process('repo_date')."',
				repo_text='".$editor_cls->text_process($_POST['repo_text'])."',
				repo_mail='".safe_text($_POST['repo_mail'])."',
				repo_url='".safe_text($_POST['repo_url'])."',
				repo_new_date='".$gedcom_date."',
				repo_new_time='".$gedcom_time."'";
			$result=$dbh->query($sql);

			$new_repo_qry= "SELECT * FROM ".$tree_prefix."repositories ORDER BY repo_id DESC LIMIT 0,1";
			$new_repo_result = $dbh->query($new_repo_qry);
			$new_repo=$new_repo_result->fetch(PDO::FETCH_OBJ);
			$_POST['repo_id']=$new_repo->repo_id;
		}

		if (isset($_POST['repo_change'])){
			$sql="UPDATE ".$tree_prefix."repositories SET
				repo_name='".$editor_cls->text_process($_POST['repo_name'])."',
				repo_address='".$editor_cls->text_process($_POST['repo_address'])."',
				repo_zip='".safe_text($_POST['repo_zip'])."',
				repo_place='".$editor_cls->text_process($_POST['repo_place'])."',
				repo_phone='".safe_text($_POST['repo_phone'])."',
				repo_date='".$editor_cls->date_process('repo_date')."',
				repo_text='".$editor_cls->text_process($_POST['repo_text'])."',
				repo_mail='".safe_text($_POST['repo_mail'])."',
				repo_url='".safe_text($_POST['repo_url'])."',
				repo_changed_date='".$gedcom_date."',
				repo_changed_time='".$gedcom_time."'
			WHERE repo_id='".safe_text($_POST["repo_id"])."'";
			$result=$dbh->query($sql);
			family_tree_update($tree_prefix);
		}

		if (isset($_POST['repo_remove'])){
			echo '<div class="confirm">';
			echo __('Really remove repository with all repository links?');
			echo ' <form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="repo_id" value="'.$_POST['repo_id'].'">';
			echo ' <input type="Submit" name="repo_remove2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
			echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
			echo '</form>';
			echo '</div>';
		}
		if (isset($_POST['repo_remove2'])){
			echo '<div class="confirm">';
			// *** Find gedcomnumber, needed for events query ***
			$repo_qry=$dbh->query("SELECT * FROM ".$tree_prefix."repositories
				WHERE repo_id='".safe_text($_POST["repo_id"])."'");
			$repoDb=$repo_qry->fetch(PDO::FETCH_OBJ);

			// *** Delete repository link ***
			$sql="UPDATE ".$tree_prefix."sources SET source_repo_gedcomnr=''
			WHERE source_repo_gedcomnr='".$repoDb->repo_gedcomnr."'";
			$result=$dbh->query($sql);

			// *** Delete repository ***
			$sql="DELETE FROM ".$tree_prefix."repositories
			WHERE repo_id='".safe_text($_POST["repo_id"])."'";
			$result=$dbh->query($sql);
			echo __('Repository is removed!');
			echo '</div>';
		}

		echo '<h2>'.__('Repositories').'</h2>';
		echo __('A repository can be connected to an extended source. Edit an extended source to connect a repository.');

		echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';
			echo '<form method="POST" action="'.$phpself.'">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			$repo_qry=$dbh->query("SELECT * FROM ".$tree_prefix."repositories
				ORDER BY repo_name, repo_place");
			echo __('Select repository').' ';
			echo '<select size="1" name="repo_id" onChange="this.form.submit();">';
			echo '<option value="">'.__('Select repository').'</option>'; // *** For new repository in new database... ***
			while ($repoDb=$repo_qry->fetch(PDO::FETCH_OBJ)){
				$selected='';
				if (isset($_POST['repo_id'])){
					if ($_POST['repo_id']==$repoDb->repo_id){$selected=' SELECTED';}
				}
				echo '<option value="'.$repoDb->repo_id.'"'.$selected.'>'.
				@$repoDb->repo_gedcomnr.', '.$repoDb->repo_name.' '.$repoDb->repo_place.'</option>'."\n";
			}
			echo '</select>';

			echo ' '.__('or').': ';
			echo '<input type="Submit" name="add_repo" value="'.__('Add repository').'">';
			echo '</form>';
		echo '</td></tr></table><br>';

		// *** Show selected repository ***
		if (isset($_POST['repo_id'])){
			echo '<table class="humo standard" border="1">';
			print '<tr class="table_header"><th>'.__('Option').'</th><th colspan="2">'.__('Value').'</th></tr>';

			if (isset($_POST['add_repo'])){
				$repo_name=''; $repo_address=''; $repo_zip=''; $repo_place='';
				$repo_phone=''; $repo_date=''; $repo_source=''; $repo_text='';
				$repo_photo=''; $repo_mail=''; $repo_url='';
				$repo_new_date=''; $repo_new_time=''; $repo_changed_date=''; $repo_changed_time='';
			}
			else{
				@$repo_qry=$dbh->query("SELECT * FROM ".$tree_prefix."repositories
					WHERE repo_id='".safe_text($_POST["repo_id"])."'");

				$die_message=__('No valid repository number.');
				try {
					@$repoDb=$repo_qry->fetch(PDO::FETCH_OBJ);
				} catch(PDOException $e) {
					echo $die_message;
				}
				$repo_name=$repoDb->repo_name;
				$repo_address=$repoDb->repo_address;
				$repo_zip=$repoDb->repo_zip;
				$repo_place=$repoDb->repo_place;
				$repo_phone=$repoDb->repo_phone;
				$repo_date=$repoDb->repo_date;
				$repo_source=$repoDb->repo_source;
				$repo_text=$repoDb->repo_text;
				$repo_photo=$repoDb->repo_photo;
				$repo_mail=$repoDb->repo_mail;
				$repo_url=$repoDb->repo_url;
				$repo_new_date=$repoDb->repo_new_date; $repo_new_time=$repoDb->repo_new_time;
				$repo_changed_date=$repoDb->repo_changed_date;
				$repo_changed_time=$repoDb->repo_changed_time;
			}

			echo '<form method="POST" action="'.$phpself.'">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="repo_id" value="'.$_POST['repo_id'].'">';

			echo '<tr><td>'.__('Title').'</td><td><input type="text" name="repo_name" value="'.htmlspecialchars($repo_name).'" size="60"></td></tr>';

			echo '<tr><td>'.__('Address').'</td><td><input type="text" name="repo_address" value="'.htmlspecialchars($repo_address).'" size="60"></td></tr>';

			echo '<tr><td>'.__('Zip code').'</td><td><input type="text" name="repo_zip" value="'.$repo_zip.'" size="60"></td></tr>';

			echo '<tr><td>'.ucfirst(__('date')).' - '.__('place').'</td><td>'.$editor_cls->date_show($repo_date,"repo_date").' <input type="text" name="repo_place" value="'.htmlspecialchars($repo_place).'" size="50"></td></tr>';

			echo '<tr><td>'.__('Phone').'</td><td><input type="text" name="repo_phone" value="'.$repo_phone.'" size="60"></td></tr>';

			//SOURCE

			echo '<tr><td>'.ucfirst(__('text')).'</td><td><textarea rows="1" name="repo_text" '.$field_text_large.'>'.
			$editor_cls->text_show($repo_text).'</textarea></td></tr>';

			echo '<tr><td>'.__('E-mail').'</td><td><input type="text" name="repo_mail" value="'.$repo_mail.'" size="60"></td></tr>';

			echo '<tr><td>'.__('URL/ Internet link').'</td><td><input type="text" name="repo_url" value="'.$repo_url.'" size="60"></td></tr>';

			if (isset($_POST['add_repo'])){
				echo '<tr><td>'.__('Add').'</td><td><input type="Submit" name="repo_add" value="'.__('Add').'"></td></tr>';
			}
			else{
				echo '<tr><td>'.__('Save').'</td><td><input type="Submit" name="repo_change" value="'.__('Save').'">';

				echo ' '.__('or').' ';
				echo '<input type="Submit" name="repo_remove" value="'.__('Delete').'">';

				echo '</td></tr>';
			}

			echo '</form>';
			echo '</table>'."\n";

			// *** Repository example in IFRAME ***
			if (!isset($_POST['add_repo'])){
//TO DO: show repo in example frame.
				//echo '<p>'.__('Example').'<br>';
				//echo '<iframe src ="'.$sourcestring.'database='.$tree_prefix.'&amp;id='.$repoDb->repo_gedcomnr.'" class="iframe">';
//TRANSLATE
				//echo '  <p>Your browser does not support iframes.</p>';
				//echo '</iframe>';
			}
		}

	}


	// ****************************
	// *** Show/ edit addresses ***
	// ****************************


	if ($menu_admin=='addresses'){
		if (isset($_POST['address_add'])){
			// *** Generate new gedcomnr, find highest gedcomnumber I100: strip I and order by numeric ***
			$new_nr_qry= "SELECT *, ABS(substring(address_gedcomnr, 2)) AS gednr
				FROM ".$tree_prefix."addresses ORDER BY gednr DESC LIMIT 0,1";
			$new_nr_result = $dbh->query($new_nr_qry);
			$new_nr=$new_nr_result->fetch(PDO::FETCH_OBJ);
			$new_gedcomnumber='R1';
			if (isset($new_nr->address_gedcomnr)){
				$new_gedcomnumber='R'.(substr($new_nr->address_gedcomnr,1)+1);
			}

			$sql="INSERT INTO ".$tree_prefix."addresses SET
				address_gedcomnr='".$new_gedcomnumber."',
				address_address='".$editor_cls->text_process($_POST['address_address'])."',
				address_date='".safe_text($_POST['address_date'])."',
				address_zip='".safe_text($_POST['address_zip'])."',
				address_place='".$editor_cls->text_process($_POST['address_place'])."',
				address_phone='".safe_text($_POST['address_phone'])."',
				address_photo='".safe_text($_POST['address_photo'])."',
				address_text='".$editor_cls->text_process($_POST['address_text'])."',
				address_new_date='".$gedcom_date."',
				address_new_time='".$gedcom_time."'";
			$result=$dbh->query($sql);

			$new_address_qry= "SELECT * FROM ".$tree_prefix."addresses ORDER BY address_id DESC LIMIT 0,1";
			$new_address_result = $dbh->query($new_address_qry);
			$new_address=$new_address_result->fetch(PDO::FETCH_OBJ);
			$_POST['address_id']=$new_address->address_id;
		}

		if (isset($_POST['address_change'])){
			$sql="UPDATE ".$tree_prefix."addresses SET
				address_address='".$editor_cls->text_process($_POST['address_address'])."',
				address_date='".$editor_cls->date_process('address_date')."',
				address_zip='".safe_text($_POST['address_zip'])."',
				address_place='".$editor_cls->text_process($_POST['address_place'])."',
				address_phone='".safe_text($_POST['address_phone'])."',
				address_photo='".safe_text($_POST['address_photo'])."',
				address_text='".$editor_cls->text_process($_POST['address_text'],true)."',
				address_changed_date='".$gedcom_date."',
				address_changed_time='".$gedcom_time."'
			WHERE address_id='".safe_text($_POST["address_id"])."'";
			$result=$dbh->query($sql);

			family_tree_update($tree_prefix);
		}

		if (isset($_POST['address_remove'])){
			echo '<div class="confirm">';
				echo __('Are you sure you want to remove this address and ALL address references?');
			echo ' <form method="post" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="address_id" value="'.$_POST['address_id'].'">';
			echo ' <input type="Submit" name="address_remove2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
			echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
			echo '</form>';
			echo '</div>';
		}
		if (isset($_POST['address_remove2'])){
			echo '<div class="confirm">';
			// *** Find gedcomnumber, needed for events query ***
			$address_qry=$dbh->query("SELECT * FROM ".$tree_prefix."addresses
			WHERE address_id='".safe_text($_POST["address_id"])."'");
			$addressDb=$address_qry->fetch(PDO::FETCH_OBJ);

			// *** Delete address references ***
			$sql="DELETE FROM ".$tree_prefix."events
			WHERE event_kind='address' AND event_source='@".$addressDb->address_gedcomnr."@'";
			$result=$dbh->query($sql);

			// *** Delete address ***
			$sql="DELETE FROM ".$tree_prefix."addresses
			WHERE address_id='".safe_text($_POST["address_id"])."'";
			$result=$dbh->query($sql);

			echo __('Address has been removed!');
			echo '</div>';
		}

		/*
		$connect_sub_kind='';
		if (isset($_GET['connect_sub_kind'])){
			$connect_sub_kind=$_GET['connect_sub_kind'];
		}
		if (isset($_POST['connect_sub_kind'])){
			$connect_sub_kind=$_POST['connect_sub_kind'];
		}
		*/

		if (isset($_POST["address_id"])){
			$address_id=$_POST["address_id"];
			$_SESSION['admin_address_gedcomnumber']=$address_id;
		}
		elseif (isset($_GET["connect_connect_id"])){
			$address_id=$_GET["connect_connect_id"];
			$_SESSION['admin_address_gedcomnumber']=$address_id;
		}
		elseif (isset($_POST["connect_connect_id"])){
			$address_id=$_POST["connect_connect_id"];
			$_SESSION['admin_address_gedcomnumber']=$address_id;
		}


		echo '<h2>'.__('Address list, these addresses can be connected to multiple persons.').'</h2>';

		// *** Edit source by address ***
		// NO SOURCE BY ADDRESS AT THIS MOMENT

		echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';
			echo '<form method="POST" action="'.$phpself.'">';
			echo '<input type="hidden" name="page" value="'.$page.'">';

			$address_qry=$dbh->query("SELECT * FROM ".$tree_prefix."addresses
				WHERE address_gedcomnr LIKE '_%' ORDER BY address_place, address_address");
			echo __('Select address').': ';
			echo '<select size="1" name="address_id" onChange="this.form.submit();">';
			echo '<option value="">'.__('Select address').'</option>'; // *** For new address in new database... ***
			while ($addressDb=$address_qry->fetch(PDO::FETCH_OBJ)){
				$selected='';
				if (isset($_POST['address_id'])){
					if ($_POST['address_id']==$addressDb->address_id){$selected=' SELECTED';}
				}
				echo '<option value="'.$addressDb->address_id.'"'.$selected.'>'.
				@$addressDb->address_place.', '.$addressDb->address_address.' ['.@$addressDb->address_gedcomnr.']</option>'."\n";
			}
			echo '</select>';

			echo ' '.__('or').': ';
			echo '<input type="Submit" name="add_address" value="'.__('Add address').'">';
			echo '</form>';
		echo '</td></tr></table><br>';

		// *** Show selected address ***
		if (isset($_POST['address_id'])){
			echo '<table class="humo standard" border="1">';
			print '<tr class="table_header"><th>'.__('Option').'</th><th colspan="2">'.__('Value').'</th></tr>';

			if (isset($_POST['add_address'])){
				$address_address=''; $address_date=''; $address_zip=''; $address_place=''; $address_phone='';
				$address_photo=''; $address_source=''; $address_text='';
			}
			else{
				@$address_qry2=$dbh->query("SELECT * FROM ".$tree_prefix."addresses WHERE address_id='".safe_text($_POST["address_id"])."'");

				$die_message=__('No valid address number.');
				try{
					@$addressDb=$address_qry2->fetch(PDO::FETCH_OBJ);
				} catch(PDOException $e) {
					echo $die_message;
				}
				$address_address=$addressDb->address_address; $address_date=$addressDb->address_date;
				$address_zip=$addressDb->address_zip; $address_place=$addressDb->address_place;
				$address_phone=$addressDb->address_phone; $address_photo=$addressDb->address_photo;
				$address_source=$addressDb->address_source; $address_text=$addressDb->address_text;
			}

			echo '<form method="POST" action="'.$phpself.'">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="address_id" value="'.$_POST['address_id'].'">';
			echo '<tr><td>'.__('Address').':</td><td><input type="text" name="address_address" value="'.htmlspecialchars($address_address).'" size="60"></td></tr>';
			echo '<tr><td>'.__('date').' - '.__('place').'</td><td>'.$editor_cls->date_show($address_date,"address_date").' <input type="text" name="address_place" value="'.htmlspecialchars($address_place).'" size="50"></td></tr>';
			echo '<tr><td>'.__('Zip code').':</td><td><input type="text" name="address_zip" value="'.$address_zip.'" size="60"></td></tr>';
			echo '<tr><td>'.__('Phone').':</td><td><input type="text" name="address_phone" value="'.$address_phone.'" size="60"></td></tr>';
			echo '<tr><td>'.__('Picture').'</td><td><input type="text" name="address_photo" value="'.$address_photo.'" size="60"></td></tr>';

			// *** Source by address ***
			echo '<tr><td>'.__('source').'</td><td>';
				if (isset($addressDb->address_id)){
					// *** Calculate and show nr. of sources ***
					$connect_qry="SELECT *
						FROM ".$tree_prefix."connections
						WHERE connect_kind='address' AND connect_sub_kind='address_source'
						AND connect_connect_id='".$addressDb->address_id."'";
					$connect_sql=$dbh->query($connect_qry);
					echo "&nbsp;<a href=\"#\" onClick=\"window.open('index.php?page=editor_sources&connect_sub_kind=address_source', '','width=800,height=500')\">".__('source');
					echo ' ['.$connect_sql->rowCount().']</a>';
				}
			echo '</td></tr>';

			echo '<tr><td>'.__('text').'</td><td><textarea rows="1" name="address_text" '.$field_text_large.'>'.
			$editor_cls->text_show($address_text).'</textarea></td></tr>';

			if (isset($_POST['add_address'])){
				echo '<tr><td>'.__('Add').'</td><td><input type="Submit" name="address_add" value="'.__('Add').'"></td></tr>';
			}
			else{
				echo '<tr><td>'.__('Save').'</td><td><input type="Submit" name="address_change" value="'.__('Save').'">';
				echo ' '.__('or').' ';
				echo '<input type="Submit" name="address_remove" value="'.__('Delete').'">';
				echo '</td></tr>';
			}

			echo '</form>';
			echo '</table>'."\n";

			// *** Example in IFRAME ***
			if (!isset($_POST['add_address'])){
				echo '<p>'.__('Example').'<br>';
				echo '<iframe src ="'.$addresstring.'database='.$tree_prefix.'&gedcomnumber='.$addressDb->address_gedcomnr.'" class="iframe">';
				echo '  <p>Your browser does not support iframes.</p>';
				echo '</iframe>';
			}

		}

	}


	// *******************
	// *** Show places ***
	// *******************


	if ($menu_admin=='places'){
		echo '<h2>'.__('Rename places').'</h2>';

		echo __('Update all places here. At this moment these places are updated: birth, baptise, death and burial places.').'<br>';

		if (isset($_POST['place_change'])){
			$sql="UPDATE ".$tree_prefix."person SET
				pers_birth_place='".$editor_cls->text_process($_POST['place_new'])."'
			WHERE pers_birth_place='".safe_text($_POST["place_old"])."'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."person SET
				pers_bapt_place='".$editor_cls->text_process($_POST['place_new'])."'
			WHERE pers_bapt_place='".safe_text($_POST["place_old"])."'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."person SET
				pers_death_place='".$editor_cls->text_process($_POST['place_new'])."'
			WHERE pers_death_place='".safe_text($_POST["place_old"])."'";
			$result=$dbh->query($sql);

			$sql="UPDATE ".$tree_prefix."person SET
				pers_buried_place='".$editor_cls->text_process($_POST['place_new'])."'
			WHERE pers_buried_place='".safe_text($_POST["place_old"])."'";
			$result=$dbh->query($sql);

			if (isset($_POST["google_maps"])){
				// *** Check if Google Maps table allready exist ***
				$tempqry = $dbh->query("SHOW TABLES LIKE 'humo_location'");
				if ($tempqry->rowCount()) {
					$sql= "UPDATE humo_location
						SET location_location ='".safe_text($_POST['place_new'])."'
						WHERE location_location = '".safe_text($_POST['place_old'])."'";
					$result=$dbh->query($sql);
				}
			}

			// *** Show changed place again ***
			$_POST["place_select"]=$_POST['place_new'];

			echo '<b>'.__('UPDATE OK!').'</b> ';
		}

		$person_qry= "(SELECT pers_birth_place as place_edit FROM ".$tree_prefix."person GROUP BY pers_birth_place)";
		$person_qry.="UNION (SELECT pers_bapt_place as place_edit FROM ".$tree_prefix."person GROUP BY pers_bapt_place)";
		$person_qry.="UNION (SELECT pers_death_place as place_edit FROM ".$tree_prefix."person GROUP BY pers_death_place)";
		$person_qry.="UNION (SELECT pers_buried_place as place_edit FROM ".$tree_prefix."person GROUP BY pers_buried_place)";
		$person_qry.=" ORDER BY place_edit";
		$person_result = $dbh->query($person_qry);
		echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';
			echo '<form method="POST" action="'.$phpself.'">';
			echo $person_result->rowCount().' '.__('Places').'. ';
			echo __('Select location');
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<select size="1" name="place_select">';
			while ($person=$person_result->fetch(PDO::FETCH_OBJ)){
				if ($person->place_edit != ''){
					$selected='';
					if (isset($_POST["place_select"]) AND $_POST["place_select"]==$person->place_edit){
						$selected=" SELECTED";
					}
					echo '<option value="'.$person->place_edit.'"'.$selected.'>'.$person->place_edit.'</option>';
				}
			}
			echo '</select>';
			echo '<input type="Submit" name="submit" value="'.__('Select').'">';
			echo '</form>';
		echo '</td></tr></table><br>';

		// *** Change selected place ***
		if (isset($_POST["place_select"])){
			echo '<table class="humo standard" border="1">';
				echo '<tr class="table_header"><th colspan="2">'.__('Change location').'</th></tr>';
				echo '<form method="POST" action="'.$phpself.'">';
				echo '<tr><td>';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				echo '<input type="hidden" name="place_old" value="'.$_POST["place_select"].'">';
				echo __('Change location').':</td><td><input type="text" name="place_new" value="'.$_POST["place_select"].'" size="60"><br>';

				echo '<input type="Checkbox" name="google_maps" value="1" checked>'.__('Also change Google Maps table.').'<br>';
				echo '<input type="Submit" name="place_change" value="'.__('Save').'">';
				echo '</td></tr>';
				echo '</form>';
			echo '</table>';
		}

		//echo '<br><br><br>'; // in some browser settings the bottom line (with the event choice!) is hidden under bottom bar
	}

//	echo '</div>'; // float left
}
?>