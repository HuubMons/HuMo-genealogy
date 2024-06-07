<?php
// *** Check block_spam_answer ***
$mail_allowed = false;
$correct_spam_answer = true;
if (isset($_POST['send_mail'])) {
    if (isset($_POST['mail_block_spam']) && strtolower($_POST['mail_block_spam']) == strtolower($humo_option["block_spam_answer"])) {
        $mail_allowed = true;
        $correct_spam_answer = true;
    } else {
        $correct_spam_answer = false;
    }
}

$correct_mail = true;
if (isset($_POST['mail_sender'])) {
    $position = strpos($_POST['mail_sender'], "@");
    if ($position < 1) {
        $correct_mail = false;
        $mail_allowed = false;
    }
}

if ($humo_option["use_spam_question"] != 'y') {
    $mail_allowed = true;
}


if (isset($_POST['send_mail']) && $mail_allowed == true) {
    $mail_address = $dataDb->tree_email;

    $treetext = show_tree_text($_SESSION['tree_id'], $selected_language);
    $mail_data["subject"] = sprintf(__('%s Mail form.'), 'HuMo-genealogy');
    $mail_data["subject"] .= " (" . $treetext['name'] . "): " . $_POST['mail_subject'] . "\n";

    // *** It's better to use plain text in the subject ***
    $mail_data["subject"] = strip_tags($mail_data["subject"], ENT_QUOTES);

    $mail_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
    $mail_message .= "<br>\n";

    $mail_message .= "<br>\n";
    $mail_message .= __('Name') . ':' . $_POST['mail_name'] . "<br>\n";
    $mail_message .= __('E-mail') . ": <a href='mailto:" . $_POST['mail_sender'] . "'>" . $_POST['mail_sender'] . "</a><br>\n";
    if (isset($_SESSION['save_last_visitid'])) {
        $mail_message .= __('Last visited family:') . " <a href='http://" . $_SESSION['save_last_visitid'] . "'>" . $_SESSION['save_last_visitid'] . "</a><br>\n";
    }
    if (isset($_POST['newsletter'])) {
        $mail_message .= __('Receive newsletter') . ': ' . $_POST['newsletter'] . "<br>\n";
    }
    $mail_message .= $_POST['mail_text'] . "<br>\n";

    //$headers  = "MIME-Version: 1.0\n";
    //$headers .= "Content-type: text/html; charset=utf-8\n";
    //$headers .= "X-Priority: 3\n";
    //$headers .= "X-MSMail-Priority: Normal\n";
    //$headers .= "X-Mailer: php\n";
    // *** Removed "From e-mail address"! Some providers don't accept other e-mail addresses because safety reasons! ***
    //$headers .= "From: \"".$_POST['mail_name']."\"\n";
    //$headers .= "Reply-To: \"".$_POST['mail_name']."\" <".$_POST['mail_sender'].">\n";

    // *** REMARK: because of security, the mail address and message entered by the visitor are not shown on screen anymore! ***
    //echo '<br>'.__('You have entered the following e-mail address: ').'<b> '.$_POST['mail_sender'].'</b><br>';
    //echo __('Message: ').'<br>'.$_POST['mail_text'];

    // *** Use PhpMailer to send mail ***
    include_once(__DIR__ . '/../include/mail.php');

    // *** Set who the message is to be sent from ***
    $mail->setFrom($_POST['mail_sender'], $_POST['mail_name']);
    // *** Removed "From e-mail address"! Some providers don't accept other e-mail addresses because of safety reasons! ***
    //$mail->setFrom('', $_POST['mail_name']);

    //NEW:
    //$mail->AddReplyTo($_POST['mail_sender'], $_POST['mail_name']);

    // *** Set who the message is to be sent to ***
    //$mail->addAddress($mail_address, $mail_address);
    $mult = explode(",", $mail_address);
    foreach ($mult as $val) {
        $val = trim($val); // this way it will work both with "someone@gmail.com,other@gmail.com" and also "someone@gmail.com , other@gmail.com"
        $mail->addAddress($val, $val);
    }

    // *** Set the subject line ***
    $mail->Subject = $mail_data["subject"];

    $mail->msgHTML($mail_message);
    // *** Replace the plain text body with one created manually ***
    //$mail->AltBody = 'This is a plain-text message body';

    if (!$mail->send()) {
?>
        <div class="alert alert-danger" role="alert">
            <?= __('Sending e-mail failed!') . ' ' . $mail->ErrorInfo; ?>
        </div>
    <?php } else { ?>
        <div class="alert alert-info" role="alert">
            <?= __('E-mail sent!'); ?>
        </div>
    <?php
    }
} else {
    if ($dataDb->tree_email) {
        $mail_data["name"] = '';
        if (isset($_POST['mail_name'])) {
            $mail_data["name"] = $_POST['mail_name'];
        }

        $mail_data["sender"] = '';
        if (isset($_POST['mail_sender'])) {
            $mail_data["sender"] = $_POST['mail_sender'];
        }

        $mail_data["subject"] = '';
        if (isset($_POST['mail_subject'])) {
            $mail_data["subject"] = $_POST['mail_subject'];
        }

        $mail_data["text"] = '';
        if (isset($_POST['mail_text'])) {
            $mail_data["text"] = $_POST['mail_text'];
        }

        $path = 'index.php?page=mailform';
        if ($humo_option["url_rewrite"] == "j") $path = 'mailform';
    ?>

        <h1 class="my-4"><?= __('Mail form'); ?></h1>

        <?php if (!$correct_spam_answer) { ?>
            <div class="alert alert-info" role="alert">
                <?= __('Wrong answer to the block-spam question! Try again...'); ?>
            </div>
        <?php } ?>

        <?php if (!$correct_mail) { ?>
            <div class="alert alert-info" role="alert">
                <?= __('The e-mail address you entered doesn\'t seem to be a valid e-mail address!'); ?>
            </div>
        <?php } ?>

        <div class="container">
            <form action="<?= $path; ?>" method="post">
                <div class="mb-2 row">
                    <label for="name" class="col-sm-3 col-form-label"><?= __('Name'); ?></label>
                    <div class="col-sm-5">
                        <input type="text" id="fname" class="form-control" name="mail_name" placeholder="<?= __('Name'); ?>" value="<?= $mail_data["name"]; ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="mail_sender" class="col-sm-3 col-form-label"><?= __('E-mail address'); ?></label>
                    <div class="col-sm-5">
                        <input type="email" id="lname" class="form-control" name="mail_sender" placeholder="<?= __('E-mail address'); ?>" value="<?= $mail_data["sender"]; ?>" required>
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="subject" class="col-sm-3 col-form-label"><?= __('Subject'); ?></label>
                    <div class="col-sm-5">
                        <input type="text" id="lname" class="form-control" name="mail_subject" placeholder="<?= __('Subject'); ?>" value="<?= $mail_data["subject"]; ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="message" class="col-sm-3 col-form-label"><?= __('Message'); ?></label>
                    <div class="col-sm-5">
                        <textarea id="message" class="form-control" name="mail_text" placeholder="<?= __('Message'); ?>" style="height:200px"><?= $mail_data["text"]; ?></textarea>
                    </div>
                </div>

                <?php if ($humo_option["use_newsletter_question"] == 'y') { ?>
                    <div class="mb-2 row">
                        <label for="newsletter" class="col-sm-3 col-form-label"><?= __('Receive newsletter'); ?></label>
                        <div class="col-sm-5">
                            <input type="radio" class="form-check-input my-1" name="newsletter" value="Yes"> <?= __('Yes'); ?><br>
                            <input type="radio" class="form-check-input my-1" name="newsletter" value="No" checked> <?= __('No'); ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($humo_option["use_spam_question"] == 'y') { ?>
                    <div class="mb-2 row">
                        <label for="mail_block_spam" class="col-sm-3 col-form-label"><?= __('Please answer the block-spam-question:'); ?></label>
                        <div class="col-sm-5">
                            <?= $humo_option["block_spam_question"]; ?>
                            <input type="text" id="lname" class="form-control" name="mail_block_spam">
                        </div>
                    </div>
                <?php } ?>

                <br>
                <div class="mb-2 row">
                    <label for="2fa_code" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-7">
                        <input type="submit" class="col-sm-2 btn btn-success" name="send_mail" value="<?= __('Send'); ?>">
                    </div>
                </div>
            </form>
        </div>

<?php
    } else {
        echo '<h2>' . __('The e-mail function has been switched off!') . '</h2>';
    }
}
