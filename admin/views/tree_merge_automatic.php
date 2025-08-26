<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

/**
 * This is the screen that will show when you choose "automatic merge" from the main merge page
 */
?>

<br>
<?= __('Automatic merge will go through the entire database and merge all persons who comply with ALL the following conditions:<br>
<ul><li>Both persons have a first name and a last name and they are identical</li>
<li>Both persons have parents with first and last names and those names are identical</li>
<li>Both persons\' parents have a marriage date and it is identical (This can be disabled under "Settings")</li>
<li>Both persons have a birth date and it is identical OR both have a death date and it is identical</li></ul>
<b>Please note that the automatic merge may take quite some time, depending on the size of the database and the number of merges.</b><br>
You will be notified of results as the action is completed'); ?>

<br><br>

<form method="post" action="index.php?page=tree&amp;menu_admin=<?= $trees['menu_tab']; ?>" style="display : inline;">
    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
    <input type="submit" name="auto_merge" value="<?= __('Start automatic merge'); ?>" class="btn btn-sm btn-secondary">
</form>

<form method="post" action="index.php?page=tree&amp;menu_admin=<?= $trees['menu_tab']; ?>" style="display : inline;">
    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
    <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success ms-5   ">
</form>