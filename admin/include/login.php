<?php
$fault='';

if (isset($_POST['username'])){
	$query = "SELECT * FROM humo_users WHERE user_name='" .$_POST["username"] ."' AND user_password='".MD5($_POST["paswoord"])."'";
	$result = $dbh->query($query);	
	if ($result->rowCount() > 0){
		@$resultDb=$result->fetch(PDO::FETCH_OBJ);
		$_SESSION['user_name_admin'] = safe_text($_POST["username"]);
		$_SESSION['user_id_admin'] = $resultDb->user_id;
		$_SESSION['group_id_admin'] = $resultDb->user_group_id;

		// *** Add login in logbook ***
		$log_date=date("Y-m-d H:i");
		$sql="INSERT INTO humo_user_log SET
			log_date='$log_date',
			log_username='".safe_text($_POST["username"])."',
			log_ip_address='".$_SERVER['REMOTE_ADDR']."',
			log_user_admin='admin'";
		@$dbh->query($sql);		
		
		// *** Go to secured page ***
		//header("Location: index.php");
		if (CMS_SPECIFIC=='Joomla'){
			@header("Location: index.php?option=com_humo-gen&amp;task=admin");
		}
		else{
			@header("Location: index.php");
		}
		exit();
	}
	else{
		// *** No valid user or password ***
		$fault='<p align="center"><font color="red">'.__('Please enter a valid username or password. ').'</font>';
	}
}


if (CMS_SPECIFIC=='Joomla'){
	$path_tmp='index.php?option=com_humo-gen&amp;task=admin';
}
else{
	$path_tmp=$_SERVER['PHP_SELF'];
}

print '<h2 align=center>'.__('Administration menu login').'</h2>';

echo $fault;

print '<form name="form1" method="post" action="'.$path_tmp.'">';
	print '<table class="humo" border="1" cellspacing="0" align="center">';
	print '<tr><td>'.__('Username').':</td><td><input name="username" type="text"  size="10" maxlength="25"></td></tr>';
	print '<tr><td>'.__('Password').':</td><td><input name="paswoord" type="password" size="10" maxlength="50"></td></tr>';
	print '<tr><td><br></td><td><input type="submit" name="Submit" value="'.__('Login').'"></td></tr>';
	print '</table>';
print '</form>';
?>