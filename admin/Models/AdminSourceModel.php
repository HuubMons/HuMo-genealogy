<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use Genealogy\Include\SafeTextDb;
use Genealogy\Include\ValidateGedcomnumber;
use PDO;
use PDOException;

class AdminSourceModel extends AdminBaseModel
{
    private $source_id;

    public function set_source_id(): void
    {
        if (isset($_POST['source_id']) && is_numeric(($_POST['source_id']))) {
            $this->source_id = $_POST['source_id'];
        }

        // *** Link to select is using gedcomnr in $_GET['source_id'] ***
        if (isset($_GET['source_id'])) {
            $sql = "SELECT source_id FROM humo_sources WHERE source_tree_id=:source_tree_id AND source_gedcomnr=:source_gedcomnr";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':source_tree_id' => $this->tree_id,
                ':source_gedcomnr' => $_GET['source_id']
            ]);
            $source = $stmt->fetch(PDO::FETCH_OBJ);
            $this->source_id = $source->source_id;
        }
    }

    public function get_source_id()
    {
        return $this->source_id;
    }

    public function get_sources(): array
    {
        $safeTextDb = new SafeTextDb();
        $validateGedcomnumber = new ValidateGedcomnumber();

        $editSource['search_gedcomnr'] = '';
        if (isset($_POST['source_search_gedcomnr']) && $validateGedcomnumber->validate($_POST['source_search_gedcomnr'])) {
            $editSource['search_gedcomnr'] = $_POST['source_search_gedcomnr'];
        }
        $editSource['search_text'] = '';
        if (isset($_POST['source_search'])) {
            $editSource['search_text'] = $safeTextDb->safe_text_db($_POST['source_search']);
        }

        $qry = "SELECT * FROM humo_sources WHERE source_tree_id = :tree_id";
        $params = [':tree_id' => $this->tree_id];

        if ($editSource['search_gedcomnr']) {
            $qry .= " AND source_gedcomnr LIKE :gedcomnr";
            $params[':gedcomnr'] = '%' . $editSource['search_gedcomnr'] . '%';
        }
        if ($editSource['search_text']) {
            $qry .= " AND (source_title LIKE :search_text OR (source_title = '' AND source_text LIKE :search_text2))";
            $params[':search_text'] = '%' . $editSource['search_text'] . '%';
            $params[':search_text2'] = '%' . $editSource['search_text'] . '%';
        }
        $qry .= " ORDER BY IF(source_title != '', source_title, source_text) LIMIT 0,200";

        $stmt = $this->dbh->prepare($qry);
        $stmt->execute($params);
        $source_qry = $stmt;

        // Build array result here. Max. results 200.
        while ($sourceDb = $source_qry->fetch(PDO::FETCH_OBJ)) {
            $editSource['sources_id'][] = $sourceDb->source_id;

            $editSource['sources_gedcomnr'][$sourceDb->source_id] = $sourceDb->source_gedcomnr;

            if ($sourceDb->source_title) {
                $editSource['sources_text'][$sourceDb->source_id] = $sourceDb->source_title;
            } else {
                $show_text = substr($sourceDb->source_text, 0, 40);
                if (strlen($sourceDb->source_text) > 40) {
                    $show_text .= '...';
                }
                $editSource['sources_text'][$sourceDb->source_id] = $show_text;
            }

            if ($sourceDb->source_status == 'restricted') {
                $editSource['sources_restricted'][$sourceDb->source_id] = ' *' . __('restricted') . '*';
            } else {
                $editSource['sources_restricted'][$sourceDb->source_id] = '';
            }
        }
        return $editSource;
    }

    public function get_source(): array
    {
        $editSource = [];

        // *** Show selected source ***
        if ($this->source_id || isset($_POST['add_source'])) {
            if (isset($_POST['add_source'])) {
                $editSource['data']['gedcomnr'] = '';
                $editSource['data']['status'] = '';
                $editSource['data']['title'] = '';
                $editSource['data']['date'] = '';
                $editSource['data']['place'] = '';
                $editSource['data']['publ'] = '';
                $editSource['data']['refn'] = '';
                $editSource['data']['auth'] = '';
                $editSource['data']['subj'] = '';
                $editSource['data']['item'] = '';
                $editSource['data']['kind'] = '';
                $editSource['data']['text'] = '';
                $editSource['data']['repo_caln'] = '';
                $editSource['data']['repo_page'] = '';
                $editSource['data']['repo_gedcomnr'] = '';
                $editSource['data']['new_user_id'] = '';
                $editSource['data']['new_datetime'] = '';
                $editSource['data']['changed_user_id'] = '';
                $editSource['data']['changed_datetime'] = '';
            } else {
                $source_qry = $this->dbh->prepare("SELECT * FROM humo_sources WHERE source_tree_id = :tree_id AND source_id = :source_id");
                $source_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_STR);
                $source_qry->bindValue(':source_id', $this->source_id, PDO::PARAM_STR);
                $source_qry->execute();
                //$sourceDb=$db_functions->get_source ($sourcenum);

                $die_message = __('No valid source number.');
                try {
                    $sourceDb = $source_qry->fetch(PDO::FETCH_OBJ);
                } catch (PDOException $e) {
                    echo $die_message;
                }
                $editSource['data']['gedcomnr'] = $sourceDb->source_gedcomnr;
                $editSource['data']['status'] = $sourceDb->source_status;
                $editSource['data']['title'] = $sourceDb->source_title;
                $editSource['data']['date'] = $sourceDb->source_date;
                $editSource['data']['place'] = $sourceDb->source_place;
                $editSource['data']['publ'] = $sourceDb->source_publ;
                $editSource['data']['refn'] = $sourceDb->source_refn;
                $editSource['data']['auth'] = $sourceDb->source_auth;
                $editSource['data']['subj'] = $sourceDb->source_subj;
                $editSource['data']['item'] = $sourceDb->source_item;
                $editSource['data']['kind'] = $sourceDb->source_kind;
                $editSource['data']['text'] = $sourceDb->source_text;
                $editSource['data']['repo_caln'] = $sourceDb->source_repo_caln;
                $editSource['data']['repo_page'] = $sourceDb->source_repo_page;
                $editSource['data']['repo_gedcomnr'] = $sourceDb->source_repo_gedcomnr;
                $editSource['data']['new_user_id'] = $sourceDb->source_new_user_id;
                $editSource['data']['new_datetime'] = $sourceDb->source_new_datetime;
                $editSource['data']['changed_user_id'] = $sourceDb->source_changed_user_id;
                $editSource['data']['changed_datetime'] = $sourceDb->source_changed_datetime;
            }
        }
            return $editSource;
    }

    public function update_source($editor_cls): void
    {
        $userid = false;
        if (is_numeric($_SESSION['user_id_admin'])) {
            $userid = $_SESSION['user_id_admin'];
        }

        if (isset($_POST['source_add'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'S' . $this->db_functions->generate_gedcomnr($this->tree_id, 'source');

            $sql = "INSERT INTO humo_sources (
                source_tree_id,
                source_gedcomnr,
                source_status,
                source_title,
                source_date,
                source_place,
                source_publ,
                source_refn,
                source_auth,
                source_subj,
                source_item,
                source_kind,
                source_repo_caln,
                source_repo_page,
                source_repo_gedcomnr,
                source_text,
                source_new_user_id
            ) VALUES (
                :source_tree_id,
                :source_gedcomnr,
                :source_status,
                :source_title,
                :source_date,
                :source_place,
                :source_publ,
                :source_refn,
                :source_auth,
                :source_subj,
                :source_item,
                :source_kind,
                :source_repo_caln,
                :source_repo_page,
                :source_repo_gedcomnr,
                :source_text,
                :source_new_user_id
            )";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':source_tree_id' => $this->tree_id,
                ':source_gedcomnr' => $new_gedcomnumber,
                ':source_status' => $_POST['source_status'],
                ':source_title' => $_POST['source_title'],
                ':source_date' => $_POST['source_date'],
                ':source_place' => $_POST['source_place'],
                ':source_publ' => $_POST['source_publ'],
                ':source_refn' => $_POST['source_refn'],
                ':source_auth' => $_POST['source_auth'],
                ':source_subj' => $_POST['source_subj'],
                ':source_item' => $_POST['source_item'],
                ':source_kind' => $_POST['source_kind'],
                ':source_repo_caln' => $_POST['source_repo_caln'],
                ':source_repo_page' => $_POST['source_repo_page'],
                ':source_repo_gedcomnr' => $_POST['source_repo_gedcomnr'],
                ':source_text' => $_POST['source_text'],
                ':source_new_user_id' => $userid
            ]);

            $this->source_id = $this->dbh->lastInsertId();
        }

        // Remark: source_change in editorModel.php (used to change sources in familyscreen).
        if (isset($_POST['source_change2'])) {
            $sql = "UPDATE humo_sources SET
                source_status = :source_status,
                source_title = :source_title,
                source_date = :source_date,
                source_place = :source_place,
                source_publ = :source_publ,
                source_refn = :source_refn,
                source_auth = :source_auth,
                source_subj = :source_subj,
                source_item = :source_item,
                source_kind = :source_kind,
                source_repo_caln = :source_repo_caln,
                source_repo_page = :source_repo_page,
                source_repo_gedcomnr = :source_repo_gedcomnr,
                source_text = :source_text,
                source_changed_user_id = :source_changed_user_id
            WHERE source_tree_id = :source_tree_id AND source_id = :source_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':source_status' => $_POST['source_status'],
                ':source_title' => $_POST['source_title'],
                ':source_date' => $editor_cls->date_process('source_date'),
                ':source_place' => $_POST['source_place'],
                ':source_publ' => $_POST['source_publ'],
                ':source_refn' => $_POST['source_refn'],
                ':source_auth' => $_POST['source_auth'],
                ':source_subj' => $_POST['source_subj'],
                ':source_item' => $_POST['source_item'],
                ':source_kind' => $_POST['source_kind'],
                ':source_repo_caln' => $_POST['source_repo_caln'],
                ':source_repo_page' => $_POST['source_repo_page'],
                ':source_repo_gedcomnr' => $_POST['source_repo_gedcomnr'],
                ':source_text' => $editor_cls->text_process($_POST['source_text'], true),
                ':source_changed_user_id' => $userid,
                ':source_tree_id' => $this->tree_id,
                ':source_id' => $this->source_id
            ]);
        }

        if (isset($_POST['source_remove2'])) {
            // *** Delete source ***
            $sql = "DELETE FROM humo_sources WHERE source_id='" . $this->source_id . "'";
            $this->dbh->query($sql);

            // *** Delete connections to source, and re-order remaining source connections ***
            $connect_sql = "SELECT * FROM humo_connections WHERE connect_tree_id = :tree_id AND connect_source_id = :source_gedcomnr";
            $connect_qry = $this->dbh->prepare($connect_sql);
            $connect_qry->execute([
                ':tree_id' => $this->tree_id,
                ':source_gedcomnr' => $_POST['source_gedcomnr']
            ]);
            while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                // *** Delete source connections ***
                $sql = "DELETE FROM humo_connections WHERE connect_id='" . $connectDb->connect_id . "'";
                $this->dbh->query($sql);

                // *** Re-order remaining source connections ***
                $event_order = 1;
                $event_sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "'
                    AND connect_kind='" . $connectDb->connect_kind . "' AND connect_sub_kind='" . $connectDb->connect_sub_kind . "'
                    AND connect_connect_id='" . $connectDb->connect_connect_id . "' ORDER BY connect_order";
                $event_qry = $this->dbh->query($event_sql);
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_connections SET connect_order='" . $event_order . "' WHERE connect_id='" . $eventDb->connect_id . "'";
                    $this->dbh->query($sql);
                    $event_order++;
                }
            }

            // *** Reset selected repository ***
            $this->source_id = NULL;
        }
    }
}
