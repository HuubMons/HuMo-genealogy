<?php
class SourceController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function source($id): array
    {
        $sourceModel = new SourceModel($this->config);

        $sourceDb = $sourceModel->GetSource($id);
        $get_source_connections = $sourceModel->GetSourceConnections($sourceDb->source_gedcomnr);

        return array(
            "sourceDb" => $sourceDb,
            "source_connections" => $get_source_connections,
            "title" => __('Source')
        );
    }
}
