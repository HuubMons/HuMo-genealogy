<?php
// *** Check block_spam_answer ***
$register_allowed = false;
if (isset($_POST['send_mail']) && (isset($_POST['register_block_spam']) && strtolower($_POST['register_block_spam']) === strtolower($humo_option["block_spam_answer"]))) {
    $register_allowed = true;
}
if ($humo_option["registration_use_spam_question"] != 'y') {
    $register_allowed = true;
}

$show_form = true;
$error = false;

if (isset($_POST['send_mail']) && $register_allowed == true) {
    $show_form = false;

    $usersql = 'SELECT * FROM humo_users WHERE user_name="' . safe_text_db($_POST["register_name"]) . '"';
    $user = $dbh->query($usersql);
    $userDb = $user->fetch(PDO::FETCH_OBJ);
    if (isset($userDb->user_id) || strtolower(safe_text_db($_POST["register_name"])) === "admin") {
        $error = __('ERROR: username already exists');
    }

    if ($_POST["register_password"] != $_POST["register_repeat_password"]) {
        $error = __('ERROR: No identical passwords');
    }

    if (strlen($_POST["register_password"]) < 6) {
        $error = __('ERROR: Password has to contain at least 6 characters');
    }

    if ($error == false) {
        $user_register_date = date("Y-m-d H:i");
        $hashToStoreInDb = password_hash($_POST["register_password"], PASSWORD_DEFAULT);
        $sql = "INSERT INTO humo_users SET
            user_name='" . safe_text_db($_POST["register_name"]) . "',
            user_remark='" . safe_text_db($_POST["register_text"]) . "',
            user_register_date='" . safe_text_db($user_register_date) . "',
            user_mail='" . safe_text_db($_POST["register_mail"]) . "',
            user_password_salted='" . $hashToStoreInDb . "',
            user_group_id='" . $humo_option["visitor_registration_group"] . "';";
        $result = $dbh->query($sql);
        echo '<h2>' . __('Registration completed') . '</h2>';
        echo __('At this moment you are registered in the user-group "guest". The administrator will check your registration, and select a user-group for you.') . '<br>';

        // *** Mail new registered user to the administrator ***
        $register_address = '';
        if (isset($dataDb->tree_email)) {
            $register_address = $dataDb->tree_email;
        } // Used in older HuMo-genealogy versions. Backwards compatible...
        if ($humo_option["general_email"]) {
            $register_address = $humo_option["general_email"];
        }

        $register_subject = "HuMo-genealogy. " . __('New registered user') . ": " . $_POST['register_name'] . "\n";

        // *** It's better to use plain text in the subject ***
        $register_subject = strip_tags($register_subject, ENT_QUOTES);

        $register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
        $register_message .= "<br><br>\n";
        $register_message .= __('New registered user') . "<br>\n";
        $register_message .= __('Name') . ':' . $_POST['register_name'] . "<br>\n";
        $register_message .= __('E-mail') . ": <a href='mailto:" . $_POST['register_mail'] . "'>" . $_POST['register_mail'] . "</a><br>\n";
        $register_message .= $_POST['register_text'] . "<br>\n";

        include_once(__DIR__ . '/../include/mail.php');

        // *** Set who the message is to be sent from ***
        $mail->setFrom($_POST['register_mail'], $_POST['register_name']);
        // *** Set who the message is to be sent to ***
        $mail->addAddress($register_address, $register_address);
        // *** Set the subject line ***
        $mail->Subject = $register_subject;
        $mail->msgHTML($register_message);
        // *** Replace the plain text body with one created manually ***
        //$mail->AltBody = 'This is a plain-text message body';
        if (!$mail->send()) {
            //	echo '<br><b>'.__('Sending e-mail failed!').' '. $mail->ErrorInfo.'</b>';
            //} else {
            //	echo '<br><b>'.__('E-mail sent!').'</b><br>';
        }
    } else {
        $show_form = true;
        echo '<h2>' . $error . '</h2>';
    }
}

if ($show_form) {
    $email = '';
    if (isset($dataDb->tree_email)) {
        $email = $dataDb->tree_email;
    } // Used in older HuMo-genealogy versions. Backwards compatible...
    if ($humo_option["general_email"]) {
        $email = $humo_option["general_email"];
    }
    if ($email != '') {
        $register_name = '';
        if (isset($_POST['register_name'])) {
            $register_name = $_POST['register_name'];
        }

        $register_mail = '';
        if (isset($_POST['register_mail'])) {
            $register_mail = $_POST['register_mail'];
        }

        $register_password = '';
        if (isset($_POST['register_password'])) {
            $register_password = $_POST['register_password'];
        }

        $register_repeat_password = '';
        if (isset($_POST['register_repeat_password'])) {
            $register_repeat_password = $_POST['register_repeat_password'];
        }

        $register_text = '';
        if (isset($_POST['register_text'])) {
            $register_text = $_POST['register_text'];
        }

        $path = 'index.php?page=register';
        if ($humo_option["url_rewrite"] == "j") {
            $path = 'register';
        }
        //$menu_path_register = $link_cls->get_link($uri_path, 'register');
?>

        <h1 class="my-4"><?= __('User registration form'); ?></h1>

        <div class="container">
            <form action="<?= $path; ?>" method="post">
                <div class="mb-2 row">
                    <label for="name" class="col-sm-3 col-form-label"><?= __('Name'); ?></label>
                    <div class="col-sm-5">
                        <input type="text" id="name" class="form-control" name="register_name" value="<?= $register_name; ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="mail_sender" class="col-sm-3 col-form-label"><?= __('E-mail address'); ?></label>
                    <div class="col-sm-5">
                        <input type="email" id="register_mail" class="form-control" name="register_mail" value="<?= $register_mail; ?>">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="register_password" class="col-sm-3 col-form-label"><?= __('Password'); ?></label>
                    <div class="col-sm-5">
                        <input type="password" id="register_password" class="form-control" name="register_password">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="register_repeat_password" class="col-sm-3 col-form-label"><?= __('Repeat password'); ?></label>
                    <div class="col-sm-5">
                        <input type="password" id="register_repeat_password" class="form-control" name="register_repeat_password">
                    </div>
                </div>

                <div class="mb-2 row">
                    <label for="register_text" class="col-sm-3 col-form-label"><?= __('Message'); ?></label>
                    <div class="col-sm-5">
                        <textarea id="register_text" class="form-control" name="register_text" style="height:200px"><?= $register_text; ?></textarea>
                    </div>
                </div>

                <?php if ($humo_option["use_spam_question"] == 'y') { ?>
                    <div class="mb-2 row">
                        <label for="register_block_spam" class="col-sm-3 col-form-label"><?= __('Please answer the block-spam-question:'); ?></label>
                        <div class="col-sm-5">
                            <?= $humo_option["block_spam_question"]; ?>
                            <input type="text" id="register_block_spam" class="form-control" name="register_block_spam">
                        </div>
                    </div>
                <?php } ?>

                <div class="mb-2 row">
                    <label for="2fa_code" class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-5">
                        <input type="submit" class="btn btn-success" name="send_mail" value="<?= __('Send'); ?>">
                    </div>
                </div>
            </form>
        </div>

<?php
        if (isset($_POST['send_mail']) && $error == false) {
            echo '<h3 style="text-align:center;">' . __('Wrong answer to the block-spam question! Try again...') . '</h3>';
        }
    } else {
        echo '<h2>' . __('The register function has been switched off!') . '</h2>';
    }
}
