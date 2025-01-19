<?php
// **********************
// *** OLD statistics ***
// **********************

include_once(__DIR__ . "/../../include/show_tree_date.php");

// *** Select database ***
$datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix != 'EMPTY' ORDER BY tree_order");
$num_rows = $datasql->rowCount();
if ($num_rows > 1) {
?>
    <h2><?= __('Old statistics (numbers since last GEDCOM update)'); ?></h2>

    <b><?= __('Select family tree'); ?></b><br>
    <?php
    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
        $tree_date = show_tree_date($dataDb->tree_date);
        $treetext = show_tree_text($dataDb->tree_id, $selected_language);

        if ($dataDb->tree_id == $tree_id) {
    ?>
            <b><?= $treetext['name']; ?></b>
        <?php } else { ?>
            <a href="index.php?page=statistics&amp;&amp;tab=statistics_old&amp;tree_id=<?= $dataDb->tree_id; ?>"><?= $treetext['name']; ?></a>
        <?php } ?>
        <font size=-1>
            (<?= $tree_date; ?>: <?= $dataDb->tree_persons; ?> <?= __('persons'); ?>, <?= $dataDb->tree_families; ?> <?= __('families'); ?>)
        </font><br>
<?php
    }
}

// *** Statistics ***
if (isset($tree_id) && $tree_id) {
    $db_functions->set_tree_id($tree_id);
}
?>
<br><b><?= __('Most visited families:'); ?></b><br>
<?php
//MAXIMUM 50 LINES
$family_qry = $dbh->query("SELECT fam_gedcomnumber, fam_tree_id, fam_counter, fam_man, fam_woman FROM humo_families
    WHERE fam_tree_id='" . $tree_id . "' AND fam_counter ORDER BY fam_counter desc LIMIT 0,50");
while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
    $vars['pers_family'] = $familyDb->fam_gedcomnumber;
    $link = $link_cls->get_link('../', 'family', $familyDb->fam_tree_id, false, $vars);
?>

    <?= $familyDb->fam_counter; ?> <a href="<?= $link; ?>"><?= __('Family'); ?>:</a>

<?php
    // *** Man ***
    $personDb = $db_functions->get_person($familyDb->fam_man);
    if (!$familyDb->fam_man) {
        echo __('N.N.');
    } else {
        $name = $person_cls->person_name($personDb);
        echo $name["standard_name"];
    }

    echo ' &amp; ';

    // *** Woman ***
    $personDb = $db_functions->get_person($familyDb->fam_woman);
    if (!$familyDb->fam_woman) {
        echo __('N.N.');
    } else {
        $name = $person_cls->person_name($personDb);
        echo $name["standard_name"];
    }
    echo '<br>';
}
