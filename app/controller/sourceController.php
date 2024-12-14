<?php
include_once(__DIR__ . "/../../include/date_place.php");
include_once(__DIR__ . "/../../include/process_text.php");
include_once(__DIR__ . "/../../include/show_picture.php");
//include_once(__DIR__ . "/../../include/show_sources.php");
include_once(__DIR__ . "/../../include/language_date.php");

class SourceController extends Controller
{
    public function source($id)
    {
        $sourceModel = new SourceModel();
        $sourceDb = $sourceModel->GetSource($this->db_functions, $id);
        $get_source_connections = $sourceModel->GetSourceConnections($this->dbh, $this->tree_id, $sourceDb->source_gedcomnr);

        return array(
            "sourceDb" => $sourceDb,
            "source_connections" => $get_source_connections,
            "title" => __('Source')
        );

        //$data = array_merge($data, $sourceDb);
    }
}
