<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
	exit;
}

echo '<h1 class="center">' . __('Extensions') . '</h1>';

// *** Read theme's ***
$folder = opendir(__DIR__ . '/../../theme/');
while (false !== ($file = readdir($folder))) {
	if (substr($file, -4, 4) == '.css') {
		$theme_folder[] = $file;
	}
}
closedir($folder);

if (isset($_POST['save_option'])) {
	// *** Update settings / Language choice ***
	$language_total = '';
	for ($i = 0; $i < count($language_file); $i++) {
		// *** Get language name ***
		if ($language_file[$i] == $humo_option["default_language"] or  $language_file[$i] == $humo_option["default_language_admin"]) {
			// *** Don't hide default languages ***
		} else {
			if (!isset($_POST["$language_file[$i]"])) {
				if ($language_total != '') {
					$language_total .= ';';
				}
				$language_total .= $language_file[$i];
			}
		}
	}
	$result = $db_functions->update_settings('hide_languages', $language_total);

	// *** Update settings / Theme choice ***
	$theme_total = '';
	for ($i = 0; $i < count($theme_folder); $i++) {
		$theme = $theme_folder[$i];
		$theme = str_replace(".css", "", $theme);

		if (!isset($_POST["$theme"])) {
			if ($theme_total != '') {
				$theme_total .= ';';
			}
			$theme_total .= $theme;
		}
	}
	$result = $db_functions->update_settings('hide_themes', $theme_total);
}

echo '<form method="post" action="/admin/index.php">';
echo '<input type="hidden" name="page" value="' . $page . '">';
echo '<table class="humo standard" border="1">';
echo '<tr class="table_header"><th colspan="2">' . __('Extensions') . '</th></tr>';
echo '<tr class="table_header"><th colspan="2">' . __('Languages') . ' <input type="Submit" name="save_option" value="' . __('Change') . '"></th></tr>';
echo '<tr><td>' . __('Show/ hide languages') . '</td><td>';
$hide_languages_array = explode(";", $humo_option["hide_languages"]);

// *** Language choice ***
for ($i = 0; $i < count($language_file); $i++) {
	// *** Get language name ***
	include __DIR__ . '/../../languages/' . $language_file[$i] . '/language_data.php';

	$checked = ' checked';
	if (in_array($language_file[$i], $hide_languages_array)) $checked = '';

	$disabled = '';
	if ($language_file[$i] == $humo_option["default_language"]) {
		$disabled = ' disabled';
		$checked = ' checked';
	}
	if ($language_file[$i] == $humo_option["default_language_admin"]) {
		$disabled = ' disabled';
		$checked = ' checked';
	}

	echo '<input type="checkbox" id="language" value="y" name="' . $language_file[$i] . '" ' . $checked . $disabled . '>';
	echo ' <img src="../languages/' . $language_file[$i] . '/flag.gif" title="' . $language["name"] . '" alt="' . $language["name"] . '" style="border:none;"> ';
	echo $language["name"] . '<br>';
}
echo '</td></tr>';

echo '<tr class="table_header"><th colspan="2">' . __('Themes') . ' <input type="Submit" name="save_option" value="' . __('Change') . '"></th></tr>';
echo '<tr><td>' . __('Show/ hide theme\'s') . '</td><td>';
$hide_themes_array = explode(";", $humo_option["hide_themes"]);

for ($i = 0; $i < count($theme_folder); $i++) {
	$theme = $theme_folder[$i];
	$theme = str_replace(".css", "", $theme);
	$checked = ' checked';
	if (in_array($theme, $hide_themes_array)) $checked = '';
	echo '<input type="checkbox" id="themes" value="y" name="' . $theme . '" ' . $checked . '>';
	echo ' ' . $theme . '<br>';
}
echo '</td></tr>';

//echo '<tr class="table_header"><th colspan="2">'.__('Save settings').' <input type="Submit" name="save_option" value="'.__('Change').'"></th></tr>';

echo '</table>';
echo '</form>';
