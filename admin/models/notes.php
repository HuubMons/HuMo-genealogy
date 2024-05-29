<?php
class NotesModel
{
    //private $note_id;

    //public function set_note_id()
    //{
    //    if (isset($_POST['note_id']) and is_numeric(($_POST['note_id']))) {
    //        $this->note_id = $_POST['note_id'];
    //    }
    //}

    public function get_limit()
    {
        $limit = 50;
        if (isset($_POST['limit']) && is_numeric($_POST['limit'])) {
            $limit = safe_text_db($_POST['limit']);
            $_SESSION['save_limit'] = $limit;
        }
        if (isset($_SESSION['save_limit']) && is_numeric($_SESSION['save_limit'])) {
            $limit = $_SESSION['save_limit'];
        }
        return $limit;
    }

    public function get_user_notes()
    {
        $user_notes = true;
        if (isset($_POST['note_settings'])) {
            $user_notes = false;
            if (isset($_POST['user_notes'])) {
                $user_notes = true;
            }
            $_SESSION['save_user_notes'] = $user_notes;
        }
        if (isset($_SESSION['save_user_notes'])) {
            $user_notes = $_SESSION['save_user_notes'];
        }
        return $user_notes;
    }

    public function get_editor_notes()
    {
        $editor_notes = true;
        if (isset($_POST['note_settings'])) {
            $editor_notes = false;
            if (isset($_POST['editor_notes'])) {
                $editor_notes = true;
            }
            $_SESSION['save_editor_notes'] = $editor_notes;
        }
        if (isset($_SESSION['save_editor_notes'])) {
            $editor_notes = $_SESSION['save_editor_notes'];
        }
        return $editor_notes;
    }

    public function update_note($dbh): void
    {
        if (isset($_POST['note_status']) && is_numeric($_POST['note_id'])) {
            $note_status = '';
            if ($_POST['note_status'] == 'new') {
                $note_status = 'new';
            }
            if ($_POST['note_status'] == 'approved') {
                $note_status = 'approved';
            }
            if ($note_status) {
                $sql = "UPDATE humo_user_notes SET note_status='" . $note_status . "' WHERE note_id='" . $_POST['note_id'] . "'";
                $dbh->query($sql);
            }
        }
    }
}
