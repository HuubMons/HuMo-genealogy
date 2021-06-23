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
	// *** Old code for PHPMailer 5.2.9 ***
	//require 'phpmailer/PHPMailerAutoload.php';
	// *** Create a new PHPMailer instance ***
	//$mail = new PHPMailer;

	// Import PHPMailer classes into the global namespace
	// These must be at the top of your script, not inside a function
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	require 'phpmailer/src/PHPMailer.php';
	require 'phpmailer/src/Exception.php';
	//require 'src/SMTP.php';

	// *** Create a new PHPMailer instance ***
	//$mail = new PHPMailer\PHPMailer\PHPMailer;
	$mail = new PHPMailer;
// *** End of part 1 ***


/*
	// *** 20 juli 2019 HM: This part was used for PHPMailer 5.2.9. To use SMTP for PHPMailer 6.x the code below must be updated ***

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
	// *** 20 juli 2019 HM: This part was used for PHPMailer 5.2.9. To use SMTP for PHPMailer 6.x the code below must be updated ***

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