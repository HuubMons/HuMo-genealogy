<?php
print '<div id="top_menu">';

$rtlmark='ltr'; if($language["dir"]=="rtl") { $rtlmark='rtl'; }
echo '<div id="top" style="direction:'.$rtlmark.';">';
   echo '<div style="direction:ltr;">';
	echo '<span id="top_website_name">';
		echo '<a href="'.$humo_option["homepage"].'">&nbsp;'.$humo_option["database_name"].'</a>';
	echo '</span>';

	echo '&nbsp;&nbsp;';

	// *** Select family tree ***
	if (!$bot_visit){
		$sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
		$tree_prefix_result2 = $dbh->query($sql);
		$num_rows = $tree_prefix_result2->rowCount();
		if ($num_rows>1){
			echo ' <form method="POST" action="tree_index.php" style="display : inline;" id="top_tree_select">';
			echo __('Family tree');
			echo ': <select size=1 name="database" onChange="this.form.submit();" style="width: 150px; height:20px;">';
			echo '<option value="">'.__('Select a family tree:').'</option>';
			$count=0;
			while($tree_prefixDb=$tree_prefix_result2->fetch(PDO::FETCH_OBJ)) {
				// *** Check if family tree is shown or hidden for user group ***
				$hide_tree_array2=explode(";",$user['group_hide_trees']);
				$hide_tree2=false; if (in_array($tree_prefixDb->tree_id, $hide_tree_array2)) $hide_tree2=true;
				if ($hide_tree2==false){
					$selected='';
					if (isset($_SESSION['tree_prefix'])){
						if ($tree_prefixDb->tree_prefix==$_SESSION['tree_prefix']){ $selected=' SELECTED'; }
					}
					else {
						if($count==0) { $_SESSION['tree_prefix'] = $tree_prefixDb->tree_prefix; $selected=' SELECTED'; }
					}
					$treetext=show_tree_text($tree_prefixDb->tree_prefix, $selected_language);
					echo '<option value="'.$tree_prefixDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
					$count++;
				}
			}
			echo '</select>';
			echo '</form>';
		}
	}
	echo '</div>';
	// *** This code is only used to restore $dataDb reading. Used for picture etc. ***
	//$treetext_name=database_name($_SESSION['tree_prefix'], $selected_language);
	$treetext=show_tree_text($_SESSION['tree_prefix'], $selected_language);

	// *** Show quicksearch field ***
	if (!$bot_visit){
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=list';
		}
		else{
			$path_tmp=CMS_ROOTPATH.'list.php';
		}

		print '<form method="post" action="'.$path_tmp.'" id="top_quicksearch">';
			print '<input type="hidden" name="index_list" value="quicksearch">';
			print '<input type="hidden" name="search_database" value="tree_selected">';
			$quicksearch='';
			if (isset($_POST['quicksearch'])){
				$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
				$_SESSION["save_quicksearch"]=$quicksearch;
			}
			if (isset($_SESSION["save_quicksearch"])){ $quicksearch=$_SESSION["save_quicksearch"]; }
			if($humo_option['min_search_chars']==1) { $pattern=""; $min_chars =" 1 ";}
			else { $pattern='pattern=".{'.$humo_option['min_search_chars'].',}"'; $min_chars = " ".$humo_option['min_search_chars']." ";}
			print '<input type="text" name="quicksearch" value="'.$quicksearch.'" size="15" '.$pattern.' title="'.__('Minimum:').$min_chars.__('characters').'">';
			print ' <input type="submit" value="'.__('Search').'">';
		print "</form>";
	}

	//TEST Line to see all cookies...
	//print_r($_COOKIE);

	// *** Favorite list for families ***
	if (!$bot_visit){
		echo ' <form method="POST" action="'.$uri_path.'family.php'.'" style="display : inline;" id="top_favorites_select">';
			echo '<img src="images/favorite_blue.png"> ';
			echo '<select size=1 name="favorite_id" onChange="this.form.submit();" style="width: 115px; height:20px;">';
			echo '<option value="">'.__('Favourites list:').'</option>';

			$favorites_array='';
			// *** Use session if session is available ***
			if (isset($_SESSION["save_favorites"]) AND $_SESSION["save_favorites"]){
				$favorites_array=$_SESSION["save_favorites"];
			}
			else{
				// *** Get favorites from cookie (only if session is empty) ***
				if (isset($_COOKIE['humo_favorite'])) {
					foreach ($_COOKIE['humo_favorite'] as $name => $value) {
						$favorites_array[]=$value;
					}
					// *** Save cookie values in session ***
					$_SESSION["save_favorites"]=$favorites_array;
				}
			}

			// *** Add new favorite to list of favorites ***
			if (isset($_POST['favorite'])){
				// *** Add value to array ***
				$favorites_array[]=$_POST['favorite'];
				$_SESSION["save_favorites"]=$favorites_array;
			}

			// *** Remove favorite from favorite list ***
			if (isset($_POST['favorite_remove'])){
				if (isset($_SESSION["save_favorites"])){
					unset ($favorites_array);
					foreach($_SESSION['save_favorites'] as $key=>$value){
						if ($value!=$_POST['favorite_remove']){
							$favorites_array[]=$value;
						}
					}
					$_SESSION["save_favorites"]=$favorites_array;
				}
			}

			// *** Show favorites in selection list ***
			if (isset($_SESSION["save_favorites"])){
				sort ($_SESSION['save_favorites']);
				foreach($_SESSION['save_favorites'] as $key=>$value){
					$favorite_array2=explode("|",$value);
					// *** Show only persons in selected family tree ***
					if ($_SESSION['tree_prefix']==$favorite_array2['2']){
						// *** Check if family tree is still the same family tree ***
						//$person_man=mysql_query("SELECT * FROM ".safe_text($_SESSION['tree_prefix'])."person
						//	WHERE pers_gedcomnumber='".$favorite_array2['1']."'",$db);
						//@$person_manDb=mysql_fetch_object($person_man);

						// *** Proces man using a class ***
						//$man_cls = New person_cls;
						//$man_cls->construct($person_manDb);
						//$name=$man_cls->person_name($person_manDb);
						//if ($name==$favorite_array2['0']){
							echo '<option value="'.$favorite_array2['1'].'|'.$favorite_array2['3'].'">'.$favorite_array2['0'].'</option>';
						//}
					}
				}
			}

		echo '</select>';
		echo '</form>';
	}

	// *** Theme select ***
	// *** Hide theme select if there is only one theme, AND it is the default theme ***
	/*
	$show_theme_select=true;
	if (count($theme_folder)==1){
		if (isset($humo_option['default_skin']) AND $humo_option['default_skin'].'.css'==$theme_folder[0]) {
			$show_theme_select=false;
		}
	}

	if ($bot_visit){ $show_theme_select=false; }

	if ($show_theme_select==true){
		echo '<form title="'.__('Select a colour theme (a cookie will be used to remember the theme)').'" action="" id="switchform">';
		echo '<select name="switchcontrol" size="1" onchange="chooseStyle(this.options[this.selectedIndex].value, 365)">';

		if (isset($humo_option['default_skin'])){
			print '<option value="'.$humo_option['default_skin'].'" selected="selected">'.__('Select a theme').':</option>';
			echo '<option value="'.$humo_option['default_skin'].'">'.__('Standard-colours').'</option>';
		}
		else{
			print '<option value="none" selected="selected">'.__('Select a theme').':</option>';
			echo '<option value="none">'.__('Standard-colours').'</option>';
		}

		sort($theme_folder);
		for ($i=0; $i<count($theme_folder); $i++){
			$theme=$theme_folder[$i];
			$theme=str_replace(".css","", $theme);
			print '<option value="'.$theme.'">'.$theme.'</option>';
		}
		echo '</select></form>';
	}
	*/


	// *** Show "A+ A- Reset" ***
	/*
	echo '<span id="top_font_size">';
		echo '&nbsp;&nbsp;&nbsp;<a href="javascript:decreaseFontSize(0);" title="decrease font size">'.$dirmark1.'A-&nbsp;</a>';
		echo ' <a href="javascript:increaseFontSize(0);" title="increase font size">A+</a>';

		$navigator_user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';
		if ((stristr($navigator_user_agent, "chrome")) OR (stristr($navigator_user_agent, "safari"))) {
			// Chrome and Safari: reset is not working good... So skip this code.
		}
		else {  // all other browsers
			echo ' <a href="javascript:delCookie();" title="reset font size">Reset</a>';
		}
	echo '</span>';
	*/

echo '</div>'; // End of Top

// *** Menu ***
$ie7_rtlhack='';  // in some skins in rtl display in IE7 menu runs off the screen and needs float:right
if (strpos($_SERVER['HTTP_USER_AGENT'],"MSIE 7.0") !== false AND $language['dir']=="rtl") { $ie7_rtlhack=' class="headerrtl"'; }
echo '<div id="humo_menu"'.$ie7_rtlhack.'>';

echo '<ul class="humo_menu_item">';
	// *** You can use this link, for an extra link to another main homepage ***
	//echo '<li><a href="'.$humo_option["homepage"].'">'.__('Homepage')."</a></li>";

	$select_menu=''; if ($menu_choice=='main_index'){ $select_menu=' id="current"'; }
	if (CMS_SPECIFIC=='Joomla'){
		$path_tmp='index.php?option=com_humo-gen';
	}
	elseif ($humo_option["url_rewrite"]=="j"){
		$path_tmp='index/'.$_SESSION['tree_prefix']."/";
	}
	else{
		$path_tmp=CMS_ROOTPATH.'index.php?database='.$_SESSION['tree_prefix'];
	}
	echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Home')."</a></li>\n";

	// *** Menu genealogy (for CMS pages) ***
	/*
	$cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
	if($cms_qry->rowCount() > 0) {
		$select_menu=''; if ($menu_choice=='cms_pages'){ $select_menu=' id="current"'; }

		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=cms_pages&amp;database='.$_SESSION['tree_prefix'];
		}
		else{
			$path_tmp=CMS_ROOTPATH.'cms_pages.php?database='.$_SESSION['tree_prefix'];
		}
		echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Information')."</a></li>\n";
	}
	*/

	// *** Menu: Family tree ***
	if ($bot_visit AND $humo_option["searchengine_cms_only"]=='y'){

		// *** Show CMS link for search bots ***
		// *** Menu genealogy (for CMS pages) ***
		$cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
		if($cms_qry->rowCount() > 0) {
			$select_menu=''; if ($menu_choice=='cms_pages'){ $select_menu=' id="current"'; }

			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=cms_pages&amp;database='.$_SESSION['tree_prefix'];
			}
			else{
				$path_tmp=CMS_ROOTPATH.'cms_pages.php?database='.$_SESSION['tree_prefix'];
			}
			echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Information')."</a></li>\n";
		}

	}
	else{
		$select_top='';
		if ($menu_choice=='tree_index'){ $select_top=' id="current_top"'; }
		if ($menu_choice=='cms_pages'){ $select_top=' id="current_top"'; }
		if ($menu_choice=='persons'){ $select_top=' id="current_top"'; }
		if ($menu_choice=='names'){ $select_top=' id="current_top"'; }
		if ($menu_choice=='sources'){ $select_top=' id="current_top"'; }
		if ($menu_choice=='places'){ $select_top=' id="current_top"'; }
		if ($menu_choice=='places_families'){ $select_top=' id="current_top"'; }
		if ($menu_choice=='pictures'){ $select_top=' id="current_top"'; }
		if ($menu_choice=='addresses'){ $select_top=' id="current_top"'; }

		echo '<li>';
		echo '<div class="'.$rtlmarker.'sddm">';

			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=tree_index&amp;database='.$_SESSION['tree_prefix'].'&amp;reset=1';
			}
			elseif ($humo_option["url_rewrite"]=="j"){
				$path_tmp='tree_index/'.$_SESSION['tree_prefix']."/";
			}
			else{
				$path_tmp=CMS_ROOTPATH.'tree_index.php?database='.$_SESSION['tree_prefix'].'&amp;reset=1';
			}

			echo '<a href="'.$path_tmp.'"';
			echo ' onmouseover="mopen(event,\'mft\',\'?\',\'?\')"';
			echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Family tree').'&nbsp;<img src="'.CMS_ROOTPATH.'images/button3.png" height= "13" style="border:none;" alt="pull_down"></a>';

			echo '<div id="mft" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
				echo '<ul class="humo_menu_item2">';

					$select_menu=''; if ($menu_choice=='tree_index'){ $select_menu=' id="current"'; }
					echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Family tree index').'</a></li>';

					// *** Menu genealogy (for CMS pages) ***
					$cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
					if($cms_qry->rowCount() > 0) {
						$select_menu=''; if ($menu_choice=='cms_pages'){ $select_menu=' id="current"'; }

						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=cms_pages&amp;database='.$_SESSION['tree_prefix'];
						}
						else{
							$path_tmp=CMS_ROOTPATH.'cms_pages.php?database='.$_SESSION['tree_prefix'];
						}
						echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Information')."</a></li>\n";
					}

					// *** Persons ***
					if ($user['group_menu_persons']=="j"){
						$select_menu=''; if ($menu_choice=='persons'){ $select_menu=' id="current"'; }
						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=list&amp;database='.$_SESSION['tree_prefix'].'&amp;reset=1';
						}
						else{
							$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'].'&amp;reset=1';
						}
						echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Persons').'</a></li>';
					}
					// *** Names ***
					if ($user['group_menu_names']=="j"){
						$select_menu=''; if ($menu_choice=='names'){ $select_menu=' id="current"'; }
						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=list_names&amp;database='.$_SESSION['tree_prefix'];
						}
						elseif ($humo_option["url_rewrite"]=="j"){
							$path_tmp= 'list_names/'.$_SESSION['tree_prefix'];
						}
						else{
							$path_tmp=CMS_ROOTPATH.'list_names.php?database='.$_SESSION['tree_prefix'];
						}
						echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Names')."</a></li>\n";
					}
					if ($user['group_menu_places']=="j"){
						$select_menu=''; if ($menu_choice=='places'){ $select_menu=' id="current"'; }
						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;database='.$_SESSION['tree_prefix'].'&amp;task=list&amp;index_list=places&amp;reset=1';
						}
						else{
							$path_tmp=CMS_ROOTPATH.'list.php?database='.$_SESSION['tree_prefix'].'&amp;index_list=places&amp;reset=1';
						}
						echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Places (by persons)')."</a></li>\n";

						$select_menu=''; if ($menu_choice=='places_families'){ $select_menu=' id="current"'; }
						//if (CMS_SPECIFIC=='Joomla'){
						//	$path_tmp='index.php?option=com_humo-gen&amp;database='.$_SESSION['tree_prefix'].'&amp;task=list&amp;index_list=places&amp;reset=1';
						//}
						//else{
							$path_tmp=CMS_ROOTPATH.'list_places_families.php?database='.$_SESSION['tree_prefix'].'&amp;index_list=places&amp;reset=1';
						//}
						echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Places (by families)')."</a></li>\n";
					}

					if ($user['group_photobook']=='j'){
						$select_menu=''; if ($menu_choice=='pictures'){ $select_menu=' id="current"'; }
						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=photoalbum&amp;database='.$_SESSION['tree_prefix'];
						}
						else{
							$path_tmp=CMS_ROOTPATH.'photoalbum.php?database='.$_SESSION['tree_prefix'];
						}
						echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Photobook')."</a></li>\n";
					}

					//if ($user['group_sources']=='j'){
					if ($user['group_sources']=='j' AND $tree_prefix_quoted!='' AND $tree_prefix_quoted!='EMPTY'){
						// *** Check if there are sources in the database ***
						//$source_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."sources");
						$source_qry=$dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='".$_SESSION['tree_id']."'");
						@$sourceDb=$source_qry->rowCount();
						if ($sourceDb>0){
							$select_menu=''; if ($menu_choice=='sources'){ $select_menu=' id="current"'; }
							if (CMS_SPECIFIC=='Joomla'){
								$path_tmp='index.php?option=com_humo-gen&amp;task=sources&amp;database='.$_SESSION['tree_prefix'];
							}
							else{
								$path_tmp=CMS_ROOTPATH.'sources.php?database='.$_SESSION['tree_prefix'];
							}
							echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Sources')."</a></li>\n";
						}
					}

					//if ($user['group_addresses']=='j'){
					if ($user['group_addresses']=='j' AND $tree_prefix_quoted!='' AND $tree_prefix_quoted!='EMPTY'){
						// *** Check for addresses in the database ***
						//$address_qry=$dbh->query("SELECT * FROM ".$tree_prefix_quoted."addresses WHERE address_gedcomnr LIKE '_%'");
						$address_qry=$dbh->query("SELECT * FROM humo_addresses
							WHERE address_tree_id='".$tree_id."' AND address_gedcomnr LIKE '_%'");
						@$addressDb=$address_qry->rowCount();
						if ($addressDb>0){
							$select_menu=''; if ($menu_choice=='addresses'){ $select_menu=' id="current"'; }
							if (CMS_SPECIFIC=='Joomla'){
								$path_tmp='index.php?option=com_humo-gen&amp;task=addresses&amp;database='.$_SESSION['tree_prefix'];
							}
							else{
								$path_tmp=CMS_ROOTPATH.'addresses.php?database='.$_SESSION['tree_prefix'];
							}
							echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Addresses')."</a></li>\n";
						}
					}

				echo '</ul>';
			echo '</div>';

		echo '</div>';
		echo '</li>';
	} // *** End of bot check ***

	// *** Menu: Tools menu ***
	if ($bot_visit AND $humo_option["searchengine_cms_only"]=='y'){
		//
	}
	else{

	// make sure at least one of the submenus is activated, otherwise don't show TOOLS menu
	/*
	if($user["group_birthday_list"]=='j' OR $user["group_showstatistics"]=='j' OR $user["group_relcalc"]=='j' OR
	($user["group_googlemaps"]=='j' AND mysql_num_rows( mysql_query("SHOW TABLES LIKE 'humo_location'", $db))) OR 
	($user["group_contact"]=='j'AND $dataDb->tree_owner AND $dataDb->tree_email ) OR 
	$user["group_latestchanges"]=='j' ) {
	*/
	if($user["group_birthday_list"]=='j' OR $user["group_showstatistics"]=='j' OR $user["group_relcalc"]=='j' OR
	($user["group_googlemaps"]=='j' AND $dbh->query("SHOW TABLES LIKE 'humo_location'")->rowCount() > 0) OR 
	($user["group_contact"]=='j'AND $dataDb->tree_owner AND $dataDb->tree_email ) OR 
	$user["group_latestchanges"]=='j' ) {
		// *** Javascript pull-down menu ***
		echo '<li>';
		echo '<div class="'.$rtlmarker.'sddm">';

			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen';
			}
			else{
				$path_tmp=CMS_ROOTPATH.'index.php';
			}

			$select_top='';
			if ($menu_choice=='birthday'){ $select_top=' id="current_top"'; }
			if ($menu_choice=='statistics'){ $select_top=' id="current_top"'; }
			if ($menu_choice=='relations'){ $select_top=' id="current_top"'; }
			if ($menu_choice=='maps'){ $select_top=' id="current_top"'; }
			if ($menu_choice=='mailform'){ $select_top=' id="current_top"'; }
			if ($menu_choice=='latest_changes'){ $select_top=' id="current_top"'; }

			echo '<a href="'.$path_tmp.'"';
			echo ' onmouseover="mopen(event,\'m1x\',\'?\',\'?\')"';

			echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Tools').'&nbsp;<img src="'.CMS_ROOTPATH.'images/button3.png" height= "13" style="border:none;" alt="pull_down"></a>';

			echo '<div id="m1x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
				echo '<ul class="humo_menu_item2">';

				if ($user["group_birthday_list"]=='j' AND file_exists(CMS_ROOTPATH.'birthday_list.php')){
					$select_menu=''; if ($menu_choice=='birthday'){ $select_menu=' id="current"'; }
					if (CMS_SPECIFIC=='Joomla'){
						$path_tmp='index.php?option=com_humo-gen&amp;task=birthday_list';
					}
					else{
						$path_tmp=CMS_ROOTPATH.'birthday_list.php';
					}
					echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Anniversary list').'</a></li>';
				}
				if ($user["group_showstatistics"]=='j' AND file_exists(CMS_ROOTPATH.'statistics.php')){
					$select_menu=''; if ($menu_choice=='statistics'){ $select_menu=' id="current"'; }
					if (CMS_SPECIFIC=='Joomla'){
						$path_tmp='index.php?option=com_humo-gen&amp;task=statistics';
					}
					else{
						$path_tmp=CMS_ROOTPATH.'statistics.php';
					}
					echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Statistics').'</a></li>';
				}
				if ($user["group_relcalc"]=='j' AND file_exists(CMS_ROOTPATH.'relations.php')){
					$select_menu=''; if ($menu_choice=='relations'){ $select_menu=' id="current"'; }
					if (CMS_SPECIFIC=='Joomla'){
						$path_tmp='index.php?option=com_humo-gen&amp;task=relations';
					}
					else{
						$path_tmp=CMS_ROOTPATH.'relations.php';
					}
					echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Relationship calculator')."</a></li>\n";
				}
				if ($user["group_googlemaps"]=='j' AND file_exists(CMS_ROOTPATH.'maps.php')){
					if(!$bot_visit AND $dbh->query("SHOW TABLES LIKE 'humo_location'")->rowCount()>0) {
						$select_menu=''; if ($menu_choice=='maps'){ $select_menu=' id="current"'; }
						if (CMS_SPECIFIC=='Joomla'){
							$path_tmp='index.php?option=com_humo-gen&amp;task=maps';
						}
						else{
							$path_tmp=CMS_ROOTPATH.'maps.php';
						}
						echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Google maps')."</a></li>\n";
					}
				}
				if ($user["group_contact"]=='j' AND file_exists(CMS_ROOTPATH.'mailform.php')){
					// *** Show link to contact form ***
					if (@$dataDb->tree_owner){
						if ($dataDb->tree_email){
							$select_menu=''; if ($menu_choice=='mailform'){ $select_menu=' id="current"'; }
							if (CMS_SPECIFIC=='Joomla'){
								$path_tmp='index.php?option=com_humo-gen&amp;task=mailform';
							}
							else{
							$path_tmp=CMS_ROOTPATH.'mailform.php';
							}
							echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Contact')."</a></li>\n";
						}
					}
				}
				if ($user["group_latestchanges"]=='j' AND file_exists(CMS_ROOTPATH.'latest_changes.php')){
					// *** Latest changes ***
					$select_menu=''; if ($menu_choice=='latest_changes'){ $select_menu=' id="current"'; }
					if (CMS_SPECIFIC=='Joomla'){
						$path_tmp='index.php?option=com_humo-gen&amp;task=latest_changes';
					}
					else{
						$path_tmp=CMS_ROOTPATH.'latest_changes.php';
					}
					echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Latest changes').'</a></li>';
				}
				echo '</ul>';
			echo '</div>';

		echo '</div>';
		echo '</li>';
	} // *** End of menu check ***
	} // *** End of bot check

	if (!$bot_visit){
		// *** User settings ***
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=user_settings';
		} else{
			$path_tmp=CMS_ROOTPATH.'user_settings.php';
		}
		$select_menu=''; if ($menu_choice=='settings'){ $select_menu=' id="current"'; }
		print '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Settings')."</a></li>\n";
	}

	$select_top='';
	if ($menu_choice=='help'){ $select_top=' id="current_top"'; }
	if ($menu_choice=='info'){ $select_top=' id="current_top"'; }
	if ($menu_choice=='credits'){ $select_top=' id="current_top"'; }
	if ($menu_choice=='info_cookies'){ $select_top=' id="current_top"'; }
	echo '<li>';
	echo '<div class="'.$rtlmarker.'sddm">';

		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=help';
		}
		else{
			$path_tmp=CMS_ROOTPATH.'help.php';
		}
		echo '<a href="'.$path_tmp.'"';
		echo ' onmouseover="mopen(event,\'m2x\',\'?\',\'?\')"';
		echo ' onmouseout="mclosetime()"'.$select_top.'>'.__('Help').'&nbsp;<img src="'.CMS_ROOTPATH.'images/button3.png" height= "13" style="border:none;" alt="pull_down"></a>';

		echo '<div id="m2x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
			echo '<ul class="humo_menu_item2">';
				$select_menu=''; if ($menu_choice=='help'){ $select_menu=' id="current"'; }
				echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Help').'</a></li>';

				$select_menu=''; if ($menu_choice=='info'){ $select_menu=' id="current"'; }
				if (CMS_SPECIFIC=='Joomla'){
					$path_tmp='index.php?option=com_humo-gen&amp;task=info';
				}
				else{
					$path_tmp=CMS_ROOTPATH.'info.php';
				}
				echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('HuMo-gen info').'</a></li>';

				$select_menu=''; if ($menu_choice=='credits'){ $select_menu=' id="current"'; }
				if (CMS_SPECIFIC=='Joomla'){
					$path_tmp='index.php?option=com_humo-gen&amp;task=credits';
				}
				else{
					$path_tmp=CMS_ROOTPATH.'credits.php';
				}
				echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('HuMo-gen credits').'</a></li>';

				if (!$bot_visit){
					$select_menu=''; if ($menu_choice=='info_cookies'){ $select_menu=' id="current"'; }
					if (CMS_SPECIFIC=='Joomla'){
						$path_tmp='index.php?option=com_humo-gen&amp;task=info_cookies';
					}
					else{
						$path_tmp=CMS_ROOTPATH.'info_cookies.php';
					}
					echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('HuMo-gen cookies').'</a></li>';
				}

			echo '</ul>';
		echo '</div>';

	echo '</div>';
	echo '</li>';

	if ($user['group_menu_login']=='j'){
		// *** Login - Logoff ***
		if (!$user["user_name"]){
			$select_menu=''; if ($menu_choice=='login'){ $select_menu=' id="current"'; }
			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=login';
			} else{
				$path_tmp=CMS_ROOTPATH.'login.php';
			}
			print '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Login')."</a></li>\n";
		} else{
			// *** Log off ***
			if (CMS_SPECIFIC=='Joomla'){
				$path_tmp='index.php?option=com_humo-gen&amp;task=index&amp;log_off=1';
			} else{
				$path_tmp=CMS_ROOTPATH.'index.php?log_off=1';
			}
			print '<li><a href="'.$path_tmp.'">'.__('Logoff')." [".$_SESSION["user_name"]."]</a></li>\n";
		}
	}

	// *** Link to administration ***
	//if  ($user['group_editor']=='j' OR $user['group_admin']=='j') {
	if  ($user['group_edit_trees'] OR $user['group_admin']=='j') {
		$select_menu='';
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=admin';
		}
		else{
			$path_tmp=CMS_ROOTPATH_ADMIN.'index.php';
		}
		echo '<li'.$select_menu.'><a href="'.$path_tmp.'" target="_blank">'.__('Admin').'</a></li>';
	}

	// *** Link to registration form ***
	if  (!$user["user_name"] AND $humo_option["visitor_registration"]=='y') {
		$select_menu=''; if ($menu_choice=='register'){ $select_menu=' id="current"'; }
		if (CMS_SPECIFIC=='Joomla'){
			$path_tmp='index.php?option=com_humo-gen&amp;task=register';
		}
		else{
			$path_tmp=CMS_ROOTPATH.'register.php';
		}
		echo '<li'.$select_menu.'><a href="'.$path_tmp.'">'.__('Register').'</a></li>';
	}

	// *** Country flags ***
	if (!$bot_visit){
		echo '<li>';
		echo '<div class="'.$rtlmarker.'sddm">';
			echo '<a href="index.php?option=com_humo-gen"';
			echo ' onmouseover="mopen(event,\'m4x\',\'?\',\'?\')"';
			$select_top='';
			//echo ' onmouseout="mclosetime()"'.$select_top.'>'.'<img src="'.CMS_ROOTPATH.'languages/'.$selected_language.'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none; height:14px"> '.$language["name"].'&nbsp;<img src="'.CMS_ROOTPATH.'images/button3.png" height= "13" style="border:none;" alt="pull_down"></a>';

			echo ' onmouseout="mclosetime()"'.$select_top.'>'.'<img src="'.CMS_ROOTPATH.'languages/'.$selected_language.'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none; height:14px"> &nbsp;<img src="'.CMS_ROOTPATH.'images/button3.png" height= "13" style="border:none;" alt="pull_down"></a>';

			//echo '<div id="m4x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
			echo '<div id="m4x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()" style="width:250px;">';
				echo '<ul class="humo_menu_item2">';
					for ($i=0; $i<count($language_file); $i++){
						// *** Get language name ***
						if ($language_file[$i] != $selected_language) {
							include(CMS_ROOTPATH.'languages/'.$language_file[$i].'/language_data.php');
							//echo '<li><a href="'.CMS_ROOTPATH.'index.php?language='.$language_file[$i].'">';
							echo '<li style="float:left; width:124px;">';
								echo '<a href="'.CMS_ROOTPATH.'index.php?language='.$language_file[$i].'">';
								echo '<img src="'.CMS_ROOTPATH.'languages/'.$language_file[$i].'/flag.gif" title="'.$language["name"].'" alt="'.$language["name"].'" style="border:none;"> ';
								echo $language["name"];
								echo '</a>';
							echo '</li>';
						}
					}

					// *** Odd number of languages in menu ***
					/*
					if ($i % 2 == 0){
						echo '<li style="float:left; width:124px;">';
							echo '<a href="'.CMS_ROOTPATH.'index.php" style="height:18px;">&nbsp;<br></a>';
						echo '</li>';
					}
					*/

				echo '</ul>';
			echo '</div>';
		echo '</div>';
		echo '</li>';
		include('languages/'.$selected_language.'/language_data.php');
	}

echo '</ul>';

echo '</div>'; // End of humo_menu

echo '</div>';   // End of top_menu

if($language["dir"]=="rtl") {
	print '<div id="rtlcontent">';
}
else {
	print '<div id="content">';
}

?>