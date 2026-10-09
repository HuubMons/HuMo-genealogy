<?php
// *** Check user authority ***
if ($data["authorised"] != '') {
    echo $data["authorised"];
    exit();
}

$processLinks = new \Genealogy\Include\ProcessLinks();
?>

<div class="row m-lg-1 py-3 genealogy_row">
    <div class="col-sm-2">
        <!-- Show pages without menu -->
        <?php foreach ($data["pages"] as $pageDb) { ?>
            <?php $link = $processLinks->get_link($uri_path, 'cms_pages', null, false, ['select_page' => $pageDb->page_id]); ?>
            <a href="<?= $HtmlEscaper->escHtml($link); ?>"><?= $HtmlEscaper->escHtml($pageDb->page_title); ?></a><br>
        <?php } ?>

        <!-- Show pages with menu -->
        <?php foreach ($data["menu"] as $menuDb) { ?>
            <br><b><?= $HtmlEscaper->escHtml($menuDb->menu_name); ?></b><br>
            <?php foreach ($data["pages_menu"] as $pageDb) { ?>
                <?php if ($pageDb->page_menu_id == $menuDb->menu_id) { ?>
                    <?php $link = $processLinks->get_link($uri_path, 'cms_pages', null, false, ['select_page' => $pageDb->page_id]); ?>
                    <a href="<?= $HtmlEscaper->escHtml($link); ?>"><?= $HtmlEscaper->escHtml($pageDb->page_title); ?></a><br>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>
    <div class="col-sm-10">
        <?= $data["page"]; ?>
    </div>
</div>