<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$validateGedcomnumber = new \Genealogy\Include\ValidateGedcomnumber;
$HtmlEscaper = new \Genealogy\Include\HtmlEscaper;

$personItem = isset($_GET['person_item']) && is_string($_GET['person_item']) ? $_GET['person_item'] : '';
$personSearch = isset($_GET['person']) && is_string($_GET['person']) ? $_GET['person'] : '';
$eventRow = isset($_GET['event_row']) && is_string($_GET['event_row']) && preg_match('/^[0-9]{1,8}$/', $_GET['event_row'])
    ? (string) (int) $_GET['event_row']
    : '';

$place_item = 'connect_man';
$form = 'form2';
$selectionMode = 'person';
$personStatus = '';
$childNumber = '';
if ($personItem === 'woman') {
    $place_item = 'connect_woman';
} elseif ($personItem === 'relation_add2') {
    $place_item = 'relation_add2';
    $form = 'form4';
} elseif ($personItem === 'child_connect2') {
    $place_item = 'child_connect2';
    $form = 'form7';
} elseif ($personItem === 'person_witness') {
    $place_item = 'event_connect_id2' . $eventRow;
    $form = 'form1';
} elseif ($personItem === 'marriage_witness') {
    $place_item = 'event_connect_id2' . $eventRow;
    $form = 'form2';
} elseif ($personItem === 'add_partner') {
    $form = 'form_entire';
    $selectionMode = 'add_person';
    $personStatus = 'partner';
} elseif (preg_match('/^add_child_([0-9]{1,8})$/', $personItem, $childMatch)) {
    $form = 'form_entire';
    $selectionMode = 'add_person';
    $personStatus = 'child';
    $childNumber = (string) (int) $childMatch[1];
}

$man_gedcomnumber = '';
if ($validateGedcomnumber->validate($personSearch)) {
    $man_gedcomnumber = $personSearch;
}

$selectionConfig = [
    'form' => $form,
    'mode' => $selectionMode,
    'field' => $place_item,
    'personStatus' => $personStatus,
    'childNumber' => $childNumber,
];
?>

<h1 class="center"><?= $HtmlEscaper->escHtml(__('Select person')); ?></h1>

<?php
$search_quicksearch_man = '';
if (isset($_POST['search_quicksearch_man']) && is_string($_POST['search_quicksearch_man'])) {
    $search_quicksearch_man = $safeTextDb->safe_text_db($_POST['search_quicksearch_man']);
}

$search_man_id = '';
if (isset($_POST['search_man_id']) && is_string($_POST['search_man_id']) && $validateGedcomnumber->validate($_POST['search_man_id'])) {
    $search_man_id = $_POST['search_man_id'];
}

$formAction = 'index.php?page=editor_person_select'
    . '&person_item=' . rawurlencode((string) $personItem)
    . '&person=' . rawurlencode((string) $man_gedcomnumber)
    . ($eventRow !== '' ? '&event_row=' . rawurlencode($eventRow) : '')
    . '&tree_id=' . rawurlencode((string) $tree_id);
?>
<form method="POST" action="<?= $HtmlEscaper->escHtml($formAction); ?>" style="display : inline;">
    <div class="row mb-2">
        <div class="col-4">
            <input type="text" name="search_quicksearch_man" placeholder="<?= $HtmlEscaper->escHtml(__('Name')); ?>" value="<?= $HtmlEscaper->escHtml((string) $search_quicksearch_man); ?>" size="15" class="form-control form-control-sm">
        </div>

        <div class="col-auto">
            <?= $HtmlEscaper->escHtml(__('or ID:')); ?>
        </div>

        <div class="col-auto">
            <input type="text" name="search_man_id" value="<?= $HtmlEscaper->escHtml($search_man_id); ?>" size="5" class="form-control form-control-sm">
        </div>

        <div class="col-auto">
            <input type="submit" name="submit" value="<?= $HtmlEscaper->escHtml(__('Search')); ?>" class="btn btn-sm btn-secondary">
        </div>
    </div>
</form><br><br>

<?php
if ($search_quicksearch_man != '') {
    $person_result = $db_functions->get_quicksearch_results($tree_id, $search_quicksearch_man);
} elseif ($search_man_id != '') {
    // TODO check this.
    if (substr($search_man_id, 0, 1) !== "i" && substr($search_man_id, 0, 1) !== "I") {
        //make entry "48" into "I48"
        $search_man_id = "I" . $search_man_id;
    }
    $person_qry = $dbh->prepare("SELECT pers_id FROM humo_persons WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :gedcomnumber");
    $person_qry->bindValue(':tree_id', $tree_id, PDO::PARAM_INT);
    $person_qry->bindValue(':gedcomnumber', $search_man_id, PDO::PARAM_STR);
    $person_qry->execute();
    $person_result = $person_qry;
} else {
    $person_qry = $dbh->prepare("SELECT pers_id FROM humo_persons WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :gedcomnumber");
    $person_qry->bindValue(':tree_id', $tree_id, PDO::PARAM_INT);
    $person_qry->bindValue(':gedcomnumber', $man_gedcomnumber, PDO::PARAM_STR);
    $person_qry->execute();
    $person_result = $person_qry;
}

$editor_cls = new \Genealogy\Include\Editor_cls;

if ($selectionMode !== 'add_person') {
    while ($person2 = $person_result->fetch(PDO::FETCH_OBJ)) {
        $person = $db_functions->get_person_with_id($person2->pers_id);
        ?>
        <a href="#" class="person-select-link" data-person-id="<?= $HtmlEscaper->escHtml((string) $person->pers_gedcomnumber); ?>">
            <?= $HtmlEscaper->escHtmlDecoded((string) $editor_cls->show_selected_person($person)); ?>
        </a>
        <?php
        if ($person->parent_relation_gedcomnumber) {
            echo ' (' . $HtmlEscaper->escHtml(__('Parents')) . ' ' . $HtmlEscaper->escHtml((string) $person->parent_relation_gedcomnumber) . ')';
        }
        echo '<br>';
    }
} else {
    while ($person2 = $person_result->fetch(PDO::FETCH_OBJ)) {
        $person = $db_functions->get_person_with_id($person2->pers_id);
        $birthDate = (string) $person->pers_birth_date;
        $deathDate = (string) $person->pers_death_date;
        $bdate_arr = explode(' ', $birthDate);
        if (substr($bdate_arr[0], 0, 3) === "BEF" || substr($bdate_arr[0], 0, 3) === "AFT" || substr($bdate_arr[0], 0, 3) === "ABT" || substr($bdate_arr[0], 0, 3) === "BET") {
            $dateprefix = $bdate_arr[0] . " ";
            $dateself = substr($birthDate, strpos($birthDate, ' ') + 1);
        } else {
            $dateself = $birthDate;
            $dateprefix = '';
        }

        $ddate_arr = explode(' ', $deathDate);
        if (substr($ddate_arr[0], 0, 3) === "BEF" || substr($ddate_arr[0], 0, 3) === "AFT" || substr($ddate_arr[0], 0, 3) === "ABT" || substr($ddate_arr[0], 0, 3) === "BET") {
            $dateprefix2 = $ddate_arr[0] . " ";
            $dateself2 = substr($deathDate, strpos($deathDate, ' ') + 1);
        } else {
            $dateself2 = $deathDate;
            $dateprefix2 = '';
        }

        $personData = [
            'gedcomnumber' => (string) $person->pers_gedcomnumber,
            'prefix' => (string) $person->pers_prefix,
            'lastname' => (string) $person->pers_lastname,
            'firstname' => (string) $person->pers_firstname,
            'birthDatePrefix' => $dateprefix,
            'birthDate' => $dateself,
            'birthPlace' => (string) $person->pers_birth_place,
            'deathDatePrefix' => $dateprefix2,
            'deathDate' => $dateself2,
            'deathPlace' => (string) $person->pers_death_place,
            'sex' => (string) $person->pers_sexe,
        ];
        $personDataJson = json_encode($personData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
        ?>
        <a href="#" class="person-select-link" data-person-data="<?= $HtmlEscaper->escHtml((string) $personDataJson); ?>">
            <?= $HtmlEscaper->escHtmlDecoded((string) $editor_cls->show_selected_person($person)); ?>
        </a><br>
        <?php
    }
}
?>


<script>
    const personSelectionConfig = <?= json_encode($selectionConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function setOpenerField(form, fieldName, value) {
        const field = form.elements.namedItem(fieldName);
        if (field) {
            field.value = value;
        }
    }

    document.querySelectorAll('.person-select-link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            if (!window.opener || !window.opener.document) {
                return;
            }

            const openerDocument = window.opener.document;
            const openerForm = openerDocument.forms.namedItem(personSelectionConfig.form);
            if (!openerForm) {
                return;
            }

            if (personSelectionConfig.mode === 'person') {
                setOpenerField(openerForm, personSelectionConfig.field, link.dataset.personId || '');
            } else {
                const person = JSON.parse(link.dataset.personData || '{}');
                const childSuffix = personSelectionConfig.childNumber ? '_' + personSelectionConfig.childNumber : '';
                const fieldPrefix = 'add_fam_' + personSelectionConfig.personStatus;

                setOpenerField(openerForm, fieldPrefix + '_exist' + childSuffix, person.gedcomnumber || '');
                setOpenerField(openerForm, fieldPrefix + '_lastname' + childSuffix, person.lastname || '');
                setOpenerField(openerForm, fieldPrefix + '_prefix' + childSuffix, person.prefix || '');
                setOpenerField(openerForm, fieldPrefix + '_firstname' + childSuffix, person.firstname || '');
                setOpenerField(openerForm, fieldPrefix + '_birthdate' + childSuffix + '_prefix', person.birthDatePrefix || '');
                setOpenerField(openerForm, fieldPrefix + '_birthdate' + childSuffix, person.birthDate || '');
                setOpenerField(openerForm, fieldPrefix + '_birthplace' + childSuffix, person.birthPlace || '');
                setOpenerField(openerForm, fieldPrefix + '_deathdate' + childSuffix + '_prefix', person.deathDatePrefix || '');
                setOpenerField(openerForm, fieldPrefix + '_deathdate' + childSuffix, person.deathDate || '');
                setOpenerField(openerForm, fieldPrefix + '_deathplace' + childSuffix, person.deathPlace || '');

                const sexRadio = person.sex === 'M' ? 'prad1' : person.sex === 'F' ? 'prad2' : 'prad3';
                const radio = openerDocument.getElementById(sexRadio + childSuffix);
                if (radio) {
                    radio.checked = true;
                }

                const personRow = openerDocument.getElementById(personSelectionConfig.childNumber ? 'child' + personSelectionConfig.childNumber : 'pmain');
                const searchField = openerDocument.getElementById(personSelectionConfig.childNumber ? 'psearp' + personSelectionConfig.childNumber : 'psearp');
                if (personRow) {
                    personRow.style.cssText = 'background-color:#EBEBE4;text-align:left;';
                }
                if (searchField) {
                    searchField.style.textAlign = 'center';
                }
            }

            window.close();
        });
    });
</script>
