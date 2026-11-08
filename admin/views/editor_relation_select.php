<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$editor_cls = new \Genealogy\Include\Editor_cls;
$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();
$validateGedcomnumber = new \Genealogy\Include\ValidateGedcomnumber();
$HtmlEscaper = new \Genealogy\Include\HtmlEscaper();

// *** Used to select adoption parents ***
$adoption_id = '';
if (isset($_GET['adoption_id']) && is_numeric($_GET['adoption_id'])) {
    $adoption_id = (string) (int) $_GET['adoption_id'];
}

if ($adoption_id) {
    $place_item = 'text_event' . $adoption_id;
} else {
    $place_item = 'add_parents';
}

$search_quicksearch_parent = '';
if (isset($_POST['search_quicksearch_parent'])) {
    $search_quicksearch_parent = $safeTextDb->safe_text_db($_POST['search_quicksearch_parent']);
}

$search_person_id = '';
if (isset($_POST['search_person_id']) && $validateGedcomnumber->validate($_POST['search_person_id'])) {
    $search_person_id = $_POST['search_person_id'];
}

if ($search_quicksearch_parent != '') {
    // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
    $search_quicksearch_parent = str_replace(' ', '%', $search_quicksearch_parent);
    // *** In case someone entered "Mons, Huub" using a comma ***
    $search_quicksearch_parent = str_replace(',', '', $search_quicksearch_parent);

    // *** Search for man and woman ***
    $like = '%' . $search_quicksearch_parent . '%';
    $parents = "SELECT 
        r.relation_gedcomnumber AS fam_gedcomnumber,
        p1.pers_id AS partner1_id,
        p2.pers_id AS partner2_id,
        p1.pers_firstname AS man_firstname,
        p1.pers_prefix AS man_prefix,
        p1.pers_lastname AS man_lastname,
        p2.pers_firstname AS woman_firstname,
        p2.pers_prefix AS woman_prefix,
        p2.pers_lastname AS woman_lastname,
        p1.pers_tree_id
    FROM humo_relations_persons r

    LEFT JOIN humo_relations_persons rp1 ON rp1.relation_gedcomnumber = r.relation_gedcomnumber AND rp1.partner_order = 1
    LEFT JOIN humo_persons p1 ON p1.pers_gedcomnumber = rp1.person_gedcomnumber AND p1.pers_tree_id = :tree_id

    LEFT JOIN humo_relations_persons rp2 ON rp2.relation_gedcomnumber = r.relation_gedcomnumber AND rp2.partner_order = 2
    LEFT JOIN humo_persons p2 ON p2.pers_gedcomnumber = rp2.person_gedcomnumber AND p2.pers_tree_id = :tree_id

    WHERE r.tree_id = :tree_id
      AND (
          CONCAT(COALESCE(p1.pers_firstname,''), REPLACE(COALESCE(p1.pers_prefix,''),'_',' '), COALESCE(p1.pers_lastname,'')) LIKE :term
       OR CONCAT(COALESCE(p1.pers_lastname,''), REPLACE(COALESCE(p1.pers_prefix,''),'_',' '), COALESCE(p1.pers_firstname,'')) LIKE :term
       OR CONCAT(COALESCE(p1.pers_lastname,''), COALESCE(p1.pers_firstname,''), REPLACE(COALESCE(p1.pers_prefix,''),'_',' ')) LIKE :term
       OR CONCAT(REPLACE(COALESCE(p1.pers_prefix,''),'_',' '), COALESCE(p1.pers_lastname,''), COALESCE(p1.pers_firstname,'')) LIKE :term

       OR CONCAT(COALESCE(p2.pers_firstname,''), REPLACE(COALESCE(p2.pers_prefix,''),'_',' '), COALESCE(p2.pers_lastname,'')) LIKE :term
       OR CONCAT(COALESCE(p2.pers_lastname,''), REPLACE(COALESCE(p2.pers_prefix,''),'_',' '), COALESCE(p2.pers_firstname,'')) LIKE :term
       OR CONCAT(COALESCE(p2.pers_lastname,''), COALESCE(p2.pers_firstname,''), REPLACE(COALESCE(p2.pers_prefix,''),'_',' ')) LIKE :term
       OR CONCAT(REPLACE(COALESCE(p2.pers_prefix,''),'_',' '), COALESCE(p2.pers_lastname,''), COALESCE(p2.pers_firstname,'')) LIKE :term
      )
    GROUP BY r.relation_gedcomnumber
    ORDER BY r.relation_gedcomnumber";
    $stmt = $dbh->prepare($parents);
    $stmt->bindParam(':tree_id', $tree_id, PDO::PARAM_STR);
    $stmt->bindParam(':term', $like, PDO::PARAM_STR);
    $stmt->execute();
    $parents_result = $stmt;
} elseif ($search_person_id != '') {
    $parents = "SELECT 
        r.relation_gedcomnumber AS fam_gedcomnumber,
        p1.pers_id AS partner1_id,
        p1.pers_gedcomnumber AS partner1_gedcomnumber,
        p2.pers_id AS partner2_id,
        p2.pers_gedcomnumber AS partner2_gedcomnumber,
        p1.pers_firstname AS man_firstname,
        p1.pers_prefix AS man_prefix,
        p1.pers_lastname AS man_lastname,
        p2.pers_firstname AS woman_firstname,
        p2.pers_prefix AS woman_prefix,
        p2.pers_lastname AS woman_lastname,
        p1.pers_tree_id
    FROM humo_relations_persons r

    LEFT JOIN humo_relations_persons rp1 ON rp1.relation_gedcomnumber = r.relation_gedcomnumber AND rp1.partner_order = 1
    LEFT JOIN humo_persons p1 ON p1.pers_gedcomnumber = rp1.person_gedcomnumber AND p1.pers_tree_id = :tree_id

    LEFT JOIN humo_relations_persons rp2 ON rp2.relation_gedcomnumber = r.relation_gedcomnumber AND rp2.partner_order = 2
    LEFT JOIN humo_persons p2 ON p2.pers_gedcomnumber = rp2.person_gedcomnumber AND p2.pers_tree_id = :tree_id

    WHERE r.tree_id = :tree_id
      AND (
          p1.pers_gedcomnumber = :search_person_id OR p2.pers_gedcomnumber = :search_person_id
      )
    GROUP BY r.relation_gedcomnumber
    ORDER BY r.relation_gedcomnumber";
    $stmt = $dbh->prepare($parents);
    $stmt->bindParam(':search_person_id', $search_person_id, PDO::PARAM_STR);
    $stmt->bindParam(':tree_id', $tree_id, PDO::PARAM_STR);
    $stmt->execute();
    $parents_result = $stmt;
} else {
    $parents = "SELECT 
        r.relation_gedcomnumber AS fam_gedcomnumber,
        p1.pers_id AS partner1_id,
        p2.pers_id AS partner2_id,
        p1.pers_firstname AS man_firstname,
        p1.pers_prefix AS man_prefix,
        p1.pers_lastname AS man_lastname,
        p2.pers_firstname AS woman_firstname,
        p2.pers_prefix AS woman_prefix,
        p2.pers_lastname AS woman_lastname,
        p1.pers_tree_id
    FROM humo_relations_persons r

    LEFT JOIN humo_relations_persons rp1 ON rp1.relation_id = r.relation_id AND rp1.partner_order = 1
    LEFT JOIN humo_persons p1 ON p1.pers_id = rp1.person_id

    LEFT JOIN humo_relations_persons rp2 ON rp2.relation_id = r.relation_id AND rp2.partner_order = 2
    LEFT JOIN humo_persons p2 ON p2.pers_id = rp2.person_id

    WHERE r.tree_id = '" . $tree_id . "'
    GROUP BY r.relation_gedcomnumber
    ORDER BY r.relation_gedcomnumber
    LIMIT 0,100";
    $parents_result = $dbh->query($parents);
}

if ($adoption_id) {
?>
    <h1 class="center"><?= __('Select adoption parents'); ?></h1>
<?php } else { ?>
    <h1 class="center"><?= __('Select parents'); ?></h1>
<?php } ?>

<form method="POST" action="index.php?page=editor_relation_select<?= $adoption_id ? '&amp;adoption_id=' . rawurlencode($adoption_id) : ''; ?>" style="display : inline;">
    <div class="row mb-2">
        <div class="col-md-4">
            <input type="text" name="search_quicksearch_parent" placeholder="<?= $HtmlEscaper->escHtml(__('Name')); ?>" value="<?= $HtmlEscaper->escHtml((string) $search_quicksearch_parent); ?>" size="15" class="form-control form-control-sm">
        </div>
        <div class="col-md-auto">
            <?= __('or ID:'); ?>
        </div>
        <div class="col-md-3">
            <input type="text" name="search_person_id" value="<?= $HtmlEscaper->escHtml((string) $search_person_id); ?>" size="5" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <input type="submit" value="<?= __('Search'); ?>" class="btn btn-primary btn-sm">
        </div>
    </div>
</form><br>

<?php
while ($parentsDb = $parents_result->fetch(PDO::FETCH_OBJ)) {
    $parent2_text = '';

    //*** Father ***
    $db_functions->set_tree_id($tree_id);
    $persDb = $db_functions->get_person_with_id($parentsDb->partner1_id);

    $privacy = $personPrivacy->get_privacy($persDb);
    $name = $personName->get_person_name($persDb, $privacy);
    $parent2_text .= $name["standard_name"];

    $parent2_text .= ' ' . __('and') . ' ';

    //*** Mother ***
    $db_functions->set_tree_id($tree_id);
    $persDb = $db_functions->get_person_with_id($parentsDb->partner2_id);

    $privacy = $personPrivacy->get_privacy($persDb);
    $name = $personName->get_person_name($persDb, $privacy);
    $parent2_text .= $name["standard_name"];

    echo '<a href="#" class="relation-select-link" data-relation-id="' . $HtmlEscaper->escHtml((string) $parentsDb->fam_gedcomnumber) . '">[' . $HtmlEscaper->escHtml((string) $parentsDb->fam_gedcomnumber) . '] ' . $HtmlEscaper->escHtml((string) $parent2_text) . '</a><br>';
}

if ($search_quicksearch_parent == '' && $search_person_id == '') {
    echo __('Results are limited, use search to find more parents.');
}
?>

<script>
    const targetFormName = 'form1';
    const targetFieldName = <?= json_encode($place_item, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function select_item(item) {
        if (!window.opener || !window.opener.document.forms[targetFormName]) {
            return false;
        }

        const targetField = window.opener.document.forms[targetFormName].elements.namedItem(targetFieldName);
        if (!targetField) {
            return false;
        }

        targetField.value = item;
        top.close();
        return false;
    }

    document.querySelectorAll('.relation-select-link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            select_item(link.dataset.relationId);
        });
    });
</script>