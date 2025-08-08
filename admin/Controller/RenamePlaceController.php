<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\RenamePlaceModel;

class RenamePlaceController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $renamePlaceModel = new RenamePlaceModel($this->admin_config);

        $renamePlaceModel->update_place();
        $place['result'] = $renamePlaceModel->get_query();
        $place['select'] = $renamePlaceModel->get_place_select();

        return $place;
    }
}
