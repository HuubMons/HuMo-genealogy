<?php
require_once  __DIR__ . "/../model/sources.php";

include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");

class SourcesController
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function list($dbh, $tree_id, $user, $humo_option, $link_cls, $uri_path)
    {
        $sourceModel = new SourcesModel($dbh);
        $listsources = $sourceModel->listSources($dbh, $tree_id, $user, $humo_option);
        $line_pages = $sourceModel->line_pages($tree_id, $link_cls, $uri_path);
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
