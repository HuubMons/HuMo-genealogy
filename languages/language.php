<?php
// *** Default language ***
$selected_language="en";

// *** Saved default language ***
if (isset($humo_option['default_language'])
	AND file_exists(CMS_ROOTPATH.'languages/'.$humo_option['default_language'].'/'.$humo_option['default_language'].'.mo')){
	$selected_language=$humo_option['default_language'];
}

// *** Extra check if language exists ***
if (isset($_SESSION["language"]) AND file_exists(CMS_ROOTPATH.'languages/'.$_SESSION["language"].'/'.$_SESSION["language"].'.mo')){
	$selected_language=$_SESSION["language"];
}

// *** Read language file ***
$language = array();
include_once(CMS_ROOTPATH.'languages/'.$selected_language.'/language_data.php');

// *** .mo language text files ***
include_once(CMS_ROOTPATH."languages/gettext.php");
// *** Load ***
$_SESSION["language_selected"]=$selected_language;
Load_default_textdomain();
//Load_textdomain('customer_domain', 'languages/'.$selected_language.'/'.$selected_language.'.mo');
?>