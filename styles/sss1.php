<?php
// *** Stylesheet Switcher 1 ***

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
	echo '<link href="'.CMS_ROOTPATH.'styles/'.$theme.'.css" rel="alternate stylesheet" type="text/css" media="screen" title="'.$theme.'">';
}

// *** Added by Huub for default skin! Edit: tb var default_skin separates logic and output ***
$default_skin = (!empty($humo_option['default_skin'])) ? $humo_option['default_skin'] : "" ;
echo '<script type="text/javascript">';
echo '  var defaultskin="'.$default_skin.'";';
echo '</script>';

// *** Stylesheetswitcher 2 ***
echo '<script src="'.CMS_ROOTPATH.'styles/styleswitch.js" type="text/javascript"></script>';
?>