<?php

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$showTreeText = new \Genealogy\Include\ShowTreeText();

// *** Select family tree ***
$tree_id_string = " AND ( ";
$id_arr = explode(";", substr($humo_option['geo_trees'], 0, -1)); // substr to remove trailing ;
foreach ($id_arr as $value) {
    $tree_id_string .= "tree_id='" . substr($value, 1) . "' OR ";  // substr removes leading "@" in geo_trees setting string
}
$tree_id_string = substr($tree_id_string, 0, -4) . ")"; // take off last " ON " and add ")"

$tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' " . $tree_id_string . " ORDER BY tree_order";
//echo $tree_search_sql;
$tree_search_result = $dbh->query($tree_search_sql);
$rowspan = $tree_search_result->rowCount() + 1;
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
                <table>
                    <tr>
                        <th><?= __('Name of tree'); ?></th>
                        <th style="text-align:center"><?= __('Starting year'); ?></th>
                        <th style="text-align:center"><?= __('Interval'); ?></th>
                        <th rowspan=<?= $rowspan; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="<?= __('Change'); ?>" class="btn btn-sm btn-secondary"></th>
                    </tr>
                    <?php
                    // *** REMARK: only works if there are multiple places in location table ***
                    while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                        ${"slider_choice" . $tree_searchDb->tree_prefix} = "1560"; // default
                        $query = "SELECT * FROM humo_settings WHERE setting_variable='gslider_" . $tree_searchDb->tree_prefix . "' ";
                        $result = $dbh->query($query);
                        $offset = "slider_choice_" . $tree_searchDb->tree_prefix;
                        if ($result->rowCount() > 0) {
                            $slider_choiceDb = $result->fetch(PDO::FETCH_OBJ);
                            ${"slider_choice" . $tree_searchDb->tree_prefix} = $slider_choiceDb->setting_value;
                            if (isset($_POST[$offset])) {
                                $db_functions->update_settings('gslider_' . $tree_searchDb->tree_prefix, $_POST[$offset]);
                                ${"slider_choice" . $tree_searchDb->tree_prefix} = $_POST[$offset];
                            }
                        } elseif (isset($_POST[$offset])) {
                            $sql = "INSERT INTO humo_settings SET setting_variable='gslider_" . $tree_searchDb->tree_prefix . "', setting_value='" . $_POST[$offset] . "'";
                            $dbh->query($sql);
                            ${"slider_choice" . $tree_searchDb->tree_prefix} = $_POST[$offset];
                        }

                        $treetext = $showTreeText ->show_tree_text($tree_searchDb->tree_id, $selected_language);
                        $interval = round((2010 - ${"slider_choice" . $tree_searchDb->tree_prefix}) / 9);
                    ?>
                        <tr>
                            <td><?= $treetext['name']; ?></td>
                            <td>
                                <?php
                                echo "<input style='text-align:center' type='text' name='" . $offset . "' value='{${"slider_choice" .$tree_searchDb->tree_prefix}}' class='form-control form-control-sm'>";
                                ?>
                            </td>
                            <td style='text-align:center'><?= $interval; ?></td>
                        </tr>
                    <?php } ?>
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

    <!-- TODO: isn't needed anymore? Can be selected at map -->
    <?php
    /*
    <div class="row mb-2">
        <div class="col-md-3"><?= __('Default map type'); ?></div>
        <div class="col-md-5">
            <select size="1" name="maptype_default" id="maptype_default" onChange="window.location='index.php?page=maps&menu=settings&maptype_default='+this.value;" class="form-select form-select-sm">
                <option value="ROADMAP" <?= $maps['map_type'] == "ROADMAP" ? 'selected' : ''; ?>><?= __('Regular map (ROADMAP)'); ?></option>
                <option value="HYBRID" <?= $maps['map_type'] == "HYBRID" ? 'selected' : ''; ?>><?= __('Satellite map with roads and places (HYBRID)'); ?></option>
            </select>
        </div>
    </div>
    */
    ?>

    <!-- No longer needed? Automatic zoom is used -->
    <?php
    /*
    <div class="row mb-2">
        <div class="col-md-3"><?= __('Default zoom'); ?></div>
        <div class="col-md-1">
            <select size="1" name="map_zoom_default" id="map_zoom_default" onChange="window.location='index.php?page=maps&menu=settings&map_zoom_default='+this.value;" class="form-select form-select-sm">
                <?php for ($x = 1; $x < 15; $x++) { ?>
                    <option value="<?= $x; ?>" <?= $maps['default_zoom'] == $x ? 'selected' : ''; ?>><?= $x; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    */
    ?>

</div>