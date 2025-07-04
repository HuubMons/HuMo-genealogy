<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$editor_cls = new Editor_cls;
$personPrivacy = new PersonPrivacy();
$personName = new PersonName();
$validateGedcomnumber = new ValidateGedcomnumber();

// *** Used to select adoption parents ***
$adoption_id = '';
if (isset($_GET['adoption_id']) && is_numeric($_GET['adoption_id'])) {
    $adoption_id = $_GET['adoption_id'];
}

if ($adoption_id) {
    echo '<h1 class="center">' . __('Select adoption parents') . '</h1>';
    $place_item = 'text_event' . $adoption_id;
    $form = 'form1';
} else {
    echo '<h1 class="center">' . __('Select parents') . '</h1>';
    $place_item = 'add_parents';
    $form = 'form1';
}

echo '
    <script>
    function select_item(item){
        window.opener.document.' . $form . '.' . $place_item . '.value=item;
        top.close();
        return false;
    }
    </script>
';

$search_quicksearch_parent = '';
if (isset($_POST['search_quicksearch_parent'])) {
    $search_quicksearch_parent = $safeTextDb->safe_text_db($_POST['search_quicksearch_parent']);
}

$search_person_id = '';
if (isset($_POST['search_person_id']) && $validateGedcomnumber->validate($_POST['search_person_id'])) {
    $search_person_id = $_POST['search_person_id'];
}
?>

<form method="POST" action="index.php?page=editor_relation_select<?= $adoption_id ? '&amp;adoption_id=' . $adoption_id : ''; ?>" style="display : inline;">
    <input type="text" name="search_quicksearch_parent" placeholder="<?= __('Name'); ?>" value="<?= $search_quicksearch_parent; ?>" size="15">
    <?= __('or ID:'); ?> <input type="text" name="search_person_id" value="<?= $search_person_id; ?>" size="5">
    <input type="submit" value="<?= __('Search'); ?>">
</form><br>

<?php
if ($search_quicksearch_parent != '') {
    // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
    $search_quicksearch_parent = str_replace(' ', '%', $search_quicksearch_parent);
    // *** In case someone entered "Mons, Huub" using a comma ***
    $search_quicksearch_parent = str_replace(',', '', $search_quicksearch_parent);

    // *** Search for man and woman ***
    $parents = "(SELECT * FROM humo_families, humo_persons
        WHERE
        (fam_man=pers_gedcomnumber AND pers_tree_id='" . $tree_id . "' AND fam_tree_id='" . $tree_id . "'
        AND (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$search_quicksearch_parent%'
        OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%$search_quicksearch_parent%'
        OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%$search_quicksearch_parent%'
        OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%$search_quicksearch_parent%'))
        OR
        (fam_woman=pers_gedcomnumber AND pers_tree_id='" . $tree_id . "' AND fam_tree_id='" . $tree_id . "'
        AND (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$search_quicksearch_parent%'
        OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%$search_quicksearch_parent%'
        OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%$search_quicksearch_parent%'
        OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%$search_quicksearch_parent%'))
        GROUP BY fam_gedcomnumber
        ORDER BY fam_gedcomnumber)";

    $parents_result = $dbh->query($parents);
} elseif ($search_person_id != '') {
    $parents = "SELECT humo_families.fam_gedcomnumber, humo_families.fam_man, humo_families.fam_woman, humo_persons.pers_gedcomnumber, humo_persons.pers_firstname, humo_persons.pers_prefix, humo_persons.pers_lastname, humo_persons.pers_tree_id
         FROM humo_families
         JOIN humo_persons ON (humo_families.fam_man = humo_persons.pers_gedcomnumber OR humo_families.fam_woman = humo_persons.pers_gedcomnumber)
         WHERE humo_persons.pers_gedcomnumber = :search_person_id
         AND humo_persons.pers_tree_id = :tree_id
         AND humo_families.fam_tree_id = :tree_id";
    $stmt = $dbh->prepare($parents);
    $stmt->bindParam(':search_person_id', $search_person_id, PDO::PARAM_STR);
    $stmt->bindParam(':tree_id', $tree_id, PDO::PARAM_STR);
    $stmt->execute();
    $parents_result = $stmt;
} else {
    $parents = "SELECT * FROM humo_families WHERE fam_tree_id='" . $tree_id . "' ORDER BY fam_gedcomnumber LIMIT 0,100";
    $parents_result = $dbh->query($parents);
}

while ($parentsDb = $parents_result->fetch(PDO::FETCH_OBJ)) {
    $parent2_text = '';

    //*** Father ***
    $db_functions->set_tree_id($tree_id);
    $persDb = $db_functions->get_person($parentsDb->fam_man);

    $privacy = $personPrivacy->get_privacy($persDb);
    $name = $personName->get_person_name($persDb, $privacy);
    $parent2_text .= $name["standard_name"];

    $parent2_text .= ' ' . __('and') . ' ';

    //*** Mother ***
    $db_functions->set_tree_id($tree_id);
    $persDb = $db_functions->get_person($parentsDb->fam_woman);

    $privacy = $personPrivacy->get_privacy($persDb);
    $name = $personName->get_person_name($persDb, $privacy);
    $parent2_text .= $name["standard_name"];

    echo '<a href="" onClick=\'return select_item("' . str_replace("'", "&prime;", $parentsDb->fam_gedcomnumber) . '")\'>[' . $parentsDb->fam_gedcomnumber . '] ' . $parent2_text . '</a><br>';
}

if ($search_quicksearch_parent == '' && $search_person_id == '') {
    echo __('Results are limited, use search to find more parents.');
}
