<?php
include_once("header.php");
include_once (CMS_ROOTPATH."menu.php");

if (isset($_SESSION['user_id']) AND is_numeric($_SESSION['user_id'])){
	@$qry = "SELECT * FROM humo_users LEFT JOIN humo_groups
		ON humo_users.user_group_id=humo_groups.group_id
		WHERE humo_users.user_id='".$_SESSION['user_id']."'";
	@$result = $dbh->query($qry);
	if($result->rowCount() > 0) {
		@$userDb=$result->fetch(PDO::FETCH_OBJ);
	}
}

if (isset($_POST['send_mail'])){
	$error='';
	if ($_POST["register_password"]!=$_POST["register_repeat_password"]){
		$error=__('ERROR: No identical passwords');
	}

	if ($error==false){
		$user_register_date=date("Y-m-d H:i");
		//user_name='".safe_text_db($_POST["register_name"])."',
		//user_remark='".safe_text_db($_POST["register_text"])."',
		//user_register_date='".safe_text_db($user_register_date)."',
		//user_group_id='".$humo_option["visitor_registration_group"]."'
		$sql="UPDATE humo_users SET";
		$sql.=" user_mail='".safe_text_db($_POST["register_mail"])."'";
		if ($_POST["register_password"]!='')
			$hashToStoreInDb = password_hash($_POST["register_password"], PASSWORD_DEFAULT);
			$sql.=", user_password_salted='".$hashToStoreInDb."'";
		$sql.=" WHERE user_id=".$userDb->user_id;
		$result = $dbh->query($sql);
		echo '<h2>'.__('Your settings are updated!').'</h2>';
	}
	else{
		echo '<h2>'.$error.'</h2>';
	}


	if ($dataDb->tree_email){
		// *** Mail new registered user to the administrator ***
		$register_address=$dataDb->tree_email;

		$register_subject="HuMo-gen. ".__('Updated profile').": ".$userDb->user_name."\n";

		// *** It's better to use plain text in the subject ***
		$register_subject=strip_tags($register_subject,ENT_QUOTES);

		$register_message =__('Message sent through HuMo-gen from the website.')."<br>\n";
		$register_message .="<br>\n";
		$register_message .=__('User updated his/ her profile')."<br>\n";
		$register_message .=__('Name').':'.$userDb->user_name."<br>\n";
		$register_message .=__('E-mail').": <a href='mailto:".$_POST['register_mail']."'>".$_POST['register_mail']."</a><br>\n";
		//$register_message .=$_POST['register_text']."<br>\n";

		//$headers  = "MIME-Version: 1.0\n";
		//$headers .= "Content-type: text/html; charset=utf-8\n";
		//$headers .= "X-Priority: 3\n";
		//$headers .= "X-MSMail-Priority: Normal\n";
		//$headers .= "X-Mailer: php\n";
		//$headers .= "From: \"".$userDb->user_name."\" <".$_POST['register_mail'].">\n";

		//@$mail = mail($register_address, $register_subject, $register_message, $headers);

		include_once ('include/mail.php');
		// *** Set who the message is to be sent from ***
		$mail->setFrom($_POST['register_mail'], $userDb->user_name);
		// *** Set who the message is to be sent to ***
		$mail->addAddress($register_address, $register_address);
		// *** Set the subject line ***
		$mail->Subject = $register_subject;
		$mail->msgHTML($register_message);
		// *** Replace the plain text body with one created manually ***
		//$mail->AltBody = 'This is a plain-text message body';
		if (!$mail->send()) {
		//	echo '<br><b>'.__('Sending e-mail failed!').' '. $mail->ErrorInfo.'</b>';
		//} else {
		//	echo '<br><b>'.__('E-mail sent!').'</b><br>';
		}

	}
}
elseif (isset($userDb->user_name)){

	echo '<script type="text/javascript">';
	echo '
	function validate(form_id,register_mail) {
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		var address = document.forms[form_id].elements[register_mail].value;
		if(reg.test(address) == false) {
			alert(\'Invalid Email Address\');
			return false;
		}
	}
	';
	echo '</script>';

	echo '<br><form id="form_id" method="post" action="user_settings.php" accept-charset = "utf-8" onsubmit="javascript:return validate(\'form_id\',\'register_mail\');">';

	echo '<table align="center" class="humo small">';
	echo '<tr class=table_headline><th class="fonts" colspan="2">'.__('User settings').'</th></tr>';

	$register_name=$userDb->user_name; if (isset($_POST['register_name'])){ $register_name=$_POST['register_name']; }
	//echo '<tr><td>'.__('Username').':</td><td><input type="text" class="fonts" name="register_name" size="40" style="background-color:#FFFFFF" value="'.$register_name.'"></td></tr>';
	echo '<tr><td>'.__('Username').':</td><td>'.$register_name.'</td></tr>';

	$register_password=''; if (isset($_POST['register_password'])){ $register_password=$_POST['register_password']; }

	if ($user['group_menu_change_password']=='y'){
		echo '<tr><td>'.__('Password').':</td><td><input type="password" class="fonts" name="register_password" size="30" style="background-color:#FFFFFF" value="'.$register_password.'"></td></tr>';

		$register_repeat_password=''; if (isset($_POST['register_repeat_password'])){ $register_repeat_password=$_POST['register_repeat_password']; }
		echo '<tr><td>'.__('Repeat password').':</td><td><input type="password" class="fonts" name="register_repeat_password" size="30" style="background-color:#FFFFFF" value="'.$register_repeat_password.'"></td></tr>';

		$register_mail=$userDb->user_mail; if (isset($_POST['register_mail'])){ $register_mail=$_POST['register_mail']; }
		echo '<tr><td>'.__('FULL e-mail address: ').'</td><td><input type="text" class="fonts" id="register_mail" name="register_mail" value="'.$register_mail.'" size="30" style="background-color:#FFFFFF"> </td></tr>';

		//$register_text=''; if (isset($_POST['register_text'])){ $register_text=$_POST['register_text']; }
		//echo '<tr><td>'.__('Message: ').'</td><td><textarea name="register_text" ROWS="5" COLS="40" class="fonts">'.$register_text.'</textarea></td></tr>';

		//echo '<tr><td></td><td style="font-weight:bold;" class="fonts" align="left">'.__('Please enter a full and valid email address,<br>otherwise I cannot respond to your e-mail!').'</td></tr>';
		echo '<tr><td></td><td><input class="fonts" type="submit" name="send_mail" value="'.__('Change').'"></td></tr>';
	}

	echo '</table>';
	echo '</form>';
}


// *** Theme select ***
// *** Hide theme select if there is only one theme, AND it is the default theme ***
$show_theme_select=true;
if (count($theme_folder)==1){
	if (isset($humo_option['default_skin']) AND $humo_option['default_skin'].'.css'==$theme_folder[0]) {
		$show_theme_select=false;
	}
}

if ($bot_visit){ $show_theme_select=false; }

if ($show_theme_select==true){
	$hide_themes_array=explode(";",$humo_option["hide_themes"]);

	//echo '<br><table class="humo small">';
	echo '<br><table class="humo">';
	echo '<tr class=table_headline><th class="fonts">'.__('Select a theme').'</th></tr>';
		echo '<td align="center">';
		echo '<form title="'.__('Select a colour theme (a cookie will be used to remember the theme)').'" action="">';
		echo '<select name="switchcontrol" size="1" onchange="chooseStyle(this.options[this.selectedIndex].value, 365)">';

// NAZIEN DIT STUK CODE KAN ERUIT? En dan het geselecteerde thema als selected (uit cookie?)?
		if (isset($humo_option['default_skin'])){
			echo '<option value="'.$humo_option['default_skin'].'" selected="selected">'.__('Select a theme').':</option>';
			echo '<option value="'.$humo_option['default_skin'].'">'.__('Standard-colours').'</option>';
		}
		else{
			echo '<option value="none" selected="selected">'.__('Select a theme').':</option>';
			echo '<option value="none">'.__('Standard-colours').'</option>';
		}

		sort($theme_folder);
		for ($i=0; $i<count($theme_folder); $i++){
			$theme=$theme_folder[$i];
			$theme=str_replace(".css","", $theme);
			if (!in_array($theme, $hide_themes_array)){
				echo '<option value="'.$theme.'">'.$theme.'</option>';
			}
		}
		echo '</select></form>';


		// *** Theme select using screen shots ***
		// *** Screen shots about 725x500 (but resized to smaller pictures) ***
		echo '<br>';
		echo '<form title="'.__('Select a colour theme (a cookie will be used to remember the theme)').'" action="">';
		for ($i=0; $i<count($theme_folder); $i++){
			$theme=$theme_folder[$i];
			$theme=str_replace(".css","", $theme);
			if (!in_array($theme, $hide_themes_array)){
				echo '<span style="float: left; margin: 3px; border: solid 1px #999999;">';
					echo '<b>'.$theme.'</b><br>';
					echo '<input type="image" name="submit" value="submit" alt="theme" src="styles/'.$theme.'.png" width="360" height="250" onclick="chooseStyle(\''.$theme.'\', 365)">';
				echo '</span>';
			}
		}
		echo '</form>';

		echo '</td>';
	echo '</table>';

}

include_once(CMS_ROOTPATH."footer.php");
?>