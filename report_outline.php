<?php 
//==================================================================
//===            OUTLINE REPORT  - report_outline.php            ===
//=== by Yossi Beck - Nov 2008 - (on basis of Huub's family.php) ===
//=== Jul 2011 Huub: translation of variables to English         ===
//==================================================================
@set_time_limit(300);

global $show_date, $dates_behind_names, $nr_generations;
global $screen_mode, $language, $db, $humo_option, $user, $selected_language;

// Check for PDF screen mode
$screen_mode=''; 
if (isset($_POST["screen_mode"]) AND ($_POST["screen_mode"]=='PDF-L' OR $_POST["screen_mode"]=='PDF-P')){ $screen_mode='PDF'; }

include_once("header.php"); // returns CMS_ROOTPATH constant

if (isset($_GET['database'])){
	// *** Check if family tree exists ***
	//$datasql = mysql_query("SELECT * FROM humo_trees WHERE tree_prefix='".$_GET['database']."'",$db);
	//if (@mysql_num_rows($datasql)==1) { $_SESSION['tree_prefix']=$_GET['database']; }
	$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='".$_GET['database']."'");
	if ($datasql->rowCount()==1) { $_SESSION['tree_prefix']=$_GET['database']; }
}

include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/language_event.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/process_text.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/marriage_cls.php");
include_once(CMS_ROOTPATH."include/show_sources.php");
 
if($screen_mode!='PDF') {  //we can't have a menu in pdf...
	include_once(CMS_ROOTPATH."menu.php");
} 
else {
	if (isset($_SESSION['tree_prefix'])){
		$dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND humo_tree_texts.treetext_language='".$selected_language."'
			WHERE tree_prefix='".$_SESSION['tree_prefix']."'";
		//@$datasql = mysql_query($dataqry,$db);
		//@$dataDb=mysql_fetch_object($datasql);
		@$datasql = $dbh->query($dataqry);
		@$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
	}
}

// *** Family gedcomnumber ***
$family_id=1; // *** Default: show 1st family ***
if (isset($urlpart[1])){ $family_id=$urlpart[1]; }
if (isset($_GET["id"])){ $family_id=$_GET["id"]; }
if (isset($_POST["id"])){ $family_id=$_POST["id"]; }

// *** Person gedcomnumber (backwards compatible) ***
$main_person=''; // *** Mainperson of family ***
if (isset($urlpart[2])){ $main_person=$urlpart[2];}
if (isset($_GET["main_person"])){ $main_person=$_GET["main_person"]; }
if (isset($_POST["main_person"])){ $main_person=$_POST["main_person"]; }

$show_date=true;
if (isset($_GET["show_date"])){ $show_date=$_GET["show_date"];}
if (isset($_POST["show_date"])){ $show_date=$_POST["show_date"];}

$dates_behind_names=true;
if (isset($_GET["dates_behind_names"])){ $dates_behind_names=$_GET["dates_behind_names"];}
if (isset($_POST["dates_behind_names"])){ $dates_behind_names=$_POST["dates_behind_names"];}

// **********************************************************
// *** Maximum number of generations in descendant_report ***
// **********************************************************
$nr_generations=($humo_option["descendant_generations"]-1);
if (isset($_GET["nr_generations"])){ $nr_generations=$_GET["nr_generations"];}
if (isset($_POST["nr_generations"])){ $nr_generations=$_POST["nr_generations"];}

if($screen_mode=='PDF') {  
	//initialize pdf generation
	$pdfdetails=array();
	$pdf_marriage=array();
	$pdf=new PDF();
	//$pers=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person WHERE pers_gedcomnumber='$main_person'",$db);
	//@$persDb=mysql_fetch_object($pers);
	$pers=$dbh->query("SELECT * FROM ".$_SESSION['tree_prefix']."person WHERE pers_gedcomnumber='$main_person'");
	@$persDb = $pers->fetch(PDO::FETCH_OBJ);
	// *** Use person class ***
	$pers_cls = New person_cls;
	$pers_cls->construct($persDb);
	$name=$pers_cls->person_name($persDb);
	$title=pdf_convert(__('Outline report').__(' of ').$name["standard_name"]);
	$pdf->SetTitle($title);
	$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
	if(isset($_POST["screen_mode"]) AND $_POST["screen_mode"]=="PDF-L") { $pdf->AddPage("L"); } 
	else { $pdf->AddPage("P"); }
	$pdf->SetFont('Arial','B',15);
	$pdf->Ln(4);
	$name=$pers_cls->person_name($persDb);
	$pdf->MultiCell(0,10,__('Outline report').__(' of ').$name["standard_name"],0,'C');
	$pdf->Ln(4);
	$pdf->SetFont('Arial','',12);
}

if($screen_mode != "PDF") {
	
	echo '<div class="standard_header fonts">'.__('Outline report').'</div>';
	echo '<div class="pers_name center">';

	// ***************************************
	// ******** Button: Show date  ***********
	// ***************************************

	if(CMS_SPECIFIC=='Joomla') {
		$qstr='';
		if($_SERVER['QUERY_STRING'] != '') { $qstr='?'.$_SERVER['QUERY_STRING']; }
		print '<form method="POST" action="'.$_SERVER['PHP_SELF'].$qstr.'" style="display : inline;">';
	}
	else {
		print '<form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
	}
	print '<input type="hidden" name="id" value="'.$family_id.'">';
	print '<input type="hidden" name="nr_generations" value="'.$nr_generations.'">';
	print '<input type="hidden" name="main_person" value="'.$main_person.'">';

	if ($show_date==true){
		print '<input type="hidden" name="show_date" value="0">';
		print '<input class="fonts" type="Submit" name="submit" value="'.__('Hide dates').'">';
	}
	else{
		print '<input type="hidden" name="show_date" value="1">';
		print '<input class="fonts" type="Submit" name="submit" value="'.__('Show dates').'">';
	}
	print '</form>';

	// *****************************************************************
	// ******** Show button: date after or below each other ************
	// *****************************************************************

	if(CMS_SPECIFIC=='Joomla') {
		$qstr='';
		if($_SERVER['QUERY_STRING'] != '') { $qstr='?'.$_SERVER['QUERY_STRING']; }
		print '<form method="POST" action="'.$_SERVER['PHP_SELF'].$qstr.'" style="display : inline;">';
	}
	else {
		print ' <form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
	}
	print '<input type="hidden" name="id" value="'.$family_id.'">';
	print '<input type="hidden" name="nr_generations" value="'.$nr_generations.'">';
	print '<input type="hidden" name="main_person" value="'.$main_person.'">';

	if ($dates_behind_names=="1"){
		print '<input type="hidden" name="dates_behind_names" value="0">';
		print '<input type="Submit" class="fonts" name="submit" value="'.__('Dates below names').'">';
	}
	else{
		print '<input type="hidden" name="dates_behind_names" value="1">';
		print '<input type="Submit" class="fonts" name="submit" value="'.__('Dates beside names').'">';
	}
	print '</form>';

// ********************************************************
// ******** Show button: nr. of generations    ************
// ********************************************************
echo ' <span class="button fonts">';
echo __('Choose number of generations to display').': ';

echo '<select size=1 name="selectnr_generations" onChange="window.location=this.value;" style="display:inline;">';

for ($i=2;$i<20;$i++) {
	$nr_gen=$i-1;
	echo '<option';
	if($nr_gen==$nr_generations) { echo ' SELECTED';}
		if(CMS_SPECIFIC=='Joomla') {
			echo ' value="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&amp;nr_generations='.$nr_gen.'&amp;show_date='.$show_date.'&amp;dates_behind_names='.$dates_behind_names.'">'.$i.'</option>';
		}
		else {
			echo ' value="'.$_SERVER['PHP_SELF'].'?nr_generations='.$nr_gen.'&amp;id='.$family_id.'&amp;main_person='.$main_person.'&amp;show_date='.$show_date.'&amp;dates_behind_names='.$dates_behind_names.'">'.$i.'</option>';
		}
}
echo '<option';
if($nr_generations==50) { echo ' SELECTED';}

if(CMS_SPECIFIC=='Joomla') {
	echo ' value="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&amp;nr_generations=50&amp;show_date='.$show_date.'&amp;dates_behind_names='.$dates_behind_names.'">'.'ALL'.'</option>';
}
else {
	echo ' value="'.$_SERVER['PHP_SELF'].'?nr_generations=50&amp;id='.$family_id.'&amp;main_person='.$main_person.'&amp;show_date='.$show_date.'&amp;dates_behind_names='.$dates_behind_names.'"> ALL </option>';
}
echo '</select>';
echo '</span>';

echo '&nbsp;&nbsp;&nbsp;<span>';
	if($language["dir"]!="rtl") {
		//Show pdf button
		print ' <form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
		print '<input type="hidden" name="database" value="'.$_SESSION['tree_prefix'].'">';
		print '<input type="hidden" name="screen_mode" value="PDF-P">';
		print '<input type="hidden" name="id" value="'.$family_id.'">';
		print '<input type="hidden" name="nr_generations" value="'.$nr_generations.'">';
		print '<input type="hidden" name="dates_behind_names" value="'.$dates_behind_names.'">';
		print '<input type="hidden" name="show_date" value="'.$show_date.'">';
		print '<input type="hidden" name="main_person" value="'.$main_person.'">';
		print '<input class="fonts" type="Submit" name="submit" value="PDF (Portrait)">';
		print '</form>';
	}
		
echo '</span>';

echo '&nbsp;&nbsp;&nbsp;<span>';
	if($language["dir"]!="rtl") {
		//Show pdf button
		print ' <form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
		print '<input type="hidden" name="database" value="'.$_SESSION['tree_prefix'].'">';
		print '<input type="hidden" name="screen_mode" value="PDF-L">';
		print '<input type="hidden" name="id" value="'.$family_id.'">';
		print '<input type="hidden" name="nr_generations" value="'.$nr_generations.'">';
		print '<input type="hidden" name="dates_behind_names" value="'.$dates_behind_names.'">';
		print '<input type="hidden" name="show_date" value="'.$show_date.'">';
		print '<input type="hidden" name="main_person" value="'.$main_person.'">';
		print '<input class="fonts" type="Submit" name="submit" value="PDF (Landscape)">';
		print '</form>';
	}
echo '</span>';

echo '</div><br>';

} // if not PDF

$gn=0;   // generatienummer

// *************************************
// ****** FUNCTION OUTLINE *************  // recursive function
// *************************************

//some PDO prepared statements before function and loops are used
$fam_prep=$dbh->prepare("SELECT fam_man, fam_woman FROM ".safe_text($_SESSION['tree_prefix']).'family WHERE fam_gedcomnumber=?');
$fam_prep->bindParam(1,$fam_prep_var);
$pers_prep=$dbh->prepare("SELECT pers_fams FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber=?");
$pers_prep->bindParam(1,$pers_prep_var);
$fam_all_prep=$dbh->prepare("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."family WHERE fam_gedcomnumber=?");
$fam_all_prep->bindParam(1,$fam_all_prep_var);
$pers_all_prep=$dbh->prepare("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person WHERE pers_gedcomnumber=?");
$pers_all_prep->bindParam(1,$pers_all_prep_var);

function outline($family_id,$main_person,$gn,$nr_generations) {

	global $db, $show_date, $dates_behind_names, $nr_generations ;
	global $language, $dirmark1, $dirmark1;
	global $screen_mode, $pdf;
	global $dbh, $fam_prep, $pers_prep, $fam_all_prep, $pers_all_prep;
	global $fam_prep_var, $pers_prep_var, $fam_all_prep_var, $pers_all_prep_var;
	
	$family_nr=1; //*** Process multiple families ***

	if($nr_generations<$gn) {return;}
	$gn++;

	// *** Count marriages of man ***
	// *** YB: if needed show woman as main_person ***
	//$family=mysql_query("SELECT fam_man, fam_woman FROM ".$_SESSION['tree_prefix'].'family
	//	WHERE fam_gedcomnumber="'.$family_id.'"',$db);
	//$die_message=__('No valid family number');
	//@$familyDb=mysql_fetch_object($family) or die("$die_message");
	$fam_prep_var = $family_id;
	$fam_prep->execute();
	try {
		@$familyDb = $fam_prep->fetch(PDO::FETCH_OBJ);
	} catch(PDOException $e) {
		echo __('No valid family number');
	}

	$parent1=''; $parent2='';	$change_main_person=false;

	// *** Standard main_person is the father ***
	if ($familyDb->fam_man){
		$parent1=$familyDb->fam_man;
	}
	// *** If mother is selected, mother will be main_person ***
	if ($familyDb->fam_woman==$main_person){
		$parent1=$familyDb->fam_woman;
		$change_main_person=true;
	}

	// *** Check family with parent1: N.N. ***
	if ($parent1){
		// *** Save man's families in array ***
		//$person_qry=mysql_query("SELECT pers_fams FROM ".$_SESSION['tree_prefix']."person
		//	WHERE pers_gedcomnumber='$parent1'",$db);
		//@$personDb=mysql_fetch_object($person_qry);
		$pers_prep_var = $parent1;
		$pers_prep->execute();
		@$personDb = $pers_prep->fetch(PDO::FETCH_OBJ);
		$marriage_array=explode(";",$personDb->pers_fams);
		$nr_families=substr_count($personDb->pers_fams, ";");
	}
	else{
		$marriage_array[0]=$family_id;
		$nr_families="0";
	}

	// *** Loop multiple marriages of main_person ***
	for ($parent1_marr=0; $parent1_marr<=$nr_families; $parent1_marr++){
		$id=$marriage_array[$parent1_marr];
		//$family=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."family WHERE fam_gedcomnumber='$id'",$db);
		//@$familyDb=mysql_fetch_object($family);
		$fam_all_prep_var = $id;
		$fam_all_prep->execute();
		@$familyDb = $fam_all_prep->fetch(PDO::FETCH_OBJ);

		// *** Raise statistics counter ***
		// Not in use in huge reports!
		//$Tel=$familyDb->fam_counter+1;
		//$sql="UPDATE ".$_SESSION['tree_prefix']."family SET fam_counter=$Tel WHERE fam_gedcomnumber='$id'";
		//mysql_query($sql, $db) or die(mysql_error());

		// *** Privacy filter man and woman ***
		//$person_man=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person WHERE pers_gedcomnumber='$familyDb->fam_man'",$db);
		//@$person_manDb=mysql_fetch_object($person_man);
		$pers_all_prep_var = $familyDb->fam_man;
		$pers_all_prep->execute();
		@$person_manDb = $pers_all_prep->fetch(PDO::FETCH_OBJ);
		$man_cls = New person_cls;
		$man_cls->construct($person_manDb);
		$privacy_man=$man_cls->privacy;

		//$person_woman=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person WHERE pers_gedcomnumber='$familyDb->fam_woman'",$db);
		//@$person_womanDb=mysql_fetch_object($person_woman);
		$pers_all_prep_var = $familyDb->fam_woman;
		$pers_all_prep->execute();
		@$person_womanDb = $pers_all_prep->fetch(PDO::FETCH_OBJ);		
		$woman_cls = New person_cls;
		$woman_cls->construct($person_womanDb);
		$privacy_woman=$woman_cls->privacy;

		$marriage_cls = New marriage_cls;
		$marriage_cls->construct($familyDb, $privacy_man, $privacy_woman);
		$familylevend=$marriage_cls->privacy;

		// *************************************************************
		// *** Parent1 (normally the father)                         ***
		// *************************************************************
		if ($familyDb->fam_kind!='PRO-GEN'){  //onecht kind, vrouw zonder man
			if ($family_nr==1){
				// *** Show data of man ***

				$dir="";
				if($language["dir"]=="rtl") {
					$dir="rtl";    // in the following code calls the css indentation for rtl pages: "div.rtlsub2" instead of "div.sub2"
				}

				$indent=$dir.'sub'.$gn;  // hier wordt de indent bepaald voor de namen div class (sub1, sub2 enz. die in gedcom.css staan)
				if($screen_mode != "PDF") {
					echo '<div class="'.$indent.'">';
					echo '<b>'.$gn.' </b>';
				}
				else {
					$pdf->SetLeftMargin($gn*10);
					$pdf->Write(8,"\n");
					$pdf->Write(8,$gn.'  ');
				}
				if ($change_main_person==true){
					if($screen_mode != "PDF") {
						echo $woman_cls->name_extended("outline");
					}
					else {
						$pdf->SetFont('Arial','B',12);
						$pdf->Write(8,$woman_cls->name_extended("outline"));
						$pdf->SetFont('Arial','',12);
					}
					if ($show_date=="1" AND !$privacy_woman) {
						if($screen_mode != "PDF") {
							echo $dirmark1.',';
							if($dates_behind_names==false) {echo '<br>';}
							echo ' &nbsp; ('.language_date($person_womanDb->pers_birth_date).' - '.language_date($person_womanDb->pers_death_date).')';
						}
						else {
							if($dates_behind_names==false) {
								$pdf->SetLeftMargin($gn*10+4);
								$pdf->Write(8,"\n");
							}
							$pdf->Write(8,' ('.language_date($person_womanDb->pers_birth_date).' - '.language_date($person_womanDb->pers_death_date).')');
						}
					}
				}
				else{
					if($screen_mode != "PDF") {
						echo $man_cls->name_extended("outline");
					}
					else {
						$pdf->SetFont('Arial','B',12);
						$pdf->Write(8,$man_cls->name_extended("outline"));
						$pdf->SetFont('Arial','',12);
					}
					if ($show_date=="1" AND !$privacy_man) {
						if($screen_mode != "PDF") {
							echo $dirmark1.',';
							if($dates_behind_names==false) {echo '<br>';}
							echo ' &nbsp; ('.language_date($person_manDb->pers_birth_date).' - '.language_date($person_manDb->pers_death_date).')';
						}
						else {
							if($dates_behind_names==false) {
								$pdf->SetLeftMargin($gn*10+4);
								$pdf->Write(8,"\n");
							}
							$pdf->Write(8,' ('.language_date($person_manDb->pers_birth_date).' - '.language_date($person_manDb->pers_death_date).')');
						}
					}
				}
				if($screen_mode != "PDF") {
					echo '</div>';
				}
			}
			else{  }   // empty: no second show of data of main_person in outline report
			$family_nr++;
		} // *** end check of PRO-GEN ***

		// *************************************************************
		// *** Parent2 (normally the mother)                         ***
		// *************************************************************
		if($screen_mode != "PDF") {
			echo '<div class="'.$indent.'" style="font-style:italic">';
			echo ' x '.$dirmark1;
		}
		else {
			$pdf->SetLeftMargin($gn*10);
			$pdf->Write(8,"\n");
			$pdf->Write(8,'x  ');
		}
		if ($change_main_person==true){
			if($screen_mode != "PDF") {
				echo $man_cls->name_extended("outline");
			}
			else {
				$pdf->SetFont('Arial','BI',12);
				$pdf->Write(8,$man_cls->name_extended("outline"));
				$pdf->SetFont('Arial','',12);
			}
			if ($show_date=="1" AND !$privacy_man) {
				if($screen_mode != "PDF") {
					echo $dirmark1.',';
					if($dates_behind_names==false) {echo '<br>';}
					echo ' &nbsp; ('.@language_date($person_manDb->pers_birth_date).' - '.@language_date($person_manDb->pers_death_date).')';
				}
				else {
					if($dates_behind_names==false) {
						$pdf->SetLeftMargin($gn*10+4);
						$pdf->Write(8,"\n");
					}
					$pdf->Write(8,' ('.language_date($person_manDb->pers_birth_date).' - '.language_date($person_manDb->pers_death_date).')');
				}
			}
		}
		else{
			if($screen_mode != "PDF") {
				echo $woman_cls->name_extended("outline");
			}
			else {
				$pdf->SetFont('Arial','BI',12);
				$pdf->Write(8,$woman_cls->name_extended("outline"));
				$pdf->SetFont('Arial','',12);
			}
			if ($show_date=="1" AND !$privacy_woman) {
				if($screen_mode != "PDF") {
					echo $dirmark1.',';
					if($dates_behind_names==false) {echo '<br>';}
					echo ' &nbsp; ('.@language_date($person_womanDb->pers_birth_date).' - '.@language_date($person_womanDb->pers_death_date).')';
				}
				else {
					if($dates_behind_names==false) {
						$pdf->SetLeftMargin($gn*10+4);
						$pdf->Write(8,"\n");
					}
					$pdf->Write(8,' ('.language_date($person_womanDb->pers_birth_date).' - '.language_date($person_womanDb->pers_death_date).')');
				}
			}
		}
		if($screen_mode != "PDF") {
			echo '</div>';
		}

		// *************************************************************
		// *** Children                                              ***
		// *************************************************************
		if ($familyDb->fam_children){
			$childnr=1;
			$child_array=explode(";",$familyDb->fam_children);

			for ($i=0; $i<=substr_count("$familyDb->fam_children", ";"); $i++){
				//$child=mysql_query("SELECT * FROM ".$_SESSION['tree_prefix']."person
				//	WHERE pers_gedcomnumber='$child_array[$i]'",$db);
				//@$childDb=mysql_fetch_object($child);
				$pers_all_prep_var = $child_array[$i];
				$pers_all_prep->execute();
				@$childDb = $pers_all_prep->fetch(PDO::FETCH_OBJ);

				$child_privacy="";
				$child_cls = New person_cls;
				$child_cls->construct($childDb);
				$child_privacy=$child_cls->privacy;

				// *** Build descendant_report ***
				if ($childDb->pers_fams){
					// *** 1e family of child ***
					$child_family=explode(";",$childDb->pers_fams);
					$child1stfam=$child_family[0];
					outline($child1stfam,$childDb->pers_gedcomnumber,$gn,$nr_generations);  // recursive
				}
				else{    // Child without own family
					if($nr_generations>=$gn) {
						$childgn=$gn+1;
						$childindent=$dir.'sub'.$childgn;
						if($screen_mode != "PDF") {
							echo '<div class="'.$childindent.'">';
							echo '<b>'.$childgn.' '.'</b>';
							echo $child_cls->name_extended("outline");
						}
						else {
							$pdf->SetLeftMargin($childgn*10);
							$pdf->Write(8,"\n");
							$pdf->Write(8,$childgn.'  ');
							$pdf->SetFont('Arial','B',12);
							$pdf->Write(8,$child_cls->name_extended("outline"));
							$pdf->SetFont('Arial','',12);
						}
						if ($show_date=="1" AND !$child_privacy) {
							if($screen_mode != "PDF") {
								echo $dirmark1.',';
								if($dates_behind_names==false) {echo '<br>';}
								echo ' &nbsp; ('.language_date($childDb->pers_birth_date).' - '.language_date($childDb->pers_death_date).')';
							}
							else {
								if($dates_behind_names==false) {
									$pdf->SetLeftMargin($childgn*10+4);
									$pdf->Write(8,"\n");
								}
								$pdf->Write(8,' ('.language_date($childDb->pers_birth_date).' - '.language_date($childDb->pers_death_date).')');
							}
						}
						if($screen_mode != "PDF") {
							echo '</div>';
						}
					}
				}
				if($screen_mode != "PDF") { echo "\n"; }
				else {}
				$childnr++;
			}
		}


	} // Show  multiple marriages

} // End of outline function


// ******* Start function here - recursive if started ******
if($screen_mode != 'PDF') {
echo '<table class="humo outlinetable"><tr><td>';
}
	outline($family_id, $main_person, $gn, $nr_generations);
if($screen_mode != 'PDF') {
echo '</td></tr></table>';
}

//include_once(CMS_ROOTPATH."footer.php");

if($screen_mode != 'PDF') {
	include_once(CMS_ROOTPATH."footer.php");
}
else {
	$pdf->Output($title.".pdf","I");
}
?>