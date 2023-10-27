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

<div id="mainmenu_centerbox">
    <div id="mainmenu_left">
        <!-- Show pages without menu -->
        <?php foreach ($data["pages"] as $pageDb) { ?>
            <a href="<?= $path . $pageDb->page_id; ?>"><?= $pageDb->page_title; ?></a><br>
        <?php } ?>

        <!-- Show pages with menu -->
        <?php foreach ($data["menu"] as $menuDb) { ?>
            <p><b><?= $menuDb->menu_name; ?></b><br>
                <?php foreach ($data["pages_menu"] as $pageDb) { ?>
                    <?php if ($pageDb->page_menu_id == $menuDb->menu_id) { ?>
                        <a href="<?= $path . $pageDb->page_id; ?>"><?= $pageDb->page_title; ?></a><br>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
    </div>

    <!-- Show page -->
    <div id="mainmenu_center_alt" style="text-align:left;">
        <?= $data["page"]; ?>
    </div>
</div>