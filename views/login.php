<?php

/**
 * Front page login form.
 * Password retreival code from: http://www.plus2net.com/ 
 */

if ($user['group_menu_login'] != 'j') {
    echo 'Access to this page is blocked.';
    exit;
}

$path_tmp = $link_cls->get_link($uri_path, 'login');

// form to enter username and mail in order to receive reset link
// *** An e-mail address is necessary for password retreival, option Username is disabled ***
if (isset($_POST['forgotpw'])) {
?>
    <form name="pw_email_form" method="post" action="<?= $path_tmp; ?>">
        <br>
        <table class="humo" cellspacing="0" align="center">
            <tr class="table_headline">
                <th colspan="2"><?= __('Password retrieval'); ?></th>
            </tr>
            <tr>
                <td><?= __('Email'); ?>:</td>
                <td><input name="got_email" type="email" size="20" maxlength="50"></td>
            </tr>

            <?php if ($humo_option["registration_use_spam_question"] == 'y') { ?>
                <tr>
                    <td><?= __('Please answer the block-spam-question:'); ?></td>
                    <td><?= $humo_option["block_spam_question"]; ?><br>
                        <input type="text" name="register_block_spam" size="80" style="background-color:#FFFFFF">
                    </td>
                </tr>
            <?php } ?>

            <tr>
                <td><br></td>
                <td><input type="submit" name="Submit" value="<?= __('Send'); ?>"></td>
            </tr>
        </table>
    </form>
<?php
}

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
    //if($pw_username==''){ 
    //	$msg .=__('You have to enter your username')."<br>"; 
    //	$status= "NOTOK";
    //}

    if (isset($_POST['register_block_spam']) && strtolower($_POST['register_block_spam']) === strtolower($humo_option["block_spam_answer"])) {
        //$register_allowed=true;
    } else {
        $msg .= __('Wrong answer to the block-spam question! Try again...') . "<br>";
        $status = "NOTOK";
    }

    echo '<br><table class="humo" cellspacing="0" align="center">';

    if ($status === "OK") {
        $countmail = $dbh->prepare("SELECT user_id, user_mail, user_name FROM humo_users WHERE user_mail=:email");
        $countmail->bindValue(':email', $email, PDO::PARAM_STR);
        $countmail->execute();
        $row = $countmail->fetch(PDO::FETCH_OBJ);
        $no_mail = $countmail->rowCount();

        // *** Email address not found in database ***
        if ($no_mail == 0) {
            echo '<br><table class="humo" cellspacing="0" align="center">';
            echo '<tr class="table_headline"><th>' . __('Error') . '</th></tr>';
            echo '<tr><td style="font-weight:bold;color:red">';
            if ($no_mail == 0) { // mail doesn't exist, username does
                echo __('This email address was not found in our database.') . "&nbsp;" . __('Please contact the site owner.');
            }
            echo "</td></tr><tr><td style='text-align:center'><input type='button' value='" . __('Retry') . "' onClick='history.go(-1)'></td>";
            echo "</tr></table>";
            exit;
        }
        // *** Check if mail address is used multiple times ***
        elseif ($no_mail > 1) {
            echo '<br><table class="humo" cellspacing="0" align="center">';
            echo '<tr class="table_headline"><th>' . __('Error') . '</th></tr>';
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
            echo '<tr class="table_headline"><th>' . __('Notice') . '</th></tr>';
            echo '<tr><td style="font-weight:bold;color:red">';
            echo __('Your password activation key was already sent to your email address, please check your inbox and spam folder');
            echo "</td></tr></table>";
            exit;
        }

        // function to generate random number 
        function random_generator($digits)
        {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            return substr(str_shuffle($chars), 0, 10);
        }

        $key = random_generator(10);
        $key = md5($key);
        $tm = time();
        $sql = $dbh->prepare("insert into humo_pw_retrieval(retrieval_userid, retrieval_pkey,retrieval_time,retrieval_status) values('$row->user_id','$key','$tm','pending')");
        $sql->execute();

        include_once(__DIR__ . '/../include/mail.php');

        // *** Get mail for password retreival ***
        $mail_address = $humo_option["password_retreival"];

        $mail_message = __('This is in response to your request for password reset at ') . $site_url;

        $site_url .= "?ak=$key&userid=$row->user_id";

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
            echo '<tr class="table_headline"><th>' . __('Error') . '</th></tr>';
            echo '<tr><td style="font-weight:bold;color:red">';
            echo $mail->ErrorInfo . '<br>' . __('We encountered a system problem in sending reset link to your email address.') . '&nbsp;' . __('Please contact the site owner.');
        } else {
            echo '<tr class="table_headline"><th>' . __('Success') . '</th></tr>';
            echo '<tr><td style="font-weight:bold">';
            echo __('Your reset link was sent to your email address. Please check your mail in a few minutes.');
        }
    } else {
        echo '<tr class="table_headline"><th>' . __('Error') . '</th></tr>';
        echo '<tr><td style="font-weight:bold;color:red">';
        echo $msg . "</td></tr><tr><td style='text-align:center'><input type='button' value='" . __('Retry') . "' onClick='history.go(-1)'>";
    }
    echo '</td></tr></table>';
}

//form to enter new password 2x (after reset link was used)
elseif (isset($_GET['ak']) && $_GET['ak'] != '') {
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

    <table class='humo' cellspacing='0' align='center'><tr class='table_headline'><th colspan='2'>" . __('New Password') . "</th></tr>
        <tr><td>" . __('New Password') . "  
        </td><td><input type ='password' class='bginput' name='password'></td></tr>
        <tr><td>" . __('Re-enter new Password') . "  
        </td><td><input type ='password' class='bginput' name='password2' ></td></tr>";

    //TODO check this code
    //if ($humo_option["registration_use_spam_question"]=='y'){
    echo '<tr><td>' . __('Please answer the block-spam-question:') . '</td>';
    echo '<td>' . $humo_option["block_spam_question"] . '<br>';
    echo '<input type="text" name="register_block_spam" size="80" style="background-color:#FFFFFF"></td></tr>';
    //}

    echo "<tr><td></td><td><input type=submit value='" . __('Submit new Password') . "'></td></tr>
    </table></form>";
}

// store new password and display success or error message 
elseif (isset($_POST['ak']) && $_POST['ak'] != '') {
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
        echo '<tr class="table_headline"><th>' . __('Error') . '</th></tr>';
        echo '<tr><td style="font-weight:bold;color:red">';
        echo __('Password activation failed.') . '&nbsp;' . __('Please contact the site owner.');
        echo '</td></tr></table>';
        exit;
    }

    if (isset($todo) && $todo == "new-password") {
        //Setting flags for checking
        $status = "OK";
        $msg = "";

        if (strlen($password) < 4 || strlen($password) > 15) {
            $msg = $msg . __('Password must be at least 4 char and maximum 15 char long') . "<br>";
            $status = "NOTOK";
        }

        if ($password <> $password2) {
            $msg = $msg . __('The passwords don\'t match!') . "<br>";
            $status = "NOTOK";
        }

        if (isset($_POST['register_block_spam']) && strtolower($_POST['register_block_spam']) === strtolower($humo_option["block_spam_answer"])) {
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
                echo '<tr class="table_headline"><th>' . __('Success') . '</th></tr>';
                echo '<tr><td style="font-weight:bold">';
                echo __('Your new password has been stored successfully');
            } else {
                echo '<tr class="table_headline"><th>' . __('Error') . '</th></tr>';
                echo '<tr><td style="font-weight:bold;color:red">';
                echo __('Failed to store new password.') . '&nbsp;' . __('Please contact the site owner.');
            }
            echo '</td></tr></table>';
        } // end of if status <> 'OK'
    }
}

// show initial login screen with "Forgot password" button
else {
?>
    <h1 class="my-4"><?= __('Login'); ?></h1>

    <div class="container">

        <?php if ($fault == true) { ?>
            <div class="alert alert-warning">
                <strong><?= __('No valid username or password.'); ?></strong>
            </div>
        <?php } ?>

        <br>

        <form action="<?= $path_tmp; ?>" method="post">
            <div class="mb-2 row">
                <label for="username" class="col-sm-3 col-form-label"><?= __('Username or e-mail address'); ?></label>
                <div class="col-sm-5">
                    <input type="text" id="username" class="form-control" name="username">
                </div>
            </div>

            <div class="mb-2 row">
                <label for="password" class="col-sm-3 col-form-label"><?= __('Password'); ?></label>
                <div class="col-sm-5">
                    <input type="password" id="password" class="form-control" name="password">
                </div>
            </div>

            <div class="mb-2 row">
                <label for="2fa_code" class="col-sm-3 col-form-label"><?= __('Two factor authentication (2FA) code if needed'); ?></label>
                <div class="col-sm-5">
                    <input type="text" id="2fa_code" name="2fa_code" class="form-control">
                </div>
            </div>

            <div class="mb-2 row">
                <label for="send_mail" class="col-sm-3 col-form-label"></label>
                <div class="col-sm-8">
                    <input type="submit" class="btn btn-success" name="send_mail" value="<?= __('Login'); ?>">
                </div>
            </div>
        </form>

        <?php
        // *** Only use password retreival option if sender mail is set in admin settings ***
        if ($humo_option["password_retreival"]) {
            $mail_address = $humo_option["password_retreival"];
            // *** Check if this is a valid a-mail address ***
            if (filter_var($mail_address, FILTER_VALIDATE_EMAIL)) {
        ?>
                <br>
                <div class="center">
                    <form name="forget_form" method="post" action="<?= $path_tmp; ?>">
                        <input type="hidden" name="forgotpw" value="1">
                        <input type="submit" class="btn btn-success" name="Submit" value="<?= __('Forgot password'); ?>">
                    </form>
                </div>
        <?php
            }
        }
        ?>
    </div>
<?php
}  // end of else (else show login screen)
?>
<br><br>