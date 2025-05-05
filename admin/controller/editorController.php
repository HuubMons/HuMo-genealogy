<?php
class EditorController
{
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new Editor_cls;
    }

    public function detail($dbh, $tree_id, $tree_prefix, $db_functions, $humo_option)
    {
        $editorModel = new EditorModel($dbh, $tree_id, $tree_prefix, $db_functions, $this->editor_cls, $humo_option);

        //$editorModel->set_pers_alive();
        $editorModel->set_hebrew_night();
        $editorModel->set_pers_gedcomnumber($db_functions);
        $editorModel->set_search_name();
        $editorModel->set_marriage();

        $editor['confirm'] = $editorModel->update_editor();
        $editor['confirm'] .= $editorModel->update_editor2();

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

        $editor['favorites'] = $editorModel->get_favorites($editor['new_tree']);

        return $editor;
    }
}
