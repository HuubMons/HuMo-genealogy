<?php
class NotesController
{
    public function detail($dbh)
    {
        $notesModel = new NotesModel($dbh);
        $notes['limit'] = $notesModel->get_limit();
        $notes['user_notes'] = $notesModel->get_user_notes();
        $notes['editor_notes'] = $notesModel->get_editor_notes();
        //$notesModel->set_note_id();
        $notesModel->update_note($dbh);

        return $notes;
    }
}
