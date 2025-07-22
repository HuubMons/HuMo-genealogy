<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

/**
 * Merge data functions are made by Yossi.
 * 22 dec 2017 Huub: Updated merge data functions with correct person and family counter for main page.
 * 09 aug 2023 Huub: Seperated several items from this file into views (preparing MVC)
 * 18 feb 2024 Huub: Moved class to view script.
 * 18-jul 2025 Huub: Split into multiple view files and seperate TreeMerge class.
 */

// *** tree_merge is the function that navigates all merge screens and options ***
$db_functions->set_tree_id($trees['tree_id']);

$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();
$treeMerge = new \Genealogy\Include\TreeMerge($dbh);

// the following creates the pages that cycle through all duplicates that are stored in the dupl_arr array
// the pages themselves are presented with the "show_pair function"
if (isset($_POST['duplicate_compare'])) {
    if (!isset($_POST['no_increase'])) {
        // no increase is used if "switch left and right" was chosen
        // present_compare is the pair that has to be shown next - saved to session
        $nr = ++$_SESSION['present_compare_' . $trees['tree_id']];
    } else {
        $nr = $_SESSION['present_compare_' . $trees['tree_id']];
    }
    if (isset($_POST['choice_nr'])) {
        // choice number is the number from the "skip to" pulldown - saved to a session
        $nr = $_POST['choice_nr'];
        $_SESSION['present_compare_' . $trees['tree_id']] = $_POST['choice_nr'];
    }

    // make sure the persons in the array are still there (in case in the mean time someone was merged)
    // after all, one person may be compared to more than one other person!
    while ($_SESSION['present_compare_' . $trees['tree_id']] < count($_SESSION['dupl_arr_' . $trees['tree_id']])) {
        $comp_set = explode(';', $_SESSION['dupl_arr_' . $trees['tree_id']][$nr]);
        $res = $db_functions->get_person_with_id($comp_set[0]);
        $res2 = $db_functions->get_person_with_id($comp_set[1]);
        if (!$res || !$res2) {
            // one or 2 persons are missing - continue with next pair
            $nr = ++$_SESSION['present_compare_' . $trees['tree_id']];
            continue; // look for next pair in array
        } else {
?>
            <br><?= __('Carefully compare these two persons. Only if you are <b>absolutely sure</b> they are identical, press "Merge right into left".'); ?><br><br>
            <?php
            $left = $comp_set[0];
            $right = $comp_set[1];
            if (isset($_POST['left'])) {
                $left = $_POST['left'];
            }
            if (isset($_POST['right'])) {
                $right = $_POST['right'];
            }
            ?>

            &nbsp;&nbsp;&nbsp;&nbsp;
            <form method="post" action="index.php" style="display : inline;">
                <input type="hidden" name="page" value="tree">
                <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-secondary">
            </form>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <form method="post" action="index.php" style="display : inline;">
                <input type="hidden" name="page" value="tree">
                <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                <input type="hidden" name="no_increase" value="1">
                <input type="hidden" name="left" value="<?= $right; ?>">
                <input type="hidden" name="right" value="<?= $left; ?>">
                <input type="submit" name="duplicate_compare" value="<?= __('<- Switch left and right ->'); ?>" class="btn btn-sm btn-secondary">
            </form>

            &nbsp;&nbsp;&nbsp;&nbsp;
            <form method="post" action="index.php" style="display : inline;">
                <input type="hidden" name="page" value="tree">
                <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                <input type="submit" name="duplicate_compare" value="<?= __('Skip to next'); ?>" class="btn btn-sm btn-secondary">
            </form>

            &nbsp;&nbsp;&nbsp;&nbsp;<?= __('Skip to nr: '); ?>
            <form method="post" action="index.php" style="display : inline;">
                <input type="hidden" name="page" value="tree">
                <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                <select style="max-width:60px" name="choice_nr">
                    <?php for ($x = 0; $x < count($_SESSION['dupl_arr_' . $trees['tree_id']]); $x++) { ?>
                        <option value="<?= $x; ?>" <?= $x == $_SESSION['present_compare_' . $trees['tree_id']] ? 'selected' : ''; ?>><?= ($x + 1); ?></option>
                    <?php } ?>
                </select>
                <input type="submit" name="duplicate_compare" value="<?= __('Go!'); ?>" class="btn btn-sm btn-secondary">
            </form>

            &nbsp;&nbsp;&nbsp;&nbsp;
            <form method="post" action="index.php" style="display : inline;">
                <input type="hidden" name="page" value="tree">
                <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                <input type="hidden" name="dupl" value="1">
                <input type="submit" name="merge" value="<?= __('Merge right into left'); ?>" class="btn btn-sm btn-success">
                <br><br>
                <?php $treeMerge->show_pair($left, $right, 'duplicate'); ?>
                <br>
            </form>

        <?php
            break; // get out of the while loop. next loop will be called by skip or merge buttons
        }
    }

    if ($_SESSION['present_compare_' . $trees['tree_id']] >= count($_SESSION['dupl_arr_' . $trees['tree_id']])) {
        unset($_SESSION['present_compare_' . $trees['tree_id']]);
        ?>
        <br><br><?= __('No more duplicates found'); ?><br><br>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>">
        </form>
    <?php
    }
} elseif (isset($_POST['manual_compare'])) {
    // this creates the screen for manual merge. the pair itself is presented with the "show_pair" function

    // check if persons are of opposite sex - if so don't continue
    $per1Db = $db_functions->get_person_with_id($_POST['left']);
    $per2Db = $db_functions->get_person_with_id($_POST['right']);
    if ($per1Db->pers_sexe != $per2Db->pers_sexe) {
    ?>
        <br><?= __('You cannot merge persons of opposite sex. Please try again'); ?><br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" name="manual" value="<?= __('Choose another pair'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php
    } elseif ($per1Db->pers_gedcomnumber == $per2Db->pers_gedcomnumber) { // trying to merge same person!!
    ?>
        <br><?= __('This is one person already - you can\'t merge! Please try again'); ?><br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" name="manual" value="<?= __('Choose another pair'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } else { ?>
        <br><?= __('Carefully compare these two persons. Only if you are <b>absolutely sure</b> they are identical, press "Merge right into left".'); ?><br>
        <?= __('The checked items will be the ones entered into the database for the merged person. You can change the default settings'); ?><br>

        &nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="hidden" name="left" value="<?= $_POST['right']; ?>">
            <input type="hidden" name="right" value="<?= $_POST['left']; ?>">
            <input type="submit" name="manual_compare" value="<?= __('<- Switch left and right ->'); ?>" class="btn btn-sm btn-success">
        </form>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" name="manual" value="<?= __('Choose another pair'); ?>" class="btn btn-sm btn-success">
        </form>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="hidden" name="manu" value="1">
            <input type="hidden" name="left" value="<?= $_POST['left']; ?>">
            <input type="hidden" name="right" value="<?= $_POST['right']; ?>">
            <input type="submit" name="merge" value="<?= __('Merge right into left'); ?>" class="btn btn-sm btn-secondary"><br><br>

            <?php $treeMerge->show_pair($_POST['left'], $_POST['right'], 'manual'); ?><br>
        </form>
    <?php
    }
} elseif (isset($_POST['relatives'])) {
    // this creates the pages that cycle through the surrounding relatives that have to be checked for merging
    // the "surrounding relatives" array is created in all merge modes (in the merge_them function) )and saved to the database

    // if skip - delete pair from database string
    if (isset($_POST['skip_rel'])) {
        // remove first entry (that the admin decided not to merge) from string
        $relcomp = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'rel_merge_" . $trees['tree_id'] . "'");
        $relcompDb = $relcomp->fetch(PDO::FETCH_OBJ);        // database row: I23@I300;I54@I304;I34@I430;
        $firstsemi = strpos($relcompDb->setting_value, ';') + 1;
        $string = substr($relcompDb->setting_value, $firstsemi);
        $db_functions->update_settings('rel_merge_' . $trees['tree_id'], $string);
        $trees['relatives_merge'] = $string;
    }

    // merge
    if (isset($_POST['rela'])) {  // the merge button was used
        $left = $_POST['left'];
        $right = $_POST['right'];
        $treeMerge->merge_them($left, $right, "relatives");
    }

    $relcomp = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'rel_merge_" . $trees['tree_id'] . "'");
    $relcompDb = $relcomp->fetch(PDO::FETCH_OBJ);        // database row: I23@I300;I54@I304;I34@I430;

    if ($relcompDb->setting_value != '') {
        if (!isset($_POST['swap'])) {
            $allpairs = explode(';', $relcompDb->setting_value);  // $allpairs[0]:  I23@I300
            $pair = explode('@', $allpairs[0]); // $pair[0]:  I23;
            $lft = $pair[0];  // I23
            $rght = $pair[1]; // I300

            $leftDb = $db_functions->get_person($lft);
            $left = $leftDb->pers_id;

            $rightDb = $db_functions->get_person($rght);
            $right = $rightDb->pers_id;
        } else {  // "switch left-right" button used"
            $left = $_POST['left'];
            $right = $_POST['right'];
        }
    ?>
        <br>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>

        <!-- button skip -->
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="hidden" name="skip_rel" value="1">
            <input type="submit" name="relatives" value="<?= __('Skip to next'); ?>" class="btn btn-sm btn-success">
        </form>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="hidden" name="swap" value="1">
            <input type="hidden" name="left" value="<?= $right; ?>">
            <input type="hidden" name="right" value="<?= $left; ?>">
            <input type="submit" name="relatives" value="<?= __('<- Switch left and right ->'); ?>" class="btn btn-sm btn-success">
        </form>

        <!-- button merge -->
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="hidden" name="left" value="<?= $left; ?>">
            <input type="hidden" name="right" value="<?= $right; ?>">
            <input type="hidden" name="rela" value="1">
            <input type="submit" name="relatives" value="<?= __('Merge right into left'); ?>" class="btn btn-sm btn-secondary">
            <br><br>
            <?php $treeMerge->show_pair($left, $right, 'relatives'); ?><br>
        </form>
    <?php } else { ?>
        <br><br><?= __('No more surrounding relatives to check'); ?><br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php
    }
} elseif (isset($_POST['merge'])) {
    // do merge and allow to continue with comparing duplicates
    // this is called up by the "Merge" button in manual and duplicate merge modes

    if (isset($_POST['manu'])) {
        $left = $_POST['left'];
        $right = $_POST['right'];
        $treeMerge->merge_them($left, $right, "man_dupl"); // merge_them is called in manual/duplicate mode
    } elseif (isset($_POST['dupl'])) { // duplicate merging
        $nr = $_SESSION['present_compare_' . $trees['tree_id']];
        $comp_set = explode(';', $_SESSION['dupl_arr_' . $trees['tree_id']][$nr]);
        $left = $comp_set[0];
        $right = $comp_set[1];
        $treeMerge->merge_them($left, $right, "man_dupl"); // merge_them is called in manual/duplicate mode
    }
} elseif (isset($_POST['duplicate'])) {
    // this is called when the "duplicate merge" button is used on the duplicate merge page
    // it creates the dupl_arr array with all duplicates found

    echo __('Please wait while duplicate list is generated');

    $famname_search = '';
    if (isset($_POST['famname_search']) && $_POST['famname_search'] != "") {
        $famname_search = " AND pers_lastname = '" . $_POST['famname_search'] . "'";
    }
    $qry = "SELECT pers_id, pers_firstname, pers_lastname, pers_birth_date, pers_death_date FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "'" . $famname_search . " ORDER BY pers_id";
    $pers = $dbh->query($qry);
    unset($dupl_arr); // just to make sure...

    while ($persDb = $pers->fetch(PDO::FETCH_OBJ)) {
        // the exact phrasing of the query depends on the admin settings
        //$qry2 = "SELECT pers_id, pers_firstname, pers_lastname, pers_birth_date, pers_death_date
        //	FROM humo_persons WHERE pers_id > ".$persDb->pers_id;
        $qry2 = "SELECT pers_id, pers_firstname, pers_lastname, pers_birth_date, pers_death_date FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_id > " . $persDb->pers_id;
        if ($humo_option["merge_firstname"] == 'YES') {
            $qry2 .= " AND SUBSTR(pers_firstname,1," . $humo_option["merge_chars"] . ") = SUBSTR('" . $persDb->pers_firstname . "',1," . $humo_option["merge_chars"] . ")";
        } else {
            $qry2 .= " AND pers_firstname != '' AND SUBSTR(pers_firstname,1," . $humo_option["merge_chars"] . ") = SUBSTR('" . $persDb->pers_firstname . "',1," . $humo_option["merge_chars"] . ")";
        }
        if ($humo_option["merge_lastname"] == 'YES') {
            $qry2 .= " AND pers_lastname ='" . $persDb->pers_lastname . "' ";
        } else {
            $qry2 .= " AND pers_lastname != '' AND pers_lastname ='" . $persDb->pers_lastname . "' ";
        }

        if ($humo_option["merge_dates"] == "YES") {
            $qry2 .= " AND (pers_birth_date ='" . $persDb->pers_birth_date . "' OR pers_birth_date ='' OR '" . $persDb->pers_birth_date . "'='') ";
            $qry2 .= " AND (pers_death_date ='" . $persDb->pers_death_date . "' OR pers_death_date ='' OR '" . $persDb->pers_death_date . "'='') ";
        } else {
            $qry2 .= " AND (( pers_birth_date != '' AND pers_birth_date ='" . $persDb->pers_birth_date . "' AND !(pers_death_date != '" . $persDb->pers_death_date . "'))
                OR
                (  pers_death_date != '' AND pers_death_date ='" . $persDb->pers_death_date . "' AND !(pers_birth_date != '" . $persDb->pers_birth_date . "')) )";
        }

        $pers2 = $dbh->query($qry2);
        if ($pers2) {
            while ($pers2Db = $pers2->fetch(PDO::FETCH_OBJ)) {
                $dupl_arr[] = $persDb->pers_id . ';' . $pers2Db->pers_id;
            }
        }
    }
    if (isset($dupl_arr)) {
        $_SESSION['dupl_arr_' . $trees['tree_id']] = $dupl_arr;
        $_SESSION['present_compare_' . $trees['tree_id']] = -1;
    ?>
        <!-- possible duplicates found -->
        <br><?= __('Possible duplicates found: ') . count($dupl_arr); ?><br><br>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" name="duplicate_compare" value="<?= __('Start comparing duplicates'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } else { ?>
        <br><?= __('No duplicates found. Duplicate merge and Automatic merge won\'t result in merges!'); ?><br>
        <?= __('You can try one of the other merge options'); ?><br><br>

        &nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php
    }
} elseif (isset($_POST['auto_merge'])) {
    // this checks the persons that can be merged automatically and merges them with the "merge_them" function

    echo '<br>' . __('Please wait while the automatic merges are processed...') . '<br>';
    $merges = 0;
    $qry = "SELECT pers_id, pers_lastname, pers_firstname, pers_birth_date, pers_death_date, pers_famc
        FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "'
        AND pers_lastname !=''
        AND pers_firstname !=''
        AND (pers_birth_date !='' OR pers_death_date !='')
        AND pers_famc !='' ORDER BY pers_id";
    $pers = $dbh->query($qry);
    while ($persDb = $pers->fetch(PDO::FETCH_OBJ)) {
        $qry2 = "SELECT pers_id, pers_lastname, pers_firstname, pers_birth_date, pers_death_date, pers_famc FROM humo_persons
            WHERE pers_tree_id='" . $trees['tree_id'] . "'
            AND pers_id > " . $persDb->pers_id . "
            AND (pers_lastname !='' AND pers_lastname = '" . $persDb->pers_lastname . "')
            AND (pers_firstname !='' AND pers_firstname = '" . $persDb->pers_firstname . "')
            AND ((pers_birth_date !='' AND pers_birth_date ='" . $persDb->pers_birth_date . "')
                OR (pers_death_date !='' AND pers_death_date ='" . $persDb->pers_death_date . "'))
            AND pers_famc !='' ORDER BY pers_id";

        $pers2 = $dbh->query($qry2);
        if ($pers2) {
            while ($pers2Db = $pers2->fetch(PDO::FETCH_OBJ)) {
                // get the two families
                $qry = "SELECT fam_man, fam_woman, fam_marr_date FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber='" . $persDb->pers_famc . "'";
                $fam1 = $dbh->query($qry);
                $fam1Db = $fam1->fetch(PDO::FETCH_OBJ);

                $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber='" . $pers2Db->pers_famc . "'";
                $fam2 = $dbh->query($qry);
                $fam2Db = $fam2->fetch(PDO::FETCH_OBJ);

                if ($fam1->rowCount() > 0 && $fam2->rowCount() > 0) {
                    $go = 1;
                    if ($humo_option["merge_parentsdate"] == 'YES') {
                        // we want to check for wedding date of parents
                        if ($fam1Db->fam_marr_date != '' && $fam1Db->fam_marr_date == $fam2Db->fam_marr_date) {
                            $go = 1;
                        } else {
                            $go = 0;  // no wedding date or no match --> no merge!
                        }
                    }

                    if ($go) {
                        // no use doing all this if the marriage date doesn't match
                        $qry = "SELECT pers_lastname, pers_firstname FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber='" . $fam1Db->fam_man . "'";
                        $fath1 = $dbh->query($qry);
                        $fath1Db = $fath1->fetch(PDO::FETCH_OBJ);
                        $qry = "SELECT pers_lastname, pers_firstname FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber='" . $fam1Db->fam_woman . "'";
                        $moth1 = $dbh->query($qry);
                        $moth1Db = $moth1->fetch(PDO::FETCH_OBJ);

                        $qry = "SELECT pers_lastname, pers_firstname FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber='" . $fam2Db->fam_man . "'";
                        $fath2 = $dbh->query($qry);
                        $fath2Db = $fath2->fetch(PDO::FETCH_OBJ);
                        $qry = "SELECT pers_lastname, pers_firstname FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber='" . $fam2Db->fam_woman . "'";
                        $moth2 = $dbh->query($qry);
                        $moth2Db = $moth2->fetch(PDO::FETCH_OBJ);
                        if (($fath1->rowCount() > 0 && $moth1->rowCount() > 0 && $fath2->rowCount() > 0 and $moth2->rowCount() > 0) && ($fath1Db->pers_lastname != '' && $fath1Db->pers_lastname == $fath2Db->pers_lastname && $moth1Db->pers_lastname != '' && $moth1Db->pers_lastname == $moth2Db->pers_lastname && $fath1Db->pers_firstname != '' && $fath1Db->pers_firstname == $fath2Db->pers_firstname && $moth1Db->pers_firstname != '' && $moth1Db->pers_firstname == $moth2Db->pers_firstname)) {
                            $treeMerge->merge_them($persDb->pers_id, $pers2Db->pers_id, 'automatic');
                            $mergedlist[] = $persDb->pers_id;
                            $merges++;
                        }
                    }
                }
            }    // end while
        } // end "if($pers2)
    }

    if ($merges == 0) {
        echo '<br>' . __('No automatic merge options were found.') . '<br><br>';
    } else {
        echo '<br>' . __('Automatic merge completed') . ' ' . $merges . __(' merges were performed') . '<br><br>';
    }
    if ($trees['relatives_merge'] != '') {
    ?>
        <?= __('It is recommended to continue with <b>"Relatives merge"</b> to consider merging persons affected by previous merges that were performed.'); ?><br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" style="font-weight:bold;font-size:120%" name="relatives" value="<?= __('Relatives merge'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } else { ?>
        <?= __('You may wish to proceed with duplicate merge or manual merge.'); ?><br><br>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" name="duplicate_merge" value="<?= __('Duplicate merge'); ?>" class="btn btn-sm btn-secondary">
        </form>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" name="manual" value="<?= __('Manual merge'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } ?>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <form method="post" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="tree">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
        <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
    </form>

    <?php
    if (isset($mergedlist)) {
        echo '<br><br><b><u>' . __('These are the persons that were merged:') . '</u></b><br>';
        for ($i = 0; $i < count($mergedlist); $i++) {
            $resultDb = $db_functions->get_person_with_id($mergedlist[$i]);
            echo $resultDb->pers_lastname . ', ' . $resultDb->pers_firstname . ' ' . strtolower(str_replace("_", " ", $resultDb->pers_prefix)) . ' (#' . $resultDb->pers_gedcomnumber . ')<br>';
        }
    }
} else {
    // The default entry to the merge feature (the main screen) with the merge modes and settings
    ?>
    <br>
    <table class="table" style="width:98%;">
        <tr class="table-primary">
            <th colspan="2"><?= __('Merge Options'); ?></th>
        </tr>
        <tr>
            <td colspan="2" style="padding:10px">
                <?= __('<b>NOTE:</b> None of these buttons will cause immediate merging. You will first be presented with information and can then decide to make a merge.<br><br>
<b>TIP:</b> Start with automatic merge to get rid of all obvious merges. (If no automatic merge options are found, try the duplicate merge option).<br>
These will likely cause surrounding relatives to be found, so continue with the "Relatives merge" option.<br>
Once you finish that, most needed merges will have been performed. You can then use "Duplicate merge" to see if there are duplicates left to consider for merging.<br>
As a last resort you can perform manual merges.'); ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:center;text-align:center;width:200px">
                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="tree">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                    <input type="submit" style="min-width:150px" name="automatic" value="<?= __('Automatic merge'); ?>" class="btn btn-sm btn-success">
                </form>
            </td>
            <td>
                <?= __('You will be shown the set of strict criteria used for automatic merging and then you can decide whether to continue.'); ?>
                <!-- relatives merge option button (only shown as button if previous merges created a "surrounding relatives" array) -->
            </td>
        </tr>
        <tr>
            <td style="vertical-align:center;text-align:center;width:200px">
                <?php if ($trees['relatives_merge'] != '') { ?>
                    <form method="post" action="index.php" style="display : inline;">
                        <input type="hidden" name="page" value="tree">
                        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                        <input type="submit" style="min-width:150px" name="relatives" value="<?= __('Relatives merge'); ?>" class="btn btn-sm btn-success">
                    </form>
                <?php } else { ?>
                    <?= __('Relatives merge'); ?>
                <?php } ?>
            </td>
            <td>
                <?= __('This button will become available if you have made merges, and surrounding relatives (parents, children or spouses) have to be considered for merging too.<br>
By pressing this button, you can then continue to check the surrounding relatives, pair by pair, and merge them if necessary. If those merges will create additional surrounding relatives to consider, they will be automatically added to the list.<br>
Surrounding relatives are saved to the database and you can also return to it at a later stage.'); ?>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:center;text-align:center;width:200px">
                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="tree">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                    <input type="submit" style="min-width:150px" name="duplicate_merge" value="<?= __('Duplicate merge'); ?>" class="btn btn-sm btn-success">
                </form>
            </td>
            <td>
                <?= __('You will be presented, one after the other, with pairs of possible duplicates to consider for merging.<br>
After a merge you can switch to "relatives merge" and after that return to duplicate search where you left off.'); ?>
            </td>
        </tr>
        <tr>
            <td style="min-height:50px;vertical-align:center;text-align:center;width:200px">
                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="tree">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                    <input type="submit" style="min-width:150px" name="manual" value="<?= __('Manual merge'); ?>" class="btn btn-sm btn-success">
                </form>
            </td>
            <td><?= __('You can pick two persons out of the database to consider for merging.'); ?></td>
        </tr>
        <tr>
            <td style="vertical-align:center;text-align:center;width:200px">
                <form method="post" action="index.php" style="display : inline;">
                    <input type="hidden" name="page" value="tree">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                    <input type="submit" style="min-width:150px" name="settings" value="<?= __('Settings'); ?>" class="btn btn-sm btn-success">
                </form>
            </td>
            <td><?= __('Here you can change the default filters for the different merge options.'); ?></td>
        </tr>
    </table>
<?php
}