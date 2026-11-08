<?php

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$showTreeText = new \Genealogy\Include\ShowTreeText();

// *** Select family tree ***
$tree_search_result = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
?>

<div class="p-3 m-2 genealogy_search container-md">
    <div class="row mb-1 p-2 bg-primary-subtle">
        <?= __('Settings'); ?>
    </div>

    <div class="row mb-2">
        <div class="col-md-12">
            <?= __('The slider has 10 steps. By default the starting year is 1560 with 9 intervals of 50 years up till 2010 and beyond.<br>You can set the starting year yourself for each tree, to suit it to the earliest years in that tree<br>The 9 intervals will be calculated automatically. Some example starting years for round intervals:<br>1110 (intv. 100), 1560 (intv. 50), 1695 (intv. 35),1740 (intv. 30), 1785 (intv. 25), 1830 (intv. 20)'); ?><br><br>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-12">
            <form name="slider" action="index.php?page=maps&amp;menu=settings" method="POST">
                <table class="table">
                    <tr>
                        <th><?= __('Name of tree'); ?></th>
                        <th style="text-align:center"><?= __('Starting year'); ?></th>
                        <th style="text-align:center"><?= __('Interval'); ?></th>
                    </tr>
                    <?php
                    // *** REMARK: only works if there are multiple places in location table ***
                    while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                        $sliderChoice = 1560;
                        $settingVariable = 'gslider_' . $tree_searchDb->tree_prefix;
                        $stmt = $dbh->prepare('SELECT setting_value FROM humo_settings WHERE setting_variable = :setting_variable');
                        $stmt->execute([':setting_variable' => $settingVariable]);
                        $sliderChoiceDb = $stmt->fetch(PDO::FETCH_OBJ);
                        $offset = "slider_choice_" . $tree_searchDb->tree_prefix;
                        if ($sliderChoiceDb && is_numeric($sliderChoiceDb->setting_value)) {
                            $sliderChoice = (int) $sliderChoiceDb->setting_value;
                        }
                        if (isset($_POST[$offset]) && is_numeric($_POST[$offset])) {
                            $sliderChoice = (int) $_POST[$offset];
                            if ($sliderChoiceDb) {
                                $db_functions->update_settings($settingVariable, (string) $sliderChoice);
                            } else {
                                $insertStmt = $dbh->prepare('INSERT INTO humo_settings (setting_variable, setting_value) VALUES (:setting_variable, :setting_value)');
                                $insertStmt->execute([
                                    ':setting_variable' => $settingVariable,
                                    ':setting_value' => (string) $sliderChoice
                                ]);
                            }
                        }

                        $treetext = $showTreeText->show_tree_text($tree_searchDb->tree_id, $selected_language);
                        $interval = round((2010 - $sliderChoice) / 9);
                    ?>
                        <tr>
                            <td><?= $HtmlEscaper->escHtml((string) $treetext['name']); ?></td>
                            <td>
                                <input style='text-align:center' type='text' name='<?= $HtmlEscaper->escHtml((string) $offset); ?>' value='<?= (int) $sliderChoice; ?>' class='form-control form-control-sm'>
                            </td>
                            <td style="text-align:center"><?= $interval; ?></td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <th colspan="3" style="text-align:center;">
                            <input type="submit" name="submit" value="<?= __('Change'); ?>" class="btn btn-sm btn-secondary">
                        </th>
                    </tr>
                </table>
            </form><br>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-3"><?= __('Default slider position'); ?></div>
        <div class="col-md-5">
            <select size="1" name="slider_default" id="slider_default" onChange="window.location='index.php?page=maps&menu=settings&slider_default='+this.value;" class="form-select form-select-sm">
                <option value="off" <?= $maps['slider'] == "off" ? 'selected' : ''; ?>><?= __('OFF position (leftmost position)'); ?></option>
                <option value="all" <?= $maps['slider'] == "all" ? 'selected' : ''; ?>><?= __('Show all periods (rightmost position)'); ?></option>
            </select>
        </div>
    </div>

</div>