<?php
class ListNamesController
{
    public function list_names($dbh, $tree_id, $user, $last_name)
    {
        $list_namesModel = new listNamesModel();

        $list_names['alphabet_array'] = $list_namesModel->getAlphabetArray($dbh, $tree_id, $user);
        $list_names['max_cols'] = $list_namesModel->getMaxCols();
        $list_names['max_names'] = $list_namesModel->getMaxNames();

        // TODO move to model.
        if (!isset($last_name)) {
            $last_name = 'a'; // *** Default first_character ***
        }
        if (isset($_GET['last_name']) && $_GET['last_name'] && is_string($_GET['last_name'])) {
            $last_name = safe_text_db($_GET['last_name']);
        }
        $list_names['last_name'] = $last_name;

        $list_names["item"] = 0;
        if (isset($_GET['item'])) {
            $list_names["item"] = $_GET['item'];
        }

        $list_names["start"] = 0;
        if (isset($_GET["start"])) {
            $list_names["start"] = $_GET["start"];
        }

        return $list_names;
    }
}
