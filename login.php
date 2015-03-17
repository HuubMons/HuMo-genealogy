<?php
$fault=false;
include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH."menu.php");

//print '<div class="standard_header fonts">'.__('Login').'</div>';

// *** No valid user found ***
if ($fault==true){
	echo '<div class="center"><font color="red"><b>'.__('No valid username or password.').'</b></font></div>';
}

if (CMS_SPECIFIC=='Joomla'){
	$path_tmp='index.php?option=com_humo-gen&amp;task=login';
}
else {
	$path_tmp=CMS_ROOTPATH.'login.php';
}
print '<form name="form1" method="post" action="'.$path_tmp.'">';
	print '<br><table class="humo" cellspacing="0" align="center">';
	echo '<tr class="table_headline"><th class=fonts" colspan="2">'.__('Login').'</th></tr>';
	print '<tr><td>'.__('Username').':</td><td><input class="fonts" name="username" type="text" size="20" maxlength="25"></td></tr>';
	print '<tr><td>'.__('Password').':</td><td><input class="fonts" name="password" type="password" size="20" maxlength="50"></td></tr>';
	print '<tr><td><br></td><td><input class="fonts" type="submit" name="Submit" value="'.__('Login').'"></td></tr>';
	print '</table>';
print '</form>';

include_once(CMS_ROOTPATH."footer.php");
?>