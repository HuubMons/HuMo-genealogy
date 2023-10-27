<?php
// *** Stylesheet Switcher 1 ***

$folder = opendir('styles/');
while (false !== ($file = readdir($folder))) {
    if (substr($file, -4, 4) == '.css') {
        $theme_folder[] = $file;
    }
}
closedir($folder);

for ($i = 0; $i < count($theme_folder); $i++) {
    $theme = $theme_folder[$i];
    $theme = str_replace(".css", "", $theme);
    echo '<link href="styles/' . $theme . '.css" rel="alternate stylesheet" type="text/css" media="screen" title="' . $theme . '">';
}

// *** Added by Huub for default skin! Edit: tb var default_skin separates logic and output ***
$default_skin = (!empty($humo_option['default_skin'])) ? $humo_option['default_skin'] : "";
echo '<script>';
echo '  var defaultskin="' . $default_skin . '";';
echo '</script>';

// *** Stylesheetswitcher 2 ***
echo '<script src="styles/styleswitch.js"></script>';
