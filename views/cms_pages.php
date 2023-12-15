<?php
// *** Check user authority ***
if ($data["authorised"] != '') {
    echo $data["authorised"];
    exit();
}

//TODO use link function (if possible)
if ($humo_option["url_rewrite"] == "j") {
    $path = 'cms_pages/';
} else {
    $path = 'index.php?page=cms_pages&amp;select_page=';
}
?>

<div class="row m-lg-1 py-3 genealogy_row">
    <div class="col-sm-2">
        <!-- Show pages without menu -->
        <?php foreach ($data["pages"] as $pageDb) { ?>
            <a href="<?= $path . $pageDb->page_id; ?>"><?= $pageDb->page_title; ?></a><br>
        <?php } ?>

        <!-- Show pages with menu -->
        <?php foreach ($data["menu"] as $menuDb) { ?>
            <br><b><?= $menuDb->menu_name; ?></b><br>
            <?php foreach ($data["pages_menu"] as $pageDb) { ?>
                <?php if ($pageDb->page_menu_id == $menuDb->menu_id) { ?>
                    <a href="<?= $path . $pageDb->page_id; ?>"><?= $pageDb->page_title; ?></a><br>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>
    <div class="col-sm-10">
        <?= $data["page"]; ?>
    </div>
</div>