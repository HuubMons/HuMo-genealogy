<?php 
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/SMTP.php';
/* This file handles SMTP and GMail as email options
 * which can be set in administration - settings - email settings
*/
$mail = new PHPMailer;

// *** 2020_06_01 added for UTF-8 mailings ***
$mail->CharSet = 'UTF-8';

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
// date_default_timezone_set('Etc/UTC');
//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = $humo_option["smtp_debug"];

//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
$mail->Host = $humo_option["smtp_server"];
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = $humo_option["smtp_port"];
//Whether to use SMTP authentication
$mail->SMTPAuth = $humo_option["smtp_auth"];
//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = $humo_option["smtp_encryption"];
//Username to use for SMTP authentication
$mail->Username = $humo_option["email_user"];
//Password to use for SMTP authentication
$mail->Password = $humo_option["email_password"];
?>