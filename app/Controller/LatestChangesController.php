<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\LatestChangesModel;

class LatestChangesController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list(): array
    {
        $latest_changesModel = new LatestChangesModel($this->config);

        $listchanges = $latest_changesModel->listChanges();
        return $listchanges;
    }
}
