<script src="googlemaps/namesearch.js"></script>

<?php
$link = $link_cls->get_link($uri_path, 'maps', $tree_id);
$link2 = $link_cls->get_link($uri_path, 'maps', $tree_id, true);

// *** Select family tree ***
$tree_id_string = " AND ( ";
$id_arr = explode(";", substr($humo_option['geo_trees'], 0, -1)); // substr to remove trailing ";"
foreach ($id_arr as $value) {
    $tree_id_string .= "tree_id='" . substr($value, 1) . "' OR ";  // substr removes leading "@" in geo_trees setting string
}
$tree_id_string = substr($tree_id_string, 0, -4) . ")"; // take off last " ON " and add ")"
$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' " . $tree_id_string . " ORDER BY tree_order";
$tree_search_result = $dbh->query($tree_search_sql);
$count = 0;
?>

<div class="p-3 m-2 genealogy_search">
    <div class="row mb-2">
        <div class="col-auto">
            <?= __('Display birth or death locations across different time periods'); ?>
        </div>

        <div class="col-auto">
            <!-- Help popup. Remark: Bootstrap popover javascript in layout script. -->
            <?php $popup_text = __('Click markers to show information about the location and see a list off all persons connected to the location.'); ?>
            <?php $popup_text = str_replace('"', "'", $popup_text); ?>

            <button type="button" class="btn btn-sm btn-secondary" data-bs-html="true" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="<?= $popup_text; ?>">
                <?= __('Help'); ?>
            </button>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-auto">
            <form method="POST" action="<?= $link; ?>" style="display : inline;">
                <select size="1" name="tree_id" onChange="this.form.submit();" class="form-select form-select-sm">
                    <option value=""><?= __('Select a family tree:'); ?></option>
                    <?php
                    while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                        // *** Check if family tree is shown or hidden for user group ***
                        $hide_tree_array = explode(";", $user['group_hide_trees']);
                        if (!in_array($tree_searchDb->tree_id, $hide_tree_array)) {
                            $selected = '';
                            if (isset($_SESSION['tree_id'])) {
                                if ($tree_searchDb->tree_id == $_SESSION['tree_id']) {
                                    $selected = 'selected';
                                    $tree_id = $tree_searchDb->tree_id;
                                    $db_functions->set_tree_id($tree_id);
                                    $_SESSION['tree_prefix'] = $tree_searchDb->tree_prefix;
                                }
                            } elseif ($count == 0) {
                                $selected = 'selected';
                                $tree_id = $tree_searchDb->tree_id;
                                $_SESSION['tree_id'] = $tree_id;
                                $db_functions->set_tree_id($tree_id);
                                $_SESSION['tree_prefix'] = $tree_searchDb->tree_prefix;
                            }
                            $treetext = show_tree_text($tree_searchDb->tree_id, $selected_language);
                            $count++;
                    ?>
                            <option value="<?= $tree_searchDb->tree_id; ?>" <?= $selected; ?>><?= $treetext['name']; ?></option>
                    <?php
                        }
                    }
                    ?>
                </select>
            </form>
        </div>

        <div class="col-auto">
            <?= __('Display:'); ?>
        </div>
        <div class="col-auto">
            <form name="type_form" method="POST" action="" style="display : inline;">
                <select style="max-width:200px" size="1" onChange="document.type_form.submit()" id="map_type" name="map_type" class="form-select form-select-sm">
                    <option value="type_birth" <?= $maps['display_birth'] ? 'selected' : ''; ?>><?= __('Birth locations'); ?></option>
                    <option value="type_death" <?= $maps['display_death'] ? 'selected' : ''; ?>><?= __('Death locations'); ?></option>
                </select>
            </form>
        </div>

        <?php if ($maps['select_world_map'] == 'OpenStreetMap') { ?>
            <div class="col-auto">
                <form method="POST" action="" style="display : inline;">
                    <select onChange="findPlace()" size="1" id="loc_search" name="loc_search" class="form-select form-select-sm">
                        <option value="toptext"><?= __('Find location on the map'); ?></option>
                        <?php
                        //sort ($maps['location']);
                        for ($i = 1; $i < count($maps['location']); $i++) {
                            //echo 'L.marker([' . $maps['latitude'][$i] . ', ' . $maps['longitude'][$i] . ']) .bindPopup(\'' . $maps['location_text'][$i] . '\')';
                        ?>
                            <option value="<?= $maps['latitude'][$i]; ?>,<?= $maps['longitude'][$i]; ?>">
                                <?= $maps['location'][$i]; ?> #<?= $maps['location_text_count'][$i]; ?>
                            </option>
                        <?php } ?>

                    </select>
                </form>
            </div>
        <?php } ?>

        <?php if ($maps['select_world_map'] == 'Google') { ?>
            <div class="col-auto">
                <!-- Slider text & year box -->
                <!-- TODO try to build a flexible slider, without special pre fixes -->
                <div style="<?= $language['dir'] != "rtl" ? 'float:left' : 'float:right'; ?>">
                    <?php
                    echo '
                    <script>
                    var minval = ' . $maps['slider_off'] . ';
                    $(function() {
                        // Set default slider setting
                        ' . $maps['slider_makesel'] . '
                        $( "#slider" ).slider({
                            value: ' . $maps['slider_default_year'] . ',
                            min: ' . $maps['slider_off'] . ',
                            max: ' . $maps['slider_year'] . ',
                            step: ' . $maps['slider_step'] . ',
                            slide: function( event, ui ) {
                                if(ui.value == minval) { $( "#amount" ).val("----->"); }
                                else if(ui.value > 2000) { $( "#amount" ).val(' . $maps['slider_year'] . '); }
                                else {	$( "#amount" ).val(ui.value ); }
                            }
                        });
                        $( "#amount" ).val("' . $maps['slider_default_display'] . '");

                        // Only change map if value is changed.
                        startPos = $("#slider").slider("value");
                        $("#slider").on("slidestop", function(event, ui) {
                            endPos = ui.value;
                            if (startPos != endPos) {
                                // Change map. This script can be found in: google_initiate.php.
                                makeSelection(endPos);
                            }
                            startPos = endPos;
                        });
                    });
                    </script>'; ?>
                </div>

                <!-- Slider text -->
                <div style="<?= $language['dir'] != "rtl" ? 'float:left' : 'float:right'; ?>">
                    <?php if ($maps['display_birth']) { ?>
                        <?= __('Display births until: '); ?>
                    <?php } elseif ($maps['display_death']) { ?>
                        <?= __('Display deaths until: '); ?>
                    <?php } ?>

                    &nbsp;<input type="text" id="amount" disabled="disabled" size="4" style="border:0;color:#0000CC;font-weight:normal;font-size:115%;">
                    &nbsp;&nbsp;
                </div>

                <!-- Slider -->
                <?php if ($language['dir'] != "rtl") { ?>
                    <div id="slider" style="float:left;width:170px;margin-top:7px;margin-right:15px;"></div>
                <?php } else { ?>
                    <div id="slider" style="float:right;direction:ltr;width:150px;margin-top:7px;margin-right:15px;"></div>
                <?php } ?>

            </div>
        <?php } ?>
    </div>

    <div class="row mb-2">
        <!-- Select specific family name(s) -->
        <div class="col-auto">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#familynameModal">
                <?= __('Filter by specific family name(s)'); ?>
            </button>

            <form method="POST" action="<?= $link; ?>">
                <?php
                $fam_search = "SELECT CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) as totalname
                    FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                    AND (pers_birth_place != '' OR (pers_birth_place='' AND pers_bapt_place != '')) AND pers_lastname != '' GROUP BY totalname ORDER BY totalname";
                $fam_search_result = $dbh->query($fam_search);
                ?>
                <div class="modal fade" id="familynameModal" tabindex="-1" aria-labelledby="familynameModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable"> <!-- <div class="modal-dialog modal-xl"> -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="familynameModalLabel"><?= __('Filter by specific family name(s)'); ?></h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <b><?= __('Mark checkbox next to name(s)'); ?></b><br>
                                <?php
                                while ($fam_searchDb = $fam_search_result->fetch(PDO::FETCH_OBJ)) {
                                    $pos = strpos($fam_searchDb->totalname, '_');
                                    $pref = substr($fam_searchDb->totalname, $pos + 1);
                                    if ($pref !== '') {
                                        $pref = ', ' . $pref;
                                    }
                                    $last = substr($fam_searchDb->totalname, 0, $pos);
                                ?>
                                    <input type="checkbox" name="items[]" value="<?= $fam_searchDb->totalname; ?>" class="form-check-input"> <?= $last . $pref; ?><br>
                                <?php } ?>
                            </div>
                            <div class="modal-footer">
                                <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Close'); ?></button> -->
                                <button type="submmit" name="submit" class="btn btn-primary"><?= __('Choose'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($maps['select_world_map'] == 'Google') { ?>

            <!-- TODO: use bootstrap. Don't show list of persons, but use search options -->
            <div class="col-auto">
                <form method="POST" style="display:inline" name="descform" action="<?= $link; ?>">
                    <input type="hidden" name="descmap" value="1">
                    <input type="submit" name="anything" value="<?= __('Filter by descendants'); ?>" class="btn btn-sm btn-secondary">
                </form>
            </div>


            <?php /*
            // Maybe just add a search box in the main form? Use 1 search box with option: descendants/ anscestors.
            <!-- Select descendants -->
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#descendantsModal">
                    <?= __('Filter by descendants'); ?>
                </button>

                <form method="POST" action="<?= $link; ?>">
                    <?php
                    $fam_search = "SELECT CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) as totalname
                        FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                        AND (pers_birth_place != '' OR (pers_birth_place='' AND pers_bapt_place != '')) AND pers_lastname != '' GROUP BY totalname ";
                    $fam_search_result = $dbh->query($fam_search);
                    ?>
                    <div class="modal fade" id="descendantsModal" tabindex="-1" aria-labelledby="descendantsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable"> <!-- <div class="modal-dialog modal-xl"> -->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="familynameModalLabel"><?= __('Filter by descendants'); ?></h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <form method="POST" action="" style="display : inline;">
                                        <div class="row mb-2">
                                            <div class="col-4">
                                                <input type="text" name="search_quicksearch_man" placeholder="<?= __('Name'); ?>" value="" size="15" class="form-control form-control-sm">
                                            </div>

                                            <div class="col-auto">
                                                <?= __('or ID:'); ?>
                                            </div>

                                            <div class="col-auto">
                                                <input type="text" name="search_man_id" value="" size="5" class="form-control form-control-sm">
                                            </div>

                                            <div class="col-auto">
                                                <input type="submit" name="submit" value="TEST" class="btn btn-sm btn-secondary">

                                                <input type="submit" name="submit" value="TEST 2" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#descendantsModal">
                                                
                                                <input type="submit" name="submit" value="TEST 3" class="btn btn-sm btn-secondary" data-dismiss="modal">
                                            </div>
                                        </div>
                                    </form>

                                </div>
                                <div class="modal-footer">
                                    <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Close'); ?></button> -->
                                    <button type="submmit" name="submit" class="btn btn-primary"><?= __('Choose'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            */
            ?>



            <!-- TODO: use bootstrap. Don't show list of persons, but use search options -->
            <div class="col-auto">
                <form method="POST" style="display:inline" name="ancform" action="<?= $link; ?>">
                    <input type="hidden" name="ancmap" value="1">
                    <input type="submit" name="anythingelse" value="<?= __('Filter by ancestors'); ?>" class="btn btn-sm btn-secondary">
                </form>
            </div>

            <div class="col-auto">
                <?php
                /*
                if($maps['display_birth']) {
                    echo ' <input style="font-size:14px" type="button" value="'.__('Mark all birth locations').'" onclick="makeSelection(3)"> ';
                }
                elseif($maps['display_death']) {
                    echo ' <input style="font-size:14px" type="button" value="'.__('Mark all death locations').'" onclick="makeSelection(3)"> ';
                }
                */

                // PULL-DOWN: FIND LOCATION
                // TODO: location_status could probably be totally removed. Combine google_initiate results with this query to generate a location list.
                /*
                if ($maps['display_birth']) {
                    $loc_search = "SELECT * FROM humo_location WHERE location_lat IS NOT NULL AND location_status LIKE '%" . $tree_prefix_quoted . "birth%' OR location_status LIKE '%" . $tree_prefix_quoted . "bapt%' OR location_status = '' ORDER BY location_location";
                }
                if ($maps['display_death']) {
                    $loc_search = "SELECT * FROM humo_location WHERE location_lat IS NOT NULL AND location_status LIKE '%" . $tree_prefix_quoted . "death%' OR location_status LIKE '%" . $tree_prefix_quoted . "buried%' OR location_status = '' ORDER BY location_location";
                }
                $loc_search_result = $dbh->query($loc_search);
                */
                //if ($loc_search_result !== false) {
                ?>
                <form method="POST" action="" style="display : inline;">
                    <select onChange="findPlace()" size="1" id="loc_search" name="loc_search" class="form-select form-select-sm">
                        <option value="toptext"><?= __('Find location on the map'); ?></option>
                        <?php
                        foreach ($maps['locarray'] as $key => $value) {
                            if ($maps['locarray'][$key][13] > 0) {
                        ?>
                                <option value="<?= $maps['locarray'][$key][14]; ?>,<?= $maps['locarray'][$key][1]; ?>,<?= $maps['locarray'][$key][2]; ?>">
                                    <?= $maps['locarray'][$key][0]; ?> #<?= $maps['locarray'][$key][13]; ?>
                                </option>

                        <?php
                            }
                        }
                        ?>
                    </select>
                </form>
            </div>
        <?php } ?>
    </div>


    <?php
    // *** Optional row ***
    if (isset($_POST['items']) || isset($_GET['persged']) && isset($_GET['persfams']) || isset($_GET['anc_persged']) && isset($_GET['anc_persfams'])) {
    ?>
        <div class="row mb-2 p-2 bg-info">
            <div class="col-auto">

                <!-- Searching by specific names -->
                <?php if ($maps['show_family_names']) { ?>
                    <div id="name_search">
                        <?= __('Mapping with specific name(s): '); ?>
                        <?= $maps['show_family_names']; ?>. <a href="<?= $link; ?>"><?= __('Switch name filter off'); ?></a>
                    </div>
                <?php } ?>

                <!-- Find descendants of chosen person -->
                <?php if (isset($_GET['persged']) && isset($_GET['persfams'])) { ?>
                    <div id="desc_search">
                        <?php if ($maps['desc_array'] != '') { ?>
                            <?= __('Filter by descendants of: ') . trim($maps['desc_chosen_name']); ?>. <a href="<?= $link; ?>"><?= __('Switch descendant filter off'); ?></a>
                        <?php } else { ?>
                            <?= __('No known birth places amongst descendants'); ?>. <a href="<?= $link; ?>"><?= __('Close'); ?></a>
                        <?php } ?>
                    </div>
                <?php } ?>

                <!-- Find ancestors -->
                <?php if (isset($_GET['anc_persged']) && isset($_GET['anc_persfams'])) { ?>
                    <div id="anc_search">
                        <?php if ($maps['anc_array'] != '') { ?>
                            <?= __('Filter by ancestors of: ') . trim($maps['chosen_name']); ?>. <a href="<?= $link; ?>"><?= __('Switch ancestor filter off'); ?></a>
                        <?php } else { ?>
                            <?= __('No known birth places amongst ancestors'); ?>. <a href="<?= $link; ?>"><?= __('Close'); ?></a>
                        <?php } ?>
                    </div>
                <?php } ?>

            </div>
        </div>
    <?php } ?>

</div>



<?php
// TODO: use bootstrap.
// FIXED WINDOW WITH LIST TO CHOOSE PERSON TO MAP WITH DESCENDANTS
if (isset($_POST['descmap'])) {
    //adjust pulldown for mobiles/tablets
    $select_size = 'size="20"';
    $select_height = '400px';
    if (isset($_SERVER["HTTP_USER_AGENT"]) or ($_SERVER["HTTP_USER_AGENT"] != "")) { //adjust pulldown for mobiles/tablets
        $visitor_user_agent = $_SERVER["HTTP_USER_AGENT"];
        if (
            strstr($visitor_user_agent, "Android") !== false || strstr($visitor_user_agent, "iOS") !== false || strstr($visitor_user_agent, "iPad") !== false || strstr($visitor_user_agent, "iPhone") !== false
        ) {
            $select_size = "";
            $select_height = '100px';
        }
    }

?>
    <div id="descmapping" style="display:block; z-index:100; position:absolute; top:150px; margin-left:140px; height:<?= $select_height; ?>; width:400px; border:1px solid #000; background:#d8d8d8; color:#000; margin-bottom:1.5em;z-index:20">
        <?php
        $orderlast = $user['group_kindindex'] == "j" ? "CONCAT(pers_prefix,pers_lastname)" : "pers_lastname";
        $desc_search = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_fams !='' ORDER BY " . $orderlast . ", pers_firstname";
        $desc_search_result = $dbh->query($desc_search);
        ?>
        &nbsp;&nbsp;<strong><?= __('Filter by descendants of a person'); ?></strong><br>
        &nbsp;&nbsp;<?= __('Pick a name or enter ID:'); ?><br>
        <form method="POST" action="" style="display : inline;">
            <select style="max-width:396px;background:#eee" <?= $select_size; ?> onChange="window.location=this.value;" id="desc_map" name="desc_map">
                <option value="toptext"><?= __('Pick a name from the pulldown list'); ?></option>
                <?php
                //prepared statement out of loop
                $chld_prep = $dbh->prepare("SELECT fam_children FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber =? AND fam_children != ''");
                $chld_prep->bindParam(1, $chld_var);
                while ($desc_searchDb = $desc_search_result->fetch(PDO::FETCH_OBJ)) {
                    $countmarr = 0;
                    $fam_arr = explode(";", $desc_searchDb->pers_fams);
                    foreach ($fam_arr as $value) {
                        //this person is already listed
                        if ($countmarr == 1) {
                            break;
                        }
                        $chld_var = $value;
                        $chld_prep->execute();
                        while ($chld_search_resultDb = $chld_prep->fetch(PDO::FETCH_OBJ)) {
                            $countmarr = 1;
                            $selected = '';
                            //if($desc_searchDb->pers_gedcomnumber == $chosenperson) { $selected = ' selected '; }
                            $man_cls = new PersonCls($desc_searchDb);
                            $privacy_man = $man_cls->get_privacy();
                            $date = '';
                            if (!$privacy_man) {
                                // if a person has privacy set (even if only for data, not for name,
                                // we won't put them on the list. Most likely it concerns recent people.
                                // Also, using the $man_cls->person_name functions takes too much time...
                                $b_date = $desc_searchDb->pers_birth_date;
                                $b_sign = __('born') . ' ';
                                if (!$desc_searchDb->pers_birth_date && $desc_searchDb->pers_bapt_date) {
                                    $b_date = $desc_searchDb->pers_bapt_date;
                                    $b_sign = __('baptised') . ' ';
                                }
                                $d_date = $desc_searchDb->pers_death_date;
                                $d_sign = __('died') . ' ';
                                if (!$desc_searchDb->pers_death_date && $desc_searchDb->pers_buried_date) {
                                    $d_date = $desc_searchDb->pers_buried_date;
                                    $d_sign = __('buried') . ' ';
                                }
                                $date = '';
                                if ($b_date && !$d_date) {
                                    $date = ' (' . $b_sign . date_place($b_date, '') . ')';
                                }
                                if ($b_date && $d_date) {
                                    $date .= ' (' . $b_sign . date_place($b_date, '') . ' - ' . $d_sign . date_place($d_date, '') . ')';
                                }
                                if (!$b_date && $d_date) {
                                    $date = '(' . $d_sign . date_place($d_date, '') . ')';
                                }
                                $name = '';
                                $pref = '';
                                $last = '- , ';
                                $first = '-';
                                if ($desc_searchDb->pers_lastname) {
                                    $last = $desc_searchDb->pers_lastname . ', ';
                                }
                                if ($desc_searchDb->pers_firstname) {
                                    $first = $desc_searchDb->pers_firstname;
                                }
                                if ($desc_searchDb->pers_prefix) {
                                    $pref = strtolower(str_replace('_', '', $desc_searchDb->pers_prefix));
                                }

                                if ($user['group_kindindex'] == "j") {
                                    if ($desc_searchDb->pers_prefix) {
                                        $pref = strtolower(str_replace('_', '', $desc_searchDb->pers_prefix)) . ' ';
                                    }
                                    $name = $pref . $last . $first;
                                } else {
                                    if ($desc_searchDb->pers_prefix) {
                                        $pref = ' ' . strtolower(str_replace('_', '', $desc_searchDb->pers_prefix));
                                    }
                                    $name = $last . $first . $pref;
                                }
                ?>
                                <option value="<?= $link2; ?>persged=<?= $desc_searchDb->pers_gedcomnumber; ?>&persfams=<?= $desc_searchDb->pers_fams; ?>" <?= $selected; ?>>
                                    <?= $name . $date; ?> [<?= $desc_searchDb->pers_gedcomnumber; ?>]
                                </option>
                <?php
                            }
                        }
                    }
                }
                ?>
            </select>
        </form>
        <script>
            function findGednr(pers_id) {
                for (var i = 1; i < desc_map.length - 1; i++) {
                    if (desc_map.options[i].text.indexOf("[#" + pers_id + "]") != -1 || desc_map.options[i].text.indexOf("[#I" + pers_id + "]") != -1) {
                        window.location = desc_map.options[i].value;
                    }
                }
            }
        </script>
        <br>
        <div style="margin-top:5px;text-align:left">
            <?php
            echo '&nbsp;&nbsp;Find by ID (I324):<input id="id_field" type="text" style="font-size:120%;width:60px;" value=""><input type="button" value="' . __('Go!') . '" onclick="findGednr(getElementById(\'id_field\').value);">';
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?= $link; ?>"><?= __('Cancel'); ?></a>
        </div>
    </div>
<?php
}

// TODO: use bootstrap.
// FIXED WINDOW WITH LIST TO CHOOSE PERSON TO MAP WITH ANCESTORS
if (isset($_POST['ancmap'])) {
    //adjust pulldown for mobiles/tablets
    $select_size = 'size="20"';
    $select_height = '400px';
    if (isset($_SERVER["HTTP_USER_AGENT"]) || $_SERVER["HTTP_USER_AGENT"] != "") { //adjust pulldown for mobiles/tablets
        $visitor_user_agent = $_SERVER["HTTP_USER_AGENT"];
        if (
            strstr($visitor_user_agent, "Android") !== false || strstr($visitor_user_agent, "iOS") !== false || strstr($visitor_user_agent, "iPad") !== false || strstr($visitor_user_agent, "iPhone") !== false
        ) {
            $select_size = "";
            $select_height = '100px';
        }
    }

?>
    <div id="ancmapping" style="display:block; z-index:100; position:absolute; top:150px; margin-left:140px; height:<?= $select_height; ?>; width:400px; border:1px solid #000; background:#d8d8d8; color:#000; margin-bottom:1.5em;z-index:20">
        <?php
        $orderlast = $user['group_kindindex'] == "j" ? "CONCAT(pers_prefix,pers_lastname)" : "pers_lastname";
        $anc_search = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_fams !='' ORDER BY " . $orderlast . ", pers_firstname";
        $anc_search_result = $dbh->query($anc_search);
        ?>
        &nbsp;&nbsp;<strong><?= __('Filter by ancestors of a person'); ?></strong><br>
        &nbsp;&nbsp;<?= __('Pick a name or enter ID:'); ?><br>
        <form method="POST" action="" style="display : inline;">
            <select style="max-width:396px;background:#eee" <?= $select_size; ?> onChange="window.location=this.value;" id="anc_map" name="anc_map">
                <option value="toptext"><?= __('Pick a name from the pulldown list'); ?></option>
                <?php
                //prepared statement out of loop
                $chld_prep = $dbh->prepare("SELECT fam_children FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber =? AND fam_children != ''");
                $chld_prep->bindParam(1, $chld_var);
                while ($anc_searchDb = $anc_search_result->fetch(PDO::FETCH_OBJ)) {
                    $countmarr = 0;
                    $fam_arr = explode(";", $anc_searchDb->pers_fams);
                    foreach ($fam_arr as $value) {
                        if ($countmarr == 1) {
                            break;
                        } //this person is already listed
                        $chld_var = $value;
                        $chld_prep->execute();
                        while ($chld_search_resultDb = $chld_prep->fetch(PDO::FETCH_OBJ)) {
                            $countmarr = 1;
                            $selected = '';
                            //if($anc_searchDb->pers_gedcomnumber == $chosenperson) { $selected = ' selected '; }
                            $man_cls = new PersonCls($anc_searchDb);
                            $privacy_man = $man_cls->get_privacy();
                            $date = '';
                            if (!$privacy_man) { // don't show dates if privacy is set for this person
                                // if a person has privacy set (even if only for data, not for name,
                                // we won't put them on the list. Most likely it concerns recent people.
                                // Also, using the $man_cls->person_name functions takes too much time...
                                $b_date = $anc_searchDb->pers_birth_date;
                                $b_sign = __('born') . ' ';
                                if (!$anc_searchDb->pers_birth_date && $anc_searchDb->pers_bapt_date) {
                                    $b_date = $anc_searchDb->pers_bapt_date;
                                    $b_sign = __('baptised') . ' ';
                                }
                                $d_date = $anc_searchDb->pers_death_date;
                                $d_sign = __('died') . ' ';
                                if (!$anc_searchDb->pers_death_date && $anc_searchDb->pers_buried_date) {
                                    $d_date = $anc_searchDb->pers_buried_date;
                                    $d_sign = __('buried') . ' ';
                                }
                                $date = '';
                                if ($b_date && !$d_date) {
                                    $date = ' (' . $b_sign . date_place($b_date, '') . ')';
                                }
                                if ($b_date && $d_date) {
                                    $date .= ' (' . $b_sign . date_place($b_date, '') . ' - ' . $d_sign . date_place($d_date, '') . ')';
                                }
                                if (!$b_date && $d_date) {
                                    $date = '(' . $d_sign . date_place($d_date, '') . ')';
                                }
                            }
                            if (!$privacy_man || ($privacy_man && $user['group_filter_name'] == "j")) {
                                // don't show the person at all on the list if names are hidden when privacy is set for person
                                $name = '';
                                $pref = '';
                                $last = '- , ';
                                $first = '-';
                                if ($anc_searchDb->pers_lastname) {
                                    $last = $anc_searchDb->pers_lastname . ', ';
                                }
                                if ($anc_searchDb->pers_firstname) {
                                    $first = $anc_searchDb->pers_firstname;
                                }
                                if ($anc_searchDb->pers_prefix) {
                                    $pref = strtolower(str_replace('_', '', $anc_searchDb->pers_prefix));
                                }

                                if ($user['group_kindindex'] == "j") {
                                    if ($anc_searchDb->pers_prefix) {
                                        $pref = strtolower(str_replace('_', '', $anc_searchDb->pers_prefix)) . ' ';
                                    }
                                    $name = $pref . $last . $first;
                                } else {
                                    if ($anc_searchDb->pers_prefix) {
                                        $pref = ' ' . strtolower(str_replace('_', '', $anc_searchDb->pers_prefix));
                                    }
                                    $name = $last . $first . $pref;
                                }
                ?>
                                <option value="<?= $link2; ?>anc_persged=<?= $anc_searchDb->pers_gedcomnumber; ?>&anc_persfams=<?= $anc_searchDb->pers_fams; ?>" <?= $selected; ?>>
                                    <?= $name . $date; ?> [<?= $anc_searchDb->pers_gedcomnumber; ?>]
                                </option>
                <?php
                            }
                        }
                    }
                }
                ?>
            </select>
        </form>
        <script>
            function findGednr(pers_id) {
                for (var i = 1; i < anc_map.length - 1; i++) {
                    if (anc_map.options[i].text.indexOf("[#" + pers_id + "]") != -1 || anc_map.options[i].text.indexOf("[#I" + pers_id + "]") != -1) {
                        window.location = anc_map.options[i].value;
                    }
                }
            }
        </script>
        <br>
        <div style="margin-top:5px;text-align:left">
            <?php
            echo '&nbsp;&nbsp;Find by ID (I324):<input id="id_field" type="text" style="font-size:120%;width:60px;" value=""><input type="button" value="' . __('Go!') . '" onclick="findGednr(getElementById(\'id_field\').value);">';
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?= $link; ?>"><?= __('Cancel'); ?></a>
        </div>
    </div>
<?php
}


// *** OpenStreetMap ***
if ($maps['select_world_map'] == 'OpenStreetMap') {
?>
    <link rel="stylesheet" href="assets/leaflet/leaflet.css">
    <script src="assets/leaflet/leaflet.js"></script>

    <!-- Show OpenStreetMap -->
    <div id="map" style="height:520px"></div>

    <!-- Zoom in to map location -->
    <script>
        function findPlace() {
            var e = document.getElementById("loc_search");
            var locSearch = e.options[e.selectedIndex].value;
            if (locSearch != "toptext") { // if not default text "find location on map"
                var opt_array = new Array();
                opt_array = locSearch.split(",", 2);

                //map.setView([lat, lng], zoomLevel);
                // WERKT: map.setView([48.85, 2.35], 10);
                map.setView([opt_array[0], opt_array[1]], 10);
            }
        }
    </script>

    <?php
    // *** Map using fitbound (all markers visible) ***
    echo '<script>
        var map = L.map("map").setView([48.85, 2.35], 10);
        var markers = [';

    // *** Add all markers from array ***
    for ($i = 1; $i < count($maps['location']); $i++) {
        if ($i > 1) echo ',';
        echo 'L.marker([' . $maps['latitude'][$i] . ', ' . $maps['longitude'][$i] . ']) .bindPopup(\'' . $maps['location_text'][$i] . '\')';
    }

    echo '];
        var group = L.featureGroup(markers).addTo(map);
        setTimeout(function () {
          map.fitBounds(group.getBounds());
        }, 1000);
        L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
          attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
        }).addTo(map);
    </script>';
} else {
    ?>

    <!-- Google Maps -->
    <div id="map_canvas" style="height:520px"></div>

    <!-- Zoom in to map location -->
    <?php
    echo '
    <script>
    function findPlace () {
        // infoWindow.close();
        var e = document.getElementById("loc_search");
        var locSearch = e.options[e.selectedIndex].value;
        if(locSearch != "toptext") {   // if not default text "find location on map"
            var opt_array = new Array();
            opt_array = locSearch.split(",",3);
            map.setZoom(11);
            var ltln = new google.maps.LatLng(opt_array[1],opt_array[2]);
            map.setCenter(ltln);
        }
    }
    </script>';

    $api_key = '';
    if (isset($humo_option['google_api_key']) && $humo_option['google_api_key'] != '') {
        //$api_key = '?key=' . $humo_option['google_api_key'] . '&callback=Function.prototype';
        //$api_key = '?key=' . $humo_option['google_api_key'] . '&loading=async&callback=initMap';

        // July 2024: for advanced markers use:
        $api_key = '?key=' . $humo_option['google_api_key'] . '&callback=initMap&v=weekly&libraries=marker';
    }
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        echo '<script src="https://maps.googleapis.com/maps/api/js' . $api_key . '"></script>';
    } else {
        echo '<script src="http://maps.googleapis.com/maps/api/js' . $api_key . '"></script>';
    }
    //$maptype = "ROADMAP";
    //if (isset($humo_option['google_map_type'])) {
    //    $maptype = $humo_option['google_map_type'];
    //}
    // Removed from initialize:
    //mapTypeId: google.maps.MapTypeId.<?= $maptype;
    ?>

    <script>
        var map;

        function initialize() {
            var latlng = new google.maps.LatLng(22, -350);
            var myOptions = {
                zoom: 2,
                center: latlng,
                mapId: "MAP_07_2024", // Map ID is required for advanced markers.
            };
            map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
        }
    </script>

    <script>
        initialize();
    </script>

    <script src="googlemaps/StyledMarker.js"></script>

    <script>
        var markersArray = [];
        var markerContent = "This is a test window";

        // Show marker text in infowindow.
        infoWindow = new google.maps.InfoWindow({
            maxWidth: 500
        });

        google.maps.event.addListener(map, 'click', function() {
            infoWindow.close();
        });

        function handleMarkerClick(marker, index) {
            return function() {
                infoWindow.setContent(index);
                infoWindow.setZIndex(1000000);
                infoWindow.open(map, marker);
            };
        }

        function clearOverlays() {
            if (markersArray) {
                for (i in markersArray) {
                    markersArray[i].setMap(null);
                }
                markersArray = new Array();
            }
        }

        <?php
        $nr = 0;
        // 1 letter array name to keep download as short as possible.
        echo 'var j = new Array();';
        foreach ($maps['locarray'] as $key => $value) {
            echo 'j[' . $nr . '] = new Array();';
            echo 'j[' . $nr . '][0] = "' . $maps['locarray'][$key][0] . '";';
            echo 'j[' . $nr . '][1] = "' . $maps['locarray'][$key][1] . '";';
            echo 'j[' . $nr . '][2] = "' . $maps['locarray'][$key][2] . '";';
            echo 'j[' . $nr . '][' . $maps['slider_min'] . '] = "' . $maps['locarray'][$key][3] . '";';
            echo 'j[' . $nr . '][' . ($maps['slider_min'] + $maps['slider_step']) . '] = "' . $maps['locarray'][$key][4] . '";';
            echo 'j[' . $nr . '][' . ($maps['slider_min'] + (2 * $maps['slider_step'])) . '] = "' . $maps['locarray'][$key][5] . '";';
            echo 'j[' . $nr . '][' . ($maps['slider_min'] + (3 * $maps['slider_step'])) . '] = "' . $maps['locarray'][$key][6] . '";';
            echo 'j[' . $nr . '][' . ($maps['slider_min'] + (4 * $maps['slider_step'])) . '] = "' . $maps['locarray'][$key][7] . '";';
            echo 'j[' . $nr . '][' . ($maps['slider_min'] + (5 * $maps['slider_step'])) . '] = "' . $maps['locarray'][$key][8] . '";';
            echo 'j[' . $nr . '][' . ($maps['slider_min'] + (6 * $maps['slider_step'])) . '] = "' . $maps['locarray'][$key][9] . '";';
            echo 'j[' . $nr . '][' . ($maps['slider_min'] + (7 * $maps['slider_step'])) . '] = "' . $maps['locarray'][$key][10] . '";';
            echo 'j[' . $nr . '][' . ($maps['slider_min'] + (8 * $maps['slider_step'])) . '] = "' . $maps['locarray'][$key][11] . '";';
            echo 'j[' . $nr . '][2000] = "' . $maps['locarray'][$key][12] . '";'; // called 2000 but contains up till today
            echo 'j[' . $nr . '][3] = "' . $maps['locarray'][$key][13] . '";';
            echo "\n";
            $nr++;
        }

        echo 'var namesearch = "";';
        $javastring = '';
        if ($maps['family_names'] != '') {   // querystring for multiple family names in popup names
            foreach ($maps['family_names'] as $value) {
                $javastring .= $value . "@";
            }
            $javastring = substr($javastring, 0, -1);  // Beck@Willems@Douglas@Smith
            echo " namesearch = '" . $javastring . "'; ";
        }
        ?>

        function setcolor(total) {
            var red = "fe2e2e";
            var blue = "2e64fe";
            var green = "2efe2e";
            var yellow = "f7fe2e";
            var cyan = "04b4ae";
            if (total < 10) {
                return yellow;
            } else if (total < 50) {
                return green;
            } else if (total < 100) {
                return blue;
            } else if (total < 10000) {
                return red;
            } else {
                return red;
            }
        }

        function setmarkersize(total) {
            if (total < 10) {
                return '0.4';
            } else if (total < 50) {
                return '0.5';
            } else if (total < 100) {
                return '0.75';
            } else if (total < 10000) {
                return '0.9';
            } else {
                return '1.05';
            }
        }

        function setfontsize(total) {
            if (total < 10) {
                return '12';
            } else if (total < 50) {
                return '12';
            } else if (total < 100) {
                return '12';
            } else if (total < 10000) {
                return '12';
            } else {
                return '12';
            }
        }

        function makeSelection(sel) {
            clearOverlays();

            var max = sel; // max is used for the "what" and "until" variables for the url_querystring to namesearch.php
            if (sel > 2000) { // gslider.js returned present year = last step in slider
                sel = 2000; // sel is used as member in the j array (j[4][sel]). this member is called "2000" for all born till present year
            }

            var what;
            var until;
            if (sel == 3) { // 3 flags the "all locations" button (j[i][3])
                what = "all=1"; // for url query string
                until = "<?= __('today '); ?>";
            } else { // years 1550, 1600 .... till today for slider
                what = "max=" + max; // for url querystring
                until = max; // "until 1850"
            }

            var namestring = '';
            if (namesearch != '') {
                namestring = 'namestring=' + namesearch;
                //namestring = encodeURI(namestring);
            }

            // simulates the php html_entity_decode function otherwise "Delft" is displayed in tooltip as &quot;Delft&quot;
            function convert_html(str) {
                var temp = document.createElement("pre");
                temp.innerHTML = str;
                return temp.firstChild.nodeValue;
            }

            var i;

            // Automatic map zoom.
            var latlngbounds = new google.maps.LatLngBounds();

            for (i = 0; i < j.length; i++) {
                var thislat = parseFloat(j[i][1]);
                var thislng = parseFloat(j[i][2]);
                var thisplace = encodeURI(j[i][0]);
                //a single quote in the name breaks the query string, so we escape it (+ double \\ to escape the \)
                thisplace = thisplace.replace(/'/g, "\\'"); // l'Ile d'Orleans becomes l\'Ile d\'Orleans

                // if 0: this location is not relevant for this period
                if (j[i][sel] > 0) {
                    /* Old script. Doesn't work anymore in 2024.
                    var latlng = new google.maps.LatLng(thislat, thislng);
                    // convert html entities in tooltip of marker:
                    var html_loc = convert_html(j[i][0]);
                    var styleMaker1 = new StyledMarker({
                        styleIcon: new StyledIcon(StyledIconTypes.MARKER, {
                            color: setcolor(j[i][sel]),
                            text: j[i][sel],
                            size: setmarkersize(j[i][sel]),
                            font: setfontsize(j[i][sel])
                        }),
                        title: html_loc,
                        position: latlng,
                        map: map
                    });
                    markersArray.push(styleMaker1);
                    */


                    // July 2024: new script
                    // convert html entities in tooltip of marker:
                    //var html_loc = convert_html(j[i][0]);
                    //var styleMaker1 = new google.maps.Marker({
                    //var styleMaker1 = new google.maps.marker.AdvancedMarkerElement({
                    var styleMaker1 = new google.maps.marker.AdvancedMarkerElement({
                        position: {
                            lat: thislat,
                            lng: thislng
                        },
                        map,
                        //shape: shape,
                        //title: html_loc,
                        //content: pinBackground.element,
                    });


                    // July 2024: Added automatic zoom
                    latlng = new google.maps.LatLng(thislat, thislng);
                    latlngbounds.extend(latlng);


                    // wikipedia doesn't search well with "Newcastle, NSW, Australia", so we just take the place name Newcastle
                    // Of course there are multiple places with this name but they will appear at the start of the wikipedia page, one click away...
                    var place;
                    var comma = j[i][0].search(/,/);
                    if (comma != -1) {
                        place = j[i][0].substr(0, comma);
                    } else {
                        place = j[i][0];
                    }

                    <?php
                    // language variables
                    echo 'var location ="' . __('Location: ') . '";';
                    if ($_SESSION['type_birth'] == 1) {
                        echo 'var list ="' . __('For a list of persons born here until ') . '";';
                    } elseif ($_SESSION['type_death'] == 1) {
                        echo 'var list ="' . __('For a list of all people that died here until ') . '";';
                    }
                    echo 'var click ="' . __(' click here') . '";';
                    echo 'var readabout ="' . __('Read about this location in ') . '";';

                    if ($selected_language == "hu") {
                        echo 'var wikilang="hu";';
                    } elseif ($selected_language == "nl") {
                        echo 'var wikilang="nl";';
                    } elseif ($selected_language == "fr") {
                        echo 'var wikilang="fr";';
                    } elseif ($selected_language == "de") {
                        echo 'var wikilang="de";';
                    } elseif ($selected_language == "fi") {
                        echo 'var wikilang="fi";';
                    } elseif ($selected_language == "es") {
                        echo 'var wikilang="es";';
                    } elseif ($selected_language == "pt") {
                        echo 'var wikilang="pt";';
                    } elseif ($selected_language == "it") {
                        echo 'var wikilang="it";';
                    } elseif ($selected_language == "no") {
                        echo 'var wikilang="no";';
                    } elseif ($selected_language == "sv") {
                        echo 'var wikilang="sv";';
                    } else {
                        echo 'var wikilang="en";';
                    }
                    ?>

                    google.maps.event.addListener(
                        styleMaker1, 'click', handleMarkerClick(
                            styleMaker1,
                            "<div>" + location + j[i][0] + "<br>" +
                            readabout +
                            "<a href=\"http://" + wikilang + ".wikipedia.org/wiki/" + place + "\" target=\"blank\"> Wikipedia </a><br> <div style = \"display:inline;\" id=\"ajaxlink\" onclick=\"loadurl('googlemaps/namesearch.php?thisplace=" +
                            thisplace + "&amp;" +
                            what +
                            "&amp;" +
                            namestring +
                            "')\">" +
                            list +
                            until +
                            ", <span style=\"color:blue;font-weight:bold\"><a href=\"javascript:void(0)\">" +
                            click + "</a></span><br><br><br><br><div style=\"min-width:370px\"></div></div></div>"
                        )
                    );

                }

                // Automatic zoom.
                map.fitBounds(latlngbounds);
            }
        }
    </script>

<?php
    /*
    echo '<script>
        window.onload = hide;
    </script>';
    */
}
?>




<?php if (1 == 0) { ?>
    <!-- TEST for colored and sized markers -->
    <!-- https://developers.google.com/maps/documentation/javascript/examples/advanced-markers-basic-style -->
    <!-- TODO check: https://developers.google.com/maps/documentation/javascript/advanced-markers/basic-customization -->

    <div id="map" style="height:520px"></div>

    <!-- prettier-ignore -->
    <script>
        (g => {
            var h, a, k, p = "The Google Maps JavaScript API",
                c = "google",
                l = "importLibrary",
                q = "__ib__",
                m = document,
                b = window;
            b = b[c] || (b[c] = {});
            var d = b.maps || (b.maps = {}),
                r = new Set,
                e = new URLSearchParams,
                u = () => h || (h = new Promise(async (f, n) => {
                    await (a = m.createElement("script"));
                    e.set("libraries", [...r] + "");
                    for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                    e.set("callback", c + ".maps." + q);
                    a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                    d[q] = f;
                    a.onerror = () => h = n(Error(p + " could not load."));
                    a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                    m.head.append(a)
                }));
            d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
        })
        ({
            key: "<?= $humo_option['google_api_key']; ?>",
            v: "weekly"
        });
    </script>

    <script>
        const parser = new DOMParser();

        async function initMap() {
            // Request needed libraries.
            const {
                Map
            } = await google.maps.importLibrary("maps");
            const {
                AdvancedMarkerElement,
                PinElement
            } = await google.maps.importLibrary(
                "marker",
            );
            const map = new Map(document.getElementById("map"), {
                center: {
                    lat: 37.419,
                    lng: -122.02
                },
                zoom: 14,
                mapId: "4504f8b37365c3d0",
            });

            // Each PinElement is paired with a MarkerView to demonstrate setting each parameter.
            // Default marker with title text (no PinElement).
            const markerViewWithText = new AdvancedMarkerElement({
                map,
                position: {
                    lat: 37.419,
                    lng: -122.03
                },
                title: "Title text for the marker at lat: 37.419, lng: -122.03",
            });

            // Adjust the scale.
            const pinScaled = new PinElement({
                scale: 1.5,
            });
            const markerViewScaled = new AdvancedMarkerElement({
                map,
                position: {
                    lat: 37.419,
                    lng: -122.02
                },
                content: pinScaled.element,
            });

            // Change the background color.
            const pinBackground = new PinElement({
                background: "#FBBC04",
            });
            const markerViewBackground = new AdvancedMarkerElement({
                map,
                position: {
                    lat: 37.419,
                    lng: -122.01
                },
                content: pinBackground.element,
            });

            // Change the background color.
            const pinTest = new PinElement({
                background: "#FFFFFF",
            });
            var test = new AdvancedMarkerElement({
                map,
                position: {
                    lat: 37.417,
                    lng: -122.01
                },
                content: pinTest.element,
            });

            var test = new AdvancedMarkerElement({
                map,
                position: {
                    lat: 37.417,
                    lng: -122.03
                },
                //content: pinTest.element,
            });

            // Change the border color.
            const pinBorder = new PinElement({
                borderColor: "#137333",
            });
            const markerViewBorder = new AdvancedMarkerElement({
                map,
                position: {
                    lat: 37.415,
                    lng: -122.03
                },
                content: pinBorder.element,
            });

            // Change the glyph color.
            const pinGlyph = new PinElement({
                glyphColor: "white",
            });
            const markerViewGlyph = new AdvancedMarkerElement({
                map,
                position: {
                    lat: 37.415,
                    lng: -122.02
                },
                content: pinGlyph.element,
            });

            // Hide the glyph.
            const pinNoGlyph = new PinElement({
                glyph: "",
            });
            const markerViewNoGlyph = new AdvancedMarkerElement({
                map,
                position: {
                    lat: 37.415,
                    lng: -122.01
                },
                content: pinNoGlyph.element,
            });
        }

        initMap();
    </script>

<?php } ?>