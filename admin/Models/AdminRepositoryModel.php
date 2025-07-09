<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class AdminRepositoryModel extends AdminBaseModel
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

    public function update_repository($editor_cls): void
    {
        $userid = false;
        if (is_numeric($_SESSION['user_id_admin'])) {
            $userid = $_SESSION['user_id_admin'];
        }

        if (isset($_POST['repo_add'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'R' . $this->db_functions->generate_gedcomnr($this->tree_id, 'repo');

            $sql = "INSERT INTO humo_repositories (
                repo_tree_id,
                repo_gedcomnr,
                repo_name,
                repo_address,
                repo_zip,
                repo_place,
                repo_phone,
                repo_date,
                repo_text,
                repo_mail,
                repo_url,
                repo_new_user_id
            ) VALUES (
                :repo_tree_id,
                :repo_gedcomnr,
                :repo_name,
                :repo_address,
                :repo_zip,
                :repo_place,
                :repo_phone,
                :repo_date,
                :repo_text,
                :repo_mail,
                :repo_url,
                :repo_new_user_id
            )";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':repo_tree_id' => $this->tree_id,
                ':repo_gedcomnr' => $new_gedcomnumber,
                ':repo_name' => $_POST['repo_name'],
                ':repo_address' => $_POST['repo_address'],
                ':repo_zip' => $_POST['repo_zip'],
                ':repo_place' => $_POST['repo_place'],
                ':repo_phone' => $_POST['repo_phone'],
                ':repo_date' => $editor_cls->date_process('repo_date'),
                ':repo_text' => $_POST['repo_text'],
                ':repo_mail' => $_POST['repo_mail'],
                ':repo_url' => $_POST['repo_url'],
                ':repo_new_user_id' => $userid
            ]);

            $this->repo_id = $this->dbh->lastInsertId();
        }

        if (isset($_POST['repo_change'])) {
            $sql = "UPDATE humo_repositories SET
                repo_name = :repo_name,
                repo_address = :repo_address,
                repo_zip = :repo_zip,
                repo_place = :repo_place,
                repo_phone = :repo_phone,
                repo_date = :repo_date,
                repo_text = :repo_text,
                repo_mail = :repo_mail,
                repo_url = :repo_url,
                repo_changed_user_id = :repo_changed_user_id
                WHERE repo_id = :repo_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':repo_name' => $_POST['repo_name'],
                ':repo_address' => $_POST['repo_address'],
                ':repo_zip' => $_POST['repo_zip'],
                ':repo_place' => $_POST['repo_place'],
                ':repo_phone' => $_POST['repo_phone'],
                ':repo_date' => $editor_cls->date_process('repo_date'),
                ':repo_text' => $_POST['repo_text'],
                ':repo_mail' => $_POST['repo_mail'],
                ':repo_url' => $_POST['repo_url'],
                ':repo_changed_user_id' => $userid,
                ':repo_id' => $this->repo_id
            ]);
        }

        if (isset($_POST['repo_remove2'])) {
            // *** Find gedcomnumber, needed for events query ***
            $repo_qry = $this->dbh->query("SELECT * FROM humo_repositories WHERE repo_id='" . $this->repo_id . "'");
            $repoDb = $repo_qry->fetch(PDO::FETCH_OBJ);

            // *** Delete repository link ***
            $sql = "UPDATE humo_sources SET source_repo_gedcomnr='' WHERE source_tree_id='" . $this->tree_id . "' AND source_repo_gedcomnr='" . $repoDb->repo_gedcomnr . "'";
            $this->dbh->query($sql);

            // *** Delete repository ***
            $sql = "DELETE FROM humo_repositories WHERE repo_id='" . $this->repo_id . "'";
            $this->dbh->query($sql);

            // *** Reset selected repository ***
            $this->repo_id = NULL;
        }
    }
}
