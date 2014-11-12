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

	$treetext=show_tree_text($_SESSION['tree_prefix'], $selected_language);
	$mail_subject="HuMo-gen ".__('Mail form')." (".$treetext['name']."): ".$_POST['mail_subject']."\n";

	// *** It's better to use plain text in the subject ***
	$mail_subject=strip_tags($mail_subject,ENT_QUOTES);

	$mail_message =__('Message sent through HuMo-gen from the website.')."<br>\n";
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

	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=utf-8\n";
	$headers .= "X-Priority: 3\n";
	$headers .= "X-MSMail-Priority: Normal\n";
	$headers .= "X-Mailer: php\n";
	//$headers .= "From: \"".$_POST['mail_name']."\" <".$_POST['mail_sender'].">\n";
	$headers .= "From: \"".$_POST['mail_name']."\"\n";
	$headers .= "Reply-To: \"".$_POST['mail_name']."\" <".$_POST['mail_sender'].">\n";

	echo '<br>'.__('You have entered the following email address: ').'<b> '.$_POST['mail_sender'].'</b><br>';
	$position = strpos($_POST['mail_sender'],"@");
	if ($position<1){ echo '<font color="red">'.__('The email address you entered doesn\'t seem to be a valid email address!').'</font><br>'; }
	echo '<b>'.__('If you do not enter a valid email address, unfortunately I cannot answer you!').'</b><br>';
	echo __('Message: ').'<br>'.$_POST['mail_text'];

	@$mail = mail($mail_address, $mail_subject, $mail_message, $headers);
	//return ($mail);
	if($mail){
		echo ("<br>".__('E-mail sent!'));
	}
	else{
		echo "<br><b>".__('Sending e-mail failed!')."</b><br>";
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

		print '<br><form id="form_id" method="post" action="'.$_SERVER['PHP_SELF'].'" accept-charset = "utf-8" onsubmit="javascript:return validate(\'form_id\',\'mail_sender\');">';

		print '<table align="center" class="humo">';
		print '<tr class=table_headline><th class="fonts" colspan="2">'.__('Mail form').'</th></tr>';

		$mail_name=''; if (isset($_POST['mail_name'])){ $mail_name=$_POST['mail_name']; }
		print '<tr><td>'.__('Name').':</td><td><input type="text" class="fonts" name="mail_name" size="40" style="background-color:#FFFFFF" value="'.$mail_name.'"></td></tr>';

		$mail_sender=''; if (isset($_POST['mail_sender'])){ $mail_sender=$_POST['mail_sender']; }
		print '<tr><td>'.__('FULL e-mail address: ').'</td><td><input type="text" class="fonts" id="mail_sender" name="mail_sender" value="'.$mail_sender.'" size="40" style="background-color:#FFFFFF"> '.__('My response will be sent to this e-mail address!').'</td></tr>';

		$mail_subject=''; if (isset($_POST['mail_subject'])){ $mail_subject=$_POST['mail_subject']; }
		print '<tr><td>'.__('Subject:').'</td><td><input type="text" class="fonts" name="mail_subject" size="80" style="background-color:#FFFFFF" value="'.$mail_subject.'"></td></tr>';

		$mail_text=''; if (isset($_POST['mail_text'])){ $mail_text=$_POST['mail_text']; }
		print '<tr><td>'.__('Message: ').'</td><td><textarea name="mail_text" ROWS="10" COLS="40" class="fonts">'.$mail_text.'</textarea></td></tr>';

		if ($humo_option["use_newsletter_question"]=='y'){
			print '<tr><td>'.__('Receive newsletter').'</td><td>
			<input type="radio" name="newsletter" value="Yes"> Ja<br>
			<input type="radio" name="newsletter" value="No" checked> Nee</td></tr>';
		}

		if ($humo_option["use_spam_question"]=='y'){
			echo '<tr><td>'.__('Please answer the block-spam-question:').'</td>';
			echo '<td>'.$humo_option["block_spam_question"].'<br>';
			echo '<input type="text" class="fonts" name="mail_block_spam" size="80" style="background-color:#FFFFFF"></td></tr>';
		}

		print '<tr><td></td><td style="font-weight:bold;" class="fonts" align="left">'.__('Please enter a full and valid email address,<br>otherwise I cannot respond to your e-mail!').'</td></tr>';
		print '<tr><td></td><td><input class="fonts" type="submit" name="send_mail" value="'.__('Send').'"></td></tr>';
		print '</table>';
		print '</form>';
		
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