<?php

/**
 * Edit or add a source.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$path_prefix = '../';

// *** These items are needed for adding and changing picture ***
$phpself = 'index.php';
$editor_cls = $editSource['editor_cls'];

// *** Process queries (needed to order and delete pictures) ***
$editor_cls = new Editor_cls;
$editorModel = new EditorModel($dbh, $tree_id, $tree_prefix, $db_functions, $editor_cls, $humo_option);
$editor['confirm'] = $editorModel->update_editor2();

// TODO this picture remove confirm box is shown above the header.
echo $editor['confirm']; // Confirm message to remove picture from source.



$field_text_large = 'style="height: 100px; width:550px"';

// TODO check if code could be improved. Also in editorModel.php.
// *** Show picture ***
// *** get path of pictures folder 
$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='" . $tree_prefix . "'");
$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
$tree_pict_path = $dataDb->tree_pict_path;
if (substr($tree_pict_path, 0, 1) === '|') {
    $tree_pict_path = 'media/';
}

$EditorEvent = new EditorEvent($dbh);

// *** Editor icon for admin and editor: select family tree ***
if (isset($tree_id) && $tree_id) {
    $db_functions->set_tree_id($tree_id);
}

// TODO: this is a temporary copy of script in views/editor.php.
include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");
// TODO: this is a temporary copy of script in views/editor.php.
function hideshow_date_place($hideshow_date, $hideshow_place)
{
    // *** If date ends with ! then date isn't valid. Show red line ***
    $check_date = false;
    if (isset($hideshow_date) && substr($hideshow_date, -1) === '!') {
        $check_date = true;
        $hideshow_date = substr($hideshow_date, 0, -1);
    }
    $text = date_place($hideshow_date, $hideshow_place);
    if ($check_date) {
        $text = '<span style="background-color:#FFAA80">' . $text . '</span>';
    }
    return $text;
}
?>


<h1 class="center"><?= __('Sources'); ?></h1>
<?= __('These sources can be connected to multiple persons, families, events and other items.'); ?>

<?php if (isset($_POST['source_remove2'])) { ?>
    <div class="alert alert-success">
        <?= __('Source is removed!'); ?>
    </div>
<?php }; ?>

<?php if (isset($_POST['source_remove'])) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Are you sure you want to remove this source and ALL source references?'); ?></strong>
        <form method="post" action="index.php?page=edit_sources" style="display : inline;">
            <input type="hidden" name="source_id" value="<?= $editSource['source_id']; ?>">
            <input type="hidden" name="source_gedcomnr" value="<?= $_POST['source_gedcomnr']; ?>">
            <input type="submit" name="source_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="dummy5" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php }; ?>

<div class="p-3 my-md-2 genealogy_search container-md">
    <form method="POST" action="index.php?page=edit_sources" style="display : inline;">
        <div class="row mb-2">
            <div class="col-md-3">
                <?= select_tree($dbh, $page, $tree_id); ?>
            </div>

            <div class="col-md-3">
                <input type="text" name="source_search_gedcomnr" value="<?= $editSource['search_gedcomnr']; ?>" size="20" placeholder="<?= __('gedcomnumber (ID)'); ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-4">
                <input type="text" name="source_search" value="<?= $editSource['search_text']; ?>" size="20" placeholder="<?= __('Source'); ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <input type="submit" name="source_select" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
            </div>

        </div>
    </form>

    <div class="row">
        <div class="col-auto">
            <label for="tree" class="col-form-label">
                <?= __('Select source'); ?>:
            </label>
        </div>

        <div class="col-md-4">
            <form method="POST" action="index.php?page=edit_sources" style="display : inline;">
                <select size="1" name="source_id" class="form-select form-select-sm" onChange="this.form.submit();">
                    <option value=""><?= __('Select source'); ?></option>

                    <?php if (!isset($editSource['sources_id'])) { ?>
                        <option value=""><?= __('No sources found.'); ?></option>
                    <?php } else { ?>
                        <?php foreach ($editSource['sources_id'] as $source_id) { ?>
                            <option value="<?= $source_id; ?>" <?= $editSource['source_id'] == $source_id ? 'selected' : ''; ?>><?= $editSource['sources_text'][$source_id]; ?> [<?= $editSource['sources_gedcomnr'][$source_id] . $editSource['sources_restricted'][$source_id]; ?>]</option>
                        <?php } ?>

                        <?php if (count($editSource['sources_id']) == 200) { ?>
                            <option value=""><?= __('Results are limited, use search to find more sources.'); ?></option>
                        <?php } ?>
                    <?php } ?>

                </select>
            </form>
        </div>

        <div class="col-auto">
            <?= __('or'); ?>:
            <form method="POST" action="index.php?page=edit_sources" style="display : inline;">
                <input type="submit" name="add_source" value="<?= __('Add source'); ?>" class="btn btn-sm btn-secondary">
            </form>
        </div>
    </div>
</div>

<?php
// *** Show selected source ***
if ($editSource['source_id'] || isset($_POST['add_source'])) {
    if (isset($_POST['add_source'])) {
        $source_gedcomnr = '';
        $source_status = '';
        $source_title = '';
        $source_date = '';
        $source_place = '';
        $source_publ = '';
        $source_refn = '';
        $source_auth = '';
        $source_subj = '';
        $source_item = '';
        $source_kind = '';
        $source_text = '';
        $source_repo_caln = '';
        $source_repo_page = '';
        $source_repo_gedcomnr = '';
    } else {
        $source_qry = $dbh->query("SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "' AND source_id='" . $editSource['source_id'] . "'");
        //$sourceDb=$db_functions->get_source ($sourcenum);

        $die_message = __('No valid source number.');
        try {
            $sourceDb = $source_qry->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $die_message;
        }
        $source_gedcomnr = $sourceDb->source_gedcomnr;
        $source_status = $sourceDb->source_status;
        $source_title = $sourceDb->source_title;
        $source_date = $sourceDb->source_date;
        $source_place = $sourceDb->source_place;
        $source_publ = $sourceDb->source_publ;
        $source_refn = $sourceDb->source_refn;
        $source_auth = $sourceDb->source_auth;
        $source_auth = $sourceDb->source_auth;
        $source_subj = $sourceDb->source_subj;
        $source_item = $sourceDb->source_item;
        $source_kind = $sourceDb->source_kind;
        $source_text = $sourceDb->source_text;
        $source_repo_caln = $sourceDb->source_repo_caln;
        $source_repo_page = $sourceDb->source_repo_page;
        $source_repo_gedcomnr = $sourceDb->source_repo_gedcomnr;
    }

    $repo_qry = $dbh->query("SELECT * FROM humo_repositories WHERE repo_tree_id='" . $tree_id . "' ORDER BY repo_name, repo_place");
?>
    <form method="POST" action="index.php?page=edit_sources" style="display : inline;" enctype="multipart/form-data" name="form3" id="form3">
        <input type="hidden" name="source_id" value="<?= $editSource['source_id']; ?>">
        <input type="hidden" name="source_gedcomnr" value="<?= $source_gedcomnr; ?>">

        <div class="p-2 my-sm-2 genealogy_search container-md">

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Status'); ?></div>
                <div class="col-md-4">
                    <select size="1" name="source_status" class="form-select form-select-sm">
                        <option value="publish" <?= $source_status == 'publish' ? ' selected' : ''; ?>><?= __('publish'); ?></option>
                        <option value="restricted" <?= $source_status == 'restricted' ? ' selected' : ''; ?>><?= __('restricted'); ?></option>
                    </select>
                    <span style="font-size: 13px;"><?= __('restricted = only visible for selected user groups'); ?></span>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Title'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_title" value="<?= htmlspecialchars($source_title); ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Subject'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_subj" value="<?= htmlspecialchars($source_subj); ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Date'); ?></div>
                <div class="col-md-4">
                    <?php $editSource['editor_cls']->date_show($source_date, "source_date"); ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Place'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_place" value="<?= htmlspecialchars($source_place); ?>" size="50" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Repository'); ?></div>
                <div class="col-md-4">
                    <select size="1" name="source_repo_gedcomnr" class="form-select form-select-sm">
                        <option value=""></option>
                        <?php while ($repoDb = $repo_qry->fetch(PDO::FETCH_OBJ)) { ?>
                            <option value="<?= $repoDb->repo_gedcomnr; ?>" <?= $repoDb->repo_gedcomnr == $source_repo_gedcomnr ? ' selected' : ''; ?>>
                                <?= $repoDb->repo_gedcomnr; ?>, <?= $repoDb->repo_name; ?> <?= $repoDb->repo_place; ?></option>
                        <?php } ?>
                    </select>
                    <!-- For new repository in new database... -->
                    <span style="font-size: 13px;"><a href="index.php?page=edit_repositories"><?= __('Add repositories'); ?></a></span>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Publication'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_publ" value="<?= htmlspecialchars($source_publ); ?>" size="60" class="form-control form-control-sm">
                    <span style="font-size: 13px;">https://... <?= __('will be shown as a link.'); ?></span>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Own code'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_refn" value="<?= $source_refn; ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Author'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_auth" value="<?= $source_auth; ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Nr.'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_item" value="<?= $source_item; ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Kind'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_kind" value="<?= $source_kind; ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Archive'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_repo_caln" value="<?= $source_repo_caln; ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Page'); ?></div>
                <div class="col-md-4">
                    <input type="text" name="source_repo_page" value="<?= $source_repo_page; ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2"><?= __('Text'); ?></div>
                <div class="col-md-4">
                    <textarea rows="6" cols="80" name="source_text" <?= $field_text_large; ?> class="form-control form-control-sm"><?= $editSource['editor_cls']->text_show($source_text); ?></textarea>
                </div>
            </div>

            <!-- TODO replace table with div. Function must be rebuild -->
            <table class="humo standard" border="1">
                <!-- Picture by source -->
                <?php
                if (!isset($_POST['add_source'])) {
                    echo $EditorEvent->show_event('source', $sourceDb->source_gedcomnr, 'source_picture');
                ?>
                    <!-- Expand and collapse source items -->
                    <script>
                        function hideShow(el_id) {
                            // *** Hide or show item ***
                            var arr = document.getElementsByClassName('row' + el_id);
                            for (i = 0; i < arr.length; i++) {
                                if (arr[i].style.display != "none") {
                                    arr[i].style.display = "none";
                                } else {
                                    arr[i].style.display = "";
                                }
                            }
                        }
                    </script>
                <?php } ?>
            </table>

            <div class="row my-2">
                <div class="col-md-1"></div>
                <?php if (isset($_POST['add_source'])) { ?>
                    <div class="col-md-2"><?= __('Add'); ?></div>
                    <div class="col-md-4">
                        <input type="submit" name="source_add" value="<?= __('Add'); ?>" class="btn btn-sm btn-success">
                    </div>
                <?php } else { ?>
                    <div class="col-md-2"><?= __('Save'); ?></div>
                    <div class="col-md-4">
                        <input type="submit" name="source_change2" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">
                        <?= __('or'); ?> <input type="submit" name="source_remove" value="<?= __('Delete'); ?>" class="btn btn-sm btn-secondary">
                    </div>
                <?php } ?>
            </div>

        </div>

    </form>

    <?php
    // *** Source example in IFRAME ***
    if (!isset($_POST['add_source'])) {
        $vars['source_gedcomnr'] = $sourceDb->source_gedcomnr;
        $sourcestring = $link_cls->get_link('../', 'source', $tree_id, false, $vars);
    ?>
        <br><br><?= __('Preview'); ?><br>
        <iframe src="<?= $sourcestring; ?>" class="iframe">
            <p>Your browser does not support iframes.</p>
        </iframe>
<?php
    }
}
