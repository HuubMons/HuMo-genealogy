<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Settings').'</h1>';

print '<p align=center>';

	if (isset($_POST['save_option'])){
		// *** Update settings ***
		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["default_skin"])."' WHERE setting_variable='default_skin'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["default_language"])."' WHERE setting_variable='default_language'") or die(mysql_error());
		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["default_language_admin"])."' WHERE setting_variable='default_language_admin'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["database_name"])."' WHERE setting_variable='database_name'") or die(mysql_error());
		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["homepage"])."' WHERE setting_variable='homepage'") or die(mysql_error());
		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["homepage_description"])."' WHERE setting_variable='homepage_description'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["rss_link"])."' WHERE setting_variable='rss_link'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["searchengine"])."' WHERE setting_variable='searchengine'") or die(mysql_error());
		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["robots_option"])."' WHERE setting_variable='robots_option'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["searchengine_cms_only"])."' WHERE setting_variable='searchengine_cms_only'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["block_spam_question"])."' WHERE setting_variable='block_spam_question'") or die(mysql_error());
		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["block_spam_answer"])."' WHERE setting_variable='block_spam_answer'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["use_spam_question"])."' WHERE setting_variable='use_spam_question'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["visitor_registration"])."' WHERE setting_variable='visitor_registration'") or die(mysql_error());
		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["visitor_registration_group"])."' WHERE setting_variable='visitor_registration_group'") or die(mysql_error());
		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["registration_use_spam_question"])."' WHERE setting_variable='registration_use_spam_question'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["descendant_generations"])."' WHERE setting_variable='descendant_generations'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["show_persons"])."' WHERE setting_variable='show_persons'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["url_rewrite"])."' WHERE setting_variable='url_rewrite'") or die(mysql_error());

		$result = mysql_query("UPDATE humo_settings SET setting_value='".safe_text($_POST["timezone"])."' WHERE setting_variable='timezone'") or die(mysql_error());
	}

	// *** Re-read variables after changing them ***
	// *** Don't use include_once! Otherwise the old value will be shown ***
	include(CMS_ROOTPATH."include/settings_global.php"); //variablen

	// *** Read languages in language array ***
	$arr_count=0; $arr_count_admin=0;
	$folder=opendir(CMS_ROOTPATH.'languages/');
	while (false!==($file = readdir($folder))) {
		if (strlen($file)<5 AND $file!='.' AND $file!='..'){
			// *** Get language name ***
			include(CMS_ROOTPATH."languages/".$file."/language_data.php");
			$langs[$arr_count][0]=$language["name"];
			$langs[$arr_count][1]=$file;
			$arr_count++;
			//if (file_exists(CMS_ROOTPATH.'languages/'.$file.'/language_admin.php')){
			if (file_exists(CMS_ROOTPATH.'languages/'.$file.'/'.$file.'.mo')){
			$langs_admin[$arr_count_admin][0]=$language["name"];
				$langs_admin[$arr_count_admin][1]=$file;
				$arr_count_admin++;
			}
		}
	}
	closedir($folder);

if(CMS_SPECIFIC == "Joomla") {
	print '<form method="post" action="index.php?option=com_humo-gen&amp;task=admin&amp;page=settings">';
}
else {
	print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
}
	echo '<input type="hidden" name="page" value="'.$page.'">';
	echo '<table class="humo standard" border="1">';
	echo '<tr class="table_header"><th>'.__('Option').'</th><th>'.__('Setting').'</th></tr>';

	print '<tr bgcolor="green"><th><font color="white">'.__('General settings').'</font></th><th><input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

	echo '<tr><td>'.__('Default skin').'</td><td><select size="1" name="default_skin">';
		print '<option value="">Standard</option>';

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
			print '<option value="'.$theme.'"'.$select.'>'.$theme.'</option>';
		}
	echo "</select>";
	echo '</td></tr>';

	echo '<tr><td>'.__('Standard language HuMo-gen').'</td><td><select size="1" name="default_language">';

		if($langs) {
			for($i=0; $i<count($langs); $i++) {
				$select=''; if ($humo_option['default_language']==$langs[$i][1]){ $select=' SELECTED'; }
				print '<option value="'.$langs[$i][1].'"'.$select.'>'.$langs[$i][0].'</option>';
			}
		}

	echo "</select>";
	echo '</td></tr>';

	echo '<tr><td>'.__('Standard language admin menu').'</td><td><select size="1" name="default_language_admin">';

		if($langs_admin) {
			for($i=0; $i<count($langs_admin); $i++) {
				$select=''; if ($humo_option['default_language_admin']==$langs_admin[$i][1]){ $select=' SELECTED'; }
				print '<option value="'.$langs_admin[$i][1].'"'.$select.'>'.$langs_admin[$i][0].'</option>';
			}
		}

	echo "</select>";
	echo '</td></tr>';

	echo '<tr><td valign="top">url_rewrite<br>'.__('Improve indexing of search engines (like Google)').'</td><td><select size="1" name="url_rewrite">';
	if ($humo_option["url_rewrite"]=='j'){
		print '<option value="j" SELECTED>'.__('Yes').'</option>';
		print '<option value="n">'.__('No').'</option>';
	}
	else{
		print '<option value="n" SELECTED>'.__('No').'</option>';
		print '<option value="j">'.__('Yes').'</option>';
	}
	echo '</select> <b>'.__('ATTENTION: the Apache module "mod_rewrite" has to be installed!').'</b><br>';
	echo 'URL&nbsp;&nbsp;: http://www.website.nl/humo-php/family.php?id=F12<br>';
	echo __('becomes:').' http://www.website.nl/humo-php/family/F12/<br>';
	echo '</td></tr>';

	echo '<tr><td>'.__('Stop search engines').'</td><td><select size="1" name="searchengine">';
	if ($humo_option["searchengine"]=='j'){
		print '<option value="j" SELECTED>'.__('Yes').'</option>';
		print '<option value="n">'.__('No').'</option>';
	}
	else{
		print '<option value="n" SELECTED>'.__('No').'</option>';
		print '<option value="j">'.__('Yes').'</option>';
	}
	echo "</select><br>";
	if(CMS_SPECIFIC == "Joomla") {  $cols="48"; } else { $cols="80"; }   // in joomla make sure it won't run off the screen
	print "<textarea cols=".$cols." rows=1 name=\"robots_option\" style='height: 20px;'>".htmlentities($humo_option["robots_option"],ENT_NOQUOTES)."</TEXTAREA></td></tr>";

	echo '<tr><td>'.__('Search engines:<br>Hide family tree (no indexing)<br>Show frontpage and CMS pages').'</td><td><select size="1" name="searchengine_cms_only">';
	if ($humo_option["searchengine_cms_only"]=='y'){
		print '<option value="y" SELECTED>'.__('Yes').'</option>';
		print '<option value="n">'.__('No').'</option>';
	}
	else{
		print '<option value="n" SELECTED>'.__('No').'</option>';
		print '<option value="y">'.__('Yes').'</option>';
	}
	echo "</select><br></td></tr>";

	echo '<tr><td>'.__('Block spam question').'<br>'.__('Block spam answer').'</td><td>';
	echo "<textarea cols=".$cols." rows=1 name=\"block_spam_question\" style='height: 20px;'>".htmlentities($humo_option["block_spam_question"],ENT_NOQUOTES).'</textarea>';
	echo "<textarea cols=".$cols." rows=1 name=\"block_spam_answer\" style='height: 20px;'>".htmlentities($humo_option["block_spam_answer"],ENT_NOQUOTES).'</textarea>';
	echo '</td></tr>';

	echo '<tr><td>'.__('Mail form: use spam question').'</td><td>';
	echo '<select size="1" name="use_spam_question">';
	if ($humo_option["use_spam_question"]=='y'){
		print '<option value="y" SELECTED>'.__('Yes').'</option>';
		print '<option value="n">'.__('No').'</option>';
	}
	else{
		print '<option value="n" SELECTED>'.__('No').'</option>';
		print '<option value="y">'.__('Yes').'</option>';
	}
	echo "</select>";
	echo '</td></tr>';

	echo '<tr><td>'.__('Visitors can register').'</td><td><select size="1" name="visitor_registration">';
	if ($humo_option["visitor_registration"]=='y'){
		print '<option value="y" SELECTED>'.__('Yes').'</option>';
		print '<option value="n">'.__('No').'</option>';
	}
	else{
		print '<option value="n" SELECTED>'.__('No').'</option>';
		print '<option value="y">'.__('Yes').'</option>';
	}
	echo '</select> ';
	echo __('Default user-group for new users:').' ';
	echo '<select size="1" name="visitor_registration_group">';
		$groupsql="SELECT * FROM humo_groups";
		$groupresult=mysql_query($groupsql,$db);
		while ($groupDb=mysql_fetch_object($groupresult)){
			$selected='';
			if ($humo_option["visitor_registration_group"]==$groupDb->group_id){ $selected='  SELECTED'; }
			print '<option value="'.$groupDb->group_id.'"'.$selected.'>'.$groupDb->group_name.'</option>';
		}
	echo '</select> ';
	echo __('Add your e-mail address by family tree data!');
	echo '</td></tr>';

	echo '<tr><td>'.__('Visitor registration: use spam question').'</td><td>';
	echo '<select size="1" name="registration_use_spam_question">';
	if ($humo_option["registration_use_spam_question"]=='y'){
		print '<option value="y" SELECTED>'.__('Yes').'</option>';
		print '<option value="n">'.__('No').'</option>';
	}
	else{
		print '<option value="n" SELECTED>'.__('No').'</option>';
		print '<option value="y">'.__('Yes').'</option>';
	}
	echo "</select>";
	echo '</td></tr>';

	echo '<tr><td valign="top">'.__('Timezone').'</td><td><select size="1" name="timezone">';

	$selected=''; if ($humo_option["timezone"]=='Kwajalein'){ $selected=' SELECTED'; }
	print '<option value="Kwajalein"'.$selected.'>-12:00 (Kwajalein)</option>';
	$selected=''; if ($humo_option["timezone"]=='Pacific/Midway'){ $selected=' SELECTED'; }
	print '<option value="Pacific/Midway"'.$selected.'>-11:00 (Pacific/Midway)</option>';
	$selected=''; if ($humo_option["timezone"]=='Pacific/Honolulu'){ $selected=' SELECTED'; }
	print '<option value="Pacific/Honolulu"'.$selected.'>-10:00 (Pacific/Honolulu)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/Anchorage'){ $selected=' SELECTED'; }
	print '<option value="America/Anchorage"'.$selected.'>-09:00 (America/Anchorage)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/Los_Angeles'){ $selected=' SELECTED'; }
	print '<option value="America/Los_Angeles"'.$selected.'>-08:00 (America/Los_Angeles)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/Denver'){ $selected=' SELECTED'; }
	print '<option value="America/Denver"'.$selected.'>-07:00 (America/Denver)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/Tegucigalpa'){ $selected=' SELECTED'; }
	print '<option value="America/Tegucigalpa"'.$selected.'>-06:00 (America/Tegucigalpa)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/New_York'){ $selected=' SELECTED'; }
	print '<option value="America/New_York"'.$selected.'>-05:00 (America/New_York)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/Caracas'){ $selected=' SELECTED'; }
	print '<option value="America/Caracas"'.$selected.'>-04:30 (America/Caracas)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/Halifax'){ $selected=' SELECTED'; }
	print '<option value="America/Halifax"'.$selected.'>-04:00 (America/Halifax)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/St_Johns'){ $selected=' SELECTED'; }
	print '<option value="America/St_Johns"'.$selected.'>-03:30 (America/St_Johns)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/Argentina/Buenos_Aires'){ $selected=' SELECTED'; }
	print '<option value="America/Argentina/Buenos_Aires"'.$selected.'>-03:00 (America/Argentina/Buenos_Aires)</option>';
	$selected=''; if ($humo_option["timezone"]=='America/Sao_Paulo'){ $selected=' SELECTED'; }
	print '<option value="America/Sao_Paulo"'.$selected.'>-03:00 (America/Argentina/Buenos_Aires)</option>';
	$selected=''; if ($humo_option["timezone"]=='Atlantic/South_Georgia'){ $selected=' SELECTED'; }
	print '<option value="Atlantic/South_Georgia"'.$selected.'>-02:00 (Atlantic/South_Georgia)</option>';
	$selected=''; if ($humo_option["timezone"]=='Atlantic/Azores'){ $selected=' SELECTED'; }
	print '<option value="Atlantic/Azores"'.$selected.'>-01:00 (Atlantic/Azores)</option>';
	$selected=''; if ($humo_option["timezone"]=='Europe/Dublin'){ $selected=' SELECTED'; }
	print '<option value="Europe/Dublin"'.$selected.'>00:00 (Europe/Dublin)</option>';
	$selected=''; if ($humo_option["timezone"]=='Europe/Amsterdam'){ $selected=' SELECTED'; }
	print '<option value="Europe/Amsterdam"'.$selected.'>01:00 (Europe/Amsterdam)</option>';
	$selected=''; if ($humo_option["timezone"]=='Europe/Minsk'){ $selected=' SELECTED'; }
	print '<option value="Europe/Minsk"'.$selected.'>02:00 (Europe/Minsk)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Kuwait'){ $selected=' SELECTED'; }
	print '<option value="Asia/Kuwait"'.$selected.'>03:00 (Asia/Kuwait)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Tehran'){ $selected=' SELECTED'; }
	print '<option value="Asia/Tehran"'.$selected.'>03:30 (Asia/Tehran)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Muscat'){ $selected=' SELECTED'; }
	print '<option value="Asia/Muscat"'.$selected.'>04:00 (Asia/Muscat)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Yekaterinburg'){ $selected=' SELECTED'; }
	print '<option value="Asia/Yekaterinburg"'.$selected.'>05:00 (Asia/Yekaterinburg)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Kolkata'){ $selected=' SELECTED'; }
	print '<option value="Asia/Kolkata"'.$selected.'>05:30 (Asia/Kolkata)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Katmandu'){ $selected=' SELECTED'; }
	print '<option value="Asia/Katmandu"'.$selected.'>05:45 (Asia/Katmandu)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Dhaka'){ $selected=' SELECTED'; }
	print '<option value="Asia/Dhaka"'.$selected.'>06:00 (Asia/Dhaka)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Rangoon'){ $selected=' SELECTED'; }
	print '<option value="Asia/Rangoon"'.$selected.'>06:30 (Asia/Rangoon)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Krasnoyarsk'){ $selected=' SELECTED'; }
	print '<option value="Asia/Krasnoyarsk"'.$selected.'>07:00 (Asia/Krasnoyarsk)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Brunei'){ $selected=' SELECTED'; }
	print '<option value="Asia/Brunei"'.$selected.'>08:00 (Asia/Brunei)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Seoul'){ $selected=' SELECTED'; }
	print '<option value="Asia/Seoul"'.$selected.'>09:00 (Asia/Seoul)</option>';
	$selected=''; if ($humo_option["timezone"]=='Australia/Darwin'){ $selected=' SELECTED'; }
	print '<option value="Australia/Darwin"'.$selected.'>09:30 (Australia/Darwin)</option>';
	$selected=''; if ($humo_option["timezone"]=='Australia/Canberra'){ $selected=' SELECTED'; }
	print '<option value="Australia/Canberra"'.$selected.'>10:00 (Australia/Canberra)</option>';
	$selected=''; if ($humo_option["timezone"]=='Asia/Magadan'){ $selected=' SELECTED'; }
	print '<option value="Asia/Magadan"'.$selected.'>11:00 (Asia/Magadan)</option>';
	$selected=''; if ($humo_option["timezone"]=='Pacific/Fiji'){ $selected=' SELECTED'; }
	print '<option value="Pacific/Fiji"'.$selected.'>12:00 (Pacific/Fiji)</option>';
	$selected=''; if ($humo_option["timezone"]=='Pacific/Tongatapu'){ $selected=' SELECTED'; }
	print '<option value="Pacific/Tongatapu"'.$selected.'>13:00 (Pacific/Tongatapu)</option>';

	echo '</select>';
	echo '</td></tr>';

	print '<tr bgcolor=green><th><font color=white>'.__('Settings Main Menu').'</font></th><th><input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

	print '<tr><td>'.__('Website name').'</td>';
	print '<td><input type="text" name="database_name" value="'.$humo_option["database_name"].'" size="40"></td></tr>';

	print '<tr><td>'.__('Link homepage').'<br>'.__('Link description').'</td>';
	print '<td><input type="text" name="homepage" value="'.$humo_option["homepage"].'" size="40"> <span style="white-space:nowrap;">'.__('(link to this site including http://)').'</span><br>';
	print '<input type="text" name="homepage_description" value="'.$humo_option["homepage_description"].'" size="40"></td>';
	print "</tr>";

	// *** Birthday RSS ***
	echo '<tr><td>'.__('Link for birthdays RSS').'</td><td>';

	print '<input type="text" name="rss_link" value="'.$humo_option["rss_link"].'" size="40"> <span style="white-space:nowrap;">'.__('(link to this site including http://)').'</span><br>';
	print '<i>'.__('This option can be turned on or off in the user groups.').'</i>';

	// *** FAMILY ***
	print '<tr bgcolor=green><th><font color=white>'.__('Settings family page').'</font></th><th><input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

	print '<tr><td style="white-space:nowrap;">'.__('Number of generations in descendant report').'</td>';
	print '<td><textarea cols=3 rows=1 name="descendant_generations" style="height: 20px;">'.$humo_option["descendant_generations"].'</TEXTAREA> '.__('Show number of generation in descendant report (standard value=4).').'</td>';
	print "</tr>";

	print '<tr><td style="white-space:nowrap;">'.__('Number of persons in search results').'</td>';
	print '<td><textarea cols=3 rows=1 name="show_persons" style="height: 20px;">'.$humo_option["show_persons"].'</TEXTAREA> '.__('Show number of persons in search results (standard value=30).').'</td>';
	print '</tr>';

	print '<tr bgcolor=green><th><font color=white>'.__('Save settings').'</font></th><th><input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

	echo '</table>';
	print '</form>';

?>