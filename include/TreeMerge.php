<?php

/**
 * Jul 2025: created class for TreeMerge.
 */

namespace Genealogy\Include;

use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\PersonName;
use PDO;

class TreeMerge
{
    private $dbh, $trees;

    public function __construct($dbh, $trees)
    {
        $this->dbh = $dbh;
        $this->trees = $trees;
    }

    /**
     * "show_pair" is the function that presents the data of two persons to be merged
     * with the possibility to determine what information is passed from left to right
     */
    public function show_pair($left_id, $right_id, $mode)
    {
        global $db_functions;

        $db_functions->set_tree_id($this->trees['tree_id']);

        // get data for left person
        $leftDb = $db_functions->get_person_with_id($left_id);

        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();

        $spouses1 = '';
        $children1 = '';
        if ($leftDb->pers_fams) {
            $fams = explode(';', $leftDb->pers_fams);
            foreach ($fams as $value) {
                $famDb = $db_functions->get_family($value);

                $spouse_ged = $famDb->fam_man == $leftDb->pers_gedcomnumber ? $famDb->fam_woman : $famDb->fam_man;
                $spouseDb = $db_functions->get_person($spouse_ged);
                $privacy = $personPrivacy->get_privacy($spouseDb);
                $name = $personName->get_person_name($spouseDb, $privacy);
                $spouses1 .= $name["standard_name"] . '<br>';

                if ($famDb->fam_children) {
                    $child = explode(';', $famDb->fam_children);
                    foreach ($child as $ch_value) {
                        $childDb = $db_functions->get_person($ch_value);
                        $privacy = $personPrivacy->get_privacy($childDb);
                        $name = $personName->get_person_name($childDb, $privacy);
                        $children1 .= $name["standard_name"] . '<br>';
                    }
                }
            }
            $spouses1 = substr($spouses1, 0, -4); // take off last <br>
            $children1 = substr($children1, 0, -4); // take of last <br>
        }

        $father1 = '';
        $mother1 = '';
        if ($leftDb->pers_famc) {
            $qry2 = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $leftDb->pers_famc . "'";
            $parents = $this->dbh->query($qry2);
            $parentsDb = $parents->fetch(PDO::FETCH_OBJ);

            $fatherDb = $db_functions->get_person($parentsDb->fam_man);
            $privacy = $personPrivacy->get_privacy($fatherDb);
            $name = $personName->get_person_name($fatherDb, $privacy);
            $father1 .= $name["standard_name"] . '<br>';

            $motherDb = $db_functions->get_person($parentsDb->fam_woman);
            $privacy = $personPrivacy->get_privacy($motherDb);
            $name = $personName->get_person_name($motherDb, $privacy);
            $mother1 .= $name["standard_name"] . '<br>';
        }

        // get data for right person
        $rightDb = $db_functions->get_person_with_id($right_id);

        $spouses2 = '';
        $children2 = '';
        if ($rightDb->pers_fams) {
            $fams = explode(';', $rightDb->pers_fams);
            foreach ($fams as $value) {
                $famDb = $db_functions->get_family($value);
                $spouse_ged = $famDb->fam_man == $rightDb->pers_gedcomnumber ? $famDb->fam_woman : $famDb->fam_man;
                $spouseDb = $db_functions->get_person($spouse_ged);
                $privacy = $personPrivacy->get_privacy($spouseDb);
                $name = $personName->get_person_name($spouseDb, $privacy);
                $spouses2 .= $name["standard_name"] . '<br>';

                if ($famDb->fam_children) {
                    $child = explode(';', $famDb->fam_children);
                    foreach ($child as $ch_value) {
                        $childDb = $db_functions->get_person($ch_value);
                        $privacy = $personPrivacy->get_privacy($childDb);
                        $name = $personName->get_person_name($childDb, $privacy);
                        $children2 .= $name["standard_name"] . '<br>';
                    }
                }
            }
            $spouses2 = substr($spouses2, 0, -4); // take off last <br>
            $children2 = substr($children2, 0, -4); // take of last <br>
        }

        $father2 = '';
        $mother2 = '';
        if ($rightDb->pers_famc && $rightDb->pers_famc != "") {
            $qry2 = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $rightDb->pers_famc . "'";
            $parents = $this->dbh->query($qry2);
            $parentsDb = $parents->fetch(PDO::FETCH_OBJ);

            $fatherDb = $db_functions->get_person($parentsDb->fam_man);
            $privacy = $personPrivacy->get_privacy($fatherDb);
            $name = $personName->get_person_name($fatherDb, $privacy);
            $father2 .= $name["standard_name"] . '<br>';

            $motherDb = $db_functions->get_person($parentsDb->fam_woman);
            $privacy = $personPrivacy->get_privacy($motherDb);
            $name = $personName->get_person_name($motherDb, $privacy);
            $mother2 .= $name["standard_name"] . '<br>';
        }
?>

        <table class="table table-striped">
            <tr class="table-primary">
                <th style="vertical-align:top;font-size:130%" colspan="3">
                    <?php if ($mode == "duplicate") { ?>
                        <?= __('Duplicate merge'); ?>
                    <?php } elseif ($mode == "relatives") { ?>
                        <?= __('Surrounding relatives check'); ?>
                    <?php } else { ?>
                        <?= __('Manual merge'); ?>
                    <?php } ?>
                </th>
            </tr>

            <tr>
                <th style="width:150px;border-bottom:2px solid #a4a4a4;text-align:left">
                    <?php
                    if ($mode == 'duplicate') {
                        $num = $_SESSION['present_compare_' . $this->trees['tree_id']] + 1;
                        echo __('Nr. ') . $num . __(' of ') . count($_SESSION['dupl_arr_' . $this->trees['tree_id']]);
                    } elseif ($mode = 'relatives') {
                        $rl = explode(';', $this->trees['relatives_merge']);
                        $rls = count($rl) - 1;
                        echo $rls . __(' relatives to check');
                    }
                    ?>
                </th>
                <th style="width:375px;border-bottom:2px solid #a4a4a4"> <?= __('Person 1: '); ?></th>
                <th style="width:375px;border-bottom:2px solid #a4a4a4"> <?= __('Person 2: '); ?></th>
            </tr>
            <tr style="background-color:#e6e6e6">
                <td style="font-weight:bold"><?= __('GEDCOM number (ID)'); ?></td>
                <td><?= $leftDb->pers_gedcomnumber; ?></td>
                <td><?= $rightDb->pers_gedcomnumber; ?></td>
            </tr>

            <?php
            $this->show_regular($leftDb->pers_lastname, $rightDb->pers_lastname, __('last name'), 'l_name');
            $this->show_regular($leftDb->pers_firstname, $rightDb->pers_firstname, __('first name'), 'f_name');
            $this->show_regular($leftDb->pers_patronym, $rightDb->pers_patronym, __('patronym'), 'patr');
            $this->show_regular($leftDb->pers_birth_date, $rightDb->pers_birth_date, __('birth date'), 'b_date');
            $this->show_regular($leftDb->pers_birth_place, $rightDb->pers_birth_place, __('birth place'), 'b_place');
            $this->show_regular($leftDb->pers_birth_time, $rightDb->pers_birth_time, __('birth time'), 'b_time');
            $this->show_regular($leftDb->pers_bapt_date, $rightDb->pers_bapt_date, __('baptism date'), 'bp_date');
            $this->show_regular($leftDb->pers_bapt_place, $rightDb->pers_bapt_place, __('baptism place'), 'bp_place');
            $this->show_regular($leftDb->pers_death_date, $rightDb->pers_death_date, __('death date'), 'd_date');
            $this->show_regular($leftDb->pers_death_place, $rightDb->pers_death_place, __('death place'), 'd_place');
            $this->show_regular($leftDb->pers_death_time, $rightDb->pers_death_time, __('death time'), 'd_time');
            $this->show_regular($leftDb->pers_death_cause, $rightDb->pers_death_cause, __('cause of death'), 'd_cause');
            $this->show_regular($leftDb->pers_cremation, $rightDb->pers_cremation, __('cremation'), 'crem');
            $this->show_regular($leftDb->pers_buried_date, $rightDb->pers_buried_date, __('burial date'), 'br_date');
            $this->show_regular($leftDb->pers_buried_place, $rightDb->pers_buried_place, __('burial place'), 'br_place');
            $this->show_regular($leftDb->pers_alive, $rightDb->pers_alive, __('alive'), 'alive');
            $this->show_regular($leftDb->pers_religion, $rightDb->pers_religion, __('religion'), 'reli');
            $this->show_regular($leftDb->pers_own_code, $rightDb->pers_own_code, __('own code'), 'code');
            $this->show_regular($leftDb->pers_stillborn, $rightDb->pers_stillborn, __('stillborn'), 'stborn');
            $this->show_regular_text($leftDb->pers_text, $rightDb->pers_text, __('general text'), 'text');
            $this->show_regular_text($leftDb->pers_name_text, $rightDb->pers_name_text, __('name text'), 'n_text');
            $this->show_regular_text($leftDb->pers_birth_text, $rightDb->pers_birth_text, __('birth text'), 'b_text');
            $this->show_regular_text($leftDb->pers_bapt_text, $rightDb->pers_bapt_text, __('baptism text'), 'bp_text');
            $this->show_regular_text($leftDb->pers_death_text, $rightDb->pers_death_text, __('death text'), 'd_text');
            $this->show_regular_text($leftDb->pers_buried_text, $rightDb->pers_buried_text, __('burial text'), 'br_text');

            // *** functions to show events, sources and addresses ***
            $this->show_events_merge($leftDb->pers_gedcomnumber, $rightDb->pers_gedcomnumber);
            $this->show_sources_merge($leftDb->pers_gedcomnumber, $rightDb->pers_gedcomnumber);
            $this->show_addresses_merge($leftDb->pers_gedcomnumber, $rightDb->pers_gedcomnumber);

            //TEST *** Address by relation ***
            // A person can be married multiple times (left and right side). Probably needed to rebuild show_addresses_merge scripts to show them seperately?
            //$r_fams = explode(';',$rightDb->pers_fams);
            //for($i=0;$i<count($r_fams);$i++) {
            //	echo $r_fams[$i].'! ';
            //	show_addresses_merge('',$r_fams[$i]);
            //}
            ?>

            <tr>
                <td colspan=3 style="border-top:2px solid #a4a4a4;border-bottom:2px solid #a4a4a4;font-weight:bold"><?= __('Relatives'); ?>:</td>
            </tr>
            <tr style="background-color:#f2f2f2">
                <td style="font-weight:bold"><?= __('Spouse'); ?>:</td>
                <td><?= $spouses1; ?></td>
                <td><?= $spouses2; ?></td>
            </tr>
            <tr style="background-color:#e6e6e6">
                <td style="font-weight:bold"><?= __('Father'); ?>:</td>
                <td><?= $father1; ?></td>
                <td><?= $father2; ?></td>
            </tr>
            <tr style="background-color:#f2f2f2">
                <td style="font-weight:bold"><?= __('Mother'); ?>:</td>
                <td><?= $mother1; ?></td>
                <td><?= $mother2; ?></td>
            </tr>
            <tr style="background-color:#e6e6e6">
                <td style="font-weight:bold"><?= __('Children'); ?>:</td>
                <td><?= $children1; ?></td>
                <td><?= $children2; ?></td>
            </tr>
        </table>
        <?php
    }

    /**
     * show_regular is a function that places the regular items from humo_persons in the comparison table
     */
    private function show_regular($left_item, $right_item, $title, $name)
    {
        if ($left_item || $right_item) {
        ?>
            <tr>
                <td style="font-weight:bold"><?= ucfirst($title); ?>:</td>

                <?php
                if ($left_item) {
                    if ($name == 'crem' && $left_item == '1') {
                        $left_item = 'Yes';
                    }
                    if ($name == 'fav' && $left_item == '1') {
                        $left_item = 'Yes';
                    }
                    if ($name == 'stborn' && $left_item == 'y') {
                        $left_item = 'Yes';
                    }
                }
                ?>
                <td><input type="radio" name="<?= $name; ?>" value="1" class="form-check-input" <?= $left_item ? 'checked' : ''; ?>> <?= $left_item; ?></td>

                <?php
                if ($name == 'crem' && $right_item == '1') {
                    $right_item = 'Yes';
                }
                if ($name == 'fav' && $right_item == '1') {
                    $right_item = 'Yes';
                }
                if ($name == 'stborn' && $right_item == 'y') {
                    $right_item = 'Yes';
                }
                ?>
                <td><input type="radio" name="<?= $name; ?>" value="2" class="form-check-input" <?= !$left_item ? 'checked' : ''; ?>> <?= $right_item; ?></td>
            </tr>
        <?php
        }
    }

    /**
     * show_regular_text is a function that places the regular text items from humo_person in the comparison table
     */
    private function show_regular_text($left_item, $right_item, $title, $name)
    {
        if ($right_item) {
        ?>
            <tr>
                <td style="font-weight:bold"><?= $title; ?>:</td>

                <!-- Person 1 -->
                <td>
                    <?php
                    $showtext = '';
                    if ($left_item) {
                        $showtext = "[" . __('Read text') . "]";
                        if (substr($left_item, 0, 2) === "@N") {
                            // not plain text but @N23@ -> look it up in humo_texts
                            $notes = $this->dbh->query("SELECT text_text FROM humo_texts WHERE text_tree_id='" . $this->trees['tree_id'] . "' AND text_gedcomnr ='" . substr($left_item, 1, -1) . "'");
                            $notesDb = $notes->fetch(PDO::FETCH_OBJ);
                            $notetext = $notesDb->text_text;
                        } else {
                            $notetext = $left_item;
                        }
                    ?>
                        <input type="checkbox" name="<?= $name; ?>_l" class="form-check-input" <?= $left_item ? 'checked' : ''; ?>>

                        <div class="dropdown dropend d-inline">
                            <button class="btn btn-link" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="--bs-btn-line-height: .5;"><?= $showtext; ?></button>
                            <ul class="dropdown-menu p-2" style="width:400px;">
                                <?= $this->popclean($notetext); ?>
                            </ul>
                        </div>

                    <?php
                    } else {
                        echo __('(no data)');
                    }

                    $showtext = "[" . __('Read text') . "]";
                    if (substr($right_item, 0, 2) === "@N") {
                        // not plain text but @N23@ -> look it up in humo_texts
                        $notes = $this->dbh->query("SELECT text_text FROM humo_texts WHERE text_tree_id='" . $this->trees['tree_id'] . "' AND text_gedcomnr ='" . substr($right_item, 1, -1) . "'");
                        $notesDb = $notes->fetch(PDO::FETCH_OBJ);
                        $notetext = $notesDb->text_text;
                    } else {
                        $notetext = $right_item;
                    }
                    ?>
                </td>

                <!-- Person 2 -->
                <td>
                    <input type="checkbox" name="<?= $name; ?>_r" class="form-check-input" <?= !$left_item ? 'checked' : ''; ?>>

                    <div class="dropdown dropend d-inline">
                        <button class="btn btn-link" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="--bs-btn-line-height: .5;"><?= $showtext; ?></button>
                        <ul class="dropdown-menu p-2" style="width:400px;">
                            <?= $this->popclean($notetext); ?>
                        </ul>
                    </div>
                </td>

            </tr>
        <?php
        }
    }

    /**
     * show_events_merge is a function that places the events in the comparison table
     */
    private function show_events_merge($left_ged, $right_ged)
    {
        $l_address = $l_picture = $l_profession = $l_source = $l_event = $l_birth_decl_witness = $l_baptism_witness = $l_death_decl_witness = $l_burial_witness = $l_name = $l_nobility = $l_title = $l_lordship = $l_URL = $l_else = array();
        $r_address = $r_picture = $r_profession = $r_source = $r_event = $r_birth_decl_witness = $r_baptism_witness = $r_death_decl_witness = $r_burial_witness = $r_name = $r_nobility = $r_title = $r_lordship = $r_URL = $r_else = array();
        $left_events = $this->dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $this->trees['tree_id'] . "'
            AND (event_connect_kind='person' OR event_kind='ASSO') AND event_connect_id ='" . $left_ged . "' ORDER BY event_kind ");
        $right_events = $this->dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $this->trees['tree_id'] . "'
            AND (event_connect_kind='person' OR event_kind='ASSO') AND event_connect_id ='" . $right_ged . "' ORDER BY event_kind ");

        if ($right_events->rowCount() > 0) {
            // no use doing this if right has no events at all...

            while ($l_eventsDb = $left_events->fetch(PDO::FETCH_OBJ)) {
                if ($l_eventsDb->event_kind == "address") {
                    $l_address[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "picture") {
                    $l_picture[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "profession") {
                    $l_profession[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "event") {
                    $l_event[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "ASSO" && $l_eventsDb->event_connect_kind == "birth_declaration") {
                    $l_birth_decl_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "ASSO" && $l_eventsDb->event_connect_kind == "CHR") {
                    $l_baptism_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "ASSO" && $l_eventsDb->event_connect_kind == "death_declaration") {
                    $l_death_decl_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "ASSO" && $l_eventsDb->event_connect_kind == "BURI") {
                    $l_burial_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "name") {
                    $l_name[$l_eventsDb->event_id] = '(' . $l_eventsDb->event_gedcom . ') ' . $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "nobility") {
                    $l_nobility[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "title") {
                    $l_title[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "lordship") {
                    $l_lordship[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } elseif ($l_eventsDb->event_kind == "URL") {
                    $l_URL[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                } else {
                    $l_else[$l_eventsDb->event_id] = $l_eventsDb->event_event;
                }
            }

            while ($r_eventsDb = $right_events->fetch(PDO::FETCH_OBJ)) {
                if ($r_eventsDb->event_kind == "address") {
                    $r_address[$r_eventsDb->event_id] = $r_eventsDb->event_event;
                } elseif ($r_eventsDb->event_kind == "picture") {
                    $r_picture[$r_eventsDb->event_id] = $r_eventsDb->event_event;
                } elseif ($r_eventsDb->event_kind == "profession") {
                    $r_profession[$r_eventsDb->event_id] = $r_eventsDb->event_event;
                } elseif ($r_eventsDb->event_kind == "event") {
                    $r_event[$r_eventsDb->event_id] = $r_eventsDb->event_event;
                } elseif ($r_eventsDb->event_kind == "ASSO" && $r_eventsDb->event_connect_kind == "birth_declaration") {
                    $r_birth_decl_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event;

                    if ($r_eventsDb->event_connect_id2) {
                        $r_birth_decl_witness[$r_eventsDb->event_id] = '@' . $r_eventsDb->event_connect_id2;
                    }
                } elseif ($r_eventsDb->event_kind == "ASSO" && $r_eventsDb->event_connect_kind == "CHR") {
                    $r_baptism_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event;

                    if ($r_eventsDb->event_connect_id2) {
                        $r_baptism_witness[$r_eventsDb->event_id] = '@' . $r_eventsDb->event_connect_id2;
                    }
                } elseif ($r_eventsDb->event_kind == "ASSO" && $r_eventsDb->event_connect_kind == "death_declaration") {
                    $r_death_decl_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event;

                    if ($r_eventsDb->event_connect_id2) {
                        $r_death_decl_witness[$r_eventsDb->event_id] = '@' . $r_eventsDb->event_connect_id2;
                    }
                } elseif ($r_eventsDb->event_kind == "ASSO" && $r_eventsDb->event_connect_kind == "BURI") {
                    $r_burial_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event;

                    if ($r_eventsDb->event_connect_id2) {
                        $r_burial_witness[$r_eventsDb->event_id] = '@' . $r_eventsDb->event_connect_id2;
                    }
                } elseif ($r_eventsDb->event_kind == "name") {
                    $r_name[$r_eventsDb->event_id] = '(' . $r_eventsDb->event_gedcom . ') ' . $r_eventsDb->event_event;
                } elseif ($r_eventsDb->event_kind == "nobility") {
                    $r_nobility[$r_eventsDb->event_id] = $r_eventsDb->event_event;
                } elseif ($r_eventsDb->event_kind == "title") {
                    $r_title[$r_eventsDb->event_id] = $r_eventsDb->event_event;
                } elseif ($r_eventsDb->event_kind == "lordship") {
                    $r_lordship[$r_eventsDb->event_id] = $r_eventsDb->event_event;
                } elseif ($r_eventsDb->event_kind == "URL") {
                    $r_URL[$r_eventsDb->event_id] = $r_eventsDb->event_event;
                } else {
                    $r_else[] = $r_eventsDb->event_event;
                }
            }

            // before calling put_event function check if right has a value otherwise there is no need to show
            if (!empty($r_address)) {
                $this->put_event('address', __('Address'), $l_address, $r_address);
            }
            if (!empty($r_picture)) {
                $this->put_event('picture', __('Picture'), $l_picture, $r_picture);
            }
            if (!empty($r_profession)) {
                $this->put_event('profession', __('Profession'), $l_profession, $r_profession);
            }
            if (!empty($r_event)) {
                $this->put_event('event', __('Event'), $l_event, $r_event);
            }

            // *** Sept. 2024: declaration and declaration witnesses are now seperate events *** 
            if (!empty($r_birth_decl_witness)) {
                $this->put_event('birth_declaration', __('birth declaration'), $l_birth_decl_witness, $r_birth_decl_witness);
            }
            if (!empty($r_baptism_witness)) {
                $this->put_event('CHR', __('baptism witness'), $l_baptism_witness, $r_baptism_witness);
            }
            // *** Sept. 2024: declaration and declaration witnesses are now seperate events *** 
            if (!empty($r_death_decl_witness)) {
                $this->put_event('death_declaration', __('death declaration'), $l_death_decl_witness, $r_death_decl_witness);
            }
            if (!empty($r_burial_witness)) {
                $this->put_event('BURI', __('burial witness'), $l_burial_witness, $r_burial_witness);
            }

            if (!empty($r_name)) {
                $this->put_event('name', __('Other names'), $l_name, $r_name);
            }
            if (!empty($r_nobility)) {
                $this->put_event('nobility', __('Title of Nobility'), $l_nobility, $r_nobility);
            }
            if (!empty($r_title)) {
                $this->put_event('title', __('Title'), $l_title, $r_title);
            }
            if (!empty($r_lordship)) {
                $this->put_event('lordship', __('Title of Lordship'), $l_lordship, $r_lordship);
            }
            if (!empty($r_URL)) {
                $this->put_event('URL', __('Internet link / URL'), $l_URL, $r_URL);
            }
        }
    }

    /**
     * "put_event" is a function to create the checkboxes for the event items
     */
    private function put_event($this_event, $name_event, $l_ev, $r_ev)
    {
        // if right has no event all stays as it is
        if ($r_ev != '') {
        ?>
            <tr>
                <td style="font-weight:bold"><?= $name_event; ?>:</td>

                <!-- Person 1 -->
                <td style="vertical-align:top;">
                    <?php
                    if (is_array($l_ev) && $l_ev != '') {
                        foreach ($l_ev as $key => $value) {
                            if (substr($value, 0, 2) === '@I') {
                                // this is a person GEDCOM number, not plain text -> show the name
                                $value = str_replace('@', '', $value);
                                $result = $this->dbh->query("SELECT pers_lastname, pers_firstname FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber = '" . $value . "'");
                                $resultDb = $result->fetch(PDO::FETCH_OBJ);
                                $value = $resultDb->pers_firstname . ' ' . $resultDb->pers_lastname;
                            }
                            if ($this_event == 'picture') {
                                // show link to pic
                                $familyTreeQry = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_id='" . $this->trees['tree_id'] . "'");
                                $familyTree = $familyTreeQry->fetch(PDO::FETCH_OBJ);
                                $tree_pict_path = $familyTree->tree_pict_path;
                                $dir = '../' . $tree_pict_path;
                                $value .= '<br><img width="150px" src="' . $dir . $value . '"><br>';
                            }
                    ?>
                            <input type="hidden" name="l_event_shown_<?= $key; ?>" value="1">
                            <input type="checkbox" name="l_event_checked_<?= $key; ?>" class="form-check-input" checked> <?= $value; ?><br>
                    <?php
                        }
                    } else {
                        echo __('(no data)');
                    }
                    ?>
                </td>

                <!-- Person 2 -->
                <td style="vertical-align:top;">
                    <?php
                    if (is_array($r_ev) && $r_ev != '') {
                        $checked = '';
                        if ($l_ev == '') {
                            $checked = " checked";
                        }
                        foreach ($r_ev as $key => $value) {
                            if (substr($value, 0, 2) === '@I') {
                                // this is a person gedcom number, not plain text
                                $value = str_replace('@', '', $value);
                                $result = $this->dbh->query("SELECT pers_lastname, pers_firstname FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber = '" . $value . "'");
                                $resultDb = $result->fetch(PDO::FETCH_OBJ);
                                $value = $resultDb->pers_firstname . ' ' . $resultDb->pers_lastname;
                            }
                            if ($this_event == 'picture') {
                                $familyTreeQry = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_id='" . $this->trees['tree_id'] . "'");
                                $familyTree = $familyTreeQry->fetch(PDO::FETCH_OBJ);
                                $tree_pict_path = $familyTree->tree_pict_path;
                                $dir = '../' . $tree_pict_path;
                                $value .= '<br><img width="150px" src="' . $dir . $value . '"><br>';
                            }
                    ?>
                            <input type="hidden" name="r_event_shown_<?= $key; ?>" value="1">
                            <input type="checkbox" name="r_event_checked_<?= $key; ?>" class="form-check-input" <?= $checked; ?>> <?= $value; ?><br>
                    <?php
                        }
                    } else {
                        echo __('(no data)');
                    }
                    ?>
                </td>
            </tr>
        <?php
        }
    }

    /**
     * "show_sources_merge" is the function that places the sources in the comparison table (if right has a value)
     */
    private function show_sources_merge($left_ged, $right_ged)
    {
        // This was disabled!
        $left_sources = $this->dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND connect_connect_id ='" . $left_ged . "'
            AND LOCATE('source',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
        $right_sources = $this->dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND connect_connect_id ='" . $right_ged . "'
            AND LOCATE('source',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");

        /* Only processes person_source... Disabled in december 2022.
        $left_sources = $this->dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='".$this->trees['tree_id']."' AND connect_connect_id ='".$left_ged."'
            AND connect_sub_kind='person_source' ORDER BY connect_order");
        $right_sources = $this->dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='".$this->trees['tree_id']."' AND connect_connect_id ='".$right_ged."'
            AND connect_sub_kind='person_source' ORDER BY connect_order");
        */

        if ($right_sources->rowCount() > 0) {
            // no use doing this if right has no sources
        ?>
            <tr>
                <td style="font-weight:bold"><?= __('Sources'); ?>:</td>

                <!-- Person 1 -->
                <td>
                    <?php
                    if ($left_sources->rowCount() > 0) {
                        while ($left_sourcesDb = $left_sources->fetch(PDO::FETCH_OBJ)) {
                            $l_source = $this->dbh->query("SELECT source_title FROM humo_sources WHERE source_tree_id='" . $this->trees['tree_id'] . "' AND source_gedcomnr='" . $left_sourcesDb->connect_source_id . "'");
                            $result = $l_source->fetch(PDO::FETCH_OBJ);
                            if (isset($result->source_title)) {
                                $title = $result->source_title;
                            } else {
                                $title = '';
                            }
                    ?>
                            <input type="hidden" name="l_source_shown_<?= $left_sourcesDb->connect_id; ?>" value="1">
                            <input type="checkbox" name="l_source_checked_<?= $left_sourcesDb->connect_id; ?>" class="form-check-input" checked> <?= $title; ?><br>
                    <?php
                        }
                    } else {
                        echo __('(no data)');
                    }
                    ?>
                </td>

                <!-- Person 2 -->
                <td>
                    <?php
                    while ($right_sourcesDb = $right_sources->fetch(PDO::FETCH_OBJ)) {
                        $checked = '';
                        if (!$left_sources->rowCount()) {
                            $checked = " checked";
                        }
                        $r_source = $this->dbh->query("SELECT source_title FROM humo_sources WHERE source_tree_id='" . $this->trees['tree_id'] . "' AND source_gedcomnr='" . $right_sourcesDb->connect_source_id . "'");
                        $result = $r_source->fetch(PDO::FETCH_OBJ);
                        if (isset($result->source_title)) {
                            $title = $result->source_title;
                        } else {
                            $title = '';
                        }
                    ?>
                        <input type="hidden" name="r_source_shown_<?= $right_sourcesDb->connect_id; ?>" value="1">
                        <input type="checkbox" name="r_source_checked_<?= $right_sourcesDb->connect_id; ?>" class="form-check-input" <?= $checked; ?>> <?= $title; ?><br>
                    <?php
                    }
                    ?>
                </td>
            </tr>
        <?php
        }
    }

    /**
     * "show_addresses_merge" is the function that places the addresses in the comparison table (if right has a value)
     */
    private function show_addresses_merge($left_ged, $right_ged)
    {
        // This part was disabled!
        $left_addresses = $this->dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND connect_connect_id ='" . $left_ged . "'
            AND LOCATE('address',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
        $right_addresses = $this->dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND connect_connect_id ='" . $right_ged . "'
            AND LOCATE('address',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");

        /* DISABLED in december 2022. Only processes person_address.
        $left_addresses = $this->dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='".$this->trees['tree_id']."' AND connect_connect_id ='".$left_ged."'
            AND connect_sub_kind='person_address'
            ORDER BY connect_sub_kind ");
        $right_addresses = $this->dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='".$this->trees['tree_id']."' AND connect_connect_id ='".$right_ged."'
            AND connect_sub_kind='person_address'
            ORDER BY connect_sub_kind ");
        */

        // no use doing this if right has no sources
        if ($right_addresses->rowCount() > 0) {
        ?>
            <tr>
                <td style="font-weight:bold"><?= __('Addresses'); ?>:</td>

                <!-- Person 1 -->
                <td>
                    <?php
                    if ($left_addresses->rowCount() > 0) {
                        while ($left_addressesDb = $left_addresses->fetch(PDO::FETCH_OBJ)) {
                            $l_address = $this->dbh->query("SELECT address_address, address_place FROM humo_addresses WHERE address_tree_id='" . $this->trees['tree_id'] . "' AND address_gedcomnr='" . $left_addressesDb->connect_item_id . "'");
                            $result = $l_address->fetch(PDO::FETCH_OBJ);
                            $title = $result->address_address . ' ' . $result->address_place;
                    ?>
                            <input type="hidden" name="l_address_shown_<?= $left_addressesDb->connect_id; ?>" value="1">
                            <input type="checkbox" name="l_address_checked_<?= $left_addressesDb->connect_id; ?>" class="form-check-input" checked> <?= $title; ?><br>
                    <?php
                        }
                    } else {
                        echo __('(no data)');
                    }
                    ?>
                </td>

                <!-- Person 2 -->
                <td>
                    <?php
                    while ($right_addressesDb = $right_addresses->fetch(PDO::FETCH_OBJ)) {
                        $checked = '';
                        if (!$left_addresses->rowCount()) {
                            $checked = " checked";
                        }
                        $r_address = $this->dbh->query("SELECT address_address, address_place FROM humo_addresses WHERE address_tree_id='" . $this->trees['tree_id'] . "' AND address_gedcomnr='" . $right_addressesDb->connect_item_id . "'");

                        $result = $r_address->fetch(PDO::FETCH_OBJ);
                        $title = $result->address_address . ' ' . $result->address_place;
                    ?>
                        <input type="hidden" name="r_address_shown_<?= $right_addressesDb->connect_id; ?>" value="1">
                        <input type="checkbox" name="r_address_checked_<?= $right_addressesDb->connect_id; ?>" class="form-check-input" <?= $checked; ?>> <?= $title; ?><br>
                    <?php } ?>
                </td>
            </tr>
<?php
        }
    }

    /**
     * function popclean prepares a mysql output string for presentation in popup
     */
    private function popclean($input)
    {
        return str_replace(array("\r\n", "\n\r", "\r", "\n"), "<br>", htmlentities(addslashes($input), ENT_QUOTES));
    }
}
