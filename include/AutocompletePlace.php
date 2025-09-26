<?php

/**
 * Autocomplete location search
 * 
 * Aug. 2025 Huub: added autocomplete.
 */

include_once(__DIR__ . "/db_login.php"); //Inloggen database.

header('Content-Type: application/json');

$q = isset($_GET['term']) ? $_GET['term'] : '';
if (!$q) {
    exit;
}

//$stmt = $dbh->prepare("SELECT location_location FROM humo_location WHERE location_location LIKE ? ORDER BY location_location LIMIT 20");
$stmt = $dbh->prepare("
    SELECT location_location AS place FROM humo_location WHERE location_location LIKE ?
    UNION
    SELECT source_place AS place FROM humo_sources WHERE source_place LIKE ?
    UNION
    SELECT repo_place AS place FROM humo_repositories WHERE repo_place LIKE ?
    UNION
    SELECT address_place AS place FROM humo_addresses WHERE address_place LIKE ?
    ORDER BY place LIMIT 20
");

$stmt->execute(["%$q%", "%$q%", "%$q%", "%$q%"]);
$results = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //$results[] = $row['location_location'];
    $results[] = $row['place'];
}
echo json_encode($results);
