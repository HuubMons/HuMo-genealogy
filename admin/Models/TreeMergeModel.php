<?php

namespace Genealogy\Admin\Models;

use PDO;

class TreeMergeModel
{
    public function get_relatives_merge($dbh, $tree_id)
    {
        $relatives_merge = '';
        $relmerge = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'rel_merge_" . $tree_id . "'");
        if ($relmerge->rowCount() > 0) {
            $relmergeDb = $relmerge->fetch(PDO::FETCH_OBJ);
            $relatives_merge = $relmergeDb->setting_value;
        } else {
            // the rel_merge row didn't exist yet - make it, with empty value
            $dbh->query("INSERT INTO humo_settings (setting_variable, setting_value) VALUES ('rel_merge_" . $tree_id . "', '')");
        }
        return $relatives_merge;
    }

    public function update_settings($db_functions)
    {
        if (isset($_POST['settings']) || isset($_POST['reset'])) {

            if (isset($_POST['merge_chars']) &&  is_numeric($_POST['merge_chars'])) {
                $db_functions->update_settings('merge_chars', $_POST['merge_chars']);
            }
            if (isset($_POST['merge_dates']) && ($_POST['merge_dates'] == 'YES' || $_POST['merge_dates'] == 'NO')) {
                $db_functions->update_settings('merge_dates', $_POST['merge_dates']);
            }
            if (isset($_POST['merge_lastname']) && ($_POST['merge_lastname'] == 'YES' || $_POST['merge_lastname'] == 'NO')) {
                $db_functions->update_settings('merge_lastname', $_POST['merge_lastname']);
            }
            if (isset($_POST['merge_firstname']) && ($_POST['merge_firstname'] == 'YES' || $_POST['merge_firstname'] == 'NO')) {
                $db_functions->update_settings('merge_firstname', $_POST['merge_firstname']);
            }
            if (isset($_POST['merge_parentsdate']) && ($_POST['merge_parentsdate'] == 'YES' || $_POST['merge_parentsdate'] == 'NO')) {
                $db_functions->update_settings('merge_parentsdate', $_POST['merge_parentsdate']);
            }

            if (isset($_POST['reset'])) {
                $db_functions->update_settings('merge_chars', '10');
                $db_functions->update_settings('merge_dates', 'YES');
                $db_functions->update_settings('merge_lastname', 'YES');
                $db_functions->update_settings('merge_firstname', 'YES');
                $db_functions->update_settings('merge_parentsdate', 'YES');
            }
        }
    }
}
