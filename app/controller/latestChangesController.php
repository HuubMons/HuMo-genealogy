<?php
class LatestChangesController
{
    public function list($dbh, $tree_id): array
    {
        $latest_changesModel = new latestChangesModel();
        $listchanges = $latest_changesModel->listChanges($dbh, $tree_id);

        return $listchanges;
    }
}
