<?php
$found = false; // if this stays false, displays message that no problems where found
?>

<table class="table">
    <thead class="table-primary">
        <tr>
            <th style="width:10%;"><?= __('ID'); ?></th>
            <th style="width:55%;"><?= __('Edit invalid date'); ?></th>
            <th style="width:20%;"><?= __('Details'); ?></th>
            <th style="width:15%;"><?= __('Invalid date'); ?></th>
        </tr>
    </thead>

    <tr>
        <td colspan="4" class="table-secondary" style="font-weight:bold"><?= __('Invalid person dates:'); ?></td>
    </tr>
    <?php
    $person = $dbh->query("SELECT pers_gedcomnumber, pers_birth_date, pers_bapt_date, pers_death_date, pers_buried_date FROM humo_persons
        WHERE pers_tree_id='" . $tree_id . "' ORDER BY pers_lastname,pers_firstname");
    while ($persdateDb = $person->fetch()) {
        if (isset($persdateDb['pers_birth_date']) && $persdateDb['pers_birth_date'] != '') {
            $result = invalid($persdateDb['pers_birth_date'], $persdateDb['pers_gedcomnumber'], 'pers_birth_date');
        }
        if ($result === true) {
            $found = true;
        }
        if (isset($persdateDb['pers_bapt_date']) && $persdateDb['pers_bapt_date'] != '') {
            $result = invalid($persdateDb['pers_bapt_date'], $persdateDb['pers_gedcomnumber'], 'pers_bapt_date');
        }
        if ($result === true) {
            $found = true;
        }
        if (isset($persdateDb['pers_death_date']) && $persdateDb['pers_death_date'] != '') {
            $result = invalid($persdateDb['pers_death_date'], $persdateDb['pers_gedcomnumber'], 'pers_death_date');
        }
        if ($result === true) {
            $found = true;
        }
        if (isset($persdateDb['pers_buried_date']) && $persdateDb['pers_buried_date'] != '') {
            $result = invalid($persdateDb['pers_buried_date'], $persdateDb['pers_gedcomnumber'], 'pers_buried_date');
        }
        if ($result === true) {
            $found = true;
        }
    }

    if ($found === false) {
    ?>
        <tr>
            <td colspan="4" style="color:red"><?= __('No invalid dates found'); ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="4" class="table-secondary" style="font-weight:bold"><?= __('Invalid family dates:'); ?></td>
    </tr>
    <?php
    $found = false;
    $family = $dbh->query("SELECT fam_gedcomnumber, fam_div_date, fam_marr_church_date, fam_marr_church_notice_date, fam_marr_date, fam_marr_notice_date, fam_relation_date FROM humo_families WHERE fam_tree_id='" . $tree_id . "'");
    while ($famdateDb = $family->fetch()) {
        if (isset($famdateDb['fam_div_date']) && $famdateDb['fam_div_date'] != '') {
            $result = invalid($famdateDb['fam_div_date'], $famdateDb['fam_gedcomnumber'], 'fam_div_date');
        }
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_marr_church_date']) && $famdateDb['fam_marr_church_date'] != '') {
            $result = invalid($famdateDb['fam_marr_church_date'], $famdateDb['fam_gedcomnumber'], 'fam_marr_church_date');
        }
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_marr_church_notice_date']) && $famdateDb['fam_marr_church_notice_date'] != '') {
            $result = invalid($famdateDb['fam_marr_church_notice_date'], $famdateDb['fam_gedcomnumber'], 'fam_marr_church_notice_date');
        }
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_marr_date']) && $famdateDb['fam_marr_date'] != '') {
            $result = invalid($famdateDb['fam_marr_date'], $famdateDb['fam_gedcomnumber'], 'fam_marr_date');
        }
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_marr_notice_date']) && $famdateDb['fam_marr_notice_date'] != '') {
            $result = invalid($famdateDb['fam_marr_notice_date'], $famdateDb['fam_gedcomnumber'], 'fam_marr_notice_date');
        }
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_relation_date']) && $famdateDb['fam_relation_date'] != '') {
            $result = invalid($famdateDb['fam_relation_date'], $famdateDb['fam_gedcomnumber'], 'fam_relation_date');
        }
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) {
    ?>
        <tr>
            <td colspan="4" style="color:red"><?= __('No invalid dates found'); ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="4" class="table-secondary" style="font-weight:bold"><?= __('Invalid event dates:'); ?></td>
    </tr>
    <?php
    $found = false;
    $event = $dbh->query("SELECT event_id, event_date FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_date NOT LIKE ''");
    while ($eventdateDb = $event->fetch()) {
        $result = invalid($eventdateDb['event_date'], $eventdateDb['event_id'], 'event_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) {
    ?>
        <tr>
            <td colspan="4" style="color:red"><?= __('No invalid dates found'); ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="4" class="table-secondary" style="font-weight:bold"><?= __('Invalid connection dates:'); ?></td>
    </tr>
    <?php
    $found = false;
    $connection = $dbh->query("SELECT connect_id, connect_date FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_date NOT LIKE ''");
    while ($connectdateDb = $connection->fetch()) {
        $result = invalid($connectdateDb['connect_date'], $connectdateDb['connect_id'], 'connect_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) {
    ?>
        <tr>
            <td colspan="4" style="color:red"><?= __('No invalid dates found'); ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="4" class="table-secondary" style="font-weight:bold"><?= __('Invalid address dates:'); ?></td>
    </tr>
    <?php
    $found = false;
    $address = $dbh->query("SELECT address_id, address_date FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_date NOT LIKE ''");
    while ($addressdateDb = $address->fetch()) {
        $result = invalid($addressdateDb['address_date'], $addressdateDb['address_id'], 'address_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) {
    ?>
        <tr>
            <td colspan="4" style="color:red"><?= __('No invalid dates found'); ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="4" class="table-secondary" style="font-weight:bold"><?= __('Invalid repository dates:'); ?></td>
    </tr>
    <?php
    $found = false;
    $repo = $dbh->query("SELECT repo_gedcomnr, repo_date FROM humo_repositories WHERE repo_tree_id='" . $tree_id . "' AND repo_date NOT LIKE ''");
    while ($repodateDb = $repo->fetch()) {
        $result = invalid($repodateDb['repo_date'], $repodateDb['repo_gedcomnr'], 'repo_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) {
    ?>
        <tr>
            <td colspan="4" style="color:red"><?= __('No invalid dates found'); ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="4" class="table-secondary" style="font-weight:bold"><?= __('Invalid source dates:'); ?></td>
    </tr>
    <?php
    $found = false;
    $sources = $dbh->query("SELECT source_gedcomnr, source_date FROM humo_sources WHERE source_tree_id='" . $tree_id . "' AND source_date NOT LIKE ''");
    while ($sourcedateDb = $sources->fetch()) {
        $result = invalid($sourcedateDb['source_date'], $sourcedateDb['source_gedcomnr'], 'source_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) {
    ?>
        <tr>
            <td colspan="4" style="color:red"><?= __('No invalid dates found'); ?></td>
        </tr>
    <?php } ?>
</table>

<?php
function invalid($date, $gednr, $table)
{  // checks validity with validate_cls.php and displays invalid dates and their details
    global $dbh, $db_functions, $tree_id, $dirmark1, $dirmark2;
    include_once(__DIR__ . '/../../include/validateDate.php');
    $process_date = new validateDate;
    $compare_date = $date;
    if (strpos($date, '/') > 0) { // check for combined julian/gregorian date entries like 1654/5 and check the first part
        $temp = explode('/', $date);
        $compare_date = $temp[0];
        // In case this was not a jul/greg case but an invalid date like: 30/Jun/1980 or 12/3/90 
        // then "$compare_date" will become 30/jun or 12/3 which is still invalid and will be found and listed.
        // For the list of invalid dates, we use "$date" so that the full invalid date (30/Jun/1980 or 12/3/90 etc.) is displayed.
        // Also, if a jul/greg date itself is invalid (3 january 1680/1, 31 FEB 1678/9) then the mistake will be found
        // in the first part and will be listed, while the list will display the original invalid full jul/greg date as we want.
    }

    if ($process_date->check_date(strtoupper($compare_date)) === null) { // invalid date
        if (substr($table, 0, 3) === "per") {
            $personDb = $db_functions->get_person($gednr);
            $name = $personDb->pers_firstname . ' ' . str_replace("_", " ", $personDb->pers_prefix . ' ' . $personDb->pers_lastname);
?>
            <tr>
                <td><?= $gednr; ?></td>
                <td><a href="../admin/index.php?page=editor&tree_id=<?= $tree_id; ?>&person=<?= $personDb->pers_gedcomnumber; ?>" target='_blank'><?= $name; ?></a></td>
                <td><?= $table; ?></td>
                <td><?= $dirmark2 . $date; ?></td>
            </tr>
        <?php
        }
        if (substr($table, 0, 3) === "fam") {
            $fam = $dbh->query("SELECT fam_man,fam_woman FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $gednr . "'");
            $famDb = $fam->fetch();

            $spouse1Db = $db_functions->get_person($famDb['fam_man']);
            $name1 = $spouse1Db->pers_firstname . ' ' . str_replace("_", " ", $spouse1Db->pers_prefix . ' ' . $spouse1Db->pers_lastname);

            $spouse2Db = $db_functions->get_person($famDb['fam_woman']);
            $name2 = $spouse2Db->pers_firstname . ' ' . str_replace("_", " ", $spouse2Db->pers_prefix . ' ' . $spouse2Db->pers_lastname);

            $spousegednr = $spouse1Db->pers_gedcomnumber;
            if ($spousegednr == '') {
                $spousegednr = $spouse2Db->pers_gedcomnumber;
            }
            $and = ' ' . __('and') . ' ';
            if ($spouse1Db->pers_gedcomnumber == '' || $spouse2Db->pers_gedcomnumber == '') {
                $and = '';
            }
        ?>
            <tr>
                <td><?= $gednr; ?></td>
                <td><a href="../admin/index.php?page=editor&tree_id=<?= $tree_id; ?>&person=<?= $spousegednr; ?>" target='_blank'><?= $name1 . $and . $name2; ?></a></td>
                <td><?= $table; ?></td>
                <td><?= $dirmark2 . $date; ?></td>
            </tr>
            <?php
        }
        if (substr($table, 0, 3) === "eve") {
            $ev = $dbh->query("SELECT * FROM humo_events WHERE event_id = '" . $gednr . "'");
            $evDb = $ev->fetch();
            if ($evDb['event_connect_kind'] == 'person' && $evDb['event_connect_id'] != '') {
                $persDb = $db_functions->get_person($evDb['event_connect_id']);
                $fullname = $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix . ' ' . $persDb->pers_lastname);
                $evdetail = $evDb['event_event'];
                if ($evdetail == '') {
                    $evdetail = $evDb['event_gedcom'];
                }
                if ($evdetail != '') {
                    $evdetail = ': ' . $evdetail;
                }
            ?>
                <tr>
                    <td><?= $persDb->pers_gedcomnumber; ?></td>
                    <td><a href="../admin/index.php?page=editor&tree_id=<?= $tree_id; ?>&person=<?= $persDb->pers_gedcomnumber; ?>" target='_blank'><?= $fullname; ?></a> (<?= __('Click events by person'); ?>)</td>
                    <td><?= $evDb['event_kind'] . $evdetail; ?></td>
                    <td><?= $dirmark2 . $date; ?></td>
                </tr>
            <?php
            } elseif ($evDb['event_connect_kind'] == 'family' && $evDb['event_connect_id'] != '') {
                $fam = $dbh->query("SELECT fam_gedcomnumber,fam_man,fam_woman FROM humo_families
                    WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $evDb['event_connect_id'] . "'");
                $famDb = $fam->fetch();

                $spouse1Db = $db_functions->get_person($famDb['fam_man']);
                $name1 = $spouse1Db->pers_firstname . ' ' . str_replace("_", " ", $spouse1Db->pers_prefix . ' ' . $spouse1Db->pers_lastname);

                $spouse2Db = $db_functions->get_person($famDb['fam_woman']);
                $name2 = $spouse2Db->pers_firstname . ' ' . str_replace("_", " ", $spouse2Db->pers_prefix . ' ' . $spouse2Db->pers_lastname);

                $fullname = $name1 . ' and ' . $name2;
                $spousegednr = $spouse1Db->pers_gedcomnumber;
                if ($spousegednr == '') {
                    $spousegednr = $spouse2Db->pers_gedcomnumber;
                }
                $evdetail = $evDb['event_event'];
                if ($evdetail == '') {
                    $evdetail = $evDb['event_gedcom'];
                }
                if ($evdetail != '') {
                    $evdetail = ': ' . $evdetail;
                }
            ?>
                <tr>
                    <td><?= $famDb['fam_gedcomnumber']; ?></td>
                    <td><a href="../admin/index.php?page=editor&tree_id=<?= $tree_id; ?>&person=<?= $spousegednr; ?>" target='_blank'><?= $fullname; ?></a> (<?= __('Click events by marriage'); ?>)</td>
                    <td><?= $evDb['event_kind'] . $evdetail; ?></td>
                    <td><?= $dirmark2 . $date; ?></td>
                </tr>
            <?php
            }
        }
        if (substr($table, 0, 3) === "con") {
            $connect = $dbh->query("SELECT * FROM humo_connections WHERE connect_id = '" . $gednr . "'");
            $connectDb = $connect->fetch();
            $name = '';
            if (substr($connectDb['connect_sub_kind'], 0, 3) === 'per') {
                $persDb = $db_functions->get_person($connectDb['connect_connect_id']);
                if (substr($connectDb['connect_sub_kind'], -6) === 'source') {
                    $name = '<a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $persDb->pers_gedcomnumber . '" target=\'_blank\'>' . $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix . ' ' . $persDb->pers_lastname) . '</a> (' . __('Click relevant person source') . ')';
                }
                if (substr($connectDb['connect_sub_kind'], -7) === 'address') {
                    $name = '<a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $persDb->pers_gedcomnumber . '" target=\'_blank\'>' . $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix . ' ' . $persDb->pers_lastname) . '</a> (' . __('Click addresses') . ')';
                }
                $gedcomnr = $persDb->pers_gedcomnumber;
            }
            if (substr($connectDb['connect_sub_kind'], 0, 3) === 'fam') {
                $fam = $dbh->query("SELECT fam_gedcomnumber,fam_man,fam_woman FROM humo_families
                    WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $connectDb['connect_connect_id'] . "'");
                $famDb = $fam->fetch();

                $spouse1Db = $db_functions->get_person($famDb['fam_man']);
                $name1 = $spouse1Db->pers_firstname . ' ' . str_replace("_", " ", $spouse1Db->pers_prefix . ' ' . $spouse1Db->pers_lastname);

                $spouse2Db = $db_functions->get_person($famDb['fam_woman']);
                $name2 = $spouse2Db->pers_firstname . ' ' . str_replace("_", " ", $spouse2Db->pers_prefix . ' ' . $spouse2Db->pers_lastname);

                $name = $name1 . ' and ' . $name2;
                $spousegednr = $spouse1Db->pers_gedcomnumber;
                if ($spousegednr == '') {
                    $spousegednr = $spouse2Db->pers_gedcomnumber;
                }
                if (substr($connectDb['connect_sub_kind'], -6) === 'source') {
                    $name = '<a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $spousegednr . '" target=\'_blank\'>' . $name . '</a> (' . __('Click relevant family source');
                }
                $gedcomnr = $famDb['fam_gedcomnumber'];
            }
            if (substr($connectDb['connect_sub_kind'], 0, 3) === 'eve') {
                $ev = $dbh->query("SELECT * FROM humo_events WHERE event_id ='" . $connectDb['connect_connect_id'] . "'");
                $evDb = $ev->fetch();
                if ($evDb['event_connect_kind'] == 'person' && $evDb['event_connect_id'] != '') {
                    $persDb = $db_functions->get_person($evDb['event_connect_id']);
                    $gednr = $persDb->pers_gedcomnumber; // for url string
                    $gedcomnr = $persDb->pers_gedcomnumber; // for first column
                    $name = $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix) . ' ' . $persDb->pers_lastname;
                }
                if ($evDb['event_connect_kind'] == 'family' && $evDb['event_connect_id'] != '') {
                    $fam = $dbh->query("SELECT fam_gedcomnumber,fam_man,fam_woman FROM humo_families
                        WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $evDb['event_connect_id'] . "'");
                    $famDb = $fam->fetch();

                    $spouse1Db = $db_functions->get_person($famDb['fam_man']);
                    $name1 = $spouse1Db->pers_firstname . ' ' . str_replace("_", " ", $spouse1Db->pers_prefix . ' ' . $spouse1Db->pers_lastname);

                    $spouse2Db = $db_functions->get_person($famDb['fam_woman']);
                    $name2 = $spouse2Db->pers_firstname . ' ' . str_replace("_", " ", $spouse2Db->pers_prefix . ' ' . $spouse2Db->pers_lastname);

                    $name = $name1 . ' and ' . $name2;
                    $gednr = $spouse1Db->pers_gedcomnumber;
                    if ($spousegednr == '') {
                        $spousegednr = $spouse2Db->pers_gedcomnumber;
                    }
                    $gedcomnr = $famDb['fam_gedcomnumber']; // for first column
                }
                if (substr($connectDb['connect_sub_kind'], -6) === 'source') {
                    $name = '<a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $gednr . '" target=\'_blank\'>' . $name . '</a> (' . __('Click relevant event source') . ')';
                }
            }
            ?>
            <tr>
                <td><?= $gedcomnr; ?></td>
                <td><?= $name; ?></td>
                <td><?= $connectDb['connect_sub_kind']; ?></td>
                <td><?= $dirmark2 . $date; ?></td>
            </tr>
            <?php
        }
        if (substr($table, 0, 3) === "add") {
            $addresses = $dbh->query("SELECT * FROM humo_addresses WHERE address_id = '" . $gednr . "' AND address_connect_sub_kind='person'");
            $addressesDb = $addresses->fetch();
            if ($addressesDb['address_connect_id'] != '') {
                $persDb = $db_functions->get_person($addressesDb['address_connect_id']);
                $name = $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix) . ' ' . $persDb->pers_lastname;
            ?>
                <tr>
                    <td><?= $persDb->pers_gedcomnumber; ?></td>
                    <td><a href="../admin/index.php?page=editor&tree_id=<?= $tree_id; ?>&person=<?= $persDb->pers_gedcomnumber; ?>" target='_blank'><?= $name; ?></a> (<?= __('Click addresses'); ?>)</td>
                    <td><?= $table; ?></td>
                    <td><?= $date; ?></td>
                </tr>
            <?php
            }
            if ($addressesDb['address_gedcomnr'] != '') {
            ?>
                <tr>
                    <td><?= $gednr; ?></td>
                    <td>
                        <a href="index.php?page=edit_addresses" target="_blank"><?= __('Address editor'); ?></a>
                        (<?= __('search for:'); ?> <?= $addressesDb['address_address']; ?>)
                    </td>
                    <td><?= $table; ?></td>
                    <td><?= $dirmark2 . $date; ?></td>
                </tr>
            <?php
            }
        }
        if (substr($table, 0, 3) === "sou") {
            $sourcesDb = $db_functions->get_source($gednr);
            ?>
            <tr>
                <td><?= $gednr; ?></td>
                <td>
                    <a href="index.php?page=edit_sources&amp;source_id=<?= $sourcesDb->source_gedcomnr; ?>" target=" _blank"><?= __('Source editor'); ?></a>
                    (<?= __('search for:'); ?> <?= $sourcesDb->source_title; ?>)
                </td>
                <td><?= $table; ?></td>
                <td><?= $dirmark2 . $date; ?></td>
            </tr>
        <?php
        }
        if (substr($table, 0, 3) === "rep") {
            $reposDb = $db_functions->get_repository($gednr);
        ?>
            <tr>
                <td><?= $gednr; ?></td>
                <td>
                    <a href="index.php?page=edit_repositories" target="_blank"><?= __('Repository editor'); ?></a>
                    (<?= __('search for:'); ?> <?= $reposDb->repo_name; ?>)
                </td>
                <td><?= $table; ?></td>
                <td><?= $dirmark2 . $date; ?></td>
            </tr>
<?php
        }
        return true;  // found invalid date
    }
    return false; // did not find invalid date
}
