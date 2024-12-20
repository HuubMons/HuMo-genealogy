<?php

/**
 * Front page login form.
 */

if ($user['group_menu_login'] != 'j') {
    echo 'Access to this page is blocked.';
    exit;
}

$path_login = $link_cls->get_link($uri_path, 'login');
$path_reset_password = $link_cls->get_link($uri_path, 'reset_password');
?>

<h1 class="my-4"><?= __('Login'); ?></h1>

<div class="container">
    <?php if ($index['fault'] == true) { ?>
        <div class="alert alert-warning">
            <strong><?= __('No valid username or password.'); ?></strong>
        </div>
    <?php } ?>

    <form action="<?= $path_login; ?>" method="post">
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

    <!-- Only use password retrieval option if sender mail is set in admin settings and is a valid mail address -->
    <?php if ($humo_option["password_retrieval"] && filter_var($humo_option["password_retrieval"], FILTER_VALIDATE_EMAIL)) { ?>
        <div class="center">
            <form name="forget_form" method="post" action="<?= $path_reset_password; ?>">
                <input type="hidden" name="forgotpw" value="1">
                <input type="submit" name="Submit" value="<?= __('Forgot password'); ?>" class="btn btn-secondary">
            </form>
        </div>
    <?php } ?>
</div>