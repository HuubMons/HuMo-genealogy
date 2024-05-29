<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

include_once(__DIR__ . "/editor_cls.php");
$editor_cls = new editor_cls;

if (isset($_SESSION['admin_pers_gedcomnumber'])) {
    $pers_gedcomnumber = $_SESSION['admin_pers_gedcomnumber'];
}
if (isset($_SESSION['admin_fam_gedcomnumber'])) {
    $marriage = $_SESSION['admin_fam_gedcomnumber'];
}
//if (isset($_SESSION['admin_address_gedcomnumber'])){ $address_gedcomnr=$_SESSION['admin_address_gedcomnumber']; }


//echo '<br>'.$pers_gedcomnumber;

// *** Needed for event sources ***
$connect_kind = '';
if (isset($_GET['connect_kind'])) {
    $connect_kind = $_GET['connect_kind'];
}
//if (isset($_POST['connect_kind'])) $connect_kind=$_POST['connect_kind'];

$connect_sub_kind = '';
if (isset($_GET['connect_sub_kind'])) {
    $connect_sub_kind = $_GET['connect_sub_kind'];
}
//if (isset($_POST['connect_sub_kind'])) $connect_sub_kind=$_POST['connect_sub_kind'];

// *** Needed for event sources ***
$connect_connect_id = '';
if (isset($_GET['connect_connect_id']) && $_GET['connect_connect_id']) {
    $connect_connect_id = $_GET['connect_connect_id'];
}
//if (isset($_POST['connect_connect_id']) AND $_POST['connect_connect_id']) $connect_connect_id=$_POST['connect_connect_id'];

$event_link = '';
if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
    $event_link = '&event_person=1';
}
if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
    $event_link = '&event_family=1';
}

$phpself2 = 'index.php?page=editor_sources&connect_kind=' . $connect_kind . '&connect_sub_kind=' . $connect_sub_kind . '&connect_connect_id=' . $connect_connect_id;
$phpself2 .= $event_link;

// *** Process queries ***
include_once(__DIR__ . "/editor_inc.php");

// **************************
// *** Show source editor ***
// **************************
function show_source_header($source_header)
{
    //echo '<h2>'.$source_header.'</h2>';
    echo '<b>' . $source_header . '</b>';
}

if ($connect_sub_kind == 'pers_name_source') {
    show_source_header(__('Name source'));
    source_edit("person", "pers_name_source", $pers_gedcomnumber);
}

// *** Edit source by sex ***
if ($connect_sub_kind == 'pers_sexe_source') {
    show_source_header(__('Source') . ' - ' . __('Sex'));
    source_edit("person", "pers_sexe_source", $pers_gedcomnumber);
}

// *** Edit source by birth ***
if ($connect_sub_kind == 'pers_birth_source') {
    show_source_header(__('Source') . ' - ' . ucfirst(__('born')));
    source_edit("person", "pers_birth_source", $pers_gedcomnumber);
}

// *** Edit source by baptise ***
if ($connect_sub_kind == 'pers_bapt_source') {
    show_source_header(__('Source') . ' - ' . ucfirst(__('baptised')));
    source_edit("person", "pers_bapt_source", $pers_gedcomnumber);
}

// *** Edit source by death ***
if ($connect_sub_kind == 'pers_death_source') {
    show_source_header(__('Source') . ' - ' . ucfirst(__('died')));
    source_edit("person", "pers_death_source", $pers_gedcomnumber);
}

// *** Edit source by buried ***
if ($connect_sub_kind == 'pers_buried_source') {
    show_source_header(__('Source') . ' - ' . ucfirst(__('buried')));
    source_edit("person", "pers_buried_source", $pers_gedcomnumber);
}

// *** Edit source by text ***
if ($connect_sub_kind == 'pers_text_source') {
    show_source_header(__('text') . ' - ' . __('source'));
    source_edit("person", "pers_text_source", $pers_gedcomnumber);
}

// *** Edit source by person ***
if ($connect_sub_kind == 'person_source') {
    show_source_header(__('Source') . ' - ' . __('person'));
    source_edit("person", "person_source", $pers_gedcomnumber);
}

// *** Edit source by person-address connection by person ***
if ($connect_sub_kind == 'pers_address_connect_source') {
    show_source_header(__('Source') . ' - ' . __('Address'));
    source_edit("person", "pers_address_connect_source", $connect_connect_id);
}

// *** Edit source by living together ***
if ($connect_sub_kind == 'fam_relation_source') {
    show_source_header(__('Source') . ' - ' . __('Living together'));
    source_edit("family", "fam_relation_source", $marriage);
}

// *** Edit source by fam_marr_notice ***
if ($connect_sub_kind == 'fam_marr_notice_source') {
    show_source_header(__('Source') . ' - ' . __('Notice of Marriage'));
    source_edit("family", "fam_marr_notice_source", $marriage);
}

// *** Edit source by fam_marr ***
if ($connect_sub_kind == 'fam_marr_source') {
    show_source_header(__('Source') . ' - ' . __('Marriage'));
    source_edit("family", "fam_marr_source", $marriage);
}

// *** Edit source by fam_church_notice ***
if ($connect_sub_kind == 'fam_marr_church_notice_source') {
    show_source_header(__('Source') . ' - ' . __('Religious Notice of Marriage'));
    source_edit("family", "fam_marr_church_notice_source", $marriage);
}

// *** Edit source by fam_marr_church ***
if ($connect_sub_kind == 'fam_marr_church_source') {
    show_source_header(__('Source') . ' - ' . __('Religious Marriage'));
    source_edit("family", "fam_marr_church_source", $marriage);
}

// *** Edit source by fam_div ***
if ($connect_sub_kind == 'fam_div_source') {
    show_source_header(__('Source') . ' - ' . __('Divorce'));
    source_edit("family", "fam_div_source", $marriage);
}

// *** Edit source by fam_text ***
if ($connect_sub_kind == 'fam_text_source') {
    show_source_header(__('Source') . ' - ' . __('text'));
    source_edit("family", "fam_text_source", $marriage);
}

// *** Edit source by relation ***
if ($connect_sub_kind == 'family_source') {
    show_source_header(__('Source') . ' - ' . __('relation'));
    source_edit("family", "family_source", $marriage);
}

// *** Edit source by family-address connection by family ***
if ($connect_sub_kind == 'fam_address_connect_source') {
    show_source_header(__('Source') . ' - ' . __('Address'));
    source_edit("family", "fam_address_connect_source", $connect_connect_id);
}

// *** Edit source by address (in address editor) AND ADD ADDRES-SOURCE IN PERSON/ FAMILY SCREEN ***
if ($connect_sub_kind == 'address_source') {
    show_source_header(__('Source') . ' - ' . __('Address'));
    //source_edit("address","address_source",$address_gedcomnr);
    source_edit("address", "address_source", $connect_connect_id);
    echo '<p>';
}
/*
OLD CODE
// *** Edit source by address (in person/ family editor in iframe) ***
if ($connect_sub_kind=='address_source2'){
    show_source_header(__('Source').' - '.__('Address'));
    //source_edit("address","address_source",$address_gedcomnr);
    source_edit("address","address_source",$connect_connect_id);
    echo '<p>';
}
*/

// *** Edit source by person event ***
//if ($connect_sub_kind=='person_event_source' OR ($connect_kind=='person' AND $connect_sub_kind=='event_source')){
//if ($connect_sub_kind=='person_event_source'){
if ($connect_sub_kind == 'pers_event_source') {
    show_source_header(__('source') . ' - ' . __('Event'));
    source_edit("person", "pers_event_source", $connect_connect_id);
    echo '<p>';
}

// *** Edit source by family event ***
//if ($connect_sub_kind=='fam_event_source' OR ($connect_kind=='family' AND $connect_sub_kind=='event_source')){
if ($connect_sub_kind == 'fam_event_source') {
    show_source_header(__('source') . ' - ' . __('Event'));
    source_edit("family", "fam_event_source", $connect_connect_id);
    echo '<p>';
}

// *** SOURCE EDIT FUNCTION ***
function source_edit($connect_kind, $connect_sub_kind, $connect_connect_id)
{
    global $dbh, $tree_id, $language, $page, $phpself2, $marriage;
    global $editor_cls, $field_date;

    global $db_functions;
    $db_functions->set_tree_id($tree_id);

    // *** Script to expand and collapse source items ***
    echo '
    <script>
    function hideShow(el_id){
        // *** Hide or show item ***
        var arr = document.getElementsByClassName(\'row\'+el_id);
        for (i=0; i<arr.length; i++){
            if(arr[i].style.display!="none"){
                arr[i].style.display="none";
            }else{
                arr[i].style.display="";
            }
        }
    }
    </script>
    ';
?>

    <form method="POST" action="<?= $phpself2; ?>">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="submit" name="submit" title="submit" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">

        <?php
        if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
            echo '<input type="hidden" name="event_person" value="1">';
        }
        if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
            echo '<input type="hidden" name="event_family" value="1">';
        }

        // *** Search for all connected sources ***
        $connect_sql = $db_functions->get_connections_connect_id($connect_kind, $connect_sub_kind, $connect_connect_id);
        $nr_sources = count($connect_sql);
        ?>

        <ul id="sortable<?= $connect_kind . $connect_sub_kind . $connect_connect_id; ?>" class="sortable" style="padding-left:0px;">

            <?php foreach ($connect_sql as $connectDb) { ?>
                <li><span style="cursor:move;" id="<?= $connectDb->connect_id; ?>" class="handle<?= $connect_kind . $connect_sub_kind . $connect_connect_id; ?>"><img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>"></span>

                    <?php
                    echo '<input type="hidden" name="connect_change[' . $connectDb->connect_id . ']" value="' . $connectDb->connect_id . '">';
                    echo '<input type="hidden" name="connect_connect_id[' . $connectDb->connect_id . ']" value="' . $connectDb->connect_connect_id . '">';
                    if (isset($marriage)) {
                        echo '<input type="hidden" name="marriage_nr[' . $connectDb->connect_id . ']" value="' . $marriage . '">';
                    }
                    echo '<input type="hidden" name="connect_kind[' . $connectDb->connect_id . ']" value="' . $connect_kind . '">';
                    echo '<input type="hidden" name="connect_sub_kind[' . $connectDb->connect_id . ']" value="' . $connect_sub_kind . '">';
                    echo '<input type="hidden" name="connect_item_id[' . $connectDb->connect_id . ']" value="">';
                    ?>

                    <!-- <b><?= __('Source'); ?></b> -->

                    <?php
                    echo ' <a href="index.php?page=' . $page . '&amp;connect_drop=' . $connectDb->connect_id;
                    // *** Needed for events **
                    echo '&amp;connect_kind=' . $connect_kind;
                    echo '&amp;connect_sub_kind=' . $connect_sub_kind;
                    echo '&amp;connect_connect_id=' . $connect_connect_id;
                    if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
                        echo '&amp;event_person=1';
                    }
                    if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
                        echo '&amp;event_family=1';
                    }
                    if (isset($marriage)) {
                        echo '&amp;marriage_nr=' . $marriage;
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
                                <input type="hidden" name="connect_source_id[<?= $connectDb->connect_id; ?>]" value="<?= $connectDb->connect_source_id; ?>">
                                <input type="hidden" name="source_id[<?= $connectDb->connect_id; ?>]" value="<?= $sourceDb->source_id; ?>">

                                <div class="row mb-2">
                                    <label for "source_title" class="col-sm-3 col-form-label"><?= __('Title'); ?></label>
                                    <div class="col-md-7">
                                        <input type="text" name="source_title[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($sourceDb->source_title); ?>" size="60" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <label for "source_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
                                    <div class="col-md-7">
                                        <?php $editor_cls->date_show($sourceDb->source_date, 'source_date', "[$connectDb->connect_id]"); ?>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <label for "source_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
                                    <div class="col-md-7">
                                        <input type="text" name="source_place[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($sourceDb->source_place); ?>" size="15" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <label for "source_own_code" class="col-sm-3 col-form-label"><?= __('Own code'); ?></label>
                                    <div class="col-md-7">
                                        <input type="text" name="source_refn[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($sourceDb->source_refn); ?>" size="15" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <label for "source_text" class="col-sm-3 col-form-label"><?= __('Text'); ?></label>
                                    <div class="col-md-7">
                                        <textarea rows="2" name="source_text[<?= $connectDb->connect_id; ?>]" <?= $field_text; ?> class=" form-control form-control-sm"><?= $editor_cls->text_show($sourceDb->source_text); ?></textarea>
                                    </div>
                                </div>

                                <!-- TODO Picture by source -->
                            </div>

                            <!-- Source connection items -->
                            <div class="row mb-2">
                                <label for "source_role" class="col-sm-3 col-form-label"><?= __('Sourcerole'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="connect_role[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($connect_role); ?>" size="6" class="form-control form-control-sm">
                                    <span style="font-size:13px;"><?= __('e.g. Writer, Brother, Sister, Father.'); ?></span>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for "source_page" class="col-sm-3 col-form-label"><?= __('Page'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="connect_page[<?= $connectDb->connect_id; ?>]" value="<?= $connectDb->connect_page; ?>" size="6" class="form-control form-control-sm">
                                    <span style="font-size:13px;"><?= __('Page in source.'); ?></span>
                                </div>
                            </div>

                            <!-- Quality -->
                            <div class="row mb-2">
                                <label for "source_quality" class="col-sm-3 col-form-label"><?= __('Quality'); ?></label>
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
                                <label for "connect_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
                                <div class="col-md-7">
                                    <?php $editor_cls->date_show($connectDb->connect_date, 'connect_date', "[$connectDb->connect_id]"); ?>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for "connect_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="connect_place[<?= $connectDb->connect_id; ?>]" value="<?= htmlspecialchars($connect_place); ?>" size="15" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- Extra text by shared source -->
                            <div class="row mb-2">
                                <label for "connect_text" class="col-sm-3 col-form-label"><?= __('Extra text'); ?></label>
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
                            $source_search_gedcomnr = safe_text_db($_POST['source_search_gedcomnr']);
                        }
                        $source_search = '';
                        if (isset($_POST['source_search'])) {
                            $source_search = safe_text_db($_POST['source_search']);
                        }

                        // *** Source: pull-down menu ***
                        // TODO only get necesary items
                        $qry = "SELECT * FROM humo_sources WHERE source_tree_id='" . safe_text_db($tree_id) . "'";
                        if (isset($_POST['source_search_gedcomnr'])) {
                            $qry .= " AND source_gedcomnr LIKE '%" . safe_text_db($_POST['source_search_gedcomnr']) . "%'";
                        }
                        if (isset($_POST['source_search'])) {
                            $qry .= " AND ( source_title LIKE '%" . safe_text_db($_POST['source_search']) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($source_search) . "%') )";
                        }
                        $qry .= " ORDER BY IF (source_title!='',source_title,source_text)";
                        //$qry.=" ORDER BY IF (source_title!='',source_title,source_text) LIMIT 0,500";
                        $source_qry = $dbh->query($qry);
                    ?>

                        <h3><?= __('Search existing source'); ?></h3>
                        <input type="text" name="source_search_gedcomnr" value="<?= $source_search_gedcomnr; ?>" size="20" placeholder="<?= __('gedcomnumber (ID)'); ?>">
                        <input type="text" name="source_search" value="<?= $source_search; ?>" size="20" placeholder="<?= __('text'); ?>">
                        <input type="submit" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary"><br>
                        <select size="1" name="connect_source_id[<?= $connectDb->connect_id; ?>]" style="width: 300px">
                            <option value=""><?= __('Select existing source'); ?>:</option>
                            <?php
                            while ($sourceDb = $source_qry->fetch(PDO::FETCH_OBJ)) {
                                // TODO $selected not useful here?
                                $selected = '';
                                if ($connectDb->connect_source_id != '' && $sourceDb->source_gedcomnr == $connectDb->connect_source_id) {
                                    $selected = ' selected';
                                }
                                echo '<option value="' . @$sourceDb->source_gedcomnr . '"' . $selected . '>';
                                if ($sourceDb->source_title) {
                                    echo $sourceDb->source_title;
                                } else {
                                    echo substr($sourceDb->source_text, 0, 40);
                                    if (strlen($sourceDb->source_text) > 40) {
                                        echo '...';
                                    }
                                }
                                echo ' [' . @$sourceDb->source_gedcomnr . ']</option>' . "\n";
                            }
                            ?>
                            <option value="">*** <?= __('Results are limited, use search to find more sources.'); ?> ***</option>
                        </select>

                        &nbsp;&nbsp;<input type="submit" name="submit" title="submit" value="<?= __('Select'); ?>" class="btn btn-sm btn-secondary">

                        <!-- Add new source -->
                        <br><br><?= __('Or:'); ?>
                        <a href="index.php?page=<?= $page; ?>&amp;source_add2=1&amp;connect_id=<?= $connectDb->connect_id; ?>
                            &amp;connect_order=<?= $connectDb->connect_order; ?>&amp;connect_kind=<?= $connectDb->connect_kind; ?>
                            &amp;connect_sub_kind=<?= $connectDb->connect_sub_kind; ?>&amp;connect_connect_id=<?= $connectDb->connect_connect_id; ?>
                    <?php
                        if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
                            echo '&amp;event_person=1';
                        }
                        if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
                            echo '&amp;event_family=1';
                        }
                    ?>
                        #addresses"><?= __('add new source'); ?></a>

                    <?php
                        echo '<input type="hidden" name="connect_role[' . $connectDb->connect_id . ']" value="">';
                        echo '<input type="hidden" name="connect_page[' . $connectDb->connect_id . ']" value="">';
                        echo '<input type="hidden" name="connect_quality[' . $connectDb->connect_id . ']" value="">';
                        echo '<input type="hidden" name="connect_text[' . $connectDb->connect_id . ']" value="">';
                    }
                    ?>
                </li>
            <?php } ?>
        </ul>
    </form>

    <!-- Script for ordering sources -->
    <?php if (count($connect_sql) > 0) { ?>
        <script>
            $('#sortable<?= $connect_kind . $connect_sub_kind . $connect_connect_id; ?>').sortable({
                handle: '.handle<?= $connect_kind . $connect_sub_kind . $connect_connect_id; ?>'
            }).bind('sortupdate', function() {
                var childstring = "";
                var chld_arr = document.getElementsByClassName(" handle<?= $connect_kind . $connect_sub_kind . $connect_connect_id; ?>");
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

    // *** Add new source connection ***
    if (!isset($_POST['connect_add'])) {
    ?>
        <h3><?= __('Add'); ?></h3>
        <form method="POST" action="<?= $phpself2; ?>">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <?php
            if (isset($_POST['event_person']) || isset($_GET['event_person'])) {
                echo '<input type="hidden" name="event_person" value="1">';
            }
            if (isset($_POST['event_family']) || isset($_GET['event_family'])) {
                echo '<input type="hidden" name="event_family" value="1">';
            }
            echo '<input type="hidden" name="connect_kind" value="' . $connect_kind . '">';
            echo '<input type="hidden" name="connect_sub_kind" value="' . $connect_sub_kind . '">';
            echo '<input type="hidden" name="connect_connect_id" value="' . $connect_connect_id . '">';
            if (isset($marriage)) {
                echo '<input type="hidden" name="marriage_nr" value="' . $marriage . '">';
            }

            if ($nr_sources > 0) {
                echo ' <input type="submit" name="connect_add" value="' . __('Add another source') . '" class="btn btn-sm btn-secondary">';
            } else {
                echo ' <input type="submit" name="connect_add" value="' . __('Add source') . '" class="btn btn-sm btn-secondary">';
            }
            ?>
        </form>
<?php
    }
}
