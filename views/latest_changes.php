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
<div class="row">
    <div class="col-sm-4">
    </div>
    <div class="col-sm-4">
        <form action="<?= $path; ?>" method="post">
            <div class="input-group mb-3 text-center">
                <input type="text" class="form-control form-control-sm" name="search_name" id="part_of_name" value="<?= safe_text_show($search_name); ?>">
                <input type="submit" class="btn btn-sm btn-success" value="<?= __('Search'); ?>">
            </div>
        </form>
    </div>
    <div class="col-sm-4">
    </div>
</div>

<table class="humo" align="center">
    <tr class="table_headline">
        <th><?= __('Changed/ Added'); ?></th>
        <th><?= __('When changed'); ?></th>
        <th><?= __('When added'); ?></th>
    </tr>

    <?php foreach ($data["listchanges"] as $changeDb) { ?>
        <tr>
            <td><?= $changeDb->show_person ?></td>
            <td>
                <span style="white-space: nowrap"><?= $changeDb->changed_date; ?></span>
            </td>
            <td>
                <span style="white-space: nowrap"><?= $changeDb->new_date; ?></span>
            </td>
        </tr>
    <?php } ?>

</table>
<br>