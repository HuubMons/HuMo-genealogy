<?php
class SourcesController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list(): array
    {
        $sourceModel = new SourcesModel($this->config);

        $sourceModel->process_variables();
        $listsources = $sourceModel->listSources();
        $line_pages = $sourceModel->line_pages();
        $source_search = $sourceModel->get_source_search();
        $sort_desc = $sourceModel->get_sort_desc();
        $order_sources = $sourceModel->get_order_sources();
        $data = array(
            "listsources" => $listsources,
            "source_search" => $source_search,
            "sort_desc" => $sort_desc,
            "order_sources" => $order_sources,
            "title" => __('Sources')
        );

        return array_merge($data, $line_pages);
    }
}
