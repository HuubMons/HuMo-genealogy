<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

global $selected_language;

if(CMS_SPECIFIC=="Joomla")
	$phpself = "index.php?option=com_humo-gen&amp;task=admin&amp;page=groups";
else
	$phpself = $_SERVER['PHP_SELF'];

echo '<h1 align="center">'.__('User groups').'</h1>';

if (isset($_POST['group_add'])){
	$sql="INSERT INTO humo_groups SET group_name='new groep', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_source_presentation='title', group_text_presentation='show', group_user_notes='n', group_show_restricted_source='y',
		group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j',
		group_religion='n', group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n',
		group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n', group_texts='j',
		group_family_presentation='compact', group_maps_presentation='hide',
		group_menu_persons='j', group_menu_names='j', group_menu_login='j',
		group_showstatistics='j', group_relcalc='j', group_googlemaps='j', group_contact='j', group_latestchanges='j',
		group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='j', group_death_date='1980',
		group_filter_death='n', group_filter_total='n', group_filter_name='j',
		group_filter_fam='j', group_filter_pers_show_act='j', group_filter_pers_show='*', group_filter_pers_hide_act='n',
		group_filter_pers_hide='#'";
	$db_update = $dbh->query($sql);
}
 
if (isset($_POST['group_change'])){
	if ($_POST["group_filter_pers_show"]==''){ $_POST["group_filter_pers_show"]='*'; }
	if ($_POST["group_filter_pers_hide"]==''){ $_POST["group_filter_pers_hide"]='#'; }
	if ($_POST["group_pers_hide_totally"]==''){ $_POST["group_pers_hide_totally"]='X'; }

	$group_admin='n'; if (isset($_POST["group_admin"])){ $group_admin='j'; }
	//$group_editor='n'; if (isset($_POST["group_editor"])){ $group_editor='j'; }
	$group_statistics='n'; if (isset($_POST["group_statistics"])){ $group_statistics='j'; }
	$group_birthday_rss='n'; if (isset($_POST["group_birthday_rss"])){ $group_birthday_rss='j'; }
	$group_menu_persons='n'; if (isset($_POST["group_menu_persons"])){ $group_menu_persons='j'; }
	$group_menu_names='n'; if (isset($_POST["group_menu_names"])){ $group_menu_names='j'; }
	$group_menu_places='n'; if (isset($_POST["group_menu_places"])){ $group_menu_places='j'; }
	$group_addresses='n'; if (isset($_POST["group_addresses"])){ $group_addresses='j'; }
	$group_photobook='n'; if (isset($_POST["group_photobook"])){ $group_photobook='j'; }
	$group_birthday_list='n'; if (isset($_POST["group_birthday_list"])){ $group_birthday_list='j'; }
	$group_showstatistics='n'; if (isset($_POST["group_showstatistics"])){ $group_showstatistics='j'; }
	$group_relcalc='n'; if (isset($_POST["group_relcalc"])){ $group_relcalc='j'; }
	$group_googlemaps='n'; if (isset($_POST["group_googlemaps"])){ $group_googlemaps='j'; }
	$group_contact='n'; if (isset($_POST["group_contact"])){ $group_contact='j'; }
	$group_latestchanges='n'; if (isset($_POST["group_latestchanges"])){ $group_latestchanges='j'; }
	$group_menu_login='n'; if (isset($_POST["group_menu_login"])){ $group_menu_login='j'; }
	$group_pictures='n'; if (isset($_POST["group_pictures"])){ $group_pictures='j'; }
	$group_gedcomnr='n'; if (isset($_POST["group_gedcomnr"])){ $group_gedcomnr='j'; }
	$group_living_place='n'; if (isset($_POST["group_living_place"])){ $group_living_place='j'; }
	$group_places='n'; if (isset($_POST["group_places"])){ $group_places='j'; }
	$group_religion='n'; if (isset($_POST["group_religion"])){ $group_religion='j'; }
	$group_event='n'; if (isset($_POST["group_event"])){ $group_event='j'; }
	$group_own_code='n'; if (isset($_POST["group_own_code"])){ $group_own_code='j'; }
	$group_pdf_button='n'; if (isset($_POST["group_pdf_button"])){ $group_pdf_button='y'; }
	$group_rtf_button='n'; if (isset($_POST["group_rtf_button"])){ $group_rtf_button='y'; }

	//if (!isset($_POST["group_user_notes"])){ $_POST["group_user_notes"]='n'; }
	$group_user_notes='n'; if (isset($_POST["group_user_notes"])){ $group_user_notes='y'; }

	$group_show_restricted_source='n'; if (isset($_POST["group_show_restricted_source"])){ $group_show_restricted_source='y'; }
	$group_work_text='n'; if (isset($_POST["group_work_text"])){ $group_work_text='j'; }
	$group_text_pers='n'; if (isset($_POST["group_text_pers"])){ $group_text_pers='j'; }
	$group_texts_pers='n'; if (isset($_POST["group_texts_pers"])){ $group_texts_pers='j'; }
	$group_texts_fam='n'; if (isset($_POST["group_texts_fam"])){ $group_texts_fam='j'; }
	// *** BE AWARE: REVERSED CHECK OF VARIABLE! ***
	$group_privacy='j'; if (isset($_POST["group_privacy"])){ $group_privacy='n'; }
	$group_alive='n'; if (isset($_POST["group_alive"])){ $group_alive='j'; }
	$group_alive_date_act='n'; if (isset($_POST["group_alive_date_act"])){ $group_alive_date_act='j'; }
	$group_death_date_act='n'; if (isset($_POST["group_death_date_act"])){ $group_death_date_act='j'; }
	$group_filter_death='n'; if (isset($_POST["group_filter_death"])){ $group_filter_death='j'; }
	$group_filter_pers_show_act='n'; if (isset($_POST["group_filter_pers_show_act"])){ $group_filter_pers_show_act='j'; }
	$group_filter_pers_hide_act='n'; if (isset($_POST["group_filter_pers_hide_act"])){ $group_filter_pers_hide_act='j'; }
	$group_pers_hide_totally_act='n'; if (isset($_POST["group_pers_hide_totally_act"])){ $group_pers_hide_totally_act='j'; }
	$group_filter_date='n'; if (isset($_POST["group_filter_date"])){ $group_filter_date='j'; }
	$group_gen_protection='n'; if (isset($_POST["group_gen_protection"])){ $group_gen_protection='j'; }

	//group_editor='".$group_editor."',
	$sql="UPDATE humo_groups SET
	group_name='".$_POST["group_name"]."',
	group_statistics='".$group_statistics."',
	group_privacy='".$group_privacy."',
	group_menu_places='".$group_menu_places."',
	group_admin='".$group_admin."',
	group_sources='".$_POST["group_sources"]."',
	group_show_restricted_source='".$group_show_restricted_source."',
	group_source_presentation='".$_POST["group_source_presentation"]."',
	group_text_presentation='".$_POST["group_text_presentation"]."',
	group_user_notes='".$group_user_notes."',
	group_birthday_rss='".$group_birthday_rss."',
	group_menu_persons='".$group_menu_persons."',
	group_menu_names='".$group_menu_names."',
	group_menu_login='".$group_menu_login."',
	group_birthday_list='".$group_birthday_list."',
	group_showstatistics='".$group_showstatistics."',
	group_relcalc='".$group_relcalc."',
	group_googlemaps='".$group_googlemaps."',
	group_contact='".$group_contact."',
	group_latestchanges='".$group_latestchanges."',
	group_photobook='".$group_photobook."',
	group_pictures='".$group_pictures."',
	group_gedcomnr='".$group_gedcomnr."',
	group_living_place='".$group_living_place."',
	group_places='".$group_places."',
	group_religion='".$group_religion."',
	group_place_date='".$_POST["group_place_date"]."',
	group_kindindex='".$_POST["group_kindindex"]."',
	group_event='".$group_event."',
	group_addresses='".$group_addresses."',
	group_own_code='".$group_own_code."',
	group_pdf_button='".$group_pdf_button."',
	group_rtf_button='".$group_rtf_button."',
	group_family_presentation='".$_POST["group_family_presentation"]."',
	group_maps_presentation='".$_POST["group_maps_presentation"]."',
	group_work_text='".$group_work_text."',
	group_texts='".$_POST["group_texts"]."',
	group_text_pers='".$group_text_pers."',
	group_texts_pers='".$group_texts_pers."',
	group_texts_fam='".$group_texts_fam."',
	group_alive='".$group_alive."',
	group_alive_date_act='".$group_alive_date_act."',
	group_alive_date='".$_POST["group_alive_date"]."',
	group_death_date_act='".$group_death_date_act."',
	group_death_date='".$_POST["group_death_date"]."',
	group_filter_death='".$group_filter_death."',
	group_filter_total='".$_POST["group_filter_total"]."',
	group_filter_name='".$_POST["group_filter_name"]."',
	group_filter_fam='".$_POST["group_filter_fam"]."',
	group_filter_date='".$group_filter_date."',
	group_filter_pers_show_act='".$group_filter_pers_show_act."',
	group_filter_pers_show='".$_POST["group_filter_pers_show"]."',
	group_filter_pers_hide_act='".$group_filter_pers_hide_act."',
	group_filter_pers_hide='".$_POST["group_filter_pers_hide"]."',
	group_pers_hide_totally_act='".$group_pers_hide_totally_act."',
	group_pers_hide_totally='".$_POST["group_pers_hide_totally"]."',
	group_gen_protection='".$group_gen_protection."'
	WHERE group_id=".$_POST["id"];
	//echo $sql;
	$result=$dbh->query($sql);
}

if (isset($_POST['group_remove'])){
	echo '<div class="confirm">';
	$usersql="SELECT * FROM humo_users WHERE user_group_id=".$_POST["id"];
	$user=$dbh->query($usersql);
	$nr_users=$user->rowCount();
	if ($nr_users>0){
		// *** There are still users connected to this group ***
		echo '<b>'.__('It\'s not possible to delete this group: there is/ are').' '.$nr_users.' '.__('user(s) connected to this group!').'</b>';
	}
	else{
		echo __('Are you sure you want to remove the group:').' "'.$_POST['group_name'].'"?';
		echo '<form method="post" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="id" value="'.$_POST['id'].'">';
		echo ' <input type="Submit" name="group_remove2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
		echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
		echo '</form>';
	}
	echo '</div>';
}
if (isset($_POST['group_remove2'])){
	$sql="DELETE FROM humo_groups WHERE group_id='".$_POST["id"]."'";
	$db_update = $dbh->query($sql);
}

$show_group_id='3'; // *** Default group to show ***
if (isset($_POST['show_group_id'])){ $show_group_id=$_POST['show_group_id']; }

// *** User groups ***
echo __('You can have multiple users in HuMo-gen. Every user can be connected to 1 group.<br>
Examples:<br>
Group "guest" = <b>guests at the website (who are not logged in).</b><br>
Group "admin" = website administrator.<br>
Group "family" = family members or genealogists.').'<br>';

$groupsql="SELECT * FROM humo_groups";
$groupresult=$dbh->query($groupsql);
echo '<br><table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';
	echo '<b>'.__('Choose a user group: ').'</b> ';
	if(CMS_SPECIFIC=="Joomla") { echo "<br>"; }  // not enough space for text and buttons
	while ($groupDb=$groupresult->fetch(PDO::FETCH_OBJ)){
		$selected=''; if ($show_group_id==$groupDb->group_id){ $selected=' class="selected_item"'; }
		echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			echo '<input type="hidden" name="show_group_id" value="'.$groupDb->group_id.'">';
			$group_name=$groupDb->group_name; if ($group_name==''){ $group_name='NO NAME'; }
			echo ' <input type="Submit" name="submit" value="'.$group_name.'"'.$selected.'>';
		echo '</form>';
	}

	// *** Add group ***
	echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo ' <input type="Submit" name="group_add" value="'.__('ADD GROUP').'">';
	echo '</form>';
echo '</td></tr></table><br>';

// *** Show usergroup ***
//$groupsql="SELECT * FROM humo_groups WHERE group_id='".$show_group_id."'";
//$groupresult=$dbh->query($groupsql);
//$groupDb=$groupresult->fetch(PDO::FETCH_OBJ);

// *** Automatic installation or update ***
$column_qry = $dbh->query('SHOW COLUMNS FROM humo_groups');
while ($columnDb = $column_qry->fetch()) {
	$field_value=$columnDb['Field'];
	$field[$field_value]=$field_value;
}
if (!isset($field['group_source_presentation'])){
	$sql="ALTER TABLE humo_groups
		ADD group_source_presentation VARCHAR(20) NOT NULL DEFAULT 'title' AFTER group_sources;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_text_presentation'])){
	$sql="ALTER TABLE humo_groups
		ADD group_text_presentation VARCHAR(20) NOT NULL DEFAULT 'show' AFTER group_source_presentation;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_show_restricted_source'])){
	$sql="ALTER TABLE humo_groups
		ADD group_show_restricted_source VARCHAR(1) NOT NULL DEFAULT 'y' AFTER group_sources;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_death_date_act'])){
	$sql="ALTER TABLE humo_groups
		ADD group_death_date_act VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_alive_date;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_death_date'])){
	$sql="ALTER TABLE humo_groups
		ADD group_death_date VARCHAR(4) NOT NULL DEFAULT '1980' AFTER group_death_date_act;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_menu_persons'])){
	$sql="ALTER TABLE humo_groups
		ADD group_menu_persons VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_statistics;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_menu_names'])){
	$sql="ALTER TABLE humo_groups
		ADD group_menu_names VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_statistics;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_menu_login'])){
	$sql="ALTER TABLE humo_groups
		ADD group_menu_login VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_menu_names;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_showstatistics'])){
	$sql="ALTER TABLE humo_groups
		ADD group_showstatistics VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_relcalc'])){
	$sql="ALTER TABLE humo_groups
		ADD group_relcalc VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_googlemaps'])){
	$sql="ALTER TABLE humo_groups
		ADD group_googlemaps VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_contact'])){
	$sql="ALTER TABLE humo_groups
		ADD group_contact VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_latestchanges'])){
	$sql="ALTER TABLE humo_groups
		ADD group_latestchanges VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_pdf_button'])){
	$sql="ALTER TABLE humo_groups
		ADD group_pdf_button VARCHAR(1) NOT NULL DEFAULT 'y' AFTER group_own_code;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_rtf_button'])){
	$sql="ALTER TABLE humo_groups ADD group_rtf_button VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_pdf_button;";
	$result=$dbh->query($sql);

	// *** Show RTF button in usergroup "Admin" ***
	$sql="UPDATE humo_groups SET group_rtf_button='y' WHERE group_id=1";
	$result=$dbh->query($sql);
}
if (!isset($field['group_user_notes'])){
	$sql="ALTER TABLE humo_groups
		ADD group_user_notes VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_own_code;";
	$result=$dbh->query($sql);
}

if (!isset($field['group_family_presentation'])){
	$sql="ALTER TABLE humo_groups
		ADD group_family_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'compact' AFTER group_pdf_button;";
	$result=$dbh->query($sql);
}
if (!isset($field['group_maps_presentation'])){
	$sql="ALTER TABLE humo_groups
		ADD group_maps_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'hide' AFTER group_family_presentation;";
	$result=$dbh->query($sql);
}

if (!isset($field['group_edit trees'])){
	$sql="ALTER TABLE humo_groups
		ADD group_edit_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER group_hide_trees;";
	$result=$dbh->query($sql);
}

// *** Show usergroup ***
$groupsql="SELECT * FROM humo_groups WHERE group_id='".$show_group_id."'";
$groupresult=$dbh->query($groupsql);
$groupDb=$groupresult->fetch(PDO::FETCH_OBJ);

echo '<form method="POST" action="'.$phpself.'">';
echo '<input type="hidden" name="page" value="'.$page.'">';
echo "<input type='hidden' name='show_group_id' value='".$show_group_id."'>";
echo '<input type="hidden" name="id" value="'.$groupDb->group_id.'">';

echo '<table class="humo standard" border="1">';
echo '<tr class="table_header"><th>'.__('Option').'</th><th>'.__('Value').'</th></tr>';

echo '<tr style="background-color:green; color:white"><th>'.__('Group');
	if ($groupDb->group_id>'3'){
		echo ' <input type="Submit" name="group_remove" value="'.__('REMOVE GROUP').'">';
	}
	echo '</th><th><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';
echo '<tr><td>'.__('Group name').'</td><td><input type="text" name="group_name" value="'.$groupDb->group_name.'" size="15"></td>';

//$group_editor=$groupDb->group_editor; if ($groupDb->group_admin=='j'){ $group_editor='j'; }

echo '<tr><td>'.__('Administrator').'</td>';
$check=''; if ($groupDb->group_admin!='n') $check=' checked';

// *** Administrator group: don't change admin rights for administrator ***
$disabled='';
if ($groupDb->group_id=='1'){
	$disabled=' disabled';
	echo '<input type="hidden" name="group_admin" value="'.$groupDb->group_admin.'">';
	//echo '<input type="hidden" name="group_editor" value="'.$group_editor.'">';
}

echo '<td><input type="checkbox" name="group_admin"'.$check.$disabled.'></td></tr>';

//echo '<tr><td>'.__('Editor').'. '.__('Also select a family tree at the bottom of this page to edit.').'<br>';
//echo __('If an .htpasswd file is used: add username in .htpasswd file.').'</td>';
//$check=''; if ($groupDb->group_editor!='n') $check=' checked';
//echo '<td><input type="checkbox" name="group_editor"'.$check.$disabled.'></td></tr>';

echo '<tr><td>'.__('Save statistics data').'</td>';
$check=''; if ($groupDb->group_statistics!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_statistics"'.$check.'></td></tr>';

echo '<tr style="background-color:green; color:white"><th>Menu</th><th><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

//echo '<tr><td>OLD!!! '.__('Show sources and menu sources').'</td>';
//echo '<td><select size="1" name="group_sources"><option value="j">'.__('Yes').'</option>';
//$selected=''; if ($groupDb->group_sources=='n'){ $selected=' SELECTED'; }
//echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

echo '<tr><td>'.__('Birthday RSS in main menu').'</td>';
$check=''; if ($groupDb->group_birthday_rss!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_birthday_rss"'.$check.'></td></tr>';

echo '<tr><td>'.__('FAMILY TREE menu: show "Persons" submenu').'</td>';
$check=''; if ($groupDb->group_menu_persons!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_menu_persons"'.$check.'></td></tr>';

echo '<tr><td>'.__('FAMILY TREE menu: show "Names" submenu').'</td>';
$check=''; if ($groupDb->group_menu_names!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_menu_names"'.$check.'></td></tr>';

echo '<tr><td>'.__('FAMILY TREE menu: show "Places" submenu').'</td>';
$check=''; if ($groupDb->group_menu_places!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_menu_places"'.$check.'></td></tr>';

echo '<tr><td>'.__('FAMILY TREE menu: show "Addresses" submenu (only shown if there really are addresses)').'</td>';
$check=''; if ($groupDb->group_addresses!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_addresses"'.$check.'></td></tr>';

echo '<tr><td>'.__('FAMILY TREE menu: show "Photobook" submenu').'</td>';
$check=''; if ($groupDb->group_photobook!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_photobook"'.$check.'></td></tr>';

echo '<tr><td>'.__('TOOLS menu: show "Anniversary" (birthday list) submenu').'</td>';
$check=''; if ($groupDb->group_birthday_list!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_birthday_list"'.$check.'></td></tr>';

echo '<tr><td>'.__('TOOLS menu: show "Statistics" submenu').'</td>';
$check=''; if ($groupDb->group_showstatistics!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_showstatistics"'.$check.'></td></tr>';

echo '<tr><td>'.__('TOOLS menu: show "Relationship Calculator" submenu').'</td>';
$check=''; if ($groupDb->group_relcalc!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_relcalc"'.$check.'></td></tr>';

echo '<tr><td>'.__('TOOLS menu: show "Google maps" submenu (only shown if geolocation database was created)').'</td>';
$check=''; if ($groupDb->group_googlemaps!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_googlemaps"'.$check.'></td></tr>';

echo '<tr><td>'.__('TOOLS menu: show "Contact" submenu (only shown if tree owner and email were entered)').'</td>';
$check=''; if ($groupDb->group_contact!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_contact"'.$check.'></td></tr>';

echo '<tr><td>'.__('TOOLS menu: show "Latest changes" submenu').'</td>';
$check=''; if ($groupDb->group_latestchanges!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_latestchanges"'.$check.'></td></tr>';

echo '<tr><td>'.__('Menu item: show "Login" for visitors').'</td>';
// *** Only change this item for guest group ***
$disabled='';
if ($groupDb->group_id!='3'){
	$disabled=' disabled';
	echo '<input type="hidden" name="group_menu_login" value="'.$groupDb->group_menu_login.'">';
}
$check=''; if ($groupDb->group_menu_login!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_menu_login"'.$check.$disabled.'></td></tr>';

echo '<tr style="background-color:green; color:white"><th>'.__('General').'</font></th><th><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

echo '<tr><td>'.__('Show pictures').'</td>';
$check=''; if ($groupDb->group_pictures!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_pictures"'.$check.'></td></tr>';

echo '<tr><td>'.__('Show Gedcom number (from gedcom file)').'</td>';
$check=''; if ($groupDb->group_gedcomnr!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_gedcomnr"'.$check.'></td></tr>';

echo '<tr><td>'.__('Show residence').'</td>';
$check=''; if ($groupDb->group_living_place!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_living_place"'.$check.'></td></tr>';

echo '<tr><td>'.__('Show places with bapt., birth, death and cemetery.').'</td>';
$check=''; if ($groupDb->group_places!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_places"'.$check.'></td></tr>';

echo '<tr><td>'.__('Show religion (with bapt. and wedding)').'</td>';
$check=''; if ($groupDb->group_religion!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_religion"'.$check.'></td></tr>';

echo '<tr><td>'.__('Show date and place (i.e. with birth, bapt., death, cemetery.)').'</td>';
echo '<td><select size="1" name="group_place_date"><option value="j">Alkmaar 18 feb 1965</option>';
$selected=''; if ($groupDb->group_place_date=='n'){ $selected=' SELECTED'; }
echo '<option value="n"'.$selected.'>18 feb 1965 Alkmaar</option></select></td></tr>';

echo '<tr><td>'.__('Show name in indexes').'</td><td><select size="1" name="group_kindindex">';
echo "<option value='j'>van Mons, Henk</option>";
$selected=''; if ($groupDb->group_kindindex=='n'){ $selected=' SELECTED'; }
echo '<option value="n"'.$selected.'>Mons, Henk van</option></select></td></tr>';

echo '<tr><td>'.__('Show events').'</td>';
$check=''; if ($groupDb->group_event!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_event"'.$check.'></td></tr>';

echo '<tr><td>'.__('Show own code').'</td>';
$check=''; if ($groupDb->group_own_code!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_own_code"'.$check.'></td></tr>';

// *** First default presentation of a family page (visitor can override value) ***
echo '<tr><td>'.__('Default presentation of family page').'</td>';
echo '<td><select size="1" name="group_family_presentation">';
$selected=''; if ($groupDb->group_family_presentation=='compact'){ $selected=' SELECTED'; }
echo '<option value="compact"'.$selected.'>'.__('Compact view').'</option>';
$selected=''; if ($groupDb->group_family_presentation=='expanded'){ $selected=' SELECTED'; }
echo '<option value="expanded"'.$selected.'>'.__('Expanded view').'</option></select></td></tr>';

// *** First default presentation of Google maps in family page (visitor can override value) ***
echo '<tr><td>'.__('Default presentation of Google maps in family page').'</td>';
echo '<td><select size="1" name="group_maps_presentation">';
$selected=''; if ($groupDb->group_maps_presentation=='show'){ $selected=' SELECTED'; }
echo '<option value="show"'.$selected.'>'.__('Show Google maps').'</option>';
$selected=''; if ($groupDb->group_maps_presentation=='hide'){ $selected=' SELECTED'; }
echo '<option value="hide"'.$selected.'>'.__('Hide Google maps').'</option></select></td></tr>';

// *** Show PDF report button ***
echo '<tr><td>'.__('Show "PDF Report" button in family screen and reports').'</td>';
$check=''; if ($groupDb->group_pdf_button!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_pdf_button"'.$check.'></td></tr>';

// *** Show RTF report button ***
echo '<tr><td>'.__('Show "RTF Report" button in family screen and reports').'</td>';
$check=''; if ($groupDb->group_rtf_button!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_rtf_button"'.$check.'></td></tr>';

echo '<tr><td>'.__('User is allowed to add notes/ remarks by a person in the family tree').'. '.__('Disabled in group "Guest"').'</td>';
$disabled=''; if ($groupDb->group_id=='3'){ $disabled=' disabled';} // *** Disable this option in "Guest" group.
$check=''; if ($groupDb->group_user_notes!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_user_notes"'.$check.$disabled.'></td></tr>';

// *** Sources ***
echo '<tr style="background-color:green; color:white"><th>'.__('Sources').'</th><th><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

echo '<tr><td>'.__('Don\'t show sources').'<br>';
echo __('Only show source titles').'<br>';
echo __('Show sources and menu sources').'<br>';
echo '</td>';
$selected=''; if ($groupDb->group_sources=='n'){ $selected=' CHECKED'; }
echo '<td><input type="radio" name="group_sources" value="n"'.$selected.'><br>';
$selected=''; if ($groupDb->group_sources=='t'){ $selected=' CHECKED'; }
echo '<input type="radio" name="group_sources" value="t"'.$selected.'><br>';
$selected=''; if ($groupDb->group_sources=='j'){ $selected=' CHECKED'; }
echo '<input type="radio" name="group_sources" value="j"'.$selected.'><br>';
echo '</td></tr>';

// *** First default presentation of sources, by administrator (visitor can override value) ***
echo '<tr><td>'.__('Default presentation of source').'</td>';
echo '<td><select size="1" name="group_source_presentation">';
$selected=''; if ($groupDb->group_source_presentation=='title'){ $selected=' SELECTED'; }
echo '<option value="title"'.$selected.'>'.__('Show source title').'</option>';
$selected=''; if ($groupDb->group_source_presentation=='footnote'){ $selected=' SELECTED'; }
echo '<option value="footnote"'.$selected.'>'.__('Show source title as footnote').'</option>';
$selected=''; if ($groupDb->group_source_presentation=='hide'){ $selected=' SELECTED'; }
echo '<option value="hide"'.$selected.'>'.__('Hide sources').'</option></select></td></tr>';

echo '<tr><td>'.__('Show restricted source').'</td>';
$check=''; if ($groupDb->group_show_restricted_source!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_show_restricted_source"'.$check.'></td></tr>';

echo '<tr style="background-color:green; color:white"><th>'.__('Texts').'</th><th><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

// *** First default presentation of texts, by administrator (visitor can override value) ***
echo '<tr><td>'.__('Default presentation of text').'</td>';
echo '<td><select size="1" name="group_text_presentation">';
$selected=''; if ($groupDb->group_text_presentation=='show'){ $selected=' SELECTED'; }
echo '<option value="show"'.$selected.'>'.__('Show texts').'</option>';
$selected=''; if ($groupDb->group_text_presentation=='popup'){ $selected=' SELECTED'; }
echo '<option value="popup"'.$selected.'>'.__('Show texts in popup screen').'</option>';
$selected=''; if ($groupDb->group_text_presentation=='hide'){ $selected=' SELECTED'; }
echo '<option value="hide"'.$selected.'>'.__('Hide texts').'</option></select></td></tr>';

echo '<tr><td>'.__('Show hidden text/ own remarks (text between # characters in text fields, example: #check birthday#)').'</td>';
$check=''; if ($groupDb->group_work_text!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_work_text"'.$check.'></td></tr>';

echo '<tr><td>';

// *** SPARE ITEM ***
echo '<input type="hidden" name="group_texts" value="j">';
//echo '<tr><td>'.__('Show text at wedding [NOT YET IN USE]').'</td>';
//echo '<td><select size="1" name="group_texts"><option value="j">'.__('Yes').'</option>';
//$selected=''; if ($groupDb->group_texts=='n'){ $selected=' SELECTED'; }
//echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

echo __('Show text with person').'</td>';
$check=''; if ($groupDb->group_text_pers!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_text_pers"'.$check.'></td></tr>';

echo '<tr><td>'.__('Show text with bapt., birth, death, cemetery').'</td>';
$check=''; if ($groupDb->group_texts_pers!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_texts_pers"'.$check.'></td></tr>';

echo '<tr><td>'.__('Show text with pre-nuptial etc.').'</td>';
$check=''; if ($groupDb->group_texts_fam!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_texts_fam"'.$check.'></td></tr>';

echo '<tr style="background-color:green; color:white"><th>'.__('Privacy filter').'</th><th><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

echo '<tr><th>'.__('Activate privacy filter').'</th><td></td></tr>';

echo '<tr><td>'.__('Activate privacy filter').'<br>';
echo '<i>'.__('TIP: the best privacy filter is your genealogy program<br>
If possible, try to filter with that').'</i></td>';
// *** BE AWARE: REVERSED CHECK OF VARIABLE! ***
$check=''; if ($groupDb->group_privacy=='n') $check=' checked';
echo '<td><input type="checkbox" name="group_privacy"'.$check.'></td></tr>';

echo '<tr><th>'.__('Privacy filter settings').'</th><td></td></tr>';

echo '<tr><td>1) '.__('HuMo-gen (alive or deceased), Aldfaer (death sign), Haza-data (filter living persons)').'</td>';
$check=''; if ($groupDb->group_alive!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_alive"'.$check.'></td></tr>';

echo '<tr><td>2) '.__('Privacy filter, filter persons born in or after this year').'</td>';
$check=''; if ($groupDb->group_alive_date_act!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_alive_date_act"'.$check.'>';
echo ' '.__('Year').': <input type="text" name="group_alive_date" value="'.$groupDb->group_alive_date.'" size="4"></td></tr>';

echo '<tr><td>3) '.__('Privacy filter, filter persons deceased in or after this year').'</td>';
$check=''; if ($groupDb->group_death_date_act!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_death_date_act"'.$check.'>';
echo ' '.__('Year').': <input type="text" name="group_death_date" value="'.$groupDb->group_death_date.'" size="4"></td></tr>';

echo '<tr><td>'.__('Also filter data of deceased persons (for filter 2)').'</td>';
$check=''; if ($groupDb->group_filter_death!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_filter_death"'.$check.'></td></tr>';

echo '<tr><th>'.__('Privacy filter exceptions').'</th><td></td></tr>';

echo '<tr><td>'.__('DO show privacy data of persons (with the following text in own code)').'</td>';
$check=''; if ($groupDb->group_filter_pers_show_act!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_filter_pers_show_act"'.$check.'>';
echo ' '.__('Text').': <input type="text" name="group_filter_pers_show" value="'.$groupDb->group_filter_pers_show.'" size="10"></td></tr>';

echo '<tr><td>'.__('HIDE privacy data of persons (with the following text in own code)').'</td>';
$check=''; if ($groupDb->group_filter_pers_hide_act!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_filter_pers_hide_act"'.$check.'>';
echo ' '.__('Text').': <input type="text" name="group_filter_pers_hide" value="'.$groupDb->group_filter_pers_hide.'" size="10"></td></tr>';

echo '<tr><td>'.__('TOTALLY filter persons (with the following text in own code)').'</td>';
$check=''; if ($groupDb->group_pers_hide_totally_act!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_pers_hide_totally_act"'.$check.'>';
echo ' '.__('Text').': <input type="text" name="group_pers_hide_totally" value="'.$groupDb->group_pers_hide_totally.'" size="10"></td></tr>';

echo '<tr><th>'.__('Extra privacy filter option').'</th><td></td></tr>';

echo '<tr><td>'.__('Show persons with no date information<br>
<i>with these persons the privacy filter cannot calculate if they are alive</i>').'</td>';
$check=''; if ($groupDb->group_filter_date!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_filter_date"'.$check.'></td></tr>';

echo '<tr><td>'.__('With privacy show names').'</td>';
echo '<td><select size="1" name="group_filter_name"><option value="j">'.__('Yes').'</option>';
$selected=''; if ($groupDb->group_filter_name=='n'){ $selected=' SELECTED'; }
echo '<option value="n"'.$selected.'>'.__('No').'</option>';
$selected=''; if ($groupDb->group_filter_name=='i'){ $selected=' SELECTED'; }
echo '<option value="i"'.$selected.'>'.__('Show initials: D. E. Duck').'</option></select></td></tr>';

echo '<tr><td>'.__('Genealogical copy protection<br>
<i>family browsing disabled, no family trees</i>').'</td>';
$check=''; if ($groupDb->group_gen_protection!='n') $check=' checked';
echo '<td><input type="checkbox" name="group_gen_protection"'.$check.'></td></tr>';

echo '<tr style="background-color:green; color:white"><th bgcolor=green>';

// *** SPARE ITEM ***
echo '<input type="hidden" name="group_filter_fam" value="n">';
//echo '<tr><td>'.__('Filter family').'</td>';
//echo '<td><select size="1" name="group_filter_fam"><option value="j">'.__('Yes').'</option>';
//$selected=''; if ($groupDb->group_filter_fam=='n'){ $selected=' SELECTED'; }
//echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

// *** SPARE ITEM ***
echo '<input type="hidden" name="group_filter_total" value="n">';
//echo '<tr><td>'.__('Filter totally').'</td>';
//echo '<td><select size="1" name="group_filter_total"><option value="j">'.__('Yes').'</option>';
//$selected=''; if ($groupDb->group_filter_total=='n'){ $selected=' SELECTED'; }
//echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

echo __('Save all changes').'</th><th><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

echo '</table>';

// *** User settings per family tree (hide or show tree, edit tree etc.) ***
$group_hide_trees=$groupDb->group_hide_trees;
$group_edit_trees=$groupDb->group_edit_trees;

// *** Update tree settings ***
if (isset($_POST['group_change'])){
	$group_hide_trees=''; $group_edit_trees='';
	$data3sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY'");
	while($data3Db=$data3sql->fetch(PDO::FETCH_OBJ)){
		// *** Show/ hide trees ***
		$check='show_tree_'.$data3Db->tree_id;
		if (!isset($_POST["$check"])){
			if ($group_hide_trees!=''){ $group_hide_trees.=';'; }
			$group_hide_trees.=$data3Db->tree_id;
		}

		// *** Edit trees (NOT USED FOR ADMINISTRATOR) ***
		$check='edit_tree_'.$data3Db->tree_id;
		if (isset($_POST["$check"])){
			if ($group_edit_trees!=''){ $group_edit_trees.=';'; }
			$group_edit_trees.=$data3Db->tree_id;
		}
	}
	$sql="UPDATE humo_groups SET
		group_hide_trees='".$group_hide_trees."', 
		group_edit_trees='".$group_edit_trees."' 
		WHERE group_id=".$_POST["id"];
	$result=$dbh->query($sql);
}

$hide_tree_array=explode(";",$group_hide_trees);
$edit_tree_array=explode(";",$group_edit_trees);

echo '<h2 align="center">'.__('Hide or show family trees per user group.').'</h2>';
echo __('Editor').': '.__('If an .htpasswd file is used: add username in .htpasswd file.').'<br>';
echo __('These settings can also be set per user!');

echo '<table class="humo standard" border="1">';
	echo '<tr style="background-color:green; color:white"><th>'.__('Table prefix').'</th><th>'.__('Family tree').'</th><th>'.__('Show tree?').'</th><th>'.__('Edit tree?').' <input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

	$data3sql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
	while($data3Db=$data3sql->fetch(PDO::FETCH_OBJ)){
		$treetext=show_tree_text($data3Db->tree_prefix, $selected_language); $treetext_name=$treetext['name'];
		echo '<tr><td>'.$data3Db->tree_prefix.'</td><td>'.$treetext_name.'</td>';

		// *** Show/ hide tree for user ***
		$check=' checked'; if (in_array($data3Db->tree_id, $hide_tree_array)) $check='';
		echo '<td><input type="checkbox" name="show_tree_'.$data3Db->tree_id.'"'.$check.'></td>';

		// *** Editor rights per family tree (NOT USED FOR ADMINISTRATOR) ***
		echo '<td>';
			$check=''; if (in_array($data3Db->tree_id, $edit_tree_array)) $check=' checked';
			$disabled=''; if ($groupDb->group_admin=='j'){
				$check=' checked'; $disabled=' disabled';
				echo '<input type="hidden" name="edit_tree_'.$data3Db->tree_id.'" value="1">';
			}
			echo '<input type="checkbox" name="edit_tree_'.$data3Db->tree_id.'"'.$check.$disabled.'>';
		echo '</td>';

		echo '</tr>';
	}
echo '</table>';

echo '</form>';
?>