<?php
include_once(__DIR__ . "/../../include/language_date.php");

class LatestChangesController
{
    public function list($dbh, $tree_id)
    {
        $latest_changesModel = new latestChangesModel();
        $listchanges = $latest_changesModel->listChanges($dbh, $tree_id);

        return $listchanges;
    }
}
