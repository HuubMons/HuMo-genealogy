<?php
require_once __DIR__ . "/../models/tree_check.php";

include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../include/select_tree.php");

class TreeCheckController
{
    public function detail($dbh)
    {
        $tree_checkModel = new TreeCheckModel($dbh);

        $tree_check['tab'] = $tree_checkModel->menu_tab();

        return $tree_check;
    }

    /* Seperate model for each tab menu file?
    if ($tree_check['tab'] == 'changes') {
        require_once __DIR__ . "/../models/tree_check_changes.php";
    } elseif ($tree_check['tab'] == 'integrity') {
        require_once __DIR__ . "/../models/tree_check_integrity.php";
    } elseif ($tree_check['tab'] == 'invalid') {
        require_once __DIR__ . "/../models/tree_check_invalid.php";
    } elseif ($tree_check['tab'] == 'consistency') {
        require_once __DIR__ . "/../models/tree_check_consistency.php";
    }
    */
}
