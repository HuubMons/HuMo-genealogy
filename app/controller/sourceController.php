<?php
class SourceController
{
    protected $config;
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function source($id)
    {
        $sourceModel = new SourceModel();
        $sourceDb = $sourceModel->GetSource($this->config->db_functions, $id);
        $get_source_connections = $sourceModel->GetSourceConnections($this->config->dbh, $this->config->tree_id, $sourceDb->source_gedcomnr);

        return array(
            "sourceDb" => $sourceDb,
            "source_connections" => $get_source_connections,
            "title" => __('Source')
        );
    }
}
