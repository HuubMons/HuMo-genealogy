<?php
class NotesController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $notesModel = new NotesModel($this->admin_config);

        $notes['limit'] = $notesModel->get_limit();
        $notes['user_notes'] = $notesModel->get_user_notes();
        $notes['editor_notes'] = $notesModel->get_editor_notes();
        //$notesModel->set_note_id();
        $notesModel->update_note();

        return $notes;
    }
}
