<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

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

include(__DIR__ . '/editor_cls.php');
$editor_cls = new editor_cls;

echo '
    <script>
    function select_item(item){
        window.opener.document.' . $form . '.' . $place_item . '.value=item;
        top.close();
        return false;
    }
    </script>
';

//echo '<form method="POST" action="index.php?page=editor_relation_select" style="display : inline;">';
$link = 'index.php?page=editor_relation_select';
if ($adoption_id) {
    $link .= '&amp;adoption_id=' . $adoption_id;
}
echo '<form method="POST" action="' . $link . '" style="display : inline;">';
$search_quicksearch_parent = '';
if (isset($_POST['search_quicksearch_parent'])) {
    $search_quicksearch_parent = safe_text_db($_POST['search_quicksearch_parent']);
}
echo '<input type="text" name="search_quicksearch_parent" placeholder="' . __('Name') . '" value="' . $search_quicksearch_parent . '" size="15">';

$search_person_id = '';
if (isset($_POST['search_person_id'])) {
    $search_person_id = safe_text_db($_POST['search_person_id']);
}
echo ' ' . __('or ID:') . ' <input type="text" name="search_person_id" value="' . $search_person_id . '" size="5">';

echo ' <input type="submit" value="' . __('Search') . '">';
echo '</form><br>';


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
    // *** Search for man ***
    $parents = "(SELECT * FROM humo_families, humo_persons
        WHERE fam_man=pers_gedcomnumber AND pers_tree_id='" . $tree_id . "' AND fam_tree_id='" . $tree_id . "'
        AND fam_man='$search_person_id')";

    // *** Search for woman ***
    $parents = "(SELECT * FROM humo_families, humo_persons
        WHERE fam_woman=pers_gedcomnumber AND pers_tree_id='" . $tree_id . "' AND fam_tree_id='" . $tree_id . "'
        AND fam_man='$search_person_id')";

    $parents_result = $dbh->query($parents);
} else {
    $parents = "SELECT * FROM humo_families WHERE fam_tree_id='" . $tree_id . "' ORDER BY fam_gedcomnumber LIMIT 0,100";
    $parents_result = $dbh->query($parents);
}

while ($parentsDb = $parents_result->fetch(PDO::FETCH_OBJ)) {
    $parent2_text = '';
    //*** Father ***
    // *** Use class to process person ***
    $db_functions->set_tree_id($tree_id);
    $persDb = $db_functions->get_person($parentsDb->fam_man);

    $pers_cls = new person_cls($persDb);
    $name = $pers_cls->person_name($persDb);
    $parent2_text .= $name["standard_name"];

    $parent2_text .= ' ' . __('and') . ' ';

    //*** Mother ***
    // *** Use class to process person ***
    $db_functions->set_tree_id($tree_id);
    $persDb = $db_functions->get_person($parentsDb->fam_woman);

    $pers_cls = new person_cls($persDb);
    $name = $pers_cls->person_name($persDb);
    $parent2_text .= $name["standard_name"];

    echo '<a href="" onClick=\'return select_item("' . str_replace("'", "&prime;", $parentsDb->fam_gedcomnumber) . '")\'>[' . $parentsDb->fam_gedcomnumber . '] ' . $parent2_text . '</a><br>';
}

if ($search_quicksearch_parent == '' && $search_person_id == '') {
    echo __('Results are limited, use search to find more parents.');
}
