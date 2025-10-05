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
$treeMerge = new \Genealogy\Include\TreeMerge($dbh, $trees);

// the following creates the pages that cycle through all duplicates that are stored in the dupl_arr array
// the pages themselves are presented with the "show_pair function"
if (isset($_POST['duplicate_compare'])) {
    if ($trees['no_more_duplicates']) {
?>
        <br><br><?= __('No more duplicates found'); ?><br><br>
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } else { ?>
        <div class="alert alert-warning my-3" role="alert">
            <?= __('Carefully compare these two persons. Only if you are <b>absolutely sure</b> they are identical, press "Merge right into left".'); ?>
        </div>

        <div class="row mb-3">
            <div class="col-md-auto">
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-secondary">
                </form>
            </div>

            <div class="col-md-auto ms-2">
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="hidden" name="no_increase" value="1">
                    <input type="hidden" name="left" value="<?= $trees['right_person']; ?>">
                    <input type="hidden" name="right" value="<?= $trees['left_person']; ?>">
                    <input type="submit" name="duplicate_compare" value="<?= __('<- Switch left and right ->'); ?>" class="btn btn-sm btn-secondary">
                </form>
            </div>

            <div class="col-md-auto ms-2">
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display: inline;">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="submit" name="duplicate_compare" value="<?= __('Skip to next'); ?>" class="btn btn-sm btn-secondary">
                </form>
            </div>

            <div class="col-md-auto ms-2">
                <?= __('Skip to nr: '); ?>
            </div>
            <div class="col-md-auto">
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display: inline;">
                    <div class="input-group">
                        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                        <select style="max-width:60px" name="choice_nr" class="form-select form-select-sm">
                            <?php for ($x = 0; $x < count($_SESSION['dupl_arr_' . $trees['tree_id']]); $x++) { ?>
                                <option value="<?= $x; ?>" <?= $x == $_SESSION['present_compare_' . $trees['tree_id']] ? 'selected' : ''; ?>><?= ($x + 1); ?></option>
                            <?php } ?>
                        </select>

                        <input type="submit" name="duplicate_compare" value="<?= __('Go!'); ?>" class="btn btn-sm btn-secondary">
                    </div>
                </form>
            </div>
        </div>

        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display: inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="dupl" value="1">
            <input type="submit" name="merge" value="<?= __('Merge right into left'); ?>" class="btn btn-sm btn-success"><br><br>

            <?php $treeMerge->show_pair($trees['left_person'], $trees['right_person'], 'duplicate'); ?><br>
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
        <div class="alert alert-warning my-3" role="alert">
            <?= __('You cannot merge persons of opposite sex. Please try again'); ?>
        </div>

        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" name="manual" value="<?= __('Choose another pair'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } elseif ($per1Db->pers_gedcomnumber == $per2Db->pers_gedcomnumber) { ?>
        <div class="alert alert-warning my-3" role="alert">
            <?= __('This is one person already - you can\'t merge! Please try again'); ?>
        </div>

        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" name="manual" value="<?= __('Choose another pair'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } else { ?>
        <div class="alert alert-warning my-3" role="alert">
            <?= __('Carefully compare these two persons. Only if you are <b>absolutely sure</b> they are identical, press "Merge right into left".'); ?><br>
            <?= __('The checked items will be the ones entered into the database for the merged person. You can change the default settings'); ?>
        </div>

        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="left" value="<?= $_POST['right']; ?>">
            <input type="hidden" name="right" value="<?= $_POST['left']; ?>">
            <input type="submit" name="manual_compare" value="<?= __('<- Switch left and right ->'); ?>" class="btn btn-sm btn-success">
        </form>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" name="manual" value="<?= __('Choose another pair'); ?>" class="btn btn-sm btn-success">
        </form>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
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
    // the "surrounding relatives" array is created in all merge modes (in the merge_them function) and saved to the database

    if ($trees['show_merge_pair']) {
    ?>
        <br>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>

        <!-- button skip -->
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="skip_rel" value="1">
            <input type="submit" name="relatives" value="<?= __('Skip to next'); ?>" class="btn btn-sm btn-success">
        </form>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="swap" value="1">
            <input type="hidden" name="left" value="<?= $trees['right_person']; ?>">
            <input type="hidden" name="right" value="<?= $trees['left_person']; ?>">
            <input type="submit" name="relatives" value="<?= __('<- Switch left and right ->'); ?>" class="btn btn-sm btn-success">
        </form>

        <!-- button merge -->
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="left" value="<?= $trees['left_person']; ?>">
            <input type="hidden" name="right" value="<?= $trees['right_person']; ?>">
            <input type="hidden" name="rela" value="1">
            <input type="submit" name="relatives" value="<?= __('Merge right into left'); ?>" class="btn btn-sm btn-secondary">
            <br><br>
            <?php $treeMerge->show_pair($trees['left_person'], $trees['right_person'], 'relatives'); ?><br>
        </form>
    <?php
    }

    if ($trees['no_more_duplicates']) {
    ?>
        <br><br><?= __('No more surrounding relatives to check'); ?><br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php
    }
} elseif (isset($_POST['merge'])) {
    // do merge and allow to continue with comparing duplicates
    // this is called up by the "Merge" button in manual and duplicate merge modes

    echo '<br>' . $trees['name2'] . __(' was successfully merged into ') . $trees['name1'] . '<br><br>';  // john was successfully merged into jack
    if ($trees['rela'] > 0) {
        printf(__('After this merge there are %d surrounding relatives to be checked for merging!'), $trees['rela']);
        echo '<br><br>';

        echo __('<b>You are strongly advised to move to "Relatives merge" mode to check all surrounding persons who may have to be checked for merging.</b><br>
While in "Relatives merge" mode, any persons who might need merging as a result of consequent merges will be added automatically.<br>
This is the easiest way to make sure you don\'t forget anyone.');
        echo '<br><br>';
    ?>
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" style="font-weight:bold;font-size:120%" name="relatives" value="<?= __('Relatives merge'); ?>" class="btn btn-sm btn-success ms-3">
        </form>

        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <?php if (isset($_POST['left'])) { ?>
                <input type="submit" name="manual" value="<?= __('Continue manual merge'); ?>" class="btn btn-sm btn-success ms-5">
            <?php } else { ?>
                <input type="submit" name="duplicate_compare" value="<?= __('Continue duplicate merge'); ?>" class="btn btn-sm btn-success ms-5">
            <?php } ?>
        </form>
    <?php } else { ?>
        <br>
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <?php if (isset($_POST['left'])) { ?>
                <input type="submit" name="manual" value="<?= __('Choose another pair'); ?>" class="btn btn-sm btn-success">
            <?php } else { ?>
                <input type="submit" name="duplicate_compare" value="<?= __('Continue with next pair'); ?>" class="btn btn-sm btn-success">
            <?php } ?>
        </form>
    <?php
    }
} elseif (isset($_POST['duplicate'])) {
    // this is called when the "duplicate merge" button is used on the duplicate merge page
    // it creates the dupl_arr array with all duplicates found

    //echo __('Please wait while duplicate list is generated');
    ?>

    <?php if ($trees['count_duplicates'] > 0) { ?>
        <br><?= __('Possible duplicates found: ') . $trees['count_duplicates']; ?><br><br>

        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" name="duplicate_compare" value="<?= __('Start comparing duplicates'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } else { ?>
        <br><?= __('No duplicates found. Duplicate merge and Automatic merge won\'t result in merges!'); ?><br>
        <?= __('You can try one of the other merge options'); ?><br><br>

        &nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php
    }
} elseif (isset($_POST['auto_merge'])) {
    // this checks the persons that can be merged automatically and merges them with the "merge_them" function

    //echo '<br>' . __('Please wait while the automatic merges are processed...') . '<br>';

    if ($trees['merges'] == 0) {
        echo '<br>' . __('No automatic merge options were found.') . '<br><br>';
    } else {
        echo '<br>' . __('Automatic merge completed') . ' ' . $trees['merges'] . __(' merges were performed') . '<br><br>';
    }

    if ($trees['relatives_merge'] != '') {
    ?>
        <?= __('It is recommended to continue with <b>"Relatives merge"</b> to consider merging persons affected by previous merges that were performed.'); ?><br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" style="font-weight:bold;font-size:120%" name="relatives" value="<?= __('Relatives merge'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } else { ?>
        <?= __('You may wish to proceed with duplicate merge or manual merge.'); ?><br><br>

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" name="duplicate_merge" value="<?= __('Duplicate merge'); ?>" class="btn btn-sm btn-secondary">
        </form>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" name="manual" value="<?= __('Manual merge'); ?>" class="btn btn-sm btn-success">
        </form>
    <?php } ?>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="submit" value="<?= __('Back to main merge menu'); ?>" class="btn btn-sm btn-success">
    </form>

    <?php if (isset($trees['mergedlist'])) { ?>
        <br><br><b><u><?= __('These are the persons that were merged:'); ?></u></b><br>
    <?php
        for ($i = 0; $i < count($trees['mergedlist']); $i++) {
            $resultDb = $db_functions->get_person_with_id($trees['mergedlist'][$i]);
            echo $resultDb->pers_lastname . ', ' . $resultDb->pers_firstname . ' ' . strtolower(str_replace("_", " ", $resultDb->pers_prefix)) . ' (#' . $resultDb->pers_gedcomnumber . ')<br>';
        }
    }
} else {
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
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
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
                    <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
                        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
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
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
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
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="submit" style="min-width:150px" name="manual" value="<?= __('Manual merge'); ?>" class="btn btn-sm btn-success">
                </form>
            </td>
            <td><?= __('You can pick two persons out of the database to consider for merging.'); ?></td>
        </tr>
        <tr>
            <td style="vertical-align:center;text-align:center;width:200px">
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_merge" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="submit" style="min-width:150px" name="settings" value="<?= __('Settings'); ?>" class="btn btn-sm btn-success">
                </form>
            </td>
            <td><?= __('Here you can change the default filters for the different merge options.'); ?></td>
        </tr>
    </table>
<?php
}
