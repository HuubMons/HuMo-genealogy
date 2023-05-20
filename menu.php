<?php
// *** Show logo or name of website ***
$logo = $humo_option["database_name"]; // default

if (is_file('media/logo.png'))
	$logo = '<img src="media/logo.png">';
elseif (is_file('media/logo.jpg'))
	$logo = '<img src="media/logo.jpg">';


print '<div id="top_menu">';

$rtlmark = 'ltr';
if ($language["dir"] == "rtl") {
	$rtlmark = 'rtl';
}
echo '<div id="top" style="direction:' . $rtlmark . ';">';
echo '<div style="direction:ltr;">';

echo '<span id="top_website_name">';
echo '<a href="' . $humo_option["homepage"] . '">' . $logo . '</a>';
echo '</span>';

// *** Select family tree ***
if (!$bot_visit) {
	$sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
	$tree_search_result2 = $dbh->query($sql);
	$num_rows = $tree_search_result2->rowCount();
	if ($num_rows > 1) {

		echo ' <form method="POST" action="/tree_index.php" style="display : inline;" id="top_tree_select">';
		echo __('Family tree') . ': ';
		//echo '<select size="1" name="database" onChange="this.form.submit();" style="width: 150px; height:20px;">';
		echo '<select size="1" name="tree_id" onChange="this.form.submit();" style="width: 150px; height:20px;">';
		echo '<option value="">' . __('Select a family tree:') . '</option>';
		$count = 0;
		while ($tree_searchDb = $tree_search_result2->fetch(PDO::FETCH_OBJ)) {
			// *** Check if family tree is shown or hidden for user group ***
			$hide_tree_array2 = explode(";", $user['group_hide_trees']);
			$hide_tree2 = false;
			if (in_array($tree_searchDb->tree_id, $hide_tree_array2)) $hide_tree2 = true;
			if ($hide_tree2 == false) {
				$selected = '';
				if (isset($_SESSION['tree_prefix'])) {
					if ($tree_searchDb->tree_prefix == $_SESSION['tree_prefix']) {
						$selected = ' selected';
					}
				} else {
					if ($count == 0) {
						$_SESSION['tree_prefix'] = $tree_searchDb->tree_prefix;
						$selected = ' selected';
					}
				}
				$treetext = $db_tree_text->show_tree_text($tree_searchDb->tree_id, $selected_language);
				//echo '<option value="'.$tree_searchDb->tree_prefix.'"'.$selected.'>'.@$treetext['name'].'</option>';
				echo '<option value="' . $tree_searchDb->tree_id . '"' . $selected . '>' . @$treetext['name'] . '</option>';
				$count++;
			}
		}
		echo '</select>';
		echo '</form>';
	}
}
echo '</div>';

// *** This code is used to restore $dataDb reading. Used for picture etc. ***
if (is_string($_SESSION['tree_prefix']))
	$dataDb = $db_functions->get_tree($_SESSION['tree_prefix']);

// *** Show quicksearch field ***
if (!$bot_visit) {
	echo '<form method="post" action="list.php" id="top_quicksearch">';
	echo '<input type="hidden" name="index_list" value="quicksearch">';
	echo '<input type="hidden" name="search_database" value="tree_selected">';
	$quicksearch = '';
	if (isset($_POST['quicksearch'])) {
		//$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
		$quicksearch = safe_text_show($_POST['quicksearch']);
		$_SESSION["save_quicksearch"] = $quicksearch;
	}
	if (isset($_SESSION["save_quicksearch"])) {
		$quicksearch = $_SESSION["save_quicksearch"];
	}
	if ($humo_option['min_search_chars'] == 1) {
		$pattern = "";
		$min_chars = " 1 ";
	} else {
		$pattern = 'pattern=".{' . $humo_option['min_search_chars'] . ',}"';
		$min_chars = " " . $humo_option['min_search_chars'] . " ";
	}
	echo '<input type="text" name="quicksearch" placeholder="' . __('Name') . '" value="' . $quicksearch . '" size="10" ' . $pattern . ' title="' . __('Minimum:') . $min_chars . __('characters') . '">';
	echo ' <input type="submit" value="' . __('Search') . '">';
	echo ' <a href="list.php?adv_search=1&index_list=search"><img src="theme/images/advanced-search.jpg" width="17px"></a>';

	echo "</form>";
}

// *** Favourite list for family pages ***
if (!$bot_visit) {

	//$favorites_array[]='';
	// *** Use session if session is available ***
	if (isset($_SESSION["save_favorites"]) and $_SESSION["save_favorites"]) {
		$favorites_array = $_SESSION["save_favorites"];
	} 

	// *** Add new favorite to list of favourites ***
	if (isset($_POST['favorite'])) {
		// *** Add favourite to session ***
		$favorites_array[] = $_POST['favorite'];
		$_SESSION["save_favorites"] = $favorites_array;

		// *** Add favourite to cookie ***
		$favorite_array2 = explode("|", $_POST['favorite']);
		// *** Combine tree prefix and family number as unique array id, for example: humo_F4 ***
		$i = $favorite_array2['2'] . $favorite_array2['1'];
	}

	// *** Remove favourite from favorite list ***
	if (isset($_POST['favorite_remove'])) {
		// *** Remove favourite from session ***
		if (isset($_SESSION["save_favorites"])) {
			unset($favorites_array);
			foreach ($_SESSION['save_favorites'] as $key => $value) {
				if ($value != $_POST['favorite_remove']) {
					$favorites_array[] = $value;
				}
			}
			$_SESSION["save_favorites"] = $favorites_array;
		}
	}

	// *** Show favorites in selection list ***
	echo ' <form method="POST" action="/family.php' . '" style="display : inline;" id="top_favorites_select">';
	echo '<img src="theme/images/favorite_blue.png"> ';
	echo '<select size=1 name="humo_favorite_id" onChange="this.form.submit();" style="width: 115px; height:20px;">';
	echo '<option value="">' . __('Favourites list:') . '</option>';

	if (isset($_SESSION["save_favorites"])) {
		sort($_SESSION['save_favorites']);
		foreach ($_SESSION['save_favorites'] as $key => $value) {
			if (is_string($value) and $value) {
				$favorite_array2 = explode("|", $value);
				// *** Show only persons in selected family tree ***
				if ($_SESSION['tree_prefix'] == $favorite_array2['2']) {
					// *** Check if family tree is still the same family tree ***
					$person_manDb = $db_functions->get_person($favorite_array2['3']);

					// *** Proces man using a class ***
					$test_favorite = $db_functions->get_person($favorite_array2['3']);
					if ($test_favorite)
						echo '<option value="' . $favorite_array2['1'] . '|' . $favorite_array2['3'] . '">' . $favorite_array2['0'] . '</option>';
				}
			}
		}
	}
	echo '</select>';
	echo '</form>';
}

echo '</div>'; // End of Top

// *** Menu ***
echo '<div id="humo_menu">';
echo '<ul class="humo_menu_item">';
echo '<li' . ($menu_choice == 'main_index' ? ' id="current"' : '') . ' class="mobile_hidden"><a href="index.php?tree_id=' . $tree_id . '"><img src="theme/images/menu_mobile.png" width="18" class="mobile_icon"> ' . __('Home') . "</a></li>";

// *** Mobile menu ***
$select_top = '';
if (in_array($menu_choice, ['help', 'info', 'info_cookies'])) {
	$select_top = ' id="current_top"';
}

echo '<li class="mobile_visible">';
echo '<div class="' . $rtlmarker . 'sddm">';
echo '<a href="index.php?tree_id=' . $tree_id . '"';
echo ' onmouseover="mopen(event,\'m0x\',\'?\',\'?\')"';
echo ' onmouseout="mclosetime()"' . $select_top . '><img src="theme/images/menu_mobile.png" width="18"></a>';
echo '<div id="m0x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
echo '<ul class="humo_menu_item2">';

echo '<li' . $select_menu . '><a href="index.php?tree_id=' . $tree_id . '">' . __('Home') . "</a></li>";

if ($user['group_menu_login'] == 'j') {
	if (!$user["user_name"]) {
		echo '<li' . ($menu_choice == 'login' ? ' id="current"' : '') . '><a href="login.php">' . __('Login') . "</a></li>";
	} else {

		if ($user['group_edit_trees'] or $user['group_admin'] == 'j') {
			echo '<li><a href="admin/index.php" target="_blank">' . __('Admin') . '</a></li>';
		}

		echo '<li><a href="index.php?log_off=1">' . __('Logoff') . '</a></li>';
	}
}

if (!$user["user_name"] and $humo_option["visitor_registration"] == 'y') {
	echo '<li' . ($menu_choice == 'register' ? ' id="current"' : '') . '><a href="register.php">' . __('Register') . '</a></li>';
}

echo '<li' . ($menu_choice == 'help' ? ' id="current"' : '') . '><a href="help.php">' . __('Help') . '</a></li>';
echo '<li' . ($menu_choice == 'info' ? ' id="current"' : '') . '><a href="info.php">';
printf(__('%s info'), 'HuMo-genealogy');
echo '</a></li>';

if (!$bot_visit) {
	echo '<li' . ($menu_choice == 'info_cookies' ? ' id="current"' : '') . '><a href="cookies.php">';
	printf(__('%s cookies'), 'HuMo-genealogy');
	echo '</a></li>';
}

echo '</ul>';
echo '</div>';

echo '</div>';
echo '</li>';


// *** Menu genealogy (for CMS pages) ***
if ($user['group_menu_cms'] == 'y') {
	$cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
	if ($cms_qry->rowCount() > 0) {
		echo '<li' . ($menu_choice == 'cms_pages' ? ' id="current"' : '') . '><a href="cms_pages.php"><img src="theme/images/reports.gif" class="mobile_hidden"><span class="mobile_hidden"> </span>' . __('Information') . "</a></li>";
	}
}

// *** Menu: Family tree ***
if ($bot_visit and $humo_option["searchengine_cms_only"] == 'y') {

	// *** Show CMS link for search bots ***
	// *** Menu genealogy (for CMS pages) ***
	$cms_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' AND page_menu_id!='9999'");
	if ($cms_qry->rowCount() > 0) {
		echo '<li' . ($menu_choice == 'cms_pages' ? ' id="current"' : '') . '><a href="cms_pages.php?tree_id=' . $tree_id . '">' . __('Information') . "</a></li>";
	}
} else {
	$select_top = '';
	if (in_array($menu_choice, ['tree_index', 'persons', 'names', 'sources', 'places', 'places_families', 'pictures', 'addresses'])) {
		$select_top = ' id="current_top"';
	}

	echo '<li>';
	echo '<div class="' . $rtlmarker . 'sddm">';
	echo '<a href="tree_index.php?tree_id=' . $tree_id . '"';
	echo ' onmouseover="mopen(event,\'mft\',\'?\',\'?\')"';
	echo ' onmouseout="mclosetime()"' . $select_top . '><img src="theme/images/family_tree.png" class="mobile_hidden"><span class="mobile_hidden"> </span>' . __('Family tree') . '</a>';
	echo '<div id="mft" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
	echo '<ul class="humo_menu_item2">';

	echo '<li' . ($menu_choice == 'tree_index' ? ' id="current"' : '') . '><a href="tree_index.php?tree_id=' . $tree_id . '">' . __('Family tree index') . '</a></li>';
	if ($user['group_menu_persons'] == "j") {
		echo '<li' . ($menu_choice == 'persons' ? ' id="current"' : '') . '><a href="list.php?tree_id=' . $tree_id . '&amp;reset=1">' . __('Persons') . '</a></li>';
	}
	if ($user['group_menu_names'] == "j") {
		echo '<li' . ($menu_choice == 'names' ? ' id="current"' : '') . '><a href="list_names.php?tree_id=' . $tree_id . '">' . __('Names') . "</a></li>";
	}
	// *** Places ***
	if ($user['group_menu_places'] == "j") {
		echo '<li' . ($menu_choice == 'places' ? ' id="current"' : '') . '><a href="list.php?tree_id=' . $tree_id . '&amp;index_list=places&amp;reset=1">' . __('Places (by persons)') . "</a></li>";
		echo '<li' . ($menu_choice == 'places_families' ? ' id="current"' : '') . '><a href="list_places_families.php?tree_id=' . $tree_id . '&amp;index_list=places&amp;reset=1">' . __('Places (by families)') . "</a></li>";
	}
	if ($user['group_photobook'] == 'j') {
		echo '<li' . ($menu_choice == 'pictures' ? ' id="current"' : '') . '><a href="photoalbum.php?tree_id=' . $tree_id . '">' . __('Photobook') . "</a></li>";
	}
	if ($user['group_sources'] == 'j' and $tree_prefix_quoted != '' and $tree_prefix_quoted != 'EMPTY') {
		$source_qry = $dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'");
		@$sourceDb = $source_qry->rowCount();
		if ($sourceDb > 0) {
			echo '<li' . ($menu_choice == 'sources' ? ' id="current"' : '') . '><a href="sources.php?tree_id=' . $tree_id . '">' . __('Sources') . "</a></li>";
		}
	}
	if ($user['group_addresses'] == 'j' and $tree_prefix_quoted != '' and $tree_prefix_quoted != 'EMPTY') {
		$address_qry = $dbh->query("SELECT * FROM humo_addresses
							WHERE address_tree_id='" . $tree_id . "' AND address_shared='1'");
		@$addressDb = $address_qry->rowCount();
		if ($addressDb > 0) {
			echo '<li' . ($menu_choice == 'addresses' ? ' id="current"' : '') . '><a href="addresses.php?tree_id=' . $tree_id . '">' . __('Addresses') . "</a></li>";
		}
	}

	echo '</ul>';
	echo '</div>';

	echo '</div>';
	echo '</li>';
} // *** End of bot check ***

// *** Menu: Tools menu ***
if ($bot_visit and $humo_option["searchengine_cms_only"] == 'y') {
	//
} else {

	// make sure at least one of the submenus is activated, otherwise don't show TOOLS menu
	//	AND $dbh->query("SELECT * FROM humo_settings WHERE setting_variable ='geo_trees'
	//		AND setting_value LIKE '%@".$tree_id.";%' ")->rowCount() > 0)
	if (
		$user["group_birthday_list"] == 'j' or $user["group_showstatistics"] == 'j' or $user["group_relcalc"] == 'j'
		or ($user["group_googlemaps"] == 'j' and $dbh->query("SHOW TABLES LIKE 'humo_location'")->rowCount() > 0)
		or ($user["group_contact"] == 'j' and $dataDb->tree_owner and $dataDb->tree_email)
		or $user["group_latestchanges"] == 'j'
	) {
		// *** Javascript pull-down menu ***
		echo '<li>';
		echo '<div class="' . $rtlmarker . 'sddm">';

		$select_top = '';
		if (in_array($menu_choice, ['birthday', 'statistics', 'relations', 'maps', 'mailform', 'latest_changes'])) {
			$select_top = ' id="current_top"';
		}

		echo '<a href="index.php"';
		echo ' onmouseover="mopen(event,\'m1x\',\'?\',\'?\')"';
		echo ' onmouseout="mclosetime()"' . $select_top . '><img src="theme/images/outline.gif" class="mobile_hidden"><span class="mobile_hidden"> </span>' . __('Tools') . '</a>';
		echo '<div id="m1x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
		echo '<ul class="humo_menu_item2">';

		if ($user["group_birthday_list"] == 'j') {
			echo '<li' . ($menu_choice == 'birthday' ? ' id="current"' : '') . '><a href="birthday_list.php">' . __('Anniversary list') . '</a></li>';
		}
		if ($user["group_showstatistics"] == 'j') {
			echo '<li' . ($menu_choice == 'statistics' ? ' id="current"' : '') . '><a href="statistics.php">' . __('Statistics') . '</a></li>';
		}
		if ($user["group_relcalc"] == 'j') {
			echo '<li' . ($menu_choice == 'relations' ? ' id="current"' : '') . '><a href="relations.php">' . __('Relationship calculator') . "</a></li>";
		}
		if ($user["group_googlemaps"] == 'j') {
			//	AND $dbh->query("SELECT * FROM humo_settings WHERE setting_variable ='geo_trees'
			//		AND setting_value LIKE '%@".$tree_id.";%' ")->rowCount() > 0) {  // this tree has been indexed
			if (!$bot_visit and $dbh->query("SHOW TABLES LIKE 'humo_location'")->rowCount() > 0) {
				echo '<li' . ($menu_choice == 'maps' ? ' id="current"' : '') . '><a href="maps.php">' . __('World map') . "</a></li>";
			}
		}
		if ($user["group_contact"] == 'j') {
			if (@$dataDb->tree_owner) {
				if ($dataDb->tree_email) {
					echo '<li' . ($menu_choice == 'mailform' ? ' id="current"' : '') . '><a href="mailform.php">' . __('Contact') . "</a></li>";
				}
			}
		}
		if ($user["group_latestchanges"] == 'j') {
			echo '<li' . ($menu_choice == 'latest_changes' ? ' id="current"' : '') . '><a href="latest_changes.php">' . __('Latest changes') . '</a></li>';
		}
		echo '</ul>';
		echo '</div>';

		echo '</div>';
		echo '</li>';
	} // *** End of menu check ***
} // *** End of bot check

$select_top = '';
if (in_array($menu_choice, ['help', 'info', 'info_cookies'])) {
	$select_top = ' id="current_top"';
}

echo '<li class="mobile_hidden">';
echo '<div class="' . $rtlmarker . 'sddm">';
echo '<a href="help.php"';
echo ' onmouseover="mopen(event,\'m2x\',\'?\',\'?\')"';
echo ' onmouseout="mclosetime()"' . $select_top . '><img src="theme/images/help.png" width="15"> ' . __('Help') . '</a>';

echo '<div id="m2x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
echo '<ul class="humo_menu_item2">';

echo '<li' . ($menu_choice == 'help' ? ' id="current"' : '') . '><a href="help.php">' . __('Help') . '</a></li>';
echo '<li' . ($menu_choice == 'info' ? ' id="current"' : '') . '><a href="info.php">';
printf(__('%s info'), 'HuMo-genealogy');
echo '</a></li>';
if (!$bot_visit) {
	echo '<li' . ($menu_choice == 'info_cookies' ? ' id="current"' : '') . '><a href="cookies.php">';
	printf(__('%s cookies'), 'HuMo-genealogy');
	echo '</a></li>';
}

echo '</ul>';
echo '</div>';

echo '</div>';
echo '</li>';

//if ($user['group_menu_login']=='j'){
// *** Only show login/ register if user isn't logged in ***
if ($user['group_menu_login'] == 'j' and !$user["user_name"]) {
	// *** Javascript pull-down menu ***
	echo '<li class="mobile_hidden">';
	echo '<div class="' . $rtlmarker . 'sddm">';

	$select_top = '';
	if (in_array($menu_choice, ['login', 'register'])) {
		$select_top = ' id="current_top"';
	}

	echo '<a href="login.php"';
	echo ' onmouseover="mopen(event,\'m6x\',\'?\',\'?\')"';
	echo ' onmouseout="mclosetime()"' . $select_top . '><img src="theme/images/man.gif" width="15"> ' . __('Login') . '</a>';
	echo '<div id="m6x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
	echo '<ul class="humo_menu_item2">';

	echo '<li' . ($menu_choice == 'login' ? ' id="current"' : '') . '><a href="login.php">' . __('Login') . '</a></li>';
	if (!$user["user_name"] and $humo_option["visitor_registration"] == 'y') {
		echo '<li' . ($menu_choice == 'register' ? ' id="current"' : '') . ' class="mobile_hidden"><a href="register.php">' . __('Register') . '</a></li>';
	}

	echo '</ul>';
	echo '</div>';

	echo '</div>';
	echo '</li>';
}

// *** Menu: Control menu ***
if (!$bot_visit) {
	// *** Javascript pull-down menu ***
	echo '<li>';
	echo '<div class="' . $rtlmarker . 'sddm">';

	$select_top = '';
	if ($menu_choice == 'settings') {
		$select_top = ' id="current_top"';
	}
	echo '<a href="user_settings.php"';
	echo ' onmouseover="mopen(event,\'m5x\',\'?\',\'?\')"';
	echo ' onmouseout="mclosetime()"' . $select_top . '><img src="theme/images/settings.png" width="15" class="mobile_hidden"><span class="mobile_hidden"> </span>' . __('Control') . '</a>';
	echo '<div id="m5x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
	echo '<ul class="humo_menu_item2">';

	echo '<li' . ($menu_choice == 'user_settings' ? ' id="current"' : '') . '><a href="user_settings.php">' . __('User settings') . '</a></li>';
	if ($user['group_edit_trees'] or $user['group_admin'] == 'j') {
		echo '<li><a href="admin/index.php" target="_blank">' . __('Admin') . '</a></li>';
	}
	if ($user['group_menu_login'] == 'j' and $user["user_name"]) {
		echo '<li><a href="index.php?log_off=1">' . __('Logoff');
		echo ' <span style="color:#0101DF; font-weight:bold;">[' . ucfirst($_SESSION["user_name"]) . ']</span>';
		echo '</a></li>';
	}

	echo '</ul>';
	echo '</div>';
	echo '</div>';
	echo '</li>';
} // *** End of bot check


// *** Country flags ***
if (!$bot_visit) {
	echo '<li>';
	echo '<div class="' . $rtlmarker . 'sddm">';
	echo '<a href="index.php?option=com_humo-gen"';
	echo ' onmouseover="mopen(event,\'m4x\',\'?\',\'?\')"';
	echo ' onmouseout="mclosetime()">' . '<img src="languages/' . $selected_language . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none; height:18px"></a>';
	// *** In gedcom.css special adjustment (width) for m4x! ***
	echo '<div id="m4x" class="sddm_abs" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
	echo '<ul class="humo_menu_item2">';
	$hide_languages_array = explode(";", $humo_option["hide_languages"]);
	for ($i = 0; $i < count($language_file); $i++) {
		// *** Get language name ***
		if ($language_file[$i] != $selected_language and !in_array($language_file[$i], $hide_languages_array)) {
			require_once __DIR__ . '/languages/' . $language_file[$i] . '/language_data.php';
			echo '<li>';
			echo '<a href="index.php?language=' . $language_file[$i] . '">';

			echo '<img src="languages/' . $language_file[$i] . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none;"> ';
			// *** Don't show names of languages in mobile version ***
			echo '<span class="mobile_hidden">' . $language["name"] . '</span>';

			echo '</a>';
			echo '</li>';
		}
	}

	echo '</ul>';
	echo '</div>';
	echo '</div>';
	echo '</li>';
	require_once __DIR__ . '/languages/' . $selected_language . '/language_data.php';
}

echo '</ul>';
echo '</div>';
echo '</div>';

// *** Override margin if slideshow is used ***
if ($menu_choice == 'main_index' and isset($humo_option["slideshow_show"]) and $humo_option["slideshow_show"] == 'y') {
	echo '<style>
	#rtlcontent {
		padding-left:0px;
		padding-right:0px;
	}
	#content {
		padding-left:0px;
		padding-right:0px;
	}
	</style>';
}

if ($language["dir"] == "rtl") {
	echo '<div id="rtlcontent">';
} else {
	echo '<div id="content">';
}
