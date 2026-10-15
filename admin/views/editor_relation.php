<?php

/**
 * Marriages/ relations and children list
 * 
 * When marriage is added, man is first and woman is second (this is done automatically if sexe is known!).
 * This is needed to show proper colours in graphical reports.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$datePlace = new \Genealogy\Include\DatePlace();
$languageDate = new \Genealogy\Include\LanguageDate;
$HtmlEscaper = new \Genealogy\Include\HtmlEscaper();
$validateGedcomnumber = new \Genealogy\Include\ValidateGedcomnumber();
$eventManager = new \Genealogy\Include\EventManager($dbh);
$orderChildren = new \Genealogy\Include\OrderChildren();

// TODO: move code to model script.
$relations = $db_functions->get_relations($person->pers_id);
$familyDb = $db_functions->get_family($marriage);

if ($familyDb) {
    $fam_kind = $familyDb->fam_kind;
    $man_gedcomnumber = $familyDb->partner1_gedcomnumber;
    $woman_gedcomnumber = $familyDb->partner2_gedcomnumber;
    $fam_gedcomnumber = $familyDb->fam_gedcomnumber;
    $fam_relation_date = $familyDb->fam_relation_date;
    $fam_relation_end_date = $familyDb->fam_relation_end_date;
    // *** Check if variabele exists, needed for PHP 8.1 ***
    $fam_relation_place = '';
    if (isset($familyDb->fam_relation_place)) {
        $fam_relation_place = $familyDb->fam_relation_place;
    }
    $fam_relation_text = $editor_cls->text_show($familyDb->fam_relation_text);
    $fam_marr_notice_date = $familyDb->fam_marr_notice_date;
    $fam_marr_notice_place = '';
    if (isset($familyDb->fam_marr_notice_place)) {
        $fam_marr_notice_place = $familyDb->fam_marr_notice_place;
    }
    $fam_marr_notice_text = $editor_cls->text_show($familyDb->fam_marr_notice_text);
    $fam_marr_date = $familyDb->fam_marr_date;
    $fam_marr_place = '';
    if (isset($familyDb->fam_marr_place)) {
        $fam_marr_place = $familyDb->fam_marr_place;
    }
    $fam_marr_text = $editor_cls->text_show($familyDb->fam_marr_text);
    $fam_marr_authority = $editor_cls->text_show($familyDb->fam_marr_authority);
    $partner1_age = $familyDb->partner1_age;
    $partner2_age = $familyDb->partner2_age;
    $fam_marr_church_notice_date = $familyDb->fam_marr_church_notice_date;
    $fam_marr_church_notice_place = '';
    if (isset($familyDb->fam_marr_church_notice_place)) {
        $fam_marr_church_notice_place = $familyDb->fam_marr_church_notice_place;
    }
    $fam_marr_church_notice_text = $editor_cls->text_show($familyDb->fam_marr_church_notice_text);
    $fam_marr_church_date = $familyDb->fam_marr_church_date;
    $fam_marr_church_place = '';
    if (isset($familyDb->fam_marr_church_place)) {
        $fam_marr_church_place = $familyDb->fam_marr_church_place;
    }
    $fam_marr_church_text = $editor_cls->text_show($familyDb->fam_marr_church_text);
    $fam_religion = '';
    if (isset($familyDb->fam_religion)) {
        $fam_religion = $familyDb->fam_religion;
    }
    $fam_div_date = $familyDb->fam_div_date;
    $fam_div_place = '';
    if (isset($familyDb->fam_div_place)) {
        $fam_div_place = $familyDb->fam_div_place;
    }
    $fam_div_text = $editor_cls->text_show($familyDb->fam_div_text);
    $fam_div_authority = $editor_cls->text_show($familyDb->fam_div_authority);

    $fam_marr_notice_date_hebnight = '';
    $fam_marr_date_hebnight = '';
    $fam_marr_church_notice_date_hebnight = '';
    $fam_marr_church_date_hebnight = '';
    if ($humo_option['admin_hebnight'] == "y") {
        if (isset($familyDb->fam_marr_notice_date_hebnight)) {
            $fam_marr_notice_date_hebnight = $familyDb->fam_marr_notice_date_hebnight;
        }
        if (isset($familyDb->fam_marr_date_hebnight)) {
            $fam_marr_date_hebnight = $familyDb->fam_marr_date_hebnight;
        }
        if (isset($familyDb->fam_marr_church_notice_date_hebnight)) {
            $fam_marr_church_notice_date_hebnight = $familyDb->fam_marr_church_notice_date_hebnight;
        }
        if (isset($familyDb->fam_marr_church_date_hebnight)) {
            $fam_marr_church_date_hebnight = $familyDb->fam_marr_church_date_hebnight;
        }
    }

    // *** Checkbox for no data by divorce ***
    $fam_div_no_data = false;
    if ($fam_div_date || $fam_div_place || $fam_div_text) {
        $fam_div_no_data = true;
    }
    $fam_text = $editor_cls->text_show($familyDb->fam_text);

    $person1 = $db_functions->get_person($man_gedcomnumber); // TODO: there allready is $person for person data.
    $person2 = $db_functions->get_person($woman_gedcomnumber);

    $check_family_eventsDb = $familyDb;
}

$hideshow = '700';
// *** Set pers_sexe for new partner ***
if ($person->pers_sexe == 'M') {
    $new_partner_sexe = 'F';
} else {
    $new_partner_sexe = 'M';
}
?>

<div class="p-1 m-2 genealogy_search">
    <?php
    if ($relations) {
        // *** Search for own family ***
        $id = [];
        $relation_gedcomnumber = [];
        $relation_id = [];
        foreach ($relations as $rel) {
            $id[] = $rel->id;
            $relation_gedcomnumber[] = $rel->relation_gedcomnumber;
            $relation_id[] = $rel->relation_id;
        }
        $fam_count = count($relation_gedcomnumber);
        if ($fam_count > 0) {
    ?>
            <ul id="sortable<?= (int) $i; ?>" class="sortable-relations sortable-pages list-group ui-sortable" data-family-id="<?= $HtmlEscaper->escHtml((string) ($familyDb->fam_id ?? '')); ?>">

                <?php
                for ($i = 0; $i < $fam_count; $i++) {
                    $qry = "SELECT f.*,
                        e.event_date AS fam_marr_date,
                        man_rel.person_id AS partner1_id,
                        man_rel.person_gedcomnumber AS partner1_gedcomnumber,
                        woman_rel.person_id AS partner2_id,
                        woman_rel.person_gedcomnumber AS partner2_gedcomnumber
                        FROM humo_families f
                        LEFT JOIN humo_events e ON f.fam_id = e.relation_id AND e.event_kind = 'marriage'
                        LEFT JOIN humo_relations_persons man_rel ON man_rel.relation_id = f.fam_id AND man_rel.relation_type = 'partner' AND man_rel.partner_order = 1
                        LEFT JOIN humo_relations_persons woman_rel ON woman_rel.relation_id = f.fam_id AND woman_rel.relation_type = 'partner' AND woman_rel.partner_order = 2
                        WHERE f.fam_id='" . $relation_id[$i] . "'";
                    $relation = $dbh->query($qry);
                    $relationDb = $relation->fetch(PDO::FETCH_OBJ);

                    // *** Highlight selected relation if there are multiple relations ***
                    $line_selected = '';
                    $button_selected = 'btn-secondary';
                    if ($fam_count > 1 and $relationDb->fam_gedcomnumber == $marriage) {
                        $line_selected = 'list-group-item-secondary';
                        $button_selected = 'btn-primary';
                    }
                ?>

                    <li class="list-group-item <?= $HtmlEscaper->escHtml($line_selected); ?>">
                        <div class="row mb-2">
                            <div class="col-1">
                                <span style="cursor:move;" id="<?= $HtmlEscaper->escHtml((string) $id[$i]); ?>" class="relation-handle" data-child-index="<?= (int) $i; ?>">
                                    <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                                </span>

                                <span id="relationnum<?= $HtmlEscaper->escHtml((string) $id[$i]); ?>">
                                    <?= (int) ($i + 1); ?>
                                </span>
                            </div>

                            <div class="col-2">
                                <?php if ($fam_count > 1) { ?>
                                    <form method="POST" action="index.php?page=editor&amp;menu_tab=marriage">
                                        <input type="hidden" name="marriage_nr" value="<?= $HtmlEscaper->escHtml((string) $relationDb->fam_gedcomnumber); ?>">
                                        <input type="submit" name="dummy3" value="<?= $HtmlEscaper->escHtml(__('Family')); ?>" class="btn btn-sm <?= $HtmlEscaper->escHtml($button_selected); ?>">
                                    </form>
                                <?php } else { ?>
                                    <?= __('Family'); ?>
                                <?php } ?>
                            </div>

                            <div class="col-9">
                                <b><?= show_person_with_id($relationDb->partner1_id) . ' ' . __('and') . ' ' . show_person_with_id($relationDb->partner2_id); ?></b>
                                <?php
                                if ($relationDb->fam_marr_date) {
                                    echo ' X ' . $HtmlEscaper->escHtml((string) $datePlace->date_place($relationDb->fam_marr_date, ''));
                                }
                                ?>
                            </div>
                        </div>
                    </li>

                <?php } ?>
            </ul>
    <?php
        }

        // *** Automatically calculate birth date if marriage date and marriage age by man is used ***
        if (
            isset($_POST["partner1_age"]) && $_POST["partner1_age"] != '' && $fam_marr_date != '' && $person1->pers_birth_date == '' && $person1->pers_bapt_date == ''
        ) {
            $pers_birth_date = 'ABT ' . (substr($fam_marr_date, -4) - $_POST["partner1_age"]);

            // *** Check if there is a birth event ***
            $birth_event = $dbh->prepare("SELECT * FROM humo_events WHERE person_id = :person_id AND event_kind = 'birth'");
            $birth_event->execute([
                ':person_id' => $person1->pers_id
            ]);
            $birth_eventDb = $birth_event->fetch(PDO::FETCH_OBJ);

            $data = [
                'tree_id' => $tree_id,
                'person_id' => $person1->pers_id,
                'event_connect_kind' => 'person',
                'event_connect_id' => $man_gedcomnumber,
                'event_kind' => 'birth',
                'event_event' => '',
                'event_gedcom' => '',
                'event_date' => $pers_birth_date
            ];
            if ($birth_eventDb && isset($birth_eventDb->event_id)) {
                $data['event_id'] = $birth_eventDb->event_id;
            }
            $eventManager->update_event($data);
        }

        // *** Automatically calculate birth date if marriage date and marriage age by woman is used ***
        if (
            isset($_POST["partner2_age"]) && $_POST["partner2_age"] != '' && $fam_marr_date != '' && $person2->pers_birth_date == '' && $person2->pers_bapt_date == ''
        ) {
            $pers_birth_date = 'ABT ' . (substr($fam_marr_date, -4) - $_POST["partner2_age"]);

            // *** Check if there is a birth event ***
            $birth_event = $dbh->prepare("SELECT * FROM humo_events WHERE person_id = :person_id AND event_kind = 'birth'");
            $birth_event->execute([
                ':person_id' => $person2->pers_id
            ]);
            $birth_eventDb = $birth_event->fetch(PDO::FETCH_OBJ);

            $data = [
                'tree_id' => $tree_id,
                'person_id' => $person2->pers_id,
                'event_connect_kind' => 'person',
                'event_connect_id' => $woman_gedcomnumber,
                'event_kind' => 'birth',
                'event_event' => '',
                'event_gedcom' => '',
                'event_date' => $pers_birth_date
            ];
            if ($birth_eventDb && isset($birth_eventDb->event_id)) {
                $data['event_id'] = $birth_eventDb->event_id;
            }
            $eventManager->update_event($data);
        }
    } ?>
</div>

<!-- Add new relation -->
<div class="p-1 m-2 genealogy_search">
    <div class="row mb-2">
        <div class="col-md-3"><b><?= __('Add relation'); ?></b></div>
        <div class="col-md-9">
            <a href="#" onclick="hideShow(<?= (int) $hideshow; ?>);"><img src="images/family_connect.gif" alt="<?= $HtmlEscaper->escHtml(__('Add relation')); ?>" title="<?= $HtmlEscaper->escHtml(__('Add relation')); ?>"> <?= __('Add new relation to this person'); ?></a>
            (<?= trim(show_person($person->pers_gedcomnumber, false, false)); ?>)
        </div>
    </div>
</div>

<div style="display:none;" class="row<?= (int) $hideshow; ?> p-3 m-2 genealogy_search">
    <?= add_person('partner', $new_partner_sexe); ?><br><br>
    <form method="POST" style="display: inline;" action="index.php?page=editor&amp;menu_tab=marriage#marriage" name="form4" id="form4">
        <div class="row mb-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <?= __('Or add relation with existing person:'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" name="relation_add2" value="" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>" required class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_person_select&person=0&person_item=relation_add2&tree_id=<?= rawurlencode((string) $tree_id); ?>","",<?= json_encode((string) $field_popup, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><img src=" ../images/search.png" alt="<?= $HtmlEscaper->escHtml(__('Search')); ?>"></a>
                </div>
            </div>
            <div class="col-md-1">
                <input type="submit" name="dummy4" value="<?= __('Add relation'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>
    </form>
</div>

<!-- Marriage editor -->
<?php if ($relations) { ?>
    <?php /*
    Don't use this link, witness buttons won't work anymore
    <form method="POST" action="index.php?page=editor&amp;menu_tab=marriage" style="display : inline;" enctype="multipart/form-data" name="form2" id="form2">
    */ ?>

    <form method="POST" action="index.php" style="display : inline;" enctype="multipart/form-data" name="form2" id="form2">
        <input type="hidden" name="page" value="editor">

        <input type="hidden" name="connect_man_old" value="<?= $HtmlEscaper->escHtml((string) $man_gedcomnumber); ?>">
        <input type="hidden" name="connect_woman_old" value="<?= $HtmlEscaper->escHtml((string) $woman_gedcomnumber); ?>">

        <?php if (isset($marriage)) { ?>
            <input type="hidden" name="marriage_nr" value="<?= $HtmlEscaper->escHtml((string) $marriage); ?>">

            <!-- $marriage is empty by single persons -->
            <input type="hidden" name="marriage" value="<?= $HtmlEscaper->escHtml((string) $marriage); ?>">
        <?php } ?>

        <!-- Event IDs needed to check if event is changed -->
        <?php if (isset($check_family_eventsDb->fam_relation_event_id)) { ?>
            <input type="hidden" name="fam_relation_event_id" value="<?= $HtmlEscaper->escHtml((string) $check_family_eventsDb->fam_relation_event_id); ?>">

            <input type="hidden" name="fam_relation_date_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_relation_date); ?>">
            <input type="hidden" name="fam_relation_place_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_relation_place); ?>">
            <input type="hidden" name="fam_relation_text_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_relation_text); ?>">
            <input type="hidden" name="fam_relation_end_date_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_relation_end_date); ?>">
        <?php } ?>

        <?php if (isset($check_family_eventsDb->fam_marr_notice_event_id)) { ?>
            <input type="hidden" name="fam_marr_notice_event_id" value="<?= $HtmlEscaper->escHtml((string) $check_family_eventsDb->fam_marr_notice_event_id); ?>">

            <input type="hidden" name="fam_marr_notice_date_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_notice_date); ?>">
            <input type="hidden" name="fam_marr_notice_date_hebnight_previous" value="<?= $fam_marr_notice_date_hebnight == 'y' ? 'y' : 'n'; ?>">
            <input type="hidden" name="fam_marr_notice_place_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_notice_place); ?>">
            <input type="hidden" name="fam_marr_notice_text_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_notice_text); ?>">
        <?php } ?>

        <?php if (isset($check_family_eventsDb->fam_marr_event_id)) { ?>
            <input type="hidden" name="fam_marr_event_id" value="<?= $HtmlEscaper->escHtml((string) $check_family_eventsDb->fam_marr_event_id); ?>">

            <input type="hidden" name="fam_marr_date_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_date); ?>">
            <input type="hidden" name="fam_marr_date_hebnight_previous" value="<?= $fam_marr_date_hebnight == 'y' ? 'y' : 'n'; ?>">
            <input type="hidden" name="fam_marr_place_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_place); ?>">
            <input type="hidden" name="fam_marr_text_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_text); ?>">
        <?php } ?>

        <?php if (isset($check_family_eventsDb->fam_marr_church_notice_event_id)) { ?>
            <input type="hidden" name="fam_marr_church_notice_event_id" value="<?= $HtmlEscaper->escHtml((string) $check_family_eventsDb->fam_marr_church_notice_event_id); ?>">

            <input type="hidden" name="fam_marr_church_notice_date_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_church_notice_date); ?>">
            <input type="hidden" name="fam_marr_church_notice_date_hebnight_previous" value="<?= $fam_marr_church_notice_date_hebnight == 'y' ? 'y' : 'n'; ?>">
            <input type="hidden" name="fam_marr_church_notice_place_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_church_notice_place); ?>">
            <input type="hidden" name="fam_marr_church_notice_text_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_church_notice_text); ?>">
        <?php } ?>

        <?php if (isset($check_family_eventsDb->fam_marr_church_event_id)) { ?>
            <input type="hidden" name="fam_marr_church_event_id" value="<?= $HtmlEscaper->escHtml((string) $check_family_eventsDb->fam_marr_church_event_id); ?>">

            <input type="hidden" name="fam_marr_church_date_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_church_date); ?>">
            <input type="hidden" name="fam_marr_church_date_hebnight_previous" value="<?= $fam_marr_church_date_hebnight == 'y' ? 'y' : 'n'; ?>">
            <input type="hidden" name="fam_marr_church_place_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_church_place); ?>">
            <input type="hidden" name="fam_marr_church_text_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_church_text); ?>">
        <?php } ?>
        <?php if (isset($check_family_eventsDb->fam_div_event_id)) { ?>
            <input type="hidden" name="fam_div_event_id" value="<?= $HtmlEscaper->escHtml((string) $check_family_eventsDb->fam_div_event_id); ?>">

            <input type="hidden" name="fam_div_date_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_div_date); ?>">
            <input type="hidden" name="fam_div_date_hebnight_previous" value="<?= $fam_div_date_hebnight == 'y' ? 'y' : 'n'; ?>">
            <input type="hidden" name="fam_div_place_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_div_place); ?>">
            <input type="hidden" name="fam_div_text_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_div_text); ?>">
            <input type="hidden" name="fam_div_authority_previous" value="<?= $HtmlEscaper->escHtml((string) $fam_div_authority); ?>">
        <?php } ?>

        <?php
        if (isset($_GET['fam_remove']) || isset($_POST['fam_remove'])) {
            if (isset($_GET['fam_remove']) && $validateGedcomnumber->validate($_GET['fam_remove'])) {
                $fam_remove = $_GET['fam_remove'];
            };
            if (isset($_POST['marriage_nr']) && $validateGedcomnumber->validate($_POST['marriage_nr'])) {
                $fam_remove = $_POST['marriage_nr'];
            };

            $new_nr = $db_functions->get_family($fam_remove);
            $children = $db_functions->get_children($new_nr->fam_id);
        ?>
            <div class="alert alert-danger">
                <?php if ($children) { ?>
                    <strong><?= __('If you continue, ALL children will be disconnected automatically!'); ?></strong><br>
                <?php } ?>
                <?= __('Are you sure to remove this mariage?'); ?>
                <input type="hidden" name="fam_remove3" value="<?= $HtmlEscaper->escHtml((string) $fam_remove); ?>">
                <input type="submit" name="fam_remove2" value="<?= __('Yes'); ?>" class="btn btn-sm btn-danger">
                <input type="submit" name="submit" value="<?= __('No'); ?>" class="btn btn-sm btn-success ms-3">
            </div>
        <?php } ?>

        <table class="table table-light">
            <!-- Empty line in table -->
            <!-- <tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr> -->

            <thead class="table-primary">
                <tr>
                    <!-- Hide or show all hide-show items -->
                    <td id="target1">
                        <a href="#marriage" onclick="hideShowAll2();"><span id="hideshowlinkall2">[+]</span> <?= __('All'); ?></a>
                        <a name="marriage"></a>
                    </td>

                    <th id="target2" colspan="2" style="font-size: 1.5em;">
                        <input type="submit" name="marriage_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">

                        <!-- Popover to show user information -->
                        <?php
                        // *** Person added by user ***
                        $content = __('Added by') . ' ';
                        if ($familyDb->fam_new_user_id || $familyDb->fam_new_datetime) {
                            $content .= $HtmlEscaper->escHtml((string) $db_functions->get_user_name($familyDb->fam_new_user_id)) . ' ' . $HtmlEscaper->escHtml((string) $languageDate->show_datetime($familyDb->fam_new_datetime));
                        }
                        // *** Person changed by user ***
                        if ($familyDb->fam_changed_user_id || $familyDb->fam_changed_datetime) {
                            $content .=  '<br>' . __('Changed by') . ' ';
                            $content .= $HtmlEscaper->escHtml((string) $db_functions->get_user_name($familyDb->fam_changed_user_id)) . ' ' . $HtmlEscaper->escHtml((string) $languageDate->show_datetime($familyDb->fam_changed_datetime));
                        }
                        ?>
                        <button type="button" class="btn btn-sm btn-info"
                            data-bs-toggle="popover" data-bs-placement="right" data-bs-custom-class="popover-wide" data-bs-html="true"
                            data-bs-content="<?= $HtmlEscaper->escHtml($content); ?>">
                            <?= __('Info'); ?>
                        </button>


                        [<?= $HtmlEscaper->escHtml((string) $fam_gedcomnumber); ?>] <?= show_person($man_gedcomnumber); ?> <?= __('and'); ?> <?= show_person($woman_gedcomnumber); ?>
                    </th>
                </tr>
            </thead>

            <tr>
                <td><?= ucfirst(__('marriage/ relation')); ?></td>
                <td colspan="2">
                    <?php if ($person1->pers_sexe == 'F' && $person2->pers_sexe == 'M') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?= __('Person 1 should be the man. Switch person 1 and person 2.'); ?>
                            <button type="submit" name="parents_switch" title="Switch Persons" class="button"><img src="images/turn_around.gif" width="17" alt="<?= __('Switch Persons'); ?>"></button>
                        </div>
                    <?php } ?>

                    <div class="row mb-2">
                        <div class="col-md-auto">
                            <?= __('Select person 1'); ?>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="connect_man" value="<?= $HtmlEscaper->escHtml((string) $man_gedcomnumber); ?>" size="5" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-auto">
                            <a href="#" onClick='window.open("index.php?page=editor_person_select&person_item=man&person=<?= rawurlencode((string) $man_gedcomnumber); ?>&tree_id=<?= rawurlencode((string) $tree_id); ?>","","width=500,height=500,top=100,left=100,scrollbars=yes")'>
                                <img src="../images/search.png" alt="<?= __('Search'); ?>">
                            </a>
                        </div>
                        <div class="col-md-auto">
                            <b><?= $HtmlEscaper->escHtmlDecoded((string) $editor_cls->show_selected_person($person1)); ?></b>
                        </div>
                    </div>

                    <?= __('and'); ?>

                    <div class="row mt-3">
                        <div class="col-md-auto">
                            <?= __('Select person 2'); ?>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="connect_woman" value="<?= $HtmlEscaper->escHtml((string) $woman_gedcomnumber); ?>" size="5" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-auto">
                            <a href="#" onClick='window.open("index.php?page=editor_person_select&person_item=woman&person=<?= rawurlencode((string) $woman_gedcomnumber); ?>&tree_id=<?= rawurlencode((string) $tree_id); ?>","","width=500,height=500,top=100,left=100,scrollbars=yes")'>
                                <img src="../images/search.png" alt="<?= __('Search'); ?>">
                            </a>
                        </div>
                        <div class="col-md-auto">
                            <b><?= $HtmlEscaper->escHtmlDecoded((string) $editor_cls->show_selected_person($person2)); ?></b>
                        </div>
                    </div>
                </td>
            </tr>

            <?php
            // *** Living together ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '6';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>

            <tr>
                <td><a name="relation"></a>
                    <!-- <a href="#marriage" onclick="hideShow(6);"><span id="hideshowlink6">[+]</span></a> -->
                    <?= __('Living together'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_relation_date, $HtmlEscaper->escHtml((string) $fam_relation_place));
                    if ($fam_relation_end_date) {
                        if ($hideshow_text) {
                            $hideshow_text .= '.';
                        }
                        $hideshow_text .= ' ' . __('End living together') . ' ' . $fam_relation_end_date;
                    }
                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_relation_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }
                    echo hideshow_editor($hideshow, $hideshow_text, $fam_relation_text);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                        <div class="row mb-2">
                            <label for="fam_relation_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_relation_date, 'fam_relation_date'); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_relation_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_relation_place" id="fam_relation_place" value="<?= $HtmlEscaper->escHtml((string) $fam_relation_place); ?>" placeholder="<?= $HtmlEscaper->escHtml(__('Start typing to search for a place.')); ?>" size="<?= (int) $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <!-- End of living together -->
                        <div class="row mb-2">
                            <label for="fam_relation_end_date" class="col-md-3 col-form-label"><?= __('End date'); ?></label>
                            <div class="col-md-7">
                                <?= $editor_cls->date_show($fam_relation_end_date, "fam_relation_end_date"); ?>
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_relation_text && preg_match('/\R/', $fam_relation_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_relation_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_relation_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $fam_relation_text); ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_relation_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_relation_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </span>
                </td>
            </tr>

            <?php
            // *** Marriage notice ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '7';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>
            <tr>
                <td><a name="marr_notice"></a>
                    <!-- <a href="#marriage" onclick="hideShow(7);"><span id="hideshowlink7">[+]</span></a> -->
                    <?= __('Notice of Marriage'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_marr_notice_date, $HtmlEscaper->escHtml((string) $fam_marr_notice_place));
                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_marr_notice_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }
                    echo hideshow_editor($hideshow, $hideshow_text, $fam_marr_notice_text);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                        <div class="row mb-2">
                            <label for="fam_marr_notice_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_marr_notice_date, "fam_marr_notice_date", "", $fam_marr_notice_date_hebnight, "fam_marr_notice_date_hebnight"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_notice_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_marr_notice_place" id="fam_marr_notice_place" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_notice_place); ?>" placeholder="<?= $HtmlEscaper->escHtml(__('Start typing to search for a place.')); ?>" size="<?= (int) $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_notice_text && preg_match('/\R/', $fam_marr_notice_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_notice_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_marr_notice_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $fam_marr_notice_text); ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_marr_notice_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_marr_notice_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </span>
                </td>
            </tr>

            <?php
            // *** Marriage ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '8';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>

            <tr>
                <td><a name="marriage_relation"></a>
                    <!-- <a href="#marriage" onclick="hideShow(8);"><span id="hideshowlink8">[+]</span></a> -->
                    <!-- <?= __('Marriage'); ?></td> -->
                    <?= ucfirst(__('marriage/ relation')); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = '';
                    if (!$fam_kind) {
                        $hideshow_text .= '<span style="background-color:#FFAA80">' . __('Marriage/ Related') . '</span>';
                    }

                    $dateplace = $datePlace->date_place($fam_marr_date, $HtmlEscaper->escHtml((string) $fam_marr_place));
                    if ($dateplace) {
                        if ($hideshow_text) {
                            $hideshow_text .= ', ';
                        }
                        $hideshow_text .= $dateplace;
                    }

                    if ($fam_marr_authority) {
                        //if ($hideshow_text){
                        //  $hideshow_text.='.';
                        //}
                        $hideshow_text .= ' [' . $HtmlEscaper->escHtml((string) $fam_marr_authority) . ']';
                    }

                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_marr_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }
                    ?>
                    <?= hideshow_editor($hideshow, $hideshow_text, $fam_marr_text); ?>

                    <input type="submit" name="add_marriage_witness" value="<?= __('witness') . ' - ' . __('officiator'); ?>" class="btn btn-sm btn-outline-primary ms-4">

                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="fam_marr_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_marr_date, "fam_marr_date", "", $fam_marr_date_hebnight, "fam_marr_date_hebnight"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_marr_place" id="fam_marr_place" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_place); ?>" placeholder="<?= $HtmlEscaper->escHtml(__('Start typing to search for a place.')); ?>" size="<?= (int) $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Age of man by marriage -->
                        <div class="row mb-2">
                            <label for="partner1_age" class="col-md-3 col-form-label"><?= __('Age person 1'); ?></label>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="text" name="partner1_age" value="<?= $HtmlEscaper->escHtml((string) $partner1_age); ?>" size="3" class="form-control form-control-sm">

                                    <!-- Help popover -->
                                    <button type="button" class="btn btn-sm btn-secondary"
                                        data-bs-toggle="popover" data-bs-placement="right" data-bs-custom-class="popover-wide"
                                        data-bs-content="<?= __('If birth year of man or woman is empty it will be calculated automatically using age by marriage.'); ?>">
                                        ?
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Age of woman by marriage -->
                        <div class="row mb-2">
                            <label for="partner2_age" class="col-md-3 col-form-label"><?= __('Age person 2'); ?></label>
                            <div class="col-md-3">
                                <input type="text" name="partner2_age" value="<?= $HtmlEscaper->escHtml((string) $partner2_age); ?>" size="3" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_kind" class="col-md-3 col-form-label">
                                <?php if (!$fam_kind) { ?>
                                    <span style="background-color:#FFAA80"><?= __('Marriage/ Related'); ?></span>
                                <?php } else { ?>
                                    <?= __('Marriage/ Related'); ?>
                                <?php } ?>
                            </label>
                            <div class="col-md-3">
                                <select size="1" id="fam_kind" name="fam_kind" class="form-select form-select-sm">
                                    <option value=""><?= __('Marriage/ Related'); ?></option>
                                    <option value="civil" <?= $fam_kind == 'civil' ? ' selected' : ''; ?>><?= __('Married'); ?></option>
                                    <option value="living together" <?= $fam_kind == 'living together' ? ' selected' : ''; ?>><?= __('Living together'); ?></option>
                                    <option value="living apart together" <?= $fam_kind == 'living apart together' ? ' selected' : ''; ?>><?= __('Living apart together'); ?></option>
                                    <option value="intentionally unmarried mother" <?= $fam_kind == 'intentionally unmarried mother' ? ' selected' : ''; ?>><?= __('Intentionally unmarried mother'); ?></option>
                                    <option value="homosexual" <?= $fam_kind == 'homosexual' ? ' selected' : ''; ?>><?= __('Homosexual'); ?></option>
                                    <option value="non-marital" <?= $fam_kind == 'non-marital' ? ' selected' : ''; ?>><?= __('Non_marital'); ?></option>
                                    <option value="extramarital" <?= $fam_kind == 'extramarital' ? ' selected' : ''; ?>><?= __('Extramarital'); ?></option>
                                    <option value="partners" <?= $fam_kind == 'partners' ? ' selected' : ''; ?>><?= __('Partner'); ?></option>
                                    <option value="registered" <?= $fam_kind == 'registered' ? ' selected' : ''; ?>><?= __('Registered partnership'); ?></option>
                                    <option value="unknown" <?= $fam_kind == 'unknown' ? ' selected' : ''; ?>><?= __('Unknown relation'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_authority" class="col-md-3 col-form-label"><?= __('Registrar'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="fam_marr_authority" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_authority); ?>" size="60" class="form-control form-control-sm">
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_text && preg_match('/\R/', $fam_marr_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_marr_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $fam_marr_text); ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_marr_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_marr_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </span>
                </td>

            </tr>

            <?php
            // *** Marriage Witness ***
            echo $EditorEvent->show_event('MARR', $marriage, 'ASSO');

            // *** Religious marriage notice ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '9';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>

            <tr>
                <td><a name="marr_church_notice"></a>
                    <!-- <a href="#marriage" onclick="hideShow(9);"><span id="hideshowlink9">[+]</span></a> -->
                    <?= __('Religious Notice of Marriage'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_marr_church_notice_date, $HtmlEscaper->escHtml((string) $fam_marr_church_notice_place));

                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_marr_church_notice_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }

                    echo hideshow_editor($hideshow, $hideshow_text, $fam_marr_church_notice_text);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="fam_marr_church_notice_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_marr_church_notice_date, "fam_marr_church_notice_date", "", $fam_marr_church_notice_date_hebnight, "fam_marr_church_notice_date_hebnight"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_church_notice_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_marr_church_notice_place" id="fam_marr_church_notice_place" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_church_notice_place); ?>" placeholder="<?= $HtmlEscaper->escHtml(__('Start typing to search for a place.')); ?>" size="<?= (int) $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_church_notice_text && preg_match('/\R/', $fam_marr_church_notice_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_church_notice_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_marr_church_notice_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $fam_marr_church_notice_text); ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_marr_church_notice_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_marr_church_notice_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </span>
                </td>

            </tr>

            <?php
            // *** Church marriage ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '10';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>

            <tr>
                <td><a name="marr_church"></a>
                    <?= __('Religious Marriage'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_marr_church_date, $HtmlEscaper->escHtml((string) $fam_marr_church_place));

                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_marr_church_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }
                    ?>
                    <?= hideshow_editor($hideshow, $hideshow_text, $fam_marr_church_text); ?>

                    <input type="submit" name="add_marriage_witness_rel" value="<?= __('witness') . ' - ' . __('clergy'); ?>" class="btn btn-sm btn-outline-primary ms-4">

                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="fam_marr_church_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_marr_church_date, "fam_marr_church_date", "", $fam_marr_church_date_hebnight, "fam_marr_church_date_hebnight"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_church_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_marr_church_place" id="fam_marr_church_place" value="<?= $HtmlEscaper->escHtml((string) $fam_marr_church_place); ?>" placeholder="<?= $HtmlEscaper->escHtml(__('Start typing to search for a place.')); ?>" size="<?= (int) $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_church_text && preg_match('/\R/', $fam_marr_church_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_church_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_marr_church_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $fam_marr_church_text); ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_marr_church_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_marr_church_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </span>
                </td>

            </tr>

            <?php
            // *** Marriage Witness (church) ***
            echo $EditorEvent->show_event('MARR_REL', $marriage, 'ASSO');
            ?>

            <!-- Religion -->
            <tr>
                <td rowspan="1"><?= __('Religion'); ?></td>
                <td colspan="2">
                    <div class="row mb-2">
                        <!-- <label for="fam_marr_authority" class="col-md-3 col-form-label"><?= __('Religion'); ?></label> -->
                        <div class="col-md-7">
                            <input type="text" name="fam_religion" value="<?= $HtmlEscaper->escHtml((string) $fam_religion); ?>" size="60" class="form-control form-control-sm">
                        </div>
                    </div>
                </td>
            </tr>

            <?php
            // *** Divorce ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '11';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>
            <tr>
                <td>
                    <a name="divorce"></a>
                    <!-- <a href="#marriage" onclick="hideShow(11);"><span id="hideshowlink11">[+]</span></a> -->
                    <?= __('Divorce'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_div_date, $HtmlEscaper->escHtml((string) $fam_div_place));

                    if ($fam_div_authority) {
                        //if ($hideshow_text) $hideshow_text.='.';
                        $hideshow_text .= ' [' . $HtmlEscaper->escHtml((string) $fam_div_authority) . ']';
                    }

                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_div_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }

                    echo hideshow_editor($hideshow, $hideshow_text, $fam_div_text);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="fam_div_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_div_date, "fam_div_date"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_div_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_div_place" id="fam_div_place" value="<?= $HtmlEscaper->escHtml((string) $fam_div_place); ?>" placeholder="<?= $HtmlEscaper->escHtml(__('Start typing to search for a place.')); ?>" size="<?= (int) $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <?php
                        $text = '';
                        if ($fam_div_authority) {
                            $text = $HtmlEscaper->escHtml((string) $fam_div_authority);
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_church_text" class="col-md-3 col-form-label"><?= __('Registrar'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="fam_div_authority" value="<?= $text; ?>" size="60" class="form-control form-control-sm">
                            </div>
                        </div>

                        <?php
                        if ($fam_div_text == 'DIVORCE') {
                            // *** Hide this text, it's a hidden value for a divorce without data ***
                            $fam_div_text = '';
                        }
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_div_text && preg_match('/\R/', $fam_div_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_div_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_div_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $fam_div_text); ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_div_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_div_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </span>
                </td>

            </tr>

            <?php
            // TODO: move to divorse lines?
            // *** Use checkbox for divorse without further data ***
            ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <input type="checkbox" name="fam_div_no_data" value="no_data" class="form-check-input" <?= $fam_div_no_data ? ' checked' : ''; ?>>
                    <?= __('Divorce (use this checkbox for a divorce without further data).'); ?>
                </td>
            </tr>

            <!-- General text by relation -->
            <tr>
                <td><a name="fam_text"></a><?= __('Text by relation'); ?></td>
                <td style="border-left:0px;">
                    <div class="row mb-2">
                        <!-- <label for="fam_relation_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label> -->
                        <div class="col-md-12">
                            <textarea rows="1" name="fam_text" <?= $field_text_large; ?> class="form-control form-control-sm"><?= $HtmlEscaper->escHtml((string) $fam_text); ?></textarea>
                        </div>
                    </div>

                    <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                        <div class="row mb-2">
                            <!-- <label for="fam_text_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label> -->
                            <div class="col-md-7">
                                <?php
                                source_link3('family', 'fam_text_source', $marriage);

                                if ($marriage) {
                                    $check_sources_text = check_sources('family', 'fam_text_source', $marriage);
                                    echo $check_sources_text;
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                </td>
            </tr>

            <!-- Relation sources -->
            <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                <tr>
                    <td><a name="fam_source"></a><?= __('Source by relation'); ?></td>
                    <td colspan="2">
                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <!-- <label for="family_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label> -->
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'family_source', $marriage);

                                    if ($marriage) {
                                        $check_sources_text = check_sources('family', 'family_source', $marriage);
                                        echo $check_sources_text;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
            <?php
            }

            // *** Picture ***
            echo $EditorEvent->show_event('family', $marriage, 'marriage_picture');

            // *** Family event editor ***
            ?>
            <tr id="event_family_link">
                <td><?= __('Events'); ?></td>
                <td colspan="2">
                    <div class="row">
                        <!-- Add relation event -->
                        <div class="col-4">
                            <select size="1" name="event_kind" aria-label="<?= __('Events'); ?>" class="form-select form-select-sm">
                                <option value="event"><?= __('Event'); ?></option>
                                <option value="URL"><?= __('URL/ Internet link'); ?></option>
                            </select>
                        </div>

                        <div class="col-3">
                            <input type="submit" name="marriage_event_add" value="<?= __('Add event'); ?>" class="btn btn-sm btn-outline-primary">

                            <!-- Help popover for events -->
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-toggle="popover" data-bs-placement="right" data-bs-custom-class="popover-wide"
                                data-bs-content="<?= __('For items like:') . ' ' . __('Event') . ', ' . __('Marriage contract') . ', ' . __('Marriage license') . ', ' . __('etc.'); ?>">
                                ?
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            <?php
            echo $EditorEvent->show_event('family', $marriage, 'family');

            // *** Show and edit addresses by family ***
            $connect_kind = 'family';
            $connect_sub_kind = 'family_address';
            $connect_connect_id = $marriage;
            include_once __DIR__ . '/partial/editor_addresses.php';

            // *** Show unprocessed GEDCOM tags ***
            $tag_qry = "SELECT * FROM humo_unprocessed_tags WHERE tag_tree_id='" . $tree_id . "' AND tag_rel_id='" . $familyDb->fam_id . "'";
            $tag_result = $dbh->query($tag_qry);
            //$num_rows = $tag_result->rowCount();
            $tagDb = $tag_result->fetch(PDO::FETCH_OBJ);
            if (isset($tagDb->tag_tag)) {
                $tags_array = explode('<br>', $tagDb->tag_tag);
                $num_rows = count($tags_array);
            ?>
                <tr class="humo_tags_fam">
                    <td>
                        <a href="#humo_tags_fam" onclick="hideShow(110);"><span id="hideshowlink110">[+]</span></a>
                        <?= __('GEDCOM tags'); ?>
                    </td>
                    <td colspan="2">
                        <?php
                        if ($tagDb->tag_tag) {
                            printf(__('There are %d unprocessed GEDCOM tags.'), $num_rows);
                        } else {
                            printf(__('There are %d unprocessed GEDCOM tags.'), 0);
                        }
                        ?>
                    </td>
                </tr>
                <tr style="display:none;" class="row110">
                    <td></td>
                    <td colspan="2"><?= nl2br($HtmlEscaper->escHtml(str_replace('<br>', "\n", (string) $tagDb->tag_tag))); ?></td>
                </tr>
            <?php
            }

            // *** Show editor notes ***
            $note_connect_kind = 'family';
            include_once __DIR__ . '/partial/editor_notes.php';
            ?>

            <!-- Extra "Save" line -->
            <tr>
                <td></td>
                <td colspan="2">
                    <input type="submit" name="marriage_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">

                    <?= __('or'); ?>

                    <!-- Remove marriage -->
                    <?php if (isset($marriage)) { ?>
                        <input type="submit" name="fam_remove" value="<?= __('Delete relation'); ?>" class="btn btn-sm btn-secondary">
                    <?php } ?>
                </td>
            </tr>
        </table><br>
    </form>

    <?php
    if ($marriage) {
        // *** Automatic order of children ***
        if (isset($_GET['order_children'])) {
            $order_changed = $orderChildren->order($dbh, $db_functions, $familyDb->fam_id);
        }

        // *** Show children ***
        $children = $db_functions->get_children($familyDb->fam_id);
        if (count($children) > 0) {
    ?>
            <a name="children"></a>

            <h3><?= __('Children'); ?></h3>

            <?= __('Use this icon to order children (drag and drop)'); ?>: <img src="images/drag-icon.gif" border="0" alt="<?= __('Drag to change order'); ?>" title="<?= __('Drag to change order'); ?>"><br>
            <?= __('Or automatically order children:'); ?> <a href="index.php?page=<?= rawurlencode((string) $page); ?>&amp;menu_tab=marriage&amp;marriage_nr=<?= rawurlencode((string) $marriage); ?>&amp;order_children=1#children">
                <?= __('Automatic order children'); ?>
            </a>

            <?php if (isset($_GET['order_children'])) { ?>
                <div class="alert <?= !empty($order_changed) ? 'alert-success' : 'alert-info'; ?>" role="alert">
                    <b>
                        <?= !empty($order_changed) ? __('Children are re-ordered.') : __('No changes in order of children.'); ?>
                    </b>
                </div>
            <?php } ?>

            <ul id="sortable<?= (int) $i; ?>" class="sortable-children sortable-pages list-group ui-sortable" data-family-id="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_id); ?>">
                <?php foreach ($children as $child) { ?>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1">
                                <span style="cursor:move;" id="<?= $HtmlEscaper->escHtml((string) $child->id); ?>" class="child-handle" data-child-index="<?= (int) $j; ?>">
                                    <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                                </span>
                            </div>

                            <div class="col-md-1">
                                <a href="index.php?page=<?= rawurlencode((string) $page); ?>&amp;family_id=<?= rawurlencode((string) $familyDb->fam_id); ?>&amp;child_disconnect_id=<?= rawurlencode((string) $child->id); ?>">
                                    <img src="images/person_disconnect.gif" border="0" title="<?= __('Disconnect child'); ?>" alt="<?= __('Disconnect child'); ?>">
                                </a>
                            </div>

                            <div class="col-md-10">
                                <span id="chldnum<?= $HtmlEscaper->escHtml((string) $child->id); ?>">
                                    <?= (int) $child->relation_order; ?>
                                </span>
                                <?= show_person_with_id($child->person_id, true); ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>

        <!-- Add child -->
        <?php $hideshow = 702; ?>
        <div id="add_child" class="p-1 m-2 genealogy_search">
            <div class="row mb-2">
                <div class="col-md-3"><b><?= __('Add child'); ?></b></div>
                <div class="col-md-7">
                    <a href="#add_child" onclick="hideShow(<?= (int) $hideshow; ?>);"><b><?= __('Add child'); ?></b></a>
                </div>
            </div>
        </div>

        <!-- <div class="p-3 m-2 genealogy_search"> -->
        <div style="display:none;" class="row<?= (int) $hideshow; ?> p-3 m-2 genealogy_search">
            <?= add_person('child', ''); ?><br>

            <!-- Search existing person as child -->
            <form method="POST" action="index.php?page=editor&amp;menu_tab=marriage" style="display : inline;" name="form7" id="form7">
                <!-- Event IDs needed to check if event is changed -->
                <?php if (isset($familyDb->fam_relation_event_id)) { ?>
                    <input type="hidden" name="fam_relation_event_id" value="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_relation_event_id); ?>">
                <?php } ?>
                <?php if (isset($familyDb->fam_marr_event_id)) { ?>
                    <input type="hidden" name="fam_marr_event_id" value="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_marr_event_id); ?>">
                <?php } ?>
                <?php if (isset($familyDb->fam_div_event_id)) { ?>
                    <input type="hidden" name="fam_div_event_id" value="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_div_event_id); ?>">
                <?php } ?>
                <?php if (isset($familyDb->fam_marr_church_event_id)) { ?>
                    <input type="hidden" name="fam_marr_church_event_id" value="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_marr_church_event_id); ?>">
                <?php } ?>
                <?php if (isset($familyDb->fam_marr_church_notice_event_id)) { ?>
                    <input type="hidden" name="fam_marr_church_notice_event_id" value="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_marr_church_notice_event_id); ?>">
                <?php } ?>
                <?php if (isset($familyDb->fam_marr_notice_event_id)) { ?>
                    <input type="hidden" name="fam_marr_notice_event_id" value="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_marr_notice_event_id); ?>">
                <?php } ?>

                <input type="hidden" name="family_id" value="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_gedcomnumber); ?>">

                <div class="row mb-2">
                    <div class="col-md-3"></div>
                    <div class="col-md-7">
                        <?= __('Or add existing person as a child:'); ?>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="child_connect2" value="" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>" required class="form-control form-control-sm">
                            <a href="#" onClick='window.open("index.php?page=editor_person_select&person=0&person_item=child_connect2&tree_id=<?= rawurlencode((string) $tree_id); ?>","",<?= json_encode((string) $field_popup, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><img src="../images/search.png" alt="<?= $HtmlEscaper->escHtml(__('Search')); ?>"></a>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <input type="submit" name="dummy4" value="<?= __('Select child'); ?>" class="btn btn-sm btn-success">
                    </div>
                </div>
            </form>
        </div><br><br>

        <!-- Order relations using drag and drop -->
        <script src="../assets/js/order_relations.js"></script>
        <!-- Order children using drag and drop (using jquery and jqueryui) -->
        <script src="../assets/js/order_children.js"></script>
    <?php
    }
}

// TODO: use separate view script.
// *** New function aug. 2021: Add partner or child ***
function add_person($person_kind, $pers_sexe)
{
    global $page, $editor_cls, $field_place, $field_date, $familyDb, $marriage, $db_functions, $field_popup;
    $HtmlEscaper = new \Genealogy\Include\HtmlEscaper();

    $pers_prefix = '';
    $pers_lastname = '';

    if ($person_kind == 'partner') {
        //$form = 5;
        $form_name = 'form5';
    } else {
        // *** Add child to family ***
        //$form = 6;
        $form_name = 'form6';

        // *** Get default prefix and lastname ***
        if ($familyDb->partner1_id) {
            $personDb = $db_functions->get_person_with_id($familyDb->partner1_id);
            $pers_prefix = $HtmlEscaper->escHtml((string) $personDb->pers_prefix);
            $pers_lastname = $HtmlEscaper->escHtml((string) $personDb->pers_lastname);
        }
    }
    ?>

    <form method="POST" style="display: inline;" action="index.php?page=editor#marriage" name="<?= $form_name; ?>" id="<?= $form_name; ?>">
        <?php if ($person_kind != 'partner') { ?>
            <input type="hidden" name="child_connect" value="1">
            <!-- TODO check code. Both variables show the same value -->
            <input type="hidden" name="family_id" value="<?= $HtmlEscaper->escHtml((string) $familyDb->fam_gedcomnumber); ?>">
            <input type="hidden" name="marriage_nr" value="<?= $HtmlEscaper->escHtml((string) $marriage); ?>">
        <?php } ?>
        <input type="hidden" name="pers_name_text" value="">
        <input type="hidden" name="pers_birth_text" value="">
        <input type="hidden" name="pers_bapt_text" value="">
        <input type="hidden" name="pers_religion" value="">
        <input type="hidden" name="pers_death_cause" value="">
        <input type="hidden" name="pers_death_time" value="">
        <input type="hidden" name="pers_death_age" value="">
        <input type="hidden" name="pers_death_text" value="">
        <input type="hidden" name="pers_buried_text" value="">
        <input type="hidden" name="pers_cremation" value="">
        <input type="hidden" name="person_text" value="">
        <input type="hidden" name="pers_own_code" value="">

        <div class="row m-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <h2>
                    <?= $person_kind == 'partner' ? __('Add relation') : __('Add child'); ?>
                </h2>
            </div>
        </div>

        <?php edit_firstname('pers_firstname', ''); ?>
        <?php edit_prefix('pers_prefix', $pers_prefix); ?>
        <?php edit_lastname('pers_lastname', $pers_lastname); ?>
        <?php edit_patronymic('pers_patronym', ''); ?>
        <?php edit_event_name('event_gedcom_new', 'event_event_name_new', ''); ?>
        <?php edit_privacyfilter('pers_alive', 'alive'); ?>
        <?php edit_sexe('pers_sexe', $pers_sexe); ?>

        <!-- Birth -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('born')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_birth_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_birth_date', '', '', 'pers_birth_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_birth_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_birth_place" id="pers_birth_place" value="" placeholder="<?= __('Start typing to search for a place.'); ?>" size="<?= $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                </div>
            </div>
        </div>
        <!-- Birth time and stillborn option -->
        <?php if ($person_kind == 'child') { ?>
            <div class="row mb-2">
                <label for="pers_birth_time" class="col-sm-3 col-form-label"><?= ucfirst(__('birth time')); ?></label>
                <div class="col-md-2">
                    <input type="text" name="pers_birth_time" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="pers_stillborn" class="form-check-input"> <?= __('stillborn child'); ?>
                </div>
            </div>
        <?php } else { ?>
            <input type="hidden" name="pers_birth_time" value="">
        <?php } ?>

        <!-- Baptise -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('baptised')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_bapt_date', '', '', 'pers_bapt_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_bapt_place" id="pers_bapt_place" value="" placeholder="<?= __('Start typing to search for a place.'); ?>" size="<?= $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                </div>
            </div>
        </div>

        <!-- Died -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('died')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_death_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_death_date', '', '', 'pers_death_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_death_place" id="pers_death_place" value="" placeholder="<?= __('Start typing to search for a place.'); ?>" size="<?= $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                </div>
            </div>
        </div>

        <!-- Buried -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('buried')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_buried_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_buried_date', '', '', 'pers_buried_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_buried_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_buried_place" id="pers_buried_place" value="" placeholder="<?= __('Start typing to search for a place.'); ?>" size="<?= $field_place; ?>" class="place-autocomplete form-control form-control-sm">
                </div>
            </div>
        </div>

        <!-- Profession -->
        <input type="hidden" name="event_date_profession_prefix" value=''>
        <input type="hidden" name="event_date_profession" value=''>
        <?php edit_profession('event_profession', ''); ?>

        <div class="row mb-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <?php if ($person_kind == 'partner') { ?>
                    <input type="submit" name="relation_add" value="<?= __('Add relation'); ?>" class="btn btn-sm btn-success">
                <?php } else { ?>
                    <input type="submit" name="person_add" value="<?= __('Add child'); ?>" class="btn btn-sm btn-success">
                <?php } ?>
            </div>
        </div>
    </form>
<?php
}
