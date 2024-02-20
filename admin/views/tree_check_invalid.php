<?php
$found = false; // if this stays false, displays message that no problems where found

// displays results of validity check (with help of the invalid() function)
?>
<table class="humo" style="width:100%">
    <tr>
        <th style="width:10%;border:1px solid black"><?= __('ID'); ?></th>
        <th style="width:55%;border:1px solid black"><?= __('Edit invalid date'); ?></th>
        <th style="width:20%;border:1px solid black"><?= __('Details'); ?></th>
        <th style="width:15%;border:1px solid black"><?= __('Invalid date'); ?></th>
    </tr>

<?php
    echo '<tr><td colspan="4" style="text-align:' . $direction . ';font-weight:bold">' . __('Invalid person dates:') . '</td></tr>';

    $person = $dbh->query("SELECT pers_gedcomnumber, pers_birth_date, pers_bapt_date, pers_death_date, pers_buried_date FROM humo_persons
    WHERE pers_tree_id='" . $tree_id . "' ORDER BY pers_lastname,pers_firstname");
    while ($persdateDb = $person->fetch()) {
    if (isset($persdateDb['pers_birth_date']) and $persdateDb['pers_birth_date'] != '')
        $result = invalid($persdateDb['pers_birth_date'], $persdateDb['pers_gedcomnumber'], 'pers_birth_date');
    if ($result === true) {
        $found = true;
    }
    if (isset($persdateDb['pers_bapt_date']) and $persdateDb['pers_bapt_date'] != '')
        $result = invalid($persdateDb['pers_bapt_date'], $persdateDb['pers_gedcomnumber'], 'pers_bapt_date');
    if ($result === true) {
        $found = true;
    }
    if (isset($persdateDb['pers_death_date']) and $persdateDb['pers_death_date'] != '')
        $result = invalid($persdateDb['pers_death_date'], $persdateDb['pers_gedcomnumber'], 'pers_death_date');
    if ($result === true) {
        $found = true;
    }
    if (isset($persdateDb['pers_buried_date']) and $persdateDb['pers_buried_date'] != '')
        $result = invalid($persdateDb['pers_buried_date'], $persdateDb['pers_gedcomnumber'], 'pers_buried_date');
    if ($result === true) {
        $found = true;
    }
}

    if ($found === false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

    echo '<tr><td colspan="4" style="text-align:' . $direction . ';font-weight:bold">' . __('Invalid family dates:') . '</td></tr>';
    $found = false;
    $family = $dbh->query("SELECT fam_gedcomnumber, fam_div_date, fam_marr_church_date, fam_marr_church_notice_date, fam_marr_date, fam_marr_notice_date, fam_relation_date FROM humo_families WHERE fam_tree_id='" . $tree_id . "'");
    while ($famdateDb = $family->fetch()) {
        if (isset($famdateDb['fam_div_date']) and $famdateDb['fam_div_date'] != '')
            $result = invalid($famdateDb['fam_div_date'], $famdateDb['fam_gedcomnumber'], 'fam_div_date');
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_marr_church_date']) and $famdateDb['fam_marr_church_date'] != '')
            $result = invalid($famdateDb['fam_marr_church_date'], $famdateDb['fam_gedcomnumber'], 'fam_marr_church_date');
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_marr_church_notice_date']) and $famdateDb['fam_marr_church_notice_date'] != '')
            $result = invalid($famdateDb['fam_marr_church_notice_date'], $famdateDb['fam_gedcomnumber'], 'fam_marr_church_notice_date');
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_marr_date']) and $famdateDb['fam_marr_date'] != '')
            $result = invalid($famdateDb['fam_marr_date'], $famdateDb['fam_gedcomnumber'], 'fam_marr_date');
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_marr_notice_date']) and $famdateDb['fam_marr_notice_date'] != '')
            $result = invalid($famdateDb['fam_marr_notice_date'], $famdateDb['fam_gedcomnumber'], 'fam_marr_notice_date');
        if ($result === true) {
            $found = true;
        }
        if (isset($famdateDb['fam_relation_date']) and $famdateDb['fam_relation_date'] != '')
            $result = invalid($famdateDb['fam_relation_date'], $famdateDb['fam_gedcomnumber'], 'fam_relation_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

    echo '<tr><td colspan="4" style="text-align:' . $direction . ';font-weight:bold">' . __('Invalid event dates:') . '</td></tr>';
    $found = false;
    $event = $dbh->query("SELECT event_id, event_date FROM humo_events WHERE event_tree_id='" . $tree_id . "' AND event_date NOT LIKE ''");
    while ($eventdateDb = $event->fetch()) {
        $result = invalid($eventdateDb['event_date'], $eventdateDb['event_id'], 'event_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

    echo '<tr><td colspan="4" style="text-align:' . $direction . ';font-weight:bold">' . __('Invalid connection dates:') . '</td></tr>';
    $found = false;
    $connection = $dbh->query("SELECT connect_id, connect_date FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' AND connect_date NOT LIKE ''");
    while ($connectdateDb = $connection->fetch()) {
        $result = invalid($connectdateDb['connect_date'], $connectdateDb['connect_id'], 'connect_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

    echo '<tr><td colspan="4" style="text-align:' . $direction . ';font-weight:bold">' . __('Invalid address dates:') . '</td></tr>';
    $found = false;
    $address = $dbh->query("SELECT address_id, address_date FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_date NOT LIKE ''");
    while ($addressdateDb = $address->fetch()) {
        $result = invalid($addressdateDb['address_date'], $addressdateDb['address_id'], 'address_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

    echo '<tr><td colspan="4" style="text-align:' . $direction . ';font-weight:bold">' . __('Invalid repository dates:') . '</td></tr>';
    $found = false;
    $repo = $dbh->query("SELECT repo_gedcomnr, repo_date FROM humo_repositories WHERE repo_tree_id='" . $tree_id . "' AND repo_date NOT LIKE ''");
    while ($repodateDb = $repo->fetch()) {
        $result = invalid($repodateDb['repo_date'], $repodateDb['repo_gedcomnr'], 'repo_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';

    echo '<tr><td colspan="4" style="text-align:' . $direction . ';font-weight:bold">' . __('Invalid source dates:') . '</td></tr>';
    $found = false;
    $sources = $dbh->query("SELECT source_gedcomnr, source_date FROM humo_sources WHERE source_tree_id='" . $tree_id . "' AND source_date NOT LIKE ''");
    while ($sourcedateDb = $sources->fetch()) {
        $result = invalid($sourcedateDb['source_date'], $sourcedateDb['source_gedcomnr'], 'source_date');
        if ($result === true) {
            $found = true;
        }
    }
    if ($found === false) echo '<tr><td colspan=4 style="color:red">No invalid dates found</td></tr>';
    ?>
</table>
<?php


function invalid($date, $gednr, $table)
{  // checks validity with validate_cls.php and displays invalid dates and their details
    global $dbh, $db_functions, $tree_id, $direction, $dirmark1, $dirmark2;
    include_once(__DIR__ . '/../../include/validate_date_cls.php');
    $process_date = new validate_date_cls;
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
        if (substr($table, 0, 3) == "per") {
            $personDb = $db_functions->get_person($gednr);
            $name = $personDb->pers_firstname . ' ' . str_replace("_", " ", $personDb->pers_prefix . ' ' . $personDb->pers_lastname);
            echo '<tr><td style="text-align:' . $direction . '">' . $gednr . '</td><td style="text-align:' . $direction . '"><a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $personDb->pers_gedcomnumber . '" target=\'_blank\'>' . $name . '</a></td><td style="text-align:' . $direction . '">' . $table . '</td><td style="text-align:' . $direction . '">' . $dirmark2 . $date . '</td></tr>';
        }
        if (substr($table, 0, 3) == "fam") {
            $fam = $dbh->query("SELECT fam_man,fam_woman FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $gednr . "'");
            $famDb = $fam->fetch();

            $spouse1Db = $db_functions->get_person($famDb['fam_man']);
            $name1 = $spouse1Db->pers_firstname . ' ' . str_replace("_", " ", $spouse1Db->pers_prefix . ' ' . $spouse1Db->pers_lastname);

            $spouse2Db = $db_functions->get_person($famDb['fam_woman']);
            $name2 = $spouse2Db->pers_firstname . ' ' . str_replace("_", " ", $spouse2Db->pers_prefix . ' ' . $spouse2Db->pers_lastname);

            $spousegednr = $spouse1Db->pers_gedcomnumber;
            if ($spousegednr == '') $spousegednr = $spouse2Db->pers_gedcomnumber;
            $and = ' ' . __('and') . ' ';
            if ($spouse1Db->pers_gedcomnumber == '' or $spouse2Db->pers_gedcomnumber == '') $and = '';
            echo '<tr><td style="text-align:' . $direction . '">' . $gednr . '</td><td style="text-align:' . $direction . '"><a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $spousegednr . '" target=\'_blank\'>' . $name1 . $and . $name2 . '</a></td><td style="text-align:' . $direction . '">' . $table . '</td><td style="text-align:' . $direction . '">' . $dirmark2 . $date . '</td></tr>';
        }
        if (substr($table, 0, 3) == "eve") {
            $ev = $dbh->query("SELECT * FROM humo_events WHERE event_id = '" . $gednr . "'");
            $evDb = $ev->fetch();
            if ($evDb['event_connect_kind'] == 'person' and $evDb['event_connect_id'] != '') {
                $persDb = $db_functions->get_person($evDb['event_connect_id']);
                $fullname = $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix . ' ' . $persDb->pers_lastname);
                $evdetail = $evDb['event_event'];
                if ($evdetail == '') $evdetail = $evDb['event_gedcom'];
                if ($evdetail != '') $evdetail = ': ' . $evdetail;
                echo '<tr><td style="text-align:' . $direction . '">' . $persDb->pers_gedcomnumber . '</td><td style="text-align:' . $direction . '"><a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $persDb->pers_gedcomnumber . '" target=\'_blank\'>' . $fullname . '</a> (' . __('Click events by person') . ')</td><td style="text-align:' . $direction . '">' . $evDb['event_kind'] . $evdetail . '</td><td style="text-align:' . $direction . '">' . $dirmark2 . $date . '</td></tr>';
            } elseif ($evDb['event_connect_kind'] == 'family' and $evDb['event_connect_id'] != '') {
                $fam = $dbh->query("SELECT fam_gedcomnumber,fam_man,fam_woman FROM humo_families
                WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $evDb['event_connect_id'] . "'");
                $famDb = $fam->fetch();

                $spouse1Db = $db_functions->get_person($famDb['fam_man']);
                $name1 = $spouse1Db->pers_firstname . ' ' . str_replace("_", " ", $spouse1Db->pers_prefix . ' ' . $spouse1Db->pers_lastname);

                $spouse2Db = $db_functions->get_person($famDb['fam_woman']);
                $name2 = $spouse2Db->pers_firstname . ' ' . str_replace("_", " ", $spouse2Db->pers_prefix . ' ' . $spouse2Db->pers_lastname);

                $fullname = $name1 . ' and ' . $name2;
                $spousegednr = $spouse1Db->pers_gedcomnumber;
                if ($spousegednr == '') $spousegednr = $spouse2Db->pers_gedcomnumber;
                $evdetail = $evDb['event_event'];
                if ($evdetail == '') $evdetail = $evDb['event_gedcom'];
                if ($evdetail != '') $evdetail = ': ' . $evdetail;
                echo '<tr><td style="text-align:' . $direction . '">' . $famDb['fam_gedcomnumber'] . '</td><td style="text-align:' . $direction . '"><a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $spousegednr . '" target=\'_blank\'>' . $fullname . '</a> (' . __('Click events by marriage') . ')</td><td style="text-align:' . $direction . '">' . $evDb['event_kind'] . $evdetail . '</td><td style="text-align:' . $direction . '">' . $dirmark2 . $date . '</td></tr>';
            }
        }
        if (substr($table, 0, 3) == "con") {
            $connect = $dbh->query("SELECT * FROM humo_connections WHERE connect_id = '" . $gednr . "'");
            $connectDb = $connect->fetch();
            $name = '';
            if (substr($connectDb['connect_sub_kind'], 0, 3) == 'per') {
                $persDb = $db_functions->get_person($connectDb['connect_connect_id']);
                if (substr($connectDb['connect_sub_kind'], -6) == 'source') {
                    $name = '<a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $persDb->pers_gedcomnumber . '" target=\'_blank\'>' . $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix . ' ' . $persDb->pers_lastname) . '</a> (' . __('Click relevant person source') . ')';
                }
                if (substr($connectDb['connect_sub_kind'], -7) == 'address') {
                    $name = '<a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $persDb->pers_gedcomnumber . '" target=\'_blank\'>' . $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix . ' ' . $persDb->pers_lastname) . '</a> (' . __('Click addresses') . ')';
                }
                $gedcomnr = $persDb->pers_gedcomnumber;
            }
            if (substr($connectDb['connect_sub_kind'], 0, 3) == 'fam') {
                $fam = $dbh->query("SELECT fam_gedcomnumber,fam_man,fam_woman FROM humo_families
                WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $connectDb['connect_connect_id'] . "'");
                $famDb = $fam->fetch();

                $spouse1Db = $db_functions->get_person($famDb['fam_man']);
                $name1 = $spouse1Db->pers_firstname . ' ' . str_replace("_", " ", $spouse1Db->pers_prefix . ' ' . $spouse1Db->pers_lastname);

                $spouse2Db = $db_functions->get_person($famDb['fam_woman']);
                $name2 = $spouse2Db->pers_firstname . ' ' . str_replace("_", " ", $spouse2Db->pers_prefix . ' ' . $spouse2Db->pers_lastname);

                $name = $name1 . ' and ' . $name2;
                $spousegednr = $spouse1Db->pers_gedcomnumber;
                if ($spousegednr == '') $spousegednr = $spouse2Db->pers_gedcomnumber;
                if (substr($connectDb['connect_sub_kind'], -6) == 'source') {
                    $name = '<a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $spousegednr . '" target=\'_blank\'>' . $name . '</a> (' . __('Click relevant family source');
                }
                $gedcomnr = $famDb['fam_gedcomnumber'];
            }
            if (substr($connectDb['connect_sub_kind'], 0, 3) == 'eve') {
                $ev = $dbh->query("SELECT * FROM humo_events WHERE event_id ='" . $connectDb['connect_connect_id'] . "'");
                $evDb = $ev->fetch();
                if ($evDb['event_connect_kind'] == 'person' and $evDb['event_connect_id'] != '') {
                    $persDb = $db_functions->get_person($evDb['event_connect_id']);
                    $gednr = $persDb->pers_gedcomnumber; // for url string
                    $gedcomnr = $persDb->pers_gedcomnumber; // for first column
                    $name = $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix) . ' ' . $persDb->pers_lastname;
                }
                if ($evDb['event_connect_kind'] == 'family' and $evDb['event_connect_id'] != '') {
                    $fam = $dbh->query("SELECT fam_gedcomnumber,fam_man,fam_woman FROM humo_families
                    WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber = '" . $evDb['event_connect_id'] . "'");
                    $famDb = $fam->fetch();

                    $spouse1Db = $db_functions->get_person($famDb['fam_man']);
                    $name1 = $spouse1Db->pers_firstname . ' ' . str_replace("_", " ", $spouse1Db->pers_prefix . ' ' . $spouse1Db->pers_lastname);

                    $spouse2Db = $db_functions->get_person($famDb['fam_woman']);
                    $name2 = $spouse2Db->pers_firstname . ' ' . str_replace("_", " ", $spouse2Db->pers_prefix . ' ' . $spouse2Db->pers_lastname);

                    $name = $name1 . ' and ' . $name2;
                    $gednr = $spouse1Db->pers_gedcomnumber;
                    if ($spousegednr == '') $spousegednr = $spouse2Db->pers_gedcomnumber;
                    $gedcomnr = $famDb['fam_gedcomnumber']; // for first column
                }
                if (substr($connectDb['connect_sub_kind'], -6) == 'source') {
                    $name = '<a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $gednr . '" target=\'_blank\'>' . $name . '</a> (' . __('Click relevant event source') . ')';
                }
            }
            echo '<tr><td style="text-align:' . $direction . '">' . $gedcomnr . '</td><td style="text-align:' . $direction . '">' . $name . '</td><td style="text-align:' . $direction . '">' . $connectDb['connect_sub_kind'] . '</td><td style="text-align:' . $direction . '">' . $dirmark2 . $date . '</td></tr>';
        }
        if (substr($table, 0, 3) == "add") {
            $addresses = $dbh->query("SELECT * FROM humo_addresses WHERE address_id = '" . $gednr . "' AND address_connect_sub_kind='person'");
            $addressesDb = $addresses->fetch();
            if ($addressesDb['address_connect_id'] != '') {
                $persDb = $db_functions->get_person($addressesDb['address_connect_id']);
                $name = $persDb->pers_firstname . ' ' . str_replace("_", " ", $persDb->pers_prefix) . ' ' . $persDb->pers_lastname;
                echo '<tr><td style="text-align:' . $direction . '">' . $persDb->pers_gedcomnumber . '</td><td style="text-align:' . $direction . '"><a href="../admin/index.php?page=editor&tree_id=' . $tree_id . '&person=' . $persDb->pers_gedcomnumber . '" target=\'_blank\'>' . $name . '</a> (' . __('Click addresses') . ')</td><td style="text-align:' . $direction . '">' . $table . '</td><td style="text-align:' . $direction . '">' . $date . '</td></tr>';
            }
            if ($addressesDb['address_gedcomnr'] != '') {
                $second_column = '<a href="index.php?page=edit_addresses" target=\'_blank\'>' . __('Address editor') . '</a> (Search for: ' . $addressesDb['address_address'] . ')';
                echo '<tr><td style="text-align:' . $direction . '">' . $gednr . '</td><td style="text-align:' . $direction . '">' . $second_column . '</td><td style="text-align:' . $direction . '">' . $table . '</td><td style="text-align:' . $direction . '">' . $dirmark2 . $date . '</td></tr>';
            }
        }
        if (substr($table, 0, 3) == "sou") {
            $sourcesDb = $db_functions->get_source($gednr);
            echo '<tr><td style="text-align:' . $direction . '">' . $gednr . '</td><td style="text-align:' . $direction . '">' . '<a href="index.php?page=edit_sources" target=\'_blank\'>' . __('Source editor') . '</a> (Search for: ' . $sourcesDb->source_title . ')</td><td style="text-align:' . $direction . '">' . $table . '</td><td style="text-align:' . $direction . '">' . $dirmark2 . $date . '</td></tr>';
        }
        if (substr($table, 0, 3) == "rep") {
            $reposDb = $db_functions->get_repository($gednr);
            echo '<tr><td style="text-align:' . $direction . '">' . $gednr . '</td><td style="text-align:' . $direction . '">' . '<a href="index.php?page=edit_repositories" target=\'_blank\'>' . __('Repository editor') . '</a> (Search for: ' . $reposDb->repo_name . ')</td><td style="text-align:' . $direction . '">' . $table . '</td><td style="text-align:' . $direction . '">' . $dirmark2 . $date . '</td></tr>';
        }
        return true;  // found invalid date
    }
    return false; // did not find invalid date
}
