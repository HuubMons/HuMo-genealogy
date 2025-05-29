<?php
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
        $editSource['search_gedcomnr'] = '';
        if (isset($_POST['source_search_gedcomnr'])) {
            $editSource['search_gedcomnr'] = safe_text_db($_POST['source_search_gedcomnr']);
        }
        $editSource['search_text'] = '';
        if (isset($_POST['source_search'])) {
            $editSource['search_text'] = safe_text_db($_POST['source_search']);
        }

        $qry = "SELECT * FROM humo_sources WHERE source_tree_id='" . $this->tree_id . "'";
        if ($editSource['search_gedcomnr']) {
            $qry .= " AND source_gedcomnr LIKE '%" . safe_text_db($editSource['search_gedcomnr']) . "%'";
        }
        if ($editSource['search_text']) {
            $qry .= " AND ( source_title LIKE '%" . safe_text_db($editSource['search_text']) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($editSource['search_text']) . "%') )";
        }
        $qry .= " ORDER BY IF (source_title!='',source_title,source_text) LIMIT 0,200";

        $source_qry = $this->dbh->query($qry);

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

    public function update_source($editor_cls): void
    {
        $userid = false;
        if (is_numeric($_SESSION['user_id_admin'])) {
            $userid = $_SESSION['user_id_admin'];
        }

        if (isset($_POST['source_add'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'S' . $this->db_functions->generate_gedcomnr($this->tree_id, 'source');

            $sql = "INSERT INTO humo_sources SET
                source_tree_id='" . $this->tree_id . "',
                source_gedcomnr='" . $new_gedcomnumber . "',
                source_status='" . $editor_cls->text_process($_POST['source_status']) . "',
                source_title='" . $editor_cls->text_process($_POST['source_title']) . "',
                source_date='" . safe_text_db($_POST['source_date']) . "',
                source_place='" . $editor_cls->text_process($_POST['source_place']) . "',
                source_publ='" . $editor_cls->text_process($_POST['source_publ']) . "',
                source_refn='" . $editor_cls->text_process($_POST['source_refn']) . "',
                source_auth='" . $editor_cls->text_process($_POST['source_auth']) . "',
                source_subj='" . $editor_cls->text_process($_POST['source_subj']) . "',
                source_item='" . $editor_cls->text_process($_POST['source_item']) . "',
                source_kind='" . $editor_cls->text_process($_POST['source_kind']) . "',
                source_repo_caln='" . $editor_cls->text_process($_POST['source_repo_caln']) . "',
                source_repo_page='" . safe_text_db($_POST['source_repo_page']) . "',
                source_repo_gedcomnr='" . $editor_cls->text_process($_POST['source_repo_gedcomnr']) . "',
                source_text='" . $editor_cls->text_process($_POST['source_text']) . "',
                source_new_user_id='" . $userid . "'";
            $this->dbh->query($sql);

            $this->source_id = $this->dbh->lastInsertId();
        }

        // Remark: source_change in editorModel.php (used to change sources in familyscreen).
        if (isset($_POST['source_change2'])) {
            $sql = "UPDATE humo_sources SET
            source_status='" . $editor_cls->text_process($_POST['source_status']) . "',
            source_title='" . $editor_cls->text_process($_POST['source_title']) . "',
            source_date='" . $editor_cls->date_process('source_date') . "',
            source_place='" . $editor_cls->text_process($_POST['source_place']) . "',
            source_publ='" . $editor_cls->text_process($_POST['source_publ']) . "',
            source_refn='" . $editor_cls->text_process($_POST['source_refn']) . "',
            source_auth='" . $editor_cls->text_process($_POST['source_auth']) . "',
            source_subj='" . $editor_cls->text_process($_POST['source_subj']) . "',
            source_item='" . $editor_cls->text_process($_POST['source_item']) . "',
            source_kind='" . $editor_cls->text_process($_POST['source_kind']) . "',
            source_repo_caln='" . $editor_cls->text_process($_POST['source_repo_caln']) . "',
            source_repo_page='" . $editor_cls->text_process($_POST['source_repo_page']) . "',
            source_repo_gedcomnr='" . $editor_cls->text_process($_POST['source_repo_gedcomnr']) . "',
            source_text='" . $editor_cls->text_process($_POST['source_text'], true) . "',
            source_changed_user_id='" . $userid . "'
            WHERE source_tree_id='" . $this->tree_id . "' AND source_id='" . $this->source_id . "'";
            $this->dbh->query($sql);
        }

        if (isset($_POST['source_remove2'])) {
            // *** Delete source ***
            $sql = "DELETE FROM humo_sources WHERE source_id='" . $this->source_id . "'";
            $this->dbh->query($sql);

            // *** Delete connections to source, and re-order remaining source connections ***
            $connect_sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' AND connect_source_id='" . safe_text_db($_POST['source_gedcomnr']) . "'";
            $connect_qry = $this->dbh->query($connect_sql);
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
