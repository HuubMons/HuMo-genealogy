<?php
$search_name = '';
if (isset($_POST["search_name"])) {
    $search_name = $_POST["search_name"];
}

// TODO use function
$path = 'index.php?page=latest_changes.php&amp;tree_id=' . $tree_id;
if ($humo_option["url_rewrite"] == "j") {
    $path = 'latest_changes/' . $tree_id;
}

$safeTextShow = new \Genealogy\Include\SafeTextShow();
?>

<h1><?= __('Recently changed persons and new persons'); ?></h1>

<!-- *** Search box *** -->
<div class="row me-1">
    <div class="col-sm-4"></div>
    <div class="col-sm-4">
        <form action="<?= $path; ?>" method="post">
            <div class="input-group mb-3 text-center">
                <input type="text" class="form-control form-control-sm" name="search_name" id="part_of_name" value="<?= $safeTextShow->safe_text_show($search_name); ?>">
                <input type="submit" class="btn btn-sm btn-success" value="<?= __('Search'); ?>">
            </div>
        </form>
    </div>
    <div class="col-sm-4"></div>
</div>

<div class="table-responsive">
    <table class="table w-75" align="center">
        <thead class="table-primary">
            <tr>
                <th><?= __('Changed/ Added'); ?></th>
                <th><?= __('When changed'); ?></th>
                <th><?= __('When added'); ?></th>
            </tr>
        </thead>

        <?php $i = 0; ?>
        <?php while ($i < count($data['show_person'])) { ?>
            <tr>
                <td class="text-nowrap"><?= $data["show_person"][$i] ?></td>
                <td class="text-nowrap"><?= $data["changed_date"][$i]; ?></td>
                <td class="text-nowrap"><?= $data["new_date"][$i]; ?></td>
            </tr>
            <?php $i++; ?>
        <?php } ?>

    </table>
</div>
<br>