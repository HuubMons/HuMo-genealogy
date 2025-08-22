<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<form method="post" action="index.php?page=tree&amp;menu_admin=tree_text" style="display : inline;">
    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
    <input type="hidden" name="language_tree" value="<?= $trees['language']; ?>">
    <?php if ($trees['treetext_id']) { ?>
        <input type="hidden" name="treetext_id" value="<?= $trees['treetext_id']; ?>">
    <?php } ?>

    <div class="p-2 me-sm-2 genealogy_search">
        <?= __('Here you can add some overall texts for EVERY family tree (and for  EVERY LANGUAGE!).<br>Select language, and change text'); ?><br>
        <?= __('Add "Default" (e.g. english) texts  for all languages, and/ or select a language to add texts for that specific language'); ?>:<br><br>

        <div class="row mb-2">
            <div class="col-md-3"><?= __('Language'); ?></div>
            <div class="col-md-auto">
                <a href="index.php?page=tree&amp;menu_admin=tree_text&amp;language_tree=default&amp;tree_id=<?= $trees['tree_id']; ?>"><?= __('Default'); ?></a>
            </div>

            <div class="col-md-auto">
                <?php include_once(__DIR__ . "/../../views/partial/select_language.php"); ?>
                <?php $language_path = 'index.php?page=tree&amp;menu_admin=tree_text&amp;'; ?>
                <?= show_country_flags($trees['language2'], '../', 'language_tree', $language_path); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><b><?= __('Name of family tree'); ?></b></div>
            <div class="col-md-7">
                <input type="text" name="treetext_name" value="<?= $trees['treetext_name']; ?>" size="60" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><?= __('Extra text in main menu'); ?></div>
            <div class="col-md-7">
                <textarea cols="60" rows="2" name="treetext_mainmenu_text" class="form-control form-control-sm"><?= $trees['treetext_mainmenu_text']; ?></textarea>
                <span style="font-size: 13px;"><?= __('I.e. a website'); ?>: &lt;a href="http://www.website.com"&gt;www.website.com&lt;/a&gt;</span>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><?= __('Extra source in main menu'); ?></div>
            <div class="col-md-7">
                <textarea cols="60" rows="2" name="treetext_mainmenu_source" class="form-control form-control-sm"><?= $trees['treetext_mainmenu_source']; ?></textarea>
                <span style="font-size: 13px;"><?= __(' I.e. a website'); ?>: &lt;a href="http://www.website.com"&gt;www.website.com&lt;/a&gt;</span>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><?= __('Upper text family page'); ?></div>
            <div class="col-md-7">
                <textarea cols="60" rows="1" name="treetext_family_top" class="form-control form-control-sm"><?= $trees['treetext_family_top']; ?></textarea>
                <span style="font-size: 13px;"><?= __('I.e. Familypage'); ?></span>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><?= __('Lower text family page'); ?></div>
            <div class="col-md-7">
                <textarea cols="60" rows="1" name="treetext_family_footer" class="form-control form-control-sm"><?= $trees['treetext_family_footer']; ?></textarea>
                <span style="font-size: 13px;"><?= __('I.e.: For more information: &lt;a href="mailform.php"&gt;contact&lt;/a&gt;'); ?></span>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <?php
                if ($trees['treetext_id']) {
                    echo '<input type="submit" name="change_tree_text" value="' . __('Change') . '" class="btn btn-sm btn-success">';
                } else {
                    echo '<input type="submit" name="add_tree_text" value="' . __('Change') . '" class="btn btn-sm btn-success">';
                }
                ?>
            </div>
        </div>

    </div>
</form>