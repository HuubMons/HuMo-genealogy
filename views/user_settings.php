<?php

/**
 * Nov. 2022 Huub: Added 2FA.
 */

// TODO use function to get link
if ($humo_option["url_rewrite"] == "j") {
    $action = 'user_settings';
    $action2 = 'user_settings?';
} else {
    $action = 'index.php?page=user_settings';
    $action2 = 'index.php?page=user_settings&amp;';
}
//$action = $link_cls->get_link($uri_path, 'user_settings');
//$action2 = $link_cls->get_link($uri_path, 'user_settings',true);


if (isset($data["user"]->user_name) and $user['group_menu_change_password'] == 'y') {
?>

    <h1 class="my-4"><?= __('User settings'); ?></h1>

    <!-- TODO use bootstrap message box. -->
    <?php if ($data["result_message"]) echo '<h3 class="center">' . $data["result_message"] . '</h3>'; ?>

    <div class="container">
        <form action="<?= $action; ?>" method="post">
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

                <?php if (isset($_GET['2fa']) and $_GET['2fa'] == '1') { ?>
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
                                <input class="form-check-input" type="checkbox" id="check1" name="user_2fa_enabled" <?= $data["checked"]; ?>>
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
    <h1 class="my-4"><?= __('Select a theme'); ?></h1>

    <form action="<?= $action; ?>" class="center">
        <div class="row">
            <div class="col-sm-4"></div>
            <div class="col-sm-4">
                <select name="switchcontrol" class="form-select form-select-sm" onchange="chooseStyle(this.options[this.selectedIndex].value, 365)">
                    <?php
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
            </div>
        </div>
    </form>

    <!--  Theme select using screen shots -->
    <!--  Screen shots about 725x500 (but resized to smaller pictures) -->

    <br>

    <div style="width:100%; clear:both;"></div>
    <?php
    $row_nr = 1;
    $selected_column = 'left';
    for ($i = 0; $i < count($theme_folder); $i++) {
        $theme = $theme_folder[$i];
        $theme = str_replace(".css", "", $theme);
        if (!in_array($theme, $hide_themes_array)) {
            if ($selected_column == 'left') {
                $row_left[] = $theme;
                $selected_column = 'center';
            } elseif ($selected_column == 'center') {
                $row_center[] = $theme;
                $selected_column = 'right';
            } elseif ($selected_column == 'right') {
                $row_right[] = $theme;
                $selected_column = 'left';
                $row_nr++;
            }
        }
    }
    ?>

    <!-- Gallery -->
    <form action="<?= $action; ?>">
        <div class="row">
            <?php for ($i = 0; $i < $row_nr; $i++) { ?>
                <?php if (isset($row_left[$i])) {; ?>
                    <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
                        <b><?= $row_left[$i]; ?></b><br>
                        <input type="image" name="submit" value="submit" class="w-100 shadow-1-strong rounded mb-4 border border-dark" alt="theme" src="styles/<?= $row_left[$i]; ?>.png" onclick="chooseStyle('<?= $row_left[$i]; ?>', 365)">
                    </div>
                <?php }; ?>

                <?php if (isset($row_center[$i])) {; ?>
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <b><?= $row_center[$i]; ?></b><br>
                        <input type="image" name="submit" value="submit" class="w-100 shadow-1-strong rounded mb-4 border border-dark" alt="theme" src="styles/<?= $row_center[$i]; ?>.png" onclick="chooseStyle('<?= $row_center[$i]; ?>', 365)">
                    </div>
                <?php }; ?>

                <?php if (isset($row_right[$i])) {; ?>
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <b><?= $row_right[$i]; ?></b><br>
                        <input type="image" name="submit" value="submit" class="w-100 shadow-1-strong rounded mb-4 border border-dark" alt="theme" src="styles/<?= $row_right[$i]; ?>.png" onclick="chooseStyle('<?= $row_right[$i]; ?>', 365)">
                    </div>
                <?php }; ?>
            <?php }; ?>
        </div>
        <!-- Gallery -->
    </form>

    <!-- Otherwise footer is at wrong place -->
    <div style="width:100%; clear:both;"></div>

    <br>
    <br>
<?php
}
