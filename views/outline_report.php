<?php

/**
 * OUTLINE REPORT  - outline_report.php
 * 
 * Nov. 2008 Yossi Beck: created outline report on basis of Huub's family script.
 * Jul. 2011 Huub: translation of variables to English.
 * Nov. 2023 Huub: rebuild to MVC.
 * Jul. 2025 Huub: moved html output function to OutlineReportModel (also changed <div> into <ul>).
 */

// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($data["family_id"]);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

$path_form = $processLinks->get_link($uri_path, 'outline_report', $tree_id);

echo $data["descendant_header"];
?>

<div class="pers_name center d-print-none">
    <!-- Button: show full detais -->
    <form method="POST" action="<?= $path_form; ?>" style="display : inline;" class="me-3">
        <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
        <input type="hidden" name="nr_generations" value="<?= $data["nr_generations"]; ?>">
        <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">

        <?php if ($data["show_details"] == true) { ?>
            <input type="hidden" name="show_details" value="0">
            <input type="Submit" class="btn btn-sm btn-success" name="submit" value="<?= __('Hide full details'); ?>">
        <?php } else { ?>
            <input type="hidden" name="show_details" value="1">
            <input type="Submit" class="btn btn-sm btn-success" name="submit" value="<?= __('Show full details'); ?>">
        <?php } ?>
    </form>

    <?php if (!$data["show_details"]) { ?>
        <!-- Button: show date -->
        <form method="POST" action="<?= $path_form; ?>" style="display : inline;" class="me-3">
            <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
            <input type="hidden" name="nr_generations" value="<?= $data["nr_generations"]; ?>">
            <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
            <?php if ($data["show_date"] == true) { ?>
                <input type="hidden" name="show_date" value="0">
                <input type="Submit" class="btn btn-sm btn-success" name="submit" value="<?= __('Hide dates'); ?>">
            <?php } else { ?>
                <input type="hidden" name="show_date" value="1">
                <input type="Submit" class="btn btn-sm btn-success" name="submit" value="<?= __('Show dates'); ?>">
            <?php } ?>
        </form>

        <!-- Show button: date after or below each other -->
        <form method="POST" action="<?= $path_form; ?>" style="display : inline;" class="me-3">
            <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
            <input type="hidden" name="nr_generations" value="<?= $data["nr_generations"]; ?>">
            <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
            <?php if ($data["dates_behind_names"] == "1") { ?>
                <input type="hidden" name="dates_behind_names" value="0">
                <input type="submit" class="btn btn-sm btn-success" name="submit" value="<?= __('Dates below names'); ?>">
            <?php } else { ?>
                <input type="hidden" name="dates_behind_names" value="1">
                <input type="submit" class="btn btn-sm btn-success" name="submit" value="<?= __('Dates beside names'); ?>">
            <?php } ?>
        </form>
    <?php } ?>

    <!-- Show button: nr. of generations -->
    <span class="button me-3">
        <?= __('Choose number of generations to display'); ?>:

        <select size="1" name="selectnr_generations" aria-label="<?= __('Select number of generations to display'); ?>" class="form-select form-select-sm" onChange="window.location=this.value;" style="display:inline; width: 100px;">
            <?php
            $path_tmp = $processLinks->get_link($uri_path, 'outline_report', $tree_id, true);
            for ($i = 2; $i < 20; $i++) {
                $nr_gen = $i - 1;
            ?>
                <option <?php if ($nr_gen == $data["nr_generations"]) echo ' selected'; ?> value="<?= $path_tmp; ?>nr_generations=<?= $nr_gen; ?>&amp;id=<?= $data["family_id"]; ?>&amp;main_person=<?= $data["main_person"]; ?>&amp;show_details=<?= $data["show_details"]; ?>&amp;show_date=<?= $data["show_date"]; ?>&amp;dates_behind_names=<?= $data["dates_behind_names"]; ?>"><?= $i; ?></option>
            <?php } ?>
            <option <?= ($data["nr_generations"] == 50) ? 'selected' : ''; ?> value="<?= $path_tmp; ?>nr_generations=50&amp;id=<?= $data["family_id"]; ?>&amp;main_person=<?= $data["main_person"]; ?>&amp;show_date=<?= $data["show_date"]; ?>&amp;dates_behind_names=<?= $data["dates_behind_names"]; ?>"><?= __('All'); ?></option>
        </select>
    </span>

    <?php
    if (!$data["show_details"]) {
        if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") {
            // TODO check all variables.
            if ($humo_option["url_rewrite"] == "j") {
                //$link = $uri_path . 'outline_report_pdf/' . $tree_id . '/' . $data["family_id"] . '?main_person=' . $data["main_person"];
                $link = $uri_path . 'outline_report_pdf';
            } else {
                //$link = $uri_path . 'index.php?page=outline_report_pdf' . $tree_id . '&amp;id=' . $data["family_id"] . '&amp;main_person=' . $data["main_person"];
                $link = $uri_path . 'index.php?page=outline_report_pdf';
            }
            if ($data["descendant_report"] == true) {
                $link .= '&amp;descendant_report=1';
            }
    ?>

            <!-- Show pdf button landscape -->
            <form method="POST" action="<?= $link; ?>" style="display:inline-block;" class="me-3">
                <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                <input type="hidden" name="screen_mode" value="PDF-P">
                <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
                <input type="hidden" name="nr_generations" value="<?= $data["nr_generations"]; ?>">
                <input type="hidden" name="dates_behind_names" value="<?= $data["dates_behind_names"]; ?>">
                <input type="hidden" name="show_date" value="<?= $data["show_date"]; ?>">
                <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                <input type="Submit" class="btn btn-sm btn-info" name="submit" value="<?= __('PDF (Portrait)'); ?>">
            </form>

            <!-- Show pdf button portrait -->
            <form method="POST" action="<?= $link; ?>" style="display : inline;">
                <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                <input type="hidden" name="screen_mode" value="PDF-L">
                <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
                <input type="hidden" name="nr_generations" value="<?= $data["nr_generations"]; ?>">
                <input type="hidden" name="dates_behind_names" value="<?= $data["dates_behind_names"]; ?>">
                <input type="hidden" name="show_date" value="<?= $data["show_date"]; ?>">
                <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                <input type="Submit" class="btn btn-sm btn-info" name="submit" value="<?= __('PDF (Landscape)'); ?>">
            </form>
    <?php
        }
    }
    ?>
</div><br>

<!-- Show outline report HTML -->
<?= $data["outline_report_html"]; ?>