<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

echo '<h1 align=center>'.__('Settings').'</h1>';

if(isset($_POST['timeline_language']) ) { $time_lang = $_POST['timeline_language']; }
elseif(isset($_GET['timeline_language']) ) { $time_lang = $_GET['timeline_language']; }
else { $time_lang = $humo_option['default_language']; }

if (isset($_POST['save_option'])){
	// *** Update settings ***
	$result = $db_functions->update_settings('default_skin',$_POST["default_skin"]);

	$result = $db_functions->update_settings('default_language',$_POST["default_language"]);
	$result = $db_functions->update_settings('default_language_admin',$_POST["default_language_admin"]);

	$result = $db_functions->update_settings('text_footer',$_POST["text_footer"]);

	$result = $db_functions->update_settings('debug_front_pages',$_POST["debug_front_pages"]);
	$result = $db_functions->update_settings('debug_admin_pages',$_POST["debug_admin_pages"]);

	$result = $db_functions->update_settings('database_name',$_POST["database_name"]);
	$result = $db_functions->update_settings('homepage',$_POST["homepage"]);
	$result = $db_functions->update_settings('homepage_description',$_POST["homepage_description"]);

	$result = $db_functions->update_settings('rss_link',$_POST["rss_link"]);

	$result = $db_functions->update_settings('searchengine',$_POST["searchengine"]);
	$result = $db_functions->update_settings('robots_option',$_POST["robots_option"]);

	$result = $db_functions->update_settings('searchengine_cms_only',$_POST["searchengine_cms_only"]);

	$result = $db_functions->update_settings('block_spam_question',$_POST["block_spam_question"]);
	$result = $db_functions->update_settings('block_spam_answer',$_POST["block_spam_answer"]);

	$result = $db_functions->update_settings('use_spam_question',$_POST["use_spam_question"]);
	$result = $db_functions->update_settings('use_newsletter_question',$_POST["use_newsletter_question"]);

	$result = $db_functions->update_settings('visitor_registration',$_POST["visitor_registration"]);
	$result = $db_functions->update_settings('general_email',$_POST["general_email"]);
	$result = $db_functions->update_settings('visitor_registration_group',$_POST["visitor_registration_group"]);
	$result = $db_functions->update_settings('registration_use_spam_question',$_POST["registration_use_spam_question"]);
	$result = $db_functions->update_settings('password_retreival',$_POST["password_retreival"]);

	/*
	***************************
	Kai Mahnke 2020-04
	Save email configuration settings 
	****************************
	*/
	$result = $db_functions->update_settings('mail_auto',$_POST["mail_auto"]);
	$result = $db_functions->update_settings('email_user',$_POST["email_user"]);
	$result = $db_functions->update_settings('email_password',$_POST["email_password"]);
	$result = $db_functions->update_settings('smtp_server',$_POST["smtp_server"]);
	$result = $db_functions->update_settings('smtp_port',$_POST["smtp_port"]);
	$result = $db_functions->update_settings('smtp_auth',$_POST["smtp_auth"]);
	$result = $db_functions->update_settings('smtp_encryption',$_POST["smtp_encryption"]);
	$result = $db_functions->update_settings('smtp_debug',$_POST["smtp_debug"]);
	/*
	***************************
	End changes
	***************************
	*/

	$result = $db_functions->update_settings('descendant_generations',$_POST["descendant_generations"]);

	$result = $db_functions->update_settings('show_persons',$_POST["show_persons"]);

	$result = $db_functions->update_settings('url_rewrite',$_POST["url_rewrite"]);

	$result = $db_functions->update_settings('timezone',$_POST["timezone"]);

	$result = $db_functions->update_settings('watermark_text',$_POST["watermark_text"]);
	$result = $db_functions->update_settings('watermark_color_r',$_POST["watermark_color_r"]);
	$result = $db_functions->update_settings('watermark_color_g',$_POST["watermark_color_g"]);
	$result = $db_functions->update_settings('watermark_color_b',$_POST["watermark_color_b"]);
	$result = $db_functions->update_settings('min_search_chars',$_POST["min_search_chars"]);
	$result = $db_functions->update_settings('date_display',$_POST["date_display"]);
	$result = $db_functions->update_settings('name_order',$_POST["name_order"]);
	$result = $db_functions->update_settings('one_name_study',$_POST["one_name_study"]);
	$result = $db_functions->update_settings('one_name_thename',$_POST["one_name_thename"]);

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
		$result = $db_functions->update_settings('default_timeline',$time_str);
	}
}

// *** Homepage ***
if (isset($_POST['save_option2'])){
	// *** Slideshow ***
	$result = $db_functions->update_settings('slideshow_show',$_POST["slideshow_show"]);
	$result = $db_functions->update_settings('slideshow_01',$_POST["slideshow_slide_01"].'|'.$_POST["slideshow_text_01"]);
	$result = $db_functions->update_settings('slideshow_02',$_POST["slideshow_slide_02"].'|'.$_POST["slideshow_text_02"]);
	$result = $db_functions->update_settings('slideshow_03',$_POST["slideshow_slide_03"].'|'.$_POST["slideshow_text_03"]);
	$result = $db_functions->update_settings('slideshow_04',$_POST["slideshow_slide_04"].'|'.$_POST["slideshow_text_04"]);

	// *** Today in history ***
	//$result = $db_functions->update_settings('today_in_history_show',$_POST["today_in_history_show"]);
}

// *** Special settings ***
if (isset($_POST['save_option3'])){
	// Jewish settings

	$setting_value='n'; if(isset($_POST["david_stars"])) $setting_value='y';
	$result = $db_functions->update_settings('david_stars',$setting_value);

	$setting_value='n'; if(isset($_POST["death_shoa"])) $setting_value='y';
	$result = $db_functions->update_settings('death_shoa',$setting_value);

	$setting_value='n'; if(isset($_POST["admin_hebnight"])) $setting_value='y';
	$result = $db_functions->update_settings('admin_hebnight',$setting_value);

	$setting_value='n'; if(isset($_POST["admin_hebdate"])) $setting_value='y';
	$result = $db_functions->update_settings('admin_hebdate',$setting_value);

	$setting_value='n'; if(isset($_POST["admin_hebname"])) $setting_value='y';
	$result = $db_functions->update_settings('admin_hebname',$setting_value);

	$setting_value='n'; if(isset($_POST["admin_brit"])) $setting_value='y';
	$result = $db_functions->update_settings('admin_brit',$setting_value);

	$setting_value='n'; if(isset($_POST["admin_barm"])) $setting_value='y';
	$result = $db_functions->update_settings('admin_barm',$setting_value);

	if(isset($_POST["death_char"]) AND safe_text_db($_POST["death_char"]) == "y"  AND $humo_option['death_char'] == "n") {
		include(CMS_ROOTPATH."languages/change_all.php");  // change cross to infinity
		$result = $db_functions->update_settings('death_char','y');
	}
	elseif((!isset($_POST["death_char"]) OR safe_text_db($_POST["death_char"]) == "n") AND $humo_option['death_char'] == "y" ) {
		include(CMS_ROOTPATH."languages/change_all.php");  // change infinity to cross
		$result = $db_functions->update_settings('death_char','n');
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



// *** Show tabs ***
$menu_admin='settings';
if (isset($_POST['menu_admin'])) $menu_admin=$_POST['menu_admin'];
if (isset($_GET['menu_admin'])) $menu_admin=$_GET['menu_admin'];

echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="">';
echo '<div class="pageHeading">';
	// <div class="pageHeadingText">Configuratie gegevens</div>
	// <div class="pageHeadingWidgets" aria-hidden="true" style="display: none;"></div>

	echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
		echo '<ul class="pageTabs">';
			//echo '<li class="pageTabItem"><div tabindex="0" class="pageTab pageTab-active">Details</div></li>';

			$select_item=''; if ($menu_admin=='settings'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?page='.$page.'">'.__('Settings')."</a></div></li>";

			// *** Homepage ***
			$select_item=''; if ($menu_admin=='settings_homepage'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?page='.$page.'&amp;menu_admin=settings_homepage'.'">'.__('Homepage')."</a></div></li>";

			// *** Family tree data ***
			$select_item=''; if ($menu_admin=='settings_special'){ $select_item=' pageTab-active'; }
			echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?page='.$page.'&amp;menu_admin=settings_special'.'">'.__('Special settings')."</a></div></li>";

		echo '</ul>';
	echo '</div>';
echo '</div>';
echo '</div>';


// *** Align content to the left ***
echo '<div style="float: left; background-color:white; height:500px; padding:10px;">';

	// *** Show settings ***
	if (isset($menu_admin) AND $menu_admin=='settings'){

		//if(CMS_SPECIFIC == "Joomla") {
		//	echo '<form method="post" action="index.php?option=com_humo-gen&amp;task=admin&amp;page=settings">';
		//}
		//else {
			echo '<form method="post" action="index.php">';
		//}
		echo '<input type="hidden" name="page" value="'.$page.'">';

		//echo '<table class="humo standard" border="1">';
		echo '<table class="humo" border="1">';

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
			echo '</select>';
		echo '</td></tr>';

		echo '<tr><td>';
		printf(__('Standard language %s'),'HuMo-genealogy');
		echo '</td><td><select size="1" name="default_language">';
			if($langs) {
				for($i=0; $i<count($langs); $i++) {
					$select=''; if ($humo_option['default_language']==$langs[$i][1]){ $select=' SELECTED'; }
					echo '<option value="'.$langs[$i][1].'"'.$select.'>'.$langs[$i][0].'</option>';
				}
			}
		echo '</select>';
		echo '</td></tr>';

		echo '<tr><td>'.__('Standard language admin menu').'</td><td><select size="1" name="default_language_admin">';
			if($langs_admin) {
				for($i=0; $i<count($langs_admin); $i++) {
					$select=''; if ($humo_option['default_language_admin']==$langs_admin[$i][1]){ $select=' SELECTED'; }
					echo '<option value="'.$langs_admin[$i][1].'"'.$select.'>'.$langs_admin[$i][0].'</option>';
				}
			}
			echo '</select>';
		echo '</td></tr>';

		echo '<tr><td>'.__('Text in footer for all pages').'</td><td>';
			if(CMS_SPECIFIC == "Joomla") {  $cols="48"; } else { $cols="80"; }   // in joomla make sure it won't run off the screen
			echo "<textarea cols=".$cols." rows=1 name=\"text_footer\" style='height: 20px;'>".htmlentities($humo_option["text_footer"],ENT_NOQUOTES)."</textarea><br>";
			echo __('Can be used for statistics, counter, etc. It\'s possible to use HTML codes!');
		echo '</td></tr>';

		// *** Debug options ***
		echo '<tr><td valign="top">';
			printf(__('Debug %s front pages'),'HuMo-genealogy');
		echo '</td><td><select size="1" name="debug_front_pages">';
			$selected=''; if ($humo_option["debug_front_pages"]!='y') $selected=' SELECTED';
			echo '<option value="y">'.__('Yes').'</option>';
			echo '<option value="n"'.$selected.'>'.__('No').'</option>';
			echo '</select> ';
			printf(__('Only use this option to debug problems in %s.'),'HuMo-genealogy');
		echo '</td></tr>';

		echo '<tr><td valign="top">';
			printf(__('Debug %s admin pages'),'HuMo-genealogy');
		echo '</td><td><select size="1" name="debug_admin_pages">';
			$selected=''; if ($humo_option["debug_admin_pages"]!='y') $selected=' SELECTED';
			echo '<option value="y">'.__('Yes').'</option>';
			echo '<option value="n"'.$selected.'>'.__('No').'</option>';
			echo '</select> ';
			printf(__('Only use this option to debug problems in %s.'),'HuMo-genealogy');
		echo '</td></tr>';

		echo '<tr class="table_header"><th colspan="2">'.__('Search engine settings').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

		echo '<tr class="humo_color"><td valign="top">url_rewrite<br>'.__('Improve indexing of search engines (like Google)').'</td><td><select size="1" name="url_rewrite">';
			$selected=''; if ($humo_option["url_rewrite"]!='j') $selected=' SELECTED';
			echo '<option value="j">'.__('Yes').'</option>';
			echo '<option value="n"'.$selected.'>'.__('No').'</option>';
			echo '</select> <b>'.__('ATTENTION: the Apache module "mod_rewrite" has to be installed!').'</b><br>';
			echo 'URL&nbsp;&nbsp;: http://www.website.nl/humo-gen/family.php?id=F12<br>';
			echo __('becomes:').' http://www.website.nl/humo-gen/family/F12/<br>';
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
			echo '</select>';
		echo '</td></tr>';

		echo '<tr><td>'.__('Mail form: use newsletter question').'</td><td>';
			echo '<a name="timeline_anchor"></a>'; // this belongs to the timeline settings - placed here it makes the page reload with timeline line in middle of page
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
		// *** Using HTML 5 email check ***
		echo '<td><input type="email" name="general_email" value="'.$humo_option["general_email"].'" size="40"> '.__('Send registration form to this e-mail address.').'</td></tr>';

		echo '<tr class="humo_color"><td>'.__('Visitor registration: use spam question').'</td><td>';
		echo '<select size="1" name="registration_use_spam_question">';
		$selected=''; if ($humo_option["registration_use_spam_question"]!='y') $selected=' SELECTED';
		echo '<option value="y">'.__('Yes').'</option>';
		echo '<option value="n"'.$selected.'>'.__('No').'</option>';
		echo '</select>';
		echo '</td></tr>';

		echo '<tr><td>'.__('Password forgotten e-mail address').'</td>';
		// *** Using HTML 5 email check ***
		echo '<td><input type="email" name="password_retreival" value="'.$humo_option["password_retreival"].'" size="40" placeholder="no-reply@your-website.com"> '.__('To enable password forgotten option: set a sender e-mail address.').'</td></tr>';

		/*
		****************************
		Kai Mahnke 2020-04
		Added Email configuration settings that will be used in mail.php, mailform.php and registration.php
		****************************
		*/
		echo '<tr class="table_header"><th colspan="2">'.__('Email Settings').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

		echo '<tr><td>'.__('Email Settings').'</td>';
		echo '<td>'.__('TIP: mail will work without changing these parameters at most hosting providers.').'</td></tr>';

		echo '<tr><td style="white-space:nowrap;">'.__('Mail: configuration').'</td>';
		echo '<td><select size="1" name="mail_auto">';
		$selected=''; if ($humo_option["mail_auto"]== 'auto') $selected=' SELECTED';
		echo '<option value="auto"'.$selected.'>'.__('auto').'</option>';
		$selected=''; if ($humo_option["mail_auto"]== 'manual') $selected=' SELECTED';
		echo '<option value="manual"'.$selected.'>'.__('manual').'</option>';
		echo '</select><br>';
		echo __('Setting: "auto" = use settings below.<br>Setting: "manual" = change settings in /include/mail.php');
		echo '</td></tr>';

		echo '<tr><td>'.__('Mail: username').'</td>';
		echo '<td><input type="text" name="email_user" value="'.$humo_option["email_user"].'" size="32"> ';
		echo __('Gmail: [email_address]@gmail.com');
		echo '</td></tr>';

		echo '<tr><td>'.__('Mail: password').'</td>';
		echo '<td><input type="text" name="email_password" value="'.$humo_option["email_password"].'" size="32"></td></tr>';


		echo '<tr><td>'.__('SMTP: mail server').'</td>';
		echo '<td><input type="text" name="smtp_server" value="'.$humo_option["smtp_server"].'" size="32"> ';
		echo __('Gmail: smtp.gmail.com');
		echo '</td></tr>';

		echo '<tr><td style="white-space:nowrap;">'.__('SMTP: port').'</td>';
		echo '<td><select size="1" name="smtp_port">';
		$selected=''; if ($humo_option["smtp_port"]== '25') $selected=' SELECTED';
		echo '<option value="25"'.$selected.'>25</option>';
		$selected=''; if ($humo_option["smtp_port"]== '465') $selected=' SELECTED';
		echo '<option value="465"'.$selected.'>465</option>';
		$selected=''; if ($humo_option["smtp_port"]== '587') $selected=' SELECTED';
		echo '<option value="587"'.$selected.'>587</option>';
		echo '</select> ';
		echo __('Gmail: 587');
		echo '</td></tr>';


		echo '<tr><td style="white-space:nowrap;">'.__('SMTP: authentication').'</td>';
		echo '<td><select size="1" name="smtp_auth">';
		$selected=''; if ($humo_option["smtp_auth"]== 'true') $selected=' SELECTED';
		echo '<option value="true"'.$selected.'>'.__('true').'</option>';
		$selected=''; if ($humo_option["smtp_auth"]== 'false') $selected=' SELECTED';
		echo '<option value="false"'.$selected.'>'.__('false').'</option>';
		echo '</select> ';
		echo __('Gmail: true');
		echo '</td></tr>';

		echo '<tr><td style="white-space:nowrap;">'.__('SMTP: encryption type').'</td>';
		echo '<td><select size="1" name="smtp_encryption">';
		$selected=''; if ($humo_option["smtp_encryption"]== 'tls') $selected=' SELECTED';
		echo '<option value="tls"'.$selected.'>TLS</option>';
		$selected=''; if ($humo_option["smtp_encryption"]== 'ssl') $selected=' SELECTED';
		echo '<option value="ssl"'.$selected.'>SSL</option>';
		echo '</select> ';
		echo __('Gmail: TLS');
		echo '</td></tr>';

		echo '<tr><td style="white-space:nowrap;">'.__('SMTP: debugging').'</td>';
		echo '<td><select size="1" name="smtp_debug">';
		$selected=''; if ($humo_option["smtp_debug"]== '0') $selected=' SELECTED';
		echo '<option value="0"'.$selected.'>'.__('Off').'</option>';
		$selected=''; if ($humo_option["smtp_debug"]== '1') $selected=' SELECTED';
		echo '<option value="1"'.$selected.'>'.__('Client').'</option>';
		$selected=''; if ($humo_option["smtp_debug"]== '2') $selected=' SELECTED';
		echo '<option value="2"'.$selected.'>'.__('Client and Server').'</option>';
		echo '</select>';
		echo '</td></tr>';
		/*
		***************************
		End changes
		***************************
		*/

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
		//$selected=''; if ($humo_option["date_display"]== 'is') $selected=' SELECTED';
		//echo '<option value="is"'.$selected.'>'.__('Israel - 5 Jan 1787 (15 Tevet 5547)').'</option>';
		echo '</select>'; 
		echo "</td></tr>";

		echo '<tr><td style="white-space:nowrap;">'.__('Order of names in reports').'</td>';
		echo '<td><select size="1" name="name_order">';
		$selected=''; if ($humo_option["name_order"]== 'western') $selected=' SELECTED';
		echo '<option value="western"'.$selected.'>'.__('Western').'</option>';
		$selected=''; if ($humo_option["name_order"]== 'chinese') $selected=' SELECTED';
		echo '<option value="chinese"'.$selected.'>'.__('Chinese')."/ ".__('Hungarian').'</option>';
		echo '</select>';
		echo "&nbsp;".__('Western - reports: John Smith, lists: Smith, John. Chinese 中文 - reports and lists: 刘 理想').". ".__('Hungarian - reports and lists: Smith John');
		echo "</td></tr>";

		// timeline default
		echo '<tr><td>'.__('Default timeline file (per language)').'</td><td>';

		// *** First select language ***
		if($langs) {
			echo '<select onChange="window.location =\'index.php?page=settings&timeline_language=\' + this.value + \'#timeline_anchor\'; "  size="1" name="timeline_language">';
			// *** Default language = english ***
			//echo '<option value="default_timelines"'.$select.'>English</option>'; // *** Don't add "English" in translation file! ***
			echo '<option value="default_timelines"'.$select.'>'.__('Default').'</option>'; // *** Don't add "English" in translation file! ***
			for($i=0; $i<count($langs); $i++) { 
				if(is_dir(CMS_ROOTPATH.'languages/'.$langs[$i][1].'/timelines/')) {
					$select=''; if ($time_lang==$langs[$i][1]){ $select=' SELECTED'; }
					echo '<option value="'.$langs[$i][1].'"'.$select.'>'.$langs[$i][0].'</option>';
				}
			}
			echo '</select>';
		}

		echo "&nbsp;&nbsp;";

		// *** First select language, then the timeline files of that language is shown ***
		$folder=@opendir(CMS_ROOTPATH.'languages/'.$time_lang.'/timelines/');
		// *** Default language = english ***
		if ($time_lang=='default_timelines') $folder=@opendir(CMS_ROOTPATH.'languages/'.$time_lang);
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
			echo '</select>';
			echo "&nbsp;&nbsp;";
			echo __('First select language, then select the default timeline for that language.');
		}
		//@closedir($folder);
		if($folder !== false) @closedir($folder);

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

	}


	// *** Show homepage settings ***
	if (isset($menu_admin) AND $menu_admin=='settings_homepage'){

		// *** Reset all modules ***
		if (isset($_GET['template_homepage_reset']) AND $_GET['template_homepage_reset']=='1'){
			$sql="DELETE FROM humo_settings WHERE setting_variable='template_homepage'";
			$result=$dbh->query($sql);

			// *** Reload page to get new values ***
			echo '<script type="text/javascript"> window.location="index.php?page=settings&menu_admin=settings_homepage";</script>';
		}

		// *** Change Module ***
		if (isset($_POST['change_module'])){
			$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage'");
			while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
				$setting_value=$_POST[$dataDb->setting_id.'module_status'].'|'.$_POST[$dataDb->setting_id.'module_column'].'|'.$_POST[$dataDb->setting_id.'module_item'];
				if (isset($_POST[$dataDb->setting_id.'module_option_1'])) $setting_value.='|'.$_POST[$dataDb->setting_id.'module_option_1'];
				if (isset($_POST[$dataDb->setting_id.'module_option_2'])) $setting_value.='|'.$_POST[$dataDb->setting_id.'module_option_2'];
				$sql="UPDATE humo_settings SET setting_value='".safe_text_db($setting_value)."' WHERE setting_id=".safe_text_db($_POST[$dataDb->setting_id.'id']);
//echo $sql.'<br>';
				$result=$dbh->query($sql);
			}
		}

		// *** Remove module  ***
		if (isset($_GET['remove_module']) AND is_numeric($_GET['remove_module'])){
			$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_id='".$_GET['remove_module']."'");
			$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
			$sql="DELETE FROM humo_settings WHERE setting_id='".$dataDb->setting_id."'";
			$result=$dbh->query($sql);

			// *** Re-order links ***
			$repair_order=$dataDb->setting_order;
			$item=$dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order>".$repair_order);
			while($itemDb=$item->fetch(PDO::FETCH_OBJ)){
				$sql="UPDATE humo_settings SET setting_order='".($itemDb->setting_order-1)."' WHERE setting_id=".$itemDb->setting_id;
				$result=$dbh->query($sql);
			}
		}

		// *** Add module ***
		if (isset($_POST['add_module']) AND is_numeric ($_POST['module_order'])){
			$setting_value=$_POST['module_status']."|".$_POST['module_column']."|".$_POST['module_item'];
			$sql="INSERT INTO humo_settings SET setting_variable='template_homepage',
				setting_value='".safe_text_db($setting_value)."', setting_order='".safe_text_db($_POST['module_order'])."'";
			$result=$dbh->query($sql);
		}

		if (isset($_GET['mod_up'])){
			// *** Search previous module ***
			$item=$dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order=".(safe_text_db($_GET['module_order'])-1));
			$itemDb=$item->fetch(PDO::FETCH_OBJ);

			// *** Raise previous module ***
			$sql="UPDATE humo_settings SET setting_order='".safe_text_db($_GET['module_order'])."' WHERE setting_id='".$itemDb->setting_id."'";

			$result=$dbh->query($sql);
			// *** Lower module order ***
			$sql="UPDATE humo_settings SET setting_order='".(safe_text_db($_GET['module_order'])-1)."' WHERE setting_id=".safe_text_db($_GET['id']);

			$result=$dbh->query($sql);
		}
		if (isset($_GET['mod_down'])){
			// *** Search next link ***
			$item=$dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order=".(safe_text_db($_GET['module_order'])+1));
			$itemDb=$item->fetch(PDO::FETCH_OBJ);

			// *** Lower previous link ***
			$sql="UPDATE humo_settings SET setting_order='".safe_text_db($_GET['module_order'])."' WHERE setting_id='".$itemDb->setting_id."'";

			$result=$dbh->query($sql);
			// *** Raise link order ***
			$sql="UPDATE humo_settings SET setting_order='".(safe_text_db($_GET['module_order'])+1)."' WHERE setting_id=".safe_text_db($_GET['id']);

			$result=$dbh->query($sql);
		}

		// *** Show all links ***
		//if(CMS_SPECIFIC == "Joomla") {
		//	echo "<form method='post' action='index.php?option=com_humo-gen&amp;task=admin&amp;page=favorites'>";
		//}
		//else {
			echo "<form method='post' action='index.php'>";
		//}
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="settings_homepage">';

		//echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large">';
		//echo '<table class="humo standard" border="1">';
		echo '<table class="humo" border="1">';
		//echo '<table class="humo" border="1" style="float:left;">';
			echo '<tr class="table_header_large">';
			echo '<th class="table_header" colspan="7">'.__('Homepage template');
			echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;template_homepage_reset=1">['.__('Default settings').']</a>';
			echo '</th></tr>';

			echo '<tr class="table_header"><th>'.__('Status').'</th><th>'.__('Position').'</th><th>'.__('Item').'</th><th><br></th>';
			echo '<th><input type="Submit" name="change_module" value="'.__('Change').'"></th></tr>';
			$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
			// *** Number for new module ***
			$count_links=0; if ($datasql->rowCount()) $count_links=$datasql->rowCount();
			$new_number=1; if ($count_links) $new_number=$count_links+1;
			if ($datasql){
				$teller=1;
				while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
					$lijst=explode("|",$dataDb->setting_value);
					echo '<tr>';
						echo '<input type="hidden" name="'.$dataDb->setting_id.'id" value="'.$dataDb->setting_id.'">';

					//echo '<td>';
					//	echo '<input type="hidden" name="'.$dataDb->setting_id.'id" value="'.$dataDb->setting_id.'">';

					//	echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_module='.$dataDb->setting_id.'">
					//		<img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="remove"></a>&nbsp;&nbsp;&nbsp;';
					//echo '</td>';

					// *** Active/ inactive with background colour ***
					$bgcolor=''; if ($lijst[0]=='inactive') $bgcolor='bgcolor="orange"';
					echo '<td '.$bgcolor.'><select size="1" name="'.$dataDb->setting_id.'module_status">';

						echo '<option value="active">'.__('Active').'</option>';
						$selected=''; if ($lijst[0]=='inactive') $selected=' SELECTED';
						echo '<option value="inactive"'.$selected.'>'.__('Inactive').'</option>';
						echo '</select>';
					echo '</td>';

					echo '<td><select size="1" name="'.$dataDb->setting_id.'module_column">';
						echo '<option value="left">'.__('Left').'</option>';
						$selected=''; if ($lijst[1]=='center') $selected=' SELECTED';
						echo '<option value="center"'.$selected.'>'.__('Center').'</option>';
						$selected=''; if ($lijst[1]=='right') $selected=' SELECTED';
						echo '<option value="right"'.$selected.'>'.__('Right').'</option>';
						echo '</select>';

						if ($dataDb->setting_order!='1'){
							echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;mod_up=1&amp;module_order='.$dataDb->setting_order.
							'&amp;id='.$dataDb->setting_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="up"></a>'; }
						if ($dataDb->setting_order!=$count_links){
							echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;mod_down=1&amp;module_order='.$dataDb->setting_order.'&amp;id='.
							$dataDb->setting_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>'; }
					echo '</td>';

// style="vertical-align:middle"
					echo '<td><select size="1" name="'.$dataDb->setting_id.'module_item">';
						echo '<option value="select_family_tree">'.__('Select family tree').'</option>';

						$selected=''; if ($lijst[2]=='selected_family_tree') $selected=' SELECTED';
						echo '<option value="selected_family_tree"'.$selected.'>'.__('Selected family tree').'</option>';

						$selected=''; if ($lijst[2]=='search') $selected=' SELECTED';
						echo '<option value="search"'.$selected.'>'.__('Search').'</option>';

						$selected=''; if ($lijst[2]=='names') $selected=' SELECTED';
						echo '<option value="names"'.$selected.'>'.__('Names').'</option>';

						$selected=''; if ($lijst[2]=='history') $selected=' SELECTED';
						echo '<option value="history"'.$selected.'>'.__('Today in history').'</option>';

						$selected=''; if ($lijst[2]=='favourites') $selected=' SELECTED';
						echo '<option value="favourites"'.$selected.'>'.__('Favourites').'</option>';

						$selected=''; if ($lijst[2]=='alphabet') $selected=' SELECTED';
						echo '<option value="alphabet"'.$selected.'>'.__('Surnames Index').'</option>';

						$selected=''; if ($lijst[2]=='random_photo') $selected=' SELECTED';
						echo '<option value="random_photo"'.$selected.'>'.__('Random photo').'</option>';

						$selected=''; if ($lijst[2]=='text') $selected=' SELECTED';
						echo '<option value="text"'.$selected.'>'.__('Text').'</option>';

						$selected=''; if ($lijst[2]=='own_script') $selected=' SELECTED';
						echo '<option value="own_script"'.$selected.'>'.__('Own script').'</option>';

						$selected=''; if ($lijst[2]=='cms_page') $selected=' SELECTED';
						echo '<option value="cms_page"'.$selected.'>'.__('CMS Own pages').'</option>';

						$selected=''; if ($lijst[2]=='empty_line') $selected=' SELECTED';
						echo '<option value="empty_line"'.$selected.'>'.__('EMPTY LINE').'</option>';

						echo '</select>';

					echo '</td>';
					// *** Extra table column used for extra options ***
					echo '<td>';

						//if ($lijst[2]=='select_family_tree'){
						//	echo ' '.__('Only use for multiple family trees.');
						//}

						if ($lijst[2]=='names'){
							echo ' '.__('Columns');
							echo ' <select size="1" name="'.$dataDb->setting_id.'module_option_1">';
								echo '<option value="1">1</option>';
								$selected=''; if ($lijst[3]=='2') $selected=' SELECTED';
								echo '<option value="2"'.$selected.'>2</option>';
								$selected=''; if ($lijst[3]=='3') $selected=' SELECTED';
								echo '<option value="3"'.$selected.'>3</option>';
								$selected=''; if ($lijst[3]=='4') $selected=' SELECTED';
								echo '<option value="4"'.$selected.'>4</option>';
							echo '</select>';

							echo ' '.__('Rows');
							echo ' <select size="1" name="'.$dataDb->setting_id.'module_option_2">';
								echo '<option value="1">1</option>';
								$selected=''; if ($lijst[4]=='2') $selected=' SELECTED';
								echo '<option value="2"'.$selected.'>2</option>';
								$selected=''; if ($lijst[4]=='3') $selected=' SELECTED';
								echo '<option value="3"'.$selected.'>3</option>';
								$selected=''; if ($lijst[4]=='4') $selected=' SELECTED';
								echo '<option value="4"'.$selected.'>4</option>';
								$selected=''; if ($lijst[4]=='5') $selected=' SELECTED';
								echo '<option value="5"'.$selected.'>5</option>';
								$selected=''; if ($lijst[4]=='6') $selected=' SELECTED';
								echo '<option value="6"'.$selected.'>6</option>';
								$selected=''; if ($lijst[4]=='7') $selected=' SELECTED';
								echo '<option value="7"'.$selected.'>7</option>';
								$selected=''; if ($lijst[4]=='8') $selected=' SELECTED';
								echo '<option value="8"'.$selected.'>8</option>';
								$selected=''; if ($lijst[4]=='9') $selected=' SELECTED';
								echo '<option value="9"'.$selected.'>9</option>';
								$selected=''; if ($lijst[4]=='10') $selected=' SELECTED';
								echo '<option value="10"'.$selected.'>10</option>';
								$selected=''; if ($lijst[4]=='11') $selected=' SELECTED';
								echo '<option value="11"'.$selected.'>11</option>';
								$selected=''; if ($lijst[4]=='12') $selected=' SELECTED';
								echo '<option value="12"'.$selected.'>12</option>';
							echo '</select>';
						}

						if ($lijst[2]=='text'){
							// *** Header text ***
							$header=''; if (isset($lijst[3])) $header=$lijst[3];
							echo '<input type="text" placeholder="'.__('Header').'" name="'.$dataDb->setting_id.'module_option_1" value="'.$header.'" size="30"><br>';

							$module_text='';if (isset ($lijst[4])) $module_text=$lijst[4];
							echo '<textarea rows="4" cols="50" placeholder="'.__('Text').'" name="'.$dataDb->setting_id.'module_option_2">'.$module_text.'</textarea><br>';

							echo __('Show text block, HTML codes can be used.');
						}

						if ($lijst[2]=='own_script'){
							// *** Header text ***
							$header=''; if (isset($lijst[3])) $header=$lijst[3];
							echo '<input type="text" placeholder="'.__('Header').'" name="'.$dataDb->setting_id.'module_option_1" value="'.$header.'" size="30"><br>';
							$module_text='';if (isset ($lijst[4])) $module_text=$lijst[4];
							echo '<input type="text" placeholder="'.__('File name').'" name="'.$dataDb->setting_id.'module_option_2" value="'.$module_text.'" size="30"><br>';
							echo __('File name (full path) of the file with own script.');
						}

						if ($lijst[2]=='cms_page'){
							echo ' <select size="1" name="'.$dataDb->setting_id.'module_option_1">';
							$qry=$dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
							while($pageDb=$qry->fetch(PDO::FETCH_OBJ)){
								//$select=''; if ($lijst[3]==$pageDb->setting_id.'module_option_1'){ $select=' SELECTED'; }
								$selected=''; if ($lijst[3]==$pageDb->page_id){ $selected=' SELECTED'; }
								echo '<option value="'.$pageDb->page_id.'"'.$selected.'>'.$pageDb->page_title.'</option>';
							}
							echo '</select>';
							echo ' '.__('Show text from CMS system.');
						}

						if ($lijst[2]=='history'){
							echo ' '.__('View');
							echo ' <select size="1" name="'.$dataDb->setting_id.'module_option_1">';
								echo '<option value="with_table">'.__('with table').'</option>';

								$selected=''; if ($lijst[3]=='without_table') $selected=' SELECTED';
								echo '<option value="without_table"'.$selected.'>'.__('without table').'</option>';
							echo '</select>';
						}

					echo '</td>';

					//echo '<td><br></td>';
					echo '<td><a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_module='.$dataDb->setting_id.'">
							<img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="remove"></a></td>';
					echo '</tr>';
					$teller++;
				}

				// *** Add new module ***
				echo '<tr bgcolor="green">';
					//echo "<td><br></td>";
					echo '<input type="hidden" name="module_order" value="'.$new_number.'">';

					echo '<td><select size="1" name="module_status">';
						echo '<option value="active">'.__('Active').'</option>';
						echo '<option value="inactive">'.__('Inactive').'</option>';
						echo '</select>';
					echo '</td>';

					echo '<td><select size="1" name="module_column">';
						echo '<option value="left">'.__('Left').'</option>';
						echo '<option value="center">'.__('Center').'</option>';
						echo '<option value="right">'.__('Right').'</option>';
					echo '</select></td>';

					echo '<td><select size="1" name="module_item">';
						echo '<option value="select_family_tree">'.__('Select family tree').'</option>';
						echo '<option value="selected_family_tree">'.__('Selected family tree').'</option>';
						echo '<option value="search">'.__('Search').'</option>';
						echo '<option value="names">'.__('Names').'</option>';
						echo '<option value="history">'.__('Today in history').'</option>';
						echo '<option value="favourites">'.__('Favourites').'</option>';
						echo '<option value="alphabet">'.__('Surnames Index').'</option>';
						echo '<option value="random_photo">'.__('Random photo').'</option>';
						echo '<option value="text">'.__('Text').'</option>';
						echo '<option value="own_script">'.__('Own script').'</option>';
						echo '<option value="cms_page">'.__('CMS Own pages').'</option>';
						echo '<option value="empty_line">'.__('EMPTY LINE').'</option>';
						echo '</select>';
					echo '</td>';

					echo '<td><br></td>';

					echo '<td><input type="Submit" name="add_module" value="'.__('Add').'"></td>';
				echo "</tr>";
			}
			else{
				echo '<tr><td colspan="4">'.__('Database is not yet available.').'</td></tr>';
			}
		echo '</table>';
		echo '</form>';
		//echo __('If there is no text in an item, the item will not be shown in the homepage.');
		echo __("If the left column isn't used, the center column will be made large automatically.");

		echo '<br>';



		echo '<h1 align=center>'.__('Homepage favourites').'</h1>';

		// *** Change Link ***
		if (isset($_POST['change_link'])){
			$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
			while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
				$setting_value=$_POST[$dataDb->setting_id.'own_code']."|".$_POST[$dataDb->setting_id.'link_text'];
				$sql="UPDATE humo_settings SET setting_value='".safe_text_db($setting_value)."' WHERE setting_id=".safe_text_db($_POST[$dataDb->setting_id.'id']);
				$result=$dbh->query($sql);
			}
		}

		// *** Remove link  ***
		if (isset($_GET['remove_link']) AND is_numeric($_GET['remove_link'])){
			$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_id='".$_GET['remove_link']."'");
			$dataDb=$datasql->fetch(PDO::FETCH_OBJ);
			$sql="DELETE FROM humo_settings WHERE setting_id='".$dataDb->setting_id."'";
			$result=$dbh->query($sql);

			// *** Re-order links ***
			$repair_order=$dataDb->setting_order;
			$item=$dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order>".$repair_order);
			while($itemDb=$item->fetch(PDO::FETCH_OBJ)){
				$sql="UPDATE humo_settings SET setting_order='".($itemDb->setting_order-1)."' WHERE setting_id=".$itemDb->setting_id;
				$result=$dbh->query($sql);
			}
		}

		// *** Add link ***
		if (isset($_POST['add_link']) AND is_numeric ($_POST['link_order'])){
			$setting_value=$_POST['own_code']."|".$_POST['link_text'];
			$sql="INSERT INTO humo_settings SET setting_variable='link',
				setting_value='".safe_text_db($setting_value)."', setting_order='".safe_text_db($_POST['link_order'])."'";
			$result=$dbh->query($sql);
		}

		if (isset($_GET['up'])){
			// *** Search previous link ***
			$item=$dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order=".(safe_text_db($_GET['link_order'])-1));
			$itemDb=$item->fetch(PDO::FETCH_OBJ);

			// *** Raise previous link ***
			$sql="UPDATE humo_settings SET setting_order='".safe_text_db($_GET['link_order'])."' WHERE setting_id='".$itemDb->setting_id."'";

			$result=$dbh->query($sql);
			// *** Lower link order ***
			$sql="UPDATE humo_settings SET setting_order='".(safe_text_db($_GET['link_order'])-1)."' WHERE setting_id=".safe_text_db($_GET['id']);

			$result=$dbh->query($sql);
		}
		if (isset($_GET['down'])){
			// *** Search next link ***
			$item=$dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order=".(safe_text_db($_GET['link_order'])+1));
			$itemDb=$item->fetch(PDO::FETCH_OBJ);

			// *** Lower previous link ***
			$sql="UPDATE humo_settings SET setting_order='".safe_text_db($_GET['link_order'])."' WHERE setting_id='".$itemDb->setting_id."'";

			$result=$dbh->query($sql);
			// *** Raise link order ***
			$sql="UPDATE humo_settings SET setting_order='".(safe_text_db($_GET['link_order'])+1)."' WHERE setting_id=".safe_text_db($_GET['id']);

			$result=$dbh->query($sql);
		}

		// *** Show all links ***
		//if(CMS_SPECIFIC == "Joomla") {
		//	echo "<form method='post' action='index.php?option=com_humo-gen&amp;task=admin&amp;page=favorites'>";
		//}
		//else {
			echo "<form method='post' action='index.php'>";
		//}
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="settings_homepage">';

		//echo '<table class="humo standard" border="1" cellspaning="0" style="text-align:center;"><tr class="table_header_large">';
		echo '<table class="humo standard" border="1">';
			echo '<tr class="table_header_large">';
			echo '<th class="table_header" colspan="4">'.__('Show list of favourites in homepage').'</th></tr>';

			echo '<tr class="table_header"><th>Nr.</th><th>'.__('Own code').'</th><th>'.__('Description').'</th>';
			echo '<th><input type="Submit" name="change_link" value="'.__('Change').'"></th></tr>';
			$datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' ORDER BY setting_order");
			// *** Number for new link ***
			$count_links=0; if ($datasql->rowCount()) $count_links=$datasql->rowCount();
			$new_number=1; if ($count_links) $new_number=$count_links+1;
			if ($datasql){
				$teller=1;
				while ($dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
					$lijst=explode("|",$dataDb->setting_value);
					echo '<tr>';
					echo '<td>';

					echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_link='.$dataDb->setting_id.'">
						<img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="remove"></a> ';

						echo '<input type="hidden" name="'.$dataDb->setting_id.'id" value="'.$dataDb->setting_id.'">'.__('Link').' '.$teller;

						if ($dataDb->setting_order!='1'){
							echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;up=1&amp;link_order='.$dataDb->setting_order.
							'&amp;id='.$dataDb->setting_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_up.gif" border="0" alt="up"></a>'; }
						if ($dataDb->setting_order!=$count_links){
							echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;down=1&amp;link_order='.$dataDb->setting_order.'&amp;id='.
							$dataDb->setting_id.'"><img src="'.CMS_ROOTPATH_ADMIN.'images/arrow_down.gif" border="0" alt="down"></a>'; }
					echo '</td>';
					echo '<td><input type="text" name="'.$dataDb->setting_id.'own_code" value="'.$lijst[0].'" size="5"></td>';
					echo '<td><input type="text" name="'.$dataDb->setting_id.'link_text" value="'.$lijst[1].'" size="20"></td>';
					echo '<td><br></td>';
					echo '</tr>';
					$teller++;
				}

				// *** Add new link ***
				echo '<tr bgcolor="green">';
					echo "<td><br></td>";
					echo '<input type="hidden" name="link_order" value="'.$new_number.'">';
					echo '<td><input type="text" name="own_code" value="Code" size="5"></td>';
					echo '<td><input type="text" name="link_text" value="'.__('Owner of tree').'" size="20"></td>';
					echo '<td><input type="Submit" name="add_link" value="'.__('Add').'"></td>';
				echo "</tr>";
			}
			else{
				echo '<tr><td colspan="4">'.__('Database is not yet available.').'</td></tr>';
			}
			echo "</table>";
		echo "</form>";
		echo __('Own code is the code that has to be entered in your genealogy program under "own code or REFN"
		<p>Do the following:<br>
		1) In your genealogy program, put a code. For example, with the patriarch enter a code "patriarch".<br>
		2) Enter the same code in this table (multiple codes are possible)<br>
		3) After processing the GEDCOM file, an extra link will appear in the main menu, i.e. to the patriarch!<br>');


		echo '<br>';


		// *** Slideshow ***
		echo '<table class="humo" border="1">';
		//echo '<tr class="table_header"><th colspan="2">'.__('Homepage').'</th></tr>';

		echo '<form method="post" action="index.php">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="settings_homepage">';

		echo '<tr class="table_header"><th colspan="2">'.__('Slideshow on the homepage').' <input type="Submit" name="save_option2" value="'.__('Change').'"></th></tr>';
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

		echo '</table>';
	}



	// *** Show special settings ***
	if (isset($menu_admin) AND $menu_admin=='settings_special'){

		//echo '<h1 align=center>'.__('Special settings').'</h1>';

		//echo '<table class="humo standard" border="1">';
		echo '<table class="humo" border="1">';
		echo '<tr class="table_header"><th colspan="2">'.__('Special settings').'</th></tr>';

		echo '<form method="post" action="index.php">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="menu_admin" value="settings_special">';

		echo '<tr><td>'.__('Jewish settings').'</td><td>';
		echo '<u>'.__('Display settings').':</u><br>';
		$checked = '';  if(isset($humo_option['death_char']) and $humo_option['death_char'] == "y")  { $checked = " checked "; }
		echo '<input type="checkbox" id="death_char" value="y" name="death_char" '.$checked.'>  <label for="death_char">'.__('Change all &#134; characters into &infin; characters in all language files')." (".__('unchecking and saving will revert to the cross sign').')</label><br>';
		$checked = '';  if(isset($humo_option['admin_hebdate']) and $humo_option['admin_hebdate'] == "y")  { $checked = " checked "; }
		echo '<input type="checkbox" id="admin_hebdate" value="y" name="admin_hebdate" '.$checked.'>  <label for="admin_hebdate">'.__('Display Hebrew date after Gregorian date: 23 Dec 1980 (16 Tevet 5741)').'</label><br>';
		$checked = '';  if(isset($humo_option['david_stars']) and $humo_option['david_stars'] == "y")  { $checked = " checked "; }
		echo '<input type="checkbox" id="david_stars" value="y" name="david_stars" '.$checked.'>  <label for="david_stars">'.__('Place yellow Stars of David before holocaust victims in lists and reports').'</label><br>';
		$checked = '';  if(isset($humo_option['death_shoa']) and $humo_option['death_shoa'] == "y")  { $checked = " checked "; }
		echo '<input type="checkbox" id="death_shoa" value="y" name="death_shoa" '.$checked.'>  <label for="death_shoa">'.__('Add: "cause of death: murdered" to holocaust victims').'</label><br>';
		echo '<u>'.__('Editor settings').':</u><br>';
		$checked = '';  if(isset($humo_option['admin_hebnight']) and $humo_option['admin_hebnight'] == "y")  { $checked = " checked "; }
		echo '<input type="checkbox" id="admin_hebnight" value="y" name="admin_hebnight" '.$checked.'>  <label for="admin_hebnight">'.__('Add "night" checkbox next to Gregorian dates to calculate Hebrew date correctly').'</label><br>';
		$checked = '';  if(isset($humo_option['admin_hebname']) and $humo_option['admin_hebname'] == "y")  { $checked = " checked "; }
		echo '<input type="checkbox" id="admin_hebname" value="y" name="admin_hebname" '.$checked.'>  <label for="admin_hebname">'.__('Add field for Hebrew name in name section of editor (instead of in "events" list)').'</label><br>';
		$checked = '';  if(isset($humo_option['admin_brit']) and $humo_option['admin_brit'] == "y")  { $checked = " checked "; }
		echo '<input type="checkbox" id="admin_brit" value="y" name="admin_brit" '.$checked.'>  <label for="admin_brit">'.__('Add field for Brit Mila under birth fields (instead of in "events" list)').'</label><br>';
		$checked = '';  if(isset($humo_option['admin_barm']) and $humo_option['admin_barm'] == "y")  { $checked = " checked "; }
		echo '<input type="checkbox" id="admin_barm" value="y" name="admin_barm" '.$checked.'>  <label for="admin_barm">'.__('Add field for Bar/ Bat Mitsva before baptise fields (instead of in "events" list)').'</label>';
		echo '<br><input type="Submit" style="margin:3px" name="save_option3" value="'.__('Change').'">';
		echo '</td></tr>';
		echo '</form>';

			echo '<tr><td>'.__('Sitemap').'</td><td>';
				echo '<b>'.__('Sitemap').'</b> <br>';
				echo __('A sitemap can be used for quick indexing of the family screens by search engines. Add the sitemap link to a search engine (like Google), or add the link in a robots.txt file (in the root folder of your website). Example of robots.txt file, sitemap line:<br>
		Sitemap: http://www.yourwebsite.com/humo-gen/sitemap.php');
				echo '<br><a href="../sitemap.php">'.__('Sitemap').'</a>';
			echo '</td></tr>';

		echo '</table>';

	}

echo '</div>';



?>