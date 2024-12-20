<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$file = 'include/prefixes.php';
$message = '';
if (isset($_POST['save_language'])) {
    if (file_exists($file)) {
        $message = __('Saved');
        file_put_contents($file, $_POST['language_text']);
    } else {
        $message = 'ERROR: FAULT IN SAVE PROCESS';
    }
}
?>

<h1 align=center><?= __('Prefix editor'); ?></h1>

<?= __('These prefixes are used to process name-prefixes if a GEDCOM file is read.'); ?><br><br>

<form method="POST" action="index.php?page=prefix_editor">
    <?php if (is_writable($file)) { ?>
        <input type="submit" class="btn btn-success btn-sm" name="save_language" value="<?= __('Save'); ?>"><br>
    <?php } else { ?>
        <div class="alert alert-danger" role="alert">
            <?= __('FILE IS NOT WRITABLE!'); ?>
        </div>
    <?php } ?>

    <?php if ($message) { ?>
        <div class="alert alert-secondary mt-2" role="alert">
            <?= $message; ?>
        </div>
    <?php } ?>

    <textarea rows="35" cols="120" name="language_text" class="mt-2"><?= file_get_contents($file); ?></textarea>
</form>
<br>