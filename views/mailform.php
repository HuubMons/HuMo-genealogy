<?php
// *** Check block_spam_answer ***
$mail_allowed = false;
if (isset($_POST['send_mail'])) {
    if (isset($_POST['mail_block_spam']) and strtolower($_POST['mail_block_spam']) == strtolower($humo_option["block_spam_answer"])) {
        $mail_allowed = true;
    }
}
if ($humo_option["use_spam_question"] != 'y') {
    $mail_allowed = true;
}

if (isset($_POST['send_mail']) and $mail_allowed == true) {
    $mail_address = $dataDb->tree_email;

    $treetext = show_tree_text($_SESSION['tree_id'], $selected_language);
    $mail_subject = sprintf(__('%s Mail form.'), 'HuMo-genealogy');
    $mail_subject .= " (" . $treetext['name'] . "): " . $_POST['mail_subject'] . "\n";

    // *** It's better to use plain text in the subject ***
    $mail_subject = strip_tags($mail_subject, ENT_QUOTES);

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

    // *** REMARK: because of security, the mail adres and message entered by the visitor are not shown on screen anymore! ***
    //echo '<br>'.__('You have entered the following e-mail address: ').'<b> '.$_POST['mail_sender'].'</b><br>';
    $position = strpos($_POST['mail_sender'], "@");
    if ($position < 1) echo '<font color="red">' . __('The e-mail address you entered doesn\'t seem to be a valid e-mail address!') . '</font><br>';
    //echo '<b>'.__('If you do not enter a valid e-mail address, unfortunately I cannot answer you!').'</b><br>';
    //echo __('Message: ').'<br>'.$_POST['mail_text'];

    // *** Use PhpMailer to send mail ***
    include_once('include/mail.php');

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
    $mail->Subject = $mail_subject;

    $mail->msgHTML($mail_message);
    // *** Replace the plain text body with one created manually ***
    //$mail->AltBody = 'This is a plain-text message body';

    if (!$mail->send()) {
        echo '<br><b>' . __('Sending e-mail failed!') . ' ' . $mail->ErrorInfo . '</b>';
    } else {
        echo '<br><b>' . __('E-mail sent!') . '</b><br>';
    }
} else {
    if ($dataDb->tree_email) {
        $mail_name = '';
        if (isset($_POST['mail_name'])) {
            $mail_name = $_POST['mail_name'];
        }

        $mail_sender = '';
        if (isset($_POST['mail_sender'])) {
            $mail_sender = $_POST['mail_sender'];
        }

        $mail_subject = '';
        if (isset($_POST['mail_subject'])) {
            $mail_subject = $_POST['mail_subject'];
        }

        $mail_text = '';
        if (isset($_POST['mail_text'])) {
            $mail_text = $_POST['mail_text'];
        }

        $path = 'index.php?page=mailform';
        if ($humo_option["url_rewrite"] == "j") $path = 'mailform';
?>

        <h1><?= __('Mail form'); ?></h1>

        <!-- Layout: https://www.w3schools.com/csS/tryit.asp?filename=trycss_form_responsive -->
        <div class="container">
            <form action="<?= $path; ?>" method="post">
                <div class="row">
                    <div class="col-25">
                        <label for="name"><?= __('Name'); ?></label>
                    </div>
                    <div class="col-75">
                        <input type="text" id="fname" class="input" name="mail_name" placeholder="<?= __('Name'); ?>" value="<?= $mail_name; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-25">
                        <label for="mail_sender"><?= __('E-mail address'); ?></label>
                    </div>
                    <div class="col-75">
                        <input type="email" id="lname" class="input" name="mail_sender" placeholder="<?= __('E-mail address'); ?>" value="<?= $mail_sender; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-25">
                        <label for="subject"><?= __('Subject'); ?></label>
                    </div>
                    <div class="col-75">
                        <input type="text" id="lname" class="input" name="mail_subject" placeholder="<?= __('Subject'); ?>" value="<?= $mail_subject; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-25">
                        <label for="message"><?= __('Message'); ?></label>
                    </div>
                    <div class="col-75">
                        <textarea id="message" class="input" name="mail_text" placeholder="<?= __('Message'); ?>" style="height:200px"><?= $mail_text; ?></textarea>
                    </div>
                </div>

                <?php if ($humo_option["use_newsletter_question"] == 'y') { ?>
                    <div class="row">
                        <div class="col-25">
                            <label for="newsletter"><?= __('Receive newsletter'); ?></label>
                        </div>
                        <div class="col-75">
                            <input type="radio" name="newsletter" value="Yes"><?= __('Yes'); ?><br>
                            <input type="radio" name="newsletter" value="No" checked><?= __('No'); ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($humo_option["use_spam_question"] == 'y') { ?>
                    <div class="row">
                        <div class="col-25">
                            <label for="mail_block_spam"><?= __('Please answer the block-spam-question:'); ?></label>
                        </div>
                        <div class="col-75">
                            <?= $humo_option["block_spam_question"]; ?>
                            <input type="text" id="lname" class="input" name="mail_block_spam">
                        </div>
                    </div>
                <?php } ?>

                <br>
                <div class="row">
                    <input type="submit" class="input_submit" name="send_mail" value="<?= __('Send'); ?>">
                </div>
            </form>
        </div>

<?php
        if (isset($_POST['send_mail'])) {
            echo '<h3 style="text-align:center;">' . __('Wrong answer to the block-spam question! Try again...') . '</h3>';
        }
    } else {
        echo '<h2>' . __('The e-mail function has been switched off!') . '</h2>';
    }
}
