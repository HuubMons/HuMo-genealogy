<?php
$limit = 2500;

// ob_start(); // Doesn't work. The statusbar is not updated in the browser.
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
        $stmt = $dbh->prepare("DELETE FROM humo_persons WHERE pers_tree_id=:tree_id LIMIT " . $limit);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
        echo '*';
        ob_flush();
        flush();
    }
    $stmt = $dbh->prepare("DELETE FROM humo_persons WHERE pers_tree_id=:tree_id");
    $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
    $stmt->execute();

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_persons");
}

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 10;

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
        $stmt = $dbh->prepare("DELETE FROM humo_families WHERE fam_tree_id=:tree_id LIMIT " . $limit);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
        echo '*';
        ob_flush();
        flush();
    }
    $stmt = $dbh->prepare("DELETE FROM humo_families WHERE fam_tree_id=:tree_id");
    $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
    $stmt->execute();

    echo ' ' . __('Optimize table...') . ' ';
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_families");
}

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 20;

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
        $stmt = $dbh->prepare("DELETE FROM humo_unprocessed_tags WHERE tag_tree_id=:tree_id LIMIT " . $limit);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
        echo '*';
        ob_flush();
        flush();
    }
    $stmt = $dbh->prepare("DELETE FROM humo_unprocessed_tags WHERE tag_tree_id=:tree_id");
    $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
    $stmt->execute();

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_unprocessed_tags");
}

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 30;

echo '<br>';


// *** Remove admin favourites ***
printf(__('Remove old family tree items from %s table...'), 'humo_settings');
ob_flush();
flush();
$stmt = $dbh->prepare("DELETE FROM humo_settings WHERE setting_variable = :setting_variable AND setting_tree_id = :tree_id");
$stmt->bindValue(':setting_variable', 'admin_favourite', PDO::PARAM_STR);
$stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
$stmt->execute();

echo ' ' . __('Optimize table...');
ob_flush();
flush();
$dbh->query("OPTIMIZE TABLE humo_settings");

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 40;

echo '<br>';

// *** Remove repositories ***
printf(__('Remove old family tree items from %s table...'), 'humo_repositories');
ob_flush();
flush();
$stmt = $dbh->prepare("DELETE FROM humo_repositories WHERE repo_tree_id = :tree_id");
$stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
$stmt->execute();

echo ' ' . __('Optimize table...');
ob_flush();
flush();
$dbh->query("OPTIMIZE TABLE humo_repositories");

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 50;

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
        $stmt = $dbh->prepare("DELETE FROM humo_sources WHERE source_tree_id = :tree_id LIMIT " . $limit);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
        echo '*';
        ob_flush();
        flush();
    }
    $stmt = $dbh->prepare("DELETE FROM humo_sources WHERE source_tree_id = :tree_id");
    $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
    $stmt->execute();

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_sources");
}

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 60;

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
        $stmt = $dbh->prepare("DELETE FROM humo_texts WHERE text_tree_id = :tree_id LIMIT " . $limit);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
        echo '*';
        ob_flush();
        flush();
    }
    $stmt = $dbh->prepare("DELETE FROM humo_texts WHERE text_tree_id = :tree_id");
    $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
    $stmt->execute();

    echo ' ' . __('Optimize table...');
    $dbh->query("OPTIMIZE TABLE humo_texts");
}

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 70;

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
        $stmt = $dbh->prepare("DELETE FROM humo_connections WHERE connect_tree_id = :tree_id LIMIT " . $limit);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
        echo '*';
        ob_flush();
        flush();
    }
    $stmt = $dbh->prepare("DELETE FROM humo_connections WHERE connect_tree_id = :tree_id");
    $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
    $stmt->execute();

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_connections");
}

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 80;

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
        $stmt = $dbh->prepare("DELETE FROM humo_addresses WHERE address_tree_id = :tree_id LIMIT " . $limit);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
        echo '*';
        ob_flush();
        flush();
    }
    $stmt = $dbh->prepare("DELETE FROM humo_addresses WHERE address_tree_id = :tree_id");
    $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
    $stmt->execute();

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_addresses");
}

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 90;

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
            $sql = "DELETE FROM humo_events WHERE event_tree_id = :tree_id AND event_kind != 'picture' LIMIT " . $limit;
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $sql = "DELETE FROM humo_events WHERE event_tree_id = :tree_id LIMIT " . $limit;
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
            $stmt->execute();
        }

        echo '*';
        ob_flush();
        flush();
    }
    if ($humo_option["gedcom_read_save_pictures"] === 'y') {
        $sql = "DELETE FROM humo_events WHERE event_tree_id = :tree_id AND event_kind != 'picture'";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $sql = "DELETE FROM humo_events WHERE event_tree_id = :tree_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':tree_id', $trees['tree_id'], PDO::PARAM_STR);
        $stmt->execute();
    }

    echo ' ' . __('Optimize table...');
    ob_flush();
    flush();
    $dbh->query("OPTIMIZE TABLE humo_events");
}

// *** Update progressbar ***
$_SESSION['save_import_progress'] = 100;

echo '<br>';

if (isset($show_gedcom_status)) {
    echo '<b>' . __('No error messages above? In that case the tables have been created!') . '</b><br>';
}
