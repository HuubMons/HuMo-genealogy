<?php
$limit = 2500;

ob_start();
printf(__('Remove old family tree items from %s table...'), 'humo_persons');
echo ' ';
ob_flush();
flush();
$total = $dbh->query("SELECT COUNT(*) FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "'");
$total = $total->fetch();
$nr_records = $total[0];
if ($nr_records > 0) {
    $loop = $nr_records / $limit;
    for ($i = 0; $i <= $loop; $i++) {
        $dbh->query("DELETE FROM humo_persons WHERE pers_tree_id='" . safe_text_db($trees['tree_id']) . "' LIMIT " . $limit);
        echo '*';
        ob_flush();
        flush();
    }
    $dbh->query("DELETE FROM humo_persons WHERE pers_tree_id='" . safe_text_db($trees['tree_id']) . "'");

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_persons");
}
echo '<br>';

// *** Remove records in chunks because of InnoDb database... ***
printf(__('Remove old family tree items from %s table...'), 'humo_families');
echo ' ';
$total = $dbh->query("SELECT COUNT(*) FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "'");
$total = $total->fetch();
$nr_records = $total[0];
if ($nr_records > 0) {
    $loop = $nr_records / $limit;
    for ($i = 0; $i <= $loop; $i++) {
        $dbh->query("DELETE FROM humo_families WHERE fam_tree_id='" . safe_text_db($trees['tree_id']) . "' LIMIT " . $limit);
        echo '*';
        ob_flush();
        flush();
    }
    $dbh->query("DELETE FROM humo_families WHERE fam_tree_id='" . safe_text_db($trees['tree_id']) . "'");

    echo ' ' . __('Optimize table...') . ' ';
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_families");
}
echo '<br>';

// *** Remove records in chunks because of InnoDb database... ***
printf(__('Remove old family tree items from %s table...'), 'humo_unprocessed_tags');
echo ' ';
ob_flush();
flush();
$total = $dbh->query("SELECT COUNT(*) FROM humo_unprocessed_tags WHERE tag_tree_id='" . $trees['tree_id'] . "'");
$total = $total->fetch();
$nr_records = $total[0];
if ($nr_records > 0) {
    $loop = $nr_records / $limit;
    for ($i = 0; $i <= $loop; $i++) {
        $dbh->query("DELETE FROM humo_unprocessed_tags WHERE tag_tree_id='" . safe_text_db($trees['tree_id']) . "' LIMIT " . $limit);
        echo '*';
        ob_flush();
        flush();
    }
    $dbh->query("DELETE FROM humo_unprocessed_tags WHERE tag_tree_id='" . safe_text_db($trees['tree_id']) . "'");

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_unprocessed_tags");
}
echo '<br>';


// *** Remove admin favourites ***
printf(__('Remove old family tree items from %s table...'), 'humo_settings');
ob_flush();
flush();
$dbh->query("DELETE FROM humo_settings WHERE setting_variable='admin_favourite' AND setting_tree_id='" . safe_text_db($trees['tree_id']) . "'");

echo ' ' . __('Optimize table...');
ob_flush();
flush();
$dbh->query("OPTIMIZE TABLE humo_settings");
echo '<br>';

// *** Remove repositories ***
printf(__('Remove old family tree items from %s table...'), 'humo_repositories');
ob_flush();
flush();
$dbh->query("DELETE FROM humo_repositories WHERE repo_tree_id='" . safe_text_db($trees['tree_id']) . "'");

echo ' ' . __('Optimize table...');
ob_flush();
flush();
$dbh->query("OPTIMIZE TABLE humo_repositories");
echo '<br>';

// *** Remove records in chunks because of InnoDb database... ***
printf(__('Remove old family tree items from %s table...'), 'humo_sources');
echo ' ';
ob_flush();
flush();
$total = $dbh->query("SELECT COUNT(*) FROM humo_sources WHERE source_tree_id='" . $trees['tree_id'] . "'");
$total = $total->fetch();
$nr_records = $total[0];
if ($nr_records > 0) {
    $loop = $nr_records / $limit;
    for ($i = 0; $i <= $loop; $i++) {
        $dbh->query("DELETE FROM humo_sources WHERE source_tree_id='" . safe_text_db($trees['tree_id']) . "' LIMIT " . $limit);
        echo '*';
        ob_flush();
        flush();
    }
    $dbh->query("DELETE FROM humo_sources WHERE source_tree_id='" . safe_text_db($trees['tree_id']) . "'");

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_sources");
}
echo '<br>';


// *** Remove records in chunks because of InnoDb database... ***
printf(__('Remove old family tree items from %s table...'), 'humo_texts');
echo ' ';
ob_flush();
flush();
$total = $dbh->query("SELECT COUNT(*) FROM humo_texts WHERE text_tree_id='" . $trees['tree_id'] . "'");
$total = $total->fetch();
$nr_records = $total[0];
if ($nr_records > 0) {
    $loop = $nr_records / $limit;
    for ($i = 0; $i <= $loop; $i++) {
        $dbh->query("DELETE FROM humo_texts WHERE text_tree_id='" . safe_text_db($trees['tree_id']) . "' LIMIT " . $limit);
        echo '*';
        ob_flush();
        flush();
    }
    $dbh->query("DELETE FROM humo_texts WHERE text_tree_id='" . safe_text_db($trees['tree_id']) . "'");

    echo ' ' . __('Optimize table...');
    $dbh->query("OPTIMIZE TABLE humo_texts");
}
echo '<br>';


// *** Remove records in chunks because of InnoDb database... ***
printf(__('Remove old family tree items from %s table...'), 'humo_connections');
echo ' ';
ob_flush();
flush();
$total = $dbh->query("SELECT COUNT(*) FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "'");
$total = $total->fetch();
$nr_records = $total[0];
if ($nr_records > 0) {
    $loop = $nr_records / $limit;
    for ($i = 0; $i <= $loop; $i++) {
        $dbh->query("DELETE FROM humo_connections WHERE connect_tree_id='" . safe_text_db($trees['tree_id']) . "' LIMIT " . $limit);
        echo '*';
        ob_flush();
        flush();
    }
    $dbh->query("DELETE FROM humo_connections WHERE connect_tree_id='" . safe_text_db($trees['tree_id']) . "'");

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_connections");
}
echo '<br>';


// *** Remove records in chunks because of InnoDb database... ***
printf(__('Remove old family tree items from %s table...'), 'humo_addresses');
echo ' ';
ob_flush();
flush();
$total = $dbh->query("SELECT COUNT(*) FROM humo_addresses WHERE address_tree_id='" . $trees['tree_id'] . "'");
$total = $total->fetch();
$nr_records = $total[0];
if ($nr_records > 0) {
    $loop = $nr_records / $limit;
    for ($i = 0; $i <= $loop; $i++) {
        $dbh->query("DELETE FROM humo_addresses WHERE address_tree_id='" . safe_text_db($trees['tree_id']) . "' LIMIT " . $limit);
        echo '*';
        ob_flush();
        flush();
    }
    $dbh->query("DELETE FROM humo_addresses WHERE address_tree_id='" . safe_text_db($trees['tree_id']) . "'");

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_addresses");
}
echo '<br>';

// *** Remove records in chunks because of InnoDb database... ***
printf(__('Remove old family tree items from %s table...'), 'humo_events');
echo ' ';
ob_flush();
flush();
$total = $dbh->query("SELECT COUNT(*) FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "'");
$total = $total->fetch();
$nr_records = $total[0];
if ($nr_records > 0) {
    $loop = $nr_records / $limit;
    for ($i = 0; $i <= $loop; $i++) {
        if ($humo_option["gedcom_read_save_pictures"] === 'y') {
            $sql = "DELETE FROM humo_events WHERE event_tree_id='" . safe_text_db($trees['tree_id']) . "' AND event_kind!='picture' LIMIT " . $limit;
        } else {
            $sql = "DELETE FROM humo_events WHERE event_tree_id='" . safe_text_db($trees['tree_id']) . "' LIMIT " . $limit;
        }

        $dbh->query($sql);
        echo '*';
        ob_flush();
        flush();
    }
    if ($humo_option["gedcom_read_save_pictures"] === 'y') {
        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . safe_text_db($trees['tree_id']) . "' AND event_kind!='picture'";
    } else {
        $sql = "DELETE FROM humo_events WHERE event_tree_id='" . safe_text_db($trees['tree_id']) . "'";
    }
    $dbh->query($sql);

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_events");
}
echo '<br>';

if (isset($show_gedcom_status)) {
    echo '<b>' . __('No error messages above? In that case the tables have been created!') . '</b><br>';
}
