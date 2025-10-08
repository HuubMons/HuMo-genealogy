<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

/**
 * This is the page where one can choose two people from all persons in the tree for manual merging
 * The pairs will be presented by the show_pair function
 */

$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();

// ===== BEGIN SEARCH BOX SYSTEM
if (!isset($_POST["search1"]) && !isset($_POST["search2"]) && !isset($_POST["manual_compare"]) && !isset($_POST["switch"])) {
    // no button pressed: this is a fresh entry from frontpage link: start clean search form
    $_SESSION["search1"] = '';
    $_SESSION["search2"] = '';
    $_SESSION['rel_search_name'] = '';
    $_SESSION['rel_search_name2'] = '';
    $_SESSION['search_indi'] = '';
    $_SESSION['search_indi2'] = '';
}

$left = '';
if (isset($_POST["left"])) {
    $left = $_POST['left'];
}
$right = '';
if (isset($_POST["right"])) {
    $right = $_POST['right'];
}
if (isset($_POST["search1"])) {
    $_SESSION["search1"] = 1;
}
if (isset($_POST["search2"])) {
    $_SESSION["search2"] = 1;
}

if (isset($_POST["switch"])) {
    $temp = $_SESSION['rel_search_name'];
    $_SESSION['rel_search_name'] = $_SESSION['rel_search_name2'];
    $_SESSION['rel_search_name2'] = $temp;

    $temp = $_SESSION['search_indi'];
    $_SESSION['search_indi'] = $_SESSION['search_indi2'];
    $_SESSION['search_indi2'] = $temp;

    $temp = $left;
    $left = $right;
    $right = $temp;

    $temp = $_SESSION["search1"];
    $_SESSION["search1"] = $_SESSION["search2"];
    $_SESSION["search2"] = $temp;
}

$search_name = '';
if (isset($_POST["search_name"]) && !isset($_POST["switch"])) {
    $search_name = trim($safeTextDb->safe_text_db($_POST['search_name']));
    $_SESSION['rel_search_name'] = $search_name;
}
if (isset($_SESSION['rel_search_name'])) {
    $search_name = $_SESSION['rel_search_name'];
}

$search_indi = '';
if (isset($_POST["search_indi"]) && !isset($_POST["switch"])) {
    $search_indi = trim($safeTextDb->safe_text_db($_POST['search_indi']));
    $_SESSION['search_indi'] = $search_indi;
}
if (isset($_SESSION['search_indi'])) {
    $search_indi = $_SESSION['search_indi'];
}

$search_name2 = '';
if (isset($_POST["search_name2"]) && !isset($_POST["switch"])) {
    $search_name2 = trim($safeTextDb->safe_text_db($_POST['search_name2']));
    $_SESSION['rel_search_name2'] = $search_name2;
}
if (isset($_SESSION['rel_search_name2'])) {
    $search_name2 = $_SESSION['rel_search_name2'];
}

$search_indi2 = '';
if (isset($_POST["search_indi2"]) && !isset($_POST["switch"])) {
    $search_indi2 = trim($safeTextDb->safe_text_db($_POST['search_indi2']));
    $_SESSION['search_indi2'] = $search_indi2;
}
if (isset($_SESSION['search_indi2'])) {
    $search_indi2 = $_SESSION['search_indi2'];
}
?>

<br><?= __('Pick the two persons you want to check for merging'); ?>. <?= __('You can enter names (or part of names) or GEDCOM no. (INDI), or leave boxes empty'); ?><br>
<?= __('<b>TIP: when you click "search" with all boxes left empty you will get a list with all persons in the database. (May take a few seconds)</b>'); ?><br><br>

<form method="post" action="index.php?page=tree&amp;menu_admin=<?= $trees['menu_tab']; ?>" style="display : inline;">
    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
    <table class="table table-light" style="text-align:center; width:100%;">
        <tr class="table-primary">
            <td>&nbsp;</td>
            <td><?= __('Name'); ?></td>
            <td><?= __('GEDCOM no. ("I43")'); ?></td>
            <td><?= __('Search'); ?></td>
            <td colspan=2><?= __('Pick a name from search results'); ?></td>
            <td><?= __('Show details'); ?></td>
        </tr>

        <!-- First person -->
        <tr>
            <td style="white-space:nowrap"><?= __('Person'); ?> 1</td>
            <td>
                <input type="text" name="search_name" value="<?= $search_name; ?>" size="30" class="form-control form-control-sm">
            </td>
            <td>
                <input type="text" name="search_indi" value="<?= $search_indi; ?>" size="10" class="form-control form-control-sm">
            </td>
            <td>
                <input type="submit" name="search1" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
            </td>
            <td>
                <?php
                $len = 300;  // length of name pulldown box

                if (isset($_SESSION["search1"]) && $_SESSION["search1"] == 1) {

                    if (isset($_SESSION["search_indi"]) && $_SESSION["search_indi"] != "") {
                        // make sure it works with "I436", "i436" and "436"
                        $indi = (substr($search_indi, 0, 1) === "I" || substr($search_indi, 0, 1) === "i") ? strtoupper($search_indi) : "I" . $search_indi;

                        $search_qry = "SELECT pers_id FROM humo_persons WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :indi";
                        $stmt = $dbh->prepare($search_qry);
                        $stmt->execute([
                            ':tree_id' => $trees['tree_id'],
                            ':indi' => $indi
                        ]);
                        $search_result = $stmt;
                    } elseif (isset($_SESSION['rel_search_name']) && $_SESSION['rel_search_name'] != '') {
                        $search_result = $db_functions->get_quicksearch_results($trees['tree_id'], $search_name);
                    } else {
                        $search_qry = "SELECT pers_id FROM humo_persons WHERE pers_tree_id = :tree_id
                            ORDER BY pers_lastname, pers_firstname, CAST(SUBSTRING(pers_gedcomnumber, 2) AS UNSIGNED)";
                        $stmt = $dbh->prepare($search_qry);
                        $stmt->execute([
                            ':tree_id' => $trees['tree_id']
                        ]);
                        $search_result = $stmt;
                    }

                    if ($search_result) {
                        if ($search_result->rowCount() > 0) {
                ?>
                            <select size="1" name="left" style="width:<?= $len; ?>px" class="form-select form-select-sm">
                                <?php
                                while ($search1Db = $search_result->fetch(PDO::FETCH_OBJ)) {
                                    $searchDb = $db_functions->get_person_with_id($search1Db->pers_id);
                                    $privacy = $personPrivacy->get_privacy($searchDb);
                                    $name = $personName->get_person_name($searchDb, $privacy);
                                    if ($name["show_name"]) {
                                        echo '<option';
                                        if (isset($left) && ($searchDb->pers_id == $left && !(isset($_POST["search1"]) && $search_lastname == '' && $search_firstname == ''))) {
                                            echo ' selected';
                                        }
                                        echo ' value="' . $searchDb->pers_id . '">';
                                        echo $name["index_name"] . ' [' . $searchDb->pers_gedcomnumber . ']</option>';
                                    }
                                }
                                ?>
                            </select>
                        <?php } else { ?>
                            <select size="1" name="notfound" value="1" style="width:<?= $len; ?>px" class="form-select form-select-sm">
                                <option><?= __('Person not found'); ?></option>
                            </select>
                    <?php
                        }
                    }
                } else {
                    ?>
                    <select size="1" name="left" style="width:<?= $len; ?>px" class="form-select form-select-sm">
                        <option></option>
                    </select>
                <?php } ?>
            </td>
            <td rowspan=2>
                <input type="submit" alt="<?= __('Switch persons'); ?>" title="<?= __('Switch persons'); ?>" value=" " name="switch" style="background: #fff url('../images/turn_around.gif') top no-repeat;width:25px;height:25px">
            </td>
            <td rowspan=2>
                <input type="submit" name="manual_compare" value="<?= __('Show details'); ?>" class="btn btn-sm btn-success">
            </td>
        </tr>

        <!-- Second person -->
        <tr>
            <td style="white-space:nowrap">
                <?= __('Person'); ?> 2
            </td>
            <td>
                <input type="text" name="search_name2" value="<?= $search_name2; ?>" size="30" class="form-control form-control-sm">
            </td>
            <td>
                <input type="text" name="search_indi2" value="<?= $search_indi2; ?>" size="10" class="form-control form-control-sm">
            </td>
            <td>
                <input type="submit" name="search2" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
            </td>
            <td>
                <?php
                if (isset($_SESSION["search2"]) && $_SESSION["search2"] == 1) {

                    if (isset($_SESSION["search_indi2"]) && $_SESSION["search_indi2"] != "") {
                        // make sure it works with "I436", "i436" and "436"
                        $indi2 = (substr($search_indi2, 0, 1) === "I" || substr($search_indi2, 0, 1) === "i") ? strtoupper($search_indi2) : "I" . $search_indi2;

                        $search_qry = "SELECT pers_id FROM humo_persons WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :indi";
                        $stmt = $dbh->prepare($search_qry);
                        $stmt->execute([
                            ':tree_id' => $trees['tree_id'],
                            ':indi' => $indi2
                        ]);
                        $search_result2 = $stmt;
                    } elseif (isset($_SESSION['rel_search_name2']) && $_SESSION['rel_search_name2'] != '') {
                        $search_result2 = $db_functions->get_quicksearch_results($trees['tree_id'], $search_name2);
                    } else {
                        $search_qry = "SELECT pers_id FROM humo_persons WHERE pers_tree_id = :tree_id
                            ORDER BY pers_lastname, pers_firstname, CAST(SUBSTRING(pers_gedcomnumber, 2) AS UNSIGNED)";
                        $stmt = $dbh->prepare($search_qry);
                        $stmt->execute([
                            ':tree_id' => $trees['tree_id']
                        ]);
                        $search_result2 = $stmt;
                    }

                    if ($search_result2) {
                        if ($search_result2->rowCount() > 0) {
                ?>
                            <select size="1" name="right" style="width:<?= $len; ?>px" class="form-select form-select-sm">
                                <?php
                                while ($search2Db = $search_result2->fetch(PDO::FETCH_OBJ)) {
                                    $searchDb2 = $db_functions->get_person_with_id($search2Db->pers_id);
                                    $privacy = $personPrivacy->get_privacy($searchDb2);
                                    $name = $personName->get_person_name($searchDb2, $privacy);
                                    if ($name["show_name"]) {
                                        echo '<option';
                                        if (isset($right) && ($searchDb2->pers_id == $right && !(isset($_POST["search2"]) && $search_lastname2 == '' && $search_firstname2 == ''))) {
                                            echo ' selected';
                                        }
                                        echo ' value="' . $searchDb2->pers_id . '">';
                                        echo $name["index_name"] . ' [' . $searchDb2->pers_gedcomnumber . ']</option>';
                                    }
                                }
                                ?>
                            </select>
                        <?php } else { ?>
                            <select size="1" name="notfound" value="1" style="width:<?= $len; ?>px" class="form-select form-select-sm">
                                <option><?= __('Person not found'); ?></option>
                            </select>
                    <?php
                        }
                    }
                } else {
                    ?>
                    <select size="1" name="right" style="width:<?= $len; ?>px" class="form-select form-select-sm">
                        <option></option>
                    </select>
                <?php } ?>
            </td>
        </tr>
    </table>
</form>