<?php
include_once("header.php");

// *** Include some HuMo-gen functions to show marriage and person information ***
if (!defined("CMS_SPECIFIC")) define("CMS_SPECIFIC", false);
include_once(CMS_ROOTPATH."include/language_event.php");
include_once(CMS_ROOTPATH."include/process_text.php");
include_once(CMS_ROOTPATH."include/calculate_age_cls.php");
include_once(CMS_ROOTPATH."include/show_sources.php");
include_once(CMS_ROOTPATH."include/witness.php");
include_once(CMS_ROOTPATH."include/show_picture.php");

$screen_mode='mobile';

echo '<!DOCTYPE html>';
//if("rtl"=="rtl") { echo '<html dir="rtl">'; }
//else { echo '<html>'; }
//echo '<html dir="rtl">';
echo '<html>';
echo '<head>';
	echo '<meta charset="utf-8">';
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
	echo '<title>HuMo-gen mobile</title>';
	echo '<link rel="stylesheet" href="themes/rene.min.css" />';
if($language["dir"]=="rtl") { 
	echo '<link rel="stylesheet" href="jquery_mobile/rtl.jquery.mobile-1.2.0.min.css" />';
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery-1.8.0.min.js"></script>';
	echo '<script type="text/javascript" src="jquery_mobile/rtl.jquery.mobile-1.2.0.min.js"></script>';
}
else {  
	echo '<link rel="stylesheet" href="jquery_mobile/jquery.mobile.structure-1.2.0.min.css" />';
	echo '<script type="text/javascript" src="'.CMS_ROOTPATH.'include/jqueryui/js/jquery-1.8.0.min.js"></script>';
	echo '<script type="text/javascript" src="jquery_mobile/jquery.mobile-1.2.0.min.js"></script>';
}

echo '</head>';

echo '<body>';
echo '<div data-role="page" data-theme="b">';
	echo '<div data-role="header" data-theme="b">';
		echo '<h1>'.__('Family').'</h1>';
		echo '<a href="./" data-direction="reverse" class="ui-btn-left jqm-home">'.__('Home').'</a>';
	echo '</div>';

	/*
	// *** Show basic information/ Show all details ***
	if (isset($_POST['id'])){ $id=$_POST['id']; }
	if (isset($_GET['id'])){ $id=$_GET['id']; }
	echo '<form action="family.php?id='.$id.'" method="post" data-ajax="false">';
	echo '<div data-role="fieldcontain">';
		//echo '<label for="select-native-1">Basic:</label>';
		echo '<select name="show_data" onChange="this.form.submit();">';
			echo '<option value="basic">Show basic information</option>';
			$selected=''; if (isset($_POST['show_data']) AND $_POST['show_data']=='extended'){ $selected='selected="selected"'; }
			echo '<option value="extended"'.$selected.'>Show all details</option>';
		echo '</select>';
	echo '</div>';
	echo '</form>';
	*/

	echo '<div data-role="content" data-theme="b">';
		echo '<ul data-role="listview" data-inset="true">';

if (isset($_POST['id'])){ $id=$_POST['id']; }
if (isset($_GET['id'])){ $id=$_GET['id']; }
$res=$dbh->query("SELECT * FROM humo_persons
	LEFT JOIN humo_families
	ON fam_gedcomnumber=pers_famc
	WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber LIKE '".safe_text($id)."'");
@$person_manDb=$res->fetch(PDO::FETCH_OBJ);  

// *** Family statistics ***
// *** Don't count search bots, crawlers etc. ***
if (!$bot_visit){
	// *** Update (old) statistics counter ***
	$fam_counter=$person_manDb->fam_counter+1;
	//$sql="UPDATE ".safe_text($_SESSION['tree_prefix'])."family SET fam_counter=$fam_counter
	//	WHERE fam_gedcomnumber='".safe_text($person_manDb->fam_gedcomnumber)."'";
	$sql="UPDATE humo_families SET fam_counter=$fam_counter
		WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber='".safe_text($person_manDb->fam_gedcomnumber)."'";
	$dbh->query($sql);
	// *** Extended statistics, first check if table exists ***
	//$statistics = mysql_query("SELECT * FROM humo_stat_date LIMIT 0,1",$db);
	$statistics = $dbh->query("SELECT * FROM humo_stat_date LIMIT 0,1");
	if ($statistics AND $user['group_statistics']=='j'){

		$datasql = $dbh->query("SELECT * FROM humo_trees
			WHERE tree_prefix='".safe_text($_SESSION['tree_prefix'])."'");
		$datasqlDb=$datasql->fetch(PDO::FETCH_OBJ);
		$stat_easy_id=$datasqlDb->tree_id.'-'.$person_manDb->fam_gedcomnumber.'-'.$person_manDb->fam_man.'-'.$person_manDb->fam_woman;

		$update_sql="INSERT INTO humo_stat_date SET
			stat_easy_id='".$stat_easy_id."',
			stat_ip_address='".$_SERVER['REMOTE_ADDR']."',
			stat_user_agent='".$_SERVER['HTTP_USER_AGENT']."',
			stat_tree_id='".$datasqlDb->tree_id."',
			stat_gedcom_fam='".$person_manDb->fam_gedcomnumber."',
			stat_gedcom_man='".$person_manDb->fam_man."',
			stat_gedcom_woman='".$person_manDb->fam_woman."',
			stat_date_stat='".date("Y-m-d H:i")."',
			stat_date_linux='".time()."'";
		$result=$dbh->query($update_sql);
	}
}


// *** Use class to show person ***
$tree_prefix_quoted = safe_text($_SESSION['tree_prefix']);

$man_cls = New person_cls;
$man_cls->construct($person_manDb);  
//$man_privacy=$man_cls->privacy;
$persname = $man_cls->person_name($person_manDb); 

print '<li data-role="list-divider">'.$persname['name'].'</li>';

// *** Show person details using standard HuMo-gen function ***
$var='<br>'.$man_cls->person_data("mobile", 0);
echo '<li><p class="myParagraph">'.$var.'</p></li>'; //data main person

// *** Parents ***
echo '<li data-role="list-divider">'.__('Parents').'</li>';
if ($person_manDb->fam_man!=NULL){
	echo popup($person_manDb->fam_man,false); // father
	// *** Show person details using standard HuMo-gen function ***
	//$parent1=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber='".safe_text($person_manDb->fam_man)."'");
	$parent1=$dbh->query("SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($person_manDb->fam_man)."'");
	@$parent1Db=$parent1->fetch(PDO::FETCH_OBJ);
	$parent1_cls = New person_cls;
	$parent1_cls->construct($parent1Db);  
	//$parent1_privacy=$parent1_cls->privacy;
	echo $parent1_cls->person_data("mobile", 0);
	echo '</li>';
}
if ($person_manDb->fam_woman!=NULL){
	echo popup($person_manDb->fam_woman,false); // father
	// *** Show person details using standard HuMo-gen function ***
	//$parent2=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber='".safe_text($person_manDb->fam_woman)."'");
	$parent2=$dbh->query("SELECT * FROM humo_persons
		WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($person_manDb->fam_woman)."'");
	@$parent2Db=$parent2->fetch(PDO::FETCH_OBJ);
	$parent2_cls = New person_cls;
	$parent2_cls->construct($parent2Db);  
	//$parent2_privacy=$parent2_cls->privacy;
	echo $parent2_cls->person_data("mobile", 0);
	echo '</li>';
}
elseif(($person_manDb->fam_man==NULL) AND ($person_manDb->fam_woman==NULL)){
	echo '<li><p>'.__('Parents').' '.strtolower(__('Unknown')).'</p></li>';	
}	

// *** Partners and children ***
if ($person_manDb->pers_fams!=NULL){
	$marr=explode(";", $person_manDb->pers_fams);
	for ($i=0; $i<=count($marr)-1; $i++){
		$res=$dbh->query("SELECT * FROM humo_families
			WHERE fam_tree_id='".$tree_id."' AND fam_gedcomnumber LIKE '".safe_text($marr[$i])."'");
		$marrDb=$res->fetch(PDO::FETCH_OBJ); 

		if ($id==$marrDb->fam_man){
			$partner=$marrDb->fam_woman;
		}
		else{
			$partner=$marrDb->fam_man;
		}	
		$children=$marrDb->fam_children;

		// *** Privacy filter main person and spouse ***
		// privacy filter main person is already set above
		// check privacy of partner
		$person_partner=$dbh->query("SELECT * FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='$partner'");
		@$person_partnerDb=$person_partner->fetch(PDO::FETCH_OBJ);
		
		// *** Proces spouse using a clas ***
		$partner_cls = New person_cls;
		$partner_cls->construct($person_partnerDb);

		// *** Proces marriage using a class ***
		$marriage_cls = New marriage_cls;
		$marriage_cls->construct($res, $man_cls->privacy, $partner_cls->privacy);
		$family_privacy=$marriage_cls->privacy;

		// *** Show full marriage details ***
		if ($family_privacy){
			// *** Show standard marriage data ***
			$var='<br>'.$marriage_cls->marriage_data($marrDb,'','short');
		}
		else{
			$var='<br>'.$marriage_cls->marriage_data($marrDb);
		}

		print '<li data-role="list-divider">'.__('Marriage').'</li>';
		print '<li><p>'.$var.'</p></li>'; // marriage date place

		// *** Show partner ***
		echo popup($partner,false);
		// *** Show person details using standard HuMo-gen function ***
		//$partner_sql=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber='".safe_text($partner)."'");
		$partner_sql=$dbh->query("SELECT * FROM humo_persons
			WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($partner)."'");
		@$partnerDb=$partner_sql->fetch(PDO::FETCH_OBJ);
		
		$partner_cls = New person_cls;
		$partner_cls->construct($partnerDb);  
		echo $partner_cls->person_data("mobile", 0);
		echo '</li>';

		// *** Children ***
		if ($children!=Null){
			print '<li data-role="list-divider">'.__('Children').'</li>';
			$child=explode(";",$children);
			//$nopict=1;
			$number=1; // 1 = show child number.
			for ($c=0; $c<=count($child)-1; $c++){
				$res2=$dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber LIKE '".safe_text($child[$c])."'");
				$info2=$res2->fetch();
				$text=$info2['pers_gedcomnumber'];
				echo popup($text,false); // father
				// *** Show person details using standard HuMo-gen function ***
				//$child_sql=$dbh->query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber='".safe_text($text)."'");
				$child_sql=$dbh->query("SELECT * FROM humo_persons
					WHERE pers_tree_id='".$tree_id."' AND pers_gedcomnumber='".safe_text($text)."'");
				@$childDb=$child_sql->fetch(PDO::FETCH_OBJ);
				$child_cls = New person_cls;
				$child_cls->construct($childDb);  
				echo $child_cls->person_data("mobile", 0);
				echo '</li>';
			}
			$number='';
		}
		//else{
		//	print '<li><p>'.__('No known children or without children').'</p></li>';
		//}
	}
} // if $marriage !=Null

		echo '</ul>';
	echo '</div>';
	echo '<div data-role="footer" data-theme="b">';
		echo '<h4>HuMo-gen GPL Licence</h4>';

include_once("footer.php");
?>