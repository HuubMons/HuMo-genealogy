<?php
$search_name = '';
if (isset($_POST["search_name"])) {
    $search_name = $_POST["search_name"];
}

// TODO use function
$path = 'index.php?page=latest_changes.php&amp;tree_id=' . $tree_id;
if ($humo_option["url_rewrite"] == "j") $path = 'latest_changes/' . $tree_id;
?>

<h1><?= __('Recently changed persons and new persons'); ?></h1>

<!-- *** Search box *** -->
<div style="text-align: center; margin-bottom: 16px">
    <form action="<?= $path; ?>" method="post">
        <input type="text" name="search_name" id="part_of_name" value="<?= safe_text_show($search_name); ?>">
        <input type="submit" value="<?= __('Search'); ?>">
    </form>
</div>

<table class="humo small">
    <tr class="table_headline">
        <th style="font-size: 90%; text-align: left"><?= __('Changed/ Added'); ?></th>
        <th style="font-size: 90%; text-align: left"><?= __('When changed'); ?></th>
        <th style="font-size: 90%; text-align: left"><?= __('When added'); ?></th>
    </tr>

    <?php foreach ($data["listchanges"] as $changeDb) { ?>
        <tr>
            <td style="font-size: 90%"><?= $changeDb->show_person ?></td>
            <td style="font-size: 90%">
                <span style="white-space: nowrap"><?= $changeDb->changed_date; ?></span>
            </td>
            <td style="font-size: 90%">
                <span style="white-space: nowrap"><?= $changeDb->new_date; ?></span>
            </td>
        </tr>
    <?php } ?>

</table>
<br>