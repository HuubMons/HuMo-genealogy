<?php

echo '<h2 align="center">' . __('Status statistics table') . '</h2>';
//$family_qry=$dbh->query("SELECT *, count(humo_stat_date.stat_easy_id) as count_lines
//	FROM humo_stat_date LEFT JOIN humo_trees
//	ON humo_trees.tree_id=humo_stat_date.stat_tree_id
//	GROUP BY humo_stat_date.stat_tree_id
//	ORDER BY tree_order desc");

$family_qry = $dbh->query("
SELECT * FROM humo_trees as humo_trees2
RIGHT JOIN
(
    SELECT stat_tree_id, count(humo_stat_date.stat_easy_id) as count_lines FROM humo_stat_date
    GROUP BY stat_tree_id
) as humo_stat_date2
ON humo_trees2.tree_id=humo_stat_date2.stat_tree_id
ORDER BY tree_order desc
");

echo '<table class="humo standard" border="1" cellspacing="0">';
echo '<tr class="table_header"><th>' . ucfirst(__('family tree')) . '</th><th>' . __('Records') . '</th><th>' . __('Number of unique visitors') . '</th></tr>';
while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
    //statistics_line($familyDb);
    if ($familyDb->tree_prefix) {
        $tree_id = $familyDb->tree_id;
        // *** Show family tree name ***
        $treetext = show_tree_text($familyDb->tree_id, $selected_language);
        echo '<tr><td>' . $treetext['name'] . '</td>';
    } else {
        echo '<tr><td><b>' . __('FAMILY TREE ERASED') . '</b></td>';
    }
    echo '<td>' . $familyDb->count_lines . '</td>';

    // *** Total number of unique visitors ***
    $count_visitors = 0;
    if ($familyDb->tree_id) {
        //$stat=$dbh->query("SELECT *
        //	FROM humo_stat_date LEFT JOIN humo_trees
        //	ON humo_trees.tree_id=humo_stat_date.stat_tree_id
        //	WHERE humo_trees.tree_id=".$familyDb->tree_id."
        //	GROUP BY stat_ip_address
        //	");
        $stat = $dbh->query("SELECT stat_ip_address
        FROM humo_stat_date LEFT JOIN humo_trees
        ON humo_trees.tree_id=humo_stat_date.stat_tree_id
        WHERE humo_trees.tree_id=" . $familyDb->tree_id . "
        GROUP BY stat_ip_address
        ");
        $count_visitors = $stat->rowCount();
    }
    echo '<td>' . $count_visitors . '</td>';
    echo '</tr>';
}
echo '</table>';

echo '<h2 align="center">' . __('General statistics:') . '</h2>';

echo '<table class="humo standard" border="1" cellspacing="0">';
echo '<tr class="table_header"><th>' . __('Item') . '</th><th>' . __('Counter') . '</th></tr>';
// *** Total number unique visitors ***
$stat = $dbh->query("SELECT stat_ip_address FROM humo_stat_date GROUP BY stat_ip_address");
$count_visitors = $stat->rowCount();
echo '<tr><td>' . __('Total number of unique visitors:') . '</td><td>' . $count_visitors . '</td>';

// *** Total number visited families ***
$datasql = $dbh->query("SELECT stat_id FROM humo_stat_date");
if ($datasql) {
    $total = $datasql->rowCount();
}
echo '<tr><td>' . __('Total number of visited families:') . '</td><td>' . $total . '</td>';

// Visitors per day/ month/ year.
// 1 day = 86400
$time_period = strtotime("now") - 3600; // 1 hour
$datasql = $dbh->query("SELECT * FROM humo_stat_date WHERE stat_date_linux > " . $time_period);
if ($datasql) {
    $total = $datasql->rowCount();
}
echo '<tr><td>' . __('Total number of families in the last hour:') . '</td><td>' . $total . '</td>';
echo '</table>';


// *** Country statistics ***
echo '<h2 align="center">' . __('Unique visitors - Country of origin') . '</h2>';
country2();
// *** End country statistics ***


$nr_lines = 15; // *** Nr. of statistics lines ***

//$family_qry=$dbh->query("SELECT *, count(humo_stat_date.stat_easy_id) as count_lines
//	FROM humo_stat_date, humo_trees
//	WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id
//	GROUP BY humo_stat_date.stat_easy_id desc
//	ORDER BY count_lines desc
//	LIMIT 0,".$nr_lines);

// *** Didn't use "GROUP BY stat_easy_id" because stat_tree_id is also needed, and 2 results in GROUP BY is not allowed in > MySQL 5.7 ***
$family_qry = $dbh->query(
    "
SELECT * FROM humo_trees as humo_trees2
RIGHT JOIN
(
    SELECT stat_tree_id, stat_gedcom_fam, stat_gedcom_man, stat_gedcom_woman, count(humo_stat_date.stat_easy_id) as count_lines FROM humo_stat_date
    GROUP BY stat_tree_id, stat_gedcom_fam, stat_gedcom_man, stat_gedcom_woman
) as humo_stat_date2
ON humo_trees2.tree_id=humo_stat_date2.stat_tree_id
ORDER BY count_lines desc
LIMIT 0," . $nr_lines
);

echo '<h2 align="center">' . $nr_lines . ' ' . __('Most visited families:') . '</h2>';
echo '<table class="humo standard" border="1" cellspacing="0">';
echo '<tr class="table_header"><th>#</th><th>' . __('family tree') . '</th><th>' . __('family') . '</th></tr>';
while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
    statistics_line($familyDb);
}
echo '</table>';

//$family_qry=$dbh->query("SELECT * FROM humo_stat_date, humo_trees
//	WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id
//	ORDER BY humo_stat_date.stat_date_stat DESC LIMIT 0,".$nr_lines);
// *** First line is a bit strange, but was needed for a specific provider ***
$family_qry = $dbh->query("SELECT humo_stat_date.* , humo_trees.tree_id, humo_trees.tree_prefix FROM humo_stat_date, humo_trees 
WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id 
ORDER BY humo_stat_date.stat_date_stat DESC LIMIT 0," . $nr_lines);
echo '<h2 align="center">' . $nr_lines . ' ' . __('last visited families:') . '</h2>';
echo '<table class="humo standard" border="1" cellspacing="0">';
echo '<tr class="table_header"><th>' . __('family tree') . '</th><th>' . __('date-time') . '</th><th>' . __('family') . '</th></tr>';
while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
    statistics_line($familyDb);
}
echo '</table>';


// *** Show 1 statistics line ***
function statistics_line($familyDb)
{
    global $dbh, $language, $person_cls, $selected_language, $db_functions, $link_cls;

    $tree_id = $familyDb->tree_id;
    if (isset($tree_id) && $tree_id) {
        $db_functions->set_tree_id($tree_id);
    }

    echo '<tr>';
    if (isset($familyDb->count_lines)) {
        echo '<td>' . $familyDb->count_lines . '</td>';
    }

    $treetext = show_tree_text($familyDb->tree_id, $selected_language);
    echo '<td>' . $treetext['name'] . '</td>';

    if (!isset($familyDb->count_lines)) {
        echo '<td>' . $familyDb->stat_date_stat . '</td>';
    }

    // *** Check if family is still in the genealogy! ***
    $checkDb = $db_functions->get_family($familyDb->stat_gedcom_fam);
    $check = false;
    if ($checkDb && $checkDb->fam_man == $familyDb->stat_gedcom_man && $checkDb->fam_woman == $familyDb->stat_gedcom_woman) {
        $check = true;
    }

    if ($check == true) {
        $vars['pers_family'] = $familyDb->stat_gedcom_fam;
        $link = $link_cls->get_link('../', 'family', $familyDb->tree_id, false, $vars);
        echo '<td><a href="' . $link . '">' . __('Family') . ': </a>';

        //*** Man ***
        $personDb = $db_functions->get_person($familyDb->stat_gedcom_man);

        if (!$familyDb->stat_gedcom_man) {
            echo 'N.N.';
        } else {
            $name = $person_cls->person_name($personDb);
            echo $name["standard_name"];
        }

        echo " &amp; ";

        //*** Woman ***
        $personDb = $db_functions->get_person($familyDb->stat_gedcom_woman);
        if (!$familyDb->stat_gedcom_woman) {
            echo 'N.N.';
        } else {
            $name = $person_cls->person_name($personDb);
            echo $name["standard_name"];
        }
    } else {
        echo '<td><b>' . __('FAMILY NOT FOUND IN FAMILY TREE') . '</b></td>';
    }
}
