<?php
include_once(__DIR__ . "/../include/2fa_authentication/authenticator.php");
$two_fa_change = false;

// TODO use function to get link
if ($humo_option["url_rewrite"] == "j") {
    $action = 'user_settings';
    $action2 = 'user_settings?';
} else {
    $action = 'index.php?page=user_settings';
    $action2 = 'index.php?page=user_settings&amp;';
}

if (isset($_SESSION['user_id']) and is_numeric($_SESSION['user_id'])) {
    @$qry = "SELECT * FROM humo_users LEFT JOIN humo_groups
        ON humo_users.user_group_id=humo_groups.group_id
        WHERE humo_users.user_id='" . $_SESSION['user_id'] . "'";
    @$result = $dbh->query($qry);
    if ($result->rowCount() > 0) {
        @$userDb = $result->fetch(PDO::FETCH_OBJ);
    }
}

if (isset($_POST['update_settings'])) {
    $result_message = '';
    if ($_POST["register_password"] != $_POST["register_repeat_password"]) {
        $result_message = __('ERROR: No identical passwords');
    }

    if ($result_message == '') {
        $user_register_date = date("Y-m-d H:i");
        $sql = "UPDATE humo_users SET user_mail='" . safe_text_db($_POST["register_mail"]) . "'";
        if ($_POST["register_password"] != '')
            $hashToStoreInDb = password_hash($_POST["register_password"], PASSWORD_DEFAULT);
        if (isset($hashToStoreInDb)) $sql .= ", user_password_salted='" . $hashToStoreInDb . "'";
        $sql .= " WHERE user_id=" . $userDb->user_id;
        $result = $dbh->query($sql);

        $result_message = __('Your settings are updated!');

        // *** Only update 2FA settings if database is updated and 2FA settings are changed ***
        if (isset($userDb->user_2fa_enabled) and isset($_POST['user_2fa_check'])) {
            // *** 2FA Authenticator (2fa_code = code from 2FA authenticator) ***
            if (!isset($_POST['user_2fa_enabled']) and $userDb->user_2fa_enabled) {
                // *** Disable 2FA ***
                $sql = "UPDATE humo_users SET user_2fa_enabled='' WHERE user_id=" . $userDb->user_id;
                $result = $dbh->query($sql);
                $two_fa_change = true;
            }
            if (isset($_POST['user_2fa_enabled']) and !$userDb->user_2fa_enabled) {
                $two_fa_change = true;
                if ($_POST['2fa_code'] and is_numeric($_POST['2fa_code'])) {
                    $Authenticator = new Authenticator();
                    $checkResult = $Authenticator->verifyCode($userDb->user_2fa_auth_secret, $_POST['2fa_code'], 2);        // 2 = 2*30sec clock tolerance
                    if (!$checkResult) {
                        $result_message = __('Wrong 2FA code. Please enter valid 2FA code to enable 2FA authentication.') . '<br>';
                    } else {
                        $sql = "UPDATE humo_users SET user_2fa_enabled='1' WHERE user_id=" . $userDb->user_id;
                        $result = $dbh->query($sql);
                        $result_message = __('Enabled 2FA authentication.') . '<br>';
                    }
                } else {
                    // *** No 2FA code entered ***
                    $result_message = __('Wrong 2FA code. Please enter valid 2FA code to enable 2FA authentication.') . '<br>';
                }
            }
        }

        // *** Reload user settings (especially needed for 2FA settings) ***
        @$qry = "SELECT * FROM humo_users LEFT JOIN humo_groups
            ON humo_users.user_group_id=humo_groups.group_id
            WHERE humo_users.user_id='" . $_SESSION['user_id'] . "'";
        @$result = $dbh->query($qry);
        if ($result->rowCount() > 0) {
            @$userDb = $result->fetch(PDO::FETCH_OBJ);
        }

        //echo '<h2>'.__('Your settings are updated!').'</h2>';
    }
    //else{
    //	echo '<h2>'.$error.'</h2>';
    //}
    echo '<h2>' . $result_message . '</h2>';

    if ($dataDb->tree_email) {
        $register_address = $dataDb->tree_email;
        $register_subject = "HuMo-genealogy. " . __('Updated profile') . ": " . $userDb->user_name . "\n";

        // *** It's better to use plain text in the subject ***
        $register_subject = strip_tags($register_subject, ENT_QUOTES);

        $register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
        $register_message .= "<br><br>\n";
        $register_message .= __('User updated his/ her profile') . "<br>\n";
        $register_message .= __('Name') . ':' . $userDb->user_name . "<br>\n";
        $register_message .= __('E-mail') . ": <a href='mailto:" . $_POST['register_mail'] . "'>" . $_POST['register_mail'] . "</a><br>\n";

        include_once(__DIR__ . '/../include/mail.php');

        // *** Set who the message is to be sent from ***
        $mail->setFrom($_POST['register_mail'], $userDb->user_name);
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
    }
}
//elseif (isset($userDb->user_name)){
if (isset($userDb->user_name) and $user['group_menu_change_password'] == 'y') {
    $register_name = $userDb->user_name;
    if (isset($_POST['register_name'])) {
        $register_name = $_POST['register_name'];
    }

    $register_password = '';
    if (isset($_POST['register_password'])) {
        $register_password = $_POST['register_password'];
    }

    if ($user['group_menu_change_password'] == 'y') {
        $register_repeat_password = '';
        if (isset($_POST['register_repeat_password'])) {
            $register_repeat_password = $_POST['register_repeat_password'];
        }

        $register_mail = $userDb->user_mail;
        if (isset($_POST['register_mail'])) {
            $register_mail = $_POST['register_mail'];
        }

        if (isset($userDb->user_2fa_auth_secret)) {
            // *** 2FA Two factor authentification ***
            $Authenticator = new Authenticator();
            if ($userDb->user_2fa_auth_secret) {
                $user_2fa_auth_secret = $userDb->user_2fa_auth_secret;
            } else {
                $user_2fa_auth_secret = $Authenticator->generateRandomSecret();

                // *** Save auth_secret, so it's not changed anymore ***
                $sql = "UPDATE humo_users SET user_2fa_auth_secret='" . safe_text_db($user_2fa_auth_secret) . "' WHERE user_id=" . $userDb->user_id;
                $result = $dbh->query($sql);
            }

            if (isset($_GET['2fa']) and $_GET['2fa'] == '1') {
                //$siteusernamestr= "Your Sites Unique String";
                $siteusernamestr = 'HuMo-genealogy ' . $_SERVER['SERVER_NAME'];
                $qrCodeUrl = $Authenticator->getQR($siteusernamestr, $user_2fa_auth_secret);
                $checked = '';
                if ($userDb->user_2fa_enabled == 1) $checked = ' checked="true"';
            }
        }
    }

?>
    <h1><?= __('User settings'); ?></h1>

    <!-- Layout: https://www.w3schools.com/csS/tryit.asp?filename=trycss_form_responsive -->
    <div class="container">
        <form action="<?= $action; ?>" method="post">
            <div class="row">
                <div class="col-25">
                    <label for="mail_sender"><?= __('E-mail address'); ?></label>
                </div>
                <div class="col-75">
                    <input type="email" id="register_mail" class="input" name="register_mail" placeholder="<?= __('E-mail address'); ?>" value="<?= $register_mail; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-25">
                    <label for="register_password"><?= __('Password'); ?></label>
                </div>
                <div class="col-75">
                    <input type="password" id="register_password" class="input" name="register_password">
                </div>
            </div>

            <div class="row">
                <div class="col-25">
                    <label for="register_repeat_password"><?= __('Repeat password'); ?></label>
                </div>
                <div class="col-75">
                    <input type="password" id="register_repeat_password" class="input" name="register_repeat_password">
                </div>
            </div>

            <?php if (isset($userDb->user_2fa_auth_secret)) { ?>
                <div class="row">
                    <div class="col-25">
                        <label for="2fa"><?= __('Two factor authentication (2FA)'); ?></label>
                    </div>
                    <div class="col-75">
                        <a href="<?= $action2; ?>2fa=1"><?= __('Two factor authentication (2FA)'); ?></a>
                    </div>
                </div>

                <?php if (isset($_GET['2fa']) and $_GET['2fa'] == '1') { ?>
                    <div class="row">
                        <div class="col-25">
                            <br>
                        </div>
                        <div class="col-75">
                            <?= __('Highly recommended:<br>Enable "Two Factor Authentication" (2FA).'); ?><br>
                            <?= __('Use a 2FA app (like Microsoft or Google authenticator) to generate a secure code to login.'); ?><br>
                            <?= __('More information about 2FA can be found at internet.'); ?><br><br>

                            <?php printf(__('1) Install a 2FA app, and add %s in the app using this QR code:'), 'HuMo-genealogy'); ?>
                            <br>
                            <img style="text-align: center;" class="img-fluid" src="<?= $qrCodeUrl; ?>" alt="Verify this Google Authenticator"><br><br>

                            <?= __('2) Use 2FA code from app and enable 2FA login:'); ?><br>
                            <input type="text" class="fonts" id="2fa_code" name="2fa_code" placeholder="<?= __('2FA code from app'); ?>" size="30" style="background-color:#FFFFFF">

                            <input type="checkbox" name="user_2fa_enabled" <?= $checked; ?>><?= __('Enable 2FA login'); ?>
                            <input type="hidden" name="user_2fa_check">
                        </div>
                    </div>
                <?php } ?>

            <?php } ?>

            <br>
            <div class="row">
                <input type="submit" class="input_submit" name="update_settings" value="<?= __('Change'); ?>">
            </div>
        </form>
    </div>
<?php
}

// *** Theme select ***
// *** Hide theme select if there is only one theme, AND it is the default theme ***
$show_theme_select = true;
if (count($theme_folder) == 1) {
    if (isset($humo_option['default_skin']) and $humo_option['default_skin'] . '.css' == $theme_folder[0]) {
        $show_theme_select = false;
    }
}

if ($bot_visit) {
    $show_theme_select = false;
}

if ($show_theme_select == true) {
    $hide_themes_array = explode(";", $humo_option["hide_themes"]);

?>
    <br>
    <h1><?= __('Select a theme'); ?></h1>
    <form title="<?= __('Select a colour theme (a cookie will be used to remember the theme)'); ?>" action="<?= $action; ?>" class="center">
        <select name="switchcontrol" size="1" onchange="chooseStyle(this.options[this.selectedIndex].value, 365)">
            <?php
            // NAZIEN DIT STUK CODE KAN ERUIT? En dan het geselecteerde thema als selected (uit cookie?)?
            if (isset($humo_option['default_skin'])) {
                echo '<option value="' . $humo_option['default_skin'] . '" selected="selected">' . __('Select a theme') . ':</option>';
                echo '<option value="' . $humo_option['default_skin'] . '">' . __('Standard-colours') . '</option>';
            } else {
                echo '<option value="none" selected="selected">' . __('Select a theme') . ':</option>';
                echo '<option value="none">' . __('Standard-colours') . '</option>';
            }

            sort($theme_folder);
            for ($i = 0; $i < count($theme_folder); $i++) {
                $theme = $theme_folder[$i];
                $theme = str_replace(".css", "", $theme);
                if (!in_array($theme, $hide_themes_array)) {
                    echo '<option value="' . $theme . '">' . $theme . '</option>';
                }
            }
            ?>
        </select>
    </form>

    <!--  Theme select using screen shots -->
    <!--  Screen shots about 725x500 (but resized to smaller pictures) -->
    <br>
    <form title="<?= __('Select a colour theme (a cookie will be used to remember the theme)'); ?>" action="<?= $action; ?>">
        <?php
        for ($i = 0; $i < count($theme_folder); $i++) {
            $theme = $theme_folder[$i];
            $theme = str_replace(".css", "", $theme);
            if (!in_array($theme, $hide_themes_array)) {
                echo '<span style="float: left; margin: 3px; border: solid 1px #999999;">';
                echo '<b>' . $theme . '</b><br>';
                echo '<input type="image" name="submit" value="submit" alt="theme" src="styles/' . $theme . '.png" width="360" height="250" onclick="chooseStyle(\'' . $theme . '\', 365)">';
                echo '</span>';
            }
        }
        ?>
    </form>

    <!-- Otherwise footer is at wrong place -->
    <div style="width:100%; clear:both;"></div>

    <br>
    <br>
<?php
}
