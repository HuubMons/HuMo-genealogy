<?php

/**
 * Nov. 2022 Huub: Added 2FA.
 */

use Genealogy\Include\BotDetector;

// TODO use function to get link
if ($humo_option["url_rewrite"] == "j") {
    $action = 'user_settings';
    $action2 = 'user_settings?';
} else {
    $action = 'index.php?page=user_settings';
    $action2 = 'index.php?page=user_settings&amp;';
}
//$action = $processLinks->get_link($uri_path, 'user_settings');
//$action2 = $processLinks->get_link($uri_path, 'user_settings',true);


if (isset($data["user"]->user_name) && $user['group_menu_change_password'] == 'y') {
?>

    <h1 class="my-4"><?= __('User settings'); ?></h1>

    <!-- TODO use bootstrap message box. -->
    <?php if ($data["result_message"]) echo '<h3 class="center">' . $data["result_message"] . '</h3>'; ?>

    <form action="<?= $action; ?>" method="post">
        <div class="container me-1">
            <div class="mb-2 row">
                <label for="mail_sender" class="col-sm-3 col-form-label"><?= __('E-mail address'); ?></label>
                <div class="col-sm-5">
                    <input type="email" id="register_mail" class="form-control" name="register_mail" placeholder="<?= __('E-mail address'); ?>" value="<?= $data["user"]->user_mail; ?>">
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

            <?php if (isset($data["user"]->user_2fa_auth_secret)) { ?>
                <div class="row">
                    <label for="2fa" class="col-sm-3 col-form-label"><?= __('Two factor authentication (2FA)'); ?></label>
                    <div class="col-sm-5">
                        <a href="<?= $action2; ?>2fa=1"><?= __('Two factor authentication (2FA)'); ?></a>
                    </div>
                </div>

                <?php if (isset($_GET['2fa']) && $_GET['2fa'] == '1') { ?>
                    <div class="row">
                        <label for="2fa_code" class="col-sm-3 col-form-label"></label>

                        <div class="col-sm-5">
                            <?= __('Highly recommended:<br>Enable "Two Factor Authentication" (2FA).'); ?><br>
                            <?= __('Use a 2FA app (like Microsoft or Google authenticator) to generate a secure code to login.'); ?><br>
                            <?= __('More information about 2FA can be found at internet.'); ?><br><br>

                            <?php printf(__('1) Install a 2FA app, and add %s in the app using this QR code:'), 'HuMo-genealogy'); ?>
                            <br>
                            <img style="text-align: center;" class="img-fluid" src="<?= $data["qrCodeUrl"]; ?>" alt="Verify this Google Authenticator"><br><br>

                            <?= __('2) Use 2FA code from app and enable 2FA login:'); ?><br>
                            <input type="text" class="form-control my-2" id="2fa_code" name="2fa_code" placeholder="<?= __('2FA code from app'); ?>" size="30" style="background-color:#FFFFFF">

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="check1" name="user_2fa_enabled" <?= $data["checked"]; ?>>
                                <label class="form-check-label"><?= __('Enable 2FA login'); ?></label>
                            </div>

                            <input type="hidden" name="user_2fa_check">
                        </div>
                    </div>
                <?php } ?>

            <?php } ?>

            <br>
            <div class="row">
                <label for="2fa_code" class="col-sm-3 col-form-label"></label>
                <div class="col-sm-5">
                    <input type="submit" class="col-sm-4 btn btn-success" name="update_settings" value="<?= __('Change'); ?>">
                </div>
            </div>
        </div>
    </form>
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

$botDetector = new BotDetector();
if ($botDetector->isBot()) {
    $show_theme_select = false;
}

if ($show_theme_select == true) {
    $hide_themes_array = explode(";", $humo_option["hide_themes"]);

?>
    <h1 class="my-4"><?= __('Select a theme'); ?></h1>

    <form action="<?= $action; ?>" class="center mb-3">
        <div class="row me-1">
            <div class="col-sm-4"></div>
            <div class="col-sm-4">
                <select id="theme_select" name="switchcontrol" class="form-select form-select-sm" aria-label="<?= __('Select a theme'); ?>" onchange="chooseStyle(this.options[this.selectedIndex].value, 365)">
                    <?php
                    if (isset($humo_option['default_skin'])) {
                        echo '<option value="' . $humo_option['default_skin'] . '" selected="selected">' . __('Select a theme') . ':</option>';
                        echo '<option value="' . $humo_option['default_skin'] . '">' . __('Standard-colours') . '</option>';
                    } else {
                        echo '<option value="none" selected="selected">' . __('Select a theme') . ':</option>';
                        echo '<option value="none">' . __('Standard-colours') . '</option>';
                    }

                    sort($theme_folder);
                    $counter = count($theme_folder);
                    for ($i = 0; $i < $counter; $i++) {
                        $theme = $theme_folder[$i];
                        $theme = str_replace(".css", "", $theme);
                        if (!in_array($theme, $hide_themes_array)) {
                            echo '<option value="' . $theme . '">' . $theme . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </form>

    <!--  Theme select using screen shots -->
    <!--  Screen shots about 725x500 (but resized to smaller pictures) -->
    <div class="container">
        <!-- <h1 class="fw-light text-center text-lg-start mt-4 mb-0">Thumbnail Gallery</h1> -->
        <!-- <hr class="mt-2 mb-5"> -->
        <div class="row text-center text-lg-start">
            <?php
            for ($i = 0; $i < count($theme_folder); $i++) {
                $theme = $theme_folder[$i];
                $theme = str_replace(".css", "", $theme);
                if (!in_array($theme, $hide_themes_array)) {
            ?>
                    <div class="col-lg-4 col-md-6">
                        <?php /* <input type="image" name="submit" value="submit" class="w-100 shadow-1-strong rounded mb-4 border border-dark" alt="theme" src="styles/<?= $theme; ?>.png" onclick="chooseStyle('<?= $theme; ?>', 365)"> */ ?>
                        <input type="image" name="submit" value="submit" class="img-thumbnail" alt="theme" src="styles/<?= $theme; ?>.png" onclick="chooseStyle('<?= $theme; ?>', 365)">
                    </div>
            <?php
                }
            }
            ?>
        </div>
    </div>

<?php
}
