<?php

/* Merge data functions are made by Yossi.
 * 22 dec 2017 Huub: Updated merge data functions with correct person and family counter for main page.
 * 09 aug 2023 Huub: Seperated several items from this file into views (preparing MVC)
 * 18 feb 2024 Huub: Moved class to view script.
 */

// *** tree_merge is the function that navigates all merge screens and options ***
$db_functions->set_tree_id($trees['tree_id']);

$personPrivacy = new PersonPrivacy();
$personName = new PersonName();

// the following creates the pages that cycle through all duplicates that are stored in the dupl_arr array
// the pages themselves are presented with the "show_pair function"
if (isset($_POST['duplicate_compare'])) {
    if (!isset($_POST['no_increase'])) {  // no increase is used if "switch left and right" was chosen
        $nr = ++$_SESSION['present_compare_' . $trees['tree_id']]; // present_compare is the pair that has to be shown next - saved to session
    } else {
        $nr = $_SESSION['present_compare_' . $trees['tree_id']];
    }
    if (isset($_POST['choice_nr'])) {  // choice number is the number from the "skip to" pulldown - saved to a session
        $nr = $_POST['choice_nr'];
        $_SESSION['present_compare_' . $trees['tree_id']] = $_POST['choice_nr'];
    }

    // make sure the persons in the array are still there (in case in the mean time someone was merged)
    // after all, one person may be compared to more than one other person!
    while ($_SESSION['present_compare_' . $trees['tree_id']] < count($_SESSION['dupl_arr_' . $trees['tree_id']])) {
        $comp_set = explode(';', $_SESSION['dupl_arr_' . $trees['tree_id']][$nr]);
        $res = $db_functions->get_person_with_id($comp_set[0]);
        $res2 = $db_functions->get_person_with_id($comp_set[1]);
        if (!$res || !$res2) { // one or 2 persons are missing - continue with next pair
            $nr = ++$_SESSION['present_compare_' . $trees['tree_id']];
            continue; // look for next pair in array
        } else {
            // we have got a valid pa
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
                <?php show_pair($left, $right, 'duplicate'); ?>
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
}

// this creates the screen for manual merge. the pair itself is presented with the "show_pair" function
elseif (isset($_POST['manual_compare'])) {

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

            <?php show_pair($_POST['left'], $_POST['right'], 'manual'); ?><br>
        </form>
    <?php
    }
}

// this creates the pages that cycle through the surrounding relatives that have to be checked for merging
// the "surrounding relatives" array is created in all merge modes (in the merge_them function) )and saved to the database
elseif (isset($_POST['relatives'])) {

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
        merge_them($left, $right, "relatives");
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
            <?php show_pair($left, $right, 'relatives'); ?><br>
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
}

// this is called up by the "Merge" button in manual and duplicate merge modes
elseif (isset($_POST['merge'])) { // do merge and allow to continue with comparing duplicates

    if (isset($_POST['manu'])) {
        $left = $_POST['left'];
        $right = $_POST['right'];
        merge_them($left, $right, "man_dupl"); // merge_them is called in manual/duplicate mode
    } elseif (isset($_POST['dupl'])) { // duplicate merging
        $nr = $_SESSION['present_compare_' . $trees['tree_id']];
        $comp_set = explode(';', $_SESSION['dupl_arr_' . $trees['tree_id']][$nr]);
        $left = $comp_set[0];
        $right = $comp_set[1];
        merge_them($left, $right, "man_dupl"); // merge_them is called in manual/duplicate mode
    }
}

//This is called when you push the "Duplicate merge option on the main merge screen.
// It gives an explanation and also offers to continue with previous dupl merge, if one was already done in this session
elseif (isset($_POST['duplicate_choices'])) {

    echo '<br>';
    echo __('With "Duplicate merge" the program will look for all persons with a fixed set of criteria for identical data.
These are:
<ul><li>Same last name and same first name.<br>
By default, people with blank first or last names are included. You can disable that under "Settings" in the main menu.</li>
<li>Same birthdate or same deathdate.<br>
By default, when one or both persons have a missing birth/death date they will still be included when the name matches.
You can change that under "Settings" in the main menu.</li></ul>
The found duplicates will be presented to you, one pair after the other, with their details.<br>
You can then decide whether to accept the default merge, or change which details of the right person will be merged into the left.<br>
If you decide not to merge this pair, you can "skip" to the next pair.<br>
If after the merge there are surrounding relatives that might need merging too, you will be urged to move to "Relatives merge"<br>
If you have interrupted a duplicate merge in this session (for example to move to "relatives merge"),
this page will also show a "Continue duplicate merge" button so you can continue where you left off.<br>
<b>Please note that generating the duplicates may take some time, depending on the size of the tree.</b>');

    echo '<br><br>';
    if (isset($_SESSION['dupl_arr_' . $trees['tree_id']])) {
    ?>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="tree">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
            <input type="submit" style="min-width:150px" name="duplicate_compare" value="<?= __('Continue duplicate merge'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } ?>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <form method="post" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="tree">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
        &nbsp;&nbsp;<?= __('Find doubles only within this family name (optional)'); ?>: <input type="text" name="famname_search">&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="submit" style="min-width:150px" name="duplicate" value="<?= __('Generate new duplicate merge'); ?>" class="btn btn-sm btn-success">
    </form>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <form method="post" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="tree">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
        <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
    </form>
    <?php
}

// this is called when the "duplicate merge" button is used on the duplicate_choices page
// it creates the dupl_arr array with all duplicates found
elseif (isset($_POST['duplicate'])) {
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
}

// this is the page where one can choose two people from all persons in the tree for manual merging
// the pairs will be presented by the show_pair function
elseif (isset($_POST['manual']) || isset($_POST["search1"]) || isset($_POST["search2"]) || isset($_POST["switch"])) {
    // TODO: improve search of persons. Example, see: relationship calculator.
    ?>
    <br><?= __('Pick the two persons you want to check for merging'); ?>. <?= __('You can enter names (or part of names) or GEDCOM no. (INDI), or leave boxes empty'); ?><br>
    <?= __('<b>TIP: when you click "search" with all boxes left empty you will get a list with all persons in the database. (May take a few seconds)</b>'); ?><br><br>

    <?php
    // ===== BEGIN SEARCH BOX SYSTEM
    if (!isset($_POST["search1"]) && !isset($_POST["search2"]) && !isset($_POST["manual_compare"]) && !isset($_POST["switch"])) {
        // no button pressed: this is a fresh entry from frontpage link: start clean search form
        $_SESSION["search1"] = '';
        $_SESSION["search2"] = '';
        $_SESSION['rel_search_firstname'] = '';
        $_SESSION['rel_search_lastname'] = '';
        $_SESSION['rel_search_firstname2'] = '';
        $_SESSION['rel_search_lastname2'] = '';
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
        $temp = $_SESSION['rel_search_firstname'];
        $_SESSION['rel_search_firstname'] = $_SESSION['rel_search_firstname2'];
        $_SESSION['rel_search_firstname2'] = $temp;
        $temp = $_SESSION['rel_search_lastname'];
        $_SESSION['rel_search_lastname'] = $_SESSION['rel_search_lastname2'];
        $_SESSION['rel_search_lastname2'] = $temp;
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

    $search_firstname = '';
    if (isset($_POST["search_firstname"]) && !isset($_POST["switch"])) {
        $search_firstname = trim($safeTextDb->safe_text_db($_POST['search_firstname']));
        $_SESSION['rel_search_firstname'] = $search_firstname;
    }
    if (isset($_SESSION['rel_search_firstname'])) {
        $search_firstname = $_SESSION['rel_search_firstname'];
    }

    $search_lastname = '';
    if (isset($_POST["search_lastname"]) && !isset($_POST["switch"])) {
        $search_lastname = trim($safeTextDb->safe_text_db($_POST['search_lastname']));
        $_SESSION['rel_search_lastname'] = $search_lastname;
    }
    if (isset($_SESSION['rel_search_lastname'])) {
        $search_lastname = $_SESSION['rel_search_lastname'];
    }

    $search_indi = '';
    if (isset($_POST["search_indi"]) && !isset($_POST["switch"])) {
        $search_indi = trim($safeTextDb->safe_text_db($_POST['search_indi']));
        $_SESSION['search_indi'] = $search_indi;
    }
    if (isset($_SESSION['search_indi'])) {
        $search_indi = $_SESSION['search_indi'];
    }

    $search_firstname2 = '';
    if (isset($_POST["search_firstname2"]) && !isset($_POST["switch"])) {
        $search_firstname2 = trim($safeTextDb->safe_text_db($_POST['search_firstname2']));
        $_SESSION['rel_search_firstname2'] = $search_firstname2;
    }
    if (isset($_SESSION['rel_search_firstname2'])) {
        $search_firstname2 = $_SESSION['rel_search_firstname2'];
    }

    $search_lastname2 = '';
    if (isset($_POST["search_lastname2"]) && !isset($_POST["switch"])) {
        $search_lastname2 = trim($safeTextDb->safe_text_db($_POST['search_lastname2']));
        $_SESSION['rel_search_lastname2'] = $search_lastname2;
    }
    if (isset($_SESSION['rel_search_lastname2'])) {
        $search_lastname2 = $_SESSION['rel_search_lastname2'];
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

    <form method="post" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="tree">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
        <table class="humo" style="text-align:center; width:100%;">
            <tr class="table_header">
                <td>&nbsp;</td>
                <td><?= __('First name'); ?></td>
                <td><?= __('Last name'); ?></td>
                <td><?= __('GEDCOM no. ("I43")'); ?></td>
                <td><?= __('Search'); ?></td>
                <td colspan=2><?= __('Pick a name from search results'); ?></td>
                <td><?= __('Show details'); ?></td>
            </tr>

            <!-- First person -->
            <tr>
                <td style="white-space:nowrap"><?= __('Person'); ?> 1</td>
                <td>
                    <input type="text" name="search_firstname" value="<?= $search_firstname; ?>" size="15">
                </td>
                <td>
                    &nbsp;<input type="text" name="search_lastname" value="<?= $search_lastname; ?>" size="15">
                </td>
                <td>
                    <input type="text" name="search_indi" value="<?= $search_indi; ?>" size="10">
                </td>
                <td>
                    &nbsp; <input type="submit" name="search1" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </td>
                <td>
                    <?php
                    $len = 230;  // length of name pulldown box

                    if (isset($_SESSION["search1"]) && $_SESSION["search1"] == 1) {
                        $indi_string = '';
                        if (isset($_SESSION["search_indi"]) && $_SESSION["search_indi"] != "") {
                            // make sure it works with "I436", "i436" and "436"
                            $indi = (substr($search_indi, 0, 1) === "I" || substr($search_indi, 0, 1) === "i") ? strtoupper($search_indi) : "I" . $search_indi;
                            $indi_string = " AND pers_gedcomnumber ='" . $indi . "' ";
                        }
                        $search_qry = "SELECT * FROM humo_persons
                            WHERE pers_tree_id='" . $trees['tree_id'] . "' AND CONCAT(REPLACE(pers_prefix,'_',' '),pers_lastname)
                            LIKE '%" . $search_lastname . "%' AND pers_firstname LIKE '%" . $search_firstname . "%' " . $indi_string . "
                            ORDER BY pers_lastname, pers_firstname";
                        $search_result = $dbh->query($search_qry);
                        if ($search_result) {
                            if ($search_result->rowCount() > 0) {
                    ?>
                                <select size="1" name="left" style="width:<?= $len; ?>px">
                                    <?php
                                    while ($searchDb = $search_result->fetch(PDO::FETCH_OBJ)) {
                                        $privacy = $personPrivacy->get_privacy($searchDb);
                                        $name = $personName->get_person_name($searchDb, $privacy);
                                        if ($name["show_name"]) {
                                            echo '<option';
                                            if (isset($left) && ($searchDb->pers_id == $left && !(isset($_POST["search1"]) && $search_lastname == '' && $search_firstname == ''))) {
                                                echo ' selected';
                                            }
                                            echo ' value="' . $searchDb->pers_id . '">' . $name["index_name"] . ' [' . $searchDb->pers_gedcomnumber . ']</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            <?php } else { ?>
                                <select size="1" name="notfound" value="1" style="width:<?= $len; ?>px">
                                    <option><?= __('Person not found'); ?></option>
                                </select>
                        <?php
                            }
                        }
                    } else {
                        ?>
                        <select size="1" name="left" style="width:<?= $len; ?>px">
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
                    <input type="text" name="search_firstname2" value="<?= $search_firstname2; ?>" size="15">
                </td>
                <td>
                    &nbsp;<input type="text" name="search_lastname2" value="<?= $search_lastname2; ?>" size="15">
                </td>
                <td>
                    <input type="text" name="search_indi2" value="<?= $search_indi2; ?>" size="10">
                </td>
                <td>
                    &nbsp; <input type="submit" name="search2" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </td>
                <td>
                    <?php
                    if (isset($_SESSION["search2"]) && $_SESSION["search2"] == 1) {
                        $indi_string2 = '';
                        if (isset($_SESSION["search_indi2"]) && $_SESSION["search_indi2"] != "") {
                            // make sure it works with "I436", "i436" and "436"
                            $indi2 = (substr($search_indi2, 0, 1) === "I" || substr($search_indi2, 0, 1) === "i") ? strtoupper($search_indi2) : "I" . $search_indi2;
                            $indi_string2 = " AND pers_gedcomnumber ='" . $indi2 . "' ";
                        }
                        $search_qry = "SELECT * FROM humo_persons
                            WHERE pers_tree_id='" . $trees['tree_id'] . "' AND CONCAT(REPLACE(pers_prefix,'_',' '),pers_lastname)
                            LIKE '%" . $search_lastname2 . "%' AND pers_firstname LIKE '%" . $search_firstname2 . "%' " . $indi_string2 . "
                            ORDER BY pers_lastname, pers_firstname";
                        $search_result2 = $dbh->query($search_qry);
                        if ($search_result2) {
                            if ($search_result2->rowCount() > 0) {
                    ?>
                                <select size="1" name="right" style="width:<?= $len; ?>px">
                                    <?php
                                    while ($searchDb2 = $search_result2->fetch(PDO::FETCH_OBJ)) {
                                        $privacy = $personPrivacy->get_privacy($searchDb2);
                                        $name = $personName->get_person_name($searchDb2, $privacy);
                                        if ($name["show_name"]) {
                                            echo '<option';
                                            if (isset($right) && ($searchDb2->pers_id == $right && !(isset($_POST["search2"]) && $search_lastname2 == '' && $search_firstname2 == ''))) {
                                                echo ' selected';
                                            }
                                            echo ' value="' . $searchDb2->pers_id . '">' . $name["index_name"] . ' [' . $searchDb2->pers_gedcomnumber . ']</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            <?php } else { ?>
                                <select size="1" name="notfound" value="1" style="width:<?= $len; ?>px">
                                    <option><?= __('Person not found'); ?></option>
                                </select>
                        <?php
                            }
                        }
                    } else {
                        ?>
                        <select size="1" name="right" style="width:<?= $len; ?>px">
                            <option></option>
                        </select>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </form>
<?php
}

// this is the screen that will show when you choose "automatic merge" from the main merge page
elseif (isset($_POST['automatic'])) {

    echo '<br>';
    echo __('Automatic merge will go through the entire database and merge all persons who comply with ALL the following conditions:<br>
<ul><li>Both persons have a first name and a last name and they are identical</li>
<li>Both persons have parents with first and last names and those names are identical</li>
<li>Both persons\' parents have a marriage date and it is identical (This can be disabled under "Settings")</li>
<li>Both persons have a birth date and it is identical OR both have a death date and it is identical</li></ul>
<b>Please note that the automatic merge may take quite some time, depending on the size of the database and the number of merges.</b><br>
You will be notified of results as the action is completed');
    echo '<br><br>';
?>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <form method="post" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="tree">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
        <input type="submit" name="auto_merge" value="<?= __('Start automatic merge'); ?>" class="btn btn-sm btn-secondary">
    </form>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <form method="post" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="tree">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
        <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
    </form>
    <?php
}

// this checks the persons that can be merged automatically and merges them with the "merge_them" function
elseif (isset($_POST['auto_merge'])) {
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
                    if ($humo_option["merge_parentsdate"] == 'YES') { // we want to check for wedding date of parents
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
                            // MERGE THEM !!
                            merge_them($persDb->pers_id, $pers2Db->pers_id, 'automatic');
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
            <input type="submit" name="duplicate_choices" value="<?= __('Duplicate merge'); ?>" class="btn btn-sm btn-secondary">
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
    if (isset($mergedlist)) { // there is a list of merged persons
        echo '<br><br><b><u>' . __('These are the persons that were merged:') . '</u></b><br>';
        for ($i = 0; $i < count($mergedlist); $i++) {
            $resultDb = $db_functions->get_person_with_id($mergedlist[$i]);
            echo $resultDb->pers_lastname . ', ' . $resultDb->pers_firstname . ' ' . strtolower(str_replace("_", " ", $resultDb->pers_prefix)) . ' (#' . $resultDb->pers_gedcomnumber . ')<br>';
        }
    }
}

// The settings screen with "Save" and "Reset" buttons and explanations
elseif (isset($_POST['settings']) || isset($_POST['reset'])) {
    // *** Re-read variables after changing them ***
    // *** Don't use include_once! Otherwise the old value will be shown ***
    include_once(__DIR__ . "/../../include/generalSettings.php");
    $generalSettings = new GeneralSettings();
    //$user = $generalSettings->get_user_settings($dbh);
    $humo_option = $generalSettings->get_humo_option($dbh);
?>

    <form method="post" action="index.php" class="my-2">
        <input type="hidden" name="page" value="tree">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">

        <table class="humo" style="width:900px;">
            <tr class="table_header">
                <th colspan="3"><?= __('Merge filter settings'); ?></th>
            </tr>

            <tr>
                <th colspan="3"><?= __('General'); ?></th>
            </tr>

            <tr>
                <td><?= __('Max characters to match firstname:'); ?></td>
                <td>
                    <input type="text" name="merge_chars" value="<?= $humo_option["merge_chars"]; ?>" size="1">
                </td>
                <td>
                    <?= __('In different trees, first names may be listed differently: Thomas Julian Booth, Thomas J. Booth, Thomas Booth etc. By default a match of the first 10 characters of the first name will be considered a match. You can change this to another value. Try and find the right balance: if you set a low number of chars you will get many unwanted possible matches. If you set it too high, you may miss possible matches as in the example names above.'); ?>
                </td>
            </tr>

            <tr>
                <th colspan="3"><?= __('Duplicate merge'); ?></th>
            </tr>

            <tr>
                <td><?= __('include blank lastnames'); ?></td>
                <td>
                    <select size="1" name="merge_lastname">
                        <option value="YES"><?= __('Yes'); ?></option>
                        <option value="NO" <?= $humo_option["merge_lastname"] == 'NO' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                    </select>
                </td>
                <td>
                    <?= __('By default two persons with missing lastnames will be included as possible duplicates. Two persons called "John" without lastname will be considered a possible match. If you have many cases like this you could get a very long list of possible duplicates and you might want to disable this, so only persons with lastnames will be included.'); ?>
                </td>
            </tr>

            <tr>
                <td><?= __('include blank firstnames'); ?></td>
                <td>
                    <select size="1" name="merge_firstname">
                        <option value="YES"><?= __('Yes'); ?></option>
                        <option value="NO" <?= $humo_option["merge_firstname"] == 'NO' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                    </select>
                </td>
                <td>
                    <?= __('Same as above, but for first names. When enabled (default), all persons called "Smith" without first name will be considered possible duplicates of each other. If you have many cases like this it could give you a long list and you might want to disable it.'); ?>
                </td>
            </tr>

            <tr>
                <td><?= __('include blank dates'); ?></td>
                <td>
                    <select size="1" name="merge_dates">
                        <option value="YES"><?= __('Yes'); ?></option>
                        <option value="NO" <?= $humo_option["merge_dates"] == 'NO' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                    </select>
                </td>
                <td>
                    <?= __('By default, two persons with identical names, but with one or both missing birth/death dates are considered possible duplicates. In certain trees this can give a long list of possible duplicates. You can choose to disable this so only persons who both have a birth or death date and this date is identical, will be considered a possible match. This can drastically cut down the number of possible duplicates, but of course you may also miss out on pairs that actually are duplicates.'); ?>
                </td>
            </tr>

            <tr>
                <th colspan="3"><?= __('Automatic merge'); ?></th>
            </tr>

            <tr>
                <td><?= __('include parents marriage date:'); ?></td>
                <td>
                    <select size="1" name="merge_parentsdate">
                        <option value="YES"><?= __('Yes'); ?></option>
                        <option value="NO" <?= $humo_option["merge_parentsdate"] == 'NO' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                    </select>
                </td>
                <td>
                    <?= __('Automatic merging is a dangerous business. Therefore many clauses are used to make sure the persons are indeed identical. Besides identical names, identical birth or death dates and identical names of parents, also the parents\' wedding date is included. If you consider this too much and rely on the above clauses, you can disable this.'); ?>
                </td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td style="text-align:center">
                    <input type="submit" name="settings" value="<?= __('Save'); ?>" class="btn btn-success">
                    &nbsp;&nbsp;&nbsp;<input type="submit" name="reset" value="<?= __('Reset'); ?>" class="btn btn-secondary">
                </td>
            </tr>

        </table>
    </form>
<?php
}

// The default entry to the merge feature (the main screen) with the merge modes and settings
else {
?>
    <br>
    <table class="humo" style="width:98%;">
        <tr class="table_header">
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
                    <input type="submit" style="min-width:150px" name="duplicate_choices" value="<?= __('Duplicate merge'); ?>" class="btn btn-sm btn-success">
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

/**
 * "show_pair" is the function that presents the data of two persons to be merged
 * with the possibility to determine what information is passed from left to right
 */
function show_pair($left_id, $right_id, $mode)
{
    global $dbh, $db_functions, $trees;

    // get data for left person
    $leftDb = $db_functions->get_person_with_id($left_id);

    $personPrivacy = new PersonPrivacy();
    $personName = new PersonName();

    $spouses1 = '';
    $children1 = '';
    if ($leftDb->pers_fams) {
        $fams = explode(';', $leftDb->pers_fams);
        foreach ($fams as $value) {
            $famDb = $db_functions->get_family($value);

            $spouse_ged = $famDb->fam_man == $leftDb->pers_gedcomnumber ? $famDb->fam_woman : $famDb->fam_man;
            $spouseDb = $db_functions->get_person($spouse_ged);
            $privacy = $personPrivacy->get_privacy($spouseDb);
            $name = $personName->get_person_name($spouseDb, $privacy);
            $spouses1 .= $name["standard_name"] . '<br>';

            if ($famDb->fam_children) {
                $child = explode(';', $famDb->fam_children);
                foreach ($child as $ch_value) {
                    $childDb = $db_functions->get_person($ch_value);
                    $privacy = $personPrivacy->get_privacy($childDb);
                    $name = $personName->get_person_name($childDb, $privacy);
                    $children1 .= $name["standard_name"] . '<br>';
                }
            }
        }
        $spouses1 = substr($spouses1, 0, -4); // take off last <br>
        $children1 = substr($children1, 0, -4); // take of last <br>
    }

    $father1 = '';
    $mother1 = '';
    if ($leftDb->pers_famc) {
        $qry2 = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $leftDb->pers_famc . "'";
        $parents = $dbh->query($qry2);
        $parentsDb = $parents->fetch(PDO::FETCH_OBJ);

        $fatherDb = $db_functions->get_person($parentsDb->fam_man);
        $privacy = $personPrivacy->get_privacy($fatherDb);
        $name = $personName->get_person_name($fatherDb, $privacy);
        $father1 .= $name["standard_name"] . '<br>';

        $motherDb = $db_functions->get_person($parentsDb->fam_woman);
        $privacy = $personPrivacy->get_privacy($motherDb);
        $name = $personName->get_person_name($motherDb, $privacy);
        $mother1 .= $name["standard_name"] . '<br>';
    }

    // get data for right person
    $rightDb = $db_functions->get_person_with_id($right_id);

    $spouses2 = '';
    $children2 = '';
    if ($rightDb->pers_fams) {
        $fams = explode(';', $rightDb->pers_fams);
        foreach ($fams as $value) {
            $famDb = $db_functions->get_family($value);
            $spouse_ged = $famDb->fam_man == $rightDb->pers_gedcomnumber ? $famDb->fam_woman : $famDb->fam_man;
            $spouseDb = $db_functions->get_person($spouse_ged);
            $privacy = $personPrivacy->get_privacy($spouseDb);
            $name = $personName->get_person_name($spouseDb, $privacy);
            $spouses2 .= $name["standard_name"] . '<br>';

            if ($famDb->fam_children) {
                $child = explode(';', $famDb->fam_children);
                foreach ($child as $ch_value) {
                    $childDb = $db_functions->get_person($ch_value);
                    $privacy = $personPrivacy->get_privacy($childDb);
                    $name = $personName->get_person_name($childDb, $privacy);
                    $children2 .= $name["standard_name"] . '<br>';
                }
            }
        }
        $spouses2 = substr($spouses2, 0, -4); // take off last <br>
        $children2 = substr($children2, 0, -4); // take of last <br>
    }

    $father2 = '';
    $mother2 = '';
    if ($rightDb->pers_famc && $rightDb->pers_famc != "") {
        $qry2 = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $rightDb->pers_famc . "'";
        $parents = $dbh->query($qry2);
        $parentsDb = $parents->fetch(PDO::FETCH_OBJ);

        $fatherDb = $db_functions->get_person($parentsDb->fam_man);
        $privacy = $personPrivacy->get_privacy($fatherDb);
        $name = $personName->get_person_name($fatherDb, $privacy);
        $father2 .= $name["standard_name"] . '<br>';

        $motherDb = $db_functions->get_person($parentsDb->fam_woman);
        $privacy = $personPrivacy->get_privacy($motherDb);
        $name = $personName->get_person_name($motherDb, $privacy);
        $mother2 .= $name["standard_name"] . '<br>';
    }
?>

    <table style="width:900px;border:2px solid #d8d8d8">
        <tr class="table_header">
            <th style="vertical-align:top;font-size:130%" colspan="3">
                <?php if ($mode == "duplicate") { ?>
                    <?= __('Duplicate merge'); ?>
                <?php } elseif ($mode == "relatives") { ?>
                    <?= __('Surrounding relatives check'); ?>
                <?php } else { ?>
                    <?= __('Manual merge'); ?>
                <?php } ?>
            </th>
        </tr>

        <tr>
            <th style="width:150px;border-bottom:2px solid #a4a4a4;text-align:left">
                <?php
                if ($mode == 'duplicate') {
                    $num = $_SESSION['present_compare_' . $trees['tree_id']] + 1;
                    echo __('Nr. ') . $num . __(' of ') . count($_SESSION['dupl_arr_' . $trees['tree_id']]);
                } elseif ($mode = 'relatives') {
                    $rl = explode(';', $trees['relatives_merge']);
                    $rls = count($rl) - 1;
                    echo $rls . __(' relatives to check');
                }
                ?>
            </th>
            <th style="width:375px;border-bottom:2px solid #a4a4a4"> <?= __('Person 1: '); ?></th>
            <th style="width:375px;border-bottom:2px solid #a4a4a4"> <?= __('Person 2: '); ?></th>
        </tr>
        <tr style="background-color:#e6e6e6">
            <td style="font-weight:bold"><?= __('GEDCOM number (ID)'); ?></td>
            <td><?= $leftDb->pers_gedcomnumber; ?></td>
            <td><?= $rightDb->pers_gedcomnumber; ?></td>
        </tr>

        <?php
        show_regular($leftDb->pers_lastname, $rightDb->pers_lastname, __('last name'), 'l_name');
        show_regular($leftDb->pers_firstname, $rightDb->pers_firstname, __('first name'), 'f_name');
        //show_regular($leftDb->pers_callname,$rightDb->pers_callname,__('Nickname'),'c_name');
        show_regular($leftDb->pers_patronym, $rightDb->pers_patronym, __('patronym'), 'patr');
        show_regular($leftDb->pers_birth_date, $rightDb->pers_birth_date, __('birth date'), 'b_date');
        show_regular($leftDb->pers_birth_place, $rightDb->pers_birth_place, __('birth place'), 'b_place');
        show_regular($leftDb->pers_birth_time, $rightDb->pers_birth_time, __('birth time'), 'b_time');
        show_regular($leftDb->pers_bapt_date, $rightDb->pers_bapt_date, __('baptism date'), 'bp_date');
        show_regular($leftDb->pers_bapt_place, $rightDb->pers_bapt_place, __('baptism place'), 'bp_place');
        show_regular($leftDb->pers_death_date, $rightDb->pers_death_date, __('death date'), 'd_date');
        show_regular($leftDb->pers_death_place, $rightDb->pers_death_place, __('death place'), 'd_place');
        show_regular($leftDb->pers_death_time, $rightDb->pers_death_time, __('death time'), 'd_time');
        show_regular($leftDb->pers_death_cause, $rightDb->pers_death_cause, __('cause of death'), 'd_cause');
        show_regular($leftDb->pers_cremation, $rightDb->pers_cremation, __('cremation'), 'crem');
        show_regular($leftDb->pers_buried_date, $rightDb->pers_buried_date, __('burial date'), 'br_date');
        show_regular($leftDb->pers_buried_place, $rightDb->pers_buried_place, __('burial place'), 'br_place');
        show_regular($leftDb->pers_alive, $rightDb->pers_alive, __('alive'), 'alive');
        show_regular($leftDb->pers_religion, $rightDb->pers_religion, __('religion'), 'reli');
        show_regular($leftDb->pers_own_code, $rightDb->pers_own_code, __('own code'), 'code');
        show_regular($leftDb->pers_stillborn, $rightDb->pers_stillborn, __('stillborn'), 'stborn');
        show_regular_text($leftDb->pers_text, $rightDb->pers_text, __('general text'), 'text');
        show_regular_text($leftDb->pers_name_text, $rightDb->pers_name_text, __('name text'), 'n_text');
        show_regular_text($leftDb->pers_birth_text, $rightDb->pers_birth_text, __('birth text'), 'b_text');
        show_regular_text($leftDb->pers_bapt_text, $rightDb->pers_bapt_text, __('baptism text'), 'bp_text');
        show_regular_text($leftDb->pers_death_text, $rightDb->pers_death_text, __('death text'), 'd_text');
        show_regular_text($leftDb->pers_buried_text, $rightDb->pers_buried_text, __('burial text'), 'br_text');

        // *** functions to show events, sources and addresses ***
        show_events($leftDb->pers_gedcomnumber, $rightDb->pers_gedcomnumber);
        show_sources($leftDb->pers_gedcomnumber, $rightDb->pers_gedcomnumber);
        show_addresses($leftDb->pers_gedcomnumber, $rightDb->pers_gedcomnumber);

        //TEST *** Address by relation ***
        // A person can be married multiple times (left and right side). Probably needed to rebuild show_addresses scripts to show them seperately?
        //$r_fams = explode(';',$rightDb->pers_fams);
        //for($i=0;$i<count($r_fams);$i++) {
        //	echo $r_fams[$i].'! ';
        //	show_addresses('',$r_fams[$i]);
        //}
        ?>

        <tr>
            <td colspan=3 style="border-top:2px solid #a4a4a4;border-bottom:2px solid #a4a4a4;font-weight:bold"><?= __('Relatives'); ?>:</td>
        </tr>
        <tr style="background-color:#f2f2f2">
            <td style="font-weight:bold"><?= __('Spouse'); ?>:</td>
            <td><?= $spouses1; ?></td>
            <td><?= $spouses2; ?></td>
        </tr>
        <tr style="background-color:#e6e6e6">
            <td style="font-weight:bold"><?= __('Father'); ?>:</td>
            <td><?= $father1; ?></td>
            <td><?= $father2; ?></td>
        </tr>
        <tr style="background-color:#f2f2f2">
            <td style="font-weight:bold"><?= __('Mother'); ?>:</td>
            <td><?= $mother1; ?></td>
            <td><?= $mother2; ?></td>
        </tr>
        <tr style="background-color:#e6e6e6">
            <td style="font-weight:bold"><?= __('Children'); ?>:</td>
            <td><?= $children1; ?></td>
            <td><?= $children2; ?></td>
        </tr>
    </table>
    <?php
}

/**
 * show_regular is a function that places the regular items from humo_persons in the comparison table
 */
function show_regular($left_item, $right_item, $title, $name)
{
    global $dbh, $language, $color;
    if ($left_item || $right_item) {
        $color = $color == '#e6e6e6' ? '#f2f2f2' : '#e6e6e6';
    ?>
        <tr style="background-color:<?= $color; ?>">
            <td style="font-weight:bold"><?= ucfirst($title); ?>:</td>

            <?php
            if ($left_item) {
                if ($name == 'crem' && $left_item == '1') {
                    $left_item = 'Yes';
                }
                if ($name == 'fav' && $left_item == '1') {
                    $left_item = 'Yes';
                }
                if ($name == 'stborn' && $left_item == 'y') {
                    $left_item = 'Yes';
                }
            }
            ?>
            <td><input type="radio" name="<?= $name; ?>" value="1" <?= $left_item ? 'checked' : ''; ?>> <?= $left_item; ?></td>

            <?php
            if ($name == 'crem' && $right_item == '1') {
                $right_item = 'Yes';
            }
            if ($name == 'fav' && $right_item == '1') {
                $right_item = 'Yes';
            }
            if ($name == 'stborn' && $right_item == 'y') {
                $right_item = 'Yes';
            }
            ?>
            <td><input type="radio" name="<?= $name; ?>" value="2" <?= !$left_item ? 'checked' : ''; ?>> <?= $right_item; ?></td>
        </tr>
    <?php
    }
}

/**
 * show_regular_text is a function that places the regular text items from humoX_person in the comparison table
 */
function show_regular_text($left_item, $right_item, $title, $name)
{
    global $dbh, $trees, $language, $data2Db, $color;
    if ($right_item) {
        $color = $color == '#e6e6e6' ? '#f2f2f2' : '#e6e6e6';
    ?>
        <tr style="background-color:<?= $color; ?>">
            <td style="font-weight:bold"><?= $title; ?>:</td>
            <td>
                <?php
                $showtext = '';
                if ($left_item) {
                    $showtext = "[" . __('Read text') . "]";
                    if (substr($left_item, 0, 2) === "@N") {  // not plain text but @N23@ -> look it up in humo_texts
                        $notes = $dbh->query("SELECT text_text FROM humo_texts WHERE text_tree_id='" . $trees['tree_id'] . "' AND text_gedcomnr ='" . substr($left_item, 1, -1) . "'");
                        $notesDb = $notes->fetch(PDO::FETCH_OBJ);
                        $notetext = $notesDb->text_text;
                    } else {
                        $notetext = $left_item;
                    }
                ?>
                    <input type="checkbox" name="<?= $name; ?>_l" <?= $left_item ? 'checked' : ''; ?>>
                    <a onmouseover="popup('<?= popclean($notetext); ?>');" href="#"><?= $showtext; ?></a>
                <?php
                } else {
                    echo __('(no data)');
                }

                $showtext = "[" . __('Read text') . "]";
                if (substr($right_item, 0, 2) === "@N") {  // not plain text but @N23@ -> look it up in humo_texts
                    $notes = $dbh->query("SELECT text_text FROM humo_texts WHERE text_tree_id='" . $trees['tree_id'] . "' AND text_gedcomnr ='" . substr($right_item, 1, -1) . "'");
                    $notesDb = $notes->fetch(PDO::FETCH_OBJ);
                    $notetext = $notesDb->text_text;
                } else {
                    $notetext = $right_item;
                }
                ?>
            </td>
            <td>
                <input type="checkbox" name="<?= $name; ?>_r" <?= !$left_item ? 'checked' : ''; ?>>
                <a onmouseover="popup('<?= popclean($notetext); ?>');" href="#"><?= $showtext; ?></a>
            </td>
        </tr>
    <?php
    }
}

/**
 * show_events is a function that places the events in the comparison table
 */
function show_events($left_ged, $right_ged)
{
    global $dbh, $trees, $language, $data2Db, $color;
    $l_address = $l_picture = $l_profession = $l_source = $l_event = $l_birth_decl_witness = $l_baptism_witness = $l_death_decl_witness = $l_burial_witness = $l_name = $l_nobility = $l_title = $l_lordship = $l_URL = $l_else = array();
    $r_address = $r_picture = $r_profession = $r_source = $r_event = $r_birth_decl_witness = $r_baptism_witness = $r_death_decl_witness = $r_burial_witness = $r_name = $r_nobility = $r_title = $r_lordship = $r_URL = $r_else = array();
    $left_events = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "'
        AND (event_connect_kind='person' OR event_kind='ASSO') AND event_connect_id ='" . $left_ged . "' ORDER BY event_kind ");
    $right_events = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "'
        AND (event_connect_kind='person' OR event_kind='ASSO') AND event_connect_id ='" . $right_ged . "' ORDER BY event_kind ");

    if ($right_events->rowCount() > 0) {  // no use doing this if right has no events at all...

        while ($l_eventsDb = $left_events->fetch(PDO::FETCH_OBJ)) {
            if ($l_eventsDb->event_kind == "address") {
                $l_address[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "picture") {
                $l_picture[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "profession") {
                $l_profession[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "event") {
                $l_event[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "ASSO" && $l_eventsDb->event_connect_kind == "birth_declaration") {
                $l_birth_decl_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "ASSO" && $l_eventsDb->event_connect_kind == "CHR") {
                $l_baptism_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "ASSO" && $l_eventsDb->event_connect_kind == "death_declaration") {
                $l_death_decl_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "ASSO" && $l_eventsDb->event_connect_kind == "BURI") {
                $l_burial_witness[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "name") {
                $l_name[$l_eventsDb->event_id] = '(' . $l_eventsDb->event_gedcom . ') ' . $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "nobility") {
                $l_nobility[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "title") {
                $l_title[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "lordship") {
                $l_lordship[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } elseif ($l_eventsDb->event_kind == "URL") {
                $l_URL[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            } else {
                $l_else[$l_eventsDb->event_id] = $l_eventsDb->event_event;
            }
        }

        while ($r_eventsDb = $right_events->fetch(PDO::FETCH_OBJ)) {
            if ($r_eventsDb->event_kind == "address") {
                $r_address[$r_eventsDb->event_id] = $r_eventsDb->event_event;
            } elseif ($r_eventsDb->event_kind == "picture") {
                $r_picture[$r_eventsDb->event_id] = $r_eventsDb->event_event;
            } elseif ($r_eventsDb->event_kind == "profession") {
                $r_profession[$r_eventsDb->event_id] = $r_eventsDb->event_event;
            } elseif ($r_eventsDb->event_kind == "event") {
                $r_event[$r_eventsDb->event_id] = $r_eventsDb->event_event;
            } elseif ($r_eventsDb->event_kind == "ASSO" && $r_eventsDb->event_connect_kind == "birth_declaration") {
                $r_birth_decl_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event;

                if ($r_eventsDb->event_connect_id2) {
                    $r_birth_decl_witness[$r_eventsDb->event_id] = '@' . $r_eventsDb->event_connect_id2;
                }
            } elseif ($r_eventsDb->event_kind == "ASSO" && $r_eventsDb->event_connect_kind == "CHR") {
                $r_baptism_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event;

                if ($r_eventsDb->event_connect_id2) {
                    $r_baptism_witness[$r_eventsDb->event_id] = '@' . $r_eventsDb->event_connect_id2;
                }
            } elseif ($r_eventsDb->event_kind == "ASSO" && $r_eventsDb->event_connect_kind == "death_declaration") {
                $r_death_decl_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event;

                if ($r_eventsDb->event_connect_id2) {
                    $r_death_decl_witness[$r_eventsDb->event_id] = '@' . $r_eventsDb->event_connect_id2;
                }
            } elseif ($r_eventsDb->event_kind == "ASSO" && $r_eventsDb->event_connect_kind == "BURI") {
                $r_burial_witness[$r_eventsDb->event_id] = $r_eventsDb->event_event;

                if ($r_eventsDb->event_connect_id2) {
                    $r_burial_witness[$r_eventsDb->event_id] = '@' . $r_eventsDb->event_connect_id2;
                }
            } elseif ($r_eventsDb->event_kind == "name") {
                $r_name[$r_eventsDb->event_id] = '(' . $r_eventsDb->event_gedcom . ') ' . $r_eventsDb->event_event;
            } elseif ($r_eventsDb->event_kind == "nobility") {
                $r_nobility[$r_eventsDb->event_id] = $r_eventsDb->event_event;
            } elseif ($r_eventsDb->event_kind == "title") {
                $r_title[$r_eventsDb->event_id] = $r_eventsDb->event_event;
            } elseif ($r_eventsDb->event_kind == "lordship") {
                $r_lordship[$r_eventsDb->event_id] = $r_eventsDb->event_event;
            } elseif ($r_eventsDb->event_kind == "URL") {
                $r_URL[$r_eventsDb->event_id] = $r_eventsDb->event_event;
            } else {
                $r_else[] = $r_eventsDb->event_event;
            }
        }
        // before calling put_event function check if right has a value otherwise there is no need to show
        if (!empty($r_address)) {
            put_event('address', __('Address'), $l_address, $r_address);
        }
        if (!empty($r_picture)) {
            put_event('picture', __('Picture'), $l_picture, $r_picture);
        }
        if (!empty($r_profession)) {
            put_event('profession', __('Profession'), $l_profession, $r_profession);
        }
        if (!empty($r_event)) {
            put_event('event', __('Event'), $l_event, $r_event);
        }

        // *** Sept. 2024: declaration and declaration witnesses are now seperate events *** 
        if (!empty($r_birth_decl_witness)) {
            put_event('birth_declaration', __('birth declaration'), $l_birth_decl_witness, $r_birth_decl_witness);
        }
        if (!empty($r_baptism_witness)) {
            put_event('CHR', __('baptism witness'), $l_baptism_witness, $r_baptism_witness);
        }
        // *** Sept. 2024: declaration and declaration witnesses are now seperate events *** 
        if (!empty($r_death_decl_witness)) {
            put_event('death_declaration', __('death declaration'), $l_death_decl_witness, $r_death_decl_witness);
        }
        if (!empty($r_burial_witness)) {
            put_event('BURI', __('burial witness'), $l_burial_witness, $r_burial_witness);
        }

        if (!empty($r_name)) {
            put_event('name', __('Other names'), $l_name, $r_name);
        }
        if (!empty($r_nobility)) {
            put_event('nobility', __('Title of Nobility'), $l_nobility, $r_nobility);
        }
        if (!empty($r_title)) {
            put_event('title', __('Title'), $l_title, $r_title);
        }
        if (!empty($r_lordship)) {
            put_event('lordship', __('Title of Lordship'), $l_lordship, $r_lordship);
        }
        if (!empty($r_URL)) {
            put_event('URL', __('Internet link / URL'), $l_URL, $r_URL);
        }
    }
}

/**
 * "put_event" is a function to create the checkboxes for the event items
 */
function put_event($this_event, $name_event, $l_ev, $r_ev)
{
    global $color, $dbh, $trees, $language;

    if ($r_ev != '') {
        // if right has no event all stays as it is
        $color = $color == '#e6e6e6' ? '#f2f2f2' : '#e6e6e6';
    ?>
        <tr style="background-color:<?= $color; ?>">
            <td style="font-weight:bold"><?= $name_event; ?>:</td>
            <td>
                <?php
                if (is_array($l_ev) && $l_ev != '') {
                    foreach ($l_ev as $key => $value) {
                        if (substr($value, 0, 2) === '@I') {  // this is a person GEDCOM number, not plain text -> show the name
                            $value = str_replace('@', '', $value);
                            $result = $dbh->query("SELECT pers_lastname, pers_firstname FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber = '" . $value . "'");
                            $resultDb = $result->fetch(PDO::FETCH_OBJ);
                            $value = $resultDb->pers_firstname . ' ' . $resultDb->pers_lastname;
                        }
                        if ($this_event == 'picture') { // show link to pic
                            $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id='" . $trees['tree_id'] . "'");
                            $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
                            // TODO check if this works using a default picture path.
                            $tree_pict_path = $dataDb->tree_pict_path;
                            $dir = '../' . $tree_pict_path;
                            $value = $value . ' <a onmouseover="popup(\'<img width=&quot;150px&quot; src=&quot;' . $dir . $value . '&quot;>\',\'150px\');" href="#">[' . __('Show') . ']</a>';
                        }
                        echo '<input type="checkbox" name="l_' . $this_event . '_' . $key . '" checked> ' . $value . '<br>';
                    }
                } else {
                    echo __('(no data)');
                }
                ?>
            </td>
            <td>
                <?php
                if (is_array($r_ev) && $r_ev != '') {
                    $checked = '';
                    if ($l_ev == '') {
                        $checked = " checked";
                    }
                    foreach ($r_ev as $key => $value) {
                        if (substr($value, 0, 2) === '@I') {  // this is a person gedcom number, not plain text
                            $value = str_replace('@', '', $value);
                            $result = $dbh->query("SELECT pers_lastname, pers_firstname FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber = '" . $value . "'");
                            $resultDb = $result->fetch(PDO::FETCH_OBJ);
                            $value = $resultDb->pers_firstname . ' ' . $resultDb->pers_lastname;
                        }
                        if ($this_event == 'picture') {
                            $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_id='" . $trees['tree_id'] . "'");
                            $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
                            $tree_pict_path = $dataDb->tree_pict_path;
                            $dir = '../' . $tree_pict_path;
                            $value = $value . ' <a onmouseover="popup(\'<img width=&quot;150px&quot; src=&quot;' . $dir . $value . '&quot;>\',\'150px\');" href="#">[' . __('Show') . ']</a>';
                        }
                        echo '<input type="checkbox" name="r_' . $this_event . '_' . $key . '" ' . $checked . '> ' . $value . '<br>';
                    }
                } else {
                    echo __('(no data)');
                }
                ?>
            </td>
        </tr>
    <?php
    }
}

/**
 * "show_sources" is the function that places the sources in the comparison table (if right has a value)
 */
function show_sources($left_ged, $right_ged)
{
    global $dbh, $trees, $language, $data2Db, $color;

    // This was disabled!
    $left_sources = $dbh->query("SELECT * FROM humo_connections
        WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_connect_id ='" . $left_ged . "'
        AND LOCATE('source',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
    $right_sources = $dbh->query("SELECT * FROM humo_connections
        WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_connect_id ='" . $right_ged . "'
        AND LOCATE('source',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");

    /* Only processes person_source... Disabled in december 2022.
    $left_sources = $dbh->query("SELECT * FROM humo_connections
        WHERE connect_tree_id='".$trees['tree_id']."' AND connect_connect_id ='".$left_ged."'
        AND connect_sub_kind='person_source' ORDER BY connect_order");
    $right_sources = $dbh->query("SELECT * FROM humo_connections
        WHERE connect_tree_id='".$trees['tree_id']."' AND connect_connect_id ='".$right_ged."'
        AND connect_sub_kind='person_source' ORDER BY connect_order");
    */

    if ($right_sources->rowCount() > 0) {
        // no use doing this if right has no sources
        $color = $color == '#e6e6e6' ? '#f2f2f2' : '#e6e6e6';
    ?>
        <tr style="background-color:<?= $color; ?>">
            <td style="font-weight:bold"><?= __('Sources'); ?>:</td>
            <td>
                <?php
                if ($left_sources->rowCount() > 0) {
                    while ($left_sourcesDb = $left_sources->fetch(PDO::FETCH_OBJ)) {
                        $l_source = $dbh->query("SELECT source_title FROM humo_sources WHERE source_tree_id='" . $trees['tree_id'] . "' AND source_gedcomnr='" . $left_sourcesDb->connect_source_id . "'");
                        $result = $l_source->fetch(PDO::FETCH_OBJ);
                        if (isset($result->source_title)) {
                            if (strlen($result->source_title) > 30) {
                                $title = '<a onmouseover="popup(\'' . popclean($result->source_title) . '\');" href="#"> [' . __('Show') . ']</a>';
                            } else {
                                $title = $result->source_title;
                            }
                        } else {
                            $title = '';
                        }
                        //echo '<input type="checkbox" name="l_source_'.$left_sourcesDb->connect_id.'" '.'checked'.'>('.str_replace('_source',' ',$left_sourcesDb->connect_sub_kind).') '.$title.'<br>';
                        echo '<input type="checkbox" name="l_source_' . $left_sourcesDb->connect_id . '" ' . 'checked' . '>' . $title . '<br>';
                    }
                } else {
                    echo __('(no data)');
                }
                ?>
            </td>
            <td>
                <?php
                while ($right_sourcesDb = $right_sources->fetch(PDO::FETCH_OBJ)) {
                    $checked = '';
                    if (!$left_sources->rowCount()) {
                        $checked = " checked";
                    }
                    $r_source = $dbh->query("SELECT source_title FROM humo_sources WHERE source_tree_id='" . $trees['tree_id'] . "' AND source_gedcomnr='" . $right_sourcesDb->connect_source_id . "'");
                    $result = $r_source->fetch(PDO::FETCH_OBJ);
                    if (isset($result->source_title)) {
                        if (strlen($result->source_title) > 30) {
                            $title = '<a onmouseover="popup(\'' . popclean($result->source_title) . '\');" href="#"> [' . __('Show') . ']</a>';
                        } else {
                            $title = $result->source_title;
                        }
                    } else {
                        $title = '';
                    }
                    //echo '<input type="checkbox" name="r_source_'.$right_sourcesDb->connect_id.'" '.$checked.'>('.str_replace('_source',' ',$right_sourcesDb->connect_sub_kind).') '.$title.'<br>';
                    echo '<input type="checkbox" name="r_source_' . $right_sourcesDb->connect_id . '" ' . $checked . '>' . $title . '<br>';
                }
                ?>
            </td>
        </tr>
    <?php
    }
}

/**
 * "show_addresses" is the function that places the addresses in the comparison table (if right has a value)
 */
function show_addresses($left_ged, $right_ged)
{
    global $dbh, $trees, $language, $data2Db, $color;

    // This part was disabled!
    $left_addresses = $dbh->query("SELECT * FROM humo_connections
        WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_connect_id ='" . $left_ged . "'
        AND LOCATE('address',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");
    $right_addresses = $dbh->query("SELECT * FROM humo_connections
        WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_connect_id ='" . $right_ged . "'
        AND LOCATE('address',connect_sub_kind)!=0 ORDER BY connect_sub_kind ");

    /* DISABLED in december 2022. Only processes person_address.
    $left_addresses = $dbh->query("SELECT * FROM humo_connections
        WHERE connect_tree_id='".$trees['tree_id']."' AND connect_connect_id ='".$left_ged."'
        AND connect_sub_kind='person_address'
        ORDER BY connect_sub_kind ");
    $right_addresses = $dbh->query("SELECT * FROM humo_connections
        WHERE connect_tree_id='".$trees['tree_id']."' AND connect_connect_id ='".$right_ged."'
        AND connect_sub_kind='person_address'
        ORDER BY connect_sub_kind ");
    */

    if ($right_addresses->rowCount() > 0) {
        // no use doing this if right has no sources
        $color = $color == '#e6e6e6' ? '#f2f2f2' : '#e6e6e6';
    ?>
        <tr style="background-color:<?= $color; ?>">
            <td style="font-weight:bold"><?= __('Addresses'); ?>:</td>
            <td>
                <?php
                if ($left_addresses->rowCount() > 0) {
                    while ($left_addressesDb = $left_addresses->fetch(PDO::FETCH_OBJ)) {
                        $l_address = $dbh->query("SELECT address_address, address_place FROM humo_addresses WHERE address_tree_id='" . $trees['tree_id'] . "' AND address_gedcomnr='" . $left_addressesDb->connect_item_id . "'");
                        $result = $l_address->fetch(PDO::FETCH_OBJ);
                        if (strlen($result->address_address . ' ' . $result->address_place) > 30) {
                            $title = '<a onmouseover="popup(\'' . popclean($result->address_address . ' ' . $result->address_place) . '\');" href="#"> [' . __('Show') . ']</a>';
                        } else {
                            $title = $result->address_address . ' ' . $result->address_place;
                        }
                        //echo '<input type="checkbox" name="l_address_'.$left_addressesDb->connect_id.'" checked>('.str_replace('_address',' ',$left_addressesDb->connect_sub_kind).') '.$title.'<br>';
                        echo '<input type="checkbox" name="l_address_' . $left_addressesDb->connect_id . '" checked>' . $title . '<br>';
                    }
                } else {
                    echo __('(no data)');
                }
                ?>
            </td>
            <td>
                <?php
                while ($right_addressesDb = $right_addresses->fetch(PDO::FETCH_OBJ)) {
                    $checked = '';
                    if (!$left_addresses->rowCount()) {
                        $checked = " checked";
                    }
                    $r_address = $dbh->query("SELECT address_address, address_place FROM humo_addresses WHERE address_tree_id='" . $trees['tree_id'] . "' AND address_gedcomnr='" . $right_addressesDb->connect_item_id . "'");

                    $result = $r_address->fetch(PDO::FETCH_OBJ);
                    if (strlen($result->address_address . ' ' . $result->address_place) > 30) {
                        $title = '<a onmouseover="popup(\'' . popclean($result->address_address . ' ' . $result->address_place) . '\');" href="#"> [' . __('Show') . ']</a>';
                    } else {
                        $title = $result->address_address . ' ' . $result->address_place;
                    }
                    //echo '<input type="checkbox" name="r_address_'.$right_addressesDb->connect_id.'" '.$checked.'>('.str_replace('_address',' ',$right_addressesDb->connect_sub_kind).') '.$title.'<br>';
                ?>
                    <input type="checkbox" name="r_address_<?= $right_addressesDb->connect_id; ?>" <?= $checked; ?>><?= $title; ?><br>
                <?php } ?>
            </td>
        </tr>
        <?php
    }
}

/**
 * "merge_them" is the function that does the actual job of merging the data of two persons (left and right)
 */
function merge_them($left, $right, $mode)
{
    global $dbh, $db_functions, $trees, $data2Db, $phpself, $language;
    global $trees, $humo_option, $result1Db, $result2Db;
    // merge algorithm - merge right into left
    // 1. if right has pers_fams with different wife - this Fxx is added to left's pers_fams (in humo_person)
    //    and in humo_family the Ixx of right is replaced with the Ixx of left
    //    Right's Ixx is deleted
    // 2. if right has pers_fams with identical wife - children are added to left's Fxx (in humo_family)
    //    and with each child the famc is changed to left's fams
    //    Right's Fxx is deleted
    //    Right's Ixx is deleted
    // 3. In either case whether right has family or not, if right has famc then in
    //    humo_family in right's parents Fxx, the child's Ixx is changed from right's to left's

    $result1Db = $db_functions->get_person_with_id($left);
    $result2Db = $db_functions->get_person_with_id($right);

    $name1 = $result1Db->pers_firstname . ' ' . $result1Db->pers_lastname; // store for notification later
    $name2 = $result2Db->pers_firstname . ' ' . $result2Db->pers_lastname; // store for notification later

    if ($result2Db->pers_fams) {
        $spouse1 = '';
        $spouse2 = '';
        $count_doubles = 0;
        $same_spouse = false; // will be made true if identical spouses found in next "if"

        if ($result1Db->pers_fams) {
            $fam1_arr = explode(";", $result1Db->pers_fams);
            $fam2_arr = explode(";", $result2Db->pers_fams);
            // start searching for spouses with same ged nr (were merged earlier) of both persons
            for ($n = 0; $n < count($fam1_arr); $n++) {
                $famqry1 = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $fam1_arr[$n] . "'";
                $famresult1 = $dbh->query($famqry1);
                $famresult1Db = $famresult1->fetch(PDO::FETCH_OBJ);
                $spouse1 = $famresult1Db->fam_man;
                if ($result2Db->pers_sexe == "M") {
                    $spouse1 = $famresult1Db->fam_woman;
                }
                for ($m = 0; $m < count($fam2_arr); $m++) {
                    $famqry2 = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $fam2_arr[$m] . "'";
                    $famresult2 = $dbh->query($famqry2);
                    $famresult2Db = $famresult2->fetch(PDO::FETCH_OBJ);
                    $spouse2 = $famresult2Db->fam_man;
                    if ($result2Db->pers_sexe == "M") {
                        $spouse2 = $famresult2Db->fam_woman;
                    }
                    if (substr($spouse1, 0, 1) === "I" && $spouse1 == $spouse2) { // found identical spouse, these F's have to be merged
                        // the substr makes sure that we find two identical real gednrs not 0==0 or ''==''
                        $same_spouse = true;
                        // make array of fam mysql objects with identical spouses
                        //(there may be more than one if they were merged earlier!)
                        $f1[] = $famresult1Db;
                        $f2[] = $famresult2Db;
                        $sp1[] = $spouse1;
                        $sp2[] = $spouse2; // need this????? after all spouse1 and spouse 2 are the same....
                    }
                }
            }
            if ($same_spouse == true) {
                // left has one or more fams with same wife (spouse was already merged)
                // if right has children - add them to the left F

                // with all possible families of the right person that will move to the left, change right's I for left I
                $r_spouses = explode(';', $result2Db->pers_fams);
                for ($i = 0; $i < count($r_spouses); $i++) { // get all fams
                    if ($result2Db->pers_sexe == "M") {
                        $per = "fam_man";
                    } else {
                        $per = "fam_woman";
                    }
                    $qry = "UPDATE humo_families SET " . $per . " = '" . $result1Db->pers_gedcomnumber . "' WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $r_spouses[$i] . "'";
                    $dbh->query($qry);
                }
                for ($i = 0; $i < count($f1); $i++) { // with all identical spouses
                    if ($f2[$i]->fam_children) {
                        if ($f1[$i]->fam_children) {
                            // add right's children to left if not same gedcomnumber (=if not merged already)
                            $rightchld = $f2[$i]->fam_children;
                            $l_chld = explode(';', $f1[$i]->fam_children);
                            $r_chld = explode(';', $f2[$i]->fam_children);
                            for ($q = 0; $q < count($l_chld); $q++) {
                                for ($w = 0; $w < count($r_chld); $w++) {
                                    if ($l_chld[$q] == $r_chld[$w]) { // same gedcomnumber
                                        $rightchld = str_replace($r_chld[$w] . ';', '', $rightchld . ';');
                                        if (substr($rightchld, -1, 1) == ';') {
                                            $rightchld = substr($rightchld, 0, -1);
                                        }
                                    }
                                }
                            }
                            $childr = $rightchld != '' ? $f1[$i]->fam_children . ';' . $rightchld : $f1[$i]->fam_children;

                            // if children were moved to left, create warning about possible duplicate children that will be created
                            if ($rightchld != '') {
                                $allch1 = explode(';', $f1[$i]->fam_children);
                                $allch2 = explode(';', $rightchld);
                                for ($z = 0; $z < count($allch1); $z++) {
                                    //TODO only need pers_firstname, pers_lastname?
                                    $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $allch1[$z] . "'";
                                    $chl1 = $dbh->query($qry);
                                    $chl1Db = $chl1->fetch(PDO::FETCH_OBJ);
                                    for ($y = 0; $y < count($allch2); $y++) {
                                        //TODO only need pers_firstname, pers_lastname?
                                        $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $allch2[$y] . "'";
                                        $chl2 = $dbh->query($qry);
                                        $chl2Db = $chl2->fetch(PDO::FETCH_OBJ);
                                        if (
                                            isset($chl1Db->pers_lastname) && isset($chl2Db->pers_lastname) && $chl1Db->pers_lastname == $chl2Db->pers_lastname && substr($chl1Db->pers_firstname, 0, $humo_option["merge_chars"]) === substr($chl2Db->pers_firstname, 0, $humo_option["merge_chars"])
                                        ) {
                                            $string1 = $allch1[$z] . '@' . $allch2[$y] . ';';
                                            $string2 = $allch2[$y] . '@' . $allch1[$z] . ';';
                                            // make sure this pair doesn't exist already in the string
                                            if (strstr($trees['relatives_merge'], $string1) === false && strstr($trees['relatives_merge'], $string2) === false) {
                                                $trees['relatives_merge'] .= $string1;
                                            }
                                            $db_functions->update_settings('rel_merge_' . $trees['tree_id'], $trees['relatives_merge']);
                                        }
                                    }
                                }
                            }
                        } else { // only right has children
                            $childr = $f2[$i]->fam_children;
                        }
                        $qry = "UPDATE humo_families SET fam_children ='" . $childr . "' WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber='" . $f1[$i]->fam_gedcomnumber . "'";
                        $dbh->query($qry);

                        // change those childrens' famc to left F
                        $allchld = explode(";", $f2[$i]->fam_children);
                        foreach ($allchld as $value) {
                            $qry = "UPDATE humo_persons SET pers_famc='" . $f1[$i]->fam_gedcomnumber . "' WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber='" . $value . "'";
                            $dbh->query($qry);
                        }
                    }
                }

                // Add the right fams to left fams, without the F's that belonged to the duplicate right spouse(s)
                $famstring = $result2Db->pers_fams . ';';
                for ($i = 0; $i < count($f1); $i++) { // can use f1 or f2 they are the same size
                    for ($i = 0; $i < count($f2); $i++) {
                        $famstring = str_replace($f2[$i]->fam_gedcomnumber . ';', '', $famstring);
                    }
                }
                if (substr($famstring, -1, 1) === ';') {
                    $famstring = substr($famstring, 0, -1);
                } // take off last ;
                $newstring = $famstring != '' ? $result1Db->pers_fams . ';' . $famstring : $result1Db->pers_fams;
                $qry = "UPDATE humo_persons SET pers_fams = '" . $newstring . "' WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $result1Db->pers_gedcomnumber . "'";
                $dbh->query($qry);

                // remove the F that belonged to the duplicate right spouse from that spouse as well - he/she is one and the same
                for ($i = 0; $i < count($f1); $i++) { // for each of the identical spouses
                    $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp1[$i] . "'";
                    $sp_data = $dbh->query($qry);
                    $sp_dataDb = $sp_data->fetch(PDO::FETCH_OBJ);
                    // TODO only need 2 items?
                    //$sp_dataDb=$db_functions->get_person($sp1[$i]);
                    if (isset($sp_dataDb)) {
                        $sp_string = $sp_dataDb->pers_fams . ';';
                        $sp_string = str_replace($f2[$i]->fam_gedcomnumber . ';', '', $sp_string);
                        if (substr($sp_string, -1, 1) === ';') {
                            $sp_string = substr($sp_string, 0, -1);
                        } // take off last ; again
                        $qry = "UPDATE humo_persons SET pers_fams = '" . $sp_string . "' WHERE pers_id ='" . $sp_dataDb->pers_id . "'";
                        $dbh->query($qry);
                    }
                }

                // before we delete the F's of duplicate wifes from the database, we first check if they have items
                // that are not known in the "receiving" F's. If so, we copy it to the corresponding left families
                // to make one Db query only, we first put the necessary fields and values in an array
                for ($i = 0; $i < count($f1); $i++) {
                    if ($f1[$i]->fam_kind == '' and $f2[$i]->fam_kind != '') {
                        $fam_items[$i]["fam_kind"] = $f2[$i]->fam_kind;
                    }
                    if ($f1[$i]->fam_relation_date == '' && $f2[$i]->fam_relation_date != '') {
                        $fam_items[$i]["fam_relation_date"] = $f2[$i]->fam_relation_date;
                    }
                    if ($f1[$i]->fam_relation_place == '' && $f2[$i]->fam_relation_place != '') {
                        $fam_items[$i]["fam_relation_place"] = $f2[$i]->fam_relation_place;
                    }
                    if ($f1[$i]->fam_relation_text == '' && $f2[$i]->fam_relation_text != '') {
                        $fam_items[$i]["fam_relation_text"] = $f2[$i]->fam_relation_text;
                    }
                    //if($f1[$i]->fam_relation_source=='' AND $f2[$i]->fam_relation_source!='') { $fam_items[$i]["fam_relation_source"] = $f2[$i]->fam_relation_source; }
                    if ($f1[$i]->fam_relation_end_date == '' && $f2[$i]->fam_relation_end_date != '') {
                        $fam_items[$i]["fam_relation_end_date"] = $f2[$i]->fam_relation_end_date;
                    }
                    if ($f1[$i]->fam_marr_notice_date == '' && $f2[$i]->fam_marr_notice_date != '') {
                        $fam_items[$i]["fam_marr_notice_date"] = $f2[$i]->fam_marr_notice_date;
                    }
                    if ($f1[$i]->fam_marr_notice_place == '' && $f2[$i]->fam_marr_notice_place != '') {
                        $fam_items[$i]["fam_marr_notice_place"] = $f2[$i]->fam_marr_notice_place;
                    }
                    if ($f1[$i]->fam_marr_notice_text == '' && $f2[$i]->fam_marr_notice_text != '') {
                        $fam_items[$i]["fam_marr_notice_text"] = $f2[$i]->fam_marr_notice_text;
                    }
                    //if($f1[$i]->fam_marr_notice_source=='' AND $f2[$i]->fam_marr_notice_source!='') { $fam_items[$i]["fam_marr_notice_source"] = $f2[$i]->fam_marr_notice_source; }
                    if ($f1[$i]->fam_marr_date == '' && $f2[$i]->fam_marr_date != '') {
                        $fam_items[$i]["fam_marr_date"] = $f2[$i]->fam_marr_date;
                    }
                    if ($f1[$i]->fam_marr_place == '' && $f2[$i]->fam_marr_place != '') {
                        $fam_items[$i]["fam_marr_place"] = $f2[$i]->fam_marr_place;
                    }
                    if ($f1[$i]->fam_marr_text == '' && $f2[$i]->fam_marr_text != '') {
                        $fam_items[$i]["fam_marr_text"] = $f2[$i]->fam_marr_text;
                    }
                    //if($f1[$i]->fam_marr_source=='' AND $f2[$i]->fam_marr_source!='') { $fam_items[$i]["fam_marr_source"] = $f2[$i]->fam_marr_source; }
                    if ($f1[$i]->fam_marr_authority == '' && $f2[$i]->fam_marr_authority != '') {
                        $fam_items[$i]["fam_marr_authority"] = $f2[$i]->fam_marr_authority;
                    }
                    if ($f1[$i]->fam_marr_church_notice_date == '' && $f2[$i]->fam_marr_church_notice_date != '') {
                        $fam_items[$i]["fam_marr_church_notice_date"] = $f2[$i]->fam_marr_church_notice_date;
                    }
                    if ($f1[$i]->fam_marr_church_notice_place == '' && $f2[$i]->fam_marr_church_notice_place != '') {
                        $fam_items[$i]["fam_marr_church_notice_place"] = $f2[$i]->fam_marr_church_notice_place;
                    }
                    if ($f1[$i]->fam_marr_church_notice_text == '' && $f2[$i]->fam_marr_church_notice_text != '') {
                        $fam_items[$i]["fam_marr_church_notice_text"] = $f2[$i]->fam_marr_church_notice_text;
                    }
                    //if($f1[$i]->fam_marr_church_notice_source=='' AND $f2[$i]->fam_marr_church_notice_source!='') { $fam_items[$i]["fam_marr_church_notice_source"] = $f2[$i]->fam_marr_church_notice_source; }
                    if ($f1[$i]->fam_marr_church_date == '' && $f2[$i]->fam_marr_church_date != '') {
                        $fam_items[$i]["fam_marr_church_date"] = $f2[$i]->fam_marr_church_date;
                    }
                    if ($f1[$i]->fam_marr_church_place == '' && $f2[$i]->fam_marr_church_place != '') {
                        $fam_items[$i]["fam_marr_church_place"] = $f2[$i]->fam_marr_church_place;
                    }
                    if ($f1[$i]->fam_marr_church_text == '' && $f2[$i]->fam_marr_church_text != '') {
                        $fam_items[$i]["fam_marr_church_text"] = $f2[$i]->fam_marr_church_text;
                    }
                    //if($f1[$i]->fam_marr_church_source=='' AND $f2[$i]->fam_marr_church_source!='') { $fam_items[$i]["fam_marr_church_source"] = $f2[$i]->fam_marr_church_source; }
                    if ($f1[$i]->fam_religion == '' && $f2[$i]->fam_religion != '') {
                        $fam_items[$i]["fam_religion"] = $f2[$i]->fam_religion;
                    }
                    if ($f1[$i]->fam_div_date == '' && $f2[$i]->fam_div_date != '') {
                        $fam_items[$i]["fam_div_date"] = $f2[$i]->fam_div_date;
                    }
                    if ($f1[$i]->fam_div_place == '' && $f2[$i]->fam_div_place != '') {
                        $fam_items[$i]["fam_div_place"] = $f2[$i]->fam_div_place;
                    }
                    if ($f1[$i]->fam_div_text == '' && $f2[$i]->fam_div_text != '') {
                        $fam_items[$i]["fam_div_text"] = $f2[$i]->fam_div_text;
                    }
                    //if($f1[$i]->fam_div_source=='' AND $f2[$i]->fam_div_source!='') { $fam_items[$i]["fam_div_source"] = $f2[$i]->fam_div_source; }
                    if ($f1[$i]->fam_div_authority == '' && $f2[$i]->fam_div_authority != '') {
                        $fam_items[$i]["fam_div_authority"] = $f2[$i]->fam_div_authority;
                    }
                    if ($f1[$i]->fam_text == '' && $f2[$i]->fam_text != '') {
                        $fam_items[$i]["fam_text"] = $f2[$i]->fam_text;
                    }
                    //if($f1[$i]->fam_text_source=='' AND $f2[$i]->fam_text_source!='') { $fam_items[$i]["fam_text_source"] = $f2[$i]->fam_text_source; }
                }
                for ($i = 0; $i < count($f1); $i++) {
                    if (isset($fam_items[$i])) {
                        $item_string = '';
                        foreach ($fam_items[$i] as $key => $value) {
                            $item_string .= $key . "='" . $value . "',";
                        }
                        $item_string = substr($item_string, 0, -1); // take off last comma

                        $qry = "UPDATE humo_families SET " . $item_string . " WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $f1[$i]->fam_gedcomnumber . "'";
                        $dbh->query($qry);
                    }
                }

                // TODO check if these queries can be combined. Use something like: AND connect_sub_kind LIKE '%_source'
                // - new piece for fam sources that were removed in the code above 2052 - 2078)
                for ($i = 0; $i < count($f1); $i++) {
                    $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_relation_source'";
                    $sourDb = $dbh->query($qry);
                    if ($sourDb->rowCount() == 0) {  // no fam sources of the sub kind for this fam
                        $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_relation_source'";
                        $sourDb2 = $dbh->query($qry2);
                        if ($sourDb2->rowCount() > 0) {  // second fam has source of this sub kind - transfer these sources to left fam
                            $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_relation_source'";
                            $dbh->query($qry3);
                        }
                    }

                    $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_notice_source'";
                    $sourDb = $dbh->query($qry);
                    if ($sourDb->rowCount() == 0) {  // no fam sources of the sub kind for this fam
                        $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_notice_source'";
                        $sourDb2 = $dbh->query($qry2);
                        if ($sourDb2->rowCount() > 0) {  // second fam has source of this sub kind - transfer these sources to left fam
                            $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_notice_source'";
                            $dbh->query($qry3);
                        }
                    }

                    $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_source'";
                    $sourDb = $dbh->query($qry);
                    if ($sourDb->rowCount() == 0) {  // no fam sources of the sub kind for this fam
                        $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_source'";
                        $sourDb2 = $dbh->query($qry2);
                        if ($sourDb2->rowCount() > 0) {  // second fam has source of this sub kind - transfer these sources to left fam
                            $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_source'";
                            $dbh->query($qry3);
                        }
                    }

                    $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_notice_source'";
                    $sourDb = $dbh->query($qry);
                    if ($sourDb->rowCount() == 0) {  // no fam sources of the sub kind for this fam
                        $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_notice_source'";
                        $sourDb2 = $dbh->query($qry2);
                        if ($sourDb2->rowCount() > 0) {  // second fam has source of this sub kind - transfer these sources to left fam
                            $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_notice_source'";
                            $dbh->query($qry3);
                        }
                    }

                    $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_source'";
                    $sourDb = $dbh->query($qry);
                    if ($sourDb->rowCount() == 0) {  // no fam sources of the sub kind for this fam
                        $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_source'";
                        $sourDb2 = $dbh->query($qry2);
                        if ($sourDb2->rowCount() > 0) {  // second fam has source of this sub kind - transfer these sources to left fam
                            $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_marr_church_source'";
                            $dbh->query($qry3);
                        }
                    }
                    $qry = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_text_source'";
                    $sourDb = $dbh->query($qry);
                    if ($sourDb->rowCount() == 0) {  // no fam sources of the sub kind for this fam
                        $qry2 = "SELECT * FROM humo_connections WHERE connect_tree_id ='" . $trees['tree_id'] . "' AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_text_source'";
                        $sourDb2 = $dbh->query($qry2);
                        if ($sourDb2->rowCount() > 0) {  // second fam has source of this sub kind - transfer these sources to left fam
                            $qry3 = "UPDATE humo_connections SET connect_connect_id = '" . $f1[$i]->fam_gedcomnumber . "' WHERE connect_tree_id ='" . $trees['tree_id'] . "'  AND connect_connect_id = '" . $f2[$i]->fam_gedcomnumber . "' AND connect_kind = 'family' AND connect_sub_kind = 'fam_text_source'";
                            $dbh->query($qry3);
                        }
                    }
                }
                // - end new piece for fam sources 

                // delete F's that belonged to identical right spouse(s)
                for ($i = 0; $i < count($f1); $i++) { // for each of the identical spouses
                    $qry = "DELETE FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $f2[$i]->fam_gedcomnumber . "'";
                    $dbh->query($qry);

                    // Substract 1 family from the number of families counter in the family tree.
                    $sql = "UPDATE humo_trees SET tree_families=tree_families-1 WHERE tree_id='" . $trees['tree_id'] . "'";
                    $dbh->query($sql);

                    // CLEANUP: also delete this F from other tables where it may appear
                    $qry = "DELETE FROM humo_addresses WHERE address_tree_id='" . $trees['tree_id'] . "' AND address_connect_sub_kind='family' AND address_connect_id ='" . $f2[$i]->fam_gedcomnumber . "'";
                    $dbh->query($qry);

                    //$qry = "DELETE FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND event_connect_kind='family' AND event_connect_id ='" . $f2[$i]->fam_gedcomnumber . "'";
                    $qry = "DELETE FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND (event_connect_kind='family' OR event_kind='ASSO') AND event_connect_id ='" . $f2[$i]->fam_gedcomnumber . "'";
                    $dbh->query($qry);

                    $qry = "DELETE FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_connect_id ='" . $f2[$i]->fam_gedcomnumber . "'";
                    $dbh->query($qry);
                }
                // check for other spouses that may have to be added to relative merge string
                if (count($r_spouses) > count($f1)) { // right had more than the identical spouse(s). maybe they need merging
                    $leftfam = explode(';', $result1Db->pers_fams);
                    $rightfam = explode(';', $famstring);
                    for ($e = 0; $e < count($leftfam); $e++) {
                        $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $leftfam[$e] . "'";
                        $fam1 = $dbh->query($qry);
                        $fam1Db = $fam1->fetch(PDO::FETCH_OBJ);
                        $sp_ged = $fam1Db->fam_woman;
                        if ($result1Db->pers_sexe == "F") {
                            $sp_ged = $fam1Db->fam_man;
                        }

                        $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp_ged . "'";
                        $spo1 = $dbh->query($qry);
                        $spo1Db = $spo1->fetch(PDO::FETCH_OBJ);
                        if ($spo1->rowCount() > 0) {
                            for ($f = 0; $f < count($rightfam); $f++) {
                                $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $rightfam[$f] . "'";
                                $fam2 = $dbh->query($qry);
                                $fam2Db = $fam2->fetch(PDO::FETCH_OBJ);
                                $sp_ged = $fam2Db->fam_woman;
                                if ($result1Db->pers_sexe == "F") {
                                    $sp_ged = $fam2Db->fam_man;
                                }

                                $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp_ged . "'";
                                $spo2 = $dbh->query($qry);
                                $spo2Db = $spo2->fetch(PDO::FETCH_OBJ);
                                if ($spo2->rowCount() > 0 && ($spo1Db->pers_lastname == $spo2Db->pers_lastname && substr($spo1Db->pers_firstname, 0, $humo_option["merge_chars"]) === substr($spo2Db->pers_firstname, 0, $humo_option["merge_chars"]))) {
                                    $string1 = $spo1Db->pers_gedcomnumber . '@' . $spo2Db->pers_gedcomnumber . ';';
                                    $string2 = $spo2Db->pers_gedcomnumber . '@' . $spo1Db->pers_gedcomnumber . ';';
                                    // make sure this pair doesn't appear already in the string
                                    if (strstr($trees['relatives_merge'], $string1) === false && strstr($trees['relatives_merge'], $string2) === false) {
                                        $trees['relatives_merge'] .= $string1;
                                    }
                                    $db_functions->update_settings('rel_merge_' . $trees['tree_id'], $trees['relatives_merge']);
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$result1Db->pers_fams || $same_spouse == false) {
            // left has no fams or fams with different spouses than right -> add fams to left

            // add right's F to left's fams
            $fam = $result1Db->pers_fams ? $result1Db->pers_fams . ";" . $result2Db->pers_fams : $result2Db->pers_fams;
            $qry = "UPDATE humo_persons SET pers_fams='" . $fam . "' WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $result1Db->pers_gedcomnumber . "'";
            $dbh->query($qry);

            // in humo_family, under right's F, change fam_man/woman to left's I
            $self = "man";
            if ($result1Db->pers_sexe == "F") {
                $self = "woman";
            }

            //in all right's families (that are now moved to left!) change right's I to left's I
            $r_fams = explode(';', $result2Db->pers_fams);
            for ($i = 0; $i < count($r_fams); $i++) {
                $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $r_fams[$i] . "'";
                $r_fm = $dbh->query($qry);
                $r_fmDb = $r_fm->fetch(PDO::FETCH_OBJ);
                $qry = "UPDATE humo_families SET fam_" . $self . "='" . $result1Db->pers_gedcomnumber . "' WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber='" . $r_fams[$i] . "'";
                $dbh->query($qry);
            }

            // check for spouses to be added to relative merge string:
            if ($result1Db->pers_fams && $same_spouse == false) {
                $leftfam = explode(';', $result1Db->pers_fams);
                $rightfam = explode(';', $result2Db->pers_fams);
                for ($e = 0; $e < count($leftfam); $e++) {
                    $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $leftfam[$e] . "'";
                    $fam1 = $dbh->query($qry);
                    $fam1Db = $fam1->fetch(PDO::FETCH_OBJ);
                    $sp_ged = $fam1Db->fam_woman;
                    if ($result1Db->pers_sexe == "F") {
                        $sp_ged = $fam1Db->fam_man;
                    }

                    $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp_ged . "'";
                    $spo1 = $dbh->query($qry);
                    $spo1Db = $spo1->fetch(PDO::FETCH_OBJ);
                    if ($spo1->rowCount() > 0) {
                        for ($f = 0; $f < count($rightfam); $f++) {
                            $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $rightfam[$f] . "'";
                            $fam2 = $dbh->query($qry);
                            $fam2Db = $fam2->fetch(PDO::FETCH_OBJ);
                            $sp_ged = $fam2Db->fam_woman;
                            if ($result1Db->pers_sexe == "F") {
                                $sp_ged = $fam2Db->fam_man;
                            }

                            $qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $sp_ged . "'";
                            $spo2 = $dbh->query($qry);
                            $spo2Db = $spo2->fetch(PDO::FETCH_OBJ);
                            if ($spo2->rowCount() > 0 && ($spo1Db->pers_lastname == $spo2Db->pers_lastname && substr($spo1Db->pers_firstname, 0, $humo_option["merge_chars"]) === substr($spo2Db->pers_firstname, 0, $humo_option["merge_chars"]))) {
                                $string1 = $spo1Db->pers_gedcomnumber . '@' . $spo2Db->pers_gedcomnumber . ';';
                                $string2 = $spo2Db->pers_gedcomnumber . '@' . $spo1Db->pers_gedcomnumber . ';';
                                // make sure this pair doesn't already exist in the string
                                if (strstr($trees['relatives_merge'], $string1) === false && strstr($trees['relatives_merge'], $string2) === false) {
                                    $trees['relatives_merge'] .= $string1;
                                }
                                $db_functions->update_settings('rel_merge_' . $trees['tree_id'], $trees['relatives_merge']);
                            }
                        }
                    }
                }
            }
        }
    }
    if ($result2Db->pers_famc) {
        // if the two merged persons had a different parent set (e.i. parents aren't merged yet)
        // then in humo_family under right's parents' F, in fam_children, change right's I to left's I
        // (because right I will be deleted and as long as the double parents aren't merged we don't want errors
        // when accessing the children!

        $parqry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $result2Db->pers_famc . "'";
        $parfam = $dbh->query($parqry);
        $parfamDb = $parfam->fetch(PDO::FETCH_OBJ);

        $children = $parfamDb->fam_children . ";";
        // add ; at end for following manipulation
        // we have to search for "I45;" if we searched for I34 without semi colon then also I346 would give true!
        // since the last entry doesn't have a ; we have to temporarily add it for the search.

        if (!$result1Db->pers_famc || $result1Db->pers_famc && $result1Db->pers_famc != $result2Db->pers_famc) {
            // left has no parents or a different parent set (at least one parent not merged yet)
            // --> change right I for left I in right's parents' F
            $children = str_replace($result2Db->pers_gedcomnumber . ";", $result1Db->pers_gedcomnumber . ";", $children);
            // check if to add to relatives merge string
            if ($result1Db->pers_famc && $result1Db->pers_famc != $result2Db->pers_famc) {
                // there is a double set of parents - these have to be merged by the user! Save in variables
                $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $result1Db->pers_famc . "'";
                $par1 = $dbh->query($qry);
                $par1Db = $par1->fetch(PDO::FETCH_OBJ);

                $qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $result2Db->pers_famc . "'";
                $par2 = $dbh->query($qry);
                $par2Db = $par2->fetch(PDO::FETCH_OBJ);
                // add the parents to string of surrounding relatives to be merged
                // to help later with exploding, sets are separated by ";" and left and right are separated by "@"
                if (
                    isset($par1Db->fam_man) && $par1Db->fam_man != '0' && isset($par2Db->fam_man) && $par2Db->fam_man != '0' && $par1Db->fam_man != $par2Db->fam_man
                ) {
                    // make sure none of the two fathers is N.N. and that this father is not merged already!
                    $string1 = $par1Db->fam_man . '@' . $par2Db->fam_man . ";";
                    $string2 = $par2Db->fam_man . '@' . $par1Db->fam_man . ";";
                    // make sure this pair doesn't appear already in the string
                    if (strstr($trees['relatives_merge'], $string1) === false && strstr($trees['relatives_merge'], $string2) === false) {
                        $trees['relatives_merge'] .= $string1;
                    }
                } elseif ((!isset($par1Db->fam_man) || $par1Db->fam_man == '0') && isset($par2Db->fam_man) && $par2Db->fam_man != '0') {
                    // left father is N.N. so move right father to left F
                    $dbh->query("UPDATE humo_families SET fam_man = '" . $par2Db->fam_man . "'
                        WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $result1Db->pers_famc . "'");
                }
                if (
                    isset($par1Db->fam_woman) && $par1Db->fam_woman != '0' && isset($par2Db->fam_woman) && $par2Db->fam_woman != '0' && $par1Db->fam_woman != $par2Db->fam_woman
                ) {
                    // make sure none of the two mothers is N.N. and that this mother is not merged already!
                    $string1 = $par1Db->fam_woman . '@' . $par2Db->fam_woman . ";";
                    $string2 = $par2Db->fam_woman . '@' . $par1Db->fam_woman . ";";
                    if (strstr($trees['relatives_merge'], $string1) === false && strstr($trees['relatives_merge'], $string2) === false) {
                        // make sure this pair doesn't appear already in the string
                        $trees['relatives_merge'] .= $string1;
                    }
                } elseif ((!isset($par1Db->fam_woman) || $par1Db->fam_woman == '0') && isset($par2Db->fam_woman) && $par2Db->fam_woman != '0') {
                    // left mother is N.N. so move right mother to left F
                    $dbh->query("UPDATE humo_families SET fam_woman = '" . $par2Db->fam_woman . "'
                        WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber ='" . $result1Db->pers_famc . "'");
                }
                $db_functions->update_settings('rel_merge_' . $trees['tree_id'], $trees['relatives_merge']);
            }
            if (!$result1Db->pers_famc) {
                // give left the famc of right
                $qry = "UPDATE humo_persons SET pers_famc ='" . $result2Db->pers_famc . "'
                    WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $result1Db->pers_gedcomnumber . "'";
                $dbh->query($qry);
            }
        } elseif ($result1Db->pers_famc && $result1Db->pers_famc == $result2Db->pers_famc) {
            // same parent set (double children in one family) just remove right's I from F
            // we can use right's F since this is also left's F....
            $children = str_replace($result2Db->pers_gedcomnumber . ";", "", $children);
        }
        if (substr($children, -1) === ";") { // if the added ';' is still there, remove it
            $children = substr($children, 0, -1); // take off last ;
        }
        $qry = "UPDATE humo_families SET fam_children='" . $children . "' WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber = '" . $result2Db->pers_famc . "'";
        $dbh->query($qry);
    }

    // PERSONAL DATA
    // default:
    // 1. if there is data for left only, or for left and right --> the left data is retained.
    // 2. if right has data and left hasn't --> right's data is transfered to left
    // in manual, duplicate and relatives merge this can be over-ruled by the admin with the radio buttons

    // for automatic merge see if data has to be transferred from right to left
    // (for manual, duplicate and relative merge this is done in the form with radio buttons by the user)
    $l_name = '1';
    $f_name = '1';
    $b_date = '1';
    $b_place = '1';
    $d_date = '1';
    $d_place = '1';
    $b_time = '1';
    $b_text = '1';
    $d_time = '1';
    $d_text = '1';
    $d_cause = '1';
    $br_date = '1';
    $br_place = '1';
    $br_text = '1';
    $bp_date = '1';
    $bp_place = '1';
    $bp_text = '1';
    $crem = '1';
    $reli = '1';
    $code = '1';
    $stborn = '1';
    $alive = '1';
    $c_name = '1';
    $patr = '1';
    $fav = '1';
    $n_text = '1';
    $text = '1';

    if ($mode == 'automatic') {
        // the regular items for automatic mode
        // 2 = move text to left person.  3 = append right text to left text
        if ($result1Db->pers_birth_date == '' && $result2Db->pers_birth_date != '') {
            $b_date = '2';
        }
        if ($result1Db->pers_birth_place == '' && $result2Db->pers_birth_place != '') {
            $b_place = '2';
        }
        if ($result1Db->pers_death_date == '' && $result2Db->pers_death_date != '') {
            $d_date = '2';
        }
        if ($result1Db->pers_death_place == '' && $result2Db->pers_death_place != '') {
            $d_place = '2';
        }
        if ($result1Db->pers_birth_time == '' && $result2Db->pers_birth_time != '') {
            $b_time = '2';
        }
        if ($result1Db->pers_birth_text == '' && $result2Db->pers_birth_text != '') {
            $b_text = '2';
        }
        if ($result1Db->pers_death_time == '' && $result2Db->pers_death_time != '') {
            $d_time = '2';
        }
        if ($result1Db->pers_death_text == '' && $result2Db->pers_death_text != '') {
            $d_text = '2';
        }
        if ($result1Db->pers_death_cause == '' && $result2Db->pers_death_cause != '') {
            $d_cause = '2';
        }
        if ($result1Db->pers_buried_date == '' && $result2Db->pers_buried_date != '') {
            $br_date = '2';
        }
        if ($result1Db->pers_buried_place == '' && $result2Db->pers_buried_place != '') {
            $br_place = '2';
        }
        if ($result1Db->pers_buried_text == '' && $result2Db->pers_buried_text != '') {
            $br_text = '2';
        }
        if ($result1Db->pers_bapt_date == '' && $result2Db->pers_bapt_date != '') {
            $bp_date = '2';
        }
        if ($result1Db->pers_bapt_place == '' && $result2Db->pers_bapt_place != '') {
            $bp_place = '2';
        }
        if ($result1Db->pers_bapt_text == '' && $result2Db->pers_bapt_text != '') {
            $bp_text = '2';
        }
        if ($result1Db->pers_religion == '' && $result2Db->pers_religion != '') {
            $reli = '2';
        }
        if ($result1Db->pers_own_code == '' && $result2Db->pers_own_code != '') {
            $code = '2';
        }
        if ($result1Db->pers_stillborn == '' && $result2Db->pers_stillborn != '') {
            $stborn = '2';
        }
        if ($result1Db->pers_alive == '' && $result2Db->pers_alive != '') {
            $alive = '2';
        }
        //if($result1Db->pers_callname=='' AND $result2Db->pers_callname!='') { $c_name='2'; }
        if ($result1Db->pers_patronym == '' && $result2Db->pers_patronym != '') {
            $patr = '2';
        }
        if ($result1Db->pers_name_text == '' && $result2Db->pers_name_text != '') {
            $n_text = '2';
        }
        if ($result1Db->pers_text == '' && $result2Db->pers_text != '') {
            $text = '2';
        }
        if ($result1Db->pers_cremation == '' && $result2Db->pers_cremation != '') {
            $crem = '2';
        }
    }
    check_regular('l_name', $l_name, 'pers_lastname');
    check_regular('f_name', $f_name, 'pers_firstname');
    check_regular('b_date', $b_date, 'pers_birth_date');
    check_regular('b_place', $b_place, 'pers_birth_place');
    check_regular('d_date', $d_date, 'pers_death_date');
    check_regular('d_place', $d_place, 'pers_death_place');
    check_regular('b_time', $b_time, 'pers_birth_time');
    check_regular_text('b_text', $b_text, 'pers_birth_text');
    check_regular('d_time', $d_time, 'pers_death_time');
    check_regular_text('d_text', $d_text, 'pers_death_text');
    check_regular('d_cause', $d_cause, 'pers_death_cause');
    check_regular('br_date', $br_date, 'pers_buried_date');
    check_regular('br_place', $br_place, 'pers_buried_place');
    check_regular_text('br_text', $br_text, 'pers_buried_text');
    check_regular('bp_date', $bp_date, 'pers_bapt_date');
    check_regular('bp_place', $bp_place, 'pers_bapt_place');
    check_regular_text('bp_text', $bp_text, 'pers_bapt_text');
    check_regular('reli', $reli, 'pers_religion');
    check_regular('code', $code, 'pers_own_code');
    check_regular('stborn', $stborn, 'pers_stillborn');
    check_regular('alive', $alive, 'pers_alive');
    //check_regular('c_name',$c_name,'pers_callname');
    check_regular('patr', $patr, 'pers_patronym');
    check_regular_text('n_text', $n_text, 'pers_name_text');
    check_regular_text('text', $text, 'pers_text');
    check_regular('crem', $crem, 'pers_cremation');

    // check for posted event, address and source items (separate functions below process input from comparison form)
    if ($mode != 'automatic') {
        $right_event_array = array();
        $left_events = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND (event_connect_kind='person' OR event_kind='ASSO') AND event_connect_id ='" . $result1Db->pers_gedcomnumber . "' ORDER BY event_kind ");
        $right_events = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND (event_connect_kind='person' OR event_kind='ASSO') AND event_connect_id ='" . $result2Db->pers_gedcomnumber . "' ORDER BY event_kind ");
        if ($right_events->rowCount() > 0) { // if right has no events it did not appear in the comparison table, so the whole thing is unnecessary
            while ($right_eventsDb = $right_events->fetch(PDO::FETCH_OBJ)) {
                $right_event_array[$right_eventsDb->event_kind] = "1"; // we need this to know whether to handle left   
                if (isset($_POST['r_' . $right_eventsDb->event_kind . '_' . $right_eventsDb->event_id])) { // change right's I to left's I
                    $dbh->query("UPDATE humo_events SET event_connect_id ='" . $result1Db->pers_gedcomnumber . "' WHERE event_id ='" . $right_eventsDb->event_id . "'");
                } elseif (isset($_POST['r_' . $right_eventsDb->event_connect_kind . '_' . $right_eventsDb->event_id])) { // change right's I to left's I
                    $dbh->query("UPDATE humo_events SET event_connect_id ='" . $result1Db->pers_gedcomnumber . "' WHERE event_id ='" . $right_eventsDb->event_id . "'");
                } else { // clean up database -> remove this entry altogether (IF IT EXISTS...)
                    // TODO: no need to check for event_kind?
                    $dbh->query("DELETE FROM humo_events WHERE event_id ='" . $right_eventsDb->event_id . "' AND event_kind='" . $right_eventsDb->event_kind . "'");
                }
            }
            while ($left_eventsDb = $left_events->fetch(PDO::FETCH_OBJ)) {
                if (isset($right_event_array[$left_eventsDb->event_kind]) && $right_event_array[$left_eventsDb->event_kind] === "1" && !isset($_POST['l_' . $left_eventsDb->event_kind . '_' . $left_eventsDb->event_id])) {
                    // TODO: no need to check for event_kind?
                    $dbh->query("DELETE FROM humo_events WHERE event_id ='" . $left_eventsDb->event_id . "' AND event_kind='" . $left_eventsDb->event_kind . "'");
                }
            }
        }

        $left_address = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND LOCATE('address',connect_sub_kind)!=0 AND connect_connect_id ='" . $result1Db->pers_gedcomnumber . "'");
        $right_address = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND LOCATE('address',connect_sub_kind)!=0 AND connect_connect_id ='" . $result2Db->pers_gedcomnumber . "'");
        if ($right_address->rowCount() > 0) { //if right has no addresses it did not appear in the comparison table, so the whole thing is unnecessary
            while ($left_addressDb = $left_address->fetch(PDO::FETCH_OBJ)) {
                if (!isset($_POST['l_address_' . $left_addressDb->connect_id])) {
                    $dbh->query("DELETE FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_id ='" . $left_addressDb->connect_id . "'");
                }
            }
            while ($right_addressDb = $right_address->fetch(PDO::FETCH_OBJ)) {
                if (isset($_POST['r_address_' . $right_addressDb->connect_id])) { // change right's I to left's I
                    $dbh->query("UPDATE humo_connections SET connect_connect_id ='" . $result1Db->pers_gedcomnumber . "' WHERE connect_id ='" . $right_addressDb->connect_id . "'");
                } else { // clean up database -> remove this entry altogether (IF IT EXISTS...)
                    $dbh->query("DELETE FROM humo_connections WHERE connect_id ='" . $right_addressDb->connect_id . "'");
                }
            }
        }

        $left_source = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND LOCATE('source',connect_sub_kind)!=0 AND connect_connect_id ='" . $result1Db->pers_gedcomnumber . "'");
        $right_source = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND LOCATE('source',connect_sub_kind)!=0 AND connect_connect_id ='" . $result2Db->pers_gedcomnumber . "'");
        if ($right_source->rowCount() > 0) {
            //if right has no sources it did not appear in the comparison table, so the whole thing is unnecessary
            while ($left_sourceDb = $left_source->fetch(PDO::FETCH_OBJ)) {
                if (!isset($_POST['l_source_' . $left_sourceDb->connect_id])) {
                    $dbh->query("DELETE FROM humo_connections WHERE connect_id ='" . $left_sourceDb->connect_id . "'");
                }
            }
            while ($right_sourceDb = $right_source->fetch(PDO::FETCH_OBJ)) {
                if (isset($_POST['r_source_' . $right_sourceDb->connect_id])) { // change right's I to left's I
                    $dbh->query("UPDATE humo_connections SET connect_connect_id ='" . $result1Db->pers_gedcomnumber . "' WHERE connect_id ='" . $right_sourceDb->connect_id . "'");
                } else {
                    // clean up database -> remove this entry altogether (IF IT EXISTS...)
                    $dbh->query("DELETE FROM humo_connections WHERE connect_id ='" . $right_sourceDb->connect_id . "'");
                }
            }
        }
    } else {
        // for automatic mode check for situation where right has event/source/address data and left not. In that case use right's.
        // $right_result = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND event_connect_kind='person' AND event_connect_id ='" . $result2Db->pers_gedcomnumber . "'");
        $right_result = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND (event_connect_kind='person' OR event_kind='ASSO') AND event_connect_id ='" . $result2Db->pers_gedcomnumber . "'");
        while ($right_resultDb = $right_result->fetch(PDO::FETCH_OBJ)) {
            $left_result = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND event_connect_id ='" . $result1Db->pers_gedcomnumber . "'");
            $foundleft = false;
            while ($left_resultDb = $left_result->fetch(PDO::FETCH_OBJ)) {
                if ($left_resultDb->event_kind == $right_resultDb->event_kind && $left_resultDb->event_gedcom == $right_resultDb->event_gedcom) {
                    // NOTE: if "event" or "name" we also check for sub-type (_AKAN, _HEBN, BARM etc) so as not to match different subtypes
                    // this event from right wil not be copied to left - left already has this type event
                    // so clear the database
                    $dbh->query("DELETE FROM humo_events WHERE event_id ='" . $right_resultDb->event_id . "'");
                    $foundleft = true;
                }
            }
            if ($foundleft == false) { // left has no such type of event, so change right's I for left I at this event
                //$dbh->query("UPDATE humo_events SET event_connect_kind='person', event_connect_id ='" . $result1Db->pers_gedcomnumber . "' WHERE event_id ='" . $right_resultDb->event_id . "'");
                $dbh->query("UPDATE humo_events SET event_connect_id ='" . $result1Db->pers_gedcomnumber . "' WHERE event_id ='" . $right_resultDb->event_id . "'");
            }
        }

        // Do same for sources and address (from connections table). no need here to differentiate between sources and addresses, all will be handled
        $right_result = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_connect_id ='" . $result2Db->pers_gedcomnumber . "'");
        while ($right_resultDb = $right_result->fetch(PDO::FETCH_OBJ)) {
            $left_result = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_connect_id ='" . $result1Db->pers_gedcomnumber . "'");
            $foundleft = false;
            while ($left_resultDb = $left_result->fetch(PDO::FETCH_OBJ)) {
                if ($left_resultDb->connect_sub_kind == $right_resultDb->connect_sub_kind) {
                    // NOTE: We check for sub-kind so as not to match different sub_kinds
                    // this source/address sub_kind from right will not be copied to left - left already has a source/address for this sub_kind
                    // so clear right's data from the database
                    $dbh->query("DELETE FROM humo_connections WHERE connect_id ='" . $right_resultDb->connect_id . "'");
                    $foundleft = true;
                }
            }
            if ($foundleft == false) { // left has no such sub_kind of source/address, so change right's I for left I at this sub_kind
                $dbh->query("UPDATE humo_connections SET connect_connect_id ='" . $result1Db->pers_gedcomnumber . "' WHERE connect_id ='" . $right_resultDb->connect_id . "'");
            }
        }
    }
    // Delete right I from humo_persons table
    $qry = "DELETE FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber ='" . $result2Db->pers_gedcomnumber . "'";
    $dbh->query($qry);

    // Substract 1 person from the number of persons counter in the family tree.
    $sql = "UPDATE humo_trees SET tree_persons=tree_persons-1 WHERE tree_id='" . $trees['tree_id'] . "'";
    $dbh->query($sql);

    // CLEANUP: delete this person's I from any other tables that refer to this person
    // *** TODO 2021: address_connect_xxxx is no longer in use. Will be removed later ***
    $qry = "DELETE FROM humo_addresses WHERE address_tree_id='" . $trees['tree_id'] . "' AND address_connect_sub_kind='person' AND address_connect_id ='" . $result2Db->pers_gedcomnumber . "'";
    $dbh->query($qry);

    $qry = "DELETE FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_connect_id ='" . $result2Db->pers_gedcomnumber . "'";
    $dbh->query($qry);

    //$qry = "DELETE FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND event_connect_kind='person' AND event_connect_id ='" . $result2Db->pers_gedcomnumber . "'";
    $qry = "DELETE FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND (event_connect_kind='person' OR event_kind='ASSO')  AND event_connect_id ='" . $result2Db->pers_gedcomnumber . "'";
    $dbh->query($qry);

    // CLEANUP: This person's I may still exist in the humo_events table under "event_event",
    // in case of birth/death declaration or bapt/burial witness. If so, change the GEDCOM to the left person's I:
    $qry = "UPDATE humo_events SET event_connect_id2 = '" . $result1Db->pers_gedcomnumber . "' WHERE event_tree_id='" . $trees['tree_id'] . "' AND event_connect_id2 ='" . $result2Db->pers_gedcomnumber . "'";
    $dbh->query($qry);

    // remove from the relatives-to-merge pairs in the database any pairs that contain the deleted right person
    if (isset($trees['relatives_merge'])) {
        $temp_rel_arr = explode(";", $trees['relatives_merge']);
        $new_rel_string = '';
        for ($x = 0; $x < count($temp_rel_arr); $x++) {
            // one array piece is I354@I54. We DONT want to match "I35" or "I5" 
            // so to make sure we find the complete number we look for I354@ or for I345;
            if (
                strstr($temp_rel_arr[$x], $result2Db->pers_gedcomnumber . "@") === false && strstr($temp_rel_arr[$x] . ";", $result2Db->pers_gedcomnumber . ";") === false
            ) {
                $new_rel_string .= $temp_rel_arr[$x] . ";";
            }
        }
        $trees['relatives_merge'] = substr($new_rel_string, 0, -1); // take off last ;
        $db_functions->update_settings('rel_merge_' . $trees['tree_id'], $trees['relatives_merge']);
    }

    if (isset($_SESSION['dupl_arr_' . $trees['tree_id']])) { //remove this pair from the dupl_arr array
        $found1 = $result1Db->pers_id . ';' . $result2Db->pers_id;
        $found2 = $result2Db->pers_id . ';' . $result1Db->pers_id;
        for ($z = 0; $z < count($_SESSION['dupl_arr_' . $trees['tree_id']]); $z++) {
            if ($_SESSION['dupl_arr_' . $trees['tree_id']][$z] == $found1 or $_SESSION['dupl_arr_' . $trees['tree_id']][$z] == $found2) {
                //unset($_SESSION['dupl_arr'][$z]) ;
                array_splice($_SESSION['dupl_arr_' . $trees['tree_id']], $z, 1);
            }
        }
    }

    if ($mode != 'automatic' && $mode != 'relatives') {
        echo '<br>' . $name2 . __(' was successfully merged into ') . $name1 . '<br><br>';  // john was successfully merged into jack
        $rela = explode(';', $trees['relatives_merge']);
        $rela = count($rela) - 1;
        if ($rela > 0) {
            printf(__('After this merge there are %d surrounding relatives to be checked for merging!'), $rela);
            echo '<br><br>';

            echo __('<b>You are strongly advised to move to "Relatives merge" mode to check all surrounding persons who may have to be checked for merging.</b><br>
While in "Relatives merge" mode, any persons who might need merging as a result of consequent merges will be added automatically.<br>
This is the easiest way to make sure you don\'t forget anyone.');
            echo '<br><br>';
        ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <form method="post" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="tree">
                <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                <input type="submit" style="font-weight:bold;font-size:120%" name="relatives" value="<?= __('Relatives merge'); ?>" class="btn btn-sm btn-success">
            </form>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <form method="post" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="tree">
                <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                <?php
                if (isset($_POST['left'])) { // manual merge
                    echo '<input type="submit" name="manual" value="' . __('Continue manual merge') . '" class="btn btn-sm btn-success">';
                } else { // duplicate merge
                    echo '<input type="submit" name="duplicate_compare" value="' . __('Continue duplicate merge') . '" class="btn btn-sm btn-success">';
                }
                ?>
            </form>
        <?php } else { ?>
            <br>
            <form method="post" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="tree">
                <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                <input type="hidden" name="menu_admin" value="<?= $trees['menu_tab']; ?>">
                <?php
                if (isset($_POST['left'])) { // manual merge
                    echo '<input type="submit" name="manual" value="' . __('Choose another pair') . '" class="btn btn-sm btn-success">';
                } else { // duplicate merge
                    echo '<input type="submit" name="duplicate_compare" value="' . __('Continue with next pair') . '" class="btn btn-sm btn-success">';
                }
                ?>
            </form>
<?php
        }
    }
}

/**
 * function check_regular checks if data from the humo_person table was marked (checked) in the comparison table
 */
function check_regular($post_var, $auto_var, $mysql_var)
{
    global $dbh, $result1Db, $result2Db;
    if (isset($_POST[$post_var]) && $_POST[$post_var] == '2' || $auto_var == '2') {
        $qry = "UPDATE humo_persons SET " . $mysql_var . " = '" . $result2Db->$mysql_var . "' WHERE pers_id ='" . $result1Db->pers_id . "'";
        $dbh->query($qry);
    }
}

/**
 * function check_regular_text checks if text data from the humo_person table was marked (checked) in the comparison table
 */
function check_regular_text($post_var, $auto_var, $mysql_var)
{
    global $dbh, $trees, $result1Db, $result2Db;
    if (isset($_POST[$post_var . '_r']) || $auto_var == '2') {
        if (isset($_POST[$post_var . '_l'])) { // when not in automatic mode, this means we have to join the notes of left and right
            // If left or right has a @N34@ text entry we join the text as regular text.
            // We can't change the notes in humoX_texts because they could be used for other persons!
            if (substr($result1Db->$mysql_var, 0, 2) === '@N') {
                $noteqry = $dbh->query("SELECT text_text FROM humo_texts WHERE text_tree_id='" . $trees['tree_id'] . "' AND text_gedcomnr = '" . substr($result1Db->$mysql_var, 1, -1) . "'");
                $noteqryDb = $noteqry->fetch(PDO::FETCH_OBJ);
                $leftnote = $noteqryDb->text_text;
            } else {
                $leftnote = $result1Db->$mysql_var;
            }
            if (substr($result2Db->$mysql_var, 0, 2) === '@N') {
                $noteqry = $dbh->query("SELECT text_text FROM humo_texts WHERE text_tree_id='" . $trees['tree_id'] . "' AND text_gedcomnr = '" . substr($result2Db->$mysql_var, 1, -1) . "'");
                $noteqryDb = $noteqry->fetch(PDO::FETCH_OBJ);
                $rightnote = $noteqryDb->text_text;
            } else {
                $rightnote = $result2Db->$mysql_var;
            }
            $qry = "UPDATE humo_persons SET " . $mysql_var . " = CONCAT('" . $leftnote . "',\"\n\",'" . $rightnote . "') WHERE pers_id ='" . $result1Db->pers_id . "'";
        } else {
            $qry = "UPDATE humo_persons SET " . $mysql_var . " = '" . $result2Db->$mysql_var . "' WHERE pers_id ='" . $result1Db->pers_id . "'";
        }
        $dbh->query($qry);
    }
}

/**
 * function popclean prepares a mysql output string for presentation with popup_merge.js
 */
function popclean($input)
{
    return str_replace(array("\r\n", "\n\r", "\r", "\n"), "<br>", htmlentities(addslashes($input), ENT_QUOTES));
}
