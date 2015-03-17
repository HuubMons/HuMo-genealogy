<?php
if (CMS_SPECIFIC=='Joomla')
	$path_tmp='index.php?option=com_humo-gen&amp;task=admin';
else
	$path_tmp=$_SERVER['PHP_SELF'];

print '<h2 align=center>'.__('Administration menu login').'</h2>';

// *** Show login fault message ***
echo $fault;

print '<form name="form1" method="post" action="'.$path_tmp.'">';
	print '<table class="humo" border="1" cellspacing="0" align="center">';
	print '<tr><td>'.__('Username').':</td><td><input name="username" type="text"  size="10" maxlength="25"></td></tr>';
	print '<tr><td>'.__('Password').':</td><td><input name="paswoord" type="password" size="10" maxlength="50"></td></tr>';
	print '<tr><td><br></td><td><input type="submit" name="Submit" value="'.__('Login').'"></td></tr>';
	print '</table>';
print '</form>';
?>