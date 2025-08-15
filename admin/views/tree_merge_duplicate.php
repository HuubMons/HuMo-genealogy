<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

echo '<br>';
echo __('With "Duplicate merge" the program will look for all persons with a fixed set of criteria for identical data.
These are:
<ul><li>Same last name and same first name.<br>
By default, people with blank first or last names are included. You can disable that under "Settings" in the main menu.</li>
<li>Same birthdate or same deathdate.<br>
By default, when one or both persons have a missing birth/death date they will still be included when the name matches.
You can change that under "Settings" in the main menu.</li></ul>
The found duplicates will be presented to you, one pair after the other, with their details.<br>
You can then decide whether to accept the default merge, or change which details of the right person will be merged into the left.<br>
If you decide not to merge this pair, you can "skip" to the next pair.<br>
If after the merge there are surrounding relatives that might need merging too, you will be urged to move to "Relatives merge"<br>
If you have interrupted a duplicate merge in this session (for example to move to "relatives merge"),
this page will also show a "Continue duplicate merge" button so you can continue where you left off.<br>
<b>Please note that generating the duplicates may take some time, depending on the size of the tree.</b>');

echo '<br><br>';

if (isset($_SESSION['dupl_arr_' . $trees['tree_id']])) {
?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <form method="post" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="tree">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
        <input type="submit" style="min-width:150px" name="duplicate_compare" value="<?= __('Continue duplicate merge'); ?>" class="btn btn-sm btn-success">
    </form>
<?php } ?>

<form method="post" action="index.php" style="display : inline;">
    <input type="hidden" name="page" value="tree">
    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
    <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
    <?= __('Find doubles only within this family name (optional)'); ?>:
    <input type="text" name="famname_search" class="form-control form-control-sm my-3 w-25">
    <input type="submit" style="min-width:150px" name="duplicate" value="<?= __('Generate new duplicate merge'); ?>" class="btn btn-sm btn-success ms-2">
</form>

<form method="post" action="index.php" style="display : inline;">
    <input type="hidden" name="page" value="tree">
    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
    <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
    <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success ms-5">
</form>