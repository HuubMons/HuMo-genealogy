<?php

/**
 * Family statistics
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$familytrees = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix != 'EMPTY' ORDER BY tree_order");
$num_rows = $familytrees->rowCount();

// *** Statistics ***
if (isset($tree_id) && $tree_id) {
    $db_functions->set_tree_id($tree_id);
}

$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();
$showTreeDate = new \Genealogy\Include\ShowTreeDate();
?>

<h2><?= __('Family statistics (numbers since last GEDCOM update)'); ?></h2>

<?php if ($num_rows > 1) { ?>
    <b><?= __('Select family tree'); ?></b><br>
    <?php
    while ($familytree = $familytrees->fetch(PDO::FETCH_OBJ)) {
        $tree_date = $showTreeDate->show_tree_date($familytree->tree_date);
        $treetext = $showTreeText->show_tree_text($familytree->tree_id, $selected_language);

        if ($familytree->tree_id == $tree_id) {
    ?>
            <b><?= $treetext['name']; ?></b>
        <?php } else { ?>
            <a href="index.php?page=statistics&amp;&amp;tab=statistics_families&amp;tree_id=<?= $familytree->tree_id; ?>"><?= $treetext['name']; ?></a>
        <?php } ?>
        <font size=-1>
            (<?= $tree_date; ?>: <?= $familytree->tree_persons; ?> <?= __('persons'); ?>, <?= $familytree->tree_families; ?> <?= __('families'); ?>)
        </font><br>
<?php
    }
}
?>

<br><b><?= __('Most visited families:'); ?></b><br>
<?php
//MAXIMUM 50 LINES
$family_qry = $dbh->query("SELECT fam_gedcomnumber, fam_tree_id, fam_counter, fam_man, fam_woman FROM humo_families
    WHERE fam_tree_id='" . $tree_id . "' AND fam_counter ORDER BY fam_counter desc LIMIT 0,50");
while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
    $vars['pers_family'] = $familyDb->fam_gedcomnumber;
    $link = $processLinks->get_link('../', 'family', $familyDb->fam_tree_id, false, $vars);
?>

    <?= $familyDb->fam_counter; ?> <a href="<?= $link; ?>"><?= __('Family'); ?>:</a>

<?php
    // *** Man ***
    $personDb = $db_functions->get_person($familyDb->fam_man);
    if (!$familyDb->fam_man) {
        echo __('N.N.');
    } else {
        $privacy = $personPrivacy->get_privacy($personDb);
        $name = $personName->get_person_name($personDb, $privacy);
        echo $name["standard_name"];
    }

    echo ' &amp; ';

    // *** Woman ***
    $personDb = $db_functions->get_person($familyDb->fam_woman);
    if (!$familyDb->fam_woman) {
        echo __('N.N.');
    } else {
        $privacy = $personPrivacy->get_privacy($personDb);
        $name = $personName->get_person_name($personDb, $privacy);
        echo $name["standard_name"];
    }
    echo '<br>';
}
