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
$HtmlEscaper = new \Genealogy\Include\HtmlEscaper();
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
            <b><?= $HtmlEscaper->escHtml($treetext['name']); ?></b>
        <?php } else { ?>
            <a href="index.php?page=statistics&amp;&amp;tab=statistics_families&amp;tree_id=<?= $HtmlEscaper->escHtml((string) $familytree->tree_id); ?>"><?= $HtmlEscaper->escHtml($treetext['name']); ?></a>
        <?php } ?>
        <font size=-1>
            (<?= $HtmlEscaper->escHtml($tree_date); ?>: <?= $HtmlEscaper->escHtml((string) $familytree->tree_persons); ?> <?= __('persons'); ?>, <?= $HtmlEscaper->escHtml((string) $familytree->tree_families); ?> <?= __('families'); ?>)
        </font><br>
<?php
    }
}
?>

<br><b><?= __('Most visited families:'); ?></b><br>
<?php
//MAXIMUM 50 LINES
$family_qry = $dbh->query("SELECT f.fam_gedcomnumber, f.fam_tree_id, f.fam_counter,
    man_rel.person_id AS partner1_id,
    woman_rel.person_id AS partner2_id
    FROM humo_families f
    LEFT JOIN humo_relations_persons AS man_rel
        ON man_rel.tree_id = f.fam_tree_id
        AND man_rel.relation_gedcomnumber = f.fam_gedcomnumber
        AND man_rel.partner_order = 1
    LEFT JOIN humo_relations_persons AS woman_rel
        ON woman_rel.tree_id = f.fam_tree_id
        AND woman_rel.relation_gedcomnumber = f.fam_gedcomnumber
        AND woman_rel.partner_order = 2
    WHERE f.fam_tree_id='" . $tree_id . "' AND f.fam_counter
    ORDER BY f.fam_counter DESC LIMIT 0,50");
while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
    $vars['pers_family'] = $familyDb->fam_gedcomnumber;
    $link = $processLinks->get_link('../', 'family', $familyDb->fam_tree_id, false, $vars);
?>

    <?= $HtmlEscaper->escHtml((string) $familyDb->fam_counter); ?> <a href="<?= $HtmlEscaper->escHtml($link); ?>"><?= __('Family'); ?>:</a>

<?php
    // *** Man ***
    $personDb = $db_functions->get_person_with_id($familyDb->partner1_id);
    if (!$familyDb->partner1_id) {
        echo __('N.N.');
    } else {
        $privacy = $personPrivacy->get_privacy($personDb);
        $name = $personName->get_person_name($personDb, $privacy);
        echo $HtmlEscaper->escHtml($name["standard_name"]);
    }

    echo ' &amp; ';

    // *** Woman ***
    $personDb = $db_functions->get_person_with_id($familyDb->partner2_id);
    if (!$familyDb->partner2_id) {
        echo __('N.N.');
    } else {
        $privacy = $personPrivacy->get_privacy($personDb);
        $name = $personName->get_person_name($personDb, $privacy);
        echo $HtmlEscaper->escHtml($name["standard_name"]);
    }
    echo '<br>';
}
