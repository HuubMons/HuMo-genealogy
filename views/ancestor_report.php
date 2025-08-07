<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011: translated all variables to english by: Huub Mons.
 */

use Genealogy\Include\MarriageCls;

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

$personPrivacy = new Genealogy\Include\PersonPrivacy();
$personName_extended = new Genealogy\Include\PersonNameExtended();
$personData = new Genealogy\Include\PersonData();
$showSourcesFootnotes = new Genealogy\Include\ShowSourcesFootnotes();
$ancestorLabel = new Genealogy\Include\AncestorLabel();

//echo '<h1 class="standard_header">'.__('Ancestor report').'</h1>';
echo $data["ancestor_header"];

$ancestor_array2[] = $data["main_person"];
$ancestor_number2[] = 1;
$marriage_gedcomnumber2[] = 0;
$generation = 0;

$listed_array = array();

// *** Loop for ancestor report ***
while (isset($ancestor_array2[0])) {
    $generation++;

    unset($ancestor_array);
    $ancestor_array = $ancestor_array2;
    unset($ancestor_array2);

    unset($ancestor_number);
    $ancestor_number = $ancestor_number2;
    unset($ancestor_number2);

    unset($marriage_gedcomnumber);
    $marriage_gedcomnumber = $marriage_gedcomnumber2;
    unset($marriage_gedcomnumber2);
?>
    <h2 class="standard_header">
        <?php if (isset($data["rom_nr"][$generation])) { ?>
            <?= __('Generation') . ' ' . $data["rom_nr"][$generation]; ?>
            <?php }

        $generationLabel = $ancestorLabel->getLabel($generation);
        if ($generationLabel) {
            echo '(' . $generationLabel . ')';
        }

        if ($generation == 1) {
            if ($user["group_pdf_button"] == 'y' && $language["dir"] != "rtl" && $language["name"] != "简体中文") {
                // Show pdf button

                //$vars['id'] = $data["main_person"];
                //$link = $processLinks->get_link($uri_path, 'ancestor_report', $tree_id, true, $vars);
                //$link .= 'screen_mode=ancestor_chart&amp;show_sources=1';

                // TODO improve variables (use router).
                if ($humo_option["url_rewrite"] == "j") {
                    //$link = $uri_path . 'ancestor_report_pdf/' . $tree_id . '/' . $data["family_id"] . '?main_person=' . $data["main_person"];
                    $link = $uri_path . 'ancestor_report_pdf';
                } else {
                    //$link = $uri_path . 'index.php?page=ancestor_report_pdf&amp;tree_id=' . $tree_id . '&amp;id=' . $data["family_id"] . '&amp;main_person=' . $data["main_person"];
                    $link = $uri_path . 'index.php?page=ancestor_report_pdf';
                }
            ?>

                <form method="POST" action="<?= $link; ?>" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <input type="hidden" name="id" value="<?= $data["main_person"]; ?>">
                    <input type="hidden" name="screen_mode" value="PDF">

                    <!-- needed to check PDF M/F/? icons -->
                    <input type="hidden" name="ancestor_report" value="1">

                    <input type="submit" class="btn btn-sm btn-info" value="<?= __('PDF'); ?>" name="submit">
                </form>

            <?php
            }

            if ($user["group_rtf_button"] == 'y' && $language["dir"] != "rtl") {
                // Show rtf button
                $vars['id'] = $data["main_person"];
                $link = $processLinks->get_link($uri_path, 'ancestor_report_rtf', $tree_id, true, $vars);
                $link .= 'show_sources=1';
            ?>
                <form method="POST" action="<?= $link; ?>" style="display : inline;">
                    <input type="hidden" name="screen_mode" value="RTF">

                    <!-- needed to check RTF M/F/? icons -->
                    <input type="hidden" name="ancestor_report" value="1">

                    <input type="submit" class="btn btn-sm btn-info" value="<?= __('RTF'); ?>" name="submit">
                </form>
        <?php
            }
        }
        ?>
    </h2><br>

    <table class="table" align="center">
        <?php
        // *** Loop per generation ***
        $counter = count($ancestor_array);

        // *** Loop per generation ***
        for ($i = 0; $i < $counter; $i++) {

            $listednr = '';
            // Check this code, can be improved?
            foreach ($listed_array as $key => $value) {
                if ($value == $ancestor_array[$i]) {
                    $listednr = $key;
                }
                // if person was already listed, $listednr gets kwartier number for reference in report:
                // instead of person's details it will say: "already listed above under number 4234"
                // and no additional ancestors will be looked for, to prevent duplicated branches
            }
            if ($listednr == '') {
                //if not listed yet, add person to array
                $listed_array[$ancestor_number[$i]] = $ancestor_array[$i];
            }

            if ($ancestor_array[$i] != '0') {
                $person_manDb = $db_functions->get_person($ancestor_array[$i]);
                $privacy_man = $personPrivacy->get_privacy($person_manDb);

                if (strtolower($person_manDb->pers_sexe) === 'm' && $ancestor_number[$i] > 1) {
                    $familyDb = $db_functions->get_family($marriage_gedcomnumber[$i]);

                    // *** Use privacy filter of woman ***
                    $person_womanDb = $db_functions->get_person($familyDb->fam_woman);
                    $privacy_woman = $personPrivacy->get_privacy($person_womanDb);

                    $marriage_cls = new MarriageCls($familyDb, $privacy_man, $privacy_woman);
                    $family_privacy = $marriage_cls->get_privacy();
                }
        ?>

                <tr>
                    <td valign="top" width="80" nowrap><b><?= $ancestor_number[$i]; ?></b> (<?= floor($ancestor_number[$i] / 2); ?>)</td>
                    <td>
                        <!-- Show data man -->
                        <div class="parent1">
                            <!-- Use "child", to show a link for own family. -->
                            <?= $personName_extended->name_extended($person_manDb, $privacy_man, "child"); ?>
                            <?php if ($listednr == '') { ?>
                                <?= $personData->person_data($person_manDb, $privacy_man, "standard", $ancestor_array[$i]); ?>
                            <?php } else { ?>
                                <strong> (<?= __('Already listed above as number ') . $listednr; ?>) </strong>
                            <?php } ?>
                        </div>
                    </td>
                </tr>

                <!-- Show own marriage (new line, after man) -->
                <?php if (strtolower($person_manDb->pers_sexe) === 'm' && $ancestor_number[$i] > 1) { ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <span class="marriage">
                                <?php
                                if ($family_privacy) {
                                    echo __(' to: ');

                                    // If privacy filter is activated, show divorce
                                    if ($familyDb->fam_div_date || $familyDb->fam_div_place) {
                                        echo ' <span class="divorse">(' . trim(__('divorced ')) . ')</span>';
                                    }
                                    // Show end of relation here?
                                    //if ($familyDb->fam_relation_end_date){
                                    //  echo ' <span class="divorse">('.trim(__('divorced ')).')</span>';
                                    //}
                                } else {
                                    // To calculate age by marriage.
                                    $parent1Db = $person_manDb;
                                    $parent2Db = $person_womanDb;
                                    echo $marriage_cls->marriage_data();
                                }
                                ?>
                            </span>
                        </td>
                    </tr>
                <?php
                }

                // ==	Check for parents
                if ($person_manDb->pers_famc && $listednr == '') {
                    $family_parentsDb = $db_functions->get_family($person_manDb->pers_famc);
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
                $person_manDb = $db_functions->get_person($ancestor_array[$i]);
                $privacy_man = $personPrivacy->get_privacy($person_manDb);
                ?>

                <tr>
                    <td valign="top" width="80" nowrap><b><?= $ancestor_number[$i]; ?></b> (<?= floor($ancestor_number[$i] / 2); ?>)</td>
                    <td>
                        <!-- Show person_data of man -->
                        <div class="parent1">
                            <?php
                            // ***  Use "child", to show a link to own family. ***
                            echo $personName_extended->name_extended($person_manDb, $privacy_man, "child");
                            echo $personData->person_data($person_manDb, $privacy_man, "standard", $ancestor_array[$i]);
                            ?>
                        </div>
                    </td>
                </tr>
        <?php
            }
        }    // loop per generation
        ?>
    </table>

<?php } ?>

<!-- If source footnotes are selected, show them here -->
<?php if (isset($_SESSION['save_source_presentation']) && $_SESSION['save_source_presentation'] == 'footnote') { ?>
    <?= $showSourcesFootnotes->show_sources_footnotes(); ?>
<?php } ?>
<br><br>