<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$phpself2 = 'index.php?page=editor_sources' .
    '&connect_kind=' . rawurlencode((string) $editSources['connect_kind']) .
    '&connect_sub_kind=' . rawurlencode((string) $editSources['connect_sub_kind']) .
    '&connect_connect_id=' . rawurlencode((string) $editSources['connect_connect_id']);
$event_person = isset($_POST['event_person']) || isset($_GET['event_person']);
$event_family = isset($_POST['event_family']) || isset($_GET['event_family']);
if ($event_person) {
    $phpself2 .= '&event_person=1';
}
if ($event_family) {
    $phpself2 .= '&event_family=1';
}

// *** Process queries ***
$editor_cls = new \Genealogy\Include\Editor_cls;
$editorModel = new \Genealogy\Admin\Models\EditorModel($admin_config, $tree_prefix, $editor_cls);
$editor['confirm'] = $editorModel->update_editor2();

$languageDate = new \Genealogy\Include\LanguageDate();
$validateGedcomnumber = new \Genealogy\Include\ValidateGedcomnumber();
$HtmlEscaper = new \Genealogy\Include\HtmlEscaper();

$db_functions->set_tree_id($tree_id);

// *** Search for all connected sources ***
$connect_sql = $db_functions->get_connections_connect_id($editSources['connect_kind'], $editSources['connect_sub_kind'], $editSources['connect_connect_id']);
$nr_sources = count($connect_sql);
?>

<h3><?= __('Source'); ?> - <?= $HtmlEscaper->escHtml((string) $editSources['source_header']); ?></h3>

<form method="POST" action="<?= $HtmlEscaper->escHtml($phpself2); ?>" class="mt-3">
    <input type="submit" name="submit" title="submit" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">

    <!-- TODO check this. Allready added in $phpself2 link -->
    <?php if ($event_person) { ?>
        <input type="hidden" name="event_person" value="1">
    <?php } ?>
    <?php if ($event_family) { ?>
        <input type="hidden" name="event_family" value="1">
    <?php } ?>

    <ul
        id="sortable<?= $HtmlEscaper->escHtml((string) $editSources['connect_kind'] . $editSources['connect_sub_kind'] . $editSources['connect_connect_id']); ?>"
        class="sortable"
        data-handle="handle<?= $HtmlEscaper->escHtml((string) $editSources['connect_kind'] . $editSources['connect_sub_kind'] . $editSources['connect_connect_id']); ?>"
        style="padding-left:0px;">

        <?php foreach ($connect_sql as $connectDb) { ?>
            <li class="genealogy_search my-2 p-2">
                <span style="cursor:move;" id="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>" class="handle<?= $HtmlEscaper->escHtml((string) $editSources['connect_kind'] . $editSources['connect_sub_kind'] . $editSources['connect_connect_id']); ?>">
                    <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                </span>

                <input type="hidden" name="connect_change[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>">
                <input type="hidden" name="connect_connect_id[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_connect_id); ?>">
                <?php if (isset($editSources['fam_gedcomnumber'])) { ?>
                    <input type="hidden" name="marriage_nr[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $editSources['fam_gedcomnumber']); ?>">
                <?php } ?>
                <input type="hidden" name="connect_kind[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $editSources['connect_kind']); ?>">
                <input type="hidden" name="connect_sub_kind[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $editSources['connect_sub_kind']); ?>">
                <input type="hidden" name="connect_item_id[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="">

                <?php
                echo ' <a href="index.php?page=editor_sources&amp;connect_drop=' . rawurlencode((string) $connectDb->connect_id);
                // *** Needed for events **
                echo '&amp;connect_kind=' . rawurlencode((string) $editSources['connect_kind']);
                echo '&amp;connect_sub_kind=' . rawurlencode((string) $editSources['connect_sub_kind']);
                echo '&amp;connect_connect_id=' . rawurlencode((string) $editSources['connect_connect_id']);
                if ($event_person) {
                    echo '&amp;event_person=1';
                }
                if ($event_family) {
                    echo '&amp;event_family=1';
                }
                if (isset($editSources['fam_gedcomnumber'])) {
                    echo '&amp;marriage_nr=' . rawurlencode((string) $editSources['fam_gedcomnumber']);
                }
                echo '"><img src="images/button_drop.png" border="0" alt="remove"></a>';
                ?>

                <?php
                if ($connectDb->connect_source_id != '') {
                    $sourceDb = $db_functions->get_source($connectDb->connect_source_id);

                    $display = ' display:none;';
                    if (!$sourceDb->source_title && !$sourceDb->source_text) {
                        $display = '';
                    }
                    $hideshow = '8' . (int) $connectDb->connect_id;
                    $text = '[' . $HtmlEscaper->escHtml((string) $connectDb->connect_source_id) . '] ';
                    if ($sourceDb->source_title) {
                        $text .= $HtmlEscaper->escHtml((string) $sourceDb->source_title);
                    } else {
                        $text .= ' [' . __('Source') . ']';
                    }
                    echo ' <span class="hideshowlink fs-4" onclick="hideShow(' . $hideshow . ');">' . $text;
                    //if ($check_text){
                    //  $return_text .= ' <img src="images/text.png" height="16" alt="' . __('Text') . '">';
                    //}
                    echo '</span>';
                }

                if ($connectDb->connect_source_id != '') {
                    //$sourceDb = $db_functions->get_source($connectDb->connect_source_id);
                    $field_date = 12; // Size of date field (function date_show).
                    //$field_text = 'style="height: 60px; width:550px"';
                    $field_text = 'style="height: 60px;"';
                    $connect_role = '';
                    if ($connectDb->connect_role) {
                        $connect_role = $connectDb->connect_role;
                    }
                    $connect_place = '';
                    if ($connectDb->connect_place) {
                        $connect_place = $connectDb->connect_place;
                    }

                    $repo_qry = $dbh->prepare("SELECT * FROM humo_repositories WHERE repo_tree_id = :tree_id ORDER BY repo_name, repo_place");
                    $repo_qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
                    $repo_qry->execute();

                    $field_extra_text = 'style="height: 20px; width:500px"';
                ?>
                    <span class="humo row<?= (int) $hideshow; ?>" style="margin-left:0px;<?= $HtmlEscaper->escHtml($display); ?>">
                        <div class="m-2 p-2" style="border: 2px solid #4a90e2">
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <h2><?= __('Source'); ?></h2>
                                </div>
                            </div>

                            <input type="hidden" name="connect_source_id[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_source_id); ?>">
                            <input type="hidden" name="source_id[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_id); ?>">

                            <div class="row mb-1">
                                <label for="source_title" class="col-sm-3 col-form-label"><?= __('Status'); ?></label>
                                <div class="col-md-7">
                                    <select size="1" id="source_status" name="source_status[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" class="form-select form-select-sm">
                                        <option value="publish" <?= $sourceDb->source_status == 'publish' ? ' selected' : ''; ?>><?= __('publish'); ?></option>
                                        <option value="restricted" <?= $sourceDb->source_status == 'restricted' ? ' selected' : ''; ?>><?= __('restricted'); ?></option>
                                    </select>
                                    <span style="font-size: 13px;"><?= __('restricted = only visible for selected user groups'); ?></span>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_title" class="col-sm-3 col-form-label"><?= __('Title'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_title[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_title); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_subj" class="col-sm-3 col-form-label"><?= __('Subject'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_subj[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_subj); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
                                <div class="col-md-7">
                                    <?php $editor_cls->date_show($sourceDb->source_date, 'source_date', "[$connectDb->connect_id]"); ?>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_place[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_place); ?>" placeholder="<?= $HtmlEscaper->escHtml(__('Start typing to search for a place.')); ?>" size="15" class="place-autocomplete form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_publ" class="col-sm-3 col-form-label"><?= __('Publication'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_publ[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_publ); ?>" size="15" class="form-control form-control-sm">
                                    <span style="font-size: 13px;">https://... <?= __('will be shown as a link.'); ?></span>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_own_code" class="col-sm-3 col-form-label"><?= __('Own code'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_refn[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_refn); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_auth" class="col-sm-3 col-form-label"><?= __('Author'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_auth[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_auth); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_item" class="col-sm-3 col-form-label"><?= __('Nr.'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_item[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_item); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_kind" class="col-sm-3 col-form-label"><?= __('Kind'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_kind[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_kind); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_place" class="col-sm-3 col-form-label"><?= __('Repository'); ?></label>
                                <div class="col-md-7">
                                    <select size="1" id="source_repo_gedcomnr" name="source_repo_gedcomnr[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" class="form-select form-select-sm">
                                        <option value=""></option>
                                        <?php while ($repoDb = $repo_qry->fetch(PDO::FETCH_OBJ)) { ?>
                                            <option value="<?= $HtmlEscaper->escHtml((string) $repoDb->repo_gedcomnr); ?>" <?= $repoDb->repo_gedcomnr == $sourceDb->source_repo_gedcomnr ? ' selected' : ''; ?>>
                                                <?= $HtmlEscaper->escHtml((string) $repoDb->repo_gedcomnr); ?>, <?= $HtmlEscaper->escHtml((string) $repoDb->repo_name); ?> <?= $HtmlEscaper->escHtml((string) $repoDb->repo_place); ?></option>
                                        <?php } ?>
                                    </select>
                                    <!-- For new repository in new database... -->
                                    <?php /*
                                    <span style="font-size: 13px;">
                                        <a href="index.php?page=edit_repositories"><?= __('Add repositories'); ?></a>
                                    </span>
                                    */ ?>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_repo_caln" class="col-sm-3 col-form-label"><?= __('Repository call number'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_repo_caln[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_repo_caln); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <label for="source_repo_page" class="col-sm-3 col-form-label"><?= __('Repository page'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_repo_page[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_repo_page); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- TODO Picture by source -->

                            <div class="row mb-1">
                                <label for="source_text" class="col-sm-3 col-form-label"><?= __('Text'); ?></label>
                                <div class="col-md-7">
                                    <textarea rows="2" name="source_text[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" <?= $field_text; ?> class=" form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $editor_cls->text_show($sourceDb->source_text)); ?></textarea>
                                </div>
                            </div>

                            <?php
                            // *** Source added by user ***
                            if ($sourceDb->source_new_user_id || $sourceDb->source_new_datetime) {
                            ?>
                                <div class="row mb-1">
                                    <div class="col-md-3"><?= __('Added by'); ?></div>
                                    <div class="col-md-7">
                                        <?= $HtmlEscaper->escHtml((string) $languageDate->show_datetime($sourceDb->source_new_datetime) . ' ' . $db_functions->get_user_name($sourceDb->source_new_user_id)); ?>
                                    </div>
                                </div>
                            <?php
                            }

                            // *** Source changed by user ***
                            if ($sourceDb->source_changed_user_id || $sourceDb->source_changed_datetime) {
                            ?>
                                <div class="row mb-1">
                                    <div class="col-md-3"><?= __('Changed by'); ?></div>
                                    <div class="col-md-7">
                                        <?= $HtmlEscaper->escHtml((string) $languageDate->show_datetime($sourceDb->source_changed_datetime) . ' ' . $db_functions->get_user_name($sourceDb->source_changed_user_id)); ?>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>

                        <div class="row mb-1">
                            <div class="col-md-12">
                                <h2><?= __('Source citation'); ?></h2>
                                <span style="font-size:13px;"><?= __('Information about how this source is connected to the selected person/family/event.'); ?></span>
                            </div>
                        </div>

                        <!-- Source connection items -->
                        <div class="row mb-1">
                            <label for="source_role" class="col-sm-3 col-form-label"><?= __('Sourcerole'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="connect_role[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connect_role); ?>" size="6" class="form-control form-control-sm">
                                <span style="font-size:13px;"><?= __('e.g. Writer, Brother, Sister, Father.'); ?></span>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="source_page" class="col-sm-3 col-form-label"><?= __('Page'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="connect_page[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_page); ?>" size="6" class="form-control form-control-sm">
                                <span style="font-size:13px;"><?= __('Page in source.'); ?></span>
                            </div>
                        </div>

                        <!-- Quality -->
                        <div class="row mb-1">
                            <label for="source_quality" class="col-sm-3 col-form-label"><?= __('Quality'); ?></label>
                            <div class="col-md-7">
                                <select size="1" id="source_quality" name="connect_quality[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" class="form-select form-select-sm">
                                    <option value=""><?= ucfirst(__('quality: default')); ?></option>
                                    <option value="0" <?php if ($connectDb->connect_quality == '0') echo ' selected'; ?>><?= ucfirst(__('quality: unreliable evidence or estimated data')); ?></option>
                                    <option value="1" <?php if ($connectDb->connect_quality == '1') echo ' selected'; ?>><?= ucfirst(__('quality: questionable reliability of evidence')); ?></option>
                                    <option value="2" <?php if ($connectDb->connect_quality == '2') echo ' selected'; ?>><?= ucfirst(__('quality: data from secondary evidence')); ?></option>
                                    <option value="3" <?php if ($connectDb->connect_quality == '3') echo ' selected'; ?>><?= ucfirst(__('quality: data from direct source')); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="connect_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($connectDb->connect_date, 'connect_date', "[$connectDb->connect_id]"); ?>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="connect_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="connect_place[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connect_place); ?>" size="15" class="form-control form-control-sm">
                            </div>
                        </div>

                        <!-- Extra text by shared source -->
                        <div class="row mb-1">
                            <label for="connect_text" class="col-sm-3 col-form-label"><?= __('Extra text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="2" name="connect_text[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" <?= $field_extra_text; ?> class="form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $editor_cls->text_show($connectDb->connect_text)); ?></textarea>
                            </div>
                        </div>
                    </span>
                <?php
                } else {
                    // *** Add new source or select existing source ***
                    $source_search_gedcomnr = '';
                    if (isset($_POST['source_search_gedcomnr']) && $validateGedcomnumber->validate($_POST['source_search_gedcomnr'])) {
                        $source_search_gedcomnr = $_POST['source_search_gedcomnr'];
                    }
                    $source_search = '';
                    if (isset($_POST['source_search'])) {
                        $source_search = $safeTextDb->safe_text_db($_POST['source_search']);
                    }

                    // *** Source: pull-down menu ***
                    $qry = "SELECT * FROM humo_sources WHERE source_tree_id = :tree_id";
                    $params = [':tree_id' => $tree_id];
                    if (!empty($_POST['source_search_gedcomnr'])) {
                        $qry .= " AND source_gedcomnr LIKE :gedcomnr";
                        $params[':gedcomnr'] = '%' . $_POST['source_search_gedcomnr'] . '%';
                    }
                    if (!empty($_POST['source_search'])) {
                        $qry .= " AND (source_title LIKE :search OR (source_title = '' AND source_text LIKE :search))";
                        $params[':search'] = '%' . $_POST['source_search'] . '%';
                    }
                    $qry .= " ORDER BY IF(source_title != '', source_title, source_text)";

                    $source_qry = $dbh->prepare($qry);
                    $source_qry->execute($params);
                ?>

                    <h3><?= __('Search existing source'); ?></h3>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <input type="text" name="source_search_gedcomnr" value="<?= $HtmlEscaper->escHtml((string) $source_search_gedcomnr); ?>" size="20" placeholder="<?= $HtmlEscaper->escHtml(__('gedcomnumber (ID)')); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="source_search" value="<?= $HtmlEscaper->escHtml((string) $source_search); ?>" size="20" placeholder="<?= $HtmlEscaper->escHtml(__('text')); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <input type="submit" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-10">
                            <select size="1" name="connect_source_id[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" aria-label="<?= $HtmlEscaper->escHtml(__('Select existing source')); ?>" class="form-select form-select-sm">
                                <option value=""><?= __('Select existing source'); ?>:</option>
                                <?php while ($sourceDb = $source_qry->fetch(PDO::FETCH_OBJ)) { ?>
                                    <option value="<?= $HtmlEscaper->escHtml((string) $sourceDb->source_gedcomnr); ?>">
                                        <?php
                                        if ($sourceDb->source_title) {
                                            echo $HtmlEscaper->escHtml((string) $sourceDb->source_title);
                                        } else {
                                            echo $HtmlEscaper->escHtml(substr((string) $sourceDb->source_text, 0, 40));
                                            if (strlen($sourceDb->source_text) > 40) {
                                                echo '...';
                                            }
                                        }
                                        ?>
                                        [<?= $HtmlEscaper->escHtml((string) $sourceDb->source_gedcomnr); ?>]
                                    </option>
                                <?php } ?>
                                <option value="">*** <?= __('Results are limited, use search to find more sources.'); ?> ***</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" name="submit" title="submit" value="<?= __('Select'); ?>" class="btn btn-sm btn-secondary">
                        </div>
                    </div>

                    <!-- Add new source -->
                    <br><?= __('Or:'); ?>
                    <a href="index.php?page=editor_sources&amp;source_add2=1&amp;connect_id=<?= rawurlencode((string) $connectDb->connect_id); ?>
                        &amp;connect_order=<?= rawurlencode((string) $connectDb->connect_order); ?>&amp;connect_kind=<?= rawurlencode((string) $connectDb->connect_kind); ?>
                        &amp;connect_sub_kind=<?= rawurlencode((string) $connectDb->connect_sub_kind); ?>&amp;connect_connect_id=<?= rawurlencode((string) $connectDb->connect_connect_id); ?>
                    <?php
                    if ($event_person) {
                        echo '&amp;event_person=1';
                    }
                    if ($event_family) {
                        echo '&amp;event_family=1';
                    }
                    ?>
                        #addresses"><?= __('add new source'); ?>
                    </a>

                    <input type="hidden" name="connect_role[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="">
                    <input type="hidden" name="connect_page[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="">
                    <input type="hidden" name="connect_quality[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="">
                    <input type="hidden" name="connect_text[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="">
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
</form>

<!-- Add new source connection -->
<?php if (!isset($_POST['connect_add'])) { ?>
    <form method="POST" action="<?= $HtmlEscaper->escHtml($phpself2); ?>">
        <!-- TODO check this. Allready added in $phpself2 link -->
        <?php if ($event_person) { ?>
            <input type="hidden" name="event_person" value="1">
        <?php } ?>
        <?php if ($event_family) { ?>
            <input type="hidden" name="event_family" value="1">
        <?php } ?>

        <input type="hidden" name="connect_kind" value="<?= $HtmlEscaper->escHtml((string) $editSources['connect_kind']); ?>">
        <input type="hidden" name="connect_sub_kind" value="<?= $HtmlEscaper->escHtml((string) $editSources['connect_sub_kind']); ?>">
        <input type="hidden" name="connect_connect_id" value="<?= $HtmlEscaper->escHtml((string) $editSources['connect_connect_id']); ?>">
        <?php if (isset($editSources['fam_gedcomnumber'])) { ?>
            <input type="hidden" name="marriage_nr" value="<?= $HtmlEscaper->escHtml((string) $editSources['fam_gedcomnumber']); ?>">
        <?php } ?>

        <?php if ($nr_sources > 0) { ?>
            <input type="submit" name="connect_add" value="<?= __('Add another source'); ?>" class="btn btn-sm btn-secondary">
        <?php } else { ?>
            <input type="submit" name="connect_add" value="<?= __('Add source'); ?>" class="btn btn-sm btn-secondary">
        <?php } ?>
    </form>
<?php } ?>

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

<!-- Script for ordering sources -->
<?php if (count($connect_sql) > 0) { ?>
    <script src="../assets/js/order_sources.js"></script>
<?php
}
?>

<!-- Autocomplete for place names -->
<script>
    var autocompleteSource = "../include/AutocompletePlace.php";
</script>
<script src="../assets/js/place_autocomplete.js"></script>