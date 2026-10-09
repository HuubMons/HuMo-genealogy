<?php
$search_name = '';
if (isset($_POST["search_name"])) {
    $search_name = $_POST["search_name"];
}

$processLinks = new \Genealogy\Include\ProcessLinks();
$path = $processLinks->get_link($uri_path, 'latest_changes', $tree_id);
?>

<h1><?= $HtmlEscaper->escHtml(__('Recently changed persons and new persons')); ?></h1>

<!-- *** Search box *** -->
<div class="row me-1">
    <div class="col-sm-4"></div>
    <div class="col-sm-4">
        <form action="<?= $HtmlEscaper->escHtml($path); ?>" method="post">
            <div class="input-group mb-3 text-center">
                <input type="text" class="form-control form-control-sm" name="search_name" id="part_of_name" value="<?= $HtmlEscaper->escHtml($search_name); ?>">
                <input type="submit" class="btn btn-sm btn-success" value="<?= $HtmlEscaper->escHtml(__('Search')); ?>">
            </div>
        </form>
    </div>
    <div class="col-sm-4"></div>
</div>

<div class="table-responsive">
    <table class="table w-75" align="center">
        <thead class="table-primary">
            <tr>
                <th><?= $HtmlEscaper->escHtml(__('Changed/ Added')); ?></th>
                <th><?= $HtmlEscaper->escHtml(__('When changed')); ?></th>
                <th><?= $HtmlEscaper->escHtml(__('When added')); ?></th>
            </tr>
        </thead>

        <?php $i = 0; ?>
        <?php while ($i < count($data['show_person'])) { ?>
            <?php $person = $data['show_person'][$i]; ?>
            <tr>
                <td class="text-nowrap">
                    <?php if (!empty($person['name'])) { ?>
                        <img src="images/<?= $HtmlEscaper->escHtml($person['sex_icon']); ?>.gif" alt="<?= $HtmlEscaper->escHtml($person['sex_icon']); ?>">
                        <a href="<?= $HtmlEscaper->escHtml($person['url']); ?>"><?= $HtmlEscaper->escHtmlDecoded(strip_tags((string) $person['name'])); ?></a>
                    <?php } ?>
                </td>
                <td class="text-nowrap"><?= $HtmlEscaper->escHtml($data["changed_date"][$i]); ?></td>
                <td class="text-nowrap"><?= $HtmlEscaper->escHtml($data["new_date"][$i]); ?></td>
            </tr>
            <?php $i++; ?>
        <?php } ?>

    </table>
</div>
<br>