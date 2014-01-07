<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

if(CMS_SPECIFIC=="Joomla") {
	$phpself2='index.php?option=com_humo-gen&amp;task=admin&amp;page=prefix_editor';
}
else {
	$phpself2=CMS_ROOTPATH.'admin/index.php?page=prefix_editor';
}

$file=CMS_ROOTPATH.'admin/prefixes.php';
$message='';
if (isset($_POST['save_language'])){
	$message='<b>'.__('Saved').' ';
	if (file_exists($file)){
		$language_text=$_POST['language_text'];
		if (get_magic_quotes_gpc()==1) {
		   // *** magic quotes is activated, addslashes is used ***
		   $language_text = stripslashes($language_text);
		}
		file_put_contents($file, $language_text);
	}
	else{
		$message='ERROR: FAULT IN SAVE PROCESS';
	}
}

echo '<h1 align=center>'.__('Prefix editor').'</h1>';

echo '<p>'.__('This is the (name) prefix editor of HuMo-gen.<br>
These prefixes are used to process name-prefixes if a gedcom file is read.');

echo '<form method="POST" action="'.$phpself2.'" style="display : inline;">';
echo '<p><table class="humo" border="1" cellspacing="0">';

	echo '<tr class="table_header_large"><th>';
		if (is_writable($file)) {
			echo ' <input type="Submit" name="save_language" value="'.__('Save').'"> ';
		}
		else{
			echo '<b>'.__('FILE IS NOT WRITABLE!').'</b>';
		}

		// *** Show "Save" message ***
		echo $message.'<br>';
	echo '</th></tr>';

	echo '<tr><td valign="top" width="100%">';
		echo '<textarea rows="35" cols="120" name="language_text" style="direction:ltr">';
		echo file_get_contents($file);
		echo '</textarea>';
	echo '</td></tr>';
echo '</table>';
echo '</form>';
?>