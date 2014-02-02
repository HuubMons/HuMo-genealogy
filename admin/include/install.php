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
		$username_admin='admin'; if (isset($_POST["username_admin"])){ $username_admin=$_POST["username_admin"]; }
		$password_admin='humogen'; if (isset($_POST["password_admin"])){ $password_admin=$_POST["password_admin"]; }
		$username_family='family'; if (isset($_POST["username_family"])){ $username_family=$_POST["username_family"]; }
		$password_family='admin'; if (isset($_POST["password_family"])){ $password_family=$_POST["password_family"]; }
	}
	else{
		$check_settings=' checked';
		$check_trees=' checked';
		$check_stat=' checked';
		$check_users=' checked';
		$check_groups=' checked';
		$check_cms_menu=' checked';
		$check_cms_pages=' checked';
		$check_user_notes=' checked';
		$check_log=' checked';
		$username_admin='admin';
		$password_admin='humogen';
		$username_family='family';
		$password_family='humogen';
	}

	echo '<form method="post" action="'.$path_tmp.'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$page.'">';

	print '<p><input type="checkbox" name="table_settings" '.$check_settings.'> '.__('(Re)create settings table.').'<br>';

	print '<p><input type="checkbox" name="table_trees" '.$check_trees.'> '.__('(Re) create family tree table. <b>EXISTING FAMILY TREES WILL BE REMOVED!</b>').'<br>';

	print '<p><input type="checkbox" name="table_stat_date" '.$check_stat.'> '.__('(Re) create statistics tree table. <b>EXISTING STATISTICS TREES WILL BE REMOVED!</b>').'<br>';

	print '<p><input type="checkbox" name="table_users" '.$check_users.'> '.__('(Re) create user tree table. <b>The user table will be filled with new users. Please add passwords:</b>').'<br>';

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
		
	print '<p><input type="checkbox" name="table_groups" '.$check_groups.'> '.__('(Re) create user group table.').'<br>';

	print '<p><input type="checkbox" name="table_cms_menu" '.$check_cms_menu.'> '.__('(Re) create CMS menu table, used for menu system of own pages.').'<br>';

	print '<p><input type="checkbox" name="table_cms_pages" '.$check_cms_pages.'> '.__('(Re) create CMS pages table, used for own pages.').'<br>';

	print '<p><input type="checkbox" name="table_user_notes" '.$check_user_notes.'> '.__('(Re) create user notes table.').'<br>';

	print '<p><input type="checkbox" name="table_user_log" '.$check_log.'> '.__('Empty log table.').'<br>';

	print '<p><b>'.__('Are you sure? Old settings will be deleted!').'</b><br>';

	if(isset($_POST['install_tables'])){
		echo '<p>'.__('install').' ';
		echo '<input type="Submit" name="install_tables2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
		echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	}
	else{
		//print '<p><b>'.__('Are you sure? Old settings will be deleted!').'</b><br>';
		print '<p><input type="Submit" name="install_tables" value='.__('install').'>';
	}

	echo '</form>';
}


if (isset($_POST['install_tables2'])){

	// *** Check tables, table wil be created if $value=1 ***
	$table_settings="1";
	$table_trees="1";
	$table_stat_date="1";
	$table_users="1";
	$table_user_log="1";
	$table_cms_menu="1";
	$table_cms_pages="1";
	$table_user_notes="1";
	$table_groups="1";

	//$query = mysql_query("SHOW TABLES");
	//while($row = mysql_fetch_array($query)){
	$query = $dbh->query("SHOW TABLES");
	while($row = $query->fetch()){	
		if ($row[0]=='humo_settings'){ $table_settings=""; }
		if ($row[0]=='humo_trees'){ $table_trees=""; }
		if ($row[0]=='humo_stat_date'){ $table_stat_date=""; }
		if ($row[0]=='humo_users'){ $table_users=""; }
		if ($row[0]=='humo_groups'){ $table_groups=""; }
		if ($row[0]=='humo_cms_menu'){ $table_cms_menu=""; }
		if ($row[0]=='humo_cms_pages'){ $table_cms_pages=""; }
		if ($row[0]=='humo_user_notes'){ $table_cms_menu=""; }
		if ($row[0]=='humo_user_log'){ $table_user_log=""; }
	}

	if (isset($_POST["table_settings"])){ $table_settings='1'; }
	if (isset($_POST["table_trees"])){ $table_trees='1'; }
	if (isset($_POST["table_stat_date"])){ $table_stat_date='1'; }
	if (isset($_POST["table_users"])){ $table_users='1'; }
	if (isset($_POST["table_groups"])){ $table_groups='1'; }
	if (isset($_POST["table_cms_menu"])){ $table_cms_menu='1'; }
	if (isset($_POST["table_cms_pages"])){ $table_cms_pages='1'; }
	if (isset($_POST["table_user_notes"])){ $table_cms_pages='1'; }
	if (isset($_POST["table_user_log"])){ $table_user_log='1'; }
	
	//*********************************************************************
	print '<p><b>'.__('Creating tables:').'</b><br>';
	
	if ($table_settings){
		//$db_update = mysql_query("DROP TABLE humo_settings"); // Remove table.
		$db_update = $dbh->query("DROP TABLE humo_settings"); // Remove table.
		print __('creating humo_settings...').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_settings (
			setting_id smallint(5) unsigned NOT NULL auto_increment,
			setting_variable varchar(50) CHARACTER SET utf8,
			setting_value text CHARACTER SET utf8,
			PRIMARY KEY (`setting_id`)
			) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
		$db_update = $dbh->query("CREATE TABLE humo_settings (
			setting_id smallint(5) unsigned NOT NULL auto_increment,
			setting_variable varchar(50) CHARACTER SET utf8,
			setting_value text CHARACTER SET utf8,
			PRIMARY KEY (`setting_id`)
			) DEFAULT CHARSET=utf8");		
		print __('fill humo_settings... <font color=red>Standard settings are saved in database!</font>').'<br>';
		/*
		$db_update = mysql_query("INSERT INTO humo_settings (setting_variable,setting_value) values ('database_name','Web Site')") or die(mysql_error());
		$db_update = mysql_query("INSERT INTO humo_settings (setting_variable,setting_value) values ('homepage','http://www.humo-gen.com')") or die(mysql_error());
		$db_update = mysql_query("INSERT INTO humo_settings (setting_variable,setting_value) values ('homepage_description','Website')") or die(mysql_error());
		$db_update = mysql_query("INSERT INTO humo_settings (setting_variable,setting_value) values ('searchengine','n')") or die(mysql_error());
		$db_update = mysql_query("INSERT INTO humo_settings (setting_variable,setting_value) values ('robots_option','<META NAME=\"robots\" CONTENT=\"noindex,nofollow\">')") or die(mysql_error());
		*/
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('database_name','Web Site')");
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('homepage','http://www.humo-gen.com')");
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('homepage_description','Website')");
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('searchengine','n')");
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('robots_option','<META NAME=\"robots\" CONTENT=\"noindex,nofollow\">')");		
		// *** Update status number. Number must be: update_status+1! ***
		//$db_update = mysql_query("INSERT INTO humo_settings (setting_variable,setting_value) values ('update_status','7')") or die(mysql_error());
		$db_update = $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('update_status','7')");
	}

	if ($table_trees){
		//$db_update = mysql_query("DROP TABLE humo_trees");
		$db_update = $dbh->query("DROP TABLE humo_trees");
		print __('creating humo_trees').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_trees (
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
			) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
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
		//$db_update = mysql_query($sql) or die(mysql_error());
		$db_update = $dbh->query($sql);
	
		//$db_update = mysql_query("DROP TABLE humo_tree_texts");
		$db_update = $dbh->query("DROP TABLE humo_tree_texts");
		print __('creating humo_trees').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_tree_texts (
			treetext_id smallint(5) unsigned NOT NULL auto_increment,
			treetext_tree_id smallint(5),
			treetext_language varchar(100) CHARACTER SET utf8,
			treetext_name varchar(100) CHARACTER SET utf8,
			treetext_mainmenu_text text CHARACTER SET utf8,
			treetext_mainmenu_source text CHARACTER SET utf8,
			treetext_family_top text CHARACTER SET utf8,
			treetext_family_footer text CHARACTER SET utf8,
			PRIMARY KEY  (`treetext_id`)
			) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
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
	}
	
	if ($table_stat_date){
		//$db_update = mysql_query("DROP TABLE humo_stat_date");
		$db_update = $dbh->query("DROP TABLE humo_stat_date");
		print __('Install statistics table...').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_stat_date (
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
		) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
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

	if ($table_groups){
		//$db_update = mysql_query("DROP TABLE humo_groups");
		$db_update = $dbh->query("DROP TABLE humo_groups");
		print __('creating humo_groups...').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_groups (
			group_id smallint(5) unsigned NOT NULL auto_increment,
			group_name varchar(25) CHARACTER SET utf8,
			group_privacy varchar(1) CHARACTER SET utf8,			group_menu_places varchar(1) CHARACTER SET utf8,
			group_admin varchar(1) CHARACTER SET utf8,
			group_editor varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
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
			group_pdf_button varchar(1) CHARACTER SET utf8,
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
			PRIMARY KEY (`group_id`)
		) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
		$db_update = $dbh->query("CREATE TABLE humo_groups (
			group_id smallint(5) unsigned NOT NULL auto_increment,
			group_name varchar(25) CHARACTER SET utf8,
			group_privacy varchar(1) CHARACTER SET utf8,
			group_menu_places varchar(1) CHARACTER SET utf8,
			group_admin varchar(1) CHARACTER SET utf8,
			group_editor varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
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
			group_pdf_button varchar(1) CHARACTER SET utf8,
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
			PRIMARY KEY (`group_id`)
		) DEFAULT CHARSET=utf8");		

		print __('filling humo_groups...').'<br>';
	
		$sql="INSERT INTO humo_groups SET group_name='admin', group_privacy='j', group_menu_places='j', group_admin='j',
		group_sources='j', group_pictures='j', group_gedcomnr='j', group_living_place='j', group_places='j', group_religion='j',
		group_place_date='n', group_kindindex='n', group_event='j', group_addresses='j', group_own_code='j', group_pdf_button='y' ,group_work_text='j',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='n',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		//$db_update = mysql_query($sql) or die(mysql_error());
		$db_update = $dbh->query($sql);
	
		$sql="INSERT INTO humo_groups SET group_name='family', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='j', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='j', group_addresses='j', group_own_code='j', group_pdf_button='y', group_work_text='j',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='n',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		//$db_update = mysql_query($sql) or die(mysql_error());
		$db_update = $dbh->query($sql);
	
		$sql="INSERT INTO humo_groups SET group_name='guest', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_work_text='n',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		//$db_update = mysql_query($sql) or die(mysql_error());
		$db_update = $dbh->query($sql);
	
		$sql="INSERT INTO humo_groups SET group_name='group 4', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_work_text='n',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		//$db_update = mysql_query($sql) or die(mysql_error());
		$db_update = $dbh->query($sql);
	
		$sql="INSERT INTO humo_groups SET group_name='group 5', group_privacy='j', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_work_text='n',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		//$db_update = mysql_query($sql) or die(mysql_error());
		$db_update = $dbh->query($sql);
	
		$sql="INSERT INTO humo_groups SET group_name='group 6', group_privacy='n', group_menu_places='n', group_admin='n',
		group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
		group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_work_text='n',
		group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
		group_alive_date='1920', group_death_date_act='n',
		group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
		group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
		//$db_update = mysql_query($sql) or die(mysql_error());
		$db_update = $dbh->query($sql);
	}
	
	if ($table_users){
		//$db_update = mysql_query("DROP TABLE humo_users");
		$db_update = $dbh->query("DROP TABLE humo_users");
		print __('creating humo_users...').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_users (
			user_id smallint(5) unsigned NOT NULL auto_increment,
			user_name varchar(25) CHARACTER SET utf8,
			user_mail varchar(100) CHARACTER SET utf8,
			user_trees text CHARACTER SET utf8,
			user_remark text CHARACTER SET utf8,
			user_password varchar(50) CHARACTER SET utf8,
			user_status varchar(1) CHARACTER SET utf8,
			user_group_id SMALLINT(5),
			user_register_date varchar(20) CHARACTER SET utf8,
			user_last_visit varchar(25) CHARACTER SET utf8,
			PRIMARY KEY  (`user_id`)
			) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
		$db_update = $dbh->query("CREATE TABLE humo_users (
			user_id smallint(5) unsigned NOT NULL auto_increment,
			user_name varchar(25) CHARACTER SET utf8,
			user_mail varchar(100) CHARACTER SET utf8,
			user_trees text CHARACTER SET utf8,
			user_remark text CHARACTER SET utf8,
			user_password varchar(50) CHARACTER SET utf8,
			user_status varchar(1) CHARACTER SET utf8,
			user_group_id smallint(5),
			user_register_date varchar(20) CHARACTER SET utf8,
			user_last_visit varchar(25) CHARACTER SET utf8,
			PRIMARY KEY  (`user_id`)
			) DEFAULT CHARSET=utf8");
			
		print __('filling humo_users...').'<br>';
		//$db_update = mysql_query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('".$_POST['username_admin']."','".md5($_POST['password_admin'])."','1')") or die(mysql_error());
		$db_update = $dbh->query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('".$_POST['username_admin']."','".md5($_POST['password_admin'])."','1')");

		//$db_update = mysql_query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('".$_POST['username_family']."','".md5($_POST['password_family'])."','2')") or die(mysql_error());
		$db_update = $dbh->query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('".$_POST['username_family']."','".md5($_POST['password_family'])."','2')");

		//$db_update = mysql_query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('guest','".md5('guest')."','3')") or die(mysql_error());
		$db_update = $dbh->query("INSERT INTO humo_users (user_name, user_password, user_group_id) values ('guest','".md5('guest')."','3')");
	}

	if ($table_cms_menu){
		//$db_update = mysql_query("DROP TABLE humo_cms_menu");
		$db_update = $dbh->query("DROP TABLE humo_cms_menu");
		print __('creating humo_cms_menu...').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_cms_menu (
			menu_id int(10) NOT NULL AUTO_INCREMENT,
			menu_parent_id int(10) NOT NULL DEFAULT '0',
			menu_order int(5) NOT NULL DEFAULT '0',
			menu_name varchar(25) CHARACTER SET utf8 DEFAULT '',
			PRIMARY KEY (`menu_id`)
			) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
		$db_update = $dbh->query("CREATE TABLE humo_cms_menu (
			menu_id int(10) NOT NULL AUTO_INCREMENT,
			menu_parent_id int(10) NOT NULL DEFAULT '0',
			menu_order int(5) NOT NULL DEFAULT '0',
			menu_name varchar(25) CHARACTER SET utf8 DEFAULT '',
			PRIMARY KEY (`menu_id`)
			) DEFAULT CHARSET=utf8");		
	}

	if ($table_cms_pages){
		//$db_update = mysql_query("DROP TABLE humo_cms_pages");
		$db_update = $dbh->query("DROP TABLE humo_cms_pages");
		print __('creating humo_cms_pages...').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_cms_pages (
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
			) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
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

	if ($table_user_log){
		//$db_update = mysql_query("DROP TABLE humo_user_log");
		$db_update = $dbh->query("DROP TABLE humo_user_log");
		print __('creating humo_log...').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_user_log (
			log_username varchar(25) CHARACTER SET utf8,
			log_date varchar(20) CHARACTER SET utf8,
			log_ip_address varchar(20) CHARACTER SET utf8 DEFAULT '',
			log_user_admin varchar(5) CHARACTER SET utf8 DEFAULT ''
			) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
		$db_update = $dbh->query("CREATE TABLE humo_user_log (
			log_username varchar(25) CHARACTER SET utf8,
			log_date varchar(20) CHARACTER SET utf8,
			log_ip_address varchar(20) CHARACTER SET utf8 DEFAULT '',
			log_user_admin varchar(5) CHARACTER SET utf8 DEFAULT ''
			) DEFAULT CHARSET=utf8");		
	}

	if ($table_user_notes){
		//$db_update = mysql_query("DROP TABLE humo_user_notes");
		$db_update = $dbh->query("DROP TABLE humo_user_notes");
		print __('creating humo_notes...').'<br>';
		/*
		$db_update = mysql_query("CREATE TABLE humo_user_notes (
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
			) DEFAULT CHARSET=utf8") or die(mysql_error());
		*/
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

	print '<b>'.__('No errors above? This means that the database has been processed!').'</b><br>';
}
?>