<?php
require_once  __DIR__ . "/../model/latest_changes.php";

include_once(__DIR__ . "/../../include/language_date.php");

class Latest_changesController
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function list($dbh, $tree_id)
    {
        $latest_changesModel = new latest_changesModel($dbh);
        $listchanges = $latest_changesModel->listChanges($dbh, $tree_id);
        $data = array(
            "listchanges" => $listchanges,
            "title" => __('Latest changes')
        );
        return $data;
    }
}
