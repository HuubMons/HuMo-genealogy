<?php
include_once("header.php");
include_once (CMS_ROOTPATH."menu.php");

// *** Check block_spam_answer ***
$mail_allowed=false;
if (isset($_POST['send_mail'])){
	if (isset($_POST['mail_block_spam']) AND strtolower($_POST['mail_block_spam'])==strtolower($humo_option["block_spam_answer"])){ $mail_allowed=true; }
}
if ($humo_option["use_spam_question"]!='y'){
	$mail_allowed=true;
}

if (isset($_POST['send_mail']) AND $mail_allowed==true){
	$mail_address=$dataDb->tree_email;

	$treetext=show_tree_text($_SESSION['tree_id'], $selected_language);
	$mail_subject = sprintf(__('%s Mail form.'),'HuMo-genealogy');
	$mail_subject .=" (".$treetext['name']."): ".$_POST['mail_subject']."\n";

	// *** It's better to use plain text in the subject ***
	$mail_subject=strip_tags($mail_subject,ENT_QUOTES);

	$mail_message = sprintf(__('Message sent through %s from the website.'),'HuMo-genealogy');
	$mail_message .="<br>\n";

	$mail_message .="<br>\n";
	$mail_message .=__('Name').':'.$_POST['mail_name']."<br>\n";
	$mail_message .=__('E-mail').": <a href='mailto:".$_POST['mail_sender']."'>".$_POST['mail_sender']."</a><br>\n";
	if (isset($_SESSION['save_last_visitid'])){
		$mail_message.=__('Last visited family:')." <a href='http://".$_SESSION['save_last_visitid']."'>".$_SESSION['save_last_visitid']."</a><br>\n";
	}
	if (isset($_POST['newsletter'])){
		$mail_message.=__('Receive newsletter').': '.$_POST['newsletter']."<br>\n";
	}
	$mail_message .=$_POST['mail_text']."<br>\n";

	//$headers  = "MIME-Version: 1.0\n";
	//$headers .= "Content-type: text/html; charset=utf-8\n";
	//$headers .= "X-Priority: 3\n";
	//$headers .= "X-MSMail-Priority: Normal\n";
	//$headers .= "X-Mailer: php\n";
	// *** Removed "From e-mail address"! Some providers don't accept other e-mail addresses because safety reasons! ***
	//$headers .= "From: \"".$_POST['mail_name']."\"\n";
	//$headers .= "Reply-To: \"".$_POST['mail_name']."\" <".$_POST['mail_sender'].">\n";

	// *** REMARK: because of security, the mail adres and message entered by the visitor are not shown on screen anymore! ***
	//echo '<br>'.__('You have entered the following e-mail address: ').'<b> '.$_POST['mail_sender'].'</b><br>';
	$position = strpos($_POST['mail_sender'],"@");
	if ($position<1) echo '<font color="red">'.__('The e-mail address you entered doesn\'t seem to be a valid e-mail address!').'</font><br>';
	//echo '<b>'.__('If you do not enter a valid e-mail address, unfortunately I cannot answer you!').'</b><br>';
	//echo __('Message: ').'<br>'.$_POST['mail_text'];

	// *** Use PhpMailer to send mail ***
	include_once ('include/mail.php');

	// *** Set who the message is to be sent from ***
	$mail->setFrom($_POST['mail_sender'], $_POST['mail_name']);
	// *** Removed "From e-mail address"! Some providers don't accept other e-mail addresses because of safety reasons! ***
	//$mail->setFrom('', $_POST['mail_name']);

	//NEW:
	//$mail->AddReplyTo($_POST['mail_sender'], $_POST['mail_name']);

	// *** Set who the message is to be sent to ***
	//$mail->addAddress($mail_address, $mail_address);
	$mult = explode(",",$mail_address);
	foreach($mult AS $val) {
		$val = trim($val); // this way it will work both with "someone@gmail.com,other@gmail.com" and also "someone@gmail.com , other@gmail.com"
		$mail->addAddress($val,$val);
	}

	// *** Set the subject line ***
	$mail->Subject = $mail_subject;

	$mail->msgHTML($mail_message);
	// *** Replace the plain text body with one created manually ***
	//$mail->AltBody = 'This is a plain-text message body';

	if (!$mail->send()) {
		echo '<br><b>'.__('Sending e-mail failed!').' '. $mail->ErrorInfo.'</b>';
	} else {
		echo '<br><b>'.__('E-mail sent!').'</b><br>';
	}

}
else{

	if ($dataDb->tree_email){

		echo '<script type="text/javascript">';
		echo '
		function validate(form_id,mail_sender) {
			var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			var address = document.forms[form_id].elements[mail_sender].value;
			if(reg.test(address) == false) {
				alert(\'Invalid Email Address\');
				return false;
			}
		}
		';
		echo '</script>';

		echo '<br><form id="form_id" method="post" action="mailform.php" accept-charset = "utf-8" onsubmit="javascript:return validate(\'form_id\',\'mail_sender\');">';

		echo '<table align="center" class="humo">';
		echo '<tr class=table_headline><th class="fonts" colspan="2">'.__('Mail form').'</th></tr>';

		$mail_name=''; if (isset($_POST['mail_name'])){ $mail_name=$_POST['mail_name']; }
		echo '<tr><td>'.__('Name').':</td><td><input type="text" class="fonts" name="mail_name" size="40" style="background-color:#FFFFFF" value="'.$mail_name.'"></td></tr>';

		$mail_sender=''; if (isset($_POST['mail_sender'])){ $mail_sender=$_POST['mail_sender']; }
		echo '<tr><td>'.__('FULL e-mail address: ').'</td><td><input type="text" class="fonts" id="mail_sender" name="mail_sender" value="'.$mail_sender.'" size="40" style="background-color:#FFFFFF"><br>'.__('My response will be sent to this e-mail address!').'</td></tr>';

		$mail_subject=''; if (isset($_POST['mail_subject'])){ $mail_subject=$_POST['mail_subject']; }
		echo '<tr><td>'.__('Subject:').'</td><td><input type="text" class="fonts" name="mail_subject" size="40" style="background-color:#FFFFFF" value="'.$mail_subject.'"></td></tr>';

		$mail_text=''; if (isset($_POST['mail_text'])){ $mail_text=$_POST['mail_text']; }
		echo '<tr><td>'.__('Message: ').'</td><td><textarea name="mail_text" ROWS="10" COLS="29" class="fonts">'.$mail_text.'</textarea></td></tr>';

		if ($humo_option["use_newsletter_question"]=='y'){
			echo '<tr><td>'.__('Receive newsletter').'</td><td>
			<input type="radio" name="newsletter" value="Yes"> '.__('Yes').'<br>
			<input type="radio" name="newsletter" value="No" checked> '.__('No').'</td></tr>';
		}

		if ($humo_option["use_spam_question"]=='y'){
			echo '<tr><td>'.__('Please answer the block-spam-question:').'</td>';
			echo '<td>'.$humo_option["block_spam_question"].'<br>';
			echo '<input type="text" class="fonts" name="mail_block_spam" size="80" style="background-color:#FFFFFF"></td></tr>';
		}

		echo '<tr><td></td><td style="font-weight:bold;" class="fonts" align="left">'.__('Please enter a full and valid email address,<br>otherwise I cannot respond to your e-mail!').'</td></tr>';
		echo '<tr><td></td><td><input class="fonts" type="submit" name="send_mail" value="'.__('Send').'"></td></tr>';
		echo '</table>';
		echo '</form>';
		
		if (isset($_POST['send_mail'])){
			echo '<h3 style="text-align:center";>'.__('Wrong answer to the block-spam question! Try again...').'</h3>';
		}
	}
	else{
		echo '<h2>'.__('The e-mail function has been switched off!').'</h2>';
	}

}
include_once(CMS_ROOTPATH."footer.php");
?>