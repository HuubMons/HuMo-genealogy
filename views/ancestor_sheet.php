<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011: translated all variables to english by: Huub Mons.
 */

$ancestorBox = new \Genealogy\Include\AncestorBox();

$screen_mode = 'ancestor_sheet';

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

$personDb = $db_functions->get_person($data["gedcomnumber"][1]);
$pers_privacy = $personPrivacy->get_privacy($personDb);
$name = $personName->get_person_name($personDb, $pers_privacy);
?>

<?= $data["ancestor_header"]; ?>

<h4 class="text-center m-3">
    <?php /*
    <?= __('Ancestor sheet') . __(' of ') . $ancestorBox->ancestorBox(1, "ancestor_header"); ?>
    */ ?>

    <?= __('Ancestor sheet') . __(' of ') . $name["name"] . $name["colour_mark"]; ?>

    <!-- Show pdf button -->
    <?php if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") { ?>
        <?php
        if ($humo_option["url_rewrite"] == "j") {
            $link = $uri_path . 'ancestor_sheet_pdf/' . $tree_id . '?main_person=' . $data["main_person"];
        } else {
            $link = $uri_path . 'index.php?page=ancestor_sheet_pdf&amp;tree_id=' . $tree_id . '&amp;main_person=' . $data["main_person"];
        }
        ?>
        <form method="POST" action="<?= $link; ?>" style="display:inline-block; vertical-align:middle;" class="ms-3">
            <input type="hidden" name="screen_mode" value="ASPDF">
            <input type="submit" class="btn btn-sm btn-info" value="<?= __('PDF'); ?>" name="submit">
        </form>
    <?php } ?>
</h4>

<!-- TEST improved layout -->
<!--
<style>
    .box_ancestor_sheet {
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.07);
        padding: 8px 4px;
        min-width: 120px;
        background: #f8f9fa;
    }
</style>
-->

<div class="table-responsive">
    <!-- <table class="ancestor_sheet"> -->
    <table class="table table-bordered table-sm text-center align-middle">
        <?php
        $gen = check_gen(16, 32);
        if ($gen == 1) {
            kwname(16, 32, 2, " kw-small", 1, "medium");
            kwname(16, 32, 2, " kw-small", 1, "ancestor_sheet_marr");
            kwname(17, 33, 2, " kw-small", 1, "medium");
            echo '<tr><td colspan=8 class="ancestor_devider">&nbsp;</td></tr>';
        }

        $gen = check_gen(8, 16);
        if ($gen == 1) {
            kwname(8, 16, 1, " kw-medium", 1, "medium");
            kwname(8, 16, 2, " kw-small", 2, "ancestor_sheet_marr");
            echo '<tr><td colspan=8 class="ancestor_devider">&nbsp;</td></tr>';
        }

        $gen = check_gen(4, 8);
        if ($gen == 1) {
            kwname(4, 8, 1, " kw-bigger", 2, "medium");
            kwname(4, 8, 2, " kw-small", 4, "ancestor_sheet_marr");
            echo '<tr><td colspan=8 class="ancestor_devider">&nbsp;</td></tr>';
        }

        kwname(2, 4, 1, " kw-big", 4, "medium");

        kwname(2, 4, 2, " kw-small", 8, "ancestor_sheet_marr");

        kwname(1, 2, 1, " kw-big", 8, "medium");
        ?>
    </table>
</div>

<div class="ancestor_legend mt-3">
    <b><?= __('Legend'); ?></b><br>
    <?= __('*') . '  ' . __('born') . ', ' . __('&#134;') . '  ' . __('died') . ', ' . __('X') . '  ' . __('married'); ?><br>
</div>

<?php
// print names and details for each row in the table
function kwname($start, $end, $increment, $fontclass, $colspan, $type)
{
    global $data, $ancestorBox;
?>

    <tr>
        <?php
        for ($x = $start; $x < $end; $x += $increment) {
            // *** Added coloured boxes in november 2022 ***
            $sexe_colour = '';
            if ($type != 'ancestor_sheet_marr') {
                if ($data["sexe"][$x] == 'F') {
                    $sexe_colour = ' box_woman';
                }
                if ($data["sexe"][$x] == 'M') {
                    $sexe_colour = ' box_man';
                }
            }
        ?>
            <td <?= $colspan > 1 ? 'colspan=' . $colspan : ''; ?> class="box_ancestor_sheet <?= $sexe_colour; ?> align-top">
                <?= $ancestorBox->ancestorBox($x, $type, $fontclass); ?>
            </td>
        <?php } ?>
    </tr>
<?php
}

// Check if there is anyone in a generation so no empty and collapsed rows will be shown
function check_gen($start, $end)
{
    global $data;
    $is_gen = 0;
    for ($i = $start; $i < $end; $i++) {
        if (isset($data["gedcomnumber"][$i]) && $data["gedcomnumber"][$i] != '') {
            $is_gen = 1;
        }
    }
    return $is_gen;
}
