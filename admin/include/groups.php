<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

include_once (CMS_ROOTPATH.'include/database_name.php');
global $selected_language;

if(CMS_SPECIFIC=="Joomla") {
	$phpself = "index.php?option=com_humo-gen&amp;task=admin&amp;page=groups";
}
else {
	$phpself = $_SERVER['PHP_SELF'];
}
echo '<h1 align=center>'.__('User groups').'</h1>';
 
if (isset($_POST['group_add'])){
	$sql="INSERT INTO humo_groups SET group_name='new groep', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_source_presentation='title', group_user_notes='n', group_show_restricted_source='y',
		group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j',
		group_religion='n', group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n',
		group_own_code='n', group_pdf_button='y', group_work_text='n', group_texts='j',
		group_menu_persons='j', group_menu_names='j', group_menu_login='j',
		group_showstatistics='j', group_relcalc='j', group_googlemaps='j', group_contact='j', group_latestchanges='j',
		group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='j', group_death_date='1980',
		group_filter_death='n', group_filter_total='n', group_filter_name='j',
		group_filter_fam='j', group_filter_pers_show_act='j', group_filter_pers_show='*', group_filter_pers_hide_act='n',
		group_filter_pers_hide='#'";
	//$db_update = mysql_query($sql) or die(mysql_error());
	$db_update = $dbh->query($sql);
}
 
if (isset($_POST['group_change'])){

	if ($_POST["group_filter_pers_show"]==''){ $_POST["group_filter_pers_show"]='*'; }
	if ($_POST["group_filter_pers_hide"]==''){ $_POST["group_filter_pers_hide"]='#'; }
	if ($_POST["group_pers_hide_totally"]==''){ $_POST["group_pers_hide_totally"]='X'; }

	if (!isset($_POST["group_user_notes"])){ $_POST["group_user_notes"]='n'; }

	$sql="UPDATE humo_groups SET
	group_name='".$_POST["group_name"]."',
	group_editor='".$_POST["group_editor"]."',
	group_statistics='".$_POST["group_statistics"]."',
	group_privacy='".$_POST["group_privacy"]."',
	group_menu_places='".$_POST["group_menu_places"]."',
	group_admin='".$_POST["group_admin"]."',
	group_sources='".$_POST["group_sources"]."',
	group_show_restricted_source='".$_POST["group_show_restricted_source"]."',
	group_source_presentation='".$_POST["group_source_presentation"]."',
	group_user_notes='".$_POST["group_user_notes"]."',
	group_birthday_rss='".$_POST["group_birthday_rss"]."',
	group_menu_persons='".$_POST["group_menu_persons"]."',
	group_menu_names='".$_POST["group_menu_names"]."',
	group_menu_login='".$_POST["group_menu_login"]."',
	group_birthday_list='".$_POST["group_birthday_list"]."',
	group_showstatistics='".$_POST["group_showstatistics"]."',
	group_relcalc='".$_POST["group_relcalc"]."',
	group_googlemaps='".$_POST["group_googlemaps"]."',
	group_contact='".$_POST["group_contact"]."',
	group_latestchanges='".$_POST["group_latestchanges"]."',
	group_photobook='".$_POST["group_photobook"]."',
	group_pictures='".$_POST["group_pictures"]."',
	group_gedcomnr='".$_POST["group_gedcomnr"]."',
	group_living_place='".$_POST["group_living_place"]."',
	group_places='".$_POST["group_places"]."',
	group_religion='".$_POST["group_religion"]."',
	group_place_date='".$_POST["group_place_date"]."',
	group_kindindex='".$_POST["group_kindindex"]."',
	group_event='".$_POST["group_event"]."',
	group_addresses='".$_POST["group_addresses"]."',
	group_own_code='".$_POST["group_own_code"]."',
	group_pdf_button='".$_POST["group_pdf_button"]."',
	group_work_text='".$_POST["group_work_text"]."',
	group_texts='".$_POST["group_texts"]."',
	group_text_pers='".$_POST["group_text_pers"]."',
	group_texts_pers='".$_POST["group_texts_pers"]."',
	group_texts_fam='".$_POST["group_texts_fam"]."',
	group_alive='".$_POST["group_alive"]."',
	group_alive_date_act='".$_POST["group_alive_date_act"]."',
	group_alive_date='".$_POST["group_alive_date"]."',
	group_death_date_act='".$_POST["group_death_date_act"]."',
	group_death_date='".$_POST["group_death_date"]."',
	group_filter_death='".$_POST["group_filter_death"]."',
	group_filter_total='".$_POST["group_filter_total"]."',
	group_filter_name='".$_POST["group_filter_name"]."',
	group_filter_fam='".$_POST["group_filter_fam"]."',
	group_filter_date='".$_POST["group_filter_date"]."',
	group_filter_pers_show_act='".$_POST["group_filter_pers_show_act"]."',
	group_filter_pers_show='".$_POST["group_filter_pers_show"]."',
	group_filter_pers_hide_act='".$_POST["group_filter_pers_hide_act"]."',
	group_filter_pers_hide='".$_POST["group_filter_pers_hide"]."',
	group_pers_hide_totally_act='".$_POST["group_pers_hide_totally_act"]."',
	group_pers_hide_totally='".$_POST["group_pers_hide_totally"]."',
	group_gen_protection='".$_POST["group_gen_protection"]."'
	WHERE group_id=".$_POST["id"];
	//echo $sql;
	//$result=mysql_query($sql) or die(mysql_error());
	$result=$dbh->query($sql);
}

if (isset($_POST['group_remove'])){
	echo '<div class="confirm">';
	$usersql="SELECT * FROM humo_users WHERE user_group_id=".$_POST["id"];
	//$user=mysql_query($usersql,$db);
	//$nr_users=mysql_num_rows($user);
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
	//$db_update = mysql_query($sql) or die(mysql_error());
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
	//$groupresult=mysql_query($groupsql,$db);
	$groupresult=$dbh->query($groupsql);
	echo '<br><table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';
		print '<b>'.__('Choose a user group: ').'</b> ';
		if(CMS_SPECIFIC=="Joomla") { echo "<br>"; }  // not enough space for text and buttons
		//while ($groupDb=mysql_fetch_object($groupresult)){
		while ($groupDb=$groupresult->fetch(PDO::FETCH_OBJ)){
			$selected='';
			if ($show_group_id==$groupDb->group_id){ $selected=' class="selected_item"'; }
				echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
				echo '<input type="hidden" name="page" value="'.$page.'">';
				print '<input type="hidden" name="show_group_id" value="'.$groupDb->group_id.'">';
				$group_name=$groupDb->group_name; if ($group_name==''){ $group_name='NO NAME'; }
				print ' <input type="Submit" name="submit" value="'.$group_name.'"'.$selected.'>';
			print '</form>';
		}

		// *** Add group ***
		echo '<form method="POST" action="'.$phpself.'" style="display : inline;">';
			echo '<input type="hidden" name="page" value="'.$page.'">';
			print ' <input type="Submit" name="group_add" value="'.__('ADD GROUP').'">';
		print '</form>';
	echo '</td></tr></table><br>';

	// *** Show usergroup ***
	$groupsql="SELECT * FROM humo_groups WHERE group_id='".$show_group_id."'";
	//$groupresult=mysql_query($groupsql,$db);
	//$groupDb=mysql_fetch_object($groupresult);
	$groupresult=$dbh->query($groupsql);
	$groupDb=$groupresult->fetch(PDO::FETCH_OBJ);	

	// *** Automatic installation or update ***
	//$column_qry = mysql_query('SHOW COLUMNS FROM humo_groups');
	$column_qry = $dbh->query('SHOW COLUMNS FROM humo_groups');
	//while ($columnDb = mysql_fetch_assoc($column_qry)) {
	while ($columnDb = $column_qry->fetch()) {
		$field_value=$columnDb['Field'];
		$field[$field_value]=$field_value;
	}
	if (!isset($field['group_source_presentation'])){
		$sql="ALTER TABLE humo_groups
			ADD group_source_presentation VARCHAR(20) NOT NULL DEFAULT 'title' AFTER group_sources;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_show_restricted_source'])){
		$sql="ALTER TABLE humo_groups
			ADD group_show_restricted_source VARCHAR(1) NOT NULL DEFAULT 'y' AFTER group_sources;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_death_date_act'])){
		$sql="ALTER TABLE humo_groups
			ADD group_death_date_act VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_alive_date;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_death_date'])){
		$sql="ALTER TABLE humo_groups
			ADD group_death_date VARCHAR(4) NOT NULL DEFAULT '1980' AFTER group_death_date_act;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_menu_persons'])){
		$sql="ALTER TABLE humo_groups
			ADD group_menu_persons VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_statistics;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_menu_names'])){
		$sql="ALTER TABLE humo_groups
			ADD group_menu_names VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_statistics;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_menu_login'])){
		$sql="ALTER TABLE humo_groups
			ADD group_menu_login VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_menu_names;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_showstatistics'])){
		$sql="ALTER TABLE humo_groups
			ADD group_showstatistics VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_relcalc'])){
		$sql="ALTER TABLE humo_groups
			ADD group_relcalc VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_googlemaps'])){
		$sql="ALTER TABLE humo_groups
			ADD group_googlemaps VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_contact'])){
		$sql="ALTER TABLE humo_groups
			ADD group_contact VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_latestchanges'])){
		$sql="ALTER TABLE humo_groups
			ADD group_latestchanges VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_pdf_button'])){
		$sql="ALTER TABLE humo_groups
			ADD group_pdf_button VARCHAR(1) NOT NULL DEFAULT 'y' AFTER group_own_code;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
	if (!isset($field['group_user_notes'])){
		$sql="ALTER TABLE humo_groups
			ADD group_user_notes VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_own_code;";
		//$result=mysql_query($sql) or die(mysql_error());
		$result=$dbh->query($sql);
	}
 
	// *** Renew data AFTER updates ***
	$groupsql="SELECT * FROM humo_groups WHERE group_id='".$show_group_id."'";
	//$groupresult=mysql_query($groupsql,$db);
	//$groupDb=mysql_fetch_object($groupresult);
	$groupresult=$dbh->query($groupsql);
	$groupDb=$groupresult->fetch(PDO::FETCH_OBJ);	


	echo '<form method="POST" action="'.$phpself.'">';
	echo '<input type="hidden" name="page" value="'.$page.'">';
	print "<input type='hidden' name='show_group_id' value='".$show_group_id."'>";
	print '<input type="hidden" name="id" value="'.$groupDb->group_id.'">';

	echo '<table class="humo standard" border="1">';
	print '<tr class="table_header"><th>'.__('Option').'</th><th>'.__('Value').'</th></tr>';

	//print '<tr><th bgcolor=green><font color=white>'.__('Group').'</font></th><th bgcolor=green><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

	echo '<tr><th bgcolor=green><font color=white>'.__('Group');
		if ($groupDb->group_id>'3'){
			echo ' <input type="Submit" name="group_remove" value="'.__('REMOVE GROUP').'">';
		}
		echo '</font></th><th bgcolor=green><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';
	print '<tr><td>'.__('Group name').'</td><td><input type="text" name="group_name" value="'.$groupDb->group_name.'" size="15"></td>';

	$group_editor=$groupDb->group_editor; if ($groupDb->group_admin=='j'){ $group_editor='j'; }
	// *** Administrator group ***
	if ($groupDb->group_id=='1'){
		print '<tr><td>'.__('Administrator').'</td><td><b>'.$groupDb->group_admin.'</b></td></tr>';
		print '<input type="hidden" name="group_admin" value="'.$groupDb->group_admin.'">';

		print '<tr><td>'.__('Editor. Use the name "editor" in .htpasswd.').'</td><td><b>'.$group_editor.'</b></td></tr>';
		print '<input type="hidden" name="group_editor" value="'.$group_editor.'">';
	}
	else{
		print '<tr><td>'.__('Administrator').'</td><td><select size="1" name="group_admin"><option value="j">'.__('Yes').'</option>';
		$selected=''; if ($groupDb->group_admin=='n'){ $selected=' SELECTED'; }
		echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

		print '<tr><td>'.__('Editor.').' '.__('If an .htpasswd file is used: add username in .htpasswd file.').'</td><td><select size="1" name="group_editor"><option value="j">'.__('Yes').'</option>';
		$selected=''; if ($group_editor=='n'){ $selected=' SELECTED'; }
		echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';
	}

	print '<tr><td>'.__('Save statistics data').'</td><td><select size="1" name="group_statistics"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_statistics=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><th bgcolor=green><font color=white>Menu</font></th><th bgcolor=green><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

	//print '<tr><td>OLD!!! '.__('Show sources and menu sources').'</td>';
	//print '<td><select size="1" name="group_sources"><option value="j">'.__('Yes').'</option>';
	//$selected=''; if ($groupDb->group_sources=='n'){ $selected=' SELECTED'; }
	//echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Birthday RSS in main menu').'</td>';
	print '<td><select size="1" name="group_birthday_rss"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_birthday_rss=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('FAMILY TREE menu: show "Persons" submenu').'</td>';
	print '<td><select size="1" name="group_menu_persons"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_menu_persons=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('FAMILY TREE menu: show "Names" submenu').'</td>';
	print '<td><select size="1" name="group_menu_names"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_menu_names=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('FAMILY TREE menu: show "Places" submenu').'</td>';
	print '<td><select size="1" name="group_menu_places"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_menu_places=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('FAMILY TREE menu: show "Addresses" submenu (only shown if there really are addresses)').'</td>';
	print '<td><select size="1" name="group_addresses"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_addresses=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('FAMILY TREE menu: show "Photobook" submenu').'</td>';
	print '<td><select size="1" name="group_photobook"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_photobook=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('TOOLS menu: show "Anniversary" (birthday list) submenu').'</td>';
	print '<td><select size="1" name="group_birthday_list"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_birthday_list=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('TOOLS menu: show "Statistics" submenu').'</td>';
	print '<td><select size="1" name="group_showstatistics"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_showstatistics=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';


	print '<tr><td>'.__('TOOLS menu: show "Relationship Calculator" submenu').'</td>';
	print '<td><select size="1" name="group_relcalc"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_relcalc=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';


	print '<tr><td>'.__('TOOLS menu: show "Google maps" submenu (only shown if geolocation database was created)').'</td>';
	print '<td><select size="1" name="group_googlemaps"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_googlemaps=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';


	print '<tr><td>'.__('TOOLS menu: show "Contact" submenu (only shown if tree owner and email were entered)').'</td>';
	print '<td><select size="1" name="group_contact"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_contact=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';


	print '<tr><td>'.__('TOOLS menu: show "Latest changes" submenu').'</td>';
	print '<td><select size="1" name="group_latestchanges"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_latestchanges=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Menu item: show "Login" for visitors (only change this setting for usergroup containing user "guest")').'</td>';
	print '<td><select size="1" name="group_menu_login"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_menu_login=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><th bgcolor=green><font color=white>'.__('General').'</font></th><th bgcolor=green><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

	print '<tr><td>'.__('Show pictures').'</td>';
	print '<td><select size="1" name="group_pictures"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_pictures=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show Gedcom number (from gedcom file)').'</td>';
	print '<td><select size="1" name="group_gedcomnr"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_gedcomnr=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show residence').'</td>';
	print '<td><select size="1" name="group_living_place"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_living_place=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show places with bapt., birth, death and cemetery.').'</td>';
	print '<td><select size="1" name="group_places"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_places=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show religion (with bapt. and wedding)').'</td>';
	print '<td><select size="1" name="group_religion"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_religion=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show date and place (i.e. with birth, bapt., death, cemetery.)').'</td>';
	print '<td><select size="1" name="group_place_date"><option value="j">Alkmaar 18 feb 1965</option>';
	$selected=''; if ($groupDb->group_place_date=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>18 feb 1965 Alkmaar</option></select></td></tr>';

	print '<tr><td>'.__('Show name in indexes').'</td><td><select size="1" name="group_kindindex">';
	echo "<option value='j'>van Mons, Henk</option>";
	$selected=''; if ($groupDb->group_kindindex=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>Mons, Henk van</option></select></td></tr>';

	print '<tr><td>'.__('Show events').'</td>';
	print '<td><select size="1" name="group_event"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_event=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show own code').'</td>';
	print '<td><select size="1" name="group_own_code"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_own_code=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show "PDF Report" button in family screen and reports').'</td>';
	print '<td><select size="1" name="group_pdf_button"><option value="y">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_pdf_button=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	// *** First default presentation of sources, by administrator (visitor can override value) ***
	print '<tr><td>'.__('Default presentation of source').'</td>';
	print '<td><select size="1" name="group_source_presentation">';
	$selected=''; if ($groupDb->group_source_presentation=='title'){ $selected=' SELECTED'; }
	echo '<option value="title"'.$selected.'>'.__('Show source title').'</option>';
	$selected=''; if ($groupDb->group_source_presentation=='footnote'){ $selected=' SELECTED'; }
	echo '<option value="footnote"'.$selected.'>'.__('Show source title as footnote').'</option>';
	$selected=''; if ($groupDb->group_source_presentation=='sources'){ $selected=' SELECTED'; }
	echo '<option value="sources"'.$selected.'>'.__('Hide sources').'</option></select></td></tr>';

	print '<tr><td>'.__('User is allowed to add notes/ remarks by a person in the family tree').'. '.__('Disabled in group "Guest"').'</td>';
	$disabled=''; if ($groupDb->group_id=='3'){ $disabled=' disabled';} // *** Disable this option in "Guest" group.
	print '<td><select size="1" name="group_user_notes"'.$disabled.'><option value="y">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_user_notes=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	// *** Sources ***
	print '<tr><th bgcolor=green><font color=white>'.__('Sources').'</font></th><th bgcolor=green><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

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

	print '<tr><td>'.__('Show restricted source').'</td><td><select size="1" name="group_show_restricted_source"><option value="y">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_show_restricted_source=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><th bgcolor=green><font color=white>'.__('Texts').'</font></th><th bgcolor=green><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

	print '<tr><td>'.__('Show work texts (In Haza-Data: #werktekst#)').'</td>';
	print '<td><select size="1" name="group_work_text"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_work_text=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>';

	// *** SPARE ITEM ***
	print '<input type="hidden" name="group_texts" value="j">';
	//print '<tr><td>'.__('Show text at wedding [NOT YET IN USE]').'</td>';
	//print '<td><select size="1" name="group_texts"><option value="j">'.__('Yes').'</option>';
	//$selected=''; if ($groupDb->group_texts=='n'){ $selected=' SELECTED'; }
	//echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print __('Show text with person').'</td>';
	print '<td><select size="1" name="group_text_pers"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_text_pers=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show text with bapt., birth, death, cemetery').'</td>';
	print '<td><select size="1" name="group_texts_pers"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_texts_pers=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('Show text with pre-nuptial etc.').'</td>';
	print '<td><select size="1" name="group_texts_fam"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_texts_fam=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><th bgcolor=green><font color=white>'.__('Privacy filter').'</font></th><th bgcolor=green><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

	print '<tr><th>'.__('Activate privacy filter').'</th><td></td></tr>';

	print '<tr><td>'.__('Activate privacy filter').'<br>';
	print '<i>'.__('TIP: the best privacy filter is your genealogy program<br>
If possible, try to filter with that').'</i></td>';
	print '<td><select size="1" name="group_privacy"><option value="j">'.__('No').'</option>';
	$selected=''; if ($groupDb->group_privacy=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('Yes').'</option></select></td></tr>';

	print '<tr><th>'.__('Privacy filter settings').'</th><td></td></tr>';

	//print '<tr><td>Aldfaer (vinkje ovl.) of Haza-data: levende personen filteren</td>';
	print '<tr><td>1) '.__('HuMo-gen (alive or deceased), Aldfaer (death sign), Haza-data (filter living persons)').'</td>';
	print '<td><select size="1" name="group_alive"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_alive=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>2) '.__('Privacy filter, filter persons born in or after this year').'</td>';
	print '<td><select size="1" name="group_alive_date_act"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_alive_date_act=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select>';
	echo ' '.__('Year').': <input type="text" name="group_alive_date" value="'.$groupDb->group_alive_date.'" size="4"></td></tr>';

	print '<tr><td>3) '.__('Privacy filter, filter persons deceased in or after this year').'</td>';
	print '<td><select size="1" name="group_death_date_act"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_death_date_act=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select>';
	echo ' '.__('Year').': <input type="text" name="group_death_date" value="'.$groupDb->group_death_date.'" size="4"></td></tr>';

	print '<tr><td>'.__('Also filter data of deceased persons (for filter 2)').'</td>';
	print '<td><select size="1" name="group_filter_death"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_filter_death=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><th>'.__('Privacy filter exceptions').'</th><td></td></tr>';

	print '<tr><td>'.__('DO show privacy data of persons (with the following text in own code)').'</td>';
	print '<td><select size="1" name="group_filter_pers_show_act"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_filter_pers_show_act=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select>';
	echo ' '.__('Text').': <input type="text" name="group_filter_pers_show" value="'.$groupDb->group_filter_pers_show.'" size="10"></td></tr>';

	print '<tr><td>'.__('HIDE privacy data of persons (with the following text in own code)').'</td>';
	print '<td><select size="1" name="group_filter_pers_hide_act"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_filter_pers_hide_act=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select>';
	echo ' '.__('Text').': <input type="text" name="group_filter_pers_hide" value="'.$groupDb->group_filter_pers_hide.'" size="10"></td></tr>';

	print '<tr><td>'.__('TOTALLY filter persons (with the following text in own code)').'</td>';
	print '<td><select size="1" name="group_pers_hide_totally_act"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_pers_hide_totally_act=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select>';
	echo ' '.__('Text').': <input type="text" name="group_pers_hide_totally" value="'.$groupDb->group_pers_hide_totally.'" size="10"></td></tr>';

	print '<tr><th>'.__('Extra privacy filter option').'</th><td></td></tr>';

	print '<tr><td>'.__('Show persons with no date information<br>
<i>with these persons the privacy filter cannot calculate if they are alive</i>').'</td>';
	print '<td><select size="1" name="group_filter_date"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_filter_date=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<tr><td>'.__('With privacy show names').'</td>';
	print '<td><select size="1" name="group_filter_name"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_filter_name=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option>';
	$selected=''; if ($groupDb->group_filter_name=='i'){ $selected=' SELECTED'; }
	echo '<option value="i"'.$selected.'>'.__('Show initials: D. E. Duck').'</option></select></td></tr>';

	print '<tr><td>'.__('Genealogical copy protection<br>
<i>family browsing disabled, no family trees</i>').'</td>';
	print '<td><select size="1" name="group_gen_protection"><option value="j">'.__('Yes').'</option>';
	$selected=''; if ($groupDb->group_gen_protection=='n'){ $selected=' SELECTED'; }
	echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	echo '<tr><th bgcolor=green>';

	// *** SPARE ITEM ***
	print '<input type="hidden" name="group_filter_fam" value="n">';
	//print '<tr><td>'.__('Filter family').'</td>';
	//print '<td><select size="1" name="group_filter_fam"><option value="j">'.__('Yes').'</option>';
	//$selected=''; if ($groupDb->group_filter_fam=='n'){ $selected=' SELECTED'; }
	//echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	// *** SPARE ITEM ***
	print '<input type="hidden" name="group_filter_total" value="n">';
	//print '<tr><td>'.__('Filter totally').'</td>';
	//print '<td><select size="1" name="group_filter_total"><option value="j">'.__('Yes').'</option>';
	//$selected=''; if ($groupDb->group_filter_total=='n'){ $selected=' SELECTED'; }
	//echo '<option value="n"'.$selected.'>'.__('No').'</option></select></td></tr>';

	print '<font color=white>'.__('Save all changes').'</font></th><th bgcolor=green><input type="Submit" name="group_change" value="'.__('Change').'"></th></tr>';

	echo '</table>';
	print '</form>';

	// *** Hide or show family trees per user group ***
	echo '<h2>'.__('Hide or show family trees per user group.').'</h2>';
	echo '<table class="humo standard" border="1">';
	print '<tr bgcolor=green><th><font color=white>'.__('Family tree').'</font></th><th></th><th><font color=white>'.__('Show tree?').'</font></th></tr>';

		$hide_tree_array=explode(";",$groupDb->group_hide_trees);

		if (isset($_POST['hide_tree'])){
			// *** Add new hide tree ***
			$group_hide_trees='';
			if ($groupDb->group_hide_trees!=''){ $group_hide_trees=';'; }
			$group_hide_trees.=safe_text($_POST['hide_tree']);
			$sql="UPDATE humo_groups SET group_hide_trees='".$groupDb->group_hide_trees.$group_hide_trees."'
			WHERE group_id=".$_POST["id"];
			//$result=mysql_query($sql) or die(mysql_error());
			$result=$dbh->query($sql);
		}

		if (isset($_POST['show_tree'])){
			// *** Rebuild hide_tree_array ***
			$group_hide_trees='';
			for ($x=0; $x<=count($hide_tree_array)-1; $x++){
				if ($hide_tree_array[$x]!=$_POST['show_tree']){
					if ($group_hide_trees!=''){ $group_hide_trees.=';'; }
					$group_hide_trees.=$hide_tree_array[$x];
				}
			}
			$sql="UPDATE humo_groups SET group_hide_trees='".$group_hide_trees."'
			WHERE group_id=".$_POST["id"];
			//$result=mysql_query($sql) or die(mysql_error());
			$result=$dbh->query($sql);
		}

		// *** Renew data after update ***
		$groupsql="SELECT * FROM humo_groups WHERE group_id='".$show_group_id."'";
		//$groupresult=mysql_query($groupsql,$db);
		//$groupDb=mysql_fetch_object($groupresult);
		$groupresult=$dbh->query($groupsql);
		$groupDb=$groupresult->fetch(PDO::FETCH_OBJ);		
		$hide_tree_array=explode(";",$groupDb->group_hide_trees);

		//$data3sql = mysql_query("SELECT * FROM humo_trees ORDER BY tree_order",$db);
		//while($data3Db=mysql_fetch_object($data3sql)){
		$data3sql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
		while($data3Db=$data3sql->fetch(PDO::FETCH_OBJ)){		
			if ($data3Db->tree_prefix!='EMPTY'){
				$treetext_name=database_name($data3Db->tree_prefix, $selected_language);
				echo '<tr><td>';

				echo $data3Db->tree_prefix.'</td><td>'.$treetext_name.'</td>';
				echo '<td>';
					echo '<form method="POST" action="'.$phpself.'">';
						echo '<input type="hidden" name="page" value="'.$page.'">';
						print " <input type='hidden' name='show_group_id' value='".$show_group_id."'>";
						print '<input type="hidden" name="id" value="'.$groupDb->group_id.'">';

						$hide_tree=false;
						for ($x=0; $x<=count($hide_tree_array)-1; $x++){
							if ($hide_tree_array[$x]==$data3Db->tree_id){ $hide_tree=true; }
						}
						if ($hide_tree==true){
							echo '<b>'.__('No').'</b>';
							print '<input type="hidden" name="show_tree" value="'.$data3Db->tree_id.'">';
							echo ' <input type="Submit" name="tree_id_yes" value="'.__('Yes').'">';
						}
						else{
							echo '<b>'.__('Yes').'</b>';
							print '<input type="hidden" name="hide_tree" value="'.$data3Db->tree_id.'">';
							echo ' <input type="Submit" name="submit" value="'.__('No').'">';
						}
					print '</form>';
				echo '</td></tr>';
			}
		}
	echo '</table>';

//}

?>