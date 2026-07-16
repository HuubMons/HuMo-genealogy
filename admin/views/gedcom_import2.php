<?php

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$limit = 2500;

function removeTreeRecords($dbh, $table, $tree_field, $tree_id, $limit = 2500, $extra_where = '', $optimize = true)
{
    printf(__('Remove old family tree items from %s table...'), $table);
    echo ' ';
    ob_flush();
    flush();

    $total = $dbh->query("SELECT COUNT(*) FROM $table WHERE $tree_field = '$tree_id' $extra_where");
    $nr_records = $total->fetchColumn();
    if ($nr_records > 0) {
        $loop = ceil($nr_records / $limit);
        for ($i = 0; $i < $loop; $i++) {
            $sql = "DELETE FROM $table WHERE $tree_field = :tree_id $extra_where LIMIT $limit";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
            $stmt->execute();
            echo '*';
            ob_flush();
            flush();
        }
        // Final cleanup
        $sql = "DELETE FROM $table WHERE $tree_field = :tree_id $extra_where";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
        $stmt->execute();
        if ($optimize) {
            echo ' ' . __('Optimize table...');
            ob_flush();
            flush();
            $dbh->query("OPTIMIZE TABLE $table");
        }
    }
    echo '<br>';
}

removeTreeRecords($dbh, 'humo_relations_persons', 'tree_id', $trees['tree_id'], $limit, '', false);

// *** Geneanet GEDCOM: don't remove events with pictures if the option is set to save pictures ***
removeTreeRecords(
    $dbh,
    'humo_events',
    'event_tree_id',
    $trees['tree_id'],
    $limit,
    ($humo_option["gedcom_read_save_pictures"] === 'y' ? "AND event_kind != 'picture'" : '')
);
removeTreeRecords($dbh, 'humo_persons', 'pers_tree_id', $trees['tree_id'], $limit);
removeTreeRecords($dbh, 'humo_families', 'fam_tree_id', $trees['tree_id'], $limit);
removeTreeRecords($dbh, 'humo_unprocessed_tags', 'tag_tree_id', $trees['tree_id'], $limit);
removeTreeRecords($dbh, 'humo_settings', 'setting_tree_id', $trees['tree_id'], $limit, "AND setting_variable = 'admin_favourite'");
removeTreeRecords($dbh, 'humo_repositories', 'repo_tree_id', $trees['tree_id'], $limit);
removeTreeRecords($dbh, 'humo_sources', 'source_tree_id', $trees['tree_id'], $limit);
removeTreeRecords($dbh, 'humo_texts', 'text_tree_id', $trees['tree_id'], $limit);
removeTreeRecords($dbh, 'humo_connections', 'connect_tree_id', $trees['tree_id'], $limit);
removeTreeRecords($dbh, 'humo_addresses', 'address_tree_id', $trees['tree_id'], $limit);

if (isset($show_gedcom_status)) {
    echo '<b>' . __('No error messages above? In that case the tables have been created!') . '</b><br>';
}
