<?php
// *** Version line, DO NOT CHANGE THIS LINE ***
// Version nummering: 1.1.1.1 (main number, sub number, update, etc.)
$humo_option["version"]='4.9.4';  // Version line, DO NOT CHANGE THIS LINE
// *** RC release candidate. Numbering like: RC2, RC3. ***
//$humo_option["version"]='RC1 20 oct 2009';  // Version line, DO NOT CHANGE THIS LINE
// *** Beta (not stable enough for production, but it's functional ***
//$humo_option["version"]='BETA version 28 nov. 2013';  // Version line, DO NOT CHANGE THIS LINE
// *** Alpha version not stable enough for production***
//$humo_option["version"]='ALPHA version 27 nov 2009';  // Version line, DO NOT CHANGE THIS LINE
//$humo_option["version"]='TEST version 19 jul 2011';  // Version line, DO NOT CHANGE THIS LINE
//$humo_option["version"]='EXPERIMENTAL VERSION 13 nov 2009';  // Version line, DO NOT CHANGE THIS LINE

// *** Version date, needed for update check ***
$humo_option["version_date"]='2013-12-15';  // Version date mm-dd-yyyy, DO NOT CHANGE THIS LINE

// *** Test lines for update procedure ***
//$humo_option["version_date"]='2012-01-01';  // Version date mm-dd-yyyy, DO NOT CHANGE THIS LINE
//$humo_option["version_date"]='2012-11-30';  // Version date mm-dd-yyyy, DO NOT CHANGE THIS LINE


// *** If needed: translate setting_variabele into setting variable ***
//$update_setting_qry = mysql_query("SELECT * FROM humo_settings",$db);
$update_setting_qry = $dbh->query("SELECT * FROM humo_settings");

//$update_settingDb = mysql_fetch_object($update_setting_qry);
$update_settingDb = $update_setting_qry->fetch(PDO::FETCH_OBJ);

if (isset($update_settingDb->setting_variabele)){
	$sql="ALTER TABLE humo_settings CHANGE setting_variabele setting_variable VARCHAR( 50 ) CHARACTER SET utf8 NULL DEFAULT NULL";
	//mysql_query($sql,$db);
	$dbh->query($sql);
}

// *** Update table humo_settings: translate dutch variables into english... ***
//$update_setting_qry = mysql_query("SELECT * FROM humo_settings",$db);
$update_setting_qry = $dbh->query("SELECT * FROM humo_settings");
//while( $update_settingDb = mysql_fetch_object($update_setting_qry)){
while($update_settingDb = $update_setting_qry->fetch(PDO::FETCH_OBJ)){
	$setting='';
	if ($update_settingDb->setting_variable=='database_naam') { $setting='database_name'; }
	if ($update_settingDb->setting_variable=='homepage_omschrijving') { $setting='homepage_description'; }
	if ($update_settingDb->setting_variable=='zoekmachine') { $setting='searchengine'; }
	if ($update_settingDb->setting_variable=='optierobots') { $setting='robots_option'; }
	if ($update_settingDb->setting_variable=='parenteel_generaties') { $setting='descendant_generations'; }
	if ($update_settingDb->setting_variable=='personen_weergeven') { $setting='show_persons'; }

	if ($setting){
		$sql='UPDATE humo_settings SET setting_variable="'.$setting.'"
		WHERE setting_variable="'.$update_settingDb->setting_variable.'"';
		//$update_Db = mysql_query($sql,$db) or die(mysql_error());
		$update_Db = $dbh->query($sql);
	}
}

// *** Read settings from database ***
//@$result = mysql_query("SELECT * FROM humo_settings",$db);
@$result = $dbh->query("SELECT * FROM humo_settings");
//while( @$row = mysql_fetch_row($result)){
while( @$row = $result->fetch(PDO::FETCH_NUM)){
	$humo_option[$row[1]] = $row[2];
}

// *** Automatic installation or update ***
if (!isset($humo_option["rss_link"])){
	$humo_option["rss_link"]='http://';
	$sql="INSERT INTO humo_settings SET setting_variable='rss_link', setting_value='http://'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["descendant_generations"])){
	$humo_option["descendant_generations"]='4';
	$sql="INSERT INTO humo_settings SET setting_variable='descendant_generations', setting_value='4'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["show_persons"])){
	$humo_option["show_persons"]='30';
	$sql="INSERT INTO humo_settings SET setting_variable='show_persons', setting_value='30'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["url_rewrite"])){
	$humo_option["url_rewrite"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='url_rewrite', setting_value='n'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["default_skin"])){
	$humo_option["default_skin"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='default_skin', setting_value=''";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["default_language"])){
	$humo_option["default_language"]='en';
	$sql="INSERT INTO humo_settings SET setting_variable='default_language', setting_value='en'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["default_language_admin"])){
	$humo_option["default_language_admin"]='en';
	$sql="INSERT INTO humo_settings SET setting_variable='default_language_admin', setting_value='en'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["timezone"])){
	$humo_option["timezone"]='Europe/Amsterdam';
	$sql="INSERT INTO humo_settings SET setting_variable='timezone', setting_value='Europe/Amsterdam'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

// *** Automatic installation or update ***
if (!isset($humo_option["update_status"])){	
	$humo_option["update_status"]='0';
	$sql="INSERT INTO humo_settings SET setting_variable='update_status', setting_value='0'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

// *** Mail form spam question ***
if (!isset($humo_option["block_spam_question"])){
	$humo_option["block_spam_question"]='What is the capital of England?';
	$sql="INSERT INTO humo_settings SET setting_variable='block_spam_question', setting_value='what is the capital of England?'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["block_spam_answer"])){
	$humo_option["block_spam_question"]='london';
	$sql="INSERT INTO humo_settings SET setting_variable='block_spam_answer', setting_value='london'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["use_spam_question"])){
	$humo_option["use_spam_question"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='use_spam_question', setting_value='n'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["visitor_registration"])){
	$humo_option["visitor_registration"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='visitor_registration', setting_value='n'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["visitor_registration_group"])){
	$humo_option["visitor_registration_group"]='3';
	$sql="INSERT INTO humo_settings SET setting_variable='visitor_registration_group', setting_value='3'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["registration_use_spam_question"])){
	$humo_option["registration_use_spam_question"]='y';
	$sql="INSERT INTO humo_settings SET setting_variable='registration_use_spam_question', setting_value='y'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["update_last_check"])){
	$humo_option["update_last_check"]='2012_01_01';
	$sql="INSERT INTO humo_settings SET setting_variable='update_last_check', setting_value='2012_01_01'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["update_text"])){
	$humo_option["update_text"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='update_text', setting_value=''";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["searchengine_cms_only"])){
	$humo_option["searchengine_cms_only"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='searchengine_cms_only', setting_value='n'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

// *** Gedcom reading settings 18-aug-2013 ***
if (!isset($humo_option["gedcom_read_reassign_gedcomnumbers"])){
	$humo_option["gedcom_read_reassign_gedcomnumbers"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_reassign_gedcomnumbers', setting_value='n'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_order_by_date"])){
	$humo_option["gedcom_read_order_by_date"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_order_by_date', setting_value='n'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_process_geo_location"])){
	$humo_option["gedcom_read_process_geo_location"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_process_geo_location', setting_value='n'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_commit_records"])){
	$humo_option["gedcom_read_commit_records"]='500';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_commit_records', setting_value='500'";
	//@$result=mysql_query($sql) or die(mysql_error());
	@$result=$dbh->query($sql);	
}
?>