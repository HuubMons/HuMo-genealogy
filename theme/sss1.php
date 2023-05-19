<?php
// *** Stylesheet Switcher 1 ***

$folder = opendir(__DIR__ . '/');
while (false !== ($file = readdir($folder))) {
	if (substr($file, -4, 4) == '.css') {
		$themes[] = $file;
	}
}
closedir($folder);

for ($i = 0; $i < count($themes); $i++) {
	$theme = $themes[$i];
	$theme = str_replace(".css", "", $theme);
	echo '<link href="theme/' . $theme . '.css" rel="alternate stylesheet" type="text/css" media="screen" title="' . $theme . '">';
}

// *** Added by Huub for default skin! Edit: tb var default_skin separates logic and output ***
$default_skin = (!empty($humo_option['default_skin'])) ? $humo_option['default_skin'] : "";

echo '<script type="text/javascript">';
echo '  var defaultskin="' . $default_skin . '";';
echo '</script>';

// *** Stylesheetswitcher 2 ***
echo '<script src="theme/styleswitch.js" type="text/javascript"></script>';
