<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Settings').'</h1>';

if(isset($_POST['timeline_language']) ) {  $time_lang = $_POST['timeline_language']; }
elseif(isset($_GET['timeline_language']) ) {  $time_lang = $_GET['timeline_language'];  }
else { $time_lang = $humo_option['default_language'];   }

if (isset($_POST['save_option'])){
	// *** Update settings ***
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["default_skin"])."' WHERE setting_variable='default_skin'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["default_language"])."' WHERE setting_variable='default_language'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["default_language_admin"])."' WHERE setting_variable='default_language_admin'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["text_footer"])."' WHERE setting_variable='text_footer'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["database_name"])."' WHERE setting_variable='database_name'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["homepage"])."' WHERE setting_variable='homepage'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["homepage_description"])."' WHERE setting_variable='homepage_description'");

	// *** Slideshow ***
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["slideshow_show"])."'
		WHERE setting_variable='slideshow_show'");
	$result = $dbh->query("UPDATE humo_settings
		SET setting_value='".safe_text_db($_POST["slideshow_slide_01"]).'|'.safe_text_db($_POST["slideshow_text_01"])."'
		WHERE setting_variable='slideshow_01'");
	$result = $dbh->query("UPDATE humo_settings
		SET setting_value='".safe_text_db($_POST["slideshow_slide_02"]).'|'.safe_text_db($_POST["slideshow_text_02"])."'
		WHERE setting_variable='slideshow_02'");
	$result = $dbh->query("UPDATE humo_settings
		SET setting_value='".safe_text_db($_POST["slideshow_slide_03"]).'|'.safe_text_db($_POST["slideshow_text_03"])."'
		WHERE setting_variable='slideshow_03'");
	$result = $dbh->query("UPDATE humo_settings
		SET setting_value='".safe_text_db($_POST["slideshow_slide_04"]).'|'.safe_text_db($_POST["slideshow_text_04"])."'
		WHERE setting_variable='slideshow_04'");

	// *** Today in history ***
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["today_in_history_show"])."'
		WHERE setting_variable='today_in_history_show'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["rss_link"])."' WHERE setting_variable='rss_link'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["searchengine"])."' WHERE setting_variable='searchengine'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["robots_option"])."' WHERE setting_variable='robots_option'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["searchengine_cms_only"])."' WHERE setting_variable='searchengine_cms_only'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["block_spam_question"])."' WHERE setting_variable='block_spam_question'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["block_spam_answer"])."' WHERE setting_variable='block_spam_answer'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["use_spam_question"])."' WHERE setting_variable='use_spam_question'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["use_newsletter_question"])."' WHERE setting_variable='use_newsletter_question'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["visitor_registration"])."' WHERE setting_variable='visitor_registration'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["general_email"])."' WHERE setting_variable='general_email'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["visitor_registration_group"])."' WHERE setting_variable='visitor_registration_group'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["registration_use_spam_question"])."' WHERE setting_variable='registration_use_spam_question'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["descendant_generations"])."' WHERE setting_variable='descendant_generations'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["show_persons"])."' WHERE setting_variable='show_persons'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["url_rewrite"])."' WHERE setting_variable='url_rewrite'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["timezone"])."' WHERE setting_variable='timezone'");

	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["watermark_text"])."' WHERE setting_variable='watermark_text'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["watermark_color_r"])."' WHERE setting_variable='watermark_color_r'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["watermark_color_g"])."' WHERE setting_variable='watermark_color_g'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["watermark_color_b"])."' WHERE setting_variable='watermark_color_b'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["min_search_chars"])."' WHERE setting_variable='min_search_chars'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["date_display"])."' WHERE setting_variable='date_display'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["name_order"])."' WHERE setting_variable='name_order'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["one_name_study"])."' WHERE setting_variable='one_name_study'");
	$result = $dbh->query("UPDATE humo_settings SET setting_value='".safe_text_db($_POST["one_name_thename"])."' WHERE setting_variable='one_name_thename'");
	if(strpos($humo_option['default_timeline'],$time_lang."!")===false) {  
		// no entry for this language yet - append it
		$result = $dbh->query("UPDATE humo_settings SET setting_value=CONCAT(setting_value,'".safe_text_db($_POST["default_timeline"])."') WHERE setting_variable='default_timeline'");
	}
	else {
		$time_arr = explode("@",substr($humo_option['default_timeline'],0,-1));
		foreach($time_arr AS $key => $value) { 
			if(strpos($value,$time_lang."!")!==false) {  
				$time_arr[$key] = substr(safe_text_db($_POST["default_timeline"]),0,-1);
			}
		}
		$time_str = implode("@",$time_arr)."@";  
		$result = $dbh->query("UPDATE humo_settings SET setting_value='".$time_str."' WHERE setting_variable='default_timeline'");
	}  
}

// *** Re-read variables after changing them ***
// *** Don't use include_once! Otherwise the old value will be shown ***
include(CMS_ROOTPATH."include/settings_global.php"); //variables

// *** Read languages in language array ***
$arr_count=0; $arr_count_admin=0;
$folder=opendir(CMS_ROOTPATH.'languages/');
while (false!==($file = readdir($folder))) {
	if (strlen($file)<6 AND $file!='.' AND $file!='..'){
		// *** Get language name ***
		include(CMS_ROOTPATH."languages/".$file."/language_data.php");
		$langs[$arr_count][0]=$language["name"];
		$langs[$arr_count][1]=$file;
		$arr_count++;
		if (file_exists(CMS_ROOTPATH.'languages/'.$file.'/'.$file.'.mo')){
		$langs_admin[$arr_count_admin][0]=$language["name"];
			$langs_admin[$arr_count_admin][1]=$file;
			$arr_count_admin++;
		}
	}
}
closedir($folder);

if(CMS_SPECIFIC == "Joomla") {
	echo '<form method="post" action="index.php?option=com_humo-gen&amp;task=admin&amp;page=settings">';
}
else {
	echo '<form method="post" action="index.php">';
}
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '<table class="humo standard" border="1">';

echo '<tr class="table_header"><th colspan="2">'.__('General settings').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

echo '<tr><td>'.__('Default skin').'</td><td><select size="1" name="default_skin">';
	echo '<option value="">Standard</option>';

	$folder=opendir(CMS_ROOTPATH.'styles/');
	while (false!==($file = readdir($folder))) {
		if (substr($file,-4,4)=='.css') {
			$theme_folder[]=$file;
		}
	}
	closedir($folder);

	for ($i=0; $i<count($theme_folder); $i++){
		$theme=$theme_folder[$i];
		$theme=str_replace(".css","", $theme);
		$select=''; if ($humo_option['default_skin']==$theme){ $select=' SELECTED'; }
		echo '<option value="'.$theme.'"'.$select.'>'.$theme.'</option>';
	}
echo "</select>";
echo '</td></tr>';

echo '<tr><td>'.__('Standard language HuMo-gen').'</td><td><select size="1" name="default_language">';
	if($langs) {
		for($i=0; $i<count($langs); $i++) {
			$select=''; if ($humo_option['default_language']==$langs[$i][1]){ $select=' SELECTED'; }
			echo '<option value="'.$langs[$i][1].'"'.$select.'>'.$langs[$i][0].'</option>';
		}
	}
echo "</select>";
echo '</td></tr>';

echo '<tr><td>'.__('Standard language admin menu').'</td><td><select size="1" name="default_language_admin">';
	if($langs_admin) {
		for($i=0; $i<count($langs_admin); $i++) {
			$select=''; if ($humo_option['default_language_admin']==$langs_admin[$i][1]){ $select=' SELECTED'; }
			echo '<option value="'.$langs_admin[$i][1].'"'.$select.'>'.$langs_admin[$i][0].'</option>';
		}
	}
echo "</select>";
echo '</td></tr>';

echo '<tr><td>'.__('Text in footer for all pages').'</td><td>';
	if(CMS_SPECIFIC == "Joomla") {  $cols="48"; } else { $cols="80"; }   // in joomla make sure it won't run off the screen
	echo "<textarea cols=".$cols." rows=1 name=\"text_footer\" style='height: 20px;'>".htmlentities($humo_option["text_footer"],ENT_NOQUOTES)."</textarea><br>";
	echo __('Can be used for statistics, counter, etc. It\'s possible to use HTML codes!');
echo '</td></tr>';

echo '<tr class="table_header"><th colspan="2">'.__('Search engine settings').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

echo '<tr class="humo_color"><td valign="top">url_rewrite<br>'.__('Improve indexing of search engines (like Google)').'</td><td><select size="1" name="url_rewrite">';
$selected=''; if ($humo_option["url_rewrite"]!='j') $selected=' SELECTED';
echo '<option value="j">'.__('Yes').'</option>';
echo '<option value="n"'.$selected.'>'.__('No').'</option>';

echo '</select> <b>'.__('ATTENTION: the Apache module "mod_rewrite" has to be installed!').'</b><br>';
echo 'URL&nbsp;&nbsp;: http://www.website.nl/humo-php/family.php?id=F12<br>';
echo __('becomes:').' http://www.website.nl/humo-php/family/F12/<br>';
echo '</td></tr>';

echo '<tr class="humo_color"><td>'.__('Stop search engines').'</td><td><select size="1" name="searchengine">';
$selected=''; if ($humo_option["searchengine"]!='j') $selected=' SELECTED';
echo '<option value="j">'.__('Yes').'</option>';
echo '<option value="n"'.$selected.'>'.__('No').'</option>';
echo "</select><br>";
if(CMS_SPECIFIC == "Joomla") {  $cols="48"; } else { $cols="80"; }   // in joomla make sure it won't run off the screen
echo "<textarea cols=".$cols." rows=1 name=\"robots_option\" style='height: 20px;'>".htmlentities($humo_option["robots_option"],ENT_NOQUOTES)."</textarea></td></tr>";

echo '<tr class="humo_color"><td>'.__('Search engines:<br>Hide family tree (no indexing)<br>Show frontpage and CMS pages').'</td><td><select size="1" name="searchengine_cms_only">';
$selected=''; if ($humo_option["searchengine_cms_only"]!='y') $selected=' SELECTED';
echo '<option value="y">'.__('Yes').'</option>';
echo '<option value="n"'.$selected.'>'.__('No').'</option>';
echo "</select><br></td></tr>";

echo '<tr class="table_header"><th colspan="2">'.__('Contact & registration form settings').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

echo '<tr><td>'.__('Block spam question').'<br>'.__('Block spam answer').'</td><td>';
echo '<input type="text" name="block_spam_question" value="'.htmlentities($humo_option["block_spam_question"],ENT_NOQUOTES).'" size="60"><br>';
echo '<input type="text" name="block_spam_answer" value="'.htmlentities($humo_option["block_spam_answer"],ENT_NOQUOTES).'" size="60">';
echo '</td></tr>';

echo '<tr><td>'.__('Mail form: use spam question').'</td><td>';
echo '<select size="1" name="use_spam_question">';
$selected=''; if ($humo_option["use_spam_question"]!='y') $selected=' SELECTED';
echo '<option value="y">'.__('Yes').'</option>';
echo '<option value="n"'.$selected.'>'.__('No').'</option>';
echo "</select>";
echo '</td></tr>';

echo '<tr><td>'.__('Mail form: use newsletter question').'</td><td>';
echo '<a name="timeline_anchor">'; // this belongs to the timeline settings - placed here it makes the page reload with timeline line in middle of page
echo '<select size="1" name="use_newsletter_question">';
$selected=''; if ($humo_option["use_newsletter_question"]!='y') $selected=' SELECTED';
echo '<option value="y">'.__('Yes').'</option>';
echo '<option value="n"'.$selected.'>'.__('No').'</option>';
echo "</select> ";
echo __('Adds the question: "Receive newsletter: yes/ no" to the mailform.');
echo '</td></tr>';

echo '<tr class="humo_color"><td>'.__('Visitors can register').'</td><td><select size="1" name="visitor_registration">';
$selected=''; if ($humo_option["visitor_registration"]!='y') $selected=' SELECTED';
echo '<option value="y">'.__('Yes').'</option>';
echo '<option value="n"'.$selected.'>'.__('No').'</option>';
echo '</select> ';
echo __('Default user-group for new users:').' ';
echo '<select size="1" name="visitor_registration_group">';
	$groupsql="SELECT * FROM humo_groups";
	$groupresult=$dbh->query($groupsql);
	while ($groupDb=$groupresult->fetch(PDO::FETCH_OBJ)){
		$selected='';
		if ($humo_option["visitor_registration_group"]==$groupDb->group_id){ $selected='  SELECTED'; }
		echo '<option value="'.$groupDb->group_id.'"'.$selected.'>'.$groupDb->group_name.'</option>';
	}
echo '</select> ';
//echo __('Add your e-mail address by family tree data!');
echo '</td></tr>';
echo '<tr class="humo_color"><td>'.__('Registration form: e-mail address').'</td>';
echo '<td><input type="text" name="general_email" value="'.$humo_option["general_email"].'" size="40"> '.__('Send registration form to this e-mail address.').'</td></tr>';

echo '<tr class="humo_color"><td>'.__('Visitor registration: use spam question').'</td><td>';
echo '<select size="1" name="registration_use_spam_question">';
$selected=''; if ($humo_option["registration_use_spam_question"]!='y') $selected=' SELECTED';
echo '<option value="y">'.__('Yes').'</option>';
echo '<option value="n"'.$selected.'>'.__('No').'</option>';
echo "</select>";
echo '</td></tr>';

echo '<tr class="humo_color"><td>'.__('SMTP mails').'</td><td>';
echo '<b>'.__('Sometimes it\'s necessary to use SMTP to send e-mails. These settings can be changed in file: include/mail.php').'</b>';
echo '</td></tr>';

echo '<tr class="table_header"><th colspan="2">'.__('International settings').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

echo '<tr><td valign="top">'.__('Timezone').'</td><td><select size="1" name="timezone">';

$selected=''; if ($humo_option["timezone"]=='Kwajalein'){ $selected=' SELECTED'; }
echo '<option value="Kwajalein"'.$selected.'>-12:00 (Kwajalein)</option>';
$selected=''; if ($humo_option["timezone"]=='Pacific/Midway'){ $selected=' SELECTED'; }
echo '<option value="Pacific/Midway"'.$selected.'>-11:00 (Pacific/Midway)</option>';
$selected=''; if ($humo_option["timezone"]=='Pacific/Honolulu'){ $selected=' SELECTED'; }
echo '<option value="Pacific/Honolulu"'.$selected.'>-10:00 (Pacific/Honolulu)</option>';
$selected=''; if ($humo_option["timezone"]=='America/Anchorage'){ $selected=' SELECTED'; }
echo '<option value="America/Anchorage"'.$selected.'>-09:00 (America/Anchorage)</option>';
$selected=''; if ($humo_option["timezone"]=='America/Los_Angeles'){ $selected=' SELECTED'; }
echo '<option value="America/Los_Angeles"'.$selected.'>-08:00 (America/Los_Angeles)</option>';
$selected=''; if ($humo_option["timezone"]=='America/Denver'){ $selected=' SELECTED'; }
echo '<option value="America/Denver"'.$selected.'>-07:00 (America/Denver)</option>';
$selected=''; if ($humo_option["timezone"]=='America/Tegucigalpa'){ $selected=' SELECTED'; }
echo '<option value="America/Tegucigalpa"'.$selected.'>-06:00 (America/Tegucigalpa)</option>';
$selected=''; if ($humo_option["timezone"]=='America/New_York'){ $selected=' SELECTED'; }
echo '<option value="America/New_York"'.$selected.'>-05:00 (America/New_York)</option>';
$selected=''; if ($humo_option["timezone"]=='America/Caracas'){ $selected=' SELECTED'; }
echo '<option value="America/Caracas"'.$selected.'>-04:30 (America/Caracas)</option>';
$selected=''; if ($humo_option["timezone"]=='America/Halifax'){ $selected=' SELECTED'; }
echo '<option value="America/Halifax"'.$selected.'>-04:00 (America/Halifax)</option>';
$selected=''; if ($humo_option["timezone"]=='America/St_Johns'){ $selected=' SELECTED'; }
echo '<option value="America/St_Johns"'.$selected.'>-03:30 (America/St_Johns)</option>';
$selected=''; if ($humo_option["timezone"]=='America/Argentina/Buenos_Aires'){ $selected=' SELECTED'; }
echo '<option value="America/Argentina/Buenos_Aires"'.$selected.'>-03:00 (America/Argentina/Buenos_Aires)</option>';
$selected=''; if ($humo_option["timezone"]=='America/Sao_Paulo'){ $selected=' SELECTED'; }
echo '<option value="America/Sao_Paulo"'.$selected.'>-03:00 (America/Argentina/Buenos_Aires)</option>';
$selected=''; if ($humo_option["timezone"]=='Atlantic/South_Georgia'){ $selected=' SELECTED'; }
echo '<option value="Atlantic/South_Georgia"'.$selected.'>-02:00 (Atlantic/South_Georgia)</option>';
$selected=''; if ($humo_option["timezone"]=='Atlantic/Azores'){ $selected=' SELECTED'; }
echo '<option value="Atlantic/Azores"'.$selected.'>-01:00 (Atlantic/Azores)</option>';
$selected=''; if ($humo_option["timezone"]=='Europe/Dublin'){ $selected=' SELECTED'; }
echo '<option value="Europe/Dublin"'.$selected.'>00:00 (Europe/Dublin)</option>';
$selected=''; if ($humo_option["timezone"]=='Europe/Amsterdam'){ $selected=' SELECTED'; }
echo '<option value="Europe/Amsterdam"'.$selected.'>01:00 (Europe/Amsterdam)</option>';
$selected=''; if ($humo_option["timezone"]=='Europe/Minsk'){ $selected=' SELECTED'; }
echo '<option value="Europe/Minsk"'.$selected.'>02:00 (Europe/Minsk)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Kuwait'){ $selected=' SELECTED'; }
echo '<option value="Asia/Kuwait"'.$selected.'>03:00 (Asia/Kuwait)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Tehran'){ $selected=' SELECTED'; }
echo '<option value="Asia/Tehran"'.$selected.'>03:30 (Asia/Tehran)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Muscat'){ $selected=' SELECTED'; }
echo '<option value="Asia/Muscat"'.$selected.'>04:00 (Asia/Muscat)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Yekaterinburg'){ $selected=' SELECTED'; }
echo '<option value="Asia/Yekaterinburg"'.$selected.'>05:00 (Asia/Yekaterinburg)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Kolkata'){ $selected=' SELECTED'; }
echo '<option value="Asia/Kolkata"'.$selected.'>05:30 (Asia/Kolkata)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Katmandu'){ $selected=' SELECTED'; }
echo '<option value="Asia/Katmandu"'.$selected.'>05:45 (Asia/Katmandu)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Dhaka'){ $selected=' SELECTED'; }
echo '<option value="Asia/Dhaka"'.$selected.'>06:00 (Asia/Dhaka)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Rangoon'){ $selected=' SELECTED'; }
echo '<option value="Asia/Rangoon"'.$selected.'>06:30 (Asia/Rangoon)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Krasnoyarsk'){ $selected=' SELECTED'; }
echo '<option value="Asia/Krasnoyarsk"'.$selected.'>07:00 (Asia/Krasnoyarsk)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Brunei'){ $selected=' SELECTED'; }
echo '<option value="Asia/Brunei"'.$selected.'>08:00 (Asia/Brunei)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Seoul'){ $selected=' SELECTED'; }
echo '<option value="Asia/Seoul"'.$selected.'>09:00 (Asia/Seoul)</option>';
$selected=''; if ($humo_option["timezone"]=='Australia/Darwin'){ $selected=' SELECTED'; }
echo '<option value="Australia/Darwin"'.$selected.'>09:30 (Australia/Darwin)</option>';
$selected=''; if ($humo_option["timezone"]=='Australia/Canberra'){ $selected=' SELECTED'; }
echo '<option value="Australia/Canberra"'.$selected.'>10:00 (Australia/Canberra)</option>';
$selected=''; if ($humo_option["timezone"]=='Asia/Magadan'){ $selected=' SELECTED'; }
echo '<option value="Asia/Magadan"'.$selected.'>11:00 (Asia/Magadan)</option>';
$selected=''; if ($humo_option["timezone"]=='Pacific/Fiji'){ $selected=' SELECTED'; }
echo '<option value="Pacific/Fiji"'.$selected.'>12:00 (Pacific/Fiji)</option>';
$selected=''; if ($humo_option["timezone"]=='Pacific/Tongatapu'){ $selected=' SELECTED'; }
echo '<option value="Pacific/Tongatapu"'.$selected.'>13:00 (Pacific/Tongatapu)</option>';

echo '</select>';
echo '</td></tr>';

echo '<tr><td style="white-space:nowrap;">'.__('Minimum characters in search box').'</td>';
echo '<td><input type="text" name="min_search_chars" value="'.$humo_option["min_search_chars"].'" size="4"> '.__('Minimum characters in search boxes (standard value=3. For Chinese set to 1).').'</td>';
echo "</tr>";

echo '<tr><td style="white-space:nowrap;">'.__('Date display').'</td>';  
echo '<td><select size="1" name="date_display">';
$selected=''; if ($humo_option["date_display"]== 'eu') $selected=' SELECTED';
echo '<option value="eu"'.$selected.'>'.__('Europe/Global - 5 Jan 1787').'</option>';
$selected=''; if ($humo_option["date_display"]== 'us') $selected=' SELECTED';
echo '<option value="us"'.$selected.'>'.__('USA - Jan 5, 1787').'</option>';
$selected=''; if ($humo_option["date_display"]== 'ch') $selected=' SELECTED';
echo '<option value="ch"'.$selected.'>'.__('China - 1787-01-05').'</option>';
$selected=''; if ($humo_option["date_display"]== 'is') $selected=' SELECTED';
echo '<option value="is"'.$selected.'>'.__('Israel - 5 Jan 1787 (15 Tevet 5547)').'</option>';
echo "</select>"; 
echo "</td></tr>";

echo '<tr><td style="white-space:nowrap;">'.__('Order of names in reports').'</td>';  
echo '<td><select size="1" name="name_order">';
$selected=''; if ($humo_option["name_order"]== 'western') $selected=' SELECTED';
echo '<option value="western"'.$selected.'>'.__('Western').'</option>';
$selected=''; if ($humo_option["name_order"]== 'chinese') $selected=' SELECTED';
echo '<option value="chinese"'.$selected.'>'.__('Chinese')."/ ".__('Hungarian').'</option>';
echo "</select>";
echo "&nbsp;".__('Western - reports: John Smith, lists: Smith, John. Chinese 中文 - reports and lists: 刘 理想').". ".__('Hungarian - reports and lists: Smith John');
echo "</td></tr>";

// timeline default
echo '<tr><td>'.__('Default timeline file (per language)').'</td><td>';
$folder=@opendir(CMS_ROOTPATH.'languages/'.$time_lang.'/timelines/');
if($folder !== false) {  // no use showing the option if we can't access the timeline folder
	while (false!==($file = readdir($folder))) {
		if (substr($file,-4,4)=='.txt') {
			$timeline_files[]=$file;
		}
	}
	echo '<select size="1" name="default_timeline">';
	for ($i=0; $i<count($timeline_files); $i++){
		$timeline=$timeline_files[$i];
		$timeline=str_replace(".txt","", $timeline);
		$select=""; if(strpos($humo_option['default_timeline'],$time_lang."!".$timeline) !== false) { $select=' SELECTED'; }
		echo '<option value="'.$time_lang.'!'.$timeline.'@"'.$select.'>'.$timeline.'</option>';
	}
	echo "</select>";
}
@closedir($folder);
echo "&nbsp;&nbsp;";

if($langs) {
	echo '<select onChange="window.location =\'index.php?page=settings&timeline_language=\' + this.value + \'#timeline_anchor\'; "  size="1" name="timeline_language">';
	for($i=0; $i<count($langs); $i++) { 
		if(is_dir(CMS_ROOTPATH.'languages/'.$langs[$i][1].'/timelines/')) {
			$select=''; if ($time_lang==$langs[$i][1]){ $select=' SELECTED'; }
			echo '<option value="'.$langs[$i][1].'"'.$select.'>'.$langs[$i][0].'</option>';
		}
	}
	echo "</select>";
}
echo '</td></tr>';

echo '<tr class="table_header"><th colspan="2">'.__('Settings Main Menu').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

echo '<tr><td>'.__('Website name').'</td>';
echo '<td><input type="text" name="database_name" value="'.$humo_option["database_name"].'" size="40"></td></tr>';

echo '<tr><td>'.__('Link homepage').'<br>'.__('Link description').'</td>';
echo '<td><input type="text" name="homepage" value="'.$humo_option["homepage"].'" size="40"> <span style="white-space:nowrap;">'.__('(link to this site including http://)').'</span><br>';
echo '<input type="text" name="homepage_description" value="'.$humo_option["homepage_description"].'" size="40"></td>';
echo "</tr>";

// *** Birthday RSS ***
echo '<tr><td>'.__('Link for birthdays RSS').'</td><td>';

echo '<input type="text" name="rss_link" value="'.$humo_option["rss_link"].'" size="40"> <span style="white-space:nowrap;">'.__('(link to this site including http://)').'</span><br>';
echo '<i>'.__('This option can be turned on or off in the user groups.').'</i>';

echo '<tr class="table_header"><th colspan="2">'.__('Slideshow on the homepage').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';
echo '<tr><td><br></td><td>'.__('This option shows a slideshow at the homepage. Put the images in the media/slideshow/ folder at the website.<br>Example of image link:').' <b>media/slideshow/slide01.jpg</b><br>';
echo __('Images size should be about:').' <b>950 x 170 pixels.</b>';
echo '</td></tr>';
echo '<tr><td style="white-space:nowrap;">'.__('Show slideshow on the homepage').'?</td>';
echo '<td><select size="1" name="slideshow_show">';
	$selected=''; if ($humo_option["slideshow_show"]!='y') $selected=' SELECTED';
	echo '<option value="y">'.__('Yes').'</option>';
	echo '<option value="n"'.$selected.'>'.__('No').'</option>';
	echo '</select>';
echo '</td></tr>';
// *** Picture 1 ***
$slideshow_01=explode('|',$humo_option["slideshow_01"]);
echo '<tr><td>'.__('Link to image').' 1<br>'.__('Link description').' 1</td>';
echo '<td><input type="text" name="slideshow_slide_01" value="'.$slideshow_01[0].'" size="40"> media/slideshow/slide01.jpg<br>';
echo '<input type="text" name="slideshow_text_01" value="'.$slideshow_01[1].'" size="40"></td>';
echo "</tr>";
// *** Picture 2 ***
$slideshow_02=explode('|',$humo_option["slideshow_02"]);
echo '<tr><td>'.__('Link to image').' 2<br>'.__('Link description').' 2</td>';
echo '<td><input type="text" name="slideshow_slide_02" value="'.$slideshow_02[0].'" size="40"> media/slideshow/slide02.jpg<br>';
echo '<input type="text" name="slideshow_text_02" value="'.$slideshow_02[1].'" size="40"></td>';
echo "</tr>";
// *** Picture 3 ***
$slideshow_03=explode('|',$humo_option["slideshow_03"]);
echo '<tr><td>'.__('Link to image').' 3<br>'.__('Link description').' 3</td>';
echo '<td><input type="text" name="slideshow_slide_03" value="'.$slideshow_03[0].'" size="40"> media/slideshow/slide03.jpg<br>';
echo '<input type="text" name="slideshow_text_03" value="'.$slideshow_03[1].'" size="40"></td>';
echo "</tr>";
// *** Picture 4 ***
$slideshow_04=explode('|',$humo_option["slideshow_04"]);
echo '<tr><td>'.__('Link to image').' 4<br>'.__('Link description').' 4</td>';
echo '<td><input type="text" name="slideshow_slide_04" value="'.$slideshow_04[0].'" size="40"> media/slideshow/slide04.jpg<br>';
echo '<input type="text" name="slideshow_text_04" value="'.$slideshow_04[1].'" size="40"></td>';
echo "</tr>";

echo '<tr class="table_header"><th colspan="2">'.__('"Today in history" on the homepage').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';
echo '<tr><td style="white-space:nowrap;">'.__('Show "Today in history" list on the homepage').'?</td>';
echo '<td><select size="1" name="today_in_history_show">';
	$selected=''; if ($humo_option["today_in_history_show"]!='y') $selected=' SELECTED';
	echo '<option value="y">'.__('Yes').'</option>';
	echo '<option value="n"'.$selected.'>'.__('No').'</option>';
	echo '</select>';
echo '</td></tr>';

// *** FAMILY ***
echo '<tr class="table_header"><th colspan="2">'.__('Settings family page').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

echo '<tr><td style="white-space:nowrap;">'.__('Number of generations in descendant report').'</td>';
echo '<td><input type="text" name="descendant_generations" value="'.$humo_option["descendant_generations"].'" size="4"> '.__('Show number of generation in descendant report (standard value=4).').'</td>';
echo "</tr>";

echo '<tr><td style="white-space:nowrap;">'.__('Number of persons in search results').'</td>';
echo '<td><input type="text" name="show_persons" value="'.$humo_option["show_persons"].'" size="4"> '.__('Show number of persons in search results (standard value=30).').'</td>';
echo '</tr>';


// *** Watermark text and color in PDF file ***
echo '<tr class="table_header"><th colspan="2">'.__('Watermark text in PDF file').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';
echo '<tr><td style="white-space:nowrap;">'.__('Watermark text in PDF file').'</td>';
echo '<td><input type="text" name="watermark_text" value="'.$humo_option["watermark_text"].'" size="40"> '.__('Watermark text (clear to remove watermark)').'</td>';
echo "</tr>";
echo '<tr><td style="white-space:nowrap;">'.__('Watermark RGB text color').'</td>';
echo '<td>';
	echo 'R:<input type="text" name="watermark_color_r" value="'.$humo_option["watermark_color_r"].'" size="4">';
	echo ' G:<input type="text" name="watermark_color_g" value="'.$humo_option["watermark_color_g"].'" size="4">';
	echo ' B:<input type="text" name="watermark_color_b" value="'.$humo_option["watermark_color_b"].'" size="4"> ';
	echo __('Default values: R = 224, G = 224, B = 224.');
echo '</td>';
echo "</tr>";

// *** Display for One Name Study web sites ***
echo '<tr class="table_header"><th colspan="2">'.__('Display for One Name Study web sites').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';
echo '<tr><td style="white-space:nowrap;">'.__('One Name Study display').'?</td>';
echo '<td><select size="1" name="one_name_study">';
$selected=''; if ($humo_option["one_name_study"]!='y') $selected=' SELECTED';
echo '<option value="y">'.__('Yes').'</option>';
echo '<option value="n"'.$selected.'>'.__('No').'</option>';
echo '</select>';
echo '</td></tr>';
echo '<tr><td style="white-space:nowrap;">'.__('Enter the One Name of this site').'</td>';
echo '<td>';
echo '<input type="text" name="one_name_thename" value="'.$humo_option["one_name_thename"].'" size="40">';
echo '</td></tr>';

echo '<tr class="table_header"><th colspan="2">'.__('Save settings').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

echo '</table>';
echo '</form>';


echo '<h1 align=center>'.__('Special settings').'</h1>';

echo '<table class="humo standard" border="1">';
	echo '<tr class="table_header"><th colspan="2">'.__('Special settings').'</th></tr>';

	echo '<tr><td>'.__('&#134 => &infin;').'</td><td>';
		echo '<b>'.__('Change all &#134; characters into &infin; characters in all language files.').'</b> <br>';
		echo __('Some remarks about this option:<br>
If you want the &infin; characters, you have to click the link below everytime HuMo-gen is updated.<br>
It\'s not posssible to reverse this action, you have to re-install the language files!');
		echo '<br><a href="../languages/change_all.php">'.__('Change all &#134; characters into &infin; characters in all language files.').'</a>';
	echo '</td></tr>';

	echo '<tr><td>'.__('Sitemap').'</td><td>';
		echo '<b>'.__('Sitemap').'</b> <br>';
		echo __('A sitemap can be used for quick indexing of the HuMo-gen family screens by search engines. Add the sitemap link to a search engine (like Google), or add the link in a robots.txt file (in the root folder of your website). Example of robots.txt file, sitemap line:<br>
Sitemap: http://www.yourwebsite.com/humo-gen/sitemap.php');
		echo '<br><a href="../sitemap.php">'.__('Sitemap').'</a>';
	echo '</td></tr>';

echo '</table>';
?>