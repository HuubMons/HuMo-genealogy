<?php
include_once("header.php");
include_once(CMS_ROOTPATH . "menu.php");

// *** Check block_spam_answer ***
$register_allowed = false;
if (isset($_POST['send_mail'])) {
	if (isset($_POST['register_block_spam']) and strtolower($_POST['register_block_spam']) == strtolower($humo_option["block_spam_answer"])) {
		$register_allowed = true;
	}
}
if ($humo_option["registration_use_spam_question"] != 'y') {
	$register_allowed = true;
}

$show_form = true;
$error = false;

if (isset($_POST['send_mail']) and $register_allowed == true) {
	$show_form = false;

	$usersql = 'SELECT * FROM humo_users WHERE user_name="' . safe_text_db($_POST["register_name"]) . '"';
	$user = $dbh->query($usersql);
	$userDb = $user->fetch(PDO::FETCH_OBJ);
	if (isset($userDb->user_id) or strtolower(safe_text_db($_POST["register_name"])) == "admin") {
		$error = __('ERROR: username already exists');
	}

	if ($_POST["register_password"] != $_POST["register_repeat_password"]) {
		$error = __('ERROR: No identical passwords');
	}

	if (strlen($_POST["register_password"]) < 6) {
		$error = __('ERROR: Password has to contain at least 6 characters');
	}

	if ($error == false) {
		$user_register_date = date("Y-m-d H:i");
		$hashToStoreInDb = password_hash($_POST["register_password"], PASSWORD_DEFAULT);
		$sql = "INSERT INTO humo_users SET
		user_name='" . safe_text_db($_POST["register_name"]) . "',
		user_remark='" . safe_text_db($_POST["register_text"]) . "',
		user_register_date='" . safe_text_db($user_register_date) . "',
		user_mail='" . safe_text_db($_POST["register_mail"]) . "',
		user_password_salted='" . $hashToStoreInDb . "',
		user_group_id='" . $humo_option["visitor_registration_group"] . "';";
		$result = $dbh->query($sql);
		echo '<h2>' . __('Registration completed') . '</h2>';
		echo __('At this moment you are registered in the user-group "guest". The administrator will check your registration, and select a user-group for you.') . '<br>';

		// *** Mail new registered user to the administrator ***
		$register_address = '';
		if (isset($dataDb->tree_email)) $register_address = $dataDb->tree_email; // Used in older HuMo-genealogy versions. Backwards compatible...
		if ($humo_option["general_email"]) $register_address = $humo_option["general_email"];

		$register_subject = "HuMo-genealogy. " . __('New registered user') . ": " . $_POST['register_name'] . "\n";

		// *** It's better to use plain text in the subject ***
		$register_subject = strip_tags($register_subject, ENT_QUOTES);

		$register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
		$register_message .= "<br><br>\n";
		$register_message .= __('New registered user') . "<br>\n";
		$register_message .= __('Name') . ':' . $_POST['register_name'] . "<br>\n";
		$register_message .= __('E-mail') . ": <a href='mailto:" . $_POST['register_mail'] . "'>" . $_POST['register_mail'] . "</a><br>\n";
		$register_message .= $_POST['register_text'] . "<br>\n";

		//$headers  = "MIME-Version: 1.0\n";
		//$headers .= "Content-type: text/html; charset=utf-8\n";
		//$headers .= "X-Priority: 3\n";
		//$headers .= "X-MSMail-Priority: Normal\n";
		//$headers .= "X-Mailer: php\n";
		//$headers .= "From: \"".$_POST['register_name']."\" <".$_POST['register_mail'].">\n";

		//@$mail = mail($register_address, $register_subject, $register_message, $headers);
		include_once('include/mail.php');

		// *** Set who the message is to be sent from ***
		$mail->setFrom($_POST['register_mail'], $_POST['register_name']);
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
	} else {
		$show_form = true;
		echo '<h2>' . $error . '</h2>';
	}
}

if ($show_form) {
	$email = '';
	if (isset($dataDb->tree_email)) $email = $dataDb->tree_email; // Used in older HuMo-genealogy versions. Backwards compatible...
	if ($humo_option["general_email"]) $email = $humo_option["general_email"];
	if ($email != '') {
		echo '<script>';
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

		echo '<br><form id="form_id" method="post" action="' . CMS_ROOTPATH . 'register.php" accept-charset = "utf-8" onsubmit="javascript:return validate(\'form_id\',\'register_mail\');">';

		print '<table align="center" class="humo">';
		echo '<tr class="table_headline"><th class=fonts" colspan="2">' . __('User registration form') . '</th></tr>';

		$register_name = '';
		if (isset($_POST['register_name'])) {
			$register_name = $_POST['register_name'];
		}
		//print '<tr><td>'.__('Username').'</td><td><input type="text" class="fonts" name="register_name" size="40" style="background-color:#FFFFFF" value="'.$register_name.'"></td></tr>';
		print '<tr><td>' . __('Name') . '</td><td><input type="text" class="fonts" name="register_name" size="40" style="background-color:#FFFFFF" value="' . $register_name . '"></td></tr>';

		$register_mail = '';
		if (isset($_POST['register_mail'])) {
			$register_mail = $_POST['register_mail'];
		}
		print '<tr><td>' . __('E-mail address') . '</td><td><input type="text" class="fonts" id="register_mail" name="register_mail" value="' . $register_mail . '" size="40" style="background-color:#FFFFFF"> </td></tr>';

		$register_password = '';
		if (isset($_POST['register_password'])) {
			$register_password = $_POST['register_password'];
		}
		print '<tr><td>' . __('Password') . '</td><td><input type="password" class="fonts" name="register_password" size="40" style="background-color:#FFFFFF" value="' . $register_password . '"></td></tr>';

		$register_repeat_password = '';
		if (isset($_POST['register_repeat_password'])) {
			$register_repeat_password = $_POST['register_repeat_password'];
		}
		print '<tr><td>' . __('Repeat password') . '</td><td><input type="password" class="fonts" name="register_repeat_password" size="40" style="background-color:#FFFFFF" value="' . $register_repeat_password . '"></td></tr>';

		//$register_subject=''; if (isset($_POST['register_subject'])){ $register_subject=$_POST['register_subject']; }
		//print '<tr><td>'.__('Subject:').'</td><td><input type="text" class="fonts" name="register_subject" size="80" style="background-color:#FFFFFF" value="'.$register_subject.'"></td></tr>';

		$register_text = '';
		if (isset($_POST['register_text'])) {
			$register_text = $_POST['register_text'];
		}
		print '<tr><td>' . __('Message') . '</td><td><textarea name="register_text" ROWS="5" COLS="40" class="fonts">' . $register_text . '</textarea></td></tr>';

		if ($humo_option["registration_use_spam_question"] == 'y') {
			echo '<tr><td>' . __('Please answer the block-spam-question:') . '</td>';
			echo '<td>' . $humo_option["block_spam_question"] . '<br>';
			echo '<input type="text" class="fonts" name="register_block_spam" size="80" style="background-color:#FFFFFF"></td></tr>';
		}

		//print '<tr><td></td><td style="font-weight:bold;" class="fonts" align="left">'.__('Please enter a full and valid email address,<br>otherwise I cannot respond to your e-mail!').'</td></tr>';
		echo '<tr><td></td><td><input class="fonts" type="submit" name="send_mail" value="' . __('Send') . '"></td></tr>';
		echo '</table>';
		echo '</form>';

		if (isset($_POST['send_mail']) and $error == false) {
			echo '<h3 style="text-align:center;">' . __('Wrong answer to the block-spam question! Try again...') . '</h3>';
		}
	} else {
		echo '<h2>' . __('The register function has been switched off!') . '</h2>';
	}
}
echo '<br>';
include_once(CMS_ROOTPATH . "footer.php");
