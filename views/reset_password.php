<?php

/**
 * Original password retrieval code from: http://www.plus2net.com/
 * 25-08-2024 Huub: rebuild script.
 */

if ($user['group_menu_login'] != 'j') {
    echo 'Access to this page is blocked.';
    exit;
}



// TODO: process text in variable.
if (isset($_POST['password']) && $_POST['password'] != '' && !$resetpassword['message_password']) {
    // *** Update the new password now (and use salted password) ***
    $hashToStoreInDb = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $count = $dbh->prepare("UPDATE humo_users SET user_password_salted = :password, user_password = '' WHERE user_id = :userid");
    $count->bindValue(':password', $hashToStoreInDb, PDO::PARAM_STR);
    $count->bindValue(':userid', $resetpassword['userid'], PDO::PARAM_INT);
    $count->execute();

    $no = $count->rowCount();
    if ($no == 1) {
        $tm = time();
        // Update the key so it can't be used again.
        $count = $dbh->prepare("UPDATE humo_pw_retrieval SET retrieval_status='done'
            WHERE retrieval_pkey=:pkey AND retrieval_userid=:userid AND retrieval_status='pending'");
        $count->bindValue(':pkey', $resetpassword['activation_key'], PDO::PARAM_STR);
        $count->bindValue(':userid', $resetpassword['userid'], PDO::PARAM_INT);
        $count->execute();
?>
        <div class="alert alert-success me-2" role="alert"><?= __('Your new password has been stored successfully'); ?></div>
    <?php } else { ?>
        <div class="alert alert-danger me-2" role="alert"><?= __('Failed to store new password.') . '&nbsp;' . __('Please contact the site owner.'); ?></div>
    <?php
    }
}



$path_reset_password = $processLinks->get_link($uri_path, 'reset_password');



// *** Problem in link from mail ***
if ($resetpassword['message_activation']) {
    ?>
    <div class="alert alert-danger me-2" role="alert"><?= __('Activation failed') ?></div>
<?php
    exit;
}

// *** Form to enter mail address in order to receive reset link ***
// *** An e-mail address is necessary for password retrieval ***
if (isset($_POST['forgotpw']) || $resetpassword['check_input_msg']) {
?>
    <h1 class="my-4"><?= __('Password retrieval'); ?></h1>

    <?php if ($resetpassword['check_input_msg']) { ?>
        <div class="alert alert-warning me-2" role="alert"><?= $resetpassword['check_input_msg']; ?></div>
    <?php } ?>

    <form name="pw_email_form" method="post" action="<?= $path_reset_password; ?>">
        <div class="container">
            <div class="row mb-2">
                <div class="col-md-2"></div>
                <div class="col-md-3">
                    <?= __('Email'); ?>
                </div>
                <div class="col-md-4">
                    <input type="email" name="user_mail" size="20" maxlength="50" class="form-control">
                </div>
            </div>

            <?php if ($humo_option["registration_use_spam_question"] == 'y') { ?>
                <div class="row mb-2">
                    <div class="col-md-2"></div>
                    <div class="col-md-3">
                        <?= __('Please answer the block-spam-question:'); ?>
                    </div>
                    <div class="col-md-4">
                        <?= $humo_option["block_spam_question"]; ?><br>
                        <input type="text" name="register_block_spam" size="80" class="form-control">
                    </div>
                </div>
            <?php } ?>

            <div class="row mb-2">
                <div class="col-md-2"></div>
                <div class="col-md-3"></div>
                <div class="col-md-4">
                    <input type="submit" name="Submit" value="<?= __('Send'); ?>" class="btn btn-success">
                </div>
            </div>
        </div>
    </form>
<?php
}

// *** Process email address and username, create random key and mail its link to user ***
elseif (isset($_POST['user_mail']) && !$resetpassword['check_input_msg']) {
?>
    <br>
    <?php
    //if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //    $msg = __('Your email address is not correct') . "<br>";
    //    $status = "NOTOK";
    //}

    $countmail = $dbh->prepare("SELECT user_id, user_mail, user_name FROM humo_users WHERE user_mail=:email");
    $countmail->bindValue(':email', $_POST['user_mail'], PDO::PARAM_STR);
    $countmail->execute();
    $row = $countmail->fetch(PDO::FETCH_OBJ);

    // *** Check if activation is pending ***
    $tm = time() - 86400; // Time in last 24 hours
    $count = $dbh->prepare("SELECT retrieval_userid FROM humo_pw_retrieval WHERE retrieval_userid = '" . $row->user_id . "' and retrieval_time > '" . $tm . "' and retrieval_status='pending'");
    $count->execute();
    $no = $count->rowCount();
    if ($no == 1) {
    ?>
        <div class="alert alert-warning me-2" role="alert">
            <?= __('Your password activation key was already sent to your email address, please check your inbox and spam folder'); ?>
        </div>
    <?php
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

    //$humo_option = $this->humo_option; // Used in mail.php
    include_once(__DIR__ . '/../include/mail.php');

    // *** Get mail for password retrieval ***
    $mail_address = $humo_option["password_retrieval"];

    $mail_message = __('This is in response to your request for password reset at ') . $resetpassword['site_url'];

    if ($humo_option["url_rewrite"] == "j") {
        $resetpassword['site_url'] .='?';
    }
    else{
        $resetpassword['site_url'] .='&amp;';
    }
    $resetpassword['site_url'] .= "ak=$key&userid=$row->user_id";

    $mail_message .= '<br>' . __('Username') . ":" . $row->user_name . '<br>';
    $mail_message .= __('To reset your password, please visit this link or copy and paste this link in your browser window ') . ":";
    $mail_message .= '<br><br><a href="' . $resetpassword['site_url'] . '">' . $resetpassword['site_url'] . '</a><br>';

    // *** Set the reply address ***
    $mail->AddReplyTo($mail_address, $mail_address);

    // *** Set who the message is sent from (this will automatically be set to your server's mail to prevent false "phishing" alarms)***
    //$mail->setFrom($mail_address, $mail_address);
    if ($humo_option["email_sender"] && filter_var($humo_option["email_sender"], FILTER_VALIDATE_EMAIL)) {
        // *** Some providers don't accept other e-mail addresses because of safety reasons! ***
        $mail->setFrom($humo_option["email_sender"], $humo_option["email_sender"]);
    } else {
        $mail->setFrom($mail_address, $mail_address);
    }

    // *** Set who the message is to be sent to. Use mail address from database ***
    $mail->addAddress($row->user_mail, $row->user_mail);

    // *** Set the subject line ***
    $mail->Subject = __('Your request for password retrieval');

    $mail->msgHTML($mail_message);
    // *** Replace the plain text body with one created manually ***
    //$mail->AltBody = 'This is a plain-text message body';

    if (!$mail->send()) {
    ?>
        <div class="alert alert-danger me-2" role="alert"><?= __('We encountered a system problem in sending reset link to your email address.') . '&nbsp;' . __('Please contact the site owner.'); ?></div>
    <?php } else { ?>
        <div class="alert alert-success me-2" role="alert"><?= __('Your reset link was sent to your email address. Please check your mail in a few minutes.'); ?></div>
    <?php } ?>
<?php
}

// *** Enter new password 2x (after reset link was used) ***
elseif ((isset($_GET['ak']) && $_GET['ak'] != '') || $resetpassword['message_password']) {
?>
    <h1 class="my-4"><?= __('New Password'); ?></h1>

    <?php if ($resetpassword['message_password']) { ?>
        <div class="alert alert-danger me-2" role="alert"><?= $resetpassword['message_password']; ?></div>
    <?php } ?>

    <form action="<?= $path_reset_password; ?>" method="post">
        <input type="hidden" name="ak" value="<?= $resetpassword['activation_key']; ?>">
        <input type="hidden" name="userid" value="<?= $resetpassword['userid']; ?>">

        <div class="container">
            <div class="row mb-2">
                <label for="password" class="col-sm-3 col-form-label"><?= __('New Password'); ?></label>
                <div class="col-md-3"><input type="password" name="password" id="password" class="form-control"></div>
            </div>

            <div class="row mb-2">
                <label for="password2" class="col-sm-3 col-form-label"><?= __('Re-enter new Password'); ?></label>
                <div class="col-md-3"><input type="password" name="password2" id="password2" class="form-control"></div>
            </div>

            <div class="row mb-2">
                <div class="col-sm-3"></div>
                <div class="col-md-3"><input type="submit" value="<?= __('Submit new Password'); ?>" class="btn btn-success"></div>
            </div>
        </div>
    </form>
<?php
}
