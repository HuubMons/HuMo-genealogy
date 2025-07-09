<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\latestChangesModel;

class LatestChangesController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list(): array
    {
        $latest_changesModel = new latestChangesModel($this->config);

        $listchanges = $latest_changesModel->listChanges();
        return $listchanges;
    }
}
