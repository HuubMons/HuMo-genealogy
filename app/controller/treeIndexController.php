<?php
class TreeIndexController
{
    /*
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }
    */

    public function get_items(): array
    {
        $mainindex = new TreeIndexModel($dbh, $humo_option);
        //$mainindex = new TreeIndexModel($config);
        $item_array = $mainindex->show_tree_index();
        return $item_array;
    }
}
