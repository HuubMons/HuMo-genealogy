<?php
include_once __DIR__ . '/header.php';
include_once  __DIR__ . '/menu.php';
include_once  __DIR__ . '/nextlib/Authenticator2fa.php';

$two_fa_change = false;

if (isset($_SESSION['user_id']) and is_numeric($_SESSION['user_id'])) {
	@$qry = "SELECT * FROM humo_users LEFT JOIN humo_groups
		ON humo_users.user_group_id=humo_groups.group_id
		WHERE humo_users.user_id='" . $_SESSION['user_id'] . "'";
	@$result = $dbh->query($qry);
	if ($result->rowCount() > 0) {
		@$userDb = $result->fetch(PDO::FETCH_OBJ);
	}
}

if (isset($_POST['update_settings'])) {
	$result_message = '';
	if ($_POST["register_password"] != $_POST["register_repeat_password"]) {
		$result_message = __('ERROR: No identical passwords');
	}

	if ($result_message == '') {
		$user_register_date = date("Y-m-d H:i");
		//user_name='".safe_text_db($_POST["register_name"])."',
		//user_remark='".safe_text_db($_POST["register_text"])."',
		//user_register_date='".safe_text_db($user_register_date)."',
		//user_group_id='".$humo_option["visitor_registration_group"]."'
		$sql = "UPDATE humo_users SET user_mail='" . safe_text_db($_POST["register_mail"]) . "'";
		if ($_POST["register_password"] != '')
			$hashToStoreInDb = password_hash($_POST["register_password"], PASSWORD_DEFAULT);
		if (isset($hashToStoreInDb)) $sql .= ", user_password_salted='" . $hashToStoreInDb . "'";
		$sql .= " WHERE user_id=" . $userDb->user_id;
		$result = $dbh->query($sql);

		$result_message = __('Your settings are updated!');

		// *** Only update 2FA settings if database is updated and 2FA settings are changed ***
		if (isset($userDb->user_2fa_enabled) and isset($_POST['user_2fa_check'])) {
			// *** 2FA Authenticator (2fa_code = code from 2FA authenticator) ***
			if (!isset($_POST['user_2fa_enabled']) and $userDb->user_2fa_enabled) {
				// *** Disable 2FA ***
				$sql = "UPDATE humo_users SET user_2fa_enabled='' WHERE user_id=" . $userDb->user_id;
				$result = $dbh->query($sql);
				$two_fa_change = true;
			}
			if (isset($_POST['user_2fa_enabled']) and !$userDb->user_2fa_enabled) {
				$two_fa_change = true;
				if ($_POST['2fa_code'] and is_numeric($_POST['2fa_code'])) {
					$Authenticator = new Authenticator2fa();
					$checkResult = $Authenticator->verifyCode($userDb->user_2fa_auth_secret, $_POST['2fa_code'], 2);		// 2 = 2*30sec clock tolerance
					if (!$checkResult) {
						$result_message = __('Wrong 2FA code. Please enter valid 2FA code to enable 2FA authentication.') . '<br>';
					} else {
						$sql = "UPDATE humo_users SET user_2fa_enabled='1' WHERE user_id=" . $userDb->user_id;
						$result = $dbh->query($sql);
						$result_message = __('Enabled 2FA authentication.') . '<br>';
					}
				} else {
					// *** No 2FA code entered ***
					$result_message = __('Wrong 2FA code. Please enter valid 2FA code to enable 2FA authentication.') . '<br>';
				}
			}
		}

		// *** Reload user settings (especially needed for 2FA settings) ***
		@$qry = "SELECT * FROM humo_users LEFT JOIN humo_groups
			ON humo_users.user_group_id=humo_groups.group_id
			WHERE humo_users.user_id='" . $_SESSION['user_id'] . "'";
		@$result = $dbh->query($qry);
		if ($result->rowCount() > 0) {
			@$userDb = $result->fetch(PDO::FETCH_OBJ);
		}

		//echo '<h2>'.__('Your settings are updated!').'</h2>';
	}
	//else{
	//	echo '<h2>'.$error.'</h2>';
	//}
	echo '<h2>' . $result_message . '</h2>';

	if ($dataDb->tree_email) {
		// *** Mail new registered user to the administrator ***
		$register_address = $dataDb->tree_email;

		$register_subject = "HuMo-genealogy. " . __('Updated profile') . ": " . $userDb->user_name . "\n";

		// *** It's better to use plain text in the subject ***
		$register_subject = strip_tags($register_subject, ENT_QUOTES);

		$register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
		$register_message .= "<br><br>\n";
		$register_message .= __('User updated his/ her profile') . "<br>\n";
		$register_message .= __('Name') . ':' . $userDb->user_name . "<br>\n";
		$register_message .= __('E-mail') . ": <a href='mailto:" . $_POST['register_mail'] . "'>" . $_POST['register_mail'] . "</a><br>\n";
		//$register_message .=$_POST['register_text']."<br>\n";

		//$headers  = "MIME-Version: 1.0\n";
		//$headers .= "Content-type: text/html; charset=utf-8\n";
		//$headers .= "X-Priority: 3\n";
		//$headers .= "X-MSMail-Priority: Normal\n";
		//$headers .= "X-Mailer: php\n";
		//$headers .= "From: \"".$userDb->user_name."\" <".$_POST['register_mail'].">\n";

		//@$mail = mail($register_address, $register_subject, $register_message, $headers);

		include_once __DIR__ . '/include/mail.php';

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
//elseif (isset($userDb->user_name)){
if (isset($userDb->user_name)) {
	//echo '<br><form id="form_id" method="post" action="user_settings.php" accept-charset = "utf-8" onsubmit="javascript:return validate(\'form_id\',\'register_mail\');">';
	echo '<br><form id="form_id" method="post" action="user_settings.php" accept-charset = "utf-8">';

	//echo '<table align="center" class="humo small">';
	echo '<table align="center" class="humo small" style="width:80%;">';
	echo '<tr class=table_headline><th class="fonts" colspan="2">' . __('User settings') . '</th></tr>';

	$register_name = $userDb->user_name;
	if (isset($_POST['register_name'])) {
		$register_name = $_POST['register_name'];
	}
	//echo '<tr><td>'.__('Username').':</td><td><input type="text" class="fonts" name="register_name" size="40" style="background-color:#FFFFFF" value="'.$register_name.'"></td></tr>';
	echo '<tr><td>' . __('Username') . '</td><td>' . $register_name . '</td></tr>';

	$register_password = '';
	if (isset($_POST['register_password'])) {
		$register_password = $_POST['register_password'];
	}

	if ($user['group_menu_change_password'] == 'y') {
		echo '<tr><td>' . __('Password') . '</td><td><input type="password" class="fonts" name="register_password" size="30" style="background-color:#FFFFFF" value="' . $register_password . '"></td></tr>';

		$register_repeat_password = '';
		if (isset($_POST['register_repeat_password'])) {
			$register_repeat_password = $_POST['register_repeat_password'];
		}
		echo '<tr><td>' . __('Repeat password') . '</td><td><input type="password" class="fonts" name="register_repeat_password" size="30" style="background-color:#FFFFFF" value="' . $register_repeat_password . '"></td></tr>';

		$register_mail = $userDb->user_mail;
		if (isset($_POST['register_mail'])) {
			$register_mail = $_POST['register_mail'];
		}
		// *** Use HTML 5 e-mail check ***
		echo '<tr><td>' . __('E-mail address') . '</td><td><input type="email" class="fonts" id="register_mail" name="register_mail" value="' . $register_mail . '" size="30" style="background-color:#FFFFFF"> </td></tr>';

		// *** Only check 2FA is database is updated ***
		if (isset($userDb->user_2fa_auth_secret)) {
			// *** 2FA Two factor authentification ***
			$Authenticator = new Authenticator2fa();
			if ($userDb->user_2fa_auth_secret) {
				$user_2fa_auth_secret = $userDb->user_2fa_auth_secret;
			} else {
				$user_2fa_auth_secret = $Authenticator->generateRandomSecret();

				// *** Save auth_secret, so it's not changed anymore ***
				$sql = "UPDATE humo_users SET user_2fa_auth_secret='" . safe_text_db($user_2fa_auth_secret) . "' WHERE user_id=" . $userDb->user_id;
				$result = $dbh->query($sql);
			}

			echo '<tr><td>';
			echo __('Two factor authentication (2FA)');
			echo '</td><td>';
			//$hideshow=1; echo '<a href="#" onclick="hideShow('.$hideshow.');">'.__('Two factor authentication (2FA)').'</a> ';
			echo '<a href="user_settings.php?2fa=1">' . __('Two factor authentication (2FA)') . '</a> ';
			echo '</td></tr>';

			//$style=' style="display:none;"';
			//if ($two_fa_change) $style=''; // *** If 2FA is changed, show the 2FA items ***
			//echo '<tr'.$style.' class="row'.$hideshow.'"><td>';
			if (isset($_GET['2fa']) and $_GET['2fa'] == '1') {
				//$siteusernamestr= "Your Sites Unique String";
				$siteusernamestr = 'HuMo-genealogy ' . $_SERVER['SERVER_NAME'];
				$qrCodeUrl = $Authenticator->getQR($siteusernamestr, $user_2fa_auth_secret);

				echo '<tr><td>';
				echo __('Highly recommended:<br>Enable "Two Factor Authentication" (2FA).') . '<br>';
				echo __('Use a 2FA app (like Microsoft or Google authenticator) to generate a secure code to login.') . '<br>';
				echo __('More information about 2FA can be found at internet.');
				echo '</td><td>';
				printf(__('1) Install a 2FA app, and add %s in the app using this QR code:'), 'HuMo-genealogy');
				echo '<br>';
				echo '<img style="text-align: center;" class="img-fluid" src="' . $qrCodeUrl . '" alt="Verify this Google Authenticator"><br><br>';

				echo __('2) Use 2FA code from app and enable 2FA login:') . '<br>';
				echo '<input type="text" class="fonts" id="2fa_code" name="2fa_code" placeholder="' . __('2FA code from app') . '" size="30" style="background-color:#FFFFFF">';

				$checked = '';
				if ($userDb->user_2fa_enabled == 1) $checked = ' checked="true"';
				echo ' <input type="checkbox" name="user_2fa_enabled"' . $checked . '>' . __('Enable 2FA login');

				echo '<input type="hidden" name="user_2fa_check">';

				//echo '<br>'.$qrCodeUrl;
				//echo '<br>'.$url_path.' '.$uri_path;
				//echo '<br>'.$_SERVER['SERVER_NAME'];
				echo '</td></tr>';
			}

			// *** Script voor expand and collapse of items ***
			/*
			echo '
			<script type="text/javascript">
			function hideShow(el_id){
				// *** Hide or show item ***
				var arr = document.getElementsByClassName(\'row\'+el_id);
				for (i=0; i<arr.length; i++){
					if(arr[i].style.display!="none"){
						arr[i].style.display="none";
					}else{
						arr[i].style.display="";
					}
				}

				// *** Change [+] into [-] or reverse ***
				if (document.getElementById(\'hideshowlink\'+el_id).innerHTML == "[+]")
					document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[-]";
				else
					document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[+]";
			}
			</script>
			';
			*/
		}

		//$register_text=''; if (isset($_POST['register_text'])){ $register_text=$_POST['register_text']; }
		//echo '<tr><td>'.__('Message: ').'</td><td><textarea name="register_text" ROWS="5" COLS="40" class="fonts">'.$register_text.'</textarea></td></tr>';

		//echo '<tr><td></td><td style="font-weight:bold;" class="fonts" align="left">'.__('Please enter a full and valid email address,<br>otherwise I cannot respond to your e-mail!').'</td></tr>';
		echo '<tr><td></td><td><input class="fonts" type="submit" name="update_settings" value="' . __('Change') . '"></td></tr>';
	}

	echo '</table>';
	echo '</form>';
}


// *** Theme select ***
// *** Hide theme select if there is only one theme, AND it is the default theme ***
$show_theme_select = true;
if (count($theme_folder) == 1) {
	if (isset($humo_option['default_skin']) and $humo_option['default_skin'] . '.css' == $theme_folder[0]) {
		$show_theme_select = false;
	}
}

if ($bot_visit) {
	$show_theme_select = false;
}

if ($show_theme_select == true) {
	$hide_themes_array = explode(";", $humo_option["hide_themes"]);
	$hide_themes_array[] = 'gedcom';
	$hide_themes_array[] = 'gedcom_mobile';
	$hide_themes_array[] = 'print';
	
	//echo '<br><table class="humo small">';
	echo '<br><table class="humo">';
	echo '<tr class=table_headline><th class="fonts">' . __('Select a theme') . '</th></tr>';
	echo '<td align="center">';
	echo '<form title="' . __('Select a colour theme (a cookie will be used to remember the theme)') . '" action="">';
	echo '<select name="switchcontrol" size="1" onchange="chooseStyle(this.options[this.selectedIndex].value, 365)">';

	// NAZIEN DIT STUK CODE KAN ERUIT? En dan het geselecteerde thema als selected (uit cookie?)?
	if (isset($humo_option['default_skin'])) {
		echo '<option value="' . $humo_option['default_skin'] . '" selected="selected">' . __('Select a theme') . ':</option>';
		echo '<option value="' . $humo_option['default_skin'] . '">' . __('Standard-colours') . '</option>';
	} else {
		echo '<option value="none" selected="selected">' . __('Select a theme') . ':</option>';
		echo '<option value="none">' . __('Standard-colours') . '</option>';
	}

	sort($theme_folder);
	for ($i = 0; $i < count($theme_folder); $i++) {
		$theme = $theme_folder[$i];
		$theme = str_replace(".css", "", $theme);
		if (!in_array($theme, $hide_themes_array)) {
			echo '<option value="' . $theme . '">' . $theme . '</option>';
		}
	}
	echo '</select></form>';


	// *** Theme select using screen shots ***
	// *** Screen shots about 725x500 (but resized to smaller pictures) ***
	echo '<br>';
	echo '<form title="' . __('Select a colour theme (a cookie will be used to remember the theme)') . '" action="">';
	for ($i = 0; $i < count($theme_folder); $i++) {
		$theme = $theme_folder[$i];
		$theme = str_replace(".css", "", $theme);
		if (!in_array($theme, $hide_themes_array)) {
			echo '<span style="float: left; margin: 3px; border: solid 1px #999999;">';
			echo '<b>' . $theme . '</b><br>';
			echo '<input type="image" name="submit" value="submit" alt="theme" src="styles/' . $theme . '.png" width="360" height="250" onclick="chooseStyle(\'' . $theme . '\', 365)">';
			echo '</span>';
		}
	}
	echo '</form>';

	echo '</td>';
	echo '</table>';
}

include_once __DIR__ . '/footer.php';
