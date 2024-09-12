<?php
require_once  __DIR__ . "/../models/editor.php";

include_once(__DIR__ . "/../include/editor_cls.php");

include_once(__DIR__ . "/../include/select_tree.php");

// *** Used for person color selection for descendants and ancestors, etc. ***
include_once(__DIR__ . "/../../include/ancestors_descendants.php");

include_once(__DIR__ . '/../include/editor_event_cls.php');


// TODO check processing of tree_id in db_functions.
// *** Editor icon for admin and editor: select family tree ***
if (isset($tree_id) && $tree_id) {
    $db_functions->set_tree_id($tree_id);
}


class EditorController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }

    public function detail($dbh, $tree_id, $tree_prefix, $db_functions, $humo_option)
    {
        $editorModel = new EditorModel($dbh, $tree_id, $tree_prefix, $db_functions, $this->editor_cls, $humo_option);
        $editorModel->set_hebrew_night();

        $editorModel->set_pers_gedcomnumber($db_functions);
        $editorModel->set_search_name();
        $editorModel->set_marriage();

        $editor['confirm'] = $editorModel->update_editor();
        $editor['confirm_note'] = $editorModel->update_note();

        $editor['pers_gedcomnumber'] = $editorModel->get_pers_gedcomnumber();

        $editor['search_id'] = $editorModel->get_search_id();

        $editor['search_name'] = $editorModel->get_search_name();

        $editor['new_tree'] = $editorModel->get_new_tree();
        $editorModel->set_favorite($dbh, $tree_id);

        $editor['marriage'] = $editorModel->get_marriage();

        // *** Check for new person ***
        $editorModel->set_add_person();
        $editor['add_person'] = $editorModel->get_add_person();

        $editor['favorites'] = $editorModel->get_favorites($dbh, $tree_id, $editor['new_tree']);

        return $editor;
    }
}
