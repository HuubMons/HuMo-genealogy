<?php
class EditorRepositoryModel
{
    private $repo_id;

    public function set_repo_id(): void
    {
        if (isset($_POST['repo_id']) && is_numeric(($_POST['repo_id']))) {
            $this->repo_id = $_POST['repo_id'];
        }
    }
    public function get_repo_id()
    {
        return $this->repo_id;
    }

    public function update_repository($dbh, $tree_id, $db_functions, $editor_cls): void
    {
        $userid = false;
        if (is_numeric($_SESSION['user_id_admin'])) {
            $userid = $_SESSION['user_id_admin'];
        }

        if (isset($_POST['repo_add'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'R' . $db_functions->generate_gedcomnr($tree_id, 'repo');

            $sql = "INSERT INTO humo_repositories SET
                repo_tree_id='" . $tree_id . "',
                repo_gedcomnr='" . $new_gedcomnumber . "',
                repo_name='" . $editor_cls->text_process($_POST['repo_name']) . "',
                repo_address='" . $editor_cls->text_process($_POST['repo_address']) . "',
                repo_zip='" . safe_text_db($_POST['repo_zip']) . "',
                repo_place='" . $editor_cls->text_process($_POST['repo_place']) . "',
                repo_phone='" . safe_text_db($_POST['repo_phone']) . "',
                repo_date='" . $editor_cls->date_process('repo_date') . "',
                repo_text='" . $editor_cls->text_process($_POST['repo_text']) . "',
                repo_mail='" . safe_text_db($_POST['repo_mail']) . "',
                repo_url='" . safe_text_db($_POST['repo_url']) . "',
                repo_new_user_id='" . $userid . "'";
            $dbh->query($sql);

            $this->repo_id = $dbh->lastInsertId();
        }

        if (isset($_POST['repo_change'])) {
            $sql = "UPDATE humo_repositories SET
                repo_name='" . $editor_cls->text_process($_POST['repo_name']) . "',
                repo_address='" . $editor_cls->text_process($_POST['repo_address']) . "',
                repo_zip='" . safe_text_db($_POST['repo_zip']) . "',
                repo_place='" . $editor_cls->text_process($_POST['repo_place']) . "',
                repo_phone='" . safe_text_db($_POST['repo_phone']) . "',
                repo_date='" . $editor_cls->date_process('repo_date') . "',
                repo_text='" . $editor_cls->text_process($_POST['repo_text']) . "',
                repo_mail='" . safe_text_db($_POST['repo_mail']) . "',
                repo_url='" . safe_text_db($_POST['repo_url']) . "',
                repo_changed_user_id='" . $userid . "'
                WHERE repo_id='" . $this->repo_id . "'";
            $dbh->query($sql);
        }

        if (isset($_POST['repo_remove2'])) {
            // *** Find gedcomnumber, needed for events query ***
            $repo_qry = $dbh->query("SELECT * FROM humo_repositories WHERE repo_id='" . $this->repo_id . "'");
            $repoDb = $repo_qry->fetch(PDO::FETCH_OBJ);

            // *** Delete repository link ***
            $sql = "UPDATE humo_sources SET source_repo_gedcomnr='' WHERE source_tree_id='" . $tree_id . "' AND source_repo_gedcomnr='" . $repoDb->repo_gedcomnr . "'";
            $dbh->query($sql);

            // *** Delete repository ***
            $sql = "DELETE FROM humo_repositories WHERE repo_id='" . $this->repo_id . "'";
            $dbh->query($sql);

            // *** Reset selected repository ***
            $this->repo_id = NULL;
        }
    }
}
