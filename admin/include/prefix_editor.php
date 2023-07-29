<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

//if (CMS_SPECIFIC == "Joomla") {
//    $phpself2 = 'index.php?option=com_humo-gen&amp;task=admin&amp;page=prefix_editor';
//} else {
$phpself2 = CMS_ROOTPATH . 'admin/index.php?page=prefix_editor';
//}

$file = CMS_ROOTPATH . 'admin/prefixes.php';
$message = '';
if (isset($_POST['save_language'])) {
    $message = '<b>' . __('Saved') . ' ';
    if (file_exists($file)) {
        $language_text = $_POST['language_text'];
        file_put_contents($file, $language_text);
    } else {
        $message = 'ERROR: FAULT IN SAVE PROCESS';
    }
}

?>
<h1 align=center><?= __('Prefix editor'); ?></h1>

<p>
    <?= __('These prefixes are used to process name-prefixes if a GEDCOM file is read.'); ?>

<form method="POST" action="<?= $phpself2; ?>" style="display : inline;">
    <p>
    <table class="humo" border="1" cellspacing="0">
        <tr class="table_header_large">
            <th>
                <?php
                if (is_writable($file)) {
                    echo ' <input type="Submit" name="save_language" value="' . __('Save') . '"> ';
                } else {
                    echo '<b>' . __('FILE IS NOT WRITABLE!') . '</b>';
                }
                // *** Show "Save" message ***
                echo $message . '<br>';
                ?>
            </th>
        </tr>

        <tr>
            <td valign="top" width="100%">
                <textarea rows="35" cols="120" name="language_text" style="direction:ltr"><?= file_get_contents($file); ?></textarea>
            </td>
        </tr>
    </table>
</form>