<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\TreeIndexModel;

class TreeIndexController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function get_items(): array
    {
        $mainindex = new TreeIndexModel($this->config);
        $item_array = $mainindex->show_tree_index();
        return $item_array;
    }
}
