<h2 align=center><?= __('Administration menu login'); ?></h2>

<?php
// *** Show login fault message ***
if ($fault) echo '<p align="center"><font color="red">' . __('Please enter a valid username or password. ') . '</font>';
?>

<form name="form1" method="post" action="index.php">
    <table class="humo" border="1" cellspacing="0" align="center">
        <tr>
            <td><?= __('Username or e-mail address'); ?>:</td>
            <td><input name="username" type="text" size="20" maxlength="25"></td>
        </tr>
        <tr>
            <td><?= __('Password'); ?>:</td>
            <td><input name="password" type="password" size="20" maxlength="50"></td>
        </tr>
        <tr>
            <td><?= __('Two factor authentication (2FA) code if needed'); ?>:</td>
            <td><input name="2fa_code" type="text" size="20" maxlength="25"></td>
        </tr>
        <tr>
            <td><br></td>
            <td><input type="submit" name="Submit" value="<?= __('Login'); ?>"></td>
        </tr>
    </table>
</form>