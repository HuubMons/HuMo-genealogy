<?php
// *** Check tables for wrongly connected id's etc. ***

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// TODO check translations in this script.

$wrong_indexnr = 0;
$wrong_famc = 0;
$wrong_fams = 0;
$removed = isset($_POST['remove']);
?>

<h3><?= __('Checking database tables...'); ?></h3>

<!-- Option to remove wrong database connections -->
<form method="POST" action="index.php?page=check&tab=integrity">
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
                    <td>Connection record: <?= $htmlEscaper->escHtml($connect->connect_id); ?>/ <?= $htmlEscaper->escHtml($connect->connect_sub_kind); ?></td>
                    <td>Missing person gedcomnr: <?= $htmlEscaper->escHtml($connect->connect_connect_id); ?><?= $removed ? ' <b>Link is removed.</b>' : ''; ?></td>
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
                    <td>Connection record: <?= $htmlEscaper->escHtml($connect->connect_id); ?>/ <?= $htmlEscaper->escHtml($connect->connect_sub_kind); ?></td>
                    <td>Missing family gedcomnr: <?= $htmlEscaper->escHtml($connect->connect_connect_id); ?><?= $removed ? ' <b>Link is removed.</b>' : ''; ?></td>
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

        // TODO also check witnesses: ASSO.
        // *** Check person ***
        if ($connect->event_connect_kind == 'person' && $connect->event_connect_id) {
            $person = $db_functions->get_person_with_id($connect->person_id);
            if (!$person) {
                if (isset($_POST['remove'])) {
                    $sql = "DELETE FROM humo_events WHERE event_id='" . $connect->event_id . "'";
                    $dbh->query($sql);
                }
            ?>
                <tr>
                    <td><b>Missing person record</b></td>
                    <td>Event record: <?= $htmlEscaper->escHtml($connect->event_id); ?>/ <?= $htmlEscaper->escHtml($connect->event_kind); ?></td>
                    <td>Missing person gedcomnr: <?= $htmlEscaper->escHtml($connect->event_connect_id); ?><?= $removed ? ' <b>Link is removed.</b>' : ''; ?></td>
                </tr>
            <?php
            }
        }

        // *** Check family ***
        if ($connect->event_connect_kind == 'family' && $connect->event_connect_id) {
            $family = $db_functions->get_family_with_id($connect->relation_id);
            if (!$family) {
                if (isset($_POST['remove'])) {
                    $sql = "DELETE FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_id='" . $connect->event_id . "'";
                    $dbh->query($sql);
                }
            ?>
                <tr>
                    <td><b>Missing family record</b></td>
                    <td>Event record: <?= $htmlEscaper->escHtml($connect->event_id); ?>/ <?= $htmlEscaper->escHtml($connect->event_kind); ?></td>
                    <td>Missing family gedcomnr: <?= $htmlEscaper->escHtml($connect->event_connect_id); ?></td>
                </tr>
        <?php
            }
        }
    }

    //echo '<tr><td>!!'.time()-$processing_time.'</td><td></td><td></td></tr>';
    //$processing_time=time();

    /*
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
    */
    ?>

    <tr>
        <td>Oct. 2025: because of database normalisation, several checks are disabled.</td>
        <td></td>
        <td>ok</td>
    </tr>

</table>