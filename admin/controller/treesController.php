<?php
require_once __DIR__ . "/../models/trees.php";

class TreesController
{
    /*
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }
    */

    public function detail($dbh, $tree_id, $db_functions, $selected_language)
    {
        $treesModel = new TreesModel($dbh);

        $treesModel->set_tree_id($tree_id); // $tree_id from index.php.
        $treesModel->update_tree($dbh, $db_functions);
        $trees['tree_id'] = $treesModel->get_tree_id();

        $trees['language'] = $treesModel->get_language($selected_language);
        $trees['menu_tab'] = $treesModel->get_menu_tab();

        // *** Menu tab: tree_merge ***
        if ($trees['menu_tab'] == 'tree_merge') {
            require_once __DIR__ . "/../models/tree_merge.php";

            $treeMergeModel = new TreeMergeModel($dbh);
            $trees['relatives_merge'] = $treeMergeModel->get_relatives_merge($dbh, $trees['tree_id']);

            $treeMergeModel->update_settings($db_functions); // *** Store and reset tree merge settings ***
        }

        return $trees;
    }
}
