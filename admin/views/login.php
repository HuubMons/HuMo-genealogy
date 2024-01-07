<h2 align="center" class="my-4"><?= __('Administration menu login'); ?></h2>

<?php if ($fault) echo '<p align="center"><font color="red">' . __('Please enter a valid username or password. ') . '</font>'; ?>

<br>

<form name="form1" method="post" action="index.php">
    <div class="container">
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
                <input type="submit" class="btn btn-success" name="Submit" value="<?= __('Login'); ?>">
            </div>
        </div>
    </div>
</form>