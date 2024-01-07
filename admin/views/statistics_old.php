<?php
// *************************
// *** OLD statistics ***
// *************************

// *** Select database ***
@$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
$num_rows = $datasql->rowCount();
if ($num_rows > 1) {
    echo '<h2>' . __('Old statistics (numbers since last GEDCOM update)') . '</h2>';

    echo '<b>' . __('Select family tree') . '</b><br>';
    while (@$dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
        if ($dataDb->tree_prefix != 'EMPTY') {
            // *** Update date ***
            $date = $dataDb->tree_date;
            if (!isset($date)) $date='';
            $month = ''; //voor lege datums
            // TODO translate months.
            if (substr($date, 5, 2) == '01') {
                $month = ' jan ';
            }
            if (substr($date, 5, 2) == '02') {
                $month = ' feb ';
            }
            if (substr($date, 5, 2) == '03') {
                $month = ' mrt ';
            }
            if (substr($date, 5, 2) == '04') {
                $month = ' apr ';
            }
            if (substr($date, 5, 2) == '05') {
                $month = ' mei ';
            }
            if (substr($date, 5, 2) == '06') {
                $month = ' jun ';
            }
            if (substr($date, 5, 2) == '07') {
                $month = ' jul ';
            }
            if (substr($date, 5, 2) == '08') {
                $month = ' aug ';
            }
            if (substr($date, 5, 2) == '09') {
                $month = ' sep ';
            }
            if (substr($date, 5, 2) == '10') {
                $month = ' okt ';
            }
            if (substr($date, 5, 2) == '11') {
                $month = ' nov ';
            }
            if (substr($date, 5, 2) == '12') {
                $month = ' dec ';
            }
            $date = substr($date, 8, 2) . $month . substr($date, 0, 4);

            $treetext = show_tree_text($dataDb->tree_id, $selected_language);
            if ($dataDb->tree_id == $tree_id) {
                echo '<b>' . $treetext['name'] . '</b>';
            } else {
                echo '<a href="index.php?page=' . $page . '&amp;tree_id=' . $dataDb->tree_id . '">' . $treetext['name'] . '</a>';
            }
            echo ' <font size=-1>(' . $date . ': ' . $dataDb->tree_persons . ' ' . __('persons') . ", " . $dataDb->tree_families . ' ' . __('families') . ")</font>\n<br>";
        }
    }
}

//*** Statistics ***
if (isset($tree_id) and $tree_id) $db_functions->set_tree_id($tree_id);
echo '<br><b>' . __('Most visited families:') . '</b><br>';
//MAXIMUM 50 LINES
$family_qry = $dbh->query("SELECT fam_gedcomnumber, fam_tree_id, fam_counter, fam_man, fam_woman FROM humo_families
    WHERE fam_tree_id='" . $tree_id . "' AND fam_counter ORDER BY fam_counter desc LIMIT 0,50");
while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
    $vars['pers_family'] = $familyDb->fam_gedcomnumber;
    $link = $link_cls->get_link('../', 'family', $familyDb->fam_tree_id, false, $vars);

    echo $familyDb->fam_counter . " ";
    echo '<a href="' . $link . '">' . __('Family') . ': </a>';

    //*** Man ***
    $personDb = $db_functions->get_person($familyDb->fam_man);
    if (!$familyDb->fam_man) {
        echo __('N.N.');
    } else {
        $name = $person_cls->person_name($personDb);
        echo $name["standard_name"];
    }

    echo " &amp; ";

    //*** Woman ***
    $personDb = $db_functions->get_person($familyDb->fam_woman);
    if (!$familyDb->fam_woman) {
        echo __('N.N.');
    } else {
        $name = $person_cls->person_name($personDb);
        echo $name["standard_name"];
    }
    echo "<br>";
}
        // *** End of old statistics ***