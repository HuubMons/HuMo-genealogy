<?php

$fault = false;
include_once __DIR__ . '/header.php'; // returns CMS_ROOTPATH constant
include_once __DIR__ . '/menu.php';

//echo '<div class="standard_header fonts">'.__('Login').'</div>';

if ($user['group_menu_login'] != 'j') {
	echo 'Access to this page is blocked.';
	exit;
}

$path_tmp = '/login.php';

// form to enter username and mail in order to receive reset link
if (isset($_POST['forgotpw'])) { ?>
	<form name="pw_email_form" method="post" action="<?= $path_tmp; ?>">
		<br>
		<table class="humo" cellspacing="0" align="center">
			<tr class="table_headline">
				<th class="fonts" colspan="2"><?= __('Password retrieval'); ?></th>
			</tr>
			<tr>
				<td><?= __('Email'); ?>:</td>
				<td><input class="fonts" name="got_email" type="email" size="20" maxlength="50"></td>
			</tr>
			<tr>
				<td><?= __('Please answer the block-spam-question:'); ?></td>
				<td>
					<?= $humo_option["block_spam_question"]; ?><br>
					<input type="text" class="fonts" name="register_block_spam" size="80" style="background-color:#FFFFFF">
				</td>
			</tr>
			<tr>
				<td><br></td>
				<td><input class="fonts" type="submit" name="Submit" value="<?= __('Send'); ?>"></td>
			</tr>
		</table>
	</form>
<?php }

// process email address and username, create random key and mail its link to user
elseif (isset($_POST['got_email'])) {

	$pw_table = $dbh->prepare("CREATE TABLE IF NOT EXISTS `humo_pw_retrieval` (
	`retrieval_userid` varchar(20) NOT NULL,
	`retrieval_pkey` varchar(32) NOT NULL,
	`retrieval_time` varchar(10) NOT NULL,
	`retrieval_status` varchar(7) NOT NULL
	) DEFAULT CHARSET=utf8");
	$pw_table->execute();

	function getUrl()
	{
		$url  = @($_SERVER["HTTPS"] != 'on') ? 'http://' . $_SERVER["SERVER_NAME"] :  'https://' . $_SERVER["SERVER_NAME"];
		// *** May 2022: removed port. For some reason port 80 was always shown ***
		//$url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		$url .= $_SERVER["REQUEST_URI"];
		return $url;
	}
	$site_url = getUrl();

	$status = "OK";
	$msg = "";

	$email = safe_text_db($_POST['got_email']);
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$msg = __('Your email address is not correct') . "<br>";
		$status = "NOTOK";
	}

	if (isset($_POST['register_block_spam']) and strtolower($_POST['register_block_spam']) == strtolower($humo_option["block_spam_answer"])) {
		//$register_allowed=true;
	} else {
		$msg .= __('Wrong answer to the block-spam question! Try again...') . "<br>";
		$status = "NOTOK";
	}

	echo '<br><table class="humo" cellspacing="0" align="center">';

	if ($status == "OK") {
		$countmail = $dbh->prepare("SELECT user_id, user_mail, user_name FROM humo_users WHERE user_mail=:email");
		$countmail->bindValue(':email', $email, PDO::PARAM_STR);
		$countmail->execute();
		$row = $countmail->fetch(PDO::FETCH_OBJ);
		$no_mail = $countmail->rowCount();

		//$countuser=$dbh->prepare("SELECT user_name FROM humo_users WHERE user_name = '".$pw_username."'");
		//$countuser->execute();
		//$no_user=$countuser->rowCount();

		//$count=$dbh->prepare("SELECT user_mail, user_name FROM humo_users WHERE user_name = '".$pw_username."' AND user_mail = '".$email."'");
		//$count->execute();
		//$row = $count->fetch(PDO::FETCH_OBJ);
		//$no=$count->rowCount();

		// *** Email address not found in database ***
		if ($no_mail == 0) {
			echo '<br><table class="humo" cellspacing="0" align="center">';
			echo '<tr class="table_headline"><th class="fonts">' . __('Error') . '</th></tr>';
			echo '<tr><td style="font-weight:bold;color:red">';
			//if ($no_mail == 0 AND $no_user !=0) { // mail doesn't exist, username does
			if ($no_mail == 0) { // mail doesn't exist, username does
				echo __('This email address was not found in our database.') . "&nbsp;" . __('Please contact the site owner.');
			}
			echo "</td></tr><tr><td style='text-align:center'><input type='button' value='" . __('Retry') . "' onClick='history.go(-1)'></td>";
			echo "</tr></table>";
			exit;
		}
		// *** Check if mail adress is used multiple times ***
		elseif ($no_mail > 1) {
			echo '<br><table class="humo" cellspacing="0" align="center">';
			echo '<tr class="table_headline"><th class="fonts">' . __('Error') . '</th></tr>';
			echo '<tr><td style="font-weight:bold;color:red">';
			echo __('Password activation failed because mail address is used multiple times.') . '&nbsp;' . __('Please contact the site owner.');
			echo "</td></tr><tr><td style='text-align:center'><input type='button' value='" . __('Retry') . "' onClick='history.go(-1)'></td>";
			echo "</tr></table>";
			exit;
		}

		// *** Check if activation is pending ***
		$tm = time() - 86400; // Time in last 24 hours
		$count = $dbh->prepare("SELECT retrieval_userid FROM humo_pw_retrieval WHERE retrieval_userid = '" . $row->user_id . "' and retrieval_time > '" . $tm . "' and retrieval_status='pending'");
		$count->execute();
		$no = $count->rowCount();
		if ($no == 1) {
			echo '<br><table class="humo" cellspacing="0" align="center">';
			echo '<tr class="table_headline"><th class="fonts">' . __('Notice') . '</th></tr>';
			echo '<tr><td style="font-weight:bold;color:red">';
			echo __('Your password activation key was already sent to your email address, please check your inbox and spam folder');
			echo "</td></tr></table>";
			exit;
		}

		// function to generate random number 
		function random_generator($digits)
		{
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$random_generator = substr(str_shuffle($chars), 0, 10);
			return $random_generator;
		}

		$key = random_generator(10);
		$key = md5($key);
		$tm = time();
		$sql = $dbh->prepare("insert into humo_pw_retrieval(retrieval_userid, retrieval_pkey,retrieval_time,retrieval_status) values('$row->user_id','$key','$tm','pending')");
		$sql->execute();

		include_once __DIR__ . '/include/mail.php';

		// *** Get mail for password retreival ***
		$mail_address = $humo_option["password_retreival"];

		$mail_message = __('This is in response to your request for password reset at ') . $site_url;

		$site_url = $site_url . "?ak=$key&userid=$row->user_id";

		$mail_message .= '<br>' . __('Username') . ":" . $row->user_name . '<br>';
		$mail_message .= __('To reset your password, please visit this link or copy and paste this link in your browser window ') . ":";
		$mail_message .= '<br><br><a href="' . $site_url . '">' . $site_url . '</a><br>';

		// *** Set the reply address ***
		$mail->AddReplyTo($mail_address, $mail_address);

		// *** Set who the message is sent from (this will automatically be set to your server's mail to prevent false "phishing" alarms)***
		$mail->setFrom($mail_address, $mail_address);

		// *** Set who the message is to be sent to. Use mail address from database ***
		$mail->addAddress($row->user_mail, $row->user_mail);

		// *** Set the subject line ***
		$mail->Subject = __('Your request for password retrieval');

		$mail->msgHTML($mail_message);
		// *** Replace the plain text body with one created manually ***
		//$mail->AltBody = 'This is a plain-text message body';

		echo '<br><table class="humo" cellspacing="0" align="center">';
		if (!$mail->send()) {
			echo '<tr class="table_headline"><th class="fonts">' . __('Error') . '</th></tr>';
			echo '<tr><td style="font-weight:bold;color:red">';
			echo $mail->ErrorInfo . '<br>' . __('We encountered a system problem in sending reset link to your email address.') . '&nbsp;' . __('Please contact the site owner.');
		} else {
			echo '<tr class="table_headline"><th class="fonts">' . __('Success') . '</th></tr>';
			echo '<tr><td style="font-weight:bold">';
			echo __('Your reset link was sent to your email address. Please check your mail in a few minutes.');
		}
	} else {
		echo '<tr class="table_headline"><th class="fonts">' . __('Error') . '</th></tr>';
		echo '<tr><td style="font-weight:bold;color:red">';
		echo $msg . "</td></tr><tr><td style='text-align:center'><input type='button' value='" . __('Retry') . "' onClick='history.go(-1)'>";
	}
	echo '</td></tr></table>';
}

//form to enter new password 2x (after reset link was used)
elseif (isset($_GET['ak']) and $_GET['ak'] != '') {
	$tm = time() - 86400; // Duration within which the key is valid is 86400 sec (=24 hours) - can be adjusted here 
	$ak = safe_text_db($_GET['ak']);
	$userid = safe_text_db($_GET['userid']);
	$sql = $dbh->prepare("SELECT retrieval_userid FROM humo_pw_retrieval
		WHERE retrieval_pkey=:ak and retrieval_userid=:userid and retrieval_time > '$tm' and retrieval_status='pending'");
	$sql->bindParam(':userid', $userid, PDO::PARAM_STR, 10);
	$sql->bindParam(':ak', $ak, PDO::PARAM_STR, 32);
	$sql->execute();
	$no = $sql->rowCount();

	if ($no <> 1) {
		echo "<center><font face='Verdana' size='2' color=red><b>" . __('Activation failed') . "</b></font> ";
		exit;
	}

	echo "<form action='" . $path_tmp . "' method=post>
	<input type=hidden name=todo value=new-password>
	<input type=hidden name=ak value=$ak>
	<input type=hidden name=userid value=$userid>

	<table class='humo' cellspacing='0' align='center'><tr class='table_headline'><th class='fonts' colspan='2'>" . __('New Password') . "</th></tr>
		<tr><td>" . __('New Password') . "  
		</td><td><input type ='password' class='bginput' name='password'></td></tr>
		<tr><td>" . __('Re-enter new Password') . "  
		</td><td><input type ='password' class='bginput' name='password2' ></td></tr>";

	//if ($humo_option["registration_use_spam_question"]=='y'){
	echo '<tr><td>' . __('Please answer the block-spam-question:') . '</td>';
	echo '<td>' . $humo_option["block_spam_question"] . '<br>';
	echo '<input type="text" class="fonts" name="register_block_spam" size="80" style="background-color:#FFFFFF"></td></tr>';
	//}

	echo "<tr><td></td><td><input type=submit value='" . __('Submit new Password') . "'></td></tr>
	</table></form>";
}

// store new password and display success or error message 
elseif (isset($_POST['ak']) and $_POST['ak'] != '') {
	$ak = safe_text_db($_POST['ak']);
	$userid = safe_text_db($_POST['userid']);
	$todo = safe_text_db($_POST['todo']);
	$password = safe_text_db($_POST['password']);
	$password2 = safe_text_db($_POST['password2']);

	$tm = time() - 86400;

	$sql = $dbh->prepare("SELECT retrieval_userid FROM humo_pw_retrieval
		WHERE retrieval_pkey=:ak and retrieval_userid=:userid and retrieval_time > '$tm' and retrieval_status='pending'");
	$sql->bindParam(':userid', $userid, PDO::PARAM_STR, 10);
	$sql->bindParam(':ak', $ak, PDO::PARAM_STR, 32);
	$sql->execute();
	$no = $sql->rowCount();
	if ($no <> 1) {
		echo '<br><table class="humo" cellspacing="0" align="center">';
		echo '<tr class="table_headline"><th class="fonts">' . __('Error') . '</th></tr>';
		echo '<tr><td style="font-weight:bold;color:red">';
		echo __('Password activation failed.') . '&nbsp;' . __('Please contact the site owner.');
		echo '</td></tr></table>';
		exit;
	}

	if (isset($todo) and $todo == "new-password") {

		//Setting flags for checking
		$status = "OK";
		$msg = "";

		if (strlen($password) < 4 or strlen($password) > 15) {
			$msg = $msg . __('Password must be at least 4 char and maximum 15 char long') . "<br>";
			$status = "NOTOK";
		}

		if ($password <> $password2) {
			$msg = $msg . __('The passwords don\'t match!') . "<br>";
			$status = "NOTOK";
		}

		if (isset($_POST['register_block_spam']) and strtolower($_POST['register_block_spam']) == strtolower($humo_option["block_spam_answer"])) {
			//$register_allowed=true;
		} else {
			$msg .= __('Wrong answer to the block-spam question! Try again...') . "<br>";
			$status = "NOTOK";
		}

		if ($status <> "OK") {
			echo '<div style="color:red;font-family:Verdana;font-size:14px;text-align:center">' . $msg . '</div>';
			echo '<div style="font-family:Verdana;font-size:14px;text-align:center"><input type="button" value="' . __('Retry') . '" onClick="history.go(-1)"></div>';
		} else { // if all validations are passed.
			// Update the new password now (and use salted password)
			$hashToStoreInDb = password_hash($password, PASSWORD_DEFAULT);
			$count = $dbh->prepare("update humo_users set user_password_salted='" . $hashToStoreInDb . "', user_password='' where user_id='" . $userid . "'");
			$count->execute();
			$no = $count->rowCount();
			echo '<br><table class="humo" cellspacing="0" align="center">';
			if ($no == 1) {
				$tm = time();
				// Update the key so it can't be used again. 
				$count = $dbh->prepare("update humo_pw_retrieval set retrieval_status='done'
					where retrieval_pkey='" . $ak . "' and retrieval_userid='" . $userid . "' and retrieval_status='pending'");
				$count->execute();
				echo '<tr class="table_headline"><th class="fonts">' . __('Success') . '</th></tr>';
				echo '<tr><td style="font-weight:bold">';
				echo __('Your new password has been stored successfully');
			} else {
				echo '<tr class="table_headline"><th class="fonts">' . __('Error') . '</th></tr>';
				echo '<tr><td style="font-weight:bold;color:red">';
				echo __('Failed to store new password.') . '&nbsp;' . __('Please contact the site owner.');
			}
			echo '</td></tr></table>';
		} // end of if status <> 'OK'
	}
}

// show initial login screen with "Forgot password" button
else {

	// *** No valid user found ***
	if ($fault == true) {
		echo '<br><table class="humo" cellspacing="0" align="center">';
		echo '<tr class="table_headline"><th class="fonts">' . __('Error') . '</th></tr>';
		echo '<tr><td style="font-weight:bold;color:red">';
		echo __('No valid username or password.');
		echo '</td></tr></table>';
	}

	echo '<form name="form1" method="post" action="' . $path_tmp . '">';
	echo '<br><table class="humo" cellspacing="0" align="center">';
	echo '<tr class="table_headline"><th class="fonts" colspan="2">' . __('Login') . '</th></tr>';
	echo '<tr><td>' . __('Username or e-mail address') . ':</td><td><input class="fonts" name="username" type="text" size="20" maxlength="25"></td></tr>';
	echo '<tr><td>' . __('Password') . ':</td><td><input class="fonts" name="password" type="password" size="20" maxlength="50"></td></tr>';
	echo '<tr><td>' . __('Two factor authentication (2FA) code if needed') . ':</td><td><input class="fonts" name="2fa_code" type="text" size="20" maxlength="25"></td></tr>';
	echo '<tr><td><br></td><td><input class="fonts" type="submit" name="Submit" value="' . __('Login') . '"></td></tr>';
	echo '</table>';
	echo '</form>';

	// *** Only use password retreival option if sender mail is set in admin settings ***
	if ($humo_option["password_retreival"]) {
		$mail_address = $humo_option["password_retreival"];
		// *** Check if this is a valid a-mail address ***
		if (filter_var($mail_address, FILTER_VALIDATE_EMAIL)) {
			echo '<br><div class="center">';
			echo '<form name="forget_form" method="post" action="' . $path_tmp . '">';
			echo '<input class="fonts" type="submit" name="Submit" value="' . __('Forgot password') . '">';
			echo '<input type="hidden" name="forgotpw" value="1">';
			echo '</form>';
			echo '</div>';
		}
	}
}  // end of else (else show login screen)

include_once __DIR__ . '/footer.php';
