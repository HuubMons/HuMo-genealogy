<?php

// *** Read settings here to be sure radio buttons show proper values. ***
include_once(__DIR__ . "/../../include/settings_global.php"); // *** Read settings ***

// *** Language choice ***
$language_tree2 = $language_tree;
if ($language_tree == 'default') {
    $language_tree2 = $selected_language;
}
include(__DIR__ . '/../../languages/' . $language_tree2 . '/language_data.php');
include_once(__DIR__ . "/../../views/partial/select_language.php");
$language_path = 'index.php?page=tree&amp;tree_id=' . $tree_id . '&amp;';
?>

<br>
<?= __('Administration of the family tree(s), i.e. the name can be changed here, and trees can be added or removed.'); ?><br>

<table class="humo" border="1" cellspacing="0" width="100%">
    <tr class="table_header">
        <th><?= __('Order'); ?></th>
        <th><?= __('Name of family tree'); ?></th>
        <th><?= __('Family tree data'); ?></th>
        <th><?= __('Remove'); ?></th>
    </tr>

    <tr class="table_header">
        <td></td>
        <td>
            <div class="row mb-2">
                <div class="col-md-3">
                    <a href="index.php?page=tree&amp;language_tree=default&amp;tree_id=<?= $tree_id; ?>"><?= __('Default'); ?></a>
                </div>

                <div class="col-md-auto ms-2">
                    <?= show_country_flags($language_tree2, '../', 'language_tree', $language_path); ?>
                </div>
            </div>
        </td>
        <td></td>
        <td></td>
    </tr>

    <?php
    // *** Check number of family trees, because last tree is not allowed to be removed ***
    $count_trees = 0;
    $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
    $count_trees = $datasql->rowCount();

    $new_number = '1';
    $datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
    if ($datasql) {
        // *** Count lines in query ***
        $count_lines = $datasql->rowCount();
        while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
            $style = '';
            if ($dataDb->tree_id == $tree_id) {
                $style = ' bgcolor="#99CCFF"';
            }
    ?>
            <tr <?= $style; ?>>
                <td nowrap>
                    <?php
                    if ($dataDb->tree_order < 10) {
                        echo '0';
                    }
                    echo $dataDb->tree_order;
                    // *** Number for new family tree ***
                    $new_number = $dataDb->tree_order + 1;
                    if ($dataDb->tree_order != '1') {
                        echo ' <a href="' . $phpself2 . 'page=' . $page . '&amp;up=1&amp;tree_order=' . $dataDb->tree_order .
                            '&amp;id=' . $dataDb->tree_id . '"><img src="images/arrow_up.gif" border="0" alt="up"></a>';
                    }
                    if ($dataDb->tree_order != $count_lines) {
                        echo ' <a href="' . $phpself2 . 'page=' . $page . '&amp;down=1&amp;tree_order=' . $dataDb->tree_order .
                            '&amp;id=' . $dataDb->tree_id . '"><img src="images/arrow_down.gif" border="0" alt="down"></a>';
                    }
                    ?>
                </td>

                <td>
                    <?php
                    // *** Show/ Change family tree name ***
                    $treetext = show_tree_text($dataDb->tree_id, $language_tree);
                    if ($dataDb->tree_prefix == 'EMPTY') {
                        echo '* ' . __('EMPTY LINE') . ' *';
                    } else {
                        echo '<a href="index.php?page=' . $page . '&amp;menu_admin=tree_text&amp;tree_id=' . $dataDb->tree_id . '"><img src="images/edit.jpg" title="edit" alt="edit"></a> ' . $treetext['name'];
                    }
                    ?>
                </td>

                <td>
                    <?php
                    if ($dataDb->tree_prefix != 'EMPTY') {
                        echo '<a href="index.php?page=' . $page . '&amp;menu_admin=tree_gedcom&amp;tree_id=' . $dataDb->tree_id . '&tree_prefix=' . $dataDb->tree_prefix . '&step1=read_gedcom"><img src="images/import.jpg" title="gedcom import" alt="gedcom import"></a>';
                    }

                    if ($dataDb->tree_prefix == 'EMPTY') {
                        //
                    } elseif ($dataDb->tree_persons > 0) {
                        echo ' <font color="#00FF00"><b>' . __('OK') . '</b></font>';

                        // *** Show tree data ***
                        $tree_date = $dataDb->tree_date;
                        $month = ''; // for empty tree_dates
                        if (substr($tree_date, 5, 2) === '01') {
                            $month = ' ' . strtolower(__('jan')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '02') {
                            $month = ' ' . strtolower(__('feb')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '03') {
                            $month = ' ' . strtolower(__('mar')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '04') {
                            $month = ' ' . strtolower(__('apr')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '05') {
                            $month = ' ' . strtolower(__('may')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '06') {
                            $month = ' ' . strtolower(__('jun')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '07') {
                            $month = ' ' . strtolower(__('jul')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '08') {
                            $month = ' ' . strtolower(__('aug')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '09') {
                            $month = ' ' . strtolower(__('sep')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '10') {
                            $month = ' ' . strtolower(__('oct')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '11') {
                            $month = ' ' . strtolower(__('nov')) . ' ';
                        }
                        if (substr($tree_date, 5, 2) === '12') {
                            $month = ' ' . strtolower(__('dec')) . ' ';
                        }
                        $tree_date = substr($tree_date, 8, 2) . $month . substr($tree_date, 0, 4);
                        echo ' <font size=-1>' . $tree_date . ': ' . $dataDb->tree_persons . ' ' .
                            __('persons') . ', ' . $dataDb->tree_families . ' ' . __('families') . '</font>';
                    } else {
                        //echo ' <font color="#FF0000"><b>'.__('ERROR').'!</b></font>';
                        echo ' <b>' . __('This tree does not yet contain any data or has not been imported properly!') . '</b>';
                    }
                    ?>
                </td>

                <td>
                    <?php
                    // *** If there is only one family tree, prevent it can be removed ***
                    if ($count_trees > 1 || $dataDb->tree_prefix == 'EMPTY') {
                        echo ' <a href="index.php?page=' . $page . '&amp;remove_tree=' . $dataDb->tree_id . '&amp;treetext_name=' . $treetext['name'] . '">';
                        echo '<img src="images/button_drop.png" alt="' . __('Remove tree') . '" border="0"></a>';
                    }
                    ?>
                </td>
            </tr>
    <?php
        }
    }

    // *** Add new family tree ***

    // *** Find latest tree_prefix ***
    $found = '1';
    $i = 1;
    while ($found == '1') {
        $new_tree_prefix = 'humo' . $i . '_';
        $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='$new_tree_prefix'");
        $found = $datasql->rowCount();
        $i++;
    }

    if ($new_number < 10) {
        $new_number = '0' . $new_number;
    }
    ?>

    <tr>
        <td colspan="4"><br></td>
    </tr>

    <tr>
        <td><?= $new_number; ?></td>
        <td colspan="3">
            <form method="post" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="tree_order" value="<?= $new_number; ?>">
                <input type="hidden" name="tree_prefix" value="<?= $new_tree_prefix; ?>">
                <input type="submit" name="add_tree_data" value="<?= __('Add family tree'); ?>" class="btn btn-sm btn-success">
            </form>
        </td>
    </tr>

    <tr>
        <td colspan="4"><br></td>
    </tr>

    <tr>
        <td><?= $new_number; ?></td>
        <td colspan="3">
            <form method="post" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="tree_order" value="<?= $new_number; ?>">
                <input type="submit" name="add_tree_data_empty" value="<?= __('Add empty line'); ?>" class="btn btn-sm btn-success">
                <?= __('Add empty line in list of family trees'); ?>
            </form>
        </td>
    </tr>
</table>

<?php
// ** Change collation of family tree (needed for Swedish etc.) ***
$collation_sql = $dbh->query("SHOW FULL COLUMNS FROM humo_persons WHERE Field = 'pers_firstname'");
$collationDb = $collation_sql->fetch(PDO::FETCH_OBJ);
$collation = $collationDb->Collation;

// *** Swedish collation ***
$select_swedish = '';
if ($collation == 'utf8_swedish_ci') {
    $select_swedish = 'selected';
}

// *** Danish collation ***
$select_danish = '';
if ($collation == 'utf8_danish_ci') {
    $select_danish = 'selected';
}
?>

<form method="post" action="<?= $phpself; ?>" style="display : inline;">
    <input type="hidden" name="page" value="<?= $page; ?>">

    <br>
    <div class="row mb-2">
        <div class="col-md-auto">
            <label for="tree_collation" class="col-form-label"><?= __('Collation'); ?></label>
        </div>

        <div class="col-md-auto">
            <select size="1" name="tree_collation" class="form-select form-select-sm">
                <!-- Default collation -->
                <option value="utf8_general_ci">utf8_general_ci (default)</option>
                <option value="utf8_swedish_ci" <?= $select_swedish; ?>>utf8_swedish_ci</option>
                <option value="utf8_danish_ci" <?= $select_danish; ?>>utf8_danish_ci</option>
            </select>
        </div>

        <div class="col-md-auto">
            <input type="submit" name="change_collation" value="OK" class="btn btn-sm btn-secondary">
        </div>
    </div>
</form>