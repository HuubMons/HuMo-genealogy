<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$phpself2 = 'index.php?page=editor_sources' .
    '&connect_kind=' . $editSources['connect_kind'] .
    '&connect_sub_kind=' . $editSources['connect_sub_kind'] .
    '&connect_connect_id=' . $editSources['connect_connect_id'];
$event_person = isset($_POST['event_person']) || isset($_GET['event_person']);
$event_family = isset($_POST['event_family']) || isset($_GET['event_family']);
if ($event_person) {
    $phpself2 .= '&event_person=1';
}
if ($event_family) {
    $phpself2 .= '&event_family=1';
}

// *** Process queries ***
$editor_cls = new Editor_cls;
$editorModel = new EditorModel($admin_config, $tree_prefix, $editor_cls);
$editor['confirm'] = $editorModel->update_editor2();

$languageDate = new LanguageDate();

$db_functions->set_tree_id($tree_id);

// *** Search for all connected sources ***
$connect_sql = $db_functions->get_connections_connect_id($editSources['connect_kind'], $editSources['connect_sub_kind'], $editSources['connect_connect_id']);
$nr_sources = count($connect_sql);
?>

<b><?= __('Source'); ?> - <?= $editSources['source_header']; ?></b>

<form method="POST" action="<?= $phpself2; ?>">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="submit" name="submit" title="submit" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">

    <?php if ($event_person) { ?>
        <input type="hidden" name="event_person" value="1">
    <?php } ?>
    <?php if ($event_family) { ?>
        <input type="hidden" name="event_family" value="1">
    <?php } ?>

    <ul id="sortable<?= $editSources['connect_kind'] . $editSources['connect_sub_kind'] . $editSources['connect_connect_id']; ?>" class="sortable" style="padding-left:0px;">

        <?php foreach ($connect_sql as $connectDb) { ?>
            <li>
                <span style="cursor:move;" id="<?= $connectDb->connect_id; ?>" class="handle<?= $editSources['connect_kind'] . $editSources['connect_sub_kind'] . $editSources['connect_connect_id']; ?>">
                    <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                </span>

                <input type="hidden" name="connect_change[<?= $connectDb->connect_id; ?>]" value="<?= $connectDb->connect_id; ?>">
                <input type="hidden" name="connect_connect_id[<?= $connectDb->connect_id; ?>]" value="<?= $connectDb->connect_connect_id; ?>">
                <?php
                if (isset($editSources['fam_gedcomnumber'])) {
                    echo '<input type="hidden" name="marriage_nr[' . $connectDb->connect_id . ']" value="' . $editSources['fam_gedcomnumber'] . '">';
                }
                echo '<input type="hidden" name="connect_kind[' . $connectDb->connect_id . ']" value="' . $editSources['connect_kind'] . '">';
                echo '<input type="hidden" name="connect_sub_kind[' . $connectDb->connect_id . ']" value="' . $editSources['connect_sub_kind'] . '">';
                echo '<input type="hidden" name="connect_item_id[' . $connectDb->connect_id . ']" value="">';

                echo ' <a href="index.php?page=' . $page . '&amp;connect_drop=' . $connectDb->connect_id;
                // *** Needed for events **
                echo '&amp;connect_kind=' . $editSources['connect_kind'];
                echo '&amp;connect_sub_kind=' . $editSources['connect_sub_kind'];
                echo '&amp;connect_connect_id=' . $editSources['connect_connect_id'];
                if ($event_person) {
                    echo '&amp;event_person=1';
                }
                if ($event_family) {
                    echo '&amp;event_family=1';
                }
                if (isset($editSources['fam_gedcomnumber'])) {
                    echo '&amp;marriage_nr=' . $editSources['fam_gedcomnumber'];
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
                    $hideshow = '8' . $connectDb->connect_id;
                    $text = '[' . $connectDb->connect_source_id . '] ';
                    if ($sourceDb->source_title) {
                        $text .= htmlspecialchars($sourceDb->source_title);
                    } else {
                        $text .= ' [' . __('Source') . ']';
                    }
                    echo ' <span class="hideshowlink" onclick="hideShow(' . $hideshow . ');">' . $text;
                    //if ($check_text) $return_text .= ' <img src="images/text.png" height="16" alt="' . __('text') . '">';
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
                    $field_extra_text = 'style="height: 20px; width:500px"';
                ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;<?= $display; ?>">
                        <div style="border: 2px solid red">
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <h2><?= __('Source'); ?></h2>
                                </div>
                            </div>

                            <input type="hidden" name="connect_source_id[<?= $connectDb->connect_id; ?>]" value="<?= $connectDb->connect_source_id; ?>">
                            <input type="hidden" name="source_id[<?= $connectDb->connect_id; ?>]" value="<?= $sourceDb->source_id; ?>">

                            <div class="row mb-2">
                                <label for="source_title" class="col-sm-3 col-form-label"><?= __('Title'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_title[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($sourceDb->source_title); ?>" size="60" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="source_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
                                <div class="col-md-7">
                                    <?php $editor_cls->date_show($sourceDb->source_date, 'source_date', "[$connectDb->connect_id]"); ?>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="source_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_place[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($sourceDb->source_place); ?>" size="15" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="source_own_code" class="col-sm-3 col-form-label"><?= __('Own code'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="source_refn[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($sourceDb->source_refn); ?>" size="15" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="source_text" class="col-sm-3 col-form-label"><?= __('Text'); ?></label>
                                <div class="col-md-7">
                                    <textarea rows="2" name="source_text[<?= $connectDb->connect_id; ?>]" <?= $field_text; ?> class=" form-control form-control-sm"><?= $editor_cls->text_show($sourceDb->source_text); ?></textarea>
                                </div>
                            </div>

                            <!-- TODO Picture by source -->

                            <?php
                            // *** Source added by user ***
                            if ($sourceDb->source_new_user_id || $sourceDb->source_new_datetime) {
                            ?>
                                <div class="row mb-2">
                                    <div class="col-md-3"><?= __('Added by'); ?></div>
                                    <div class="col-md-7">
                                        <?= $languageDate->show_datetime($sourceDb->source_new_datetime) . ' ' . $db_functions->get_user_name($sourceDb->source_new_user_id); ?>
                                    </div>
                                </div>
                            <?php
                            }

                            // *** Source changed by user ***
                            if ($sourceDb->source_changed_user_id || $sourceDb->source_changed_datetime) {
                            ?>
                                <div class="row mb-2">
                                    <div class="col-md-3"><?= __('Changed by'); ?></div>
                                    <div class="col-md-7">
                                        <?= $languageDate->show_datetime($sourceDb->source_changed_datetime) . ' ' . $db_functions->get_user_name($sourceDb->source_changed_user_id); ?>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>

                        <div class="row mb-2">
                            <div class="col-md-12">
                                <h2><?= __('Source citation'); ?></h2>
                            </div>
                        </div>

                        <!-- Source connection items -->
                        <div class="row mb-2">
                            <label for="source_role" class="col-sm-3 col-form-label"><?= __('Sourcerole'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="connect_role[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($connect_role); ?>" size="6" class="form-control form-control-sm">
                                <span style="font-size:13px;"><?= __('e.g. Writer, Brother, Sister, Father.'); ?></span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="source_page" class="col-sm-3 col-form-label"><?= __('Page'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="connect_page[<?= $connectDb->connect_id; ?>]" value="<?= $connectDb->connect_page; ?>" size="6" class="form-control form-control-sm">
                                <span style="font-size:13px;"><?= __('Page in source.'); ?></span>
                            </div>
                        </div>

                        <!-- Quality -->
                        <div class="row mb-2">
                            <label for="source_quality" class="col-sm-3 col-form-label"><?= __('Quality'); ?></label>
                            <div class="col-md-7">
                                <select size="1" name="connect_quality[<?= $connectDb->connect_id; ?>]" class="form-select form-select-sm">
                                    <option value=""><?= ucfirst(__('quality: default')); ?></option>
                                    <option value="0" <?php if ($connectDb->connect_quality == '0') echo ' selected'; ?>><?= ucfirst(__('quality: unreliable evidence or estimated data')); ?></option>
                                    <option value="1" <?php if ($connectDb->connect_quality == '1') echo ' selected'; ?>><?= ucfirst(__('quality: questionable reliability of evidence')); ?></option>
                                    <option value="2" <?php if ($connectDb->connect_quality == '2') echo ' selected'; ?>><?= ucfirst(__('quality: data from secondary evidence')); ?></option>
                                    <option value="3" <?php if ($connectDb->connect_quality == '3') echo ' selected'; ?>><?= ucfirst(__('quality: data from direct source')); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="connect_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($connectDb->connect_date, 'connect_date', "[$connectDb->connect_id]"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="connect_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="connect_place[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($connect_place); ?>" size="15" class="form-control form-control-sm">
                            </div>
                        </div>

                        <!-- Extra text by shared source -->
                        <div class="row mb-2">
                            <label for="connect_text" class="col-sm-3 col-form-label"><?= __('Extra text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="2" name="connect_text[<?= $connectDb->connect_id; ?>]" <?= $field_extra_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($connectDb->connect_text); ?></textarea>
                            </div>
                        </div>
                    </span>
                <?php
                } else {
                    // *** Add new source or select existing source ***
                    $source_search_gedcomnr = '';
                    if (isset($_POST['source_search_gedcomnr'])) {
                        $source_search_gedcomnr = $safeTextDb->safe_text_db($_POST['source_search_gedcomnr']);
                    }
                    $source_search = '';
                    if (isset($_POST['source_search'])) {
                        $source_search = $safeTextDb->safe_text_db($_POST['source_search']);
                    }

                    // *** Source: pull-down menu ***
                    // TODO only get necesary items
                    $qry = "SELECT * FROM humo_sources WHERE source_tree_id='" . $safeTextDb->safe_text_db($tree_id) . "'";
                    if (isset($_POST['source_search_gedcomnr'])) {
                        $qry .= " AND source_gedcomnr LIKE '%" . $safeTextDb->safe_text_db($_POST['source_search_gedcomnr']) . "%'";
                    }
                    if (isset($_POST['source_search'])) {
                        $qry .= " AND ( source_title LIKE '%" . $safeTextDb->safe_text_db($_POST['source_search']) . "%' OR (source_title='' AND source_text LIKE '%" . $safeTextDb->safe_text_db($source_search) . "%') )";
                    }
                    $qry .= " ORDER BY IF (source_title!='',source_title,source_text)";
                    //$qry.=" ORDER BY IF (source_title!='',source_title,source_text) LIMIT 0,500";
                    $source_qry = $dbh->query($qry);
                ?>

                    <h3><?= __('Search existing source'); ?></h3>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <input type="text" name="source_search_gedcomnr" value="<?= $source_search_gedcomnr; ?>" size="20" placeholder="<?= __('gedcomnumber (ID)'); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="source_search" value="<?= $source_search; ?>" size="20" placeholder="<?= __('text'); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <input type="submit" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-10">
                            <select size="1" name="connect_source_id[<?= $connectDb->connect_id; ?>]" class="form-select form-select-sm">
                                <option value=""><?= __('Select existing source'); ?>:</option>
                                <?php while ($sourceDb = $source_qry->fetch(PDO::FETCH_OBJ)) { ?>
                                    <option value="<?= $sourceDb->source_gedcomnr; ?>">
                                        <?php
                                        if ($sourceDb->source_title) {
                                            echo $sourceDb->source_title;
                                        } else {
                                            echo substr($sourceDb->source_text, 0, 40);
                                            if (strlen($sourceDb->source_text) > 40) {
                                                echo '...';
                                            }
                                        }
                                        ?>
                                        [<?= $sourceDb->source_gedcomnr; ?>]
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
                    <a href="index.php?page=<?= $page; ?>&amp;source_add2=1&amp;connect_id=<?= $connectDb->connect_id; ?>
                            &amp;connect_order=<?= $connectDb->connect_order; ?>&amp;connect_kind=<?= $connectDb->connect_kind; ?>
                            &amp;connect_sub_kind=<?= $connectDb->connect_sub_kind; ?>&amp;connect_connect_id=<?= $connectDb->connect_connect_id; ?>
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

                    <input type="hidden" name="connect_role[<?= $connectDb->connect_id; ?>]" value="">
                    <input type="hidden" name="connect_page[<?= $connectDb->connect_id; ?>]" value="">
                    <input type="hidden" name="connect_quality[<?= $connectDb->connect_id; ?>]" value="">
                    <input type="hidden" name="connect_text[<?= $connectDb->connect_id; ?>]" value="">
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
</form>

<!-- Add new source connection -->
<?php if (!isset($_POST['connect_add'])) { ?>
    <h3><?= __('Add'); ?></h3>
    <form method="POST" action="<?= $phpself2; ?>">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <?php if ($event_person) { ?>
            <input type="hidden" name="event_person" value="1">
        <?php } ?>
        <?php if ($event_family) { ?>
            <input type="hidden" name="event_family" value="1">
        <?php } ?>
        <input type="hidden" name="connect_kind" value="<?= $editSources['connect_kind']; ?>">
        <input type="hidden" name="connect_sub_kind" value="<?= $editSources['connect_sub_kind']; ?>">
        <input type="hidden" name="connect_connect_id" value="<?= $editSources['connect_connect_id']; ?>">
        <?php if (isset($editSources['fam_gedcomnumber'])) { ?>
            <input type="hidden" name="marriage_nr" value="<?= $editSources['fam_gedcomnumber']; ?>">
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
    <script>
        $('#sortable<?= $editSources['connect_kind'] . $editSources['connect_sub_kind'] . $editSources['connect_connect_id']; ?>').sortable({
            handle: '.handle<?= $editSources['connect_kind'] . $editSources['connect_sub_kind'] . $editSources['connect_connect_id']; ?>'
        }).bind('sortupdate', function() {
            var childstring = "";
            var chld_arr = document.getElementsByClassName(" handle<?= $editSources['connect_kind'] . $editSources['connect_sub_kind'] . $editSources['connect_connect_id']; ?>");
            for (var z = 0; z < chld_arr.length; z++) {
                childstring = childstring + chld_arr[z].id + ";";
                //document.getElementById('chldnum' + chld_arr[z].id).innerHTML=(z + 1);
            }
            childstring = childstring.substring(0, childstring.length - 1);
            $.ajax({
                url: "include/drag.php?drag_kind=sources&sourcestring=" + childstring,
                success: function(data) {},
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status);
                    alert(thrownError);
                }
            });
        });
    </script>
<?php
}
