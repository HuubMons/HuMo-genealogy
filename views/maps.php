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

// *** Set birth or death display. Default values ***
// *** BE AWARE: session values are used in google_initiate script. If these are disabled, the slider doesn't work ***
$maps['display_birth'] = true;
$maps['display_death'] = false;
if (!isset($_SESSION['type_death']) && !isset($_SESSION['type_death'])) {
    $_SESSION['type_birth'] = 1;
    $_SESSION['type_death'] = 0;
}
if (isset($_SESSION['type_death']) && $_SESSION['type_death'] == 1) {
    $maps['display_death'] = true;
    $maps['display_birth'] = false;
}
if (isset($_POST['map_type']) && $_POST['map_type'] == "type_birth") {
    $_SESSION['type_birth'] = 1;
    $_SESSION['type_death'] = 0;
    $maps['display_birth'] = true;
    $maps['display_death'] = false;
}
if (isset($_POST['map_type']) && $_POST['map_type'] == "type_death") {
    $_SESSION['type_death'] = 1;
    $_SESSION['type_birth'] = 0;
    $maps['display_death'] = true;
    $maps['display_birth'] = false;
}

if ($maps['select_world_map'] == 'Google') {
    // slider defaults
    $realmin = 1560;  // first year shown on slider
    $step = "50";     // interval
    $minval = "1510"; // OFF position (first year minus step, year is not shown)
    $yr = date("Y");

    // check for stored min value, created with google maps admin menu
    $query = "SELECT setting_value FROM humo_settings WHERE setting_variable='gslider_" . $tree_prefix_quoted . "' ";
    $result = $dbh->query($query);
    if ($result->rowCount() > 0) {
        $sliderDb = $result->fetch(PDO::FETCH_OBJ);
        $realmin = $sliderDb->setting_value;
        $step = floor(($yr - $realmin) / 9);
        $minval = $realmin - $step;
    }

    $qry = "SELECT setting_value FROM humo_settings WHERE setting_variable='gslider_default_pos'";
    $result = $dbh->query($qry);
    if ($result->rowCount() > 0) {
        $def = $result->fetch(); // defaults to array
        $slider_def = $def['setting_value'];
        if ($slider_def == "off") {
            // slider at leftmost position
            $defaultyr = $minval;
            $default_display = "------>";
            $makesel = "";
        } else {
            // slider ar rightmost position
            $defaultyr = $yr;
            $default_display = $defaultyr;
            $makesel = " makeSelection(3); ";
        }
    } else {
        //$defaultyr = $minval; $default_display = "------>"; $makesel=""; // slider at leftmost position 
        $defaultyr = $yr;
        $default_display = $defaultyr;
        $makesel = " makeSelection(3); ";  // slider at rightmost position (default)
    }
}
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
                        $hide_tree = false;
                        if (in_array($tree_searchDb->tree_id, $hide_tree_array)) {
                            $hide_tree = true;
                        }
                        if ($hide_tree == false) {
                            $selected = '';
                            // TODO check tree_prefix. Replace with tree_id.
                            if (isset($_SESSION['tree_prefix'])) {
                                if ($tree_searchDb->tree_prefix == $_SESSION['tree_prefix']) {
                                    $selected = 'selected';
                                    $tree_id = $tree_searchDb->tree_id;
                                    $_SESSION['tree_id'] = $tree_id;
                                    $db_functions->set_tree_id($tree_id);
                                }
                            } elseif ($count == 0) {
                                $_SESSION['tree_prefix'] = $tree_searchDb->tree_prefix;
                                $selected = 'selected';
                                $tree_id = $tree_searchDb->tree_id;
                                $_SESSION['tree_id'] = $tree_id;
                                $db_functions->set_tree_id($tree_id);
                            }
                            $treetext = show_tree_text($tree_searchDb->tree_id, $selected_language);
                            $count++;
                    ?>
                            <option value="<?= $tree_searchDb->tree_id; ?>" <?= $selected; ?>><?= @$treetext['name']; ?></option>
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


        <?php if ($maps['select_world_map'] == 'Google') { ?>
            <div class="col-auto">
                <!-- Slider text & year box -->
                <div style="<?= $language['dir'] != "rtl" ? 'float:left' : 'float:right'; ?>">
                    <?php
                    echo '
                    <script>
                    var minval = ' . $minval . ';
                    $(function() {
                        // Set default slider setting
                        ' . $makesel . '
                        $( "#slider" ).slider({
                            value: ' . $defaultyr . ',
                            min: ' . $minval . ',
                            max: ' . $yr . ',
                            step: ' . $step . ',
                            slide: function( event, ui ) {
                                if(ui.value == minval) { $( "#amount" ).val("----->"); }
                                else if(ui.value > 2000) { $( "#amount" ).val(' . $yr . '); }
                                else {	$( "#amount" ).val(ui.value ); }
                            }
                        });
                        $( "#amount" ).val("' . $default_display . '");

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


    <?php if ($maps['select_world_map'] == 'Google') { ?>
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
                if ($maps['display_birth']) {
                    $loc_search = "SELECT * FROM humo_location WHERE location_lat IS NOT NULL AND location_status LIKE '%" . $tree_prefix_quoted . "birth%' OR location_status LIKE '%" . $tree_prefix_quoted . "bapt%' OR location_status = '' ORDER BY location_location";
                }
                if ($maps['display_death']) {
                    $loc_search = "SELECT * FROM humo_location WHERE location_lat IS NOT NULL AND location_status LIKE '%" . $tree_prefix_quoted . "death%' OR location_status LIKE '%" . $tree_prefix_quoted . "buried%' OR location_status = '' ORDER BY location_location";
                }
                $loc_search_result = $dbh->query($loc_search);
                //if ($loc_search_result !== false) {
                ?>
                <form method="POST" action="" style="display : inline;">
                    <select onChange="findPlace()" size="1" id="loc_search" name="loc_search" class="form-select form-select-sm">
                        <option value="toptext"><?= __('Find location on the map'); ?></option>
                        <?php
                        while ($loc_searchDb = $loc_search_result->fetch(PDO::FETCH_OBJ)) {
                            $count++;
                        ?>
                            <option value="<?= $loc_searchDb->location_id; ?>,<?= $loc_searchDb->location_lat; ?>,<?= $loc_searchDb->location_lng; ?>">
                                <?= $loc_searchDb->location_location; ?>
                            </option>
                        <?php } ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- optional row -->
        <?php
        // *** Searching by specific names ***
        $flag_namesearch = '';
        if (isset($_POST['items'])) {
            $flag_namesearch = $_POST['items'];
            $names = '';
            foreach ($flag_namesearch as $value) {
                $pos = strpos($value, '_');
                $pref = substr($value, $pos + 1);
                if ($pref !== '') {
                    $pref .= ' ';
                }
                $last = substr($value, 0, $pos);
                $names .= $pref . $last . ", ";
            }
            $names = substr($names, 0, -2); // take off last ", "
        }

        // *** Find descendants of chosen person ***
        $flag_desc_search = 0;
        $chosenperson = '';
        $persfams = '';
        if (isset($_GET['persged']) && isset($_GET['persfams'])) {
            $flag_desc_search = 1;
            $chosenperson = $_GET['persged'];
            $persfams = $_GET['persfams'];
            $persfams_arr = explode(';', $persfams);
            $myresult = $dbh->query("SELECT pers_lastname, pers_firstname, pers_prefix FROM humo_persons
                WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $chosenperson . "'");
            $myresultDb = $myresult->fetch(PDO::FETCH_OBJ);
            $chosenname = $myresultDb->pers_firstname . ' ' . strtolower(str_replace('_', '', $myresultDb->pers_prefix)) . ' ' . $myresultDb->pers_lastname;

            $generation_number = 0; // generation number

            // TODO use general descendant function
            function outline($outline_family_id, $outline_person, $generation_number)
            {
                global $dbh, $db_functions, $desc_array;
                global $language, $dirmark1, $dirmark1;
                $family_nr = 1; //*** Process multiple families ***

                $familyDb = $db_functions->get_family($outline_family_id, 'man-woman');
                $parent1 = '';
                $parent2 = '';
                $swap_parent1_parent2 = false;

                // *** Standard main_person is the father ***
                if ($familyDb->fam_man) {
                    $parent1 = $familyDb->fam_man;
                }
                // *** If mother is selected, mother will be main_person ***
                if ($familyDb->fam_woman == $outline_person) {
                    $parent1 = $familyDb->fam_woman;
                    $swap_parent1_parent2 = true;
                }

                // *** Check family with parent1: N.N. ***
                if ($parent1) {
                    // *** Save man's families in array ***
                    @$personDb = $db_functions->get_person($parent1, 'famc-fams');
                    $marriage_array = explode(";", $personDb->pers_fams);
                    $nr_families = substr_count($personDb->pers_fams, ";");
                } else {
                    $marriage_array[0] = $outline_family_id;
                    $nr_families = "0";
                }

                // *** Loop multiple marriages of main_person ***
                for ($parent1_marr = 0; $parent1_marr <= $nr_families; $parent1_marr++) {
                    @$familyDb = $db_functions->get_family($marriage_array[$parent1_marr]);

                    // *** Privacy filter man and woman ***
                    @$person_manDb = $db_functions->get_person($familyDb->fam_man);
                    @$person_womanDb = $db_functions->get_person($familyDb->fam_woman);

                    // *************************************************************
                    // *** Parent1 (normally the father)                         ***
                    // *************************************************************
                    if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, vrouw zonder man
                        if ($family_nr == 1) {
                            // *** Show data of man ***

                            if ($swap_parent1_parent2 == true) {
                                if ($person_womanDb->pers_birth_place || $person_womanDb->pers_bapt_place) {
                                    $desc_array[] = $person_womanDb->pers_gedcomnumber;
                                }
                            } elseif ($person_manDb->pers_birth_place || $person_manDb->pers_bapt_place) {
                                $desc_array[] = $person_manDb->pers_gedcomnumber;
                            }
                        } else {
                        }   // don't take person twice!
                        $family_nr++;
                    } // *** end check of PRO-GEN ***

                    // *************************************************************
                    // *** Children                                              ***
                    // *************************************************************
                    if ($familyDb->fam_children) {
                        //$childnr=1;
                        $child_array = explode(";", $familyDb->fam_children);
                        foreach ($child_array as $i => $value) {
                            @$childDb = $db_functions->get_person($child_array[$i]);

                            // *** Build descendant_report ***
                            if ($childDb->pers_fams) {
                                // *** 1e family of child ***
                                $child_family = explode(";", $childDb->pers_fams);
                                $child1stfam = $child_family[0];
                                outline($child1stfam, $childDb->pers_gedcomnumber, $generation_number);  // recursive
                            } else {    // Child without own family
                                if ($childDb->pers_birth_place || $childDb->pers_bapt_place) {
                                    $desc_array[] = $childDb->pers_gedcomnumber;
                                }
                            }
                        }
                        //$childnr++;
                    }
                } // Show  multiple marriages
            } // End of outline function

            // ******* Start function here - recursive if started ******
            $desc_array = [];
            outline($persfams_arr[0], $chosenperson, $generation_number);
            if ($desc_array != '') {
                $desc_array = array_unique($desc_array); // removes duplicate persons (because of related ancestors)
            }
        }

        // =============================
        // TODO use general ancestor function
        // *** Find ancestors ***
        $flag_anc_search = 0;
        $chosenperson = '';
        $persfams = '';
        if (isset($_GET['anc_persged']) && isset($_GET['anc_persfams'])) {
            $flag_anc_search = 1;
            $chosenperson = $_GET['anc_persged'];
            $persfams = $_GET['anc_persfams'];
            $persfams_arr = explode(';', $persfams);
            $myresult = $dbh->query("SELECT pers_lastname, pers_firstname, pers_prefix FROM humo_persons
                WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $chosenperson . "'");
            $myresultDb = $myresult->fetch(PDO::FETCH_OBJ);
            //also check privacy
            $chosenname = $myresultDb->pers_firstname . ' ' . strtolower(str_replace('_', '', $myresultDb->pers_prefix)) . ' ' . $myresultDb->pers_lastname;

            // function to find all ancestors - family_id = person GEDCOM number
            function find_anc($family_id)
            {
                global $dbh, $db_functions, $anc_array;
                global $language, $dirmark1, $dirmark1;
                global $listed_array;
                $ancestor_array2[] = $family_id;
                $ancestor_number2[] = 1;
                $marriage_gedcomnumber2[] = 0;
                $generation = 1;

                //$listed_array=array();

                // *** Loop for ancestor report ***
                while (isset($ancestor_array2[0])) {
                    unset($ancestor_array);
                    $ancestor_array = $ancestor_array2;
                    unset($ancestor_array2);

                    unset($ancestor_number);
                    $ancestor_number = $ancestor_number2;
                    unset($ancestor_number2);

                    unset($marriage_gedcomnumber);
                    $marriage_gedcomnumber = $marriage_gedcomnumber2;
                    unset($marriage_gedcomnumber2);
                    // *** Loop per generation ***
                    $counter = count($ancestor_array);

                    // *** Loop per generation ***
                    for ($i = 0; $i < $counter; $i++) {
                        $listednr = '';

                        foreach ($listed_array as $key => $value) {
                            if ($value == $ancestor_array[$i]) {
                                $listednr = $key;
                            }
                            // if person was already listed, $listednr gets kwartier number for reference in report:
                            // instead of person's details it will say: "already listed above under number 4234"
                            // and no additional ancestors will be looked for, to prevent duplicated branches
                        }
                        if ($listednr == '') {  //if not listed yet, add person to array
                            $listed_array[$ancestor_number[$i]] = $ancestor_array[$i];
                            //$listed_array[]=$ancestor_array[$i];  
                        }

                        if ($ancestor_array[$i] != '0') {
                            @$person_manDb = $db_functions->get_person($ancestor_array[$i]);

                            // ==	Check for parents
                            if ($person_manDb->pers_famc && $listednr == '') {
                                @$family_parentsDb = $db_functions->get_family($person_manDb->pers_famc);
                                if ($family_parentsDb->fam_man) {
                                    $ancestor_array2[] = $family_parentsDb->fam_man;
                                    $ancestor_number2[] = (2 * $ancestor_number[$i]);
                                    $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                                }

                                if ($family_parentsDb->fam_woman) {
                                    $ancestor_array2[] = $family_parentsDb->fam_woman;
                                    $ancestor_number2[] = (2 * $ancestor_number[$i] + 1);
                                    $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                                } else {
                                    // *** N.N. name ***
                                    $ancestor_array2[] = '0';
                                    $ancestor_number2[] = (2 * $ancestor_number[$i] + 1);
                                    $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                                }
                            }
                        } else {
                            // *** Show N.N. person ***
                            @$person_manDb = $db_functions->get_person($ancestor_array[$i]);
                            $listed_array[0] = $person_manDb->pers_gedcomnumber;
                        }
                    }    // loop per generation
                    $generation++;
                }    // loop ancestor report

            }

            // ******* Start function here ******
            $anc_array = array();
            $listed_array = array();
            find_anc($chosenperson);
            foreach ($listed_array as $value) {
                $anc_array[] = $value;
            }
            //$anc_array = $listed_array;
        }

        if (isset($_POST['items']) || isset($_GET['persged']) && isset($_GET['persfams']) || isset($_GET['anc_persged']) && isset($_GET['anc_persfams'])) {
        ?>
            <div class="row mb-2 p-2 bg-info">
                <div class="col-auto">

                    <!-- Searching by specific names -->
                    <?php if (isset($_POST['items'])) { ?>
                        <div id="name_search">
                            <?= __('Mapping with specific name(s): '); ?>
                            <?= $names; ?>. <a href="<?= $link; ?>"><?= __('Switch name filter off'); ?></a>
                        </div>
                    <?php } ?>

                    <!-- Find descendants of chosen person -->
                    <?php if (isset($_GET['persged']) && isset($_GET['persfams'])) { ?>
                        <div id="desc_search">
                            <?php if ($desc_array != '') { ?>
                                <?= __('Filter by descendants of: ') . trim($chosenname); ?>. <a href="<?= $link; ?>"><?= __('Switch descendant filter off'); ?></a>
                            <?php } else { ?>
                                <?= __('No known birth places amongst descendants'); ?>. <a href="<?= $link; ?>"><?= __('Close'); ?></a>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <!-- Find ancestors -->
                    <?php if (isset($_GET['anc_persged']) && isset($_GET['anc_persfams'])) { ?>
                        <div id="anc_search">
                            <?php if ($anc_array != '') { ?>
                                <?= __('Filter by ancestors of: ') . trim($chosenname); ?>. <a href="<?= $link; ?>"><?= __('Switch ancestor filter off'); ?></a>
                            <?php } else { ?>
                                <?= __('No known birth places amongst ancestors'); ?>. <a href="<?= $link; ?>"><?= __('Close'); ?></a>
                            <?php } ?>
                        </div>
                    <?php } ?>

                </div>
            </div>
        <?php } ?>

    <?php } ?>

</div>



<?php
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
                            $man_cls = new person_cls($desc_searchDb);
                            $privacy_man = $man_cls->privacy;
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
                            $man_cls = new person_cls($anc_searchDb);
                            $privacy_man = $man_cls->privacy;
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
    $location_array[] = '';
    $lat_array[] = '';
    $lon_array[] = '';
    $text_array[] = '';
    $text_count_array[] = '';

    $location = $dbh->query("SELECT location_id, location_location, location_lat, location_lng FROM humo_location WHERE location_lat IS NOT NULL");
    while (@$locationDb = $location->fetch(PDO::FETCH_OBJ)) {
        $locarray[$locationDb->location_location][0] = htmlspecialchars($locationDb->location_location);
        $locarray[$locationDb->location_location][1] = $locationDb->location_lat;
        $locarray[$locationDb->location_location][2] = $locationDb->location_lng;
        //$locarray[$locationDb->location_location][3] = 0;    // till starting year  (depending on settings)
        //$locarray[$locationDb->location_location][4] = 0;    // + 1 interval
        //$locarray[$locationDb->location_location][5] = 0;    // + 2 intervals
        //$locarray[$locationDb->location_location][6] = 0;    // + 3 intervals
        //$locarray[$locationDb->location_location][7] = 0;    // + 4 intervals
        //$locarray[$locationDb->location_location][8] = 0;    // + 5 intervals
        //$locarray[$locationDb->location_location][9] = 0;    // + 6 intervals
        //$locarray[$locationDb->location_location][10] = 0;   // + 7 intervals
        //$locarray[$locationDb->location_location][11] = 0;   // + 8 intervals
        //$locarray[$locationDb->location_location][12] = 0;   // till today (=2010 and beyond)
        //$locarray[$locationDb->location_location][13] = 0;   // all


        //TEST add all location in maps...
        //$location_array[] = htmlspecialchars($locationDb->location_location);
        //$text_array[] = '<b>'.htmlspecialchars($locationDb->location_location).'</b>';
        //$lat_array[] = $locationDb->location_lat;
        //$lon_array[] = $locationDb->location_lng;
    }

    $namesearch_string = '';
    if ($maps['display_birth']) {
        //$persoon=$dbh->query("SELECT pers_tree_id, pers_birth_place, pers_birth_date, pers_bapt_place, pers_bapt_date
        //	FROM humo_persons WHERE pers_tree_id='".$tree_id."'
        //	AND (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !='')) ".$namesearch_string);
        $persoon = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
            AND (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !='')) " . $namesearch_string);
    } elseif ($maps['display_death']) {
        //$persoon=$dbh->query("SELECT pers_tree_id, pers_death_place, pers_death_date, pers_buried_place, pers_buried_date
        //	FROM humo_persons WHERE pers_tree_id='".$tree_id."'
        //	AND (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !='')) ".$namesearch_string);
        $persoon = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
            AND (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !='')) " . $namesearch_string);
    }
    while (@$personDb = $persoon->fetch(PDO::FETCH_OBJ)) {
        if ($maps['display_birth']) {
            $place = $personDb->pers_birth_place;
            $date = $personDb->pers_birth_date;
            if (!$personDb->pers_birth_place && $personDb->pers_bapt_place) {
                $place = $personDb->pers_bapt_place;
            }
            if (!$personDb->pers_birth_date && $personDb->pers_bapt_date) {
                $date = $personDb->pers_bapt_date;
            }
        } elseif ($maps['display_death']) {
            $place = $personDb->pers_death_place;
            $date = $personDb->pers_death_date;
            if (!$personDb->pers_death_place && $personDb->pers_buried_place) {
                $place = $personDb->pers_buried_place;
            }
            if (!$personDb->pers_death_date && $personDb->pers_buried_date) {
                $date = $personDb->pers_buried_date;
            }
        }

        if (isset($locarray[$place])) { // Place exists in location database
            if ($date) {
                $year = substr($date, -4);

                //if($year > 1 AND $year < $realmin) {  $locarray[$place][3]++; }
                //if($year > 1 AND $year < ($realmin+ $step)) {  $locarray[$place][4]++; }
                //if($year > 1 AND $year < ($realmin+ (2*$step))) {  $locarray[$place][5]++; }
                //if($year > 1 AND $year < ($realmin+ (3*$step))) {  $locarray[$place][6]++; }
                //if($year > 1 AND $year < ($realmin+ (4*$step))) {  $locarray[$place][7]++; }
                //if($year > 1 AND $year < ($realmin+ (5*$step))) {  $locarray[$place][8]++; }
                //if($year > 1 AND $year < ($realmin+ (6*$step))) {  $locarray[$place][9]++; }
                //if($year > 1 AND $year < ($realmin+ (7*$step))) {  $locarray[$place][10]++; }
                //if($year > 1 AND $year < ($realmin+ (8*$step))) {  $locarray[$place][11]++; }
                //if($year > 1 AND $year < 2050) {  $locarray[$place][12]++; }
                //$locarray[$place][13]++;  // array of all people incl without birth date

                // *** Use person class ***
                // TODO: this slows down page for large family trees. Use Javascript to show persons?
                $person_cls = new person_cls($personDb);
                $name = $person_cls->person_name($personDb);

                $key = array_search($locarray[$place][0], $location_array);
                if (isset($key) && $key > 0) {
                    // *** Check the number of lines of the text_array ***
                    $text_count_array[$key]++;
                    // *** For now: limited results in text box of OpenStreetMap ***
                    if ($text_count_array[$key] < 26) {
                        $text_array[$key] .= '<br>' . addslashes($name["standard_name"] . ' ' . $locarray[$place][0]);
                    }
                    if ($text_count_array[$key] == 26) {
                        $text_array[$key] .= '<br>' . __('Results are limited.');
                    }
                } else {
                    $location_array[] = htmlspecialchars($locarray[$place][0]);
                    $lat_array[] = $locarray[$place][1];
                    $lon_array[] = $locarray[$place][2];

                    $text_array[] = addslashes($name["standard_name"] . ' ' . $locarray[$place][0]);
                    $text_count_array[] = 1; // *** Number of text lines ***
                }
            } else {
                //$locarray[$place][13]++ ; // array of all people incl without birth date
            }
            //echo $locarray[$place][1].'!'.$locarray[$place][2];
        }
    }
?>

    <link rel="stylesheet" href="assets/leaflet/leaflet.css">
    <script src="assets/leaflet/leaflet.js"></script>

    <!-- Show OpenStreetMap -->
    <div id="map" style="height:520px"></div>

    <?php
    // *** Map using fitbound (all markers visible) ***
    echo '<script>
        var map = L.map("map").setView([48.85, 2.35], 10);
        var markers = [';

    // *** Add all markers from array ***
    for ($i = 1; $i < count($location_array); $i++) {
        if ($i > 1) echo ',';
        echo 'L.marker([' . $lat_array[$i] . ', ' . $lon_array[$i] . ']) .bindPopup(\'' . $text_array[$i] . '\')';
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

    // *** Google Maps ***
    echo '<div id="map_canvas" style="height:520px"></div>'; // placeholder div for map generated below

    // function to read multiple values from location search bar and zoom to map location:
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

<?php
    include_once(__DIR__ . "/../googlemaps/google_initiate.php");

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