<?php

/**
 * Jul 2025: created class for TreeMerge.
 */

namespace Genealogy\Include;

use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\PersonName;
use Genealogy\Include\EventManager;
use PDO;

class TreeMerge
{
    private $dbh, $trees;
    private $leftPerson, $rightPerson;

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

        // get data for left person
        $leftDb = $db_functions->get_person_with_id($left_id);

        //$personPrivacy = new \Genealogy\Include\PersonPrivacy();
        //$personName = new \Genealogy\Include\PersonName();
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
        if ($r_ev != '') {
            // if right has no event all stays as it is
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
                                // TODO check if this works using a default picture path.
                                $tree_pict_path = $familyTree->tree_pict_path;
                                $dir = '../' . $tree_pict_path;

                                $value .= '<br><img width="150px" src="' . $dir . $value . '"><br>';
                            }
                            echo '<input type="checkbox" name="l_' . $this_event . '_' . $key . '" class="form-check-input" checked> ' . $value . '<br>';
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
                            echo '<input type="checkbox" name="r_' . $this_event . '_' . $key . '" class="form-check-input" ' . $checked . '> ' . $value . '<br>';
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
                            echo '<input type="checkbox" name="l_source_' . $left_sourcesDb->connect_id . '" class="form-check-input" ' . 'checked' . '> ' . $title . '<br>';
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
                        echo '<input type="checkbox" name="r_source_' . $right_sourcesDb->connect_id . '" class="form-check-input" ' . $checked . '> ' . $title . '<br>';
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

        if ($right_addresses->rowCount() > 0) {
            // no use doing this if right has no sources
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
                            echo '<input type="checkbox" name="l_address_' . $left_addressesDb->connect_id . '" class="form-check-input"checked> ' . $title . '<br>';
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
                        <input type="checkbox" name="r_address_<?= $right_addressesDb->connect_id; ?>" class="form-check-input" <?= $checked; ?>> <?= $title; ?><br>
                    <?php } ?>
                </td>
            </tr>
            <?php
        }
    }

    /**
     * "merge_them" is the function that does the actual job of merging the data of two persons (left and right)
     */
    public function merge_them($left, $right, $mode)
    {
        global $db_functions, $humo_option;

        $eventManager = new EventManager($this->dbh);

        // merge algorithm - merge right into left
        // 1. if right has pers_fams with different wife - this Fxx is added to left's pers_fams (in humo_person)
        //    and in humo_family the Ixx of right is replaced with the Ixx of left
        //    Right's Ixx is deleted
        // 2. if right has pers_fams with identical wife - children are added to left's Fxx (in humo_family)
        //    and with each child the famc is changed to left's fams
        //    Right's Fxx is deleted
        //    Right's Ixx is deleted
        // 3. In either case whether right has family or not, if right has famc then in
        //    humo_family in right's parents Fxx, the child's Ixx is changed from right's to left's

        $this->leftPerson = $db_functions->get_person_with_id($left);
        $this->rightPerson = $db_functions->get_person_with_id($right);

        $name1 = $this->leftPerson->pers_firstname . ' ' . $this->leftPerson->pers_lastname; // store for notification later
        $name2 = $this->rightPerson->pers_firstname . ' ' . $this->rightPerson->pers_lastname; // store for notification later

        if ($this->rightPerson->pers_fams) {
            $spouse1 = '';
            $spouse2 = '';
            $count_doubles = 0;
            $same_spouse = false; // will be made true if identical spouses found in next "if"

            if ($this->leftPerson->pers_fams) {
                $fam1_arr = explode(";", $this->leftPerson->pers_fams);
                $fam2_arr = explode(";", $this->rightPerson->pers_fams);
                // start searching for spouses with same ged nr (were merged earlier) of both persons
                for ($n = 0; $n < count($fam1_arr); $n++) {
                    //$famqry1 = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $fam1_arr[$n] . "'";
                    //$famresult1 = $this->dbh->query($famqry1);
                    //$famresult1Db = $famresult1->fetch(PDO::FETCH_OBJ);
                    $famresult1Db = $db_functions->get_family($fam1_arr[$n]);

                    $spouse1 = $famresult1Db->fam_man;
                    if ($this->rightPerson->pers_sexe == "M") {
                        $spouse1 = $famresult1Db->fam_woman;
                    }
                    for ($m = 0; $m < count($fam2_arr); $m++) {
                        //$famqry2 = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $fam2_arr[$m] . "'";
                        //$famresult2 = $this->dbh->query($famqry2);
                        //$famresult2Db = $famresult2->fetch(PDO::FETCH_OBJ);
                        $famresult2Db = $db_functions->get_family($fam2_arr[$m]);

                        $spouse2 = $famresult2Db->fam_man;
                        if ($this->rightPerson->pers_sexe == "M") {
                            $spouse2 = $famresult2Db->fam_woman;
                        }
                        if (substr($spouse1, 0, 1) === "I" && $spouse1 == $spouse2) {
                            // found identical spouse, these F's have to be merged
                            // the substr makes sure that we find two identical real gednrs not 0==0 or ''==''
                            $same_spouse = true;
                            // make array of fam mysql objects with identical spouses
                            //(there may be more than one if they were merged earlier!)
                            $f1[] = $famresult1Db;
                            $f2[] = $famresult2Db;
                            $sp1[] = $spouse1;
                            $sp2[] = $spouse2; // need this????? after all spouse1 and spouse 2 are the same....
                        }
                    }
                }
                if ($same_spouse == true) {
                    // left has one or more fams with same wife (spouse was already merged)
                    // if right has children - add them to the left F

                    // with all possible families of the right person that will move to the left, change right's I for left I
                    $r_spouses = explode(';', $this->rightPerson->pers_fams);
                    for ($i = 0; $i < count($r_spouses); $i++) {
                        // get all fams
                        if ($this->rightPerson->pers_sexe == "M") {
                            $per = "fam_man";
                        } else {
                            $per = "fam_woman";
                        }
                        $qry = "UPDATE humo_families SET " . $per . " = '" . $this->leftPerson->pers_gedcomnumber . "' WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $r_spouses[$i] . "'";
                        $this->dbh->query($qry);
                    }
                    for ($i = 0; $i < count($f1); $i++) {
                        // with all identical spouses
                        if ($f2[$i]->fam_children) {
                            if ($f1[$i]->fam_children) {
                                // add right's children to left if not same gedcomnumber (=if not merged already)
                                $rightchld = $f2[$i]->fam_children;
                                $l_chld = explode(';', $f1[$i]->fam_children);
                                $r_chld = explode(';', $f2[$i]->fam_children);
                                for ($q = 0; $q < count($l_chld); $q++) {
                                    for ($w = 0; $w < count($r_chld); $w++) {
                                        if ($l_chld[$q] == $r_chld[$w]) {
                                            // same gedcomnumber
                                            $rightchld = str_replace($r_chld[$w] . ';', '', $rightchld . ';');
                                            if (substr($rightchld, -1, 1) == ';') {
                                                $rightchld = substr($rightchld, 0, -1);
                                            }
                                        }
                                    }
                                }
                                $childr = $rightchld != '' ? $f1[$i]->fam_children . ';' . $rightchld : $f1[$i]->fam_children;

                                // if children were moved to left, create warning about possible duplicate children that will be created
                                if ($rightchld != '') {
                                    $allch1 = explode(';', $f1[$i]->fam_children);
                                    $allch2 = explode(';', $rightchld);
                                    for ($z = 0; $z < count($allch1); $z++) {
                                        //TODO only need pers_firstname, pers_lastname?
                                        $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $allch1[$z] . "'";
                                        $chl1 = $this->dbh->query($qry);
                                        $chl1Db = $chl1->fetch(PDO::FETCH_OBJ);
                                        for ($y = 0; $y < count($allch2); $y++) {
                                            //TODO only need pers_firstname, pers_lastname?
                                            $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $allch2[$y] . "'";
                                            $chl2 = $this->dbh->query($qry);
                                            $chl2Db = $chl2->fetch(PDO::FETCH_OBJ);
                                            if (
                                                isset($chl1Db->pers_lastname) && isset($chl2Db->pers_lastname) && $chl1Db->pers_lastname == $chl2Db->pers_lastname && substr($chl1Db->pers_firstname, 0, $humo_option["merge_chars"]) === substr($chl2Db->pers_firstname, 0, $humo_option["merge_chars"])
                                            ) {
                                                $string1 = $allch1[$z] . '@' . $allch2[$y] . ';';
                                                $string2 = $allch2[$y] . '@' . $allch1[$z] . ';';
                                                // make sure this pair doesn't exist already in the string
                                                if (strstr($this->trees['relatives_merge'], $string1) === false && strstr($this->trees['relatives_merge'], $string2) === false) {
                                                    $this->trees['relatives_merge'] .= $string1;
                                                }
                                                $db_functions->update_settings('rel_merge_' . $this->trees['tree_id'], $this->trees['relatives_merge']);
                                            }
                                        }
                                    }
                                }
                            } else {
                                // only right has children
                                $childr = $f2[$i]->fam_children;
                            }
                            $qry = "UPDATE humo_families SET fam_children ='" . $childr . "' WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber='" . $f1[$i]->fam_gedcomnumber . "'";
                            $this->dbh->query($qry);

                            // change those childrens' famc to left F
                            $allchld = explode(";", $f2[$i]->fam_children);
                            foreach ($allchld as $value) {
                                $qry = "UPDATE humo_persons SET pers_famc='" . $f1[$i]->fam_gedcomnumber . "' WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber='" . $value . "'";
                                $this->dbh->query($qry);
                            }
                        }
                    }

                    // Add the right fams to left fams, without the F's that belonged to the duplicate right spouse(s)
                    $famstring = $this->rightPerson->pers_fams . ';';
                    for ($i = 0; $i < count($f1); $i++) {
                        // can use f1 or f2 they are the same size
                        for ($i = 0; $i < count($f2); $i++) {
                            $famstring = str_replace($f2[$i]->fam_gedcomnumber . ';', '', $famstring);
                        }
                    }
                    if (substr($famstring, -1, 1) === ';') {
                        $famstring = substr($famstring, 0, -1);
                    }
                    // take off last ;
                    $newstring = $famstring != '' ? $this->leftPerson->pers_fams . ';' . $famstring : $this->leftPerson->pers_fams;
                    $qry = "UPDATE humo_persons SET pers_fams = '" . $newstring . "' WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $this->leftPerson->pers_gedcomnumber . "'";
                    $this->dbh->query($qry);

                    // remove the F that belonged to the duplicate right spouse from that spouse as well - he/she is one and the same
                    for ($i = 0; $i < count($f1); $i++) {
                        // for each of the identical spouses
                        $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp1[$i] . "'";
                        $sp_data = $this->dbh->query($qry);
                        $sp_dataDb = $sp_data->fetch(PDO::FETCH_OBJ);
                        // TODO only need 2 items?
                        //$sp_dataDb=$db_functions->get_person($sp1[$i]);
                        if (isset($sp_dataDb)) {
                            $sp_string = $sp_dataDb->pers_fams . ';';
                            $sp_string = str_replace($f2[$i]->fam_gedcomnumber . ';', '', $sp_string);
                            if (substr($sp_string, -1, 1) === ';') {
                                // take off last ; again
                                $sp_string = substr($sp_string, 0, -1);
                            }
                            $qry = "UPDATE humo_persons SET pers_fams = '" . $sp_string . "' WHERE pers_id ='" . $sp_dataDb->pers_id . "'";
                            $this->dbh->query($qry);
                        }
                    }

                    // before we delete the F's of duplicate wifes from the database, we first check if they have items
                    // that are not known in the "receiving" F's. If so, we copy it to the corresponding left families
                    // to make one Db query only, we first put the necessary fields and values in an array
                    for ($i = 0; $i < count($f1); $i++) {
                        if ($f1[$i]->fam_kind == '' and $f2[$i]->fam_kind != '') {
                            $fam_items[$i]["fam_kind"] = $f2[$i]->fam_kind;
                        }
                        if ($f1[$i]->fam_relation_date == '' && $f2[$i]->fam_relation_date != '') {
                            $fam_items[$i]["fam_relation_date"] = $f2[$i]->fam_relation_date;
                        }
                        if ($f1[$i]->fam_relation_place == '' && $f2[$i]->fam_relation_place != '') {
                            $fam_items[$i]["fam_relation_place"] = $f2[$i]->fam_relation_place;
                        }
                        if ($f1[$i]->fam_relation_text == '' && $f2[$i]->fam_relation_text != '') {
                            $fam_items[$i]["fam_relation_text"] = $f2[$i]->fam_relation_text;
                        }
                        //if($f1[$i]->fam_relation_source=='' AND $f2[$i]->fam_relation_source!='') { $fam_items[$i]["fam_relation_source"] = $f2[$i]->fam_relation_source; }
                        if ($f1[$i]->fam_relation_end_date == '' && $f2[$i]->fam_relation_end_date != '') {
                            $fam_items[$i]["fam_relation_end_date"] = $f2[$i]->fam_relation_end_date;
                        }
                        if ($f1[$i]->fam_marr_notice_date == '' && $f2[$i]->fam_marr_notice_date != '') {
                            $fam_items[$i]["fam_marr_notice_date"] = $f2[$i]->fam_marr_notice_date;
                        }
                        if ($f1[$i]->fam_marr_notice_place == '' && $f2[$i]->fam_marr_notice_place != '') {
                            $fam_items[$i]["fam_marr_notice_place"] = $f2[$i]->fam_marr_notice_place;
                        }
                        if ($f1[$i]->fam_marr_notice_text == '' && $f2[$i]->fam_marr_notice_text != '') {
                            $fam_items[$i]["fam_marr_notice_text"] = $f2[$i]->fam_marr_notice_text;
                        }
                        //if($f1[$i]->fam_marr_notice_source=='' AND $f2[$i]->fam_marr_notice_source!='') { $fam_items[$i]["fam_marr_notice_source"] = $f2[$i]->fam_marr_notice_source; }
                        if ($f1[$i]->fam_marr_date == '' && $f2[$i]->fam_marr_date != '') {
                            $fam_items[$i]["fam_marr_date"] = $f2[$i]->fam_marr_date;
                        }
                        if ($f1[$i]->fam_marr_place == '' && $f2[$i]->fam_marr_place != '') {
                            $fam_items[$i]["fam_marr_place"] = $f2[$i]->fam_marr_place;
                        }
                        if ($f1[$i]->fam_marr_text == '' && $f2[$i]->fam_marr_text != '') {
                            $fam_items[$i]["fam_marr_text"] = $f2[$i]->fam_marr_text;
                        }
                        //if($f1[$i]->fam_marr_source=='' AND $f2[$i]->fam_marr_source!='') { $fam_items[$i]["fam_marr_source"] = $f2[$i]->fam_marr_source; }
                        if ($f1[$i]->fam_marr_authority == '' && $f2[$i]->fam_marr_authority != '') {
                            $fam_items[$i]["fam_marr_authority"] = $f2[$i]->fam_marr_authority;
                        }
                        if ($f1[$i]->fam_marr_church_notice_date == '' && $f2[$i]->fam_marr_church_notice_date != '') {
                            $fam_items[$i]["fam_marr_church_notice_date"] = $f2[$i]->fam_marr_church_notice_date;
                        }
                        if ($f1[$i]->fam_marr_church_notice_place == '' && $f2[$i]->fam_marr_church_notice_place != '') {
                            $fam_items[$i]["fam_marr_church_notice_place"] = $f2[$i]->fam_marr_church_notice_place;
                        }
                        if ($f1[$i]->fam_marr_church_notice_text == '' && $f2[$i]->fam_marr_church_notice_text != '') {
                            $fam_items[$i]["fam_marr_church_notice_text"] = $f2[$i]->fam_marr_church_notice_text;
                        }
                        //if($f1[$i]->fam_marr_church_notice_source=='' AND $f2[$i]->fam_marr_church_notice_source!='') { $fam_items[$i]["fam_marr_church_notice_source"] = $f2[$i]->fam_marr_church_notice_source; }
                        if ($f1[$i]->fam_marr_church_date == '' && $f2[$i]->fam_marr_church_date != '') {
                            $fam_items[$i]["fam_marr_church_date"] = $f2[$i]->fam_marr_church_date;
                        }
                        if ($f1[$i]->fam_marr_church_place == '' && $f2[$i]->fam_marr_church_place != '') {
                            $fam_items[$i]["fam_marr_church_place"] = $f2[$i]->fam_marr_church_place;
                        }
                        if ($f1[$i]->fam_marr_church_text == '' && $f2[$i]->fam_marr_church_text != '') {
                            $fam_items[$i]["fam_marr_church_text"] = $f2[$i]->fam_marr_church_text;
                        }
                        //if($f1[$i]->fam_marr_church_source=='' AND $f2[$i]->fam_marr_church_source!='') { $fam_items[$i]["fam_marr_church_source"] = $f2[$i]->fam_marr_church_source; }
                        if ($f1[$i]->fam_religion == '' && $f2[$i]->fam_religion != '') {
                            $fam_items[$i]["fam_religion"] = $f2[$i]->fam_religion;
                        }
                        if ($f1[$i]->fam_div_date == '' && $f2[$i]->fam_div_date != '') {
                            $fam_items[$i]["fam_div_date"] = $f2[$i]->fam_div_date;
                        }
                        if ($f1[$i]->fam_div_place == '' && $f2[$i]->fam_div_place != '') {
                            $fam_items[$i]["fam_div_place"] = $f2[$i]->fam_div_place;
                        }
                        if ($f1[$i]->fam_div_text == '' && $f2[$i]->fam_div_text != '') {
                            $fam_items[$i]["fam_div_text"] = $f2[$i]->fam_div_text;
                        }
                        //if($f1[$i]->fam_div_source=='' AND $f2[$i]->fam_div_source!='') { $fam_items[$i]["fam_div_source"] = $f2[$i]->fam_div_source; }
                        if ($f1[$i]->fam_div_authority == '' && $f2[$i]->fam_div_authority != '') {
                            $fam_items[$i]["fam_div_authority"] = $f2[$i]->fam_div_authority;
                        }
                        if ($f1[$i]->fam_text == '' && $f2[$i]->fam_text != '') {
                            $fam_items[$i]["fam_text"] = $f2[$i]->fam_text;
                        }
                        //if($f1[$i]->fam_text_source=='' AND $f2[$i]->fam_text_source!='') { $fam_items[$i]["fam_text_source"] = $f2[$i]->fam_text_source; }
                    }
                    for ($i = 0; $i < count($f1); $i++) {
                        if (isset($fam_items[$i])) {
                            $item_string = '';
                            foreach ($fam_items[$i] as $key => $value) {
                                $item_string .= $key . "='" . $value . "',";
                            }
                            $item_string = substr($item_string, 0, -1); // take off last comma

                            $qry = "UPDATE humo_families SET " . $item_string . " WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $f1[$i]->fam_gedcomnumber . "'";
                            $this->dbh->query($qry);
                        }
                    }

                    // TODO check if these queries can be combined. Use something like: AND connect_sub_kind LIKE '%_source'
                    // - new piece for fam sources that were removed in the code above 2052 - 2078)
                    for ($i = 0; $i < count($f1); $i++) {
                        $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_relation_source'";
                        $sourDb = $this->dbh->query($qry);
                        if ($sourDb->rowCount() == 0) {
                            // no fam sources of the sub kind for this fam
                            $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_relation_source'";
                            $sourDb2 = $this->dbh->query($qry2);
                            if ($sourDb2->rowCount() > 0) {
                                // second fam has source of this sub kind - transfer these sources to left fam
                                $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $this->trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_relation_source'";
                                $this->dbh->query($qry3);
                            }
                        }

                        $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_notice_source'";
                        $sourDb = $this->dbh->query($qry);
                        if ($sourDb->rowCount() == 0) {
                            // no fam sources of the sub kind for this fam
                            $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_notice_source'";
                            $sourDb2 = $this->dbh->query($qry2);
                            if ($sourDb2->rowCount() > 0) {
                                // second fam has source of this sub kind - transfer these sources to left fam
                                $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $this->trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_notice_source'";
                                $this->dbh->query($qry3);
                            }
                        }

                        $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_source'";
                        $sourDb = $this->dbh->query($qry);
                        if ($sourDb->rowCount() == 0) {
                            // no fam sources of the sub kind for this fam
                            $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_source'";
                            $sourDb2 = $this->dbh->query($qry2);
                            if ($sourDb2->rowCount() > 0) {
                                // second fam has source of this sub kind - transfer these sources to left fam
                                $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $this->trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_source'";
                                $this->dbh->query($qry3);
                            }
                        }

                        $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_notice_source'";
                        $sourDb = $this->dbh->query($qry);
                        if ($sourDb->rowCount() == 0) {
                            // no fam sources of the sub kind for this fam
                            $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_notice_source'";
                            $sourDb2 = $this->dbh->query($qry2);
                            if ($sourDb2->rowCount() > 0) {
                                // second fam has source of this sub kind - transfer these sources to left fam
                                $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $this->trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_notice_source'";
                                $this->dbh->query($qry3);
                            }
                        }

                        $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_source'";
                        $sourDb = $this->dbh->query($qry);
                        if ($sourDb->rowCount() == 0) {
                            // no fam sources of the sub kind for this fam
                            $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_source'";
                            $sourDb2 = $this->dbh->query($qry2);
                            if ($sourDb2->rowCount() > 0) {
                                // second fam has source of this sub kind - transfer these sources to left fam
                                $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $this->trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_source'";
                                $this->dbh->query($qry3);
                            }
                        }
                        $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_text_source'";
                        $sourDb = $this->dbh->query($qry);
                        if ($sourDb->rowCount() == 0) {
                            // no fam sources of the sub kind for this fam
                            $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $this->trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_text_source'";
                            $sourDb2 = $this->dbh->query($qry2);
                            if ($sourDb2->rowCount() > 0) {
                                // second fam has source of this sub kind - transfer these sources to left fam
                                $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $this->trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_text_source'";
                                $this->dbh->query($qry3);
                            }
                        }
                    }
                    // - end new piece for fam sources 

                    // delete F's that belonged to identical right spouse(s)
                    for ($i = 0; $i < count($f1); $i++) {
                        $qry = "DELETE FROM humo_events
                            WHERE event_tree_id='" . $this->trees['tree_id'] . "'
                            AND (event_connect_kind='family' OR event_kind='ASSO')
                            AND event_connect_id ='" . $f2[$i]->fam_gedcomnumber . "'";
                        $this->dbh->query($qry);

                        // for each of the identical spouses
                        $qry = "DELETE FROM humo_families
                            WHERE fam_tree_id='" . $this->trees['tree_id'] . "' 
                            AND fam_gedcomnumber ='" . $f2[$i]->fam_gedcomnumber . "'";
                        $this->dbh->query($qry);

                        // Substract 1 family from the number of families counter in the family tree.
                        $sql = "UPDATE humo_trees SET tree_families=tree_families-1 WHERE tree_id='" . $this->trees['tree_id'] . "'";
                        $this->dbh->query($sql);

                        // CLEANUP: also delete this F from other tables where it may appear
                        $qry = "DELETE FROM humo_addresses
                            WHERE address_tree_id='" . $this->trees['tree_id'] . "' 
                            AND address_connect_sub_kind='family'
                            AND address_connect_id ='" . $f2[$i]->fam_gedcomnumber . "'";
                        $this->dbh->query($qry);

                        $qry = "DELETE FROM humo_connections
                            WHERE connect_tree_id='" . $this->trees['tree_id'] . "'
                            AND connect_connect_id ='" . $f2[$i]->fam_gedcomnumber . "'";
                        $this->dbh->query($qry);
                    }
                    // check for other spouses that may have to be added to relative merge string
                    if (count($r_spouses) > count($f1)) {
                        // right had more than the identical spouse(s). maybe they need merging
                        $leftfam = explode(';', $this->leftPerson->pers_fams);
                        $rightfam = explode(';', $famstring);
                        for ($e = 0; $e < count($leftfam); $e++) {
                            $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $leftfam[$e] . "'";
                            $fam1 = $this->dbh->query($qry);
                            $fam1Db = $fam1->fetch(PDO::FETCH_OBJ);
                            $sp_ged = $fam1Db->fam_woman;
                            if ($this->leftPerson->pers_sexe == "F") {
                                $sp_ged = $fam1Db->fam_man;
                            }

                            $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp_ged . "'";
                            $spo1 = $this->dbh->query($qry);
                            $spo1Db = $spo1->fetch(PDO::FETCH_OBJ);
                            if ($spo1->rowCount() > 0) {
                                for ($f = 0; $f < count($rightfam); $f++) {
                                    $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $rightfam[$f] . "'";
                                    $fam2 = $this->dbh->query($qry);
                                    $fam2Db = $fam2->fetch(PDO::FETCH_OBJ);
                                    $sp_ged = $fam2Db->fam_woman;
                                    if ($this->leftPerson->pers_sexe == "F") {
                                        $sp_ged = $fam2Db->fam_man;
                                    }

                                    $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp_ged . "'";
                                    $spo2 = $this->dbh->query($qry);
                                    $spo2Db = $spo2->fetch(PDO::FETCH_OBJ);
                                    if ($spo2->rowCount() > 0 && ($spo1Db->pers_lastname == $spo2Db->pers_lastname && substr($spo1Db->pers_firstname, 0, $humo_option["merge_chars"]) === substr($spo2Db->pers_firstname, 0, $humo_option["merge_chars"]))) {
                                        $string1 = $spo1Db->pers_gedcomnumber . '@' . $spo2Db->pers_gedcomnumber . ';';
                                        $string2 = $spo2Db->pers_gedcomnumber . '@' . $spo1Db->pers_gedcomnumber . ';';
                                        // make sure this pair doesn't appear already in the string
                                        if (strstr($this->trees['relatives_merge'], $string1) === false && strstr($this->trees['relatives_merge'], $string2) === false) {
                                            $this->trees['relatives_merge'] .= $string1;
                                        }
                                        $db_functions->update_settings('rel_merge_' . $this->trees['tree_id'], $this->trees['relatives_merge']);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!$this->leftPerson->pers_fams || $same_spouse == false) {
                // left has no fams or fams with different spouses than right -> add fams to left

                // add right's F to left's fams
                $fam = $this->leftPerson->pers_fams ? $this->leftPerson->pers_fams . ";" . $this->rightPerson->pers_fams : $this->rightPerson->pers_fams;
                $qry = "UPDATE humo_persons SET pers_fams='" . $fam . "' WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $this->leftPerson->pers_gedcomnumber . "'";
                $this->dbh->query($qry);

                // in humo_family, under right's F, change fam_man/woman to left's I
                $self = "man";
                if ($this->leftPerson->pers_sexe == "F") {
                    $self = "woman";
                }

                //in all right's families (that are now moved to left!) change right's I to left's I
                $r_fams = explode(';', $this->rightPerson->pers_fams);
                for ($i = 0; $i < count($r_fams); $i++) {
                    $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $r_fams[$i] . "'";
                    $r_fm = $this->dbh->query($qry);
                    $r_fmDb = $r_fm->fetch(PDO::FETCH_OBJ);
                    $qry = "UPDATE humo_families SET fam_" . $self . "='" . $this->leftPerson->pers_gedcomnumber . "' WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber='" . $r_fams[$i] . "'";
                    $this->dbh->query($qry);
                }

                // check for spouses to be added to relative merge string:
                if ($this->leftPerson->pers_fams && $same_spouse == false) {
                    $leftfam = explode(';', $this->leftPerson->pers_fams);
                    $rightfam = explode(';', $this->rightPerson->pers_fams);
                    for ($e = 0; $e < count($leftfam); $e++) {
                        $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $leftfam[$e] . "'";
                        $fam1 = $this->dbh->query($qry);
                        $fam1Db = $fam1->fetch(PDO::FETCH_OBJ);
                        $sp_ged = $fam1Db->fam_woman;
                        if ($this->leftPerson->pers_sexe == "F") {
                            $sp_ged = $fam1Db->fam_man;
                        }

                        $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp_ged . "'";
                        $spo1 = $this->dbh->query($qry);
                        $spo1Db = $spo1->fetch(PDO::FETCH_OBJ);
                        if ($spo1->rowCount() > 0) {
                            for ($f = 0; $f < count($rightfam); $f++) {
                                $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $rightfam[$f] . "'";
                                $fam2 = $this->dbh->query($qry);
                                $fam2Db = $fam2->fetch(PDO::FETCH_OBJ);
                                $sp_ged = $fam2Db->fam_woman;
                                if ($this->leftPerson->pers_sexe == "F") {
                                    $sp_ged = $fam2Db->fam_man;
                                }

                                $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp_ged . "'";
                                $spo2 = $this->dbh->query($qry);
                                $spo2Db = $spo2->fetch(PDO::FETCH_OBJ);
                                if ($spo2->rowCount() > 0 && ($spo1Db->pers_lastname == $spo2Db->pers_lastname && substr($spo1Db->pers_firstname, 0, $humo_option["merge_chars"]) === substr($spo2Db->pers_firstname, 0, $humo_option["merge_chars"]))) {
                                    $string1 = $spo1Db->pers_gedcomnumber . '@' . $spo2Db->pers_gedcomnumber . ';';
                                    $string2 = $spo2Db->pers_gedcomnumber . '@' . $spo1Db->pers_gedcomnumber . ';';
                                    // make sure this pair doesn't already exist in the string
                                    if (strstr($this->trees['relatives_merge'], $string1) === false && strstr($this->trees['relatives_merge'], $string2) === false) {
                                        $this->trees['relatives_merge'] .= $string1;
                                    }
                                    $db_functions->update_settings('rel_merge_' . $this->trees['tree_id'], $this->trees['relatives_merge']);
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($this->rightPerson->pers_famc) {
            // if the two merged persons had a different parent set (e.i. parents aren't merged yet)
            // then in humo_family under right's parents' F, in fam_children, change right's I to left's I
            // (because right I will be deleted and as long as the double parents aren't merged we don't want errors
            // when accessing the children!

            $parqry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $this->rightPerson->pers_famc . "'";
            $parfam = $this->dbh->query($parqry);
            $parfamDb = $parfam->fetch(PDO::FETCH_OBJ);

            $children = $parfamDb->fam_children . ";";
            // add ; at end for following manipulation
            // we have to search for "I45;" if we searched for I34 without semi colon then also I346 would give true!
            // since the last entry doesn't have a ; we have to temporarily add it for the search.

            if (!$this->leftPerson->pers_famc || $this->leftPerson->pers_famc && $this->leftPerson->pers_famc != $this->rightPerson->pers_famc) {
                // left has no parents or a different parent set (at least one parent not merged yet)
                // --> change right I for left I in right's parents' F
                $children = str_replace($this->rightPerson->pers_gedcomnumber . ";", $this->leftPerson->pers_gedcomnumber . ";", $children);
                // check if to add to relatives merge string
                if ($this->leftPerson->pers_famc && $this->leftPerson->pers_famc != $this->rightPerson->pers_famc) {
                    // there is a double set of parents - these have to be merged by the user! Save in variables
                    $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $this->leftPerson->pers_famc . "'";
                    $par1 = $this->dbh->query($qry);
                    $par1Db = $par1->fetch(PDO::FETCH_OBJ);

                    $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $this->rightPerson->pers_famc . "'";
                    $par2 = $this->dbh->query($qry);
                    $par2Db = $par2->fetch(PDO::FETCH_OBJ);
                    // add the parents to string of surrounding relatives to be merged
                    // to help later with exploding, sets are separated by ";" and left and right are separated by "@"
                    if (
                        isset($par1Db->fam_man) && $par1Db->fam_man != '0' && isset($par2Db->fam_man) && $par2Db->fam_man != '0' && $par1Db->fam_man != $par2Db->fam_man
                    ) {
                        // make sure none of the two fathers is N.N. and that this father is not merged already!
                        $string1 = $par1Db->fam_man . '@' . $par2Db->fam_man . ";";
                        $string2 = $par2Db->fam_man . '@' . $par1Db->fam_man . ";";
                        // make sure this pair doesn't appear already in the string
                        if (strstr($this->trees['relatives_merge'], $string1) === false && strstr($this->trees['relatives_merge'], $string2) === false) {
                            $this->trees['relatives_merge'] .= $string1;
                        }
                    } elseif ((!isset($par1Db->fam_man) || $par1Db->fam_man == '0') && isset($par2Db->fam_man) && $par2Db->fam_man != '0') {
                        // left father is N.N. so move right father to left F
                        $this->dbh->query("UPDATE humo_families SET fam_man = '" . $par2Db->fam_man . "'
                        WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $this->leftPerson->pers_famc . "'");
                    }
                    if (
                        isset($par1Db->fam_woman) && $par1Db->fam_woman != '0' && isset($par2Db->fam_woman) && $par2Db->fam_woman != '0' && $par1Db->fam_woman != $par2Db->fam_woman
                    ) {
                        // make sure none of the two mothers is N.N. and that this mother is not merged already!
                        $string1 = $par1Db->fam_woman . '@' . $par2Db->fam_woman . ";";
                        $string2 = $par2Db->fam_woman . '@' . $par1Db->fam_woman . ";";
                        if (strstr($this->trees['relatives_merge'], $string1) === false && strstr($this->trees['relatives_merge'], $string2) === false) {
                            // make sure this pair doesn't appear already in the string
                            $this->trees['relatives_merge'] .= $string1;
                        }
                    } elseif ((!isset($par1Db->fam_woman) || $par1Db->fam_woman == '0') && isset($par2Db->fam_woman) && $par2Db->fam_woman != '0') {
                        // left mother is N.N. so move right mother to left F
                        $this->dbh->query("UPDATE humo_families SET fam_woman = '" . $par2Db->fam_woman . "'
                        WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber ='" . $this->leftPerson->pers_famc . "'");
                    }
                    $db_functions->update_settings('rel_merge_' . $this->trees['tree_id'], $this->trees['relatives_merge']);
                }
                if (!$this->leftPerson->pers_famc) {
                    // give left the famc of right
                    $qry = "UPDATE humo_persons SET pers_famc ='" . $this->rightPerson->pers_famc . "'
                    WHERE pers_tree_id='" . $this->trees['tree_id'] . "' AND pers_gedcomnumber ='" . $this->leftPerson->pers_gedcomnumber . "'";
                    $this->dbh->query($qry);
                }
            } elseif ($this->leftPerson->pers_famc && $this->leftPerson->pers_famc == $this->rightPerson->pers_famc) {
                // same parent set (double children in one family) just remove right's I from F
                // we can use right's F since this is also left's F....
                $children = str_replace($this->rightPerson->pers_gedcomnumber . ";", "", $children);
            }
            if (substr($children, -1) === ";") {
                // if the added ';' is still there, remove it
                $children = substr($children, 0, -1); // take off last ;
            }
            $qry = "UPDATE humo_families SET fam_children='" . $children . "' WHERE fam_tree_id='" . $this->trees['tree_id'] . "' AND fam_gedcomnumber = '" . $this->rightPerson->pers_famc . "'";
            $this->dbh->query($qry);
        }

        // PERSONAL DATA
        // default:
        // 1. if there is data for left only, or for left and right --> the left data is retained.
        // 2. if right has data and left hasn't --> right's data is transfered to left
        // in manual, duplicate and relatives merge this can be over-ruled by the admin with the radio buttons

        // for automatic merge see if data has to be transferred from right to left
        // (for manual, duplicate and relative merge this is done in the form with radio buttons by the user)
        $l_name = '1';
        $f_name = '1';
        $b_date = '1';
        $b_place = '1';
        $d_date = '1';
        $d_place = '1';
        $b_time = '1';
        $b_text = '1';
        $d_time = '1';
        $d_text = '1';
        $d_cause = '1';
        $br_date = '1';
        $br_place = '1';
        $br_text = '1';
        $bp_date = '1';
        $bp_place = '1';
        $bp_text = '1';
        $crem = '1';
        $reli = '1';
        $code = '1';
        $stborn = '1';
        $alive = '1';
        $c_name = '1';
        $patr = '1';
        $fav = '1';
        $n_text = '1';
        $text = '1';

        if ($mode == 'automatic') {
            // the regular items for automatic mode
            // 2 = move text to left person.
            // 3 = append right text to left text
            if ($this->leftPerson->pers_birth_date == '' && $this->rightPerson->pers_birth_date != '') {
                $b_date = '2';
            }
            if ($this->leftPerson->pers_birth_place == '' && $this->rightPerson->pers_birth_place != '') {
                $b_place = '2';
            }
            if ($this->leftPerson->pers_death_date == '' && $this->rightPerson->pers_death_date != '') {
                $d_date = '2';
            }
            if ($this->leftPerson->pers_death_place == '' && $this->rightPerson->pers_death_place != '') {
                $d_place = '2';
            }
            if ($this->leftPerson->pers_birth_time == '' && $this->rightPerson->pers_birth_time != '') {
                $b_time = '2';
            }
            if ($this->leftPerson->pers_birth_text == '' && $this->rightPerson->pers_birth_text != '') {
                $b_text = '2';
            }
            if ($this->leftPerson->pers_death_time == '' && $this->rightPerson->pers_death_time != '') {
                $d_time = '2';
            }
            if ($this->leftPerson->pers_death_text == '' && $this->rightPerson->pers_death_text != '') {
                $d_text = '2';
            }
            if ($this->leftPerson->pers_death_cause == '' && $this->rightPerson->pers_death_cause != '') {
                $d_cause = '2';
            }
            if ($this->leftPerson->pers_buried_date == '' && $this->rightPerson->pers_buried_date != '') {
                $br_date = '2';
            }
            if ($this->leftPerson->pers_buried_place == '' && $this->rightPerson->pers_buried_place != '') {
                $br_place = '2';
            }
            if ($this->leftPerson->pers_buried_text == '' && $this->rightPerson->pers_buried_text != '') {
                $br_text = '2';
            }
            if ($this->leftPerson->pers_bapt_date == '' && $this->rightPerson->pers_bapt_date != '') {
                $bp_date = '2';
            }
            if ($this->leftPerson->pers_bapt_place == '' && $this->rightPerson->pers_bapt_place != '') {
                $bp_place = '2';
            }
            if ($this->leftPerson->pers_bapt_text == '' && $this->rightPerson->pers_bapt_text != '') {
                $bp_text = '2';
            }
            if ($this->leftPerson->pers_religion == '' && $this->rightPerson->pers_religion != '') {
                $reli = '2';
            }
            if ($this->leftPerson->pers_own_code == '' && $this->rightPerson->pers_own_code != '') {
                $code = '2';
            }
            if ($this->leftPerson->pers_stillborn == '' && $this->rightPerson->pers_stillborn != '') {
                $stborn = '2';
            }
            if ($this->leftPerson->pers_alive == '' && $this->rightPerson->pers_alive != '') {
                $alive = '2';
            }
            if ($this->leftPerson->pers_patronym == '' && $this->rightPerson->pers_patronym != '') {
                $patr = '2';
            }
            if ($this->leftPerson->pers_name_text == '' && $this->rightPerson->pers_name_text != '') {
                $n_text = '2';
            }
            if ($this->leftPerson->pers_text == '' && $this->rightPerson->pers_text != '') {
                $text = '2';
            }
            if ($this->leftPerson->pers_cremation == '' && $this->rightPerson->pers_cremation != '') {
                $crem = '2';
            }
        } else {
            // *** Manual merge ***

            // *** Birth ***
            if (isset($_POST['b_date']) && $_POST['b_date'] == '2') {
                $b_date = '2';
            }
            if (isset($_POST['b_place']) && $_POST['b_place'] == '2') {
                $b_place = '2';
            }
            if (isset($_POST['b_time']) && $_POST['b_time'] == '2') {
                $b_time = '2';
            }
            if (isset($_POST['b_text']) && $_POST['b_text'] == '2') {
                $b_text = '2';
            }
            if (isset($_POST['stborn']) && $_POST['stborn'] == '2') {
                $stborn = '2';
            }
            //isset($_POST["pers_birth_date_hebnight"]

            // *** Baptised ***
            if (isset($_POST['bp_date']) && $_POST['bp_date'] == '2') {
                $bp_date = '2';
            }
            if (isset($_POST['bp_place']) && $_POST['bp_place'] == '2') {
                $bp_place = '2';
            }
            if (isset($_POST['bp_text']) && $_POST['bp_text'] == '2') {
                $bp_text = '2';
            }

            // *** Death ***
            if (isset($_POST['d_date']) && $_POST['d_date'] == '2') {
                $d_date = '2';
            }
            if (isset($_POST['d_place']) && $_POST['d_place'] == '2') {
                $d_place = '2';
            }
            if (isset($_POST['d_text']) && $_POST['d_text'] == '2') {
                $d_text = '2';
            }
            if (isset($_POST['d_time']) && $_POST['d_time'] == '2') {
                $d_time = '2';
            }
            if (isset($_POST['d_cause']) && $_POST['d_cause'] == '2') {
                $d_cause = '2';
            }

            // *** Buried ***
            if (isset($_POST['br_date']) && $_POST['br_date'] == '2') {
                $br_date = '2';
            }
            if (isset($_POST['br_place']) && $_POST['br_place'] == '2') {
                $br_place = '2';
            }
            if (isset($_POST['br_text']) && $_POST['br_text'] == '2') {
                $br_text = '2';
            }
            if (isset($_POST['crem']) && $_POST['crem'] == '2') {
                $crem = '2';
            }
        }

        // *** Update manually selected ($_POST) or automatically selected items ***
        // EXAMPLE: $this->check_regular(MANUAL $_POST variable, AUTO variable, 'pers_lastname');
        $this->check_regular('l_name', $l_name, 'pers_lastname');
        $this->check_regular('f_name', $f_name, 'pers_firstname');
        $this->check_regular('reli', $reli, 'pers_religion');
        $this->check_regular('code', $code, 'pers_own_code');
        $this->check_regular('alive', $alive, 'pers_alive');
        $this->check_regular('patr', $patr, 'pers_patronym');
        $this->check_regular_text('n_text', $n_text, 'pers_name_text');
        $this->check_regular_text('text', $text, 'pers_text');

        // *** Add or update birth event (left person) ***
        // TODO: pers_birth_date_hebnight
        if ($b_date == '2' || $b_place == '2' || $b_time == '2' || $b_text == '2' || $stborn == '2') {
            $birth_event = [
                'tree_id' => $this->leftPerson->pers_tree_id,
                'event_person_id' => $this->leftPerson->pers_id,
                'event_connect_kind' => 'person',
                'event_connect_id' => $this->leftPerson->pers_gedcomnumber,
                'event_kind' => 'birth',
                'event_event' => '',
                'event_gedcom' => ''
            ];

            if ($b_date == '2') {
                $birth_event['event_date'] = $this->rightPerson->pers_birth_date;
            }
            if ($b_place == '2') {
                $birth_event['event_place'] = $this->rightPerson->pers_birth_place;
            }
            if ($b_time == '2') {
                $birth_event['event_time'] = $this->rightPerson->pers_birth_time;
            }
            if ($b_text == '2') {
                $birth_event['event_text'] = $this->rightPerson->pers_birth_text;
            }
            if ($stborn == '2') {
                $birth_event['event_stillborn'] = $this->rightPerson->pers_stillborn;
            }
            //'event_date_hebnight' => isset($_POST["pers_birth_date_hebnight"]) ? $_POST["pers_birth_date_hebnight"] : ''

            if (isset($this->leftPerson->pers_birth_event_id)) {
                $birth_event['event_id'] = $this->leftPerson->pers_birth_event_id;
            }
            $eventManager->update_event($birth_event);

            // *** Remove right person birth event ***
            if (isset($this->rightPerson->pers_birth_event_id)) {
                $this->dbh->query("DELETE FROM humo_events WHERE event_id = '" . $this->rightPerson->pers_birth_event_id . "'");
            }
        }

        // *** Add or update baptise event (left person) ***
        if ($bp_date == '2' || $bp_place == '2' || $bp_text == '2') {
            $baptise_event = [
                'tree_id' => $this->leftPerson->pers_tree_id,
                'event_person_id' => $this->leftPerson->pers_id,
                'event_connect_kind' => 'person',
                'event_connect_id' => $this->leftPerson->pers_gedcomnumber,
                'event_kind' => 'baptism',
                'event_event' => '',
                'event_gedcom' => ''
            ];

            if ($bp_date == '2') {
                $baptise_event['event_date'] = $this->rightPerson->pers_bapt_date;
            }
            if ($bp_place == '2') {
                $baptise_event['event_place'] = $this->rightPerson->pers_bapt_place;
            }
            if ($bp_text == '2') {
                $baptise_event['event_text'] = $this->rightPerson->pers_bapt_text;
            }

            if (isset($this->leftPerson->pers_bapt_event_id)) {
                $baptise_event['event_id'] = $this->leftPerson->pers_bapt_event_id;
            }
            $eventManager->update_event($baptise_event);

            // *** Remove right person baptise event ***
            if (isset($this->rightPerson->pers_bapt_event_id)) {
                $this->dbh->query("DELETE FROM humo_events WHERE event_id = '" . $this->rightPerson->pers_bapt_event_id . "'");
            }
        }

        // *** Add or update death event (left person) ***
        //TODO: pers_death_date_hebnight, pers_death_age
        if ($d_date == '2' || $d_place == '2' || $d_time == '2' || $d_text == '2' || $d_cause == '2') {
            $death_event = [
                'tree_id' => $this->leftPerson->pers_tree_id,
                'event_person_id' => $this->leftPerson->pers_id,
                'event_connect_kind' => 'person',
                'event_connect_id' => $this->leftPerson->pers_gedcomnumber,
                'event_kind' => 'death',
                'event_event' => '',
                'event_gedcom' => ''
            ];
            if ($d_date == '2') {
                $death_event['event_date'] = $this->rightPerson->pers_death_date;
            }
            if ($d_place == '2') {
                $death_event['event_place'] = $this->rightPerson->pers_death_place;
            }
            if ($d_time == '2') {
                $death_event['event_time'] = $this->rightPerson->pers_death_time;
            }
            if ($d_text == '2') {
                $death_event['event_text'] = $this->rightPerson->pers_death_text;
            }
            if ($d_cause == '2') {
                $death_event['event_cause'] = $this->rightPerson->pers_death_cause;
            }
            if (isset($this->leftPerson->pers_death_event_id)) {
                $death_event['event_id'] = $this->leftPerson->pers_death_event_id;
            }
            $eventManager->update_event($death_event);
            // *** Remove right person death event ***
            if (isset($this->rightPerson->pers_death_event_id)) {
                $this->dbh->query("DELETE FROM humo_events WHERE event_id = '" . $this->rightPerson->pers_death_event_id . "'");
            }
        }

        // *** Add or update buried event (left person) ***
        // TODO pers_buried_date_hebnight
        if ($br_date == '2' || $br_place == '2' || $br_text == '2' || $crem == '2') {
            $buried_event = [
                'tree_id' => $this->leftPerson->pers_tree_id,
                'event_person_id' => $this->leftPerson->pers_id,
                'event_connect_kind' => 'person',
                'event_connect_id' => $this->leftPerson->pers_gedcomnumber,
                'event_kind' => 'burial',
                'event_event' => '',
                'event_gedcom' => ''
            ];
            if ($br_date == '2') {
                $buried_event['event_date'] = $this->rightPerson->pers_buried_date;
            }
            if ($br_place == '2') {
                $buried_event['event_place'] = $this->rightPerson->pers_buried_place;
            }
            if ($br_text == '2') {
                $buried_event['event_text'] = $this->rightPerson->pers_buried_text;
            }
            if ($crem == '2') {
                $buried_event['event_cremation'] = $this->rightPerson->pers_cremation;
            }
            if (isset($this->leftPerson->pers_buried_event_id)) {
                $buried_event['event_id'] = $this->leftPerson->pers_buried_event_id;
            }
            $eventManager->update_event($buried_event);
            // *** Remove right person buried event ***
            if (isset($this->rightPerson->pers_buried_event_id)) {
                $this->dbh->query("DELETE FROM humo_events WHERE event_id = '" . $this->rightPerson->pers_buried_event_id . "'");
            }
        }

        // check for posted event, address and source items (separate functions below process input from comparison form)
        if ($mode != 'automatic') {
            $right_event_array = array();
            // Skip events like birth, baptise, death and buried
            $skip_events = ["birth", "baptism", "death", "burial"];
            $left_events = $this->dbh->query("SELECT * FROM humo_events
                WHERE (event_connect_kind='person' OR event_kind='ASSO') 
                AND event_person_id ='" . $this->leftPerson->pers_id . "' 
                AND event_kind NOT IN ('" . implode("','", $skip_events) . "')
                ORDER BY event_kind ");

            // Skip events like birth, baptism, death and burial
            $skip_events = ["birth", "baptism", "death", "burial"];
            $right_events = $this->dbh->query("SELECT * FROM humo_events
                WHERE (event_connect_kind='person' OR event_kind='ASSO') 
                AND event_person_id ='" . $this->rightPerson->pers_id . "' 
                AND event_kind NOT IN ('" . implode("','", $skip_events) . "')
                ORDER BY event_kind ");

            if ($right_events->rowCount() > 0) {
                // if right has no events it did not appear in the comparison table, so the whole thing is unnecessary
                while ($right_eventsDb = $right_events->fetch(PDO::FETCH_OBJ)) {
                    $right_event_array[$right_eventsDb->event_kind] = "1"; // we need this to know whether to handle left
                    if (isset($_POST['r_' . $right_eventsDb->event_kind . '_' . $right_eventsDb->event_id])) {
                        // change right's I to left's I
                        $this->dbh->query("UPDATE humo_events SET
                            event_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "',
                            event_person_id = '" . $this->leftPerson->pers_id . "'
                            WHERE event_id ='" . $right_eventsDb->event_id . "'");
                    } elseif (isset($_POST['r_' . $right_eventsDb->event_connect_kind . '_' . $right_eventsDb->event_id])) {
                        // change right's I to left's I
                        $this->dbh->query("UPDATE humo_events SET
                            event_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "',
                            event_person_id = '" . $this->leftPerson->pers_id . "'
                            WHERE event_id ='" . $right_eventsDb->event_id . "'");
                    } else {
                        // clean up database -> remove this entry altogether (IF IT EXISTS...)
                        $this->dbh->query("DELETE FROM humo_events WHERE event_id ='" . $right_eventsDb->event_id . "'");
                    }
                }
                while ($left_eventsDb = $left_events->fetch(PDO::FETCH_OBJ)) {
                    if (isset($right_event_array[$left_eventsDb->event_kind]) && $right_event_array[$left_eventsDb->event_kind] === "1" && !isset($_POST['l_' . $left_eventsDb->event_kind . '_' . $left_eventsDb->event_id])) {
                        $this->dbh->query("DELETE FROM humo_events WHERE event_id ='" . $left_eventsDb->event_id . "'");
                    }
                }
            }

            $left_address = $this->dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND LOCATE('address',connect_sub_kind)!=0 AND connect_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "'");
            $right_address = $this->dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND LOCATE('address',connect_sub_kind)!=0 AND connect_connect_id ='" . $this->rightPerson->pers_gedcomnumber . "'");
            if ($right_address->rowCount() > 0) {
                //if right has no addresses it did not appear in the comparison table, so the whole thing is unnecessary
                while ($left_addressDb = $left_address->fetch(PDO::FETCH_OBJ)) {
                    if (!isset($_POST['l_address_' . $left_addressDb->connect_id])) {
                        $this->dbh->query("DELETE FROM humo_connections WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND connect_id ='" . $left_addressDb->connect_id . "'");
                    }
                }
                while ($right_addressDb = $right_address->fetch(PDO::FETCH_OBJ)) {
                    if (isset($_POST['r_address_' . $right_addressDb->connect_id])) {
                        // change right's I to left's I
                        $this->dbh->query("UPDATE humo_connections SET connect_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "' WHERE connect_id ='" . $right_addressDb->connect_id . "'");
                    } else {
                        // clean up database -> remove this entry altogether (IF IT EXISTS...)
                        $this->dbh->query("DELETE FROM humo_connections WHERE connect_id ='" . $right_addressDb->connect_id . "'");
                    }
                }
            }

            $left_source = $this->dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND LOCATE('source',connect_sub_kind)!=0 AND connect_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "'");
            $right_source = $this->dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND LOCATE('source',connect_sub_kind)!=0 AND connect_connect_id ='" . $this->rightPerson->pers_gedcomnumber . "'");
            if ($right_source->rowCount() > 0) {
                //if right has no sources it did not appear in the comparison table, so the whole thing is unnecessary
                while ($left_sourceDb = $left_source->fetch(PDO::FETCH_OBJ)) {
                    if (!isset($_POST['l_source_' . $left_sourceDb->connect_id])) {
                        $this->dbh->query("DELETE FROM humo_connections WHERE connect_id ='" . $left_sourceDb->connect_id . "'");
                    }
                }
                while ($right_sourceDb = $right_source->fetch(PDO::FETCH_OBJ)) {
                    if (isset($_POST['r_source_' . $right_sourceDb->connect_id])) {
                        // change right's I to left's I
                        $this->dbh->query("UPDATE humo_connections SET connect_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "' WHERE connect_id ='" . $right_sourceDb->connect_id . "'");
                    } else {
                        // clean up database -> remove this entry altogether (IF IT EXISTS...)
                        $this->dbh->query("DELETE FROM humo_connections WHERE connect_id ='" . $right_sourceDb->connect_id . "'");
                    }
                }
            }
        } else {
            // for automatic mode check for situation where right has event/source/address data and left not. In that case use right's.
            $right_result = $this->dbh->query("SELECT * FROM humo_events WHERE event_person_id ='" . $this->rightPerson->pers_id . "'");
            while ($right_resultDb = $right_result->fetch(PDO::FETCH_OBJ)) {
                $left_result = $this->dbh->query("SELECT * FROM humo_events WHERE event_person_id ='" . $this->leftPerson->pers_id . "'");
                $foundleft = false;
                while ($left_resultDb = $left_result->fetch(PDO::FETCH_OBJ)) {
                    if ($left_resultDb->event_kind == $right_resultDb->event_kind && $left_resultDb->event_gedcom == $right_resultDb->event_gedcom) {
                        // NOTE: if "event" or "name" we also check for sub-type (_AKAN, _HEBN, BARM etc) so as not to match different subtypes
                        // this event from right wil not be copied to left - left already has this type event
                        // so clear the database
                        $this->dbh->query("DELETE FROM humo_events WHERE event_id ='" . $right_resultDb->event_id . "'");
                        $foundleft = true;
                    }
                }
                if ($foundleft == false) {
                    // left has no such type of event, so change right's I for left I at this event
                    $this->dbh->query("UPDATE humo_events
                        SET event_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "',
                            event_person_id = '" . $this->leftPerson->pers_id . "'
                        WHERE event_id ='" . $right_resultDb->event_id . "'");
                }
            }

            // Do same for sources and address (from connections table). no need here to differentiate between sources and addresses, all will be handled
            $right_result = $this->dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND connect_connect_id ='" . $this->rightPerson->pers_gedcomnumber . "'");
            while ($right_resultDb = $right_result->fetch(PDO::FETCH_OBJ)) {
                $left_result = $this->dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND connect_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "'");
                $foundleft = false;
                while ($left_resultDb = $left_result->fetch(PDO::FETCH_OBJ)) {
                    if ($left_resultDb->connect_sub_kind == $right_resultDb->connect_sub_kind) {
                        // NOTE: We check for sub-kind so as not to match different sub_kinds
                        // this source/address sub_kind from right will not be copied to left - left already has a source/address for this sub_kind
                        // so clear right's data from the database
                        $this->dbh->query("DELETE FROM humo_connections WHERE connect_id ='" . $right_resultDb->connect_id . "'");
                        $foundleft = true;
                    }
                }
                if ($foundleft == false) {
                    // left has no such sub_kind of source/address, so change right's I for left I at this sub_kind
                    $this->dbh->query("UPDATE humo_connections SET connect_connect_id ='" . $this->leftPerson->pers_gedcomnumber . "' WHERE connect_id ='" . $right_resultDb->connect_id . "'");
                }
            }
        }

        // Substract 1 person from the number of persons counter in the family tree.
        $sql = "UPDATE humo_trees SET tree_persons=tree_persons-1 WHERE tree_id='" . $this->trees['tree_id'] . "'";
        $this->dbh->query($sql);

        // CLEANUP: delete this person's I from any other tables that refer to this person
        // *** TODO 2021: address_connect_xxxx is no longer in use. Will be removed later ***
        $qry = "DELETE FROM humo_addresses WHERE address_tree_id='" . $this->trees['tree_id'] . "' AND address_connect_sub_kind='person' AND address_connect_id ='" . $this->rightPerson->pers_gedcomnumber . "'";
        $this->dbh->query($qry);

        $qry = "DELETE FROM humo_connections WHERE connect_tree_id='" . $this->trees['tree_id'] . "' AND connect_connect_id ='" . $this->rightPerson->pers_gedcomnumber . "'";
        $this->dbh->query($qry);

        $qry = "DELETE FROM humo_events WHERE event_person_id ='" . $this->rightPerson->pers_id . "'";
        $this->dbh->query($qry);

        // CLEANUP: This person's I may still exist in the humo_events table under "event_event" (in event_connect_id2 field),
        // in case of birth/death declaration or bapt/burial witness. If so, change the GEDCOM to the left person's I:
        $qry = "UPDATE humo_events
            SET event_connect_id2 = '" . $this->leftPerson->pers_gedcomnumber . "'
            WHERE event_tree_id='" . $this->trees['tree_id'] . "'
            AND event_connect_id2 ='" . $this->rightPerson->pers_gedcomnumber . "'";
        $this->dbh->query($qry);

        // Delete right I from humo_persons table
        $qry = "DELETE FROM humo_persons WHERE pers_id ='" . $this->rightPerson->pers_id . "'";
        $this->dbh->query($qry);

        // Remove from the relatives-to-merge pairs in the database any pairs that contain the deleted right person
        if (isset($this->trees['relatives_merge'])) {
            $temp_rel_arr = explode(";", $this->trees['relatives_merge']);
            $new_rel_string = '';
            for ($x = 0; $x < count($temp_rel_arr); $x++) {
                // one array piece is I354@I54. We DONT want to match "I35" or "I5" 
                // so to make sure we find the complete number we look for I354@ or for I345;
                if (
                    strstr($temp_rel_arr[$x], $this->rightPerson->pers_gedcomnumber . "@") === false && strstr($temp_rel_arr[$x] . ";", $this->rightPerson->pers_gedcomnumber . ";") === false
                ) {
                    $new_rel_string .= $temp_rel_arr[$x] . ";";
                }
            }
            $this->trees['relatives_merge'] = substr($new_rel_string, 0, -1); // take off last ;
            $db_functions->update_settings('rel_merge_' . $this->trees['tree_id'], $this->trees['relatives_merge']);
        }

        if (isset($_SESSION['dupl_arr_' . $this->trees['tree_id']])) {
            //remove this pair from the dupl_arr array
            $found1 = $this->leftPerson->pers_id . ';' . $this->rightPerson->pers_id;
            $found2 = $this->rightPerson->pers_id . ';' . $this->leftPerson->pers_id;
            for ($z = 0; $z < count($_SESSION['dupl_arr_' . $this->trees['tree_id']]); $z++) {
                if ($_SESSION['dupl_arr_' . $this->trees['tree_id']][$z] == $found1 or $_SESSION['dupl_arr_' . $this->trees['tree_id']][$z] == $found2) {
                    //unset($_SESSION['dupl_arr'][$z]) ;
                    array_splice($_SESSION['dupl_arr_' . $this->trees['tree_id']], $z, 1);
                }
            }
        }

        if ($mode != 'automatic' && $mode != 'relatives') {
            echo '<br>' . $name2 . __(' was successfully merged into ') . $name1 . '<br><br>';  // john was successfully merged into jack
            $rela = explode(';', $this->trees['relatives_merge']);
            $rela = count($rela) - 1;
            if ($rela > 0) {
                printf(__('After this merge there are %d surrounding relatives to be checked for merging!'), $rela);
                echo '<br><br>';

                echo __('<b>You are strongly advised to move to "Relatives merge" mode to check all surrounding persons who may have to be checked for merging.</b><br>
While in "Relatives merge" mode, any persons who might need merging as a result of consequent merges will be added automatically.<br>
This is the easiest way to make sure you don\'t forget anyone.');
                echo '<br><br>';
            ?>
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $this->trees['tree_id']; ?>">
                    <input type="submit" style="font-weight:bold;font-size:120%" name="relatives" value="<?= __('Relatives merge'); ?>" class="btn btn-sm btn-success ms-3">
                </form>

                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $this->trees['tree_id']; ?>">
                    <?php if (isset($_POST['left'])) { ?>
                        <input type="submit" name="manual" value="<?= __('Continue manual merge'); ?>" class="btn btn-sm btn-success ms-5">
                    <?php } else { ?>
                        <input type="submit" name="duplicate_compare" value="<?= __('Continue duplicate merge'); ?>" class="btn btn-sm btn-success ms-5">
                    <?php } ?>
                </form>
            <?php } else { ?>
                <br>
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $this->trees['tree_id']; ?>">
                    <?php if (isset($_POST['left'])) { ?>
                        <input type="submit" name="manual" value="<?= __('Choose another pair'); ?>" class="btn btn-sm btn-success">
                    <?php } else { ?>
                        <input type="submit" name="duplicate_compare" value="<?= __('Continue with next pair'); ?>" class="btn btn-sm btn-success">
                    <?php } ?>
                </form>
<?php
            }
        }
    }

    /**
     * function check_regular checks if data from the humo_person table was marked (checked) in the comparison table
     */
    private function check_regular($post_var, $auto_var, $mysql_var)
    {
        if (isset($_POST[$post_var]) && $_POST[$post_var] == '2' || $auto_var == '2') {
            $qry = "UPDATE humo_persons SET " . $mysql_var . " = '" . $this->rightPerson->$mysql_var . "' WHERE pers_id ='" . $this->leftPerson->pers_id . "'";
            $this->dbh->query($qry);
        }
    }

    /**
     * function check_regular_text checks if text data from the humo_person table was marked (checked) in the comparison table
     */
    private function check_regular_text($post_var, $auto_var, $mysql_var)
    {
        if (isset($_POST[$post_var . '_r']) || $auto_var == '2') {
            if (isset($_POST[$post_var . '_l'])) {
                // when not in automatic mode, this means we have to join the notes of left and right
                // If left or right has a @N34@ text entry we join the text as regular text.
                // We can't change the notes in humoX_texts because they could be used for other persons!
                if (substr($this->leftPerson->$mysql_var, 0, 2) === '@N') {
                    $noteqry = $this->dbh->query("SELECT text_text FROM humo_texts WHERE text_tree_id='" . $this->trees['tree_id'] . "' AND text_gedcomnr = '" . substr($this->leftPerson->$mysql_var, 1, -1) . "'");
                    $noteqryDb = $noteqry->fetch(PDO::FETCH_OBJ);
                    $leftnote = $noteqryDb->text_text;
                } else {
                    $leftnote = $this->leftPerson->$mysql_var;
                }
                if (substr($this->rightPerson->$mysql_var, 0, 2) === '@N') {
                    $noteqry = $this->dbh->query("SELECT text_text FROM humo_texts WHERE text_tree_id='" . $this->trees['tree_id'] . "' AND text_gedcomnr = '" . substr($this->rightPerson->$mysql_var, 1, -1) . "'");
                    $noteqryDb = $noteqry->fetch(PDO::FETCH_OBJ);
                    $rightnote = $noteqryDb->text_text;
                } else {
                    $rightnote = $this->rightPerson->$mysql_var;
                }
                $qry = "UPDATE humo_persons SET " . $mysql_var . " = CONCAT('" . $leftnote . "',\"\n\",'" . $rightnote . "') WHERE pers_id ='" . $this->leftPerson->pers_id . "'";
            } else {
                $qry = "UPDATE humo_persons SET " . $mysql_var . " = '" . $this->rightPerson->$mysql_var . "' WHERE pers_id ='" . $this->leftPerson->pers_id . "'";
            }
            $this->dbh->query($qry);
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
