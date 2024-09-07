<?php
// *** Check tables for wrongly connected id's etc. ***
//echo '<h3>'.__('Checking database tables...').'<br>'.__('Please wait till finished').'.</h3>';
echo '<h3>' . __('Checking database tables...') . '</h3>';

$wrong_indexnr = 0;
$wrong_famc = 0;
$wrong_fams = 0;
$removed = '';
if (isset($_POST['remove'])) {
    $removed = ' <b>Link is removed.</b>';
}

// *** Option to remove wrong database connections ***
?>
<form method="POST" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="tab" value="integrity">
    <?= __('Remove links to missing items from database (first make a database backup!)'); ?>
    <input type="submit" name="remove" value="<?= __('REMOVE'); ?>" class="btn btn-sm btn-secondary">
</form>

<table class="table mt-2">
    <thead class="table-primary">
        <tr>
            <th><?= __('Check item'); ?></th>
            <th><?= __('Item'); ?></th>
            <th><?= __('Result'); ?></th>
        </tr>
    </thead>

    <?php
    // Test line to show processing time
    //$processing_time=time();

    // *** Check person table ***
    // *** First get pers_id, otherwise there will be a memory problem if a large family tree is used ***
    $person_start = $dbh->query("SELECT pers_id FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' ORDER BY pers_lastname,pers_firstname");
    while ($person_startDb = $person_start->fetch()) {

        // *** Now get all data for one person at a time ***
        $person = $dbh->query("SELECT pers_gedcomnumber,pers_famc,pers_fams FROM humo_persons
            WHERE pers_id='" . $person_startDb['pers_id'] . "'");
        $person = $person->fetch(PDO::FETCH_OBJ);

        // *** Relations/ marriages ***
        if ($person->pers_fams) {
            $check_fams1 = false;
            $fams = explode(";", $person->pers_fams);
            foreach ($fams as $i => $value) {
                $fam_qry = "SELECT fam_man,fam_woman FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $fams[$i] . "'";
                $fam_result = $dbh->query($fam_qry);
                $famDb = $fam_result->fetch(PDO::FETCH_OBJ);
                if ($famDb) {
                    if ($famDb->fam_man == $person->pers_gedcomnumber) {
                        $check_fams1 = true;
                    }
                    if ($famDb->fam_woman == $person->pers_gedcomnumber) {
                        $check_fams1 = true;
                    }
                } else {
                    // *** Family not found in database ***
                    $check_fams1 = false;
                }

                if ($check_fams1 == false) {
                    if (isset($_POST['remove'])) {
                        $new_fams = '';
                        for ($j = 0; $j <= count($fams) - 1; $j++) {
                            if ($fams[$j] !== $fams[$i]) {
                                if ($new_fams !== '') {
                                    $new_fams .= ';';
                                }
                                $new_fams .= $fams[$j];
                            }
                        }
                        $sql = "UPDATE humo_persons SET pers_fams='" . $new_fams . "' WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $person->pers_gedcomnumber . "'";
                        $dbh->query($sql);
                    }
                    $wrong_fams++;
    ?>
                    <tr>
                        <td><b>Missing marriage/ relation record</b></td>
                        <td>Person gedcomnr: <?= $person->pers_gedcomnumber; ?></td>
                        <td>Missing marriage/ relation gedcomnr: <?= $fams[$i] . $removed; ?></td>
                    </tr>
                <?php
                }
            }
        }

        // *** Parents ***
        if ($person->pers_famc) {
            $fam_qry = "SELECT fam_children FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $person->pers_famc . "'";
            $fam_result = $dbh->query($fam_qry);
            $famDb = $fam_result->fetch(PDO::FETCH_OBJ);

            $check_children = false;
            if (isset($famDb->fam_children)) {
                $children = explode(";", $famDb->fam_children);
                if (in_array($person->pers_gedcomnumber, $children)) {
                    $check_children = true;
                }
            }

            if ($check_children == false) {
                if ($famDb) {
                    // *** Restore child number ***
                    if ($famDb->fam_children) {
                        $fam_children = $famDb->fam_children . ';' . $person->pers_gedcomnumber;
                    } else {
                        $fam_children = $person->pers_gedcomnumber;
                    }
                    $sql = "UPDATE humo_families SET fam_children='" . $fam_children . "' WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $person->pers_famc . "'";
                    $dbh->query($sql);
                    $check_children = true;
                ?>
                    <tr>
                        <td><b>Missing child nr.</b></td>
                        <td>Fam gedcomnr: <?= $person->pers_famc; ?></td>
                        <td>Missing child gedcomnr: <?= $person->pers_gedcomnumber; ?>. <b>Is restored.</b></td>
                    </tr>
                <?php
                } else {
                    if (isset($_POST['remove'])) {
                        $sql = "UPDATE humo_persons SET pers_famc='' WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $person->pers_gedcomnumber . "'";
                        $dbh->query($sql);
                    }

                    // *** Missing parent record, no restore possible? ***
                ?>
                    <tr>
                        <td><b>Missing parents record</b></td>
                        <td>Child gedcomnr: <?= $person->pers_gedcomnumber; ?></td>
                        <td>missing parents gedcomnr: <?= $person->pers_famc . $removed; ?></td>
                    <?php
                    $wrong_famc++;
                }
            }
        }
    }

    //echo '<tr><td>!!'.time()-$processing_time.'</td><td></td><td></td></tr>';
    //$processing_time=time();

    // *** Check family table ***
    $wrong_children = 0;
    $fam_qry_start = "SELECT fam_id FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_children LIKE '_%'";
    $fam_result_start = $dbh->query($fam_qry_start);
    while ($famDb_start = $fam_result_start->fetch(PDO::FETCH_OBJ)) {
        $fam_qry = "SELECT fam_gedcomnumber,fam_man,fam_woman,fam_children FROM humo_families WHERE fam_id='" . $famDb_start->fam_id . "'";
        $fam_result = $dbh->query($fam_qry);
        $famDb = $fam_result->fetch(PDO::FETCH_OBJ);

        // *** Check man ***
        if ($famDb->fam_man) {
            $person = $db_functions->get_person($famDb->fam_man);
            $check_item = false;
            //if ($person){
            if (isset($person) && $person) {
                $fams_array = explode(";", $person->pers_fams);
                if (in_array($famDb->fam_gedcomnumber, $fams_array)) {
                    $check_item = true;
                }
                if ($check_item == false) {
                    // *** Restore pers_fams ***
                    $pers_fams = $person->pers_fams ? $person->pers_fams . ';' . $famDb->fam_gedcomnumber : $famDb->fam_gedcomnumber;
                    $sql = "UPDATE humo_persons SET pers_fams='" . $pers_fams . "' WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $person->pers_gedcomnumber . "'";
                    $dbh->query($sql);
                    ?>
                    <tr>
                        <td><b>Missing marriage/ relation nr. in person record</b></td>
                        <td>Man gedcomnr: <?= $famDb->fam_man; ?></td>
                        <td>Missing marriage/ relation gedcomnr: <?= $famDb->fam_gedcomnumber; ?>. <b>Is restored.</b></td>
                    </tr>
                <?php
                }
            } else {
                if (isset($_POST['remove'])) {
                    $sql = "UPDATE humo_families SET fam_man='0' WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
                    $dbh->query($sql);
                }
                ?>
                <tr>
                    <td><b>Missing man record in family</b></td>
                    <td>Family gedcomnr: <?= $famDb->fam_gedcomnumber; ?></td>
                    <td>Missing man gedcomnr: <?= $famDb->fam_man . $removed; ?></td>
                </tr>
                <?php
            }
        }

        // *** Check woman ***
        if ($famDb->fam_woman) {
            $person = $db_functions->get_person($famDb->fam_woman);
            $check_item = false;
            //if ($person){
            if (isset($person) && $person) {
                $fams_array = explode(";", $person->pers_fams);
                if (in_array($famDb->fam_gedcomnumber, $fams_array)) {
                    $check_item = true;
                }
                if ($check_item == false) {
                    // *** Restore pers_fams ***
                    $pers_fams = $person->pers_fams ? $person->pers_fams . ';' . $famDb->fam_gedcomnumber : $famDb->fam_gedcomnumber;
                    $sql = "UPDATE humo_persons SET pers_fams='" . $pers_fams . "' WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $person->pers_gedcomnumber . "'";
                    $dbh->query($sql);
                ?>
                    <tr>
                        <td><b>Missing marriage/ relation nr. in person record</b></td>
                        <td>Woman gedcomnr: <?= $famDb->fam_woman; ?></td>
                        <td>Missing marriage/ relation gedcomnr: <?= $famDb->fam_gedcomnumber; ?>. <b>Is restored.</b></td>
                    </tr>
                <?php
                }
            } else {
                if (isset($_POST['remove'])) {
                    $sql = "UPDATE humo_families SET fam_woman='0' WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
                    $dbh->query($sql);
                }
                ?>
                <tr>
                    <td><b>Missing woman record in family</b></td>
                    <td>Family gedcomnr: <?= $famDb->fam_gedcomnumber; ?></td>
                    <td>Missing woman gedcomnr: <?= $famDb->fam_woman . $removed; ?></td>
                </tr>
                <?php
            }
        }

        // *** Check children ***
        if ($famDb->fam_children) {
            $children = explode(";", $famDb->fam_children);
            foreach ($children as $i => $value) {
                $person = $db_functions->get_person($children[$i]);
                if ($person) {
                    if ($person->pers_famc == '') {
                        $sql = "UPDATE humo_persons SET pers_famc='" . $famDb->fam_gedcomnumber . "' WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $person->pers_gedcomnumber . "'";
                        $dbh->query($sql);
                ?>
                        <tr>
                            <td><b>Missing parent connection</b></td>
                            <td>Child gedcomnr: <?= $children[$i]; ?></td>
                            <td>Missing parent gedcomnr: <?= $famDb->fam_gedcomnumber; ?>. <b>Is restored.</b></td>
                        </tr>
                    <?php
                    }
                } else {
                    if (isset($_POST['remove'])) {
                        $new_children = '';
                        foreach ($children as $j => $value) {
                            if ($children[$j] != $children[$i]) {
                                if ($new_children != '') $new_children .= ';';
                                $new_children .= $children[$j];
                            }
                        }
                        $sql = "UPDATE humo_families SET fam_children='" . $new_children . "' WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $famDb->fam_gedcomnumber . "'";
                        $dbh->query($sql);
                    }

                    $wrong_children++;
                    ?>
                    <tr>
                        <td><b>Missing child record</b></td>
                        <td>Fam gedcomnr: <?= $famDb->fam_gedcomnumber; ?></td>
                        <td>Missing child gedcomnr: <?= $children[$i] . $removed; ?></td>
                    </tr>
                <?php
                    // NO RESTORE YET (not possible?)
                }
            }
        }
    }

    //echo '<tr><td>!!'.time()-$processing_time.'</td><td></td><td></td></tr>';
    //$processing_time=time();

    // *** Check connections table ***
    $connect_qry_start = "SELECT connect_id FROM humo_connections WHERE connect_tree_id='" . $tree_id . "'";
    $connect_result_start = $dbh->query($connect_qry_start);
    while ($connect_start = $connect_result_start->fetch(PDO::FETCH_OBJ)) {

        $connect_qry = "SELECT * FROM humo_connections WHERE connect_id='" . $connect_start->connect_id . "'";
        $connect_result = $dbh->query($connect_qry);
        $connect = $connect_result->fetch(PDO::FETCH_OBJ);

        // *** Check person ***
        //if ($connect->connect_kind=='person' AND $connect->connect_sub_kind!='pers_event_source' AND $connect->connect_sub_kind!='pers_address_source'){
        if ($connect->connect_kind == 'person' && $connect->connect_sub_kind != 'pers_event_source' && $connect->connect_sub_kind != 'pers_address_connect_source') {
            $person = $db_functions->get_person($connect->connect_connect_id);
            if (!$person) {
                if (isset($_POST['remove'])) {
                    $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_id='" . $connect->connect_id . "'";
                    $dbh->query($sql);
                }
                ?>
                <tr>
                    <td><b>Missing person record</b></td>
                    <td>Connection record: <?= $connect->connect_id; ?>/ <?= $connect->connect_sub_kind; ?></td>
                    <td>Missing person gedcomnr: <?= $connect->connect_connect_id . $removed; ?></td>
                </tr>
            <?php
            }
        }

        // *** Check family ***
        //if ($connect->connect_kind=='family' AND $connect->connect_sub_kind!='fam_event_source'){
        if ($connect->connect_kind == 'family' && $connect->connect_sub_kind != 'fam_event_source' && $connect->connect_sub_kind != 'fam_address_connect_source') {
            $fam_qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $connect->connect_connect_id . "'";
            $fam_result = $dbh->query($fam_qry);
            $fam = $fam_result->fetch(PDO::FETCH_OBJ);
            if (!$fam) {
                if (isset($_POST['remove'])) {
                    $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_id='" . $connect->connect_id . "'";
                    $dbh->query($sql);
                }
            ?>
                <tr>
                    <td><b>Missing family record</b></td>
                    <td>Connection record: <?= $connect->connect_id; ?>/ <?= $connect->connect_sub_kind; ?></td>
                    <td>Missing family gedcomnr: <?= $connect->connect_connect_id . $removed; ?></td>
                </tr>
            <?php
                // NO RESTORE YET (not possible?)
            }
        }
    }

    //echo '<tr><td>!!'.time()-$processing_time.'</td><td></td><td></td></tr>';
    //$processing_time=time();

    // *** Check events table ***
    $connect_qry_start = "SELECT event_id FROM humo_events WHERE event_tree_id='" . $tree_id . "'";
    $connect_result_start = $dbh->query($connect_qry_start);
    while ($connect_start = $connect_result_start->fetch(PDO::FETCH_OBJ)) {

        $connect_qry = "SELECT * FROM humo_events WHERE event_id='" . $connect_start->event_id . "'";
        $connect_result = $dbh->query($connect_qry);
        $connect = $connect_result->fetch(PDO::FETCH_OBJ);

        // *** Check person ***
        if ($connect->event_connect_kind == 'person' && $connect->event_connect_id) {
            // Use function check_person?
            $person = $db_functions->get_person($connect->event_connect_id);
            if (!$person) {
                if (isset($_POST['remove'])) {
                    $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_id='" . $connect->event_id . "'";
                    $dbh->query($sql);
                }
            ?>
                <tr>
                    <td><b>Missing person record</b></td>
                    <td>Event record: <?= $connect->event_id; ?>/ <?= $connect->event_kind; ?></td>
                    <td>Missing person gedcomnr: <?= $connect->event_connect_id . $removed; ?></td>
                </tr>
            <?php
            }
        }

        // *** Check family ***
        if ($connect->event_connect_kind == 'family' && $connect->event_connect_id) {
            // Create function check_family?
            $person_qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $tree_id . "'
                AND fam_gedcomnumber='" . $connect->event_connect_id . "'";
            $person_result = $dbh->query($person_qry);
            $person = $person_result->fetch(PDO::FETCH_OBJ);
            if (!$person) {
                if (isset($_POST['remove'])) {
                    $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_id='" . $connect->event_id . "'";
                    $dbh->query($sql);
                }
            ?>
                <tr>
                    <td><b>Missing family record</b></td>
                    <td>Event record: <?= $connect->event_id; ?>/ <?= $connect->event_kind; ?></td>
                    <td>Missing family gedcomnr: <?= $connect->event_connect_id; ?></td>
                </tr>
        <?php
            }
        }
    }

    //echo '<tr><td>!!'.time()-$processing_time.'</td><td></td><td></td></tr>';
    //$processing_time=time();

    if ($wrong_indexnr == 0) {
        ?>
        <tr>
            <td><?= __('Checked all person index numbers'); ?></td>
            <td></td>
            <td>ok</td>
        </tr>
    <?php
    }
    if ($wrong_fams == 0) {
    ?>
        <tr>
            <td><?= __('Checked all person - relation connections'); ?></td>
            <td></td>
            <td>ok</td>
        </tr>
    <?php
    }
    if ($wrong_famc == 0) {
    ?>
        <tr>
            <td><?= __('Checked all child - parent connections'); ?></td>
            <td></td>
            <td>ok</td>
        </tr>
    <?php
    }
    if ($wrong_children == 0) {
    ?>
        <tr>
            <td><?= __('Checked all parent - child connections'); ?></td>
            <td></td>
            <td>ok</td>
        </tr>
    <?php
    }
    ?>
</table>