<?php 

/* This file contains multiple parts:
 *
 * 1) First part: send standard mail.
 *
 * 2) Second part: use this part to send mail using SMTP protocol.
 * 		Remove or disable first part if using second part!
 *
 * 3) Third part: example of sending mail by SMTP using Gmail account.
 * 		Remove or disable first part if using third part!
 */

 
// *** PART 1: Settings to send standard PHP mail ***
	require 'phpmailer/PHPMailerAutoload.php';
	//Create a new PHPMailer instance
	$mail = new PHPMailer;
// *** End of part 1 ***


/*
	// *** PART 2: send mail using SMTP protocol ***
	//SMTP needs accurate times, and the PHP time zone MUST be set
	//This should be done in your php.ini, but this is how to do it if you don't have access to that
	date_default_timezone_set('Etc/UTC');
	require 'phpmailer/PHPMailerAutoload.php';

	//Create a new PHPMailer instance
	$mail = new PHPMailer;

	//Tell PHPMailer to use SMTP
	$mail->isSMTP();

	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;

	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';

	//Set the hostname of the mail server
	$mail->Host = "mail.example.com";

	//Set the SMTP port number - likely to be 25, 465 or 587
	$mail->Port = 25;

	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;

	//Username to use for SMTP authentication
	$mail->Username = "yourname@example.com";

	//Password to use for SMTP authentication
	$mail->Password = "yourpassword";
*/


/*
	// *** PART 2: send mail using SMTP protocol with Gmail account ***
	//SMTP needs accurate times, and the PHP time zone MUST be set
	//This should be done in your php.ini, but this is how to do it if you don't have access to that
	date_default_timezone_set('Etc/UTC');

	require 'phpmailer/PHPMailerAutoload.php';

	//Create a new PHPMailer instance
	$mail = new PHPMailer;

	//Tell PHPMailer to use SMTP
	$mail->isSMTP();

	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;

	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';

	//Set the hostname of the mail server
	$mail->Host = 'smtp.gmail.com';

	//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
	$mail->Port = 587;

	//Set the encryption system to use - ssl (deprecated) or tls
	$mail->SMTPSecure = 'tls';

	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;

	//Username to use for SMTP authentication - use full email address for gmail
	$mail->Username = "username@gmail.com";

	//Password to use for SMTP authentication
	$mail->Password = "yourpassword";
*/

?>