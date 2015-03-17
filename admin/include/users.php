<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

// *** Automatic installation or update ***
$column_qry = $dbh->query('SHOW COLUMNS FROM humo_users');
while ($columnDb = $column_qry->fetch()) {
	$field_value=$columnDb['Field'];
	$field[$field_value]=$field_value;
}

if (!isset($field['user_hide_trees'])){
	$sql="ALTER TABLE humo_users
		ADD user_hide_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER user_group_id;";
	$result=$dbh->query($sql);
}
if (!isset($field['user_edit_trees'])){
	$sql="ALTER TABLE humo_users
		ADD user_edit_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER user_hide_trees;";
	$result=$dbh->query($sql);
}

echo '<h1 align=center>'.__('Users').'</h1>';

if (isset($_POST['change_user'])){
	$usersql="SELECT * FROM humo_users ORDER BY user_name";
	$user=$dbh->query($usersql);
	while ($userDb=$user->fetch(PDO::FETCH_OBJ)){
		$username=$_POST[$userDb->user_id."username"];
		$usermail=$_POST[$userDb->user_id."usermail"];
		if ($_POST[$userDb->user_id."username"]==""){
			$username='GEEN NAAM / NO NAME';
		}
		$sql="UPDATE humo_users SET
			user_name='".$username."',
			user_mail='".$usermail;
		if (isset($_POST[$userDb->user_id."password"]) AND $_POST[$userDb->user_id."password"]){
			$sql=$sql."', user_password='".MD5($_POST[$userDb->user_id."password"]);
		}
		$sql=$sql."', user_group_id='".$_POST[$userDb->user_id."group_id"];
		$sql=$sql."' WHERE user_id=".$_POST[$userDb->user_id."user_id"];
		$result=$dbh->query($sql);
	}
}

if (isset($_POST['add_user'])){
	$sql="INSERT INTO humo_users SET
	user_name='".$_POST["add_username"]."',
	user_mail='".$_POST["add_usermail"]."',
	user_password='".MD5($_POST["add_password"])."',
	user_group_id='".$_POST["add_group_id"]."';";
	$result=$dbh->query($sql);
}

// *** Remove user ***
if (isset($_GET['remove_user'])){
	echo '<div class="confirm">';
	echo __('Are you sure you want to delete this user?');
	echo ' <form method="post" action="'.$_SERVER['PHP_SELF'].'" style="display : inline;">';
	echo '<input type="hidden" name="page" value="'.$_GET['page'].'">';
	echo '<input type="hidden" name="remove_user" value="'.$_GET['remove_user'].'">';
	echo ' <input type="Submit" name="remove_user2" value="'.__('Yes').'" style="color : red; font-weight: bold;">';
	echo ' <input type="Submit" name="submit" value="'.__('No').'" style="color : blue; font-weight: bold;">';
	echo '</form>';
	echo '</div>';
}
if (isset($_POST['remove_user2'])){
	// *** Delete source connection ***
	$sql="DELETE FROM humo_users WHERE user_id='".safe_text($_POST['remove_user'])."'";
	$result=$dbh->query($sql);
}


// *************
// *** Users ***
// *************

if(CMS_SPECIFIC=="Joomla") {
	echo "<form method=\"POST\" action=\"index.php?option=com_humo-gen&amp;task=admin&amp;page=users\">\n";
}
else {
	echo "<form method=\"POST\" action=\"".$_SERVER['PHP_SELF']."\">\n";
}
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '<br><table class="humo standard" border="1" style="width:95%;">';

echo '<tr class="table_header_large"><th>'.__('User').'</th>';
echo '<th>'.__('E-mail address').'</th>';
echo '<th>'.__('Change password').'</th>';
echo '<th>'.__('User group').'</th>';
echo '<th>'.__('Extra settings').'</th>';
echo '<th>'.__('Statistics').'</th>';
//echo '<th>'.__('Change').'</th></tr>';
echo '<th><input type="Submit" name="change_user" value="'.__('Change').'"></th></tr>';

$usersql="SELECT * FROM humo_users ORDER BY user_name";
$user=$dbh->query($usersql);
while ($userDb=$user->fetch(PDO::FETCH_OBJ)){
	echo '<tr align="center"><td>';

	if ($userDb->user_name!='gast' AND $userDb->user_name!='guest' AND $userDb->user_id!='1'){
		echo '<a href="'.$_SERVER['PHP_SELF'].'?page=users&remove_user='.$userDb->user_id.'">';
		echo '<img src="'.CMS_ROOTPATH_ADMIN.'images/button_drop.png" border="0" alt="remove person"></a> ';
	}
	else
		echo '&nbsp;&nbsp;';

	echo '<input type="hidden" name="'.$userDb->user_id.'user_id" value="'.$userDb->user_id.'">';

	// *** It's not allowed to change username "guest" (gast = backwards compatibility) ***
	if ($userDb->user_name=='gast' OR $userDb->user_name=='guest') {
		echo '<input type="hidden" name="'.$userDb->user_id.'username" value="'.$userDb->user_name.'">';
		echo '<b>'.$userDb->user_name.'</b></td>';

		echo '<input type="hidden" name="'.$userDb->user_id.'usermail" value="">';
		echo '<td><br></td>';
		echo '<td><b>'.__('no need to log in').'</b>';
	}
	else{
		echo '<input type="text" name="'.$userDb->user_id.'username" value="'.$userDb->user_name.'" size="15"></td>';

		echo '<td><input type="text" name="'.$userDb->user_id.'usermail" value="'.$userDb->user_mail.'" size="20"></td>';

		echo '<td><input type="password" name="'.$userDb->user_id.'password" size="15">';
	}
	echo '</td>';

	//*** User groups ***
	if ($userDb->user_id=='1'){ //1st user is always admin.
		print '<td><input type="hidden" name="'.$userDb->user_id.'group_id" value="1"><b>admin</b></td>';
	}
	else{
		$groupsql="SELECT * FROM humo_groups";
		$groupresult=$dbh->query($groupsql);
		print '<td><select size="1" name="'.$userDb->user_id.'group_id">';
		while ($groupDb=$groupresult->fetch(PDO::FETCH_OBJ)){
			$select=''; if ($userDb->user_group_id==$groupDb->group_id) $select=' SELECTED';
			echo '<option value="'.$groupDb->group_id.'"'.$select.'>'.$groupDb->group_name.'</option>';
		}
		print "</select></td>";
	}

	echo '<td>';
	echo '<a href="javascript:;" onClick=window.open("index.php?page=editor_user_settings&user='.$userDb->user_id.'","","scrollbars=1,width=900,height=500,top=100,left=100");><img src="../images/search.png" border="0"></a>';
	echo '</td>';

	// *** Show statistics ***
	$logbooksql='SELECT COUNT(log_date) as nr_login FROM humo_user_log WHERE log_username="'.$userDb->user_name.'"';
	$logbook=$dbh->query($logbooksql);
	$logbookDb=$logbook->fetch(PDO::FETCH_OBJ);	

	$logdatesql='SELECT log_date FROM humo_user_log	WHERE log_username="'.$userDb->user_name.'" ORDER BY log_date DESC LIMIT 0,1';
	$logdate=$dbh->query($logdatesql);
	$logdateDb=$logdate->fetch(PDO::FETCH_OBJ);	

	if ($logbookDb->nr_login){
		echo '<td>#'.$logbookDb->nr_login.', '.$logdateDb->log_date.'</td>';
	}
	else{
		echo '<td><br></td>';
	}

	//print '<td><input type="Submit" name="change_user" value="'.__('Change').'"></td>';
	echo '<td><br></td>';
	echo "</tr>\n";
}

// *** Add user ***
print '<tr align="center" bgcolor="green">';
echo '<td><input type="text" name="add_username" size="15"></td>';
echo '<td><input type="text" name="add_usermail" size="20"></td>';
print '<td><input type="password" name="add_password" size="15"></td>';

// *** Select group for new user ***
$groupsql="SELECT * FROM humo_groups";
$groupresult=$dbh->query($groupsql);
print "<td><select size='1' name='add_group_id'>";
while ($groupDb=$groupresult->fetch(PDO::FETCH_OBJ)){
	$select=''; if ($groupDb->group_id=='2') $select=' SELECTED';
	echo '<option value="'.$groupDb->group_id.'"'.$select.'>'.$groupDb->group_name.'</option>';
}
print "</select></td>";

print '<td></td><td></td><td><input type="Submit" name="add_user" value="'.__('Add').'"></td>';
print '</tr>';
echo '</table>';
print '</form>';
?>