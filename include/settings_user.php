<?php
require __DIR__ . '/model/db_user.php';
require __DIR__ . '/model/db_group.php';

$user_model = new db_user($dbh);
$group_model = new db_group($dbh);

$user["user_name"] = "";
$user['group_statistics'] = 'j';
$user['group_birthday_rss'] = 'j';
$user['group_birthday_list'] = 'j';
$user['group_showstatistics'] = 'j';
$user['group_relcalc'] = 'j';
$user['group_googlemaps'] = 'j';
$user['group_contact'] = 'j';
$user['group_latestchanges'] = 'j';
$user['group_menu_cms'] = 'y';
$user['group_menu_persons'] = 'j';
$user['group_menu_names'] = 'j';
$user['group_menu_login'] = 'j';
$user['group_menu_change_password'] = 'y';
// $user['group_editor']='n';
$user['group_photobook'] = 'n';
$user['group_show_restricted_source'] = 'y';
$user['group_source_presentation'] = 'title';
$user['group_text_presentation'] = 'show';
$user['group_citation_generation'] = 'n';
$user['group_user_notes'] = 'n';
$user['group_user_notes_show'] = 'n';
$user['group_show_age_living_person'] = 'y';
$user['group_pdf_button'] = 'y';
$user['group_rtf_button'] = 'n';
$user['group_family_presentation'] = 'compact';
$user['group_maps_presentation'] = 'hide';
$user['group_death_date_act'] = 'n';
$user['group_death_date'] = '';
$user['group_pers_hide_totally_act'] = 'n';
$user['group_pers_hide_totally'] = 'X';
$user['group_filter_date'] = 'n';
$user['group_gen_protection'] = 'n';
$user['group_hide_trees'] = '';
$user['group_hide_photocat'] = '';
$user['group_edit_trees'] = '';

if (isset($_SESSION["user_name"]) and is_numeric($_SESSION["user_id"])) {
	$user["user_name"] = $_SESSION["user_name"];
	$this_user = $user_model->findId($_SESSION["user_id"]);
	$this_group = $group_model->findId($this_user->user_group_id);


	$user['group_statistics'] = $this_group->group_statistics;
	$user['group_birthday_rss'] = $this_group->group_birthday_rss;
	$user['group_birthday_list'] = $this_group->group_birthday_list;
	$user['group_showstatistics'] = $this_group->group_showstatistics;
	$user['group_relcalc'] = $this_group->group_relcalc;
	$user['group_googlemaps'] = $this_group->group_googlemaps;
	$user['group_contact'] = $this_group->group_contact;
	$user['group_latestchanges'] = $this_group->group_latestchanges;
	$user['group_menu_cms'] = $this_group->group_menu_cms;
	$user['group_menu_persons'] = $this_group->group_menu_persons;
	$user['group_menu_names'] = $this_group->group_menu_names;
	$user['group_menu_places'] = $this_group->group_menu_places;
	$user['group_menu_login'] = $this_group->group_menu_login;
	$user['group_menu_change_password'] = $this_group->group_menu_change_password;
	$user["group_privacy"] = $this_group->group_privacy;
	$user['group_admin'] = $this_group->group_admin;
	// $user['group_editor']=$this_group->group_editor; }
	$user['group_pictures'] = $this_group->group_pictures;
	$user['group_photobook'] = $this_group->group_photobook;
	$user['group_sources'] = $this_group->group_sources;
	$user['group_show_restricted_source'] = $this_group->group_show_restricted_source;
	$user['group_source_presentation'] = $this_group->group_source_presentation;
	$user['group_text_presentation'] = $this_group->group_text_presentation;
	$user['group_citation_generation'] = $this_group->group_citation_generation;
	$user['group_user_notes'] = $this_group->group_user_notes; // User can add notes/remarks by a person in the family tree
	$user['group_user_notes_show'] = $this_group->group_user_notes_show;
	$user['group_gedcomnr'] = $this_group->group_gedcomnr; // Show gedcomnumber
	$user['group_living_place'] = $this_group->group_living_place; // Show living place
	$user['group_places'] = $this_group->group_places; // Show birth, bapt, death and buried places.
	$user['group_religion'] = $this_group->group_religion; // Show birth and marr. religion
	$user['group_place_date'] = $this_group->group_place_date; // j=place-date, n-date-place
	$user['group_kindindex'] = $this_group->group_kindindex; // n='Mons, Henk van', j='van Mons, Henk'
	$user['group_event'] = $this_group->group_event; // Show events
	$user['group_addresses'] = $this_group->group_addresses; // Show addresses IN MENU
	$user['group_own_code'] = $this_group->group_own_code; // Show Own code
	$user['group_show_age_living_person'] = $this_group->group_show_age_living_person;
	$user['group_pdf_button'] = $this_group->group_pdf_button;
	$user['group_rtf_button'] = $this_group->group_rtf_button;
	$user['group_family_presentation'] = $this_group->group_family_presentation;
	$user['group_maps_presentation'] = $this_group->group_maps_presentation;
	$user['group_work_text'] = $this_group->group_work_text; // Show (Haza-data) worktexts
	$user['group_texts'] = $this_group->group_texts; // Show (marriage?) text
	$user['group_text_pers'] = $this_group->group_text_pers; // Show person text
	$user['group_texts_pers'] = $this_group->group_texts_pers; // Show birth, bapt, death, burr. texts.
	$user['group_texts_fam'] = $this_group->group_texts_fam; // Show marr. (licence) texts
	$user['group_alive'] = $this_group->group_alive; // Person filter.
	$user['group_alive_date_act'] = $this_group->group_alive_date_act; // Privacy filter activated
	$user['group_alive_date'] = $this_group->group_alive_date; // Privacy filter year
	$user['group_death_date_act'] = $this_group->group_death_date_act; // Privacy filter activated
	$user['group_death_date'] = $this_group->group_death_date; // Privacy filter year
	$user['group_filter_death'] = $this_group->group_filter_death; // Filter deceased persons
	$user['group_filter_total'] = $this_group->group_filter_total;
	$user['group_filter_name'] = $this_group->group_filter_name; // Privacy: show persons
	$user['group_filter_fam'] = $this_group->group_filter_fam;
	$user['group_filter_pers_show_act'] = $this_group->group_filter_pers_show_act; // Activate next line
	$user['group_filter_pers_show'] = $this_group->group_filter_pers_show; // Person filter
	$user['group_filter_pers_hide_act'] = $this_group->group_filter_pers_hide_act; // Activate next line
	$user['group_filter_pers_hide'] = $this_group->group_filter_pers_hide; // Person filter
	$user['group_pers_hide_totally_act'] = $this_group->group_pers_hide_totally_act;
	$user['group_pers_hide_totally'] = $this_group->group_pers_hide_totally;
	$user['group_filter_date'] = $this_group->group_filter_date;
	$user['group_gen_protection'] = $this_group->group_gen_protection;
	$user['group_hide_trees'] = $this_group->group_hide_trees;

	// *** Also check user settings. Example: 1, y2, 3, y4. y=yes to show family tree ***
	if (isset($this_user->user_hide_trees) and $this_user->user_hide_trees) {
		$hidden_trees = explode(";", $this_user->user_hide_trees); // Array of hidden trees

		foreach ($hidden_trees as $hidden_tree) {
			// *** Check for y (used in y1, y2 etc.). Indicates to SHOW a family tree ***
			// *** $key[0]= 1st character ***
			if ($hidden_tree[0] == 'y') {
				// *** remove y1; ***
				$replace = $hidden_tree[1] . ';';
				$user['group_hide_trees'] = str_replace($replace, '', $user['group_hide_trees']);
				// *** Or: remove y1 (without ;) ***
				//$user['group_hide_trees']=str_replace($key[1],'',$user['group_hide_trees']);
				$user['group_hide_trees'] = rtrim($user['group_hide_trees'], $hidden_tree[1]);
			} else {
				$check_array = explode(";", $user['group_hide_trees']);
				//if (!in_array($key, $user['group_hide_trees'])){
				if (!in_array($hidden_tree, $check_array)) {
					if ($user['group_hide_trees']) $user['group_hide_trees'] .= ';' . $hidden_tree;
					else $user['group_hide_trees'] = $hidden_tree;
				}
			}
		}
	}

	$user['group_hide_photocat'] = $this_group->group_hide_photocat; // Show or hide photo categories, saved as ; separated id numbers
	$user['group_edit_trees'] = $this_group->group_edit_trees; // Edit family trees [GROUP SETTING], saved as ; separated id numbers (NOT USED FOR ADMINISTRATOR)

	// *** Edit family trees [USER SETTING] ***
	if (isset($this_user->user_edit_trees) and $this_user->user_edit_trees) {
		if ($user['group_edit_trees'])
			$user['group_edit_trees'] .= ';' . $this_user->user_edit_trees;
		else
			$user['group_edit_trees'] = $this_user->user_edit_trees;
	}
}
