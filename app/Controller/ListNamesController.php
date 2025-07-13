<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\ListNamesModel;

class ListNamesController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list_names($last_name): array
    {
        $list_namesModel = new ListNamesModel($this->config);

        $list_names['alphabet_array'] = $list_namesModel->getAlphabetArray();
        $list_names['max_cols'] = $list_namesModel->getMaxCols();
        $list_names['max_names'] = $list_namesModel->getMaxNames();
        $list_names['last_name'] = $list_namesModel->get_last_name($last_name);
        $list_names["item"] = $list_namesModel->get_item();
        $list_names['start'] = $list_namesModel->get_start();

        $get_names = $list_namesModel->get_names($list_names);
        $list_names = array_merge($list_names, $get_names);

        $get_pagination = $list_namesModel->get_pagination($list_names);
        $list_names = array_merge($list_names, $get_pagination);

        return $list_names;
    }
}
