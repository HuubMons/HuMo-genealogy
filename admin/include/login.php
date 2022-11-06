<?php
if (CMS_SPECIFIC=='Joomla')
	$path_tmp='index.php?option=com_humo-gen&amp;task=admin';
else
	$path_tmp='index.php';

echo '<h2 align=center>'.__('Administration menu login').'</h2>';

// *** Show login fault message ***
if ($fault) echo '<p align="center"><font color="red">'.__('Please enter a valid username or password. ').'</font>';

echo '<form name="form1" method="post" action="'.$path_tmp.'">';
	echo '<table class="humo" border="1" cellspacing="0" align="center">';
	echo '<tr><td>'.__('Username or e-mail address').':</td><td><input name="username" type="text" size="20" maxlength="25"></td></tr>';
	echo '<tr><td>'.__('Password').':</td><td><input name="password" type="password" size="20" maxlength="50"></td></tr>';
	echo '<tr><td>'.__('Two factor authentication (2FA) code if needed').':</td><td><input name="2fa_code" type="text" size="20" maxlength="25"></td></tr>';
	echo '<tr><td><br></td><td><input type="submit" name="Submit" value="'.__('Login').'"></td></tr>';
	echo '</table>';
echo '</form>';
?>