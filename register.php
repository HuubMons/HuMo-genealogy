<?php
include_once("header.php");
include_once (CMS_ROOTPATH."menu.php");

// *** Check block_spam_answer ***
$register_allowed=false;
if (isset($_POST['send_mail'])){
	if (isset($_POST['register_block_spam']) AND strtolower($_POST['register_block_spam'])==strtolower($humo_option["block_spam_answer"])){ $register_allowed=true; }
}
if ($humo_option["registration_use_spam_question"]!='y'){
	$register_allowed=true;
}

if (isset($_POST['send_mail']) AND $register_allowed==true){
	$error='';

	$usersql='SELECT * FROM humo_users WHERE user_name="'.safe_text($_POST["register_name"]).'"';
	$user = $dbh->query($usersql);
	$userDb=$user->fetch(PDO::FETCH_OBJ);
	if (isset($userDb->user_id)){
		$error=__('ERROR: username already exists');
	}

	if ($_POST["register_password"]!=$_POST["register_repeat_password"]){
		$error=__('ERROR: No identical passwords');
	}

	if ($error==false){
		$user_register_date=date("Y-m-d H:i");
		$sql="INSERT INTO humo_users SET
		user_name='".safe_text($_POST["register_name"])."',
		user_remark='".safe_text($_POST["register_text"])."',
		user_register_date='".safe_text($user_register_date)."',
		user_mail='".safe_text($_POST["register_mail"])."',
		user_password='".MD5($_POST["register_password"])."',
		user_group_id='".$humo_option["visitor_registration_group"]."';";
		$result = $dbh->query($sql);
		echo '<h2>'.__('Registration completed').'</h2>';
		echo __('At this moment you are registered in the user-group "guest". The administrator will check your registration, and select a user-group for you.');

		// *** Mail new registered user to the administrator ***
		//$register_address=$dataDb->tree_email;
		$register_address='';
		if (isset($dataDb->tree_email)) $register_address=$dataDb->tree_email; // Used in older HuMo-gen versions. Backwards compatible...
		if ($humo_option["general_email"]) $register_address=$humo_option["general_email"];

		$register_subject="HuMo-gen. ".__('New registered user').": ".$_POST['register_name']."\n";

		// *** It's better to use plain text in the subject ***
		$register_subject=strip_tags($register_subject,ENT_QUOTES);

		$register_message =__('Message sent through HuMo-gen from the website.')."<br>\n";
		$register_message .="<br>\n";
		$register_message .=__('New registered user')."<br>\n";
		$register_message .=__('Name').':'.$_POST['register_name']."<br>\n";
		$register_message .=__('E-mail').": <a href='mailto:".$_POST['register_mail']."'>".$_POST['register_mail']."</a><br>\n";
		$register_message .=$_POST['register_text']."<br>\n";

		$headers  = "MIME-Version: 1.0\n";
		//$headers .= "Content-type: text/plain; charset=utf-8\n";
		$headers .= "Content-type: text/html; charset=utf-8\n";
		$headers .= "X-Priority: 3\n";
		$headers .= "X-MSMail-Priority: Normal\n";
		$headers .= "X-Mailer: php\n";
		$headers .= "From: \"".$_POST['register_name']."\" <".$_POST['register_mail'].">\n";

		//echo '<br>'.__('You have entered the following email address: ').'<b> '.$_POST['register_sender'].'</b><br>';
		//$position = strpos($_POST['register_sender'],"@");
		//if ($position<1){ echo '<font color="red">'.__('The email address you entered doesn\'t seem to be a valid email address!').'</font><br>'; }
		//echo '<b>'.__('If you do not enter a valid email address, unfortunately I cannot answer you!').'</b><br>';
		//echo __('Message: ').'<br>'.$_POST['register_text'];

		@$mail = mail($register_address, $register_subject, $register_message, $headers);
		//if($mail){
		//	echo ("<br>".__('E-mail sent!'));
		//}
		//else{
		//	echo "<br><b>".__('Sending e-mail failed!')."</b><br>";
		//}
	}
	else{
		echo '<h2>'.$error.'</h2>';
	}

}
else{
	$email='';
	if (isset($dataDb->tree_email)) $email=$dataDb->tree_email; // Used in older HuMo-gen versions. Backwards compatible...
	if ($humo_option["general_email"]) $email=$humo_option["general_email"];
	if ($email!=''){
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

		print '<br><form id="form_id" method="post" action="'.$_SERVER['PHP_SELF'].'" accept-charset = "utf-8" onsubmit="javascript:return validate(\'form_id\',\'register_mail\');">';

		print '<table align="center" class="humo">';
		print '<tr><th class="fonts" colspan="2">'.__('User registration form').'</th></tr>';

		$register_name=''; if (isset($_POST['register_name'])){ $register_name=$_POST['register_name']; }
		print '<tr><td>'.__('Username').':</td><td><input type="text" class="fonts" name="register_name" size="40" style="background-color:#FFFFFF" value="'.$register_name.'"></td></tr>';

		$register_password=''; if (isset($_POST['register_password'])){ $register_password=$_POST['register_password']; }
		print '<tr><td>'.__('Password').':</td><td><input type="password" class="fonts" name="register_password" size="40" style="background-color:#FFFFFF" value="'.$register_password.'"></td></tr>';

		$register_repeat_password=''; if (isset($_POST['register_repeat_password'])){ $register_repeat_password=$_POST['register_repeat_password']; }
		print '<tr><td>'.__('Repeat password').':</td><td><input type="password" class="fonts" name="register_repeat_password" size="40" style="background-color:#FFFFFF" value="'.$register_repeat_password.'"></td></tr>';

		$register_mail=''; if (isset($_POST['register_mail'])){ $register_mail=$_POST['register_mail']; }
		print '<tr><td>'.__('FULL e-mail address: ').'</td><td><input type="text" class="fonts" id="register_mail" name="register_mail" value="'.$register_mail.'" size="40" style="background-color:#FFFFFF"> </td></tr>';

		//$register_subject=''; if (isset($_POST['register_subject'])){ $register_subject=$_POST['register_subject']; }
		//print '<tr><td>'.__('Subject:').'</td><td><input type="text" class="fonts" name="register_subject" size="80" style="background-color:#FFFFFF" value="'.$register_subject.'"></td></tr>';

		$register_text=''; if (isset($_POST['register_text'])){ $register_text=$_POST['register_text']; }
		print '<tr><td>'.__('Message: ').'</td><td><textarea name="register_text" ROWS="5" COLS="40" class="fonts">'.$register_text.'</textarea></td></tr>';

		if ($humo_option["registration_use_spam_question"]=='y'){
			echo '<tr><td>'.__('Please answer the block-spam-question:').'</td>';
			echo '<td>'.$humo_option["block_spam_question"].'<br>';
			echo '<input type="text" class="fonts" name="register_block_spam" size="80" style="background-color:#FFFFFF"></td></tr>';
		}

		//print '<tr><td></td><td style="font-weight:bold;" class="fonts" align="left">'.__('Please enter a full and valid email address,<br>otherwise I cannot respond to your e-mail!').'</td></tr>';
		print '<tr><td></td><td><input class="fonts" type="submit" name="send_mail" value="'.__('Send').'"></td></tr>';
		print '</table>';
		print '</form>';
		
		if (isset($_POST['send_mail'])){
			echo '<h3 style="text-align:center";>'.__('Wrong answer to the block-spam question! Try again...').'</h3>';
		}
	}
	else{
		echo '<h2>'.__('The register function has been switched off!').'</h2>';
	}
}
include_once(CMS_ROOTPATH."footer.php");
?>