<?php

/**
 * Sep. 2014 Huub: added this script to HuMo-genealogy.
 */

// *** Show number of persons and pages ***
$item = 0;
if (isset($_GET['item'])) {
    $item = $_GET['item'];
}
$start = 0;
if (isset($_GET["start"])) {
    $start = $_GET["start"];
}
$nr_persons = $humo_option['show_persons'];
$person_result = $dbh->query($data["query"] . " LIMIT " . $item . "," . $nr_persons);

$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$safeTextShow = new \Genealogy\Include\SafeTextShow();


//TODO use COUNT
//if ($count_qry) {
//    // *** Use COUNT command to calculate nr. of persons in simple queries (faster than php num_rows and in simple queries faster than SQL_CAL_FOUND_ROWS) ***
//    $result = $dbh->query($count_qry);
//    $resultDb = $result->fetch(PDO::FETCH_OBJ);
//    $count_persons = $resultDb->teller;
//} else {
// *** USE SQL_CALC_FOUND_ROWS for complex queries (faster than mysql count) ***
$result = $dbh->query("SELECT FOUND_ROWS() AS 'found_rows'");
$rows = $result->fetch();
$count_persons = $rows['found_rows'];
//}

$link = $processLinks->get_link($uri_path, 'list_places_families', $tree_id);
?>

<!-- Search places -->
<form method="post" action="<?= $link; ?>">
    <div class="p-2 me-sm-2 genealogy_search">
        <div class="row mb-2">
            <div class="col-2">
                <div class="form-check">
                    <input type="Checkbox" name="select_marriage_notice" value="1" <?= $data["select_marriage_notice"] == '1' ? 'checked' : ''; ?> class="form-check-input">
                    <span class="place_index_selected" style="float:none;"><?= __('&infin;'); ?></span>
                    <?= __('Marriage notice'); ?></span>
                </div>
            </div>

            <div class="col-2">
                <div class="form-check">
                    <input type="Checkbox" name="select_marriage" value="1" <?= $data["select_marriage"] == '1' ? 'checked' : ''; ?> class="form-check-input">
                    <span class="place_index_selected" style="float:none;"><?= __('X'); ?></span>
                    <?= __('Marriage'); ?></span>
                </div>
            </div>

            <div class="col-2">
                <div class="form-check">
                    <input type="Checkbox" name="select_marriage_notice_religious" value="1" <?= $data["select_marriage_notice_religious"] == '1' ? 'checked' : ''; ?> class="form-check-input">
                    <span class="place_index_selected" style="float:none;"><?= __('o'); ?></span>
                    <?= __('Married notice (religious)'); ?></span>
                </div>
            </div>

            <div class="col-2">
                <div class="form-check">
                    <input type="Checkbox" name="select_marriage_religious" value="1" <?= $data["select_marriage_religious"] == '1' ? 'checked' : ''; ?> class="form-check-input">
                    <span class="place_index_selected" style="float:none;"><?= __('x'); ?></span>
                    <?= __('Married (religious)'); ?></span><br clear="all">
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-1">
                <label for="part_place_name" class="col-form-label">
                    <?= __('Find place'); ?>:
                </label>
            </div>

            <div class="col-2">
                <select id="part_place_name" name="part_place_name" aria-label="<?= __('Find place'); ?>" class="form-select form-select-sm">
                    <option value="contains"><?= __('Contains'); ?></option>
                    <option value="equals" <?php if ($data["part_place_name"] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                    <option value="starts_with" <?php if ($data["part_place_name"] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                </select>
            </div>

            <div class="col-2">
                <input type="text" name="place_name" value="<?= $safeTextShow->safe_text_show($data["place_name"]); ?>" size="15" class="form-control form-control-sm">
            </div>

            <input type="submit" value="<?= __('Search'); ?>" name="B1" class="col-sm-1 btn btn-sm btn-success">
        </div>

    </div>
</form>

<?php
$uri_path_string = $processLinks->get_link($uri_path, 'list_places_families', $tree_id, true);

// *** Check for search results ***
if ($person_result->rowCount() == 0) {
    //
} else {
    // "<="
    $data["previous_link"] = '';
    $data["previous_status"] = '';
    if ($start > 1) {
        $start2 = $start - 20;
        $calculated = ($start - 2) * $nr_persons;
        $data["previous_link"] = $uri_path_string . "start=" . $start2 . "&amp;item=" . $calculated;
    }
    if ($start <= 0) {
        $start = 1;
    }
    if ($start == '1') {
        $data["previous_status"] = 'disabled';
    }

    // 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
    for ($i = $start; $i <= $start + 19; $i++) {
        $calculated = ($i - 1) * $nr_persons;
        if ($calculated < $count_persons) {
            $data["page_nr"][] = $i;
            if ($item == $calculated) {
                $data["page_status"][$i] = 'active';
            } else {
                $data["page_status"][$i] = '';
            }
            $data["page_link"][$i] = $uri_path_string . "start=" . $start . "&amp;item=" . $calculated;
        }
    }

    // "=>"
    $data["next_link"] = '';
    $data["next_status"] = '';
    $calculated = ($i - 1) * $nr_persons;
    if ($calculated < $count_persons) {
        $data["next_link"] = $uri_path_string . "start=" . $i . "&amp;item=" . $calculated;
    } else {
        $data["next_status"] = 'disabled';
    }
}
?>

<div class="index_list1">
    <?php
    echo $count_persons . ' ' . __('families found.');

    // *** Normal or expanded list ***
    if (isset($_POST['list_expanded'])) {
        $_SESSION['save_list_expanded'] = $_POST['list_expanded'] == '0' ? '0' : '1';
    }
    $list_expanded = true; // *** Default value ***
    if (isset($_SESSION['save_list_expanded'])) {
        if ($_SESSION['save_list_expanded'] == '1')
            $list_expanded = true;
        else $list_expanded = false;
    }

    // *** Button: normal or expanded list ***
    $button_line = "item=" . $item;
    if (isset($_GET['start'])) {
        $button_line .= "&amp;start=" . $_GET['start'];
    } else {
        $button_line .= "&amp;start=1";
    }
    ?>

    <form method="POST" action="<?= $uri_path_string . $button_line; ?>" style="display : inline;">
        <?php if ($list_expanded == true) { ?>
            <input type="hidden" name="list_expanded" value="0">
            <input type="submit" name="submit" value="<?= __('Concise view'); ?>" class="btn btn-sm btn-secondary">
        <?php } else { ?>
            <input type="hidden" name="list_expanded" value="1">
            <input type="submit" name="submit" value="<?= __('Expanded view'); ?>" class="btn btn-sm btn-secondary">
        <?php } ?>
    </form>

    <br><br>
    <?php include __DIR__ . '/partial/pagination.php'; ?>

    <?php if ($person_result->rowCount() == 0) { ?>
        <br>
        <div class="center"><?= __('No names found.'); ?></div>
    <?php } ?>
</div>

<?php
$dir = '';
if ($language["dir"] == "rtl") {
    $dir = "rtl"; // loads the proper CSS for rtl display (rtlindex_list2):
}

// with extra sort date column, set smaller left margin
$listnr = "2";      // default 20% margin

//*** Show persons ***
$privcount = 0; // *** Count privacy persons ***

$selected_place = '';
?>
<!-- Table to hold left sort date column (when necessary) and right person list column -->
<table class="table table-sm">
    <thead class="table-primary">
        <tr>
            <th><?= __('Places'); ?></th>
            <th colspan="2"><?= __('Family'); ?></th>
            <th colspan="2" width="280px"><?= ucfirst(__('Married notice (religious)')); ?></th>
            <th colspan="2" width="280px"><?= ucfirst(__('Married (religious)')); ?></th>
        </tr>
    </thead>

    <?php
    while ($familyDb = $person_result->fetch(PDO::FETCH_OBJ)) {
        // *** Man privacy filter ***
        $personDb = $db_functions->get_person($familyDb->fam_man);
        $man_privacy = $personPrivacy->get_privacy($personDb);

        // *** Woman privacy filter ***
        $personDb = $db_functions->get_person($familyDb->fam_woman);
        $woman_privacy = $personPrivacy->get_privacy($personDb);

        $marriage_cls = new \Genealogy\Include\MarriageCls($familyDb, $man_privacy, $woman_privacy);

        $family_privacy = $marriage_cls->get_privacy();

        // *** $family_privacy=true => filter ***
        if ($family_privacy) {
            $privcount++;
        } else {
            show_person($familyDb);
        }
    }
    ?>
</table><br>

<?php if ($privcount) { ?>
    <?= $privcount . __(' persons are not shown due to privacy settings'); ?><br>
<?php } ?>

<?php include __DIR__ . '/partial/pagination.php'; ?>
<br>

<?php
function show_person($familyDb)
{
    global $db_functions, $selected_place, $list_expanded, $privacy, $data, $language;

    $directionMarkers = new \Genealogy\Include\DirectionMarkers($language["dir"]);
    $personPrivacy = new \Genealogy\Include\PersonPrivacy();
    $personName = new \Genealogy\Include\PersonName();
    $personPopup = new \Genealogy\Include\PersonPopup();
    $datePlace = new \Genealogy\Include\DatePlace();

    $selected_person1 = $familyDb->fam_man ? $familyDb->fam_man : $familyDb->fam_woman;
    $personDb = $db_functions->get_person($selected_person1);
    $privacy = $personPrivacy->get_privacy($personDb);
    $name = $personName->get_person_name($personDb, $privacy);

    // *** Show name ***
    $index_name = '';
    if ($name["show_name"] == false) {
        $index_name = __('Name filtered');
    } else {
        // *** If there is no lastname, show a - character. ***
        // Don't show a "-" by pers_patronymes
        if ($personDb->pers_lastname == "" && !isset($_GET['pers_patronym'])) {
            $index_name = "-&nbsp;&nbsp;";
        }
        $index_name .= $name["index_name_extended"] . $name["colour_mark"];
    }

    // *** Show extra colums before a person in index places ***
    if ($selected_place != $familyDb->place_order) { ?>
        <tr>
            <td colspan="7"><b><?= $directionMarkers->dirmark2 . $familyDb->place_order; ?></b></td>
        </tr>
    <?php } ?>
    <?php $selected_place = $familyDb->place_order; ?>
    <tr>
        <td valign="top" style="white-space:nowrap;width:90px">
            <?php
            if ($data["select_marriage_notice"] == '1') {
                if ($selected_place == $familyDb->fam_marr_notice_place) {
                    echo '<span class="place_index place_index_selected">' . __('&infin;') . '</span>';
                } else {
                    echo '<span class="place_index">&nbsp;</span>';
                }
            }

            if ($data["select_marriage_notice_religious"] == '1') {
                if ($selected_place == $familyDb->fam_marr_church_notice_place) {
                    echo '<span class="place_index place_index_selected">' . __('o') . '</span>';
                } else {
                    echo '<span class="place_index">&nbsp;</span>';
                }
            }

            if ($data["select_marriage"] == '1') {
                if ($selected_place == $familyDb->fam_marr_place) {
                    echo '<span class="place_index place_index_selected">' . __('X') . '</span>';
                } else {
                    echo '<span class="place_index">&nbsp;</span>';
                }
            }

            if ($data["select_marriage_religious"] == '1') {
                if ($selected_place == $familyDb->fam_marr_church_place) {
                    echo '<span class="place_index place_index_selected">' . __('x') . '</span>';
                } else {
                    echo '<span class="place_index">&nbsp;</span>';
                }
            }
            ?>
        </td>

        <td valign="top" style="border-right:0px; white-space:nowrap;">
            <?php
            // *** Show person popup menu ***
            echo $personPopup->person_popup_menu($personDb, $privacy);

            // *** Show picture man or wife ***
            if ($personDb->pers_sexe == "M") {
                echo $directionMarkers->dirmark1 . ' <img src="images/man.gif" alt="man">';
            } elseif ($personDb->pers_sexe == "F") {
                echo $directionMarkers->dirmark1 . ' <img src="images/woman.gif" alt="woman">';
            } else {
                echo $directionMarkers->dirmark1 . ' <img src="images/unknown.gif" alt="unknown">';
            }
            ?>
        </td>

        <td style="border-left:0px;">
            <?php
            // *** Show name of person ***
            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $personLink = new \Genealogy\Include\PersonLink();
            $start_url = $personLink->get_person_link($personDb);
            echo ' <a href="' . $start_url . '">' . rtrim($index_name) . '</a>';

            //*** Show spouse/ partner ***
            if ($list_expanded == true && $personDb->pers_fams) {
                $marriage_array = explode(";", $personDb->pers_fams);
                // *** Code to show only last marriage ***
                $nr_marriages = count($marriage_array);

                for ($x = 0; $x <= $nr_marriages - 1; $x++) {
                    $fam_partnerDb = $db_functions->get_family($marriage_array[$x]);

                    // *** This check is better then a check like: $personDb->pers_sexe=='F', because of unknown sexe or homosexual relations. ***
                    if ($personDb->pers_gedcomnumber == $fam_partnerDb->fam_man) {
                        $partner_id = $fam_partnerDb->fam_woman;
                    } else {
                        $partner_id = $fam_partnerDb->fam_man;
                    }

                    $relation_short = __('&amp;');
                    if ($fam_partnerDb->fam_marr_date || $fam_partnerDb->fam_marr_place || $fam_partnerDb->fam_marr_church_date || $fam_partnerDb->fam_marr_church_place) {
                        $relation_short = __('X');
                    }
                    if ($fam_partnerDb->fam_div_date || $fam_partnerDb->fam_div_place) {
                        $relation_short = __(') (');
                    }

                    if ($partner_id != '0' && $partner_id != '') {
                        $partnerDb = $db_functions->get_person($partner_id);
                        $privacy = $personPrivacy->get_privacy($partnerDb);
                        $name = $personName->get_person_name($partnerDb, $privacy);
                    } else {
                        $name["standard_name"] = __('N.N.');
                    }

                    if ($nr_marriages > 1) {
                        echo ',';
                    }
                    if ($partnerDb->pers_gedcomnumber != $familyDb->fam_woman) {
                        // *** Show actual relation/ marriage in special font ***
                        echo ' <span class="index_partner" style="font-size:10px;">';
                    } else {
                        echo ' ';
                    }
                    if ($nr_marriages > 1) {
                        if ($x == 0) {
                            echo __('1st');
                        } elseif ($x == 1) {
                            echo ' ' . __('2nd');
                        } elseif ($x == 2) {
                            echo ' ' . __('3rd');
                        } elseif ($x > 2) {
                            echo ' ' . ($x + 1) . __('th');
                        }
                    }
                    echo ' ' . $relation_short . ' ' . rtrim($name["standard_name"]);
                    if ($partnerDb->pers_gedcomnumber != $familyDb->fam_woman) {
                        echo '</span>';
                    }
                }
            }
            ?>
        </td>

        <td style="white-space:nowrap;">
            <?php
            $info = '';
            if ($familyDb->fam_marr_church_notice_date) {
                $info = __('o') . ' ' . $datePlace->date_place($familyDb->fam_marr_church_notice_date, '');
            }
            if ($familyDb->fam_marr_notice_date) {
                $info = __('&infin;') . ' ' . $datePlace->date_place($familyDb->fam_marr_notice_date, '');
            }
            //echo "<span style='font-size:90%'>" . $info . $directionMarkers->dirmark1 . "</span>";
            if ($privacy && $info) {
                echo ' ' . __('PRIVACY FILTER');
            } else {
                echo $info;
            }
            ?>
        </td>

        <td>
            <?php
            $info = '';
            if ($familyDb->fam_marr_church_notice_place) {
                $info = __('o') . ' ' . $familyDb->fam_marr_church_notice_place;
            }
            if ($familyDb->fam_marr_notice_place) {
                $info = __('&infin;') . ' ' . $familyDb->fam_marr_notice_place;
            }
            if ($privacy && $info) {
                echo ' ' . __('PRIVACY FILTER');
            } else {
                echo $info;
            }
            ?>
        </td>

        <td style="white-space:nowrap;">
            <?php
            $info = '';
            if ($familyDb->fam_marr_church_date) {
                $info = __('x') . ' ' . $datePlace->date_place($familyDb->fam_marr_church_date, '');
            }
            if ($familyDb->fam_marr_date) {
                $info = __('X') . ' ' . $datePlace->date_place($familyDb->fam_marr_date, '');
            }
            if ($privacy && $info) {
                echo ' ' . __('PRIVACY FILTER');
            } else {
                echo $info;
            }
            ?>
        </td>

        <td>
            <?php
            $info = '';
            if ($familyDb->fam_marr_church_place) {
                $info = __('x') . ' ' . $familyDb->fam_marr_church_place;
            }
            if ($familyDb->fam_marr_place) {
                $info = __('X') . ' ' . $familyDb->fam_marr_place;
            }
            if ($privacy && $info) {
                echo ' ' . __('PRIVACY FILTER');
            } else {
                echo $info;
            }
            ?>
        </td>
    </tr>
<?php
}
