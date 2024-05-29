<?php
$user["user_name"] = "";
if (isset($_SESSION["user_name"]) && is_numeric($_SESSION["user_id"])) {
    $user["user_name"] = $_SESSION["user_name"];
    $account = "SELECT * FROM humo_users WHERE user_id='" . safe_text_db($_SESSION["user_id"]) . "'";
} else {
    // *** For guest account ("gast" is only used for backward compatibility) ***
    $account = "SELECT * FROM humo_users WHERE user_name='gast' OR user_name='guest'";
}
$accountqry = $dbh->query($account);
try {
    @$accountDb = $accountqry->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo "No valid user / Geen geldige gebruiker.";
}

$groupsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $accountDb->user_group_id . "'");
try {
    @$groupDb = $groupsql->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    echo "No valid usergroup / Geen geldige gebruikersgroup.";
}

$user['group_statistics'] = isset($groupDb->group_statistics) ? $groupDb->group_statistics : 'j';

$user['group_birthday_rss'] = isset($groupDb->group_birthday_rss) ? $groupDb->group_birthday_rss : 'j';

$user['group_birthday_list'] = isset($groupDb->group_birthday_list) ? $groupDb->group_birthday_list : 'j';

$user['group_showstatistics'] = isset($groupDb->group_showstatistics) ? $groupDb->group_showstatistics : 'j';

$user['group_relcalc'] = isset($groupDb->group_relcalc) ? $groupDb->group_relcalc : 'j';

$user['group_googlemaps'] = isset($groupDb->group_googlemaps) ? $groupDb->group_googlemaps : 'j';

$user['group_contact'] = isset($groupDb->group_contact) ? $groupDb->group_contact : 'j';

$user['group_latestchanges'] = isset($groupDb->group_latestchanges) ? $groupDb->group_latestchanges : 'j';

$user['group_menu_cms'] = isset($groupDb->group_menu_cms) ? $groupDb->group_menu_cms : 'y';

$user['group_menu_persons'] = isset($groupDb->group_menu_persons) ? $groupDb->group_menu_persons : 'j';

$user['group_menu_names'] = isset($groupDb->group_menu_names) ? $groupDb->group_menu_names : 'j';

$user['group_menu_places'] = $groupDb->group_menu_places;

$user['group_menu_login'] = isset($groupDb->group_menu_login) ? $groupDb->group_menu_login : 'j';

if (!isset($groupDb->group_menu_change_password)) {
    $user['group_menu_change_password'] = 'y';
} else {
    $user['group_menu_change_password'] = $groupDb->group_menu_change_password;
}

$user["group_privacy"] = $groupDb->group_privacy;

$user['group_admin'] = $groupDb->group_admin;

//if (!isset($groupDb->group_editor)){ $user['group_editor']='n'; }
//	else{ $user['group_editor']=$groupDb->group_editor; }

$user['group_pictures'] = $groupDb->group_pictures;

$user['group_photobook'] = isset($groupDb->group_photobook) ? $groupDb->group_photobook : 'n';

$user['group_sources'] = $groupDb->group_sources;

if (!isset($groupDb->group_show_restricted_source)) {
    $user['group_show_restricted_source'] = 'y';
} else {
    $user['group_show_restricted_source'] = $groupDb->group_show_restricted_source;
}

if (!isset($groupDb->group_source_presentation)) {
    $user['group_source_presentation'] = 'title';
} else {
    $user['group_source_presentation'] = $groupDb->group_source_presentation;
}

if (!isset($groupDb->group_text_presentation)) {
    $user['group_text_presentation'] = 'show';
} else {
    $user['group_text_presentation'] = $groupDb->group_text_presentation;
}

if (!isset($groupDb->group_citation_generation)) {
    $user['group_citation_generation'] = 'n';
} else {
    $user['group_citation_generation'] = $groupDb->group_citation_generation;
}

// *** User can add notes/ remarks by a person in the family tree ***
$user['group_user_notes'] = isset($groupDb->group_user_notes) ? $groupDb->group_user_notes : 'n';

$user['group_user_notes_show'] = isset($groupDb->group_user_notes_show) ? $groupDb->group_user_notes_show : 'n';

$user['group_gedcomnr'] = $groupDb->group_gedcomnr; // Show gedcomnumber
$user['group_living_place'] = $groupDb->group_living_place; // Show living place
$user['group_places'] = $groupDb->group_places; // Show birth, bapt, death and buried places.
$user['group_religion'] = $groupDb->group_religion; // Show birth and marr. religion
$user['group_place_date'] = $groupDb->group_place_date; // j=place-date, n-date-place
$user['group_kindindex'] = $groupDb->group_kindindex; // n='Mons, Henk van', j='van Mons, Henk'
$user['group_event'] = $groupDb->group_event; // Show events
$user['group_addresses'] = $groupDb->group_addresses; // Show addresses IN MENU
$user['group_own_code'] = $groupDb->group_own_code; // Show Own code

if (!isset($groupDb->group_show_age_living_person)) {
    $user['group_show_age_living_person'] = 'y';
} else {
    $user['group_show_age_living_person'] = $groupDb->group_show_age_living_person;
}

$user['group_pdf_button'] = isset($groupDb->group_pdf_button) ? $groupDb->group_pdf_button : 'y';

$user['group_rtf_button'] = isset($groupDb->group_rtf_button) ? $groupDb->group_rtf_button : 'n';

if (!isset($groupDb->group_family_presentation)) {
    $user['group_family_presentation'] = 'compact';
} else {
    $user['group_family_presentation'] = $groupDb->group_family_presentation;
}
if (!isset($groupDb->group_maps_presentation)) {
    $user['group_maps_presentation'] = 'hide';
} else {
    $user['group_maps_presentation'] = $groupDb->group_maps_presentation;
}

$user['group_work_text'] = $groupDb->group_work_text; // Show (Haza-data) worktexts
$user['group_texts'] = $groupDb->group_texts; // Show (marriage?) text
$user['group_text_pers'] = $groupDb->group_text_pers; // Show person text
$user['group_texts_pers'] = $groupDb->group_texts_pers; // Show birth, bapt, death, burr. texts.
$user['group_texts_fam'] = $groupDb->group_texts_fam; // Show marr. (licence) texts

//Privacy filter
$user['group_alive'] = $groupDb->group_alive; // Person filter.

$user['group_alive_date_act'] = $groupDb->group_alive_date_act; // Privacy filter activated
$user['group_alive_date'] = $groupDb->group_alive_date; // Privacy filter year

$user['group_death_date_act'] = isset($groupDb->group_death_date_act) ? $groupDb->group_death_date_act : 'n';

$user['group_death_date'] = isset($groupDb->group_death_date) ? $groupDb->group_death_date : '';

$user['group_filter_death'] = $groupDb->group_filter_death; // Filter deceased persons
$user['group_filter_total'] = $groupDb->group_filter_total;
$user['group_filter_name'] = $groupDb->group_filter_name; // Privacy: show persons
$user['group_filter_fam'] = $groupDb->group_filter_fam;

$user['group_filter_pers_show_act'] = $groupDb->group_filter_pers_show_act; // Activate next line
$user['group_filter_pers_show'] = $groupDb->group_filter_pers_show; // Person filter

$user['group_filter_pers_hide_act'] = $groupDb->group_filter_pers_hide_act; // Activate next line
$user['group_filter_pers_hide'] = $groupDb->group_filter_pers_hide; // Person filter

if (!isset($groupDb->group_pers_hide_totally_act)) {
    $user['group_pers_hide_totally_act'] = 'n';
} else {
    $user['group_pers_hide_totally_act'] = $groupDb->group_pers_hide_totally_act;
}

$user['group_pers_hide_totally'] = isset($groupDb->group_pers_hide_totally) ? $groupDb->group_pers_hide_totally : 'X';

$user['group_filter_date'] = isset($groupDb->group_filter_date) ? $groupDb->group_filter_date : 'n';

$user['group_gen_protection'] = isset($groupDb->group_gen_protection) ? $groupDb->group_gen_protection : 'n';

// *** Show or hide family trees, saved as ; separated id numbers ***
$user['group_hide_trees'] = isset($groupDb->group_hide_trees) ? $groupDb->group_hide_trees : '';

// *** Also check user settings. Example: 1, y2, 3, y4. y=yes to show family tree ***
if (isset($accountDb->user_hide_trees) && $accountDb->user_hide_trees) {
    $user_hide_trees_array = explode(";", $accountDb->user_hide_trees);
    foreach ($user_hide_trees_array as $key) {
        // *** Check for y (used in y1, y2 etc.). Indicates to SHOW a family tree ***
        // *** $key[0]= 1st character ***
        if ($key[0] === 'y') {
            // *** remove y1; ***
            $replace = $key[1] . ';';
            $user['group_hide_trees'] = str_replace($replace, '', $user['group_hide_trees']);
            // *** Or: remove y1 (without ;) ***
            //$user['group_hide_trees']=str_replace($key[1],'',$user['group_hide_trees']);
            $user['group_hide_trees'] = rtrim($user['group_hide_trees'], $key[1]);
        } else {
            $check_array = explode(";", $user['group_hide_trees']);
            //if (!in_array($key, $user['group_hide_trees'])){
            if (!in_array($key, $check_array)) {
                if ($user['group_hide_trees']) {
                    $user['group_hide_trees'] .= ';' . $key;
                } else {
                    $user['group_hide_trees'] = $key;
                }
            }
        }
    }
}

// *** Show or hide photo categories, saved as ; separated id numbers ***
$user['group_hide_photocat'] = isset($groupDb->group_hide_photocat) ? $groupDb->group_hide_photocat : '';

// *** Edit family trees [GROUP SETTING], saved as ; separated id numbers (NOT USED FOR ADMINISTRATOR) ***
$user['group_edit_trees'] = isset($groupDb->group_edit_trees) ? $groupDb->group_edit_trees : '';
// *** Edit family trees [USER SETTING] ***
if (isset($accountDb->user_edit_trees) && $accountDb->user_edit_trees) {
    if ($user['group_edit_trees']) {
        $user['group_edit_trees'] .= ';' . $accountDb->user_edit_trees;
    } else {
        $user['group_edit_trees'] = $accountDb->user_edit_trees;
    }
}
