<?php
/* This file contains multiple parts:
 *
 * 1) First part: send standard mail or use HuMo-genealogy mail settings.
 *
 * 2) Second part: use this part to send mail using SMTP protocol (and Gmail).
 * 		Remove or disable first part if using second part!
 */


// *** PART 1: Settings to send standard PHP mail ***
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
require_once __DIR__.'/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__.'/../vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__.'/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
if ($humo_option["mail_auto"] == 'auto') {
    require __DIR__.'/../vendor/phpmailer/phpmailer/src/SMTP.php';
}

// *** Create a new PHPMailer instance ***
//$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer;

// *** 2019_12_14 added for UTF-8 mailings ***
$mail->CharSet = 'UTF-8';

// *** Send mail using SMTP protocol using HuMo-genealogy settings ***
if ($humo_option["mail_auto"] == 'auto') {
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
}

// *** End of part 1 ***


/*
    // *** PART 2: send mail using SMTP protocol ***
    // * SMTP needs accurate times, and the PHP time zone MUST be set
    // * This should be done in your php.ini, but this is how to do it if you don't have access to that
    date_default_timezone_set('Etc/UTC');

    // *** 2020_05_03: Newly added code for PHPMailer 6.x. NOT TESTED YET ***

    // Import PHPMailer classes into the global namespace
    // These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'phpmailer/src/PHPMailer.php';
    require 'phpmailer/src/Exception.php';
    require 'phpmailer/src/SMTP.php';

    // *** Create a new PHPMailer instance ***
    //$mail = new PHPMailer\PHPMailer\PHPMailer;
    $mail = new PHPMailer;

    // *** 2019_12_14 added for UTF-8 mailings ***
    $mail->CharSet = 'UTF-8';

    $mail->isSMTP();

    //Set the hostname of the mail server. For Gmail: smtp.gmail.com
    $mail->Host = 'mail.example.com';

    //Whether to use SMTP authentication. For Gmail: true.
    $mail->SMTPAuth = true;

    //Username to use for SMTP authentication. For Gmail: username@gmail.com
    $mail->Username = "yourname@example.com";

    //Password to use for SMTP authentication
    $mail->Password = "yourpassword";

    //Set the SMTP port number - likely to be 25, 465 or 587. For Gmail use: 587.
    $mail->Port = 25;

    //Set the encryption system to use - ssl (deprecated) or tls. For Gmail: tls.
    $mail->SMTPSecure = 'tls';

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;

    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';
*/
