<?php
// TODO: @Devs add a button link for PDF to /pdf_report_outline

global $show_date, $dates_behind_names, $nr_generations;
global $screen_mode, $language, $humo_option, $user, $selected_language;

include_once __DIR__ . '/header.php'; // returns CMS_ROOTPATH constant

include_once __DIR__ . '/include/language_date.php';
include_once __DIR__ . '/include/language_event.php';
include_once __DIR__ . '/include/date_place.php';
include_once __DIR__ . '/include/process_text.php';
include_once __DIR__ . '/include/person_cls.php';
include_once __DIR__ . '/include/marriage_cls.php';

// TRY OUTLINE WITH DETAILS
include_once __DIR__ . '/include/show_sources.php'; // *** No sources in use in outline report ***
include_once __DIR__ . '/include/show_addresses.php';
include_once __DIR__ . '/include/witness.php';
include_once __DIR__ . '/include/show_picture.php';
include_once __DIR__ . '/include/calculate_age_cls.php';


/* if (isset($_SESSION['tree_prefix'])) {
	$dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
		ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
		AND humo_tree_texts.treetext_language='" . $selected_language . "'
		WHERE tree_prefix='" . $tree_prefix_quoted . "'";
	@$datasql = $dbh->query($dataqry);
	@$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
	$tree_id = $dataDb->tree_id;
}

include_once __DIR__ . '/include/db_functions_cls.php';
$db_functions = new db_functions;
$db_functions->set_tree_id($tree_id); */


// DEFAULT ==============================
$family_id = 'F1'; // Default: show 1st family
$main_person = ''; // Mainperson of family
$show_details = false;
$show_date = true;
$dates_behind_names = true;
$nr_generations = ($humo_option["descendant_generations"] - 1);
$gn = 0;   // generatienummer

// REQUEST =============================
// TODO: @Devs: Need to choose only on method for all requests!
if (isset($_GET["id"])) {
	$family_id = $_GET["id"];
}
if (isset($_POST["id"])) {
	$family_id = $_POST["id"];
}

if (isset($_GET["main_person"])) {
	$main_person = $_GET["main_person"];
}
if (isset($_POST["main_person"])) {
	$main_person = $_POST["main_person"];
}

if (isset($_GET["show_details"])) {
	$show_details = $_GET["show_details"];
}
if (isset($_POST["show_details"])) {
	$show_details = $_POST["show_details"];
}

if (isset($_GET["show_date"])) {
	$show_date = $_GET["show_date"];
}
if (isset($_POST["show_date"])) {
	$show_date = $_POST["show_date"];
}

if (isset($_GET["dates_behind_names"])) {
	$dates_behind_names = $_GET["dates_behind_names"];
}
if (isset($_POST["dates_behind_names"])) {
	$dates_behind_names = $_POST["dates_behind_names"];
}

if (isset($_GET["nr_generations"])) {
	$nr_generations = $_GET["nr_generations"];
}
if (isset($_POST["nr_generations"])) {
	$nr_generations = $_POST["nr_generations"];
}

$db_functions->check_family($family_id); // Check if family gedcomnumber is valid
$db_functions->check_person($main_person); // Check if person gedcomnumber is valid



// **********************************************************
// *** Maximum number of generations in descendant_report ***
// **********************************************************

echo '<div class="standard_header fonts">' . __('Outline report') . '</div>';
echo '<div class="pers_name center print_version">';


// ******************************************************
// ******** Button: Show full details (book)  ***********
// ******************************************************

if (CMS_SPECIFIC == 'Joomla') {
	$qstr = '';
	if ($_SERVER['QUERY_STRING'] != '') {
		$qstr = '?' . $_SERVER['QUERY_STRING'];
	}
	echo '<form method="POST" action="report_outline.php' . $qstr . '" style="display : inline;">';
} else {
	//echo '<form method="POST" action="'.$uri_path.'report_outline.php" style="display : inline;">';
	echo '<form method="POST" action="report_outline.php" style="display : inline;">';
}
echo '<input type="hidden" name="id" value="' . $family_id . '">';
echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
echo '<input type="hidden" name="main_person" value="' . $main_person . '">';

if ($show_details == true) {
	echo '<input type="hidden" name="show_details" value="0">';
	echo '<input class="fonts" type="Submit" name="submit" value="' . __('Hide full details') . '">';
} else {
	echo '<input type="hidden" name="show_details" value="1">';
	echo '<input class="fonts" type="Submit" name="submit" value="' . __('Show full details') . '">';
}
echo '</form>&nbsp;';

if (!$show_details) {

	// ***************************************
	// ******** Button: Show date  ***********
	// ***************************************

	echo '<form method="POST" action="/report_outline.php" style="display : inline;">';
	echo '<input type="hidden" name="id" value="' . $family_id . '">';
	echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
	echo '<input type="hidden" name="main_person" value="' . $main_person . '">';

	if ($show_date == true) {
		echo '<input type="hidden" name="show_date" value="0">';
		echo '<input class="fonts" type="Submit" name="submit" value="' . __('Hide dates') . '">';
	} else {
		echo '<input type="hidden" name="show_date" value="1">';
		echo '<input class="fonts" type="Submit" name="submit" value="' . __('Show dates') . '">';
	}
	echo '</form>';

	// *****************************************************************
	// ******** Show button: date after or below each other ************
	// *****************************************************************

	echo '<form method="POST" action="/report_outline.php" style="display : inline;">';
	echo '<input type="hidden" name="id" value="' . $family_id . '">';
	echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
	echo '<input type="hidden" name="main_person" value="' . $main_person . '">';

	if ($dates_behind_names == "1") {
		echo '<input type="hidden" name="dates_behind_names" value="0">';
		echo '<input type="Submit" class="fonts" name="submit" value="' . __('Dates below names') . '">';
	} else {
		echo '<input type="hidden" name="dates_behind_names" value="1">';
		echo '<input type="Submit" class="fonts" name="submit" value="' . __('Dates beside names') . '">';
	}
	echo '</form>';
}

// ********************************************************
// ******** Show button: nr. of generations    ************
// ********************************************************
echo ' <span class="button fonts">';
echo __('Choose number of generations to display') . ': ';

echo '<select size=1 name="selectnr_generations" onChange="window.location=this.value;" style="display:inline;">';

for ($i = 2; $i < 20; $i++) {
	$nr_gen = $i - 1;
	echo '<option';
	if ($nr_gen == $nr_generations) {
		echo ' SELECTED';
	}
	echo ' value="/report_outline.php?nr_generations=' . $nr_gen . '&amp;id=' . $family_id . '&amp;main_person=' . $main_person . '&amp;show_details=' . $show_details . '&amp;show_date=' . $show_date . '&amp;dates_behind_names=' . $dates_behind_names . '">' . $i . '</option>';
	
}
echo '<option';
if ($nr_generations == 50) {
	echo ' SELECTED';
}

echo ' value="/report_outline.php?nr_generations=50&amp;id=' . $family_id . '&amp;main_person=' . $main_person . '&amp;show_date=' . $show_date . '&amp;dates_behind_names=' . $dates_behind_names . '"> ALL </option>';
echo '</select>';
echo '</span>';

if (!$show_details) {

	echo '&nbsp;&nbsp;&nbsp;<span>';
	//if($language["dir"]!="rtl") {
	if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") {
		//Show pdf button
		echo ' <form method="POST" action="' . $uri_path . 'report_outline.php" style="display : inline;">';
		echo '<input type="hidden" name="database" value="' . $_SESSION['tree_prefix'] . '">';
		echo '<input type="hidden" name="screen_mode" value="PDF-P">';
		echo '<input type="hidden" name="id" value="' . $family_id . '">';
		echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
		echo '<input type="hidden" name="dates_behind_names" value="' . $dates_behind_names . '">';
		echo '<input type="hidden" name="show_date" value="' . $show_date . '">';
		echo '<input type="hidden" name="main_person" value="' . $main_person . '">';
		echo '<input class="fonts" type="Submit" name="submit" value="' . __('PDF (Portrait)') . '">';
		echo '</form>';
	}

	echo '</span>';

	echo '&nbsp;&nbsp;&nbsp;<span>';
	//if($language["dir"]!="rtl") {
	if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") {
		//Show pdf button
		echo ' <form method="POST" action="' . $uri_path . 'report_outline.php" style="display : inline;">';
		echo '<input type="hidden" name="database" value="' . $_SESSION['tree_prefix'] . '">';
		echo '<input type="hidden" name="screen_mode" value="PDF-L">';
		echo '<input type="hidden" name="id" value="' . $family_id . '">';
		echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
		echo '<input type="hidden" name="dates_behind_names" value="' . $dates_behind_names . '">';
		echo '<input type="hidden" name="show_date" value="' . $show_date . '">';
		echo '<input type="hidden" name="main_person" value="' . $main_person . '">';
		echo '<input class="fonts" type="Submit" name="submit" value="' . __('PDF (Landscape)') . '">';
		echo '</form>';
	}
	echo '</span>';
}

echo '</div><br>';




// *************************************
// ****** FUNCTION OUTLINE *************  // recursive function
// *************************************

function outline($family_id, $main_person, $gn, $nr_generations)
{
	global $db_functions, $pdf, $pdf_font, $show_details, $show_date, $dates_behind_names, $nr_generations;
	global $language, $dirmark1, $dirmark1, $screen_mode, $user;

	$family_nr = 1; //*** Process multiple families ***

	$show_privacy_text = false;

	if ($nr_generations < $gn) {
		return;
	}
	$gn++;

	// *** Count marriages of man ***
	// *** YB: if needed show woman as main_person ***
	@$familyDb = $db_functions->get_family($family_id, 'man-woman');
	$parent1 = '';
	$swap_parent1_parent2 = false;

	// *** Standard main_person is the father ***
	if ($familyDb->fam_man) {
		$parent1 = $familyDb->fam_man;
	}
	// *** If mother is selected, mother will be main_person ***
	if ($familyDb->fam_woman == $main_person) {
		$parent1 = $familyDb->fam_woman;
		$swap_parent1_parent2 = true;
	}

	// *** Check family with parent1: N.N. ***
	if ($parent1) {
		// *** Save man's families in array ***
		@$personDb = $db_functions->get_person($parent1, 'famc-fams');
		$marriage_array = explode(";", $personDb->pers_fams);
		$nr_families = substr_count($personDb->pers_fams, ";");
	} else {
		$marriage_array[0] = $family_id;
		$nr_families = "0";
	}

	// *** Loop multiple marriages of main_person ***
	for ($parent1_marr = 0; $parent1_marr <= $nr_families; $parent1_marr++) {
		@$familyDb = $db_functions->get_family($marriage_array[$parent1_marr]);

		// *** Privacy filter man and woman ***
		@$person_manDb = $db_functions->get_person($familyDb->fam_man);
		$man_cls = new person_cls;
		$man_cls->construct($person_manDb);
		$privacy_man = $man_cls->privacy;

		@$person_womanDb = $db_functions->get_person($familyDb->fam_woman);
		$woman_cls = new person_cls;
		$woman_cls->construct($person_womanDb);
		$privacy_woman = $woman_cls->privacy;

		$marriage_cls = new marriage_cls;
		$marriage_cls->construct($familyDb, $privacy_man, $privacy_woman);
		$family_privacy = $marriage_cls->privacy;

		// *************************************************************
		// *** Parent1 (normally the father)                         ***
		// *************************************************************
		if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, vrouw zonder man
			if ($family_nr == 1) {
				// *** Show data of man ***

				$dir = "";
				if ($language["dir"] == "rtl") {
					$dir = "rtl";    // in the following code calls the css indentation for rtl pages: "div.rtlsub2" instead of "div.sub2"
				}

				$indent = $dir . 'sub' . $gn;  // hier wordt de indent bepaald voor de namen div class (sub1, sub2 enz. die in gedcom.css staan)
				echo '<div class="' . $indent . '">';
				echo '<span style="font-weight:bold;font-size:120%">' . $gn . ' </span>';
				if ($swap_parent1_parent2 == true) {
					echo $woman_cls->name_extended("outline");
					if ($show_details and !$privacy_woman) {
						echo $woman_cls->person_data("outline", $familyDb->fam_gedcomnumber);
					}
					if ($show_date == "1" and !$privacy_woman and !$show_details) {
						echo $dirmark1 . ',';
						if ($dates_behind_names == false) {
							echo '<br>';
						}
						echo ' &nbsp; (' . language_date($person_womanDb->pers_birth_date) . ' - ' . language_date($person_womanDb->pers_death_date) . ')';
					}
				} else {
					echo $man_cls->name_extended("outline");
					if ($show_details and !$privacy_man) {
						echo $man_cls->person_data("outline", $familyDb->fam_gedcomnumber);
					}
					if ($show_date == "1" and !$privacy_man and !$show_details) {
						echo $dirmark1 . ',';
						if ($dates_behind_names == false) {
							echo '<br>';
						}
						echo ' &nbsp; (' . language_date($person_manDb->pers_birth_date) . ' - ' . language_date($person_manDb->pers_death_date) . ')';
					}
				}
				echo '</div>';
			}

			$family_nr++;
		}

		// *************************************************************
		// *** Parent2 (normally the mother)                         ***
		// *************************************************************

		// *** Totally hide parent2 if setting is active ***
		$show_parent2 = true;
		if ($swap_parent1_parent2) {
			if ($user["group_pers_hide_totally_act"] == 'j' and strpos(' ' . $person_manDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
				$show_privacy_text = true;
				$family_privacy = true;
				$show_parent2 = false;
			}
		} else {
			if ($user["group_pers_hide_totally_act"] == 'j' and strpos(' ' . $person_womanDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
				$show_privacy_text = true;
				$family_privacy = true;
				$show_parent2 = false;
			}
		}

		echo '<div class="' . $indent . '" style="font-style:italic">';
		if (!$show_details) {
			echo ' x ' . $dirmark1;
		} else {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if ($parent1_marr == 0) {
				if ($family_privacy) {
					echo $marriage_cls->marriage_data($familyDb, '', 'short') . "<br>";
				} else {
					echo $marriage_cls->marriage_data() . "<br>";
				}
			} else {
				echo $marriage_cls->marriage_data($familyDb, $parent1_marr + 1, 'shorter') . ' <br>';
			}
		}

		if ($show_parent2 and $swap_parent1_parent2) {
			if ($show_details) {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			echo $man_cls->name_extended("outline");
			if ($show_details and !$privacy_man) {
				echo $man_cls->person_data("outline", $familyDb->fam_gedcomnumber);
			}
			if ($show_date == "1" and !$privacy_man and !$show_details) {
				echo $dirmark1 . ',';
				if ($dates_behind_names == false) {
					echo '<br>';
				}
				echo ' &nbsp; (' . @language_date($person_manDb->pers_birth_date) . ' - ' . @language_date($person_manDb->pers_death_date) . ')';
			}
		} elseif ($show_parent2) {
			if ($show_details) {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			echo $woman_cls->name_extended("outline");
			if ($show_details and !$privacy_woman) {
				echo $woman_cls->person_data("outline", $familyDb->fam_gedcomnumber);
			}
			if ($show_date == "1" and !$privacy_woman and !$show_details) {
				echo $dirmark1 . ',';
				if ($dates_behind_names == false) {
					echo '<br>';
				}
				echo ' &nbsp; (' . @language_date($person_womanDb->pers_birth_date) . ' - ' . @language_date($person_womanDb->pers_death_date) . ')';
			}
		} else {
			// *** No permission to show parent2 ***
			echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
		}
		echo '</div>';

		// *************************************************************
		// *** Children                                              ***
		// *************************************************************
		if ($familyDb->fam_children) {
			$childnr = 1;
			$child_array = explode(";", $familyDb->fam_children);
			foreach ($child_array as $i => $value) {
				@$childDb = $db_functions->get_person($child_array[$i]);

				// *** Totally hide children if setting is active ***
				if ($user["group_pers_hide_totally_act"] == 'j' and strpos(' ' . $childDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
					if (!$show_privacy_text) {
						echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
						$show_privacy_text = true;
					}
					continue;
				}

				$child_cls = new person_cls;
				$child_cls->construct($childDb);
				$child_privacy = $child_cls->privacy;

				// *** Build descendant_report ***
				if ($childDb->pers_fams) {
					// *** 1e family of child ***
					$child_family = explode(";", $childDb->pers_fams);
					$child1stfam = $child_family[0];
					outline($child1stfam, $childDb->pers_gedcomnumber, $gn, $nr_generations);  // recursive
				} else {    // Child without own family
					if ($nr_generations >= $gn) {
						$childgn = $gn + 1;
						$childindent = $dir . 'sub' . $childgn;
						echo '<div class="' . $childindent . '">';
						echo '<span style="font-weight:bold;font-size:120%">' . $childgn . ' ' . '</span>';
						echo $child_cls->name_extended("outline");
						if ($show_details and !$child_privacy) {
							echo $child_cls->person_data("outline", "");
						}
						if ($show_date == "1" and !$child_privacy and !$show_details) {
							echo $dirmark1 . ',';
							if ($dates_behind_names == false) {
								echo '<br>';
							}
							echo ' &nbsp; (' . language_date($childDb->pers_birth_date) . ' - ' . language_date($childDb->pers_death_date) . ')';
						}
						echo '</div>';
					}
				}
				echo "br";

				$childnr++;
			}
		}
	}
}


// ******* Start function here - recursive if started ******
echo '<table class="humo outlinetable"><tr><td>';

outline($family_id, $main_person, $gn, $nr_generations);

echo '</td></tr></table>';

include_once __DIR__ . '/footer.php';
