<?php
// *** Version line, DO NOT CHANGE THIS LINE ***
// Version nummering: 1.1.1.1 (main number, sub number, update, etc.)
$humo_option["version"]='5.7.2';  // Version line, DO NOT CHANGE THIS LINE
// *** Beta (not stable enough for production, but it's functional ***
//$humo_option["version"]='BETA version 28 nov. 2019';  // Version line, DO NOT CHANGE THIS LINE
//$humo_option["version"]='TEST version 11 oct. 2011';  // Version line, DO NOT CHANGE THIS LINE

// *** Version date, needed for update check ***
//$humo_option["version_date"]='2019-09-01';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE
$humo_option["version_date"]='2021-04-04';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE

// *** Test lines for update procedure ***
//$humo_option["version_date"]='2012-01-01';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE
//$humo_option["version_date"]='2012-11-30';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE


// *** Database updates (can be moved to database update script later) ***
// ..............................


// *** If needed: translate setting_variabele into setting variable ***
$update_setting_qry = $dbh->query("SELECT * FROM humo_settings");
$update_settingDb = $update_setting_qry->fetch(PDO::FETCH_OBJ);
if (isset($update_settingDb->setting_variabele)){
	$sql="ALTER TABLE humo_settings CHANGE setting_variabele setting_variable VARCHAR( 50 ) CHARACTER SET utf8 NULL DEFAULT NULL";
	$dbh->query($sql);
}

// *** Update table humo_settings: translate dutch variables into english... ***
$update_setting_qry = $dbh->query("SELECT * FROM humo_settings");
while($update_settingDb = $update_setting_qry->fetch(PDO::FETCH_OBJ)){
	$setting='';
	if ($update_settingDb->setting_variable=='database_naam') { $setting='database_name'; }
	if ($update_settingDb->setting_variable=='homepage_omschrijving') { $setting='homepage_description'; }
	if ($update_settingDb->setting_variable=='zoekmachine') { $setting='searchengine'; }
	if ($update_settingDb->setting_variable=='optierobots') { $setting='robots_option'; }
	if ($update_settingDb->setting_variable=='parenteel_generaties') { $setting='descendant_generations'; }
	if ($update_settingDb->setting_variable=='personen_weergeven') { $setting='show_persons'; }

	if ($setting){
		$sql='UPDATE humo_settings SET setting_variable="'.$setting.'" WHERE setting_variable="'.$update_settingDb->setting_variable.'"';
		$update_Db = $dbh->query($sql);
	}
}


// *** Read settings from database ***
@$result = $dbh->query("SELECT * FROM humo_settings");
while( @$row = $result->fetch(PDO::FETCH_NUM)){
	$humo_option[$row[1]] = $row[2];
}

// *** Automatic installation or update ***

//if (!isset($humo_option["template_homepage"])){
// THIS PART CAN BE MOVED TO DATABASE UPDATE IF NEEDED.
if (!isset($humo_option["template_homepage"]) AND $humo_option["update_status"] > 10){
	$order=1;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|left|select_family_tree', setting_order='".$order."'";
	@$result=$dbh->query($sql);

	$order++;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|selected_family_tree', setting_order='".$order."'";
	@$result=$dbh->query($sql);

	$order++;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|names|2|4', setting_order='".$order."'";
	@$result=$dbh->query($sql);

	$order++;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|alphabet', setting_order='".$order."'";
	@$result=$dbh->query($sql);

	$order++;
	// *** Replace old "today in history setting"  ***
	if (isset($humo_option["today_in_history_show"])){
		$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|history', setting_order='".$order."'";
		@$result=$dbh->query($sql);

		$sql="DELETE FROM humo_settings WHERE setting_variable='today_in_history_show'";
		$result=$dbh->query($sql);
	}
	else{
		$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='inactive|center|history', setting_order='".$order."'";
		@$result=$dbh->query($sql);
	}

	$order++;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|favourites', setting_order='".$order."'";
	@$result=$dbh->query($sql);

	$order++;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='inactive|center|text', setting_order='".$order."'";
	@$result=$dbh->query($sql);

	$order++;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='inactive|center|cms_page', setting_order='".$order."'";
	@$result=$dbh->query($sql);

	$order++;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|right|search', setting_order='".$order."'";
	@$result=$dbh->query($sql);

	$order++;
	$sql="INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='inactive|right|random_photo', setting_order='".$order."'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["rss_link"])){
	$humo_option["rss_link"]='http://';
	$sql="INSERT INTO humo_settings SET setting_variable='rss_link', setting_value='http://'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["descendant_generations"])){
	$humo_option["descendant_generations"]='4';
	$sql="INSERT INTO humo_settings SET setting_variable='descendant_generations', setting_value='4'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["show_persons"])){
	$humo_option["show_persons"]='30';
	$sql="INSERT INTO humo_settings SET setting_variable='show_persons', setting_value='30'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["url_rewrite"])){
	$humo_option["url_rewrite"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='url_rewrite', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["default_skin"])){
	$humo_option["default_skin"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='default_skin', setting_value=''";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["default_language"])){
	$humo_option["default_language"]='en';
	$sql="INSERT INTO humo_settings SET setting_variable='default_language', setting_value='en'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["default_language_admin"])){
	$humo_option["default_language_admin"]='en';
	$sql="INSERT INTO humo_settings SET setting_variable='default_language_admin', setting_value='en'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["text_footer"])){
	$humo_option["text_footer"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='text_footer', setting_value=''";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["timezone"])){
	$humo_option["timezone"]='Europe/Amsterdam';
	$sql="INSERT INTO humo_settings SET setting_variable='timezone', setting_value='Europe/Amsterdam'";
	@$result=$dbh->query($sql);
}

// *** Automatic installation or update ***
if (!isset($humo_option["update_status"])){
	$humo_option["update_status"]='0';
	$sql="INSERT INTO humo_settings SET setting_variable='update_status', setting_value='0'";
	@$result=$dbh->query($sql);
}

// *** Mail form spam question ***
if (!isset($humo_option["block_spam_question"])){
	$humo_option["block_spam_question"]='What is the capital of England?';
	$sql="INSERT INTO humo_settings SET setting_variable='block_spam_question', setting_value='what is the capital of England?'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["block_spam_answer"])){
	$humo_option["block_spam_question"]='london';
	$sql="INSERT INTO humo_settings SET setting_variable='block_spam_answer', setting_value='london'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["use_spam_question"])){
	$humo_option["use_spam_question"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='use_spam_question', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["use_newsletter_question"])){
	$humo_option["use_newsletter_question"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='use_newsletter_question', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["visitor_registration"])){
	$humo_option["visitor_registration"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='visitor_registration', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["general_email"])){
	$humo_option["general_email"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='general_email', setting_value=''";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["visitor_registration_group"])){
	$humo_option["visitor_registration_group"]='3';
	$sql="INSERT INTO humo_settings SET setting_variable='visitor_registration_group', setting_value='3'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["registration_use_spam_question"])){
	$humo_option["registration_use_spam_question"]='y';
	$sql="INSERT INTO humo_settings SET setting_variable='registration_use_spam_question', setting_value='y'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["password_retreival"])){
	$humo_option["password_retreival"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='password_retreival', setting_value=''";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["update_last_check"])){
	$humo_option["update_last_check"]='2012_01_01';
	$sql="INSERT INTO humo_settings SET setting_variable='update_last_check', setting_value='2012_01_01'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["update_text"])){
	$humo_option["update_text"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='update_text', setting_value=''";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["searchengine_cms_only"])){
	$humo_option["searchengine_cms_only"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='searchengine_cms_only', setting_value='n'";
	@$result=$dbh->query($sql);
}

// *** Gedcom reading settings 18 aug 2013, updated 30 may 2015. ***
if (!isset($humo_option["gedcom_read_add_source"])){
	$humo_option["gedcom_read_add_source"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_add_source', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_reassign_gedcomnumbers"])){
	$humo_option["gedcom_read_reassign_gedcomnumbers"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_reassign_gedcomnumbers', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_order_by_date"])){
	$humo_option["gedcom_read_order_by_date"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_order_by_date', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_order_by_fams"])){
	$humo_option["gedcom_read_order_by_fams"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_order_by_fams', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_process_geo_location"])){
	$humo_option["gedcom_read_process_geo_location"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_process_geo_location', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_process_pict_path"])){
	$humo_option["gedcom_process_pict_path"]='file_name';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_process_pict_path', setting_value='file_name'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_save_pictures"])){
	$humo_option["gedcom_read_save_pictures"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_save_pictures', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_commit_records"])){
	$humo_option["gedcom_read_commit_records"]='500';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_commit_records', setting_value='500'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["gedcom_read_time_out"])){
	$humo_option["gedcom_read_time_out"]='0';
	$sql="INSERT INTO humo_settings SET setting_variable='gedcom_read_time_out', setting_value='0'";
	@$result=$dbh->query($sql);
}

// *** Watermark text and color in PDF file ***
if (!isset($humo_option["watermark_text"])){
	$humo_option["watermark_text"]=''; $sql="INSERT INTO humo_settings SET setting_variable='watermark_text', setting_value=''";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["watermark_color_r"])){
	$humo_option["watermark_color_r"]=''; $sql="INSERT INTO humo_settings SET setting_variable='watermark_color_r', setting_value='224'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["watermark_color_g"])){
	$humo_option["watermark_color_g"]=''; $sql="INSERT INTO humo_settings SET setting_variable='watermark_color_g', setting_value='224'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["watermark_color_b"])){
	$humo_option["watermark_color_b"]=''; $sql="INSERT INTO humo_settings SET setting_variable='watermark_color_b', setting_value='224'";
	@$result=$dbh->query($sql);
}

// *** Minimum characters in search boxes
if (!isset($humo_option["min_search_chars"])){
	$humo_option["min_search_chars"]='3'; $sql="INSERT INTO humo_settings SET setting_variable='min_search_chars', setting_value='3'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["date_display"])){
	$humo_option["date_display"]='eu'; $sql="INSERT INTO humo_settings SET setting_variable='date_display', setting_value='eu'";
	@$result=$dbh->query($sql);	
}
if (!isset($humo_option["name_order"])){
	$humo_option["name_order"]='western'; $sql="INSERT INTO humo_settings SET setting_variable='name_order', setting_value='western'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["default_timeline"])){
	$humo_option["default_timeline"]=''; $sql="INSERT INTO humo_settings SET setting_variable='default_timeline', setting_value=''";
	@$result=$dbh->query($sql);
}
// one name study display
if (!isset($humo_option["one_name_study"])){
	$humo_option["one_name_study"]='n'; $sql="INSERT INTO humo_settings SET setting_variable='one_name_study', setting_value='n'";
	@$result=$dbh->query($sql);
}
// one name study setting of the name
if (!isset($humo_option["one_name_thename"])){
	$humo_option["one_name_thename"]=''; $sql="INSERT INTO humo_settings SET setting_variable='one_name_thename', setting_value=''";
	@$result=$dbh->query($sql);	
}
if (!isset($humo_option["geo_trees"])){
	$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
	if(!$temp->rowCount()) { 
		// no humo_location table was created yet. just enter the geo_trees setting with empty value to be ready if needed in future
		$humo_option["geo_trees"]=''; $sql="INSERT INTO humo_settings SET setting_variable='geo_trees', setting_value=''";
		@$result=$dbh->query($sql);	
	} 
	else {
		// A humo_location table already exists. A situation where there is a location table but no geo_trees setting can only happen one time
		// when upgrading to the first version that introduces this option. We'll check and enter the required tree_ids of trees that have been indexed.
		$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY'";
		$tree_search_result = $dbh->query($tree_search_sql);
		$geo_string=""; // string to hold the tree_ids of the trees that have been indexed in the location table
		while ($tree_searchDb=$tree_search_result->fetch(PDO::FETCH_OBJ)){  // for each tree...

			// make sure the location_status column exists. If not create it
			$result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
			$exists = $result->rowCount();
			if(!$exists) {
				$dbh->query("ALTER TABLE humo_location ADD location_status TEXT AFTER location_lng");
			}

			$found=false; 
			$loc_sql = "SELECT location_status FROM humo_location";
			$loc_result = $dbh->query($loc_sql); 
			while($loc_resultDb = $loc_result->fetch(PDO::FETCH_OBJ)) {
				if(strpos($loc_resultDb->location_status,$tree_searchDb->tree_prefix)!==false) {  // the tree prefix is listed
					$found=true; // this tree should be included in the geo_trees setting
				}
			} 
			if($found===true) { $geo_string .= "@".$tree_searchDb->tree_id.";"; } // we create string: @4;@12;@13; which can also be searched by strpos
		}
		$humo_option["geo_trees"]=$geo_string; 
		$sql="INSERT INTO humo_settings SET setting_variable='geo_trees', setting_value='".$geo_string."'";
		@$result=$dbh->query($sql);
	}
}

// *** Slideshow_show homepage ***
if (!isset($humo_option["slideshow_show"])){
	$humo_option["slideshow_show"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='slideshow_show', setting_value='n'";
	@$result=$dbh->query($sql);
}
// *** Slideshow slide 1 ***
if (!isset($humo_option["slideshow_01"])){
	$humo_option["slideshow_01"]='|';
	$sql="INSERT INTO humo_settings SET setting_variable='slideshow_01', setting_value='|'";
	@$result=$dbh->query($sql);
}
// *** Slideshow slide 2 ***
if (!isset($humo_option["slideshow_02"])){
	$humo_option["slideshow_02"]='|';
	$sql="INSERT INTO humo_settings SET setting_variable='slideshow_02', setting_value='|'";
	@$result=$dbh->query($sql);
}
// *** Slideshow slide 3 ***
if (!isset($humo_option["slideshow_03"])){
	$humo_option["slideshow_03"]='|';
	$sql="INSERT INTO humo_settings SET setting_variable='slideshow_03', setting_value='|'";
	@$result=$dbh->query($sql);
}
// *** Slideshow slide 4 ***
if (!isset($humo_option["slideshow_04"])){
	$humo_option["slideshow_04"]='|';
	$sql="INSERT INTO humo_settings SET setting_variable='slideshow_04', setting_value='|'";
	@$result=$dbh->query($sql);
}

// *** Jewish settings ***
if (!isset($humo_option["david_stars"])){
	$humo_option["david_stars"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='david_stars', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["death_char"])){
	$humo_option["death_char"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='death_char', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["death_shoa"])){
	$humo_option["death_shoa"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='death_shoa', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["admin_hebdate"])){
	$humo_option["admin_hebdate"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='admin_hebdate', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["admin_hebnight"])){
	$humo_option["admin_hebnight"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='admin_hebnight', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["admin_hebname"])){
	$humo_option["admin_hebname"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='admin_hebname', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["admin_brit"])){
	$humo_option["admin_brit"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='admin_brit', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["admin_barm"])){
	$humo_option["admin_barm"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='admin_barm', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["admin_online_search"])){
	$humo_option["admin_online_search"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='admin_online_search', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["debug_front_pages"])){
	$humo_option["debug_front_pages"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='debug_front_pages', setting_value='n'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["debug_admin_pages"])){
	$humo_option["debug_admin_pages"]='n';
	$sql="INSERT INTO humo_settings SET setting_variable='debug_admin_pages', setting_value='n'";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["hide_languages"])){
	$humo_option["hide_languages"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='hide_languages', setting_value=''";
	@$result=$dbh->query($sql);
}

if (!isset($humo_option["hide_themes"])){
	$humo_option["hide_themes"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='hide_themes', setting_value=''";
	@$result=$dbh->query($sql);
}

// *** New mail settings ***
if (!isset($humo_option["mail_auto"])){
	$humo_option["mail_auto"]='manual';
	$sql="INSERT INTO humo_settings SET setting_variable='mail_auto', setting_value='manual'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["email_user"])){
	$humo_option["email_user"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='email_user', setting_value=''";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["email_password"])){
	$humo_option["email_password"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='email_password', setting_value=''";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["smtp_server"])){
	$humo_option["smtp_server"]='';
	$sql="INSERT INTO humo_settings SET setting_variable='smtp_server', setting_value=''";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["smtp_port"])){
	$humo_option["smtp_port"]='587';
	$sql="INSERT INTO humo_settings SET setting_variable='smtp_port', setting_value='587'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["smtp_auth"])){
	$humo_option["smtp_auth"]='true';
	$sql="INSERT INTO humo_settings SET setting_variable='smtp_auth', setting_value='true'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["smtp_encryption"])){
	$humo_option["smtp_encryption"]='tls';
	$sql="INSERT INTO humo_settings SET setting_variable='smtp_encryption', setting_value='tls'";
	@$result=$dbh->query($sql);
}
if (!isset($humo_option["smtp_debug"])){
	$humo_option["smtp_debug"]='0';
	$sql="INSERT INTO humo_settings SET setting_variable='smtp_debug', setting_value='0'";
	@$result=$dbh->query($sql);
}
?>