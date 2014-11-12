<?php
$user["user_name"]="";
if (isset($_SESSION["user_name"])){
	$user["user_name"]=$_SESSION["user_name"];
	$account="SELECT * FROM humo_users WHERE user_id=".safe_text($_SESSION["user_id"]);
}
else{
	// *** For guest account ("gast" is only used for backward compatibility) ***
	$account="SELECT * FROM humo_users WHERE user_name='gast' OR user_name='guest'";
}
$accountqry = $dbh->query($account);
try {
	@$accountDb = $accountqry->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	echo "No valid user / Geen geldige gebruiker.";
}

$groupsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='".$accountDb->user_group_id."'");
try {
	@$groupDb = $groupsql->fetch(PDO::FETCH_OBJ);
} catch (PDOException $e) {
	echo "No valid usergroup / Geen geldige gebruikersgroup.";
}

if (!isset($groupDb->group_statistics)){ $user['group_statistics']='j'; }
	else{ $user['group_statistics']=$groupDb->group_statistics; }

if (!isset($groupDb->group_birthday_rss)){ $user['group_birthday_rss']='j'; }
	else{ $user['group_birthday_rss']=$groupDb->group_birthday_rss; }

if (!isset($groupDb->group_birthday_list)){ $user['group_birthday_list']='j'; }
	else{ $user['group_birthday_list']=$groupDb->group_birthday_list; }

if (!isset($groupDb->group_showstatistics)){ $user['group_showstatistics']='j'; }
	else{ $user['group_showstatistics']=$groupDb->group_showstatistics; }

if (!isset($groupDb->group_relcalc)){ $user['group_relcalc']='j'; }
	else{ $user['group_relcalc']=$groupDb->group_relcalc; }

if (!isset($groupDb->group_googlemaps)){ $user['group_googlemaps']='j'; }
	else{ $user['group_googlemaps']=$groupDb->group_googlemaps; }

if (!isset($groupDb->group_contact)){ $user['group_contact']='j'; }
	else{ $user['group_contact']=$groupDb->group_contact; }

if (!isset($groupDb->group_latestchanges)){ $user['group_latestchanges']='j'; }
	else{ $user['group_latestchanges']=$groupDb->group_latestchanges; }

if (!isset($groupDb->group_menu_persons)){ $user['group_menu_persons']='j'; }
	else{ $user['group_menu_persons']=$groupDb->group_menu_persons; }

if (!isset($groupDb->group_menu_names)){ $user['group_menu_names']='j'; }
	else{ $user['group_menu_names']=$groupDb->group_menu_names; }

$user['group_menu_places']=$groupDb->group_menu_places;

if (!isset($groupDb->group_menu_login)){ $user['group_menu_login']='j'; }
	else{ $user['group_menu_login']=$groupDb->group_menu_login; }

$user["group_privacy"]=$groupDb->group_privacy;

$user['group_admin']=$groupDb->group_admin;

//if (!isset($groupDb->group_editor)){ $user['group_editor']='n'; }
//	else{ $user['group_editor']=$groupDb->group_editor; }

$user['group_pictures']=$groupDb->group_pictures;

if (!isset($groupDb->group_photobook)){ $user['group_photobook']='n'; }
	else{ $user['group_photobook']=$groupDb->group_photobook; }

$user['group_sources']=$groupDb->group_sources;

if (!isset($groupDb->group_show_restricted_source)){ $user['group_show_restricted_source']='y'; }
	else{ $user['group_show_restricted_source']=$groupDb->group_show_restricted_source; }

if (!isset($groupDb->group_source_presentation)){ $user['group_source_presentation']='title'; }
	else{ $user['group_source_presentation']=$groupDb->group_source_presentation; }

if (!isset($groupDb->group_text_presentation)){ $user['group_text_presentation']='show'; }
	else{ $user['group_text_presentation']=$groupDb->group_text_presentation; }

// *** User can add notes/ remarks by a person in the family tree ***
if (!isset($groupDb->group_user_notes)){ $user['group_user_notes']='n'; }
	else{ $user['group_user_notes']=$groupDb->group_user_notes; }

$user['group_gedcomnr']=$groupDb->group_gedcomnr; // Show gedcomnumber
$user['group_living_place']=$groupDb->group_living_place; // Show living place
$user['group_places']=$groupDb->group_places; // Show birth, bapt, death and buried places.
$user['group_religion']=$groupDb->group_religion; // Show birth and marr. religion
$user['group_place_date']=$groupDb->group_place_date; // j=place-date, n-date-place
$user['group_kindindex']=$groupDb->group_kindindex; // n='Mons, Henk van', j='van Mons, Henk'
$user['group_event']=$groupDb->group_event; // Show events
$user['group_addresses']=$groupDb->group_addresses; // Show addresses
$user['group_own_code']=$groupDb->group_own_code; // Show Own code

if (!isset($groupDb->group_pdf_button)){ $user['group_pdf_button']='y'; }
	else{ $user['group_pdf_button']=$groupDb->group_pdf_button; }

if (!isset($groupDb->group_rtf_button)){ $user['group_rtf_button']='n'; }
	else{ $user['group_rtf_button']=$groupDb->group_rtf_button; }

if (!isset($groupDb->group_family_presentation)){ $user['group_family_presentation']='compact'; }
	else{ $user['group_family_presentation']=$groupDb->group_family_presentation; }
if (!isset($groupDb->group_maps_presentation)){ $user['group_maps_presentation']='hide'; }
	else{ $user['group_maps_presentation']=$groupDb->group_maps_presentation; }

$user['group_work_text']=$groupDb->group_work_text; // Show (Haza-data) worktexts
$user['group_texts']=$groupDb->group_texts; // Show (marriage?) text
$user['group_text_pers']=$groupDb->group_text_pers; // Show person text
$user['group_texts_pers']=$groupDb->group_texts_pers; // Show birth, bapt, death, burr. texts.
$user['group_texts_fam']=$groupDb->group_texts_fam; // Show marr. (licence) texts

//Privacy filter
$user['group_alive']=$groupDb->group_alive; // Person filter.

$user['group_alive_date_act']=$groupDb->group_alive_date_act; // Privacy filter activated
$user['group_alive_date']=$groupDb->group_alive_date; // Privacy filter year

if (isset($groupDb->group_death_date_act)) $user['group_death_date_act']=$groupDb->group_death_date_act; // Privacy filter activated
	else $user['group_death_date_act']='n';

if (isset($groupDb->group_death_date)) $user['group_death_date']=$groupDb->group_death_date; // Privacy filter year
	else $user['group_death_date']='';

$user['group_filter_death']=$groupDb->group_filter_death; // Filter deceased persons
$user['group_filter_total']=$groupDb->group_filter_total;
$user['group_filter_name']=$groupDb->group_filter_name; // Privacy: show persons
$user['group_filter_fam']=$groupDb->group_filter_fam;

$user['group_filter_pers_show_act']=$groupDb->group_filter_pers_show_act; // Activate next line
$user['group_filter_pers_show']=$groupDb->group_filter_pers_show; // Person filter

$user['group_filter_pers_hide_act']=$groupDb->group_filter_pers_hide_act; // Activate next line
$user['group_filter_pers_hide']=$groupDb->group_filter_pers_hide; // Person filter

if (!isset($groupDb->group_pers_hide_totally_act)){ $user['group_pers_hide_totally_act']='n'; }
	else{ $user['group_pers_hide_totally_act']=$groupDb->group_pers_hide_totally_act; }

if (!isset($groupDb->group_pers_hide_totally)){ $user['group_pers_hide_totally']='X'; }
	else{ $user['group_pers_hide_totally']=$groupDb->group_pers_hide_totally; }

if (!isset($groupDb->group_filter_date)){ $user['group_filter_date']='n'; }
	else{ $user['group_filter_date']=$groupDb->group_filter_date; }

if (!isset($groupDb->group_gen_protection)){ $user['group_gen_protection']='n'; }
	else{ $user['group_gen_protection']=$groupDb->group_gen_protection; }

// *** Show or hide family trees, saved as ; separated id numbers ***
if (!isset($groupDb->group_hide_trees)){ $user['group_hide_trees']=''; }
	else{ $user['group_hide_trees']=$groupDb->group_hide_trees; }

// *** Edit family trees [GROUP SETTING], saved as ; separated id numbers (NOT USED FOR ADMINISTRATOR) ***
if (!isset($groupDb->group_edit_trees)){ $user['group_edit_trees']=''; }
	else{ $user['group_edit_trees']=$groupDb->group_edit_trees; }
// *** Edit family trees [USER SETTING] ***
if (isset($accountDb->user_edit_trees) AND $accountDb->user_edit_trees){
	if ($user['group_edit_trees']) $user['group_edit_trees'].=';'.$accountDb->user_edit_trees;
		else $user['group_edit_trees']=$accountDb->user_edit_trees;
}
?>