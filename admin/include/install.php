<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Install').'</h1>';

echo '<p>'.__('Installation of the standard tables. Or create tables from a scratch, they will be filled in with standard data.');

if (CMS_SPECIFIC=='Joomla'){
	$path_tmp='index.php?option=com_humo-gen&amp;task=admin&amp;page=install';
}
else{
	$path_tmp=$_SERVER['PHP_SELF'];
}

// *** Check if tables exists ***
$table['settings']='';
$table['trees']='';
$table['stat_date']='';
$table['users']='';
$table['groups']='';
$table['cms_menu']='';
$table['cms_pages']='';
$table['user_notes']='';
$table['user_log']='';

$query = $dbh->query("SHOW TABLES");
while($row = $query->fetch()){
	if ($row[0]=='humo_settings'){ $table['settings']="1"; }
	if ($row[0]=='humo_trees'){ $table['trees']="1"; }
	if ($row[0]=='humo_stat_date'){ $table['stat_date']="1"; }
	if ($row[0]=='humo_users'){ $table['users']="1"; }
	if ($row[0]=='humo_groups'){ $table['groups']="1"; }
	if ($row[0]=='humo_cms_menu'){ $table['cms_menu']="1"; }
	if ($row[0]=='humo_cms_pages'){ $table['cms_pages']="1"; }
	if ($row[0]=='humo_user_notes'){ $table['user_notes']="1"; }
	if ($row[0]=='humo_user_log'){ $table['user_log']="1"; }
}

if(!isset($_POST['install_tables2'])){

	if(isset($_POST['install_tables'])){
		$check_settings=''; if (isset($_POST["table_settings"])){ $check_settings=' checked'; }
		$check_trees=''; if (isset($_POST["table_trees"])){ $check_trees=' checked'; }
		$check_stat=''; if (isset($_POST["table_stat_date"])){ $check_stat=' checked'; }
		$check_users=''; if (isset($_POST["table_users"])){ $check_users=' checked'; }
		$check_groups=''; if (isset($_POST["table_groups"])){ $check_groups=' checked'; }
		$check_cms_menu=''; if (isset($_POST["table_cms_menu"])){ $check_cms_menu=' checked'; }
		$check_cms_pages=''; if (isset($_POST["table_cms_pages"])){ $check_cms_pages=' checked'; }
		$check_user_notes=''; if (isset($_POST["table_user_notes"])){ $check_user_notes=' checked'; }
		$check_log=''; if (isset($_POST["table_user_log"])){ $check_log=' checked'; }
		//$check_tags=''; if (isset($_POST["table_tags"])){ $check_tags=' checked'; }

		if (!$table['settings']) $check_settings=" checked disabled";
		if (!$table['trees']) $check_trees=" checked disabled";
		if (!$table['stat_date']) $check_stat=" checked disabled";
		if (!$table['users']) $check_users=" checked disabled";
		if (!$table['groups']) $check_groups=" checked disabled";
		if (!$table['cms_menu']) $check_cms_menu=" checked disabled";
		if (!$table['cms_pages']) $check_cms_pages=" checked disabled";
		if (!$table['user_notes']) $check_user_notes=" checked disabled";
		if (!$table['user_log']) $check_log=" checked disabled";

		$username_admin='admin'; if (isset($_POST["username_admin"])){ $username_admin=$_POST["username_admin"]; }
		$password_admin='humogen'; if (isset($_POST["password_admin"])){ $password_admin=$_POST["password_admin"]; }
		$username_family='family'; if (isset($_POST["username_family"])){ $username_family=$_POST["username_family"]; }
		$password_family='admin'; if (isset($_POST["password_family"])){ $password_family=$_POST["password_family"]; }
	}
	else{
		$check_settings=' checked disabled';
		$check_trees=' checked disabled';
		$check_stat=' checked disabled';
		$check_users=' checked disabled';
		$check_groups=' checked disabled';
		$check_cms_menu=' checked disabled';
		$check_cms_pages=' checked disabled';
		$check_user_notes=' checked disabled';
		$check_log=' checked disabled';
		//$check_tags=' checked';

		/*
		// *** If table exists, then disable checkbox ***
		$query = $dbh->query("SHOW TABLES");
		while($row = $query->fetch()){
			if ($row[0]=='humo_settings'){ $check_settings=""; }
			if ($row[0]=='humo_trees'){ $check_trees=""; }
			if ($row[0]=='humo_stat_date'){ $check_stat=""; }
			if ($row[0]=='humo_users'){ $check_users=""; }
			if ($row[0]=='humo_groups'){ $check_groups=""; }
			if ($row[0]=='humo_cms_menu'){ $check_cms_menu=""; }
			if ($row[0]=='humo_cms_pages'){ $check_cms_pages=""; }
			if ($row[0]=='humo_user_notes'){ $check_user_notes=""; }
			if ($row[0]=='humo_user_log'){ $check_log=""; }
		}
		*/

		if ($table['settings']) $check_settings="";
		if ($table['trees']) $check_trees="";
		if ($table['stat_date']) $check_stat="";
		if ($table['users']) $check_users="";
		if ($table['groups']) $check_groups="";
		if ($table['cms_menu']) $check_cms_menu="";
		if ($table['cms_pages']) $check_cms_pages="";
		if ($table['user_notes']) $check_user_notes="";
		if ($table['user_log']) $check_log="";

		$username_admin='admin';
		$password_admin='humogen';
		$username_family='family';
		$password_family='humogen';
	}

	echo '<form method="post" action="'.$path_tmp.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';

	echo '<p><input type="checkbox" name="table_settings" '.$check_settings.'> '.__('(Re)create settings table.').'<br>';

	echo '<p><input type="checkbox" name="table_stat_date" '.$check_stat.'> '.__('(Re) create statistics tree table. <b>EXISTING STATISTICS TREES WILL BE REMOVED!</b>').'<br>';

	echo '<p><input type="checkbox" name="table_users" '.$check_users.'> '.__('(Re) create user tree table. <b>The user table will be filled with new users. Please add passwords:</b>').'<br>';

		echo '<table class="humo" border="1" cellspacing="0" bgcolor="#DDFD9B" style="margin-left: 20px;">';
		echo '<tr class="table_header"><td><b>'.__('User').'</b></td><td><b>'.__('Username').'</b></td><td><b>'.__('Password').'</b></td></tr>';

		echo '<tr><td>'.__('Administrator').'</td>';
		echo '<td><input type="text" name="username_admin" value="'.$username_admin.'" size="15"></td>';
		echo '<td><input type="password" name="password_admin" value="'.$password_admin.'" size="15"> '.__('THIS WILL BE YOUR ADMIN PASSWORD! (default password = humogen)').'</td></tr>';

		echo '<tr><td>'.__('Family or genealogists').'</td>';
		echo '<td><input type="text" name="username_family" value="'.$username_family.'" size="15"></td>';
		echo '<td><input type="password" name="password_family" value="'.$password_family.'" size="15"> '.__('Password for user: "family" (default password = humogen)').'</td></tr>';

		echo '<tr><td colspan="3">'.__('Remark: more users can be added after installation.').'</td></tr>';

		echo '</table>';

	echo '<p><input type="checkbox" name="table_groups" '.$check_groups.'> '.__('(Re) create user group table.').'<br>';

	echo '<p><input type="checkbox" name="table_cms_menu" '.$check_cms_menu.'> '.__('(Re) create CMS menu table, used for menu system of own pages.').'<br>';

	echo '<p><input type="checkbox" name="table_cms_pages" '.$check_cms_pages.'> '.__('(Re) create CMS pages table, used for own pages.').'<br>';

	echo '<p><input type="checkbox" name="table_user_notes" '.$check_user_notes.'> '.__('(Re) create user notes table.').'<br>';

	echo '<p><input type="checkbox" name="table_user_log" '.$check_log.'> '.__('Empty log table.').'<br>';


	echo '<p><b>'.__('Family tree tables').'</b>';
	echo '<p><input type="checkbox" name="table_trees" '.$check_trees.'> '.
		__('(Re) create all family tree tables. <b>*** ALL EXISTING FAMILY TREES WILL BE REMOVED! ***</b>').'<br>';

	echo '<p><b>'.__('Are you sure? Old settings will be deleted!').'</b><br>';

	if(isset($_POST['install_tables'])){
		echo '<p>'.__('install').' ';
		echo '<input type="Submit" name="install_tables2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
		echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	}
	else{
		//echo '<p><b>'.__('Are you sure? Old settings will be deleted!').'</b><br>';
		echo '<p><input type="Submit" name="install_tables" value='.__('install').'>';
	}

	echo '</form>';
}


if (isset($_POST['install_tables2'])){
	// *** Check tables, table wil be created if $value="" ***
	$table_settings="";
	$table_trees="";
	$table_stat_date="";
	$table_users="";
	$table_user_log="";
	$table_cms_menu="";
	$table_cms_pages="";
	$table_user_notes="";
	$table_groups="";
	//$table_tags="";

	if ($table['settings']) $table_settings="1";
	if ($table['trees']) $table_trees="1";
	if ($table['stat_date']) $table_stat_date="1";
	if ($table['users']) $table_users="1";
	if ($table['groups']) $table_groups="1";
	if ($table['cms_menu']) $table_cms_menu="1";
	if ($table['cms_pages']) $table_cms_pages="1";
	if ($table['user_notes']) $table_user_notes="1";
	if ($table['user_log']) $table_user_log="1";

	if (isset($_POST["table_settings"])) $table_settings='';
	if (isset($_POST["table_trees"])) $table_trees='';
	if (isset($_POST["table_stat_date"])) $table_stat_date='';
	if (isset($_POST["table_users"])) $table_users='';
	if (isset($_POST["table_groups"])) $table_groups='';
	if (isset($_POST["table_cms_menu"])) $table_cms_menu='';
	if (isset($_POST["table_cms_pages"])) $table_cms_pages='';
	if (isset($_POST["table_user_notes"])) $table_user_notes='';
	if (isset($_POST["table_user_log"])) $table_user_log='';
	//if (isset($_POST["table_tags"])) $table_tags='';

	//*********************************************************************
	echo '<p><b>'.__('Creating tables:').'</b><br>';

	if (!$table_settings){
		$db_update = $dbh->query("DROP TABLE humo_settings"); // Remove table.
		//echo __('creating humo_settings...').'<br>';
		printf(__('create table: %s.'), 'humo_settings');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_settings (
			setting_id smallint(5) unsigned NOT NULL auto_increment,
			setting_variable varchar(50) CHARACTER SET utf8,
			setting_value text CHARACTER SET utf8,
			setting_order smallint(5),
			setting_tree_id smallint(5),
			PRIMARY KEY (`setting_id`)
			) DEFAULT CHARSET=utf8");
		echo __('fill humo_settings... <font color=red>Standard settings are saved in database!</font>').'<br>';
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('database_name','Web Site')");
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('homepage','http://www.humo-gen.com')");
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('homepage_description','Website')");
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('searchengine','n')");
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('robots_option','<META NAME=\"robots\" CONTENT=\"noindex,nofollow\">')");

		// *** Other settings are saved in the table in file: settings_global.php ***

		// *** Update status number. Number must be: update_status+1! ***
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('update_status','8')");
	}

	if (!$table_stat_date){
		$db_update = $dbh->query("DROP TABLE humo_stat_date");
		//echo __('Install statistics table...').'<br>';
		printf(__('create table: %s.'), 'humo_stat_date');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_stat_date (
			stat_id int(10) NOT NULL auto_increment,
			stat_easy_id varchar(100) CHARACTER SET utf8,
			stat_ip_address varchar(20) CHARACTER SET utf8,
			stat_user_agent varchar(255) CHARACTER SET utf8,
			stat_tree_id varchar(5) CHARACTER SET utf8,
			stat_gedcom_fam varchar(20) CHARACTER SET utf8,
			stat_gedcom_man varchar(20) CHARACTER SET utf8,
			stat_gedcom_woman varchar(20) CHARACTER SET utf8,
			stat_date_stat datetime NOT NULL,
			stat_date_linux varchar(50) CHARACTER SET utf8,
			PRIMARY KEY (`stat_id`)
		) DEFAULT CHARSET=utf8");
	}

	if (!$table_groups){
		$db_update = $dbh->query("DROP TABLE humo_groups");
		//echo __('creating humo_groups...').'<br>';
		printf(__('create table: %s.'), 'humo_groups');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_groups (
			group_id smallint(5) unsigned NOT NULL auto_increment,
			group_name varchar(25) CHARACTER SET utf8,
			group_privacy varchar(1) CHARACTER SET utf8,
			group_menu_places varchar(1) CHARACTER SET utf8,
			group_admin varchar(1) CHARACTER SET utf8,
			group_statistics varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j',
			group_birthday_rss varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j',
			group_birthday_list varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j',
			group_sources varchar(1) CHARACTER SET utf8,
			group_show_restricted_source varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'y',
			group_source_presentation varchar(20) CHARACTER SET utf8,
			group_pictures varchar(1) CHARACTER SET utf8,
			group_photobook varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
			group_gedcomnr varchar(1) CHARACTER SET utf8,
			group_living_place varchar(1) CHARACTER SET utf8,
			group_places varchar(1) CHARACTER SET utf8,
			group_religion varchar(1) CHARACTER SET utf8,
			group_place_date varchar(1) CHARACTER SET utf8,
			group_kindindex varchar(1) CHARACTER SET utf8,
			group_event varchar(1) CHARACTER SET utf8,
			group_addresses varchar(1) CHARACTER SET utf8,
			group_own_code varchar(1) CHARACTER SET utf8,
			group_user_notes varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
			group_family_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'compact',
			group_maps_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'hide',
			group_pdf_button varchar(1) CHARACTER SET utf8,
			group_rtf_button varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
			group_work_text varchar(1) CHARACTER SET utf8,
			group_texts varchar(1) CHARACTER SET utf8,
			group_text_pers varchar(1) CHARACTER SET utf8,
			group_texts_pers varchar(1) CHARACTER SET utf8,
			group_texts_fam varchar(1) CHARACTER SET utf8,
			group_alive varchar(1) CHARACTER SET utf8,
			group_alive_date_act varchar(1) CHARACTER SET utf8,
			group_alive_date varchar(4) CHARACTER SET utf8,
			group_death_date_act varchar(1) CHARACTER SET utf8,
			group_death_date varchar(4) CHARACTER SET utf8,
			group_filter_date varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',	
			group_filter_death varchar(1) CHARACTER SET utf8,
			group_filter_total varchar(1) CHARACTER SET utf8,
			group_filter_name varchar(1) CHARACTER SET utf8,
			group_filter_fam varchar(1) CHARACTER SET utf8,
			group_filter_pers_show_act varchar(1) CHARACTER SET utf8,
			group_filter_pers_show varchar(50) CHARACTER SET utf8,
			group_filter_pers_hide_act varchar(1) CHARACTER SET utf8,
			group_filter_pers_hide varchar(50) CHARACTER SET utf8,
			group_pers_hide_totally_act VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
			group_pers_hide_totally VARCHAR(50) CHARACTER SET utf8 NOT NULL DEFAULT 'X',
			group_gen_protection VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
			group_hide_trees VARCHAR(200) NOT NULL DEFAULT '',
			group_edit_trees VARCHAR(200) NOT NULL DEFAULT '',
			PRIMARY KEY (`group_id`)
		) DEFAULT CHARSET=utf8");
		//group_editor varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',

		echo __('filling humo_groups...').'<br>';

		$sql="INSERT INTO humo_groups SET group_name='admin', group_privacy='j', group_menu_places='j', group_admin='j',
		group_sources='j', group_pictures='j', group_gedcomnr='j', group_living_place='j', group_places='j', group_religion='j',
		group_place_date='n', group_kindindex='n', group_event='j', group_addresses='j', group_own_code='j', group_pdf_button='y', group_rtf_button='y', group_work_text='j',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='n',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		$db_update = $dbh->query($sql);

		$sql="INSERT INTO humo_groups SET group_name='family', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='j', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='j', group_addresses='j', group_own_code='j', group_pdf_button='y', group_rtf_button='n', group_work_text='j',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='n',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		$db_update = $dbh->query($sql);

		$sql="INSERT INTO humo_groups SET group_name='guest', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		$db_update = $dbh->query($sql);

		$sql="INSERT INTO humo_groups SET group_name='group 4', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		$db_update = $dbh->query($sql);

		$sql="INSERT INTO humo_groups SET group_name='group 5', group_privacy='j', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		$db_update = $dbh->query($sql);

		$sql="INSERT INTO humo_groups SET group_name='group 6', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		$db_update = $dbh->query($sql);
	}

	if (!$table_users){
		$db_update = $dbh->query("DROP TABLE humo_users");
		//echo __('creating humo_users...').'<br>';
		printf(__('create table: %s.'), 'humo_users');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_users (
			user_id smallint(5) unsigned NOT NULL auto_increment,
			user_name varchar(25) CHARACTER SET utf8,
			user_mail varchar(100) CHARACTER SET utf8,
			user_trees text CHARACTER SET utf8,
			user_remark text CHARACTER SET utf8,
			user_password varchar(50) CHARACTER SET utf8,
			user_status varchar(1) CHARACTER SET utf8,
			user_group_id smallint(5),
			user_hide_trees VARCHAR(200) NOT NULL DEFAULT '',
			user_edit_trees VARCHAR(200) NOT NULL DEFAULT '',
			user_register_date varchar(20) CHARACTER SET utf8,
			user_last_visit varchar(25) CHARACTER SET utf8,
			PRIMARY KEY  (`user_id`)
			) DEFAULT CHARSET=utf8");
		echo __('filling humo_users...').'<br>';
		$db_update = $dbh->query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('".$_POST['username_admin']."','".md5($_POST['password_admin'])."','1')");

		$db_update = $dbh->query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('".$_POST['username_family']."','".md5($_POST['password_family'])."','2')");

		$db_update = $dbh->query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('guest','".md5('guest')."','3')");
	}

	if (!$table_cms_menu){
		$db_update = $dbh->query("DROP TABLE humo_cms_menu");
		//echo __('creating humo_cms_menu...').'<br>';
		printf(__('create table: %s.'), 'humo_cms_menu');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_cms_menu (
			menu_id int(10) NOT NULL AUTO_INCREMENT,
			menu_parent_id int(10) NOT NULL DEFAULT '0',
			menu_order int(5) NOT NULL DEFAULT '0',
			menu_name varchar(25) CHARACTER SET utf8 DEFAULT '',
			PRIMARY KEY (`menu_id`)
			) DEFAULT CHARSET=utf8");
	}

	if (!$table_cms_pages){
		$db_update = $dbh->query("DROP TABLE humo_cms_pages");
		//echo __('creating humo_cms_pages...').'<br>';
		printf(__('create table: %s.'), 'humo_cms_pages');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_cms_pages (
			page_id int(10) NOT NULL AUTO_INCREMENT,
			page_status varchar(1) CHARACTER SET utf8 DEFAULT '',
			page_menu_id int(10) NOT NULL DEFAULT '0',
			page_order int(10) NOT NULL DEFAULT '0',
			page_counter int(10) NOT NULL DEFAULT '0',
			page_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			page_edit_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			page_title varchar(50) CHARACTER SET utf8 DEFAULT '',
			page_text longtext CHARACTER SET utf8,
			PRIMARY KEY (`page_id`)
			) DEFAULT CHARSET=utf8");
	}

	if (!$table_user_log){
		$db_update = $dbh->query("DROP TABLE humo_user_log");
		//echo __('creating humo_log...').'<br>';
		printf(__('create table: %s.'), 'humo_log');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_user_log (
			log_username varchar(25) CHARACTER SET utf8,
			log_date varchar(20) CHARACTER SET utf8,
			log_ip_address varchar(20) CHARACTER SET utf8 DEFAULT '',
			log_user_admin varchar(5) CHARACTER SET utf8 DEFAULT ''
			) DEFAULT CHARSET=utf8");
	}

	if (!$table_user_notes){
		$db_update = $dbh->query("DROP TABLE humo_user_notes");
		//echo __('creating humo_notes...').'<br>';
		printf(__('create table: %s.'), 'humo_notes');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_user_notes (
			note_id smallint(5) unsigned NOT NULL auto_increment,
			note_date varchar(20) CHARACTER SET utf8,
			note_time varchar(25) CHARACTER SET utf8,
			note_user_id smallint(5),
			note_note text CHARACTER SET utf8,
			note_status varchar(10) CHARACTER SET utf8,
			note_tree_prefix varchar(25) CHARACTER SET utf8,
			note_pers_gedcomnumber varchar(20) CHARACTER SET utf8,
			note_fam_gedcomnumber varchar(20) CHARACTER SET utf8,
			note_names text CHARACTER SET utf8,
			PRIMARY KEY  (`note_id`)
			) DEFAULT CHARSET=utf8");
	}


	// *** Family tree tables ***
	if (!$table_trees){
		$db_update = $dbh->query("DROP TABLE humo_trees");
		//echo __('creating humo_trees').'<br>';
		printf(__('create table: %s.'), 'humo_trees');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_trees (
			tree_id smallint(5) unsigned NOT NULL auto_increment,
			tree_order smallint(5),
			tree_prefix varchar(10) CHARACTER SET utf8,
			tree_date varchar(20) CHARACTER SET utf8,
			tree_persons varchar(10) CHARACTER SET utf8,
			tree_families varchar(10) CHARACTER SET utf8,
			tree_email varchar(100) CHARACTER SET utf8,
			tree_owner varchar(100) CHARACTER SET utf8,
			tree_pict_path varchar (100) CHARACTER SET utf8,
			tree_privacy varchar (100) CHARACTER SET utf8,
			tree_gedcom varchar (100),
			tree_gedcom_program varchar (100),
			PRIMARY KEY  (`tree_id`)
			) DEFAULT CHARSET=utf8");

		$gedcom_date=date("Y-m-d H:i");
		$sql="INSERT INTO humo_trees
		SET
		tree_order='1',
		tree_prefix='humo_',
		tree_date='".$gedcom_date."',
		tree_persons='0',
		tree_families='0',
		tree_email='',
		tree_owner='',
		tree_pict_path='../pictures/',
		tree_privacy=''
		";
		$db_update = $dbh->query($sql);

		$db_update = $dbh->query("DROP TABLE humo_tree_texts");
		//echo __('creating humo_trees').'<br>';
		printf(__('create table: %s.'), 'humo_tree_texts');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_tree_texts (
			treetext_id smallint(5) unsigned NOT NULL auto_increment,
			treetext_tree_id smallint(5),
			treetext_language varchar(100) CHARACTER SET utf8,
			treetext_name varchar(100) CHARACTER SET utf8,
			treetext_mainmenu_text text CHARACTER SET utf8,
			treetext_mainmenu_source text CHARACTER SET utf8,
			treetext_family_top text CHARACTER SET utf8,
			treetext_family_footer text CHARACTER SET utf8,
			PRIMARY KEY  (`treetext_id`)
			) DEFAULT CHARSET=utf8");

		// *** Immediately add new tables in tree ***
		$_SESSION['tree_prefix']='humo_';

		//include_once ("gedcom_tables.php");

		// *** Reset session values for editor ***
		unset($_SESSION['admin_pers_gedcomnumber']); unset($_SESSION['admin_fam_gedcomnumber']);
		unset($_SESSION['admin_tree_prefix']); unset ($_SESSION['admin_tree_id']);

		// *** Persons ***
		$db_update = $dbh->query("DROP TABLE humo_persons");
		printf(__('create table: %s.'), 'humo_persons');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_persons (
			pers_id mediumint(7) unsigned NOT NULL auto_increment,
			pers_gedcomnumber varchar(20) CHARACTER SET utf8,
			pers_tree_id mediumint(7),
			pers_tree_prefix varchar(10) CHARACTER SET utf8,
			pers_famc varchar(50) CHARACTER SET utf8,
			pers_fams varchar(150) CHARACTER SET utf8,
			pers_indexnr varchar(20) CHARACTER SET utf8,
			pers_firstname varchar(60) CHARACTER SET utf8,
			pers_callname varchar(50) CHARACTER SET utf8,
			pers_prefix varchar(20) CHARACTER SET utf8,
			pers_lastname varchar(60) CHARACTER SET utf8,
			pers_patronym varchar(50) CHARACTER SET utf8,
			pers_name_text text CHARACTER SET utf8,
			pers_sexe varchar(1) CHARACTER SET utf8,
			pers_own_code varchar(100) CHARACTER SET utf8,
		pers_birth_place varchar(75) CHARACTER SET utf8,
		pers_birth_date varchar(35) CHARACTER SET utf8,
		pers_birth_time varchar(25) CHARACTER SET utf8,
		pers_birth_text text CHARACTER SET utf8,
		pers_stillborn varchar(1) CHARACTER SET utf8 DEFAULT 'n',
		pers_bapt_place varchar(75) CHARACTER SET utf8,
		pers_bapt_date varchar(35) CHARACTER SET utf8,
		pers_bapt_text text CHARACTER SET utf8,
		pers_religion varchar(50) CHARACTER SET utf8,
		pers_death_place varchar(75) CHARACTER SET utf8,
		pers_death_date varchar(35) CHARACTER SET utf8,
		pers_death_time varchar(25) CHARACTER SET utf8,
		pers_death_text text CHARACTER SET utf8,
		pers_death_cause varchar(255) CHARACTER SET utf8,
		pers_death_age varchar(15) CHARACTER SET utf8,
		pers_buried_place varchar(75) CHARACTER SET utf8,
		pers_buried_date varchar(35) CHARACTER SET utf8,
		pers_buried_text text CHARACTER SET utf8,
		pers_cremation varchar(1) CHARACTER SET utf8,
			pers_place_index text CHARACTER SET utf8,
			pers_text text CHARACTER SET utf8,
			pers_alive varchar(20) CHARACTER SET utf8,
			pers_cal_date varchar(35) CHARACTER SET utf8,
			pers_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
			pers_new_date varchar(35) CHARACTER SET utf8,
			pers_new_time varchar(25) CHARACTER SET utf8,
			pers_changed_date varchar(35) CHARACTER SET utf8,
			pers_changed_time varchar(25) CHARACTER SET utf8,
			PRIMARY KEY (`pers_id`),
			KEY (pers_prefix),
			KEY (pers_lastname),
			KEY (pers_gedcomnumber),
			KEY (pers_tree_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// *** Families ***
		$db_update = $dbh->query("DROP TABLE humo_families");
		printf(__('create table: %s.'), 'humo_families');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_families (
			fam_id mediumint(7) unsigned NOT NULL auto_increment,
			fam_tree_id mediumint(7),
			fam_gedcomnumber varchar(20) CHARACTER SET utf8,
			fam_man varchar(20) CHARACTER SET utf8,
			fam_man_age varchar(15) CHARACTER SET utf8,
			fam_woman varchar(20) CHARACTER SET utf8,
			fam_woman_age varchar(15) CHARACTER SET utf8,
			fam_children text CHARACTER SET utf8,
			fam_kind varchar(50) CHARACTER SET utf8,
		fam_relation_date varchar(35) CHARACTER SET utf8,
		fam_relation_place varchar(75) CHARACTER SET utf8,
		fam_relation_text text CHARACTER SET utf8,
		fam_relation_end_date varchar(35) CHARACTER SET utf8,
		fam_marr_notice_date varchar(35) CHARACTER SET utf8,
		fam_marr_notice_place varchar(75) CHARACTER SET utf8,
		fam_marr_notice_text text CHARACTER SET utf8,
		fam_marr_date varchar(35) CHARACTER SET utf8,
		fam_marr_place varchar(75) CHARACTER SET utf8,
		fam_marr_text text CHARACTER SET utf8,
		fam_marr_authority text CHARACTER SET utf8,
		fam_marr_church_notice_date varchar(35) CHARACTER SET utf8,
		fam_marr_church_notice_place varchar(75) CHARACTER SET utf8,
		fam_marr_church_notice_text text CHARACTER SET utf8,
		fam_marr_church_date varchar(35) CHARACTER SET utf8,
		fam_marr_church_place varchar(75) CHARACTER SET utf8,
		fam_marr_church_text text CHARACTER SET utf8,
		fam_religion varchar(50) CHARACTER SET utf8,
		fam_div_date varchar(35) CHARACTER SET utf8,
		fam_div_place varchar(75) CHARACTER SET utf8,
		fam_div_text text CHARACTER SET utf8,
		fam_div_authority text CHARACTER SET utf8,
			fam_text text CHARACTER SET utf8,
			fam_alive int(1),
			fam_cal_date varchar(35) CHARACTER SET utf8,
			fam_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
			fam_counter mediumint(7),
			fam_new_date varchar(35) CHARACTER SET utf8,
			fam_new_time varchar(25) CHARACTER SET utf8,
			fam_changed_date varchar(35) CHARACTER SET utf8,
			fam_changed_time varchar(25) CHARACTER SET utf8,
			PRIMARY KEY (`fam_id`),
			KEY (fam_tree_id),
			KEY (fam_gedcomnumber),
			KEY (fam_man),
			KEY (fam_woman)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");


		// *** Unprocessed tags ***
		$db_update = $dbh->query("DROP TABLE humo_unprocessed_tags");
		printf(__('create table: %s.'), 'humo_unprocessed_tags');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_unprocessed_tags (
			tag_id mediumint(6) unsigned NOT NULL auto_increment,
			tag_pers_id mediumint(6),
			tag_rel_id mediumint(6),
			tag_event_id mediumint(6),
			tag_source_id mediumint(6),
			tag_connect_id mediumint(6),
			tag_repo_id mediumint(6),
			tag_place_id mediumint(6),
			tag_address_id mediumint(6),
			tag_text_id mediumint(6),
			tag_tree_id smallint(5),
			tag_tag text CHARACTER SET utf8,
			PRIMARY KEY (`tag_id`),
			KEY (tag_tree_id),
			KEY (tag_pers_id),
			KEY (tag_rel_id),
			KEY (tag_event_id),
			KEY (tag_source_id),
			KEY (tag_connect_id),
			KEY (tag_repo_id),
			KEY (tag_place_id),
			KEY (tag_address_id),
			KEY (tag_text_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// *** Repositories ***
		$db_update = $dbh->query("DROP TABLE humo_repositories");
		//echo __('creating humo_repositories...').'<br>';
		printf(__('create table: %s.'), 'humo_repositories');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_repositories (
			repo_id mediumint(6) unsigned NOT NULL auto_increment,
			repo_tree_id smallint(5),
			repo_gedcomnr varchar(20) CHARACTER SET utf8,
			repo_name text CHARACTER SET utf8,
			repo_address text CHARACTER SET utf8,
			repo_zip varchar(20) CHARACTER SET utf8,
			repo_place varchar(75) CHARACTER SET utf8,
			repo_phone varchar(20) CHARACTER SET utf8,
			repo_date varchar(35) CHARACTER SET utf8,
			repo_text text CHARACTER SET utf8,
			repo_photo text CHARACTER SET utf8,
			repo_mail varchar(100) CHARACTER SET utf8,
			repo_url varchar(150) CHARACTER SET utf8,
			repo_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
			repo_new_date varchar(35) CHARACTER SET utf8,
			repo_new_time varchar(25) CHARACTER SET utf8,
			repo_changed_date varchar(35) CHARACTER SET utf8,
			repo_changed_time varchar(25) CHARACTER SET utf8,
			PRIMARY KEY (`repo_id`),
			KEY (repo_tree_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// *** Sources ***
		$db_update = $dbh->query("DROP TABLE humo_sources");
		//echo __('creating humo_sources...').'<br>';
		printf(__('create table: %s.'), 'humo_sources');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_sources (
			source_id mediumint(6) unsigned NOT NULL auto_increment,
			source_tree_id smallint(5),
			source_status varchar(10) CHARACTER SET utf8,
			source_gedcomnr varchar(20) CHARACTER SET utf8,
			source_order mediumint(6),
			source_title text CHARACTER SET utf8,
			source_abbr varchar(50) CHARACTER SET utf8,
			source_date varchar(35) CHARACTER SET utf8,
			source_publ varchar(150) CHARACTER SET utf8,
			source_place varchar(75) CHARACTER SET utf8,
			source_refn varchar(50) CHARACTER SET utf8,
			source_auth varchar(50) CHARACTER SET utf8,
			source_subj varchar(50) CHARACTER SET utf8,
			source_item varchar(30) CHARACTER SET utf8,
			source_kind varchar(50) CHARACTER SET utf8,
			source_text text CHARACTER SET utf8,
			source_photo text CHARACTER SET utf8,
			source_repo_name varchar(50) CHARACTER SET utf8,
			source_repo_caln varchar(50) CHARACTER SET utf8,
			source_repo_page varchar(50) CHARACTER SET utf8,
			source_repo_gedcomnr varchar(20) CHARACTER SET utf8,
			source_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
			source_new_date varchar(35) CHARACTER SET utf8,
			source_new_time varchar(25) CHARACTER SET utf8,
			source_changed_date varchar(35) CHARACTER SET utf8,
			source_changed_time varchar(25) CHARACTER SET utf8,
			PRIMARY KEY (`source_id`),
			KEY (source_tree_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// *** Texts ***
		$db_update = $dbh->query("DROP TABLE humo_texts");
		printf(__('create table: %s.'), 'humo_texts');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_texts (
			text_id mediumint(6) unsigned NOT NULL auto_increment,
			text_tree_id smallint(5),
			text_gedcomnr varchar(20) CHARACTER SET utf8,
			text_text text CHARACTER SET utf8,
			text_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
			text_new_date varchar(35) CHARACTER SET utf8,
			text_new_time varchar(25) CHARACTER SET utf8,
			text_changed_date varchar(35) CHARACTER SET utf8,
			text_changed_time varchar(25) CHARACTER SET utf8,
			PRIMARY KEY (text_id),
			KEY (text_tree_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// *** Texts ***
		$db_update = $dbh->query("DROP TABLE humo_connections");
		printf(__('create table: %s.'), 'humo_connections');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_connections (
			connect_id mediumint(6) unsigned NOT NULL auto_increment,
			connect_tree_id smallint(5),
			connect_order mediumint(6),
			connect_kind varchar(25) CHARACTER SET utf8,
			connect_sub_kind varchar(30) CHARACTER SET utf8,
			connect_connect_id varchar(20) CHARACTER SET utf8,
			connect_date varchar(35) CHARACTER SET utf8,
			connect_place varchar(75) CHARACTER SET utf8,
			connect_time varchar(25) CHARACTER SET utf8,
			connect_page text CHARACTER SET utf8,
			connect_role varchar(75) CHARACTER SET utf8,
			connect_text text CHARACTER SET utf8,
			connect_source_id varchar(20) CHARACTER SET utf8,
			connect_item_id varchar(20) CHARACTER SET utf8,
			connect_status varchar(10) CHARACTER SET utf8,
			connect_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
			connect_new_date varchar(35) CHARACTER SET utf8,
			connect_new_time varchar(25) CHARACTER SET utf8,
			connect_changed_date varchar(35) CHARACTER SET utf8,
			connect_changed_time varchar(25) CHARACTER SET utf8,
			PRIMARY KEY (`connect_id`),
			KEY (connect_connect_id),
			KEY (connect_tree_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// *** Addresses ***
		$db_update = $dbh->query("DROP TABLE humo_addresses");
		printf(__('create table: %s.'), 'humo_addresses');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_addresses (
			address_id mediumint(6) unsigned NOT NULL auto_increment,
			address_tree_id smallint(5),
			address_gedcomnr varchar(20) CHARACTER SET utf8,
			address_order mediumint(6),
			address_person_id varchar(20) CHARACTER SET utf8,
			address_family_id varchar(20) CHARACTER SET utf8,
			address_address text CHARACTER SET utf8,
			address_zip varchar(20) CHARACTER SET utf8,
			address_place varchar(75) CHARACTER SET utf8,
			address_phone varchar(20) CHARACTER SET utf8,
			address_date varchar(35) CHARACTER SET utf8,
			address_text text CHARACTER SET utf8,
			address_photo text CHARACTER SET utf8,
			address_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
			address_new_date varchar(35) CHARACTER SET utf8,
			address_new_time varchar(25) CHARACTER SET utf8,
			address_changed_date varchar(35) CHARACTER SET utf8,
			address_changed_time varchar(25) CHARACTER SET utf8,
			PRIMARY KEY (`address_id`),
			KEY (address_tree_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// *** Events ***
		$db_update = $dbh->query("DROP TABLE humo_events");
		printf(__('create table: %s.'), 'humo_events');
		echo '<br>';
		$db_update = $dbh->query("CREATE TABLE humo_events (
			event_id mediumint(6) unsigned NOT NULL auto_increment,
			event_tree_id smallint(5),
			event_gedcomnr varchar(20) CHARACTER SET utf8,
			event_order mediumint(6),
			event_person_id varchar(20) CHARACTER SET utf8,
			event_pers_age varchar(15) CHARACTER SET utf8,
			event_family_id varchar(20) CHARACTER SET utf8,
			event_kind varchar(20) CHARACTER SET utf8,
			event_event text CHARACTER SET utf8,
			event_event_extra text CHARACTER SET utf8,
			event_gedcom varchar(10) CHARACTER SET utf8,
			event_date varchar(35) CHARACTER SET utf8,
			event_place varchar(75) CHARACTER SET utf8,
			event_text text CHARACTER SET utf8,
			event_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
			event_new_date varchar(35) CHARACTER SET utf8,
			event_new_time varchar(25) CHARACTER SET utf8,
			event_changed_date varchar(35) CHARACTER SET utf8,
			event_changed_time varchar(25) CHARACTER SET utf8,
			PRIMARY KEY (`event_id`),
			KEY (event_tree_id),
			KEY (event_person_id),
			KEY (event_family_id),
			KEY (event_kind)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");

	} // *** End of family tree tables ***

	echo '<b>'.__('No errors above? This means that the database has been processed!').'</b><br>';
	echo __('All updates completed, click at "Mainmenu"').'.';
	echo ' <a href="index.php">'.__('Main menu').'</a>';
}
?>