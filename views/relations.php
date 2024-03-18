<?php

/**
 * relations.php - checks relationships between person X and person Y
 *
 * Aug. 2010 written by Yossi Beck - for HuMo-genealogy
 * 2011 - 2023 adjusted for several languages by Yossi Beck
 * Feb. 2014 extended marital calculator added by Yossi Beck
 * Nov. 2023 prepare MVC model by Huub Mons.
 * 
 *
 * contains the following functions:
 * create_rel_array      - creates $rel_array with GEDCOM nr and generation nr of ancestors of person X and Y
 * compare_rel_array     - compares the $rel_array arrays of X and Y to find common ancestor (can be the persons themselves)
 * calculate_rel         - if found, determines the nature of the relation (siblings, ancestors, nephews etc.)
 * calculate_ancestor    - calculates the degree of relations (2nd great-grandfather)
 * calculate_descendant  - calculates the degree of relations (3rd great-grandson)
 * calculate_nephews     - calculates the degree of relations (grand-niece)
 * calculate_uncles      - calculates the degree of relations (4th great-grand-uncle)
 * calculate_cousins     - calculates the degree of relations (2nd cousin twice removed)
 * search_marital        - if no direct blood relation found, searches for relation between spouses of person X and Y
 * search_bloodrel       - searches for blood relationship between X and Y
 * display               - displays the result of comparison checks
 * display_table         - displays simple chart showing the found relationship
 * unset_var             - unsets the vital variables before searching marital relations
 * get_person            - retrieves person from MySQL database by GEDCOM nr
 * dutch_ancestor        - special algorithm to process complicated dutch terminology for distant ancestors
 *
 * the meaning of the value of the $table variable (for displaying table with lineage if a match is found):
 * 1 = parent - child
 * 2 = child - parent
 * 3 = uncle - nephew
 * 4 = nephew - uncle
 * 5 = cousin
 * 6 = siblings
 * 7 = spouses or self
 *
 * the meaning of the value of the $spouse variable (flagging type of relationship check):
 * 0 = checks relation X vs Y
 * 1 = checks relation spouse of X versus person Y
 * 2 = checks relation person X versus spouse of Y
 * 3 = checks relation spouse of X versus spouse of Y
 *
 * values in the genarray:
 * the genarray is an array of the ancestors of a base person (one of the two persons entered in the search or their spouses)
 * genarray[][0] = GEDCOM number of the person
 * genarray[][1] = number of generations (counted from base person)
 * genarray[][2] = array number of child
 *
 * meaning of some other global variables:
 * $doublespouse - flags situation where searched persons X and Y are both spouses of a third person
 * $special_spouseX (and Y) - flags situation where the regular text "spouse of" has to be changed:
 * ----- for example: "X is spouse of brother of Y" should become "X is sister-in-law of Y"
 * $sexe, $sexe2 - the sexe of persons X and Y
 * $data["person1"], $data["person2"] - GEDCOM nr of the searched persons X and Y
 */

// TODO create function to show person.
// TODO use a popup selection screen to select persons?

// http://localhost/HuMo-genealogy/family/3/F116?main_person=I202
$fampath = $link_cls->get_link($uri_path, 'family', $tree_id, true);

$relpath_form = $link_cls->get_link($uri_path, 'relations', $tree_id);

$data_found["foundX_nr"] = '';
$data_found["foundY_nr"] = '';
$data_found["foundX_gen"] = '';
$data_found["foundY_gen"] = '';
$data_found["foundX_match"] = '';
$data_found["foundY_match"] = '';
$spouse = '';
$reltext = '';
$special_spouseX = '';
$special_spouseY = '';
$table = '';
$name1 = '';
$name2 = '';

$pers_cls = new person_cls;

// No longer needed? Allready removed multiple $len variables.
//$len = 230; // length of name pulldown box

$limit = 500; // *** Limit results ***
?>

<!-- TODO not sure if this is still usefull in modern browsers. -->
<?php if (isset($_POST["extended"]) or isset($_POST["next_path"])) { ?>
    <div id="geargif"><br><img src="images/gear.gif">&nbsp;&nbsp;&nbsp;<?= __('Calculating relations'); ?></div>
<?php } ?>

<form method="POST" action="<?= $relpath_form; ?>" style="display : inline;">
    <div class="p-2 me-sm-2 genealogy_search">
        <div class="row">
            <div class="col-md-2"><b><?= __('Person') . ' 1'; ?></b></div>
        </div>

        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-auto">
                <?= __('Name'); ?>
                <div class="input-group mb-3">
                    <input type="text" name="search_name" value="<?= safe_text_show($data["search_name1"]); ?>" size="20" placeholder="<?= __('Name'); ?>" class="form-control form-control-sm">
                    <input type="submit" name="button_search_name1" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="col-md-auto">
                <?= __('or: ID'); ?>
                <div class="input-group mb-3">
                    <input type="text" name="search_gednr" value="<?= safe_text_show($data["search_gednr1"]); ?>" size="8" class="form-control form-control-sm">
                    <input type="submit" name="button_search_id1" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="col-md-3">
                <?= __('Pick a name from search results'); ?>
                <?php
                if (isset($_SESSION["button_search_name1"]) and $_SESSION["button_search_name1"] == 1) {
                    $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id=" . $tree_id . " ORDER BY pers_lastname, pers_firstname LIMIT 0," . $limit;

                    if ($data["search_name1"] != '') {
                        // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
                        $data["search_name1"] = str_replace(' ', '%', $data["search_name1"]);
                        // *** In case someone entered "Mons, Huub" using a comma ***
                        $data["search_name1"] = str_replace(',', '', $data["search_name1"]);
                        // *** August 2022: new query ***
                        $search_qry = "
                            SELECT * FROM humo_persons LEFT JOIN humo_events
                            ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id 
                            WHERE pers_tree_id='" . $tree_id . "' AND
                                (
                                CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%" . safe_text_db($data["search_name1"]) . "%'
                                OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%" . safe_text_db($data["search_name1"]) . "%' 
                                OR CONCAT(pers_patronym,pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($data["search_name1"]) . "%' 
                                OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%" . safe_text_db($data["search_name1"]) . "%'
                                OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($data["search_name1"]) . "%'
                                OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%" . safe_text_db($data["search_name1"]) . "%' 
                                OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($data["search_name1"]) . "%' 
                                OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%" . safe_text_db($data["search_name1"]) . "%'
                                )
                                GROUP BY pers_id, event_event, event_kind, event_id
                                ORDER BY pers_lastname, pers_firstname, CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) LIMIT 0," . $limit;
                    } elseif ($data["search_gednr1"] != '') {
                        $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                            AND (pers_gedcomnumber = '" . $data["search_gednr1"] . "' OR pers_gedcomnumber = 'I" . $data["search_gednr1"] . "')";
                    }

                    // *** Link from person pop-up menu ***
                    if (isset($_SESSION["search_pers_id"])) {
                        $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_id='" . $_SESSION["search_pers_id"] . "'";
                    }

                    $search_result = $dbh->query($search_qry);
                    if ($search_result) {
                        $number_results = $search_result->rowCount();
                        if ($number_results > 0) {
                ?>
                            <select size="1" name="person1" class="form-select form-select-sm">
                                <?php
                                while ($searchDb = $search_result->fetch(PDO::FETCH_OBJ)) {
                                    $name = $pers_cls->person_name($searchDb);
                                    if ($name["show_name"]) {
                                        $birth = '';
                                        if ($searchDb->pers_bapt_date) {
                                            $birth = ' ' . __('~') . ' ' . date_place($searchDb->pers_bapt_date, '');
                                        }
                                        if ($searchDb->pers_birth_date) {
                                            $birth = ' ' . __('*') . ' ' . date_place($searchDb->pers_birth_date, '');
                                        }
                                        $search1_cls = new person_cls($searchDb);
                                        if ($search1_cls->privacy) {
                                            $birth = '';
                                        }

                                        echo '<option';
                                        if (isset($data["person1"])) {
                                            if ($searchDb->pers_gedcomnumber == $data["person1"] and !(isset($_POST["button_search_name1"]) and $data["search_name1"] == '' and $data["search_gednr1"] == '')) {
                                                echo ' selected';
                                            }
                                        }
                                        echo ' value="' . $searchDb->pers_gedcomnumber . '">' . $name["index_name"] . $birth . ' [' . $searchDb->pers_gedcomnumber . ']</option>';
                                    }
                                }
                                // *** Simple test only, if number of results = limit then show message ***
                                if ($number_results == $limit) {
                                    echo '<option value="">' . __('Results are limited, use search to find more persons.') . '</option>';
                                }
                                ?>
                            </select>
                        <?php } else { ?>
                            <select size="1" name="notfound" value="1" class="form-select form-select-sm">
                                <option><?= __('Person not found'); ?>
                                </option>
                            </select>
                    <?php
                        }
                    }
                } else {
                    ?>
                    <select size="1" name="person" class="form-select form-select-sm">
                        <option></option>
                    </select>
                <?php } ?>
            </div>
        </div>

        <!-- Second person -->
        <div class="row">
            <div class="col-md-2"><b><?= __('Person') . ' 2'; ?></b></div>
        </div>

        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-auto">
                <?= __('Search'); ?>
                <div class="input-group mb-3">
                    <input type="text" name="search_name2" value="<?= safe_text_show($data["search_name2"]); ?>" size="20" placeholder="<?= __('Name'); ?>" class="form-control form-control-sm">
                    <input type="submit" name="button_search_name2" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="col-md-auto">
                <?= __('or: ID'); ?>
                <div class="input-group mb-3">
                    <input type="text" name="search_gednr2" value="<?= safe_text_show($data["search_gednr2"]); ?>" size="8" class="form-control form-control-sm">
                    <input type="submit" name="button_search_id2" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="col-md-3">
                <?= __('Pick a name from search results'); ?>
                <?php
                if (isset($_SESSION["button_search_name2"]) and $_SESSION["button_search_name2"] == 1) {
                    $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id=" . $tree_id . " ORDER BY pers_lastname, pers_firstname LIMIT 0," . $limit;

                    if ($data["search_name2"] != '') {
                        // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
                        $data["search_name2"] = str_replace(' ', '%', $data["search_name2"]);
                        // *** In case someone entered "Mons, Huub" using a comma ***
                        $data["search_name2"] = str_replace(',', '', $data["search_name2"]);
                        // *** August 2022: new query ***
                        $search_qry = "
                            SELECT * FROM humo_persons LEFT JOIN humo_events
                            ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id 
                            WHERE pers_tree_id='" . $tree_id . "' AND
                                (
                                CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%" . safe_text_db($data["search_name2"]) . "%'
                                OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%" . safe_text_db($data["search_name2"]) . "%' 
                                OR CONCAT(pers_patronym,pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($data["search_name2"]) . "%' 
                                OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%" . safe_text_db($data["search_name2"]) . "%'
                                OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($data["search_name2"]) . "%'
                                OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%" . safe_text_db($data["search_name2"]) . "%' 
                                OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($data["search_name2"]) . "%' 
                                OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%" . safe_text_db($data["search_name2"]) . "%'
                                )
                                GROUP BY pers_id, event_event, event_kind, event_id
                                ORDER BY pers_lastname, pers_firstname, CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) LIMIT 0," . $limit;
                    } elseif ($data["search_gednr2"] != '') {
                        $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                            AND (pers_gedcomnumber = '" . $data["search_gednr2"] . "' OR pers_gedcomnumber = 'I" . $data["search_gednr2"] . "')";
                    }

                    // *** Link from person pop-up menu ***
                    if (isset($_SESSION["search_pers_id2"])) {
                        $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_id='" . $_SESSION["search_pers_id2"] . "'";
                    }

                    $search_result2 = $dbh->query($search_qry);
                    if ($search_result2) {
                        $number_results = $search_result2->rowCount();
                        if ($number_results > 0) {
                ?>
                            <select size="1" name="person2" class="form-select form-select-sm">
                                <?php
                                while ($searchDb2 = $search_result2->fetch(PDO::FETCH_OBJ)) {
                                    $name = $pers_cls->person_name($searchDb2);
                                    if ($name["show_name"]) {
                                        $birth = '';
                                        if ($searchDb2->pers_bapt_date) {
                                            $birth = ' ' . __('~') . ' ' . date_place($searchDb2->pers_bapt_date, '');
                                        }
                                        if ($searchDb2->pers_birth_date) {
                                            $birth = ' ' . __('*') . ' ' . date_place($searchDb2->pers_birth_date, '');
                                        }
                                        $search2_cls = new person_cls($searchDb2);
                                        if ($search2_cls->privacy) {
                                            $birth = '';
                                        }

                                        echo '<option';
                                        if (isset($data["person2"])) {
                                            if ($searchDb2->pers_gedcomnumber == $data["person2"] and !(isset($_POST["button_search_name2"]) and $data["search_name2"] == '' and $data["search_gednr2"] == '')) {
                                                echo ' selected';
                                            }
                                        }
                                        echo ' value="' . $searchDb2->pers_gedcomnumber . '">' . $name["index_name"] . $birth . ' [' . $searchDb2->pers_gedcomnumber . ']</option>';
                                    }
                                }
                                // *** Simple test only, if number of results = limit then show message ***
                                if ($number_results == $limit) {
                                    echo '<option value="">' . __('Results are limited, use search to find more persons.') . '</option>';
                                }
                                ?>
                            </select>
                        <?php } else { ?>
                            <select size="1" name="notfound" value="1" class="form-select form-select-sm">
                                <option><?= __('Person not found'); ?></option>
                            </select>
                    <?php
                        }
                    }
                } else {
                    ?>
                    <select size="1" name="person2" class="form-select form-select-sm">
                        <option></option>
                    </select>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-auto">
                <!-- HELP POPUP -->
                <div class="<?= $rtlmarker; ?>sddm" style="display:inline;">
                    <a href="#" style="display:inline" onmouseover="mopen(event,'help_address_address',100,200)" onmouseout="mclosetime()">
                        <img src="images/help.png">
                    </a>
                    <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_address_address" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                        <?= __('This calculator will find the following relationships:<br>
<ul><li>Any blood relationship between X and Y ("X is great-grandfather of Y", "X is 3rd cousin once removed of Y" etc.)</li>
<li>Blood relationship between the spouse of X and person Y ("X is spouse of 2nd cousin of Y", "X is son-in-law of Y")</li>
<li>Blood relationship between person X and the spouse of Y ("X is 2nd cousin of spouse of Y", "X is father-in-law of Y")</li>
<li>Blood relationship between spouse of X and spouse of Y ("X spouse of sister-in-law of Y" etc.)</li>
<li>Direct marital relation ("X is spouse of Y")</li></ul>
Directions for use:<br>
<ul><li>Enter first and/or last name (or part of names) in the search boxes and press "Search". Repeat this for person 1 and 2.</li>
<li>If more than 1 person is found, select the one you want from the search result pulldown box. Repeat this for person 1 and 2.</li>
<li>Now press the "Calculate relationships" button on the right.</li>
<li><b>TIP: when you click "search" with empty first <u>and</u> last name boxes you will get a list with all persons in the database. (May take a few seconds)</b></li></ul>'); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-auto">
                <?php
                /* <input type="submit" alt="<?= __('Switch persons'); ?>" title="<?= __('Switch persons'); ?>" value=" " name="switch" style="background: #fff url('images/turn_around.gif') top no-repeat;width:25px;height:25px"> */
                ?>
                <input type="submit" name="switch" value="<?= __('Switch persons'); ?>" class="btn btn-sm btn-secondary">
            </div>
            <div class="col-md-auto">
                <input type="submit" name="calculator" value="<?= __('Calculate relationships'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST["extended"]) or isset($_POST["next_path"])) {
        if (!isset($_POST["next_path"])) {
            $_SESSION['next_path'] = "";
        }

        echo '<script> var element = document.getElementById("geargif");  element.parentNode.removeChild(element);   </script>';

        $count = 0;
        $countfunc = 0;

        $globaltrack = "";
        $firstcall = array();
        $firstcall[0] = $data["person1"] . "@fst@fst@" . "fst" . $data["person1"];

        $globaltrack2 = "";
        $firstcall2 = array();
        $firstcall2[0] = $data["person2"] . "@fst@fst@" . "fst" . $data["person2"];

        $total_arr = array();

        if (isset($_POST["extended"]) and !isset($_POST["next_path"])) {
            $_SESSION["couple"] = "";
            // session[couple] flags that persons A & B are a couple. consequences: 
            // 1. don't display that (has already been done in regular calculator)
            // 2. in the map_tree function don't search thru the fam of the couple, since this gives errors.
            $persDb = $db_functions->get_person($data["person1"]);
            $pers2Db = $db_functions->get_person($data["person2"]);
            if (isset($persDb->pers_fams) and isset($pers2Db->pers_fams)) {
                $fam1 = explode(";", $persDb->pers_fams);
                $fam2 = explode(";", $pers2Db->pers_fams);
                foreach ($fam1 as $value1) {
                    foreach ($fam2 as $value2) {
                        if ($value1 == $value2) {
                            $_SESSION["couple"] = $value1;
                        }
                    }
                }
            }
        }

    ?>
        <br><br>
        <table class="ext">
            <tr>
                <td>
                    <?php
                    $global_array = array();
                    $global_array2 = array();

                    map_tree($firstcall, $firstcall2);
                    ?>
                </td>
            </tr>
        </table>
    <?php
    }

    // calculate or switch button is pressed
    if (isset($_POST["calculator"]) or isset($_POST["switch"])) {
        // 2 persons have been selected
        if (isset($data["person1"]) and $data["person1"] != '' and isset($data["person2"]) and $data["person2"] != '') {
            $searchDb = $db_functions->get_person($data["person1"]);
            $searchDb2 = $db_functions->get_person($data["person2"]);
            if (isset($searchDb)) {
                $gednr = $searchDb->pers_gedcomnumber;
                $name = $pers_cls->person_name($searchDb);
                $name1 = $name["name"];
                $sexe = '';
                if ($searchDb->pers_sexe == 'M') {
                    $sexe = 'm';
                } else {
                    $sexe = 'f';
                }
            }
            if ($searchDb->pers_fams) {
                $famsX = $searchDb->pers_fams;
                $tempfam = explode(";", $famsX);
                $famX = $tempfam[0];
            } else {
                $famX = $searchDb->pers_famc;
            }

            if (isset($searchDb2)) {
                $gednr2 = $searchDb2->pers_gedcomnumber;
                $name = $pers_cls->person_name($searchDb2);
                $name2 = $name["name"];
                $sexe2 = '';
                if ($searchDb2->pers_sexe == 'M') {
                    $sexe2 = 'm';
                } else {
                    $sexe2 = 'f';
                }
            }
            if ($searchDb2->pers_fams) {
                $famsY = $searchDb2->pers_fams;
                $tempfam = explode(";", $famsY);
                $famY = $tempfam[0];
            } else {
                $famY = $searchDb2->pers_famc;
            }

            // initiates all the comparison and calculation functions and writes result
            display();
        } else {
            // "calculate" or "switch" button pressed with one or two names not selected: write warning to first choose two names
            echo "<br><h3>&nbsp;&nbsp;&nbsp;" . __('You have to search and than choose Person 1 and Person 2 from the search result pulldown') . "</h3>";
        }
    }
    ?>
</form>
<br><br><br>



<?php
function create_rel_array($db_functions, $gednr)
{
    // creates array of ancestors of person with GEDCOM nr. $gednr
    $family_id = $gednr;
    $ancestor_id2[] = $family_id;
    $ancestor_number2[] = 1;
    $marriage_number2[] = 0;
    $generation = 1;
    $genarray_count = 0;

    $trackfamc = array();

    // *** Loop ancestor report ***
    while (isset($ancestor_id2[0])) {
        unset($ancestor_id);
        $ancestor_id = $ancestor_id2;
        unset($ancestor_id2);

        unset($ancestor_number);
        $ancestor_number = $ancestor_number2;
        unset($ancestor_number2);

        unset($marriage_number);
        $marriage_number = $marriage_number2;
        unset($marriage_number2);

        // *** Loop per generation ***
        $kwcount = count($ancestor_id);
        for ($i = 0; $i < $kwcount; $i++) {
            if ($ancestor_id[$i] != '0') {
                $person_manDb = $db_functions->get_person($ancestor_id[$i]);
                /*
                $man_cls = New person_cls($person_manDb);
                $man_privacy=$man_cls->privacy;
                if (strtolower($person_manDb->pers_sexe)=='m' AND $ancestor_number[$i]>1){
                    @$familyDb=$db_functions->get_family($marriage_number[$i]);

                    // *** Use privacy filter of woman ***
                    $person_womanDb=$db_functions->get_person($familyDb->fam_woman);
                    $woman_cls = New person_cls($person_womanDb);
                    $woman_privacy=$woman_cls->privacy;

                    // *** Use class for marriage ***
                    $marriage_cls = New marriage_cls($familyDb, $man_privacy, $woman_privacy);
                    $family_privacy=$marriage_cls->privacy;
                }
                */

                //*** Show person data ***
                $genarray[$genarray_count][0] = $ancestor_id[$i];
                $genarray[$genarray_count][1] = $generation - 1;
                $genarray_count++; // increase by one

                // *** Check for parents ***
                if ($person_manDb->pers_famc and !in_array($person_manDb->pers_famc, $trackfamc)) {
                    $trackfamc[] = $person_manDb->pers_famc;

                    @$familyDb = $db_functions->get_family($person_manDb->pers_famc);
                    if ($familyDb->fam_man) {
                        $ancestor_id2[] = $familyDb->fam_man;
                        $ancestor_number2[] = (2 * $ancestor_number[$i]);
                        $marriage_number2[] = $person_manDb->pers_famc;
                        $genarray[][2] = $genarray_count - 1;
                        // save array nr of child in parent array so we can build up ancestral line later
                    }

                    if ($familyDb->fam_woman) {
                        $ancestor_id2[] = $familyDb->fam_woman;
                        $ancestor_number2[] = (2 * $ancestor_number[$i] + 1);
                        $marriage_number2[] = $person_manDb->pers_famc;
                        $genarray[][2] = $genarray_count - 1;
                        // save array nr of child in parent array so we can build up ancestral line later
                    }
                }
            }
        }    // loop per generation
        $generation++;
    }    // loop ancestors

    return @$genarray;
}

function compare_rel_array($arrX, $arrY, $spouce_flag)
{
    global $spouse;
    global $data_found;

    foreach ($arrX as $keyx => $valx) {
        foreach ($arrY as $keyy => $valy) {
            if ($arrX[$keyx][0] == $arrY[$keyy][0]) {
                $data_found["foundX_match"] = $keyx;  // saves the array nr of common ancestor in ancestor array of X
                $data_found["foundY_match"] = $keyy;  // saves the array nr of common ancestor in ancestor array of Y
                if (isset($arrX[$keyx][2])) {
                    $data_found["foundX_nr"] = $arrX[$keyx][2];
                } // saves the array nr of the child leading to X
                if (isset($arrY[$keyy][2])) {
                    $data_found["foundY_nr"] = $arrY[$keyy][2];
                } // saves the array nr of the child leading to Y
                if (isset($arrX[$keyx][1])) {
                    $data_found["foundX_gen"] = $arrX[$keyx][1];
                } // saves the nr of generations common ancestor is removed from X
                if (isset($arrY[$keyy][1])) {
                    $data_found["foundY_gen"] = $arrY[$keyy][1];
                } // saves the nr of generations common ancestor is removed from Y
                $spouse = $spouce_flag; // saves global variable flagging if we're comparing X - Y or spouse combination
                return;
            }
        }
    }
}


function calculate_rel($data_found)
{
    // calculates the relationship found: "X is 2nd cousin once removed of Y"
    global $reltext, $sexe, $sexe2, $spouse, $special_spouseY, $special_spouseX, $doublespouse, $table;
    global $selected_language;

    $doublespouse = 0;
    if ($data_found["foundX_match"] == 0 and $data_found["foundY_match"] == 0) {  // self
        $reltext = __(' identical to ');
        if ($spouse == 1 or $spouse == 2) {
            $reltext = " ";
        }
        if ($spouse == 3) {
            $doublespouse = 1;
        }
        // it's the spouse itself so text should be "X is spouse of Y", not "X is spouse of is identical to Y" !!
        $table = 7;
    } elseif ($data_found["foundX_match"] == 0 and $data_found["foundY_match"] > 0) {  // x is ancestor of y
        $table = 1;
        calculate_ancestor($data_found["foundY_gen"]);
    } elseif ($data_found["foundY_match"] == 0 and $data_found["foundX_match"] > 0) {  // x is descendant of y
        $table = 2;
        calculate_descendant($data_found["foundX_gen"]);
    } elseif ($data_found["foundX_gen"] == 1 and $data_found["foundY_gen"] == 1) {  // x is brother of y

        /*
        elder brother's wife 嫂
        younger brother's wife 弟妇
        elder sister's husband 姊夫
        younger sister's husband 妹夫
        */
        $table = 6;
        if ($sexe == 'm') {
            $reltext = __('brother of ');
            //***Greek ***
            /**In the Greek language, the gender of the second person plays a role in expressing the blood relationship that exists between two people.
             * For example, father:
             * If it is a boy we say (father ΤΟΥ John).
             * If it's a girl, (father ΤΗΣ Helen).
             * The code for the Greek language was modified by Dimitris Fasoulas, for the website www.remen.gr
             */

            /** Στην ελληνική γλώσσα για την διατύπωση της συγγένειας αίματος  που υπάρχει μεταξύ δύο ατόμων παίζει ρόλο το γένος του δεύτερου προσώπου.
             *Για παράδειγμα, πατέρας:
             *Αν είναι αγόρι λέμε (πατέρας ΤΟΥ Γιάννη).
             *Αν είναι κορίτσι, πατέρας ΤΗΣ Ελένης.
             *Ο κώδικας για την ελληνική γλώσσα τροποποιήθηκε από τον Δημητρη Φασούλα, για τον ιστότοπο www.remen.gr
             */

            // *** Ελληνικά αδελφός***
            if ($selected_language == "gr") {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext = 'αδελφός του ';
                    } else {
                        $reltext = 'αδελφός της ';
                    }
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end***            
            if ($selected_language == "cn") {
                if ($sexe2 == "m") {
                    $reltext = '兄弟是';
                } // "A's brother is B"
                else {
                    $reltext = '姊妹是';
                }  // "A's sister is B"
            }
            if ($spouse == 1) {
                $reltext = __('sister-in-law of ');
                $special_spouseX = 1;  //comparing spouse of X with Y
                // *** Greek***
                // *** Ελληνικά κουνιάδα***
                if ($selected_language == "gr") {
                    if ($sexe == "m") {
                        if ($sexe2 == 'm') {
                            $reltext = 'κουνιάδα του ';
                        } else {
                            $reltext = 'κουνιάδα της ';
                        }
                    }
                    // *** Ελληνικά τέλος***
                    // *** Greek end*** 
                }
                if ($selected_language == "cn") {
                    if ($sexe2 == "m") {
                        $reltext = '大爷(小叔)是';
                    } // "A's brother-in-law is B"   (husband's brother)
                    else {
                        $reltext = '大姑(小姑)是';
                    }  // "A's sister-in-law is B" (husband's sister)
                }
            }
            if ($spouse == 2 or $spouse == 3) {
                $reltext =  __('brother-in-law of ');
                $special_spouseY = 1;
                //comparing X with spouse of Y or comparing 2 spouses
                //$special_spouseX flags not to enter "spouse of" for X in display function
                //$special_spouseY flags not to enter "spouse of" for Y in display function
                // *** Greek***
                // *** Ελληνικά κουνιάδος***
                if ($selected_language == "gr" and $spouse == 2) {
                    if ($sexe2 == "m") {
                        $reltext = 'κουνιάδος του ';
                    } else {
                        $reltext = 'κουνιάδος της ';
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end***                 
                if ($selected_language == "cn" and $spouse == 2) {
                    if ($sexe2 == "m") {
                        $reltext = '姊夫(妹夫)是';
                    } // "A's brother-in-law is B" (sister's husband) 
                    else {
                        $reltext = '嫂(弟妇)是';
                    }  // "A's sister-in-law is B" (brother's wife) 
                }
                //***Greek ***                
                // *** Ελληνικά κουνιάδος***                
                if ($selected_language == "gr" and $spouse == 3) {
                    if ($sexe2 == "m") {
                        $reltext = 'κουνιάδος του ';
                    } else {
                        $reltext = 'κουνιάδος της ';
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end*** 
                if ($selected_language == "cn" and $spouse == 3) {
                    if ($sexe2 == "m") {
                        $reltext = '大姑丈(小姑丈)是';
                    } // "A's brother-in-law is B" (husband's sister's husband) 
                    else {
                        $reltext = '大嫂(小嫂)是';
                    }  // "A's sister-in-law is B" (husband's brother's wife)
                }
            }
        } else {
            $reltext = __('sister of ');
            // *** Greek***
            // *** Ελληνικά αδελφή***            
            if ($selected_language == "gr") {
                if ($sexe2 == "m") {
                    $reltext = 'αδελφή του ';
                } else {
                    $reltext = 'αδελφή της ';
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end***             
            if ($selected_language == "cn") {
                if ($sexe2 == "m") {
                    $reltext = '兄弟是';
                } // "A's brother is B"
                else {
                    $reltext = '姊妹是';
                } // "A's sister is B"
            }
            if ($spouse == 1) {
                $reltext =  __('brother-in-law of ');
                $special_spouseX = 1;  //comparing spouse of X with Y
                // *** Greek***
                // *** Ελληνικά κουνιάδος***                
                if ($selected_language == "gr") {
                    if ($sexe2 == "m") {
                        $reltext = 'κουνιάδος του ';
                    } else {
                        $reltext = 'κουνιάδος της ';
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end***           
                if ($selected_language == "cn") {
                    if ($sexe2 == "m") {
                        $reltext = '大舅(小舅)是';
                    } // "A's brother-in-law is B" (wife's brother)
                    else {
                        $reltext = '大姨子(小姨)是';
                    }  // "A's sister-in-law is B" (wife's sister)
                }
            }
            if ($spouse == 2 or $spouse == 3) {
                $reltext =  __('sister-in-law of ');
                $special_spouseY = 1; //comparing X with spouse of Y or comparing 2 spouses
                //$special_spouseX flags not to enter "spouse of" for X in display function
                //$special_spouseY flags not to enter "spouse of" for Y in display function
                // *** Greek***
                // *** Ελληνικά κουνιάδα***                  
                if ($selected_language == "gr" and $spouse == 2) {
                    if ($sexe2 == "m") {
                        $reltext = 'κουνιάδα του ';
                    } else {
                        $reltext = 'κουνιάδα της ';
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end*** 
                if ($selected_language == "cn" and $spouse == 2) {
                    if ($sexe2 == "m") {
                        $reltext = '姊夫(妹夫)是';
                    } // "A's brother-in-law is B"  (sister's husband) 
                    else {
                        $reltext = '嫂(弟妇)是';
                    }  // "A's sister-in-law is B" (brother's wife)
                }
                if ($selected_language == "cn" and $spouse == 3) {
                    if ($sexe2 == "m") {
                        $reltext = '姐夫(妹夫)是';
                    } // "A's brother-in-law is B" (wife's sister's husband) 
                    else {
                        $reltext = '表嫂(表嫂)是';
                    }  // "A's sister-in-law is B" (wife's brother's wife)
                }
            }
        }
    } elseif ($data_found["foundX_gen"] == 1 and $data_found["foundY_gen"] > 1) {  // x is uncle, great-uncle etc of y
        $table = 3;
        calculate_uncles($data_found["foundY_gen"]);
    } elseif ($data_found["foundX_gen"] > 1 and $data_found["foundY_gen"] == 1) {  // x is nephew, great-nephew etc of y
        $table = 4;
        calculate_nephews($data_found["foundX_gen"]);
    } else {  // x and y are cousins of any number (2nd, 3rd etc) and any distance removed (once removed, twice removed etc)
        $table = 5;
        calculate_cousins($data_found["foundX_gen"], $data_found["foundY_gen"]);
    }
}


function spanish_degrees($pers, $text)
{
    $spantext = '';
    if ($pers == 2) {
        $spantext = $text;
    }
    if ($pers == 3) {
        $spantext = 'bis' . $text;
    }
    if ($pers == 4) {
        $spantext = 'tris' . $text;
    }
    if ($pers == 5) {
        $spantext = 'tetra' . $text;
    }
    if ($pers == 6) {
        $spantext = 'penta' . $text;
    }
    if ($pers == 7) {
        $spantext = 'hexa' . $text;
    }
    if ($pers == 8) {
        $spantext = 'hepta' . $text;
    }
    if ($pers == 9) {
        $spantext = 'octa' . $text;
    }
    if ($pers == 10) {
        $spantext = 'nona' . $text;
    }
    if ($pers == 11) {
        $spantext = 'deca' . $text;
    }
    if ($pers == 12) {
        $spantext = 'undeca' . $text;
    }
    if ($pers == 13) {
        $spantext = 'dodeca' . $text;
    }
    if ($pers == 14) {
        $spantext = 'trideca' . $text;
    }
    if ($pers == 15) {
        $spantext = 'tetradeca' . $text;
    }
    if ($pers == 16) {
        $spantext = 'pentadeca' . $text;
    }
    if ($pers == 17) {
        $spantext = 'hexadeca' . $text;
    }
    if ($pers == 18) {
        $spantext = 'heptadeca' . $text;
    }
    if ($pers == 19) {
        $spantext = 'octadeca' . $text;
    }
    if ($pers == 20) {
        $spantext = 'nonadeca' . $text;
    }
    if ($pers == 21) {
        $spantext = 'icosa' . $text;
    }
    if ($pers == 22) {
        $spantext = 'unicosa' . $text;
    }
    if ($pers == 23) {
        $spantext = 'doicosa' . $text;
    }
    if ($pers == 24) {
        $spantext = 'tricosa' . $text;
    }
    if ($pers == 25) {
        $spantext = 'tetricosa' . $text;
    }
    if ($pers == 26) {
        $spantext = 'penticosa' . $text;
    }
    return $spantext;
}


function calculate_ancestor($pers)
{
    global $db_functions, $reltext, $sexe, $sexe2, $spouse, $special_spouseY, $dutchtext, $selected_language, $rel_arrayY;
    global $rel_arrayspouseY;
    global $data_found;

    $ancestortext = '';
    if ($sexe == 'm') {
        $parent = __('father');
    } else {
        $parent = __('mother');
    }
    // *** Greek***
    // *** Ελληνικά πατέρας μητέρα***  
    if ($selected_language == "gr") {
        if ($sexe == 'm') {
            if ($sexe2 == 'm') {
                $parent = 'πατέρας του ';
            } else {
                $parent = 'πατέρας της  ';
            }
        } else {
            if ($sexe2 == 'm') {
                $parent = 'μητέρα του ';
            } else {
                $parent = 'μητέρα της  ';
            }
        }
    }
    // *** Ελληνικά τέλος***
    // *** Greek end***   
    if ($selected_language == "cn") {
        // chinese instead of A is father of B we say: A's son is B
        // therefore we need sex of B instead of A and use son/daughter instead of father/mother
        if ($sexe2 == 'm') {
            $parent = '儿子';  // son
        } else {
            $parent = '女儿';  //daughter
        }
    }

    if ($pers == 1) {
        if ($spouse == 2 or $spouse == 3) {
            $special_spouseY = 1; // prevents "spouse of Y" in output
            // TODO improve code.
            if ($parent == __('father')) {
                $parent = __('father-in-law');
            } else {
                $parent = __('mother-in-law');
            }
            // *** Greek***
            // *** Ελληνικά πεθερός πεθερά***  
            if ($selected_language == "gr") {
                if ($sexe == "m") {
                    if ($sexe2 == "m") {
                        $parent = 'πεθερός του ';
                    } else {
                        $parent = 'πεθερός της ';
                    }
                } else {
                    if ($sexe2 == "m") {
                        $parent = 'πεθερά του ';
                    } else {
                        $parent = 'πεθερά της ';
                    }
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end*** 
            if ($selected_language == "cn") {
                if ($sexe2 == "m") {
                    $parent = '女婿';
                }   // son-in-law
                else {
                    $parent = '儿媳';
                } // daughter-in-law
            }
        }
        if ($selected_language == "gr") {
            $reltext = $parent . ' ';
        } else {
            $reltext = $parent . __(' of ');
        }
        if ($selected_language == "da") {
            $reltext = $parent . ' til ';
        }
        if ($selected_language == "cn") {
            $reltext = $parent . '是';
        }
    } else {
        if ($selected_language == "nl") {
            $ancestortext = dutch_ancestors($pers);
            $reltext = $ancestortext . $parent . __(' of ');
            if ($pers > 4) {
                $gennr = $pers - 2;
                $dutchtext =  "(" . $ancestortext . $parent . " = " . $gennr . __('th') . ' ' . __('great-grand') . $parent . ")";
            }
            // *** Greek***
            // *** Ελληνικά παππούς γιαγιά***
        } elseif ($selected_language == "gr") {
            // TODO improve code
            if ($parent == __('father')) {
                $grparent = 'παππούς';
                $grgrparent = 'προπάππος';
                $gr_postfix = "oς";
            } else {
                $grparent = 'γιαγιά';
                $grgrparent = 'προγιαγιά';
                $gr_postfix = "η";
            }

            $gennr = $pers - 1;
            $degree = $gennr . $gr_postfix;
            if ($pers == 2) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext = $grparent . ' του ';
                    } else {
                        $reltext = $grparent . ' της ';
                    }
                } else {
                    if ($sexe2 == 'm') {
                        $reltext = $grparent . ' του ';
                    } else {
                        $reltext = $grparent . ' της ';
                    }
                }
            } elseif ($pers == 3) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext = $grgrparent . " (" . $degree . " " . $grparent . ') του ';
                    } else {
                        $reltext = $grgrparent . " (" . $degree . " " . $grparent . ') της ';
                    }
                } else {
                    if ($sexe2 == 'm') {
                        $reltext = $grgrparent . " (" . $degree . " " . $grparent . ') του ';
                    } else {
                        $reltext = $grgrparent . " (" . $degree . " " . $grparent . ') της ';
                    }
                }
            } elseif ($pers == 4) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext =  $grgrparent . " (" . $degree . " " . $grparent . ') του ';
                    } else {
                        $reltext = $grgrparent . " (" . $degree . " " . $grparent . ') της ';
                    }
                } else {
                    if ($sexe2 == 'm') {
                        $reltext =  $grgrparent . " (" . $degree . " " . $grparent . ') του ';
                    } else {
                        $reltext =  $grgrparent . " (" . $degree . " " . $grparent . ') της ';
                    }
                }
            } elseif ($pers == 5) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext =  $grgrparent . " (" . $degree . " " . $grparent . ') του ';
                    } else {
                        $reltext =  $grgrparent . " (" . $degree . " " . $grparent . ') της ';
                    }
                } else {
                    if ($sexe2 == 'm') {
                        $reltext =  $grgrparent . " (" . $degree . " " . $grparent . ') του ';
                    } else {
                        $reltext =  $grgrparent . " (" . $degree . " " . $grparent . ') της ';
                    }
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end*** 
        } elseif ($selected_language == "es") {
            // TODO improve code
            if ($parent == __('father')) {
                $grparent = 'abuelo';
                $spanishnumber = "o";
            } else {
                $grparent = 'abuela';
                $spanishnumber = "a";
            }
            $gennr = $pers - 1;
            $degree = $gennr . $spanishnumber . " " . $grparent;
            if ($pers == 2) {
                $reltext = $grparent . __(' of ');
            } elseif ($pers > 2 and $pers < 27) {
                $spantext = spanish_degrees($pers, $grparent); // sets spanish "bis", "tris" etc prefix
                $reltext = $spantext . " (" . $degree . ")" . __(' of ');
            } else {
                $reltext = $degree . __(' of ');
            }
        } elseif ($selected_language == "he") {
            //TODO improve code
            if ($parent == __('father')) {
                $grparent = __('grand');
                $grgrparent = __('great-grand');
            } else {
                $grparent = __('grand');
                $grgrparent = __('great-grand');
            }
            $gennr = $pers - 2;
            if ($pers == 2) {
                $reltext = $grparent . __(' of ');
            } elseif ($pers > 2) {
                $degree = '';
                if ($pers > 3) {
                    $degree = ' דרגה ';
                    $degree .= $gennr;
                }
                $reltext = $grgrparent . $degree . __(' of ');
            }
        } elseif ($selected_language == "fi") {
            if ($pers == 2) {
                $reltext = __('grand') . $parent . __(' of ');
            }
            $gennr = $pers - 1;
            if ($pers >  2) {
                $reltext = $gennr . '. ' . __('grand') . $parent . __(' of ');
            }
        } elseif ($selected_language == "no") {
            if ($pers == 2) {
                $reltext = __('grand') . $parent . __(' of ');
            }
            if ($pers == 3) {
                $reltext = __('great-grand') . $parent . __(' of ');
            }
            if ($pers == 4) {
                $reltext = 'tippolde' . $parent . __(' of ');
            }
            if ($pers == 5) {
                $reltext = 'tipp-tippolde' . $parent . __(' of ');
            }
            $gennr = $pers - 3;
            if ($pers >  5) {
                $reltext = $gennr . "x " . 'tippolde' . $parent . __(' of ');
            }
        } elseif ($selected_language == "da") {

            if ($spouse == "2" or $spouse == "3") { // right person is spouse of Y, not Y
                $relarr = $rel_arrayspouseY;
            } else {
                $relarr = $rel_arrayY;
            }
            if ($pers == 2) {
                // grandfather
                $arrnum = 0;
                $ancsarr = array();
                $count = $data_found["foundY_nr"];
                while ($count != 0) {
                    $parnumber = $count;
                    $ancsarr[$arrnum] = $parnumber;
                    $arrnum++;
                    $count = $relarr[$count][2];
                }
                $persidDb = $db_functions->get_person($relarr[$parnumber][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $reltext = 'far' . $parent . ' til ';
                } else {
                    $reltext = 'mor' . $parent . ' til ';
                }
            }
            if ($pers == 3) {
                $reltext = "olde" . $parent . ' til ';
            }
            if ($pers == 4) {
                $reltext = "tip olde" . $parent . ' til ';
            }
            if ($pers == 5) {
                $reltext = "tip tip olde" . $parent . ' til ';
            }
            if ($pers == 6) {
                $reltext = "tip tip tip olde" . $parent . ' til ';
            }
            $gennr = $pers - 3;
            if ($pers >  6) {
                $reltext = $gennr . ' gange tip olde' . $parent . ' til ';
            }
        }

        // Swedish needs to know if grandparent is related through mother or father - different names there
        // also for great-grandparent and 2nd great-grandparent!!!
        elseif ($selected_language == "sv") {
            if ($spouse == "2" or $spouse == "3") { // right person is spouse of Y, not Y
                $relarr = $rel_arrayspouseY;
            } else {
                $relarr = $rel_arrayY;
            }

            if ($pers > 1) {
                // grandfather
                $arrnum = 0;
                reset($ancsarr);
                $count = $data_found["foundY_nr"];
                while ($count != 0) {
                    $parnumber = $count;
                    $ancsarr[$arrnum] = $parnumber;
                    $arrnum++;
                    $count = $relarr[$count][2];
                }
                $persidDb = $db_functions->get_person($relarr[$parnumber][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $se_grandpar = 'far' . $parent;
                    $direct_par = 'far';
                } else {
                    $se_grandpar = 'mor' . $parent;
                    $direct_par = 'mor';
                }
            }

            if ($pers > 2) {
                // great-grandfather
                $persidDb2 = $db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                $parsexe2 = $persidDb2->pers_sexe;

                if ($parsexe2 == "M") {
                    if ($parsexe == "M") $se_gr_grandpar = 'farfars ' . $parent;
                    else $se_gr_grandpar = 'morfars ' . $parent;
                } else {
                    if ($parsexe == "M") $se_gr_grandpar = 'farmors ' . $parent;
                    else $se_gr_grandpar = 'mormors ' . $parent;
                }
            }

            if ($pers > 3) {
                // 2nd great-grandfather
                $persidDb3 = $db_functions->get_person($relarr[$ancsarr[$arrnum - 3]][0]);
                $parsexe3 = $persidDb3->pers_sexe;
                if ($parsexe3 == "M") {
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") $se_2ndgr_grandpar = 'farfars far' . $parent;
                        else $se_2ndgr_grandpar = 'morfars far' . $parent;
                    } else {
                        if ($parsexe == "M") $se_2ndgr_grandpar = 'farmors far' . $parent;
                        else $se_2ndgr_grandpar = 'mormors far' . $parent;
                    }
                } else {
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") $se_2ndgr_grandpar = 'farfars mor' . $parent;
                        else $se_2ndgr_grandpar = 'morfars mor' . $parent;
                    } else {
                        if ($parsexe == "M") $se_2ndgr_grandpar = 'farmors mor' . $parent;
                        else $se_2ndgr_grandpar = 'mormors mor' . $parent;
                    }
                }
            }

            if ($pers == 2) {
                $reltext = $se_grandpar . __(' of ');
            }
            if ($pers == 3) {
                $reltext = $se_gr_grandpar . __(' of ');
            }
            if ($pers == 4) {
                $reltext = $se_2ndgr_grandpar . __(' of ');
            }
            $gennr = $pers;
            if ($pers >  4) {
                $reltext = $gennr . ':e generations ana på ' . $direct_par . 's sida' . __(' of ');
            }
        } elseif ($selected_language == "cn") {
            if (($sexe2 == 'm' and $spouse != 2 and $spouse != 3) or ($sexe2 == 'f' and ($spouse == 2 or $spouse == 3))) {
                //if($sexe2=="m") { // kwan gives: grandson, great-grandson etc 曾內孫仔  孫子 ???
                if ($pers == 2) {
                    $reltext = '孙子';
                }
                if ($pers == 3) {
                    $reltext = '曾孙';
                }
                if ($pers == 4) {
                    $reltext = '玄孙';
                }
                if ($pers > 4) {
                    $reltext = 'notext';
                } // in Chinese don't display text after 2nd great grandson
            } else {  // granddaughter etc (kwan gives: 曾孫女 曾內孫女  玄孫 ???)
                if ($pers == 2) {
                    $reltext = '孙女';
                }
                if ($pers == 3) {
                    $reltext = '曾孙女';
                }
                if ($pers == 4) {
                    $reltext = '玄孙女';
                }
                if ($pers > 4) {
                    $reltext = 'notext';
                } // in Chinese don't display text after 2nd great granddaughter
            }
            $reltext .= '是';
        } elseif ($selected_language == "fr") {
            if ($pers == 2) {
                $reltext = 'grand-' . $parent . __(' of ');
            }
            if ($pers == 3) {
                $reltext = 'arrière-grand-' . $parent . __(' of ');
            }
            if ($pers == 4) {
                $reltext = 'arrière-arrière-grand-' . $parent . __(' of ');
            }
            if ($pers == 5) {
                $reltext = 'arrière-arrière-arrière-grand-' . $parent . __(' of ');
            }
            if ($pers == 6) {
                $reltext = 'arrière-arrière-arrière-arrière-grand-' . $parent . __(' of ');
            }
            $gennr = $pers + 1;
            if ($pers >  6) {
                $reltext = 'ancêtre ' . $gennr . 'ème génération' . __(' of ');
            }
        } else { // *** Other languages ***
            if ($pers == 2) {
                $reltext = __('grand') . $parent . __(' of ');
            }
            if ($pers == 3) {
                $reltext = __('great-grand') . $parent . __(' of ');
            }
            if ($pers == 4) {
                $reltext = __('2nd') . ' ' . __('great-grand') . $parent . __(' of ');
            }
            if ($pers == 5) {
                $reltext = __('3rd') . ' ' . __('great-grand') . $parent . __(' of ');
            }
            $gennr = $pers - 2;
            if ($pers >  5) {
                $reltext = $gennr . __('th') . ' ' . __('great-grand') . $parent . __(' of ');
            }
        }
    }
}

function dutch_ancestors($gennr)
{
    $ancestortext = '';
    $rest = '';

    if ($gennr > 512) {
        $ancestortext = " Neanthertaler ancestor of ";    //  ;-)
    } else {
        if ($gennr > 256) {
            $ancestortext = "hoog-";
            $gennr -= 256;
            dutch_ancestors($gennr);
        } elseif ($gennr > 128) {
            $ancestortext = "opper-";
            $gennr -= 128;
            dutch_ancestors($gennr);
        } elseif ($gennr > 64) {
            $ancestortext = "aarts-";
            $gennr -= 64;
            dutch_ancestors($gennr);
        } elseif ($gennr > 32) {
            $ancestortext = "voor-";
            $gennr -= 32;
            dutch_ancestors($gennr);
        } elseif ($gennr > 16) {
            $ancestortext = "edel-";
            $gennr -= 16;
            dutch_ancestors($gennr);
        } elseif ($gennr > 8) {
            $ancestortext = "stam-";
            $gennr -= 8;
            dutch_ancestors($gennr);
        } elseif ($gennr > 4) {
            $ancestortext = "oud";
            $gennr -= 4;
            dutch_ancestors($gennr);
        } else {
            if ($gennr == 4) {
                $rest = 'betovergroot';
            }
            if ($gennr == 3) {
                $rest = 'overgroot';
            }
            if ($gennr == 2) {
                $rest = 'groot';
            }
            if ($gennr == 1) {
                $rest = '';
            }
        }
    }
    $ancestortext = $ancestortext . $rest;
    return $ancestortext;
}


function calculate_descendant($pers)
{
    global $db_functions, $reltext, $sexe, $sexe2, $spouse, $special_spouseX, $selected_language, $rel_arrayX, $rel_arrayspouseX;
    global $data_found;

    if ($sexe == 'm') {
        $child = __('son');
    } else {
        $child = __('daughter');
    }
    // *** Greek***
    // *** Ελληνικά γιος κόρη***  
    if ($selected_language == "gr") {
        if ($sexe == 'm') {
            if ($sexe2 == 'm') {
                $child = 'γιος του ';
            } else {
                $child = 'γιος της ';
            }
        } else {
            if ($sexe2 == 'm') {
                $child = 'κόρη του ';
                $child = 'κόρη της ';
            }
        }
        // *** Ελληνικά τέλος***
        // *** Greek end***
    }

    if ($selected_language == "cn") {
        // chinese instead of A is son of B we say: A's father is B
        // therefore we need sex of B instead of A and use father/ mother instead of son/ daughter
        if ($sexe2 == 'm') {
            $child = '父亲';  // father
        } else {
            $child = '母亲';  // mother
        }
    }
    if ($pers == 1) {
        if ($spouse == 1) {
            if ($child == __('son')) {
                $child = __('daughter-in-law');
            } else {
                $child = __('son-in-law');
            }
            $special_spouseX = 1;
            // *** Greek***
            // *** Ελληνικά νύφη γαμπρός***
            if ($selected_language == "gr") {
                if ($sexe == "m") {
                    if ($sexe2 == "m") {
                        $child = 'νύφη του ';
                    } else {
                        $child = 'νύφη της';
                    }
                } else {
                    if ($sexe2 == "m") {
                        $child = 'γαμπρός του ';
                    } else {
                        $child = 'γαμπρός της ';
                    }
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end*** 
            if ($selected_language == "cn") {  // A's father/mother-in-law is B (instead of A is son/daughter-in-law of B)
                if ($sexe2 == "m") {
                    if ($sexe == "f") {
                        $child = '公公';
                    }   // father-in-law called by daughter-in-law  
                    else {
                        $child = '岳父';
                    } // father-in-law called by son-in-law
                } else {
                    if ($sexe == "f") {
                        $child = '婆婆';
                    } // mother-in-law called by daughter-in-law
                    else {
                        $child = '岳母';
                    } // mother-in-law called by son-in-law
                }
            }
        }
        if ($selected_language == "gr") {
            $reltext = $child . '  ';
        } else {
            $reltext = $child . __(' of ');
        }
        if ($selected_language == "cn") {
            $reltext = $child . '是';
        }
        // *** Greek***
        // *** Ελληνικά εγγονός***
    } elseif ($selected_language == "gr") {
        if ($child == __('son')) {
            $grchild = 'εγγονός';
            $grgrchild = 'δισέγγονος';
            $gr_postfix = "ος";
        } else {
            $grchild = 'εγγονή';
            $grgrchild = 'δισέγγονη';
            $gr_postfix = "η";
        }
        $gennr = $pers - 1;
        $degree = $gennr . $gr_postfix . " " . $grchild;
        if ($pers == 2) {
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $grchild . ' του ';
                } else {
                    $reltext = $grchild . ' της ';
                }
            } else {
                if ($sexe2 == 'm') {
                    $reltext = $grchild . ' του ';
                } else {
                    $reltext = $grchild . ' της ';
                }
            }
        } elseif ($pers > 2) {
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $grgrchild . " (" . $degree . ' ) του ';
                } else {
                    $reltext = $grgrchild . " (" . $degree . ' ) της ';
                }
            } else {
                if ($sexe2 == 'm') {
                    $reltext = $grgrchild . " (" . $degree . ' ) του ';
                } else {
                    $reltext =  $grgrchild . " (" . $degree . ' ) της ';
                }
            }
        }
        // *** Ελληνικά τέλος***
        // *** Greek end*** 
    } elseif ($selected_language == "es") {
        if ($child == __('son')) {
            $grchild = 'nieto';
            $spanishnumber = "o";
        } else {
            $grchild = 'nieta';
            $spanishnumber = "a";
        }
        $gennr = $pers - 1;
        $degree = $gennr . $spanishnumber . " " . $grchild;
        if ($pers == 2) {
            $reltext = $grchild . __(' of ');
        } elseif ($pers > 2 and $pers < 27) {
            $spantext = spanish_degrees($pers, $grchild); // sets spanish "bis", "tris" etc prefix
            $reltext = $spantext . " (" . $degree . ")" . __(' of ');
        } else {
            $reltext = $degree . __(' of ');
        }
    } elseif ($selected_language == "he") {
        if ($child == __('son')) {
            $grchild = 'נכד ';
            $grgrchild = 'נין ';
        } else {
            $grchild = 'נכדה ';
            $grgrchild = 'נינה ';
        }
        $gennr = $pers - 2;
        if ($pers == 2) {
            $reltext = $grchild . __(' of ');
        } elseif ($pers > 2) {
            $degree = '';
            if ($pers > 3) {
                $degree = 'דרגה ' . $gennr;
            }
            $reltext = $grgrchild . $degree . __(' of ');
        }
    } elseif ($selected_language == "fi") {
        if ($pers == 2) {
            $reltext = __('grandchild') . __(' of ');
        }
        $gennr = $pers - 1;
        if ($pers >  2) {
            $reltext = $gennr . '. ' . __('grandchild') . __(' of ');
        }
    } elseif ($selected_language == "no") {
        $child = 'barnet'; // barn
        if ($pers == 2) {
            $reltext = 'barnebarnet ' . __(' of ');
        } // barnebarn
        if ($pers == 3) {
            $reltext = __('great-grand') . $child . __(' of ');
        } // olde + barn
        if ($pers == 4) {
            $reltext = 'tippolde' . $child . __(' of ');
        } // tippolde + barn
        if ($pers == 5) {
            $reltext = 'tipp-tippolde' . $child . __(' of ');
        } // tipp-tippolde + barn
        $gennr = $pers - 3;
        if ($pers >  5) {
            $reltext = $gennr . 'x tipp-tippolde' . $child . __(' of ');
        }
    } elseif ($selected_language == "da") {

        if ($spouse == "1" or $spouse == "3") { // right person is spouse of Y, not Y
            $relarr = $rel_arrayspouseX;
        } else {
            $relarr = $rel_arrayX;
        }

        if ($pers == 2) {
            // grandchild
            $arrnum = 0;
            $ancsarr = array();
            $count = $data_found["foundX_nr"];
            while ($count != 0) {
                $parnumber = $count;
                $ancsarr[$arrnum] = $parnumber;
                $arrnum++;
                $count = $relarr[$count][2];
            }
            $persidDb = $db_functions->get_person($relarr[$data_found["foundX_nr"]][0]);
            $parsexe = $persidDb->pers_sexe;
            if ($parsexe == 'M') {
                $reltext = 'sønne' . $child . __(' of ');
            } else {
                $reltext = 'datter' . $child . __(' of ');
            }
        }

        if ($pers == 3) {
            $reltext = 'olde' . $child . __(' of ');
        } // oldeson oldedatter
        if ($pers == 4) {
            $reltext = 'tip olde' . $child . __(' of ');
        } // tip oldeson
        if ($pers == 5) {
            $reltext = 'tip tip olde' . $child . __(' of ');
        } // tip tip oldeson
        if ($pers == 6) {
            $reltext = 'tip tip tip olde' . $child . __(' of ');
        } // tip tip tip oldeson
        $gennr = $pers - 3;
        if ($pers >  6) {
            $reltext = $gennr . ' gange tip olde' . $child . __(' of ');
        }
    }
    // Swedish needs to know if grandchild is related through son or daughter - different names there
    // also for great-grandchild and 2nd great-grandchild!!!
    elseif ($selected_language == "sv") {

        if ($spouse == "1" or $spouse == "3") { // right person is spouse of Y, not Y
            $relarr = $rel_arrayspouseX;
        } else {
            $relarr = $rel_arrayX;
        }

        if ($pers > 1) {
            // grandchild
            $arrnum = 0;
            reset($ancsarr);
            $count = $data_found["foundX_nr"];
            while ($count != 0) {
                $parnumber = $count;
                $ancsarr[$arrnum] = $parnumber;
                $arrnum++;
                //$count=$rel_arrayX[$count][2];
                $count = $relarr[$count][2];
            }
            $persidDb = $db_functions->get_person($relarr[$data_found["foundX_nr"]][0]);
            $parsexe = $persidDb->pers_sexe;
            if ($parsexe == 'M') {
                $se_grandch = 'son' . $child;
                $direct_ch = 'son';
            } else {
                $se_grandch = 'dotter' . $child;
                $direct_ch = 'dotter';
            }
        }

        if ($pers > 2) {
            // great-grandchild
            $persidDb2 = $db_functions->get_person($relarr[$ancsarr[1]][0]);
            $parsexe2 = $persidDb2->pers_sexe;

            if ($parsexe2 == "M") {
                if ($parsexe == "M") $se_gr_grandch = 'sonsons ' . $child;
                else $se_gr_grandch = 'dottersons ' . $child;
            } else {
                if ($parsexe == "M") $se_gr_grandch = 'sondotters ' . $child;
                else $se_gr_grandch = 'dotterdotters ' . $child;
            }
        }

        if ($pers > 3) {
            // 2nd great-grandchild
            $persidDb3 = $db_functions->get_person($relarr[$ancsarr[2]][0]);
            $parsexe3 = $persidDb3->pers_sexe;
            if ($parsexe3 == "M") {
                if ($parsexe2 == "M") {
                    if ($parsexe == "M") $se_2ndgr_grandch = 'sonsons son' . $child;
                    else $se_2ndgr_grandch = 'dottersons son' . $child;
                } else {
                    if ($parsexe == "M") $se_2ndgr_grandch = 'sondotters son' . $child;
                    else $se_2ndgr_grandch = 'dotterdotters son' . $child;
                }
            } else {
                if ($parsexe2 == "M") {
                    if ($parsexe == "M") $se_2ndgr_grandch = 'sonsons dotter' . $child;
                    else $se_2ndgr_grandch = 'dottersons dotter' . $child;
                } else {
                    if ($parsexe == "M") $se_2ndgr_grandch = 'sondotters dotter' . $child;
                    else $se_2ndgr_grandch = 'dotterdotters dotter' . $child;
                }
            }
        }

        if ($pers == 2) {
            $reltext = $se_grandch . __(' of ');
        }
        if ($pers == 3) {
            $reltext = $se_gr_grandch . __(' of ');
        }
        if ($pers == 4) {
            $reltext = $se_2ndgr_grandch . __(' of ');
        }
        $gennr = $pers;
        if ($pers >  4) {
            $reltext = $gennr . ':e generations barn' . __(' of ');
        }
    } elseif ($selected_language == "cn") {    // instead of A is grandson of B we say: A's granfather is B
        if (($sexe2 == 'm' and $spouse != 2 and $spouse != 3) or ($sexe2 == 'f' and ($spouse == 2 or $spouse == 3))) {
            //if($sexe2=="m") { // grandfather, great-grandfather etc
            if ($pers == 2) {
                $reltext = '祖父';
            }
            if ($pers == 3) {
                $reltext = '曾祖父';
            }
            if ($pers == 4) {
                $reltext = '高祖父';
            }
            if ($pers > 4) {
                $reltext = 'notext';
            } // in Chinese don't display text after 2nd great grandfather
        } else {  // grandmother etc
            if ($pers == 2) {
                $reltext = '祖母';
            }
            if ($pers == 3) {
                $reltext = '曾祖母';
            }
            if ($pers == 4) {
                $reltext = '高祖母';
            }
            if ($pers > 4) {
                $reltext = 'notext';
            } // in Chinese don't display text after 2nd great grandmother
        }
        $reltext .= '是';
    } elseif ($selected_language == "fr") {
        if ($sexe == 'm') {
            $gend = "";
        } else {
            $gend = "e";
        }
        if ($pers == 2) {
            $reltext = 'petit' . $gend . '-' . $child . __(' of ');
        }
        if ($pers == 3) {
            $reltext = 'arrière-petit' . $gend . '-' . $child . __(' of ');
        }
        if ($pers == 4) {
            $reltext = 'arrière-arrière-petit' . $gend . '-' . $child . __(' of ');
        }
        if ($pers == 5) {
            $reltext = 'arrière-arrière-arrière-petit' . $gend . '-' . $child . __(' of ');
        }
        $gennr = $pers - 2;
        if ($pers >  5) {
            $reltext = 'arrière (' . ($pers - 2) . ' fois) petit' . $gend . '-' . $child . __(' of ');
        }
    } else {
        if ($pers == 2) {
            $reltext = __('grand') . $child . __(' of ');
        }
        if ($pers == 3) {
            $reltext = __('great-grand') . $child . __(' of ');
        }
        if ($pers == 4) {
            $reltext = __('2nd') . ' ' . __('great-grand') . $child . __(' of ');
        }
        if ($pers == 5) {
            $reltext = __('3rd') . ' ' . __('great-grand') . $child . __(' of ');
        }
        $gennr = $pers - 2;
        if ($pers >  5) {
            $reltext = $gennr . __('th') . ' ' . __('great-grand') . $child . __(' of ');
        }
    }
}


function calculate_nephews($generX)
{ // handed generations x is removed from common ancestor
    global $db_functions, $reltext, $sexe, $sexe2, $selected_language, $rel_arrayX, $rel_arrayspouseX, $spouse;
    global $reltext_nor, $reltext_nor2; // for Norwegian and Danish
    global $data_found;

    // *** Greek***
    // *** Ελληνικά***
    if ($selected_language == "gr") {
        if ($sexe == "m") {
            $neph = 'ανιψιος';
            $gr_postfix = "ος ";
            $grson = 'εγγονός';
            $grgrson = 'δισέγγονος';
        } else {
            $neph = 'ανιψιά';
            $gr_postfix = "η ";
            $grson = 'εγγονή';
            $grgrson = 'δισέγγονη';
        }
        $gendiff = $generX - 1;
        $gennr = $gendiff - 1;
        $degree = $grson . " " . $gennr . $gr_postfix;
        if ($gendiff == 1) {
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $neph . ' του ';
                } else {
                    $reltext = $neph . ' της ';
                }
            } else {
                if ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $reltext = $neph . ' του ';
                    } else {
                        $reltext = $neph . ' της ';
                    }
                }
            }
        } elseif ($gendiff == 2) {
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $neph . " " . $grson . ' του ';
                } else {
                    $reltext = $neph . " " . $grson . ' της ';
                }
            } else {
                if ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $reltext = $neph . " " . $grson . ' του ';
                    } else {
                        $reltext = $neph . " " . $grson . ' της ';
                    }
                }
            }
        } else {
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $neph . " " . $grgrson . ' του ';
                } else {
                    $reltext = $neph . " " . $grgrson . ' της ';
                }
            } else {
                if ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $reltext = $neph . " " . $grgrson . ' του ';
                    } else {
                        $reltext = $neph . " " . $grgrson . ' της ';
                    }
                }
            }
        }
        // *** Ελληνικά τέλος***
        // *** Greek end*** 
    } elseif ($selected_language == "es") {
        if ($sexe == "m") {
            $neph = __('nephew');
            $span_postfix = "o ";
            $grson = 'nieto';
        } else {
            $neph = __('niece');
            $span_postfix = "a ";
            $grson = 'nieta';
        }
        $gendiff = $generX - 1;
        $gennr = $gendiff - 1;
        $degree = $grson . " " . $gennr . $span_postfix;
        if ($gendiff == 1) {
            $reltext = $neph . __(' of ');
        } elseif ($gendiff > 1 and $gendiff < 27) {
            $spantext = spanish_degrees($gendiff, $grson);
            $reltext = $neph . " " . $spantext . __(' of ');
        } else {
            $reltext = $neph . " " . $degree;
        }
    } elseif ($selected_language == "he") {
        if ($sexe == 'm') {
            $nephniece = __('nephew');
        } else {
            $nephniece = __('niece');
        }
        $gendiff = $generX - 1;
        if ($gendiff == 1) {
            $reltext = $nephniece . __(' of ');
        } elseif ($gendiff > 1) {
            $degree = ' דרגה ' . $gendiff;
            $reltext = $nephniece . $degree . __(' of ');
        }
    } elseif ($selected_language == "fi") {
        if ($sexe == 'm') {
            $nephniece = __('nephew');
        } else {
            $nephniece = __('niece');
        }
        if ($generX == 2) {
            $reltext = $nephniece . __(' of ');
        }
        if ($generX == 3) {
            $reltext = __('grand') . $nephniece . __(' of ');
        }
        $gennr = $generX - 2;
        if ($generX >  3) {
            $reltext = $gennr . '. ' . __('grand') . $nephniece . __(' of ');
        }
    } elseif ($selected_language == "no") {
        if ($sexe == 'm') {
            $nephniece = __('nephew');
        } else {
            $nephniece = __('niece');
        }
        $reltext_nor = '';
        $reltext_nor2 = '';
        if ($generX > 3) {
            $reltext_nor = "s " . substr('søskenet', 0, -2);  // for: A er oldebarnet av Bs søsken

            $reltext_nor2 = 'søskenet' .
                __(' of '); // for: A er oldebarnet av søskenet av mannen til B
        }
        if ($generX == 2) {
            $reltext = $nephniece . __(' of ');
        }
        if ($generX == 3) {
            $reltext = 'grand' . $nephniece . __(' of ');
        }
        if ($generX == 4) {
            $reltext = __('great-grand') . ' barnet' . __(' of ');
        }
        if ($generX == 5) {
            $reltext = 'tippolde barnet' . __(' of ');
        }
        if ($generX == 6) {
            $reltext = 'tipp-tippolde barnet' . __(' of ');
        }
        $gennr = $generX - 4;
        if ($generX >  6) {
            $reltext = $gennr . 'x tippolde barnet' . __(' of ');
        }
    } elseif ($selected_language == "da") {
        if ($sexe == 'm') {
            $nephniece = __('nephew');
        } else {
            $nephniece = __('niece');
        }
        $reltext_nor = '';
        $reltext_nor2 = '';
        if ($generX > 3) {
            $reltext_nor = "s søskende";  // for: A er oldebarn af Bs søskende

            $reltext_nor2 = 'søskende' .
                __(' of '); // for: A er oldebarn af søskende af ..... til B
        }
        if ($generX == 2) {
            $reltext = $nephniece . __(' of ');
        }
        if ($generX == 3) {
            $reltext = 'grand' . $nephniece . __(' of ');
        }
        if ($generX == 4) {
            $reltext = 'oldebarn' . __(' of ');
        }
        if ($generX == 5) {
            $reltext = 'tip oldebarn' . __(' of ');
        }
        if ($generX == 6) {
            $reltext = 'tip tip oldebarn' . __(' of ');
        }
        if ($generX == 7) {
            $reltext = 'tip tip tip oldebarn' . __(' of ');
        }
        $gennr = $generX - 4;
        if ($generX >  7) {
            $reltext = $gennr . ' gange tip oldebarn' . __(' of ');
        }
    } elseif ($selected_language == "nl") {
        if ($sexe == 'm') {
            $nephniece = __('nephew');
        } else {
            $nephniece = __('niece');
        }
        // in Dutch we use the __('3rd [COUSIN]') variables, that work for nephews as well
        if ($generX == 2) {
            $reltext = $nephniece . __(' of ');
        }
        if ($generX == 3) {
            $reltext = __('2nd [COUSIN]') . $nephniece . __(' of ');
        }
        if ($generX == 4) {
            $reltext = __('3rd [COUSIN]') . $nephniece . __(' of ');
        }
        if ($generX == 5) {
            $reltext = __('2nd') . ' ' . __('3rd [COUSIN]') . $nephniece . __(' of ');
        }
        if ($generX == 6) {
            $reltext = __('3rd') . ' ' . __('3rd [COUSIN]') . $nephniece . __(' of ');
        }
        $gennr = $generX - 3;
        if ($generX >  6) {
            $reltext = $gennr . __('th ') . __('3rd [COUSIN]') . $nephniece . __(' of ');
        }
    } elseif ($selected_language == "sv") {
        // Swedish needs to know if nephew/niece is related through brother or sister - different names there
        // also for grandnephew!!!

        if ($spouse == "1" or $spouse == "3") { // right person is spouse of Y, not Y
            $relarr = $rel_arrayspouseX;
        } else {
            $relarr = $rel_arrayX;
        }

        if ($sexe == 'm') {
            $nephniece = "son";
        } else {
            $nephniece = "dotter";
        }
        if ($generX > 1) {
            // niece/nephew
            $arrnum = 0;
            reset($ancsarr);
            $count = $data_found["foundX_nr"];
            while ($count != 0) {
                $parnumber = $count;
                $ancsarr[$arrnum] = $parnumber;
                $arrnum++;
                $count = $relarr[$count][2];
            }
            $persidDb = $db_functions->get_person($relarr[$parnumber][0]);
            $parsexe = $persidDb->pers_sexe;
            if ($parsexe == 'M') {
                $se_nephniece = 'bror' . $nephniece;
            } else {
                $se_nephniece = 'syster' . $nephniece;
            }
        }
        if ($generX == 3) {
            // grandniece/nephew
            $persidDb2 = $db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
            $parsexe2 = $persidDb2->pers_sexe;
            if ($parsexe2 == "M") {
                if ($parsexe == "M") $se_gr_nephniece = 'brors son' . $nephniece;
                else $se_gr_nephniece = 'brors dotter' . $nephniece;
            } else {
                if ($parsexe == "M") $se_gr_nephniece = 'systers son' . $nephniece;
                else $se_gr_nephniece = 'systers dotter' . $nephniece;
            }
        }
        if ($generX == 2) {
            $reltext = $se_nephniece . __(' of ');
        }
        if ($generX == 3) {
            $reltext = $se_gr_nephniece . __(' of ');
        }
        $gennr = $generX - 1;
        if ($generX >  3) {
            $persidDb = $db_functions->get_person($rel_arrayX[$data_found["foundX_nr"]][0]);
            $parsexe = $persidDb->pers_sexe;
            if ($parsexe == 'M') {
                $se_sib = "bror";
            } else {
                $se_sib = "syster";
            }
            $reltext = $se_sib . 's ' . $gennr . ':e generations barn' . __(' of ');
        }
    } elseif ($selected_language == "cn") {
        // Used: http://www.kwanfamily.info/culture/familytitles_table.php
        if ($spouse == "1") { // left person is spouse of X, not X
            $relarrX = $rel_arrayspouseX;
        } else {
            $relarrX = $rel_arrayX;
        }
        $arrnumX = 0;
        if (isset($ancsarrX)) reset($ancsarrX);
        $count = $data_found["foundX_nr"];
        while ($count != 0) {
            $parnumberX = $count;
            $ancsarrX[$arrnumX] = $parnumberX;
            $arrnumX++;
            $count = $relarrX[$count][2];
        }
        $persidDbX = $db_functions->get_person($relarrX[$parnumberX][0]);
        $parsexeX = $persidDbX->pers_sexe;
        if ($parsexeX == 'M') { // uncle/aunt from father's side
            if (($sexe2 == "m" and $spouse != 2 and $spouse != 3) or ($sexe2 == "f" and ($spouse == 2 or $spouse == 3))) {
                $reltext = '伯父(叔父)是';  // uncle - brother of father
            } else {
                $reltext = '姑母是';  // aunt - sister of father
            }
        } else { // uncle/aunt from mother's side
            if (($sexe2 == "m" and $spouse != 2 and $spouse != 3) or ($sexe2 == "f" and ($spouse == 2 or $spouse == 3))) {
                $reltext = '舅父是';  // uncle - brother of mother
            } else {
                $reltext = '姨母(姨)是';  // aunt - sister of mother
            }
        }

        /*		
        if(($sexe2=='m' AND $spouse!=2 AND $spouse!=3) OR ($sexe2=='f' AND ($spouse==2 OR $spouse==3))) {  
            $nephniece = '叔伯是';  // A's uncle is B
        }
        else {
            $nephniece = '婶娘是';  //  A's aunt is B
        }
*/
        if ($generX == 2) {
        }
        if ($generX > 2) {
            $reltext = "notext";
        }  // suppress text - "granduncle" etc is not (yet) supported in Chinese
    } elseif ($selected_language == "fr") {
        if ($sexe == 'm') {
            $nephniece = __('nephew');
            $gend = "";
        } else {
            $nephniece = __('niece');
            $gend = "e";
        }
        if ($generX == 2) {
            $reltext = $nephniece . __(' of ');
        }
        if ($generX == 3) {
            $reltext = 'petit' . $gend . '-' . $nephniece . __(' of ');
        }
        if ($generX == 4) {
            $reltext = 'arrière-petit' . $gend . '-' . $nephniece . __(' of ');
        }
        if ($generX == 5) {
            $reltext = 'arrière-arrière-petit' . $gend . '-' . $nephniece . __(' of ');
        }
        if ($generX == 6) {
            $reltext = 'arrière-arrière-arrière-petit' . $gend . '-' . $nephniece . __(' of ');
        }
        $gennr = $generX - 3;
        if ($generX >  6) {
            $reltext = 'arrière (' . $gennr . ' fois) petit' . $gend . '-' . $nephniece . __(' of ');
        }
    } else {
        if ($sexe == 'm') {
            $nephniece = __('nephew');
        } else {
            $nephniece = __('niece');
        }
        if ($generX == 2) {
            $reltext = $nephniece . __(' of ');
        }
        if ($generX == 3) {
            $reltext = __('grand') . $nephniece . __(' of ');
        }
        if ($generX == 4) {
            $reltext = __('great-grand') . $nephniece . __(' of ');
        }
        if ($generX == 5) {
            $reltext = __('2nd') . ' ' . __('great-grand') . $nephniece . __(' of ');
        }
        if ($generX == 6) {
            $reltext = __('3rd') . ' ' . __('great-grand') . $nephniece . __(' of ');
        }
        $gennr = $generX - 3;
        if ($generX >  6) {
            $reltext = $gennr . __('th ') . __('great-grand') . $nephniece . __(' of ');
        }
    }
}

function calculate_uncles($generY)
{ // handed generations y is removed from common ancestor
    global $db_functions, $reltext,  $sexe, $sexe2, $dutchtext, $selected_language, $rel_arrayspouseY, $spouse;
    global $rel_arrayY;  // only for Finnish paragraph
    global $reltext_nor, $reltext_nor2; // for Norwegian and Danish
    global $data_found;

    if ($sexe == 'm') {
        $uncleaunt = __('uncle');
        if ($selected_language == "cn") {  // A's nephew/niece is B
            // Used: http://www.kwanfamily.info/culture/familytitles_table.php
            // Other translations (not used):  dongshan: nephew: 侄子是  niece 侄女是
            if ($spouse == "2" or $spouse == "3") { // right person is spouse of Y, not Y
                $relarrY = $rel_arrayspouseY;
            } else {
                $relarrY = $rel_arrayY;
            }
            $arrnumY = 0;
            if (isset($ancsarrY)) reset($ancsarrY);
            $count = $data_found["foundY_nr"];
            while ($count != 0) {
                $parnumberY = $count;
                $ancsarrY[$arrnumY] = $parnumberY;
                $arrnumY++;
                $count = $relarrY[$count][2];
            }
            $persidDbY = $db_functions->get_person($relarrY[$parnumberY][0]);
            $parsexeY = $persidDbY->pers_sexe;
            if ($parsexeY == "M") { // is child of brother
                if (($sexe2 == 'm' and $spouse != 2 and $spouse != 3) or ($sexe2 == 'f' and ($spouse == 2 or $spouse == 3))) {
                    $uncleaunt = '姪子是';
                } else {
                    $uncleaunt = '姪女是';
                }
            } else { // is child of sister - term depends also on sex of A
                if (($sexe2 == 'm' and $spouse != 2 and $spouse != 3) or ($sexe2 == 'f' and ($spouse == 2 or $spouse == 3))) {
                    if ($sexe == "m") $uncleaunt = '外甥是'; // son of sister (A is male)
                    else $uncleaunt = '姨甥是'; // son of sister (A is female)
                } else {
                    if ($sexe == "m") $uncleaunt = '外甥女是'; // daughter of sister (A is male)
                    else $uncleaunt = '姨甥女是';    // daughter of sister (A is female)
                }
            }
        }

        // Finnish needs to know if uncle is related through mother or father - different names there
        if ($selected_language == "fi") {
            $count = $data_found["foundY_nr"];
            while ($count != 0) {
                $parnumber = $count;
                $count = $rel_arrayY[$count][2];
            }
            $persidDb = $db_functions->get_person($rel_arrayY[$parnumber][0]);
            $parsexe = $persidDb->pers_sexe;
            if ($parsexe == 'M') {
                $uncleaunt = 'setä';
            } else {
                $uncleaunt = 'eno';
            }
        }

        // Swedish needs to know if uncle is related through mother or father - different names there
        // also for granduncle and great-granduncle!!!
        if ($selected_language == "sv") {

            if ($spouse == "2" or $spouse == "3") { // right person is spouse of Y, not Y
                $relarr = $rel_arrayspouseY;
            } else {
                $relarr = $rel_arrayY;
            }

            $se_sibling = "bror"; // used for gr_gr_granduncle and more "4:e gen anas bror"
            // uncle
            $arrnum = 0;
            reset($ancsarr);
            $count = $data_found["foundY_nr"];
            while ($count != 0) {
                $parnumber = $count;
                $ancsarr[$arrnum] = $parnumber;
                $arrnum++;
                $count = $relarr[$count][2];
            }
            $persidDb = $db_functions->get_person($relarr[$parnumber][0]);
            $parsexe = $persidDb->pers_sexe;
            if ($parsexe == 'M') {
                $uncleaunt = 'farbror';
            } else {
                $uncleaunt = 'morbror';
            }

            if ($generY > 2) {
                // granduncle
                $persidDb2 = $db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                $parsexe2 = $persidDb2->pers_sexe;
                if ($parsexe2 == "M") {
                    if ($parsexe == "M") $se_granduncleaunt = 'fars farbror';
                    else $se_granduncleaunt = 'mors farbror';
                } else {
                    if ($parsexe == "M") $se_granduncleaunt = 'fars morbror';
                    else $se_granduncleaunt = 'mors morbror';
                }
            }

            if ($generY > 3) {
                // great-granduncle
                $persidDb3 = $db_functions->get_person($relarr[$ancsarr[$arrnum - 3]][0]);
                $parsexe3 = $persidDb3->pers_sexe;
                if ($parsexe3 == "M") {
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") $se_gr_granduncleaunt = 'farfars farbror';
                        else $se_gr_granduncleaunt = 'morfars farbror';
                    } else {
                        if ($parsexe == "M") $se_gr_granduncleaunt = 'farmors farbror';
                        else $se_gr_granduncleaunt = 'mormors farbror';
                    }
                } else {
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") $se_gr_granduncleaunt = 'farfars morbror';
                        else $se_gr_granduncleaunt = 'morfars morbror';
                    } else {
                        if ($parsexe == "M") $se_gr_granduncleaunt = 'farmors morbror';
                        else $se_gr_granduncleaunt = 'mormors morbror';
                    }
                }
            }
        }
    } else {
        $uncleaunt = __('aunt');
        if ($selected_language == "cn") {
            if ($sexe2 == "m") {
                $uncleaunt = '侄子是';
            } // "A's nephew is B"
            else {
                $uncleaunt = '侄女是';
            } // "A's niece is B"
        }
        // Swedish needs to know if aunt is related through mother or father - different names there
        // also for grandaunt and great-grandaunt!!!
        if ($selected_language == "sv") {

            if ($spouse == "2" or $spouse == "3") { // right person is spouse of Y, not Y
                $relarr = $rel_arrayspouseY;
            } else {
                $relarr = $rel_arrayY;
            }

            $se_sibling = "syster"; // used for gr_gr_grandaunt and more "4:e gen anas syster"
            // aunt
            $arrnum = 0;
            reset($ancsarr);
            $count = $data_found["foundY_nr"];
            while ($count != 0) {
                $parnumber = $count;
                $ancsarr[$arrnum] = $parnumber;
                $arrnum++;
                $count = $relarr[$count][2];
            }
            $persidDb = $db_functions->get_person($relarr[$parnumber][0]);
            $parsexe = $persidDb->pers_sexe;
            if ($parsexe == 'M') {
                $uncleaunt = 'faster';
            } else {
                $uncleaunt = 'moster';
            }

            if ($generY > 2) {
                // grandaunt
                $persidDb2 = $db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                $parsexe2 = $persidDb2->pers_sexe;
                if ($parsexe2 == "M") {
                    if ($parsexe == "M") $se_granduncleaunt = 'fars faster';
                    else $se_granduncleaunt = 'mors faster';
                } else {
                    if ($parsexe == "M") $se_granduncleaunt = 'fars moster';
                    else $se_granduncleaunt = 'mors moster';
                }
            }

            if ($generY > 3) {
                // great-grandaunt
                $persidDb3 = $db_functions->get_person($relarr[$ancsarr[$arrnum - 3]][0]);
                $parsexe3 = $persidDb3->pers_sexe;
                if ($parsexe3 == "M") {
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") $se_gr_granduncleaunt = 'farfars faster';
                        else $se_gr_granduncleaunt = 'morfars faster';
                    } else {
                        if ($parsexe == "M") $se_gr_granduncleaunt = 'farmors faster';
                        else $se_gr_granduncleaunt = 'mormors faster';
                    }
                } else {
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") $se_gr_granduncleaunt = 'farfars moster';
                        else $se_gr_granduncleaunt = 'morfars moster';
                    } else {
                        if ($parsexe == "M") $se_gr_granduncleaunt = 'farmors moster';
                        else $se_gr_granduncleaunt = 'mormors moster';
                    }
                }
            }
        }
    }

    if ($selected_language == "nl") {
        $ancestortext = dutch_ancestors($generY - 1);
        $reltext = $ancestortext . $uncleaunt . __(' of ');
        if ($generY > 4) {
            $gennr = $generY - 3;
            $dutchtext =  "(" . $ancestortext . $uncleaunt . " = " . $gennr . __('th') . ' ' . __('great-grand') . $uncleaunt . ")";
        }
        // *** Greek***
        // *** Ελληνικά θείος***
    } elseif ($selected_language == "gr") {
        // TODO improve code
        if ($sexe == "m") {
            $uncle = 'θείος';
            $gr_postfix = "ος ";
            $gran = 'παππούς';
            $grgrparent = 'προπάππος';
        } else {
            $uncle = 'θεία';
            $gr_postfix = "η ";
            $gran = 'γιαγιά';
            $grgrparent = 'προγιαγιά';
        }
        $gendiff = $generY - 1;
        $gennr = $gendiff - 1;
        $degree = $gran . " " . $gennr . $gr_postfix;
        if ($gendiff == 1) {
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $uncle . ' του ';
                } else {
                    $reltext = $uncle . ' της ';
                }
            } else {
                if ($sexe2 == 'm') {
                    $reltext = $uncle . ' του ';
                } else {
                    $reltext =  $uncle . ' της ';
                }
            }
        } elseif ($gendiff == 2) {
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $uncle . " " . $gran . ' του ';
                } else {
                    $reltext = $uncle . " " . $gran . ' της ';
                }
            } else {
                if ($sexe2 == 'm') {
                    $reltext = $uncle . " " . $gran . ' του ';
                } else {
                    $reltext = $uncle . " " . $gran . ' της ';
                }
            }
        } elseif ($gendiff > 2) {

            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $uncle . " " . $grgrparent . ' του ';
                } else {
                    $reltext = $uncle . " " . $grgrparent . ' της ';
                }
            } else {
                if ($sexe2 == 'm') {
                    $reltext = $uncle . " " . $grgrparent . ' του ';
                } else {
                    $reltext = $uncle . " " . $grgrparent . ' της ';
                }
            }
        }
        // *** Ελληνικά τέλος***
        // *** Greek end*** 
    } elseif ($selected_language == "es") {
        if ($sexe == "m") {
            $uncle = __('uncle');
            $span_postfix = "o ";
            $gran = 'abuelo';
        } else {
            $uncle = __('aunt');
            $span_postfix = "a ";
            $gran = 'abuela';
        }
        $gendiff = $generY - 1;
        $gennr = $gendiff - 1;
        $degree = $gran . " " . $gennr . $span_postfix;
        if ($gendiff == 1) {
            $reltext = $uncle . __(' of ');
        } elseif ($gendiff > 1 and $gendiff < 27) {
            $spantext = spanish_degrees($gendiff, $gran);
            $reltext = $uncle . " " . $spantext . __(' of ');
        } else {
            $reltext = $uncle . " " . $degree;
        }
    } elseif ($selected_language == "he") {
        $gendiff = $generY - 1;
        if ($gendiff == 1) {
            $reltext = $uncleaunt . __(' of ');
        } elseif ($gendiff > 1) {
            $degree = ' דרגה ' . $gendiff;
            $reltext = $uncleaunt . $degree . __(' of ');
        }
    } elseif ($selected_language == "fi") {
        if ($generY == 2) {
            $reltext = $uncleaunt . __(' of ');
        }
        if ($generY == 3) {
            $reltext = __('grand') . $uncleaunt . __(' of ');
        }
        $gennr = $generY - 2;
        if ($generY >  3) {
            $reltext = $gennr . __('th') . ' ' . __('grand') . $uncleaunt . __(' of ');
        }
    } elseif ($selected_language == "sv") {
        if ($generY == 2) {
            $reltext = $uncleaunt . __(' of ');
        }
        if ($generY == 3) {
            $reltext = $se_granduncleaunt . __(' of ');
        }
        if ($generY == 4) {
            $reltext = $se_gr_granduncleaunt . __(' of ');
        }
        $gennr = $generY - 1;
        if ($generY >  4) {
            $reltext = $gennr . ':e gen anas ' . $se_sibling . __(' of ');
        }
    } elseif ($selected_language == "no") {
        $temptext = '';
        $reltext_nor = '';
        $reltext_nor2 = '';
        if ($generY == 2) {
            $reltext = $uncleaunt . __(' of ');
        }
        if ($generY == 3) {
            $reltext = 'grand' . $uncleaunt . __(' of ');
        }
        if ($generY > 3) {
            if ($uncleaunt == __('uncle')) {
                $reltext = __('brother of ');
            } else {
                $reltext = __('sister of ');
            }
        }
        if ($generY == 4) {
            $temptext = 'oldeforelderen';
        }
        if ($generY == 5) {
            $temptext = 'tippoldeforelderen';
        }
        if ($generY == 6) {
            $temptext = 'tipp-tippoldeforelderen';
        }
        $gennr = $generY - 4;
        if ($generY >  6) {
            $temptext = $gennr . 'x tippoldeforelderen';
        }
        if ($temptext != '') {
            $reltext_nor = "s " . substr($temptext, 0, -2);
            $reltext_nor2 = $temptext . __(' of ');
        }
    } elseif ($selected_language == "da") {
        $temptext = '';
        $reltext_nor = '';
        $reltext_nor2 = '';
        if ($generY == 2) {
            $reltext = $uncleaunt . ' til ';
        }
        if ($generY == 3) {
            $reltext = 'grand' . $uncleaunt . ' til ';
        }
        if ($generY > 3) {
            if ($uncleaunt == __('uncle')) {
                $reltext = __('brother of ');
            } else {
                $reltext = __('sister of ');
            }
        }
        if ($generY == 4) {
            $temptext = 'oldeforældre';
        }
        if ($generY == 5) {
            $temptext = 'tip oldeforældre';
        }
        if ($generY == 6) {
            $temptext = 'tip tip oldeforældre';
        }
        if ($generY == 7) {
            $temptext = 'tip tip tip oldeforældre';
        }
        $gennr = $generY - 4;
        if ($generY >  7) {
            $temptext = $gennr . ' gange tip oldeforældre';
        }
        if ($temptext != '') {
            $reltext_nor = "s " . $temptext;
            $reltext_nor2 = $temptext . ' til ';
        }
    } elseif ($selected_language == "cn") {
        if ($generY == 2) {
            $reltext = $uncleaunt;
        }
        if ($generY > 2) {
            $reltext = "notext";
        }
    } elseif ($selected_language == "fr") {
        if ($generY == 2) {
            $reltext = $uncleaunt . __(' of ');
        }
        if ($generY == 3) {
            $reltext = 'grand-' . $uncleaunt . __(' of ');
        }
        if ($generY == 4) {
            $reltext = 'arrière-grand-' . $uncleaunt . __(' of ');
        }
        if ($generY == 5) {
            $reltext = 'arrière-arrière-grand-' . $uncleaunt . __(' of ');
        }
        if ($generY == 6) {
            $reltext = 'arrière-arrière-arrière-grand-' . $uncleaunt . __(' of ');
        }
        if ($generY == 7) {
            $reltext = 'arrière-arrière-arrière-arrière-grand-' . $uncleaunt . __(' of ');
        }
        $gennr = $generY - 3;
        if ($generY >  7) {
            $reltext = 'arrière (' . $gennr . ' fois) grand-' . $uncleaunt . __(' of ');
        }
    } else {
        if ($generY == 2) {
            $reltext = $uncleaunt . __(' of ');
        }
        if ($generY == 3) {
            $reltext = __('grand') . $uncleaunt . __(' of ');
        }
        if ($generY == 4) {
            $reltext = __('great-grand') . $uncleaunt . __(' of ');
        }
        if ($generY == 5) {
            $reltext = __('2nd') . ' ' . __('great-grand') . $uncleaunt . __(' of ');
        }
        if ($generY == 6) {
            $reltext = __('3rd') . ' ' . __('great-grand') . $uncleaunt . __(' of ');
        }
        $gennr = $generY - 3;
        if ($generY >  6) {
            $reltext = $gennr . __('th') . ' ' . __('great-grand') . $uncleaunt . __(' of ');
        }
    }
}


function calculate_cousins($generX, $generY)
{
    global $db_functions, $reltext, $sexe, $sexe2, $selected_language, $rel_arrayX, $rel_arrayspouseX, $rel_arrayY, $rel_arrayspouseY, $spouse;
    global $reltext_nor, $reltext_nor2; // for Norwegian
    global $data_found;



    if ($selected_language == "es") {
        $gendiff = abs($generX - $generY);

        if ($gendiff == 0) {
            //if($sexe=="m") { $cousin=__('COUSIN_MALE'); $span_postfix="o "; $sibling=__('1st [COUSIN]'); }
            //else { $cousin=__('COUSIN_FEMALE'); $span_postfix="a "; $sibling='hermana';}
            if ($sexe == "m") {
                $cousin = __('cousin.male');
                $span_postfix = "o ";
                $sibling = __('1st [COUSIN]');
            } else {
                $cousin = __('cousin.female');
                $span_postfix = "a ";
                $sibling = 'hermana';
            }
            if ($generX == 2) {
                $reltext = $cousin . " " . $sibling . __(' of ');
            } elseif ($generX > 2) {
                $degree = $generX - 1;
                $reltext = $cousin . " " . $degree . $span_postfix . __(' of ');
            }
        } elseif ($generX < $generY) {
            if ($sexe == "m") {
                $uncle = __('uncle');
                $span_postfix = "o ";
                $gran = 'abuelo';
            } else {
                $uncle = __('aunt');
                $span_postfix = "a ";
                $gran = 'abuela';
            }

            if ($gendiff == 1) {
                $relname = $uncle;
            } elseif ($gendiff > 1 and $gendiff < 27) {
                $spantext = spanish_degrees($gendiff, $gran);
                $relname = $uncle . " " . $spantext;
            } else {
            }
            $reltext = $relname . " " . $generX . $span_postfix . __(' of ');
        } else {
            if ($sexe == "m") {
                $nephew = __('nephew');
                $span_postfix = "o ";
                $grson = 'nieto';
            } else {
                $nephew = __('niece');
                $span_postfix = "a ";
                $grson = 'nieta';
            }

            if ($gendiff == 1) {
                $relname = $nephew;
            } else {
                $spantext = spanish_degrees($gendiff, $grson);
                $relname = $nephew . " " . $spantext;
            }
            $reltext = $relname . " " . $generY . $span_postfix . __(' of ');
        }
        // *** Greek***
        // *** Ελληνικά ξαδέλφια***
    } elseif ($selected_language == "gr") {
        // TODO improve code
        $gendiff = abs($generX - $generY);

        if ($gendiff == 0) {
            //if($sexe=="m") { $cousin=__('COUSIN_MALE'); $span_postfix="o "; $sibling=__('1st [COUSIN]'); }
            //else { $cousin=__('COUSIN_FEMALE'); $span_postfix="a "; $sibling='hermana';}
            if ($sexe == "m") {
                $cousin = __('cousin.male');
                $gr_postfix = "ος ";
                $sibling = __('1st [COUSIN]');
            } else {
                $cousin = __('cousin.female');
                $gr_postfix = "η ";
                $sibling = __('1st [COUSIN]');
            }
            if ($generX == 2) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext = $sibling . $gr_postfix . $cousin . '  του ';
                    } else {
                        $reltext = $sibling . $gr_postfix . $cousin . ' της ';
                    }
                } elseif ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $reltext = $sibling . $gr_postfix . $cousin . ' του ';
                    } else {
                        $reltext = $sibling . $gr_postfix . $cousin . ' της ';
                    }
                }
            } elseif ($generX > 2) {
                $degree = $generX - 1;
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext =  $degree . $gr_postfix . $cousin . ' του ';
                    } else {
                        $reltext =  $degree . $gr_postfix . $cousin . ' της ';
                    }
                } elseif ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $reltext =  $degree . $gr_postfix . $cousin . ' του ';
                    } else {
                        $reltext =  $degree . $gr_postfix . $cousin . ' της ';
                    }
                }
            }
        } elseif ($generX < $generY) {
            if ($sexe == "m") {
                $uncle = __('uncle');
                $gr_postfix = "ος ";
                $gran = 'παππούς';
            } else {
                $uncle = __('aunt');
                $gr_postfix = "η ";
                $gran = 'γιαγιά';
            }
            if ($gendiff == 1) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $relname = $uncle . ' του ';
                    } else {
                        $relname = $uncle . ' της ';
                    }
                } elseif ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $relname = $uncle . ' του ';
                    } else {
                        $relname = $uncle . ' της ';
                    }
                }
            } else {

                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $relname = $uncle . ' του ';
                    } else {
                        $relname = $uncle . ' του ';
                    }
                } elseif ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $relname = $uncle . ' του ';
                    } else {
                        $relname = $uncle . ' του ';
                    }
                }
            }
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $uncle . " " . $generX . $gr_postfix . ' του';
                } else {
                    $reltext = $uncle . " " . $generX . $gr_postfix . ' της ';
                }
            } elseif ($sexe == 'f') {
                if ($sexe2 == 'm') {
                    $reltext = $uncle . " " . $generX . $gr_postfix . ' του ';
                } else {
                    $reltext = $uncle . " " . $generX . $gr_postfix . ' της ';
                }
            }
            if ($gendiff == 2) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext = $uncle . " " . $gran . ' του';
                    } else {
                        $reltext = $uncle . " " . $gran . ' της ';
                    }
                } elseif ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $reltext = $uncle . " " . $gran . ' του ';
                    } else {
                        $reltext = $uncle . " " . $gran . ' της ';
                    }
                }
            }
        } else {
            if ($sexe == "m") {
                $nephew = 'ανιψιος';
                $gr_postfix = "ος ";
                $grson = 'εγγονός';
            } else {
                $nephew = 'ανιψιά';
                $gr_postfix = "η ";
                $grson = 'εγγονή';
            }
            if ($gendiff == 1) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $relname = $nephew . ' του ';
                    } else {
                        $relname = $nephew . ' του ';
                    }
                } elseif ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $relname = $nephew . ' του ';
                    } else {
                        $relname = $nephew . ' του ';
                    }
                }
            }
            if ($sexe == 'm') {
                if ($sexe2 == 'm') {
                    $reltext = $nephew . " " . $generY . $gr_postfix . ' του';
                } else {
                    $reltext = $nephew . " " . $generY . $gr_postfix . ' της ';
                }
            } elseif ($sexe == 'f') {
                if ($sexe2 == 'm') {
                    $reltext = $nephew . " " . $generY . $gr_postfix . ' του ';
                } else {
                    $reltext = $nephew . " " . $generY . $gr_postfix . ' της ';
                }
            }
            if ($gendiff == 2) {
                if ($sexe == 'm') {
                    if ($sexe2 == 'm') {
                        $reltext = $nephew . " " . $grson . ' του';
                    } else {
                        $reltext = $nephew . " " . $grson . ' της ';
                    }
                } elseif ($sexe == 'f') {
                    if ($sexe2 == 'm') {
                        $reltext = $nephew . " " . $grson . ' του ';
                    } else {
                        $reltext = $nephew . " " . $grson . ' της ';
                    }
                }
            }
        }
        // *** Ελληνικά τέλος***
        // *** Greek end***    
    } elseif ($selected_language == "he") {
        if ($sexe == 'm') {
            $cousin = __('COUSIN_MALE');
        } else {
            $cousin = __('COUSIN_FEMALE');
        }
        $gendiff = abs($generX - $generY);
        if ($gendiff == 0) {
            $removenr = "";
        } elseif ($gendiff == 1) {
            $removenr = 'בהפרש ' . __('once removed');
        } else {
            $removenr = 'בהפרש ' . $gendiff . " " . __('times removed');
        }
        $degree = '';
        $degreediff = min($generX, $generY);
        if ($degreediff > 2) {
            $degree = 'דרגה ' . ($degreediff - 1) . " ";
        }
        $reltext = $cousin . $degree . $removenr . __(' of ');
    } elseif ($selected_language == "no") {
        $reltext_nor = '';
        $reltext_nor2 = '';
        $degreediff = min($generX, $generY);
        if ($degreediff == 2) {
            $nor_cousin = __('1st [COUSIN]'); // 1st cousin
        } elseif ($degreediff == 3) {
            $nor_cousin = __('2nd [COUSIN]'); // 2nd cousin
        } elseif ($degreediff == 4) {
            $nor_cousin = __('3rd [COUSIN]'); // 3rd cousin
        } elseif ($degreediff == 5) {
            $nor_cousin = __('4th [COUSIN]'); // 4th cousin
        } elseif ($degreediff > 5) {
            $gennr = $degreediff - 3;
            $nor_cousin = $degreediff . "-menningen";
        }

        $gendiff = abs($generX - $generY);
        if ($gendiff == 0) { // A and B are cousins of same generation
            $reltext = $nor_cousin . __(' of ');
        } elseif ($generX > $generY) {  // A is the "younger" cousin  (A er barnebarnet av Bs tremenning)
            if ($sexe == 'm') {
                $child = __('son');
            }  // only for 1st generation
            else {
                $child = __('daughter');
            }
            if ($gendiff == 1) {
                $reltext = $child . __(' of ');
            }   // sønnen/datteren til
            if ($gendiff == 2) {
                $reltext = 'barnebarnet ' .
                    __(' of ');
            } // barnebarnet til
            if ($gendiff == 3) {
                $reltext = __('great-grand') . ' barnet' . __(' of ');
            } //olde+barnet
            if ($gendiff == 4) {
                $reltext = 'tippolde barnet' . __(' of ');
            }
            if ($gendiff == 5) {
                $reltext = 'tipp-tippolde barnet' . __(' of ');
            }
            $gennr = $gendiff - 3;
            if ($gendiff >  5) {
                $reltext = $gennr . 'x tippolde barnet' . __(' of ');
            }
            $reltext_nor = "s " . substr($nor_cousin, 0, -2);
            $reltext_nor2 = $nor_cousin . __(' of ');
        } elseif ($generX < $generY) {  // A is the "older" cousin (A er timenning av Bs tipp-tippoldefar)
            if ($gendiff == 1) {
                $temptext = 'forelderen';
            }
            if ($gendiff == 2) {
                $temptext = __('grand') . 'forelderen';
            }
            if ($gendiff == 3) {
                $temptext = __('great-grand') . 'forelderen';
            }
            if ($gendiff == 4) {
                $temptext = 'tippoldeforelderen';
            }
            if ($gendiff == 5) {
                $temptext = 'tipp-tippoldeforelderen';
            }
            $gennr = $gendiff - 3;
            if ($gendiff >  5) {
                $temptext = $gennr . 'x tippoldeforelderen';
            }
            $reltext = $nor_cousin . __(' of ');
            $reltext_nor = "s " . substr($temptext, 0, -2);
            $reltext_nor2 = $temptext . __(' of ');

            /* following is the alternative way of notation for cousins when X is the older one
            // (A er barnebarn av Bs tipp-tippolefars sosken)
            // at the moment we use the previous method that is shorter and approved by our Norwegian user
            // but we'll leave this here, just in case....
            $reltext = $nor_removed;
            if ($generX == 2) {
                $X_removed = 'barnet'."barn";
            }
            if ($generX == 3) {
                $X_removed = __('great-grand')."barn";
            }
            if ($generX == 4) {
                $X_removed = 'tippolde'."barn";
            }
            if ($generX == 5) {
                $X_removed = 'tipp-tippolde'."barn";
            }
            if ($generX >  5) {
                $gennr = $generX-3;
                $X_removed = $gennr.'x tippolde'."barn";
            }

            if ($generY == 3) {
                $Y_removed = __('great-grand')."barn";
            }
            if ($generY == 4) {
                $Y_removed = 'tippolde '."barn";
            }
            if ($generY == 5) {
                $Y_removed = 'tipp-tippolde '."barn";
            }
            if ($generY >  5) {
                $gennr = $generY-3;
                $Y_removed = $gennr.'x tippolde'."barn";
            }
            $reltext = $X_removed.__(' of ');
            $reltext_nor = "s ".$Y_removed."s ".'søskenet';
            $reltext_nor2 = $Y_removed.__(' of ');
            */
        }
    } elseif ($selected_language == "sv") {
        $degreediff = min($generX, $generY);
        if ($degreediff == 2) {
            $se_cousin = "kusin";
        } // 1st cousin  
        elseif ($degreediff == 3) {
            $se_cousin = "tremänning";
        } // 2nd cousin  
        elseif ($degreediff == 4) {
            $se_cousin = "fyrmänning";
        } // 3rd cousin  
        elseif ($degreediff == 5) {
            $se_cousin = "femmänning";
        } // 4th cousin  
        elseif ($degreediff == 6) {
            $se_cousin = "sexmänning";
        } // 5th cousin  
        elseif ($degreediff == 7) {
            $se_cousin = "sjumänning";
        } // 6th cousin 
        elseif ($degreediff == 8) {
            $se_cousin = "åttamänning";
        } // 7th cousin  
        elseif ($degreediff == 9) {
            $se_cousin = "niomänning";
        } // 8th cousin  
        elseif ($degreediff == 10) {
            $se_cousin = "tiomänning";
        } // 9th cousin  
        elseif ($degreediff == 11) {
            $se_cousin = "elvammänning";
        } // 10th cousin  
        elseif ($degreediff == 12) {
            $se_cousin = "tolvmänning";
        } // 11nd cousin  
        elseif ($degreediff == 13) {
            $se_cousin = "trettonmänning";
        } // 12th cousin  
        elseif ($degreediff == 14) {
            $se_cousin = "fjortonmänning";
        } // 13th cousin  
        elseif ($degreediff == 15) {
            $se_cousin = "femtonmänning";
        } // 14th cousin  
        elseif ($degreediff == 16) {
            $se_cousin = "sextonmänning";
        } // 15th cousin  
        elseif ($degreediff == 17) {
            $se_cousin = "sjuttonmänning";
        } // 16th cousin  
        elseif ($degreediff == 18) {
            $se_cousin = "artonmänning";
        } // 17th cousin  
        elseif ($degreediff == 19) {
            $se_cousin = "nittonmänning";
        } // 18th cousin  
        elseif ($degreediff == 20) {
            $se_cousin = "tjugomänning";
        } // 19th cousin  
        elseif ($degreediff > 20) {
            $gennr = $degreediff - 3;
            $se_cousin = $degreediff . "-männing";
        }

        $gendiff = abs($generX - $generY); // generation gap between A and B
        if ($gendiff == 0) { // A and B are cousins of same generation
            $reltext = $se_cousin . __(' of ');
        } elseif ($generX > $generY) {  // A is the "younger" cousin  (example A är tremannings barnbarn för B)
            if ($gendiff == 1)
                if ($se_cousin == "kusin") {
                    $reltext = 'kusinbarn' . __(' of ');
                } else {
                    $reltext = $se_cousin . 's barn' . __(' of ');
                }
            if ($gendiff == 2) {
                $reltext = $se_cousin . 's barnbarn' . __(' of ');
            }
            $gennr = $gendiff;
            if ($gendiff >  2) {
                $reltext = $se_cousin . 's ' . $gennr . ':e generations barn' . __(' of ');
            }
        } elseif ($generX < $generY) {  // A is the "older" cousin (A är farfars tremanning för B)

            if ($spouse == "2" or $spouse == "3") { // right person is spouse of Y, not Y
                $relarr = $rel_arrayspouseY;
            } else {
                $relarr = $rel_arrayY;
            }

            $arrnum = 0;
            reset($ancsarr);
            $count = $data_found["foundY_nr"];
            while ($count != 0) {
                $parnumber = $count;
                $ancsarr[$arrnum] = $parnumber;
                $arrnum++;
                $count = $relarr[$count][2];
            }

            // parent
            $persidDb = $db_functions->get_person($relarr[$parnumber][0]);
            $parsexe = $persidDb->pers_sexe;
            if ($parsexe == "M") {
                $se_par = "far";
            } else {
                $se_par = "mor";
            }

            //grandparent
            if ($gendiff > 1) {
                $persidDb2 = $db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                $parsexe2 = $persidDb2->pers_sexe;
                if ($parsexe2 == "M") {
                    $se_grpar = "fars";
                } else {
                    $se_grpar = "mors";
                }
            }
            if ($gendiff == 1) {
                $reltext = $se_par . 's ' . $se_cousin . __(' of ');
            }
            if ($gendiff == 2) {
                $reltext = $se_par . $se_grpar . ' ' . $se_cousin . __(' of ');
            }
            $gennr = $gendiff;
            if ($gendiff >  2) {
                $reltext = $gennr . ':e generation anas ' . $se_cousin . __(' of ');
            }
        }
    } elseif ($selected_language == "cn") {    // cousin biao
        // Followed guidelines of: http://www.kwanfamily.info/culture/familytitles_table.php
        // paternal male cousin -	father's brother's son	堂兄弟
        // paternal female cousin-	father's brother's daughters	堂姊妹
        // paternal male cousin - father's sisters's son	表兄弟
        // maternal male cousin	mother's siblings' son 表兄弟
        // paternal female cousin father's sister's daughters	表姊妹
        // maternal female cousin	mother's siblings' daughters 表姊妹
        // Other translations for cousins that I saw: (not used)
        // dongshan: 叔伯, 叔伯公, 曾叔伯公
        // 表姐 cousin jie
        // 表妹 cousin mei
        // 表姐妹 cousin jie-mei
        // 表亲 cousin qin


        $gendiff = abs($generX - $generY);
        $degreediff = min($generX, $generY);
        if ($gendiff == 0 and $degreediff == 2) {
            // deals with first cousins not removed only.
            // Unfortunately we miss the Chinese terminology for 2nd, 3rd cousins and "removed" sequence...
            if ($spouse == "1") { // left person is spouse of X, not X
                $relarrX = $rel_arrayspouseX;
            } else {
                $relarrX = $rel_arrayX;
            }
            $arrnumX = 0;
            if (isset($ancsarrX)) reset($ancsarrX);
            $count = $data_found["foundX_nr"];
            while ($count != 0) {
                $parnumberX = $count;
                $ancsarrX[$arrnumX] = $parnumberX;
                $arrnumX++;
                $count = $relarrX[$count][2];
            }
            $persidDbX = $db_functions->get_person($relarrX[$parnumberX][0]);
            $parsexeX = $persidDbX->pers_sexe;
            if ($parsexeX == 'F') { // the easier part: with siblings of mother doesn't matter from her brothers or sisters
                if (($sexe2 == "m" and $spouse != 2 and $spouse != 3) or ($sexe2 == "f" and ($spouse == 2 or $spouse == 3))) {
                    $reltext = '表兄弟是';  // male cousin from mother's side
                } else {
                    $reltext = '表姊妹是';  // female cousin from mother's side
                }
            } else { // difficult part: it matters whether cousins thru father's brothers of father's sister!

                if ($spouse == "2" or $spouse == "3") { // right person is spouse of Y, not Y
                    $relarrY = $rel_arrayspouseY;
                } else {
                    $relarrY = $rel_arrayY;
                }
                $arrnumY = 0;
                if (isset($ancsarrY)) reset($ancsarrY);
                $count = $data_found["foundY_nr"];
                while ($count != 0) {
                    $parnumberY = $count;
                    $ancsarrY[$arrnumY] = $parnumberY;
                    $arrnumY++;
                    $count = $relarrY[$count][2];
                }
                $persidDbY = $db_functions->get_person($relarrY[$parnumberY][0]);
                $parsexeY = $persidDbY->pers_sexe;
                if ($parsexeY == "M") { // child of father's brother
                    if (($sexe2 == "m" and $spouse != 2 and $spouse != 3) or ($sexe2 == "f" and ($spouse == 2 or $spouse == 3))) {
                        $reltext = '堂兄弟是';
                    } else {
                        $reltext = '堂姊妹是';
                    }
                } else { // child of father's sister
                    if (($sexe2 == "m" and $spouse != 2 and $spouse != 3) or ($sexe2 == "f" and ($spouse == 2 or $spouse == 3))) {
                        $reltext = '表兄弟是';
                    } else {
                        $reltext = '表姊妹是';
                    }
                }
            }
        } else {
            $reltext = "notext";
        }
    } elseif ($selected_language == "da") {
        $gendiff = abs($generX - $generY);
        $degreediff = min($generX, $generY);

        if ($degreediff == 2) {
            $nor_cousin = 'kusine'; // 1st cousin
        } elseif ($degreediff == 3) {
            $nor_cousin = 'halvkusine'; // 2nd cousin
        } elseif ($degreediff > 3) {
            $gennr = $degreediff - 1;
            $nor_cousin = $gennr . ". kusine";  // 3. kusine
        }

        if ($degreediff == 2 and $gendiff == 0) {
            $reltext = __('COUSIN_MALE') . __(' of ');
        }  // first cousins
        elseif ($degreediff == 2 and $gendiff == 1 and $generX < $generY) {   // first cousins once removed - X older
            if ($sexe == "m") {
                $reltext =  'halvonkel' . __(' of ');
            } else {
                $reltext =  'halvtante' . __(' of ');
            }
        } elseif ($degreediff == 2 and $gendiff == 1 and $generX > $generY) {   // first cousins once removed - Y older
            if ($sexe == "m") {
                $reltext =  'halvnevø' . __(' of ');
            } else {
                $reltext =  'halvniece' . __(' of ');
            }
        } elseif ($degreediff == 3 and $gendiff == 0) {
            $reltext = 'halvkusine' . __(' of ');
        }  // second cousins

        elseif ($generX > $generY) {  // A is the "younger" cousin  (A er barnebarn af Bs tremenning)
            if ($sexe == 'm') {
                $child = __('son');
            }  // only for 1st generation
            else {
                $child = __('daughter');
            }
            if ($gendiff == 1) {
                $reltext = $child . __(' of ');
            }   // søn/datter af
            if ($gendiff == 2) {
                $reltext = 'barnebarn ' . __(' of ');
            } // barnebarn af
            if ($gendiff == 3) {
                $reltext = 'oldebarn' . __(' of ');
            }
            if ($gendiff == 4) {
                $reltext = 'tip oldebarn' . __(' of ');
            }
            if ($gendiff == 5) {
                $reltext = 'tip tip oldebarn' . __(' of ');
            }
            if ($gendiff == 6) {
                $reltext = 'tip tip tip oldebarn' . __(' of ');
            }
            $gennr = $gendiff - 3;
            if ($gendiff >  6) {
                $reltext = $gennr . ' gange tip oldebarn' . __(' of ');
            }
            $reltext_nor = "s " . $nor_cousin;
            $reltext_nor2 = $nor_cousin . __(' of ');
        } elseif ($generX < $generY) {  // A is the "older" cousin (A er timenning af Bs tiptipoldeforældre)
            if ($gendiff == 1) {
                $temptext = 'forældre';
            }
            if ($gendiff == 2) {
                $temptext = 'bedsteforældre';
            }
            if ($gendiff == 3) {
                $temptext = 'oldeforældre';
            }
            if ($gendiff == 4) {
                $temptext = 'tip oldeforældre';
            }
            if ($gendiff == 5) {
                $temptext = 'tip tip oldeforældre';
            }
            if ($gendiff == 6) {
                $temptext = 'tip tip tip oldeforældre';
            }
            $gennr = $gendiff - 3;
            if ($gendiff >  7) {
                $temptext = $gennr . ' gange tip oldeforældre';
            }
            $reltext = $nor_cousin . ' til ';
            $reltext_nor = "s " . $temptext;
            $reltext_nor2 = $temptext . ' til ';
        }
    } elseif ($selected_language == "fr") {  // french
        if ($sexe == 'm') {
            $cousin = __('cousin.male');
            $gend = '';
        } else {
            $cousin = __('cousin.female');
            $gend = 'e';
        }
        // 1st cousin, 2nd cousin etc
        $degreediff = min($generX, $generY);

        if ($degreediff == 2) {
            $cousin .= ' germain' . $gend;
        }
        if ($degreediff == 3) {
            $cousin .= ' issu' . $gend . ' de germains ';
        }
        if ($degreediff == 4) {
            $cousin = ' petit' . $gend . '-' . $cousin;
        }
        if ($degreediff == 5) {
            $cousin = 'arrière-petit' . $gend . '-' . $cousin;
        }
        if ($degreediff == 6) {
            $cousin = 'arrière-arrière-petit' . $gend . '-' . $cousin;
        }
        if ($degreediff == 7) {
            $cousin = 'arrière-arrière-arrière-petit' . $gend . '-' . $cousin;
        } elseif ($degreediff > 7) {
            $cousin = 'arrière (' . ($degreediff - 4) . ' fois) petit' . $gend . '-' . $cousin;
        }

        // once/twice etc removed
        $gendiff = abs($generX - $generY);
        if ($gendiff == 1) {
            $cousin .= " éloigné" . $gend . " au 1er degré";
        } elseif ($gendiff == 2) {
            $cousin .= " éloigné" . $gend . " au 2ème degré";
        } elseif ($gendiff == 3) {
            $cousin .= " éloigné" . $gend . " au 3ème degré";
        } elseif ($gendiff == 4) {
            $cousin .= " éloigné" . $gend . " au 4ème degré";
        } elseif ($gendiff == 5) {
            $cousin .= " éloigné" . $gend . " au 5ème degré";
        } elseif ($gendiff > 5) {
            $cousin .= " éloigné" . $gend . " au " . $gendiff . "ème degré ";
        }

        $reltext = $cousin . __(' of ');
    } else {
        $gendiff = abs($generX - $generY);
        if ($gendiff == 0) {
            $removenr = "";
        } elseif ($gendiff == 1) {
            $removenr = ' ' . __('once removed');
        } elseif ($gendiff == 2) {
            $removenr = ' ' . __('twice removed');
        } elseif ($gendiff > 2) {
            $removenr = $gendiff . ' ' . __('times removed');
        }

        $degreediff = min($generX, $generY);
        if ($degreediff == 2) {
            $degree = __('1st [COUSIN]');
        }
        if ($degreediff == 3) {
            $degree = __('2nd [COUSIN]');
        }
        if ($degreediff == 4) {
            $degree = __('3rd [COUSIN]');
        }

        if ($sexe == 'm') {
            $cousin = __('cousin.male');
        } else {
            $cousin = __('cousin.female');
        }

        if ($degreediff > 4) {
            $degreediff -= 1;
            $degree = $degreediff . __('th') . ' ';
            if ($selected_language == "nl") {
                $degreediff--;  // 5th cousin is in dutch "4de achterneef"
                $degree = $degreediff . __('th') . ' ' . __('2nd [COUSIN]'); // in Dutch cousins are counted with 2nd cousin as base
            }
        }
        if (($selected_language == "fi" and $degreediff == 3) or ($selected_language == "nl" and $degreediff >= 3)) {
            // no space here (FI): pikkuserkku
            // no space here (NL): achterneef, achter-achternicht, 3de achterneef
            $reltext = $degree . $cousin . ' ' . $removenr . __(' of ');
        } else {
            $reltext = $degree . ' ' . $cousin . ' ' . $removenr . __(' of ');
        }
    }
}


function search_marital()
{
    global $db_functions, $famsX, $famsY, $famspouseX, $famspouseY, $rel_arrayX, $rel_arrayY;
    global $sexe, $sexe2, $spousenameX, $spousenameY;
    global $rel_arrayspouseX, $rel_arrayspouseY;
    global $data_found;

    $pers_cls = new person_cls;
    $marrX = '';
    if (isset($famsX)) $marrX = explode(";", $famsX);
    $marrY = '';
    if (isset($famsY)) $marrY = explode(";", $famsY);

    if ($famsX != '') {
        $marrcount = count($marrX);
        for ($x = 0; $x < $marrcount; $x++) {
            @$familyDb = $db_functions->get_family($marrX[$x], 'man-woman');
            if ($sexe == 'f') {
                $thespouse = $familyDb->fam_man;
            } else {
                $thespouse = $familyDb->fam_woman;
            }

            $rel_arrayspouseX = create_rel_array($db_functions, $thespouse);

            if (isset($rel_arrayspouseX)) {
                compare_rel_array($rel_arrayspouseX, $rel_arrayY, 1); // "1" flags comparison with "spouse of X"
            }

            if ($data_found["foundX_match"] !== '') {
                $famspouseX = $marrX[$x];

                if ($sexe == 'm') {
                    $sexe = "f";
                } else {
                    $sexe = "m";
                } // we have to switch sex since the spouse is the relative!
                calculate_rel($data_found);

                $spouseidDb = $db_functions->get_person($thespouse);
                $name = $pers_cls->person_name($spouseidDb);
                $spousenameX = $name["name"];

                break;
            }
        }
    }

    if ($data_found["foundX_match"] === '' and $famsY != '') {  // no match found between "spouse of X" and "Y", let's try "X" with "spouse of "Y"
        $ymarrcount = count($marrY);
        for ($x = 0; $x < $ymarrcount; $x++) {
            @$familyDb = $db_functions->get_family($marrY[$x], 'man-woman');
            if ($sexe2 == 'f') {
                $thespouse2 = $familyDb->fam_man;
            } else {
                $thespouse2 = $familyDb->fam_woman;
            }

            $rel_arrayspouseY = create_rel_array($db_functions, $thespouse2);

            if (isset($rel_arrayspouseY)) {
                compare_rel_array($rel_arrayX, $rel_arrayspouseY, 2); // "2" flags comparison with "spouse of Y"
            }
            if ($data_found["foundX_match"] !== '') {
                $famspouseY = $marrY[$x];
                calculate_rel($data_found);
                $spouseidDb = $db_functions->get_person($thespouse2);
                $name = $pers_cls->person_name($spouseidDb);
                $spousenameY = $name["name"];
                break;
            }
        }
    }

    if ($data_found["foundX_match"] === '' and $famsX != '' and $famsY != '') { // still no matches, let's try comparison of "spouse of X" with "spouse of Y"
        $xmarrcount = count($marrX);
        $ymarrcount = count($marrY);
        for ($x = 0; $x < $xmarrcount; $x++) {
            for ($y = 0; $y < $ymarrcount; $y++) {
                @$familyDb = $db_functions->get_family($marrX[$x], 'man-woman');
                if ($sexe == 'f') {
                    $thespouse = $familyDb->fam_man;
                } else {
                    $thespouse = $familyDb->fam_woman;
                }

                $rel_arrayspouseX = create_rel_array($db_functions, $thespouse);
                @$familyDb = $db_functions->get_family($marrY[$y], 'man-woman');
                if ($sexe2 == 'f') {
                    $thespouse2 = $familyDb->fam_man;
                } else {
                    $thespouse2 = $familyDb->fam_woman;
                }

                $rel_arrayspouseY = create_rel_array($db_functions, $thespouse2);

                if (isset($rel_arrayspouseX) and isset($rel_arrayspouseY)) {
                    compare_rel_array($rel_arrayspouseX, $rel_arrayspouseY, 3); //"3" flags comparison "spouse of X" with "spouse of Y"
                }
                if ($data_found["foundX_match"] !== '') {

                    if ($sexe == 'm') {
                        $sexe = "f";
                    } else {
                        $sexe = "m";
                    } // we have to switch sex since the spouse is the relative!
                    calculate_rel($data_found);

                    $spouseidDb = $db_functions->get_person($thespouse);
                    $name = $pers_cls->person_name($spouseidDb);
                    $spousenameX = $name["name"];

                    $spouseidDb = $db_functions->get_person($thespouse2);
                    $name = $pers_cls->person_name($spouseidDb);
                    $spousenameY = $name["name"];

                    $famspouseX = $marrX[$x];
                    $famspouseY = $marrY[$y];

                    break;
                } //end if foundmatch !=''
            } // for y
            if ($data_found["foundX_match"] !== '') {
                break;
            }
        } // for x
    } // end if not found match

} //end function


function search_bloodrel()
{
    global $rel_arrayX, $rel_arrayY, $person;
    global $db_functions;
    global $data, $data_found;
    unset_vars();
    $rel_arrayX = create_rel_array($db_functions, $data["person1"]); // === GEDCOM nr of person X ===
    $rel_arrayY = create_rel_array($db_functions, $data["person2"]); // === GEDCOM nr of person Y ===
    if (isset($rel_arrayX) and isset($rel_arrayY)) {
        compare_rel_array($rel_arrayX, $rel_arrayY, 0);
    }

    if ($data_found["foundX_match"] !== '') {
        calculate_rel($data_found);
    }
}


function unset_vars()
{
    global $reltext, $spouse, $table;
    global $data_found;

    $data_found["foundX_nr"] = '';
    $data_found["foundY_nr"] = '';
    $data_found["foundX_gen"] = '';
    $data_found["foundY_gen"] = '';
    $data_found["foundX_match"] = '';
    $data_found["foundY_match"] = '';
    $table = '';
    $reltext = '';
    $spouse = '';
}




function display()
{
    global $dbh, $db_functions, $reltext, $bloodreltext, $name1, $name2, $spouse, $rel_arrayspouseX;
    global $special_spouseY, $special_spouseX, $spousenameX, $spousenameY, $table, $doublespouse;
    global $rel_arrayX, $rel_arrayY, $famX, $famY, $dutchtext, $searchDb, $searchDb2;
    global $sexe, $selected_language, $famspouseX, $famspouseY, $reltext_nor, $reltext_nor2;
    global $tree_id, $link_cls, $uri_path;
    global $data_found;

    // *** Use person class ***
    $pers_cls = new person_cls;

    $vars['pers_family'] = $famX;
    $linkX = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

    $vars['pers_family'] = $famY;
    $linkY = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

    $language_is = ' ' . __('is') . ' ';
    if ($selected_language == "he") {
        if ($sexe == "m") {
            $language_is = ' הוא ';
        } else {
            $language_is = ' היא ';
        }
    } elseif ($selected_language == "cn") {
        $language_is = '的';
    }
    $bloodrel = '';
    search_bloodrel();

    if ($reltext) {
        echo '<br><br><table class="ext"><tr><td style="padding-right:30px;vertical-align:text-top;">';
        $bloodrel = 1;
        echo __('BLOOD RELATIONSHIP: ');
        echo "<br><br>";
        if ($selected_language == "cn" and strpos($reltext, "notext") !== false) {
            // don't display text if relation can't be phrased  
        } else {
            if ($selected_language == "fi") {
                echo 'Kuka: ';
            }   // who
            echo "&nbsp;&nbsp;<a class='relsearch' href='" . $linkX . "main_person=" . $rel_arrayX[0][0] . "'>";
            echo $name1 . "</a>";
            if ($selected_language == "fi") {
                echo '&nbsp;&nbsp;' . 'Kenelle: ';
            }  // to whom
            else {
                echo $language_is . $reltext;
            }
            echo "<a class='relsearch' href='" . $linkY . "main_person=" . $rel_arrayY[0][0] . "'>" . $name2 . "</a>" . $reltext_nor . "<p>";
            echo $dutchtext;
            if ($selected_language == "fi") {
                echo 'Sukulaisuus tai muu suhde: <b>' . $reltext . '</b>';
            }
            echo '<hr style="width:100%;height:0.25em;color:darkblue;background-color:darkblue;"  >';
        }
        $bloodreltext = $reltext;
        display_table();
    }

    if ($table != 1 and $table != 2 and $table != 7) {
        unset_vars();
        search_marital();

        if ($reltext) {  // notext is used in Chinese display if relation can't be worded.

            //check if this is involves a marriage or a partnership of any kind
            $relmarriedX = 0;
            if (isset($famspouseX)) {
                $kindrel = $dbh->query("SELECT fam_kind FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famspouseX . "'");
                @$kindrelDb = $kindrel->fetch(PDO::FETCH_OBJ);
                if (
                    $kindrelDb->fam_kind != 'living together' and
                    $kindrelDb->fam_kind != 'engaged' and
                    $kindrelDb->fam_kind != 'homosexual' and
                    $kindrelDb->fam_kind != 'unknown' and
                    $kindrelDb->fam_kind != 'non-marital' and
                    $kindrelDb->fam_kind != 'partners' and
                    $kindrelDb->fam_kind != 'registered'
                ) {
                    $relmarriedX = 1;  // use: husband or wife
                } else {
                    $relmarriedX = 0;  // use: partner
                }
            }

            $relmarriedY = 0;
            if (isset($famspouseY)) {
                $kindrel2 = $dbh->query("SELECT fam_kind
                    FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famspouseY . "'");
                @$kindrel2Db = $kindrel2->fetch(PDO::FETCH_OBJ);
                if (
                    $kindrel2Db->fam_kind != 'living together' and
                    $kindrel2Db->fam_kind != 'engaged' and
                    $kindrel2Db->fam_kind != 'homosexual' and
                    $kindrel2Db->fam_kind != 'unknown' and
                    $kindrel2Db->fam_kind != 'non-marital' and
                    $kindrel2Db->fam_kind != 'partners' and
                    $kindrel2Db->fam_kind != 'registered'
                ) {
                    $relmarriedY = 1;  // use: husband or wife
                } else {
                    $relmarriedY = 0;  // use: partner
                }
            }

            if ($bloodrel == 1) {
                echo '</td><td style="padding-left:30px;border-left:2px solid #bbbbbb;vertical-align:text-top;">';
            } else {
                echo '<br><br><table class="ext"<tr><td>';
            }

            echo __('MARITAL RELATIONSHIP: ');

            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="print_version"><input type="submit" name="extended" value="' . __('Use Extended Calculator') . '" class="btn btn-sm btn-success"></span>';

            echo "<br><br>";
            $spousetext1 = '';
            $spousetext2 = '';
            $finnish_spouse1 = '';
            $finnish_spouse2 = '';

            if ($doublespouse == 1) { // X and Y are both spouses of Z
                $spouseidDb = $db_functions->get_person($rel_arrayspouseX[$data_found["foundX_match"]][0]);
                $name = $pers_cls->person_name($spouseidDb);
                $spousename = $name["name"];

                echo "<span>&nbsp;&nbsp;<a class='relsearch' href='" . $linkX . "main_person=" . $rel_arrayX[0][0] . "'>";
                echo $name1 . "</a> " . __('and') . ': ';
                echo "<a class='relsearch' href='" . $linkY . "main_person=" . $rel_arrayY[0][0] . "'>" . $name2 . "</a>";
                if ($searchDb->pers_sexe == "M") {
                    echo ' ' . __('are both husbands of') . ' ';
                } else {
                    echo ' ' . __('are both wifes of') . ' ';
                }
                echo "<a href='" . $linkY . "main_person=" . $rel_arrayspouseX[$data_found["foundX_match"]][0] . "'>" . $spousename . "</a></span><br>";
            } elseif ($reltext != "notext") {
                if (($spouse == 1 and $special_spouseX !== 1) or $spouse == 3) {
                    if ($relmarriedX == 0 and $selected_language != "cn") {
                        $spousetext1 = __('partner') . __(' of ');
                        $finnish_spouse1 = __('partner');
                    } else {
                        if ($searchDb->pers_sexe == 'M') {
                            $spousetext1 = ' ' . __('husband of') . ' ';
                            if ($selected_language == "fi") {
                                $finnish_spouse1 = 'mies';
                            }
                            if ($selected_language == "cn") {
                                $spousetext1 = '妻子';
                            } // "A's wife is B"
                        } else {
                            $spousetext1 = ' ' . __('wife of') . ' ';
                            if ($selected_language == "fi") {
                                $finnish_spouse1 = 'vaimo';
                            }
                            if ($selected_language == "cn") {
                                $spousetext1 = '丈夫';
                            } // "A's husband is B"
                        }
                    }
                }
                if (($spouse == 2 or $spouse == 3) and $special_spouseY !== 1) {
                    if ($relmarriedY == 0 and $selected_language != "cn") {
                        $spousetext2 = __('partner') . __(' of ');
                        $finnish_spouse2 = __('partner');
                    } else {
                        if ($searchDb2->pers_sexe == 'M') {
                            $spousetext2 = ' ' . __('wife of') . ' ';
                            if ($selected_language == "fi") {
                                $finnish_spouse2 = 'mies';
                            }
                            // yes - it's really husband cause the sentence goes differently
                            if ($selected_language == "cn") {
                                $spousetext2 = '丈夫';
                            } // "A's uncle's husband is B"
                        } else {
                            $spousetext2 = ' ' . __('husband of') . ' ';
                            if ($selected_language == "fi") {
                                $finnish_spouse2 = 'vaimo';
                            }
                            // yes - it's really wife cause the sentence goes differently
                            if ($selected_language == "cn") {
                                $spousetext2 = '妻子';
                            } // "A's uncle's wife is B"
                        }
                    }
                }

                if ($selected_language == "fi") {  // very different phrasing for correct grammar
                    echo 'Kuka: ';
                    echo "<span>&nbsp;&nbsp;<a class='relsearch' href='" . $linkX . "main_person=" . $rel_arrayX[0][0] . "'>";
                    echo $name1 . "</a>";
                    echo '&nbsp;&nbsp;Kenelle: ';
                    echo "<a class='relsearch' href='" . $linkY . "main_person=" . $rel_arrayY[0][0] . "'>" . $name2 . "</a></span><br>";
                    echo 'Sukulaisuus tai muu suhde: ';
                    if (!$special_spouseX and !$special_spouseY and $table != 7) {
                        if ($spousetext2 != '' and $spousetext1 == '') { // X is relative of spouse of Y
                            echo '(';
                            echo "<a href='" . $linkX . "main_person=" . $rel_arrayX[0][0] . "'>" . $name1 . "</a>";
                            echo ' - ' . $spousenameY . '):&nbsp;&nbsp;' . $reltext . '<br>';
                            echo $spousenameY . ', ' . $finnish_spouse2 . ' ';
                            echo "<a href='" . $linkY . "main_person=" . $rel_arrayY[0][0] . "'>" . $name2 . "</a>";
                        } elseif ($spousetext1 != '' and $spousetext2 == '') { // X is spouse of relative of Y
                            echo '(' . $spousenameX . ' - ';
                            echo "<a href='" . $linkY . "main_person=" . $rel_arrayY[0][0] . "'>" . $name2 . "</a>";
                            echo '):&nbsp;&nbsp;' . $reltext . '<br>';
                            echo $spousenameX . ', ' . $finnish_spouse1 . ' ';
                            echo "<a href='" . $linkX . "main_person=" . $rel_arrayX[0][0] . "'>" . $name1 . "</a>";
                        } else {   // X is spouse of relative of spouse of Y
                            echo '(' . $spousenameX . ' - ' . $spousenameY . '):&nbsp;&nbsp;' . $reltext . '<br>';
                            echo $spousenameX . ', ' . $finnish_spouse1 . ' ';
                            echo "<a href='" . $linkX . "main_person=" . $rel_arrayX[0][0] . "'>" . $name1 . "</a><br>";
                            echo $spousenameY . ', ' . $finnish_spouse2 . ' ';
                            echo "<a href='" . $linkY . "main_person=" . $rel_arrayY[0][0] . "'>" . $name2 . "</a>";
                        }
                    } elseif ($special_spouseX or $special_spouseY) { // brother-in-law/sister-in-law/father-in-law/mother-in-law
                        echo '<b>' . $reltext . '</b><br>';
                    } elseif ($table == 7) {
                        if ($relmarriedX == 0 or $relmarriedY == 0) {
                            echo '<b>' . __('partner') . '</b><br>';
                        } else {
                            echo '<b>' . $finnish_spouse1 . '</b><br>';
                        }
                    }
                }  // end of finnish part

                else {
                    if ($spousetext2 == '') {
                        $reltext_nor2 = '';
                    }  // Norwegian grammar...
                    else {
                        $reltext_nor = '';
                    }
                    if ($selected_language == "cn") {
                        $language_is = '的';
                        if ($reltext == " ") { // A's husband/wife is B
                            $reltext = "是";
                        } else {
                            mb_internal_encoding("UTF-8");
                            if ($spousetext1 != "" and $spousetext2 == "") {
                                $spousetext1 .= '的';
                            } elseif ($spousetext2 != "" and $spousetext1 == "") {
                                $reltext = mb_substr($reltext, 0, -1) . '的';
                                $spousetext2 .= '是';
                            } elseif ($spousetext1 != "" and $spousetext2 != "") {
                                $spousetext1 .= '的';
                                $reltext = mb_substr($reltext, 0, -1) . '的';
                                $spousetext2 .= '是';
                            }
                        }
                    }
                    if ($table == 6 or $table == 7) {
                        $reltext_nor = '';
                    }

                    echo "<span>&nbsp;&nbsp;<a class='relsearch' href='" . $linkX . "main_person=" . $rel_arrayX[0][0] . "'>";

                    echo $name1 . "</a>" . $language_is . $spousetext1 . $reltext . $reltext_nor2 . $spousetext2;
                    echo "<a class='relsearch' href='" . $linkY . "main_person=" . $rel_arrayY[0][0] . "'>" . $name2 . "</a>" . $reltext_nor . "</span><br>";
                }
            }

            echo '<hr style="width:100%;height:0.25em;color:darkblue;background-color:darkblue;" >';

            display_table();
        }
    }

    if ($reltext == '') {
        if ($bloodreltext == '') {
            echo '<br><br><table class="ext"><tr><td>';
            echo '<td style="text-align:left;border-left:0px;padding10px;vertical-align:text-top;width:800px">';
            echo "<div style='font-weight:bold'>" . __('No blood relation or direct marital relation found') . "</div>";
        } else {
            echo '<td style="width:60px">&nbsp;</td>';
            echo '<td class="print_version" style="padding-left:50px;padding-right:10px;vertical-align:text-top;border-left:2px solid #bbbbbb;width:350px">';
            echo __('MARITAL RELATIONSHIP: ');
            echo "<br><br><div style='font-weight:bold;margin-bottom:10px'>" . __('No direct marital relation found') . "</div>";
        }

        echo '<hr style="width:100%;height:0.25em;color:darkblue;background-color:darkblue;" >';
        echo  __("You may wish to try finding a connection with the <span style='font-weight:bold'>Extended Marital Calculator</span> below.<br>
This will find connections that span over many marital relations and generations.<br>
Computing time will vary depending on the size of the tree and the distance between the two persons.<br>
For example, in a 10,000 person tree even the most distant persons will usually be found within 1-2 seconds.<br>
In a 75,000 person tree the most distant persons may take up to 8 sec to find.");
        echo '<br><br><input type="submit" name="extended" value="' . __('Perform extended marital calculation') . '" class="btn btn-sm btn-success">';
        echo "</td></tr></table>";
    } else {
        echo '</td></tr></table>';
    }

    echo '<br><br>';
}


function display_table()
{
    global $db_functions;
    global $table, $name1, $name2, $rel_arrayX, $rel_arrayY, $spouse, $rel_arrayspouseX, $rel_arrayspouseY, $famspouseX, $famspouseY;
    global $famX, $famY, $gednr, $gednr2;
    global $fampath, $tree_id, $link_cls, $uri_path;
    global $data_found;

    // *** Use person class to show names ***
    $pers_cls = new person_cls;

    $vars['pers_family'] = $famX;
    $linkX = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

    $vars['pers_family'] = $famspouseX;
    $linkSpouseX = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

    $vars['pers_family'] = $famY;
    $linkY = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

    $vars['pers_family'] = $famspouseY;
    $linkSpouseY = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

    //$border="border:1px solid #777777;";
    $border = "";

    if ($table == 1 or $table == 2) {
        if ($table == 1 and $data_found["foundY_gen"] == 1 and $spouse == '') {
            // father-son - no need for table
        } else if ($table == 2 and $data_found["foundX_gen"] == 1 and $spouse == '') {
            // son-father - no need for table
        } else {
            if ($spouse == 1) {
                $rel_arrayX = $rel_arrayspouseX;
            }
            if ($spouse == 2) {
                $rel_arrayY = $rel_arrayspouseY;
            }
            if ($spouse == 3) {
                $rel_arrayX = $rel_arrayspouseX;
                $rel_arrayY = $rel_arrayspouseY;
            }

            if ($table == 2) {
                $tempfound = $data_found["foundY_nr"];
                $data_found["foundY_nr"] = $data_found["foundX_nr"];
                $data_found["foundX_nr"] = $tempfound;
                $temprel = $rel_arrayY;
                $rel_arrayY = $rel_arrayX;
                $rel_arrayX = $temprel;
                $tempname = $name1;
                $name1 = $name2;
                $name2 = $tempname;
                $tempfam = $famspouseX;
                $famspouseX = $famspouseY;
                $famspouseY = $tempfam;
                $tempfamily = $famX;
                $famX = $famY;
                $famY = $tempfamily;
                $tempged = $gednr2;
                $gednr2 = $gednr;
                $gednr = $tempged;
            }

?>
            <br>
            <table class="newrel" style="border:0px;border-collapse:separate;border-spacing:3px 1px;">
                <tr>
                    <?php
                    if (($spouse == 1 and $table == 1) or ($spouse == 2 and $table == 2) or $spouse == 3) {
                        $persidDb = $db_functions->get_person($rel_arrayX[0][0]);
                        $name = $pers_cls->person_name($persidDb);
                        $personname = $name["name"];

                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td class="' . $ext_cls . '"  style="width:200px;text-align:center;' . $border . 'padding:2px"><a href="' . $linkSpouseX . 'main_person=' . $rel_arrayX[0][0] . '">' . $personname . '</a></td>';

                        $persidDb = $db_functions->get_person($gednr);
                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td style="border:0px;">&nbsp;&nbsp;X&nbsp;&nbsp;</td>';

                        echo '<td class="' . $ext_cls . '"  style="width:200px;text-align:center;' . $border . 'padding:2px"><a class="search" href="' . $linkX . "main_person=" . $gednr . '">' . $name1 . "</a></td>";
                        echo '</tr><tr>';
                        echo '<td style="border:0px;">&#8593;</td>';
                        echo '<td style="border:0px;">&nbsp;</td>';
                        echo '<td style="border:0px;">&nbsp;</td>';
                    } else {
                        $persidDb = $db_functions->get_person($rel_arrayX[0][0]);
                        $name = $pers_cls->person_name($persidDb);
                        $personname = $name["name"];

                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td class="' . $ext_cls . '"  style="width:200px;text-align:center;' . $border . 'padding:2px"><a class="search" href="' . $linkX . "main_person=" . $rel_arrayX[0][0] . '">' . $name1 . "</a></td>";
                        if (($spouse == 1 and $table == 2) or ($spouse == 2 and $table == 1)) {
                            echo '<td style="border:0px;">&nbsp;</td>';
                            echo '<td style="border:0px;">&nbsp;</td>';
                        }
                        echo '</tr><tr>';
                        echo '<td style="border:0px;">&#8595;</td>';
                    }

                    echo "</tr>";
                    $count = $data_found["foundY_nr"];
                    while ($count != 0) {
                        $persidDb = $db_functions->get_person($rel_arrayY[$count][0]);
                        $name = $pers_cls->person_name($persidDb);
                        $personname = $name["name"];

                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        if ($persidDb->pers_fams) {
                            $fams = $persidDb->pers_fams;
                            $tempfam = explode(";", $fams);
                            $fam = $tempfam[0];
                        } else {
                            $fam = $persidDb->pers_famc;
                        }
                        echo "<tr>";
                        $vars['pers_family'] = $fam;
                        $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px"><a href="' . $link . "main_person=" . $persidDb->pers_gedcomnumber . '">' . $personname . "</a></td>";

                        if ($spouse == 1 or $spouse == 2 or $spouse == 3) {
                            echo '<td style="border:0px;">&nbsp;</td>';
                        }
                        echo '</tr><tr><td style="border:0px;">&#8593;</td>';
                        $count = $rel_arrayY[$count][2];
                    }
                    ?>
                </tr>
                <tr>
                    <?php
                    if (($spouse == 1 and $table == 2) or ($spouse == 2 and $table == 1) or $spouse == 3) {
                        $persidDb = $db_functions->get_person($rel_arrayY[0][0]);
                        $name = $pers_cls->person_name($persidDb);
                        $personname = $name["name"];

                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px"><a href="' . $linkSpouseY . "main_person=" . $rel_arrayY[0][0] . '">' . $personname . "</a></td>";

                        $persidDb = $db_functions->get_person($gednr2);
                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td style="border:0px;">&nbsp;&nbsp;X&nbsp;&nbsp;</td>';

                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px"><a class="search" href="' . $linkY . "main_person=" . $gednr2 . '">' . $name2 . "</a></td>";
                    } else {
                        $persidDb = $db_functions->get_person($rel_arrayY[0][0]);
                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px"><a class="search" href="' . $linkY . "main_person=" . $rel_arrayY[0][0] . '">' . $name2 . "</a></td>";
                        if (($spouse == 1 and $table == 1) or ($spouse == 2 and $table == 2)) {
                            echo '<td style="border:0px;">&nbsp;</td>';
                            echo '<td style="border:0px;">&nbsp;</td>';
                        }
                    }
                    ?>
                </tr>
            </table>
        <?php
        }
    }
    if ($table == 3 or $table == 4 or $table == 5 or $table == 6) {
        $rowcount = max($data_found["foundX_gen"], $data_found["foundY_gen"]);
        $countX = $data_found["foundX_nr"];
        $countY = $data_found["foundY_nr"];
        $name1_done = 0;
        $name2_done = 0;

        $colspan = 3;
        if ($spouse == 1) {
            $rel_arrayX = $rel_arrayspouseX;
        }
        if ($spouse == 2) {
            $rel_arrayY = $rel_arrayspouseY;
        }
        if ($spouse == 3) {
            $rel_arrayX = $rel_arrayspouseX;
            $rel_arrayY = $rel_arrayspouseY;
        }

        $persidDb = $db_functions->get_person($rel_arrayX[$data_found["foundX_match"]][0]);
        $name = $pers_cls->person_name($persidDb);
        $personname = $name["name"];

        if ($persidDb->pers_sexe == "M") {
            $ext_cls = "extended_man ";
        } else {
            $ext_cls = "extended_woman ";
        }

        if ($persidDb->pers_fams) {
            $fams = $persidDb->pers_fams;
            $tempfam = explode(";", $fams);
            $fam = $tempfam[0];
        } else {
            $fam = $persidDb->pers_famc;
        }
        $vars['pers_family'] = $fam;
        $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
        ?>

        <br>
        <table class="newrel" style="border-collapse:separate;border-spacing:3px 1px;">
            <tr>
                <?php if ($spouse == 1 or $spouse == 3) { ?>
                    <td style="border:0px;">&nbsp;</td>
                    <td style="border:0px;">&nbsp;</td>
                <?php } ?>
                <td class="<?= $ext_cls; ?>" style="width:200px;text-align:center;<?= $border; ?>padding:2px" colspan="<?= $colspan; ?>">
                    <a href="<?= $link; ?>main_person=<?= $persidDb->pers_gedcomnumber; ?>"><?= $personname; ?></a>
                </td>

                <?php if ($spouse == 2 or $spouse == 3) { ?>
                    <td style="border:0px;">&nbsp;</td>
                    <td style="border:0px;">&nbsp;</td>
                <?php } ?>
            </tr>

            <tr>
                <?php if ($spouse == 1 or $spouse == 3) { ?>
                    <td style="border:0px;">&nbsp;</td>
                    <td style="border:0px;">&nbsp;</td>
                <?php } ?>
                <td style="border:0px;">&#8593;</td>
                <td style="border:0px;">&nbsp;</td>
                <td style="border:0px;">&#8595;</td>
                <?php if ($spouse == 2 or $spouse == 3) { ?>
                    <td style="border:0px;">&nbsp;</td>
                    <td style="border:0px;">&nbsp;</td>
                <?php } ?>
            </tr>
            <?php

            for ($e = 1; $e <= $rowcount; $e++) {
                if ($countX != 0) {
                    $persidDb = $db_functions->get_person($rel_arrayX[$countX][0]);
                    $name = $pers_cls->person_name($persidDb);
                    $personname = $name["name"];

                    if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                    else $ext_cls = "extended_woman ";

                    echo "<tr>";
                    if ($spouse == 1 or $spouse == 3) {
                        echo '<td style="border:0px;">&nbsp;</td>';
                        echo '<td style="border:0px;">&nbsp;</td>';
                    }
                    if ($persidDb->pers_fams) {
                        $fams = $persidDb->pers_fams;
                        $tempfam = explode(";", $fams);
                        $fam = $tempfam[0];
                    } else {
                        $fam = $persidDb->pers_famc;
                    }
                    $vars['pers_family'] = $fam;
                    $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                    echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px"><a href="' . $link . "main_person=" . $persidDb->pers_gedcomnumber . '">' . $personname . "</a></td>";

                    $countX = $rel_arrayX[$countX][2];
                } elseif ($name1_done == 0) {
                    echo "<tr>";
                    if ($spouse == 1 or $spouse == 3) {
                        $persidDb = $db_functions->get_person($rel_arrayX[0][0]);
                        $name = $pers_cls->person_name($persidDb);
                        $personname = $name["name"];

                        if ($persidDb->pers_sexe == "M") $ext_cls2 = "extended_man ";
                        else $ext_cls2 = "extended_woman ";

                        if ($persidDb->pers_fams) {
                            $fams = $persidDb->pers_fams;
                            $tempfam = explode(";", $fams);
                            $fam = $tempfam[0];
                        } else {
                            $fam = $persidDb->pers_famc;
                        }

                        $persidDb2 = $db_functions->get_person($gednr);
                        if ($persidDb2->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;padding:2px"><a class="search" href="' . $linkX . "main_person=" . $gednr . '">' . $name1 . "</a>";

                        echo '<td style="border:0px;">&nbsp;&nbsp;X&nbsp;&nbsp;</td>';

                        echo '<td  class="' . $ext_cls2 . '" style="width:200px;text-align:center;padding:2px" class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px"><a href="' . $fampath . $fam . "&amp;main_person=" . $persidDb->pers_gedcomnumber . '">' . $personname . "</a></td>";
                    } else {
                        $persidDb2 = $db_functions->get_person($gednr);
                        if ($persidDb2->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";
                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;padding:2px"><a class="search" href="' . $linkX . "main_person=" . $gednr . '">' . $name1 . "</a></td>";
                    }
                    $name1_done = 1;
                } else {
                    echo '<tr>';
                    if ($spouse == 1 or $spouse == 3) {
                        echo '<td style="border:0px;">&nbsp;</td>';
                        echo '<td style="border:0px;">&nbsp;</td>';
                    }
                    echo '<td style="border:0px;">&nbsp;</td>';
                }

                if ($countY != 0) {
                    $persidDb = $db_functions->get_person($rel_arrayY[$countY][0]);
                    $name = $pers_cls->person_name($persidDb);
                    $personname = $name["name"];

                    if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                    else $ext_cls = "extended_woman ";

                    echo '<td style="border:0px;width:70px">&nbsp;</td>';

                    if ($persidDb->pers_fams) {
                        $fams = $persidDb->pers_fams;
                        $tempfam = explode(";", $fams);
                        $fam = $tempfam[0];
                    } else {
                        $fam = $persidDb->pers_famc;
                    }
                    $vars['pers_family'] = $fam;
                    $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                    echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px"><a href="' . $link . "main_person=" . $persidDb->pers_gedcomnumber . '">' . $personname . "</a></td>";

                    if ($spouse == 2 or $spouse == 3) {
                        echo '<td style="border:0px;">&nbsp;</td>';
                        echo '<td style="border:0px;">&nbsp;</td>';
                    }
                    echo "</tr>";
                    $countY = $rel_arrayY[$countY][2];
                } elseif ($name2_done == 0) {
                    if ($spouse == 2 or $spouse == 3) {
                        $persidDb = $db_functions->get_person($rel_arrayY[0][0]);
                        $name = $pers_cls->person_name($persidDb);
                        $personname = $name["name"];

                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td style="border:0px;width:70px">&nbsp;</td>';

                        if ($persidDb->pers_fams) {
                            $fams = $persidDb->pers_fams;
                            $tempfam = explode(";", $fams);
                            $fam = $tempfam[0];
                        } else {
                            $fam = $persidDb->pers_famc;
                        }
                        $vars['pers_family'] = $fam;
                        $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;padding:2px"><a href="' . $link . "main_person=" . $persidDb->pers_gedcomnumber . '">' . $personname . "</a></td>";

                        echo '<td style="border:0px;">&nbsp;&nbsp;X&nbsp;&nbsp;</td>';

                        $persidDb = $db_functions->get_person($gednr2);
                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;padding:2px"><a class="search" href="' . $linkY . "main_person=" . $gednr2 . '">' . $name2 . "</a></td>";
                    } else {
                        echo '<td style="border:0px;width:70px">&nbsp;</td>';

                        $persidDb = $db_functions->get_person($gednr2);
                        if ($persidDb->pers_sexe == "M") $ext_cls = "extended_man ";
                        else $ext_cls = "extended_woman ";

                        echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;padding:2px"><a class="search" href="' . $linkY . "main_person=" . $gednr2 . '">' . $name2 . "</a></td>";
                    }
                    echo "</tr>";
                    $name2_done = 1;
                } else {
                    echo '<td style="border:0px;width:70px>">&nbsp;</td>';
                    echo '<td style="border:0px;">&nbsp;</td>';
                    if ($spouse == 2 or $spouse == 3) {
                        echo '<td style="border:0px;">&nbsp;</td>';
                        echo '<td style="border:0px;">&nbsp;</td>';
                    }
                    echo "</tr>";
                }

            ?>
                <tr>
                    <?php
                    if ($spouse == 1 or $spouse == 3) {
                        echo '<td style="border:0px;">&nbsp;</td>';
                        echo '<td style="width=50px;border:0px;">&nbsp;</td>';
                    }
                    if ($name1_done == 0) {
                        echo '<td style="border:0px;">&#8593;</td>';
                    } else {
                        echo '<td style="border:0px;">&nbsp;</td>';
                    }
                    echo '<td style="border:0px;">&nbsp;</td>';
                    if ($name2_done == 0) {
                        echo '<td style="border:0px;">&#8595;</td>';
                    } else {
                        echo '<td style="border:0px;">&nbsp;</td>';
                    }
                    if ($spouse == 2 or $spouse == 3) {
                        echo '<td style="border:0px;">&nbsp;</td>';
                        echo '<td style="border:0px;">&nbsp;</td>';
                    }
                    ?>
                </tr>
            <?php } ?>
        </table>
    <?php
    }
}

/* the extended marital calculator computation */
function map_tree($pers_array, $pers_array2)
{
    // in first loop $pers_array and $pers_array2 hold persons A and B
    // in the next loop it will contain the parents, children and spouses of persons A and B, where they exist etc
    // the algorithm starts simultaneously from person A and person B in expanding circles until a common person is found (= connection found)
    // or until either person A or B runs out of persons (= no connection exists)

    global $db_functions, $globaltrack, $globaltrack2, $count;
    global $countfunc, $global_array;
    global $data;
    $count++;
    if ($count > 400000) {
        echo "Database too large!!!!";
        exit;
    }
    $countfunc++;
    //$tree = safe_text_db($_SESSION['tree_prefix']);

    $work_array = array();
    $work_array2 = array();

    // build closest circle around person A (parents, children, spouse(s))
    foreach ($pers_array as $value) {   // each array item has 4 parts, separated by "@": I124@par@I15@I54;I46;I326;I123;I15
        $params = explode("@", $value);
        $persged = $params[0]; // the gedcomnumber of this person
        $refer = $params[1];   // the referrer type: par (parent), spo (spouse), chd (child) - this means who was the previous person that called this one
        $callged = $params[2]; // the gedcomnumber of the referrer (in case referrer is child: gedcomnumber;famc gedcomnumber)
        $pathway = $params[3]; // the path from person A to this person (gedcomnumbers separated by semi-colon)

        if ($refer == "chd") {
            $callarray = explode(";", $callged);    // [0] = gedcomnumber of referring child, [1] = famc gedcomnumber of referring child
        } else {
            $callarray[0] = $callged;
        }

        $persDb = $db_functions->get_person($persged);
        if ($persDb == false) {
            echo "NO SUCH PERSON:" . "ref=" . $refer . "persged=" . $persged . "callged=" . $callged . "$$";
            return (false);
        }

        if ($refer == "fst") {
            $globaltrack .= $persDb->pers_gedcomnumber . "@";
        }
        // find parents
        if (isset($persDb->pers_famc) and $persDb->pers_famc != "" and $refer != "par") {
            $famcDb = $db_functions->get_family($persDb->pers_famc);
            if ($famcDb == false) {
                echo "NO SUCH FAMILY";
                return;
            }

            if (isset($famcDb->fam_man) and $famcDb->fam_man != "" and $famcDb->fam_man != "0" and strpos($globaltrack, $famcDb->fam_man . "@") === false) {
                if (strpos($_SESSION['next_path'], $famcDb->fam_man . "@") === false) {
                    $work_array[] = $famcDb->fam_man . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_man;
                    $global_array[] = $famcDb->fam_man . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_man;
                }
                $count++;
                $globaltrack .= $famcDb->fam_man . "@";
            }
            if (isset($famcDb->fam_woman) and $famcDb->fam_woman != "" and $famcDb->fam_woman != "0" and strpos($globaltrack, $famcDb->fam_woman . "@") === false) {
                if (strpos($_SESSION['next_path'], $famcDb->fam_woman . "@") === false) {
                    $work_array[] = $famcDb->fam_woman . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_woman;
                    $global_array[] = $famcDb->fam_woman . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_woman;
                }
                $count++;
                $globaltrack .= $famcDb->fam_woman . "@";
            }
        }

        if (isset($persDb->pers_fams) and $persDb->pers_fams != "") {
            $famsarray = explode(";", $persDb->pers_fams);

            foreach ($famsarray as $value) {
                if ($refer == "spo" and $value == $callged) continue;
                if ($refer == "fst" and $_SESSION['couple'] == $value) continue;
                $famsDb = $db_functions->get_family($value);
                if ($refer == "chd" and $famsDb->fam_woman == $persDb->pers_gedcomnumber and isset($famsDb->fam_man) and $famsDb->fam_man != "" and $famsDb->fam_gedcomnumber == $callarray[1]) {
                    continue;
                }
                // find children
                if (isset($famsDb->fam_children) and $famsDb->fam_children != "") {
                    $childarray = explode(";", $famsDb->fam_children);
                    foreach ($childarray as $value) {
                        if ($refer == "chd" and $callarray[0] == $value) continue;
                        if (strpos($globaltrack, $value . "@") === false) {
                            if (strpos($_SESSION['next_path'], $value . "@") === false) {
                                $work_array[] = $value . "@par@" . $persged . "@" . $pathway . ";" . "par" . $value;
                                $global_array[] = $value . "@par@" . $persged . "@" . $pathway . ";" . "par" . $value;
                            }
                            $count++;
                            $globaltrack .= $value . "@";
                        }
                    }
                }
            }
            // find spouses
            foreach ($famsarray as $value) {
                if ($refer == "chd" and $value == $callarray[1]) continue;
                if ($refer == "spo" and $value == $callged) continue;
                if ($refer == "fst" and $_SESSION['couple'] == $value) continue;
                $famsDb = $db_functions->get_family($value);
                if ($famsDb->fam_man == $persDb->pers_gedcomnumber) {
                    if (isset($famsDb->fam_woman) and $famsDb->fam_woman != "" and $famsDb->fam_woman != "0" and strpos($globaltrack, $famsDb->fam_woman . "@") === false) {
                        if (strpos($_SESSION['next_path'], $famsDb->fam_woman . "@") === false) {
                            $work_array[] = $famsDb->fam_woman . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_woman;
                            $global_array[] = $famsDb->fam_woman . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_woman;
                        }
                        $count++;
                        $globaltrack .= $famsDb->fam_woman . "@";
                    }
                } else {
                    if (isset($famsDb->fam_man) and $famsDb->fam_man != "" and $famsDb->fam_man != "0" and strpos($globaltrack, $famsDb->fam_man . "@") === false) {
                        if (strpos($_SESSION['next_path'], $famsDb->fam_man . "@") === false) {
                            $work_array[] = $famsDb->fam_man . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_man;
                            $global_array[] = $famsDb->fam_man . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_man;
                        }
                        $count++;
                        $globaltrack .= $famsDb->fam_man . "@";
                    }
                }
            }
        }
    }
    // build closest circle around person B (parents, children, spouse(s))
    foreach ($pers_array2 as $value) {
        $params = explode("@", $value);
        $persged = $params[0];
        $refer = $params[1];
        $callged = $params[2];
        $pathway = $params[3];

        if ($refer == "chd") {
            $callarray = explode(";", $callged);
        } else {
            $callarray[0] = $callged;
        }

        $persDb = $db_functions->get_person($persged);
        if ($persDb == false) {
            echo "NO SUCH PERSON:" . "ref=" . $refer . "persged=" . $persged . "callged=" . $callged . "$$";
            return (false);
        }

        if ($refer == "fst") {
            $globaltrack2 .= $persDb->pers_gedcomnumber . "@";
        }

        if (isset($persDb->pers_famc) and $persDb->pers_famc != "" and $refer != "par") {
            $famcDb = $db_functions->get_family($persDb->pers_famc);
            if ($famcDb == false) {
                echo "NO SUCH FAMILY";
                return;
            }
            if (isset($famcDb->fam_man) and $famcDb->fam_man != "" and $famcDb->fam_man != "0") {
                $var1 = strpos($_SESSION['next_path'], $famcDb->fam_man . "@");
                if (strpos($globaltrack, $famcDb->fam_man . "@") !== false  and $var1 === false) {
                    $totalpath = join_path($global_array, $pathway, $famcDb->fam_man, "chd");
                    $_SESSION['next_path'] .= $famcDb->fam_man . "@";
                    display_result($totalpath);
                    return ($famcDb->fam_man);
                }
                if (strpos($globaltrack2, $famcDb->fam_man . "@") === false) {
                    if ($var1 === false) {
                        $work_array2[] = $famcDb->fam_man . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_man;
                    }
                    $count++;
                    $globaltrack2 .= $famcDb->fam_man . "@";
                }
            }
            if (isset($famcDb->fam_woman) and $famcDb->fam_woman != "" and $famcDb->fam_woman != "0") {
                $var2 = strpos($_SESSION['next_path'], $famcDb->fam_woman . "@");
                if (strpos($globaltrack, $famcDb->fam_woman . "@") !== false and $var2 === false) {
                    $totalpath = join_path($global_array, $pathway, $famcDb->fam_woman, "chd");
                    $_SESSION['next_path'] .= $famcDb->fam_woman . "@";
                    display_result($totalpath);
                    return ($famcDb->fam_woman);
                }
                if (strpos($globaltrack2, $famcDb->fam_woman . "@") === false) {
                    if ($var2 === false) {
                        $work_array2[] = $famcDb->fam_woman . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_woman;
                    }
                    $count++;
                    $globaltrack2 .= $famcDb->fam_woman . "@";
                }
            }
        }

        if (isset($persDb->pers_fams) and $persDb->pers_fams != "") {
            $famsarray = explode(";", $persDb->pers_fams);
            foreach ($famsarray as $value) {
                if ($refer == "spo" and $value == $callged) continue;
                if ($refer == "fst" and $_SESSION['couple'] == $value) continue;
                $famsDb = $db_functions->get_family($value);
                if ($refer == "chd" and $famsDb->fam_woman == $persDb->pers_gedcomnumber and isset($famsDb->fam_man) and $famsDb->fam_man != "" and $famsDb->fam_gedcomnumber == $callarray[1]) {
                    continue;
                }
                if (isset($famsDb->fam_children) and $famsDb->fam_children != "") {
                    $childarray = explode(";", $famsDb->fam_children);
                    foreach ($childarray as $value) {
                        if ($refer == "chd" and $callarray[0] == $value) continue;
                        $var3 = strpos($_SESSION['next_path'], $value . "@");
                        if (strpos($globaltrack, $value . "@") !== false and $var3 === false) {
                            $totalpath = join_path($global_array, $pathway, $value, "par");
                            $_SESSION['next_path'] .= $value . "@";
                            display_result($totalpath);
                            return ($value);
                        }
                        if (strpos($globaltrack2, $value . "@") === false) {
                            if ($var3 === false) {
                                $work_array2[] = $value . "@par@" . $persged . "@" . $pathway . ";" . "par" . $value;
                            }
                            $count++;
                            $globaltrack2 .= $value . "@";
                        }
                    }
                }
            }
            foreach ($famsarray as $value) {
                if ($refer == "chd" and $value == $callarray[1]) continue;
                if ($refer == "spo" and $value == $callged) continue;
                if ($refer == "fst" and $_SESSION['couple'] == $value) {
                    continue;
                }
                $famsDb = $db_functions->get_family($value);
                if ($famsDb->fam_man == $persDb->pers_gedcomnumber) {
                    if (isset($famsDb->fam_woman) and $famsDb->fam_woman != "" and $famsDb->fam_woman != "0") {
                        $var4 = strpos($_SESSION['next_path'], $famsDb->fam_woman . "@");
                        if (strpos($globaltrack, $famsDb->fam_woman . "@") !== false and $var4 === false) {
                            $totalpath = join_path($global_array, $pathway, $famsDb->fam_woman, "spo");
                            $_SESSION['next_path'] .= $famsDb->fam_woman . "@";
                            display_result($totalpath);
                            return ($famsDb->fam_woman);
                        }
                        if (strpos($globaltrack2, $famsDb->fam_woman . "@") === false) {
                            if ($var4 === false) {
                                $work_array2[] = $famsDb->fam_woman . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_woman;
                            }
                            $count++;
                            $globaltrack2 .= $famsDb->fam_woman . "@";
                        }
                    }
                } elseif ($famsDb->fam_woman == $persDb->pers_gedcomnumber) {
                    if (isset($famsDb->fam_man) and $famsDb->fam_man != "" and $famsDb->fam_man != "0") {
                        $var5 = strpos($_SESSION['next_path'], $famsDb->fam_man . "@");
                        if (strpos($globaltrack, $famsDb->fam_man . "@") !== false and $var5 === false) {
                            $totalpath = join_path($global_array, $pathway, $famsDb->fam_man, "spo");
                            $_SESSION['next_path'] .= $famsDb->fam_man . "@";
                            display_result($totalpath);
                            return ($famsDb->fam_man);
                        }
                        if (strpos($globaltrack2, $famsDb->fam_man . "@") === false) {
                            if ($var5 === false) {
                                $work_array2[] = $famsDb->fam_man . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_man;
                            }
                            $count++;
                            $globaltrack2 .= $famsDb->fam_man . "@";
                        }
                    }
                }
            }
        }
    }
    if (isset($work_array[0]) and isset($work_array2[0])) {
        // no common person was found but both A and B still have a wider circle to expand -> call this function again
        map_tree($work_array, $work_array2);
    } elseif (!isset($_SESSION['next_path'])) {
        echo "<br><span style='font-weight:bold;font-size:120%'>&nbsp;&nbsp;" . __("These persons are not related in any way.") . "&nbsp;&nbsp;<br><br>";
    } else {
        echo "<br><span style='font-weight:bold;font-size:120%'>&nbsp;&nbsp;" . __("No further paths found.") . "&nbsp;&nbsp;<br><br>";
    }
}

function join_path($workarr, $path2, $pers2, $ref)
{
    // we have two trails. one from person A to the common person and one from person B to the common person (A ---> common <---- B)
    // we have to create one trail from A to B
    // since the second trail is reverse (from B to the common person) it first has to be turned around, including changing the relation to previous and next person

    // $workarr is the array with all trails from person A 
    // we have to find the trail that contains the common person ($pers2)
    foreach ($workarr as $value) {
        if (strpos($value . ";", $pers2 . ";") === false) {
            continue;
        }
        $path1 = substr($value, strrpos($value, "@") + 1);  // found the right trail
    }
    $fstcommon = substr($path1, strpos($path1 . ";", $pers2 . ";") - 3, 3); // find the common person as appears in the trail from person A ("parI3120")

    // now turn around the second trail and adjust par, chd, spo values accordingly
    $secpath = explode(";", $path2);
    $new_path2 = "";
    $changepath = array();
    $commonpers = ";" . $fstcommon . $pers2;
    if ($ref == "par" and $fstcommon == "par") {
        // the common person is a child of both sides - discard child and make right person spouse of left!
        $changepath[count($secpath) - 1] = "spo" . substr($secpath[count($secpath) - 1], 3);
        $commonpers = "";
        $_SESSION['next_path'] .= substr($secpath[count($secpath) - 1], 3) . "@"; // add parent from side B to ignore string for next path
        $par1str = substr($path1, 0, strrpos($path1, ";"));  // first take off last (=common) person
        $_SESSION['next_path'] .= substr($par1str, strrpos($par1str, ";") + 4) . "@";     // add parent from side A to ignore string for next path
    } elseif ($ref == "par") $changepath[count($secpath) - 1] = "chd" . substr($secpath[count($secpath) - 1], 3);
    elseif ($ref == "chd") $changepath[count($secpath) - 1] = "par" . substr($secpath[count($secpath) - 1], 3);
    else $changepath[count($secpath) - 1] = "spo" . substr($secpath[count($secpath) - 1], 3);
    for ($w = count($secpath) - 1; $w > 0; $w--) {
        if (substr($secpath[$w], 0, 3) == "par") $changepath[$w - 1] = "chd" . substr($secpath[$w - 1], 3);
        elseif (substr($secpath[$w], 0, 3) == "chd") $changepath[$w - 1] = "par" . substr($secpath[$w - 1], 3);
        else $changepath[$w - 1] = "spo" . substr($secpath[$w - 1], 3);
    }
    for ($w = count($changepath) - 1; $w >= 0; $w--) {
        $new_path2 .= ";" . $changepath[$w];
    }
    $result = substr($path1, 0, strpos($path1, $pers2) - 4) . $commonpers . $new_path2;  // the entire trail from person A to B
    return ($result);
}


/* displays result of extended marital calculator */
function display_result($result)
{
    // $result holds the entire track of persons from person A to person B
    // this string is made up of items sperated by ";"
    // each items starts with "par" (parent), "chd" (child) or "spo" (spouse), followed by the gedcomnumber of the person
    // example: parI232;parI65;chdI2304;spoI212;parI304
    // the par-chd-spo prefixes indicate if the person was called up by his parent, child or spouse so we can later create the graphical display

    global $db_functions, $data;
    ?>

    <div class="print_version" style="padding:3px;width:auto;background-color:#eeeeee">
        <input type="submit" name="next_path" value="<?= __('Try to find another path'); ?>" class="btn btn-sm btn-success">
        &nbsp;&nbsp;<?= __('(With each consecutive search the path may get longer and computing time may increase!)'); ?>
    </div>
    <?php

    $map = array();    // array that will hold all data needed for the graphical display

    $tracks = explode(";", $result); // $tracks is array with each person in the trail

    /* initialize  */
    for ($x = 0; $x < count($tracks); $x++) {
        $map[$x][0] = "1"; /* x value in graphical display */
        $map[$x][1] = "1"; /* y value in graphical display */
        $map[$x][2] = "1"; /* colspan value (usually 1, turns 2 for two column parent) */
        $map[$x][3] = substr($tracks[$x], 0, 3); /* call value (oar, chd, spo) */
        $map[$x][4] = substr($tracks[$x], 3); /* gedcomnumber value */
    }

    $xval = 1;
    $yval = 1;
    $miny = 1;
    $maxy = 1;
    $marrsign = array();

    // fill map array
    for ($x = 0; $x < count($tracks); $x++) {
        $ged = substr($tracks[$x], 3);    // gedcomnumber
        $cal = substr($tracks[$x], 0, 3);  // par, chd, spo
        if ($cal == "fst") {
            continue;
        }
        if ($cal == "spo") {
            $marrsign[$xval + 1] = $yval;
            $xval += 2;
            $map[$x][0] = $xval;
            $map[$x][1] = $yval;
        }
        if ($cal == "chd") {
            $yval--;
            if ($yval < $miny) $miny = $yval;
            $map[$x][0] = $xval;
            $map[$x][1] = $yval;
            if (isset($map[$x + 1]) and $map[$x + 1][3] == "par") $map[$x][2] = 2;
        }
        if ($cal == "par") {
            $yval++;
            if ($yval > $maxy) $maxy = $yval;
            if ($map[$x - 1][3] == "chd") $xval++;
            $map[$x][0] = $xval;
            $map[$x][1] = $yval;
        }
    }
    if ($miny < 1) {
        for ($x = 0; $x < count($map); $x++) {
            $map[$x][1] += (1 + abs($miny));
            if ($map[$x][1] > $maxy)    $maxy = $map[$x][1];
        }
        if (isset($marrsign)) {
            foreach ($marrsign as $key => $value) {
                $marrsign[$key] += (1 + abs($miny));
            }
        }
    }
    ?>

    <!-- the following code displays the graphical view of the found trail -->
    <br>
    <table style="border:0px;border-collapse:separate;border-spacing:30px 1px;">
        <?php for ($a = 1; $a <= $maxy; $a++) { ?>
            <tr>
                <?php
                $nextline = "";
                for ($b = 1; $b <= $xval; $b++) {
                    $colsp = false;
                    $marr = false;
                    for ($x = 0; $x < count($map); $x++) {
                        if ($map[$x][0] == $b and $map[$x][1] == $a) {
                            $color = "#8ceffd";
                            $border = "border:1px solid #777777;";

                            $ancDb = $db_functions->get_person($map[$x][4]);
                            if ($ancDb->pers_sexe == "M") {
                                $ext_cls = "extended_man ";
                            } else {
                                $ext_cls = "extended_woman ";
                            }

                            // person A and B (first and last) get thicker border
                            if ($map[$x][4] == $data["person1"] or $map[$x][4] == $data["person2"]) {
                                $color = "#72fe95";
                                $border = "border:2px solid #666666;";
                            }
                            if ($map[$x][2] == 2) {
                                $b++;
                                echo '<td class="' . $ext_cls . '" colspan=2 style="width:200px;text-align:center;' . $border . 'padding:2px">';
                                $nextline .= "&#8593;@&#8595;@";   // up and down arrows under two column parent
                            } elseif (isset($map[$x + 1][3]) and $map[$x + 1][3] == "par") {
                                $nextline .= "&#8595;@";  // down arrow
                                echo '<td class="' . $ext_cls . '"  style="width:200px;text-align:center;' . $border . 'padding:2px">';
                            } elseif (isset($map[$x][3]) and $map[$x][3] == "chd") {
                                $nextline .= "&#8593;@";  // up arrow
                                echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px">';
                            } else {
                                $nextline .= "&nbsp;@";  // empty box
                                echo '<td class="' . $ext_cls . '" style="width:200px;text-align:center;' . $border . 'padding:2px">';
                            }

                            $pers_cls = new person_cls;
                            $name = $pers_cls->person_name($ancDb);
                            $personname = $name["name"];
                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $url = $pers_cls->person_url2($ancDb->pers_tree_id, $ancDb->pers_famc, $ancDb->pers_fams, $ancDb->pers_gedcomnumber);
                            echo "<a href='" . $url . "'>" . $personname . "</a>";

                            $colsp = true;
                        }
                    }
                    if ($colsp == false) {
                        if (isset($marrsign[$b]) and $marrsign[$b] == $a) {  // display the X sign between two married people
                            echo '<td style="font-weight:bold;font-size:130%;width:10px;text-align:center;border:0px;padding:0px">X';
                        } else {
                            echo '<td style="width:10px;text-align:center;border:0px;padding:0px">';
                        }
                        $nextline .= "&nbsp;@";
                    }
                    echo "</td>";
                }
                ?>
            </tr>

            <!-- The following code places a row with arrows (or blanks) under a row with name boxes -->
            <?php if ($a != $maxy) { ?>
                <tr>
                    <?php
                    $nextline = substr($nextline, 0, -1);
                    $next = explode("@", $nextline);
                    foreach ($next as $value) {
                    ?>
                        <td style='padding:2px;color:black;width:10px;font-weight:bold;font-size:140%;text-align:center;'>
                            <?= $value; ?>
                        </td>
                    <?php } ?>
                </tr>
        <?php
            }
        }
        ?>
    </table>
<?php
}
