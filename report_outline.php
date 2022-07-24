<?php 
// error_reporting(E_ALL);

//==================================================================
//===            OUTLINE REPORT  - report_outline.php            ===
//=== by Yossi Beck - Nov 2008 - (on basis of Huub's family.php) ===
//=== Jul 2011 Huub: translation of variables to English         ===
//==================================================================
@set_time_limit(300);

global $show_date, $dates_behind_names, $nr_generations;
global $screen_mode, $language, $humo_option, $user, $selected_language;

// Check for PDF screen mode
$screen_mode=''; 
if (isset($_POST["screen_mode"]) AND ($_POST["screen_mode"]=='PDF-L' OR $_POST["screen_mode"]=='PDF-P')){ $screen_mode='PDF'; }

include_once("header.php"); // returns CMS_ROOTPATH constant

include_once(CMS_ROOTPATH."include/language_date.php");
include_once(CMS_ROOTPATH."include/language_event.php");
include_once(CMS_ROOTPATH."include/date_place.php");
include_once(CMS_ROOTPATH."include/process_text.php");
include_once(CMS_ROOTPATH."include/person_cls.php");
include_once(CMS_ROOTPATH."include/marriage_cls.php");

// TRY OUTLINE WITH DETAILS
include_once(CMS_ROOTPATH."include/show_sources.php"); // *** No sources in use in outline report ***
include_once(CMS_ROOTPATH."include/show_addresses.php");
include_once(CMS_ROOTPATH."include/witness.php");
include_once(CMS_ROOTPATH."include/show_picture.php");
include_once(CMS_ROOTPATH."include/calculate_age_cls.php");

if($screen_mode!='PDF') {  //we can't have a menu in pdf...
	include_once(CMS_ROOTPATH."menu.php");
} 
else {
	if (isset($_SESSION['tree_prefix'])){
		$dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
			ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
			AND humo_tree_texts.treetext_language='".$selected_language."'
			WHERE tree_prefix='".$tree_prefix_quoted."'";
		@$datasql = $dbh->query($dataqry);
		@$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
		$tree_id=$dataDb->tree_id;
	}

	include_once(CMS_ROOTPATH."include/db_functions_cls.php");
	$db_functions = New db_functions;
	$db_functions->set_tree_id($tree_id);
}

// *** Family gedcomnumber ***
$family_id='F1'; // *** Default: show 1st family ***
//if (isset($urlpart[1])){ $family_id=$urlpart[1]; }
if (isset($_GET["id"])){ $family_id=$_GET["id"]; }
if (isset($_POST["id"])){ $family_id=$_POST["id"]; }
// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($family_id);

// *** Person gedcomnumber (backwards compatible) ***
$main_person=''; // *** Mainperson of family ***
//if (isset($urlpart[2])){ $main_person=$urlpart[2];}
if (isset($_GET["main_person"])){ $main_person=$_GET["main_person"]; }
if (isset($_POST["main_person"])){ $main_person=$_POST["main_person"]; }
// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($main_person);

$show_details=false;
if (isset($_GET["show_details"])){ $show_details=$_GET["show_details"];}
if (isset($_POST["show_details"])){ $show_details=$_POST["show_details"];}

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
	@$persDb = $db_functions->get_person($main_person);
	// *** Use person class ***
	$pers_cls = New person_cls;
	$pers_cls->construct($persDb);
	$name=$pers_cls->person_name($persDb);
	$title=pdf_convert(__('Outline report').__(' of ').$name["standard_name"]);

	$pdf=new PDF();
	$pdf->SetTitle($title);
	$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
	if(isset($_POST["screen_mode"]) AND $_POST["screen_mode"]=="PDF-L") { $pdf->AddPage("L"); } 
	else { $pdf->AddPage("P"); }
	$pdf->SetFont('Arial','B',15);
	$pdf->Ln(4);
	$pdf->MultiCell(0,10,__('Outline report').__(' of ').$name["standard_name"],0,'C');
	$pdf->Ln(4);
	$pdf->SetFont('Arial','',12);
}

if($screen_mode != "PDF") {
	echo '<div class="standard_header fonts">'.__('Outline report').'</div>';
	echo '<div class="pers_name center print_version">';


	// ******************************************************
	// ******** Button: Show full details (book)  ***********
	// ******************************************************

	if(CMS_SPECIFIC=='Joomla') {
		$qstr='';
		if($_SERVER['QUERY_STRING'] != '') { $qstr='?'.$_SERVER['QUERY_STRING']; }
		print '<form method="POST" action="report_outline.php'.$qstr.'" style="display : inline;">';
	}
	else {
		//print '<form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
		print '<form method="POST" action="report_outline.php" style="display : inline;">';
	}
	print '<input type="hidden" name="id" value="'.$family_id.'">';
	print '<input type="hidden" name="nr_generations" value="'.$nr_generations.'">';
	print '<input type="hidden" name="main_person" value="'.$main_person.'">';

	if ($show_details==true){
		print '<input type="hidden" name="show_details" value="0">';
		print '<input class="fonts" type="Submit" name="submit" value="'.__('Hide full details').'">';
	}
	else{
		print '<input type="hidden" name="show_details" value="1">';
		print '<input class="fonts" type="Submit" name="submit" value="'.__('Show full details').'">';
	}
	print '</form>&nbsp;';

	if(!$show_details) {
	   
		// ***************************************
		// ******** Button: Show date  ***********
		// ***************************************

		if(CMS_SPECIFIC=='Joomla') {
			$qstr='';
			if($_SERVER['QUERY_STRING'] != '') { $qstr='?'.$_SERVER['QUERY_STRING']; }
			print '<form method="POST" action="report_outline.php'.$qstr.'" style="display : inline;">';
		}
		else {
			//print '<form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
			print '<form method="POST" action="report_outline.php" style="display : inline;">';
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
			print '<form method="POST" action="report_outline.php'.$qstr.'" style="display : inline;">';
		}
		else {
			print ' <form method="POST" action="report_outline.php" style="display : inline;">';
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
	}

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
				echo ' value="report_outline.php?'.$_SERVER['QUERY_STRING'].'&amp;nr_generations='.$nr_gen.'&amp;show_details='.$show_details.'&amp;show_date='.$show_date.'&amp;dates_behind_names='.$dates_behind_names.'">'.$i.'</option>';
			}
			else {
				echo ' value="report_outline.php?nr_generations='.$nr_gen.'&amp;id='.$family_id.'&amp;main_person='.$main_person.'&amp;show_details='.$show_details.'&amp;show_date='.$show_date.'&amp;dates_behind_names='.$dates_behind_names.'">'.$i.'</option>';
			}
	}
	echo '<option';
	if($nr_generations==50) { echo ' SELECTED';}

	if(CMS_SPECIFIC=='Joomla') {
		echo ' value="report_outline.php?'.$_SERVER['QUERY_STRING'].'&amp;nr_generations=50&amp;show_date='.$show_date.'&amp;dates_behind_names='.$dates_behind_names.'">'.'ALL'.'</option>';
	}
	else {
		echo ' value="report_outline.php?nr_generations=50&amp;id='.$family_id.'&amp;main_person='.$main_person.'&amp;show_date='.$show_date.'&amp;dates_behind_names='.$dates_behind_names.'"> ALL </option>';
	}
	echo '</select>';
	echo '</span>';

	if(!$show_details) {

		echo '&nbsp;&nbsp;&nbsp;<span>';
		//if($language["dir"]!="rtl") {
		if($user["group_pdf_button"]=='y' AND $language["dir"]!="rtl" AND $language["name"]!="简体中文") {
			//Show pdf button
			print ' <form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
			print '<input type="hidden" name="database" value="'.$_SESSION['tree_prefix'].'">';
			print '<input type="hidden" name="screen_mode" value="PDF-P">';
			print '<input type="hidden" name="id" value="'.$family_id.'">';
			print '<input type="hidden" name="nr_generations" value="'.$nr_generations.'">';
			print '<input type="hidden" name="dates_behind_names" value="'.$dates_behind_names.'">';
			print '<input type="hidden" name="show_date" value="'.$show_date.'">';
			print '<input type="hidden" name="main_person" value="'.$main_person.'">';
			print '<input class="fonts" type="Submit" name="submit" value="'.__('PDF (Portrait)').'">';
			print '</form>';
		}

		echo '</span>';

		echo '&nbsp;&nbsp;&nbsp;<span>';
		//if($language["dir"]!="rtl") {
		if($user["group_pdf_button"]=='y' AND $language["dir"]!="rtl" AND $language["name"]!="简体中文") {
			//Show pdf button
			print ' <form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
			print '<input type="hidden" name="database" value="'.$_SESSION['tree_prefix'].'">';
			print '<input type="hidden" name="screen_mode" value="PDF-L">';
			print '<input type="hidden" name="id" value="'.$family_id.'">';
			print '<input type="hidden" name="nr_generations" value="'.$nr_generations.'">';
			print '<input type="hidden" name="dates_behind_names" value="'.$dates_behind_names.'">';
			print '<input type="hidden" name="show_date" value="'.$show_date.'">';
			print '<input type="hidden" name="main_person" value="'.$main_person.'">';
			print '<input class="fonts" type="Submit" name="submit" value="'.__('PDF (Landscape)').'">';
			print '</form>';
		}
		echo '</span>';
	}

	echo '</div><br>';

} // if not PDF

$gn=0;   // generatienummer

// *************************************
// ****** FUNCTION OUTLINE *************  // recursive function
// *************************************

function outline($family_id,$main_person,$gn,$nr_generations) {
	global $dbh, $db_functions, $tree_prefix_quoted, $pdf, $show_details, $show_date, $dates_behind_names, $nr_generations;
	global $language, $dirmark1, $dirmark1, $screen_mode;

	$family_nr=1; //*** Process multiple families ***

	if($nr_generations<$gn) {return;}
	$gn++;

	// *** Count marriages of man ***
	// *** YB: if needed show woman as main_person ***
	@$familyDb = $db_functions->get_family($family_id,'man-woman');
	$parent1=''; $parent2='';	$swap_parent1_parent2=false;

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
		@$personDb = $db_functions->get_person($parent1,'famc-fams');
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
		$man_cls = New person_cls;
		$man_cls->construct($person_manDb);
		$privacy_man=$man_cls->privacy;

		@$person_womanDb = $db_functions->get_person($familyDb->fam_woman);
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
					echo '<span style="font-weight:bold;font-size:120%">'.$gn.' </span>';
				}
				else {
					$pdf->SetLeftMargin($gn*10);
					$pdf->Write(8,"\n");
					$pdf->Write(8,$gn.'  ');
				}
				if ($swap_parent1_parent2==true){
					if($screen_mode != "PDF") {
						echo $woman_cls->name_extended("outline");
						if($show_details AND !$privacy_woman) { echo $woman_cls->person_data("outline",$familyDb->fam_gedcomnumber); }
					}
					else {
						$pdf->SetFont('Arial','B',12);
						$pdf->Write(8,$woman_cls->name_extended("outline"));
						$pdf->SetFont('Arial','',12);
					}
					if ($show_date=="1" AND !$privacy_woman AND !$show_details) {
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
						if($show_details AND !$privacy_man) { echo $man_cls->person_data("outline",$familyDb->fam_gedcomnumber); }

					}
					else {
						$pdf->SetFont('Arial','B',12);
						$pdf->Write(8,$man_cls->name_extended("outline"));
						$pdf->SetFont('Arial','',12);
					}
					if ($show_date=="1" AND !$privacy_man AND !$show_details) {
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
			if(!$show_details) {
				echo ' x '.$dirmark1;
			}
			else {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				if($parent1_marr==0) {
					if($familylevend) {echo $marriage_cls->marriage_data($familyDb,'','short')."<br>"; }
					else { echo $marriage_cls->marriage_data()."<br>";  }
				}
				else {
					echo $marriage_cls->marriage_data($familyDb,$parent1_marr+1,'shorter').' <br>';
				}
			}
		}
		else {
			$pdf->SetLeftMargin($gn*10);
			$pdf->Write(8,"\n");
			$pdf->Write(8,'x  ');
		}
		if ($swap_parent1_parent2==true){
			if($screen_mode != "PDF") {
				if($show_details) { echo "&nbsp;&nbsp;&nbsp;&nbsp;"; }
				echo $man_cls->name_extended("outline");
				if($show_details AND !$privacy_man) { echo $man_cls->person_data("outline",$familyDb->fam_gedcomnumber); }
			}
			else {
				$pdf->SetFont('Arial','BI',12);
				$pdf->Write(8,$man_cls->name_extended("outline"));
				$pdf->SetFont('Arial','',12);
			}
			if ($show_date=="1" AND !$privacy_man AND !$show_details) {
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
					$pdf->Write(8,' ('.@language_date($person_manDb->pers_birth_date).' - '.@language_date($person_manDb->pers_death_date).')');
				}
			}
		}
		else{
			if($screen_mode != "PDF") {
				if($show_details) { echo "&nbsp;&nbsp;&nbsp;&nbsp;"; }
				echo $woman_cls->name_extended("outline");
				if($show_details AND !$privacy_woman) { echo $woman_cls->person_data("outline",$familyDb->fam_gedcomnumber); }
			}
			else {
				$pdf->SetFont('Arial','BI',12);
				$pdf->Write(8,$woman_cls->name_extended("outline"));
				$pdf->SetFont('Arial','',12);
			}
			if ($show_date=="1" AND !$privacy_woman AND !$show_details) {
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
					$pdf->Write(8,' ('.@language_date($person_womanDb->pers_birth_date).' - '.@language_date($person_womanDb->pers_death_date).')');
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
				@$childDb = $db_functions->get_person($child_array[$i]);
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
							echo '<span style="font-weight:bold;font-size:120%">'.$childgn.' '.'</span>';
							echo $child_cls->name_extended("outline");
							if($show_details AND !$child_privacy) { echo $child_cls->person_data("outline",""); }
						}
						else {
							$pdf->SetLeftMargin($childgn*10);
							$pdf->Write(8,"\n");
							$pdf->Write(8,$childgn.'  ');
							$pdf->SetFont('Arial','B',12);
							$pdf->Write(8,$child_cls->name_extended("outline"));
							$pdf->SetFont('Arial','',12);
						}
						if ($show_date=="1" AND !$child_privacy AND !$show_details) {
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

if($screen_mode != 'PDF') {
	include_once(CMS_ROOTPATH."footer.php");
}
else {
	$pdf->Output($title.".pdf","I");
}
?>