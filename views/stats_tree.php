<br>
<table class="humo small" align="center">
    <tr class="table_headline">
        <th><?= __('Item'); ?></th>
        <th><br></th>
        <th><br></th>
    </tr>

    <!-- Latest database update -->
    <tr>
        <td><?= __('Latest update'); ?></td>
        <td align="center"><i><?= $tree_date; ?></i></td>
        <td><br></td>
    </tr>

    <tr>
        <td colspan="3"><br></td>
    </tr>

    <!-- Nr. of families in database -->
    <tr>
        <td><?= __('No. of families'); ?></td>
        <td align="center"><i><?= $dataDb->tree_families; ?></i></td>
        <td><br></td>
    </tr>

    <?php
    // *** Most children in family ***
    $test_number = 0; // *** minimum of 0 children ***
    $res = @$dbh->query("SELECT fam_gedcomnumber, fam_man, fam_woman, fam_children
        FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_children != ''");
    while (@$record = $res->fetch(PDO::FETCH_OBJ)) {
        $count_children = substr_count($record->fam_children, ';');
        $count_children = $count_children + 1;
        if ($count_children > $test_number) {
            $test_number = $count_children;
            $man_gedcomnumber = $record->fam_man;
            $woman_gedcomnumber = $record->fam_woman;
            $fam_gedcomnumber = $record->fam_gedcomnumber;
        }
    }
    ?>
    <tr>
        <td><?= __('Most children in family'); ?></td>
        <td align='center'><i><?= $test_number; ?></i></td>
        <?php
        if ($test_number != "0") {
            @$record = $db_functions->get_person($man_gedcomnumber);
            $person_cls = new person_cls($record);
            $name = $person_cls->person_name($record);
            $man = $name["standard_name"];

            @$record = $db_functions->get_person($woman_gedcomnumber);
            $person_cls = new person_cls($record);
            $name = $person_cls->person_name($record);
            $woman = $name["standard_name"];

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            //$url=$person_cls->person_url2($fatherDb->pers_tree_id,$fatherDb->pers_famc,$fatherDb->pers_fams,$fatherDb->pers_gedcomnumber);
            $url = $person_cls->person_url2($tree_id, $fam_gedcomnumber, '', '');

            echo '<td align="center"><a href="' . $url . '"><i><b>' . $man . __(' and ') . $woman . '</b></i> </a></td>';
        } else {
            echo '<td></td>';
        }
        ?>
    </tr>
    <?php
    // *** Nr. of persons database ***
    $nr_persons = $dataDb->tree_persons;
    ?>
    <tr>
        <td><?= __('No. of persons'); ?></td>
        <td align='center'><i><?= $nr_persons; ?></i></td>
        <td><br></td>
    </tr>
</table>