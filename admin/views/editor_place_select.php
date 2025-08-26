<?php

/**
 * Select place in editor.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$place_item = '';
$form = '';
if (isset($_GET['form'])) {
    $check_array = array("1", "2", "3", "5", "6");
    if (in_array($_GET['form'], $check_array)) {
        $url_form = $_GET['form'];
        $form = 'form' . $_GET['form'];
    }

    $check_array = array(
        "pers_birth_place",
        "pers_bapt_place",
        "pers_death_place",
        "pers_buried_place",
        "fam_relation_place",
        "fam_marr_notice_place",
        "fam_marr_place",
        "fam_marr_church_notice_place",
        "fam_marr_church_place",
        "fam_div_place",
        "address_place",
        "event_place",
        "birth_decl_place",
        "death_decl_place"
    );
    if (in_array($_GET['place_item'], $check_array)) {
        $url_place_item = $_GET['place_item'];
        $place_item = $_GET['place_item'];
    }

    // *** Multiple places/ addresses: add address_id ***
    if (isset($_GET['address_id']) && is_numeric($_GET['address_id'])) {
        $url_address_id = $_GET['address_id'];
        $place_item .= '_' . $_GET['address_id'];
    }

    // *** Multiple events: add event_id ***
    if (isset($_GET['event_id']) && is_numeric($_GET['event_id'])) {
        $url_event_id = $_GET['event_id'];
        $place_item .= $_GET['event_id'];
    }
}

// *** January 2022: no longer in use? ***
//if(strpos($_GET['place_item'],"add_fam")!== false ) {
//	$form = "form_entire";
//	$place_item = $_GET['place_item'];
//}

// *** Search for place ***
$url_add = '';
if (isset($url_form)) {
    $url_add .= '&amp;form=' . $url_form;
}
if (isset($url_place_item)) {
    $url_add .= '&amp;place_item=' . $url_place_item;
}
if (isset($url_address_id)) {
    $url_add .= '&amp;address_id=' . $url_address_id;
}
if (isset($url_event_id)) {
    $url_add .= '&amp;event_id=' . $url_event_id;
}
$quicksearch_place = '';
if (isset($_POST['search_quicksearch_place'])) {
    $quicksearch_place = $safeTextDb->safe_text_db($_POST['search_quicksearch_place']);
}

$search = '_%';
if ($quicksearch_place) {
    $search = '%' . $quicksearch_place . '%';
}

$params = [
    ':tree_id' => $tree_id,
    ':search' => $search
];
$query = "
    (SELECT pers_birth_place as place_order FROM humo_persons
        WHERE pers_tree_id = :tree_id AND pers_birth_place LIKE :search GROUP BY place_order)
    UNION
    (SELECT pers_bapt_place as place_order FROM humo_persons
        WHERE pers_tree_id = :tree_id AND pers_bapt_place LIKE :search GROUP BY place_order)
    UNION
    (SELECT event_place as place_order FROM humo_events
        WHERE event_tree_id = :tree_id AND event_place LIKE :search GROUP BY place_order)
    UNION
    (SELECT pers_death_place as place_order FROM humo_persons
        WHERE pers_tree_id = :tree_id AND pers_death_place LIKE :search GROUP BY place_order)
    UNION
    (SELECT pers_buried_place as place_order FROM humo_persons
        WHERE pers_tree_id = :tree_id AND pers_buried_place LIKE :search GROUP BY place_order)
    ORDER BY place_order
";

$stmt = $dbh->prepare($query);
$stmt->execute($params);
$result = $stmt;
?>

<h1 class="center"><?= __('Select place') ?></h1>

<form method="POST" action="index.php?page=editor_place_select' . $url_add . '" style="display : inline;">
    <div class="row mb-2">
        <div class="col-md-4">
            <input type="text" name="search_quicksearch_place" placeholder="<?= __('Name') ?>" value="<?= $quicksearch_place ?>" size="15" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <input type="submit" value="<?= __('Search') ?>" class="btn btn-sm btn-secondary">
        </div>
</form><br>

<?php
while ($resultDb = $result->fetch(PDO::FETCH_OBJ)) {
    //echo '<a href="" onClick=\'return select_item("'.$resultDb->place_order.'")\'>'.$resultDb->place_order.'</a><br>';
    // *** Replace ' by &prime; otherwise a place including a ' character can't be selected ***
    echo '<a href="" onClick=\'return select_item("' . str_replace("'", "&prime;", $resultDb->place_order) . '")\'>' . $resultDb->place_order . '</a><br>';
}
?>

<script>
    function select_item(item) {
        /* EXAMPLE: window.opener.document.form1.pers_birth_place.value=item; */
        window.opener.document.<?= $form ?>.<?= $place_item ?>.value = item;
        top.close();
        return false;
    }
</script>